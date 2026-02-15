/**
 * FluidCheckout Mobile/Tablet Toggle Functionality
 *
 * Adds collapsible order summary toggle for mobile/tablet viewports.
 * Uses FC's placeholder system for place order button movement.
 *
 * @package Blaze_Blocksy
 * @since 1.41.0
 */

(function($) {
  'use strict';

  /**
   * Initialize order summary toggle
   */
  function initOrderSummaryToggle() {
    // Only run on mobile/tablet viewports
    if (window.innerWidth > 999) {
      return;
    }

    var sidebar = document.querySelector('.fc-sidebar');
    var orderReview = document.getElementById('fc-checkout-order-review');

    if (!sidebar || !orderReview) {
      return;
    }

    // Check if toggle already exists (prevent duplicates)
    if (document.getElementById('order-summary-toggle')) {
      return;
    }

    // Remove FC's flyout attributes to prevent conflict with our toggle
    orderReview.removeAttribute('data-flyout');
    orderReview.removeAttribute('data-flyout-order-review');
    orderReview.removeAttribute('data-flyout-open-animation-class');
    orderReview.removeAttribute('data-flyout-close-animation-class');

    // Get the total price
    var totalElement = document.querySelector('.fc-sidebar .order-total .amount');
    var totalPrice = totalElement ? totalElement.textContent.trim() : '';

    // Create toggle header HTML
    var headerHTML =
      '<div class="fc-sidebar-header" id="order-summary-toggle">' +
        '<div class="fc-sidebar-header-left">' +
          '<div class="fc-sidebar-header-icon">' +
            '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">' +
              '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>' +
            '</svg>' +
          '</div>' +
          '<span class="fc-sidebar-header-text">Show order summary</span>' +
          '<svg class="fc-sidebar-header-chevron" fill="currentColor" viewBox="0 0 20 20">' +
            '<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>' +
          '</svg>' +
        '</div>' +
        '<div class="fc-sidebar-header-total">' + totalPrice + '</div>' +
      '</div>';

    // Insert header before the order review section
    orderReview.insertAdjacentHTML('beforebegin', headerHTML);

    // Add toggle functionality
    var toggleButton = document.getElementById('order-summary-toggle');
    var chevron = toggleButton.querySelector('.fc-sidebar-header-chevron');
    var toggleText = toggleButton.querySelector('.fc-sidebar-header-text');

    toggleButton.addEventListener('click', function() {
      orderReview.classList.toggle('show');
      toggleButton.classList.toggle('expanded');
      chevron.classList.toggle('rotated');

      if (orderReview.classList.contains('show')) {
        toggleText.textContent = 'Hide order summary';
      } else {
        toggleText.textContent = 'Show order summary';
      }
    });
  }

  /**
   * Move place order button from sidebar to payment step on mobile/tablet.
   * FC's JS moves the button into the order review sidebar on small viewports,
   * but our collapsed toggle hides it. Move it to the Payment step instead.
   */
  function movePlaceOrderButton() {
    if (window.innerWidth > 999) {
      return;
    }

    var placeOrderSection = document.querySelector('.fc-place-order__section');
    if (!placeOrderSection) {
      return;
    }

    // Only move if currently inside the sidebar (where FC's JS puts it)
    if (!placeOrderSection.closest('.fc-sidebar')) {
      return;
    }

    // Find the Payment step's placeholder (FC leaves one where it removed the button)
    var placeholders = document.querySelectorAll('.fc-place-order__section-placeholder');
    var stepPlaceholder = null;
    for (var i = 0; i < placeholders.length; i++) {
      if (placeholders[i].closest('.fc-checkout-step')) {
        stepPlaceholder = placeholders[i];
        break;
      }
    }

    if (stepPlaceholder) {
      // Insert after the placeholder inside the Payment step
      stepPlaceholder.parentNode.insertBefore(placeOrderSection, stepPlaceholder.nextSibling);
    }
  }

  /**
   * Watch the order review sidebar for FC re-inserting the place order button.
   * FC moves it back on every updated_checkout, so we use a MutationObserver
   * to immediately move it back to the Payment step — eliminating flicker.
   */
  var placeOrderObserver = null;

  function startPlaceOrderObserver() {
    if (window.innerWidth > 999) {
      stopPlaceOrderObserver();
      return;
    }

    // Don't create duplicate observers
    if (placeOrderObserver) {
      return;
    }

    var orderReviewInner = document.querySelector('.fc-checkout-order-review__inner');
    if (!orderReviewInner) {
      return;
    }

    placeOrderObserver = new MutationObserver(function(mutations) {
      for (var i = 0; i < mutations.length; i++) {
        for (var j = 0; j < mutations[i].addedNodes.length; j++) {
          var node = mutations[i].addedNodes[j];
          if (node.nodeType === 1 && node.classList && node.classList.contains('fc-place-order__section')) {
            // FC just moved the Place Order button back to sidebar — move it out immediately
            movePlaceOrderButton();
            return;
          }
        }
      }
    });

    placeOrderObserver.observe(orderReviewInner, { childList: true });
  }

  function stopPlaceOrderObserver() {
    if (placeOrderObserver) {
      placeOrderObserver.disconnect();
      placeOrderObserver = null;
    }
  }

  /**
   * Re-initialize on window resize
   */
  var resizeTimeout;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
      // Remove existing toggle if viewport is now desktop
      if (window.innerWidth > 999) {
        var existingToggle = document.getElementById('order-summary-toggle');
        if (existingToggle) {
          existingToggle.remove();
        }
        // Show order review on desktop
        var orderReview = document.getElementById('fc-checkout-order-review');
        if (orderReview) {
          orderReview.classList.remove('show');
          orderReview.style.display = '';
        }
        stopPlaceOrderObserver();
      } else {
        initOrderSummaryToggle();
        movePlaceOrderButton();
        startPlaceOrderObserver();
      }
    }, 250);
  });

  /**
   * Initialize on document ready and after FluidCheckout updates
   */
  $(document).ready(function() {
    initOrderSummaryToggle();
    movePlaceOrderButton();
    startPlaceOrderObserver();

    // Re-initialize after FluidCheckout AJAX updates
    $(document.body).on('updated_checkout', function() {
      initOrderSummaryToggle();

      // Move immediately — the MutationObserver will also catch FC's re-insertion
      movePlaceOrderButton();

      // Re-start observer in case FC rebuilt the DOM
      stopPlaceOrderObserver();
      startPlaceOrderObserver();
    });
  });

})(jQuery);
