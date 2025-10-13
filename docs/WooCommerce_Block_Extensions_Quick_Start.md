# WooCommerce Block Extensions - Quick Start Guide

## What's Implemented

Three powerful enhancements for WooCommerce Gutenberg blocks:

### 1. Product Collection - Responsive Controls ✅
Configure different layouts for each device type:
- **Desktop**: 4 columns, 8 products (default)
- **Tablet**: 3 columns, 6 products (default)
- **Mobile**: 2 columns, 4 products (default)

### 2. Product Image - Hover Swap ✅
Show second gallery image on mouse hover for better product preview.

### 3. Product Image - Wishlist Button ✅
Add wishlist button with Blocksy integration and configurable position.

## How to Use

### In Block Editor (Gutenberg)

#### Product Collection Block
1. Add **Product Collection** block to your page
2. In the right sidebar, scroll to **"Responsive Settings"** panel
3. Toggle **"Enable Responsive Layout"**
4. Adjust columns and product counts for each device:
   - Desktop Columns (1-6)
   - Tablet Columns (1-4)
   - Mobile Columns (1-3)
   - Desktop Products (1-24)
   - Tablet Products (1-18)
   - Mobile Products (1-12)

#### Product Image Block
1. Add **Product Image** block (usually inside Product Collection)
2. In the right sidebar, scroll to **"Image Enhancements"** panel
3. Toggle **"Enable Hover Image"** to show second image on hover
4. Toggle **"Show Wishlist Button"** to add wishlist functionality
5. Choose **"Wishlist Button Position"**:
   - Top Left
   - Top Right (default)
   - Bottom Left
   - Bottom Right

## File Locations

```
/includes/customization/wc-blocks/
├── loader.php                          # Main loader
├── product-collection-responsive.php   # Responsive extension
├── product-image-enhancements.php      # Image enhancements
└── wishlist-ajax-handler.php           # Wishlist AJAX

/assets/wc-blocks/
├── product-collection-responsive-editor.js
├── product-collection-responsive-frontend.js
├── product-collection-responsive.css
├── product-image-enhancement-editor.js
├── product-image-enhancement-frontend.js
└── product-image-enhancement.css
```

## Features

### Responsive Product Collection
- ✅ Automatic responsive behavior
- ✅ Different column counts per device
- ✅ Different product counts per device
- ✅ Smooth transitions on resize
- ✅ CSS Grid-based layout
- ✅ Works with existing WooCommerce blocks

### Hover Image Swap
- ✅ Shows second gallery image on hover
- ✅ Smooth fade transition
- ✅ Preloads images for performance
- ✅ Restores original on mouse leave
- ✅ Works with any product with gallery images

### Wishlist Button
- ✅ Integrates with Blocksy wishlist
- ✅ Configurable button position
- ✅ Visual feedback (added/not added)
- ✅ AJAX-powered (no page reload)
- ✅ Fallback to cookie-based storage
- ✅ Mobile-friendly (always visible on mobile)
- ✅ Accessible (keyboard navigation support)

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- WooCommerce Blocks (bundled with WooCommerce 8.0+)
- Blocksy theme (for wishlist integration)
- Modern browser with JavaScript enabled

## Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Customization

### Change Breakpoints

Edit `/assets/wc-blocks/product-collection-responsive-frontend.js`:

```javascript
this.breakpoints = {
    desktop: 1024,  // Change this
    tablet: 768     // Change this
};
```

### Change Wishlist Button Style

Edit `/assets/wc-blocks/product-image-enhancement.css`:

```css
.wc-wishlist-button {
    width: 40px;           /* Button size */
    height: 40px;
    background: rgba(255, 255, 255, 0.95);
}

.wc-wishlist-button.wc-wishlist-added {
    background: #e74c3c;   /* Color when added */
}
```

### Change Hover Effect

Edit `/assets/wc-blocks/product-image-enhancement.css`:

```css
.wc-hover-image-enabled:hover img {
    transform: scale(1.05);  /* Zoom on hover */
}
```

## Troubleshooting

### Settings Panel Not Showing
1. Clear browser cache
2. Check browser console for errors
3. Ensure WooCommerce Blocks is active

### Hover Image Not Working
1. Ensure product has gallery images
2. Check "Enable Hover Image" is toggled on
3. Check browser console for errors

### Wishlist Button Not Working
1. Check browser console for AJAX errors
2. Verify Blocksy wishlist extension is active
3. Check nonce is being generated

## Next Steps

1. **Test on Different Devices**: Check responsive behavior on actual devices
2. **Customize Styling**: Adjust colors and sizes to match your theme
3. **Add More Products**: Ensure products have gallery images for hover effect
4. **Monitor Performance**: Check page load times and optimize if needed

## Support

For detailed documentation, see:
- `/docs/WooCommerce_Block_Extensions_Implementation.md`
- `/docs/WooCommerce_Block_Extensions_Technical_Documentation.md`

For issues:
1. Enable WordPress debug mode
2. Check browser console
3. Review error logs
4. Contact development team

## Version

**Current Version**: 1.0.0

**Last Updated**: 2025-10-13

