# Blocksy WooCommerce Card Border Implementation Guide (Plugin/Child Theme)

## Overview

This technical documentation provides a comprehensive guide for AI agents to implement a custom border option for WooCommerce product cards in the Blocksy theme through a **plugin or child theme**. The implementation adds a new option to the Card Options panel with live preview functionality **without modifying the core theme files**.

## Objective

Add a new "Product Card Border" option to the WooCommerce Card Options panel that:

- Allows users to configure border properties (width, style, color)
- Provides live preview updates without page reload
- Integrates seamlessly with existing Blocksy customizer architecture
- Works across all card types (type-1, type-2, type-3)
- **Implemented via plugin or child theme (no core theme modification)**

## Current Architecture Analysis

### Card Options Structure

The WooCommerce card options are located in:

- **Options File**: `inc/options/woocommerce/card-product-elements.php`
- **Dynamic Styles**: `inc/dynamic-styles/global/woocommerce/archive.php`
- **Sync JavaScript**: `static/js/customizer/sync/variables/woocommerce/archive.js`

### Existing Border Implementation

Currently, only `type-2` cards have border support:

```php
'cardProductBorder' => [
    'label' => __( 'Card Border', 'blocksy' ),
    'type' => 'ct-border',
    'design' => 'block',
    'sync' => 'live',
    'divider' => 'bottom',
    'responsive' => true,
    'value' => [
        'width' => 1,
        'style' => 'none',
        'color' => [
            'color' => 'rgba(0, 0, 0, 0.05)',
        ],
    ]
],
```

## Implementation Requirements

### 1. Implementation Method: Plugin/Child Theme Approach

Instead of modifying core theme files, we'll use WordPress hooks and filters to extend Blocksy's functionality:

- **Plugin Approach**: Create a custom plugin that hooks into Blocksy's option system
- **Child Theme Approach**: Add functionality via child theme's functions.php
- **No Core Modification**: Zero changes to parent theme files

### 2. Hook Points to Utilize

- `blocksy_woo_card_options_layers:extra` - Add custom options
- `wp_head` - Inject custom CSS
- `customize_register` - Register customizer settings
- `customize_preview_init` - Add live preview JavaScript

### 3. Plugin Structure

```
custom-woo-card-border/
├── custom-woo-card-border.php (main plugin file)
├── includes/
│   ├── class-customizer-options.php
│   ├── class-dynamic-styles.php
│   └── class-live-preview.php
└── assets/
    └── js/
        └── customizer-preview.js
```

### 4. Option Configuration (via Hook)

```php
// Hook into Blocksy's card options
add_filter('blocksy_woo_card_options_layers:extra', 'add_custom_card_border_option');

function add_custom_card_border_option($options) {
    $options['woo_card_border'] = [
        'label' => __( 'Product Card Border', 'blocksy' ),
        'type' => 'ct-border',
        'design' => 'block',
        'sync' => 'live',
        'responsive' => true,
        'divider' => 'bottom',
        'value' => [
            'width' => 1,
            'style' => 'none',
            'color' => [
                'color' => 'rgba(0, 0, 0, 0.1)',
            ],
        ],
        'setting' => [ 'transport' => 'postMessage' ],
    ];

    return $options;
}
```

### 5. CSS Generation (via wp_head Hook)

```php
// Hook into wp_head to inject custom CSS
add_action('wp_head', 'inject_custom_card_border_styles');

function inject_custom_card_border_styles() {
    if (!is_shop() && !is_product_category() && !is_product_tag()) {
        return;
    }

    $border_settings = get_theme_mod('woo_card_border', [
        'width' => 1,
        'style' => 'none',
        'color' => ['color' => 'rgba(0, 0, 0, 0.1)']
    ]);

    if ($border_settings['style'] === 'none') {
        return;
    }

    $css = generate_border_css($border_settings);

    echo '<style id="custom-woo-card-border">' . $css . '</style>';
}

function generate_border_css($border_settings) {
    $width = $border_settings['width'] . 'px';
    $style = $border_settings['style'];
    $color = $border_settings['color']['color'];

    return "[data-products] .product { border: {$width} {$style} {$color}; }";
}
```

### 6. Live Preview JavaScript (via customize_preview_init)

```php
// Hook into customizer preview
add_action('customize_preview_init', 'enqueue_custom_card_border_preview_js');

function enqueue_custom_card_border_preview_js() {
    wp_enqueue_script(
        'custom-card-border-preview',
        plugin_dir_url(__FILE__) . 'assets/js/customizer-preview.js',
        ['customize-preview'],
        '1.0.0',
        true
    );
}
```

**JavaScript File (assets/js/customizer-preview.js):**

