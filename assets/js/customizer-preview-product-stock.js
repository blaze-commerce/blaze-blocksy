/**
 * Product Stock - Customizer Live Preview
 *
 * Handles instant live preview updates for Product Stock element
 * by directly manipulating CSS variables without page refresh.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  // Helper function to update CSS variable on element
  function updateCSSVariable(selector, variable, value) {
    const elements = document.querySelectorAll(selector);
    elements.forEach(function (el) {
      el.style.setProperty(variable, value);
    });
  }

  // Helper function to extract color value from Blocksy color object
  function getColorValue(colorObj) {
    if (!colorObj) return null;
    if (typeof colorObj === "string") return colorObj;
    if (colorObj.default && colorObj.default.color)
      return colorObj.default.color;
    if (colorObj.color) return colorObj.color;
    return null;
  }

  // Typography sync
  wp.customize("productStockFont", function (value) {
    value.bind(function (newValue) {
      const el = ".ct-product-stock-element";

      if (newValue.family && newValue.family !== "Default") {
        updateCSSVariable(el, "--theme-font-family", newValue.family);
      }

      if (newValue.size) {
        const size =
          typeof newValue.size === "object"
            ? newValue.size.desktop
            : newValue.size;
        updateCSSVariable(el, "--theme-font-size", size);
      }

      if (newValue.variation) {
        // Parse variation like 'n4', 'i7', etc.
        const weight = newValue.variation.replace(/[a-z]/g, "") + "00";
        const style = newValue.variation.startsWith("i") ? "italic" : "normal";
        updateCSSVariable(
          el,
          "--theme-font-weight",
          weight === "00" ? "400" : weight
        );
        updateCSSVariable(el, "--theme-font-style", style);
      }

      if (newValue["line-height"]) {
        updateCSSVariable(el, "--theme-line-height", newValue["line-height"]);
      }

      if (newValue["letter-spacing"]) {
        updateCSSVariable(
          el,
          "--theme-letter-spacing",
          newValue["letter-spacing"]
        );
      }

      if (newValue["text-transform"]) {
        updateCSSVariable(
          el,
          "--theme-text-transform",
          newValue["text-transform"]
        );
      }

      if (newValue["text-decoration"]) {
        updateCSSVariable(
          el,
          "--theme-text-decoration",
          newValue["text-decoration"]
        );
      }
    });
  });

  // In Stock Color sync
  wp.customize("productStockInStockColor", function (value) {
    value.bind(function (newValue) {
      const color = getColorValue(newValue);
      if (color) {
        updateCSSVariable(
          ".ct-product-stock-element.ct-product-stock-in-stock",
          "--color",
          color
        );
      }
    });
  });

  // Out of Stock Color sync
  wp.customize("productStockOutOfStockColor", function (value) {
    value.bind(function (newValue) {
      const color = getColorValue(newValue);
      if (color) {
        updateCSSVariable(
          ".ct-product-stock-element.ct-product-stock-out-of-stock",
          "--color",
          color
        );
      }
    });
  });

  // On Backorder Color sync
  wp.customize("productStockOnBackorderColor", function (value) {
    value.bind(function (newValue) {
      const color = getColorValue(newValue);
      if (color) {
        updateCSSVariable(
          ".ct-product-stock-element.ct-product-stock-on-backorder",
          "--color",
          color
        );
      }
    });
  });

  // Bottom Spacing sync via woo_single_layout
  wp.customize("woo_single_layout", function (value) {
    value.bind(function (newValue) {
      if (!Array.isArray(newValue)) return;

      const layer = newValue.find(function (l) {
        return l.id === "product_stock_element";
      });

      if (layer && layer.spacing !== undefined) {
        const spacing =
          typeof layer.spacing === "object"
            ? layer.spacing.desktop
            : layer.spacing;
        updateCSSVariable(
          ".entry-summary-items > .ct-product-stock-element",
          "--product-element-spacing",
          spacing + "px"
        );
      }
    });
  });
})(jQuery);
