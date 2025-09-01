# Technical Documentation: WooCommerce Filter Counter Update

## Project Overview

**Objective**: Implement automatic update of the "SHOWING X-Y OF Z RESULTS" counter when users apply filters on WooCommerce shop/category pages.

**Current Behavior**: Counter may not update correctly after filtering, showing outdated product counts.

**Expected Behavior**: Counter updates dynamically to reflect filtered results (e.g., "SHOWING 1-8 OF 25 RESULTS" after applying price filter).

## Technical Analysis

### Filter System Architecture

- **Theme**: Blocksy WordPress Theme with extensive filter plugin support
- **Filter Types**: Price, Category, Attribute, Brand, Rating, Stock Status
- **Integration**: Multiple filter plugin compatibility
- **AJAX Implementation**: Real-time filtering without page reload

### Supported Filter Plugins

#### 1. Popular Filter Plugins

- **YITH WooCommerce Ajax Product Filter** (`yith-wcan-ajax-filtered`)
- **BeRocket AJAX Product Filters** (`berocket_ajax_filtering_end`)
- **FacetWP** (`facetwp-loaded`)
- **Jet Smart Filters** (`jet-filter-content-rendered`)
- **WooCommerce Product Filter** (`wpf_ajax_success`)
- **SearchAndFilter** (`sf:ajaxfinish`)
- **WooCommerce Product Table** (`draw.wcpt`)

#### 2. Built-in WooCommerce Events

- `wc_fragments_refreshed`
- `wc_fragments_loaded`
- `updated_wc_div`
- `found_variation`
- `reset_data`

### Key Files and Components

#### 1. Filter Event Handling

- **File**: `static/js/frontend/handle-3rd-party-events.js`
- **Purpose**: Centralized event handling for filter plugins
- **Events Monitored**: All major filter plugin completion events

#### 2. WooCommerce Event Integration

- **File**: `static/js/frontend/woocommerce/handle-events.js`
- **Purpose**: WooCommerce-specific event handling
- **Integration**: Core WooCommerce AJAX events

#### 3. Theme Event System

- **Custom Event**: `blocksy:frontend:init`
- **Custom Event**: `blocksy:ajax:filters:done`
- **Purpose**: Theme-level re-initialization after content changes

### DOM Structure Analysis

#### Filter-Related Elements

```html
<!-- Result Counter -->
<p class="woocommerce-result-count">Showing 1–16 of 163 results</p>

<!-- Products Container -->
<ul class="products columns-4">
  <li class="product">...</li>
  <!-- Filtered products appear here -->
</ul>

<!-- Filter Widgets (varies by plugin) -->
<div class="widget_price_filter">...</div>
<div class="yith-wcan-filters">...</div>
<div class="berocket_aapf_widget">...</div>
```

#### Filter State Indicators

```html
<!-- Active Filters Display -->
<div class="woocommerce-active-filters">
  <ul>
    <li><a href="#">Price: $10 - $50 ×</a></li>
    <li><a href="#">Color: Blue ×</a></li>
  </ul>
</div>
```

## Filter Event Detection Strategy

### Primary Event Listeners

```javascript
// Major filter plugin events
const filterEvents = [
  "berocket_ajax_filtering_end", // BeRocket
  "yith-wcan-ajax-filtered", // YITH
  "facetwp-loaded", // FacetWP
  "jet-filter-content-rendered", // Jet Smart Filters
  "wpf_ajax_success", // WP Filter
  "sf:ajaxfinish", // SearchAndFilter
  "prdctfltr-reload", // Product Filter Pro
  "wc_fragments_refreshed", // WooCommerce
  "blocksy:ajax:filters:done", // Blocksy theme
];
```

### Event Binding Implementation

