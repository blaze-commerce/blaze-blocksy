<?php
/**
 * Recently Viewed Products — PDP section below Related Products.
 *
 * Cache-safe: product pages are usually full-page cached (Kinsta, WP Rocket,
 * LiteSpeed, ...), which (a) never runs PHP on a cache hit and drops any
 * Set-Cookie it sent on the miss, and (b) would serve the first visitor's
 * list to everyone if the row were rendered into the page. So:
 *
 *   1. assets/js/recently-viewed.js keeps WooCommerce's
 *      `woocommerce_recently_viewed` cookie (ids joined by "|", oldest
 *      first, max 15) in the browser, on every product view.
 *   2. The page only prints an empty slot.
 *   3. The JS fills the slot from ?wc-ajax=bc_recently_viewed&ids=..., whose
 *      output depends only on its URL, so a cached copy is still correct.
 *
 * The cookie name and format match WooCommerce's, so the Recently Viewed
 * widget/block and inc/mini-cart-empty.php keep reading it.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────────────────────────────
 * 1. Enqueue the tracker / loader on product pages.
 * ──────────────────────────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', 'bc_enqueue_recently_viewed' );

function bc_enqueue_recently_viewed() {
	if ( ! is_product() ) {
		return;
	}

	$js = BLOCKSY_CHILD_PATH . 'assets/js/recently-viewed.js';
	if ( ! file_exists( $js ) ) {
		return;
	}

	wp_enqueue_script(
		'blocksy-child-recently-viewed',
		BLOCKSY_CHILD_URL . 'assets/js/recently-viewed.js',
		[],
		filemtime( $js ),
		true
	);
	wp_localize_script( 'blocksy-child-recently-viewed', 'bcRecentlyViewed', [
		'endpoint' => WC_AJAX::get_endpoint( 'bc_recently_viewed' ),
		'max'      => 15,
		// Same path/domain WooCommerce's wc_setcookie() uses, so there is one cookie, not two.
		'path'     => COOKIEPATH ? COOKIEPATH : '/',
		'domain'   => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
	] );
}

/* ────────────────────────────────────────────────────────────────
 * 2. Render recently viewed products after related products.
 * ──────────────────────────────────────────────────────────────── */

/**
 * Render recently viewed products after related products.
 *
 * Hooks into Blocksy's `blocksy:woocommerce:product-single:related:after`
 * which fires after both upsells and related products on the PDP.
 */
add_action( 'blocksy:woocommerce:product-single:related:after', 'bc_render_recently_viewed_products' );

/**
 * Also hook into woocommerce_after_main_content as fallback
 * in case Blocksy's hook doesn't fire.
 */
add_action( 'woocommerce_after_main_content', 'bc_render_recently_viewed_products', 10 );

/**
 * Print the empty slot recently-viewed.js fills (once per page).
 */
function bc_render_recently_viewed_products() {
	static $rendered = false;

	if ( $rendered || ! is_product() ) {
		return;
	}

	$rendered = true;

	global $product;
	$product_id = $product instanceof \WC_Product ? $product->get_id() : get_the_ID();

	echo '<div class="bc-recently-viewed-slot" data-product-id="' . esc_attr( $product_id ) . '"></div>';
}

/* ────────────────────────────────────────────────────────────────
 * 3. AJAX endpoint: ?wc-ajax=bc_recently_viewed&ids=12,34&exclude=56
 *
 * The ids come from the URL (the browser's cookie, read by the JS), not
 * from $_COOKIE, so the response is a pure function of the URL and stays
 * correct even if a cache in front of the site stores it.
 * ──────────────────────────────────────────────────────────────── */
add_action( 'wc_ajax_bc_recently_viewed', 'bc_ajax_recently_viewed' );

function bc_ajax_recently_viewed() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- public, read-only.
	$ids     = isset( $_GET['ids'] ) ? wp_parse_id_list( wp_unslash( $_GET['ids'] ) ) : [];
	$exclude = isset( $_GET['exclude'] ) ? absint( $_GET['exclude'] ) : 0;
	// phpcs:enable

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
	echo bc_recently_viewed_markup( array_slice( $ids, -15 ), $exclude );
	wp_die();
}

