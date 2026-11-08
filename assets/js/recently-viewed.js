/**
 * Recently Viewed — cache-safe tracker and loader (see inc/recently-viewed.php).
 *
 * Full-page caches never run PHP on a hit and strip Set-Cookie on the miss,
 * so the `woocommerce_recently_viewed` cookie is kept here, in the browser,
 * in WooCommerce's own format: product IDs joined by "|", oldest first.
 * The row itself is fetched from ?wc-ajax=bc_recently_viewed with the IDs
 * in the URL, then dropped into the page's empty slot.
 *
 * After injecting, Blocksy is asked to mount the new Flexy carousel
 * (`blocksy:frontend:init`) and `bc:recently-viewed:loaded` is fired for
 * product-carousel-dots.js.
 */
( function () {
	'use strict';

	var cfg = window.bcRecentlyViewed || {};
	var NAME = 'woocommerce_recently_viewed';

	function readIds() {
		var m = new RegExp( '(?:^|;\\s*)' + NAME + '=([^;]*)' ).exec( document.cookie );
		if ( ! m ) {
			return [];
		}
		try {
			return decodeURIComponent( m[ 1 ] ).split( '|' ).map( Number ).filter( function ( n ) {
				return n > 0;
			} );
		} catch ( e ) {
			return [];
		}
	}

	function writeIds( ids ) {
		document.cookie = NAME + '=' + encodeURIComponent( ids.join( '|' ) ) +
			'; path=' + ( cfg.path || '/' ) +
			( cfg.domain ? '; domain=' + cfg.domain : '' ) +
			'; SameSite=Lax' +
			( 'https:' === location.protocol ? '; Secure' : '' );
	}

	function run() {
		var slot = document.querySelector( '.bc-recently-viewed-slot' );
		var id = slot ? parseInt( slot.getAttribute( 'data-product-id' ), 10 ) : 0;
		if ( ! id ) {
			return;
		}

		var others = readIds().filter( function ( x ) {
			return x !== id;
		} );
		writeIds( others.concat( id ).slice( -( cfg.max || 15 ) ) );

		if ( ! others.length || ! cfg.endpoint || ! window.fetch ) {
			return;
		}

		var url = cfg.endpoint + ( cfg.endpoint.indexOf( '?' ) > -1 ? '&' : '?' ) +
			'ids=' + others.join( ',' ) + '&exclude=' + id;

		fetch( url, { credentials: 'same-origin' } )
			.then( function ( r ) {
				return r.ok ? r.text() : '';
			} )
			.then( function ( html ) {
				if ( ! html || html.indexOf( 'bc-recently-viewed' ) === -1 ) {
					return;
				}
				slot.innerHTML = html;
				if ( window.ctEvents && typeof window.ctEvents.trigger === 'function' ) {
					window.ctEvents.trigger( 'blocksy:frontend:init' );
				}
				document.dispatchEvent( new CustomEvent( 'bc:recently-viewed:loaded', {
					detail: { section: slot.querySelector( '.bc-recently-viewed' ) },
				} ) );
			} )
			.catch( function () {} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', run );
	} else {
		run();
	}
} )();
