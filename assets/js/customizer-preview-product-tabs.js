/**
 * Product Tabs - Customizer Live Preview
 *
 * Handles instant live preview updates for Product Tabs element
 * by directly manipulating CSS variables without page refresh.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function ($) {
	'use strict';

	// Helper function to update CSS variable on element
	function updateCSSVariable(selector, variable, value) {
		const elements = document.querySelectorAll(selector);
		elements.forEach(function (el) {
			el.style.setProperty(variable, value);
		});
	}

	// Bottom Spacing sync via woo_single_layout
	wp.customize('woo_single_layout', function (value) {
		value.bind(function (newValue) {
			if (!Array.isArray(newValue)) return;

			const layer = newValue.find(function (l) {
				return l.id === 'product_tabs_element';
			});

			if (layer && layer.spacing !== undefined) {
				const spacing = typeof layer.spacing === 'object' ? layer.spacing.desktop : layer.spacing;
				updateCSSVariable(
					'.entry-summary-items > .ct-product-tabs-element',
					'--product-element-spacing',
					spacing + 'px'
				);
			}
		});
	});

})(jQuery);

