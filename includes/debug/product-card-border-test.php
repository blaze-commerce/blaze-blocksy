<?php
/**
 * Product Card Border Feature Test
 * 
 * This file provides testing utilities for the WooCommerce Product Card Border feature.
 * Only loads in debug mode for development testing.
 * 
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Only load in debug mode
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    return;
}

/**
 * Product Card Border Test Class
 */
class Product_Card_Border_Test {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize test functionality
     */
    public function init() {
        // Add admin menu for testing
        add_action('admin_menu', array($this, 'add_test_menu'));
        
        // Add AJAX handlers for testing
        add_action('wp_ajax_test_border_css', array($this, 'test_border_css_generation'));
        add_action('wp_ajax_test_border_settings', array($this, 'test_border_settings'));
    }
    
    /**
     * Add test menu to admin
     */
    public function add_test_menu() {
        add_submenu_page(
            'tools.php',
            'Product Card Border Test',
            'Border Test',
            'manage_options',
            'product-card-border-test',
            array($this, 'render_test_page')
        );
    }
    
    /**
     * Render test page
     */
    public function render_test_page() {
        ?>
        <div class="wrap">
            <h1>Product Card Border Feature Test</h1>
            
            <div class="card">
                <h2>Current Settings</h2>
                <?php $this->display_current_settings(); ?>
            </div>
            
            <div class="card">
                <h2>CSS Generation Test</h2>
                <?php $this->display_css_test(); ?>
            </div>
            
            <div class="card">
                <h2>Integration Status</h2>
                <?php $this->display_integration_status(); ?>
            </div>
            
            <div class="card">
                <h2>Quick Tests</h2>
                <?php $this->display_quick_tests(); ?>
            </div>
        </div>
        
        <style>
        .card { background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; }
        .test-result { padding: 10px; margin: 10px 0; border-left: 4px solid #00a0d2; background: #f7f7f7; }
        .test-success { border-left-color: #46b450; }
        .test-error { border-left-color: #dc3232; }
        .test-warning { border-left-color: #ffb900; }
        </style>
        <?php
    }
    
    /**
     * Display current border settings
     */
    private function display_current_settings() {
        // Try to get settings using the same method as the main class
        if (function_exists('blocksy_get_theme_mod')) {
            $settings = blocksy_get_theme_mod('woo_card_border', array(
                'width' => 1,
                'style' => 'none',
                'color' => array('color' => 'rgba(0, 0, 0, 0.1)'),
            ));
        } else {
            $settings = array(
                'width' => get_theme_mod('woo_card_border_width', 1),
                'style' => get_theme_mod('woo_card_border_style', 'none'),
                'color' => array('color' => get_theme_mod('woo_card_border_color', 'rgba(0, 0, 0, 0.1)')),
            );
        }
        
        echo '<div class="test-result">';
        echo '<strong>Border Width:</strong> ' . esc_html($settings['width']) . 'px<br>';
        echo '<strong>Border Style:</strong> ' . esc_html($settings['style']) . '<br>';
        echo '<strong>Border Color:</strong> ' . esc_html($settings['color']['color']) . '<br>';
        echo '</div>';
    }
    
    /**
     * Display CSS generation test
     */
    private function display_css_test() {
        $test_settings = array(
            'width' => 2,
            'style' => 'solid',
            'color' => array('color' => '#e0e0e0'),
        );
        
        // Simulate CSS generation
        $width = absint($test_settings['width']) . 'px';
        $style = sanitize_text_field($test_settings['style']);
        $color = sanitize_text_field($test_settings['color']['color']);
        $selector = '[data-products] .product';
        
        $css = "
        /* WooCommerce Product Card Dynamic Border */
        {$selector} {
            --woo-card-border-width: {$width};
            --woo-card-border-style: {$style};
            --woo-card-border-color: {$color};
            border: {$width} {$style} {$color} !important;
        }";
        
        echo '<div class="test-result test-success">';
        echo '<strong>Test CSS Generation:</strong><br>';
        echo '<pre>' . esc_html($css) . '</pre>';
        echo '</div>';
    }
    
    /**
     * Display integration status
     */
    private function display_integration_status() {
        $checks = array();
        
        // Check if main class exists
        $checks['Main Class'] = class_exists('WooCommerce_Product_Card_Border') ? 'success' : 'error';
        
        // Check if WooCommerce is active
        $checks['WooCommerce'] = class_exists('WooCommerce') ? 'success' : 'error';
        
        // Check if Blocksy functions are available
        $checks['Blocksy Integration'] = function_exists('blocksy_get_theme_mod') ? 'success' : 'warning';
        
        // Check if JavaScript file exists
        $js_file = BLAZE_BLOCKSY_PATH . '/assets/js/customizer-preview.js';
        $checks['JavaScript File'] = file_exists($js_file) ? 'success' : 'error';
        
        // Check if CSS file exists
        $css_file = BLAZE_BLOCKSY_PATH . '/assets/css/archive.css';
        $checks['CSS File'] = file_exists($css_file) ? 'success' : 'error';
        
        foreach ($checks as $check => $status) {
            $class = 'test-' . $status;
            $icon = $status === 'success' ? '✓' : ($status === 'warning' ? '⚠' : '✗');
            echo '<div class="test-result ' . $class . '">';
            echo '<strong>' . $icon . ' ' . esc_html($check) . ':</strong> ';
            echo ucfirst($status);
            echo '</div>';
        }
    }
    
    /**
     * Display quick tests
     */
    private function display_quick_tests() {
        ?>
        <p>Use these links to quickly test the feature:</p>
        <ul>
            <li><a href="<?php echo admin_url('customize.php'); ?>" target="_blank">Open Customizer</a></li>
            <li><a href="<?php echo wc_get_page_permalink('shop'); ?>" target="_blank">View Shop Page</a></li>
            <li><a href="<?php echo admin_url('customize.php?url=' . urlencode(wc_get_page_permalink('shop'))); ?>" target="_blank">Customize Shop Page</a></li>
        </ul>
        
        <h4>Test Scenarios:</h4>
        <ol>
            <li>Set border width to 3px, style to solid, color to red</li>
            <li>Verify live preview updates immediately</li>
            <li>Check that borders appear on all product cards</li>
            <li>Test responsive behavior on mobile/tablet</li>
            <li>Verify hover effects work properly</li>
        </ol>
        <?php
    }
    
    /**
     * Test border CSS generation via AJAX
     */
    public function test_border_css_generation() {
        check_ajax_referer('border_test_nonce', 'nonce');
        
        $settings = array(
            'width' => intval($_POST['width']),
            'style' => sanitize_text_field($_POST['style']),
            'color' => array('color' => sanitize_text_field($_POST['color'])),
        );
        
        // Generate CSS using the same method as main class
        $width = absint($settings['width']) . 'px';
        $style = sanitize_text_field($settings['style']);
        $color = sanitize_text_field($settings['color']['color']);
        $selector = '[data-products] .product';
        
        $css = "{$selector} { border: {$width} {$style} {$color} !important; }";
        
        wp_send_json_success(array('css' => $css));
    }
    
    /**
     * Test border settings via AJAX
     */
    public function test_border_settings() {
        check_ajax_referer('border_test_nonce', 'nonce');
        
        if (function_exists('blocksy_get_theme_mod')) {
            $settings = blocksy_get_theme_mod('woo_card_border');
        } else {
            $settings = array(
                'width' => get_theme_mod('woo_card_border_width', 1),
                'style' => get_theme_mod('woo_card_border_style', 'none'),
                'color' => array('color' => get_theme_mod('woo_card_border_color', 'rgba(0, 0, 0, 0.1)')),
            );
        }
        
        wp_send_json_success($settings);
    }
}

// Initialize test class only in debug mode
if (defined('WP_DEBUG') && WP_DEBUG) {
    new Product_Card_Border_Test();
}
