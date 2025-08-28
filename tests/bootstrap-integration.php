<?php
/**
 * PHPUnit Bootstrap File for Integration Tests
 * 
 * This file is loaded before integration tests are run and sets up Brain Monkey
 * for WordPress function mocking without loading WordPress core.
 * 
 * @package BlazeCommerce\Tests\Integration
 */

// Composer autoloader
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Set up test constants
if (!defined('BLAZE_COMMERCE_TESTS_RUNNING')) {
    define('BLAZE_COMMERCE_TESTS_RUNNING', true);
}

if (!defined('BLAZE_COMMERCE_TEST_DATA_DIR')) {
    define('BLAZE_COMMERCE_TEST_DATA_DIR', __DIR__ . '/fixtures');
}

// Initialize Brain Monkey
if (class_exists('Brain\Monkey\Functions')) {
    Brain\Monkey\setUp();
}

// Mock WordPress constants that might be needed
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Clean up function for after tests
register_shutdown_function(function() {
    if (class_exists('Brain\Monkey\Functions')) {
        Brain\Monkey\tearDown();
    }
});
