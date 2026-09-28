<?php
/**
 * Pins the no-opt-in no-op contract of inc/perf/loader.php.
 *
 * The theme release that ships the perf family must change NOTHING on an
 * existing site: no active client manifest, or an active manifest whose
 * JSON has no `"perf"` key, means no perf module is required, no hook is
 * registered, nothing is printed, no constant is defined, nothing is
 * logged, and no loader variable leaks into global scope.
 *
 * Every case runs in its own subprocess (bc_test_run_isolated(),
 * bootstrap.php): run.php requires every test file into one process, and
 * inc/perf/loader.php — plus any module another test file already loaded —
 * would otherwise make "requiring the loader" either a no-op by accident
 * (require_once) or polluted by earlier state.
 *
 * The positive control (`"perf": ["rucss-safelist"]`) proves the probe is
 * not vacuous: the same harness DOES see exactly one module requested.
 *
 * @package Blocksy_Child
 */

/**
 * Build the subprocess body: bootstrap, set the active-client list, snapshot
 * state, require inc/perf/loader.php, and report every observable delta.
 *
 * @param array $active_clients Value for $GLOBALS['blocksy_child_active_clients'].
 * @return string
 */
function bc_noop_probe_body( array $active_clients ): string {
	return sprintf(
		<<<'PHP'
require %s;
$GLOBALS['blocksy_child_active_clients'] = %s;

$bc_before_hooks     = $GLOBALS['bc_test_hooks'];
$bc_before_constants = get_defined_constants( true )['user'] ?? [];
clearstatcache();
$bc_before_log       = (string) @file_get_contents( $GLOBALS['bc_test_error_log_file'] );
$bc_before_globals   = array_keys( $GLOBALS );

ob_start();
require %s;
$bc_after_globals = array_keys( $GLOBALS );
$bc_output = ob_get_clean();

// blocksy_child_perf_state is helpers.php's own namespaced memo (the resolved
// feature set) — intended; anything else (e.g. $feature / $file) is a leak.
$bc_new_globals = array_values( array_diff( $bc_after_globals, $bc_before_globals, [ 'bc_before_globals', 'blocksy_child_perf_state' ] ) );
$bc_new_constants = array_keys( array_diff_key( get_defined_constants( true )['user'] ?? [], $bc_before_constants ) );
clearstatcache();
$bc_after_log = (string) @file_get_contents( $GLOBALS['bc_test_error_log_file'] );

$bc_enabled_each = [];
foreach ( blocksy_child_perf_known_features() as $bc_f ) {
	$bc_enabled_each[ $bc_f ] = blocksy_child_perf_enabled( $bc_f );
}

echo json_encode( [
	'output'           => $bc_output,
	'enabled_features' => blocksy_child_perf_enabled_features(),
	'loaded_modules'   => $GLOBALS['bc_wp_stub']['loaded_modules'],
	'hooks_unchanged'  => $GLOBALS['bc_test_hooks'] === $bc_before_hooks,
	'hook_names'       => array_keys( $GLOBALS['bc_test_hooks'] ),
	'new_constants'    => $bc_new_constants,
	'log_unchanged'    => $bc_after_log === $bc_before_log,
	'log_delta'        => substr( $bc_after_log, strlen( $bc_before_log ) ),
	'script_ids'       => blocksy_child_perf_script_ids(),
	'enabled_each'     => $bc_enabled_each,
	'new_globals'      => $bc_new_globals,
] );
PHP,
		var_export( __DIR__ . '/bootstrap.php', true ),
		var_export( $active_clients, true ),
		var_export( dirname( __DIR__, 2 ) . '/inc/perf/loader.php', true )
	);
}

/**
 * Shared no-op assertions for one probe result.
 *
 * @param array  $r     Decoded probe result.
 * @param string $label Case label for messages.
 * @return void
 */
function bc_noop_assert_all( array $r, string $label ): void {
	assert_same( $r['output'], '', "{$label}: loader printed output" );
	assert_same( $r['enabled_features'], [], "{$label}: resolved feature set" );
	assert_same( $r['loaded_modules'], [], "{$label}: modules requested via blocksy_child_load_module()" );
	assert_same( $r['hooks_unchanged'], true, "{$label}: hook table changed (now: " . implode( ',', $r['hook_names'] ) . ')' );
	assert_same( $r['new_constants'], [], "{$label}: new user constants" );
	assert_same( $r['log_unchanged'], true, "{$label}: error log changed: " . $r['log_delta'] );
	assert_same( $r['script_ids'], [], "{$label}: registered script ids" );
	assert_same( $r['new_globals'], [], "{$label}: loader leaked globals" );

	foreach ( $r['enabled_each'] as $feature => $enabled ) {
		assert_same( $enabled, false, "{$label}: blocksy_child_perf_enabled('{$feature}')" );
	}
}

bc_test( 'no-op: no active client -> loader loads nothing, registers nothing, prints/logs/defines/leaks nothing', function () {
	$r = bc_test_run_isolated( bc_noop_probe_body( [] ) );
	bc_noop_assert_all( $r, 'no client' );
	assert_same( count( $r['enabled_each'] ), count( bc_noop_known_features() ), 'every known feature was probed' );
} );

bc_test( 'no-op: active manifest with "features" but no "perf" key -> loader loads nothing, registers nothing, prints/logs/defines/leaks nothing', function () {
	$clients = [
		[
			'slug'     => 'legacy-site',
			'dir'      => '/tmp/legacy-site/',
			'manifest' => [
				'name'     => 'Legacy Site',
				'slug'     => 'legacy-site',
				'active'   => true,
				'features' => [ 'wishlist-offcanvas', 'product-tabs' ],
			],
		],
	];

	$r = bc_test_run_isolated( bc_noop_probe_body( $clients ) );
	bc_noop_assert_all( $r, 'features-only manifest' );
} );

bc_test( 'no-op positive control: "perf": ["rucss-safelist"] -> exactly that one module is requested', function () {
	$clients = [
		[
			'slug'     => 'new-site',
			'dir'      => '/tmp/new-site/',
			'manifest' => [
				'slug'   => 'new-site',
				'active' => true,
				'perf'   => [ 'rucss-safelist' ],
			],
		],
	];

	$r = bc_test_run_isolated( bc_noop_probe_body( $clients ) );

	assert_same( $r['enabled_features'], [ 'rucss-safelist' ], 'resolved feature set' );
	assert_same( $r['loaded_modules'], [ 'inc/perf/rucss-safelist.php' ], 'modules requested' );
	assert_same( $r['enabled_each']['rucss-safelist'], true, 'rucss-safelist enabled' );
	assert_same( array_keys( array_filter( $r['enabled_each'] ) ), [ 'rucss-safelist' ], 'only rucss-safelist enabled' );
	assert_same( $r['new_globals'], [], 'loader leaked globals' );
} );

/**
 * The known-feature list, read in a subprocess so this file never requires
 * inc/perf/helpers.php into the shared run.php process itself.
 *
 * @return string[]
 */
function bc_noop_known_features(): array {
	return bc_test_run_isolated( sprintf(
		"require %s;\nrequire_once %s;\necho json_encode( blocksy_child_perf_known_features() );",
		var_export( __DIR__ . '/bootstrap.php', true ),
		var_export( dirname( __DIR__, 2 ) . '/inc/perf/helpers.php', true )
	) );
}
