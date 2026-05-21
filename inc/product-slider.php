<?php
/**
 * Product Slider — Custom shortcode for product carousels.
 *
 * Usage: [bc_product_slider ids="8020,8142,8259" columns="4" dots="1" arrows="1"]
 *
 * Uses WooCommerce's native product loop template (content-product.php)
 * so cards inherit all Blocksy styling: hover image swap, badges, wishlist, etc.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Temporarily override product title heading tag to H3.
 *
 * Used inside the product slider shortcode so product titles render as H3
 * (items within a section) instead of the global H2 default.
 * Only active during the slider's WC loop — removed immediately after.
 *
 * @param array $layout The woo_card_layout theme mod value.
 * @return array Modified layout with heading_tag set to h3.
 */
function bc_slider_h3_product_titles( $layout ) {
	if ( ! is_array( $layout ) ) {
		return $layout;
	}
	foreach ( $layout as &$item ) {
		if ( isset( $item['id'] ) && 'product_title' === $item['id'] ) {
			$item['heading_tag'] = 'h3';
		}
	}
	return $layout;
}

add_shortcode( 'bc_product_slider', 'bc_product_slider_shortcode' );

/**
 * Render the product slider shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function bc_product_slider_shortcode( $atts ) {
	$atts = shortcode_atts( [
		'ids'     => '',
		'columns' => 4,
		'dots'    => 1,
		'arrows'  => 1,
		'limit'   => 8,
		'orderby' => 'post__in',
	], $atts, 'bc_product_slider' );

	if ( empty( $atts['ids'] ) ) {
		return '';
	}

	$product_ids = array_map( 'absint', explode( ',', $atts['ids'] ) );
	$columns     = absint( $atts['columns'] );

	// Whitelist orderby to prevent injection via shortcode attributes.
	$allowed_orderby = [ 'post__in', 'date', 'title', 'popularity', 'rating', 'rand', 'menu_order' ];
	$orderby = in_array( $atts['orderby'], $allowed_orderby, true ) ? $atts['orderby'] : 'post__in';

	$args = [
		'post_type'      => 'product',
		'post__in'       => $product_ids,
		'orderby'        => $orderby,
		'posts_per_page' => absint( $atts['limit'] ),
		'post_status'    => 'publish',
	];

	$products = new WP_Query( $args );

	if ( ! $products->have_posts() ) {
		return '';
	}

	ob_start();
	$total = $products->post_count;
	// Render enough dots for the smallest responsive column count (2).
	// JS will show/hide dots based on actual viewport columns.
	$min_columns = 2;
	$max_pages   = max( 1, ceil( $total / $min_columns ) );
	$slider_id = 'bc-ps-' . wp_rand( 1000, 9999 );

	// Set WooCommerce loop columns for proper grid classes.
	wc_set_loop_prop( 'columns', $columns );

	// Build Blocksy product loop attributes so cards get full archive styling
	// (image hover swap, card type, quick view, etc.).
	$ul_attrs = '';
	if ( function_exists( 'blocksy_get_theme_mod' ) ) {
		$card_type = blocksy_get_theme_mod( 'shop_cards_type', 'type-1' );
		$ul_attrs .= ' data-products="' . esc_attr( $card_type ) . '"';

		$card_layout = blocksy_get_theme_mod( 'woo_card_layout', [
			[ 'id' => 'product_image', 'enabled' => true ],
		] );
		foreach ( $card_layout as $layout ) {
			if ( isset( $layout['id'] ) && 'product_image' === $layout['id'] ) {
				$hover = isset( $layout['product_image_hover'] ) ? $layout['product_image_hover'] : 'none';
				if ( function_exists( 'blocksy_akg' ) ) {
					$hover = blocksy_akg( 'product_image_hover', $layout, 'none' );
				}
				if ( 'none' !== $hover ) {
					$ul_attrs .= ' data-hover="' . esc_attr( $hover ) . '"';
				}
				break;
			}
		}
	}
	?>
	<div class="bc-product-slider" id="<?php echo esc_attr( $slider_id ); ?>"
		data-columns="<?php echo esc_attr( $columns ); ?>"
		data-total="<?php echo esc_attr( $total ); ?>">
		<div class="bc-product-slider__track woocommerce">
			<ul class="products columns-<?php echo esc_attr( $columns ); ?>"<?php echo $ul_attrs; ?>>
				<?php
				// Temporarily change product titles to H3 (items within section, not sections).
				add_filter( 'theme_mod_woo_card_layout', 'bc_slider_h3_product_titles' );

				while ( $products->have_posts() ) {
					$products->the_post();
					wc_get_template_part( 'content', 'product' );
				}
				wp_reset_postdata();
				remove_filter( 'theme_mod_woo_card_layout', 'bc_slider_h3_product_titles' );
				?>
			</ul>
		</div>

		<?php if ( $atts['arrows'] && $total > $columns ) : ?>
		<button class="bc-product-slider__arrow bc-product-slider__arrow--prev" aria-label="Previous">
			<svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
		</button>
		<button class="bc-product-slider__arrow bc-product-slider__arrow--next" aria-label="Next">
			<svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"></polyline></svg>
		</button>
		<?php endif; ?>

		<?php if ( $atts['dots'] && $max_pages > 1 ) : ?>
		<div class="bc-product-slider__dots">
			<?php for ( $i = 0; $i < $max_pages; $i++ ) : ?>
			<button class="bc-product-slider__dot<?php echo 0 === $i ? ' active' : ''; ?>"
				aria-label="Page <?php echo $i + 1; ?>"></button>
			<?php endfor; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
