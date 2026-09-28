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

// Absolute paths handed into the generated subprocess scripts below — a
// subprocess has none of this file's context, so every path it needs must
// be embedded as a literal (via var_export()) at generation time.
$bc_pm_bootstrap_path = __DIR__ . '/bootstrap.php';
$bc_pm_module_path    = dirname( __DIR__, 2 ) . '/inc/perf/perfmatters-config.php';
$bc_pm_mu_plugin_path = dirname( __DIR__, 2 ) . '/inc/perf/mu-plugins/bc-perfmatters-config.php';
$bc_pm_theme_dir      = dirname( __DIR__, 2 );

// Every temp script path bc_pm_run_isolated() has ever created, so a test
// near the end of this file can assert none were left behind — see that
// test for why this is tracked rather than glob()'d after the fact.
$GLOBALS['bc_pm_isolated_script_paths'] = [];

/**
 * Run inline PHP as a fresh subprocess and decode its JSON stdout.
 *
 * Some of this module's behaviours are only observable once per PHP
 * process: BC_PERFMATTERS_CONFIG_AS_CODE is a constant (can't be
 * redefined to test both the opt-out and the normal path in the same
 * run), and BLOCKSY_CHILD_PERF_CONFIG_LOADED's whole point is "stays
 * true for the rest of this process". A subprocess gives each such test
 * a clean process instead of requiring test-file-level process isolation
 * for the whole suite.
 *
 * tempnam() itself creates the temp file (a real 0-byte file at the exact
 * path it returns) — the script is written to THAT path, not a derived
 * one, so there is exactly one file to clean up, and the cleanup runs in
 * a `finally` so it happens whether the subprocess (or JSON decoding)
 * throws or not.
 *
 * @param string $body PHP code (no opening `<?php` tag) that MUST end by
 *                      echoing exactly one JSON-encoded value.
 * @return array Decoded JSON result.
 */
function bc_pm_run_isolated( string $body ): array {
	$script_path = tempnam( sys_get_temp_dir(), 'bc_pm_iso_' );

	$GLOBALS['bc_pm_isolated_script_paths'][] = $script_path;

	try {
		file_put_contents( $script_path, "<?php\n" . $body . "\n" );

		$command = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $script_path ) . ' 2>&1';

		$output    = [];
		$exit_code = 0;
		exec( $command, $output, $exit_code );

		if ( 0 !== $exit_code ) {
			throw new \RuntimeException( 'subprocess exited ' . $exit_code . ': ' . implode( "\n", $output ) );
		}

		$decoded = json_decode( implode( "\n", $output ), true );

		if ( ! is_array( $decoded ) ) {
			throw new \RuntimeException( 'subprocess did not print valid JSON: ' . implode( "\n", $output ) );
		}

		return $decoded;
	} finally {
		@unlink( $script_path );
	}
}

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

	// bootstrap.php's add_filter()/add_action() share one hook registry, so
	// remove_action() here removes the filter callback registered above —
	// it is not actually removing a WordPress "action".
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

// -----------------------------------------------------------------------
// Process-scoped behaviours — the BC_PERFMATTERS_CONFIG_AS_CODE opt-out
// and the BLOCKSY_CHILD_PERF_CONFIG_LOADED double-require guard. Both
// hinge on state (a constant, a "stays true forever" define) that this
// process has already set one way for every test above, so each is
// exercised in its own subprocess via bc_pm_run_isolated().
// -----------------------------------------------------------------------

bc_test( 'BC_PERFMATTERS_CONFIG_AS_CODE=false: no filters registered, BLOCKSY_CHILD_PERF_CONFIG_LOADED never defined', function () use ( $bc_pm_bootstrap_path, $bc_pm_module_path ) {
	$body = sprintf(
		<<<'PHP'
define('BC_PERFMATTERS_CONFIG_AS_CODE', false);
require %s;
require_once %s;
echo json_encode([
	'option_filters'  => count($GLOBALS['bc_test_hooks']['option_perfmatters_options'][10] ?? []),
	'default_filters' => count($GLOBALS['bc_test_hooks']['default_option_perfmatters_options'][10] ?? []),
	'admin_notices'   => count($GLOBALS['bc_test_hooks']['admin_notices'][10] ?? []),
	'loaded_defined'  => defined('BLOCKSY_CHILD_PERF_CONFIG_LOADED') ? 1 : 0,
]);
PHP,
		var_export( $bc_pm_bootstrap_path, true ),
		var_export( $bc_pm_module_path, true )
	);

	$result = bc_pm_run_isolated( $body );

	assert_same( $result['option_filters'], 0, 'option_perfmatters_options filter never registered' );
	assert_same( $result['default_filters'], 0, 'default_option_perfmatters_options filter never registered' );
	assert_same( $result['admin_notices'], 0, 'admin_notices callback never registered' );
	assert_same( $result['loaded_defined'], 0, 'BLOCKSY_CHILD_PERF_CONFIG_LOADED never defined — the module returned before reaching it' );
} );

