# Contributing

**Before you build anything: search `docs/MODULES.md` (or run `swamp workflow run blocksy-child-module`).**

The catalogue lists every feature the theme has, including v1 code that is still inside `legacy-v1` and features
that are planned. Most requests are a setting on an existing module, or a port of a v1 file, not a new feature. If
you build a second version of something the catalogue already has, the surface rule below will stop the PR.

## Finding an existing feature

1. Open `docs/MODULES.md`. Search for the page area (cart, drawer, checkout, product tabs) or the plugin.
2. Read the module's `modules/<id>/README.md`: what it does, its settings, its hooks.
3. Status tells you what you are looking at:
   - `stable` / `beta`: v2 code, works today.
   - `legacy`: the code still lives in `inc/` and runs through `legacy-v1`. The module is a catalogue entry; porting
     it is the work (recipe below).
   - `planned`: on the old line but not on `main`, stub only.
4. If a module exists but does not do what the site needs, add a setting or a filter to it. Don't fork it.

## Adding a module

One module is one folder, `modules/<id>/`, with a kebab-case id. A new module is for a feature that doesn't exist
yet. Porting a v1 file into an existing stub is the same recipe: replace the stub in place, keep the id and
surfaces.

### The port recipe (copy `modules/hide-cart-page/`)

1. **Manifest.** Copy `modules/hide-cart-page/module.json`. Set `id`, `title`, `description`, `status`
   (`beta` until it has run on a staging site), `loadable: true`, `owner`, `since`, `surfaces`, `requires`,
   `settings` and `critical`. Keep `default: false`. For a port, list the v1 file under `legacy.files` and the old
   constants and filters under `legacy.constants` and `legacy.filters`.
2. **Surface.** Pick the surface from `tools/surfaces.json`. If none fits, add one there in the same PR, with a
   one-line description. See the surface rule below.
3. **Class.** `modules/<id>/Module.php`: namespace `Blaze\Child\Modules\<StudlyId>`, `class Module extends
   \Blaze\Child\Core\Module`, a `boot()` that registers hooks with named public methods. Read options with
   `$this->setting( 'key' )`. No closures on hooks.
4. **Behaviour parity.** A port must render the same as v1 unless the difference is a documented fix. Keep v1's
   hooks, priorities and markup. Keep v1's constants and filters as deprecated aliases that record
   `deprecated_alias` in the health log.
5. **Assets.** Declare them in `module.json` with a `where`, not in PHP. One CSS file per module, classes
   `bc-<id>__<el>`. No jQuery. See [STYLE.md](STYLE.md).
6. **Tests.** `tests/modules/test-<id>.php` (see `test-hide-cart-page.php`). JS gets `*.test.mjs` next to it.
7. **README.** `modules/<id>/README.md`: what it does, settings table, surfaces, hooks (new and deprecated), how to
   extend or disable it. Follow `modules/hide-cart-page/README.md`.
8. **Catalogue.** Run `php tools/gen-modules-md.php`.
9. **Changelog.** Add a line under the top `## [x]` section of `CHANGELOG.md`.

A module never contains a client name, a client page ID or a client colour. Per-client differences are settings
in the site's `site-config.php`, or code in the site's `site.php` (`wp-content/blaze-site/`).

### The surface rule

A surface is a page area (`cart.page`, `drawer.cart.icon`, `pdp.recently-viewed`). **Only one non-deprecated
module may declare a given surface.** If you need to change what a surface does, either add a setting to the module
that owns it, or write a new module that lists the old one in `replaces`. Two modules fighting over the same area
is how v1 ended up with duplicates. The `surface_unique` gate enforces this.

Drawers and carousels are shared components. Only the module that `provides` `component.drawer` or
`component.carousel` may contain that code; other modules `use` it.

## Pull requests

The PR template carries the checklist. In short:

- [ ] I searched the catalogue (`docs/MODULES.md`) and no module already does this
- [ ] The surface is claimed once (a second module on a surface uses `replaces`)
- [ ] The module defaults off
- [ ] Tests added or updated, and `php tests/run-all.php` is green
- [ ] Docs updated: module README, `CHANGELOG.md`, `docs/MODULES.md` regenerated
- [ ] `php tools/check.php` is green

## Running the tests and the gates locally

```bash
php tests/run-all.php          # perf, core, modules, tools suites, then module JS tests if node is installed
php tests/modules/run.php      # one suite
node --test "modules/**/*.test.mjs"   # module JS tests (node 22)

php tools/check.php            # all gates; exit 1 if any fail
php tools/check.php --module=hide-cart-page   # module-scoped gates for one module
php tools/check.php --json     # the report as JSON (what swamp reads)
php tools/gen-modules-md.php            # regenerate docs/MODULES.md
php tools/gen-modules-md.php --check    # fail if it is stale (CI runs this)
```

Nothing needs WordPress installed; the tests use stubs.

`phpcs` and `phpstan` are optional locally: when they are missing, `check.php` prints a warning for them and
skips them. CI always runs them, so install them to see what CI will say. CI (`.github/workflows/check.yml`)
runs on PHP 8.1, 8.3 and 8.4 with these exact versions:

```bash
composer global config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
composer global config allow-plugins.phpstan/extension-installer true
composer global require \
  squizlabs/php_codesniffer:3.13.6 \
  wp-coding-standards/wpcs:3.4.1 \
  phpcsstandards/phpcsutils:1.2.3 \
  phpcsstandards/phpcsextra:1.5.1 \
  dealerdirect/phpcodesniffer-composer-installer:1.2.1 \
  phpstan/phpstan:2.3.4 \
  szepeviktor/phpstan-wordpress:2.0.4 \
  php-stubs/wordpress-stubs:7.1.2 \
  phpstan/extension-installer:1.4.3 \
  php-stubs/woocommerce-stubs:11.2.0
export PATH="$(composer global config bin-dir --absolute 2>/dev/null):$PATH"
```

When you bump one of these, change `check.yml` and this page together.

The house rules and the gate that enforces each one are in [STYLE.md](STYLE.md). How the theme boots and fails is
in [ARCHITECTURE.md](ARCHITECTURE.md).
