# Single Product Page — Customization Patterns

> All patterns below are **reusable** across projects using this child theme.
> Client-specific overrides go in `clients/{client-name}/{client-name}.css`.

---

## File Map

```
blocksy-child/
├── inc/
│   └── woocommerce.php          ← PHP hooks for PDP (wishlist, reviews, related products rename)
├── assets/css/components/
│   └── woo-single.css           ← All reusable PDP styles (loaded on is_product() pages only)
├── clients/{client}/
│   └── {client}.css             ← Client-specific PDP overrides (e.g. hide payment on bundles)
```

---

## PDP #1 — Wishlist Button Beside Add to Cart

**What:** Heart icon button inline with qty + ATC, same row.

**Blocksy Settings:**
- `product_actions` (Additional Actions) element → **DISABLED**
- Wishlist Extension → **ON** (this is the master on/off switch)

**PHP** (`inc/woocommerce.php`):
```php
add_action( blocksy:pro:woo-extra:wishlist:button:output, function () {
    if ( function_exists( blocksy_output_add_to_wish_list ) ) {
        echo blocksy_output_add_to_wish_list( single );
    }
} );
```

**Why the hook is needed:** Blocksy fires `blocksy:pro:woo-extra:wishlist:button:output` via `woocommerce_after_add_to_cart_button`, but nothing in Blocksy core listens to it. Without this hook, the wishlist button only renders as a standalone block via the `product_actions` layout element.

