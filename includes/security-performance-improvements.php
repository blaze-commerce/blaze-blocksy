<?php
/**
 * Security and Performance Improvements
 * 
 * Additional security hardening and performance optimizations
 * based on comprehensive code review findings.
 * 
 * @package Blocksy_Child
 * @since 1.17.1
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enhanced Input Sanitization Helper
 * 
 * Provides centralized input sanitization for common use cases
 */
class Blocksy_Child_Security_Helper {

	/**
	 * Sanitize URL from $_SERVER safely
	 * 
	 * @param string $server_key The $_SERVER key to sanitize
	 * @return string Sanitized URL or empty string
	 */
	public static function sanitize_server_url( $server_key = 'REQUEST_URI' ) {
		if ( ! isset( $_SERVER[ $server_key ] ) ) {
			return '';
		}
		
		return sanitize_text_field( wp_unslash( $_SERVER[ $server_key ] ) );
	}

	/**
	 * Sanitize and validate order ID
	 * 
	 * @param mixed $order_id Order ID to validate
	 * @return int Valid order ID or 0
	 */
	public static function validate_order_id( $order_id ) {
		$order_id = absint( $order_id );
		
		if ( ! $order_id ) {
			return 0;
		}
		
		// Verify order exists and user has access
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return 0;
		}
		
		// Check if current user can access this order
		if ( ! current_user_can( 'view_order', $order_id ) && ! current_user_can( 'manage_woocommerce' ) ) {
			$current_user_id = get_current_user_id();
			if ( $current_user_id !== $order->get_customer_id() ) {
				return 0;
			}
		}
		
		return $order_id;
	}

	/**
	 * Enhanced nonce verification with context
	 * 
	 * @param string $nonce_field Nonce field name
	 * @param string $action Nonce action
	 * @param string $method Request method (POST, GET)
	 * @return bool True if nonce is valid
	 */
	public static function verify_nonce( $nonce_field, $action, $method = 'POST' ) {
		$request_data = $method === 'POST' ? $_POST : $_GET;
		
		if ( ! isset( $request_data[ $nonce_field ] ) ) {
			return false;
		}
		
		return wp_verify_nonce( 
			sanitize_text_field( wp_unslash( $request_data[ $nonce_field ] ) ), 
			$action 
		);
	}
}

/**
 * Performance Optimization Helper
 * 
 * Provides performance optimization utilities
 */
class Blocksy_Child_Performance_Helper {

	/**
	 * Cache expensive file operations
	 * 
	 * @param string $file_path Path to file
	 * @param string $cache_key Cache key
	 * @param int $expiration Cache expiration in seconds
	 * @return array File information
	 */
	public static function cached_file_info( $file_path, $cache_key = null, $expiration = 3600 ) {
		if ( ! $cache_key ) {
			$cache_key = 'file_info_' . md5( $file_path );
		}
		
		$cached = wp_cache_get( $cache_key, 'blocksy_child_files' );
		if ( $cached !== false ) {
			return $cached;
		}
		
		$file_info = array(
			'exists' => file_exists( $file_path ),
			'readable' => is_readable( $file_path ),
			'size' => file_exists( $file_path ) ? filesize( $file_path ) : 0,
			'modified' => file_exists( $file_path ) ? filemtime( $file_path ) : 0,
		);
		
		wp_cache_set( $cache_key, $file_info, 'blocksy_child_files', $expiration );
		
		return $file_info;
	}

	/**
	 * Optimize CSS delivery by removing unused !important declarations
	 * 
	 * @param string $css CSS content
	 * @return string Optimized CSS
	 */
	public static function optimize_css_specificity( $css ) {
		// Remove unnecessary !important declarations for common properties
		$patterns = array(
			'/(\s*width:\s*[^;]+)\s*!important/i' => '$1',
			'/(\s*height:\s*[^;]+)\s*!important/i' => '$1',
			'/(\s*margin:\s*[^;]+)\s*!important/i' => '$1',
			'/(\s*padding:\s*[^;]+)\s*!important/i' => '$1',
		);
		
		foreach ( $patterns as $pattern => $replacement ) {
			$css = preg_replace( $pattern, $replacement, $css );
		}
		
		return $css;
	}

