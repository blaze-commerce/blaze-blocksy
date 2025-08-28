<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// Load security hardening functions with enhanced error handling
$security_file = get_stylesheet_directory() . '/security-fixes/security-hardening.php';
if ( file_exists( $security_file ) && is_readable( $security_file ) ) {
	try {
		require_once $security_file;
	} catch ( Error $e ) {
		// Log error but don't break the site
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'BlazeCommerce: Failed to load security hardening: ' . $e->getMessage() );
		}
	}
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( 'BlazeCommerce: Security hardening file not found or not readable: ' . $security_file );
}

// Load performance enhancement functions with enhanced error handling
$performance_file = get_stylesheet_directory() . '/performance-optimizations/performance-enhancements.php';
if ( file_exists( $performance_file ) && is_readable( $performance_file ) ) {
	try {
		require_once $performance_file;
	} catch ( Error $e ) {
		// Log error but don't break the site
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'BlazeCommerce: Failed to load performance enhancements: ' . $e->getMessage() );
		}
	}
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( 'BlazeCommerce: Performance enhancement file not found or not readable: ' . $performance_file );
}

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

// Auto-merge workflow test: Add performance optimization for admin
add_action( 'admin_init', function() {
	// Optimize admin performance by reducing unnecessary queries
	if ( is_admin() && ! wp_doing_ajax() ) {
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	}
} );

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
			$wp_post_types['shop_coupon']->show_in_rest          = true;
			$wp_post_types['shop_coupon']->rest_base             = 'shop_coupon';
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
$scripts_file = get_stylesheet_directory() . '/includes/scripts.php';
if ( file_exists( $scripts_file ) && is_readable( $scripts_file ) ) {
	try {
		require_once $scripts_file;
	} catch ( Error $e ) {
		// Log error but don't break the site
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'BlazeCommerce: Failed to load scripts file: ' . $e->getMessage() );
		}
	}
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( 'BlazeCommerce: Scripts file not found or not readable: ' . $scripts_file );
}

// Fibo search customization with enhanced error handling
$fibo_search_file = get_stylesheet_directory() . '/includes/customization/fibo-search-suggestions.php';
if ( file_exists( $fibo_search_file ) && is_readable( $fibo_search_file ) ) {
	try {
		require_once $fibo_search_file;
	} catch ( Error $e ) {
		// Log error but don't break the site
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'BlazeCommerce: Failed to load fibo search customization: ' . $e->getMessage() );
		}
	}
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( 'BlazeCommerce: Fibo search file not found or not readable: ' . $fibo_search_file );
}

// Thank you page customizations with enhanced error handling
$thank_you_file = get_stylesheet_directory() . '/includes/customization/thank-you-page.php';
if ( file_exists( $thank_you_file ) && is_readable( $thank_you_file ) ) {
	try {
		require_once $thank_you_file;
	} catch ( Error $e ) {
		// Log error but don't break the site
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'BlazeCommerce: Failed to load thank you page customization: ' . $e->getMessage() );
		}
	}
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( 'BlazeCommerce: Thank you page file not found or not readable: ' . $thank_you_file );
}

// My Account page customizations with enhanced error handling
$my_account_file = get_stylesheet_directory() . '/includes/customization/my-account.php';
if ( file_exists( $my_account_file ) && is_readable( $my_account_file ) ) {
	try {
		require_once $my_account_file;
	} catch ( Error $e ) {
		// Log error but don't break the site
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'BlazeCommerce: Failed to load my account customization: ' . $e->getMessage() );
		}
	}
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( 'BlazeCommerce: My account file not found or not readable: ' . $my_account_file );
}

// Load security and performance improvements
$security_performance_file = get_stylesheet_directory() . '/includes/security-performance-improvements.php';
if ( file_exists( $security_performance_file ) && is_readable( $security_performance_file ) ) {
	try {
		require_once $security_performance_file;
	} catch ( Error $e ) {
		// Log error but don't break the site
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'BlazeCommerce: Failed to load security performance improvements: ' . $e->getMessage() );
		}
	}
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( 'BlazeCommerce: Security performance file not found or not readable: ' . $security_performance_file );
}

// Disable terms and conditions validation completely using WooCommerce settings filter
add_filter( 'pre_option_woocommerce_terms_page_id', '__return_empty_string', 999 );

// BlazeCommerce Security Configuration Filters
// These filters allow customization of security features without modifying core security files

/**
 * Configure whitelisted IPs for automation and monitoring systems
 * Add trusted IPs that should bypass login attempt limiting
 */
add_filter( 'blaze_commerce_whitelisted_ips', function( $ips ) {
	// Add your trusted IPs here
	$trusted_ips = [
		// Example: Monitoring services
		// '192.168.1.100',
		// '10.0.0.50',
		// 'YOUR_MONITORING_SERVER_IP',
		// 'YOUR_CI_CD_SYSTEM_IP'
	];

	// Allow environment-specific configuration
	if ( defined( 'BLAZE_COMMERCE_TRUSTED_IPS' ) ) {
		$env_ips = explode( ',', BLAZE_COMMERCE_TRUSTED_IPS );
		$trusted_ips = array_merge( $trusted_ips, array_map( 'trim', $env_ips ) );
	}

	return array_merge( $ips, $trusted_ips );
} );

/**
 * Configure Content Security Policy
 * Disable CSP if it conflicts with your plugins
 */
add_filter( 'blaze_commerce_enable_csp', function( $enabled ) {
	// Disable CSP in development or if conflicts detected
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		// You can disable CSP in development if needed
		// return false;
	}

	// Check for known conflicting plugins
	$conflicting_plugins = [
		'elementor/elementor.php',
		'js_composer/js_composer.php', // WPBakery
		'revslider/revslider.php',
	];

	foreach ( $conflicting_plugins as $plugin ) {
		if ( is_plugin_active( $plugin ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( "BlazeCommerce: CSP disabled due to conflicting plugin: $plugin" );
			}
			return false;
		}
	}

	return $enabled;
} );

/**
 * Customize CSP sources for plugin compatibility
 */
add_filter( 'blaze_commerce_csp_sources', function( $sources ) {
	// Add additional sources if needed for your plugins

	// Example: Add PayPal for WooCommerce
	if ( class_exists( 'WooCommerce' ) ) {
		$sources['paypal'] = '*.paypal.com *.paypalobjects.com';
		$sources['stripe'] = '*.stripe.com';
	}

	// Example: Add Google Fonts if used by theme
	$sources['fonts'] = 'fonts.googleapis.com fonts.gstatic.com';

	return $sources;
} );

/**
 * Force enable login limiting even with security plugin conflicts
 * Use this if you want to override conflict detection
 */
add_filter( 'blaze_commerce_force_login_limiting', function( $force ) {
	// Enable this if you want to force login limiting despite detected conflicts
	// return true;

	return $force;
} );


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

// Include Blaze Commerce Progressive 3-Step Checkout (if file exists)
$blaze_checkout_file = get_stylesheet_directory() . '/includes/blaze-commerce-checkout.php';
if ( file_exists( $blaze_checkout_file ) ) {
    require_once $blaze_checkout_file;
}
