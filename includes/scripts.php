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

	// Enqueue load more counter script on shop/category pages
	if ( is_shop() || is_product_category() || is_product_tag() ) {
		wp_enqueue_script(
			'blaze-blocksy-load-more-counter',
			BLAZE_BLOCKSY_URL . '/assets/js/load-more-counter.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		// Localize script for debugging
		wp_localize_script(
			'blaze-blocksy-load-more-counter',
			'blazeBlocksyLoadMore',
			array(
				'debug' => defined( 'WP_DEBUG' ) && WP_DEBUG,
			)
		);
	}

} );
