<?php
/**
 * Central campaign resolution system
 *
 * @package CRO_Toolkit
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * CRO_Decision_Engine class.
 *
 * Central system for resolving which campaign to show.
 * Ensures only ONE campaign per pageview.
 */
class CRO_Decision_Engine {

	/**
	 * Singleton instance.
	 *
	 * @var CRO_Decision_Engine|null
	 */
	private static $instance = null;

	/**
	 * Active campaigns cache.
	 *
	 * @var array
	 */
	private $active_campaigns = array();

	/**
	 * Decision log for debugging.
	 *
	 * @var array
	 */
	private $decision_log = array();

	/**
	 * Whether a campaign was shown this pageview.
	 *
	 * @var bool
	 */
	private $shown_this_pageview = false;

	/**
	 * Get singleton instance.
	 *
	 * @return CRO_Decision_Engine
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get the single best campaign to show for current context.
	 * Only ONE campaign per pageview.
	 *
	 * @param array $context Current page context.
	 * @return object|null Campaign object or null.
	 */
	public function resolve_campaign( $context ) {
		// Already shown something this pageview.
		if ( $this->shown_this_pageview ) {
			$this->log( 'SKIP', 'Already shown campaign this pageview' );
			return null;
		}

		// Get all active campaigns.
		$campaigns = CRO_Campaign::get_all( array( 'status' => 'active' ) );

		if ( empty( $campaigns ) ) {
			$this->log( 'SKIP', 'No active campaigns found' );
			return null;
		}

		$this->log( 'INFO', sprintf( 'Evaluating %d active campaigns', count( $campaigns ) ) );

		// Filter by targeting rules.
		$matching = $this->filter_by_targeting( $campaigns, $context );

		if ( empty( $matching ) ) {
			$this->log( 'SKIP', 'No campaigns matched targeting rules' );
			return $this->get_fallback_campaign( $context );
		}

		// Filter by suppression rules.
		$eligible = $this->filter_by_suppression( $matching, $context );

		if ( empty( $eligible ) ) {
			$this->log( 'SKIP', 'All matching campaigns suppressed' );
			return null;
		}

		// Sort by priority and pick the winner.
		$winner = $this->select_winner( $eligible );

		$this->log( 'SUCCESS', sprintf( 'Selected campaign: %s (ID: %d)', $winner->name, $winner->id ) );

		return $winner;
	}

	/**
	 * Mark that a campaign was shown this pageview.
	 *
	 * @param int $campaign_id Campaign ID.
	 */
	public function mark_shown( $campaign_id ) {
		$this->shown_this_pageview = true;
		$this->log( 'INFO', sprintf( 'Marked campaign %d as shown', $campaign_id ) );
	}

	/**
	 * Filter campaigns by targeting rules.
	 *
	 * @param array $campaigns Campaign objects.
	 * @param array $context Current context.
	 * @return array Matching campaigns.
	 */
	private function filter_by_targeting( $campaigns, $context ) {
		$targeting = new CRO_Targeting();
		$matched = array();

		foreach ( $campaigns as $campaign ) {
			// Convert array to object for targeting evaluation if needed.
			$campaign_obj = is_array( $campaign ) ? (object) $campaign : $campaign;
			
			$result = $targeting->evaluate( $campaign_obj, $context );

			if ( $result === true ) {
				$matched[] = $campaign_obj;
				$this->log( 'PASS', sprintf( 'Campaign "%s" passed targeting', $campaign_obj->name ) );
			} else {
				$this->log( 'FAIL', sprintf( 'Campaign "%s" failed targeting: %s', $campaign_obj->name, $result ) );
			}
		}

		return $matched;
	}

	/**
	 * Filter campaigns by suppression rules.
	 *
	 * @param array $campaigns Campaign objects.
	 * @param array $context Current context.
	 * @return array Eligible campaigns.
	 */
	private function filter_by_suppression( $campaigns, $context ) {
		$visitor_state = CRO_Visitor_State::get_instance();
		$eligible = array();

		foreach ( $campaigns as $campaign ) {
			// Ensure campaign is an object.
			$campaign_obj = is_array( $campaign ) ? (object) $campaign : $campaign;
			
			// Check frequency cap.
			if ( ! $this->check_frequency( $campaign_obj, $visitor_state ) ) {
				$this->log( 'SUPPRESS', sprintf( 'Campaign "%s" suppressed: frequency cap', $campaign_obj->name ) );
				continue;
			}

			// Check dismissal cooldown.
			if ( ! $this->check_dismissal_cooldown( $campaign_obj, $visitor_state ) ) {
				$this->log( 'SUPPRESS', sprintf( 'Campaign "%s" suppressed: dismissal cooldown', $campaign_obj->name ) );
				continue;
			}

			// Check post-conversion suppression.
			if ( ! $this->check_conversion_suppression( $campaign_obj, $visitor_state ) ) {
				$this->log( 'SUPPRESS', sprintf( 'Campaign "%s" suppressed: post-conversion window', $campaign_obj->name ) );
				continue;
			}

			// Check global suppression rules.
			if ( ! $this->check_global_suppression( $context ) ) {
				$this->log( 'SUPPRESS', sprintf( 'Campaign "%s" suppressed: global rules', $campaign_obj->name ) );
				continue;
			}

			$eligible[] = $campaign_obj;
		}

		return $eligible;
	}

