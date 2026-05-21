# Value Proposition Cards

Horizontal card grid showcasing brand values (e.g., Clean Ingredients, Plant Based).

## Usage

Used on the homepage below "Hand-Poured with Love" heading. Can be reused on any page.

## Block Structure

```
wp:group (className: bc-value-props, padding: 40px 24px 48px)
  wp:columns (className: bc-value-cards, isStackedOnMobile: false)
    wp:column × N
      wp:group (bg: #FAF6F2, borderRadius: 12px, padding: 32px 24px)
        wp:shortcode → [bc_icon name="verified" size="32"]
        wp:heading (H3, 20px, 600 weight, palette-color-1, quicksand)
        wp:paragraph (16px, palette-color-4, lineHeight: 1.6)
      /wp:group
    /wp:column
  /wp:columns
  wp:buttons (centered) → CTA
/wp:group
```

## Key Components

### Icon Shortcode
- `[bc_icon name="verified" size="32"]` — renders the verified badge SVG
- Registered in `inc/icons.php`
- Add new icons to `blocksy_child_get_icons()` registry
- Wrapper class: `.bc-icon--{name}`

### CSS Grid Layout
- **Mobile (≤999px):** 2 columns, 16px gap
- **Desktop (>999px):** 4 columns, 32px gap
- Cards stretch to equal height via `height: 100%` + `min-height: 214px`
- CSS lives in `clients/byronbay/byronbay.css` (client-specific)
- Classes: `.bc-value-cards` (grid container), `.bc-value-card-icon` / `.bc-icon--verified` (icon spacing)

## Why Not Native Block Settings

| Need | Why CSS |
|------|---------|
| Equal-height cards | Gutenberg columns has no `items-stretch` |
| 2-col at mobile (not 1) | Gutenberg stacks to 1-col on mobile by default |
| Fixed 214px min-height | Not a block setting |
| Responsive gap (16px → 32px) | Block gap doesn't change per breakpoint |

## Files

- `inc/icons.php` — `[bc_icon]` shortcode + SVG registry
- `inc/loader.php` — loads icons.php as core module
- `clients/byronbay/byronbay.css` — grid layout + icon spacing CSS
- Post 645429 — homepage block content
