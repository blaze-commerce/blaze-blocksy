# 86ey0r46n — Wishlist off-canvas (slide-in) panel → match Figma

**ClickUp:** https://app.clickup.com/t/86ey0r46n (in-progress · Lan · Alternate Worlds) **Staging:** https://aworld-retheme.blz.au · **Started:** 2026-06-30 · **Status:** In Progress **Plan:** ~/.claude-dev-blazecommerce-io/plans/https-app-clickup-com-t-36771024-86ey0r4-jaunty-beacon.md **Final step (user):** run `/figma-staging-audit` against the staging drawer once fidelity is done.

## Objective Refine the existing wishlist off-canvas drawer (`#woo-wishlist-panel`) to match Figma across desktop/tablet/mobile, then audit. Not from-scratch — the drawer exists and **already opens** on staging.

## Environment & architecture (verified 2026-06-30)
- Active theme **`blocksy-child`** (parent `blocksy`), WP 7.0 / Woo 10.8.1. Theme dir `…/themes/blocksy-child/`. Deployed **v1.1.46** (origin/main = **1.1.48** — staging slightly behind; drawer files are current though).
- **Drawer core = `blaze-blocksy`** repo (`github.com/blaze-commerce/blaze-blocksy`): `inc/wishlist-offcanvas.php`, `assets/js/wishlist-offcanvas.js`, `assets/css/components/wishlist-offcanvas.css` (+ shared `offcanvas.css`). Deploy = **SSH-rsync/scp** to the theme dir + `touch inc/enqueue.php`.
- **AW overlay = `bc-site-customizations` → `sites/alternateworlds/custom/`** — deploys INTO `blocksy-child/custom/` (loaded via `functions.php` → `custom/custom.php`). Confirmed live: `custom/css/design-tokens.css|product-card.css|slider.css` (ver 1782790908) are enqueued. Deploy = PR + **`ready-for-qa-v1`** label (CI). Also `clients/alternateworlds/alternateworlds.css` loads (blaze-blocksy client system).
- **Decision:** AW-specific Figma fidelity (CSS + AW copy/category-grid) → **AW overlay** (`sites/alternateworlds/custom/css/wishlist-offcanvas.css`), no Byron Bay regression. Touch `blaze-blocksy` core only for unavoidable structural/JS changes.

## Worktrees
- blaze-blocksy core: `/Users/alanregaya/Documents/.work/.blz/.worktrees/blaze-blocksy-wishlist-1782792038` (branch `feat/wishlist-offcanvas-figma-86ey0r46n`, off origin/main `0da9e7f`).
- AW overlay (bc-site-customizations): TBD — `bc-sc-wt-86ey0r46n` (to create).

## Current drawer behavior (live, verified)
- Header `.ct-header-wishlist` `<a href=".../my-account/woo-wish-list/">` with `aria-controls=woo-wishlist-panel`. Click → **drawer OPENS** (capture-phase listener in wishlist-offcanvas.js; `panelActive:true`, no navigation). Task's "routes to full page" note is **outdated**. ESC + click-outside close work.
- Empty state renders: `.ct-wishlist-empty` "Your Wishlist is Empty" + `.ct-wishlist-guest-notice` (7-day copy, matches Figma) + `.ct-wishlist-signup-btn` "Sign Up". Suggested = Blocksy carousel "SUGGESTED PRODUCTS".
- Item template (JS): `.ct-wishlist-item` = image link + `.ct-wishlist-item-info` (title link + `.price` = `get_price_html()` incl. sale del/ins) + `.ct-wishlist-remove` icon button.
- Panel width `--side-panel-width` mirrored from cart Customizer (`header_placements` → cart `cart_panel_width`); default desktop 500 / tablet 65vw / mobile 90vw (Figma: desktop **451**, mobile **346**).

## Figma deltas (empty state, desktop — screenshots in scratchpad)
1. Title "Wishlist" → **"WISHLIST"** uppercase bold (`.ct-wishlist-panel-title` + count). [CSS]
2. Close X → **X in bordered square** (`.ct-toggle-close` border). [CSS]
3. "Your Wishlist is Empty" left → **centered**, Figma weight/size. [CSS]
4. Guest notice **filled blue box → no box**, plain centered copy. [CSS]
5. CTA blue "SIGN UP" → **outlined "REGISTER"** (white bg, dark/orange border, dark text). [label + CSS]
6. Suggested "SUGGESTED PRODUCTS" carousel → **"You May Also Like" 2×2 category grid** (COMICS/COLLECTABLES/GAMES/TOYS + counts) for EMPTY; product cards for FILLED. [heading + grid — check `woo-category-grid.css` / homepage `category-cards` block for reuse]

## Figma tokens (re-extract exact per BP at build) Primary **Barlow Semi Condensed**, secondary **Inter**; title H6 20/24/700; body/links 14; text `#343437`, sale `#cb2f00`, blue `#02478f`, border `#d1d9e6`, secondary `#575757`; radius-xs 2; product-card min-w 140. Blocksy mobile BP **690px**.

## Figma nodes Desktop filled `30639-224597` / empty `21316-76971`; Tablet `23310-35577` / `23310-35575`; Mobile `2138-116628` / `2138-116569`.