```javascript
(function ($) {
  "use strict";

  // Live preview for card border
  wp.customize("woo_card_border", function (value) {
    value.bind(function (newval) {
      updateCardBorder(newval);
    });
  });

  function updateCardBorder(borderSettings) {
    var css = "";

    if (borderSettings.style !== "none") {
      css = "[data-products] .product { ";
      css += "border: " + borderSettings.width + "px ";
      css += borderSettings.style + " ";
      css += borderSettings.color.color + "; ";
      css += "}";
    }

    // Update or create style tag
    var styleTag = $("#custom-woo-card-border-preview");
    if (styleTag.length) {
      styleTag.html(css);
    } else {
      $("head").append(
        '<style id="custom-woo-card-border-preview">' + css + "</style>"
      );
    }
  }
})(jQuery);
```

## Plugin/Child Theme Implementation

### Method 1: Complete Plugin Implementation

**Main Plugin File (custom-woo-card-border.php):**

```php
<?php
/**
 * Plugin Name: Custom WooCommerce Card Border
 * Description: Adds border customization for WooCommerce product cards in Blocksy theme
 * Version: 1.0.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CustomWooCardBorder {

    public function __construct() {
        add_action('init', [$this, 'init']);
    }

    public function init() {
        // Check if Blocksy theme is active
        if (!function_exists('blocksy_get_theme_mod')) {
            return;
        }

        // Add customizer option
        add_filter('blocksy_woo_card_options_layers:extra', [$this, 'add_border_option']);

        // Register customizer setting
        add_action('customize_register', [$this, 'register_customizer_setting']);

        // Add CSS
        add_action('wp_head', [$this, 'inject_border_styles']);

        // Add live preview
        add_action('customize_preview_init', [$this, 'enqueue_preview_js']);
    }

    public function add_border_option($options) {
        $options['woo_card_border'] = [
            'label' => __('Product Card Border', 'custom-woo-card-border'),
            'type' => 'ct-border',
            'design' => 'block',
            'sync' => 'live',
            'responsive' => true,
            'divider' => 'bottom',
            'value' => [
                'width' => 1,
                'style' => 'none',
                'color' => [
                    'color' => 'rgba(0, 0, 0, 0.1)',
                ],
            ],
            'setting' => ['transport' => 'postMessage'],
        ];

        return $options;
    }

    public function register_customizer_setting($wp_customize) {
        $wp_customize->add_setting('woo_card_border', [
            'default' => [
                'width' => 1,
                'style' => 'none',
                'color' => ['color' => 'rgba(0, 0, 0, 0.1)'],
            ],
            'transport' => 'postMessage',
            'sanitize_callback' => [$this, 'sanitize_border_setting'],
        ]);
    }

    public function sanitize_border_setting($input) {
        // Add sanitization logic here
        return $input;
    }

    public function inject_border_styles() {
        if (!is_shop() && !is_product_category() && !is_product_tag()) {
            return;
        }

        $border_settings = get_theme_mod('woo_card_border', [
            'width' => 1,
            'style' => 'none',
            'color' => ['color' => 'rgba(0, 0, 0, 0.1)']
        ]);

        if ($border_settings['style'] === 'none') {
            return;
        }

        $css = $this->generate_border_css($border_settings);
        echo '<style id="custom-woo-card-border">' . $css . '</style>';
    }

    private function generate_border_css($border_settings) {
        $width = intval($border_settings['width']) . 'px';
        $style = sanitize_text_field($border_settings['style']);
        $color = sanitize_text_field($border_settings['color']['color']);

        return "[data-products] .product { border: {$width} {$style} {$color}; }";
    }

    public function enqueue_preview_js() {
        wp_enqueue_script(
            'custom-card-border-preview',
            plugin_dir_url(__FILE__) . 'assets/js/customizer-preview.js',
            ['customize-preview'],
            '1.0.0',
            true
        );
    }
}

// Initialize the plugin
new CustomWooCardBorder();
```

### Step 2: Implement CSS Generation

**Location**: `inc/dynamic-styles/global/woocommerce/archive.php`

**Action**: Add border CSS generation that applies to all product cards regardless of type.

**Position**: Add after the existing card styling code (around line 250-300).

### Step 3: Add Live Preview Support

**Location**: `static/js/customizer/sync/variables/woocommerce/archive.js`

**Action**: Add JavaScript configuration for live preview updates.

**Position**: Add to the existing variables object.

### Step 4: CSS Selector Strategy

**Target Selector**: `[data-products] .product`

- This selector targets all product cards across all card types
- Ensures consistent border application
- Maintains compatibility with existing styles

### Step 5: Integration Points

**Sync Configuration**:

```php
'sync' => 'live'  // Enables live preview
'setting' => [ 'transport' => 'postMessage' ]  // Prevents page reload
```

**Responsive Support**:

