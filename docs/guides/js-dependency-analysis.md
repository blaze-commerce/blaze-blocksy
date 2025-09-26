---
title: "JavaScript Dependency Analysis and Loading Priority"
description: "Comprehensive analysis of JavaScript file dependencies and optimal loading sequence"
category: "guide"
last_updated: "2025-01-26"
tags: [javascript, dependencies, performance]
---

# JavaScript Dependency Analysis and Loading Priority

## Overview

This document analyzes JavaScript file dependencies and determines optimal loading priorities for the Blocksy Child Theme build system.

## Dependency Matrix

### Core Dependencies
All JavaScript files depend on:
- **jQuery** - Primary dependency for DOM manipulation
- **WordPress Core** - For admin/customizer contexts
- **WooCommerce** - For e-commerce functionality (conditional)

### File-Specific Dependencies

#### 🚀 Critical Priority (Load First)

**`assets/src/critical/js/core.js`**
- **Dependencies**: None (vanilla JS)
- **Purpose**: Dependency validation and polyfills
- **Loading**: Immediate, synchronous
- **Size**: ~2KB
- **Critical**: Yes - validates other dependencies

#### 🌐 High Priority (Load Early)

**`assets/src/frontend/js/components/general.js`**
- **Dependencies**: jQuery
- **Purpose**: Product variation display logic
- **Loading**: Deferred after DOM ready
- **Size**: ~1KB
- **Critical**: No - UI enhancement only

**`assets/src/frontend/js/components/minicart-control.js`**
- **Dependencies**: 
  - jQuery
  - `wc_add_to_cart_params` (WooCommerce)
  - WooCommerce cart fragments
- **Purpose**: Cart flow control and redirection
- **Loading**: Deferred, conditional on WooCommerce
- **Size**: ~12KB
- **Critical**: High for e-commerce flow

#### 🛒 WooCommerce Priority (Conditional)

**`assets/src/frontend/js/components/mini-cart.js`**
- **Dependencies**:
  - jQuery
  - `blazeBlocksyMiniCart` (localized data)
  - WooCommerce AJAX endpoints
- **Purpose**: Mini cart interactions and coupon handling
- **Loading**: Conditional on cart presence
- **Size**: ~4KB
- **Critical**: High for cart functionality

**`assets/src/frontend/js/pages/single-product.js`**
- **Dependencies**:
  - jQuery
  - `blazeBlocksySingleProduct` (localized data)
  - localStorage/sessionStorage APIs
- **Purpose**: Recently viewed products tracking
- **Loading**: Only on product pages
- **Size**: ~7KB
- **Critical**: Medium - tracking functionality

**`assets/src/frontend/js/pages/archive.js`**
- **Dependencies**: jQuery
- **Purpose**: Product archive enhancements
- **Loading**: Only on archive pages
- **Size**: ~2KB
- **Critical**: Low - enhancement only

**`assets/src/frontend/js/pages/checkout.js`**
- **Dependencies**:
  - jQuery
  - WooCommerce checkout scripts
  - Payment gateway APIs
- **Purpose**: Checkout process enhancements
- **Loading**: Only on checkout page
- **Size**: ~3KB
- **Critical**: High for checkout flow

**`assets/src/frontend/js/pages/my-account.js`**
- **Dependencies**: jQuery
- **Purpose**: Account page enhancements
- **Loading**: Only on account pages
- **Size**: ~2KB
- **Critical**: Low - enhancement only

**`assets/src/frontend/js/pages/thank-you.js`**
- **Dependencies**: jQuery
- **Purpose**: Order confirmation functionality
- **Loading**: Only on thank you page
- **Size**: ~3KB
- **Critical**: Medium - order completion

**`assets/src/frontend/js/pages/thank-you-inline.js`**
- **Dependencies**: jQuery
- **Purpose**: Inline thank you page scripts
- **Loading**: Only on thank you page
- **Size**: ~2KB
- **Critical**: Medium - order completion

#### 🎨 Feature Priority (Lazy Load)

**`assets/src/frontend/js/features/wishlist-offcanvas.js`**
- **Dependencies**:
  - jQuery
  - Wishlist plugin APIs
  - Off-canvas panel system
- **Purpose**: Wishlist off-canvas functionality
- **Loading**: Lazy load after user interaction
- **Size**: ~10KB
- **Critical**: Low - feature enhancement

**`assets/src/frontend/js/features/mix-and-match-products.js`**
- **Dependencies**:
  - jQuery
  - WooCommerce product APIs
