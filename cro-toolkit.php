<?php
/**
 * Plugin Name: CRO Toolkit for WooCommerce
 * Plugin URI: 
 * Description: Lightweight conversion rate optimization toolkit for WooCommerce - sticky CTAs, exit intent popups, cart optimizers, and checkout improvements.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: 
 * License: GPL v2 or later
 * Text Domain: cro-toolkit
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'CRO_VERSION', '1.0.0' );
define( 'CRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Error handler (load early so plugin errors are caught)
require_once CRO_PLUGIN_DIR . 'includes/class-cro-error-handler.php';

// Models
require_once CRO_PLUGIN_DIR . 'includes/models/class-cro-visitor-state.php';
require_once CRO_PLUGIN_DIR . 'includes/models/class-cro-context.php';
require_once CRO_PLUGIN_DIR . 'includes/models/class-cro-campaign-model.php';

// Engine
require_once CRO_PLUGIN_DIR . 'includes/engine/class-cro-rule-engine.php';
require_once CRO_PLUGIN_DIR . 'includes/engine/class-cro-intent-scorer.php';
require_once CRO_PLUGIN_DIR . 'includes/engine/class-cro-decision.php';
require_once CRO_PLUGIN_DIR . 'includes/engine/class-cro-decision-engine.php';

// A/B Testing
require_once CRO_PLUGIN_DIR . 'includes/ab-testing/class-cro-ab-test.php';
require_once CRO_PLUGIN_DIR . 'includes/ab-testing/class-cro-ab-statistics.php';

// Load textdomain at init priority 0 so WP 6.7+ does not trigger "translation too early" (translations must be at init or later).
add_action( 'init', function() {
	load_plugin_textdomain( 'cro-toolkit', false, dirname( CRO_PLUGIN_BASENAME ) . '/languages/' );
}, 0 );

add_action( 'plugins_loaded', function() {
	CRO_Visitor_State::get_instance();
}, 5 );

add_action( 'shutdown', function() {
	CRO_Visitor_State::get_instance()->save();
} );

// Persist "campaign shown" to visitor state (cookie) so frequency rules suppress on next request.
add_action( 'cro_campaign_shown_this_pageview', function( $campaign_id ) {
	$visitor = CRO_Visitor_State::get_instance();
	$visitor->record_campaign_shown( (int) $campaign_id );
	$visitor->save();
}, 10, 1 );

/**
 * Declare compatibility with WooCommerce HPOS (High-Performance Order Storage).
 * CRO Toolkit uses wc_get_order() and order CRUD APIs only, not post/postmeta.
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

// Load activator and database before activation hook so callback exists and create_tables() is available.
require_once CRO_PLUGIN_DIR . 'includes/class-cro-database.php';
require_once CRO_PLUGIN_DIR . 'includes/class-cro-activator.php';

register_activation_hook( __FILE__, array( 'CRO_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, 'deactivate_cro_toolkit' );

/**
 * Check if WooCommerce is active
 */
function cro_is_woocommerce_active() {
	return class_exists( 'WooCommerce' ) || is_plugin_active( 'woocommerce/woocommerce.php' );
}

/**
 * Show admin notice if WooCommerce is not active
 */
function cro_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'CRO Toolkit for WooCommerce requires WooCommerce to be installed and active.', 'cro-toolkit' ); ?></p>
	</div>
	<?php
}

/**
 * Prevent activation if WooCommerce is not active (called from CRO_Activator::activate).
 */
function cro_activation_check() {
	if ( ! cro_is_woocommerce_active() ) {
		deactivate_plugins( CRO_PLUGIN_BASENAME );
		wp_die(
			esc_html__( 'CRO Toolkit for WooCommerce requires WooCommerce to be installed and active. Please install WooCommerce first.', 'cro-toolkit' ),
			esc_html__( 'Plugin Activation Error', 'cro-toolkit' ),
			array( 'back_link' => true )
		);
	}
}

/**
 * Prevent WooCommerce deactivation while plugin is active
 */
function cro_prevent_woocommerce_deactivation( $plugin ) {
	if ( 'woocommerce/woocommerce.php' === $plugin && is_plugin_active( CRO_PLUGIN_BASENAME ) ) {
		wp_die(
			esc_html__( 'CRO Toolkit for WooCommerce requires WooCommerce. Please deactivate CRO Toolkit first.', 'cro-toolkit' ),
			esc_html__( 'Plugin Deactivation Error', 'cro-toolkit' ),
			array( 'back_link' => true )
		);
	}
}
add_action( 'deactivate_plugin', 'cro_prevent_woocommerce_deactivation', 10, 1 );

/**
 * Show admin notice if WooCommerce is missing
 */
function cro_admin_notices() {
	if ( ! cro_is_woocommerce_active() ) {
		add_action( 'admin_notices', 'cro_woocommerce_missing_notice' );
	}
}
add_action( 'admin_init', 'cro_admin_notices' );

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_cro_toolkit() {
	require_once CRO_PLUGIN_DIR . 'includes/class-cro-deactivator.php';
	CRO_Deactivator::deactivate();
}

/**
 * Check WooCommerce before loading plugin
 * Use plugins_loaded hook to ensure WooCommerce is loaded first
 */
function cro_init_plugin() {
	if ( ! cro_is_woocommerce_active() ) {
		return;
	}
	
	// Run DB upgrade routine on plugin load (for migrations after updates)
	require_once CRO_PLUGIN_DIR . 'includes/class-cro-activator.php';
	CRO_Activator::maybe_upgrade_tables();

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require CRO_PLUGIN_DIR . 'includes/class-cro-loader.php';

	/**
	 * Begins execution of the plugin.
	 */
	function run_cro_toolkit() {
		$plugin = new CRO_Loader();
		$plugin->run();
	}
	run_cro_toolkit();

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once CRO_PLUGIN_DIR . 'includes/class-cro-cli.php';
	}
}

// Run plugin at init priority 1 so textdomain (init 0) runs first and WooCommerce is ready (WP 6.7 "translation too early" + WC get_continents safe).
add_action( 'init', 'cro_init_plugin', 1 );

/**
 * Performance monitoring for debug mode
 */
if ( defined( 'CRO_DEBUG' ) && CRO_DEBUG ) {
	add_action( 'shutdown', function () {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$stats = array(
			'memory'  => class_exists( 'CRO_Resource_Manager' ) && method_exists( 'CRO_Resource_Manager', 'get_memory_stats' )
				? CRO_Resource_Manager::get_memory_stats()
				: array(),
			'time'    => class_exists( 'CRO_Resource_Manager' ) && method_exists( 'CRO_Resource_Manager', 'get_execution_time' )
				? CRO_Resource_Manager::get_execution_time()
				: 0,
			'cache'   => class_exists( 'CRO_Cache' ) && method_exists( 'CRO_Cache', 'get_stats' )
				? CRO_Cache::get_stats()
				: array(),
			'queries' => function_exists( 'get_num_queries' ) ? get_num_queries() : 0,
		);

		if ( class_exists( 'CRO_Error_Handler' ) && method_exists( 'CRO_Error_Handler', 'log' ) ) {
			CRO_Error_Handler::log( 'PERF', 'Request stats', $stats );
		}
	} );
}
