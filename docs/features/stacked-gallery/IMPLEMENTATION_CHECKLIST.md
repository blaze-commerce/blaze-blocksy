# Implementation Checklist

Use this checklist to ensure complete and correct implementation of the gallery stacked modification.

---

## Pre-Implementation

### Environment Check
- [ ] WordPress version 6.0 or higher
- [ ] WooCommerce plugin installed and activated
- [ ] Blocksy parent theme installed (not activated yet)
- [ ] Access to theme files (FTP/cPanel/Local)
- [ ] Backup created (database + files)

### Understanding
- [ ] Read `TECHNICAL_DOCUMENTATION_GALLERY_MODIFICATION.md`
- [ ] Read `QUICK_REFERENCE_GUIDE.md`
- [ ] Reviewed `IMPLEMENTATION_EXAMPLE.md`
- [ ] Understand desktop vs mobile behavior
- [ ] Understand breakpoint (1024px)

---

## Phase 1: Child Theme Setup

### File Structure
- [ ] Created `blocksy-child` folder in `wp-content/themes/`
- [ ] Created `assets` folder in `blocksy-child/`
- [ ] Created `assets/css` folder
- [ ] Created `assets/js` folder

### Core Files
- [ ] Created `style.css` with proper WordPress header
- [ ] Created `functions.php`
- [ ] Verified `style.css` has `Template: blocksy` line
- [ ] Verified `functions.php` starts with `<?php`

### File Permissions
- [ ] All folders have 755 permissions
- [ ] All files have 644 permissions
- [ ] Files are readable by web server

---

## Phase 2: Code Implementation

### CSS File
- [ ] Created `assets/css/gallery-stacked.css`
- [ ] Copied complete CSS from `IMPLEMENTATION_EXAMPLE.md`
- [ ] Verified all `@media (min-width: 1024px)` queries present
- [ ] Verified `!important` flags on critical rules
- [ ] No syntax errors (validate with CSS validator)

### JavaScript File
- [ ] Created `assets/js/gallery-stacked.js`
- [ ] Copied complete JS from `IMPLEMENTATION_EXAMPLE.md`
- [ ] Verified jQuery wrapper `(function($) { ... })(jQuery);`
- [ ] Verified CONFIG object present
- [ ] No syntax errors (validate with JSHint/ESLint)

### Functions File
- [ ] Copied complete code from `IMPLEMENTATION_EXAMPLE.md`
- [ ] Verified `wp_enqueue_scripts` action with priority 5
- [ ] Verified CSS enqueued with dependency on `blocksy-styles`
- [ ] Verified JS enqueued only on `is_product()`
- [ ] Verified JS loads in header (5th parameter = `false`)
- [ ] Verified body class filter added
- [ ] Verified flexy args filter added

---

## Phase 3: Activation

### Theme Activation
- [ ] Logged into WordPress Admin
- [ ] Navigated to Appearance → Themes
- [ ] Located "Blocksy Child" theme
- [ ] Clicked "Activate"
- [ ] No PHP errors displayed
- [ ] Site frontend still loads

### Initial Verification
- [ ] Visited homepage - no errors
- [ ] Visited shop page - no errors
- [ ] Visited single product page - page loads
- [ ] Checked browser console - no JavaScript errors
- [ ] Checked Network tab - CSS and JS files load (200 status)

---

## Phase 4: Desktop Testing (≥1024px)

### Layout Verification
- [ ] Opened product with 3+ images on desktop
- [ ] Thumbnails visible on left side
- [ ] All thumbnails visible (no slider)
- [ ] Thumbnail width is 120px (measure with DevTools)
- [ ] All main images visible (stacked vertically)
- [ ] Gap between images is 18px (measure with DevTools)
- [ ] No slider arrows visible on main images
- [ ] No slider arrows visible on thumbnails

### Thumbnail Interaction
- [ ] Clicked first thumbnail
- [ ] Page scrolls to first image
- [ ] Scroll is smooth (not instant jump)
- [ ] Image appears ~100px from top of viewport
- [ ] Active class added to clicked thumbnail
- [ ] Clicked third thumbnail
- [ ] Page scrolls to third image
- [ ] Active class moved to third thumbnail

