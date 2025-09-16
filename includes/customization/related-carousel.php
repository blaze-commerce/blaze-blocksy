<?php

/**
 * Related carousel functionality
 * Assets are now handled in scripts.php
 */

add_action( 'wp_footer', function () {
	if ( ! is_product() )
		return;

	?>
	<script>
		const carouselConfig = {
			loop: false,
			margin: 24,
			nav: true,
			dots: true,
			responsive: {
				0: {
					items: 2,
					nav: false
				},
				1000: {
					items: 4
				}
			}
		}
		jQuery(document).ready(function ($) {
			// Initialize carousel for related products and up-sells (excluding recently-viewed which uses AJAX)
			$('.related.products .products, .up-sells.products:not(.recently-viewed-products) .products').addClass('owl-carousel owl-theme');
			$('.related.products .owl-carousel').owlCarousel(carouselConfig);

			$('.up-sells.products:not(.recently-viewed-products) .owl-carousel').owlCarousel(carouselConfig);
		});
	</script>
	<?php
} );

add_filter( 'woocommerce_product_loop_start', function ($echo) {
	if ( ( ! is_product() || is_archive() ) ) {
		if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'get_recently_viewed_products' )
			return $echo;
	}

	return str_replace( '<ul', '<div', $echo );

}, 999 );

add_filter( 'woocommerce_product_loop_end', function ($echo) {
	if ( ( ! is_product() || is_archive() ) ) {
		if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'get_recently_viewed_products' )
			return $echo;
	}

	//replace ul with div with preg_replace
	$echo = preg_replace( '/<\/ul>/', '</div>', $echo );

	return;
}, 999 );

// modify wc_get_template_part filter for content-content
add_filter( 'wc_get_template_part', function ($template, $slug, $name) {

	if ( 'product/recommend-product-card' === $slug ) {
		return BLAZE_BLOCKSY_PATH . '/woocommerce/product/recommend-product-card.php';
	}

	if ( ! is_product() || is_archive() )
		return $template;

	if ( 'content' === $slug && 'product' === $name ) {
		return BLAZE_BLOCKSY_PATH . '/woocommerce/content/product.php';
	}


	return $template;
}, 10, 3 );


