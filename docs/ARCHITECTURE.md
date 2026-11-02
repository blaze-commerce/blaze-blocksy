# Architecture

How the v2 theme boots, how it decides which modules run, and what happens when something breaks. The design
rationale is in the [spec](https://github.com/blaze-commerce/blaze-swamp/blob/jayr/blocksy-child-v2/docs/superpowers/specs/2026-11-01-blocksy-child-v2-design.md)
(sections 3 to 5). Where this page and the spec differ, this page describes the code.

## Layout

| Path | What lives there |
|---|---|
| `functions.php` | Loads `src/bootstrap.php` and nothing else. |
| `src/bootstrap.php` | Defines `BLAZE_CHILD_VERSION`, `BLAZE_CHILD_PATH`, `BLAZE_CHILD_URL`, registers the PSR-4 autoloader, calls `Theme::boot()`. |
| `src/Core/` | `Theme`, `Registry`, `SiteLayer`, `FailClosed`, `Health`, `Assets`, `Compat`, `Legacy`, `Module`. |
| `src/Api.php` | `Blaze\Child\Api\module( $id )`: the booted instance of a module, or null. |
| `modules/<id>/` | One folder per feature: `module.json` (the only metadata), `Module.php`, `README.md`, assets. |
| `modules/legacy-v1/` | Runs the v1 code in `inc/` unchanged. See below. |
| `inc/`, `assets/`, `woocommerce/`, `partials/` | v1 code. Not edited except the one skip-list check in `inc/loader.php`. |
| `tools/` | `check.php` (the gates), `gen-modules-md.php`, `build.php`. |
| `tests/` | Dependency-free suites; `php tests/run-all.php` runs them all. |

Autoloading: `Blaze\Child\Core\Registry` is `src/Core/Registry.php`. `Blaze\Child\Modules\HideCartPage\Module` is
`modules/hide-cart-page/Module.php` (the id is kebab-case, the namespace segment is StudlyCase).

## Boot order

All of it runs at theme-load time, from `Theme::boot()`, in this order:

1. `SiteLayer::config()` reads `wp-content/blaze-site/site-config.php` (once per request).
2. The `Registry` reads every `modules/*/module.json`.
3. The legacy-v1 skip list is computed from manifest defaults, site config and the kill switch only
   (`Registry::skip_files()`), and exposed on the `blaze_child/legacy_v1/skip_files` filter. Site code has not run
   yet, so it cannot influence this step (R1).
4. `legacy-v1` loads v1's `inc/loader.php`, if it is on and the site config is valid.
5. `SiteLayer::include_site_code()` includes `wp-content/blaze-site/site.php`.
6. Modules are resolved (see below) and booted, unless the site config is broken.
7. If a critical module failed and `on_failure` is not `open`, checkout is closed (`FailClosed::arm()`).
8. Orphan check: a module whose v1 files were skipped but which did not boot is recorded as `legacy_files_orphaned`.
9. `Assets::register()` hooks the manifest-declared assets and the `bc-m-<id>` body classes.
10. The `admin_notices` hook is added for the health list.

`legacy-v1` then runs v1's `inc/perf/loader.php` from its own `boot()`, in the same position as in v1.

## The site layer

Per-site code lives outside the theme folder, because a WordPress theme update deletes anything inside it (R5):

```
wp-content/blaze-site/
  site-config.php   returns [ 'modules' => [ id => bool ], 'settings' => [ id => [ key => value ] ], 'on_failure' => 'closed'|'open' ]
  site.php          hooks, filters and constants for this site
```

- `BLAZE_SITE_DIR` overrides the folder. If it is defined and the folder is missing, `site_dir_missing` is recorded.
- With no `blaze-site/` folder, v1's `blocksy-child/custom/custom.php` is still loaded as site code
  (`legacy_custom_in_theme`: move it out). If both exist, only `blaze-site` loads (`legacy_custom_ignored`).
- With neither, no module boots and one `no_site_config` entry is recorded. No PHP warning is raised.
- Unknown keys in `site-config.php` are ignored. `on_failure` is `closed` unless it is exactly `open`.

## How a module is switched on or off

`Registry::resolve()` decides in this order, per module:

1. The manifest `default` (always `false` in v2).
2. The site-config `modules` entry for that id.
3. The `blaze_child/module_enabled` filter (`$on, $id`), which runs after site code has loaded. For a module with
   non-empty `legacy.files`, this filter may only switch it off, never on (R1). Such a module is enabled from
   `site-config.php` only.
4. `loadable: false` forces off. Stubs have no PHP.
5. The `BLAZE_CHILD_DISABLE_MODULES` kill switch (a constant: array or comma-separated string) removes ids.
6. Surface clashes: one module per surface. The one that `replaces` the other wins, otherwise the one that comes
   first in site-config order. The loser is skipped and `surface_clash` is recorded.

Then each module is booted: `requires` is checked first (`requires_unmet` skips only that module), then the class
is loaded and `boot()` called. An exception records `module_failed`. A module id in site config with no manifest
records `unknown_module`; the other modules still boot.

## Fail closed

The rule: if code that can affect price, tax, shipping or payment is broken, the site stops taking orders rather
than taking wrong ones. The catalogue stays browsable.

`FailClosed::arm( $reason )` is called when:

- `site-config.php` throws, has a parse error or returns a non-array (`site_config_invalid`);
- `site.php` throws (`site_code_failed`);
- a module with `critical: true` fails to boot, or is skipped for an unmet `requires` (R7);
- legacy-v1's core fails to load (`legacy_v1_failed`).

`on_failure => 'open'` in `site-config.php` turns the arming off for the site-code and module cases. A broken
`site-config.php` always closes.

When armed, checkout is refused three ways:

- Classic checkout: an error notice on `woocommerce_checkout_process`.
- Classic order-pay: the same notice on `woocommerce_before_pay_action`, so WooCommerce skips payment (R9).
- Store API: `rest_pre_dispatch` returns a 503 for any `POST` to a route matching
  `#^/+wc/store(?:/v\d+)?/checkout#i`, including `/checkout/{id}` and the unversioned namespace (R8). WordPress
  matches routes case-insensitively, so the pattern does too.

The site admin gets one email per hour (transient `blaze_child_failclosed_mail`), and `checkout_closed` is
recorded.

A broken `site-config.php` boots no modules and no legacy-v1 core, but `site.php` still loads, because site code
may define functions that templates call (R10, R11).

## legacy-v1 and the skip list

`legacy-v1` is a module whose only job is to run today's v1 code (`inc/loader.php`, site code, `inc/perf/`)
unchanged, so a v1 site renders the same after the update. It adds no body class (R12).

When a ported module replaces a v1 file, its manifest lists the file under `legacy.files`. While the module is on,
`inc/loader.php` skips that file (the check is the `blaze_child/legacy_v1/skip_files` filter). Turn the module off
through site config or the kill switch and v1 loads the file again.

Because the skip list is computed before site code runs, only `site-config.php` and the kill switch count. The
`module_enabled` filter cannot enable such a module (R1). If a module is on when the skip list is built but then
fails to boot (filtered off, lost a surface clash, unmet `requires`, exception), the v1 file is skipped and nothing
replaces it. `legacy_files_orphaned` records that, naming the files (R13).

## Assets

A module declares assets in `module.json`, not in PHP:

```json
"assets": [ { "file": "assets/x.js", "where": ["pdp"], "deps": [] } ]
```

`Assets::enqueue()` runs on `wp_enqueue_scripts` at priority 20 and enqueues a file when any of its `where` values
matches the request. Files are relative to the module folder; the handle is `bc-<id>-<filename>` and the version is the file's mtime. JS is enqueued with
`defer`. A missing file records `asset_missing`. A `jquery` dependency is dropped and recorded as
`jquery_dep_refused` (legacy modules are exempt).

| `where` | Matches |
|---|---|
| `all` | every request |
| `front` | not wp-admin |
| `wc` | `is_woocommerce()`, `is_cart()`, `is_checkout()`, `is_account_page()`, or a singular or front page that contains a WooCommerce block or shortcode (`Assets::page_has_wc_content()`). Never page IDs. |
| `pdp` | `is_product()` |
| `archive` | `is_shop()` or `is_product_taxonomy()` |
| `cart` | `is_cart()` |
| `checkout` | `is_checkout()` |
| `account` | `is_account_page()` |

Every booted module except `legacy-v1` also adds the body class `bc-m-<id>`.

## Health codes

`Health::record( $code, $message, $context )` appends to the `blaze_child_health` option: at most 50 entries, and an
identical code and message is not rewritten within an hour. Admins see entries from the last 24 hours in a wp-admin
notice.

| Code | Recorded when |
|---|---|
| `no_site_config` | No `blaze-site/` folder and no legacy `custom/custom.php`. |
| `site_dir_missing` | `BLAZE_SITE_DIR` is defined but the folder does not exist. |
| `site_config_invalid` | `site-config.php` threw, failed to parse or did not return an array. Closes checkout. |
| `site_code_failed` | `site.php` (or legacy `custom.php`) threw. Closes checkout unless `on_failure` is `open`. |
| `legacy_custom_in_theme` | Site code is loaded from the theme folder's `custom/custom.php`. |
| `legacy_custom_ignored` | Both `blaze-site/` and `custom/custom.php` exist; only `blaze-site` loads. |
| `manifest_invalid` | A `module.json` is not valid JSON, has a bad id, or its id does not match its folder. |
| `unknown_module` | Site config names a module id that has no manifest. |
| `surface_clash` | Two enabled modules declare the same surface; one is skipped. |
| `requires_unmet` | A module's `requires` (WooCommerce version, Blocksy version) is not met; it is skipped. |
| `module_failed` | A module threw while booting, or its class is missing. |
| `legacy_files_orphaned` | v1 files were skipped for a module that then did not boot. |
| `checkout_closed` | `FailClosed::arm()` ran; the message names the reason. |
| `asset_missing` | A manifest asset file does not exist. |
| `jquery_dep_refused` | A non-legacy asset declared a jQuery dependency; the dependency was dropped. |
| `deprecated_alias` | A deprecated constant or filter from v1 was used (`hide-cart-page`). |
| `svg_rejected` | `offcanvas-icons` refused an uploaded SVG; the default icon is used. |

Add a row here when a module records a new code.
