/**
 * Product Image Block Extension - Frontend Script
 *
 * Handles hover image swap and wishlist functionality on the frontend
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Product Image Enhancements Handler
     */
    class ProductImageEnhancements {
        constructor() {
            this.init();
        }

        /**
         * Initialize the handler
         */
        init() {
            this.setupHoverImages();
            this.setupWishlistButtons();
        }

        /**
         * Setup hover image functionality
         */
        setupHoverImages() {
            $('.wc-hover-image-enabled').each((index, element) => {
                this.initHoverImage($(element));
            });
        }

        /**
         * Initialize hover image for a single element
         *
         * @param {jQuery} $container Image container element
         */
        initHoverImage($container) {
            const hoverImageData = $container.data('hover-image');
            if (!hoverImageData) {
                return;
            }

            const $image = $container.find('img').first();
            if (!$image.length) {
                return;
            }

            // Store original image data
            const originalSrc = $image.attr('src');
            const originalSrcset = $image.attr('srcset') || '';
            const originalAlt = $image.attr('alt') || '';

            // Preload hover image for smooth transition
            const preloadImage = new Image();
            preloadImage.src = hoverImageData.url;

            // Mouse enter - show hover image
            $container.on('mouseenter', () => {
                $image.attr('src', hoverImageData.url);
                if (hoverImageData.srcset) {
                    $image.attr('srcset', hoverImageData.srcset);
                }
                if (hoverImageData.alt) {
                    $image.attr('alt', hoverImageData.alt);
                }
                $image.addClass('wc-hover-active');
            });

            // Mouse leave - restore original image
            $container.on('mouseleave', () => {
                $image.attr('src', originalSrc);
                if (originalSrcset) {
                    $image.attr('srcset', originalSrcset);
                }
                $image.attr('alt', originalAlt);
                $image.removeClass('wc-hover-active');
            });
        }

        /**
         * Setup wishlist button functionality
         */
        setupWishlistButtons() {
            $('.wc-wishlist-enabled').each((index, element) => {
                this.initWishlistButton($(element));
            });
        }

        /**
         * Initialize wishlist button for a single element
         *
         * @param {jQuery} $container Image container element
         */
        initWishlistButton($container) {
            const $button = $container.find('.wc-wishlist-button');
            if (!$button.length) {
                return;
            }

            // Prevent multiple bindings
            if ($button.data('wc-initialized')) {
                return;
            }
            $button.data('wc-initialized', true);

            $button.on('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const productId = $button.data('product-id');
                this.toggleWishlist(productId, $button);
            });
        }

        /**
         * Toggle product in wishlist
         *
         * @param {number} productId Product ID
         * @param {jQuery} $button Wishlist button element
         */
        toggleWishlist(productId, $button) {
            const isInWishlist = $button.hasClass('wc-wishlist-added');

            // Add loading state
            $button.addClass('wc-wishlist-loading').prop('disabled', true);

            // Make AJAX request
            $.ajax({
                url: wcBlockExtensions.ajax_url,
                type: 'POST',
                data: {
                    action: 'wc_block_toggle_wishlist',
                    product_id: productId,
                    nonce: wcBlockExtensions.nonce
                },
                success: (response) => {
                    if (response.success) {
                        // Update button state
                        $button.toggleClass('wc-wishlist-added');
                        
                        // Update SVG fill
                        const $svg = $button.find('svg path');
                        if (response.data.in_wishlist) {
                            $svg.attr('fill', 'currentColor');
                        } else {
                            $svg.attr('fill', 'none');
                        }

                        // Show message
                        this.showWishlistMessage(response.data.message);

                        // Trigger Blocksy wishlist update event if available
                        if (typeof ctEvents !== 'undefined') {
                            ctEvents.trigger('blocksy:woocommerce:wish-list-update', {
                                productId: productId,
                                action: response.data.action
                            });
                        }

                        // Update wishlist count in header if element exists
                        this.updateWishlistCount();
                    } else {
                        this.showWishlistMessage(response.data.message || wcBlockExtensions.messages.error, 'error');
                    }
                },
                error: () => {
                    this.showWishlistMessage(wcBlockExtensions.messages.error, 'error');
                },
                complete: () => {
                    $button.removeClass('wc-wishlist-loading').prop('disabled', false);
                }
            });
        }

        /**
         * Show wishlist message notification
         *
         * @param {string} message Message text
         * @param {string} type Message type (success or error)
         */
        showWishlistMessage(message, type = 'success') {
            // Remove existing messages
            $('.wc-wishlist-message').remove();

            // Create message element
            const $message = $('<div class="wc-wishlist-message wc-wishlist-message--' + type + '">' + message + '</div>');
            $('body').append($message);

            // Trigger animation
            setTimeout(() => {
                $message.addClass('wc-wishlist-message--visible');
            }, 10);

            // Auto-hide after 3 seconds
            setTimeout(() => {
                $message.removeClass('wc-wishlist-message--visible');
                setTimeout(() => {
                    $message.remove();
                }, 300);
            }, 3000);
        }

        /**
         * Update wishlist count in header
         */
        updateWishlistCount() {
            // Trigger Blocksy's wishlist count update
            if (typeof ctEvents !== 'undefined') {
                ctEvents.trigger('blocksy:woocommerce:wish-list-count-update');
            }

            // Also update any custom wishlist count elements
            const $countElements = $('.ct-header-wishlist .ct-dynamic-count-wishlist, .wishlist-count');
            if ($countElements.length) {
                // Fetch updated count via AJAX
                $.ajax({
                    url: wcBlockExtensions.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'get_wishlist_count',
                        nonce: wcBlockExtensions.nonce
                    },
                    success: (response) => {
                        if (response.success && response.data.count !== undefined) {
                            $countElements.text(response.data.count);
                        }
                    }
                });
            }
        }
    }

    /**
     * Initialize when DOM is ready
     */
    $(document).ready(() => {
        new ProductImageEnhancements();
    });

    /**
     * Re-initialize after WooCommerce blocks are loaded
     */
    $(document.body).on('wc-blocks_render_blocks_frontend', () => {
        new ProductImageEnhancements();
    });

    /**
     * Re-initialize after AJAX product loading
     */
    $(document.body).on('updated_wc_div', () => {
        new ProductImageEnhancements();
    });

})(jQuery);

