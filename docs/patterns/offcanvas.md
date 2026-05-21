# Off-Canvas Panel — Shared Component

> **Single source of truth** for all off-canvas panel styling. When you need to change panel padding, heading fonts, borders, or responsive breakpoints — edit `offcanvas.css` ONLY. Individual panel CSS files only contain what's UNIQUE to that panel.

---

## Architecture

```
assets/
├── css/components/
│   ├── offcanvas.css           ← SHARED: padding, borders, fonts, icons, responsive,
│   │                              mini-cart unified-scroll layout, qty stepper, totals
│   ├── wishlist-offcanvas.css  ← UNIQUE: item rows, remove button, empty state,
│   │                              guest prompt, suggested products, continue shopping
│   └── product-information.css ← UNIQUE: tabs, shipping calculator, results cards
└── js/
    ├── cart-offcanvas.js       ← Mini cart drawer JS — heading-count sync (CU-86exbe71m)
    │                              + unified-scroll wrap (CU-86exbd8kg V3)
    └── wishlist-offcanvas.js   ← Wishlist drawer JS — trigger, refresh, remove
```

No dedicated mini cart CSS file — the cart panel only needs the shared styles + its icon (both in `offcanvas.css`).

---

## What's Shared (edit in offcanvas.css)

| Property | Selector | Value |
|----------|----------|-------|
| Panel layout | `.ct-panel-inner` | `flex column, height 100%` |
| Header padding | `.ct-panel-actions` | `20px 24px` (mobile: `16px`) |
| Header border | `.ct-panel-actions` | `1px solid var(--theme-border-color)` |
| Heading font | `.ct-panel-heading` | h2 CSS variables with fallbacks |
| Content padding | `.ct-panel-content-inner` | `0 24px` (mobile: `0 16px`) |
| Icon size | `::before` / `.ct-wishlist-panel-icon` | `20px × 20px` |

## What's Unique (edit in individual files)

| Panel | File | Unique Styles |
|-------|------|---------------|
| Cart | `offcanvas.css` (icon only) | Cart icon via CSS mask |
| Wishlist | `wishlist-offcanvas.css` | Item rows, remove button, states, suggested products |
| Product Info | `product-information.css` | Tabs (sticky), calculator form, results, tab panes |

---

## Adding a New Off-Canvas Panel

1. Use Blocksy's `ct-panel` HTML structure (see product-information.php for reference)
2. The shared `offcanvas.css` automatically styles the header, heading, and content padding
3. Only create a new CSS file if the panel has unique content styling
4. Add the panel ID to the selectors in `offcanvas.css` if it needs the shared heading/border treatment

---

## Rule: Extract at 2

When 2+ elements share the same styling pattern, extract into a shared component immediately. Don't wait for 3. This file was created when the mini cart panel needed the same styling as the wishlist panel — rather than duplicating, we extracted.

---

## Icons

Each panel has its own icon in the heading:
- **Cart:** Feather Icons `shopping-cart` — rendered via CSS `mask-image` (no PHP needed)
- **Wishlist:** Custom heart SVG — rendered via PHP in `wishlist-offcanvas.php` (inline in heading)
- **Product Info:** No heading icon (close button only)

---

## Mini Cart Line-Item Qty Stepper (`offcanvas.css`)

The mini cart line item shows a horizontal `[-] [1] [+] × $price` stepper next to each product. This is styled in `offcanvas.css` (NOT in a dedicated mini-cart CSS file) because it's part of the off-canvas panel's UX.

### Why we shimmed it
Blocksy renders the cart-page qty stepper horizontally via rules scoped to `.ct-cart-actions > .quantity[data-type="type-2"]` (defined in `clients/byronbay/byronbay.css`). The mini cart drawer wraps the same qty input in `.ct-product-actions` instead — those rules don't match, and Blocksy's default `.quantity[data-type="type-2"]` styling positions `.ct-increase`/`.ct-decrease` as overlays floating inside the input. Result: vertical/floating stepper instead of clean inline `[-] [1] [+]`.

### Fix
`assets/css/components/offcanvas.css` ships a mini-cart-scoped block that mirrors the cart-page horizontal stepper rules under `.ct-product-actions > .quantity[data-type="type-2"]`. Selectors:

```css
#woo-cart-panel .ct-product-actions > .quantity,
.woocommerce-mini-cart .ct-product-actions > .quantity { ... }
```

Plus typographic `−` / `+` glyphs on the buttons (`.ct-decrease::before`, `.ct-increase::before`) and `display: none` on `.bc-qty-label` inside the drawer.

### "Qty:" label leak
`inc/woocommerce.php` registers `woocommerce_before_quantity_input_field` (gated by `is_product()`) which emits `<span class="bc-qty-label">Qty:</span>`. The hook fires on EVERY qty input on the page — including the mini cart line item — so the label leaks into the drawer when opened from a product page. The shim hides it inside the drawer (`#woo-cart-panel .bc-qty-label { display: none }`).

