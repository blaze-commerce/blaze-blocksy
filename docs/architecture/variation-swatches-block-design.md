---
title: "Product Variation Swatches Block Architecture"
description: "Technical design for custom WordPress block integrating with WooCommerce Product Collection"
category: "architecture"
last_updated: "2025-01-24"
tags: [wordpress-blocks, woocommerce, variation-swatches, architecture]
---

# Product Variation Swatches Block Architecture

## Overview

Design for a custom WordPress block that integrates variation swatches functionality with WooCommerce Product Collection blocks, maintaining compatibility with the existing "Variation Swatches Pro" plugin.

## Block Specifications

### Block Identity
- **Name**: `custom/product-variation-swatches`
- **Title**: "Product Variation Swatches"
- **Category**: `woocommerce`
- **Parent**: `woocommerce/product-template`
- **Context**: Product Collection blocks only

### Block Attributes
```json
{
  "showLabel": {
    "type": "boolean",
    "default": false
  },
  "maxVisible": {
    "type": "number",
    "default": 3
  },
  "showMoreButton": {
    "type": "boolean", 
    "default": true
  },
  "swatchSize": {
    "type": "string",
    "default": "30px"
  },
  "onlyVariableProducts": {
    "type": "boolean",
    "default": true
  }
}
```

## Implementation Strategy

### 1. Block Registration Approach

#### File Structure
```
includes/blocks/
├── variation-swatches/
│   ├── block.json
│   ├── index.php
│   ├── render.php
│   └── assets/
│       ├── editor.js
│       ├── style.css
│       └── editor.css
```

#### Registration Method
- Use `register_block_type()` with `block.json`
- Server-side rendering for dynamic content
- Editor-side JavaScript for block controls
- Automatic enqueuing of assets

### 2. Integration with Product Collection

#### Context Detection
```php
// Check if we're inside a Product Template block
function is_product_template_context() {
    return isset($GLOBALS['woocommerce_loop']['is_shortcode']) || 
           wp_is_block_theme() && 
           has_block('woocommerce/product-collection');
}
```

#### Product Data Access
```php
// Access current product in Product Collection context
function get_current_product_in_collection() {
    global $product, $woocommerce_loop;
    
    if (isset($woocommerce_loop['product'])) {
        return $woocommerce_loop['product'];
    }
    
    return $product;
}
```

### 3. Variation Swatches Integration

#### Plugin Compatibility
- Reuse existing Variation Swatches Pro data structure
- Maintain same CSS classes for styling consistency
- Use plugin's JavaScript for swatch interactions
- Respect plugin's configuration settings

#### Data Retrieval
```php
function get_variation_data($product) {
    if (!$product || !$product->is_type('variable')) {
        return null;
    }
    
    // Use existing plugin's data structure
    $available_variations = $product->get_available_variations();
    $attributes = $product->get_variation_attributes();
    
    return [
        'variations' => $available_variations,
        'attributes' => $attributes,
        'product_id' => $product->get_id()
    ];
}
```

## Block Implementation Details

### 1. Block.json Configuration
```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "custom/product-variation-swatches",
  "title": "Product Variation Swatches",
  "category": "woocommerce",
  "parent": ["woocommerce/product-template"],
  "description": "Display variation swatches for variable products in Product Collection blocks.",
  "keywords": ["woocommerce", "variations", "swatches", "product"],
  "textdomain": "blocksy-child",
  "usesContext": ["woocommerce/product-id", "postId"],
  "supports": {
    "html": false,
    "align": false,
    "spacing": {
      "margin": true,
      "padding": true
    }
  },
  "attributes": {
    "showLabel": {
      "type": "boolean",
      "default": false
    },
    "maxVisible": {
      "type": "number",
      "default": 3
    },
    "showMoreButton": {
      "type": "boolean",
      "default": true
    }
  },
  "editorScript": "file:./assets/editor.js",
  "style": "file:./assets/style.css",
  "editorStyle": "file:./assets/editor.css"
}
```

