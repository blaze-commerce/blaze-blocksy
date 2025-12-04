# ⚠️ IMPORTANT CORRECTION - Design Options Placement

## The Issue

The AI Agent placed design options **inside the layer options** (Product Elements panel), but they should be in the **Design tab** of Product Elements.

---

## ❌ WRONG - What AI Did

```php
// In single-product-layers.php
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['product_tabs'] = [
        'label' => __('Product Tabs', 'blocksy'),
        'options' => [
            // ❌ WRONG: Design options here
            'tab_style' => [...],
            'tab_alignment' => [...],
            'tab_font_size' => [...],
            'tab_color' => [...],
        ],
    ];
    return $options;
});
```

**Result**: Design options appear inside Product Tabs layer settings (wrong location)

---

## ✅ CORRECT - Proper Structure

### Step 1: Layer Options (Functional Settings Only)

**File**: `inc/options/woocommerce/single-product-layers.php`

```php
// Only functional/content options here
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['product_tabs'] = [
        'label' => __('Product Tabs', 'blocksy'),
        'options' => [
            
            // ✅ CORRECT: Only functional options
            'tabs_visibility' => [
                'label' => __('Tabs Visibility', 'blocksy'),
                'type' => 'ct-checkboxes',
                'value' => [
                    'description' => true,
                    'additional_information' => true,
                    'reviews' => true,
                ],
                'choices' => [
                    'description' => __('Description', 'blocksy'),
                    'additional_information' => __('Additional Information', 'blocksy'),
                    'reviews' => __('Reviews', 'blocksy'),
                ],
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],
            
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

### Step 2: Design Options (Visual Settings)

**File**: `inc/options/woocommerce/single-product-elements.php`

Add design options in the **Design tab** using filter:

```php
// Add design options to Design tab
add_filter('blocksy:options:single_product:elements:design_tab:end', function($options) {
    
    // ✅ CORRECT: Design options in Design tab
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
                'divider' => 'top:full',
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
                    'active' => [
                        'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
                    ],
                ],
                'pickers' => [
                    [
                        'title' => __('Initial', 'blocksy'),
                        'id' => 'default',
                        'inherit' => 'var(--theme-text-color)'
                    ],
                    [
                        'title' => __('Active', 'blocksy'),
                        'id' => 'active',
                        'inherit' => 'var(--theme-link-hover-color)'
                    ],
                ],
            ],
            
            'productTabsActiveIndicatorColor' => [
                'label' => __('Active Indicator Color', 'blocksy'),
                'type'  => 'ct-color-picker',
                'design' => 'inline',
                'setting' => ['transport' => 'postMessage'],
                'value' => [
                    'default' => [
                        'color' => 'var(--theme-palette-color-1)',
                    ],
                ],
                'pickers' => [
                    [
                        'title' => __('Initial', 'blocksy'),
                        'id' => 'default',
                    ],
                ],
            ],
            
        ],
    ];
    
    return $options;
});
```

---

## 📍 Key Differences

| Aspect | Layer Options | Design Options |
|--------|--------------|----------------|
| **File** | `single-product-layers.php` | `single-product-elements.php` |
| **Location** | Inside layer settings | Design tab |
| **Filter** | `blocksy_woo_single_options_layers:extra` | `blocksy:options:single_product:elements:design_tab:end` |
| **Purpose** | Functional/content settings | Visual/styling settings |
| **Examples** | Spacing, visibility, text content | Font, colors, borders, backgrounds |
| **Sync** | `['id' => 'woo_single_layout_skip']` | `['transport' => 'postMessage']` |

---

## 🎯 Complete Correct Example

See `CORRECT_EXAMPLE_PRODUCT_TABS.md` for full working example.

---

**Created**: 2025-11-26  
**Priority**: HIGH - Critical correction needed

