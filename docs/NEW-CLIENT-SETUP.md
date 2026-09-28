# New Client Setup Guide

How to set up the Blocksy child theme for a new client project.

## Prerequisites

- WordPress 6.0+
- Blocksy parent theme installed and active
- Blocksy Companion Pro (licensed)
- WooCommerce (if e-commerce features needed)

## Step 1: Create Client Module

```bash
cp -r clients/_template clients/{client-slug}
```

Rename files inside:
- `client-slug.css` → `{client-slug}.css`
- `client-slug.php` → `{client-slug}.php`

## Step 2: Configure manifest.json

Edit `clients/{client-slug}/manifest.json`:

```json
{
  "name": "Client Name",
  "slug": "client-slug",
  "version": "1.0.0",
  "active": true,
  "css": "client-slug.css",
  "js": null,
  "php": "client-slug.php",
  "features": [
    "wishlist-offcanvas",
    "product-slider"
  ]
}
```

### Feature Flags

Only list the features this client needs. Available features:

| Feature | Module | Description |
|---------|--------|-------------|
| `wishlist-offcanvas` | `inc/wishlist-offcanvas.php` | Off-canvas wishlist panel |
| `product-tabs` | `inc/product-tabs.php` | ACF accordion fields as WC tabs |
| `product-information` | `inc/product-information.php` | Shipping calculator + returns + FAQ panel |
| `recently-viewed` | `inc/recently-viewed.php` | Recently viewed products on PDP |
| `mini-cart-empty` | `inc/mini-cart-empty.php` | Empty mini cart with suggestions |
| `product-slider` | `inc/product-slider.php` | `[bc_product_slider]` shortcode |

Omit the `"features"` key entirely to enable ALL features (backward compatible).

## Step 3: Deactivate Other Clients

Only ONE client should have `"active": true` at a time. Set all other clients to `"active": false`.

## Step 4: Set Up Blocksy Customizer

Use `claude-commands/setup-foundation.md` to apply Figma design tokens to Blocksy Customizer:
- Colors (palette)
- Typography (headings, body)
- Header Builder layout
- Footer layout
- Button styles
- Product card layout + hover behavior

## Step 5: Add Client-Specific Hooks

Edit `clients/{client-slug}/{client-slug}.php` for hooks that are unique to this client:
- Related products heading text
- Review collection shortcode + ID
- Payment icons
- Custom gettext translations

## Step 6: Add Client-Specific CSS

Edit `clients/{client-slug}/{client-slug}.css` for design overrides that:
- Can't be done via Blocksy Customizer
- Are specific to this client's design (not reusable)
- Have WHY comments and @date tags

## Step 7: Add Enqueue Conditions (if needed)

If the client uses shortcodes on pages other than the homepage, add the page condition to `inc/enqueue.php`. For example, to load `woo-archive.css` on a custom page:

```php
if ( is_shop() || is_product_category() || is_product_tag() || is_front_page() || is_page('custom-page') ) {
    blocksy_child_enqueue_component( 'woo-archive', $css_url, $css_path );
}
```

## Step 8: PageSpeed

The `inc/perf/` modules are **opt-in only**, through a separate `"perf"` key. Omitting it enables nothing. Full reference: [`docs/patterns/perf.md`](patterns/perf.md).

**Copying `clients/_template/` opts the site into the perf family**, because the template ships the recommended `"perf"` list. For an EXISTING site that only needs `features` gating, set `"perf": []`. Existing sites without a manifest, or with a manifest lacking a `perf` key, are unaffected by this release — nothing loads, no hooks register.

1. **Preflight before touching anything:** `bash scripts/preflight.sh <home> <cat> <pdp>`. The script is in `blaze-commerce-mcp` and served as an MCP skill script. Fix its stop conditions first.
2. **Enable the recommended set** in `clients/{client-slug}/manifest.json`. That is every perf feature except `hero-facade`, which needs poster assets and `.bc-hero-video` wrapper markup, so add it per site:

   ```json
   "perf": [
     "perfmatters-config", "perfmatters-filters", "rucss-safelist",
     "critical-css-supplements", "lcp-image", "fonts-critical-path",
     "async-styles", "dequeue-assets", "media-hygiene",
     "content-visibility", "minicart-hydrate", "lazy-rescan"
   ]
   ```

   `dequeue-assets` ships a conservative list, but still check each listed handle against the site's DOM with the relevant UI open.
