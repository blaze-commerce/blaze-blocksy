# Implementation Example - Complete Code

This file contains complete, ready-to-use code for the gallery stacked modification.

---

## File 1: style.css

**Location**: `blocksy-child/style.css`

```css
/*
Theme Name: Blocksy Child - Gallery Stacked
Theme URI: https://example.com
Description: Child theme for Blocksy with stacked product gallery on desktop
Author: Your Name
Author URI: https://example.com
Template: blocksy
Version: 1.0.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: blocksy-child
*/

/* 
 * Custom styles can be added here or in separate CSS files
 * Gallery-specific styles are in assets/css/gallery-stacked.css
 */
```

---

## File 2: functions.php

**Location**: `blocksy-child/functions.php`

```php
<?php
/**
 * Blocksy Child Theme Functions
 * 
 * @package Blocksy Child
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue child theme styles and scripts
 */
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_assets', 5);
function blocksy_child_enqueue_assets() {
    // Get child theme version
    $theme_version = wp_get_theme()->get('Version');
    
    // Enqueue child theme main stylesheet
    wp_enqueue_style(
        'blocksy-child-style',
        get_stylesheet_uri(),
        array('blocksy-styles'),
        $theme_version
    );
    
    // Enqueue gallery stacked CSS
    wp_enqueue_style(
        'blocksy-child-gallery-stacked',
        get_stylesheet_directory_uri() . '/assets/css/gallery-stacked.css',
        array('blocksy-styles'),
        $theme_version
    );
    
    // Enqueue gallery stacked JS (only on product pages)
    if (is_product()) {
        wp_enqueue_script(
            'blocksy-child-gallery-stacked',
            get_stylesheet_directory_uri() . '/assets/js/gallery-stacked.js',
            array(), // No dependencies - run as early as possible
            $theme_version,
            false // Load in header, not footer
        );
    }
}

/**
 * Add custom body class for stacked gallery
 */
add_filter('body_class', 'blocksy_child_add_stacked_gallery_class');
function blocksy_child_add_stacked_gallery_class($classes) {
    if (is_product()) {
        $classes[] = 'ct-has-stacked-gallery';
    }
    return $classes;
}

/**
 * Modify flexy gallery arguments
 */
add_filter('blocksy:woocommerce:single_product:flexy-args', 'blocksy_child_modify_flexy_args', 20);
function blocksy_child_modify_flexy_args($args) {
    // Add custom class to identify our stacked gallery
    if (isset($args['class'])) {
        $args['class'] .= ' ct-stacked-desktop';
    } else {
        $args['class'] = 'ct-stacked-desktop';
    }
    
    return $args;
}

/**
 * Optional: Add theme support or custom features here
 */
add_action('after_setup_theme', 'blocksy_child_setup');
function blocksy_child_setup() {
    // Add custom theme support if needed
    // Example: add_theme_support('custom-feature');
}
```

---

## File 3: assets/css/gallery-stacked.css

**Location**: `blocksy-child/assets/css/gallery-stacked.css`

```css
/**
 * Gallery Stacked Styles
 * 
 * Desktop: All images stacked vertically with thumbnails on left
 * Mobile: Keep parent theme flexy slider behavior
 */

/* ============================================
   DESKTOP STYLES (≥1024px)
   ============================================ */

@media (min-width: 1024px) {
    
    /* ----------------------------------------
       Main Images Container - Stacked Layout
       ---------------------------------------- */
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items {
        display: flex !important;
        flex-direction: column !important;
        gap: 18px !important;
        transform: none !important;
        will-change: auto !important;
        flex-wrap: nowrap !important;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items > * {
        flex: 0 0 auto !important;
        width: 100% !important;
        max-width: 100% !important;
        transform: none !important;
        height: auto !important;
        opacity: 1 !important;
        order: initial !important;
    }
    
    /* Remove height restriction on hidden items */
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items > *:not(.flexy-item-is-visible) {
        height: auto !important;
    }
    
    /* ----------------------------------------
       Flexy View Container
       ---------------------------------------- */
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-view {
        height: auto !important;
        overflow: visible !important;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-view[data-flexy-view='boxed'] {
        overflow: visible !important;
    }
    
    /* ----------------------------------------
       Thumbnails - Left Side Vertical
       ---------------------------------------- */
    
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy-pills {
        position: absolute;
        left: 0;
        top: 0;
        height: auto;
        --thumbs-width: 120px;
        --pills-direction: column;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy-pills ol {
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        overflow: visible !important;
        height: auto !important;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy-pills [data-flexy] {
        overflow: visible !important;
        height: auto !important;
        max-height: none !important;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy-pills li {
        width: 120px;
        flex: 0 0 120px;
        cursor: pointer;
    }
    
    /* ----------------------------------------
       Main Images - Offset for Thumbnails
       ---------------------------------------- */
    
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy {
        margin-left: calc(120px + var(--thumbs-spacing, 15px));
    }
    
    /* ----------------------------------------
       Hide Slider Arrows
       ---------------------------------------- */
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy .flexy-arrow-prev,
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy .flexy-arrow-next {
        display: none !important;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-pills .flexy-arrow-prev,
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-pills .flexy-arrow-next {
        display: none !important;
    }
    
    /* ----------------------------------------
       Thumbnail Active State
       ---------------------------------------- */
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-pills li.active .ct-media-container:after {
        border-color: rgba(0, 0, 0, 0.2);
    }
    
    /* ----------------------------------------
       Accessibility - Focus States
       ---------------------------------------- */
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-pills li:focus {
        outline: 2px solid var(--theme-palette-color-1, #3366ff);
        outline-offset: 2px;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-pills li:focus-visible {
        outline: 2px solid var(--theme-palette-color-1, #3366ff);
        outline-offset: 2px;
    }
    
    /* ----------------------------------------
       Ensure Images Display Properly
       ---------------------------------------- */
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-item {
        display: block !important;
        min-height: auto !important;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .ct-media-container {
        display: flex;
        width: 100%;
    }
    
}

/* ============================================
   MOBILE STYLES (<1024px)
   ============================================ */

@media (max-width: 1023px) {
    /* 
     * No custom styles needed for mobile
     * Parent theme flexy slider behavior is maintained
     */
}

/* ============================================
   DEBUG STYLES (Uncomment for debugging)
   ============================================ */

/*
@media (min-width: 1024px) {
    .ct-has-stacked-gallery .flexy-items > * {
        outline: 2px solid red !important;
    }
    
    .ct-has-stacked-gallery .flexy-pills li {
        outline: 2px solid blue !important;
    }
    
    .ct-has-stacked-gallery .flexy-pills li.active {
        outline: 2px solid green !important;
    }
}
*/
```

