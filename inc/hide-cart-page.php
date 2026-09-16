<?php
/**
 * Hide Cart Page
 *
 * Redirects the WooCommerce cart page and removes cart-page references
 * throughout the site. Every add-to-cart action on these builds opens the
 * off-canvas mini-cart drawer, so the standalone cart page is a dead end that
 * still collects links, menu items and search-engine crawls.
 *
 * HISTORY, so nobody re-derives this from scratch again.
 *
 * It originally shipped as `includes/features/hide-cart-page.php` in PR #209
 * (commit 1003dde, 2026-02-12, ClickUp 86ewjqyw7). It disappeared from `main`
 * in 26b5ea9 (2026-05-21), `feat!: adopt BBC-based child theme as the shared
 * theme`, which replaced the whole `includes/` + `custom/` architecture with
 * the BBC `inc/` + `clients/` codebase imported from a production server. The
 * feature was collateral of a wholesale tree swap, and it still exists on tag
 * `v1.78.2-legacy` and branch `legacy/main-pre-bbc`. This port keeps the
 * original behaviour and adapts it to the module loader and per-client flags.
 *
 * CONFIG FIRST, checked before this file was written and logged via
 * figma-config-check.sh. Result: checked-does-not-satisfy.
 *   - `woocommerce_cart_page_id` is a page assignment (42 on the reference
 *     site), with no "disable" value. Emptying it breaks wc_get_cart_url()
 *     and every WooCommerce notice that links to the cart.
 *   - `woocommerce_cart_redirect_after_add` is `no`, and it only controls
 *     where an add-to-cart POST lands. It cannot stop a direct /cart request.
 *   - Blocksy's `cart_drawer_type`, `has_cart_drawer` and
 *     `header_cart_behavior` theme mods are all `false`, and none of them
 *     governs the cart PAGE in any case.
 *   - Fluid Checkout and Fluid Checkout Pro are active and restyle checkout;
 *     neither offers a cart-page bypass.
 * No native toggle produces this behaviour, so a module is the right level.
 *
 * TOGGLES, in precedence order:
 *   1. `BLAZE_HIDE_CART_PAGE` constant. Define it false in wp-config.php or a
 *      client module to switch the whole feature off for one site.
 *   2. The `hide-cart-page` entry in a client's manifest.json "features" array,
 *      handled by inc/loader.php before this file is required at all.
 *
 * DESTINATION. The original sent a non-empty cart to checkout and an empty one
 * to the shop, and that default is preserved for two reasons. A shopper with
 * items in their basket is mid-purchase, so checkout is where they were
 * heading. An empty cart sent to checkout bounces straight back to /cart,
 * because WooCommerce redirects an empty checkout to the cart page, and that
 * pair is the redirect loop this file exists to avoid. A site that wants
 * something else sets `BLAZE_HIDE_CART_PAGE_DESTINATION` to 'checkout',
 * 'shop' or 'home', or filters `blaze_hide_cart_page_destination`.
 *
 * @package Blocksy_Child
 * @see https://app.clickup.com/t/86ewjqyw7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BLAZE_HIDE_CART_PAGE' ) ) {
	define( 'BLAZE_HIDE_CART_PAGE', true );
}

if ( ! BLAZE_HIDE_CART_PAGE ) {
	return;
}

/**
 * Where a request for the cart page should land.
 *
 * Returns the shop URL whenever the cart is empty, whatever the configured
 * destination, because WooCommerce redirects an empty checkout back to /cart
 * and the pair would loop.
 *
 * @return string Absolute URL. Falls back to home_url() if the shop page is unset.
 */
function blaze_hide_cart_page_destination() {
	$configured = defined( 'BLAZE_HIDE_CART_PAGE_DESTINATION' )
		? BLAZE_HIDE_CART_PAGE_DESTINATION
		: 'checkout';

	$shop = wc_get_page_permalink( 'shop' );

	if ( ! $shop ) {
		$shop = home_url( '/' );
	}

	// WC()->cart is unavailable before wp_loaded. Treat that as "unknown", and
	// assume there is nothing to check out with.
	$cart_is_empty = ! did_action( 'wp_loaded' )
		|| ! WC()->cart
		|| WC()->cart->is_empty();

	if ( 'home' === $configured ) {
		$destination = home_url( '/' );
	} elseif ( 'shop' === $configured || $cart_is_empty ) {
		$destination = $shop;
	} else {
		$destination = wc_get_checkout_url();
	}

	/**
	 * Filter the cart-page destination.
	 *
	 * @param string $destination   Absolute URL.
	 * @param bool   $cart_is_empty Whether the cart is currently empty.
	 * @param string $configured    The configured destination keyword.
	 */
	return apply_filters(
		'blaze_hide_cart_page_destination',
		$destination,
		$cart_is_empty,
		$configured
	);
}

/**
 * Redirect requests for the cart page.
 *
 * Priority 1 so it runs before anything renders or caches the page.
 */
add_action(
	'template_redirect',
	function () {
		if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
			return;
		}

		// Leave the block-based cart alone inside the editor or a REST preview,
		// which would otherwise make the page uneditable in wp-admin.
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		wp_safe_redirect( blaze_hide_cart_page_destination() );
		exit;
	},
	1
);

/**
 * Replace cart URLs site-wide.
 *
 * Catches plugin-generated cart links, "View cart" buttons and widget URLs
 * that would otherwise send a shopper to a page that only redirects.
 */
add_filter(
	'woocommerce_get_cart_url',
	function ( $cart_url ) {
		return blaze_hide_cart_page_destination();
	}
);

/**
 * Remove the cart page from navigation menus.
 *
 * @param array   $items Menu items.
 * @param WP_Term $menu  Menu object.
 * @param array   $args  Menu args.
 * @return array
 */
add_filter(
	'wp_get_nav_menu_items',
	function ( $items, $menu, $args ) {
		if ( ! function_exists( 'wc_get_page_id' ) || ! is_array( $items ) ) {
			return $items;
		}

		$cart_page_id = (int) wc_get_page_id( 'cart' );

		if ( $cart_page_id <= 0 ) {
			return $items;
		}

		foreach ( $items as $key => $item ) {
			if ( 'page' === $item->object && $cart_page_id === (int) $item->object_id ) {
				unset( $items[ $key ] );
			}
		}

		return $items;
	},
	10,
	3
);

/**
 * noindex the cart page.
 *
 * The redirect above means a crawler rarely reaches it, but the page can still
 * be requested directly with caching in front, and Yoast or RankMath read the
 * post record itself.
 */
add_action(
	'wp_head',
	function () {
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			echo '<meta name="robots" content="noindex, nofollow">' . "\n";
		}
	},
	1
);

/**
 * Drop the cart page from Blocksy's breadcrumb trail.
 *
 * @param array $items Breadcrumb items.
 * @return array
 */
add_filter(
	'blocksy:general:breadcrumbs:items',
	function ( $items ) {
		if ( ! function_exists( 'wc_get_page_id' ) || ! is_array( $items ) ) {
			return $items;
		}

		$cart_page_id = (int) wc_get_page_id( 'cart' );

		if ( $cart_page_id <= 0 ) {
			return $items;
		}

		foreach ( $items as $key => $item ) {
			if ( isset( $item['id'] ) && $cart_page_id === (int) $item['id'] ) {
				unset( $items[ $key ] );
			}
		}

		return array_values( $items );
	}
);
