# Code Templates - Blocksy Customizer

Ready-to-use code templates for AI Agent.

---

## ⚠️ CRITICAL: Layer Options vs Design Options

**MUST READ BEFORE USING TEMPLATES**

### The Two Separate Locations:

| Type | Location | File | Filter |
|------|----------|------|--------|
| **Layer Options** | Inside layer settings | `single-product-layers.php` | `blocksy_woo_single_options_layers:extra` |
| **Design Options** | Design tab | `single-product-elements.php` | `blocksy:options:single_product:elements:design_tab:end` |

### What Goes Where:

**Layer Options (Functional/Content):**
- ✅ Text content, titles, descriptions
- ✅ Visibility toggles, checkboxes
- ✅ Icon selection
- ✅ Layout choices (style, alignment)
- ✅ Spacing (bottom spacing for layer)
- ❌ NO fonts, colors, backgrounds, borders

**Design Options (Visual/Styling):**
- ✅ Typography (fonts, sizes, weights)
- ✅ Colors (text, background, borders)
- ✅ Backgrounds (solid, gradient, image)
- ✅ Spacing (padding, margin)
- ✅ Borders and border radius
- ❌ NO content or functional settings

### Example - Product Tabs:

**❌ WRONG:**
```php
// In layer options - DO NOT DO THIS
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['product_tabs'] = [
        'options' => [
            'tab_font' => [...],  // ❌ WRONG - Design option in layer
            'tab_color' => [...], // ❌ WRONG - Design option in layer
        ],
    ];
});
```

**✅ CORRECT:**
```php
// Layer options - Functional only
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['product_tabs'] = [
        'options' => [
            'tab_style' => [...],     // ✅ Functional
            'tab_alignment' => [...], // ✅ Functional
            'spacing' => [...],       // ✅ Functional
        ],
    ];
});

// Design options - In Design tab
add_filter('blocksy:options:single_product:elements:design_tab:end', function($options) {
    $options[blocksy_rand_md5()] = [
        'condition' => ['woo_single_layout:array-ids:product_tabs:enabled' => '!no'],
        'options' => [
            'productTabsFont' => [...],  // ✅ Design
            'productTabsColor' => [...], // ✅ Design
        ],
    ];
});
```

---

## Template 1: Toggle Setting (Simple)

**Use Case**: Add toggle on/off for a feature

```php
<?php
/**
 * File: inc/options/woocommerce/my-toggle-option.php
 */

$options = [
    'my_feature_toggle' => [
        'label' => __( 'My Feature', 'blocksy' ),
        'type' => 'ct-panel',
        'switch' => true,
        'value' => 'yes',
        'sync' => blocksy_sync_whole_page([
            'prefix' => 'product',
            'loader_selector' => '.type-product'
        ]),
        'inner-options' => [
            // Additional options when enabled
            'my_feature_text' => [
                'label' => __( 'Feature Text', 'blocksy' ),
                'type' => 'text',
                'design' => 'block',
                'value' => __( 'Default text', 'blocksy' ),
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],
        ],
    ],
];
```

**Usage in template:**

```php
<?php
$is_enabled = blocksy_get_theme_mod('my_feature_toggle', 'yes');

if ($is_enabled === 'yes') {
    $text = blocksy_get_theme_mod('my_feature_text', 'Default text');
    echo '<div class="my-feature">' . esc_html($text) . '</div>';
}
```

---

## Template 2: Custom Layer (Complete)

**Use Case**: Add new element to Product Elements

**⚠️ IMPORTANT**: This template shows ONLY functional/content options for the layer.
Design options (fonts, colors, backgrounds, borders) should be added separately in the Design tab (see Template 3).

