<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_filter( 'woocommerce_product_tabs', function (array $tabs) {

	if ( ! class_exists( 'Judgeme_WooCommerce' ) ) {
		return $tabs;
	}

	$tabs['judgeme_tab'] = array(
		'title' => __( 'Reviews', 'textdomain' ),
		'priority' => 50,
		'callback' => 'blaze_blocksy_render_judgeme_tab',
	);
	return $tabs;
} );

function blaze_blocksy_render_judgeme_tab() {
	global $product;

	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	?>
	<h2>Customer Review</h2>
	<div>

		<?php echo do_shortcode( '[jgm-review-widget id="' . $product->get_id() . '"]' ); ?>
	</div>
	<?php
}