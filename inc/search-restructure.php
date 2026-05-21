<?php
/**
 * Search Restructure — FiboSearch two-column dropdown layout.
 *
 * Transforms FiboSearch's flat suggestion dropdown into a section-based
 * two-column grid: categories/posts/pages on left, product grid on right.
 *
 * Requires: FiboSearch Premium (ajax-search-for-woocommerce-premium).
 * Feature flag: 'search-restructure' in client manifest.json.
 *
 * @package Blocksy_Child
 * @since   1.0.0
 * @date    2026-04-16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bail if FiboSearch is not active.
if ( ! defined( 'DGWT_WCAS_VERSION' ) ) {
	return;
}

/**
 * Override FiboSearch's Blocksy template to show full search bar.
 *
 * WHY: FiboSearch's default Blocksy template renders [fibosearch layout="icon"]
 * which shows only a magnifying glass icon. The live site shows a persistent
 * full search bar in the header.
 * @date 2026-04-09
 */
add_filter( 'blocksy:header:item-view-path:search', function ( $path ) {
	return get_stylesheet_directory() . '/partials/fibosearch-header.php';
}, 20 );

/**
 * FiboSearch broken UI fix.
 *
 * WHY: FiboSearch has a known bug where the search UI breaks on certain
 * page loads. This filter enables the "hard fix" mode.
 */
add_filter( 'dgwt/wcas/scripts/fixer', function ( $fixer ) {
	$fixer['broken_search_ui_hard'] = true;
	return $fixer;
} );

/**
 * Use WooCommerce thumbnail size for FiboSearch product suggestions.
 *
 * WHY: FiboSearch default is 'dgwt-wcas-product-suggestion' (64px wide).
 * The search dropdown grid displays images at ~230px, causing blur.
 * NOTE: Requires FiboSearch index rebuild after deploying this filter.
 * @date 2026-04-16
 */
add_filter( 'dgwt/wcas/setup/thumbnail_size', function () {
	return 'woocommerce_thumbnail';
} );

/**
 * Enqueue search restructure assets (JS + CSS).
 *
 * Loaded globally since search appears on every page.
 * Passes configuration to JS via wp_localize_script.
 */
add_action( 'wp_enqueue_scripts', function () {
	$css_file = BLOCKSY_CHILD_PATH . 'assets/css/components/search-dropdown.css';
	if ( file_exists( $css_file ) ) {
		wp_enqueue_style(
			'blocksy-child-search-dropdown',
			BLOCKSY_CHILD_URL . 'assets/css/components/search-dropdown.css',
			[ 'blocksy-child-style' ],
			filemtime( $css_file )
		);
	}

	$js_file = BLOCKSY_CHILD_PATH . 'assets/js/search-restructure.js';
	if ( file_exists( $js_file ) ) {
		wp_enqueue_script(
			'blocksy-child-search-restructure',
			BLOCKSY_CHILD_URL . 'assets/js/search-restructure.js',
			[],
			filemtime( $js_file ),
			true
		);

		wp_localize_script( 'blocksy-child-search-restructure', 'bcSearchConfig', [
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'bc_search_gallery' ),
			'maxProducts' => 8,
		] );
	}
} );

/**
 * AJAX endpoint: return gallery images for product hover effect.
 *
 * WHY: FiboSearch only serves the primary thumbnail. The search dropdown
 * needs the second gallery image for a hover-swap effect.
 * Returns 300x300 gallery URLs keyed by product ID.
 *
 * Security: nonce verification, absint sanitization, max 20 IDs.
 * @date 2026-04-16
 */
add_action( 'wp_ajax_bc_search_gallery', 'blocksy_child_search_gallery_images' );
add_action( 'wp_ajax_nopriv_bc_search_gallery', 'blocksy_child_search_gallery_images' );

function blocksy_child_search_gallery_images() {
	check_ajax_referer( 'bc_search_gallery', 'nonce' );

	$ids = isset( $_POST['product_ids'] ) ? array_map( 'absint', (array) $_POST['product_ids'] ) : [];

	if ( empty( $ids ) || count( $ids ) > 20 ) {
		wp_send_json_error( 'Invalid product IDs' );
	}

	$result = [];
	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );
		if ( ! $product ) {
			continue;
		}

		$gallery_ids = $product->get_gallery_image_ids();
		if ( ! empty( $gallery_ids ) ) {
			$img_src = wp_get_attachment_image_src( $gallery_ids[0], 'woocommerce_thumbnail' );
			if ( $img_src ) {
				// Defensive escape (audit P1 2026-05-08): future-proofs against
				// any caller that uses .html() instead of .attr('src') on this URL.
				$result[ $id ] = esc_url_raw( $img_src[0] );
			}
		}
	}

	wp_send_json_success( $result );
}
