---
title: "Current Variation Swatches Implementation Analysis"
description: "Analysis of existing Variation Swatches Pro plugin and Blocksy theme integration"
category: "analysis"
last_updated: "2025-01-24"
tags: [variation-swatches, woocommerce, blocksy, analysis]
---

# Current Variation Swatches Implementation Analysis

## Overview

The site currently uses the "Variation Swatches for WooCommerce - Pro" plugin (v2.2.1) integrated with the Blocksy theme to display color and attribute swatches on product cards in the shop archive.

## Plugin Structure

### Main Plugin Files
- **Main File**: `woo-variation-swatches-pro.php`
- **Archive Handler**: `includes/class-woo-variation-swatches-pro-archive-page.php`
- **Template**: `templates/variable.php`

### Key Hooks and Integration Points

#### Archive Display Hook
```php
// In class-woo-variation-swatches-pro-archive-page.php
add_action('woocommerce_after_shop_loop_item', array($this, 'after_shop_loop_item'), 30);
```

#### Template Structure
The plugin uses `templates/variable.php` which generates:
```html
<div class="wvs-archive-variations-wrapper" data-product_id="..." data-product_variations="...">
    <ul class="variations">
        <li class="woo-variation-items-wrapper">
            <!-- Swatch items generated here -->
        </li>
    </ul>
</div>
```

## Current CSS Classes and Styling

### Blocksy Child Theme Integration
File: `assets/css/product-card.css`

```css
.products .product .ct-variation-swatches .ct-swatch-container:nth-child(n + 5) {
    display: none;
}

.products .product .ct-swatch-container .ct-swatch {
    --swatch-size: 30px;
}

.products .product .ct-variation-swatches .view-more-variations {
    background: none;
    border: none;
    color: var(--theme-palette-color-1);
    font-size: 14px;
    line-height: 24px;
    text-decoration: none;
    font-weight: 400;
    cursor: pointer;
}
```

### JavaScript Enhancement
File: `assets/js/general.js`

```javascript
// Hide swatches beyond 3 and add "view more" button
if (count > 3) {
    $(this).append(`<button class="view-more-variations">${count - 3}+</button>`);
}
```

## Current Display Behavior

### Visual Structure
1. **Product Image**
2. **Product Title**
3. **Variation Swatches** (Color/Size options as radio buttons)
4. **Product Price**

### Swatch Functionality
- Displays as radiogroup with individual radio buttons
- Shows first 3 swatches, hides rest with "+X More" button
- Clicking swatches updates product information
- Supports color, image, and text-based swatches

## Plugin Configuration

### Archive Settings
- **Show on Archive**: Yes
- **Position**: After shop loop item (priority 30)
- **Catalog Mode**: Configurable
- **Clear Button**: Available

### Data Structure
Each product variation data is stored as JSON in `data-product_variations` attribute containing:
- Variation IDs
- Attribute combinations
- Pricing information
- Stock status
- Images

## Blocksy Theme Integration

### Companion Plugin
The Blocksy Companion Pro plugin includes additional variation swatch styling:
- `variation-swatches/variations-archive.scss`
- `variation-swatches/variations-form.scss`

### CSS Custom Properties
Uses Blocksy's CSS custom property system:
- `--swatch-size: 30px`
- `--theme-palette-color-1` for text colors

## Current Limitations for Block Editor

### Issues with Product Collection Blocks
1. **Hook Dependency**: Current implementation relies on `woocommerce_after_shop_loop_item` hook
2. **Template Override**: Product Collection blocks use different template structure
3. **Block Context**: No direct integration with Gutenberg block system
4. **Manual Placement**: Cannot be manually positioned within block templates

### Block Editor Compatibility Gap
- Product Collection blocks don't trigger traditional WooCommerce hooks
- No native block for variation swatches within Product Template
- Current swatches appear outside of intended block structure

## Recommendations for Block Development

### Integration Strategy
1. Create custom block that hooks into Product Collection's Product Template
2. Maintain visual and functional consistency with existing implementation
3. Use same CSS classes and JavaScript for seamless integration
4. Detect variable products automatically within block context

### Technical Approach
- Extend WooCommerce Product Collection with custom variation swatch block
- Reuse existing plugin's data structure and functionality
- Maintain compatibility with current theme styling
- Ensure proper block editor preview and frontend rendering
