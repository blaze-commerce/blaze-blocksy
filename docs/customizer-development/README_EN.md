# Blocksy Theme - Customizer Documentation for AI Agent

> Complete technical documentation for creating custom functions in Blocksy Theme Customizer, specifically for WooCommerce Single Product.

---

## 📖 About This Documentation

This documentation is created for **AI Agent** as a foundational understanding in developing custom functions for Blocksy Theme Customizer. The documentation covers:

✅ How to add new toggle settings
✅ How to add new elements/layers to Product Elements
✅ How to add design options with live preview
✅ Ready-to-use code templates
✅ Troubleshooting and FAQ

---

## ⚠️ CRITICAL CONCEPT: Layer Options vs Design Options

**MUST UNDERSTAND THIS FIRST:**

Blocksy Theme separates customizer options into **TWO distinct locations**:

### 1. Layer Options (Functional Settings)
- **Location**: Inside the layer settings panel
- **Contains**: Content, visibility, layout choices, spacing
- **File**: `single-product-layers.php`
- **Filter**: `blocksy_woo_single_options_layers:extra`

### 2. Design Options (Visual Styling)
- **Location**: Design tab (separate from layer)
- **Contains**: Fonts, colors, backgrounds, borders
- **File**: `single-product-elements.php`
- **Filter**: `blocksy:options:single_product:elements:design_tab:end`

**❌ NEVER put design options (fonts, colors, etc.) inside layer options!**

See `CORRECTION_DESIGN_OPTIONS.md` for detailed explanation.

---

## 🚀 Quick Start

### For AI Agent

**Scenario 1: Adding Toggle Setting**
```
1. Read: QUICK_START_GUIDE_EN.md → Section "Toggle Setting"
2. Copy: CODE_TEMPLATES_EN.md → Template 1
3. Implement in child theme
```

**Scenario 2: Adding Custom Layer**
```
1. Read: QUICK_START_GUIDE_EN.md → Section "New Element"
2. Copy: CODE_TEMPLATES_EN.md → Template 2
3. Implement layer + rendering
```

**Scenario 3: Complete Implementation**
```
1. Read: BLOCKSY_CUSTOMIZER_TECHNICAL_GUIDE_EN.md
2. Copy: CODE_TEMPLATES_EN.md → Template 8
3. Implement layer + design + CSS + live preview
```

---

## 📚 Documentation Structure

### 1. **INDEX_EN.md** - Table of Contents
Navigation guide for all documentation.

### 2. **QUICK_START_GUIDE_EN.md** - Quick Guide
Checklists and cheat sheets for quick reference.

**Contents:**
- Checklist for adding toggle settings
- Checklist for adding new elements
- Checklist for adding design options
- Helper functions cheat sheet
- File structure reference
- Common option types
- Important hooks

### 3. **CODE_TEMPLATES_EN.md** - Code Templates
Ready-to-use code templates for various use cases.

**Contents:**
- Template 1: Toggle Setting (Simple)
- Template 2: Custom Layer (Complete)
- Template 3: Design Options with Live Preview
- Template 4: Dynamic CSS Generation
- Template 5: Live Preview Sync (JavaScript)
- Template 6: Conditional Options
- Template 7: Tabs and Panels
- Template 8: Complete Example - Product Badge Layer

### 4. **BLOCKSY_CUSTOMIZER_TECHNICAL_GUIDE_EN.md** - Complete Technical Guide
Comprehensive documentation about architecture and implementation.

**Contents:**
- Blocksy Customizer architecture
- Directory and file structure
- Customizer workflow
- How to add toggle settings
- How to add elements/layers
- How to add design options
- Helper functions and API reference
- Hooks and filters
- Tips and best practices
- Debugging guide
- Complete implementation examples

### 5. **FAQ_TROUBLESHOOTING_EN.md** - FAQ & Troubleshooting
Solutions for common problems and debugging tips.

**Contents:**
- 10 most common FAQs
- Common errors and solutions
- Debugging tips
- Performance tips
- Best practices checklist

---

## 🎯 Use Cases

### Use Case 1: Disable Product Tabs

**Requirement**: Add a toggle in customizer to disable product tabs.

**Solution**:
1. File already exists: `inc/options/woocommerce/single-product-tabs.php`
2. Toggle already available: `woo_has_product_tabs`
3. Implementation in template:

```php
$has_tabs = blocksy_get_theme_mod('woo_has_product_tabs', 'yes');

if ($has_tabs === 'yes') {
    woocommerce_output_product_data_tabs();
}
```

**Reference**: 
- `QUICK_START_GUIDE_EN.md` → Section "Toggle Setting"
- `CODE_TEMPLATES_EN.md` → Template 1

---

### Use Case 2: Add Product Tabs as Element

**Requirement**: Add "Product Tabs" as an element in Product Elements with design options.

**Solution**:
1. Define layer options (filter: `blocksy_woo_single_options_layers:extra`)
2. Add to default layout (filter: `blocksy_woo_single_options_layers:defaults`)
3. Implement rendering (action: `blocksy:woocommerce:product:custom:layer`)
4. Add design options in `single-product-elements.php`
5. Generate dynamic CSS (action: `blocksy:global-dynamic-css:enqueue`)
6. Setup live preview sync (JavaScript)

**Reference**:
- `QUICK_START_GUIDE_EN.md` → Section "New Element" + "Design Options"
- `CODE_TEMPLATES_EN.md` → Template 2, 3, 4, 5
- `BLOCKSY_CUSTOMIZER_TECHNICAL_GUIDE_EN.md` → Section "Adding New Elements"

