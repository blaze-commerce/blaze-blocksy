# WooCommerce Category Grid

## Overview
Reusable card styling for the `[product_categories]` shortcode. Square image + centered title bar with background color.

## Usage
Add `[product_categories]` shortcode in any Gutenberg Shortcode block. The styling applies automatically.

## CSS File
`assets/css/components/woo-category-grid.css`

## Loaded On
Any WooCommerce page (inside `function_exists('is_shop')` block in `enqueue.php`).

## Custom Properties
Override on a parent container to customize per-instance:

| Variable | Default | Description |
|----------|---------|-------------|
| `--bc-cat-radius` | `8px` | Card border radius |
| `--bc-cat-title-bg` | `palette-color-6` (#FAF6F2) | Title bar background |
| `--bc-cat-title-color` | `palette-color-3` (#746A5F) | Title text color |
| `--bc-cat-title-hover` | `palette-color-1` (#111) | Title hover color |
| `--bc-cat-title-size` | `24px` | Desktop font size |
| `--bc-cat-title-size-mobile` | `16px` | Mobile font size |
| `--bc-cat-title-height` | `74px` | Title bar min height |

## Example: Custom colors on a specific page
```css
.my-custom-section {
    --bc-cat-title-bg: #E8F0F4;
    --bc-cat-title-color: #333;
    --bc-cat-radius: 12px;
}
```

## HTML Structure (rendered by WooCommerce)
```html
<li class="product-category product">
  <figure>
    <a class="ct-media-container has-hover-effect">
      <img ... />
    </a>
  </figure>
  <h2 class="woocommerce-loop-category__title">
    <a>Category Name <mark class="count">(48)</mark></a>
  </h2>
</li>
```

## Notes
- Count badge is hidden via CSS
- Image hover: scale(1.03) with 0.3s ease transition
- Blocksy `ct-media-container` border-radius overridden to 0 (card handles rounding)
- Title bar uses flexbox for vertical centering
