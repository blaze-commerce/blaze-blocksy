/**
 * WooCommerce Product Card Border - Customizer Live Preview
 * 
 * Handles real-time preview updates for product card border settings
 * in the WordPress customizer without requiring page reload.
 * 
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Ensure we're in the customizer preview
    if (typeof wp === 'undefined' || typeof wp.customize === 'undefined') {
        return;
    }

    /**
     * Product Card Border Live Preview Handler
     */
    var ProductCardBorderPreview = {
        
        /**
         * Initialize the preview functionality
         */
        init: function() {
            this.bindEvents();
            this.createStyleElement();
        },
        
        /**
         * Bind customizer events
         */
        bindEvents: function() {
            var self = this;
            
            // Listen for Blocksy border control changes
            wp.customize('woo_card_border', function(value) {
                value.bind(function(newValue) {
                    self.updateBorderStyles(newValue);
                });
            });
            
            // Fallback listeners for individual controls
            wp.customize('woo_card_border_width', function(value) {
                value.bind(function(newValue) {
                    self.updateFallbackStyles();
                });
            });
            
            wp.customize('woo_card_border_style', function(value) {
                value.bind(function(newValue) {
                    self.updateFallbackStyles();
                });
            });
            
            wp.customize('woo_card_border_color', function(value) {
                value.bind(function(newValue) {
                    self.updateFallbackStyles();
                });
            });
        },
        
        /**
         * Create or get the style element for dynamic CSS
         */
        createStyleElement: function() {
            var styleId = 'woo-card-border-preview-styles';
            this.$styleElement = $('#' + styleId);
            
            if (this.$styleElement.length === 0) {
                this.$styleElement = $('<style id="' + styleId + '"></style>');
                $('head').append(this.$styleElement);
            }
        },
        
        /**
         * Update border styles from Blocksy border control
         * 
         * @param {Object} borderSettings - Border settings object
         */
        updateBorderStyles: function(borderSettings) {
            if (!borderSettings || typeof borderSettings !== 'object') {
                return;
            }
            
            // Extract border properties
            var width = parseInt(borderSettings.width) || 1;
            var style = borderSettings.style || 'none';
            var color = 'rgba(0, 0, 0, 0.1)';
            
            if (borderSettings.color && borderSettings.color.color) {
                color = borderSettings.color.color;
            }
            
            // Generate and apply CSS
            var css = this.generateBorderCSS(width, style, color);
            this.applyCSSToPreview(css);
        },
        
        /**
         * Update styles from fallback individual controls
         */
        updateFallbackStyles: function() {
            var width = wp.customize('woo_card_border_width')() || 1;
            var style = wp.customize('woo_card_border_style')() || 'none';
            var color = wp.customize('woo_card_border_color')() || 'rgba(0, 0, 0, 0.1)';
            
            var css = this.generateBorderCSS(width, style, color);
            this.applyCSSToPreview(css);
        },
        
        /**
         * Generate CSS for border styles
         * 
         * @param {number} width - Border width in pixels
         * @param {string} style - Border style (solid, dashed, etc.)
         * @param {string} color - Border color
         * @return {string} Generated CSS
         */
        generateBorderCSS: function(width, style, color) {
            // Don't generate CSS if style is 'none'
            if (style === 'none') {
                return '[data-products] .product { border: none !important; }';
            }
            
            var borderValue = width + 'px ' + style + ' ' + color;
            
            return '[data-products] .product { border: ' + borderValue + ' !important; }';
        },
        
        /**
         * Apply CSS to the preview
         * 
         * @param {string} css - CSS to apply
         */
        applyCSSToPreview: function(css) {
            if (this.$styleElement) {
                this.$styleElement.html(css);
            }
        },
        
        /**
         * Remove preview styles (cleanup)
         */
        removePreviewStyles: function() {
            if (this.$styleElement) {
                this.$styleElement.remove();
            }
        }
    };

    /**
     * Responsive Border Preview Handler
     * 
     * Handles responsive border settings if Blocksy supports them
     */
    var ResponsiveBorderPreview = {
        
        /**
         * Initialize responsive preview
         */
        init: function() {
            this.bindResponsiveEvents();
        },
        
        /**
         * Bind responsive customizer events
         */
        bindResponsiveEvents: function() {
            var self = this;
            
            // Listen for responsive border changes
            wp.customize('woo_card_border', function(value) {
                value.bind(function(newValue) {
                    self.updateResponsiveBorders(newValue);
                });
            });
        },
        
        /**
         * Update responsive border styles
         * 
         * @param {Object} borderSettings - Responsive border settings
         */
        updateResponsiveBorders: function(borderSettings) {
            if (!borderSettings || typeof borderSettings !== 'object') {
                return;
            }
            
            var css = '';
            
            // Desktop styles
            if (borderSettings.desktop) {
                css += this.generateResponsiveCSS(borderSettings.desktop, '');
            }
            
            // Tablet styles
            if (borderSettings.tablet) {
                css += this.generateResponsiveCSS(borderSettings.tablet, '@media (max-width: 999px)');
            }
            
            // Mobile styles
            if (borderSettings.mobile) {
                css += this.generateResponsiveCSS(borderSettings.mobile, '@media (max-width: 767px)');
            }
            
            ProductCardBorderPreview.applyCSSToPreview(css);
        },
        
        /**
         * Generate responsive CSS
         * 
         * @param {Object} settings - Border settings for specific breakpoint
         * @param {string} mediaQuery - Media query wrapper
         * @return {string} Generated CSS
         */
        generateResponsiveCSS: function(settings, mediaQuery) {
            var width = parseInt(settings.width) || 1;
            var style = settings.style || 'none';
            var color = settings.color && settings.color.color ? settings.color.color : 'rgba(0, 0, 0, 0.1)';
            
            if (style === 'none') {
                return '';
            }
            
            var borderValue = width + 'px ' + style + ' ' + color;
            var css = '[data-products] .product { border: ' + borderValue + ' !important; }';
            
            if (mediaQuery) {
                css = mediaQuery + ' { ' + css + ' }';
            }
            
            return css;
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        ProductCardBorderPreview.init();
        ResponsiveBorderPreview.init();
    });

    /**
     * Cleanup on page unload
     */
    $(window).on('beforeunload', function() {
        ProductCardBorderPreview.removePreviewStyles();
    });

})(jQuery);
