<?php
/**
 * Performance Enhancement Functions
 * 
 * Comprehensive performance optimizations based on baseline analysis
 * 
 * @package BlazeCommerce\Performance
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Performance Optimization 1: Browser Caching and CDN
 * 
 * Implements aggressive caching strategies for static assets
 */
function blaze_commerce_optimize_caching() {
    // Set cache headers for static assets
    add_action('wp_head', function() {
        if (!is_admin()) {
            echo '<meta http-equiv="Cache-Control" content="public, max-age=31536000">' . "\n";
        }
    });
    
    // Optimize WordPress query caching
    add_action('init', function() {
        // Enable object caching if available
        if (function_exists('wp_cache_set')) {
            wp_cache_set('blaze_commerce_cache_enabled', true, 'performance', 3600);
        }
    });
}
add_action('init', 'blaze_commerce_optimize_caching');

/**
 * Performance Optimization 2: Image Optimization
 * 
 * Implements WebP format and lazy loading for images
 */
function blaze_commerce_optimize_images() {
    // Add WebP support with enhanced memory management
    add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
        if (!function_exists('imagewebp')) {
            return $metadata;
        }

        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            return $metadata;
        }

        $info = pathinfo($file);
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        if (!in_array(strtolower($info['extension']), $allowed_extensions)) {
            return $metadata;
        }

        // Check file size to prevent memory issues (skip files larger than 10MB)
        if (filesize($file) > 10 * 1024 * 1024) {
            return $metadata;
        }

        $webp_file = $info['dirname'] . '/' . $info['filename'] . '.webp';

        // Skip if WebP already exists and is newer
        if (file_exists($webp_file) && filemtime($webp_file) >= filemtime($file)) {
            return $metadata;
        }

        // Create WebP version with error handling
        $image = null;
        try {
            switch (strtolower($info['extension'])) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($file);
                    break;
                case 'png':
                    $image = imagecreatefrompng($file);
                    // Preserve transparency for PNG
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                    break;
            }

            if ($image && imagewebp($image, $webp_file, 85)) {
                // Set proper file permissions
                chmod($webp_file, 0644);
            }

        } catch (Exception $e) {
            // Log error but don't break the process
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('BlazeCommerce WebP conversion failed: ' . $e->getMessage());
            }
        } finally {
            // Always clean up memory
            if ($image) {
                imagedestroy($image);
            }
        }

        return $metadata;
    }, 10, 2);
    
    // Add lazy loading to images
    add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
        if (!is_admin() && !wp_is_mobile()) {
            $attr['loading'] = 'lazy';
            $attr['decoding'] = 'async';
        }
        return $attr;
    }, 10, 3);
    
    // Optimize image sizes
    add_filter('wp_calculate_image_srcset_meta', function($image_meta, $size_array, $image_src, $attachment_id) {
        // Add responsive image optimization
        if (isset($image_meta['sizes'])) {
            foreach ($image_meta['sizes'] as $size => $data) {
                if ($data['width'] > 1920) {
                    unset($image_meta['sizes'][$size]);
                }
            }
        }
        return $image_meta;
    }, 10, 4);
}
add_action('init', 'blaze_commerce_optimize_images');

/**
 * Performance Optimization 3: JavaScript Optimization
 * 
 * Minimizes and defers non-critical JavaScript
 */
function blaze_commerce_optimize_javascript() {
    // Defer non-critical JavaScript
    add_filter('script_loader_tag', function($tag, $handle, $src) {
        // Critical scripts that should not be deferred
        $critical_scripts = [
            'jquery-core',
            'jquery-migrate',
            'woocommerce',
            'wc-checkout'
        ];
        
        if (!in_array($handle, $critical_scripts) && !is_admin()) {
            // Add defer attribute to non-critical scripts
            $tag = str_replace(' src', ' defer src', $tag);
        }
        
        return $tag;
    }, 10, 3);
    
    // Remove unnecessary scripts
    add_action('wp_enqueue_scripts', function() {
        if (!is_admin()) {
            // Remove emoji scripts
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('wp_print_styles', 'print_emoji_styles');
            
            // Remove unnecessary WordPress scripts
            wp_deregister_script('wp-embed');
            
            // Conditionally load WooCommerce scripts
            if (!is_woocommerce() && !is_cart() && !is_checkout()) {
                wp_dequeue_script('woocommerce');
                wp_dequeue_script('wc-cart-fragments');
            }
        }
    }, 100);
    
    // Optimize jQuery loading with fallback
    add_action('wp_enqueue_scripts', function() {
        if (!is_admin() && !defined('BLAZE_COMMERCE_DISABLE_CDN_JQUERY')) {
            // Use jQuery from CDN for better caching with integrity check
            wp_deregister_script('jquery');
            wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', array(), '3.6.0', true);

            // Add integrity and crossorigin attributes for security
            add_filter('script_loader_tag', function($tag, $handle) {
                if ($handle === 'jquery' && strpos($tag, 'googleapis.com') !== false) {
                    $tag = str_replace('<script ', '<script integrity="sha384-vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK" crossorigin="anonymous" ', $tag);
                }
                return $tag;
            }, 10, 2);

            wp_enqueue_script('jquery');
        }
    }, 1);
}
add_action('init', 'blaze_commerce_optimize_javascript');

/**
 * Performance Optimization 4: CSS Optimization
 * 
 * Inlines critical CSS and defers non-critical styles
 */
