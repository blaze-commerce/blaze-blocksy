# Migrating a site from v1 to v2

Outline only. This covers phases 2 and 3 of the
[plan](https://github.com/blaze-commerce/blaze-swamp/blob/jayr/blocksy-child-v2/docs/superpowers/specs/2026-11-01-blocksy-child-v2-design.md)
(section 5). The procedure gets filled in as the first site goes through it. Phase 0 and 1 touch no site.

## Rules that never bend

- **Never upload v2 onto a v1 site with "replace current".** WordPress deletes the old theme folder first, and with
  it everything inside it: `custom/`, `clients/`, hand edits. Nothing in phase 0 refuses such an upload. Phase 1
  plans an update guard that refuses a theme folder with extra files, and even then it will protect v2 sites only.
  A v1 site moves through the steps below, never through a plain theme upload.
- **Site code moves out of the theme.** It lives in `wp-content/blaze-site/` (`site.php` and `site-config.php`), which
  a theme update never touches. v1's `custom/custom.php` still loads while the move is under way, and the health log
  says so (`legacy_custom_in_theme`).
- **`site-config.php` is generated, not hand-written.** It comes from the `site_scan` result for that site, so the
  list of modules and settings starts from what the site actually runs. Review the generated file; don't type one
  from memory.
- **Staging first, with a visual check.** Before `recently-viewed` is enabled on a site, compare its product page on
  staging against the live site (R23). The cards are rendered in the browser as a light grid instead of v1's slider,
  so the look can differ.
- **LIVE only with Jayr's go.** Every change that reaches a live site is named and approved first. Nothing here
  deploys on its own.

## Phase 2: the site folder and the generated config

1. Take a Kinsta restore point of the target environment (tag it `before-<change>-claude`).
2. Run `site_scan` against the site: theme version, active plugins, v1 `custom/` contents, constants and filters in
   use, PHP and WordPress versions against the floors (PHP 8.1, WordPress 6.6, Blocksy 2.1).
3. Generate `site-config.php` from the scan: every module the site relies on, set explicitly. `legacy-v1` is on, so
   the site renders as before.
4. Move `custom/` code into `wp-content/blaze-site/site.php`. Keep client-specific behaviour there, not in the theme.
5. Deploy `blaze-site/` to staging through the site-customizations repo. Merging that repo's `main` deploys to LIVE,
   so a phase-2 change needs Jayr's go, or a staging-only path.
6. Check the health log (`blaze_child_health`) for `site_config_invalid`, `unknown_module`, `requires_unmet`,
   `legacy_custom_ignored`.

## Phase 3: moving the theme

1. On staging, install v2 as a new theme version through the supported path (not "replace current" on a v1 site).
2. Confirm the BLAZE_BLOCKSY_* shims cover the site: v1 `BLOCKSY_CHILD_*` and the older `BLAZE_BLOCKSY_URL` /
   `BLAZE_BLOCKSY_PATH` constants are defined by the theme only when the site has not defined them already. Search
   the site's own code for other v1 names and list any that are missing.
3. Compare staging against live: home, a category, a product page, cart, checkout (up to payment), account.
4. Enable ported modules one at a time in `site-config.php` (`hide-cart-page`, `offcanvas-icons`, then
   `recently-viewed` after its visual check). Each one makes `legacy-v1` skip the v1 file it replaces.
5. Check the health log again, in particular `legacy_files_orphaned` and `checkout_closed`.
6. LIVE: only with Jayr's go. Take a restore point first. After the move, watch the health log and a test order's
   path up to payment.
7. Rollback: restore the Kinsta restore point, or set the module's entry to `false` in `site-config.php` (v1 loads
   its file again).

Legacy-line sites (the ones still on the older theme line) go last, after the shims have been proven on the sites
above.
