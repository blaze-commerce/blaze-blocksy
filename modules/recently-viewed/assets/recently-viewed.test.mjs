// Unit tests for recently-viewed.js: node --test modules/recently-viewed/assets/recently-viewed.test.mjs
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { createRequire } from 'node:module';

const require = createRequire( import.meta.url );
const rv = require( './recently-viewed.js' );

test( 'push_dedupes_and_caps', () => {
	assert.deepEqual( rv.push( [], 5, 8 ), [ 5 ] );
	assert.deepEqual( rv.push( [ 3, 2, 1 ], 4, 8 ), [ 4, 3, 2, 1 ], 'newest first' );
	assert.deepEqual( rv.push( [ 3, 2, 1 ], 2, 8 ), [ 2, 3, 1 ], 'a repeat view moves to the front' );
	assert.deepEqual( rv.push( [ 3, 2, 1 ], 4, 3 ), [ 4, 3, 2 ], 'capped at limit, oldest dropped' );
	assert.deepEqual( rv.push( [ 1, 1, 2, 2 ], 3, 8 ), [ 3, 1, 2 ], 'duplicates in the input collapse' );
	assert.deepEqual( rv.push( [ 1 ], '7', 8 ), [ 7, 1 ], 'numeric string from a data attribute' );
	const list = [ 2, 1 ];
	rv.push( list, 3, 8 );
	assert.deepEqual( list, [ 2, 1 ], 'input not mutated' );
	assert.equal( rv.push( Array.from( { length: 40 }, ( _, i ) => i + 1 ), 99 ).length, rv.STORE_MAX, 'default cap is the stored max' );
} );

test( 'push_ignores_invalid_ids', () => {
	for ( const bad of [ 0, -3, 1.5, NaN, Infinity, '', 'abc', '12abc', null, undefined, {}, [], true ] ) {
		assert.deepEqual( rv.push( [ 2, 1 ], bad, 8 ), [ 2, 1 ], `ignores ${ String( bad ) }` );
	}
	assert.deepEqual( rv.push( [ 2, 'x', -1, 0, 1 ], 3, 8 ), [ 3, 2, 1 ], 'bad ids already in the list are dropped' );
	assert.deepEqual( rv.push( 'garbage', 3, 8 ), [ 3 ], 'non-array list' );
} );

test( 'parse_tolerates_garbage', () => {
	assert.deepEqual( rv.parse( '[3,2,1]' ), [ 3, 2, 1 ] );
	for ( const bad of [ null, undefined, '', '{', 'null', '"x"', '{"a":1}', '42', 'true', 42, {} ] ) {
		assert.deepEqual( rv.parse( bad ), [], `garbage ${ String( bad ) }` );
	}
	assert.deepEqual( rv.parse( '[3,"2",-1,0,1.5,"x",null,3,{}]' ), [ 3, 2 ], 'keeps only positive ints, deduped' );
	assert.equal( rv.parse( JSON.stringify( Array.from( { length: 50 }, ( _, i ) => i + 1 ) ) ).length, rv.STORE_MAX, 'capped' );
} );

test( 'cookie_ids_reads_the_v1_woocommerce_cookie_newest_first', () => {
	assert.deepEqual( rv.cookieIds( 'a=1; woocommerce_recently_viewed=11%7C22%7C33; b=2' ), [ 33, 22, 11 ] );
	assert.deepEqual( rv.cookieIds( 'woocommerce_recently_viewed=11|22|11' ), [ 11, 22 ], 'duplicate keeps the newest position' );
	assert.deepEqual( rv.cookieIds( 'xwoocommerce_recently_viewed=11' ), [], 'exact cookie name only' );
	assert.deepEqual( rv.cookieIds( 'woocommerce_recently_viewed=%E0%A4%A' ), [], 'bad encoding' );
	assert.deepEqual( rv.cookieIds( undefined ), [] );
} );

test( 'cookie_string_writes_woocommerce_format', () => {
	assert.equal(
		rv.cookieString( [ 3, 2, 1 ], { cookiePath: '/shop/', cookieDomain: '.example.test' }, true ),
		'woocommerce_recently_viewed=1%7C2%7C3; path=/shop/; domain=.example.test; SameSite=Lax; Secure'
	);
	assert.equal( rv.cookieString( [ 5 ], {}, false ), 'woocommerce_recently_viewed=5; path=/; SameSite=Lax' );
	assert.equal( rv.cookieString( [ 5 ], { cookiePath: 'x;evil=1', cookieDomain: 'a;b' }, false ), 'woocommerce_recently_viewed=5; path=/; SameSite=Lax', 'unsafe path/domain ignored' );
} );

