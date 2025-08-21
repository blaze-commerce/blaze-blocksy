<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_filter( 'woocommerce_product_tabs', function (array $tabs) {
	$tabs['judgeme_tab'] = array(
		'title' => __( 'Judgeme', 'textdomain' ),
		'priority' => 50,
		'callback' => 'blaze_blocksy_render_judgeme_tab',
	);
	return $tabs;
} );

function blaze_blocksy_render_judgeme_tab() {
	global $product;
	?>
	<h2>Customer Review</h2>
	<div>

		<?php echo do_shortcode( '[jgm-review-widget id="' . $product->get_id() . '"]' ); ?>
	</div>
	<?php
}