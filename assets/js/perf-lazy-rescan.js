/**
 * Perf Lazy Rescan — re-run Perfmatters' lazy loader for images injected
 * after it initialised (Blocksy AJAX filter, Load More, pagination).
 *
 * Enqueued by inc/perf/lazy-rescan.php (shop + product taxonomy archives).
 * Promoted from bc-site-customizations/sites/choiceammunition/custom/js/
 * perfmatters-lazy-ajax-rescan.js (choiceammo: 18 stuck lazy images -> 0).
 *
 * How it re-runs the loader:
 *   1. The Perfmatters vanilla-lazyload INSTANCE, captured from its
 *      `LazyLoad::Initialized` event (or a global instance object, if one is
 *      exposed), gets `.update()` — the loader then handles the new images
 *      itself, still lazily. Never `.update()` on `window.LazyLoad`: that is
 *      the constructor, and calling it throws.
 *   2. With an instance, any stuck image that is IN the viewport a moment
 *      after update() (never picked up — no `data-ll-status`) is upgraded
 *      directly, so a visible card can never stay a placeholder.
 *   3. With no instance at all, the source's direct `data-src` -> `src`
 *      upgrade runs — but ONLY for stuck images whose box intersects the
 *      viewport grown by FALLBACK_MARGIN px. Off-screen images are never
 *      upgraded up front; an IntersectionObserver (or, without one, a
 *      throttled scroll/resize sweep) upgrades each as it nears the
 *      viewport, so the fallback stays lazy instead of force-loading the
 *      whole grid.
 * This file is excluded from Delay JS (inc/perf/lazy-rescan.php registers
 * its tag id), so it normally runs before `LazyLoad::Initialized` fires.
 * Without Perfmatters there are no `.perfmatters-lazy` images: every sweep
 * is a no-op.
 */
