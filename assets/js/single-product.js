/**
 * Single Product Page JavaScript
 * Handles recently viewed products tracking and AJAX loading
 */
(function ($) {
  "use strict";

  // Sync wishlist button state from Blocksy localized data or cookie
  function syncWishlistButtonState() {
    var btn = document.querySelector(
      ".ct-wishlist-button-single[data-product-id]",
    );
    if (!btn) return;

    var productId = parseInt(btn.dataset.productId);
    if (!productId) return;

    var items = [];

    try {
      // Try ct_localizations first (works for logged-in users and guests when no page cache)
      if (
        typeof ct_localizations !== "undefined" &&
        ct_localizations.blc_ext_wish_list &&
        ct_localizations.blc_ext_wish_list.list &&
        ct_localizations.blc_ext_wish_list.list.items
      ) {
        items = ct_localizations.blc_ext_wish_list.list.items;
      }

      // Fallback to cookie (for guests, especially with page caching)
      if (!items.length) {
        var cookie = document.cookie.split("; ").find(function (c) {
          return c.startsWith("blc_products_wish_list=");
        });
        if (cookie) {
          var data = JSON.parse(decodeURIComponent(cookie.split("=")[1]));
          if (data && data.items) {
            items = data.items;
          }
        }
      }

      for (var i = 0; i < items.length; i++) {
        if (parseInt(items[i].id) === productId) {
          btn.dataset.buttonState = "active";
          break;
        }
      }
    } catch (e) {}
  }

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
    // Sync wishlist button state (needs document ready so ct_localizations is available)
    syncWishlistButtonState();

    // Only run on single product pages
    if (
      blazeBlocksySingleProduct.recently_viewed &&
      blazeBlocksySingleProduct.recently_viewed.current_product_id
    ) {
      var currentProductId =
        blazeBlocksySingleProduct.recently_viewed.current_product_id;

      // Track current product
      trackCurrentProduct(currentProductId);

      // Auto-load recently viewed products if container exists
      if ($("#recently-viewed-loading").length) {
        loadRecentlyViewedProducts(currentProductId);
      }
    }

    $(
      ".cwginstock-subscribe-form.cwginstock-0outofstock .cwginstock-panel-heading",
    ).on("click", function () {
      $(this).hide();
      $(this).next(".cwginstock-panel-body").slideToggle();
    });

    $(
      ".cwginstock-subscribe-form.cwginstock-0outofstock .cwginstock-panel-heading h4",
    ).prepend(
      '<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.834 2.33301H5.16732C3.16732 2.33301 1.83398 3.33301 1.83398 5.66634V10.333C1.83398 12.6663 3.16732 13.6663 5.16732 13.6663H11.834C13.834 13.6663 15.1673 12.6663 15.1673 10.333V5.66634C15.1673 3.33301 13.834 2.33301 11.834 2.33301ZM12.1473 6.39301L10.0607 8.05967C9.62065 8.41301 9.06065 8.58634 8.50065 8.58634C7.94065 8.58634 7.37398 8.41301 6.94065 8.05967L4.85398 6.39301C4.64065 6.21967 4.60732 5.89967 4.77398 5.68634C4.94732 5.47301 5.26065 5.43301 5.47398 5.60634L7.56065 7.27301C8.06732 7.67967 8.92732 7.67967 9.43398 7.27301L11.5207 5.60634C11.734 5.43301 12.054 5.46634 12.2207 5.68634C12.394 5.89967 12.3607 6.21967 12.1473 6.39301Z" fill="#121212"/></svg>',
    );

    $(document.body).on(
      "click",
      ".woocommerce-product-rating .star-rating",
      function (e) {
        // jump to #reviews then open reviews tab
        jQuery("html, body").animate(
          {
            scrollTop: jQuery("#reviews").offset().top,
          },
          100,
        );
        jQuery('[data-target="#tab-reviews"]').trigger("click");
      },
    );

    $(document.body).on("click", ".cr-reviews-link", function (e) {
      jQuery('[data-target="#tab-reviews"]').trigger("click");
    });

    $(document.body).on("click", ".cr-qna-link", function (e) {
      jQuery('[data-target="#tab-cr_qna"]').trigger("click");
    });

    // Handle review submission success notice
    handleReviewSubmissionSuccess();

    // Auto-open reviews tab when URL has #comment-{id} hash
    handleReviewCommentHash();

    // Client-side review form validation (WordPress adds novalidate to comment forms)
    initReviewFormValidation();

    // Watch for WooCommerce notices and scroll to them
    initNoticesObserver();
  });

  /**
   * Client-side validation for the WooCommerce product review form.
   * WordPress adds `novalidate` to comment forms, disabling browser HTML5
   * validation — so we enforce required-field checks manually.
   */
  function initReviewFormValidation() {
    // Find the review comment form — try #commentform first, fallback to
    // any form inside #review_form or #respond (covers Blocksy/WooCommerce variants)
    var form =
      document.getElementById("commentform") ||
      document.querySelector("#review_form form, #respond form");
    if (!form) {
      return;
    }

    var submitBtn = form.querySelector(
      'button[type="submit"], input[type="submit"]',
    );
    if (!submitBtn) {
      return;
    }

    // Use click on submit button instead of form submit event, because
    // form.submit() (called by some plugins) bypasses submit event listeners.
    submitBtn.addEventListener("click", function (e) {
      // Clear previous errors
      var oldErrors = form.querySelectorAll(".blaze-field-error");
      oldErrors.forEach(function (el) {
        el.remove();
      });
      form
        .querySelectorAll(".blaze-field-invalid")
        .forEach(function (el) {
          el.classList.remove("blaze-field-invalid");
        });

      var errors = [];

      // Rating (hidden <select> behind star UI)
      var rating = form.querySelector('select[name="rating"]');
      if (rating && rating.hasAttribute("required") && !rating.value) {
        errors.push({
          field: rating.closest(".comment-form-rating") || rating,
          message: "Please select a rating.",
        });
      }

      // Author name
      var author = form.querySelector('input[name="author"]');
      if (
        author &&
        author.hasAttribute("required") &&
        !author.value.trim()
      ) {
        errors.push({ field: author, message: "Please enter your name." });
      }

      // Email
      var email = form.querySelector('input[name="email"]');
      if (
        email &&
        email.hasAttribute("required") &&
        !email.value.trim()
      ) {
        errors.push({
          field: email,
          message: "Please enter your email address.",
        });
      } else if (email && email.value.trim() && !isValidEmail(email.value)) {
        errors.push({
          field: email,
          message: "Please enter a valid email address.",
        });
      }

      // Comment text
      var comment = form.querySelector('textarea[name="comment"]');
      if (
        comment &&
        comment.hasAttribute("required") &&
        !comment.value.trim()
      ) {
        errors.push({
          field: comment,
          message: "Please write your review.",
        });
      }

      if (errors.length > 0) {
        e.preventDefault();

        errors.forEach(function (err) {
          var wrapper = err.field.closest("p, div") || err.field;
          wrapper.classList.add("blaze-field-invalid");

          var msg = document.createElement("span");
          msg.className = "blaze-field-error";
          msg.textContent = err.message;
          wrapper.appendChild(msg);
        });

        // Scroll to first error
        var firstError = form.querySelector(".blaze-field-invalid");
        if (firstError) {
          var header = document.querySelector(
            "header.site-header, .site-header, #header, [data-sticky]",
          );
          var headerHeight = header ? header.offsetHeight : 0;
          var top =
            firstError.getBoundingClientRect().top +
            window.pageYOffset -
            headerHeight -
            30;
          window.scrollTo({ top: top, behavior: "smooth" });
        }
      }
    });

    // Clear error on field input
    form.addEventListener("input", function (e) {
      var wrapper = e.target.closest(".blaze-field-invalid");
      if (wrapper) {
        wrapper.classList.remove("blaze-field-invalid");
        var err = wrapper.querySelector(".blaze-field-error");
        if (err) err.remove();
      }
    });

    // Clear rating error on star click
    var ratingContainer = form.querySelector(".comment-form-rating");
    if (ratingContainer) {
      ratingContainer.addEventListener("click", function () {
        ratingContainer.classList.remove("blaze-field-invalid");
        var err = ratingContainer.querySelector(".blaze-field-error");
        if (err) err.remove();
      });
    }
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  /**
   * Auto-open reviews tab when URL contains a #comment-{id} hash.
   * Blocksy renders product tabs as accordions — the reviews panel stays
   * collapsed unless explicitly clicked.
   */
  function handleReviewCommentHash() {
    var hash = window.location.hash;
    if (!hash || !/^#(comment-\d+|reviews)$/.test(hash)) {
      return;
    }

    var reviewsTab = document.querySelector(
      '[data-target="#tab-reviews"]',
    );
    if (reviewsTab) {
      reviewsTab.click();
    }

    // Scroll to the specific comment after the accordion opens
    setTimeout(function () {
      var commentEl = document.querySelector(hash);
      if (commentEl) {
        var header = document.querySelector(
          "header.site-header, .site-header, #header, [data-sticky]",
        );
        var headerHeight = header ? header.offsetHeight : 0;
        var top =
          commentEl.getBoundingClientRect().top +
          window.pageYOffset -
          headerHeight -
          30;
        window.scrollTo({ top: top, behavior: "smooth" });
      }
    }, 400);
  }

  /**
   * Show a success notice after a product review is submitted.
   * The status (approved | hold) is passed via blazeBlocksySingleProduct.reviewSubmitted.
   */
  function handleReviewSubmissionSuccess() {
    if (
      typeof blazeBlocksySingleProduct === "undefined" ||
      !blazeBlocksySingleProduct.reviewSubmitted
    ) {
      return;
    }

    var status = blazeBlocksySingleProduct.reviewSubmitted;
    var isApproved = status === "approved";

    var message = isApproved
      ? "Thank you! Your review has been posted successfully."
      : "Thank you! Your review has been submitted and is awaiting moderation.";

    var icon = isApproved
      ? '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 0C4.48 0 0 4.48 0 10s4.48 10 10 10 10-4.48 10-10S15.52 0 10 0zm-1 15l-5-5 1.41-1.41L9 12.17l6.59-6.59L17 7l-8 8z" fill="currentColor"/></svg>'
      : '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 0C4.48 0 0 4.48 0 10s4.48 10 10 10 10-4.48 10-10S15.52 0 10 0zm1 15H9v-2h2v2zm0-4H9V5h2v6z" fill="currentColor"/></svg>';

    // Open reviews tab
    var reviewsTab = document.querySelector(
      '[data-target="#tab-reviews"]',
    );
    if (reviewsTab) {
      reviewsTab.click();
    }

    // Create the notice element
    var notice = document.createElement("div");
    notice.className =
      "blaze-review-success-notice" +
      (isApproved ? " is-approved" : " is-hold");
    notice.innerHTML =
      '<span class="blaze-review-success-icon">' +
      icon +
      "</span>" +
      "<p>" +
      message +
      "</p>";

    // Insert into the reviews tab content area
    var reviewsPanel = document.querySelector("#tab-reviews");
    if (reviewsPanel) {
      // Look for the expandable content wrapper inside the accordion tab
      var content = reviewsPanel.querySelector(".ct-expandable-content");
      var target = content || reviewsPanel;
      target.insertBefore(notice, target.firstChild);
    }

    // Scroll to the notice
    setTimeout(function () {
      var header = document.querySelector(
        "header.site-header, .site-header, #header, [data-sticky]",
      );
      var headerHeight = header ? header.offsetHeight : 0;
      var top =
        notice.getBoundingClientRect().top +
        window.pageYOffset -
        headerHeight -
        30;
      window.scrollTo({ top: top, behavior: "smooth" });
    }, 400);

    // Clean query params from URL
    if (window.history && window.history.replaceState) {
      var url = new URL(window.location.href);
      url.searchParams.delete("review_submitted");
      url.searchParams.delete("unapproved");
      url.searchParams.delete("moderation-hash");
      window.history.replaceState({}, "", url.toString());
    }
  }

  /**
   * Initialize MutationObserver to watch for WooCommerce notices
   * When notices (errors/alerts) are added after AJAX add to cart, scroll to them
   */
  function initNoticesObserver() {
    const noticesWrapper = document.querySelector(
      ".woocommerce-notices-wrapper",
    );

    if (!noticesWrapper) {
      return;
    }

    // Create observer to watch for added notices
    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          // Check if the added node is an error notice
          if (node.nodeType === Node.ELEMENT_NODE) {
            const isError =
              node.classList.contains("woocommerce-error") ||
              node.classList.contains("woocommerce-message") ||
              node.classList.contains("wc-block-components-notice-banner");

            if (isError) {
              scrollToNotice(node);
            }
          }
        });
      });
    });

    // Start observing
    observer.observe(noticesWrapper, {
      childList: true,
      subtree: true,
    });
  }

  /**
   * Scroll to the notice element with smooth animation
   * @param {HTMLElement} noticeElement - The notice element to scroll to
   */
  function scrollToNotice(noticeElement) {
    if (!noticeElement) {
      return;
    }

    // Small delay to ensure the element is fully rendered
    setTimeout(function () {
      // Get actual header height dynamically
      const header = document.querySelector(
        "header.site-header, .site-header, #header, [data-sticky]",
      );
      const headerHeight = header ? header.offsetHeight : 0;

      // Get configurable offset from localized data, fallback to default
      const extraPadding =
        typeof blazeBlocksySingleProduct !== "undefined" &&
        blazeBlocksySingleProduct.scrollOffsetPadding !== undefined
          ? parseInt(blazeBlocksySingleProduct.scrollOffsetPadding, 10)
          : -60;

      const headerOffset = headerHeight + extraPadding;

      const elementPosition = noticeElement.getBoundingClientRect().top;
      const offsetPosition =
        elementPosition + window.pageYOffset - headerOffset - 120;

      window.scrollTo({
        top: offsetPosition,
        behavior: "smooth",
      });
    }, 100);
  }
  /**
   * Initialize Product Information Offcanvas functionality
   */
  function initProductInformationOffcanvas() {
    var ctProductInformationOffCanvas = document.getElementById(
      "ct-product-information-offcanvas",
    );
    var ctProductInformationOffCanvasOverlay = document.getElementById(
      "ct-product-information-offcanvas-overlay",
    );

    // Skip if elements don't exist
    if (
      !ctProductInformationOffCanvas ||
      !ctProductInformationOffCanvasOverlay
    ) {
      return;
    }

    // Open offcanvas function
    window.openOffcanvas = function (tab) {
      ctProductInformationOffCanvasOverlay.classList.add("active");
      ctProductInformationOffCanvas.classList.add("active");
      switchTab(tab);
    };

    // Close offcanvas function
    window.closeOffcanvas = function () {
      ctProductInformationOffCanvasOverlay.classList.remove("active");
      ctProductInformationOffCanvas.classList.remove("active");
    };

    // Switch tab function
    function switchTab(tabName) {
      var tabs = document.querySelectorAll(".offcanvas-tab");
      var contents = document.querySelectorAll(".tab-content");

      tabs.forEach(function (tab) {
        tab.classList.remove("active");
      });
      contents.forEach(function (content) {
        content.classList.remove("active");
      });

      var selectedTab = document.querySelector('[data-tab="' + tabName + '"]');
      var selectedContent = document.getElementById(tabName + "-content");

      if (selectedTab) selectedTab.classList.add("active");
      if (selectedContent) selectedContent.classList.add("active");
    }

    // Add click event listeners to tabs
    document.querySelectorAll(".offcanvas-tab").forEach(function (tab) {
      tab.addEventListener("click", function () {
        var tabName = this.getAttribute("data-tab");
        switchTab(tabName);
      });
    });

    // Close offcanvas when pressing Escape key
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        window.closeOffcanvas();
      }
    });
  }

  /**
   * Initialize Product Full Description show/less toggle functionality
   */
  function initProductFullDescriptionToggle() {
    $(".ct-product-full-description-element").each(function () {
      var container = $(this);
      var content = container.find(".ct-full-description-content");
      var toggle = container.find(".ct-full-description-toggle");
      var maxLines = parseInt(container.data("max-lines")) || 4;
      var lineHeight = parseFloat(content.css("line-height")) || 24;
      var maxHeight = maxLines * lineHeight;

      // Check if content exceeds max lines
      if (content[0] && content[0].scrollHeight > maxHeight + 5) {
        container.addClass("is-truncated");
        content.css("max-height", maxHeight + "px");
        toggle.show();
      } else {
        toggle.hide();
      }

      // Toggle click handler
      toggle.on("click", function (e) {
        e.preventDefault();
        if (container.hasClass("is-expanded")) {
          container.removeClass("is-expanded").addClass("is-truncated");
          content.css("max-height", maxHeight + "px");
        } else {
          container.removeClass("is-truncated").addClass("is-expanded");
          content.css("max-height", "none");
        }
      });
    });
  }

  // Initialize Product Full Description toggle and Product Information Offcanvas on document ready
  $(document).ready(function () {
    initProductFullDescriptionToggle();
    initProductInformationOffcanvas();
  });
})(jQuery);
