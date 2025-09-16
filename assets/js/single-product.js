/**
 * Single Product Page JavaScript
 * Handles recently viewed products tracking and AJAX loading
 */
(function ($) {
  "use strict";

  // Storage key for recently viewed products
  const STORAGE_KEY = "recently_viewed_products";
  const COOKIE_NAME = "recently_viewed_products";

  // Get products from localStorage with sessionStorage and cookie fallback
  function getRecentlyViewedProducts() {
    let products = [];

    // Try localStorage first
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored) {
        products = JSON.parse(stored);
      }
    } catch (e) {
      // Fallback to sessionStorage
      try {
        const stored = sessionStorage.getItem(STORAGE_KEY);
        if (stored) {
          products = JSON.parse(stored);
        }
      } catch (e2) {
        // Fallback to cookie
        const cookieValue = getCookie(COOKIE_NAME);
        if (cookieValue) {
          try {
            products = JSON.parse(cookieValue);
          } catch (e3) {
            products = [];
          }
        }
      }
    }

    return Array.isArray(products) ? products : [];
  }

  // Save products to storage
  function saveRecentlyViewedProducts(products) {
    const jsonProducts = JSON.stringify(products);

    // Try localStorage first
    try {
      localStorage.setItem(STORAGE_KEY, jsonProducts);
    } catch (e) {
      // Fallback to sessionStorage
      try {
        sessionStorage.setItem(STORAGE_KEY, jsonProducts);
      } catch (e2) {
        // Fallback to cookie
        setCookie(COOKIE_NAME, jsonProducts, 30);
      }
    }

    // Always try to set cookie as backup
    try {
      setCookie(COOKIE_NAME, jsonProducts, 30);
    } catch (e) {
      // Silent fail
    }
  }

  // Cookie helper functions
  function getCookie(name) {
    const value = "; " + document.cookie;
    const parts = value.split("; " + name + "=");
    if (parts.length === 2) return parts.pop().split(";").shift();
    return null;
  }

  function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie =
      name + "=" + value + ";expires=" + expires.toUTCString() + ";path=/";
  }

  // Track current product
  function trackCurrentProduct(productId) {
    let products = getRecentlyViewedProducts();

    // Remove current product if it exists (avoid duplicates)
    products = products.filter((id) => parseInt(id) !== parseInt(productId));

    // Add current product to the beginning
    products.unshift(parseInt(productId));

    // Limit to maximum 20 products
    if (products.length > 20) {
      products = products.slice(0, 20);
    }

    // Save to storage
    saveRecentlyViewedProducts(products);
  }

  // Load recently viewed products via AJAX
  window.loadRecentlyViewedProducts = function (currentProductId) {
    const products = getRecentlyViewedProducts();
    const $loading = $("#recently-viewed-loading");
    const $section = $("#recently-viewed-section");

    // Show loading indicator
    $loading.show();
    $section.hide();

    $.ajax({
      url: blazeBlocksySingleProduct.ajax_url,
      type: "POST",
      data: {
        action: "get_recently_viewed_products",
        current_product_id: currentProductId,
        recently_viewed: products,
        nonce: blazeBlocksySingleProduct.recently_viewed.nonce,
      },
      success: function (response) {
        // Hide loading indicator with fade effect
        $loading.addClass("fade-out");

        setTimeout(function () {
          $loading.hide().removeClass("fade-out");

          if (response.success && response.data.has_products) {
            $("#recently-viewed-products-container").html(response.data.html);
            $section.show();

            // Initialize owl carousel for recently viewed products
            initializeRecentlyViewedCarousel();
          }
        }, 300); // Match the CSS transition duration
      },
      error: function () {
        console.log("Failed to load recently viewed products");

        // Hide loading indicator on error
        $loading.addClass("fade-out");
        setTimeout(function () {
          $loading.hide().removeClass("fade-out");
        }, 300);
      },
    });
  };

  // Initialize owl carousel for recently viewed products
  function initializeRecentlyViewedCarousel() {
    const $carousel = $(".recently-viewed-products .products");

    if ($carousel.length && !$carousel.hasClass("owl-carousel")) {
      $carousel.addClass("owl-carousel owl-theme");

      // Use the same config as related products (from related-carousel.php)
      const carouselConfig = {
        loop: false,
        margin: 24,
        nav: false,
        dots: true,
        responsive: {
          0: {
            items: 2,
          },
          1000: {
            items: 4,
            nav: true,
          },
        },
      };

      // Small delay to ensure DOM is ready
      setTimeout(function () {
        $carousel.owlCarousel(carouselConfig);
      }, 100);
    }
  }

  // Initialize when document is ready
  $(document).ready(function () {
    // Only run on single product pages
    if (
      blazeBlocksySingleProduct.recently_viewed &&
      blazeBlocksySingleProduct.recently_viewed.current_product_id
    ) {
      trackCurrentProduct(
        blazeBlocksySingleProduct.recently_viewed.current_product_id
      );
    }
  });
})(jQuery);
