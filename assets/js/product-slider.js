/**
 * Product Slider — Multi-visible-item carousel with pagination dots.
 * No dependencies. Slides by page (columns count) with smooth transition.
 * Works with native WooCommerce product cards (li.product inside ul.products).
 *
 * Uses pixel-based offsets (not percentage) to handle CSS gap correctly.
 *
 * @package Blocksy_Child
 */
(function() {
	'use strict';

	document.querySelectorAll('.bc-product-slider').forEach(function(slider) {
		// Support both WooCommerce (ul.products > li.product) and generic lists
		var track   = slider.querySelector('ul.products') || slider.querySelector('.bc-product-slider__track > ul');
		var items   = track ? track.querySelectorAll(':scope > li') : [];
		var dots    = slider.querySelectorAll('.bc-product-slider__dot');
		var prevBtn = slider.querySelector('.bc-product-slider__arrow--prev');
		var nextBtn = slider.querySelector('.bc-product-slider__arrow--next');

		if (!track || items.length === 0) return;

		var columns = parseInt(slider.dataset.columns, 10) || 4;
		var slideBy = parseInt(slider.dataset.slideBy, 10) || 0; // 0 = by page (default), 1+ = by N items
		var total   = items.length;
		var current = 0;

		function getColumns() {
			var w = window.innerWidth;
			var colsMobile = parseInt(slider.dataset.columnsMobile, 10) || 2;
			var colsTablet = parseInt(slider.dataset.columnsTablet, 10) || Math.min(3, columns);
			if (w < 690) return colsMobile;
			if (w < 1000) return colsTablet;
			return columns;
		}

		function getSlideBy() {
			return slideBy || getColumns(); // slide-by-1 or slide-by-page
		}

		function setWidths() {
			var cols = getColumns();
			var step = getSlideBy();
			// Clear inline styles — let CSS calc() handle widths.
			items.forEach(function(item) {
				item.style.flex = '';
				item.style.maxWidth = '';
			});

			// Update dot visibility for responsive
			var newPages;
			if (step < cols) {
				if (cols >= 4) {
					// Desktop/Tablet: 2 dots (start + end)
					newPages = total > cols ? 2 : 1;
				} else {
					// Mobile: page-based (e.g., 9 logos / 2 visible = 5 dots)
					newPages = Math.ceil(total / cols);
				}
			} else {
				// Standard page-based carousel
				newPages = Math.ceil(total / cols);
			}
			dots.forEach(function(dot, i) {
				dot.style.display = i < newPages ? '' : 'none';
			});

			if (current >= newPages) goTo(newPages - 1);
		}

		function goTo(page) {
			var cols = getColumns();
			var step = getSlideBy();
			var maxPage;
			var targetIndex;

			if (step < cols && cols >= 4) {
				// Desktop/Tablet: 2 positions (start + end)
				maxPage = total > cols ? 1 : 0;
				targetIndex = page === 0 ? 0 : total - cols;
			} else if (step < cols) {
				// Mobile: page-based with slide-by cols
				maxPage = Math.max(0, Math.ceil(total / cols) - 1);
				targetIndex = page * cols;
				// Last page: ensure last item is rightmost
				if (targetIndex + cols > total) {
					targetIndex = Math.max(0, total - cols);
				}
			} else {
				// Standard page-based
				maxPage = Math.max(0, Math.ceil(total / cols) - 1);
				targetIndex = page * cols;
			}

			current = Math.max(0, Math.min(page, maxPage));

			var offset = 0;
			if (targetIndex < items.length) {
				offset = items[targetIndex].offsetLeft - items[0].offsetLeft;
			}

			track.style.transform = 'translateX(-' + offset + 'px)';

			dots.forEach(function(dot, i) {
				dot.classList.toggle('active', i === current);
			});
		}

		function next() { goTo(current + 1); }
		function prev() { goTo(current - 1); }

		setWidths();
		window.addEventListener('resize', function() {
			setWidths();
			goTo(current);
		});

		dots.forEach(function(dot, i) {
			dot.addEventListener('click', function() { goTo(i); });
		});

		if (prevBtn) prevBtn.addEventListener('click', prev);
		if (nextBtn) nextBtn.addEventListener('click', next);

		// Touch swipe
		var touchStartX = 0;
		track.addEventListener('touchstart', function(e) {
			touchStartX = e.changedTouches[0].screenX;
		}, { passive: true });
		track.addEventListener('touchend', function(e) {
			var diff = e.changedTouches[0].screenX - touchStartX;
			if (Math.abs(diff) > 50) {
				diff > 0 ? prev() : next();
			}
		}, { passive: true });
	});
})();
