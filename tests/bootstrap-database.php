<?php
/**
 * PHPUnit Bootstrap File for Database Tests
 * 
 * This file is loaded before database tests are run and sets up the testing environment
 * 
 * @package BlazeCommerce\Tests\Database
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

// Database test configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}

if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: '[REPLACE_WITH_DB_USER]');
}

if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '[REPLACE_WITH_DB_PASSWORD]');
}

if (!defined('DB_PORT')) {
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
}

// Test database name
if (!defined('TEST_DB_NAME')) {
    define('TEST_DB_NAME', 'blaze_commerce_test');
}

// Set timezone
date_default_timezone_set('UTC');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Memory limit for large dataset tests
ini_set('memory_limit', '512M');

echo "Database Test Bootstrap Loaded\n";
echo "Host: " . DB_HOST . "\n";
echo "User: " . DB_USER . "\n";
echo "Test DB: " . TEST_DB_NAME . "\n";
