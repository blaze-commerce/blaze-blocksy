# Live Preview Troubleshooting Guide - Blocksy Customizer

## 🚨 Critical Issue: Design Changes Don't Reflect in Live Preview

This is the **most common issue** when adding custom Product Elements to Blocksy theme. Even after updating settings in the Customizer, changes don't appear in the live preview, and sometimes don't even apply after clicking "Publish".

---

## 🔍 Root Cause Analysis

When you add a custom Product Element with design options (typography, colors, spacing), you need **THREE components** working together:

### 1. **PHP Options Registration** ✅
- Register layer in Product Elements
- Add design options in Design Tab
- This part is usually done correctly

### 2. **PHP Dynamic CSS Generation** ✅
- Generate CSS based on option values
- This part is usually done correctly

### 3. **JavaScript Sync Configuration** ❌
- **THIS IS WHAT'S MISSING!**
- Without this, live preview won't work
- Changes won't reflect until full page refresh

---

## 🎯 The Complete Solution

### Problem Symptoms

- ✅ Options appear in Customizer
- ✅ Element renders on frontend
- ❌ Changing design settings doesn't update live preview
- ❌ Even after clicking "Publish", design doesn't change
- ❌ Need to hard refresh (Ctrl+Shift+R) to see changes

### Why This Happens

Blocksy uses a sophisticated live preview system that requires JavaScript sync configuration. When you change a setting in the Customizer:

1. **Without JS Sync**: Nothing happens in preview
2. **With `blocksy_sync_whole_page()`**: Partial refresh (reloads element)
3. **With JS Sync Variables**: Instant CSS variable update (best UX)

---

## 📝 Step-by-Step Fix

Let's use **Product Stock Element** as an example.

### Step 1: Identify Your Element

Your PHP code in `sample.php` or similar:

```php
class BlazeBlocksy_Product_Stock_Customizer {
    public function __construct() {
        // Register layer
        add_filter('blocksy_woo_single_options_layers:defaults', [...]);
        add_filter('blocksy_woo_single_options_layers:extra', [...]);
        
        // Register design options
        add_filter('blocksy:options:single_product:elements:design_tab:end', [...]);
        
        // Generate CSS
        add_action('blocksy:global-dynamic-css:enqueue', [...]);
    }
}
```

**Design options you added:**
- `productStockFont` - Typography
- `productStockInStockColor` - Color picker
- `productStockOutOfStockColor` - Color picker
- `productStockOnBackorderColor` - Color picker

**Element details:**
- Layer ID: `product_stock_element`
- CSS Class: `.ct-product-stock-element`
- Spacing option: `spacing` (default: 20px)

---

### Step 2: Add JavaScript Sync Configuration

Open file: `static/js/customizer/sync/variables/woocommerce/single-product-layers.js`

This file contains the JavaScript configuration for live preview sync.

#### 2.1: Add Selector Mapping

Find the `selectorsMap` object (around line 7-37) and add your element:

```javascript
const collectVariablesForLayers = (v) => {
    let variables = []
    v.map((layer) => {
        let selectorsMap = {
            product_title: '.entry-summary-items > .entry-title',
            product_rating: '.entry-summary-items > .woocommerce-product-rating',
            product_price: '.entry-summary-items > .price',
            // ... other elements ...
            product_waitlist: '.entry-summary-items > .ct-product-waitlist',
            product_attributes: '.entry-summary-items > .ct-product-attributes',

            // ✅ ADD YOUR ELEMENT HERE
            product_stock_element: '.entry-summary-items > .ct-product-stock-element',
        }
```

**Important**: The selector must match where your element is rendered in the DOM.

#### 2.2: Add Default Spacing Value

Find the `switch` statement for default spacing (around line 50-88) and add your case:

```javascript
switch (layer.id) {
    case 'product_breadcrumbs':
        defaultValue = 10
        break
    case 'product_title':
        defaultValue = 10
        break
    // ... other cases ...
    case 'product_waitlist':
        defaultValue = 35
        break

    // ✅ ADD YOUR CASE HERE
    case 'product_stock_element':
        defaultValue = 20  // Match your PHP default
        break

    default:
        break
}
```

