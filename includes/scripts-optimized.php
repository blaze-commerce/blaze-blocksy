<?php
/**
 * Optimized Asset Loading for Blaze Commerce
 * 
 * This file handles conditional loading of bundled CSS and JS assets
 * based on context (WooCommerce, admin, customizer) for optimal performance.
 * 
 * @package BlazeCommerce
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Asset loading configuration
 */
class BlazeCommerce_Asset_Loader {
    
    private $version;
    private $is_development;
    private $asset_path;
    
    public function __construct() {
        $this->version = '1.0.0';
        $this->is_development = defined('WP_DEBUG') && WP_DEBUG;
        $this->asset_path = get_stylesheet_directory_uri() . '/assets/dist/';
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'), 10);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'), 10);
        add_action('customize_preview_init', array($this, 'enqueue_customizer_assets'), 10);
        add_action('wp_head', array($this, 'inline_critical_css'), 1);
    }
    
    /**
     * Inline critical CSS for immediate rendering
     */
    public function inline_critical_css() {
        $critical_css_file = get_stylesheet_directory() . '/assets/dist/css/critical.min.css';
        
        if (file_exists($critical_css_file)) {
            $critical_css = file_get_contents($critical_css_file);
            if ($critical_css) {
                echo '<style id="blaze-critical-css">' . $critical_css . '</style>';
            }
        }
    }
    
    /**
     * Enqueue frontend assets based on context
     */
    public function enqueue_frontend_assets() {
        // Critical JavaScript (always loaded)
        wp_enqueue_script(
            'blaze-critical-js',
            $this->asset_path . 'js/critical.min.js',
            array('jquery'),
            $this->version,
            false // Load in head for critical functionality
        );
        
        // Global CSS and JS (always loaded)
        wp_enqueue_style(
            'blaze-global-css',
            $this->asset_path . 'css/global.min.css',
            array(),
            $this->version,
            'all'
        );
        
        wp_enqueue_script(
            'blaze-global-js',
            $this->asset_path . 'js/global.min.js',
            array('jquery', 'blaze-critical-js'),
            $this->version,
            true
        );
        
        // WooCommerce specific assets
        if (class_exists('WooCommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page())) {
            wp_enqueue_style(
                'blaze-woocommerce-css',
                $this->asset_path . 'css/woocommerce.min.css',
                array('blaze-global-css'),
                $this->version,
                'all'
            );
            
            wp_enqueue_script(
                'blaze-woocommerce-js',
                $this->asset_path . 'js/woocommerce.min.js',
                array('jquery', 'blaze-global-js'),
                $this->version,
                true
            );
        }
        
        // Features CSS and JS (loaded on specific pages or conditions)
        if ($this->should_load_features()) {
            wp_enqueue_style(
                'blaze-features-css',
                $this->asset_path . 'css/features.min.css',
                array('blaze-global-css'),
                $this->version,
                'all'
            );
            
            wp_enqueue_script(
                'blaze-features-js',
                $this->asset_path . 'js/features.min.js',
                array('jquery', 'blaze-global-js'),
                $this->version,
                true
            );
        }
        
        // Localize scripts with necessary data
        wp_localize_script('blaze-global-js', 'blazeCommerce', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('blaze_nonce'),
            'isWooCommerce' => class_exists('WooCommerce'),
            'isMobile' => wp_is_mobile(),
            'version' => $this->version
        ));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on specific admin pages
        $admin_pages = array(
            'post.php',
            'post-new.php',
            'edit.php',
            'admin.php'
        );
        
        if (in_array($hook, $admin_pages) || strpos($hook, 'blaze') !== false) {
            wp_enqueue_style(
                'blaze-admin-css',
                $this->asset_path . 'css/admin.min.css',
                array(),
                $this->version,
                'all'
            );
            
            wp_enqueue_script(
                'blaze-admin-js',
                $this->asset_path . 'js/admin.min.js',
                array('jquery'),
                $this->version,
                true
            );
        }
    }
    
    /**
     * Enqueue customizer assets
     */
    public function enqueue_customizer_assets() {
        wp_enqueue_script(
            'blaze-customizer-js',
            $this->asset_path . 'js/customizer.min.js',
            array('jquery', 'customize-preview'),
            $this->version,
            true
        );
    }
    
    /**
     * Determine if features assets should be loaded
     */
    private function should_load_features() {
        // Load features on pages that use advanced functionality
        return (
            is_front_page() ||
            is_shop() ||
            is_product() ||
            is_product_category() ||
            is_product_tag() ||
            has_shortcode(get_post()->post_content ?? '', 'product_carousel') ||
            has_shortcode(get_post()->post_content ?? '', 'wishlist') ||
            has_shortcode(get_post()->post_content ?? '', 'mix_and_match')
        );
    }
    
    /**
     * Get file suffix based on environment
     */
    private function get_file_suffix() {
        return $this->is_development ? '' : '.min';
    }
}

// Initialize the asset loader
new BlazeCommerce_Asset_Loader();

/**
 * Development mode helpers
 */
if (defined('WP_DEBUG') && WP_DEBUG) {
    
    /**
     * Add development mode indicator
     */
    add_action('wp_footer', function() {
        echo '<!-- BlazeCommerce: Development Mode Active -->';
    });
    
    /**
     * Disable asset caching in development
     */
    add_filter('style_loader_src', function($src) {
        if (strpos($src, 'blaze-') !== false) {
            return add_query_arg('t', time(), $src);
        }
        return $src;
    });
    
    add_filter('script_loader_src', function($src) {
        if (strpos($src, 'blaze-') !== false) {
            return add_query_arg('t', time(), $src);
        }
        return $src;
    });
}
