# Blaze Blocksy — Child Theme

Parent: **Blocksy**. Portable child theme deployed across multiple sites.
Version in `style.css` header → `BLAZE_BLOCKSY_VERSION` constant in `functions.php`.

## Architecture

```
functions.php              ← Entry point, loads all modules
├── custom/custom.php      ← Site-specific entry point (GITIGNORED)
├── includes/              ← Generic reusable features
│   ├── scripts.php        ← Asset enqueueing
│   ├── features/          ← Standalone feature modules
│   ├── customization/     ← WooCommerce/Blocksy customizations
│   ├── blocks/            ← Custom Gutenberg blocks
│   └── gutenberg/         ← Gutenberg extensions
├── assets/                ← Generic CSS, JS, images, vendor libs
├── woocommerce/           ← WooCommerce template overrides
└── partials/              ← Template partials
```

## The custom/ Directory (CRITICAL — STRICT)

### Gitignored — Never overridden

`custom/` contents are **gitignored**. They exist only on each server or Local Sites instance. Only `custom.php.dist` (the template) is tracked in git.

- `custom/` **persists independently of theme updates** — deploying or updating the child theme MUST NEVER override, delete, or replace `custom/` contents on the server
- Claude **cannot modify custom/ files via git** — when site-specific changes are needed, **output the code** and instruct the user to apply it on the server or in Local Sites
- When working on a Local Sites instance or via SSH where `custom/` is accessible on the filesystem, Claude may edit those files directly

### Boundary rules

1. **ALL site-specific code → `custom/` only** — never in `includes/`, `assets/`, or root files
2. **Everything outside `custom/` must be generic** — portable across all sites, no store-specific logic
3. **`custom/custom.php` is the ONLY entry point** — `functions.php` loads it (line 160). All other custom PHP files MUST be `require_once`d from `custom.php`
4. **Never add custom/ paths to `$required_files`** in `functions.php` — that array is for generic includes only

### Setup: custom.php.dist → custom.php

The repo ships `custom/custom.php.dist` as a committed template. On each new deployment:

```bash
cp custom/custom.php.dist custom/custom.php
```

`custom.php` is gitignored — once copied, it belongs to that deployment and will never be overwritten by git. Each site can then modify `custom.php` to add site-specific `require_once` lines, CSS/JS, and hooks.

### Recommended custom/ file structure

```
custom/
├── custom.php.dist  ← Template (tracked in git)
├── custom.php       ← Loader (GITIGNORED — copied from .dist per deployment)
├── custom.css       ← Site-specific frontend CSS (gitignored)
├── custom.js        ← Site-specific frontend JS (gitignored)
├── css/             ← Additional site-specific stylesheets
├── js/              ← Additional site-specific scripts
└── *.php            ← Feature modules loaded via custom.php
```

## Where Does Code Go?

| Code type | Location | Example |
|-----------|----------|---------|
| Site-specific CSS/JS | `custom/custom.css`, `custom/custom.js` | Store colors, layout overrides |
| Site-specific PHP logic | `custom/*.php` (via `custom.php`) | Currency visibility, store hooks |
| Site-specific Gutenberg extensions | `custom/*.php` + `custom/*.js` | Block editor plugins for one store |
| Generic theme features | `includes/features/` | Offcanvas module, shipping calc |
| Plugin-specific customizations | `includes/customization/` | Fluid Checkout tweaks, Judge.me |
| Generic CSS/JS assets | `assets/css/`, `assets/js/` | Product card styles, mini-cart JS |
| WooCommerce template overrides | `woocommerce/` | Cart, checkout, email templates |
| Gutenberg blocks | `includes/blocks/` | Variation swatches block |

## Module Conventions

- Each PHP module is self-contained: registers its own hooks/actions/filters
- `ABSPATH` guard at top of every PHP file
- Feature toggles via `define()` constants (e.g., `BLAZE_HIDE_CART_PAGE`)
- Asset versioning: `filemtime()` for cache busting — not hardcoded versions
- Function prefix: `blaze_blocksy_` (generic), `blaze_custom_` (site-specific)
- New generic modules: add path to `$required_files` array in `functions.php`

## Commits

- Conventional commits with emoji: `✨ feat:`, `🐛 fix:`, `📝 docs:`, `♻️ refactor:`, `🔧 chore:`
- Do NOT add Claude as co-author or Co-Authored-By header
- Present tense, imperative mood, first line under 72 chars
