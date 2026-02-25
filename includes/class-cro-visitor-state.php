<?php
/**
 * Persistent visitor memory
 *
 * @package CRO_Toolkit
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * CRO_Visitor_State class.
 *
 * Manages persistent visitor state via cookies.
 * Tracks campaigns shown, dismissed, conversions, sessions, etc.
 */
class CRO_Visitor_State {

	/**
	 * Singleton instance.
	 *
	 * @var CRO_Visitor_State|null
	 */
	private static $instance = null;

	/**
	 * Visitor ID.
	 *
	 * @var string
	 */
	private $visitor_id;

	/**
	 * Visitor state data.
	 *
	 * @var array
	 */
	private $state = array();

	/**
	 * Cookie name.
	 *
	 * @var string
	 */
	private $cookie_name = 'cro_visitor_state';

	/**
	 * Cookie duration in seconds (30 days).
	 *
	 * @var int
	 */
	private $cookie_duration = 30 * DAY_IN_SECONDS;

	/**
	 * Get singleton instance.
	 *
	 * @return CRO_Visitor_State
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_state();
	}

	/**
	 * Load visitor state from cookie.
	 */
	private function load_state() {
		if ( isset( $_COOKIE[ $this->cookie_name ] ) ) {
			$decoded = json_decode( base64_decode( $_COOKIE[ $this->cookie_name ] ), true );
			if ( is_array( $decoded ) ) {
				$this->state = $decoded;
			}
		}

		// Initialize if new visitor.
		if ( empty( $this->state ) ) {
			$this->state = array(
				'visitor_id'          => $this->generate_visitor_id(),
				'first_seen'          => time(),
				'session_count'       => 1,
				'last_session_start' => time(),
				'campaigns_shown'     => array(),
				'campaigns_dismissed' => array(),
				'last_conversion'     => null,
				'total_conversions'   => 0,
			);
		}

		// Check if new session (30 min gap).
		$last_activity = $this->state['last_activity'] ?? 0;
		if ( ( time() - $last_activity ) > 1800 ) {
			$this->state['session_count']++;
			$this->state['last_session_start'] = time();
			$this->state['shown_this_session'] = array();
		}

		$this->state['last_activity'] = time();
		$this->visitor_id = $this->state['visitor_id'];
	}

	/**
	 * Save state to cookie.
	 */
	public function save() {
		$encoded = base64_encode( wp_json_encode( $this->state ) );

		if ( ! headers_sent() ) {
			setcookie(
				$this->cookie_name,
				$encoded,
				time() + $this->cookie_duration,
				COOKIEPATH,
				COOKIE_DOMAIN,
				is_ssl(),
				true // httponly
			);
		}
	}

	/**
	 * Generate unique visitor ID.
	 *
	 * @return string
	 */
	private function generate_visitor_id() {
		return 'cro_' . bin2hex( random_bytes( 16 ) );
	}

	/**
	 * Get visitor ID.
	 *
	 * @return string
	 */
	public function get_visitor_id() {
		return $this->visitor_id;
	}

	/**
	 * Check if first visit.
	 *
	 * @return bool
	 */
	public function is_new_visitor() {
		return $this->state['session_count'] <= 1;
	}

	/**
	 * Get session count.
	 *
	 * @return int
	 */
	public function get_session_count() {
		return $this->state['session_count'];
	}

	/**
	 * Get first seen timestamp.
	 *
	 * @return int
	 */
	public function get_first_seen() {
		return $this->state['first_seen'];
	}

	/**
	 * Record campaign shown.
	 *
	 * @param int $campaign_id Campaign ID.
	 */
	public function record_campaign_shown( $campaign_id ) {
		$this->state['campaigns_shown'][ $campaign_id ] = time();

		if ( ! isset( $this->state['shown_this_session'] ) ) {
			$this->state['shown_this_session'] = array();
		}
		$this->state['shown_this_session'][] = $campaign_id;

		$this->save();
	}

	/**
	 * Record campaign dismissed.
	 *
	 * @param int $campaign_id Campaign ID.
	 */
	public function record_campaign_dismissed( $campaign_id ) {
		$this->state['campaigns_dismissed'][ $campaign_id ] = time();
		$this->save();
	}

