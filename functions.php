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

// Enqueue custom checkout styles and scripts
function infinity_targets_checkout_assets() {
    // Only load on checkout page
    if ( is_checkout() && ! is_wc_endpoint_url() ) {
        // Enqueue custom checkout CSS
        wp_enqueue_style(
            'infinity-checkout-custom',
            get_stylesheet_directory_uri() . '/assets/css/checkout-custom.css',
            array( 'woocommerce-general' ),
            filemtime( get_stylesheet_directory() . '/assets/css/checkout-custom.css' )
        );

        // Enqueue mobile-responsive checkout CSS
        wp_enqueue_style(
            'infinity-checkout-mobile',
            get_stylesheet_directory_uri() . '/assets/css/checkout-mobile.css',
            array( 'infinity-checkout-custom' ),
            filemtime( get_stylesheet_directory() . '/assets/css/checkout-mobile.css' )
        );

        // Enqueue custom checkout JavaScript
        wp_enqueue_script(
            'infinity-checkout-custom',
            get_stylesheet_directory_uri() . '/assets/js/checkout-custom.js',
            array( 'jquery', 'wc-checkout' ),
            filemtime( get_stylesheet_directory() . '/assets/js/checkout-custom.js' ),
            true
        );

        // Enqueue checkout optimization JavaScript
        wp_enqueue_script(
            'infinity-checkout-optimization',
            get_stylesheet_directory_uri() . '/assets/js/checkout-optimization.js',
            array( 'jquery', 'infinity-checkout-custom' ),
            filemtime( get_stylesheet_directory() . '/assets/js/checkout-optimization.js' ),
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'infinity_targets_checkout_assets', 20 );

// Customize checkout field labels and placeholders to match Figma design
function infinity_targets_checkout_fields( $fields ) {
    // Customer Information section
    if ( isset( $fields['billing']['billing_email'] ) ) {
        $fields['billing']['billing_email']['label'] = 'Email Address';
        $fields['billing']['billing_email']['placeholder'] = 'Enter your email address';
        $fields['billing']['billing_email']['priority'] = 10;
    }

    // Billing fields customization
    if ( isset( $fields['billing']['billing_first_name'] ) ) {
        $fields['billing']['billing_first_name']['label'] = 'First Name';
        $fields['billing']['billing_first_name']['placeholder'] = 'Enter your first name';
    }

    if ( isset( $fields['billing']['billing_last_name'] ) ) {
        $fields['billing']['billing_last_name']['label'] = 'Last Name';
        $fields['billing']['billing_last_name']['placeholder'] = 'Enter your last name';
    }

    if ( isset( $fields['billing']['billing_phone'] ) ) {
        $fields['billing']['billing_phone']['label'] = 'Phone Number';
        $fields['billing']['billing_phone']['placeholder'] = 'Enter your phone number';
    }

    if ( isset( $fields['billing']['billing_address_1'] ) ) {
        $fields['billing']['billing_address_1']['label'] = 'Street Address';
        $fields['billing']['billing_address_1']['placeholder'] = 'Enter your street address';
    }

    if ( isset( $fields['billing']['billing_city'] ) ) {
        $fields['billing']['billing_city']['label'] = 'City';
        $fields['billing']['billing_city']['placeholder'] = 'Enter your city';
    }

    if ( isset( $fields['billing']['billing_state'] ) ) {
        $fields['billing']['billing_state']['label'] = 'State';
    }

    if ( isset( $fields['billing']['billing_postcode'] ) ) {
        $fields['billing']['billing_postcode']['label'] = 'ZIP Code';
        $fields['billing']['billing_postcode']['placeholder'] = 'Enter your ZIP code';
    }

    // Shipping fields customization
    if ( isset( $fields['shipping'] ) ) {
        foreach ( $fields['shipping'] as $key => $field ) {
            if ( strpos( $key, 'shipping_' ) === 0 ) {
                $billing_key = str_replace( 'shipping_', 'billing_', $key );
                if ( isset( $fields['billing'][$billing_key] ) ) {
                    $fields['shipping'][$key]['label'] = $fields['billing'][$billing_key]['label'];
                    $fields['shipping'][$key]['placeholder'] = $fields['billing'][$billing_key]['placeholder'];
                }
            }
        }
    }

    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'infinity_targets_checkout_fields' );

// Customize checkout section titles
function infinity_targets_checkout_section_titles() {
    // Change section titles to match Figma design
    add_filter( 'woocommerce_checkout_fields', function( $fields ) {
        // This will be handled by our custom CSS and JavaScript
        return $fields;
    });
}
add_action( 'init', 'infinity_targets_checkout_section_titles' );

// Remove default checkout fields that don't match Figma design
function infinity_targets_remove_checkout_fields( $fields ) {
    // Remove company field by default (can be made optional)
    unset( $fields['billing']['billing_company'] );
    unset( $fields['shipping']['shipping_company'] );

    // Remove address line 2 by default (can be made optional)
    unset( $fields['billing']['billing_address_2'] );
    unset( $fields['shipping']['shipping_address_2'] );

    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'infinity_targets_remove_checkout_fields' );

// Customize checkout button text
function infinity_targets_checkout_button_text() {
    return 'Complete Your Order';
}
add_filter( 'woocommerce_order_button_text', 'infinity_targets_checkout_button_text' );

// Add custom checkout scripts and styles inline for immediate effect
function infinity_targets_checkout_inline_styles() {
    if ( is_checkout() && ! is_wc_endpoint_url() ) {
        ?>
        <style>
        /* Immediate styling to prevent flash of unstyled content */
        .woocommerce-checkout {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .woocommerce-checkout.loaded {
            opacity: 1;
        }

        /* Hide default WooCommerce styling that conflicts */
        .woocommerce .col2-set .col-1,
        .woocommerce .col2-set .col-2 {
            float: none !important;
            width: auto !important;
        }

        .woocommerce .col2-set {
            width: 100% !important;
        }

        /* Ensure our custom layout takes precedence */
        .infinity-checkout-wrapper {
            position: relative;
            z-index: 10;
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add loaded class to prevent flash of unstyled content
            setTimeout(function() {
                document.querySelector('.woocommerce-checkout').classList.add('loaded');
            }, 100);
        });
        </script>
        <?php
    }
}
add_action( 'wp_head', 'infinity_targets_checkout_inline_styles' );

// Customize WooCommerce checkout process
function infinity_targets_checkout_process_customization() {
    // Add custom validation messages
    add_filter( 'woocommerce_checkout_required_field_notice', function( $notice, $field_label ) {
        return sprintf( 'Please enter your %s to continue.', strtolower( $field_label ) );
    }, 10, 2 );

    // Customize email validation message
    add_filter( 'woocommerce_checkout_email_validation_error', function( $message ) {
        return 'Please enter a valid email address to receive order updates.';
    });
}
add_action( 'init', 'infinity_targets_checkout_process_customization' );


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