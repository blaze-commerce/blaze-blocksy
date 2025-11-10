# Blocksy Offcanvas Module - Documentation Package

## 📦 Package Contents

This documentation package contains everything needed to implement a reusable offcanvas module in a Blocksy child theme.

### Files Included

1. **OFFCANVAS_MODULE_DOCUMENTATION.md** (Main Documentation - 1,812 lines)
   - Complete technical specification
   - Detailed implementation guide
   - Full code examples
   - Accessibility guidelines
   - Best practices

2. **OFFCANVAS_QUICK_REFERENCE.md** (Quick Reference - 300 lines)
   - Essential code snippets
   - Common customizations
   - Troubleshooting guide
   - Minimal working examples

3. **README_OFFCANVAS_DOCS.md** (This file)
   - Package overview
   - Getting started guide
   - Document navigation

---

## 🎯 What You Can Do

Based on the Blocksy theme's offcanvas implementation, you can create a fully customizable offcanvas module with:

### ✅ Customizable Elements

1. **Title/Heading**
   - Enable/disable heading
   - Custom text
   - Custom styling

2. **Close Icon**
   - Custom SVG icon (via WordPress filter)
   - 3 button styles (Simple, Border, Background)
   - Adjustable size (5-50px)
   - Custom colors (default & hover states)
   - Border radius control

3. **Content**
   - Custom HTML content
   - WordPress widgets
   - Navigation menus
   - Shortcodes
   - Dynamic content via hooks

4. **Behavior**
   - Slide from left
   - Slide from right
   - Center modal
   - Custom animations

5. **Styling**
   - Panel width (responsive)
   - Background colors
   - Padding & spacing
   - Border radius
   - Offset from edges
   - Custom CSS variables

---

## 🚀 Quick Start

### For AI Agents

If you're an AI agent implementing this:

1. **Read First**: `OFFCANVAS_MODULE_DOCUMENTATION.md` sections 1-3
   - Understand the architecture
   - Review HTML structure
   - Study CSS animations

2. **Implementation**: Follow section 7 "Implementation in Child Theme"
   - Step-by-step guide (10 steps)
   - Complete code examples
   - File structure

3. **Customization**: Refer to section 6 "Customization Guide"
   - 7 customization methods
   - WordPress hooks
   - CSS variables

4. **Testing**: Use section 8 "Accessibility Features" and section 9 "Best Practices"
   - Testing checklist
   - Accessibility requirements
   - Performance optimization

### For Developers

1. **Quick Implementation**: Use `OFFCANVAS_QUICK_REFERENCE.md`
   - Copy minimal working example
   - Customize as needed
   - Test accessibility

2. **Deep Dive**: Read `OFFCANVAS_MODULE_DOCUMENTATION.md`
   - Understand the system
   - Learn best practices
   - Implement advanced features

---

## 📚 Document Structure

### OFFCANVAS_MODULE_DOCUMENTATION.md

```
1. Overview
   - Key features
   - System capabilities

2. Architecture
   - Core components
   - File locations
   - Layer structure

3. HTML Structure
   - Basic structure
   - Trigger button
   - Behavior types
   - State management

4. CSS Styling & Animations
   - SCSS variables
   - Base styles
   - Side panel animations
   - Modal animations
   - Responsive breakpoints

5. JavaScript API
   - Opening offcanvas
   - Closing offcanvas
   - Full implementation
   - Event system

6. Customization Guide
   - Heading customization
   - Close icon customization
   - Content customization
   - Behavior & position
   - Panel width
   - Offset & border radius
   - Background & backdrop

7. Implementation in Child Theme
   - 10-step implementation guide
   - Complete code examples
   - File structure
   - Widget areas
   - Menu locations
   - Full integration example

8. Accessibility Features
   - ARIA attributes
   - Keyboard navigation
   - Focus management
   - Inert attribute
   - Screen reader support
   - Reduced motion
   - Color contrast
   - Touch targets

9. Best Practices
   - Performance optimization
   - Scroll lock
   - Multiple instances
   - Event delegation
   - Error handling
   - Responsive behavior
   - Content loading
   - State persistence
   - Testing checklist
   - Common pitfalls
```

### OFFCANVAS_QUICK_REFERENCE.md

```
- Essential code snippets
- Key attributes table
- CSS variables list
- WordPress hooks
- Common customizations
- Accessibility checklist
- Browser support
- File structure
- Minimal working example
- Troubleshooting table
- Performance tips
```

---

## 🎨 Key Features Confirmed

Based on analysis of the Blocksy theme source code:

### ✅ Offcanvas Exists
- Located in: `inc/components/builder/header-elements.php`
- Styles: `static/sass/frontend/5-modules/off-canvas/`
- JavaScript: `static/js/frontend/lazy/overlay.js`

