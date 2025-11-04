# LazyLoad Image Swap - Testing Guide

**Feature**: Product Image Hover Effect with LazyLoad Support  
**Date**: 2025-11-04  
**Version**: 1.1.0

---

## 🎯 Testing Objectives

1. ✅ Verify image swap works with LazyLoad active
2. ✅ Verify image restores correctly on mouseleave
3. ✅ Verify no blank/base64 images appear
4. ✅ Verify performance is acceptable
5. ✅ Verify backwards compatibility

---

## 🧪 Test Scenarios

### Test 1: Basic Image Swap (LazyLoad Active)

**Prerequisites:**
- LazyLoad plugin active (lazysizes, WP Rocket, etc.)
- Product has gallery images
- Browser cache cleared

**Steps:**
1. Navigate to product archive page (shop page)
2. Wait for page to fully load
3. Hover over a product image
4. Observe image changes to second gallery image
5. Move mouse away from image
6. Observe image restores to original

**Expected Results:**
- ✅ Hover shows second image smoothly
- ✅ Mouseleave restores original image (NOT blank/base64)
- ✅ No flickering or jumping
- ✅ Smooth transitions

**Pass Criteria:**
- [ ] Image swap works on hover
- [ ] Original image restored on mouseleave
- [ ] No blank images appear
- [ ] No console errors

---

### Test 2: Slow Network Simulation

**Prerequisites:**
- Chrome DevTools open
- Network throttling enabled (Slow 3G)

**Steps:**
1. Open Chrome DevTools (F12)
2. Go to Network tab
3. Set throttling to "Slow 3G"
4. Navigate to product archive page
5. Wait for images to start loading (but not complete)
6. Hover over product image while still loading
7. Move mouse away

**Expected Results:**
- ✅ Image swap works even during loading
- ✅ Original image restored correctly
- ✅ No race condition issues

**Pass Criteria:**
- [ ] Works during image loading
- [ ] No base64 placeholders on restore
- [ ] Graceful handling of slow loads

---

### Test 3: Multiple Rapid Hovers

**Prerequisites:**
- Product archive page loaded
- Multiple products visible

**Steps:**
1. Rapidly hover over multiple product images
2. Move mouse quickly between products
3. Hover and leave same product multiple times rapidly
4. Observe image behavior

**Expected Results:**
- ✅ Smooth transitions without flickering
- ✅ No stuck hover images
- ✅ Correct image restoration
- ✅ No memory leaks

**Pass Criteria:**
- [ ] No flickering
- [ ] No stuck states
- [ ] Smooth performance
- [ ] No console errors

---

### Test 4: AJAX-Loaded Products

**Prerequisites:**
- Infinite scroll or Load More enabled
- Initial products loaded

**Steps:**
1. Scroll to bottom of product list
2. Trigger AJAX load (infinite scroll or click "Load More")
3. Wait for new products to load
4. Hover over newly loaded product images
5. Move mouse away

**Expected Results:**
- ✅ Image swap works on AJAX-loaded products
- ✅ Original image restored correctly
- ✅ Same behavior as initial products

**Pass Criteria:**
- [ ] Works on AJAX-loaded products
- [ ] No initialization issues
- [ ] Consistent behavior

---

### Test 5: Without LazyLoad

**Prerequisites:**
- LazyLoad plugin disabled
- Browser cache cleared

**Steps:**
1. Disable LazyLoad plugin
2. Clear cache
3. Navigate to product archive page
4. Hover over product images
5. Move mouse away

**Expected Results:**
- ✅ Image swap still works
- ✅ Backwards compatible
- ✅ No errors in console

**Pass Criteria:**
- [ ] Works without LazyLoad
- [ ] No console errors
- [ ] Same smooth behavior

---

### Test 6: Different LazyLoad Plugins

**Prerequisites:**
- Test with multiple LazyLoad plugins

**Plugins to Test:**
1. **lazysizes** (most common)
2. **WP Rocket Lazy Load**
3. **a3 Lazy Load**
4. **Native browser lazy loading**

**Steps (for each plugin):**
1. Activate plugin
2. Clear cache
3. Navigate to product archive
4. Test hover and mouseleave
5. Verify correct behavior

**Expected Results:**
- ✅ Works with all LazyLoad plugins
- ✅ Consistent behavior across plugins

**Pass Criteria:**
- [ ] lazysizes: ✓
- [ ] WP Rocket: ✓
- [ ] a3 Lazy Load: ✓
- [ ] Native lazy loading: ✓

---

### Test 7: Browser Compatibility

**Browsers to Test:**
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile Safari (iOS)
- Chrome Mobile (Android)

**Steps (for each browser):**
1. Open product archive page
2. Test hover effect
3. Test mouseleave
4. Check console for errors

**Expected Results:**
- ✅ Works in all modern browsers
- ✅ Graceful degradation in older browsers

**Pass Criteria:**
- [ ] Chrome: ✓
- [ ] Firefox: ✓
- [ ] Safari: ✓
- [ ] Edge: ✓
- [ ] Mobile Safari: ✓
- [ ] Chrome Mobile: ✓

---

### Test 8: Console Validation

**Prerequisites:**
- Browser DevTools open

**Steps:**
1. Open Console tab in DevTools
2. Navigate to product archive page
3. Hover over products
4. Look for any warnings or errors

