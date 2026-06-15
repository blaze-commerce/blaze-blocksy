# BBC container side-gutter — Customizer change 2026-04-29

> **If anything looks off with tablet/mobile horizontal padding, READ THIS FIRST.**
>
> **Local copy:** `~/GitHub/.claude/notes/container-spacing-2026-04-29.md` (gitignored, this Mac only)
> **Staging copy:** `wp-content/themes/blocksy-child/docs/notes/container-spacing-2026-04-29.md` (canonical, accessible from any PC via SSH `byronbaySITEBUILDKinsta`)
> **Trigger phrase for next session:** *"Revert the Blocksy contentEdgeSpacing change from 2026-04-29. Read the note at `docs/notes/container-spacing-2026-04-29.md` on staging (byronbaySITEBUILDKinsta) and follow the revert steps."*

## What we changed (current state — 2026-04-29 final)

Cam asked staging to match live (`byronbaycandles.com`) at 24px gutter. Live is on a different stack (Next.js + Tailwind `px-6`). On Blocksy-themed staging, the canonical knob is **`contentEdgeSpacing`** in the Customizer — a vw-based responsive setting that produces `(setting × 1)vw` of gutter on each side at the matching breakpoint.

**Two changes made (Claude tablet via WP-CLI, Cam mobile via Customizer UI):**

| | Desktop | Tablet | Mobile |
|---|---|---|---|
| Before (Blocksy default — same as Austin Natural Mattress build) | 5 | **5** | **6** |
| After Claude's WP-CLI change (2026-04-29 evening) | 5 | **3** | 6 |
| **FINAL — after Cam's manual UI tweak (mobile 6→3)** | **5** | **3** | **3** |

The `__changed: ["mobile"]` flag on the saved theme_mod confirms Cam touched the mobile slider in the Customizer (in addition to my WP-CLI tablet change).

Result at viewports (final state):