### ✅ Fully Customizable

**Title/Heading:**
- Option: `has_offcanvas_heading` (yes/no)
- Text: `offcanvas_heading` (customizable)
- Styling: `.ct-panel-heading` class

**Close Icon:**
- Filter: `blocksy:main:offcanvas:close:icon`
- Types: `type-1`, `type-2`, `type-3`
- Size: `menu_close_button_icon_size` (5-50px)
- Colors: `menu_close_button_color` (default & hover)

**Content:**
- Hooks: `blocksy:header:offcanvas:desktop:top/bottom`
- Hooks: `blocksy:header:offcanvas:mobile:top/bottom`
- Container: `.ct-panel-content-inner`

**Behavior:**
- `data-behaviour="left-side"` - Slide from left
- `data-behaviour="right-side"` - Slide from right
- `data-behaviour="modal"` - Center modal

**Animations:**
- Duration: 0.25s
- Easing: ease-in-out
- Transform: translate3d (GPU accelerated)

---

## 🔧 Implementation Approach

### Recommended Workflow

1. **Setup** (15 minutes)
   - Create child theme structure
   - Copy base files
   - Enqueue assets

2. **Basic Implementation** (30 minutes)
   - Create HTML template
   - Add CSS styles
   - Add JavaScript functionality

3. **Customization** (30 minutes)
   - Customize heading
   - Customize close icon
   - Add custom content

4. **Testing** (30 minutes)
   - Test functionality
   - Test accessibility
   - Test responsiveness

5. **Optimization** (15 minutes)
   - Performance tuning
   - Error handling
   - Final polish

**Total Time**: ~2 hours for complete implementation

---

## 📋 Requirements

### Technical Requirements

- **WordPress**: 5.0+
- **PHP**: 7.4+
- **Blocksy Theme**: 2.0+
- **Browser Support**: Modern browsers (last 2 versions)

### Knowledge Requirements

- HTML5
- CSS3 (SCSS optional)
- JavaScript (ES6+)
- WordPress theme development
- Basic accessibility concepts

---

## 🎓 Learning Path

### Beginner
1. Read Quick Reference
2. Copy minimal working example
3. Test basic functionality
4. Customize colors and text

### Intermediate
1. Read full documentation sections 1-5
2. Implement complete child theme integration
3. Add custom content via hooks
4. Customize animations

### Advanced
1. Read full documentation sections 6-9
2. Implement multiple offcanvas instances
3. Add dynamic content loading
4. Optimize performance
5. Ensure full accessibility compliance

---

## 🐛 Troubleshooting

### Common Issues

**Issue**: Offcanvas doesn't appear
- **Check**: HTML structure is correct
- **Check**: CSS is enqueued
- **Check**: JavaScript is loaded

**Issue**: Animation is jerky
- **Solution**: Add GPU acceleration with `transform: translate3d(0,0,0)`

**Issue**: Can't close with ESC key
- **Solution**: Add keyup event listener (see documentation)

**Issue**: Focus not trapped
- **Solution**: Implement focus lock (see Accessibility section)

For more troubleshooting, see Quick Reference guide.

---

## 📞 Support

### Documentation Issues
If you find errors or need clarification:
1. Check both documentation files
2. Review code examples
3. Test minimal working example

### Implementation Help
For implementation assistance:
1. Follow step-by-step guide in section 7
2. Use Quick Reference for code snippets
3. Check troubleshooting section

---

## 📝 Version Information

- **Documentation Version**: 1.0.0
- **Last Updated**: 2025-11-07
- **Compatible With**: Blocksy Theme v2.0+
- **Language**: English
- **Format**: Markdown

---

## ✨ Summary

This documentation package provides:

✅ **Complete technical specification** based on Blocksy theme source code  
✅ **Step-by-step implementation guide** for child themes  
✅ **Full customization options** for title, icon, and content  
✅ **Accessibility compliance** with WCAG 2.1 Level AA  
✅ **Performance optimization** techniques  
✅ **Working code examples** ready to use  
✅ **Quick reference guide** for common tasks  
✅ **Troubleshooting guide** for common issues  

**Ready to implement!** 🚀

---

## 📖 How to Use This Documentation

### For AI Agents
```
1. Load OFFCANVAS_MODULE_DOCUMENTATION.md
2. Parse sections 1-7 for implementation
3. Follow step-by-step guide in section 7
4. Validate against section 8 (Accessibility)
5. Apply best practices from section 9
```

### For Human Developers
```
1. Skim README_OFFCANVAS_DOCS.md (this file)
2. Use OFFCANVAS_QUICK_REFERENCE.md for quick tasks
3. Refer to OFFCANVAS_MODULE_DOCUMENTATION.md for details
4. Test using provided checklists
```

---

**Happy Coding!** 🎉

