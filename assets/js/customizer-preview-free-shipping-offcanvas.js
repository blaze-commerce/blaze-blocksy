/**
 * Customizer Live Preview for Free Shipping OffCanvas Content
 *
 * Handles instant live preview updates for the Free Shipping OffCanvas customizer settings.
 * Uses CSS custom properties for proper synchronization with PHP-generated CSS.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  /**
   * Helper function to set CSS custom property on element
   *
   * @param {jQuery} $element jQuery element
   * @param {string} property CSS custom property name (without --)
   * @param {string} value Value to set
   */
  function setCssVariable($element, property, value) {
    if ($element.length && value) {
      $element[0].style.setProperty("--" + property, value);
    }
  }

  /**
   * Helper function to convert spacing object to CSS value
   * Handles both old format (top/right/bottom/left) and new Blocksy format (state/values)
   *
   * @param {object|string} spacing Spacing value from customizer
   * @return {string} CSS value string
   */
  function getSpacingCssValue(spacing) {
    if (typeof spacing === "string") {
      return spacing;
    }

    if (typeof spacing !== "object" || spacing === null) {
      return "0px";
    }

    // Handle responsive array (desktop/tablet/mobile)
    if (spacing.desktop) {
      spacing = spacing.desktop;
    }

    var top, right, bottom, left;

    // Handle new Blocksy format with state and values array
    if (spacing.values && Array.isArray(spacing.values)) {
      // values array: [top, right, bottom, left]
      // Each value can be: { value: number, unit: string } or string
      top = formatSpacingValue(spacing.values[0]);
      right = formatSpacingValue(spacing.values[1]);
      bottom = formatSpacingValue(spacing.values[2]);
      left = formatSpacingValue(spacing.values[3]);
    } else {
      // Handle old format with top/right/bottom/left directly
      top = spacing.top || "0px";
      right = spacing.right || "0px";
      bottom = spacing.bottom || "0px";
      left = spacing.left || "0px";
    }

    // If all values are the same, return single value
    if (top === right && right === bottom && bottom === left) {
      return top;
    }

    // If top/bottom same and left/right same
    if (top === bottom && left === right) {
      return top + " " + right;
    }

    return top + " " + right + " " + bottom + " " + left;
  }

  /**
   * Helper function to format individual spacing value
   * Handles both { value: X, unit: 'px' } and string formats
   *
   * @param {object|string} val Spacing value
   * @return {string} CSS value string
   */
  function formatSpacingValue(val) {
    if (typeof val === "string") {
      return val || "0px";
    }

    if (typeof val === "object" && val !== null) {
      var value = val.value !== undefined ? val.value : 0;
      var unit = val.unit || "px";

      if (value === "" || value === null || value === undefined) {
        return "0px";
      }

      if (value === "auto") {
        return "auto";
      }

      return value + unit;
    }

    return "0px";
  }

  // Wait for customizer preview to be ready
  wp.customize.bind("preview-ready", function () {
    /**
     * Toggle visibility of the free shipping content element
     */
    wp.customize("free_shipping_offcanvas_enabled", function (value) {
      value.bind(function (newval) {
        var $element = $(".ct-free-shipping-offcanvas-content");

        if (newval === "yes") {
          if ($element.length === 0) {
            // Element doesn't exist, trigger a refresh to create it
            wp.customize.preview.send("refresh");
          } else {
            $element.show();
          }
        } else {
          $element.hide();
        }
      });
    });

    /**
     * Update content text
     */
    wp.customize("free_shipping_offcanvas_content", function (value) {
      value.bind(function (newval) {
        var $element = $(".ct-free-shipping-offcanvas-content");

        if ($element.length) {
          $element.html(newval);
        }
      });
    });

    /**
     * Update font color using CSS custom property
     */
    wp.customize("free_shipping_offcanvas_font_color", function (value) {
      value.bind(function (newval) {
        var $element = $(".ct-free-shipping-offcanvas-content");

        if (
          $element.length &&
          newval &&
          newval.default &&
          newval.default.color
        ) {
          var color = newval.default.color;

          // Skip if using default keyword
          if (color.indexOf("CT_CSS_SKIP_RULE") === -1) {
            setCssVariable($element, "color", color);
          }
        }
      });
    });

    /**
     * Update background
     */
    wp.customize("free_shipping_offcanvas_background", function (value) {
      value.bind(function (newval) {
        var $element = $(".ct-free-shipping-offcanvas-content");

        if ($element.length && newval) {
          // Handle backgroundColor object
          if (newval.backgroundColor && newval.backgroundColor.default) {
            var bgColor = newval.backgroundColor.default.color;
            if (bgColor && bgColor.indexOf("CT_CSS_SKIP_RULE") === -1) {
              $element.css("background-color", bgColor);
            }
          }

          // Handle gradient if set
          if (newval.background_type === "gradient" && newval.gradient) {
            $element.css("background", newval.gradient);
          }
        }
      });
    });

    /**
     * Update border directly on element
     */
    wp.customize("free_shipping_offcanvas_border", function (value) {
      value.bind(function (newval) {
        var $element = $(".ct-free-shipping-offcanvas-content");

        if ($element.length && newval) {
          var width = (newval.width || 1) + "px";
          var style = newval.style || "solid";
          var color = "rgba(0, 0, 0, 0.1)";

          if (newval.color && newval.color.color) {
            color = newval.color.color;
          }

          // Apply border directly as CSS property
          $element.css("border", width + " " + style + " " + color);
        }
      });
    });

    /**
     * Update border radius directly on element
     */
    wp.customize("free_shipping_offcanvas_border_radius", function (value) {
      value.bind(function (newval) {
        var $element = $(".ct-free-shipping-offcanvas-content");

        if ($element.length && newval) {
          var radius = getSpacingCssValue(newval);
          // Apply border-radius directly as CSS property
          $element.css("border-radius", radius);
        }
      });
    });

    /**
     * Update padding directly on element
     */
    wp.customize("free_shipping_offcanvas_padding", function (value) {
      value.bind(function (newval) {
        var $element = $(".ct-free-shipping-offcanvas-content");

        if ($element.length && newval) {
          var padding = getSpacingCssValue(newval);
          // Apply padding directly as CSS property
          $element.css("padding", padding);
        }
      });
    });

    /**
     * Update font settings
     */
    wp.customize("free_shipping_offcanvas_font", function (value) {
      value.bind(function (newval) {
        var $element = $(".ct-free-shipping-offcanvas-content");

        if ($element.length && newval) {
          // Apply font family
          if (newval.family) {
            setCssVariable($element, "theme-font-family", newval.family);
          }

          // Apply font size
          if (newval.size) {
            setCssVariable($element, "theme-font-size", newval.size);
          }

          // Apply line height
          if (newval["line-height"]) {
            setCssVariable(
              $element,
              "theme-line-height",
              newval["line-height"]
            );
          }

          // Apply font weight and style (from variation)
          if (newval.variation) {
            // Blocksy uses 'n4' format where n=style (n=normal, i=italic), 4=weight (100-900)
            var weight = parseInt(newval.variation.charAt(1)) * 100;
            var style =
              newval.variation.charAt(0) === "i" ? "italic" : "normal";

            setCssVariable($element, "theme-font-weight", weight);
            setCssVariable($element, "theme-font-style", style);
          }

          // Apply letter spacing
          if (newval["letter-spacing"]) {
            setCssVariable(
              $element,
              "theme-letter-spacing",
              newval["letter-spacing"]
            );
          }

          // Apply text transform
          if (newval["text-transform"]) {
            setCssVariable(
              $element,
              "theme-text-transform",
              newval["text-transform"]
            );
          }

          // Apply text decoration
          if (newval["text-decoration"]) {
            setCssVariable(
              $element,
              "theme-text-decoration",
              newval["text-decoration"]
            );
          }
        }
      });
    });
  });
})(jQuery);
