<?php
/**
 * Preset Library for campaigns and boosters.
 *
 * @package CRO_Toolkit
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * CRO_Presets class.
 */
class CRO_Presets {

	/**
	 * Get all available presets.
	 *
	 * @return array
	 */
	public static function get_all() {
		$presets = self::get_preset_definitions();
		return array_values( $presets );
	}

	/**
	 * Get a single preset by ID.
	 *
	 * @param string $id Preset ID.
	 * @return array|null Preset array or null.
	 */
	public static function get( $id ) {
		$presets = self::get_preset_definitions();
		return isset( $presets[ $id ] ) ? $presets[ $id ] : null;
	}

	/**
	 * Apply a preset: write settings and optionally create a campaign.
	 *
	 * @param string $id Preset ID.
	 * @return array{ success: bool, message: string, campaign_id?: int }
	 */
	public static function apply( $id ) {
		$preset = self::get( $id );
		if ( ! $preset ) {
			return array( 'success' => false, 'message' => __( 'Preset not found.', 'cro-toolkit' ) );
		}

		if ( ! function_exists( 'cro_settings' ) ) {
			return array( 'success' => false, 'message' => __( 'Settings not available.', 'cro-toolkit' ) );
		}

		$settings = cro_settings();

		// 1. Enable/disable features
		$all_features = array( 'campaigns', 'sticky_cart', 'shipping_bar', 'trust_badges', 'cart_optimizer', 'checkout_optimizer', 'stock_urgency' );
		$enable      = isset( $preset['features'] ) ? (array) $preset['features'] : array();
		foreach ( $all_features as $feature ) {
			$settings->set( 'general', $feature . '_enabled', in_array( $feature, $enable, true ) );
		}

		// 2. Apply group settings
		if ( ! empty( $preset['settings'] ) && is_array( $preset['settings'] ) ) {
			foreach ( $preset['settings'] as $group => $pairs ) {
				if ( ! is_array( $pairs ) ) {
					continue;
				}
				foreach ( $pairs as $key => $value ) {
					$settings->set( $group, $key, $value );
				}
			}
		}

		// 3. Optional: create campaign
		$campaign_id = null;
		if ( ! empty( $preset['campaign'] ) && is_array( $preset['campaign'] ) && class_exists( 'CRO_Campaign' ) ) {
			$campaign_data = wp_parse_args(
				$preset['campaign'],
				array(
					'name'             => sanitize_text_field( $preset['name'] ?? '' ),
					'status'           => 'draft',
					'campaign_type'    => 'exit_intent',
					'template_type'    => 'centered',
					'trigger_settings' => array(),
					'content'          => array(),
					'styling'          => array(),
					'targeting_rules'  => array(),
					'display_rules'    => array(),
				)
			);
			$campaign_id = CRO_Campaign::create( $campaign_data );
		}

		$message = isset( $preset['apply_message'] ) ? $preset['apply_message'] : __( 'Preset applied successfully.', 'cro-toolkit' );
		if ( $campaign_id ) {
			$message .= ' ' . __( 'A new campaign was created.', 'cro-toolkit' );
		}

		return array(
			'success'     => true,
			'message'     => $message,
			'campaign_id' => $campaign_id,
		);
	}

