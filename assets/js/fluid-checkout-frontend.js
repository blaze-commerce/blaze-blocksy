/**
 * Fluid Checkout Frontend Script
 *
 * Applies customizer settings to the checkout page on the frontend.
 *
 * @package Blocksy_Child
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  /**
   * Reposition FluidCheckout progress bar as first element in fc-inside container
   *
   * This ensures the progress bar appears at the top of the checkout flow
   * for better user experience and visual hierarchy.
   */
  function repositionProgressBar() {
    // Find the progress bar element
    const progressBar = document.querySelector(".fc-progress-bar");

    // Find the fc-inside container
    const fcInside = document.querySelector(".fc-inside");

    // Verify both elements exist
    if (!progressBar || !fcInside) {
      return;
    }

    // Check if progress bar is already the first child of fc-inside
    if (fcInside.firstElementChild === progressBar) {
      return; // Already in correct position
    }

    // Move progress bar to be the first child of fc-inside
    // Using prepend ensures it becomes the first element
    fcInside.prepend(progressBar);
  }

  /**
   * Replace the "My contact" heading text with custom text from customizer
   */
  function updateMyContactHeading() {
    // Check if settings are available
    if (typeof blocksyFluidCheckoutSettings === "undefined") {
      return;
    }

    const customText = blocksyFluidCheckoutSettings.myContactHeadingText;

    // If custom text is empty or same as default, don't do anything
    if (!customText || customText === "My contact") {
      return;
    }

    // Find the "My contact" heading
    // Try multiple selectors to ensure we find it
    const selectors = [
      '.fc-step__substep-title:contains("My contact")',
      '.fc-step__substep-title:contains("My Contact")',
      'h3.fc-step__substep-title:contains("My contact")',
      'h3.fc-step__substep-title:contains("My Contact")',
    ];

    let contactHeading = null;

    for (let i = 0; i < selectors.length; i++) {
      contactHeading = $(selectors[i]);
      if (contactHeading.length > 0) {
        break;
      }
    }

    // Update the heading text if found
    if (contactHeading && contactHeading.length > 0) {
      contactHeading.text(customText);
    }
  }

  /**
   * Initialize on document ready
   */
  $(document).ready(function () {
    // Reposition progress bar as first element
    repositionProgressBar();

    // Update custom heading text
    updateMyContactHeading();
  });

  /**
   * Re-run when Fluid Checkout updates the checkout
   * Fluid Checkout uses AJAX to update checkout sections
   */
  $(document.body).on("updated_checkout", function () {
    // Reposition progress bar after AJAX updates
    repositionProgressBar();

    // Update custom heading text
    updateMyContactHeading();
  });

  /**
   * Also listen for any DOM mutations in case elements are added dynamically
   */
  if (typeof MutationObserver !== "undefined") {
    let checkoutObserver = null;

    const initCheckoutObserver = function () {
      // Disconnect existing observer if any
      if (checkoutObserver) {
        checkoutObserver.disconnect();
      }

      checkoutObserver = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          if (mutation.addedNodes.length > 0) {
            // Reposition progress bar if DOM changes
            repositionProgressBar();

            // Update custom heading text
            updateMyContactHeading();
          }
        });
      });

      // Observe the checkout form for changes
      const checkoutForm = document.querySelector(".woocommerce-checkout");
      if (checkoutForm) {
        checkoutObserver.observe(checkoutForm, {
          childList: true,
          subtree: true,
        });
      }
    };

    // Initialize observer
    initCheckoutObserver();

    // Cleanup observer on page unload to prevent memory leaks
    window.addEventListener("beforeunload", function () {
      if (checkoutObserver) {
        checkoutObserver.disconnect();
        checkoutObserver = null;
      }
    });
  }
})(jQuery);
