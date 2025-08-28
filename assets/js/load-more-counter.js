/**
 * Load More Counter JavaScript functionality
 * Updates the "SHOWING X-Y OF Z RESULTS" counter when Load More button is clicked
 */

jQuery(document).ready(function ($) {
  /**
   * Debug logging function
   */
  function debugLog(message) {
    if (window.blazeBlocksyLoadMore && window.blazeBlocksyLoadMore.debug) {
      console.log("[BlazeBlocksy LoadMore Counter]", message);
    }
  }

  /**
   * Generate counter text based on current and total products
   */
  function generateCounterText(currentProducts, totalCount) {
    if (currentProducts === 1) {
      return "Showing the single result";
    } else if (currentProducts >= totalCount) {
      return `Showing all ${totalCount} results`;
    } else {
      return `Showing 1–${currentProducts} of ${totalCount} results`;
    }
  }

  /**
   * Update the result counter
   */
  function updateCounter() {
    const resultCountEl = document.querySelector(".woocommerce-result-count");
    const productsContainer = document.querySelector(".products");

    if (!resultCountEl || !productsContainer) {
      debugLog("Required elements not found");
      return;
    }

    const currentProducts =
      productsContainer.querySelectorAll(".product").length;
    const originalText = resultCountEl.textContent;
    const totalMatch = originalText.match(/of\s+(\d+)/i);

    if (totalMatch) {
      const totalCount = parseInt(totalMatch[1]);
      const newText = generateCounterText(currentProducts, totalCount);

      debugLog(`Updating counter: ${currentProducts}/${totalCount} products`);
      debugLog(`Old text: ${originalText}`);
      debugLog(`New text: ${newText}`);

      resultCountEl.textContent = newText;
    } else {
      debugLog("Could not extract total count from: " + originalText);
    }
  }

  /**
   * Debounce function to prevent excessive calls
   */
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // Create debounced version of updateCounter
  const debouncedUpdateCounter = debounce(updateCounter, 100);

  /**
   * Initialize event listeners
   */
  function initEventListeners() {
    debugLog("Initializing event listeners");

    // Listen for Blocksy theme events
    if (window.ctEvents) {
      debugLog("Blocksy ctEvents found, binding events");

      // Primary event for infinite scroll load
      ctEvents.on("ct:infinite-scroll:load", function () {
        debugLog("ct:infinite-scroll:load event triggered");
        setTimeout(debouncedUpdateCounter, 500); // Small delay to ensure DOM is updated
      });

      // Secondary event for frontend initialization
      ctEvents.on("blocksy:frontend:init", function () {
        debugLog("blocksy:frontend:init event triggered");
        setTimeout(debouncedUpdateCounter, 500);
      });
    } else {
      debugLog("Blocksy ctEvents not found, using fallback methods");
    }

    // Fallback: Listen for Load More button clicks
    $(document).on("click", ".ct-load-more", function () {
      debugLog("Load More button clicked");

      // Wait for AJAX to complete and DOM to update
      setTimeout(function () {
        debouncedUpdateCounter();
      }, 1000);
    });

    // Fallback: MutationObserver for DOM changes
    const productsContainer = document.querySelector(".products");
    if (productsContainer) {
      debugLog("Setting up MutationObserver");

      const observer = new MutationObserver(function (mutations) {
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
          debugLog("Products container changed, updating counter");
          debouncedUpdateCounter();
        }
      });

      observer.observe(productsContainer, {
        childList: true,
        subtree: true,
      });

      // Store observer for cleanup
      window.blazeBlocksyLoadMoreObserver = observer;
    }

    // Listen for WooCommerce events
    $(document.body).on("wc_fragments_refreshed", function () {
      debugLog("WooCommerce fragments refreshed");
      setTimeout(debouncedUpdateCounter, 300);
    });
  }

  /**
   * Cleanup function
   */
  function cleanup() {
    if (window.blazeBlocksyLoadMoreObserver) {
      window.blazeBlocksyLoadMoreObserver.disconnect();
      delete window.blazeBlocksyLoadMoreObserver;
    }
  }

  // Initialize when document is ready
  initEventListeners();

  // Initial counter update
  setTimeout(updateCounter, 100);

  // Cleanup on page unload
  $(window).on("beforeunload", cleanup);

  debugLog("Load More Counter initialized");
});
