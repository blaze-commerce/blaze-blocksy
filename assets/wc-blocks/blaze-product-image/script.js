/**
 * Blaze Product Image - Frontend Script
 * Handles wishlist button and hover image functionality
 */

(function ($) {
	'use strict';

	/**
	 * Blaze Product Image Handler
	 */
	class BlazeProductImage {
		constructor(element) {
			this.$element = $(element);
			this.productId = this.$element.data('product-id');
			this.showWishlist = this.$element.data('show-wishlist');
			this.enableHover = this.$element.data('enable-hover');
			this.$wishlistBtn = this.$element.find('.blaze-product-image__wishlist');
			this.$mainImage = this.$element.find('.blaze-product-image__img--main');
			this.$hoverImage = this.$element.find('.blaze-product-image__img--hover');

			this.init();
		}

		/**
		 * Initialize
		 */
		init() {
			if (this.showWishlist) {
				this.initWishlist();
			}

			if (this.enableHover && this.$hoverImage.length) {
				this.initHoverImage();
			}
		}

		/**
		 * Initialize wishlist functionality
		 */
		initWishlist() {
			this.$wishlistBtn.on('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				this.toggleWishlist();
			});
		}

		/**
		 * Initialize hover image functionality
		 */
		initHoverImage() {
			const $container = this.$element.find('.blaze-product-image__container');

			$container.on('mouseenter', () => {
				this.$mainImage.css('opacity', '0');
				this.$hoverImage.css('opacity', '1');
			});

			$container.on('mouseleave', () => {
				this.$mainImage.css('opacity', '1');
				this.$hoverImage.css('opacity', '0');
			});
		}

		/**
		 * Toggle wishlist
		 */
		toggleWishlist() {
			const isInWishlist = this.$wishlistBtn.hasClass('is-in-wishlist');

			// Add loading state
			this.$wishlistBtn.addClass('is-loading');

			// Check if Blocksy wishlist exists
			if (typeof window.blocksyWishlist !== 'undefined') {
				// Use Blocksy's wishlist system
				this.toggleBlocksyWishlist(isInWishlist);
			} else {
				// Use custom AJAX
				this.toggleCustomWishlist(isInWishlist);
			}
		}

		/**
		 * Toggle Blocksy wishlist
		 */
		toggleBlocksyWishlist(isInWishlist) {
			// Trigger Blocksy wishlist event
			const event = new CustomEvent('blaze:wishlist:toggle', {
				detail: {
					productId: this.productId,
					action: isInWishlist ? 'remove' : 'add',
				},
			});

			document.dispatchEvent(event);

			// Update UI
			setTimeout(() => {
				this.$wishlistBtn.toggleClass('is-in-wishlist');
				this.$wishlistBtn.removeClass('is-loading');
				this.updateWishlistButton(!isInWishlist);
			}, 300);
		}

		/**
		 * Toggle custom wishlist via AJAX
		 */
		toggleCustomWishlist(isInWishlist) {
			const action = isInWishlist ? 'remove_from_wishlist' : 'add_to_wishlist';

			$.ajax({
				url: blazeProductImage.ajaxUrl,
				type: 'POST',
				data: {
					action: 'blaze_' + action,
					product_id: this.productId,
					nonce: blazeProductImage.nonce,
				},
				success: (response) => {
					if (response.success) {
						this.$wishlistBtn.toggleClass('is-in-wishlist');
						this.updateWishlistButton(!isInWishlist);
						this.showNotification(response.data.message);

						// Trigger custom event for other scripts
						$(document).trigger('blaze:wishlist:updated', {
							productId: this.productId,
							inWishlist: !isInWishlist,
						});
					} else {
						this.showNotification(response.data.message, 'error');
					}
				},
				error: () => {
					this.showNotification('Error updating wishlist', 'error');
				},
				complete: () => {
					this.$wishlistBtn.removeClass('is-loading');
				},
			});
		}

		/**
		 * Update wishlist button
		 */
		updateWishlistButton(isInWishlist) {
			const label = isInWishlist
				? 'Remove from wishlist'
				: 'Add to wishlist';
			this.$wishlistBtn.attr('aria-label', label);
		}

		/**
		 * Show notification
		 */
		showNotification(message, type = 'success') {
			// Check if there's a notification system
			if (typeof window.blazeNotify !== 'undefined') {
				window.blazeNotify(message, type);
			} else {
				// Simple console log fallback
				console.log(`[Blaze Wishlist] ${message}`);
			}
		}
	}

	/**
	 * Initialize all Blaze Product Image blocks
	 */
	function initBlazeProductImages() {
		$('.blaze-product-image').each(function () {
			const instance = new BlazeProductImage(this);
			$(this).data('blazeProductImage', instance);
		});
	}

	/**
	 * Document ready
	 */
	$(document).ready(function () {
		initBlazeProductImages();
	});

	/**
	 * Reinitialize on AJAX complete (for dynamic content)
	 */
	$(document).ajaxComplete(function () {
		initBlazeProductImages();
	});

	/**
	 * Expose to global scope
	 */
	window.BlazeProductImage = BlazeProductImage;
	window.initBlazeProductImages = initBlazeProductImages;

})(jQuery);

