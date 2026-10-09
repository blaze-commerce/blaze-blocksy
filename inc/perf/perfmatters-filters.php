<?php
/**
 * Perfmatters Filters — the code-side PageSpeed levers that must ship as
 * filters rather than as `wp_options` row values.
 *
 * PURPOSE: `perfmatters-config.php` (Task 2) ships the bulk Perfmatters
 * settings row as code. A handful of levers do not fit that row at all —
 * either because Perfmatters only exposes them as PHP filters with no UI
 * field (`perfmatters_used_css_below`, `perfmatters_delay_js_delay_click`,
 * `perfmatters_css_background_selectors`, `perfmatters_exclude_leading_images`),
 * or because the value legitimately depends on runtime state this theme
 * already tracks (`blocksy_child_perf_script_ids()` — every id a perf
 * module has registered this request, so a script/style a later module
 * prints is automatically excluded from Delay JS / Defer JS without a
 * second place to list it). This module registers each of those filters
 * with a named callback (never an anonymous closure — Global Constraint
 * 1's `blocksy_child_perf_` filter-name rule exists so a site can
 * `remove_filter()` any one of these if it needs to).
 *
 * PROMOTED FROM:
 *   - `perfmatters_used_css_below` — Austin
 *     `sites/austinnaturalmattress/custom/perfmatters-used-css-position.php`.
 *   - `perfmatters_delay_js_timeout` — Austin
 *     `sites/austinnaturalmattress/custom/perfmatters-delay-tuning.php`.
 *   - `perfmatters_delay_js_delay_click` — Houston
 *     `sites/houstonnaturalmattress/custom/perfmatters-click-delay.php`
 *     (branch `fix/hnm-a11y-bp-90plus`).
 *   - `perfmatters_delay_js_exclusions` (analytics portion) — Houston
 *     `sites/houstonnaturalmattress/custom/perfmatters-analytics-exclusions.php`
 *     (same branch).
 *   - `perfmatters_css_background_selectors` /
 *     `perfmatters_exclude_leading_images` — Houston
 *     `sites/houstonnaturalmattress/custom/perfmatters-lazy-tuning.php`
 *     (same branch).
 *   - `perfmatters_rucss_excluded_stylesheets` / `perfmatters_defer_js_exclusions`
 *     — new in this module: the former generalises Global Constraint 11
 *     (never `main.min.css` in an RUCSS exclusion) to the filter surface,
 *     not just the shipped JSON row; the latter mirrors
 *     `perfmatters_delay_js_exclusions`'s use of the script-id registry
 *     for Perfmatters' separate Defer JS feature.
 *
 * MEASURED EVIDENCE (per callback, from the sources above):
 *   - used-CSS position: Austin homepage 84 → 87 (moving the 142 KB inline
 *     `<style id="perfmatters-used-css">` block from right after `</title>`
 *     to right before `</head>` let the browser discover the LCP preload
 *     link before having to parse it, cutting "resource load delay" from
 *     ~500 ms to as low as 367 ms).
 *   - delay-JS timeout: Perfmatters' inline loader computes
 *     `setTimeout(d, window.pmDT*1e3)` (`inc/classes/JS.php`, Perfmatters
 *     ≥2.6) — the value is SECONDS, not milliseconds. 7000 s (~1h57m) means
 *     delayed scripts effectively only ever run on first interaction, which
 *     is the point for a no-interaction PSI audit; real visitors still
 *     trigger them on scroll/tap/click.
 *   - click delay: Houston — first tap replayed at ~0.9 s instead of lost
 *     (`pmDC=0` as shipped: a mobile filter button's first tap made no
 *     request at all; `pmDC=1`: the panel opened after the replay).
 *   - analytics exclusions: delay_timeout is 7000 seconds so GA4 page_view
 *     never fires for non-interacting visitors — Houston prod measured a
 *     GA4 `page_view` + `view_item_list` and Google Ads conversion/
 *     remarketing pings, all through one `googletagmanager.com/gtag/js`
 *     loader, that a 7000 s Delay JS timeout would otherwise hold until an
 *     interaction that a bounced visit never makes.
 *   - CSS background selectors: Houston homepage — before-FCP 37 images +
 *     1.19 MB of CSS `background-image` fetches on GreenShift containers,
 *     invisible to `<img>`-based lazy-loading, cut to 19 requests / 0
 *     images once those selectors were registered for Perfmatters' CSS
 *     background lazy-loader.
 *   - leading-images: Houston homepage — needed 14, where the visible DOM
 *     (logo + 5 brand logos = 6 on-screen images) suggested 8. Perfmatters
 *     counts `<img>` tags in HTML SOURCE ORDER, not the browser's rendered
 *     image list; six header icons precede the logo in source order, so
 *     the logo is index 6-7 and the brand row is 9-13 — 14 is the smallest
 *     count that covers index 13. Re-derive per template if the header
 *     markup changes (count occurrences of `<img\b[^>]*>` in the raw HTML
 *     source, in order, and find the index of the last genuinely
 *     above-the-fold one).
 *   - RUCSS excluded stylesheets / Defer JS exclusions: no separate
 *     before/after PageSpeed measurement of their own — see "what does not
 *     travel" below.
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: every default here is a
 * cross-client baseline (7000 s timeout, the two GreenShift background
 * selector classes, an empty extra-exclusions/extra-stylesheets list). A
 * client-specific value — a different leading-images count, a
 * client-specific background selector, a stylesheet that must stay
 * render-blocking on one site only — is added via this module's own
 * `blocksy_child_perf_*` filters from that client's
 * `bc-site-customizations` code, not by editing this file. The one
 * exception is `perfmatters_used_css_below` and
 * `perfmatters_delay_js_delay_click`, which are unconditional `true` with
 * no filter escape hatch — every measured client benefited from both and
 * neither has a plausible reason to differ per site (Global Constraint 2's
 * per-feature opt-in, via `rucss-safelist`/`perfmatters-filters` not being
 * in a client's manifest, is the escape hatch for a site that should not
 * get any of this module at all).
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Analytics-loader substrings excluded from Delay JS by default, so GA4 /
 * Google Ads / GTM still fire for a visitor who never interacts.
 *
 * Perfmatters matches each entry as a substring of the whole script tag,
 * inline content included. `/gtm.js` and `gtm4wp` cover GTM4WP even where
 * its container loader is not currently in the served HTML, so tracking
 * survives if that plugin's placement setting ever changes.
 *
 * @return string[]
 */