```javascript
// Listen for filter completion events
filterEvents.forEach((eventName) => {
  if (eventName.startsWith("blocksy:")) {
    // Custom theme events
    ctEvents.on(eventName, updateFilterCounter);
  } else if (eventName.startsWith("wc_")) {
    // WooCommerce events
    $(document.body).on(eventName, updateFilterCounter);
  } else {
    // Third-party plugin events
    $(document).on(eventName, updateFilterCounter);
  }
});
```

## Implementation Strategy

### Recommended Approach: Enhanced Plugin

#### Plugin Structure

```
woo-filter-counter-update/
├── woo-filter-counter-update.php
├── assets/
│   ├── js/
│   │   └── filter-counter.js
│   └── css/
│       └── filter-counter.css (optional)
├── includes/
│   ├── class-filter-detector.php
│   ├── class-counter-calculator.php
│   └── class-event-manager.php
└── readme.txt
```

### Core Functionality Requirements

#### 1. Filter Detection System

```javascript
class FilterDetector {
  constructor() {
    this.filterEvents = [
      "berocket_ajax_filtering_end",
      "yith-wcan-ajax-filtered",
      "facetwp-loaded",
      "jet-filter-content-rendered",
      "wpf_ajax_success",
      "sf:ajaxfinish",
      "wc_fragments_refreshed",
      "blocksy:ajax:filters:done",
    ];

    this.bindEvents();
  }

  bindEvents() {
    this.filterEvents.forEach((event) => {
      this.bindEvent(event);
    });
  }

  bindEvent(eventName) {
    const delay = this.getEventDelay(eventName);

    if (eventName.startsWith("blocksy:")) {
      ctEvents.on(eventName, () => {
        setTimeout(() => this.onFilterComplete(), delay);
      });
    } else if (eventName.startsWith("wc_")) {
      $(document.body).on(eventName, () => {
        setTimeout(() => this.onFilterComplete(), delay);
      });
    } else {
      $(document).on(eventName, () => {
        setTimeout(() => this.onFilterComplete(), delay);
      });
    }
  }

  getEventDelay(eventName) {
    // Different plugins need different delays
    const delays = {
      berocket_ajax_filtering_end: 100,
      "yith-wcan-ajax-filtered": 200,
      "facetwp-loaded": 50,
      "jet-filter-content-rendered": 150,
      wpf_ajax_success: 100,
      "sf:ajaxfinish": 100,
      wc_fragments_refreshed: 200,
      "blocksy:ajax:filters:done": 50,
    };

    return delays[eventName] || 100;
  }

  onFilterComplete() {
    // Trigger counter update
    window.FilterCounterUpdater?.updateCounter();
  }
}
```

#### 2. Counter Calculation Logic

```javascript
class CounterCalculator {
  calculateFilteredResults() {
    const productsContainer = document.querySelector(".products");
    const resultCountEl = document.querySelector(".woocommerce-result-count");

    if (!productsContainer || !resultCountEl) {
      return null;
    }

    // Count visible products
    const visibleProducts = productsContainer.querySelectorAll(
      ".product:not(.hidden)"
    );
    const currentCount = visibleProducts.length;

    // Try to extract total from various sources
    const totalCount = this.extractTotalCount();

    return {
      current: currentCount,
      total: totalCount,
      element: resultCountEl,
    };
  }

  extractTotalCount() {
    // Method 1: From existing counter text
    const resultCountEl = document.querySelector(".woocommerce-result-count");
    if (resultCountEl) {
      const match = resultCountEl.textContent.match(/of\s+(\d+)/i);
      if (match) return parseInt(match[1]);
    }

    // Method 2: From pagination info
    const paginationInfo = document.querySelector(".woocommerce-info");
    if (paginationInfo) {
      const match = paginationInfo.textContent.match(/(\d+)\s+results?/i);
      if (match) return parseInt(match[1]);
    }

    // Method 3: From filter plugin data
    return this.extractFromFilterPlugins();
  }

  extractFromFilterPlugins() {
    // YITH Filter
    const yithInfo = document.querySelector(".yith-wcan-result-count");
    if (yithInfo) {
      const match = yithInfo.textContent.match(/(\d+)/);
      if (match) return parseInt(match[1]);
    }

    // BeRocket Filter
    const berocketInfo = document.querySelector(
      ".berocket_aapf_products_count"
    );
    if (berocketInfo) {
      return parseInt(berocketInfo.textContent);
    }

    // FacetWP
    if (window.FWP && window.FWP.settings) {
      return window.FWP.settings.num_choices || null;
    }

    // Fallback: count all products
    const allProducts = document.querySelectorAll(".product");
    return allProducts.length;
  }

  generateCounterText(current, total) {
    if (current === 0) {
      return "No products found";
    } else if (current === 1) {
      return "Showing the single result";
    } else if (current >= total) {
      return `Showing all ${total} results`;
    } else {
      return `Showing 1–${current} of ${total} results`;
    }
  }
}
```

