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

// Disable WooCommerce terms and conditions server-side validation
add_action( 'woocommerce_checkout_process', 'bypass_terms_and_conditions_validation', 1 );
function bypass_terms_and_conditions_validation() {
	// Automatically set the terms checkbox as accepted in the POST data
	// This bypasses the server-side validation while maintaining other checkout validations
	if ( ! isset( $_POST['terms'] ) || empty( $_POST['terms'] ) ) {
		$_POST['terms'] = '1';
	}
}

// Additional hook to ensure terms validation is bypassed for all payment methods
add_action( 'woocommerce_before_checkout_process', 'ensure_terms_accepted', 1 );
function ensure_terms_accepted() {
	// Force terms acceptance in POST data before any validation occurs
	$_POST['terms'] = '1';
}

// Remove terms validation from WooCommerce checkout fields
add_filter( 'woocommerce_checkout_fields', 'remove_terms_validation_from_checkout_fields', 999 );
function remove_terms_validation_from_checkout_fields( $fields ) {
	// Remove terms field validation if it exists
	if ( isset( $fields['order']['terms'] ) ) {
		unset( $fields['order']['terms'] );
	}
	return $fields;
}

// Disable terms and conditions validation completely using WooCommerce settings filter
add_filter( 'pre_option_woocommerce_terms_page_id', '__return_empty_string', 999 );

// Alternative approach: Remove terms validation from WooCommerce form handler
add_action( 'init', 'disable_woocommerce_terms_validation', 999 );
function disable_woocommerce_terms_validation() {
	// Remove the terms validation from WooCommerce form handler
	remove_action( 'woocommerce_checkout_process', array( 'WC_Form_Handler', 'checkout_action' ), 20 );

	// Add our own checkout action without terms validation
	add_action( 'woocommerce_checkout_process', 'custom_checkout_process_without_terms', 20 );
}

function custom_checkout_process_without_terms() {
	// Force terms to be accepted in the POST data
	$_POST['terms'] = '1';

	// Let WooCommerce handle the rest of the checkout process normally
	// but with terms already set as accepted
}



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