/**
 * Gallery Stacked JavaScript
 * 
 * Handles:
 * - Preventing Flexy initialization on desktop
 * - Thumbnail click to scroll to image
 * - Responsive behavior
 * 
 * @package Blocksy_Child
 * @category Scripts
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
     * 
     * @return {boolean} True if desktop (≥1024px), false otherwise
     */
    function isDesktop() {
        return window.innerWidth >= CONFIG.breakpoint;
    }
    
    /**
     * Debug logger
     * 
     * @param {string} message Log message
     * @param {*} data Optional data to log
     */
    function debugLog(message, data) {
        if (CONFIG.debug && window.console) {
            console.log('[Gallery Stacked]', message, data || '');
        }
    }
    
    /**
     * Prevent Flexy slider initialization on desktop
     * 
     * Removes data-flexy attribute and destroys existing instance
     * to prevent slider behavior on desktop.
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
     * 
     * Accounts for WordPress admin bar and sticky header.
     * 
     * @return {number} Total offset in pixels
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
     * 
     * Scrolls to element with calculated offset and smooth behavior.
     * Falls back to instant scroll for older browsers.
     * 
     * @param {HTMLElement} element Element to scroll to
     * @param {number} offset Offset from top in pixels
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
     * 
     * Adds 'active' class to clicked thumbnail and removes from others.
     * 
     * @param {number} index Index of thumbnail to activate
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
     * 
     * Attaches click event listeners to thumbnails for smooth scrolling
     * to corresponding main images on desktop.
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
     * 
     * Main initialization function that sets up all features.
     */
    function initialize() {
        debugLog('Initializing gallery stacked');
        preventFlexyInitialization();
        initializeThumbnailScroll();
    }
    
    /**
     * Handle window resize (debounced)
     * 
     * Re-initializes gallery when window is resized.
     * Debounced to prevent excessive calls.
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
     * 
     * Re-initializes gallery when product variation changes.
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

