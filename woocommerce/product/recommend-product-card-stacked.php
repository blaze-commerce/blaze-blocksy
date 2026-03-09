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

$product_url  = $product->get_permalink();
$product_name = $product->get_name();
$product_price = $product->get_price_html();

/**
 * Determine the correct add-to-cart behavior:
 *
 * - Simple / subscription (no variations): AJAX add directly.
 * - Variable / variable-subscription with exactly 1 child: use variation_id
 *   as the product_id so WC_Cart routes it correctly without UI selection.
 * - Variable / variable-subscription with multiple children: redirect to
 *   the product page so the user can choose a variation.
 * - Any other type (grouped, bundle, composite …): redirect to product page.
 */
$btn_product_id  = $product->get_id();
$btn_redirect_url = null;
$variable_types  = array( 'variable', 'variable-subscription' );

if ( in_array( $product->get_type(), $variable_types, true ) ) {
	$children = $product->get_children(); // array of variation post IDs
	if ( count( $children ) === 1 ) {
		// Exactly one variation — auto-select it by using its ID as the cart product_id.
		// WC_Cart::add_to_cart() detects post_type === 'product_variation' and resolves
		// parent + attributes automatically (works for WCS subscription_variation too).
		$btn_product_id = $children[0];
	} else {
		// Multiple (or zero) variations — send the user to the product page.
		$btn_redirect_url = $product_url;
	}
} elseif ( ! in_array( $product->get_type(), array( 'simple', 'subscription' ), true ) ) {
	// Grouped, bundle, composite, etc. — not directly addable via AJAX.
	$btn_redirect_url = $product_url;
}

?>
<div class="recommended-product-item-stacked">
	<a href="<?php echo esc_url( $product_url ); ?>" class="product-image-link" tabindex="-1" aria-hidden="true">
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
	</a>
	<div class="product-info">
		<a href="<?php echo esc_url( $product_url ); ?>" class="product-title-link">
			<span class="product-title"><?php echo esc_html( $product_name ); ?></span>
		</a>
		<div class="product-price-quantity">
			<span class="product-price"><?php echo $product_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</div>
		<?php if ( $product->is_purchasable() && $product->is_in_stock() && WC()->cart && ! WC()->cart->is_empty() ) : ?>
		<button type="button"
			class="rec-add-to-cart-btn"
			data-product-id="<?php echo esc_attr( $btn_product_id ); ?>"
			data-product-type="<?php echo esc_attr( $product->get_type() ); ?>"
			<?php if ( $btn_redirect_url ) : ?>
			data-redirect-url="<?php echo esc_url( $btn_redirect_url ); ?>"
			<?php endif; ?>
			aria-label="<?php echo esc_attr( sprintf( __( 'Add %s to cart', 'woocommerce' ), $product_name ) ); ?>">
			<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
				<path d="M4 8H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M8 4V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<?php esc_html_e( 'Add', 'woocommerce' ); ?>
		</button>
		<?php endif; ?>
	</div>
</div>
