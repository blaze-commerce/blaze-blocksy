<?php
/**
 * Best Seller Product Feature
 *
 * Adds a "Best Seller" checkbox to product General tab, displays badge
 * on all product listings, and provides catalog sorting by best seller status.
 *
 * @package BlazeBlocksy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BLAZE_BEST_SELLER_BADGE' ) ) {
	define( 'BLAZE_BEST_SELLER_BADGE', true );
}

if ( ! BLAZE_BEST_SELLER_BADGE ) {
	return;
}

/**
 * Add "Best Seller" checkbox to the product General tab.
 */
add_action( 'woocommerce_product_options_general_product_data', function () {
	woocommerce_wp_checkbox( [
		'id'          => '_best_seller',
		'label'       => __( 'Best Seller', 'blaze-blocksy' ),
		'description' => __( 'Mark this product as a best seller to display a badge on product listings.', 'blaze-blocksy' ),
	] );
} );

/**
 * Save "Best Seller" meta on product save.
 */
add_action( 'woocommerce_process_product_meta', function ( $post_id ) {
	if ( ! empty( $_POST['_best_seller'] ) ) {
		update_post_meta( $post_id, '_best_seller', 'yes' );
	} else {
		delete_post_meta( $post_id, '_best_seller' );
	}
} );

/**
 * Generate the best seller badge HTML.
 *
 * @return string Badge HTML markup.
 */
function blaze_blocksy_best_seller_badge_html() {
	$shape = function_exists( 'blocksy_get_theme_mod' )
		? blocksy_get_theme_mod( 'sale_badge_shape', 'type-2' )
		: 'type-2';

	return '<span class="ct-woo-badge-best-seller" data-shape="' . esc_attr( $shape ) . '">'
		. esc_html__( 'Best Seller', 'blaze-blocksy' )
		. '</span>';
}

/**
 * Check if a product is marked as best seller.
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function blaze_blocksy_is_best_seller( $product_id ) {
	return get_post_meta( $product_id, '_best_seller', true ) === 'yes';
}

/**
 * Add best seller badge on archive/shop product cards.
 */
add_filter( 'blocksy:woocommerce:product-card:badges', function ( $badges ) {
	global $product;

	if ( $product && blaze_blocksy_is_best_seller( $product->get_id() ) ) {
		$badges[] = blaze_blocksy_best_seller_badge_html();
	}

	return $badges;
} );

/**
 * Add best seller badge on single product page.
 */
add_filter( 'blocksy:woocommerce:single:after-sale-badge', function ( $badges ) {
	global $product;

	if ( $product && blaze_blocksy_is_best_seller( $product->get_id() ) ) {
		$badges[] = blaze_blocksy_best_seller_badge_html();
	}

	return $badges;
} );

/**
 * Add "Best Seller" option to catalog ordering dropdown.
 */
add_filter( 'woocommerce_catalog_orderby', function ( $options ) {
	$options['best_seller'] = __( 'Best Seller', 'blaze-blocksy' );
	return $options;
} );

/**
 * Handle "Best Seller" sorting query args.
 *
 * Uses a meta_query with OR relation so all products appear,
 * with best sellers sorted first.
 */
add_filter( 'woocommerce_get_catalog_ordering_args', function ( $args ) {
	if ( ! isset( $_GET['orderby'] ) || $_GET['orderby'] !== 'best_seller' ) {
		return $args;
	}

	$args['orderby']  = 'meta_value';
	$args['order']    = 'DESC';
	$args['meta_key'] = '_best_seller';

	// Include products without _best_seller meta (they appear after best sellers).
	$args['meta_query'] = [
		'relation' => 'OR',
		[
			'key'     => '_best_seller',
			'value'   => 'yes',
			'compare' => '=',
		],
		[
			'key'     => '_best_seller',
			'compare' => 'NOT EXISTS',
		],
	];

	return $args;
} );

/**
 * Add "Best Seller" select to bulk edit form.
 */
add_action( 'woocommerce_product_bulk_edit_end', function () {
	?>
	<label>
		<span class="title"><?php esc_html_e( 'Best Seller?', 'blaze-blocksy' ); ?></span>
		<span class="input-text-wrap">
			<select class="best_seller" name="_best_seller_bulk">
				<option value=""><?php esc_html_e( '— No change —', 'blaze-blocksy' ); ?></option>
				<option value="yes"><?php esc_html_e( 'Yes', 'blaze-blocksy' ); ?></option>
				<option value="no"><?php esc_html_e( 'No', 'blaze-blocksy' ); ?></option>
			</select>
		</span>
	</label>
	<?php
} );

/**
 * Save "Best Seller" meta from bulk edit.
 */
add_action( 'woocommerce_product_bulk_edit_save', function ( $product ) {
	if ( ! isset( $_REQUEST['_best_seller_bulk'] ) || $_REQUEST['_best_seller_bulk'] === '' ) {
		return;
	}

	$post_id = $product->get_id();

	if ( $_REQUEST['_best_seller_bulk'] === 'yes' ) {
		update_post_meta( $post_id, '_best_seller', 'yes' );
	} else {
		delete_post_meta( $post_id, '_best_seller' );
	}
} );

/**
 * Add "Best Seller" select to quick edit form.
 */
add_action( 'woocommerce_product_quick_edit_end', function () {
	?>
	<label>
		<span class="title"><?php esc_html_e( 'Best Seller?', 'blaze-blocksy' ); ?></span>
		<span class="input-text-wrap">
			<select class="best_seller" name="_best_seller_quick">
				<option value=""><?php esc_html_e( '— No change —', 'blaze-blocksy' ); ?></option>
				<option value="yes"><?php esc_html_e( 'Yes', 'blaze-blocksy' ); ?></option>
				<option value="no"><?php esc_html_e( 'No', 'blaze-blocksy' ); ?></option>
			</select>
		</span>
	</label>
	<?php
} );

/**
 * Save "Best Seller" meta from quick edit.
 */
add_action( 'woocommerce_product_quick_edit_save', function ( $product ) {
	if ( ! isset( $_REQUEST['_best_seller_quick'] ) || $_REQUEST['_best_seller_quick'] === '' ) {
		return;
	}

	$post_id = $product->get_id();

	if ( $_REQUEST['_best_seller_quick'] === 'yes' ) {
		update_post_meta( $post_id, '_best_seller', 'yes' );
	} else {
		delete_post_meta( $post_id, '_best_seller' );
	}
} );

/**
 * Enqueue best seller badge styles.
 */
add_action( 'wp_enqueue_scripts', function () {
	$css_path = '/assets/css/best-seller.css';
	$file     = BLAZE_BLOCKSY_PATH . $css_path;

	if ( file_exists( $file ) ) {
		wp_enqueue_style(
			'blaze-blocksy-best-seller',
			BLAZE_BLOCKSY_URL . $css_path,
			[],
			filemtime( $file )
		);
	}
} );
