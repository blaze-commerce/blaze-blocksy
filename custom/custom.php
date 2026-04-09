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

// add_action( 'wp_enqueue_scripts', function () {
// 	wp_enqueue_style( 'custom-style', BLAZE_BLOCKSY_URL . '/custom/product.css', array(), '1.0.0' );
// 	wp_enqueue_style( 'custom-minicart', BLAZE_BLOCKSY_URL . '/custom/minicart.css', array( 'blaze-blocksy-mini-cart' ), '1.0.0' );

// } );

require_once __DIR__ . '/default-sorting.php';
require_once __DIR__ . '/floating-cart-buy-now.php';
require_once __DIR__ . '/auto-select-variant.php';
require_once __DIR__ . '/variation-gallery-images.php';

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_product() ) {
		return;
	}

	$dir = get_stylesheet_directory() . '/custom';
	$uri = get_stylesheet_directory_uri() . '/custom';

	wp_enqueue_script( 'blaze-custom-product-info-tabs', "$uri/js/product-info-tabs.js", [], filemtime( "$dir/js/product-info-tabs.js" ), true );
	wp_enqueue_script( 'blaze-custom-variation-gallery', "$uri/js/variation-gallery-images.js", [ 'jquery' ], filemtime( "$dir/js/variation-gallery-images.js" ), true );
} );