### Sync warning
Horizontal stepper rules now live in TWO places: `clients/byronbay/byronbay.css` (cart page, `.ct-cart-actions` scope) and `assets/css/components/offcanvas.css` (mini cart, `.ct-product-actions` scope). When Blocksy updates its qty stepper or the customizer setting changes, **both blocks must stay in sync**.

---

## Mini Cart Totals Section (`inc/woocommerce.php` + `offcanvas.css`)

Native Blocksy + WooCommerce mini cart shows only a Subtotal row before the Checkout button. The Austin Natural Mattress reference layout (and our Figma 684:85959 parent spec) calls for a totals breakdown above the Checkout button:

```
Subtotal:                    $X.XX     ← native (Blocksy renders)
[free shipping progress bar]            ← native (Blocksy renders)
─────────────────────────
Shipping              Calculated at checkout   ← bc-mini-cart-totals (NEW)
─────────────────────────
Order Total                  $X.XX     ← bc-mini-cart-totals (NEW, bold)
[Checkout button]
```

### Implementation

`inc/woocommerce.php` registers `bc_render_mini_cart_totals_section()` on `woocommerce_widget_shopping_cart_before_buttons` (priority 30 — fires AFTER Blocksy's shipping-progress-bar at priority 10–20 and BEFORE the View Cart / Checkout buttons). The function:
- Bails if cart is empty
- Renders a Shipping row when `$cart->needs_shipping()` is true. Value is decided by `bc_mini_cart_shipping_value()` (see "Shipping label" below).
- Always renders an Order Total row using `wc_price( $cart->get_total('edit') )`

Why no extra JS for live updates: the entire `div.widget_shopping_cart_content` is a registered cart fragment (see WC's `wc_get_refreshed_fragments_filter`). When qty changes, WC re-renders that whole subtree server-side and our function fires automatically.

### Shipping label — `bc_mini_cart_shipping_value()`

Returns `"Free"` or `"Calculated at checkout"`. Mirrors Blocksy Pro's shipping-progress-bar threshold logic exactly so the row always agrees with the green "Congratulations! You got free shipping 🎉" banner above it. We intentionally do NOT walk `WC()->shipping()->calculate_shipping()` rates — that path produces two failure modes:

1. **False positives on $0 non-free rates.** Local Pickup, Australia Post fallbacks, etc. are zero-cost but aren't "Free shipping" — matching them as Free shows "Free" even on an empty-of-free-shipping-zone cart.
2. **Disagreement with Blocksy banner.** Blocksy reads `theme_mod woo_count_progress_amount` (custom mode), NOT WC's zone `min_amount`. On BBC, custom limit = $100, Australia zone min = $75. At $88 cart, walking WC rates returns "Free" while Blocksy banner says "Add $12 more". Two contradictory signals in one drawer.

Instead, `bc_mini_cart_shipping_value()` reads the SAME theme mods Blocksy reads (`woo_count_method`, `woo_custom_count_criteria`, `woo_count_progress_amount`, `woo_count_progress_items`, `woo_count_with_discount`) and returns "Free" iff `total >= limit` OR a free-shipping coupon is applied. Banner and row stay in lockstep automatically — including when QA toggles the count method between "custom" and "woo" in the Customizer.

If a future site needs the row to follow WC zones rather than Blocksy's banner, switch the customizer setting (Cart → Shipping Progress → Count Method) to "woo" and the function picks it up — no code change.

### Toggle
`BC_FEATURE_MINI_CART_TOTALS` (default `true`). Set `false` in client PHP to disable for sites that prefer the lightweight Subtotal-only drawer (Baymard-aligned for tax-inclusive markets).

### Styling — `assets/css/components/offcanvas.css`
- `.bc-mini-cart-totals` — 12px padding-top + dashed border-top divider above the block
- `.bc-mini-cart-totals__row` — flex space-between, label left + value right
- `.bc-mini-cart-totals__shipping` — 14px, secondary text color
- `.bc-mini-cart-totals__total` — 16px semibold, divider above for prominence

### Tax-inclusive markets (AU GST)
For sites with tax-inclusive pricing, `Subtotal` already includes GST and `Order Total = Subtotal` when shipping is at checkout. The breakdown still adds value (clear "Calculated at checkout" expectation, prominent Order Total = primary CTA anchor). For tax-exclusive markets (US, like Austin Natural Mattress) the totals will diverge naturally as tax + shipping accumulate.

### Coupons (deferred — not currently implemented)
`bc_render_mini_cart_totals_section()` does NOT currently render a Discount row when coupons are applied. This is a deliberate omission because Baymard's research flags coupon-code INPUT fields in mini cart drawers as a conversion killer (~50% leave-and-search → don't return). We should still render an applied-coupon DISPLAY row (no input) when `WC()->cart->get_applied_coupons()` is non-empty — left as a follow-up task.

---

## Mini Cart Unified Scroll (`assets/js/cart-offcanvas.js` + `offcanvas.css`)

The mini cart drawer body has FIVE sibling regions inside `.ct-panel-content-inner`:

1. `<ul.woocommerce-mini-cart>` — cart line items
2. `.ct-suggested-products--mini-cart` — Blocksy native carousel (state #1 in `drawer-suggested-products.md`)
3. `.woocommerce-mini-cart__total` — subtotal line
4. `.ct-shipping-progress-mini-cart` + `.bc-mini-cart-totals` + `.woocommerce-mini-cart__buttons` + `.bc-mini-cart-secure` — totals breakdown + checkout button + secure badge

Blocksy's stock behaviour applies `overflow-y: auto` + a tight `max-height` to the `<ul>` ALONE. On short viewports this produces a cramped inner scroll-box: only one cart row is visible while the suggested-products carousel sits below at full height. Items appear cut off (Cam, screenshot 2026-04-30).

### Architecture

The drawer body is split into a **scroll region** and a **pinned footer**:

```
.ct-panel-content-inner               (display:flex; flex-direction:column; height:100%)
├── .bc-cart-scroll                   (flex:1 1 auto; min-height:0; overflow-y:auto;
│   │                                  display:flex; flex-direction:column)
│   ├── ul.woocommerce-mini-cart      (overflow:visible; max-height:none — Blocksy reset)
│   └── .ct-suggested-products--mini-cart  (margin-top:auto)
├── .woocommerce-mini-cart__total           ┐
├── .ct-shipping-progress-mini-cart         │
├── .bc-mini-cart-totals                    ├── footer (flex-shrink:0 — pinned)
├── .woocommerce-mini-cart__buttons         │
└── .bc-mini-cart-secure                    ┘
```

The `.bc-cart-scroll` wrapper is created at **runtime** by `cart-offcanvas.js` because `<ul>` and `.ct-suggested-products--mini-cart` come out of WooCommerce/Blocksy as flat siblings — there is no PHP filter to re-parent them server-side, and CSS alone can't make TWO siblings share a scroll container while keeping OTHER siblings outside.

### JS — `wrapScrollItems()` IIFE

```js
function wrapScrollItems() {
    var panel = document.getElementById("woo-cart-panel");
    if (!panel) return;
    if (panel.querySelector(".bc-cart-scroll")) return;        // idempotent
    var ul = panel.querySelector("ul.woocommerce-mini-cart");
    if (!ul) return;                                            // empty-cart state — bail
    var suggested = panel.querySelector(".ct-suggested-products--mini-cart");
    var wrapper = document.createElement("div");
    wrapper.className = "bc-cart-scroll";
    ul.parentNode.insertBefore(wrapper, ul);
    wrapper.appendChild(ul);
    if (suggested && suggested.parentNode === panel.querySelector(".ct-panel-content-inner")) {
        wrapper.appendChild(suggested);
    }
}
```

Bound to: `wc_fragments_refreshed`, `wc_fragments_loaded`, `added_to_cart`, `removed_from_cart`, plus initial `DOMContentLoaded`. WooCommerce replaces the entire `<ul>` on every cart change so the wrapper must re-apply on every fragment refresh — the idempotency check (`querySelector(".bc-cart-scroll")`) prevents double-wrapping.

### Why `margin-top: auto` instead of `justify-content: space-between`

`space-between` on a flex container with `overflow-y: auto` can render the first child above the visible scroll start when content overflows (browsers pre-distribute then overflow). `margin-top: auto` contributes 0 px when there is no slack and behaves identically to natural stacking on overflow — safer for the variable-height case.

### Behaviour matrix

| Cart state | `<ul>` position | Suggested position | Footer | Wrapper scrolls? |
|---|---|---|---|---|
| Empty (no `<ul>`) | n/a — empty-state UI takes over | n/a | n/a | n/a |
| 1–2 items (slack) | top of scroll area | bottom of scroll area (auto-margin gap fills between) | pinned | no |
| 3–4 items (no slack) | top, natural | directly below `<ul>` | pinned | no |
| 5+ items (overflow) | top, natural | directly below `<ul>` | pinned | yes |

### Why footer pinning uses `flex-shrink: 0`, not `position: sticky`

`.ct-panel-inner` is already `display: flex; flex-direction: column; height: 100%`. Adding `flex-shrink: 0` on each footer block is the natural extension of that layout — `position: sticky` would require additional positioning context and break the existing Blocksy assumptions.

### Maintenance

- If a future Blocksy update renames `.ct-suggested-products--mini-cart`, the wrap function silently skips suggested but still wraps the `<ul>` — drawer continues to work, suggested simply re-appears as a flat sibling outside the scroll area. Audit on next Blocksy major.
- The wrapper class `.bc-cart-scroll` is selector-matched in CSS — keep the class name in sync between JS (`WRAPPER_CLASS` constant) and CSS.

---

## Related Patterns

- **[drawer-suggested-products.md](drawer-suggested-products.md)** — Master doc for the suggested-products carousel (7-state matrix, 8-layer bulletproofing). State #1 (mini cart with items) renders inside `.bc-cart-scroll` per the unified-scroll architecture above.
- **[wishlist-offcanvas.md](wishlist-offcanvas.md)** — Wishlist drawer specifics (data sync, panel open/close)
