# Pattern — Checkout Step Form

> **Module:** `inc/checkout-step-form.php` + `assets/css/components/checkout-step-form.css`
> **Feature key:** `checkout-step-form`
> **Loaded on:** `is_checkout()`
> **Depends on:** Fluid Checkout (`class FluidCheckout`)
> **Figma:** Byron Bay Candles checkout spec (form column)
> **Refactored from:** `custom/checkout-step-form.{php,css}` (2026-05-08, audit P1).

## What it does

Two custom UI elements injected into the Fluid Checkout step-form column, plus matching styling:

1. **Guest description paragraph** — for non-logged-in users only, above the email/phone fields:
   > "Checking out as a Guest? You'll be able to save your details to create an account with us later."
   - Hook: `fc_checkout_before_contact_fields` (priority 5).

2. **Newsletter opt-in checkbox** — below contact fields:
   > "Sign me up to receive email updates and member only offers (Optional)"
   - Hook: `fc_checkout_contact_after_fields` (priority 20).
   - Saved to order meta as `_bc_newsletter_optin = 'yes'` via `woocommerce_checkout_update_order_meta`.

## Why a feature flag

Both texts and the newsletter consent flow are BBC-specific. Other clients should never inherit them. Disable by removing `checkout-step-form` from `manifest.json → features`.

## Files

```
inc/checkout-step-form.php           PHP hooks (3 actions)
assets/css/components/checkout-step-form.css   ~21 KB Figma-spec styling
```

## Hooks

| Hook | Priority | Effect |
| --- | --- | --- |
| `fc_checkout_before_contact_fields` | 5 | Guest description paragraph (logged-out only) |
| `fc_checkout_contact_after_fields` | 20 | Newsletter opt-in checkbox |
| `woocommerce_checkout_update_order_meta` | 10 | Save opt-in to order meta |

## Failure modes

- **FC Pro inactive** → file early-returns.
- **WC inactive** → loader gate.
- **User logged in** → guest description suppressed.

## Related

- `inc/checkout-order-summary.php` — sibling module for the order-summary column.
- `inc/checkout-trust-badges.php` — sibling module for the trust section below the form.

## TODO (separate ticket)

- Extract Byron-specific colors / fonts from the 21 KB CSS into client-scoped overrides via CSS custom properties.
- Wire the `_bc_newsletter_optin` meta into Klaviyo / Mailchimp sync if marketing wants automated opt-in.
