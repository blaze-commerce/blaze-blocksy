<?php
/**
 * Hooks — WordPress/Blocksy action and filter hooks.
 *
 * ONLY hooks that the Blocksy Customizer cannot handle.
 * Each hook must have a comment explaining WHY it exists.
 *
 * FiboSearch hooks have been moved to inc/search-restructure.php
 * (loaded via feature flag in loader.php).
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile header trigger (hamburger) — replace Blocksy default Type-1
 * (3 filled rectangles) with 3 fully-rounded pills.
 *
 * Why: live `byronbaycandles.com` uses a thin-pill hamburger — uniform with
 * the rest of the icon row (account/wishlist/cart). Blocksy's default
 * Type-1 looked visually heavier, flagged in QA CU-86exerxur 2026-05-04.
 *
 * Why this filter (not custom upload): Blocksy's trigger panel doesn't
 * expose a custom-SVG upload field. The official extension point is the
 * `blocksy:header:trigger:svg` filter applied in
 * `inc/panel-builder/header/trigger/view.php`.
 *
 * Why `fill="currentColor"`: keeps Layer 1 reusable (no client-specific
 * colour baked in). The fill cascades from CSS `color`, which Blocksy
 * controls via the `triggerIconColor.default.color` theme_mod set by
 * each client. BBC's theme_mod renders #888888; other clients get their
 * own value automatically.
 *
 * @date 2026-05-04 (CU-86exerxur), refactored to currentColor 2026-05-07
 */
add_filter(
	'blocksy:header:trigger:svg',
	function ( $svg ) {
		return '<svg class="ct-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" data-type="bc-pill" aria-hidden="true">'
			. '<rect x="2" y="5" width="20" height="2" rx="1" fill="currentColor"/>'
			. '<rect x="2" y="11" width="20" height="2" rx="1" fill="currentColor"/>'
			. '<rect x="2" y="17" width="20" height="2" rx="1" fill="currentColor"/>'
			. '</svg>';
	}
);