bc_test( 'mu-plugin shim require_once + a later require_once of the module: filter registered exactly once', function () use ( $bc_pm_bootstrap_path, $bc_pm_module_path, $bc_pm_mu_plugin_path, $bc_pm_theme_dir ) {
	$body = sprintf(
		<<<'PHP'
require %s;
function get_stylesheet_directory() { return %s; }
require_once %s; // the mu-plugin shim — requires helpers.php + the module if both exist.
require_once %s; // the theme loader's own later require_once of the same module file.
echo json_encode([
	'option_filters' => count($GLOBALS['bc_test_hooks']['option_perfmatters_options'][10] ?? []),
	'loaded_defined' => defined('BLOCKSY_CHILD_PERF_CONFIG_LOADED') ? 1 : 0,
]);
PHP,
		var_export( $bc_pm_bootstrap_path, true ),
		var_export( $bc_pm_theme_dir, true ),
		var_export( $bc_pm_mu_plugin_path, true ),
		var_export( $bc_pm_module_path, true )
	);

	$result = bc_pm_run_isolated( $body );

	assert_same( $result['option_filters'], 1, 'exactly one registration across both require_once call sites, not two' );
	assert_same( $result['loaded_defined'], 1, 'constant defined once the mu-plugin path loaded the module' );
} );

bc_test( 'require_once with a spelling-different ("/./" segment) path to the same module file: still exactly one registration', function () use ( $bc_pm_bootstrap_path, $bc_pm_module_path ) {
	// This exercises require_once()'s OWN realpath()-based dedup, not the
	// BLOCKSY_CHILD_PERF_CONFIG_LOADED guard: an inserted "/./" segment is
	// a spelling variant require_once already resolves to the same file
	// before re-opening it, on both Windows and *nix — confirmed
	// empirically (see this task's fix report) — so the module body never
	// executes a second time and the guard's own `if` is never reached
	// again either. That IS the behaviour both real call sites in this
	// theme rely on (inc/perf/mu-plugins/bc-perfmatters-config.php's shim
	// and functions.php's BLOCKSY_CHILD_PATH both derive their path from
	// the same get_stylesheet_directory() call), so this test's coverage
	// matches production. A path require_once() genuinely cannot resolve
	// to the same file (e.g. a filesystem hard link) is a different,
	// worse case — confirmed (not committed here, see the fix report) to
	// fatal on function redeclaration regardless of the guard, because
	// this module's functions are declared unconditionally at file scope;
	// that is not a live risk for either real caller, both of which always
	// produce identical path strings.
	$body = sprintf(
		<<<'PHP'
require %s;
$canonical        = %s;
$spelling_variant = dirname( $canonical ) . '/./' . basename( $canonical );
require_once $canonical;
require_once $spelling_variant;
echo json_encode([
	'strings_differ' => ( $canonical !== $spelling_variant ) ? 1 : 0,
	'option_filters' => count($GLOBALS['bc_test_hooks']['option_perfmatters_options'][10] ?? []),
]);
PHP,
		var_export( $bc_pm_bootstrap_path, true ),
		var_export( $bc_pm_module_path, true )
	);

	$result = bc_pm_run_isolated( $body );

	assert_same( $result['strings_differ'], 1, 'sanity: the two require_once calls really used different path strings' );
	assert_same( $result['option_filters'], 1, 'require_once normalises both spellings to the same file, so still exactly one registration' );
} );

bc_test( 'admin_notice(): prints the notice when Perfmatters is active and the current screen is one of its own', function () use ( $bc_pm_bootstrap_path, $bc_pm_module_path ) {
	$body = sprintf(
		<<<'PHP'
require %s;
define('PERFMATTERS_VERSION', '2.6.8');
function get_current_screen() { return (object) [ 'id' => 'toplevel_page_perfmatters' ]; }
require_once %s;
ob_start();
blocksy_child_perf_pm_admin_notice();
$output = ob_get_clean();
echo json_encode([
	'contains_notice' => false !== strpos( $output, 'Perfmatters settings are managed by the child theme' ) ? 1 : 0,
	'is_dismissible'  => false !== strpos( $output, 'is-dismissible' ) ? 1 : 0,
]);
PHP,
		var_export( $bc_pm_bootstrap_path, true ),
		var_export( $bc_pm_module_path, true )
	);

	$result = bc_pm_run_isolated( $body );

	assert_same( $result['contains_notice'], 1, 'notice text printed when the screen id contains "perfmatters"' );
	assert_same( $result['is_dismissible'], 1, 'dismissible notice markup' );
} );

bc_test( 'bc_pm_run_isolated(): every subprocess script file it created has been cleaned up', function () {
	$leftover = array_values( array_filter( $GLOBALS['bc_pm_isolated_script_paths'] ?? [], 'file_exists' ) );

	assert_same( $leftover, [], 'no bc_pm_iso_* temp file survives in sys_get_temp_dir() after the run' );
} );

// Leave the shared memo clean for anything that requires this file again
// within the same process (defensive; run.php requires each test file once).
unset( $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] );
