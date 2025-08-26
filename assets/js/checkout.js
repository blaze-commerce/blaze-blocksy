/**
 * Checkout Customization Script
 *
 * This script customizes the checkout page text and elements for better UX.
 * Specifically changes "My contact" to "ACCOUNT" in the checkout flow.
 */

jQuery(document).ready(function($) {

    // Global variables for performance optimization
    let stepIconUpdateTimeout;
    let checkoutObserver;
    let checkInterval = 500;
    let checkCount = 0;
    const maxChecks = 10;

    // Selector caching for performance
    const SELECTORS = {
        contactTitle: 'h3.fc-step__substep-title.fc-step__substep-title--contact',
        changeButtons: 'a.fc-step__substep-edit',
        editCartLink: 'a.fc-checkout-order-review__header-link.fc-checkout-order-review__edit-cart',
        shippingTitle: 'h3.fc-step__substep-title.fc-step__substep-title--shipping_address',
        billingTitle: 'h3.fc-step__substep-title.fc-step__substep-title--billing_address',
        shippingHeaders: 'tr.woocommerce-shipping-totals.shipping th, th:contains("Shipping")',
        paymentTitle: 'h3.fc-step__substep-title.fc-step__substep-title--payment',
        privacyPolicyText: '.woocommerce-privacy-policy-text',
        checkoutContainer: '.woocommerce-checkout, .fc-checkout',
        orderSummaryContainer: '.woocommerce-checkout-review-order',
        orderSummaryTable: '.shop_table.woocommerce-checkout-review-order-table',
        checkoutSubstep: '.fc-step__substep',
        productQuantity: '.product-quantity',
        priceContainer: '.cart-item__price',
        couponInputs: 'input[name="coupon_code"], #accordion_coupon_code, .checkout-substep-accordion-inner input[name="coupon_code"], .accordion-original-coupon input[name="coupon_code"]',
        couponApplyButtons: '.fc-coupon-code__apply, .checkout-substep-accordion-inner .fc-coupon-code__apply, .accordion-original-coupon .fc-coupon-code__apply'
    };

    const elementCache = new Map();

    /**
     * Get cached element or refresh cache if needed
     */
    function getCachedElement(selector, forceRefresh = false) {
        if (!elementCache.has(selector) || forceRefresh) {
            elementCache.set(selector, $(selector));
        }
        return elementCache.get(selector);
    }

    /**
     * Clear element cache to force refresh on next access
     */
    function clearElementCache() {
        elementCache.clear();
    }
    
    /**
     * Replace "My contact" text with "ACCOUNT" in checkout flow
     */
    function replaceContactText() {
        try {
            // Use cached selectors for performance
            const contactTitle = getCachedElement(SELECTORS.contactTitle, true);

            if (contactTitle.length > 0) {
                contactTitle.text('ACCOUNT');
                console.log('✓ Checkout customization: "My contact" replaced with "ACCOUNT"');
            }

            // Also update the aria-label for accessibility
            const changeButton = $('a[aria-label*="My contact"]');
            if (changeButton.length > 0) {
                changeButton.attr('aria-label', 'Change: ACCOUNT');
                console.log('✓ Checkout customization: Change button aria-label updated');
            }
        } catch (error) {
            console.error('Error in replaceContactText:', error);
        }
    }

    /**
     * Replace "Change" button text with "Edit" in checkout flow
     */
    function replaceChangeButtonText() {
        try {
            // Use cached selectors for performance
            const changeButtons = getCachedElement(SELECTORS.changeButtons, true);
            let replacedCount = 0;

            changeButtons.each(function() {
                const $button = $(this);
                if ($button.text().trim() === 'Change') {
                    $button.text('Edit');
                    replacedCount++;
                }
            });

            if (replacedCount > 0) {
                console.log(`✓ Checkout customization: ${replacedCount} "Change" buttons replaced with "Edit"`);
            }
        } catch (error) {
            console.error('Error in replaceChangeButtonText:', error);
        }
    }

    /**
     * Replace "Edit cart" link text with "Edit"
     */
    function replaceEditCartText() {
        try {
            // Use cached selectors for performance
            const editCartLink = getCachedElement(SELECTORS.editCartLink, true);

            if (editCartLink.length > 0 && editCartLink.text().trim() === 'Edit cart') {
                editCartLink.text('Edit');
                console.log('✓ Checkout customization: "Edit cart" replaced with "Edit"');
            }
        } catch (error) {
            console.error('Error in replaceEditCartText:', error);
        }
    }

    /**
     * Replace "Shipping to" heading text with "SHIPPING ADDRESS"
     */
    function replaceShippingToText() {
        try {
            // Use cached selectors for performance
            const shippingTitle = getCachedElement(SELECTORS.shippingTitle, true);

            if (shippingTitle.length > 0 && shippingTitle.text().trim() === 'Shipping to') {
                shippingTitle.text('SHIPPING ADDRESS');
                console.log('✓ Checkout customization: "Shipping to" replaced with "SHIPPING ADDRESS"');
            }
        } catch (error) {
            console.error('Error in replaceShippingToText:', error);
        }
    }

    /**
     * Replace "Billing to" heading text with "BILLING ADDRESS"
     */
    function replaceBillingToText() {
        try {
            // Use cached selectors for performance
            const billingTitle = getCachedElement(SELECTORS.billingTitle, true);

            if (billingTitle.length > 0 && billingTitle.text().trim() === 'Billing to') {
                billingTitle.text('BILLING ADDRESS');
                console.log('✓ Checkout customization: "Billing to" replaced with "BILLING ADDRESS"');
            }
        } catch (error) {
            console.error('Error in replaceBillingToText:', error);
        }
    }

    /**
     * Replace "Shipping" table header text with "Delivery"
     */
    function replaceShippingTableText() {
        try {
            // Use cached selectors for performance
            const shippingHeaders = getCachedElement(SELECTORS.shippingHeaders, true);
            let replacedCount = 0;

            shippingHeaders.each(function() {
                const $header = $(this);
                if ($header.text().trim() === 'Shipping') {
                    $header.text('Delivery');
                    replacedCount++;
                }
            });

            if (replacedCount > 0) {
                console.log(`✓ Checkout customization: ${replacedCount} "Shipping" table headers replaced with "Delivery"`);
            }
        } catch (error) {
            console.error('Error in replaceShippingTableText:', error);
        }
    }

    /**
     * Replace "Payment method" heading text with "Payment options"
     */
    function replacePaymentMethodText() {
        try {
            // Use cached selectors for performance
            const paymentTitle = getCachedElement(SELECTORS.paymentTitle, true);

            if (paymentTitle.length > 0 && paymentTitle.text().trim() === 'Payment method') {
                paymentTitle.text('Payment options');
                console.log('✓ Checkout customization: "Payment method" replaced with "Payment options"');
            }
        } catch (error) {
            console.error('Error in replacePaymentMethodText:', error);
        }
    }

    /**
     * Replace privacy policy text with custom purchase agreement text
     */
    function replacePrivacyPolicyText() {
        try {
            // Use cached selectors for performance
            const privacyPolicyElement = getCachedElement(SELECTORS.privacyPolicyText, true);

            if (privacyPolicyElement.length > 0) {
                // Find the paragraph element within the privacy policy container
                const privacyParagraph = privacyPolicyElement.find('p');

                if (privacyParagraph.length > 0) {
                    // Preserve the existing privacy policy link if it exists
                    const existingLink = privacyParagraph.find('a.woocommerce-privacy-policy-link');
                    let linkHtml = '';

                    if (existingLink.length > 0) {
                        // Extract the link HTML to preserve it
                        linkHtml = existingLink.prop('outerHTML');
                    }

                    // Replace with custom purchase agreement text, preserving the privacy policy link
                    const customText = linkHtml
                        ? `By clicking this button, you agree to finalize your purchase. Note that your data can't be edited later and will be used as described in our ${linkHtml}.`
                        : 'By clicking this button, you agree to finalize your purchase. Note that your data can\'t be edited later and will be used as described in our privacy policy.';

                    privacyParagraph.html(customText);
                    console.log('✓ Checkout customization: Privacy policy text replaced with custom purchase agreement');
                }
            }
        } catch (error) {
            console.error('Error in replacePrivacyPolicyText:', error);
        }
    }

    /**
     * Replace coupon code input placeholder text with "Enter Coupon Code"
     */
    function replaceCouponInputPlaceholder() {
        try {
            // Small delay to handle accordion animations
            setTimeout(() => {
                // Use cached selectors for performance
                const couponInputs = getCachedElement(SELECTORS.couponInputs, true);
                let replacedCount = 0;

                couponInputs.each(function() {
                    const $input = $(this);
                    const currentPlaceholder = $input.attr('placeholder');

                    // Replace placeholder if it's not already our target text
                    if (currentPlaceholder && currentPlaceholder !== 'Enter Coupon Code') {
                        $input.attr('placeholder', 'Enter Coupon Code');

                        // Verify replacement was successful before incrementing counter
                        if ($input.attr('placeholder') === 'Enter Coupon Code') {
                            replacedCount++;
                        }
                    }
                });

                if (replacedCount > 0) {
                    console.log(`✓ Checkout customization: ${replacedCount} coupon input placeholders replaced with "Enter Coupon Code"`);
                }
            }, 100);
        } catch (error) {
            console.error('Error in replaceCouponInputPlaceholder:', error);
        }
    }

    /**
     * Replace coupon apply button text with "APPLY COUPON"
     */
    function replaceCouponApplyButtonText() {
        try {
            // Small delay to handle accordion animations
            setTimeout(() => {
                // Use cached selectors for performance
                const couponApplyButtons = getCachedElement(SELECTORS.couponApplyButtons, true);
                let replacedCount = 0;

                couponApplyButtons.each(function() {
                    const $button = $(this);
                    const currentText = $button.text().trim();

                    // Replace button text if it's "Apply" (simplified logic)
                    if (currentText === 'Apply') {
                        $button.text('APPLY COUPON');
                        replacedCount++;
                    }
                });

                if (replacedCount > 0) {
                    console.log(`✓ Checkout customization: ${replacedCount} coupon apply buttons replaced with "APPLY COUPON"`);
                }
            }, 100);
        } catch (error) {
            console.error('Error in replaceCouponApplyButtonText:', error);
        }
    }

    /**
     * Move checkout substep element to order summary table as accordion-style row
     */
    function moveCheckoutSubstepToOrderSummary() {
        try {
            // Use cached selectors for performance
            const orderSummaryTable = getCachedElement(SELECTORS.orderSummaryTable, true);
            const checkoutSubsteps = getCachedElement(SELECTORS.checkoutSubstep, true);

            if (orderSummaryTable.length > 0 && checkoutSubsteps.length > 0) {
                // Find a suitable substep to move (e.g., coupon or additional options)
                let substepToMove = null;

                checkoutSubsteps.each(function() {
                    const $substep = $(this);
                    // Look for substeps that contain coupon, additional options, or similar content
                    if ($substep.find('.fc-step__substep-title').text().toLowerCase().includes('coupon') ||
                        $substep.find('.woocommerce-form-coupon-toggle').length > 0 ||
                        $substep.find('[class*="coupon"]').length > 0) {
                        substepToMove = $substep;
                        return false; // Break the loop
                    }
                });

                if (substepToMove && substepToMove.length > 0) {
                    // Check if already moved to avoid duplicates
                    if (orderSummaryTable.find('.checkout-substep-accordion-row').length > 0) {
                        return;
                    }

                    // Get the content of the substep
                    const substepContent = substepToMove.html();
                    // Get only the title text, excluding the edit button text
                    const titleElement = substepToMove.find('.fc-step__substep-title');
                    const titleTextElement = titleElement.find('.fc-step__substep-title-text');
                    const substepTitle = titleTextElement.length > 0 ?
                        titleTextElement.text().trim() :
                        titleElement.clone().children().remove().end().text().trim() || 'Additional Options';

                    // Create accordion-style table row
                    const accordionRow = $(`
                        <tr class="checkout-substep-accordion-row">
                            <td colspan="2" class="checkout-substep-accordion-cell">
                                <div class="checkout-substep-accordion">
                                    <div class="checkout-substep-accordion-header" role="button" tabindex="0" aria-expanded="false" aria-controls="checkout-substep-content">
                                        <span class="checkout-substep-accordion-title">${substepTitle}</span>
                                        <span class="checkout-substep-accordion-icon">▼</span>
                                    </div>
                                    <div class="checkout-substep-accordion-content" id="checkout-substep-content" style="display: none;">
                                        <div class="checkout-substep-accordion-inner">
                                            ${substepContent}
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `);

                    // Add accordion functionality
                    accordionRow.find('.checkout-substep-accordion-header').on('click keypress', function(e) {
                        if (e.type === 'click' || (e.type === 'keypress' && (e.which === 13 || e.which === 32))) {
                            e.preventDefault();
                            const $header = $(this);
                            const $content = $header.next('.checkout-substep-accordion-content');
                            const $icon = $header.find('.checkout-substep-accordion-icon');
                            const isExpanded = $header.attr('aria-expanded') === 'true';

                            if (isExpanded) {
                                // Collapse
                                $content.slideUp(300);
                                $header.attr('aria-expanded', 'false');
                                $icon.text('▼');
                                $header.removeClass('expanded');
                            } else {
                                // Expand
                                $content.slideDown(300, function() {
                                    // After expansion animation completes, show coupon input directly
                                    showCouponInputDirectly($content);
                                });
                                $header.attr('aria-expanded', 'true');
                                $icon.text('▲');
                                $header.addClass('expanded');
                            }
                        }
                    });

                    // Function to show coupon input field directly using original WooCommerce field
                    function showCouponInputDirectly($content) {
                        try {
                            // Check if original coupon field already exists in accordion to prevent duplication
                            const existingOriginalCoupon = $content.find('.accordion-original-coupon');
                            if (existingOriginalCoupon.length > 0 && existingOriginalCoupon.is(':visible')) {
                                console.log('✓ Checkout customization: Existing original coupon field already displayed');
                                return;
                            }

                            // Find the original FluidCheckout coupon field
                            const $originalCouponContainer = $('.fc-expansible-form-section__content[class*="coupon_code"]').first();
                            const $originalCouponSection = $originalCouponContainer.find('.fc-coupon-code-section');

                            if ($originalCouponSection.length > 0) {
                                // Clone the original coupon section to preserve all functionality
                                const $clonedCouponSection = $originalCouponSection.clone(true, true);

                                // Update IDs to prevent conflicts (since we're cloning)
                                $clonedCouponSection.find('#coupon_code').attr('id', 'accordion_coupon_code');
                                $clonedCouponSection.find('#coupon_code_field').attr('id', 'accordion_coupon_code_field');

                                // Add a wrapper class for styling and identification
                                $clonedCouponSection.addClass('accordion-original-coupon');

                                // Ensure the cloned section is visible
                                $clonedCouponSection.show();
                                $clonedCouponSection.find('input, button').show();

                                // Insert the cloned original field into the accordion
                                $content.find('.checkout-substep-accordion-inner').append($clonedCouponSection);

                                console.log('✓ Checkout customization: Original WooCommerce coupon field cloned and displayed in accordion');
                            } else {
                                // Check if we already have the original coupon field in the accordion content (moved, not cloned)
                                const $existingCouponSection = $content.find('.fc-coupon-code-section');
                                if ($existingCouponSection.length > 0) {
                                    // We have the original field, just need to make it visible
                                    $existingCouponSection.addClass('accordion-original-coupon');
                                    $existingCouponSection.show();
                                    $existingCouponSection.find('input, button').show();

                                    // Also expand any collapsed parent containers
                                    $existingCouponSection.parents('.fc-expansible-form-section__content').show().css({
                                        'display': 'block',
                                        'height': 'auto',
                                        'overflow': 'visible'
                                    });

                                    console.log('✓ Checkout customization: Existing original coupon field made visible in accordion');
                                } else {
                                    // Fallback: Create a basic coupon form if original not found
                                    const couponFormHtml = `
                                        <div class="fc-coupon-code-section accordion-original-coupon">
                                            <p class="form-row form-row-wide fc-no-validation-icon fc-text-field" id="accordion_coupon_code_field">
                                                <span class="woocommerce-input-wrapper">
                                                    <input type="text" class="input-text" name="coupon_code" id="accordion_coupon_code" placeholder="Enter your code here" value="" aria-label="Coupon code" />
                                                </span>
                                            </p>
                                            <button type="button" class="fc-coupon-code__apply button" data-apply-coupon-button="">Apply</button>
                                        </div>
                                    `;
                                    $content.find('.checkout-substep-accordion-inner').append(couponFormHtml);
                                    console.log('✓ Checkout customization: Fallback coupon field created in accordion');
                                }
                            }

                            // Hide the "Add coupon code" button/link if it exists
                            const $addCouponButton = $content.find('a:contains("Add coupon code"), button:contains("Add coupon code"), .woocommerce-form-coupon-toggle, [class*="coupon-toggle"]');
                            if ($addCouponButton.length > 0) {
                                $addCouponButton.hide();
                            }
                        } catch (error) {
                            console.error('Error in showCouponInputDirectly:', error);
                        }
                    }

                    // Append to the end of the table body
                    const tableBody = orderSummaryTable.find('tbody');
                    if (tableBody.length > 0) {
                        tableBody.append(accordionRow);
                    } else {
                        orderSummaryTable.append(accordionRow);
                    }

                    // Hide the original substep
                    substepToMove.hide();

                    // Add CSS styles for the accordion
                    if (!$('#checkout-substep-accordion-styles').length) {
                        $('head').append(`
                            <style id="checkout-substep-accordion-styles">
                                .checkout-substep-accordion-row {
                                    border-top: 1px solid #e0e0e0;
                                }

                                .checkout-substep-accordion-cell {
                                    padding: 0 !important;
                                    border: none !important;
                                }

                                /* Hide original FluidCheckout coupon elements */
                                .fc-step__substep-title.fc-step__substep-title--coupon_codes,
                                .fc-step__substep-fields.fc-substep__fields--coupon_codes,
                                .fc-step__substep-fields.fc-substep__fields--coupon_codes.is-expanded,
                                .fc-step__substep-fields.fc-substep__fields--coupon_codes.is-activated {
                                    display: none !important;
                                    visibility: hidden !important;
                                    height: 0 !important;
                                    overflow: hidden !important;
                                }

                                .checkout-substep-accordion-header {
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    padding: 15px 20px;
                                    background-color: transparent !important;
                                    border-bottom: 1px solid #e0e0e0;
                                    cursor: pointer;
                                    transition: background-color 0.2s ease;
                                    user-select: none;
                                }

                                .checkout-substep-accordion-header:hover {
                                    background-color: transparent !important;
                                }

                                .checkout-substep-accordion-header:active,
                                .checkout-substep-accordion-header:focus {
                                    background-color: transparent !important;
                                }

                                .checkout-substep-accordion-header.expanded {
                                    background-color: transparent !important;
                                }

                                .checkout-substep-accordion-title {
                                    font-weight: 600;
                                    font-size: 14px;
                                    color: #333;
                                }

                                .checkout-substep-accordion-icon {
                                    font-size: 12px;
                                    color: #666;
                                    transition: transform 0.2s ease;
                                }

                                .checkout-substep-accordion-content {
                                    border-bottom: 1px solid #e0e0e0;
                                }

                                .checkout-substep-accordion-inner {
                                    padding: 20px;
                                    background-color: #fff;
                                }

                                /* Original FluidCheckout coupon field styles in accordion */
                                .checkout-substep-accordion-inner .accordion-original-coupon,
                                .checkout-substep-accordion-inner .fc-coupon-code-section {
                                    display: block !important;
                                    margin: 0;
                                    padding: 0;
                                    visibility: visible !important;
                                    height: auto !important;
                                    overflow: visible !important;
                                }

                                .checkout-substep-accordion-inner .accordion-original-coupon .form-row,
                                .checkout-substep-accordion-inner .fc-coupon-code-section .form-row {
                                    margin-bottom: 15px;
                                    display: flex;
                                    align-items: center;
                                    gap: 10px;
                                }

                                .checkout-substep-accordion-inner .accordion-original-coupon input[name="coupon_code"],
                                .checkout-substep-accordion-inner .fc-coupon-code-section input[name="coupon_code"] {
                                    flex: 1;
                                    max-width: 200px;
                                    padding: 8px 12px;
                                    border: 1px solid #ddd;
                                    border-radius: 4px;
                                    font-size: 14px;
                                    display: block !important;
                                    visibility: visible !important;
                                }

                                .checkout-substep-accordion-inner .accordion-original-coupon .fc-coupon-code__apply,
                                .checkout-substep-accordion-inner .fc-coupon-code-section .fc-coupon-code__apply {
                                    background-color: #0073aa;
                                    color: white;
                                    border: none;
                                    padding: 8px 16px;
                                    border-radius: 4px;
                                    cursor: pointer;
                                    font-size: 14px;
                                    white-space: nowrap;
                                    display: block !important;
                                    visibility: visible !important;
                                }

                                .checkout-substep-accordion-inner .accordion-original-coupon .fc-coupon-code__apply:hover,
                                .checkout-substep-accordion-inner .fc-coupon-code-section .fc-coupon-code__apply:hover {
                                    background-color: #005a87;
                                }

                                /* Force visibility of collapsed FluidCheckout sections within accordion */
                                .checkout-substep-accordion-inner .fc-expansible-form-section__content {
                                    display: block !important;
                                    height: auto !important;
                                    overflow: visible !important;
                                    visibility: visible !important;
                                }

                                /* Hide "Add coupon code" buttons */
                                .checkout-substep-accordion-inner .woocommerce-form-coupon-toggle,
                                .checkout-substep-accordion-inner button[class*="coupon-toggle"],
                                .checkout-substep-accordion-inner button:contains("Add coupon code") {
                                    display: none !important;
                                    visibility: hidden !important;
                                }

                                /* Responsive adjustments */
                                @media (max-width: 768px) {
                                    /* REMOVED: Mobile/Tablet Layout modifications that were causing issues */

                                    /* Accordion adjustments for mobile */
                                    .checkout-substep-accordion-header {
                                        padding: 12px 15px;
                                    }

                                    .checkout-substep-accordion-inner {
                                        padding: 15px;
                                    }

                                    .checkout-substep-accordion-inner input[name="coupon_code"],
                                    .checkout-substep-accordion-inner input[id*="coupon"],
                                    .checkout-substep-accordion-inner input[class*="coupon"] {
                                        max-width: 150px;
                                        margin-bottom: 10px;
                                    }

                                    /* Mobile Order Summary Accordion */
                                    .fc-checkout-order-review__head {
                                        background-color: #f8f9fa;
                                        border: 1px solid #e0e0e0;
                                        border-radius: 6px;
                                        padding: 15px 20px;
                                        margin-bottom: 10px;
                                        transition: background-color 0.2s ease;
                                    }

                                    .fc-checkout-order-review__head:hover {
                                        background-color: #e9ecef;
                                    }

                                    .fc-checkout-order-review__head h3 {
                                        margin: 0;
                                        display: flex;
                                        align-items: center;
                                        justify-content: space-between;
                                        font-size: 16px;
                                        font-weight: 600;
                                    }

                                    .mobile-order-summary-icon {
                                        color: #666;
                                        font-weight: normal;
                                    }

                                    .woocommerce-checkout-review-order {
                                        border: 1px solid #e0e0e0;
                                        border-radius: 6px;
                                        padding: 0;
                                        margin-bottom: 20px;
                                        overflow: hidden;
                                    }

                                    /* Fix mobile order summary table content styling */
                                    .woocommerce-checkout-review-order table {
                                        width: 100% !important;
                                        border-collapse: collapse;
                                        margin: 0;
                                    }

                                    .woocommerce-checkout-review-order table td,
                                    .woocommerce-checkout-review-order table th {
                                        padding: 12px 15px;
                                        border-bottom: 1px solid #f0f0f0;
                                        vertical-align: top;
                                        font-size: 14px;
                                        line-height: 1.4;
                                    }

                                    /* Fix product thumbnail size on mobile */
                                    .woocommerce-checkout-review-order .product-thumbnail img,
                                    .woocommerce-checkout-review-order .product-name img {
                                        max-width: 60px !important;
                                        max-height: 60px !important;
                                        width: 60px !important;
                                        height: 60px !important;
                                        object-fit: cover;
                                        border-radius: 4px;
                                        margin-right: 12px;
                                        flex-shrink: 0;
                                    }

                                    /* Product row layout optimization */
                                    .woocommerce-checkout-review-order .cart_item td:first-child {
                                        display: flex;
                                        align-items: flex-start;
                                        gap: 12px;
                                        padding: 15px;
                                    }

                                    .woocommerce-checkout-review-order .product-name {
                                        flex: 1;
                                        min-width: 0;
                                        display: flex !important;
                                        align-items: flex-start;
                                        gap: 12px;
                                    }

                                    /* Ensure product details container takes remaining space */
                                    .woocommerce-checkout-review-order .product-name .product-details {
                                        flex: 1;
                                        min-width: 0;
                                    }

                                    .woocommerce-checkout-review-order .product-name a {
                                        font-size: 14px;
                                        font-weight: 500;
                                        line-height: 1.3;
                                        color: #333;
                                        text-decoration: none;
                                        display: block;
                                        margin-bottom: 4px;
                                    }

                                    /* Quantity display styling */
                                    .woocommerce-checkout-review-order .product-quantity {
                                        font-size: 12px;
                                        color: #666;
                                        margin-top: 4px;
                                    }

                                    /* Price column styling */
                                    .woocommerce-checkout-review-order .product-total {
                                        text-align: right;
                                        font-weight: 600;
                                        font-size: 14px;
                                        white-space: nowrap;
                                        padding-left: 10px;
                                    }

                                    /* Order totals section styling */
                                    .woocommerce-checkout-review-order .order-total {
                                        background-color: #f8f9fa;
                                        font-weight: 600;
                                        font-size: 16px;
                                    }

                                    .woocommerce-checkout-review-order .order-total td {
                                        padding: 15px;
                                        border-bottom: none;
                                    }

                                    /* Ensure proper spacing for all table rows */
                                    .woocommerce-checkout-review-order tbody tr:last-child td {
                                        border-bottom: none;
                                    }
                                }

                                /* Desktop-specific CSS to restore original WooCommerce thumbnail sizing */
                                @media (min-width: 769px) {
                                    /* Restore original desktop product thumbnail size */
                                    .woocommerce-checkout-review-order .product-thumbnail img,
                                    .woocommerce-checkout-review-order .product-name img {
                                        max-width: 64px !important;
                                        max-height: 64px !important;
                                        width: auto !important;
                                        height: auto !important;
                                        object-fit: cover;
                                        border-radius: 4px;
                                        margin-right: 15px;
                                        vertical-align: top;
                                    }

                                    /* Ensure desktop table structure is preserved */
                                    .woocommerce-checkout-review-order .product-name {
                                        display: table-cell !important;
                                        vertical-align: top;
                                        padding: 15px;
                                        border-bottom: 1px solid #f0f0f0;
                                    }

                                    /* Desktop product details styling */
                                    .woocommerce-checkout-review-order .product-name .product-details {
                                        display: inline-block;
                                        vertical-align: top;
                                        margin-left: 0;
                                    }

                                    /* Desktop table cell styling */
                                    .woocommerce-checkout-review-order table td,
                                    .woocommerce-checkout-review-order table th {
                                        padding: 15px;
                                        border-bottom: 1px solid #f0f0f0;
                                        vertical-align: top;
                                    }

                                    /* Desktop order totals styling */
                                    .woocommerce-checkout-review-order .order-total td {
                                        background-color: #f8f9fa;
                                        font-weight: 600;
                                        border-bottom: none;
                                    }
                                }

                                /* Mobile/Tablet Place Order Section Repositioning */
                                @media (max-width: 768px) {
                                    /* Use JavaScript DOM manipulation instead of CSS order for better control */
                                }

                                /* Coupon Code Field Styling - All Viewports - Enhanced Specificity */
                                /* Target the specific coupon input field found in the accordion */
                                .woocommerce-checkout input[name="coupon_code"],
                                .checkout-substep-content input[name="coupon_code"],
                                .woocommerce-input-wrapper input[name="coupon_code"],
                                input[name="coupon_code"].input-text,
                                .coupon-code input,
                                .checkout-substep-content input[type="text"],
                                .form-row input[type="text"] {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                    box-sizing: border-box !important;
                                    padding: 12px 15px !important;
                                    border: 1px solid #ddd !important;
                                    border-radius: 4px !important;
                                    font-size: 14px !important;
                                    margin-bottom: 10px !important;
                                    display: block !important;
                                }

                                /* Target the specific coupon apply button found in the accordion */
                                .fc-coupon-code__apply.button,
                                .fc-coupon-code-section .fc-coupon-code__apply,
                                .checkout-substep-content .fc-coupon-code__apply,
                                .woocommerce-checkout button[name="apply_coupon"],
                                .coupon-code button,
                                .checkout-substep-content button,
                                .form-row button {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                    box-sizing: border-box !important;
                                    padding: 12px 20px !important;
                                    background-color: #007cba !important;
                                    color: white !important;
                                    border: none !important;
                                    border-radius: 4px !important;
                                    font-size: 14px !important;
                                    font-weight: 600 !important;
                                    cursor: pointer !important;
                                    transition: background-color 0.2s ease !important;
                                    display: block !important;
                                    margin-top: 10px !important;
                                }

                                /* Hover states for coupon apply button */
                                .fc-coupon-code__apply.button:hover,
                                .fc-coupon-code-section .fc-coupon-code__apply:hover,
                                .checkout-substep-content .fc-coupon-code__apply:hover,
                                .woocommerce-checkout button[name="apply_coupon"]:hover,
                                .coupon-code button:hover,
                                .checkout-substep-content button:hover,
                                .form-row button:hover {
                                    background-color: #005a87 !important;
                                }

                                /* Coupon field container styling - Enhanced specificity */
                                .fc-coupon-code-section,
                                .checkout-substep-content .fc-coupon-code-section,
                                .checkout-substep-content .form-row,
                                .coupon-code,
                                .woocommerce-form-coupon,
                                .woocommerce-input-wrapper {
                                    display: flex !important;
                                    flex-direction: column !important;
                                    gap: 10px !important;
                                    padding: 15px !important;
                                    background-color: #f9f9f9 !important;
                                    border-radius: 6px !important;
                                    margin: 10px 0 !important;
                                    width: 100% !important;
                                    box-sizing: border-box !important;
                                }

                                /* Issue 1: Place Order Section Positioning Fix - Mobile/Tablet Only */
                                @media (max-width: 768px) {
                                    /* Move place order section from order summary to payment section container */
                                    .fc-place-order__section.fc-place-order__section--main {
                                        /* Remove from order summary positioning */
                                        position: static !important;
                                        transform: none !important;

                                        /* Reset any existing mobile wrapper styles */
                                        margin: 0 !important;
                                        padding: 0 !important;
                                        background: none !important;
                                        border: none !important;
                                        box-shadow: none !important;
                                        border-radius: 0 !important;
                                    }

                                    /* Ensure the mobile wrapper is properly positioned */
                                    .fc-mobile-place-order-wrapper {
                                        /* Position within payment section flow */
                                        order: 999 !important;
                                        margin: 20px 0 !important;
                                        padding: 20px !important;
                                        background-color: #ffffff !important;
                                        border-radius: 8px !important;
                                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
                                        border: 1px solid #e0e0e0 !important;
                                    }

                                    /* Style the place order button for mobile */
                                    .fc-mobile-place-order-wrapper .place-order,
                                    .fc-mobile-place-order-wrapper button[name="woocommerce_checkout_place_order"] {
                                        width: 100% !important;
                                        padding: 15px 20px !important;
                                        font-size: 16px !important;
                                        font-weight: 600 !important;
                                        background-color: #007cba !important;
                                        color: white !important;
                                        border: none !important;
                                        border-radius: 6px !important;
                                        cursor: pointer !important;
                                        transition: background-color 0.2s ease !important;
                                    }

                                    .fc-mobile-place-order-wrapper .place-order:hover,
                                    .fc-mobile-place-order-wrapper button[name="woocommerce_checkout_place_order"]:hover {
                                        background-color: #005a87 !important;
                                    }
                                }



                                /* Issue 1: Order Summary Title Layout Broken on Desktop - Fix Float Disruption */
                                .fc-checkout-order-review__edit-cart {
                                    float: none !important;
                                    display: block !important;
                                    margin: 0 !important;
                                    padding: 0 !important;
                                }

                                /* Ensure parent container maintains proper flexbox layout */
                                .fc-checkout-order-review__head {
                                    display: flex !important;
                                    flex-direction: row !important;
                                    justify-content: space-between !important;
                                    align-items: center !important;
                                    width: 100% !important;
                                }

                                /* Issue 2: Coupon Field Container Width Problems - All Viewports */
                                /* Remove excessive padding from coupon section container */
                                .fc-coupon-code-section.accordion-original-coupon,
                                .fc-coupon-code-section {
                                    padding: 0 !important;
                                    margin: 0 !important;
                                    background: none !important;
                                    border: none !important;
                                    box-shadow: none !important;
                                    border-radius: 0 !important;
                                }

                                /* Force display of collapsed coupon field containers */
                                .fc-expansible-form-section__content--coupon_code--534090.is-collapsed,
                                .fc-step__substep-fields--coupon_codes,
                                .fc-expansible-form-section__content.is-collapsed {
                                    display: block !important;
                                    visibility: visible !important;
                                    height: auto !important;
                                    overflow: visible !important;
                                    opacity: 1 !important;
                                }

                                /* Ensure woocommerce input wrapper displays at full width */
                                .woocommerce-input-wrapper {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                    box-sizing: border-box !important;
                                    padding: 0 !important;
                                    margin: 0 !important;
                                    display: block !important;
                                    visibility: visible !important;
                                    height: auto !important;
                                    overflow: visible !important;
                                }

                                /* Ensure coupon input field within woocommerce wrapper displays properly */
                                .woocommerce-input-wrapper input[name="coupon_code"],
                                .woocommerce-input-wrapper .input-text,
                                input[name="coupon_code"].input-text {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                    box-sizing: border-box !important;
                                    display: block !important;
                                    visibility: visible !important;
                                    height: auto !important;
                                    min-height: 40px !important;
                                    padding: 12px 15px !important;
                                    border: 1px solid #ddd !important;
                                    border-radius: 4px !important;
                                    font-size: 14px !important;
                                    margin: 0 0 10px 0 !important;
                                }

                                /* Fix parent containers that are constraining width */
                                .form-row.form-row-wide.fc-no-validation-icon.fc-text-field,
                                #coupon_code_field {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                    display: block !important;
                                    visibility: visible !important;
                                    height: auto !important;
                                }



                                /* Issue 4: Order Summary Header Alignment - Fix desktop horizontal alignment */
                                @media (min-width: 769px) {
                                    /* Ensure order summary container maintains proper flexbox alignment */
                                    .fc-checkout-order-review__head {
                                        display: flex !important;
                                        flex-direction: row !important;
                                        justify-content: space-between !important;
                                        align-items: center !important;
                                        width: 100% !important;
                                        min-height: 18px !important;
                                        height: 18px !important;
                                        line-height: 18px !important;
                                        gap: 10px !important;
                                    }

                                    /* Ensure order summary title aligns properly in flexbox */
                                    .fc-checkout-order-review__head h3,
                                    .fc-checkout-order-review__head .fc-checkout-order-review-title {
                                        display: inline-block !important;
                                        margin: 0 !important;
                                        padding: 0 !important;
                                        line-height: 18px !important;
                                        height: 18px !important;
                                        flex: 1 !important;
                                        flex-shrink: 1 !important;
                                        vertical-align: top !important;
                                        width: auto !important;
                                        max-width: none !important;
                                    }

                                    /* Ensure edit link aligns properly in flexbox */
                                    .fc-checkout-order-review__head a,
                                    .fc-checkout-order-review__head .fc-checkout-order-review__edit-cart {
                                        display: inline-block !important;
                                        margin: 0 !important;
                                        padding: 0 !important;
                                        line-height: 18px !important;
                                        height: 18px !important;
                                        flex: 0 0 auto !important;
                                        flex-shrink: 0 !important;
                                        vertical-align: top !important;
                                        float: none !important;
                                        text-decoration: underline !important;
                                        width: auto !important;
                                    }

                                    /* Remove any conflicting positioning and sizing */
                                    .fc-checkout-order-review__head * {
                                        position: static !important;
                                        box-sizing: border-box !important;
                                    }
                                }





                                /* Issue 7: Payment Method Container - Mobile/Tablet layout fixes */
                                @media (max-width: 768px) {
                                    /* Ensure place order section is properly contained within payment area */
                                    .fc-place-order__section.fc-place-order__section--main {
                                        order: 2 !important;
                                        margin-top: 20px !important;
                                    }

                                    /* Ensure payment method and place order are in same container */
                                    .fc-step[data-step-id="payment"] .fc-step__substep,
                                    .fc-step[data-step-id="payment"] .fc-step__substep-fields {
                                        display: flex !important;
                                        flex-direction: column !important;
                                    }

                                    /* Move place order section to payment container */
                                    .fc-step[data-step-id="payment"] .fc-step__substep-fields {
                                        position: relative !important;
                                    }
                                }




                            </style>
                        `);
                    }

                    console.log('✓ Checkout customization: Checkout substep moved to order summary table as accordion');
                }
            }
        } catch (error) {
            console.error('Error in moveCheckoutSubstepToOrderSummary:', error);
        }
    }

    /**
     * Modify product quantity display and move to price location in order summary
     */
    function modifyOrderSummaryQuantity() {
        try {
            // Use cached selectors for performance
            const orderSummaryContainer = getCachedElement(SELECTORS.orderSummaryContainer, true);

            if (orderSummaryContainer.length === 0) {
                return; // No order summary container found
            }

            // Find the order summary table within the container
            const orderSummaryTable = orderSummaryContainer.find('table');

            if (orderSummaryTable.length === 0) {
                return; // No order summary table found
            }

            // Find product rows (rows with images, not subtotal/shipping/tax/total rows)
            const productRows = orderSummaryTable.find('tbody tr').filter(function() {
                return $(this).find('img').length > 0;
            });

            let modifiedCount = 0;

            productRows.each(function() {
                const $row = $(this);
                const $cell = $row.find('td').first();

                // Find the quantity element
                const $quantityElement = $cell.find('.product-quantity');

                if ($quantityElement.length > 0) {
                    const currentQuantityText = $quantityElement.text().trim();

                    // Extract the number from "× 1" or "x 2" format
                    const quantityMatch = currentQuantityText.match(/[×x]\s*(\d+)/i);

                    if (quantityMatch) {
                        const quantityNumber = quantityMatch[1];
                        const newQuantityText = `Qty: ${quantityNumber}`;

                        // Find the price container and replace its entire content
                        const $priceContainer = $cell.find('.cart-item__price');

                        if ($priceContainer.length > 0) {
                            // Replace the entire price container content with just the quantity
                            $priceContainer.html(`<span class="woocommerce-Price-amount amount">${newQuantityText}</span>`);

                            // Force hide the original quantity element with !important CSS
                            $quantityElement.css('display', 'none !important');
                            $quantityElement.css('visibility', 'hidden');
                            $quantityElement.attr('aria-hidden', 'true');

                            modifiedCount++;
                        }
                    }
                }
            });

            if (modifiedCount > 0) {
                console.log(`✓ Checkout customization: ${modifiedCount} product quantities moved to price location and reformatted`);
            }
        } catch (error) {
            console.error('Error in modifyOrderSummaryQuantity:', error);
        }
    }

    /**
     * Modify shipping cost display based on price value
     */
    function modifyShippingCostDisplay() {
        try {
            // Use cached selectors for performance
            const orderSummaryContainer = getCachedElement(SELECTORS.orderSummaryContainer, true);

            if (orderSummaryContainer.length === 0) {
                return; // No order summary container found
            }

            // Find the order summary table within the container
            const orderSummaryTable = orderSummaryContainer.find('table');

            if (orderSummaryTable.length === 0) {
                return; // No order summary table found
            }

            // Find the shipping row with class woocommerce-shipping-totals shipping
            const $shippingRow = orderSummaryTable.find('tr.woocommerce-shipping-totals.shipping');

            if ($shippingRow.length === 0) {
                return; // No shipping row found
            }

            // Get the price cell (last td in the row)
            const $priceCell = $shippingRow.find('td').last();

            if ($priceCell.length === 0) {
                return; // No price cell found
            }

            // Find the price amount element
            const $priceElement = $priceCell.find('.woocommerce-Price-amount.amount');

            if ($priceElement.length === 0) {
                return; // No price element found
            }

            // Extract the numeric value from the price text
            const priceText = $priceElement.text().trim();
            const priceMatch = priceText.match(/[\d.]+/);

            if (!priceMatch) {
                return; // Could not extract price value
            }

            const priceValue = parseFloat(priceMatch[0]);

            // Get the label cell (th element)
            const $labelCell = $shippingRow.find('th');

            if (priceValue === 0) {
                // Free shipping: Replace "$0.00" with "FREE" and keep "Delivery" label
                $priceElement.html('FREE');

                // Ensure label is "Delivery" (should already be set by replaceShippingTableText)
                if ($labelCell.length > 0 && $labelCell.text().trim() !== 'Delivery') {
                    $labelCell.text('Delivery');
                }

                console.log('✓ Checkout customization: Free shipping price replaced with "FREE"');
            } else {
                // Paid shipping: Revert label back to "Shipping" and keep original price
                if ($labelCell.length > 0) {
                    $labelCell.text('Shipping');
                }

                console.log(`✓ Checkout customization: Paid shipping label reverted to "Shipping" (${priceText})`);
            }
        } catch (error) {
            console.error('Error in modifyShippingCostDisplay:', error);
        }
    }

    /**
     * Completely disable and hide the terms and conditions checkbox while maintaining checkout functionality
     */
    function makeTermsCheckboxOptional() {
        try {
            // Find the terms and conditions checkbox
            const $termsCheckbox = $('input[name="terms"]');

            if ($termsCheckbox.length === 0) {
                return; // Terms checkbox not found
            }

            // Find the parent container (paragraph element containing checkbox and label)
            const $checkboxContainer = $termsCheckbox.closest('p');

            if ($checkboxContainer.length === 0) {
                return; // Container not found
            }

            let modificationsCount = 0;

            // 1. Set the checkbox as checked to prevent server-side validation errors
            if (!$termsCheckbox.prop('checked')) {
                $termsCheckbox.prop('checked', true);
                modificationsCount++;
            }

            // 2. Disable the checkbox input element
            if (!$termsCheckbox.prop('disabled')) {
                $termsCheckbox.prop('disabled', true);
                modificationsCount++;
            }

            // 3. Hide the entire checkbox container (paragraph with checkbox and label text)
            if ($checkboxContainer.is(':visible')) {
                $checkboxContainer.hide();
                modificationsCount++;
            }

            // 4. Remove validation classes and attributes for compatibility
            const $formRow = $termsCheckbox.closest('.form-row');
            if ($formRow.length > 0 && $formRow.hasClass('validate-required')) {
                $formRow.removeClass('validate-required');
                modificationsCount++;
            }

            // 5. Remove required and aria-required attributes
            if ($termsCheckbox.attr('required')) {
                $termsCheckbox.removeAttr('required');
                modificationsCount++;
            }

            if ($termsCheckbox.attr('aria-required')) {
                $termsCheckbox.removeAttr('aria-required');
                modificationsCount++;
            }

            if (modificationsCount > 0) {
                console.log(`✓ Checkout customization: Terms and conditions checkbox completely disabled and hidden (${modificationsCount} modifications applied)`);
            }
        } catch (error) {
            console.error('Error in makeTermsCheckboxOptional:', error);
        }
    }

    /**
     * Replace checkmark icons with step numbers for expanded sections (with debouncing)
     */
    function replaceStepIcons() {
        // Clear any existing timeout to debounce rapid calls
        clearTimeout(stepIconUpdateTimeout);

        stepIconUpdateTimeout = setTimeout(() => {
            try {
                // Define corrected step mappings
                const stepMappings = {
                    'contact': '1',
                    'shipping_address': '2',
                    'shipping_method': '3',    // Added new mapping for shipping method
                    'billing_address': '4',    // Changed from '3' to '4'
                    'payment': '5'             // Changed from '4' to '5'
                };

                // Create or update dynamic CSS for step icons
                let dynamicStyleId = 'fc-dynamic-step-icons';
                let existingStyle = document.getElementById(dynamicStyleId);

                if (!existingStyle) {
                    const styleElement = document.createElement('style');
                    styleElement.id = dynamicStyleId;
                    document.head.appendChild(styleElement);
                    existingStyle = styleElement;
                }

                let cssRules = '';
                let changesCount = 0;

                // Check each step type for expansion state
                Object.keys(stepMappings).forEach(function(stepType) {
                    const stepNumber = stepMappings[stepType];
                    const fieldsElement = $(`.fc-step__substep-fields.fc-substep__fields--${stepType}`);

                    if (fieldsElement.length > 0) {
                        const isExpanded = fieldsElement.hasClass('is-expanded');
                        const isCollapsed = fieldsElement.hasClass('is-collapsed');

                        if (isExpanded) {
                            // Show step number for expanded sections - only modify content property with enhanced centering
                            cssRules += `
                                .has-checkout-layout--multi-step.woocommerce-checkout form .fc-wrapper .fc-checkout-step[data-step-complete] .fc-step__substep .fc-step__substep-title.fc-step__substep-title--${stepType}:before {
                                    display: flex !important;
                                    align-items: center !important;
                                    justify-content: center !important;
                                    text-align: center !important;
                                    line-height: 18px !important;
                                    content: "${stepNumber}" !important;
                                }
                            `;
                            changesCount++;
                        } else if (isCollapsed && !isExpanded) {
                            // Restore checkmark for collapsed sections - only modify content property with enhanced centering
                            cssRules += `
                                .has-checkout-layout--multi-step.woocommerce-checkout form .fc-wrapper .fc-checkout-step[data-step-complete] .fc-step__substep .fc-step__substep-title.fc-step__substep-title--${stepType}:before {
                                    display: flex !important;
                                    align-items: center !important;
                                    justify-content: center !important;
                                    text-align: center !important;
                                    line-height: 18px !important;
                                    content: "\\e805" !important;
                                }
                            `;
                            changesCount++;
                        }
                    }
                });

                // Apply the CSS rules
                if (cssRules) {
                    existingStyle.textContent = cssRules;
                }

                if (changesCount > 0) {
                    console.log(`✓ Checkout customization: ${changesCount} step icons updated based on expansion state (Contact=1, Shipping Address=2, Shipping Method=3, Billing=4, Payment=5)`);
                }
            } catch (error) {
                console.error('Error in replaceStepIcons:', error);
            }
        }, 50); // 50ms debounce delay
    }

    /**
     * Single orchestrator function to execute all replacements with error handling
     */
    function executeAllReplacements() {
        const replacements = [
            replaceContactText,
            replaceChangeButtonText,
            replaceEditCartText,
            replaceShippingToText,
            replaceBillingToText,
            replaceShippingTableText,
            replacePaymentMethodText,
            replacePrivacyPolicyText,
            replaceCouponInputPlaceholder,
            replaceCouponApplyButtonText,
            moveCheckoutSubstepToOrderSummary,
            modifyOrderSummaryQuantity,
            modifyShippingCostDisplay,
            makeTermsCheckboxOptional,
            replaceStepIcons
        ];

        replacements.forEach(fn => {
            try {
                fn();
            } catch (error) {
                console.error(`Error in ${fn.name}:`, error);
            }
        });

        // Clear element cache after replacements to ensure fresh queries next time
        clearElementCache();
    }

    /**
     * Exponential backoff scheduling for performance optimization
     */
    function scheduleNextCheck() {
        if (checkCount >= maxChecks) {
            console.log('✓ Checkout customization: Exponential backoff checks completed');
            return;
        }

        setTimeout(() => {
            executeAllReplacements();
            checkCount++;
            checkInterval = Math.min(checkInterval * 1.5, 5000); // Cap at 5s
            scheduleNextCheck();
        }, checkInterval);
    }

    /**
     * Initialize text replacement with multiple fallback methods
     */
    function initializeTextReplacement() {
        // Method 1: Immediate replacement on DOM ready
        executeAllReplacements();

        // Method 2: Delayed replacement for dynamic content
        setTimeout(function() {
            executeAllReplacements();
        }, 500);

        // Method 3: Watch for DOM changes (for AJAX-loaded content) with narrowed scope
        if (window.MutationObserver) {
            // Disconnect existing observer if present
            if (checkoutObserver) {
                checkoutObserver.disconnect();
            }

            checkoutObserver = new MutationObserver(function(mutations) {
                let shouldReplace = false;

                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' || mutation.type === 'characterData') {
                        // Check if any added nodes contain our target element
                        if (mutation.addedNodes.length > 0) {
                            for (let i = 0; i < mutation.addedNodes.length; i++) {
                                const node = mutation.addedNodes[i];
                                if (node.nodeType === Node.ELEMENT_NODE) {
                                    if ($(node).find('.fc-step__substep-title--contact').length > 0 ||
                                        $(node).hasClass('fc-step__substep-title--contact')) {
                                        shouldReplace = true;
                                        break;
                                    }
                                }
                            }
                        }

                        // Check if the text content changed to "My contact"
                        if (mutation.target &&
                            mutation.target.textContent &&
                            mutation.target.textContent.includes('My contact')) {
                            shouldReplace = true;
                        }
                    }
                });

                if (shouldReplace) {
                    setTimeout(function() {
                        executeAllReplacements();
                    }, 100);
                }
            });

            // Target specific checkout container instead of entire document.body
            const checkoutContainer = $(SELECTORS.checkoutContainer)[0];
            if (checkoutContainer) {
                checkoutObserver.observe(checkoutContainer, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
                console.log('✓ Checkout customization: MutationObserver initialized with narrowed scope');
            } else {
                // Fallback to document.body if checkout container not found
                checkoutObserver.observe(document.body, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
                console.log('✓ Checkout customization: MutationObserver initialized with document.body fallback');
            }
        }

        // Method 4: Exponential backoff scheduling (replaces aggressive periodic checking)
        scheduleNextCheck();
    }

    /**
     * Initialize mobile/tablet order summary accordion
     */
    function initializeMobileOrderSummaryAccordion() {
        const orderReviewHead = document.querySelector('.fc-checkout-order-review__head');
        const orderReviewContent = document.querySelector('.woocommerce-checkout-review-order');

        if (!orderReviewHead || !orderReviewContent) {
            return;
        }

        // Reset desktop state first
        if (window.innerWidth > 768) {
            // Desktop: Remove accordion functionality and show content
            orderReviewContent.style.display = 'block';
            orderReviewHead.style.cursor = 'default';
            orderReviewHead.removeAttribute('role');
            orderReviewHead.removeAttribute('tabindex');
            orderReviewHead.removeAttribute('aria-expanded');
            orderReviewHead.removeAttribute('aria-controls');

            // Remove mobile accordion icon if it exists
            const existingIcon = orderReviewHead.querySelector('.mobile-order-summary-icon');
            if (existingIcon) {
                existingIcon.remove();
            }

            console.log('✓ Checkout customization: Desktop order summary restored');
            return;
        }

        // Mobile/Tablet: Add accordion functionality
        // Check if already initialized to prevent duplicates
        if (orderReviewHead.hasAttribute('data-mobile-accordion-initialized')) {
            return;
        }

        // Mark as initialized
        orderReviewHead.setAttribute('data-mobile-accordion-initialized', 'true');

        // Add accordion functionality to the header
        orderReviewHead.style.cursor = 'pointer';
        orderReviewHead.style.userSelect = 'none';
        orderReviewHead.setAttribute('role', 'button');
        orderReviewHead.setAttribute('tabindex', '0');
        orderReviewHead.setAttribute('aria-expanded', 'false');
        orderReviewHead.setAttribute('aria-controls', 'mobile-order-summary-content');

        // Set up the content
        orderReviewContent.id = 'mobile-order-summary-content';
        orderReviewContent.style.display = 'none'; // Start collapsed
        orderReviewContent.style.transition = 'all 0.3s ease';

        // Add accordion icon (check for existing icon first)
        let accordionIcon = orderReviewHead.querySelector('.mobile-order-summary-icon');
        if (!accordionIcon) {
            accordionIcon = document.createElement('span');
            accordionIcon.className = 'mobile-order-summary-icon';
            accordionIcon.innerHTML = '▼';
            accordionIcon.style.marginLeft = '10px';
            accordionIcon.style.fontSize = '14px';
            accordionIcon.style.transition = 'transform 0.3s ease';

            // Insert icon after the "Order summary" text but before the "Edit" link
            const heading = orderReviewHead.querySelector('h3');
            if (heading) {
                heading.appendChild(accordionIcon);
            } else {
                orderReviewHead.appendChild(accordionIcon);
            }
        }

        // Toggle function
        function toggleOrderSummary() {
            const isExpanded = orderReviewHead.getAttribute('aria-expanded') === 'true';

            if (isExpanded) {
                // Collapse
                orderReviewContent.style.display = 'none';
                orderReviewHead.setAttribute('aria-expanded', 'false');
                accordionIcon.innerHTML = '▼';
                accordionIcon.style.transform = 'rotate(0deg)';
                console.log('✓ Checkout customization: Mobile order summary collapsed');
            } else {
                // Expand
                orderReviewContent.style.display = 'block';
                orderReviewHead.setAttribute('aria-expanded', 'true');
                accordionIcon.innerHTML = '▲';
                accordionIcon.style.transform = 'rotate(180deg)';
                console.log('✓ Checkout customization: Mobile order summary expanded');
            }
        }

        // Add click event listener
        orderReviewHead.addEventListener('click', function(e) {
            // Don't trigger if clicking on the Edit link
            if (e.target.tagName === 'A' || e.target.closest('a')) {
                return;
            }
            toggleOrderSummary();
        });

        // Add keyboard support
        orderReviewHead.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleOrderSummary();
            }
        });

        console.log('✓ Checkout customization: Mobile order summary accordion initialized');
    }

    /**
     * Reposition place order section to appear after payment fields on mobile/tablet
     */
    function repositionPlaceOrderSectionOnMobile() {
        // Only apply on mobile/tablet viewports
        if (window.innerWidth > 768) {
            return;
        }

        try {
            const placeOrderSection = document.querySelector('.fc-place-order__section.fc-place-order__section--main');
            const paymentFields = document.querySelector('.fc-substep__fields--payment');

            if (!placeOrderSection || !paymentFields) {
                return;
            }

            // Check if already repositioned to prevent duplicates
            if (placeOrderSection.hasAttribute('data-mobile-repositioned')) {
                return;
            }

            // Mark as repositioned
            placeOrderSection.setAttribute('data-mobile-repositioned', 'true');

            // Find the payment section parent to insert after
            const paymentSection = paymentFields.closest('.fc-checkout-step');

            if (paymentSection) {
                // Create a wrapper for the place order section
                const mobileWrapper = document.createElement('div');
                mobileWrapper.className = 'fc-mobile-place-order-wrapper';

                // Move the place order section into the wrapper
                mobileWrapper.appendChild(placeOrderSection);

                // Insert the wrapper after the payment section
                paymentSection.parentNode.insertBefore(mobileWrapper, paymentSection.nextSibling);

                console.log('✓ Checkout customization: Place order section repositioned below payment fields on mobile');
            }
        } catch (error) {
            console.error('Error in repositionPlaceOrderSectionOnMobile:', error);
        }
    }

    /**
     * Handle checkout page updates and AJAX events
     */
    function handleCheckoutUpdates() {
        // Listen for WooCommerce checkout updates
        $(document.body).on('updated_checkout', function() {
            setTimeout(function() {
                executeAllReplacements();
                initializeMobileOrderSummaryAccordion();
                repositionPlaceOrderSectionOnMobile();
                fixCouponCodeFieldStyling();
            }, 200);
        });

        // Listen for FluidCheckout specific events if available
        $(document.body).on('fc_checkout_updated', function() {
            setTimeout(function() {
                executeAllReplacements();
                initializeMobileOrderSummaryAccordion();
                repositionPlaceOrderSectionOnMobile();
                fixCouponCodeFieldStyling();
            }, 200);
        });

        // Listen for any form updates that might reload sections
        $(document).on('change', '.woocommerce-checkout input, .woocommerce-checkout select', function() {
            setTimeout(function() {
                executeAllReplacements();
            }, 500);
        });

        // Listen for FluidCheckout step expansion/collapse events
        $(document).on('click', '.fc-step__substep-edit, .fc-step__substep-save', function() {
            setTimeout(function() {
                replaceStepIcons();
            }, 300);
        });
    }

    /**
     * Fix coupon code field styling and ensure proper accordion expansion
     */
    function fixCouponCodeFieldStyling() {
        try {
            // Find the coupon accordion and ensure it's properly expanded
            const couponAccordion = document.querySelector('.checkout-substep-accordion-header');
            const couponContent = document.querySelector('.checkout-substep-content');

            if (couponAccordion && couponContent) {
                // Force accordion to be expanded if it contains coupon fields
                const couponInput = couponContent.querySelector('input[name="coupon_code"]');
                const couponButton = couponContent.querySelector('.fc-coupon-code__apply');

                if (couponInput || couponButton) {
                    // Set aria-expanded to true
                    couponAccordion.setAttribute('aria-expanded', 'true');

                    // Ensure content is visible
                    couponContent.style.display = 'block';
                    couponContent.style.visibility = 'visible';
                    couponContent.style.height = 'auto';
                    couponContent.style.overflow = 'visible';

                    // Apply full-width styling directly to elements
                    if (couponInput) {
                        couponInput.style.width = '100%';
                        couponInput.style.maxWidth = '100%';
                        couponInput.style.boxSizing = 'border-box';
                        couponInput.style.display = 'block';
                    }

                    if (couponButton) {
                        couponButton.style.width = '100%';
                        couponButton.style.maxWidth = '100%';
                        couponButton.style.boxSizing = 'border-box';
                        couponButton.style.display = 'block';
                        couponButton.style.marginTop = '10px';
                    }

                    console.log('✓ Checkout customization: Coupon code field styling fixed and accordion expanded');
                }
            }
        } catch (error) {
            console.error('Error in fixCouponCodeFieldStyling:', error);
        }
    }

    /**
     * Cleanup function to prevent memory leaks
     */
    function cleanup() {
        if (checkoutObserver) {
            checkoutObserver.disconnect();
            checkoutObserver = null;
        }
        clearTimeout(stepIconUpdateTimeout);
        clearElementCache();
        console.log('✓ Checkout customization: Cleanup completed');
    }

    // Initialize everything
    console.log('🚀 Checkout customization script loaded');
    initializeTextReplacement();
    handleCheckoutUpdates();

    // Initialize mobile accordion with delay to ensure DOM is ready
    setTimeout(function() {
        initializeMobileOrderSummaryAccordion();
        repositionPlaceOrderSectionOnMobile();
        fixCouponCodeFieldStyling();
        // applyCriticalLayoutFixes(); // DISABLED - was causing layout issues
    }, 500);

    // Handle viewport changes
    $(window).on('resize', function() {
        setTimeout(function() {
            initializeMobileOrderSummaryAccordion();
            repositionPlaceOrderSectionOnMobile();
            fixCouponCodeFieldStyling();
            // applyCriticalLayoutFixes(); // DISABLED - was causing layout issues
        }, 100);
    });

    // Additional safety check for single-page applications or heavy AJAX usage
    $(window).on('load', function() {
        setTimeout(function() {
            executeAllReplacements();
            // applyCriticalLayoutFixes(); // DISABLED - was causing layout issues
        }, 1000);
    });

    /**
     * Apply critical layout fixes for FluidCheckout issues
     */
    function applyCriticalLayoutFixes() {
        try {
            // DISABLED - These functions were causing layout issues
            // Issue 1: Move edit buttons inside title containers
            // moveEditButtonsInsideTitles(); // DISABLED

            // Issue 3: Fix payment container structure on mobile/tablet
            // if (window.innerWidth <= 768) {
            //     fixPaymentContainerStructure(); // DISABLED
            // }

            console.log('✓ Checkout customization: Critical layout fixes applied (layout modifications disabled)');
        } catch (error) {
            console.error('Error in applyCriticalLayoutFixes:', error);
        }
    }

    /**
     * Move edit buttons inside title containers for proper inline positioning
     */
    function moveEditButtonsInsideTitles() {
        try {
            const substeps = document.querySelectorAll('.fc-step__substep');
            let movedCount = 0;

            substeps.forEach(substep => {
                const title = substep.querySelector('.fc-step__substep-title');
                const editElement = substep.querySelector('.fc-step__substep-edit');

                if (title && editElement && !title.contains(editElement)) {
                    // Create a wrapper for the title text
                    const titleText = title.innerHTML;
                    title.innerHTML = '';

                    // Create title text span
                    const titleSpan = document.createElement('span');
                    titleSpan.innerHTML = titleText;
                    titleSpan.className = 'fc-step__substep-title-text';

                    // Append title text and edit button to title container
                    title.appendChild(titleSpan);
                    title.appendChild(editElement);

                    movedCount++;
                }
            });

            if (movedCount > 0) {
                console.log(`✓ Checkout customization: ${movedCount} edit buttons moved inside title containers`);
            }
        } catch (error) {
            console.error('Error in moveEditButtonsInsideTitles:', error);
        }
    }

    /**
     * Fix payment container structure on mobile/tablet
     */
    function fixPaymentContainerStructure() {
        try {
            const paymentStep = document.querySelector('.fc-step[data-step-id="payment"]');
            const placeOrderSection = document.querySelector('.fc-place-order__section.fc-place-order__section--main');

            if (paymentStep && placeOrderSection) {
                const paymentFields = paymentStep.querySelector('.fc-step__substep-fields');

                if (paymentFields && !paymentFields.contains(placeOrderSection)) {
                    // Move place order section into payment fields container
                    paymentFields.appendChild(placeOrderSection);
                    console.log('✓ Checkout customization: Place order section moved to payment container on mobile');
                }
            }
        } catch (error) {
            console.error('Error in fixPaymentContainerStructure:', error);
        }
    }

    // Add cleanup on page unload to prevent memory leaks
    $(window).on('beforeunload', function() {
        cleanup();
    });

});
