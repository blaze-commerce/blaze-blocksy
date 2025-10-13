/**
 * Product Collection Block - Responsive Frontend
 *
 * Handles responsive column and product count adjustments on the frontend
 * based on device breakpoints.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function ($) {
	'use strict';

	/**
	 * Responsive Product Collection Handler
	 */
	class ResponsiveProductCollection {
		/**
		 * Constructor
		 */
		constructor() {
			this.breakpoints = {
				desktop: 1024,
				tablet: 768
			};
			this.resizeTimer = null;
			this.init();
		}

		/**
		 * Initialize the handler
		 */
		init() {
			this.setupResponsiveCollections();
			this.bindEvents();
		}

		/**
		 * Setup all responsive collections on the page
		 */
		setupResponsiveCollections() {
			$('.wc-responsive-collection').each((index, element) => {
				this.setupCollection($(element));
			});
		}

		/**
		 * Setup a single responsive collection
		 *
		 * @param {jQuery} $collection Collection element
		 */
		setupCollection($collection) {
			const responsiveColumns = $collection.data('responsive-columns');
			const responsiveCounts = $collection.data('responsive-counts');

			if (!responsiveColumns || !responsiveCounts) {
				return;
			}

			this.applyResponsiveLayout($collection, responsiveColumns, responsiveCounts);
		}

		/**
		 * Apply responsive layout to collection
		 *
		 * @param {jQuery} $collection Collection element
		 * @param {Object} columns Column configuration
		 * @param {Object} counts Product count configuration
		 */
		applyResponsiveLayout($collection, columns, counts) {
			const currentBreakpoint = this.getCurrentBreakpoint();
			const targetColumns = columns[currentBreakpoint] || columns.desktop;
			const targetCount = counts[currentBreakpoint] || counts.desktop;

			// Apply column classes
			this.updateColumnClasses($collection, targetColumns);

			// Update CSS custom property for grid
			$collection[0].style.setProperty('--wc-responsive-columns', targetColumns);

			// Show/hide products based on count
			this.updateProductVisibility($collection, targetCount);
		}

		/**
		 * Update column classes on collection
		 *
		 * @param {jQuery} $collection Collection element
		 * @param {number} columns Number of columns
		 */
		updateColumnClasses($collection, columns) {
			// Remove existing column classes
			$collection.removeClass((index, className) => {
				return (className.match(/(^|\s)columns-\d+/g) || []).join(' ');
			});

			// Add new column class
			$collection.addClass(`columns-${columns}`);
		}

		/**
		 * Update product visibility based on count
		 *
		 * @param {jQuery} $collection Collection element
		 * @param {number} count Number of products to show
		 */
		updateProductVisibility($collection, count) {
			const $products = $collection.find('.wp-block-post-template > li, .wp-block-woocommerce-product-template > li');
			
			$products.each((index, product) => {
				const $product = $(product);
				if (index < count) {
					$product.show().css('display', '');
				} else {
					$product.hide();
				}
			});
		}

		/**
		 * Get current breakpoint based on window width
		 *
		 * @return {string} Current breakpoint (desktop, tablet, or mobile)
		 */
		getCurrentBreakpoint() {
			const width = window.innerWidth;

			if (width >= this.breakpoints.desktop) {
				return 'desktop';
			} else if (width >= this.breakpoints.tablet) {
				return 'tablet';
			} else {
				return 'mobile';
			}
		}

		/**
		 * Bind event listeners
		 */
		bindEvents() {
			// Handle window resize with debouncing
			$(window).on('resize', () => {
				clearTimeout(this.resizeTimer);
				this.resizeTimer = setTimeout(() => {
					this.setupResponsiveCollections();
				}, 250);
			});

			// Re-initialize on AJAX complete (for dynamic content)
			$(document).on('ajaxComplete', () => {
				setTimeout(() => {
					this.setupResponsiveCollections();
				}, 100);
			});
		}
	}

	/**
	 * Initialize when DOM is ready
	 */
	$(document).ready(() => {
		new ResponsiveProductCollection();
	});

	/**
	 * Re-initialize on Gutenberg block updates (for editor preview)
	 */
	if (window.wp && window.wp.data) {
		const { subscribe } = window.wp.data;
		let previousBlocks = [];

		subscribe(() => {
			const blocks = window.wp.data.select('core/block-editor')?.getBlocks() || [];
			if (blocks.length !== previousBlocks.length) {
				setTimeout(() => {
					if ($('.wc-responsive-collection').length) {
						new ResponsiveProductCollection();
					}
				}, 100);
			}
			previousBlocks = blocks;
		});
	}

})(jQuery);

