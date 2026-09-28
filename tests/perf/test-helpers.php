<?php
/**
 * Tests for inc/perf/helpers.php.
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/inc/perf/helpers.php';

// -----------------------------------------------------------------------
// Shared fixture for the enabled-set tests below.
//
// blocksy_child_perf_enabled_features() memoises in a static, so every
// source it reads (manifest, constant, filter) must be in place BEFORE
// the very first call anywhere in this process — later mutations are
// invisible to it. All assertions about that one resolution therefore
// live in this file, each in its own bc_test() case for readable output.
// -----------------------------------------------------------------------
$blocksy_child_active_clients = [
	[
		'slug'     => 'test-client',
		'dir'      => '/tmp/',
		'manifest' => [
			// Mixed case on purpose: proves lowercase normalisation.
			'perf' => [ 'LCP-Image', '*', 'bogus-feature-xyz' ],
		],
	],
];

if ( ! defined( 'BLOCKSY_CHILD_PERF_FEATURES' ) ) {
	define( 'BLOCKSY_CHILD_PERF_FEATURES', [ 'async-styles' ] );
}

add_filter( 'blocksy_child_perf_features', function ( $features ) {
	$features[] = 'dequeue-assets';
	return $features;
} );

$blocksy_child_perf_resolved = blocksy_child_perf_enabled_features();

bc_test( 'blocksy_child_perf_enabled_features(): unions manifest "perf" + BLOCKSY_CHILD_PERF_FEATURES + the filter', function () use ( $blocksy_child_perf_resolved ) {
	assert_same( in_array( 'lcp-image', $blocksy_child_perf_resolved, true ), true, 'manifest perf feature present, lowercased' );
	assert_same( in_array( 'async-styles', $blocksy_child_perf_resolved, true ), true, 'BLOCKSY_CHILD_PERF_FEATURES constant feature present' );
	assert_same( in_array( 'dequeue-assets', $blocksy_child_perf_resolved, true ), true, 'blocksy_child_perf_features filter feature present' );
} );

bc_test( "blocksy_child_perf_enabled(): '*' in the resolved set enables every known feature", function () {
	assert_same( blocksy_child_perf_enabled( 'content-visibility' ), true, 'not explicitly listed anywhere, but wildcard-enabled' );
	assert_same( blocksy_child_perf_enabled( 'lazy-rescan' ), true, 'wildcard-enabled' );
	assert_same( blocksy_child_perf_enabled( 'lcp-image' ), true, 'explicitly listed AND wildcard-enabled' );
} );

bc_test( 'blocksy_child_perf_enabled_features(): unknown feature name is dropped from the resolved set', function () use ( $blocksy_child_perf_resolved ) {
	assert_same( in_array( 'bogus-feature-xyz', $blocksy_child_perf_resolved, true ), false, 'unknown name never enters the resolved set' );
} );

bc_test( "blocksy_child_perf_enabled(): unknown feature name is not enabled, even though '*' is present", function () {
	assert_same( blocksy_child_perf_enabled( 'bogus-feature-xyz' ), false, "'*' only covers known features" );
} );

bc_test( 'blocksy_child_perf_enabled_features(): unknown feature name is logged exactly once via error_log()', function () {
	$messages = bc_test_error_log_messages();
	$count    = 0;

	foreach ( $messages as $message ) {
		if ( false !== strpos( $message, '[blocksy-child][perf] unknown feature: bogus-feature-xyz' ) ) {
			$count++;
		}
	}

	assert_same( $count, 1, 'exactly one log line for the one unknown feature name (memoised resolution runs its check loop once)' );
} );

// -----------------------------------------------------------------------
// Script-id registry.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_register_script_id() + blocksy_child_perf_script_ids(): registers and dedupes', function () {
	blocksy_child_perf_register_script_id( 'bc-perf-test-registry-a' );
	blocksy_child_perf_register_script_id( 'bc-perf-test-registry-b' );
	blocksy_child_perf_register_script_id( 'bc-perf-test-registry-a' ); // duplicate

	$ids = blocksy_child_perf_script_ids();

	assert_same( in_array( 'bc-perf-test-registry-a', $ids, true ), true, 'first id registered' );
	assert_same( in_array( 'bc-perf-test-registry-b', $ids, true ), true, 'second id registered' );
	assert_same( count( array_keys( $ids, 'bc-perf-test-registry-a', true ) ), 1, 'duplicate registration does not add a second entry' );
} );

bc_test( 'blocksy_child_perf_script(): registers its id at registration time (not print time)', function () {
	// Registration must happen even though nothing has fired wp_footer yet
	// (i.e. even on a request where the script is never printed).
	blocksy_child_perf_script( 'bc-perf-test-script-id', 'console.log(1);' );

	assert_same( in_array( 'bc-perf-test-script-id', blocksy_child_perf_script_ids(), true ), true, 'id present immediately after blocksy_child_perf_script() returns' );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_style() output.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_style(): prints exactly the mandated <style> markup via wp_head', function () {
	$GLOBALS['bc_test_hooks']['wp_head'] = []; // isolate: only our callback below fires.

	blocksy_child_perf_style( 'bc-perf-test-style', 'body{color:red}' );

	ob_start();
	foreach ( $GLOBALS['bc_test_hooks']['wp_head'] as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}
	$output = ob_get_clean();

	assert_same(
		$output,
		'<style id="bc-perf-test-style" data-no-optimize="1" data-no-minify="1">body{color:red}</style>',
		'exact attribute set + content (Global Constraint 4)'
	);
} );

bc_test( 'blocksy_child_perf_style(): $condition suppresses output at print time when falsy', function () {
	$GLOBALS['bc_test_hooks']['wp_head'] = [];

	blocksy_child_perf_style( 'bc-perf-test-style-cond', 'body{color:blue}', 1, function () {
		return false;
	} );

	ob_start();
	foreach ( $GLOBALS['bc_test_hooks']['wp_head'] as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}
	$output = ob_get_clean();

	assert_same( $output, '', 'nothing printed when $condition returns falsy' );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_script() output.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_script(): prints exactly the mandated <script> markup via wp_footer', function () {
	$GLOBALS['bc_test_hooks']['wp_footer'] = []; // isolate: only our callback below fires.

	blocksy_child_perf_script( 'bc-perf-test-script', 'console.log(2);' );

	ob_start();
	foreach ( $GLOBALS['bc_test_hooks']['wp_footer'] as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}
	$output = ob_get_clean();

	assert_same(
		$output,
		'<script id="bc-perf-test-script" data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false">console.log(2);</script>',
		'exact attribute set + content (Global Constraint 4)'
	);
} );
