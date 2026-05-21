# Product Tabs — ACF Accordions as WooCommerce Tabs

> Hooks ACF accordion fields into WooCommerce's `woocommerce_product_tabs` filter so per-product content renders as native WC tabs on the front end. No migration needed — reads existing ACF data.

---

## File Structure

```
blocksy-child/
├── inc/
│   ├── loader.php           ← MODIFIED: loads product-tabs.php
│   └── product-tabs.php     ← NEW: tab config, filter hook, render callback
└── docs/patterns/
    └── product-tabs.md      ← This file
```

---

## How It Works

1. `bc_acf_tab_slots()` defines the ACF field pairs (title_one/text_one, etc.)
2. `bc_acf_product_tabs()` hooks into `woocommerce_product_tabs` at priority 25
3. For each slot, reads ACF `title_{suffix}` and `text_{suffix}` from the current product
4. Skips if either title or content is empty
5. Generates a URL-safe slug from the title for the tab ID
6. `bc_acf_tab_render()` outputs content sanitized with `wp_kses_post()`

---

## ACF Field Groups

| Group | Title Field | Content Field | Content Type |
|-------|-------------|---------------|--------------|
| Accordion One (7619) | `title_one` | `text_one` | WYSIWYG |
| Accordion Two (7622) | `title_two` | `text_two` | WYSIWYG |
| Accordion Three (7625) | `title_three` | `text_three` | WYSIWYG |

---

## Adding a New Tab

1. Create an ACF field group with `title_{suffix}` (text) and `text_{suffix}` (WYSIWYG)
2. Add one line to `bc_acf_tab_slots()`:

```php
[ 'suffix' => 'four', 'priority' => 28 ],
```

---

## Error Handling

- ACF not active → filter returns tabs unchanged (no crash)
- Product not set → filter returns tabs unchanged
- ACF field read error → caught by try/catch, logged to error_log, tab skipped
- Empty title or content → tab skipped (no empty tabs rendered)
- All output sanitized with `wp_kses_post()`

---

## Dependencies

- ACF Pro (for `get_field()`)
- WooCommerce (for `woocommerce_product_tabs` filter)

---

## Data Coverage

- 125 out of 126 products have tab data populated
- Content is stored in `wp_postmeta` — the most portable WordPress data format