function blaze_commerce_optimize_css() {
    // Critical CSS inlining
    add_action('wp_head', function() {
        if (!is_admin()) {
            $critical_css = "
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
                .header { background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .main-content { min-height: 60vh; }
                .loading { opacity: 0.7; pointer-events: none; }
                .btn-primary { background: #007cba; color: #fff; padding: 12px 24px; border: none; border-radius: 4px; }
                .btn-primary:hover { background: #005a87; }
            ";
            
            echo '<style id="critical-css">' . $critical_css . '</style>' . "\n";
        }
    }, 1);
    
    // Defer non-critical CSS
    add_filter('style_loader_tag', function($html, $handle, $href, $media) {
        // Critical styles that should load immediately
        $critical_styles = [
            'blocksy-child-style',
            'woocommerce-layout',
            'woocommerce-smallscreen',
            'woocommerce-general'
        ];
        
        if (!in_array($handle, $critical_styles) && !is_admin()) {
            // Convert to preload and load asynchronously
            $html = '<link rel="preload" href="' . $href . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
            $html .= '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>' . "\n";
        }
        
        return $html;
    }, 10, 4);
    
    // Remove unused CSS
    add_action('wp_enqueue_scripts', function() {
        if (!is_admin()) {
            // Remove block library CSS if not using Gutenberg blocks
            if (!has_blocks()) {
                wp_dequeue_style('wp-block-library');
                wp_dequeue_style('wp-block-library-theme');
            }
            
            // Remove classic themes CSS
            wp_dequeue_style('classic-theme-styles');
        }
    }, 100);
}
add_action('init', 'blaze_commerce_optimize_css');

/**
 * Performance Optimization 5: Database Query Optimization
 * 
 * Optimizes database queries and implements query caching
 */
function blaze_commerce_optimize_database() {
    // Optimize WooCommerce queries
    add_action('pre_get_posts', function($query) {
        if (!is_admin() && $query->is_main_query()) {
            // Limit posts per page for better performance
            if (is_shop() || is_product_category()) {
                $query->set('posts_per_page', 12);
            }
            
            // Optimize product queries
            if (is_shop()) {
                $query->set('meta_query', array(
                    array(
                        'key' => '_visibility',
                        'value' => array('catalog', 'visible'),
                        'compare' => 'IN'
                    )
                ));
            }
        }
    });
    
    // Implement query caching
    add_action('init', function() {
        // Cache expensive queries
        add_filter('posts_pre_query', function($posts, $query) {
            if (!is_admin() && $query->is_main_query()) {
                $cache_key = 'blaze_commerce_query_' . md5(serialize($query->query_vars));
                $cached_posts = wp_cache_get($cache_key, 'posts');
                
                if ($cached_posts !== false) {
                    return $cached_posts;
                }
            }
            
            return $posts;
        }, 10, 2);
        
        // Cache query results
        add_action('wp', function() {
            if (!is_admin() && is_main_query()) {
                global $wp_query;
                $cache_key = 'blaze_commerce_query_' . md5(serialize($wp_query->query_vars));
                wp_cache_set($cache_key, $wp_query->posts, 'posts', 300); // 5 minutes
            }
        });
    });
    
    // Optimize database connections
    add_action('init', function() {
        // Enable persistent connections if available
        if (!defined('DB_PERSISTENT_CONNECTION')) {
            define('DB_PERSISTENT_CONNECTION', true);
        }
    });
}
add_action('init', 'blaze_commerce_optimize_database');

/**
 * Performance Optimization 6: Resource Preloading
 * 
 * Preloads critical resources for faster loading
 */
function blaze_commerce_preload_resources() {
    add_action('wp_head', function() {
        if (!is_admin()) {
            // Preload critical fonts
            echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" as="style">' . "\n";
            
            // Preload critical images
            if (is_front_page()) {
                echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/images/hero-bg.webp" as="image">' . "\n";
            }
            
            // DNS prefetch for external resources
            echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
            echo '<link rel="dns-prefetch" href="//ajax.googleapis.com">' . "\n";
            echo '<link rel="dns-prefetch" href="//www.google-analytics.com">' . "\n";
        }
    }, 1);
}
add_action('init', 'blaze_commerce_preload_resources');

/**
 * Performance Optimization 7: Output Compression
 * 
 * Enables GZIP compression and output optimization
 */
function blaze_commerce_optimize_output() {
    // Enable GZIP compression
    add_action('init', function() {
        if (!is_admin() && !headers_sent()) {
            if (function_exists('gzencode') && !ob_get_level()) {
                ob_start('ob_gzhandler');
            }
        }
    });
    
    // Minify HTML output
    add_action('wp_loaded', function() {
        if (!is_admin()) {
            ob_start(function($html) {
                // Remove unnecessary whitespace
                $html = preg_replace('/\s+/', ' ', $html);
                $html = preg_replace('/>\s+</', '><', $html);
                
                // Remove HTML comments (except IE conditionals)
                $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
                
                return trim($html);
            });
        }
    });
}
add_action('init', 'blaze_commerce_optimize_output');

/**
 * Performance Monitoring and Metrics
 * 
 * Tracks performance metrics for continuous optimization
 */
function blaze_commerce_performance_monitoring() {
    // Add performance timing to footer
    add_action('wp_footer', function() {
        if (!is_admin() && current_user_can('administrator')) {
            $load_time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            echo '<script>console.log("Page load time: ' . round($load_time * 1000, 2) . 'ms");</script>' . "\n";
        }
    });
    
    // Log slow queries
    add_action('shutdown', function() {
        if (defined('SAVEQUERIES') && SAVEQUERIES) {
            global $wpdb;
            $slow_queries = 0;
            
            foreach ($wpdb->queries as $query) {
                if ($query[1] > 0.05) { // Queries slower than 50ms
                    $slow_queries++;
                }
            }
            
            if ($slow_queries > 0) {
                error_log("Blaze Commerce Performance: {$slow_queries} slow queries detected");
            }
        }
    });
}
add_action('init', 'blaze_commerce_performance_monitoring');
