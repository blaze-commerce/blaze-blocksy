# Instructions for AI Agent

**Task**: Implement WooCommerce Product Gallery Stacked Layout Modification in Blocksy Child Theme

---

## 📋 Task Overview

You are tasked with implementing a modification to the WooCommerce product gallery in a Blocksy WordPress theme. The modification will be implemented in a **child theme** (never modify parent theme).

### Goal:
- **Desktop (≥1024px)**: Display all product images stacked vertically with thumbnails on the left
- **Mobile (<1024px)**: Keep existing slider behavior (no changes)

---

## 📚 Documentation Package

You have been provided with a complete documentation package containing 11 files. Here's how to use them:

### Primary Documents (Read These First)

1. **TECHNICAL_DOCUMENTATION_GALLERY_MODIFICATION.md** ⭐ MOST IMPORTANT
   - Complete technical specification
   - Parent theme architecture analysis
   - Implementation strategy
   - **Read this first for complete context**

2. **IMPLEMENTATION_EXAMPLE.md** ⭐ CODE REFERENCE
   - Complete, ready-to-use code for all 4 files
   - Copy-paste friendly
   - **Use this for actual implementation**

3. **IMPLEMENTATION_CHECKLIST.md** ⭐ VERIFICATION
   - 200+ checklist items
   - Use to verify your implementation
   - **Check off items as you complete them**

### Reference Documents (Use As Needed)

4. **QUICK_REFERENCE_GUIDE.md** - Quick lookup for requirements and code snippets
5. **VISUAL_DIAGRAMS.md** - Visual diagrams of architecture and flow
6. **FAQ.md** - 50 common questions and answers
7. **README.md** - Project overview
8. **_START_HERE.md** - Entry point for humans
9. **INDEX.md** - Navigation guide
10. **RINGKASAN_UNTUK_CLIENT.md** - Client summary (Indonesian)
11. **MANIFEST.md** - Package contents

---

## 🎯 Your Task

### Step 1: Understand Requirements
Read **TECHNICAL_DOCUMENTATION_GALLERY_MODIFICATION.md** sections:
- Requirements Summary
- Parent Theme Architecture Analysis
- Implementation Strategy

**Key Requirements:**
- Desktop: Stacked images, thumbnails left, 18px gap, 120px thumbnail width
- Mobile: No changes (keep slider)
- Breakpoint: 1024px
- Preserve: Lightbox, zoom, badges, video support

### Step 2: Create Child Theme Structure
Create the following file structure:
```
wp-content/themes/blocksy-child/
├── style.css
├── functions.php
└── assets/
    ├── css/
    │   └── gallery-stacked.css
    └── js/
        └── gallery-stacked.js
```

### Step 3: Implement Code
Use **IMPLEMENTATION_EXAMPLE.md** to get complete code for:

1. **style.css** - WordPress child theme header
2. **functions.php** - Enqueue scripts, filters, body class
3. **assets/css/gallery-stacked.css** - All CSS for stacked layout
4. **assets/js/gallery-stacked.js** - JavaScript for scroll behavior

**IMPORTANT**: 
- Copy code exactly as provided
- Do NOT modify parent theme files
- Maintain all comments in code
- Follow WordPress coding standards

### Step 4: Verify Implementation
Use **IMPLEMENTATION_CHECKLIST.md** to verify:

**Critical Checks:**
- [ ] Child theme folder created
- [ ] All 4 files created with correct content
- [ ] File permissions correct (644 for files, 755 for directories)
- [ ] No syntax errors in PHP, CSS, or JS
- [ ] Child theme activated in WordPress

**Desktop Testing (≥1024px):**
- [ ] All images displayed stacked vertically
- [ ] Thumbnails displayed on left side
- [ ] 18px gap between images
- [ ] Thumbnail width is 120px
- [ ] Click thumbnail scrolls to image
- [ ] Lightbox opens on image click
- [ ] Badges display correctly
- [ ] Zoom works on hover

