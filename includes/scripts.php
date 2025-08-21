<?php


add_action( 'wp_enqueue_scripts', function () {

	// $template_uri = get_stylesheet_directory_uri();
	// wp_enqueue_style( 'parent-style', $template_uri . '/style.css' );

	// // Enqueue child style
	wp_enqueue_style(
		'blocksy-child-search-style',
		BLAZE_BLOCKSY_URL . '/assets/css/search.css',
		array( 'parent-style' )
	);

} );
