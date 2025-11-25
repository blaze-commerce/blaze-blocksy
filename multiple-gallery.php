<?php
/**
 * Multiple Gallery Customization
 *
 * Customizes WooCommerce product gallery:
 * - Desktop (≥992px): Stacked layout (all images visible vertically)
 * - Tablet & Mobile (<992px): Slideshow/carousel with thumbnails
 *
 * @package Blaze_Commerce
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Modify gallery arguments based on device
 *
 * Hook: blocksy:woocommerce:single_product:flexy-args
 * Priority: 20 (after parent theme)
 */
add_filter( 'blocksy:woocommerce:single_product:flexy-args', 'blaze_responsive_gallery_args', 20 );

function blaze_responsive_gallery_args( $args ) {
	// Add custom class for CSS targeting
	$args['class'] = isset( $args['class'] ) ? $args['class'] . ' blaze-responsive-gallery' : 'blaze-responsive-gallery';

	// Keep default behavior (will be controlled by CSS/JS)
	// Desktop will hide slider via CSS
	// Mobile will show slider via CSS

	return $args;
}

/**
 * Enqueue custom gallery assets
 */
add_action( 'wp_enqueue_scripts', 'blaze_gallery_enqueue_assets', 20 );

function blaze_gallery_enqueue_assets() {
	// Only load on single product pages
	if ( ! is_product() ) {
		return;
	}

	// Custom CSS
	wp_enqueue_style(
		'blaze-multiple-gallery-css',
		BLAZE_BLOCKSY_URL . '/custom/multiple-gallery.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);

	// Custom JavaScript
	wp_enqueue_script(
		'blaze-multiple-gallery-js',
		BLAZE_BLOCKSY_URL . '/custom/multiple-gallery.js',
		array( 'jquery' ),
		wp_get_theme()->get( 'Version' ),
		true
	);

	// Pass data to JavaScript
	wp_localize_script( 'blaze-multiple-gallery-js', 'blazeGalleryConfig', array(
		'desktopBreakpoint' => 992,
		'enableDebug' => false,
	) );
}

/**
 * Add body class for easier CSS targeting
 */
add_filter( 'body_class', 'blaze_gallery_body_class' );

function blaze_gallery_body_class( $classes ) {
	if ( is_product() ) {
		$classes[] = 'blaze-responsive-gallery-enabled';
	}
	return $classes;
}
