# WooCommerce Login/Register Form Customization Guide

## Overview

This guide covers the WooCommerce login and register form customization system integrated into the Blocksy child theme. The functionality was originally from the Blaze My Account plugin and has been seamlessly integrated to provide custom login/register form templates with extensive customization options while maintaining WooCommerce compatibility and following WordPress best practices.

## File Structure

```
blocksy-child/
├── includes/customization/
│   └── my-account.php                  # Main customization hooks and functions
├── assets/css/
│   └── my-account.css                  # My account page specific styles
├── assets/js/
│   ├── my-account.js                   # Interactive enhancements
│   └── my-account-admin.js             # Admin interface functionality
├── woocommerce/myaccount/
│   ├── template1/
│   │   └── form-login.php              # Side by side login/register
│   └── template2/
│       └── form-login.php              # Centered login/register with toggle
└── docs/
    └── MY-ACCOUNT-CUSTOMIZATION.md     # This documentation
```

## Features

### Template Designs

1. **Template 1 - Side by Side Layout**
   - Login and register forms displayed side by side
   - Professional business-style layout
   - Responsive design that stacks on mobile

2. **Template 2 - Centered Layout**
   - Centered login form with toggle to register
   - Modern, clean design
   - Interactive form switching

3. **Default WooCommerce**
   - Standard WooCommerce login/register forms
   - No custom styling applied
   - Fallback option

### Customization Options

#### Typography Settings
- **Heading Fonts**: Family, size, color, weight, text transform
- **Label Fonts**: Family, size, color, weight
- **Input Fonts**: Family, size, color
- **Button Fonts**: Family, size, color

#### Color Customization
- **Input Colors**: Background, border, text
- **Button Colors**: Background, hover background, text
- **Heading Colors**: Text color
- **Label Colors**: Text color

#### Button Styling (Desktop)
- **Padding Controls**: Individual top, right, bottom, left padding
- **Border Radius**: Customizable button corner rounding
- **Column Border Radius**: Column corner rounding (all templates)
- **Live Preview**: Real-time updates in customizer

#### Form Elements
- **Checkbox Border Color**: Customizable checkbox border appearance
- **Required Field Color**: Control required field asterisk color

#### Footer Text
- **Desktop Font Size**: Footer text size for desktop devices
- **Mobile Font Size**: Responsive footer text size for mobile

#### Account Navigation
- **Border Color**: Navigation border customization
- **Text Color**: Navigation link text color
- **Active/Hover Color**: Background color for active states

#### Layout Options
- **Template Selection**: Choose between layouts
- **Responsive Behavior**: Automatic mobile optimization
- **Accessibility**: WCAG compliant focus states

## Implementation Methods

### 1. Hook-Based Customization (Recommended)

The system uses WordPress hooks and filters for maximum compatibility:

```php
// Template override filter
add_filter( 'woocommerce_locate_template', 'override_my_account_template', 10, 3 );

// Asset loading
add_action( 'wp_enqueue_scripts', 'enqueue_my_account_assets' );

// Dynamic CSS generation
add_action( 'wp_head', 'output_custom_css' );
```

### 2. Template Overrides

WooCommerce templates are overridden using the standard WordPress template hierarchy:

```
wp-content/themes/blocksy-child/woocommerce/myaccount/template1/my-account.php
wp-content/themes/blocksy-child/woocommerce/myaccount/template1/form-login.php
```

### 3. CSS Customization

Base structural styles are in `assets/css/my-account.css` with:
- **Responsive breakpoints**
- **Accessibility enhancements**
- **Theme integration**
- **WooCommerce overrides**

### 4. JavaScript Enhancements

Interactive functionality in `assets/js/my-account.js` includes:
- **Form validation**
- **Template 2 login/register toggle**
- **Mobile navigation**
- **Smooth scrolling**

## Admin Interface

### Accessing Settings

Navigate to **WordPress Admin > My Account Form** to access customization options.

### Available Settings

1. **Template Selection**
   - Default WooCommerce
   - Template 1 (Side by Side)
   - Template 2 (Centered)

2. **Typography Controls**
   - Font family selection
   - Font size with CSS units
   - Font weight options
   - Text transform options

3. **Color Controls**
   - WordPress color picker integration
   - Live preview capabilities
   - Hex color code support

### Settings Storage

All settings are stored as WordPress options with the prefix `blocksy_child_my_account_`:

```php
// Example option names
blocksy_child_my_account_template
blocksy_child_my_account_heading_font
blocksy_child_my_account_heading_font_size
blocksy_child_my_account_heading_font_color
```

