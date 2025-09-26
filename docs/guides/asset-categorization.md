---
title: "Asset Categorization and Loading Strategy"
description: "Comprehensive guide to CSS and JS file categorization for optimized loading performance"
category: "guide"
last_updated: "2025-01-26"
tags: [performance, assets, optimization]
---

# Asset Categorization and Loading Strategy

## Overview

This document outlines the categorization strategy for CSS and JavaScript files in the Blocksy Child Theme, organized by loading priority and context for optimal performance.

## CSS File Categorization

### 🚀 Critical CSS (Inline in <head>)
**Purpose**: Above-the-fold styles that prevent layout shift and ensure immediate rendering

**Files**:
- `assets/src/critical/css/layout.css` - Essential header, navigation, and product card layout

**Content**:
- Header z-index and positioning
- Mobile menu critical styles
- Basic product card layout variables
- Essential layout containers

**Loading**: Inlined directly in HTML `<head>` for immediate rendering

### 🌐 Global CSS (High Priority - Preload)
**Purpose**: Site-wide styles needed on all pages

**Files**:
- `assets/src/frontend/css/components/footer.css` - Footer styling
- `assets/src/frontend/css/components/search.css` - Search functionality
- `assets/src/frontend/css/base/**/*.css` - Base typography and variables

**Content**:
- Footer navigation and styling
- Search input and results styling
- Global typography and spacing
- Base color variables

**Loading**: Preloaded and loaded early in page lifecycle

### 🛒 WooCommerce CSS (Conditional - High Priority)
**Purpose**: E-commerce specific styles loaded only on WooCommerce pages

**Files**:
- `assets/src/frontend/css/pages/archive.css` - Product archive pages
- `assets/src/frontend/css/pages/single-product.css` - Individual product pages
- `assets/src/frontend/css/pages/checkout.css` - Checkout process
- `assets/src/frontend/css/pages/my-account.css` - Customer account pages
- `assets/src/frontend/css/pages/thank-you.css` - Order confirmation
- `assets/src/frontend/css/components/product-card.css` - Product card styling
- `assets/src/frontend/css/components/product-carousel.css` - Product carousels
- `assets/src/frontend/css/components/mini-cart.css` - Shopping cart sidebar

**Content**:
- Product display and interaction styles
- Shopping cart and checkout styling
- Customer account interface
- E-commerce specific components

**Loading**: Conditional loading based on `is_woocommerce()` context

### 🎨 Features CSS (Medium Priority - Async)
**Purpose**: Enhanced features that can load after initial render

**Files**:
- `assets/src/frontend/css/features/wishlist.css` - Wishlist functionality
- `assets/src/frontend/css/features/mix-match.css` - Mix and match products
- `assets/src/frontend/css/features/custom.css` - Custom styling enhancements

**Content**:
- Wishlist off-canvas styling
- Product bundling interfaces
- Custom design enhancements
- Advanced UI components

**Loading**: Async loading after critical content is rendered

### ⚙️ Admin CSS (Low Priority - On Demand)
**Purpose**: Admin and editor specific styles

**Files**:
- `assets/src/admin/css/editor.css` - Block editor styling

**Content**:
- Gutenberg block editor enhancements
- Admin interface customizations
- Editor-specific styling

**Loading**: Only loaded in admin context or customizer

## JavaScript File Categorization

### 🚀 Critical JS (Immediate Load)
**Purpose**: Essential functionality for page stability and critical interactions

**Files**:
- `assets/src/critical/js/core.js` - Dependency validation and critical utilities

**Content**:
- jQuery availability check
- Essential polyfills
- Critical layout fixes
- Global utility functions

**Loading**: Loaded immediately in `<head>` without defer

### 🌐 Global JS (High Priority - Defer)
**Purpose**: Site-wide functionality needed on most pages

**Files**:
- `assets/src/frontend/js/components/general.js` - General UI enhancements
- `assets/src/frontend/js/components/minicart-control.js` - Cart control logic

**Content**:
- Product variation display logic
- Mini cart opening/closing
- General UI interactions
- Site-wide event handlers

