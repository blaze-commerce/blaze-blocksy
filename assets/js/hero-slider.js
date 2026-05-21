/**
 * Hero Slider — auto-advance carousel for the homepage hero.
 *
 * Timing constants (audit P1 2026-05-08 — documented for future devs):
 *   • interval (default 5000 ms): time between slides.  WCAG 2.2.2 requires
 *     a way to Pause/Stop/Hide content that auto-advances under 5 seconds.
 *     5000 ms sits at the boundary, plus the user can hover/focus to pause.
 *   • Math.abs(diff) > 50: minimum swipe pixel-distance before the gesture
 *     is treated as a slide-change.  Below this, treat as a tap.
 */
(function() {
	'use strict';
	var slider = document.querySelector('.bc-hero-slider');
	if (!slider) return;

	var slides = slider.querySelectorAll('.bc-hero-slide');
	var dots = slider.querySelectorAll('.bc-hero-dot');
	var prevBtn = slider.querySelector('.bc-hero-arrow--prev');
	var nextBtn = slider.querySelector('.bc-hero-arrow--next');
	var current = 0;
	var interval = 5000;
	var timer;

	// WCAG 2.2.2 — Pause, Stop, Hide. Auto-advancing content >5 s must be
	// pausable, stoppable, or hidable by users. Honor `prefers-reduced-motion`
	// up front: if the user expressed that preference at the OS level, do
	// not start the auto-advance at all. They can still navigate via dots
	// / arrows / swipe / keyboard.
	var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (slides.length < 2) return;

	function goTo(index) {
		slides[current].classList.remove('active');
		if (dots[current]) dots[current].classList.remove('active');
		current = (index + slides.length) % slides.length;
		slides[current].classList.add('active');
		if (dots[current]) dots[current].classList.add('active');
	}

	function next() { goTo(current + 1); }
	function prev() { goTo(current - 1); }

	function resetTimer() {
		clearInterval(timer);
		timer = setInterval(next, interval);
	}

	// Start auto-advance — but skip entirely if user prefers reduced motion.
	if (!reducedMotion) {
		timer = setInterval(next, interval);
	}

	// Dot click handlers
	dots.forEach(function(dot, i) {
		dot.addEventListener('click', function() {
			goTo(i);
			resetTimer();
		});
	});

	// Arrow click handlers
	if (prevBtn) {
		prevBtn.addEventListener('click', function() { prev(); resetTimer(); });
	}
	if (nextBtn) {
		nextBtn.addEventListener('click', function() { next(); resetTimer(); });
	}

	// Pause on pointer hover AND on keyboard focus (WCAG 2.2.2).
	// The previous version only paused on mouseenter — keyboard users could
	// not pause the auto-advance, which is a WCAG conformance gap.
	function pauseAutoAdvance() {
		clearInterval(timer);
	}
	function resumeAutoAdvance() {
		if (reducedMotion) return; // never resume if user prefers reduced motion
		clearInterval(timer);
		timer = setInterval(next, interval);
	}
	slider.addEventListener('mouseenter', pauseAutoAdvance);
	slider.addEventListener('mouseleave', resumeAutoAdvance);
	slider.addEventListener('focusin',    pauseAutoAdvance);
	slider.addEventListener('focusout',   resumeAutoAdvance);

	// Touch swipe support
	var touchStartX = 0;
	slider.addEventListener('touchstart', function(e) {
		touchStartX = e.changedTouches[0].screenX;
		clearInterval(timer);
	}, { passive: true });
	slider.addEventListener('touchend', function(e) {
		var diff = e.changedTouches[0].screenX - touchStartX;
		if (Math.abs(diff) > 50) {
			diff > 0 ? prev() : next();
		}
		resetTimer();
	}, { passive: true });
})();
