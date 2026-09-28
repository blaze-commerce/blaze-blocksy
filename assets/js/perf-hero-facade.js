/**
 * Hero facade — mounts the real hero media late, reveals it only once it
 * is actually playing. Inlined by inc/perf/hero-facade.php (never enqueued)
 * so it is exempt from delay-JS; config arrives as window.bcPerfHeroFacade.
 *
 * The still image painted by the server is the LCP element and is NEVER
 * faded, hidden or removed: the media is stacked on top of it.
 */
(function () {
	var cfg = window.bcPerfHeroFacade || {};
	var DELAY_MS = typeof cfg.delay_ms === 'number' ? cfg.delay_ms : 5000;
	var HIRES_MIN_WIDTH = typeof cfg.hires_min_width === 'number' ? cfg.hires_min_width : 900;

	var hosts = document.querySelectorAll('[data-bc-yt-src]');
	var videos = document.querySelectorAll('video[data-bc-perf-hero]');
	if (!hosts.length && !videos.length) { return; }

	var each = function (list, fn) { Array.prototype.forEach.call(list, fn); };

	// Hi-res still for wide viewports, after load (PSI mobile keeps the small inline one).
	function upgradeStill() {
		if (window.innerWidth < HIRES_MIN_WIDTH) { return; }
		each(document.querySelectorAll('.bc-perf-hero__poster[data-bc-hires]'), function (img) {
			var hi = new Image();
			hi.onload = function () { img.src = hi.src; };
			hi.src = img.getAttribute('data-bc-hires');
		});
		each(videos, function (v) {
			if (v.getAttribute('data-bc-started')) { return; }
			var hires = v.getAttribute('data-bc-hires');
			if (hires) { v.setAttribute('poster', hires); }
		});
	}

	// ---- YouTube ------------------------------------------------------

	function withApi(src) {
		if (/[?&]enablejsapi=1/.test(src)) { return src; }
		return src + (src.indexOf('?') === -1 ? '?' : '&') + 'enablejsapi=1&origin=' + encodeURIComponent(location.origin);
	}

	// Reveal only when the player reports PLAYING (playerState === 1): a
	// client that cannot play (headless Chrome, blocked autoplay) never
	// sees a pixel change, so the filmstrip / Speed Index are unaffected.
	function watch(f) {
		var shown = false, tries = 0, poll;

		function reveal() {
			if (shown) { return; }
			shown = true;
			clearInterval(poll);
			window.removeEventListener('message', onMsg);
			f.style.opacity = '1';
			f.style.pointerEvents = '';
		}

		function onMsg(e) {
			if (e.source !== f.contentWindow || !/^https:\/\/www\.youtube(-nocookie)?\.com$/.test(e.origin)) { return; }
			var d;
			try { d = typeof e.data === 'string' ? JSON.parse(e.data) : e.data; } catch (err) { return; }
			if (!d) { return; }
			if ((d.event === 'onStateChange' && d.info === 1) || (d.info && d.info.playerState === 1)) { reveal(); }
		}

		function listen() {
			// The embed ignores this until its API is ready, so repeat for a while.
			try { f.contentWindow.postMessage('{"event":"listening","id":1,"channel":"widget"}', '*'); } catch (err) {}
			if (++tries > 30) { clearInterval(poll); }
		}

		window.addEventListener('message', onMsg);
		f.addEventListener('load', function () { listen(); poll = setInterval(listen, 500); });
	}

	function mount(host) {
		if (host.querySelector('iframe')) { return; }
		var f = document.createElement('iframe');
		f.setAttribute('src', withApi(host.getAttribute('data-bc-yt-src')));
		f.setAttribute('title', host.getAttribute('data-bc-yt-title') || '');
		f.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
		f.setAttribute('allowfullscreen', '');
		f.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
		f.setAttribute('loading', 'eager');
		// Stacked above the still (z-index:1); only the iframe itself ever changes opacity.
		f.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;border:0;z-index:1;opacity:0;pointer-events:none;transition:opacity .4s';
		watch(f);
		host.appendChild(f);
	}

	// ---- <video> ------------------------------------------------------

	function start(v) {
		if (v.getAttribute('data-bc-started')) { return; }
		v.setAttribute('data-bc-started', '1');
		var src = v.getAttribute('data-bc-src');
		if (src && !v.getAttribute('src')) { v.setAttribute('src', src); }
		try { v.preload = 'auto'; v.load(); } catch (e) {}
		var p = v.play();
		if (p && typeof p.catch === 'function') { p.catch(function () {}); }
	}

	function startVideos() {
		if (!videos.length) { return; }
		if (!('IntersectionObserver' in window)) { each(videos, start); return; }
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) { start(entry.target); io.unobserve(entry.target); }
			});
		}, { rootMargin: '200px 0px' });
		each(videos, function (v) { io.observe(v); });
	}

	// ---- Triggers -----------------------------------------------------

	function go() {
		var run = function () { each(hosts, mount); startVideos(); };
		if (window.requestIdleCallback) { window.requestIdleCallback(run, { timeout: 2000 }); } else { run(); }
	}

	var started = false, timer;
	var EVENTS = ['pointerdown', 'touchstart', 'wheel', 'keydown', 'mousemove', 'scroll'];
	var opts = { passive: true };

	function trigger() {
		if (started) { return; }
		started = true;
		clearTimeout(timer);
		EVENTS.forEach(function (e) { window.removeEventListener(e, trigger, opts); });
		go();
	}

	// Armed at parse time, so an interaction during a slow load counts.
	EVENTS.forEach(function (e) { window.addEventListener(e, trigger, opts); });

	// Timer backstop for visitors who never interact; none under reduced motion.
	function onLoad() {
		upgradeStill();
		if (started) { return; }
		if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
		timer = setTimeout(trigger, DELAY_MS);
	}

	if (document.readyState === 'complete') { onLoad(); } else { window.addEventListener('load', onLoad); }
})();
