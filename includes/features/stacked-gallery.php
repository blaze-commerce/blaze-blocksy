<?php
/**
 * Stacked Gallery Feature
 * 
 * Modifies WooCommerce product gallery to display stacked images on desktop
 * with thumbnails on the left, while maintaining slider behavior on mobile.
 *
 * @package Blocksy_Child
 * @category Features
 * @author Blaze Commerce
 * @license GPLv2 or later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class StackedGalleryFeature
 * 
 * Handles the stacked gallery functionality for WooCommerce product pages.
 * 
 * Desktop (≥1024px): All images stacked vertically with thumbnails on left
 * Mobile (<1024px): Keep existing flexy slider behavior
 */
class StackedGalleryFeature {

    /**
     * Constructor
     * 
     * Initializes the stacked gallery feature by setting up hooks and filters.
     */
    public function __construct() {
        // Enqueue assets (CSS and JS)
        add_action('wp_enqueue_scripts', array($this, 'enqueueAssets'), 5);
        
        // Add custom body class for stacked gallery
        add_filter('body_class', array($this, 'addBodyClass'));
        
        // Modify flexy gallery arguments
        add_filter('blocksy:woocommerce:single_product:flexy-args', array($this, 'modifyFlexyArgs'), 20);
    }

    /**
     * Enqueue CSS and JavaScript assets
     * 
     * Loads gallery-stacked.css and gallery-stacked.js with proper dependencies.
     * JavaScript is loaded in header with priority 5 to run before parent theme.
     */
    public function enqueueAssets() {
        // Only load on single product pages
        if (!is_product()) {
            return;
        }

        // Get theme version for cache busting
        $theme_version = wp_get_theme()->get('Version');

        // Enqueue gallery stacked CSS
        wp_enqueue_style(
            'blocksy-child-gallery-stacked',
            BLAZE_BLOCKSY_URL . '/assets/css/gallery-stacked.css',
            array('blocksy-styles'), // Dependency on parent theme styles
            $theme_version
        );

        // Enqueue gallery stacked JS
        wp_enqueue_script(
            'blocksy-child-gallery-stacked',
            BLAZE_BLOCKSY_URL . '/assets/js/gallery-stacked.js',
            array(), // No dependencies - run as early as possible
            $theme_version,
            false // Load in header, not footer
        );
    }

    /**
     * Add custom body class for stacked gallery
     * 
     * Adds 'ct-has-stacked-gallery' class to body on product pages.
     * This class is used as a CSS selector for styling.
     * 
     * @param array $classes Existing body classes
     * @return array Modified body classes
     */
    public function addBodyClass($classes) {
        if (is_product()) {
            $classes[] = 'ct-has-stacked-gallery';
        }
        return $classes;
    }

    /**
     * Modify flexy gallery arguments
     * 
     * Adds custom class 'ct-stacked-desktop' to flexy container.
     * This class is used to identify and modify the gallery behavior.
     * 
     * @param array $args Flexy gallery arguments
     * @return array Modified arguments
     */
    public function modifyFlexyArgs($args) {
        // Add custom class to identify stacked gallery
        if (isset($args['class'])) {
            $args['class'] .= ' ct-stacked-desktop';
        } else {
            $args['class'] = 'ct-stacked-desktop';
        }
        
        return $args;
    }
}

// Initialize the feature
new StackedGalleryFeature();

