/**
 * Wishlist Off-Canvas Variable Descriptors for Blocksy Sync System
 * 
 * This file registers the variable descriptors for wishlist off-canvas settings
 * to integrate properly with Blocksy's customizer sync system.
 */

// Check if Blocksy's customizer sync system is available
if (typeof ctEvents !== 'undefined') {
    
    /**
     * Register variable descriptors for wishlist off-canvas settings
     */
    ctEvents.on('ct:customizer:sync:collect-variable-descriptors', function(allVariables) {
        
        // Add wishlist off-canvas icon size variable descriptor
        allVariables.result = {
            ...allVariables.result,
            
            // Wishlist off-canvas icon size
            wishlist_offcanvas_icon_size: {
                selector: [
                    '.ct-header-wishlist .ct-icon',
                    '.ct-wishlist-button .ct-icon',
                    '[data-id="wish-list"] .ct-icon',
                    '.ct-header .ct-wishlist .ct-icon',
                    '.ct-offcanvas-wishlist-trigger .ct-icon'
                ].join(', '),
                variable: 'theme-icon-size',
                responsive: true,
                unit: 'px'
            },
            
            // Wishlist off-canvas width (already handled in PHP but adding for completeness)
            wishlist_offcanvas_width: {
                selector: '#ct-wishlist-offcanvas',
                variable: 'offcanvas-width',
                responsive: true,
                unit: ''
            }
        };
    });

    /**
     * Mount wishlist off-canvas sync handlers
     */
    function mountWishlistOffcanvasSync() {
        
        // Handle icon size changes
        if (wp.customize('wishlist_offcanvas_icon_size')) {
            wp.customize('wishlist_offcanvas_icon_size', function(value) {
                value.bind(function(newValue) {
                    updateWishlistOffcanvasIconSize(newValue);
                });
            });
        }
        
        // Handle icon source changes
        if (wp.customize('wishlist_offcanvas_icon_source')) {
            wp.customize('wishlist_offcanvas_icon_source', function(value) {
                value.bind(function(newValue) {
                    // When switching to custom, apply current icon size
                    if (newValue === 'custom') {
                        var currentSize = wp.customize('wishlist_offcanvas_icon_size')();
                        if (currentSize) {
                            updateWishlistOffcanvasIconSize(currentSize);
                        }
                    }
                });
            });
        }
        
        // Handle display mode changes
        if (wp.customize('wishlist_display_mode')) {
            wp.customize('wishlist_display_mode', function(value) {
                value.bind(function(newValue) {
                    if (newValue === 'offcanvas') {
                        // Apply current settings when switching to off-canvas mode
                        var iconSource = wp.customize('wishlist_offcanvas_icon_source')();
                        if (iconSource === 'custom') {
                            var currentSize = wp.customize('wishlist_offcanvas_icon_size')();
                            if (currentSize) {
                                updateWishlistOffcanvasIconSize(currentSize);
                            }
                        }
                    }
                });
            });
        }
    }

    /**
     * Update wishlist off-canvas icon size
     * @param {Object|string} sizeValue - The size value (responsive object or string)
     */
    function updateWishlistOffcanvasIconSize(sizeValue) {
        var selectors = [
            '.ct-header-wishlist .ct-icon',
            '.ct-wishlist-button .ct-icon',
            '[data-id="wish-list"] .ct-icon',
            '.ct-header .ct-wishlist .ct-icon',
            '.ct-offcanvas-wishlist-trigger .ct-icon'
        ];

        // Apply the size using Blocksy's variable system if available
        if (typeof updateCSSVariable !== 'undefined') {
            updateCSSVariable(selectors.join(', '), 'theme-icon-size', sizeValue);
        } else {
            // Fallback to direct CSS application
            applyIconSizeDirectly(selectors, sizeValue);
        }
    }

    /**
     * Apply icon size directly via CSS (fallback method)
     * @param {Array} selectors - CSS selectors
     * @param {Object|string} sizeValue - Size value
     */
    function applyIconSizeDirectly(selectors, sizeValue) {
        var css = '';
        
        if (typeof sizeValue === 'object' && sizeValue !== null) {
            // Responsive values
            if (sizeValue.desktop) {
                css += selectors.join(', ') + ' { font-size: ' + sizeValue.desktop + ' !important; }';
            }
            if (sizeValue.tablet) {
                css += '@media (max-width: 999px) { ' + selectors.join(', ') + ' { font-size: ' + sizeValue.tablet + ' !important; } }';
            }
            if (sizeValue.mobile) {
                css += '@media (max-width: 689px) { ' + selectors.join(', ') + ' { font-size: ' + sizeValue.mobile + ' !important; } }';
            }
        } else if (typeof sizeValue === 'string') {
            // Simple string value
            css = selectors.join(', ') + ' { font-size: ' + sizeValue + ' !important; }';
        }

        // Apply CSS
        if (css) {
            var styleId = 'wishlist-offcanvas-icon-size-sync';
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
    }

    // Initialize when customizer is ready
    if (typeof wp !== 'undefined' && wp.customize) {
        wp.customize.bind('ready', function() {
            mountWishlistOffcanvasSync();
        });
    }

} else {
    // Fallback if Blocksy sync system is not available
    console.warn('Blocksy customizer sync system not available. Wishlist off-canvas sync may not work properly.');
}
