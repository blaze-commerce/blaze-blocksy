<?php
/**
 * Dependency-free test harness shared by the core and modules runners.
 *
 * @package Blocksy_Child
 */

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
