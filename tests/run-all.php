<?php
/**
 * Runs the perf, core and tools suites; exits 1 if any fails.
 *
 * Usage: php tests/run-all.php
 *
 * @package Blocksy_Child
 */

$failed = false;

$suites = [
	'perf'    => 'perf/run.php',
	'core'    => 'core/run.php',
	'modules' => 'modules/run.php',
	'tools'   => 'tools/test-check.php',
];

foreach ( $suites as $suite => $script ) {
	echo "=== {$suite} ===\n";
	$code = 0;
	passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . '/' . $script ), $code );
	if ( 0 !== $code ) {
		$failed = true;
	}
}

exit( $failed ? 1 : 0 );
