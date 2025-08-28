<?php
/**
 * PHPUnit Bootstrap File for BlazeCommerce WordPress Child Theme
 * 
 * This file is loaded before any tests are run and sets up the WordPress testing environment.
 * 
 * @package BlazeCommerce
 * @subpackage Tests
 */

// Composer autoloader
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// WordPress test configuration
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file
$_phpunit_polyfills_path = getenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH');
if (false !== $_phpunit_polyfills_path) {
    define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path);
}

if (!file_exists($_tests_dir . '/includes/functions.php')) {
    echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit(1);
}

// Give access to tests_add_filter() function
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested
 */
function _manually_load_theme() {
    // Load parent theme functions if needed
    $parent_theme_functions = get_template_directory() . '/functions.php';
    if (file_exists($parent_theme_functions)) {
        require_once $parent_theme_functions;
    }
    
    // Load child theme functions
    require_once dirname(__DIR__) . '/functions.php';
}

tests_add_filter('muplugins_loaded', '_manually_load_theme');

// Start up the WP testing environment
require $_tests_dir . '/includes/bootstrap.php';

// Load test helpers
require_once __DIR__ . '/helpers/TestCase.php';
require_once __DIR__ . '/helpers/WooCommerceTestCase.php';
require_once __DIR__ . '/helpers/FactoryHelpers.php';

// Initialize Brain Monkey for unit tests
if (class_exists('Brain\Monkey\Functions')) {
    Brain\Monkey\setUp();
}

// Set up test constants
if (!defined('BLAZE_COMMERCE_TESTS_RUNNING')) {
    define('BLAZE_COMMERCE_TESTS_RUNNING', true);
}

if (!defined('BLAZE_COMMERCE_TEST_DATA_DIR')) {
    define('BLAZE_COMMERCE_TEST_DATA_DIR', __DIR__ . '/fixtures');
}

// Clean up function for after tests
register_shutdown_function(function() {
    if (class_exists('Brain\Monkey\Functions')) {
        Brain\Monkey\tearDown();
    }
});