test( 'visible_excludes_current_and_caps', () => {
	assert.deepEqual( rv.visible( [ 5, 4, 3, 2, 1 ], 5, 2 ), [ 4, 3 ] );
	assert.deepEqual( rv.visible( [ 5, 4 ], '4', 8 ), [ 5 ] );
	assert.deepEqual( rv.visible( [ 5, 4 ], 0, 'x' ), [ 5, 4 ], 'bad limit falls back to the default' );
} );

test( 'products_url_uses_store_api_include_and_per_page', () => {
	assert.equal(
		rv.productsUrl( 'https://example.test/wp-json/wc/store/v1/products', [ 4, 3 ], 8 ),
		'https://example.test/wp-json/wc/store/v1/products?include=4,3&per_page=8'
	);
	assert.equal(
		rv.productsUrl( 'https://example.test/index.php?rest_route=/wc/store/v1/products', [ 4 ], 8 ),
		'https://example.test/index.php?rest_route=/wc/store/v1/products&include=4&per_page=8',
		'plain permalinks'
	);
	assert.equal( rv.productsUrl( '', [ 4 ], 2 ), '/wp-json/wc/store/v1/products?include=4&per_page=2', 'fallback without config' );
} );

test( 'in_order_follows_stored_order_and_skips_missing', () => {
	const api = [ { id: 3, name: 'C' }, { id: 1, name: 'A' }, { id: 77, name: 'not asked' } ];
	assert.deepEqual( rv.inOrder( api, [ 1, 2, 3 ] ).map( ( p ) => p.name ), [ 'A', 'C' ] );
	assert.deepEqual( rv.inOrder( { code: 'error' }, [ 1 ] ), [] );
	assert.deepEqual( rv.inOrder( [ null, 'x' ], [ 1 ] ), [] );
} );

test( 'safe_url_and_decode_entities', () => {
	assert.equal( rv.safeUrl( 'https://example.test/p/a/' ), 'https://example.test/p/a/' );
	assert.equal( rv.safeUrl( '/p/a/' ), '/p/a/' );
	for ( const bad of [ 'javascript:alert(1)', ' javascript:x', '//evil.test/x', 'data:text/html,x', 42, null ] ) {
		assert.equal( rv.safeUrl( bad ), '', String( bad ) );
	}
	assert.equal( rv.decodeEntities( 'Tea &amp; Cake &#8211; &#x2014; &lt;b&gt; &quot;x&quot; &#039;y&#039; &bogus;' ), 'Tea & Cake – — <b> "x" \'y\' &bogus;' );
	assert.equal( rv.decodeEntities( '&#0; &#99999999;' ), '&#0; &#99999999;', 'out of range left as is' );
	assert.equal( rv.decodeEntities( null ), '' );
} );

test( 'format_price_from_store_api_minor_units', () => {
	const base = { currency_minor_unit: 2, currency_decimal_separator: '.', currency_thousand_separator: ',', currency_prefix: '$', currency_suffix: '' };
	assert.equal( rv.formatPrice( { ...base, price: '123456' } ), '$1,234.56' );
	assert.equal( rv.formatPrice( { ...base, price: '5' } ), '$0.05' );
	assert.equal( rv.formatPrice( { ...base, price: '1000', currency_minor_unit: 0, currency_prefix: '', currency_suffix: ' kr' } ), '1,000 kr' );
	assert.equal( rv.formatPrice( { ...base, price: '199900', currency_decimal_separator: ',', currency_thousand_separator: '.', currency_prefix: '', currency_suffix: '&nbsp;&euro;' } ), '1.999,00 €' );
	assert.equal( rv.formatPrice( { ...base, regular_price: '2000' }, 'regular_price' ), '$20.00' );
	assert.equal( rv.formatPrice( { ...base, price: '12.5' } ), '', 'not minor units' );
	assert.equal( rv.formatPrice( null ), '' );
} );

// ---------------------------------------------------------------------------
// A tiny fake DOM, enough for render() and init().
// ---------------------------------------------------------------------------

class FakeEl {
	constructor( tag ) {
		this.tagName = tag.toUpperCase();
		this.attrs = {};
		this.children = [];
		this.textContent = '';
	}
	setAttribute( k, v ) {
		this.attrs[ k ] = String( v );
	}
	getAttribute( k ) {
		return Object.prototype.hasOwnProperty.call( this.attrs, k ) ? this.attrs[ k ] : null;
	}
	hasAttribute( k ) {
		return Object.prototype.hasOwnProperty.call( this.attrs, k );
	}
	removeAttribute( k ) {
		delete this.attrs[ k ];
	}
	appendChild( c ) {
		this.children.push( c );
		return c;
	}
	all() {
		return this.children.flatMap( ( c ) => [ c, ...c.all() ] );
	}
}

