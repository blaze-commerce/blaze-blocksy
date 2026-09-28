<?php
/**
 * RUCSS Safelist — keep JS-hydrated markup styled through Perfmatters'
 * Remove Unused CSS.
 *
 * PURPOSE: Perfmatters' Remove Unused CSS decides what to inline by
 * scanning the server-rendered HTML buffer only — no JS runs during that
 * scan. Any selector whose markup is only added by JS after load (a
 * search flyout, a hydrated mini-cart, a carousel's own track/nav
 * classes, a variation swap) is invisible to it and gets pruned from the
 * inlined used-CSS, so that element renders unstyled until its deferred
 * stylesheet finally loads on user interaction. This module registers the
 * generic, cross-client baseline of such selectors — via Perfmatters' own
 * `perfmatters_rucss_excluded_selectors` filter, so it ships with the
 * theme code rather than living only in a `wp_options` row that a fresh
 * clone does not inherit — and rejects (with a logged reason) any entry
 * that Perfmatters' selector matcher would silently ignore.
 *
 * The selector list itself lives in `inc/perf/data/rucss-safelist.php`
 * (Global Constraint 7); that file's own docblock documents Perfmatters'
 * substring-matching regex and why BEM descendants each need their own
 * entry.
 *
 * PROMOTED FROM: the 111-selector Perfmatters known-good row documented in
 * `W:\BLAZE COMMERCE\pagespeed-docs\analysis\03-extraction-houston-docs-and-perfmatters-row.md`
 * §3, plus the validation pattern from
 * `bc-site-customizations/sites/alternateworlds/custom/search-results/rucss-selectors.php`
 * (`perfmatters_rucss_excluded_selectors` as the fix for JS-injected markup
 * losing its first-paint styling).
 *
 * MEASURED EVIDENCE: see `inc/perf/perfmatters-filters.php`'s docblock for
 * the Houston homepage numbers that motivated shipping the Perfmatters row
 * (including this selector set) as code. This module's own contribution —
 * always merging the safelist into the filter regardless of which row is
 * stored — has no separate before/after measurement; it is a shield
 * against the row being re-applied by hand and the selectors being
 * forgotten (exactly the "no-op filters become load-bearing when config
 * changes" failure mode documented in that same analysis, §6).
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: a selector that is specific to one
 * client's own JS-rendered markup (e.g. AlternateWorlds'
 * `AW_SR_RUCSS_CARD_SELECTORS`, gated to one search-results template) does
 * not belong in the shipped data file — that site instead adds its own
 * entries via `blocksy_child_perf_rucss_selectors`, the same escape hatch
 * `blocksy_child_perf_pmf_rucss_excluded_selectors()` below already
 * applies to the shipped list.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a single RUCSS excluded-selector entry is one Perfmatters will
 * actually act on.
 *
 * Perfmatters' selector matcher (documented in full in
 * `inc/perf/data/rucss-safelist.php`) silently no-ops on a handful of
 * shapes rather than erroring — a typo or a stray combinator simply never
 * rescues anything, with no feedback anywhere. Rejecting (and logging)
 * those shapes here surfaces the mistake instead of letting it hide as
 * "the safelist entry that never worked":
 *
 *   - empty string (including all-whitespace) — matches nothing;
 *   - a trailing combinator (`>`, `+`, `~`), with or without trailing
 *     whitespace — a dangling combinator has no right-hand selector to
 *     combine with;
 *   - a trailing-dash token (e.g. `.foo-`) — an incomplete BEM
 *     prefix/typo; the matcher's lookahead
 *     (`(?=\s|\.|\:|,|\[|$)`) would never accept a bare trailing `-`
 *     anyway, since `-` is not one of the lookahead's accepted characters,
 *     so an entry shaped like this can never match anything.
 *
 * @param mixed $selector Candidate excluded-selector value.
 * @return bool
 */
function blocksy_child_perf_rucss_selector_is_valid( $selector ): bool {
	if ( ! is_string( $selector ) ) {
		return false;
	}

	$selector = trim( $selector );

	if ( '' === $selector ) {
		return false;
	}

	if ( preg_match( '/[>+~]\s*$/', $selector ) ) {
		return false;
	}

	if ( preg_match( '/-\s*$/', $selector ) ) {
		return false;
	}

	return true;
}

/**
 * `perfmatters_rucss_excluded_selectors` filter callback.
 *
 * Merges the shipped safelist (`inc/perf/data/rucss-safelist.php`, itself
 * run through the `blocksy_child_perf_rucss_selectors` filter so a site
 * can add/remove entries without editing that file) into whatever
 * Perfmatters/another filter already supplied, drops anything
 * blocksy_child_perf_rucss_selector_is_valid() rejects (logged), dedupes
 * and reindexes. Global Constraint 6: always returns an array.
 *
 * @param mixed $selectors Incoming excluded-selector list.
 * @return string[]
 */
function blocksy_child_perf_pmf_rucss_excluded_selectors( $selectors ) {
	$selectors = is_array( $selectors ) ? $selectors : [];

	$safelist = blocksy_child_perf_data( 'rucss-safelist' );

	/**
	 * Filter the shipped RUCSS safelist before it is merged in.
	 *
	 * A site adds its own JS-hydrated-markup selectors here (see this
	 * file's own docblock, "WHAT DOES NOT TRAVEL WITH THE THEME") rather
	 * than editing inc/perf/data/rucss-safelist.php.
	 *
	 * @param array $safelist The shipped, cross-client selector list.
	 */
	$safelist = apply_filters( 'blocksy_child_perf_rucss_selectors', $safelist );
	$safelist = is_array( $safelist ) ? $safelist : [];

	$merged = array_merge( $selectors, $safelist );

	$valid = [];

	foreach ( $merged as $selector ) {
		if ( ! blocksy_child_perf_rucss_selector_is_valid( $selector ) ) {
			error_log( '[blocksy-child][perf] rejected invalid RUCSS excluded selector: ' . var_export( $selector, true ) );
			continue;
		}

		$valid[] = $selector;
	}

	return array_values( array_unique( $valid ) );
}

add_filter( 'perfmatters_rucss_excluded_selectors', 'blocksy_child_perf_pmf_rucss_excluded_selectors' );
