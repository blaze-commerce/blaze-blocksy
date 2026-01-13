<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * @package Blaze_Commerce
 * @subpackage Blaze_Commerce
 * @since 1.0.0
 */

/*
 * Add your custom functions here
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'custom-style', BLAZE_BLOCKSY_URL . '/custom/custom.css' );
		wp_enqueue_script( 'custom-script', BLAZE_BLOCKSY_URL . '/custom/custom.js', array( 'jquery' ), '1.0.0', true );

		// Enqueue Owl Carousel for responsive button slider
		wp_enqueue_style( 'owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css' );
		wp_enqueue_style( 'owl-theme-default', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css', array( 'owl-carousel' ) );
		wp_enqueue_script( 'owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', array( 'jquery' ), null, true );
	}
);


add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_style(
			'blocksy-child-editor-styles',
			BLAZE_BLOCKSY_URL . '/custom/custom.css',
			array(),
			filemtime( get_template_directory() . '/css/editor.css' )
		);
	}
);

// Change the product thumbnail size on the search suggestion
add_filter(
	'dgwt/wcas/tnt/indexer/readable/product/data',
	function ( $data, $product_id, $product ) {
		$thumbnail_url = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'full' );
		if ( is_array( $thumbnail_url ) && ! empty( $thumbnail_url[0] ) ) {
			$data['image'] = $thumbnail_url[0];
		}
		return $data;
	},
	10,
	3
);


require_once BLAZE_BLOCKSY_PATH . '/custom/page-meta-fields.php';
require_once BLAZE_BLOCKSY_PATH . '/custom/currency-based-page-display.php';
require_once BLAZE_BLOCKSY_PATH . '/custom/block-currency-visibility.php';
require_once BLAZE_BLOCKSY_PATH . '/custom/product-page.php';
require_once BLAZE_BLOCKSY_PATH . '/custom/add-to-cart-fix.php';
