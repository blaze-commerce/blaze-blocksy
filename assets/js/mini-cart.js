/**
 * Mini Cart JavaScript functionality
 * Handles coupon application and UI interactions
 */

jQuery(document).ready(function($) {
    
    /**
     * Toggle coupon form visibility
     */
    $(document).on('click', '.coupon-toggle', function(e) {
        e.preventDefault();
        
        var $wrapper = $(this).siblings('.coupon-form-wrapper');
        var $arrow = $(this).find('.coupon-arrow');
        
        $wrapper.slideToggle(300);
        
        // Toggle arrow direction
        if ($arrow.text() === '▼') {
            $arrow.text('▲');
        } else {
            $arrow.text('▼');
        }
    });
    
    /**
     * Handle coupon form submission
     */
    $(document).on('submit', '.mini-cart-coupon-form', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $form.find('.apply-coupon-btn');
        var $input = $form.find('.coupon-code-input');
        var couponCode = $input.val().trim();
        
        if (!couponCode) {
            showCouponMessage('Please enter a coupon code.', 'error');
            return;
        }
        
        // Disable button and show loading state
        $button.prop('disabled', true).text(blazeBlocksyMiniCart.applying_coupon);
        
        // Remove any existing messages
        $('.coupon-message').remove();
        
        $.ajax({
            url: blazeBlocksyMiniCart.ajax_url,
            type: 'POST',
            data: {
                action: 'apply_mini_cart_coupon',
                coupon_code: couponCode,
                nonce: blazeBlocksyMiniCart.nonce
            },
            success: function(response) {
                if (response.success) {
                    showCouponMessage('Coupon applied successfully!', 'success');
                    $input.val(''); // Clear input
                    
                    // Trigger cart update
                    $(document.body).trigger('wc_fragment_refresh');
                } else {
                    showCouponMessage(response.data.message, 'error');
                }
            },
            error: function() {
                showCouponMessage('An error occurred. Please try again.', 'error');
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false).text(blazeBlocksyMiniCart.apply_coupon);
            }
        });
    });
    
    /**
     * Show coupon message
     */
    function showCouponMessage(message, type) {
        var messageClass = type === 'success' ? 'coupon-success' : 'coupon-error';
        var $message = $('<div class="coupon-message ' + messageClass + '">' + message + '</div>');
        
        $('.mini-cart-coupon-section').append($message);
        
        // Auto-hide success messages after 3 seconds
        if (type === 'success') {
            setTimeout(function() {
                $message.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    }
    
    /**
     * Handle mini cart updates
     */
    $(document.body).on('wc_fragments_refreshed', function() {
        // Re-initialize any dynamic elements if needed
        console.log('Mini cart fragments refreshed');
    });
    
    /**
     * Handle recommended product clicks
     */
    $(document).on('click', '.recommended-product-item .product-link', function(e) {
        // Allow normal navigation - no special handling needed
        // This is just a placeholder for future enhancements
    });
    
    /**
     * Add smooth animations for mini cart interactions
     */
    $(document).on('mouseenter', '.recommended-product-item', function() {
        $(this).addClass('hover-effect');
    }).on('mouseleave', '.recommended-product-item', function() {
        $(this).removeClass('hover-effect');
    });
    
});
