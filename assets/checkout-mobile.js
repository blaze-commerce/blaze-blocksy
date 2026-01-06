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
      } else {
        initOrderSummaryToggle();
      }
    }, 250);
  });

  /**
   * Initialize on document ready and after FluidCheckout updates
   */
  $(document).ready(function() {
    initOrderSummaryToggle();

    // Re-initialize after FluidCheckout AJAX updates
    $(document.body).on('updated_checkout', initOrderSummaryToggle);
  });

})(jQuery);
