<?php
/**
 * Speed Optimization
 *
 * Performance improvements for the theme.
 *
 * @package Blaze_Blocksy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', function () {
	if ( is_product() ) {
		global $product;

		if ( ! $product ) {
			$product = wc_get_product( get_the_ID() );
		}

		if ( $product ) {
			$image_id = $product->get_image_id();
			if ( $image_id ) {
				// Try woocommerce_single first, fall back to full
				$image_url = wp_get_attachment_image_url( $image_id, 'woocommerce_single' );
				if ( ! $image_url ) {
					$image_url = wp_get_attachment_image_url( $image_id, 'full' );
				}
				if ( $image_url ) {
					echo '<link rel="preload" as="image" href="' . esc_url( $image_url ) . '" fetchpriority="high">' . "\n";
				}
			}
		}
	}
}, 1 );

add_filter( 'perfmatters_delay_js_timeout', function ( $timeout ) {
	return '7';
} );
