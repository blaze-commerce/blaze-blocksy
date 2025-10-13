# WooCommerce Block Extensions - Testing Guide

## Quick Testing Checklist

Use this guide to quickly test all features of the WooCommerce Block Extensions.

## Prerequisites

Before testing, ensure:
- [ ] WordPress 6.0+ is installed
- [ ] WooCommerce 8.0+ is active
- [ ] Blocksy theme is active
- [ ] Blocksy Companion plugin is active
- [ ] At least 10 test products exist
- [ ] Test products have gallery images (for hover feature)

## Test 1: Product Collection - Responsive Settings

### Setup (5 minutes)

1. **Create Test Page**
   - Go to Pages → Add New
   - Title: "Test Product Collection Responsive"

2. **Add Product Collection Block**
   - Click (+) to add block
   - Search for "Product Collection"
   - Add the block

3. **Configure Basic Settings**
   - Choose "Product Catalog" or "Featured Products"
   - Ensure at least 8 products are available

### Test Responsive Controls (10 minutes)

#### Enable Responsive Mode
- [ ] Find "Responsive Settings" panel in block sidebar
- [ ] Toggle "Enable Responsive Layout" to ON
- [ ] Verify panel expands with controls

#### Configure Desktop Settings
- [ ] Set "Desktop Columns" to 4
- [ ] Set "Desktop Products" to 8
- [ ] Verify settings save

#### Configure Tablet Settings
- [ ] Set "Tablet Columns" to 3
- [ ] Set "Tablet Products" to 6
- [ ] Verify settings save

#### Configure Mobile Settings
- [ ] Set "Mobile Columns" to 2
- [ ] Set "Mobile Products" to 4
- [ ] Verify settings save

#### Save and Preview
- [ ] Click "Update" or "Publish"
- [ ] Click "Preview" → "Preview in new tab"

### Frontend Testing (15 minutes)

#### Desktop View (≥1024px)
- [ ] Open preview in desktop browser
- [ ] Verify 4 columns are displayed
- [ ] Count products - should show 8
- [ ] Check spacing between products
- [ ] Verify grid layout is even

#### Tablet View (768px-1023px)
- [ ] Resize browser to ~900px width
- [ ] Verify layout changes to 3 columns
- [ ] Count products - should show 6
- [ ] Check that 2 products are hidden
- [ ] Verify smooth transition

#### Mobile View (<768px)
- [ ] Resize browser to ~375px width
- [ ] Verify layout changes to 2 columns
- [ ] Count products - should show 4
- [ ] Check that 4 products are hidden
- [ ] Verify mobile-friendly spacing

#### Resize Testing
- [ ] Slowly resize browser from wide to narrow
- [ ] Verify smooth transitions at breakpoints
- [ ] Check no layout jumping or flickering
- [ ] Resize back to wide - verify returns to 4 columns

### Expected Results
✅ **Pass**: Layout adjusts smoothly at each breakpoint with correct columns and product counts  
❌ **Fail**: Layout doesn't change, products don't hide/show, or layout breaks

---

## Test 2: Product Image - Hover Image Swap

### Setup (5 minutes)

1. **Prepare Test Products**
   - Go to Products → All Products
   - Edit a product
   - Ensure it has at least 2 images:
     - Main product image
     - At least 1 gallery image
   - Save product
   - Repeat for 3-5 products

2. **Use Existing Product Collection**
   - Use the page created in Test 1
   - Or create new page with Product Collection block

### Enable Hover Image (5 minutes)

1. **Select Product Image Block**
   - Click on a product image in the Product Collection
   - This should select the Product Image block
   - Check block breadcrumb shows "Product Image"

2. **Enable Hover Feature**
   - [ ] Find "Image Enhancements" panel in sidebar
   - [ ] Toggle "Enable Hover Image" to ON
   - [ ] Save page

### Frontend Testing (10 minutes)

#### Hover Functionality
- [ ] Open page in browser
- [ ] Hover mouse over first product image
- [ ] Verify second image appears
- [ ] Verify transition is smooth (no flash)
- [ ] Move mouse away
- [ ] Verify original image returns

#### Multiple Products
- [ ] Test hover on 3-5 different products
- [ ] Verify each shows its own gallery image
- [ ] Check no images are mixed up between products

