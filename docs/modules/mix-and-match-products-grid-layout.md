---
title: "Mix and Match Products Grid Layout Configuration"
description: "Configuration for Mix and Match products display with grid layout and customizable product counts"
category: "module"
last_updated: "2025-09-26"
framework: "wordpress-theme"
domain: "catalog"
layer: "frontend"
tags: [woocommerce, mix-and-match, grid, products, configuration]
---

# Overview

This module provides configuration for WooCommerce Mix and Match products display, implementing a CSS Grid layout with customizable product counts for initial display and load more functionality. The system replaces the previous flexbox layout with a more consistent grid system that ensures equal width product cards.

# Usage

The configuration is automatically applied to all Mix and Match products. To modify the display settings, update the configuration variables in the PHP file.

```php
// Configuration variables for Mix and Match products display
function get_mnm_config() {
    return array(
        'initial_products_count' => 4,  // Number of products to show initially
        'load_more_count' => 4,         // Number of products to load per "Load More" click
        'grid_columns' => 4             // Number of grid columns for layout
    );
}
```

# Parameters

## Configuration Array

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `initial_products_count` | integer | 4 | Number of products displayed initially |
| `load_more_count` | integer | 4 | Number of products loaded per "Load More" click |
| `grid_columns` | integer | 4 | Number of grid columns for desktop layout |

## Data Attributes

The load more button receives the following data attributes:

| Attribute | Description |
|-----------|-------------|
| `data-initial` | Initial products count |
| `data-load-more` | Products to load per click |
| `data-grid-columns` | Grid columns configuration |

# Returns

- **CSS Grid Layout**: Responsive grid with equal-width product cards
- **Load More Button**: Dynamically generated with configuration data
- **Responsive Design**: Adapts to tablet (2 columns) and mobile (1 column)

# Dependencies

## Internal Dependencies
- `WC_MNM_PLUGIN_FILE` constant (WooCommerce Mix and Match plugin)
- `get_mnm_config()` function for configuration
- CSS grid layout styles
- JavaScript load more functionality

## External Dependencies
- WooCommerce Mix and Match plugin
- jQuery library
- WordPress theme system

# Testing

## Manual Testing Steps

1. **Initial Display Test**:
   ```bash
   # Navigate to a Mix and Match product page
   # Verify 4 products are displayed initially in a 4-column grid
   ```

2. **Load More Test**:
   ```bash
   # Click "Load More" button
   # Verify 4 additional products are loaded
   # Verify grid layout is maintained
   ```

3. **Responsive Test**:
   ```bash
   # Test on tablet viewport (768px and below)
   # Verify 2-column grid layout
   # Test on mobile viewport (480px and below)
   # Verify 1-column grid layout
   ```

## Configuration Test

```php
// Test different configuration values
function get_mnm_config() {
    return array(
        'initial_products_count' => 6,  // Test with 6 initial products
        'load_more_count' => 3,         // Test with 3 products per load
        'grid_columns' => 3             // Test with 3-column grid
    );
}
```

# Changelog

- **2025-09-26**: Initial implementation
  - Replaced flexbox layout with CSS Grid
  - Added configurable product counts
  - Implemented responsive grid design
  - Updated JavaScript to use new configuration system
  - Added equal-width product card support
