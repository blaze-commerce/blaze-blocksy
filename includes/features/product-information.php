<?php
/**
 * Product Information Customizer
 *
 * Adds Product Information element in Product Elements with design options.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Information Customizer Class
 *
 * Handles Product Information element including:
 * - Product Information element for Product Elements
 * - Design options (Border, Padding, Typography, Colors)
 *
 * NOTE: Live preview requires JavaScript sync configuration.
 * Uses postMessage transport for instant live preview.
 */
class BlazeBlocksy_Product_Information_Customizer {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register Product Information element in Product Elements
		add_filter( 'blocksy_woo_single_options_layers:defaults', array( $this, 'register_layer_defaults' ) );
		add_filter( 'blocksy_woo_single_options_layers:extra', array( $this, 'register_layer_options' ) );
		add_action( 'blocksy:woocommerce:product:custom:layer', array( $this, 'render_layer' ) );

		// Add Design Options to Design Tab
		add_filter( 'blocksy:options:single_product:elements:design_tab:end', array( $this, 'register_design_options' ) );

		// Generate dynamic CSS
		add_action( 'blocksy:global-dynamic-css:enqueue', array( $this, 'generate_dynamic_css' ), 10, 1 );

		// Enqueue scripts
		add_action( 'wp_footer', array( $this, 'add_frontend_script' ), 100 );

