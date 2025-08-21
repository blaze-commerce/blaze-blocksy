<?php

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'blaze-blocksy-mini-cart', BLAZE_BLOCKSY_URL . '/assets/css/mini-cart.css' );
} );
