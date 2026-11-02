<?php
namespace Blaze\Child\Modules\HideCartPage;

defined( 'ABSPATH' ) || exit;

use Blaze\Child\Core\Health;

/**
 * Redirects the WooCommerce cart page and removes cart-page references (port of v1 inc/hide-cart-page.php).
 *
 * Every add-to-cart opens the mini cart, so the standalone cart page is a dead end that still collects
 * links, menu items and crawls. Every callback is a named public method, so site code can
 * remove_action( 'x', [ Api\module( 'hide-cart-page' ), 'method' ] ).
 */
class Module extends \Blaze\Child\Core\Module {

	const DESTINATIONS = [ 'auto', 'checkout', 'shop', 'home' ];

	/**
	 * Registers the five hooks of v1, unless the deprecated BLAZE_HIDE_CART_PAGE constant is false.
	 *
	 * @return void
	 */
	public function boot(): void {
		if ( defined( 'BLAZE_HIDE_CART_PAGE' ) && false === BLAZE_HIDE_CART_PAGE ) {
			Health::record(
				'deprecated_alias',
				"Constant BLAZE_HIDE_CART_PAGE is false, so 'hide-cart-page' is off. Deprecated since 2.0.0: set 'hide-cart-page' => false under modules in site-config.php.",
				[
					'module' => 'hide-cart-page',
					'alias'  => 'BLAZE_HIDE_CART_PAGE',
				]
			);
			return;
		}

		if ( defined( 'BLAZE_HIDE_CART_PAGE_DESTINATION' ) ) {
			Health::record(
				'deprecated_alias',
				"Constant BLAZE_HIDE_CART_PAGE_DESTINATION is deprecated since 2.0.0: use the 'destination' setting of 'hide-cart-page' in site-config.php.",
				[
					'module' => 'hide-cart-page',
					'alias'  => 'BLAZE_HIDE_CART_PAGE_DESTINATION',
				]
			);
		}

		add_action( 'template_redirect', [ $this, 'maybe_redirect' ], 1 );
		add_filter( 'woocommerce_get_cart_url', [ $this, 'apply_cart_url' ] );
		add_filter( 'wp_get_nav_menu_items', [ $this, 'filter_menu_items' ], 10, 3 );
		add_action( 'wp_head', [ $this, 'noindex' ], 1 );
		add_filter( 'blocksy:general:breadcrumbs:items', [ $this, 'filter_breadcrumbs' ] );
	}

	/**
	 * The configured destination keyword: the deprecated constant if defined, else the setting.
	 * Anything unknown means 'auto'.
	 *
	 * @return string One of self::DESTINATIONS.
	 */
	private function configured(): string {
		$value = defined( 'BLAZE_HIDE_CART_PAGE_DESTINATION' ) ? BLAZE_HIDE_CART_PAGE_DESTINATION : $this->setting( 'destination' );
		return is_string( $value ) && in_array( $value, self::DESTINATIONS, true ) ? $value : 'auto';
	}

	/**
	 * Where a request for the cart page should land.
	 *
	 * An empty cart always goes to the shop unless the destination is 'home': WooCommerce redirects an
	 * empty checkout back to /cart, and that pair is a redirect loop.
	 *
	 * @return string Absolute URL.
	 */
	public function destination(): string {
		$configured = $this->configured();

		$shop = wc_get_page_permalink( 'shop' );
		if ( ! $shop ) {
			$shop = home_url( '/' );
		}

		// WC()->cart is unavailable before wp_loaded: treat that as "unknown", i.e. nothing to check out with.
		$cart_is_empty = ! did_action( 'wp_loaded' ) || ! WC()->cart || WC()->cart->is_empty();

		if ( 'home' === $configured ) {
			$destination = home_url( '/' );
		} elseif ( 'shop' === $configured || $cart_is_empty ) {
			$destination = $shop;
		} else {
			$destination = wc_get_checkout_url();
		}

		/**
		 * Filters the cart-page destination.
		 *
		 * @param string $destination   Absolute URL.
		 * @param bool   $cart_is_empty Whether the cart is currently empty.
		 * @param string $configured    The configured destination keyword.
		 */
		$destination = apply_filters( 'blaze_child/hide-cart-page/destination', $destination, $cart_is_empty, $configured );

		// Deprecated v1 name; same arguments, runs after the new filter.
		return (string) apply_filters_deprecated(
			'blaze_hide_cart_page_destination',
			[ $destination, $cart_is_empty, $configured ],
			'2.0.0',
			'blaze_child/hide-cart-page/destination'
		);
	}

	/**
	 * Redirects requests for the cart page (template_redirect, priority 1: before anything renders or caches).
	 *
	 * @return void
	 */
	public function maybe_redirect(): void {
		if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
			return;
		}

		// Leave the block-based cart alone in the editor or a REST preview, or the page cannot be edited.
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		wp_safe_redirect( $this->destination() );
		exit;
	}

	/**
	 * Replaces cart URLs site-wide (plugin links, "View cart" buttons, widgets).
	 *
	 * @param mixed $cart_url Cart URL (ignored).
	 * @return string
	 */
	public function apply_cart_url( $cart_url ): string {
		unset( $cart_url );
		return $this->destination();
	}

	/**
	 * Removes the cart page from navigation menus.
	 *
	 * @param mixed $items Menu items.
	 * @param mixed $menu  Menu object (unused).
	 * @param mixed $args  Menu args (unused).
	 * @return mixed
	 */
	public function filter_menu_items( $items, $menu = null, $args = null ) {
		unset( $menu, $args );
		if ( ! function_exists( 'wc_get_page_id' ) || ! is_array( $items ) ) {
			return $items;
		}

		$cart_page_id = (int) wc_get_page_id( 'cart' );
		if ( $cart_page_id <= 0 ) {
			return $items;
		}

		foreach ( $items as $key => $item ) {
			if ( is_object( $item ) && 'page' === ( $item->object ?? '' ) && $cart_page_id === (int) ( $item->object_id ?? 0 ) ) {
				unset( $items[ $key ] );
			}
		}

		return $items;
	}

	/**
	 * Adds noindex to the cart page. A crawler rarely reaches it after the redirect, but a cached or
	 * directly requested page can still be read.
	 *
	 * @return void
	 */
	public function noindex(): void {
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			echo '<meta name="robots" content="noindex, nofollow">' . "\n";
		}
	}

	/**
	 * Drops the cart page from Blocksy's breadcrumb trail.
	 *
	 * @param mixed $items Breadcrumb items.
	 * @return mixed
	 */
	public function filter_breadcrumbs( $items ) {
		if ( ! function_exists( 'wc_get_page_id' ) || ! is_array( $items ) ) {
			return $items;
		}

		$cart_page_id = (int) wc_get_page_id( 'cart' );
		if ( $cart_page_id <= 0 ) {
			return $items;
		}

		foreach ( $items as $key => $item ) {
			if ( is_array( $item ) && isset( $item['id'] ) && $cart_page_id === (int) $item['id'] ) {
				unset( $items[ $key ] );
			}
		}

		return array_values( $items );
	}
}