---

## File 4: assets/js/gallery-stacked.js

**Location**: `blocksy-child/assets/js/gallery-stacked.js`

```javascript
/**
 * Gallery Stacked JavaScript
 * 
 * Handles:
 * - Preventing Flexy initialization on desktop
 * - Thumbnail click to scroll to image
 * - Responsive behavior
 */

(function($) {
    'use strict';
    
    // Configuration
    const CONFIG = {
        breakpoint: 1024,
        scrollOffset: 100,
        scrollBehavior: 'smooth',
        debug: false // Set to true for console logs
    };
    
    /**
     * Check if current viewport is desktop
     */
    function isDesktop() {
        return window.innerWidth >= CONFIG.breakpoint;
    }
    
    /**
     * Debug logger
     */
    function debugLog(message, data) {
        if (CONFIG.debug && window.console) {
            console.log('[Gallery Stacked]', message, data || '');
        }
    }
    
    /**
     * Prevent Flexy slider initialization on desktop
     */
    function preventFlexyInitialization() {
        if (!isDesktop()) {
            debugLog('Mobile mode - Flexy allowed');
            return;
        }
        
        const gallery = document.querySelector('.woocommerce-product-gallery .flexy-container.ct-stacked-desktop');
        
        if (!gallery) {
            debugLog('Gallery not found');
            return;
        }
        
        // Remove data-flexy attribute to prevent initialization
        if (gallery.hasAttribute('data-flexy')) {
            gallery.removeAttribute('data-flexy');
            debugLog('Flexy initialization prevented');
        }
        
        // Destroy existing Flexy instance if it exists
        if (gallery.flexy) {
            if (typeof gallery.flexy.destroy === 'function') {
                gallery.flexy.destroy();
            }
            gallery.flexy = null;
            debugLog('Flexy instance destroyed');
        }
    }
    
    /**
     * Calculate scroll offset accounting for fixed elements
     */
    function calculateScrollOffset() {
        let offset = CONFIG.scrollOffset;
        
        // Account for WordPress admin bar
        const adminBar = document.getElementById('wpadminbar');
        if (adminBar) {
            offset += adminBar.offsetHeight;
        }
        
        // Account for sticky header (if exists)
        const header = document.querySelector('.site-header');
        if (header && (header.classList.contains('sticky') || header.classList.contains('fixed'))) {
            offset += header.offsetHeight;
        }
        
        debugLog('Scroll offset calculated', offset);
        return offset;
    }
    
    /**
     * Smooth scroll to element
     */
    function scrollToElement(element, offset) {
        const elementTop = element.getBoundingClientRect().top + window.pageYOffset;
        const scrollTop = elementTop - offset;
        
        debugLog('Scrolling to', { elementTop, offset, scrollTop });
        
        // Check if smooth scroll is supported
        if ('scrollBehavior' in document.documentElement.style) {
            window.scrollTo({
                top: scrollTop,
                behavior: CONFIG.scrollBehavior
            });
        } else {
            // Fallback for older browsers
            window.scrollTo(0, scrollTop);
        }
    }
    
    /**
     * Update active thumbnail
     */
    function setActiveThumbnail(index) {
        const pills = document.querySelectorAll('.woocommerce-product-gallery .flexy-pills li');
        
        pills.forEach((pill, i) => {
            if (i === index) {
                pill.classList.add('active');
            } else {
                pill.classList.remove('active');
            }
        });
        
        debugLog('Active thumbnail set', index);
    }
    
    /**
     * Initialize thumbnail click handlers
     */
    function initializeThumbnailScroll() {
        if (!isDesktop()) {
            debugLog('Mobile mode - thumbnail scroll disabled');
            return;
        }
        
        const pills = document.querySelectorAll('.woocommerce-product-gallery .flexy-pills li');
        const images = document.querySelectorAll('.woocommerce-product-gallery .flexy-items > *');
        
        if (pills.length === 0 || images.length === 0) {
            debugLog('Pills or images not found', { pills: pills.length, images: images.length });
            return;
        }
        
        debugLog('Initializing thumbnail scroll', { pills: pills.length, images: images.length });
        
        pills.forEach((pill, index) => {
            // Remove existing listeners to prevent duplicates
            const newPill = pill.cloneNode(true);
            pill.parentNode.replaceChild(newPill, pill);
            
            newPill.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                debugLog('Thumbnail clicked', index);
                
                // Update active state
                setActiveThumbnail(index);
                
                // Scroll to corresponding image
                if (images[index]) {
                    const offset = calculateScrollOffset();
                    scrollToElement(images[index], offset);
                }
            });
        });
        
        debugLog('Thumbnail scroll initialized');
    }
    
    /**
     * Initialize gallery stacked functionality
     */
    function initialize() {
        debugLog('Initializing gallery stacked');
        preventFlexyInitialization();
        initializeThumbnailScroll();
    }
    
    /**
     * Handle window resize (debounced)
     */
    let resizeTimer;
    function handleResize() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            debugLog('Window resized', window.innerWidth);
            initialize();
        }, 250);
    }
    
    /**
     * Handle variation change (for variable products)
     */
    function handleVariationChange() {
        debugLog('Variation changed');
        setTimeout(function() {
            initialize();
        }, 100);
    }
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
    
    // Handle window resize
    window.addEventListener('resize', handleResize);
    
    // Handle WooCommerce variation change
    $(document).on('found_variation', handleVariationChange);
    $(document).on('reset_data', handleVariationChange);
    
    debugLog('Gallery stacked script loaded');
    
})(jQuery);
```

