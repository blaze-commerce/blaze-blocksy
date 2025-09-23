<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Judge.me reviews tab only if the Judge.me plugin is active.
 * This prevents duplicate reviews tabs when Judge.me is not installed.
 */
add_filter( 'woocommerce_product_tabs', 'blaze_blocksy_filter_product_tabs' );

/**
 * Filter WooCommerce product tabs to conditionally add Judge.me reviews tab.
 *
 * @param array $tabs Existing product tabs.
 * @return array Modified tabs array.
 * @since 1.0.0
 */
function blaze_blocksy_filter_product_tabs( $tabs ) {
	// Only add the Judge.me reviews tab if the plugin is active
	if ( ! blaze_blocksy_is_plugin_active( 'judgeme-product-reviews/judgeme.php' ) &&
		 ! function_exists( 'judgeme_widget' ) &&
		 ! shortcode_exists( 'jgm-review-widget' ) ) {
		return $tabs;
	}

	$tabs['judgeme_tab'] = array(
		'title' => __( 'Reviews', 'blaze-blocksy' ),
		'priority' => 50,
		'callback' => 'blaze_blocksy_render_judgeme_tab',
	);
	return $tabs;
}

/**
 * Render Judge.me reviews tab content with enhanced fallback handling.
 *
 * @since 1.0.0
 */
function blaze_blocksy_render_judgeme_tab() {
	global $product;

	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	// Enhanced availability check with multiple fallbacks
	$judgeme_available = (
		shortcode_exists( 'jgm-review-widget' ) ||
		function_exists( 'judgeme_widget' ) ||
		class_exists( 'JudgeMe' )
	);

	if ( ! $judgeme_available ) {
		// Graceful fallback to WooCommerce native reviews
		if ( comments_open( $product->get_id() ) ) {
			comments_template();
		} else {
			echo '<div class="judgeme-fallback">';
			echo '<p>' . esc_html__( 'Reviews are currently unavailable. Please check back later.', 'blaze-blocksy' ) . '</p>';
			echo '</div>';
		}
		return;
	}

	// Validate product ID before shortcode execution
	$product_id = absint( $product->get_id() );
	if ( $product_id <= 0 ) {
		return;
	}
	?>
	<h2><?php esc_html_e( 'Customer Reviews', 'blaze-blocksy' ); ?></h2>
	<div>
		<?php echo do_shortcode( '[jgm-review-widget id="' . $product_id . '"]' ); ?>
	</div>
	<?php
}