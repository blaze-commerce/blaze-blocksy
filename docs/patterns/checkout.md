# Checkout — Fluid Checkout Multi-Step Pattern

> **Requires:** Fluid Checkout Lite + Fluid Checkout Pro (active).
> The Blocksy Customizer WooCommerce → Checkout settings are **not** the source of truth — Fluid Checkout replaces the default WC checkout template entirely. All settings live in `wp_options` as `fc_*` keys.

---

## File Structure

```
blocksy-child/
├── assets/css/components/
│   └── woo-checkout.css              ← Button styling (Place Order, Next/Prev step)
├── inc/
│   └── enqueue.php                   ← Enqueues woo-checkout.css on is_checkout()
└── docs/patterns/
    └── checkout.md                   ← This file
```

No PHP hooks, no template overrides. Fluid Checkout owns the checkout rendering.

---

## Plugin Dependencies

| Plugin | Slug | Version (verified 2026-04-17) | Role |
|--------|------|-------------------------------|------|
| Fluid Checkout for WooCommerce | `fluid-checkout` | 4.2.0 | Replaces default WC checkout with multi-step layout |
| Fluid Checkout Pro | `fluid-checkout-pro` | 4.0.0 | Adds express checkout, progress bar style options, order-received customization |

Pro plugin source: `https://github.com/blaze-commerce/wp-premium-plugins`

---

## Required `fc_*` WP Options

Set via `wp option update <key> <value>` from the WP root. These are the verified settings that produce the 4-step focused checkout.

### Lite (fluid-checkout)

| Option | Value | Why |
|--------|-------|-----|
| `fc_checkout_layout` | `multi-step` | Enables 4-step flow (Contact → Shipping → Billing → Payment) |
| `fc_design_template` | `classic` | Matches Blocksy form styling; no external CSS needed |
| `fc_enable_checkout_progress_bar` | `yes` | Shows step indicator above checkout |
| `fc_enable_checkout_sticky_progress_bar` | `yes` | Progress bar sticks to top on scroll |
| `fc_checkout_progress_bar_style` | `bars` | Horizontal segmented bars (alternative: `dots`, `numbers`) |
| `fc_enable_checkout_sticky_order_summary` | `yes` | Order summary sidebar sticky on desktop |
| `fc_enable_checkout_validation` | `yes` | Inline field validation (disables browser default) |
| `fc_enable_checkout_coupon_codes` | `yes` | Shows coupon field in checkout |
| `fc_enable_checkout_hide_optional_fields` | `yes` | Optional fields collapse behind an "Add..." link |
| `fc_hide_site_header_footer_at_checkout` | `yes` | Removes Blocksy header/footer during checkout for focus UX |
| `fc_default_to_billing_same_as_shipping` | `yes` | Pre-checks the billing=shipping toggle |
| `fc_checkout_place_order_position` | `below_order_summary` | Places order button under the order summary, not after payment |
| `fc_fix_zoom_in_form_fields_mobile_devices` | `yes` | Prevents iOS Safari zoom on input focus |
| `fc_enable_checkout_express_checkout` | `yes` | Shows express buttons (Apple Pay, Google Pay, PayPal) if gateway supports |
| `fc_enable_checkout_express_checkout_inline_buttons` | `yes` | Inline layout for express buttons |
| `fc_enable_checkout_hide_optional_fields` | `yes` | Collapse optional fields behind link |
| `fc_optional_fields_link_label_lowercase` | `yes` | "add phone" instead of "Add phone" |
| `fc_checkout_column_layout` | `two_columns` | 2-column desktop layout (form left, summary right) |

### Pro (fluid-checkout-pro)

| Option | Value | Why |
|--------|-------|-----|
| `fc_pro_enable_order_received` | `yes` | Enhanced Thank You page (see `thankyou-page.md`) |
| `fc_pro_enable_order_received_widget_areas` | `yes` | Widget injection points on Thank You page |
| `fc_pro_checkout_billing_address_position` | `step_after_shipping` | Billing appears as its own step after shipping |
| `fc_pro_checkout_coupon_codes_position` | `substep_before_payment` | Coupon field inside payment step |
| `fc_pro_checkout_order_summary_collapsible_initial_state` | `collapsed` | Summary collapsed on mobile by default |
| `fc_pro_checkout_order_summary_position_mobile` | `site_header` | Summary sits in header area on mobile for persistent visibility |
| `fc_show_billing_section_highlighted` | `yes` | Visual emphasis on billing substep |
| `fc_show_shipping_section_highlighted` | `yes` | Visual emphasis on shipping substep |

---

## Customizer Settings — SKIPPED

Per Figma Notes (`ClickUp doc 13256g-163598 page 13256g-134138`):

> **Cart Page — SKIPPED** (using mini cart drawer + Fluid Checkout flow)
> **Checkout Page — SKIPPED** (Fluid Checkout plugin replaces default WooCommerce checkout template)

The Blocksy WooCommerce Checkout panel in Customizer has no effect — do not waste time tuning it.

---

## CSS — Button Styling

File: `assets/css/components/woo-checkout.css`

Aligns Fluid Checkout's step buttons to the Blocksy Customizer global button token:
- `buttonPadding` → `14px 24px`
- `buttonRadius` → `4px`
- `buttonMinHeight` → `40px`

Color tokens (inverted for strong CTA on dark background):
- Place Order / Next bg: `--theme-palette-color-1` (primary brown)
- Text: `--theme-palette-color-5` (white)
- Prev (outlined): `--theme-palette-color-3` text + `--theme-palette-color-12` border

Enqueued conditionally on `is_checkout()` via `inc/enqueue.php:83-85`. Not loaded on other pages.

