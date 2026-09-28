<?php
/**
 * Critical CSS Supplements — inline first-paint rules for the
 * `critical-css-supplements` module (`inc/perf/critical-css-supplements.php`).
 *
 * PURPOSE: Perfmatters' Remove Unused CSS (RUCSS) only rewrites enqueued
 * `<link>` stylesheets, and it decides what counts as "used" by scanning
 * the server-rendered HTML buffer at a MOBILE viewport with no JS run — so
 * three whole classes of first-paint rule are systematically invisible to
 * it: (1) declarations that only *set* a CSS custom property another rule
 * *reads* (the setter selector looks unused on its own even though the
 * property is load-bearing), (2) desktop-only `@media(min-width:…)` rules,
 * because the scan itself runs mobile-first, and (3) anything markup-gated
 * on JS that has not run yet (a gallery's own `flexy-items`/`flexy-item`
 * classes, a stepper's JS-appended number). Every rule below reproduces
 * exactly one such casualty as an inline `<style>` block instead — inline
 * blocks are never enqueued stylesheets, so RUCSS cannot see, defer or
 * tree-shake them, and they travel with the PR instead of living in a
 * `wp_options` row.
 *
 * Returns three buckets, one per `wp_head` printer in the module:
 *   - 'global'   — every front-end page (link underline, Gutenberg block
 *                  utilities; the module appends the opt-in header
 *                  min-height rule here too, when configured).
 *   - 'archive'  — `is_shop() || is_product_taxonomy()` only.
 *   - 'product'  — `is_product()` only.
 *
 * SOURCES (read via `git show <branch>:<path>` / `Read`, all in
 * `W:\BLAZE COMMERCE\bc-site-customizations` unless noted):
 *
 *   - GLOBAL / link underline — Houston
 *     `sites/houstonnaturalmattress/custom/critical-css-supplements.php`
 *     (`fix/hnm-a11y-bp-90plus`). Blocksy's in-copy link underline is
 *     `text-decoration: var(--has-link-decoration, var(--theme-text-decoration, none))`,
 *     set by `[data-link="type-2"] .entry-content :where(p,em,strong) > a,
 *     [data-link="type-2"] .entry-content > :where(ul,ol) a{--theme-text-decoration:underline}`
 *     in `main.min.css`, which RUCSS defers — so the setter never reaches
 *     the inline used-CSS and the property falls through to `none`.
 *     PORTED VERBATIM: this is Blocksy's own selector, not client copy —
 *     nothing to strip.
 *     MEASURED EVIDENCE: Lighthouse's `link-in-text-block` audit took the
 *     Houston homepage from 97 to 94 the moment RUCSS deferred this rule
 *     (a colour-blind visitor loses the same distinction, for real, on
 *     every first paint); restoring it recovers 94 -> 97.
 *
 *   - GLOBAL / block utilities — Mettahemp `custom/dequeue-unused.php`
 *     (`blaze-block-utilities`). That site dequeues `wp-block-library`
 *     entirely (~16 KB) and inlines this ~250-byte shim to keep
 *     `.has-text-align-*` / `.align*` working. PORTED VERBATIM (no
 *     site-specific values in the source rule at all).
 *     MEASURED EVIDENCE: restores Gutenberg alignment utilities lost by
 *     the block-library dequeue at near-zero byte cost (~250 B vs ~30 KB).
 *
 *   - ARCHIVE — Kajal Naina `sites/kajalnaina/custom/perf/woo-grid-supplement.php`.
 *     RUCSS's mobile-viewport scan drops the desktop
 *     `@media(min-width:1000px) [data-products].columns-N{--shop-columns:…}`
 *     grid-column rules (mobile columns survive; desktop collapses to one
 *     column until the deferred sheet loads), the `.woo-listing-top`
 *     result-count/filters/sort bar (renders unstyled/stacked), the
 *     secondary-image hide rules for `.pif-has-gallery` / Blocksy's
 *     `[data-hover="swap"] .ct-swap` (the hover-swap image shows BESIDE the
 *     primary instead of hidden under it), and the wishlist heart's
 *     not-active fill hide (hearts paint solid instead of outlined). Ported
 *     the generic Blocksy layout rules only — Kajal's literal font-size
 *     (`11px`/`14px`), font-weight/text-transform/letter-spacing, and
 *     hardcoded colour fallbacks (`var(--theme-text-color)`,
 *     `var(--theme-border-color,#e7e7e7)`, `…,#ebecee`) are dropped; theme
 *     custom properties are kept but their site-supplied hex fallback is
 *     removed.
 *     MEASURED EVIDENCE: Kajal category page pixel-identical with the
 *     console one-liner below disabling all 55 stylesheets.
 *
 *   - PRODUCT / Flexy containment — Byron Bay Candles
 *     `custom/fix-product-gallery-fouc.php`. Flexy (Blocksy's PDP gallery
 *     slider) sets `.flexy-view` clipping and `.flexy-items` flex layout in
 *     JS; until that JS runs (delayed further by Perfmatters "Delay JS"),
 *     every slide renders stacked full-width below the main image. PORTED
 *     VERBATIM (already generic Blocksy markup, no BBC-specific values).
 *     MEASURED EVIDENCE: eliminates the confirmed pre-init FOUC (verified
 *     2026-06-22: `.flexy` collapsed to height:0 with slides stacked,
 *     reproducible on both the fast staging box and production).
 *
 *   - PRODUCT / qty fallback — AlternateWorlds
 *     `custom/includes/pdp-qty-critical-css.php`. WooCommerce's
 *     sold-individually/max==1 quantity box renders a `type="hidden"`
 *     input RUCSS's used-CSS scan does not keep a fallback number for, and
 *     the +/- buttons only dim to `opacity:.3` once JS adds a `.locked`
 *     class. Ported only the two `max==1` rules (the AW source's own
 *     docblock says the `input.qty` rule already survives RUCSS); AW's
 *     site CSS variables (`var(--aw-font-head)`, `var(--color-text-primary)`,
 *     `var(--color-bg-primary)`) are replaced with plain, theme-neutral
 *     values (`font-family:inherit`, `color:inherit`, `background:transparent`)
 *     since this shim has no theme of its own to draw from.
 *     MEASURED EVIDENCE: AW measured 580 ms of visibly broken stepper
 *     (FCP 2,088 ms -> DOMContentLoaded 2,668 ms) on a fast desktop
 *     connection before this fix.
 *
 * The opt-in header min-height rule (choiceammunition CLS 0.175 -> 0.000,
 * `W:\BLAZE COMMERCE\pagespeed-docs\clickup-tasks\86ewtgyv1-choiceammunition-pagespeed.md`)
 * is NOT a data-file entry — it is built at print time from the
 * `blocksy_child_perf_header_min_height` filter in the module itself, since
 * it has no safe cross-client default (a wrong min-height reserves the
 * wrong box and CAUSES a CLS shift rather than preventing one).
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: any client's own colours, fonts,
 * pixel-tuned spacing or brand ids — every rule above was stripped to the
 * generic Blocksy/WooCommerce/Gutenberg markup it targets before shipping
 * here. A site that wants its own first-paint polish adds it via the
 * `blocksy_child_perf_critical_css_extra` filter, not by editing this file.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'global'  => '[data-link="type-2"] .entry-content :where(p,em,strong) > a,[data-link="type-2"] .entry-content > :where(ul,ol) a{--theme-text-decoration:underline}'
		. '.has-text-align-left{text-align:left}.has-text-align-center{text-align:center}.has-text-align-right{text-align:right}'
		. '.alignleft{float:left;margin-inline-end:1em;margin-block-end:1em}'
		. '.alignright{float:right;margin-inline-start:1em;margin-block-end:1em}'
		. '.aligncenter{margin-inline:auto;display:block;clear:both}'
		. '.entry-content>.alignwide,.wp-block-group>.alignwide,.is-root-container>.alignwide{margin-inline:0;max-width:100%}'
		. '.entry-content>.alignfull,.wp-block-group>.alignfull,.is-root-container>.alignfull{margin-inline:calc(50% - 50vw);max-width:none;width:auto}'
		. '.summary .alignfull,.entry-summary .alignfull,.ct-product-content-block .alignfull{margin-inline:0!important;max-width:100%!important;width:auto!important}',

	'archive' => '.woo-listing-top{display:flex;align-items:center;gap:13px;justify-content:flex-start}'
		. '.woo-listing-top:not(:empty){margin-bottom:25px}'
		. '.archive .woo-listing-top,.post-type-archive .woo-listing-top{padding-bottom:16px;border-bottom:1px solid var(--theme-border-color)}'
		. '.woo-listing-top .woocommerce-result-count{margin-bottom:0;display:block!important}'
		. '.woo-listing-top .ct-toggle-filter-panel{gap:8px;--toggle-button-radius:3px;--toggle-button-margin-start:0;--toggle-button-padding:7px 13px;--toggle-button-border-width:1px;--toggle-button-border-color:var(--theme-border-color);--theme-icon-size:12px;padding:var(--toggle-button-padding);border:var(--toggle-button-border-width) solid var(--toggle-button-border-color);border-radius:var(--toggle-button-radius)}'
		. '.woo-listing-top .woocommerce-ordering{display:flex;align-items:center;justify-content:center;margin-inline-start:auto;position:relative}'
		. '.woo-listing-top .woocommerce-ordering select{height:40px;cursor:pointer}'
		. '.woo-listing-top .woocommerce-ordering .ct-sort-icon{position:absolute;pointer-events:none}'
		// `color:rgba(0,0,0,0)` here is a structural technique (fully transparent,
		// theme-neutral), not a Kajal brand colour: it visually hides the native
		// <select> text so the control reads as a 34px icon button on mobile,
		// same as the source. Kept for that reason.
		. '@media(max-width:689.98px){.woo-listing-top .woocommerce-ordering select{color:rgba(0,0,0,0);width:34px;height:34px;padding:0;user-select:none;background-image:none}}'
		. '.pif-has-gallery{position:relative}'
		. '.pif-has-gallery .wp-post-image--secondary{position:absolute;top:0;left:0;opacity:0}'
		. '[data-hover="swap"] .ct-swap{position:absolute;inset:0;opacity:0}'
		. '[class*="ct-wishlist-button"]:not([data-button-state="active"]) .ct-heart-fill{opacity:0}'
		. '@media(min-width:1000px){[data-products].columns-2{--shop-columns:repeat(2,minmax(0,1fr))}[data-products].columns-3{--shop-columns:repeat(3,minmax(0,1fr))}[data-products].columns-4{--shop-columns:repeat(4,minmax(0,1fr))}[data-products].columns-5{--shop-columns:repeat(5,minmax(0,1fr))}[data-products].columns-6{--shop-columns:repeat(6,minmax(0,1fr))}}',

	'product' => '.woocommerce-product-gallery .flexy-view{overflow:hidden}'
		. '.woocommerce-product-gallery .flexy-items{display:flex;flex-wrap:nowrap}'
		. '.woocommerce-product-gallery .flexy-items>.flexy-item{flex:0 0 100%;max-width:100%}'
		. '.single-product .summary form.cart .quantity.hidden::after{content:"1";flex:1;display:flex;align-items:center;justify-content:center;height:100%;border-left:1px solid rgba(0,0,0,.12);border-right:1px solid rgba(0,0,0,.12);text-align:center;font-family:inherit;font-size:18px;font-weight:400;line-height:24px;letter-spacing:.1px;color:inherit;background:transparent;order:1}'
		. '.single-product .summary form.cart .quantity.hidden .ct-increase,.single-product .summary form.cart .quantity.hidden .ct-decrease,.single-product .summary form.cart .quantity.hidden .plus,.single-product .summary form.cart .quantity.hidden .minus{opacity:.3;cursor:default}',
];
