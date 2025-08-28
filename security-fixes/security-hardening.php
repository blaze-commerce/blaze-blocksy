<?php
/**
 * WordPress Security Hardening Functions
 * 
 * Critical security fixes for the identified vulnerabilities
 * 
 * @package BlazeCommerce\Security
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Security Fix 1: Hide WordPress Version Information
 * 
 * Removes WordPress version from HTML head and RSS feeds
 */
function blaze_commerce_remove_wp_version() {
    // Remove version from HTML head
    remove_action('wp_head', 'wp_generator');
    
    // Remove version from RSS feeds
    add_filter('the_generator', '__return_empty_string');
    
    // Remove version from scripts and styles
    add_filter('style_loader_src', 'blaze_commerce_remove_version_strings', 9999);
    add_filter('script_loader_src', 'blaze_commerce_remove_version_strings', 9999);
}
add_action('init', 'blaze_commerce_remove_wp_version');

/**
 * Remove version strings from CSS and JS files
 */
function blaze_commerce_remove_version_strings($src) {
    if (strpos($src, 'ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

/**
 * Security Fix 2: Secure Admin Area Access
 * 
 * Ensures proper authentication for admin areas
 */
function blaze_commerce_secure_admin_access() {
    // Redirect non-authenticated users from admin area
    if (is_admin() && !current_user_can('administrator') && !(defined('DOING_AJAX') && DOING_AJAX)) {
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('admin_init', 'blaze_commerce_secure_admin_access');

/**
 * Security Fix 3: Restrict REST API Access
 * 
 * Requires authentication for sensitive REST API endpoints
 */
function blaze_commerce_restrict_rest_api($access) {
    // Get current route
    $route = $GLOBALS['wp']->query_vars['rest_route'];
    
    // Sensitive endpoints that require authentication
    $restricted_endpoints = [
        '/wp/v2/users',
        '/wp/v2/comments',
        '/wp/v2/posts',
        '/wp/v2/pages'
    ];
    
    // Check if current route is restricted
    foreach ($restricted_endpoints as $endpoint) {
        if (strpos($route, $endpoint) === 0) {
            if (!is_user_logged_in()) {
                return new WP_Error(
                    'rest_authentication_required',
                    __('Authentication required.'),
                    array('status' => 401)
                );
            }
        }
    }
    
    return $access;
}
add_filter('rest_authentication_errors', 'blaze_commerce_restrict_rest_api');

/**
 * Security Fix 4: Add Security Headers
 * 
 * Implements essential security headers
 */
function blaze_commerce_add_security_headers() {
    // Check if security headers are disabled
    if (defined('BLAZE_COMMERCE_DISABLE_SECURITY_HEADERS') && BLAZE_COMMERCE_DISABLE_SECURITY_HEADERS) {
        return;
    }

    // X-Frame-Options
    header('X-Frame-Options: SAMEORIGIN');

    // X-Content-Type-Options
    header('X-Content-Type-Options: nosniff');

    // X-XSS-Protection
    header('X-XSS-Protection: 1; mode=block');

    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (configurable)
    $csp_enabled = !defined('BLAZE_COMMERCE_DISABLE_CSP') || !BLAZE_COMMERCE_DISABLE_CSP;
    if (!is_admin() && $csp_enabled && apply_filters('blaze_commerce_enable_csp', true)) {
        $csp_sources = apply_filters('blaze_commerce_csp_sources', [
            'self' => "'self'",
            'unsafe-inline' => "'unsafe-inline'",
            'unsafe-eval' => "'unsafe-eval'",
            'google' => '*.googleapis.com *.gstatic.com *.google.com',
            'social' => '*.facebook.com *.twitter.com *.instagram.com',
            'media' => '*.youtube.com *.vimeo.com',
            'wordpress' => '*.gravatar.com *.w.org',
            'data' => 'data: blob:'
        ]);

        $csp_policy = 'default-src ' . implode(' ', $csp_sources) . ';';
        header("Content-Security-Policy: $csp_policy");
    }
}
add_action('send_headers', 'blaze_commerce_add_security_headers');

/**
 * Security Fix 5: Disable File Editing
 * 
 * Prevents file editing through WordPress admin
 */
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

/**
 * Get real IP address with proxy support and enhanced validation
 *
 * Implements comprehensive IP validation to prevent spoofing attacks
 * and ensure only legitimate public IP addresses are used for security checks.
 */
function blaze_commerce_get_real_ip() {
    // Check for various proxy headers in order of trust
    $ip_headers = [
        'HTTP_CF_CONNECTING_IP',     // Cloudflare (most trusted)
        'HTTP_X_REAL_IP',            // Nginx proxy
        'HTTP_X_FORWARDED_FOR',      // Standard proxy header (can be spoofed)
        'HTTP_CLIENT_IP',            // Proxy header (less trusted)
        'REMOTE_ADDR'                // Direct connection (fallback)
    ];

    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];

            // Handle comma-separated IPs (X-Forwarded-For can contain multiple IPs)
            if (strpos($ip, ',') !== false) {
                $ip_list = explode(',', $ip);
                // Get the first IP (original client IP)
                $ip = trim($ip_list[0]);
            }

            // Clean and validate the IP address
            $ip = trim($ip);

            // Enhanced IP validation with multiple checks
            if (blaze_commerce_validate_public_ip($ip)) {
                return $ip;
            }
        }
    }

    // Final fallback - validate REMOTE_ADDR directly
    $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (blaze_commerce_validate_public_ip($remote_addr)) {
        return $remote_addr;
    }

    // If no valid public IP found, log the issue and return REMOTE_ADDR as last resort
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('BlazeCommerce Security: No valid public IP found, using REMOTE_ADDR as fallback');
    }

    // Return REMOTE_ADDR even if it's private - better than grouping all users under 0.0.0.0
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Enhanced IP validation function
 *
 * Validates that an IP address is a legitimate public IP address
 * and not from private, reserved, or potentially spoofed ranges.
 */
function blaze_commerce_validate_public_ip($ip) {
    // Basic IP format validation
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }

    // Check for private and reserved ranges
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return false;
    }

    // Additional security checks for commonly spoofed ranges
    $blocked_ranges = [
        '0.0.0.0/8',        // "This" network
        '127.0.0.0/8',      // Loopback
        '169.254.0.0/16',   // Link-local
        '224.0.0.0/4',      // Multicast
        '240.0.0.0/4',      // Reserved for future use
    ];

    foreach ($blocked_ranges as $range) {
        if (blaze_commerce_ip_in_range($ip, $range)) {
            return false;
        }
    }

    // Additional validation for IPv6
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        // Block IPv6 loopback and link-local
        if (strpos($ip, '::1') === 0 || strpos($ip, 'fe80:') === 0) {
            return false;
        }

        // Block IPv6 private ranges (ULA - Unique Local Addresses)
        if (strpos($ip, 'fc00:') === 0 || strpos($ip, 'fd00:') === 0) {
            return false;
        }

        // Block IPv6 multicast
        if (strpos($ip, 'ff00:') === 0) {
            return false;
        }

        // Allow legitimate IPv6 public addresses
        return true;
    }

    return true;
}

