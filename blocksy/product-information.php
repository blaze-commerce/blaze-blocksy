<?php

/**
 * @category Blocksy
 * @package  Blocksy
 * @author   Your Name <yourname@example.com>
 * @license  GPLv2 or later
 * @link     https://www.example.com
 */

// Pastikan file ini tidak diakses langsung
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to add custom product dimensions element to Blocksy theme
 *
 * @category Blocksy
 * @package  Blocksy
 * @author   Your Name <yourname@example.com>
 * @license  GPLv2 or later
 * @link     https://www.example.com
 */
class CustomProductInformationElement {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Hook untuk menambahkan element ke single product page
		add_filter( 'blocksy_woo_single_options_layers:defaults', array( $this, 'addToDefaultLayout' ) );
		add_filter( 'blocksy_woo_single_options_layers:extra', array( $this, 'addLayerOptions' ) );
		add_action( 'blocksy:woocommerce:product:custom:layer', array( $this, 'renderLayer' ) );
		add_filter( 'blocksy:global-dynamic-css:enqueue:singular', array( $this, 'renderDynamicCSS' ) );
		add_filter( 'blocksy:options:single_product:elements:design_tab:end', array( $this, 'addDesignOptions' ) );

		// Enqueue styles jika diperlukan
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueStyles' ), 100 );
		add_action( 'wp_footer', array( $this, 'addScript' ), 100 );

		// Enqueue customizer scripts untuk live preview
		add_action( 'customize_preview_init', array( $this, 'enqueueCustomizerScripts' ) );