#### 3. Counter Update Manager

```javascript
class FilterCounterUpdater {
  constructor() {
    this.detector = new FilterDetector();
    this.calculator = new CounterCalculator();
    this.debounceTimer = null;

    // Make globally accessible
    window.FilterCounterUpdater = this;
  }

  updateCounter() {
    // Debounce to prevent excessive updates
    clearTimeout(this.debounceTimer);
    this.debounceTimer = setTimeout(() => {
      this.performUpdate();
    }, 100);
  }

  performUpdate() {
    const result = this.calculator.calculateFilteredResults();

    if (!result) {
      this.debugLog("Required elements not found");
      return;
    }

    const newText = this.calculator.generateCounterText(
      result.current,
      result.total
    );

    // Update the counter
    result.element.textContent = newText;

    // Add visual feedback
    this.addUpdateAnimation(result.element);

    this.debugLog(
      `Updated filter counter: ${newText} (${result.current}/${result.total})`
    );
  }

  addUpdateAnimation(element) {
    element.style.transition = "opacity 0.3s ease";
    element.style.opacity = "0.7";

    setTimeout(() => {
      element.style.opacity = "1";
    }, 150);

    setTimeout(() => {
      element.style.transition = "";
    }, 300);
  }

  debugLog(message) {
    if (window.wooFilterCounter?.debug) {
      console.log("[WooFilterCounter]", message);
    }
  }
}
```

### WordPress Plugin Implementation

#### Main Plugin File

```php
<?php
/**
 * Plugin Name: WooCommerce Filter Counter Update
 * Description: Updates product count when filters are applied
 * Version: 1.0.0
 */

class WooFilterCounterUpdate {

    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'add_debug_info'));
    }

    public function enqueue_scripts() {
        if (is_shop() || is_product_category() || is_product_tag()) {
            wp_enqueue_script(
                'woo-filter-counter',
                plugin_dir_url(__FILE__) . 'assets/js/filter-counter.js',
                array('jquery'),
                '1.0.0',
                true
            );

            wp_localize_script('woo-filter-counter', 'wooFilterCounter', array(
                'debug' => defined('WP_DEBUG') && WP_DEBUG,
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('filter_counter_nonce')
            ));
        }
    }

    public function add_debug_info() {
        if (defined('WP_DEBUG') && WP_DEBUG && (is_shop() || is_product_category())) {
            echo '<script>console.log("WooFilterCounter: Debug mode enabled");</script>';
        }
    }
}

new WooFilterCounterUpdate();
```

### Advanced Features

#### 1. Multi-Language Support

```javascript
getLocalizedText(key, current, total) {
    const texts = {
        'en': {
            'no_results': 'No products found',
            'single_result': 'Showing the single result',
            'all_results': `Showing all ${total} results`,
            'partial_results': `Showing 1–${current} of ${total} results`
        },
        'id': {
            'no_results': 'Tidak ada produk ditemukan',
            'single_result': 'Menampilkan satu hasil',
            'all_results': `Menampilkan semua ${total} hasil`,
            'partial_results': `Menampilkan 1–${current} dari ${total} hasil`
        }
    };

    const lang = document.documentElement.lang.substring(0, 2) || 'en';
    return texts[lang]?.[key] || texts['en'][key];
}
```

