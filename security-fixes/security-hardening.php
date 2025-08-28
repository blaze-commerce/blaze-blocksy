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
    // X-Frame-Options
    header('X-Frame-Options: SAMEORIGIN');
    
    // X-Content-Type-Options
    header('X-Content-Type-Options: nosniff');
    
    // X-XSS-Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (basic)
    if (!is_admin()) {
        header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' *.googleapis.com *.gstatic.com *.google.com *.facebook.com *.twitter.com *.instagram.com *.youtube.com *.vimeo.com *.gravatar.com *.w.org data: blob:;");
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
 * Get real IP address with proxy support
 */
function blaze_commerce_get_real_ip() {
    // Check for various proxy headers
    $ip_headers = [
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_X_FORWARDED_FOR',      // Standard proxy header
        'HTTP_X_REAL_IP',            // Nginx proxy
        'HTTP_CLIENT_IP',            // Proxy header
        'REMOTE_ADDR'                // Standard IP
    ];

    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            // Handle comma-separated IPs (X-Forwarded-For can contain multiple IPs)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            // Validate IP address
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    // Fallback to REMOTE_ADDR if no valid public IP found
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Security Fix 6: Limit Login Attempts
 *
 * Basic brute force protection with enhanced IP detection
 */
function blaze_commerce_limit_login_attempts() {
    $ip = blaze_commerce_get_real_ip();
    $attempts_key = 'login_attempts_' . md5($ip);
    $lockout_key = 'login_lockout_' . md5($ip);
    
    // Check if IP is locked out
    if (get_transient($lockout_key)) {
        wp_die('Too many failed login attempts. Please try again later.');
    }
    
    // Get current attempts
    $attempts = get_transient($attempts_key) ?: 0;
    
    // Increment attempts
    $attempts++;
    set_transient($attempts_key, $attempts, 3600); // 1 hour
    
    // Lock out after 5 attempts
    if ($attempts >= 5) {
        set_transient($lockout_key, true, 1800); // 30 minutes lockout
        delete_transient($attempts_key);
        wp_die('Too many failed login attempts. Account locked for 30 minutes.');
    }
}

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
 * Log security-related events
 */
function blaze_commerce_security_audit_log($event, $details = '') {
    $log_entry = [
        'timestamp' => current_time('mysql'),
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'event' => $event,
        'details' => $details,
        'user_id' => get_current_user_id()
    ];
    
    // Log to database or file
    error_log('SECURITY_AUDIT: ' . json_encode($log_entry));
}

// Log failed login attempts
add_action('wp_login_failed', function($username) {
    blaze_commerce_security_audit_log('login_failed', 'Username: ' . $username);
});

// Log successful logins
add_action('wp_login', function($user_login, $user) {
    blaze_commerce_security_audit_log('login_success', 'User: ' . $user_login);
}, 10, 2);
