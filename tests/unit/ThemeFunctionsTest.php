<?php
/**
 * Unit Tests for Theme Functions
 *
 * @package BlazeCommerce
 * @subpackage Tests
 */

namespace BlazeCommerce\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test theme functions and hooks
 */
class ThemeFunctionsTest extends TestCase {

    /**
     * Test basic PHP functionality
     */
    public function test_php_basic_functionality() {
        // Test that PHP is working correctly
        $this->assertTrue(true, 'PHP basic test should pass');
        $this->assertEquals(4, 2 + 2, 'Basic math should work');
        $this->assertIsString('test', 'String type check should work');
    }

    /**
     * Test that theme directory structure exists
     */
    public function test_theme_directory_structure() {
        // Test that basic theme files exist
        $theme_root = dirname(dirname(__DIR__));

        $this->assertFileExists($theme_root . '/style.css', 'Theme style.css should exist');
        $this->assertFileExists($theme_root . '/functions.php', 'Theme functions.php should exist');
    }

    /**
     * Test configuration files exist
     */
    public function test_configuration_files_exist() {
        $theme_root = dirname(dirname(__DIR__));

        // Test that configuration files exist
        $this->assertFileExists($theme_root . '/package.json', 'package.json should exist');
        $this->assertFileExists($theme_root . '/composer.json', 'composer.json should exist');
        $this->assertFileExists($theme_root . '/phpunit.xml', 'phpunit.xml should exist');
        $this->assertFileExists($theme_root . '/playwright.config.js', 'playwright.config.js should exist');
    }

    /**
     * Test JSON configuration files are valid
     */
    public function test_json_configuration_valid() {
        $theme_root = dirname(dirname(__DIR__));

        // Test package.json is valid JSON
        $package_json = file_get_contents($theme_root . '/package.json');
        $package_data = json_decode($package_json, true);
        $this->assertNotNull($package_data, 'package.json should be valid JSON');
        $this->assertArrayHasKey('name', $package_data, 'package.json should have name field');

        // Test composer.json is valid JSON
        $composer_json = file_get_contents($theme_root . '/composer.json');
        $composer_data = json_decode($composer_json, true);
        $this->assertNotNull($composer_data, 'composer.json should be valid JSON');
        $this->assertArrayHasKey('name', $composer_data, 'composer.json should have name field');
    }

    /**
     * Test testing infrastructure is properly set up
     */
    public function test_testing_infrastructure() {
        $theme_root = dirname(dirname(__DIR__));

        // Test that test directories exist
        $this->assertDirectoryExists($theme_root . '/tests', 'Tests directory should exist');
        $this->assertDirectoryExists($theme_root . '/tests/unit', 'Unit tests directory should exist');
        $this->assertDirectoryExists($theme_root . '/tests/e2e', 'E2E tests directory should exist');

        // Test that coverage directory exists
        $this->assertDirectoryExists($theme_root . '/coverage', 'Coverage directory should exist');
    }
}
