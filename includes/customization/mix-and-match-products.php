<?php
/**
 * Check if Mix and Match plugin is active
 */
add_action( 'init', function () {

	if ( ! defined( 'WC_MNM_PLUGIN_FILE' ) )
		return;

	/**
	 * Disable center alignment of quantity buttons
	 */
	add_filter( 'wc_mnm_center_align_quantity', function () {
		return false;
	} );

	/**
	 * Enqueue custom styles for Mix and Match products
	 */
	add_action( 'wp_enqueue_scripts', function () {
		if ( ! is_product() )
			return;

		global $product;

		$product_id = null;

		if ( is_a( $product, 'WC_Product' ) ) {
			$product_id = $product->get_id();
		} elseif ( is_string( $product ) ) {
			// get product by slug
			$post = get_page_by_path( $product, OBJECT, 'product' );
			$product = wc_get_product( $post->ID );

			$product_id = $post->ID;

		}

		if ( ! $product->is_type( 'mix-and-match' ) )
			return;

		$theme_version = wp_get_theme()->get( 'Version' );

		wp_enqueue_style( 'blaze-blocksy-mnm', BLAZE_BLOCKSY_URL . '/assets/css/mix-and-match-products.css', array(), $theme_version );
		wp_enqueue_script( 'blaze-blocksy-mnm', BLAZE_BLOCKSY_URL . '/assets/js/mix-and-match-products.js', array( 'jquery' ), $theme_version, true );
	} );

	add_action( 'wc_mnm_before_container_status', function ($product) {
		$child_items = $product->get_child_items();
		$default_columns = get_option( 'wc_mnm_number_columns', 3 );

		// create load more button if there are more products than columns
		if ( count( $child_items ) > $default_columns ) {
			?>
			<button class="ct-mnm-load-more" aria-label="Load more products"
				data-columns="<?php echo esc_attr( $default_columns ); ?>">
				Load More
			</button>
			<?php
		}
	} );


} );