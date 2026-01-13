# Currency-Based Page Display - Quick Start Guide

## 5-Minute Setup

### What This Does
Automatically shows different page content based on the visitor's currency.

### Example
- US visitor (USD) visits "About Us" → sees US-specific version
- Canadian visitor (CAD) visits "About Us" → sees Canadian-specific version

## Setup Steps

### 1. Create Your Pages
Create two pages with different content:
- Page A: "About Us - US" (content for US visitors)
- Page B: "About Us - Canada" (content for Canadian visitors)

### 2. Configure Page A
1. Go to **Pages** → Edit "About Us - US"
2. Scroll to **"Blaze Commerce Settings"** (right sidebar)
3. Set:
   - **Page Region**: "United States (USD)"
   - **Related Page**: Search and select "About Us - Canada"
4. Click **Update**

### 3. Configure Page B
1. Go to **Pages** → Edit "About Us - Canada"
2. Scroll to **"Blaze Commerce Settings"** (right sidebar)
3. Set:
   - **Page Region**: "Canada (CAD)"
   - **Related Page**: Search and select "About Us - US"
4. Click **Update**

### 4. Test
1. Visit "About Us - US" page
2. If your store currency is USD → you see "About Us - US" content
3. If your store currency is CAD → you see "About Us - Canada" content

## How It Works

```
Visitor arrives with currency USD
         ↓
Check current page metadata
         ↓
Page Region = "United States (USD)" ✓
Related Page = "About Us - Canada" ✓
         ↓
Display "About Us - Canada" content
(URL stays the same)
```

## Common Scenarios

### Scenario 1: Multi-Currency Store
**Goal**: Show region-specific pricing and content

**Setup**:
- Create pages for each region (US, Canada, UK, etc.)
- Link them as related pages
- Each page shows when visitor's currency matches

### Scenario 2: Language + Currency
**Goal**: Show different language based on currency

**Setup**:
- Create English version for USD
- Create French version for CAD
- Link them together
- Visitors see appropriate language

### Scenario 3: Promotional Pages
**Goal**: Show different promotions by region

**Setup**:
- Create promotion page for US (USD)
- Create promotion page for Canada (CAD)
- Link them together
- Each region sees their promotion

## Troubleshooting

### Related Page Not Showing?

**Check 1**: Is the related page published?
- Go to Pages → Check status is "Published"

**Check 2**: Is the region correct?
- Go to Blaze Commerce Settings
- Verify "Page Region" matches your currency

**Check 3**: Is Aelia Currency Switcher active?
- Go to Plugins
- Search for "Aelia Currency Switcher"
- Make sure it's activated

**Check 4**: Is WooCommerce currency set?
- Go to WooCommerce → Settings → General
- Check "Store Currency" is set

### How to Debug

Add this to your page temporarily to see what's happening:

```php
// Add to functions.php temporarily
add_action( 'wp_footer', function() {
    if ( is_singular( 'page' ) ) {
        echo '<!-- Page ID: ' . get_the_ID() . ' -->';
        echo '<!-- Region: ' . get_post_meta( get_the_ID(), 'blaze_page_region', true ) . ' -->';
        echo '<!-- Related: ' . get_post_meta( get_the_ID(), 'blaze_related_page', true ) . ' -->';
    }
} );
```

Then check page source (Ctrl+U or Cmd+U) to see the values.

## Advanced Options

### Option 1: Redirect Instead of Display
By default, the related page content is displayed while keeping the original URL.

To redirect instead (301 permanent redirect):
1. Open `custom/currency-based-page-display.php`
2. Find the commented redirect code
3. Uncomment it

### Option 2: Add Custom Conditions
To add additional logic before displaying related page:

```php
// Add to functions.php
add_filter( 'blaze_should_display_related_page', function( $should_display, $post_id ) {
    // Your custom logic
    if ( is_user_logged_in() ) {
        return false; // Don't redirect for logged-in users
    }
    return $should_display;
}, 10, 2 );
```

## FAQ

**Q: Will this affect SEO?**
A: In display mode (current), the URL stays the same. Consider adding canonical tags. In redirect mode, it's SEO-friendly.

**Q: Can I use this for more than 2 pages?**
A: Currently, each page can link to one related page. For complex scenarios, consider using page templates or custom post types.

**Q: What if I don't have Aelia Currency Switcher?**
A: This feature requires Aelia Currency Switcher to map currencies to regions. Without it, the feature won't work.

**Q: Can I manually set the region instead of using currency?**
A: Yes, the "Page Region" field in Blaze Commerce Settings is manual. You can set any region code.

**Q: Does this work with WooCommerce Multilingual?**
A: This feature is designed for Aelia Currency Switcher. For WPML, you may need custom modifications.

## Next Steps

1. ✅ Create your pages
2. ✅ Configure metadata
3. ✅ Test with different currencies
4. ✅ Monitor analytics to see if visitors see correct content
5. ✅ Adjust content based on performance

## Support Resources

- **Aelia Currency Switcher**: https://www.aelia.co/
- **WooCommerce**: https://woocommerce.com/
- **WordPress Docs**: https://developer.wordpress.org/

## Need Help?

Check the full documentation: `docs/features/CURRENCY-BASED-PAGE-DISPLAY.md`

