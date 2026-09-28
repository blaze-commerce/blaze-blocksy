<?php
/**
 * Lazy Rescan — un-stick Perfmatters lazy images injected after its
 * observer initialised (Blocksy AJAX filters, Load More, pagination).
 *
 * PURPOSE: Perfmatters' lazy loader only observes the elements present when
 * it initialises. Product cards swapped in by a Blocksy AJAX filter or
 * "Load More" keep their SVG placeholder (`img.perfmatters-lazy[data-src]`)
 * forever. `assets/js/perf-lazy-rescan.js` watches `document.body` with a
 * MutationObserver plus Blocksy's AJAX events and re-runs the lazy loader —
 * via the Perfmatters vanilla-lazyload INSTANCE (captured from its
 * `LazyLoad::Initialized` event), never `.update()` on the `window.LazyLoad`
 * constructor, which throws — falling back to the source's direct
 * `data-src` → `src` upgrade when no instance can be found.
 *
 * Enqueued only on `is_shop() || is_product_taxonomy()` (the surfaces with
 * AJAX-swapped product grids), in the footer, versioned with `filemtime()`
 * like every other theme asset (`inc/enqueue.php`). Works with or without
 * Perfmatters (Global Constraint 10): without it there are no
 * `.perfmatters-lazy` images and no instance, so every sweep is a no-op.
 *
 * PROMOTED FROM: `bc-site-customizations/sites/choiceammunition/custom/js/perfmatters-lazy-ajax-rescan.js`
 * (staggered sweeps, body MutationObserver, Blocksy event list, jQuery
 * `ajaxComplete`, `load` sweep) and its enqueue in
 * `sites/choiceammunition/custom/custom.php` (@20, footer, filemtime).
 *
 * MEASURED EVIDENCE: choiceammo 18 stuck lazy images → 0 after a Blocksy
 * AJAX filter on the shop archive.
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: nothing client-specific; a site whose
 * AJAX grid lives on another template (e.g. a search page) needs its own
 * enqueue condition.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether this request is a surface with an AJAX-swapped product grid.
 *
 * @return bool
 */
function blocksy_child_perf_lazy_rescan_applies(): bool {
	return ( function_exists( 'is_shop' ) && is_shop() )
		|| ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() );
}

/**
 * `wp_enqueue_scripts` @20 — enqueue assets/js/perf-lazy-rescan.js.
 *
 * @return void
 */
function blocksy_child_perf_lazy_rescan_enqueue(): void {
	if ( ! blocksy_child_perf_lazy_rescan_applies() ) {
		return;
	}

	$path = BLOCKSY_CHILD_PATH . 'assets/js/perf-lazy-rescan.js';
	if ( ! file_exists( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'bc-perf-lazy-rescan',
		BLOCKSY_CHILD_URL . 'assets/js/perf-lazy-rescan.js',
		[],
		filemtime( $path ),
		true
	);
}

/**
 * Register this module's hook. Front-end only (Global Constraint 5).
 *
 * @return void
 */
function blocksy_child_perf_lazy_rescan_register(): void {
	if ( ! blocksy_child_perf_is_frontend_request() ) {
		return;
	}

	add_action( 'wp_enqueue_scripts', 'blocksy_child_perf_lazy_rescan_enqueue', 20 );
}

blocksy_child_perf_lazy_rescan_register();
