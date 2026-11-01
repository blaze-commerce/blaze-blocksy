<?php
/**
 * Runs the perf suite then the core suite; exits 1 if either fails.
 *
 * Usage: php tests/run-all.php
 *
 * @package Blocksy_Child
 */

$failed = false;

foreach ( [ 'perf', 'core' ] as $suite ) {
	echo "=== {$suite} ===\n";
	$code = 0;
	passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . "/{$suite}/run.php" ), $code );
	if ( 0 !== $code ) {
		$failed = true;
	}
}

exit( $failed ? 1 : 0 );
