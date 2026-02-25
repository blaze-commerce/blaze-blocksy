# Blaze Blocksy — Child Theme

Parent: **Blocksy**. Portable child theme deployed across multiple sites.
Version in `style.css` header → `BLAZE_BLOCKSY_VERSION` constant in `functions.php`.

## Architecture

```
functions.php              ← Entry point, loads all modules
├── custom/custom.php      ← Site-specific entry point (gitignored but tracked)
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

### Gitignored — never overridden

`custom/` is in `.gitignore`. The base files (`custom.php`, `custom.css`, `custom.js`, `index.php`) were added to git before the ignore rule, so they remain tracked — gitignore only affects untracked files. All other files in `custom/` (feature modules, `css/`, `js/` subdirs) are untracked and exist only on each deployment.

- `custom/` **persists independently of theme updates** — deploying or updating the child theme MUST NEVER override, delete, or replace custom/ contents on the server
- Site-specific feature files (untracked) can only be edited directly on the server or in Local Sites
- When working on a Local Sites instance or via SSH where `custom/` is accessible on the filesystem, Claude may edit those files directly

### Boundary rules

1. **ALL site-specific code → `custom/` only** — never in `includes/`, `assets/`, or root files
2. **Everything outside `custom/` must be generic** — portable across all sites, no store-specific logic
3. **`custom/custom.php` is the ONLY entry point** — `functions.php` loads it (line 160). All other custom PHP files MUST be `require_once`d from `custom.php`
4. **Never add custom/ paths to `$required_files`** in `functions.php` — that array is for generic includes only

### Modular files — STRICTLY PROHIBITED from code changes (CRITICAL — BLOCKING)

`custom.php`, `custom.css`, and `custom.js` are **append-only loaders**. Writing any feature code, styles, or logic directly into these files is **STRICTLY PROHIBITED** — no exceptions, including "quick one-offs". The only permitted modification is appending a `require_once` or `wp_enqueue_*` line to load a new dedicated file.

**PHP — create a dedicated file, then require it in custom.php:**
```php
// custom/header-tweaks.php ← new file with all the logic
// custom/custom.php ← just append this line:
require_once __DIR__ . '/header-tweaks.php';
```

**CSS — create a dedicated stylesheet, then enqueue it in custom.php:**
```php
// custom/css/header.css ← new file with all the styles
// custom/custom.php ← append inside wp_enqueue_scripts action:
wp_enqueue_style( 'blaze-custom-header', "$uri/css/header.css", [], filemtime( "$dir/css/header.css" ) );
```

**JS — create a dedicated script, then enqueue it in custom.php:**
```php
// custom/js/header.js ← new file with all the logic
// custom/custom.php ← append inside wp_enqueue_scripts action:
wp_enqueue_script( 'blaze-custom-header', "$uri/js/header.js", [ 'jquery' ], filemtime( "$dir/js/header.js" ), true );
```

**Naming convention:** name files after the feature — `currency-visibility.php`, `css/checkout-upsell.css`, `js/mini-cart-extras.js`.

### Recommended custom/ file structure

```
custom/
├── custom.php               ← Loader: require_once + enqueue lines (tracked)
├── custom.css               ← Append-only loader: enqueue lines only (tracked)
├── custom.js                ← Append-only loader: enqueue lines only (tracked)
├── index.php                ← Silence is golden (tracked)
├── header-tweaks.php        ← Feature module (untracked — site-specific)
├── currency-visibility.php  ← Feature module (untracked — site-specific)
├── css/
│   ├── header.css           ← Feature stylesheet (untracked — enqueued in custom.php)
│   └── checkout-upsell.css
└── js/
    ├── header.js            ← Feature script (untracked — enqueued in custom.php)
    └── mini-cart-extras.js
```

## Where Does Code Go?

| Code type | Location | Example |
|-----------|----------|---------|
| Site-specific PHP feature | `custom/<feature>.php` → require in `custom.php` | `currency-visibility.php`, `header-tweaks.php` |
| Site-specific CSS feature | `custom/css/<feature>.css` → enqueue in `custom.php` | `css/header.css`, `css/checkout-upsell.css` |
| Site-specific JS feature | `custom/js/<feature>.js` → enqueue in `custom.php` | `js/header.js`, `js/mini-cart-extras.js` |
| Any CSS change (even minor) | `custom/css/<feature>.css` → enqueue in `custom.php` | `css/tweak.css` |
| Any JS change (even minor) | `custom/js/<feature>.js` → enqueue in `custom.php` | `js/tweak.js` |
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

- Conventional commits: `feat:`, `fix:`, `docs:`, `refactor:`, `chore:`
- Do NOT add Claude as co-author or Co-Authored-By header
- Present tense, imperative mood, first line under 72 chars
