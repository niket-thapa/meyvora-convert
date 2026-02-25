<?php
/**
 * Cart optimizer
 *
 * @package CRO_Toolkit
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Cart optimizer class.
 */
class CRO_Cart_Optimizer {

	/**
	 * Initialize cart optimizer.
	 */
	public function __construct() {
		add_action( 'woocommerce_before_cart', array( $this, 'add_cart_upsells' ) );
		add_action( 'woocommerce_before_cart', array( $this, 'render_trust_urgency_top' ), 5 );
		add_action( 'woocommerce_cart_collaterals', array( $this, 'add_cart_cross_sells' ), 15 );
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'optimize_quantity_input' ), 10, 3 );
		add_action( 'woocommerce_after_cart_table', array( $this, 'render_benefits_bottom' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_exit_nudge' ), 25 );
	}

	/**
	 * Add upsells to cart page.
	 */
	public function add_cart_upsells() {
		// Upsells can be added here
		do_action( 'cro_cart_upsells' );
	}

	/**
	 * Add cross-sells to cart page.
	 */
	public function add_cart_cross_sells() {
		// Cross-sells are handled by WooCommerce by default
		do_action( 'cro_cart_cross_sells' );
	}

	/**
	 * Optimize quantity input.
	 *
	 * @param string $product_quantity Quantity input HTML.
	 * @param string $cart_item_key    Cart item key.
	 * @param array  $cart_item        Cart item data.
	 * @return string
	 */
	public function optimize_quantity_input( $product_quantity, $cart_item_key, $cart_item ) {
		// Add plus/minus buttons or other optimizations
		return apply_filters( 'cro_quantity_input', $product_quantity, $cart_item_key, $cart_item );
	}

	/**
	 * Render trust and urgency at the top of the cart (classic cart only; block cart uses CRO_Blocks_Integration).
	 * Respects banner frequency cap (max N times per visitor per 24h).
	 */
	public function render_trust_urgency_top() {
		if ( ! function_exists( 'cro_settings' ) || ! cro_settings()->is_feature_enabled( 'cart_optimizer' ) ) {
			return;
		}
		$settings = cro_settings()->get_cart_optimizer_settings();
		$cap      = cro_settings()->get_banner_frequency_settings();
		$max_24h  = (int) ( $cap['max_per_24h'] ?? 0 );
		$visitor  = class_exists( 'CRO_Visitor_State' ) ? CRO_Visitor_State::get_instance() : null;
		$parts    = array();

		if ( ! empty( $settings['show_trust_under_total'] ) ) {
			$can_show = ! $visitor || $visitor->can_show_banner( 'trust', $max_24h );
			if ( $can_show ) {
				$msg   = isset( $settings['trust_message'] ) && (string) $settings['trust_message'] !== ''
					? (string) $settings['trust_message']
					: __( 'Secure payment - Fast shipping - Easy returns', 'cro-toolkit' );
				$parts[] = '<div class="cro-cart-trust cro-blocks-trust"><p>' . esc_html( $msg ) . '</p></div>';
				if ( $visitor ) {
					$visitor->record_banner_show( 'trust' );
				}
			}
		}

		if ( ! empty( $settings['show_urgency'] ) ) {
			$can_show = ! $visitor || $visitor->can_show_banner( 'urgency', $max_24h );
			if ( $can_show ) {
				$msg   = isset( $settings['urgency_message'] ) && (string) $settings['urgency_message'] !== ''
					? (string) $settings['urgency_message']
					: __( 'Items in your cart are in high demand!', 'cro-toolkit' );
				$parts[] = '<div class="cro-cart-urgency cro-blocks-urgency"><p>' . esc_html( $msg ) . '</p></div>';
				if ( $visitor ) {
					$visitor->record_banner_show( 'urgency' );
				}
			}
		}

		if ( empty( $parts ) ) {
			return;
		}
		echo '<div class="cro-blocks-cart-prefix cro-cart-optimizer-block">' . implode( "\n", $parts ) . '</div>';
	}

	/**
	 * Render benefits list below the cart table (classic cart only; block cart uses CRO_Blocks_Integration).
	 */
	public function render_benefits_bottom() {
		if ( ! function_exists( 'cro_settings' ) || ! cro_settings()->is_feature_enabled( 'cart_optimizer' ) ) {
			return;
		}
		$settings = cro_settings()->get_cart_optimizer_settings();
		if ( empty( $settings['show_benefits'] ) ) {
			return;
		}
		$benefits = (array) ( $settings['benefits_list'] ?? array() );
		$benefits = array_filter( array_map( 'trim', $benefits ) );
		if ( empty( $benefits ) ) {
			return;
		}
		echo '<div class="cro-blocks-cart-suffix cro-cart-optimizer-block"><ul class="cro-cart-benefits cro-blocks-benefits"><li>' . implode( '</li><li>', array_map( 'esc_html', $benefits ) ) . '</li></ul></div>';
	}

	/**
	 * Enqueue exit-intent nudge script on cart/checkout when enabled. Once per session, mobile-safe.
	 */
	public function enqueue_exit_nudge() {
		if ( ! function_exists( 'is_cart' ) || ! function_exists( 'is_checkout' ) ) {
			return;
		}
		if ( ! is_cart() && ! is_checkout() ) {
			return;
		}
		if ( ! function_exists( 'cro_settings' ) || ! cro_settings()->is_feature_enabled( 'cart_optimizer' ) ) {
			return;
		}
		$settings = cro_settings()->get_cart_optimizer_settings();
		if ( empty( $settings['exit_intent_nudge'] ) ) {
			return;
		}

		$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '';
		if ( is_checkout() ) {
			$checkout_url = ''; // On checkout, CTA can scroll to place order or stay as-is; use # to close.
			$checkout_url = apply_filters( 'cro_exit_nudge_checkout_cta_url', '#', true );
		} else {
			$checkout_url = apply_filters( 'cro_exit_nudge_checkout_cta_url', $checkout_url, false );
		}

		wp_enqueue_script(
			'cro-cart-exit-nudge',
			defined( 'CRO_PLUGIN_URL' ) ? CRO_PLUGIN_URL . 'public/js/cro-cart-exit-nudge.js' : '',
			array(),
			defined( 'CRO_VERSION' ) ? CRO_VERSION : '1.0',
			true
		);

		$message = isset( $settings['exit_intent_message'] ) && (string) $settings['exit_intent_message'] !== ''
			? (string) $settings['exit_intent_message']
			: __( 'Complete your order now — your discount is ready', 'cro-toolkit' );
		$cta     = isset( $settings['exit_intent_cta'] ) && (string) $settings['exit_intent_cta'] !== ''
			? (string) $settings['exit_intent_cta']
			: __( 'Complete order', 'cro-toolkit' );

		wp_localize_script(
			'cro-cart-exit-nudge',
			'croCartExitNudgeConfig',
			array(
				'enabled'      => true,
				'message'      => $message,
				'ctaText'      => $cta,
				'checkoutUrl'  => $checkout_url ?: '#',
			)
		);

		wp_register_style( 'cro-cart-exit-nudge', false, array() );
		wp_enqueue_style( 'cro-cart-exit-nudge' );
		$css = '.cro-exit-nudge{position:fixed;inset:0;z-index:999999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.4);opacity:0;visibility:hidden;transition:opacity .25s ease,visibility .25s ease}.cro-exit-nudge--visible{opacity:1;visibility:visible}.cro-exit-nudge__box{position:relative;max-width:360px;margin:1rem;padding:1.5rem;background:#fff;border-radius:8px;box-shadow:0 4px 24px rgba(0,0,0,.15);text-align:center}.cro-exit-nudge__message{margin:0 0 1rem;font-size:1.1rem;line-height:1.4;color:#333}.cro-exit-nudge__cta{display:inline-block;margin-bottom:.5rem;padding:.6rem 1.2rem;font-size:1rem;text-decoration:none;color:#fff;background:#333;border-radius:4px;border:none;cursor:pointer}.cro-exit-nudge__cta:hover{color:#fff;background:#555}.cro-exit-nudge__close{position:absolute;top:.5rem;right:.5rem;width:32px;height:32px;padding:0;font-size:1.5rem;line-height:1;color:#666;background:none;border:none;cursor:pointer}.cro-exit-nudge__close:hover{color:#333}';
		wp_add_inline_style( 'cro-cart-exit-nudge', $css );
	}
}
