<?php
/**
 * Product Full Description Customizer
 *
 * Adds Product Full Description element in Product Elements with design options.
 * Shows product description with show/less toggle when content exceeds max lines.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Full Description Customizer Class
 *
 * Handles Product Full Description element including:
 * - Product Full Description element for Product Elements
 * - Function options (Max Lines, Bottom Spacing)
 * - Design options (Font, Colors for Description and Show/Less button)
 */
class BlazeBlocksy_Product_Full_Description_Customizer {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register Product Full Description element in Product Elements
		add_filter( 'blocksy_woo_single_options_layers:defaults', array( $this, 'register_layer_defaults' ) );
		add_filter( 'blocksy_woo_single_options_layers:extra', array( $this, 'register_layer_options' ) );
		add_action( 'blocksy:woocommerce:product:custom:layer', array( $this, 'render_layer' ) );

		// Add Design Options to Design Tab
		add_filter( 'blocksy:options:single_product:elements:design_tab:end', array( $this, 'register_design_options' ) );

		// Generate dynamic CSS
		add_action( 'blocksy:global-dynamic-css:enqueue', array( $this, 'generate_dynamic_css' ), 10, 1 );

		// Enqueue customizer preview script for instant live preview
		add_action( 'customize_preview_init', array( $this, 'enqueue_customizer_preview_script' ) );

