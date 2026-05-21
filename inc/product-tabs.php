<?php
/**
 * Product Tabs — Renders ACF accordion fields as WooCommerce product tabs.
 *
 * Hooks into woocommerce_product_tabs to inject per-product tabs
 * from ACF field groups (Accordion One/Two/Three).
 *
 * Each accordion has two ACF fields:
 *   - title_{suffix}  (text)    → tab title
 *   - text_{suffix}   (wysiwyg) → tab content
 *
 * Tabs only render when BOTH title and content are populated.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tab slot configuration — add a line here to add a new ACF accordion tab.
 *
 * Each entry maps to an ACF field pair: title_{suffix} + text_{suffix}.
 * Priority controls tab order (higher = further right).
 *
 * @return array
 */
function bc_acf_tab_slots() {
	return [
		[ 'suffix' => 'one',   'priority' => 25 ],
		[ 'suffix' => 'two',   'priority' => 26 ],
		[ 'suffix' => 'three', 'priority' => 27 ],
	];
}

/**
 * Register ACF accordion fields as WooCommerce product tabs.
 *
 * @param array $tabs Existing WooCommerce tabs.
 * @return array Modified tabs.
 */
function bc_acf_product_tabs( $tabs ) {
	// Guard: ACF must be active.
	if ( ! function_exists( 'get_field' ) ) {
		return $tabs;
	}

	global $product;

	if ( ! $product instanceof \WC_Product ) {
		return $tabs;
	}

	$product_id = $product->get_id();
	$slots      = bc_acf_tab_slots();

	foreach ( $slots as $slot ) {
		$suffix = $slot['suffix'];

		try {
			$title   = get_field( 'title_' . $suffix, $product_id );
			$content = get_field( 'text_' . $suffix, $product_id );
		} catch ( \Throwable $e ) {
			error_log( '[blocksy-child] ACF tab error (product ' . $product_id . ', slot ' . $suffix . '): ' . $e->getMessage() );
			continue;
		}

		// Skip if either title or content is empty.
		if ( empty( $title ) || empty( $content ) ) {
			continue;
		}

		$slug = 'bc-tab-' . sanitize_title( $title );

		$tabs[ $slug ] = [
			'title'    => esc_html( $title ),
			'priority' => $slot['priority'],
			'callback' => 'bc_acf_tab_render',
			'content'  => $content,
		];
	}

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'bc_acf_product_tabs', 25 );

/**
 * Render a single ACF tab's content.
 *
 * WooCommerce passes the tab key and tab array to the callback.
 *
 * @param string $key Tab key.
 * @param array  $tab Tab data including 'content'.
 */
function bc_acf_tab_render( $key, $tab ) {
	if ( empty( $tab['content'] ) ) {
		return;
	}

	echo '<div class="bc-acf-tab-content">';
	echo wp_kses_post( $tab['content'] );
	echo '</div>';
}
