/**
 * WooCommerce Product Image Block Enhancements
 *
 * JavaScript functionality for hover image effect and wishlist button
 * on WooCommerce Product Image blocks.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  /**
   * Product Image Block Enhancement Class
   */
  class ProductImageBlockEnhancement {
    constructor() {
      this.hoverTimeout = null;
      this.preloadedImages = new Set();
      this.activeHoverContainers = new Set();
      this.init();
    }

    /**
     * Initialize all enhancements
     */
    init() {
      this.setupHoverImages();
      this.setupWishlistButtons();
      this.setupDynamicContent();
    }

    /**
     * Setup hover image functionality
     */
    setupHoverImages() {
      $(".wc-hover-image-enabled").each((_, element) => {
        this.initHoverImage($(element));
      });
    }

    /**
     * Initialize hover image for a single container
     *
     * @param {jQuery} $container - The product image container
     */
    initHoverImage($container) {
      // Get hover image data from separate data attributes
      const hoverUrl = $container.attr("data-hover-url");
      const hoverSrcset = $container.attr("data-hover-srcset") || "";
      const hoverAlt = $container.attr("data-hover-alt") || "";

      // Exit if no hover image URL
      if (!hoverUrl) {
        return;
      }

      const $image = $container.find("img").first();

      // Exit if no image found
      if (!$image.length) {
        return;
      }

      // Store original image data
      const originalSrc = $image.attr("src");
      const originalSrcset = $image.attr("srcset") || "";
      const originalAlt = $image.attr("alt") || "";

      // Store original data in container for global access
      $container.data("original-src", originalSrc);
      $container.data("original-srcset", originalSrcset);
      $container.data("original-alt", originalAlt);

      // Preload hover image on first interaction
      $container.one("mouseenter touchstart", () => {
        this.preloadImage(hoverUrl);
      });

      // Store timeout reference for this specific container
      let containerTimeout = null;

      // Mouse enter - show hover image
      $container.on("mouseenter", () => {
        // Clear any pending timeout for this container
        clearTimeout(containerTimeout);

        // Reset all other hover images first
        this.resetAllHoverImages($container);

        // Add this container to active set
        this.activeHoverContainers.add($container[0]);

        // Add swapping class for smooth transition
        $image.addClass("swapping");

        // Immediate image swap for better responsiveness
        setTimeout(() => {
          $image.attr("src", hoverUrl);

          if (hoverSrcset) {
            $image.attr("srcset", hoverSrcset);
          }

          if (hoverAlt) {
            $image.attr("alt", hoverAlt);
          }

          // Remove swapping class
          setTimeout(() => {
            $image.removeClass("swapping");
          }, 20);
        }, 20);
      });

      // Mouse leave - restore original image
      $container.on("mouseleave", () => {
        // Clear any existing timeout for this container
        clearTimeout(containerTimeout);

        // Remove from active set
        this.activeHoverContainers.delete($container[0]);

        // Set immediate timeout for restoring original image
        containerTimeout = setTimeout(() => {
          // Add swapping class for smooth transition
          $image.addClass("swapping");

          // Restore original image attributes immediately
          setTimeout(() => {
            $image.attr("src", originalSrc);
            $image.attr("srcset", originalSrcset);
            $image.attr("alt", originalAlt);

            // Remove swapping class after transition
            setTimeout(() => {
              $image.removeClass("swapping");
            }, 20);
          }, 20);
        }, 5); // Very short delay for immediate response
      });
    }

    /**
     * Reset all hover images to their original state except the current one
     *
     * @param {jQuery} $currentContainer - The currently hovered container to exclude
     */
    resetAllHoverImages($currentContainer) {
      $(".wc-hover-image-enabled")
        .not($currentContainer)
        .each((_, element) => {
          const $container = $(element);
          const $image = $container.find("img").first();

          if (!$image.length) return;

          // Get original image data from container
          const originalSrc = $container.data("original-src");
          const originalSrcset = $container.data("original-srcset") || "";
          const originalAlt = $container.data("original-alt") || "";

          // Only reset if we have original data stored
          if (originalSrc && $image.attr("src") !== originalSrc) {
            $image.addClass("swapping");

            setTimeout(() => {
              $image.attr("src", originalSrc);
              $image.attr("srcset", originalSrcset);
              $image.attr("alt", originalAlt);

              setTimeout(() => {
                $image.removeClass("swapping");
              }, 20);
            }, 20);
          }
        });
    }

    /**
     * Preload image for better performance
     *
     * @param {string} src - Image source URL
     */
    preloadImage(src) {
      if (this.preloadedImages.has(src)) {
        return;
      }

      const img = new Image();
      img.src = src;
      this.preloadedImages.add(src);
    }

    /**
     * Setup wishlist button functionality
     */
    setupWishlistButtons() {
      // Use event delegation for dynamic content
      $(document).on("click", ".wc-product-image-wishlist-button", (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.handleWishlistClick($(e.currentTarget));
      });
    }

    /**
     * Handle wishlist button click
     *
     * @param {jQuery} $button - The clicked wishlist button
     */
    handleWishlistClick($button) {
      const productId = $button.data("product-id");

      // Check if already in wishlist
      const isAdded = $button.hasClass("added");

      // Add loading state
      $button.addClass("loading");

      // Determine action
      const action = isAdded ? "remove_from_wishlist" : "add_to_wishlist";

      // Make AJAX request
      $.ajax({
        url: blazeProductImageBlock.ajax_url,
        type: "POST",
        data: {
          action: action,
          product_id: productId,
          nonce: blazeProductImageBlock.nonce,
        },
        success: (response) => {
          if (response.success) {
            // Toggle added state
            $button.toggleClass("added");

            // Show success message
            const message = isAdded
              ? blazeProductImageBlock.messages.removed
              : blazeProductImageBlock.messages.added;
            this.showNotification(message, "success");

            // Trigger custom event for other scripts to listen
            $(document).trigger("blazeWishlistUpdated", {
              productId: productId,
              action: action,
              isAdded: !isAdded,
            });

            // Update all wishlist buttons for this product
            this.updateWishlistButtons(productId, !isAdded);

            // Sync with wishlist offcanvas if exists
            this.syncWishlistOffcanvas();
          } else {
            this.showNotification(
              response.data || blazeProductImageBlock.messages.error,
              "error"
            );
          }
        },
        error: () => {
          this.showNotification(blazeProductImageBlock.messages.error, "error");
        },
        complete: () => {
          $button.removeClass("loading");
        },
      });
    }

    /**
     * Update all wishlist buttons for a product
     *
     * @param {number} productId - The product ID
     * @param {boolean} isAdded - Whether product is added to wishlist
     */
    updateWishlistButtons(productId, isAdded) {
      $(
        `.wc-product-image-wishlist-button[data-product-id="${productId}"]`
      ).each(function () {
        if (isAdded) {
          $(this).addClass("added");
        } else {
          $(this).removeClass("added");
        }
      });
    }

    /**
     * Sync with wishlist offcanvas
     */
    syncWishlistOffcanvas() {
      // Trigger wishlist refresh if offcanvas exists
      if (typeof window.blazeWishlistOffcanvas !== "undefined") {
        $(document).trigger("blazeRefreshWishlist");
      }

      // Update wishlist counter if exists
      this.updateWishlistCounter();
    }

    /**
     * Update wishlist counter in header
     */
    updateWishlistCounter() {
      // This will be handled by the existing wishlist system
      // Just trigger the event
      $(document).trigger("blazeUpdateWishlistCounter");
    }

    /**
     * Show notification message
     *
     * @param {string} message - The message to display
     * @param {string} type - The notification type (success, error, info)
     */
    showNotification(message, type = "success") {
      // Create notification element
      const $notification = $(
        `<div class="wc-wishlist-notification ${type}">${message}</div>`
      );

      // Append to body
      $("body").append($notification);

      // Auto remove after 3 seconds
      setTimeout(() => {
        $notification.css("animation", "fadeOut 0.3s ease-out");
        setTimeout(() => {
          $notification.remove();
        }, 300);
      }, 3000);
    }

    /**
     * Setup dynamic content handling
     * For AJAX-loaded products (infinite scroll, filters, etc.)
     */
    setupDynamicContent() {
      // Listen for WooCommerce events
      $(document.body).on("updated_wc_div", () => {
        this.setupHoverImages();
      });

      // Listen for custom events from other scripts
      $(document).on("blazeProductsLoaded", () => {
        this.setupHoverImages();
      });
    }
  }

  /**
   * Initialize on document ready
   */
  $(document).ready(function () {
    new ProductImageBlockEnhancement();
  });

  /**
   * Re-initialize on AJAX complete (for dynamic content)
   */
  $(document).ajaxComplete(function (_, __, settings) {
    // Check if this is a WooCommerce AJAX request
    if (
      settings.url &&
      (settings.url.indexOf("wc-ajax") !== -1 ||
        settings.url.indexOf("action=") !== -1)
    ) {
      // Small delay to ensure DOM is updated
      setTimeout(() => {
        new ProductImageBlockEnhancement();
      }, 100);
    }
  });
})(jQuery);