#### 2. Filter State Detection

```javascript
detectActiveFilters() {
    const activeFilters = [];

    // Price filter
    const priceFilter = document.querySelector('.price_slider_amount input[name="min_price"]');
    if (priceFilter && priceFilter.value) {
        activeFilters.push({type: 'price', value: priceFilter.value});
    }

    // Category filter
    const categoryFilters = document.querySelectorAll('.widget_product_categories input:checked');
    categoryFilters.forEach(filter => {
        activeFilters.push({type: 'category', value: filter.value});
    });

    // Attribute filters
    const attributeFilters = document.querySelectorAll('.widget_layered_nav input:checked');
    attributeFilters.forEach(filter => {
        activeFilters.push({type: 'attribute', value: filter.value});
    });

    return activeFilters;
}
```

### Testing Requirements

#### Test Scenarios

1. **Price Filter**: Apply price range, verify counter updates
2. **Category Filter**: Select categories, verify counter updates
3. **Attribute Filter**: Select colors/sizes, verify counter updates
4. **Multiple Filters**: Apply multiple filters simultaneously
5. **Filter Removal**: Remove filters, verify counter resets
6. **Pagination + Filter**: Combine with load more functionality
7. **Mobile Responsive**: Test filter interactions on mobile

#### Plugin Compatibility Testing

- Test with each supported filter plugin individually
- Test with multiple filter plugins active
- Test with custom filter implementations
- Performance testing with large product catalogs

### Performance Optimization

#### Efficient Event Handling

```javascript
// Use event delegation for better performance
$(document).on(
  "click",
  ".filter-trigger",
  debounce(function () {
    updateCounter();
  }, 300)
);

// Optimize DOM queries
const cachedElements = {
  productsContainer: null,
  resultCounter: null,

  getProductsContainer() {
    if (!this.productsContainer) {
      this.productsContainer = document.querySelector(".products");
    }
    return this.productsContainer;
  },

  getResultCounter() {
    if (!this.resultCounter) {
      this.resultCounter = document.querySelector(".woocommerce-result-count");
    }
    return this.resultCounter;
  },
};
```

### Error Handling and Fallbacks

#### Graceful Degradation

```javascript
class ErrorHandler {
  static handleMissingElements() {
    const requiredElements = [".woocommerce-result-count", ".products"];

    const missing = requiredElements.filter(
      (selector) => !document.querySelector(selector)
    );

    if (missing.length > 0) {
      console.warn("WooFilterCounter: Missing elements:", missing);
      return false;
    }

    return true;
  }

  static handlePluginConflicts() {
    // Detect conflicting plugins
    const conflicts = [];

    if (window.jQuery && window.jQuery.fn.woof) {
      conflicts.push("WOOF - WooCommerce Products Filter");
    }

    if (conflicts.length > 0) {
      console.info("WooFilterCounter: Detected plugins:", conflicts);
    }
  }
}
```

---

## Implementation Checklist

- [ ] Create enhanced plugin structure
- [ ] Implement FilterDetector class
- [ ] Implement CounterCalculator class
- [ ] Implement FilterCounterUpdater class
- [ ] Add support for major filter plugins
- [ ] Add multi-language support
- [ ] Implement error handling
- [ ] Add performance optimizations
- [ ] Create comprehensive test suite
- [ ] Add debugging capabilities
- [ ] Package for deployment

## Integration Notes

This filter counter system should work alongside the Load More counter system, providing comprehensive counter updates for all user interactions on WooCommerce shop pages.

### Combined Implementation Strategy

#### Unified Counter Manager