```php
<?php
/**
 * File: inc/custom/my-custom-layer.php
 * FUNCTIONAL OPTIONS ONLY - Design options go in Design tab
 */

// 1. Define layer options (FUNCTIONAL ONLY)
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['my_custom_layer'] = [
        'label' => __('My Custom Layer', 'blocksy'),
        'options' => [

            // ✅ CORRECT: Functional options only

            // Text option
            'custom_title' => [
                'label' => __('Title', 'blocksy'),
                'type' => 'text',
                'design' => 'block',
                'value' => __('Custom Title', 'blocksy'),
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],

            // Textarea option
            'custom_content' => [
                'label' => __('Content', 'blocksy'),
                'type' => 'wp-editor',
                'value' => '',
                'sync' => blocksy_sync_whole_page([
                    'prefix' => 'product',
                    'loader_selector' => '.entry-summary-items'
                ]),
            ],

            // Toggle option
            'show_icon' => [
                'label' => __('Show Icon', 'blocksy'),
                'type' => 'ct-switch',
                'value' => 'yes',
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],

            // Spacing option (required for all layers)
            'spacing' => [
                'label' => __('Bottom Spacing', 'blocksy'),
                'type' => 'ct-slider',
                'min' => 0,
                'max' => 100,
                'value' => 20,
                'responsive' => true,
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],

            // ❌ DO NOT add design options here:
            // ❌ NO fonts, colors, backgrounds, borders
            // ❌ Design options belong in the Design tab (see Template 3)
        ],
    ];

    return $options;
});

// 2. Add to default layout
add_filter('blocksy_woo_single_options_layers:defaults', function($defaults) {
    $defaults[] = [
        'id' => 'my_custom_layer',
        'enabled' => false, // Default disabled
    ];
    
    return $defaults;
});

// 3. Render the layer
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    if ($layer['id'] !== 'my_custom_layer') {
        return;
    }
    
    $title = blocksy_akg('custom_title', $layer, 'Custom Title');
    $content = blocksy_akg('custom_content', $layer, '');
    $show_icon = blocksy_akg('show_icon', $layer, 'yes');
    
    ?>
    <div class="ct-custom-layer">
        <?php if (!empty($title)): ?>
            <h3 class="custom-layer-title">
                <?php if ($show_icon === 'yes'): ?>
                    <span class="icon">★</span>
                <?php endif; ?>
                <?php echo esc_html($title); ?>
            </h3>
        <?php endif; ?>
        
        <?php if (!empty($content)): ?>
            <div class="custom-layer-content">
                <?php echo wp_kses_post($content); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}, 10, 1);
```

**Load in functions.php:**

```php
require_once get_template_directory() . '/inc/custom/my-custom-layer.php';
```

---

## Template 3: Design Options with Live Preview

**Use Case**: Add styling options for custom layer in the Design tab

**⚠️ CRITICAL**: Design options MUST be added to the Design tab, NOT in layer options!

**Location**: `inc/options/woocommerce/single-product-elements.php` OR use filter

**Method 1: Using Filter (Recommended)**

```php
<?php
/**
 * File: inc/custom/my-custom-layer-design.php
 * Add design options to Design tab using filter
 */

add_filter('blocksy:options:single_product:elements:design_tab:end', function($options) {

    // Only show when layer is enabled
    $options[blocksy_rand_md5()] = [
        'type' => 'ct-condition',
        'condition' => ['woo_single_layout:array-ids:my_custom_layer:enabled' => '!no'],
        'computed_fields' => ['woo_single_layout'],
        'options' => [

            // Section title
            blocksy_rand_md5() => [
                'type' => 'ct-title',
                'label' => __('My Custom Layer Design', 'blocksy'),
            ],

        // Typography
        'customLayerTitleFont' => [
            'type' => 'ct-typography',
            'label' => __('Title Font', 'blocksy'),
            'value' => blocksy_typography_default_values([
                'size' => '20px',
                'variation' => 'n6',
                'line-height' => '1.3',
            ]),
            'setting' => ['transport' => 'postMessage'],
        ],

        // Color Picker (Single State)
        'customLayerTitleColor' => [
            'label' => __('Title Color', 'blocksy'),
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
                    'inherit' => 'var(--theme-heading-color)'
                ],
            ],
        ],

        // Color Picker (Multiple States)
        'customLayerLinkColor' => [
            'label' => __('Link Color', 'blocksy'),
            'type'  => 'ct-color-picker',
            'design' => 'inline',
            'setting' => ['transport' => 'postMessage'],
            'value' => [
                'default' => [
                    'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
                ],
                'hover' => [
                    'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
                ],
            ],
            'pickers' => [
                [
                    'title' => __('Initial', 'blocksy'),
                    'id' => 'default',
                    'inherit' => 'var(--theme-link-initial-color)'
                ],
                [
                    'title' => __('Hover', 'blocksy'),
                    'id' => 'hover',
                    'inherit' => 'var(--theme-link-hover-color)'
                ],
            ],
        ],

        // Background
        'customLayerBackground' => [
            'label' => __('Background', 'blocksy'),
            'type' => 'ct-background',
            'design' => 'block:right',
            'responsive' => true,
            'sync' => 'live',
            'value' => blocksy_background_default_value([
                'backgroundColor' => [
                    'default' => [
                        'color' => 'transparent',
                    ],
                ],
            ]),
            'setting' => ['transport' => 'postMessage'],
        ],

        // Spacing (4 sides)
        'customLayerPadding' => [
            'label' => __('Padding', 'blocksy'),
            'type' => 'ct-spacing',
            'value' => blocksy_spacing_value([
                'linked' => true,
                'top' => '15px',
                'left' => '15px',
                'right' => '15px',
                'bottom' => '15px',
            ]),
            'responsive' => true,
            'setting' => ['transport' => 'postMessage'],
        ],

        // Border
        'customLayerBorder' => [
            'label' => __('Border', 'blocksy'),
            'type' => 'ct-border',
            'design' => 'inline',
            'value' => [
                'width' => 1,
                'style' => 'solid',
                'color' => [
                    'color' => 'rgba(0,0,0,0.1)',
                ],
            ],
            'responsive' => true,
            'setting' => ['transport' => 'postMessage'],
        ],

        // Border Radius
        'customLayerBorderRadius' => [
            'label' => __('Border Radius', 'blocksy'),
            'type' => 'ct-spacing',
            'value' => blocksy_spacing_value([
                'linked' => true,
                'top' => '0px',
                'left' => '0px',
                'right' => '0px',
                'bottom' => '0px',
            ]),
            'responsive' => true,
            'setting' => ['transport' => 'postMessage'],
        ],

        ],
    ];

    return $options;
});
```

