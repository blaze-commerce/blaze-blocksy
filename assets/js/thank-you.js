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
    console.log('🔧 Applying immediate visibility fixes');
    $('.blaze-commerce-thank-you-wrapper, .blaze-commerce-thank-you-container, .blaze-commerce-thank-you-header, .blaze-commerce-main-content, .blaze-commerce-order-details, .blaze-commerce-addresses-section, .blaze-commerce-account-creation, .blaze-commerce-order-summary').css({
        'opacity': '1 !important',
        'visibility': 'visible !important',
        'display': 'block'
    });

    // Ensure grid containers display correctly
    $('.blaze-commerce-thank-you-container, .blaze-commerce-addresses-grid, .blaze-commerce-account-form').each(function() {
        if ($(this).css('display') === 'none' || $(this).css('opacity') === '0') {
            $(this).css({
                'display': 'grid',
                'opacity': '1',
                'visibility': 'visible'
            });
        }
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
        // initPrintOrder();
        initResponsiveBehavior();
    }
    
    /**
     * Initialize order summary toggle functionality
     */
    function initOrderSummaryToggle() {
        $('.blaze-commerce-summary-toggle').on('click', function(e) {
            e.preventDefault();

            const $button = $(this);
            const $content = $('.blaze-commerce-summary-content');

            $content.slideToggle(300, function() {
                if ($content.is(':visible')) {
                    $button.text('Hide');
                } else {
                    $button.text('Show');
                }
            });

            // Track toggle event
            if (typeof gtag !== 'undefined') {
                gtag('event', 'toggle_order_summary', {
                    'event_category': 'user_interaction',
                    'action': $content.is(':visible') ? 'show' : 'hide'
                });
            }
        });
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

        // CRITICAL FIX: First ensure all elements are visible
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
                'visibility': 'visible',
                'display': 'block'
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
            orderNumberElement.css({
                'cursor': 'pointer',
                'position': 'relative',
                'padding': '4px 8px',
                'border-radius': '4px',
                'transition': 'all 0.2s ease',
                'background-color': '#f8f9fa'
            }).attr('title', 'Click to copy order number');

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
                $(this).css('background-color', '#e9ecef');
            }).on('mouseleave', function() {
                $(this).css('background-color', '#f8f9fa');
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
        const originalBg = $element.css('background-color');

        $element.attr('title', 'Copied!').css('background-color', '#d1fae5');

        setTimeout(() => {
            $element.attr('title', originalTitle).css('background-color', originalBg);
        }, 2000);
    }

    /**
     * Add print order functionality for Blaze Commerce design
     */
    function initPrintOrder() {
        // Add print button to the order summary
        if (!$('.blaze-commerce-print-order-btn').length) {
            const printButton = $('<button class="blaze-commerce-print-order-btn" style="margin-top: 16px; padding: 8px 16px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; font-size: 14px; cursor: pointer; width: 100%;">📄 Print Order Details</button>');

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
        function handleResize() {
            const windowWidth = $(window).width();

            // Handle order summary positioning on mobile/tablet
            if (windowWidth < 1024) {
                $('.blaze-commerce-addresses-grid').css('grid-template-columns', '1fr');
                $('.blaze-commerce-account-form').css('grid-template-columns', '1fr');
            } else {
                $('.blaze-commerce-order-summary').css('order', 'initial');
                $('.blaze-commerce-addresses-grid').css('grid-template-columns', '1fr 1fr');
                $('.blaze-commerce-account-form').css('grid-template-columns', '1fr 1fr');
            }

            // Handle product item layout on mobile
            if (windowWidth < 768) {
                $('.blaze-commerce-product-item').css({
                    'flex-direction': 'column',
                    'align-items': 'center',
                    'text-align': 'center'
                });
            } else {
                $('.blaze-commerce-product-item').css({
                    'flex-direction': 'row',
                    'align-items': 'flex-start',
                    'text-align': 'left'
                });
            }
        }

        // Initial call
        handleResize();

        // Handle window resize
        $(window).on('resize', debounce(handleResize, 250));
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
