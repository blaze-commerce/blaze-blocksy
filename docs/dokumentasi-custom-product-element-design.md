# Technical Documentation: Custom Product Element with Design Fields

## Overview

This documentation explains how to create a custom product element that can add design fields and implement appropriate styling on the element display.

## Blocksy System Structure

### 1. Custom Product Element Registration

#### A. Adding Layer to Default Layout

```php
// Add to functions.php or plugin
add_filter('blocksy_woo_single_options_layers:defaults', function($layers) {
    $layers[] = [
        'id' => 'custom_element',
        'enabled' => true,
    ];
    return $layers;
});
```

#### B. Options Definition for Custom Element

```php
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['custom_element'] = [
        'label' => __('Custom Element', 'textdomain'),
        'options' => [
            'spacing' => [
                'label' => __('Bottom Spacing', 'textdomain'),
                'type' => 'ct-slider',
                'min' => 0,
                'max' => 100,
                'value' => 10,
                'responsive' => true,
                'sync' => [
                    'id' => 'woo_single_layout_skip'
                ],
            ],
            'custom_text' => [
                'label' => __('Custom Text', 'textdomain'),
                'type' => 'text',
                'value' => 'Default text',
                'sync' => [
                    'id' => 'woo_single_layout_skip'
                ],
            ],
        ],
    ];
    return $options;
});
```

### 2. Adding Design Fields

#### A. Adding Fields to Design Tab

```php
add_filter('blocksy:options:single_product:elements:design_tab:end', function($options) {
    $options[] = [
        blocksy_rand_md5() => [
            'type' => 'ct-condition',
            'condition' => ['woo_single_layout:array-ids:custom_element:enabled' => '!no'],
            'computed_fields' => ['woo_single_layout'],
            'options' => [
                'customElementFont' => [
                    'type' => 'ct-typography',
                    'label' => __('Custom Element Font', 'textdomain'),
                    'value' => blocksy_typography_default_values([
                        'size' => '16px',
                        'variation' => 'n4',
                    ]),
                    'setting' => ['transport' => 'postMessage'],
                    'divider' => 'top:full',
                ],

                'customElementColor' => [
                    'label' => __('Custom Element Color', 'textdomain'),
                    'type' => 'ct-color-picker',
                    'design' => 'inline',
                    'setting' => ['transport' => 'postMessage'],
                    'value' => [
                        'default' => [
                            'color' => '#333333',
                        ],
                        'hover' => [
                            'color' => '#666666',
                        ],
                    ],
                    'pickers' => [
                        [
                            'title' => __('Initial', 'textdomain'),
                            'id' => 'default',
                        ],
                        [
                            'title' => __('Hover', 'textdomain'),
                            'id' => 'hover',
                        ],
                    ],
                ],

                'customElementBackground' => [
                    'label' => __('Custom Element Background', 'textdomain'),
                    'type' => 'ct-background',
                    'design' => 'inline',
                    'setting' => ['transport' => 'postMessage'],
                    'value' => blocksy_background_default_value([
                        'backgroundColor' => [
                            'default' => [
                                'color' => 'transparent',
                            ],
                        ],
                    ]),
                    'divider' => 'top',
                ],

                'customElementBorder' => [
                    'label' => __('Custom Element Border', 'textdomain'),
                    'type' => 'ct-border',
                    'design' => 'inline',
                    'setting' => ['transport' => 'postMessage'],
                    'value' => [
                        'width' => 0,
                        'style' => 'solid',
                        'color' => [
                            'color' => 'var(--theme-border-color)',
                        ],
                    ],
                    'divider' => 'top',
                ],

                'customElementBorderRadius' => [
                    'label' => __('Custom Element Border Radius', 'textdomain'),
                    'type' => 'ct-spacing',
                    'setting' => ['transport' => 'postMessage'],
                    'value' => blocksy_spacing_value(),
                    'min' => 0,
                    'responsive' => true,
                    'divider' => 'top',
                ],

                'customElementPadding' => [
                    'label' => __('Custom Element Padding', 'textdomain'),
                    'type' => 'ct-spacing',
                    'setting' => ['transport' => 'postMessage'],
                    'value' => blocksy_spacing_value([
                        'top' => '10px',
                        'bottom' => '10px',
                        'left' => '15px',
                        'right' => '15px',
                    ]),
                    'min' => 0,
                    'responsive' => true,
                    'divider' => 'top',
                ],
            ],
        ],
    ];

    return $options;
});
```

### 3. Rendering Custom Element

#### A. Hook for Element Rendering

```php
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    if ($layer['id'] !== 'custom_element') {
        return;
    }

    $custom_text = blocksy_akg('custom_text', $layer, 'Default text');

    echo blocksy_html_tag(
        'div',
        [
            'class' => 'ct-custom-element',
            'data-id' => blocksy_akg('__id', $layer, 'default')
        ],
        $custom_text
    );
});
```

### 4. Dynamic CSS Implementation

#### A. Creating Dynamic CSS File

Create file: `inc/dynamic-styles/global/woocommerce/custom-element.php`

