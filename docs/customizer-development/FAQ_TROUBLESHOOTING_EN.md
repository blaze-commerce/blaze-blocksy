# FAQ & Troubleshooting - Blocksy Customizer

## ⚠️ MOST COMMON MISTAKE

### Design Options in Wrong Location

**Problem**: Design options (fonts, colors, backgrounds, borders) appear inside the layer settings panel instead of the Design tab.

**Cause**: Adding design options to layer options instead of Design tab.

**❌ WRONG:**
```php
// In layer options - INCORRECT
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['my_layer'] = [
        'options' => [
            'my_font' => [...],  // ❌ Design option in layer
            'my_color' => [...], // ❌ Design option in layer
        ],
    ];
});
```

**✅ CORRECT:**
```php
// Layer options - Functional only
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['my_layer'] = [
        'options' => [
            'spacing' => [...], // ✅ Functional option
        ],
    ];
});

// Design options - In Design tab
add_filter('blocksy:options:single_product:elements:design_tab:end', function($options) {
    $options[blocksy_rand_md5()] = [
        'condition' => ['woo_single_layout:array-ids:my_layer:enabled' => '!no'],
        'options' => [
            'myLayerFont' => [...],  // ✅ Design option in Design tab
            'myLayerColor' => [...], // ✅ Design option in Design tab
        ],
    ];
    return $options;
});
```

**See**: `CORRECTION_DESIGN_OPTIONS.md` and `CORRECT_EXAMPLE_PRODUCT_TABS.md`

---

## Frequently Asked Questions

### Q1: Where should I put custom code?

**A:** Always use **child theme**. Don't edit parent theme directly.

```
wp-content/themes/
├── blocksy/              # Parent theme (DON'T EDIT)
└── blocksy-child/        # Child theme (EDIT HERE)
    ├── functions.php
    ├── style.css
    └── inc/
        └── custom/
```

---

### Q2: My custom layer doesn't appear in customizer?

**A:** Checklist:

1. ✅ Is the filter `blocksy_woo_single_options_layers:extra` added?
2. ✅ Is the file loaded in `functions.php`?
3. ✅ Are there any PHP errors? Check error log
4. ✅ Clear cache (browser & WordPress)
5. ✅ Refresh customizer

**Debug:**

```php
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    error_log('Filter called'); // Check if filter is called
    error_log(print_r($options, true)); // Check existing options
    
    $options['my_layer'] = [...];
    
    return $options;
});
```

---

### Q3: Layer appears in customizer but doesn't render on frontend?

**A:** Checklist:

1. ✅ Is the layer enabled in customizer?
2. ✅ Is the action `blocksy:woocommerce:product:custom:layer` added?
3. ✅ Are the conditions in rendering function correct?
4. ✅ Check for JavaScript errors in console

**Debug:**

```php
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    error_log('Layer ID: ' . $layer['id']); // Check which layers are called
    error_log('Layer data: ' . print_r($layer, true));
    
    if ($layer['id'] !== 'my_layer') {
        return;
    }
    
    echo '<!-- My layer rendered -->';
    // Your render code
}, 10, 1);
```

---

### Q4: Design options don't appear in customizer?

**A:** Checklist:

1. ✅ Is the condition correct? `['woo_single_layout:array-ids:my_layer:enabled' => '!no']`
2. ✅ Is `computed_fields` added? `'computed_fields' => ['woo_single_layout']`
3. ✅ Is the layer enabled?
4. ✅ Are options added in the correct file? (`single-product-elements.php`)

---

### Q5: Live preview doesn't work?

**A:** Checklist:

1. ✅ Is `'setting' => ['transport' => 'postMessage']` added?
2. ✅ Is dynamic CSS generated?
3. ✅ Is JavaScript sync enqueued?
4. ✅ Check browser console for errors
5. ✅ Hard refresh browser (Ctrl+Shift+R)

**Alternative:** Use `blocksy_sync_whole_page()` for selective refresh:

```php
'my_option' => [
    'type' => 'text',
    'sync' => blocksy_sync_whole_page([
        'prefix' => 'product',
        'loader_selector' => '.entry-summary-items'
    ]),
],
```

