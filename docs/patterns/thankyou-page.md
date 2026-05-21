# Thank You / Order Received Page — Fluid Checkout Pro Built-In

> **Requires:** Fluid Checkout Pro (active).
> **Decision:** Use FC Pro built-in Order Received feature. No child theme template override, no custom `woocommerce_thankyou` hook. Zero custom code — configured entirely via `wp_options`.

---

## File Structure

```
blocksy-child/
└── docs/patterns/
    └── thankyou-page.md              ← This file
```

No theme-side files. The Thank You page is rendered by `wp-content/plugins/fluid-checkout-pro/inc/order-received-page.php` when `fc_pro_enable_order_received=yes`.

---

## How It Works

1. After `Place Order` submit, WooCommerce redirects to `/checkout/order-received/{order_id}/`
2. `FluidCheckout_PRO_OrderReceivedPage::is_feature_enabled()` checks `fc_pro_enable_order_received` option
3. If enabled, FC Pro replaces the default WC order-received output with:
   - Enhanced success/failed notice (alerts for failed/on-hold payment statuses)
   - Order details overview section (items, totals, gift options)
   - Order actions (Pay for Order / View Order / downloads if applicable)
   - Optional sidebar with widget area injection points (requires `fc_pro_enable_order_received_widget_areas=yes`)
4. If disabled, falls back to WC core template (Blocksy default styling)

---

## Required `fc_*` WP Options

| Option | Value | Purpose |
|--------|-------|---------|
| `fc_pro_enable_order_received` | `yes` | Master toggle for FC Pro Thank You feature |
| `fc_pro_enable_order_received_widget_areas` | `yes` | Enables widget areas for custom content injection |
| `fc_pro_enable_order_details_wide_layout` | (default) | When enabled, order details use full width; when disabled with widget areas active, shows sidebar |
| `fc_pro_order_details_order_summary_position` | (default) | Where order summary appears on the page |
| `fc_pro_order_details_order_actions_position` | (default) | Where order action buttons appear |

Set via `wp option update` from WP root. See `checkout.md` for full option matrix.

---

## Styling

No custom CSS added for the Thank You page. The current approach relies on:
1. FC Pro's own stylesheet (enqueued automatically when feature is enabled)
2. Blocksy's WooCommerce default template styling (typography, spacing, containers)
3. Theme palette variables (inherited from Blocksy Customizer)

If a client asks for deeper Thank You page customization later, see "Future Customization Options" below.

---

## Expected Visual Result

1. Customer lands on `/checkout/order-received/{id}/` after successful payment
2. Large success notice ("Thank you. Your order has been received.") with brand color
3. Order details card with:
   - Order number, date, email, total, payment method
   - Line items (thumbnails, name, qty, price)
   - Totals (subtotal, shipping, tax, total)
4. (Optional) Sidebar with widget areas — empty by default; content blocks can be injected via WP admin → Appearance → Widgets → "FC Order Received" area
5. (Failed payment) Alert-style notice instead of success — guides user to retry payment

---

## Future Customization Options (Not Currently Implemented)

If a client requires a custom Thank You page beyond FC Pro built-in:

### Option A — FC Pro Widget Areas (Recommended, no code)
- WP admin → Appearance → Widgets → inject Blocksy Content Block into "FC Order Received" sidebar
- Best for: brand messaging, upsell CTAs, social share buttons

### Option B — Child Theme Template Override
- Create `woocommerce/checkout/thankyou.php` in `blocksy-child/`
- Copy starting template from `wp-content/plugins/fluid-checkout-pro/templates/fc-pro/order-received/checkout/thankyou.php`
- Full control but higher maintenance burden
- Best for: layout restructures that FC Pro settings can't achieve

### Option C — `woocommerce_thankyou` Action Hook
- Add `add_action('woocommerce_thankyou', fn($order_id) => ...)` in `inc/hooks.php` or a new module
- Inject Blocksy Content Block output
- Best for: conditional content (e.g., only show on first-time orders)

Same pattern as PDP #2 utility links — see `single-product-page.md` for reference implementation.

---

## Replicating on a New Client

1. Install + activate Fluid Checkout Pro (license required)
2. Set `wp option update fc_pro_enable_order_received yes`
3. Set `wp option update fc_pro_enable_order_received_widget_areas yes`
4. Test with a real order → verify `/checkout/order-received/{id}/` renders FC Pro enhanced layout
5. If client wants custom content, choose Option A/B/C above
