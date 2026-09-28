# Blocksy Child

Reusable Blocksy child theme for Blaze Commerce projects. Powered by Claude, crafted by @jarutosurano.

## Requirements

- WordPress 6.0+
- [Blocksy](https://creativethemes.com/blocksy/) parent theme
- [Blocksy Companion Pro](https://creativethemes.com/blocksy/) (for premium extensions)

## Quick Start (New Client)

See [`docs/NEW-CLIENT-SETUP.md`](docs/NEW-CLIENT-SETUP.md) for the full guide.

```bash
# 1. Copy the template
cp -r clients/_template clients/my-client

# 2. Rename files
mv clients/my-client/client-slug.css clients/my-client/my-client.css
mv clients/my-client/client-slug.php clients/my-client/my-client.php

# 3. Edit manifest.json — set slug, name, active: true, features
# 4. Deactivate other clients (set active: false)
# 5. Set up Blocksy Customizer
```

## Architecture

```
blocksy-child/
├── style.css                              # Theme header only — no CSS rules
├── functions.php                          # Lean loader → inc/loader.php
├── README.md                              # This file
│
├── inc/                                   # Shared PHP modules (reusable across ALL clients)
│   ├── loader.php                         # Module bootstrapper + feature flag system
│   ├── helpers.php                        # Utility functions (is_plugin_active wrapper)
│   ├── enqueue.php                        # Conditional CSS/JS per page type
│   ├── hooks.php                          # WordPress/Blocksy hooks (FiboSearch fix)
│   ├── woocommerce.php                    # Reusable WC hooks (PayPal fix, wishlist, stock, qty)
│   ├── wishlist-offcanvas.php             # [feature] Off-canvas wishlist panel
│   ├── product-tabs.php                   # [feature] ACF accordion tabs
│   ├── product-information.php            # [feature] Shipping/returns/FAQ panel
│   ├── recently-viewed.php                # [feature] Recently viewed products
│   ├── mini-cart-empty.php                # [feature] Empty cart suggestions
│   ├── product-slider.php                 # [feature] [bc_product_slider] shortcode
│   └── perf/                              # [perf] Opt-in PageSpeed modules (see docs/patterns/perf.md)
│       ├── loader.php                     # Loads each enabled inc/perf/<feature>.php
│       ├── helpers.php                    # Opt-in resolution, style/script print helpers
│       ├── <feature>.php                  # One file per perf feature (13)
│       ├── data/                          # Shipped lists + perfmatters-defaults.json
│       └── mu-plugins/                    # Copy-to-mu-plugins Perfmatters config shim
│
├── assets/
│   ├── css/
│   │   ├── base.css                       # Global tweaks (always loaded)
│   │   ├── utilities.css                  # Utility classes (always loaded)
│   │   └── components/                    # Conditional per page type
│   │       ├── header.css                 # Header/nav overrides
│   │       ├── homepage.css               # Hero slider, sections, VIP signup
│   │       ├── product-slider.css         # Product carousel (flex track, dots, arrows)
│   │       ├── woo-archive.css            # Product cards (buttons, price suffix)
│   │       ├── woo-single.css             # Single product page (PDP)
│   │       ├── woo-checkout.css           # Checkout page
│   │       ├── woo-category-grid.css      # Category grid cards
│   │       ├── product-information.css    # Shipping/returns/FAQ panel
│   │       ├── offcanvas.css              # Shared off-canvas panel base
│   │       └── wishlist-offcanvas.css     # Wishlist panel
│   └── js/                                # Vanilla JS — zero jQuery dependency
│       ├── hero-slider.js                 # Homepage hero carousel (~1.2KB)
│       ├── product-slider.js              # Product carousel (~1.5KB)
│       ├── product-information.js         # Shipping calculator + tabs
│       └── wishlist-offcanvas.js          # Wishlist panel trigger/render
│
├── clients/
│   ├── _template/                         # Copy this for new client projects
│   │   ├── manifest.json                  # Client config (slug, features, active toggle)
│   │   ├── client-slug.css                # Client CSS template with rules
│   │   └── client-slug.php                # Client PHP template with examples
│   └── {client-slug}/                     # Active client module
│       ├── manifest.json                  # Client config + feature flags
│       ├── {slug}.css                     # Client design overrides (loaded LAST)
│       └── {slug}.php                     # Client-specific hooks
│
├── woocommerce/                           # WooCommerce template overrides
│   └── single-product/
│       └── bundled-item-attributes.php    # Bundle product heading override
│
├── docs/
│   ├── NEW-CLIENT-SETUP.md               # Step-by-step new client guide
│   ├── gutenberg-block-checklist.md       # Block implementation rules
│   └── patterns/                          # Pattern docs for each feature
│       ├── product-slider.md
│       ├── single-product-page.md
│       ├── global-ux-overrides.md
│       ├── offcanvas.md
│       ├── wishlist-offcanvas.md
│       ├── product-information.md
│       ├── product-tabs.md
│       ├── recently-viewed.md
│       ├── woo-category-grid.md
│       └── perf.md                        # PageSpeed modules (inc/perf/)
│
└── claude-commands/                       # Claude Code automation
    ├── setup-foundation.md
    └── setup-project.md
```

### Key Principles

- **Single component**: Product cards, buttons, etc. are styled once — apply everywhere
- **Client isolation**: Client-specific code in `clients/{slug}/`, never in shared `inc/`
- **Feature flags**: `manifest.json` controls which optional modules load
- **Conditional enqueue**: CSS/JS only loads on pages that need it
- **Vanilla JS**: Zero jQuery dependency, ~1-2KB per module
- **Blocksy-first**: Use Customizer settings before writing CSS

## Feature Flags

In `manifest.json`, list only the features this client needs:

```json
{
  "features": ["wishlist-offcanvas", "product-slider"]
}
```

Omit `"features"` entirely to enable all modules (backward compatible).

| Feature | Description |
|---------|-------------|
| `wishlist-offcanvas` | Off-canvas wishlist panel |
| `product-tabs` | ACF accordion fields as WC tabs |
| `product-information` | Shipping calculator + returns + FAQ |
| `recently-viewed` | Recently viewed products on PDP |
| `mini-cart-empty` | Empty mini cart with suggestions |
| `product-slider` | `[bc_product_slider]` shortcode |

### Perf feature flags (`perf` key)

PageSpeed modules in `inc/perf/` are **opt-in only**. They use their own `"perf"` key, and the "omit the key to enable all" rule above does not apply. List names, or `["*"]` for all. The `BLOCKSY_CHILD_PERF_FEATURES` constant and the `blocksy_child_perf_features` filter add to the list. Full reference: [`docs/patterns/perf.md`](docs/patterns/perf.md).

Copying `clients/_template/` opts a new site into the perf family (set `"perf": []` for an existing site that only needs `features` gating); existing sites without a manifest, or with a manifest lacking a `perf` key, are unaffected by this release — nothing loads, no hooks register.

```json
{
  "perf": ["perfmatters-config", "perfmatters-filters", "rucss-safelist", "lcp-image"]
}
```

| Perf feature | Purpose | Recommended default |
|---------|-------------|-------------|
| `perfmatters-config` | Perfmatters `perfmatters_options` row as code (`perfmatters-defaults.json` + per-site `custom/perfmatters.json`) | On |
| `perfmatters-filters` | Used-CSS below `</head>`, 7000 s Delay JS timeout, click replay, analytics excluded from delay, leading-images and CSS-background filters | On |
| `rucss-safelist` | Keeps JS-injected markup (FiboSearch, cart panel, carousels) styled through Remove Unused CSS | On |
| `critical-css-supplements` | Inline `bc-perf-critical-{global,archive,product}` rules that RUCSS drops | On |
| `lcp-image` | Un-lazies the PDP gallery, first cover block and `bc-hero-img` hero, with one matching `<link rel=preload as=image>` | On |
| `hero-facade` | Poster-first YouTube/`<video>` hero, media mounted on interaction or after 5 s and revealed when PLAYING | Off: needs per-site poster assets and `.bc-hero-video` markup |
| `fonts-critical-path` | Inlines the local Google Fonts CSS (`bc-perf-fonts-inline`), plus optional metric-matched fallbacks | On |
| `async-styles` | Non-render-blocking `media=print` swap for listed plugin sheets (FiboSearch, reviews widget) | On |
| `dequeue-assets` | Dequeues and deregisters handles verified unused in the DOM | On. The default list is conservative, so still verify it per site |
| `media-hygiene` | Missing-size fallback, logo `sizes`, archive thumbnail dimensions, below-fold lazy, stretched-height fix, youtube-nocookie | On |
| `content-visibility` | `content-visibility:auto` for the footer, related products and closed off-canvas panels (`bc-perf-cv`) | On |
| `minicart-hydrate` | Empty-cart suggestions inside a `<template>`, hydrated on intent, plus a WC fragments seed that skips the first-session refresh | On |
| `lazy-rescan` | Re-runs the Perfmatters lazy loader after Blocksy AJAX filter or Load More on shop/taxonomy archives | On |

## Responsive Breakpoints

| Name | Breakpoint | Media Query |
|------|-----------|-------------|
| Desktop | >999.98px | Default |
| Tablet | ≤999.98px | `@media (max-width: 999.98px)` |
| Mobile | ≤689.98px | `@media (max-width: 689.98px)` |

## Versioning

- Bump `Version` in `style.css` after each release-worthy change. `BLOCKSY_CHILD_VERSION` in `functions.php` is read from that header (`wp_get_theme()->get( 'Version' )`), so it needs no edit.
- Follow semver: major.minor.patch
- Record changes in `CHANGELOG.md` at the theme root (newest entries first)

## Deployment

Deploy directly to the staging server via SSH (then promote to production via Kinsta). The server is the source of truth — there is no git-driven CI/CD pipeline. The local checkout / GitHub repo is used only for code review and history; never as the deploy target.

```bash
# Push individual files
scp -P <port> path/to/file.css <user>@<host>:/www/<site>/public/wp-content/themes/blocksy-child/path/to/file.css

# After enqueue.php / functions.php edits, bust the asset cache
ssh <alias> "touch /www/<site>/public/wp-content/themes/blocksy-child/inc/enqueue.php"
```

Each project's CLAUDE.md / SSH alias config has the correct host, port, and path. Always update `CHANGELOG.md` after a deploy.

## Documentation

- [`docs/NEW-CLIENT-SETUP.md`](docs/NEW-CLIENT-SETUP.md) — New client onboarding
- [`docs/gutenberg-block-checklist.md`](docs/gutenberg-block-checklist.md) — Block implementation rules
- [`docs/patterns/`](docs/patterns/) — Pattern docs for each feature

## License

GPL-2.0-or-later
