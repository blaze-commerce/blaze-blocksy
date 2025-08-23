<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// Debug: Check if functions.php is being loaded
error_log( 'BLOCKSY CHILD: functions.php loaded' );

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

// Register Blaze Checkout Gutenberg Block
add_action( 'init', 'register_blaze_checkout_block' );
function register_blaze_checkout_block() {
	// Debug: Check if function is called
	error_log( 'Blaze Checkout: register_blaze_checkout_block() called' );

	$block_path = get_stylesheet_directory() . '/blocks/blaze-checkout';
	error_log( 'Blaze Checkout: Block path: ' . $block_path );

	// Check if block.json exists
	if ( file_exists( $block_path . '/block.json' ) ) {
		error_log( 'Blaze Checkout: block.json found' );

		// Register the block
		$result = register_block_type( $block_path );

		if ( $result ) {
			error_log( 'Blaze Checkout: Block registered successfully' );
		} else {
			error_log( 'Blaze Checkout: Block registration failed' );
		}
	} else {
		error_log( 'Blaze Checkout: block.json NOT found at ' . $block_path );
	}
}

// Register Blaze Commerce Block Category
add_action( 'block_categories_all', 'register_blaze_commerce_block_category' );
function register_blaze_commerce_block_category( $categories ) {
	// Debug: Check if function is called
	error_log( 'Blaze Commerce: register_blaze_commerce_block_category() called' );

	// Check if category already exists
	foreach ( $categories as $category ) {
		if ( $category['slug'] === 'blaze-commerce' ) {
			error_log( 'Blaze Commerce: Category already exists' );
			return $categories;
		}
	}

	// Add the Blaze Commerce category
	$categories[] = array(
		'slug'  => 'blaze-commerce',
		'title' => __( 'Blaze Commerce', 'blocksy-child' ),
		'icon'  => 'cart',
	);

	error_log( 'Blaze Commerce: Category registered successfully' );
	return $categories;
}

// Add custom block category for Blaze Commerce
add_filter( 'block_categories_all', 'add_blaze_commerce_block_category', 10, 2 );
function add_blaze_commerce_block_category( $categories, $post ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'blaze-commerce',
				'title' => __( 'Blaze Commerce', 'blocksy-child' ),
				'icon'  => 'cart',
			),
		)
	);
}

// Include Blaze Checkout Block helper functions
include_once get_stylesheet_directory() . '/blocks/blaze-checkout/includes/helper-functions.php';

// AJAX handlers for Blaze Checkout Block
add_action( 'wp_ajax_blaze_checkout_login', 'blaze_checkout_handle_login' );
add_action( 'wp_ajax_nopriv_blaze_checkout_login', 'blaze_checkout_handle_login' );
add_action( 'wp_ajax_blaze_checkout_register', 'blaze_checkout_handle_register' );
add_action( 'wp_ajax_nopriv_blaze_checkout_register', 'blaze_checkout_handle_register' );

function blaze_checkout_handle_login() {
	check_ajax_referer( 'blaze_checkout_nonce', 'nonce' );

	$username = sanitize_text_field( $_POST['username'] );
	$password = sanitize_text_field( $_POST['password'] );

	$creds = array(
		'user_login'    => $username,
		'user_password' => $password,
		'remember'      => true
	);

	$user = wp_signon( $creds, false );

	if ( is_wp_error( $user ) ) {
		wp_send_json_error( array( 'message' => $user->get_error_message() ) );
	} else {
		wp_send_json_success( array( 'message' => __( 'Login successful!', 'blocksy-child' ) ) );
	}
}

function blaze_checkout_handle_register() {
	check_ajax_referer( 'blaze_checkout_nonce', 'nonce' );

	$first_name = sanitize_text_field( $_POST['first_name'] );
	$last_name = sanitize_text_field( $_POST['last_name'] );
	$email = sanitize_email( $_POST['email'] );
	$password = sanitize_text_field( $_POST['password'] );

	if ( empty( $email ) || empty( $password ) ) {
		wp_send_json_error( array( 'message' => __( 'Email and password are required.', 'blocksy-child' ) ) );
	}

	if ( email_exists( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'An account with this email already exists.', 'blocksy-child' ) ) );
	}

	$user_id = wp_create_user( $email, $password, $email );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
	} else {
		// Update user meta
		wp_update_user( array(
			'ID' => $user_id,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'display_name' => $first_name . ' ' . $last_name
		) );

		// Auto login
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		wp_send_json_success( array( 'message' => __( 'Registration successful!', 'blocksy-child' ) ) );
	}
}