```php
<?php

// Custom Element Typography
blocksy_output_font_css([
    'font_value' => blocksy_get_theme_mod(
        'customElementFont',
        blocksy_typography_default_values([
            'size' => '16px',
            'variation' => 'n4',
        ])
    ),
    'css' => $css,
    'tablet_css' => $tablet_css,
    'mobile_css' => $mobile_css,
    'selector' => '.ct-custom-element'
]);

// Custom Element Colors
blocksy_output_colors([
    'value' => blocksy_get_theme_mod('customElementColor'),
    'default' => [
        'default' => ['color' => '#333333'],
        'hover' => ['color' => '#666666'],
    ],
    'css' => $css,
    'tablet_css' => $tablet_css,
    'mobile_css' => $mobile_css,
    'variables' => [
        'default' => [
            'selector' => '.ct-custom-element',
            'variable' => 'theme-text-color'
        ],
        'hover' => [
            'selector' => '.ct-custom-element',
            'variable' => 'theme-link-hover-color'
        ],
    ],
    'responsive' => true
]);

// Custom Element Background
blocksy_output_background_css([
    'selector' => '.ct-custom-element',
    'css' => $css,
    'tablet_css' => $tablet_css,
    'mobile_css' => $mobile_css,
    'value' => blocksy_get_theme_mod(
        'customElementBackground',
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

// Custom Element Border
blocksy_output_border([
    'css' => $css,
    'tablet_css' => $tablet_css,
    'mobile_css' => $mobile_css,
    'selector' => '.ct-custom-element',
    'variableName' => 'theme-border',
    'value' => blocksy_get_theme_mod('customElementBorder'),
    'default' => [
        'width' => 0,
        'style' => 'solid',
        'color' => [
            'color' => 'var(--theme-border-color)',
        ],
    ],
    'responsive' => true
]);

// Custom Element Border Radius
blocksy_output_spacing([
    'css' => $css,
    'tablet_css' => $tablet_css,
    'mobile_css' => $mobile_css,
    'selector' => '.ct-custom-element',
    'property' => 'border-radius',
    'value' => blocksy_get_theme_mod(
        'customElementBorderRadius',
        blocksy_spacing_value()
    )
]);

// Custom Element Padding
blocksy_output_spacing([
    'css' => $css,
    'tablet_css' => $tablet_css,
    'mobile_css' => $mobile_css,
    'selector' => '.ct-custom-element',
    'property' => 'padding',
    'value' => blocksy_get_theme_mod(
        'customElementPadding',
        blocksy_spacing_value([
            'top' => '10px',
            'bottom' => '10px',
            'left' => '15px',
            'right' => '15px',
        ])
    )
]);
```

#### B. Include Dynamic CSS File

```php
// Add to functions.php or appropriate hook
add_action('blocksy:global-dynamic-css:enqueue', function($args) {
    if ($args['context'] !== 'inline') {
        return;
    }

    // Include custom element styles
    include get_template_directory() . '/inc/dynamic-styles/global/woocommerce/custom-element.php';
}, 10, 1);
```

### 5. Complete Implementation with Spacing

#### A. Adding Spacing to Single Product Layers

```php
// Add spacing for custom element
add_filter('blocksy:woocommerce:single-product:layers:spacing', function($selectors_map) {
    $selectors_map['custom_element'] = '.ct-custom-element';
    return $selectors_map;
});
```

#### B. Hook for Dynamic CSS on Single Product

```php
add_action('blocksy:global-dynamic-css:enqueue:singular', function($args) {
    if ($args['context'] !== 'inline') {
        return;
    }

    // Ensure this is a single product page
    if (!is_product()) {
        return;
    }

    $css = $args['css'];
    $tablet_css = $args['tablet_css'];
    $mobile_css = $args['mobile_css'];

    // Include custom element styles
    include get_template_directory() . '/inc/dynamic-styles/global/woocommerce/custom-element.php';
}, 10, 1);
```

### 6. Troubleshooting

#### A. Fields Not Appearing in Design Tab

1. Ensure condition `woo_single_layout:array-ids:custom_element:enabled` matches the element ID
2. Check if `computed_fields` is correct
3. Ensure element is registered in layout defaults

#### B. Styling Not Applied

1. Check if dynamic CSS file is included correctly
2. Ensure CSS selector matches the class used in render
3. Check if hook `blocksy:global-dynamic-css:enqueue` is called

#### C. Element Not Appearing on Frontend

1. Ensure hook `blocksy:woocommerce:product:custom:layer` is registered
2. Check condition `$layer['id']` in render function
3. Ensure element is enabled in layout

### 7. Best Practices

#### A. Naming Convention

- Use consistent prefix for all option keys
- Use underscore to separate words
- Use descriptive names

#### B. Performance

- Use `setting => ['transport' => 'postMessage']` for live preview
- Implement conditional loading for CSS
- Use caching if needed

#### C. Compatibility

- Always check if function exists before using
- Use proper escaping for output
- Implement fallback values

### 8. Complete Implementation Example

