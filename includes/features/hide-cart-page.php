<?php
/**
 * Hide Cart Page Feature
 *
 * Redirects the WooCommerce cart page and removes cart page references
 * throughout the site. All add-to-cart actions use the off-canvas mini-cart
 * drawer, making the traditional cart page unnecessary.
 *
 * Toggle: Define BLAZE_HIDE_CART_PAGE as false to disable.
 *
 * @package BlazeBlocksy
 * @since 1.67.0
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
 * Redirect /cart/ to checkout (or shop if cart is empty).
 *
 * When the cart has items, redirects to checkout. When empty, redirects
 * to shop to avoid a redirect loop (WooCommerce redirects empty checkout
 * back to cart).
 */
add_action( 'template_redirect', function () {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return;
	}

	if ( WC()->cart && ! WC()->cart->is_empty() ) {
		wp_safe_redirect( wc_get_checkout_url() );
	} else {
		wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
	}
	exit;
}, 1 );

/**
 * Replace cart URLs with checkout (or shop) URL site-wide.
 *
 * Catches plugin-generated cart links, "View cart" buttons, and widget URLs.
 * Returns shop URL when cart is empty to prevent checkout redirect loops.
 */
add_filter( 'woocommerce_get_cart_url', function ( $cart_url ) {
	if ( did_action( 'wp_loaded' ) && WC()->cart && WC()->cart->is_empty() ) {
		return wc_get_page_permalink( 'shop' );
	}
	return wc_get_checkout_url();
} );

/**
 * Remove cart page from navigation menus.
 */
add_filter( 'wp_get_nav_menu_items', function ( $items, $menu, $args ) {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return $items;
	}

	$cart_page_id = wc_get_page_id( 'cart' );

	foreach ( $items as $key => $item ) {
		if ( (int) $cart_page_id === (int) $item->object_id && 'page' === $item->object ) {
			unset( $items[ $key ] );
		}
	}

	return $items;
}, 10, 3 );

/**
 * Add noindex meta tag on cart page for SEO.
 */
add_action( 'wp_head', function () {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		echo '<meta name="robots" content="noindex, nofollow">' . "\n";
	}
}, 1 );

/**
 * Remove cart from Blocksy breadcrumbs.
 */
add_filter( 'blocksy:general:breadcrumbs:items', function ( $items ) {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return $items;
	}

	$cart_page_id = wc_get_page_id( 'cart' );

	foreach ( $items as $key => $item ) {
		if ( isset( $item['id'] ) && (int) $item['id'] === (int) $cart_page_id ) {
			unset( $items[ $key ] );
		}
	}

	return $items;
} );