(function () {
	'use strict';

	var STUCK = 'img.perfmatters-lazy[data-src]:not([data-ll-status])';
	var instance = null;

	window.addEventListener('LazyLoad::Initialized', function (e) {
		if (e && e.detail && e.detail.instance) {
			instance = e.detail.instance;
		}
	}, false);

	function findInstance() {
		if (instance && typeof instance.update === 'function') {
			return instance;
		}
		var candidates = [window.lazyLoadInstance, window.perfmattersLazyLoadInstance];
		for (var i = 0; i < candidates.length; i++) {
			var c = candidates[i];
			// typeof 'object' excludes the LazyLoad constructor (a function).
			if (c && typeof c === 'object' && typeof c.update === 'function') {
				instance = c;
				return c;
			}
		}
		return null;
	}

	function upgradeLazyImage(img) {
		if (!img || !img.classList || !img.classList.contains('perfmatters-lazy')) {
			return false;
		}
		var realSrc = img.getAttribute('data-src');
		var realSrcset = img.getAttribute('data-srcset');
		if (!realSrc) return false;
		img.setAttribute('src', realSrc);
		if (realSrcset) {
			img.setAttribute('srcset', realSrcset);
		}
		img.classList.remove('perfmatters-lazy');
		img.classList.add('perfmatters-lazy-loaded');
		return true;
	}

	// No-instance fallback: how far outside the viewport (px) an image may
	// be and still be upgraded.
	var FALLBACK_MARGIN = 300;

	// Whether img's bounding box intersects the viewport grown by margin px
	// on every side (margin 0 = strictly on screen). A hidden image
	// (display:none, closed panel) has an all-zero rect at 0,0, which would
	// otherwise count as on screen and get force-loaded.
	function inViewport(img, margin) {
		if (!img.getBoundingClientRect) return false;
		var m = margin || 0;
		var r = img.getBoundingClientRect();
		if (r.width === 0 && r.height === 0) return false;
		var h = window.innerHeight || document.documentElement.clientHeight;
		var w = window.innerWidth || document.documentElement.clientWidth;
		return r.bottom >= -m && r.right >= -m && r.top <= h + m && r.left <= w + m;
	}

	// Upgrade stuck images near the viewport only — every caller passes a
	// margin; there is no "upgrade everything" mode.
	function upgradeStuck(margin) {
		var imgs = document.querySelectorAll(STUCK);
		var n = 0;
		for (var i = 0; i < imgs.length; i++) {
			if (!inViewport(imgs[i], margin)) continue;
			if (upgradeLazyImage(imgs[i])) n++;
		}
		return n;
	}

	function isStillStuck(img) {
		return !!(img && img.classList && img.classList.contains('perfmatters-lazy')
			&& img.getAttribute('data-src') && !img.hasAttribute('data-ll-status'));
	}

	var fallbackObserver = null;
	var fallbackScrollBound = false;
	var fallbackScrollPending = false;

	// Keep the no-instance fallback lazy: images left off-screen by
	// upgradeStuck(FALLBACK_MARGIN) are upgraded only once they come within
	// FALLBACK_MARGIN px of the viewport.
	function watchOffscreenStuck() {
		if (typeof IntersectionObserver !== 'undefined') {
			if (!fallbackObserver) {
				fallbackObserver = new IntersectionObserver(function (entries) {
					for (var i = 0; i < entries.length; i++) {
						var en = entries[i];
						if (!en.isIntersecting && !(en.intersectionRatio > 0)) continue;
						fallbackObserver.unobserve(en.target);
						if (isStillStuck(en.target)) upgradeLazyImage(en.target);
					}
				}, { rootMargin: FALLBACK_MARGIN + 'px' });
			}
			var imgs = document.querySelectorAll(STUCK);
			for (var i = 0; i < imgs.length; i++) {
				if (imgs[i].__bcPerfLazyWatched) continue;
				imgs[i].__bcPerfLazyWatched = true;
				fallbackObserver.observe(imgs[i]);
			}
			return;
		}
		if (fallbackScrollBound) return;
		fallbackScrollBound = true;
		var onScroll = function () {
			if (fallbackScrollPending) return;
			fallbackScrollPending = true;
			setTimeout(function () {
				fallbackScrollPending = false;
				if (!findInstance()) upgradeStuck(FALLBACK_MARGIN);
			}, 100);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll, { passive: true });
	}

	var visibleCheck;
	function rescanLazyImages() {
		var inst = findInstance();
		if (inst) {
			try {
				inst.update();
			} catch (e) {
				inst = null;
			}
		}
		if (!inst) {
			var n = upgradeStuck(FALLBACK_MARGIN);
			watchOffscreenStuck();
			return n;
		}
		clearTimeout(visibleCheck);
		visibleCheck = setTimeout(function () { upgradeStuck(0); }, 300);
		return 0;
	}

	function hasNewLazyNodes(mutations) {
		for (var i = 0; i < mutations.length; i++) {
			var added = mutations[i].addedNodes;
			for (var j = 0; j < added.length; j++) {
				var n = added[j];
				if (!n || n.nodeType !== 1) continue;
				if (n.classList && n.classList.contains('perfmatters-lazy')) return true;
				if (n.querySelector && n.querySelector('.perfmatters-lazy')) return true;
			}
		}
		return false;
	}

	var pending;
	function scheduleRescan(delay) {
		clearTimeout(pending);
		pending = setTimeout(rescanLazyImages, delay || 50);
	}

	function init() {
		// Staggered initial sweeps for server-rendered pages.
		setTimeout(rescanLazyImages, 100);
		setTimeout(rescanLazyImages, 600);
		setTimeout(rescanLazyImages, 2000);

		// Observe document.body so AJAX swaps that replace large subtrees are caught.
		if (typeof MutationObserver !== 'undefined' && document.body) {
			var observer = new MutationObserver(function (mutations) {
				if (hasNewLazyNodes(mutations)) scheduleRescan(50);
			});
			observer.observe(document.body, { childList: true, subtree: true });
		}

		// Blocksy AJAX filter / pagination events (Companion Pro).
		var blocksyEvents = [
			'ct:refresh',
			'ct:product-card:refresh',
			'blocksy:ajax:loaded',
			'blocksy:ajax-filter:done',
			'blocksy:frontend:init',
			'blocksy-pagination:load-more:complete'
		];
		// Blocksy dispatches these through its own bus (window.ctEvents) —
		// subscribe there when it exists; the DOM listeners (as in the
		// source) stay as a fallback for anything dispatched as a DOM event.
		var onBlocksyEvent = function () { scheduleRescan(100); };
		blocksyEvents.forEach(function (ev) {
			document.addEventListener(ev, onBlocksyEvent, true);
		});
		// ctEvents may not exist yet at DOMContentLoaded (Blocksy's bundle can
		// be deferred or delayed), so retry once on window load.
		var ctSubscribed = false;
		var subscribeCtEvents = function () {
			if (ctSubscribed || !window.ctEvents || typeof window.ctEvents.on !== 'function') return;
			ctSubscribed = true;
			blocksyEvents.forEach(function (ev) {
				try { window.ctEvents.on(ev, onBlocksyEvent); } catch (e) {}
			});
		};
		subscribeCtEvents();
		window.addEventListener('load', subscribeCtEvents);

		// Generic AJAX completion (jQuery).
		if (window.jQuery) {
			window.jQuery(document).on('ajaxComplete', function () { scheduleRescan(150); });
		}

		// Sweep on window load (covers late async render).
		window.addEventListener('load', function () { rescanLazyImages(); });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
