<?php
/**
 * Shop Page Customizations — Result count repositioning & ordering label.
 *
 * WHY: Blocksy Customizer hanya menyediakan toggle show/hide untuk result count
 * dan ordering. Tidak ada opsi untuk reposisi element atau mengubah label sorting.
 *
 * Reusable: YES — repositioning shop elements is a common requirement.
 * @refactored 2026-05-08 — moved from custom/ to inc/ (Layer 1/Layer 2 architecture compliance)
 * @package Blocksy_Child
 * @date    2026-04-16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Move result count to AFTER woo-listing-top wrapper.
 *
 * Default: priority 20 on woocommerce_before_shop_loop (inside woo-listing-top).
 * New: priority 32 (after woo-listing-top closes at priority 31).
 */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
add_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 32 );

/**
 * 2. Also display result count AFTER pagination block (ct-load-more).
 *
 * Pagination renders at woocommerce_after_shop_loop priority 10.
 * We add result count at priority 11 so it appears right after.
 */
add_action( 'woocommerce_after_shop_loop', 'woocommerce_result_count', 11 );

/**
 * 3. Update result count text after AJAX load-more.
 *
 * Blocksy's infinite scroll appends products via AJAX but the static result
 * count text is not updated. This enqueues a small JS that listens for the
 * ct:infinite-scroll:load event and recalculates the visible product count.
 */
// Enqueues are centralized in inc/enqueue.php (refactored 2026-05-08, audit P1).

/**
 * 4. Change "menu_order" option label to "Sort By None".
 */
add_filter( 'woocommerce_catalog_orderby', function ( $options ) {
	if ( isset( $options['menu_order'] ) ) {
		$options['menu_order'] = __( 'Sort By None', 'woocommerce' );
	}
	return $options;
} );
