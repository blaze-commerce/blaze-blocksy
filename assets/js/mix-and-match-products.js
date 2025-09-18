(function ($) {
  $(document).ready(function () {
    console.log("Mix and Match Load More script loaded");

    // Search functionality variables
    var searchTimeout;
    var isSearchMode = false;
    var container = $(".mnm_child_products.products");
    var searchField, noResultsMessage;
    var loadMoreButton = $(".ct-mnm-load-more");

    // Create and insert search elements
    function createSearchElements() {
      if (container.length === 0) {
        console.log("Container not found, retrying...");
        return false;
      }

      // Create search container
      var searchContainer = $('<div class="mnm-search-container"></div>');

      // Create search field
      searchField = $(
        '<input type="text" class="mnm-search-field" placeholder="Search products..." aria-label="Search products" autocomplete="off">'
      );

      // Create no results message
      noResultsMessage = $(
        '<div class="mnm-search-no-results" style="display: none;"><p>No Products Found</p></div>'
      );

      // Append elements to container
      searchContainer.append(searchField);
      searchContainer.append(noResultsMessage);

      // Insert search container before products container
      searchContainer.insertBefore(container);

      console.log("Search elements created and inserted");
      return true;
    }

    // Initialize search elements
    if (!createSearchElements()) {
      // Retry after a short delay if container not found
      setTimeout(createSearchElements, 500);
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
      console.log("Search term:", searchTerm);

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
        console.log("Entering search mode");
        isSearchMode = true;
        container.addClass("mnm-search-mode");
        loadMoreButton.hide();

        // BETTER APPROACH: Hide all products first, then show only matching ones
        var allProducts = container.find("li.product");
        allProducts.addClass("mnm-search-hidden").hide();

        console.log("Search mode: All products hidden, ready for filtering");
      }
    }

    function exitSearchMode() {
      if (isSearchMode) {
        console.log("Exiting search mode");
        isSearchMode = false;
        container.removeClass("mnm-search-mode");
        loadMoreButton.show();

        // Reset all products to original state
        resetProductsToOriginalState();
        noResultsMessage.hide();
      }
    }

    function filterProducts(searchTerm) {
      var allProducts = container.find("li.product");
      var matchingProducts = [];
      var searchTermLower = searchTerm.toLowerCase();

      console.log("Filtering products with term:", searchTerm);
      console.log("Total products to filter:", allProducts.length);

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

          // Debug: Log matching products
          var debugTitle = titleElement.text();
          var debugVariations = [];
          product.find(".product-details .variation dd").each(function () {
            debugVariations.push($(this).text().trim());
          });
          console.log(
            "✅ Match found:",
            debugTitle,
            "| Variations:",
            debugVariations.join(", ")
          );
        }
        // Non-matching products remain hidden (already hidden in enterSearchMode)
      });

      console.log("Matching products found:", matchingProducts.length);
      console.log("Search term:", searchTerm);

      // Show/hide no results message
      if (matchingProducts.length === 0) {
        noResultsMessage.show();
      } else {
        noResultsMessage.hide();
      }
    }

    function resetProductsToOriginalState() {
      var allProducts = container.find("li.product");

      console.log("Resetting products to original state...");
      console.log("Total products found:", allProducts.length);

      // Remove search-related classes
      allProducts.removeClass("mnm-search-hidden");

      // Reset to original load more state (only first row visible)
      allProducts.removeClass("mnm-show");

      // Force show all products first, then let CSS rules hide the appropriate ones
      allProducts.show();

      // Reset load more button state
      loadMoreButton.prop("disabled", false).text("Load More");

      // Check final state after reset
      setTimeout(function () {
        var visibleAfterReset = allProducts.filter(":visible").length;
        var hiddenAfterReset = allProducts.filter(":hidden").length;
        console.log(
          "After reset - Visible:",
          visibleAfterReset,
          "Hidden:",
          hiddenAfterReset
        );
      }, 100);

      console.log("Products reset to original state completed");
    }

    // Handle load more button click
    $(document).on("click", ".ct-mnm-load-more", function (e) {
      e.preventDefault();

      // Don't process if in search mode
      if (isSearchMode) {
        return;
      }

      var button = $(this);
      var columns = parseInt(button.data("columns")) || 3; // fallback to 3 columns

      console.log("Load more clicked, columns:", columns);
      console.log("Container found:", container.length);

      // Find hidden products using CSS visibility (not .mnm-show class)
      var allProducts = container.find("li.product");
      var hiddenProducts = allProducts.filter(":hidden");

      console.log("Hidden products found:", hiddenProducts.length);

      if (hiddenProducts.length === 0) {
        button.prop("disabled", true).text("All products loaded");
        console.log("No more products to load");
        return;
      }

      // Show next row of products based on columns by adding mnm-show class
      var productsToShow = hiddenProducts.slice(0, columns);
      productsToShow.addClass("mnm-show");

      console.log("Showing", productsToShow.length, "products");

      // Check if there are more products to load
      var remainingHidden = allProducts.filter(":hidden");

      console.log("Remaining hidden products:", remainingHidden.length);

      if (remainingHidden.length === 0) {
        button.prop("disabled", true).text("All products loaded");
      }
    });

    // Debug: Log the structure when page loads
    setTimeout(function () {
      var debugContainer = $(".mnm_child_products.products");
      var allProducts = debugContainer.find("li.product");
      var hiddenProducts = allProducts.filter(":hidden");
      var visibleProducts = allProducts.filter(":visible");

      console.log("Debug - Total products:", allProducts.length);
      console.log("Debug - Hidden products:", hiddenProducts.length);
      console.log("Debug - Visible products:", visibleProducts.length);
      console.log("Debug - Container classes:", debugContainer.attr("class"));
      console.log("Debug - Search field found:", $(".mnm-search-field").length);
      console.log("Debug - Load more button found:", loadMoreButton.length);

      // Log first few hidden products for debugging
      hiddenProducts.slice(0, 3).each(function (index) {
        var product = $(this);
        var title = product.find(".woocommerce-loop-product__title").text();
        var variations = [];

        // Get variation data for debugging
        product.find(".product-details .variation dd").each(function () {
          variations.push($(this).text().trim());
        });

        console.log(
          "Hidden product " + (index + 1) + ":",
          title,
          "| Variations:",
          variations.join(", ")
        );
      });
    }, 1000);
  });
})(jQuery);