```javascript
class UnifiedCounterManager {
  constructor() {
    this.loadMoreCounter = new LoadMoreCounterUpdater();
    this.filterCounter = new FilterCounterUpdater();
    this.isUpdating = false;
  }

  updateCounter(source = "unknown") {
    if (this.isUpdating) return;

    this.isUpdating = true;

    setTimeout(() => {
      const result = this.calculateCurrentState();
      this.updateDisplay(result, source);
      this.isUpdating = false;
    }, 50);
  }

  calculateCurrentState() {
    const productsContainer = document.querySelector(".products");
    const resultCountEl = document.querySelector(".woocommerce-result-count");

    if (!productsContainer || !resultCountEl) return null;

    const visibleProducts = productsContainer.querySelectorAll(
      ".product:not(.hidden)"
    );
    const totalProducts = this.extractTotalCount();

    return {
      visible: visibleProducts.length,
      total: totalProducts,
      element: resultCountEl,
    };
  }
}
```

### Event Priority System

```javascript
// Ensure filter events take priority over load more events
const eventPriorities = {
  filter: 1,
  load_more: 2,
  pagination: 3,
};

class EventManager {
  constructor() {
    this.eventQueue = [];
    this.processing = false;
  }

  addEvent(type, callback) {
    this.eventQueue.push({
      type,
      callback,
      priority: eventPriorities[type] || 999,
      timestamp: Date.now(),
    });

    this.processQueue();
  }

  processQueue() {
    if (this.processing) return;

    this.processing = true;

    // Sort by priority, then by timestamp
    this.eventQueue.sort((a, b) => {
      if (a.priority !== b.priority) {
        return a.priority - b.priority;
      }
      return a.timestamp - b.timestamp;
    });

    // Process events with debouncing
    setTimeout(() => {
      const event = this.eventQueue.shift();
      if (event) {
        event.callback();
      }
      this.processing = false;

      if (this.eventQueue.length > 0) {
        this.processQueue();
      }
    }, 100);
  }
}
```

### Deployment Strategy

#### Development Environment Setup

1. **Local WordPress Installation**

   - WordPress 6.0+
   - WooCommerce 7.0+
   - Blocksy theme
   - Test filter plugins (YITH, BeRocket, FacetWP)

2. **Test Data Requirements**
   - Minimum 100 test products
   - Multiple product categories
   - Various product attributes (color, size, brand)
   - Price range variations
   - Stock status variations

#### Staging Environment Testing

1. **Performance Testing**

   - Test with 1000+ products
   - Multiple simultaneous filter applications
   - Mobile device testing
   - Network throttling tests

2. **Compatibility Testing**
   - Test with popular themes
   - Test with caching plugins
   - Test with CDN configurations
   - Test with multilingual plugins (WPML, Polylang)

#### Production Deployment

1. **Gradual Rollout**

   - Deploy to staging first
   - A/B testing with small user group
   - Monitor performance metrics
   - Full deployment after validation

2. **Monitoring Setup**
   - JavaScript error tracking
   - Performance monitoring
   - User interaction analytics
   - Filter usage statistics

### Troubleshooting Guide

#### Common Issues and Solutions

1. **Counter Not Updating After Filter**

   ```javascript
   // Debug: Check if events are firing
   console.log("Filter events detected:", filterEventsDetected);

   // Solution: Add manual trigger
   setTimeout(() => {
     window.FilterCounterUpdater?.updateCounter();
   }, 500);
   ```

2. **Incorrect Product Count**

   ```javascript
   // Debug: Check product visibility
   const products = document.querySelectorAll(".product");
   const visible = document.querySelectorAll(".product:not(.hidden)");
   console.log(`Total: ${products.length}, Visible: ${visible.length}`);

   // Solution: Adjust visibility detection
   const visibleProducts = Array.from(products).filter((product) => {
     const style = window.getComputedStyle(product);
     return style.display !== "none" && style.visibility !== "hidden";
   });
   ```

