# Testing the Post Replacement Fix

## 🧪 Quick Test

### Setup Test Pages

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

### Test 1: CAD Visitor on US Page

**Steps:**
1. Set currency to CAD (or use currency switcher)
2. Visit Page A: `/about-us-us/`
3. Check what displays

**Expected Result:**
- ✅ URL: `/about-us-us/` (unchanged)
- ✅ Page Title: "About Us - Canada"
- ✅ Page Content: "This is the Canadian version of About Us"
- ✅ No redirect occurs

**What to Look For:**
- [ ] Title changed to "About Us - Canada"
- [ ] Content shows Canadian version
- [ ] URL stayed the same
- [ ] No console errors

### Test 2: USD Visitor on US Page

**Steps:**
1. Set currency to USD
2. Visit Page A: `/about-us-us/`
3. Check what displays

**Expected Result:**
- ✅ URL: `/about-us-us/` (unchanged)
- ✅ Page Title: "About Us - US"
- ✅ Page Content: "This is the US version of About Us"
- ✅ No replacement occurs

**What to Look For:**
- [ ] Title stays "About Us - US"
- [ ] Content shows US version
- [ ] URL stayed the same
- [ ] No console errors

### Test 3: USD Visitor on Canada Page

**Steps:**
1. Set currency to USD
2. Visit Page B: `/about-us-canada/`
3. Check what displays

**Expected Result:**
- ✅ URL: `/about-us-canada/` (unchanged)
- ✅ Page Title: "About Us - US"
- ✅ Page Content: "This is the US version of About Us"
- ✅ Replacement occurs

**What to Look For:**
- [ ] Title changed to "About Us - US"
- [ ] Content shows US version
- [ ] URL stayed the same
- [ ] No console errors

### Test 4: CAD Visitor on Canada Page

**Steps:**
1. Set currency to CAD
2. Visit Page B: `/about-us-canada/`
3. Check what displays

**Expected Result:**
- ✅ URL: `/about-us-canada/` (unchanged)
- ✅ Page Title: "About Us - Canada"
- ✅ Page Content: "This is the Canadian version of About Us"
- ✅ No replacement occurs

**What to Look For:**
- [ ] Title stays "About Us - Canada"
- [ ] Content shows Canadian version
- [ ] URL stayed the same
- [ ] No console errors

## 🔍 Debug Checklist

### Before Testing
- [ ] WooCommerce is active
- [ ] Aelia Currency Switcher is active
- [ ] Currency mappings are configured
- [ ] Test pages are published
- [ ] Metadata is correctly set
- [ ] `custom/currency-based-page-display.php` is loaded

### During Testing
- [ ] Open browser console (F12)
- [ ] Check for JavaScript errors
- [ ] Check page title in browser tab
- [ ] Check page content
- [ ] Check URL in address bar

### After Testing
- [ ] All 4 tests pass
- [ ] No console errors
- [ ] No PHP errors in logs
- [ ] Performance is acceptable

## 🐛 Troubleshooting

### Related Page Not Showing

**Check 1: Is the metadata set correctly?**
```php
// In WordPress admin, check:
// Page A: blaze_related_page = 2
// Page B: blaze_related_page = 1
// Page A: blaze_page_region = "US"
// Page B: blaze_page_region = "CA"
```

**Check 2: Is the currency mapped correctly?**
```php
// In WooCommerce settings, check:
// USD → US
// CAD → CA
```

**Check 3: Is the filter running?**
Add this to `functions.php` temporarily:
```php
add_filter( 'the_posts', function( $posts, $query ) {
    if ( $query->is_main_query() && $query->is_singular( 'page' ) ) {
        error_log( 'the_posts filter running' );
        error_log( 'Current post ID: ' . get_the_ID() );
    }
    return $posts;
}, 5, 2 );
```

Check WordPress debug log for output.

### Wrong Content Still Showing

**Check 1: Clear cache**
- Clear browser cache
- Clear WordPress cache (if using caching plugin)
- Clear CDN cache (if using CDN)

**Check 2: Check page template**
- Make sure both pages use the same template
- Or check if template is page-specific

**Check 3: Check for conflicting plugins**
- Disable other plugins temporarily
- Test if issue persists

### Console Errors

**Error: "Cannot read property 'ID' of undefined"**
- Check that related page exists
- Check that metadata is set correctly

**Error: "get_post is not defined"**
- Check that WordPress is loaded
- Check that custom file is included

## 📊 Test Results Template

```
Test Date: _______________
Tester: ___________________
Currency Plugin: ___________

Test 1: CAD on US Page
  Title Changed: [ ] YES [ ] NO
  Content Changed: [ ] YES [ ] NO
  URL Unchanged: [ ] YES [ ] NO
  Result: [ ] PASS [ ] FAIL

Test 2: USD on US Page
  Title Unchanged: [ ] YES [ ] NO
  Content Unchanged: [ ] YES [ ] NO
  URL Unchanged: [ ] YES [ ] NO
  Result: [ ] PASS [ ] FAIL

Test 3: USD on Canada Page
  Title Changed: [ ] YES [ ] NO
  Content Changed: [ ] YES [ ] NO
  URL Unchanged: [ ] YES [ ] NO
  Result: [ ] PASS [ ] FAIL

Test 4: CAD on Canada Page
  Title Unchanged: [ ] YES [ ] NO
  Content Unchanged: [ ] YES [ ] NO
  URL Unchanged: [ ] YES [ ] NO
  Result: [ ] PASS [ ] FAIL

Overall Result: [ ] PASS [ ] FAIL
Issues Found: _________________
```

## 🔧 Debug Code

Add this to `functions.php` temporarily for debugging:

```php
// Debug: Log the_posts filter
add_filter( 'the_posts', function( $posts, $query ) {
    if ( $query->is_main_query() && $query->is_singular( 'page' ) && ! is_admin() ) {
        $post_id = ! empty( $posts ) ? $posts[0]->ID : 'none';
        error_log( '=== the_posts Filter ===' );
        error_log( 'Post ID: ' . $post_id );
        error_log( 'Post Title: ' . ( ! empty( $posts ) ? $posts[0]->post_title : 'none' ) );
    }
    return $posts;
}, 5, 2 );

// Debug: Log template_include
add_filter( 'template_include', function( $template ) {
    if ( is_singular( 'page' ) && ! is_admin() ) {
        error_log( '=== template_include Filter ===' );
        error_log( 'Post ID: ' . get_the_ID() );
        error_log( 'Post Title: ' . get_the_title() );
        error_log( 'Template: ' . $template );
    }
    return $template;
}, 999 );
```

Check WordPress debug log at `/wp-content/debug.log`

## ✅ Success Criteria

All of the following must be true:

- [x] Test 1 passes (CAD on US page shows Canada content)
- [x] Test 2 passes (USD on US page shows US content)
- [x] Test 3 passes (USD on Canada page shows US content)
- [x] Test 4 passes (CAD on Canada page shows Canada content)
- [x] No console errors
- [x] No PHP errors
- [x] URLs don't change
- [x] Page titles change correctly
- [x] Page content changes correctly

## 🚀 Next Steps After Testing

### If All Tests Pass ✅
1. Remove debug code from `functions.php`
2. Clear all caches
3. Deploy to production
4. Monitor for issues

### If Tests Fail ❌
1. Check troubleshooting section
2. Review debug log output
3. Verify metadata is set correctly
4. Verify currency mappings are correct
5. Check for conflicting plugins

---

**Testing Guide Version**: 2.1.0
**Last Updated**: 2025-10-17