	/**
	 * Preset definitions (id => preset array).
	 *
	 * @return array
	 */
	private static function get_preset_definitions() {
		return array(
			'free_shipping_bar' => array(
				'id'          => 'free_shipping_bar',
				'name'        => __( 'Free Shipping Bar', 'cro-toolkit' ),
				'description' => __( 'Shows a progress bar toward free shipping on product, cart, and shop. Encourages higher order value.', 'cro-toolkit' ),
				'features'    => array( 'shipping_bar' ),
				'settings'    => array(
					'shipping_bar' => array(
						'use_woo_threshold' => true,
						'threshold'         => 0,
						'tone'              => 'neutral',
						'message_progress'  => '',
						'message_achieved'  => '',
						'show_on_pages'     => array( 'product', 'cart', 'shop' ),
						'position'          => 'top',
						'bg_color'          => '#f7f7f7',
						'bar_color'         => '#333333',
					),
				),
				'apply_message' => __( 'Free shipping bar is now enabled on product, cart, and shop.', 'cro-toolkit' ),
			),

			'low_stock_urgency' => array(
				'id'          => 'low_stock_urgency',
				'name'        => __( 'Low Stock Urgency', 'cro-toolkit' ),
				'description' => __( 'Displays "Only X left!" on product pages when stock is low. Drives urgency without a campaign.', 'cro-toolkit' ),
				'features'    => array( 'stock_urgency' ),
				'settings'    => array(),
				'apply_message' => __( 'Low stock urgency messages are now enabled on product pages.', 'cro-toolkit' ),
			),

			'trust_badges_checkout' => array(
				'id'          => 'trust_badges_checkout',
				'name'        => __( 'Trust Badges & Checkout', 'cro-toolkit' ),
				'description' => __( 'Trust badges on product and cart; secure checkout message and badge on checkout to reduce friction.', 'cro-toolkit' ),
				'features'    => array( 'trust_badges', 'checkout_optimizer' ),
				'settings'    => array(
					'checkout_optimizer' => array(
						'show_trust_message' => true,
						'trust_message_text' => __( 'Secure checkout – your data is protected.', 'cro-toolkit' ),
						'show_secure_badge'  => true,
						'show_guarantee'     => true,
						'guarantee_text'     => __( '30-day money-back guarantee', 'cro-toolkit' ),
					),
				),
				'apply_message' => __( 'Trust badges and checkout trust elements are now enabled.', 'cro-toolkit' ),
			),

			'sticky_cta_minimal' => array(
				'id'          => 'sticky_cta_minimal',
				'name'        => __( 'Sticky CTA Minimal', 'cro-toolkit' ),
				'description' => __( 'Minimal sticky add-to-cart bar on product pages (mobile-first). Clean look, no image.', 'cro-toolkit' ),
				'features'    => array( 'sticky_cart' ),
				'settings'    => array(
					'sticky_cart' => array(
						'show_on_mobile_only' => true,
						'show_after_scroll'   => 150,
						'show_product_image'  => false,
						'show_product_title'  => true,
						'show_price'          => true,
						'button_text'         => __( 'Add to Cart', 'cro-toolkit' ),
						'bg_color'            => '#ffffff',
						'button_bg_color'     => '#333333',
						'button_text_color'   => '#ffffff',
					),
				),
				'apply_message' => __( 'Minimal sticky add-to-cart bar is now enabled.', 'cro-toolkit' ),
			),

			'exit_intent_email' => array(
				'id'          => 'exit_intent_email',
				'name'        => __( 'Exit Intent Email', 'cro-toolkit' ),
				'description' => __( 'Exit-intent popup that captures email and offers a discount. Targets product and cart pages.', 'cro-toolkit' ),
				'features'    => array( 'campaigns' ),
				'settings'    => array(),
				'campaign'    => array(
					'name'             => __( 'Exit Intent – Email Capture', 'cro-toolkit' ),
					'campaign_type'    => 'exit_intent',
					'template_type'    => 'centered',
					'status'           => 'draft',
					'trigger_settings' => array(
						'type'                => 'exit_intent',
						'sensitivity'         => 'medium',
						'require_interaction' => true,
					),
					'content' => array(
						'headline'          => __( 'Wait! Get 10% Off', 'cro-toolkit' ),
						'subheadline'       => __( 'Enter your email for a discount code.', 'cro-toolkit' ),
						'show_email_field'   => true,
						'email_placeholder'  => __( 'Your email', 'cro-toolkit' ),
						'show_coupon'        => true,
						'coupon_code'        => 'SAVE10',
						'coupon_display_text'=> __( 'Use code: SAVE10', 'cro-toolkit' ),
						'cta_text'           => __( 'Send My Code', 'cro-toolkit' ),
						'show_dismiss_link'  => true,
						'dismiss_text'       => __( 'No thanks', 'cro-toolkit' ),
					),
					'styling' => array(
						'bg_color'          => '#ffffff',
						'text_color'        => '#333333',
						'headline_color'    => '#000000',
						'button_bg_color'   => '#333333',
						'button_text_color' => '#ffffff',
						'border_radius'     => 8,
					),
					'targeting_rules' => array(
						'pages' => array(
							'type'    => 'specific',
							'include' => array( 'product', 'cart' ),
							'exclude' => array( 'checkout' ),
						),
					),
					'display_rules' => array(
						'frequency' => 'once_per_session',
					),
				),
				'apply_message' => __( 'Exit intent email campaign created. Review and activate it in Campaigns.', 'cro-toolkit' ),
			),

			'cart_upsell_reminder' => array(
				'id'          => 'cart_upsell_reminder',
				'name'        => __( 'Cart Upsell Reminder', 'cro-toolkit' ),
				'description' => __( 'Time-based popup on cart page reminding visitors of free shipping or an offer. Good for cart abandoners.', 'cro-toolkit' ),
				'features'    => array( 'campaigns' ),
				'settings'    => array(),
				'campaign'    => array(
					'name'             => __( 'Cart – Free Shipping Reminder', 'cro-toolkit' ),
					'campaign_type'    => 'time_trigger',
					'template_type'    => 'centered',
					'status'           => 'draft',
					'trigger_settings' => array(
						'type'              => 'time_trigger',
						'time_on_page_seconds' => 15,
						'require_interaction' => false,
					),
					'content' => array(
						'headline'          => __( 'You\'re so close!', 'cro-toolkit' ),
						'subheadline'       => __( 'Add a bit more to your cart to get free shipping.', 'cro-toolkit' ),
						'show_email_field'   => false,
						'show_coupon'        => false,
						'cta_text'           => __( 'Continue to Cart', 'cro-toolkit' ),
						'show_dismiss_link'  => true,
					),
					'styling' => array(
						'bg_color'          => '#ffffff',
						'button_bg_color'   => '#333333',
						'button_text_color' => '#ffffff',
						'border_radius'     => 8,
					),
					'targeting_rules' => array(
						'pages' => array(
							'type'    => 'specific',
							'include' => array( 'cart' ),
							'exclude' => array(),
						),
					),
					'display_rules' => array(
						'frequency' => 'once_per_session',
					),
				),
				'apply_message' => __( 'Cart upsell reminder campaign created. Review and activate it in Campaigns.', 'cro-toolkit' ),
			),

			'quick_boost' => array(
				'id'          => 'quick_boost',
				'name'        => __( 'Quick Boost', 'cro-toolkit' ),
				'description' => __( 'Shipping bar + sticky add-to-cart + trust badges. Ideal first setup for new stores.', 'cro-toolkit' ),
				'features'    => array( 'shipping_bar', 'sticky_cart', 'trust_badges' ),
				'settings'    => array(
					'shipping_bar' => array(
						'use_woo_threshold' => true,
						'show_on_pages'     => array( 'product', 'cart', 'shop' ),
						'message_progress'  => __( 'You are {amount} away from free shipping!', 'cro-toolkit' ),
						'message_achieved'  => __( 'You qualify for free shipping!', 'cro-toolkit' ),
					),
					'sticky_cart' => array(
						'show_on_mobile_only' => true,
						'show_product_image'  => true,
						'show_product_title'  => true,
						'show_price'          => true,
						'button_text'         => __( 'Add to Cart', 'cro-toolkit' ),
					),
				),
				'apply_message' => __( 'Quick boost preset applied: shipping bar, sticky cart, and trust badges enabled.', 'cro-toolkit' ),
			),

			'conversion_stack' => array(
				'id'          => 'conversion_stack',
				'name'        => __( 'Conversion Stack', 'cro-toolkit' ),
				'description' => __( 'Full stack: shipping bar, sticky cart, trust badges, cart optimizer, and one exit-intent campaign. Maximize conversions.', 'cro-toolkit' ),
				'features'    => array( 'shipping_bar', 'sticky_cart', 'trust_badges', 'cart_optimizer', 'campaigns' ),
				'settings'    => array(
					'shipping_bar' => array(
						'use_woo_threshold' => true,
						'show_on_pages'     => array( 'product', 'cart', 'shop' ),
					),
					'cart_optimizer' => array(
						'show_trust_under_total' => true,
						'trust_message'         => __( 'Secure payment · Fast shipping · Easy returns', 'cro-toolkit' ),
						'show_urgency'           => true,
						'urgency_message'       => __( 'Items in your cart are in high demand!', 'cro-toolkit' ),
					),
				),
				'campaign' => array(
					'name'             => __( 'Exit Intent – Special Offer', 'cro-toolkit' ),
					'campaign_type'    => 'exit_intent',
					'template_type'    => 'centered',
					'status'           => 'draft',
					'trigger_settings' => array( 'type' => 'exit_intent', 'sensitivity' => 'medium' ),
					'content' => array(
						'headline'           => __( 'Wait! Don\'t leave yet', 'cro-toolkit' ),
						'subheadline'        => __( 'We have a special offer for you.', 'cro-toolkit' ),
						'show_email_field'    => true,
						'show_coupon'         => true,
						'coupon_code'        => 'WELCOME10',
						'coupon_display_text'=> 'Use code: WELCOME10',
						'cta_text'            => __( 'Claim My Discount', 'cro-toolkit' ),
						'show_dismiss_link'   => true,
					),
					'styling' => array(
						'bg_color' => '#ffffff',
						'button_bg_color' => '#333333',
						'button_text_color' => '#ffffff',
						'border_radius' => 8,
					),
					'targeting_rules' => array(
						'pages' => array(
							'type'    => 'specific',
							'include' => array( 'product', 'cart' ),
							'exclude' => array( 'checkout' ),
						),
					),
					'display_rules' => array( 'frequency' => 'once_per_session' ),
				),
				'apply_message' => __( 'Conversion stack applied. One exit-intent campaign was created – review and activate in Campaigns.', 'cro-toolkit' ),
			),
		);
	}
}
