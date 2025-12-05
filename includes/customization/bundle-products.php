<?php
/**
 * WooCommerce Bundle Product Customizations
 *
 * Modifies bundle product display on archive/shop pages to show
 * "Select Options" button instead of "Add to Cart".
 *
 * @package BlazeBlocksy
 * @since 1.45.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Change button text for bundles on archive pages
 *
 * @param string     $text    Button text.
 * @param WC_Product $product Product object.
 * @return string
 */
function blaze_blocksy_bundle_custom_button_text( $text, $product ) {
	if ( ! is_product() && $product->is_type( 'bundle' ) ) {
		return __( 'Select Options', 'woocommerce' );
	}
	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'blaze_blocksy_bundle_custom_button_text', 10, 2 );

/**
 * Change button URL for bundles on archive pages
 *
 * @param string     $url     Button URL.
 * @param WC_Product $product Product object.
 * @return string
 */
function blaze_blocksy_bundle_custom_button_url( $url, $product ) {
	if ( ! is_product() && $product->is_type( 'bundle' ) ) {
		return get_permalink( $product->get_id() );
	}
	return $url;
}
add_filter( 'woocommerce_product_add_to_cart_url', 'blaze_blocksy_bundle_custom_button_url', 10, 2 );
