# Fluid Checkout Customizer - Reset to Default Feature

## Overview

This document describes the "Reset to Default" feature added to the Fluid Checkout Customizer panel in the Blocksy child theme.

## Feature Description

The reset feature allows users to reset all Fluid Checkout styling options back to their default values with a single click. This is useful when:
- Testing different styling configurations
- Reverting unwanted changes
- Starting fresh with default styles
- Troubleshooting styling issues

## Implementation Details

### Files Modified
- **File**: `blaze-blocksy/includes/customization/fluid-checkout-customizer.php`
- **Lines Added**: ~155 lines
- **Date**: 2024-11-08

### Components Added

#### 1. Reset Section
- **Location**: Top of the Fluid Checkout Styling panel (priority 5)
- **Section ID**: `blocksy_fc_reset`
- **Title**: "Reset Settings"
- **Description**: "Reset all Fluid Checkout styling options to their default values."

#### 2. Custom Control Class
- **Class Name**: `Blocksy_FC_Reset_Control`
- **Extends**: `WP_Customize_Control`
- **Type**: `blocksy_fc_reset_button`
- **Renders**: A full-width button with warning message

#### 3. AJAX Handler
- **Action**: `blocksy_fc_reset_settings`
- **Method**: `ajax_reset_settings()`
- **Security**: Nonce verification + capability check
- **Functionality**: Removes all theme mods starting with `blocksy_fc_`

#### 4. JavaScript Handler
- **Method**: `enqueue_reset_button_script()`
- **Features**:
  - Confirmation dialog before reset
  - Loading state during reset
  - Success/error feedback
  - Automatic page reload after reset

## User Experience

### Reset Process
1. User navigates to **Appearance → Customize → Fluid Checkout Styling → Reset Settings**
2. User clicks the **"Reset All Styles to Default"** button
3. Confirmation dialog appears: "Are you sure you want to reset all Fluid Checkout styling options to their default values? This action cannot be undone."
4. If confirmed:
   - Button shows "Resetting..." state
   - AJAX request sent to server
   - All `blocksy_fc_*` theme mods are removed
   - Success message displayed
   - Customizer reloads automatically after 1 second
5. If cancelled: No action taken

### Visual Elements
- **Button**: Full-width, secondary style, bold text
- **Warning**: Red italic text with warning emoji (⚠️)
- **Loading State**: Button disabled with "Resetting..." text
- **Success State**: Green background with "Reset Complete!" text

## Security Features

### Nonce Verification
```php
wp_verify_nonce( $_POST['nonce'], 'blocksy_fc_reset_nonce' )
```

### Capability Check
```php
current_user_can( 'edit_theme_options' )
```

### Sanitization
- All inputs sanitized
- Only theme mods with `blocksy_fc_` prefix are affected
- No direct database queries

## Settings Affected

The reset feature removes ALL theme mods starting with `blocksy_fc_`, including:

### General Colors (8 settings)
- `blocksy_fc_primary_color`
- `blocksy_fc_secondary_color`
- `blocksy_fc_body_text_color`
- `blocksy_fc_heading_color`
- `blocksy_fc_link_color`
- `blocksy_fc_link_hover_color`
- `blocksy_fc_content_background`
- `blocksy_fc_border_color`

### Typography (5 elements × 6 properties = 30 settings)
Elements: heading, body, label, placeholder, button
Properties: font_family, font_size, font_weight, line_height, letter_spacing, text_transform

### Form Elements (10 settings)
- Input background, text, border colors
- Input padding, border radius
- Focus border color
- Checkbox/radio colors

### Buttons (10 settings)
- Background, text, border colors
- Hover states
- Padding, border radius

### Spacing (6 settings)
- Section padding (top, right, bottom, left)
- Section margin bottom
- Field gap

### Borders (3 settings)
- Section border width
- Section border radius
- Section border style

**Total**: ~67 individual settings

## Code Structure

### Constructor Additions
```php
add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_reset_button_script' ) );
add_action( 'wp_ajax_blocksy_fc_reset_settings', array( $this, 'ajax_reset_settings' ) );
```

### Method Flow
1. `register_reset_section()` - Registers section and control
2. `enqueue_reset_button_script()` - Adds JavaScript handler
3. `ajax_reset_settings()` - Processes reset request

## Testing Checklist

- [x] Reset button appears in customizer
- [x] Confirmation dialog works
- [x] AJAX request succeeds
- [x] All settings are removed
- [x] Customizer reloads after reset
- [x] Default values are restored
- [x] Live preview updates correctly
- [x] No JavaScript errors
- [x] Security checks pass
- [x] Works with Kinsta cache

## Deployment

### Local to Server
```bash
# Upload modified file via SCP
scp -P 18705 blaze-blocksy/includes/customization/fluid-checkout-customizer.php \
  dancewearcouk@35.198.155.162:/public/wp-content/themes/blaze-blocksy/includes/customization/

# Clear Kinsta cache
# Via WordPress admin: Kinsta Cache → Clear All Cache
```

### Verification Steps
1. Access customizer: https://cart.dancewear.blz.au/wp-admin/customize.php
2. Navigate to Fluid Checkout Styling panel
3. Verify "Reset Settings" section appears at the top
4. Click reset button and confirm functionality
5. Verify all settings are reset to defaults
6. Check browser console for errors

## Troubleshooting

### Reset Button Not Appearing
- Clear browser cache
- Clear Kinsta cache
- Verify Fluid Checkout plugin is active
- Check PHP error logs

### AJAX Request Fails
- Verify nonce is being generated correctly
- Check user has `edit_theme_options` capability
- Review server error logs
- Ensure AJAX URL is correct

### Settings Not Resetting
- Verify theme mods are using `blocksy_fc_` prefix
- Check database for orphaned settings
- Review AJAX response in browser console

## Future Enhancements

Potential improvements for future versions:
- [ ] Selective reset (by section)
- [ ] Export/import settings
- [ ] Reset confirmation with preview
- [ ] Undo functionality
- [ ] Reset history log

## Support

For issues or questions:
- Check WordPress debug log
- Review browser console
- Contact: alan@blazecommerce.io

## Changelog

### Version 1.0.0 - 2024-11-08
- Initial implementation of reset feature
- Added custom control class
- Implemented AJAX handler
- Added JavaScript functionality
- Deployed to cart.dancewear.blz.au

