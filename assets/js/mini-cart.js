/**
 * Mini Cart JavaScript functionality
 * Handles coupon application and UI interactions
 */

jQuery(document).ready(function ($) {
  /**
   * Get cart item count from Blocksy's dynamic count element
   */
  function getCartItemCount() {
    var $countElement = $(".ct-dynamic-count-cart");
    if ($countElement.length) {
      var count = $countElement.attr("data-count");
      return parseInt(count, 10) || 0;
    }
    return 0;
  }

  /**
   * Update cart panel heading with item count
   */
  function updateCartPanelHeadingCount() {
    var $heading = $("#woo-cart-panel .ct-panel-heading");
    if (!$heading.length) {
      return;
    }

    var count = getCartItemCount();
    var $totalItems = $heading.find(".total-items");

    // Create or update the total-items span
    if ($totalItems.length) {
      $totalItems.text("(" + count + ")");
    } else {
      $heading.append(' <span class="total-items">(' + count + ")</span>");
    }
  }

  /**
   * Update cart panel heading text from customizer setting
   * This replaces expensive PHP output buffering with lightweight JS
   */
  function updateCartPanelHeading() {
    var $heading = $("#woo-cart-panel .ct-panel-heading");
    if (!$heading.length) {
      return;
    }

    // Get the base title text (without the count)
    var customTitle =
      typeof blazeBlocksyMiniCart !== "undefined" &&
      blazeBlocksyMiniCart.panel_title
        ? blazeBlocksyMiniCart.panel_title
        : null;
    var defaultTitle =
      typeof blazeBlocksyMiniCart !== "undefined" &&
      blazeBlocksyMiniCart.default_panel_title
        ? blazeBlocksyMiniCart.default_panel_title
        : "Shopping Cart";

    // Get custom SVG icon from customizer
    var panelIconSvg =
      typeof blazeBlocksyMiniCart !== "undefined" &&
      blazeBlocksyMiniCart.panel_icon_svg
        ? blazeBlocksyMiniCart.panel_icon_svg
        : "";

    // Get current heading text without the count span and icon
    var $totalItems = $heading.find(".total-items");
    var currentText = $heading.clone().children().remove().end().text().trim();

    // Build the heading content
    var titleToUse =
      customTitle && customTitle !== defaultTitle ? customTitle : currentText;
    if (titleToUse === defaultTitle && customTitle) {
      titleToUse = customTitle;
    }

    // Only update if we have customizations
    if (customTitle || panelIconSvg) {
      var headingHtml = "";

      // Add SVG icon if provided
      if (panelIconSvg) {
        headingHtml +=
          '<span class="cart-panel-icon">' + panelIconSvg + "</span> ";
      }

      // Add title
      headingHtml +=
        '<span class="cart-panel-title">' + titleToUse ||
        defaultTitle + "</span>";

      // Add total items count
      if ($totalItems.length) {
        headingHtml +=
          ' <span class="total-items">' + $totalItems.text() + "</span>";
      }

      $heading.html(headingHtml);
    }

    // Always update the count
    updateCartPanelHeadingCount();
  }

  // Run on page load
  updateCartPanelHeading();

  // Run when cart panel is opened (MutationObserver for dynamic content)
  var cartPanelObserver = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (
        mutation.attributeName === "class" ||
        mutation.attributeName === "inert"
      ) {
        updateCartPanelHeading();
      }
    });
  });

  var cartPanel = document.getElementById("woo-cart-panel");
  if (cartPanel) {
    cartPanelObserver.observe(cartPanel, { attributes: true });
  }

  // Observe changes to the cart count element
  var countElement = document.querySelector(".ct-dynamic-count-cart");
  if (countElement) {
    var countObserver = new MutationObserver(function () {
      updateCartPanelHeadingCount();
    });
    countObserver.observe(countElement, {
      attributes: true,
      attributeFilter: ["data-count"],
    });
  }

  /**
   * Toggle coupon form visibility
   */
  $(document).on("click", ".coupon-toggle", function (e) {
    e.preventDefault();

    var $wrapper = $(this).siblings(".coupon-form-wrapper");

    $wrapper.slideToggle(300);

    // Toggle arrow rotation via CSS class
    $(this).toggleClass("open");
  });

  /**
   * Toggle shipping form visibility
   */
  $(document).on("click", ".shipping-toggle", function (e) {
    e.preventDefault();

    var $wrapper = $(this).siblings(".shipping-form-wrapper");

    $wrapper.slideToggle(300);

    $(this).toggleClass("open");
  });

  /**
   * Country change - load states/provinces via AJAX
   */
  $(document).on("change", ".shipping-country-select", function () {
    var countryCode = $(this).val();
    var $stateSelect = $(this)
      .closest(".mini-cart-shipping-form")
      .find(".shipping-state-select");

    if (!countryCode) {
      $stateSelect
        .html('<option value="">City</option>')
        .prop("disabled", true);
      return;
    }

    $stateSelect
      .html('<option value="">Loading...</option>')
      .prop("disabled", true);

    $.ajax({
      url: blazeBlocksyMiniCart.ajax_url,
      type: "POST",
      data: {
        action: "blaze_blocksy_get_states",
        country_code: countryCode,
      },
      success: function (response) {
        if (response.success && response.data) {
          var states = response.data;
          var options = '<option value="">City</option>';

          if (Object.keys(states).length > 0) {
            $.each(states, function (code, name) {
              options += '<option value="' + code + '">' + name + "</option>";
            });
            $stateSelect.html(options).prop("disabled", false);
          } else {
            $stateSelect.html('<option value="">City</option>').prop("disabled", false);
          }
        }
      },
      error: function () {
        $stateSelect.html('<option value="">City</option>').prop("disabled", false);
      },
    });
  });

  /**
   * Shipping form submission - calculate shipping methods
   */
  $(document).on("submit", ".mini-cart-shipping-form", function (e) {
    e.preventDefault();

    var $form = $(this);
    var $button = $form.find(".calculate-shipping-btn");
    var $results = $form.siblings(".shipping-results");
    var $methodsList = $results.find(".shipping-methods-list");

    var country = $form.find(".shipping-country-select").val();
    var state = $form.find(".shipping-state-select").val();
    var postcode = $form.find(".shipping-postcode-input").val();

    if (!country) {
      return;
    }

    $button.prop("disabled", true).text("CALCULATING...");
    $methodsList.empty();
    $results.hide();

    $.ajax({
      url: blazeBlocksyMiniCart.ajax_url,
      type: "POST",
      data: {
        action: "calculate_minicart_shipping",
        country: country,
        state: state,
        postcode: postcode,
        nonce: blazeBlocksyMiniCart.nonce,
      },
      success: function (response) {
        if (response.success && response.data) {
          displayMiniCartShippingMethods(response.data, $methodsList);
          $results.slideDown(300);
        } else {
          var msg =
            response.data && response.data.message
              ? response.data.message
              : "No shipping methods available.";
          $methodsList.html(
            '<div class="shipping-no-methods">' + msg + "</div>"
          );
          $results.slideDown(300);
        }
      },
      error: function () {
        $methodsList.html(
          '<div class="shipping-no-methods">Error calculating shipping. Please try again.</div>'
        );
        $results.slideDown(300);
      },
      complete: function () {
        $button.prop("disabled", false).text("CALCULATE SHIPPING");
      },
    });
  });

  /**
   * Display shipping methods in the results area
   */
  function displayMiniCartShippingMethods(methods, $container) {
    $container.empty();

    $.each(methods, function (i, method) {
      var $item = $('<div class="shipping-method-item"></div>');
      $item.append($('<span class="shipping-method-label"></span>').text(method.title));
      // method.cost is pre-formatted HTML from wc_price()
      $item.append($('<span class="shipping-method-cost"></span>').html(method.cost));
      $container.append($item);
    });
  }

  /**
   * Handle coupon form submission
   */
  $(document).on("submit", ".mini-cart-coupon-form", function (e) {
    e.preventDefault();

    var $form = $(this);
    var $button = $form.find(".apply-coupon-btn");
    var $input = $form.find(".coupon-code-input");
    var couponCode = $input.val().trim();

    if (!couponCode) {
      showCouponMessage("Please enter a coupon code.", "error");
      return;
    }

    // Disable button and show loading state
    $button.prop("disabled", true).text(blazeBlocksyMiniCart.applying_coupon);

    // Remove any existing messages
    $(".coupon-message").remove();

    $.ajax({
      url: blazeBlocksyMiniCart.ajax_url,
      type: "POST",
      data: {
        action: "apply_mini_cart_coupon",
        coupon_code: couponCode,
        nonce: blazeBlocksyMiniCart.nonce,
      },
      success: function (response) {
        $input.val(""); // Clear input

        // Trigger cart update
        $(document.body).trigger("wc_fragment_refresh");
      },
      error: function () {
        showCouponMessage("An error occurred. Please try again.", "error");
      },
      complete: function () {
        // Re-enable button
        $button.prop("disabled", false).text(blazeBlocksyMiniCart.apply_coupon);
      },
    });
  });

  /**
   * Show coupon message
   */
  function showCouponMessage(message, type) {
    var messageClass = type === "success" ? "coupon-success" : "coupon-error";
    var $message = $(
      '<div class="coupon-message ' + messageClass + '">' + message + "</div>"
    );

    $(".mini-cart-coupon-section").append($message);

    // Auto-hide success messages after 3 seconds
    if (type === "success") {
      setTimeout(function () {
        $message.fadeOut(300, function () {
          $(this).remove();
        });
      }, 3000);
    }
  }

  /**
   * Handle mini cart updates
   */
  $(document.body).on("wc_fragments_refreshed", function () {
    // Update heading count when cart is refreshed
    updateCartPanelHeadingCount();

    // Reset shipping results on cart update (totals may have changed)
    $(".shipping-results").hide().find(".shipping-methods-list").empty();
  });

  /**
   * Handle recommended product clicks
   */
  $(document).on(
    "click",
    ".recommended-product-item .product-link",
    function (e) {
      // Allow normal navigation - no special handling needed
      // This is just a placeholder for future enhancements
    }
  );

  /**
   * Add smooth animations for mini cart interactions
   */
  $(document)
    .on("mouseenter", ".recommended-product-item", function () {
      $(this).addClass("hover-effect");
    })
    .on("mouseleave", ".recommended-product-item", function () {
      $(this).removeClass("hover-effect");
    });
});
