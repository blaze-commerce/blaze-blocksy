jQuery( document ).ready(
	function ($) {
		console.log( 'Checkout JS loaded' );

		/**
		 * Auto-scroll to checkout errors
		 * Scrolls the page to the first error message when validation fails
		 */
		function scrollToCheckoutErrors() {
			// Target selectors for error messages (WooCommerce standard classes)
			const errorSelectors = [
				'.woocommerce-error',
				'.woocommerce-NoticeGroup-checkout .woocommerce-error',
				'.woocommerce-notices-wrapper .woocommerce-error',
				'ul.woocommerce-error',
				'.wc-block-components-notice-banner.is-error'
			];

			// Find the first error element
			let errorElement = null;
			for (const selector of errorSelectors) {
				errorElement = $(selector).first();
				if (errorElement.length > 0) {
					break;
				}
			}

			// If an error element is found, scroll to it
			if (errorElement && errorElement.length > 0) {
				const offset = 100; // Offset from top for better visibility
				const scrollPosition = errorElement.offset().top - offset;

				// Smooth scroll to error
				$('html, body').animate({
					scrollTop: scrollPosition
				}, 500, function() {
					// Optional: Add a visual highlight effect
					errorElement.addClass('error-highlight');
					setTimeout(function() {
						errorElement.removeClass('error-highlight');
					}, 2000);
				});

				console.log('Scrolled to checkout error');
			}
		}

		/**
		 * Method 1: Listen for WooCommerce checkout_error event
		 * This is triggered when WooCommerce detects validation errors
		 */
		$(document.body).on('checkout_error', function() {
			console.log('checkout_error event triggered');
			setTimeout(scrollToCheckoutErrors, 100);
		});

		/**
		 * Method 2: MutationObserver for error messages
		 * Watches for error messages being added to the DOM
		 */
		const checkoutForm = document.querySelector('form.woocommerce-checkout');
		if (checkoutForm) {
			const observer = new MutationObserver(function(mutations) {
				mutations.forEach(function(mutation) {
					if (mutation.addedNodes.length > 0) {
						mutation.addedNodes.forEach(function(node) {
							if (node.nodeType === 1) { // Element node
								// Check if the added node or its children contain error messages
								if (
									node.classList &&
									(node.classList.contains('woocommerce-error') ||
									 node.classList.contains('woocommerce-NoticeGroup'))
								) {
									setTimeout(scrollToCheckoutErrors, 100);
								} else if (node.querySelector) {
									const hasError = node.querySelector('.woocommerce-error, .woocommerce-NoticeGroup-checkout');
									if (hasError) {
										setTimeout(scrollToCheckoutErrors, 100);
									}
								}
							}
						});
					}
				});
			});

			// Observe the checkout form and notices wrapper for changes
			observer.observe(checkoutForm, {
				childList: true,
				subtree: true
			});

			// Also observe the notices wrapper if it exists outside the form
			const noticesWrapper = document.querySelector('.woocommerce-notices-wrapper');
			if (noticesWrapper) {
				observer.observe(noticesWrapper, {
					childList: true,
					subtree: true
				});
			}
		}

		/**
		 * Method 3: Listen for form submission and check for errors
		 * Fallback method that checks for errors after form validation
		 */
		$(document.body).on('submit', 'form.woocommerce-checkout', function() {
			setTimeout(function() {
				scrollToCheckoutErrors();
			}, 500);
		});

		/**
		 * Also handle WooCommerce AJAX complete event
		 * For cases where checkout updates via AJAX
		 */
		$(document.body).on('updated_checkout', function() {
			setTimeout(function() {
				const hasErrors = $('.woocommerce-error, .woocommerce-NoticeGroup-checkout .woocommerce-error').length > 0;
				if (hasErrors) {
					scrollToCheckoutErrors();
				}
			}, 100);
		});
	}
);
