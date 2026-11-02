# House style

These rules apply to v2 code: `src/`, `tools/` and modules with status `beta` or `stable`. v1 code in `inc/`,
`woocommerce/` and `partials/` is not held to them.

The right-hand column names the gate in `php tools/check.php` that enforces the rule. "Review" means no tool
checks it, so the PR reviewer does. Gates marked warn print a warning and do not fail the run.

## PHP

| Rule | Enforced by |
|---|---|
| PHP 8.1 or newer. Don't use syntax newer than 8.1. | `php_lint` (CI lints on 8.1, 8.3 and 8.4) |
| WordPress-Extra coding standard, short array syntax, text domain `blocksy-child`, global prefix `blaze_child` / `Blaze\Child` (config: `tools/phpcs.xml`). | `phpcs` |
| PHPStan with the WordPress and WooCommerce stubs (config: `tools/phpstan.neon`). | `phpstan` |
| No `declare(strict_types=1)`. A TypeError inside a hook callback is an uncaught fatal. | `no_strict_types` |
| Hook callbacks are named methods or functions, never closures, so site code can unhook them. | `no_hook_closures` |
| Every PHP file starts with `defined( 'ABSPATH' ) \|\| exit;` (after the namespace line, if any). | `abspath_guard` |
| No unguarded global function, class or constant declared twice. | `duplicate_symbols` |
| Escape output, sanitise input. | `phpcs` (WordPress.Security sniffs) |
| Nonce and capability check on every write. | Review (phpcs catches only missing nonce verification on request data) |
| No direct SQL. Use WordPress and WooCommerce APIs. | `phpcs` (WordPress.DB sniffs) catches the obvious cases; review for the rest |
| Order data through `WC_Order` methods, so it works with HPOS. | Review |
| Strings are translatable with text domain `blocksy-child`. | `phpcs` (WordPress.WP.I18n) |
| `load_child_theme_textdomain()` once; `wp_set_script_translations()` for JS strings. | Review |
| Hook and filter names start with `blaze_child/`. Body class per module is `bc-m-<id>` (added by core). | `phpcs` in part (WordPress.NamingConventions.PrefixAllGlobals): a hook the theme fires with `do_action()` / `apply_filters()` must start with `blaze_child` (errors; a fully variable name is only a warning). It does not check the `/` after the prefix (`blaze_child_x` passes; WordPress.NamingConventions.ValidHookName is off), nor hooks the theme only listens to. Review for the rest. |
| Every module defaults off. | `manifest_schema` (`default` must be `false`) |
| Module `Module.php` declares `Blaze\Child\Modules\<StudlyId>\Module` and extends `Blaze\Child\Core\Module`. | `class_name` |

## Manifest and surfaces

| Rule | Enforced by |
|---|---|
| `module.json` matches the schema. | `manifest_schema` |
| Ids are kebab-case, unique, and match the folder name. | `manifest_ids` |
| One non-deprecated module per surface. A second needs `replaces`. | `surface_unique` |
| Surfaces and components come from `tools/surfaces.json`. Add the entry in the same PR. | `surface_vocabulary` |
| Drawer and carousel code lives only in the module that `provides` that component. Others call the shared component. | `component_lookalike` (warn) |
| `docs/MODULES.md` is regenerated, never edited by hand. | `modules_md_fresh` |
| `style.css` Version and the top `## [x]` heading of `CHANGELOG.md` agree. | `version_agreement` |
| The zip built by `tools/build.php` has the `blocksy-child/` root, no tests or fixtures, and a manifest that lists exactly what ships. | `dist_contents` |

## JavaScript

| Rule | Enforced by |
|---|---|
| Vanilla ES2020. No jQuery: not as a dependency, not in the code. | `no_jquery` |
| Scripts load with `strategy => defer`. | Core does it for manifest assets; review for anything enqueued by hand |
| Shared code is exposed as `window.blazeChild.<component>`; no other JS globals. Dependencies are script handles. No bundler. | Review |
| Config reaches JS through `wp_add_inline_script` or data attributes. | Review |
| Delegated events, no polling, ARIA built in. | Review |
| `textContent` or `setAttribute` for server-supplied strings, never `innerHTML` with a variable. | `no_innerhtml_vars` (warn) |
| Unit tests for non-trivial JS, as `modules/<id>/assets/*.test.mjs` (`node:test`). | `tests/run-all.php` |

## Cache safety

No nonce and no per-visitor data in HTML that a page cache can store. Anything personal is fetched or rendered in
the browser. This is a review item, and the most common reason a PR is sent back: ask "what happens if the full
page cache stores this response for the next visitor?"

## CSS

| Rule | Enforced by |
|---|---|
| One CSS file per module. | Review |
| Classes are `bc-<id>__<element>`. | Review |
| Colours and sizes are custom properties taken from the Blocksy palette. | Review |
| No client-specific hex colour outside `:root`. | `client_denylist` |
| `!important` only with a comment saying why. | Review |

## Shared code

| Rule | Enforced by |
|---|---|
| No client names, client page or post IDs, or client hex colours in `src/`, `tools/` or beta and stable modules. Client behaviour goes in the site folder (`wp-content/blaze-site/`) and is switched with settings. | `client_denylist` (list in `tools/denylist.txt`) |

## Assets

No bundler in phase 0: the files in the repo are the files that ship. Phase 1 adds minification at release time,
with a gate that the zip matches its source. Until then, don't commit minified files.
