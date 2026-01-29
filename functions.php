<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

define( 'BLAZE_BLOCKSY_URL', get_stylesheet_directory_uri() );
define( 'BLAZE_BLOCKSY_PATH', get_stylesheet_directory() );
define( 'BLAZE_BLOCKSY_VERSION', wp_get_theme()->get( 'Version' ) );

/**
 * WOOLESS-8737: Fluid Checkout Spacing Fix
 *
 * Adds 20px bottom margin to Fluid Checkout progress bar and express checkout sections.
 * Uses wp_head hook to output inline CSS directly.
 *
 * @since 1.39.0
 */
function blaze_blocksy_fluid_checkout_spacing() {
	// Only load on checkout page
	if ( ! is_checkout() ) {
		return;
	}
	?>
	<style id="wooless-8737-fluid-checkout-spacing">
		/* WOOLESS-8737: Fluid Checkout Spacing */
		.fc-wrapper .fc-progress-bar,
		.woocommerce-checkout .fc-progress-bar {
			margin-bottom: 20px !important;
		}

		.fc-wrapper .fc-express-checkout,
		.woocommerce-checkout .fc-express-checkout {
			margin-bottom: 20px !important;
		}

		/* WOOLESS-8737: Amazon Pay Button Container Max-Width */
		.amazonpay-button-container {
			max-width: 100% !important;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'blaze_blocksy_fluid_checkout_spacing', 999 );

/**
 * Enqueue Fluid Checkout Mobile/Tablet Customizations
 *
 * Loads CSS and JavaScript for mobile/tablet checkout enhancements:
 * - Moves order summary sidebar above checkout form
 * - Adds collapsible toggle for order summary
 *
 * @since 1.40.0
 */
function blaze_blocksy_enqueue_checkout_mobile_assets() {
	// Only load on checkout page and when FluidCheckout is active
	if ( ! is_checkout() || ! class_exists( 'FluidCheckout' ) ) {
		return;
	}

	// Enqueue CSS
	wp_enqueue_style(
		'blaze-checkout-mobile',
		BLAZE_BLOCKSY_URL . '/assets/checkout-mobile.css',
		array(),
		'1.40.13',
		'all'
	);

	// Enqueue JavaScript
	wp_enqueue_script(
		'blaze-checkout-mobile',
		BLAZE_BLOCKSY_URL . '/assets/checkout-mobile.js',
		array( 'jquery' ),
		'1.40.13',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'blaze_blocksy_enqueue_checkout_mobile_assets', 999 );


// Disable Blocksy WooCommerce filters at earliest possible point
add_action(
	'plugins_loaded',
	function () {
		if ( class_exists( '\Blocksy\Extensions\WoocommerceExtra\FiltersTaxonomiesProductsLookupTable' ) ) {
			remove_action( 'wp', array( \Blocksy\Extensions\WoocommerceExtra\FiltersTaxonomiesProductsLookupTable::instance(), 'maybe_setup_lookup_table' ) );
		}

		if ( class_exists( '\Blocksy\Extensions\WoocommerceExtra\ServiceProvider' ) ) {
			remove_all_actions( 'blocksy:woocommerce:filters:init' );
		}
	},
	1
);

// Fix REST API permissions for shop_coupon
add_filter(
	'rest_pre_dispatch',
	function ( $result, $server, $request ) {
		$route = $request->get_route();
		if (
			strpos( $route, '/wp/v2/shop_coupon' ) !== false ||
			strpos( $route, '/wc/v3/coupons' ) !== false ||
			strpos( $route, '/wp/v2/types/shop_coupon' ) !== false
		) {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				return null; // Allow the request to proceed
			}
		}
		return $result;
	},
	10,
	3
);

// Ensure proper REST API capabilities
add_filter(
	'rest_shop_coupon_query',
	function ( $args, $request ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$args['post_status'] = array( 'publish', 'draft', 'pending' );
		}
		return $args;
	},
	10,
	2
);

// Add REST API support for shop_coupon
add_action(
	'init',
	function () {
		add_post_type_support( 'shop_coupon', 'custom-fields' );
		global $wp_post_types;
		if ( isset( $wp_post_types['shop_coupon'] ) ) {
			$wp_post_types['shop_coupon']->show_in_rest = true;
			$wp_post_types['shop_coupon']->rest_base = 'shop_coupon';
			$wp_post_types['shop_coupon']->rest_controller_class = 'WP_REST_Posts_Controller';
		}
	},
	11
);

// Add REST API endpoints support
add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		if ( isset( $endpoints['/wp/v2/types/shop_coupon'] ) ) {
			$endpoints['/wp/v2/types/shop_coupon'][0]['permission_callback'] = function () {
				return current_user_can( 'manage_woocommerce' );
			};
		}
		return $endpoints;
	}
);