### 2. Server-Side Rendering
```php
function render_variation_swatches_block($attributes, $content, $block) {
    // Get product from block context
    $product_id = $block->context['woocommerce/product-id'] ?? 
                  $block->context['postId'] ?? null;
    
    if (!$product_id) {
        return '';
    }
    
    $product = wc_get_product($product_id);
    
    if (!$product || !$product->is_type('variable')) {
        return '';
    }
    
    // Generate swatches HTML using existing plugin structure
    return generate_swatches_html($product, $attributes);
}
```

### 3. Frontend HTML Structure
```html
<div class="wp-block-custom-product-variation-swatches">
    <div class="wvs-archive-variations-wrapper" 
         data-product_id="123" 
         data-product_variations="[...]">
        <ul class="variations">
            <li class="woo-variation-items-wrapper">
                <div class="ct-variation-swatches">
                    <div class="ct-swatch-container">
                        <input type="radio" name="attribute_color" value="red" id="swatch-red-123">
                        <label for="swatch-red-123" class="ct-swatch ct-color-swatch" 
                               style="background-color: #ff0000;"></label>
                    </div>
                    <!-- More swatches... -->
                    <button class="view-more-variations">+2 More</button>
                </div>
            </li>
        </ul>
    </div>
</div>
```

## CSS Integration Strategy

### 1. Maintain Existing Styles
- Use same CSS classes as current implementation
- Inherit from `assets/css/product-card.css`
- Ensure consistent swatch sizing and spacing

### 2. Block-Specific Enhancements
```css
.wp-block-custom-product-variation-swatches {
    margin: 10px 0;
}

.wp-block-custom-product-variation-swatches .ct-variation-swatches {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

/* Maintain existing swatch styles */
.wp-block-custom-product-variation-swatches .ct-swatch {
    --swatch-size: 30px;
}
```

## JavaScript Integration

### 1. Editor Experience
```javascript
// assets/editor.js
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl } from '@wordpress/components';

registerBlockType('custom/product-variation-swatches', {
    edit: ({ attributes, setAttributes }) => {
        return (
            <>
                <InspectorControls>
                    <PanelBody title="Swatch Settings">
                        <ToggleControl
                            label="Show attribute labels"
                            checked={attributes.showLabel}
                            onChange={(showLabel) => setAttributes({ showLabel })}
                        />
                        <RangeControl
                            label="Maximum visible swatches"
                            value={attributes.maxVisible}
                            onChange={(maxVisible) => setAttributes({ maxVisible })}
                            min={1}
                            max={10}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="wp-block-custom-product-variation-swatches">
                    <p>Product Variation Swatches (Preview in frontend)</p>
                </div>
            </>
        );
    },
    save: () => null // Server-side rendering
});
```

### 2. Frontend Functionality
- Reuse existing `assets/js/general.js` functionality
- Ensure swatch interactions work within block context
- Maintain "view more" button behavior

## Testing Strategy

### 1. Block Editor Testing
- Verify block appears in Product Template inserter
- Test block controls and attribute updates
- Ensure proper preview in editor

### 2. Frontend Testing
- Test with various product types (variable, simple)
- Verify swatch interactions and price updates
- Test responsive behavior and styling

### 3. Compatibility Testing
- Ensure no conflicts with existing Variation Swatches Pro plugin
- Test with different WooCommerce Product Collection configurations
- Verify performance with large product catalogs

## Performance Considerations

### 1. Conditional Loading
- Only load block assets on pages with Product Collection blocks
- Lazy load variation data for better performance
- Cache variation attributes when possible

### 2. JavaScript Optimization
- Reuse existing plugin's JavaScript where possible
- Minimize additional JavaScript overhead
- Use event delegation for swatch interactions

## Deployment Strategy

### 1. File Organization
- Place block files in `includes/blocks/variation-swatches/`
- Register block in main `functions.php`
- Enqueue assets conditionally

### 2. Backward Compatibility
- Maintain existing hook-based swatches for non-block themes
- Provide fallback for themes not using Product Collection blocks
- Ensure graceful degradation when plugin is disabled
