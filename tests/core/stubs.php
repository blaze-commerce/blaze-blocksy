<?php
/**
 * Core test stubs: WordPress functions missing from tests/perf/bootstrap.php.
 * Requires the perf bootstrap first, then adds only what it lacks.
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__ ) . '/perf/bootstrap.php';

$GLOBALS['bc_core_store'] = [
	'options'    => [],
	'transients' => [],
	'mails'      => [],
	'actions'    => [],
	'notices'    => [],
	'assets'     => [],
];

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $string ) {
		return rtrim( $string, '/\\' ) . '/';
	}
}
if ( ! function_exists( 'get_stylesheet_directory' ) ) {
	function get_stylesheet_directory() {
		return dirname( __DIR__, 2 );
	}
}
if ( ! function_exists( 'get_stylesheet_directory_uri' ) ) {
	function get_stylesheet_directory_uri() {
		return 'https://example.test/wp-content/themes/blocksy-child';
	}
}
if ( ! function_exists( 'wp_get_theme' ) ) {
	/**
	 * Returns an object whose get( 'Version' ) reads the real style.css header.
	 */
	function wp_get_theme( $stylesheet = '' ) {
		return new class() {
			public function get( $header ) {
				$data = get_file_data_stub( dirname( __DIR__, 2 ) . '/style.css' );
				return $data[ $header ] ?? false;
			}
		};
	}
}
if ( ! function_exists( 'get_file_data_stub' ) ) {
	function get_file_data_stub( string $file ): array {
		$out = [];
		foreach ( file( $file ) as $line ) {
			if ( preg_match( '/^\s*([A-Za-z ]+):\s*(.*?)\s*$/', $line, $m ) ) {
				$out[ trim( $m[1] ) ] = $m[2];
			}
			if ( false !== strpos( $line, '*/' ) ) {
				break;
			}
		}
		return $out;
	}
}
if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) {
		return $GLOBALS['bc_core_store']['options'][ $name ] ?? $default;
	}
}
if ( ! function_exists( 'update_option' ) ) {
	function update_option( $name, $value, $autoload = null ) {
		// get_option() comes from tests/perf/bootstrap.php and reads bc_wp_stub['options'].
		$GLOBALS['bc_wp_stub']['options'][ $name ] = $value;
		return true;
	}
}
if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $name ) {
		unset( $GLOBALS['bc_wp_stub']['options'][ $name ] );
		return true;
	}
}
if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $name ) {
		return $GLOBALS['bc_core_store']['transients'][ $name ] ?? false;
	}
}
if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $name, $value, $expiration = 0 ) {
		$GLOBALS['bc_core_store']['transients'][ $name ] = $value;
		return true;
	}
}
if ( ! function_exists( 'wp_mail' ) ) {
	function wp_mail( $to, $subject, $message, $headers = '', $attachments = [] ) {
		$GLOBALS['bc_core_store']['mails'][] = compact( 'to', 'subject', 'message' );
		return true;
	}
}
if ( ! function_exists( 'get_bloginfo' ) ) {
	function get_bloginfo( $show = '' ) {
		return 'Test Site';
	}
}
if ( ! function_exists( 'do_action' ) ) {
	function do_action( $hook, ...$args ) {
		$GLOBALS['bc_core_store']['actions'][ $hook ] = ( $GLOBALS['bc_core_store']['actions'][ $hook ] ?? 0 ) + 1;
	}
}
if ( ! function_exists( 'did_action' ) ) {
	function did_action( $hook ) {
		return $GLOBALS['bc_core_store']['actions'][ $hook ] ?? 0;
	}
}
foreach ( [ 'is_page', 'is_cart', 'is_checkout', 'is_product', 'is_shop', 'is_product_taxonomy', 'is_account_page' ] as $bc_cond ) {
	if ( ! function_exists( $bc_cond ) ) {
		eval( "function {$bc_cond}() { return ! empty( \$GLOBALS['bc_wp_stub']['{$bc_cond}'] ); }" ); // phpcs:ignore -- test stub.
	}
}
foreach ( [ 'wp_register_script', 'wp_register_style', 'wp_enqueue_script', 'wp_enqueue_style' ] as $bc_fn ) {
	if ( ! function_exists( $bc_fn ) ) {
		eval( "function {$bc_fn}( \$handle, ...\$rest ) { \$GLOBALS['bc_core_store']['assets'][] = [ '{$bc_fn}', \$handle ]; return true; }" ); // phpcs:ignore -- test stub.
	}
}
if ( ! function_exists( 'wp_add_inline_script' ) ) {
	function wp_add_inline_script( $handle, $data, $position = 'after' ) {
		$GLOBALS['bc_core_store']['assets'][] = [ 'wp_add_inline_script', $handle ];
		return true;
	}
}
if ( ! function_exists( 'wc_add_notice' ) ) {
	function wc_add_notice( $message, $type = 'success' ) {
		$GLOBALS['bc_core_store']['notices'][] = [ $type, $message ];
	}
}
if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $cap ) {
		return ! empty( $GLOBALS['bc_wp_stub']['current_user_can'] );
	}
}
