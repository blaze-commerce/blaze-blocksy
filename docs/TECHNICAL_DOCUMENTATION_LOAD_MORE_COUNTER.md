# Technical Documentation: WooCommerce Load More Counter Update

## Project Overview
**Objective**: Implement automatic update of the "SHOWING X-Y OF Z RESULTS" counter when users click the "Load More" button on WooCommerce shop/category pages.

**Current Behavior**: Counter remains static (e.g., "SHOWING 1-16 OF 163 RESULTS") even after loading more products.

**Expected Behavior**: Counter updates dynamically (e.g., "SHOWING 1-32 OF 163 RESULTS") after loading additional products.

## Technical Analysis

### Current Implementation
- **Theme**: Blocksy WordPress Theme
- **WooCommerce Integration**: Standard WooCommerce shop pages
- **Pagination System**: Uses InfiniteScroll library for load more functionality
- **Counter Element**: `.woocommerce-result-count` class

### Key Files and Components

#### 1. Pagination Core
- **File**: `inc/components/pagination.php`
- **Function**: `blocksy_display_posts_pagination()`
- **Load More Button**: Generated with class `.ct-load-more`
- **Pagination Types**: simple, load_more, infinite_scroll, next_prev

#### 2. Infinite Scroll Implementation
- **File**: `static/js/frontend/layouts/infinite-scroll.js`
- **Library**: InfiniteScroll.js
- **Events Triggered**: `ct:infinite-scroll:load`, `blocksy:frontend:init`
- **Product Container**: `.products` class

#### 3. WooCommerce Integration
- **File**: `inc/components/woocommerce/archive/loop-elements.php`
- **Result Count**: Handled by WooCommerce template `loop/result-count.php`
- **Theme Options**: `has_shop_results_count`, `shop_results_count_visibility`

#### 4. Event System
- **File**: `static/js/frontend/woocommerce/handle-events.js`
- **Events**: Various WooCommerce and theme events for re-initialization

### DOM Structure Analysis

#### Result Counter Element
```html
<p class="woocommerce-result-count">Showing 1–16 of 163 results</p>
```

#### Products Container
```html
<ul class="products columns-4">
    <li class="product">...</li>
    <!-- More products loaded here via AJAX -->
</ul>
```

#### Load More Button
```html
<nav class="ct-pagination" data-pagination="load_more">
    <div class="ct-load-more-helper">
        <button class="wp-element-button ct-load-more">Load More</button>
        <span class="ct-ajax-loader">...</span>
        <div class="ct-last-page-text">No more products to load</div>
    </div>
</nav>
```

## Implementation Strategy

### Recommended Approach: WordPress Plugin

#### Plugin Structure
```
woo-load-more-counter/
├── woo-load-more-counter.php
├── assets/
│   ├── js/
│   │   └── load-more-counter.js
│   └── css/
│       └── load-more-counter.css (optional)
├── includes/
│   └── class-counter-updater.php
└── readme.txt
```

#### Core Functionality Requirements

1. **Event Listening**
   - Listen for `ct:infinite-scroll:load` event
   - Listen for `blocksy:frontend:init` event
   - Monitor DOM changes in `.products` container

2. **Counter Calculation**
   - Count current visible products: `document.querySelectorAll('.products .product').length`
   - Extract total from existing text: regex `/of\s+(\d+)/i`
   - Generate new counter text

3. **Text Update Logic**
   ```javascript
   // Single result
   if (currentProducts === 1) {
       newText = "Showing the single result";
   }
   // All results shown
   else if (currentProducts >= totalCount) {
       newText = `Showing all ${totalCount} results`;
   }
   // Partial results
   else {
       newText = `Showing 1–${currentProducts} of ${totalCount} results`;
   }
   ```

### Technical Implementation Details

#### JavaScript Event Binding
```javascript
// Primary event listeners
if (window.ctEvents) {
    ctEvents.on('ct:infinite-scroll:load', updateCounter);
    ctEvents.on('blocksy:frontend:init', updateCounter);
}

// Fallback: MutationObserver
const observer = new MutationObserver(handleProductsChange);
observer.observe(productsContainer, { childList: true });
```

