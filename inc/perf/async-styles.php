<?php
/**
 * Async Styles — load non-critical plugin stylesheets without blocking render.
 *
 * PURPOSE: a plugin stylesheet that only styles something below the fold
 * or behind an interaction (FiboSearch's suggestions dropdown, a reviews
 * widget) still blocks first paint when it loads as a plain
 * `<link rel=stylesheet media=all>`. For every handle in
 * `inc/perf/data/async-style-handles.php` ∪ the
 * `blocksy_child_perf_async_style_handles` filter, `style_loader_tag` @20
 * swaps `media="…"` to `media="print" onload="this.media='all'"` (injecting
 * the pair when the tag has no media attribute) and appends a `<noscript>`
 * copy of the original tag. One body for all handles.
 *
 * Skipped (tag returned unchanged): tags containing `data-pmdelayedstyle`
 * (Perfmatters already delays them); tags already carrying `onload=`; a
 * stylesheet whose own media is `print` (it must never be flipped to `all`).
 * A non-`all` media query is restored as-is on load rather than widened.
 *
 * REFUSAL LIST (Global Constraint 11). Loading the used-CSS or a core
 * Blocksy stylesheet as print-swap produces a fully unstyled first paint
 * and a large CLS. Even when listed, these are returned unchanged and
 * logged via error_log(): `ct-main-styles`, `blocksy-child-style`,
 * `perfmatters-used-css`, any handle starting `ct-` or `blocksy-child-`,
 * and any tag whose href contains `main.min.css`.
 *
 * PROMOTED FROM: `bc-site-customizations/sites/austinnaturalmattress/custom/async-fibosearch-css.php`
 * (media swap regex, no-media fallback injection, noscript, pmdelayedstyle
 * skip), generalised from one handle to a data-file + filter list.
 *
 * MEASURED EVIDENCE: Austin — FiboSearch `style.min.css` (~41 KB) was the
 * render-blocking request left once it had been excluded from Perfmatters
 * RUCSS; its visible bar stays styled through the RUCSS safelist
 * (`inc/perf/data/rucss-safelist.php`), so only the dropdown waits.
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: which plugin stylesheets are safe to
 * defer is per client (it depends on what is above the fold there) — add
 * them via `blocksy_child_perf_async_style_handles`.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles to load asynchronously: data file ∪ filter.
 *
 * @return string[]
 */
function blocksy_child_perf_async_style_handles(): array {
	$extra = apply_filters( 'blocksy_child_perf_async_style_handles', [] );

	$handles = array_merge(
		blocksy_child_perf_data( 'async-style-handles' ),
		is_array( $extra ) ? $extra : []
	);

	return array_values( array_unique( array_filter( $handles, 'is_string' ) ) );
}

/**
 * Why a handle/tag must never be made async, or null when it may.
 *
 * @param string $handle
 * @param string $tag
 * @return string|null
 */
function blocksy_child_perf_async_style_refusal( string $handle, string $tag ): ?string {
	if ( in_array( $handle, [ 'ct-main-styles', 'blocksy-child-style', 'perfmatters-used-css' ], true ) ) {
		return 'protected handle';
	}

	if ( 0 === strpos( $handle, 'ct-' ) || 0 === strpos( $handle, 'blocksy-child-' ) ) {
		return 'protected handle prefix';
	}

	if ( preg_match( '#\bhref=([\'"])[^\'"]*main\.min\.css#i', $tag ) ) {
		return 'href contains main.min.css';
	}

	return null;
}

/**
 * Pure: rewrite a stylesheet tag to the async print-swap form, or return it unchanged.
 *
 * @param string $tag
 * @param string $handle
 * @return string
 */
function blocksy_child_perf_async_style_tag( $tag, $handle ) {
	$tag    = (string) $tag;
	$handle = (string) $handle;

	if ( ! in_array( $handle, blocksy_child_perf_async_style_handles(), true ) ) {
		return $tag;
	}

	$refusal = blocksy_child_perf_async_style_refusal( $handle, $tag );
	if ( null !== $refusal ) {
		error_log( '[blocksy-child][perf] async-styles: refused ' . $handle . ' (' . $refusal . ')' );
		return $tag;
	}

	if ( false !== strpos( $tag, 'data-pmdelayedstyle' ) || false !== stripos( $tag, 'onload=' ) ) {
		return $tag;
	}

	if ( preg_match( '/\smedia=([\'"])([^\'"]*)\1/i', $tag, $m ) ) {
		$media = trim( $m[2] );

		if ( 'print' === strtolower( $media ) ) {
			return $tag;
		}

		$restore = ( '' === $media ) ? 'all' : $media;
		$async   = str_replace( $m[0], ' media="print" onload="this.media=\'' . $restore . '\'"', $tag );
	} else {
		$async = (string) preg_replace( '#\s*/?>\s*$#', ' media="print" onload="this.media=\'all\'" />', $tag, 1 );
	}

	if ( $async === $tag ) {
		return $tag;
	}

	return rtrim( $async ) . '<noscript>' . rtrim( $tag ) . "</noscript>\n";
}

/**
 * `style_loader_tag` @20 callback — front-end only.
 *
 * @param string $tag
 * @param string $handle
 * @return string
 */
function blocksy_child_perf_async_filter_style_tag( $tag, $handle ) {
	if ( ! blocksy_child_perf_is_frontend_request() ) {
		return $tag;
	}

	return blocksy_child_perf_async_style_tag( $tag, $handle );
}

add_filter( 'style_loader_tag', 'blocksy_child_perf_async_filter_style_tag', 20, 2 );
