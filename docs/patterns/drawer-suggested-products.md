# Drawer Suggested Products — Shared Architecture

> **Master doc** for the suggested-products carousel that appears in BOTH the mini cart drawer AND the wishlist drawer, across 7 distinct states. Image #1 (mini cart with items, Blocksy native rendering) is the visual source of truth. All 7 states must render byte-for-byte identically to it.

---

## The 7 states

| # | Drawer | Cart/Wishlist state | Render path |
|---|---|---|---|
| 1 | mini cart | with items (any count) | Blocksy native via `blocksy:pro:woo-extra:offcanvas:minicart:list:after` priority 10 → `cart-suggested-products/feature.php` (we don't touch this) |
| 2 | mini cart | empty (after delete) | `bc_mini_cart_empty_state()` → shared helper with `'bc-minicart-suggested-grid'` |
| 3 | mini cart | empty (cold visit) | same as #2 |
| 4 | wishlist | with items, **guest** | `wp_footer` panel render → `blocksy_child_render_wishlist_suggested()` → shared helper with `'bc-wishlist-suggested-grid'` |
| 5 | wishlist | with items, **logged-in** | same render, no signup CTA |
| 6 | wishlist | empty, **guest** | same render → bestsellers fallback |
| 7 | wishlist | empty, **logged-in** | same render → bestsellers fallback |

State #1 is Blocksy native and works out of the box — we don't touch it. States 2–7 go through our shared helper and **must match #1**.

> **Layout note for state #1:** the carousel is moved at runtime into the mini cart's unified-scroll wrapper (`<div.bc-cart-scroll>`) by `assets/js/cart-offcanvas.js`. CSS gives it `margin-top: auto` so on short carts (1–2 items) it hugs the bottom of the scroll area, and on long carts it stacks naturally beneath `<ul>` with the wrapper scrolling. The carousel rendering itself is untouched — only its DOM parent changes. See `offcanvas.md` → "Mini Cart Unified Scroll" for the full architecture.

---

## File map

```
blocksy-child/
├── inc/
│   ├── helpers.php                ← bc_render_blocksy_suggested_carousel() (shared helper)
│   │                                bc_resolve_suggested_product_ids() (caller IDs → bestsellers
│   │                                fallback, 1h transient cached)
│   ├── mini-cart-empty.php        ← bc_mini_cart_empty_state() — calls helper for states 2-3
│   └── wishlist-offcanvas.php     ← blocksy_child_render_wishlist_suggested() — calls helper
│                                    for states 4-7
├── assets/
│   ├── css/components/
│   │   └── wishlist-offcanvas.css ← LAYER 8 CSS shim (mirrors Blocksy customizer rules under
│   │                                our renamed wrappers)
│   └── js/
│       └── wishlist-flexy-sentinel.js ← LAYER 7 runtime visibility sentinel (post-Blocksy-update
│                                         QA — logs console.error if items go zero-dim)
└── clients/byronbay/byronbay.php  ← LAYER 6 admin notice on Blocksy version change
```

---

## The shared helper

```php
bc_render_blocksy_suggested_carousel( array $product_ids, string $unique_class ): string
```

Called in 6 places (states 2–7). Always pass:
- `$product_ids` — the IDs to render. Empty array → bestsellers fallback.
- `$unique_class` — REQUIRED, must be `'bc-minicart-suggested-grid'` or `'bc-wishlist-suggested-grid'`.

Returns the full HTML string (or empty string if catalog has zero published products).

---

## 8-layer bulletproofing

Each layer is independent. Even if every other layer breaks, the drawer still shows products.

### LAYER 1 — Cart-fragments-safe class rename

WooCommerce registers `[class*="ct-suggested-products--mini-cart"]` as a cart-fragments AJAX selector. Every cart change wipes any element matching that substring. Blocksy's native template renders with that exact class — we rename it.

The helper does:
```php
str_replace(
    'ct-suggested-products--mini-cart',
    $unique_class . ' ct-suggested-products',
    $html
);
```

Final wrapper class: `bc-minicart-suggested-grid ct-suggested-products` (or `bc-wishlist-...`).

Why two classes:
- `bc-*-suggested-grid` — does NOT contain `ct-suggested-products--mini-cart` substring → cart-fragments wipe doesn't match
- `ct-suggested-products` — DOES match `[class*="ct-suggested-products"]` → Blocksy's customizer CSS AND flexy.js arrow binding both work

### LAYER 2 — Native flexy carousel preserved (DO NOT strip flexy hooks)

Keep `flexy-container` class, `data-flexy="no"`, and `data-autoplay` untouched. Blocksy's `flexy.min.js` auto-initializes any `.flexy-container` element on first `mouseover` and produces the polished prev/next carousel. Stripping these attributes was the 2026-04-28 morning regression — produced a raw stacked layout.

To disable autoplay: set the customizer setting, NOT strip the attribute.
```bash
wp option patch update theme_mods_blocksy-child mini_cart_suggested_products_autoplay no
```

### LAYER 3 — ID validation

The helper validates IDs with `wc_get_product()` before rendering. Blocksy's template crashes on invalid IDs.

### LAYER 4 — Bestsellers fallback (caller side)

If caller IDs are empty/invalid, fall back to top 4 bestsellers. Cached in a 1-hour transient (`bc_suggested_bestsellers_4`). Bust hooks: `save_post_product`, `woocommerce_product_set_stock`.

### LAYER 5 — Output validation + simple-grid fallback (renderer side)

Wrap `blocksy_render_view()` in try/catch. Validate the returned HTML actually contains product markup. If broken/missing, render a self-contained simple HTML grid (`bc-fallback-grid`). Even if Blocksy is uninstalled, drawers still show products.

### LAYER 6 — Version sentinel (admin notice)

`clients/byronbay/byronbay.php` watches Blocksy theme + Blocksy Companion Pro version strings via `admin_init`. Surfaces an admin notice when either changes — prompt the team to QA the drawers.

### LAYER 7 — Runtime visibility sentinel (browser console)

`assets/js/wishlist-flexy-sentinel.js` checks the rendered drawer on every open. If items are zero-dim, off-screen, or transformed off-axis, logs `console.error('[BBC sentinel] Wishlist drawer suggested products are NOT VISIBLE')` and tags `window.bcWishlistFlexyState = 'BROKEN'`.

### LAYER 8 — CSS shim under renamed wrappers

In `assets/css/components/wishlist-offcanvas.css`. Mirrors Blocksy's customizer-generated CSS that targets the EXACT class `.ct-suggested-products--mini-cart` so it applies to our renamed wrapper too. Required because:
- `--grid-columns-width: calc(100% / 2)` — drives 2-column layout (without it, items render at `flex: 0 0 100%` = 1-column)
- `--product-image-width`, `--theme-border-radius`, `--slider-arrow-button-size`
- Title typography (Quicksand n5 14px), price typography (Quicksand n5 13px)
- `.ct-module-title` flex/uppercase/12px/700 (without it, heading renders as block 18px regular)
- `.flexy-item > section` flex column for title/price stacking (without it, they render inline → "Reed Sticks$33.00")
- `[data-flexy*="no"] .flexy-item:nth-child(n+3) { height: 1px }` — collapses items 3+ in static state

**When client changes column count or typography in customizer, also update the LAYER 8 shim values.**

---

## flexy.js arrow binding (the substring trick)

Blocksy's `wp-content/themes/blocksy/static/js/frontend/flexy.js` line 32:
```js
const maybeSuggested = sliderEl.closest('[class*="ct-suggested-products"]');
if (maybeSuggested) {
    leftArrow = maybeSuggested.querySelector('.ct-arrow-prev');
    rightArrow = maybeSuggested.querySelector('.ct-arrow-next');
}
```

Without `ct-suggested-products` in our wrapper class, this `closest()` fails and arrows stay decorative. The dual-class rename (LAYER 1) satisfies this lookup.

**Lazy-mount note:** flexy initializes on first `mouseover` over the carousel. Programmatic `.click()` from JS without prior hover does nothing. Real users always hover before clicking, so this is invisible in production but matters when writing Playwright tests — dispatch `mouseover` first.

---

## Validation checklist (run after any change)

After ANY change touching the drawer suggested-products code, Playwright-verify ALL 7 states:
- State 1: add a product, open mini cart → carousel matches Image #1 visually
- State 2: remove all items in drawer → carousel still visible (empty state)
- State 3: hard refresh in incognito with empty cart → carousel visible (cold)
- States 4–7: open wishlist drawer with various wishlist contents and login states
- After 4-second wait: cart-fragments AJAX should have fired but NOT wiped any carousel
- All breakpoints (320 / 375 / 768 / 1400 / 2560)
- No auto-rotation if `mini_cart_suggested_products_autoplay = "no"`
- Click prev/next arrows → items slide; panel does NOT close
- Computed CSS diff `.ct-suggested-products--mini-cart` (native, state 1) vs `.bc-minicart-suggested-grid` (renamed, states 2–3) → must be byte-for-byte identical for `.ct-module-title`, `.flexy-item > section`, `.ct-product-title`, `.price`

---

## CSS rules to AVOID writing

Don't add `transform: none !important` overrides on `.flexy-items > *`. Don't override `.flexy-view` height. Don't override `.flexy` overflow. **All of those break Blocksy's flexy carousel.**

If states 2–7 look broken, the cause is almost always one of these:
1. `flexy-container` class was stripped at PHP render time → fix at PHP layer, not CSS
2. `data-flexy` attribute was stripped → fix at PHP layer
3. Cart-fragments wiped the wrapper → confirm the dual-class rename is in place (`bc-* ct-suggested-products`)
4. Items render 1-column instead of 2 → LAYER 8 CSS shim is missing the column-width rule
5. Heading is lowercase / regular weight → LAYER 8 shim missing the `.ct-module-title` block
6. Arrows don't slide → wrapper class is missing `ct-suggested-products` substring (flexy.js can't find the arrows)
7. Wishlist drawer arrows close the panel → `assets/js/wishlist-offcanvas.js` is intercepting in capture phase OR the openPanel() didn't pass `clickOutside: false`

---

## Related patterns

- [wishlist-offcanvas.md](wishlist-offcanvas.md) — Full wishlist drawer architecture (data sync, panel open/close, MutationObserver)
- [offcanvas.md](offcanvas.md) — Shared off-canvas panel styling (padding, header, icons, qty stepper)
- [recently-viewed.md](recently-viewed.md) — Recently Viewed product IDs cookie source

