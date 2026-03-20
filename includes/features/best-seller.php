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
 * Uses posts_clauses with LEFT JOIN so ALL products appear,
 * with best sellers sorted first.
 */
add_filter( 'woocommerce_get_catalog_ordering_args', function ( $args ) {
	if ( ! isset( $_GET['orderby'] ) || $_GET['orderby'] !== 'best_seller' ) {
		return $args;
	}

	// Override default ordering — actual sort handled by posts_clauses filter.
	$args['orderby'] = 'date';
	$args['order']   = 'DESC';

	add_filter( 'posts_clauses', 'blaze_blocksy_best_seller_sort_clauses', 10, 2 );

	return $args;
} );

/**
 * Modify SQL clauses to sort best sellers first via LEFT JOIN.
 *
 * @param array    $clauses SQL clauses.
 * @param WP_Query $query   Current query.
 * @return array Modified clauses.
 */
function blaze_blocksy_best_seller_sort_clauses( $clauses, $query ) {
	global $wpdb;

	// Only apply once.
	remove_filter( 'posts_clauses', 'blaze_blocksy_best_seller_sort_clauses', 10 );

	$clauses['join']    .= " LEFT JOIN {$wpdb->postmeta} AS bs_meta ON ({$wpdb->posts}.ID = bs_meta.post_id AND bs_meta.meta_key = '_best_seller') ";
	$clauses['orderby']  = "CASE WHEN bs_meta.meta_value = 'yes' THEN 0 ELSE 1 END ASC, {$wpdb->posts}.post_date DESC";

	return $clauses;
}

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
				<option value="no"><?php esc_html_e( 'No', 'blaze-blocksy' ); ?></option>
				<option value="yes"><?php esc_html_e( 'Yes', 'blaze-blocksy' ); ?></option>
			</select>
		</span>
	</label>
	<?php
} );

/**
 * Save "Best Seller" meta from quick edit.
 */
add_action( 'woocommerce_product_quick_edit_save', function ( $product ) {
	if ( ! isset( $_REQUEST['_best_seller_quick'] ) ) {
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
 * Append best seller value to WooCommerce inline data for quick edit.
 */
add_action( 'manage_product_posts_custom_column', function ( $column, $post_id ) {
	if ( $column === 'name' ) {
		$value = get_post_meta( $post_id, '_best_seller', true ) === 'yes' ? 'yes' : 'no';
		echo '<div class="hidden best_seller_inline_data" data-best-seller="' . esc_attr( $value ) . '"></div>';
	}
}, 99, 2 );

/**
 * Enqueue admin JS to populate quick edit best seller field.
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( $hook !== 'edit.php' ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'product' ) {
		return;
	}

	wp_add_inline_script( 'wc-admin-product-quick-edit', "
		jQuery(function($) {
			$('#the-list').on('click', '.editinline', function() {
				var post_id = $(this).closest('tr').attr('id').replace('post-', '');
				var best_seller = $('#post-' + post_id + ' .best_seller_inline_data').data('best-seller') || 'no';
				$('select[name=\"_best_seller_quick\"]', '.inline-edit-row').val(best_seller);
			});
		});
	" );
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
