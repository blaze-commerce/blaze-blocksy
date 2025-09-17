<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if Judge.me plugin is active before proceeding
// Include the plugin.php file to use is_plugin_active function
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( ! is_plugin_active( 'judgeme-product-reviews-woocommerce/judgeme.php' ) &&
	! function_exists( 'judgeme_init' ) ) {
	return;
}


add_filter( 'woocommerce_product_tabs', function (array $tabs) {
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