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
	}
);
