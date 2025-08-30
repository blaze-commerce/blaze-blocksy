<?php

// Ensure is_plugin_active() function is available
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

add_action(
	'wp_enqueue_scripts',
	function () {

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

		// Enqueue BlazeCommerce minicart control script
		wp_enqueue_script(
			'blazecommerce-minicart-control',
			$template_uri . '/assets/js/minicart-control.js',
			array( 'jquery', 'wc-add-to-cart' ),
			'1.0.0',
			true
		);
	}
);
