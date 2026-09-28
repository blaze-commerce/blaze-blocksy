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
