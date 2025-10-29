# Frequently Asked Questions (FAQ)

Common questions and answers about the gallery stacked modification.

---

## General Questions

### Q1: Why use a child theme instead of modifying the parent theme?
**A:** Child themes allow you to:
- Preserve customizations when parent theme updates
- Safely override parent theme functionality
- Easily revert changes by deactivating child theme
- Follow WordPress best practices

### Q2: Will this modification break when Blocksy updates?
**A:** No, as long as:
- You only modify child theme files (never parent theme)
- Blocksy doesn't drastically change its gallery structure
- You test after each Blocksy update

### Q3: Can I use this with other WooCommerce themes?
**A:** Not directly. This is specifically designed for Blocksy theme. Other themes would require:
- Different CSS selectors
- Different JavaScript hooks
- Different PHP filters
- Custom implementation based on that theme's structure

### Q4: Do I need coding knowledge to implement this?
**A:** Basic knowledge required:
- **Minimal**: Copy-paste from `IMPLEMENTATION_EXAMPLE.md`
- **Recommended**: Understanding of HTML, CSS, JavaScript, PHP
- **Ideal**: WordPress theme development experience

---

## Technical Questions

### Q5: Why is the breakpoint 1024px?
**A:** 
- Standard tablet landscape width
- Matches common responsive design patterns
- Provides enough space for thumbnails + images
- Can be customized if needed (see customization section)

### Q6: Why load JavaScript in header instead of footer?
**A:**
- Must run BEFORE parent theme's Flexy initialization
- Parent theme loads Flexy early
- Header loading ensures our script runs first
- Prevents Flexy from initializing on desktop

### Q7: Why use `!important` in CSS?
**A:**
- Parent theme has specific selectors
- Need to override slider transforms
- Ensures stacked layout takes precedence
- Alternative: Use even higher specificity (longer selectors)

### Q8: Can I remove the `!important` flags?
**A:** Yes, but you'll need to:
- Increase CSS specificity
- Use longer, more specific selectors
- Test thoroughly to ensure overrides work
- Example: `.ct-has-stacked-gallery.single-product .woocommerce-product-gallery.thumbs-left .flexy-items`

### Q9: Why is thumbnail width 120px?
**A:**
- Provides good visibility
- Doesn't take too much horizontal space
- Matches common e-commerce patterns
- Can be customized (see `IMPLEMENTATION_EXAMPLE.md`)

### Q10: Why is the scroll offset 100px?
**A:**
- Provides breathing room from top
- Accounts for potential sticky headers
- Ensures image is clearly visible
- Can be customized in JavaScript CONFIG

---

## Functionality Questions

### Q11: Will the lightbox still work?
**A:** Yes, because:
- HTML structure remains unchanged
- Parent theme's PhotoSwipe integration intact
- All images have required data attributes
- Click handlers from parent theme still function

### Q12: Will zoom on hover still work?
**A:** Yes, because:
- Parent theme's zoom functionality preserved
- Each image maintains zoom initialization
- No conflicts with stacked layout
- Works on each individual stacked image

### Q13: Will badges (SALE, SOLD OUT) still display?
**A:** Yes, because:
- Badges rendered by parent theme
- Each image has its own badge container
- CSS positioning maintained
- No modifications to badge logic

### Q14: What happens with video in gallery?
**A:** Videos:
- Display inline (not as placeholder)
- Can be played directly
- Maintain aspect ratio
- Work with lightbox

### Q15: How does it work with variable products?
**A:** 
- Variation change detected via WooCommerce event
- Child theme re-initializes after variation change
- Stacked layout maintained
- New images display correctly

---

## Layout Questions

### Q16: Can I change the thumbnail position (right instead of left)?
**A:** Yes, but requires:
- Modifying CSS to position thumbnails on right
- Adjusting main images margin
- Testing thoroughly
- Not covered in current documentation

### Q17: Can I change the gap between images?
**A:** Yes, easily:
```css
.ct-has-stacked-gallery .flexy-items {
    gap: 24px !important; /* Change from 18px */
}
```

### Q18: Can thumbnails be horizontal on desktop?
**A:** Not recommended because:
- Defeats purpose of stacked layout
- Would require significant CSS changes
- Mobile already has horizontal thumbnails
- Current design is optimized for vertical

