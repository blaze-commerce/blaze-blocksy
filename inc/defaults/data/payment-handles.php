<?php
/**
 * Payment gateway asset handles that `payment-assets-scope` keeps off pages
 * that take no payment. Extend with the `blocksy_child_payment_assets` filter.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'scripts' => [
		'ppcp-smart-button',
		'wc-zipmoney-script',
		'wc-zipmoney-script-order-button',
		'eway-checkout-sdk-js',
		'eway-cerberus-js',
		'eway-secure-fields',
		'wc-jquery-payment',
	],
	'styles'  => [
		'gateway',
		'wc-zipmoney-style',
	],
];