#### Products Without Gallery
- [ ] Find a product without gallery images
- [ ] Hover over it
- [ ] Verify no error occurs
- [ ] Verify image doesn't change (expected behavior)

#### Performance
- [ ] Hover rapidly between multiple products
- [ ] Check for smooth transitions
- [ ] Verify no lag or delay
- [ ] Check browser console for errors

### Expected Results
✅ **Pass**: Second image appears smoothly on hover and original returns on mouse leave  
❌ **Fail**: No image change, images flash, wrong images show, or errors occur

---

## Test 3: Product Image - Wishlist Button

### Setup (5 minutes)

1. **Verify Blocksy Wishlist**
   - Go to Blocksy → Extensions
   - Ensure "WooCommerce Extra" is enabled
   - Check wishlist is configured

2. **Use Existing Product Collection**
   - Use the page from previous tests

### Enable Wishlist Button (5 minutes)

1. **Select Product Image Block**
   - Click on a product image
   - Verify "Product Image" block is selected

2. **Enable Wishlist**
   - [ ] Find "Image Enhancements" panel
   - [ ] Toggle "Show Wishlist Button" to ON
   - [ ] Verify "Wishlist Button Position" dropdown appears

3. **Test All Positions**
   - [ ] Select "Top Right" - Save and preview
   - [ ] Select "Top Left" - Save and preview
   - [ ] Select "Bottom Right" - Save and preview
   - [ ] Select "Bottom Left" - Save and preview

### Frontend Testing - Desktop (15 minutes)

#### Button Visibility
- [ ] Open page in browser
- [ ] Hover over product image
- [ ] Verify wishlist button appears
- [ ] Check button is in correct position
- [ ] Move mouse away
- [ ] Verify button fades out

#### Add to Wishlist
- [ ] Hover over product image
- [ ] Click wishlist button
- [ ] Verify button changes to filled heart
- [ ] Check success message appears
- [ ] Verify message says "Added to wishlist"
- [ ] Check wishlist count in header increases

#### Remove from Wishlist
- [ ] Click wishlist button again (on same product)
- [ ] Verify button changes to unfilled heart
- [ ] Check success message appears
- [ ] Verify message says "Removed from wishlist"
- [ ] Check wishlist count in header decreases

#### Multiple Products
- [ ] Add 3 different products to wishlist
- [ ] Verify each button updates independently
- [ ] Check wishlist count shows correct total
- [ ] Remove 1 product
- [ ] Verify count updates correctly

#### Persistence
- [ ] Add products to wishlist
- [ ] Refresh page
- [ ] Verify added products still show filled hearts
- [ ] Check wishlist count is correct

### Frontend Testing - Mobile (10 minutes)

#### Mobile Visibility
- [ ] Open page on mobile device or resize to <768px
- [ ] Verify wishlist button is always visible (no hover needed)
- [ ] Check button size is appropriate for touch
- [ ] Verify button doesn't overlap image content

#### Touch Interaction
- [ ] Tap wishlist button
- [ ] Verify it responds to touch
- [ ] Check success message appears
- [ ] Verify message is readable on mobile
- [ ] Test on multiple products

### User State Testing (10 minutes)

#### Logged-in User
- [ ] Log in to WordPress
- [ ] Add products to wishlist
- [ ] Go to Blocksy wishlist page
- [ ] Verify products appear in wishlist
- [ ] Remove from wishlist page
- [ ] Verify button updates on product page

#### Guest User (if enabled)
- [ ] Log out
- [ ] Add products to wishlist
- [ ] Verify wishlist saves (cookie/session)
- [ ] Refresh page
- [ ] Check wishlist persists
- [ ] Clear cookies
- [ ] Verify wishlist resets

### Error Handling (5 minutes)

#### Network Error Simulation
- [ ] Open browser DevTools
- [ ] Go to Network tab
- [ ] Set throttling to "Offline"
- [ ] Try to add to wishlist
- [ ] Verify error message appears
- [ ] Check message is user-friendly
- [ ] Set back to "Online"
- [ ] Verify functionality resumes

### Expected Results
✅ **Pass**: Wishlist button appears, adds/removes products, updates state, and shows messages  
❌ **Fail**: Button doesn't appear, clicks don't work, state doesn't update, or errors occur

---

## Test 4: Cross-Browser Compatibility

### Desktop Browsers (20 minutes)

