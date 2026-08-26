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

/**
 * Wishlist off-canvas drawer, ClickUp 86eypb6jy.
 *
 * Figma component set 68:34939 "Wishlist" (Empty/Filled desktop, mobile
 * Variant3/Variant4) shows a 2-col PRODUCT CARD grid, not the shared
 * wishlist-offcanvas module's default list-row layout, so opt into the
 * module's card-grid item template.
 */
add_filter( 'blocksy_child_wishlist_card_layout', '__return_true' );

/**
 * Enqueue the wishlist off-canvas stylesheet.
 *
 * Kept out of clients/bonza/bonza.css (loaded by the manifest, shared with
 * other in-flight Bonza work) so this task cannot collide with concurrent
 * edits to that file, same isolation pattern as bonza_footer_enqueue_assets()
 * above. Depends on the shared module's own handle so this file always
 * loads after it.
 */
function bonza_wishlist_offcanvas_enqueue_assets() {
	$file = BLOCKSY_CHILD_PATH . 'clients/bonza/wishlist-offcanvas.css';

	if ( ! file_exists( $file ) ) {
		return;
	}

	wp_enqueue_style(
		'bonza-wishlist-offcanvas',
		BLOCKSY_CHILD_URL . 'clients/bonza/wishlist-offcanvas.css',
		[ 'blocksy-child-wishlist-offcanvas' ],
		filemtime( $file )
	);
}
add_action( 'wp_enqueue_scripts', 'bonza_wishlist_offcanvas_enqueue_assets' );
