/**
 * AJAX Search for WooCommerce - Custom Suggestion Template Override
 * 
 * This script overrides the default AJAX search suggestion template
 * to match the "recommended-product-item" structure from the mini-cart component.
 * 
 * @package Blaze Blocksy
 * @since 1.0.0
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Wait for autocomplete to be initialized
		var checkAutocomplete = setInterval(function() {
			var searchInput = document.querySelector('.dgwt-wcas-search-input');
			
			if (searchInput && searchInput.autocomplete) {
				clearInterval(checkAutocomplete);
				initializeCustomTemplate(searchInput);
			}
		}, 100);

		// Fallback: also check on document events
		$(document).on('dgwt_wcas_initialized', function(e, instance) {
			if (instance && instance.createProductSuggestion) {
				overrideCreateProductSuggestion(instance);
			}
		});
	});

	/**
	 * Initialize custom template override
	 */
	function initializeCustomTemplate(searchInput) {
		// Get the autocomplete instance
		var instance = $(searchInput).data('autocomplete');
		
		if (instance && instance.createProductSuggestion) {
			overrideCreateProductSuggestion(instance);
		}
	}

	/**
	 * Override the createProductSuggestion method
	 */
	function overrideCreateProductSuggestion(instance) {
		var originalCreateProductSuggestion = instance.createProductSuggestion;

		instance.createProductSuggestion = function(suggestion, index) {
			// Check if we should use custom template
			if (suggestion.use_custom_template) {
				return createCustomProductSuggestion.call(this, suggestion, index);
			}
			
			// Fall back to original template
			return originalCreateProductSuggestion.call(this, suggestion, index);
		};
	}

	/**
	 * Create custom product suggestion HTML matching recommended-product-item structure
	 */
	function createCustomProductSuggestion(suggestion, index) {
		var url = typeof suggestion.url === 'string' && suggestion.url.length ? suggestion.url : '#';
		var dataAttrs = '';
		
		// Build data attributes
		dataAttrs += typeof suggestion.post_id !== 'undefined' ? 'data-post-id="' + suggestion.post_id + '" ' : '';
		dataAttrs += typeof suggestion.taxonomy !== 'undefined' ? 'data-taxonomy="' + suggestion.taxonomy + '" ' : '';
		dataAttrs += typeof suggestion.term_id !== 'undefined' ? 'data-term-id="' + suggestion.term_id + '" ' : '';
		
		// Get image HTML
		var imageHtml = '';
		if (typeof suggestion.thumb_html !== 'undefined' && suggestion.thumb_html) {
			imageHtml = suggestion.thumb_html;
		}
		
		// Get price HTML
		var priceHtml = '';
		if (typeof suggestion.price !== 'undefined' && suggestion.price) {
			priceHtml = '<div class="product-price">' + suggestion.price + '</div>';
		}
		
		// Build the custom HTML structure
		var html = '<div class="recommended-product-item dgwt-wcas-suggestion dgwt-wcas-suggestion-product" data-index="' + index + '" ' + dataAttrs + '>';
		html += '<a href="' + url + '" class="product-link">';
		
		// Product image
		if (imageHtml) {
			html += '<div class="product-image">' + imageHtml + '</div>';
		}
		
		// Product info
		html += '<div class="product-info">';
		html += '<h5 class="product-title">' + suggestion.value + '</h5>';
		
		// SKU if available
		if (typeof suggestion.sku !== 'undefined' && suggestion.sku) {
			html += '<span class="product-sku">SKU: ' + suggestion.sku + '</span>';
		}
		
		// Price
		if (priceHtml) {
			html += priceHtml;
		}
		
		html += '</div>';
		html += '</a>';
		html += '</div>';
		
		return html;
	}

	/**
	 * Add hover effects to custom suggestions
	 */
	$(document).on('mouseenter', '.recommended-product-item.dgwt-wcas-suggestion', function() {
		$(this).addClass('hover-effect');
	}).on('mouseleave', '.recommended-product-item.dgwt-wcas-suggestion', function() {
		$(this).removeClass('hover-effect');
	});

})(jQuery);

