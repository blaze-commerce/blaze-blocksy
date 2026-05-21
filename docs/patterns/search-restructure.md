# Search Restructure — FiboSearch Two-Column Dropdown

> **Requires:** FiboSearch Premium (ajax-search-for-woocommerce-premium).
> **Feature flag:** `search-restructure` in client manifest.json features array.
> If no features key exists, loads by default (backward compatible).

---

## File Structure

```
blocksy-child/
├── inc/
│   ├── loader.php                        ← MODIFIED: loads search-restructure module
│   └── search-restructure.php            ← PHP module: filters, AJAX endpoint, enqueue
├── assets/
│   ├── css/components/
│   │   └── search-dropdown.css           ← Dropdown grid layout, responsive, product cards
│   └── js/
│       └── search-restructure.js         ← Vanilla JS: MutationObserver, DOM restructure,
│                                            gallery hover, image upgrade, hover fix
├── partials/
│   └── fibosearch-header.php             ← Blocksy search element template override
└── docs/patterns/
    └── search-restructure.md             ← This file
```

---

## How It Works

### 1. PHP Module (`inc/search-restructure.php`)

Loaded by `loader.php` when both conditions are met:
- Feature `search-restructure` is enabled (or no features key in manifest)
- FiboSearch is active (`DGWT_WCAS_VERSION` constant exists)

The module:
- **Overrides FiboSearch's Blocksy template** → renders full search bar via `partials/fibosearch-header.php`
- **Enables FiboSearch broken UI hard fix** → `dgwt/wcas/scripts/fixer` filter
- **Upgrades thumbnail size** → `dgwt/wcas/setup/thumbnail_size` returns `woocommerce_thumbnail` (300x300 instead of 64px)
- **Registers gallery AJAX endpoint** → `wp_ajax_bc_search_gallery` returns second gallery image URLs for hover swap
- **Enqueues assets** → CSS + JS globally with `bcSearchConfig` localized data (ajaxUrl, nonce, maxProducts)

### 2. JavaScript (`assets/js/search-restructure.js`)

Vanilla JS (no jQuery dependency). Uses IIFE with strict mode.

**Flow:**
1. `MutationObserver` watches `.dgwt-wcas-suggestions-wrapp` for child changes
2. When FiboSearch populates suggestions, observer detects headlines/products
3. `process()` classifies each item by type (category, product, post, page)
4. Creates section wrappers: `.dgwt-wcas-section-categories`, `.dgwt-wcas-section-products`, etc.
5. Reorders: categories → products → blog → pages
6. Adds search header ("Showing results for...") + close button
7. `upgradeImages()` swaps FiboSearch 64px thumbnails to 300x300 via URL regex
8. `disableFiboHover()` strips FiboSearch's `.dgwt-wcas-suggestion-selected` class (wrong-item highlight fix)
9. `loadGalleryImages()` fetches second image via AJAX, attaches mouseenter/mouseleave hover swap

**Key design decisions:**
- **Clone, not move** — product elements are cloned into new containers (`cloneNode(true)`)
- **Re-query DOM in fetch callback** — by the time AJAX returns, FiboSearch may have re-rendered; always query fresh elements
- **MutationObserver on class attribute** — strips FiboSearch's selected class whenever re-applied
- **Progressive enhancement** — gallery hover silently fails if AJAX errors; image upgrade has fallback

### 3. CSS (`assets/css/components/search-dropdown.css`)

CSS Grid layout:
- **Desktop (>999px):** `grid-template-columns: 220px 1fr` — left sidebar + product area
- **Product grid:** `repeat(4, 1fr)` with 16px gap, 8 products max (4×2)
- **Product images:** 180px fixed height, `object-fit: contain`, 5px border-radius, white bg
- **Hover:** `transform: scale(1.05)` on image, 0.3s transition
- **Tablet (<999px):** single column, 3-col product grid
- **Mobile (<689px):** 2-col product grid
- **Scrolling:** `overflow-y: auto` on whole dropdown, `max-height: calc(100vh - 150px) !important`
- **Mobile scroll lock (section 14):** `html.dgwt-wcas-overlay-mobile-on` triggers `overflow: hidden` on `html`/`body` — prevents background page scroll. `overscroll-behavior: contain` + `touch-action: none` prevent iOS rubber-band. Dropdown wrapper re-enables `overflow-y: auto` and `-webkit-overflow-scrolling: touch` for internal scroll.

---

## FiboSearch Settings Required

| Setting | Value | Why |
|---------|-------|-----|
| `suggestions_limit` | 25 | Enough for categories + posts + pages + 8 products |
| `show_headings` | 1 | JS relies on headline elements to classify sections |
| `show_images` | 1 | Product grid needs thumbnails |
| `show_price` | 1 | Price displayed below product title |
| `show_matching_posts` | on | Blog section in left column |
| `show_matching_pages` | on | Pages section in left column |

**After deploying the thumbnail size filter**, rebuild the FiboSearch index:
WP Admin → FiboSearch → Indexer → Rebuild.

---

## Configuration

Configuration is passed to JS via `wp_localize_script`:

```php
wp_localize_script( 'blocksy-child-search-restructure', 'bcSearchConfig', [
    'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
    'nonce'       => wp_create_nonce( 'bc_search_gallery' ),
    'maxProducts' => 8,
] );
```

---

## Disabling This Feature

**Option 1: Feature flag (per client)**
Add `features` array to `clients/{slug}/manifest.json` and omit `search-restructure`:
```json
{
  "features": ["wishlist-offcanvas", "product-tabs"]
}
```

**Option 2: FiboSearch deactivated**
The module checks `defined('DGWT_WCAS_VERSION')` and bails if FiboSearch isn't active. No errors, no assets loaded.

---

## Troubleshooting

| Issue | Cause | Fix |
|-------|-------|-----|
| Blurry product images | FiboSearch index has 64px thumbs | Rebuild FiboSearch index in WP Admin |
| Hover highlights wrong item | FiboSearch's `activate()` uses DOM position | `disableFiboHover()` strips selected class; CSS `:hover` handles it |
| Gallery hover not working | AJAX nonce expired (page cached) | Nonce regenerates on each page load; check caching plugin excludes admin-ajax.php |
| Dropdown doesn't restructure | JS not loaded or FiboSearch not active | Check `bcSearchConfig` in browser console; verify FiboSearch is active |
| Products show in single column | CSS grid not applied | Check `search-dropdown.css` is enqueued; inspect for `display: grid` on container |
| Background page scrolls through overlay on mobile | `overflow: hidden` not set on `html`/`body` when overlay active | Section 14 in `search-dropdown.css` — `html.dgwt-wcas-overlay-mobile-on, html.dgwt-wcas-overlay-mobile-on body { overflow: hidden !important }` (CU-86exe005n) |