		// Enqueue customizer preview script for instant live preview
		add_action( 'customize_preview_init', array( $this, 'enqueue_customizer_preview_script' ) );
	}

	/**
	 * Enqueue customizer preview script for instant live preview
	 *
	 * @return void
	 */
	public function enqueue_customizer_preview_script() {
		wp_enqueue_script(
			'blaze-blocksy-product-information-customizer-preview',
			get_stylesheet_directory_uri() . '/assets/js/customizer-preview-product-information.js',
			array( 'jquery', 'customize-preview' ),
			filemtime( get_stylesheet_directory() . '/assets/js/customizer-preview-product-information.js' ),
			true
		);
	}

	/**
	 * Add layer to default layout
	 *
	 * @param array $defaults Default layers.
	 * @return array
	 */
	public function register_layer_defaults( $defaults ) {
		$defaults[] = array(
			'id' => 'product_information',
			'enabled' => false,
		);
		return $defaults;
	}

	/**
	 * Register layer options for Product Information element
	 * Only contains non-design options (spacing)
	 *
	 * @param array $options Existing options.
	 * @return array
	 */
	public function register_layer_options( $options ) {
		$options['product_information'] = array(
			'label' => __( 'Product Information', 'blaze-blocksy' ),
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
	 * Register design options in Design Tab
	 *
	 * @param array $options Existing options.
	 * @return array
	 */
	public function register_design_options( $options ) {
		$options[blocksy_rand_md5()] = array(
			'type' => 'ct-condition',
			'condition' => array( 'woo_single_layout:array-ids:product_information:enabled' => '!no' ),
			'computed_fields' => array( 'woo_single_layout' ),
			'options' => array(

				// Section Divider
				blocksy_rand_md5() => array(
					'type' => 'ct-divider',
				),

				// Section Title
				blocksy_rand_md5() => array(
					'type' => 'ct-title',
					'label' => __( 'Product Information', 'blaze-blocksy' ),
				),

				// Typography
				'productInformationFont' => array(
					'type' => 'ct-typography',
					'label' => __( 'Font', 'blaze-blocksy' ),
					'value' => blocksy_typography_default_values(
						array(
							'size' => '14px',
							'variation' => 'n4',
						)
					),
					'setting' => array( 'transport' => 'postMessage' ),
				),

				// Text Color
				'productInformationTextColor' => array(
					'label' => __( 'Text Color', 'blaze-blocksy' ),
					'type' => 'ct-color-picker',
					'design' => 'inline',
					'setting' => array( 'transport' => 'postMessage' ),
					'value' => array(
						'default' => array(
							'color' => '#333333',
						),
					),
					'pickers' => array(
						array(
							'title' => __( 'Color', 'blaze-blocksy' ),
							'id' => 'default',
						),
					),
				),

				// Border Width
				'productInformationBorderWidth' => array(
					'label' => __( 'Border Width', 'blaze-blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 10,
					'value' => 1,
					'setting' => array( 'transport' => 'postMessage' ),
				),

				// Border Style
				'productInformationBorderStyle' => array(
					'label' => __( 'Border Style', 'blaze-blocksy' ),
					'type' => 'ct-select',
					'value' => 'solid',
					'setting' => array( 'transport' => 'postMessage' ),
					'choices' => blocksy_ordered_keys(
						array(
							'solid' => __( 'Solid', 'blaze-blocksy' ),
							'dashed' => __( 'Dashed', 'blaze-blocksy' ),
							'dotted' => __( 'Dotted', 'blaze-blocksy' ),
							'double' => __( 'Double', 'blaze-blocksy' ),
							'none' => __( 'None', 'blaze-blocksy' ),
						)
					),
				),

				// Border Color
				'productInformationBorderColor' => array(
					'label' => __( 'Border Color', 'blaze-blocksy' ),
					'type' => 'ct-color-picker',
					'design' => 'inline',
					'setting' => array( 'transport' => 'postMessage' ),
					'value' => array(
						'default' => array(
							'color' => '#e9ecef',
						),
					),
					'pickers' => array(
						array(
							'title' => __( 'Color', 'blaze-blocksy' ),
							'id' => 'default',
						),
					),
				),

				// Separator Width
				'productInformationSeparatorWidth' => array(
					'label' => __( 'Separator Width', 'blaze-blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 10,
					'value' => 1,
					'setting' => array( 'transport' => 'postMessage' ),
				),

				// Separator Style
				'productInformationSeparatorStyle' => array(
					'label' => __( 'Separator Style', 'blaze-blocksy' ),
					'type' => 'ct-select',
					'value' => 'solid',
					'setting' => array( 'transport' => 'postMessage' ),
					'choices' => blocksy_ordered_keys(
						array(
							'solid' => __( 'Solid', 'blaze-blocksy' ),
							'dashed' => __( 'Dashed', 'blaze-blocksy' ),
							'dotted' => __( 'Dotted', 'blaze-blocksy' ),
							'double' => __( 'Double', 'blaze-blocksy' ),
							'none' => __( 'None', 'blaze-blocksy' ),
						)
					),
				),

				// Separator Color
				'productInformationSeparatorColor' => array(
					'label' => __( 'Separator Color', 'blaze-blocksy' ),
					'type' => 'ct-color-picker',
					'design' => 'inline',
					'setting' => array( 'transport' => 'postMessage' ),
					'value' => array(
						'default' => array(
							'color' => '#e9ecef',
						),
					),
					'pickers' => array(
						array(
							'title' => __( 'Color', 'blaze-blocksy' ),
							'id' => 'default',
						),
					),
				),

				// Vertical Padding
				'productInformationVerticalPadding' => array(
					'label' => __( 'Vertical Padding', 'blaze-blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 20,
					'setting' => array( 'transport' => 'postMessage' ),
				),

				// Item Horizontal Padding
				'productInformationItemHorizontalPadding' => array(
					'label' => __( 'Item Horizontal Padding', 'blaze-blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 20,
					'setting' => array( 'transport' => 'postMessage' ),
				),

				// Gap Inside Item
				'productInformationGapInside' => array(
					'label' => __( 'Gap Inside Item', 'blaze-blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 20,
					'setting' => array( 'transport' => 'postMessage' ),
				),

				// Justify Content
				'productInformationJustifyContent' => array(
					'label' => __( 'Justify Content', 'blaze-blocksy' ),
					'type' => 'ct-select',
					'value' => 'center',
					'setting' => array( 'transport' => 'postMessage' ),
					'choices' => blocksy_ordered_keys(
						array(
							'flex-start' => __( 'Left', 'blaze-blocksy' ),
							'center' => __( 'Center', 'blaze-blocksy' ),
							'flex-end' => __( 'Right', 'blaze-blocksy' ),
							'space-between' => __( 'Justify', 'blaze-blocksy' ),
							'space-around' => __( 'Space Around', 'blaze-blocksy' ),
							'space-evenly' => __( 'Space Evenly', 'blaze-blocksy' ),
						)
					),
				),

				// Text Underline
				'productInformationTextUnderline' => array(
					'label' => __( 'Text Underline', 'blaze-blocksy' ),
					'type' => 'ct-switch',
					'value' => 'no',
					'setting' => array( 'transport' => 'postMessage' ),
				),
			),
		);

		return $options;
	}

	/**
	 * Render the Product Information layer
	 *
	 * @param array $layer Layer configuration.
	 * @return void
	 */
	public function render_layer( $layer ) {
		if ( 'product_information' !== $layer['id'] ) {
			return;
		}

		global $product;

		if ( ! $product ) {
			return;
		}

		echo '<div class="ct-product-information" data-element="product_information">';

		include BLAZE_BLOCKSY_PATH . '/partials/product/information.php';

		echo '</div>';
	}

	/**
	 * Generate dynamic CSS for Product Information
	 *
	 * Uses blocksy_output_colors() and blocksy_output_responsive() for proper CSS output.
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
			if ( isset( $layer['id'] ) && 'product_information' === $layer['id'] && ! empty( $layer['enabled'] ) ) {
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
				'selector' => '.entry-summary-items > .ct-product-information',
				'variableName' => 'product-element-spacing',
				'value' => $spacing,
				'unit' => 'px',
			)
		);

		// Typography
		blocksy_output_font_css(
			array(
				'font_value' => blocksy_get_theme_mod(
					'productInformationFont',
					blocksy_typography_default_values(
						array(
							'size' => '14px',
							'variation' => 'n4',
						)
					)
				),
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'selector' => '.ct-product-information',
			)
		);

		// Text Color
		blocksy_output_colors(
			array(
				'value' => blocksy_get_theme_mod( 'productInformationTextColor' ),
				'default' => array(
					'default' => array( 'color' => '#333333' ),
				),
				'css' => $css,
				'variables' => array(
					'default' => array(
						'selector' => '.ct-product-information',
						'variable' => 'product-information-text-color',
					),
				),
			)
		);

		// Border Width
		$border_width = blocksy_get_theme_mod( 'productInformationBorderWidth', 1 );
		$css->put(
			'.ct-product-information',
			'--product-information-border-width: ' . $border_width . 'px'
		);

		// Border Style
		$border_style = blocksy_get_theme_mod( 'productInformationBorderStyle', 'solid' );
		$css->put(
			'.ct-product-information',
			'--product-information-border-style: ' . $border_style
		);

		// Border Color
		blocksy_output_colors(
			array(
				'value' => blocksy_get_theme_mod( 'productInformationBorderColor' ),
				'default' => array(
					'default' => array( 'color' => '#e9ecef' ),
				),
				'css' => $css,
				'variables' => array(
					'default' => array(
						'selector' => '.ct-product-information',
						'variable' => 'product-information-border-color',
					),
				),
			)
		);

		// Separator Width
		$separator_width = blocksy_get_theme_mod( 'productInformationSeparatorWidth', 1 );
		$css->put(
			'.ct-product-information',
			'--product-information-separator-width: ' . $separator_width . 'px'
		);

		// Separator Style
		$separator_style = blocksy_get_theme_mod( 'productInformationSeparatorStyle', 'solid' );
		$css->put(
			'.ct-product-information',
			'--product-information-separator-style: ' . $separator_style
		);

		// Separator Color
		blocksy_output_colors(
			array(
				'value' => blocksy_get_theme_mod( 'productInformationSeparatorColor' ),
				'default' => array(
					'default' => array( 'color' => '#e9ecef' ),
				),
				'css' => $css,
				'variables' => array(
					'default' => array(
						'selector' => '.ct-product-information',
						'variable' => 'product-information-separator-color',
					),
				),
			)
		);

		// Vertical Padding
		$vertical_padding = blocksy_get_theme_mod( 'productInformationVerticalPadding', 20 );
		$css->put(
			'.ct-product-information',
			'--product-information-padding: ' . $vertical_padding . 'px'
		);

		// Item Horizontal Padding
		$item_horizontal_padding = blocksy_get_theme_mod( 'productInformationItemHorizontalPadding', 20 );
		$css->put(
			'.ct-product-information',
			'--product-information-item-horizontal-padding: ' . $item_horizontal_padding . 'px'
		);

		// Gap Inside Item
		$gap_inside = blocksy_get_theme_mod( 'productInformationGapInside', 20 );
		$css->put(
			'.ct-product-information',
			'--product-information-gap-inside: ' . $gap_inside . 'px'
		);

		// Justify Content
		$justify_content = blocksy_get_theme_mod( 'productInformationJustifyContent', 'center' );
		$css->put(
			'.ct-product-information',
			'--product-information-justify-content: ' . $justify_content
		);

		// Text Underline
		$text_underline = blocksy_get_theme_mod( 'productInformationTextUnderline', 'no' );
		$underline_value = ( 'yes' === $text_underline ) ? 'underline' : 'none';
		$css->put(
			'.ct-product-information',
			'--product-information-text-underline: ' . $underline_value
		);
	}

	/**
	 * Enqueue frontend scripts
	 *
	 * @return void
	 */
	public function add_frontend_script() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		// Check if layer is enabled
		$layout = blocksy_get_theme_mod( 'woo_single_layout', array() );
		$layer_enabled = false;

		foreach ( $layout as $layer ) {
			if ( isset( $layer['id'] ) && 'product_information' === $layer['id'] && ! empty( $layer['enabled'] ) ) {
				$layer_enabled = true;
				break;
			}
		}

		if ( ! $layer_enabled ) {
			return;
		}

		// Script is now bundled in single-product.js which is enqueued in includes/scripts.php
		// No inline script needed - initProductInformationOffcanvas() is called on document ready
	}
}

// Initialize
new BlazeBlocksy_Product_Information_Customizer();