### Q19: What if I have 20+ images?
**A:** 
- All will display (very long page)
- Consider performance impact
- Lazy loading helps
- May want to limit images or use pagination (custom development)

### Q20: Can I limit how many images are stacked?
**A:** Yes, requires custom development:
- Add PHP filter to limit images array
- Or use CSS to hide images beyond certain count
- Or implement "Load More" button (advanced)

---

## Mobile Questions

### Q21: Why keep the slider on mobile?
**A:**
- Better UX on small screens
- Stacked layout would be too long
- Swipe gestures natural on mobile
- Follows mobile e-commerce best practices

### Q22: Can I make mobile also stacked?
**A:** Yes, but not recommended:
- Remove `@media (min-width: 1024px)` wrapper
- Apply styles to all screen sizes
- Very long scroll on mobile
- Poor user experience

### Q23: What about tablet (768px - 1023px)?
**A:**
- Currently uses mobile slider
- Can be customized with additional breakpoint
- Would require separate media query
- Test thoroughly on actual tablets

---

## Performance Questions

### Q24: Will this slow down my site?
**A:** Minimal impact:
- Small CSS file (~5KB)
- Small JS file (~3KB)
- No additional HTTP requests
- Lazy loading still works

### Q25: Do all images load at once?
**A:** Depends on lazy loading:
- If enabled: Images load as you scroll (recommended)
- If disabled: All images load immediately
- Parent theme controls lazy loading
- No changes to loading behavior

### Q26: Is smooth scroll performance good?
**A:** Generally yes:
- Native browser smooth scroll
- Hardware accelerated
- May be slightly janky with 10+ images
- Can disable smooth scroll if needed

---

## Compatibility Questions

### Q27: What WordPress version is required?
**A:** 
- Minimum: WordPress 6.0
- Recommended: Latest stable version
- Tested up to: 6.4+

### Q28: What WooCommerce version is required?
**A:**
- Minimum: WooCommerce 7.0
- Recommended: Latest stable version
- Tested up to: 8.0+

### Q29: What PHP version is required?
**A:**
- Minimum: PHP 7.4
- Recommended: PHP 8.0+
- Tested up to: PHP 8.2

### Q30: Does it work with page builders?
**A:** Depends:
- **Elementor**: May conflict if product template customized
- **Gutenberg**: Should work fine
- **WPBakery**: May conflict
- **Beaver Builder**: May conflict
- Test thoroughly with your specific page builder

### Q31: Does it work with other WooCommerce plugins?
**A:** Generally yes, but test with:
- Variation swatches plugins
- Quick view plugins
- Gallery enhancement plugins
- Product customizer plugins

---

## Troubleshooting Questions

### Q32: Images are still in slider mode on desktop. Why?
**A:** Check:
1. Is viewport actually ≥1024px? (Check DevTools)
2. Is child theme activated?
3. Is CSS file loading? (Check Network tab)
4. Is `.ct-has-stacked-gallery` class on body?
5. Clear browser cache

### Q33: Thumbnails don't scroll to images. Why?
**A:** Check:
1. Browser console for JavaScript errors
2. Is JS file loading? (Check Network tab)
3. Are event listeners attached? (Enable debug mode)
4. Is jQuery loaded?

### Q34: Lightbox doesn't open. Why?
**A:** Check:
1. Is PhotoSwipe library loaded?
2. Are there JavaScript errors?
3. Test with parent theme only (isolate issue)
4. Check if HTML structure was accidentally modified

### Q35: Mobile slider is broken. Why?
**A:** Check:
1. Is custom CSS wrapped in `@media (min-width: 1024px)`?
2. Is JavaScript allowing Flexy on mobile?
3. Clear cache
4. Test with child theme deactivated

### Q36: Styles are not applying. Why?
**A:** Check:
1. CSS file path correct in `functions.php`?
2. File permissions (should be 644)
3. CSS syntax errors? (Validate)
4. Specificity too low? (Add `!important`)
5. Clear all caches (browser, server, CDN)

---

## Customization Questions

