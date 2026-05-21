# Product Slider Shortcode

Reusable product carousel that renders native WooCommerce/Blocksy product cards in a paginated slider.

## Usage

```
[bc_product_slider ids="8020,8142,8259,633701,8177,8292,8520,634487" columns="4" dots="1" arrows="1"]
```

### Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `ids`     | (required) | Comma-separated product IDs. Duplicates are ignored by WP_Query |
| `columns` | `4` | Visible products per page (desktop) |
| `dots`    | `1` | Show pagination dots (1=yes, 0=no) |
| `arrows`  | `1` | Show prev/next arrows (1=yes, 0=no) |
| `limit`   | `8` | Max products to query |
| `orderby` | `post__in` | Order by (preserves ID order by default) |

### How to Add/Remove Products

1. Open **Pages → Home** (ID 645429) in Gutenberg
2. Find the shortcode block `[bc_product_slider ids="..." ...]`
3. Edit the `ids` list — comma-separated WooCommerce product IDs
4. Save. No code changes needed.

To find a product ID: go to **Products → All Products**, hover over the product, the ID is in the URL (`post=XXXXX`).

## Architecture

The shortcode is a **thin wrapper** around WooCommerce's native product loop. It does NOT render its own card markup — it calls `wc_get_template_part('content', 'product')`, which means:

- Cards inherit **all Blocksy styling**: hover image swap, badges, wishlist buttons, card type
- Cards inherit **all WooCommerce behavior**: sale badges, out-of-stock badges, AJAX add-to-cart
- The shortcode only handles the **slider mechanics**: track, pagination, arrows, swipe

### Single Component Principle

One product card component, used everywhere. Change it once (via Blocksy Customizer or `woo-archive.css`), it updates on archive pages, homepage slider, related products — everywhere.

### Key: Blocksy Data Attributes

The `<ul class="products">` must include Blocksy's data attributes to enable card styling:

```html
<ul class="products columns-4" data-products="type-1" data-hover="swap">
```

- `data-products` — read from `blocksy_get_theme_mod('shop_cards_type', 'type-1')`
- `data-hover` — read from `woo_card_layout` → `product_image_hover` setting

Without these attributes, the product cards render as plain WooCommerce cards (no image swap, no Blocksy card type styling).

### Key: Blocksy CSS Grid Override

Blocksy uses CSS Grid (`--shop-columns`) on `[data-products]` elements. The slider needs flexbox, so we override with:

```css
.bc-product-slider__track ul.products {
    display: flex !important;
    grid-template-columns: none !important;  /* kills Blocksy grid */
    gap: 16px !important;
}
```

Without `grid-template-columns: none`, Blocksy's grid makes the `ul` wider than the container, causing cards to be cut off.

## Responsive Behavior

| Viewport | Breakpoint | Columns | Cards per page |
|----------|-----------|---------|---------------|
| Desktop | >999px | 4 | 4 |
| Tablet | 690–999px | 3 | 3 |
| Mobile | <690px | 2 | 2 |

Breakpoints match Blocksy's archive breakpoints (999.98px and 689.98px).

### Dots (Pagination)

PHP renders the **maximum** possible dots (based on smallest column count = 2). JS shows/hides dots based on the current viewport:

- 8 products, desktop (4 cols) → 2 dots
- 8 products, tablet (3 cols) → 3 dots
- 8 products, mobile (2 cols) → 4 dots

### Sliding

JS uses **pixel-based offsets** (`offsetLeft`) not percentage-based `translateX`. This correctly handles CSS `gap` between cards, which percentage-based math gets wrong.

## Performance

| Metric | Value |
|--------|-------|
| JS size | ~1.5KB (vanilla, no dependencies) |
| CSS size | ~2KB (conditional, homepage only) |
| External libraries | None (no Swiper, Slick, or jQuery) |
| DB queries | 1 × WP_Query (by product IDs) |
| HTTP requests | 0 additional (inline with page) |

For comparison: Slick.js = 43KB + jQuery. Swiper = 140KB. MetaSlider (previously used) = 47KB + jQuery.

## Files