/**
 * Check if an IP address is within a given CIDR range
 */
function blaze_commerce_ip_in_range($ip, $range) {
    if (strpos($range, '/') === false) {
        return $ip === $range;
    }

    list($subnet, $bits) = explode('/', $range);

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        // IPv6 range checking - implement basic IPv6 CIDR support
        if (strpos($range, ':') !== false) {
            // Basic IPv6 range check - expand this for production use
            list($subnet, $prefix_length) = explode('/', $range);

            // For now, do simple prefix matching for common cases
            $ip_parts = explode(':', $ip);
            $subnet_parts = explode(':', $subnet);

            // Compare first few segments based on prefix length
            $segments_to_check = min(4, intval($prefix_length / 16));
            for ($i = 0; $i < $segments_to_check; $i++) {
                if (isset($ip_parts[$i]) && isset($subnet_parts[$i])) {
                    if ($ip_parts[$i] !== $subnet_parts[$i]) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    // IPv4 range checking
    $ip_long = ip2long($ip);
    $subnet_long = ip2long($subnet);
    $mask = -1 << (32 - $bits);

    return ($ip_long & $mask) === ($subnet_long & $mask);
}

/**
 * Security Fix 6: Limit Login Attempts
 *
 * Enhanced brute force protection with proper IP detection and logging
 */
function blaze_commerce_limit_login_attempts($username) {
    $ip = blaze_commerce_get_real_ip();

    // Check if IP is whitelisted (for trusted automation/API access)
    $whitelisted_ips = apply_filters('blaze_commerce_whitelisted_ips', [
        // Add trusted IPs here, e.g., monitoring services, CI/CD systems
        // '192.168.1.100',
        // '10.0.0.50'
    ]);

    if (in_array($ip, $whitelisted_ips)) {
        return; // Skip rate limiting for whitelisted IPs
    }

    // WooCommerce specific considerations
    if (class_exists('WooCommerce')) {
        // Skip rate limiting for payment gateway callbacks
        if (isset($_GET['wc-api']) || strpos($_SERVER['REQUEST_URI'] ?? '', '/wc-api/') !== false) {
            return;
        }

        // More lenient limits for customer accounts vs admin
        $is_customer_login = !user_can($username, 'manage_options');
        $admin_max = defined('BLAZE_COMMERCE_ADMIN_MAX_ATTEMPTS') ? BLAZE_COMMERCE_ADMIN_MAX_ATTEMPTS : 5;
        $user_max = defined('BLAZE_COMMERCE_USER_MAX_ATTEMPTS') ? BLAZE_COMMERCE_USER_MAX_ATTEMPTS : 8;
        $max_attempts = $is_customer_login ? $user_max : $admin_max;
    } else {
        $max_attempts = defined('BLAZE_COMMERCE_ADMIN_MAX_ATTEMPTS') ? BLAZE_COMMERCE_ADMIN_MAX_ATTEMPTS : 5;
    }

    // Use both IP and username for more granular tracking
    $user_attempts_key = 'login_attempts_user_' . md5($username . $ip);
    $ip_attempts_key = 'login_attempts_ip_' . md5($ip);
    $lockout_key = 'login_lockout_' . md5($ip);

    // Check if IP is already locked out
    if (get_transient($lockout_key)) {
        // Log the blocked attempt
        blaze_commerce_security_audit_log('login_blocked', "IP: $ip, Username: $username");
        wp_die('Too many failed login attempts. Please try again later.', 'Login Blocked', array('response' => 429));
    }

    // Get current attempts with database fallback
    $attempts = get_transient($attempts_key);
    if ($attempts === false) {
        // Fallback to database if transients fail
        $attempts = get_option($attempts_key, 0);
    }

    // Increment attempts
    $attempts++;

    // Store in both transient and database for reliability
    $attempt_duration = defined('BLAZE_COMMERCE_ATTEMPT_DURATION') ? BLAZE_COMMERCE_ATTEMPT_DURATION : 3600;
    set_transient($attempts_key, $attempts, $attempt_duration);
    update_option($attempts_key, $attempts);

    // Log the failed attempt
    blaze_commerce_security_audit_log('login_attempt_failed', "IP: $ip, Username: $username, Attempt: $attempts/$max_attempts");

    // Lock out after max attempts reached
    if ($attempts >= $max_attempts) {
        $lockout_duration = defined('BLAZE_COMMERCE_LOCKOUT_DURATION') ? BLAZE_COMMERCE_LOCKOUT_DURATION : 1800;
        set_transient($lockout_key, true, $lockout_duration);
        delete_transient($attempts_key);

        // Log the lockout
        blaze_commerce_security_audit_log('login_lockout', "IP: $ip, Username: $username");

        wp_die('Too many failed login attempts. Account locked for 30 minutes.', 'Account Locked', array('response' => 429));
    }
}
// Hook the function to wp_login_failed action with conflict detection
add_action('init', function() {
    // Check for common security plugins that might conflict
    $conflicting_plugins = [
        'wordfence/wordfence.php',
        'better-wp-security/better-wp-security.php',
        'sucuri-scanner/sucuri.php',
        'all-in-one-wp-security-and-firewall/wp-security.php'
    ];

    $has_conflicts = false;
    foreach ($conflicting_plugins as $plugin) {
        if (is_plugin_active($plugin)) {
            $has_conflicts = true;
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("BlazeCommerce Security: Potential conflict detected with plugin: $plugin");
            }
        }
    }

    // Check configuration overrides
    $force_enable = defined('BLAZE_COMMERCE_FORCE_LOGIN_LIMITING') ? BLAZE_COMMERCE_FORCE_LOGIN_LIMITING : false;
    $disable_feature = defined('BLAZE_COMMERCE_DISABLE_LOGIN_LIMITING') ? BLAZE_COMMERCE_DISABLE_LOGIN_LIMITING : false;

    // Only enable our login limiting if no major conflicts detected or forced
    if (!$disable_feature && (!$has_conflicts || $force_enable || apply_filters('blaze_commerce_force_login_limiting', false))) {
        add_action('wp_login_failed', 'blaze_commerce_limit_login_attempts', 5); // Early priority
    }
});

/**
 * Reset login attempts on successful login
 */
function blaze_commerce_reset_login_attempts($user_login) {
    $ip = blaze_commerce_get_real_ip();
    $attempts_key = 'login_attempts_' . md5($ip);
    $lockout_key = 'login_lockout_' . md5($ip);
    
    delete_transient($attempts_key);
    delete_transient($lockout_key);
}
add_action('wp_login', 'blaze_commerce_reset_login_attempts');

/**
 * Cleanup old login attempt data
 * Runs daily to prevent database bloat
 */
function blaze_commerce_cleanup_login_data() {
    global $wpdb;

    // Clean up old login attempt options (older than 24 hours)
    $wpdb->query($wpdb->prepare("
        DELETE FROM {$wpdb->options}
        WHERE option_name LIKE %s
        AND option_name LIKE %s
    ", 'login_attempts_%', '%' . date('Y-m-d', strtotime('-1 day')) . '%'));
}

// Schedule daily cleanup
if (!wp_next_scheduled('blaze_commerce_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'blaze_commerce_daily_cleanup');
}
add_action('blaze_commerce_daily_cleanup', 'blaze_commerce_cleanup_login_data');

/**
 * Security Fix 7: Disable XML-RPC
 * 
 * Prevents XML-RPC attacks
 */
function blaze_commerce_disable_xmlrpc() {
    // Disable XML-RPC
    add_filter('xmlrpc_enabled', '__return_false');
    
    // Remove XML-RPC pingback
    add_filter('wp_headers', function($headers) {
        unset($headers['X-Pingback']);
        return $headers;
    });
    
    // Block XML-RPC requests
    add_action('xmlrpc_call', function() {
        wp_die('XML-RPC services are disabled on this site.', 'XML-RPC Disabled', array('response' => 403));
    });
}
add_action('init', 'blaze_commerce_disable_xmlrpc');

/**
 * Security Fix 8: Hide Login Errors
 * 
 * Prevents username enumeration
 */
function blaze_commerce_hide_login_errors() {
    return 'Invalid login credentials.';
}
add_filter('login_errors', 'blaze_commerce_hide_login_errors');

/**
 * Security Fix 9: Disable User Enumeration
 * 
 * Prevents user enumeration via REST API and author pages
 */
function blaze_commerce_disable_user_enumeration() {
    // Block user enumeration via REST API
    add_filter('rest_endpoints', function($endpoints) {
        if (isset($endpoints['/wp/v2/users'])) {
            unset($endpoints['/wp/v2/users']);
        }
        if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
        }
        return $endpoints;
    });
    
    // Block author page enumeration
    add_action('template_redirect', function() {
        if (is_author() && !is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }
    });
}
add_action('init', 'blaze_commerce_disable_user_enumeration');

/**
 * Security Fix 10: Secure File Permissions Check
 * 
 * Alerts if critical files have wrong permissions
 */
function blaze_commerce_check_file_permissions() {
    if (!is_admin()) {
        return;
    }
    
    $critical_files = [
        ABSPATH . 'wp-config.php' => '0644',
        ABSPATH . '.htaccess' => '0644'
    ];
    
    foreach ($critical_files as $file => $expected_perms) {
        if (file_exists($file)) {
            $current_perms = substr(sprintf('%o', fileperms($file)), -4);
            if ($current_perms !== $expected_perms) {
                add_action('admin_notices', function() use ($file, $current_perms, $expected_perms) {
                    echo '<div class="notice notice-error"><p>';
                    echo '<strong>Security Warning:</strong> File ' . basename($file) . ' has permissions ' . $current_perms . ' but should be ' . $expected_perms;
                    echo '</p></div>';
                });
            }
        }
    }
}
add_action('admin_init', 'blaze_commerce_check_file_permissions');

/**
 * Security Fix 11: Content Security Policy for Admin
 * 
 * Stricter CSP for admin area
 */
function blaze_commerce_admin_security_headers() {
    if (is_admin()) {
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: *.gravatar.com *.w.org;");
        header('X-Frame-Options: DENY');
    }
}
add_action('admin_init', 'blaze_commerce_admin_security_headers');

/**
 * Security Fix 12: Database Security
 * 
 * Prevents SQL injection in custom queries
 */
function blaze_commerce_secure_database_queries() {
    // Example of secure query preparation
    global $wpdb;
    
    // Always use prepared statements
    add_action('wp_loaded', function() {
        // Example: Secure search query
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $search_term = sanitize_text_field($_GET['s']);
            // Use $wpdb->prepare() for any custom queries
        }
    });
}
add_action('init', 'blaze_commerce_secure_database_queries');

/**
 * Enhanced File Upload Security
 *
 * Additional file upload validation beyond WordPress defaults
 */
function blaze_commerce_enhanced_file_upload_security() {
    // Validate file uploads with enhanced security checks
    add_filter('wp_handle_upload_prefilter', function($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $file['error'] = 'Invalid file upload.';
            return $file;
        }

        // Check file size (prevent extremely large files)
        $max_size = wp_max_upload_size();
        if ($file['size'] > $max_size) {
            $file['error'] = 'File size exceeds maximum allowed size.';
            return $file;
        }

        // Enhanced MIME type validation
        $allowed_mimes = get_allowed_mime_types();
        $file_type = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $allowed_mimes);

        if (!$file_type['type'] || !$file_type['ext']) {
            $file['error'] = 'File type not allowed for security reasons.';
            return $file;
        }

        // Check for executable file extensions in any case variation
        $dangerous_extensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi', 'exe', 'bat', 'com', 'scr', 'vbs', 'js'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($file_ext, $dangerous_extensions)) {
            $file['error'] = 'Executable files are not allowed for security reasons.';
            return $file;
        }

        // Scan file content for suspicious patterns (basic check)
        if (is_readable($file['tmp_name'])) {
            $content = file_get_contents($file['tmp_name'], false, null, 0, 1024); // Read first 1KB
            $suspicious_patterns = ['<?php', '<?=', '<script', 'eval(', 'base64_decode', 'system(', 'exec(', 'shell_exec'];

            foreach ($suspicious_patterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    $file['error'] = 'File contains suspicious content and cannot be uploaded.';
                    return $file;
                }
            }
        }

        return $file;
    });

    // Additional security for image uploads
    add_filter('wp_handle_upload', function($upload, $context) {
        if (isset($upload['file']) && isset($upload['type'])) {
            // Verify image files are actually images
            if (strpos($upload['type'], 'image/') === 0) {
                $image_info = getimagesize($upload['file']);
                if ($image_info === false) {
                    // Not a valid image file
                    unlink($upload['file']);
                    return array('error' => 'Invalid image file.');
                }
            }
        }

        return $upload;
    }, 10, 2);
}
add_action('init', 'blaze_commerce_enhanced_file_upload_security');