```php
'responsive' => true  // Enables tablet/mobile specific settings
```

## Technical Considerations

### 1. CSS Specificity

- Use appropriate CSS specificity to ensure border styles apply correctly
- Consider existing card type specific styles
- Ensure compatibility with hover effects

### 2. Performance

- Use CSS variables for efficient style updates
- Minimize DOM manipulation during live preview
- Leverage existing Blocksy sync infrastructure

### 3. Compatibility

- Ensure compatibility with all card types (type-1, type-2, type-3)
- Test with different product layouts
- Verify mobile responsiveness

### 4. Default Values

- Set sensible defaults that don't interfere with existing designs
- Use `style: 'none'` as default to maintain current appearance
- Provide subtle default color for when border is enabled

## Testing Checklist

### Functionality Tests

- [ ] Option appears in Card Options > Design tab
- [ ] Border settings apply to all card types
- [ ] Live preview works without page reload
- [ ] Responsive settings function correctly
- [ ] Default values don't break existing designs

### Visual Tests

- [ ] Border renders correctly on all card types
- [ ] Color picker updates preview immediately
- [ ] Width and style changes apply instantly
- [ ] Mobile/tablet responsive behavior works
- [ ] No conflicts with existing card styles

### Integration Tests

- [ ] Compatible with existing card options
- [ ] Works with different product layouts
- [ ] Functions with card hover effects
- [ ] Maintains performance standards

## Code Examples

### Complete Option Definition

```php
'woo_card_border' => [
    'label' => __( 'Product Card Border', 'blocksy' ),
    'type' => 'ct-border',
    'design' => 'block',
    'sync' => 'live',
    'responsive' => true,
    'divider' => 'bottom',
    'value' => [
        'width' => 1,
        'style' => 'none',
        'color' => [
            'color' => 'rgba(0, 0, 0, 0.1)',
        ],
    ],
    'setting' => [ 'transport' => 'postMessage' ],
],
```

### CSS Generation Code

```php
blocksy_output_border([
    'css' => $css,
    'tablet_css' => $tablet_css,
    'mobile_css' => $mobile_css,
    'selector' => '[data-products] .product',
    'variableName' => 'woo-card-border',
    'value' => blocksy_get_theme_mod('woo_card_border'),
    'default' => [
        'width' => 1,
        'style' => 'none',
        'color' => [
            'color' => 'rgba(0, 0, 0, 0.1)',
        ],
    ],
    'responsive' => true,
    'skip_none' => true,
]);
```

### JavaScript Sync Configuration

```javascript
woo_card_border: [
    {
        selector: '[data-products] .product',
        variable: 'woo-card-border',
        type: 'border',
        responsive: true,
    },
],
```

## Expected Outcome

After implementation, users will have:

1. A new "Product Card Border" option in WooCommerce Card Options
2. Full border customization (width, style, color)
3. Live preview functionality
4. Responsive border settings
5. Consistent border application across all card types

The implementation will seamlessly integrate with Blocksy's existing architecture and provide a professional user experience consistent with other theme options.

## AI Agent Implementation Instructions

### Task Summary

Implement a new "Product Card Border" option in Blocksy WooCommerce Card Options with live preview functionality.

### Implementation Steps

#### Step 1: Add Option to Card Options Panel

**File**: `inc/options/woocommerce/card-product-elements.php`
**Location**: In the Design tab section (around line 720-750)
**Action**: Add the border option outside any card type conditions

```php
'woo_card_border' => [
    'label' => __( 'Product Card Border', 'blocksy' ),
    'type' => 'ct-border',
    'design' => 'block',
    'sync' => 'live',
    'responsive' => true,
    'divider' => 'bottom',
    'value' => [
        'width' => 1,
        'style' => 'none',
        'color' => [
            'color' => 'rgba(0, 0, 0, 0.1)',
        ],
    ],
    'setting' => [ 'transport' => 'postMessage' ],
],
```

#### Step 2: Add CSS Generation

**File**: `inc/dynamic-styles/global/woocommerce/archive.php`
**Location**: After existing card styling code (around line 250-300)
**Action**: Add border CSS generation

```php
// Product card border (all types)
blocksy_output_border([
    'css' => $css,
    'tablet_css' => $tablet_css,
    'mobile_css' => $mobile_css,
    'selector' => '[data-products] .product',
    'variableName' => 'woo-card-border',
    'value' => blocksy_get_theme_mod('woo_card_border'),
    'default' => [
        'width' => 1,
        'style' => 'none',
        'color' => [
            'color' => 'rgba(0, 0, 0, 0.1)',
        ],
    ],
    'responsive' => true,
    'skip_none' => true,
]);
```

#### Step 3: Add Live Preview Support

