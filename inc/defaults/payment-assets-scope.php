<?php
/**
 * Default `payment-assets-scope` — payment gateway scripts only on cart,
 * checkout, order-pay and add-payment-method.
 *
 * BREAKS, if a site uses them: PayPal Pay Later messages, PayPal / Apple Pay
 * / Google Pay express buttons on product pages or the mini cart, and the
 * Zip widget on product pages. Only opt in when none of these show outside
 * checkout (Bonza shows express buttons, so it must not use this).
 *
 * Dequeued twice: at wp_enqueue_scripts 999, and again at
 * wp_print_footer_scripts 1, because PayPal and Zip enqueue late while
 * their markup hooks run.
 *
 * Filters: `blocksy_child_payment_assets` (the handle lists, from
 * inc/defaults/data/payment-handles.php) and
 * `blocksy_child_payment_assets_needed` (bool, whether this page keeps them).
 *
 * PROMOTED FROM: bulkwholesale custom/includes/bw-core.php.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether this page takes a payment and so keeps the gateway assets.
 *
 * @return bool
 */
function blocksy_child_payment_assets_needed(): bool {
	$needed = ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_checkout_pay_page' ) && is_checkout_pay_page() )
		|| ( function_exists( 'is_add_payment_method_page' ) && is_add_payment_method_page() );

	return (bool) apply_filters( 'blocksy_child_payment_assets_needed', $needed );
}

/**
 * Gateway handle lists.
 *
 * @return array{scripts: string[], styles: string[]}
 */
function blocksy_child_payment_assets(): array {
	$assets = apply_filters( 'blocksy_child_payment_assets', require BLOCKSY_CHILD_PATH . 'inc/defaults/data/payment-handles.php' );

	return [
		'scripts' => array_values( (array) ( $assets['scripts'] ?? [] ) ),
		'styles'  => array_values( (array) ( $assets['styles'] ?? [] ) ),
	];
}

add_action( 'wp_enqueue_scripts', function () {
	if ( blocksy_child_payment_assets_needed() ) {
		return;
	}
	$assets = blocksy_child_payment_assets();
	foreach ( $assets['scripts'] as $handle ) {
		wp_dequeue_script( $handle );
	}
	foreach ( $assets['styles'] as $handle ) {
		wp_dequeue_style( $handle );
	}
}, 999 );

add_action( 'wp_print_footer_scripts', function () {
	if ( blocksy_child_payment_assets_needed() ) {
		return;
	}
	foreach ( blocksy_child_payment_assets()['scripts'] as $handle ) {
		wp_dequeue_script( $handle );
	}
}, 1 );
