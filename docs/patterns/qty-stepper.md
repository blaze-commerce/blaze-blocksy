# Pattern — Qty Stepper (horizontal layout)

> **Module:** `assets/css/components/qty-stepper.css`
> **Loaded:** globally (alongside `offcanvas.css`) — applies wherever Blocksy renders `.quantity[data-type="type-2"]`
> **Refactored:** 2026-05-08 (#54) — extracted from duplicate blocks in `clients/byronbay/byronbay.css` (PDP) + `assets/css/components/offcanvas.css` (mini cart).

## What it does

Renders Blocksy's `.quantity[data-type="type-2"]` qty input as a flat horizontal stepper `[ − | 1 | + ]` with shared dividers, instead of Blocksy's default absolute-positioned overlay buttons.

```
   ┌────┬────┬────┐
   │  − │  1 │  + │
   └────┴────┴────┘
```

## Why one shared file

Two contexts use the same structural pattern at different sizes:

| Context | Selector | Default knob values |
| --- | --- | --- |
| **PDP add-to-cart row** | `.ct-product-add-to-cart .ct-cart-actions > .quantity` | 36px button width, `--theme-button-min-height` (40px) tall, 45px input |
| **Mini cart drawer** | `#woo-cart-panel .ct-product-actions > .quantity` + `.woocommerce-mini-cart .ct-product-actions > .quantity` | 28px button width, 28px tall, 36px input |

Pre-refactor: both blocks had ~50 lines of identical structural rules + different sizing. When Blocksy updated its qty stepper or the Customizer setting changed, both blocks had to stay in sync — easy to forget.

Post-refactor: structural rules live ONCE in `qty-stepper.css`. Each context overrides only the size/color knobs via CSS custom properties.

## Knobs (CSS custom properties)

| Variable | Default | Purpose |
| --- | --- | --- |
| `--bc-qty-btn-width` | `36px` | Width of `−` and `+` buttons |
| `--bc-qty-btn-height` | `var(--theme-button-min-height, 40px)` | Height of buttons + input |
| `--bc-qty-input-width` | `45px` | Width of the number input |
| `--bc-qty-border` | `var(--theme-border-color, #c0c0c0)` | Border color of all 3 elements |
| `--bc-qty-radius` | `var(--theme-form-field-border-radius, 3px)` | Outer corner radius (left side of `−`, right side of `+`) |

## Usage example — adding the stepper to a new context

```css
/* If the new context uses a non-default size, override the knobs: */
.my-new-context .quantity[data-type="type-2"] {
    --bc-qty-btn-width: 32px;
    --bc-qty-btn-height: 32px;
    --bc-qty-input-width: 40px;
}
```

That's it. The structural rules in `qty-stepper.css` will adopt the new sizes automatically.

## Files

```
assets/css/components/qty-stepper.css       Layer 1 reusable (this file)
clients/byronbay/byronbay.css                PDP block — now empty (defaults match)
assets/css/components/offcanvas.css          Mini cart block — only 4 var overrides + label hide
inc/enqueue.php                              Globally enqueued (line ~187)
```

## `!important` density note

The structural rules use `!important` on key properties (display, position, width, height, border, padding) because they fight Blocksy's default `.quantity[data-type="type-2"]` rules. Selector specificity isn't enough — Blocksy ships the type-2 rules with the same base specificity. See also: F3/F4 reclassification entry in CHANGELOG.

## Related

- `inc/woocommerce.php` — `BC_FEATURE_QTY_LABEL` toggle controls the `.bc-qty-label` insertion (PDP only). Mini cart hides the label via `display: none` since it leaks from the same hook.
- `clients/byronbay/byronbay.css:344-368` — wrapper rule for `.ct-cart-actions > .quantity` (the flex container that hosts the stepper + adjacent ATC button + wishlist button).

## Failure modes

- **Blocksy parent updates type-2 rules** → may need to add new `!important` rules here. Run a Playwright matrix at PDP + mini cart to confirm.
- **Customizer Quantity Field type changes from "type-2" to another** → this file no longer applies. The default Blocksy stepper would render. Switch back to type-2 via Customizer if the design relies on this layout.
- **Container missing `.ct-product-add-to-cart` flex parent on PDP** → buttons may collapse. Check `clients/byronbay/byronbay.css:336-341` is still present.
