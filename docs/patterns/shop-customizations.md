# Pattern — Shop Customizations

> **Module:** `inc/shop-customizations.php` + `assets/js/shop-customizations.js`
> **Feature key:** `shop-customizations` (toggle in `clients/{slug}/manifest.json → features`)
> **Loaded on:** `is_shop() || is_product_category() || is_product_tag()`
> **Refactored from:** `custom/shop-customizations.{php,js}` (2026-05-08, audit P1).

## What it does

Three small Woo-archive tweaks that the Blocksy Customizer can't do:

1. **Repositions the result count.** Default Blocksy puts it inside `.woo-listing-top` at priority 20. We move it to priority 32 (after the wrapper closes at 31), and ALSO render it again at priority 11 of `woocommerce_after_shop_loop` (i.e. directly under the products + load-more button).
2. **Updates the result count after AJAX load-more.** Blocksy's infinite scroll appends products via AJAX but the static "Showing X–Y of Z results" text doesn't refresh. The JS listens for the `ct:infinite-scroll:load` event, recounts visible `.product` items in the `.products` container, and rewrites the text.
3. **Renames the default sort option.** Changes `menu_order` label from "Default sorting" to **"Sort By None"** via the `woocommerce_catalog_orderby` filter.

## Why a feature flag

Other clients forking the theme may not want any of this. The `shop-customizations` key in `manifest.json → features` controls both the PHP module load (`inc/loader.php`) and the JS enqueue (`inc/enqueue.php`). Default-enabled on Byron Bay; disable by removing the key from the client's manifest.

## Files

```
inc/shop-customizations.php          PHP hooks (result-count + sort label)
assets/js/shop-customizations.js     IIFE for AJAX load-more count update
```

## Hooks

| Hook | Priority | Effect |
| --- | --- | --- |
| `woocommerce_before_shop_loop` | 20 (default) → 32 (us) | Result count moved to AFTER `.woo-listing-top` wrapper |
| `woocommerce_after_shop_loop` | 11 (us) | Second result count below products |
| `woocommerce_catalog_orderby` | 10 | "Default sorting" → "Sort By None" |
| `ct:infinite-scroll:load` (DOM) | n/a | Recount visible products + rewrite count text |

## Browser/JS

- Listens once for the Blocksy infinite-scroll event.
- Regex: `/Showing\s+(\d+)\s*[–\-]\s*\d+\s+of\s+(\d+)\s+result/` — matches both en-dash and hyphen variants.
- No jQuery dependency.

## Failure modes

- **`.products` container missing** → JS exits early, no error.
- **Regex doesn't match** (string format changed by another locale/plugin) → text left alone.
- **WC inactive** → PHP module unloaded by `inc/loader.php` (WC gate); JS still enqueues but the page won't have shop markup so nothing happens.

## Related

- `inc/loader.php` — `$optional_modules['shop-customizations']` entry.
- `inc/enqueue.php` — feature-gated JS enqueue inside the shop-archive branch.
