<?php
/**
 * Core (v2) test runner. Dependency-free; no WordPress install.
 *
 * Usage: php tests/core/run.php
 *
 * @package Blocksy_Child
 */

require_once __DIR__ . '/stubs.php';

$GLOBALS['bc_test_results'] = [
	'pass' => 0,
	'fail' => 0,
];

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
	bc_wp_stub_reset();
	require $test_file;
}

printf( "\n%d passed, %d failed\n", $GLOBALS['bc_test_results']['pass'], $GLOBALS['bc_test_results']['fail'] );

exit( $GLOBALS['bc_test_results']['fail'] > 0 ? 1 : 0 );
