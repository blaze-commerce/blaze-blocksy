/**
 * FluidCheckout Mobile/Tablet Toggle Functionality
 *
 * Adds collapsible order summary toggle for mobile/tablet viewports
 *
 * @package Blaze_Blocksy
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

    const sidebar = document.querySelector('.fc-sidebar');
    const orderReview = document.getElementById('fc-checkout-order-review');

    if (!sidebar || !orderReview) {
      return;
    }

    // Check if toggle already exists (prevent duplicates)
    if (document.getElementById('order-summary-toggle')) {
      return;
    }

    // Get the total price
    const totalElement = document.querySelector('.fc-sidebar .order-total .amount');
    const totalPrice = totalElement ? totalElement.textContent.trim() : '';

    // Create toggle header HTML
    const headerHTML = `
      <div class="fc-sidebar-header" id="order-summary-toggle">
        <div class="fc-sidebar-header-left">
          <svg class="fc-sidebar-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          <span class="fc-sidebar-header-text">Show order summary</span>
          <svg class="fc-sidebar-header-chevron" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="fc-sidebar-header-total">${totalPrice}</div>
      </div>
    `;

    // Insert header before the order review section
    orderReview.insertAdjacentHTML('beforebegin', headerHTML);

    // Add toggle functionality
    const toggleButton = document.getElementById('order-summary-toggle');
    const chevron = toggleButton.querySelector('.fc-sidebar-header-chevron');
    const toggleText = toggleButton.querySelector('.fc-sidebar-header-text');

    toggleButton.addEventListener('click', function() {
      orderReview.classList.toggle('show');
      chevron.classList.toggle('rotated');

      if (orderReview.classList.contains('show')) {
        toggleText.textContent = 'Hide order summary';
      } else {
        toggleText.textContent = 'Show order summary';
      }
    });
  }

  // Global observer reference
  let placeOrderObserver = null;

  /**
   * Move place order button below payment fields on mobile
   */
  function movePlaceOrderButton() {
    console.log('[Blaze] movePlaceOrderButton() called', {
      viewportWidth: window.innerWidth,
      timestamp: new Date().toISOString()
    });

    // Only run on mobile/tablet viewports
    if (window.innerWidth > 999) {
      console.log('[Blaze] Skipping - desktop viewport');
      return;
    }

    const placeOrderSection = document.querySelector('.fc-place-order__section');
    const paymentSection = document.querySelector('.fc-substep__fields--payment');

    console.log('[Blaze] Elements found:', {
      placeOrderSection: !!placeOrderSection,
      paymentSection: !!paymentSection
    });

    if (!placeOrderSection || !paymentSection) {
      console.log('[Blaze] Missing elements - aborting');
      return;
    }

    // Check if already in correct location (verify actual parent, not just data attribute)
    const paymentParent = paymentSection.parentElement;
    const isInCorrectLocation = placeOrderSection.parentElement === paymentParent;

    console.log('[Blaze] Current state:', {
      placeOrderParent: placeOrderSection.parentElement.className,
      paymentParent: paymentParent.className,
      isInCorrectLocation: isInCorrectLocation,
      dataAttribute: placeOrderSection.dataset.movedToPayment
    });

    if (isInCorrectLocation && placeOrderSection.dataset.movedToPayment === 'true') {
      console.log('[Blaze] Already in correct location - skipping');
      return;
    }

    console.log('[Blaze] Moving place order section...');

    // Mark as moved
    placeOrderSection.dataset.movedToPayment = 'true';

    // Move place order section after payment section
    paymentParent.insertBefore(placeOrderSection, paymentSection.nextSibling);

    console.log('[Blaze] Move complete!', {
      newParent: placeOrderSection.parentElement.className
    });
  }

  /**
   * Set up MutationObserver to watch for Place Order button being moved back
   */
  function setupPlaceOrderObserver() {
    // Only run on mobile/tablet viewports
    if (window.innerWidth > 999) {
      return;
    }

    // Disconnect existing observer if any
    if (placeOrderObserver) {
      placeOrderObserver.disconnect();
    }

    const orderReviewInner = document.querySelector('.fc-checkout-order-review__inner');
    if (!orderReviewInner) {
      console.log('[Blaze] Order review inner not found for observer');
      return;
    }

    console.log('[Blaze] Setting up MutationObserver to watch order review section');

    placeOrderObserver = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
          // Check if Place Order button was added back to order review
          const placeOrderSection = document.querySelector('.fc-checkout-order-review__inner .fc-place-order__section');

          if (placeOrderSection) {
            console.log('[Blaze] MutationObserver detected Place Order button in order summary - moving it!');

            // Remove the moved marker since it's been reset
            delete placeOrderSection.dataset.movedToPayment;

            // Move it back to correct location
            movePlaceOrderButton();
          }
        }
      });
    });

    // Observe the order review inner section
    placeOrderObserver.observe(orderReviewInner, {
      childList: true,
      subtree: true
    });

    console.log('[Blaze] MutationObserver active');
  }

  /**
   * Disconnect the Place Order observer
   */
  function disconnectPlaceOrderObserver() {
    if (placeOrderObserver) {
      placeOrderObserver.disconnect();
      placeOrderObserver = null;
      console.log('[Blaze] MutationObserver disconnected');
    }
  }

  /**
   * Restore place order button to sidebar on desktop
   */
  function restorePlaceOrderButton() {
    const placeOrderSection = document.querySelector('.fc-place-order__section');
    const orderReviewInner = document.querySelector('.fc-checkout-order-review__inner');

    if (!placeOrderSection || !orderReviewInner) {
      return;
    }

    // Disconnect observer on desktop
    disconnectPlaceOrderObserver();

    // Only restore if it was moved
    if (placeOrderSection.dataset.movedToPayment === 'true') {
      // Remove the marker
      delete placeOrderSection.dataset.movedToPayment;

      // Move back to sidebar
      orderReviewInner.appendChild(placeOrderSection);
    }
  }

  /**
   * Re-initialize on window resize
   */
  let resizeTimeout;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
      // Remove existing toggle if viewport is now desktop
      if (window.innerWidth > 999) {
        const existingToggle = document.getElementById('order-summary-toggle');
        if (existingToggle) {
          existingToggle.remove();
        }
        // Show order review on desktop
        const orderReview = document.getElementById('fc-checkout-order-review');
        if (orderReview) {
          orderReview.classList.remove('show');
          orderReview.style.display = '';
        }
        // Restore place order button to sidebar
        restorePlaceOrderButton();
      } else {
        initOrderSummaryToggle();
        movePlaceOrderButton();
        setupPlaceOrderObserver();
      }
    }, 250);
  });

  /**
   * Initialize on document ready and after FluidCheckout updates
   */
  $(document).ready(function() {
    console.log('[Blaze] Document ready - initializing checkout mobile customizations');
    initOrderSummaryToggle();
    movePlaceOrderButton();
    setupPlaceOrderObserver();

    // Re-initialize after FluidCheckout AJAX updates
    $(document.body).on('updated_checkout', function() {
      console.log('[Blaze] FluidCheckout updated_checkout event fired');
      initOrderSummaryToggle();

      // Delay execution to ensure FluidCheckout finishes its DOM manipulation
      setTimeout(function() {
        console.log('[Blaze] Executing delayed movePlaceOrderButton after updated_checkout');
        movePlaceOrderButton();
        setupPlaceOrderObserver();
      }, 100);
    });
  });

})(jQuery);
