# Fluid Checkout Customizer Guide

## Overview

The Fluid Checkout Customizer integration provides comprehensive styling options for Fluid Checkout elements through the WordPress Customizer interface. This allows administrators to customize the appearance of the checkout page without writing any code.

## Accessing the Customizer

1. Log in to WordPress admin
2. Navigate to **Appearance > Customize**
3. Look for the **Fluid Checkout Styling** panel
4. Click to expand and access all styling sections

## Available Styling Sections

### 1. General Colors

Control the primary color scheme for the checkout page:

- **Primary Color**: Main brand color used throughout the checkout
- **Secondary Color**: Accent color for secondary elements
- **Body Text Color**: Default text color for body content
- **Heading Color**: Color for headings and titles
- **Link Color**: Color for clickable links
- **Link Hover Color**: Color when hovering over links
- **Content Background**: Background color for content areas
- **Border Color**: Default border color for elements

### 2. Typography Sections

Fine-tune typography for different element types:

#### Heading Typography
- Font Family
- Font Size
- Font Color
- Font Weight (100-900)

#### Body Text Typography
- Font Family
- Font Size
- Font Color
- Font Weight (100-900)

#### Form Label Typography
- Font Family
- Font Size
- Font Color
- Font Weight (100-900)

#### Placeholder Typography
- Font Family
- Font Size
- Font Color
- Font Weight (100-900)

#### Button Typography
- Font Family
- Font Size
- Font Color
- Font Weight (100-900)

### 3. Form Elements

Customize the appearance of form inputs:

- **Input Background Color**: Background color for text inputs
- **Input Border Color**: Border color for inputs
- **Input Text Color**: Text color inside inputs
- **Input Focus Border Color**: Border color when input is focused
- **Input Padding**: Internal spacing within inputs
- **Input Border Radius**: Rounded corners for inputs

### 4. Buttons

Complete button styling control:

- **Primary Button Background**: Background color for primary buttons
- **Primary Button Text**: Text color for primary buttons
- **Primary Button Hover Background**: Background color on hover
- **Primary Button Hover Text**: Text color on hover
- **Button Padding Top**: Top padding
- **Button Padding Right**: Right padding
- **Button Padding Bottom**: Bottom padding
- **Button Padding Left**: Left padding
- **Button Border Radius**: Rounded corners for buttons

### 5. Spacing

Control spacing and layout:

- **Section Padding Top**: Top padding for checkout sections
- **Section Padding Right**: Right padding for checkout sections
- **Section Padding Bottom**: Bottom padding for checkout sections
- **Section Padding Left**: Left padding for checkout sections
- **Section Margin Bottom**: Space between checkout sections
- **Field Gap**: Space between form fields

### 6. Borders

Customize borders for checkout sections:

- **Section Border Width**: Thickness of borders (e.g., 1px, 2px)
- **Section Border Color**: Color of borders
- **Section Border Style**: Style of borders (None, Solid, Dashed, Dotted, Double)
- **Section Border Radius**: Rounded corners for sections

## CSS Units

All size-related fields accept standard CSS units:

- **px** (pixels): e.g., `16px`, `24px`
- **rem** (relative to root): e.g., `1rem`, `1.5rem`
- **em** (relative to parent): e.g., `1em`, `1.2em`
- **%** (percentage): e.g., `100%`, `50%`

If you enter a number without a unit, `px` will be automatically added.

## Font Families

You can use any web-safe font or Google Fonts. Examples:

- System fonts: `-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`
- Google Fonts: `"Roboto", sans-serif` (make sure to load the font separately)
- Web-safe fonts: `Arial, sans-serif`, `Georgia, serif`, `"Courier New", monospace`

## Live Preview

Most settings support live preview using the `postMessage` transport, meaning changes appear instantly in the customizer preview without refreshing the page.

## CSS Variables Integration

The customizer integrates with Fluid Checkout's CSS variable system, allowing for seamless theme integration. The following CSS variables are controlled:

- `--fluidtheme--color--primary`
- `--fluidtheme--color--secondary`
- `--fluidtheme--color--body-text`
- `--fluidtheme--color--heading`
- `--fluidtheme--color--link`
- `--fluidtheme--color--link--hover`
- `--fluidtheme--color--content-background`
- `--fluidtheme--color--border`

## Targeted Elements

The customizer applies styles to the following Fluid Checkout elements:

### Headings
- `.woocommerce-checkout h1, h2, h3`
- `.fc-step__title`

### Form Elements
- `.woocommerce-checkout input[type="text"]`
- `.woocommerce-checkout input[type="email"]`
- `.woocommerce-checkout input[type="tel"]`
- `.woocommerce-checkout input[type="password"]`
- `.woocommerce-checkout textarea`
- `.woocommerce-checkout select`

### Buttons
- `.woocommerce-checkout button.button`
- `.woocommerce-checkout .button`
- `.woocommerce-checkout input[type="submit"]`
- `.woocommerce-checkout #place_order`

### Sections
- `.woocommerce-checkout .fc-step`
- `.woocommerce-checkout .fc-cart-section`
- `.woocommerce-checkout .woocommerce-checkout-review-order`

## Best Practices

1. **Start with Colors**: Begin by setting your brand colors in the General Colors section
2. **Typography Hierarchy**: Ensure headings are larger and bolder than body text
3. **Contrast**: Maintain sufficient contrast between text and backgrounds for accessibility
4. **Consistency**: Use consistent spacing values throughout
5. **Test Responsively**: Preview changes on different screen sizes
6. **Save Regularly**: Click "Publish" to save your changes

## Troubleshooting

### Changes Not Appearing

1. **Clear Cache**: Clear your browser cache and any WordPress caching plugins
2. **Check Page**: Ensure you're viewing the checkout page
3. **Fluid Checkout Active**: Verify Fluid Checkout plugin is active
4. **Theme Conflicts**: Check for theme CSS that might override customizer styles

### Styles Being Overridden

The customizer uses `!important` declarations to ensure styles are applied. If styles still don't appear:

1. Check browser developer tools to see which styles are being applied
2. Look for more specific CSS selectors in your theme
3. Consider adjusting the CSS specificity in the customizer file if needed

## Technical Details

### File Location
`blaze-blocksy/includes/customization/fluid-checkout-customizer.php`

### Class Name
`Blocksy_Child_Fluid_Checkout_Customizer`

### Hooks Used
- `customize_register`: Registers all customizer settings and controls
- `customize_preview_init`: Enqueues preview scripts
- `wp_head`: Outputs customizer CSS (priority 999)

### CSS Output
All customizer CSS is output inline in the `<head>` section with the ID `blocksy-fluid-checkout-customizer-css`.

## Extending the Customizer

To add additional styling options:

1. Open `blaze-blocksy/includes/customization/fluid-checkout-customizer.php`
2. Add new settings in the appropriate section registration method
3. Add corresponding CSS output in the relevant output method
4. Follow the existing pattern for consistency

## Support

For issues or questions:

1. Check the WordPress Customizer documentation
2. Review Fluid Checkout documentation
3. Contact the development team

## Changelog

### Version 1.0.0
- Initial release
- General colors section
- Typography sections (5 element types)
- Form elements section
- Buttons section
- Spacing section
- Borders section
- Live preview support
- CSS variables integration

