/**
 * Product Geo-Restriction - Frontend JavaScript
 *
 * Handles geolocation detection, reverse geocoding, and UI manipulation
 * for product geographic restrictions.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  // Configuration
  const CONFIG = {
    CACHE_KEY: "blaze_user_location",
    CACHE_TIMESTAMP_KEY: "blaze_location_timestamp",
    CACHE_DURATION: 24 * 60 * 60 * 1000, // 24 hours
    GEOCODING_TIMEOUT: 10000, // 10 seconds
    NOMINATIM_API: "https://nominatim.openstreetmap.org/reverse",
  };

  // State management
  let userState = null;

  /**
   * Initialize geo-restriction on page load
   */
  $(document).ready(function () {
    // Check if geo-restriction is enabled for this product
    if (
      typeof window.blazeGeoRestrictionData === "undefined" ||
      !window.blazeGeoRestrictionData.enabled
    ) {
      return;
    }

    log("Geo-restriction enabled for this product");
    initGeoRestriction();
  });

  /**
   * Initialize geo-restriction process
   */
  function initGeoRestriction() {
    // Show loading state
    showLoadingState();

    // Check cache first
    const cachedState = getCachedLocation();
    if (cachedState) {
      log("Using cached location:", cachedState);
      userState = cachedState;
      checkRestriction(cachedState);
      return;
    }

    // Try to get user location
    getUserLocation()
      .then((coords) => {
        log("Got coordinates:", coords);
        return reverseGeocode(coords.latitude, coords.longitude);
      })
      .then((state) => {
        log("Detected state:", state);
        userState = state;
        cacheLocation(state);
        checkRestriction(state);
      })
      .catch((error) => {
        // Differentiate between detection errors and non-US locations
        if (error.type === "NON_US_LOCATION") {
          log("Location detected but outside US:", error.location);
          handleNonUSLocation(error.location);
        } else {
          log("Error detecting location:", error.message);
          handleLocationError(error.message);
        }
      });
  }

  /**
   * Get user's geographic coordinates using browser Geolocation API
   *
   * @returns {Promise} Resolves with {latitude, longitude}
   */
  function getUserLocation() {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject(new Error("Geolocation not supported"));
        return;
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          resolve({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
          });
        },
        (error) => {
          let errorMessage = "Location access denied";
          switch (error.code) {
            case error.PERMISSION_DENIED:
              errorMessage = "Location permission denied by user";
              break;
            case error.POSITION_UNAVAILABLE:
              errorMessage = "Location information unavailable";
              break;
            case error.TIMEOUT:
              errorMessage = "Location request timeout";
              break;
          }
          reject(new Error(errorMessage));
        },
        {
          enableHighAccuracy: false,
          timeout: 10000,
          maximumAge: 0,
        }
      );
    });
  }

  /**
   * Reverse geocode coordinates to get US state
   * Uses Nominatim (OpenStreetMap) free API
   *
   * @param {number} lat Latitude
   * @param {number} lon Longitude
   * @returns {Promise} Resolves with state code (e.g., 'TX')
   */
  function reverseGeocode(lat, lon) {
    return new Promise((resolve, reject) => {
      const url = `${CONFIG.NOMINATIM_API}?format=json&lat=${lat}&lon=${lon}&addressdetails=1`;

      // Set timeout
      const timeoutId = setTimeout(() => {
        reject(new Error("Geocoding timeout"));
      }, CONFIG.GEOCODING_TIMEOUT);

      fetch(url, {
        headers: {
          "User-Agent": "BlazeCommerce-GeoRestriction/1.0",
        },
      })
        .then((response) => {
          clearTimeout(timeoutId);
          if (!response.ok) {
            throw new Error("Geocoding API error");
          }
          return response.json();
        })
        .then((data) => {
          log("Geocoding response:", data);

          // Extract state from response
          const address = data.address || {};
          const state = address.state || address.region || null;

          if (!state) {
            reject(new Error("Could not determine state"));
            return;
          }

          // Convert state name to code
          const stateCode = getStateCodeFromName(state);
          if (stateCode) {
            resolve(stateCode);
          } else {
            // Location detected successfully, but not a US state
            const error = new Error("Location detected outside US: " + state);
            error.type = "NON_US_LOCATION";
            error.location = state;
            reject(error);
          }
        })
        .catch((error) => {
          clearTimeout(timeoutId);
          reject(error);
        });
    });
  }

  /**
   * Convert state name to state code
   *
   * @param {string} stateName Full state name
   * @returns {string|null} State code or null
   */
  function getStateCodeFromName(stateName) {
    if (!stateName || typeof blazeGeoRestriction === "undefined") {
      return null;
    }

    const states = blazeGeoRestriction.states || {};
    const normalizedName = stateName.toLowerCase().trim();

    // Find matching state
    for (const [code, name] of Object.entries(states)) {
      if (name.toLowerCase() === normalizedName) {
        return code;
      }
    }

    return null;
  }

  /**
   * Check if user's state is allowed for this product
   *
   * @param {string} stateCode User's state code
   */
  function checkRestriction(stateCode) {
    const data = window.blazeGeoRestrictionData;

    // If no allowed states specified, allow all
    if (!data.allowedStates || data.allowedStates.length === 0) {
      log("No restrictions - allowing all states");
      hideLoadingState();
      return;
    }

    // Check if user's state is in allowed list
    const isAllowed = data.allowedStates.includes(stateCode);

    log("State check:", {
      userState: stateCode,
      allowedStates: data.allowedStates,
      isAllowed: isAllowed,
    });

    if (isAllowed) {
      // User is in allowed state - show add to cart button
      hideLoadingState();
    } else {
      // User is NOT in allowed state - show restriction message
      showRestrictionMessage(stateCode);
    }
  }

  /**
   * Show restriction message and hide add to cart button
   *
   * @param {string} stateCode User's state code
   */
  function showRestrictionMessage(stateCode) {
    hideLoadingState();

    const data = window.blazeGeoRestrictionData;
    const stateName = getStateName(stateCode);

    // Hide add to cart button and quantity selector
    $(".single_add_to_cart_button").hide();
    $(".quantity").hide();
    $(".cart").addClass("geo-restricted");

    // Create restriction message
    const allowedStatesText =
      data.allowedStatesNames && data.allowedStatesNames.length > 0
        ? data.allowedStatesNames.join(", ")
        : "selected states";

    const messageHtml = `
            <div class="geo-restriction-message">
                <div class="geo-restriction-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="geo-restriction-content">
                    <p class="geo-restriction-title">${data.restrictionMessage}</p>
                </div>
            </div>
        `;

    // Insert message before the cart form or after product summary
    if ($("form.cart").length) {
      $("form.cart").prepend(messageHtml);
    } else {
      $(".product .summary").append(messageHtml);
    }

    // Add CSS class to body for additional styling
    $("body").addClass("product-geo-restricted");
  }

  /**
   * Handle non-US location detection
   *
   * @param {string} location Detected location name (e.g., "England")
   */
  function handleNonUSLocation(location) {
    hideLoadingState();

    const data = window.blazeGeoRestrictionData;

    // Hide add to cart button and quantity selector
    $(".single_add_to_cart_button").hide();
    $(".quantity").hide();
    $(".cart").addClass("geo-restricted");

    // Create message for non-US location
    const allowedStatesText =
      data.allowedStatesNames && data.allowedStatesNames.length > 0
        ? data.allowedStatesNames.join(", ")
        : "US states only";

    const messageHtml = `
            <div class="geo-restriction-message geo-restriction-non-us">
                <div class="geo-restriction-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                    </svg>
                </div>
                <div class="geo-restriction-content">
                    <p class="geo-restriction-title">${data.restrictionMessage}</p>
                </div>
            </div>
        `;

    // Insert message before the cart form
    if ($("form.cart").length) {
      $("form.cart").prepend(messageHtml);
    } else {
      $(".product .summary").append(messageHtml);
    }

    // Add CSS class to body
    $("body").addClass("product-geo-restricted");
  }

  /**
   * Handle location detection error
   *
   * @param {string} errorMessage Error message
   */
  function handleLocationError(errorMessage) {
    hideLoadingState();

    const data = window.blazeGeoRestrictionData;

    // Hide add to cart button and quantity selector
    $(".single_add_to_cart_button").hide();
    $(".quantity").hide();
    $(".cart").addClass("geo-restricted");

    const messageHtml = `
            <div class="geo-restriction-message geo-restriction-error">
                <div class="geo-restriction-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="geo-restriction-content">
                    <p class="geo-restriction-title">${data.restrictionMessage}</p>
                </div>
            </div>
        `;

    // Insert message before the cart form
    if ($("form.cart").length) {
      $("form.cart").prepend(messageHtml);
    } else {
      $(".product .summary").append(messageHtml);
    }

    // Add CSS class to body
    $("body").addClass("product-geo-restricted");
  }

  /**
   * Show loading state
   */
  function showLoadingState() {
    const loadingHtml = `
            <div class="geo-restriction-loading">
                <div class="geo-restriction-spinner"></div>
                <p>Verifying location availability...</p>
            </div>
        `;

    if ($("form.cart").length) {
      $("form.cart").prepend(loadingHtml);
    }

    // Hide add to cart button during loading
    $(".single_add_to_cart_button").css("opacity", "0.5");
    $(".quantity").css("opacity", "0.5");
  }

  /**
   * Hide loading state
   */
  function hideLoadingState() {
    $(".geo-restriction-loading").remove();
    $(".single_add_to_cart_button").css("opacity", "1");
    $(".quantity").css("opacity", "1");
  }

  /**
   * Cache user location in localStorage
   *
   * @param {string} stateCode State code to cache
   */
  function cacheLocation(stateCode) {
    try {
      localStorage.setItem(CONFIG.CACHE_KEY, stateCode);
      localStorage.setItem(CONFIG.CACHE_TIMESTAMP_KEY, Date.now().toString());
      log("Location cached:", stateCode);
    } catch (e) {
      log("Failed to cache location:", e);
    }
  }

  /**
   * Get cached location if valid
   *
   * @returns {string|null} Cached state code or null
   */
  function getCachedLocation() {
    try {
      const cached = localStorage.getItem(CONFIG.CACHE_KEY);
      const timestamp = localStorage.getItem(CONFIG.CACHE_TIMESTAMP_KEY);

      if (!cached || !timestamp) {
        return null;
      }

      const age = Date.now() - parseInt(timestamp);
      if (age > CONFIG.CACHE_DURATION) {
        // Cache expired
        localStorage.removeItem(CONFIG.CACHE_KEY);
        localStorage.removeItem(CONFIG.CACHE_TIMESTAMP_KEY);
        return null;
      }

      return cached;
    } catch (e) {
      log("Failed to get cached location:", e);
      return null;
    }
  }

  /**
   * Get state name from code
   *
   * @param {string} stateCode State code
   * @returns {string} State name
   */
  function getStateName(stateCode) {
    if (typeof blazeGeoRestriction === "undefined") {
      return stateCode;
    }

    const states = blazeGeoRestriction.states || {};
    return states[stateCode] || stateCode;
  }

  /**
   * Debug logging
   *
   * @param {...any} args Arguments to log
   */
  function log(...args) {
    if (
      typeof blazeGeoRestriction !== "undefined" &&
      blazeGeoRestriction.debug
    ) {
      console.log("[Geo-Restriction]", ...args);
    }
  }
})(jQuery);
