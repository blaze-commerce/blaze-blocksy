<?php

// Ensure is_plugin_active() function is available
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * Main enqueue scripts and styles handler
 */
add_action( 'wp_enqueue_scripts', 'blaze_blocksy_enqueue_assets' );

function blaze_blocksy_enqueue_assets() {
	$template_uri = get_stylesheet_directory_uri();

	// === GLOBAL STYLES ===
	wp_enqueue_style( 'parent-style', $template_uri . '/style.css' );

	// Enqueue child styles
	wp_enqueue_style(
		'blocksy-child-search-style',
		$template_uri . '/assets/css/search.css',
		array( 'parent-style' )
	);

	wp_enqueue_style(
		'blocksy-child-footer-style',
		$template_uri . '/assets/css/footer.css',
		array( 'parent-style' )
	);

	wp_enqueue_style(
		'blocksy-child-header-style',
		$template_uri . '/assets/css/header.css',
		array( 'parent-style' )
	);

	wp_enqueue_style(
		'blocksy-child-product-card-style',
		$template_uri . '/assets/css/product-card.css',
		array( 'parent-style' )
	);

	// === MINI CART ASSETS ===
	wp_enqueue_style( 'blaze-blocksy-mini-cart', BLAZE_BLOCKSY_URL . '/assets/css/mini-cart.css' );
	wp_enqueue_script( 'blaze-blocksy-mini-cart-js', BLAZE_BLOCKSY_URL . '/assets/js/mini-cart.js', array( 'jquery' ), '1.0.0', true );

	// === BLOCKUI LIBRARY ===
	// Use WooCommerce's bundled blockUI if available, fallback to local file
	if ( ! wp_script_is( 'jquery-blockui', 'enqueued' ) ) {
		if ( wp_script_is( 'jquery-blockui', 'registered' ) ) {
			// WooCommerce has already registered it, just enqueue
			wp_enqueue_script( 'jquery-blockui' );
		} else {
			// Fallback to local file if WooCommerce's script is not available
			wp_enqueue_script(
				'jquery-blockui',
				$template_uri . '/assets/vendor/jquery.blockUI.min.js',
				array( 'jquery' ),
				'2.70',
				true
			);
		}
	}

	// === OWL CAROUSEL ASSETS ===
	// Load Owl Carousel on product pages or pages with product carousel block (using local files)
	if ( is_product() || has_block( 'blaze-blocksy/product-carousel' ) ) {
		wp_enqueue_style( 'owl-carousel', $template_uri . '/assets/vendor/owlcarousel/owl.carousel.min.css', array(), '2.3.4' );
		wp_enqueue_style( 'owl-theme-default', $template_uri . '/assets/vendor/owlcarousel/owl.theme.default.min.css', array( 'owl-carousel' ), '2.3.4' );
		wp_enqueue_script( 'owl-carousel', $template_uri . '/assets/vendor/owlcarousel/owl.carousel.min.js', array( 'jquery' ), '2.3.4', true );
	}

	// === SINGLE PRODUCT PAGE ASSETS ===
	if ( is_product() ) {
		// Single product styles
		wp_enqueue_style( 'blaze-blocksy-single-product', BLAZE_BLOCKSY_URL . '/assets/css/single-product.css' );

		// Single product JavaScript
		wp_enqueue_script(
			'blaze-blocksy-single-product',
			BLAZE_BLOCKSY_URL . '/assets/js/single-product.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);
	}

	// === LOCALIZE SCRIPTS ===
	blaze_blocksy_localize_scripts();
}

/**
 * Centralized script localization with filter hooks
 */
function blaze_blocksy_localize_scripts() {
	// Mini Cart localization
	$mini_cart_data = apply_filters( 'blaze_blocksy_mini_cart_localize_data', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'blaze_blocksy_mini_cart_nonce' ),
		'applying_coupon' => __( 'Applying...', 'blaze-blocksy' ),
		'apply_coupon' => __( 'APPLY COUPON', 'blaze-blocksy' )
	) );

	wp_localize_script( 'blaze-blocksy-mini-cart-js', 'blazeBlocksyMiniCart', $mini_cart_data );

	// Single Product localization (only on product pages)
	if ( is_product() ) {
		$single_product_data = apply_filters( 'blaze_blocksy_single_product_localize_data', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'scrollOffsetPadding' => -60, // Extra padding for scroll-to-notice calculations
		) );

		wp_localize_script( 'blaze-blocksy-single-product', 'blazeBlocksySingleProduct', $single_product_data );
	}
}

/**
 * Enqueue customizer scripts for wishlist off-canvas sync
 */
add_action( 'customize_preview_init', function () {
	$template_uri = get_stylesheet_directory_uri();

	// Enqueue the main sync handler (using consolidated wishlist-offcanvas.js)
	wp_enqueue_script(
		'wishlist-offcanvas-sync',
		$template_uri . '/assets/js/wishlist-offcanvas.js',
		array( 'jquery', 'customize-preview' ),
		'1.0.0',
		true
	);

	// Enqueue the Blocksy variables integration
	wp_enqueue_script(
		'wishlist-offcanvas-variables',
		$template_uri . '/assets/js/wishlist-offcanvas-variables.js',
		array( 'jquery', 'customize-preview', 'wishlist-offcanvas-sync' ),
		'1.0.0',
		true
	);
} );