#### Counter Update Function
```javascript
function updateCounter() {
    const resultCountEl = document.querySelector('.woocommerce-result-count');
    const productsContainer = document.querySelector('.products');
    
    if (!resultCountEl || !productsContainer) return;
    
    const currentProducts = productsContainer.querySelectorAll('.product').length;
    const originalText = resultCountEl.textContent;
    const totalMatch = originalText.match(/of\s+(\d+)/i);
    
    if (totalMatch) {
        const totalCount = parseInt(totalMatch[1]);
        const newText = generateCounterText(currentProducts, totalCount);
        resultCountEl.textContent = newText;
    }
}
```

### WordPress Plugin Implementation

#### Main Plugin File
```php
<?php
/**
 * Plugin Name: WooCommerce Load More Counter Update
 * Description: Updates product count when Load More is clicked
 * Version: 1.0.0
 */

class WooLoadMoreCounter {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function enqueue_scripts() {
        if (is_shop() || is_product_category() || is_product_tag()) {
            wp_enqueue_script(
                'woo-load-more-counter',
                plugin_dir_url(__FILE__) . 'assets/js/load-more-counter.js',
                array('jquery'),
                '1.0.0',
                true
            );
        }
    }
}

new WooLoadMoreCounter();
```

### Compatibility Considerations

#### Theme Compatibility
- **Primary**: Blocksy theme (optimized)
- **Secondary**: Any theme using standard WooCommerce pagination
- **Fallback**: MutationObserver for non-standard implementations

#### Plugin Compatibility
- WooCommerce (required)
- AJAX filter plugins (FacetWP, WooCommerce Product Filter)
- Pagination plugins
- Performance optimization plugins

### Testing Requirements

#### Test Scenarios
1. **Basic Load More**: Click load more button, verify counter updates
2. **Infinite Scroll**: Scroll to trigger auto-load, verify counter updates
3. **Filter Integration**: Apply filters, verify counter remains accurate
4. **Mobile Responsive**: Test on various screen sizes
5. **Performance**: Test with large product catalogs

#### Browser Compatibility
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+
- Mobile browsers (iOS Safari, Chrome Mobile)

### Performance Optimization

#### Efficient DOM Queries
- Cache DOM elements where possible
- Use specific selectors to minimize query time
- Debounce update function to prevent excessive calls

#### Memory Management
- Remove event listeners when not needed
- Avoid memory leaks in MutationObserver
- Clean up on page unload

### Error Handling

#### Graceful Degradation
- Function silently if required elements not found
- Fallback to original behavior if JavaScript fails
- Console logging for debugging (in debug mode only)

#### Debug Mode
```javascript
const DEBUG = window.wooLoadMoreCounter?.debug || false;

function debugLog(message) {
    if (DEBUG) {
        console.log('[WooLoadMoreCounter]', message);
    }
}
```

### Security Considerations

#### Input Validation
- Validate DOM element existence before manipulation
- Sanitize any user input (though minimal in this case)
- Use WordPress nonces if AJAX requests are added

#### XSS Prevention
- Use `textContent` instead of `innerHTML` for text updates
- Validate extracted numbers from regex matches

### Deployment Strategy

#### Development Environment
1. Set up local WordPress with WooCommerce
2. Install Blocksy theme
3. Create test products and categories
4. Implement and test plugin

#### Staging Environment
1. Deploy to staging site
2. Test with real product data
3. Performance testing with large catalogs
4. Cross-browser testing

#### Production Deployment
1. Code review and testing completion
2. Plugin packaging and documentation
3. Gradual rollout with monitoring
4. Performance monitoring post-deployment

### Maintenance and Updates

#### Version Control
- Use semantic versioning (1.0.0, 1.0.1, etc.)
- Maintain changelog for updates
- Git repository for code management

#### Future Enhancements
- Internationalization support
- Admin settings panel
- Custom counter text formats
- Analytics integration

### Support and Documentation

#### User Documentation
- Installation instructions
- Configuration options
- Troubleshooting guide
- FAQ section

#### Developer Documentation
- Code comments and documentation
- Hook and filter references
- Customization examples
- API documentation

---

## Implementation Checklist

- [ ] Create plugin structure
- [ ] Implement JavaScript counter update logic
- [ ] Add event listeners for Blocksy theme
- [ ] Implement MutationObserver fallback
- [ ] Add error handling and debugging
- [ ] Create WordPress plugin wrapper
- [ ] Test with various scenarios
- [ ] Optimize for performance
- [ ] Add documentation
- [ ] Package for deployment

## Contact and Support

For technical questions or implementation support, refer to:
- WordPress Codex for plugin development
- WooCommerce developer documentation
- Blocksy theme documentation
- InfiniteScroll.js documentation
