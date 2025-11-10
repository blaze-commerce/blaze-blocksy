# Product Geo-Restriction - Quick Start Guide

## 🚀 5-Minute Setup

### Step 1: Verify Prerequisites

✅ **Check Requirements:**
- [ ] Advanced Custom Fields (ACF) installed and active
- [ ] WooCommerce installed and active
- [ ] Site running on HTTPS (required for geolocation)

### Step 2: Activate Feature

The feature is **automatically activated** when you update the theme. No manual activation needed.

**Verify Installation:**
1. Go to **Products → Add New** or edit existing product
2. Scroll down - you should see **Product Geo-Restriction** meta box
3. If not visible, ensure ACF is installed

### Step 3: Configure Your First Product

#### Example: Restrict Product to Texas Only

1. **Edit Product**
   - Go to **Products → All Products**
   - Click on product to edit

2. **Enable Restriction**
   - Scroll to **Product Geo-Restriction** section
   - Toggle **Enable Geo-Restriction** to ON

3. **Select States**
   - Click on the **Allowed US States** dropdown
   - Type "Texas" or scroll to find it
   - Click **Texas** to select it
   - You can select multiple states if needed

4. **Optional: Custom Message**
   - Add custom message (optional):
   ```
   Sorry, this product is only available for Texas residents.
   ```

5. **Save Product**
   - Click **Update** button

### Step 4: Test on Frontend

1. **Visit Product Page**
   - Open product in new tab/window
   - You should see "Verifying location availability..." message

2. **Allow Location Access**
   - Browser will ask for location permission
   - Click **Allow**

3. **View Result**
   - If you're in Texas: Normal "Add to Cart" button
   - If you're NOT in Texas: Restriction message appears

---

## 🧪 Testing Scenarios

### Test 1: Automatic Detection (Happy Path)

**Setup:**
- Product restricted to California
- You're physically in California

**Expected Result:**
```
✅ Location detected: California
✅ "Add to Cart" button visible
✅ Can add product to cart
```

### Test 2: Automatic Detection (Restricted)

**Setup:**
- Product restricted to Texas
- You're physically in New York

**Expected Result:**
```
⚠️ Location detected: New York
⚠️ "Add to Cart" button hidden
⚠️ Message: "This item is ineligible for your location"
⚠️ Shows: "Your location: New York"
⚠️ Shows: "Available only in: Texas"
```

### Test 3: Manual Selection (Fallback)

**Setup:**
- Product restricted to Florida
- Deny location permission

**Expected Result:**
```
📍 Manual selector appears
📍 Dropdown: "Select your state..."
📍 Button: "Check Availability"
```

**Action:**
- Select "Florida" from dropdown
- Click "Check Availability"

**Result:**
```
✅ "Add to Cart" button appears
✅ Can add product to cart
```

### Test 4: No Restrictions

**Setup:**
- Product has geo-restriction disabled

**Expected Result:**
```
✅ No location detection
✅ Normal product page
✅ "Add to Cart" button always visible
```

---

## 🎯 Common Use Cases

### Use Case 1: State-Specific Products

**Scenario:** Selling products only legal in certain states

**Configuration:**
```
Product: CBD Oil
Enable Geo-Restriction: ON
Allowed States: California, Colorado, Oregon, Washington
  (Select multiple states from dropdown)
Custom Message: "This product is only available in states where CBD is legal."
```

### Use Case 2: Regional Exclusives

**Scenario:** Limited edition product for specific region

**Configuration:**
```
Product: Texas BBQ Sauce - Limited Edition
Enable Geo-Restriction: ON
Allowed States: Texas
  (Select from dropdown)
Custom Message: "This exclusive Texas edition is only available to Texas residents."
```

### Use Case 3: Shipping Restrictions

**Scenario:** Product can only ship to nearby states

**Configuration:**
```
Product: Fresh Seafood
Enable Geo-Restriction: ON
Allowed States: Maine, New Hampshire, Vermont, Massachusetts, Rhode Island, Connecticut
  (Select multiple New England states from dropdown)
Custom Message: "Fresh seafood only ships to New England states for quality assurance."
```

---

## 🔧 Troubleshooting

### Issue: ACF Fields Not Showing

**Problem:** Can't see "Product Geo-Restriction" meta box

**Solution:**
1. Verify ACF is installed:
   - Go to **Plugins → Installed Plugins**
   - Look for "Advanced Custom Fields"
   - Ensure it's **Active**

2. Check ACF version:
   - Minimum version: ACF 5.0+
   - Recommended: ACF Pro 6.0+

3. Clear cache:
   - Clear WordPress cache
   - Clear browser cache
   - Refresh product edit page

### Issue: Location Always Fails

**Problem:** Always shows manual selector, never auto-detects

**Possible Causes:**

1. **Not using HTTPS**
   - Modern browsers require HTTPS for geolocation
   - Solution: Enable SSL certificate

2. **Browser doesn't support geolocation**
   - Very old browsers
   - Solution: Use manual selector (automatic fallback)

3. **User denied permission**
   - User clicked "Block" on location prompt
   - Solution: Use manual selector (automatic fallback)

**How to Test:**
```javascript
// Open browser console (F12)
// Run this command:
navigator.geolocation.getCurrentPosition(
  (pos) => console.log('✅ Geolocation works:', pos),
  (err) => console.log('❌ Geolocation failed:', err)
);
```

### Issue: Wrong State Detected

**Problem:** System detects wrong state

**Possible Causes:**