---

### Use Case 3: Custom Product Badge

**Requirement**: Add custom badge with text, position, and styling options.

**Solution**:
Use the complete template already available.

**Reference**:
- `CODE_TEMPLATES_EN.md` → Template 8 (Complete Example)

---

## 🛠️ Development Workflow

### Step 1: Setup
```bash
# Create child theme if not exists
wp-content/themes/blocksy-child/
├── functions.php
├── style.css
└── inc/
    └── custom/
```

### Step 2: Implementation
```php
// functions.php
require_once get_stylesheet_directory() . '/inc/custom/my-layer-options.php';
require_once get_stylesheet_directory() . '/inc/custom/my-layer-dynamic-css.php';
```

### Step 3: Testing
1. Open Customizer
2. Navigate to WooCommerce → Single Product
3. Test toggle/layer options
4. Test design options
5. Check live preview
6. Test on frontend

### Step 4: Debugging
```php
// Enable debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Check error log
tail -f wp-content/debug.log
```

---

## 📋 Implementation Checklist

### Before Starting
- [ ] Read relevant documentation
- [ ] Create child theme
- [ ] Enable debug mode
- [ ] Backup theme

### During Development
- [ ] Copy appropriate template
- [ ] Modify as needed
- [ ] Test in customizer
- [ ] Check browser console
- [ ] Test responsive

### After Implementation
- [ ] Test in various browsers
- [ ] Test on various devices
- [ ] Clear cache
- [ ] Document code
- [ ] Commit to Git

---

## 🎓 Learning Resources

### Internal Documentation
1. **INDEX_EN.md** - Navigation and workflow
2. **QUICK_START_GUIDE_EN.md** - Quick reference
3. **CODE_TEMPLATES_EN.md** - Ready-to-use templates
4. **BLOCKSY_CUSTOMIZER_TECHNICAL_GUIDE_EN.md** - Complete guide
5. **FAQ_TROUBLESHOOTING_EN.md** - Problem solving

### External Documentation
- [Blocksy Official Docs](https://creativethemes.com/blocksy/docs/)
- [WordPress Customizer API](https://developer.wordpress.org/themes/customize-api/)
- [WooCommerce Hooks](https://woocommerce.github.io/code-reference/hooks/hooks.html)

---

## 🔧 Tools & Requirements

### Required
- WordPress 5.0+
- WooCommerce 3.0+
- Blocksy Theme (latest version)
- PHP 7.4+

### Recommended
- Child theme
- Code editor (VS Code, PHPStorm)
- Browser DevTools
- Git for version control

---

## 📞 Support

### If You Encounter Problems

1. **Check FAQ**: `FAQ_TROUBLESHOOTING_EN.md`
2. **Check Error Log**: `wp-content/debug.log`
3. **Check Browser Console**: F12 → Console
4. **Review Code**: Compare with template
5. **Clear Cache**: Browser + WordPress + Plugin cache

---

## 📝 Version Info

**Version**: 1.0.0  
**Last Updated**: 2025-11-26  
**Author**: AI Agent Documentation  
**For**: Blocksy Theme Customizer Development  

---

## 🎯 Quick Links

| Document | Purpose | When to Use |
|----------|---------|-------------|
| [INDEX_EN.md](INDEX_EN.md) | Navigation | Finding specific topics |
| [QUICK_START_GUIDE_EN.md](QUICK_START_GUIDE_EN.md) | Quick reference | During coding |
| [CODE_TEMPLATES_EN.md](CODE_TEMPLATES_EN.md) | Code templates | Copy-paste implementation |
| [BLOCKSY_CUSTOMIZER_TECHNICAL_GUIDE_EN.md](BLOCKSY_CUSTOMIZER_TECHNICAL_GUIDE_EN.md) | Deep dive | Understanding architecture |
| [FAQ_TROUBLESHOOTING_EN.md](FAQ_TROUBLESHOOTING_EN.md) | Problem solving | When facing issues |

---

## 🚦 Getting Started

### For First Time Users

1. **Read**: Start with `INDEX_EN.md` for overview
2. **Quick Start**: Read `QUICK_START_GUIDE_EN.md`
3. **Practice**: Copy template from `CODE_TEMPLATES_EN.md`
4. **Deep Dive**: Read `BLOCKSY_CUSTOMIZER_TECHNICAL_GUIDE_EN.md`
5. **Troubleshoot**: Use `FAQ_TROUBLESHOOTING_EN.md` if issues arise

### For Experienced Users

1. **Quick Reference**: `QUICK_START_GUIDE_EN.md`
2. **Copy Template**: `CODE_TEMPLATES_EN.md`
3. **Implement**: Modify as needed
4. **Debug**: `FAQ_TROUBLESHOOTING_EN.md` if necessary

---

## ⚡ Key Concepts

### 1. Layers System
Elements that can be dragged & dropped in Product Elements.

### 2. Design Options
Styling controls with live preview.

### 3. Dynamic CSS
CSS generated from option values.

### 4. Live Preview
Real-time updates without page reload.

### 5. Hooks & Filters
Extension points for custom functionality.

---

## 📌 Important Notes

⚠️ **Always use child theme** - Don't edit parent theme  
⚠️ **Use hooks/filters** - Don't edit core files  
⚠️ **Test before production** - Test in development environment  
⚠️ **Backup before update** - Backup theme and database  
⚠️ **Clear cache** - Clear cache after changes  

---

**Happy Coding! 🚀**

To get started, open [INDEX_EN.md](INDEX_EN.md) for complete navigation.

