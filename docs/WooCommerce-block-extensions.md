# WooCommerce Block Extensions

This directory contains extensions for WooCommerce Gutenberg blocks that add enhanced functionality without modifying core WooCommerce files.

## Features

### 1. Product Collection - Responsive Controls
- Configure different column layouts for desktop, tablet, and mobile
- Set different product counts per device type
- Automatic responsive behavior based on screen size

### 2. Product Image - Enhancements
- **Hover Image Swap**: Show second gallery image on hover
- **Wishlist Button**: Add wishlist functionality with Blocksy integration

## Files

- `loader.php` - Main loader that initializes all extensions
- `product-collection-responsive.php` - Product Collection responsive extension
- `product-image-enhancements.php` - Product Image enhancement extension
- `wishlist-ajax-handler.php` - AJAX handler for wishlist functionality

## Usage

Extensions are automatically loaded via `functions.php`. No additional configuration required.

### In Block Editor

1. **Product Collection Block**:
   - Add Product Collection block
   - Open block settings sidebar
   - Find "Responsive Settings" panel
   - Enable and configure responsive options

2. **Product Image Block**:
   - Add Product Image block (within Product Collection)
   - Open block settings sidebar
   - Find "Image Enhancements" panel
   - Enable hover image and/or wishlist button

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- WooCommerce Blocks (bundled with WooCommerce 8.0+)
- Blocksy theme (for wishlist integration)

## Documentation

See `/docs/WooCommerce_Block_Extensions_Implementation.md` for detailed documentation.

