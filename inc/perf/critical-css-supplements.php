<?php
/**
 * Critical CSS Supplements — inline first-paint rules that survive
 * Perfmatters' Remove Unused CSS.
 *
 * PURPOSE: Perfmatters Remove Unused CSS (RUCSS) only rewrites ENQUEUED
 * `<link>` stylesheets, and it builds its per-page "used CSS" by scanning
 * the server-rendered HTML at a mobile viewport with no JavaScript run.
 * Three shapes of rule are systematically invisible to that scan: a
 * declaration that only SETS a custom property another (kept) rule reads;
 * a desktop-only `@media(min-width:…)` rule, because the scan itself is
 * mobile-first; and anything gated on markup JS adds after load (a
 * gallery's own slider classes, a stepper's JS-appended number). This
 * module prints three `wp_head` `<style>` blocks — global, archive,
 * product — that each reproduce exactly the rules RUCSS drops for that
 * template, via `blocksy_child_perf_style()` so the block itself carries
 * the `data-no-optimize`/`data-no-minify` attributes that keep Perfmatters
 * (and any minifier) from touching it in turn.
 *
 * The CSS content itself lives in `inc/perf/data/critical-css.php`
 * (Global Constraint 7) — that file's docblock has the full per-rule
 * source, rationale and stripped-vs-kept reasoning. This file only wires
 * those three buckets to `blocksy_child_perf_style()` with their
 * conditions, plus the two print-time additions neither bucket's data can
 * carry on its own: the opt-in header min-height rule (global bucket) and
 * the `blocksy_child_perf_critical_css_extra` per-site escape hatch (every
 * bucket).
 *
 * HOW TO VERIFY: open a page in DevTools and disable every real
 * stylesheet without touching this module's inline blocks —
 *
 *     for (const s of document.styleSheets) if (s.href) s.disabled = true;
 *
 * — then confirm, per template:
 *   - EVERY page: body-copy links are still underlined (not colour-only);
 *     `.alignwide`/`.alignfull`/`.has-text-align-*` blocks keep their
 *     layout.
 *   - Shop / product-taxonomy archive: the product grid keeps its
 *     configured column count on desktop (not collapsed to one column);
 *     the listing-top bar (result count + Filters pill + sort) still lays
 *     out as a row instead of stacking unstyled; wishlist hearts show
 *     outlined, not solid-filled, when not active; no product card shows
 *     its hover/secondary image doubled beside the primary one.
 *   - Single product: the gallery renders as one contained slide (not
 *     every image stacked full-width down the page); a sold-individually
 *     (max==1) quantity box still shows "1" instead of a blank hole, with
 *     its +/- buttons already dimmed.
 *
 * PROMOTED FROM / MEASURED EVIDENCE: see inc/perf/data/critical-css.php —
 * Houston a11y 94 -> 97 (`link-in-text-block`); Kajal category page
 * pixel-identical with the 55-stylesheet-disabled check above; Byron Bay
 * Candles gallery FOUC (confirmed pre-init `.flexy` collapse, fixed);
 * AlternateWorlds qty stepper 580 ms visible-broken-state flash, fixed;
 * choiceammunition header min-height CLS 0.175 -> 0.000
 * (`W:\BLAZE COMMERCE\pagespeed-docs\clickup-tasks\86ewtgyv1-choiceammunition-pagespeed.md`).
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: the header min-height pixel values
 * are one specific site's `--header-height` measurement, not a generalised
 * default — shipping a wrong guess would CAUSE a CLS shift instead of
 * preventing one, so this module never sets one itself. A site opts in via
 * `blocksy_child_perf_header_min_height`. Likewise any site's own first-
 * paint polish belongs behind `blocksy_child_perf_critical_css_extra`, not
 * in this file or the data file.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the opt-in header min-height CSS.
 *
 * Reads `blocksy_child_perf_header_min_height` (default `null` — this
 * rule never prints unless a site explicitly configures it, per this
 * module's own docblock: a wrong guess causes the CLS it is meant to
 * prevent). Expects `['desktop' => px, 'mobile' => px]`; any other shape,
 * a missing key, or a non-positive value is treated as "not configured"
 * and yields ''.
 *
 * @return string
 */
