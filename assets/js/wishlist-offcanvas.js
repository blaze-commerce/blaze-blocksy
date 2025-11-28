/**
 * Wishlist Off-Canvas Functionality
 *
 * This script handles the off-canvas wishlist functionality for the Blocksy child theme.
 */

(function ($) {
  'use strict';

  // Constants
  const DELAYS = {
    DATA_SYNC: 200, // Delay to ensure wishlist data is committed
    CONTENT_RENDER: 100, // Delay to ensure content is rendered before showing
    BUTTON_RESET: 2000, // Delay to reset button text after add to cart
  };

  const SELECTORS = {
    PANEL: '#wishlist-offcanvas-panel',
    CONTENT: '.ct-panel-content-inner',
    WISHLIST_COUNT: '.wishlist-count',
    HEADER_WISHLIST:
      '.ct-header-wishlist, .ct-header-wishlist a, [data-id="wishlist"], [data-shortcut="wishlist"]',
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
    $(document).on(
      'click',
      '.ct-header-wishlist a, .ct-header-wishlist',
      function (e) {
        const $this = $(this);
        const href = $this.attr('href') || '';
        const hasOffcanvasClass = $this.hasClass('ct-offcanvas-trigger');

        if (hasOffcanvasClass || href.includes('#wishlist-offcanvas')) {
          e.preventDefault();
          e.stopPropagation();
          openWishlistOffCanvas();
          return false;
        }
      },
    );
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
      console.warn(
        'Wishlist off-canvas panel not found. Make sure "Off Canvas" is selected in Customizer > WooCommerce > Wishlist Display Mode',
      );
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
          isModal: false,
        },
      });

      // CRITICAL FIX: Ensure pointer events work after Blocksy's system activates
      // Add a small delay to check if the panel is properly activated
      setTimeout(function () {
        ensurePanelIsClickable(panel);
      }, 50);
    } else {
      // Fallback: simple show/hide
      $(panel).addClass('active');
      $('body').addClass('wishlist-offcanvas-open');
      ensurePanelIsClickable(panel);
    }
  }

  /**
   * Ensure the panel is clickable by forcing pointer events if needed
   */
  function ensurePanelIsClickable(panel) {
    if (!panel) return;

    // Force the panel to be active and clickable
    panel.classList.add('active');

    // Ensure pointer events are enabled on the panel itself
    panel.style.pointerEvents = 'auto';

    // Also ensure all child elements are clickable
    const interactiveSelectors = [
      'a',
      'button',
      'input',
      'select',
      'textarea',
      '.button',
      '.ct-wishlist-remove',
      '.add_to_cart_button',
      '.wishlist-item',
      '.recommendation-item',
      '.guest-signup-button',
      '.signup-button',
      '.ct-toggle-close',
      '.wishlist-item-title a',
      '.recommendation-item-title a',
      '.wishlist-item-actions',
      '.recommendation-item-actions',
    ];

    const interactiveElements = panel.querySelectorAll(
      interactiveSelectors.join(', '),
    );
    interactiveElements.forEach(function (element) {
      element.style.pointerEvents = 'auto';
      element.style.position = 'relative';
      element.style.zIndex = '10';

      // CRITICAL: Force cursor pointer for links and add backup click handler
      if (element.tagName.toLowerCase() === 'a') {
        element.style.cursor = 'pointer';

        // Add click event listener as backup to force navigation
        element.addEventListener(
          'click',
          function (e) {
            const href = this.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('#')) {
              console.log('Backup navigation triggered for:', href);
              // Small delay to ensure any other handlers run first
              setTimeout(function () {
                window.location.href = href;
              }, 50);
            }
          },
          true,
        ); // Use capture phase to run before other handlers
      }
    });

    // Ensure all container elements are clickable
    const containerSelectors = [
      '.ct-panel-content',
      '.ct-panel-content-inner',
      '.ct-offcanvas-wishlist',
      '.wishlist-items',
      '.wishlist-recommendations',
      '.recommendations-grid',
    ];

    containerSelectors.forEach(function (selector) {
      const elements = panel.querySelectorAll(selector);
      elements.forEach(function (element) {
        element.style.pointerEvents = 'auto';
        element.style.position = 'relative';
      });
    });

    // Debug log to help troubleshoot if needed
    console.log(
      'Wishlist off-canvas comprehensive clickability ensured for panel and',
      interactiveElements.length,
      'interactive elements',
    );
  }

  /**
   * Handle actions within the off-canvas wishlist
   */
  function handleOffCanvasWishlistActions() {
    // Handle remove from wishlist
    $(document).on(
      'click',
      '.ct-offcanvas-wishlist .ct-wishlist-remove',
      function (e) {
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
            productId: productId,
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
      },
    );

    // Handle add to cart from off-canvas
    $(document).on(
      'click',
      '.ct-offcanvas-wishlist .add_to_cart_button',
      function (e) {
        e.preventDefault();

        const $button = $(this);
        const productId = $button.data('product_id');

        if (!productId) {
          return;
        }

        // Add loading state
        $button.addClass('loading').prop('disabled', true);

        // Add to cart via AJAX
        $.post(
          wc_add_to_cart_params.ajax_url,
          {
            action: 'woocommerce_add_to_cart',
            product_id: productId,
            quantity: 1,
          },
          function (response) {
            if (response.error) {
              // Show error message in a more user-friendly way
              $button.text('Error').addClass('error');
              setTimeout(function () {
                $button
                  .text('Add to cart')
                  .removeClass('error')
                  .prop('disabled', false);
              }, DELAYS.BUTTON_RESET);
            } else {
              // Trigger cart update
              $(document.body).trigger('added_to_cart', [
                response.fragments,
                response.cart_hash,
                $button,
              ]);

              // Show success message
              $button.text('Added!').removeClass('loading');

              setTimeout(function () {
                $button.text('Add to cart').prop('disabled', false);
              }, DELAYS.BUTTON_RESET);
            }
          },
        ).fail(function () {
          $button.removeClass('loading').prop('disabled', false);
          $button.text('Error').addClass('error');
          setTimeout(function () {
            $button
              .text('Add to cart')
              .removeClass('error')
              .prop('disabled', false);
          }, DELAYS.BUTTON_RESET);
        });
      },
    );

    // CRITICAL FIX: Ensure all links work properly by forcing click events
    $(document).on(
      'click',
      '.ct-offcanvas-wishlist a, .wishlist-recommendations a',
      function (e) {
        const $link = $(this);
        const href = $link.attr('href');

        // Debug log
        console.log('Link clicked in wishlist off-canvas:', href);

        // If it's a valid URL, ensure navigation works
        if (href && href !== '#' && !href.startsWith('#')) {
          // Force navigation if the default behavior is being blocked
          setTimeout(function () {
            if (href.startsWith('http') || href.startsWith('/')) {
              window.location.href = href;
            }
          }, 10);
        }

        // Don't prevent default - let normal navigation work
        return true;
      },
    );

    // Handle sign-up button clicks for guest users
    $(document).on(
      'click',
      '.ct-offcanvas-wishlist .guest-signup-button, .ct-offcanvas-wishlist .signup-button',
      function (e) {
        const $button = $(this);
        const href = $button.attr('href');

        console.log('Sign-up button clicked in wishlist off-canvas:', href);

        // Force navigation for sign-up buttons
        if (href && href !== '#') {
          setTimeout(function () {
            window.location.href = href;
          }, 10);
        }

        return true;
      },
    );
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
    const ajaxUrl =
      typeof wishlistOffcanvas !== 'undefined' && wishlistOffcanvas.ajaxUrl
        ? wishlistOffcanvas.ajaxUrl
        : '/wp-admin/admin-ajax.php';

    const nonce =
      typeof wishlistOffcanvas !== 'undefined' && wishlistOffcanvas.nonce
        ? wishlistOffcanvas.nonce
        : '';

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
      nonce: nonce,
    })
      .done(function (response) {
        if (response.success) {
          $content.html(response.data.content);

          // Update wishlist count in heading
          const count = response.data.count || 0;
          $panel.find(SELECTORS.WISHLIST_COUNT).text('(' + count + ')');

          // CRITICAL FIX: Ensure new content is clickable
          ensurePanelIsClickable($panel[0]);

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
   * CRITICAL FIX: Ensure this doesn't interfere with clicks inside the panel
   */
  $(document).on('click', function (e) {
    if ($('body').hasClass('wishlist-offcanvas-open')) {
      const $panel = $(SELECTORS.PANEL);
      const $target = $(e.target);

      // IMPROVED: More precise detection of clicks inside the panel
      // Don't close if clicking on the panel itself or any of its children
      if (
        !$panel.is(e.target) &&
        $panel.has(e.target).length === 0 &&
        !$target.closest('#wishlist-offcanvas-panel').length &&
        !$target.closest('.ct-offcanvas-wishlist').length &&
        !$target.closest('.wishlist-recommendations').length
      ) {
        $panel.removeClass('active');
        $('body').removeClass('wishlist-offcanvas-open');
      }
    }
  });
})(jQuery);