**Loading**: Deferred loading after DOM is ready

### 🛒 WooCommerce JS (Conditional - High Priority)
**Purpose**: E-commerce functionality for WooCommerce pages

**Files**:
- `assets/src/frontend/js/pages/archive.js` - Product archive functionality
- `assets/src/frontend/js/pages/single-product.js` - Product page interactions
- `assets/src/frontend/js/pages/checkout.js` - Checkout process
- `assets/src/frontend/js/pages/my-account.js` - Account management
- `assets/src/frontend/js/pages/thank-you.js` - Order confirmation
- `assets/src/frontend/js/pages/thank-you-inline.js` - Inline thank you scripts
- `assets/src/frontend/js/components/mini-cart.js` - Cart functionality

**Content**:
- Product interactions and AJAX
- Cart management and updates
- Checkout form handling
- Order processing scripts

**Loading**: Conditional loading on WooCommerce pages with defer

### 🎨 Features JS (Medium Priority - Lazy Load)
**Purpose**: Enhanced features that can load after core functionality

**Files**:
- `assets/src/frontend/js/features/wishlist-offcanvas.js` - Wishlist functionality
- `assets/src/frontend/js/features/mix-and-match-products.js` - Product bundling

**Content**:
- Wishlist management
- Advanced product interactions
- Feature-specific enhancements

**Loading**: Lazy loaded after initial page interaction

### ⚙️ Admin JS (Low Priority - On Demand)
**Purpose**: Admin and editor functionality

**Files**:
- `assets/src/admin/js/admin/my-account-admin.js` - Admin account management
- `assets/src/admin/js/editor/product-carousel-editor.js` - Block editor scripts

**Content**:
- Admin interface enhancements
- Block editor functionality
- Backend management tools

**Loading**: Only loaded in admin context

### 🎛️ Customizer JS (On Demand)
**Purpose**: WordPress customizer functionality

**Files**:
- `assets/src/admin/js/customizer/customizer-preview.js` - Customizer preview
- `assets/src/admin/js/customizer/my-account-customizer-preview.js` - Account customizer
- `assets/src/admin/js/customizer/wishlist-offcanvas-sync.js` - Wishlist sync
- `assets/src/admin/js/customizer/wishlist-offcanvas-variables.js` - Wishlist variables

**Content**:
- Live preview functionality
- Customizer control interactions
- Real-time style updates

**Loading**: Only loaded in customizer context

## Performance Impact

### Before Optimization
- **CSS**: 16 individual files, ~150KB total, 16 HTTP requests
- **JS**: 17 individual files, ~200KB total, 17 HTTP requests
- **Total**: 33 HTTP requests, multiple render-blocking resources

### After Optimization
- **Critical CSS**: Inlined, 0 HTTP requests, immediate render
- **CSS Bundles**: 4 conditional bundles, ~120KB total (minified), 2-4 HTTP requests
- **JS Bundles**: 5 conditional bundles, ~150KB total (minified), 2-5 HTTP requests
- **Total**: 4-9 HTTP requests, optimized loading sequence

### Expected Improvements
- **First Contentful Paint (FCP)**: 40-60% improvement
- **Largest Contentful Paint (LCP)**: 30-50% improvement
- **Cumulative Layout Shift (CLS)**: Reduced by critical CSS inlining
- **Total Blocking Time (TBT)**: 50-70% reduction

## Loading Strategy Implementation

### Critical Path Optimization
1. **Inline Critical CSS** - Immediate rendering of above-the-fold content
2. **Preload High Priority** - Global styles and essential scripts
3. **Conditional Loading** - WooCommerce assets only when needed
4. **Async/Defer Features** - Non-essential functionality loaded progressively
5. **On-Demand Admin** - Admin assets only in appropriate context

### Browser Caching Strategy
- **Critical**: Inlined (no caching needed)
- **Global**: Long-term caching with versioning
- **Conditional**: Context-specific caching
- **Features**: Progressive enhancement caching
- **Admin**: Admin-specific cache rules

This categorization ensures optimal loading performance while maintaining functionality and user experience across all contexts.
