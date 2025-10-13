# WooCommerce Block Extensions - Implementation Guide

## Overview

This implementation extends WooCommerce Gutenberg blocks with enhanced functionality without modifying core WooCommerce files. The extensions are located in `/includes/customization/wc-blocks/` and provide:

1. **Product Collection Block**: Responsive column and product count controls
2. **Product Image Block**: Hover image swap and wishlist button integration

## Features

### 1. Product Collection - Responsive Settings

Allows configuring different column layouts and product counts for different device types:

- **Desktop** (≥1024px): Default 4 columns, 8 products
- **Tablet** (768px-1023px): Default 3 columns, 6 products
- **Mobile** (<768px): Default 2 columns, 4 products

#### Usage in Block Editor

1. Add a **Product Collection** block to your page
2. In the block settings sidebar, find **"Responsive Settings"** panel
3. Enable **"Enable Responsive Layout"**
4. Configure columns and product counts for each device type

#### Technical Implementation

- **PHP Class**: `WC_Product_Collection_Responsive_Extension`
- **Location**: `/includes/customization/wc-blocks/product-collection-responsive.php`
- **Editor Script**: `/assets/wc-blocks/product-collection-responsive-editor.js`
- **Frontend Script**: `/assets/wc-blocks/product-collection-responsive-frontend.js`
- **Styles**: `/assets/wc-blocks/product-collection-responsive.css`

### 2. Product Image - Enhancements

Adds two powerful features to Product Image blocks:

#### A. Hover Image Swap

Shows the second gallery image when hovering over the product image.

**Requirements**:
- Product must have at least one gallery image
- Enable "Enable Hover Image" in block settings

**Features**:
- Smooth fade transition
- Preloads hover image for performance
- Restores original image on mouse leave

#### B. Wishlist Button

Adds a wishlist button overlay on product images.

**Features**:
- Integrates with Blocksy wishlist functionality
- Configurable button position (top-left, top-right, bottom-left, bottom-right)
- Visual feedback for added/removed states
- AJAX-powered for seamless experience
- Fallback to custom cookie-based wishlist if Blocksy wishlist is not available

**Usage in Block Editor**:

1. Add a **Product Image** block (usually within Product Collection)
2. In the block settings sidebar, find **"Image Enhancements"** panel
3. Enable **"Enable Hover Image"** for hover effect
4. Enable **"Show Wishlist Button"** for wishlist functionality
5. Choose **"Wishlist Button Position"** if wishlist is enabled

#### Technical Implementation

- **PHP Class**: `WC_Product_Image_Enhancement_Extension`
- **Location**: `/includes/customization/wc-blocks/product-image-enhancements.php`
- **Editor Script**: `/assets/wc-blocks/product-image-enhancement-editor.js`
- **Frontend Script**: `/assets/wc-blocks/product-image-enhancement-frontend.js`
- **Styles**: `/assets/wc-blocks/product-image-enhancement.css`
- **AJAX Handler**: `/includes/customization/wc-blocks/wishlist-ajax-handler.php`

## File Structure

```
/includes/customization/wc-blocks/
├── loader.php                          # Main loader file
├── product-collection-responsive.php   # Product Collection extension
├── product-image-enhancements.php      # Product Image extension
└── wishlist-ajax-handler.php           # Wishlist AJAX handler

/assets/wc-blocks/
├── product-collection-responsive-editor.js    # Editor controls for Product Collection
├── product-collection-responsive-frontend.js  # Frontend logic for Product Collection
├── product-collection-responsive.css          # Styles for Product Collection
├── product-image-enhancement-editor.js        # Editor controls for Product Image
├── product-image-enhancement-frontend.js      # Frontend logic for Product Image
└── product-image-enhancement.css              # Styles for Product Image
```

## How It Works

### Block Extension Mechanism

The implementation uses WordPress block filters and hooks to extend existing WooCommerce blocks:

1. **Block Metadata Extension**: Uses `block_type_metadata` filter to add custom attributes
2. **Block Rendering**: Uses `render_block_woocommerce/*` filters to modify block output
3. **Editor Integration**: Uses `editor.BlockEdit` filter to add custom controls
4. **Frontend Enhancement**: JavaScript handles responsive behavior and interactions

### Responsive Product Collection

