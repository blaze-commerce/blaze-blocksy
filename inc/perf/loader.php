<?php
/**
 * Perf Module Loader — bootstraps the opt-in PageSpeed module family.
 *
 * PURPOSE: Requires inc/perf/helpers.php unconditionally, then loads each
 * inc/perf/<feature>.php file whose feature name is in the resolved
 * enabled set (blocksy_child_perf_enabled_features(), in helpers.php).
 * Runs at theme-load time — required directly from functions.php, not on
 * a hook (Global Constraint 3) — so that even the earliest theme/plugin
 * hooks a feature module wants to register (e.g. `option_perfmatters_options`)
 * are available.
 *
 * PROMOTED FROM: generalises the pattern used across individual client
 * PageSpeed passes into shared theme infrastructure, per
 * `W:\BLAZE COMMERCE\pagespeed-docs\RECOMMENDATIONS-90plus-in-3-hours.md`
 * (the plan spec this module family implements).
 *
 * MEASURED EVIDENCE: none directly attaches to this file — it is a
 * dispatcher and prints nothing on its own. Before/after PageSpeed numbers
 * live in each feature module's own docblock (later tasks).
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: nothing here is client-specific.
 * The per-client opt-in list (`clients/<slug>/manifest.json`'s `"perf"`
 * key) is what does not travel — this file only reads it, via
 * blocksy_child_perf_enabled().
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Required unconditionally: every perf helper (opt-in resolution, the
// style/script print helpers, the script-id registry, the data-file
// loader) must exist regardless of which — if any — feature is enabled.
require_once BLOCKSY_CHILD_PATH . 'inc/perf/helpers.php';

/**
 * Feature -> file map. One file per feature, all under inc/perf/.
 *
 * `lazy-rescan` maps the same way as every other feature — the file
 * itself (a later task) is what enqueues assets/js/perf-lazy-rescan.js.
 */
$feature_files = [];
foreach ( blocksy_child_perf_known_features() as $feature ) {
	$feature_files[ $feature ] = 'inc/perf/' . $feature . '.php';
}

foreach ( $feature_files as $feature => $file ) {
	if ( ! blocksy_child_perf_enabled( $feature ) ) {
		continue;
	}

	// blocksy_child_load_module() (inc/loader.php) already tolerates a
	// missing file with its own error_log() + no fatal — later tasks may
	// not have shipped inc/perf/<feature>.php yet.
	blocksy_child_load_module( $file );
}
