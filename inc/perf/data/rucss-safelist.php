<?php
/**
 * RUCSS Safelist — selectors that must survive Perfmatters' Remove Unused
 * CSS regardless of what its server-rendered-HTML scan sees.
 *
 * PURPOSE: data file for the `rucss-safelist` module
 * (`inc/perf/rucss-safelist.php`), which merges this list into
 * `perfmatters_rucss_excluded_selectors`. Perfmatters' RUCSS decides what
 * to keep in the inlined used-CSS by scanning the server-rendered HTML
 * buffer — no JS runs. Anything a selector needs that only exists after
 * JS has run (a search flyout, a hydrated mini-cart, a slider's own
 * classes, a variation swap) is invisible to that scan and gets pruned,
 * so the first paint of that element is unstyled until the deferred
 * stylesheet arrives on interaction.
 *
 * HOW PERFMATTERS MATCHES A SELECTOR (copied from
 * `bc-site-customizations/sites/alternateworlds/custom/search-results/rucss-selectors.php`,
 * which documents it from the plugin's own source):
 *
 *     preg_match( '#(' . preg_quote( $value ) . ')(?=\s|\.|\:|,|\[|$)#', $selector )
 *
 * Each listed value is matched as a substring of the whole selector text,
 * followed by a lookahead for whitespace, `.`, `:`, `,`, `[`, or end of
 * string. Two consequences:
 *
 *   - NO BEM-DESCENDANT COVERAGE. The lookahead deliberately excludes `-`
 *     and `_`, so a value does not cover its own BEM descendants: `.foo`
 *     matches `.foo`, `.foo:hover`, `.foo.bar` and `.foo .child`, but NOT
 *     `.foo__body` or `.foo--modifier`. There is no prefix wildcard —
 *     every modifier and element needs its own entry.
 *   - WHOLE-SELECTOR-TEXT MATCH, NOT JUST THE FIRST COMPOUND. Because the
 *     match runs against the whole selector string, listing `.foo` also
 *     rescues `.foo .add_to_cart_button { ... }` even though
 *     `add_to_cart_button` is never listed itself — one ancestor entry can
 *     cover many descendant rules written against it.
 *
 * PROMOTED FROM: the 111-selector Perfmatters known-good row documented in
 * `W:\BLAZE COMMERCE\pagespeed-docs\analysis\03-extraction-houston-docs-and-perfmatters-row.md`
 * §3 (FiboSearch, Blocksy cart panel, WooCommerce/card selectors — the set
 * Houston and Austin ship set-identical), plus additions covering carousel
 * libraries (owl/GSPB/swiper), subscriptions (WCSATT), the WooCommerce
 * mini-cart/cross-sell widgets, Gutenberg alignment utility classes and one
 * generic JS-toggled loading-state class — none of which are in the served
 * HTML on first paint on at least one client site.
 *
 * MEASURED EVIDENCE: none directly attaches to this file — it is a static
 * list. The FiboSearch/cart-panel/WooCommerce-card subset carries the
 * Houston homepage evidence in `perfmatters-filters.php`'s docblock (this
 * same PR); the AlternateWorlds card-grid subset (a different, per-site
 * addition, not carried here) is what taught this project the matcher
 * semantics documented above, per the file cited.
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: a per-site addition to this list
 * (e.g. AlternateWorlds' `AW_SR_RUCSS_CARD_SELECTORS`, gated to one
 * template via `template_redirect`) stays in that site's own
 * `bc-site-customizations` filter on `blocksy_child_perf_rucss_selectors`
 * — this file is the generic, cross-client baseline only.
 *
 * @package Blocksy_Child
 */

// FiboSearch (Ajax Search for WooCommerce) flyout — 57 selectors. Rendered
// entirely by the plugin's own JS after the input is focused/typed into;
// none of this markup exists in the server-rendered HTML.
$fibosearch = [
	'.dgwt-wcas-search-wrapp',
	'.dgwt-wcas-search-wrapp-mobile',
	'.dgwt-wcas-search-form',
	'.dgwt-wcas-sf-wrapp',
	'.dgwt-wcas-search-input',
	'.dgwt-wcas-search-submit',
	'.dgwt-wcas-style-solaris',
	'.dgwt-wcas-style-solaris .dgwt-wcas-search-input',
	'.dgwt-wcas-style-pirx',
	'.dgwt-wcas-style-pirx .dgwt-wcas-search-input',
	'.dgwt-wcas-has-submit',
	'.dgwt-wcas-search-wrapp .dgwt-wcas-search-input',
	'input.dgwt-wcas-search-input',
	'input[type=search].dgwt-wcas-search-input',
	'.dgwt-wcas-sf-wrapp input[type=search]',
	'.dgwt-wcas-search-wrapp .dgwt-wcas-sf-wrapp input[type=search]',
	'.dgwt-wcas-search-wrapp .dgwt-wcas-sf-wrapp input[type=search].dgwt-wcas-search-input',
	'.dgwt-wcas-search-icon',
	'.dgwt-wcas-search-icon-arrow',
	'a.dgwt-wcas-search-icon',
	'.dgwt-wcas-ico-magnifier',
	'.dgwt-wcas-ico-magnifier-handler',
	'.dgwt-wcas-layout-icon',
	'.dgwt-wcas-layout-icon-open',
	'.dgwt-wcas-layout-icon-flexible',
	'.dgwt-wcas-layout-icon-flexible-loaded',
	'.dgwt-wcas-layout-bar',
	'.dgwt-wcas-layout-classic',
	'.dgwt-wcas-suggestions-wrapp',
	'.dgwt-wcas-suggestions',
	'.dgwt-wcas-suggestion',
	'.dgwt-wcas-si',
	'.dgwt-wcas-sp',
	'.dgwt-wcas-sd',
	'.dgwt-wcas-st',
	'.dgwt-wcas-content-wrapp',
	'.dgwt-wcas-details-wrapp',
	'.dgwt-wcas-details-inner',
	'.dgwt-wcas-product-details',
	'.dgwt-wcas-overlay',
	'.dgwt-wcas-overlay-mobile',
	'.dgwt-wcas-close',
	'.dgwt-wcas-preloader',
	'.dgwt-wcas-loader',
	'.dgwt-wcas-no-results',
	'.dgwt-wcas-overlay-mobile-on',
	'.dgwt-wcas-open-om',
	'.dgwt-wcas-om-bar',
	'.dgwt-wcas-om-bar-form',
	'.dgwt-wcas-search-active',
	'.dgwt-wcas-active',
	'.dgwt-wcas-search-filled',
	'.dgwt-wcas-search-focused',
	'.dgwt-wcas-enable-mobile-form',
	'.dgwt-wcas-ico',
	'svg.dgwt-wcas-ico-magnifier',
	'[class*="dgwt-wcas"]',
];

