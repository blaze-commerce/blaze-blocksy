(function ($) {
  $(document).ready(function () {
    // Search functionality variables
    var searchTimeout;
    var isSearchMode = false;
    var container = $(".mnm_child_products.products");
    var searchField, noResultsMessage, itemsCounter;
    var loadMoreButton = $(".ct-mnm-load-more");

    // Create and insert search elements
    function createSearchElements() {
      if (container.length === 0) {
        return false;
      }

      // Create search container
      var searchContainer = $('<div class="mnm-search-container"></div>');

      // Create search field
      searchField = $(
        '<input type="text" class="mnm-search-field" placeholder="Search products for product" aria-label="Search products" autocomplete="off">'
      );

      // Create items counter
      itemsCounter = $(
        '<div class="mnm-items-counter"><span class="mnm-items-count">0 items available</span></div>'
      );

      // Create no results message
      noResultsMessage = $(
        '<div class="mnm-search-no-results" style="display: none;"><p>No Products Found</p></div>'
      );

      // Append elements to container
      searchContainer.append(searchField);
      searchContainer.append(itemsCounter);
      searchContainer.append(noResultsMessage);

      // Insert search container before products container
      searchContainer.insertBefore(container);

      return true;
    }

    // Function to update items counter
    function updateItemsCounter() {
      var allProducts = container.find("li.product");
      var visibleProducts = allProducts.filter(":visible");
      var totalProducts = allProducts.length;

      var counterText;
      if (isSearchMode) {
        // In search mode, show matching results
        counterText =
          visibleProducts.length + " of " + totalProducts + " items found";
      } else {
        // Normal mode, show total available
        counterText = totalProducts + " items available";
      }

      itemsCounter.find(".mnm-items-count").text(counterText);
    }

    // Initialize search elements
    if (!createSearchElements()) {
      // Retry after a short delay if container not found
      setTimeout(function () {
        createSearchElements();
        updateItemsCounter(); // Update counter after retry
      }, 500);
    } else {
      // Update counter after successful initialization
      setTimeout(updateItemsCounter, 100);
    }

    // Handle search input with debouncing (using event delegation)
    $(document).on("input", ".mnm-search-field", function () {
      var searchTerm = $(this).val().trim();

      // Clear previous timeout
      clearTimeout(searchTimeout);

      // Set new timeout for debouncing (500ms)
      searchTimeout = setTimeout(function () {
        handleSearch(searchTerm);
      }, 500);
    });

    function handleSearch(searchTerm) {
      // Check if search term meets minimum length requirement (3 characters)
      if (searchTerm.length < 3) {
        exitSearchMode();
        return;
      }

      enterSearchMode();
      filterProducts(searchTerm);
    }

    function enterSearchMode() {
      if (!isSearchMode) {
        isSearchMode = true;
        container.addClass("mnm-search-mode");
        loadMoreButton.hide();

        // CRITICAL FIX: Remove mnm-show class from load more, then hide all products
        var allProducts = container.find("li.product");

        allProducts.removeClass("mnm-show"); // Remove load more visibility
        allProducts.addClass("mnm-search-hidden").hide(); // Hide all for search
      }
    }

    function exitSearchMode() {
      if (isSearchMode) {
        isSearchMode = false;
        container.removeClass("mnm-search-mode");
        loadMoreButton.show();

        // Reset all products to original state
        resetProductsToOriginalState();
        noResultsMessage.hide();

        // Update items counter
        updateItemsCounter();
      }
    }

    function filterProducts(searchTerm) {
      var allProducts = container.find("li.product");
      var matchingProducts = [];
      var searchTermLower = searchTerm.toLowerCase();

      allProducts.each(function () {
        var product = $(this);
        var isMatch = false;

        // Search in product title
        var titleElement = product.find(".woocommerce-loop-product__title > a");
        var productTitle = titleElement.text().toLowerCase();

        if (productTitle.includes(searchTermLower)) {
          isMatch = true;
        }

        // Search in variation data if title doesn't match
        if (!isMatch) {
          var variationElement = product.find(".product-details .variation");
          if (variationElement.length > 0) {
            // Get all variation values (dd elements)
            var variationValues = [];
            variationElement.find("dd").each(function () {
              var value = $(this).text().trim();
              if (value) {
                variationValues.push(value.toLowerCase());
              }
            });

            // Check if search term matches any variation value
            var variationText = variationValues.join(" ");
            if (variationText.includes(searchTermLower)) {
              isMatch = true;
            }
          }
        }

        // Show only matching products (all products are already hidden)
        if (isMatch) {
          matchingProducts.push(product);
          // Show matching product with mnm-show class to override any CSS rules
          product.removeClass("mnm-search-hidden").addClass("mnm-show").show();
        }
        // Non-matching products remain hidden (already hidden in enterSearchMode)
      });

      // Show/hide no results message
      if (matchingProducts.length === 0) {
        noResultsMessage.show();
      } else {
        noResultsMessage.hide();
      }

      // Update items counter
      updateItemsCounter();
    }

    function resetProductsToOriginalState() {
      var allProducts = container.find("li.product");

      // Remove search-related classes
      allProducts.removeClass("mnm-search-hidden");

      // Reset to original load more state (only first row visible)
      allProducts.removeClass("mnm-show");

      // Force show all products first, then let CSS rules hide the appropriate ones
      allProducts.show();

      // Reset load more button state
      loadMoreButton.prop("disabled", false).text("Load More");
    }

    // Handle load more button click
    $(document).on("click", ".ct-mnm-load-more", function (e) {
      e.preventDefault();

      // Don't process if in search mode
      if (isSearchMode) {
        return;
      }

      var button = $(this);
      var loadMoreCount = parseInt(button.data("load-more")) || 4; // fallback to 4 products
      var initialCount = parseInt(button.data("initial")) || 4; // fallback to 4 products
      var gridColumns = parseInt(button.data("grid-columns")) || 4; // fallback to 4 columns

      // Find hidden products using CSS visibility (not .mnm-show class)
      var allProducts = container.find("li.product");
      var hiddenProducts = allProducts.filter(":hidden");

      if (hiddenProducts.length === 0) {
        button.prop("disabled", true).text("All products loaded");
        return;
      }

      // Show next batch of products based on loadMoreCount by adding mnm-show class
      var productsToShow = hiddenProducts.slice(0, loadMoreCount);
      productsToShow.addClass("mnm-show");

      // Auto-scroll to bottom of products container
      setTimeout(function () {
        container.animate(
          {
            scrollTop: container[0].scrollHeight,
          },
          500
        ); // 500ms smooth scroll
      }, 100); // Small delay to ensure products are rendered

      // Check if there are more products to load
      var remainingHidden = allProducts.filter(":hidden");

      if (remainingHidden.length === 0) {
        button.prop("disabled", true).text("All products loaded");
      }
    });

    // Initialize load more button position
    setTimeout(function () {
      loadMoreButton.insertAfter($(".mnm_child_products.products"));
    }, 1000);
  });
})(jQuery);
