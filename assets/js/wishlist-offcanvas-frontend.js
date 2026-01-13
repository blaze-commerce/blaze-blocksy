/**
 * Wishlist Off-Canvas Frontend Handler
 *
 * Modifies the wishlist header link to trigger the off-canvas panel
 * instead of navigating to the wishlist page.
 *
 * @package BlocksyChild
 * @since 1.0.0
 */

(function ($) {
  'use strict';

  /**
   * Initialize wishlist off-canvas functionality
   */
  function initWishlistOffcanvas() {
    // Find all wishlist header links
    var wishlistLinks = document.querySelectorAll('a.ct-header-wishlist[data-id="wish-list"]');
    
    if (!wishlistLinks.length) {
      return;
    }

    wishlistLinks.forEach(function(link) {
      // Change href to point to the off-canvas panel
      link.setAttribute('href', '#wishlist-offcanvas');
      
      // Add the off-canvas trigger class
      if (!link.classList.contains('ct-offcanvas-trigger')) {
        link.classList.add('ct-offcanvas-trigger');
      }
      
      // Add data-toggle-panel attribute for the offcanvas module
      link.setAttribute('data-toggle-panel', '#wishlist-offcanvas-panel');
      
      // Add aria-expanded attribute
      link.setAttribute('aria-expanded', 'false');
    });

    // Also handle click events directly as a fallback
    $(document).on('click', 'a.ct-header-wishlist[data-id="wish-list"]', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      var panel = document.getElementById('wishlist-offcanvas-panel');
      
      if (panel) {
        // Use the OffcanvasModule if available
        if (typeof window.OffcanvasModule !== 'undefined') {
          window.OffcanvasModule.openPanel('wishlist-offcanvas-panel', this);
        } else {
          // Fallback: Use Blocksy's native panel handling
          openWishlistPanel(panel, this);
        }
      }
      
      return false;
    });
  }

  /**
   * Fallback function to open the wishlist panel
   * Uses Blocksy's standard panel state management
   *
   * @param {HTMLElement} panel - The panel element
   * @param {HTMLElement} trigger - The trigger element
   */
  function openWishlistPanel(panel, trigger) {
    var behaviour = panel.getAttribute('data-behaviour') || 'right-side';
    var direction = behaviour.includes('left') ? ':left' : behaviour === 'modal' ? '' : ':right';

    // Set initial state
    document.body.setAttribute('data-panel', '');
    panel.classList.add('active');
    panel.removeAttribute('inert');

    // Use requestAnimationFrame for smooth animation
    requestAnimationFrame(function() {
      requestAnimationFrame(function() {
        document.body.setAttribute('data-panel', 'in' + direction);
      });
    });

    // Update ARIA
    if (trigger) {
      trigger.setAttribute('aria-expanded', 'true');
    }

    // Setup close handlers
    setupCloseHandlers(panel);
  }

  /**
   * Setup close handlers for the panel
   *
   * @param {HTMLElement} panel - The panel element
   */
  function setupCloseHandlers(panel) {
    // Close button
    var closeBtn = panel.querySelector('.ct-toggle-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        closeWishlistPanel(panel);
      });
    }

    // Click outside to close
    panel.addEventListener('click', function(e) {
      if (e.target === panel) {
        closeWishlistPanel(panel);
      }
    });

    // ESC key
    document.addEventListener('keydown', function escHandler(e) {
      if (e.key === 'Escape' && panel.classList.contains('active')) {
        closeWishlistPanel(panel);
        document.removeEventListener('keydown', escHandler);
      }
    });
  }

  /**
   * Close the wishlist panel
   *
   * @param {HTMLElement} panel - The panel element
   */
  function closeWishlistPanel(panel) {
    document.body.setAttribute('data-panel', 'out');

    setTimeout(function() {
      document.body.removeAttribute('data-panel');
      panel.classList.remove('active');
      panel.setAttribute('inert', '');

      // Update ARIA on triggers
      var triggers = document.querySelectorAll('a.ct-header-wishlist[data-id="wish-list"]');
      triggers.forEach(function(trigger) {
        trigger.setAttribute('aria-expanded', 'false');
      });
    }, 250);
  }

  // Initialize on DOM ready
  $(document).ready(function() {
    initWishlistOffcanvas();
  });

  // Re-initialize after AJAX content updates (for dynamic content)
  $(document).on('ajaxComplete', function() {
    initWishlistOffcanvas();
  });

})(jQuery);

