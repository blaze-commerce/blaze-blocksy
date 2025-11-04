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
      this.isProcessing = false;
      this.init();
    }

    /**
     * Initialize all enhancements
     */
    init() {
      this.setupHoverImages();
      this.setupWishlistButtons();
      this.setupDynamicContent();
      this.syncWishlistOnLoad();
      this.setupWishlistEventListeners();
    }

    /**
     * Setup hover image functionality
     */
    setupHoverImages() {
      $(".wc-hover-image-enabled").each((index, element) => {
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

      // Check if image is using LazyLoad
      const isLazyLoaded = this.isImageLazyLoaded($image);

      // Function to get the actual image source (handles LazyLoad)
      const getActualImageSrc = () => {
        // Priority 1: data-src (LazyLoad original source)
        const dataSrc = $image.attr("data-src");
        if (dataSrc && !dataSrc.startsWith("data:image")) {
          return dataSrc;
        }

        // Priority 2: current src (if not base64 placeholder)
        const currentSrc = $image.attr("src");
        if (currentSrc && !currentSrc.startsWith("data:image")) {
          return currentSrc;
        }

        // Priority 3: first srcset URL (if available)
        const srcset =
          $image.attr("srcset") || $image.attr("data-srcset") || "";
        if (srcset) {
          const firstSrcsetUrl = srcset.split(",")[0].trim().split(" ")[0];
          if (firstSrcsetUrl && !firstSrcsetUrl.startsWith("data:image")) {
            return firstSrcsetUrl;
          }
        }

        // Fallback: return current src even if it's base64
        return currentSrc || "";
      };

      // Function to get the actual srcset (handles LazyLoad)
      const getActualImageSrcset = () => {
        // Priority 1: data-srcset (LazyLoad original srcset)
        const dataSrcset = $image.attr("data-srcset");
        if (dataSrcset) {
          return dataSrcset;
        }

        // Priority 2: current srcset
        return $image.attr("srcset") || "";
      };

      // Store original image data with LazyLoad handling
      let originalSrc = getActualImageSrc();
      let originalSrcset = getActualImageSrcset();
      let originalAlt = $image.attr("alt") || "";

      // If LazyLoad is detected and image not yet loaded, wait for it
      if (isLazyLoaded && !$image.hasClass("lazyloaded")) {
        // Listen for lazyloaded event
        $image.one("lazyloaded", () => {
          // Update original image data after LazyLoad completes
          originalSrc = getActualImageSrc();
          originalSrcset = getActualImageSrcset();
          originalAlt = $image.attr("alt") || "";
        });
      }

      // Store original data in container for reference
      $container.data("original-image-data", {
        src: originalSrc,
        srcset: originalSrcset,
        alt: originalAlt,
      });

      // Preload hover image on first interaction
      $container.one("mouseenter touchstart", () => {
        this.preloadImage(hoverUrl);
      });

      // Mouse enter - show hover image
      $container.on("mouseenter", () => {
        clearTimeout(this.hoverTimeout);

        // Get latest original data (in case LazyLoad updated it)
        const latestOriginalData = $container.data("original-image-data");
        if (latestOriginalData) {
          originalSrc = latestOriginalData.src;
          originalSrcset = latestOriginalData.srcset;
          originalAlt = latestOriginalData.alt;
        }

        // Trigger mouseleave on all other hover-enabled containers
        $(".wc-hover-image-enabled").not($container).trigger("mouseleave");

        // Add swapping class for smooth transition
        $image.addClass("swapping");

        // Small delay for smooth transition
        setTimeout(() => {
          $image.attr("src", hoverUrl);

          if (hoverSrcset) {
            $image.attr("srcset", hoverSrcset);
          }

          if (hoverAlt) {
            $image.attr("alt", hoverAlt);
          }

          // Remove LazyLoad data attributes to prevent re-loading
          if (isLazyLoaded) {
            $image.removeAttr("data-src");
            $image.removeAttr("data-srcset");
          }

          // Remove swapping class
          setTimeout(() => {
            $image.removeClass("swapping");
          }, 50);
        }, 50);
      });

      // Mouse leave - restore original image
      $container.on("mouseleave", () => {
        this.hoverTimeout = setTimeout(() => {
          // Get latest original data before restoring
          const latestOriginalData = $container.data("original-image-data");
          if (latestOriginalData) {
            originalSrc = latestOriginalData.src;
            originalSrcset = latestOriginalData.srcset;
            originalAlt = latestOriginalData.alt;
          }

          // Validate that we're not restoring a base64 placeholder
          if (originalSrc && originalSrc.startsWith("data:image")) {
            console.warn(
              "Attempted to restore base64 placeholder. Using current src instead."
            );
            originalSrc = getActualImageSrc();
            originalSrcset = getActualImageSrcset();

            // Update stored data
            $container.data("original-image-data", {
              src: originalSrc,
              srcset: originalSrcset,
              alt: originalAlt,
            });
          }

          $image.addClass("swapping");

          setTimeout(() => {
            $image.attr("src", originalSrc);
            $image.attr("srcset", originalSrcset);
            $image.attr("alt", originalAlt);

            setTimeout(() => {
              $image.removeClass("swapping");
            }, 50);
          }, 50);
        }, 100);
      });
    }

    /**
     * Check if image is using LazyLoad
     *
     * @param {jQuery} $image - The image element
     * @return {boolean} True if image is lazy loaded
     */
    isImageLazyLoaded($image) {
      // Check for common LazyLoad indicators
      return (
        $image.hasClass("lazyload") ||
        $image.hasClass("lazyloaded") ||
        $image.hasClass("lazyloading") ||
        $image.hasClass("lazy") ||
        $image.attr("data-src") !== undefined ||
        $image.attr("loading") === "lazy"
      );
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
    async handleWishlistClick($button) {
      // Prevent double-click
      if (this.isProcessing) {
        return;
      }

      const productId = parseInt($button.data("product-id"));

      // Validate data availability
      if (!this.validateWishlistData()) {
        return;
      }

      // Save original icon
      const originalIcon = $button.html();

      // Check if already in wishlist
      const isAdded = this.isInWishlist(productId);

      // Set processing state
      this.isProcessing = true;

      // Show spinner
      $button.html(this.getSpinnerIcon());
      $button.addClass("loading");
      $button.prop("disabled", true);

      try {
        // Add or remove from wishlist
        const success = isAdded
          ? await this.removeFromWishlist(productId)
          : await this.addToWishlist(productId);

        if (success) {
          // Update button state
          $button.toggleClass("added");

          // Show success message
          const message = isAdded
            ? blazeProductImageBlock.messages.removed
            : blazeProductImageBlock.messages.added;

          // Update all wishlist buttons for this product
          this.updateWishlistButtons(productId, !isAdded);

          // Update wishlist counter
          this.updateWishlistCounter();

          // Sync with wishlist offcanvas if exists
          this.syncWishlistOffcanvas();
        } else {
        }
      } catch (error) {
        console.error("Wishlist error:", error);
      } finally {
        // Restore icon
        $button.html(originalIcon);
        $button.removeClass("loading");
        $button.prop("disabled", false);
        this.isProcessing = false;
      }
    }

    /**
     * Validate wishlist data availability
     *
     * @return {boolean} True if wishlist data is available
     */
    validateWishlistData() {
      return (
        window.ct_localizations &&
        window.ct_localizations.blc_ext_wish_list &&
        window.ct_localizations.blc_ext_wish_list.list &&
        window.ct_localizations.ajax_url
      );
    }

    /**
     * Check if product is in wishlist
     *
     * @param {number} productId - The product ID
     * @return {boolean} True if product is in wishlist
     */
    isInWishlist(productId) {
      if (!this.validateWishlistData()) {
        return false;
      }

      const items = window.ct_localizations.blc_ext_wish_list.list.items;
      return items.some((item) => item.id === productId);
    }

    /**
     * Add product to wishlist
     *
     * @param {number} productId - The product ID
     * @return {Promise<boolean>} True if successful
     */
    async addToWishlist(productId) {
      if (!this.validateWishlistData()) {
        return false;
      }

      const currentItems = window.ct_localizations.blc_ext_wish_list.list.items;
      const newList = [...currentItems, { id: productId }];

      try {
        const response = await fetch(
          `${window.ct_localizations.ajax_url}?action=blc_ext_wish_list_sync_likes`,
          {
            method: "POST",
            body: JSON.stringify({
              v: 2,
              items: newList,
            }),
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          }
        );

        const result = await response.json();

        if (result.success) {
          // Update global state
          window.ct_localizations.blc_ext_wish_list.list.items = newList;

          // Trigger Blocksy event
          document.dispatchEvent(
            new CustomEvent("blocksy:woocommerce:wish-list-change", {
              detail: { operation: "add", productId: productId },
            })
          );

          return true;
        }

        return false;
      } catch (error) {
        console.error("Add to wishlist error:", error);
        return false;
      }
    }

    /**
     * Remove product from wishlist
     *
     * @param {number} productId - The product ID
     * @return {Promise<boolean>} True if successful
     */
    async removeFromWishlist(productId) {
      if (!this.validateWishlistData()) {
        return false;
      }

      const currentItems = window.ct_localizations.blc_ext_wish_list.list.items;
      const newList = currentItems.filter((item) => item.id !== productId);

      try {
        const response = await fetch(
          `${window.ct_localizations.ajax_url}?action=blc_ext_wish_list_sync_likes`,
          {
            method: "POST",
            body: JSON.stringify({
              v: 2,
              items: newList,
            }),
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          }
        );

        const result = await response.json();

        if (result.success) {
          // Update global state
          window.ct_localizations.blc_ext_wish_list.list.items = newList;

          // Trigger Blocksy event
          document.dispatchEvent(
            new CustomEvent("blocksy:woocommerce:wish-list-change", {
              detail: { operation: "remove", productId: productId },
            })
          );

          return true;
        }

        return false;
      } catch (error) {
        console.error("Remove from wishlist error:", error);
        return false;
      }
    }

    /**
     * Get spinner icon HTML
     *
     * @return {string} Spinner icon HTML
     */
    getSpinnerIcon() {
      return `<svg class="spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <circle cx="12" cy="12" r="10" stroke-width="3" stroke-opacity="0.25"/>
        <path d="M12 2 A10 10 0 0 1 22 12" stroke-width="3" stroke-linecap="round"/>
      </svg>`;
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
     * Sync wishlist state on page load
     */
    async syncWishlistOnLoad() {
      if (!window.ct_localizations || !window.ct_localizations.ajax_url) {
        return;
      }

      try {
        const response = await fetch(
          `${window.ct_localizations.ajax_url}?action=blc_ext_wish_list_get_all_likes`,
          {
            method: "POST",
            headers: {
              Accept: "application/json",
            },
          }
        );

        const result = await response.json();

        if (result.success && result.data) {
          // Update global state
          window.ct_localizations.blc_ext_wish_list = {
            list: result.data.likes,
            user_logged_in: result.data.user_logged_in,
          };

          // Update all button states
          this.updateAllWishlistButtons();

          // Update counter
          this.updateWishlistCounter();
        }
      } catch (error) {
        console.error("Wishlist sync error:", error);
      }
    }

    /**
     * Update all wishlist buttons based on current state
     */
    updateAllWishlistButtons() {
      if (!this.validateWishlistData()) {
        return;
      }

      const items = window.ct_localizations.blc_ext_wish_list.list.items;
      const wishlistIds = items.map((item) => item.id);

      $(".wc-product-image-wishlist-button").each(function () {
        const productId = parseInt($(this).data("product-id"));
        const isInWishlist = wishlistIds.includes(productId);

        if (isInWishlist) {
          $(this).addClass("added");
        } else {
          $(this).removeClass("added");
        }
      });
    }

    /**
     * Update wishlist counter in header
     */
    updateWishlistCounter() {
      if (!this.validateWishlistData()) {
        return;
      }

      const wishlistItems =
        window.ct_localizations.blc_ext_wish_list.list.items;
      const itemCount = wishlistItems.length;

      // Update all counter elements
      document
        .querySelectorAll(".ct-dynamic-count-wishlist")
        .forEach((counter) => {
          counter.textContent = itemCount;
          counter.setAttribute("data-count", itemCount);
        });

      // Add animation class to header wishlist element
      document.querySelectorAll(".ct-header-wishlist").forEach((el) => {
        el.classList.remove("ct-added");

        // Trigger reflow to restart animation
        void el.offsetWidth;

        if (itemCount > 0) {
          el.classList.add("ct-added");
        }
      });
    }

    /**
     * Setup wishlist event listeners
     */
    setupWishlistEventListeners() {
      // Listen for Blocksy wishlist change events
      document.addEventListener(
        "blocksy:woocommerce:wish-list-change",
        (event) => {
          const { operation, productId } = event.detail;

          // Update buttons for this product
          const isAdded = operation === "add";
          this.updateWishlistButtons(productId, isAdded);

          // Update counter
          this.updateWishlistCounter();
        }
      );
    }

    /**
     * Setup dynamic content handling
     * For AJAX-loaded products (infinite scroll, filters, etc.)
     */
    setupDynamicContent() {
      // Listen for WooCommerce events
      $(document.body).on("updated_wc_div", () => {
        this.setupHoverImages();
        this.updateAllWishlistButtons();
      });

      // Listen for custom events from other scripts
      $(document).on("blazeProductsLoaded", () => {
        this.setupHoverImages();
        this.updateAllWishlistButtons();
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
  $(document).ajaxComplete(function (event, xhr, settings) {
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
