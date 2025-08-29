# How to Use Custom Product Element

## 1. Element Installation

### Method 1: Via functions.php (Recommended)

Add the following code to your theme's `functions.php` file:

```php
// Add custom product dimensions element
function add_custom_product_dimensions_element() {
    // Ensure Blocksy Companion Pro is active
    if (!class_exists('BlocksyExtensionWoocommerceExtra')) {
        return;
    }
    
    // Include element file
    require_once get_template_directory() . '/custom-product-dimensions-element.php';
}
add_action('init', 'add_custom_product_dimensions_element');
```

### Method 2: As a Plugin

Create a new plugin file with header:

```php
<?php
/**
 * Plugin Name: Custom Product Dimensions Element
 * Description: Adds product dimensions element to Blocksy Companion Pro
 * Version: 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include element class
require_once plugin_dir_path(__FILE__) . 'custom-product-dimensions-element.php';
```

## 2. How to Activate Element

1. **Open WordPress Customizer**

   - Go to `Appearance > Customize`

2. **Navigate to WooCommerce Settings**

   - Click `WooCommerce`
   - Select `Single Product`

3. **Edit Product Elements**

   - Scroll to `Product Elements` section
   - Click the drag & drop button to edit layout

4. **Add New Element**

   - You will see a new element named "Product Dimensions"
   - Drag the element to your desired position
   - Toggle the switch to activate it

## 3. Element Configuration

This element has several configurable options:

### Section Title
- **Field**: Text input
- **Default**: "Dimensions"
- **Function**: Title displayed above dimension information

### Show Weight
- **Field**: Toggle switch
- **Default**: Yes
- **Function**: Show/hide product weight

### Show Dimensions
- **Field**: Toggle switch  
- **Default**: Yes
- **Function**: Show/hide dimensions (L x W x H)

### Dimensions Format
- **Field**: Select dropdown
- **Options**: 
  - Table Format (default)
  - Inline Format
  - List Format
- **Function**: Controls how dimension information is displayed

### Bottom Spacing
- **Field**: Slider (0-100px)
- **Default**: 20px
- **Function**: Bottom margin of the element

## 4. Output Examples

### Table Format:
```
Dimensions
┌─────────────┬──────────────┐
│ Weight      │ 2.5 kg       │
│ Dimensions  │ 30 × 20 × 10 cm │
└─────────────┴──────────────┘
```

### Inline Format:
```
Dimensions
Weight: 2.5 kg | Dimensions: 30 × 20 × 10 cm
```

### List Format:
```
Dimensions
• Weight: 2.5 kg
• Dimensions: 30 × 20 × 10 cm
```

## 5. CSS Customization

The element comes with basic styling. For further customization, add the following CSS:

```css
/* Customize title */
.ct-product-dimensions-title {
    color: #333;
    font-size: 18px;
    margin-bottom: 15px;
}

/* Customize table format */
.ct-dimensions-table {
    border-radius: 5px;
    overflow: hidden;
}

.ct-dimensions-table td {
    padding: 12px;
}

/* Customize inline format */
.ct-dimensions-inline {
    background: #f5f5f5;
    padding: 10px;
    border-radius: 5px;
}

/* Customize list format */
.ct-dimensions-list {
    background: #fff;
    border: 1px solid #ddd;
    padding: 15px 20px;
    border-radius: 5px;
}
```

## 6. Available Hooks

This element uses the following hooks:

- `blocksy_woo_single_options_layers:defaults` - Add to default layout
- `blocksy_woo_single_options_layers:extra` - Add customizer options
- `blocksy:woocommerce:product:custom:layer` - Render element on single product
- `blocksy_woo_card_options_layers:defaults` - Add to product cards
- `blocksy_woo_card_options_layers:extra` - Options for product cards
- `blocksy:woocommerce:product-card:custom:layer` - Render on product cards

## 7. Troubleshooting

### Element not appearing in customizer:
- Ensure Blocksy Companion Pro is active
- Ensure WooCommerce is active
- Clear cache if using caching plugins

### Element not displaying on frontend:
- Ensure element is activated in customizer
- Ensure product has dimension/weight data
- Check browser console for JavaScript errors

### Styling issues:
- Ensure theme doesn't override element CSS
- Use higher CSS specificity
- Check for conflicts with other plugins

## 8. Further Development

You can extend this element by:

1. **Adding new fields** in the `add_layer_options()` method
2. **Adding new display formats** by creating new render methods
3. **Adding conditional logic** based on product categories
4. **Integrating with custom fields** from other plugins
5. **Adding icons** for each dimension information

Example of adding a new field:

```php
'show_volume' => [
    'label' => __('Show Volume', 'textdomain'),
    'type' => 'ct-switch',
    'value' => 'no',
    'sync' => [
        'id' => 'woo_single_layout_skip',
    ],
],
```

Then implement in the `render_layer()` method to calculate and display product volume.