## Integration with Existing Theme

### Naming Convention

All functions follow the `blocksy_child_` prefix to maintain consistency:

```php
class Blocksy_Child_Blaze_My_Account
function blocksy_child_enqueue_my_account_assets()
```

### Asset Loading

Assets are loaded conditionally using the same patterns as other theme customizations:

```php
// CSS with file modification time for cache busting
wp_enqueue_style(
    'blocksy-child-my-account-style',
    get_stylesheet_directory_uri() . '/assets/css/my-account.css',
    array(),
    filemtime( $css_file ),
    'all'
);
```

### Theme Compatibility

The system integrates with Blocksy theme features:
- Respects theme color schemes
- Uses theme typography when appropriate
- Maintains theme responsive behavior
- Preserves theme accessibility features

## Customization Examples

### Adding Custom Styles

```css
/* Custom styles in assets/css/my-account.css */
.blaze-login-register.template1 .custom-element {
    background: #f5f5f5;
    border-radius: 8px;
    padding: 20px;
}
```

### Modifying Templates

```php
// Custom template modifications in woocommerce/myaccount/template1/my-account.php
<div class="custom-welcome-message">
    <h2>Welcome to Our Store!</h2>
    <p>Manage your account and orders below.</p>
</div>
```

### Adding JavaScript Functionality

```javascript
// Custom functionality in assets/js/my-account.js
function initCustomFeature() {
    $('.custom-button').on('click', function() {
        // Custom functionality
    });
}
```

## Troubleshooting

### Debug Mode

Enable debug mode by adding `?blaze_debug=1` to any my-account page URL (admin users only):

```
https://yoursite.com/my-account/?blaze_debug=1
```

This displays a debug panel showing:
- Selected template
- Asset loading status
- Page detection results
- File existence checks

### Common Issues

#### Templates Not Loading
1. Check file permissions on template files
2. Verify template selection in admin settings
3. Clear any caching plugins
4. Check for PHP errors in error logs

#### Styles Not Applying
1. Verify CSS file exists and is readable
2. Check for conflicting theme styles
3. Ensure proper asset enqueueing
4. Test with browser developer tools

#### JavaScript Not Working
1. Check browser console for errors
2. Verify jQuery is loaded
3. Ensure proper script dependencies
4. Test JavaScript functionality step by step

### Performance Optimization

#### CSS Optimization
- Base CSS contains only structural styles
- Visual styles are generated dynamically
- Conditional loading based on page type
- File modification time used for cache busting

#### JavaScript Optimization
- Scripts loaded only on relevant pages
- Minimal DOM manipulation
- Event delegation for better performance
- Graceful degradation for accessibility

## Migration from Plugin

### Automatic Migration

The integration maintains compatibility with existing plugin settings:

1. **Settings Migration**: Option names updated with `blocksy_child_` prefix
2. **Template Compatibility**: Existing templates work without modification
3. **Asset Loading**: Same conditional loading logic preserved
4. **Admin Interface**: Identical functionality with theme integration

### Manual Steps Required

1. **Deactivate Plugin**: Safely deactivate the Blaze My Account plugin
2. **Verify Settings**: Check that all customizations are preserved
3. **Test Functionality**: Ensure all features work as expected
4. **Clear Cache**: Clear any caching to see changes

## Security Considerations

### Input Sanitization

All user inputs are properly sanitized:

```php
// Example sanitization
$heading_font = get_option( 'blocksy_child_my_account_heading_font',
    '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' );
echo esc_attr( $heading_font );
```

### Capability Checks

Admin functionality requires proper capabilities:

```php
// Admin menu access
add_menu_page(
    'My Account Form',
    'My Account Form',
    'manage_options',  // Requires admin capability
    'blocksy-child-my-account',
    array( $this, 'display_admin_page' )
);
```

### Nonce Verification

Forms include nonce verification for security:

```php
// Settings form protection
settings_fields( 'blocksy_child_my_account_settings' );
wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' );
```

## Maintenance

### Regular Updates
- Monitor WordPress and WooCommerce updates for compatibility
- Test functionality after theme updates
- Update version numbers when making changes
- Maintain documentation for any customizations

### Backup Recommendations
- Backup customization files before updates
- Export settings before major changes
- Test changes on staging environment first
- Keep documentation of custom modifications

## Conclusion

The integrated my-account customization system provides powerful template and styling options while maintaining the reliability and compatibility of the Blocksy child theme architecture. The modular approach ensures easy maintenance and future extensibility.
