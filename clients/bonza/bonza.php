<?php
/**
 * Bonza (client-specific hooks).
 *
 * Loaded via manifest.json when this client is active.
 * Only add hooks specific to THIS client here.
 * Reusable hooks go in inc/woocommerce.php or inc/hooks.php.
 *
 * @package Blocksy_Child
 * @client  Bonza
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add client-specific hooks below.

// Wishlist off-canvas drawer (86eypb6jy). Figma shows a 2-col PRODUCT CARD
// grid for the Filled/Empty states, not the shared module's default
// list-row layout, so opt into the card-grid item template.
add_filter( 'blocksy_child_wishlist_card_layout', '__return_true' );

// Bonza-specific skin for the shared wishlist-offcanvas module (colors,
// type, spacing, card-grid layout). See clients/bonza/wishlist-offcanvas.css.
add_action( 'wp_enqueue_scripts', function () {
	$css_file = __DIR__ . '/wishlist-offcanvas.css';

	if ( file_exists( $css_file ) ) {
		wp_enqueue_style(
			'bonza-wishlist-offcanvas',
			get_stylesheet_directory_uri() . '/clients/bonza/wishlist-offcanvas.css',
			[ 'blocksy-child-wishlist-offcanvas' ],
			filemtime( $css_file )
		);
	}
} );
