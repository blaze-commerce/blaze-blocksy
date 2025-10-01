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

	/**
	 * Configuration variables for Mix and Match products display
	 */
	function get_mnm_config() {
		return array(
			'initial_products_count' => 4,  // Number of products to show initially
			'load_more_count' => 4,         // Number of products to load per "Load More" click
			'grid_columns' => 4             // Number of grid columns for layout
		);
	}

	add_action( 'wc_mnm_before_container_status', function ( $product ) {
		$child_items = $product->get_child_items();
		$mnm_config = get_mnm_config();
		$initial_count = $mnm_config['initial_products_count'];
		$load_more_count = $mnm_config['load_more_count'];
		$grid_columns = $mnm_config['grid_columns'];

		// create load more button if there are more products than initial count
		if ( count( $child_items ) > $initial_count ) {
			?>
			<button class="ct-mnm-load-more" aria-label="Load more products"
				data-initial="<?php echo esc_attr( $initial_count ); ?>"
				data-load-more="<?php echo esc_attr( $load_more_count ); ?>"
				data-grid-columns="<?php echo esc_attr( $grid_columns ); ?>">
				Load More
			</button>
			<?php
		}
	} );


} );