**Mobile Testing (<1024px):**
- [ ] Slider works (swipe/arrows)
- [ ] Thumbnails below image
- [ ] Only 1 image visible at a time
- [ ] All features work as in parent theme

### Step 5: Test Edge Cases
- [ ] Product with 1 image
- [ ] Product with 10+ images
- [ ] Product with video
- [ ] Variable product (variation switching)
- [ ] Different browsers (Chrome, Firefox, Safari, Edge)

---

## ⚠️ Critical Requirements

### DO:
✅ Create child theme (never modify parent)  
✅ Use exact code from IMPLEMENTATION_EXAMPLE.md  
✅ Wrap desktop CSS in `@media (min-width: 1024px)`  
✅ Load JavaScript in header with priority 5  
✅ Test both desktop AND mobile  
✅ Preserve all existing features (lightbox, zoom, badges)  
✅ Follow WordPress coding standards  
✅ Add comments to code  
✅ Verify with checklist  

### DON'T:
❌ Modify parent theme files  
❌ Remove `@media` wrapper from CSS  
❌ Load JavaScript in footer  
❌ Change HTML structure  
❌ Break existing features  
❌ Skip mobile testing  
❌ Deploy without testing  
❌ Forget to clear cache  

---

## 🔧 Implementation Details

### File 1: style.css
```css
/*
Theme Name: Blocksy Child
Template: blocksy
Version: 1.0
*/
```

### File 2: functions.php
**Key Points:**
- Enqueue CSS with dependency on 'blocksy-styles'
- Enqueue JS with priority 5 (BEFORE parent theme)
- Load JS in header (not footer)
- Add body class filter for `.ct-has-stacked-gallery`
- Add flexy args filter to add custom class

### File 3: assets/css/gallery-stacked.css
**Key Points:**
- Wrap ALL styles in `@media (min-width: 1024px)`
- Override flexy slider transforms
- Set flex-direction to column
- Set gap to 18px
- Set thumbnail width to 120px
- Hide arrows on desktop
- Use `!important` for critical overrides

### File 4: assets/js/gallery-stacked.js
**Key Points:**
- Check if desktop (≥1024px)
- Prevent Flexy initialization on desktop
- Add thumbnail click handlers
- Smooth scroll to image (top + 100px offset)
- Update active thumbnail
- Handle window resize
- Support variable products

---

## 🧪 Testing Procedure

### 1. Pre-Implementation
- [ ] Backup site (database + files)
- [ ] Verify WordPress, WooCommerce, Blocksy versions
- [ ] Test parent theme gallery works

### 2. Post-Implementation
- [ ] Clear all caches (browser, server, CDN)
- [ ] Test on actual product pages
- [ ] Test desktop (≥1024px)
- [ ] Test mobile (<1024px)
- [ ] Test tablet (768px - 1023px)
- [ ] Test all browsers
- [ ] Check browser console for errors
- [ ] Verify no PHP errors

### 3. Feature Verification
- [ ] Lightbox works
- [ ] Zoom works
- [ ] Badges display
- [ ] Video plays inline
- [ ] Variable products work
- [ ] Lazy loading works

---

## 🐛 Troubleshooting

### Issue: Flexy still initializes on desktop
**Solution**: 
- Ensure JS loads in header (not footer)
- Ensure priority is 5 (before parent theme)
- Check `data-flexy` attribute is removed

### Issue: Styles not applying
**Solution**:
- Check CSS file is loading (Network tab)
- Check media query `@media (min-width: 1024px)`
- Increase specificity or use `!important`
- Clear all caches

### Issue: Thumbnail click doesn't scroll
**Solution**:
- Check browser console for JS errors
- Ensure jQuery is loaded
- Check event listeners are attached
- Enable debug mode in JS

### Issue: Mobile slider broken
**Solution**:
- Ensure CSS is wrapped in media query
- Ensure JS allows Flexy on mobile
- Test with child theme deactivated

