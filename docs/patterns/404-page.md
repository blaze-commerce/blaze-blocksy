# Pattern: 404 Page

Branded 404 page rendered via a Blocksy Pro Content Block (`template_type = '404'`). DB-driven, fully client-editable in Gutenberg.

## Baymard UX checklist

1. **Clear error code** — "404 — Page not found" eyebrow above the headline
2. **Friendly, non-blaming tone** — candle-themed metaphor ("The flame has flickered out")
3. **Branding preserved** — Blocksy header + footer intact (Content Block only replaces the main template area)
4. **Prominent site search** — FiboSearch bar matching header styling
5. **Primary recovery action** — "Shop All Candles" fill button
6. **Home link** — "Back to Homepage" outline button
7. **Popular products recovery** — 4-product bestseller grid (WooCommerce `[products]` shortcode, orderby=popularity)
8. **Category shortcuts** — 4 outline buttons (Candles / Diffusers / Gifts / Gift Cards)
9. **Social proof** — Customer reviews block embedded before footer

## Content Block config

| Field | Value |
|---|---|
| Post type | `ct_content_block` |
| `template_type` meta | `404` |
| Conditions | `include` → `rule: 404` (Blocksy's built-in 404 conditions rule) |

The `404` conditions rule matches WordPress's `is_404()`. No location hook needed — Blocksy's 404 Content Block framework takes over the main template area when a 404 is triggered.

## Structure

```
.bc-404
├── .bc-404__hero                       (group: vertical flex, centered)
│   ├── .bc-404__icon                   (wp:image — aromatic-candle-1.png)
│   ├── .bc-404__eyebrow                (wp:paragraph — "404 — PAGE NOT FOUND")
│   ├── .bc-404__title                  (wp:heading h1)
│   ├── .bc-404__lead                   (wp:paragraph — supportive copy)
│   ├── .bc-404__search                 (wp:shortcode [fibosearch])
│   └── .bc-404__ctas                   (wp:buttons — Shop / Home)
├── h2.bc-section-heading               (decorated — "explore our bestsellers")
├── [products limit=4 columns=4 orderby=popularity]
├── h2.bc-section-heading               (decorated — "browse by collection")
├── .bc-404__categories                 (wp:buttons × 4)
└── <!-- wp:blocksy/content-block {"content_block":"645402"} /-->    (reviews)
```

## CSS

Client-specific styling lives in `clients/byronbay/byronbay.css` under the `.bc-404` namespace. Covers:

- Hero icon sizing (96px max, responsive)
- Eyebrow typography (uppercase, tracked, muted)
- Headline scale (48px desktop → 32px mobile)
- Lead paragraph measure (540px max)
- Search bar — duplicated from `[data-id="search"]` rules, scoped to `.bc-404`
- Outline button contrast fix (dark text on white, dark fill on hover)
- Section spacing + reviews top margin

**Reusable components used:**
- `bc-section-heading` — decorated h2 with flanking lines (from `assets/css/components/homepage.css`)
- FiboSearch styles — currently duplicated; extract to `components/fibosearch-base.css` when a third use-case appears

## Embedding another Content Block

The Customer Reviews section uses Blocksy's native block:
```html
<!-- wp:blocksy/content-block {"content_block":"645402"} /-->
```
This renders another Content Block by ID. The referenced block (645402 — "Customer's Share their Experience") stays hook-attached to `blocksy:content:bottom` for singulars. Embedding it here bypasses the conditions check (`match_conditions => false`), which is why 404 (not a singular) can still render it.

## Editing

1. **Copy / headlines / bullets:** WP Admin → Content Blocks → "404 Page — The flame has flickered out" → Gutenberg
2. **Featured products:** Change the `[products]` shortcode attributes (limit, orderby, category, ids, etc.)
3. **Category URLs:** Click each category button → Link editor
4. **Hero image:** Click the image block → Replace
5. **Reviews embed:** Update Content Block 645402 directly — this 404 page will re-render it automatically

## Files

- DB: `ct_content_block` post `645646` (staging ID, will differ on live after import)
- CSS: `clients/byronbay/byronbay.css` — `.bc-404` section (~150 lines)
- No PHP code

## Go-Live

Included in `GO-LIVE.md` Phase 7i (Import Blocksy Content Blocks). Ensure post 645402 (reviews) is imported BEFORE 645646 (404) so the embed reference resolves, OR update the embed's `content_block` attribute to the new imported ID after import.
