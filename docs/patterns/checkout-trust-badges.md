# Pattern — Checkout Trust Badges

> **Module:** `inc/checkout-trust-badges.php` + `assets/css/components/checkout-trust-badges.css` + `assets/images/payment-icons/{afterpay,paypal,secure,zip}.png`
> **Feature key:** `checkout-trust-badges`
> **Loaded on:** `is_checkout()`
> **Depends on:** Fluid Checkout (`class FluidCheckout`)
> **Figma:** Byron Bay Candles checkout spec, node 6054-109813, Section 8
> **Refactored from:** `custom/checkout-trust-badges.{php,css}` + `custom/images/*.png` (2026-05-08, audit P1).

## What it does

Renders a "Checkout Features" trust block on the FC checkout. Two variants share the same markup, attached to the same parent hook at different priorities:

| Variant | Hook | Priority | Visible at |
| --- | --- | --- | --- |
| `sidebar` | `fc_checkout_after_order_review` | 60 | desktop, inside `.fc-sidebar__inner` after `#fc-checkout-order-review` |
| `mobile` | `fc_checkout_after_order_review` | 70 | mobile, sibling AFTER `#fc-checkout-order-review` (so it lands below the Place Order button which FC Pro injects inside the wrapper) |

CSS toggles which variant is visible per breakpoint so only one shows at a time.

## Block content

- **Payment logos** (4) — PayPal, Secure (SSL badge), Afterpay, Zip — sourced from `assets/images/payment-icons/`.
- **Heading** — "Secure Payments" + checkmark glyph (`✓`).
- **Body** — "We protect your transaction with 256-bit SSL encryption and secure payment methods. Shop confidently, knowing your data is fully protected."
- **Contact paragraph**, per-site, sourced from the `bc_checkout_trust_contact_text` filter. Empty by default (renders nothing) since 2026-08-27; a site with no filter shows no contact line rather than another client's real phone/address.

Each client adds its own contact copy via `add_filter( 'bc_checkout_trust_contact_text', fn() => '...' )`. BBC's own real number ("+61 2 6685 5478", Industry and Arts Estate in Byron Bay) used to be the hardcoded default for every site. It is no longer hardcoded, and byronbaycandles' own site still needs its own filter added to keep showing it.

## Why a feature flag

Other clients won't have the same payment logos or BBC's exact copy. Disable via `manifest.json → features`.

## Files

```
inc/checkout-trust-badges.php                         PHP config + render
assets/css/components/checkout-trust-badges.css       ~3.5 KB styling (mobile/desktop toggle)
assets/images/payment-icons/{paypal,secure,afterpay,zip}.png    ~13 KB total
```

## Hooks

| Hook | Priority | Effect |
| --- | --- | --- |
| `fc_checkout_after_order_review` | 60 | Render sidebar (desktop) variant |
| `fc_checkout_after_order_review` | 70 | Render mobile variant |

## Failure modes

- **FC Pro inactive** → file early-returns.
- **WC inactive** → loader gate.
- **Image file missing** → `<img>` tag still renders with broken-icon (alt text covers a11y). Add the missing PNG to `assets/images/payment-icons/`.

## Architecture note

Payment-icon images live in `assets/images/payment-icons/` (Layer 1 — reusable). They were originally in `custom/images/` (Byron-specific dir) before the 2026-05-08 refactor. Other clients can use them directly without copying.

## Related

- `inc/checkout-order-summary.php` — sibling module for the order-summary column.
- `inc/checkout-step-form.php` — sibling module for the form column.

## TODO (separate ticket)

- Expose `bc_checkout_trust_get_config()` payload via a `bc_checkout_trust_config` filter so other clients can override the phone number, copy, and logos without forking the module.
- Make `screen-reader-text` checkmark accessible (currently uses HTML entity `&#10004;` — should be wrapped or aria-hidden depending on UX intent).
