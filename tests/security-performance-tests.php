<?php
/**
 * Security and Performance Tests
 * 
 * Unit tests for security and performance improvements
 * 
 * @package Blocksy_Child
 * @since 1.17.1
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test Security Helper Functions
 */
class Blocksy_Child_Security_Tests {

	/**
	 * Test URL sanitization
	 */
	public static function test_url_sanitization() {
		// Mock $_SERVER data
		$_SERVER['REQUEST_URI'] = '/test-page?param=<script>alert("xss")</script>';
		
		$sanitized = Blocksy_Child_Security_Helper::sanitize_server_url();
		
		// Should not contain script tags
		if ( strpos( $sanitized, '<script>' ) !== false ) {
			return array(
				'status' => 'FAIL',
				'message' => 'XSS vulnerability: Script tags not sanitized',
				'input' => $_SERVER['REQUEST_URI'],
				'output' => $sanitized
			);
		}
		
		return array(
			'status' => 'PASS',
			'message' => 'URL sanitization working correctly',
			'input' => $_SERVER['REQUEST_URI'],
			'output' => $sanitized
		);
	}

	/**
	 * Test order ID validation
	 */
	public static function test_order_validation() {
		$tests = array(
			array(
				'input' => '123',
				'expected' => 123,
				'description' => 'Valid numeric string'
			),
			array(
				'input' => 'abc',
				'expected' => 0,
				'description' => 'Invalid non-numeric string'
			),
			array(
				'input' => '123<script>',
				'expected' => 123,
				'description' => 'Numeric with XSS attempt'
			),
			array(
				'input' => '',
				'expected' => 0,
				'description' => 'Empty string'
			)
		);
		
		$results = array();
		
		foreach ( $tests as $test ) {
			$result = Blocksy_Child_Security_Helper::validate_order_id( $test['input'] );
			
			$status = ( $result === $test['expected'] ) ? 'PASS' : 'FAIL';
			
			$results[] = array(
				'status' => $status,
				'description' => $test['description'],
				'input' => $test['input'],
				'expected' => $test['expected'],
				'actual' => $result
			);
		}
		
		return $results;
	}

	/**
	 * Test nonce verification
	 */
	public static function test_nonce_verification() {
		// Create a valid nonce
		$action = 'test_action';
		$nonce = wp_create_nonce( $action );
		
		// Test valid nonce
		$_POST['test_nonce'] = $nonce;
		$valid_result = Blocksy_Child_Security_Helper::verify_nonce( 'test_nonce', $action );
		
		// Test invalid nonce
		$_POST['test_nonce'] = 'invalid_nonce';
		$invalid_result = Blocksy_Child_Security_Helper::verify_nonce( 'test_nonce', $action );
		
		$results = array();
		
		$results[] = array(
			'status' => $valid_result ? 'PASS' : 'FAIL',
			'description' => 'Valid nonce verification',
			'expected' => true,
			'actual' => $valid_result
		);
		
		$results[] = array(
			'status' => ! $invalid_result ? 'PASS' : 'FAIL',
			'description' => 'Invalid nonce rejection',
			'expected' => false,
			'actual' => $invalid_result
		);
		
		return $results;
	}
}

/**
 * Test Performance Helper Functions
 */
class Blocksy_Child_Performance_Tests {

	/**
	 * Test CSS optimization
	 */
	public static function test_css_optimization() {
		$test_css = '
			.test-class {
				width: 100px !important;
				height: 50px !important;
				margin: 10px;
				z-index: 1000 !important;
			}
		';
		
		$optimized = Blocksy_Child_Performance_Helper::optimize_css_specificity( $test_css );
		
		// Count !important declarations
		$original_count = substr_count( $test_css, '!important' );
		$optimized_count = substr_count( $optimized, '!important' );
		
		return array(
			'status' => $optimized_count < $original_count ? 'PASS' : 'FAIL',
			'message' => sprintf( 
				'Reduced !important declarations from %d to %d', 
				$original_count, 
				$optimized_count 
			),
			'original' => $test_css,
			'optimized' => $optimized
		);
	}

