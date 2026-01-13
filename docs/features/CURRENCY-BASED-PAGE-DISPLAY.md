# Currency-Based Page Display Feature

## Overview

The Currency-Based Page Display feature automatically displays alternative page content based on the current currency and page metadata. This is useful for multi-currency stores where you want to show region-specific or currency-specific content to visitors.

## How It Works

### Core Logic

1. **Detect Current Currency**: The system gets the current WooCommerce currency
2. **Map Currency to Region**: Uses Aelia Currency Switcher settings to map the currency to a region/country code
3. **Check Page Metadata**: Looks for two custom meta fields on the current page:
   - `blaze_page_region`: The region this page is configured for
   - `blaze_related_page`: The page ID to display if the region matches
4. **Display Related Page**: If the current region matches the page's configured region and a related page is set, the related page content is displayed instead

### Example Scenario

**Setup:**
- Page A is configured for "United States (USD)" region with Related Page set to Page B
- Page B is configured for "Canada (CAD)" region with Related Page set to Page A

**Behavior:**
- When a US visitor (USD currency) visits Page A → Page B is displayed
- When a Canadian visitor (CAD currency) visits Page B → Page A is displayed
- When a US visitor visits Page B → Page B is displayed (no match)

## Configuration

### Step 1: Set Up Page Metadata

1. Go to **Pages** in WordPress admin
2. Edit the page you want to configure
3. In the right sidebar, find **"Blaze Commerce Settings"** meta box
4. Configure:
   - **Page Region**: Select the region this page is for (e.g., "United States (USD)")
   - **Related Page**: Search and select the page to display when the region matches

### Step 2: Set Up Related Page

Configure the related page with:
- **Page Region**: The region for that page (e.g., "Canada (CAD)")
- **Related Page**: Link back to the original page (optional, for bidirectional setup)

## Features

### ✅ Automatic Region Detection
- Automatically maps WooCommerce currency to region using Aelia Currency Switcher
- No manual region selection needed for visitors

### ✅ Flexible Display Options
- **Display Mode (Current)**: Shows related page content while maintaining URL
- **Redirect Mode (Optional)**: Can be enabled to perform 301 redirects

### ✅ Safety Checks
- Verifies related page exists and is published
- Validates metadata before processing
- Only works on singular pages (not archives)
- Skips processing in admin area

### ✅ Performance Optimized
- Uses high priority hook (999) to ensure proper template loading
- Minimal database queries
- Caching-friendly approach

## Implementation Details

### File Location
```
custom/currency-based-page-display.php
```

### Class: BlazeCommerceCurrencyPageDisplay

#### Key Methods

**`get_current_currency()`**
- Returns the current WooCommerce currency code
- Example: 'USD', 'EUR', 'CAD'

**`get_current_region()`**
- Maps current currency to region/country code using Aelia settings
- Returns first country code mapped to the currency

**`should_redirect_to_related_page()`**
- Determines if page should display related content
- Checks: singular page, metadata exists, region matches, related page is published

**`maybe_redirect_to_related_page( $template )`**
- Main filter hook that processes the template
- Sets global post to related page
- Returns appropriate template

## Enabling Redirect Mode

By default, the feature displays related page content while keeping the original URL. To enable 301 redirects instead:

1. Open `custom/currency-based-page-display.php`
2. Find the commented section in `maybe_redirect_to_related_page()` method
3. Uncomment the redirect code:

```php
$related_page_url = get_permalink( $related_page_id );
if ( $related_page_url ) {
    wp_safe_remote_post( $related_page_url );
    wp_redirect( $related_page_url, 301 );
    exit;
}
```

## Requirements

- **WooCommerce**: Active and configured
- **Aelia Currency Switcher**: Active and configured with currency-to-country mappings
- **Page Meta Fields**: Already configured via `page-meta-fields.php`

## Troubleshooting

### Related Page Not Displaying

**Check:**
1. Is the related page published?
2. Does the page region match the current currency's region?
3. Is Aelia Currency Switcher active and properly configured?
4. Are the meta fields properly saved?

**Debug:**
Add this to your theme's functions.php temporarily:
```php
add_action( 'wp_footer', function() {
    if ( is_singular( 'page' ) ) {
        $post_id = get_the_ID();
        echo '<!-- DEBUG: Page ID: ' . $post_id . ' -->';
        echo '<!-- DEBUG: Region: ' . get_post_meta( $post_id, 'blaze_page_region', true ) . ' -->';
        echo '<!-- DEBUG: Related Page: ' . get_post_meta( $post_id, 'blaze_related_page', true ) . ' -->';
    }
} );
```

### Currency Not Detected

**Check:**
1. Is WooCommerce active?
2. Is the currency properly set in WooCommerce settings?
3. Is Aelia Currency Switcher active?

## Advanced Usage

### Custom Currency Detection

To use a different currency detection method, override the `get_current_currency()` method:

```php
// In your custom code
add_filter( 'blaze_current_currency', function( $currency ) {
    // Your custom logic here
    return $currency;
} );
```

### Conditional Display

To add additional conditions before displaying related page:

```php
add_filter( 'blaze_should_display_related_page', function( $should_display, $post_id, $related_page_id ) {
    // Add your custom logic
    if ( some_condition() ) {
        return false;
    }
    return $should_display;
}, 10, 3 );
```

## Performance Considerations

- **Hook Priority**: Uses priority 999 to ensure it runs after most other template filters
- **Database Queries**: Minimal - only queries related page if metadata exists
- **Caching**: Compatible with page caching plugins
- **SEO**: Consider using canonical tags if using display mode (not redirect)

## SEO Implications

### Display Mode (Current)
- URL remains the same
- Consider adding canonical tag to related page
- May confuse search engines if not properly configured

### Redirect Mode
- 301 redirects are SEO-friendly
- Search engines will index the related page
- Original page URL will be replaced in search results

## Related Features

- **Page Meta Fields**: `custom/page-meta-fields.php` - Manages the metadata UI
- **Aelia Currency Switcher**: External plugin for currency management
- **WooCommerce**: Core e-commerce functionality

## Support

For issues or questions, check:
1. WooCommerce documentation
2. Aelia Currency Switcher documentation
3. WordPress template hierarchy documentation