**Server-side (PHP)**:
- Adds `responsiveColumns`, `responsiveProductCount`, and `enableResponsive` attributes
- Injects data attributes into rendered HTML for JavaScript consumption

**Client-side (JavaScript)**:
- Detects current breakpoint based on window width
- Applies appropriate column count via CSS custom properties
- Shows/hides products based on configured count
- Handles window resize with debouncing

### Product Image Enhancements

**Hover Image**:
- Stores hover image data in `data-hover-image` attribute
- JavaScript swaps image src/srcset on mouseenter/mouseleave
- Preloads hover image for smooth transition

**Wishlist Button**:
- Renders wishlist button HTML server-side
- JavaScript handles click events
- Integrates with Blocksy wishlist via AJAX
- Falls back to cookie-based storage if Blocksy unavailable

## Integration with Blocksy Wishlist

The wishlist functionality integrates seamlessly with Blocksy's WooCommerce Extra extension:

1. **Detection**: Checks if `BlocksyChildWishlistHelper` class exists
2. **Integration**: Uses Blocksy's wishlist methods for add/remove operations
3. **Fallback**: Provides custom cookie-based wishlist if Blocksy is unavailable
4. **Synchronization**: Updates wishlist count and state across the site

## Customization

### Breakpoints

To customize responsive breakpoints, edit the JavaScript file:

**File**: `/assets/wc-blocks/product-collection-responsive-frontend.js`

```javascript
this.breakpoints = {
    desktop: 1024,  // Change desktop breakpoint
    tablet: 768     // Change tablet breakpoint
};
```

### Wishlist Button Styling

To customize wishlist button appearance, edit the CSS file:

**File**: `/assets/wc-blocks/product-image-enhancement.css`

```css
.wc-wishlist-button {
    width: 40px;           /* Button size */
    height: 40px;
    background: rgba(255, 255, 255, 0.95);  /* Background color */
    border-radius: 50%;    /* Shape */
}

.wc-wishlist-button.wc-wishlist-added {
    background: #e74c3c;   /* Color when added */
    color: white;
}
```

### Hover Image Transition

To customize hover image transition, edit the CSS file:

**File**: `/assets/wc-blocks/product-image-enhancement.css`

```css
.wc-hover-image-enabled img {
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
}

.wc-hover-image-enabled:hover img {
    transform: scale(1.05);  /* Zoom effect on hover */
}
```

## Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11+ (with polyfills for CSS custom properties)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Considerations

1. **Lazy Loading**: Hover images are preloaded only when needed
2. **Debouncing**: Window resize events are debounced (250ms)
3. **CSS Custom Properties**: Used for efficient responsive updates
4. **Conditional Loading**: Assets only loaded when blocks are present

## Troubleshooting

### Responsive Settings Not Showing

**Issue**: Responsive settings panel doesn't appear in block editor

**Solution**:
1. Clear browser cache
2. Check browser console for JavaScript errors
3. Ensure WooCommerce Blocks plugin is active
4. Verify file permissions for JavaScript files

### Hover Image Not Working

**Issue**: Second image doesn't show on hover

**Solution**:
1. Ensure product has gallery images
2. Check that "Enable Hover Image" is toggled on
3. Verify JavaScript is loading (check Network tab)
4. Check browser console for errors

### Wishlist Button Not Responding

**Issue**: Clicking wishlist button does nothing

**Solution**:
1. Check browser console for AJAX errors
2. Verify nonce is being generated correctly
3. Ensure AJAX handler is loaded
4. Check if Blocksy wishlist extension is active

## Future Enhancements

Potential improvements for future versions:

1. **Additional Breakpoints**: Support for more device sizes
2. **Animation Options**: Configurable transition effects
3. **Wishlist Sync**: Real-time synchronization across tabs
4. **Quick View Integration**: Add quick view button alongside wishlist
5. **Analytics**: Track wishlist interactions
6. **A/B Testing**: Built-in testing for different configurations

## Support

For issues or questions:
1. Check browser console for errors
2. Enable WordPress debug mode (`WP_DEBUG`)
3. Review error logs in `/wp-content/debug.log`
4. Contact development team with error details

## Changelog

### Version 1.0.0
- Initial implementation
- Product Collection responsive controls
- Product Image hover swap
- Product Image wishlist button
- Blocksy wishlist integration
- AJAX wishlist handler
- Responsive CSS framework