#### 2.3: Add Design Options Sync Variables

Find the `getWooSingleLayersVariablesFor` function (around line 375) and add your options **before the closing `})`**:

```javascript
export const getWooSingleLayersVariablesFor = () => ({
    woo_single_layout: collectVariablesForLayers,
    woo_single_split_layout: (v) => {
        return [
            ...collectVariablesForLayers(v.left),
            ...collectVariablesForLayers(v.right),
        ]
    },

    // ... existing options (breadcrumbs, title, price, etc.) ...

    entry_summary_container_border_radius: {
        selector: '.product[class*=top-gallery] .entry-summary',
        type: 'spacing',
        variable: 'container-border-radius',
        responsive: true,
    },

    // ✅ ADD YOUR OPTIONS HERE (before closing })

    // Product Stock - Typography
    ...typographyOption({
        id: 'productStockFont',
        selector: '.entry-summary .ct-product-stock-element',
    }),

    // Product Stock - In Stock Color
    productStockInStockColor: {
        selector: '.entry-summary .ct-product-stock-element.ct-product-stock-in-stock',
        variable: 'theme-text-color',
        type: 'color',
    },

    // Product Stock - Out of Stock Color
    productStockOutOfStockColor: {
        selector: '.entry-summary .ct-product-stock-element.ct-product-stock-out-of-stock',
        variable: 'theme-text-color',
        type: 'color',
    },

    // Product Stock - On Backorder Color
    productStockOnBackorderColor: {
        selector: '.entry-summary .ct-product-stock-element.ct-product-stock-on-backorder',
        variable: 'theme-text-color',
        type: 'color',
    },
})
```

---

### Step 3: Rebuild JavaScript Assets

After editing the JavaScript file, you need to rebuild the assets:

```bash
# Navigate to theme directory
cd wp-content/themes/blocksy

# Install dependencies (if not already done)
npm install

# Build assets
npm run build

# Or for development with watch mode
npm run dev
```

---

### Step 4: Clear Cache and Test

1. **Clear browser cache** (Ctrl+Shift+R)
2. **Clear WordPress cache** (if using cache plugin)
3. **Refresh Customizer**
4. **Test live preview**:
   - Change font size → Should update instantly
   - Change colors → Should update instantly
   - Change spacing → Should update instantly

---

## 🔧 Understanding the Sync Configuration

### Typography Sync

```javascript
...typographyOption({
    id: 'productStockFont',  // Must match PHP option ID
    selector: '.entry-summary .ct-product-stock-element',  // CSS selector
}),
```

This automatically creates sync for:
- Font family
- Font weight
- Font style
- Font size
- Line height
- Letter spacing
- Text transform

### Color Sync

```javascript
productStockInStockColor: {
    selector: '.entry-summary .ct-product-stock-element.ct-product-stock-in-stock',
    variable: 'theme-text-color',  // CSS variable name
    type: 'color',  // or 'color:default', 'color:hover'
},
```

**Important**: The `variable` must match what you use in `blocksy_output_colors()` in PHP.

### Multiple Color States

For options with multiple color pickers (default, hover, etc.):

```javascript
myOptionColor: [
    {
        selector: '.my-element',
        variable: 'theme-text-color',
        type: 'color:default',
    },
    {
        selector: '.my-element',
        variable: 'theme-link-hover-color',
        type: 'color:hover',
    },
],
```

---

## 📋 Complete Example: Product Stock Element

### File Structure

```
wp-content/themes/blocksy/
├── inc/
│   └── custom/
│       └── product-stock-customizer.php  ← PHP code
└── static/
    └── js/
        └── customizer/
            └── sync/
                └── variables/
                    └── woocommerce/
                        └── single-product-layers.js  ← JS sync
```

### PHP Code (product-stock-customizer.php)

