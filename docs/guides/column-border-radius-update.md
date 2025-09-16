---
title: "Column Border Radius Selector Update"
description: "Updated column border radius to apply to all templates instead of just Template 1"
category: "guide"
last_updated: "2025-01-16"
framework: "wordpress"
domain: "user-management"
layer: "frontend"
tags: [border-radius, css-selector, template-compatibility]
---

# Overview

Updated the column border radius customization to apply to all my account form templates instead of being limited to Template 1 only. This change makes the styling more consistent and flexible across different template layouts.

# Changes Made

## CSS Selector Update

### Before
```css
.blaze-login-register.template1 .blaze-column {
    border-radius: {column_border_radius};
}
```

### After
```css
.blaze-column {
    border-radius: {column_border_radius};
}
```

## Control Label Update

### Before
- **Label**: "Column Border Radius (Template 1)"
- **Description**: "Border radius for login/register columns in Template 1"

### After
- **Label**: "Column Border Radius"
- **Description**: "Border radius for login/register columns in all templates"

# Files Modified

## 1. PHP Customizer File
**File**: `includes/customization/my-account-customizer.php`

### Control Label and Description
```php
$wp_customize->add_control(
    'blocksy_child_my_account_column_border_radius',
    array(
        'label' => __( 'Column Border Radius', 'blocksy-child' ),
        'description' => __( 'Border radius for login/register columns in all templates (e.g., 12px, 1rem)', 'blocksy-child' ),
        'section' => 'blocksy_my_account_spacing',
        'type' => 'text',
    )
);
```

### CSS Generation
```php
// Column border radius (All Templates)
$column_border_radius = get_theme_mod( 'blocksy_child_my_account_column_border_radius', '12px' );

$css .= ".blaze-column {";
$css .= "border-radius: {$column_border_radius};";
$css .= '}';
```

## 2. JavaScript Live Preview
**File**: `assets/js/my-account-customizer-preview.js`

### Updated Selector
```javascript
function updateColumnBorderRadius() {
    var template = wp.customize( 'blocksy_child_my_account_template' )();
    if (template === 'default') {
        return;
    }

    var borderRadius = wp.customize( 'blocksy_child_my_account_column_border_radius' )() || '12px';
    var selector     = '.blaze-column';  // Updated from '.blaze-login-register.template1 .blaze-column'

    updateCSS( selector, 'border-radius', borderRadius );
}
```

## 3. Static CSS File
**File**: `assets/css/my-account.css`

### Updated Comment
```css
/* Column border radius (all templates) - now customizable */
/*
.blaze-column {
    border-radius: 12px;
}
*/
```

## 4. Documentation Updates
Updated all documentation files to reflect the change:
- `docs/features/my-account-advanced-customization.md`
- `docs/features/MY-ACCOUNT-CUSTOMIZATION.md`
- `docs/guides/my-account-customization-quick-reference.md`

# Impact

## Template Compatibility

### Template 1 (Side-by-Side)
- ✅ Still applies border radius to columns
- ✅ Maintains existing functionality
- ✅ No visual changes for existing users

### Template 2 (Centered)
- ✅ Now applies border radius to columns
- ✅ Improved visual consistency
- ✅ Enhanced customization options

### Default WooCommerce
- ✅ No impact (customizer controls don't apply)
- ✅ Maintains standard WooCommerce styling

## Benefits

1. **Consistency**: Border radius now applies uniformly across templates
2. **Flexibility**: Users can customize column appearance regardless of template choice
3. **Simplicity**: Removes template-specific limitations
4. **Future-Proof**: Works with any new templates that use `.blaze-column` class

# Usage

## Accessing the Control

1. Go to **Appearance > Customize**
2. Navigate to **My Account Form > Button Styling (Desktop)**
3. Find **Column Border Radius** control
4. Adjust the value (e.g., `0px`, `8px`, `12px`, `1rem`)

## Supported Values

- **Pixels**: `0px`, `8px`, `12px`, `20px`
- **Rem units**: `0.5rem`, `1rem`, `1.5rem`
- **Em units**: `0.5em`, `1em`
- **Percentage**: `50%` (for circular corners)

## Live Preview

- Changes appear instantly in the customizer
- Works with all templates that have `.blaze-column` elements
- No page refresh required

# Testing

## Verification Steps

1. **Template 1 Testing**:
   - Select Template 1
   - Adjust column border radius
   - Verify columns have rounded corners

2. **Template 2 Testing**:
   - Switch to Template 2
   - Verify border radius applies to columns
   - Confirm live preview works

3. **Cross-Template Testing**:
   - Switch between templates
   - Verify consistent border radius application
   - Check that settings persist

## Expected Results

- Border radius applies to all templates with `.blaze-column` elements
- Live preview updates immediately
- Settings save and persist correctly
- No conflicts with existing functionality

# Backward Compatibility

- ✅ Existing settings are preserved
- ✅ Default value remains `12px`
- ✅ No breaking changes for current users
- ✅ Template 1 functionality unchanged

# Future Considerations

This change makes the column border radius more flexible and consistent. Future enhancements could include:

- Individual corner radius controls (top-left, top-right, etc.)
- Responsive border radius controls
- Animation transitions for border radius changes
- Integration with other layout controls

# Related Documentation

- [My Account Advanced Customization](../features/my-account-advanced-customization.md)
- [My Account Customization Guide](../features/MY-ACCOUNT-CUSTOMIZATION.md)
- [Quick Reference Guide](my-account-customization-quick-reference.md)