		// Enqueue frontend script for show/less toggle
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	}

	/**
	 * Enqueue customizer preview script for instant live preview
	 *
	 * @return void
	 */
	public function enqueue_customizer_preview_script() {
		$js_file = get_stylesheet_directory() . '/assets/js/customizer-preview-product-full-description.js';
		if ( file_exists( $js_file ) ) {
			wp_enqueue_script(
				'blaze-blocksy-product-full-description-customizer-preview',
				get_stylesheet_directory_uri() . '/assets/js/customizer-preview-product-full-description.js',
				array( 'jquery', 'customize-preview' ),
				filemtime( $js_file ),
				true
			);
		}
	}

	/**
	 * Enqueue frontend scripts for show/less toggle functionality
	 *
	 * Note: The JavaScript for show/less toggle has been moved to assets/js/single-product.js
	 * This method is kept for potential future use or can be removed.
	 *
	 * @return void
	 */
	public function enqueue_frontend_scripts() {
		// Script is now bundled in single-product.js which is enqueued in includes/scripts.php
		// No inline script needed
	}
	/**
	 * Add layer to default layout
	 *
	 * @param array $defaults Default layers.
	 * @return array
	 */
	public function register_layer_defaults( $defaults ) {
		$defaults[] = array(
			'id' => 'product_full_description',
			'enabled' => false,
		);
		return $defaults;
	}

	/**
	 * Register layer options for Product Full Description element
	 *
	 * @param array $options Existing options.
	 * @return array
	 */
	public function register_layer_options( $options ) {
		$options['product_full_description'] = array(
			'label' => __( 'Product Full Description', 'blaze-blocksy' ),
			'options' => array(
				// Max Lines
				'product_full_description_max_lines' => array(
					'label' => __( 'Maximum Lines', 'blaze-blocksy' ),
					'type' => 'ct-number',
					'min' => 1,
					'max' => 20,
					'value' => 4,
					'design' => 'inline',
					'sync' => array( 'id' => 'woo_single_layout_skip' ),
				),
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
	 * Register design options in Design Tab
	 *
	 * @param array $options Existing options.
	 * @return array
	 */
	public function register_design_options( $options ) {
		$options[blocksy_rand_md5()] = array(
			'type' => 'ct-condition',
			'condition' => array( 'woo_single_layout:array-ids:product_full_description:enabled' => '!no' ),
			'computed_fields' => array( 'woo_single_layout' ),
			'options' => array(

				// Section Divider
				blocksy_rand_md5() => array(
					'type' => 'ct-divider',
				),

				// Section Title
				blocksy_rand_md5() => array(
					'type' => 'ct-title',
					'label' => __( 'Product Full Description', 'blaze-blocksy' ),
				),

				// Description Typography
				'productFullDescriptionFont' => array(
					'type' => 'ct-typography',
					'label' => __( 'Description Font', 'blaze-blocksy' ),
					'value' => blocksy_typography_default_values(
						array(
							'size' => '14px',
							'variation' => 'n4',
							'line-height' => '1.65',
						)
					),
					'setting' => array( 'transport' => 'postMessage' ),
				),

				// Description Color
				'productFullDescriptionColor' => array(
					'label' => __( 'Description Color', 'blaze-blocksy' ),
					'type' => 'ct-color-picker',
					'design' => 'inline',
					'setting' => array( 'transport' => 'postMessage' ),
					'value' => array(
						'default' => array(
							'color' => 'var(--theme-text-color, #4b5563)',
						),
					),
					'pickers' => array(
						array(
							'title' => __( 'Color', 'blaze-blocksy' ),
							'id' => 'default',
						),
					),
				),

				// Show/Less Typography
				'productFullDescriptionToggleFont' => array(
					'type' => 'ct-typography',
					'label' => __( 'Show/Less Font', 'blaze-blocksy' ),
					'value' => blocksy_typography_default_values(
						array(
							'size' => '14px',
							'variation' => 'n6',
						)
					),
					'setting' => array( 'transport' => 'postMessage' ),
				),

				// Show/Less Color
				'productFullDescriptionToggleColor' => array(
					'label' => __( 'Show/Less Color', 'blaze-blocksy' ),
					'type' => 'ct-color-picker',
					'design' => 'inline',
					'setting' => array( 'transport' => 'postMessage' ),
					'value' => array(
						'default' => array(
							'color' => 'var(--theme-palette-color-1, #3b82f6)',
						),
					),
					'pickers' => array(
						array(
							'title' => __( 'Color', 'blaze-blocksy' ),
							'id' => 'default',
						),
					),
				),
			),
		);

		return $options;
	}

	/**
	 * Render the Product Full Description layer
	 *
	 * @param array $layer Layer configuration.
	 * @return void
	 */
	public function render_layer( $layer ) {
		if ( 'product_full_description' !== $layer['id'] ) {
			return;
		}

		global $product;

		if ( ! $product ) {
			return;
		}

		$description = $product->get_description();

		if ( empty( $description ) ) {
			return;
		}

		$max_lines = isset( $layer['product_full_description_max_lines'] ) ? intval( $layer['product_full_description_max_lines'] ) : 4;

		echo '<div class="ct-product-full-description-element" data-element="product_full_description" data-max-lines="' . esc_attr( $max_lines ) . '">';
		echo '<div class="ct-full-description-content">' . wp_kses_post( wpautop( $description ) ) . '</div>';
		echo '<button type="button" class="ct-full-description-toggle">';
		echo '<span class="show-more-text">' . esc_html__( 'Show More', 'blaze-blocksy' ) . '</span>';
		echo '<span class="show-less-text">' . esc_html__( 'Show Less', 'blaze-blocksy' ) . '</span>';
		echo '</button>';
		echo '</div>';
	}

	/**
	 * Generate dynamic CSS for Product Full Description
	 *
	 * @param array $args CSS generation arguments from Blocksy.
	 * @return void
	 */
	public function generate_dynamic_css( $args ) {
		$css = $args['css'];
		$tablet_css = $args['tablet_css'];
		$mobile_css = $args['mobile_css'];

		// Check if layer is enabled and get layer config
		$layout = blocksy_get_theme_mod( 'woo_single_layout', array() );
		$layer_config = null;

		foreach ( $layout as $layer ) {
			if ( isset( $layer['id'] ) && 'product_full_description' === $layer['id'] && ! empty( $layer['enabled'] ) ) {
				$layer_config = $layer;
				break;
			}
		}

		if ( ! $layer_config ) {
			return;
		}

		// Output spacing CSS
		$spacing = blocksy_akg( 'spacing', $layer_config, 20 );
		blocksy_output_responsive(
			array(
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'selector' => '.entry-summary-items > .ct-product-full-description-element',
				'variableName' => 'product-element-spacing',
				'value' => $spacing,
				'unit' => 'px',
			)
		);

		// Description Typography
		blocksy_output_font_css(
			array(
				'font_value' => blocksy_get_theme_mod(
					'productFullDescriptionFont',
					blocksy_typography_default_values(
						array(
							'size' => '14px',
							'variation' => 'n4',
							'line-height' => '1.65',
						)
					)
				),
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'selector' => '.ct-product-full-description-element .ct-full-description-content',
			)
		);

		// Description Color
		blocksy_output_colors(
			array(
				'value' => blocksy_get_theme_mod( 'productFullDescriptionColor' ),
				'default' => array(
					'default' => array( 'color' => 'var(--theme-text-color, #4b5563)' ),
				),
				'css' => $css,
				'variables' => array(
					'default' => array(
						'selector' => '.ct-product-full-description-element .ct-full-description-content',
						'variable' => 'description-color',
					),
				),
			)
		);

		// Toggle Typography
		blocksy_output_font_css(
			array(
				'font_value' => blocksy_get_theme_mod(
					'productFullDescriptionToggleFont',
					blocksy_typography_default_values(
						array(
							'size' => '14px',
							'variation' => 'n6',
						)
					)
				),
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'selector' => '.ct-product-full-description-element .ct-full-description-toggle',
			)
		);

		// Toggle Color
		blocksy_output_colors(
			array(
				'value' => blocksy_get_theme_mod( 'productFullDescriptionToggleColor' ),
				'default' => array(
					'default' => array( 'color' => 'var(--theme-palette-color-1, #3b82f6)' ),
				),
				'css' => $css,
				'variables' => array(
					'default' => array(
						'selector' => '.ct-product-full-description-element .ct-full-description-toggle',
						'variable' => 'toggle-color',
					),
				),
			)
		);
	}
}

// Initialize
new BlazeBlocksy_Product_Full_Description_Customizer();
