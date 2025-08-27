/**
 * Wishlist Off-Canvas Functionality
 *
 * This script handles the off-canvas wishlist functionality for the Blocksy child theme.
 */

(function ($) {
    'use strict';

    // Constants
    const DELAYS = {
        DATA_SYNC: 200,        // Delay to ensure wishlist data is committed
        CONTENT_RENDER: 100,   // Delay to ensure content is rendered before showing
        BUTTON_RESET: 2000     // Delay to reset button text after add to cart
    };

    const SELECTORS = {
        PANEL: '#wishlist-offcanvas-panel',
        CONTENT: '.ct-panel-content-inner',
        WISHLIST_COUNT: '.wishlist-count',
        HEADER_WISHLIST: '.ct-header-wishlist, .ct-header-wishlist a, [data-id="wishlist"], [data-shortcut="wishlist"]'
    };

    // Initialize when DOM is ready
    $(document).ready(function () {
        initWishlistOffCanvas();
    });

    /**
     * Initialize wishlist off-canvas functionality
     */
    function initWishlistOffCanvas() {
        // Override wishlist header item clicks
        overrideWishlistHeaderClicks();

        // Add fallback click handler
        addFallbackClickHandler();

        // Handle wishlist item actions within off-canvas
        handleOffCanvasWishlistActions();

        // Listen for wishlist changes to refresh and auto-show off-canvas
        listenForWishlistChanges();
    }

    /**
     * Override wishlist header item clicks to open off-canvas
     */
    function overrideWishlistHeaderClicks() {
        const panel = document.querySelector(SELECTORS.PANEL);
        if (!panel) {
            return; // Off-canvas not enabled, let default behavior work
        }

        // Override any wishlist header clicks when off-canvas is available
        $(document).on('click', '.ct-header-wishlist a, .ct-header-wishlist', function (e) {
            const $this = $(this);
            const href = $this.attr('href') || '';
            const hasOffcanvasClass = $this.hasClass('ct-offcanvas-trigger');

            if (hasOffcanvasClass || href.includes('#wishlist-offcanvas')) {
                e.preventDefault();
                e.stopPropagation();
                openWishlistOffCanvas();
                return false;
            }
        });
    }

    /**
     * Add fallback click handler for wishlist header items
     */
    function addFallbackClickHandler() {
        const panel = document.querySelector(SELECTORS.PANEL);
        if (!panel) {
            return;
        }

        // Add a more aggressive click handler that catches all wishlist clicks
        $(document).on('click', SELECTORS.HEADER_WISHLIST, function (e) {
            // Always prevent default and open off-canvas when panel exists
            e.preventDefault();
            e.stopPropagation();
            openWishlistOffCanvas();
            return false;
        });
    }

    /**
     * Open the wishlist off-canvas panel
     */
    function openWishlistOffCanvas() {
        const panel = document.querySelector(SELECTORS.PANEL);

        if (!panel) {
            console.warn('Wishlist off-canvas panel not found. Make sure "Off Canvas" is selected in Customizer > WooCommerce > Wishlist Display Mode');
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
        $(document).on('click', '.ct-offcanvas-wishlist .ct-wishlist-remove', function (e) {
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
            $button.closest('.wishlist-item').fadeOut(300, function () {
                $(this).remove();

                // Check if wishlist is now empty
                if ($('.ct-offcanvas-wishlist .wishlist-item').length === 0) {
                    refreshOffCanvasContent();
                }
            });
        });

        // Handle add to cart from off-canvas
        $(document).on('click', '.ct-offcanvas-wishlist .add_to_cart_button', function (e) {
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
            }, function (response) {
                if (response.error) {
                    alert(response.error);
                } else {
                    // Trigger cart update
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);

                    // Show success message
                    $button.text('Added!').removeClass('loading');

                    setTimeout(function () {
                        $button.text('Add to cart').prop('disabled', false);
                    }, DELAYS.BUTTON_RESET);
                }
            }).fail(function () {
                $button.removeClass('loading').prop('disabled', false);
                alert('Error adding to cart');
            });
        });
    }

    /**
     * Listen for wishlist changes and handle off-canvas refresh/auto-show
     */
    function listenForWishlistChanges() {
        if (typeof ctEvents !== 'undefined') {
            ctEvents.on('blocksy:wishlist:sync', function () {
                // Check if off-canvas panel exists for auto-show
                const panel = document.querySelector(SELECTORS.PANEL);
                const shouldAutoShow = panel !== null;

                // Add a small delay to ensure wishlist data is fully updated before fetching content
                setTimeout(function () {
                    refreshOffCanvasContent(shouldAutoShow);
                }, DELAYS.DATA_SYNC);
            });
        }
    }

    /**
     * Get AJAX configuration for wishlist requests
     */
    function getAjaxConfig() {
        // Use localized data if available, otherwise fallback
        const ajaxUrl = (typeof wishlistOffcanvas !== 'undefined' && wishlistOffcanvas.ajaxUrl) ?
            wishlistOffcanvas.ajaxUrl : '/wp-admin/admin-ajax.php';

        const nonce = (typeof wishlistOffcanvas !== 'undefined' && wishlistOffcanvas.nonce) ?
            wishlistOffcanvas.nonce : '';

        return { ajaxUrl, nonce };
    }

    /**
     * Refresh the off-canvas wishlist content
     */
    function refreshOffCanvasContent(showAfterRefresh = false) {
        const $panel = $(SELECTORS.PANEL);
        const $content = $panel.find(SELECTORS.CONTENT);

        if ($content.length === 0) {
            return;
        }

        // Show loading state
        $content.addClass('loading');

        const { ajaxUrl, nonce } = getAjaxConfig();

        // Load fresh content via AJAX
        $.post(ajaxUrl, {
            action: 'load_wishlist_offcanvas',
            nonce: nonce
        })
            .done(function (response) {
                if (response.success) {
                    $content.html(response.data.content);

                    // Update wishlist count in heading
                    const count = response.data.count || 0;
                    $panel.find(SELECTORS.WISHLIST_COUNT).text('(' + count + ')');

                    // Show off-canvas if requested
                    if (showAfterRefresh) {
                        setTimeout(openWishlistOffCanvas, DELAYS.CONTENT_RENDER);
                    }
                }
            })
            .fail(function (xhr, status, error) {
                console.error('Wishlist AJAX error:', status, error);
            })
            .always(function () {
                $content.removeClass('loading');
            });
    }

    /**
     * Close off-canvas when clicking outside (fallback)
     */
    $(document).on('click', function (e) {
        if ($('body').hasClass('wishlist-offcanvas-open')) {
            const $panel = $(SELECTORS.PANEL);

            if (!$panel.is(e.target) && $panel.has(e.target).length === 0) {
                $panel.removeClass('active');
                $('body').removeClass('wishlist-offcanvas-open');
            }
        }
    });

    // Expose function for testing
    window.testWishlistOffCanvas = function () {
        const panel = document.querySelector(SELECTORS.PANEL);
        if (panel) {
            openWishlistOffCanvas();
        } else {
            console.log('Off-canvas panel not found. Check if off-canvas mode is enabled in Customizer.');
        }
    };

})(jQuery);