**Method 2: Direct Addition (Alternative)**

If you have access to edit `single-product-elements.php` directly, add inside the Design tab array:

```php
// In inc/options/woocommerce/single-product-elements.php
// Inside the Design tab options array

blocksy_rand_md5() => [
    'type' => 'ct-condition',
    'condition' => ['woo_single_layout:array-ids:my_custom_layer:enabled' => '!no'],
    'computed_fields' => ['woo_single_layout'],
    'options' => [
        // Same options as above
    ],
],
```

**Key Points:**

1. ✅ Design options go in **Design tab**, not layer options
2. ✅ Use filter `blocksy:options:single_product:elements:design_tab:end`
3. ✅ Wrap in `ct-condition` to show only when layer is enabled
4. ✅ Use `'setting' => ['transport' => 'postMessage']` for live preview
5. ✅ Add `'divider' => 'top:full'` to first option for visual separation

---

## Template 4: Dynamic CSS Generation

**Use Case**: Generate CSS from design options

```php
<?php
/**
 * File: inc/custom/my-custom-layer-dynamic-css.php
 */

add_action('blocksy:global-dynamic-css:enqueue', function($args) {
    $css = $args['css'];
    $tablet_css = $args['tablet_css'];
    $mobile_css = $args['mobile_css'];

    // Check if layer is enabled
    $layout = blocksy_get_theme_mod('woo_single_layout', []);
    $custom_layer = array_filter($layout, function($layer) {
        return $layer['id'] === 'my_custom_layer' && $layer['enabled'];
    });

    if (empty($custom_layer)) {
        return; // Layer not enabled, skip CSS generation
    }

    // 1. Typography
    blocksy_output_font_css([
        'font_value' => blocksy_get_theme_mod(
            'customLayerTitleFont',
            blocksy_typography_default_values([
                'size' => '20px',
                'variation' => 'n6',
            ])
        ),
        'css' => $css,
        'tablet_css' => $tablet_css,
        'mobile_css' => $mobile_css,
        'selector' => '.ct-custom-layer .custom-layer-title'
    ]);

    // 2. Single Color
    blocksy_output_colors([
        'value' => blocksy_get_theme_mod('customLayerTitleColor'),
        'default' => [
            'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
        ],
        'css' => $css,
        'variables' => [
            'default' => [
                'selector' => '.ct-custom-layer .custom-layer-title',
                'variable' => 'theme-heading-color'
            ],
        ],
    ]);

    // 3. Multiple Color States
    blocksy_output_colors([
        'value' => blocksy_get_theme_mod('customLayerLinkColor'),
        'default' => [
            'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
            'hover' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
        ],
        'css' => $css,
        'variables' => [
            'default' => [
                'selector' => '.ct-custom-layer a',
                'variable' => 'theme-link-initial-color'
            ],
            'hover' => [
                'selector' => '.ct-custom-layer a',
                'variable' => 'theme-link-hover-color'
            ],
        ],
    ]);

    // 4. Background
    blocksy_output_background_css([
        'selector' => '.ct-custom-layer',
        'css' => $css,
        'tablet_css' => $tablet_css,
        'mobile_css' => $mobile_css,
        'value' => blocksy_get_theme_mod(
            'customLayerBackground',
            blocksy_background_default_value([
                'backgroundColor' => [
                    'default' => [
                        'color' => 'transparent',
                    ],
                ],
            ])
        ),
        'responsive' => true,
    ]);

    // 5. Padding (Spacing)
    blocksy_output_spacing([
        'css' => $css,
        'tablet_css' => $tablet_css,
        'mobile_css' => $mobile_css,
        'selector' => '.ct-custom-layer',
        'property' => 'padding',
        'value' => blocksy_get_theme_mod(
            'customLayerPadding',
            blocksy_spacing_value([
                'linked' => true,
                'top' => '15px',
                'left' => '15px',
                'right' => '15px',
                'bottom' => '15px',
            ])
        )
    ]);

    // 6. Border
    blocksy_output_border([
        'css' => $css,
        'tablet_css' => $tablet_css,
        'mobile_css' => $mobile_css,
        'selector' => '.ct-custom-layer',
        'value' => blocksy_get_theme_mod(
            'customLayerBorder',
            [
                'width' => 1,
                'style' => 'solid',
                'color' => [
                    'color' => 'rgba(0,0,0,0.1)',
                ],
            ]
        ),
        'responsive' => true,
    ]);

    // 7. Border Radius
    blocksy_output_spacing([
        'css' => $css,
        'tablet_css' => $tablet_css,
        'mobile_css' => $mobile_css,
        'selector' => '.ct-custom-layer',
        'property' => 'border-radius',
        'value' => blocksy_get_theme_mod(
            'customLayerBorderRadius',
            blocksy_spacing_value([
                'linked' => true,
                'top' => '0px',
                'left' => '0px',
                'right' => '0px',
                'bottom' => '0px',
            ])
        )
    ]);

    // 8. Custom CSS (manual)
    $custom_value = blocksy_get_theme_mod('my_custom_option', '10px');
    $css->put(
        '.ct-custom-layer',
        '--custom-variable: ' . $custom_value
    );

}, 10, 1);
```

