# 86eypb6jy: Wishlist header trigger, open into off-canvas panel

**ClickUp Task:** https://app.clickup.com/t/86eypb6jy **ClickUp Doc:** https://app.clickup.com/36771024/v/dc/13256g-171238/13256g-161758 **PR:** https://github.com/blaze-commerce/blaze-blocksy/pull/255 **Branch:** feat/86eypb6jy-wishlist-header-trigger **Status:** PR open, not yet merged

## Objective Give the Bonza (bonza.dog) wishlist off-canvas panel a working header trigger. The panel's own CSS/structure was already built and pixel-verified against Figma in a prior session; this task closes the gap that there was no way to open it on the live site.

## Root cause (config-first re-check corrected the original assumption) Blocksy Companion Pro 2.1.48 already ships a native "Wishlist" header item type, addable in Header Builder. Two real gaps, both confirmed live on bonza-retheme.blz.au:
1. The native item's stock view links to a full-page WooCommerce account endpoint instead of opening `#woo-wishlist-panel`.
2. Companion Pro's own WooCommerce Extra "Wishlist" feature flag had never been turned on for this site (the `blocksy_ext_woocommerce_extra_settings` option was absent from `wp_options` entirely, silently defaulting to off), so the native item was never registered and the panel's preload always showed empty regardless of real cart contents.

## Changes Made
| File | Change |
|---|---|
| `inc/wishlist-offcanvas.php` | New `blocksy:header:item-view-path:wish-list` filter, swaps only the rendered view for the native item. |
| `partials/wishlist-header-offcanvas-trigger.php` | New file. Near copy of Companion Pro's own view, with the anchor pointed at `#woo-wishlist-panel` and `ct-offcanvas-trigger` added, same pattern as the theme's own Cart item in its offcanvas drawer mode. |
| `CHANGELOG.md` | Entry recording both the code change and the config root cause found while verifying. |

Config only, applied directly to staging via wp-cli (not part of the code diff):
- Enabled the Wishlist feature flag via `update_option()` (`wp_options` backup taken first).
- Placed the `wish-list` item into `header_placements`: desktop `search, account, wishlist, cart`; mobile `search, wishlist, cart, trigger`.

## Screenshots No 21-shot breakpoint x engine matrix this session, see Known Limitations. Real evidence captured: `curl` confirmation of the rendered markup, and a live Playwright click-to-open screenshot (`bonza-wishlist-panel-element.png`) showing the actual off-canvas panel opening on chromium.

## Rollback Plan Revert the two file changes in this PR (or don't merge it). On staging, the config changes can be reverted by restoring `blocksy_ext_woocommerce_extra_settings.features.wishlist` to `false` and removing `wish-list` from the `header_placements` theme_mod (backups of both were taken before changing them, under `/tmp/bonza-*` on the staging host).

## Known Limitations
| Limitation | Detail |
|---|---|
| PR not merged | Nothing here is on `main` yet. |
| Production not touched | Both config steps (feature flag, header placement) need repeating on production once the PR merges and ships. |
| No full breakpoint x engine screenshot proof this session | Wiring/config fix reusing the theme's existing Cart-item offcanvas pattern and Companion Pro's own stock icon markup, no new CSS surface. The drawer's own CSS/layout was already pixel-verified against Figma in the prior session (see the wishlist-drawer-figma-verify doc), unchanged by this fix. |
| Icon placement order | Placed by convention (search, account, wishlist, cart / search, wishlist, cart, trigger). The Figma-cited header frame node IDs returned not-found via the Figma API this session, so exact icon order against Figma is worth a quick visual confirmation. |

## Related Links
- [Task](https://app.clickup.com/t/86eypb6jy)
- [Claude Documentation](https://app.clickup.com/36771024/v/dc/13256g-171238/13256g-161758)
- [Pull Request](https://github.com/blaze-commerce/blaze-blocksy/pull/255)
- [Branch](https://github.com/blaze-commerce/blaze-blocksy/tree/feat/86eypb6jy-wishlist-header-trigger)
