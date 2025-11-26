# Quick Start Guide - Blocksy Customizer

## 🎯 Document Purpose

Quick guide for AI Agent in creating custom functions for Blocksy Theme customizer, specifically for WooCommerce Single Product.

---

## ⚠️ CRITICAL RULE: Separate Layer Options from Design Options

**MUST UNDERSTAND:**

1. **Layer Options** = Functional settings (content, visibility, layout)
   - Location: Inside layer settings panel
   - File: `single-product-layers.php`
   - Filter: `blocksy_woo_single_options_layers:extra`

2. **Design Options** = Visual styling (fonts, colors, backgrounds, borders)
   - Location: Design tab (separate from layer)
   - File: `single-product-elements.php`
   - Filter: `blocksy:options:single_product:elements:design_tab:end`

**❌ NEVER put design options (fonts, colors, etc.) inside layer options!**

---

## 📋 Checklist: Adding Toggle Setting

### Example: Disable Product Tabs

**Files to modify:**
- ✅ `inc/options/woocommerce/single-product-tabs.php` (already exists)

**Implementation:**

```php
// File already exists with toggle structure
$options = [
    'woo_has_product_tabs' => [
        'label' => __( 'Product Tabs', 'blocksy' ),
        'type' => 'ct-panel',
        'switch' => true,  // ← This creates the toggle
        'value' => 'yes',
        'sync' => blocksy_sync_whole_page([
            'prefix' => 'product',
            'loader_selector' => '.type-product'
        ]),
    ]
];
```

**Use in template:**

```php
$has_tabs = blocksy_get_theme_mod('woo_has_product_tabs', 'yes');

if ($has_tabs === 'yes') {
    woocommerce_output_product_data_tabs();
}
```

---

## 📋 Checklist: Adding New Element

### Example: Product Tabs as Layer

**Steps:**

### 1️⃣ Define Layer Options

```php
// In functions.php or custom file
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['product_tabs'] = [
        'label' => __('Product Tabs', 'blocksy'),
        'options' => [
            'spacing' => [
                'label' => __('Bottom Spacing', 'blocksy'),
                'type' => 'ct-slider',
                'min' => 0,
                'max' => 100,
                'value' => 10,
                'responsive' => true,
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],
        ],
    ];
    return $options;
});
```

### 2️⃣ Add to Default Layout

```php
add_filter('blocksy_woo_single_options_layers:defaults', function($defaults) {
    $defaults[] = [
        'id' => 'product_tabs',
        'enabled' => true,
    ];
    return $defaults;
});
```

### 3️⃣ Render Element

```php
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    if ($layer['id'] !== 'product_tabs') {
        return;
    }
    
    woocommerce_output_product_data_tabs();
}, 10, 1);
```

---

## 📋 Checklist: Adding Design Options

### Example: Styling Product Tabs

**⚠️ IMPORTANT**: Design options go in the **Design tab**, NOT in layer options!

### 1️⃣ Add Design Options (Using Filter - Recommended)

```php
// File: inc/custom/product-tabs-design.php

add_filter('blocksy:options:single_product:elements:design_tab:end', function($options) {

    // Only show when product_tabs layer is enabled
    $options[blocksy_rand_md5()] = [
        'type' => 'ct-condition',
        'condition' => ['woo_single_layout:array-ids:product_tabs:enabled' => '!no'],
        'computed_fields' => ['woo_single_layout'],
        'options' => [

            'productTabsFont' => [
                'type' => 'ct-typography',
                'label' => __('Tabs Font', 'blocksy'),
                'value' => blocksy_typography_default_values([
                    'size' => '16px',
                ]),
                'setting' => ['transport' => 'postMessage'],
                'divider' => 'top:full', // Visual separator
            ],

            'productTabsColor' => [
                'label' => __('Tabs Color', 'blocksy'),
                'type'  => 'ct-color-picker',
                'design' => 'inline',
                'setting' => ['transport' => 'postMessage'],
                'value' => [
                    'default' => [
                        'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
                    ],
                ],
                'pickers' => [
                    [
                        'title' => __('Initial', 'blocksy'),
                        'id' => 'default',
                        'inherit' => 'var(--theme-text-color)'
                    ],
                ],
            ],

        ],
    ];

    return $options;
});
```

**Load in functions.php:**

```php
require_once get_stylesheet_directory() . '/inc/custom/product-tabs-design.php';
```

### 2️⃣ Generate Dynamic CSS