#### A. Plugin/Theme Functions File

```php
<?php
// Custom Product Element Implementation

class Custom_Product_Element {

    public function __construct() {
        add_filter('blocksy_woo_single_options_layers:defaults', [$this, 'add_to_defaults']);
        add_filter('blocksy_woo_single_options_layers:extra', [$this, 'add_options']);
        add_filter('blocksy:options:single_product:elements:design_tab:end', [$this, 'add_design_options']);
        add_action('blocksy:woocommerce:product:custom:layer', [$this, 'render_element']);
        add_action('blocksy:global-dynamic-css:enqueue:singular', [$this, 'add_dynamic_css'], 10, 1);
    }

    public function add_to_defaults($layers) {
        $layers[] = [
            'id' => 'custom_element',
            'enabled' => true,
        ];
        return $layers;
    }

    public function add_options($options) {
        $options['custom_element'] = [
            'label' => __('Custom Element', 'textdomain'),
            'options' => [
                'spacing' => [
                    'label' => __('Bottom Spacing', 'textdomain'),
                    'type' => 'ct-slider',
                    'min' => 0,
                    'max' => 100,
                    'value' => 10,
                    'responsive' => true,
                    'sync' => ['id' => 'woo_single_layout_skip'],
                ],
                'custom_text' => [
                    'label' => __('Custom Text', 'textdomain'),
                    'type' => 'text',
                    'value' => 'Default text',
                    'sync' => ['id' => 'woo_single_layout_skip'],
                ],
            ],
        ];
        return $options;
    }

    public function add_design_options($options) {
        $options[] = [
            blocksy_rand_md5() => [
                'type' => 'ct-condition',
                'condition' => ['woo_single_layout:array-ids:custom_element:enabled' => '!no'],
                'computed_fields' => ['woo_single_layout'],
                'options' => [
                    'customElementFont' => [
                        'type' => 'ct-typography',
                        'label' => __('Custom Element Font', 'textdomain'),
                        'value' => blocksy_typography_default_values([
                            'size' => '16px',
                            'variation' => 'n4',
                        ]),
                        'setting' => ['transport' => 'postMessage'],
                        'divider' => 'top:full',
                    ],
                    'customElementColor' => [
                        'label' => __('Custom Element Color', 'textdomain'),
                        'type' => 'ct-color-picker',
                        'design' => 'inline',
                        'setting' => ['transport' => 'postMessage'],
                        'value' => [
                            'default' => ['color' => '#333333'],
                            'hover' => ['color' => '#666666'],
                        ],
                        'pickers' => [
                            ['title' => __('Initial', 'textdomain'), 'id' => 'default'],
                            ['title' => __('Hover', 'textdomain'), 'id' => 'hover'],
                        ],
                    ],
                ],
            ],
        ];
        return $options;
    }

    public function render_element($layer) {
        if ($layer['id'] !== 'custom_element') {
            return;
        }

        $custom_text = blocksy_akg('custom_text', $layer, 'Default text');

        echo blocksy_html_tag(
            'div',
            [
                'class' => 'ct-custom-element',
                'data-id' => blocksy_akg('__id', $layer, 'default')
            ],
            esc_html($custom_text)
        );
    }

    public function add_dynamic_css($args) {
        if ($args['context'] !== 'inline' || !is_product()) {
            return;
        }

        $css = $args['css'];
        $tablet_css = $args['tablet_css'];
        $mobile_css = $args['mobile_css'];

        // Typography
        blocksy_output_font_css([
            'font_value' => blocksy_get_theme_mod(
                'customElementFont',
                blocksy_typography_default_values(['size' => '16px', 'variation' => 'n4'])
            ),
            'css' => $css,
            'tablet_css' => $tablet_css,
            'mobile_css' => $mobile_css,
            'selector' => '.ct-custom-element'
        ]);

        // Colors
        blocksy_output_colors([
            'value' => blocksy_get_theme_mod('customElementColor'),
            'default' => [
                'default' => ['color' => '#333333'],
                'hover' => ['color' => '#666666'],
            ],
            'css' => $css,
            'tablet_css' => $tablet_css,
            'mobile_css' => $mobile_css,
            'variables' => [
                'default' => [
                    'selector' => '.ct-custom-element',
                    'variable' => 'theme-text-color'
                ],
                'hover' => [
                    'selector' => '.ct-custom-element',
                    'variable' => 'theme-link-hover-color'
                ],
            ],
            'responsive' => true
        ]);
    }
}

// Initialize
new Custom_Product_Element();
```

### 9. Conclusion

To create a custom product element with functional design fields:

1. **Element Registration**: Add to defaults and extra options
2. **Design Options**: Use filter `blocksy:options:single_product:elements:design_tab:end`
3. **Rendering**: Implement hook `blocksy:woocommerce:product:custom:layer`
4. **Dynamic CSS**: Use hook `blocksy:global-dynamic-css:enqueue:singular`
5. **Testing**: Ensure all conditions and selectors are correct

By following this structure, the added design fields will be able to modify the element appearance according to the created configuration.
