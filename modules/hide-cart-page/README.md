# hide-cart-page

Redirects the WooCommerce cart page and removes cart-page references. Every add-to-cart opens the mini cart, so
the standalone cart page is a dead end that still collects links, menu items and crawls.

Status `stable`. Default off. Requires WooCommerce 9.0 or newer. Port of v1 `inc/hide-cart-page.php`, which
`legacy-v1` stops loading while this module is on.

## What it does

1. Redirects the cart page to the destination (`template_redirect`, priority 1). Skipped in wp-admin and REST requests.
2. Replaces every cart URL with the destination (`woocommerce_get_cart_url`), so "View cart" links do not hit a redirect.
3. Removes the cart page from navigation menus (`wp_get_nav_menu_items`).
4. Adds `noindex, nofollow` on the cart page (`wp_head`, priority 1).
5. Drops the cart page from Blocksy's breadcrumb trail (`blocksy:general:breadcrumbs:items`).

## Settings

Set in `site-config.php`:

```php
'settings' => [ 'hide-cart-page' => [ 'destination' => 'shop' ] ],
```

| Key | Default | Values |
|---|---|---|
| `destination` | `auto` | `auto`, `checkout`, `shop`, `home` |

- `auto`: a cart with items goes to checkout, an empty cart to the shop. This is v1 behaviour.
- `checkout`: same as `auto`. An empty cart still goes to the shop, because WooCommerce sends an empty checkout
  back to `/cart` and the pair would loop.
- `shop`: always the shop page (home if no shop page is set).
- `home`: always the home page, empty cart or not.

An unknown value counts as `auto`.

## Surfaces

`cart.page`. No other module may declare it unless it `replaces` this one.

## Hooks

New:

- Filter `blaze_child/hide-cart-page/destination` `( string $url, bool $cart_is_empty, string $configured )`:
  change the destination URL.

Deprecated (still work, will be removed in a major release):

| Alias | Use instead |
|---|---|
| Constant `BLAZE_HIDE_CART_PAGE` set to a falsy value | `'hide-cart-page' => false` under `modules` in `site-config.php`. Turns the module off at boot, as in v1. |
| Constant `BLAZE_HIDE_CART_PAGE_DESTINATION` | The `destination` setting. A `destination` set in `site-config.php` (even `auto`) wins over the constant, which is then ignored; without one, the constant applies. |
| Filter `blaze_hide_cart_page_destination` | `blaze_child/hide-cart-page/destination`. Runs after the new filter, with the same arguments. |

Each use of a constant is recorded in the health log as `deprecated_alias`; the filter raises the standard
WordPress deprecation notice.

## Extend or disable

Every callback is a named public method on the module instance, so site code can unhook any of the five behaviours:

```php
use function Blaze\Child\Api\module;

add_action( 'wp_loaded', function () {
	$m = module( 'hide-cart-page' );
	if ( $m ) {
		remove_filter( 'wp_get_nav_menu_items', [ $m, 'filter_menu_items' ] );
	}
} );
```

Methods: `maybe_redirect`, `apply_cart_url`, `filter_menu_items`, `noindex`, `filter_breadcrumbs`.

Disable the module with `'hide-cart-page' => false` in `site-config.php`, or name it in the
`BLAZE_CHILD_DISABLE_MODULES` kill switch. Either way `legacy-v1` loads `inc/hide-cart-page.php` again.

Turning it off any other way after boot (the `blaze_child/module_enabled` filter, a failed `requires`, a lost
surface clash, an exception) leaves v1's file skipped with no replacement. The health log then records
`legacy_files_orphaned` naming the module and the file.
