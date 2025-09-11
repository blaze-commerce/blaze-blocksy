<?php
/**
 * Recently Viewed Products - AJAX Implementation
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add recently viewed products after related products (AJAX placeholder)
 */
add_action( 'woocommerce_after_single_product', 'display_recently_viewed_products_placeholder', 125 );

/**
 * Display recently viewed products placeholder for AJAX loading
 */
function display_recently_viewed_products_placeholder() {
	global $product;
	$current_product_id = $product->get_id();
	?>
	<!-- Loading indicator for recently viewed products -->
	<div id="recently-viewed-loading" class="recently-viewed-loading"
		style="text-align: center; padding: 20px; display: block;">
		<div class="loading-spinner"
			style="display: inline-block; width: 20px; height: 20px; border: 2px solid #f3f3f3; border-top: 2px solid #333; border-radius: 50%; animation: spin 1s linear infinite;">
		</div>
		<span style="margin-left: 10px; color: #666;">Loading recently viewed products...</span>
	</div>

	<section class="recently-viewed-products up-sells products is-width-constrained" id="recently-viewed-section"
		style="display: none;">
		<h2 class="ct-module-title">Recently Viewed Products</h2>
		<div class="products columns-4" data-products="type-1" data-hover="zoom-in" id="recently-viewed-products-container">
			<!-- Products will be loaded via AJAX -->
		</div>
	</section>

	<style>
		@keyframes spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		.recently-viewed-loading {
			opacity: 1;
			transition: opacity 0.3s ease;
		}

		.recently-viewed-loading.fade-out {
			opacity: 0;
		}
	</style>

	<script>
		jQuery(document).ready(function ($) {
			// Load recently viewed products via AJAX (function defined in single-product.js)
			if (typeof loadRecentlyViewedProducts === 'function') {
				loadRecentlyViewedProducts(<?php echo $current_product_id; ?>);
			}
		});
	</script>
	<?php
}

/**
 * Get recently viewed products from localStorage/sessionStorage with cookie fallback
 */
function get_recently_viewed_products_from_storage() {
	// This function will be called via AJAX, so we check both cookie and POST data
	$products = array();

	// Check if products are sent via AJAX POST
	if ( isset( $_POST['recently_viewed'] ) && is_array( $_POST['recently_viewed'] ) ) {
		$products = array_map( 'intval', $_POST['recently_viewed'] );
	} else {
		// Fallback to cookie
		$cookie_name = 'recently_viewed_products';
		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			$cookie_products = json_decode( stripslashes( $_COOKIE[ $cookie_name ] ), true );
			if ( is_array( $cookie_products ) ) {
				$products = array_map( 'intval', $cookie_products );
			}
		}
	}

	return $products;
}

/**
 * AJAX handler for getting recently viewed products
 */
add_action( 'wp_ajax_get_recently_viewed_products', 'ajax_get_recently_viewed_products' );
add_action( 'wp_ajax_nopriv_get_recently_viewed_products', 'ajax_get_recently_viewed_products' );

function ajax_get_recently_viewed_products() {
	// Verify nonce for security
	if ( ! wp_verify_nonce( $_POST['nonce'], 'recently_viewed_nonce' ) ) {
		wp_die( 'Security check failed' );
	}

	$current_product_id = intval( $_POST['current_product_id'] );

	// Get recently viewed products
	$recently_viewed_products = get_recently_viewed_products_from_storage();

	// Filter out current product and limit to 10 products
	$product_ids = array_filter( $recently_viewed_products, function ($id) use ($current_product_id) {
		return intval( $id ) !== $current_product_id;
	} );
	$product_ids = array_slice( $product_ids, 0, 10 );

	// If no products to show, return empty
	if ( empty( $product_ids ) ) {
		wp_send_json_success( array( 'html' => '', 'has_products' => false ) );
		return;
	}

	// Start output buffering
	ob_start();

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

	$html = ob_get_clean();

	wp_send_json_success( array(
		'html' => $html,
		'has_products' => ! empty( $product_ids ),
		'product_count' => count( $product_ids )
	) );
}

/**
 * Get recently viewed products from cookie (legacy function for compatibility)
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
 * Add recently viewed products data to single product localize script
 */
add_filter( 'blaze_blocksy_single_product_localize_data', 'add_recently_viewed_localize_data' );

function add_recently_viewed_localize_data( $data ) {
	if ( ! is_product() ) {
		return $data;
	}

	global $product;

	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return $data;
	}

	// Add recently viewed specific data
	$data['recently_viewed'] = array(
		'nonce' => wp_create_nonce( 'recently_viewed_nonce' ),
		'current_product_id' => $product->get_id()
	);

	return $data;
}