---

### Q6: CSS doesn't apply on frontend?

**A:** Checklist:

1. ✅ Is dynamic CSS hook added?
2. ✅ Is CSS selector correct?
3. ✅ Is layer enabled?
4. ✅ Clear cache (browser, WordPress, plugin cache)
5. ✅ Check CSS specificity

**Debug:**

```php
add_action('blocksy:global-dynamic-css:enqueue', function($args) {
    $css = $args['css'];
    
    // Test CSS
    $css->put('.test-selector', 'background: red !important;');
    
    // Check if CSS is generated
    error_log('CSS: ' . $css->build_css_structure());
}, 10, 1);
```

---

### Q7: How to add options only for specific products?

**A:** Use conditionals based on product data:

```php
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    if ($layer['id'] !== 'my_layer') {
        return;
    }
    
    global $product;
    
    // Only show for specific category
    if (!has_term('my-category', 'product_cat', $product->get_id())) {
        return;
    }
    
    // Only show for products with specific tag
    if (!has_term('my-tag', 'product_tag', $product->get_id())) {
        return;
    }
    
    // Only show for specific product IDs
    if (!in_array($product->get_id(), [123, 456, 789])) {
        return;
    }
    
    // Render layer
}, 10, 1);
```

---

### Q8: How to access product data in layer?

**A:** Use global `$product`:

```php
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    if ($layer['id'] !== 'my_layer') {
        return;
    }
    
    global $product;
    
    if (!$product) {
        return;
    }
    
    // Get product data
    $product_id = $product->get_id();
    $product_name = $product->get_name();
    $product_price = $product->get_price();
    $is_on_sale = $product->is_on_sale();
    $stock_status = $product->get_stock_status();
    
    // Get custom fields
    $custom_field = get_post_meta($product_id, '_my_custom_field', true);
    
    // Use data
    echo '<div class="my-layer">';
    echo '<h3>' . esc_html($product_name) . '</h3>';
    echo '<p>Price: ' . wc_price($product_price) . '</p>';
    echo '</div>';
}, 10, 1);
```

---

### Q9: How to add icon picker?

**A:** Use type `icon-picker`:

```php
'my_icon' => [
    'type' => 'icon-picker',
    'label' => __('Icon', 'blocksy'),
    'design' => 'inline',
    'value' => [
        'icon' => 'fas fa-star'
    ]
],
```

**Render:**

```php
$icon = blocksy_akg('my_icon', $layer, ['icon' => 'fas fa-star']);

if (!empty($icon['icon'])) {
    echo '<i class="' . esc_attr($icon['icon']) . '"></i>';
}
```

---

### Q10: How to add image uploader?

**A:** Use type `ct-image-uploader`:

```php
'my_image' => [
    'label' => __('Image', 'blocksy'),
    'type' => 'ct-image-uploader',
    'design' => 'inline',
    'value' => [
        'attachment_id' => null,
    ],
],
```

**Render:**

```php
$image = blocksy_akg('my_image', $layer, ['attachment_id' => null]);

if (!empty($image['attachment_id'])) {
    echo wp_get_attachment_image($image['attachment_id'], 'full');
}
```

---

## Common Errors & Solutions

### Error 1: "Call to undefined function blocksy_get_theme_mod()"

**Cause:** Blocksy theme not active or function called too early

**Solution:**

```php
// Check if Blocksy is active
if (!function_exists('blocksy_get_theme_mod')) {
    return;
}

// Or use WordPress get_theme_mod
$value = get_theme_mod('my_option', 'default');
```

---

### Error 2: "Undefined index: id"

**Cause:** Layer array doesn't have 'id' key

**Solution:**

```php
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    // Check if id exists
    if (!isset($layer['id']) || $layer['id'] !== 'my_layer') {
        return;
    }
    
    // Your code
}, 10, 1);
```

---

### Error 3: CSS doesn't apply due to specificity

**Cause:** CSS selector not specific enough

**Solution:**