3. **Per-site overrides** go in `bc-site-customizations`, under `sites/<slug>/custom/`:
   - `custom/perfmatters.json` holds Perfmatters overrides. Sections merge one level down, and **list keys replace the default list wholesale**, so repeat any default entries you want to keep.
   - `custom/custom.php` holds `blocksy_child_perf_*` filters, such as the leading-images count, the header min-height, or extra Delay JS exclusions.
   - `custom/images/` holds hero posters (`yt-poster-<id>-800.avif`) and re-encoded background images.
4. **Deploy both lanes before measuring.**
   - **Lane A** is the theme PR: `inc/perf/` modules, `inc/perf/data/perfmatters-defaults.json`, and the manifest.
   - **Lane B** is the site-customizations PR: `custom/perfmatters.json`, the `custom/custom.php` filters, and `custom/images/`.
   - Some things don't travel with either PR, so do them on each environment: media-library files (run `wp media regenerate --only-missing` for `bc_hero_poster`), ShortPixel and CDN settings, and the per-host Perfmatters used-CSS and local-fonts caches. Regenerate both caches. The fonts module recovers on its own after `clear-local-fonts`.
5. **Measure.** Run `bash scripts/clear-cache.sh <urls>`, then `bash scripts/pagespeed.sh <url> 5` per template.
   - That is 5 warmed runs, each following warm → purge → warm.
   - PASS = median ≥ 90 AND (floor ≥ 85 OR ≥ 80% of unique runs ≥ 90). Fewer than 3 unique runs, or `benchmarkIndex` < 500, means low confidence.
   - Never measure right after a deploy.
   - Report the floor and the distribution.

## CSS Architecture

Load order (lowest → highest cascade priority):

1. Blocksy parent (`ct-main-styles`) — Customizer output
2. `style.css` — theme header only
3. `base.css` — global tweaks (always loaded)
4. `components/*.css` — conditional per page type
5. `utilities.css` — utility classes
6. `clients/{slug}/{slug}.css` — client-specific (loaded LAST)

### Decision Checklist

Before writing any CSS rule: **Would this rule make sense on a different client's site?**

- **YES** → `assets/css/components/{component}.css`
- **NO** → `clients/{slug}/{slug}.css`

## Responsive Breakpoints

Use Blocksy's canonical breakpoints consistently:

| Name | Breakpoint | Usage |
|------|-----------|-------|
| Desktop | >999.98px | Default (no media query) |
| Tablet | ≤999.98px | `@media (max-width: 999.98px)` |
| Mobile | ≤689.98px | `@media (max-width: 689.98px)` |

## Directory Structure

```
blocksy-child/
├── style.css                    # Theme header only
├── functions.php                # Lean loader
├── inc/                         # Shared PHP modules
│   ├── loader.php               # Module bootstrapper + feature flags
│   ├── helpers.php              # Utility functions
│   ├── enqueue.php              # Asset enqueuing
│   ├── hooks.php                # WordPress/Blocksy hooks
│   ├── woocommerce.php          # Reusable WooCommerce hooks
│   ├── wishlist-offcanvas.php   # [feature] Wishlist panel
│   ├── product-tabs.php         # [feature] ACF tabs
│   ├── product-information.php  # [feature] Shipping/returns/FAQ
│   ├── recently-viewed.php      # [feature] Recently viewed
│   ├── mini-cart-empty.php      # [feature] Empty cart state
│   ├── product-slider.php       # [feature] Product carousel
│   └── perf/                    # [perf] Opt-in PageSpeed modules
├── assets/
│   ├── css/
│   │   ├── base.css             # Global (always loaded)
│   │   ├── utilities.css        # Utilities (always loaded)
│   │   └── components/          # Conditional per page type
│   └── js/                      # Vanilla JS (no jQuery)
├── clients/
│   ├── _template/               # Copy this for new clients
│   │   ├── manifest.json
│   │   ├── client-slug.css
│   │   └── client-slug.php
│   └── {client-slug}/           # Active client
│       ├── manifest.json
│       ├── {slug}.css
│       └── {slug}.php
├── docs/
│   ├── NEW-CLIENT-SETUP.md      # This file
│   ├── gutenberg-block-checklist.md
│   └── patterns/                # Pattern docs for each feature
└── woocommerce/                 # WC template overrides
```
