<?php
/**
 * BlazeCommerce Security Configuration Example
 * 
 * Copy this file to security-config.php and customize for your environment.
 * This file should be included in your wp-config.php or loaded early in the WordPress bootstrap.
 * 
 * @package BlazeCommerce\Security
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Trusted IP Addresses
 * 
 * Add IP addresses that should bypass login attempt limiting.
 * Useful for monitoring services, CI/CD systems, and trusted automation.
 */
define('BLAZE_COMMERCE_TRUSTED_IPS', implode(',', [
    // Example trusted IPs - replace with your actual IPs
    // '192.168.1.100',    // Your monitoring server
    // '10.0.0.50',        // Your CI/CD system
    // '203.0.113.10',     // Your office static IP
    // '198.51.100.20',    // Your backup monitoring service
]));

/**
 * Security Feature Toggles
 * 
 * Enable or disable specific security features based on your environment needs.
 */

// Force enable login limiting even if security plugin conflicts are detected
// define('BLAZE_COMMERCE_FORCE_LOGIN_LIMITING', true);

// Disable Content Security Policy if it conflicts with your plugins
// define('BLAZE_COMMERCE_DISABLE_CSP', true);

// Disable security audit logging in production to save disk space
// define('BLAZE_COMMERCE_DISABLE_AUDIT_LOG', true);

// Custom log file path for security audit logs
// define('BLAZE_COMMERCE_AUDIT_LOG_PATH', '/path/to/custom/security.log');

/**
 * Login Attempt Limits
 * 
 * Customize login attempt limits for different user types.
 */

// Maximum login attempts for administrators
// define('BLAZE_COMMERCE_ADMIN_MAX_ATTEMPTS', 3);

// Maximum login attempts for regular users/customers
// define('BLAZE_COMMERCE_USER_MAX_ATTEMPTS', 8);

// Lockout duration in seconds (default: 1800 = 30 minutes)
// define('BLAZE_COMMERCE_LOCKOUT_DURATION', 1800);

// Attempt tracking duration in seconds (default: 3600 = 1 hour)
// define('BLAZE_COMMERCE_ATTEMPT_DURATION', 3600);

/**
 * IP Detection Configuration
 * 
 * Configure how real IP addresses are detected behind proxies and CDNs.
 */

// Trust Cloudflare headers (if using Cloudflare)
// define('BLAZE_COMMERCE_TRUST_CLOUDFLARE', true);

// Custom proxy headers to trust (comma-separated)
// define('BLAZE_COMMERCE_TRUSTED_HEADERS', 'HTTP_X_REAL_IP,HTTP_X_FORWARDED_FOR');

// Fallback IP when no valid public IP is found
// define('BLAZE_COMMERCE_FALLBACK_IP', '127.0.0.1');

/**
 * Content Security Policy Configuration
 * 
 * Customize CSP sources for your specific plugin and theme requirements.
 */

// Additional CSP sources (comma-separated)
// define('BLAZE_COMMERCE_ADDITIONAL_CSP_SOURCES', '*.example.com,*.trusted-cdn.com');

// CSP report URI for violation reporting
// define('BLAZE_COMMERCE_CSP_REPORT_URI', 'https://your-domain.com/csp-report');

/**
 * Database Cleanup Configuration
 * 
 * Configure automatic cleanup of security-related data.
 */

// Enable automatic cleanup of old login attempt data
// define('BLAZE_COMMERCE_ENABLE_CLEANUP', true);

// Cleanup interval in seconds (default: 86400 = daily)
// define('BLAZE_COMMERCE_CLEANUP_INTERVAL', 86400);

// Data retention period in seconds (default: 604800 = 7 days)
// define('BLAZE_COMMERCE_DATA_RETENTION', 604800);

/**
 * Development and Testing Configuration
 * 
 * Settings for development and testing environments.
 */

// Disable all security features in development
if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_ENVIRONMENT') && WP_ENVIRONMENT === 'development') {
    // Uncomment to disable security features in development
    // define('BLAZE_COMMERCE_DISABLE_LOGIN_LIMITING', true);
    // define('BLAZE_COMMERCE_DISABLE_CSP', true);
    // define('BLAZE_COMMERCE_DISABLE_SECURITY_HEADERS', true);
}

/**
 * Integration with External Services
 * 
 * Configuration for integration with external monitoring and security services.
 */

// Webhook URL for security alerts
// define('BLAZE_COMMERCE_SECURITY_WEBHOOK', 'https://your-monitoring.com/webhook');

// API key for external security service integration
// define('BLAZE_COMMERCE_SECURITY_API_KEY', 'your-api-key-here');

/**
 * Advanced Configuration
 * 
 * Advanced settings for fine-tuning security behavior.
 */

// Rate limiting for security audit logs (entries per hour per IP)
// define('BLAZE_COMMERCE_LOG_RATE_LIMIT', 100);

// Enable enhanced IP validation (stricter but may block some legitimate users)
// define('BLAZE_COMMERCE_ENHANCED_IP_VALIDATION', true);

// Custom user agent patterns to block (regex patterns, comma-separated)
// define('BLAZE_COMMERCE_BLOCKED_USER_AGENTS', 'bot,crawler,scanner');

/**
 * Load custom security configuration
 * 
 * Include this file in your wp-config.php:
 * require_once(ABSPATH . 'wp-content/themes/your-theme/security-config.php');
 */
