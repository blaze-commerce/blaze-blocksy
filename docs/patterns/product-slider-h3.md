# Product Slider — H3 Title Override

## Purpose

Product titles inside the `[bc_product_slider]` shortcode render as `<h3>` instead of the global `<h2>` default. This creates proper heading hierarchy on pages where the slider sits under a section heading (H2).

## How It Works

A temporary filter on `theme_mod_woo_card_layout` sets `heading_tag: "h3"` on the `product_title` element **only during the slider's WC loop**. The filter is added before the loop and removed immediately after `wp_reset_postdata()`.

This means:
- Product slider titles → `<h3>` ✅
- Category grid titles → `<h2>` (unchanged)
- New Arrivals / other shortcode titles → `<h2>` (unchanged)
- Shop/archive page titles → `<h2>` (unchanged)

## Heading Hierarchy (Homepage)

```
H1 — Scent your home... (bc-section-heading bc-visual-h2)
  H2 — Category titles (Candles + Refills, etc.)
  H2 — Favourite Buys (section heading)
    H3 — Product titles (Japanese Honeysuckle, etc.)
  H2 — Creating a Moment For You (section heading)
  H2 — Hand-Poured with Love (section heading)
  H2 — New Arrivals (section heading)
    H2 — Product titles (global default, not inside slider)
  H2 — Customer's Share their Experience
  H2 — Join Our VIP list
```

## File

`inc/product-slider.php` — filter function `bc_slider_h3_product_titles()` + temporary `add_filter` / `remove_filter` around WC loop.

## CSS

No CSS changes needed — Blocksy's product card styles use `.woocommerce-loop-product__title` class regardless of heading level.
