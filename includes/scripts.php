<?php

// Ensure is_plugin_active() function is available
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

add_action( 'wp_enqueue_scripts', function () {

	$template_uri = get_stylesheet_directory_uri();
	wp_enqueue_style( 'parent-style', $template_uri . '/style.css' );

	// Enqueue child style
	wp_enqueue_style(
		'blocksy-child-search-style',
		$template_uri . '/assets/css/search.css',
		array( 'parent-style' )
	);

	// Enqueue footer style
	wp_enqueue_style(
		'blocksy-child-footer-style',
		$template_uri . '/assets/css/footer.css',
		array( 'parent-style' )
	);

	// Enqueue header style
	wp_enqueue_style(
		'blocksy-child-header-style',
		$template_uri . '/assets/css/header.css',
		array( 'parent-style' )
	);
} );

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
