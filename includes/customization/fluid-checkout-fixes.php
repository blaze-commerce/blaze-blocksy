<?php
/**
 * Fluid Checkout Fixes
 *
 * This file contains fixes and customizations for the Fluid Checkout plugin.
 * It is loaded conditionally only when Fluid Checkout is active.
 *
 * @package    BlazeCommerce
 * @subpackage BlocksyChild
 * @since      1.41.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if Fluid Checkout plugin is active before applying fixes.
 *
 * This ensures we don't apply filters that won't have any effect
 * and prevents potential conflicts if the plugin is deactivated.
 */
if ( ! class_exists( 'FluidCheckout' ) ) {
	return;
}

/**
 * Fix spacing issue in Fluid Checkout payment not needed message.
 *
 * ISSUE:
 * When the order total is $0 (e.g., when a 100% discount coupon is applied
 * and no shipping is required), the Fluid Checkout plugin displays a notification
 * message: "Your order has a total amount due of$0. No further payment is needed."
 * Notice the missing space between "of" and "$0".
 *
 * ROOT CAUSE:
 * The issue occurs in the Fluid Checkout plugin template at:
 * /wp-content/plugins/fluid-checkout/templates/fc/checkout-steps/checkout/payment.php
 *
 * The sprintf() function with wc_price(0) can cause spacing issues due to
 * HTML rendering when the WooCommerce price span elements are inserted.
 *
 * SOLUTION:
 * Override the message using the 'fc_payment_not_needed_message' filter hook
 * provided by Fluid Checkout. We insert a non-breaking space (&nbsp;) before
 * the price to ensure proper spacing regardless of CSS or HTML rendering.
 *
 * @since 1.41.0
 * @see   /wp-content/plugins/fluid-checkout/templates/fc/checkout-steps/checkout/payment.php
 * @link  https://fluidcheckout.com/docs/
 */
if ( ! function_exists( 'blaze_fix_fluid_checkout_payment_notification_spacing' ) ) {

	/**
	 * Fix the payment not needed message spacing.
	 *
	 * @param string $message The original payment not needed message with HTML.
	 * @return string The modified message with proper spacing using non-breaking space.
	 */
	function blaze_fix_fluid_checkout_payment_notification_spacing( $message ) {
		// Safety check: Ensure WooCommerce pricing function exists.
		if ( ! function_exists( 'wc_price' ) ) {
			// Log error for debugging without breaking the site.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Blaze Fluid Checkout Fix: wc_price() function not available.' );
			}
			return $message;
		}

		// Get the formatted price for $0.
		$price = wc_price( 0 );

		// Build the message with proper spacing using a non-breaking space (&nbsp;).
		// This ensures the space is preserved regardless of HTML/CSS rendering.
		$new_message = sprintf(
			/* translators: %s: Order total amount (formatted price, e.g., $0.00) */
			esc_html__( 'Your order has a total amount due of', 'fluid-checkout' ) . '&nbsp;%s. ' . esc_html__( 'No further payment is needed.', 'fluid-checkout' ),
			$price
		);

		return $new_message;
	}

	// Hook into Fluid Checkout's filter with priority 10.
	add_filter( 'fc_payment_not_needed_message', 'blaze_fix_fluid_checkout_payment_notification_spacing', 10, 1 );
}

