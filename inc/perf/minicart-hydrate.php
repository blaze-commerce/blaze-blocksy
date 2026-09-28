<?php
/**
 * Mini-cart Hydrate — keep the empty-cart suggestions out of the critical
 * path, and skip the no-op first-of-session cart-fragments request.
 *
 * PURPOSE, part 1 — `<template>` hydration. The off-canvas cart panel is in
 * the footer of EVERY page, and its empty state (`inc/mini-cart-empty.php`)
 * renders a suggested-products carousel: several KB of HTML plus product
 * images early in DOM order, on every pageview, for a panel nobody has
 * opened. When this feature is on, `inc/mini-cart-empty.php` wraps that
 * markup in `<template class="bc-perf-minicart-template">` (inert: not
 * rendered, images not fetched), and the inline `bc-perf-minicart-hydrate`
 * script clones it into place on the first intent signal — hover/focus of a
 * cart trigger (`blocksy_child_perf_minicart_triggers` filter), first
 * scroll or click, or `requestIdleCallback` (4 s timeout) as a safety net.
 * After cloning it triggers Blocksy's `blocksy:frontend:init` so the
 * carousel mounts; a later fragments refresh that re-renders the empty
 * state is re-hydrated once intent has been shown.
 *
 * PURPOSE, part 2 — WC fragments seed. On a brand-new session
 * `cart-fragments.js` finds no cached fragments and fires one uncached
 * `?wc-ajax=get_refreshed_fragments` (~1 s of PHP on AW that the page cache
 * can never absorb) — for an empty cart, to replace markup with itself. A
 * `before` inline script on `wc-cart-fragments` seeds
 * `sessionStorage[wc_fragments_<hash>]` with a single no-op fragment
 * (`div.widget_shopping_cart_content`, absent from Blocksy's DOM) so Woo
 * takes its cached branch. AW's three-layer guard, all load-bearing, plus
 * a fourth for this theme:
 *   1. no JS-visible WooCommerce cart cookie (`document.cookie`);
 *   2. neither sessionStorage key already set (never overwrite);
 *   3. no non-empty cart hash in `localStorage` — the only signal that
 *      survives a browser restart, because `wp_woocommerce_session_*` is
 *      HttpOnly (proved on AW staging: without layer 3 a restored cart
 *      rendered EMPTY);
 *   4. no `div.widget_shopping_cart_content` in the DOM. AW's theme had none,
 *      so its no-op seed could never match anything; THIS theme's own
 *      off-canvas cart renders inside that element (`inc/woocommerce.php`,
 *      `assets/css/components/offcanvas.css`, `docs/patterns/offcanvas.md`),
 *      and seeding would replace the empty state — and the `<template>`
 *      this module hydrates — with an empty div. `wc-cart-fragments`
 *      prints in the footer, so the check runs against the parsed DOM.
 *      Where the element exists the seed is simply skipped (one normal
 *      fragments request, as without this feature).
 * Any doubt = no seed = one normal fragments request. Add-to-cart is
 * unaffected (its response carries its own fragments + hash).
 *
 * PROMOTED FROM: `bc-site-customizations/sites/austinnaturalmattress/custom/mini-cart-recommendations-hydrate.php`
 * (hydrate triggers + idle fallback) with this theme's `inc/mini-cart-empty.php`
 * as the template source; the fragments seed from
 * `bc-site-customizations/sites/alternateworlds/custom/custom.php`
 * (`wc_fragments` seed, storage keys computed in PHP exactly as
 * `WC_Frontend_Scripts` does).
 *
 * MEASURED EVIDENCE: Austin mini-cart `<template>` removed 6 eager images
 * site-wide (and ~8.7 KB of HTML off every pageview, per the source); AW
 * fragments seed removed the 0.95–1.04 s uncached `get_refreshed_fragments`
 * request on first pageviews.
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: nothing client-specific. Layer 4
 * means the seed only ever applies on pages with no
 * `div.widget_shopping_cart_content` (e.g. a site whose cart UI is
 * Blocksy's own off-canvas without this theme's widget wrapper); elsewhere
 * the hydration half still works and the seed is inert.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The cart-trigger selector list that signals intent to open the cart.
 *
 * @return string
 */
function blocksy_child_perf_minicart_triggers(): string {
	/**
	 * @param string $selectors Default '.ct-cart-item, [data-toggle-panel="#woo-cart-panel"], .ct-cart-trigger, .cart-customlocation a'.
	 */
	return (string) apply_filters(
		'blocksy_child_perf_minicart_triggers',
		'.ct-cart-item, [data-toggle-panel="#woo-cart-panel"], .ct-cart-trigger, .cart-customlocation a'
	);
}

/**
 * The hydrate script body.
 *
 * @param string $triggers CSS selector list (JSON-encoded into the script).
 * @return string
 */
