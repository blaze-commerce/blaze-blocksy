/**
 * Blocksy Child My Account Admin JavaScript
 *
 * Admin interface functionality for my-account customization settings.
 *
 * @package Blocksy_Child
 * @since 1.5.0
 */

jQuery( document ).ready(
	function ($) {
		/**
		 * Initialize color pickers
		 */
		function initColorPickers() {
			$( '.color-picker' ).wpColorPicker();
		}

		/**
		 * Initialize template preview
		 */
		function initTemplatePreview() {
			var $templateSelect = $( 'select[name="blocksy_child_my_account_template"]' );

			if ($templateSelect.length) {
				$templateSelect.on(
					'change',
					function () {
						var selectedTemplate = $( this ).val();
						showTemplatePreview( selectedTemplate );
					}
				);

				// Show initial preview
				showTemplatePreview( $templateSelect.val() );
			}
		}

		/**
		 * Show template preview
		 */
		function showTemplatePreview(template) {
			// Remove existing preview
			$( '.template-preview' ).remove();

			var previewHtml = '';

			switch (template) {
				case 'template1':
					previewHtml = '<div class="template-preview">' +
						'<h4>Template 1 - Side by Side Layout</h4>' +
						'<div style="display: flex; gap: 20px; border: 1px solid #ddd; padding: 20px; margin-top: 10px;">' +
							'<div style="flex: 1; border: 1px solid #ccc; padding: 15px;">' +
								'<strong>Login Form</strong>' +
								'<p>Username / Email field</p>' +
								'<p>Password field</p>' +
								'<p>Login button</p>' +
							'</div>' +
							'<div style="flex: 1; border: 1px solid #ccc; padding: 15px;">' +
								'<strong>Register Form</strong>' +
								'<p>Username field</p>' +
								'<p>Email field</p>' +
								'<p>Password field</p>' +
								'<p>Register button</p>' +
							'</div>' +
						'</div>' +
					'</div>';
					break;
				case 'template2':
					previewHtml = '<div class="template-preview">' +
						'<h4>Template 2 - Centered Layout</h4>' +
						'<div style="max-width: 400px; border: 1px solid #ddd; padding: 20px; margin: 10px auto; text-align: center;">' +
							'<strong>Login Form (Centered)</strong>' +
							'<p>Username / Email field</p>' +
							'<p>Password field</p>' +
							'<p>Login button</p>' +
							'<p><small>Toggle to Register form</small></p>' +
						'</div>' +
					'</div>';
					break;
				default:
					previewHtml = '<div class="template-preview">' +
						'<h4>Default WooCommerce Template</h4>' +
						'<p>Uses the standard WooCommerce my-account layout.</p>' +
					'</div>';
			}

			$( 'select[name="blocksy_child_my_account_template"]' ).closest( 'td' ).append( previewHtml );
		}

		/**
		 * Initialize form validation
		 */
		function initFormValidation() {
			$( 'form' ).on(
				'submit',
				function () {
					var isValid = true;

					// Validate font size inputs
					$( 'input[name*="font_size"]' ).each(
						function () {
							var value = $( this ).val();
							if (value && ! value.match( /^\d+(px|em|rem|%|pt|vh|vw|ex|ch)$/ )) {
								alert( 'Font size must include a valid CSS unit (px, em, rem, %, pt, vh, vw, ex, ch)' );
								$( this ).focus();
								isValid = false;
								return false;
							}
						}
					);

					// Validate padding inputs
					$( 'input[name*="padding"]' ).each(
						function () {
							var value = $( this ).val();
							if (value && ! value.match( /^\d+(px|em|rem|%|vh|vw|pt|pc|in|cm|mm|ex|ch)$/ )) {
								alert( 'Padding must include a valid CSS unit (px, em, rem, %, vh, vw, pt, pc, in, cm, mm, ex, ch)' );
								$( this ).focus();
								isValid = false;
								return false;
							}
						}
					);

					// Validate color inputs
					$( 'input.color-picker' ).each(
						function () {
							var value = $( this ).val();
							if (value && ! value.match( /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/ )) {
								alert( 'Color must be a valid hex color code (e.g., #ffffff or #fff)' );
								$( this ).focus();
								isValid = false;
								return false;
							}
						}
					);

					return isValid;
				}
			);
		}

		/**
		 * Initialize live preview functionality
		 */
		function initLivePreview() {
			if ($( '.template-preview' ).length === 0) {
				$( 'h1' ).after(
					'<div class="notice notice-info">' +
					'<p><strong>Live Preview:</strong> Changes will be visible on your my-account page after saving.</p>' +
					'</div>'
				);
			}
		}

		/**
		 * Initialize help tooltips
		 */
		function initHelpTooltips() {
			$( 'input[name*="font"]' ).each(
				function () {
					var $input = $( this );
					var name   = $input.attr( 'name' );

					if (name.includes( 'font_size' )) {
						$input.after( '<span class="help-tooltip" title="Enter size with CSS unit (e.g., 16px, 1.2em, 100%, 1.5rem)">?</span>' );
					} else if (name.includes( 'font_weight' )) {
						$input.after( '<span class="help-tooltip" title="Font weight: 300 (light), 400 (normal), 500 (medium), 600 (semi-bold), 700 (bold)">?</span>' );
					} else if (name.includes( 'font' ) && ! name.includes( 'color' )) {
						$input.after( '<span class="help-tooltip" title="Enter font family (e.g., Arial, sans-serif or Google Font name)">?</span>' );
					}
				}
			);

			$( 'input[name*="padding"]' ).each(
				function () {
					var $input = $( this );
					$input.after( '<span class="help-tooltip" title="Enter padding with CSS unit (e.g., 12px, 1rem, 0.5em)">?</span>' );
				}
			);

			$( 'input.color-picker' ).each(
				function () {
					var $input = $( this );
					$input.after( '<span class="help-tooltip" title="Click to open color picker or enter hex color code (e.g., #ffffff)">?</span>' );
				}
			);

			$( '.help-tooltip' ).css(
				{
					'display': 'inline-block',
					'width': '16px',
					'height': '16px',
					'background': '#666',
					'color': '#fff',
					'border-radius': '50%',
					'text-align': 'center',
					'font-size': '12px',
					'line-height': '16px',
					'margin-left': '5px',
					'cursor': 'help'
				}
			);
		}

		/**
		 * Initialize responsive settings tabs
		 */
		function initResponsiveTabs() {
			if ( ! $( '.responsive-tabs-wrapper' ).length) {
				return;
			}

			$( '.nav-tab' ).on(
				'click',
				function (e) {
					e.preventDefault();
					var targetTab = $( this ).data( 'tab' );

					$( '.nav-tab' ).removeClass( 'nav-tab-active' );
					$( this ).addClass( 'nav-tab-active' );

					$( '.tab-panel' ).removeClass( 'active' ).hide();
					$( '#' + targetTab + '-responsive' ).addClass( 'active' ).show();
				}
			);

			addTabStyles();
		}

		/**
		 * Add custom CSS styles for tabs
		 */
		function addTabStyles() {
			var styleId = 'blocksy-my-account-admin-tabs';
			if ( $( '#' + styleId ).length ) {
				return;
			}

			var css = '.responsive-tabs-wrapper { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0; }' +
				'.responsive-tabs-wrapper .nav-tab-wrapper { border-bottom: 1px solid #ccd0d4; margin: 0 0 20px 0; padding: 0; }' +
				'.responsive-tabs-wrapper .nav-tab { background: #f1f1f1; border: 1px solid #ccd0d4; border-bottom: none; color: #555; text-decoration: none; padding: 8px 12px; margin: 0 2px -1px 0; display: inline-block; cursor: pointer; transition: all 0.2s ease; }' +
				'.responsive-tabs-wrapper .nav-tab:hover { background: #e1e1e1; color: #333; }' +
				'.responsive-tabs-wrapper .nav-tab.nav-tab-active { background: #fff; border-bottom: 1px solid #fff; color: #333; font-weight: 600; }' +
				'.responsive-tabs-wrapper .tab-content-wrapper { min-height: 200px; }' +
				'.responsive-tabs-wrapper .tab-panel { display: none; }' +
				'.responsive-tabs-wrapper .tab-panel.active { display: block; }' +
				'.responsive-tabs-wrapper .form-table th { width: 200px; padding: 15px 10px 15px 0; }' +
				'.responsive-tabs-wrapper .form-table td { padding: 15px 10px; }' +
				'.responsive-tabs-wrapper .description { font-style: italic; color: #666; margin-top: 5px; }' +
				'.responsive-tabs-wrapper h3 { margin-top: 0; color: #23282d; font-size: 16px; }';

			$( 'head' ).append( '<style id="' + styleId + '">' + css + '</style>' );
		}

		// Initialize all admin functionality
		initColorPickers();
		initTemplatePreview();
		initFormValidation();
		initLivePreview();
		initHelpTooltips();
		initResponsiveTabs();
	}
);
