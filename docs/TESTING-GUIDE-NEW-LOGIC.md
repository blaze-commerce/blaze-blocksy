# Testing Guide - New Currency-Based Page Display Logic

## 🧪 Quick Test Setup

### Create Test Pages

**Page A: "About Us - US"**
- ID: 1
- Content: "This is the US version of About Us"
- `blaze_page_region` = "US"
- `blaze_related_page` = 2

**Page B: "About Us - Canada"**
- ID: 2
- Content: "This is the Canadian version of About Us"
- `blaze_page_region` = "CA"
- `blaze_related_page` = 1

## 🧪 Test Cases

### Test 1: USD Visitor on US Page

**Setup:**
- Currency: USD (maps to region "US")
- Visit: Page A (ID: 1)

**Expected Behavior:**
```
1. Current page ID = 1
2. Query: Find pages where blaze_related_page = 1
3. Found: Page B (ID: 2, region: CA)
4. Check: CA == US? NO
5. Result: Display Page A
```

**Verification:**
- [ ] URL shows: `/about-us-us/`
- [ ] Page title shows: "About Us - US"
- [ ] Content shows: "This is the US version of About Us"
- [ ] No redirect occurs

---

### Test 2: CAD Visitor on US Page

**Setup:**
- Currency: CAD (maps to region "CA")
- Visit: Page A (ID: 1)

**Expected Behavior:**
```
1. Current page ID = 1
2. Query: Find pages where blaze_related_page = 1
3. Found: Page B (ID: 2, region: CA)
4. Check: CA == CA? YES
5. Result: Display Page B
```

**Verification:**
- [ ] URL shows: `/about-us-us/` (unchanged)
- [ ] Page title shows: "About Us - Canada"
- [ ] Content shows: "This is the Canadian version of About Us"
- [ ] No redirect occurs

---

### Test 3: USD Visitor on Canada Page

**Setup:**
- Currency: USD (maps to region "US")
- Visit: Page B (ID: 2)

**Expected Behavior:**
```
1. Current page ID = 2
2. Query: Find pages where blaze_related_page = 2
3. Found: Page A (ID: 1, region: US)
4. Check: US == US? YES
5. Result: Display Page A
```

**Verification:**
- [ ] URL shows: `/about-us-canada/` (unchanged)
- [ ] Page title shows: "About Us - US"
- [ ] Content shows: "This is the US version of About Us"
- [ ] No redirect occurs

---

### Test 4: CAD Visitor on Canada Page

**Setup:**
- Currency: CAD (maps to region "CA")
- Visit: Page B (ID: 2)

**Expected Behavior:**
```
1. Current page ID = 2
2. Query: Find pages where blaze_related_page = 2
3. Found: Page A (ID: 1, region: US)
4. Check: US == CA? NO
5. Result: Display Page B
```

**Verification:**
- [ ] URL shows: `/about-us-canada/`
- [ ] Page title shows: "About Us - Canada"
- [ ] Content shows: "This is the Canadian version of About Us"
- [ ] No redirect occurs

---

## 🔍 Debug Checklist

### Before Testing
- [ ] WooCommerce is active
- [ ] Aelia Currency Switcher is active
- [ ] Currency mappings are configured
- [ ] Test pages are published
- [ ] Metadata is correctly set

### During Testing
- [ ] Check browser console for errors
- [ ] Verify page title changes
- [ ] Verify page content changes
- [ ] Check URL doesn't change (display mode)
- [ ] Monitor page load time

### After Testing
- [ ] All 4 test cases pass
- [ ] No console errors
- [ ] Performance is acceptable
- [ ] Caching works correctly

## 🐛 Troubleshooting

### Related Page Not Showing

**Check:**
1. Is the related page published?
   ```php
   get_post_status( 2 );  // Should return 'publish'
   ```

2. Is the metadata set correctly?
   ```php
   get_post_meta( 1, 'blaze_related_page', true );  // Should return 2
   get_post_meta( 1, 'blaze_page_region', true );   // Should return 'US'
   ```

3. Is the currency mapped correctly?
   ```php
   get_woocommerce_currency();  // Should return 'USD' or 'CAD'
   ```

4. Is the region mapping correct?
   ```php
   // Check Aelia settings
   $aelia = get_option( 'wc_aelia_currency_switcher' );
   print_r( $aelia['currency_countries_mappings'] );
   ```

### Currency Not Detected

**Check:**
1. Is WooCommerce active?
   ```php
   function_exists( 'get_woocommerce_currency' );  // Should return true
   ```

2. Is currency set in WooCommerce?
   ```php
   get_woocommerce_currency();  // Should return currency code
   ```

3. Is Aelia Currency Switcher active?
   ```php
   is_plugin_active( 'woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php' );
   ```

### Performance Issues

**Check:**
1. Enable Query Monitor plugin
2. Look for slow queries
3. Check database indexes
4. Enable object caching

## 📊 Test Results Template

```
Test Date: _______________
Tester: ___________________

Test 1: USD on US Page
  Result: [ ] PASS [ ] FAIL
  Notes: _________________

Test 2: CAD on US Page
  Result: [ ] PASS [ ] FAIL
  Notes: _________________

Test 3: USD on Canada Page
  Result: [ ] PASS [ ] FAIL
  Notes: _________________

Test 4: CAD on Canada Page
  Result: [ ] PASS [ ] FAIL
  Notes: _________________

Overall: [ ] PASS [ ] FAIL
Issues Found: _____________
```

## 🔧 Debug Code

Add this to `functions.php` temporarily for debugging:

```php
add_action( 'wp_footer', function() {
    if ( is_singular( 'page' ) && current_user_can( 'manage_options' ) ) {
        $post_id = get_the_ID();
        $region = get_post_meta( $post_id, 'blaze_page_region', true );
        $related = get_post_meta( $post_id, 'blaze_related_page', true );
        
        echo '<!-- DEBUG: Currency Page Display -->';
        echo '<!-- Current Page ID: ' . $post_id . ' -->';
        echo '<!-- Page Region: ' . $region . ' -->';
        echo '<!-- Related Page: ' . $related . ' -->';
        
        if ( function_exists( 'get_woocommerce_currency' ) ) {
            echo '<!-- WooCommerce Currency: ' . get_woocommerce_currency() . ' -->';
        }
    }
} );
```

## ✅ Success Criteria

All of the following must be true:

- [x] Test 1 passes (USD on US page shows US content)
- [x] Test 2 passes (CAD on US page shows CA content)
- [x] Test 3 passes (USD on CA page shows US content)
- [x] Test 4 passes (CAD on CA page shows CA content)
- [x] No console errors
- [x] Page load time < 100ms
- [x] Caching works correctly
- [x] No database errors

## 🚀 Next Steps After Testing

1. **If all tests pass:**
   - Deploy to production
   - Monitor for issues
   - Collect user feedback

2. **If tests fail:**
   - Check troubleshooting section
   - Review debug output
   - Check configuration
   - Review code changes

## 📝 Notes

- Tests should be run in incognito/private mode to avoid caching
- Clear browser cache between tests
- Test on multiple browsers if possible
- Test on mobile devices
- Monitor server logs for errors

---

**Testing Guide Version**: 2.0.0
**Last Updated**: 2025-10-17

