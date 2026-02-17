/**
 * Checkout Upsell — AJAX add/remove on checkbox toggle
 * @task 86ewm9gtt
 */
(function ($) {
  'use strict';

  $(document).on('change', '.checkout-upsell__checkbox', function () {
    var $checkbox = $(this);
    var $upsell = $checkbox.closest('.checkout-upsell');
    var productId = $upsell.data('product-id');
    var nonce = $upsell.data('nonce');
    var action = $checkbox.is(':checked') ? 'add' : 'remove';

    $upsell.addClass('checkout-upsell--loading');

    $.ajax({
      url: blazeUpsell.ajaxUrl,
      type: 'POST',
      data: {
        action: 'blaze_upsell_toggle',
        product_id: productId,
        upsell_action: action,
        nonce: nonce
      },
      success: function (response) {
        if (response.success) {
          $(document.body).trigger('update_checkout');
        } else {
          $checkbox.prop('checked', !$checkbox.is(':checked'));
        }
      },
      error: function () {
        $checkbox.prop('checked', !$checkbox.is(':checked'));
      },
      complete: function () {
        $upsell.removeClass('checkout-upsell--loading');
      }
    });
  });
})(jQuery);
