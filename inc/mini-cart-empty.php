<?php
/**
 * Mini Cart Empty State — single Blocksy carousel matching Image #1.
 *
 * When the mini cart is empty, shows:
 *   1. "Your cart is currently empty" message + Return to shop button
 *   2. ONE Blocksy native suggested-products carousel (single heading,
 *      prev/next arrows, 2-column flexy layout) — visually identical to
 *      Image #1 (mini cart with items source of truth).
 *
 * Product ID priority for the single carousel:
 *   1. Recently Viewed (Baymard primary signal)
 *   2. + Wishlist favourites (deduped) until 4 IDs
 *   3. Bestsellers fallback (handled inside the shared helper) if both empty
 *
 * Hooks into blocksy:pro:woo-extra:offcanvas:minicart:empty which Blocksy
 * checks before falling back to its default empty cart template.
 *
 * The shared helper bc_render_blocksy_suggested_carousel() (in inc/helpers.php)
 * handles the cart-fragments-safe class rename, output validation, and a
 * simple-grid LAYER 5 fallback. All 7 drawer states share that helper —
 * see memory file blocksy-drawer-suggested-products-architecture.md.
 *
 * @package Blocksy_Child
 * @date 2026-04-28 — collapsed to single carousel for Image #1 parity
 *                    (removed outer "Recently Viewed" / "Your Favourites" /
 *                    "Popular Right Now" h3 labels + wrapper sections).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'blocksy:pro:woo-extra:offcanvas:minicart:empty', 'bc_mini_cart_empty_state' );

function bc_mini_cart_empty_state() {
	echo '<div class="bc-empty-cart-state">';
	echo '<div class="wc-empty-cart-message">';
	echo '<div class="cart-empty woocommerce-info" role="status">' . esc_html__( 'Your cart is currently empty.', 'woocommerce' ) . '</div>';
	echo '</div>';
	echo '<p class="return-to-shop"><a class="button wc-backward" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Return to shop', 'woocommerce' ) . '</a></p>';

	// Build a single merged ID list: recently viewed first, then wishlist
	// favourites (deduped). Empty array → helper falls back to bestsellers.
	$viewed_ids   = bc_get_recently_viewed_ids( 4 );
	$wishlist_ids = bc_get_wishlist_ids( 4 );
	$merged_ids   = array_values( array_unique( array_merge( $viewed_ids, $wishlist_ids ) ) );
	$merged_ids   = array_slice( $merged_ids, 0, 4 );

	$html = bc_render_blocksy_suggested_carousel( $merged_ids, 'bc-minicart-suggested-grid' );
	if ( ! empty( $html ) ) {
		echo $html;
	}

	echo '</div>';
}

/**
 * Get recently viewed product IDs from the WooCommerce cookie.
 *
 * @param int $limit Max products to return.
 * @return array Product IDs (most recent first).
 */
function bc_get_recently_viewed_ids( $limit = 4 ) {
	// Refactored to bc_get_recently_viewed_cookie() helper (audit P1 2026-05-08).
	$ids = bc_get_recently_viewed_cookie();
	if ( empty( $ids ) ) {
		return [];
	}

	return array_slice( array_reverse( $ids ), 0, $limit );
}

/**
 * Get wishlist product IDs from Blocksy's wishlist extension.
 *
 * @param int $limit Max products to return.
 * @return array Product IDs.
 */
function bc_get_wishlist_ids( $limit = 4 ) {
	if ( ! function_exists( 'blc_get_ext' ) ) {
		return [];
	}

	try {
		$ext = blc_get_ext( 'woocommerce-extra' );
		if ( ! $ext || ! method_exists( $ext, 'get_wish_list' ) ) {
			return [];
		}

		$wishlist = $ext->get_wish_list()->get_current_wish_list();

		if ( empty( $wishlist ) || ! is_array( $wishlist ) ) {
			return [];
		}

		$ids = [];
		foreach ( $wishlist as $item ) {
			if ( is_array( $item ) && isset( $item['id'] ) ) {
				$ids[] = absint( $item['id'] );
			} elseif ( is_object( $item ) && isset( $item->id ) ) {
				$ids[] = absint( $item->id );
			} elseif ( is_int( $item ) ) {
				$ids[] = absint( $item );
			}
		}

		return array_slice( array_filter( $ids ), 0, $limit );
	} catch ( \Throwable $e ) {
		error_log( '[blocksy-child] Wishlist read error in mini cart empty: ' . $e->getMessage() );
		return [];
	}
}
