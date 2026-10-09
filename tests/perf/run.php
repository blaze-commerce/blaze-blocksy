<?php
/**
 * Perf module test runner — dependency-free PHP CLI harness. No
 * WordPress install is used; see bootstrap.php for the WP stubs.
 *
 * Usage: php tests/perf/run.php
 *
 * @package Blocksy_Child
 */

require_once __DIR__ . '/bootstrap.php';

$GLOBALS['bc_test_results'] = [
	'pass' => 0,
	'fail' => 0,
];

/**
 * Register and immediately run a single test case.
 *
 * @param string   $name Test case description.
 * @param callable $fn   Test body. Throw (directly, or via assert_same()) to fail it.
 * @return void
 */
function bc_test( $name, $fn ) {
	try {
		$fn();
		$GLOBALS['bc_test_results']['pass']++;
		echo "PASS: {$name}\n";
	} catch ( \Throwable $e ) {
		$GLOBALS['bc_test_results']['fail']++;
		echo "FAIL: {$name} -- {$e->getMessage()}\n";
	}
}

/**
 * Minimal strict-equality assertion. Throws on mismatch so bc_test() can
 * catch it and report a FAIL without stopping the rest of the run.
 *
 * @param mixed  $actual
 * @param mixed  $expected
 * @param string $message
 * @return void
 */
function assert_same( $actual, $expected, $message = '' ) {
	if ( $actual !== $expected ) {
		throw new \RuntimeException( sprintf(
			'%s (expected %s, got %s)',
			'' !== $message ? $message : 'assert_same failed',
			var_export( $expected, true ),
			var_export( $actual, true )
		) );
	}
}

$test_files = glob( __DIR__ . '/test-*.php' );
sort( $test_files );

foreach ( $test_files as $test_file ) {
	// Give each test-*.php file a clean slate. blocksy_child_perf_reset_state()
	// only exists once inc/perf/helpers.php has been loaded (by whichever
	// test file requires it first) and only when BC_PERF_TESTING is true.
	if ( function_exists( 'blocksy_child_perf_reset_state' ) ) {
		blocksy_child_perf_reset_state();
	}

	// Same clean-slate reasoning for the shared WP conditional/data stubs
	// (bootstrap.php) — bc_wp_stub_reset() always exists (declared
	// unconditionally in bootstrap.php), unlike blocksy_child_perf_reset_state().
	bc_wp_stub_reset();

	require $test_file;
}

printf( "\n%d passed, %d failed\n", $GLOBALS['bc_test_results']['pass'], $GLOBALS['bc_test_results']['fail'] );

exit( $GLOBALS['bc_test_results']['fail'] > 0 ? 1 : 0 );