function blocksy_child_perf_pmf_analytics_delay_exclusions(): array {
	return [
		'googletagmanager.com/gtag/js',
		'gtag(',
		'woocommerce-google-analytics-integration',
		'gtm4wp',
		'/gtm.js',
	];
}

/**
 * `perfmatters_delay_js_timeout` filter callback.
 *
 * @param mixed $timeout Perfmatters' current value (unused: this module's
 *                        value comes from blocksy_child_perf_delay_timeout,
 *                        not from layering on top of Perfmatters' own).
 * @return int
 */
function blocksy_child_perf_pmf_delay_js_timeout( $timeout ) {
	/**
	 * Filter the Delay JS timeout, in SECONDS (see this file's docblock).
	 *
	 * @param int $seconds Default 7000 (~1h57m — interaction-only).
	 */
	return apply_filters( 'blocksy_child_perf_delay_timeout', 7000 );
}

/**
 * `perfmatters_delay_js_exclusions` filter callback.
 *
 * Merges, in order: every id blocksy_child_perf_script_ids() has
 * registered this request; the analytics-loader substrings above (unless
 * `blocksy_child_perf_exclude_analytics_from_delay` returns false); and
 * whatever `blocksy_child_perf_delay_exclusions` supplies. That third
 * filter is called with a fresh `[]`, not the accumulated list, so a site
 * extending it sees a stable, order-independent base rather than having to
 * account for what the first two steps already added.
 *
 * Global Constraint 6: always returns an array (non-array input becomes
 * `[]` before merging).
 *
 * @param mixed $exclusions Incoming exclusions list.
 * @return string[]
 */
function blocksy_child_perf_pmf_delay_js_exclusions( $exclusions ) {
	$exclusions = is_array( $exclusions ) ? $exclusions : [];

	$merged = array_merge( $exclusions, blocksy_child_perf_script_ids() );

	/**
	 * Whether the analytics-loader substrings are excluded from Delay JS.
	 *
	 * @param bool $exclude Default true.
	 */
	if ( apply_filters( 'blocksy_child_perf_exclude_analytics_from_delay', true ) ) {
		$merged = array_merge( $merged, blocksy_child_perf_pmf_analytics_delay_exclusions() );
	}

	/**
	 * Filter additional Delay JS exclusions.
	 *
	 * Called with a fresh `[]`, not the exclusions accumulated above — see
	 * this callback's own docblock.
	 *
	 * @param array $extra
	 */
	$extra = apply_filters( 'blocksy_child_perf_delay_exclusions', [] );
	$extra = is_array( $extra ) ? $extra : [];

	return array_values( array_unique( array_merge( $merged, $extra ) ) );
}

/**
 * `perfmatters_css_background_selectors` filter callback.
 *
 * Global Constraint 6: always returns an array.
 *
 * @param mixed $selectors Incoming selector list.
 * @return string[]
 */
function blocksy_child_perf_pmf_css_background_selectors( $selectors ) {
	$selectors = is_array( $selectors ) ? $selectors : [];

	/**
	 * Filter the CSS `background-image` selectors Perfmatters should
	 * lazy-load (only consulted when Perfmatters'
	 * `lazyload.css_background_images` is itself enabled — a database
	 * setting, not managed by this module).
	 *
	 * @param string[] $selectors Default: the two GreenShift container/row
	 *                            classes measured on Houston's homepage.
	 */
	$extra = apply_filters( 'blocksy_child_perf_css_bg_selectors', [
		'.wp-block-greenshift-blocks-container',
		'.wp-block-greenshift-blocks-row',
	] );
	$extra = is_array( $extra ) ? $extra : [];

	return array_values( array_unique( array_merge( $selectors, $extra ) ) );
}

