<?php
/**
 * Content Visibility — skip style/layout/paint of off-screen sections
 * during the initial render.
 *
 * PURPOSE: `content-visibility:auto` lets the browser skip rendering work
 * for an element until it nears the viewport; `contain-intrinsic-size:auto
 * {px}px` reserves its measured height meanwhile (and `auto` remembers the
 * real size once rendered), so skipping layout cannot shift what follows.
 * Visible sections render exactly as before. This module prints one inline
 * `<style id="bc-perf-cv">` at `wp_head` priority 2 from a selector → px
 * map (`blocksy_child_perf_cv_map` filter), resolved at PRINT time.
 *
 * NEVER apply it to the header, a sticky element, or any popup / modal /
 * off-canvas block that is not scoped to its closed state (the default
 * off-canvas rule is `:not(.active)` — `.active` is Blocksy's open-state
 * class, so an open panel always renders normally). A skipped subtree has
 * no layout, so anything that measures it, anchors to it, or animates in
 * from it breaks.
 *
 * MEASURING: PSI/Lighthouse cannot resolve this change — the saved
 * Style & Layout time is below its run-to-run noise. Measure with a forced-
 * layout A/B instead (DevTools Performance panel, same page, rule toggled,
 * compare "Recalculate style" + "Layout" totals).
 *
 * PROMOTED FROM: `bc-site-customizations/sites/austinnaturalmattress/custom/product-lcp-image.php`
 * (PDP `.related.products` rule, wp_head @2) and Houston
 * `custom/pdp-perf-supplements.php` (`git show fix/hnm-a11y-bp-90plus:…` —
 * the `.ct-panel.ct-offcanvas-module:not(.active)` 823 px rule and the
 * "measured rendered heights, rounded up" discipline).
 *
 * MEASURED EVIDENCE: Austin PDP — ~400 ms of Style & Layout competing with
 * the gallery LCP image before the rule (source docblock). No PSI score
 * delta is claimed, by design (see MEASURING).
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: client-specific section selectors
 * (Austin `.anm-reviews-section` / `.bb-product-accordion`, Houston's
 * purity-ratings panel and post-`main` reusable blocks) — a site adds its
 * own through `blocksy_child_perf_cv_map`, with heights measured on it.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The selector → intrinsic-height (px) map.
 *
 * @return array<string,int>
 */
function blocksy_child_perf_cv_map(): array {
	/**
	 * Filter the content-visibility map. Keys are CSS selectors, values
	 * the measured rendered height in px (rounded up). Never add a header
	 * or popup selector (see this file's docblock).
	 *
	 * @param array<string,int> $map
	 */
	$map = apply_filters(
		'blocksy_child_perf_cv_map',
		[
			'footer.ct-footer'                           => 400,
			'.related.products'                          => 600,
			'.ct-panel.ct-offcanvas-module:not(.active)' => 823,
		]
	);

	return is_array( $map ) ? $map : [];
}

/**
 * Build the CSS: `{sel}{content-visibility:auto;contain-intrinsic-size:auto {px}px}`
 * per entry. Drops (and logs) an empty selector, one containing `{`, `}` or
 * `<` (cannot break out of the rule or the `<style>` tag), or a non-positive
 * height.
 *
 * @param array $map Selector => px.
 * @return string
 */
function blocksy_child_perf_cv_css( array $map ): string {
	$css = '';

	foreach ( $map as $selector => $px ) {
		$selector = trim( (string) $selector );
		$px       = (int) $px;

		if ( '' === $selector || false !== strpbrk( $selector, '{}<' ) || $px <= 0 ) {
			error_log( '[blocksy-child][perf] content-visibility: dropped invalid entry ' . var_export( [ $selector => $px ], true ) );
			continue;
		}

		$css .= $selector . '{content-visibility:auto;contain-intrinsic-size:auto ' . $px . 'px}';
	}

	return $css;
}

/**
 * Register the `bc-perf-cv` printer (wp_head @2). The CSS is a callable so
 * the map filter is applied at print time, not module-load time.
 *
 * @return void
 */
function blocksy_child_perf_content_visibility_register(): void {
	blocksy_child_perf_style(
		'bc-perf-cv',
		function () {
			return blocksy_child_perf_cv_css( blocksy_child_perf_cv_map() );
		},
		2
	);
}

blocksy_child_perf_content_visibility_register();