function fakeWindow( { sections = [], stored, cookie = '', products = [], storageThrows = false, ok = true } = {} ) {
	const store = new Map();
	if ( undefined !== stored ) {
		store.set( 'bc_recently_viewed', stored );
	}
	const win = {
		requests: [],
		cookieWrites: [],
		events: [],
		location: { protocol: 'https:' },
		blazeChild: { recentlyViewedConfig: { restUrl: 'https://example.test/wp-json/wc/store/v1/products', cookiePath: '/' } },
		document: {
			readyState: 'complete',
			createElement: ( tag ) => new FakeEl( tag ),
			querySelectorAll: ( sel ) => ( '[data-bc-rv]' === sel ? sections : [] ),
			dispatchEvent: ( e ) => win.events.push( e ),
		},
		CustomEvent: class {
			constructor( type, init ) {
				this.type = type;
				this.detail = init && init.detail;
			}
		},
		fetch: ( url ) => {
			win.requests.push( url );
			return Promise.resolve( { ok, json: () => Promise.resolve( products ) } );
		},
	};
	Object.defineProperty( win.document, 'cookie', {
		get: () => cookie,
		set: ( v ) => win.cookieWrites.push( v ),
	} );
	Object.defineProperty( win, 'localStorage', {
		get() {
			if ( storageThrows ) {
				throw new Error( 'SecurityError' );
			}
			return {
				getItem: ( k ) => ( store.has( k ) ? store.get( k ) : null ),
				setItem: ( k, v ) => store.set( k, String( v ) ),
				removeItem: ( k ) => store.delete( k ),
			};
		},
	} );
	win.store = store;
	return win;
}

function section( productId, limit = 8 ) {
	const s = new FakeEl( 'section' );
	s.setAttribute( 'class', 'bc-recently-viewed' );
	s.setAttribute( 'data-bc-rv', '' );
	s.setAttribute( 'data-limit', String( limit ) );
	s.setAttribute( 'data-product-id', String( productId ) );
	s.setAttribute( 'hidden', '' );
	return s;
}

const PRICES = { price: '1500', regular_price: '2000', currency_minor_unit: 2, currency_prefix: '$', currency_suffix: '', currency_decimal_separator: '.', currency_thousand_separator: ',' };

test( 'render_builds_cards_with_text_and_attributes_only', () => {
	const s = section( 9 );
	const doc = { createElement: ( t ) => new FakeEl( t ) };
	const shown = rv.render( s, [
		{ id: 1, name: 'Tea &amp; <script>x</script>', permalink: 'https://example.test/p/tea/', on_sale: true, prices: PRICES, images: [ { thumbnail: 'https://example.test/t.jpg', alt: 'A &quot;cup&quot;' } ] },
		{ id: 2, name: 'Bad link', permalink: 'javascript:alert(1)', prices: { ...PRICES, price: '2000' }, images: [ { src: 'javascript:x' } ] },
	], doc );
	assert.equal( shown, true );
	assert.equal( s.hasAttribute( 'hidden' ), false, 'unhidden once it has a card' );
	const list = s.children[ 0 ];
	assert.equal( list.tagName, 'UL' );
	assert.equal( list.getAttribute( 'class' ), 'bc-recently-viewed__list' );
	assert.equal( list.children.length, 2 );
	const els = list.all();
	const names = els.filter( ( e ) => /bc-recently-viewed__name/.test( e.getAttribute( 'class' ) ) );
	assert.deepEqual( names.map( ( e ) => e.textContent ), [ 'Tea & <script>x</script>', 'Bad link' ], 'name set as text' );
	const links = els.filter( ( e ) => 'A' === e.tagName );
	assert.equal( links[ 0 ].getAttribute( 'href' ), 'https://example.test/p/tea/' );
	assert.equal( links[ 1 ].getAttribute( 'href' ), null, 'unsafe permalink dropped' );
	const imgs = els.filter( ( e ) => 'IMG' === e.tagName );
	assert.equal( imgs.length, 1, 'unsafe image dropped' );
	assert.equal( imgs[ 0 ].getAttribute( 'src' ), 'https://example.test/t.jpg' );
	assert.equal( imgs[ 0 ].getAttribute( 'alt' ), 'A "cup"' );
	assert.equal( imgs[ 0 ].getAttribute( 'loading' ), 'lazy' );
	assert.deepEqual( els.filter( ( e ) => 'DEL' === e.tagName ).map( ( e ) => e.children[ 0 ].textContent ), [ '$20.00' ], 'regular price struck on sale' );
	assert.deepEqual( els.filter( ( e ) => /bc-recently-viewed__amount/.test( e.getAttribute( 'class' ) ) ).map( ( e ) => e.textContent ), [ '$20.00', '$15.00', '$20.00' ], 'regular + current, then the second card' );

	const ranged = section( 9 );
	rv.render( ranged, [ { id: 3, name: 'Var', permalink: '/v/', on_sale: true, prices: { ...PRICES, price_range: { min_amount: '1000', max_amount: '2500' } } } ], doc );
	assert.deepEqual( ranged.children[ 0 ].all().filter( ( e ) => /__amount/.test( e.getAttribute( 'class' ) ) ).map( ( e ) => e.textContent ), [ '$10.00 \u2013 $25.00' ], 'variable product shows its range' );

	const empty = section( 9 );
	assert.equal( rv.render( empty, [], doc ), false );
	assert.equal( empty.hasAttribute( 'hidden' ), true, 'stays hidden without cards' );
	assert.equal( empty.children.length, 0 );
} );

