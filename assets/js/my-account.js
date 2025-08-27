/**
 * Blocksy Child My Account JavaScript
 * 
 * Interactive functionality for the custom my-account pages.
 * Integrated from Blaze My Account plugin into Blocksy child theme.
 * 
 * @package Blocksy_Child
 * @since 1.5.0
 */

jQuery(document).ready(function($) {
    
    console.log('🎯 Blocksy Child My Account functionality loaded');

    /**
     * Initialize login/register form functionality
     */
    function initLoginRegisterForms() {
        // Template 2 login/register form switching
        initTemplate2FormSwitching();

        // Enhanced form validation
        initFormValidation();
    }

    /**
     * Initialize Template 2 form switching functionality
     */
    function initTemplate2FormSwitching() {
        // Initially hide the register form for template2
        $('.blaze-login-register.template2 .register-container').hide();
        
        // Show register form
        $('.show-register-form').on('click', function(e) {
            e.preventDefault();
            $('.blaze-login-register.template2 .login-container').fadeOut(300, function() {
                $('.blaze-login-register.template2 .register-container').fadeIn(300);
            });
        });
        
        // Show login form
        $('.show-login-form').on('click', function(e) {
            e.preventDefault();
            $('.blaze-login-register.template2 .register-container').fadeOut(300, function() {
                $('.blaze-login-register.template2 .login-container').fadeIn(300);
            });
        });
    }

    /**
     * Enhanced form validation
     */
    function initFormValidation() {
        // Real-time email validation
        $('input[type="email"]').on('blur', function() {
            var email = $(this).val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                $(this).addClass('error');
                showValidationMessage($(this), 'Please enter a valid email address.');
            } else {
                $(this).removeClass('error');
                hideValidationMessage($(this));
            }
        });

        // Password strength indicator (basic)
        $('input[type="password"]').on('input', function() {
            var password = $(this).val();
            var strength = getPasswordStrength(password);
            
            // Remove existing strength indicators
            $(this).siblings('.password-strength').remove();
            
            if (password.length > 0) {
                var strengthHtml = '<div class="password-strength strength-' + strength.level + '">' + strength.text + '</div>';
                $(this).after(strengthHtml);
            }
        });

        // Form submission validation
        $('.blaze-login-register form').on('submit', function(e) {
            var isValid = true;
            var $form = $(this);
            
            // Clear previous errors
            $form.find('.error').removeClass('error');
            $form.find('.validation-message').remove();
            
            // Check required fields
            $form.find('input[required]').each(function() {
                if (!$(this).val().trim()) {
                    $(this).addClass('error');
                    showValidationMessage($(this), 'This field is required.');
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                var firstError = $form.find('.error').first();
                if (firstError.length) {
                    $('html, body').animate({
                        scrollTop: firstError.offset().top - 100
                    }, 300);
                }
            }
        });
    }

    /**
     * Show validation message
     */
    function showValidationMessage($field, message) {
        hideValidationMessage($field);
        $field.after('<div class="validation-message error">' + message + '</div>');
    }

    /**
     * Hide validation message
     */
    function hideValidationMessage($field) {
        $field.siblings('.validation-message').remove();
    }

    /**
     * Get password strength
     */
    function getPasswordStrength(password) {
        var score = 0;
        
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        switch (score) {
            case 0:
            case 1:
                return { level: 'weak', text: 'Weak' };
            case 2:
            case 3:
                return { level: 'medium', text: 'Medium' };
            case 4:
            case 5:
                return { level: 'strong', text: 'Strong' };
            default:
                return { level: 'weak', text: 'Weak' };
        }
    }

    // Initialize all functionality
    initLoginRegisterForms();
    
    console.log('✅ Blocksy Child My Account functionality initialized successfully');
});
