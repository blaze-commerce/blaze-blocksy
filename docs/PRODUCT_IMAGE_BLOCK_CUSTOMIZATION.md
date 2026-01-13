# WooCommerce Product Image Block Customization

## Overview

This document describes the implementation of hover image effect and wishlist button functionality for WooCommerce Product Image blocks in product listings (shop, category, tag pages).

**Features:**
1. **Hover Image Effect**: Displays the second product image when hovering over the main image with smooth transition
2. **Wishlist Button**: Floating add to wishlist button positioned at bottom-right of product images

## File Structure

```
blocksy-child/
├── includes/customization/
│   └── product-image-block.php          # Main PHP class for customization
├── assets/css/
│   └── product-image-block.css          # Styles for hover effect & wishlist button
├── assets/js/
│   └── product-image-block.js           # JavaScript for interactions
└── docs/
    └── PRODUCT_IMAGE_BLOCK_CUSTOMIZATION.md  # This documentation
```

## Technical Implementation

### 1. PHP Implementation

**File**: `includes/customization/product-image-block.php`

**Class**: `WooCommerce_Product_Image_Block_Enhancement`

#### Key Methods:

- `enhance_product_image_block()`: Filters WooCommerce product image block output
- `get_hover_image_data()`: Retrieves second product image from gallery
- `get_wishlist_button_html()`: Generates wishlist button HTML
- `is_product_in_wishlist()`: Checks if product is already in wishlist
- `enqueue_assets()`: Loads CSS and JavaScript files

#### How It Works:

1. Hooks into `render_block_woocommerce/product-image` filter
2. Uses `WP_HTML_Tag_Processor` to manipulate block HTML
3. Adds CSS classes and data attributes for JavaScript
4. Injects wishlist button HTML into the block
5. Integrates with existing `BlocksyChildWishlistHelper` class

### 2. CSS Implementation

**File**: `assets/css/product-image-block.css`

#### Key Features:

**Hover Image Effect:**
- Smooth opacity and transform transitions (0.3s ease-in-out)
- Scale effect on hover (1.05x zoom)
- Hardware-accelerated animations using `will-change`

