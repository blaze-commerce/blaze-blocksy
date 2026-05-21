# Pattern — Checkout Order Summary

> **Module:** `inc/checkout-order-summary.php` + `assets/js/checkout-order-summary.js` + `assets/css/components/checkout-order-summary.css`
> **Feature key:** `checkout-order-summary`
> **Loaded on:** `is_checkout()`
> **Depends on:** Fluid Checkout Pro (`class FluidCheckout`)
> **Figma:** Byron Bay Candles node 6054-109813
> **Refactored from:** `custom/checkout-order-summary.{php,css,js}` (2026-05-08, audit P1).

## What it does

Reshapes the Fluid Checkout Pro order-summary block to match the Byron Bay Candles checkout spec. Behaviorally:

- Forces mobile order summary into the `before_checkout_steps` slot (FC Pro's native collapsible toggle).
- Renames the order-review title to **"Order Summary"**.
- Renames the FC Pro mobile collapsible toggle to **"Show Order Summary"** / **"Hide Order Summary"**.
- Appends an item-count pill badge after the order-review title (desktop) and a separate header + badge for mobile.
- Moves coupon codes inside the order summary (vs FC Lite's default top-of-page slot).
- Renames the coupon toggle label to **"Coupon Code/Gift Voucher"** and the coupon input placeholder to **"Enter promo code"**.
- Replaces the cart-line-item remove `<a>` text with a trash SVG icon (with `screen-reader-text` fallback).
- Splits the cart line-item product name from its variation onto separate lines (`<dl class="variation"><dd>...</dd></dl>` format).
- **Disables** Blocksy's Companion Pro suggested-products carousel on checkout (via `theme_mod_checkout_suggested_products = 'no'`).
- **Disables** Blocksy's free-shipping progress bar on checkout (via `theme_mod_woo_shipping_progress_in_checkout = 'no'`).

## Why a feature flag

Behavior is BBC-specific and Fluid-Checkout-coupled. Other clients without FC Pro should never load this module. The PHP also bails early via `if ( ! class_exists( 'FluidCheckout' ) ) return;` — but the manifest flag prevents even loading the file.

## Files

```
inc/checkout-order-summary.php       PHP hooks + filters (FC Pro + Woo)
assets/js/checkout-order-summary.js  IIFE (DOM polish, runs on every updated_checkout)
assets/css/components/checkout-order-summary.css   ~58 KB Figma-spec styling
```

## Filters / hooks

| Hook | Filter / action | Effect |
| --- | --- | --- |
| `theme_mod_checkout_suggested_products` | filter | force `'no'` |
| `theme_mod_woo_shipping_progress_in_checkout` | filter | force `'no'` |
| `option_fc_pro_checkout_order_summary_position_mobile` | filter | force `'before_checkout_steps'` |
| `fc_order_review_title` | filter | "Order Summary" |
| `fc_pro_checkout_order_summary_collapsible_toggle_title_text` | filter | "Show Order Summary" |
| `pre_option_fc_pro_checkout_coupon_codes_position` | filter (priority 20) | `'inside_order_summary'` |
| `fc_expansible_section_toggle_label_coupon_code` | filter | "Coupon Code/Gift Voucher" |
| `fc_coupon_code_field_placeholder` | filter | "Enter promo code" |
| `woocommerce_cart_item_remove_link` | filter | replace `<a>` content with trash SVG |
| `woocommerce_cart_item_name` | filter | split parent name + variation onto separate lines |
| `fc_checkout_after_order_review_title_after` | action | item-count pill badge (desktop) |
| `woocommerce_checkout_order_review` | action | mobile header + badge |

## Failure modes

- **FC Pro inactive** → file early-returns. No fatal.
- **WC inactive** → loader gate prevents file load.
- **Figma values drift** → CSS file is the source of truth; PHP doesn't hardcode design values.

## Related

- `inc/checkout-step-form.php` — sibling module for the form column.
- `inc/checkout-trust-badges.php` — sibling module for the trust section.
- `inc/loader.php` — feature-gated load.
- `inc/enqueue.php` — feature-gated CSS + JS enqueue.

## TODO (separate ticket)

- Extract Byron-specific colors (`#D9EAF0`, `#BFD6DD`, `museo`) from the 58 KB CSS into CSS custom properties in the component file with overrides in `clients/byronbay/byronbay.css`. Mirrors the `--fc-btn-primary-bg` pattern in `assets/css/components/woo-checkout.css`.
