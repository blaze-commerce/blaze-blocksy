/**
 * Fluid Checkout Customizer Live Preview
 *
 * Provides real-time preview updates for Fluid Checkout customizer settings
 * using the WordPress Customizer postMessage API.
 *
 * @package Blocksy_Child
 * @since 1.0.0
 */

(function ($) {
	'use strict';

	// Wait for customizer to be ready
	wp.customize('blogname', function (value) {
		// General Colors
		const colorSettings = {
			blocksy_fc_primary_color: '--fluidtheme--color--primary',
			blocksy_fc_secondary_color: '--fluidtheme--color--secondary',
			blocksy_fc_body_text_color: '--fluidtheme--color--body-text',
			blocksy_fc_heading_color: '--fluidtheme--color--heading',
			blocksy_fc_link_color: '--fluidtheme--color--link',
			blocksy_fc_link_hover_color: '--fluidtheme--color--link--hover',
			blocksy_fc_content_background: '--fluidtheme--color--content-background',
			blocksy_fc_border_color: '--fluidtheme--color--border',
		};

		// Update CSS variables for colors
		Object.keys(colorSettings).forEach(function (setting) {
			wp.customize(setting, function (value) {
				value.bind(function (newval) {
					document.documentElement.style.setProperty(
						colorSettings[setting],
						newval
					);
				});
			});
		});

		// Typography Settings
		const typographyElements = {
			heading: '.woocommerce-checkout h1, .woocommerce-checkout h2, .woocommerce-checkout h3, .fc-step__title',
			body: '.woocommerce-checkout, .woocommerce-checkout p, .woocommerce-checkout span',
			label: '.woocommerce-checkout label, .form-row label',
			placeholder: '.woocommerce-checkout input::placeholder, .woocommerce-checkout textarea::placeholder',
			button: '.woocommerce-checkout button, .woocommerce-checkout .button',
		};

		Object.keys(typographyElements).forEach(function (element) {
			const selector = typographyElements[element];

			// Font Family
			wp.customize('blocksy_fc_' + element + '_font_family', function (value) {
				value.bind(function (newval) {
					$(selector).css('font-family', newval);
				});
			});

			// Font Size
			wp.customize('blocksy_fc_' + element + '_font_size', function (value) {
				value.bind(function (newval) {
					$(selector).css('font-size', newval);
				});
			});

			// Font Color
			wp.customize('blocksy_fc_' + element + '_font_color', function (value) {
				value.bind(function (newval) {
					$(selector).css('color', newval);
				});
			});

			// Font Weight
			wp.customize('blocksy_fc_' + element + '_font_weight', function (value) {
				value.bind(function (newval) {
					$(selector).css('font-weight', newval);
				});
			});
		});

		// Form Elements
		const formInputs = [
			'.woocommerce-checkout input[type="text"]',
			'.woocommerce-checkout input[type="email"]',
			'.woocommerce-checkout input[type="tel"]',
			'.woocommerce-checkout input[type="password"]',
			'.woocommerce-checkout textarea',
			'.woocommerce-checkout select',
		].join(', ');

		wp.customize('blocksy_fc_input_background', function (value) {
			value.bind(function (newval) {
				$(formInputs).css('background-color', newval);
			});
		});

		wp.customize('blocksy_fc_input_border_color', function (value) {
			value.bind(function (newval) {
				$(formInputs).css('border-color', newval);
			});
		});

		wp.customize('blocksy_fc_input_text_color', function (value) {
			value.bind(function (newval) {
				$(formInputs).css('color', newval);
			});
		});

		wp.customize('blocksy_fc_input_focus_border', function (value) {
			value.bind(function (newval) {
				// Add dynamic style for focus state
				updateDynamicStyle('input-focus-border', formInputs + ':focus { border-color: ' + newval + ' !important; }');
			});
		});

		wp.customize('blocksy_fc_input_padding', function (value) {
			value.bind(function (newval) {
				$(formInputs).css('padding', newval);
			});
		});

		wp.customize('blocksy_fc_input_border_radius', function (value) {
			value.bind(function (newval) {
				$(formInputs).css('border-radius', newval);
			});
		});

		// Buttons
		const buttonSelectors = [
			'.woocommerce-checkout button.button',
			'.woocommerce-checkout .button',
			'.woocommerce-checkout input[type="submit"]',
			'.woocommerce-checkout #place_order',
		].join(', ');

		wp.customize('blocksy_fc_button_primary_bg', function (value) {
			value.bind(function (newval) {
				$(buttonSelectors).css('background-color', newval);
			});
		});

		wp.customize('blocksy_fc_button_primary_text', function (value) {
			value.bind(function (newval) {
				$(buttonSelectors).css('color', newval);
			});
		});

		wp.customize('blocksy_fc_button_primary_hover_bg', function (value) {
			value.bind(function (newval) {
				updateDynamicStyle('button-hover-bg', buttonSelectors + ':hover { background-color: ' + newval + ' !important; }');
			});
		});

		wp.customize('blocksy_fc_button_primary_hover_text', function (value) {
			value.bind(function (newval) {
				updateDynamicStyle('button-hover-text', buttonSelectors + ':hover { color: ' + newval + ' !important; }');
			});
		});

		// Button Padding
		const buttonPaddingSettings = ['top', 'right', 'bottom', 'left'];
		buttonPaddingSettings.forEach(function (side) {
			wp.customize('blocksy_fc_button_padding_' + side, function (value) {
				value.bind(function (newval) {
					updateButtonPadding();
				});
			});
		});

		function updateButtonPadding() {
			const top = wp.customize('blocksy_fc_button_padding_top')() || '12px';
			const right = wp.customize('blocksy_fc_button_padding_right')() || '24px';
			const bottom = wp.customize('blocksy_fc_button_padding_bottom')() || '12px';
			const left = wp.customize('blocksy_fc_button_padding_left')() || '24px';
			$(buttonSelectors).css('padding', top + ' ' + right + ' ' + bottom + ' ' + left);
		}

		wp.customize('blocksy_fc_button_border_radius', function (value) {
			value.bind(function (newval) {
				$(buttonSelectors).css('border-radius', newval);
			});
		});

		// Spacing
		const sectionSelectors = [
			'.woocommerce-checkout .fc-step',
			'.woocommerce-checkout .fc-cart-section',
			'.woocommerce-checkout .woocommerce-checkout-review-order',
		].join(', ');

		const spacingSettings = ['top', 'right', 'bottom', 'left'];
		spacingSettings.forEach(function (side) {
			wp.customize('blocksy_fc_section_padding_' + side, function (value) {
				value.bind(function (newval) {
					updateSectionPadding();
				});
			});
		});

		function updateSectionPadding() {
			const top = wp.customize('blocksy_fc_section_padding_top')() || '20px';
			const right = wp.customize('blocksy_fc_section_padding_right')() || '20px';
			const bottom = wp.customize('blocksy_fc_section_padding_bottom')() || '20px';
			const left = wp.customize('blocksy_fc_section_padding_left')() || '20px';
			$(sectionSelectors).css('padding', top + ' ' + right + ' ' + bottom + ' ' + left);
		}

		wp.customize('blocksy_fc_section_margin_bottom', function (value) {
			value.bind(function (newval) {
				$('.woocommerce-checkout .fc-step, .woocommerce-checkout .fc-cart-section').css('margin-bottom', newval);
			});
		});

		wp.customize('blocksy_fc_field_gap', function (value) {
			value.bind(function (newval) {
				$('.woocommerce-checkout .form-row').css('margin-bottom', newval);
			});
		});

		// Borders
		wp.customize('blocksy_fc_section_border_width', function (value) {
			value.bind(function (newval) {
				updateSectionBorder();
			});
		});

		wp.customize('blocksy_fc_section_border_color', function (value) {
			value.bind(function (newval) {
				updateSectionBorder();
			});
		});

		wp.customize('blocksy_fc_section_border_style', function (value) {
			value.bind(function (newval) {
				updateSectionBorder();
			});
		});

		function updateSectionBorder() {
			const width = wp.customize('blocksy_fc_section_border_width')() || '1px';
			const color = wp.customize('blocksy_fc_section_border_color')() || '#dfdfde';
			const style = wp.customize('blocksy_fc_section_border_style')() || 'solid';
			$(sectionSelectors).css('border', width + ' ' + style + ' ' + color);
		}

		wp.customize('blocksy_fc_section_border_radius', function (value) {
			value.bind(function (newval) {
				$(sectionSelectors).css('border-radius', newval);
			});
		});

		// Helper function to update dynamic styles
		function updateDynamicStyle(id, css) {
			let styleElement = document.getElementById('fc-customizer-dynamic-' + id);
			if (!styleElement) {
				styleElement = document.createElement('style');
				styleElement.id = 'fc-customizer-dynamic-' + id;
				document.head.appendChild(styleElement);
			}
			styleElement.textContent = css;
		}
	});
})(jQuery);

