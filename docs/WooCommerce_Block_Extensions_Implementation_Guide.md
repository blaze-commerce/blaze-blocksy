# WooCommerce Block Extensions - Implementation Guide

## Overview

This implementation extends WooCommerce Gutenberg blocks with responsive features and enhancements without modifying core WooCommerce files. The extensions are integrated directly into the Blocksy child theme.

## Features Implemented

### 1. Product Collection Block - Responsive Controls
- **Responsive Columns**: Configure different column layouts for desktop, tablet, and mobile
- **Responsive Product Count**: Set different product counts per device
- **Automatic Adjustment**: Layout automatically adjusts based on screen size
- **Smooth Transitions**: CSS-based responsive behavior with no page reload

### 2. Product Image Block - Hover & Wishlist
- **Hover Image Swap**: Display second product image on hover (uses first gallery image)
- **Wishlist Integration**: Fully integrated with Blocksy wishlist functionality
- **Customizable Position**: Choose wishlist button position (top-left, top-right, bottom-left, bottom-right)
- **AJAX Functionality**: Add/remove from wishlist without page reload

## File Structure

```
includes/woocommerce-blocks/
├── wc-block-extensions.php                          # Main loader file
├── includes/
│   ├── class-product-collection-extension.php       # Product Collection extension
│   └── class-product-image-extension.php            # Product Image extension
└── assets/
    ├── js/
    │   ├── product-collection-extension.js          # Editor controls for Product Collection
    │   ├── product-collection-frontend.js           # Frontend responsive behavior
    │   ├── product-image-extension.js               # Editor controls for Product Image
    │   └── product-image-frontend.js                # Frontend hover & wishlist functionality
    └── css/
        ├── frontend.css                              # Frontend styles
        └── editor.css                                # Editor styles
```

## Installation

The extension is automatically loaded through the theme's `functions.php` file. No additional installation steps required.

## Usage Guide

### Product Collection Block - Responsive Settings

1. **Add Product Collection Block**
   - In the WordPress editor, add a "Product Collection" block
   - Configure your product query as usual

2. **Enable Responsive Settings**
   - In the block sidebar, find the "Responsive Settings" panel
   - Toggle "Enable Responsive Layout" to ON

3. **Configure Columns per Device**
   - **Desktop Columns**: 1-6 columns (default: 4)
   - **Tablet Columns**: 1-4 columns (default: 3)
   - **Mobile Columns**: 1-2 columns (default: 2)

4. **Configure Products per Device**
   - **Desktop Products**: 1-20 products (default: 8)
   - **Tablet Products**: 1-12 products (default: 6)
   - **Mobile Products**: 1-8 products (default: 4)

#### Example Configuration

**E-commerce Store Example:**
- Desktop: 4 columns, 8 products
- Tablet: 3 columns, 6 products
- Mobile: 2 columns, 4 products

**Minimal Design Example:**
- Desktop: 3 columns, 6 products
- Tablet: 2 columns, 4 products
- Mobile: 1 column, 3 products

### Product Image Block - Image Enhancements

1. **Add Product Image Block**
   - Product Image blocks are typically inside Product Collection blocks
   - Or add manually within Product Template

2. **Enable Hover Image**
   - In the block sidebar, find "Image Enhancements" panel
   - Toggle "Enable Hover Image" to ON
   - **Note**: Product must have gallery images for this to work

3. **Enable Wishlist Button**
   - Toggle "Show Wishlist Button" to ON
   - Choose button position from dropdown:
     - Top Left
     - Top Right (default)
     - Bottom Left
     - Bottom Right

## Technical Details

### Responsive Breakpoints

```javascript
{
    mobile: 768,   // < 768px
    tablet: 1024   // 768px - 1023px
    desktop: 1024+ // >= 1024px
}
```

### Blocksy Wishlist Integration

The extension integrates seamlessly with Blocksy's wishlist functionality:

```php
// Uses Blocksy extension
blc_get_ext('woocommerce-extra')->get_wish_list()

// Or uses helper class
BlocksyChildWishlistHelper::get_current_wishlist()
```

### AJAX Endpoints

**Toggle Wishlist:**
- Action: `wc_block_toggle_wishlist`
- Nonce: `wc_block_extensions_nonce`
- Parameters: `product_id`

### CSS Custom Properties

```css
.wc-responsive-collection {
    --wc-responsive-columns: 4; /* Dynamically updated */
}
```

## Browser Compatibility

- **Modern Browsers**: Full support (Chrome, Firefox, Safari, Edge)
- **IE11**: Basic support (no CSS Grid, falls back to flexbox)
- **Mobile Browsers**: Full support with touch-optimized interactions

## Performance Considerations

### Optimizations Implemented

1. **Debounced Resize**: Window resize events are debounced (250ms)
2. **Image Preloading**: Hover images are preloaded for smooth transitions
3. **CSS-based Responsive**: Uses CSS Grid for better performance
4. **Minimal DOM Manipulation**: Only updates necessary elements

### Best Practices

