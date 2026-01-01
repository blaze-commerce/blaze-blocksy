/**
 * Wishlist Overlay Click-to-Close Handler
 *
 * Adds functionality to close the wishlist panel when clicking outside of it
 * (on the dark overlay backdrop)
 *
 * @package WishlistPlugin
 * @since 1.0.0
 */

(function() {
  'use strict';

  /**
   * Initialize overlay click-to-close functionality
   * Runs when DOM is ready
   */
  function initOverlayClickHandler() {
    // Add click handler to document body
    document.body.addEventListener('click', function(e) {
      // Check if wishlist panel is currently open
      const wishlistPanel = document.querySelector('[id*="wishlist"][role="dialog"].active') ||
                           document.querySelector('[id*="wish-list"][role="dialog"].active');

      // If panel is not open, do nothing
      if (!wishlistPanel) {
        return;
      }

      // Check if click was outside the panel (on the overlay area)
      // If click target is not inside the panel, user clicked on overlay
      if (!wishlistPanel.contains(e.target)) {
        // Find the close button for the wishlist panel
        const closeButton = wishlistPanel.querySelector('[data-type="type-1:close"]') ||
                           wishlistPanel.querySelector('.ct-toggle-close');

        // Trigger close button click to close the panel
        if (closeButton) {
          closeButton.click();
        }
      }
    });
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOverlayClickHandler);
  } else {
    // DOM already loaded
    initOverlayClickHandler();
  }

})();
