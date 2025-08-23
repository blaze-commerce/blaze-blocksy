(function($) {
	'use strict';

	// Debounce function
	function debounce(fn, delay) {
		var timer = null;
		return function () {
			var context = this,
				args = arguments;
			clearTimeout(timer);
			timer = setTimeout(function () {
				fn.apply(context, args);
			}, delay);
		};
	}

	// Email validation
	var validateEmail = function (email) {
		const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(String(email).toLowerCase());
	};

	// Initialize Blaze Checkout
	var BlazeCheckout = {
		currentStep: 1,
		totalSteps: 3,
		isMultiStep: false,

		init: function() {
			// Check if multi-step is enabled
			this.isMultiStep = typeof blazeCheckoutSettings !== 'undefined' && blazeCheckoutSettings.multiStepEnabled;

			if (this.isMultiStep) {
				this.setupMultiStepCheckout();
			} else {
				this.setupTraditionalCheckout();
			}

			this.setupAccordions();
			this.setupTabs();
			this.setupCouponToggle();
			this.setupGuestCheckout();
			this.setupRegistration();
			this.setupFormValidation();
			this.setupResponsiveAccordions();
			this.bindEvents();
		},

		setupMultiStepCheckout: function() {
			this.setupStepNavigation();
			this.setupStepValidation();
			this.setupStepSummaries();
			this.initializeFirstStep();
		},

		setupTraditionalCheckout: function() {
			// Traditional single-page checkout setup
			this.setupShippingToggle();
			this.setupAccountCreation();
		},

		setupStepNavigation: function() {
			var self = this;

			// Continue buttons with progressive disclosure
			$(document).on('click', '.blaze-continue-step-1', function(e) {
				e.preventDefault();
				if (self.validateStep1()) {
					self.completeStep(1);
					self.goToStep(2);
				}
			});

			$(document).on('click', '.blaze-continue-step-2', function(e) {
				e.preventDefault();
				if (self.validateStep2()) {
					self.completeStep(2);
					self.goToStep(3);
				}
			});

			// Edit buttons - allow editing of previous steps
			$(document).on('click', '.blaze-edit-step', function(e) {
				e.preventDefault();
				var step = parseInt($(this).data('step'));
				self.editStep(step);
			});

			// Tab switching in step 1
			$(document).on('click', '.tab-button', function(e) {
				e.preventDefault();
				var tab = $(this).data('tab');
				self.switchTab(tab);
			});

			// Shipping address toggle
			$(document).on('change', '#ship-to-different-address-checkbox', function() {
				var $shippingFields = $('.blaze-shipping-fields');
				if ($(this).is(':checked')) {
					$shippingFields.slideDown();
				} else {
					$shippingFields.slideUp();
				}
			});

			// Account creation toggle
			$(document).on('change', '#createaccount', function() {
				var $passwordField = $('.blaze-create-account-password');
				if ($(this).is(':checked')) {
					$passwordField.slideDown();
				} else {
					$passwordField.slideUp();
				}
			});

			// Sign in form submission
			$(document).on('submit', '.login-form', function(e) {
				e.preventDefault();
				self.handleSignIn($(this));
			});
		},

		setupStepValidation: function() {
			// Real-time validation for each step
			$(document).on('blur', '#guest_email, #signin_email', function() {
				var email = $(this).val().trim();
				var $error = $(this).siblings('.blaze-field-error');

				if (email && !validateEmail(email)) {
					$error.text('Please enter a valid email address.').show();
					$(this).addClass('error');
				} else {
					$error.hide();
					$(this).removeClass('error');
				}
			});

			// Required field validation
			$(document).on('blur', 'input[required]', function() {
				var value = $(this).val().trim();
				var $error = $(this).siblings('.blaze-field-error');

				if (!value) {
					$error.text('This field is required.').show();
					$(this).addClass('error');
				} else {
					$error.hide();
					$(this).removeClass('error');
				}
			});
		},

		setupStepSummaries: function() {
			// Setup step summary generation
			this.updateStepSummary = function(step) {
				var $stepElement = $('.blaze-step-' + step);
				var $summary = $stepElement.find('.blaze-step-summary');

				switch(step) {
					case 1:
						var email = $('#guest_email').val() || $('#signin_email').val();
						if (email) {
							$summary.html('<p>' + email + '</p>').show();
						}
						break;
					case 2:
						var billingName = $('#billing_first_name').val() + ' ' + $('#billing_last_name').val();
						var billingPhone = $('#billing_phone').val();
						var billingAddress = $('#billing_address_1').val();
						var billingCity = $('#billing_city').val();
						var billingPostcode = $('#billing_postcode').val();

						var summaryHtml = '<div class="blaze-address-summary">';
						summaryHtml += '<h4>Billing Address</h4>';
						summaryHtml += '<p>' + billingName + '</p>';
						if (billingPhone) summaryHtml += '<p>' + billingPhone + '</p>';
						summaryHtml += '<p>' + billingAddress + '</p>';
						summaryHtml += '<p>' + billingCity + ', ' + billingPostcode + ' / GB</p>';

						if ($('#ship-to-different-address-checkbox').is(':checked')) {
							var shippingName = $('#shipping_first_name').val() + ' ' + $('#shipping_last_name').val();
							var shippingAddress = $('#shipping_address_1').val();
							var shippingCity = $('#shipping_city').val();
							var shippingPostcode = $('#shipping_postcode').val();

							summaryHtml += '<h4>Shipping Address</h4>';
							summaryHtml += '<p>' + shippingName + '</p>';
							summaryHtml += '<p>' + shippingAddress + '</p>';
							summaryHtml += '<p>' + shippingCity + ', ' + shippingPostcode + ' / GB</p>';
						} else {
							summaryHtml += '<h4>Shipping Address</h4>';
							summaryHtml += '<p>' + billingName + '</p>';
							summaryHtml += '<p>' + billingAddress + '</p>';
							summaryHtml += '<p>' + billingCity + ', ' + billingPostcode + ' / GB</p>';
						}
						summaryHtml += '</div>';

						$summary.html(summaryHtml).show();
						break;
				}
			};
		},

		initializeFirstStep: function() {
			// Show first step, hide others with progressive disclosure
			$('.blaze-step').removeClass('active current-step completed').addClass('inactive');
			$('.blaze-step').hide();

			// Show only step 1
			$('.blaze-step-1').removeClass('inactive').addClass('active current-step').show();
			$('.blaze-step-1 .blaze-step-content').show();
			$('.blaze-step-1 .blaze-step-summary').hide();
			$('.blaze-step-1 .blaze-step-edit').hide();
		},

		goToStep: function(stepNumber) {
			if (stepNumber < 1 || stepNumber > this.totalSteps) {
				return;
			}

			// Hide all steps first
			$('.blaze-step').removeClass('active current-step').addClass('inactive');
			$('.blaze-step .blaze-step-content').hide();

			// Show target step
			var $targetStep = $('.blaze-step-' + stepNumber);
			$targetStep.removeClass('inactive').addClass('active current-step').show();
			$targetStep.find('.blaze-step-content').show();
			$targetStep.find('.blaze-step-summary').hide();
			$targetStep.find('.blaze-step-edit').hide();

			// Update current step
			this.currentStep = stepNumber;

			// Scroll to step
			$('html, body').animate({
				scrollTop: $targetStep.offset().top - 100
			}, 500);

			// Trigger WooCommerce update if on payment step
			if (stepNumber === 3) {
				$('body').trigger('update_checkout');
			}
		},

		completeStep: function(stepNumber) {
			var $step = $('.blaze-step-' + stepNumber);

			// Mark step as completed
			$step.removeClass('current-step').addClass('completed');
			$step.find('.blaze-step-number').addClass('completed');

			// Update step summary
			this.updateStepSummary(stepNumber);

			// Show edit button and summary
			$step.find('.blaze-step-edit').show();
			$step.find('.blaze-step-content').hide();
			$step.find('.blaze-step-summary').show();
		},

		editStep: function(stepNumber) {
			// Allow editing of completed steps
			var $step = $('.blaze-step-' + stepNumber);
			$step.removeClass('completed').addClass('current-step');
			$step.find('.blaze-step-content').show();
			$step.find('.blaze-step-summary').hide();
			$step.find('.blaze-step-edit').hide();

			// Hide subsequent steps
			for (var i = stepNumber + 1; i <= this.totalSteps; i++) {
				$('.blaze-step-' + i).hide().removeClass('active current-step completed').addClass('inactive');
			}

			// Update current step
			this.currentStep = stepNumber;

			// Scroll to step
			$('html, body').animate({
				scrollTop: $step.offset().top - 100
			}, 500);
		},

		switchTab: function(tab) {
			$('.tab-button').removeClass('active');
			$('.tab-button[data-tab="' + tab + '"]').addClass('active');
			$('.tab-content').hide();
			$('#' + tab + '-tab').show();
		},

		validateStep1: function() {
			var isValid = true;
			var self = this;

			// Clear previous errors
			$('.blaze-step-1 .blaze-field-error').hide();
			$('.blaze-step-1 input').removeClass('error');

			// Check if user is logged in
			if ($('.logged-in-user').length > 0) {
				return true; // Already logged in
			}

			// Check if login tab is active
			if ($('.tab-button[data-tab="login"]').hasClass('active') && $('#login-tab').is(':visible')) {
				var email = $('#signin_email').val().trim();
				var password = $('#signin_password').val().trim();

				if (!email || !self.validateEmail(email)) {
					$('#signin_email').addClass('error').siblings('.blaze-field-error').text('Please enter a valid email address.').show();
					isValid = false;
				}

				if (!password) {
					$('#signin_password').addClass('error').siblings('.blaze-field-error').text('Please enter your password.').show();
					isValid = false;
				}

				if (isValid) {
					// Handle sign in - this will be processed separately
					return true;
				}
			} else {
				// Guest checkout validation
				var email = $('#guest_email').val().trim();

				if (!email || !self.validateEmail(email)) {
					$('#guest_email').addClass('error').siblings('.blaze-field-error').text('Please enter a valid email address.').show();
					isValid = false;
				} else {
					// Set billing email for guest checkout
					$('#billing_email').val(email);
				}
			}

			return isValid;
		},

		validateEmail: function(email) {
			var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return emailRegex.test(email);
		},

		validateStep2: function() {
			var isValid = true;
			var self = this;

			// Clear previous errors
			$('.blaze-step-2 input').removeClass('error');

			// Required billing fields
			var requiredFields = [
				'#billing_first_name',
				'#billing_last_name',
				'#billing_address_1',
				'#billing_city',
				'#billing_postcode',
				'#billing_phone'
			];

			// Validate required billing fields
			$.each(requiredFields, function(index, fieldId) {
				var $field = $(fieldId);
				var value = $field.val().trim();

				if (!value) {
					$field.addClass('error');
					self.showFieldError($field, 'This field is required.');
					isValid = false;
				} else {
					$field.removeClass('error');
					self.hideFieldError($field);
				}
			});

			// Validate email format
			var email = $('#billing_email').val().trim();
			if (email && !self.validateEmail(email)) {
				$('#billing_email').addClass('error');
				self.showFieldError($('#billing_email'), 'Please enter a valid email address.');
				isValid = false;
			}

			// Validate shipping fields if different address is selected
			if ($('#ship-to-different-address-checkbox').is(':checked')) {
				var shippingFields = [
					'#shipping_first_name',
					'#shipping_last_name',
					'#shipping_address_1',
					'#shipping_city',
					'#shipping_postcode'
				];

				$.each(shippingFields, function(index, fieldId) {
					var $field = $(fieldId);
					var value = $field.val().trim();

					if (!value) {
						$field.addClass('error');
						self.showFieldError($field, 'This field is required.');
						isValid = false;
					} else {
						$field.removeClass('error');
						self.hideFieldError($field);
					}
				});
			}

			return isValid;
		},

		showFieldError: function($field, message) {
			var $error = $field.siblings('.blaze-field-error');
			if ($error.length === 0) {
				$error = $('<span class="blaze-field-error"></span>');
				$field.after($error);
			}
			$error.text(message).show();
		},

		hideFieldError: function($field) {
			$field.siblings('.blaze-field-error').hide();
		},

		handleSignIn: function($form) {
			var self = this;
			var formData = $form.serialize();

			$.ajax({
				url: blazeCheckoutSettings.ajaxUrl,
				type: 'POST',
				data: formData + '&action=blaze_checkout_login&nonce=' + blazeCheckoutSettings.nonce,
				beforeSend: function() {
					$form.find('button[type="submit"]').prop('disabled', true).text('Signing in...');
				},
				success: function(response) {
					if (response.success) {
						// Reload page to update user state
						window.location.reload();
					} else {
						$form.find('.blaze-field-error').text(response.data.message).show();
					}
				},
				error: function() {
					$form.find('.blaze-field-error').text('An error occurred. Please try again.').show();
				},
				complete: function() {
					$form.find('button[type="submit"]').prop('disabled', false).text('Sign in');
				}
			});
		},

		setupShippingToggle: function() {
			$(document).on('change', '#ship-to-different-address-checkbox', function() {
				if ($(this).is(':checked')) {
					$('.blaze-shipping-fields, .shipping_address').slideDown(300);
				} else {
					$('.blaze-shipping-fields, .shipping_address').slideUp(300);
				}
			});
		},

		setupAccountCreation: function() {
			$(document).on('change', '#createaccount', function() {
				if ($(this).is(':checked')) {
					$('.blaze-create-account-password, .create-account').slideDown(300);
				} else {
					$('.blaze-create-account-password, .create-account').slideUp(300);
				}
			});
		},

		setupAccordions: function() {
			$('.blaze-accordion-title').on('click', function() {
				var $accordion = $(this).closest('.blaze-accordion-item');
				var $content = $accordion.find('.blaze-accordion-content');
				
				if ($accordion.hasClass('active')) {
					$accordion.removeClass('active');
					$content.slideUp(300);
				} else {
					$accordion.addClass('active');
					$content.slideDown(300);
				}
			});

			// Set initial state based on settings
			this.setInitialAccordionState();

			// Setup order summary accordion for mobile/tablet
			this.setupOrderSummaryAccordion();
		},

		setupOrderSummaryAccordion: function() {
			var self = this;

			// Handle accordion toggle click and keyboard events
			$('.blaze-order-summary-accordion-header').on('click keydown', function(e) {
				// Handle keyboard navigation (Enter and Space)
				if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) {
					return;
				}

				if (e.type === 'keydown') {
					e.preventDefault();
				}

				var $header = $(this);
				var $content = $('.blaze-order-summary-accordion-content');

				if ($header.hasClass('active')) {
					// Close accordion
					$header.removeClass('active')
						   .attr('aria-expanded', 'false');
					$content.removeClass('active')
							.attr('aria-hidden', 'true')
							.slideUp(300);
				} else {
					// Open accordion
					$header.addClass('active')
						   .attr('aria-expanded', 'true');
					$content.addClass('active')
							.attr('aria-hidden', 'false')
							.slideDown(300);
				}
			});

			// Set initial state based on screen size
			this.setOrderSummaryInitialState();

			// Handle window resize
			$(window).on('resize', function() {
				self.setOrderSummaryInitialState();
			});
		},

		setOrderSummaryInitialState: function() {
			var $header = $('.blaze-order-summary-accordion-header');
			var $content = $('.blaze-order-summary-accordion-content');

			if ($(window).width() <= 1023) {
				// Mobile/Tablet: Show accordion header, hide content by default
				$header.show();
				if (!$header.hasClass('active')) {
					$content.hide().removeClass('active').attr('aria-hidden', 'true');
					$header.attr('aria-expanded', 'false');
				}
			} else {
				// Desktop: Hide accordion header, show content
				$header.hide().removeClass('active').attr('aria-expanded', 'false');
				$content.show().addClass('active').attr('aria-hidden', 'false');
			}
		},

		setInitialAccordionState: function() {
			if (typeof blazeCheckoutSettings !== 'undefined' && blazeCheckoutSettings.accordionSettings) {
				var settings = blazeCheckoutSettings.accordionSettings;
				var $accordions = $('.blaze-accordion-item');
				
				$accordions.each(function() {
					var $accordion = $(this);
					var $content = $accordion.find('.blaze-accordion-content');
					
					// Desktop
					if (window.innerWidth >= 1025) {
						if (settings.desktop.enabled && settings.desktop.defaultOpen) {
							$accordion.addClass('active');
							$content.show();
						} else if (settings.desktop.enabled && !settings.desktop.defaultOpen) {
							$accordion.removeClass('active');
							$content.hide();
						}
					}
					// Tablet
					else if (window.innerWidth >= 769) {
						if (settings.tablet.enabled && settings.tablet.defaultOpen) {
							$accordion.addClass('active');
							$content.show();
						} else if (settings.tablet.enabled && !settings.tablet.defaultOpen) {
							$accordion.removeClass('active');
							$content.hide();
						}
					}
					// Mobile
					else {
						if (settings.mobile.enabled && settings.mobile.defaultOpen) {
							$accordion.addClass('active');
							$content.show();
						} else if (settings.mobile.enabled && !settings.mobile.defaultOpen) {
							$accordion.removeClass('active');
							$content.hide();
						}
					}
				});
			}
		},

		setupResponsiveAccordions: function() {
			var self = this;
			$(window).on('resize', debounce(function() {
				self.setInitialAccordionState();
			}, 250));
		},

		setupTabs: function() {
			$('.blaze-tab-button').on('click', function() {
				var $button = $(this);
				var tab = $button.data('tab');
				var $container = $button.closest('.blaze-login-register-tabs');
				
				// Update active button
				$container.find('.blaze-tab-button').removeClass('active');
				$button.addClass('active');
				
				// Show/hide content
				$container.find('.blaze-tab-content').hide();
				$container.find('#' + tab + '-tab').show();
			});
		},

		setupCouponToggle: function() {
			$(document).on('click', '.blaze-coupon-toggle', function(e) {
				e.preventDefault();
				var $form = $('.blaze-form-toggle');
				
				if ($form.hasClass('show')) {
					$form.removeClass('show');
					setTimeout(function() {
						$form.css('display', 'none');
					}, 300);
				} else {
					$form.css('display', 'block');
					setTimeout(function() {
						$form.addClass('show');
					}, 10);
				}
				$(this).toggleClass('toggled');
			});

			// Coupon application
			$(document).on('click', '.coupon-code-apply-button', function(e) {
				e.preventDefault();
				
				// Remove any existing error messages
				$('.blaze-coupon-error').remove();
				
				var couponCode = $('#coupon-code-input').val();
				$('input#coupon_code').val(couponCode);
				
				// Create an observer to watch for messages
				const observer = new MutationObserver(function(mutations) {
					mutations.forEach(function(mutation) {
						if (mutation.addedNodes.length) {
							// Check for success message
							const successNotice = document.querySelector('.is-success');
							// Check for error notices
							const errorNotices = document.querySelectorAll('.coupon-error-notice');
							
							// Remove any existing messages first
							$('.blaze-coupon-error').remove();
							
							if (successNotice) {
								// Handle success case
								$('.coupon-code-form.blaze-form-toggle').append(
									'<div class="blaze-coupon-error blaze-coco" style="margin-top: 10px;">' + 
									successNotice.textContent + 
									'</div>'
								);
								observer.disconnect();
							} else if (errorNotices.length > 0) {
								// Handle error case
								const lastErrorNotice = errorNotices[errorNotices.length - 1];
								$('.coupon-code-form.blaze-form-toggle').append(
									'<div class="blaze-coupon-error" style="color: #cc0000; margin-top: 10px;">' + 
									lastErrorNotice.textContent + 
									'</div>'
								);
								observer.disconnect();
							}
						}
					});
				});
				
				// Start observing the checkout form for changes
				if ($('form.checkout')[0]) {
					observer.observe($('form.checkout')[0], {
						childList: true,
						subtree: true
					});
				}
				
				// Trigger WooCommerce's native coupon button
				$('form.checkout_coupon.woocommerce-form-coupon button.button').trigger('click');
			});
		},

		setupGuestCheckout: function() {
			$(document).on('click', '.btn-checkout-as-guest', function(e) {
				e.preventDefault();
				var guestEmail = $('#guest_email').val();
				var isLoggedIn = $('body').hasClass('logged-in') || $('body').hasClass('woocommerce-logged-in');
				
				if (validateEmail(guestEmail)) {
					// Set the billing email
					$('#billing_email').val(guestEmail);
					
					// Show the recipients details section
					var $accordion = $('.blaze-recipients-details');
					if (!$accordion.hasClass('active')) {
						$accordion.find('.blaze-accordion-title').trigger('click');
					}
					
					// Scroll to the recipients details
					$('html, body').animate({
						scrollTop: $accordion.offset().top - 100
					}, 500);
					
					// Focus on first name field
					setTimeout(function() {
						$('#billing_first_name').focus();
					}, 600);
				} else {
					// Show error message
					$('#guest_email').addClass('error');
					if (!$('#guest_email').next('.error-message').length) {
						$('#guest_email').after('<div class="error-message" style="color: red; font-size: 14px; margin-top: 5px;">Please enter a valid email address.</div>');
					}
				}
			});
			
			// Remove error on input
			$(document).on('input', '#guest_email', function() {
				$(this).removeClass('error');
				$(this).next('.error-message').remove();
			});
		},

		setupRegistration: function() {
			// Toggle registration form
			$(document).on('change', '#save-info-checkbox', function() {
				var $container = $('.blaze-register-form-container');
				if ($(this).is(':checked')) {
					$container.slideDown(300);
				} else {
					$container.slideUp(300);
				}
			});
			
			// Check for subscription products
			this.checkSubscriptionProducts();
		},

		checkSubscriptionProducts: function() {
			// Check if there are subscription products in cart
			var hasSubscriptions = $('.blaze-order-item').filter(function() {
				return $(this).find('.subscription-details').length > 0;
			}).length > 0;
			
			if (hasSubscriptions && !$('body').hasClass('logged-in')) {
				$('.blaze-subscription-warning').show();
				$('#save-info-checkbox').prop('checked', true).trigger('change');
			}
		},

		setupFormValidation: function() {
			// Real-time validation for required fields
			$(document).on('blur', 'input[required]', function() {
				var $input = $(this);
				var value = $input.val().trim();
				
				if (!value) {
					$input.addClass('error');
					if (!$input.next('.error-message').length) {
						$input.after('<div class="error-message" style="color: red; font-size: 14px; margin-top: 5px;">This field is required.</div>');
					}
				} else {
					$input.removeClass('error');
					$input.next('.error-message').remove();
				}
			});
			
			// Email validation
			$(document).on('blur', 'input[type="email"]', function() {
				var $input = $(this);
				var email = $input.val().trim();
				
				if (email && !validateEmail(email)) {
					$input.addClass('error');
					if (!$input.next('.error-message').length) {
						$input.after('<div class="error-message" style="color: red; font-size: 14px; margin-top: 5px;">Please enter a valid email address.</div>');
					}
				} else if (email) {
					$input.removeClass('error');
					$input.next('.error-message').remove();
				}
			});
		},

		bindEvents: function() {
			// Update page title
			if ($("body.page-id-6 > div.wp-site-blocks > div.wp-block-group.has-global-padding.is-layout-constrained.wp-block-group-is-layout-constrained > h1").length) {
				$("body.page-id-6 > div.wp-site-blocks > div.wp-block-group.has-global-padding.is-layout-constrained.wp-block-group-is-layout-constrained > h1").text("Secure Checkout");
			}
			
			// Handle checkout form updates
			$(document.body).on('updated_checkout', function() {
				// Reinitialize components after checkout update
				BlazeCheckout.checkSubscriptionProducts();
			});
			
			// Handle shipping method changes
			$(document).on('change', 'input[name^="shipping_method"]', function() {
				$('body').trigger('update_checkout');
			});
			
			// Handle payment method changes
			$(document).on('change', 'input[name="payment_method"]', function() {
				$('body').trigger('update_checkout');
			});
		}
	};

	// Initialize when document is ready
	$(document).ready(function() {
		BlazeCheckout.init();
	});

	// Re-initialize on AJAX complete (for WooCommerce updates)
	$(document).ajaxComplete(function(event, xhr, settings) {
		if (settings.url && settings.url.indexOf('wc-ajax=update_order_review') !== -1) {
			setTimeout(function() {
				BlazeCheckout.setInitialAccordionState();
			}, 100);
		}
	});

})(jQuery);
