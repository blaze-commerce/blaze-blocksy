---
title: "My Account Advanced Customization Fields"
description: "Comprehensive customization controls for WooCommerce My Account forms including form elements, footer text, and navigation styling"
category: "module"
last_updated: "2025-01-16"
framework: "wordpress"
domain: "user-management"
layer: "frontend"
tags: [customizer, form-styling, my-account, advanced-controls]
---

# Overview

Extended the WooCommerce My Account customization system with advanced styling controls, converting hardcoded CSS values into customizable fields accessible through the WordPress Customizer. This enhancement provides granular control over form elements, footer text, account navigation, and layout components.

# Usage

## Accessing Advanced Controls

1. Navigate to **Appearance > Customize**
2. Open **My Account Form** panel
3. Access the new sections:
   - **Form Elements** - Checkbox and required field styling
   - **Footer Text** - Desktop and mobile font sizes
   - **Account Navigation** - Border, text, and active colors
   - **Button Styling (Desktop)** - Enhanced with column border radius

## Available Customization Options

### Form Elements Section
- **Checkbox Border Color**: Customize checkbox border appearance
- **Required Field Asterisk Color**: Control the color of required field indicators

### Footer Text Section
- **Desktop Font Size**: Footer text size for desktop devices
- **Mobile Font Size**: Footer text size for mobile devices (responsive)

### Account Navigation Section
- **Navigation Border Color**: Border color for account navigation
- **Navigation Text Color**: Text color for navigation links
- **Active/Hover Background Color**: Background color for active and hovered navigation items

### Button Styling Section (Enhanced)
- **Button Border Radius**: Corner rounding for buttons
- **Column Border Radius**: Corner rounding for login/register columns (all templates)

# Implementation Details

## New Customizer Sections

### Form Elements Section
```php
// Priority: 75
$wp_customize->add_section('blocksy_my_account_form_elements', [
    'title' => 'Form Elements',
    'panel' => 'blocksy_my_account_panel',
    'priority' => 75,
]);
```

### Footer Text Section
```php
// Priority: 76
$wp_customize->add_section('blocksy_my_account_footer_text', [
    'title' => 'Footer Text',
    'panel' => 'blocksy_my_account_panel',
    'priority' => 76,
]);
```

### Account Navigation Section
```php
// Priority: 77
$wp_customize->add_section('blocksy_my_account_navigation', [
    'title' => 'Account Navigation',
    'panel' => 'blocksy_my_account_panel',
    'priority' => 77,
]);
```

## Customizer Settings

| Setting ID | Default Value | Type | Description |
|------------|---------------|------|-------------|
| `blocksy_child_my_account_checkbox_border_color` | `#CDD1D4` | Color | Checkbox border color |
| `blocksy_child_my_account_required_field_color` | `#ff0000` | Color | Required field asterisk color |
| `blocksy_child_my_account_footer_font_size_desktop` | `14px` | CSS Unit | Desktop footer font size |
| `blocksy_child_my_account_footer_font_size_mobile` | `12px` | CSS Unit | Mobile footer font size |
| `blocksy_child_my_account_nav_border_color` | `#CDD1D4` | Color | Navigation border color |
| `blocksy_child_my_account_nav_text_color` | `#242424` | Color | Navigation text color |
| `blocksy_child_my_account_nav_active_color` | `#be252f` | Color | Navigation active/hover color |
| `blocksy_child_my_account_column_border_radius` | `12px` | CSS Unit | Column border radius (all templates) |

## CSS Selectors and Properties

### Form Elements
```css
/* Checkbox border */
.blaze-login-register input.woocommerce-form__input-checkbox {
    border-color: {checkbox_border_color} !important;
}

/* Required field asterisk */
.blaze-login-register span .required,
.blaze-login-register.template1 span.required {
    color: {required_field_color} !important;
}
```

