# Site defaults (`inc/defaults/`)

Fixes that retheme builds kept re-writing in their own `custom/` folders. Each one changes how a site looks or which scripts load, so **every default is opt-in**. A site with no manifest gets none of them (unlike `$optional_modules` in `inc/loader.php`, where no `"features"` key means everything is on).

## Opting in

From `custom/custom.php`:

```php
add_filter( 'blocksy_child_defaults', function ( $d ) {
	return array_merge( $d, [ 'mobile-inputs', 'tap-targets' ] );
} );
```

Or a manifest `"defaults": [ ... ]` array, or `define( 'BLOCKSY_CHILD_DEFAULTS', [ ... ] )`. The three are unioned. Unknown names are logged (`[blocksy-child][defaults] unknown default: …`) and ignored.

## The defaults

| Name | Loads | What changes | Use when |
|---|---|---|---|
| `mobile-inputs` | `defaults-mobile-inputs.css` | On touch screens every text field, select and textarea is 16px, so iOS Safari stops zooming on focus. FC's order-summary qty input gets 28px wide to fit. Desktop unchanged. | Any site. Candidate for default-on after a trial. |
| `tap-targets` | `defaults-tap-targets.css` | Breadcrumb links get a 24px minimum height (WCAG 2.2 SC 2.5.8). Rows up to ~5px taller on mobile. | Any site. Candidate for default-on after a trial. |
| `design-scope` | `defaults-design-scope.css` | Inside `.bc-design` only: headings inherit line-height, labels lose Blocksy's bottom margin, bold is 700, `.bc-logo-row img` isn't squashed. | Hand-built design markup (homepage sections, design pages). Put `bc-design` on the wrapper. |
| `pl-placeholder-contain` | `defaults-pl-placeholder-contain.css` | No Performance Lab dominant-colour placeholder behind product-grid and gallery images. | Performance Lab Image Placeholders is on and product images are contain-fit. |
| `card-image-contain` | `defaults-card-image-contain.css` | Product-card photos fit (contain) instead of crop. | Packshot stores whose photo ratio differs from the Customizer ratio. Not lifestyle-photo stores. |
| `recaptcha-scope` | `inc/defaults/recaptcha-scope.php` | CF7's reCAPTCHA (and badge) loads only on pages that render a CF7 form. | Sites using CF7 + reCAPTCHA. Forms that hide the badge must show Google's notice text. |
| `payment-assets-scope` | `inc/defaults/payment-assets-scope.php` | PayPal, Zip and eWAY scripts load only on cart, checkout, order-pay and add-payment-method. | **Only** when no Pay Later messages, express buttons (PayPal / Apple Pay / Google Pay) or Zip widgets show outside checkout. Bonza must not use it. |

## Also in the theme, not opt-in

- `--bc-main-bottom-gap` (base.css): the 100px gap under `.site-main`. Set `--bc-main-bottom-gap: 0` on a body class when the design draws its own footer spacing, instead of overriding `#main`.
- For any viewport-tall panel, write `100vh` then a `100dvh` line after it, so iPhone Safari doesn't hide the bottom under its toolbar.

## Promoting a default to default-on

When a default has run cleanly on 2–3 sites, it can move to default-on. That change ships as its own PR naming the sites it was tested on, because it changes every site on the next deploy.
