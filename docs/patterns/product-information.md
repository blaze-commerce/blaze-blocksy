# Product Information — Off-Canvas Panel (PDP #2)

> **Shipping Calculator + Returns + FAQ** in a right-side off-canvas panel. Renders 3 clickable items below Add-to-Cart on every product page. No jQuery, no ACF, no Customizer — vanilla JS + standard WooCommerce shipping API.

---

## File Structure

```
blocksy-child/
├── inc/
│   ├── loader.php                    ← MODIFIED: loads product-information.php (WooCommerce conditional)
│   ├── enqueue.php                   ← MODIFIED: enqueues CSS + JS on is_product() pages
│   └── product-information.php       ← Tab config, PDP item row, panel HTML, dynamic CSS, AJAX endpoints
├── assets/
│   ├── css/components/
│   │   └── product-information.css   ← Item row, panel overrides, tabs, calculator form, results, loading/error states
│   └── js/
│       └── product-information.js    ← Panel open/close via ctEvents, tab switching, country→state AJAX, shipping calc AJAX
├── docs/patterns/
│   └── product-information.md        ← This file
```

---

## How It Works

1. **`product-information.php`** hooks into `woocommerce_single_product_summary` at priority 35 (after ATC) to render the Shipping | Returns | FAQ item row
2. Panel HTML is injected via `blocksy:footer:offcanvas-drawer` filter (same pattern as wishlist panel)
3. Dynamic CSS mirrors the cart panel width/backdrop/shadow from Blocksy Customizer (same as wishlist)
4. Shipping calculator creates a temporary WC package + calls `calculate_shipping_for_package()` — works with all installed shipping plugins (Australia Post, Starshipit, etc.)
5. Returns + FAQ tabs pull content from WP pages (IDs configurable in the tab array)

---

## Hook Points

| Hook | Priority | Purpose |
|------|----------|---------|
| `woocommerce_single_product_summary` | 35 | Item row (after ATC) |
| `blocksy:footer:offcanvas-drawer` | 10 | Panel HTML in footer |
| `wp_ajax_bc_get_states` | — | State dropdown AJAX |
| `wp_ajax_nopriv_bc_get_states` | — | State dropdown (logged out) |
| `wp_ajax_bc_calculate_shipping` | — | Shipping rate calc |
| `wp_ajax_nopriv_bc_calculate_shipping` | — | Shipping rate calc (logged out) |

---

## Tab Configuration

Edit `bc_product_info_tabs()` in `inc/product-information.php`:

```php
[
    'slug'    => 'shipping',       // URL-safe identifier
    'title'   => 'Shipping',       // Display label
    'icon'    => '<svg ...>',      // Inline SVG (Feather Icons)
    'type'    => 'calculator',     // 'calculator' or 'page'
],
[
    'slug'    => 'returns',
    'title'   => 'Returns',
    'icon'    => '<svg ...>',
    'type'    => 'page',
    'page_id' => 7315,             // WP page ID
],
```

**To add a tab:** Add an array entry. No other file changes needed.
**To remove a tab:** Delete or comment out the entry.
**Tab types:** `'calculator'` renders the shipping form. `'page'` pulls content from a WP page.

---

## Feature Toggle

In `functions.php`:

```php
define( 'BC_FEATURE_PRODUCT_INFORMATION', true );  // set false to disable
```

---

## Blocksy Panel Integration (Critical)

The panel uses Blocksy's `ct-panel` system. Key learnings:

1. **Tabs MUST be inside `.ct-panel-content`** — Blocksy's `handleContainerClick` only allows clicks inside `.ct-panel-content` or `.ct-panel-actions`. Anything between them triggers panel close.
2. **Use `sticky` positioning** for tabs so they stay pinned while content scrolls.
3. **`e.stopImmediatePropagation()`** on all clicks inside the panel inner to prevent Blocksy's overlay system from closing.
4. **`ctEvents.trigger("ct:overlay:handle-click")`** to open — pass `container: panel` (the panel DOM element), not an href string.

---

## Shipping Calculator

- Creates a temporary WC shipping package with `product_id`, `variation_id`, `data`, `quantity`, `line_total`
- Package `contents` MUST include `product_id` and `variation_id` keys — Australia Post plugin crashes without them
- Package `destination` MUST include `address_2` key — Starshipit plugin warns without it
- Calls `WC()->shipping()->calculate_shipping_for_package($package)` — standard WooCommerce API
- All shipping plugins (Australia Post, Starshipit, flat rate, free shipping) integrate automatically via WC shipping zones

---

## Icons

[Feather Icons](https://feathericons.com/) — MIT licensed, inline SVG:
- **Shipping:** truck icon
- **Returns:** refresh-ccw icon
- **FAQ:** help-circle icon

---

## Page IDs (Byron Bay Candles)

| Tab | Page ID | Slug |
|-----|---------|------|
| Returns | 7315 | `returns` |
| FAQ | 7244 | `faqs` |

Update these in `bc_product_info_tabs()` per site.

---

## Dependencies

- WooCommerce (required)
- Blocksy theme + Companion Pro (for `ct-panel` CSS and `ctEvents` JS)
- No jQuery, no ACF, no additional plugins

---

## vs Zephyr Implementation

| | Zephyr | Ours |
|--|--------|------|
| JS | jQuery + $.ajax + $.blockUI (~90KB) | Vanilla JS + fetch() (~4KB) |
| Content | ACF Options fields (requires ACF Pro) | WP Pages (Gutenberg-editable) |
| Panel | Custom HTML + inline onclick | Blocksy ct-panel + ctEvents (native) |
| Shipping | Same WC API, jQuery-wrapped | Same WC API, vanilla fetch() |
| Config | Hardcoded + ACF repeater | PHP array (1 line = 1 tab) |
| Customizer | ~400 lines design options | No Customizer (mirrors cart panel auto) |
| Toggle | None (always on) | `BC_FEATURE_PRODUCT_INFORMATION` constant |
| Portability | Tied to Zephyr theme | Reusable across Blocksy child themes |