| File | Purpose |
|------|---------|
| `inc/product-slider.php` | Shortcode registration + PHP render logic |
| `assets/js/product-slider.js` | Vanilla JS carousel (pixel-based pagination, dots, arrows, swipe) |
| `assets/css/components/product-slider.css` | Slider layout CSS (flex track, calc() widths, responsive breakpoints, dots, arrows) |

### Dependency: `woo-archive.css`

The full-width button and price suffix styles are in `woo-archive.css`, which is loaded on the homepage via `is_front_page()` in `enqueue.php`. If the shortcode is used on other pages, add that page condition to the enqueue.

## How It Works

1. **PHP** (`inc/product-slider.php`):
   - Queries products by ID via `WP_Query`
   - Sets `wc_set_loop_prop('columns', $columns)` for WooCommerce grid classes
   - Reads Blocksy Customizer settings (`shop_cards_type`, `woo_card_layout`) to build `data-products` and `data-hover` attributes
   - Loops with `wc_get_template_part('content', 'product')` — same template as archive pages
   - Renders max dots for smallest viewport (min 2 columns)
   - Outputs arrow buttons and dot pagination

2. **JS** (`assets/js/product-slider.js`):
   - Page-based navigation: slides by `columns` count per viewport
   - Pixel-based offset: `items[targetIndex].offsetLeft` handles CSS gap correctly
   - Responsive: `getColumns()` returns 4/3/2 matching CSS breakpoints
   - Dot show/hide on resize: JS manages visibility of PHP-rendered dots
   - Touch swipe support (50px threshold)
   - No dependencies (vanilla JS)

3. **CSS** (`assets/css/components/product-slider.css`):
   - Overrides Blocksy CSS Grid with `display: flex` + `grid-template-columns: none`
   - Card widths via `calc((100% - gaps) / cols)` per breakpoint
   - `gap: 16px` between cards (no padding/margin hacks)
   - `overflow: hidden` on container
   - Dot colors: inactive `#888888`, hover `#B8A898` (scale 1.25x), active `#E7D4B0`

## Enqueue

Loaded conditionally on the homepage only (`inc/enqueue.php`):

```php
if ( is_front_page() ) {
    blocksy_child_enqueue_component( 'homepage', $css_url, $css_path );
    blocksy_child_enqueue_component( 'product-slider', $css_url, $css_path );
    // + hero-slider.js, product-slider.js
}
```

`woo-archive.css` also loads on homepage for product card button/price styles:

```php
if ( is_shop() || is_product_category() || is_product_tag() || is_front_page() ) {
    blocksy_child_enqueue_component( 'woo-archive', $css_url, $css_path );
}
```

To use on other pages, add the page condition to both enqueue blocks.

## Troubleshooting

| Symptom | Cause | Fix |
|---------|-------|-----|
| No image hover swap | Missing `data-products`/`data-hover` on `<ul>` | Check `product-slider.php` reads Blocksy theme mods |
| Cards cut off on right | Blocksy CSS Grid active | Verify `grid-template-columns: none !important` in CSS |
| Dots don't match pages | PHP renders too few dots | `$min_columns = 2` in PHP; JS shows/hides |
| Slide offset wrong | Percentage-based translateX | JS must use `offsetLeft` pixel calculation |
| Buttons not full-width | `woo-archive.css` not loaded | Add page condition to enqueue |
| Duplicate products ignored | WP_Query deduplicates `post__in` | Use unique IDs only |

## Changelog

- **2026-04-02**: Added Lotus Flower (8177) as 8th product. Now 8 products, 2 pages on desktop.
- **2026-04-01**: Major refactor — pixel-based offsets, CSS gap layout, responsive 4/3/2 columns, Blocksy grid override, dot colors matching live site (#E7D4B0 active, #888888 inactive).
- **2026-04-01**: Added Blocksy `data-products` and `data-hover` attributes to `<ul>` so cards match archive styling (hover swap, card type).
- **2026-04-01**: Added `woo-archive.css` on homepage for full-width buttons and price suffix.
- **2026-03-31**: Initial implementation. Custom carousel replacing `[products best_selling]` for homepage "Favourite Buys" section.
