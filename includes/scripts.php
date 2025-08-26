<?php


add_action( 'wp_enqueue_scripts', function () {

	$template_uri = get_stylesheet_directory_uri();
	wp_enqueue_style( 'parent-style', $template_uri . '/style.css' );

	// Enqueue child style
	wp_enqueue_style(
		'blocksy-child-search-style',
		$template_uri . '/assets/css/search.css',
		array( 'parent-style' )
	);

	// Enqueue footer style
	wp_enqueue_style(
		'blocksy-child-footer-style',
		$template_uri . '/assets/css/footer.css',
		array( 'parent-style' )
	);

		// Enqueue header style
    	wp_enqueue_style(
    		'blocksy-child-header-style',
    		$template_uri . '/assets/css/header.css',
    		array( 'parent-style' )
    	);

} );