3. **Multiple Plugin Conflicts**
   ```javascript
   // Debug: Detect active filter plugins
   const activePlugins = {
     yith: !!document.querySelector(".yith-wcan-filters"),
     berocket: !!document.querySelector(".berocket_aapf_widget"),
     facetWP: !!window.FWP,
     jetFilters: !!document.querySelector(".jet-smart-filters"),
   };
   console.log("Active filter plugins:", activePlugins);
   ```

#### Debug Mode Implementation

```javascript
class DebugManager {
  constructor() {
    this.enabled = window.wooFilterCounter?.debug || false;
    this.logs = [];
  }

  log(message, data = null) {
    if (!this.enabled) return;

    const logEntry = {
      timestamp: new Date().toISOString(),
      message,
      data,
    };

    this.logs.push(logEntry);
    console.log(`[WooFilterCounter] ${message}`, data);

    // Keep only last 100 logs
    if (this.logs.length > 100) {
      this.logs.shift();
    }
  }

  exportLogs() {
    return JSON.stringify(this.logs, null, 2);
  }

  showStats() {
    const stats = {
      totalEvents: this.logs.filter((log) => log.message.includes("event"))
        .length,
      counterUpdates: this.logs.filter((log) => log.message.includes("Updated"))
        .length,
      errors: this.logs.filter((log) => log.message.includes("Error")).length,
    };

    console.table(stats);
  }
}
```

### Security Considerations

#### Input Validation

```php
// WordPress plugin security
public function validate_filter_request($data) {
    // Sanitize filter parameters
    $clean_data = array();

    if (isset($data['price_min'])) {
        $clean_data['price_min'] = absint($data['price_min']);
    }

    if (isset($data['price_max'])) {
        $clean_data['price_max'] = absint($data['price_max']);
    }

    if (isset($data['categories'])) {
        $clean_data['categories'] = array_map('absint', (array) $data['categories']);
    }

    return $clean_data;
}
```

#### XSS Prevention

```javascript
// Safe text updates
function updateCounterSafely(text) {
  const element = document.querySelector(".woocommerce-result-count");
  if (element) {
    // Use textContent instead of innerHTML
    element.textContent = text;
  }
}
```

### Future Enhancements

#### Advanced Analytics

```javascript
class FilterAnalytics {
  constructor() {
    this.events = [];
  }

  trackFilterUsage(filterType, filterValue) {
    this.events.push({
      type: "filter_applied",
      filter_type: filterType,
      filter_value: filterValue,
      timestamp: Date.now(),
      products_before: this.getProductCount(),
      products_after: null, // Will be updated after filter completes
    });
  }

  sendAnalytics() {
    // Send to analytics service
    if (window.gtag) {
      window.gtag("event", "filter_interaction", {
        event_category: "ecommerce",
        event_label: "product_filter",
      });
    }
  }
}
```

#### Machine Learning Integration

```javascript
// Predict user intent based on filter patterns
class FilterPredictor {
  constructor() {
    this.userPatterns = this.loadUserPatterns();
  }

  predictNextFilter(currentFilters) {
    // Analyze patterns and suggest next likely filter
    const suggestions = this.analyzePatterns(currentFilters);
    return suggestions;
  }

  preloadFilterResults(predictedFilters) {
    // Preload likely filter combinations for better performance
    predictedFilters.forEach((filter) => {
      this.preloadFilter(filter);
    });
  }
}
```

---

## Contact and Support

For technical questions or implementation support, refer to:

- WordPress Plugin Development Handbook
- WooCommerce Developer Documentation
- Blocksy Theme Documentation
- Filter Plugin Specific Documentation (YITH, BeRocket, FacetWP)
- JavaScript Event Handling Best Practices

## Version History

- **v1.0.0**: Initial implementation with basic filter detection
- **v1.1.0**: Added multi-plugin support and error handling
- **v1.2.0**: Performance optimizations and caching
- **v1.3.0**: Analytics integration and debugging tools
- **v2.0.0**: Unified counter management with Load More integration
