<?php
/**
 * Module test runner (tests/modules/test-<id>.php). Dependency-free; no WordPress install.
 *
 * Usage: php tests/modules/run.php
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__ ) . '/core/stubs.php';
require_once dirname( __DIR__ ) . '/core/harness.php';

$test_files = glob( __DIR__ . '/test-*.php' );
sort( $test_files );

foreach ( $test_files as $test_file ) {
	bc_wp_stub_reset();
	require $test_file;
}

printf( "\n%d passed, %d failed\n", $GLOBALS['bc_test_results']['pass'], $GLOBALS['bc_test_results']['fail'] );

exit( $GLOBALS['bc_test_results']['fail'] > 0 ? 1 : 0 );
