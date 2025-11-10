# Fluid Checkout Customizer Integration

## Quick Start

The Fluid Checkout Customizer integration provides a comprehensive set of styling options for the Fluid Checkout plugin through the WordPress Customizer interface.

### Accessing the Customizer

1. Log in to WordPress admin
2. Go to **Appearance > Customize**
3. Click on **Fluid Checkout Styling** panel
4. Choose a section to customize

## Features

### ✨ 6 Comprehensive Styling Sections

1. **General Colors** - 8 color controls for primary, secondary, text, links, backgrounds, and borders
2. **Typography** - 5 element types (headings, body, labels, placeholders, buttons) with font family, size, color, and weight
3. **Form Elements** - Complete input styling with background, borders, padding, and focus states
4. **Buttons** - Primary button styling with normal and hover states, plus padding controls
5. **Spacing** - Section padding, margins, and field gaps
6. **Borders** - Border width, color, style, and radius for checkout sections

### 🎨 Total Customization Options

- **8** Color pickers
- **20** Typography controls (4 properties × 5 elements)
- **6** Form element controls
- **9** Button controls
- **6** Spacing controls
- **4** Border controls

**Total: 53 individual customization options**

### 🚀 Advanced Features

- ✅ Live preview support (postMessage transport)
- ✅ CSS variables integration
- ✅ Responsive design
- ✅ WCAG AA accessibility compliance
- ✅ No code required
- ✅ Blocksy theme integration
- ✅ Fluid Checkout plugin integration

## File Structure

```
blaze-blocksy/
├── includes/
│   └── customization/
│       └── fluid-checkout-customizer.php    # Main customizer class
├── assets/
│   └── js/
│       └── fluid-checkout-customizer-preview.js    # Live preview script
├── docs/
│   ├── FLUID_CHECKOUT_README.md             # This file
│   ├── fluid-checkout-customizer-guide.md   # User guide
│   ├── fluid-checkout-element-map.md        # Element reference
│   └── fluid-checkout-deployment-guide.md   # Deployment instructions
└── functions.php                             # Updated to include customizer
```

## Technical Details

### Class: `Blocksy_Child_Fluid_Checkout_Customizer`

**Hooks:**
- `customize_register` - Registers all settings and controls
- `customize_preview_init` - Enqueues preview scripts
- `wp_head` - Outputs customizer CSS (priority 999)

**Methods:**
- `register_customizer_settings()` - Main registration method
- `register_general_colors_section()` - Color controls
- `register_typography_sections()` - Typography controls
- `register_form_elements_section()` - Form styling
- `register_buttons_section()` - Button styling
- `register_spacing_section()` - Spacing controls
- `register_borders_section()` - Border controls
- `output_customizer_css()` - CSS output
- `enqueue_preview_scripts()` - Preview script enqueue

### CSS Selectors Targeted

**Typography:**
- Headings: `.woocommerce-checkout h1, h2, h3, .fc-step__title`
- Body: `.woocommerce-checkout, .woocommerce-checkout p, span`
- Labels: `.woocommerce-checkout label, .form-row label`
- Placeholders: `.woocommerce-checkout input::placeholder, textarea::placeholder`
- Buttons: `.woocommerce-checkout button, .button`

**Form Elements:**
- Inputs: `input[type="text"], input[type="email"], input[type="tel"], input[type="password"]`
- Textareas: `textarea`
- Selects: `select`

**Buttons:**
- Primary: `button.button, .button, input[type="submit"], #place_order`

**Sections:**
- Steps: `.fc-step`
- Cart: `.fc-cart-section`
- Order Review: `.woocommerce-checkout-review-order`

### CSS Variables

The customizer integrates with Fluid Checkout's CSS variable system:

```css
:root {
  --fluidtheme--color--primary
  --fluidtheme--color--secondary
  --fluidtheme--color--body-text
  --fluidtheme--color--heading
  --fluidtheme--color--link
  --fluidtheme--color--link--hover
  --fluidtheme--color--content-background
  --fluidtheme--color--border
}
```