### Image Features
- [ ] Badge visible on images (if product on sale)
- [ ] Badge positioned correctly (top-left or top-right)
- [ ] Clicked main image
- [ ] Lightbox (PhotoSwipe) opens
- [ ] Lightbox shows correct image
- [ ] Can navigate in lightbox
- [ ] Lightbox close button works
- [ ] Hovered over image (if zoom enabled)
- [ ] Zoom functionality works

### Video Support (if applicable)
- [ ] Product with video in gallery
- [ ] Video displays inline (not placeholder)
- [ ] Video can play
- [ ] Video controls work

### Edge Cases
- [ ] Product with 1 image only - no errors
- [ ] Product with 10+ images - all display correctly
- [ ] Product with no images - no errors
- [ ] Scrolled page manually - no layout issues

---

## Phase 5: Mobile Testing (<1024px)

### Layout Verification
- [ ] Opened product on mobile (or resize browser to <1024px)
- [ ] Only 1 main image visible (slider mode)
- [ ] Thumbnails visible below main image
- [ ] Slider arrows visible
- [ ] Pills/dots visible (if many images)

### Slider Interaction
- [ ] Swiped left on main image
- [ ] Image changes to next
- [ ] Swiped right on main image
- [ ] Image changes to previous
- [ ] Clicked right arrow
- [ ] Image changes to next
- [ ] Clicked left arrow
- [ ] Image changes to previous

### Thumbnail Interaction
- [ ] Clicked thumbnail
- [ ] Main image changes to clicked image
- [ ] Active state updates on thumbnail

### Image Features
- [ ] Badge visible on main image
- [ ] Clicked main image
- [ ] Lightbox opens (if enabled on mobile)
- [ ] Lightbox works correctly

---

## Phase 6: Responsive Testing

### Breakpoint Transition
- [ ] Opened product on desktop (>1024px)
- [ ] Verified stacked layout active
- [ ] Resized browser to <1024px
- [ ] Layout changes to slider
- [ ] Resized browser to >1024px
- [ ] Layout changes back to stacked
- [ ] No JavaScript errors during resize

### Different Screen Sizes
- [ ] Tested at 1920px width (large desktop)
- [ ] Tested at 1366px width (laptop)
- [ ] Tested at 1024px width (tablet landscape)
- [ ] Tested at 768px width (tablet portrait)
- [ ] Tested at 375px width (mobile)

---

## Phase 7: Browser Compatibility

### Desktop Browsers
- [ ] Chrome (latest) - all features work
- [ ] Firefox (latest) - all features work
- [ ] Safari (latest) - all features work
- [ ] Edge (latest) - all features work

### Mobile Browsers
- [ ] Chrome Mobile - slider works
- [ ] Safari iOS - slider works
- [ ] Samsung Internet - slider works

### Smooth Scroll Fallback
- [ ] Tested in Safari <15.4 (if available)
- [ ] Scroll still works (even if not smooth)

---

## Phase 8: WooCommerce Features

### Variable Products
- [ ] Opened variable product
- [ ] Selected different variation
- [ ] Gallery updates correctly
- [ ] Stacked layout maintained on desktop
- [ ] Slider maintained on mobile
- [ ] No JavaScript errors

### Product Types
- [ ] Simple product - works
- [ ] Variable product - works
- [ ] Grouped product - works (if has gallery)
- [ ] External product - works (if has gallery)

### Quick View (if enabled)
- [ ] Opened quick view modal
- [ ] Gallery displays correctly
- [ ] No conflicts with stacked layout

---

## Phase 9: Performance

### Page Load
- [ ] Page loads in <3 seconds (on good connection)
- [ ] No render-blocking resources
- [ ] CSS loads before content renders
- [ ] JS loads without blocking

### Scroll Performance
- [ ] Smooth scroll is smooth (not janky)
- [ ] No layout shifts during scroll
- [ ] Images don't flicker

### Lazy Loading
- [ ] Images lazy load as expected
- [ ] Lazy loading doesn't break stacked layout
- [ ] Images load when scrolling down

---

## Phase 10: Accessibility

### Keyboard Navigation
- [ ] Tabbed to thumbnails
- [ ] Thumbnails receive focus
- [ ] Focus outline visible
- [ ] Pressed Enter on thumbnail
- [ ] Page scrolls to image

### Screen Reader
- [ ] Thumbnails have aria-labels
- [ ] Images have alt text
- [ ] Screen reader can navigate gallery

### Focus Management
- [ ] Focus visible on all interactive elements
- [ ] Focus order is logical
- [ ] No focus traps

