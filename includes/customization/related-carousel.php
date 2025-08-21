<?php

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_product() )
		return;

	wp_enqueue_style( 'blaze-blocksy-single-product', BLAZE_BLOCKSY_URL . '/assets/css/single-product.css' );

	// load owl carousel library js and css via cdn
	wp_enqueue_style( 'owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css', array( 'blaze-blocksy-single-product' ) );
	wp_enqueue_style( 'owl-theme-default', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css', array( 'owl-carousel' ) );
	wp_enqueue_script( 'owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', array( 'jquery' ), null, true );

} );

add_action( 'wp_footer', function () {
	if ( ! is_product() )
		return;

	?>
	<script>
		const carouselConfig = {
			loop: false,
			margin: 24,
			nav: false,
			dots: true,
			responsive: {
				0: {
					items: 1,
				},
				600: {
					items: 2,
				},
				1000: {
					items: 4
				}
			}
		}
		jQuery(document).ready(function ($) {
			$('.related.products .products, .upsells.products .products').addClass('owl-carousel owl-theme');
			$('.related.products .owl-carousel').owlCarousel(carouselConfig);

			$('.upsells.products .owl-carousel').owlCarousel(carouselConfig);
		});
	</script>
	<?php
} );

add_filter( 'woocommerce_product_loop_start', function ($echo) {
	if ( ! is_product() )
		return;

	return str_replace( '<ul', '<div', $echo );

}, 999 );

add_filter( 'woocommerce_product_loop_end', function ($echo) {
	if ( ! is_product() )
		return;

	//replace ul with div with preg_replace
	$echo = preg_replace( '/<\/ul>/', '</div>', $echo );

	return;
}, 999 );

// modify wc_get_template_part filter for content-content
add_filter( 'wc_get_template_part', function ($template, $slug, $name) {
	if ( 'content' === $slug && 'product' === $name ) {
		do_action( 'qm/info', [ 
			'template' => $template,
			'slug' => $slug,
			'name' => $name,
		] );
		return BLAZE_BLOCKSY_PATH . '/woocommerce/content/product.php';
	}
	return $template;
}, 10, 3 );


