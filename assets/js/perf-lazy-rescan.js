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
 *      upgrade runs for every stuck image (exactly the measured fix).
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

	function inViewport(img) {
		if (!img.getBoundingClientRect) return false;
		var r = img.getBoundingClientRect();
		var h = window.innerHeight || document.documentElement.clientHeight;
		var w = window.innerWidth || document.documentElement.clientWidth;
		return r.bottom >= 0 && r.right >= 0 && r.top <= h && r.left <= w;
	}

	function upgradeStuck(onlyVisible) {
		var imgs = document.querySelectorAll(STUCK);
		var n = 0;
		for (var i = 0; i < imgs.length; i++) {
			if (onlyVisible && !inViewport(imgs[i])) continue;
			if (upgradeLazyImage(imgs[i])) n++;
		}
		return n;
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
			return upgradeStuck(false);
		}
		clearTimeout(visibleCheck);
		visibleCheck = setTimeout(function () { upgradeStuck(true); }, 300);
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
			if (window.ctEvents && typeof window.ctEvents.on === 'function') {
				try { window.ctEvents.on(ev, onBlocksyEvent); } catch (e) {}
			}
			document.addEventListener(ev, onBlocksyEvent, true);
		});

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
