---
title: "My Account Button Border Radius Feature"
description: "Implementation of customizable border radius for buttons in WooCommerce My Account forms"
category: "module"
last_updated: "2025-01-16"
framework: "wordpress"
domain: "user-management"
layer: "frontend"
tags: [customizer, button-styling, border-radius, my-account]
---

# Overview

Added a new customizable border radius field for buttons in the WooCommerce My Account form customization system. This enhancement allows users to control the corner rounding of login/register buttons through the WordPress Customizer with live preview functionality.

# Usage

## Accessing the Control

1. Navigate to **Appearance > Customize**
2. Open **My Account Form** panel
3. Go to **Button Styling (Desktop)** section
4. Find the **Border Radius** field

## Setting Border Radius

```css
/* Example values */
border-radius: 3px;    /* Default - slight rounding */
border-radius: 0px;    /* Sharp corners */
border-radius: 8px;    /* More rounded */
border-radius: 50px;   /* Pill-shaped buttons */
border-radius: 0.5rem; /* Using rem units */
```

# Implementation Details

## Files Modified

### PHP Customizer Integration
- **File**: `includes/customization/my-account-customizer.php`
- **Changes**:
  - Added border radius setting and control in `register_spacing_section()`
  - Updated section title from "Button Padding (Desktop)" to "Button Styling (Desktop)"
  - Integrated border radius into CSS generation in `generate_desktop_css()`

### JavaScript Live Preview
- **File**: `assets/js/my-account-customizer-preview.js`
- **Changes**:
  - Added `updateButtonBorderRadius()` function
  - Added border radius binding in `initSpacingPreview()`
  - Enabled real-time preview updates

### Documentation
- **File**: `docs/features/MY-ACCOUNT-CUSTOMIZATION.md`
- **Changes**:
  - Added "Button Styling (Desktop)" section
  - Documented border radius and padding controls

## Technical Implementation

### Customizer Setting
```php
$wp_customize->add_setting(
    'blocksy_child_my_account_button_border_radius',
    array(
        'default' => '3px',
        'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
        'transport' => 'postMessage',
    )
);
```

### CSS Generation
```php
$border_radius = get_theme_mod( 'blocksy_child_my_account_button_border_radius', '3px' );
$css .= "border-radius: {$border_radius} !important;";
```

### Live Preview JavaScript
```javascript
function updateButtonBorderRadius() {
    var borderRadius = wp.customize( 'blocksy_child_my_account_button_border_radius' )() || '3px';
    var selector = '.blaze-login-register.' + template + ' button, .blaze-login-register.' + template + ' .button';
    updateCSS( selector, 'border-radius', borderRadius );
}
```

# Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| border_radius | string | '3px' | CSS border-radius value with unit |

# Returns

The feature returns dynamically generated CSS that applies the border radius to all buttons within the my account forms.

# Dependencies

- WordPress Customizer API
- WooCommerce My Account templates
- Existing my account customization system
- jQuery (for live preview)

# Testing

## Manual Testing Steps

1. **Access Customizer**:
   ```
   WordPress Admin > Appearance > Customize > My Account Form > Button Styling (Desktop)
   ```

2. **Test Border Radius Control**:
   - Change border radius value (e.g., from 3px to 10px)
   - Verify live preview updates immediately
   - Test various units (px, rem, em, %)

3. **Test Different Templates**:
   - Switch between Template 1 and Template 2
   - Verify border radius applies to both templates
   - Confirm default template is unaffected

4. **Cross-Browser Testing**:
   - Test in Chrome, Firefox, Safari, Edge
   - Verify border radius renders correctly
   - Check mobile responsiveness

## Expected Results

- Border radius changes appear instantly in customizer preview
- Settings persist after saving
- No conflicts with existing button styling
- Maintains accessibility and usability

# Changelog

- **Added**: Border radius customizer control for my account buttons
- **Enhanced**: Button styling section with comprehensive controls
- **Improved**: Live preview functionality for button styling
- **Updated**: Documentation with new feature details