// Enqueue theme styles and scripts with enhanced error handling
$required_files = [
	'/custom/custom.php',
	'/includes/scripts.php',
	'/includes/features/shipping.php',
	'/includes/features/product-information.php',
	'/includes/features/offcanvas-module.php', // Generic offcanvas module
	'/includes/customization/fibo-search-suggestions.php',
	'/includes/customization/thank-you-page.php',
	'/includes/customization/thank-you-page-customizer.php',
	'/includes/customization/my-account.php',
	'/includes/customization/judgeme.php',
	'/includes/customization/klaviyo-star-ratings.php',
	'/includes/customization/mini-cart.php',
	'/includes/customization/related-carousel.php',
	'/includes/customization/product-category.php',
	'/includes/customization/product-card.php',
	'/includes/customization/recently-viewed-products.php',
	'/includes/customization/wishlist/wishlist.php',
	'/includes/customization/single-product.php',
	'/includes/customization/mix-and-match-products.php',
	'/includes/customization/product-tabs.php',
	'/includes/customization/product-custom-tabs.php',
	'/includes/customization/product-stock.php',
	'/includes/customization/product-full-description.php',
	'/includes/customization/slideshow-on-mobile.php',
	'/includes/customization/bundle-products.php',
	'/includes/customization/free-shipping-offcanvas.php',
	'/includes/customization/results-count-placement.php',

	// Gutenberg Blocks
	'/includes/gutenberg/product-slider.php',
	'/includes/blocks/variation-swatches/index.php',
];

// Conditionally load Fluid Checkout files only if Fluid Checkout is active
// This prevents fatal errors if the Fluid Checkout plugin is deactivated
if ( class_exists( 'FluidCheckout' ) ) {
	$required_files[] = '/includes/customization/fluid-checkout-customizer.php';
	$required_files[] = '/includes/customization/fluid-checkout-field-labels.php';
	$required_files[] = '/includes/customization/fluid-checkout-fixes.php';
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
	error_log( 'BlazeCommerce: Fluid Checkout Customizer not loaded - FluidCheckout class not found. Please ensure Fluid Checkout Lite or Pro is installed and activated.' );
}

// Add debug files in debug mode
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	// $required_files[] = '/includes/debug/product-card-border-test.php';
	// $required_files[] = '/includes/debug/judgeme-tab-test.php';
	// Uncomment to enable notification offcanvas example
	// $required_files[] = '/includes/features/notification-offcanvas-example.php';
}

foreach ( $required_files as $file ) {
	$file_path = BLAZE_BLOCKSY_PATH . $file;
	if ( file_exists( $file_path ) && is_readable( $file_path ) ) {
		try {
			require_once $file_path;
		} catch (Error $e) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( 'BlazeCommerce: Failed to load ' . $file . ': ' . $e->getMessage() );
			}
		} catch (Exception $e) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( 'BlazeCommerce: Exception loading ' . $file . ': ' . $e->getMessage() );
			}
		}
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		error_log( 'BlazeCommerce: File not found: ' . $file_path );
	}
}

// Disable Blocksy WooCommerce filters on shop/archive pages
add_action(
	'init',
	function () {
		if ( is_admin() ) {
			return;
		}

		// Prevent the filters extension from initializing
		remove_all_actions( 'blocksy:woocommerce:filters:init' );

		// Remove expensive taxonomy filters from Blocksy Companion Pro
		remove_all_actions( 'blocksy:woocommerce:filters:product-taxonomies' );
		remove_all_actions( 'blocksy:woocommerce:filters:product-attributes' );
		remove_all_actions( 'blocksy:woocommerce:filters:product-price' );
	},
	20
);


// Instrument Add to Cart AJAX for New Relic visibility
add_action(
	'wc_ajax_add_to_cart',
	function () {
		if ( function_exists( 'newrelic_name_transaction' ) ) {
			newrelic_name_transaction( 'wc_ajax_add_to_cart' );
			newrelic_add_custom_parameter( 'source', 'manual_hook' );
		}
	}
);

/**
 * Safely check if a plugin is active without including admin files.
 *
 * @param string $plugin Plugin path.
 * @return bool Whether plugin is active.
 * @since 1.0.0
 */
function blaze_blocksy_is_plugin_active( $plugin ) {
	// First try the WordPress function if available
	if ( function_exists( 'is_plugin_active' ) ) {
		return is_plugin_active( $plugin );
	}

	// Fallback: Check if plugin is in active plugins option
	$active_plugins = get_option( 'active_plugins', array() );
	if ( in_array( $plugin, $active_plugins, true ) ) {
		return true;
	}

	// Check network activation if multisite
	if ( is_multisite() ) {
		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
		return array_key_exists( $plugin, $network_plugins );
	}

	return false;
}

/**
 * Override WooCommerce templates

 */
add_filter( 'woocommerce_locate_template', function ( $template, $template_name ) {
	// override woocommerce template if file exists
	$custom_template = BLAZE_BLOCKSY_PATH . '/woocommerce/' . $template_name;

	if ( file_exists( $custom_template ) ) {
		return $custom_template;
	}

	return $template;

}, 999, 2 );
