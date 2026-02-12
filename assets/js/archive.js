/**
 * Archive/Category Page Enhancements
 *
 * Handles sidebar header persistence and product counter updates
 * for WooCommerce archive pages with AJAX filtering support.
 *
 * @package Blaze_Commerce
 * @since 1.0.0
 */
(function ($) {
  "use strict";

  /**
   * Configuration
   */
  const CONFIG = {
    selectors: {
      sidebar: ".ct-sidebar",
      sidebarPanel: ".ct-panel-content-inner",
      sidebarHeader: ".woo-sidebar-header",
      products: ".products",
      product: ".product",
      pagination: ".ct-pagination",
      resultCount: ".woocommerce-result-count",
      categoryCount: ".ct-product-category-count",
      loadMore: ".ct-load-more",
      showResultsButton: ".mobile-filters-show-results",
    },
    timing: {
      observerDebounce: 10,
      counterDebounce: 100,
      fallbackDelay: 200,
    },
  };

  /**
   * Get the visible sidebar element (excludes offcanvas/panel sidebars)
   *
   * @returns {Element|null} The visible sidebar element or null
   */
  const getVisibleSidebar = function () {
    try {
      const sidebars = document.querySelectorAll(CONFIG.selectors.sidebar);
      if (!sidebars.length) {
        return null;
      }

      for (const sidebar of sidebars) {
        // Skip offcanvas/panel sidebars (they have zero dimensions)
        if (sidebar.classList.contains("ct-panel-content-inner")) {
          continue;
        }
        return sidebar;
      }
      return null;
    } catch (e) {
      return null;
    }
  };

  /**
   * Restore sidebar header if missing
   * Uses requestAnimationFrame for smoother DOM updates
   */
  const restoreSidebarHeader = function () {
    try {
      if (
        typeof blazeArchive === "undefined" ||
        !blazeArchive.sidebarHeaderHTML
      ) {
        return;
      }

      const sidebar = getVisibleSidebar();
      if (!sidebar) {
        return;
      }

      const headerExists = sidebar.querySelector(
        CONFIG.selectors.sidebarHeader
      );
      if (!headerExists) {
        requestAnimationFrame(function () {
          // Double-check after frame to avoid race conditions
          if (!sidebar.querySelector(CONFIG.selectors.sidebarHeader)) {
            sidebar.insertAdjacentHTML(
              "afterbegin",
              blazeArchive.sidebarHeaderHTML
            );
          }
        });
      }
    } catch (e) {
      // Silently fail - non-critical feature
    }
  };

  /**
   * Initialize MutationObserver on the visible sidebar
   * Uses efficient observation with debouncing
   */
  const initSidebarObserver = function () {
    try {
      if (
        typeof blazeArchive === "undefined" ||
        !blazeArchive.sidebarHeaderHTML
      ) {
        return;
      }

      const sidebar = getVisibleSidebar();
      if (!sidebar) {
        return;
      }

      let debounceTimer = null;

      const observer = new MutationObserver(function () {
        if (debounceTimer) {
          clearTimeout(debounceTimer);
        }
        debounceTimer = setTimeout(
          restoreSidebarHeader,
          CONFIG.timing.observerDebounce
        );
      });

      // Observe only direct children for better performance
      observer.observe(sidebar, {
        childList: true,
        subtree: false,
      });

      // Also observe parent in case sidebar itself is replaced
      const parent = sidebar.parentElement;
      if (parent) {
        const parentObserver = new MutationObserver(function () {
          // Re-check and re-attach observer if sidebar was replaced
          const newSidebar = getVisibleSidebar();
          if (newSidebar && newSidebar !== sidebar) {
            restoreSidebarHeader();
            // Recursively init observer on new sidebar
            initSidebarObserver();
          }
        });

        parentObserver.observe(parent, {
          childList: true,
          subtree: false,
        });
      }
    } catch (e) {
      // Silently fail - non-critical feature
    }
  };

  /**
   * Generate counter text based on current and total products
   *
   * @param {number} currentProducts - Number of currently visible products
   * @param {number} totalCount - Total number of products
   * @returns {string} Formatted counter text
   */
  const generateCounterText = function (currentProducts, totalCount) {
    if (currentProducts === 1) {
      return "Showing the single result";
    } else if (currentProducts >= totalCount) {
      return "Showing all " + totalCount + " results";
    }
    return "Showing 1–" + currentProducts + " of " + totalCount + " results";
  };

  /**
   * Update product counter elements
   */
  const updateCounters = function () {
    try {
      const resultCountEl = document.querySelector(
        CONFIG.selectors.resultCount
      );
      const productsContainer = document.querySelector(
        CONFIG.selectors.products
      );
      let navCountEl = document.querySelector(CONFIG.selectors.categoryCount);

      // Ensure navigation counter element exists
      if (!navCountEl) {
        const pagination = document.querySelector(CONFIG.selectors.pagination);
        if (pagination) {
          pagination.insertAdjacentHTML(
            "afterbegin",
            '<div class="ct-product-category-count"></div>'
          );
          navCountEl = document.querySelector(CONFIG.selectors.categoryCount);
        }
      }

      if (!resultCountEl || !productsContainer) {
        return;
      }

      const currentProducts = productsContainer.querySelectorAll(
        CONFIG.selectors.product + ":not(.hidden)"
      ).length;
      const originalText = resultCountEl.textContent || "";
      const totalMatch = originalText.match(/of\s+(\d+)/i);

      if (totalMatch) {
        const totalCount = parseInt(totalMatch[1], 10);
        const newText = generateCounterText(currentProducts, totalCount);

        resultCountEl.textContent = newText;

        if (navCountEl) {
          navCountEl.textContent = newText;
        }
      } else if (navCountEl) {
        navCountEl.textContent = originalText;
      }
    } catch (e) {
      // Silently fail - non-critical feature
    }
  };

  /**
   * Display initial product count
   */
  const displayProductCount = function () {
    try {
      const pagination = document.querySelector(CONFIG.selectors.pagination);
      if (!pagination) {
        return;
      }

      let countEl = document.querySelector(CONFIG.selectors.categoryCount);
      if (!countEl) {
        pagination.insertAdjacentHTML(
          "afterbegin",
          '<div class="ct-product-category-count"></div>'
        );
        countEl = document.querySelector(CONFIG.selectors.categoryCount);
      }

      const resultCount = document.querySelector(CONFIG.selectors.resultCount);
      if (resultCount && countEl) {
        countEl.textContent = resultCount.textContent || "";
      }
    } catch (e) {
      // Silently fail - non-critical feature
    }
  };

  /**
   * Get total product count from the result count element.
   *
   * Handles all WooCommerce result count formats:
   * - "Showing 1–24 of 123 results"
   * - "Showing all 123 results"
   * - "Showing the single result"
   *
   * @returns {number} Total product count
   */
  const getTotalFromResultCount = function () {
    try {
      const resultCountEl = document.querySelector(
        CONFIG.selectors.resultCount
      );
      if (!resultCountEl) {
        return 0;
      }

      const text = resultCountEl.textContent || "";

      // "Showing 1–24 of 123 results"
      const ofMatch = text.match(/of\s+(\d+)/i);
      if (ofMatch) {
        return parseInt(ofMatch[1], 10);
      }

      // "Showing all 123 results"
      const allMatch = text.match(/all\s+(\d+)/i);
      if (allMatch) {
        return parseInt(allMatch[1], 10);
      }

      // "Showing the single result"
      if (text.indexOf("single") !== -1) {
        return 1;
      }

      return 0;
    } catch (e) {
      return 0;
    }
  };

  /**
   * Restore the "Show X results" button if removed by AJAX filtering.
   * Similar to restoreSidebarHeader() approach.
   */
  const restoreShowResultsButton = function () {
    try {
      if (
        typeof blazeArchive === "undefined" ||
        !blazeArchive.showResultsButtonHTML
      ) {
        return;
      }

      // Find the offcanvas filter panel
      const panel = document.getElementById("woo-filters-panel");
      if (!panel) {
        return;
      }

      const buttonExists = panel.querySelector(
        CONFIG.selectors.showResultsButton
      );
      if (!buttonExists) {
        // Find the content container and append button at the end
        const contentInner = panel.querySelector(".ct-panel-content-inner");
        if (contentInner) {
          requestAnimationFrame(function () {
            if (!contentInner.querySelector(CONFIG.selectors.showResultsButton)) {
              contentInner.insertAdjacentHTML(
                "beforeend",
                blazeArchive.showResultsButtonHTML
              );
            }
          });
        }
      }
    } catch (e) {
      // Silently fail
    }
  };

  /**
   * Update the "Show X results" button text in offcanvas filter.
   * Restores the button first if it was removed by AJAX.
   */
  const updateShowResultsButton = function () {
    try {
      restoreShowResultsButton();

      // Use requestAnimationFrame to ensure DOM is updated after restore
      requestAnimationFrame(function () {
        const button = document.querySelector(
          CONFIG.selectors.showResultsButton
        );
        if (!button) {
          return;
        }

        const total = getTotalFromResultCount();
        button.textContent =
          "Show " + total + " result" + (total !== 1 ? "s" : "");
      });
    } catch (e) {
      // Silently fail
    }
  };

  /**
   * Debounce utility function
   *
   * @param {Function} func - Function to debounce
   * @param {number} wait - Wait time in milliseconds
   * @returns {Function} Debounced function
   */
  const debounce = function (func, wait) {
    let timeout;
    return function () {
      const args = arguments;
      const context = this;
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        func.apply(context, args);
      }, wait);
    };
  };

  // Create debounced version of updateCounters
  const debouncedUpdateCounters = debounce(
    updateCounters,
    CONFIG.timing.counterDebounce
  );

  /**
   * Handle AJAX filter completion
   */
  const onFilterComplete = function () {
    restoreSidebarHeader();
    displayProductCount();
    debouncedUpdateCounters();
    updateShowResultsButton();
  };

  /**
   * Initialize all functionality
   */
  const init = function () {
    try {
      // Initialize sidebar header observer
      initSidebarObserver();

      // Ensure header exists on init
      restoreSidebarHeader();

      // Initialize product count display
      displayProductCount();

      // Initialize show results button text
      updateShowResultsButton();

      // Close offcanvas when show results button is clicked
      $(document).on("click", CONFIG.selectors.showResultsButton, function (e) {
        e.preventDefault();
        var panel = $(this).closest(".ct-panel");
        if (panel.length) {
          var closeBtn = panel.find(".ct-toggle-close");
          if (closeBtn.length) {
            closeBtn[0].click();
          }
        }
      });

      // Listen for Blocksy theme events
      if (window.ctEvents) {
        ctEvents.on("ct:infinite-scroll:load", function () {
          setTimeout(debouncedUpdateCounters, 500);
        });

        ctEvents.on("blocksy:frontend:init", function () {
          setTimeout(onFilterComplete, 100);
        });
      }

      // Listen for Load More button clicks
      $(document).on("click", CONFIG.selectors.loadMore, function () {
        setTimeout(debouncedUpdateCounters, 1000);
      });

      // MutationObserver for products container
      const productsContainer = document.querySelector(
        CONFIG.selectors.products
      );
      if (productsContainer) {
        const productObserver = new MutationObserver(function (mutations) {
          let shouldUpdate = false;

          for (const mutation of mutations) {
            if (
              mutation.type === "childList" &&
              mutation.addedNodes.length > 0
            ) {
              for (const node of mutation.addedNodes) {
                if (node.nodeType === Node.ELEMENT_NODE) {
                  if (node.classList && node.classList.contains("product")) {
                    shouldUpdate = true;
                    break;
                  }
                  if (
                    node.querySelector &&
                    node.querySelector(CONFIG.selectors.product)
                  ) {
                    shouldUpdate = true;
                    break;
                  }
                }
              }
            }
            if (shouldUpdate) break;
          }

          if (shouldUpdate) {
            debouncedUpdateCounters();
          }
        });

        productObserver.observe(productsContainer, {
          childList: true,
          subtree: true,
        });
      }

      // Listen for WooCommerce events
      $(document.body).on(
        "wc_fragments_refreshed wc_fragments_loaded",
        function () {
          setTimeout(onFilterComplete, CONFIG.timing.fallbackDelay);
        }
      );

      // Listen for filter plugin events
      var filterEvents = [
        "berocket_ajax_filtering_end",
        "yith-wcan-ajax-filtered",
        "facetwp-loaded",
        "jet-filter-content-rendered",
        "wpf_ajax_success",
        "sf:ajaxfinish",
        "prdctfltr-reload",
        "blocksy:ajax:filters:done",
        "updated_wc_div",
      ];

      filterEvents.forEach(function (eventName) {
        $(document).on(eventName, function () {
          setTimeout(onFilterComplete, CONFIG.timing.fallbackDelay);
        });
      });

      // Fallback for generic AJAX complete
      $(document).ajaxComplete(function (event, xhr, settings) {
        if (
          settings.url &&
          (settings.url.indexOf("wc-ajax") !== -1 ||
            settings.url.indexOf("admin-ajax") !== -1 ||
            settings.url.indexOf("filter") !== -1)
        ) {
          setTimeout(onFilterComplete, CONFIG.timing.fallbackDelay);
        }
      });
    } catch (e) {
      // Log error in development, silent in production
      if (typeof console !== "undefined" && console.error) {
        console.error("Archive JS initialization error:", e);
      }
    }
  };

  // Initialize on document ready
  $(document).ready(init);
})(jQuery);
