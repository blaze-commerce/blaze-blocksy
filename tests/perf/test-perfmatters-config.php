<?php
/**
 * Tests for inc/perf/perfmatters-config.php.
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/inc/perf/helpers.php';
require_once dirname( __DIR__, 2 ) . '/inc/perf/perfmatters-config.php';

$bc_pm_defaults_path = dirname( __DIR__, 2 ) . '/inc/perf/data/perfmatters-defaults.json';
$bc_pm_defaults      = json_decode( (string) file_get_contents( $bc_pm_defaults_path ), true );

// -----------------------------------------------------------------------
// inc/perf/data/perfmatters-defaults.json — structural checks.
// -----------------------------------------------------------------------

bc_test( 'perfmatters-defaults.json decodes to a non-empty array', function () use ( $bc_pm_defaults ) {
	assert_same( is_array( $bc_pm_defaults ), true, 'valid JSON object' );
	assert_same( count( $bc_pm_defaults ) > 0, true, 'non-empty' );
} );

bc_test( 'perfmatters-defaults.json: no analytics/cdn/login_url* keys (unmanaged, removed entirely)', function () use ( $bc_pm_defaults ) {
	assert_same( isset( $bc_pm_defaults['analytics'] ), false, 'analytics absent' );
	assert_same( isset( $bc_pm_defaults['cdn'] ), false, 'cdn absent' );

	foreach ( array_keys( $bc_pm_defaults ) as $key ) {
		assert_same( 0 === strpos( $key, 'login_url' ), false, "no login_url* key present ({$key} would match)" );
	}
} );

bc_test( 'perfmatters-defaults.json: no LIST_KEYS path holds a string', function () use ( $bc_pm_defaults ) {
	foreach ( blocksy_child_perf_pm_list_keys() as $path ) {
		[ $section, $key ] = explode( '.', $path, 2 );

		if ( ! isset( $bc_pm_defaults[ $section ][ $key ] ) ) {
			continue;
		}

		assert_same( is_array( $bc_pm_defaults[ $section ][ $key ] ), true, "{$path} is an array, not a string" );
	}
} );

bc_test( 'perfmatters-defaults.json: preload.early_hint_types excludes script', function () use ( $bc_pm_defaults ) {
	assert_same( in_array( 'script', $bc_pm_defaults['preload']['early_hint_types'], true ), false, 'script excluded' );
	assert_same( in_array( 'font', $bc_pm_defaults['preload']['early_hint_types'], true ), true, 'font present' );
	assert_same( in_array( 'image', $bc_pm_defaults['preload']['early_hint_types'], true ), true, 'image present' );
	assert_same( in_array( 'style', $bc_pm_defaults['preload']['early_hint_types'], true ), true, 'style present' );
} );

bc_test( 'perfmatters-defaults.json: lazy_loading_exclusions carries the Task 6 hero poster class', function () use ( $bc_pm_defaults ) {
	assert_same( in_array( 'bc-perf-hero__poster', $bc_pm_defaults['lazyload']['lazy_loading_exclusions'], true ), true, 'controller ruling: must never be lazy-loaded' );
} );

bc_test( 'perfmatters-defaults.json: assets.rucss_excluded_selectors / rucss_excluded_stylesheets ship empty (safelist travels via Task 3 filter)', function () use ( $bc_pm_defaults ) {
	assert_same( $bc_pm_defaults['assets']['rucss_excluded_selectors'], [] );
	assert_same( $bc_pm_defaults['assets']['rucss_excluded_stylesheets'], [] );
} );

bc_test( 'perfmatters-defaults.json: never ships main.min.css in an RUCSS exclusion list (Global Constraint 11)', function () use ( $bc_pm_defaults ) {
	assert_same( in_array( 'main.min.css', $bc_pm_defaults['assets']['rucss_excluded_selectors'], true ), false );
	assert_same( in_array( 'main.min.css', $bc_pm_defaults['assets']['rucss_excluded_stylesheets'], true ), false );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_pm_merge_options() — merge semantics.
// Ported from Houston's test-merge-logic.php / 86eyhbd6j checks.
// -----------------------------------------------------------------------

bc_test( 'merge_options(): a 76-byte stub row is fully taken over by the shipped config', function () use ( $bc_pm_defaults ) {
	$stub   = [ 'fonts' => [ 'subsets' => [ 'latin' ] ] ]; // the real-world prod/v1 case.
	$merged = blocksy_child_perf_pm_merge_options( $stub, $bc_pm_defaults );

	assert_same( $merged['assets']['delay_js'], '1', 'delay_js taken over' );
	assert_same( $merged['assets']['delay_js_behavior'], 'all', 'delay_js_behavior taken over' );
	assert_same( $merged['assets']['remove_unused_css'], '1', 'RUCSS taken over' );
	assert_same( $merged['assets']['rucss_excluded_selectors'], [], 'RUCSS exclusions taken over' );
	assert_same( $merged['lazyload']['lazy_loading'], '1', 'lazyload taken over' );
	assert_same( $merged['lazyload']['youtube_preview_thumbnails'], '1', 'youtube thumbnails taken over' );
	assert_same( $merged['lazyload']['lazy_loading_exclusions'], [ 'blaze-lcp-image', 'bc-perf-lcp', 'bc-perf-hero__poster' ], 'lazyload exclusions taken over' );
} );

bc_test( 'merge_options(): list-valued keys are replaced wholesale, never element-unioned', function () {
	$stored = [ 'assets' => [ 'delay_js_exclusions' => [ 'old-handle' ] ] ];
	$config = [ 'assets' => [ 'delay_js_exclusions' => [ 'new-handle' ] ] ];

	$merged = blocksy_child_perf_pm_merge_options( $stored, $config );

	assert_same( $merged['assets']['delay_js_exclusions'], [ 'new-handle' ], 'old-handle is gone entirely, not unioned' );
} );

bc_test( 'merge_options(): keys the config does not mention keep their stored value, including whole unmanaged sections', function () {
	$stored = [
		'assets'    => [
			'delay_js'  => '0',
			'body_code' => '<div>kept</div>',
		],
		'analytics' => [ 'tracking_id' => 'UA-KEPT' ],
	];
	$config = [
		'assets' => [ 'delay_js' => '1' ], // does not mention body_code.
	];

	$merged = blocksy_child_perf_pm_merge_options( $stored, $config );

	assert_same( $merged['assets']['delay_js'], '1', 'key the config mentions is overwritten' );
	assert_same( $merged['assets']['body_code'], '<div>kept</div>', 'key within a shared section the config does not mention is kept' );
	assert_same( $merged['analytics'], [ 'tracking_id' => 'UA-KEPT' ], 'whole section the config does not mention is kept' );
} );

bc_test( 'merge_options(): merge(config, config) === config (idempotent)', function () use ( $bc_pm_defaults ) {
	$merged = blocksy_child_perf_pm_merge_options( $bc_pm_defaults, $bc_pm_defaults );

	assert_same( $merged, $bc_pm_defaults );
} );

bc_test( 'merge_options(): type guard turns a newline-separated string into an array for a LIST_KEYS path', function () {
	$stored = [ 'assets' => [ 'delay_js_exclusions' => "handle-a\nhandle-b\n\n  \nhandle-c" ] ];

	$merged = blocksy_child_perf_pm_merge_options( $stored, [] );

	assert_same( $merged['assets']['delay_js_exclusions'], [ 'handle-a', 'handle-b', 'handle-c' ], 'split on newline, trimmed, empty lines removed' );
} );

bc_test( 'merge_options(): type guard turns an int/float into a string for a STRING_NUMERIC_KEYS path', function () {
	$stored = [ 'lazyload' => [ 'threshold' => 300, 'exclude_leading_images' => 3.0 ] ];

	$merged = blocksy_child_perf_pm_merge_options( $stored, [] );

	assert_same( $merged['lazyload']['threshold'], '300', 'int cast to string' );
	assert_same( $merged['lazyload']['exclude_leading_images'], '3', 'float cast to string' );
} );

bc_test( 'merge_options(): type guard leaves minify_css_exclusions (a string field, NOT a list key) untouched', function () {
	$stored = [ 'assets' => [ 'minify_css_exclusions' => "one\ntwo" ] ];

	$merged = blocksy_child_perf_pm_merge_options( $stored, [] );

	assert_same( $merged['assets']['minify_css_exclusions'], "one\ntwo", 'left as a single string, not split into an array' );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_pm_shipped_config() — file loading + memoisation +
// site override.
// -----------------------------------------------------------------------

bc_test( 'shipped_config(): decodes inc/perf/data/perfmatters-defaults.json when no custom/perfmatters.json exists', function () use ( $bc_pm_defaults ) {
	unset( $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] );

	$config = blocksy_child_perf_pm_shipped_config();

	assert_same( $config, $bc_pm_defaults, 'no custom/ directory in this worktree, so defaults are the whole story' );
	assert_same( blocksy_child_perf_pm_shipped_config(), $config, 'second call returns the memoised value without recomputing' );
} );

bc_test( 'shipped_config(): a site override file wins over the defaults for a list key, and does not touch keys it does not mention', function () use ( $bc_pm_defaults ) {
	$override_path = tempnam( sys_get_temp_dir(), 'bc_pm_site_' );
	file_put_contents( $override_path, wp_json_encode( [ 'assets' => [ 'delay_js_exclusions' => [ 'site-only-handle' ] ] ] ) );

	$override_filter = function () use ( $override_path ) {
		return $override_path;
	};
	add_filter( 'blocksy_child_perf_pm_site_config_path', $override_filter );

	unset( $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] );
	$config = blocksy_child_perf_pm_shipped_config();

	assert_same( $config['assets']['delay_js_exclusions'], [ 'site-only-handle' ], 'site override replaces the defaults list wholesale' );
	assert_same( $config['assets']['delay_js'], $bc_pm_defaults['assets']['delay_js'], 'key the override does not mention keeps the defaults value' );

	remove_action( 'blocksy_child_perf_pm_site_config_path', $override_filter );
	unlink( $override_path );
	unset( $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] );
} );

// -----------------------------------------------------------------------
// The registered filters.
// -----------------------------------------------------------------------

bc_test( 'option_perfmatters_options filter: merges the shipped config over an array stored row', function () use ( $bc_pm_defaults ) {
	unset( $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] );

	$stub     = [ 'fonts' => [ 'subsets' => [ 'latin' ] ] ];
	$filtered = apply_filters( 'option_perfmatters_options', $stub );
	$expected = blocksy_child_perf_pm_merge_options( $stub, $bc_pm_defaults );

	assert_same( $filtered, $expected );
} );

bc_test( 'option_perfmatters_options filter: returns the shipped config itself when the stored row is not an array', function () use ( $bc_pm_defaults ) {
	unset( $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] );

	assert_same( apply_filters( 'option_perfmatters_options', false ), $bc_pm_defaults );
	assert_same( apply_filters( 'option_perfmatters_options', '' ), $bc_pm_defaults );
} );

bc_test( 'option_perfmatters_options filter: null shipped config + non-array stored row -> [] (Global Constraint 6)', function () {
	$GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] = null;

	assert_same( blocksy_child_perf_pm_filter_option( false ), [] );
	assert_same( blocksy_child_perf_pm_filter_option( 'not-an-array' ), [] );

	unset( $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] );
} );

bc_test( 'option_perfmatters_options filter: null shipped config + array stored row -> stored row unchanged', function () {
	$GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] = null;

	$stored = [ 'fonts' => [ 'subsets' => [ 'latin' ] ] ];
	assert_same( blocksy_child_perf_pm_filter_option( $stored ), $stored );

	unset( $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] );
} );

bc_test( 'default_option_perfmatters_options filter: supplies the shipped config to a site with no row at all', function () use ( $bc_pm_defaults ) {
	unset( $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] );

	assert_same( apply_filters( 'default_option_perfmatters_options', false ), $bc_pm_defaults );
} );

bc_test( 'default_option_perfmatters_options filter: null shipped config + non-array default -> [] (Global Constraint 6)', function () {
	$GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] = null;

	assert_same( blocksy_child_perf_pm_filter_default_option( false ), [] );

	unset( $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] );
} );

// -----------------------------------------------------------------------
// Admin notice.
// -----------------------------------------------------------------------

bc_test( 'admin_notice(): registered on admin_notices', function () {
	assert_same( isset( $GLOBALS['bc_test_hooks']['admin_notices'] ), true, 'hook has at least one registered callback' );

	$found = false;
	foreach ( $GLOBALS['bc_test_hooks']['admin_notices'] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			if ( 'blocksy_child_perf_pm_admin_notice' === $callback ) {
				$found = true;
			}
		}
	}
	assert_same( $found, true, 'blocksy_child_perf_pm_admin_notice registered' );
} );

bc_test( 'admin_notice(): prints nothing and does not fatal when Perfmatters is absent (Global Constraint 10)', function () {
	ob_start();
	blocksy_child_perf_pm_admin_notice();
	$output = ob_get_clean();

	assert_same( $output, '', 'no PERFMATTERS_VERSION defined and no perfmatters_admin_menu() -> silent no-op' );
} );

// Leave the shared memo clean for anything that requires this file again
// within the same process (defensive; run.php requires each test file once).
unset( $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] );