**Load in functions.php:**

```php
require_once get_template_directory() . '/inc/custom/my-custom-layer-dynamic-css.php';
```

---

## Template 5: Live Preview Sync (JavaScript)

**Use Case**: Setup live preview for custom options

```javascript
/**
 * File: static/js/custom/my-custom-layer-sync.js
 */

import { typographyOption } from '../customizer/sync/variables/typography'
import { handleBackgroundOptionFor } from '../customizer/sync/variables/background'

wp.customize.bind('preview-ready', () => {

    // Typography sync
    wp.customize('customLayerTitleFont', (value) => {
        value.bind((to) => {
            // Handled automatically by typographyOption
        })
    })

    // Color sync
    wp.customize('customLayerTitleColor', (value) => {
        value.bind((to) => {
            // Handled automatically by color system
        })
    })

    // Custom sync for layer options
    wp.customize('woo_single_layout', (value) => {
        value.bind((to) => {
            const customLayer = to.find(layer => layer.id === 'my_custom_layer')

            if (!customLayer || !customLayer.enabled) {
                return
            }

            // Update title
            const titleEl = document.querySelector('.ct-custom-layer .custom-layer-title')
            if (titleEl && customLayer.custom_title) {
                titleEl.textContent = customLayer.custom_title
            }

            // Update content
            const contentEl = document.querySelector('.ct-custom-layer .custom-layer-content')
            if (contentEl && customLayer.custom_content) {
                contentEl.innerHTML = customLayer.custom_content
            }

            // Toggle icon
            const iconEl = document.querySelector('.ct-custom-layer .icon')
            if (iconEl) {
                iconEl.style.display = customLayer.show_icon === 'yes' ? 'inline' : 'none'
            }
        })
    })
})

// Export variable descriptors
export const getCustomLayerVariables = () => ({

    // Typography
    ...typographyOption({
        id: 'customLayerTitleFont',
        selector: '.ct-custom-layer .custom-layer-title',
    }),

    // Single color
    customLayerTitleColor: {
        selector: '.ct-custom-layer .custom-layer-title',
        variable: 'theme-heading-color',
        type: 'color:default',
    },

    // Multiple color states
    customLayerLinkColor: [
        {
            selector: '.ct-custom-layer a',
            variable: 'theme-link-initial-color',
            type: 'color:default',
        },
        {
            selector: '.ct-custom-layer a',
            variable: 'theme-link-hover-color',
            type: 'color:hover',
        },
    ],

    // Background
    customLayerBackground: {
        selector: '.ct-custom-layer',
        type: 'background',
    },

    // Spacing
    customLayerPadding: {
        selector: '.ct-custom-layer',
        type: 'spacing',
        variable: 'padding',
    },

    // Border
    customLayerBorder: {
        selector: '.ct-custom-layer',
        type: 'border',
    },

    // Border Radius
    customLayerBorderRadius: {
        selector: '.ct-custom-layer',
        type: 'spacing',
        variable: 'border-radius',
    },
})
```

