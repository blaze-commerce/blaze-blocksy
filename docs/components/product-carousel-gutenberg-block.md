---
title: "Product Carousel Gutenberg Block"
description: "Custom Gutenberg block for displaying WooCommerce products in a responsive Owl Carousel slider with configurable options"
category: "component"
last_updated: "2024-12-19"
framework: "wordpress"
domain: "catalog"
layer: "frontend"
tags: ["gutenberg", "woocommerce", "carousel", "products", "responsive"]
---

# Product Carousel Gutenberg Block

A custom Gutenberg block that displays WooCommerce products in a responsive carousel slider using Owl Carousel. The block provides extensive configuration options for product filtering, carousel behavior, and responsive design.

## Overview

The Product Carousel block allows content editors to easily add product carousels to any page or post through the Gutenberg editor. It integrates seamlessly with existing WooCommerce product templates and supports advanced filtering options.

### Key Features

- **Product Filtering**: Filter by categories and sale attributes (featured, on sale, new, stock status)
- **Responsive Design**: Configure different items per slide for desktop, tablet, and mobile
- **Carousel Controls**: Navigation arrows, dots pagination, autoplay, and loop options
- **Template Integration**: Uses existing WooCommerce product card templates
- **Performance Optimized**: Conditional asset loading and efficient queries

## Usage

### Adding the Block

1. Open the Gutenberg editor
2. Click the "+" button to add a new block
3. Search for "Product Carousel" or find it in the WooCommerce category
4. Click to insert the block

### Basic Configuration

The block provides several configuration panels in the sidebar:

#### Product Selection

- **Product Categories**: Select specific categories to display (leave empty for all)
- **Sale Attribute**: Filter products by status (all, featured, on sale, new, in stock, out of stock)
- **Maximum Products**: Set the total number of products to display (1-50)

#### Carousel Settings

- **Products Per Slide**: Configure responsive breakpoints
  - Desktop: 1-8 products per slide
  - Tablet: 1-6 products per slide
  - Mobile: 1-4 products per slide
- **Navigation**: Enable/disable arrow navigation
- **Dots Pagination**: Show/hide dot indicators
- **Loop**: Enable infinite loop
- **Autoplay**: Enable automatic sliding with configurable timeout
- **Margin**: Adjust spacing between items (0-50px)

## Parameters

### Block Attributes

| Attribute            | Type    | Default                              | Description                                                         |
| -------------------- | ------- | ------------------------------------ | ------------------------------------------------------------------- |
| `selectedCategories` | array   | `[]`                                 | Array of category IDs to filter products                            |
| `saleAttribute`      | string  | `'all'`                              | Product filter: all, featured, on_sale, new, in_stock, out_of_stock |
| `productsPerSlide`   | object  | `{desktop: 4, tablet: 3, mobile: 2}` | Responsive items per slide                                          |
| `showNavigation`     | boolean | `true`                               | Show navigation arrows                                              |
| `showDots`           | boolean | `true`                               | Show dots pagination                                                |
| `autoplay`           | boolean | `false`                              | Enable autoplay                                                     |
| `autoplayTimeout`    | number  | `5000`                               | Autoplay timeout in milliseconds                                    |
| `loop`               | boolean | `false`                              | Enable infinite loop                                                |
| `margin`             | number  | `24`                                 | Margin between items in pixels                                      |
| `productsLimit`      | number  | `12`                                 | Maximum number of products to display                               |

### Sale Attribute Options

- `all`: Display all products
- `featured`: Only featured products
- `on_sale`: Products currently on sale
- `new`: Products created in the last 30 days
- `in_stock`: Products currently in stock
- `out_of_stock`: Products currently out of stock

## Returns

The block renders a responsive carousel containing WooCommerce product cards with the following structure:

```html
<div class="blaze-product-carousel-wrapper" id="product-carousel-{uuid}">
  <div class="products owl-carousel owl-theme blaze-product-carousel">
    <!-- WooCommerce product cards -->
  </div>
</div>
```

## Dependencies

### Required WordPress Plugins

- **WooCommerce**: Core functionality for product data
- **Gutenberg**: Block editor (WordPress 5.0+)

### JavaScript Dependencies

- **jQuery**: Required by Owl Carousel
- **Owl Carousel 2.3.4**: Carousel functionality
- **WordPress Block Editor APIs**: wp.blocks, wp.element, wp.components

### CSS Dependencies

- **Owl Carousel CSS**: Base carousel styles
- **Product Card Styles**: Existing theme product card styling
- **Block-specific CSS**: Custom carousel enhancements

### Theme Integration

- Uses existing WooCommerce product templates (`content-product.php`)
- Integrates with theme's product card styling variables
- Respects theme's responsive breakpoints

