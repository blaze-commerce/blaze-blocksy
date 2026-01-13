# Currency-Based Page Display - Practical Examples

## Example 1: Simple Two-Region Setup

### Scenario
You have a store serving US and Canadian customers with different pricing and content.

### Setup

**Page 1: "Pricing - US"**
- Content: US pricing in USD
- Page Region: United States (USD)
- Related Page: Pricing - Canada

**Page 2: "Pricing - Canada"**
- Content: Canadian pricing in CAD
- Page Region: Canada (CAD)
- Related Page: Pricing - US

### Result
- US visitor (USD) visits `/pricing-us/` → sees US pricing
- Canadian visitor (CAD) visits `/pricing-us/` → sees Canadian pricing
- US visitor visits `/pricing-canada/` → sees US pricing
- Canadian visitor visits `/pricing-canada/` → sees Canadian pricing

### URL Structure
```
/pricing-us/        ← Main US page
/pricing-canada/    ← Main Canadian page
```

## Example 2: Multi-Region E-Commerce

### Scenario
International store with 4 regions: US, Canada, UK, Australia

### Setup

**Create 4 pages:**
1. "Shipping Info - US" (USD)
2. "Shipping Info - Canada" (CAD)
3. "Shipping Info - UK" (GBP)
4. "Shipping Info - Australia" (AUD)

**Link them in a chain:**
- US → Canada
- Canada → UK
- UK → Australia
- Australia → US (optional, or leave blank)

### Result
Each visitor sees shipping info for their region regardless of which page they visit.

### Configuration Example
```
Page: Shipping Info - US
├─ Region: United States (USD)
└─ Related Page: Shipping Info - Canada

Page: Shipping Info - Canada
├─ Region: Canada (CAD)
└─ Related Page: Shipping Info - UK

Page: Shipping Info - UK
├─ Region: United Kingdom (GBP)
└─ Related Page: Shipping Info - Australia

Page: Shipping Info - Australia
├─ Region: Australia (AUD)
└─ Related Page: (leave blank or link back to US)
```

## Example 3: Promotional Campaign by Region

### Scenario
Running different promotions in different regions.

### Setup

**Page 1: "Summer Sale - North America"**
- Content: 20% off summer collection
- Page Region: United States (USD)
- Related Page: Summer Sale - Europe

**Page 2: "Summer Sale - Europe"**
- Content: €15 off summer collection
- Page Region: United Kingdom (GBP)
- Related Page: Summer Sale - North America

### Result
- North American visitors see North American promotion
- European visitors see European promotion
- Both can access the same URL structure

## Example 4: Language-Based Display

### Scenario
Different languages based on currency/region.

### Setup

**Page 1: "About Us - English"**
- Content: English version
- Page Region: United States (USD)
- Related Page: About Us - French

**Page 2: "About Us - French"**
- Content: French version
- Page Region: Canada (CAD)
- Related Page: About Us - English

### Result
- USD visitors see English
- CAD visitors see French
- Automatic language switching based on currency

## Example 5: Tax & Compliance Information

### Scenario
Different tax information and legal compliance for each region.

### Setup

**Page 1: "Tax Information - US"**
- Content: US tax information, sales tax details
- Page Region: United States (USD)
- Related Page: Tax Information - Canada

**Page 2: "Tax Information - Canada"**
- Content: Canadian tax information, GST/HST details
- Page Region: Canada (CAD)
- Related Page: Tax Information - US

### Result
Visitors automatically see tax information relevant to their region.

## Example 6: Support & Contact Information

### Scenario
Different support contact info for each region.

### Setup

**Page 1: "Contact Us - US"**
- Content: US phone number, US address, US support hours
- Page Region: United States (USD)
- Related Page: Contact Us - Canada

**Page 2: "Contact Us - Canada"**
- Content: Canadian phone number, Canadian address, Canadian support hours
- Page Region: Canada (CAD)
- Related Page: Contact Us - US

### Result
Visitors see contact information for their region.

## Example 7: Product Availability by Region

### Scenario
Some products are only available in certain regions.

### Setup

**Page 1: "Available Products - US"**
- Content: List of products available in US
- Page Region: United States (USD)
- Related Page: Available Products - Canada

**Page 2: "Available Products - Canada"**
- Content: List of products available in Canada
- Page Region: Canada (CAD)
- Related Page: Available Products - US

### Result
Visitors see products available in their region.

## Example 8: Terms & Conditions by Region

### Scenario
Different terms and conditions for each region.

### Setup

