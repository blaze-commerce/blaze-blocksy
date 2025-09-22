---
title: "How to Use Product Carousel Block"
description: "Complete guide for using custom Gutenberg block to display WooCommerce products in carousel slider"
category: "guide"
last_updated: "2024-12-19"
framework: "wordpress"
domain: "catalog"
layer: "frontend"
tags: ["gutenberg", "woocommerce", "carousel", "guide", "english"]
---

# How to Use Product Carousel Block

Complete guide for using the custom Gutenberg block that displays WooCommerce products in a responsive carousel slider format.

## Overview

Product Carousel Block is a custom block that allows you to display WooCommerce products in an attractive and responsive slider format. This block integrates with existing product card templates and provides various configuration options.

## Adding the Block

### Step 1: Open Gutenberg Editor
1. Navigate to the page or post you want to edit
2. Ensure you're using the Gutenberg editor (not Classic Editor)

### Step 2: Add the Block
1. Click the "+" button to add a new block
2. Search for "Product Carousel" in the search box
3. Or find it in the "WooCommerce" category
4. Click to add the block

## Block Configuration

### Product Selection Panel

#### Product Categories
- **Function**: Filter products by specific categories
- **Usage**: 
  - Check the categories you want to display
  - Leave empty to display all categories
- **Tips**: Select maximum 3-4 categories for optimal performance

#### Sale Attribute
Filter options based on product status:

- **All Products**: All products
- **Featured Products**: Featured products only
- **On Sale Products**: Products currently on sale
- **New Products**: New products (last 30 days)
- **In Stock Products**: Available products
- **Out of Stock Products**: Out of stock products

#### Maximum Products
- **Range**: 1-50 products
- **Default**: 12 products
- **Recommendation**: 8-16 products for optimal performance

### Carousel Settings Panel

#### Products Per Slide
Responsive configuration for different screen sizes:

- **Desktop**: 1-8 products per slide (recommended: 4)
- **Tablet**: 1-6 products per slide (recommended: 3)
- **Mobile**: 1-4 products per slide (recommended: 2)

#### Navigation Controls

- **Show Navigation Arrows**: Display navigation arrows
  - Automatically hidden on mobile for better UX
- **Show Dots Pagination**: Display dot indicators
- **Enable Loop**: Enable infinite loop

#### Autoplay Settings

- **Enable Autoplay**: Enable automatic sliding
- **Autoplay Timeout**: Duration between slides (1000-10000ms)
  - Recommendation: 5000ms (5 seconds)

#### Spacing

- **Margin Between Items**: Space between products (0-50px)
- **Default**: 24px
- **Mobile**: Automatically adjusts for small screens

## Best Practices

### Product Selection
1. **Specific Categories**: Choose categories relevant to page content
2. **Featured Products**: Use "Featured" filter for main pages
3. **Sale Products**: Use "On Sale" filter for promotional pages

### Responsive Settings
1. **Desktop (4 products)**: Optimal for large screens
2. **Tablet (3 products)**: Balance between visibility and detail
3. **Mobile (2 products)**: Ensures products remain readable

### Performance
1. **Limit Quantity**: Maximum 16 products per carousel
2. **Limited Categories**: Select maximum 3-4 categories
3. **Smart Autoplay**: Use autoplay only when necessary

## Usage Examples

### Homepage Hero Section
```
Configuration:
- Categories: Featured Products
- Sale Attribute: Featured Products
- Desktop: 4, Tablet: 3, Mobile: 2
- Autoplay: Enabled (5000ms)
- Navigation: Enabled
```

### Category Page Related Products
```
Configuration:
- Categories: Related categories
- Sale Attribute: All Products
- Desktop: 4, Tablet: 3, Mobile: 2
- Autoplay: Disabled
- Navigation: Enabled
```

### Sale/Promo Page
```
Configuration:
- Categories: All categories
- Sale Attribute: On Sale Products
- Desktop: 5, Tablet: 3, Mobile: 2
- Autoplay: Enabled (4000ms)
- Navigation: Enabled
```

## Troubleshooting

### Block Not Appearing in Editor
1. Ensure WooCommerce plugin is active
2. Check theme supports Gutenberg
3. Check browser console for JavaScript errors

### Carousel Not Working
1. Ensure jQuery is available
2. Check for conflicts with other plugins
3. Verify Owl Carousel loads correctly

### Products Not Displaying
1. Ensure published products exist
2. Check category and attribute filters
3. Ensure products meet visibility criteria

### Non-Responsive Display
1. Check responsive settings in block
2. Check CSS conflicts with theme
3. Ensure viewport meta tag exists in header

## Tips and Tricks

### Performance Optimization
1. Use CDN for product images
2. Limit number of displayed products
3. Enable caching for pages with carousel

### Attractive Design
1. Choose products with high-quality images
2. Ensure consistent image sizes
3. Use adequate margin for readability

### SEO Friendly
1. Ensure products have good titles and descriptions
2. Use alt text for product images
3. Consider lazy loading for images

## Support and Maintenance

### Regular Updates
- Monitor compatibility with latest WordPress and WooCommerce
- Update Owl Carousel if new version available
- Test with latest Gutenberg releases

### Performance Monitoring
- Monitor page loading times with carousel
- Check memory usage with large product sets
- Optimize queries for better performance

## Related Documentation

- [Product Carousel Technical Documentation](../components/product-carousel-gutenberg-block.md)
- [WooCommerce Product Card Styling](../features/woocommerce-product-card-border.md)
- [Blocksy Theme Integration Guide](../blocksy-custom-element-development-guide.md)

## Support Contact

If you experience issues or need assistance:
1. Check technical documentation first
2. Enable debug mode to see detailed errors
3. Contact development team with complete error information
