<?php
/**
 * Recently Viewed Products - Auto Display After Related Products
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add recently viewed products after related products
 */
add_action( 'woocommerce_after_single_product', 'display_recently_viewed_products', 125 );

/**
 * Get recently viewed products from cookie
 */
function get_recently_viewed_products_from_cookie() {
	$cookie_name = 'recently_viewed_products';

	if ( ! isset( $_COOKIE[ $cookie_name ] ) ) {
		return array();
	}

	$products = json_decode( stripslashes( $_COOKIE[ $cookie_name ] ), true );

	if ( ! is_array( $products ) ) {
		return array();
	}

	return $products;
}

/**
 * Display recently viewed products section
 */
function display_recently_viewed_products() {
	global $product;

	$current_product_id = $product->get_id();

	// Get recently viewed products from cookie
	$recently_viewed_products = get_recently_viewed_products_from_cookie();

	// Filter out current product and limit to 4 products
	$product_ids = array_filter( $recently_viewed_products, function ($id) use ($current_product_id) {
		return intval( $id ) !== $current_product_id;
	} );
	$product_ids = array_slice( $product_ids, 0, 4 );

	// If no products to show, don't display the section
	if ( empty( $product_ids ) ) {
		return;
	}

	?>

	<section class="recently-viewed-products up-sells products is-width-constrained">
		<h2 class="ct-module-title">Recently Viewed Products</h2>

		<div class="products columns-4" data-products="type-1" data-hover="zoom-in">
			<?php
			// Set up WooCommerce loop
			global $woocommerce_loop;
			$woocommerce_loop['is_shortcode'] = true;
			$woocommerce_loop['columns'] = 4;

			foreach ( $product_ids as $product_id ) {
				$product_obj = wc_get_product( intval( $product_id ) );

				if ( ! $product_obj || ! $product_obj->is_visible() ) {
					continue;
				}

				// Set global product untuk template
				$GLOBALS['product'] = $product_obj;

				// Render menggunakan WooCommerce content template
				wc_get_template_part( 'content', 'product' );
			}
			?>
		</div>
	</section>



	<?php
}

/**
 * Track viewed product in cookie
 */
add_action( 'template_redirect', 'track_recently_viewed_product' );

function track_recently_viewed_product() {
	if ( ! is_product() ) {
		return;
	}

	global $product;

	if ( ! $product ) {
		return;
	}

	if ( ! is_a( $product, 'WC_Product' ) ) {
		global $post;
		$product = wc_get_product( $post->ID );
	}

	$product_id = $product->get_id();
	$cookie_name = 'recently_viewed_products';

	// Get existing products from cookie
	$recently_viewed = get_recently_viewed_products_from_cookie();

	// Remove current product if it already exists (avoid duplicates)
	$recently_viewed = array_filter( $recently_viewed, function ($id) use ($product_id) {
		return intval( $id ) !== $product_id;
	} );

	// Add current product to the beginning
	array_unshift( $recently_viewed, $product_id );

	// Limit to maximum 20 products
	if ( count( $recently_viewed ) > 20 ) {
		$recently_viewed = array_slice( $recently_viewed, 0, 20 );
	}

	// Set cookie for 30 days
	$cookie_value = json_encode( $recently_viewed );
	setcookie( $cookie_name, $cookie_value, time() + ( 30 * 24 * 60 * 60 ), '/' );

	// Also set for current request
	$_COOKIE[ $cookie_name ] = $cookie_value;
}


