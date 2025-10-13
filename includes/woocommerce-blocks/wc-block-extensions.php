<?php
/**
 * WooCommerce Block Extensions
 *
 * Extends WooCommerce Gutenberg blocks with responsive features and enhancements
 * without modifying core WooCommerce files.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

// Check if WooCommerce is active
if (!function_exists('WC')) {
    return;
}

define('WC_BLOCK_EXTENSIONS_VERSION', '1.0.0');
define('WC_BLOCK_EXTENSIONS_FILE', __FILE__);
define('WC_BLOCK_EXTENSIONS_URL', BLAZE_BLOCKSY_URL . '/includes/woocommerce-blocks');
define('WC_BLOCK_EXTENSIONS_PATH', BLAZE_BLOCKSY_PATH . '/includes/woocommerce-blocks');

/**
 * Main WooCommerce Block Extensions Loader Class
 *
 * Initializes and manages all block extensions
 */
class WC_Block_Extensions_Loader {
    
    /**
     * Single instance of the class
     *
     * @var WC_Block_Extensions_Loader
     */
    private static $instance = null;

    /**
     * Get single instance
     *
     * @return WC_Block_Extensions_Loader
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    /**
     * Initialize block extensions
     */
    public function init() {
        // Load extension classes
        $this->load_extensions();
    }

    /**
     * Load extension classes
     */
    private function load_extensions() {
        // Product Collection Extension
        require_once WC_BLOCK_EXTENSIONS_PATH . '/includes/class-product-collection-extension.php';
        new WC_Product_Collection_Extension();

        // Product Image Extension
        require_once WC_BLOCK_EXTENSIONS_PATH . '/includes/class-product-image-extension.php';
        new WC_Product_Image_Extension();
    }
    
    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets() {
        // Product Collection Editor Script
        wp_enqueue_script(
            'wc-block-extensions-product-collection-editor',
            WC_BLOCK_EXTENSIONS_URL . '/assets/js/product-collection-extension.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-hooks', 'wp-compose'),
            WC_BLOCK_EXTENSIONS_VERSION,
            true
        );

        // Product Image Editor Script
        wp_enqueue_script(
            'wc-block-extensions-product-image-editor',
            WC_BLOCK_EXTENSIONS_URL . '/assets/js/product-image-extension.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-hooks', 'wp-compose'),
            WC_BLOCK_EXTENSIONS_VERSION,
            true
        );
        
        // Editor Styles
        wp_enqueue_style(
            'wc-block-extensions-editor',
            WC_BLOCK_EXTENSIONS_URL . '/assets/css/editor.css',
            array(),
            WC_BLOCK_EXTENSIONS_VERSION
        );
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Product Collection Frontend Script
        if (has_block('woocommerce/product-collection')) {
            wp_enqueue_script(
                'wc-block-extensions-product-collection-frontend',
                WC_BLOCK_EXTENSIONS_URL . '/assets/js/product-collection-frontend.js',
                array('jquery'),
                WC_BLOCK_EXTENSIONS_VERSION,
                true
            );
        }

        // Product Image Frontend Script
        if (has_block('woocommerce/product-image')) {
            wp_enqueue_script(
                'wc-block-extensions-product-image-frontend',
                WC_BLOCK_EXTENSIONS_URL . '/assets/js/product-image-frontend.js',
                array('jquery'),
                WC_BLOCK_EXTENSIONS_VERSION,
                true
            );

            // Localize script with AJAX data
            wp_localize_script('wc-block-extensions-product-image-frontend', 'wcBlockExtensions', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_block_extensions_nonce'),
                'messages' => array(
                    'added' => __('Added to wishlist', 'blocksy-child'),
                    'removed' => __('Removed from wishlist', 'blocksy-child'),
                    'error' => __('Error updating wishlist', 'blocksy-child')
                )
            ));
        }

        // Frontend Styles
        wp_enqueue_style(
            'wc-block-extensions-frontend',
            WC_BLOCK_EXTENSIONS_URL . '/assets/css/frontend.css',
            array(),
            WC_BLOCK_EXTENSIONS_VERSION
        );
    }
}

// Initialize the loader
add_action('plugins_loaded', function() {
    WC_Block_Extensions_Loader::instance();
}, 20);

