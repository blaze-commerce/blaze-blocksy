<?php
/**
 * Test bootstrap — dependency-free WordPress stubs for the perf module
 * CLI test harness. No WordPress install is used or required.
 *
 * @package Blocksy_Child
 */

error_reporting( E_ALL );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
}

if ( ! defined( 'BLOCKSY_CHILD_PATH' ) ) {
	define( 'BLOCKSY_CHILD_PATH', dirname( __DIR__, 2 ) . '/' );
}

if ( ! defined( 'BLOCKSY_CHILD_URL' ) ) {
	define( 'BLOCKSY_CHILD_URL', 'https://example.test/wp-content/themes/blocksy-child/' );
}

// Gates blocksy_child_perf_reset_state() (inc/perf/helpers.php) into
// existence — that function does not exist in a real WordPress request.
if ( ! defined( 'BC_PERF_TESTING' ) ) {
	define( 'BC_PERF_TESTING', true );
}

// Recorded add_action()/add_filter() callbacks: $GLOBALS['bc_test_hooks'][ $hook ][ $priority ][] = $callback.
$GLOBALS['bc_test_hooks'] = [];

// Collected error_log() calls, in call order.
$GLOBALS['bc_test_errors'] = [];

/**
 * Records a callback under the given filter hook + priority.
 *
 * @param string   $hook
 * @param callable $callback
 * @param int      $priority
 * @param int      $accepted_args Unused by this stub; accepted for signature parity.
 * @return true
 */
function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['bc_test_hooks'][ $hook ][ $priority ][] = $callback;
	ksort( $GLOBALS['bc_test_hooks'][ $hook ] );
	return true;
}

/**
 * Same recording mechanism as add_filter() — WordPress actions and filters
 * share one hook registry, and this test harness mirrors that.
 *
 * @param string   $hook
 * @param callable $callback
 * @param int      $priority
 * @param int      $accepted_args
 * @return true
 */
function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	return add_filter( $hook, $callback, $priority, $accepted_args );
}

/**
 * Removes a previously recorded callback.
 *
 * @param string   $hook
 * @param callable $callback
 * @param int      $priority
 * @return bool True if a matching callback was found and removed.
 */
function remove_action( $hook, $callback, $priority = 10 ) {
	if ( empty( $GLOBALS['bc_test_hooks'][ $hook ][ $priority ] ) ) {
		return false;
	}

	foreach ( $GLOBALS['bc_test_hooks'][ $hook ][ $priority ] as $index => $registered ) {
		if ( $registered === $callback ) {
			unset( $GLOBALS['bc_test_hooks'][ $hook ][ $priority ][ $index ] );
			return true;
		}
	}

	return false;
}

/**
 * Alias of remove_action() — actions and filters share one hook registry
 * in this stub (see add_filter()/add_action() above), so removing either
 * kind is the same operation. Provided so test files can name the removal
 * after what they registered (add_filter() -> remove_filter()) instead of
 * reaching for remove_action() on a filter hook.
 *
 * @param string   $hook
 * @param callable $callback
 * @param int      $priority
 * @return bool True if a matching callback was found and removed.
 */
function remove_filter( $hook, $callback, $priority = 10 ) {
	return remove_action( $hook, $callback, $priority );
}

/**
 * Applies every recorded callback for $hook, in priority order, to $value.
 *
 * @param string $hook
 * @param mixed  $value
 * @param mixed  ...$extra_args
 * @return mixed
 */
function apply_filters( $hook, $value, ...$extra_args ) {
	if ( empty( $GLOBALS['bc_test_hooks'][ $hook ] ) ) {
		return $value;
	}

	foreach ( $GLOBALS['bc_test_hooks'][ $hook ] as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$value = call_user_func_array( $callback, array_merge( [ $value ], $extra_args ) );
		}
	}

	return $value;
}

// WordPress core's own no-op filter callbacks (wp-includes/default-filters.php
// et al.) — not WordPress-specific behaviour of their own, just the two
// literal "always true"/"always false" callbacks core ships and that
// perf modules are expected to reuse (Perfmatters filters brief) rather
// than declare their own trivial closures for.
function __return_true() {
	return true;
}

function __return_false() {
	return false;
}

function is_admin() {
	return false;
}

function wp_doing_ajax() {
	return false;
}

function esc_attr( $text ) {
	return $text;
}

function esc_html( $text ) {
	return $text;
}

function esc_url( $text ) {
	return $text;
}

function wp_json_encode( $data, $options = 0, $depth = 512 ) {
	return json_encode( $data, $options, $depth );
}