**Enqueue in functions.php:**

```php
add_action('customize_preview_init', function() {
    wp_enqueue_script(
        'blocksy-custom-layer-sync',
        get_template_directory_uri() . '/static/js/custom/my-custom-layer-sync.js',
        ['customize-preview', 'ct-customizer'],
        '1.0.0',
        true
    );
});
```

---

## Template 6: Conditional Options

**Use Case**: Show/hide options based on other option values

```php
<?php
/**
 * Conditional options example
 */

$options = [

    // Parent option
    'enable_custom_feature' => [
        'label' => __('Enable Custom Feature', 'blocksy'),
        'type' => 'ct-switch',
        'value' => 'no',
        'sync' => ['id' => 'woo_single_layout_skip'],
    ],

    // Child options (shown only when parent is enabled)
    blocksy_rand_md5() => [
        'type' => 'ct-condition',
        'condition' => ['enable_custom_feature' => 'yes'],
        'options' => [

            'custom_feature_title' => [
                'label' => __('Feature Title', 'blocksy'),
                'type' => 'text',
                'value' => '',
            ],

            'custom_feature_style' => [
                'label' => __('Style', 'blocksy'),
                'type' => 'ct-radio',
                'value' => 'style1',
                'view' => 'text',
                'choices' => [
                    'style1' => __('Style 1', 'blocksy'),
                    'style2' => __('Style 2', 'blocksy'),
                    'style3' => __('Style 3', 'blocksy'),
                ],
            ],

        ],
    ],

    // Nested conditions
    blocksy_rand_md5() => [
        'type' => 'ct-condition',
        'condition' => [
            'enable_custom_feature' => 'yes',
            'custom_feature_style' => 'style2',
        ],
        'options' => [

            'style2_specific_option' => [
                'label' => __('Style 2 Option', 'blocksy'),
                'type' => 'text',
                'value' => '',
            ],

        ],
    ],

    // Condition with array-ids (for layers)
    blocksy_rand_md5() => [
        'type' => 'ct-condition',
        'condition' => ['woo_single_layout:array-ids:my_custom_layer:enabled' => '!no'],
        'computed_fields' => ['woo_single_layout'],
        'options' => [
            // Options shown only when layer is enabled
        ],
    ],
];
```

---

## Template 7: Tabs and Panels

**Use Case**: Organize options in tabs and panels