		// Hook tambahan untuk memastikan styles ter-load di customizer
		add_action( 'wp_head', array( $this, 'enqueueCustomizerStyles' ), 999 );
	}

	/**
	 * Add element to default layout
	 *
	 * @param array $opt Default layout options
	 *
	 * @return array
	 */
	public function addToDefaultLayout( $opt ) {
		return array_merge(
			$opt,
			array(
				array(
					'id' => 'product_information',
					'enabled' => false,
				),
			)
		);
	}

	/**
	 * Add element options to customizer
	 *
	 * @param array $opt Existing options
	 *
	 * @return array
	 */
	public function addLayerOptions( $opt ) {
		return array_merge(
			$opt,
			array(
				'product_information' => array(
					'label' => __( 'Product Information', 'textdomain' ),
					'options' => array(
						'ct-information-add-separator' => array(
							'label' => __( 'Add Separator', 'textdomain' ),
							'type' => 'ct-switch',
							'value' => 'no',
							'sync' => array(
								'id' => 'woo_single_layout_skip'
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Add design options to customizer
	 *
	 * @param array $options Existing options
	 *
	 * @return array
	 */
	public function addDesignOptions( $options ) {
		$design_options = array(
			'product_information_design_section' => array(
				'type' => 'ct-condition',
				'condition' => array( 'woo_single_layout:array-ids:product_information:enabled' => '!no' ),
				'computed_fields' => array( 'woo_single_layout' ),
				'options' => array(
					'product_information_design_title' => array(
						'type' => 'ct-title',
						'variation' => 'small-divider',
						'label' => __( 'Product Information', 'textdomain' ),
					),
					'ct-information-border_width' => array(
						'label' => __( 'Border Width', 'textdomain' ),
						'type' => 'ct-slider',
						'min' => 0,
						'max' => 10,
						'value' => 1,
						'setting' => array( 'transport' => 'postMessage' ),
					),
					'ct-information-border_color' => array(
						'label' => __( 'Border Color', 'textdomain' ),
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
								'title' => __( 'Initial', 'textdomain' ),
								'id' => 'default',
							),
						),
					),
					'ct-information-padding' => array(
						'label' => __( 'Vertical Padding', 'textdomain' ),
						'type' => 'ct-slider',
						'min' => 0,
						'max' => 100,
						'value' => 20,
						'setting' => array( 'transport' => 'postMessage' ),
					),
					'ct-information-justify_content' => array(
						'label' => __( 'Justify Content', 'textdomain' ),
						'type' => 'ct-select',
						'value' => 'center',
						'choices' => array(
							'flex-start' => __( 'Left', 'textdomain' ),
							'center' => __( 'Center', 'textdomain' ),
							'flex-end' => __( 'Right', 'textdomain' ),
							'space-between' => __( 'Justify', 'textdomain' ),
							'space-around' => __( 'Justify with Space Around', 'textdomain' ),
							'space-evenly' => __( 'Justify with Space Evenly', 'textdomain' ),
						),
						'setting' => array( 'transport' => 'postMessage' ),
					),
					'ct-information-text-color' => array(
						'label' => __( 'Text Color', 'textdomain' ),
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
								'title' => __( 'Initial', 'textdomain' ),
								'id' => 'default',
							),
						),
					),
					'ct-information-font-size' => array(
						'label' => __( 'Font Size', 'textdomain' ),
						'type' => 'ct-slider',
						'min' => 10,
						'max' => 50,
						'value' => 14,
						'setting' => array( 'transport' => 'postMessage' ),
					),
					'ct-information-text-underline' => array(
						'label' => __( 'Text Underline', 'textdomain' ),
						'type' => 'ct-switch',
						'value' => 'no',
						'setting' => array( 'transport' => 'postMessage' ),
					),
					'ct-information-item_horizontal_padding' => array(
						'label' => __( 'Item Horizontal Padding', 'textdomain' ),
						'type' => 'ct-slider',
						'min' => 0,
						'max' => 100,
						'value' => 20,
						'setting' => array( 'transport' => 'postMessage' ),
					),
					'ct-information-gap_inside' => array(
						'label' => __( 'Gap Inside Item', 'textdomain' ),
						'type' => 'ct-slider',
						'min' => 0,
						'max' => 100,
						'value' => 20,
						'setting' => array( 'transport' => 'postMessage' ),
					),
				),
			),
		);

		return array_merge( $options, $design_options );
	}

	/**
	 * Render dynamic CSS for live preview
	 *
	 * @param array $args CSS generation arguments
	 * @return void
	 */
	public function renderDynamicCSS( $args ) {
		if ( $args['context'] !== 'inline' ) {
			return;
		}

		// Check if we're on a single product page
		if ( ! is_product() ) {
			return;
		}

		// Get current layout to check if element is enabled
		$layout = get_theme_mod( 'woo_single_layout', array() );
		$element_enabled = false;

		foreach ( $layout as $layer ) {
			if ( isset( $layer['id'] ) && $layer['id'] === 'product_information' && isset( $layer['enabled'] ) && $layer['enabled'] ) {
				$element_enabled = true;
				break;
			}
		}

		if ( ! $element_enabled ) {
			return;
		}

		// Get design options from theme customizer
		$border_width = get_theme_mod( 'ct-information-border_width', 1 );
		$border_color = get_theme_mod( 'ct-information-border_color', array( 'default' => array( 'color' => '#e9ecef' ) ) );
		$padding = get_theme_mod( 'ct-information-padding', 20 );
		$justify_content = get_theme_mod( 'ct-information-justify_content', 'center' );
		$gap_inside = get_theme_mod( 'ct-information-gap_inside', 20 );
		$item_horizontal_padding = get_theme_mod( 'ct-information-item_horizontal_padding', 20 );
		$text_color = get_theme_mod( 'ct-information-text-color', array( 'default' => array( 'color' => '#333333' ) ) );
		$font_size = get_theme_mod( 'ct-information-font-size', 14 );
		$text_underline = get_theme_mod( 'ct-information-text-underline', 'no' );

		// Extract colors
		$border_color_value = $this->getColorValue( $border_color, '#e9ecef' );
		$text_color_value = $this->getColorValue( $text_color, '#333333' );

		// Generate CSS
		$css = $args['css'];
		$tablet_css = $args['tablet_css'];
		$mobile_css = $args['mobile_css'];

		$css->put(
			'.ct-product-information',
			array(
				'--product-information-border-width' => $border_width . 'px',
				'--product-information-border-color' => $border_color_value,
				'--product-information-padding' => $padding . 'px',
				'--product-information-justify-content' => $justify_content,
				'--product-information-gap-inside' => $gap_inside . 'px',
				'--product-information-item-horizontal-padding' => $item_horizontal_padding . 'px',
				'--product-information-text-color' => $text_color_value,
				'--product-information-font-size' => $font_size . 'px',
				'--product-information-text-underline' => $text_underline === 'yes' ? 'underline' : 'none',
			)
		);
	}

	/**
	 * Render element on frontend
	 *
	 * @param array $layer Layer options
	 *
	 * @return void
	 */
	public function renderLayer( $layer ) {
		if ( $layer['id'] !== 'product_information' ) {
			return;
		}

		global $product;

		if ( ! $product ) {
			return;
		}

		// Get add_separator from layer options (functional option, not design)
		$add_separator = isset( $layer['ct-information-add-separator'] ) ? $layer['ct-information-add-separator'] === 'yes' : false;
		$classes = [ 'ct-product-information' ];

		if ( $add_separator ) {
			$classes[] = 'has-separator';
		}

		ob_start();

		require_once( BLAZE_BLOCKSY_PATH . '/partials/product/information.php' );

		$content = ob_get_clean();

		// Output final dengan data attributes untuk JavaScript targeting
		echo blocksy_html_tag(
			'div',
			array(
				'class' => implode( ' ', $classes ),
				'data-element' => 'product_information', // Tambahkan untuk JavaScript targeting
				'data-id' => uniqid( 'product-info-' ), // Tambahkan unique ID
			),
			$content
		);
	}

	/**
	 * Enqueue needed styles
	 *
	 * @return void
	 */
	public function enqueueStyles() {
		if ( ! is_product() && ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
			return;
		}

		// Only enqueue the base CSS file, dynamic CSS is handled by renderDynamicCSS
		wp_enqueue_style( 'product-information-styles', BLAZE_BLOCKSY_URL . '/assets/product/information/style.css', array() );
	}

	private function getColorValue( $color_array, $default ) {
		if ( is_array( $color_array ) && isset( $color_array['default']['color'] ) ) {
			return $color_array['default']['color'];
		}

		return $default;
	}



	/**
	 * Enqueue needed scripts
	 *
	 * @return void
	 */
	public function addScript() {
		if ( ! is_product() && ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
			return;
		}

		ob_start();

		require_once( BLAZE_BLOCKSY_PATH . '/assets/product/information/script.js' );

		$js = ob_get_clean();

		?>
		<script>
			<?php echo $js; ?>
		</script>
		<?php

	}

	/**
	 * Enqueue customizer scripts untuk live preview
	 *
	 * @return void
	 */
	public function enqueueCustomizerScripts() {
		if ( ! is_customize_preview() ) {
			return;
		}

		wp_enqueue_script(
			'product-information-customizer',
			BLAZE_BLOCKSY_URL . '/assets/product/information/customizer.js',
			array( 'jquery', 'customize-preview', 'wp-util' ),
			'1.0.1', // Increment version untuk cache busting
			true
		);
	}

	/**
	 * Enqueue styles khusus untuk customizer preview
	 *
	 * @return void
	 */
	public function enqueueCustomizerStyles() {
		if ( ! is_customize_preview() ) {
			return;
		}

		// Force enqueue styles untuk customizer
		$this->enqueueStyles();
	}
}

// Inisialisasi element
new CustomProductInformationElement();
