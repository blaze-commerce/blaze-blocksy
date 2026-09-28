<?php
/**
 * Dequeue Assets — stop enqueuing stylesheets/scripts the rendered DOM
 * never uses.
 *
 * PURPOSE: Perfmatters' Remove Unused CSS can only prune what it sees in the
 * initial server-rendered HTML, so a plugin stylesheet that styles an
 * interaction-only UI (a popup, a dialog) either gets added to
 * `rucss_excluded_stylesheets` — and then loads in full, render-blocking,
 * on every page — or breaks that UI. The cleaner fix is to not enqueue what
 * is genuinely never used, so RUCSS never has to make the judgement call.
 * This module dequeues AND deregisters every handle in
 * `inc/perf/data/dequeue-handles.php` ∪ the `blocksy_child_perf_dequeue_handles`
 * filter. Deregistering (not only dequeuing) also removes derived artefacts:
 * Perfmatters' `<link rel=preload>` for the handle and core's
 * `dns-prefetch` for its host.
 *
 * PRIORITY 1000. A dequeue only sticks if it runs AFTER the enqueue that
 * added the handle; some plugins enqueue late (AW: Back In Stock Notifier at
 * 999 re-added its Bootstrap at priority 100 — verified on staging).
 *
 * PROTECTED HANDLES (Global Constraint 11) are refused even if the data file
 * or a filter asks for them, with an `error_log()` line: any handle starting
 * `ct-` (Blocksy) or `blocksy-child-` (this theme), plus `jquery-core`,
 * `jquery`, `woocommerce`, `wc-cart-fragments`.
 *
 * CAUTION: deregistering a handle that another enqueued asset lists as a
 * dependency makes WordPress silently drop that dependent asset too. Verify
 * with the relevant UI open before adding a handle.
 *
 * PROMOTED FROM: `bc-site-customizations/sites/alternateworlds/custom/includes/dequeue-unused-assets.php`
 * (priority 1000, dequeue + deregister, per-handle DOM verification) and
 * `bc-site-customizations/sites/mettahemp/custom/dequeue-unused.php`
 * (block-library family; its block utility CSS shim already ships in
 * `inc/perf/data/critical-css.php`, Task 4 — not duplicated here).
 *
 * MEASURED EVIDENCE: AW: 3 dead plugin sheets → HP 57→80 (mobile PSI,
 * aworld-retheme.blz.au, 63 of 63 critical-path requests render-blocking
 * before); AW jQuery UI theme alone 823 ms on the mobile trace (third-party
 * origin); Mettahemp ~16 KB unused CSS removed.
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: AW's conditional Back In Stock
 * Notifier Bootstrap drop (`cwginstock_bootstrap`, only on in-stock simple
 * products) and Mettahemp's checkout-only SMS-consent sheet are site
 * decisions — such a site adds them from its own
 * `blocksy_child_perf_dequeue_handles` callback, which runs at request time
 * (priority 1000) so it can apply its own conditional tags.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a handle must never be dequeued/deregistered by this module.
 *
 * @param string $handle Asset handle.
 * @return bool
 */
function blocksy_child_perf_dequeue_is_protected( string $handle ): bool {
	if ( 0 === strpos( $handle, 'ct-' ) || 0 === strpos( $handle, 'blocksy-child-' ) ) {
		return true;
	}

	return in_array( $handle, [ 'jquery-core', 'jquery', 'woocommerce', 'wc-cart-fragments' ], true );
}

/**
 * The resolved handle lists: data file ∪ `blocksy_child_perf_dequeue_handles`
 * filter, minus protected handles (each refusal logged), deduped.
 *
 * @return array{styles:string[],scripts:string[]}
 */
function blocksy_child_perf_dequeue_handles(): array {
	$data = blocksy_child_perf_data( 'dequeue-handles' );

	$handles = [
		'styles'  => isset( $data['styles'] ) && is_array( $data['styles'] ) ? $data['styles'] : [],
		'scripts' => isset( $data['scripts'] ) && is_array( $data['scripts'] ) ? $data['scripts'] : [],
	];

	/**
	 * Filter the handles to dequeue + deregister.
	 *
	 * Runs at `wp_enqueue_scripts` priority 1000, so a callback may use
	 * conditional tags. Protected handles are refused regardless.
	 *
	 * @param array{styles:string[],scripts:string[]} $handles
	 */
	$filtered = apply_filters( 'blocksy_child_perf_dequeue_handles', $handles );
	$filtered = is_array( $filtered ) ? $filtered : $handles;

	$out = [
		'styles'  => [],
		'scripts' => [],
	];

	foreach ( [ 'styles', 'scripts' ] as $type ) {
		$list = isset( $filtered[ $type ] ) && is_array( $filtered[ $type ] ) ? $filtered[ $type ] : [];

		foreach ( $list as $handle ) {
			if ( ! is_string( $handle ) || '' === $handle ) {
				continue;
			}

			if ( blocksy_child_perf_dequeue_is_protected( $handle ) ) {
				error_log( '[blocksy-child][perf] dequeue-assets: refused protected handle ' . $handle );
				continue;
			}

			$out[ $type ][] = $handle;
		}

		$out[ $type ] = array_values( array_unique( $out[ $type ] ) );
	}

	return $out;
}

/**
 * `wp_enqueue_scripts` @1000 — dequeue and deregister every resolved handle.
 *
 * @return void
 */
function blocksy_child_perf_dequeue_assets(): void {
	$handles = blocksy_child_perf_dequeue_handles();

	foreach ( $handles['styles'] as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}

	foreach ( $handles['scripts'] as $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}
}

/**
 * Register this module's hook. Front-end only (Global Constraint 5).
 *
 * @return void
 */
function blocksy_child_perf_dequeue_assets_register(): void {
	if ( ! blocksy_child_perf_is_frontend_request() ) {
		return;
	}

	add_action( 'wp_enqueue_scripts', 'blocksy_child_perf_dequeue_assets', 1000 );
}

blocksy_child_perf_dequeue_assets_register();
