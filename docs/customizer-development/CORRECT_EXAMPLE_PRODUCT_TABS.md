# ✅ CORRECT EXAMPLE - Product Tabs Layer

Complete working example with proper separation of layer options and design options.

---

## File 1: Layer Definition & Functional Options

**File**: `inc/custom/product-tabs-layer.php`

```php
<?php
/**
 * Product Tabs Layer - Functional Options
 * This file defines the layer and its functional settings
 */

// 1. Define layer with functional options only
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['product_tabs'] = [
        'label' => __('Product Tabs', 'blocksy'),
        'options' => [
            
            // Functional option: Which tabs to show
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
            
            // Functional option: Tab style
            'tab_style' => [
                'label' => __('Tab Style', 'blocksy'),
                'type' => 'ct-radio',
                'value' => 'default',
                'view' => 'text',
                'design' => 'block',
                'choices' => [
                    'default' => __('Default', 'blocksy'),
                    'underline' => __('Underline', 'blocksy'),
                    'boxed' => __('Boxed', 'blocksy'),
                ],
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],
            
            // Functional option: Alignment
            'tab_alignment' => [
                'label' => __('Tab Alignment', 'blocksy'),
                'type' => 'ct-radio',
                'value' => 'left',
                'view' => 'text',
                'design' => 'block',
                'choices' => [
                    'left' => __('Left', 'blocksy'),
                    'center' => __('Center', 'blocksy'),
                    'right' => __('Right', 'blocksy'),
                ],
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],
            
            // Functional option: Spacing
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

// 2. Add to default layout
add_filter('blocksy_woo_single_options_layers:defaults', function($defaults) {
    $defaults[] = [
        'id' => 'product_tabs',
        'enabled' => true,
    ];
    return $defaults;
});

// 3. Render the layer
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    if ($layer['id'] !== 'product_tabs') {
        return;
    }
    
    $tabs_visibility = blocksy_akg('tabs_visibility', $layer, [
        'description' => true,
        'additional_information' => true,
        'reviews' => true,
    ]);
    
    $tab_style = blocksy_akg('tab_style', $layer, 'default');
    $tab_alignment = blocksy_akg('tab_alignment', $layer, 'left');
    
    // Filter tabs based on visibility
    add_filter('woocommerce_product_tabs', function($tabs) use ($tabs_visibility) {
        if (!isset($tabs_visibility['description']) || !$tabs_visibility['description']) {
            unset($tabs['description']);
        }
        if (!isset($tabs_visibility['additional_information']) || !$tabs_visibility['additional_information']) {
            unset($tabs['additional_information']);
        }
        if (!isset($tabs_visibility['reviews']) || !$tabs_visibility['reviews']) {
            unset($tabs['reviews']);
        }
        return $tabs;
    });
    
    // Output tabs with custom attributes
    ?>
    <div class="ct-product-tabs" 
         data-style="<?php echo esc_attr($tab_style); ?>" 
         data-alignment="<?php echo esc_attr($tab_alignment); ?>">
        <?php woocommerce_output_product_data_tabs(); ?>
    </div>
    <?php
}, 10, 1);
```

---

## File 2: Design Options

**File**: `inc/custom/product-tabs-design.php`

```php
<?php
/**
 * Product Tabs - Design Options
 * This file adds design options to the Design tab
 */

// Add design options to Design tab
add_filter('blocksy:options:single_product:elements:design_tab:end', function($options) {
    
    // Only show when product_tabs layer is enabled
    $options[blocksy_rand_md5()] = [
        'type' => 'ct-condition',
        'condition' => ['woo_single_layout:array-ids:product_tabs:enabled' => '!no'],
        'computed_fields' => ['woo_single_layout'],
        'options' => [
            
            // Typography
            'productTabsFont' => [
                'type' => 'ct-typography',
                'label' => __('Tabs Font', 'blocksy'),
                'value' => blocksy_typography_default_values([
                    'size' => '16px',
                    'variation' => 'n6',
                ]),
                'setting' => ['transport' => 'postMessage'],
                'divider' => 'top:full',
            ],

            // Tab Colors
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

            // Active Indicator Color
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

            // Background Color
            'productTabsBackgroundColor' => [
                'label' => __('Background Color', 'blocksy'),
                'type'  => 'ct-color-picker',
                'design' => 'inline',
                'setting' => ['transport' => 'postMessage'],
                'value' => [
                    'default' => [
                        'color' => 'transparent',
                    ],
                ],
                'pickers' => [
                    [
                        'title' => __('Initial', 'blocksy'),
                        'id' => 'default',
                    ],
                ],
            ],

            // Border
            'productTabsBorder' => [
                'label' => __('Border', 'blocksy'),
                'type' => 'ct-border',
                'design' => 'inline',
                'setting' => ['transport' => 'postMessage'],
                'value' => [
                    'width' => 1,
                    'style' => 'solid',
                    'color' => [
                        'color' => 'var(--theme-border-color)',
                    ],
                ],
            ],

            // Border Radius
            'productTabsBorderRadius' => [
                'label' => __('Border Radius', 'blocksy'),
                'type' => 'ct-spacing',
                'setting' => ['transport' => 'postMessage'],
                'value' => blocksy_spacing_value([
                    'top' => '0px',
                    'left' => '0px',
                    'right' => '0px',
                    'bottom' => '0px',
                ]),
                'responsive' => false,
            ],

        ],
    ];

    return $options;
});
```

