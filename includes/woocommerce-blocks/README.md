# WooCommerce Block Extensions

Extends WooCommerce Gutenberg blocks with responsive features and enhancements.

## Quick Start

### Product Collection - Responsive Layout

1. Add "Product Collection" block to your page
2. In block settings → "Responsive Settings" → Enable "Responsive Layout"
3. Configure columns and product counts for each device:
   - **Desktop**: 4 columns, 8 products (example)
   - **Tablet**: 3 columns, 6 products (example)
   - **Mobile**: 2 columns, 4 products (example)

### Product Image - Hover & Wishlist

1. Select "Product Image" block (inside Product Collection)
2. In block settings → "Image Enhancements":
   - Enable "Hover Image" to show second image on hover
   - Enable "Show Wishlist Button" to add wishlist functionality
   - Choose button position (top-right, top-left, etc.)

## Features

### ✨ Responsive Product Collection
- Different column layouts per device
- Different product counts per device
- Automatic responsive adjustment
- Smooth transitions

### 🖼️ Enhanced Product Images
- Hover image swap (shows gallery image)
- Wishlist button overlay
- Blocksy wishlist integration
- Customizable button position

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- Blocksy Theme
- Blocksy Companion Plugin (for wishlist)

## File Structure

```
includes/woocommerce-blocks/
├── wc-block-extensions.php              # Main loader
├── includes/
│   ├── class-product-collection-extension.php
│   └── class-product-image-extension.php
└── assets/
    ├── js/
    │   ├── product-collection-extension.js
    │   ├── product-collection-frontend.js
    │   ├── product-image-extension.js
    │   └── product-image-frontend.js
    └── css/
        ├── frontend.css
        └── editor.css
```

## Technical Details

### Responsive Breakpoints
- **Mobile**: < 768px
- **Tablet**: 768px - 1023px
- **Desktop**: ≥ 1024px

### Blocksy Integration
Uses Blocksy's wishlist API:
```php
blc_get_ext('woocommerce-extra')->get_wish_list()
```

### AJAX Endpoints
- `wc_block_toggle_wishlist` - Toggle product in wishlist

## Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers
- ⚠️ IE11 (limited support)

## Documentation

See [WooCommerce_Block_Extensions_Implementation_Guide.md](../../docs/WooCommerce_Block_Extensions_Implementation_Guide.md) for complete documentation.

## Support

For issues or questions, refer to the troubleshooting section in the implementation guide.

---

**Version**: 1.0.0  
**Author**: Blaze Commerce Team

