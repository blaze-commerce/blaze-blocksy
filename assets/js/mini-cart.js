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

        // Init selectWoo when panel becomes visible (not inert)
        var panel = mutation.target;
        if (!panel.hasAttribute("inert")) {
          initShippingSelectWoo();
        }
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
   * Toggle mini cart form sections
   * Using both click and touchend for iOS compatibility
   */
  $(document).on("click touchend", ".mini-cart-toggle", function (e) {
    e.preventDefault();
    e.stopPropagation();

    // Prevent double-firing on devices that trigger both touch and click
    if (e.type === "touchend") {
      $(this).data("touchFired", true);
      return void handleToggle($(this));
    }
    if ($(this).data("touchFired")) {
      $(this).removeData("touchFired");
      return;
    }
    handleToggle($(this));
  });

  function handleToggle($toggle) {
    var $wrapper = $toggle.siblings(".mini-cart-form-wrapper");
    var $arrow = $toggle.find(".mini-cart-arrow");
    var $section = $toggle.closest(".mini-cart-form-section");
    var isOpening = $wrapper.is(":hidden");

    // Accordion: close other sections when opening this one
    if (isOpening) {
      $(".mini-cart-form-section").not($section).each(function () {
        var $otherWrapper = $(this).find(".mini-cart-form-wrapper");
        var $otherArrow = $(this).find(".mini-cart-arrow");
        if ($otherWrapper.is(":visible")) {
          $otherWrapper.slideUp(300);
          $otherArrow.text("▼");
        }
      });
    }

    $wrapper.slideToggle(300);

    // Toggle arrow direction
    if ($arrow.text() === "▼") {
      $arrow.text("▲");
    } else {
      $arrow.text("▼");
    }
  }

  /**
   * Shipping Calculator in Mini Cart
   */
  var selectWooAvailable = typeof $.fn.selectWoo !== "undefined";

  function initMiniCartSelectWoo($el) {
    if (!selectWooAvailable) return;
    if ($el.data("select2")) {
      $el.selectWoo("destroy");
    }
    $el.selectWoo({
      width: "100%",
      placeholder: $el.find("option:first").text(),
      dropdownParent: $("body"),
    });
  }

  function initShippingSelectWoo() {
    var $country = $("#mini-cart-shipping-country");
    if (!$country.length) return;
    initMiniCartSelectWoo($country);
    initMiniCartSelectWoo($("#mini-cart-shipping-state"));

    // Restore saved country → triggers change → loads states → restores state
    var savedCountry = localStorage.getItem("blaze_shipping_country");
    if (
      savedCountry &&
      $country.find('option[value="' + savedCountry + '"]').length
    ) {
      $country.val(savedCountry).trigger("change");
    } else {
      // Auto-select if only one shipping country is available
      var $countryOptions = $country.find('option[value!=""]');
      if ($countryOptions.length === 1) {
        $country.val($countryOptions.first().val()).trigger("change");
      }
    }

    // Restore saved postcode
    var savedPostcode = localStorage.getItem("blaze_shipping_postcode");
    if (savedPostcode) {
      $("#mini-cart-shipping-postcode").val(savedPostcode);
    }
  }

  // Save state to localStorage on change
  $(document).on("change", "#mini-cart-shipping-state", function () {
    var state = $(this).val();
    if (state) {
      localStorage.setItem("blaze_shipping_state", state);
    } else {
      localStorage.removeItem("blaze_shipping_state");
    }
  });

  // Save postcode to localStorage on change
  $(document).on("input", "#mini-cart-shipping-postcode", function () {
    var postcode = $(this).val();
    if (postcode) {
      localStorage.setItem("blaze_shipping_postcode", postcode);
    } else {
      localStorage.removeItem("blaze_shipping_postcode");
    }
  });

  // Country change → load states
  $(document).on("change", "#mini-cart-shipping-country", function () {
    var country = $(this).val();
    var $state = $("#mini-cart-shipping-state");

    $state.empty().append('<option value="">Select State</option>');

    if (!country) {
      localStorage.removeItem("blaze_shipping_country");
      localStorage.removeItem("blaze_shipping_state");
      localStorage.removeItem("blaze_shipping_postcode");
      initMiniCartSelectWoo($state);
      return;
    }

    localStorage.setItem("blaze_shipping_country", country);

    $.ajax({
      url: blazeBlocksyMiniCart.ajax_url,
      type: "POST",
      data: {
        action: "blaze_blocksy_get_states",
        country_code: country,
      },
      success: function (response) {
        $state.empty().append('<option value="">Select State</option>');
        if (response.success && response.data) {
          var states = response.data;
          if (Object.keys(states).length > 0) {
            $.each(states, function (key, value) {
              $state.append(
                '<option value="' + key + '">' + value + "</option>",
              );
            });
          }
        }
        initMiniCartSelectWoo($state);

        // Restore saved state if available
        var savedState = localStorage.getItem("blaze_shipping_state");
        if (
          savedState &&
          $state.find('option[value="' + savedState + '"]').length
        ) {
          $state.val(savedState).trigger("change.select2");
        }
      },
      error: function () {
        $state.empty().append('<option value="">Error loading states</option>');
        initMiniCartSelectWoo($state);
      },
    });
  });

  // Calculate shipping button
  $(document).on("click", ".mini-cart-calculate-shipping-btn", function (e) {
    e.preventDefault();

    var country = $("#mini-cart-shipping-country").val();
    var state = $("#mini-cart-shipping-state").val();
    var postcode = $("#mini-cart-shipping-postcode").val();
    var $button = $(this);
    var $results = $(".mini-cart-shipping-results");
    var $methodsList = $(".mini-cart-shipping-methods-list");

    if (!country || !state) {
      alert("Please select a country and state/province");
      return;
    }

    $button.prop("disabled", true).text("CALCULATING...");
    $results.hide();
    $methodsList.empty();

    $.ajax({
      url: blazeBlocksyMiniCart.ajax_url,
      type: "POST",
      data: {
        action: "calculate_cart_shipping",
        country: country,
        state: state,
        postcode: postcode || "",
        nonce: blazeBlocksyMiniCart.nonce,
      },
      success: function (response) {
        if (response.success && response.data) {
          var methods = response.data;
          if (!methods.length) {
            $methodsList.html(
              '<div class="no-shipping">No shipping methods available for this location.</div>',
            );
          } else {
            var html = '<div class="shipping-methods">';
            $.each(methods, function (i, method) {
              var checked = i === 0 ? " checked" : "";
              html += '<label class="shipping-method-radio">';
              html +=
                '<input type="radio" name="mini_cart_shipping_method" value="' +
                method.id +
                '" data-raw-cost="' +
                method.raw_cost +
                '"' +
                checked +
                ">";
              html += '<span class="method-title">' + method.title + "</span>";
              html += '<span class="method-cost">' + method.cost + "</span>";
              html += "</label>";
            });
            html += "</div>";
            $methodsList.html(html);

            // Auto-select first shipping method
            $methodsList
              .find('input[name="mini_cart_shipping_method"]:first')
              .trigger("change");
          }
          $results.show();
        } else {
          var msg =
            response.data && response.data.message
              ? response.data.message
              : "Unable to calculate shipping";
          $methodsList.html('<div class="shipping-error">' + msg + "</div>");
          $results.show();
        }
      },
      error: function () {
        $methodsList.html(
          '<div class="shipping-error">Error calculating shipping. Please try again.</div>',
        );
        $results.show();
      },
      complete: function () {
        $button.prop("disabled", false).text("Calculate Shipping");
      },
    });
  });

  /**
   * Handle shipping method selection in mini cart
   */
  $(document).on(
    "change",
    'input[name="mini_cart_shipping_method"]',
    function () {
      var methodId = $(this).val();
      var $breakdown = $(".mini-cart-totals-breakdown");

      // Persist selected shipping method to localStorage
      localStorage.setItem("blaze_shipping_method", methodId);

      // Show shipping line
      $breakdown.find(".shipping-line").show();

      $.ajax({
        url: blazeBlocksyMiniCart.ajax_url,
        type: "POST",
        data: {
          action: "select_mini_cart_shipping_method",
          method_id: methodId,
          nonce: blazeBlocksyMiniCart.nonce,
        },
        success: function (response) {
          if (response.success && response.data) {
            var totals = response.data;
            $breakdown
              .find(".subtotal-line .total-amount")
              .html(totals.subtotal);
            $breakdown
              .find(".shipping-line .total-amount")
              .html(totals.shipping);
            $breakdown.find(".tax-line .total-amount").html(totals.tax);

            if (totals.discount_raw > 0) {
              var $couponLine = $breakdown.find(".coupon-line");
              if ($couponLine.length) {
                $couponLine.find(".total-amount").html("-" + totals.discount);
                $couponLine.show();
              } else {
                $(
                  '<div class="total-line coupon-line">' +
                    '<span class="total-label">Coupon</span>' +
                    '<span class="total-amount">-' +
                    totals.discount +
                    "</span></div>",
                ).insertBefore($breakdown.find(".grand-total-line"));
              }
            }

            $breakdown
              .find(".grand-total-line .total-amount")
              .html(totals.total);
          }
        },
      });
    },
  );

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
      '<div class="coupon-message ' + messageClass + '">' + message + "</div>",
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
   * Auto-recalculate shipping after cart fragment refresh
   * If a shipping method was previously selected, recalculate and re-select it
   */
  function autoRecalculateShipping() {
    var savedMethod = localStorage.getItem("blaze_shipping_method");
    if (!savedMethod) return;

    var country = $("#mini-cart-shipping-country").val();
    var state = $("#mini-cart-shipping-state").val();
    var postcode = $("#mini-cart-shipping-postcode").val();

    if (!country || !state) return;

    var $results = $(".mini-cart-shipping-results");
    var $methodsList = $(".mini-cart-shipping-methods-list");

    $.ajax({
      url: blazeBlocksyMiniCart.ajax_url,
      type: "POST",
      data: {
        action: "calculate_cart_shipping",
        country: country,
        state: state,
        postcode: postcode || "",
        nonce: blazeBlocksyMiniCart.nonce,
      },
      success: function (response) {
        if (response.success && response.data) {
          var methods = response.data;
          if (!methods.length) {
            $methodsList.html(
              '<div class="no-shipping">No shipping methods available for this location.</div>',
            );
          } else {
            var html = '<div class="shipping-methods">';
            $.each(methods, function (i, method) {
              var checked = method.id === savedMethod ? " checked" : "";
              html += '<label class="shipping-method-radio">';
              html +=
                '<input type="radio" name="mini_cart_shipping_method" value="' +
                method.id +
                '" data-raw-cost="' +
                method.raw_cost +
                '"' +
                checked +
                ">";
              html += '<span class="method-title">' + method.title + "</span>";
              html += '<span class="method-cost">' + method.cost + "</span>";
              html += "</label>";
            });
            html += "</div>";
            $methodsList.html(html);

            // Select saved method, or fallback to first if saved method no longer available
            var $savedRadio = $methodsList.find(
              'input[name="mini_cart_shipping_method"][value="' +
                savedMethod +
                '"]',
            );
            if ($savedRadio.length) {
              $savedRadio.prop("checked", true).trigger("change");
            } else {
              $methodsList
                .find('input[name="mini_cart_shipping_method"]:first')
                .prop("checked", true)
                .trigger("change");
            }
          }
          $results.show();
        }
      },
    });
  }

  /**
   * Handle mini cart updates
   */
  $(document.body).on("wc_fragments_refreshed", function () {
    // Re-init selectWoo only if panel is open (DOM was replaced by fragments)
    var panel = document.getElementById("woo-cart-panel");
    if (panel && !panel.hasAttribute("inert")) {
      initShippingSelectWoo();

      // Auto-recalculate shipping if a method was previously selected
      // Wait for initShippingSelectWoo to finish restoring country/state via AJAX
      var savedMethod = localStorage.getItem("blaze_shipping_method");
      if (savedMethod) {
        // Use a polling approach to wait for state select to be restored
        var attempts = 0;
        var maxAttempts = 20;
        var checkInterval = setInterval(function () {
          attempts++;
          var country = $("#mini-cart-shipping-country").val();
          var state = $("#mini-cart-shipping-state").val();

          if ((country && state) || attempts >= maxAttempts) {
            clearInterval(checkInterval);
            if (country && state) {
              autoRecalculateShipping();
            }
          }
        }, 200);
      }
    }

    // Update heading count when cart is refreshed
    updateCartPanelHeadingCount();
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
    },
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
