<?php

/**
 * Enqueue product category styles
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_product_category() && ! is_product_tag() && ! is_shop() )
		return;

	wp_enqueue_style( 'blaze-blocksy-archive', BLAZE_BLOCKSY_URL . '/assets/css/archive.css' );
	wp_enqueue_script( 'blaze-blocksy-archive', BLAZE_BLOCKSY_URL . '/assets/js/archive.js', array( 'jquery' ), '1.0.0', true );
} );


/**
 * Display category description
 */
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
