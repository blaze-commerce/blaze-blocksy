<?php
/**
 * Runs the perf, core, modules and tools suites, then the module JS tests when node is available; exits 1 if any fails.
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

// Module JS unit tests (modules/<id>/assets/*.test.mjs, node:test). Node 22 takes a glob, not a folder.
echo "=== js ===\n";
$node = trim( (string) shell_exec( 'command -v node 2>/dev/null' ) );
if ( '' === $node ) {
	echo "SKIP: node not found; run node --test \"modules/**/*.test.mjs\" where node is available\n";
} else {
	$code = 0;
	chdir( dirname( __DIR__ ) );
	passthru( escapeshellarg( $node ) . ' --test ' . escapeshellarg( 'modules/**/*.test.mjs' ), $code );
	if ( 0 !== $code ) {
		$failed = true;
	}
}

exit( $failed ? 1 : 0 );