- **Purpose**: Product bundling functionality
- **Loading**: Only when feature is active
- **Size**: ~5KB
- **Critical**: Low - specific feature

#### ⚙️ Admin Priority (On Demand)

**`assets/src/admin/js/admin/my-account-admin.js`**
- **Dependencies**:
  - jQuery
  - WordPress admin APIs
- **Purpose**: Admin account management
- **Loading**: Only in admin context
- **Size**: ~3KB
- **Critical**: Low - admin only

**`assets/src/admin/js/editor/product-carousel-editor.js`**
- **Dependencies**:
  - jQuery
  - Gutenberg block APIs
  - Owl Carousel library
- **Purpose**: Block editor functionality
- **Loading**: Only in block editor
- **Size**: ~4KB
- **Critical**: Low - editor only

#### 🎛️ Customizer Priority (Contextual)

**`assets/src/admin/js/customizer/customizer-preview.js`**
- **Dependencies**:
  - jQuery
  - `wp.customize` API
  - WordPress customizer framework
- **Purpose**: Live preview functionality
- **Loading**: Only in customizer preview
- **Size**: ~8KB
- **Critical**: Medium for customizer

**`assets/src/admin/js/customizer/my-account-customizer-preview.js`**
- **Dependencies**:
  - jQuery
  - `wp.customize` API
- **Purpose**: Account customizer preview
- **Loading**: Only in customizer
- **Size**: ~3KB
- **Critical**: Low - specific customizer feature

**`assets/src/admin/js/customizer/wishlist-offcanvas-sync.js`**
- **Dependencies**:
  - jQuery
  - `wp.customize` API
  - Wishlist APIs
- **Purpose**: Wishlist customizer sync
- **Loading**: Only in customizer
- **Size**: ~4KB
- **Critical**: Low - customizer feature

**`assets/src/admin/js/customizer/wishlist-offcanvas-variables.js`**
- **Dependencies**:
  - jQuery
  - `wp.customize` API
  - CSS custom properties
- **Purpose**: Wishlist variable management
- **Loading**: Only in customizer
- **Size**: ~2KB
- **Critical**: Low - customizer feature

## Loading Sequence Strategy

### 1. Critical Path (Immediate)
```javascript
// Load immediately in <head>
assets/dist/js/critical.min.js
```

### 2. High Priority (Deferred)
```javascript
// Load after DOM ready
assets/dist/js/global.min.js
```

### 3. Conditional Loading (Context-Based)
```javascript
// WooCommerce pages only
if (is_woocommerce()) {
    assets/dist/js/woocommerce.min.js
}

// Admin context only
if (is_admin()) {
    assets/dist/js/admin.min.js
}

// Customizer only
if (is_customize_preview()) {
    assets/dist/js/customizer.min.js
}
```

### 4. Feature Loading (Lazy/On-Demand)
```javascript
// Load after user interaction or feature detection
assets/dist/js/features.min.js
```

## Bundle Composition

### Critical Bundle (~2KB)
- `core.js` - Dependency validation and polyfills

### Global Bundle (~13KB)
- `general.js` - UI enhancements
- `minicart-control.js` - Cart flow control

### WooCommerce Bundle (~23KB)
- `mini-cart.js` - Cart interactions
- `single-product.js` - Product page functionality
- `archive.js` - Archive enhancements
- `checkout.js` - Checkout functionality
- `my-account.js` - Account features
- `thank-you.js` - Order confirmation
- `thank-you-inline.js` - Inline scripts

### Features Bundle (~15KB)
- `wishlist-offcanvas.js` - Wishlist functionality
- `mix-and-match-products.js` - Product bundling

### Admin Bundle (~7KB)
- `my-account-admin.js` - Admin management
- `product-carousel-editor.js` - Block editor

### Customizer Bundle (~17KB)
- `customizer-preview.js` - Live preview
- `my-account-customizer-preview.js` - Account preview
- `wishlist-offcanvas-sync.js` - Wishlist sync
- `wishlist-offcanvas-variables.js` - Variable management

## Performance Impact

### Before Bundling
- 17 individual files
- 17 HTTP requests
- ~200KB total size
- Multiple dependency chains

### After Bundling
- 6 conditional bundles
- 2-6 HTTP requests (context dependent)
- ~150KB total size (minified)
- Optimized dependency loading

### Expected Improvements
- **Reduced HTTP Requests**: 65-88% reduction
- **Faster Parse Time**: Bundled and minified code
- **Better Caching**: Fewer, larger files cache more efficiently
- **Conditional Loading**: Only load what's needed for current context
