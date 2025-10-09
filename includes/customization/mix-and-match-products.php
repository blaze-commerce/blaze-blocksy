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

		// Add dynamic CSS for grid columns and initial products count
		add_action( 'wp_head', 'output_mnm_dynamic_css' );
	} );

	/**
	 * Configuration variables for Mix and Match products display
	 * Now retrieves values from WordPress Customizer with fallback defaults
	 */
	function get_mnm_config() {
		// Get number of columns from existing Mix and Match customizer setting
		$default_columns = get_option( 'wc_mnm_number_columns', 4 );
		$grid_columns = (int) apply_filters( 'wc_mnm_grid_layout_columns', $default_columns );

		return [
			'initial_products_count' => (int) get_option( 'wc_mnm_initial_products_count', 4 ),  // Number of products to show initially
			'load_more_count' => (int) get_option( 'wc_mnm_load_more_count', 4 ),               // Number of products to load per "Load More" click
			'grid_columns' => $grid_columns                                                      // Number of grid columns for layout
		];
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

	/**
	 * Output dynamic CSS for Mix and Match products based on customizer settings
	 */
	function output_mnm_dynamic_css() {
		if ( ! is_product() ) {
			return;
		}

		global $product;
		if ( ! $product || ! $product->is_type( 'mix-and-match' ) ) {
			return;
		}

		$mnm_config = get_mnm_config();
		$grid_columns = $mnm_config['grid_columns'];
		$initial_count = $mnm_config['initial_products_count'];

		$css = "
		<style id='mnm-dynamic-css'>
			.mnm_child_products.products {
				grid-template-columns: repeat({$grid_columns}, 1fr) !important;
			}

			/* Hide products after the initial count */
			.mnm_child_products.products li.product:nth-child(n + " . ( $initial_count + 1 ) . "):not(.mnm-show) {
				display: none !important;
			}

			/* Responsive grid for smaller screens */
			@media (max-width: 768px) {
				.mnm_child_products.products {
					grid-template-columns: repeat(" . min( 2, $grid_columns ) . ", 1fr) !important;
				}
			}

			@media (max-width: 480px) {
				.mnm_child_products.products {
					grid-template-columns: 1fr !important;
				}
			}
		</style>";

		echo $css;
	}

	/**
	 * Add customizer fields for Mix and Match Products configuration
	 */
	add_action( 'customize_register', 'add_mnm_customizer_fields' );

	function add_mnm_customizer_fields( $wp_customize ) {
		// Ensure the Mix and Match section exists, if not create it
		if ( ! $wp_customize->get_section( 'wc_mnm' ) ) {
			// Add WooCommerce panel if it doesn't exist
			if ( ! $wp_customize->get_panel( 'woocommerce' ) ) {
				$wp_customize->add_panel(
					'woocommerce',
					[
						'title' => __( 'WooCommerce', 'blocksy-child' ),
						'description' => __( 'WooCommerce settings and customization options.', 'blocksy-child' ),
						'priority' => 200,
						'capability' => 'manage_woocommerce',
					]
				);
			}

			// Add Mix and Match section
			$wp_customize->add_section(
				'wc_mnm',
				[
					'title' => __( 'Mix and Match Products', 'blocksy-child' ),
					'description' => __( 'Configure display settings for Mix and Match products.', 'blocksy-child' ),
					'panel' => 'woocommerce',
					'priority' => 50,
					'capability' => 'manage_woocommerce',
				]
			);
		}

		// Add Initial Products Count setting
		$wp_customize->add_setting(
			'wc_mnm_initial_products_count',
			[
				'default' => 4,
				'type' => 'option',
				'capability' => 'manage_woocommerce',
				'transport' => 'refresh',
				'sanitize_callback' => 'absint',
			]
		);

		$wp_customize->add_control(
			'wc_mnm_initial_products_count',
			[
				'label' => __( 'Initial Products Count', 'blocksy-child' ),
				'description' => __( 'Number of products to show initially before "Load More" button.', 'blocksy-child' ),
				'section' => 'wc_mnm',
				'type' => 'number',
				'input_attrs' => [
					'min' => 1,
					'max' => 50,
					'step' => 1,
				],
			]
		);

		// Add Load More Count setting
		$wp_customize->add_setting(
			'wc_mnm_load_more_count',
			[
				'default' => 4,
				'type' => 'option',
				'capability' => 'manage_woocommerce',
				'transport' => 'refresh',
				'sanitize_callback' => 'absint',
			]
		);

		$wp_customize->add_control(
			'wc_mnm_load_more_count',
			[
				'label' => __( 'Load More Count', 'blocksy-child' ),
				'description' => __( 'Number of products to load when "Load More" button is clicked.', 'blocksy-child' ),
				'section' => 'wc_mnm',
				'type' => 'number',
				'input_attrs' => [
					'min' => 1,
					'max' => 20,
					'step' => 1,
				],
			]
		);
	}


} );