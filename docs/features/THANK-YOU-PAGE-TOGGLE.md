# Thank You Page Toggle Feature

## Overview

This feature adds a toggle option in the WordPress Customizer to enable or disable the custom Blaze Commerce thank you page design. When disabled, the site will fall back to the default WooCommerce thank you page.

## Location

The toggle option is located in:
**WordPress Admin → Appearance → Customize → WooCommerce → General → Enable Custom Thank You Page**

## Functionality

### When Enabled (Default)
- Uses the custom Blaze Commerce thank you page design
- Loads custom CSS and JavaScript assets
- Hides default WooCommerce elements
- Provides enhanced order confirmation experience

### When Disabled
- Falls back to default WooCommerce thank you page
- Does not load custom assets
- Shows standard WooCommerce order details and messages
- Maintains WooCommerce core functionality

## Technical Implementation

### Files Added
- `includes/customization/thank-you-page-customizer.php` - Customizer integration
- `assets/js/thank-you-customizer-preview.js` - Live preview functionality
- `docs/features/THANK-YOU-PAGE-TOGGLE.md` - This documentation

### Files Modified
- `includes/customization/thank-you-page.php` - Added conditional logic
- `functions.php` - Included new customizer file

### Key Functions
- `blocksy_child_is_custom_thank_you_page_enabled()` - Check if toggle is enabled
- `Blocksy_Child_Thank_You_Page_Customizer::is_custom_thank_you_page_enabled()` - Static method for checking toggle state

### WordPress Customizer Integration
- **Setting ID**: `blocksy_child_enable_custom_thank_you_page`
- **Default Value**: `true` (enabled by default)
- **Storage**: WordPress theme modifications (`theme_mod`)
- **Transport**: `refresh` (requires page refresh for changes)

## Usage

### For Developers
```php
// Check if custom thank you page is enabled
if ( blocksy_child_is_custom_thank_you_page_enabled() ) {
    // Custom thank you page logic
} else {
    // Default WooCommerce behavior
}
```

### For Site Administrators
1. Navigate to **Appearance → Customize**
2. Go to **WooCommerce → General**
3. Scroll to the bottom to find **Enable Custom Thank You Page**
4. Toggle the checkbox to enable/disable the feature
5. Click **Publish** to save changes

## Conditional Logic

The following functions now check the toggle state:

1. **`blocksy_child_blaze_commerce_thank_you_content()`**
   - Only executes if toggle is enabled
   - Returns early if disabled

2. **`blocksy_child_hide_default_order_details()`**
   - Only hides default elements if toggle is enabled
   - Preserves WooCommerce defaults when disabled

3. **`blocksy_child_hide_default_thank_you_message()`**
   - Only hides default message if toggle is enabled
   - Returns original message when disabled

4. **`blocksy_child_enqueue_thank_you_assets()`**
   - Only loads custom assets if toggle is enabled
   - Prevents unnecessary asset loading when disabled

## Benefits

- **Flexibility**: Easy switching between custom and default designs
- **Performance**: Assets only load when needed
- **Compatibility**: Maintains WooCommerce core functionality
- **User Experience**: Live preview in customizer
- **Maintenance**: Easy to disable custom features for troubleshooting

## Backward Compatibility

- Default state is enabled to maintain existing functionality
- No breaking changes to existing implementations
- Graceful fallback to WooCommerce defaults

## Testing

To test the implementation:

1. **Enable Toggle**: Verify custom thank you page displays
2. **Disable Toggle**: Verify default WooCommerce page displays
3. **Asset Loading**: Check that custom CSS/JS only loads when enabled
4. **Customizer Preview**: Verify live preview works (requires page refresh)

## Future Enhancements

Potential improvements for future versions:
- Live preview without page refresh
- Additional customization options
- A/B testing integration
- Analytics tracking for conversion optimization
