/**
 * Product Collection Block Extension - Frontend Script
 *
 * Handles responsive column and product count adjustments on the frontend
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Responsive Product Collection Handler
     */
    class ResponsiveProductCollection {
        constructor() {
            this.breakpoints = {
                mobile: 768,
                tablet: 1024
            };
            this.init();
        }

        /**
         * Initialize the handler
         */
        init() {
            this.setupResponsiveCollections();
            this.bindEvents();
        }

        /**
         * Setup all responsive collections on the page
         */
        setupResponsiveCollections() {
            $('.wc-responsive-collection').each((index, element) => {
                this.setupCollection($(element));
            });
        }

        /**
         * Setup a single collection
         *
         * @param {jQuery} $collection Collection element
         */
        setupCollection($collection) {
            const responsiveColumns = $collection.data('responsive-columns');
            const responsiveCounts = $collection.data('responsive-counts');

            if (!responsiveColumns || !responsiveCounts) {
                return;
            }

            this.applyResponsiveLayout($collection, responsiveColumns, responsiveCounts);
        }

        /**
         * Apply responsive layout to collection
         *
         * @param {jQuery} $collection Collection element
         * @param {Object} columns Column settings
         * @param {Object} counts Product count settings
         */
        applyResponsiveLayout($collection, columns, counts) {
            const currentBreakpoint = this.getCurrentBreakpoint();
            const targetColumns = columns[currentBreakpoint] || columns.desktop;
            const targetCount = counts[currentBreakpoint] || counts.desktop;

            // Apply column classes
            this.applyColumnClasses($collection, targetColumns);

            // Show/hide products based on count
            this.applyProductCount($collection, targetCount);

            // Update CSS custom properties for responsive grid
            $collection[0].style.setProperty('--wc-responsive-columns', targetColumns);
        }

        /**
         * Apply column classes to collection
         *
         * @param {jQuery} $collection Collection element
         * @param {number} columns Number of columns
         */
        applyColumnClasses($collection, columns) {
            // Remove existing column classes
            $collection.removeClass((index, className) => {
                return (className.match(/(^|\s)columns-\S+/g) || []).join(' ');
            });

            // Add new column class
            $collection.addClass(`columns-${columns}`);
        }

        /**
         * Apply product count visibility
         *
         * @param {jQuery} $collection Collection element
         * @param {number} count Number of products to show
         */
        applyProductCount($collection, count) {
            // Find product items - try multiple selectors for compatibility
            let $products = $collection.find('.wp-block-woocommerce-product-template .wp-block-post');
            
            if ($products.length === 0) {
                $products = $collection.find('.wc-block-grid__products .wc-block-grid__product');
            }

            if ($products.length === 0) {
                $products = $collection.find('.products .product');
            }

            // Show/hide products based on count
            $products.each((index, product) => {
                const $product = $(product);
                if (index < count) {
                    $product.show().removeClass('wc-hidden-responsive');
                } else {
                    $product.hide().addClass('wc-hidden-responsive');
                }
            });
        }

        /**
         * Get current breakpoint
         *
         * @return {string} Current breakpoint (mobile, tablet, or desktop)
         */
        getCurrentBreakpoint() {
            const width = window.innerWidth;

            if (width >= this.breakpoints.tablet) {
                return 'desktop';
            } else if (width >= this.breakpoints.mobile) {
                return 'tablet';
            } else {
                return 'mobile';
            }
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            let resizeTimer;
            
            $(window).on('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    this.setupResponsiveCollections();
                }, 250);
            });

            // Re-initialize when products are loaded via AJAX
            $(document.body).on('wc-blocks_render_blocks_frontend', () => {
                this.setupResponsiveCollections();
            });
        }
    }

    /**
     * Initialize when DOM is ready
     */
    $(document).ready(() => {
        new ResponsiveProductCollection();
    });

    /**
     * Also initialize after WooCommerce blocks are loaded
     */
    $(document.body).on('wc-blocks_render_blocks_frontend', () => {
        new ResponsiveProductCollection();
    });

})(jQuery);