## Decision (final): ALL changes in the AW overlay (bc-site-customizations), single PR + `ready-for-qa-v1` deploy Reverted the core blaze-blocksy JS edit. The "REGISTER" label is done via a tiny AW overlay JS relabel (MutationObserver on the drawer), so the shared core (and Byron Bay) is untouched. Deploy = `bash deploy.sh --site alternateworlds --env v1` from the overlay worktree (needs `ssh-add ~/.ssh/impactfurniture_ed25519` first — deploy.sh connects by IP, not the alias). Host is GCP (not *.kinsta.cloud) so no Kinsta edge cache; cf DYNAMIC / kinsta BYPASS.

## Change Log
| # | Timestamp | File | Action | Description |
|---|-----------|------|--------|-------------|
| 1 | 2026-06-30 | sites/alternateworlds/custom/css/wishlist-offcanvas.css | Created | Header uppercase/700, bordered-X close, centered empty msg, de-boxed guest notice, outlined REGISTER, filled-item title/sale-price colours. All scoped `#woo-wishlist-panel`. |
| 2 | 2026-06-30 | sites/alternateworlds/custom/js/wishlist-offcanvas.js | Created | Relabel guest CTA "Sign Up"→"REGISTER" (overlay, not core). |
| 3 | 2026-06-30 | sites/alternateworlds/custom/custom.php | Modified | Enqueue the new wishlist css + js (global). |
| 4 | 2026-06-30 | (deploy) | — | `deploy.sh --site alternateworlds --env v1` → staging. Verified both overlay assets load after core; CTA="REGISTER"; title uppercases. |

## First-pass result (verified on staging, desktop) DONE vs Figma: WISHLIST uppercase ✓, bordered-X close ✓, de-boxed guest notice ✓, outlined REGISTER ✓. REMAINING (for /figma-staging-audit): empty-message **centering not applied** (still left); panel **width 451px** (currently ~500 from cart Customizer); filled-item spacing/badge; **"You May Also Like" heading + category grid** (delta 6 — reuse `woo-category-grid.css` via `[product_categories]`); full **7-BP responsive** (320–2560 + 690).

## Round 2 — structural parity (user chose "build full structural parity now") The audit surfaced two structural deltas beyond CSS fidelity: filled items as a **2-col card grid** and the empty state as a **category grid**. These need shared-core markup/JS (image size, item template, category render), so they went into `blaze-blocksy` core behind a **per-site filter** (`blocksy_child_wishlist_card_layout`, default OFF) — Byron Bay renders byte-for-byte as before. AW opts in + styles in the overlay.

### Core (`blaze-blocksy`, branch `feat/wishlist-offcanvas-figma-86ey0r46n`, commit `1f41cfe`, theme 1.1.48→1.1.49)
- `inc/wishlist-offcanvas.php`: `blocksy_child_wishlist_uses_cards()` gate; `blocksy_child_wishlist_image_size()` → `woocommerce_single` (portrait) when on; `cardLayout` in `bcWishlistData`; `blocksy_child_render_wishlist_categories()` (empty-state grid: top-level cats with a featured term image, live names+counts, `blocksy_child_wishlist_category_ids` filterable); panel gets `ct-wishlist-cards` + server-computed `ct-wishlist-state-empty|filled`.
- `assets/js/wishlist-offcanvas.js`: card item template (`ct-wishlist-items--cards`, "Remove" text control); `setPanelEmptyState()` toggles category-grid vs suggested-carousel; card flag read at RENDER time via `usesCards()` (preload `<script>` prints after this footer script — init-time read returned false; the real bug fix).
- Deploy = scp 2 files to `blocksy-child/{inc,assets/js}/` (filemtime-versioned).

### Overlay (`bc-site-customizations` → `sites/alternateworlds/custom/`, commits `5a30d23`+`9c0af11`, merged `origin/main`)
- `custom.php`: `add_filter('blocksy_child_wishlist_card_layout','__return_true')`.
- `css/wishlist-offcanvas.css` §10–§13: card grid, REMOVE link, category grid, empty/filled state toggle (+ empty-state content sizes to content so the REGISTER button isn't clipped by the category grid's flex height).

### Verified on staging (desktop 1440 + mobile 375, both states)
- Filled: 2-col grid, portrait `woocommerce_single` covers (204×300 from srcset), 2-line title clamp, sale prices, "REMOVE" underlined link; no horizontal overflow (panel 451/345px).
- Empty: centered "Your Wishlist is Empty" + 7-day notice + outlined REGISTER + "You May Also Like" 2-col category grid (COMICS 158,172 / GAMES 7,851 / TOYS·NOVELTIES 1,326 — 3 of Figma's 4; auto-completes to 2×2 when the client adds a top-level Collectables cat + thumbnail).
- Byron Bay safety: every core change behind the filter (static-reviewed diff); flag-off path is the original list-row drawer.

### Deferred / notes
- "Ordered N days ago" image badge — Figma scaffold, no wishlist data source; omitted pending a product decision.
- **Shared-staging contention:** v1 (`aworld-retheme.blz.au`) is shared; another active PR (task `86exr72z6`, minicart) deploys its branch there too and clobbered the wishlist files mid-session (its tree lacks them, deploy used `--delete`). Reconciled to my branch state (`origin/main` had already removed minicart in `86exr72yj`). Final QA needs a coordinated single-PR label-deploy; the real fix is both PRs merging to `main`.

## Next steps
1. PRs: `blaze-blocksy` (core) + `bc-site-customizations` (overlay) → `ready-for-qa-v1`.
2. ClickUp Doc + comment + before/after composites.
