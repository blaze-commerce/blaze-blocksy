<?php
/**
 * Dequeue Handles — stylesheets/scripts verified unused in the rendered DOM.
 *
 * PURPOSE: data file for the `dequeue-assets` module
 * (`inc/perf/dequeue-assets.php`), which dequeues AND deregisters every
 * handle below at `wp_enqueue_scripts` priority 1000. Each entry names the
 * site where it was verified to match nothing in the live DOM (with the
 * relevant UI actually open) — never add a handle on the assumption it is
 * unused.
 *
 * Protected handles (`ct-*`, `blocksy-child-*`, `jquery-core`, `jquery`,
 * `woocommerce`, `wc-cart-fragments`) are refused by the module even if
 * listed here or added via the `blocksy_child_perf_dequeue_handles` filter
 * (Global Constraint 11).
 *
 * PROMOTED FROM: `bc-site-customizations/sites/mettahemp/custom/dequeue-unused.php`
 * (block-library family) and
 * `bc-site-customizations/sites/alternateworlds/custom/includes/dequeue-unused-assets.php`
 * (jQuery UI, slick, Algolia satellite).
 *
 * MEASURED EVIDENCE: see `inc/perf/dequeue-assets.php`'s docblock.
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: a client-specific or conditional
 * handle (e.g. AW's Back In Stock Notifier `cwginstock_bootstrap`, only
 * droppable on in-stock simple products) is added by that site through the
 * `blocksy_child_perf_dequeue_handles` filter, not here.
 *
 * @package Blocksy_Child
 */

return [
	'styles'  => [
		// Mettahemp (task 86exatv6x): Gutenberg block CSS unused by the Blocksy
		// templates; the alignment/text-align utility shim that replaces it
		// ships with the critical-css-supplements module (Task 4).
		'wp-block-library',
		'wp-block-library-theme',
		'classic-theme-styles',
		'wp-components',
		// AlternateWorlds (2026-08-10): jQuery UI "smoothness" theme from
		// code.jquery.com — 0 `.ui-*` widgets in the runtime DOM; 823 ms on
		// the mobile trace (third-party origin on the critical path).
		'jquery-ui-styles',
		// AlternateWorlds (2026-08-06): slick carousel CSS — 0 `[class*="slick-"]`
		// elements on home / archive / product after JS settle.
		'slick-css',
		'slick-css-theme',
		// AlternateWorlds (2026-08-06): Algolia InstantSearch "satellite" theme —
		// 0 `.ais-*` elements with the search popup open and returning results.
		'algolia-satellite',
	],
	'scripts' => [
		// AlternateWorlds (2026-08-10): jQuery UI's only consumer was `.dialog()`
		// on click paths that no longer render; 64.1 kB third-party script.
		'jquery-ui-scripts',
	],
];
