/**
 * Checkout Error Auto-scroll.
 *
 * Brings the first error message into view whenever a WooCommerce checkout
 * error appears, so customers see what they need to fix instead of clicking
 * "Place order" repeatedly with the notice scrolled out of view.
 *
 * Triggers:
 *   - DOMContentLoaded: server-rendered errors already in the DOM.
 *   - jQuery 'checkout_error' on document.body: AJAX submit failures.
 *
 * Targets, in priority order:
 *   - first .woocommerce-error (top-level notice)
 *   - first .woocommerce-invalid (per-field validation marker)
 */
( function () {
	'use strict';

	var STICKY_OFFSET_FALLBACK = 80;

	function getStickyOffset() {
		var raw = getComputedStyle( document.documentElement )
			.getPropertyValue( '--header-sticky-height' )
			.trim();
		var parsed = parseInt( raw, 10 );
		return isNaN( parsed ) ? STICKY_OFFSET_FALLBACK : parsed;
	}

	function findErrorTarget() {
		var notice = document.querySelector(
			'.woocommerce-error, .wc-block-components-notice-banner.is-error'
		);
		if ( notice ) {
			return notice;
		}
		var invalidField = document.querySelector( '.woocommerce-invalid' );
		return invalidField || null;
	}

	function scrollToTarget( el ) {
		if ( ! el ) {
			return;
		}

		var prefersReducedMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches;

		var rect = el.getBoundingClientRect();
		var top = rect.top + window.pageYOffset - getStickyOffset() - 16;

		window.scrollTo( {
			top: Math.max( 0, top ),
			behavior: prefersReducedMotion ? 'auto' : 'smooth',
		} );

		// Move focus so screen readers announce the error region.
		if ( ! el.hasAttribute( 'tabindex' ) ) {
			el.setAttribute( 'tabindex', '-1' );
		}
		try {
			el.focus( { preventScroll: true } );
		} catch ( e ) {
			el.focus();
		}
	}

	function handleError() {
		// Wait one frame so WC has finished injecting the notice.
		window.requestAnimationFrame( function () {
			scrollToTarget( findErrorTarget() );
		} );
	}

	function init() {
		// Server-rendered errors already on page.
		if ( findErrorTarget() ) {
			handleError();
		}

		// AJAX checkout failures.
		if ( typeof jQuery !== 'undefined' ) {
			jQuery( document.body ).on( 'checkout_error', handleError );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