```php
<?php
class BlazeBlocksy_Product_Stock_Customizer {
    public function __construct() {
        add_filter('blocksy_woo_single_options_layers:defaults', [$this, 'register_layer_defaults']);
        add_filter('blocksy_woo_single_options_layers:extra', [$this, 'register_layer_options']);
        add_action('blocksy:woocommerce:product:custom:layer', [$this, 'render_layer']);
        add_filter('blocksy:options:single_product:elements:design_tab:end', [$this, 'register_design_options']);
        add_action('blocksy:global-dynamic-css:enqueue', [$this, 'generate_dynamic_css'], 10, 1);
    }

    public function register_layer_defaults($defaults) {
        $defaults[] = [
            'id' => 'product_stock_element',
            'enabled' => false,
        ];
        return $defaults;
    }

    public function register_layer_options($options) {
        $options['product_stock_element'] = [
            'label' => __('Product Stock', 'blaze-blocksy'),
            'options' => [
                'spacing' => [
                    'label' => __('Bottom Spacing', 'blaze-blocksy'),
                    'type' => 'ct-slider',
                    'min' => 0,
                    'max' => 100,
                    'value' => 20,
                    'responsive' => true,
                    'sync' => ['id' => 'woo_single_layout_skip'],
                ],
            ],
        ];
        return $options;
    }

    public function register_design_options($options) {
        $options[blocksy_rand_md5()] = [
            'type' => 'ct-condition',
            'condition' => ['woo_single_layout:array-ids:product_stock_element:enabled' => '!no'],
            'computed_fields' => ['woo_single_layout'],
            'options' => [
                blocksy_rand_md5() => ['type' => 'ct-divider'],
                blocksy_rand_md5() => [
                    'type' => 'ct-title',
                    'label' => __('Product Stock', 'blaze-blocksy'),
                ],

                // Typography
                'productStockFont' => [
                    'type' => 'ct-typography',
                    'label' => __('Font', 'blaze-blocksy'),
                    'value' => blocksy_typography_default_values([
                        'size' => '14px',
                        'variation' => 'n4',
                    ]),
                    'sync' => blocksy_sync_whole_page([
                        'prefix' => 'product',
                        'loader_selector' => '.ct-product-stock-element',
                    ]),
                ],

                // In Stock Color
                'productStockInStockColor' => [
                    'label' => __('In Stock Color', 'blaze-blocksy'),
                    'type' => 'ct-color-picker',
                    'design' => 'inline',
                    'sync' => blocksy_sync_whole_page([
                        'prefix' => 'product',
                        'loader_selector' => '.ct-product-stock-element',
                    ]),
                    'value' => [
                        'default' => ['color' => '#46a529'],
                    ],
                    'pickers' => [
                        ['title' => __('Color', 'blaze-blocksy'), 'id' => 'default'],
                    ],
                ],

                // Out of Stock Color
                'productStockOutOfStockColor' => [
                    'label' => __('Out of Stock Color', 'blaze-blocksy'),
                    'type' => 'ct-color-picker',
                    'design' => 'inline',
                    'sync' => blocksy_sync_whole_page([
                        'prefix' => 'product',
                        'loader_selector' => '.ct-product-stock-element',
                    ]),
                    'value' => [
                        'default' => ['color' => '#dc3545'],
                    ],
                    'pickers' => [
                        ['title' => __('Color', 'blaze-blocksy'), 'id' => 'default'],
                    ],
                ],

                // On Backorder Color
                'productStockOnBackorderColor' => [
                    'label' => __('On Backorder Color', 'blaze-blocksy'),
                    'type' => 'ct-color-picker',
                    'design' => 'inline',
                    'sync' => blocksy_sync_whole_page([
                        'prefix' => 'product',
                        'loader_selector' => '.ct-product-stock-element',
                    ]),
                    'value' => [
                        'default' => ['color' => '#f0ad4e'],
                    ],
                    'pickers' => [
                        ['title' => __('Color', 'blaze-blocksy'), 'id' => 'default'],
                    ],
                ],
            ],
        ];
        return $options;
    }

    public function render_layer($layer) {
        if ('product_stock_element' !== $layer['id']) {
            return;
        }

        global $product;
        if (!$product) {
            return;
        }

        $availability = $product->get_availability();
        $stock_status = $product->get_stock_status();

        if (empty($availability['availability'])) {
            return;
        }

        $status_class = 'in-stock';
        if ('outofstock' === $stock_status) {
            $status_class = 'out-of-stock';
        } elseif ('onbackorder' === $stock_status) {
            $status_class = 'on-backorder';
        }

        echo '<div class="ct-product-stock-element ct-product-stock-' . esc_attr($status_class) . '" data-element="product_stock_element">';
        echo '<span class="stock ' . esc_attr($availability['class']) . '">' . wp_kses_post($availability['availability']) . '</span>';
        echo '</div>';
    }

    public function generate_dynamic_css($args) {
        $css = $args['css'];
        $tablet_css = $args['tablet_css'];
        $mobile_css = $args['mobile_css'];

        // Check if layer is enabled
        $layout = blocksy_get_theme_mod('woo_single_layout', []);
        $layer_exists = false;

        foreach ($layout as $layer) {
            if (isset($layer['id']) && 'product_stock_element' === $layer['id'] && !empty($layer['enabled'])) {
                $layer_exists = true;
                break;
            }
        }

        if (!$layer_exists) {
            return;
        }

        // Typography
        blocksy_output_font_css([
            'font_value' => blocksy_get_theme_mod('productStockFont', blocksy_typography_default_values([
                'size' => '14px',
                'variation' => 'n4',
            ])),
            'css' => $css,
            'tablet_css' => $tablet_css,
            'mobile_css' => $mobile_css,
            'selector' => '.ct-product-stock-element',
        ]);

        // In Stock Color
        blocksy_output_colors([
            'value' => blocksy_get_theme_mod('productStockInStockColor'),
            'default' => [
                'default' => ['color' => '#46a529'],
            ],
            'css' => $css,
            'variables' => [
                'default' => [
                    'selector' => '.ct-product-stock-element.ct-product-stock-in-stock',
                    'variable' => 'theme-text-color',
                ],
            ],
        ]);

        // Out of Stock Color
        blocksy_output_colors([
            'value' => blocksy_get_theme_mod('productStockOutOfStockColor'),
            'default' => [
                'default' => ['color' => '#dc3545'],
            ],
            'css' => $css,
            'variables' => [
                'default' => [
                    'selector' => '.ct-product-stock-element.ct-product-stock-out-of-stock',
                    'variable' => 'theme-text-color',
                ],
            ],
        ]);

        // On Backorder Color
        blocksy_output_colors([
            'value' => blocksy_get_theme_mod('productStockOnBackorderColor'),
            'default' => [
                'default' => ['color' => '#f0ad4e'],
            ],
            'css' => $css,
            'variables' => [
                'default' => [
                    'selector' => '.ct-product-stock-element.ct-product-stock-on-backorder',
                    'variable' => 'theme-text-color',
                ],
            ],
        ]);
    }
}

new BlazeBlocksy_Product_Stock_Customizer();
```