	/**
	 * Test CSS minification
	 */
	public static function test_css_minification() {
		$test_css = '
			/* This is a comment */
			.test-class {
				width: 100px;
				height: 50px;
				margin: 10px;
			}
			
			.another-class { color: red; }
		';
		
		$minified = Blocksy_Child_Performance_Helper::minify_css( $test_css );
		
		$original_size = strlen( $test_css );
		$minified_size = strlen( $minified );
		$reduction = round( ( ( $original_size - $minified_size ) / $original_size ) * 100, 2 );
		
		return array(
			'status' => $minified_size < $original_size ? 'PASS' : 'FAIL',
			'message' => sprintf( 
				'CSS size reduced by %s%% (%d to %d bytes)', 
				$reduction, 
				$original_size, 
				$minified_size 
			),
			'original_size' => $original_size,
			'minified_size' => $minified_size,
			'reduction_percent' => $reduction
		);
	}

	/**
	 * Test file caching
	 */
	public static function test_file_caching() {
		$test_file = __FILE__;
		$cache_key = 'test_file_cache';
		
		// Clear any existing cache
		wp_cache_delete( $cache_key, 'blocksy_child_files' );
		
		// First call should hit the filesystem
		$start_time = microtime( true );
		$result1 = Blocksy_Child_Performance_Helper::cached_file_info( $test_file, $cache_key );
		$first_call_time = microtime( true ) - $start_time;
		
		// Second call should hit the cache
		$start_time = microtime( true );
		$result2 = Blocksy_Child_Performance_Helper::cached_file_info( $test_file, $cache_key );
		$second_call_time = microtime( true ) - $start_time;
		
		return array(
			'status' => $second_call_time < $first_call_time ? 'PASS' : 'FAIL',
			'message' => sprintf( 
				'Cache improved performance: %s vs %s seconds', 
				number_format( $first_call_time, 6 ), 
				number_format( $second_call_time, 6 ) 
			),
			'first_call_time' => $first_call_time,
			'second_call_time' => $second_call_time,
			'performance_improvement' => round( ( ( $first_call_time - $second_call_time ) / $first_call_time ) * 100, 2 ) . '%'
		);
	}
}

/**
 * Run all security and performance tests
 */
function blocksy_child_run_security_performance_tests() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions' );
	}
	
	$results = array(
		'security_tests' => array(),
		'performance_tests' => array(),
		'timestamp' => current_time( 'mysql' ),
		'summary' => array(
			'total_tests' => 0,
			'passed' => 0,
			'failed' => 0
		)
	);
	
	// Run security tests
	$results['security_tests']['url_sanitization'] = Blocksy_Child_Security_Tests::test_url_sanitization();
	$results['security_tests']['order_validation'] = Blocksy_Child_Security_Tests::test_order_validation();
	$results['security_tests']['nonce_verification'] = Blocksy_Child_Security_Tests::test_nonce_verification();
	
	// Run performance tests
	$results['performance_tests']['css_optimization'] = Blocksy_Child_Performance_Tests::test_css_optimization();
	$results['performance_tests']['css_minification'] = Blocksy_Child_Performance_Tests::test_css_minification();
	$results['performance_tests']['file_caching'] = Blocksy_Child_Performance_Tests::test_file_caching();
	
	// Calculate summary
	foreach ( $results as $category => $tests ) {
		if ( ! is_array( $tests ) || $category === 'summary' ) {
			continue;
		}
		
		foreach ( $tests as $test ) {
			if ( is_array( $test ) && isset( $test['status'] ) ) {
				$results['summary']['total_tests']++;
				if ( $test['status'] === 'PASS' ) {
					$results['summary']['passed']++;
				} else {
					$results['summary']['failed']++;
				}
			} elseif ( is_array( $test ) ) {
				// Handle nested test results
				foreach ( $test as $subtest ) {
					if ( isset( $subtest['status'] ) ) {
						$results['summary']['total_tests']++;
						if ( $subtest['status'] === 'PASS' ) {
							$results['summary']['passed']++;
						} else {
							$results['summary']['failed']++;
						}
					}
				}
			}
		}
	}
	
	return $results;
}

/**
 * Display test results in admin
 */
function blocksy_child_display_test_results() {
	if ( ! isset( $_GET['run_tests'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$results = blocksy_child_run_security_performance_tests();
	
	echo '<div class="notice notice-info"><p><strong>Security & Performance Test Results</strong></p>';
	echo '<p>Total Tests: ' . esc_html( $results['summary']['total_tests'] ) . ' | ';
	echo 'Passed: ' . esc_html( $results['summary']['passed'] ) . ' | ';
	echo 'Failed: ' . esc_html( $results['summary']['failed'] ) . '</p>';
	echo '<pre>' . esc_html( wp_json_encode( $results, JSON_PRETTY_PRINT ) ) . '</pre>';
	echo '</div>';
}
add_action( 'admin_notices', 'blocksy_child_display_test_results' );
