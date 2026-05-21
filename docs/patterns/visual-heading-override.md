# Visual Heading Override — `bc-visual-h2`

## Purpose

Makes any heading tag visually match H2 Blocksy Customizer typography. Use when the semantic heading level (for SEO) differs from the visual hierarchy.

**Common use case:** An `<h1>` that should look like the section `<h2>` headings.

## How to Use

In the Gutenberg editor:
1. Select the heading block
2. Open **Advanced** > **Additional CSS class(es)**
3. Add `bc-visual-h2`

Combine with `bc-section-heading` for decorative lines:
```
bc-section-heading bc-visual-h2
```

## What It Controls

| Property | Value | Source |
|----------|-------|--------|
| Font family | Quicksand, sans-serif | Customizer > Typography > H2 |
| Font weight | 600 | Customizer > Typography > H2 |
| Font size (desktop) | 36px | Customizer > Typography > H2 |
| Font size (tablet) | 36px | Customizer > Typography > H2 |
| Font size (mobile) | 20px | Customizer > Typography > H2 |
| Line height | 1.2 | Customizer > Typography > H2 |

## Important

- Values are **hardcoded** to match current H2 Customizer settings
- If H2 typography changes in Customizer, update `base.css` > `.bc-visual-h2` to match
- Do **not** set font size or weight on the block itself — let the class handle it

## File Location

`assets/css/base.css` — loaded globally on all pages.

## Example

Homepage H1 "Scent your home the Byron Bay way":
- Tag: `<h1>` (SEO — only H1 on the page)
- Classes: `bc-section-heading bc-visual-h2`
- Result: H2-sized text with decorative horizontal lines
- No inline font-size, no inline font-weight, no custom color