**CSS** (`assets/css/components/woo-single.css` — PDP #1 section):
- `.ct-product-add-to-cart .ct-cart-actions` → `flex-wrap: nowrap`
- `.ct-wishlist-button-single` → square button, icon only (label + tooltip hidden)
- Border-radius matches ATC: `var(--theme-button-border-radius, 8px)`
- Three states: default (border), hover (border highlights), active/wishlisted (filled)

**CSS Variables (all with fallbacks):**

| Variable | Purpose | Fallback |
|----------|---------|----------|
| `--theme-button-min-height` | Button size (matches ATC) | `40px` |
| `--theme-button-border-radius` | Corner radius (matches ATC) | `8px` |
| `--theme-border-color` | Default border | `#e0e0e0` |
| `--theme-text-color` | Icon color | `#333` |
| `--theme-button-background-initial-color` | Active fill + hover border | `#333` |
| `--theme-button-text-initial-color` | Active icon color | `#fff` |

**To remove wishlist entirely on a project:** Disable the Blocksy Wishlist Extension (Blocksy > Extensions > Wishlist). Do NOT re-enable `product_actions` — that is just a positioning option, not the on/off switch.

---

## PDP #2 — Product Utility Links (Shipping / Returns / FAQ)

**What:** Three icon links in a horizontal row below ATC: Calculate Shipping, Returns, FAQ.

**Implementation:** Blocksy Content Block (post ID 645195) renders the HTML. CSS handles the layout.

**CSS** (`assets/css/components/woo-single.css` — PDP #2 section):
- `.bc-utility-links` → flex row with dividers
- `.bc-utility-link` → icon + text, centered, with hover
- `.bc-utility-divider` → 1px vertical line between links

**To change links/icons:** Edit the Content Block in WP Admin > Content Blocks > Product Utility Links.

**To change styling:** Edit the PDP #2 section in `woo-single.css`.

---

## PDP #4 — Bundle Pricing Font Size

**What:** "3 for $45.00" text was rendering too large on bundle products.

**CSS** (`assets/css/components/woo-single.css` — PDP #4 section):
- `.bundle_price .price` → `font-size: 16px`
- `.bundle_price .price del` → `font-size: 14px`

---

## PDP #5 — Product Tabs Fix

**What:** Tabs (Description, Instructions, Ingredients) were not displaying.

**Fix:** Blocksy theme mod `woo_has_product_tabs_description` set to `yes`.

**No code needed** — this is a Blocksy Customizer setting only.

---

## PDP #6 — Gift Wrap / Product Addons Typography

**What:** "Gift Wrapping" addon heading and description needed tighter typography.

**CSS** (`assets/css/components/woo-single.css` — PDP #6 section):
- `.wc-pao-addon-name` → `15px`, weight `600`
- `.wc-pao-addon-description` → `13px`, `opacity: 0.75`

---

## PDP #7 — Out-of-Stock Variants Visible

**What:** Out-of-stock variants were hidden instead of showing as unavailable.

**Fix:** WooCommerce setting `woocommerce_hide_out_of_stock_items` set to `no`. Blocksy `out_of_stock_swatch_type` already set to `crossed`.

**No code needed** — WooCommerce + Blocksy settings only.

---

## PDP #8 — Disable Variant Swatch Tooltips

**What:** Tooltips on variant swatches hidden.

**CSS** (`assets/css/components/woo-single.css` — PDP #8 section):
- `.ct-swatch-container [data-tooltip], .ct-swatch-container .ct-tooltip` → `display: none`

**Blocksy setting:** `variations_swatches_display_type` already set to `no`. CSS is a safety net.

---

## PDP #9 — Business Reviews Bundle

**What:** Google reviews collection displayed after product tabs, before related products.

**PHP** (`inc/woocommerce.php`):
```php
add_action( woocommerce_after_single_product_summary, function () {
    if ( shortcode_exists( brb_collection ) ) {
        echo '<div class="bc-reviews-bundle">';
        echo do_shortcode( '[brb_collection id="58908"]' );
        echo '</div>';
    }
}, 25 );
```

**CSS** (`assets/css/components/woo-single.css`):- `.bc-reviews-bundle` → constrained to content width via `max-width: var(--theme-normal-content-max-width)`, centered with auto margins, `margin-top: 50px` for spacing after tabs
**Note:** Collection ID `58908` is Byron Bay specific. For a new client, change the ID or make it dynamic.

---

## PDP #10 — "You May Also Like" Rename

**What:** "Related products" heading renamed to "You May Also Like".

**PHP** (`inc/woocommerce.php`):
```php
add_filter( gettext, function ( $translated, $text, $domain ) {
    if ( woocommerce === $domain && Related products === $text ) {
        return You May Also Like;
    }
    return $translated;
}, 10, 3 );
```

---

## B1 — Bundle Scent Heading

**What:** `h4.bundled_product_attributes_title` styled as h3 weight.

**CSS** (`assets/css/components/woo-single.css` — B1 section):
- `font-size: 18px`, `font-weight: 600`

---

## B2 — Hide Payment Badges in Bundles

**What:** Square payment placement and Afterpay badge hidden inside bundle product forms to reduce visual clutter.

**CSS** (`assets/css/components/woo-single.css` — B2 section):
- `.bundle_form .square-placement` → `display: none`
- `.bundle_form [class*="afterpay"]` → `display: none`

**Why `.bundle_form` scope:** These badges only need hiding inside the bundle form context. On simple/variable products, the payment badges may still be desirable. Scoping to `.bundle_form` prevents unintended hiding elsewhere.

**Previous approach:** Earlier used `body.single-product .product-type-bundle .ct-payment-methods` in client CSS. Now moved to reusable component CSS with tighter scoping.

---

## B3 — Bundle Price Hierarchy

**What:** Bundled item prices styled smaller/lighter than the bundle total price, creating a clear visual hierarchy.

**CSS** (`assets/css/components/woo-single.css` — B3 section):
- `.bundled_product .price` → `font-size: 14px`, `font-weight: 400` (individual item prices)
- `.bundle_price .price` → `font-size: 24px`, `font-weight: 700` (bundle total price)

**Why this matters:** Without hierarchy, individual item prices and the bundle total look identical, confusing customers about what they are actually paying.

---

## B4 — Bundle ATC + Wishlist Inline

**What:** Wishlist heart button rendered inside the bundle Add to Cart button area (`.bundle_button`), matching the same inline layout as PDP #1 on simple/variable products.

**PHP** (`inc/woocommerce.php`):
```php
add_action( 'woocommerce_bundles_add_to_cart_button', function () {
    if ( function_exists( 'blocksy_output_add_to_wish_list' ) ) {
        echo blocksy_output_add_to_wish_list( 'single' );
    }
}, 20 );
```

**Why a separate hook:** Bundle products use `woocommerce_bundles_add_to_cart_button` instead of `woocommerce_after_add_to_cart_button`. The PDP #1 wishlist hook does not fire inside `.bundle_button`, so a second hook is needed.

**Hide duplicate:** Blocksy may also render a wishlist button outside the bundle form. Hide it:
- `.bundle_data > .ct-wishlist-button-single` → `display: none`

**CSS selectors:** All PDP #1 wishlist selectors are extended with `.bundle_button` variants:
- `.bundle_button .ct-wishlist-button-single` mirrors `.ct-cart-actions .ct-wishlist-button-single`
- Same square button styling, same states (default, hover, active/wishlisted)

---

## Hide Bundle Error Notice

**What:** WooCommerce Product Bundles shows an info notice inside the bundle form when validation fails (e.g., "Please choose options..."). This notice is styled as a generic WooCommerce info box that looks out of place.

**CSS** (`assets/css/components/woo-single.css`):
- `.bundle_data .bundle_error .woocommerce-info` → `display: none`

**Note:** The bundle still prevents ATC when invalid — this only hides the visual notice. The bundle validation indicators (red highlights on incomplete items) remain visible.

---

## Wishlist Off-Canvas Panel

**What:** Clicking the header wishlist icon or adding a product to the wishlist opens a slide-in off-canvas panel (similar to the Blocksy mini cart drawer) showing all wishlisted products, guest sign-up prompts, and suggested products.

**Full documentation:** **[wishlist-offcanvas.md](wishlist-offcanvas.md)**

**Key files:**
- `inc/wishlist-offcanvas.php` — Panel HTML shell, preloaded JSON data, dynamic CSS, AJAX endpoint, suggested products
- `assets/css/components/wishlist-offcanvas.css` — Panel layout, item styles, all states
- `assets/js/wishlist-offcanvas.js` — Client-side rendering, MutationObserver, cookie/AJAX sync, panel open/close

**How it connects to PDP #1:** The wishlist heart button (PDP #1 above) is the trigger. When a user clicks it, Blocksy handles the add/remove natively and updates the header counter badge. The off-canvas panel's MutationObserver detects the counter change and auto-opens the panel with the updated wishlist.

---

## Related Patterns

- **[wishlist-offcanvas.md](wishlist-offcanvas.md)** — Off-canvas wishlist drawer (slides in from right)


---

## PDP #1a — Stock Status (In Stock / Out of Stock)

**What:** Displays stock status text ("In Stock" or "Out of Stock") directly below the price.

**PHP** (`inc/woocommerce.php`):
- Hook on `woocommerce_single_product_summary` at priority 11 (fires after price at priority 10)
- Outputs `<p class="bc-stock-status bc-stock-in-stock">In Stock</p>` or `<p class="bc-stock-status bc-stock-out-of-stock">Out of Stock</p>`

**CSS** (`assets/css/components/woo-single.css` — PDP #1a section):
- `.bc-stock-in-stock` → green `color: var(--theme-palette-color-18, #10b981)`
- `.bc-stock-out-of-stock` → red `color: var(--theme-palette-color-15, #EF4444)`
- `font-size: 14px`, `font-weight: 400`, body font

**Why this exists:** Blocksy has NO simple stock status text element — only a scarcity progress bar (stock quantity bar). This hook provides a clean text-based indicator.

---

## PDP #1b — Price Suffix Styling

**What:** Makes the WooCommerce price suffix ("incl. GST (AU only)") visually lighter than the price itself.

**CSS only** (`assets/css/components/woo-single.css` — PDP #1b section):
- `.woocommerce-price-suffix` → `font-weight: 400`, `color: var(--theme-palette-color-4, #777)`

**No PHP needed** — the suffix is set in WooCommerce > Settings > Tax > "Price display suffix".

---

## PDP #4b — Product Addon Group Heading

**What:** WooCommerce Product Addons outputs addon group names as `<h2 class="wc-pao-addon-heading">`. The default h2 size (48px) is far too large for an inline form label.

**CSS only** (`assets/css/components/woo-single.css` — PDP #4b section):
- `.wc-pao-addon-heading` → `font-size: 16px`, `font-weight: 700`, `text-transform: none`, body font

**NOTE:** Cannot use `var(--theme-font-size)` here because it inherits from the h2 cascade (48px). Must hardcode `16px`.

---

## PDP #4c — Variation Label + Description Styling

**What:** "Select a scent" variation label and the variation description paragraph styled to match body typography.

**CSS only** (`assets/css/components/woo-single.css` — PDP #4c section):
- `.variations .label label` → `font-weight: 700 !important`, `font-size: 16px`, `text-transform: none`
  - `!important` is required because Blocksy inline CSS overrides without it
  - The label span (showing the selected value) stays at `font-weight: 400`
- `.woocommerce-variation-description` → `font-size: 14px`, `font-weight: 400`, `color: var(--theme-palette-color-2, #393939)`

---

## B2B/Addons Hidden on PDP

**What:** B2B wholesale table and product addons total row hidden on single product pages to prevent them breaking the ATC flex layout.

**CSS** (`assets/css/components/woo-single.css`):
- `.single-product .b2bwhs_shop_table` → `display: none`
- `.single-product #product-addons-total` → `display: none`

**NOTE:** Use `.single-product` body class prefix, NOT `.ct-cart-actions >` child selector — plugins inject at different DOM depths. These elements break `flex-wrap: nowrap` on the ATC row if not hidden.

---

> **View Cart hide** is documented in **[global-ux-overrides.md](global-ux-overrides.md)**, not this file. It is a global UX pattern, not PDP-specific.
