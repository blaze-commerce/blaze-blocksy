/**
 * My Account Customizer Live Preview
 *
 * Handles live preview functionality for my-account form customization
 * in the WordPress Customizer.
 */

(function ($) {
	'use strict';

	// Wait for customizer to be ready
	wp.customize.bind(
		'ready',
		function () {
			console.log( '🎨 My Account Customizer Preview initialized' );

			// Initialize live preview handlers
			initTypographyPreview();
			initColorPreview();
			initSpacingPreview();
			initResponsivePreview();
			initTemplatePreview();
		}
	);

	/**
	 * Initialize typography live preview
	 */
	function initTypographyPreview() {
		var elements   = ['heading', 'body', 'placeholder', 'button'];
		var properties = ['font', 'font_size', 'font_color', 'font_weight', 'text_transform'];

		elements.forEach(
			function (element) {
				properties.forEach(
					function (property) {
						var settingId = 'blocksy_child_my_account_' + element + '_' + property;

						wp.customize(
							settingId,
							function (value) {
								value.bind(
									function (newValue) {
										updateElementStyle( element, property, newValue );
									}
								);
							}
						);
					}
				);
			}
		);
	}

	/**
	 * Initialize color live preview
	 */
	function initColorPreview() {
		// Button colors
		var buttonColors = [
			'button_color',
			'button_text_color',
			'button_hover_color',
			'button_hover_text_color'
		];

		buttonColors.forEach(
			function (colorType) {
				var settingId = 'blocksy_child_my_account_' + colorType;

				wp.customize(
					settingId,
					function (value) {
						value.bind(
							function (newValue) {
								updateButtonColor( colorType, newValue );
							}
						);
					}
				);
			}
		);

		// Input colors
		var inputColors = [
			'input_background_color',
			'input_border_color',
			'input_text_color'
		];

		inputColors.forEach(
			function (colorType) {
				var settingId = 'blocksy_child_my_account_' + colorType;

				wp.customize(
					settingId,
					function (value) {
						value.bind(
							function (newValue) {
								updateInputColor( colorType, newValue );
							}
						);
					}
				);
			}
		);
	}

	/**
	 * Initialize spacing live preview
	 */
	function initSpacingPreview() {
		var paddingSides = ['top', 'right', 'bottom', 'left'];

		paddingSides.forEach(
			function (side) {
				var settingId = 'blocksy_child_my_account_button_padding_' + side;

				wp.customize(
					settingId,
					function (value) {
						value.bind(
							function (newValue) {
								updateButtonPadding();
							}
						);
					}
				);
			}
		);

		// Border radius binding
		wp.customize(
			'blocksy_child_my_account_button_border_radius',
			function (value) {
				value.bind(
					function (newValue) {
						updateButtonBorderRadius();
					}
				);
			}
		);
	}

	/**
	 * Initialize responsive preview
	 */
	function initResponsivePreview() {
		var devices    = ['tablet', 'mobile'];
		var elements   = ['heading', 'body', 'placeholder', 'button'];
		var properties = ['font_size', 'font_weight'];

		devices.forEach(
			function (device) {
				elements.forEach(
					function (element) {
						properties.forEach(
							function (property) {
								var settingId = 'blocksy_child_my_account_' + device + '_' + element + '_' + property;

								wp.customize(
									settingId,
									function (value) {
										value.bind(
											function (newValue) {
												updateResponsiveStyle( device, element, property, newValue );
											}
										);
									}
								);
							}
						);
					}
				);
			}
		);
	}

	/**
	 * Initialize template preview
	 */
	function initTemplatePreview() {
		wp.customize(
			'blocksy_child_my_account_template',
			function (value) {
				value.bind(
					function (newValue) {
						// Template changes require a refresh
						wp.customize.preview.send( 'refresh' );
					}
				);
			}
		);
	}

	/**
	 * Update element style
	 */
	function updateElementStyle(element, property, value) {
		var template = wp.customize( 'blocksy_child_my_account_template' )();
		if (template === 'default') {
			return;
		}

		var selector    = getElementSelector( element, template );
		var cssProperty = getCSSProperty( property );

		if (selector && cssProperty) {
			updateCSS( selector, cssProperty, value );
		}
	}

	/**
	 * Update button colors
	 */
	function updateButtonColor(colorType, value) {
		var template = wp.customize( 'blocksy_child_my_account_template' )();
		if (template === 'default') {
			return;
		}

		var selector = '.blaze-login-register.' + template + ' button, .blaze-login-register.' + template + ' .button';

		switch (colorType) {
			case 'button_color':
				updateCSS( selector, 'background-color', value );
				break;
			case 'button_text_color':
				updateCSS( selector, 'color', value );
				break;
			case 'button_hover_color':
				updateCSS( selector + ':hover', 'background-color', value );
				break;
			case 'button_hover_text_color':
				updateCSS( selector + ':hover', 'color', value );
				break;
		}
	}

	/**
	 * Update input colors
	 */
	function updateInputColor(colorType, value) {
		var template = wp.customize( 'blocksy_child_my_account_template' )();
		if (template === 'default') {
			return;
		}

		var selector = '.blaze-login-register.' + template + ' input[type="text"], ' +
						'.blaze-login-register.' + template + ' input[type="email"], ' +
						'.blaze-login-register.' + template + ' input[type="password"]';

		switch (colorType) {
			case 'input_background_color':
				updateCSS( selector, 'background-color', value );
				break;
			case 'input_border_color':
				updateCSS( selector, 'border-color', value );
				break;
			case 'input_text_color':
				updateCSS( selector, 'color', value );
				break;
		}
	}

	/**
	 * Update button padding
	 */
	function updateButtonPadding() {
		var template = wp.customize( 'blocksy_child_my_account_template' )();
		if (template === 'default') {
			return;
		}

		var top    = wp.customize( 'blocksy_child_my_account_button_padding_top' )() || '12px';
		var right  = wp.customize( 'blocksy_child_my_account_button_padding_right' )() || '24px';
		var bottom = wp.customize( 'blocksy_child_my_account_button_padding_bottom' )() || '12px';
		var left   = wp.customize( 'blocksy_child_my_account_button_padding_left' )() || '24px';

		var selector     = '.blaze-login-register.' + template + ' button, .blaze-login-register.' + template + ' .button';
		var paddingValue = top + ' ' + right + ' ' + bottom + ' ' + left;

		updateCSS( selector, 'padding', paddingValue );
	}

	/**
	 * Update button border radius
	 */
	function updateButtonBorderRadius() {
		var template = wp.customize( 'blocksy_child_my_account_template' )();
		if (template === 'default') {
			return;
		}

		var borderRadius = wp.customize( 'blocksy_child_my_account_button_border_radius' )() || '3px';
		var selector     = '.blaze-login-register.' + template + ' button, .blaze-login-register.' + template + ' .button';

		updateCSS( selector, 'border-radius', borderRadius );
	}

	/**
	 * Update responsive styles
	 */
	function updateResponsiveStyle(device, element, property, value) {
		var template = wp.customize( 'blocksy_child_my_account_template' )();
		if (template === 'default') {
			return;
		}

		var selector    = getElementSelector( element, template );
		var cssProperty = getCSSProperty( property );
		var mediaQuery  = getMediaQuery( device );

		if (selector && cssProperty && mediaQuery) {
			updateResponsiveCSS( mediaQuery, selector, cssProperty, value );
		}
	}

	/**
	 * Get element selector
	 */
	function getElementSelector(element, template) {
		var selectors = {
			'heading': '.blaze-login-register.' + template + ' h2',
			'body': '.blaze-login-register.' + template + ' p, .blaze-login-register.' + template + ' label, .blaze-login-register.' + template + ' span, .blaze-login-register.' + template + ' a',
			'placeholder': '.blaze-login-register.' + template + ' input::placeholder',
			'button': '.blaze-login-register.' + template + ' button, .blaze-login-register.' + template + ' .button'
		};

		return selectors[element] || '';
	}

	/**
	 * Get CSS property name
	 */
	function getCSSProperty(property) {
		var properties = {
			'font': 'font-family',
			'font_size': 'font-size',
			'font_color': 'color',
			'font_weight': 'font-weight',
			'text_transform': 'text-transform'
		};

		return properties[property] || '';
	}

	/**
	 * Get media query for device
	 */
	function getMediaQuery(device) {
		var queries = {
			'tablet': '(max-width: 1023px) and (min-width: 768px)',
			'mobile': '(max-width: 767px)'
		};

		return queries[device] || '';
	}

	/**
	 * Update CSS rule
	 */
	function updateCSS(selector, property, value) {
		var styleId = 'blocksy-my-account-customizer-preview';
		var $style  = $( '#' + styleId );

		if ($style.length === 0) {
			$style = $( '<style id="' + styleId + '"></style>' ).appendTo( 'head' );
		}

		var css  = $style.text();
		var rule = selector + ' { ' + property + ': ' + value + ' !important; }';

		// Remove existing rule for this selector and property
		var regex = new RegExp( selector.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ) + '\\s*{[^}]*' + property.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ) + '[^}]*}', 'g' );
		css       = css.replace( regex, '' );

		// Add new rule
		css += rule;

		$style.text( css );
	}

	/**
	 * Update responsive CSS rule
	 */
	function updateResponsiveCSS(mediaQuery, selector, property, value) {
		var styleId = 'blocksy-my-account-customizer-responsive-preview';
		var $style  = $( '#' + styleId );

		if ($style.length === 0) {
			$style = $( '<style id="' + styleId + '"></style>' ).appendTo( 'head' );
		}

		var css  = $style.text();
		var rule = '@media ' + mediaQuery + ' { ' + selector + ' { ' + property + ': ' + value + ' !important; } }';

		// Remove existing rule
		var regex = new RegExp( '@media\\s+' + mediaQuery.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ) + '\\s*{[^}]*' + selector.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ) + '[^}]*' + property.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ) + '[^}]*}[^}]*}', 'g' );
		css       = css.replace( regex, '' );

		// Add new rule
		css += rule;

		$style.text( css );
	}

})( jQuery );