```php
// Instead of:
$css->put('.my-element', 'color: red');

// Use more specific selector:
$css->put('.entry-summary .my-element', 'color: red');

// Or use !important (last resort):
$css->put('.my-element', 'color: red !important');
```

---

### Error 4: Live preview doesn't update

**Cause:** JavaScript sync not configured correctly

**Solution:**

```php
// Use selective refresh instead of live preview
'my_option' => [
    'type' => 'text',
    'sync' => blocksy_sync_whole_page([
        'prefix' => 'product',
        'loader_selector' => '.entry-summary-items'
    ]),
],
```

---

### Error 5: Layer doesn't appear in default layout

**Cause:** Defaults filter not added

**Solution:**

```php
add_filter('blocksy_woo_single_options_layers:defaults', function($defaults) {
    // Add your layer to defaults
    $defaults[] = [
        'id' => 'my_layer',
        'enabled' => true, // or false
    ];
    
    return $defaults;
});
```

---

## Debugging Tips

### 1. Enable WordPress Debug Mode

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

### 2. Check Error Log

```bash
# Location: wp-content/debug.log
tail -f wp-content/debug.log
```

### 3. Debug Option Values

```php
// In template
$value = blocksy_get_theme_mod('my_option', 'default');
echo '<pre>';
var_dump($value);
echo '</pre>';
```

### 4. Debug Layout Array

```php
add_action('blocksy:woocommerce:product-single:layout:before', function($args) {
    echo '<pre>';
    print_r($args['layout']);
    echo '</pre>';
});
```

### 5. Check Generated CSS

```php
add_action('blocksy:global-dynamic-css:enqueue', function($args) {
    $css = $args['css'];
    
    // Output CSS to error log
    error_log('Generated CSS:');
    error_log($css->build_css_structure());
}, 999); // High priority to run last
```

### 6. Browser Console

Open browser console (F12) and check for:
- JavaScript errors
- Network requests
- CSS changes in Elements tab

---

## Performance Tips

### 1. Conditional CSS Loading

```php
add_action('blocksy:global-dynamic-css:enqueue', function($args) {
    // Only generate CSS if layer is enabled
    $layout = blocksy_get_theme_mod('woo_single_layout', []);
    $my_layer = array_filter($layout, function($layer) {
        return $layer['id'] === 'my_layer' && $layer['enabled'];
    });
    
    if (empty($my_layer)) {
        return; // Skip CSS generation
    }
    
    // Generate CSS
}, 10, 1);
```

### 2. Cache Expensive Operations

```php
function get_my_expensive_data() {
    $cache_key = 'my_expensive_data';
    $cached = get_transient($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    // Expensive operation
    $data = do_expensive_operation();
    
    // Cache for 1 hour
    set_transient($cache_key, $data, HOUR_IN_SECONDS);
    
    return $data;
}
```

### 3. Minimize Database Queries

```php
// Bad: Multiple queries
$value1 = get_post_meta($product_id, '_field1', true);
$value2 = get_post_meta($product_id, '_field2', true);
$value3 = get_post_meta($product_id, '_field3', true);

// Good: Single query
$all_meta = get_post_meta($product_id);
$value1 = isset($all_meta['_field1'][0]) ? $all_meta['_field1'][0] : '';
$value2 = isset($all_meta['_field2'][0]) ? $all_meta['_field2'][0] : '';
$value3 = isset($all_meta['_field3'][0]) ? $all_meta['_field3'][0] : '';
```

---

## Best Practices Checklist

- ✅ Always use child theme
- ✅ Use hooks and filters, never edit core files
- ✅ Add proper error checking
- ✅ Escape output (`esc_html()`, `esc_attr()`, `wp_kses_post()`)
- ✅ Sanitize input
- ✅ Use WordPress coding standards
- ✅ Add comments to explain complex code
- ✅ Test on different screen sizes
- ✅ Test with different products
- ✅ Clear cache after changes
- ✅ Check browser console for errors
- ✅ Use version control (Git)

---

**FAQ Version**: 1.0.0  
**Last Updated**: 2025-11-26
