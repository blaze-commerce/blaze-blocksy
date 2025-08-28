/**
 * Thank You Page Inline Functionality
 *
 * Extracted from inline script for better caching and maintainability.
 * Provides critical visibility fixes and interactive functionality for
 * the Blaze Commerce thank you page design.
 *
 * @package Blocksy_Child
 * @since 2.0.3
 */

(function ($) {
	'use strict';

	/**
	 * Apply critical visibility fixes for Blaze Commerce elements
	 *
	 * Ensures all thank you page elements are visible immediately,
	 * preventing any flash of invisible content (FOIC).
	 */
	function applyVisibilityFixes() {
		console.log( "🔧 Applying visibility fixes for Blaze Commerce elements" );

		// Define all Blaze Commerce elements that need visibility fixes
		const blazeElements = [
			'.blaze-commerce-thank-you-header',
			'.blaze-commerce-order-summary',
			'.blaze-commerce-main-content',
			'.blaze-commerce-order-details',
			'.blaze-commerce-addresses-section',
			'.blaze-commerce-account-creation'
		].join( ', ' );

		// Apply visibility fixes with important declarations
		$( blazeElements ).css(
			{
				"opacity": "1",
				"visibility": "visible",
				"display": "block"
			}
		);

		console.log( "✅ Blaze Commerce elements visibility fixed" );
	}

	/**
	 * Setup global functions for backward compatibility
	 *
	 * Maintains compatibility with existing code that may reference
	 * these global functions.
	 */
	function setupGlobalFunctions() {
		// Global function for compatibility with existing code
		window.blocksy_child_blaze_commerce_order_summary = function () {
			console.log( "✅ blocksy_child_blaze_commerce_order_summary function called" );
			return true;
		};
	}

	/**
	 * Initialize order summary toggle functionality
	 *
	 * Provides collapsible order summary with smooth animations
	 * and proper button text updates.
	 */
	function initOrderSummaryToggle() {
		$( ".blaze-commerce-summary-toggle" ).on(
			"click",
			function (e) {
				e.preventDefault();

				const $content = $( ".blaze-commerce-summary-content" );
				const $button  = $( this );

				// Animate the toggle with smooth slide effect
				$content.slideToggle(
					300,
					function () {
						// Update button text based on visibility state
						const isVisible = $content.is( ":visible" );
						$button.text( isVisible ? "Hide" : "Show" );

						// Add ARIA attributes for accessibility
						$button.attr( 'aria-expanded', isVisible );
						$content.attr( 'aria-hidden', ! isVisible );
					}
				);
			}
		);
	}

	/**
	 * Initialize all thank you page functionality
	 *
	 * Main initialization function that sets up all interactive
	 * elements and applies necessary fixes.
	 */
	function initThankYouPage() {
		applyVisibilityFixes();
		setupGlobalFunctions();
		initOrderSummaryToggle();

		// Log successful initialization
		console.log( "✅ Blaze Commerce Thank You page inline functionality initialized" );
	}

	// Initialize when DOM is ready
	$( document ).ready(
		function () {
			initThankYouPage();
		}
	);

	// Expose initialization function globally for manual triggering if needed
	window.blazeCommerceThankYou = {
		init: initThankYouPage,
		applyVisibilityFixes: applyVisibilityFixes,
		initOrderSummaryToggle: initOrderSummaryToggle
	};

})( jQuery );
