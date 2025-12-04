/**
 * Product Information - Customizer Live Preview
 *
 * Handles instant live preview updates for Product Information element
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

  const selector = ".ct-product-information";

  // Typography sync
  wp.customize("productInformationFont", function (value) {
    value.bind(function (newValue) {
      if (newValue.family && newValue.family !== "Default") {
        updateCSSVariable(selector, "--theme-font-family", newValue.family);
      }

      if (newValue.size) {
        const size =
          typeof newValue.size === "object"
            ? newValue.size.desktop
            : newValue.size;
        updateCSSVariable(selector, "--theme-font-size", size);
      }

      if (newValue.variation) {
        const weight = newValue.variation.replace(/[a-z]/g, "") + "00";
        const style = newValue.variation.startsWith("i") ? "italic" : "normal";
        updateCSSVariable(
          selector,
          "--theme-font-weight",
          weight === "00" ? "400" : weight
        );
        updateCSSVariable(selector, "--theme-font-style", style);
      }

      if (newValue["line-height"]) {
        updateCSSVariable(
          selector,
          "--theme-line-height",
          newValue["line-height"]
        );
      }

      if (newValue["letter-spacing"]) {
        updateCSSVariable(
          selector,
          "--theme-letter-spacing",
          newValue["letter-spacing"]
        );
      }

      if (newValue["text-transform"]) {
        updateCSSVariable(
          selector,
          "--theme-text-transform",
          newValue["text-transform"]
        );
      }

      if (newValue["text-decoration"]) {
        updateCSSVariable(
          selector,
          "--theme-text-decoration",
          newValue["text-decoration"]
        );
      }
    });
  });

  // Text Color sync
  wp.customize("productInformationTextColor", function (value) {
    value.bind(function (newValue) {
      const color = getColorValue(newValue);
      if (color) {
        updateCSSVariable(selector, "--product-information-text-color", color);
      }
    });
  });

  // Border Width sync
  wp.customize("productInformationBorderWidth", function (value) {
    value.bind(function (newValue) {
      updateCSSVariable(
        selector,
        "--product-information-border-width",
        newValue + "px"
      );
    });
  });

  // Border Style sync
  wp.customize("productInformationBorderStyle", function (value) {
    value.bind(function (newValue) {
      updateCSSVariable(
        selector,
        "--product-information-border-style",
        newValue
      );
    });
  });

  // Border Color sync
  wp.customize("productInformationBorderColor", function (value) {
    value.bind(function (newValue) {
      const color = getColorValue(newValue);
      if (color) {
        updateCSSVariable(
          selector,
          "--product-information-border-color",
          color
        );
      }
    });
  });

  // Separator Width sync
  wp.customize("productInformationSeparatorWidth", function (value) {
    value.bind(function (newValue) {
      updateCSSVariable(
        selector,
        "--product-information-separator-width",
        newValue + "px"
      );
    });
  });

  // Separator Style sync
  wp.customize("productInformationSeparatorStyle", function (value) {
    value.bind(function (newValue) {
      updateCSSVariable(
        selector,
        "--product-information-separator-style",
        newValue
      );
    });
  });

  // Separator Color sync
  wp.customize("productInformationSeparatorColor", function (value) {
    value.bind(function (newValue) {
      const color = getColorValue(newValue);
      if (color) {
        updateCSSVariable(
          selector,
          "--product-information-separator-color",
          color
        );
      }
    });
  });

  // Vertical Padding sync
  wp.customize("productInformationVerticalPadding", function (value) {
    value.bind(function (newValue) {
      updateCSSVariable(
        selector,
        "--product-information-padding",
        newValue + "px"
      );
    });
  });

  // Item Horizontal Padding sync
  wp.customize("productInformationItemHorizontalPadding", function (value) {
    value.bind(function (newValue) {
      updateCSSVariable(
        selector,
        "--product-information-item-horizontal-padding",
        newValue + "px"
      );
    });
  });

  // Gap Inside Item sync
  wp.customize("productInformationGapInside", function (value) {
    value.bind(function (newValue) {
      updateCSSVariable(
        selector,
        "--product-information-gap-inside",
        newValue + "px"
      );
    });
  });

  // Justify Content sync
  wp.customize("productInformationJustifyContent", function (value) {
    value.bind(function (newValue) {
      updateCSSVariable(
        selector,
        "--product-information-justify-content",
        newValue
      );
    });
  });

  // Text Underline sync
  wp.customize("productInformationTextUnderline", function (value) {
    value.bind(function (newValue) {
      const underlineValue = newValue === "yes" ? "underline" : "none";
      updateCSSVariable(
        selector,
        "--product-information-text-underline",
        underlineValue
      );
    });
  });

  // Bottom Spacing sync via woo_single_layout
  wp.customize("woo_single_layout", function (value) {
    value.bind(function (newValue) {
      if (!Array.isArray(newValue)) return;

      const layer = newValue.find(function (l) {
        return l.id === "product_information";
      });

      if (layer && layer.spacing !== undefined) {
        const spacing =
          typeof layer.spacing === "object"
            ? layer.spacing.desktop
            : layer.spacing;
        updateCSSVariable(
          ".entry-summary-items > .ct-product-information",
          "--product-element-spacing",
          spacing + "px"
        );
      }
    });
  });
})(jQuery);