---

## Expected Visual Result

1. User lands on `/checkout/` → Blocksy header + footer hidden; site logo replaced by FC minimal header
2. Progress bar shows 4 steps (Contact, Shipping, Billing, Payment) at top, sticky on scroll
3. 2-column layout (desktop): form fields left, order summary right (sticky)
4. Mobile: summary collapsed, sits in header area; form full-width
5. Buttons match Blocksy global button — brown bg, white text, 14px 24px padding, 4px radius, uppercase
6. On `Place Order` submit → redirects to `/checkout/order-received/{id}/` (see `thankyou-page.md`)

---

## Troubleshooting

- **Progress bar not appearing** → verify `fc_enable_checkout_progress_bar=yes` and `fc_enable_checkout_sticky_progress_bar=yes`
- **Site header still showing** → verify `fc_hide_site_header_footer_at_checkout=yes`; also check that Blocksy header isn't being injected via a must-use plugin
- **Button padding off** → ensure `woo-checkout.css` is enqueued (check `is_checkout()` returns true; some cache plugins can interfere)
- **Place Order button at wrong position** → check `fc_checkout_place_order_position=below_order_summary`

---

## Replicating on a New Client

1. Install + activate Fluid Checkout Lite + Pro (license required for Pro)
2. Copy the `fc_*` option matrix above via `wp option update`
3. Copy `assets/css/components/woo-checkout.css` (already in child theme)
4. Adjust palette vars in client-specific `clients/{client}/{client}.css` if primary CTA color differs
5. Verify at `/checkout/` end-to-end with test order

---

## Figma Alignment (Phase 2.5 — added 2026-04-17)

Reference: Figma Byron Bay Candles → Checkout frame node `6054:66809` (`https://www.figma.com/design/p9q4tgxQ24oiUagh56PuXw/Byron-Bay-Candles?node-id=6054-66809`)

### Key decisions after Figma verification

- **Header + footer are VISIBLE on checkout** per Figma — earlier `fc_hide_site_header_footer_at_checkout=yes` was reverted to `no`. Previously the team had flagged checkout as "SKIPPED" in Figma Notes, but the detailed Checkout frame shows a full BBC header + footer with announcement bar.
- **Primary CTA color is light blue `#D9EAF0` with dark text `#111`** — unusual choice but per Figma. Because this is client-specific (not a reusable pattern), the shared component CSS only defines structure; colors are tokenized via CSS variables and overridden in `clients/byronbay/byronbay.css` under `body.woocommerce-checkout`.
- **Progress bar is a single thin bar** (6px height, `#CFCFCF` bg, `#C1B19E` fill, rounded) with a "STEP X OF N" counter text above it — not Fluid's default segmented `bars` style. We keep `fc_checkout_progress_bar_style=bars` but CSS-override to render as a single bar; the counter is injected via the `fc_checkout_progress_bar_start` action in `inc/checkout-customization.php`.
- **Step/substep labels renamed** via Fluid Checkout filters (also in `inc/checkout-customization.php`):
  - `Contact` → `Account`
  - `Shipping` (step) / `Shipping address` (substep) → `Address`
  - `Billing` → `Shipping`
  - `Payment` / `Payment method` → `Order Payment`

### CSS variables exposed for client overrides

`assets/css/components/woo-checkout.css` exposes these for `clients/{client}/{client}.css`:

| Variable | Default (fallback) | Figma value for Byron Bay |
|----------|-------------------|---------------------------|
| `--fc-btn-primary-bg` | `var(--theme-palette-color-1, #000)` | `#D9EAF0` |
| `--fc-btn-primary-bg-hover` | `var(--theme-palette-color-2, #333)` | `#BFD6DD` |
| `--fc-btn-primary-text` | `var(--theme-palette-color-5, #fff)` | `#111111` |
| `--fc-progress-bar-bg` | `#CFCFCF` | `#CFCFCF` |
| `--fc-progress-bar-fill` | `#C1B19E` | `#C1B19E` |

### Files added/modified in Phase 2.5

- `inc/checkout-customization.php` — NEW: step/substep label filters + progress counter hook
- `inc/loader.php` — registered `checkout-customization` feature flag
- `assets/css/components/woo-checkout.css` — tokenized button colors + added progress bar rules + counter text styling
- `clients/byronbay/byronbay.css` — client-specific color overrides for Byron Bay Figma spec

### P2 items deferred (next session)

The following Figma spec items were intentionally deferred and should be addressed in a follow-up session:

- **Urgency timer** ("Due to limited supply, we've reserved your order for: X min Y sec") — needs PHP hook + CSS + JS countdown (~2-3h)
- **Guest Checkout / Sign in tabs** UI inside ACCOUNT step — needs `fc_pro_enable_account_matching=yes` + custom tab markup (~1-2h)
- **Input field styling** (48px tall, 8px radius, 1px border, placeholder Quicksand 300 14px) — CSS match (~30min)
- **Collapsed section cards** styling (72px, `#F3F4F5` bg, 8px radius) — CSS match (~20min)
- **Order Summary card** styling (border, radius, typography, "Order Summary" title + "1 Item" pill badge) — CSS match (~1h)
- **Shipping progress bar inside Order Summary** (Add $X to get free shipping) — hook into `fc_order_summary_section_content` (~30min)
- **"Checkout Feature" card** below summary (payment logos + Secure Payments copy + 60 Days Money Back Guarantee) — Content Block + hook injection (~1-2h)
- **Font decision** (Figma uses Inter + Quicksand; site uses Rubik + Museo Sans) — await designer clarification
- **"SECURE CHECKOUT" 48px heading** replacing default page title — depends on whether checkout retains page heading