---

## Installation Instructions

1. **Create child theme folder**:
   ```bash
   cd wp-content/themes
   mkdir blocksy-child
   ```

2. **Create file structure**:
   ```bash
   cd blocksy-child
   mkdir -p assets/css assets/js
   ```

3. **Copy files**:
   - Copy `style.css` content to `blocksy-child/style.css`
   - Copy `functions.php` content to `blocksy-child/functions.php`
   - Copy CSS content to `blocksy-child/assets/css/gallery-stacked.css`
   - Copy JS content to `blocksy-child/assets/js/gallery-stacked.js`

4. **Activate child theme**:
   - Go to WordPress Admin → Appearance → Themes
   - Activate "Blocksy Child - Gallery Stacked"

5. **Test**:
   - Visit a product page with multiple images
   - Check desktop (≥1024px): Images should be stacked
   - Check mobile (<1024px): Slider should work normally

---

## Customization Options

### Change Breakpoint
In `gallery-stacked.js`, modify:
```javascript
const CONFIG = {
    breakpoint: 1200, // Change from 1024 to 1200
    // ...
};
```

And in `gallery-stacked.css`, change all:
```css
@media (min-width: 1024px) { /* Change to 1200px */ }
```

### Change Scroll Offset
In `gallery-stacked.js`, modify:
```javascript
const CONFIG = {
    scrollOffset: 150, // Change from 100 to 150
    // ...
};
```

### Change Image Gap
In `gallery-stacked.css`, modify:
```css
.ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items {
    gap: 24px !important; /* Change from 18px to 24px */
}
```

### Change Thumbnail Width
In `gallery-stacked.css`, modify:
```css
.ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy-pills {
    --thumbs-width: 150px; /* Change from 120px to 150px */
}

.ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy-pills li {
    width: 150px; /* Change from 120px to 150px */
    flex: 0 0 150px;
}

.ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy {
    margin-left: calc(150px + var(--thumbs-spacing, 15px)); /* Change 120px to 150px */
}
```

### Enable Debug Mode
In `gallery-stacked.js`, modify:
```javascript
const CONFIG = {
    debug: true, // Change from false to true
    // ...
};
```

---

## Troubleshooting

### Issue: Flexy still initializes on desktop
**Solution**: Clear browser cache and ensure JS loads in header

### Issue: Images not stacked
**Solution**: Check if `.ct-has-stacked-gallery` class is on body element

### Issue: Scroll doesn't work
**Solution**: Check browser console for errors, enable debug mode

### Issue: Mobile slider broken
**Solution**: Ensure all custom CSS is wrapped in `@media (min-width: 1024px)`

---

**Implementation Example Version**: 1.0  
**Ready to Use**: Yes  
**Tested**: WordPress 6.0+, WooCommerce 7.0+, Blocksy Theme