function trailingslashit( $string ) {
	return rtrim( $string, '/\\' ) . '/';
}

/**
 * error_log() COLLECTION.
 *
 * PHP's error_log() is a built-in function — redeclaring it in the global
 * namespace is a fatal "Cannot redeclare" error, so it cannot be stubbed
 * the way the other functions above are. Instead, the runtime 'error_log'
 * ini directive is pointed at a private temp file; production code's
 * plain `error_log( $message )` calls (default type 0) are written there
 * by the real, unmodified error_log(), and bc_test_error_log_messages()
 * below re-reads + parses that file on demand so tests can assert on what
 * was logged.
 */
$GLOBALS['bc_test_error_log_file'] = tempnam( sys_get_temp_dir(), 'bc_test_error_log_' );
ini_set( 'error_log', $GLOBALS['bc_test_error_log_file'] );

register_shutdown_function( function () {
	if ( ! empty( $GLOBALS['bc_test_error_log_file'] ) && file_exists( $GLOBALS['bc_test_error_log_file'] ) ) {
		@unlink( $GLOBALS['bc_test_error_log_file'] );
	}
} );

// -----------------------------------------------------------------------
// Shared WP conditional/data stubs — used by more than one test-*.php file
// (or will be, by a later task). Declared HERE, each wrapped in
// function_exists(), instead of in an individual test-*.php file, because
// run.php requires every tests/perf/test-*.php file into ONE process: a
// second bare (unguarded) declaration of e.g. is_singular() in a later
// test file is a fatal "Cannot redeclare".
//
// All driven by the single $GLOBALS['bc_wp_stub'] array — a test case sets
// $GLOBALS['bc_wp_stub']['is_singular'] = true; instead of declaring its
// own is_singular(). bc_wp_stub_reset() restores every key to its default;
// run.php calls it before requiring each test file, right next to
// blocksy_child_perf_reset_state().
// -----------------------------------------------------------------------

/**
 * Default state for $GLOBALS['bc_wp_stub']. See bc_wp_stub_reset().
 *
 * @return array
 */
function bc_wp_stub_defaults(): array {
	return [
		// Conditional tags.
		'is_singular'             => false,
		'is_front_page'           => false,
		'is_shop'                 => false,
		'is_product_taxonomy'     => false,
		'is_product'              => false,

		// Data getters. The attachment-image maps and attachment_metadata /
		// wc_image_size are keyed (see bc_wp_stub_attachment_key() / each
		// stub below) so more than one fixture can coexist in one test case.
		'post_thumbnail_id'       => 0,
		'attachment_image_url'    => [],
		'attachment_image_srcset' => [],
		'attachment_image_sizes'  => [],
		'attachment_metadata'     => [], // <id> => array. Task 8.
		'wc_image_size'           => [], // <size name> => array. Task 8.
		'post_field'              => [], // <field name> => string.
		'options'                 => [], // <option name> => mixed.

		// Task 6 (hero-facade).
		'attachment_url_to_postid' => [], // <url> => attachment id.
		'image_sizes'              => [], // Recorded add_image_size() calls: <name> => [ width, height, crop ].
		'upload_dir'               => [   // wp_get_upload_dir() return value.
			'basedir' => '',
			'baseurl' => 'https://example.test/wp-content/uploads',
		],
	];
}

/**
 * Resets $GLOBALS['bc_wp_stub'] to bc_wp_stub_defaults(). Called by
 * run.php before requiring each test-*.php file, so no test file inherits
 * state a previous file set.
 *
 * @return void
 */
function bc_wp_stub_reset(): void {
	$GLOBALS['bc_wp_stub'] = bc_wp_stub_defaults();
}

bc_wp_stub_reset();

/**
 * Keys the "<id>:<size>" stub maps below. An array size (a `[width, height]`
 * pair, as e.g. blocksy_child_perf_lcp_hero_from_content() derives) is
 * flattened to "WxH" so it can be used as an array key.
 *
 * @param int          $id
 * @param string|array $size
 * @return string
 */
function bc_wp_stub_attachment_key( $id, $size ): string {
	return $id . ':' . ( is_array( $size ) ? implode( 'x', $size ) : $size );
}

if ( ! function_exists( 'is_singular' ) ) {
	function is_singular( $type = '' ) {
		return ! empty( $GLOBALS['bc_wp_stub']['is_singular'] );
	}
}

if ( ! function_exists( 'is_front_page' ) ) {
	function is_front_page() {
		return ! empty( $GLOBALS['bc_wp_stub']['is_front_page'] );
	}
}