## Testing

### Manual Testing Steps

1. **Block Registration**

   ```bash
   # Verify block appears in editor
   # Search for "Product Carousel" in block inserter
   ```

2. **Product Filtering**

   ```bash
   # Test category filtering
   # Test sale attribute filtering
   # Verify product count limits
   ```

3. **Responsive Behavior**

   ```bash
   # Test on desktop (1200px+)
   # Test on tablet (768px-1199px)
   # Test on mobile (<768px)
   ```

4. **Carousel Functionality**
   ```bash
   # Test navigation arrows
   # Test dots pagination
   # Test autoplay functionality
   # Test loop behavior
   ```

### Code Examples

#### Basic Implementation

```php
// Block is automatically registered via includes/gutenberg/product-slider.php
// No additional code required for basic usage
```

#### Custom Product Query

```php
// The block uses WP_Query with these parameters:
$args = array(
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => $attributes['productsLimit'],
    'meta_query' => array(),
    'tax_query' => array(),
);
```

#### Frontend Carousel Initialization

```javascript
jQuery(document).ready(function ($) {
  var $carousel = $("#carousel-id .blaze-product-carousel");
  var config = $carousel.data("carousel-config");

  if ($carousel.length && typeof $.fn.owlCarousel !== "undefined") {
    $carousel.owlCarousel(config);
  }
});
```

## Changelog

### Version 1.0.0 (2024-12-19)

- Initial implementation
- Product filtering by categories and sale attributes
- Responsive carousel configuration
- Integration with existing product templates
- Block editor interface with comprehensive controls
- Performance optimizations and conditional asset loading

## File Structure

```
blocksy-child/
├── includes/gutenberg/
│   └── product-slider.php              # Main block registration and rendering
├── assets/
│   ├── css/
│   │   ├── product-carousel.css        # Frontend carousel styles
│   │   └── product-carousel-editor.css # Block editor styles
│   └── js/
│       └── product-carousel-editor.js  # Block editor JavaScript
├── includes/scripts.php                # Updated asset loading
└── docs/components/
    └── product-carousel-gutenberg-block.md # This documentation
```

## Security Considerations

- All user inputs are sanitized and validated
- Product queries use WordPress security best practices
- XSS prevention through proper escaping
- Nonce verification for API requests
- Capability checks for block editor access

## Performance Notes

- Conditional asset loading (only when block is present)
- Efficient WooCommerce product queries
- Responsive image loading
- Minimal DOM manipulation
- CDN-hosted Owl Carousel assets for better caching

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Internet Explorer 11+ (with polyfills)
- Mobile browsers (iOS Safari, Chrome Mobile)
- Responsive design for all screen sizes

## Accessibility Features

- Keyboard navigation support
- Screen reader compatible
- High contrast mode support
- Reduced motion preferences respected
- Proper ARIA labels and roles

## Troubleshooting

### Common Issues

#### Block Not Appearing in Editor

- Ensure WooCommerce plugin is active
- Check that the theme includes the block registration file
- Verify JavaScript console for errors

#### Carousel Not Working

- Confirm Owl Carousel assets are loading
- Check for JavaScript conflicts with other plugins
- Ensure jQuery is available

#### Products Not Displaying

- Verify WooCommerce products exist and are published
- Check category and attribute filter settings
- Confirm products meet visibility requirements

#### Responsive Issues

- Test responsive settings in block editor
- Check theme CSS conflicts
- Verify viewport meta tag is present

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support and Maintenance

### Regular Updates

- Monitor WordPress and WooCommerce compatibility
- Update Owl Carousel version as needed
- Test with new Gutenberg releases

### Performance Monitoring

- Monitor page load times with carousel blocks
- Check for memory usage with large product sets
- Optimize queries for better performance

## Related Documentation

- [WooCommerce Product Card Styling](../features/woocommerce-product-card-border.md)
- [Blocksy Theme Integration Guide](../blocksy-custom-element-development-guide.md)
- [Asset Loading and Performance](../performance/)

## API Reference

### PHP Hooks

```php
// Filter product query arguments
add_filter('blaze_product_carousel_query_args', function($args, $attributes) {
    // Modify query arguments
    return $args;
}, 10, 2);

// Filter carousel configuration
add_filter('blaze_product_carousel_config', function($config, $attributes) {
    // Modify carousel settings
    return $config;
}, 10, 2);
```

### JavaScript Events

```javascript
// Carousel initialized
$(document).on("blaze_carousel_initialized", function (event, carousel) {
  console.log("Carousel initialized:", carousel);
});

// Products loaded
$(document).on("blaze_carousel_products_loaded", function (event, products) {
  console.log("Products loaded:", products);
});
```
