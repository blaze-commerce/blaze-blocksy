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
    var $arrow = $(this).find(".coupon-arrow");

    $wrapper.slideToggle(300);

    // Toggle arrow direction
    if ($arrow.text() === "▼") {
      $arrow.text("▲");
    } else {
      $arrow.text("▼");
    }
  });

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
    // Re-initialize any dynamic elements if needed
    console.log("Mini cart fragments refreshed");

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