**For more troubleshooting**: See FAQ.md and TECHNICAL_DOCUMENTATION_GALLERY_MODIFICATION.md

---

## 📊 Success Criteria

Your implementation is successful when:

✅ **Desktop (≥1024px)**:
- All images stacked vertically
- Thumbnails on left (120px wide)
- 18px gap between images
- Thumbnail click scrolls to image
- Lightbox, zoom, badges work

✅ **Mobile (<1024px)**:
- Slider works normally
- No changes from parent theme

✅ **Quality**:
- No console errors
- No PHP errors
- Fast page load
- All browsers work
- All checklist items passed

---

## 📝 Deliverables

When you complete the task, provide:

1. **Confirmation** that all 4 files are created
2. **File paths** of created files
3. **Verification** that checklist items are completed
4. **Test results** for desktop and mobile
5. **Screenshots** (if possible) showing:
   - Desktop stacked layout
   - Mobile slider layout
6. **Any issues encountered** and how you resolved them
7. **Any customizations made** beyond the documentation

---

## 🎓 Key Concepts

### Child Theme
- WordPress best practice
- Preserves customizations during parent theme updates
- Only way to safely modify themes

### Responsive Design
- Different layouts for different screen sizes
- Media queries control behavior
- Breakpoint at 1024px

### CSS Specificity
- Child theme must override parent
- Use higher specificity or `!important`
- Wrap in media query for desktop only

### JavaScript Timing
- Must run BEFORE parent theme
- Priority 5 (parent is 10)
- Header loading (not footer)

### Feature Preservation
- Don't change HTML structure
- Keep all data attributes
- Maintain parent theme functionality

---

## 🚀 Quick Start Checklist

- [ ] Read TECHNICAL_DOCUMENTATION_GALLERY_MODIFICATION.md
- [ ] Understand requirements (desktop vs mobile)
- [ ] Create child theme folder structure
- [ ] Copy code from IMPLEMENTATION_EXAMPLE.md
- [ ] Create style.css
- [ ] Create functions.php
- [ ] Create assets/css/gallery-stacked.css
- [ ] Create assets/js/gallery-stacked.js
- [ ] Activate child theme
- [ ] Clear caches
- [ ] Test desktop (≥1024px)
- [ ] Test mobile (<1024px)
- [ ] Verify with IMPLEMENTATION_CHECKLIST.md
- [ ] Report completion

---

## 📞 Questions?

If you encounter issues or have questions:

1. Check **FAQ.md** (50 common questions)
2. Check **TECHNICAL_DOCUMENTATION_GALLERY_MODIFICATION.md** → Common Issues
3. Check **VISUAL_DIAGRAMS.md** → Troubleshooting Decision Tree
4. Enable debug mode in JavaScript
5. Check browser console for errors

---

## ✅ Final Checklist Before Reporting Complete

- [ ] All 4 files created
- [ ] Code matches IMPLEMENTATION_EXAMPLE.md
- [ ] Child theme activated
- [ ] Desktop layout works (stacked images)
- [ ] Mobile layout works (slider)
- [ ] Thumbnail click scrolls to image
- [ ] Lightbox works
- [ ] Zoom works
- [ ] Badges display
- [ ] No console errors
- [ ] No PHP errors
- [ ] Tested in multiple browsers
- [ ] All checklist items verified
- [ ] Ready for production

---

## 🎯 Your Mission

Implement a professional, production-ready WooCommerce product gallery modification in a Blocksy child theme that:

1. Displays stacked images on desktop (≥1024px)
2. Keeps slider on mobile (<1024px)
3. Preserves all existing features
4. Follows WordPress best practices
5. Is maintainable and well-documented
6. Passes all quality checks

**You have complete documentation. Follow it carefully. Test thoroughly. Deliver quality.**

---

**Good luck! 🚀**

---

**Documentation Version**: 1.0  
**Date**: 2025-10-29  
**Status**: Ready for implementation

---

*End of Instructions*