```php
<?php
/**
 * Tabs and panels example
 */

$options = [

    // Tab container
    blocksy_rand_md5() => [
        'type' => 'tab',
        'label' => __('General', 'blocksy'),
        'options' => [

            'general_option_1' => [
                'label' => __('Option 1', 'blocksy'),
                'type' => 'text',
                'value' => '',
            ],

        ],
    ],

    blocksy_rand_md5() => [
        'type' => 'tab',
        'label' => __('Design', 'blocksy'),
        'options' => [

            'design_option_1' => [
                'label' => __('Color', 'blocksy'),
                'type' => 'ct-color-picker',
                'value' => [
                    'default' => ['color' => '#000000'],
                ],
            ],

        ],
    ],

    // Panel with switch
    'my_panel' => [
        'label' => __('My Panel', 'blocksy'),
        'type' => 'ct-panel',
        'switch' => true,
        'value' => 'yes',
        'inner-options' => [

            'panel_option_1' => [
                'label' => __('Panel Option', 'blocksy'),
                'type' => 'text',
                'value' => '',
            ],

        ],
    ],
];
```

---

## Template 8: Complete Example - Product Badge Layer

**Use Case**: Real-world example - Custom product badge

```php
<?php
/**
 * File: inc/custom/product-badge-layer.php
 * Complete example: Product badge layer
 */

// 1. Define layer options
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['product_badge'] = [
        'label' => __('Product Badge', 'blocksy'),
        'options' => [

            'badge_text' => [
                'label' => __('Badge Text', 'blocksy'),
                'type' => 'text',
                'design' => 'block',
                'value' => __('New', 'blocksy'),
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],

            'badge_position' => [
                'label' => __('Position', 'blocksy'),
                'type' => 'ct-radio',
                'value' => 'top-left',
                'view' => 'text',
                'design' => 'block',
                'choices' => [
                    'top-left' => __('Top Left', 'blocksy'),
                    'top-right' => __('Top Right', 'blocksy'),
                    'bottom-left' => __('Bottom Left', 'blocksy'),
                    'bottom-right' => __('Bottom Right', 'blocksy'),
                ],
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],

            'show_on_sale_only' => [
                'label' => __('Show on Sale Products Only', 'blocksy'),
                'type' => 'ct-switch',
                'value' => 'no',
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],

            'spacing' => [
                'label' => __('Bottom Spacing', 'blocksy'),
                'type' => 'ct-slider',
                'min' => 0,
                'max' => 100,
                'value' => 15,
                'responsive' => true,
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],
        ],
    ];

    return $options;
});

// 2. Add to defaults
add_filter('blocksy_woo_single_options_layers:defaults', function($defaults) {
    $defaults[] = [
        'id' => 'product_badge',
        'enabled' => false,
    ];
    return $defaults;
});

// 3. Render
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    if ($layer['id'] !== 'product_badge') {
        return;
    }

    global $product;

    if (!$product) {
        return;
    }

    $badge_text = blocksy_akg('badge_text', $layer, 'New');
    $position = blocksy_akg('badge_position', $layer, 'top-left');
    $show_on_sale_only = blocksy_akg('show_on_sale_only', $layer, 'no');

    // Check if should show
    if ($show_on_sale_only === 'yes' && !$product->is_on_sale()) {
        return;
    }

    ?>
    <div class="ct-product-badge" data-position="<?php echo esc_attr($position); ?>">
        <span class="badge-text"><?php echo esc_html($badge_text); ?></span>
    </div>
    <?php
}, 10, 1);
```

**Design Options (add to single-product-elements.php):**

```php
blocksy_rand_md5() => [
    'type' => 'ct-condition',
    'condition' => ['woo_single_layout:array-ids:product_badge:enabled' => '!no'],
    'computed_fields' => ['woo_single_layout'],
    'options' => [

        blocksy_rand_md5() => [
            'type' => 'ct-title',
            'label' => __('Product Badge Design', 'blocksy'),
        ],

        'productBadgeFont' => [
            'type' => 'ct-typography',
            'label' => __('Font', 'blocksy'),
            'value' => blocksy_typography_default_values([
                'size' => '12px',
                'variation' => 'n7',
                'text-transform' => 'uppercase',
            ]),
            'setting' => ['transport' => 'postMessage'],
        ],

        'productBadgeTextColor' => [
            'label' => __('Text Color', 'blocksy'),
            'type'  => 'ct-color-picker',
            'design' => 'inline',
            'setting' => ['transport' => 'postMessage'],
            'value' => [
                'default' => ['color' => '#ffffff'],
            ],
        ],

        'productBadgeBackgroundColor' => [
            'label' => __('Background Color', 'blocksy'),
            'type'  => 'ct-color-picker',
            'design' => 'inline',
            'setting' => ['transport' => 'postMessage'],
            'value' => [
                'default' => ['color' => '#e74c3c'],
            ],
        ],

        'productBadgePadding' => [
            'label' => __('Padding', 'blocksy'),
            'type' => 'ct-spacing',
            'value' => blocksy_spacing_value([
                'top' => '5px',
                'left' => '10px',
                'right' => '10px',
                'bottom' => '5px',
            ]),
            'setting' => ['transport' => 'postMessage'],
        ],

        'productBadgeBorderRadius' => [
            'label' => __('Border Radius', 'blocksy'),
            'type' => 'ct-slider',
            'min' => 0,
            'max' => 50,
            'value' => 3,
            'setting' => ['transport' => 'postMessage'],
        ],

    ],
],
```

