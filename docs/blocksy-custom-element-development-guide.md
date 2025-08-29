# Blocksy Custom Element Development Guide

## Overview

This comprehensive guide explains how to develop custom product elements for Blocksy Companion Pro. Custom elements integrate seamlessly with Blocksy's drag-and-drop Product Elements system and appear in the WordPress Customizer alongside built-in elements like Breadcrumbs, Title, Star Rating, etc.

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Element Structure](#element-structure)
3. [Field Types Reference](#field-types-reference)
4. [Live Preview Implementation](#live-preview-implementation)
5. [Complete Example](#complete-example)
6. [Best Practices](#best-practices)
7. [Troubleshooting](#troubleshooting)

## System Architecture

### Hook System

Blocksy uses a filter-based system for registering custom elements:

**For Single Product Pages:**

- `blocksy_woo_single_options_layers:defaults` - Add element to default layout
- `blocksy_woo_single_options_layers:extra` - Add customizer options
- `blocksy:woocommerce:product:custom:layer` - Render element

**For Product Cards (Archive Pages):**

- `blocksy_woo_card_options_layers:defaults` - Add to card layout
- `blocksy_woo_card_options_layers:extra` - Add card options
- `blocksy:woocommerce:product-card:custom:layer` - Render on cards

**For Split Layout (Right Column):**

- `blocksy_woo_single_right_options_layers:defaults` - Add to right column default layout
- `blocksy_woo_single_right_options_layers:extra` - Add right column customizer options

### Element Registration Flow

1. Element class hooks into Blocksy filters during WordPress initialization
2. `register_layer_defaults()` adds element to available options (disabled by default)
3. `register_layer_options()` defines customizer fields and their configuration
4. `render_layer()` outputs HTML when element is enabled and conditions are met
5. Live preview updates automatically when `'sync' => 'live'` is configured

## Element Structure

### Basic Class Template

```php
<?php
namespace YourNamespace;

class CustomElementLayer {

    public function __construct() {
        // Register for single product pages
        add_filter('blocksy_woo_single_options_layers:defaults', [$this, 'register_layer_defaults']);
        add_filter('blocksy_woo_single_options_layers:extra', [$this, 'register_layer_options']);
        add_action('blocksy:woocommerce:product:custom:layer', [$this, 'render_layer']);

        // Optional: Register for product cards
        add_filter('blocksy_woo_card_options_layers:defaults', [$this, 'register_layer_defaults']);
        add_filter('blocksy_woo_card_options_layers:extra', [$this, 'register_layer_options']);
        add_action('blocksy:woocommerce:product-card:custom:layer', [$this, 'render_layer']);

        // Enqueue styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    /**
     * Add element to default layout (disabled by default)
     */
    public function register_layer_defaults($opt) {
        return array_merge($opt, [
            [
                'id' => 'your_element_id',
                'enabled' => false,
            ]
        ]);
    }

    /**
     * Define customizer options for the element
     */
    public function register_layer_options($opt) {
        return array_merge($opt, [
            'your_element_id' => [
                'label' => __('Your Element Name', 'textdomain'),
                'options' => [
                    // Field definitions go here
                ]
            ]
        ]);
    }

    /**
     * Render element HTML
     */
    public function render_layer($layer) {
        if ($layer['id'] !== 'your_element_id') {
            return;
        }

        global $product;

        if (!$product) {
            return;
        }

        // Get field values
        $field_value = blocksy_akg('field_name', $layer, 'default_value');

        // Output HTML
        echo blocksy_html_tag(
            'div',
            [
                'class' => 'ct-your-element',
                'data-element' => 'your_element_id',
            ],
            $this->get_element_content($layer, $product)
        );
    }

    /**
     * Generate element content
     */
    private function get_element_content($layer, $product) {
        // Implementation here
        return '';
    }

    /**
     * Enqueue element styles
     */
    public function enqueue_styles() {
        if (!is_product() && !is_shop() && !is_product_category()) {
            return;
        }

        wp_add_inline_style('ct-main-styles', $this->get_element_css());
    }

    /**
     * Get element CSS
     */
    private function get_element_css() {
        return '
        .ct-your-element {
            /* Base styles */
        }
        ';
    }
}

// Initialize
new CustomElementLayer();
```

## Field Types Reference

### Text Input

```php
'field_name' => [
    'label' => __('Field Label', 'textdomain'),
    'type' => 'text',
    'value' => 'default value',
    'design' => 'block', // 'block', 'inline'
    'disableRevertButton' => true,
    'sync' => 'live', // Enable live preview
],
```

### Switch/Toggle

```php
'enable_feature' => [
    'label' => __('Enable Feature', 'textdomain'),
    'type' => 'ct-switch',
    'value' => 'yes', // 'yes' or 'no'
    'sync' => 'live',
],
```

### Select Dropdown

```php
'display_format' => [
    'label' => __('Display Format', 'textdomain'),
    'type' => 'ct-select',
    'value' => 'format1',
    'design' => 'inline',
    'choices' => [
        'format1' => __('Format 1', 'textdomain'),
        'format2' => __('Format 2', 'textdomain'),
    ],
    'sync' => 'live',
],
```

### Radio Buttons

```php
'layout_type' => [
    'label' => __('Layout Type', 'textdomain'),
    'type' => 'ct-radio',
    'value' => 'horizontal',
    'view' => 'text',
    'design' => 'inline',
    'choices' => [
        'horizontal' => __('Horizontal', 'textdomain'),
        'vertical' => __('Vertical', 'textdomain'),
    ],
    'sync' => 'live',
],
```

### Slider

```php
'element_size' => [
    'label' => __('Size', 'textdomain'),
    'type' => 'ct-slider',
    'value' => 20,
    'min' => 0,
    'max' => 100,
    'defaultUnit' => 'px',
    'responsive' => true,
    'sync' => 'live',
],
```

### Color Picker

```php
'text_color' => [
    'label' => __('Text Color', 'textdomain'),
    'type' => 'ct-color-picker',
    'design' => 'inline',
    'sync' => 'live',
    'value' => [
        'default' => [
            'color' => '#333333',
        ],
        'hover' => [
            'color' => '#000000',
        ],
    ],
    'pickers' => [
        [
            'title' => __('Initial', 'textdomain'),
            'id' => 'default',
            'inherit' => 'var(--theme-text-color)',
        ],
        [
            'title' => __('Hover', 'textdomain'),
            'id' => 'hover',
            'inherit' => 'var(--theme-link-hover-color)',
        ],
    ],
],
```

### Typography

```php
'element_typography' => [
    'type' => 'ct-typography',
    'label' => __('Typography', 'textdomain'),
    'value' => blocksy_typography_default_values([
        'size' => '16px',
        'variation' => 'n4',
        'text-transform' => 'none',
    ]),
    'design' => 'block',
    'sync' => 'live',
],
```

### Spacing

```php
'element_spacing' => [
    'label' => __('Spacing', 'textdomain'),
    'type' => 'ct-spacing',
    'value' => blocksy_spacing_value([
        'top' => '10px',
        'right' => '15px',
        'bottom' => '10px',
        'left' => '15px',
    ]),
    'responsive' => true,
    'sync' => 'live',
],
```

### Border

```php
'element_border' => [
    'label' => __('Border', 'textdomain'),
    'type' => 'ct-border',
    'design' => 'block',
    'sync' => 'live',
    'responsive' => true,
    'value' => [
        'width' => 1,
        'style' => 'solid',
        'color' => [
            'color' => '#cccccc',
        ],
    ],
],
```

### Background

```php
'element_background' => [
    'label' => __('Background', 'textdomain'),
    'type' => 'ct-background',
    'design' => 'block:right',
    'responsive' => true,
    'sync' => 'live',
    'value' => blocksy_background_default_value([
        'backgroundColor' => [
            'default' => [
                'color' => '#ffffff',
            ],
        ],
    ]),
],
```

### Conditional Fields

```php
blocksy_rand_md5() => [
    'type' => 'ct-condition',
    'condition' => ['parent_field' => 'specific_value'],
    'options' => [
        'conditional_field' => [
            'label' => __('Conditional Field', 'textdomain'),
            'type' => 'text',
            'value' => '',
            'sync' => 'live',
        ],
    ],
],
```

### Tabs Organization

```php
'options' => [
    blocksy_rand_md5() => [
        'title' => __('General', 'textdomain'),
        'type' => 'tab',
        'options' => [
            // General fields
        ],
    ],

    blocksy_rand_md5() => [
        'title' => __('Design', 'textdomain'),
        'type' => 'tab',
        'options' => [
            // Design fields
        ],
    ],
]
```

## Live Preview Implementation

### Essential Requirements for Live Preview

1. **Use `'sync' => 'live'` in field definitions** - This enables real-time preview updates
2. **Add proper CSS classes and data attributes** - For JavaScript targeting and styling
3. **Use Blocksy's CSS variable system** - For dynamic style injection
4. **Implement responsive controls** - For mobile/tablet/desktop compatibility
5. **Follow Blocksy's naming conventions** - For seamless integration

### CSS Variables Integration

```php
// In your render method
$element_id = 'your_element_id';
$css_class = "ct-{$element_id}";

echo blocksy_html_tag(
    'div',
    [
        'class' => $css_class,
        'data-element' => $element_id,
        // Add unique identifier for CSS targeting
        'data-id' => blocksy_rand_md5(),
    ],
    $content
);
```

### Dynamic CSS Generation

```php
public function get_dynamic_css($layer, $device = 'desktop') {
    $element_id = $layer['id'];
    $css = '';

    // Get field values
    $text_color = blocksy_akg('text_color', $layer, []);
    $typography = blocksy_akg('element_typography', $layer, []);
    $spacing = blocksy_akg('element_spacing', $layer, []);

    // Generate CSS
    if (!empty($text_color['default']['color'])) {
        $css .= "--element-text-color: {$text_color['default']['color']};";
    }

    if (!empty($text_color['hover']['color'])) {
        $css .= "--element-text-hover-color: {$text_color['hover']['color']};";
    }

    // Typography CSS
    if (!empty($typography)) {
        $css .= blocksy_get_typography_css_variables($typography, 'element-font');
    }

    // Spacing CSS
    if (!empty($spacing)) {
        $css .= blocksy_get_spacing_css_variables($spacing, 'element-spacing');
    }

    return ".ct-{$element_id} { {$css} }";
}
```

### Responsive CSS

```php
public function enqueue_dynamic_styles() {
    if (!is_product() && !is_shop()) {
        return;
    }

    // Get current layer settings
    $layout = get_theme_mod('woo_single_layout', []);

    foreach ($layout as $layer) {
        if ($layer['id'] !== 'your_element_id' || !$layer['enabled']) {
            continue;
        }

        // Desktop styles
        $desktop_css = $this->get_dynamic_css($layer, 'desktop');

        // Tablet styles
        $tablet_css = $this->get_dynamic_css($layer, 'tablet');

        // Mobile styles
        $mobile_css = $this->get_dynamic_css($layer, 'mobile');

        $responsive_css = "
        {$desktop_css}

        @media (max-width: 999px) {
            {$tablet_css}
        }

        @media (max-width: 689px) {
            {$mobile_css}
        }
        ";

        wp_add_inline_style('ct-main-styles', $responsive_css);
    }
}
```

### JavaScript for Enhanced Live Preview

```php
public function enqueue_customizer_scripts() {
    if (!is_customize_preview()) {
        return;
    }

    wp_enqueue_script(
        'your-element-customizer',
        'path/to/your-customizer.js',
        ['customize-preview'],
        '1.0.0',
        true
    );
}
```

```javascript
// your-customizer.js
wp.customize("woo_single_layout", function (value) {
  value.bind(function (newval) {
    // Handle layout changes
    updateElementPreview(newval);
  });
});

function updateElementPreview(layout) {
  layout.forEach(function (layer) {
    if (layer.id === "your_element_id" && layer.enabled) {
      // Update element appearance
      updateElementStyles(layer);
    }
  });
}
```

## Complete Example

Here's a complete working example of a Product Information element:

```php
<?php
namespace YourPlugin;

class ProductInfoElement {

    public function __construct() {
        add_filter('blocksy_woo_single_options_layers:defaults', [$this, 'register_defaults']);
        add_filter('blocksy_woo_single_options_layers:extra', [$this, 'register_options']);
        add_action('blocksy:woocommerce:product:custom:layer', [$this, 'render_layer']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function register_defaults($opt) {
        return array_merge($opt, [
            [
                'id' => 'product_info',
                'enabled' => false,
            ]
        ]);
    }

    public function register_options($opt) {
        return array_merge($opt, [
            'product_info' => [
                'label' => __('Product Information', 'textdomain'),
                'options' => [

                    blocksy_rand_md5() => [
                        'title' => __('General', 'textdomain'),
                        'type' => 'tab',
                        'options' => [

                            'info_title' => [
                                'label' => __('Section Title', 'textdomain'),
                                'type' => 'text',
                                'value' => __('Product Information', 'textdomain'),
                                'design' => 'block',
                                'sync' => 'live',
                            ],

                            'show_sku' => [
                                'label' => __('Show SKU', 'textdomain'),
                                'type' => 'ct-switch',
                                'value' => 'yes',
                                'sync' => 'live',
                            ],

                            'show_categories' => [
                                'label' => __('Show Categories', 'textdomain'),
                                'type' => 'ct-switch',
                                'value' => 'yes',
                                'sync' => 'live',
                            ],

                            'display_format' => [
                                'label' => __('Display Format', 'textdomain'),
                                'type' => 'ct-select',
                                'value' => 'list',
                                'design' => 'inline',
                                'choices' => [
                                    'list' => __('List', 'textdomain'),
                                    'table' => __('Table', 'textdomain'),
                                    'inline' => __('Inline', 'textdomain'),
                                ],
                                'sync' => 'live',
                            ],

                        ],
                    ],

                    blocksy_rand_md5() => [
                        'title' => __('Design', 'textdomain'),
                        'type' => 'tab',
                        'options' => [

                            'info_typography' => [
                                'type' => 'ct-typography',
                                'label' => __('Typography', 'textdomain'),
                                'value' => blocksy_typography_default_values([]),
                                'sync' => 'live',
                            ],

                            'info_color' => [
                                'label' => __('Text Color', 'textdomain'),
                                'type' => 'ct-color-picker',
                                'design' => 'inline',
                                'sync' => 'live',
                                'value' => [
                                    'default' => [
                                        'color' => 'var(--theme-text-color)',
                                    ],
                                ],
                                'pickers' => [
                                    [
                                        'title' => __('Initial', 'textdomain'),
                                        'id' => 'default',
                                        'inherit' => 'var(--theme-text-color)',
                                    ],
                                ],
                            ],

                            'info_spacing' => [
                                'label' => __('Bottom Spacing', 'textdomain'),
                                'type' => 'ct-slider',
                                'value' => 20,
                                'min' => 0,
                                'max' => 100,
                                'defaultUnit' => 'px',
                                'responsive' => true,
                                'sync' => 'live',
                            ],

                        ],
                    ],

                ]
            ]
        ]);
    }

    public function render_layer($layer) {
        if ($layer['id'] !== 'product_info') {
            return;
        }

        global $product;

        if (!$product) {
            return;
        }

        $title = blocksy_akg('info_title', $layer, __('Product Information', 'textdomain'));
        $show_sku = blocksy_akg('show_sku', $layer, 'yes') === 'yes';
        $show_categories = blocksy_akg('show_categories', $layer, 'yes') === 'yes';
        $format = blocksy_akg('display_format', $layer, 'list');

        $content = '';

        // Title
        if (!empty($title) || is_customize_preview()) {
            $content .= blocksy_html_tag(
                'h4',
                ['class' => 'ct-product-info-title'],
                $title
            );
        }

        // Information content
        $info_items = [];

        if ($show_sku && $product->get_sku()) {
            $info_items[] = [
                'label' => __('SKU:', 'textdomain'),
                'value' => $product->get_sku(),
            ];
        }

        if ($show_categories) {
            $categories = get_the_terms($product->get_id(), 'product_cat');
            if ($categories && !is_wp_error($categories)) {
                $cat_names = array_map(function($cat) {
                    return $cat->name;
                }, $categories);

                $info_items[] = [
                    'label' => __('Categories:', 'textdomain'),
                    'value' => implode(', ', $cat_names),
                ];
            }
        }

        if (!empty($info_items)) {
            $content .= $this->render_info_items($info_items, $format);
        }

        if (!empty($content)) {
            echo blocksy_html_tag(
                'div',
                [
                    'class' => 'ct-product-info ct-format-' . $format,
                    'data-element' => 'product_info',
                ],
                $content
            );
        }
    }

    private function render_info_items($items, $format) {
        switch ($format) {
            case 'table':
                $rows = '';
                foreach ($items as $item) {
                    $rows .= "<tr><td>{$item['label']}</td><td>{$item['value']}</td></tr>";
                }
                return "<table class='ct-info-table'><tbody>{$rows}</tbody></table>";

            case 'inline':
                $inline_items = [];
                foreach ($items as $item) {
                    $inline_items[] = "<span class='ct-info-item'>{$item['label']} {$item['value']}</span>";
                }
                return "<div class='ct-info-inline'>" . implode(' | ', $inline_items) . "</div>";

            default: // list
                $list_items = '';
                foreach ($items as $item) {
                    $list_items .= "<li><strong>{$item['label']}</strong> {$item['value']}</li>";
                }
                return "<ul class='ct-info-list'>{$list_items}</ul>";
        }
    }

    public function enqueue_styles() {
        if (!is_product() && !is_shop() && !is_product_category()) {
            return;
        }

        $css = '
        .ct-product-info {
            margin-bottom: var(--info-spacing, 20px);
        }

        .ct-product-info-title {
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--info-color, var(--theme-text-color));
        }

        .ct-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ct-info-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            color: var(--info-color, var(--theme-text-color));
        }

        .ct-info-table td:first-child {
            font-weight: 600;
            background-color: #f9f9f9;
            width: 30%;
        }

        .ct-info-inline .ct-info-item {
            margin-right: 15px;
            color: var(--info-color, var(--theme-text-color));
        }

        .ct-info-list {
            margin: 0;
            padding-left: 20px;
        }

        .ct-info-list li {
            margin-bottom: 5px;
            color: var(--info-color, var(--theme-text-color));
        }
        ';

        wp_add_inline_style('ct-main-styles', $css);
    }
}

// Initialize
new ProductInfoElement();
```

## Best Practices

### 1. Naming Conventions

- Use descriptive, unique element IDs (e.g., `product_specifications`, `custom_badges`)
- Prefix field names to avoid conflicts (e.g., `spec_title`, `badge_color`)
- Use consistent CSS class naming following Blocksy's `ct-` prefix pattern
- Follow WordPress coding standards for function and variable names

### 2. Performance Optimization

- Only enqueue styles on relevant pages (product, shop, category pages)
- Use conditional loading for heavy features and external resources
- Implement proper caching for dynamic content and database queries
- Minimize DOM manipulation and use efficient CSS selectors
- Lazy load non-critical elements when possible

### 3. Accessibility & UX

- Include proper ARIA labels and semantic HTML elements
- Ensure keyboard navigation works for all interactive elements
- Provide sufficient color contrast and readable font sizes
- Test with screen readers and accessibility tools
- Implement proper focus management

### 4. Internationalization & Localization

- Wrap all user-facing strings in translation functions (`__()`, `_e()`, etc.)
- Use consistent text domains throughout your plugin/theme
- Provide context for translators using `_x()` when needed
- Support RTL languages with appropriate CSS
- Test with different languages and character sets

### 5. Error Handling & Validation

- Always check for product existence before accessing product data
- Validate and sanitize all field values from user input
- Provide meaningful fallbacks for missing or invalid data
- Log errors appropriately for debugging without exposing sensitive information
- Handle edge cases gracefully (empty products, missing images, etc.)

## Troubleshooting

### Element Not Appearing in Customizer

- Verify Blocksy Companion Pro is active
- Check hook names are correct
- Ensure element ID is unique
- Clear any caching plugins

### Live Preview Not Working

- Confirm `'sync' => 'live'` is set on fields
- Check CSS variable implementation
- Verify JavaScript console for errors
- Test with default theme temporarily

### Styling Issues

- Use browser dev tools to inspect CSS
- Check CSS specificity conflicts
- Verify Blocksy's CSS variables are available
- Test responsive breakpoints

### Performance Issues

- Profile database queries
- Optimize CSS generation
- Implement proper caching
- Use conditional loading

This guide provides a complete foundation for developing custom elements that integrate seamlessly with Blocksy's Product Elements system, including full live preview functionality and responsive design support.