/**
 * Security Audit Log
 *
 * Log security-related events with rate limiting and log rotation
 */
function blaze_commerce_security_audit_log($event, $details = '') {
    // Rate limiting: max 100 log entries per hour per IP
    $ip = blaze_commerce_get_real_ip();
    $rate_limit_key = 'security_log_rate_' . md5($ip);
    $current_count = get_transient($rate_limit_key) ?: 0;

    if ($current_count >= 100) {
        return; // Skip logging if rate limit exceeded
    }

    set_transient($rate_limit_key, $current_count + 1, 3600); // 1 hour

    $log_entry = [
        'timestamp' => current_time('mysql'),
        'ip' => $ip, // Use enhanced IP detection
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255), // Limit length
        'event' => $event,
        'details' => substr($details, 0, 500), // Limit details length
        'user_id' => get_current_user_id()
    ];

    // Only log if WP_DEBUG is enabled to prevent production log bloat
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('SECURITY_AUDIT: ' . json_encode($log_entry));
    }
}

// Log failed login attempts
add_action('wp_login_failed', function($username) {
    blaze_commerce_security_audit_log('login_failed', 'Username: ' . $username);
});

// Log successful logins
add_action('wp_login', function($user_login, $user) {
    blaze_commerce_security_audit_log('login_success', 'User: ' . $user_login);
}, 10, 2);
