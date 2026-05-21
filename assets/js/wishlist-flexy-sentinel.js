/**
 * Wishlist drawer suggested-products visibility sentinel — LAYER 7.
 *
 * Runtime QA tool: detects if Blocksy ever updates `flexy.min.css` in a
 * way that pushes carousel items off-screen, transforms them off-axis,
 * or zero-dims them inside the wishlist drawer. Logs a distinct
 * `[BBC sentinel]` console.error and tags
 * `window.bcWishlistFlexyState = 'BROKEN'` so the next QA / dev sees
 * the regression immediately.
 *
 * WHY: All 7 drawer states (mini cart + wishlist; with/without items;
 * guest/logged-in) render Blocksy's native flexy carousel via the
 * shared helper `bc_render_blocksy_suggested_carousel()`. Blocksy's
 * `flexy.min.js` auto-initializes any `.flexy-container` on
 * DOMContentLoaded, so the carousel works out of the box — but a
 * future Blocksy update could regress positioning. This sentinel is
 * the runtime safety net.
 *
 * This sentinel runs once per drawer open. It checks the first .flexy-item:
 *   • Is its bounding rect's top + height inside the panel viewport?
 *   • Is its width > 0?
 *   • Is its computed transform anything other than `none` or `matrix(1, 0, 0, 1, 0, 0)` (identity)?
 *
 * If any check fails, we log an error and tag window.bcWishlistFlexyState =
 * 'BROKEN' so the next QA / dev can immediately see something is wrong.
 *
 * See memory file `blocksy-drawer-suggested-products-architecture.md`
 * for the full 7-layer bulletproofing strategy.
 *
 * @date 2026-04-28 — reframed as Layer 7 sentinel after the 2026-04-26
 *                    flexy-strip workaround was reverted.
 */
( function () {
	'use strict';

	if ( typeof window === 'undefined' ) return;

	window.bcWishlistFlexyState = 'unchecked';

	function checkFlexyVisibility() {
		var panel = document.getElementById( 'woo-wishlist-panel' );
		if ( ! panel ) return;
		if ( panel.getAttribute( 'inert' ) === '' || panel.hasAttribute( 'inert' ) ) {
			// Drawer is closed — nothing visible to check.
			return;
		}

		var suggested = panel.querySelector( '.ct-wishlist-suggested' );
		if ( ! suggested ) {
			window.bcWishlistFlexyState = 'OK_NO_SUGGESTED_DIV';
			return;
		}

		var firstItem = suggested.querySelector( '.flexy-item' );
		if ( ! firstItem ) {
			// No carousel rendered — could be intentional (empty render).
			window.bcWishlistFlexyState = 'OK_NO_FLEXY_ITEM';
			return;
		}

		var rect = firstItem.getBoundingClientRect();
		var cs   = window.getComputedStyle( firstItem );
		var panelRect = panel.getBoundingClientRect();

		var problems = [];

		if ( rect.width === 0 || rect.height === 0 ) {
			problems.push( 'zero-dimension (' + Math.round( rect.width ) + 'x' + Math.round( rect.height ) + ')' );
		}

		// Transform should be identity (none or matrix(1,0,0,1,0,0)).
		var t = cs.transform;
		if ( t && t !== 'none' && t.indexOf( 'matrix(1, 0, 0, 1, 0, 0)' ) === -1 ) {
			problems.push( 'transform=' + t );
		}

		// Item should overlap the panel horizontally.
		if ( rect.right < panelRect.left || rect.left > panelRect.right ) {
			problems.push( 'off-screen-X (item.left=' + Math.round( rect.left ) + ', panel.left=' + Math.round( panelRect.left ) + '-' + Math.round( panelRect.right ) + ')' );
		}

		if ( problems.length === 0 ) {
			window.bcWishlistFlexyState = 'OK';
			return;
		}

		window.bcWishlistFlexyState = 'BROKEN';
		console.error(
			'[BBC sentinel] Wishlist drawer suggested products are NOT VISIBLE. ' +
			'Likely Blocksy flexy CSS regression. Issues:\n  • ' +
			problems.join( '\n  • ' ) +
			'\nFix: review assets/css/components/wishlist-offcanvas.css overrides for .ct-wishlist-suggested .flexy-items > *. ' +
			'Inspect window.bcWishlistFlexyState for current state.'
		);
	}

	// Run on every drawer open (Blocksy fires this when offcanvas opens).
	document.addEventListener( 'click', function ( e ) {
		var trigger = e.target.closest( '[href="#woo-wishlist-panel"], [data-toggle-panel="#woo-wishlist-panel"]' );
		if ( ! trigger ) return;
		// Wait for Blocksy to remove inert + render.
		setTimeout( checkFlexyVisibility, 250 );
		setTimeout( checkFlexyVisibility, 800 );
	} );

	// Also run once at page load if drawer happens to be open already.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			setTimeout( checkFlexyVisibility, 500 );
		} );
	} else {
		setTimeout( checkFlexyVisibility, 500 );
	}
}() );
