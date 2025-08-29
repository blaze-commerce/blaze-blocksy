<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

global $product;

if ( ! $product || ! method_exists( $product, 'get_name' ) ) {
	return;
}

$product_url = $product->get_permalink();
$product_name = $product->get_name();
$product_price = $product->get_price_html();

?>
<div class="recommended-product-item">
	<a href="<?php echo esc_url( $product_url ); ?>" class="product-link">
		<div class="product-image">
			<?php
			$image_id = $product->get_image_id();
			if ( $image_id ) {
				echo wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, array( 'loading' => 'lazy' ) );
			} else {
				echo '<img src="' . esc_url( wc_placeholder_img_src() ) . '" alt="' . esc_attr( $product->get_name() ) . '" loading="lazy">';
			}
			?>
		</div>
		<div class="product-info">
			<h5 class="product-title"><?php echo esc_html( $product_name ); ?></h5>
			<div class="product-price"><?php echo $product_price; ?></div>
		</div>
	</a>
</div>