### JavaScript Sync Code (single-product-layers.js)

**Location**: `static/js/customizer/sync/variables/woocommerce/single-product-layers.js`

Add these changes to the existing file:

#### Change 1: Add to selectorsMap (around line 36)

```javascript
const collectVariablesForLayers = (v) => {
    let variables = []
    v.map((layer) => {
        let selectorsMap = {
            product_title: '.entry-summary-items > .entry-title',
            product_rating: '.entry-summary-items > .woocommerce-product-rating',
            product_price: '.entry-summary-items > .price',
            product_desc: '.entry-summary-items > .woocommerce-product-details__short-description',
            product_add_to_cart: '.entry-summary-items > .ct-product-add-to-cart',
            product_meta: '.entry-summary-items > .product_meta',
            product_payment_methods: '.entry-summary-items > .ct-payment-methods',
            additional_info: '.entry-summary-items > .ct-product-additional-info',
            product_tabs: '.entry-summary-items > .woocommerce-tabs',
            product_breadcrumbs: '.entry-summary-items > .ct-breadcrumbs',
            product_brands: '.entry-summary-items > .ct-product-brands-single',
            product_sharebox: '.entry-summary-items > .ct-share-box',
            free_shipping: '.entry-summary-items > .ct-shipping-progress-single',
            product_actions: '.entry-summary-items > .ct-product-additional-actions',
            product_countdown: '.entry-summary-items > .ct-product-sale-countdown',
            product_stock_scarcity: '.entry-summary-items > .ct-product-stock-scarcity',
            product_waitlist: '.entry-summary-items > .ct-product-waitlist',
            product_attributes: '.entry-summary-items > .ct-product-attributes',

            // ✅ ADD THIS LINE
            product_stock_element: '.entry-summary-items > .ct-product-stock-element',
        }
        // ... rest of the function
    })
}
```

