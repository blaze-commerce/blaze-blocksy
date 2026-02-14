/**
 * Thank You Page Blaze Commerce Design JavaScript
 *
 * Interactive functionality for the redesigned WooCommerce thank you page.
 * Includes order summary toggle, account creation, and enhanced UX.
 *
 * @package Blocksy_Child
 * @since 2.0.3
 */

jQuery(document).ready(function($) {

    console.log('🎉 Blaze Commerce Thank You page loaded');

    // CRITICAL FIX: Immediate visibility enforcement
    // Only set opacity/visibility — do NOT override display property (breaks flex/grid layouts)
    console.log('🔧 Applying immediate visibility fixes');
    $('.blaze-commerce-thank-you-wrapper, .blaze-commerce-thank-you-container, .blaze-commerce-thank-you-header, .blaze-commerce-main-content, .blaze-commerce-order-details, .blaze-commerce-addresses-section, .blaze-commerce-account-creation, .blaze-commerce-order-summary').css({
        'opacity': '1',
        'visibility': 'visible'
    });

    // Register global function for compatibility
    window.blocksy_child_blaze_commerce_order_summary = function() {
        console.log('✅ blocksy_child_blaze_commerce_order_summary function available globally');
        return true;
    };

    console.log('✅ Immediate visibility fixes applied');

    /**
     * Initialize thank you page enhancements
     */
    function initThankYouPage() {
        initOrderSummaryToggle();
        initAccountCreation();
        initAnimations();
        initCopyOrderNumber();
        initResendEmail();
        // initPrintOrder();
        initResponsiveBehavior();
    }

    /**
     * Initialize order summary toggle functionality
     *
     * On mobile/tablet (<=1023px): Creates a collapsible toggle header matching
     * the checkout page pattern. Summary content is hidden by default.
     * On desktop (>1023px): No toggle — summary is always visible in sidebar.
     */
    function initOrderSummaryToggle() {
        // Only create toggle on tablet/mobile
        if (window.innerWidth > 1023) {
            return;
        }

        var $orderSummary = $('.blaze-commerce-order-summary');
        var $summaryContent = $orderSummary.find('.blaze-commerce-summary-content');

        if (!$orderSummary.length || !$summaryContent.length) {
            return;
        }

        // Prevent duplicate toggle creation
        if ($('.blaze-commerce-toggle-header').length) {
            return;
        }

        // Get the total price from the summary
        var totalPrice = $orderSummary.find('.blaze-commerce-total-value').text().trim() || '';

        // Create toggle header HTML (matching checkout toggle pattern)
        var toggleHTML = '<div class="blaze-commerce-toggle-header" role="button" tabindex="0" aria-expanded="false">' +
            '<div class="blaze-commerce-toggle-header-left">' +
                '<div class="blaze-commerce-toggle-header-icon">' +
                    '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>' +
                    '</svg>' +
                '</div>' +
                '<span class="blaze-commerce-toggle-header-text">Show order summary</span>' +
                '<svg class="blaze-commerce-toggle-header-chevron" fill="currentColor" viewBox="0 0 20 20" width="24" height="24">' +
                    '<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>' +
                '</svg>' +
            '</div>' +
            '<div class="blaze-commerce-toggle-header-total">' + totalPrice + '</div>' +
        '</div>';

        // Insert toggle header inside the order summary, before the summary header
        // This keeps the toggle within the flex-ordered .blaze-commerce-order-summary container
        $orderSummary.prepend(toggleHTML);

        // Move the summary header INTO the summary content so it appears
        // inside the bordered/padded expanded area (Figma shows heading inside content)
        var $summaryHeader = $orderSummary.find('.blaze-commerce-summary-header');
        if ($summaryHeader.length && $summaryContent.length) {
            $summaryContent.prepend($summaryHeader);
        }

        // Bind click handler using native addEventListener for reliable event handling
        var toggleEl = document.querySelector('.blaze-commerce-toggle-header');
        if (!toggleEl) return;

        var chevronEl = toggleEl.querySelector('.blaze-commerce-toggle-header-chevron');
        var toggleTextEl = toggleEl.querySelector('.blaze-commerce-toggle-header-text');
        var summaryContentEl = $summaryContent[0];

        function handleToggleClick() {
            var isExpanding = !summaryContentEl.classList.contains('show');

            summaryContentEl.classList.toggle('show');
            toggleEl.classList.toggle('expanded');
            $orderSummary[0].classList.toggle('expanded');
            if (chevronEl) chevronEl.classList.toggle('rotated');

            if (toggleTextEl) {
                toggleTextEl.textContent = isExpanding ? 'Hide order summary' : 'Show order summary';
            }
            toggleEl.setAttribute('aria-expanded', isExpanding);

            // Track toggle event
            if (typeof gtag !== 'undefined') {
                gtag('event', 'toggle_order_summary', {
                    'event_category': 'user_interaction',
                    'action': isExpanding ? 'show' : 'hide'
                });
            }
        }

        toggleEl.addEventListener('click', handleToggleClick);

        // Keyboard accessibility: Enter/Space to toggle
        toggleEl.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                handleToggleClick();
            }
        });
    }

    /**
     * Clean up toggle on resize to desktop
     */
    var resizeTimeout;
    $(window).on('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (window.innerWidth > 1023) {
                // Remove toggle header on desktop
                $('.blaze-commerce-toggle-header').remove();
                // Ensure summary content is visible
                var $summaryContent = $('.blaze-commerce-summary-content');
                $summaryContent.removeClass('show');
                // Remove expanded classes
                var $orderSummary = $('.blaze-commerce-order-summary');
                $orderSummary.removeClass('expanded');
                // Move summary header back to its original position (before summary-content)
                var $summaryHeader = $summaryContent.find('.blaze-commerce-summary-header');
                if ($summaryHeader.length) {
                    $summaryContent.before($summaryHeader);
                }
                $summaryHeader.show();
            } else {
                // Re-create toggle if needed
                if (!$('.blaze-commerce-toggle-header').length) {
                    initOrderSummaryToggle();
                }
            }
        }, 250);
    });

    /**
     * Initialize resend email functionality
     */
    function initResendEmail() {
        // Check if AJAX configuration is available
        if (typeof blazeCommerceAjax === 'undefined') {
            console.error('Blaze Commerce: AJAX configuration not loaded');
            return;
        }

        let isProcessing = false; // Prevent rapid clicks

        $('#resend-email-btn').on('click', function(e) {
            e.preventDefault();

            if (isProcessing) {
                return; // Prevent multiple simultaneous requests
            }

            const $button = $(this);
            const $feedback = $('#resend-feedback');
            const originalText = $button.text();

            // Get order data from button attributes
            const orderId = $button.data('order-id');
            const orderKey = $button.data('order-key');

            if (!orderId || !orderKey) {
                showResendEmailFeedback('error', 'Invalid order data. Please refresh the page and try again.');
                return;
            }

            // Set processing state
            isProcessing = true;
            $button.prop('disabled', true).text('Sending...');
            $feedback.hide();

            // Make AJAX request with error handling
            $.ajax({
                url: blazeCommerceAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'blaze_commerce_resend_email',
                    order_id: orderId,
                    order_key: orderKey,
                    nonce: blazeCommerceAjax.nonce
                },
                success: function(response) {
                    isProcessing = false; // Reset processing state
                    if (response.success) {
                        showResendEmailFeedback('success', response.data.message);
                        // Keep button disabled for 60 seconds to prevent spam
                        let countdown = 60;
                        const timer = setInterval(function() {
                            countdown--;
                            if (countdown > 0) {
                                $button.text('Please wait (' + countdown + 's)');
                            } else {
                                $button.prop('disabled', false).text(originalText);
                                clearInterval(timer);
                            }
                        }, 1000);
                    } else {
                        showResendEmailFeedback('error', response.data.message);
                        $button.prop('disabled', false).text(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    isProcessing = false; // Reset processing state
                    console.error('Blaze Commerce: AJAX error:', status, error);
                    const errorMessage = blazeCommerceAjax && blazeCommerceAjax.messages
                        ? blazeCommerceAjax.messages.error
                        : 'Failed to send email. Please try again or contact support.';
                    showResendEmailFeedback('error', errorMessage);
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });
    }

    /**
     * Show feedback message for resend email action
     */
    function showResendEmailFeedback(type, message) {
        const $feedback = $('#resend-feedback');
        $feedback.removeClass('success error').addClass(type).text(message).show();

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                $feedback.fadeOut();
            }, 5000);
        }
    }

    /**
     * Initialize account creation form functionality
     */
    function initAccountCreation() {
        $('.blaze-commerce-account-form').on('submit', function(e) {
            const $form = $(this);
            const $button = $form.find('.blaze-commerce-create-account-btn');
            const originalText = $button.text();

            // Show loading state
            $button.text('Creating Account...').prop('disabled', true);

            // Basic form validation
            const firstName = $form.find('#account_first_name').val().trim();
            const lastName = $form.find('#account_last_name').val().trim();
            const password = $form.find('#account_password').val();

            if (!firstName || !lastName || !password) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                $button.text(originalText).prop('disabled', false);
                return;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                $button.text(originalText).prop('disabled', false);
                return;
            }

            // Track account creation attempt
            if (typeof gtag !== 'undefined') {
                gtag('event', 'create_account_attempt', {
                    'event_category': 'account_creation'
                });
            }
        });
    }

    /**
     * Initialize entrance animations for Blaze Commerce design
     * CRITICAL FIX: Ensure elements remain visible if animations fail
     */
    function initAnimations() {
        console.log('🎬 Initializing animations with visibility safeguards');

        // CRITICAL FIX: Ensure all elements are visible
        // Only set opacity/visibility — do NOT override display (breaks flex/grid from CSS)
        const allBlazeCommerceElements = [
            '.blaze-commerce-thank-you-wrapper',
            '.blaze-commerce-thank-you-container',
            '.blaze-commerce-thank-you-header',
            '.blaze-commerce-main-content',
            '.blaze-commerce-order-details',
            '.blaze-commerce-addresses-section',
            '.blaze-commerce-account-creation',
            '.blaze-commerce-order-summary',
            '.blaze-commerce-product-item'
        ];

        allBlazeCommerceElements.forEach(selector => {
            $(selector).css({
                'opacity': '1',
                'visibility': 'visible'
            });
        });

        // Only attempt subtle animations if user prefers motion
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            // Gentle fade-in animation that doesn't hide elements
            const sections = [
                '.blaze-commerce-thank-you-header',
                '.blaze-commerce-order-details',
                '.blaze-commerce-addresses-section',
                '.blaze-commerce-account-creation',
                '.blaze-commerce-order-summary'
            ];

            sections.forEach((selector, index) => {
                const $section = $(selector);
                if ($section.length) {
                    // Start from visible state, add subtle enhancement
                    $section.css({
                        'opacity': '0.8',
                        'transform': 'translateY(10px)'
                    });

                    setTimeout(() => {
                        $section.animate({
                            'opacity': '1'
                        }, 400).css('transform', 'translateY(0)');
                    }, index * 100);
                }
            });
        }

        console.log('✅ Animation initialization complete with visibility safeguards');
    }

    /**
     * Add copy order number functionality for Blaze Commerce design
     */
    function initCopyOrderNumber() {
        // Find order number in the new Blaze Commerce design
        const orderNumberElement = $('.blaze-commerce-order-confirmation strong');

        if (orderNumberElement.length) {
            const orderNumber = orderNumberElement.text().replace('#', '');

            // Make order number clickable
            orderNumberElement.addClass('blaze-commerce-copy-target')
                .attr('title', 'Click to copy order number');

            orderNumberElement.on('click', function() {
                copyToClipboard(orderNumber);
                showCopyFeedback($(this));

                // Track copy event
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'copy_order_number', {
                        'event_category': 'user_interaction',
                        'order_number': orderNumber
                    });
                }
            });

            orderNumberElement.on('mouseenter', function() {
                $(this).addClass('blaze-commerce-copy-target--hover');
            }).on('mouseleave', function() {
                $(this).removeClass('blaze-commerce-copy-target--hover');
            });
        }
    }

    /**
     * Copy text to clipboard
     */
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            document.execCommand('copy');
            textArea.remove();
        }
    }

    /**
     * Show copy feedback
     */
    function showCopyFeedback($element) {
        const originalTitle = $element.attr('title');

        $element.attr('title', 'Copied!').addClass('blaze-commerce-copy-target--copied');

        setTimeout(() => {
            $element.attr('title', originalTitle).removeClass('blaze-commerce-copy-target--copied');
        }, 2000);
    }

    /**
     * Add print order functionality for Blaze Commerce design
     */
    function initPrintOrder() {
        // Add print button to the order summary
        if (!$('.blaze-commerce-print-order-btn').length) {
            const printButton = $('<button class="blaze-commerce-print-order-btn">Print Order Details</button>');

            $('.blaze-commerce-order-summary').append(printButton);
        }

        $(document).on('click', '.blaze-commerce-print-order-btn', function(e) {
            e.preventDefault();
            window.print();

            // Track print event
            if (typeof gtag !== 'undefined') {
                gtag('event', 'print_order', {
                    'event_category': 'order_actions'
                });
            }
        });
    }

    /**
     * Initialize responsive behavior for Blaze Commerce design
     */
    function initResponsiveBehavior() {
        // All responsive layout is handled by CSS media queries.
        // No inline style overrides needed — they conflict with CSS flex/grid rules.
    }

    /**
     * Debounce function to limit resize event calls
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Add smooth scrolling for anchor links
     */
    function initSmoothScrolling() {
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();

            const target = $($(this).attr('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });
    }

    // Initialize all functionality
    initThankYouPage();
    initSmoothScrolling();

    console.log('✅ Blaze Commerce Thank You page initialized successfully');
});
