# Off-Canvas Wishlist Panel

> **This is NOT a Blocksy built-in feature.** Blocksy has no off-canvas wishlist panel. This is custom child theme code, reusable across all projects using this child theme.

---

## File Structure

```
blocksy-child/
├── inc/
│   ├── loader.php               ← MODIFIED: loads wishlist-offcanvas.php
│   ├── enqueue.php              ← MODIFIED: loads CSS + JS globally
│   ├── woocommerce.php          ← no change
│   └── wishlist-offcanvas.php   ← Panel HTML shell, preloaded JSON data, dynamic CSS from cart panel DB, AJAX endpoint for single product fetch, suggested products render
├── assets/
│   ├── css/components/
│   │   └── wishlist-offcanvas.css  ← Panel inner layout, item styles, empty/error/guest states, continue shopping button, suggested products slider
│   └── js/
│       └── wishlist-offcanvas.js   ← Client-side rendering, MutationObserver for counter changes, cookie/AJAX data sync, panel open/close via ctEvents, remove sync, minimal flexy slider for suggested products
```

---

## How It Works

1. **`wishlist-offcanvas.php`** renders an empty panel shell in the page footer + preloads wishlist product data as JSON (`bcWishlistData`) + outputs dynamic CSS that mirrors the cart panel styling from Blocksy Customizer
2. **`wishlist-offcanvas.js`** reads preloaded data + cookie (guests) or Blocksy AJAX (logged-in) to get current wishlist IDs, renders panel content client-side (no AJAX for initial load)
3. **MutationObserver** watches the header wishlist counter badge — when Blocksy updates it after add/remove, JS syncs data and auto-opens panel
4. **Header wishlist icon click** → `openPanel()` runs. Calls Blocksy's `ct:overlay:handle-click` with `clickOutside: false` (so Blocksy doesn't bind its own close-on-outside-click listener — see Key Technical Decisions below for why). Our own click-outside listener is bound via `bindClickOutsideOnce()` and uses `closest('.ct-panel-inner')` for accurate inside/outside detection.
5. **PDP wishlist button** → Blocksy handles add/remove natively, counter updates, panel auto-opens
6. **Remove inside panel** → animate out, sync to server (logged-in: `blc_ext_wish_list_sync_likes` with full list, guest: cookie update), update counter
7. **Guest users** see "Sign up" prompt with 7-day cache warning
8. **Logged-in users** see "Continue Shopping" button when empty
9. **Suggested products** rendered via shared helper `bc_render_blocksy_suggested_carousel()` — see [drawer-suggested-products.md](drawer-suggested-products.md) for the full architecture. Wrapper class is `bc-wishlist-suggested-grid ct-suggested-products` — escapes WC cart-fragments AJAX wipe AND satisfies Blocksy's flexy.js `closest('[class*="ct-suggested-products"]')` arrow lookup. Arrows slide natively via Blocksy's flexy carousel — no custom scroll handler needed.

---

## Blocksy Settings Required

| Setting | Value | Why |
|---------|-------|-----|
| Blocksy Wishlist Extension | **ON** | Master on/off switch for wishlist |
| `product_actions` layout element | **DISABLED** | Avoid duplicate wishlist button on product page (see `wishlist-inline.md`) |

---

## CSS Variables Used (all global, all with fallbacks)

| Variable | Purpose | Fallback |
|----------|---------|----------|
| `--theme-heading-2-font-family` | Panel heading font family | system default |
| `--theme-heading-2-font-size` | Panel heading font size | system default |
| `--theme-heading-2-font-weight` | Panel heading font weight | system default |
| `--theme-heading-2-color` | Panel heading color | system default |
| `--theme-border-color` | Item dividers, panel header border | `#e0e0e0` |
| `--theme-headings-color` | Item heading text | `#333` |
| `--theme-text-color` | Item body text, icon colors | `#333` |
| `--theme-link-hover-color` | Product name hover | `#000` |
| `--theme-button-background-initial-color` | Sign Up / Continue Shopping button background | `#333` |
| `--theme-button-text-initial-color` | Sign Up / Continue Shopping button text | `#fff` |
| `--theme-button-border-radius` | Thumbnail corners, button corners | `8px` |
| `--theme-palette-color-6` | Guest notice background | `#f5f5f5` |

