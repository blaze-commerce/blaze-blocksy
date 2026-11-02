/**
 * Recently viewed products: tracked and rendered in the browser, so a full-page cache cannot break it or leak it.
 *
 * The page holds one empty, hidden <section data-bc-rv> that is the same for every visitor (Module.php). This
 * script keeps the visitor's list in localStorage (key bc_recently_viewed, newest first), records the current
 * product, then fills the section from the WooCommerce Store API with textContent/setAttribute only.
 *
 * Design from PR #273 by its author. Also kept from it: the list is mirrored, write-only, into WooCommerce's
 * woocommerce_recently_viewed cookie (ids joined by "|", oldest first) so v1 code that reads that cookie still
 * works. On the first run, if localStorage has no list, the ids in that cookie are migrated once.
 *
 * Exposed as window.blazeChild.recentlyViewed; under node (tests) the same object is module.exports.
 */
( function ( root, factory ) {
	'use strict';

	var api = factory();

	// Node (tests) has no window; a browser page that happens to define a global `module` still gets the global.
	if ( 'undefined' === typeof window && 'object' === typeof module && module && module.exports ) {
		module.exports = api;
		return;
	}

	root.blazeChild = root.blazeChild || {};
	root.blazeChild.recentlyViewed = api;

	if ( root.document ) {
		if ( 'loading' === root.document.readyState ) {
			root.document.addEventListener( 'DOMContentLoaded', function () {
				api.init( root );
			} );
		} else {
			api.init( root );
		}
	}
}( 'undefined' !== typeof window ? window : this, function () {
	'use strict';

	var KEY = 'bc_recently_viewed';
	var COOKIE = 'woocommerce_recently_viewed';
	var STORE_MAX = 15;
	var DEFAULT_LIMIT = 8;
	var EVENT = 'bc:recently-viewed:loaded';
	var STORE_API = '/wp-json/wc/store/v1/products';

	var NAMED = {
		amp: '&',
		lt: '<',
		gt: '>',
		quot: '"',
		apos: '\'',
		nbsp: ' ',
		ndash: '–',
		mdash: '—',
		hellip: '…',
		lsquo: '‘',
		rsquo: '’',
		ldquo: '“',
		rdquo: '”',
		times: '×',
		euro: '€',
		pound: '£',
		yen: '¥',
		cent: '¢',
		reg: '®',
		trade: '™',
		copy: '©',
	};

	/**
	 * A positive integer id from a number or a digits-only string, else 0.
	 *
	 * @param {*} value Candidate.
	 * @return {number} Id or 0.
	 */
	function toId( value ) {
		var n = NaN;
		if ( 'number' === typeof value ) {
			n = value;
		} else if ( 'string' === typeof value && /^\s*\d+\s*$/.test( value ) ) {
			n = Number( value );
		}
		return Number.isSafeInteger( n ) && n > 0 ? n : 0;
	}

	/**
	 * Valid ids of a list, deduped (first one wins), at most max.
	 *
	 * @param {*}      list Candidate list.
	 * @param {number} max  Cap.
	 * @return {number[]} Clean copy.
	 */
	function clean( list, max ) {
		var out = [];
		var i;
		var id;
		if ( ! Array.isArray( list ) ) {
			return out;
		}
		for ( i = 0; i < list.length && out.length < max; i++ ) {
			id = toId( list[ i ] );
			if ( id && -1 === out.indexOf( id ) ) {
				out.push( id );
			}
		}
		return out;
	}

	/**
	 * The list stored in localStorage, or [] for anything that is not a JSON array.
	 *
	 * @param {*} raw Stored string.
	 * @return {number[]} Ids, newest first.
	 */
	function parse( raw ) {
		var data;
		if ( 'string' !== typeof raw || '' === raw ) {
			return [];
		}
		try {
			data = JSON.parse( raw );
		} catch ( e ) {
			return [];
		}
		return clean( data, STORE_MAX );
	}

	/**
	 * Records a view: id moves to the front, duplicates go, the list is capped. Invalid ids are ignored.
	 *
	 * @param {*}      list  Ids, newest first.
	 * @param {*}      id    Viewed product id.
	 * @param {number} limit Cap (default STORE_MAX).
	 * @return {number[]} New list.
	 */
	function push( list, id, limit ) {
		var max = toId( limit ) || STORE_MAX;
		var current = toId( id );
		var rest = clean( list, Infinity ).filter( function ( x ) {
			return x !== current;
		} );
		return ( current ? [ current ].concat( rest ) : rest ).slice( 0, max );
	}

	/**
	 * Ids in WooCommerce's woocommerce_recently_viewed cookie (oldest first there), newest first.
	 *
	 * @param {string} cookies document.cookie.
	 * @return {number[]} Ids.
	 */
	function cookieIds( cookies ) {
		var match = new RegExp( '(?:^|;\\s*)' + COOKIE + '=([^;]*)' ).exec( 'string' === typeof cookies ? cookies : '' );
		var raw;
		if ( ! match ) {
			return [];
		}
		try {
			raw = decodeURIComponent( match[ 1 ] );
		} catch ( e ) {
			return [];
		}
		return clean( raw.split( '|' ).reverse(), STORE_MAX );
	}

	/**
	 * The Set-Cookie string for document.cookie: WooCommerce's name, format, path and domain, session lifetime.
	 *
	 * @param {number[]} list   Ids, newest first.
	 * @param {Object}   cfg    Config (cookiePath, cookieDomain).
	 * @param {boolean}  secure Page is https.
	 * @return {string} Cookie string.
	 */
	function cookieString( list, cfg, secure ) {
		var ids = clean( list, STORE_MAX ).reverse();
		var path = cfg && 'string' === typeof cfg.cookiePath && /^\/[^;,\s]*$/.test( cfg.cookiePath ) ? cfg.cookiePath : '/';
		var domain = cfg && 'string' === typeof cfg.cookieDomain && /^\.?[a-z0-9.-]+$/i.test( cfg.cookieDomain ) ? cfg.cookieDomain : '';
		return COOKIE + '=' + encodeURIComponent( ids.join( '|' ) ) +
			'; path=' + path +
			( domain ? '; domain=' + domain : '' ) +
			'; SameSite=Lax' +
			( secure ? '; Secure' : '' );
	}

	/**
	 * Ids to show: the list without the current product, at most limit.
	 *
	 * @param {number[]} list      Ids, newest first.
	 * @param {*}        currentId Current product id (0 = none).
	 * @param {*}        limit     Cap (default 8).
	 * @return {number[]} Ids.
	 */
	function visible( list, currentId, limit ) {
		var current = toId( currentId );
		return clean( list, STORE_MAX ).filter( function ( id ) {
			return id !== current;
		} ).slice( 0, toId( limit ) || DEFAULT_LIMIT );
	}

	/**
	 * Store API products URL. No nonce: this GET is public.
	 *
	 * @param {string}   base  rest_url( 'wc/store/v1/products' ), pretty or ?rest_route= form.
	 * @param {number[]} ids   Ids.
	 * @param {number}   limit per_page.
	 * @return {string} URL.
	 */
	function productsUrl( base, ids, limit ) {
		var url = 'string' === typeof base && base ? base : STORE_API;
		return url + ( -1 === url.indexOf( '?' ) ? '?' : '&' ) +
			'include=' + clean( ids, Infinity ).join( ',' ) +
			'&per_page=' + ( toId( limit ) || DEFAULT_LIMIT );
	}

	/**
	 * API products in the order of ids; ids the API did not return (deleted, private) are skipped.
	 *
	 * @param {*}        products Store API response.
	 * @param {number[]} ids      Ids in display order.
	 * @return {Object[]} Products.
	 */
	function inOrder( products, ids ) {
		var byId = {};
		if ( Array.isArray( products ) ) {
			products.forEach( function ( p ) {
				var id = p && 'object' === typeof p ? toId( p.id ) : 0;
				if ( id ) {
					byId[ id ] = p;
				}
			} );
		}
		return ids.filter( function ( id ) {
			return Object.prototype.hasOwnProperty.call( byId, id );
		} ).map( function ( id ) {
			return byId[ id ];
		} );
	}

	/**
	 * The URL when it is http(s) or root-relative, else ''.
	 *
	 * @param {*} url Candidate.
	 * @return {string} URL or ''.
	 */
	function safeUrl( url ) {
		return 'string' === typeof url && /^(?:https?:\/\/|\/(?!\/))/i.test( url ) ? url : '';
	}

	/**
	 * Decodes the HTML entities WordPress leaves in titles, without parsing HTML.
	 *
	 * @param {*} text Text.
	 * @return {string} Decoded text.
	 */
	function decodeEntities( text ) {
		if ( 'string' !== typeof text ) {
			return '';
		}
		return text.replace( /&(#x[0-9a-f]+|#\d+|[a-z]+);/gi, function ( whole, code ) {
			var n;
			var key;
			if ( '#' === code.charAt( 0 ) ) {
				n = 'x' === code.charAt( 1 ).toLowerCase() ? parseInt( code.slice( 2 ), 16 ) : parseInt( code.slice( 1 ), 10 );
				return n > 0 && n <= 0x10ffff ? String.fromCodePoint( n ) : whole;
			}
			key = code.toLowerCase();
			return Object.prototype.hasOwnProperty.call( NAMED, key ) ? NAMED[ key ] : whole;
		} );
	}

	/**
	 * Formats a Store API price (an integer string in minor units) with the store's currency settings.
	 *
	 * @param {Object} prices Store API prices object.
	 * @param {string} field  Field (default 'price').
	 * @return {string} Formatted price or ''.
	 */
	function formatPrice( prices, field ) {
		var raw = prices && 'object' === typeof prices ? prices[ field || 'price' ] : null;
		var minor;
		var negative;
		var digits;
		var whole;
		var fraction;
		if ( ( 'string' !== typeof raw && 'number' !== typeof raw ) || ! /^-?\d+$/.test( String( raw ) ) ) {
			return '';
		}
		raw = String( raw );
		minor = Number( prices.currency_minor_unit );
		minor = Number.isInteger( minor ) && minor >= 0 && minor <= 10 ? minor : 2;
		negative = '-' === raw.charAt( 0 );
		digits = negative ? raw.slice( 1 ) : raw;
		while ( digits.length <= minor ) {
			digits = '0' + digits;
		}
		whole = minor ? digits.slice( 0, -minor ) : digits;
		fraction = minor ? digits.slice( -minor ) : '';
		whole = whole.replace( /\B(?=(\d{3})+(?!\d))/g, 'string' === typeof prices.currency_thousand_separator ? prices.currency_thousand_separator : ',' );
		return ( negative ? '-' : '' ) +
			decodeEntities( prices.currency_prefix || '' ) +
			whole +
			( fraction ? ( 'string' === typeof prices.currency_decimal_separator ? prices.currency_decimal_separator : '.' ) + fraction : '' ) +
			decodeEntities( prices.currency_suffix || '' );
	}

	/**
	 * @param {Document} doc       Document.
	 * @param {string}   tag       Tag name.
	 * @param {string}   className Class attribute.
	 * @return {Element} Element.
	 */
	function el( doc, tag, className ) {
		var node = doc.createElement( tag );
		node.setAttribute( 'class', className );
		return node;
	}

	/**
	 * Price line: the current price, or the min-max range of a variable product; regular price struck on sale.
	 *
	 * @param {Document} doc     Document.
	 * @param {Object}   product Store API product.
	 * @return {Element|null} Price element.
	 */
	function priceEl( doc, product ) {
		var prices = product.prices;
		var range = prices && prices.price_range;
		var current = '';
		var regular = '';
		var wrap;
		var amount;
		var del;
		if ( range && range.min_amount !== range.max_amount ) {
			current = formatPrice( Object.assign( {}, prices, { price: range.min_amount } ) ) + ' – ' + formatPrice( Object.assign( {}, prices, { price: range.max_amount } ) );
		} else {
			current = formatPrice( prices );
			if ( product.on_sale && prices && prices.regular_price !== prices.price ) {
				regular = formatPrice( prices, 'regular_price' );
			}
		}
		if ( ! current ) {
			return null;
		}
		wrap = el( doc, 'span', 'bc-recently-viewed__price price' );
		if ( regular ) {
			del = el( doc, 'del', 'bc-recently-viewed__regular' );
			amount = el( doc, 'span', 'bc-recently-viewed__amount' );
			amount.textContent = regular;
			del.appendChild( amount );
			wrap.appendChild( del );
		}
		amount = el( doc, 'span', 'bc-recently-viewed__amount' );
		amount.textContent = current;
		wrap.appendChild( amount );
		return wrap;
	}

	/**
	 * One product card.
	 *
	 * @param {Document} doc     Document.
	 * @param {Object}   product Store API product.
	 * @return {Element} <li>.
	 */
	function card( doc, product ) {
		var item = el( doc, 'li', 'bc-recently-viewed__item product' );
		var link = el( doc, 'a', 'bc-recently-viewed__link woocommerce-LoopProduct-link' );
		var href = safeUrl( product.permalink );
		var image = Array.isArray( product.images ) ? product.images[ 0 ] : null;
		var src = image && 'object' === typeof image ? safeUrl( image.thumbnail ) || safeUrl( image.src ) : '';
		var img;
		var name = el( doc, 'h3', 'bc-recently-viewed__name woocommerce-loop-product__title' );
		var price = priceEl( doc, product );

		if ( href ) {
			link.setAttribute( 'href', href );
		}
		if ( src ) {
			img = el( doc, 'img', 'bc-recently-viewed__image' );
			img.setAttribute( 'src', src );
			img.setAttribute( 'alt', decodeEntities( image.alt || '' ) );
			img.setAttribute( 'loading', 'lazy' );
			img.setAttribute( 'decoding', 'async' );
			link.appendChild( img );
		}
		name.textContent = decodeEntities( product.name );
		link.appendChild( name );
		item.appendChild( link );
		if ( price ) {
			item.appendChild( price );
		}
		return item;
	}

	/**
	 * Appends the cards and unhides the section. With no products the section stays hidden and untouched.
	 *
	 * @param {Element}  section  The [data-bc-rv] section.
	 * @param {Object[]} products Store API products, in display order.
	 * @param {Document} doc      Document.
	 * @return {boolean} Whether anything was shown.
	 */
	function render( section, products, doc ) {
		var list;
		if ( ! Array.isArray( products ) || ! products.length ) {
			return false;
		}
		list = el( doc, 'ul', 'bc-recently-viewed__list' );
		// list-style: none drops list semantics in Safari; the role keeps them.
		list.setAttribute( 'role', 'list' );
		products.forEach( function ( product ) {
			list.appendChild( card( doc, product ) );
		} );
		section.appendChild( list );
		section.removeAttribute( 'hidden' );
		return true;
	}

	/**
	 * localStorage, or null when it is missing or blocked (private mode, disabled site data).
	 *
	 * @param {Window} root Window.
	 * @return {Storage|null} Storage.
	 */
	function storage( root ) {
		var store;
		try {
			store = root.localStorage;
			store.setItem( KEY + '__probe', '1' );
			store.removeItem( KEY + '__probe' );
			return store;
		} catch ( e ) {
			return null;
		}
	}

	/**
	 * Fetches and renders one section.
	 *
	 * @param {Window}   root    Window.
	 * @param {Element}  section Section.
	 * @param {number[]} list    Ids, newest first.
	 * @param {number}   current Current product id.
	 * @param {Object}   cfg     Config.
	 * @return {Promise<boolean>} Whether anything was shown.
	 */
	function fill( root, section, list, current, cfg ) {
		var limit = toId( section.getAttribute( 'data-limit' ) ) || DEFAULT_LIMIT;
		var ids = visible( list, current, limit );
		if ( ! ids.length || 'function' !== typeof root.fetch ) {
			return Promise.resolve( false );
		}
		return root.fetch( productsUrl( cfg.restUrl, ids, limit ), {
			credentials: 'same-origin',
			headers: { Accept: 'application/json' },
		} ).then( function ( response ) {
			return response && response.ok ? response.json() : [];
		} ).then( function ( products ) {
			var shown = render( section, inOrder( products, ids ), root.document );
			if ( shown && 'function' === typeof root.CustomEvent ) {
				root.document.dispatchEvent( new root.CustomEvent( EVENT, { detail: { section: section } } ) );
			}
			return shown;
		} ).catch( function () {
			return false;
		} );
	}

	/**
	 * Migrates the v1 cookie once, records the current product, mirrors the cookie and fills every section.
	 *
	 * @param {Window} root Window.
	 * @return {Promise<boolean>} Whether any section was shown (false when storage is unavailable).
	 */
	function init( root ) {
		var doc = root.document;
		var cfg = ( root.blazeChild && root.blazeChild.recentlyViewedConfig ) || {};
		var sections = Array.prototype.slice.call( doc.querySelectorAll( '[data-bc-rv]' ) );
		var store = storage( root );
		var raw;
		var list;
		var current = 0;

		if ( ! store ) {
			return Promise.resolve( false );
		}

		raw = store.getItem( KEY );
		// First run only: no stored list yet, so take over the ids of v1's cookie. Never read it again.
		list = null === raw ? cookieIds( doc.cookie ) : parse( raw );

		sections.forEach( function ( section ) {
			current = current || toId( section.getAttribute( 'data-product-id' ) );
		} );

		// Fill from the list as it was before this view (fill() reads it synchronously), then record the view.
		sections = sections.map( function ( section ) {
			return fill( root, section, list, current, cfg );
		} );

		if ( current || null === raw ) {
			list = push( list, current, STORE_MAX );
			try {
				store.setItem( KEY, JSON.stringify( list ) );
			} catch ( e ) {
				// Quota or blocked: nothing to keep.
			}
			if ( current ) {
				doc.cookie = cookieString( list, cfg, Boolean( root.location && 'https:' === root.location.protocol ) );
			}
		}

		return Promise.all( sections ).then( function ( results ) {
			return results.indexOf( true ) > -1;
		} );
	}

	return {
		KEY: KEY,
		COOKIE: COOKIE,
		STORE_MAX: STORE_MAX,
		parse: parse,
		push: push,
		cookieIds: cookieIds,
		cookieString: cookieString,
		visible: visible,
		productsUrl: productsUrl,
		inOrder: inOrder,
		safeUrl: safeUrl,
		decodeEntities: decodeEntities,
		formatPrice: formatPrice,
		render: render,
		init: init,
	};
} ) );
