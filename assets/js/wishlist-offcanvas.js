/**
 * Wishlist Off-Canvas Functionality
 * 
 * This script handles the off-canvas wishlist functionality for the Blocksy child theme.
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initWishlistOffCanvas();
    });

    /**
     * Initialize wishlist off-canvas functionality
     */
    function initWishlistOffCanvas() {
        // Override wishlist header item clicks
        overrideWishlistHeaderClicks();
        
        // Handle wishlist item actions within off-canvas
        handleOffCanvasWishlistActions();
        
        // Refresh off-canvas content when wishlist changes
        listenForWishlistChanges();
    }

    /**
     * Override wishlist header item clicks to open off-canvas
     */
    function overrideWishlistHeaderClicks() {
        $(document).on('click', '.ct-header-wishlist', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            openWishlistOffCanvas();
        });
    }

    /**
     * Open the wishlist off-canvas panel
     */
    function openWishlistOffCanvas() {
        const panel = document.getElementById('wishlist-offcanvas-panel');
        
        if (!panel) {
            console.warn('Wishlist off-canvas panel not found');
            return;
        }

        // Use Blocksy's overlay system if available
        if (typeof ctEvents !== 'undefined') {
            ctEvents.trigger('ct:overlay:handle-click', {
                event: new Event('click'),
                options: {
                    container: panel,
                    clickOutside: true,
                    focus: true,
                    isModal: false
                }
            });
        } else {
            // Fallback: simple show/hide
            $(panel).addClass('active');
            $('body').addClass('wishlist-offcanvas-open');
        }
    }

    /**
     * Handle actions within the off-canvas wishlist
     */
    function handleOffCanvasWishlistActions() {
        // Handle remove from wishlist
        $(document).on('click', '.ct-offcanvas-wishlist .ct-wishlist-remove', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const productId = $button.data('product-id');
            
            if (!productId) {
                return;
            }

            // Add loading state
            $button.addClass('loading').prop('disabled', true);
            
            // Remove item from wishlist (this will trigger the existing wishlist system)
            if (typeof ctEvents !== 'undefined') {
                ctEvents.trigger('blocksy:woocommerce:wish-list-remove', {
                    productId: productId
                });
            }
            
            // Remove the item from the off-canvas display
            $button.closest('.wishlist-item').fadeOut(300, function() {
                $(this).remove();
                
                // Check if wishlist is now empty
                if ($('.ct-offcanvas-wishlist .wishlist-item').length === 0) {
                    refreshOffCanvasContent();
                }
            });
        });

        // Handle add to cart from off-canvas
        $(document).on('click', '.ct-offcanvas-wishlist .add_to_cart_button', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const productId = $button.data('product_id');
            
            if (!productId) {
                return;
            }

            // Add loading state
            $button.addClass('loading').prop('disabled', true);
            
            // Add to cart via AJAX
            $.post(wc_add_to_cart_params.ajax_url, {
                action: 'woocommerce_add_to_cart',
                product_id: productId,
                quantity: 1
            }, function(response) {
                if (response.error) {
                    alert(response.error);
                } else {
                    // Trigger cart update
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                    
                    // Show success message
                    $button.text('Added!').removeClass('loading');
                    
                    setTimeout(function() {
                        $button.text('Add to cart').prop('disabled', false);
                    }, 2000);
                }
            }).fail(function() {
                $button.removeClass('loading').prop('disabled', false);
                alert('Error adding to cart');
            });
        });
    }

    /**
     * Listen for wishlist changes and refresh off-canvas content
     */
    function listenForWishlistChanges() {
        // Listen for Blocksy wishlist events
        if (typeof ctEvents !== 'undefined') {
            ctEvents.on('blocksy:woocommerce:wish-list-change', function(data) {
                refreshOffCanvasContent();
            });
        }
    }

    /**
     * Refresh the off-canvas wishlist content
     */
    function refreshOffCanvasContent() {
        const $panel = $('#wishlist-offcanvas-panel');
        const $content = $panel.find('.ct-panel-content-inner');
        
        if ($content.length === 0) {
            return;
        }

        // Show loading state
        $content.addClass('loading');
        
        // Load fresh content via AJAX
        $.post(wishlistOffcanvas.ajaxUrl, {
            action: 'load_wishlist_offcanvas',
            nonce: wishlistOffcanvas.nonce
        }, function(response) {
            if (response.success) {
                $content.html(response.data.content);
            }
        }).always(function() {
            $content.removeClass('loading');
        });
    }

    /**
     * Close off-canvas when clicking outside (fallback)
     */
    $(document).on('click', function(e) {
        if ($('body').hasClass('wishlist-offcanvas-open')) {
            const $panel = $('#wishlist-offcanvas-panel');
            
            if (!$panel.is(e.target) && $panel.has(e.target).length === 0) {
                $panel.removeClass('active');
                $('body').removeClass('wishlist-offcanvas-open');
            }
        }
    });

})(jQuery);
