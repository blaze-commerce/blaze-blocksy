# legacy-v1

Runs the v1 child theme (`inc/`) unchanged. Default off; a v1 site switches it on in
`wp-content/blaze-site/site-config.php` (`'modules' => [ 'legacy-v1' => true ]`) and renders exactly as before.

## Order (same as v1 `functions.php`)

1. `Module::load_v1_core()` requires `inc/loader.php` (called by `Core\Theme::boot()` before site code).
2. Site code: `blaze-site/site.php`, or legacy `custom/custom.php`.
3. `Module::boot()` requires `inc/perf/loader.php`.

`load_v1_core()` is skipped when the module is off (default, site config, kill switch
`BLAZE_CHILD_DISABLE_MODULES`) and when `site-config.php` is broken (R11: a broken config boots nothing).

One difference from v1: `inc/loader.php` now runs inside a method, so its file-level loop variables
(`$optional_modules`, `$feature`, `$file`) are locals, no longer globals. Nothing in the theme reads them.

## Skip list

When a ported module that lists `legacy.files` is switched on, the v1 loader must not load those files.
`blocksy_child_load_module()` in `inc/loader.php` returns false for any file in
`apply_filters( 'blaze_child/legacy_v1/skip_files', [] )`.

The list is computed by `Registry::skip_files()` **before** v1's loader runs, and before site code loads. So it uses
only the manifest default, site config and the kill switch. The `blaze_child/module_enabled` filter cannot affect it
(R1): a module that replaces v1 files is switched on from `site-config.php`, not from PHP code. Catalogue stubs
(`loadable: false`) never skip anything.

`inc/wishlist-product-card.php` is loaded with `require_once` by `inc/wishlist-offcanvas.php`, not through the
loader; the skip list cannot skip it alone.

## Catalogue

Every other `inc/*.php` and `inc/perf/*.php` file has a stub in `modules/<id>/module.json`
(`status: legacy`, `loadable: false`); `inc/perf/<feature>.php` is `perf-<feature>`.
`tests/core/test-legacy-v1.php` fails when a file has no stub or two.
