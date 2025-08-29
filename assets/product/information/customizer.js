/**
 * Product Information Element - Customizer Live Preview
 * Enhanced JavaScript untuk real-time preview updates
 */

(function ($) {
  "use strict";

  // Initialize ketika document ready
  $(document).ready(function () {
    initCustomizerPreview();
    preventUnwantedRefresh();
  });

  // Listen untuk perubahan pada woo_single_layout dengan error handling
  if (typeof wp !== "undefined" && wp.customize) {
    wp.customize("woo_single_layout", function (value) {
      value.bind(function (newval) {
        console.log("Layout changed:", newval);
        // Track the change time
        window.lastProductInfoChange = Date.now();
        updateProductInformationPreview(newval);
      });
    });
  } else {
    console.warn("wp.customize not available for product information");
  }

  // Listen untuk refresh events
  $(window).on("load", function () {
    // Re-initialize setelah page load
    setTimeout(function () {
      initCustomizerPreview();
    }, 100);
  });

  // Listen untuk customizer refresh - hanya untuk initial load
  if (typeof wp !== "undefined" && wp.customize && wp.customize.preview) {
    wp.customize.preview.bind("active", function () {
      // Hanya ensure CSS loaded, tidak refresh
      ensureBaseCSSLoaded();
    });
  }

  /**
   * Update preview untuk Product Information element
   * @param {Array} layout - Layout configuration array
   */
  function updateProductInformationPreview(layout) {
    if (!layout || !Array.isArray(layout)) {
      return;
    }

    layout.forEach(function (layer) {
      if (layer.id === "product_information" && layer.enabled) {
        // HANYA update CSS variables - TIDAK refresh preview
        updateProductInformationStyles(layer);
      }
    });
  }

  /**
   * Update styles untuk Product Information element
   * @param {Object} layer - Layer configuration object
   */
  function updateProductInformationStyles(layer) {
    const element = document.querySelector(".ct-product-information");
    if (!element) {
      console.warn("Product information element not found");
      return;
    }

    console.log("Updating product information styles for layer:", layer);

    // Get values dari layer configuration
    const borderWidth = layer["ct-information-border_width"] || 1;
    const borderColor = getColor(
      layer,
      "ct-information-border_color",
      "#e9ecef"
    );
    const padding = layer["ct-information-padding"] || 20;
    const justifyContent = layer["ct-information-justify_content"] || "center";
    const gapInside = layer["ct-information-gap_inside"] || 20;
    const itemHorizontalPadding =
      layer["ct-information-item_horizontal_padding"] || 20;
    const textColor = getColor(layer, "ct-text-color", "#333333");
    const fontSize = layer["ct-information-font-size"] || 14;
    const textUnderline =
      layer["ct-information-text-underline"] === "yes" ? "underline" : "none";

    // Update CSS variables untuk real-time changes
    updateCSSVariable("--product-information-border-width", borderWidth + "px");
    updateCSSVariable("--product-information-border-color", borderColor);
    updateCSSVariable("--product-information-padding", padding + "px");
    updateCSSVariable("--product-information-justify-content", justifyContent);
    updateCSSVariable("--product-information-gap-inside", gapInside + "px");
    updateCSSVariable(
      "--product-information-item-horizontal-padding",
      itemHorizontalPadding + "px"
    );
    updateCSSVariable("--product-information-text-color", textColor);
    updateCSSVariable("--product-information-font-size", fontSize + "px");
    updateCSSVariable("--product-information-text-underline", textUnderline);

    // Handle separator class
    var addSeparator = layer["ct-add-separator"] === "yes";
    var productInfoElement = document.querySelector(".ct-product-information");
    if (productInfoElement) {
      if (addSeparator) {
        productInfoElement.classList.add("has-separator");
      } else {
        productInfoElement.classList.remove("has-separator");
      }
    }
  }

  function getColor(layer, field, defaultColor) {
    if (layer[field] && layer[field].default && layer[field].default.color) {
      return layer[field].default.color;
    }
    return defaultColor;
  }

  /**
   * Update CSS variable di document root
   * @param {string} property - CSS variable name
   * @param {string} value - CSS variable value
   */
  function updateCSSVariable(property, value) {
    document.documentElement.style.setProperty(property, value);
  }

  /**
   * Initialize customizer preview
   */
  function initCustomizerPreview() {
    // Ensure base CSS is loaded SEKALI saja
    ensureBaseCSSLoaded();

    // Set initial CSS variables dari current settings
    if (typeof wp !== "undefined" && wp.customize) {
      var currentLayout = wp.customize("woo_single_layout").get();
      if (currentLayout && Array.isArray(currentLayout)) {
        currentLayout.forEach(function (layer) {
          if (layer.id === "product_information" && layer.enabled) {
            updateProductInformationStyles(layer);
          }
        });
      }
    }

    console.log("Product Information Customizer Preview initialized");
  }

  /**
   * Ensure base CSS untuk Product Information ter-load
   */
  function ensureBaseCSSLoaded() {
    // Check jika CSS link sudah ada
    var existingLink = document.getElementById("product-information-css-link");
    if (existingLink) {
      return;
    }

    // Load CSS file dynamically
    loadCSSFile();
  }

  /**
   * Load CSS file secara dynamic
   */
  function loadCSSFile() {
    // Get theme directory URL (biasanya tersedia di WordPress)
    var themeUrl = "";

    // Try to get theme URL dari berbagai sources
    if (
      typeof wp !== "undefined" &&
      wp.customize &&
      wp.customize.settings &&
      wp.customize.settings.url
    ) {
      themeUrl = wp.customize.settings.url.template;
    } else if (typeof ajaxurl !== "undefined") {
      // Fallback: construct dari ajaxurl
      themeUrl = ajaxurl.replace(
        "/wp-admin/admin-ajax.php",
        "/wp-content/themes/" + getCurrentThemeName()
      );
    } else {
      // Last fallback: try to detect dari existing stylesheets
      var stylesheets = document.querySelectorAll('link[rel="stylesheet"]');
      for (var i = 0; i < stylesheets.length; i++) {
        var href = stylesheets[i].href;
        if (href.includes("/themes/") && href.includes("blaze-blocksy")) {
          themeUrl = href.substring(0, href.lastIndexOf("/"));
          break;
        }
      }
    }

    if (themeUrl) {
      var cssUrl = themeUrl + "/assets/product/information/style.css";
      loadCSSFromURL(cssUrl);
    } else {
      console.warn("Could not detect theme URL for CSS loading");
    }
  }

  /**
   * Load CSS dari URL
   */
  function loadCSSFromURL(cssUrl) {
    var link = document.createElement("link");
    link.id = "product-information-css-link";
    link.rel = "stylesheet";
    link.type = "text/css";
    link.href = cssUrl + "?v=" + Date.now(); // Cache busting

    link.onload = function () {
      console.log("Product Information CSS loaded successfully from:", cssUrl);
    };

    link.onerror = function () {
      console.error("Failed to load CSS from URL:", cssUrl);
      console.warn("CSS not loaded - styles may not work correctly");
    };

    document.head.appendChild(link);
  }

  /**
   * Get current theme name
   */
  function getCurrentThemeName() {
    // Try to detect theme name dari body class atau lainnya
    var bodyClasses = document.body.className;
    if (bodyClasses.includes("blaze-blocksy")) {
      return "blaze-blocksy";
    }
    return "blaze-blocksy"; // default fallback
  }

  /**
   * Reload CSS file (untuk development/debugging)
   */
  function reloadCSS() {
    // Remove existing CSS link
    var existingLink = document.getElementById("product-information-css-link");

    if (existingLink) {
      existingLink.remove();
    }

    // Reload CSS file
    ensureBaseCSSLoaded();

    console.log("CSS file reloaded");
  }

  // Expose functions untuk debugging
  window.reloadProductInfoCSS = reloadCSS;

  /**
   * Prevent unwanted refresh behavior
   */
  function preventUnwantedRefresh() {
    // Simple approach: just log that we're preventing refresh
    console.log("Product Information: Refresh prevention initialized");

    // The main prevention is now handled by PHP settings:
    // - 'sync' => 'live'
    // - 'refresh' => false
    // - 'transport' => 'postMessage'
  }
})(jQuery);
