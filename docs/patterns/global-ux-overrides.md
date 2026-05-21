# Global UX Overrides

> Site-wide UX decisions that apply across all pages.
> These rules live in `base.css` (loaded on every page) or WooCommerce settings.

---

## File Map

```
blocksy-child/
├── assets/css/
│   └── base.css    ← Global rules (loaded on EVERY page, keep minimal)
```

---

## Hide "View Cart" Button

**What:** After adding a product to cart, WooCommerce appends a "View cart" link next to the ATC button. This is hidden globally.

**Why:** Per Baymard UX guidelines, we discourage users from visiting the cart page. After adding to cart, users should go directly to checkout via the mini cart off-canvas panel.

**CSS** (`assets/css/base.css`):
```css
.added_to_cart.wc-forward {
    display: none;
}
```

**Affects:** Product pages, archive/category pages, mini cart — anywhere WooCommerce renders the "View cart" link.

---

## Store Notice

**What:** WooCommerce site-wide store notice banner (e.g. "Woohoo! Add any product from the Candle Refills to your cart...").

**How to disable (no code):**
1. WP Admin > WooCommerce > Settings > General
2. Uncheck "Enable site-wide store notice"
3. Save

**How to hide on specific pages only (CSS):**
```css
/* Hide on product pages only */
.single-product .woocommerce-store-notice {
    display: none;
}
```

---

## Related Patterns

- **[single-product-page.md](single-product-page.md)** — PDP-specific customizations
- **[wishlist-offcanvas.md](wishlist-offcanvas.md)** — Off-canvas wishlist panel