if ( ! function_exists( 'is_shop' ) ) {
	function is_shop() {
		return ! empty( $GLOBALS['bc_wp_stub']['is_shop'] );
	}
}

if ( ! function_exists( 'is_product_taxonomy' ) ) {
	function is_product_taxonomy() {
		return ! empty( $GLOBALS['bc_wp_stub']['is_product_taxonomy'] );
	}
}

if ( ! function_exists( 'is_product' ) ) {
	function is_product() {
		return ! empty( $GLOBALS['bc_wp_stub']['is_product'] );
	}
}

if ( ! function_exists( 'get_post_thumbnail_id' ) ) {
	function get_post_thumbnail_id( $post = 0 ) {
		return (int) $GLOBALS['bc_wp_stub']['post_thumbnail_id'];
	}
}

if ( ! function_exists( 'wp_get_attachment_image_url' ) ) {
	function wp_get_attachment_image_url( $id, $size ) {
		$key = bc_wp_stub_attachment_key( $id, $size );
		return $GLOBALS['bc_wp_stub']['attachment_image_url'][ $key ] ?? '';
	}
}

if ( ! function_exists( 'wp_get_attachment_image_srcset' ) ) {
	function wp_get_attachment_image_srcset( $id, $size ) {
		$key = bc_wp_stub_attachment_key( $id, $size );
		return $GLOBALS['bc_wp_stub']['attachment_image_srcset'][ $key ] ?? '';
	}
}

if ( ! function_exists( 'wp_get_attachment_image_sizes' ) ) {
	function wp_get_attachment_image_sizes( $id, $size ) {
		$key = bc_wp_stub_attachment_key( $id, $size );
		return $GLOBALS['bc_wp_stub']['attachment_image_sizes'][ $key ] ?? '';
	}
}

if ( ! function_exists( 'wp_get_attachment_metadata' ) ) {
	// Not consumed by any module shipped so far — included now so Task 8
	// can set $GLOBALS['bc_wp_stub']['attachment_metadata'][ $id ] without
	// needing to declare this stub itself.
	function wp_get_attachment_metadata( $id ) {
		return $GLOBALS['bc_wp_stub']['attachment_metadata'][ (int) $id ] ?? [];
	}
}

if ( ! function_exists( 'wc_get_image_size' ) ) {
	// Not consumed by any module shipped so far — included now for Task 8,
	// same reasoning as wp_get_attachment_metadata() above.
	function wc_get_image_size( $image_size, $default_args = [] ) {
		return $GLOBALS['bc_wp_stub']['wc_image_size'][ (string) $image_size ] ?? $default_args;
	}
}

if ( ! function_exists( 'get_post_field' ) ) {
	function get_post_field( $field, $post = 0 ) {
		return $GLOBALS['bc_wp_stub']['post_field'][ $field ] ?? '';
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) {
		return $GLOBALS['bc_wp_stub']['options'][ $name ] ?? $default;
	}
}

if ( ! function_exists( 'attachment_url_to_postid' ) ) {
	function attachment_url_to_postid( $url ) {
		return (int) ( $GLOBALS['bc_wp_stub']['attachment_url_to_postid'][ (string) $url ] ?? 0 );
	}
}

if ( ! function_exists( 'add_image_size' ) ) {
	// Records the call instead of registering anything — a test asserts on
	// $GLOBALS['bc_wp_stub']['image_sizes'][ $name ].
	function add_image_size( $name, $width = 0, $height = 0, $crop = false ) {
		$GLOBALS['bc_wp_stub']['image_sizes'][ $name ] = [ (int) $width, (int) $height, $crop ];
	}
}

if ( ! function_exists( 'wp_get_upload_dir' ) ) {
	function wp_get_upload_dir() {
		return $GLOBALS['bc_wp_stub']['upload_dir'];
	}
}

/**
 * Every message passed to error_log() since the test run started, in call
 * order, with the leading PHP `[date-time]` prefix stripped.
 *
 * @return string[]
 */
function bc_test_error_log_messages(): array {
	$file = $GLOBALS['bc_test_error_log_file'];

	if ( ! file_exists( $file ) ) {
		return [];
	}

	$lines = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

	if ( false === $lines ) {
		return [];
	}

	$GLOBALS['bc_test_errors'] = array_map( function ( $line ) {
		return preg_replace( '/^\[[^\]]+\]\s*/', '', $line );
	}, $lines );

	return $GLOBALS['bc_test_errors'];
}
