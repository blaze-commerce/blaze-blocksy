<?php
/**
 * Product Tabs Customizer
 *
 * Adds customization options for product tabs on single product pages.
 * - Disable Product Tabs toggle in Single Product customizer settings
 * - Product Tabs element in Product Elements
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Tabs Customizer Class
 *
 * Handles all product tabs customization including:
 * - Toggle to disable default product tabs
 * - Product Tabs element for Product Elements
 */
class BlazeBlocksy_Product_Tabs_Customizer {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register Product Tabs element in Product Elements
		add_filter( 'blocksy_woo_single_options_layers:defaults', array( $this, 'register_layer_defaults' ) );
		add_filter( 'blocksy_woo_single_options_layers:extra', array( $this, 'register_layer_options' ) );
		add_action( 'blocksy:woocommerce:product:custom:layer', array( $this, 'render_layer' ) );

		// Add disable option to Single Product customizer (WooCommerce General section)
		add_filter( 'blocksy_customizer_options:woocommerce:general:end', array( $this, 'add_disable_tabs_option' ), 60 );

		// Handle disabling default product tabs using remove_action
		add_action( 'wp', array( $this, 'maybe_disable_default_tabs' ) );
	}

	/**
	 * Add layer to default layout
	 *
	 * @param array $defaults Default layers.
	 * @return array
	 */
	public function register_layer_defaults( $defaults ) {
		$defaults[] = array(
			'id' => 'product_tabs_element',
			'enabled' => false,
		);
		return $defaults;
	}

	/**
	 * Register layer options for Product Tabs element
	 *
	 * @param array $options Existing options.
	 * @return array
	 */
	public function register_layer_options( $options ) {
		$options['product_tabs_element'] = array(
			'label' => __( 'Product Tabs', 'blaze-blocksy' ),
			'options' => array(
				// Bottom Spacing
				'spacing' => array(
					'label' => __( 'Bottom Spacing', 'blaze-blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 20,
					'responsive' => true,
					'sync' => array( 'id' => 'woo_single_layout_skip' ),
				),
			),
		);

		return $options;
	}

	/**
	 * Add disable tabs option to Single Product customizer
	 *
	 * @param array $options Existing options.
	 * @return array
	 */
	public function add_disable_tabs_option( $options ) {
		$tabs_options = array(
			'blaze_product_tabs_divider' => array(
				'type' => 'ct-divider',
			),

			'blaze_product_tabs_title' => array(
				'type' => 'ct-title',
				'label' => __( 'Product Tabs', 'blaze-blocksy' ),
			),

			'blaze_disable_product_tabs' => array(
				'label' => __( 'Disable Product Tabs', 'blaze-blocksy' ),
				'type' => 'ct-switch',
				'value' => 'no',
				'desc' => __( 'When enabled, default WooCommerce product tabs (Description, Reviews, etc.) will be hidden. Use the Product Tabs element in Product Elements for custom placement.', 'blaze-blocksy' ),
				'sync' => blocksy_sync_whole_page(
					array(
						'prefix' => 'product',
						'loader_selector' => '.type-product',
					)
				),
			),
		);

		return array_merge( $options, $tabs_options );
	}

	/**
	 * Maybe disable default product tabs
	 * Uses remove_action to remove the default WooCommerce product tabs
	 *
	 * @return void
	 */
	public function maybe_disable_default_tabs() {
		$disable_tabs = get_theme_mod( 'blaze_disable_product_tabs', 'no' );

		if ( 'yes' === $disable_tabs ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
		}
	}

	/**
	 * Render the Product Tabs layer
	 *
	 * @param array $layer Layer configuration.
	 * @return void
	 */
	public function render_layer( $layer ) {
		if ( 'product_tabs_element' !== $layer['id'] ) {
			return;
		}

		global $product;

		if ( ! $product ) {
			return;
		}

		echo '<div class="ct-product-tabs-element" data-element="product_tabs_element">';

		// Output WooCommerce product tabs
		woocommerce_output_product_data_tabs();

		echo '</div>';
	}
}

// Initialize
new BlazeBlocksy_Product_Tabs_Customizer();
