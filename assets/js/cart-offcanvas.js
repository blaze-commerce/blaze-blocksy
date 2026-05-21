/**
 * Cart Off-Canvas — heading count sync (CU-86exbe71m).
 *
 * Blocksy Companion Pro renders the cart drawer heading once on page load
 * (it's NOT one of the WooCommerce cart fragments). Our gettext filter in
 * inc/woocommerce.php injects an initial count, but when the cart contents
 * change (qty stepper, remove item, add to cart) the heading wouldn't
 * update on its own.
 *
 * This script keeps `.ct-cart-count-number` inside the drawer heading in
 * lockstep with Blocksy's authoritative count fragment `.ct-dynamic-count-cart`
 * which IS a registered cart fragment and re-renders on every cart change.
 *
 * No jQuery — listens to native `wc_fragments_refreshed` and a fallback
 * MutationObserver on the dynamic count badge.
 *
 * @package Blocksy_Child
 */
(function () {
	"use strict";

	var HEADING_COUNT_SEL = "#woo-cart-panel .ct-cart-count-number";
	var SOURCE_COUNT_SEL = ".ct-dynamic-count-cart";

	function readCount() {
		var src = document.querySelector(SOURCE_COUNT_SEL);
		if (!src) return null;
		var n = parseInt((src.textContent || "0").replace(/[^\d]/g, ""), 10);
		return isNaN(n) ? 0 : n;
	}

	function syncCount() {
		var target = document.querySelector(HEADING_COUNT_SEL);
		if (!target) return;
		var n = readCount();
		if (n === null) return;
		if (target.textContent !== String(n)) {
			target.textContent = String(n);
		}
	}

	// 1. WooCommerce cart-fragments refresh event (jQuery — but we only need the event name)
	if (window.jQuery) {
		window.jQuery(document.body).on("wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart", syncCount);
	}

	// 2. Fallback: observe the canonical count badge for any DOM mutation
	var srcBadge = document.querySelector(SOURCE_COUNT_SEL);
	if (srcBadge && "MutationObserver" in window) {
		new MutationObserver(syncCount).observe(srcBadge, {
			characterData: true,
			childList: true,
			subtree: true,
		});
	}

	// 3. Initial sync after DOM is ready (gettext filter renders server-side
	//    so this is mostly defensive — handles cached pages where the
	//    server-rendered count is stale).
	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", syncCount);
	} else {
		syncCount();
	}
})();

/**
 * Mini cart unified scroll — wraps <ul.woocommerce-mini-cart> +
 * .ct-suggested-products--mini-cart in a single <div.bc-cart-scroll> so
 * cart items + suggested products share scroll on short viewports.
 * Footer (totals/buttons/secure badge) stays pinned via CSS flex-shrink.
 *
 * Without this, Blocksy's intrinsic overflow on the <ul> creates a tiny
 * inner scroll-box: only one cart row is visible while the suggested
 * carousel sits below at full height — cramped layout (Cam, screenshot
 * 2026-04-30). CU-86exbd8kg V3.
 */
(function () {
	"use strict";

	var WRAPPER_CLASS = "bc-cart-scroll";

	function wrapScrollItems() {
		var panel = document.getElementById("woo-cart-panel");
		if (!panel) return;
		if (panel.querySelector("." + WRAPPER_CLASS)) return; // already wrapped

		var ul = panel.querySelector("ul.woocommerce-mini-cart");
		if (!ul) return; // empty cart state — nothing to wrap

		var suggested = panel.querySelector(".ct-suggested-products--mini-cart");
		var parent = ul.parentNode;
		var wrapper = document.createElement("div");
		wrapper.className = WRAPPER_CLASS;
		parent.insertBefore(wrapper, ul);
		wrapper.appendChild(ul);
		if (suggested && suggested.parentNode === parent) {
			wrapper.appendChild(suggested);
		}
	}

	if (window.jQuery) {
		window.jQuery(document.body).on(
			"wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart",
			wrapScrollItems
		);
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", wrapScrollItems);
	} else {
		wrapScrollItems();
	}
})();