	/**
	 * Record conversion.
	 *
	 * @param int|null $campaign_id Optional campaign ID that triggered conversion.
	 */
	public function record_conversion( $campaign_id = null ) {
		$this->state['last_conversion'] = time();
		$this->state['total_conversions']++;

		if ( $campaign_id ) {
			if ( ! isset( $this->state['converted_campaigns'] ) ) {
				$this->state['converted_campaigns'] = array();
			}
			$this->state['converted_campaigns'][ $campaign_id ] = time();
		}

		$this->save();
	}

	/**
	 * Get last time campaign was shown.
	 *
	 * @param int $campaign_id Campaign ID.
	 * @return int|null Timestamp or null if never shown.
	 */
	public function get_campaign_last_shown( $campaign_id ) {
		return $this->state['campaigns_shown'][ $campaign_id ] ?? null;
	}

	/**
	 * Get last time campaign was dismissed.
	 *
	 * @param int $campaign_id Campaign ID.
	 * @return int|null Timestamp or null if never dismissed.
	 */
	public function get_campaign_last_dismiss( $campaign_id ) {
		return $this->state['campaigns_dismissed'][ $campaign_id ] ?? null;
	}

	/**
	 * Check if campaign was shown this session.
	 *
	 * @param int $campaign_id Campaign ID.
	 * @return bool
	 */
	public function was_shown_this_session( $campaign_id ) {
		$shown = is_array( $this->state['shown_this_session'] ?? null ) ? $this->state['shown_this_session'] : array();
		return in_array( (int) $campaign_id, $shown, true );
	}

	/**
	 * Get last conversion time.
	 *
	 * @return int|null Timestamp or null if never converted.
	 */
	public function get_last_conversion_time() {
		return $this->state['last_conversion'];
	}

	/**
	 * Get total conversions.
	 *
	 * @return int
	 */
	public function get_total_conversions() {
		return $this->state['total_conversions'];
	}

	/**
	 * Check if visitor already converted on specific campaign.
	 *
	 * @param int $campaign_id Campaign ID.
	 * @return bool
	 */
	public function has_converted_on_campaign( $campaign_id ) {
		$converted = $this->state['converted_campaigns'] ?? array();
		return isset( $converted[ $campaign_id ] );
	}

	/**
	 * Record coupon used by visitor (for offer guard / abuse prevention).
	 *
	 * @param string $coupon_code Coupon code.
	 */
	public function record_coupon_used( $coupon_code ) {
		if ( empty( $coupon_code ) || ! is_string( $coupon_code ) ) {
			return;
		}
		if ( ! isset( $this->state['used_coupons'] ) ) {
			$this->state['used_coupons'] = array();
		}
		$code = strtolower( trim( (string) ( $coupon_code ?? '' ) ) );
		$uc = is_array( $this->state['used_coupons'] ?? null ) ? $this->state['used_coupons'] : array();
		if ( ! in_array( $code, $uc, true ) ) {
			$this->state['used_coupons'] = array_merge( $uc, array( $code ) );
		}
		$this->save();
	}

	/**
	 * Check if visitor has already used this coupon.
	 *
	 * @param string $coupon_code Coupon code.
	 * @return bool
	 */
	public function has_used_coupon( $coupon_code ) {
		$used  = is_array( $this->state['used_coupons'] ?? null ) ? $this->state['used_coupons'] : array();
		$code  = strtolower( trim( (string) ( $coupon_code ?? '' ) ) );
		return in_array( $code, array_map( 'strtolower', $used ), true );
	}

	/**
	 * Get full state (for debugging).
	 *
	 * @return array
	 */
	public function get_state() {
		return $this->state;
	}

	/**
	 * Clear state (for testing).
	 */
	public function clear() {
		$this->state = array();
		if ( ! headers_sent() ) {
			setcookie( $this->cookie_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
		}
	}
}

/**
 * Initialize visitor state on plugins_loaded.
 */
add_action(
	'plugins_loaded',
	function() {
		CRO_Visitor_State::get_instance();
	},
	5
);

/**
 * Save state on shutdown.
 */
add_action(
	'shutdown',
	function() {
		CRO_Visitor_State::get_instance()->save();
	}
);