**Dynamic CSS:**

```php
add_action('blocksy:global-dynamic-css:enqueue', function($args) {
    $css = $args['css'];
    $tablet_css = $args['tablet_css'];
    $mobile_css = $args['mobile_css'];

    $layout = blocksy_get_theme_mod('woo_single_layout', []);
    $badge_layer = array_filter($layout, function($layer) {
        return $layer['id'] === 'product_badge' && $layer['enabled'];
    });

    if (empty($badge_layer)) {
        return;
    }

    // Font
    blocksy_output_font_css([
        'font_value' => blocksy_get_theme_mod('productBadgeFont'),
        'css' => $css,
        'selector' => '.ct-product-badge .badge-text'
    ]);

    // Text Color
    $text_color = blocksy_get_theme_mod('productBadgeTextColor', ['default' => ['color' => '#ffffff']]);
    $css->put(
        '.ct-product-badge .badge-text',
        '--badge-text-color: ' . $text_color['default']['color']
    );

    // Background
    $bg_color = blocksy_get_theme_mod('productBadgeBackgroundColor', ['default' => ['color' => '#e74c3c']]);
    $css->put(
        '.ct-product-badge',
        '--badge-bg-color: ' . $bg_color['default']['color']
    );

    // Padding
    blocksy_output_spacing([
        'css' => $css,
        'selector' => '.ct-product-badge',
        'property' => 'padding',
        'value' => blocksy_get_theme_mod('productBadgePadding')
    ]);

    // Border Radius
    $radius = blocksy_get_theme_mod('productBadgeBorderRadius', 3);
    $css->put(
        '.ct-product-badge',
        'border-radius: ' . $radius . 'px'
    );

}, 10, 1);
```

**CSS (add to style.css or custom CSS file):**

```css
.ct-product-badge {
    display: inline-block;
    background-color: var(--badge-bg-color, #e74c3c);
    position: relative;
    z-index: 10;
}

.ct-product-badge .badge-text {
    color: var(--badge-text-color, #ffffff);
    display: block;
}

.ct-product-badge[data-position="top-left"] {
    /* Position styles */
}

.ct-product-badge[data-position="top-right"] {
    /* Position styles */
}
```

---

## Usage Guide

### How to Use These Templates

1. **Copy the template** that matches your needs
2. **Replace placeholder names**:
   - `my_custom_layer` → your layer name
   - `customLayer` → your option prefix
   - `.ct-custom-layer` → your CSS class
3. **Adjust values** as needed (colors, sizes, defaults)
4. **Load files** in `functions.php`
5. **Test** in customizer

### File Organization

```
your-child-theme/
├── functions.php
├── inc/
│   └── custom/
│       ├── my-layer-options.php
│       └── my-layer-dynamic-css.php
└── static/
    └── js/
        └── custom/
            └── my-layer-sync.js
```

### Loading Order

```php
// In functions.php

// 1. Load layer options
require_once get_stylesheet_directory() . '/inc/custom/my-layer-options.php';

// 2. Load dynamic CSS
require_once get_stylesheet_directory() . '/inc/custom/my-layer-dynamic-css.php';

// 3. Enqueue sync JS
add_action('customize_preview_init', function() {
    wp_enqueue_script(
        'my-layer-sync',
        get_stylesheet_directory_uri() . '/static/js/custom/my-layer-sync.js',
        ['customize-preview', 'ct-customizer'],
        '1.0.0',
        true
    );
});
```

---

**Templates Version**: 1.0.0
**Last Updated**: 2025-11-26

