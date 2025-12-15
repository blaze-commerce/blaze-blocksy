(function ($) {
  // Cache DOM elements for better performance
  let $pagination = null;
  let $resultCount = null;
  let $categoryCount = null;

  /**
   * Initialize cached selectors
   */
  const initCachedSelectors = function () {
    $pagination = $(".ct-pagination");
    $resultCount = $(".woocommerce-result-count");
    $categoryCount = $(".ct-product-category-count");
  };

  /**
   * Generate counter text based on current and total products
   */
  const generateCounterText = function (currentProducts, totalCount) {
    if (currentProducts === 1) {
      return "Showing the single result";
    } else if (currentProducts >= totalCount) {
      return `Showing all ${totalCount} results`;
    } else {
      return `Showing 1–${currentProducts} of ${totalCount} results`;
    }
  };

  /**
   * Update both original and navigation counters
   */
  const updateCounters = function () {
    const resultCountEl = document.querySelector(".woocommerce-result-count");
    const productsContainer = document.querySelector(".products");
    let navCountEl = document.querySelector(".ct-product-category-count");

    // Ensure navigation counter element exists
    if (!navCountEl && $pagination && $pagination.length > 0) {
      $pagination.prepend('<div class="ct-product-category-count"></div>');
      navCountEl = document.querySelector(".ct-product-category-count");
      // Update cached selector
      $categoryCount = $(".ct-product-category-count");
    }

    if (!resultCountEl || !productsContainer) {
      return;
    }

    const currentProducts = productsContainer.querySelectorAll(
      ".product:not(.hidden)"
    ).length;
    const originalText = resultCountEl.textContent;
    const totalMatch = originalText.match(/of\s+(\d+)/i);

    if (totalMatch) {
      const totalCount = parseInt(totalMatch[1]);
      const newText = generateCounterText(currentProducts, totalCount);

      // Update original counter
      resultCountEl.textContent = newText;

      // Update navigation counter if exists
      if (navCountEl) {
        navCountEl.textContent = newText;
      }
    } else {
      // Fallback: use the original text if no match found
      if (navCountEl) {
        navCountEl.textContent = originalText;
      }
    }
  };

  /**
   * Display initial product count and ensure element exists
   */
  const displayProductCount = function () {
    // Ensure the element exists
    if (!$categoryCount || $categoryCount.length === 0) {
      if ($pagination && $pagination.length > 0) {
        $pagination.prepend('<div class="ct-product-category-count"></div>');
        $categoryCount = $(".ct-product-category-count");
      }
    }

    if (
      $resultCount &&
      $resultCount.length > 0 &&
      $categoryCount &&
      $categoryCount.length > 0
    ) {
      $categoryCount.text($resultCount.text());
    }
  };

  /**
   * Debounce function to prevent excessive calls
   */
  const debounce = function (func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  };

  // Create debounced version of updateCounters
  const debouncedUpdateCounters = debounce(updateCounters, 100);

  $(document).ready(function () {
    // Initialize cached selectors
    initCachedSelectors();

    // Add element to .ct-pagination using cached selector
    if ($pagination && $pagination.length > 0) {
      $pagination.prepend('<div class="ct-product-category-count"></div>');
      // Update cached selector after adding element
      $categoryCount = $(".ct-product-category-count");
    }
    displayProductCount();

    // Initialize load more counter functionality
    // Listen for Blocksy theme events
    if (window.ctEvents) {
      // Primary event for infinite scroll load
      ctEvents.on("ct:infinite-scroll:load", function () {
        setTimeout(debouncedUpdateCounters, 500);
      });

      // Secondary event for frontend initialization
      ctEvents.on("blocksy:frontend:init", function () {
        setTimeout(debouncedUpdateCounters, 500);
      });
    }

    // Fallback: Listen for Load More button clicks
    $(document).on("click", ".ct-load-more", function () {
      // Wait for AJAX to complete and DOM to update
      setTimeout(function () {
        debouncedUpdateCounters();
      }, 1000);
    });

    // Fallback: MutationObserver for DOM changes
    const productsContainer = document.querySelector(".products");
    let productsObserver = null;

    if (productsContainer) {
      productsObserver = new MutationObserver(function (mutations) {
        let shouldUpdate = false;

        mutations.forEach(function (mutation) {
          if (mutation.type === "childList" && mutation.addedNodes.length > 0) {
            // Check if any added nodes are product items
            for (let node of mutation.addedNodes) {
              if (
                node.nodeType === Node.ELEMENT_NODE &&
                (node.classList.contains("product") ||
                  (node.querySelector && node.querySelector(".product")))
              ) {
                shouldUpdate = true;
                break;
              }
            }
          }
        });

        if (shouldUpdate) {
          debouncedUpdateCounters();
        }
      });

      productsObserver.observe(productsContainer, {
        childList: true,
        subtree: true,
      });

      // Cleanup observer on page unload to prevent memory leaks
      window.addEventListener("beforeunload", function () {
        if (productsObserver) {
          productsObserver.disconnect();
          productsObserver = null;
        }
      });
    }

    // Listen for WooCommerce events
    $(document.body).on("wc_fragments_refreshed", function () {
      setTimeout(debouncedUpdateCounters, 300);
    });

    // Listen for filter plugin events
    const filterEvents = [
      "berocket_ajax_filtering_end", // BeRocket AJAX Product Filters
      "yith-wcan-ajax-filtered", // YITH WooCommerce Ajax Product Filter
      "facetwp-loaded", // FacetWP
      "jet-filter-content-rendered", // Jet Smart Filters
      "wpf_ajax_success", // WooCommerce Product Filter
      "sf:ajaxfinish", // SearchAndFilter
      "prdctfltr-reload", // Product Filter Pro
      "blocksy:ajax:filters:done", // Blocksy theme filters
      "wc_fragments_loaded", // WooCommerce fragments
      "updated_wc_div", // WooCommerce div updates
      "woocommerce_update_checkout", // WooCommerce checkout updates
    ];

    // Bind filter events
    filterEvents.forEach(function (eventName) {
      $(document).on(eventName, function () {
        // Different plugins need different delays
        const delays = {
          berocket_ajax_filtering_end: 100,
          "yith-wcan-ajax-filtered": 200,
          "facetwp-loaded": 50,
          "jet-filter-content-rendered": 150,
          wpf_ajax_success: 100,
          "sf:ajaxfinish": 100,
          "blocksy:ajax:filters:done": 50,
          wc_fragments_loaded: 200,
          updated_wc_div: 150,
        };

        const delay = delays[eventName] || 100;
        setTimeout(function () {
          displayProductCount(); // Ensure element exists
          debouncedUpdateCounters();
        }, delay);
      });
    });

    // Additional fallback for generic AJAX complete
    $(document).ajaxComplete(function (event, xhr, settings) {
      // Check if this is a filter-related AJAX call
      if (
        settings.url &&
        (settings.url.includes("wc-ajax") ||
          settings.url.includes("admin-ajax") ||
          settings.url.includes("filter"))
      ) {
        setTimeout(function () {
          displayProductCount();
          debouncedUpdateCounters();
        }, 200);
      }
    });
  });
})(jQuery);
