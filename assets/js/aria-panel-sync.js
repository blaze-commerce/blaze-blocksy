/**
 * ARIA Panel Sync — keep aria-expanded / aria-controls on offcanvas-panel
 * triggers in lockstep with the panel's `active` class.
 *
 * Why: Blocksy renders cart / wishlist / filter / account / product-info
 * triggers as plain <a href="#panel-id"> (or a real URL for wishlist) with
 * NO ARIA state.  Screen-reader users can't tell the trigger expands a
 * region or what its current state is — fails WCAG 4.1.2 Name, Role, Value.
 *
 * Approach (no jQuery, vanilla JS):
 *   1. On init, walk a known-mapping table of trigger-selector → panel-id.
 *   2. For each pair: stamp aria-controls + initial aria-expanded="false"
 *      on the trigger.
 *   3. Bind a MutationObserver on the panel's `class` attribute.  When
 *      `.active` is added → aria-expanded="true".  When removed → "false".
 *   4. Observer is one per panel, not per trigger, so multiple triggers
 *      pointing at the same panel (e.g. mobile + desktop) all stay in sync.
 *
 * Scope (this site, 2026-05-08):
 *   • #woo-cart-panel       cart drawer
 *   • #woo-wishlist-panel   wishlist drawer  (trigger uses real URL — see WISHLIST_TRIGGER_SEL)
 *   • #woo-filters-panel    filter drawer (shop archive only)
 *   • #account-modal        login modal
 *   • #product-info-panel   product info (PDP only)
 *
 * Audit P1 F9, 2026-05-08.
 */
(function () {
	'use strict';

	// Wishlist trigger: `#woo-wishlist-panel` since 86eypb6jy overrode
	// Companion Pro's native "wish-list" header item view to open the
	// off-canvas drawer directly (inc/wishlist-offcanvas.php's
	// blocksy:header:item-view-path:wish-list filter). The real-URL form
	// is kept in the selector too, for any older markup still using
	// Companion Pro's stock page-link view.
	var WISHLIST_TRIGGER_SEL = 'a[href*="/my-account/woo-wish-list"], a[href="#woo-wishlist-panel"]';

	var PAIRS = [
		{ panelId: 'woo-cart-panel',     triggerSel: 'a[href="#woo-cart-panel"]' },
		{ panelId: 'woo-wishlist-panel', triggerSel: WISHLIST_TRIGGER_SEL },
		{ panelId: 'woo-filters-panel',  triggerSel: 'a[href="#woo-filters-panel"]' },
		{ panelId: 'account-modal',      triggerSel: 'a[href="#account-modal"]' },
		{ panelId: 'product-info-panel', triggerSel: '[data-panel="product-info-panel"]' }
	];

	function syncAll( panel, expanded ) {
		var triggers = document.querySelectorAll( '[aria-controls="' + panel.id + '"]' );
		Array.prototype.forEach.call( triggers, function ( t ) {
			t.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
		} );
	}

	function bindPanel( pair ) {
		var panel    = document.getElementById( pair.panelId );
		var triggers = document.querySelectorAll( pair.triggerSel );

		if ( ! panel || ! triggers.length ) {
			return false;
		}

		Array.prototype.forEach.call( triggers, function ( t ) {
			t.setAttribute( 'aria-controls', pair.panelId );
			// haspopup hints assistive tech; "dialog" matches Blocksy's panel role.
			if ( ! t.hasAttribute( 'aria-haspopup' ) ) {
				t.setAttribute( 'aria-haspopup', 'dialog' );
			}
			t.setAttribute(
				'aria-expanded',
				panel.classList.contains( 'active' ) ? 'true' : 'false'
			);
		} );

		// Watch the panel's class attribute. Blocksy + our own panels both
		// toggle `.active` for open/closed state.  One observer per panel.
		var observer = new MutationObserver( function () {
			syncAll( panel, panel.classList.contains( 'active' ) );
		} );
		observer.observe( panel, { attributes: true, attributeFilter: [ 'class' ] } );

		return true;
	}

	function init() {
		PAIRS.forEach( bindPanel );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
})();