### Q37: How do I change the breakpoint to 1200px?
**A:** 
1. In `gallery-stacked.js`: Change `breakpoint: 1024` to `breakpoint: 1200`
2. In `gallery-stacked.css`: Change all `@media (min-width: 1024px)` to `@media (min-width: 1200px)`
3. Test thoroughly

### Q38: How do I change thumbnail width to 150px?
**A:** In `gallery-stacked.css`, change:
```css
--thumbs-width: 120px;  /* to 150px */
width: 120px;           /* to 150px */
flex: 0 0 120px;        /* to 0 0 150px */
margin-left: calc(120px + ...);  /* to calc(150px + ...) */
```

### Q39: How do I disable smooth scroll?
**A:** In `gallery-stacked.js`, change:
```javascript
scrollBehavior: 'smooth'  // to 'auto'
```

### Q40: How do I add animation to stacked images?
**A:** Add to `gallery-stacked.css`:
```css
.ct-has-stacked-gallery .flexy-items > * {
    opacity: 0;
    animation: fadeInUp 0.5s ease forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

---

## Maintenance Questions

### Q41: How do I update the child theme?
**A:**
1. Backup current child theme
2. Make changes to files
3. Update version number in `style.css`
4. Clear all caches
5. Test thoroughly

### Q42: How do I debug issues?
**A:**
1. Enable debug mode in JS: `debug: true`
2. Check browser console
3. Use DevTools to inspect elements
4. Uncomment debug CSS
5. Test with parent theme only to isolate

### Q43: How do I revert to original gallery?
**A:**
1. Deactivate child theme
2. Activate parent theme
3. Or delete child theme files
4. Clear caches

### Q44: Can I use this on multiple sites?
**A:** Yes:
- Copy entire child theme folder
- Upload to each site
- Activate on each site
- Test on each site (themes may differ)

---

## Best Practices Questions

### Q45: Should I minify CSS and JS?
**A:**
- **Development**: No (easier to debug)
- **Production**: Yes (better performance)
- Use build tools or plugins
- Keep unminified versions for editing

### Q46: Should I use a CDN?
**A:**
- Not necessary for child theme files
- Parent theme may use CDN
- If using CDN, ensure child theme files included
- Test thoroughly

### Q47: How often should I test?
**A:**
- After any code changes
- After WordPress updates
- After WooCommerce updates
- After Blocksy theme updates
- Monthly (preventive)

### Q48: Should I document my customizations?
**A:** Absolutely:
- Keep notes of changes made
- Document custom CSS/JS
- Note version numbers
- Share with team
- Future you will thank you

---

## Support Questions

### Q49: Where can I get help?
**A:**
1. Review all documentation files
2. Check troubleshooting sections
3. Enable debug mode
4. Search WordPress forums
5. Contact theme support (for parent theme issues)

### Q50: Can I hire someone to implement this?
**A:** Yes:
- WordPress developers
- WooCommerce specialists
- Freelance platforms (Upwork, Fiverr)
- Provide this documentation package
- Ensure they understand requirements

---

## Quick Reference

**Most Common Issues:**
1. Flexy still initializes → JS loads too late
2. Styles not applying → CSS specificity too low
3. Scroll doesn't work → JS errors in console
4. Mobile broken → CSS not wrapped in media query
5. Lightbox broken → HTML structure changed

**Most Common Customizations:**
1. Change breakpoint
2. Change thumbnail width
3. Change image gap
4. Change scroll offset
5. Disable smooth scroll

**Most Important Files:**
1. `functions.php` - Core functionality
2. `gallery-stacked.css` - Layout styles
3. `gallery-stacked.js` - Interaction logic

**Most Important Concepts:**
1. Child theme (never modify parent)
2. Media queries (desktop vs mobile)
3. CSS specificity (override parent styles)
4. JavaScript timing (run before parent)
5. Testing (desktop, mobile, browsers)

---

**FAQ Version**: 1.0  
**Last Updated**: 2025-10-29  
**Total Questions**: 50

For more detailed information, refer to:
- `TECHNICAL_DOCUMENTATION_GALLERY_MODIFICATION.md`
- `QUICK_REFERENCE_GUIDE.md`
- `IMPLEMENTATION_EXAMPLE.md`

