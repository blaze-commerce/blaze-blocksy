/**
 * Multiple Gallery Responsive JavaScript
 *
 * Handles dynamic behavior for responsive gallery:
 * - Prevents slider initialization on desktop
 * - Re-initializes slider on resize when crossing breakpoint
 */

(function ($) {
  "use strict";

  // Configuration
  const config = window.blazeGalleryConfig || {
    desktopBreakpoint: 992,
    enableDebug: false,
  };

  // Debug logger
  const log = (...args) => {
    if (config.enableDebug) {
      console.log("[Blaze Gallery]", ...args);
    }
  };

  // Check if desktop
  const isDesktop = () => window.innerWidth >= config.desktopBreakpoint;

  // Main gallery handler
  class BlazeGalleryHandler {
    constructor() {
      this.$gallery = $(
        ".woocommerce-product-gallery .blaze-responsive-gallery"
      );
      this.currentMode = null;
      this.flexyInstance = null;

      if (this.$gallery.length === 0) {
        log("Gallery not found");
        return;
      }

      this.init();
    }

    init() {
      log("Initializing gallery handler");

      // Set initial mode
      this.updateMode();

      // Handle window resize
      let resizeTimer;
      $(window).on("resize", () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
          this.handleResize();
        }, 250);
      });

      // Prevent flexy initialization on desktop
      if (isDesktop()) {
        this.disableSlider();
      }
    }

    updateMode() {
      const newMode = isDesktop() ? "desktop" : "mobile";

      if (this.currentMode !== newMode) {
        log("Mode changed:", this.currentMode, "->", newMode);
        this.currentMode = newMode;

        if (newMode === "desktop") {
          this.enableStackedMode();
        } else {
          this.enableSliderMode();
        }
      }
    }

    handleResize() {
      log("Window resized");
      this.updateMode();
    }

    disableSlider() {
      log("Disabling slider for desktop");

      // Remove data-flexy attribute to prevent initialization
      this.$gallery.removeAttr("data-flexy");

      // If flexy instance exists, destroy it
      if (this.$gallery[0] && this.$gallery[0].flexy) {
        log("Destroying existing flexy instance");
        // Flexy doesn't have destroy method, so we just remove reference
        delete this.$gallery[0].flexy;
      }
    }

    enableStackedMode() {
      log("Enabling stacked mode");

      this.disableSlider();

      // Add stacked mode class
      this.$gallery.addClass("gallery-stacked-mode");
      this.$gallery.removeClass("gallery-slider-mode");

      // Reset any inline transforms
      this.$gallery.find(".flexy-items").css("transform", "");
      this.$gallery.find(".flexy-item").css("transform", "");
    }

    enableSliderMode() {
      log("Enabling slider mode");

      // Add slider mode class
      this.$gallery.addClass("gallery-slider-mode");
      this.$gallery.removeClass("gallery-stacked-mode");

      // Re-enable flexy if needed
      if (!this.$gallery.attr("data-flexy")) {
        this.$gallery.attr("data-flexy", "no");

        // Trigger flexy initialization
        // Blocksy's flexy initializes on interaction, so we trigger it
        if (window.ctEvents) {
          window.ctEvents.trigger("ct:flexy:init");
        }
      }
    }
  }

  // Initialize when DOM is ready
  $(document).ready(function () {
    log("DOM ready, initializing...");

    // Wait for Blocksy to load
    setTimeout(() => {
      new BlazeGalleryHandler();
    }, 100);
  });

  // Also handle AJAX product updates (for variations)
  $(document).on("found_variation", function () {
    log("Variation changed, reinitializing...");
    setTimeout(() => {
      new BlazeGalleryHandler();
    }, 300);
  });
})(jQuery);