	/**
	 * Check frequency cap for campaign.
	 *
	 * @param object $campaign Campaign object.
	 * @param object $visitor_state Visitor state instance.
	 * @return bool True if frequency cap allows showing.
	 */
	private function check_frequency( $campaign, $visitor_state ) {
		// Handle both array and object formats.
		$rules = is_array( $campaign->display_rules ) ? $campaign->display_rules : (array) $campaign->display_rules;
		$frequency = $rules['frequency'] ?? 'once_per_session';

		$last_shown = $visitor_state->get_campaign_last_shown( $campaign->id );

		if ( ! $last_shown ) {
			return true; // Never shown before.
		}

		$now = time();

		switch ( $frequency ) {
			case 'once_ever':
				return false; // Already shown once.

			case 'once_per_session':
				return ! $visitor_state->was_shown_this_session( $campaign->id );

			case 'once_per_day':
				return ( $now - $last_shown ) > DAY_IN_SECONDS;

			case 'once_per_x_days':
				$days = intval( $rules['frequency_days'] ?? 7 );
				return ( $now - $last_shown ) > ( $days * DAY_IN_SECONDS );

			default:
				return true;
		}
	}

	/**
	 * Check dismissal cooldown (don't show again too soon after dismiss).
	 *
	 * @param object $campaign Campaign object.
	 * @param object $visitor_state Visitor state instance.
	 * @return bool True if cooldown allows showing.
	 */
	private function check_dismissal_cooldown( $campaign, $visitor_state ) {
		$last_dismiss = $visitor_state->get_campaign_last_dismiss( $campaign->id );

		if ( ! $last_dismiss ) {
			return true;
		}

		// Default: 1 hour cooldown after dismiss.
		$cooldown = apply_filters( 'cro_dismissal_cooldown', HOUR_IN_SECONDS, $campaign );

		return ( time() - $last_dismiss ) > $cooldown;
	}

	/**
	 * Check post-conversion suppression (don't spam after they converted).
	 *
	 * @param object $campaign Campaign object.
	 * @param object $visitor_state Visitor state instance.
	 * @return bool True if suppression window allows showing.
	 */
	private function check_conversion_suppression( $campaign, $visitor_state ) {
		$last_conversion = $visitor_state->get_last_conversion_time();

		if ( ! $last_conversion ) {
			return true;
		}

		// Default: Don't show campaigns for 24 hours after any conversion.
		$suppression_window = apply_filters( 'cro_conversion_suppression_window', DAY_IN_SECONDS );

		return ( time() - $last_conversion ) > $suppression_window;
	}

	/**
	 * Check global suppression rules.
	 *
	 * @param array $context Current context.
	 * @return bool True if global rules allow showing.
	 */
	private function check_global_suppression( $context ) {
		// Don't show if user is currently typing in a form.
		if ( ! empty( $context['is_typing'] ) ) {
			return false;
		}

		// Don't show on checkout page.
		if ( ! empty( $context['is_checkout'] ) ) {
			return false;
		}

		// Don't show during payment.
		if ( ! empty( $context['is_payment_form'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Select winner from eligible campaigns by priority.
	 *
	 * @param array $campaigns Eligible campaigns.
	 * @return object Winning campaign.
	 */
	private function select_winner( $campaigns ) {
		// Sort by priority (higher = better).
		usort(
			$campaigns,
			function( $a, $b ) {
				$rules_a = is_array( $a->display_rules ) ? $a->display_rules : (array) $a->display_rules;
				$rules_b = is_array( $b->display_rules ) ? $b->display_rules : (array) $b->display_rules;
				$priority_a = $rules_a['priority'] ?? 10;
				$priority_b = $rules_b['priority'] ?? 10;
				return $priority_b - $priority_a;
			}
		);

		// Return highest priority.
		return $campaigns[0];
	}

	/**
	 * Get fallback campaign if no regular campaigns matched.
	 *
	 * @param array $context Current context.
	 * @return object|null Fallback campaign or null.
	 */
	private function get_fallback_campaign( $context ) {
		$fallback_id = cro_settings()->get( 'general', 'fallback_campaign_id', 0 );

		if ( ! $fallback_id ) {
			return null;
		}

		$fallback = CRO_Campaign::get( $fallback_id );

		if ( $fallback && ( is_array( $fallback ) ? $fallback['status'] : $fallback->status ) === 'active' ) {
			$this->log( 'INFO', 'Using fallback campaign' );
			// Convert to object if needed.
			return is_array( $fallback ) ? (object) $fallback : $fallback;
		}

		return null;
	}

	/**
	 * Log decision for debugging.
	 *
	 * @param string $type Log type (INFO, SKIP, SUCCESS, etc.).
	 * @param string $message Log message.
	 */
	private function log( $type, $message ) {
		$this->decision_log[] = array(
			'time'    => microtime( true ),
			'type'    => $type,
			'message' => $message,
		);
	}

	/**
	 * Get decision log (for debug mode).
	 *
	 * @return array Decision log entries.
	 */
	public function get_decision_log() {
		return $this->decision_log;
	}

	/**
	 * Get decision summary as string.
	 *
	 * @return string Decision summary.
	 */
	public function get_decision_summary() {
		$summary = array();
		foreach ( $this->decision_log as $entry ) {
			$summary[] = sprintf( '[%s] %s', $entry['type'], $entry['message'] );
		}
		return implode( "\n", $summary );
	}
}

/**
 * Global accessor function for decision engine.
 *
 * @return CRO_Decision_Engine
 */
function cro_decision_engine() {
	return CRO_Decision_Engine::get_instance();
}