**Expected Results:**
- ✅ No errors in console
- ⚠️ May see warning: "Attempted to restore base64 placeholder" (this is expected and handled)
- ✅ No JavaScript errors

**Pass Criteria:**
- [ ] No JavaScript errors
- [ ] No unhandled exceptions
- [ ] Warning messages are informational only

---

### Test 9: Performance Testing

**Prerequisites:**
- Chrome DevTools Performance tab

**Steps:**
1. Open Performance tab
2. Start recording
3. Hover over 10 different products
4. Stop recording
5. Analyze performance

**Expected Results:**
- ✅ No significant performance degradation
- ✅ Smooth 60fps transitions
- ✅ No memory leaks

**Metrics to Check:**
- Frame rate: Should stay near 60fps
- Memory: Should not continuously increase
- CPU usage: Should be minimal

**Pass Criteria:**
- [ ] Frame rate ≥ 55fps
- [ ] No memory leaks
- [ ] CPU usage acceptable

---

### Test 10: Edge Cases

#### 10a: Product with No Gallery Images
**Steps:**
1. Find product with only featured image (no gallery)
2. Hover over image
3. Observe behavior

**Expected:**
- ✅ No hover effect (graceful degradation)
- ✅ No errors

#### 10b: Product with Broken Image URL
**Steps:**
1. Set hover image to broken URL (via browser DevTools)
2. Hover over image
3. Observe behavior

**Expected:**
- ✅ Graceful handling
- ✅ No JavaScript errors

#### 10c: Very Large Images
**Steps:**
1. Test with very large product images (>5MB)
2. Hover and mouseleave
3. Observe loading behavior

**Expected:**
- ✅ Preloading works
- ✅ Smooth transition when loaded

**Pass Criteria:**
- [ ] No gallery: Graceful ✓
- [ ] Broken URL: Handled ✓
- [ ] Large images: Works ✓

---

## 🔍 Debugging Checklist

If issues occur, check:

### 1. Console Errors
```javascript
// Open browser console and look for:
- "Attempted to restore base64 placeholder" (warning, not error)
- Any JavaScript errors
- Network errors for images
```

### 2. Image Attributes
```javascript
// In console, inspect image:
$('.wc-hover-image-enabled img').first().attr('src')
$('.wc-hover-image-enabled img').first().attr('data-src')
$('.wc-hover-image-enabled img').first().attr('srcset')
```

### 3. LazyLoad Detection
```javascript
// Check if LazyLoad is detected:
$('.wc-hover-image-enabled img').first().hasClass('lazyloaded')
$('.wc-hover-image-enabled img').first().attr('data-src')
```

### 4. Original Data Storage
```javascript
// Check stored original data:
$('.wc-hover-image-enabled').first().data('original-image-data')
```

---

## 📊 Test Results Template

```markdown
## Test Results - [Date]

**Tester**: [Name]
**Environment**: [Production/Staging/Local]
**Browser**: [Browser Name & Version]
**LazyLoad Plugin**: [Plugin Name & Version]

### Test Summary
- Total Tests: 10
- Passed: __
- Failed: __
- Skipped: __

### Detailed Results

| Test # | Test Name | Status | Notes |
|--------|-----------|--------|-------|
| 1 | Basic Image Swap | ✅/❌ | |
| 2 | Slow Network | ✅/❌ | |
| 3 | Rapid Hovers | ✅/❌ | |
| 4 | AJAX Products | ✅/❌ | |
| 5 | Without LazyLoad | ✅/❌ | |
| 6 | Different Plugins | ✅/❌ | |
| 7 | Browser Compat | ✅/❌ | |
| 8 | Console Validation | ✅/❌ | |
| 9 | Performance | ✅/❌ | |
| 10 | Edge Cases | ✅/❌ | |

### Issues Found
1. [Issue description]
2. [Issue description]

### Recommendations
1. [Recommendation]
2. [Recommendation]
```

---

## 🚀 Automated Testing (Future)

### Potential Automated Tests
```javascript
// Example Cypress test
describe('Product Image Swap', () => {
  it('should swap image on hover', () => {
    cy.visit('/shop');
    cy.get('.wc-hover-image-enabled').first().trigger('mouseenter');
    cy.get('.wc-hover-image-enabled img').first()
      .should('have.attr', 'src')
      .and('include', 'hover-image');
  });

  it('should restore original on mouseleave', () => {
    cy.visit('/shop');
    const originalSrc = cy.get('.wc-hover-image-enabled img').first()
      .invoke('attr', 'data-src');
    
    cy.get('.wc-hover-image-enabled').first().trigger('mouseenter');
    cy.get('.wc-hover-image-enabled').first().trigger('mouseleave');
    
    cy.get('.wc-hover-image-enabled img').first()
      .should('have.attr', 'src', originalSrc);
  });
});
```

---

## 📝 Notes

### Known Limitations
- Requires jQuery
- Requires product to have gallery images
- May show warning in console (expected behavior)

### Performance Considerations
- Preloading happens on first hover (minimal impact)
- Event listeners are efficiently managed
- No memory leaks detected in testing

---

## ✅ Sign-off

**Tested By**: _______________  
**Date**: _______________  
**Status**: ✅ Approved / ❌ Needs Work  
**Signature**: _______________

---

**Next Steps After Testing:**
1. Document any issues found
2. Create tickets for bugs
3. Update documentation if needed
4. Deploy to production (if all tests pass)