test( 'init_tracks_fetches_and_renders_in_stored_order', async () => {
	const s = section( 9, 2 );
	const win = fakeWindow( {
		sections: [ s ],
		stored: '[3,2,1]',
		products: [ { id: 2, name: 'Two', permalink: '/two/', prices: PRICES }, { id: 3, name: 'Three', permalink: '/three/', prices: PRICES } ],
	} );
	await rv.init( win );
	assert.equal( win.store.get( 'bc_recently_viewed' ), '[9,3,2,1]', 'current product stored first' );
	assert.deepEqual( win.requests, [ 'https://example.test/wp-json/wc/store/v1/products?include=3,2&per_page=2' ] );
	assert.deepEqual( win.cookieWrites, [ 'woocommerce_recently_viewed=1%7C2%7C3%7C9; path=/; SameSite=Lax; Secure' ], 'WooCommerce cookie mirrored for v1 readers' );
	const names = s.children[ 0 ].all().filter( ( e ) => /__name/.test( e.getAttribute( 'class' ) ) ).map( ( e ) => e.textContent );
	assert.deepEqual( names, [ 'Three', 'Two' ], 'stored order, not API order' );
	assert.equal( s.hasAttribute( 'hidden' ), false );
	assert.equal( win.events.length, 1 );
	assert.equal( win.events[ 0 ].type, 'bc:recently-viewed:loaded' );
	assert.equal( win.events[ 0 ].detail.section, s );
} );

test( 'init_migrates_the_v1_cookie_once', async () => {
	const win = fakeWindow( { sections: [], cookie: 'woocommerce_recently_viewed=11%7C22' } );
	await rv.init( win );
	assert.equal( win.store.get( 'bc_recently_viewed' ), '[22,11]', 'cookie ids migrated, newest first' );

	const later = fakeWindow( { sections: [], stored: '[]', cookie: 'woocommerce_recently_viewed=11%7C22' } );
	await rv.init( later );
	assert.equal( later.store.get( 'bc_recently_viewed' ), '[]', 'cookie not read again once storage exists' );
	assert.deepEqual( later.requests, [] );
} );

test( 'init_stays_hidden_with_nothing_to_show', async () => {
	const first = section( 9 );
	const win = fakeWindow( { sections: [ first ] } );
	await rv.init( win );
	assert.deepEqual( win.requests, [], 'no request for a first visit' );
	assert.equal( first.hasAttribute( 'hidden' ), true );
	assert.equal( win.store.get( 'bc_recently_viewed' ), '[9]' );

	const gone = section( 9 );
	const win2 = fakeWindow( { sections: [ gone ], stored: '[5]', products: [] } );
	await rv.init( win2 );
	assert.equal( win2.requests.length, 1 );
	assert.equal( gone.hasAttribute( 'hidden' ), true, 'deleted/private products leave it hidden' );

	const failed = section( 9 );
	const win3 = fakeWindow( { sections: [ failed ], stored: '[5]', ok: false } );
	await rv.init( win3 );
	assert.equal( failed.hasAttribute( 'hidden' ), true, 'HTTP error leaves it hidden' );
} );

test( 'init_is_a_noop_without_storage', async () => {
	const s = section( 9 );
	const win = fakeWindow( { sections: [ s ], storageThrows: true, cookie: 'woocommerce_recently_viewed=11' } );
	assert.equal( await rv.init( win ), false );
	assert.deepEqual( win.requests, [] );
	assert.deepEqual( win.cookieWrites, [] );
	assert.equal( s.hasAttribute( 'hidden' ), true );
} );