**File**: `static/js/customizer/sync/variables/woocommerce/archive.js`
**Location**: Add to existing variables object
**Action**: Add JavaScript sync configuration

```javascript
woo_card_border: [
    {
        selector: '[data-products] .product',
        variable: 'woo-card-border',
        type: 'border',
        responsive: true,
    },
],
```

### Key Requirements

- Use exact option ID: `woo_card_border`
- Apply to selector: `[data-products] .product`
- Enable live preview with `'sync' => 'live'`
- Include responsive support
- Set default style to 'none' to maintain current appearance

### Testing

After implementation:

1. Check Card Options panel for new border option
2. Test live preview functionality
3. Verify responsive behavior
4. Ensure compatibility with all card types

### Method 2: Child Theme Implementation

**Add to Child Theme functions.php:**

```php
<?php
/**
 * Custom WooCommerce Card Border for Blocksy Child Theme
 */

// Check if Blocksy theme is active
if (!function_exists('blocksy_get_theme_mod')) {
    return;
}

// Add border option to card options
add_filter('blocksy_woo_card_options_layers:extra', 'child_theme_add_card_border_option');
function child_theme_add_card_border_option($options) {
    $options['woo_card_border'] = [
        'label' => __('Product Card Border', 'blocksy-child'),
        'type' => 'ct-border',
        'design' => 'block',
        'sync' => 'live',
        'responsive' => true,
        'divider' => 'bottom',
        'value' => [
            'width' => 1,
            'style' => 'none',
            'color' => [
                'color' => 'rgba(0, 0, 0, 0.1)',
            ],
        ],
        'setting' => ['transport' => 'postMessage'],
    ];

    return $options;
}

// Register customizer setting
add_action('customize_register', 'child_theme_register_card_border_setting');
function child_theme_register_card_border_setting($wp_customize) {
    $wp_customize->add_setting('woo_card_border', [
        'default' => [
            'width' => 1,
            'style' => 'none',
            'color' => ['color' => 'rgba(0, 0, 0, 0.1)'],
        ],
        'transport' => 'postMessage',
    ]);
}

// Inject CSS styles
add_action('wp_head', 'child_theme_inject_card_border_styles');
function child_theme_inject_card_border_styles() {
    if (!is_shop() && !is_product_category() && !is_product_tag()) {
        return;
    }

    $border_settings = get_theme_mod('woo_card_border', [
        'width' => 1,
        'style' => 'none',
        'color' => ['color' => 'rgba(0, 0, 0, 0.1)']
    ]);

    if ($border_settings['style'] === 'none') {
        return;
    }

    $width = intval($border_settings['width']) . 'px';
    $style = sanitize_text_field($border_settings['style']);
    $color = sanitize_text_field($border_settings['color']['color']);

    $css = "[data-products] .product { border: {$width} {$style} {$color}; }";

    echo '<style id="custom-woo-card-border">' . $css . '</style>';
}

// Add live preview JavaScript
add_action('customize_preview_init', 'child_theme_enqueue_card_border_preview');
function child_theme_enqueue_card_border_preview() {
    $js = "
    (function($) {
        wp.customize('woo_card_border', function(value) {
            value.bind(function(newval) {
                var css = '';
                if (newval.style !== 'none') {
                    css = '[data-products] .product { ';
                    css += 'border: ' + newval.width + 'px ';
                    css += newval.style + ' ';
                    css += newval.color.color + '; ';
                    css += '}';
                }

                var styleTag = $('#custom-woo-card-border-preview');
                if (styleTag.length) {
                    styleTag.html(css);
                } else {
                    $('head').append('<style id=\"custom-woo-card-border-preview\">' + css + '</style>');
                }
            });
        });
    })(jQuery);
    ";

    wp_add_inline_script('customize-preview', $js);
}
```

## Final Implementation Guide for AI Agent

### Task Summary

Implement a new "Product Card Border" option for Blocksy WooCommerce cards via **plugin or child theme** (no core theme modification).

### Choose Implementation Method:

#### Option A: Plugin Implementation

1. Create plugin directory: `custom-woo-card-border/`
2. Use the complete plugin code provided above
3. Create `assets/js/customizer-preview.js` with the JavaScript code
4. Activate plugin

#### Option B: Child Theme Implementation

1. Add the child theme code to `functions.php`
2. No additional files needed (JavaScript is inline)

### Key Requirements

- **No core theme modification**
- Use hook: `blocksy_woo_card_options_layers:extra`
- Option ID: `woo_card_border`
- Selector: `[data-products] .product`
- Enable live preview with postMessage transport
- Include responsive support

### Testing Checklist

- [ ] Option appears in Card Options panel
- [ ] Border settings apply to all card types
- [ ] Live preview works without page reload
- [ ] No conflicts with existing functionality
- [ ] Works in plugin and child theme approaches