function blocksy_child_perf_critical_css_header_min_height(): string {
	$config = apply_filters( 'blocksy_child_perf_header_min_height', null );

	if ( ! is_array( $config ) || ! isset( $config['desktop'], $config['mobile'] ) ) {
		return '';
	}

	$desktop = (int) $config['desktop'];
	$mobile  = (int) $config['mobile'];

	if ( $desktop <= 0 || $mobile <= 0 ) {
		return '';
	}

	return '@media (max-width: 999.98px){header#header.ct-header{min-height:' . $mobile . 'px}}'
		. '@media (min-width:1000px){header#header.ct-header{min-height:' . $desktop . 'px}}';
}

/**
 * Assemble one bucket's full CSS: the shipped base rules, plus — global
 * bucket only — the opt-in header min-height rule, plus — every bucket —
 * any extra site CSS from `blocksy_child_perf_critical_css_extra`.
 *
 * @param string $bucket One of 'global' | 'archive' | 'product' (also
 *                        passed to the extra-CSS filter as $bucket).
 * @param string $base   The bucket's shipped CSS from
 *                        inc/perf/data/critical-css.php.
 * @return string
 */
function blocksy_child_perf_critical_css_bucket( string $bucket, string $base ): string {
	$css = $base;

	if ( 'global' === $bucket ) {
		$css .= blocksy_child_perf_critical_css_header_min_height();
	}

	/**
	 * Extra per-site critical CSS appended to one bucket.
	 *
	 * @param string $extra  Default ''.
	 * @param string $bucket 'global' | 'archive' | 'product'.
	 */
	$extra = apply_filters( 'blocksy_child_perf_critical_css_extra', '', $bucket );

	if ( is_string( $extra ) ) {
		$css .= $extra;
	}

	return $css;
}

/**
 * Print-time condition for the archive bucket.
 *
 * Guarded with function_exists() (Global Constraint 10): WooCommerce's
 * `is_shop()` / `is_product_taxonomy()` do not exist when WooCommerce is
 * inactive, and this test harness never defines them either.
 *
 * @return bool
 */
function blocksy_child_perf_critical_css_is_archive(): bool {
	if ( ! function_exists( 'is_shop' ) || ! function_exists( 'is_product_taxonomy' ) ) {
		return false;
	}

	return is_shop() || is_product_taxonomy();
}

/**
 * Print-time condition for the product bucket.
 *
 * @return bool
 */
function blocksy_child_perf_critical_css_is_product(): bool {
	return function_exists( 'is_product' ) && is_product();
}

/**
 * Register the three wp_head printers.
 *
 * A named function (rather than top-level file-scope calls) so the test
 * suite can re-run registration after changing the filters that
 * blocksy_child_perf_critical_css_bucket() reads, without re-requiring this
 * file (PHP would fatal on redeclaring the functions above).
 *
 * @return void
 */
function blocksy_child_perf_critical_css_register(): void {
	$data = blocksy_child_perf_data( 'critical-css' );

	blocksy_child_perf_style(
		'bc-perf-critical-global',
		blocksy_child_perf_critical_css_bucket( 'global', $data['global'] ?? '' ),
		1
	);

	blocksy_child_perf_style(
		'bc-perf-critical-archive',
		blocksy_child_perf_critical_css_bucket( 'archive', $data['archive'] ?? '' ),
		1,
		'blocksy_child_perf_critical_css_is_archive'
	);

	blocksy_child_perf_style(
		'bc-perf-critical-product',
		blocksy_child_perf_critical_css_bucket( 'product', $data['product'] ?? '' ),
		1,
		'blocksy_child_perf_critical_css_is_product'
	);
}

blocksy_child_perf_critical_css_register();
