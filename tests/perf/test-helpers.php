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

// Named so it can be removed at the end of this file — run.php requires
// every test file into ONE process, and a filter left behind would enable
// 'dequeue-assets' for every later file.
$bc_helpers_features_filter = function ( $features ) {
	$features[] = 'dequeue-assets';
	return $features;
};
add_filter( 'blocksy_child_perf_features', $bc_helpers_features_filter );

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

bc_test( 'blocksy_child_perf_style(): $css may be a callable, resolved at wp_head PRINT time (not registration time)', function () {
	$GLOBALS['bc_test_hooks']['wp_head'] = [];

	// Mutated AFTER blocksy_child_perf_style() has already registered its
	// wp_head callback below, simulating a filter a plugin/mu-plugin only
	// registers on init/plugins_loaded, after this module has already
	// loaded — the case that motivates $css accepting a callable at all.
	$late_value = 'body{color:green}';

	blocksy_child_perf_style( 'bc-perf-test-style-callable', function () use ( &$late_value ) {
		return $late_value;
	} );

	$late_value = 'body{color:purple}'; // changed after registration, before print.

	ob_start();
	foreach ( $GLOBALS['bc_test_hooks']['wp_head'] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}
	$output = ob_get_clean();

	assert_same(
		$output,
		'<style id="bc-perf-test-style-callable" data-no-optimize="1" data-no-minify="1">body{color:purple}</style>',
		'the value read at PRINT time (purple) wins, not the value at registration time (green)'
	);
} );

bc_test( 'blocksy_child_perf_style(): a bare content string equal to a function name is printed verbatim, never invoked', function () {
	$GLOBALS['bc_test_hooks']['wp_head'] = [];

	// "phpinfo" is itself a callable (a real PHP built-in) — is_callable('phpinfo')
	// is true. A naive is_callable()-first check would call it instead of
	// printing the literal string. Proves the is_string()-first hardening.
	blocksy_child_perf_style( 'bc-perf-test-style-string-fnname', 'phpinfo' );

	ob_start();
	foreach ( $GLOBALS['bc_test_hooks']['wp_head'] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}
	$output = ob_get_clean();

	assert_same(
		$output,
		'<style id="bc-perf-test-style-string-fnname" data-no-optimize="1" data-no-minify="1">phpinfo</style>',
		'the literal string "phpinfo" is printed as content, not executed as a function'
	);
} );

bc_test( 'blocksy_child_perf_style(): prints nothing (not even the tag) when the resolved CSS is empty', function () {
	$GLOBALS['bc_test_hooks']['wp_head'] = [];

	blocksy_child_perf_style( 'bc-perf-test-style-empty', function () {
		return '';
	} );

	ob_start();
	foreach ( $GLOBALS['bc_test_hooks']['wp_head'] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}
	$output = ob_get_clean();

	assert_same( $output, '', 'no <style> tag at all when the resolved content is empty' );
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

bc_test( 'blocksy_child_perf_script(): $js may be a callable, resolved at wp_footer print time, and empty result prints nothing', function () {
	$GLOBALS['bc_test_hooks']['wp_footer'] = [];

	blocksy_child_perf_script( 'bc-perf-test-script-callable', function () {
		return 'console.log(3);';
	} );

	ob_start();
	foreach ( $GLOBALS['bc_test_hooks']['wp_footer'] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}
	$output = ob_get_clean();

	assert_same(
		$output,
		'<script id="bc-perf-test-script-callable" data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false">console.log(3);</script>',
		'callable resolved at print time'
	);

	$GLOBALS['bc_test_hooks']['wp_footer'] = [];

	blocksy_child_perf_script( 'bc-perf-test-script-empty', function () {
		return '';
	} );

	ob_start();
	foreach ( $GLOBALS['bc_test_hooks']['wp_footer'] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}
	$output = ob_get_clean();

	assert_same( $output, '', 'no <script> tag at all when the resolved content is empty' );
} );

bc_test( 'blocksy_child_perf_script(): a bare content string equal to a function name is printed verbatim, never invoked', function () {
	$GLOBALS['bc_test_hooks']['wp_footer'] = [];

	// Same is_string()-first hardening as blocksy_child_perf_style() (see
	// that test above) — "phpinfo" is itself a callable (a real PHP
	// built-in), so a naive is_callable()-first check would call it
	// instead of printing the literal string.
	blocksy_child_perf_script( 'bc-perf-test-script-string-fnname', 'phpinfo' );

	ob_start();
	foreach ( $GLOBALS['bc_test_hooks']['wp_footer'] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}
	$output = ob_get_clean();

	assert_same(
		$output,
		'<script id="bc-perf-test-script-string-fnname" data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false">phpinfo</script>',
		'the literal string "phpinfo" is printed as content, not executed as a function'
	);
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_reset_state() — testing-only state reset.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_reset_state(): clears both the memoised enabled-features resolution and the script-id registry', function () {
	// Preconditions: both stores are non-empty from earlier in this file.
	assert_same( blocksy_child_perf_enabled( 'lcp-image' ), true, 'precondition: lcp-image enabled from the shared fixture' );
	assert_same( in_array( 'bc-perf-test-registry-a', blocksy_child_perf_script_ids(), true ), true, 'precondition: registry holds ids from earlier tests' );

	blocksy_child_perf_reset_state();

	// The registry is trivially observable as cleared.
	assert_same( blocksy_child_perf_script_ids(), [], 'script-id registry cleared' );

	// Proving the enabled-features MEMO (not just its inputs) was cleared:
	// register a new filter callback — invisible to a still-memoised
	// result — and confirm a fresh call picks it up.
	$late_filter = function ( $features ) {
		$features[] = 'media-hygiene';
		return $features;
	};
	add_filter( 'blocksy_child_perf_features', $late_filter );

	$features = blocksy_child_perf_enabled_features();

	remove_filter( 'blocksy_child_perf_features', $late_filter );

	assert_same( in_array( 'media-hygiene', $features, true ), true, 'post-reset resolution recomputes and sees a filter feature added after the original (pre-reset) resolution' );
} );

// -----------------------------------------------------------------------
// Teardown — do not leak this file's fixture into later test files.
// $blocksy_child_active_clients is also reset per file by bc_wp_stub_reset()
// (bootstrap.php); the BLOCKSY_CHILD_PERF_FEATURES constant cannot be
// undefined (see bootstrap.php).
// -----------------------------------------------------------------------
remove_filter( 'blocksy_child_perf_features', $bc_helpers_features_filter );
$GLOBALS['blocksy_child_active_clients'] = [];
blocksy_child_perf_reset_state();