1. **Using VPN/Proxy**
   - VPN shows VPN server location, not actual location
   - Solution: Disable VPN or use manual selector

2. **Inaccurate GPS**
   - Mobile devices near state borders
   - Solution: Use manual selector

3. **Cached old location**
   - Location cached from different place
   - Solution: Clear cache

**Clear Location Cache:**
```javascript
// Open browser console (F12)
// Run these commands:
localStorage.removeItem('blaze_user_location');
localStorage.removeItem('blaze_location_timestamp');
// Refresh page
```

### Issue: Button Not Hiding

**Problem:** "Add to Cart" button still visible when restricted

**Debug Steps:**

1. **Check browser console (F12)**
   ```
   Look for errors in red
   Should see: [Geo-Restriction] logs
   ```

2. **Verify JavaScript loaded**
   ```javascript
   // In console:
   console.log(typeof blazeGeoRestriction);
   // Should output: "object"
   ```

3. **Check product settings**
   - Is geo-restriction enabled?
   - Are states selected?
   - Save product again

4. **Theme conflict**
   - Try default WordPress theme
   - Check for JavaScript errors

---

## 📊 Performance Tips

### Optimize for Speed

1. **Cache Duration**
   - Default: 24 hours
   - Reduces API calls by 99%
   - No configuration needed

2. **Conditional Loading**
   - Assets only load on restricted products
   - No impact on other pages
   - Automatic optimization

3. **API Timeout**
   - 10-second timeout
   - Automatic fallback to manual selector
   - No hanging requests

### Monitor Performance

**Check Load Time:**
```javascript
// Browser console
performance.getEntriesByName('geo-restriction.js')
```

**Expected Times:**
- Cached location: < 100ms
- First visit: 1-3 seconds
- Manual selection: Instant

---

## 🎨 Customization

### Change Restriction Message Style

**Edit:** `assets/css/geo-restriction.css`

```css
/* Change warning color */
.geo-restriction-message {
  background: #your-color;
  border-color: #your-border-color;
}

/* Change text color */
.geo-restriction-title {
  color: #your-text-color;
}
```

### Change Cache Duration

**Edit:** `includes/features/geo-restriction.php`

```php
// Line ~230
'cache_duration' => 48 * 60 * 60 * 1000, // 48 hours instead of 24
```

### Add Custom State List

**Edit:** `includes/features/geo-restriction.php`

```php
// Add to $us_states array (line ~30)
'PR' => 'Puerto Rico',
'GU' => 'Guam',
```

---

## 🔐 Security Notes

### Current Implementation

⚠️ **Client-Side Only**
- Location detection happens in browser
- Can be bypassed by tech-savvy users
- Suitable for UX/convenience, not security

### For Security-Critical Restrictions

**Recommended Approach:**

1. **Use for UX only** (current)
   - Inform users about availability
   - Improve shopping experience
   - Reduce support tickets

2. **Add server-side validation** (future)
   - Validate at checkout
   - Use IP geolocation
   - Cannot be bypassed

**Example Server-Side Validation:**
```php
// Future enhancement
add_action('woocommerce_checkout_process', function() {
    // Get user's IP
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Use IP geolocation service
    $state = get_state_from_ip($ip);
    
    // Check cart items
    foreach (WC()->cart->get_cart() as $item) {
        $product_id = $item['product_id'];
        $allowed_states = get_field('allowed_us_states', $product_id);
        
        if (!in_array($state, $allowed_states)) {
            wc_add_notice('Product not available in your state', 'error');
        }
    }
});
```

---

## 📞 Getting Help

### Debug Mode

Enable debug logging:

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check console for:
```
[Geo-Restriction] Geo-restriction enabled for this product
[Geo-Restriction] Using cached location: TX
[Geo-Restriction] State check: {...}
```

### Support Channels

1. **Documentation**
   - Full docs: `docs/features/PRODUCT-GEO-RESTRICTION.md`
   - This guide: `docs/features/GEO-RESTRICTION-QUICK-START.md`

2. **Code Comments**
   - PHP: `includes/features/geo-restriction.php`
   - JS: `assets/js/geo-restriction.js`
   - CSS: `assets/css/geo-restriction.css`

3. **Browser Console**
   - Press F12
   - Check Console tab
   - Look for `[Geo-Restriction]` logs

---

## ✅ Checklist

### Before Going Live

- [ ] ACF installed and active
- [ ] WooCommerce installed and active
- [ ] Site using HTTPS
- [ ] Tested on multiple products
- [ ] Tested with allowed state
- [ ] Tested with restricted state
- [ ] Tested manual selector
- [ ] Tested on mobile device
- [ ] Tested on different browsers
- [ ] Custom messages configured
- [ ] Cache working (check localStorage)
- [ ] No JavaScript errors in console
- [ ] Performance acceptable (< 3s)

### Post-Launch Monitoring

- [ ] Monitor user feedback
- [ ] Check API rate limits (Nominatim)
- [ ] Review restriction effectiveness
- [ ] Track manual selector usage
- [ ] Consider server-side validation
- [ ] Update documentation as needed

---

## 🎓 Next Steps

1. **Read Full Documentation**
   - `docs/features/PRODUCT-GEO-RESTRICTION.md`

2. **Configure Products**
   - Start with 1-2 test products
   - Expand to more products

3. **Monitor Performance**
   - Check browser console
   - Monitor API usage

4. **Plan Enhancements**
   - Server-side validation
   - Additional countries
   - Analytics integration

---

**Last Updated:** 2024-11-10  
**Version:** 1.0.0  
**Author:** Blaze Commerce Team