```php
add_action('blocksy:global-dynamic-css:enqueue', function($args) {
    $css = $args['css'];
    $tablet_css = $args['tablet_css'];
    $mobile_css = $args['mobile_css'];
    
    // Typography
    blocksy_output_font_css([
        'font_value' => blocksy_get_theme_mod('productTabsTitleFont'),
        'css' => $css,
        'tablet_css' => $tablet_css,
        'mobile_css' => $mobile_css,
        'selector' => '.woocommerce-tabs .tabs li a'
    ]);
    
    // Color
    blocksy_output_colors([
        'value' => blocksy_get_theme_mod('productTabsTitleColor'),
        'default' => [
            'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
        ],
        'css' => $css,
        'variables' => [
            'default' => [
                'selector' => '.woocommerce-tabs .tabs li a',
                'variable' => 'theme-text-color'
            ],
        ],
    ]);
}, 10, 1);
```

### 3️⃣ Setup Live Preview (Optional)

In `static/js/customizer/sync/variables/woocommerce/single-product-layers.js`:

```javascript
import { typographyOption } from '../typography'

export const getWooSingleLayersVariablesFor = () => ({
    ...typographyOption({
        id: 'productTabsTitleFont',
        selector: '.woocommerce-tabs .tabs li a',
    }),

    productTabsTitleColor: {
        selector: '.woocommerce-tabs .tabs li a',
        variable: 'theme-text-color',
        type: 'color:default',
    },
})
```

---

## 🔧 Helper Functions Cheat Sheet

| Function | Purpose | Example |
|----------|---------|---------|
| `blocksy_get_theme_mod()` | Get option value | `blocksy_get_theme_mod('my_option', 'default')` |
| `blocksy_akg()` | Get array key | `blocksy_akg('spacing', $layer, 10)` |
| `blocksy_rand_md5()` | Generate unique ID | `blocksy_rand_md5() => [...]` |
| `blocksy_sync_whole_page()` | Refresh config | `'sync' => blocksy_sync_whole_page([...])` |
| `blocksy_output_font_css()` | Output typography | `blocksy_output_font_css([...])` |
| `blocksy_output_colors()` | Output colors | `blocksy_output_colors([...])` |
| `blocksy_output_responsive()` | Output responsive CSS | `blocksy_output_responsive([...])` |

---

## 📁 File Structure Reference

```
inc/
├── options/woocommerce/
│   ├── single-main.php              # Main single product options
│   ├── single-product-elements.php  # Product elements (layers)
│   ├── single-product-layers.php    # Layer definitions
│   └── single-product-tabs.php      # Product tabs options
├── dynamic-styles/global/woocommerce/
│   └── single-product-layers.php    # CSS generation
└── components/woocommerce/single/
    ├── single.php                   # Rendering logic
    └── helpers.php                  # Helper functions

static/js/customizer/sync/variables/woocommerce/
└── single-product-layers.js         # Live preview sync
```

---

## 🎨 Common Option Types

| Type | Description | Use Case |
|------|-------------|----------|
| `ct-switch` | Toggle on/off | Enable/disable features |
| `ct-slider` | Numeric slider | Spacing, sizes |
| `ct-color-picker` | Color picker | Colors with states |
| `ct-typography` | Font settings | Typography controls |
| `ct-spacing` | 4-side spacing | Padding, margin |
| `ct-background` | Background | BG color/image |
| `ct-panel` | Collapsible panel | Group options |
| `ct-condition` | Conditional display | Show/hide based on value |
| `ct-layers` | Sortable list | Product elements |

---

## 🔍 Important Hooks

### Filters
- `blocksy_woo_single_options_layers:extra` - Add layer options
- `blocksy_woo_single_options_layers:defaults` - Add default layers
- `blocksy:woocommerce:product-single:layout` - Modify layout

### Actions
- `blocksy:woocommerce:product:custom:layer` - Render custom layer
- `blocksy:global-dynamic-css:enqueue` - Add dynamic CSS
- `blocksy:woocommerce:product-single:layout:before` - Before layout
- `blocksy:woocommerce:product-single:layout:after` - After layout

---

## ⚡ Quick Tips

1. **Always use child theme** - Never edit parent theme files
2. **Use hooks/filters** - Don't modify core files
3. **Enable live preview** - Set `'transport' => 'postMessage'`
4. **Test responsive** - Use `'responsive' => true`
5. **Check conditions** - Use `ct-condition` for conditional options
6. **Debug with error_log()** - Log values for debugging

---

## 📚 Full Documentation

See `BLOCKSY_CUSTOMIZER_TECHNICAL_GUIDE_EN.md` for:
- Detailed architecture explanation
- Complete implementation examples
- Best practices
- Debugging guide
- Complete API reference

---

**Quick Start Version**: 1.0.0
**Last Updated**: 2025-11-26