#### Change 2: Add to switch statement (around line 85)

```javascript
switch (layer.id) {
    case 'product_breadcrumbs':
        defaultValue = 10
        break
    case 'product_title':
        defaultValue = 10
        break
    case 'product_rating':
        defaultValue = 10
        break
    case 'product_price':
        defaultValue = 35
        break
    case 'product_desc':
        defaultValue = 35
        break
    case 'product_add_to_cart':
        defaultValue = 35
        break
    case 'product_meta':
        defaultValue = 10
        break
    case 'product_payment_methods':
        defaultValue = 10
        break
    case 'additional_info':
        defaultValue = 10
        break
    case 'product_actions':
        defaultValue = 35
        break
    case 'product_countdown':
        defaultValue = 35
        break
    case 'product_waitlist':
        defaultValue = 35
        break

    // ✅ ADD THIS CASE
    case 'product_stock_element':
        defaultValue = 20
        break

    default:
        break
}
```

#### Change 3: Add to getWooSingleLayersVariablesFor (before line 552)

```javascript
export const getWooSingleLayersVariablesFor = () => ({
    woo_single_layout: collectVariablesForLayers,
    woo_single_split_layout: (v) => {
        return [
            ...collectVariablesForLayers(v.left),
            ...collectVariablesForLayers(v.right),
        ]
    },

    // ... all existing options ...

    entry_summary_container_border_radius: {
        selector: '.product[class*=top-gallery] .entry-summary',
        type: 'spacing',
        variable: 'container-border-radius',
        responsive: true,
    },

    // ✅ ADD THESE LINES BEFORE THE CLOSING })

    // Product Stock - Typography
    ...typographyOption({
        id: 'productStockFont',
        selector: '.entry-summary .ct-product-stock-element',
    }),

    // Product Stock - In Stock Color
    productStockInStockColor: {
        selector: '.entry-summary .ct-product-stock-element.ct-product-stock-in-stock',
        variable: 'theme-text-color',
        type: 'color',
    },

    // Product Stock - Out of Stock Color
    productStockOutOfStockColor: {
        selector: '.entry-summary .ct-product-stock-element.ct-product-stock-out-of-stock',
        variable: 'theme-text-color',
        type: 'color',
    },

    // Product Stock - On Backorder Color
    productStockOnBackorderColor: {
        selector: '.entry-summary .ct-product-stock-element.ct-product-stock-on-backorder',
        variable: 'theme-text-color',
        type: 'color',
    },
})
```

---

## 🎓 Understanding How It Works

### The Live Preview Flow

1. **User changes setting** in Customizer
2. **JavaScript detects change** via `wp.customize('optionId', ...)`
3. **Sync configuration** determines what to do:
   - **CSS Variable Update**: Instant, no reload
   - **Selective Refresh**: Partial reload of element
   - **Full Refresh**: Reload entire page
4. **Preview updates** in real-time

### Why Three Locations?

#### 1. Selector Mapping (selectorsMap)
- Maps layer ID to CSS selector
- Used for spacing sync
- Enables responsive spacing updates

#### 2. Default Spacing (switch statement)
- Provides fallback value
- Ensures consistent spacing
- Matches PHP default value

#### 3. Design Options Sync (getWooSingleLayersVariablesFor)
- Maps option ID to CSS selector and variable
- Enables typography and color sync
- Creates instant preview updates

---

## 🔍 Debugging Live Preview Issues

### Check 1: Verify JavaScript File is Loaded

Open browser console (F12) and check:

```javascript
// Check if sync variables are registered
wp.customize.settings.values
```

Look for your option IDs: `productStockFont`, `productStockInStockColor`, etc.

