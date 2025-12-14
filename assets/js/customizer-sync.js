/**
 * Customizer Live Preview Sync for Mini Cart Options
 * This file handles real-time preview updates for custom mini cart settings
 */

(function ($, wp) {
  "use strict";

  // Check if we're in the customizer preview
  if (typeof wp === "undefined" || typeof wp.customize === "undefined") {
    return;
  }

  /**
   * Helper function to update CSS property for a selector
   */
  function updateCssProperty(selector, property, value) {
    var styleId = "blaze-mini-cart-sync-" + property.replace(/[^a-z0-9]/gi, "-");
    var $style = $("#" + styleId);

    if (!$style.length) {
      $style = $('<style id="' + styleId + '"></style>');
      $("head").append($style);
    }

    $style.html(selector + " { " + property + ": " + value + " !important; }");
  }

  /**
   * Helper function to update typography properties
   */
  function updateTypography(selector, fontValue) {
    if (!fontValue || typeof fontValue !== "object") {
      return;
    }

    var styleId = "blaze-mini-cart-sync-typography-" + selector.replace(/[^a-z0-9]/gi, "-");
    var $style = $("#" + styleId);

    if (!$style.length) {
      $style = $('<style id="' + styleId + '"></style>');
      $("head").append($style);
    }

    var css = selector + " { ";

    if (fontValue["font-family"] && fontValue["font-family"] !== "Default") {
      css += "font-family: " + fontValue["font-family"] + "; ";
    }

    if (fontValue["font-size"]) {
      css += "font-size: " + fontValue["font-size"] + "; ";
    } else if (fontValue["size"]) {
      css += "font-size: " + fontValue["size"] + "; ";
    }

    if (fontValue["font-weight"]) {
      css += "font-weight: " + fontValue["font-weight"] + "; ";
    } else if (fontValue["variation"]) {
      // Parse Blocksy variation format (e.g., "n5" = normal 500)
      var variation = fontValue["variation"];
      var weight = variation.replace(/[^0-9]/g, "") + "00";
      if (weight) {
        css += "font-weight: " + weight + "; ";
      }
    }

    if (fontValue["line-height"]) {
      css += "line-height: " + fontValue["line-height"] + "; ";
    }

    if (fontValue["letter-spacing"]) {
      css += "letter-spacing: " + fontValue["letter-spacing"] + "; ";
    }

    if (fontValue["text-transform"]) {
      css += "text-transform: " + fontValue["text-transform"] + "; ";
    }

    css += "}";

    $style.html(css);
  }

  /**
   * Product Title Font
   */
  wp.customize("header_item_cart_mini_cart_product_title_font", function (value) {
    value.bind(function (newVal) {
      updateTypography("#woo-cart-panel .mini_cart_item .product-info a", newVal);
    });
  });

  /**
   * Product Price Font
   */
  wp.customize("header_item_cart_mini_cart_product_price_font", function (value) {
    value.bind(function (newVal) {
      updateTypography(
        ".woocommerce-mini-cart-item.mini_cart_item .product-price-quantity .product-price .woocommerce-Price-amount",
        newVal
      );
    });
  });

  /**
   * Product Price Color
   */
  wp.customize("header_item_cart_mini_cart_product_price_color", function (value) {
    value.bind(function (newVal) {
      if (newVal && newVal["default"] && newVal["default"]["color"]) {
        updateCssProperty(
          ".woocommerce-mini-cart-item.mini_cart_item .product-price-quantity .product-price .woocommerce-Price-amount",
          "color",
          newVal["default"]["color"]
        );
      }
    });
  });

  /**
   * Subtotal Amount Font
   */
  wp.customize("header_item_cart_mini_cart_subtotal_font", function (value) {
    value.bind(function (newVal) {
      updateTypography(
        ".woocommerce-mini-cart-item.mini_cart_item .product-subtotal .subtotal-amount .woocommerce-Price-amount",
        newVal
      );
    });
  });

  /**
   * Subtotal Amount Color
   */
  wp.customize("header_item_cart_mini_cart_subtotal_color", function (value) {
    value.bind(function (newVal) {
      if (newVal && newVal["default"] && newVal["default"]["color"]) {
        updateCssProperty(
          ".woocommerce-mini-cart-item.mini_cart_item .product-subtotal .subtotal-amount .woocommerce-Price-amount",
          "color",
          newVal["default"]["color"]
        );
      }
    });
  });
})(jQuery, wp);

