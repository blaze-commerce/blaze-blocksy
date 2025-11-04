/**
 * Responsive Button Slider
 * Converts wp-block-buttons with class 'responsive-slider-buttons' into Owl Carousel slider
 * when content overflows the container width
 */
(function ($) {
  "use strict";

  // Configuration for button slider
  const buttonSliderConfig = {
    items: 1,
    margin: 10,
    nav: false, // No arrows, only dots
    dots: true,
    loop: false,
    autoWidth: true,
    responsive: {
      0: {
        // Mobile
        items: 1,
        stagePadding: 20,
      },
      600: {
        // Tablet
        items: 2,
        stagePadding: 30,
      },
      1024: {
        // Desktop
        items: 3,
        stagePadding: 0,
      },
    },
  };

  /**
   * Check if buttons overflow the container
   */
  function checkOverflow($container) {
    // Get all button elements
    const $buttons = $container.find(".wp-block-button");

    if ($buttons.length === 0) return false;

    // Method 1: Use scrollWidth (most reliable for flex containers)
    const containerElement = $container[0];
    const hasScrollableContent =
      containerElement.scrollWidth > containerElement.clientWidth;

    // Method 2: Calculate total width of buttons
    let totalWidth = 0;
    $buttons.each(function () {
      const $button = $(this);
      // Get the button wrapper width
      const buttonWidth = $button.outerWidth(true); // Include margin
      totalWidth += buttonWidth;
    });

    const containerWidth = $container.width();

    console.log("=== Overflow Detection ===");
    console.log("Method 1 - scrollWidth:", containerElement.scrollWidth);
    console.log("Method 1 - clientWidth:", containerElement.clientWidth);
    console.log("Method 1 - Has overflow:", hasScrollableContent);
    console.log("Method 2 - Total buttons width:", totalWidth);
    console.log("Method 2 - Container width:", containerWidth);
    console.log("Method 2 - Overflow:", totalWidth > containerWidth);

    // Use Method 1 (scrollWidth) as primary detection
    // Fallback to Method 2 if needed
    return hasScrollableContent || totalWidth > containerWidth + 20;
  }

  /**
   * Initialize Owl Carousel on button container
   */
  function initButtonSlider($container) {
    // Check if already initialized
    if ($container.hasClass("owl-carousel")) {
      return;
    }

    // Add owl-carousel classes
    $container.addClass("owl-carousel owl-theme");

    // Initialize Owl Carousel
    $container.owlCarousel(buttonSliderConfig);

    // Mark as initialized
    $container.data("slider-initialized", true);
  }

  /**
   * Destroy Owl Carousel and restore normal layout
   */
  function destroyButtonSlider($container) {
    // Check if initialized
    if (!$container.data("slider-initialized")) {
      return;
    }

    // Destroy Owl Carousel
    if ($container.data("owl.carousel")) {
      $container.trigger("destroy.owl.carousel");
      $container.removeClass(
        "owl-carousel owl-theme responsive-buttons-slider"
      );
    }

    // Mark as not initialized
    $container.data("slider-initialized", false);
  }

  /**
   * Toggle slider based on overflow detection
   */
  function toggleButtonSlider($container) {
    const needsSlider = checkOverflow($container);

    console.log("Button slider needs slider:", needsSlider);

    if (needsSlider) {
      initButtonSlider($container);
    } else {
      destroyButtonSlider($container);
    }
  }

  /**
   * Debounce function to limit resize event calls
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

  /**
   * Initialize all responsive button sliders on the page
   */
  function initResponsiveButtonSliders() {
    $(".responsive-slider-buttons").each(function () {
      const $container = $(this);
      console.log("Initializing button slider for container:", $container);
      toggleButtonSlider($container);
    });
  }

  /**
   * Handle window resize
   */
  const handleResize = debounce(function () {
    $(".responsive-slider-buttons").each(function () {
      const $container = $(this);

      // Temporarily destroy to recalculate
      if ($container.data("slider-initialized")) {
        destroyButtonSlider($container);
      }

      // Re-check and toggle
      setTimeout(function () {
        toggleButtonSlider($container);
      }, 100);
    });
  }, 250);

  /**
   * Document ready
   */
  $(document).ready(function () {
    // Wait for Owl Carousel to be available
    if (typeof $.fn.owlCarousel !== "undefined") {
      // Small delay to ensure all styles are loaded
      setTimeout(function () {
        initResponsiveButtonSliders();
      }, 100);

      // Handle window resize
      $(window).on("resize", handleResize);
    } else {
      console.warn(
        "Owl Carousel not loaded. Responsive button slider disabled."
      );
    }
  });
})(jQuery);