function blocksy_child_perf_minicart_hydrate_js( string $triggers ): string {
	return '(function(){'
		. 'var T=' . wp_json_encode( $triggers ) . ',intent=false;'
		. 'function hydrate(){'
		. "var l=document.querySelectorAll('template.bc-perf-minicart-template:not([data-bc-perf-hydrated])'),n=0;"
		. 'for(var i=0;i<l.length;i++){var t=l[i],p=t.parentNode;if(!t.content||!p)continue;'
		. "t.setAttribute('data-bc-perf-hydrated','1');p.insertBefore(t.content.cloneNode(true),t);n++;}"
		. "if(n&&window.ctEvents&&typeof window.ctEvents.trigger==='function'){try{window.ctEvents.trigger('blocksy:frontend:init');}catch(e){}}"
		. '}'
		// Once intent is shown the trigger listeners have done their job; later
		// fragment re-renders are re-hydrated from the jQuery events below.
		. "function go(){if(!intent){intent=true;document.removeEventListener('mouseover',onTrigger,{passive:true});document.removeEventListener('focusin',onTrigger);}hydrate();}"
		. 'function onTrigger(e){var el=e.target;if(T&&el&&el.closest){try{if(el.closest(T))go();}catch(x){}}}'
		. "document.addEventListener('mouseover',onTrigger,{passive:true});"
		. "document.addEventListener('focusin',onTrigger);"
		. "window.addEventListener('scroll',go,{once:true,passive:true});"
		. "window.addEventListener('click',go,{once:true,passive:true});"
		. "if('requestIdleCallback' in window){requestIdleCallback(go,{timeout:4000});}else{setTimeout(go,4000);}"
		. "if(window.jQuery){window.jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded removed_from_cart',function(){if(intent)hydrate();});}"
		. '})();';
}

/**
 * The fragments-seed script body, with AW's three guards plus the
 * widget-element guard (layer 4, see the file docblock).
 *
 * @param string $fragment_name sessionStorage key cart-fragments.js reads fragments from.
 * @param string $cart_hash_key sessionStorage/localStorage key for the cart hash.
 * @return string
 */
function blocksy_child_perf_minicart_fragments_seed_js( string $fragment_name, string $cart_hash_key ): string {
	// The stored value must itself be a JSON string — cart-fragments.js JSON.parse()s it.
	$seed = wp_json_encode( [ 'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content"></div>' ] );

	return 'try{'
		// Layer 1: any JS-visible WooCommerce cart cookie.
		. 'if(!/(^|;\s*)woocommerce_(items_in_cart|cart_hash)=/.test(document.cookie)'
		// Layer 4 (this theme): the off-canvas cart itself renders inside
		// div.widget_shopping_cart_content, which the no-op seed would
		// replace with an empty div. wc-cart-fragments prints in the footer,
		// so the DOM is parsed by now.
		. "&&!document.querySelector('div.widget_shopping_cart_content')"
		// Layer 2: never overwrite.
		. '&&!sessionStorage.getItem(' . wp_json_encode( $fragment_name ) . ')'
		. '&&!sessionStorage.getItem(' . wp_json_encode( $cart_hash_key ) . ')'
		// Layer 3: the only cart signal that survives a browser restart.
		. '&&!localStorage.getItem(' . wp_json_encode( $cart_hash_key ) . ')){'
		. 'sessionStorage.setItem(' . wp_json_encode( $fragment_name ) . ',' . wp_json_encode( $seed ) . ');'
		. '}}catch(e){}';
}

/**
 * `wp_enqueue_scripts` @30 — attach the seed `before` wc-cart-fragments.
 * Keys mirror `WC_Frontend_Scripts::get_script_data()` for that handle, so
 * there is no ordering dependency on `wc_cart_fragments_params`.
 *
 * @return void
 */
function blocksy_child_perf_minicart_fragments_seed(): void {
	if ( ! class_exists( 'WooCommerce' ) || ! wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
		return;
	}

	$suffix        = md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() );
	$fragment_name = (string) apply_filters( 'woocommerce_cart_fragment_name', 'wc_fragments_' . $suffix );
	$cart_hash_key = (string) apply_filters( 'woocommerce_cart_hash_key', 'wc_cart_hash_' . $suffix );

	wp_add_inline_script( 'wc-cart-fragments', blocksy_child_perf_minicart_fragments_seed_js( $fragment_name, $cart_hash_key ), 'before' );
}

/**
 * Register this module. Front-end only (Global Constraint 5; the script
 * helper itself also gates its printer).
 *
 * @return void
 */
function blocksy_child_perf_minicart_hydrate_register(): void {
	blocksy_child_perf_script(
		'bc-perf-minicart-hydrate',
		function () {
			return blocksy_child_perf_minicart_hydrate_js( blocksy_child_perf_minicart_triggers() );
		}
	);

	if ( ! blocksy_child_perf_is_frontend_request() ) {
		return;
	}

	add_action( 'wp_enqueue_scripts', 'blocksy_child_perf_minicart_fragments_seed', 30 );
}

blocksy_child_perf_minicart_hydrate_register();
