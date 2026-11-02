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
	'caps'       => [],
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
		$GLOBALS['bc_core_store']['transients'][ $name ]    = $value;
		$GLOBALS['bc_core_store']['transient_ttl'][ $name ] = $expiration;
		return true;
	}
}
if ( ! function_exists( 'wp_mail' ) ) {
	function wp_mail( $to, $subject, $message, $headers = '', $attachments = [] ) {
		$GLOBALS['bc_core_store']['mails'][] = compact( 'to', 'subject', 'message' );
		return true;
	}
}
if ( ! function_exists( 'apply_filters_deprecated' ) ) {
	/**
	 * Like core: runs the old hook (and records the deprecation) only when something is attached to it.
	 */
	function apply_filters_deprecated( $hook, $args, $version, $replacement = '', $message = '' ) {
		if ( empty( $GLOBALS['bc_test_hooks'][ $hook ] ) ) {
			return $args[0];
		}
		$GLOBALS['bc_core_store']['deprecated'][] = [ $hook, $version, $replacement ];
		return apply_filters( $hook, ...$args );
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
		eval( "function {$bc_fn}( \$handle, ...\$rest ) { \$GLOBALS['bc_core_store']['assets'][] = array_merge( [ '{$bc_fn}', \$handle ], \$rest ); return true; }" ); // phpcs:ignore -- test stub.
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
		$GLOBALS['bc_core_store']['caps'][] = $cap;
		return ! empty( $GLOBALS['bc_wp_stub']['current_user_can'] );
	}
}

// Tier B stubs: $GLOBALS['bc_wp_stub']['has_blocks'] / ['shortcodes'] are lists of names present on the current post.
if ( ! function_exists( 'has_block' ) ) {
	function has_block( $name, $post = null ) {
		return in_array( $name, (array) ( $GLOBALS['bc_wp_stub']['has_blocks'] ?? [] ), true );
	}
}
if ( ! function_exists( 'has_shortcode' ) ) {
	function has_shortcode( $content, $tag ) {
		return in_array( $tag, (array) ( $GLOBALS['bc_wp_stub']['shortcodes'] ?? [] ), true );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! class_exists( 'WP_Error', false ) ) {
	// phpcs:ignore Generic.Files.OneObjectStructurePerFile
	class WP_Error {
		private $code;
		private $message;
		private $data;

		public function __construct( $code = '', $message = '', $data = '' ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}

		public function get_error_code() {
			return $this->code;
		}

		public function get_error_message() {
			return $this->message;
		}

		public function get_error_data() {
			return $this->data;
		}
	}
}

if ( ! class_exists( 'WP_REST_Request', false ) ) {
	// phpcs:ignore Generic.Files.OneObjectStructurePerFile
	class WP_REST_Request {
		private $method;
		private $route;

		public function __construct( $method = '', $route = '' ) {
			$this->method = strtoupper( (string) $method );
			$this->route  = (string) $route;
		}

		public function get_method() {
			return $this->method;
		}

		public function get_route() {
			return $this->route;
		}
	}
}