- **Gallery Images**: Optimize gallery images to same size as main product image
- **Product Count**: Don't set excessively high product counts (max 20 recommended)
- **Lazy Loading**: Enable WordPress lazy loading for product images

## Accessibility Features

### Keyboard Navigation
- Wishlist buttons are fully keyboard accessible
- Focus indicators visible on all interactive elements

### Screen Readers
- Proper ARIA labels on wishlist buttons
- Semantic HTML structure maintained

### Reduced Motion
- Respects `prefers-reduced-motion` media query
- Disables animations for users who prefer reduced motion

## Troubleshooting

### Responsive Settings Not Showing

**Issue**: Responsive Settings panel not visible in editor

**Solutions**:
1. Clear browser cache and reload editor
2. Check browser console for JavaScript errors
3. Ensure WooCommerce is active and updated
4. Verify block is actually "Product Collection" block

### Hover Image Not Working

**Issue**: Second image not showing on hover

**Solutions**:
1. Verify product has gallery images
2. Check that "Enable Hover Image" is toggled ON
3. Ensure gallery images are published (not draft)
4. Check browser console for image loading errors

### Wishlist Button Not Appearing

**Issue**: Wishlist button not visible

**Solutions**:
1. Verify Blocksy Companion plugin is active
2. Check that WooCommerce Extra extension is enabled in Blocksy
3. Ensure "Show Wishlist Button" is toggled ON
4. Check CSS conflicts with theme

### Wishlist Not Saving

**Issue**: Products not being added to wishlist

**Solutions**:
1. Check browser console for AJAX errors
2. Verify nonce is valid (clear cache if needed)
3. Test with different browser/incognito mode
4. Check server error logs for PHP errors

## Customization

### Custom Breakpoints

Edit `product-collection-frontend.js`:

```javascript
this.breakpoints = {
    mobile: 576,   // Custom mobile breakpoint
    tablet: 992    // Custom tablet breakpoint
};
```

### Custom Wishlist Button Styles

Add to your theme's CSS:

```css
.wc-wishlist-button {
    background: #your-color;
    border-radius: 4px; /* Square instead of circle */
}

.wc-wishlist-button--top-right {
    top: 20px;  /* Custom position */
    right: 20px;
}
```

### Custom Responsive Columns

Override CSS custom property:

```css
.wc-responsive-collection {
    --wc-responsive-columns: 5; /* Force 5 columns */
}

@media (max-width: 1200px) {
    .wc-responsive-collection {
        --wc-responsive-columns: 3;
    }
}
```

## Testing Checklist

### Product Collection Responsive

- [ ] Desktop view shows correct columns and product count
- [ ] Tablet view adjusts columns and products correctly
- [ ] Mobile view displays properly
- [ ] Resize window triggers responsive changes
- [ ] No layout shift or flickering during resize
- [ ] Products hide/show smoothly based on count

### Product Image Hover

- [ ] Hover shows second image smoothly
- [ ] Mouse leave restores original image
- [ ] Works on all products with gallery images
- [ ] No hover effect on products without gallery
- [ ] Image transition is smooth (no flash)
- [ ] Works in Product Collection and single product

### Wishlist Integration

- [ ] Wishlist button appears in correct position
- [ ] Click adds product to wishlist
- [ ] Button state updates (filled heart)
- [ ] Click again removes from wishlist
- [ ] Success message displays
- [ ] Wishlist count updates in header
- [ ] Works for logged-in users
- [ ] Works for guest users (if enabled)
- [ ] Persists across page loads

### Cross-browser Testing

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Mobile Chrome (Android)

### Accessibility Testing

- [ ] Keyboard navigation works
- [ ] Screen reader announces buttons correctly
- [ ] Focus indicators visible
- [ ] Color contrast meets WCAG AA
- [ ] Works with reduced motion preference

## Support & Maintenance

### Version Compatibility

- **WordPress**: 6.0+
- **WooCommerce**: 8.0+
- **Blocksy Theme**: Latest version
- **Blocksy Companion**: Latest version
- **PHP**: 7.4+

### Update Procedure

When updating WooCommerce or WordPress:

1. Test in staging environment first
2. Check browser console for deprecation warnings
3. Verify block editor loads without errors
4. Test all features on frontend
5. Update code if necessary

### Known Limitations

1. **Product Collection Only**: Responsive features only work with Product Collection block (not legacy product grids)
2. **Gallery Images Required**: Hover image requires products to have gallery images
3. **Blocksy Dependency**: Wishlist requires Blocksy Companion plugin
4. **No IE11 Grid**: Internet Explorer 11 doesn't support CSS Grid (uses fallback)

## Future Enhancements

Potential features for future versions:

- [ ] Custom breakpoint configuration in editor
- [ ] Multiple hover images (image gallery on hover)
- [ ] Quick view integration
- [ ] Compare products button
- [ ] Animation options for hover effects
- [ ] Wishlist button icon customization
- [ ] Integration with other wishlist plugins

---

**Version**: 1.0.0  
**Last Updated**: 2025-10-13  
**Author**: Blaze Commerce Team

