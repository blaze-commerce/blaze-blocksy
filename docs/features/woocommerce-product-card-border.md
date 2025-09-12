---
title: "WooCommerce Product Card Border Feature"
description: "Dynamic customizable border system for WooCommerce product cards with live preview functionality"
category: "module"
last_updated: "2025-01-10"
framework: "wordpress-theme"
domain: "catalog"
layer: "frontend"
tags: ["woocommerce", "customizer", "product-cards", "borders", "live-preview"]
---

# WooCommerce Product Card Border Feature

## Overview

This feature implements a dynamic, customizable border system for WooCommerce product cards that integrates seamlessly with the Blocksy theme's customizer architecture. Users can configure border width, style, and color with real-time preview functionality without page reload.

## Usage

### Accessing the Feature

1. **WordPress Admin** → **Appearance** → **Customize**
2. Navigate to **WooCommerce** → **Product Archive** → **Card Options**
3. Look for **"Product Card Border"** option

### Configuration Options

- **Border Width**: 0-10 pixels
- **Border Style**: None, Solid, Dashed, Dotted, Double
- **Border Color**: Color picker with transparency support
- **Live Preview**: Real-time updates in customizer

### Example Usage

```php
// Get current border settings
$border_settings = blocksy_get_theme_mod('woo_card_border', [
    'width' => 1,
    'style' => 'none',
    'color' => ['color' => 'rgba(0, 0, 0, 0.1)']
]);

// Check if borders are enabled
if ($border_settings['style'] !== 'none') {
    // Borders are active
}
```

## Parameters

### Border Settings Object

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `width` | integer | 1 | Border width in pixels (0-10) |
| `style` | string | 'none' | Border style (none, solid, dashed, dotted, double) |
| `color` | object | `{'color': 'rgba(0, 0, 0, 0.1)'}` | Border color object |

### CSS Custom Properties

The system generates CSS custom properties for advanced styling:

- `--woo-card-border-width`: Border width value
- `--woo-card-border-style`: Border style value  
- `--woo-card-border-color`: Border color value

## Returns

### CSS Output

When borders are enabled, the system generates CSS targeting `[data-products] .product`:

```css
[data-products] .product {
    --woo-card-border-width: 2px;
    --woo-card-border-style: solid;
    --woo-card-border-color: #e0e0e0;
    border: 2px solid #e0e0e0 !important;
    border-radius: 8px;
}
```

### JavaScript Events

The live preview system triggers these events:

- `woo-card-border-updated`: When border settings change
- `customizer-preview-refresh`: When major changes occur

## Dependencies

### Required Files

- `includes/customization/product-card.php` - Main implementation
- `assets/js/customizer-preview.js` - Live preview functionality
- `assets/css/archive.css` - Base styling integration

### WordPress Dependencies

- WordPress Customizer API
- WooCommerce plugin
- Blocksy theme (recommended)

### JavaScript Dependencies

- jQuery
- WordPress Customizer Preview API

### Theme Integration

Works with:
- ✅ Blocksy theme (full integration)
- ✅ Other themes (fallback mode)
- ✅ Child themes

## Testing

### Manual Testing Steps

1. **Basic Functionality**
   ```bash
   # Navigate to shop page
   # Open customizer
   # Test border width changes (0-10px)
   # Test border style changes (none, solid, dashed, dotted, double)
   # Test border color changes
   ```

2. **Live Preview Testing**
   ```bash
   # In customizer preview:
   # Change border width - should update immediately
   # Change border style - should update immediately  
   # Change border color - should update immediately
   # No page reload should occur
   ```

3. **Responsive Testing**
   ```bash
   # Test on desktop (>999px)
   # Test on tablet (768-999px)
   # Test on mobile (<768px)
   # Verify borders maintain proper appearance
   ```

4. **Cross-browser Testing**
   ```bash
   # Test in Chrome, Firefox, Safari, Edge
   # Verify CSS custom properties work
   # Verify live preview functions
   ```

### Automated Testing

```php
// Unit test example
public function test_border_settings_sanitization() {
    $border_handler = new WooCommerce_Product_Card_Border();
    
    $input = [
        'width' => '5',
        'style' => 'solid',
        'color' => ['color' => '#ff0000']
    ];
    
    $result = $border_handler->sanitize_border_setting($input);
    
    $this->assertEquals(5, $result['width']);
    $this->assertEquals('solid', $result['style']);
    $this->assertEquals('#ff0000', $result['color']['color']);
}
```

### Performance Testing

```bash
# Check CSS generation performance
# Verify no memory leaks in live preview
# Test with large product catalogs (100+ products)
# Measure customizer load time impact
```

## Changelog

### Version 1.0.0 (2025-01-10)
- ✅ Initial implementation
- ✅ Blocksy theme integration
- ✅ Live preview functionality
- ✅ Responsive design support
- ✅ CSS custom properties
- ✅ Fallback mode for non-Blocksy themes
- ✅ Comprehensive sanitization
- ✅ Hover effects and transitions

### Implementation Details

**Files Modified:**
- `includes/customization/product-card.php` - Main feature implementation
- `assets/css/archive.css` - Removed static borders, added dynamic support
- `assets/js/customizer-preview.js` - Live preview functionality

**Integration Points:**
- WordPress Customizer API
- Blocksy theme options system
- WooCommerce product archive pages
- CSS custom properties system

**Security Features:**
- Input sanitization for all border values
- Proper escaping of CSS output
- Validation of border style options
- Safe color handling

## Troubleshooting

### Common Issues

1. **Borders not appearing**
   - Check if border style is set to 'none'
   - Verify WooCommerce is active
   - Ensure on shop/archive pages

2. **Live preview not working**
   - Check browser console for JavaScript errors
   - Verify customizer-preview.js is loaded
   - Ensure jQuery is available

3. **Styling conflicts**
   - Check CSS specificity issues
   - Verify !important declarations
   - Review theme compatibility

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Future Enhancements

- [ ] Border radius customization
- [ ] Individual border side controls
- [ ] Animation/transition options
- [ ] Border image support
- [ ] Advanced responsive controls
- [ ] Integration with Blocksy's design system
