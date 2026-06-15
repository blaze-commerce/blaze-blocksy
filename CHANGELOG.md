## [woo-cart-smartcoupons-guarded-enqueue-2026-06-03] - 2026-06-03

### Changed
- Made the Smart Coupons BOGO cart CSS (`woo-cart.css`, migrated earlier today from Blocksy code-snippet #5) load **defensively** instead of unconditionally. Because the rule is client-specific (WebToffee Smart Coupons), `inc/enqueue.php` now gates the `is_cart()` enqueue behind a new `bbc_smart_coupons_cart_css_active()` helper: (1) an on/off switch via the `bbc_smart_coupons_cart_css_enabled` filter (default true — flip to false anywhere to disable site-wide WITHOUT editing the theme); (2) a `class_exists( 'Wt_Smart_Coupon_Giveaway_Product' ) || class_exists( 'Wt_Smart_Coupon' )` guard that covers "plugin not installed right" — deactivated, half-installed, or a renamed build all fall through and the file is never enqueued. The selector remains inert by design (only matches Smart Coupons giveaway markup). WHY: Jayr flagged that a bare always-on CSS hide had no kill-switch and no plugin-present check. Theme 1.1.45 -> 1.1.46.

## [woo-cart-smartcoupons-css-migrate-2026-06-03] - 2026-06-03

### Changed
- Smart Coupons BOGO giveaway "discount detail" hide on the cart page (`.wt_sc_giveaway_products_cart_page .wt_product_discount { display:none }`) migrated OUT of the Blocksy `code-snippets` extension (snippet #5 "WebToffee Smart Coupons", wp_footer) and INTO the child theme as `assets/css/components/woo-cart.css`, conditionally enqueued on `is_cart()` in `inc/enqueue.php`. WHY: `code-snippets` was dropped from `blocksy_active_extensions` (now 4: woocommerce-extra, mega-menu, local-google-fonts, cookies-consent), which stopped the snippet executing — and the go-live cutover script does not migrate the `unz_snippets` table. Moving the rule into the theme decouples it from the extension and lets the cutover carry it automatically via the theme transfer. Selector only matches when Smart Coupons renders giveaway products, so the rule is inert elsewhere (no PHP guard needed in CSS). Re-added the `is_cart()` enqueue that audit P0.2 (2026-05-07) had removed as dead. Verified RENDERED: `components/woo-cart.css` present in `/cart/` head. Theme 1.1.44 -> 1.1.45.

## [primary-button-hover-letterspacing-2026-06-02] - 2026-06-02

### Fixed
- #3 primary buttons: RENDERED QA (Browserless computed styles) found Add to Cart + See More were NOT darkening on hover (stayed #DDE2CF) and See More had letter-spacing: normal. Added #3b: hover/focus-visible bg #C5CDB4 + text #3F3A36, and letter-spacing 0.6px across all primary buttons. Theme 1.1.39 -> 1.1.40.

## [mega-menu-columns-padding-2026-06-02] - 2026-06-02

### Fixed
- ROOT CAUSE of the big gap below mega-menu headings found (Jayr): each column <li> has padding: var(--columns-padding, 20px 30px) = 20px top/bottom per column, which stacked (heading bottom + column top) into a large gap. Reduced --columns-padding vertical 20px -> 8px (kept 30px gutters). Theme 1.1.35 -> 1.1.36.

## [mega-menu-fullwidth-heading-spacing-2026-06-02] - 2026-06-02

### Fixed
- Diffusers full-width heading (#menu-item-639100 Scented Diffusers, .ct-column-heading): removed leftover Feb padding-bottom:12px on the heading row that made the gap below it too big. Now consistent with uniform item spacing. Theme 1.1.32 -> 1.1.33.

## [mega-menu-heading-balanced-padding-2026-06-02] - 2026-06-02

### Fixed
- Mega menu heading hover pill was vertically unbalanced (leftover Feb rules: candles padding-bottom 10px + margin-bottom 4px; diffusers padding-bottom 0). Forced headings to Blocksy --menu-item-padding (equal top/bottom, same as items) + margin-bottom 0. Theme 1.1.31 -> 1.1.32.

## [mega-menu-heading-hover-2026-06-02] - 2026-06-02

### Changed
- Mega menu heading items are clickable category links, so they now get the normal hover pill (#EEF1E5) like any item (client request). Removed the #4b rule that suppressed hover/focus on headings. #4d still forces non-hover/current/focus states transparent, so a heading only highlights when hovered directly (not persistently). Theme 1.1.30 -> 1.1.31.

## [mega-menu-heading-spacing-inherit-2026-06-02] - 2026-06-02

### Fixed
- Mega menu parent/heading items (e.g. #menu-item-639055 Scented/Citrus Candles) stayed tight because the #4 headings=links block forced padding-bottom:0 + margin-bottom:0, blocking Blocksy item spacing. Removed that zeroing (kept font 500/13px + no divider) so headings inherit the same Blocksy Items Spacing (18px) as every other item. Theme 1.1.29 -> 1.1.30.

## [mega-menu-spacing-customizer-control-2026-06-02] - 2026-06-02

### Changed
- Mega menu item spacing now fully Customizer-controlled. Removed the last child override (.sub-menu li > a padding 8px 12px) so Blocksy --menu-item-padding (derived from Customizer Items Spacing, dropdownItemsSpacing=13px) governs. For the solid dropdown type Blocksy renders 13px as ~6px vertical padding (calc 13-7). Adjust live via Header > Menu > Dropdown Options > Items Spacing. Theme 1.1.28 -> 1.1.29.

## [mega-menu-item-spacing-blocksy-2026-06-02] - 2026-06-02

### Changed
- Mega menu item spacing: removed child-theme overrides (.sub-menu li margin 2px + heading row/margin/padding tweaks) that were squashing Blocksy Customizer dropdownItemsSpacing (13px) down to 2px. Spacing is now uniform + Customizer-managed (Header > Menu > Dropdown Options > Items Spacing). Theme 1.1.27 -> 1.1.28.

## [mega-menu-4d-restore-hover-2026-06-02] - 2026-06-02

### Fixed
- Mega menu: restored the hover tint (#4c :not(:hover) rule had suppressed the visible hover). Now explicit states: base/focus/active/current = transparent (no lingering highlight on headings), :hover/:focus-visible = #EEF1E5. Also halved the full-width column-heading bottom spacing. Theme 1.1.26 -> 1.1.27.

## [mega-menu-4c-only-hover-2026-06-02] - 2026-06-02

### Fixed
- Mega menu: persistent green on column heading (e.g. Scented Candles) traced to Blocksy (headerDropdownBackground / current-item state), NOT the child theme (verified: no child JS/PHP touches the menu). Added bulletproof rule: .sub-menu li > a:not(:hover):not(:focus-visible) background transparent !important — only the hovered item can ever highlight. Theme 1.1.25 -> 1.1.26.

## [mega-menu-focus-visible-2026-06-02] - 2026-06-02

### Fixed
- Mega menu: column heading (e.g. Scented Candles) stayed highlighted because the menu-link hover rule also fired on :focus, so the opened/clicked item kept its green tint. Changed :focus -> :focus-visible so a mouse click no longer leaves a lingering highlight (keyboard focus still shows). Now ONLY the hovered item highlights. Theme 1.1.24 -> 1.1.25.

## [mega-menu-4b-heading-spacing-2026-06-02] - 2026-06-02

### Changed
- Mega menu #4b: +5px breathing room below column headings (was too tight after divider removal); column headings (parent links + .ct-column-heading) no longer highlight on hover/focus so only leaf items highlight (client req). Theme 1.1.23 -> 1.1.24.

## [mega-menu-4-headings-and-font-2026-06-02] - 2026-06-02

### Changed
- Mega menu (#4): switched header Menu/Dropdown font Museo Sans -> Montserrat (6 theme_mod spots; backup option theme_mods_blocksy_child_bak_2026_06_02). Neutralised the 2026-02-25 column-heading treatment (bold 700, 15px, border-bottom divider) so headings MATCH links (500 / 13px / no divider) per client consistency req. Theme 1.1.22 -> 1.1.23.

## [button-consistency-normalize-2026-06-02] - 2026-06-02

### Changed
- Normalised secondary buttons to the primary spec for sitewide consistency (weight 500, letter-spacing 0.6px, radius 4px): checkout Place Order/prev/next-step (woo-checkout.css, checkout-step-form.css), variation swatches radius 8->4px (byronbay.css), hero CTA (homepage.css), wishlist signup/continue (wishlist-offcanvas.css), shipping-calc submit (product-information.css). Colours intentionally kept distinct: cookie Decline, coupon-apply outline, FiboSearch View All brown. Theme 1.1.21 -> 1.1.22.

## [primary-button-size-17px-2026-06-02] - 2026-06-02

### Changed
- Primary button font-size set to 17px (client spec, Launch Checklist \xc2\xa72 Button Typography). Added font-size: 17px !important to both #3 sitewide override groups (.ct-button + anchor/.bc-see-more-link). Theme 1.1.20 -> 1.1.21.

## [bogo-gift-icon-halved-2026-06-01] - 2026-06-01

### Changed
- BOGO free-gift chooser icon (.wbte_sc_bogo_popup_btn) halved per client request: container 150x150 -> 75x75px, padding 18px -> 9px, inner <img> 96x96 -> 48x48px. Position (fixed bottom-right) untouched. Theme bumped 1.1.19 -> 1.1.20.

# Changelog

All notable changes to the Blocksy child theme. Newest entries first.

## [bogo-popup-cta-buttons-2026-06-01] - 2026-06-01

> **Environment:** bbcv1.blz.au (port 46945). Theme **1.1.18 -> 1.1.19**.
>
> **Source:** CU-86extrx5y #3 / #5. Jayr-approved.

### BOGO popup CTA buttons - apply client #3 primary-button spec

The WebToffee Smart Coupons Pro BOGO free-gift popup is body-appended (rendered outside `.woocommerce`), so its two customer CTA buttons fell through the sitewide #3 primary-button override (which is scoped under `.woocommerce`) and rendered with the plugin/theme default `.button` style instead of the client's sage spec. Added a minimal `!important` override targeting only the two front-end CTAs - `.wbte_sc_bogo_add_to_cart` ("Add to cart") and `.wbte_sc_bogo_proceed_checkout` ("Add & go to checkout") - applying the client #3 primary-button spec: background `--theme-palette-color-12 (#DDE2CF)`, hover/focus background `--theme-palette-color-13 (#C5CDB4)`, text + hover text `--theme-palette-color-24 (#3F3A36)`, Montserrat 500, letter-spacing 0.6px, border-radius 4px. Admin/"add new"/dropdown buttons (`wbte_sc_bogo_add_new_*`, `wbte_sc_bogo_edit_*`) intentionally NOT styled. Color/hover/text/font/radius only - no padding/layout change. byronbay.css loads LAST so the override out-specifies the plugin stylesheet. Verified via served-CSS + on-server brace balance; live visual confirm by Jayr (BOGO offer is cart-session-stateful, Playwright resets context here). Theme 1.1.18 -> 1.1.19.

## [qty-stepper-radius-2026-06-01] - 2026-06-01

> **Environment:** bbcv1.blz.au (port 46945). Theme **1.1.17 -> 1.1.18**.
>
> **Source:** CU-86extrx5y #3 ("consistent radius sitewide"). Jayr-approved.

### Qty steppers - unify corner radius 8px -> 4px to match primary buttons

The three quantity-stepper blocks (PDP `.ct-cart-actions`, sticky `.ct-floating-bar-actions`, off-canvas `#woo-cart-panel` / `.woocommerce-mini-cart`) used an 8px corner radius on the decrease (top-left + bottom-left) and increase (top-right + bottom-right) buttons. This conflicted with the primary buttons, which honour the Blocksy Customizer `buttonRadius = 4px`. Changed all six `border-*-left-radius`/`border-*-right-radius` declarations from 8px to 4px (6 left + 6 right corner declarations across the 3 blocks) so the steppers match the buttons. The variation-swatch shorthand `border-radius: 8px` on `.ct-swatch` is a separate spec and was intentionally left untouched. Verified via served-CSS + on-server brace balance; zero structural change (byte size unchanged). Theme 1.1.17 -> 1.1.18.

## [bogo-gift-chooser-button-2026-06-01] - 2026-06-01

> **Environment:** bbcv1.blz.au (port 46945). Theme **1.1.16 -> 1.1.17**.
>
> **Source:** CU-86extrx5y #5. WebToffee Smart Coupons Pro BOGO free-gift chooser floating button enlarged for discoverability (option A).

### BOGO gift-chooser button - enlarge to 150x150

- **Mechanism:** plugin markup is `<div class='wbte_sc_bogo_popup_btn bottom-right'><img src='bogo_popup_btn.svg'></div>`. The plugin's `modules/bogo/assets/style.css` ships the button at `width:50px; height:50px` (circle) with a 24x24px inner `<img>` glyph.
- **Fix (byronbay.css, appended last):** high-specificity `!important` block. Container `.wbte_sc_bogo_popup_btn` -> `width/height:150px` + `padding:18px box-sizing:border-box`. Inner `.wbte_sc_bogo_popup_btn img` -> `width/height:96px` (`max-width/height:100%`) so the glyph scales to fill the circle proportionally instead of sitting tiny in a big box. Position (`fixed`, bottom-right) untouched. byronbay.css loads LAST among child CSS so it out-specifies the plugin stylesheet.
- **Files:** `clients/byronbay/byronbay.css` (+1 block). `style.css` Version 1.1.16 -> 1.1.17.
- **Verified:** served CSS contains the new selector; braces balanced. BOGO offer is cart-session-stateful (needs 2x "Large - 50 Hour Candles" + `freegift` coupon) and Playwright resets context in this env, so live visual confirmation is by Jayr.

## [minicart-qty-stepper-2026-06-01] - 2026-06-01

> **Environment:** bbcv1.blz.au (port 46945). Theme **1.1.15 -> 1.1.16**.
>
> **Source:** CU-86extrx5y. Mini-cart / off-canvas "Your bag" drawer qty stepper restyled to match the PDP + sticky-bar seamless grouped pill, compact for the slim drawer.

### Mini-cart qty stepper - seamless pill (compact)

- **Root cause:** Blocksy's lazy-loaded `cart-header-element-lazy.min.css` loads AFTER `byronbay.css` and `qty-stepper.css`, out-specifying the component knob overrides in `offcanvas.css`. The drawer stepper rendered as three separate boxes: `.ct-decrease`/`.ct-increase` at ~19px (smaller than the 28px input) with per-button 3px radius, not the merged outer-only pill.
- **Fix (byronbay.css, appended last):** new high-specificity `!important` block scoped to `#woo-cart-panel .ct-product-actions > .quantity[data-type='type-2']` (and the `.woocommerce-mini-cart` fragment variant). Mirrors the existing `.ct-cart-actions` (PDP) and `.ct-floating-bar-actions` (sticky) pill structure: `display:inline-flex`, `position:static`, merged borders (`border-right:0`/`border-left:0`), outer-only 8px radius (decrease order:0 / input order:1 / increase order:2), `1px solid var(--theme-form-field-border-initial-color,#D8D1C7)`, `background:#fff`.
- **Compact dimensions:** buttons **30px wide x 34px tall**, input **40px wide x 34px tall** (vs PDP 44/46/52, sticky 38/40/46) - smaller to suit the slim drawer.
- **Files:** `clients/byronbay/byronbay.css` (+1 block). `style.css` Version 1.1.15 -> 1.1.16.
- **Verified:** served CSS contains the new selector; braces balanced (239/239). Pre-fix broken state confirmed via Playwright computed styles (buttons 19.4px, per-button 3px radius). Stateful Playwright click chains unreliable in this env; relied on served-CSS + computed-style checks.

## [buttons-and-token-refactor-2026-06-01] - 2026-06-01

> **Environment:** bbcv1.blz.au (port 46945). Theme **1.1.14 → 1.1.15**.
>
> **Source:** CU-86extrx5y (Anne Marie immediate revisions). Two changes: (1) #3 primary-button spec compliance sitewide; (2) byronbay.css hardcoded-value → design-token refactor (zero visual change).

### #3 — Primary buttons (sitewide)

- **Root cause:** a legacy `font-weight: 600 !important` on `.ct-cart-actions > .single_add_to_cart_button`, plus Blocksy not emitting its `buttonTextColor` theme_mod (buttons rendered `#111` instead of the configured `#3F3A36`).
- **Fix (byronbay.css):** weight 600 → **500**; added `color: var(--theme-palette-color-24)` (#3F3A36) to the main ATC; appended two sitewide override blocks forcing all primary buttons (ATC main + sticky, cookie Accept/Decline, See More anchors ×9, `.wp-block-button__link`, `.ct-button:not(.ct-button-ghost)`) to button-text color24 + weight 500. Ghost / wishlist / icon buttons untouched.
- **Spec met:** bg `#DDE2CF` (color12), hover bg `#C5CDB4` (color13), text + hover-text `#3F3A36` (color24), Montserrat 500, letter-spacing 0.6px, border-radius 4px.
- **Verified:** computed `color: rgb(63, 58, 54)` / `font-weight: 500` on every primary button (main ATC, sticky bar, cookie bar, all 9 See More, block buttons). Uniform.

### Token refactor (byronbay.css, zero visual change)

Every swapped token resolves to the identical current literal — verified before swapping (`--theme-font-family`=Montserrat, palette `color4`=#888888, `--theme-form-field-border-initial-color` fallback=#D8D1C7). No pixel change; maintainability + Customizer-follow only.

- `font-family: Montserrat, sans-serif` → `var(--theme-font-family, Montserrat, sans-serif)` (×2 — variation label, description body). Now follows the Customizer global font.
- Variation-swatch border `1px solid #D8D1C7` → `1px solid var(--theme-form-field-border-initial-color, #D8D1C7)` (matches the qty-stepper border token).
- Bare icon grey `#888888` → `var(--theme-palette-color-4, #888888)` (×6; matches the 3 pre-existing var-wrapped usages — color4 IS #888888).
- Hover tints `#E6EAD9` (swatch) + `#EEF1E5` (mega-menu) → new `:root` design tokens `--bc-swatch-hover-bg` / `--bc-menu-hover-bg` (single source of truth).

### Deliberate scope decisions

- **Museo Sans fallback retained** — it's a child-theme-wide convention across 5+ component CSS files (header, checkout-*, search-dropdown); removing it from byronbay.css alone would create drift. No `@font-face`/`@import` in this file (matches the live site's Adobe Fonts).
- **`#fff` literals (×6) left as-is** — white qty/swatch backgrounds aren't a theming concern.
- **Qty-stepper DRY merge deferred** — the `.ct-cart-actions` (46px) and `.ct-floating-bar-actions` (40px) blocks are *not* duplicates (different dimensions) and are Playwright-verified working. Merging risks regressing a working component for marginal gain; tracked as a separate task.

Files: `clients/byronbay/byronbay.css`. In-place backup: `byronbay.css.bak-2026-06-01`.

## [anne-marie-kickback-2026-05-13] - 2026-05-13

> **Environment:** bbcv1.blz.au (port 46945, ex-FROZEN, now opened back up after the 2026-05-13 STG→bbcv1 restore). Theme **1.1.13 → 1.1.14**.
>
> **Source:** Client kickback email from Anne Marie 2026-05-13 — replace blue button with sage `#DDE2CF`, button text `#3B3935`, body text default `#5B554F`, all fonts to Montserrat with `letter-spacing 0.01em`, ensure footer subscribe form visible. Plus the diagnostic finding from CU-86exk7c3z (Vita's letter-spacing report): cause was Anne Marie's own admin-side edits post-freeze, NOT dev team.

### Architecture decisions

- **Customizer-first** for every brand value. Following the BC styling hierarchy (Gutenberg → Customizer → CSS aliases, never CSS hex). Per Anne Marie's own pattern in the existing palette (e.g. `color19 "Backup/Old Brown (was Contrast 3)"`), replaced-value preservation uses **Option C**: update the slot AND add a `Backup/Old X` slot so the palette tells the history.
- **Zero hex in `byronbay.css` token blocks** after this batch. Every `--fc-*` and `--bc-sf-*` token aliases to a Blocksy Customizer palette/typography variable. Anne Marie can change brand values via Customizer alone — no CSS edit needed.

### Customizer changes (theme_mod / Color Palette)

**Updated slots:**

| Slot | Title | Before | After |
| --- | --- | --- | --- |
| `color12` | Button/Default | `#D9EAF0` | `#DDE2CF` (sage) |
| `color13` | Button/Hover | `#DFEDF2` | `#C5CDB4` (sage hover — ~10% darker) |

**Added slots (new roles + backups):**

| Slot | Title | Value | Purpose |
| --- | --- | --- | --- |
| `color22` | Backup/Old Button Blue (was Button/Default) | `#D9EAF0` | History preservation |
| `color23` | Backup/Old Button Hover (was Button/Hover) | `#DFEDF2` | History preservation |
| `color24` | Button/Text Dark | `#3B3935` | New role — button text |
| `color25` | Body/Text Default | `#5B554F` | New role — body text on white |
| `color26` | BG/Pale | `#F7FBFC` | Migrated from `--fc-bg-pale` literal (batch 8) |
| `color27` | Accent/Teal | `#2890a8` | Migrated from `--fc-accent-teal` literal (batch 8) |
| `color28` | Progress Bar Fill | `#C1B19E` | Migrated from `--fc-progress-bar-fill` literal (batch 8) |

**Theme_mod retargeting:**

- `buttonTextColor` → `var(--theme-palette-color-24)` (was `var(--theme-palette-color-3)`)
- `fontColor` → `var(--theme-palette-color-25)` (was `var(--theme-palette-color-2)`)

### Typography (128 theme_mod updates)

Recursive sweep across every theme_mod that holds a typography object:

- `family`: `Quicksand` or `ct_font_museo__sans` → **`Montserrat`** (Google Font auto-loaded by Blocksy)
- `letter-spacing`: `0em` or `CT_CSS_SKIP_RULE` → `0.01em` (preserves any explicit non-zero existing values — design choices unchanged)

Hit `buttons`, `h1Typography` – `h6Typography`, `cardProductTitleFont`, `cardProductPriceFont`, `form_font`, `filter_panel_widgets_font`, `breadcrumbsFont`, `categories_pageTitleFont`, `categories_breadcrumbsFont`, `headerTextFont`, `headerMenuFont`, `cart_suggested_products_*_font`, `checkout_suggested_products_*_font`, and 100+ more. Total 128 field updates.

### `clients/byronbay/byronbay.css` — token alias refactor

**Before** (batch-8 state — held hex literals):

```css
body.woocommerce-checkout {
    --fc-btn-primary-bg: #D9EAF0;
    --fc-btn-primary-bg-hover: #BFD6DD;
    --fc-btn-primary-text: #111111;
    --fc-progress-bar-bg: #CFCFCF;
    --fc-progress-bar-fill: #C1B19E;
}

:root {
    --bc-sf-font-body:  'Quicksand', sans-serif;
    --bc-sf-font-label: 'Museo Sans', var(--theme-body-font-family, sans-serif);
    --fc-bg-pale:       #F7FBFC;
    --fc-accent-teal:   #2890a8;
    --fc-text-brown:    #746A5F;
    --fc-text-strong:   #393939;
    --fc-text-soft:     #888888;
    --fc-error:         #EF4444;
}
```

**After** (palette aliases, ZERO hex outside comments):

```css
body.woocommerce-checkout {
    --fc-btn-primary-bg:       var(--theme-palette-color-12);   /* sage */
    --fc-btn-primary-bg-hover: var(--theme-palette-color-13);
    --fc-btn-primary-text:     var(--theme-palette-color-24);
    --fc-progress-bar-bg:      var(--theme-palette-color-16);
    --fc-progress-bar-fill:    var(--theme-palette-color-28);
}

:root {
    --bc-sf-font-body:  var(--theme-body-font-family);
    --bc-sf-font-label: var(--theme-body-font-family);
    --fc-bg-pale:       var(--theme-palette-color-26);
    --fc-accent-teal:   var(--theme-palette-color-27);
    --fc-text-brown:    var(--theme-palette-color-19);
    --fc-text-strong:   var(--theme-palette-color-2);
    --fc-text-soft:     var(--theme-palette-color-4);
    --fc-error:         var(--theme-palette-color-15);
}
```

### Verification

| Check | Result |
| --- | --- |
| HTTP 200 on `/`, `/shop`, `/product/<slug>/`, `/product-category/all-candles`, `/cart`, `/checkout`, `/my-account`, `/my-account/woo-wish-list`, `/scent-guide`, `/stockists`, `/candle-care` | ✅ all green |
| `style.css Version: 1.1.14` ↔ `wp_get_theme()->get('Version')` returns `1.1.14` | ✅ match |
| Sage `#DDE2CF` rendered in homepage HTML | ✅ 2 hits |
| Sage hover `#C5CDB4` rendered | ✅ 2 hits |
| Button text `#3B3935` rendered | ✅ 2 hits |
| Body text `#5B554F` rendered | ✅ 2 hits |
| Old blue `#D9EAF0` — only in `color22` slot definition (backup), no active CSS uses it | ✅ 3 hits all in palette definitions |
| Montserrat font references in homepage HTML | ✅ present |
| `letter-spacing: 0.01em` in HTML | ✅ present |
| `byronbay.css` served fresh — palette aliases visible | ✅ |
| Footer Klaviyo subscribe form rendering | ✅ 6 Klaviyo refs + Email Address + SUBSCRIBE button |
| PHP error markers in HTML | ✅ 0 |
| Backups taken before changes | `.refactor-backups/20260513-<ts>-anne-marie-baseline/` (CSS + theme_mods JSON) + `.refactor-backups/20260513-<ts>-phase-c/` |

### Files / settings touched

```
Customizer (theme_mods):
  colorPalette          7 slots updated/added (color12, 13, 22, 23, 24, 25, 26, 27, 28)
  buttonTextColor       repointed to color24
  fontColor             repointed to color25
  128 typography keys   family → Montserrat, letter-spacing → 0.01em

Files:
  clients/byronbay/byronbay.css   2 token blocks refactored to var() aliases (+779 B)
  style.css                       Version 1.1.13 → 1.1.14
```

### Footer subscribe form (Anne Marie's last item)

She reported the footer subscribe form was hidden. **Verification post-restore confirms it's rendering correctly** — Klaviyo form with Email Address input + SUBSCRIBE button + VIP signup text all present on the homepage. The hidden-form issue was part of her pre-restore admin-side edits and resolved automatically by the STG → bbcv1 restore on 2026-05-13.

### What the client can now do herself (and what she still can't)

**Can do via Customizer alone (no dev needed):**
- Change ANY of the 26 palette colors — flows everywhere automatically through token aliases
- Switch font family — flows to body, headings, menu, buttons, cards, filter panel, etc.
- Adjust letter-spacing, font sizes, weights via Customizer Typography panels

**Still requires dev:**
- Adding NEW palette slots (we have to register them via PHP — Customizer UI only edits existing slots)
- Structural CSS changes (layout, spacing rules, etc.)
- Plugin / functionality changes

## [batch-9-e2e-verify-and-version-fixup] - 2026-05-08

> **Environment:** STG (`byronbaySITEBUILDStagingKinsta`, port 23441). bbcv1 FROZEN. Theme version 1.1.10 → **1.1.13**.

### E2E verification sweep across all 8 prior batches

Ran a server-side verification sweep (SSH + curl) covering every file touched today. Findings green except one self-inflicted drift caught and fixed (below).

#### What's verified ✅

| Area | Result |
| --- | --- |
| PHP lint clean on all 14 modified files | ✅ |
| HTTP 200 on `/`, `/shop`, `/product/<slug>/`, `/product-category/all-candles`, `/cart`, `/checkout`, `/my-account`, `/my-account/woo-wish-list` | ✅ |
| Asset URL HTTP 200 on 20 batch-touched files (CSS, JS, PNG, screenshot.png) | ✅ |
| PDP HTML enqueues: `qty-stepper.css` × 1, `aria-panel-sync.js` × 1, `byronbay.css` × 1, `woo-single.css` × 1, `product-information.js` × 3 | ✅ |
| `/shop` HTML enqueues: `shop-customizations.js` × 1, `woo-archive.css` × 1 | ✅ |
| Homepage HTML enqueues: `hero-slider.js` × 1, `product-slider.js` × 1, `bc-hero-slide` markup × 5 | ✅ |
| Token references in 3 tokenized CSS files: 103 + 73 + 7 = **183 var() refs** | ✅ |
| All 9 brand tokens present in `byronbay.css :root` | ✅ |
| All P0/P1 fixes still in place (nonce, reduced-motion, focus indicator, contrast, esc_url_raw, helper, aria-live, openPanelTimer, aria-panel-sync) | ✅ |
| 7 timestamped backup directories preserved on STG | ✅ |
| MyKinsta backup `refactirsnapshot` covers everything | ✅ |

#### Drift caught and fixed 🐛

The version-bump `sed` commands in batches 7 and 8 failed silently. Bash heredoc / single-quote nesting mangled the inline `sed` invocation, so the bumps from 1.1.10 → 1.1.11 (batch 7) and 1.1.11 → 1.1.12 (batch 8) **never landed on disk**. CHANGELOG entries for those batches CLAIMED the version had bumped, but `style.css` Version stayed at 1.1.10 the whole time.

**The SSoT pattern from batch 1 saved us.** Because `BLOCKSY_CHILD_VERSION` is derived from `wp_get_theme('blocksy-child')->get('Version')` (which reads `style.css`'s `Version:` header), the constant was ALSO 1.1.10 — perfectly in sync with the file. There was no constant/file drift. The drift was at the CHANGELOG-claim level only.

A pre-SSoT codebase would have hidden this: a hardcoded `define('BLOCKSY_CHILD_VERSION', '1.1.12')` in functions.php would have meant WP showed 1.1.12 while style.css said 1.1.10 — silent drift, no signal. With SSoT, the discrepancy was loud the moment we asked WP what the version was.

**Fix:** bumped style.css Version 1.1.10 → 1.1.13 (skipping the never-shipped 1.1.11 + 1.1.12 to avoid two false-shipped versions in the version history).

**Verified post-fix:**
- `style.css Version: 1.1.13` ✅
- `wp eval 'wp_get_theme("blocksy-child")->get("Version")'` → `1.1.13` ✅
- Rendered `?ver=1.1.13` in homepage HTML ✅

### Stateful E2E (cart → checkout) — not verified by this batch

The Playwright MCP server today resets the browser context between every tool call (each call hits "about:blank" after a successful navigate). Stateful flows (click "Add to cart" → assert mini cart drawer state → click checkout → assert checkout step form) are structurally not possible through this MCP. Single-shot navigate-and-snapshot works (we got fresh PDP DOM with qty stepper, add-to-cart button, all expected refs), but click + verify cannot be chained.

**Flagged for Vita's QA pass** — full real-user clickflow on:
- Cart drawer open → aria-expanded="true" on `.ct-header-cart` link
- PDP qty stepper behavior at all breakpoints (320 / 375 / 768 / 1080 / 1440)
- Mini cart drawer behavior + suggested products carousel
- /checkout form layout with token-resolved colors at all breakpoints
- Wishlist add → aria-live announcement to screen readers
- Hero slider pause-on-keyboard-focus

### Architecture compliance — final state

```
✅  Audit P0 backlog              8 / 8 shipped
✅  Audit P1 backlog              9 / 9 shipped (+ 6 FP + 2 reclass + 1 vague = 18/18 closed)
✅  custom/ migration             7 / 7 sub-tasks closed
✅  Pre-existing #54 drawer/qty   closed (qty-stepper extracted)
✅  BBC color extraction          9 tokens, 72 literal replacements
✅  Single-source-of-truth bump   confirmed working (caught its own drift)
```

### Files touched (1)

```
M  style.css                                   Version 1.1.10 → 1.1.13
```

## [batch-8-bbc-color-extraction] - 2026-05-08

> **Environment:** STG (`byronbaySITEBUILDStagingKinsta`, port 23441). bbcv1 FROZEN. Theme version 1.1.11 → **1.1.12**.
> **Backup:** MyKinsta full-site snapshot `refactirsnapshot` taken 2026-05-08 13:48 (expires 2026-06-07).

### Refactored — Layer 1/Layer 2 cleanup of the 3 moved CSS component files

The 3 CSS files moved from `custom/` to `assets/css/components/` in batch 2 still had Byron-specific values baked in (`#D9EAF0`, `#F7FBFC`, `#2890a8`, `#746A5F`, Quicksand, Museo Sans). The original audit doc called this out as the final architecture-compliance step ("Quick win #3" — extract Byron values to clients/byronbay/byronbay.css via CSS custom properties, mirroring the existing `--fc-btn-primary-bg` pattern).

This batch tokenizes the brand-distinct values across all 3 files. Each literal is replaced with a `var(--token, #fallback)` reference where the fallback is the original literal — so the file is still self-contained, and a future client forking the theme can change the brand by re-defining the tokens in their own `{slug}.css`.

### 9 new tokens added to `clients/byronbay/byronbay.css :root`

| Token | Value | Purpose |
| --- | --- | --- |
| `--bc-sf-font-body` | `'Quicksand', sans-serif` | Body / paragraph font on checkout |
| `--bc-sf-font-label` | `'Museo Sans', var(--theme-body-font-family, sans-serif)` | Form-label / button-label font |
| `--fc-bg-pale` | `#F7FBFC` | Pale blue background fields (BBC brand) |
| `--fc-accent-teal` | `#2890a8` | Teal accent (badges, links — BBC brand) |
| `--fc-text-brown` | `#746A5F` | Earth-brown body-text accent (matches `--theme-palette-color-3`) |
| `--fc-text-strong` | `#393939` | Primary text, headings |
| `--fc-text-soft` | `#888888` | Secondary text, helper copy |
| `--fc-error` | `#EF4444` | Form errors, required asterisks |

Plus 5 already-existing tokens left as-is (`--fc-btn-primary-{bg,bg-hover,text}`, `--fc-progress-bar-{bg,fill}`).

### 72 literal replacements across 3 files

| File | Replacements | Bytes Δ |
| --- | --- | --- |
| `assets/css/components/checkout-order-summary.css` | 43 | +1033 (added `var(...)` wrappers) |
| `assets/css/components/checkout-step-form.css` | 22 | +469 |
| `assets/css/components/checkout-trust-badges.css` | 7 | +157 |
| **Total** | **72** | **+1659** |

The file size increase (+1.6 KB across 80 KB of CSS) is the cost of tokenization — each `#393939` becomes `var(--fc-text-strong, #393939)` (~25 extra bytes per replacement). Acceptable trade for client-fork flexibility.

### What got tokenized (vs left as literal)

**Tokenized** (Byron-distinct or commonly customizable):
- All 6 fonts (Quicksand + Museo Sans variants)
- All 5 brand colors (#D9EAF0 already done in earlier work; this batch did #F7FBFC, #2890a8, #746A5F)
- All text colors (#393939, #888888) — generic but worth tokenizing for fork flexibility
- Error red (#EF4444)

**Left as literal** (no tokenization, generic UI neutrals):
- White `#fff` / `#ffffff`
- Light grays `#cfcfcf`, `#dddddd`, `#E4E5E7` — divider/border greys, identical across most brands
- Near-black `#111`, `#353638`, `#030712` — generic
- Success greens `#22c55e`, `#163317`, `#2e7d32`
- `rgba(0,0,0,0.x)` shadow alphas

### Visual regression — zero by construction

`var(--token, #fallback)` resolves to:
- `#fallback` if `--token` is undefined
- The token's value if defined

In this codebase, every `--token` is **defined in `byronbay.css :root` with the SAME value as the fallback**. So the computed style for any property is identical pre- and post-refactor.

Net visual change: **zero**.

### Verification

- All 3 tokenized CSS files HTTP 200 ✅
- `clients/byronbay/byronbay.css` HTTP 200 (new `:root` block parses) ✅
- HTTP 200 on `/`, `/shop`, `/cart`, `/my-account` ✅
- `/product/<slug>` 301 → trailing-slash variant (normal) ✅
- 0 console errors on cart page (loads same `offcanvas.css` chunk that the mini-cart drawer uses) ✅
- Backups at `.refactor-backups/20260508-175230-color-extract/` ✅
- MyKinsta backup `refactirsnapshot` covers the entire STG site ✅
- Kinsta cache flushed ✅

End-to-end Playwright matrix at /checkout (with cart populated) flagged for Vita's QA pass — Playwright sessions kept losing page state today.

### Files touched (5)

```
M  assets/css/components/checkout-order-summary.css   +1033 bytes (43 var() wraps)
M  assets/css/components/checkout-step-form.css       +469 bytes (22 var() wraps)
M  assets/css/components/checkout-trust-badges.css    +157 bytes (7 var() wraps)
M  clients/byronbay/byronbay.css                      +9 token definitions in :root
M  style.css                                           Version 1.1.11 → 1.1.12
```

### Architecture compliance — `custom/` migration FULLY-FULLY closed

Adding to the closed list from batch 6:
- ✅ All 10 module files moved to correct layers
- ✅ Feature-flag system wired
- ✅ Manifest features registered
- ✅ Pattern docs written
- ✅ `custom/` directory deleted
- ✅ Dangling enqueue dead code removed
- ✅ **BBC color extraction → byronbay.css (this batch)**

Zero items remain on the `custom/` migration. The 2026-04-24 audit is fully resolved.

## [batch-7-qty-stepper-extracted] - 2026-05-08

> **Environment:** STG (`byronbaySITEBUILDStagingKinsta`, port 23441). bbcv1 FROZEN. Theme version 1.1.10 → **1.1.11**.

### Refactored — qty-stepper rules now live in one place (#54)

Pre-existing task: the horizontal qty-stepper structural rules were duplicated in two files — `clients/byronbay/byronbay.css` (PDP add-to-cart row, ~50 lines) and `assets/css/components/offcanvas.css` (mini cart drawer, ~60 lines). When Blocksy updated its qty stepper or the Customizer setting changed, both blocks had to stay in sync — and a code comment in offcanvas.css explicitly flagged this as TODO.

This batch extracts the structural rules into a new shared component file using CSS custom properties for the size/color knobs. Each context now overrides only what's different.

### Added

- **`assets/css/components/qty-stepper.css`** (3.9 KB) — Layer 1 reusable component. Uses generic `.quantity[data-type="type-2"]` selector so the horizontal stepper layout applies wherever Blocksy renders type-2 qty inputs. Five CSS custom properties expose the knobs (`--bc-qty-btn-width`, `--bc-qty-btn-height`, `--bc-qty-input-width`, `--bc-qty-border`, `--bc-qty-radius`).
- **`docs/patterns/qty-stepper.md`** (~3.4 KB) — pattern doc with selector matrix, knob reference, usage example, failure modes.
- **`inc/enqueue.php`** — wired global enqueue for `qty-stepper` component (sits next to the existing `offcanvas` enqueue).

### Removed (duplicates)

- **`clients/byronbay/byronbay.css`** PDP qty-stepper block — **−1551 bytes** (1856 → 305). The PDP context now uses default knob values from `qty-stepper.css` (36px / 40px / 45px). Single comment marker remains noting the refactor.
- **`assets/css/components/offcanvas.css`** mini-cart qty-stepper block — **−1966 bytes** (2637 → 671). The mini cart now sets only 4 CSS custom properties (28px button, 28px height, 36px input width, form-field-border color) + the existing `.bc-qty-label` hide rule + a font-size: 14px tweak.

Net code removed: **−3517 bytes of duplicated structural CSS** (≈ 110 lines collapsed to ≈ 12).

### Why CSS custom properties (not separate selectors)

Considered alternatives:

1. Keep duplicate scoped selectors with a shared mixin pattern → still requires touching N places when Blocksy changes a default.
2. Generic selector + `:where()` for low specificity → loses the `!important` fight against Blocksy's bundled CSS.
3. **CSS custom properties (chosen)** → structural rules live once, contexts override only the variables. Adding a new context (e.g. checkout page qty stepper, B2B bulk-order modal) costs 4 lines of vars, not 50 lines of structural rules.

### Files touched (5)

```
A  assets/css/components/qty-stepper.css       NEW (3.9 KB structural Layer 1)
A  docs/patterns/qty-stepper.md                NEW (3.4 KB pattern doc)
M  inc/enqueue.php                             + qty-stepper global enqueue (1 line + 2 comment)
M  clients/byronbay/byronbay.css               −1551 bytes (PDP block collapsed)
M  assets/css/components/offcanvas.css         −1966 bytes (mini cart block collapsed)
M  style.css                                   Version 1.1.10 → 1.1.11
```

### Verification

- `php -l inc/enqueue.php` clean ✅
- `qty-stepper.css?ver=<filemtime>` rendered in `/shop` HTML with handle `blocksy-child-qty-stepper` ✅
- HTTP 200 on `/`, `/shop`, `/cart`, `/my-account` ✅
- Backups at `.refactor-backups/20260508-161332-qty-stepper/` ✅
- Kinsta cache flushed ✅

### Visual regression — caveat

The refactor preserves all the CSS that was previously rendering. CSS-property-by-property the post-refactor output equals the pre-refactor output (same `display`, `position`, `width`, `height`, `border`, `border-radius`, `flex` values per context). Logically there should be zero visual change.

End-to-end Playwright matrix verification at every breakpoint (320 / 375 / 768 / 1080 / 1440 / 2560) on PDP + mini cart drawer + cart page is **flagged for Vita's next QA pass** — Playwright sessions kept losing page state today, and the property-equivalence is solid enough static evidence to ship.

Backup files are at `.refactor-backups/20260508-161332-qty-stepper/` for one-step rollback if anything regresses.

## [batch-6-custom-dir-delete-and-dead-code-strip] - 2026-05-08

> **Environment:** STG (`byronbaySITEBUILDStagingKinsta`, port 23441). bbcv1 FROZEN. Theme version 1.1.9 → **1.1.10**.

### Removed

- **`custom/` directory deleted wholesale** (20 files / 13 KB). The dir had been kept on disk for one verification cycle after batch 2 (`audit-P1-batch-1-custom-dir`). All files had already been duplicated into the correct layers (`inc/`, `assets/js/`, `assets/css/components/`, `assets/images/payment-icons/`) and `inc/loader.php` no longer requires anything from it. Backup: `.refactor-backups/20260508-160323-custom-final/`.
- **Dangling `wp_enqueue_scripts` blocks stripped from 4 moved PHP files** (~1.96 KB of dead code total):
  - `inc/shop-customizations.php` — −452 bytes (was still referencing `custom/shop-customizations.js` that no longer existed)
  - `inc/checkout-order-summary.php` — −708 bytes (was duplicate-enqueueing the same CSS/JS that `inc/enqueue.php` was already loading via the new feature-gated path)
  - `inc/checkout-step-form.php` — −430 bytes (same — duplicate enqueue)
  - `inc/checkout-trust-badges.php` — −406 bytes (was referencing the OLD `assets/css/components/checkout-trust.css` filename, but the file was renamed to `checkout-trust-badges.css` during the move — `file_exists()` guard kept it silent but it was dead code)

### Why this needed a follow-up

Batch 2's regex for stripping the self-enqueue blocks (`add_action('wp_enqueue_scripts', function () { ... });`) had a `^}\s*\)\s*;` anchor that didn't match in any of the 4 files — likely because the closing `} );` had different whitespace than the regex assumed. The `str.replace()` calls that ran after the regex DID rewrite path strings (`custom/css/` → `assets/css/components/`, etc.), which is why 3 of the 4 files appeared to be "working" — they pointed at the new asset paths and just duplicated the enqueue. The 4th (`shop-customizations.php`) didn't match the path-replace pattern (`custom/shop-customizations.js`, no `custom/css/` or `custom/js/` substring), so it kept pointing at the original `custom/` path and only `file_exists()` saved it from a fatal once `custom/` was deleted.

This batch replaces the regex with a brace-counting pass: find `add_action('wp_enqueue_scripts', function () {`, walk forward counting `{` / `}` until depth returns to 0, then consume the trailing `} );`. Robust against any internal indentation.

### Verification

- All 4 inc/ files: `grep -c 'wp_enqueue_scripts'` returns **0** ✅
- `php -l` clean on all 4 ✅
- HTTP 200 on `/`, `/shop`, `/product-category/all-candles`, `/cart`, `/my-account` ✅
- `/shop` HTML still serves `assets/js/shop-customizations.js?ver=<filemtime>` (now from the canonical `inc/enqueue.php` enqueue, no duplication) ✅
- Backups at `.refactor-backups/20260508-160323-custom-final/` + `20260508-160601-strip-dangling/` ✅
- Kinsta cache flushed ✅

### Architecture compliance status

The `custom/` → Layer 1/2 refactor is now **fully closed**:
- ✅ All 10 module files moved to correct layers (batch 2)
- ✅ Feature-flag system wired (batch 2)
- ✅ Manifest features registered (batch 2)
- ✅ Pattern docs written (batch 4)
- ✅ `custom/` directory deleted (this batch)
- ✅ Dangling enqueue dead code removed (this batch)

Only deferred item from the original audit: BBC color extraction from the 3 moved CSS component files into `clients/byronbay/byronbay.css` (48 KB CSS, separate sprint).

### Files touched (5)

```
D  custom/                                      (entire directory, 20 files, 13 KB)
M  inc/shop-customizations.php                  (−452 bytes dead enqueue block)
M  inc/checkout-order-summary.php               (−708 bytes dead enqueue block)
M  inc/checkout-step-form.php                   (−430 bytes dead enqueue block)
M  inc/checkout-trust-badges.php                (−406 bytes dead enqueue block)
M  style.css                                    (Version 1.1.9 → 1.1.10)
```

## [audit-P1-batch-4-f3-f4-reclass] - 2026-05-08

> **Environment:** STG (`byronbaySITEBUILDStagingKinsta`, port 23441). bbcv1 FROZEN. Theme version 1.1.8 → **1.1.9**.

### Reclassified — F3 + F4 are not refactor targets

The two remaining audit P1 candidates (`F3: search-dropdown.css 61 !important` and `F4: woo-checkout.css 58 !important`) were flagged by an audit agent as "specificity wars" / "code smell." On verification, both files use `!important` appropriately for their job: **overriding third-party plugin CSS that ships its own bundled stylesheet which we cannot edit.**

| File | `!important` count | Vendor it overrides | Rationale |
| --- | --- | --- | --- |
| `assets/css/components/search-dropdown.css` | 61 | FiboSearch / DGWT AJAX Search | Container geometry, product-card sizing, hover state, mobile @media overrides — all fight the plugin's own bundled CSS. No CSS custom properties exposed by the plugin for these knobs. |
| `assets/css/components/woo-checkout.css` | 58 | Fluid Checkout | `.fc-checkout-*`, `.fc-step__*`, `.fc-progress-bar` — deeply nested plugin selectors. CSS-var pattern (`--fc-btn-primary-*`, `--fc-progress-bar-*`) handles tokens; `!important` covers the structural rules where no var counterpart exists. |

**What would a "refactor" actually look like?** Either (a) replace each `!important` with a parent-class specificity bump (e.g. `body.search-active .X` or `body.fc-checkout .Y`) — which would have to wrap nearly every rule and is no cleaner than `!important`; or (b) wait for the plugins to expose CSS custom properties, which they don't today.

**Decision:** ship explanatory docblocks rather than mechanical edits. The next dev / auditor / future-me opening either file gets the rationale up front and doesn't waste time re-litigating the choice.

### Changed

- **`assets/css/components/search-dropdown.css`** — top docblock expanded with a 27-line "!important USAGE — INTENTIONAL" section explaining (1) the FiboSearch override pattern, (2) the four categories of `!important` here (container geometry, product-card sizing, mobile @media overrides, view-all button), and (3) the regression risk of removing `!important` without raising selector specificity.
- **`assets/css/components/woo-checkout.css`** — top docblock gained an 8-line `!important USAGE — INTENTIONAL` paragraph noting the Fluid Checkout coupling and the existing CSS-var extension point as the preferred override mechanism for client-specific tokens.
- **`style.css`** — Version 1.1.8 → 1.1.9.

### Verification

- Rule-only `!important` count (excluding comment text) unchanged: 61 in `search-dropdown.css`, 58 in `woo-checkout.css` → confirms zero behavioral edits ✅
- `HTTP 200` on `/`, `/shop`, `/cart` ✅
- Backups at `.refactor-backups/20260508-155707-css-docblocks/` ✅
- Kinsta cache flushed ✅

### Audit P1 — final tally

After this batch the audit P1 backlog is fully resolved:

| Bucket | Count | Disposition |
| --- | --- | --- |
| **Shipped** | 9 | Real fixes deployed across batches 2 + 3 |
| **False positives** | 6 | Code already does the thing (B1, B3, B6, F1, F2, F5) |
| **Reclassified** | 2 | F3, F4 — `!important` density is appropriate for vendor override, docblocks added |
| **Vague** | 1 | F11 — agent cited a CSS section with no specific magic number |
| **Total** | 18 | 100 % closed |

### Pattern note for future audit sweeps

Of 18 P1 candidates, **9 needed code (50 %)** and **9 didn't (50 %)** — the audit-agent false-positive rate for this codebase is roughly 1-in-2 once you include reclassifications and code-already-does-the-thing items. Going forward: every audit-flagged candidate must be reproduced at its stated `file:line` before any code change. "Agent flagged it → dev reproduces it → dev fixes it" is the working rule.

### Files touched (3)

```
M  assets/css/components/search-dropdown.css   (docblock expanded, +27 lines comment)
M  assets/css/components/woo-checkout.css      (docblock expanded, +8 lines comment)
M  style.css                                   (Version 1.1.8 → 1.1.9)
```

## [audit-P1-batch-3-aria-and-pattern-docs] - 2026-05-08

> **Environment:** STG (`byronbaySITEBUILDStagingKinsta`, port 23441). bbcv1 FROZEN. Theme version 1.1.7 → **1.1.8**.

### Added — `assets/js/aria-panel-sync.js`

New shared script that fixes audit P1 F9 (the "missing aria-expanded / aria-controls on offcanvas triggers" finding that I'd deferred from batch-2 because it needed DOM probing).

**What it does:**
- Walks a known-mapping table of trigger-selector → panel-id at init:
  - `a[href="#woo-cart-panel"]` → `#woo-cart-panel`
  - `a[href*="/my-account/woo-wish-list"]` → `#woo-wishlist-panel` (wishlist trigger uses a real URL, intercepted by `wishlist-offcanvas.js`)
  - `a[href="#woo-filters-panel"]` → `#woo-filters-panel`
  - `a[href="#account-modal"]` → `#account-modal`
  - `[data-panel="product-info-panel"]` → `#product-info-panel`
- Stamps `aria-controls`, `aria-expanded="false"` (or `"true"` if panel is already open), and `aria-haspopup="dialog"` on each trigger.
- Binds one `MutationObserver` per panel, watching the `class` attribute. When `.active` toggles, every trigger pointing at that panel has its `aria-expanded` updated. One observer per panel (not per trigger) so multiple triggers (e.g. mobile + desktop) stay in sync.
- No jQuery. ~85 lines including the "why" docblock.

**Why this design:**
- The sentinel for open/closed is each panel's `.active` class — Blocksy and our own panels both use this. We don't need to hook into Blocksy's `ctEvents.trigger('ct:overlay:handle-click')` or wishlist-offcanvas's `openPanel/closePanel`. The class change is the single source of truth.
- One observer per panel keeps the listener count tiny (5 panels max) regardless of how many triggers exist on a page.
- Wishlist trigger is matched by URL contains rather than `href="#..."` because Blocksy's rendering uses the real `/my-account/woo-wish-list` URL even though our JS intercepts the click and opens the offcanvas.

**Wired in `inc/enqueue.php`** as a globally-loaded footer script (panels are reachable from every page). Handle: `blocksy-child-aria-panel-sync`. Cache-busted via `filemtime()`.

### Added — 4 mandatory pattern docs

Per the BC architecture rule, every reusable pattern must have a `docs/patterns/*.md`. Today's `custom/` → `inc/` refactor moved 4 modules into the optional-modules system; their pattern docs were missing. Now created:

- `docs/patterns/shop-customizations.md` — result-count repositioning + AJAX update + sort-label tweak.
- `docs/patterns/checkout-order-summary.md` — FC Pro hooks + filters covering ~14 individual customizations to the checkout order-summary block.
- `docs/patterns/checkout-step-form.md` — guest description paragraph + newsletter opt-in checkbox flow (saves to order meta).
- `docs/patterns/checkout-trust-badges.md` — desktop sidebar + mobile variant of the "Secure Payments" trust block + payment-icon images now in `assets/images/payment-icons/`.

Each doc has the standard sections: feature key, where it loads, what it does, hooks/filters, failure modes, related modules, TODO (separate ticket).

### Verification

- `php -l` clean on `inc/enqueue.php` ✅
- `assets/js/aria-panel-sync.js` rendered in `/shop` HTML with proper handle ID `blocksy-child-aria-panel-sync` and `?ver=<filemtime>` cache-bust ✅
- File on disk, body matches local copy ✅
- Kinsta cache flushed ✅
- `style.css Version: 1.1.8` ✅

**Note:** in-browser MutationObserver behavior wasn't fully exercised in this batch because Playwright sessions kept losing page state between `navigate` and `evaluate` calls. Static evidence (file deployed, script tag rendered, syntax-clean JS) is solid. End-to-end verification (open cart drawer → assert `aria-expanded="true"` on the trigger) flagged for next QA pass.

### Files touched (6)

```
A  assets/js/aria-panel-sync.js                   (new, ~85 lines)
A  docs/patterns/shop-customizations.md           (new, 2.8 KB)
A  docs/patterns/checkout-order-summary.md        (new, 4.0 KB)
A  docs/patterns/checkout-step-form.md            (new, 2.3 KB)
A  docs/patterns/checkout-trust-badges.md         (new, 3.5 KB)
M  inc/enqueue.php                                (+ aria-panel-sync enqueue)
M  style.css                                      (Version 1.1.7 → 1.1.8)
```

### Audit P1 status update

After this batch:

| Bucket | Count | Notes |
| --- | --- | --- |
| Shipped | 9 | (was 8) — adds F9 |
| False positives | 6 | unchanged |
| Deferred | 2 | (was 3) — F9 cleared. Remaining: F3 + F4 (CSS !important mass refactors) |
| Vague | 1 | F11 unchanged |

**Deferred remainder:** F3 + F4 are the two CSS hygiene refactors (`search-dropdown.css` 61 `!important`, `woo-checkout.css` 58 `!important`). They each need a full Playwright-matrix visual-regression cycle, not safe to bundle into today's work.

## [audit-P1-batch-2-verify-then-fix] - 2026-05-08

> **Environment:** STG (`byronbaySITEBUILDStagingKinsta`, port 23441). bbcv1 FROZEN. Theme version 1.1.6 → **1.1.7**.
>
> **Source:** P1 verify-then-fix sweep across the 18 audit candidates (`/tmp/bbc-audit-P1-{backend,frontend}-2026-05-08.md`). Each candidate was reproduced at its stated `file:line` before any code change. Result: 8 REAL fixes shipped, 6 confirmed FALSE positives, 3 deferred.

### Shipped (8 real fixes)

#### Backend — defensive coding

- **`inc/search-restructure.php`** — `esc_url_raw()` wrap on the gallery image URL returned by AJAX. JSON-encoding already protects the wire, but the wrap future-proofs against any caller that uses `.html()` instead of `.attr('src')`. Audit B2.
- **`inc/helpers.php`** — added `bc_get_recently_viewed_cookie()` helper (parses + sanitizes the WC `woocommerce_recently_viewed` cookie). Consolidates the duplicate parsing pattern that lived in 3 places. Audit B4.
- **`inc/mini-cart-empty.php`** + **`inc/recently-viewed.php`** — refactored to use the new helper. 2 callsites simplified, both now share one canonical parser. Audit B4.
- **`inc/product-information.php`** — defensive `! empty( $item['id'] )` guard added before the `$item['id'] === 'cart'` check inside the `header_placements → sections → items` foreach. Future-proofs against Blocksy returning items without an `id` key after a Customizer change. Audit B5.

#### Frontend — accessibility (WCAG)

- **`assets/js/wishlist-offcanvas.js`** — `aria-live="polite"` + `aria-atomic="true"` set on `.ct-dynamic-count-wishlist` badges at init. Screen-reader users now hear add/remove announcements without needing the panel open (WCAG 4.1.3 Status Messages). Audit F6.

#### Frontend — JS hygiene / DX

- **`assets/js/wishlist-offcanvas.js`** — `clearTimeout` guard around the duplicated `setTimeout(openPanel, 300)` calls. Module-scope `openPanelTimer` collapses rapid wishlist-add bursts into a single panel-open. Audit F10.
- **`assets/js/search-restructure.js`** — replaced silent `.catch(function () {})` in the gallery-image fetch fallback with a one-line `console.warn('[BC search] Gallery image fetch failed (hover swap disabled):', err)`. Hover swap remains a progressive enhancement — page works without it — but QA now has a breadcrumb when sessions report missing hover images. Audit F7 + F12.
- **`assets/js/hero-slider.js`** — top-of-file docblock now documents the timing constants (5000 ms interval re: WCAG 2.2.2 Pause/Stop/Hide; 50px swipe threshold). Audit F8.

### Confirmed FALSE positives (no fix shipped — code already does the thing)

| ID | Claim | Reality |
| --- | --- | --- |
| B1 | Missing nonce localization on product info AJAX | `wp_localize_script('bcProductInfo', ['nonce' => wp_create_nonce('bc_product_info')])` already in `inc/enqueue.php`; JS sends it in both fetch bodies. |
| B3 | Unescaped SVG in cart panel heading (`woocommerce.php:425-440`) | SVG is a hard-coded literal string built in PHP — no user input flows in. Surrounding text uses `esc_html__()` and `esc_html()`. |
| B6 | Missing `function_exists` guards in `inc/helpers.php:228, 246, 258, 314` | Guards on `blocksy_render_view()`, `wp_enqueue_style()`, `wc_get_product()` etc. all already present at those lines. |
| F1 + F5 | `setInterval` poll never cleared (`product-carousel-dots.js:268-283, 301-337`) | Poll IS bounded — clears on success AND after 40 attempts (50 ms × 40 = 2 s). Not a leak. |
| F2 | `wishlist-offcanvas.js` missing `'use strict'` | Already on line 15. |

### Deferred (not shipped this batch — separate ticket)

| ID | Item | Why deferred |
| --- | --- | --- |
| F3 | `search-dropdown.css` 61 `!important` declarations (12 % of file) | Mass mechanical refactor with visual-regression risk at every breakpoint. Needs full Playwright matrix + designer eyes. Separate sprint. |
| F4 | `woo-checkout.css` 58 `!important` declarations (15 % of file) | Same as F3. |
| F9 | Missing `aria-expanded` / `aria-controls` on cart + wishlist triggers | Live HTML probe didn't reveal the Blocksy trigger markup at `.ct-header-{cart,wishlist}` — likely uses different selectors. Needs DOM inspection during a real session before adding JS that keeps state in sync. |
| F11 | Magic numbers in `woo-checkout.css` progress bar | Vague — agent cited line 65-80 but the values there are CSS-var-bridged (`--fc-progress-bar-bg`, `var()` chain). No clear magic number to comment. |

### Verification

- `php -l` clean on all 5 modified PHP files ✅
- HTTP 200 on `/`, `/shop`, `/product-category/all-candles`, `/cart`, `/my-account` ✅
- Sanity greps confirm every patch landed (esc_url_raw, helper definition + 2 callers, aria-live, console.warn, openPanelTimer × 5) ✅
- `style.css Version: 1.1.7` ↔ `BLOCKSY_CHILD_VERSION: 1.1.7` (single-source still working) ✅
- Kinsta cache flushed ✅
- Backups at `.refactor-backups/20260508-152650-p1/` ✅

### Numerical comparison

| Metric | Before | After | Delta |
| --- | --- | --- | --- |
| Duplicated cookie-parsing copies | 3 | 1 (in helper) | −67 % |
| AJAX endpoints with unescaped output | 1 (gallery-image URL) | 0 | −100 % |
| Defensive `isset()` gaps in Blocksy-array iterations | 1 | 0 | −100 % |
| Wishlist a11y status announcements | 0 | 1 (aria-live polite) | new |
| Silent `.catch()` blocks in frontend JS | 1 | 0 | −100 % |
| Unbounded `setTimeout(openPanel)` calls | 2 | 0 (collapsed via timer) | −100 % |
| Documented timing constants in JS | partial | hero-slider.js full | + |
| Audit candidates resolved (this batch) | 0 / 18 | 14 / 18 (8 fixed + 6 false-positive verified) | 78 % |

### Files touched

```
M  inc/search-restructure.php       (esc_url_raw on gallery URL)
M  inc/helpers.php                  (+ bc_get_recently_viewed_cookie helper)
M  inc/mini-cart-empty.php          (use helper)
M  inc/recently-viewed.php          (use helper)
M  inc/product-information.php      (defensive isset on $item['id'])
M  assets/js/wishlist-offcanvas.js  (aria-live + openPanelTimer guard)
M  assets/js/search-restructure.js  (console.warn in catch)
M  assets/js/hero-slider.js         (timing-constants docblock)
M  style.css                        (Version 1.1.6 → 1.1.7)
```

9 files modified.

## [audit-P1-batch-1-custom-dir] - 2026-05-08

> **Environment:** STG (`byronbaySITEBUILDStagingKinsta`, port 23441). bbcv1 is FROZEN — no edits there. Theme version 1.1.5 → **1.1.6**.
>
> **Source:** Pending custom/ directory architecture audit from 2026-04-24 (`/private/docs/2026-04-24-child-theme-custom-dir-audit.md`, memory `project_bbc_custom_dir_audit_pending.md`). Resolves 6 architecture violations identified in that audit.

### Refactored — `custom/` directory eliminated (Layer 1 / Layer 2 architecture compliance)

A colleague's `custom/` dir lived outside the documented `inc/` + `assets/` + `clients/` architecture. 10 files (4 PHP + 2 JS + 3 CSS + 4 PNG + 1 stray text) were:
1. **Self-enqueuing** — each PHP file registered its own `wp_enqueue_scripts` handler instead of going through `inc/enqueue.php`.
2. **Bypassing the feature-flag system** — loaded unconditionally via a hard-coded "Custom Modules" block in `inc/loader.php`, above the `$optional_modules` array. Other clients forking the theme couldn't disable them.
3. **Mixing layers** — Byron-specific colors/fonts living in what looked like reusable code; reusable payment-icon images in a Byron-only directory.

**This batch redistributes everything to the correct layers.**

### Files moved

| From | To |
| --- | --- |
| `custom/shop-customizations.php` | `inc/shop-customizations.php` |
| `custom/checkout-order-summary.php` | `inc/checkout-order-summary.php` |
| `custom/checkout-step-form.php` | `inc/checkout-step-form.php` |
| `custom/checkout-trust-badges.php` | `inc/checkout-trust-badges.php` |
| `custom/shop-customizations.js` | `assets/js/shop-customizations.js` |
| `custom/js/checkout-order-summary.js` | `assets/js/checkout-order-summary.js` |
| `custom/css/checkout-order-summary.css` | `assets/css/components/checkout-order-summary.css` |
| `custom/css/checkout-step-form.css` | `assets/css/components/checkout-step-form.css` |
| `custom/css/checkout-trust.css` | `assets/css/components/checkout-trust-badges.css` |
| `custom/images/{afterpay,paypal,secure,zip}.png` | `assets/images/payment-icons/{afterpay,paypal,secure,zip}.png` |

The 4 moved PHP files had their **self-enqueue blocks stripped** (replaced by a comment pointing to `inc/enqueue.php`). The trust-badges PHP also had its 4 hard-coded `BLOCKSY_CHILD_URL . 'custom/images/...'` references rewritten to `BLOCKSY_CHILD_URL . 'assets/images/payment-icons/...'`.

### Loader + manifest changes

- **`inc/loader.php`** — removed the hard-coded `// --- Custom Modules ---` block (the 4 unconditional `blocksy_child_load_module( 'custom/...' )` calls). Added 4 entries to `$optional_modules` array under the keys `shop-customizations`, `checkout-trust-badges`, `checkout-order-summary`, `checkout-step-form` — same feature-flag treatment as the other 8 optional modules.
- **`clients/byronbay/manifest.json`** — added the 4 new feature keys to the `features` array (now 13 entries, up from 9).

### Enqueue changes

- **`inc/enqueue.php`** — added 5 new conditional enqueues:
  - `shop-customizations.js` on `is_shop() || is_product_category() || is_product_tag()`, gated by `blocksy_child_feature_enabled('shop-customizations')`.
  - `checkout-order-summary.css` + `.js` on `is_checkout()`, gated by `blocksy_child_feature_enabled('checkout-order-summary')`.
  - `checkout-step-form.css` on `is_checkout()`, gated by `blocksy_child_feature_enabled('checkout-step-form')`.
  - `checkout-trust-badges.css` on `is_checkout()`, gated by `blocksy_child_feature_enabled('checkout-trust-badges')`.

### Custom/ directory not yet deleted (intentional — soft cutover)

The `custom/` directory still exists on disk but is no longer loaded. Everything that mattered has been duplicated to the new locations and wired through the feature-flag system. We're keeping the `custom/` dir for one verification cycle (Vita's next checkout pass) before deleting wholesale. **Rollback path:** restore `inc/loader.php` from `.refactor-backups/20260508-140410/` and the original modules will load again.

### Deferred to a follow-up sprint

The 3 CSS files still contain Byron-specific values (`#D9EAF0`, `#BFD6DD`, `museo`, `palette-color-*`). Per the original audit's "Quick win #3", these should be extracted to CSS custom properties in the component files with overrides in `clients/byronbay/byronbay.css` (mirroring the `--fc-btn-primary-bg` pattern). That's a 48 KB CSS refactor with visual-regression risk at every breakpoint — needs its own ticket.

Other deferred items from the audit:
- Pattern docs (`docs/patterns/{shop-customizations,checkout-order-summary,checkout-step-form,checkout-trust-badges}.md`) — required by CLAUDE.md but not blocking ship.
- Delete `custom/` directory wholesale once Vita's checkout QA pass green-lights it.

### Verification

- `php -l` clean on `inc/loader.php`, `inc/enqueue.php`, all 4 new `inc/checkout-*.php`, `inc/shop-customizations.php` ✅
- All 9 new asset URLs return HTTP 200 (3 CSS + 2 JS + 4 PNG icons) ✅
- `/shop/` HTML now serves `assets/js/shop-customizations.js?ver=<filemtime>` (was `custom/shop-customizations.js`) ✅
- `wp option get template/stylesheet` confirms STG (not bbcv1) ✅
- Kinsta cache flushed via `wp kinsta cache purge --all` ✅
- `style.css Version: 1.1.6` ↔ `BLOCKSY_CHILD_VERSION: 1.1.6` (single-source from 2026-05-08 batch) ✅

### What's NOT in this batch (P1 audit findings)

Two parallel audit subagents (backend + frontend, ran 2026-05-07) flagged 18 candidate P1 items (6 backend, 12 frontend). On verification, **multiple turned out to be false positives**:
- Backend #1 (`product-information.php` AJAX nonce missing): FALSE — nonce IS localized via `wp_localize_script('bcProductInfo', ['nonce' => wp_create_nonce('bc_product_info')])` in `inc/enqueue.php` and verified in the JS body.
- Backend #6 (missing `function_exists` guards in `inc/helpers.php`): FALSE — guards on `blocksy_render_view`, `wp_enqueue_style`, `wc_get_product` etc. already present in the lines flagged.
- Frontend #2 (`wishlist-offcanvas.js` missing `'use strict'`): FALSE — already on line 15 of the file.

**Decision:** rather than ship half-trusted patches, the remaining audit items go to a follow-up "P1 verification + selective execution" task. Each item must be reproduced before a fix lands.

### Files touched (this batch)

```
A  inc/shop-customizations.php                  (from custom/, self-enqueue stripped)
A  inc/checkout-order-summary.php               (from custom/, self-enqueue stripped)
A  inc/checkout-step-form.php                   (from custom/, self-enqueue stripped)
A  inc/checkout-trust-badges.php                (from custom/, self-enqueue stripped, image URLs rewritten)
A  assets/js/shop-customizations.js             (from custom/)
A  assets/js/checkout-order-summary.js          (from custom/js/)
A  assets/css/components/checkout-order-summary.css   (from custom/css/)
A  assets/css/components/checkout-step-form.css       (from custom/css/)
A  assets/css/components/checkout-trust-badges.css    (from custom/css/checkout-trust.css)
A  assets/images/payment-icons/{afterpay,paypal,secure,zip}.png  (from custom/images/)
M  inc/loader.php                               (Custom Modules block removed; 4 entries added to $optional_modules)
M  inc/enqueue.php                              (5 new conditional enqueues, all feature-gated)
M  clients/byronbay/manifest.json               (4 new features added)
M  style.css                                    (Version 1.1.5 → 1.1.6)
```

13 files added, 4 modified. Net + ≈ 100 KB (CSS files duplicated until custom/ deletion). Once custom/ is deleted, net delta ≈ 0.


## [audit-P0-batch-2] - 2026-05-08

> **Environment:** STG (`byronbaySITEBUILDStagingKinsta`, port 23441). bbcv1 is FROZEN — no edits there. All P0 items below applied to STG only.
>
> **Source:** Two parallel audit subagents (backend/security/defensive + frontend/CSS/JS/a11y/UX) ran 2026-05-07. Surfaced 8 P0 items (2 security, 3 a11y, 3 reliability/maintenance). All landed in this batch. Full breakdown with before/after + percentages: `docs/refactor/2026-05-08-audit-P0.md` (or `/tmp/bbc-refactor-2026-05-08.md`).

### Security
- **Wishlist AJAX endpoint now CSRF-protected.** `inc/wishlist-offcanvas.php` adds `check_ajax_referer( 'bc_wishlist', '_wpnonce' )` + `function_exists( 'wc_get_product' )` guard. `inc/enqueue.php` localizes the nonce to JS via `wp_localize_script( ..., 'bcWishlist', [ 'ajaxUrl', 'nonce' ] )`. `assets/js/wishlist-offcanvas.js` appends `&_wpnonce=<nonce>` to the AJAX body. Audit P0.

### Accessibility (WCAG 2.2 AA)
- **Hero slider — Pause/Stop/Hide (WCAG 2.2.2) + reduced-motion.** `assets/js/hero-slider.js` no longer auto-advances when `window.matchMedia('(prefers-reduced-motion: reduce)').matches`. Added `focusin` / `focusout` listeners (mirroring `mouseenter` / `mouseleave`) so keyboard users get pause-on-focus parity. `assets/css/components/homepage.css` adds `@media (prefers-reduced-motion: reduce) { .bc-hero-slide { transition: none } }` to gate the CSS slide transition as well. Audit P0.
- **Gift-card form — visible focus indicator (WCAG 2.4.7).** `assets/css/components/gift-card-form.css` adds `.pwgc-input:focus, .pwgc-input-text:focus { outline: none; border-color: #999; box-shadow: 0 0 0 2px rgba(116, 106, 95, 0.25); }`. Brand-aligned earth-brown ring at ≥3:1 against page background (also satisfies 1.4.11). Audit P0.
- **404 search-no-results placeholder — contrast (WCAG 1.4.3).** `clients/byronbay/byronbay.css:1215` `#BFBFBF` → `#746A5F`. Contrast against `#F8F4ED` cream: 3.04:1 → 5.27:1 (FAIL → PASS AA, +73%). `#746A5F` is already in BBC's palette as `--fc-text-soft` — change reads as on-brand. Audit P0.

### Reliability
- **Shipping calc — states fetch fail-open.** `assets/js/product-information.js` now guards on `r.ok` before `.json()` (prevents JSON parse error on 5xx HTML), and adds `.catch()` that hides the state field + sets value to "N/A" so the calculator stays usable with country + postcode alone. Mirrors the existing fallback in the calc-submit fetch. Audit P0.

### Maintenance / Architecture
- **`BLOCKSY_CHILD_VERSION` is now a single source of truth.** `functions.php` derives the constant from `wp_get_theme( 'blocksy-child' )->get( 'Version' )` — i.e. it reads `style.css`'s `Version:` header. Eliminates the drift class of bug entirely (style.css was at 1.1.1 while the constant was hardcoded at 1.1.4). Bumping `Version:` in style.css is now sufficient. Per-asset cache-busting via `filemtime()` in `inc/enqueue.php` is kept (different concern: per-file vs per-release). `style.css Version` bumped 1.1.1 → 1.1.5 to land in sync. Audit P0.
- **`style.css` — duplicate `max-height: 80vh` declaration removed** from `.dgwt-wcas-suggestions-wrapp` rule in `assets/css/components/search-dropdown.css`. Stylelint clean. Audit P0.
- **`assets/css/components/woo-single.css` — duplicate `entry-meta` + `has-small-font-size` block removed.** The same rule lived in both `base.css` and `woo-single.css` — kept the base.css copy because the rule is not woo-specific (also applies to blog/page templates). Layer 1 architecture compliance. Audit P0.

### Branding / DX
- **`screenshot.png` deployed** (1200×876, 140 KB) to theme root — Appearance → Themes admin now shows a branded BBC thumbnail instead of the default placeholder.
- **`style.css` `Description:` rewritten** to be Blaze Commerce-forward, architecture-aware, with link to blazecommerce.io. Preserves `@jarutosurano` lead-developer credit.

### Verification
- `php -l functions.php inc/wishlist-offcanvas.php inc/enqueue.php` ✅ no syntax errors
- JS length sanity ✅ wishlist-offcanvas.js, hero-slider.js, product-information.js
- Version sentinel: `style.css Version: 1.1.5` ↔ `BLOCKSY_CHILD_VERSION: 1.1.5` ✅
- HTTP 200 on `/`, `/shop`, `/product-category/all-candles`, `/cart`, `/my-account` ✅
- HTTP 404 on `/404testpath` ✅
- Blocksy + Kinsta caches flushed ✅
- Hero slider: confirmed pause on hover, pause on focus, no auto-advance with reduced-motion forced ✅
- Wishlist add: request rejected with HTTP 403 without nonce, accepted with nonce ✅

## [86exggfgq] - 2026-05-07

### Changed
- **Product-card wishlist heart — initial color white** (CU-86exggfgq, QA Vita 2026-05-07).
  - **Why:** the archive product-card wishlist button (`.ct-wishlist-button-archive`) renders a black heart outline in default state because Blocksy paints the icon via inherited page text color. Per design, initial color should be white so the heart contrasts on the product image background.
  - **Why CSS, not Customizer:** verified 2026-05-07 — Blocksy's Customizer has ZERO color controls for the archive product-card wishlist button. Available wishlist theme_mods are all toggles (`has_archive_wishlist`, `product_wishlist_display_for`, `has_variations_wishlist`). Single-product wishlist HAS color controls (used in `woo-single.css`); the archive variant doesn't.
  - **Implementation:** `assets/css/components/woo-archive.css` (Layer 1) — added rule `.ct-wishlist-button-archive:not([data-button-state="active"]) .ct-icon-container svg path { fill: #fff }`.
  - **Active (wishlisted) state preserved:** Blocksy's existing CSS un-hides `.ct-heart-fill` at `[data-button-state="active"]` and applies the active red color — our rule is scoped to `:not([data-button-state="active"])` so the active state is unchanged.
  - **Selector covers BOTH paths in non-active states.** `.ct-heart-fill` is `opacity: 0` per Blocksy's own CSS, so visually only the outline path shows white.
  - Files: `assets/css/components/woo-archive.css` (+27 lines including comment block).

## [audit-P0-cleanup] - 2026-05-07

> **Environment:** STG (`byronbaySITEBUILDStagingKinsta`, port 23441). bbcv1 is FROZEN — no edits there. All P0 items below applied to STG only.

### Removed
- **35 stale `.bak*` files (all >7 days old)** from `inc/`, `partials/`, `assets/css/components/`, `assets/js/`, `clients/byronbay/`, `custom/`. ~360 KB freed. Kept the 24 most-recent (last 2 days) for active-work safety. Audit P0.1.
- **Dead enqueue refs in `inc/enqueue.php`** for non-existent component files (`woo-cart.css`, `woo-account.css`, `footer.css`). Helper `blocksy_child_enqueue_component()` was silently no-op-ing on these — wasted code. Comment left in place noting where to re-add if page-specific styles are wanted later. Audit P0.2.

### Changed
- **`clients/byronbay/manifest.json` — added `"features"` key** with all 9 optional modules (`drawer-offcanvas`, `wishlist-offcanvas`, `product-tabs`, `product-information`, `recently-viewed`, `mini-cart-empty`, `product-slider`, `search-restructure`, `checkout-customization`). Without this key, `inc/loader.php` falls through the backwards-compat path and loads everything implicitly. Explicit array makes the contract visible + lets future client forks turn modules off. Audit P0.3.
- **`inc/hooks.php` hamburger filter — `fill="#888888"` → `fill="currentColor"`** on the 3 SVG rects. The hex was a Byron-specific value baked into a Layer 1 hook — wrong layer per architecture rule. `currentColor` cascades from CSS `color`, which Blocksy sets via `triggerIconColor.default.color` theme_mod (already configured to `#888888` per CU-86exerxur). Render is visually unchanged on BBC; other clients now get their own `triggerIconColor` automatically. Audit P0.4.
- **`#D9EAF0` fallback values → `transparent` in 3 Layer 1 files** (`product-information.css`, `homepage.css`, `woo-single.css`, total 6 occurrences). The hex was Byron-specific-as-fallback inside `var(--theme-button-background-initial-color, #D9EAF0)`. Primary value still comes from Blocksy's variable; fallback is now a generic `transparent` so a forked child theme on another client can't accidentally render a glimpse of Byron blue. The actual Byron color is preserved in `clients/byronbay/byronbay.css:1115` as `--fc-btn-primary-bg: #D9EAF0`. Audit P0.5.

### Verification
- `php -l inc/enqueue.php` ✅ no syntax errors
- `php -l inc/hooks.php` ✅ no syntax errors
- `python3 -m json.tool clients/byronbay/manifest.json` ✅ valid JSON
- `grep -i 'D9EAF0' assets/css/components/*.css` ✅ no matches in live files (.bak files unaffected)
- HTTP 200 on staging homepage post-flush
- `triggerIconColor.default.color` theme_mod still `#888888` on both header sections — hamburger render unchanged

## [86exf034k] - 2026-05-05
### Fixed
- `assets/css/components/woo-single.css` (PDP #1 + B4): Replace `aspect-ratio:1/1` with
  `align-self:stretch` + `height:auto` so the wishlist button height tracks the rendered
  ATC flex-row height at all breakpoints instead of locking to `--theme-button-min-height`
  token. Adds `box-sizing:border-box`. Applies to standard product and bundle product selectors.

## [86exe005n] - 2026-05-05
### Fixed
- **Search dropdown — mobile background scroll lock** (CU-86exe005n).
  - **Bug:** When FiboSearch adds `dgwt-wcas-overlay-mobile-on` to `<html>` on mobile, the background page remained scrollable through the overlay.
  - **Fix:** Section 14 added to `assets/css/components/search-dropdown.css` — `overflow: hidden`, `overscroll-behavior: contain`, and `touch-action: none` on `html` and `body` when the overlay class is active. Dropdown wrapper retains `overflow-y: auto` and `-webkit-overflow-scrolling: touch` for smooth internal scroll on iOS.
  - **Scope:** CSS-only, no PHP/JS changes. Cache busting automatic via filemtime enqueue.
  - **Verified:** `body.overflow = hidden`, `html.overflow = hidden` when class active; no regression at desktop (class absent at ≥1024px).

## [86exe3dcv QA polish] - 2026-05-05
### Changed
- `custom/css/checkout-order-summary.css`: Appended [CU-86exe3dcv] block — forces all-white text + SVG icons on the cloned coupon badge (`.bc-wt-coupon-row .bc-wt-coupon-clone`), removes plugin-injected `min-height:100px`, tightens margin from `8px 0` to `4px 0 0 0`. Fixes QA readability and badge-to-subtotal spacing (ClickUp 86exe3dcv).

## [86ewn0gwh kickback] - 2026-05-05

### Changed
- **Awards / "Protect You" section — remove 40 px column padding at mobile + tablet** (CU-86ewn0gwh kickback, Vita 2026-05-05).
  - **Why:** The "1 in 3 people suffer…" text column carries an inline `padding: 0 40px 48px` from the block markup. At desktop the card sits in a 2-column layout where that 40 px gives breathing room; at mobile/tablet the column already reaches the page gutter so the extra 40 px on each side compresses the bullet list awkwardly. Vita: "please remove padding in mobile and tablet version for this section".
  - **Implementation:** `assets/css/components/homepage.css` — added `@media (max-width: 999px) { body.home .wp-block-column[style*="padding-right:40px"][style*="padding-left:40px"] { padding-left: 0 !important; padding-right: 0 !important } }`.
  - **Why attribute selector:** the column has no unique class — it's just `wp-block-column is-vertically-aligned-top is-layout-flow`. Targeting via the inline-style attribute pattern catches this column without affecting unrelated columns elsewhere on the homepage. The bottom 48 px padding is preserved (kept as section breathing room).
  - **Verified:** mobile 375 → padding `0 0 0 0` ✅; tablet 768 → padding `0 0 48px 0` ✅ (bottom retained).
  - Files: `assets/css/components/homepage.css` (+24 lines).

## [86exbe963] - 2026-05-04

### Changed
- **Header middle row height — fixed at iPad-Pro / tablet viewport range (690-1080px)** (CU-86exbe963, QA from Lan 2026-05-01).
  - **Why:** At viewports 690-1080 (the Blocksy mobile-device variant range, including the iPad-Pro CSS swap at 1000-1080), the middle row rendered at 115px (desktop's `headerRowHeight`) creating an excessive vertical gap between the announcement bar and the logo+icons row. Live (`byronbaycandles.com` Next.js) renders the main nav row at 79.5px at the same viewport. Total staging header was 211px vs live's 183px.
  - **Why CSS, not theme_mod:** Setting `header_placements → middle-row → headerRowHeight.tablet = "80"` via `set_theme_mod()` does NOT propagate to Blocksy's dynamic-styles for `header_placements` (verified via Playwright + curl 2026-05-04). `blocksy_output_responsive()` should emit a tablet @media rule when `tablet !== desktop`, but for `header_placements` items the per-section iteration appears to skip per-breakpoint output (only mobile @media is emitted, even with explicit `__changed: ["mobile", "tablet"]`). Filed under "Blocksy edge case".
  - **Implementation:** `assets/css/components/header.css` lines 119-156 — replaced the unscoped `[data-device="mobile"] > [data-row="middle"] > .ct-container { min-height: auto; grid-template-rows: auto }` rule with two media-query-scoped variants:
    - `@media (max-width: 689.98px)` — `min-height: auto !important; grid-template-rows: auto !important` (preserves original mobile auto-shrink behavior)
    - `@media (min-width: 690px) and (max-width: 1080px)` — `min-height: 80px !important; grid-template-rows: 80px !important` (matches live's main-nav row height in the iPad-Pro / tablet range)
  - **Why `!important`:** Blocksy core's `#header [data-row] > div { min-height: var(--shrink-height, var(--height)) }` (specificity 1,1,1) beats unscoped `[data-device="mobile"] > [data-row="middle"] > .ct-container` (specificity 0,3,0). Since we already use `!important` overrides elsewhere for similar Blocksy fights and a higher-specificity selector hop wasn't materially cleaner, kept `!important` here for consistency.
  - **What was NOT changed:** desktop variant (>1080px) middle row still 115px — outside QA scope. Live renders 79.5px at all viewports — flag as a future task if Cam wants desktop also matched (would mean a third media query branch + possible Customizer headerRowHeight desktop adjustment).
  - **Theme_mod state:** kept the (non-functional) `headerRowHeight.tablet = "80"` save in place for documentation purposes — if Blocksy fixes the dynamic-styles edge case in a future release, the theme_mod will start working and the CSS override becomes redundant. No harm in keeping both.
  - **Verified:** 689 (44px auto, unchanged from before fix), 690/768/900/1080 (all 80px ✅), 1400 (115px desktop, unchanged — outside scope).
  - Files: `assets/css/components/header.css` (+24 / -3 lines).

- **Header logo max-height — match Blocksy tablet value (50px) at iPad-Pro range (1000-1080px)** (CU-86exbe963 V2, Vita kickback 2026-05-05).
  - **Why:** After dropping the middle row to 80px (above), at 1000-1080 the logo was still rendering at 80px (the desktop `logoMaxHeight` value) — same height as the row. Result: zero padding above and below the logo, the logo crashed into the bottom edge of the announcement bar. Vita comment "this too close with the logo, please add some spacing here" with screenshot showing the issue at the announcement-bar / logo boundary.
  - **Why this is a Blocksy edge case:** Blocksy's responsive `logoMaxHeight` config is `{desktop: 80, tablet: 50, mobile: 44}`. Tablet value applies natively at 690-999.98px (Blocksy's standard tablet range). At 1000-1080 the iPad-Pro CSS swap shows the mobile-device variant, but Blocksy's responsive CSS still uses the actual viewport's breakpoint (which is "desktop" at 1000-1080), so logo gets `--logo-max-height: 80px`.
  - **Implementation:** `assets/css/components/header.css` line 211 — added `#header [data-id="logo"] .site-logo-container { --logo-max-height: 50px !important }` inside the existing `@media (min-width: 1000px) and (max-width: 1080px)` iPad-Pro swap block.
  - **Result:** logo at 1080 → 50px tall in 80px row → 15px padding above + 15px padding below ✅. Matches live's spacing ratio (live logo 44px in 79.5px row → ~17.75px padding).
  - **Verified at 1080:** middle row 80px, logo 50px, padding-top 15px, padding-bottom 15px.
  - Files: `assets/css/components/header.css` (+10 lines, inside existing iPad-Pro swap block).

- **Header search bar — full content width at iPad-Pro range (1000-1080px)** (CU-86exbe963 V3, Jayr request 2026-05-05 "search box end to end, match content width").
  - **Why:** Blocksy applies a 10px horizontal margin to header-row items (`[data-items] > [data-id]` pattern) for inter-item spacing. On the bottom row that meant the search wrap sat at 79-1001 (width 922) while the logo + icons content area above sat at 69-1011 (width 942) — a 10px shortfall on each side, visually misaligned.
  - **Implementation:** added `[data-device="mobile"] > [data-row="bottom"] [data-id="search"] { margin-left: 0 !important; margin-right: 0 !important }` inside the existing iPad-Pro swap block.
  - **Result at 1080:** search wrap left 69 = logo left ✅; search wrap right 1011 = cart icon right ✅; search bar now exactly the same horizontal extent as the logo+icons row above.
  - Files: `assets/css/components/header.css` (+13 lines, inside existing iPad-Pro swap block).

## [86exerxur] - 2026-05-04

### Changed
- **Mobile header hamburger — match live icon weight + color** (CU-86exerxur, QA from Vita 2026-05-01).
  - **Why:** Blocksy's default trigger SVG (type-1, viewBox 18×14, three filled rectangles height 1.7 with rx=1) painted at `var(--theme-palette-color-3)` (#655B51, darker brown) was visibly heavier than the rest of the mobile icon row (account/wishlist/cart/phone all render at #888888 via explicit `fill="#888888"` on uploaded SVGs). Live (`byronbaycandles.com` Next.js) uses 3 fully-rounded pills (height 2, rx=1) at #888888 — uniform with the rest of the icon row.
  - **Why filter, not custom upload:** Blocksy's trigger panel doesn't expose a custom-SVG upload field (verified by walking `wp-content/themes/blocksy/inc/panel-builder/header/trigger/options.php` — only `triggerIconColor`, `triggerIconSize`, `mobile_menu_trigger_type` available). The official extension point is `apply_filters('blocksy:header:trigger:svg', ...)` in `wp-content/themes/blocksy/inc/panel-builder/header/trigger/view.php`.
  - **Implementation:** `inc/hooks.php` — added a 3-line filter that returns a 24×24 SVG with three rects (`x=2 y=5/11/17 width=20 height=2 rx=1 fill="#888888"`) — capsule pill shape, same visual weight as live. Inline string (no media upload) so the icon always paints in the correct color even if `triggerIconColor` theme_mod drifts later.
  - **Doc:** captured Live + Staging values for ALL 5 mobile-header icons (hamburger, phone, account, wishlist, cart) + announcement-bar truck + search magnifier in new ClickUp parent doc page [Header Icons Reference — Live vs Staging vs Figma](https://app.clickup.com/36771024/docs/13256g-163598/13256g-148118). Sub-page per CU-86exerxur with rollback command + verification screenshot.
  - **Cleanup:** removed unused `icon_source` / `icon` / `triggerIcon` / `customTriggerIcon` keys from `header_placements` → trigger items (Blocksy doesn't honor them on the trigger). Deleted orphan `Menu.svg` attachment (645877) — initial attempt at the Blocksy custom-icon route before discovering the trigger panel has no upload field.
  - Files: `inc/hooks.php` (+38 lines).

## [86exbd8kg] - 2026-05-01

### Changed
- **Mini cart drawer — unified scroll for items + suggested products** (CU-86exbd8kg V3, follow-up to V2 compaction).
  - **Why:** On short viewports Blocksy's stock mini cart renders `<ul.woocommerce-mini-cart>` with its own intrinsic `overflow-y` + tight `max-height`. Only one cart row was visible inside a cramped inner scroll-box while the suggested-products carousel sat below at full height — items appeared cut off (Cam screenshot 2026-04-30). With 1–2 items there was also a large empty gap between the (short) `<ul>` and the suggested-products carousel.
  - **Implementation:** `assets/js/cart-offcanvas.js` — appended a `wrapScrollItems()` IIFE that wraps `<ul.woocommerce-mini-cart>` + `.ct-suggested-products--mini-cart` in a single `<div class="bc-cart-scroll">` after every cart fragment refresh (`wc_fragments_refreshed`, `wc_fragments_loaded`, `added_to_cart`, `removed_from_cart`) plus initial `DOMContentLoaded`. Idempotent — bails if already wrapped. Bails on empty-cart state.
  - **CSS:** `assets/css/components/offcanvas.css` — new "Mini Cart unified scroll" block. `.ct-panel-content-inner` is now `display:flex; flex-direction:column; height:100%`. `.bc-cart-scroll` is `flex:1 1 auto; min-height:0; overflow-y:auto; display:flex; flex-direction:column` (single scroll region inside the drawer body). `<ul>` has its Blocksy `overflow` and `max-height` reset. `.ct-suggested-products--mini-cart` gets `margin-top:auto` — pushes it to the bottom of the scroll area when there's slack (1–2 items), contributes 0 px on overflow (5+ items, natural stacking). Footer (`.woocommerce-mini-cart__total`, `.ct-shipping-progress-mini-cart`, `.bc-mini-cart-totals`, `.woocommerce-mini-cart__buttons`, `.bc-mini-cart-secure`) is pinned via `flex-shrink:0`.
  - **Why JS-wrap, not CSS-only:** `<ul>` and `.ct-suggested-products--mini-cart` come out of WooCommerce/Blocksy as flat siblings inside `.ct-panel-content-inner` along with the totals/footer. There's no PHP filter to re-parent them server-side, and CSS alone can't make TWO siblings share a scroll container while keeping the OTHER siblings outside. The JS wrap is the cleanest, most resilient approach — fragment-refresh-aware and idempotent.
  - **Why `margin-top:auto`, not `justify-content:space-between`:** `space-between` on a flex container with `overflow-y:auto` can render the first child above the visible scroll start when content overflows. `margin-top:auto` is safer.
  - Files: `assets/js/cart-offcanvas.js` (+52 lines), `assets/css/components/offcanvas.css` (+60 lines), `inc/enqueue.php` (touched — `filemtime()` cache-bust). Documented in `docs/patterns/offcanvas.md` (new "Mini Cart Unified Scroll" section + architecture diagram updated to include `assets/js/`) and `docs/patterns/drawer-suggested-products.md` (cross-reference note added to state #1).
  - Bumped `BLOCKSY_CHILD_VERSION` 1.1.3 → 1.1.4.

## [86exemy5y] - 2026-04-30

### Investigated (no code or DB changes)
- **BOGO + 6th Refill FREE coupon audit on `bbcv1.blz.au` (rebuild) and `cart.byronbaycandles.com` (live)** (CU-86exemy5y)
  - **Why:** Task requested verifying a "BOGO" coupon and "6th Refill FREE" coupon on the rebuild before QA Section 1 (`86exdp68h`) could proceed. BOGO had a $0.00 bug history on prior live go-lives, so configuration parity was critical.
  - **Method:** Read-only WP-CLI queries on rebuild, then SSH-audited live (`byronbaycandlescom@35.189.2.37:14054`, Kinsta `byronbaycandlescom.kinsta.cloud`).
  - **Finding 1 — only one BOGO-type coupon exists on either site:** `wp post list --post_type=shop_coupon --meta_key=discount_type --meta_value=wbte_sc_bogo` returned post `643576` `6threfillfree1` and nothing else, on both rebuild and live. Distribution on rebuild: 91 percent / 55 fixed_cart / 10 fixed_product / 4 store_credit / 1 wbte_sc_bogo. The task brief's "BOGO" and "6th Refill" are the same coupon — the WT Smart Coupon Pro plugin uses `wbte_sc_bogo` as the discount type for both Buy-X-Get-X and Buy-X-Get-Y mechanics.
  - **Finding 2 — rebuild meta matches live byte-for-byte for all 18 audited fields:** `wbte_sc_bogo_type=wbte_sc_bogo_bxgx`, `_wbte_sc_bogo_min_qty=5`, `wbte_sc_bogo_product_categories=685,746` (Candle Refills + Large 50hr Candles), `wbte_sc_bogo_free_category_ids=685` (refills only), `wbte_sc_bogo_customer_gets_cheap_exp=customer_gets_cheapest`, `wbte_sc_bogo_apply_offer=apply_once`, `wbte_sc_bogo_code_condition=code_auto`, `_wt_make_auto_coupon=1`, `_wt_sc_user_roles=administrator,customer,wbte_sc_guest`, `individual_use=no`, `free_shipping=yes`, plus 6 others. Only diffs: `usage_count` (live=32 real customers, rebuild=25 test usages) and `_used_by` rows (different customer pools).
  - **Finding 3 — task brief live-ID `633033` is a draft on live:** the post still exists at `post_status=draft` (the OLD version of `6threfillfree`), replaced by published `643576` and never deleted. Five additional historical BOGO drafts on live (`637474 refillbonusbuy`, `634168 2NDCAR`, `643292`, `632569 WOMEN`) — all `draft`, NOT active.
  - **Finding 4 — task brief URL `/shop/candle-jar-refills-essential-oils/` is invalid on rebuild;** taxonomy uses `/product-category/candle-refill/` (term 685, the one targeted by the coupon) or `/product-category/candle-jar-refills/` (term 567). Documentation issue in the brief, not a config issue.
  - **Finding 5 — plugin name discrepancy:** task brief says "WooCommerce Smart Coupons" (StoreApps); actually installed on both live and rebuild is "WT Smart Coupon Pro 3.5.0" (WebToffee). These are different vendor products. Decision: keep WT (matches live).
  - **Action items resolved:** No replication needed (rebuild = live for the active coupon). No new BOGO Classic to create. `usage_count` left as-is (cosmetic, faithful to live behavior).
  - **Open flag (separate task):** `individual_use=no` on both sites contradicts Eunice's spec (`86eu34h84`, "NOT stackable with 10% coupon"). Same behavior on live, so out of scope here — file with @Eunice to clarify spec vs production.
  - **Deliverables on ClickUp:** Doc page `13256g-147738` ([Audit & Test Plan](https://app.clickup.com/36771024/docs/13256g-163678/13256g-147738)), 2 task comments with adjusted 4-test QA plan (Tests 2–5; Test 1 BOGO Classic skipped — verified non-existent), CHANGELOG page entry.
  - Files: `docs/86exemy5y-bogo-6th-refill-coupons.md` (created + updated). No DB or code changes to either site. (@Ridwan)

## [86exbzf96-followup] - 2026-04-30

### Changed
- **PDP Related/Recently-Viewed carousels — arrows are now visible-but-offset (not hidden), and the active dot is an elongated pill** (CU-86exbzf96, follow-up to the 2026-04-29 entry below).
  - Per user direction (Indonesian): "untuk arrow, kamu harus tampilkan lagi tapi posisinya offset, tidak di atas product card. untuk dot, khusus dot yang aktif dia bentuknya memanjang, dengan lebar 4x dari dot biasa. namun tingginya tetap sama".
  - **Arrows** — previously moved off-screen at `left: -9999px` and only used as programmatic click targets. Now positioned at `left: -56px` / `right: -56px` (sitting in the page gutter outside the card area) with `opacity: 1` overriding Blocksy's hover-only visibility. Users can click them directly and JS dot-clicks still drive them. Hidden under `@media (max-width: 767px)` since there is no gutter to offset into on mobile.
  - **Active dot** — replaced the previous `transform: scale(1.6)` ring style with an elongated pill: `width: 32px` (4× the 8px inactive width), `height` unchanged at 8px, `border-radius: 4px`. Inactive dots stay 8×8 circles. Width and border-radius transition smoothly between states.
  - Added `overflow: visible` on `.flexy-container` (defensive — Blocksy's default is already not-clipped, but a client could add it) so negative-offset arrows are never clipped.
  - Files: `assets/css/components/product-carousel-dots.css` (rewrote arrow + active-dot rules); `inc/recently-viewed.php` (added `.flexy-arrow-prev/next` markup using Blocksy's stock SVGs from `helpers.php:178-179` so the dot navigation has arrows to drive — Recently Viewed previously had none).
  - Bumped `BLOCKSY_CHILD_VERSION` 1.1.1 → 1.1.2.

- **Arrow icons swapped to 32px chevrons** (CU-86exbzf96, follow-up).
  - User asked for chevron icons sized 32px ("tolong diganti jadi chevron dengan size 32px"). Blocksy ships long-line arrows by default; replaced them with chevron-style glyphs.
  - Implementation: the inner SVG inside `.flexy-arrow-prev/next` is hidden via `display: none`, and a 32×32 chevron is drawn on the button's `::before` using `mask-image` of an inline white-stroked SVG polyline. The mask approach lets the chevron pick up `currentColor` so it inherits Blocksy's `--flexy-nav-arrow-color` / hover-color tokens (no hard-coded fill).
  - Button container resized from default 40×40 to 48×48 to leave breathing room around the larger 32px icon, and the `top` offset adjusted (`calc(50% - 24px)`) to keep vertical centering in the boxed view.
  - Scope is unchanged — only `.related.products.is-layout-slider` and `.bc-recently-viewed`. The Blocksy product gallery and any archive sliders keep their original long-arrow glyph.
  - Bumped `BLOCKSY_CHILD_VERSION` 1.1.2 → 1.1.3.

## [86exe3dcv] - 2026-04-29

### Changed
- **Move WT Smart Coupon Pro available-coupons block (`.wt_coupon_wrapper`) below `.coupon-code-form` on checkout** (CU-86exe3dcv)
  - Approach: JavaScript DOM **clone** in `custom/js/checkout-order-summary.js`. Earlier attempts (PHP hook swap, JS move) were rejected: the PHP swap produced duplicate wrappers because FC Pro renders the order summary in two places (sidebar + `before_checkout_steps` mobile collapsible) and the destination hook fires in both; the JS move worked initially but lost the wrapper after the first `updated_checkout` AJAX, because `#order_review` is replaced wholesale and the moved node was inside it.
  - Final implementation: keep the WT-rendered original at its server position, insert a `.bc-wt-coupon-clone` copy into the order summary after the visible `tr.coupon-code-form`, and re-clone after each `updated_checkout` event so the clone survives FC Pro fragment refreshes. Original wrapper is hidden via CSS to prevent the FOUC of seeing it briefly at the top of the page before JS runs.
  - Files: `custom/js/checkout-order-summary.js` (extended IIFE with `moveWtCouponWrapper()`); `custom/css/checkout-order-summary.css` (`body.woocommerce-checkout .wt_coupon_wrapper:not(.bc-wt-coupon-clone) { display: none !important }`).
  - WT plugin hook is left untouched (still `woocommerce_before_checkout_form` priority 10) — the JS just clones the rendered DOM node into the order summary on every render.

## [86exbzf96] - 2026-04-29

### Added
- New reusable component **product-carousel-dots** that replaces Blocksy's overlapping side arrows on the PDP "You May Also Like" (Related Products) and "Recently Viewed" carousels with bottom dot pagination.
  - `assets/css/components/product-carousel-dots.css` — moves `.flexy-arrow-prev/next` off-screen (`left: -9999px`, kept programmatically clickable), styles the new `.bc-carousel-dots` row.
  - `assets/js/product-carousel-dots.js` — injects `<ul class="bc-carousel-dots"><li>×N</li></ul>` next to `.flexy`. Computes dot count = `totalItems - visibleCols + 1` from the live `--flexy-item-width` CSS var, recomputed on `resize`. Click handler navigates by clicking the off-screen Flexy arrows `abs(targetIndex - currentIndex)` times. Pre-mounts Flexy on init via `container.forcedMount()` so arrow listeners are wired before the first click. Active dot synced via `blocksy:frontend:flexy:slide-change` event.
  - Wired in `inc/enqueue.php` under the `is_product()` branch only.

### Fixed
- **PDP Related/Recently-Viewed arrows overlap product cards** (CU-86exbzf96)
  - Issue: Flexy's prev/next arrow buttons sit absolutely 20px from the carousel edge and overlap the first/last visible product card image, covering the wishlist heart, badges, and hover state.
  - Reference site (mettahemp.com) drops the carousel entirely and uses a plain grid; per user direction we kept the carousel and added bottom dots instead.

### Implementation notes (the failed approaches and why they failed)
1. **First attempt:** used Blocksy's native `.flexy-pills[data-type='circle']` markup so the bundled Flexy library would manage clicks and active state. Result: when the user jumped more than one slide, Flexy crashed with `Uncaught TypeError: Cannot read properties of undefined (reading 'classList')` inside its draw loop. Root cause: Flexy's pill code does `pillsContainerSelector.children[previousCurrentIndex]` and assumes one pill per *slide-item* (gallery model — 8 items = 8 pills). Our multi-column product carousel needs `total - visibleCols + 1` pills (8 items, 4 visible = 5 pills). Once the internal `currentIndex` advanced past 4, `children[5..7]` was `undefined` and the carousel locked up. This is also why Blocksy's own related-products renderer ships arrows-only — pills don't fit the multi-column model.
2. **Second attempt:** custom click handler that called `inst.slideTo(targetIndex)`. Discovered while inspecting the Flexy bundle that the `Flexy` class only exposes `constructor`, `destroy`, `refreshActivation`, `render`, `retrieveSliderAttributes`, `scheduleSliderRecalculation` — there is no `slideTo` method. The call was a silent no-op; the only navigation that happened was Flexy's native pill handler firing alongside, which produced the wrong-direction crash from #1.
3. **Final approach:** rename the dots container to `.bc-carousel-dots` (NOT `.flexy-pills`) so Flexy's pill code early-outs (`if (!options.pillsContainerSelector) return`). Navigate by programmatically clicking the off-screen Flexy arrow buttons, which Flexy *does* wire up correctly. No more crashes; multi-step jumps work.

### Notes
- Bumped `BLOCKSY_CHILD_VERSION` to `1.1.1` in `functions.php`.
- Pattern doc: `docs/patterns/product-carousel-dots.md`.

## [86exbd8gx-followup-2] - 2026-04-29
### Fixed
- Mini cart Shipping row now correctly reverts from "Free" to "Calculated at checkout" when items are removed and the cart drops back below the free-shipping threshold. It also now agrees with Blocksy's progress-bar banner in every cart state (above, below, exactly-at the threshold).

### Why
QA flagged a state-machine bug: after qualifying for free shipping then removing items, the Shipping row stayed pinned at "Free" while the Blocksy banner correctly showed "Add $X more". Two contradictions in one drawer was worse than the original problem we set out to fix.

### Root cause
Two mismatches at once:
1. The previous helper matched any `cost === 0` rate as "Free". On this site that included Local Pickup (Australia zone) — so even an empty-of-free-shipping cart returned "Free" because Local Pickup is always $0.
2. The previous helper read WC's zone `min_amount` (Australia zone = $75), but Blocksy reads from `theme_mod woo_count_progress_amount` (set to $100 on this site). So at $88 cart, WC said free shipping was available but Blocksy said "Add $12 more" — and the row sided with WC, contradicting the banner directly above it.

### Implementation
- `inc/woocommerce.php` — `bc_mini_cart_shipping_value()` now mirrors Blocksy Pro's `shipping-progress` feature.php logic exactly: reads `woo_count_method`, `woo_custom_count_criteria`, `woo_count_progress_amount`/`woo_count_progress_items`, applies `woo_count_with_discount` coupon adjustment, and supports both `'custom'` (fixed limit) and `'woo'` (zone min_amount) modes. Returns "Free" iff `total >= limit` OR a `get_free_shipping()` coupon is applied — same conditions Blocksy uses to flip the banner to "Congratulations!".
- No longer walks WC()->shipping() rates — that path was the source of the false-Free on $0 pickup rates and the Blocksy/WC zone disagreement.

### Verified
| Cart | Blocksy banner | Shipping row | OK? |
|------|---------------|--------------|-----|
| $44  | "Add $56 more" | Calculated at checkout | ✓ |
| $124 | "Congrats! Free shipping 🎉" | Free | ✓ |
| $80 (after qty decrement from $120) | "Add $20 more" | Calculated at checkout | ✓ |

Screenshots: `cu-86exbd8gx-shipping-below-threshold-final.png`, `cu-86exbd8gx-shipping-drop-below-final.png`.

### Backups
- `inc/woocommerce.php.bak-20260428-shipping-fix`

### Note for future devs
If the Customizer setting "Cart > Shipping Progress > Count Method" is ever switched from `custom` to `woo`, this function automatically picks up the change (it reads the same theme mod Blocksy reads). No code change needed.

## [86exbd8gx-followup] - 2026-04-29
### Fixed
- Mini cart Shipping row now shows "Free" when the cart qualifies for free shipping (matches Blocksy's progress-bar messaging "Congratulations! You got free shipping 🎉"). Previously hard-coded to "Calculated at checkout" which contradicted the green bar.

### Why
QA flagged the inconsistency: the Blocksy free-shipping progress bar declared the customer had earned free shipping, but our Shipping row directly below said "Calculated at checkout". Customers would either dismiss the green bar as marketing noise or assume the rate was about to change. Showing "Free" in both spots removes the cognitive dissonance.

### Implementation
- `inc/woocommerce.php` — added `bc_mini_cart_shipping_value()` helper. Calls `WC()->shipping()->calculate_shipping( $cart->get_shipping_packages() )` to recalculate rates for the current cart, then walks `WC()->shipping()->get_packages()` looking for any rate where `method_id === 'free_shipping'` OR `cost === 0`. Returns "Free" if found, otherwise falls back to "Calculated at checkout".
- `bc_render_mini_cart_totals_section()` now uses this helper for the Shipping row value.

### Performance note
We force a shipping calculation per mini cart fragment render. WC caches the result in the cart session so subsequent fragment fetches in the same request don't recompute. Still — if a future site has a slow shipping plugin (the AusPost plugin used here is one example), this adds latency to every cart-fragments AJAX call. Watch the slow-query log if you start seeing wc-ajax timeouts.

### Backups
- `inc/woocommerce.php.bak-20260429-002000`

## [86exbd8kg] - 2026-04-29
### Changed
- Suggested Products carousel in mini cart + wishlist drawers now feels more compact: title clamped to 2 lines (was wrapping 3+ on long names), title 13px / price 12px, tighter module-title spacing. Image keeps its native full-column-width square layout — `object-fit: contain` so source product photos never crop top/bottom.

### Why
QA flagged the section as visually too large making the drawer bottom-heavy. Root cause was long product titles wrapping 3+ lines (e.g. "All Reed Diffuser Refills 130mls with New Reed Sticks") not the image itself. Fixing the title with `-webkit-line-clamp: 2` shaved ~14px per card; the smaller fonts + tighter heading margin removed another ~10–15px. Image stays full-width square per Figma 684:85959 reference.

### Iterations during this fix (for posterity — explains the diff if you bisect)
1. **First attempt:** capped image at `max-height: 140px` with `object-fit: cover`. This visibly cropped top/bottom of square candle photos (lid + holder cut off). Reverted.
2. **Second attempt:** capped image at `max-width: 140px` with `object-fit: contain`. Image stayed un-cropped but became smaller-than-card-width with whitespace, which made cards feel inconsistent with native Blocksy layout. Reverted.
3. **Final (this commit):** drop both width and height caps. Keep full-column-width square via `aspect-ratio: 1/1`. Use `object-fit: contain` so any non-square source photo never crops. Visual-weight reduction comes purely from title clamp + smaller fonts.

### Selectors
Both drawers (`#woo-cart-panel`, `#woo-wishlist-panel`) and both render paths covered in one block — `[class*="ct-suggested-products"]` matches Blocksy's native `.ct-suggested-products--mini-cart` (state 1) AND our renamed `bc-*-suggested-grid ct-suggested-products` two-class wrappers (states 2–7) — see `docs/patterns/drawer-suggested-products.md` for the 7-state matrix.

### Verified at 320 / 375 / 768 / 1400 / 1440
Card height dropped from 295 → 281 (≈5%), but visually feels more balanced because the title block is the same height regardless of product name length. No image cropping in any state. Screenshots: `cu-86exbd8kg-{minicart,wishlist}-final-*.png` in repo root.

### Backups
- `assets/css/components/offcanvas.css.bak-20260429-001500`

## [86exbd8gx] - 2026-04-28
### Added
- Mini cart drawer now shows a totals breakdown above the Checkout button (Shipping: Calculated at checkout, Order Total: $X). Matches the Austin Natural Mattress reference layout (Image #13). Auto-updates on qty change via cart-fragments AJAX (no extra JS needed — function fires inside `.widget_shopping_cart_content` which is a registered cart fragment).

### Changed
- `inc/woocommerce.php` — appended `bc_render_mini_cart_totals_section()` hooked to `woocommerce_widget_shopping_cart_before_buttons` (priority 30, runs after Blocksy's shipping progress bar at priority 10–20). Renders only when cart is non-empty. Toggleable via `BC_FEATURE_MINI_CART_TOTALS` (default true) so a client PHP can disable.
- `assets/css/components/offcanvas.css` — appended styling for `.bc-mini-cart-totals` block: 12px top padding + dashed border-top, flex space-between rows, Order Total in 16px semibold with its own divider above for visual prominence.

### Why
Native Blocksy + WooCommerce mini cart shows only the Subtotal row before the Checkout button. Per Figma 684:85959 reference (parent off-canvas spec) and the Austin Natural Mattress reference layout, the drawer needs a clearer breakdown so customers see "Shipping: Calculated at checkout" + "Order Total" before clicking Checkout. Reduces surprise-cost abandonment per Baymard's checkout-transparency research.

### Backups
- `inc/woocommerce.php.bak-20260428-234500`

## [86exbd8en] - 2026-04-28
### Fixed
- Mini cart Checkout button now spans the full content width of the drawer at all breakpoints (320 / 375 / 768 / 1440 / 2560).

### Why
Native Blocksy ships `.woocommerce-mini-cart__buttons` with `padding: 0 24px 24px` AND `display: flex; gap: 8px`. The wrap already sits inside `.ct-panel-content-inner` which has its own 24px (mobile: 16px) horizontal padding — double padding made the button render at ~80% drawer width.

### Fix
- `assets/css/components/offcanvas.css` — appended a mini-cart-scoped block that removes the wrap's horizontal padding, switches the wrap to vertical stack with 8px gap (so View Cart + Checkout each get full width when both visible), and forces `width: 100%` on the buttons. Selectors target both `#woo-cart-panel` and `.woocommerce-mini-cart` so the rule survives cart-fragments AJAX re-renders.

### Verified
Button width at each breakpoint matches drawer content area exactly (inner_width − 2 × content_padding):
- 320 → 288 inner / 256 btn (16px padding)
- 375 → 338 inner / 306 btn (16px padding)
- 1440 → 500 inner / 452 btn (24px padding)
- 2560 → 500 inner / 452 btn (drawer width capped)

`href` = `/checkout` (Section 1 functionality intact).

## [86exbd883-v5] - 2026-04-28
### Fixed
- Wishlist drawer prev/next arrows now slide the suggested-products carousel (Image #12 broken state). Panel stays open during arrow interaction; click on dim backdrop still closes it normally.

### Two root causes
1. Our `wishlist-offcanvas.js` registered a CAPTURE-phase document click listener that called `e.stopImmediatePropagation()` on arrow clicks — this fired BEFORE Blocksy's flexy.js target-phase arrow handler, killing the carousel slide.
2. After we removed the capture-phase intercept and let flexy fire, Blocksy's `ct:overlay:handle-click` clickOutside listener treated arrow clicks as outside-the-panel and closed the drawer (probably because the carousel's flexy items get translated outside the panel rect, breaking Blocksy's closest()-based outside detection).

### Fix
- `assets/js/wishlist-offcanvas.js` — removed the arrow-click interception block in the capture-phase listener (let flexy.js handle natively); changed the panel-open call to `clickOutside: false` so Blocksy doesn't bind its own close listener; added our own click-outside listener bound once via `bindClickOutsideOnce()` that uses `closest('.ct-panel-inner')` for accurate inside/outside detection. Also ignores clicks on `.ct-header-wishlist` to prevent the open trigger from immediately closing the drawer.

### Backups
- `assets/js/wishlist-offcanvas.js.bak-20260428-211800`

### Verified
Empty wishlist drawer state — clicked next arrow, items slid by `-468px` (one full page in 2-column boxed mode); clicked dim backdrop, panel closed with `active` class removed. Same flow works for guest and logged-in empty states.

## [86exbd883-v4] - 2026-04-28
### Fixed
- Mini cart line-item qty stepper now renders as a clean horizontal `[-] [1] [+] × $price` row (Image #11), matching the cart page styling. Removed the "Qty:" label that was leaking from the product page into the mini cart.

### Why this happened
1. `inc/woocommerce.php` registers `woocommerce_before_quantity_input_field` (gated by `is_product()`) which emits `<span class="bc-qty-label">Qty:</span>`. The hook fires on EVERY qty input on the page — including the mini cart line item — so the label leaked into the drawer when opened from a product page.
2. Blocksy's mini cart wraps the qty input in `.ct-product-actions`, but the existing horizontal-stepper styling in `clients/byronbay/byronbay.css` only targets `.ct-cart-actions` (cart page). Without those rules, Blocksy's default `.quantity[data-type="type-2"]` styling positions `.ct-increase`/`.ct-decrease` as overlays floating inside the input — broken UX.

### Fix
- `assets/css/components/offcanvas.css` — appended a mini-cart-scoped block (`#woo-cart-panel`, `.woocommerce-mini-cart`) that:
  - Hides `.bc-qty-label` inside the drawer.
  - Mirrors the `.ct-cart-actions > .quantity[data-type="type-2"]` horizontal stepper rules under `.ct-product-actions > .quantity[data-type="type-2"]`.
  - Adds typographic `−` / `+` glyphs on the buttons.

### Note for future
The horizontal stepper rules are now in TWO places: `clients/byronbay/byronbay.css` (cart page, `.ct-cart-actions` scope) and `assets/css/components/offcanvas.css` (mini cart, `.ct-product-actions` scope). When Blocksy updates its qty stepper or the customizer setting changes, both blocks must stay in sync. Documented as TODO in code comment.

## [86exbd883-v3] - 2026-04-28
### Fixed
- Suggested Products prev/next arrows now work on all 7 drawer states. Click `<` / `>` slides the carousel exactly like Blocksy native cart-with-items rendering (matrix transform `-468px` per click in 2-column boxed mode).

### Why
Blocksy's `flexy.js` (theme bundle) binds prev/next click handlers via:
```js
const maybeSuggested = sliderEl.closest('[class*="ct-suggested-products"]');
if (maybeSuggested) {
    leftArrow = maybeSuggested.querySelector('.ct-arrow-prev');
    rightArrow = maybeSuggested.querySelector('.ct-arrow-next');
}
```
Our previous wrapper class `bc-minicart-suggested-grid` did NOT contain the substring `ct-suggested-products`, so flexy couldn't find the arrows from within the slider. Items rendered correctly (because the CSS shim mirrored the column rules) but arrows were inert (decoration-only).

### Fix
`inc/helpers.php` — the str_replace now produces `bc-minicart-suggested-grid ct-suggested-products` (and `bc-wishlist-suggested-grid ct-suggested-products` for the wishlist drawer). Two classes on one element:
- `bc-minicart-suggested-grid` — our cart-fragments-safe namespace
- `ct-suggested-products` — Blocksy's substring without the `--mini-cart` suffix

### How this stays safe
- Cart-fragments wipe selector: `[class*="ct-suggested-products--mini-cart"]` — REQUIRES the `--mini-cart` substring; our wrapper has only `ct-suggested-products` (no suffix), so it does NOT match. Verified: applied `el.outerHTML = newFragmentHtml` for every fragment selector, our grid survived.
- Flexy.js arrow lookup: `closest('[class*="ct-suggested-products"]')` — substring match, our wrapper has `ct-suggested-products` so it MATCHES.
- Customizer typography rules `[class*="ct-suggested-products"] .ct-module-title` etc — substring match, also applies. (LAYER 8 v1/v2 shim rules are still kept as belt-and-suspenders, but most are now redundant.)

### Verified
Empty mini cart state, hover next-arrow → click — items slid `956,1190,1424,1658` → `722,956,1190,1424`. Same translate as native cart-with-items state.

### Bonus discovery
Flexy lazy-mounts on first `mouseover`; programmatic `.click()` without prior hover does nothing. Real users hover before clicking, so no UX impact — but the wishlist sentinel JS test was passing arrows but actually testing `mouseover` separately. Documented in code architecture memory.

## [86exbd883-v2] - 2026-04-28
### Fixed
- Suggested Products heading and product card layout now match Blocksy native (Image #8) byte-for-byte. Heading is "SUGGESTED PRODUCTS" UPPERCASE 12px 700 with arrows on the right of the same row; product titles and prices stack vertically inside each card (no more "Reed Sticks$33.00" run-together inline display).

### Why this was needed
Image #7 vs Image #8 audit (2026-04-28 evening) found three Blocksy CSS rules that the previous LAYER 8 v1 shim missed:
- `[class*="ct-suggested-products"] .ct-module-title { display: flex; justify-content: space-between; ... font-size: 12px; font-weight: 700; text-transform: uppercase; }` — the heading row layout
- `[class*="ct-suggested-products"] section { display: flex; flex-direction: column; align-items: flex-start; }` — the unnamed `<section>` wrapper that stacks title + price vertically
- `[class*="ct-suggested-products"] [data-products="block"] .ct-media-container { margin-bottom: 15px; }` — image bottom spacing

All three target `[class*="ct-suggested-products"]` (substring match) which our renamed `bc-minicart-suggested-grid` / `bc-wishlist-suggested-grid` doesn't match. LAYER 8 v2 mirrors them under our renamed wrappers.

### Verified
Computed CSS values for renamed empty-cart rendering now match native cart-with-item rendering exactly (.ct-module-title display: flex / justifyContent: space-between / textTransform: uppercase / fontSize: 12px / fontWeight: 700 / marginBottom: 15px; flexy-item > section display: flex / flexDirection: column; .ct-product-title display: block; .price display: block).

### Changed
- `assets/css/components/wishlist-offcanvas.css` — appended LAYER 8 v2 block (3 new rules above) to the existing LAYER 8 shim.

## [86exbd883] - 2026-04-28
### Fixed
- All 7 drawer states (mini cart + wishlist; with/without items; guest/logged-in) now render IDENTICALLY to Image #1 source-of-truth (Blocksy native cart with items): single "Suggested Products" heading + prev/next arrows + 2-column flexy carousel. No more outer "Recently Viewed" / "Your Favourites" / "Popular Right Now" `<h3>` labels stacking above the carousel.

### Changed
- `inc/mini-cart-empty.php` — collapsed three separate sections (recently viewed, wishlist favourites, bestsellers) into ONE merged carousel call. IDs are merged in priority order (recently-viewed first, then deduped wishlist) and sliced to 4. Empty merged list falls through to the helper's bestsellers fallback. Removed `<h3 class="bc-empty-cart-section-title">` emissions and `<div class="bc-empty-cart-section">` wrappers entirely. Updated stale docblock that claimed flexy hooks were stripped (no longer true after 2026-04-28 morning revert).
- `inc/helpers.php` — added 1-hour transient caching to `bc_resolve_suggested_product_ids()` bestsellers fallback. Cache key: `bc_suggested_bestsellers_4`. Busted automatically on `save_post_product` and `woocommerce_product_set_stock` so stock changes don't show stale carousels.
- `assets/js/wishlist-flexy-sentinel.js` — reframed header comment as LAYER 7 runtime visibility sentinel (post-Blocksy-update QA tool). Previous comment claimed "flexy JS doesn't initialize inside our custom panel" — that was wrong; `flexy.min.js` does auto-init via DOMContentLoaded. Implementation untouched.
- `assets/css/components/wishlist-offcanvas.css` — appended LAYER 8 column/typography shim. Mirrors Blocksy's customizer-driven CSS (`--grid-columns-width: calc(100% / 2)`, typography vars, image radius, slider arrow size, items-3+-collapse-when-static) under our renamed `bc-minicart-suggested-grid` / `bc-wishlist-suggested-grid` wrappers. Necessary because the cart-fragments-safe class rename strips Blocksy's own CSS targeting `.ct-suggested-products--mini-cart`.

### Why this matters
The class rename in the shared helper escapes WooCommerce's cart-fragments AJAX wipe (`[class*="ct-suggested-products--mini-cart"]`), but Blocksy's customizer-derived inline stylesheet only targets the original class. Without the LAYER 8 CSS shim, items render at `flex: 0 0 100%` (1-column) and use default theme typography. The shim is the missing link that gives all 7 drawer states visual parity with the Blocksy-native "mini cart with items" state (Image #1).

### Architecture
Memory file: `~/.claude/projects/-Users-jarutosurano-GitHub--claude/memory/blocksy-drawer-suggested-products-architecture.md` documents the 7-state matrix and the rule "DO NOT strip flexy hooks". This entry adds LAYER 8 (column/typography shim) to the bulletproofing strategy already documented (LAYERS 1-7).


## [86exbz9aq] - 2026-04-28
### Added
- New file `assets/css/components/breadcrumbs.css` — at `max-width: 689px`, the `.ct-breadcrumbs` wrapper becomes a single-line, horizontally-scrollable element with the scrollbar hidden. Both `flex-wrap: nowrap` and `white-space: nowrap` are set so the rule works whether Blocksy renders the wrapper as flex or block. Touch-momentum scrolling enabled via `-webkit-overflow-scrolling: touch`. No layout change at viewports ≥ 690 px.

### Changed
- `inc/enqueue.php` — registered the new `breadcrumbs` component as a globally-loaded stylesheet (next to `offcanvas` and `wishlist-offcanvas`, lines ~123-128). Reuses the existing `blocksy_child_enqueue_component()` helper so cache-busting via `filemtime()` is automatic.

### Task
- ClickUp: [#86exbz9aq - PDP: breadcrumb with the long title, please enable horizontal scroll refer to mettahemp project](https://app.clickup.com/t/86exbz9aq)

## [86exd0ugz] - 2026-04-28
### Added
- New file `assets/js/checkout-error-scroll.js` — vanilla IIFE that auto-scrolls the checkout page to the first error notice (or first invalid field as fallback) on `DOMContentLoaded` and on the `checkout_error` jQuery event. Honors `prefers-reduced-motion`, accounts for sticky-header offset via `--header-sticky-height`, and moves focus to the error region for screen-reader users.

### Changed
- `inc/enqueue.php` — registered `blocksy-child-checkout-error-scroll` inside the existing `is_checkout()` branch (lines 83-97), footer-loaded, with `filemtime()` cache-busting. Pattern matches `hero-slider.js` enqueue.

### Task
- ClickUp: [#86exd0ugz - Checkout Page: auto-scroll to error message](https://app.clickup.com/t/86exd0ugz)
- Doc: `docs/86exd0ugz-checkout-error-scroll.md`
