<?php
/**
 * Base Test Case for BlazeCommerce WordPress Child Theme
 * 
 * @package BlazeCommerce
 * @subpackage Tests
 */

namespace BlazeCommerce\Tests\Helpers;

use WP_UnitTestCase;
use Brain\Monkey;

/**
 * Base test case class that provides common functionality for all tests
 */
class TestCase extends WP_UnitTestCase {
    
    /**
     * Set up test environment before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Set up Brain Monkey for mocking WordPress functions
        Monkey\setUp();
        
        // Clear any existing hooks
        $this->clear_hooks();
        
        // Set up common test data
        $this->set_up_test_data();
    }
    
    /**
     * Clean up after each test
     */
    public function tearDown(): void {
        // Clean up Brain Monkey
        Monkey\tearDown();
        
        // Clear hooks and filters
        $this->clear_hooks();
        
        // Clean up test data
        $this->clean_up_test_data();
        
        parent::tearDown();
    }
    
    /**
     * Clear all WordPress hooks and filters
     */
    protected function clear_hooks() {
        global $wp_filter, $wp_actions;
        
        if (isset($wp_filter)) {
            $wp_filter = array();
        }
        
        if (isset($wp_actions)) {
            $wp_actions = array();
        }
    }
    
    /**
     * Set up common test data
     */
    protected function set_up_test_data() {
        // Override in child classes as needed
    }
    
    /**
     * Clean up test data
     */
    protected function clean_up_test_data() {
        // Override in child classes as needed
    }
    
    /**
     * Create a test user with specific capabilities
     * 
     * @param array $args User arguments
     * @return int User ID
     */
    protected function create_test_user($args = array()) {
        $defaults = array(
            'user_login' => 'testuser_' . wp_generate_password(8, false),
            'user_email' => 'test@example.com',
            'user_pass' => 'password',
            'role' => 'subscriber'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        return $this->factory->user->create($args);
    }
    
    /**
     * Create a test post
     * 
     * @param array $args Post arguments
     * @return int Post ID
     */
    protected function create_test_post($args = array()) {
        $defaults = array(
            'post_title' => 'Test Post',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'post'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        return $this->factory->post->create($args);
    }
    
    /**
     * Assert that a hook is registered
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     */
    protected function assertHookRegistered($hook, $callback = null, $priority = 10) {
        $this->assertTrue(has_action($hook, $callback), 
            "Hook '{$hook}' is not registered" . ($callback ? " with callback" : ""));
        
        if ($callback) {
            $this->assertEquals($priority, has_action($hook, $callback),
                "Hook '{$hook}' is not registered with priority {$priority}");
        }
    }
    
    /**
     * Assert that a filter is registered
     * 
     * @param string $filter Filter name
     * @param callable $callback Callback function
     * @param int $priority Priority
     */
    protected function assertFilterRegistered($filter, $callback = null, $priority = 10) {
        $this->assertTrue(has_filter($filter, $callback), 
            "Filter '{$filter}' is not registered" . ($callback ? " with callback" : ""));
        
        if ($callback) {
            $this->assertEquals($priority, has_filter($filter, $callback),
                "Filter '{$filter}' is not registered with priority {$priority}");
        }
    }
    
    /**
     * Load test fixture data
     * 
     * @param string $fixture_name Fixture file name (without .json extension)
     * @return array Fixture data
     */
    protected function load_fixture($fixture_name) {
        $fixture_file = BLAZE_COMMERCE_TEST_DATA_DIR . '/' . $fixture_name . '.json';
        
        if (!file_exists($fixture_file)) {
            $this->fail("Fixture file '{$fixture_file}' does not exist");
        }
        
        $data = file_get_contents($fixture_file);
        $decoded = json_decode($data, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail("Invalid JSON in fixture file '{$fixture_file}': " . json_last_error_msg());
        }
        
        return $decoded;
    }
}