| Viewport | Computed gutter | Matches live? |
|---|---|---|
| 1024+ desktop | unchanged (5vw × 1024 ≈ 51px each side) | desktop unchanged |
| 768 tablet | 3vw × 768 = **23px** each side | live = 24px → 1px diff ✓ |
| 430 iPhone 14 Pro Max (Cam's test viewport) | 3vw × 430 = **12.9px** each side | Cam confirmed visual match ✓ |
| 375 mobile | 3vw × 375 = **11.25px** each side | tighter than live's 24px but Cam preferred this look |

**Why mobile final is 3vw not 6vw:** Cam's manual visual comparison at 430 (iPhone 14 Pro Max) showed 6vw produced gutters that felt too wide vs live. He preferred 3vw to match the visual proportion he saw on live at that specific viewport — even though my math earlier said 6vw was within 1.5px of live's 24px at 375. The visual comparison won over the px math.

## Why this is the canonical fix (not CSS override)

We went through TWO earlier attempts that were rolled back:

1. **V1 attempt (CSS override):** added `assets/css/components/container.css` with `body main .wp-block-group.is-layout-constrained { margin: 33px ... }`. Compounded on nested groups (depth-1 nested groups got 66/66 instead of 33/33). Reverted same day.

2. **V2 attempt (CSS override scoped to direct children):** changed selector to `body .entry-content > .wp-block-group.is-layout-constrained`. Fixed the compounding but still ad-hoc — didn't cover all block types (image, columns, cover at root).

**Final approach (this is what's deployed):** use Blocksy's own Customizer setting `contentEdgeSpacing`. Blocksy applies the resulting `--theme-container-edge-spacing` CSS variable uniformly to all `.ct-container` elements (header, footer, content) AND it propagates via `--theme-container-width` into the Gutenberg `is-layout-constrained` content size. Single source of truth, no CSS file, no nested-block compounding, survives theme updates.

## Files / state at end of 2026-04-29 (FINAL)

- **Theme mod `contentEdgeSpacing`** = `{desktop: 5, tablet: 3, mobile: "3", __changed: ["mobile"]}` (was `{5, 5, 6}` — Blocksy default)
- **CSS file `assets/css/components/container.css`** — DELETED (existed earlier today, removed when we switched to Customizer fix)
- **Enqueue `inc/enqueue.php`** — line `blocksy_child_enqueue_component( 'container', ... )` REMOVED
- **WC products grid override** — also removed (was inside container.css)
- **Header/footer override** — also removed (was inside container.css)
- **Backup files** in `assets/css/components/container*` — none exist
- **Remnant CSS sweep** — verified clean (no leftover container/spacing overrides anywhere in child theme)

## How to revert in a future session

**Tell Claude in next session:**

> "Revert the Blocksy contentEdgeSpacing change from 2026-04-29 on bbcv1.blz.au. Set theme_mod back to Blocksy default `{desktop: 5, tablet: 5, mobile: 6}` and flush Blocksy dynamic CSS cache."

**Exact one-line revert command Claude can run:**

```bash
ssh byronbaySITEBUILDKinsta "cd /www/byronbaycandlessitebuild_845/public && wp eval 'set_theme_mod(\"contentEdgeSpacing\", array(\"desktop\" => 5, \"tablet\" => 5, \"mobile\" => 6)); global \$wpdb; \$wpdb->query(\"DELETE FROM \$wpdb->options WHERE option_name LIKE \\\"%blocksy%dynamic%\\\"\"); update_option(\"blocksy-cache-version\", intval(get_option(\"blocksy-cache-version\", 0)) + 1);' 2>&1 | tail -3 && wp cache flush"
```

After that, gaps return to:
- Tablet 768: 5vw × 768 = **38px** (Blocksy default, matches Austin)
- Mobile 375: 6vw × 375 = **23px** (Blocksy default)
- Mobile 430 (iPhone 14 Pro Max): 6vw × 430 = **25.8px** (Blocksy default)

## Critical: Blocksy dynamic-CSS cache

Blocksy generates dynamic CSS from theme_mods and caches it. **Just changing the theme_mod is not enough** — also flush:

```bash
wp eval 'global $wpdb; $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE \"%blocksy%dynamic%\""); update_option("blocksy-cache-version", intval(get_option("blocksy-cache-version", 0)) + 1);'
wp cache flush
```

Without this flush, the page keeps serving the old `--theme-container-edge-spacing` value and you'll think the Customizer change didn't work.

## Verified FINAL state (2026-04-29 evening, after Cam's mobile tweak)

| Element | Tablet 768 | Mobile 375 |
|---|---|---|
| Header `.ct-container` | 23 / 722px | 11 / 353px |
| Footer `.ct-container` | 23 / 722px | 11 / 353px |
| Page content (`.bc-category-grid`, `.products`, `.wp-block-group`) | 23 / 722px | 11 / 353px |
| Nested groups | 23 (no compounding) | 11 (no compounding) |

All uniform. Cam's "feels wider" complaint is solved at the canonical Blocksy layer. Mobile 11px gutter is tighter than live's 24px but Cam preferred this look from his manual visual comparison at 430 viewport.

## How `contentEdgeSpacing` actually works (for next-Claude reference)

Blocksy converts the value via `100 - (setting × 2)`:
- setting `5` → `90vw` container width → 10vw split = 5vw each side gap
- setting `3` → `94vw` container width → 6vw split = 3vw each side gap
- setting `6` → `88vw` container width → 12vw split = 6vw each side gap

CSS variable chain (in `inc/dynamic-styles/global/all.php` line 318+):

```
--theme-container-edge-spacing: 94vw  (at @media tablet)
  → --theme-container-width-base: calc(94vw - 0px*2)
    → --theme-container-width: min(100%, 94vw)
      → applied as `width` on `.ct-container` and propagates into Gutenberg's content-size

```

Math at 768 viewport: `min(100%, 94vw)` = `min(768, 722.08)` = 722px → leftGap = (768-722)/2 = 23px ✓.