### Check 2: Verify CSS Variables are Applied

Inspect element in browser:

```css
.ct-product-stock-element {
    /* Should see CSS variables */
    --theme-font-family: ...;
    --theme-font-size: ...;
    --theme-text-color: ...;
}
```

### Check 3: Monitor Customizer Changes

```javascript
// In browser console
wp.customize('productStockFont', (value) => {
    value.bind((to) => {
        console.log('Font changed:', to);
    });
});
```

### Check 4: Verify Assets are Built

```bash
# Check if bundle exists
ls -la static/bundle/main.js

# Check file modification time
stat static/bundle/main.js
```

### Check 5: Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| No preview update | JS not built | Run `npm run build` |
| Partial update only | Missing sync config | Add to `getWooSingleLayersVariablesFor` |
| Wrong selector | Selector mismatch | Match PHP and JS selectors |
| CSS not applied | Variable name mismatch | Match `variable` in JS and PHP |
| Spacing not working | Missing in selectorsMap | Add to `selectorsMap` object |

---

## 📚 Advanced Patterns

### Pattern 1: Multiple Color States

For hover, active, or other states:

```javascript
myElementColor: [
    {
        selector: '.my-element',
        variable: 'theme-text-color',
        type: 'color:default',
    },
    {
        selector: '.my-element:hover',
        variable: 'theme-link-hover-color',
        type: 'color:hover',
    },
    {
        selector: '.my-element.active',
        variable: 'theme-link-active-color',
        type: 'color:active',
    },
],
```

### Pattern 2: Responsive Typography

Typography options automatically handle responsive values:

```javascript
...typographyOption({
    id: 'myElementFont',
    selector: '.my-element',
}),
```

This syncs:
- Desktop font size
- Tablet font size
- Mobile font size
- All other typography properties

### Pattern 3: Conditional Selectors

Different selectors based on conditions:

```javascript
myElementColor: {
    selector: '.my-element',
    variable: 'theme-text-color',
    type: 'color',
    extractValue: (value) => {
        // Custom logic
        return value.default?.color || '#000000';
    },
},
```

### Pattern 4: Background Colors

For background with gradient support:

```javascript
myElementBackground: [
    {
        selector: '.my-element',
        variable: 'background-color',
        type: 'color:default',
    },
    {
        selector: '.my-element',
        variable: 'background-gradient',
        type: 'color:gradient',
    },
],
```

---

## ✅ Checklist: Before Asking for Help

Before reporting a live preview issue, verify:

- [ ] JavaScript sync configuration added to `single-product-layers.js`
- [ ] Element added to `selectorsMap` object
- [ ] Default spacing added to `switch` statement
- [ ] Design options added to `getWooSingleLayersVariablesFor`
- [ ] Assets rebuilt with `npm run build`
- [ ] Browser cache cleared (Ctrl+Shift+R)
- [ ] WordPress cache cleared
- [ ] Customizer refreshed
- [ ] Browser console checked for errors
- [ ] CSS selectors match between PHP and JS
- [ ] CSS variable names match between PHP and JS
- [ ] Option IDs match exactly (case-sensitive)

---

## 🎯 Quick Reference

### File Locations

| Component | File Path |
|-----------|-----------|
| PHP Options | `inc/custom/your-customizer.php` |
| PHP Dynamic CSS | Same file, `generate_dynamic_css()` method |
| JS Sync Config | `static/js/customizer/sync/variables/woocommerce/single-product-layers.js` |
| Built Assets | `static/bundle/main.js` |

### Build Commands

```bash
# Install dependencies
npm install

# Build for production
npm run build

# Build for development (with watch)
npm run dev

# Clean build
rm -rf static/bundle && npm run build
```

### Key Functions

| Function | Purpose |
|----------|---------|
| `typographyOption()` | Sync typography options |
| `collectVariablesForLayers()` | Sync layer spacing |
| `getWooSingleLayersVariablesFor()` | Export all sync variables |
| `blocksy_sync_whole_page()` | Fallback selective refresh |

---

**Document Version**: 1.0.0
**Last Updated**: 2025-11-26
**Applies To**: Blocksy Theme 2.0+


