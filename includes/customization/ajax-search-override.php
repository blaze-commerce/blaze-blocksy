<?php
/**
 * AJAX Search for WooCommerce - Custom Suggestion Template Override
 * 
 * This file overrides the default AJAX search suggestion template
 * to match the "recommended-product-item" structure from the mini-cart component.
 * 
 * @package Blaze Blocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Filter to customize the AJAX search suggestion data
 * Adds custom data attributes and classes for the new template
 */
add_filter( 'dgwt/wcas/tnt/search_results/suggestion/product', function( $outputData, $suggestion ) {
	// Add custom template indicator
	$outputData['use_custom_template'] = true;
	
	// Add custom CSS classes for the new template
	$outputData['custom_classes'] = 'recommended-product-item';
	
	// Ensure we have the image HTML in the right format
	if ( ! isset( $outputData['thumb_html'] ) || empty( $outputData['thumb_html'] ) ) {
		$outputData['thumb_html'] = '<img src="' . wc_placeholder_img_src() . '" alt="' . esc_attr( $outputData['value'] ) . '" loading="lazy">';
	}
	
	return $outputData;
}, 10, 2 );

/**
 * Enqueue custom JavaScript for AJAX search suggestion template override
 */
add_action( 'wp_enqueue_scripts', function() {
	// Get version from child theme
	$version = defined( 'BLAZE_BLOCKSY_VERSION' ) ? BLAZE_BLOCKSY_VERSION : '1.0.0';

	wp_enqueue_script(
		'blaze-blocksy-ajax-search-override',
		BLAZE_BLOCKSY_URL . '/assets/js/ajax-search-override.js',
		array( 'jquery' ),
		$version,
		true
	);
}, 20 );

/**
 * Enqueue custom CSS for AJAX search suggestions
 */
add_action( 'wp_enqueue_scripts', function() {
	// Get version from child theme
	$version = defined( 'BLAZE_BLOCKSY_VERSION' ) ? BLAZE_BLOCKSY_VERSION : '1.0.0';
	$url = defined( 'BLAZE_BLOCKSY_URL' ) ? BLAZE_BLOCKSY_URL : get_stylesheet_directory_uri();

	wp_enqueue_style(
		'blaze-blocksy-ajax-search-override',
		$url . '/assets/css/ajax-search-override.css',
		array(),
		$version
	);
}, 20 );

