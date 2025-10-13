/**
 * Blaze Product Collection - Frontend Script
 * Handles responsive column and product count adjustments
 */

(function ($) {
	'use strict';

	/**
	 * Blaze Product Collection Handler
	 */
	class BlazeProductCollection {
		constructor(element) {
			this.$element = $(element);
			this.enableResponsive = this.$element.data('enable-responsive');
			this.responsiveColumns = this.$element.data('responsive-columns');
			this.responsiveCounts = this.$element.data('responsive-counts');
			this.currentDevice = 'desktop';
			this.$productList = this.$element.find('.products');

			if (this.enableResponsive) {
				this.init();
			}
		}

		/**
		 * Initialize
		 */
		init() {
			this.detectDevice();
			this.applyResponsiveLayout();
			this.bindEvents();
		}

		/**
		 * Bind events
		 */
		bindEvents() {
			let resizeTimer;
			$(window).on('resize', () => {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(() => {
					this.detectDevice();
					this.applyResponsiveLayout();
				}, 250);
			});
		}

		/**
		 * Detect current device based on viewport width
		 */
		detectDevice() {
			const width = $(window).width();
			let newDevice;

			if (width < 768) {
				newDevice = 'mobile';
			} else if (width < 1024) {
				newDevice = 'tablet';
			} else {
				newDevice = 'desktop';
			}

			// Only update if device changed
			if (newDevice !== this.currentDevice) {
				this.currentDevice = newDevice;
				this.$element.attr('data-current-device', newDevice);
				return true;
			}

			return false;
		}

		/**
		 * Apply responsive layout
		 */
		applyResponsiveLayout() {
			if (!this.responsiveColumns || !this.responsiveCounts) {
				return;
			}

			const columns = this.responsiveColumns[this.currentDevice];
			const productCount = this.responsiveCounts[this.currentDevice];

			// Update columns class
			this.updateColumns(columns);

			// Update visible products
			this.updateVisibleProducts(productCount);
		}

		/**
		 * Update columns
		 */
		updateColumns(columns) {
			// Remove all column classes
			this.$productList.removeClass(function (index, className) {
				return (className.match(/(^|\s)columns-\S+/g) || []).join(' ');
			});

			// Add new column class
			this.$productList.addClass('columns-' + columns);

			// Update CSS custom property for more flexible styling
			this.$productList.css('--blaze-columns', columns);
		}

		/**
		 * Update visible products
		 */
		updateVisibleProducts(count) {
			const $products = this.$productList.find('> li');

			// Show/hide products based on count
			$products.each(function (index) {
				if (index < count) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});
		}

		/**
		 * Get current device
		 */
		getCurrentDevice() {
			return this.currentDevice;
		}

		/**
		 * Get current columns
		 */
		getCurrentColumns() {
			return this.responsiveColumns ? this.responsiveColumns[this.currentDevice] : null;
		}

		/**
		 * Get current product count
		 */
		getCurrentProductCount() {
			return this.responsiveCounts ? this.responsiveCounts[this.currentDevice] : null;
		}
	}

	/**
	 * Initialize all Blaze Product Collection blocks
	 */
	function initBlazeProductCollections() {
		$('.blaze-product-collection').each(function () {
			const instance = new BlazeProductCollection(this);
			$(this).data('blazeProductCollection', instance);
		});
	}

	/**
	 * Document ready
	 */
	$(document).ready(function () {
		initBlazeProductCollections();
	});

	/**
	 * Expose to global scope for external access
	 */
	window.BlazeProductCollection = BlazeProductCollection;
	window.initBlazeProductCollections = initBlazeProductCollections;

})(jQuery);