/**
 * The Recently Viewed section for a list of product IDs.
 *
 * @param int[] $viewed_ids Product IDs, oldest first (cookie order).
 * @param int   $exclude    Current product ID.
 * @return string HTML, or '' when there is nothing to show.
 */
function bc_recently_viewed_markup( $viewed_ids, $exclude = 0 ) {
	$viewed_ids = array_diff( array_map( 'absint', $viewed_ids ), [ (int) $exclude, 0 ] );

	if ( empty( $viewed_ids ) ) {
		return '';
	}

	// Limit to most recent 8, reverse so newest first.
	$viewed_ids = array_slice( array_reverse( $viewed_ids ), 0, 8 );

	// Query products.
	$args = [
		'post_type'      => 'product',
		'post__in'       => $viewed_ids,
		'orderby'        => 'post__in',
		'posts_per_page' => 8,
		'post_status'    => 'publish',
	];

	$products = new \WP_Query( $args );

	if ( ! $products->have_posts() ) {
		return '';
	}

	ob_start();

	// Match Blocksy's related products column settings.
	$columns = blocksy_expand_responsive_value(
		get_theme_mod( 'woocommerce_related_products_slideshow_columns', [
			'desktop' => 4,
			'tablet'  => 3,
			'mobile'  => 2,
		] )
	);

	$desktop_cols = isset( $columns['desktop'] ) ? intval( $columns['desktop'] ) : 4;

	// Stock Blocksy arrow SVGs — kept verbatim from
	// blocksy/inc/components/archive/helpers.php:178-179 so visual styling
	// (size, viewBox, currentColor fill) inherits automatically.
	$arrow_prev_svg = '<svg width="16" height="10" fill="currentColor" viewBox="0 0 16 10"><path d="M15.3 4.3h-13l2.8-3c.3-.3.3-.7 0-1-.3-.3-.6-.3-.9 0l-4 4.2-.2.2v.6c0 .1.1.2.2.2l4 4.2c.3.4.6.4.9 0 .3-.3.3-.7 0-1l-2.8-3h13c.2 0 .4-.1.5-.2s.2-.3.2-.5-.1-.4-.2-.5c-.1-.1-.3-.2-.5-.2z"></path></svg>';
	$arrow_next_svg = '<svg width="16" height="10" fill="currentColor" viewBox="0 0 16 10"><path d="M.2 4.5c-.1.1-.2.3-.2.5s.1.4.2.5c.1.1.3.2.5.2h13l-2.8 3c-.3.3-.3.7 0 1 .3.3.6.3.9 0l4-4.2.2-.2V5v-.3c0-.1-.1-.2-.2-.2l-4-4.2c-.3-.4-.6-.4-.9 0-.3.3-.3.7 0 1l2.8 3H.7c-.2 0-.4.1-.5.2z"></path></svg>';

	// Match Blocksy's Related Products HTML structure exactly.
	echo '<section class="bc-recently-viewed related products is-layout-slider is-width-constrained">';
	echo '<h2 class="ct-module-title">' . esc_html__( 'Recently Viewed', 'blocksy-child' ) . '</h2>';
	echo '<div class="flexy-container" data-flexy="no">';
	echo '<div class="flexy">';
	echo '<div class="flexy-view" data-flexy-view="boxed">';
	echo '<div class="products flexy-items columns-' . esc_attr( $desktop_cols ) . '" data-products="type-1" data-hover="swap">';

	while ( $products->have_posts() ) {
		$products->the_post();
		echo '<div class="flexy-item">';
		wc_get_template_part( 'content', 'product' );
		echo '</div>';
	}

	echo '</div>';
	echo '</div>';

	// Arrow controls — required by product-carousel-dots.js, which navigates
	// the slider by dispatching click events on these elements. Also serve as
	// a direct user affordance once positioned outside the card area via
	// assets/css/components/product-carousel-dots.css.
	echo '<span class="flexy-arrow-prev">' . $arrow_prev_svg . '</span>';
	echo '<span class="flexy-arrow-next">' . $arrow_next_svg . '</span>';

	echo '</div>';
	echo '</div>';
	echo '</section>';

	wp_reset_postdata();

	return ob_get_clean();
}