/**
 * `perfmatters_exclude_leading_images` filter callback.
 *
 * Perfmatters counts `<img>` tags in HTML SOURCE ORDER (not the browser's
 * rendered image list) and never lazy-loads the first N of them. See this
 * file's own docblock for how Houston's homepage needed 14 where the
 * visible DOM suggested 8, and how to re-derive the number for a template.
 *
 * Takes the max of Perfmatters' own current value and the filtered
 * default, so a higher value set on the settings row (or by a later
 * filter) is never accidentally lowered.
 *
 * @param mixed $count Perfmatters' current value.
 * @return int
 */
function blocksy_child_perf_pmf_exclude_leading_images( $count ) {
	/**
	 * Filter the minimum number of leading `<img>` tags (HTML source
	 * order) Perfmatters must never lazy-load.
	 *
	 * @param int $count Default 0 (no theme-side minimum).
	 */
	return max( (int) $count, (int) apply_filters( 'blocksy_child_perf_leading_images', 0 ) );
}

/**
 * `perfmatters_rucss_excluded_stylesheets` filter callback.
 *
 * Merges in `blocksy_child_perf_rucss_stylesheets`, then rejects (logs and
 * drops) any resulting entry that would substring-match `main.min.css` —
 * Global Constraint 11: main.min.css must never end up excluded from
 * RUCSS, from ANY source, not just the shipped JSON row
 * (`perfmatters-config.php` already keeps it out of the row itself; this
 * is the same rule enforced on the filter surface, where a future
 * `blocksy_child_perf_rucss_stylesheets` caller could otherwise
 * reintroduce it).
 *
 * Global Constraint 6: always returns an array.
 *
 * @param mixed $stylesheets Incoming excluded-stylesheet list.
 * @return string[]
 */
function blocksy_child_perf_pmf_rucss_excluded_stylesheets( $stylesheets ) {
	$stylesheets = is_array( $stylesheets ) ? $stylesheets : [];

	/**
	 * Filter additional stylesheets to exclude from Remove Unused CSS.
	 *
	 * @param string[] $stylesheets Default `[]`.
	 */
	$extra = apply_filters( 'blocksy_child_perf_rucss_stylesheets', [] );
	$extra = is_array( $extra ) ? $extra : [];

	$merged = array_merge( $stylesheets, $extra );

	$filtered = [];

	foreach ( $merged as $stylesheet ) {
		if ( is_string( $stylesheet ) && false !== strpos( $stylesheet, 'main.min.css' ) ) {
			error_log( '[blocksy-child][perf] rejected RUCSS excluded stylesheet matching main.min.css: ' . $stylesheet );
			continue;
		}

		$filtered[] = $stylesheet;
	}

	return array_values( array_unique( $filtered ) );
}

/**
 * `perfmatters_defer_js_exclusions` filter callback.
 *
 * Merges every id blocksy_child_perf_script_ids() has registered this
 * request into Perfmatters' Defer JS exclusions — the same script-id
 * registry `perfmatters_delay_js_exclusions` (above) already draws on for
 * Delay JS, so a later perf module's own printed `<script>` id needs to be
 * registered in exactly one place to be exempted from both.
 *
 * Global Constraint 6: always returns an array.
 *
 * @param mixed $exclusions Incoming exclusions list.
 * @return string[]
 */
function blocksy_child_perf_pmf_defer_js_exclusions( $exclusions ) {
	$exclusions = is_array( $exclusions ) ? $exclusions : [];

	return array_values( array_unique( array_merge( $exclusions, blocksy_child_perf_script_ids() ) ) );
}

add_filter( 'perfmatters_used_css_below', '__return_true' );
add_filter( 'perfmatters_delay_js_timeout', 'blocksy_child_perf_pmf_delay_js_timeout' );
add_filter( 'perfmatters_delay_js_delay_click', '__return_true' );
add_filter( 'perfmatters_delay_js_exclusions', 'blocksy_child_perf_pmf_delay_js_exclusions' );
add_filter( 'perfmatters_css_background_selectors', 'blocksy_child_perf_pmf_css_background_selectors' );
add_filter( 'perfmatters_exclude_leading_images', 'blocksy_child_perf_pmf_exclude_leading_images' );
add_filter( 'perfmatters_rucss_excluded_stylesheets', 'blocksy_child_perf_pmf_rucss_excluded_stylesheets' );
add_filter( 'perfmatters_defer_js_exclusions', 'blocksy_child_perf_pmf_defer_js_exclusions' );
