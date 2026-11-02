# recently-viewed

Shows the products a visitor looked at recently in a section below related products on the product page.

Status `stable`. Default off. Requires WooCommerce 9.0 or newer. Port of v1 `inc/recently-viewed.php`, which
`legacy-v1` stops loading while this module is on.

The design comes from PR #273 by its author (blaze-commerce/blaze-blocksy, "recently-viewed: track and render
client-side so page caching can't break it"): track in the browser, print only an empty slot, fill it from a request
whose answer depends only on its URL, and keep WooCommerce's cookie in the browser for the code that reads it. This
port takes that design and changes the details listed under "Differences from PR #273".

## Why it changed

v1 tracked views in PHP (`template_redirect` set the `woocommerce_recently_viewed` cookie) and printed the visitor's
products into the page. Behind a full-page cache (Kinsta, WP Rocket, LiteSpeed) that fails both ways:

- A cache hit never runs PHP, and the miss that fills the cache has its `Set-Cookie` stripped, so nothing is tracked.
- A row that did render was cached with the page, so one visitor's recently viewed products were served to every
  later visitor of that product.

This module never reads the cookie in PHP. The page holds an empty, hidden container that is byte-identical for every
visitor. It carries no personal data and no nonce.

## What it does

1. Prints the container on a product page, after related products (`blocksy:woocommerce:product-single:related:after`,
   priority 10). The fallback is `woocommerce_after_main_content`, priority 10. These are v1's hooks and priorities,
   and the container is printed once per request whichever fires first, as in v1.
2. Adds the script config before `assets/recently-viewed.js` (`wp_enqueue_scripts`, priority 21, after the module
   assets are enqueued at 20): `window.blazeChild.recentlyViewedConfig = { restUrl, cookiePath, cookieDomain }`.
   `restUrl` is `rest_url( 'wc/store/v1/products' )`. These are site-wide values, so a cached page stays correct.
3. In the browser (`assets/recently-viewed.js`, deferred, no jQuery):
   - Reads the list from `localStorage` key `bc_recently_viewed` (JSON array of product ids, newest first, at most 15).
   - First run only: when that key does not exist yet, the ids in v1's `woocommerce_recently_viewed` cookie are
     migrated into it. The cookie is never read again after that.
   - Requests `GET <restUrl>?include=<ids>&per_page=<limit>` from the WooCommerce Store API. This is a public GET, so
     there is no nonce. The ids exclude the current product and are capped at `limit`.
   - Renders the cards in the stored order, not the API's. Ids the API does not return (deleted, private, draft) are
     skipped. Each card has the image (the Store API `thumbnail`), the name and the price (a range for variable
     products, with the regular price struck through when the product is on sale). The cards are built with
     `createElement`, `textContent` and `setAttribute` only. Links and images must be `http(s)://` or root-relative.
   - Unhides the section only when it has at least one card. It then fires `bc:recently-viewed:loaded` on
     `document`, with `detail.section`.
   - Records the current product (moves to the front, duplicates dropped, at most 15 kept) and mirrors the list,
     write-only, into the `woocommerce_recently_viewed` cookie in WooCommerce's format: ids joined by `|`, oldest
     first, session cookie, WordPress's `COOKIEPATH` / `COOKIE_DOMAIN`, `SameSite=Lax`, `Secure` on https. v1's
     `inc/mini-cart-empty.php` and WooCommerce's own Recently Viewed widget read that cookie, so they keep working.
   - Without `localStorage` (blocked or private mode) the script does nothing and the section stays hidden.

The script also loads on archive and other front-end pages (the manifest `where` is `pdp`, `archive`, `front`). With
no container on the page it only migrates the v1 cookie on a first run.

## Markup

```html
<section class="bc-recently-viewed related products is-layout-slider is-width-constrained" data-bc-rv
	data-limit="8" data-product-id="123" aria-labelledby="bc-recently-viewed-title"
	style="--bc-rv-columns:4;--bc-rv-columns-tablet:3;--bc-rv-columns-mobile:2" hidden>
	<h2 class="ct-module-title bc-recently-viewed__title" id="bc-recently-viewed-title">Recently viewed</h2>
	<!-- added by the script: -->
	<ul class="bc-recently-viewed__list" role="list">
		<li class="bc-recently-viewed__item product">
			<a class="bc-recently-viewed__link woocommerce-LoopProduct-link" href="...">
				<img class="bc-recently-viewed__image" src="..." alt="..." loading="lazy" decoding="async">
				<h3 class="bc-recently-viewed__name woocommerce-loop-product__title">...</h3>
			</a>
			<span class="bc-recently-viewed__price price">
				<del class="bc-recently-viewed__regular"><span class="bc-recently-viewed__amount">$20.00</span></del>
				<span class="bc-recently-viewed__amount">$15.00</span>
			</span>
		</li>
	</ul>
</section>
```

### Differences from v1

- The section keeps v1's classes (`bc-recently-viewed related products is-layout-slider is-width-constrained`) and
  the heading keeps `ct-module-title`, so existing site CSS on those still applies.
- The heading text is now `Recently viewed` (v1: `Recently Viewed`), from the `title` setting. A translation of the
  old string does not apply to the new one.
