<?php

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'blaze-blocksy-mini-cart', BLAZE_BLOCKSY_URL . '/assets/css/mini-cart.css' );
} );

/**
 * Override mini cart template
 */
add_filter( 'wc_get_template', function ($template, $template_name, $args) {


	if ( 'cart/mini-cart.php' === $template_name ) {
		return BLAZE_BLOCKSY_PATH . '/woocommerce/cart/mini-cart.php';
	}

	return $template;
}, 999, 3 );
