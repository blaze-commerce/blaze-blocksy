/**
 * Checkout Order Summary — Dynamic toggle text + WT coupon wrapper relocation.
 * Tasks: CU-86ewn0gw9, CU-86exe3dcv
 *
 * 1. Toggle text: watches FC Pro collapsible state (.is-collapsed) and
 *    swaps "Show Order Summary" / "Hide Order Summary" on the toggle.
 * 2. WT coupon wrapper: moves <div class="wt_coupon_wrapper"> (rendered by
 *    WT Smart Coupon Pro at the top of the checkout) to immediately after
 *    <tr class="coupon-code-form"> inside the FC Pro order summary so both
 *    coupon UIs sit together.
 */
( function () {
	'use strict';

	var observer = null;

	function initToggleText() {
		var content = document.querySelector( '.fc-checkout-order-review-collapsible__content' );
		var title   = document.querySelector( '.fc-checkout-order-review-collapsible__title' );

		if ( ! title ) {
			return;
		}

		function updateText() {
			var el = document.querySelector( '.fc-checkout-order-review-collapsible__content' );
			if ( ! el ) {
				return;
			}
			if ( el.classList.contains( 'is-collapsed' ) ) {
				title.textContent = 'Show Order Summary';
			} else {
				title.textContent = 'Hide Order Summary';
			}
		}

		updateText();

		if ( observer ) {
			observer.disconnect();
		}

		observer = new MutationObserver( updateText );
		var opts = { attributes: true, attributeFilter: [ 'class' ] };
		if ( content ) {
			observer.observe( content, opts );
		}
	}

	function moveWtCouponWrapper() {
		// Find the original wrapper (server-rendered at top of <form>, NOT
		// inside #order_review). We never move this one — order_review is
		// replaced on every WC `updated_checkout` AJAX, which would wipe
		// any node we placed inside it. Instead, clone the original into
		// the order summary and re-clone after each AJAX refresh.
		var all = document.querySelectorAll( '.wt_coupon_wrapper' );
		if ( ! all.length ) {
			return;
		}

		var original = null;
		for ( var i = 0; i < all.length; i++ ) {
			if ( ! all[ i ].closest( '#order_review' ) && ! all[ i ].classList.contains( 'bc-wt-coupon-clone' ) ) {
				original = all[ i ];
				break;
			}
		}
		if ( ! original ) {
			return;
		}

		// Hide the original at its server-rendered position.
		original.style.display = 'none';

		// FC Pro can render the order summary in two places (sidebar +
		// before_checkout_steps mobile collapsible). Pick the first
		// visible coupon-code-form, falling back to the last.
		var forms = document.querySelectorAll( 'tr.coupon-code-form' );
		if ( ! forms.length ) {
			return;
		}
		var form = null;
		for ( var j = 0; j < forms.length; j++ ) {
			if ( forms[ j ].offsetParent !== null ) {
				form = forms[ j ];
				break;
			}
		}
		if ( ! form ) {
			form = forms[ forms.length - 1 ];
		}

		// Remove any stale rows left from a previous run, then insert fresh.
		var stale = document.querySelectorAll( '.bc-wt-coupon-row' );
		for ( var k = 0; k < stale.length; k++ ) {
			stale[ k ].parentNode.removeChild( stale[ k ] );
		}

		// Wrap the clone in <tr><td colspan="2"> so it sits inside <tfoot>
		// as valid HTML, matching the structure of <tr class="coupon-code-form">.
		var clone = original.cloneNode( true );
		clone.style.display = '';
		clone.classList.add( 'bc-wt-coupon-clone' );

		// [CU-86exe3dcv] Strip title attributes — prevents native browser "Click to apply coupon"
		// tooltip from appearing (coupon is already applied on checkout).
		clone.removeAttribute( 'title' );
		clone.querySelectorAll( '[title]' ).forEach( function( el ) { el.removeAttribute( 'title' ); } );

		var row = document.createElement( 'tr' );
		row.className = 'bc-wt-coupon-row';
		var cell = document.createElement( 'td' );
		cell.setAttribute( 'colspan', '2' );
		cell.appendChild( clone );
		row.appendChild( cell );

		form.insertAdjacentElement( 'afterend', row );
	}

	function init() {
		initToggleText();
		moveWtCouponWrapper();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	// Re-run after WooCommerce AJAX cart updates (FC Pro re-renders the
	// order review fragment, which can detach our prior DOM moves).
	if ( typeof jQuery !== 'undefined' ) {
		jQuery( document.body ).on( 'updated_checkout', init );
	}
} )();
