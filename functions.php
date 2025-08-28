<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// Disable Blocksy WooCommerce filters at earliest possible point
add_action( 'plugins_loaded', function () {
	if ( class_exists( '\Blocksy\Extensions\WoocommerceExtra\FiltersTaxonomiesProductsLookupTable' ) ) {
		remove_action( 'wp', [ \Blocksy\Extensions\WoocommerceExtra\FiltersTaxonomiesProductsLookupTable::instance(), 'maybe_setup_lookup_table' ] );
	}

	if ( class_exists( '\Blocksy\Extensions\WoocommerceExtra\ServiceProvider' ) ) {
		remove_all_actions( 'blocksy:woocommerce:filters:init' );
	}
}, 1 );

// Auto-merge workflow test: Add performance optimization for admin
add_action( 'admin_init', function() {
	// Optimize admin performance by reducing unnecessary queries
	if ( is_admin() && ! wp_doing_ajax() ) {
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	}
} );

// Fix REST API permissions for shop_coupon
add_filter( 'rest_pre_dispatch', function ($result, $server, $request) {
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
}, 10, 3 );

// Ensure proper REST API capabilities
add_filter( 'rest_shop_coupon_query', function ($args, $request) {
	if ( current_user_can( 'manage_woocommerce' ) ) {
		$args['post_status'] = array( 'publish', 'draft', 'pending' );
	}
	return $args;
}, 10, 2 );

// Add REST API support for shop_coupon
add_action( 'init', function () {
	add_post_type_support( 'shop_coupon', 'custom-fields' );
	global $wp_post_types;
	if ( isset( $wp_post_types['shop_coupon'] ) ) {
		$wp_post_types['shop_coupon']->show_in_rest          = true;
		$wp_post_types['shop_coupon']->rest_base             = 'shop_coupon';
		$wp_post_types['shop_coupon']->rest_controller_class = 'WP_REST_Posts_Controller';
	}
}, 11 );

// Add REST API endpoints support
add_filter( 'rest_endpoints', function ($endpoints) {
	if ( isset( $endpoints['/wp/v2/types/shop_coupon'] ) ) {
		$endpoints['/wp/v2/types/shop_coupon'][0]['permission_callback'] = function () {
			return current_user_can( 'manage_woocommerce' );
		};
	}
	return $endpoints;
} );

// Enqueue theme styles and scripts
include_once get_stylesheet_directory() . '/includes/scripts.php';

// fibo search customization
include_once get_stylesheet_directory() . '/includes/customization/fibo-search-suggestions.php';

// Thank you page customizations
include_once get_stylesheet_directory() . '/includes/customization/thank-you-page.php';

// My Account page customizations
include_once get_stylesheet_directory() . '/includes/customization/my-account.php';

// Disable terms and conditions validation completely using WooCommerce settings filter
add_filter( 'pre_option_woocommerce_terms_page_id', '__return_empty_string', 999 );


// Disable Blocksy WooCommerce filters on shop/archive pages
add_action( 'init', function () {
	if ( is_admin() )
		return;

	// Prevent the filters extension from initializing
	remove_all_actions( 'blocksy:woocommerce:filters:init' );

	// Remove expensive taxonomy filters from Blocksy Companion Pro
	remove_all_actions( 'blocksy:woocommerce:filters:product-taxonomies' );
	remove_all_actions( 'blocksy:woocommerce:filters:product-attributes' );
	remove_all_actions( 'blocksy:woocommerce:filters:product-price' );
}, 20 );


// Instrument Add to Cart AJAX for New Relic visibility
add_action( 'wc_ajax_add_to_cart', function () {
	if ( function_exists( 'newrelic_name_transaction' ) ) {
		newrelic_name_transaction( 'wc_ajax_add_to_cart' );
		newrelic_add_custom_parameter( 'source', 'manual_hook' );
	}
} );

// TO DO: FIX, NOT WORKING
// Enqueue checkout assets
// add_action( 'wp_enqueue_scripts', function() {
// 	if ( is_checkout() && ! is_wc_endpoint_url() ) {
// 		$template_uri = get_stylesheet_directory_uri();

// 		// Enqueue CSS
// 		wp_enqueue_style(
// 			'custom-checkout-css',
// 			$template_uri . '/assets/css/checkout.css',
// 			array(),
// 			'1.0.0'
// 		);

// 		// Enqueue JS
// 		wp_enqueue_script(
// 			'custom-checkout-js',
// 			$template_uri . '/assets/js/checkout.js',
// 			array( 'jquery' ),
// 			'1.0.0',
// 			true
// 		);
// 	}
// }, 20 );

/**
 * Register checkout sidebar widget area
 *
 * Creates a widget area that displays below the order summary
 * on WooCommerce checkout pages only. Provides enhanced styling
 * control with checkout-specific CSS classes.
 *
 * @since 1.0.0
 */
function blocksy_child_register_checkout_sidebar() {
    register_sidebar( array(
        'name'          => __( 'Checkout Sidebar', 'blocksy-child' ),
        'id'            => 'checkout-sidebar',
        'description'   => __( 'Widgets here will appear below the order summary on the checkout page.', 'blocksy-child' ),
        'before_widget' => '<div id="%1$s" class="widget checkout-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'blocksy_child_register_checkout_sidebar' );

/**
 * Display checkout sidebar widget area below the order summary
 *
 * Outputs the checkout sidebar widget area on WooCommerce checkout pages only.
 * Includes WooCommerce dependency check for enhanced error prevention and
 * conditional display logic to ensure widgets only appear when appropriate.
 *
 * @since 1.0.0
 */
function blocksy_child_checkout_sidebar_output() {
    // WooCommerce dependency check for enhanced error prevention
    if ( ! function_exists( 'is_checkout' ) ) {
        return;
    }

    if ( is_checkout() && ! is_wc_endpoint_url() ) {
        if ( is_active_sidebar( 'checkout-sidebar' ) ) {
            echo '<aside class="checkout-sidebar">';
            dynamic_sidebar( 'checkout-sidebar' );
            echo '</aside>';
        }
    }
}
add_action( 'woocommerce_checkout_after_order_review', 'blocksy_child_checkout_sidebar_output' );
