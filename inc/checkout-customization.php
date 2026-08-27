<?php
/**
 * Checkout Customization — Fluid Checkout step/substep label overrides + counter.
 *
 * Renames the default step/substep titles to match the Byron Bay Candles Figma
 * spec (node 6054:66809) and injects a "STEP X OF N" counter above the progress
 * bar. CSS for the counter lives in assets/css/components/woo-checkout.css.
 *
 * Requires: Fluid Checkout (fluid-checkout plugin, class FluidCheckout_Steps).
 * Feature flag: 'checkout-customization' in client manifest.json.
 *
 * @package Blocksy_Child
 * @since   1.0.0
 * @date    2026-04-17
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bail if Fluid Checkout is not active.
if ( ! class_exists( 'FluidCheckout' ) ) {
	return;
}

/**
 * Rename step titles (top-level progress bar labels) to Figma spec.
 *
 * WHY: Figma shows ACCOUNT / ADDRESS / SHIPPING / ORDER PAYMENT instead of the
 * Fluid defaults Contact / Shipping / Billing / Payment. Using the per-step
 * filter keeps it reversible and avoids forking the plugin template.
 * @date 2026-04-17
 */
add_filter( 'fc_step_title_contact', function () {
	return __( 'Account', 'blocksy-child' );
} );

add_filter( 'fc_step_title_shipping', function () {
	return __( 'Address', 'blocksy-child' );
} );

add_filter( 'fc_step_title_billing', function () {
	return __( 'Shipping', 'blocksy-child' );
} );

add_filter( 'fc_step_title_payment', function () {
	return __( 'Order Payment', 'blocksy-child' );
} );

/**
 * Rename substep titles (shown on the collapsed step cards) to Figma spec.
 * @date 2026-04-17
 */
add_filter( 'fc_substep_title_contact', function () {
	return __( 'Account', 'blocksy-child' );
} );

add_filter( 'fc_substep_title_shipping_address', function () {
	return __( 'Shipping To', 'blocksy-child' );
} );

add_filter( 'fc_substep_title_shipping_method', function () {
	return __( 'Shipping Method', 'blocksy-child' );
} );

add_filter( 'fc_substep_title_gift_options', function () {
	return __( 'Gift Options', 'blocksy-child' );
} );

add_filter( 'fc_substep_title_order_notes', function () {
	return __( 'Additional Notes', 'blocksy-child' );
} );

add_filter( 'fc_substep_title_billing_address', function () {
	return __( 'Billing To', 'blocksy-child' );
} );

add_filter( 'fc_substep_title_payment', function () {
	return __( 'Payment Method', 'blocksy-child' );
} );

/**
 * Move progress bar and checkout notices inside .fc-inside.
 *
 * WHY: Fluid Checkout renders these via woocommerce_before_checkout_form
 * (before the <form> tag), placing them outside .fc-inside. Relocating to
 * fc_checkout_before_steps puts them inside .fc-inside for full layout control.
 *
 * Timing: Must run after FC loads features at after_setup_theme priority 10.
 * @date 2026-04-22
 */
add_action( 'after_setup_theme', function () {
	if ( ! class_exists( 'FluidCheckout_Steps' ) ) {
		return;
	}

	$steps = FluidCheckout_Steps::instance();

	// Remove from woocommerce_before_checkout_form (outside .fc-inside).
	remove_action( 'woocommerce_before_checkout_form', array( $steps, 'output_checkout_progress_bar' ), 4 );
	remove_action( 'woocommerce_before_checkout_form', array( $steps, 'output_checkout_notices_wrapper_start_tag' ), 5 );
	remove_action( 'woocommerce_before_checkout_form', array( $steps, 'output_checkout_notices_wrapper_end_tag' ), 100 );

	// Re-add to fc_checkout_before_steps (inside .fc-inside).
	//
	// BUG FIX (86eypb6my, 2026-08-27): output_checkout_progress_bar() must be
	// re-added with 0 accepted args, matching its original registration in
	// fluid-checkout/inc/checkout-steps.php:89 ("0 = No arguments because the
	// action passed in an object of unexpected type"). Without the explicit
	// `, 0` here, WordPress defaults to 1 accepted arg, so the WC_Checkout
	// object that `do_action( 'fc_checkout_before_steps', $checkout )` passes
	// lands in $context, and output_checkout_progress_bar( $context ) forwards
	// it into get_current_step( $context ), which fatals on the string
	// concatenation `'current_step_' . $context` ("Object of class
	// WC_Checkout could not be converted to string"). That kills the entire
	// checkout page output with nothing written to wp-content/debug.log; it
	// only surfaced running wp-cli eval directly, which prints the uncaught
	// Error and stack trace.
	//
	// Confirmed live on bonza-retheme.blz.au 2026-08-27. The fatal was
	// dormant on every site using this file because Fluid Checkout Pro alone,
	// without the free "Fluid Checkout" base plugin also active, never
	// reaches multi-step rendering at all: the free plugin's own step-split
	// template only loads once that free plugin is installed and active. It
	// first surfaced when the free plugin was installed on Bonza staging to
	// unblock the checkout step 1 through 3 Figma builds, and would hit any
	// other site running this theme with both plugins active and multi-step
	// enabled.
	add_action( 'fc_checkout_before_steps', array( $steps, 'output_checkout_progress_bar' ), 1, 0 );
	add_action( 'fc_checkout_before_steps', array( $steps, 'output_checkout_notices_wrapper_start_tag' ), 2 );
	add_action( 'fc_checkout_before_steps', array( $steps, 'output_checkout_notices_wrapper_end_tag' ), 99 );
}, 20 );