### Footer Text
```css
/* Desktop footer text */
.blaze-login-register .login-form-footer span,
.blaze-login-register .login-form-footer a,
.blaze-login-register .woocommerce-privacy-policy-text p,
.blaze-login-register .woocommerce-privacy-policy-text p a {
    font-size: {footer_font_size_desktop} !important;
}

/* Mobile footer text */
@media (max-width: 768px) {
    /* Same selectors with mobile font size */
}
```

### Account Navigation
```css
/* Navigation border */
.blz-my_account .ct-acount-nav {
    border: 1px solid {nav_border_color} !important;
}

/* Navigation text */
.blz-my_account p, .blz-my_account a {
    color: {nav_text_color};
}

/* Active/hover states */
.blz-my_account ul li.is-active,
.blz-my_account ul li:hover {
    --account-nav-background-active-color: {nav_active_color};
}
```

### Column Border Radius
```css
/* All template columns */
.blaze-column {
    border-radius: {column_border_radius};
}
```

## Live Preview JavaScript

### Update Functions
- `updateFormElementColor(colorType, value)` - Form element colors
- `updateFooterTextStyle(device, value)` - Footer text styling
- `updateAccountNavColor(colorType, value)` - Navigation colors
- `updateColumnBorderRadius()` - Column border radius

### Event Bindings
- Real-time preview for all new controls
- Responsive handling for mobile footer text
- Template-aware updates

# Parameters

## Form Elements
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| checkbox_border_color | string | '#CDD1D4' | Hex color for checkbox borders |
| required_field_color | string | '#ff0000' | Hex color for required field asterisks |

## Footer Text
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| footer_font_size_desktop | string | '14px' | CSS font size for desktop |
| footer_font_size_mobile | string | '12px' | CSS font size for mobile |

## Account Navigation
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| nav_border_color | string | '#CDD1D4' | Hex color for navigation border |
| nav_text_color | string | '#242424' | Hex color for navigation text |
| nav_active_color | string | '#be252f' | Hex color for active/hover states |

## Layout
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| column_border_radius | string | '12px' | CSS border radius for columns (all templates) |

# Returns

The feature returns dynamically generated CSS that applies the customized styling to the appropriate elements based on the selected template and device.

# Dependencies

- WordPress Customizer API
- WooCommerce My Account system
- Existing my account customization framework
- jQuery (for live preview)
- CSS custom properties support (for navigation colors)

# Testing

## Manual Testing Steps

1. **Access Customizer Controls**:
   ```
   WordPress Admin > Appearance > Customize > My Account Form
   ```

2. **Test Form Elements**:
   - Change checkbox border color
   - Modify required field asterisk color
   - Verify live preview updates

3. **Test Footer Text**:
   - Adjust desktop font size
   - Modify mobile font size
   - Check responsive behavior

4. **Test Account Navigation**:
   - Change border color
   - Modify text color
   - Update active/hover color

5. **Test Column Border Radius**:
   - Adjust column border radius
   - Switch between templates to verify it applies to all templates

## Expected Results

- All controls provide instant live preview
- Settings persist after saving
- Responsive controls work correctly
- Template-specific styles apply appropriately
- No conflicts with existing functionality

# Migration from Hardcoded Values

The implementation automatically migrates from hardcoded CSS values to customizer-controlled values:

## Before (Hardcoded)
```css
.blaze-login-register input.woocommerce-form__input-checkbox {
    border-color: #CDD1D4 !important;
}
```

## After (Customizable)
```php
$checkbox_border_color = get_theme_mod('blocksy_child_my_account_checkbox_border_color', '#CDD1D4');
$css .= "border-color: {$checkbox_border_color} !important;";
```

# Changelog

- **Added**: Form Elements customization section
- **Added**: Footer Text responsive font size controls
- **Added**: Account Navigation color customization
- **Enhanced**: Button Styling section with column border radius
- **Improved**: Live preview for all new controls
- **Migrated**: Hardcoded CSS values to customizer controls
- **Updated**: Documentation with comprehensive usage guide