## Usage Examples

### Example 1: Change Primary Color

1. Go to **Appearance > Customize**
2. Click **Fluid Checkout Styling**
3. Click **General Colors**
4. Click on **Primary Color** picker
5. Select your brand color
6. Click **Publish**

### Example 2: Customize Button Styling

1. Go to **Appearance > Customize**
2. Click **Fluid Checkout Styling**
3. Click **Buttons**
4. Set **Primary Button Background** to your brand color
5. Set **Primary Button Text** to white
6. Set **Primary Button Hover Background** to a darker shade
7. Adjust padding as needed
8. Click **Publish**

### Example 3: Adjust Typography

1. Go to **Appearance > Customize**
2. Click **Fluid Checkout Styling**
3. Click **Heading Typography**
4. Set **Font Family** to your brand font
5. Set **Font Weight** to 600 (Semi Bold)
6. Set **Font Size** to 24px
7. Click **Publish**

## Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- **CSS Output**: Inline in `<head>` (minimal impact)
- **JavaScript**: Only loaded in customizer preview
- **Page Load**: No additional HTTP requests
- **Caching**: Compatible with all caching plugins

## Compatibility

- **WordPress**: 5.8+
- **WooCommerce**: 6.0+
- **Fluid Checkout**: 3.0+
- **Blocksy Theme**: 1.8+
- **PHP**: 7.4+

## Accessibility

All customizer controls follow WCAG AA guidelines:

- ✅ Proper labels for all controls
- ✅ Descriptive help text
- ✅ Keyboard navigation support
- ✅ Screen reader compatible
- ✅ Color contrast recommendations

## Best Practices

### Colors
- Maintain sufficient contrast (4.5:1 for text, 3:1 for UI)
- Use consistent color palette
- Test in dark mode if applicable

### Typography
- Use web-safe fonts or load Google Fonts separately
- Maintain hierarchy (headings larger than body)
- Ensure readability (minimum 14px for body text)

### Spacing
- Use consistent spacing values
- Follow 8px grid system when possible
- Test on mobile devices

### Borders
- Keep border widths subtle (1-2px)
- Use border radius consistently
- Match border colors to overall design

## Troubleshooting

### Changes Not Appearing

1. Clear WordPress cache
2. Clear browser cache
3. Verify you're on the checkout page
4. Check that Fluid Checkout plugin is active

### Live Preview Not Working

1. Check browser console for errors
2. Verify JavaScript file is loaded
3. Ensure jQuery is available
4. Try refreshing the customizer

### Styles Being Overridden

1. Check for theme CSS conflicts
2. Verify CSS specificity
3. Use browser dev tools to inspect elements
4. Check for caching issues

## Support & Documentation

- **User Guide**: [fluid-checkout-customizer-guide.md](./fluid-checkout-customizer-guide.md)
- **Element Map**: [fluid-checkout-element-map.md](./fluid-checkout-element-map.md)
- **Deployment**: [fluid-checkout-deployment-guide.md](./fluid-checkout-deployment-guide.md)

## Changelog

### Version 1.0.0 (2024)
- Initial release
- 6 styling sections
- 53 customization options
- Live preview support
- CSS variables integration
- Comprehensive documentation

## Future Enhancements

Planned features for future versions:

- [ ] Progress bar styling
- [ ] Message styling (errors, success, info)
- [ ] Advanced form element styling (checkboxes, radio buttons)
- [ ] Responsive spacing controls
- [ ] Animation controls
- [ ] Color presets
- [ ] Import/export settings
- [ ] Reset to defaults button

## Credits

**Developed by**: BlazeCommerce Development Team  
**For**: Dancewear Live  
**Theme**: Blocksy Child Theme  
**Plugin**: Fluid Checkout for WooCommerce  

## License

This customizer integration is part of the BlazeCommerce child theme and follows the same license as the parent Blocksy theme.

---

**Last Updated**: 2024  
**Version**: 1.0.0  
**Status**: Production Ready

