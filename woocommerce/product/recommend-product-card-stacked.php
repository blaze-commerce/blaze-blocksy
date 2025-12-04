<?php
/**
 * Recommended Product Card - Stacked Layout
 *
 * This template displays recommended products in a stacked/horizontal layout
 * similar to mini cart items.
 *
 * @package BlazeBlocksy
 */

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
<div class="recommended-product-item-stacked">
	<a href="<?php echo esc_url( $product_url ); ?>" class="product-link-stacked">
		<figure class="product-image-wrapper">
			<?php
			$image_id = $product->get_image_id();
			if ( $image_id ) {
				echo wp_get_attachment_image( $image_id, 'woocommerce_gallery_thumbnail', false, array( 'loading' => 'lazy' ) );
			} else {
				echo '<img src="' . esc_url( wc_placeholder_img_src() ) . '" alt="' . esc_attr( $product->get_name() ) . '" loading="lazy">';
			}
			?>
		</figure>
		<div class="product-info">
			<span class="product-title"><?php echo esc_html( $product_name ); ?></span>
			<div class="product-price-quantity">
				<span class="product-price"><?php echo $product_price; ?></span>
			</div>
		</div>
	</a>
</div>

