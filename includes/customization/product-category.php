<?php

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_product_category() )
		return;

	wp_enqueue_style( 'blaze-blocksy-product-category', BLAZE_BLOCKSY_URL . '/assets/css/product-category.css' );
} );
