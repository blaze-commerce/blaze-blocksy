/**
 * Product Information — off-canvas panel, tabs, shipping calculator.
 *
 * No jQuery. Vanilla JS + fetch().
 *
 * @package Blocksy_Child
 */
( function () {
	'use strict';

	const panel = document.getElementById( 'product-info-panel' );
	if ( ! panel ) return;

	const tabButtons = panel.querySelectorAll( '.bc-info-tab' );
	const tabPanes   = panel.querySelectorAll( '.bc-tab-pane' );
	const calcForm   = panel.querySelector( '.bc-shipping-calc' );
	const results    = panel.querySelector( '.bc-shipping-results' );
	const countryEl  = panel.querySelector( '#bc-calc-country' );
	const stateEl    = panel.querySelector( '#bc-calc-state' );
	const stateField = panel.querySelector( '.bc-field-state' );

	var PANEL_SEL = '#product-info-panel';

	/* ── Click router (single handler, same pattern as wishlist) ── */
	document.addEventListener( 'click', function ( e ) {

		// 1. Trigger links on the PDP — open panel.
		var trigger = e.target.closest( '[data-panel="product-info-panel"]' );
		if ( trigger && ! trigger.closest( PANEL_SEL ) ) {
			e.preventDefault();
			e.stopImmediatePropagation();
			var targetTab = trigger.dataset.tab;
			if ( targetTab ) activateTab( targetTab );
			openPanel();
			return;
		}

		// 2. Close button inside panel.
		if ( e.target.closest( PANEL_SEL + ' .ct-toggle-close' ) ) {
			e.preventDefault();
			closePanel();
			return;
		}

		// 3. Tab buttons inside panel — switch tab, don't close.
		var tabBtn = e.target.closest( PANEL_SEL + ' .bc-info-tab' );
		if ( tabBtn ) {
			e.stopImmediatePropagation();
			activateTab( tabBtn.dataset.tab );
			return;
		}

		// 4. Any other click inside panel inner — stop Blocksy from closing.
		if ( e.target.closest( PANEL_SEL + ' .ct-panel-inner' ) ) {
			e.stopImmediatePropagation();
			return;
		}

		// 5. Click on backdrop (the panel element itself) — close.
		if ( e.target.closest( PANEL_SEL ) && e.target === panel ) {
			closePanel();
			return;
		}
	} );

	function openPanel() {
		panel.removeAttribute( 'inert' );

		if ( typeof ctEvents !== 'undefined' ) {
			ctEvents.trigger( 'ct:overlay:handle-click', {
				event   : new Event( 'click' ),
				options : {
					container    : panel,
					clickOutside : true,
					focus        : true,
					isModal      : false,
				},
			} );
		} else {
			panel.classList.add( 'active' );
			document.body.style.overflow = 'hidden';
		}
	}

	function closePanel() {
		panel.classList.remove( 'active' );
		panel.setAttribute( 'inert', '' );
		document.body.style.overflow = '';
	}

	/* ── ESC key ─────────────────────────────────────────────── */
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && ! panel.hasAttribute( 'inert' ) ) {
			closePanel();
		}
	} );

	function activateTab( slug ) {
		tabButtons.forEach( function ( b ) {
			const isActive = b.dataset.tab === slug;
			b.classList.toggle( 'active', isActive );
			b.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
		} );
		tabPanes.forEach( function ( p ) {
			const isActive = p.id === 'bc-tab-' + slug;
			p.classList.toggle( 'active', isActive );
			if ( isActive ) {
				p.removeAttribute( 'hidden' );
			} else {
				p.setAttribute( 'hidden', '' );
			}
		} );
	}

	/* ── Shipping calc: country → states ─────────────────────── */
	if ( countryEl ) {
		countryEl.addEventListener( 'change', function () {
			const country = this.value;

			if ( ! country ) {
				stateEl.innerHTML = '<option value="">Select a state&hellip;</option>';
				stateField.style.display = '';
				return;
			}

			fetch( bcProductInfo.ajaxUrl, {
				method : 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body   : new URLSearchParams( {
					action  : 'bc_get_states',
					nonce   : bcProductInfo.nonce,
					country : country,
				} ),
			} )
			.then( function ( r ) {
				if ( ! r.ok ) {
					throw new Error( 'States fetch HTTP ' + r.status );
				}
				return r.json();
			} )
			.then( function ( data ) {
				if ( data.success && Object.keys( data.data ).length ) {
					var opts = '<option value="">Select a state&hellip;</option>';
					for ( var code in data.data ) {
						opts += '<option value="' + code + '">' + data.data[ code ] + '</option>';
					}
					stateEl.innerHTML = opts;
					stateField.style.display = '';
				} else {
					stateEl.innerHTML = '<option value="">N/A</option>';
					stateField.style.display = 'none';
				}
			} )
			.catch( function ( err ) {
				/* Network / parse failure — fail open: hide state field so the
				   user can still complete the shipping calculator with country
				   only. Mirrors the calc-submit fetch fallback further below.
				   Audit P0 fix 2026-05-08. */
				if ( window.console && console.warn ) {
					console.warn( '[bc-product-info] States fetch failed:', err );
				}
				stateEl.innerHTML = '<option value="">N/A</option>';
				stateField.style.display = 'none';
			} );

			/* Auto-trigger states on page load if a country is pre-selected */
		} );

		/* Load states for pre-selected country (e.g. AU). */
		if ( countryEl.value ) {
			countryEl.dispatchEvent( new Event( 'change' ) );
		}
	}

	/* ── Shipping calc: submit ───────────────────────────────── */
	if ( calcForm ) {
		calcForm.addEventListener( 'submit', function ( e ) {
			e.preventDefault();

			var submitBtn = calcForm.querySelector( '.bc-calc-submit' );
			calcForm.classList.add( 'is-loading' );
			submitBtn.disabled = true;
			results.innerHTML  = '';

			fetch( bcProductInfo.ajaxUrl, {
				method : 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body   : new URLSearchParams( {
					action     : 'bc_calculate_shipping',
					nonce      : bcProductInfo.nonce,
					product_id : calcForm.dataset.productId,
					country    : calcForm.querySelector( '[name="country"]' ).value,
					state      : calcForm.querySelector( '[name="state"]' ).value,
					postcode   : calcForm.querySelector( '[name="postcode"]' ).value,
				} ),
			} )
			.then( function ( r ) { return r.json(); } )
			.then( function ( data ) {
				calcForm.classList.remove( 'is-loading' );
				submitBtn.disabled = false;

				if ( data.success ) {
					var html = '<ul class="bc-shipping-rates">';
					data.data.forEach( function ( rate ) {
						html += '<li class="bc-shipping-rate">';
						html += '<span class="bc-rate-label">' + rate.label + '</span>';
						html += '<span class="bc-rate-cost">' + rate.cost + '</span>';
						html += '</li>';
					} );
					html += '</ul>';
					results.innerHTML = html;
				} else {
					results.innerHTML = '<p class="bc-shipping-error">' +
						( data.data && data.data.message ? data.data.message : 'Unable to calculate shipping.' ) +
						'</p>';
				}
			} )
			.catch( function () {
				calcForm.classList.remove( 'is-loading' );
				submitBtn.disabled = false;
				results.innerHTML = '<p class="bc-shipping-error">Something went wrong. Please try again.</p>';
			} );
		} );
	}
} )();
