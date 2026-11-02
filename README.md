# Blocksy Child

Reusable Blocksy child theme for Blaze Commerce projects. Powered by Claude, crafted by @jarutosurano.

This is version 2 (branch `2.x`): a modular rewrite. Every feature is a module with a manifest, off by default,
switched on per site. v1 code still runs unchanged through the `legacy-v1` module until each feature is ported.

## Requirements

- PHP 8.1+ (CI runs 8.1, 8.3 and 8.4)
- WordPress 6.6+
- [Blocksy](https://creativethemes.com/blocksy/) 2.1+ as the parent theme
- WooCommerce 9.0+ for the modules that need it (a per-module requirement)
- [Blocksy Companion Pro](https://creativethemes.com/blocksy/) for the premium extensions

## Before you build anything

Search [`docs/MODULES.md`](docs/MODULES.md). It lists every feature, ported or not, with its surface and status. A
page area can have only one module. Most requests are a setting on an existing module.

## How it fits together

```
functions.php            loads src/bootstrap.php
src/Core/                Theme, Registry, SiteLayer, FailClosed, Health, Assets, Compat, Legacy, Module
modules/<id>/            module.json (metadata), Module.php, README.md, assets
modules/legacy-v1/       runs the v1 code in inc/ unchanged
inc/ assets/ ...         v1 code, kept as it was
tools/                   check.php (quality gates), gen-modules-md.php, build.php
tests/                   dependency-free suites
wp-content/blaze-site/   per-site code and config, outside the theme (site.php, site-config.php)
```

A site chooses its modules in `wp-content/blaze-site/site-config.php`:

```php
return [
	'modules'  => [ 'hide-cart-page' => true ],
	'settings' => [ 'hide-cart-page' => [ 'destination' => 'shop' ] ],
];
```

With no site folder, no module boots and one health entry is recorded. If site code or a critical module breaks,
checkout refuses orders and the catalogue stays up (see Architecture).

## Develop

```bash
php tests/run-all.php    # all test suites
php tools/check.php      # all quality gates, the same script CI runs
```

## Docs

| Page | For |
|---|---|
| [`docs/MODULES.md`](docs/MODULES.md) | The catalogue (generated). Search here first. |
| [`docs/CONTRIBUTING.md`](docs/CONTRIBUTING.md) | Adding or porting a module, the PR checklist, running tests and gates |
| [`docs/STYLE.md`](docs/STYLE.md) | House style and the gate that enforces each rule |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Boot order, site layer, resolver, fail-closed, legacy-v1, health codes |
| [`docs/MIGRATING-A-SITE.md`](docs/MIGRATING-A-SITE.md) | Outline for moving a v1 site to v2 |
| [Design spec](https://github.com/blaze-commerce/blaze-swamp/blob/jayr/blocksy-child-v2/docs/superpowers/specs/2026-11-01-blocksy-child-v2-design.md) | Why it is built this way |

Never "replace current"-upload v2 onto a v1 site. See the migration page.

The v1 setup guides (`docs/NEW-CLIENT-SETUP.md`, `docs/patterns/`) describe the v1 `clients/` folder and apply to
`main` and to sites still on `legacy-v1`.

## Versioning

`style.css` `Version` is the single source. `CHANGELOG.md` carries a matching `## [x]` heading at the top.
