<?php
/**
 * Gift Card Checkout Compat — Fluid Checkout + PW Gift Cards.
 *
 * Defensive priority guard: PWGC's woocommerce_coupon_is_valid hook runs at
 * priority 11 while Smart Coupon Pro (wt-smart-coupon-pro) runs at priority 10.
 * If SC Pro throws WC_Coupon_Exception before PWGC validates the gift card,
 * the code fails silently. Running at priority 25 guarantees we catch that case.
 *
 * PWGC identifies its virtual coupons with description 'pw_gift_card'
 * (set in woocommerce_get_shop_coupon_data).
 *
 * Label/placeholder changes live in custom/checkout-order-summary.php
 * via fc_expansible_section_toggle_label_coupon_code and
 * fc_coupon_code_field_placeholder filters.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_coupon_is_valid', function( $valid, $coupon ) {
	if ( $coupon instanceof WC_Coupon && 'pw_gift_card' === $coupon->get_description() ) {
		return true;
	}
	return $valid;
}, 25, 2 );
