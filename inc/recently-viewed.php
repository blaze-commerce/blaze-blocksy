<?php
/**
 * Recently Viewed Products — PDP section below Related Products.
 *
 * Reads the WooCommerce `woocommerce_recently_viewed` cookie (set natively
 * by WC's wc_track_product_view on every product page visit) and renders
 * a product grid matching the Related Products layout.
 *
 * No plugin dependency — uses WooCommerce core cookie + standard product query.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────────────────────────────
 * 1. Track product views — set the woocommerce_recently_viewed cookie.
 *
 * WooCommerce's native wc_track_product_view() only runs when the
 * "Recently Viewed Products" WIDGET is active in a sidebar. We don't
 * use that widget, so we set the cookie ourselves.
 * ──────────────────────────────────────────────────────────────── */
add_action( 'template_redirect', 'bc_track_product_view', 21 );

function bc_track_product_view() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}

	global $post;

	if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) {
		$viewed_products = [];
	} else {
		$viewed_products = wp_parse_id_list(
			explode( '|', sanitize_text_field( wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) )
		);
	}

	// Remove if already in list (will be re-added at end).
	$keys = array_flip( $viewed_products );
	if ( isset( $keys[ $post->ID ] ) ) {
		unset( $viewed_products[ $keys[ $post->ID ] ] );
	}

	$viewed_products[] = $post->ID;

	// Keep max 15 products.
	if ( count( $viewed_products ) > 15 ) {
		array_shift( $viewed_products );
	}

	wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ) );
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
 * Prevent double rendering.
 */
function bc_render_recently_viewed_products() {
	static $rendered = false;

	if ( $rendered ) {
		return;
	}

	if ( ! is_product() ) {
		return;
	}

	// Read WooCommerce's native recently viewed cookie.
	// Refactored to bc_get_recently_viewed_cookie() helper (audit P1 2026-05-08).
	$viewed_ids = bc_get_recently_viewed_cookie();
	if ( empty( $viewed_ids ) ) {
		return;
	}

	if ( empty( $viewed_ids ) ) {
		return;
	}

	// Exclude the current product.
	global $product;
	if ( $product instanceof \WC_Product ) {
		$viewed_ids = array_diff( $viewed_ids, [ $product->get_id() ] );
	}

	if ( empty( $viewed_ids ) ) {
		return;
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
		return;
	}

	$rendered = true;

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
}