**Page 1: "Terms & Conditions - US"**
- Content: US-specific terms
- Page Region: United States (USD)
- Related Page: Terms & Conditions - Canada

**Page 2: "Terms & Conditions - Canada"**
- Content: Canadian-specific terms
- Page Region: Canada (CAD)
- Related Page: Terms & Conditions - US

### Result
Visitors see terms applicable to their region.

## Advanced Example: Conditional Display with Custom Code

### Scenario
Show related page only for certain user types.

### Implementation
```php
// Add to functions.php
add_filter( 'blaze_should_display_related_page', function( $should_display, $post_id ) {
    // Don't redirect for logged-in users
    if ( is_user_logged_in() ) {
        return false;
    }
    
    // Don't redirect on mobile
    if ( wp_is_mobile() ) {
        return false;
    }
    
    return $should_display;
}, 10, 2 );
```

## Advanced Example: Redirect Instead of Display

### Scenario
You want 301 redirects instead of displaying content.

### Implementation
1. Open `custom/currency-based-page-display.php`
2. Find the commented redirect code (around line 140)
3. Uncomment it:

```php
$related_page_url = get_permalink( $related_page_id );
if ( $related_page_url ) {
    wp_safe_remote_post( $related_page_url );
    wp_redirect( $related_page_url, 301 );
    exit;
}
```

### Result
- Visitors are redirected to the appropriate page
- URL changes to the related page
- Search engines see 301 redirect

## Advanced Example: Add Analytics Tracking

### Scenario
Track when currency-based page display is triggered.

### Implementation
```php
// Add to functions.php
add_action( 'wp_footer', function() {
    if ( is_singular( 'page' ) && current_user_can( 'manage_options' ) ) {
        $post_id = get_the_ID();
        $region = get_post_meta( $post_id, 'blaze_page_region', true );
        $related = get_post_meta( $post_id, 'blaze_related_page', true );
        
        if ( $region && $related ) {
            // Log to analytics
            echo '<script>
                gtag("event", "currency_page_display", {
                    "page_id": ' . $post_id . ',
                    "region": "' . $region . '",
                    "related_page": ' . $related . '
                });
            </script>';
        }
    }
} );
```

## Testing Checklist for Examples

### For Each Example:
- [ ] Create pages with different content
- [ ] Set Page Region metadata
- [ ] Set Related Page metadata
- [ ] Test with USD currency
- [ ] Test with CAD currency
- [ ] Verify correct content displays
- [ ] Check page title is correct
- [ ] Verify breadcrumbs work
- [ ] Test on mobile
- [ ] Test in different browsers

### Common Issues to Check:
- [ ] Related page is published
- [ ] Region matches currency
- [ ] Aelia Currency Switcher is active
- [ ] WooCommerce currency is set
- [ ] Metadata is saved correctly

## Performance Tips

### For Multiple Pages:
1. Use page caching (WP Super Cache, W3 Total Cache)
2. Enable object caching (Redis, Memcached)
3. Minimize related page queries
4. Use CDN for static assets

### Monitoring:
1. Use Query Monitor to check queries
2. Monitor page load time
3. Check database performance
4. Use Xdebug for profiling

## SEO Considerations

### Display Mode (Current):
- URL stays the same
- Add canonical tag to related page
- Use hreflang tags for language variants
- Monitor search console for duplicates

### Redirect Mode:
- 301 redirects are SEO-friendly
- Search engines follow redirects
- Original page won't rank
- Related page will rank

## Troubleshooting Examples

### Example: Related Page Not Showing

**Check:**
```php
// Add to functions.php temporarily
add_action( 'wp_footer', function() {
    if ( is_singular( 'page' ) ) {
        $post_id = get_the_ID();
        $region = get_post_meta( $post_id, 'blaze_page_region', true );
        $related = get_post_meta( $post_id, 'blaze_related_page', true );
        $related_post = get_post( $related );
        
        echo '<!-- DEBUG -->';
        echo '<!-- Post ID: ' . $post_id . ' -->';
        echo '<!-- Region: ' . $region . ' -->';
        echo '<!-- Related ID: ' . $related . ' -->';
        echo '<!-- Related Status: ' . ( $related_post ? $related_post->post_status : 'NOT FOUND' ) . ' -->';
    }
} );
```

Then check page source to see what's happening.

## Next Steps

1. Choose an example that matches your use case
2. Create the pages
3. Configure metadata
4. Test with different currencies
5. Monitor performance
6. Adjust content based on analytics