**Wishlist Button:**
- Positioned absolutely at bottom-right (10px from edges)
- Circular button (40px × 40px on desktop, 35px on mobile)
- Backdrop blur effect for modern appearance
- Hidden by default, appears on container hover (desktop)
- Always visible on mobile/touch devices
- Active state: red background (#e74c3c) with filled heart icon
- Loading state: spinning animation

**Responsive Design:**
- Desktop: Button appears on hover
- Mobile/Tablet: Button always visible
- Touch devices: Hover effect disabled

**Accessibility:**
- Focus states with outline
- Reduced motion support
- Dark mode support
- Print styles (hides button)

### 3. JavaScript Implementation

**File**: `assets/js/product-image-block.js`

**Class**: `ProductImageBlockEnhancement`

#### Key Methods:

**Hover Image Functionality:**
- `setupHoverImages()`: Initializes all hover-enabled images
- `initHoverImage()`: Sets up hover behavior for single image
- `preloadImage()`: Preloads hover image for better performance

**Wishlist Functionality:**
- `setupWishlistButtons()`: Event delegation for wishlist buttons
- `handleWishlistClick()`: AJAX handler for add/remove wishlist
- `updateWishlistButtons()`: Syncs all buttons for same product
- `syncWishlistOffcanvas()`: Updates wishlist offcanvas panel
- `showNotification()`: Displays success/error messages

**Dynamic Content Support:**
- Listens to WooCommerce AJAX events
- Re-initializes on dynamic content load
- Supports infinite scroll and filters

#### AJAX Integration:

Uses existing wishlist system:
- Action: `add_to_wishlist` or `remove_from_wishlist`
- Nonce: `blaze_product_image_block_nonce`
- Triggers: `blazeWishlistUpdated` custom event

### 4. Integration with Existing Wishlist System

The implementation integrates seamlessly with the existing wishlist functionality:

**PHP Integration:**
```php
// Uses existing helper class
BlocksyChildWishlistHelper::get_wishlist_extension()
BlocksyChildWishlistHelper::get_current_wishlist()
```

**JavaScript Integration:**
```javascript
// Triggers events for synchronization
$(document).trigger('blazeWishlistUpdated', {...});
$(document).trigger('blazeRefreshWishlist');
$(document).trigger('blazeUpdateWishlistCounter');
```

## Usage

### Automatic Activation

The customization is automatically applied to all WooCommerce Product Image blocks on:
- Shop page (`is_shop()`)
- Category pages (`is_product_category()`)
- Tag pages (`is_product_tag()`)
- Product taxonomy pages (`is_product_taxonomy()`)
- Single product pages (`is_product()`)

### Requirements

1. **Product Gallery Images**: Products must have at least 2 images (main + gallery) for hover effect
2. **Wishlist Extension**: Blocksy WooCommerce Extra extension with wishlist enabled
3. **WooCommerce Blocks**: Using WooCommerce Product Image block

### Customization

#### CSS Variables

You can customize appearance by overriding CSS variables:

```css
:root {
  --wishlist-button-size: 40px;
  --wishlist-button-size-mobile: 35px;
  --wishlist-button-position-bottom: 10px;
  --wishlist-button-position-right: 10px;
  --wishlist-button-bg: rgba(255, 255, 255, 0.95);
  --wishlist-button-active-bg: #e74c3c;
  --hover-image-transition: 0.3s ease-in-out;
}
```

#### Button Position

To change button position, modify CSS:

```css
/* Top-right position */
.wc-product-image-wishlist-button {
  top: 10px;
  right: 10px;
  bottom: auto;
}

/* Top-left position */
.wc-product-image-wishlist-button {
  top: 10px;
  left: 10px;
  right: auto;
  bottom: auto;
}
```

#### Transition Speed

To adjust hover transition speed:

```css
:root {
  --hover-image-transition: 0.5s ease-in-out; /* Slower */
}
```

## Browser Compatibility

- **Modern Browsers**: Full support (Chrome, Firefox, Safari, Edge)
- **Mobile Browsers**: Full support (iOS Safari, Chrome Mobile)
- **IE11**: Basic support (no backdrop-filter)
- **Touch Devices**: Optimized experience (button always visible)

## Performance Considerations

1. **Image Preloading**: Hover images are preloaded on first interaction
2. **Debounced Events**: Hover events use timeout for smooth transitions
3. **Hardware Acceleration**: CSS transforms use GPU acceleration
4. **Conditional Loading**: Assets only load on relevant pages
5. **Event Delegation**: Efficient event handling for dynamic content

## Accessibility

- **ARIA Labels**: Wishlist button has proper `aria-label`
- **Keyboard Navigation**: Button is keyboard accessible
- **Focus States**: Clear focus indicators
- **Screen Readers**: Descriptive labels for assistive technology
- **Reduced Motion**: Respects `prefers-reduced-motion` setting

## Testing

### Manual Testing Checklist

- [ ] Hover effect works on desktop
- [ ] Second image displays smoothly
- [ ] Original image restores on mouse leave
- [ ] Wishlist button appears on hover (desktop)
- [ ] Wishlist button always visible on mobile
- [ ] Click adds product to wishlist
- [ ] Button shows active state when in wishlist
- [ ] Success notification appears
- [ ] Wishlist counter updates
- [ ] Works with AJAX-loaded products
- [ ] No JavaScript errors in console

### Browser Testing

Test in:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- iOS Safari
- Chrome Mobile

## Troubleshooting

### Hover Image Not Working

**Issue**: Second image doesn't appear on hover

**Solutions**:
1. Verify product has gallery images
2. Check browser console for JavaScript errors
3. Ensure `data-hover-image` attribute is present in HTML
4. Clear browser cache

### Wishlist Button Not Appearing

**Issue**: Wishlist button is not visible

**Solutions**:
1. Check if CSS file is loaded
2. Verify Blocksy WooCommerce Extra extension is active
3. Inspect element to ensure button HTML is injected
4. Check z-index conflicts with other elements

### AJAX Not Working

**Issue**: Clicking wishlist button does nothing

**Solutions**:
1. Check browser console for AJAX errors
2. Verify nonce is valid
3. Ensure wishlist extension is properly configured
4. Check server error logs

## Future Enhancements

Potential improvements for future versions:

1. **Customizer Integration**: Add options to Blocksy Customizer
2. **Multiple Position Options**: Allow position selection via settings
3. **Animation Variants**: Different transition effects
4. **Quick View Integration**: Add quick view button alongside wishlist
5. **Compare Feature**: Add compare button option
6. **Video Support**: Support video in gallery for hover effect

## Changelog

### Version 1.0.0 (2025-01-13)
- Initial implementation
- Hover image effect with smooth transitions
- Wishlist button with floating design
- Integration with existing wishlist system
- Mobile responsive design
- Accessibility features
- Performance optimizations

## Support

For issues or questions:
1. Check this documentation
2. Review browser console for errors
3. Test with default theme to isolate conflicts
4. Check WooCommerce and Blocksy versions

## Credits

- **Developer**: Blaze Commerce Team
- **Framework**: Blocksy Theme
- **Platform**: WordPress + WooCommerce

