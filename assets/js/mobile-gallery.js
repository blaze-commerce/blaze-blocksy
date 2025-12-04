/**
 * Mobile Gallery - OwlCarousel + Fancybox Integration
 *
 * @package Blaze_Blocksy
 * @since 1.44.0
 */

(function ($) {
	'use strict';

	if (typeof $ === 'undefined') {
		console.warn('Blaze Mobile Gallery: jQuery is required');
		return;
	}

	var BlazeGallery = {
		init: function () {
			this.initMainSlider();
			this.initThumbnails();
			this.initFancybox();
			this.syncSliders();
		},

		initMainSlider: function () {
			var $main = $('.blaze-gallery-main');
			if (!$main.length) return;

			$main.owlCarousel({
				items: 1,
				loop: false,
				nav: true,
				dots: false,
				navText: [
					'<svg width="16" height="10" fill="currentColor" viewBox="0 0 16 10"><path d="M15.3 4.3h-13l2.8-3c.3-.3.3-.7 0-1-.3-.3-.6-.3-.9 0l-4 4.2-.2.2v.6c0 .1.1.2.2.2l4 4.2c.3.4.6.4.9 0 .3-.3.3-.7 0-1l-2.8-3h13c.2 0 .4-.1.5-.2s.2-.3.2-.5-.1-.4-.2-.5c-.1-.1-.3-.2-.5-.2z"></path></svg>',
					'<svg width="16" height="10" fill="currentColor" viewBox="0 0 16 10"><path d="M.2 4.5c-.1.1-.2.3-.2.5s.1.4.2.5c.1.1.3.2.5.2h13l-2.8 3c-.3.3-.3.7 0 1 .3.3.6.3.9 0l4-4.2.2-.2V5v-.3c0-.1-.1-.2-.2-.2l-4-4.2c-.3-.4-.6-.4-.9 0-.3.3-.3.7 0 1l2.8 3H.7c-.2 0-.4.1-.5.2z"></path></svg>'
				],
				touchDrag: true,
				mouseDrag: true,
				autoHeight: false,
				lazyLoad: true
			});

			this.$mainSlider = $main;
		},

		initThumbnails: function () {
			var $thumbs = $('.blaze-gallery-thumbs');
			if (!$thumbs.length) return;

			$thumbs.owlCarousel({
				items: 4,
				loop: false,
				nav: false,
				dots: false,
				margin: 8,
				touchDrag: true,
				mouseDrag: true,
				responsive: {
					0: { items: 4 },
					480: { items: 5 },
					768: { items: 6 }
				}
			});

			this.$thumbsSlider = $thumbs;

			// Click on thumbnail to change main slide
			var self = this;
			$thumbs.find('.blaze-thumb-item').on('click', function () {
				var index = $(this).data('index');
				self.$mainSlider.trigger('to.owl.carousel', [index, 300]);
				self.updateActiveThumb(index);
			});
		},

		updateActiveThumb: function (index) {
			$('.blaze-thumb-item').removeClass('active');
			$('.blaze-thumb-item[data-index="' + index + '"]').addClass('active');
		},

		syncSliders: function () {
			var self = this;
			if (!this.$mainSlider) return;

			this.$mainSlider.on('changed.owl.carousel', function (e) {
				var index = e.item.index;
				self.updateActiveThumb(index);

				// Move thumbs slider to show active thumb
				if (self.$thumbsSlider) {
					self.$thumbsSlider.trigger('to.owl.carousel', [Math.max(0, index - 1), 300]);
				}
			});
		},

		initFancybox: function () {
			if (typeof Fancybox === 'undefined') {
				console.warn('Blaze Mobile Gallery: Fancybox is not loaded');
				return;
			}

			Fancybox.bind('[data-fancybox="blaze-gallery"]', {
				animated: true,
				showClass: 'fancybox-fadeIn',
				hideClass: 'fancybox-fadeOut',
				dragToClose: true,
				Toolbar: {
					display: {
						left: ['infobar'],
						middle: [],
						right: ['close']
					}
				},
				Images: {
					zoom: true
				},
				Thumbs: {
					type: 'classic'
				}
			});
		}
	};

	// Initialize on document ready
	$(document).ready(function () {
		if ($('.blaze-gallery-container').length) {
			BlazeGallery.init();
		}
	});

	// Re-initialize on AJAX (for variable products, quick view, etc.)
	$(document).on('blaze_gallery_init', function () {
		BlazeGallery.init();
	});

})(jQuery);