// Blocksy's cart panel / off-canvas — 19 selectors. Toggled open by JS;
// only its trigger button is in the server-rendered HTML.
$blocksy_cart_panel = [
	'.ct-header-cart',
	'.ct-cart-panel',
	'.ct-panel',
	'.ct-panel.active',
	'.ct-offcanvas',
	'.ct-bag-container',
	'.ct-cart-content',
	'.ct-cart-item',
	'.ct-media-container',
	'.ct-product-title',
	'.ct-quantity',
	'.ct-price-container',
	'.ct-cart-total',
	'.ct-cart-count',
	'.ct-proceed-to-checkout',
	'.ct-toggle-close',
	'[data-behaviour="modal"]',
	'[data-panel]',
	'[data-id="cart"]',
];

// WooCommerce product cards + Blaze/Blocksy card chrome — 35 selectors.
// Product grids are frequently hydrated/re-rendered by AJAX (filters,
// swatches, quick view) after the initial scan.
$woocommerce_cards = [
	'.product',
	'.products',
	'.product-type-variable',
	'.product_type_variable',
	'.product_type_simple',
	'.product_type_grouped',
	'.woocommerce',
	'.woocommerce-LoopProduct-link',
	'.woocommerce-loop-product__link',
	'.woocommerce-loop-product__title',
	'.woocommerce-Price-amount',
	'.woocommerce-Price-currencySymbol',
	'.woocommerce-notices-wrapper',
	'.price',
	'.regular-price',
	'.sale-price',
	'.amount',
	'.onsale',
	'.instock',
	'.outofstock',
	'.purchasable',
	'.taxable',
	'.shipping-taxable',
	'.add_to_cart_button',
	'.button',
	'.wp-post-image',
	'.has-post-thumbnail',
	'.screen-reader-text',
	'.ct-woo-badges',
	'.ct-woo-card-actions',
	'.ct-woo-card-extra',
	'.ct-wishlist-button-archive',
	'.has-hover-effect',
	'.blaze-product-carousel-wrapper',
	'.wp-block-blaze-blocksy-product-carousel',
];

// Gutenberg block alignment utility classes — applied by the block editor
// to whatever block is above/below the fold on a given page; not tied to
// any one template, so RUCSS's per-page scan is an unreliable signal for
// them.
$gutenberg_utilities = [
	'.has-text-align-left',
	'.has-text-align-center',
	'.has-text-align-right',
	'.alignleft',
	'.alignright',
	'.aligncenter',
	'.alignwide',
	'.alignfull',
];

// Carousel/slider libraries (Owl Carousel, GreenShift product block,
// generic swiper pagination) — the slide track and nav/dot markup are
// built by each library's own JS after load, not present in the initial
// HTML.
$carousels = [
	'.owl-carousel',
	'.owl-stage',
	'.owl-stage-outer',
	'.owl-item',
	'.owl-nav',
	'.owl-dots',
	'.owl-dot',
	'.owl-theme',
	'.gspb_carousel',
	'.gspb-product-carousel',
	'.swiper-pagination-bullet',
];

// WooCommerce Subscribe All The Things (WCSATT) + WooCommerce Subscriptions
// variation UI — rendered on variation change / AJAX, not on first load.
$subscriptions = [
	'.wcsatt-options',
	'.wcsatt-options-product',
	'.wcsatt-options-wrapper',
	'.wsc_wrap',
	'.single_variation_wrap',
	'.subscription_options',
	'.woocommerce-subscriptions-wrapper',
];

// Mini-cart / cross-sell / "added to cart" widgets — hydrated on
// add-to-cart via AJAX (`.mini_cart_item` etc. are never in the initial
// page HTML for a visitor who has not added anything yet).
$mini_cart = [
	'.product_list_widget',
	'.product_list_widget li',
	'.woocommerce-mini-cart',
	'.woocommerce-mini-cart-item',
	'.mini_cart_item',
	'.woocommerce-mini-cart__total',
	'.woocommerce-mini-cart__buttons',
	'.cart_list',
	'.recommended-product-item-stacked',
	'.recommendations-products',
	'.product-price-quantity',
	'.added_to_cart',
	'.added_to_cart.wc-forward',
	'.woocommerce-message',
	'#woo-cart-panel',
];

// Generic JS-toggled runtime-state class — added/removed by many different
// scripts (spinners, AJAX panels) with no single owning component.
$runtime_state = [
	'.loading',
];

return array_merge(
	$fibosearch,
	$blocksy_cart_panel,
	$woocommerce_cards,
	$gutenberg_utilities,
	$carousels,
	$subscriptions,
	$mini_cart,
	$runtime_state
);
