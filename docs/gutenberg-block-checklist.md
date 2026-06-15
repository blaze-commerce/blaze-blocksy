# Gutenberg Block Checklist

Every page element must go through this checklist before implementation.
**Rule: Use native Gutenberg/Blocksy settings first. CSS only for what blocks can't do.**

---

## Step 1: Choose the Right Block

| Need | Block | NOT this |
|------|-------|----------|
| Text | Paragraph | wp:html |
| Title | Heading (H2-H6) | Paragraph with big font |
| Image | Image / Gallery | wp:html with img tag |
| Video | Video (alignfull) | wp:html with video tag |
| Link button | Buttons | Paragraph with styled link |
| Layout row | Group (flex) | wp:html with divs |
| Layout columns | Columns | wp:html grid |
| Product grid | Product Collection / Products | Custom shortcode |
| Spacer | Spacer | Empty paragraphs |
| List | List | Multiple paragraphs |

---

## Step 2: Use Block Settings (NOT Inline Styles)

| Property | Where to set | NOT this |
|----------|-------------|----------|
| Font size | Typography → Size | `style="font-size:24px"` |
| Font weight | Typography → Appearance | `style="font-weight:400"` |
| Font family | Typography → Font Family | hardcoded in CSS |
| Text decoration | Typography → Decoration | CSS `text-decoration` |
| Text transform | Typography → Letter Case | CSS `text-transform` |
| Letter spacing | Typography → Letter Spacing | CSS `letter-spacing` |
| Line height | Typography → Line Height | CSS `line-height` |
| Text color | Color → Text | `style="color:#888"` |
| Background | Color → Background | `style="background:#FAF6F2"` |
| Link color | Color → Link | CSS `a { color: }` |
| Padding | Dimensions → Padding | `style="padding:24px"` |
| Margin | Dimensions → Margin | `style="margin:0"` |
| Border radius | Border → Radius | CSS `border-radius` |
| Alignment | Toolbar → Align | CSS `text-align` |
| Block width | Toolbar → Wide/Full | CSS `width: 100vw` |

---

## Step 3: Use Blocksy Customizer (NOT Block-Level Overrides)

These should be set ONCE in Customizer, inherited everywhere:

| Setting | Customizer location |
|---------|-------------------|
| H1-H6 typography | General → Typography → Headings |
| Body font | General → Typography → Body |
| Color palette | General → Colors |
| Button style | General → Buttons |
| Content width | General → Layout |
| Border colors | Stored in palette (color-16) |

**Do NOT override these per-block unless the design explicitly differs from the global setting.**

---

## Step 4: CSS Only For These Cases

| Case | Example | Why CSS needed |
|------|---------|---------------|
| Hover states | Link hover color | Gutenberg has no hover UI |
| Transitions | Image scale on hover | No block setting |
| Animations | Fade in, slide | No block setting |
| Pseudo-elements | ::before/::after lines | No block equivalent |
| Slider/carousel | Product slider track | Custom behavior |
| Complex responsive | Different layout at breakpoints | Limited block responsive |
| WooCommerce overrides | Product card tweaks | Theme template output |

---

## Step 5: Add CSS Class (NOT Inline Styles)

When CSS is needed:
1. Add class via block's **Advanced → Additional CSS class(es)**
2. Write the CSS in the appropriate component file
3. Target the class, not the element type

**Naming convention:** `bc-{section}-{element}`
- `bc-section-heading` — decorated heading with lines
- `bc-see-all-link` — see all link hover state
- `bc-hero-video` — hero video sizing
- `bc-hero-slider` — hero slider container
- `bc-product-slider` — product carousel wrapper

---

## Step 6: Verify Editability

Before shipping, open the block editor and confirm:
- [ ] Client can change text by clicking on it
- [ ] Client can change colors via the sidebar panel
- [ ] Client can change font size via typography panel
- [ ] Client can swap images/links via block toolbar
- [ ] No "Edit as HTML" needed for any content change
- [ ] Block doesn't show as "wp:html" (use native blocks)

---

## Responsive Spacing Tips

**Block Spacing** (Dimensions panel → Block Spacing) controls the gap between child blocks when they stack on mobile. This is the key setting for:
- **Columns block** — controls the vertical gap between columns when stacked on mobile
- **Media & Text block** — controls gap between media and content when stacked
- **Group (flex/grid)** — controls gap between flex children

Use this instead of CSS for mobile spacing. The value applies when blocks stack vertically at the mobile breakpoint.

**Padding** can be set per-breakpoint by clicking the device icon (desktop/tablet/mobile) next to the Padding controls in the Dimensions panel. Same for Margin.

---

## Anti-Patterns (DO NOT DO)

| Bad | Good |
|-----|------|
| `wp:html` block for text | Paragraph/Heading block |
| `style="color:#888"` on paragraph | Set text color from palette |
| `style="font-size:24px"` inline | Typography panel preset |
| Custom PHP rendering product cards | WooCommerce Product Collection block |
| Shortcode for static content | Native blocks |
| Hardcoded hex colors | Palette variables |
| CSS for font-size/weight/color | Block typography settings |
