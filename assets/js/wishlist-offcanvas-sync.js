/**
 * Wishlist Off-Canvas Customizer Sync Handler
 *
 * Simple sync handler for the wishlist icon size text input field.
 */

(function ($) {
    'use strict';

    // Ensure wp.customize is available
    if (typeof wp === 'undefined' || typeof wp.customize === 'undefined') {
        console.warn('WordPress customizer API not available');
        return;
    }

    // Wait for WordPress customizer to be ready
    wp.customize.bind('ready', function () {
        // Handle wishlist off-canvas icon size changes
        wp.customize('wishlist_offcanvas_icon_size', function (value) {
            value.bind(function (newValue) {
                updateWishlistIconSize(newValue);
            });
        });

        // Handle wishlist off-canvas close icon size changes
        wp.customize('wishlist_offcanvas_close_icon_size', function (value) {
            value.bind(function (newValue) {
                updateWishlistCloseIconSize(newValue);
            });
        });
    });

    /**
     * Update wishlist icon size in the preview
     * @param {string} sizeValue - The icon size value
     */
    function updateWishlistIconSize(sizeValue) {
        // Specific selectors for off-canvas wishlist icons only
        var selectors = [
            '#wishlist-offcanvas-panel .ct-icon',
            '.ct-offcanvas-wishlist-trigger .ct-icon'
        ];

        // Ensure we have a valid size value
        if (!sizeValue || sizeValue === '') {
            return;
        }

        // Add 'px' if it's just a number
        var size = sizeValue;
        if (/^\d+$/.test(sizeValue)) {
            size = sizeValue + 'px';
        }

        // Create CSS rule
        var css = selectors.join(', ') + ' { font-size: ' + size + ' !important; }';

        // Apply the CSS
        var styleId = 'wishlist-icon-size-sync';
        var existingStyle = document.getElementById(styleId);

        if (existingStyle) {
            existingStyle.textContent = css;
        } else {
            var style = document.createElement('style');
            style.id = styleId;
            style.textContent = css;
            document.head.appendChild(style);
        }
    }

    /**
     * Update wishlist close icon size in the preview
     * @param {string} sizeValue - The close icon size value
     */
    function updateWishlistCloseIconSize(sizeValue) {
        // Specific selector for close icon
        var selector = '#wishlist-offcanvas-panel .ct-toggle-close .ct-icon';

        // Ensure we have a valid size value
        if (!sizeValue || sizeValue === '') {
            return;
        }

        // Add 'px' if it's just a number
        var size = sizeValue;
        if (/^\d+$/.test(sizeValue)) {
            size = sizeValue + 'px';
        }

        // Create CSS rule
        var css = selector + ' { font-size: ' + size + ' !important; width: ' + size + ' !important; height: ' + size + ' !important; }';

        // Apply the CSS
        var styleId = 'wishlist-close-icon-size-sync';
        var existingStyle = document.getElementById(styleId);

        if (existingStyle) {
            existingStyle.textContent = css;
        } else {
            var style = document.createElement('style');
            style.id = styleId;
            style.textContent = css;
            document.head.appendChild(style);
        }
    }

})(jQuery);
