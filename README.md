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
│   └── product-slider.php                 # [feature] [bc_product_slider] shortcode
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
│       └── woo-category-grid.md
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

## Responsive Breakpoints

| Name | Breakpoint | Media Query |
|------|-----------|-------------|
| Desktop | >999.98px | Default |
| Tablet | ≤999.98px | `@media (max-width: 999.98px)` |
| Mobile | ≤689.98px | `@media (max-width: 689.98px)` |

## Versioning

- Bump `BLOCKSY_CHILD_VERSION` in `functions.php` and `Version` in `style.css` after each release-worthy change
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
