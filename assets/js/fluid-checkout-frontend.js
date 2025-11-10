/**
 * Fluid Checkout Frontend Script
 *
 * Applies customizer settings to the checkout page on the frontend.
 *
 * @package Blocksy_Child
 * @since 1.0.0
 */

(function ($) {
	'use strict';

	/**
	 * Replace the "My contact" heading text with custom text from customizer
	 */
	function updateMyContactHeading() {
		// Check if settings are available
		if (typeof blocksyFluidCheckoutSettings === 'undefined') {
			return;
		}

		const customText = blocksyFluidCheckoutSettings.myContactHeadingText;
		
		// If custom text is empty or same as default, don't do anything
		if (!customText || customText === 'My contact') {
			return;
		}

		// Find the "My contact" heading
		// Try multiple selectors to ensure we find it
		const selectors = [
			'.fc-step__substep-title:contains("My contact")',
			'.fc-step__substep-title:contains("My Contact")',
			'h3.fc-step__substep-title:contains("My contact")',
			'h3.fc-step__substep-title:contains("My Contact")'
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
		updateMyContactHeading();
	});

	/**
	 * Re-run when Fluid Checkout updates the checkout
	 * Fluid Checkout uses AJAX to update checkout sections
	 */
	$(document.body).on('updated_checkout', function () {
		updateMyContactHeading();
	});

	/**
	 * Also listen for any DOM mutations in case the heading is added dynamically
	 */
	if (typeof MutationObserver !== 'undefined') {
		const observer = new MutationObserver(function (mutations) {
			mutations.forEach(function (mutation) {
				if (mutation.addedNodes.length > 0) {
					updateMyContactHeading();
				}
			});
		});

		// Observe the checkout form for changes
		const checkoutForm = document.querySelector('.woocommerce-checkout');
		if (checkoutForm) {
			observer.observe(checkoutForm, {
				childList: true,
				subtree: true
			});
		}
	}

})(jQuery);

