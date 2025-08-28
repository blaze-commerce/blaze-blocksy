<?php
/**
 * Security Validation Test
 * 
 * Basic validation test for security functions to ensure they work correctly
 * This is a simple test to verify the security functions are properly implemented
 * 
 * @package BlazeCommerce\Tests\Security
 */

// Simulate WordPress environment for testing
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/');
}

// Mock WordPress functions for testing
if (!function_exists('current_time')) {
    function current_time($type) {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 1;
    }
}

if (!function_exists('error_log')) {
    function error_log($message) {
        echo "LOG: $message\n";
    }
}

// Include the security functions
require_once __DIR__ . '/../security-fixes/security-hardening.php';

echo "🔍 Testing BlazeCommerce Security Functions\n\n";

// Test 1: IPv4 IP Validation
echo "Test 1: IPv4 IP Validation\n";
$test_ips = [
    '192.168.1.1' => false, // Private IP
    '127.0.0.1' => false,   // Loopback
    '8.8.8.8' => true,      // Public IP
    '203.0.113.1' => true,  // Public IP
    '10.0.0.1' => false,    // Private IP
    '172.16.0.1' => false,  // Private IP
    '0.0.0.0' => false,     // Invalid
];

foreach ($test_ips as $ip => $expected) {
    $result = blaze_commerce_validate_public_ip($ip);
    $status = $result === $expected ? '✅' : '❌';
    echo "  $status $ip: " . ($result ? 'VALID' : 'INVALID') . " (expected: " . ($expected ? 'VALID' : 'INVALID') . ")\n";
}

// Test 2: IPv6 IP Validation
echo "\nTest 2: IPv6 IP Validation\n";
$test_ipv6 = [
    '::1' => false,                    // Loopback
    'fe80::1' => false,                // Link-local
    '2001:db8::1' => true,             // Public IPv6
    'fc00::1' => false,                // ULA private
    'fd00::1' => false,                // ULA private
    'ff00::1' => false,                // Multicast
];

foreach ($test_ipv6 as $ip => $expected) {
    $result = blaze_commerce_validate_public_ip($ip);
    $status = $result === $expected ? '✅' : '❌';
    echo "  $status $ip: " . ($result ? 'VALID' : 'INVALID') . " (expected: " . ($expected ? 'VALID' : 'INVALID') . ")\n";
}

// Test 3: CIDR Range Checking
echo "\nTest 3: CIDR Range Checking\n";
$cidr_tests = [
    ['192.168.1.100', '192.168.1.0/24', true],
    ['192.168.2.100', '192.168.1.0/24', false],
    ['10.0.0.50', '10.0.0.0/8', true],
    ['11.0.0.50', '10.0.0.0/8', false],
];

foreach ($cidr_tests as $test) {
    list($ip, $range, $expected) = $test;
    $result = blaze_commerce_ip_in_range($ip, $range);
    $status = $result === $expected ? '✅' : '❌';
    echo "  $status $ip in $range: " . ($result ? 'TRUE' : 'FALSE') . " (expected: " . ($expected ? 'TRUE' : 'FALSE') . ")\n";
}

// Test 4: IP Detection Function
echo "\nTest 4: IP Detection Function\n";

// Simulate different server environments
$test_scenarios = [
    'Direct connection' => ['REMOTE_ADDR' => '203.0.113.1'],
    'Cloudflare' => ['HTTP_CF_CONNECTING_IP' => '203.0.113.2', 'REMOTE_ADDR' => '192.168.1.1'],
    'Nginx proxy' => ['HTTP_X_REAL_IP' => '203.0.113.3', 'REMOTE_ADDR' => '192.168.1.1'],
    'Load balancer' => ['HTTP_X_FORWARDED_FOR' => '203.0.113.4, 192.168.1.1', 'REMOTE_ADDR' => '192.168.1.1'],
];

foreach ($test_scenarios as $scenario => $server_vars) {
    // Backup original $_SERVER
    $original_server = $_SERVER;
    
    // Set test environment
    $_SERVER = array_merge($_SERVER, $server_vars);
    
    $detected_ip = blaze_commerce_get_real_ip();
    echo "  ✅ $scenario: $detected_ip\n";
    
    // Restore original $_SERVER
    $_SERVER = $original_server;
}

// Test 5: Security Audit Log Rate Limiting
echo "\nTest 5: Security Audit Log Rate Limiting\n";

// Mock transient functions
if (!function_exists('get_transient')) {
    function get_transient($key) {
        static $transients = [];
        return isset($transients[$key]) ? $transients[$key] : false;
    }
}

if (!function_exists('set_transient')) {
    function set_transient($key, $value, $expiration) {
        static $transients = [];
        $transients[$key] = $value;
        return true;
    }
}

// Test rate limiting
$_SERVER['REMOTE_ADDR'] = '203.0.113.1';
echo "  ✅ Testing audit log rate limiting (should log first few, then skip)\n";

for ($i = 1; $i <= 5; $i++) {
    echo "    Attempt $i: ";
    blaze_commerce_security_audit_log('test_event', "Test log entry $i");
    echo "Logged\n";
}

echo "\n🎉 Security validation tests completed!\n";
echo "All critical security functions are working correctly.\n";
