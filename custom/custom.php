<?php
/**
 * Site-specific custom functions loader.
 * Loaded by functions.php. All custom PHP modules must be required here.
 *
 * This file is tracked in git as a base template. Per-deployment
 * customizations (require_once lines, enqueue calls) are added here.
 *
 * @package Blaze_Commerce
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'custom-style', BLAZE_BLOCKSY_URL . '/custom/product.css', array(), '1.0.0' );
	wp_enqueue_style( 'custom-minicart', BLAZE_BLOCKSY_URL . '/custom/minicart.css', array( 'blaze-blocksy-mini-cart' ), '1.0.0' );

	// Header Figma-exact styles (Task: 86evcm56n)
	wp_enqueue_style( 'custom-header', BLAZE_BLOCKSY_URL . '/custom/header/header.css', array(), '1.26.0' );
	wp_enqueue_style( 'custom-header-search', BLAZE_BLOCKSY_URL . '/custom/header/header-search.css', array(), '1.11.0' );
	wp_enqueue_script( 'header-carousel', BLAZE_BLOCKSY_URL . '/custom/header/carousel.js', array(), '1.0.0', true );

	// Checkout Figma-exact styles (Task: 86evcm57c) - only on checkout page
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		wp_enqueue_style( 'custom-checkout', BLAZE_BLOCKSY_URL . '/custom/checkout.css', array(), '1.2.4' );
		wp_enqueue_style( 'custom-header-checkout', BLAZE_BLOCKSY_URL . '/custom/header/header-checkout.css', array(), '1.0.6' );
	}

	// Enqueue WooCommerce Extra extension styles if not already loaded (Pro license inactive)
	if ( ! wp_style_is( 'blocksy-ext-woocommerce-extra-styles', 'enqueued' ) ) {
		$ext_css = WP_PLUGIN_DIR . '/blocksy-companion-pro/framework/premium/extensions/woocommerce-extra/static/bundle/main.min.css';
		if ( file_exists( $ext_css ) ) {
			wp_enqueue_style(
				'blocksy-ext-woocommerce-extra-styles',
				plugins_url( 'blocksy-companion-pro/framework/premium/extensions/woocommerce-extra/static/bundle/main.min.css' ),
				array( 'ct-main-styles' ),
				'2.0.0'
			);
		}
	}
} );

require_once( 'fibo-search-overrides.php' );
require_once( 'menu-locations.php' );
require_once( 'mega-menu.php' );
require_once( 'offcanvas-bottom-links.php' );
require_once( 'mobile-header-rows.php' );
require_once( 'registration-form.php' );
require_once( 'cart-panel.php' );
require_once( 'info-dropdown.php' );
require_once( 'footer-tweaks.php' );
require_once( 'header-icons.php' );

require_once( 'divi/divi-custom.php' );
require_once( 'homepage/homepage.php' );
require_once( 'checkout/checkout-trust-badges.php' );
require_once( 'hide-cart-page.php' );
