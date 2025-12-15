/**
 * BlazeCommerce Minicart Control
 * Handles minicart opening/closing and cart flow modifications
 *
 * This script modifies the WooCommerce cart flow to:
 * - Redirect users from product pages to homepage after adding to cart
 * - Automatically open the minicart panel on homepage
 * - Bypass the standard /cart page entirely
 * - Maintain full WooCommerce cart fragments compatibility
 */

// Critical dependency validation to prevent runtime crashes
(function () {
  "use strict";

  // Check for required WooCommerce dependencies
  if (typeof wc_add_to_cart_params === "undefined") {
    return;
  }

  // Check for jQuery dependency
  if (typeof jQuery === "undefined") {
    return;
  }
})();

// Input validation function to prevent malicious input processing
function validateProductId(productId) {
  if (!productId) {
    return null;
  }

  // Convert to string and trim whitespace
  const cleanId = String(productId).trim();

  // Check if it's a valid positive integer
  const numericId = parseInt(cleanId, 10);
  if (isNaN(numericId) || numericId <= 0 || numericId.toString() !== cleanId) {
    return null;
  }

  return numericId;
}
// Simple minicart control functions using Method 1 (most reliable)
function openMinicart() {
  const cartTrigger = document.querySelector('a[href="#woo-cart-panel"]');
  if (cartTrigger) {
    cartTrigger.click();
  }
}

function closeMinicart() {
  const closeButton = document.querySelector('button[aria-label*="Close"]');
  if (closeButton) {
    closeButton.click();
  }
}

// Check if minicart is currently open
function isMinicartOpen() {
  const cartPanel = document.querySelector(
    'dialog[aria-label="Shopping cart panel"]'
  );
  const cartTrigger = document.querySelector('a[href="#woo-cart-panel"]');

  return (
    cartPanel &&
    (cartPanel.hasAttribute("open") ||
      cartPanel.getAttribute("aria-expanded") === "true" ||
      cartTrigger?.getAttribute("aria-expanded") === "true")
  );
}

// Initialize BlazeCommerce cart flow modifications
function initializeBlazeCommerceCartFlow() {
  // Wait for DOM to be ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupCartFlow);
  } else {
    setupCartFlow();
  }
}

function setupCartFlow() {
  // Override add to cart form submissions
  document.addEventListener("submit", function (e) {
    const form = e.target;

    // Check if this is an add to cart form
    if (
      form.classList.contains("cart") ||
      form.querySelector('button[name="add-to-cart"]')
    ) {
      e.preventDefault();
      handleAddToCartSubmission(form);
    }
  });

  // Also handle direct button clicks (for AJAX add to cart)
  document.addEventListener("click", function (e) {
    const button = e.target.closest(
      'button[name="add-to-cart"], .single_add_to_cart_button'
    );
    if (button) {
      e.preventDefault();
      handleAddToCartClick(button);
    }
  });

  // Add edit link to checkout page if we're on checkout
  if (isCheckoutPage()) {
    addEditLinkToOrderSummary();
  }
}

function handleAddToCartSubmission(form) {
  const formData = new FormData(form);
  const rawProductId =
    formData.get("add-to-cart") || formData.get("product_id");
  const quantity = formData.get("quantity") || 1;

  // Validate product ID before processing
  const productId = validateProductId(rawProductId);
  if (!productId) {
    return;
  }

  // Add to cart via AJAX
  addToCartAjax(productId, quantity);
}

function handleAddToCartClick(button) {
  const rawProductId = button.value || button.getAttribute("data-product_id");
  const quantity = 1;

  // Validate product ID before processing
  const productId = validateProductId(rawProductId);
  if (!productId) {
    return;
  }

  // Add to cart via AJAX
  addToCartAjax(productId, quantity);
}

function addToCartAjax(productId, quantity) {
  // Validate dependencies before proceeding
  if (
    typeof wc_add_to_cart_params === "undefined" ||
    !wc_add_to_cart_params.wc_ajax_url
  ) {
    document.body.classList.remove("adding-to-cart");
    return;
  }

  // Show loading state
  document.body.classList.add("adding-to-cart");

  // Prepare AJAX data
  const data = new FormData();
  data.append("action", "woocommerce_add_to_cart");
  data.append("product_id", productId);
  data.append("quantity", quantity);
  // Send AJAX request
  fetch(
    wc_add_to_cart_params.wc_ajax_url.replace("%%endpoint%%", "add_to_cart"),
    {
      method: "POST",
      body: data,
    }
  )
    .then((response) => response.json())
    .then((result) => {
      document.body.classList.remove("adding-to-cart");

      if (result.error) {
        return;
      }

      // Update cart fragments if available
      if (result.fragments) {
        updateCartFragments(result.fragments);
      }

      // Redirect to homepage and open minicart
      redirectToHomepageWithMinicart();
    })
    .catch(() => {
      document.body.classList.remove("adding-to-cart");
    });
}