#### Chrome
- [ ] Test all features in Chrome
- [ ] Check console for errors
- [ ] Verify all animations work

#### Firefox
- [ ] Test all features in Firefox
- [ ] Check console for errors
- [ ] Verify all animations work

#### Safari
- [ ] Test all features in Safari
- [ ] Check console for errors
- [ ] Verify all animations work

#### Edge
- [ ] Test all features in Edge
- [ ] Check console for errors
- [ ] Verify all animations work

### Mobile Browsers (15 minutes)

#### Mobile Safari (iOS)
- [ ] Test on iPhone or iPad
- [ ] Verify responsive layout
- [ ] Test hover image (should work on tap)
- [ ] Test wishlist button
- [ ] Check touch interactions

#### Mobile Chrome (Android)
- [ ] Test on Android device
- [ ] Verify responsive layout
- [ ] Test hover image
- [ ] Test wishlist button
- [ ] Check touch interactions

---

## Test 5: Accessibility Testing

### Keyboard Navigation (10 minutes)

- [ ] Tab to wishlist button
- [ ] Verify focus indicator is visible
- [ ] Press Enter to activate
- [ ] Verify wishlist toggles
- [ ] Tab through multiple products
- [ ] Check focus order is logical

### Screen Reader Testing (15 minutes)

- [ ] Enable screen reader (VoiceOver, NVDA, or JAWS)
- [ ] Navigate to product collection
- [ ] Verify products are announced
- [ ] Tab to wishlist button
- [ ] Verify button label is announced
- [ ] Activate button
- [ ] Verify state change is announced

### Color Contrast (5 minutes)

- [ ] Use browser DevTools or contrast checker
- [ ] Check wishlist button contrast
- [ ] Verify meets WCAG AA (4.5:1 minimum)
- [ ] Check success message contrast
- [ ] Verify all text is readable

---

## Test 6: Performance Testing

### Page Load (10 minutes)

- [ ] Open browser DevTools → Network tab
- [ ] Load page with Product Collection
- [ ] Check total page size
- [ ] Verify CSS/JS files load
- [ ] Check load time is acceptable (<3s)
- [ ] Verify no 404 errors

### Runtime Performance (10 minutes)

- [ ] Open DevTools → Performance tab
- [ ] Start recording
- [ ] Resize browser window
- [ ] Hover over multiple products
- [ ] Click wishlist buttons
- [ ] Stop recording
- [ ] Check for performance issues
- [ ] Verify no long tasks (>50ms)

---

## Test 7: Edge Cases

### No Products (5 minutes)
- [ ] Create Product Collection with no matching products
- [ ] Verify no errors occur
- [ ] Check empty state displays correctly

### Single Product (5 minutes)
- [ ] Create Product Collection with only 1 product
- [ ] Enable responsive mode
- [ ] Verify layout doesn't break
- [ ] Check product displays correctly

### Many Products (5 minutes)
- [ ] Set Desktop Products to 20
- [ ] Verify all 20 load
- [ ] Check performance is acceptable
- [ ] Test responsive behavior

### No Gallery Images (5 minutes)
- [ ] Enable hover image on product without gallery
- [ ] Verify no error occurs
- [ ] Check image doesn't change (expected)
- [ ] Verify console has no errors

---

## Bug Report Template

If you find issues, use this template:

```
**Issue**: [Brief description]
**Feature**: [Product Collection / Hover Image / Wishlist]
**Steps to Reproduce**:
1. [Step 1]
2. [Step 2]
3. [Step 3]

**Expected**: [What should happen]
**Actual**: [What actually happened]
**Browser**: [Chrome/Firefox/Safari/Edge + version]
**Device**: [Desktop/Mobile + OS]
**Console Errors**: [Any JavaScript errors]
**Screenshots**: [If applicable]
```

---

## Testing Summary Checklist

After completing all tests, verify:

- [ ] All Product Collection responsive features work
- [ ] Hover image swaps correctly
- [ ] Wishlist button adds/removes products
- [ ] All 4 button positions work
- [ ] Works in all major browsers
- [ ] Mobile experience is good
- [ ] Keyboard navigation works
- [ ] Screen reader compatible
- [ ] No console errors
- [ ] Performance is acceptable

---

**Testing Time**: ~2-3 hours for complete testing  
**Recommended**: Test in staging environment first  
**Priority**: High priority tests marked with ⭐

