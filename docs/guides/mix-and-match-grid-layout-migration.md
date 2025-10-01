---
title: "Mix and Match Products: Flexbox to Grid Migration"
description: "Migration guide for converting Mix and Match products layout from flexbox to CSS Grid with responsive design"
category: "guide"
last_updated: "2025-09-26"
framework: "wordpress-theme"
domain: "catalog"
layer: "frontend"
tags: [migration, css-grid, flexbox, responsive, woocommerce]
---

# Overview

This guide documents the migration from flexbox-based layout to CSS Grid for WooCommerce Mix and Match products. The change provides better control over product card sizing, ensures equal widths, and improves responsive behavior.

# Migration Changes

## Layout System Change

### Before (Flexbox)
```css
.mnm_child_products.products {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  justify-content: space-between;
}
```

### After (CSS Grid)
```css
.mnm_child_products.products {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}
```

## Product Count Configuration

### Before
- Fixed 2-3 products initially
- Used `get_option('wc_mnm_number_columns', 3)`
- Hardcoded column-based hiding

### After
- Configurable 4 products initially
- Configurable load more count (4 products)
- Simplified nth-child hiding rules

## Responsive Design

### Before
```css
@media screen and (max-width: 768px) {
  .mnm_child_products.products.columns-3 li.product,
  .mnm_child_products.products.columns-4 li.product {
    width: 47% !important;
  }
}
```

### After
```css
@media screen and (max-width: 768px) {
  .mnm_child_products.products {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media screen and (max-width: 480px) {
  .mnm_child_products.products {
    grid-template-columns: 1fr;
  }
}
```

# Usage

## Configuration Variables

Update the configuration in `includes/customization/mix-and-match-products.php`:

```php
function get_mnm_config() {
    return array(
        'initial_products_count' => 4,  // Show 4 products initially
        'load_more_count' => 4,         // Load 4 more products per click
        'grid_columns' => 4             // 4-column grid layout
    );
}
```

## CSS Grid Benefits

1. **Equal Width Cards**: All product cards have identical widths
2. **Responsive Control**: Easy breakpoint management
3. **Simplified CSS**: No complex width calculations
4. **Better Alignment**: Automatic grid alignment

# Parameters

## Grid Configuration

| Setting | Desktop | Tablet | Mobile |
|---------|---------|--------|--------|
| Columns | 4 | 2 | 1 |
| Breakpoint | Default | ≤768px | ≤480px |
| Gap | 24px | 24px | 24px |

## Product Display

| Setting | Value | Description |
|---------|-------|-------------|
| Initial Count | 4 | Products shown on page load |
| Load More Count | 4 | Products loaded per button click |
| Grid Columns | 4 | Desktop grid columns |

# Returns

## Layout Improvements
- Consistent product card widths
- Better responsive behavior
- Cleaner CSS architecture
- Improved visual alignment

## Performance Benefits
- Reduced CSS complexity
- Better browser rendering
- Simplified responsive calculations

# Dependencies

## CSS Features
- CSS Grid support (IE11+)
- CSS Custom Properties
- Media queries

## JavaScript Updates
- Updated data attributes handling
- New configuration parameter reading
- Maintained backward compatibility

# Testing

## Visual Testing
```bash
# Test grid layout on different screen sizes
# Desktop: 4 columns
# Tablet: 2 columns  
# Mobile: 1 column

# Verify equal width product cards
# Check gap consistency
# Test load more functionality
```

## Browser Compatibility
```bash
# Test on modern browsers (Chrome, Firefox, Safari, Edge)
# Verify CSS Grid support
# Check responsive breakpoints
```

## Configuration Testing
```php
// Test different configuration values
function get_mnm_config() {
    return array(
        'initial_products_count' => 6,
        'load_more_count' => 2,
        'grid_columns' => 3
    );
}
```

# Changelog

- **2025-09-26**: Migration completed
  - Converted from flexbox to CSS Grid
  - Added configurable product counts
  - Implemented responsive grid system
  - Updated JavaScript configuration handling
  - Simplified CSS selectors and rules