	/**
	 * Minify inline CSS
	 * 
	 * @param string $css CSS content
	 * @return string Minified CSS
	 */
	public static function minify_css( $css ) {
		// Remove comments
		$css = preg_replace( '/\/\*.*?\*\//s', '', $css );
		
		// Remove unnecessary whitespace
		$css = preg_replace( '/\s+/', ' ', $css );
		
		// Remove whitespace around specific characters
		$css = str_replace( array( '; ', ' {', '{ ', ' }', '} ', ': ', ', ' ), array( ';', '{', '{', '}', '}', ':', ',' ), $css );
		
		return trim( $css );
	}
}

/**
 * Enhanced Error Handling
 * 
 * Provides better error handling and logging
 */
class Blocksy_Child_Error_Handler {

	/**
	 * Log error with context
	 * 
	 * @param string $message Error message
	 * @param string $context Error context
	 * @param array $data Additional data
	 */
	public static function log_error( $message, $context = 'general', $data = array() ) {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}
		
		$log_message = sprintf(
			'[Blocksy Child - %s] %s',
			strtoupper( $context ),
			$message
		);
		
		if ( ! empty( $data ) ) {
			$log_message .= ' | Data: ' . wp_json_encode( $data );
		}
		
		error_log( $log_message );
	}

	/**
	 * Handle file loading errors gracefully
	 * 
	 * @param string $file_path Path to file that failed to load
	 * @param string $context Context of the error
	 */
	public static function handle_file_error( $file_path, $context = 'file_load' ) {
		self::log_error(
			sprintf( 'Failed to load file: %s', $file_path ),
			$context,
			array(
				'file_exists' => file_exists( $file_path ),
				'is_readable' => is_readable( $file_path ),
				'file_size' => file_exists( $file_path ) ? filesize( $file_path ) : 0,
			)
		);
	}
}

/**
 * Initialize security and performance improvements
 */
function blocksy_child_init_security_performance() {
	// Add security headers for AJAX requests
	add_action( 'wp_ajax_nopriv_*', 'blocksy_child_add_ajax_security_headers', 1 );
	add_action( 'wp_ajax_*', 'blocksy_child_add_ajax_security_headers', 1 );
	
	// Optimize asset loading
	add_action( 'wp_enqueue_scripts', 'blocksy_child_optimize_asset_loading', 5 );
	
	// Add performance monitoring
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		add_action( 'wp_footer', 'blocksy_child_performance_debug_info' );
	}
}
add_action( 'init', 'blocksy_child_init_security_performance' );

/**
 * Add security headers for AJAX requests
 */
function blocksy_child_add_ajax_security_headers() {
	if ( wp_doing_ajax() ) {
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: DENY' );
		header( 'X-XSS-Protection: 1; mode=block' );
	}
}

/**
 * Optimize asset loading order and dependencies
 */
function blocksy_child_optimize_asset_loading() {
	// Preload critical fonts
	if ( ! is_admin() ) {
		echo '<link rel="preload" href="' . esc_url( get_stylesheet_directory_uri() . '/assets/fonts/main.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	}
	
	// Optimize jQuery loading for better performance
	if ( ! is_admin() && ! wp_script_is( 'jquery', 'done' ) ) {
		wp_script_add_data( 'jquery', 'async', true );
	}
}

/**
 * Add performance debug information
 */
function blocksy_child_performance_debug_info() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$memory_usage = memory_get_peak_usage( true );
	$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
	$memory_percent = round( ( $memory_usage / $memory_limit ) * 100, 2 );
	
	echo '<!-- Blocksy Child Performance Debug -->' . "\n";
	echo '<!-- Memory Usage: ' . esc_html( size_format( $memory_usage ) ) . ' (' . esc_html( $memory_percent ) . '%) -->' . "\n";
	echo '<!-- Queries: ' . esc_html( get_num_queries() ) . ' -->' . "\n";
	echo '<!-- Load Time: ' . esc_html( timer_stop() ) . 's -->' . "\n";
}