---

## Panel States

| State | What shows |
|-------|-----------|
| **Empty + logged-in** | "Your Wishlist is Empty" + Continue Shopping button + Suggested Products |
| **Empty + guest** | "Your Wishlist is Empty" + guest 7-day notice + Sign Up button + Suggested Products |
| **Has items + logged-in** | Product cards + Suggested Products |
| **Has items + guest** | Product cards + guest notice + Sign Up + Suggested Products |
| **Error** | "Could not load wishlist. Please refresh the page." |

---

## Key Technical Decisions

### No jQuery — vanilla JS only
The entire wishlist panel runs on vanilla JS. No jQuery dependency.

### MutationObserver instead of ctEvents for wishlist changes
Blocksy fires various events, but MutationObserver on the header counter badge is more reliable — it catches every add/remove regardless of source (PDP button, quick view, etc.).

### Client-side rendering from preloaded JSON
`wishlist-offcanvas.php` outputs all current wishlist product data as a JSON object (`bcWishlistData`). The JS reads this on init, so the first panel open requires zero AJAX. Only when a NEW product is added that is not in the preloaded cache does JS fetch from the server.

### Cookie reading for guests
Guest wishlist IDs come from the `blc_products_wish_list` cookie, which is always current. This is more reliable than reading `ct_localizations` which can be stale from page cache.

### Dynamic panel styling from Blocksy Customizer
Panel width, backdrop, shadow, and close button styles are read from the Blocksy header builder DB (cart panel settings) and output as inline CSS. Changes made in Customizer auto-apply to the wishlist panel — no manual CSS updates needed.

### Native flexy.js arrow handler (NOT a custom scrollBy)
Earlier versions of this drawer used a custom `scrollBy` arrow handler because we believed Blocksy's flexy.js doesn't init inside custom panels. **That belief was wrong** — flexy lazy-mounts on first `mouseover` over any `.flexy-container` element. The 2026-04-28 evening fix removed our custom handler and added the `ct-suggested-products` substring to the wrapper class so flexy's `closest()` arrow lookup finds them. See [drawer-suggested-products.md](drawer-suggested-products.md) for the full story.

### clickOutside: false on the overlay
Blocksy's built-in clickOutside listener (registered via `ct:overlay:handle-click`) treats arrow clicks INSIDE the panel as outside-clicks — probably because flexy translates child items outside the panel rect, breaking Blocksy's `closest()`-based detection. We pass `clickOutside: false` so Blocksy doesn't bind its own listener, and bind our own via `bindClickOutsideOnce()` that uses `closest('.ct-panel-inner')` for accurate detection.

### Panel rendered in footer
Same reason as Blocksy mini cart — the panel needs to be a fixed-position overlay on top of everything. Footer placement avoids z-index stacking context issues.

---

## How to Customise for a New Client

1. **Panel width** — automatically mirrors the cart panel width from Blocksy Customizer (no code change needed)
2. **Panel heading text** — change `Wishlist` string in `wishlist-offcanvas.php` (`blocksy_child_render_wishlist_panel()`)
3. **Sign-up button text/URL** — change in the guest notice section of `wishlist-offcanvas.php`
4. **Continue Shopping URL** — defaults to shop page, change in `wishlist-offcanvas.php`
5. **All colors** — automatically follow Blocksy global palette. No client-specific CSS needed.
6. **Suggested products** — automatically uses Blocksy's suggested products template. Configure in Customizer under cart panel settings.

---

## Related Patterns

- **[drawer-suggested-products.md](drawer-suggested-products.md)** — Master doc for the suggested-products carousel (7-state matrix, 8-layer bulletproofing, shared helper API, LAYER 8 CSS shim, flexy.js arrow binding via substring)
- **[offcanvas.md](offcanvas.md)** — Shared off-canvas panel styling (mini cart qty stepper lives here)
- **[wishlist-inline.md](wishlist-inline.md)** — Wishlist heart button beside Add to Cart (PDP #1)
- **[single-product-page.md](single-product-page.md)** — All single product page customization patterns