- The section is empty and `hidden` in the HTML. It appears after the Store API answers, so it can shift the content
  below it. It sits below related products, normally out of the first viewport.
- The cards are a CSS grid of light cards (image, name, price). v1 printed Blocksy's slider (`.flexy-container`, arrows,
  dots via `assets/js/product-carousel-dots.js`) with the full WooCommerce loop card (`content-product.php`, including
  the add to cart button and hover image). Site CSS aimed at `.bc-recently-viewed .flexy*` or
  `.bc-recently-viewed .ct-woo-card-actions` no longer matches anything. `product-carousel-dots.js` still selects
  `.bc-recently-viewed`, finds no `.flexy` and leaves it alone.
- The grid follows Blocksy's related-products column setting
  (`woocommerce_related_products_slideshow_columns`, v1 defaults 4 / 3 / 2) through the inline custom properties.
- v1 showed at most 8 products from the 15 tracked; the `limit` setting now sets that number.

### Differences from PR #273

PR #273 is not merged or changed by this module; its design is taken over here.

- Data: the Store API (`/wp-json/wc/store/v1/products?include=...`) instead of a new `?wc-ajax=bc_recently_viewed`
  endpoint returning server-rendered HTML. No new endpoint, no PHP query.
- Rendering: `textContent` / `setAttribute` instead of assigning the response to `innerHTML`.
- Storage: `localStorage` key `bc_recently_viewed` is the source of truth. PR #273 kept the list only in the cookie.
  The cookie is still written in WooCommerce's format, as in PR #273, but read only once, to migrate.
- Markup: a bare `<section data-bc-rv hidden>` with the heading, instead of a `<div class="bc-recently-viewed-slot">`
  that the response replaced.
- No changes to v1's `assets/js/product-carousel-dots.js` (v1 files stay byte-identical in 2.x).

## Settings

Set in `site-config.php`:

```php
'settings' => [ 'recently-viewed' => [ 'limit' => 4, 'title' => 'You looked at' ] ],
```

| Key | Default | Value |
|---|---|---|
| `limit` | `8` | Cards shown, 1 to 15. Anything else means 8. |
| `title` | `Recently viewed` | Section heading. The default is translated (`blocksy-child` text domain). |

## Styling

`assets/recently-viewed.css` uses `bc-recently-viewed__*` classes and these custom properties (set on `:root`,
override them in site CSS): `--bc-rv-gap`, `--bc-rv-inner-gap`, `--bc-rv-image-ratio`, `--bc-rv-image-radius`,
`--bc-rv-name-size`, `--bc-rv-name-weight`, `--bc-rv-name-color`, `--bc-rv-price-color`, `--bc-rv-regular-opacity`,
`--bc-rv-focus-color`. Colours default to Blocksy's `--theme-*` variables. Columns come from `--bc-rv-columns`,
`--bc-rv-columns-tablet` (below 1000px) and `--bc-rv-columns-mobile` (below 690px) on the section.

## Surfaces

`pdp.recently-viewed`. No other module may declare it unless it `replaces` this one.

## Hooks

No new PHP hooks. JS: `document` event `bc:recently-viewed:loaded` (`detail.section`) after cards are added; the
pure functions are on `window.blazeChild.recentlyViewed` (`parse`, `push`, `visible`, `productsUrl`, `inOrder`,
`render`, `init`, ...).

Deprecated (still work, will be removed in a major release):

| v1 function | Now | Use instead |
|---|---|---|
| `bc_render_recently_viewed_products()` | Prints the empty container (once per request) | `module( 'recently-viewed' )->render()` |
| `bc_track_product_view()` | Does nothing: tracking is in the browser | Nothing |

Each call raises the standard WordPress deprecation notice. Neither is a hooked callback any more, so
`remove_action( 'blocksy:woocommerce:product-single:related:after', 'bc_render_recently_viewed_products' )` no longer
removes the section. Use the method below.

## Extend or disable

The two render callbacks are a named public method on the module instance:

```php
use function Blaze\Child\Api\module;

add_action( 'wp_loaded', function () {
	$m = module( 'recently-viewed' );
	if ( $m ) {
		remove_action( 'woocommerce_after_main_content', [ $m, 'render' ], 10 );
	}
} );
```

Methods: `render`, `container_markup`, `add_config`, `config`, `limit`, `title`, `columns`.

Disable the module with `'recently-viewed' => false` in `site-config.php`, or name it in the
`BLAZE_CHILD_DISABLE_MODULES` kill switch. Either way `legacy-v1` loads `inc/recently-viewed.php` again, with its
server-side cookie and its caching problems.

Turning it off any other way after boot (the `blaze_child/module_enabled` filter, a failed `requires`, a lost surface
clash, an exception) leaves v1's file skipped with no replacement. The section then disappears, and the health log
records `legacy_files_orphaned` naming the module and the file.

## Tests

- PHP: `php tests/modules/run.php` (`tests/modules/test-recently-viewed.php`).
- JS: `node --test modules/recently-viewed/assets/recently-viewed.test.mjs` (node 22; also run by
  `php tests/run-all.php` and CI as `node --test "modules/**/*.test.mjs"`).
