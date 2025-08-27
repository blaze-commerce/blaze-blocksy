<?php

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_product_category() && ! is_product_tag() && ! is_shop() )
		return;

	wp_enqueue_style( 'blaze-blocksy-product-category', BLAZE_BLOCKSY_URL . '/assets/css/product-category.css' );
} );


add_action( 'wp_footer', function () {

	if ( ! is_product_category() && ! is_shop() )
		return;

	?>
	<script>
		(function ($) {
			const displayProductCount = function () {
				const theText = $('.woocommerce-result-count').text();
				$('.ct-product-category-count').text(theText);
			}
			$(document).ready(function () {
				// add element to .ct-pagination
				$('.ct-pagination').prepend('<div class="ct-product-category-count"></div>');
				displayProductCount();
			});
		})(jQuery)
	</script>
	<?php
} );

add_action( 'woocommerce_after_shop_loop', function () {
	if ( ! is_product_category() ) {
		return;
	}
	$term = get_queried_object();
	$term_title = $term->name;
	$description = $term->description;


	?>
	<div class="ct-product-category-description-wrapper">
		<h4 class="ct-module-title"><?php echo $term_title; ?></h4>
		<div class="ct-product-category-description">
			<?php echo $description; ?>
		</div>
	</div>
	<?php
}, 9999 );