---

## File 3: Dynamic CSS

**File**: `inc/custom/product-tabs-css.php`

```php
<?php
/**
 * Product Tabs - Dynamic CSS
 */

add_action('blocksy:global-dynamic-css:enqueue', function($args) {
    $css = $args['css'];
    $tablet_css = $args['tablet_css'];
    $mobile_css = $args['mobile_css'];

    // Check if layer is enabled
    $layout = blocksy_get_theme_mod('woo_single_layout', []);
    $tabs_layer = array_filter($layout, function($layer) {
        return $layer['id'] === 'product_tabs' && $layer['enabled'];
    });

    if (empty($tabs_layer)) {
        return;
    }

    // Typography
    blocksy_output_font_css([
        'font_value' => blocksy_get_theme_mod('productTabsFont'),
        'css' => $css,
        'tablet_css' => $tablet_css,
        'mobile_css' => $mobile_css,
        'selector' => '.ct-product-tabs .woocommerce-tabs ul.tabs li a'
    ]);

    // Colors
    blocksy_output_colors([
        'value' => blocksy_get_theme_mod('productTabsColor'),
        'default' => [
            'default' => ['color' => 'var(--theme-text-color)'],
            'active' => ['color' => 'var(--theme-link-hover-color)'],
        ],
        'css' => $css,
        'variables' => [
            'default' => '--tabs-initial-color',
            'active' => '--tabs-active-color',
        ],
    ]);

    // Active Indicator
    blocksy_output_colors([
        'value' => blocksy_get_theme_mod('productTabsActiveIndicatorColor'),
        'default' => [
            'default' => ['color' => 'var(--theme-palette-color-1)'],
        ],
        'css' => $css,
        'variables' => [
            'default' => '--tabs-indicator-color',
        ],
    ]);

    // Background
    blocksy_output_colors([
        'value' => blocksy_get_theme_mod('productTabsBackgroundColor'),
        'default' => [
            'default' => ['color' => 'transparent'],
        ],
        'css' => $css,
        'variables' => [
            'default' => '--tabs-background-color',
        ],
    ]);

    // Border
    blocksy_output_border([
        'css' => $css,
        'selector' => '.ct-product-tabs .woocommerce-tabs',
        'value' => blocksy_get_theme_mod('productTabsBorder'),
    ]);

    // Border Radius
    blocksy_output_spacing([
        'css' => $css,
        'selector' => '.ct-product-tabs .woocommerce-tabs',
        'property' => 'border-radius',
        'value' => blocksy_get_theme_mod('productTabsBorderRadius'),
    ]);

}, 10, 1);
```

---

## File 4: Live Preview Sync (JavaScript)

**File**: `static/js/custom/product-tabs-sync.js`

```javascript
/**
 * Product Tabs - Live Preview Sync
 */

wp.customize('productTabsFont', (value) => {
    value.bind((to) => {
        blocksy_sync_whole_page({
            prefix: 'product',
            loader_selector: '.ct-product-tabs'
        });
    });
});

wp.customize('productTabsColor', (value) => {
    value.bind((to) => {
        blocksy_sync_whole_page({
            prefix: 'product',
            loader_selector: '.ct-product-tabs'
        });
    });
});

wp.customize('productTabsActiveIndicatorColor', (value) => {
    value.bind((to) => {
        blocksy_sync_whole_page({
            prefix: 'product',
            loader_selector: '.ct-product-tabs'
        });
    });
});

wp.customize('productTabsBackgroundColor', (value) => {
    value.bind((to) => {
        blocksy_sync_whole_page({
            prefix: 'product',
            loader_selector: '.ct-product-tabs'
        });
    });
});

wp.customize('productTabsBorder', (value) => {
    value.bind((to) => {
        blocksy_sync_whole_page({
            prefix: 'product',
            loader_selector: '.ct-product-tabs'
        });
    });
});

wp.customize('productTabsBorderRadius', (value) => {
    value.bind((to) => {
        blocksy_sync_whole_page({
            prefix: 'product',
            loader_selector: '.ct-product-tabs'
        });
    });
});
```

---

## File 5: Load All Files

**File**: `functions.php` (in child theme)

```php
<?php
/**
 * Load Product Tabs customization
 */

// 1. Load layer definition and functional options
require_once get_stylesheet_directory() . '/inc/custom/product-tabs-layer.php';

// 2. Load design options
require_once get_stylesheet_directory() . '/inc/custom/product-tabs-design.php';

// 3. Load dynamic CSS
require_once get_stylesheet_directory() . '/inc/custom/product-tabs-css.php';

// 4. Enqueue live preview sync
add_action('customize_preview_init', function() {
    wp_enqueue_script(
        'product-tabs-sync',
        get_stylesheet_directory_uri() . '/static/js/custom/product-tabs-sync.js',
        ['customize-preview', 'ct-customizer'],
        '1.0.0',
        true
    );
});
```

---

## 📍 Result

### In Customizer:

**Product Elements > General Tab:**
- Product Tabs layer appears in drag-and-drop builder
- Click to expand shows: Tabs Visibility, Tab Style, Tab Alignment, Bottom Spacing

**Product Elements > Design Tab:**
- Tabs Font
- Tabs Color (Initial, Active)
- Active Indicator Color
- Background Color
- Border
- Border Radius

All design options only appear when Product Tabs layer is enabled!

---

**Created**: 2025-11-26
**Status**: ✅ Complete & Tested

