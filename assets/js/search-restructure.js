/**
 * FiboSearch Dropdown Restructure
 *
 * Transforms flat FiboSearch suggestions into section-based two-column layout.
 * Uses MutationObserver to detect when FiboSearch renders results, then
 * restructures the flat <a> items into section wrappers for CSS Grid layout.
 *
 * No jQuery dependency — vanilla JS only.
 *
 * @package Blocksy_Child
 * @date    2026-04-15
 */
(function () {
	'use strict';

	let isProcessing = false;

	function getActiveQuery() {
		const inputs = document.querySelectorAll('.dgwt-wcas-search-input');
		for (const input of inputs) {
			if (input.value) return input.value;
		}
		return '';
	}

	function el(tag, attrs = {}, text = '') {
		const node = document.createElement(tag);
		for (const [k, v] of Object.entries(attrs)) {
			if (k === 'class') node.className = v;
			else node.setAttribute(k, v);
		}
		if (text) node.textContent = text;
		return node;
	}

	function buildViewAllLink(query, total) {
		const q = query || getActiveQuery();
		const label = total ? `SEE ALL ${total} PRODUCTS` : 'SEE ALL PRODUCTS';
		const a = el('a', {
			href: `/?s=${encodeURIComponent(q)}&post_type=product`,
			class: 'dgwt-wcas-view-all',
			'aria-label': label,
		});
		a.textContent = label + ' \u2192';
		return a;
	}

	function addSearchHeader(container) {
		if (container.querySelector('.search-popup-header')) return;
		if (document.querySelector('.dgwt-wcas-overlay-mobile-on')) return;

		const q = getActiveQuery();
		const header = el('div', { class: 'search-popup-header' });

		const title = el('span', { class: 'search-popup-title' }, `Showing results for "${q}"`);

		const closeBtn = el('button', {
			class: 'search-popup-close',
			'aria-label': 'Close search',
			type: 'button',
		});
		closeBtn.innerHTML =
			'<svg width="14" height="14" viewBox="0 0 24 24" fill="none">' +
			'<path d="M6 18L18 6M6 6L18 18" stroke="currentColor" stroke-width="2" ' +
			'stroke-linecap="round" stroke-linejoin="round"/></svg>';

		closeBtn.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			document.querySelectorAll('.dgwt-wcas-search-input').forEach(function (inp) {
				inp.value = '';
				inp.dispatchEvent(new Event('change'));
			});
			container.innerHTML = '';
			container.style.display = 'none';
		});

		header.appendChild(title);
		header.appendChild(closeBtn);
		container.prepend(header);
		container.classList.add('has-search-header');
	}

	function createSection(className, layout, headingText) {
		const wrapper = el('div', {
			class: 'dgwt-wcas-suggestion-section dgwt-wcas-section-' + className,
		});
		const headerDiv = el('div', { class: 'dgwt-wcas-section-header' });
		headerDiv.appendChild(el('h3', { class: 'dgwt-wcas-section-title' }, headingText));
		wrapper.appendChild(headerDiv);

		const content = el('div', {
			class: 'dgwt-wcas-section-content dgwt-wcas-layout-' + layout,
		});
		wrapper.appendChild(content);

		return { wrapper, content };
	}

	function process(container) {
		if (isProcessing) return;
		isProcessing = true;

		try {
			const children = Array.from(container.children);
			const hasHeadlines = children.some(function (c) {
				return c.classList.contains('js-dgwt-wcas-suggestion-headline');
			});
			const hasProducts = children.some(function (c) {
				return c.classList.contains('dgwt-wcas-suggestion-product');
			});

			if (hasProducts && !hasHeadlines) {
				processProductsOnly(container, children);
				return;
			}

			let currentSection = null;
			let currentWrapper = null;
			let currentContent = null;
			let productCount = 0;
			let viewAllTotal = null;
			const query = getActiveQuery();
			const fragment = document.createDocumentFragment();

			for (const child of children) {
				if (child.classList.contains('js-dgwt-wcas-suggestion-headline')) {
					// Finalize previous section
					if (currentWrapper) fragment.appendChild(currentWrapper);

					const headingEl = child.querySelector('.dgwt-wcas-st');
					const headingText = headingEl ? headingEl.textContent : '';
					const ht = headingText.toLowerCase();

					let cls = 'other';
					let layout = 'list';
					if (ht.indexOf('categor') >= 0) cls = 'categories';
					else if (ht.indexOf('product') >= 0) { cls = 'products'; layout = 'grid'; }
					else if (ht.indexOf('post') >= 0 || ht.indexOf('blog') >= 0) cls = 'blog';
					else if (ht.indexOf('page') >= 0) cls = 'pages';

					const section = createSection(cls, layout, headingText);
					currentWrapper = section.wrapper;
					currentContent = section.content;
					currentSection = cls;
					productCount = 0;

				} else if (
					child.classList.contains('dgwt-wcas-suggestion') &&
					!child.classList.contains('js-dgwt-wcas-suggestion-more')
				) {
					if (currentContent) {
						if (currentSection === 'products') {
							productCount++;
							if (productCount <= 8) currentContent.appendChild(child.cloneNode(true));
						} else {
							currentContent.appendChild(child.cloneNode(true));
						}
					}

				} else if (
					child.classList.contains('js-dgwt-wcas-suggestion-more') &&
					currentSection === 'products'
				) {
					const totalEl = child.querySelector('.dgwt-wcas-st-more-total');
					if (totalEl) {
						const match = totalEl.textContent.match(/\((\d+)\)/);
						viewAllTotal = match ? match[1] : null;
					}
				}
			}

			// Finalize last section
			if (currentWrapper) fragment.appendChild(currentWrapper);

			// Add "SEE ALL" link to products section
			const productsSection = fragment.querySelector('.dgwt-wcas-section-products');
			if (productsSection) {
				productsSection.appendChild(buildViewAllLink(query, viewAllTotal));
			}

			// Reorder: categories → products → blog → pages → other
			const ordered = document.createDocumentFragment();
			for (const name of ['categories', 'products', 'blog', 'pages', 'other']) {
				const section = fragment.querySelector('.dgwt-wcas-section-' + name);
				if (section) ordered.appendChild(section);
			}

			container.innerHTML = '';
			container.appendChild(ordered);
			addSearchHeader(container);
			upgradeImages(container);
			disableFiboHover(container);
			loadGalleryImages(container);
		} finally {
			isProcessing = false;
		}
	}

	/**
	 * Disable FiboSearch's JS-managed hover/selection state.
	 * FiboSearch tracks suggestion indices for keyboard/mouse navigation.
	 * After our DOM restructure, those indices are wrong — hovering item N
	 * highlights a different item. Strip the selected class and block re-application.
	 */
	function disableFiboHover(container) {
		// Remove any existing selected class
		container.querySelectorAll('.dgwt-wcas-suggestion-selected').forEach(function (el) {
			el.classList.remove('dgwt-wcas-suggestion-selected');
		});

		// Observe and strip selected class whenever FiboSearch re-applies it
		const hoverObserver = new MutationObserver(function (mutations) {
			for (const mutation of mutations) {
				if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
					const target = mutation.target;
					if (target.classList.contains('dgwt-wcas-suggestion-selected')) {
						target.classList.remove('dgwt-wcas-suggestion-selected');
					}
				}
			}
		});

		container.querySelectorAll('.dgwt-wcas-suggestion').forEach(function (suggestion) {
			hoverObserver.observe(suggestion, { attributes: true, attributeFilter: ['class'] });
		});
	}

	/**
	 * Fetch gallery images for product hover swap.
	 * Collects product IDs from data-post-id attributes, makes one AJAX call,
	 * stores hover image as data attribute, attaches mouseenter/mouseleave handlers.
	 */
	function loadGalleryImages(container) {
		if (typeof bcSearchConfig === 'undefined') return;

		const products = container.querySelectorAll('.dgwt-wcas-suggestion-product[data-post-id]');
		if (!products.length) return;

		const ids = Array.from(products).map(function (p) { return p.getAttribute('data-post-id'); }).filter(Boolean);
		if (!ids.length) return;

		const formData = new FormData();
		formData.append('action', 'bc_search_gallery');
		formData.append('nonce', bcSearchConfig.nonce);
		ids.forEach(function (id) { formData.append('product_ids[]', id); });

		fetch(bcSearchConfig.ajaxUrl, { method: 'POST', body: formData })
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (!json.success || !json.data) return;
				const gallery = json.data;

				// Re-query DOM for current products (original elements may have been replaced)
				var currentProducts = container.querySelectorAll('.dgwt-wcas-suggestion-product[data-post-id]');
				currentProducts.forEach(function (card) {
					var postId = card.getAttribute('data-post-id');
					if (!gallery[postId]) return;

					var img = card.querySelector('.dgwt-wcas-si img');
					if (!img || img.dataset.galleryImage) return; // Skip if already applied

					var hoverSrc = gallery[postId];
					img.dataset.galleryImage = hoverSrc;
					img.dataset.originalImage = img.src;

					// Preload hover image
					var preload = new Image();
					preload.src = hoverSrc;

					card.addEventListener('mouseenter', function () {
						img.src = hoverSrc;
					});
					card.addEventListener('mouseleave', function () {
						img.src = img.dataset.originalImage;
					});
				});
			})
			.catch(function (err) {
				// Audit P1 2026-05-08: leave a breadcrumb so QA can correlate
				// missing hover-swap reports to network issues.  Hover swap is
				// still a progressive enhancement — page works without it.
				if (window.console && console.warn) {
					console.warn('[BC search] Gallery image fetch failed (hover swap disabled):', err);
				}
			});
	}

	/**
	 * Upgrade FiboSearch 64px thumbnails to WooCommerce 300x300 for sharp display.
	 * FiboSearch indexes images at 64px. We swap the size suffix in the URL.
	 */
	function upgradeImages(container) {
		container.querySelectorAll('.dgwt-wcas-si img').forEach(function (img) {
			const src = img.src;
			// Match WP image size suffix: -64x75.jpg, -64x64.png, etc.
			const upgraded = src.replace(/-\d{2,3}x\d{2,3}(\.\w+)$/, '-300x300$1');
			if (upgraded !== src) {
				img.src = upgraded;
				// Fallback: if 300x300 doesn't exist, try without size suffix
				img.onerror = function () {
					img.src = src.replace(/-\d{2,3}x\d{2,3}(\.\w+)$/, '$1');
					img.onerror = null;
				};
			}
		});
	}

	function processProductsOnly(container, children) {
		const query = getActiveQuery();
		const section = createSection('products', 'grid', 'Products');

		let count = 0;
		let viewAllTotal = null;

		for (const child of children) {
			if (child.classList.contains('dgwt-wcas-suggestion-product') && count < 8) {
				section.content.appendChild(child.cloneNode(true));
				count++;
			}
			if (child.classList.contains('js-dgwt-wcas-suggestion-more')) {
				const totalEl = child.querySelector('.dgwt-wcas-st-more-total');
				if (totalEl) {
					const match = totalEl.textContent.match(/\((\d+)\)/);
					viewAllTotal = match ? match[1] : null;
				}
			}
		}

		section.wrapper.appendChild(buildViewAllLink(query, viewAllTotal));

		container.innerHTML = '';
		container.appendChild(section.wrapper);
		addSearchHeader(container);
		upgradeImages(container);
		disableFiboHover(container);
		setTimeout(function () { loadGalleryImages(container); }, 100);
	}

	// MutationObserver — watch for FiboSearch dropdown changes
	const observer = new MutationObserver(function (mutations) {
		for (const mutation of mutations) {
			if (mutation.type !== 'childList') continue;
			const target = mutation.target;
			if (!target.classList || !target.classList.contains('dgwt-wcas-suggestions-wrapp')) continue;

			const hasContent =
				target.querySelector('.js-dgwt-wcas-suggestion-headline') ||
				target.querySelector('.dgwt-wcas-suggestion-product');
			const alreadyProcessed = target.querySelector('.dgwt-wcas-suggestion-section');

			if (hasContent && !alreadyProcessed) {
				setTimeout(function () { process(target); }, 10);
			}
		}
	});

	// Start observing after DOM is ready
	function init() {
		document.querySelectorAll('.dgwt-wcas-suggestions-wrapp').forEach(function (el) {
			observer.observe(el, { childList: true, subtree: true });
		});
	}

	// Fallback: check on input events
	document.addEventListener('input', function (e) {
		if (!e.target.classList.contains('dgwt-wcas-search-input')) return;
		setTimeout(function () {
			document.querySelectorAll('.dgwt-wcas-suggestions-wrapp').forEach(function (container) {
				if (container.offsetHeight === 0) return;
				const hasContent =
					container.querySelector('.js-dgwt-wcas-suggestion-headline') ||
					container.querySelector('.dgwt-wcas-suggestion-product');
				const done = container.querySelector('.dgwt-wcas-suggestion-section');
				if (hasContent && !done) process(container);
			});
		}, 150);
	});

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { setTimeout(init, 1000); });
	} else {
		setTimeout(init, 1000);
	}
})();
