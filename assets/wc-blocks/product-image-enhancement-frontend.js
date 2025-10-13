/**
 * Product Image Block - Enhancement Frontend
 *
 * Handles hover image swap and wishlist button functionality on the frontend.
 * Integrates with Blocksy wishlist functionality.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function ($) {
	'use strict';

	/**
	 * Product Image Enhancement Handler
	 */
	class ProductImageEnhancements {
		/**
		 * Constructor
		 */
		constructor() {
			this.init();
		}

		/**
		 * Initialize the handler
		 */
		init() {
			this.setupHoverImages();
			this.setupWishlistButtons();
		}

		/**
		 * Setup hover image functionality for all enhanced images
		 */
		setupHoverImages() {
			$('.wc-hover-image-enabled').each((index, element) => {
				this.initHoverImage($(element));
			});
		}

		/**
		 * Initialize hover image for a single product image
		 *
		 * @param {jQuery} $container Image container element
		 */
		initHoverImage($container) {
			const hoverImageData = $container.data('hover-image');
			if (!hoverImageData) {
				return;
			}

			const $image = $container.find('img').first();
			if (!$image.length) {
				return;
			}

			// Store original image data
			const originalSrc = $image.attr('src');
			const originalSrcset = $image.attr('srcset') || '';
			const originalAlt = $image.attr('alt') || '';

			// Preload hover image for smooth transition
			const preloadImage = new Image();
			preloadImage.src = hoverImageData.url;

			// Handle mouse enter
			$container.on('mouseenter', () => {
				$image.attr('src', hoverImageData.url);
				if (hoverImageData.srcset) {
					$image.attr('srcset', hoverImageData.srcset);
				}
				if (hoverImageData.alt) {
					$image.attr('alt', hoverImageData.alt);
				}
				$image.addClass('wc-hover-image-active');
			});

			// Handle mouse leave
			$container.on('mouseleave', () => {
				$image.attr('src', originalSrc);
				if (originalSrcset) {
					$image.attr('srcset', originalSrcset);
				}
				$image.attr('alt', originalAlt);
				$image.removeClass('wc-hover-image-active');
			});
		}

		/**
		 * Setup wishlist buttons for all enhanced images
		 */
		setupWishlistButtons() {
			$('.wc-wishlist-enabled').each((index, element) => {
				this.initWishlistButton($(element));
			});
		}

		/**
		 * Initialize wishlist button for a single product image
		 *
		 * @param {jQuery} $container Image container element
		 */
		initWishlistButton($container) {
			const $button = $container.find('.wc-wishlist-button');
			if (!$button.length) {
				return;
			}

			$button.on('click', (e) => {
				e.preventDefault();
				e.stopPropagation();

				const productId = $button.data('product-id');
				this.toggleWishlist(productId, $button);
			});
		}

		/**
		 * Toggle wishlist status for a product
		 *
		 * @param {number} productId Product ID
		 * @param {jQuery} $button Wishlist button element
		 */
		toggleWishlist(productId, $button) {
			const isInWishlist = $button.hasClass('wc-wishlist-added');

			// Add loading state
			$button.addClass('wc-wishlist-loading').prop('disabled', true);

			// Check if Blocksy wishlist is available
			if (typeof window.blocksyWishlist !== 'undefined') {
				// Use Blocksy wishlist functionality
				this.toggleBlocksyWishlist(productId, $button, isInWishlist);
			} else {
				// Fallback to custom AJAX implementation
				this.toggleCustomWishlist(productId, $button, isInWishlist);
			}
		}

		/**
		 * Toggle wishlist using Blocksy wishlist functionality
		 *
		 * @param {number} productId Product ID
		 * @param {jQuery} $button Wishlist button element
		 * @param {boolean} isInWishlist Current wishlist status
		 */
		toggleBlocksyWishlist(productId, $button, isInWishlist) {
			// Trigger Blocksy wishlist action
			const action = isInWishlist ? 'remove' : 'add';
			
			// Find Blocksy wishlist button if exists and trigger it
			const $blocksyButton = $(`.ct-wishlist-button[data-id="${productId}"]`);
			if ($blocksyButton.length) {
				$blocksyButton.trigger('click');
				
				// Update button state
				setTimeout(() => {
					$button.toggleClass('wc-wishlist-added');
					$button.removeClass('wc-wishlist-loading').prop('disabled', false);
					
					const message = isInWishlist 
						? wcBlockImageEnhancements.messages.removed 
						: wcBlockImageEnhancements.messages.added;
					this.showMessage(message, 'success');
				}, 300);
			} else {
				// Fallback to custom implementation
				this.toggleCustomWishlist(productId, $button, isInWishlist);
			}
		}

		/**
		 * Toggle wishlist using custom AJAX implementation
		 *
		 * @param {number} productId Product ID
		 * @param {jQuery} $button Wishlist button element
		 * @param {boolean} isInWishlist Current wishlist status
		 */
		toggleCustomWishlist(productId, $button, isInWishlist) {
			$.ajax({
				url: wcBlockImageEnhancements.ajax_url,
				type: 'POST',
				data: {
					action: 'wc_block_toggle_wishlist',
					product_id: productId,
					wishlist_action: isInWishlist ? 'remove' : 'add',
					nonce: wcBlockImageEnhancements.nonce
				},
				success: (response) => {
					if (response.success) {
						$button.toggleClass('wc-wishlist-added');
						this.showMessage(response.data.message, 'success');
						
						// Trigger custom event for other scripts to listen
						$(document).trigger('wc-block-wishlist-updated', {
							productId: productId,
							action: isInWishlist ? 'remove' : 'add'
						});
					} else {
						this.showMessage(response.data.message || wcBlockImageEnhancements.messages.error, 'error');
					}
				},
				error: () => {
					this.showMessage(wcBlockImageEnhancements.messages.error, 'error');
				},
				complete: () => {
					$button.removeClass('wc-wishlist-loading').prop('disabled', false);
				}
			});
		}

		/**
		 * Show notification message
		 *
		 * @param {string} message Message text
		 * @param {string} type Message type (success, error)
		 */
		showMessage(message, type = 'success') {
			const $message = $(`<div class="wc-wishlist-message wc-wishlist-message--${type}">${message}</div>`);
			$('body').append($message);

			// Animate in
			setTimeout(() => {
				$message.addClass('wc-wishlist-message--visible');
			}, 10);

			// Animate out and remove
			setTimeout(() => {
				$message.removeClass('wc-wishlist-message--visible');
				setTimeout(() => {
					$message.remove();
				}, 300);
			}, 3000);
		}
	}

	/**
	 * Initialize when DOM is ready
	 */
	$(document).ready(() => {
		new ProductImageEnhancements();
	});

	/**
	 * Re-initialize on dynamic content load
	 */
	$(document).on('updated_wc_div', () => {
		new ProductImageEnhancements();
	});

})(jQuery);

