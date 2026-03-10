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