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

	/**
	 * Check if all required dependencies are available
	 *
	 * @return {boolean} True if all dependencies are met, false otherwise
	 */
	function checkDependencies() {
		// Check for WordPress Customizer API
		if (typeof wp === 'undefined' || typeof wp.customize === 'undefined') {
			console.warn('Fluid Checkout Customizer: WordPress Customizer API not available');
			return false;
		}

		// Check for jQuery
		if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
			console.warn('Fluid Checkout Customizer: jQuery not available');
			return false;
		}

		// Check for document.documentElement
		if (!document.documentElement) {
			console.warn('Fluid Checkout Customizer: document.documentElement not available');
			return false;
		}

		return true;
	}

	/**
	 * Safely set CSS property on element
	 *
	 * @param {string} selector - CSS selector
	 * @param {string} property - CSS property name
	 * @param {string} value - CSS property value
	 */
	function safeSetCSS(selector, property, value) {
		try {
			const elements = $(selector);
			if (elements.length > 0) {
				elements.css(property, value);
			}
		} catch (error) {
			console.warn('Fluid Checkout Customizer: Error setting CSS for ' + selector, error);
		}
	}

	/**
	 * Safely set CSS variable on document root
	 *
	 * @param {string} variable - CSS variable name
	 * @param {string} value - CSS variable value
	 */
	function safeSetCSSVariable(variable, value) {
		try {
			if (document.documentElement && document.documentElement.style) {
				document.documentElement.style.setProperty(variable, value);
			}
		} catch (error) {
			console.warn('Fluid Checkout Customizer: Error setting CSS variable ' + variable, error);
		}
	}

	// Early return if dependencies not met
	if (!checkDependencies()) {
		return;
	}

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
			try {
				wp.customize(setting, function (value) {
					value.bind(function (newval) {
						safeSetCSSVariable(colorSettings[setting], newval);
					});
				});
			} catch (error) {
				console.warn('Fluid Checkout Customizer: Error binding color setting ' + setting, error);
			}
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

			try {
				// Font Family
				wp.customize('blocksy_fc_' + element + '_font_family', function (value) {
					value.bind(function (newval) {
						safeSetCSS(selector, 'font-family', newval);
					});
				});

				// Font Size
				wp.customize('blocksy_fc_' + element + '_font_size', function (value) {
					value.bind(function (newval) {
						safeSetCSS(selector, 'font-size', newval);
					});
				});

				// Font Color
				wp.customize('blocksy_fc_' + element + '_font_color', function (value) {
					value.bind(function (newval) {
						safeSetCSS(selector, 'color', newval);
					});
				});

				// Font Weight
				wp.customize('blocksy_fc_' + element + '_font_weight', function (value) {
					value.bind(function (newval) {
						safeSetCSS(selector, 'font-weight', newval);
					});
				});
			} catch (error) {
				console.warn('Fluid Checkout Customizer: Error binding typography settings for ' + element, error);
			}
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

		try {
			wp.customize('blocksy_fc_input_background', function (value) {
				value.bind(function (newval) {
					safeSetCSS(formInputs, 'background-color', newval);
				});
			});

			wp.customize('blocksy_fc_input_border_color', function (value) {
				value.bind(function (newval) {
					safeSetCSS(formInputs, 'border-color', newval);
				});
			});

			wp.customize('blocksy_fc_input_text_color', function (value) {
				value.bind(function (newval) {
					safeSetCSS(formInputs, 'color', newval);
				});
			});

			wp.customize('blocksy_fc_input_focus_border', function (value) {
				value.bind(function (newval) {
					// Add dynamic style for focus state
					if (typeof updateDynamicStyle === 'function') {
						updateDynamicStyle('input-focus-border', formInputs + ':focus { border-color: ' + newval + ' !important; }');
					}
				});
			});

			wp.customize('blocksy_fc_input_padding', function (value) {
				value.bind(function (newval) {
					safeSetCSS(formInputs, 'padding', newval);
				});
			});

			wp.customize('blocksy_fc_input_border_radius', function (value) {
				value.bind(function (newval) {
					safeSetCSS(formInputs, 'border-radius', newval);
				});
			});
		} catch (error) {
			console.warn('Fluid Checkout Customizer: Error binding form element settings', error);
		}

		// Buttons
		const buttonSelectors = [
			'.woocommerce-checkout button.button',
			'.woocommerce-checkout .button',
			'.woocommerce-checkout input[type="submit"]',
			'.woocommerce-checkout #place_order',
		].join(', ');

		try {
			wp.customize('blocksy_fc_button_primary_bg', function (value) {
				value.bind(function (newval) {
					safeSetCSS(buttonSelectors, 'background-color', newval);
				});
			});

			wp.customize('blocksy_fc_button_primary_text', function (value) {
				value.bind(function (newval) {
					safeSetCSS(buttonSelectors, 'color', newval);
				});
			});

			wp.customize('blocksy_fc_button_primary_hover_bg', function (value) {
				value.bind(function (newval) {
					if (typeof updateDynamicStyle === 'function') {
						updateDynamicStyle('button-hover-bg', buttonSelectors + ':hover { background-color: ' + newval + ' !important; }');
					}
				});
			});

			wp.customize('blocksy_fc_button_primary_hover_text', function (value) {
				value.bind(function (newval) {
					if (typeof updateDynamicStyle === 'function') {
						updateDynamicStyle('button-hover-text', buttonSelectors + ':hover { color: ' + newval + ' !important; }');
					}
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
				try {
					const top = wp.customize('blocksy_fc_button_padding_top')() || '12px';
					const right = wp.customize('blocksy_fc_button_padding_right')() || '24px';
					const bottom = wp.customize('blocksy_fc_button_padding_bottom')() || '12px';
					const left = wp.customize('blocksy_fc_button_padding_left')() || '24px';
					safeSetCSS(buttonSelectors, 'padding', top + ' ' + right + ' ' + bottom + ' ' + left);
				} catch (error) {
					console.warn('Fluid Checkout Customizer: Error updating button padding', error);
				}
			}

			wp.customize('blocksy_fc_button_border_radius', function (value) {
				value.bind(function (newval) {
					safeSetCSS(buttonSelectors, 'border-radius', newval);
				});
			});
		} catch (error) {
			console.warn('Fluid Checkout Customizer: Error binding button settings', error);
		}

		// Spacing
		const sectionSelectors = [
			'.woocommerce-checkout .fc-step',
			'.woocommerce-checkout .fc-cart-section',
			'.woocommerce-checkout .woocommerce-checkout-review-order',
		].join(', ');

		try {
			const spacingSettings = ['top', 'right', 'bottom', 'left'];
			spacingSettings.forEach(function (side) {
				wp.customize('blocksy_fc_section_padding_' + side, function (value) {
					value.bind(function (newval) {
						updateSectionPadding();
					});
				});
			});

			function updateSectionPadding() {
				try {
					const top = wp.customize('blocksy_fc_section_padding_top')() || '20px';
					const right = wp.customize('blocksy_fc_section_padding_right')() || '20px';
					const bottom = wp.customize('blocksy_fc_section_padding_bottom')() || '20px';
					const left = wp.customize('blocksy_fc_section_padding_left')() || '20px';
					safeSetCSS(sectionSelectors, 'padding', top + ' ' + right + ' ' + bottom + ' ' + left);
				} catch (error) {
					console.warn('Fluid Checkout Customizer: Error updating section padding', error);
				}
			}

			wp.customize('blocksy_fc_section_margin_bottom', function (value) {
				value.bind(function (newval) {
					safeSetCSS('.woocommerce-checkout .fc-step, .woocommerce-checkout .fc-cart-section', 'margin-bottom', newval);
				});
			});

			wp.customize('blocksy_fc_field_gap', function (value) {
				value.bind(function (newval) {
					safeSetCSS('.woocommerce-checkout .form-row', 'margin-bottom', newval);
				});
			});
		} catch (error) {
			console.warn('Fluid Checkout Customizer: Error binding spacing settings', error);
		}

		// Borders
		try {
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
				try {
					const width = wp.customize('blocksy_fc_section_border_width')() || '1px';
					const color = wp.customize('blocksy_fc_section_border_color')() || '#dfdfde';
					const style = wp.customize('blocksy_fc_section_border_style')() || 'solid';
					safeSetCSS(sectionSelectors, 'border', width + ' ' + style + ' ' + color);
				} catch (error) {
					console.warn('Fluid Checkout Customizer: Error updating section border', error);
				}
			}

			wp.customize('blocksy_fc_section_border_radius', function (value) {
				value.bind(function (newval) {
					safeSetCSS(sectionSelectors, 'border-radius', newval);
				});
			});
		} catch (error) {
			console.warn('Fluid Checkout Customizer: Error binding border settings', error);
		}

		// Helper function to update dynamic styles
		function updateDynamicStyle(id, css) {
			try {
				if (!document.head) {
					console.warn('Fluid Checkout Customizer: document.head not available');
					return;
				}

				let styleElement = document.getElementById('fc-customizer-dynamic-' + id);
				if (!styleElement) {
					styleElement = document.createElement('style');
					styleElement.id = 'fc-customizer-dynamic-' + id;
					document.head.appendChild(styleElement);
				}
				styleElement.textContent = css;
			} catch (error) {
				console.warn('Fluid Checkout Customizer: Error updating dynamic style ' + id, error);
			}
		}

		// Content & Text Settings
		try {
			// My Contact Heading Text
			wp.customize('blocksy_fc_my_contact_heading_text', function (value) {
				value.bind(function (newval) {
					// Find the "My contact" heading and update it
					const contactHeading = $('.fc-step__substep-title:contains("My contact")');
					if (contactHeading.length > 0) {
						contactHeading.text(newval || 'My contact');
					}
				});
			});
		} catch (error) {
			console.warn('Fluid Checkout Customizer: Error binding content text settings', error);
		}
	});
})(jQuery);

