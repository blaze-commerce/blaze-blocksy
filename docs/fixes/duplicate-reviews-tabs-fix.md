---
title: "Fix: Duplicate Reviews Tabs on Product Pages"
description: "Solution for duplicate reviews tabs caused by Judge.me integration without plugin activation"
category: "guide"
last_updated: "2025-01-23"
tags: [bugfix, woocommerce, reviews, judgeme]
---

# Fix: Duplicate Reviews Tabs on Product Pages

## Overview

This fix resolves the issue where two reviews tabs appear on WooCommerce product pages:
- WooCommerce native "Reviews (0)" tab
- Custom "Reviews" tab from Judge.me integration

The problem occurred because the theme was unconditionally adding a Judge.me reviews tab even when the Judge.me plugin was not installed or active.

## Root Cause

The `includes/customization/judgeme.php` file was adding a custom reviews tab without checking if the Judge.me plugin was actually available:

```php
// BEFORE: Unconditional tab addition
add_filter( 'woocommerce_product_tabs', function (array $tabs) {
    $tabs['judgeme_tab'] = array(
        'title' => __( 'Reviews', 'textdomain' ),
        'priority' => 50,
        'callback' => 'blaze_blocksy_render_judgeme_tab',
    );
    return $tabs;
} );
```

## Solution

Added conditional checks to only display the Judge.me reviews tab when the plugin is actually available:

```php
// AFTER: Conditional tab addition
add_filter( 'woocommerce_product_tabs', function (array $tabs) {
    // Include plugin functions if not already loaded
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    // Only add the Judge.me reviews tab if the plugin is active
    if ( ! is_plugin_active( 'judgeme-product-reviews/judgeme.php' ) &&
         ! function_exists( 'judgeme_widget' ) &&
         ! shortcode_exists( 'jgm-review-widget' ) ) {
        return $tabs;
    }

    $tabs['judgeme_tab'] = array(
        'title' => __( 'Reviews', 'textdomain' ),
        'priority' => 50,
        'callback' => 'blaze_blocksy_render_judgeme_tab',
    );
    return $tabs;
} );
```

## Detection Methods

The fix uses three detection methods to ensure Judge.me is available:

1. **Plugin Active Check**: `is_plugin_active( 'judgeme-product-reviews/judgeme.php' )`
2. **Function Exists Check**: `function_exists( 'judgeme_widget' )`
3. **Shortcode Exists Check**: `shortcode_exists( 'jgm-review-widget' )`

If ANY of these conditions are true, the Judge.me tab will be displayed.

## Fallback Handling

Added safety checks in the render function to handle cases where Judge.me becomes unavailable after the tab is added:

```php
function blaze_blocksy_render_judgeme_tab() {
    global $product;

    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        return;
    }

    // Double-check that Judge.me is available before rendering
    if ( ! shortcode_exists( 'jgm-review-widget' ) ) {
        echo '<div class="judgeme-fallback">';
        echo '<p>' . __( 'Reviews are currently unavailable. Please check back later.', 'textdomain' ) . '</p>';
        echo '</div>';
        return;
    }
    ?>
    <h2>Customer Review</h2>
    <div>
        <?php echo do_shortcode( '[jgm-review-widget id="' . $product->get_id() . '"]' ); ?>
    </div>
    <?php
}
```

## Debug Utilities

Added debug utilities for development environments (`includes/debug/judgeme-tab-test.php`):

- **Admin Bar Indicator**: Shows whether Judge.me tab is active/disabled
- **Console Logging**: Detailed detection results in browser console
- **Product Page Debug**: Visual debug information on product pages (admin only)

## Testing

### Manual Testing Steps

1. **Without Judge.me Plugin**:
   - Navigate to any product page
   - Verify only one "Reviews (0)" tab appears (WooCommerce native)
   - No duplicate tabs should be visible

2. **With Judge.me Plugin Active**:
   - Install and activate Judge.me plugin
   - Navigate to any product page
   - Verify Judge.me "Reviews" tab appears
   - WooCommerce native reviews may be disabled by Judge.me

3. **Debug Mode Testing** (WP_DEBUG = true):
   - Check admin bar for Judge.me debug indicator
   - Click indicator to see console output
   - Verify debug information on product pages

### Expected Results

- **Before Fix**: Two reviews tabs visible ("Reviews (0)" and "Reviews")
- **After Fix**: Only one reviews tab visible based on plugin availability

## Files Modified

1. **`includes/customization/judgeme.php`**: Added conditional checks
2. **`functions.php`**: Added debug file inclusion
3. **`includes/debug/judgeme-tab-test.php`**: New debug utilities (created)

## Compatibility

- **WordPress**: 5.0+
- **WooCommerce**: 3.0+
- **Judge.me Plugin**: All versions
- **Blocksy Theme**: All versions

## Performance Impact

- **Minimal**: Only adds lightweight conditional checks
- **No Database Queries**: Uses existing WordPress functions
- **Debug Mode Only**: Debug utilities only load when WP_DEBUG is enabled

## Rollback Plan

If issues occur, revert `includes/customization/judgeme.php` to remove conditional checks:

```php
// Rollback: Remove conditional checks (not recommended)
add_filter( 'woocommerce_product_tabs', function (array $tabs) {
    $tabs['judgeme_tab'] = array(
        'title' => __( 'Reviews', 'textdomain' ),
        'priority' => 50,
        'callback' => 'blaze_blocksy_render_judgeme_tab',
    );
    return $tabs;
} );
```

## Changelog

- **2025-01-23**: Initial fix implementation
  - Added conditional Judge.me plugin detection
  - Implemented fallback handling
  - Created debug utilities
  - Updated documentation
