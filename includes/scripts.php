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
	// Enqueue blockUI if WooCommerce doesn't provide it
	if ( ! wp_script_is( 'jquery-blockui', 'enqueued' ) && ! wp_script_is( 'wc-checkout', 'enqueued' ) ) {
		wp_enqueue_script(
			'jquery-blockui',
			'https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js',
			array( 'jquery' ),
			'2.70',
			true
		);
	}

	// === OWL CAROUSEL ASSETS ===
	// Load Owl Carousel on product pages or pages with product carousel block
	if ( is_product() || has_block( 'blaze-blocksy/product-carousel' ) ) {
		wp_enqueue_style( 'owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css' );
		wp_enqueue_style( 'owl-theme-default', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css', array( 'owl-carousel' ) );
		wp_enqueue_script( 'owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', array( 'jquery' ), null, true );
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
			'ajax_url' => admin_url( 'admin-ajax.php' )
		) );

		wp_localize_script( 'blaze-blocksy-single-product', 'blazeBlocksySingleProduct', $single_product_data );
	}
}

/**
 * Enqueue customizer scripts for wishlist off-canvas sync
 */
add_action( 'customize_preview_init', function () {
	$template_uri = get_stylesheet_directory_uri();

	// Enqueue the main sync handler
	wp_enqueue_script(
		'wishlist-offcanvas-sync',
		$template_uri . '/assets/js/wishlist-offcanvas-sync.js',
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

