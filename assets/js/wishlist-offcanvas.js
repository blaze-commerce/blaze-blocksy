/**
 * Off-Canvas Wishlist Panel — Client-side rendering.
 *
 * Reads preloaded product data from bcWishlistData (output by PHP).
 * Renders panel content instantly — no AJAX for initial load.
 * Only fetches from server when a NEW product is added that isn't
 * in the preloaded data.
 *
 * No jQuery. No ctEvents dependency for wishlist changes.
 * Uses MutationObserver on the counter badge for reliable detection.
 *
 * @package Blocksy_Child
 */
(function () {
	"use strict";

	// Audit P1 2026-05-08: guard for the duplicate setTimeout(openPanel)
	// calls below, so rapid wishlist-add bursts collapse into one open.
	var openPanelTimer = null;

	var PANEL_SEL = "#woo-wishlist-panel";
	var CONTENT_SEL = PANEL_SEL + " .ct-panel-content-inner";

	// Local product data cache — populated from preloaded JSON.
	var productCache = {};
	var data = window.bcWishlistData || { items: [], isGuest: true, accountUrl: "/my-account/", ajaxUrl: "/wp-admin/admin-ajax.php" };

	// Constants — must be declared before syncFromBlocksyState calls renderPanel.
	var REMOVE_ICON = '<svg class="ct-icon" width="10" height="10" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.6,0l0,1.2H1.2v2.4h21.6V1.2h-8.4l0-1.2H9.6z M2.8,6l1.8,15.9C4.8,23.1,5.9,24,7.1,24h9.9c1.2,0,2.2-0.9,2.4-2.1L21.2,6H2.8z"></path></svg>';
	var GUEST_TEXT = "Guest favorites are only saved to your device for 7 days, or until you clear your cache. Sign in or create an account to hang on to your picks.";

	// Figma "card grid" layout (opt-in per site via bcWishlistData.cardLayout).
	// When on, items render as a 2-col card grid with a "Remove" text control
	// (CSS uppercases it). When off (Byron Bay) the original list-row icon
	// template is used, unchanged. The remove BUTTON + its data-product-id +
	// aria-label are identical either way, so the remove click handler and
	// server sync work the same in both layouts.
	//
	// Read at RENDER time, not init: the preloaded `bcWishlistData` <script>
	// prints AFTER this enqueued footer script, so `data.cardLayout` is not
	// yet defined when this file first executes. Re-reading window.bcWishlistData
	// inside renderPanel() picks up the real flag once it lands.
	function usesCards() {
		return !!( window.bcWishlistData && window.bcWishlistData.cardLayout );
	}

	// Populate cache from preloaded data.
	data.items.forEach(function (item) {
		productCache[item.id] = item;
	});

	// --- Render on page load ---
	// Check if Blocksy has more items than our preloaded cache (guest cookie sync issue).
	// If so, fetch missing products before rendering.
	syncFromBlocksyState(false);

	// ==========================================================================
	// Panel open/close
	// ==========================================================================

	function openPanel() {
		var panel = document.querySelector(PANEL_SEL);
		if (!panel) return;

		panel.removeAttribute("inert");

		if (typeof ctEvents !== "undefined") {
			// We pass clickOutside: false because Blocksy's built-in clickOutside
			// listener treats clicks INSIDE the panel as outside-clicks (probably
			// because Blocksy's flexy carousel translates child items outside
			// the panel's bounding box, and Blocksy's detection doesn't
			// closest()-walk reliably). We bind our own clickOutside listener
			// below that uses closest() against the panel selector.
			ctEvents.trigger("ct:overlay:handle-click", {
				event: new Event("click"),
				options: {
					container: panel,
					clickOutside: false,
					focus: true,
					isModal: false
				}
			});
		} else {
			panel.classList.add("active");
			document.body.style.overflow = "hidden";
		}

		bindClickOutsideOnce();
	}

	var clickOutsideBound = false;
	function bindClickOutsideOnce() {
		if (clickOutsideBound) return;
		clickOutsideBound = true;
		document.addEventListener("click", function (e) {
			var panel = document.querySelector(PANEL_SEL);
			if (!panel || !panel.classList.contains("active")) return;
			// The panel element is full-viewport with a dim background; the
			// actual drawer is .ct-panel-inner. Click is OUTSIDE the drawer
			// when target is not inside .ct-panel-inner — even if it's
			// within the panel's dim backdrop.
			if (e.target.closest(PANEL_SEL + " .ct-panel-inner")) return;
			// Also ignore clicks inside the heart icon itself (which would
			// re-open immediately after closing).
			if (e.target.closest(".ct-header-wishlist")) return;
			// Click was on the dim backdrop or truly outside — close.
			closePanel();
		}, false);
	}

	function closePanel() {
		var panel = document.querySelector(PANEL_SEL);
		if (!panel) return;
		panel.classList.remove("active");
		panel.setAttribute("inert", "");
		document.body.style.overflow = "";
	}

	// ==========================================================================
	// Click handlers (capture phase)
	// ==========================================================================

	document.addEventListener("click", function (e) {
		// Header wishlist icon — open panel.
		if (e.target.closest(".ct-header-wishlist")) {
			e.preventDefault();
			e.stopImmediatePropagation();
			openPanel();
			return;
		}

		// Suggested products arrows — let Blocksy's flexy.js handle the slide.
		// Our wrapper has the substring "ct-suggested-products" so flexy's
		// closest() lookup finds the arrows. We intentionally do NOT intercept here:
		// our previous capture-phase stopImmediatePropagation killed flexy's
		// click handlers (CU-86exbd883 wishlist arrow audit, 2026-04-28). Blocksy
		// only closes the overlay on clicks OUTSIDE the panel via its
		// ct:overlay:handle-click clickOutside listener; arrow clicks inside the
		// panel don't trigger close.

		// Suggested products links — let them work, don't close panel.
		if (e.target.closest(PANEL_SEL + " .ct-wishlist-suggested a")) {
			e.stopImmediatePropagation();
			return;
		}

		// Close button.
		if (e.target.closest(PANEL_SEL + " .ct-toggle-close")) {
			e.preventDefault();
			closePanel();
			return;
		}

		// Remove item inside panel.
		var removeBtn = e.target.closest(PANEL_SEL + " .ct-wishlist-remove");
		if (removeBtn) {
			e.preventDefault();
			var productId = parseInt(removeBtn.dataset.productId, 10);
			var item = removeBtn.closest(".ct-wishlist-item");

			// Animate out.
			if (item) {
				item.style.transition = "opacity 0.2s";
				item.style.opacity = "0";
				setTimeout(function () {
					item.remove();

					// Remove from local cache.
					delete productCache[productId];

					// Update counter + check empty.
					updateCount();
					checkEmpty();
				}, 200);
			}

			// Sync removal to server (background, non-blocking).
			syncRemoveToServer(productId);
			return;
		}
	}, true);

	// Bubble-phase: arrow clicks slide the carousel (Blocksy flexy runs in
	// target phase) — stop propagation here so the click never reaches
	// Blocksy's ct:overlay:handle-click clickOutside listener that would
	// otherwise close the wishlist drawer. Same for product-link clicks.
	document.addEventListener("click", function (e) {
		if (
			e.target.closest(PANEL_SEL + " .ct-arrow-prev") ||
			e.target.closest(PANEL_SEL + " .ct-arrow-next") ||
			e.target.closest(PANEL_SEL + " .ct-wishlist-suggested a")
		) {
			e.stopPropagation();
		}
	}, false);

	// ESC closes panel.
	document.addEventListener("keydown", function (e) {
		if (e.key === "Escape") closePanel();
	});

	// ==========================================================================
	// MutationObserver — watch counter badge for wishlist changes
	// ==========================================================================

	var badge = document.querySelector(".ct-dynamic-count-wishlist");

	if (badge) {
		// Audit P1 2026-05-08 (a11y, WCAG 4.1.3 Status Messages): announce
		// add/remove to screen readers without forcing focus or panel-open.
		document.querySelectorAll(".ct-dynamic-count-wishlist").forEach(function (b) {
			b.setAttribute("aria-live", "polite");
			b.setAttribute("aria-atomic", "true");
		});

		var lastCount = parseInt(badge.getAttribute("data-count") || "0", 10);

		var observer = new MutationObserver(function () {
			var newCount = parseInt(badge.getAttribute("data-count") || "0", 10);

			if (newCount !== lastCount) {
				var wasAdded = newCount > lastCount;
				lastCount = newCount;

				// Sync local data with Blocksy's state.
				syncFromBlocksyState(wasAdded);
			}
		});

		observer.observe(badge, {
			attributes: true,
			attributeFilter: ["data-count"],
			childList: true,
			characterData: true
		});
	}

	// ==========================================================================
	// Client-side rendering
	// ==========================================================================

	function renderPanel() {
		var inner = document.querySelector(CONTENT_SEL);
		if (!inner) return;

		try {
			var items = getWishlistIds();
			var isGuest = !document.body.classList.contains("logged-in");
			var html = "";

			// Toggle the panel's empty/filled state class so the server-rendered
			// "You May Also Like" block (category grid vs product carousel) shows
			// the right variant. No-op when card layout is off (classes unused).
			setPanelEmptyState(items.length === 0);

			if (items.length === 0) {
				// Empty state — centered message.
				html = '<div class="ct-wishlist-empty">'
					+ '<p class="ct-wishlist-empty-message">Your Wishlist is Empty</p>'
					+ '</div>';

				// Logged-in: show Continue Shopping. Guest notice is added below.
				if (!isGuest) {
					html += '<div class="ct-wishlist-continue">'
						+ '<a href="/shop/" class="ct-wishlist-continue-btn">Continue Shopping</a>'
						+ '</div>';
				}
			} else {
				// Wishlist items.
				var useCards = usesCards();
				var removeInner = useCards ? '<span class="ct-wishlist-remove-label">Remove</span>' : REMOVE_ICON;
				html = '<ul class="woocommerce-mini-cart ct-wishlist-items' + (useCards ? ' ct-wishlist-items--cards' : '') + '">';

				items.forEach(function (id) {
					var product = productCache[id];
					if (!product) return;

					html += '<li class="woocommerce-mini-cart-item ct-wishlist-item" data-product-id="' + product.id + '">'
						+ '<a href="' + product.url + '" class="ct-media-container">' + product.image + '</a>'
						+ '<div class="ct-wishlist-item-info">'
						+ '<a href="' + product.url + '">' + escapeHtml(product.name) + '</a>'
						+ '<span class="price">' + product.price + '</span>'
						+ '</div>'
						+ '<button class="ct-wishlist-remove" data-product-id="' + product.id + '" aria-label="Remove from wishlist">'
						+ removeInner
						+ '</button>'
						+ '</li>';
				});

				html += '</ul>';
			}

			// Guest prompt — always show for guests (empty or not).
			if (isGuest) {
				html += '<div class="ct-wishlist-guest-notice">'
					+ '<p>' + GUEST_TEXT + '</p>'
					+ '<a href="' + data.accountUrl + '" class="ct-wishlist-signup-btn">Sign Up</a>'
					+ '</div>';
			}

			inner.innerHTML = html;

			// Update heading counter.
			updateHeadingCount(items.length);
		} catch (err) {
			inner.innerHTML = '<p class="ct-wishlist-error">Could not load wishlist. Please refresh the page.</p>';
		}
	}

	/**
	 * Update the panel heading counter (e.g., "Wishlist (3)").
	 */
	function updateHeadingCount(count) {
		var countEl = document.querySelector(PANEL_SEL + " .ct-wishlist-count-number");
		if (countEl) countEl.textContent = count;
	}

	/**
	 * Toggle the panel's empty/filled state class. Card layout uses it (CSS)
	 * to swap the empty-state category grid for the filled-state suggested
	 * products carousel. Harmless when card layout is off — the classes are
	 * simply unstyled.
	 */
	function setPanelEmptyState(isEmpty) {
		var panel = document.querySelector(PANEL_SEL);
		if (!panel) return;
		panel.classList.toggle("ct-wishlist-state-empty", isEmpty);
		panel.classList.toggle("ct-wishlist-state-filled", !isEmpty);
	}

	// ==========================================================================
	// Sync with Blocksy state
	// ==========================================================================

	/**
	 * Read current wishlist IDs — multiple sources, most reliable first.
	 *
	 * Priority:
	 * 1. Guest cookie (blc_products_wish_list) — always current for guests
	 * 2. ct_localizations (may be stale on page load, but updated after interactions)
	 * 3. Preloaded data (fallback)
	 */
	function getWishlistIds() {
		// 1. Try guest cookie first.
		var cookieIds = getIdsFromCookie();
		if (cookieIds.length > 0) {
			return cookieIds;
		}

		// 2. Try ct_localizations (works for logged-in after Blocksy lazy-fetch).
		if (window.ct_localizations && window.ct_localizations.blc_ext_wish_list && window.ct_localizations.blc_ext_wish_list.list) {
			var ctIds = window.ct_localizations.blc_ext_wish_list.list.items.map(function (i) {
				return i.id;
			});
			if (ctIds.length > 0) {
				return ctIds;
			}
		}

		// 3. Fallback: preloaded data.
		return data.items.map(function (i) {
			return i.id;
		});
	}

	/**
	 * Read wishlist IDs from the blc_products_wish_list cookie.
	 * Cookie format: JSON string { items: [{id: 123}, {id: 456}], ... }
	 */
	function getIdsFromCookie() {
		try {
			var match = document.cookie.match(/(?:^|;\s*)blc_products_wish_list=([^;]*)/);
			if (!match) return [];

			var decoded = decodeURIComponent(match[1]);
			var parsed = JSON.parse(decoded);

			if (parsed && parsed.items && Array.isArray(parsed.items)) {
				return parsed.items.map(function (i) { return i.id; });
			}
		} catch (err) {}
		return [];
	}

	/**
	 * Sync local cache with current wishlist state.
	 * Fetches product data for any IDs not already cached.
	 * For logged-in users with no cookie, uses Blocksy's AJAX endpoint first.
	 */
	function syncFromBlocksyState(wasAdded) {
		var isLoggedIn = document.body.classList.contains("logged-in");
		var currentIds = getWishlistIds();

		// Logged-in users: cookie may be empty. If we have no IDs but badge
		// shows items, fetch the full wishlist from Blocksy's endpoint.
		if (isLoggedIn && currentIds.length === 0) {
			var badge = document.querySelector(".ct-dynamic-count-wishlist");
			var badgeCount = badge ? parseInt(badge.getAttribute("data-count") || "0", 10) : 0;

			if (badgeCount > 0) {
				fetchFullWishlist(function (ids) {
					fetchMissingAndRender(ids, wasAdded);
				});
				return;
			}
		}

		fetchMissingAndRender(currentIds, wasAdded);
	}

	/**
	 * Fetch full wishlist from Blocksy's AJAX endpoint (logged-in users).
	 */
	function fetchFullWishlist(callback) {
		var xhr = new XMLHttpRequest();
		xhr.open("POST", data.ajaxUrl + "?action=blc_ext_wish_list_get_all_likes");
		xhr.setRequestHeader("Content-Type", "application/json");
		xhr.onload = function () {
			if (xhr.status === 200) {
				try {
					var resp = JSON.parse(xhr.responseText);
					if (resp.success && resp.data && resp.data.likes && resp.data.likes.items) {
						// Update ct_localizations so future reads are correct.
						if (window.ct_localizations && window.ct_localizations.blc_ext_wish_list) {
							window.ct_localizations.blc_ext_wish_list.list = resp.data.likes;
						}
						var ids = resp.data.likes.items.map(function (i) { return i.id; });
						callback(ids);
						return;
					}
				} catch (err) {}
			}
			callback([]);
		};
		xhr.onerror = function () { callback([]); };
		xhr.send();
	}

	/**
	 * Fetch missing product details and render panel.
	 */
	function fetchMissingAndRender(currentIds, wasAdded) {
		var missingIds = currentIds.filter(function (id) {
			return !productCache[id];
		});

		if (missingIds.length > 0) {
			var fetched = 0;
			var total = missingIds.length;

			missingIds.forEach(function (id) {
				fetchProduct(id, function (product) {
					if (product) {
						productCache[product.id] = product;
					}
					fetched++;
					if (fetched === total) {
						renderPanel();
						if (wasAdded) {
							if (openPanelTimer) clearTimeout(openPanelTimer);
				openPanelTimer = setTimeout(openPanel, 300);
						}
					}
				});
			});
		} else {
			renderPanel();
			if (wasAdded) {
				if (openPanelTimer) clearTimeout(openPanelTimer);
				openPanelTimer = setTimeout(openPanel, 300);
			}
		}
	}

	/**
	 * Fetch a single product's data from the server.
	 * Only called for products not in the preloaded cache.
	 */
	function fetchProduct(productId, callback) {
		var xhr = new XMLHttpRequest();
		xhr.open("POST", data.ajaxUrl);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.onload = function () {
			if (xhr.status === 200) {
				try {
					var resp = JSON.parse(xhr.responseText);
					if (resp.success && resp.data) {
						callback(resp.data);
						return;
					}
				} catch (err) {}
			}
			callback(null);
		};
		xhr.onerror = function () {
			callback(null);
		};
		// Use the localized nonce + ajaxUrl from inc/enqueue.php (window.bcWishlist).
		// Falls back to global ajaxurl if the localize block didn't load (defensive —
		// e.g. if the script is enqueued without localize during a partial cache state).
		var wishlistNonce = (window.bcWishlist && window.bcWishlist.nonce) || "";
		xhr.send(
			"action=blocksy_child_wishlist_product" +
			"&product_id=" + productId +
			"&_wpnonce=" + encodeURIComponent(wishlistNonce)
		);
	}

	/**
	 * Sync removal to server — uses Blocksy's actual sync method.
	 *
	 * Logged-in: POST full updated items list to blc_ext_wish_list_sync_likes.
	 * Guest: write full updated items list to blc_products_wish_list cookie.
	 */
	function syncRemoveToServer(productId) {
		var blc = window.ct_localizations && window.ct_localizations.blc_ext_wish_list;
		if (!blc || !blc.list) return;

		// Remove from Blocksy's local state.
		blc.list.items = blc.list.items.filter(function (i) {
			return i.id !== productId;
		});

		var updatedList = JSON.parse(JSON.stringify(blc.list));

		if (blc.user_logged_in === "yes") {
			// Logged-in: sync full list to server via AJAX.
			var xhr = new XMLHttpRequest();
			xhr.open("POST", data.ajaxUrl + "?action=blc_ext_wish_list_sync_likes");
			xhr.setRequestHeader("Content-Type", "application/json");
			xhr.setRequestHeader("Accept", "application/json");
			xhr.send(JSON.stringify(updatedList));
		} else {
			// Guest: write full list to cookie.
			var expires = new Date();
			expires.setTime(expires.getTime() + 7 * 24 * 60 * 60 * 1000);
			document.cookie = "blc_products_wish_list=" + encodeURIComponent(JSON.stringify(updatedList)) + "; expires=" + expires.toGMTString() + "; path=/";
		}
	}

	// ==========================================================================
	// Suggested Products — minimal slider (Blocksy flexy doesn't init in panel)
	// ==========================================================================

	function handleSuggestedArrow(arrow) {
		var container = arrow.closest(".flexy-container");
		if (!container) return;

		var items = container.querySelector(".flexy-items");
		if (!items) return;

		var flexyItem = items.querySelector(".flexy-item");
		if (!flexyItem) return;

		var itemWidth = flexyItem.offsetWidth + parseInt(getComputedStyle(items).gap || "0", 10);
		var isNext = arrow.classList.contains("ct-arrow-next");
		var scrollAmount = isNext ? itemWidth : -itemWidth;

		items.scrollBy({ left: scrollAmount, behavior: "smooth" });
	}

	// ==========================================================================
	// Helpers
	// ==========================================================================

	function updateCount() {
		var count = document.querySelectorAll(PANEL_SEL + " .ct-wishlist-item").length;
		document.querySelectorAll(".ct-dynamic-count-wishlist").forEach(function (b) {
			b.textContent = count;
			b.setAttribute("data-count", count);
		});
		updateHeadingCount(count);
	}

	function checkEmpty() {
		if (document.querySelectorAll(PANEL_SEL + " .ct-wishlist-item").length === 0) {
			var inner = document.querySelector(CONTENT_SEL);
			if (!inner) return;

			// Last item removed — swap to the empty-state "You May Also Like".
			setPanelEmptyState(true);

			var isGuest = !document.body.classList.contains("logged-in");
			var html = '<div class="ct-wishlist-empty">'
				+ '<p class="ct-wishlist-empty-message">Your Wishlist is Empty</p>'
				+ '</div>';

			if (isGuest) {
				html += '<div class="ct-wishlist-guest-notice">'
					+ '<p>' + GUEST_TEXT + '</p>'
					+ '<a href="' + data.accountUrl + '" class="ct-wishlist-signup-btn">Sign Up</a>'
					+ '</div>';
			} else {
				html += '<div class="ct-wishlist-continue">'
					+ '<a href="/shop/" class="ct-wishlist-continue-btn">Continue Shopping</a>'
					+ '</div>';
			}

			inner.innerHTML = html;
		}
	}

	function escapeHtml(str) {
		var div = document.createElement("div");
		div.appendChild(document.createTextNode(str));
		return div.innerHTML;
	}
})();