---

## Phase 11: Code Quality

### CSS Validation
- [ ] Validated CSS with W3C CSS Validator
- [ ] No critical errors
- [ ] Warnings reviewed and acceptable

### JavaScript Validation
- [ ] Validated JS with JSHint or ESLint
- [ ] No errors
- [ ] No unused variables
- [ ] Proper use of strict mode

### PHP Validation
- [ ] No PHP errors in error log
- [ ] Functions properly namespaced/prefixed
- [ ] Proper use of WordPress hooks

### Code Comments
- [ ] CSS has section comments
- [ ] JS has function comments
- [ ] PHP has docblocks

---

## Phase 12: Cleanup

### Debug Code
- [ ] Set `CONFIG.debug = false` in JS
- [ ] Removed or commented out debug CSS
- [ ] Removed console.log statements (if any added)

### File Optimization
- [ ] Minified CSS (optional, for production)
- [ ] Minified JS (optional, for production)
- [ ] Removed unused code

### Documentation
- [ ] Added inline comments where needed
- [ ] Created README.md in child theme (optional)
- [ ] Documented any customizations made

---

## Phase 13: Final Testing

### Regression Testing
- [ ] Re-tested all desktop features
- [ ] Re-tested all mobile features
- [ ] Re-tested all browsers
- [ ] Re-tested variable products

### User Acceptance
- [ ] Showed to client/stakeholder
- [ ] Received approval
- [ ] Addressed any feedback

### Production Readiness
- [ ] All tests passed
- [ ] No console errors
- [ ] No PHP errors
- [ ] Performance acceptable
- [ ] Accessibility acceptable

---

## Phase 14: Deployment (if applicable)

### Pre-Deployment
- [ ] Created backup of production site
- [ ] Tested on staging environment
- [ ] Documented deployment steps

### Deployment
- [ ] Uploaded child theme to production
- [ ] Activated child theme
- [ ] Cleared all caches (server, CDN, browser)
- [ ] Verified site loads correctly

### Post-Deployment
- [ ] Tested on production environment
- [ ] Monitored error logs
- [ ] Checked analytics for issues
- [ ] Confirmed with stakeholders

---

## Phase 15: Maintenance

### Documentation
- [ ] Saved all documentation files
- [ ] Documented in project wiki/notes
- [ ] Shared with team members

### Monitoring
- [ ] Set up error monitoring (if available)
- [ ] Scheduled periodic checks
- [ ] Documented known issues (if any)

### Future Updates
- [ ] Noted compatibility with current versions
- [ ] Planned for parent theme updates
- [ ] Planned for WordPress/WooCommerce updates

---

## Troubleshooting Reference

### If stacked layout doesn't work on desktop:
1. Check if `.ct-has-stacked-gallery` class on `<body>`
2. Check if CSS file loads (Network tab)
3. Check CSS specificity (DevTools)
4. Check breakpoint (window.innerWidth >= 1024)

### If slider doesn't work on mobile:
1. Check if custom CSS wrapped in `@media (min-width: 1024px)`
2. Check if JS allows Flexy on mobile
3. Check parent theme JS loads

### If thumbnail click doesn't scroll:
1. Check browser console for JS errors
2. Check if event listeners attached
3. Enable debug mode in JS
4. Check scroll offset calculation

### If lightbox doesn't work:
1. Check if PhotoSwipe library loads
2. Check if HTML structure unchanged
3. Check parent theme lightbox settings
4. Test without child theme active

---

## Sign-Off

### Developer
- [ ] All checklist items completed
- [ ] Code reviewed
- [ ] Ready for handoff

**Developer Name**: ___________________  
**Date**: ___________________  
**Signature**: ___________________

### QA/Tester
- [ ] All tests passed
- [ ] Issues documented
- [ ] Approved for deployment

**Tester Name**: ___________________  
**Date**: ___________________  
**Signature**: ___________________

### Client/Stakeholder
- [ ] Reviewed functionality
- [ ] Meets requirements
- [ ] Approved for production

**Name**: ___________________  
**Date**: ___________________  
**Signature**: ___________________

---

## Notes

Use this section to document any issues, customizations, or important information:

```
[Add notes here]
```

---

**Checklist Version**: 1.0  
**Last Updated**: 2025-10-29  
**Total Items**: 200+