function updateCartFragments(fragments) {
  // Update cart fragments using jQuery if available
  if (typeof jQuery !== "undefined") {
    jQuery.each(fragments, function (key, value) {
      jQuery(key).replaceWith(value);
    });
  }
}

function redirectToHomepageWithMinicart() {
  // Get the homepage URL
  const homeUrl = window.location.origin;

  // If we're already on homepage, just open minicart
  if (window.location.pathname === "/" || window.location.pathname === "") {
    setTimeout(() => {
      openMinicart();
    }, 500);
    return;
  }

  // Store flag to open minicart after redirect
  sessionStorage.setItem("blazecommerce_open_minicart", "true");

  // Redirect to homepage
  window.location.href = homeUrl;
}

// Check if we're on the checkout page
function isCheckoutPage() {
  return (
    window.location.pathname.includes("/checkout") ||
    document.body.classList.contains("woocommerce-checkout") ||
    document.querySelector(".woocommerce-checkout")
  );
}

// Add edit link to order summary on checkout page
function addEditLinkToOrderSummary() {
  // Wait for order summary to load
  setTimeout(() => {
    let orderSummaryHeading = null;

    // First priority: Look for WooCommerce Blocks order summary title
    orderSummaryHeading = document.querySelector(
      ".wc-block-components-checkout-order-summary__title"
    );

    if (!orderSummaryHeading) {
      // Second priority: Look for other WooCommerce order summary selectors
      orderSummaryHeading =
        document.querySelector("#order_review h3") ||
        document.querySelector(".woocommerce-checkout-review-order h3") ||
        document.querySelector(
          '[class*="order-summary"] h2, [class*="order-summary"] h3, [class*="order-summary"] h4'
        );
    }

    if (!orderSummaryHeading) {
      // Third priority: Find heading by text content (main Order Summary only)
      const headings = document.querySelectorAll("h1, h2, h3, h4, h5, h6");
      for (const heading of headings) {
        const text = heading.textContent.toLowerCase().trim();
        // Only match exact "order summary" text, not product titles
        if (text === "order summary" || text === "order review") {
          orderSummaryHeading = heading;
          break;
        }
      }
    }

    if (!orderSummaryHeading) {
      return;
    }

    addEditLinkToHeading(orderSummaryHeading);
  }, 1000);
}

// Add edit link to a specific heading element
function addEditLinkToHeading(heading) {
  // Check if edit link already exists
  if (heading.querySelector(".blazecommerce-edit-link")) {
    return;
  }

  // Create edit link
  const editLink = document.createElement("a");
  editLink.href = "#";
  editLink.className = "blazecommerce-edit-link";
  editLink.textContent = "Edit";
  // Apply styles individually to prevent XSS vulnerability
  editLink.style.float = "right";
  editLink.style.margin = "0 16px 16px 16px";

  // Add hover effect
  editLink.addEventListener("mouseenter", () => {
    editLink.style.textDecoration = "underline";
  });
  editLink.addEventListener("mouseleave", () => {
    editLink.style.textDecoration = "none";
  });

  // Add click handler
  editLink.addEventListener("click", handleEditLinkClick);

  // Append to heading
  heading.appendChild(editLink);
}

// Handle edit link click
function handleEditLinkClick(e) {
  e.preventDefault();

  // Use the same redirect mechanism as add to cart
  redirectToHomepageWithMinicart();
}

// Check if we should open minicart after page load (after redirect)
function checkForMinicartOpen() {
  if (sessionStorage.getItem("blazecommerce_open_minicart") === "true") {
    sessionStorage.removeItem("blazecommerce_open_minicart");

    // Wait a bit for the page to fully load
    setTimeout(() => {
      openMinicart();
    }, 1000);
  }
}

// Initialize everything when script loads
initializeBlazeCommerceCartFlow();

// Safe initialization to prevent race condition and double execution
let minicartInitExecuted = false;

function safeCheckForMinicartOpen() {
  if (minicartInitExecuted) {
    return;
  }
  minicartInitExecuted = true;
  checkForMinicartOpen();
}

// Check for minicart open flag on page load with race condition protection
document.addEventListener("DOMContentLoaded", safeCheckForMinicartOpen);

// Also check if DOM is already loaded
if (document.readyState !== "loading") {
  safeCheckForMinicartOpen();
}

// Expose functions globally for debugging
window.BlazeCommerceMinicart = {
  open: openMinicart,
  close: closeMinicart,
  isOpen: isMinicartOpen,
  addEditLink: addEditLinkToOrderSummary,
  redirectToHomepage: redirectToHomepageWithMinicart,
};
