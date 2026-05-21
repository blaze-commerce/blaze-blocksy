# Recently Viewed Products — Custom PDP Section

> **This is custom development, NOT a Blocksy or WooCommerce built-in feature.** Neither Blocksy nor WooCommerce provides a "Recently Viewed Products" section on the single product page out of the box. This is ~50 lines of PHP with zero plugin dependencies.

---

## Why Custom Development Was Needed

| Option | Why It Doesn't Work |
|--------|-------------------|
| Blocksy built-in | Blocksy's `RecentlyViewedProducts` class only provides cookie tracking for Cart Suggested Products (mini cart, cart page, checkout). No PDP rendering exists. The `woo_has_recently_viewed_products` theme mod does not map to any actual PDP feature. |
| WooCommerce core | `wc_track_product_view()` only sets the cookie when the "Recently Viewed Products" **widget** is active in a sidebar (line 912: `is_active_widget` check). Blocksy doesn't use traditional sidebars on product pages. |
| Plugin | Adds a dependency for something that's 50 lines of PHP. |

---

## File Structure

```
blocksy-child/
├── inc/
│   ├── loader.php              ← MODIFIED: loads recently-viewed.php
│   └── recently-viewed.php     ← NEW: cookie tracking + product grid render
└── docs/patterns/
    └── recently-viewed.md      ← This file
```

---

## How It Works

### 1. Cookie Tracking (`bc_track_product_view`)

Hooked on `template_redirect` at priority 21 (after WC's native tracker at 20).

- Reads existing `woocommerce_recently_viewed` cookie
- Appends current product ID
- Keeps max 15 products
- Sets cookie via `wc_setcookie()`
- No widget dependency — runs on every `is_singular('product')` page

**Why we can't rely on WC's native tracking:** WooCommerce's `wc_track_product_view()` has this guard:
```php
if ( ! is_singular( 'product' ) || ! is_active_widget( false, false, 'woocommerce_recently_viewed_products', true ) ) {
    return; // exits if no widget is active
}
```

### 2. Rendering (`bc_render_recently_viewed_products`)

Hooked on:
- `blocksy:woocommerce:product-single:related:after` (Blocksy's hook, fires after Related Products)
- `woocommerce_after_main_content` at priority 10 (fallback)
- Uses `static $rendered` flag to prevent double rendering

**Rendering logic:**
1. Reads `woocommerce_recently_viewed` cookie
2. Excludes the current product
3. Limits to 8 most recent (newest first)
4. Queries published products via `WP_Query`
5. Renders using Blocksy's exact HTML structure (matching Related Products)

**HTML structure matches Related Products exactly:**
```html
<section class="bc-recently-viewed related products is-layout-slider is-width-constrained">
  <h2 class="ct-module-title">Recently Viewed</h2>
  <div class="flexy-container" data-flexy="no">
    <div class="flexy">
      <div class="flexy-view" data-flexy-view="boxed">
        <div class="products flexy-items columns-4" data-products="type-1" data-hover="swap">
          <div class="flexy-item"><!-- product card via content-product.php --></div>
        </div>
      </div>
    </div>
  </div>
</section>
```

This ensures:
- Same content width constraint (`is-width-constrained`)
- Same heading style (`ct-module-title`)
- Same product card layout, hover effects, wishlist buttons
- Same column count (reads from Blocksy's `woocommerce_related_products_slideshow_columns` theme mod)

---

## CSS

In `assets/css/components/woo-single.css`:

```css
/* Recently Viewed Products — spacing above section. */
.bc-recently-viewed {
    margin-top: 50px;
}

/* Full-width ATC buttons (shared with Related + Suggested). */
.bc-recently-viewed .ct-woo-card-actions {
    display: flex;
    width: 100%;
}
.bc-recently-viewed .ct-woo-card-actions .button {
    width: 100%;
}
```

---

## Behaviour Notes

- **First-time visitors:** Section won't show (no cookie yet). It appears after browsing 2+ products.
- **Cookie limit:** 15 products max, oldest dropped first.
- **Display limit:** 8 products shown, newest first.
- **Current product excluded:** The product you're viewing is filtered out.
- **Cache-safe:** Cookie is set via PHP headers on every uncached product page visit. On cached pages, the cookie already exists in the browser from prior visits.

---

## Dependencies

- WooCommerce (for `wc_setcookie()`, `wc_get_template_part()`)
- Blocksy theme (for `ct-module-title`, `flexy-*` classes, `is-width-constrained`)
- No plugins, no ACF, no jQuery
