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
		// add_filter( 'blocksy:global-dynamic-css:enqueue:singular', array( $this, 'renderDesign' ) );
		// add_filter( 'blocksy:options:single_product:elements:design_tab:end', array( $this, 'addDesignOptions' ) );

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
						'ct-information-border_width' => array(
							'label' => __( 'Border Width', 'textdomain' ),
							'type' => 'ct-slider',
							'min' => 0,
							'max' => 10,
							'value' => 1,
							'sync' => 'live', // Ubah ke 'live' untuk real-time preview
							'refresh' => false, // Explicitly prevent refresh
							'transport' => 'postMessage', // Use postMessage transport
						),
						'ct-information-border_color' => array(
							'label' => __( 'Border Color', 'textdomain' ),
							'type' => 'ct-color-picker',
							'design' => 'inline', // atau 'block'
							'sync' => 'live', // Ubah ke 'live' untuk real-time preview
							'refresh' => false, // Explicitly prevent refresh
							'transport' => 'postMessage', // Use postMessage transport
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
							'label' => __( 'VerticalPadding', 'textdomain' ),
							'type' => 'ct-slider',
							'min' => 0,
							'max' => 100,
							'value' => 20,
							'sync' => 'live', // Ubah ke 'live' untuk real-time preview
							'refresh' => false, // Explicitly prevent refresh
							'transport' => 'postMessage', // Use postMessage transport
						),
						'ct-information-justify_content' => array(
							'label' => __( 'Justify Content', 'textdomain' ),
							'type' => 'ct-select',
							'value' => 'justify-center',
							'choices' => array(
								'flex-start' => __( 'Left', 'textdomain' ),
								'center' => __( 'Center', 'textdomain' ),
								'flex-end' => __( 'Right', 'textdomain' ),
								'space-between' => __( 'Justify', 'textdomain' ),
								'space-around' => __( 'Justify with Space Around', 'textdomain' ),
								'space-evenly' => __( 'Justify with Space Evenly', 'textdomain' ),
							),
							'sync' => 'live', // Ubah ke 'live' untuk real-time preview
							'refresh' => false, // Explicitly prevent refresh
							'transport' => 'postMessage', // Use postMessage transport
						),
						'ct-information-add-separator' => array(
							'label' => __( 'Add Separator', 'textdomain' ),
							'type' => 'ct-switch',
							'value' => 'no',
							'sync' => 'live', // Ubah ke 'live' untuk real-time preview
							'refresh' => false, // Explicitly prevent refresh
							'transport' => 'postMessage', // Use postMessage transport
						),
						'ct-information-text-color' => array(
							'label' => __( 'Text Color', 'textdomain' ),
							'type' => 'ct-color-picker',
							'design' => 'inline', // atau 'block'
							'sync' => 'live', // Ubah ke 'live' untuk real-time preview
							'refresh' => false, // Explicitly prevent refresh
							'transport' => 'postMessage', // Use postMessage transport
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
							'sync' => 'live', // Ubah ke 'live' untuk real-time preview
							'refresh' => false, // Explicitly prevent refresh
							'transport' => 'postMessage', // Use postMessage transport
						),
						'ct-information-text-underline' => array(
							'label' => __( 'Text Underline', 'textdomain' ),
							'type' => 'ct-switch',
							'value' => 'no',
							'sync' => 'live', // Ubah ke 'live' untuk real-time preview
							'refresh' => false, // Explicitly prevent refresh
							'transport' => 'postMessage', // Use postMessage transport
						),
						'ct-information-item_horizontal_padding' => array(
							'label' => __( 'Item Horizontal Padding', 'textdomain' ),
							'type' => 'ct-slider',
							'min' => 0,
							'max' => 100,
							'value' => 20,
							'sync' => 'live', // Ubah ke 'live' untuk real-time preview
							'refresh' => false, // Explicitly prevent refresh
							'transport' => 'postMessage', // Use postMessage transport
						),
						'ct-information-gap_inside' => array(
							'label' => __( 'Gap Inside Item', 'textdomain' ),
							'type' => 'ct-slider',
							'min' => 0,
							'max' => 100,
							'value' => 20,
							'sync' => 'live', // Ubah ke 'live' untuk real-time preview
							'refresh' => false, // Explicitly prevent refresh
							'transport' => 'postMessage', // Use postMessage transport
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
			blocksy_rand_md5() => array(
				'type' => 'ct-condition',
				'condition' => [ 'woo_single_layout:array-ids:product_information:enabled' => '!no' ],
				'computed_fields' => [ 'woo_single_layout' ],
				'options' => array(
					blocksy_rand_md5() => array(
						'type' => 'ct-title',
						'variation' => 'small-divider',
						'label' => __( 'Product Information', 'blocksy' ),
					),
					'ct-information-border_width' => array(
						'label' => __( 'Border Width', 'textdomain' ),
						'type' => 'ct-slider',
						'min' => 0,
						'max' => 10,
						'value' => 1,
						'sync' => 'live', // Ubah ke 'live' untuk real-time preview
						'refresh' => false, // Explicitly prevent refresh
						'transport' => 'postMessage', // Use postMessage transport
					),
					'ct-information-border_color' => array(
						'label' => __( 'Border Color', 'textdomain' ),
						'type' => 'ct-color-picker',
						'design' => 'inline', // atau 'block'
						'sync' => 'live', // Ubah ke 'live' untuk real-time preview
						'refresh' => false, // Explicitly prevent refresh
						'transport' => 'postMessage', // Use postMessage transport
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
						'label' => __( 'VerticalPadding', 'textdomain' ),
						'type' => 'ct-slider',
						'min' => 0,
						'max' => 100,
						'value' => 20,
						'sync' => 'live', // Ubah ke 'live' untuk real-time preview
						'refresh' => false, // Explicitly prevent refresh
						'transport' => 'postMessage', // Use postMessage transport
					),
					'ct-information-justify_content' => array(
						'label' => __( 'Justify Content', 'textdomain' ),
						'type' => 'ct-select',
						'value' => 'justify-center',
						'choices' => array(
							'flex-start' => __( 'Left', 'textdomain' ),
							'center' => __( 'Center', 'textdomain' ),
							'flex-end' => __( 'Right', 'textdomain' ),
							'space-between' => __( 'Justify', 'textdomain' ),
							'space-around' => __( 'Justify with Space Around', 'textdomain' ),
							'space-evenly' => __( 'Justify with Space Evenly', 'textdomain' ),
						),
						'sync' => 'live', // Ubah ke 'live' untuk real-time preview
						'refresh' => false, // Explicitly prevent refresh
						'transport' => 'postMessage', // Use postMessage transport
					),
					'ct-information-add-separator' => array(
						'label' => __( 'Add Separator', 'textdomain' ),
						'type' => 'ct-switch',
						'value' => 'no',
						'sync' => 'live', // Ubah ke 'live' untuk real-time preview
						'refresh' => false, // Explicitly prevent refresh
						'transport' => 'postMessage', // Use postMessage transport
					),
					'ct-information-text-color' => array(
						'label' => __( 'Text Color', 'textdomain' ),
						'type' => 'ct-color-picker',
						'design' => 'inline', // atau 'block'
						'sync' => 'live', // Ubah ke 'live' untuk real-time preview
						'refresh' => false, // Explicitly prevent refresh
						'transport' => 'postMessage', // Use postMessage transport
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
						'sync' => 'live', // Ubah ke 'live' untuk real-time preview
						'refresh' => false, // Explicitly prevent refresh
						'transport' => 'postMessage', // Use postMessage transport
					),
					'ct-information-text-underline' => array(
						'label' => __( 'Text Underline', 'textdomain' ),
						'type' => 'ct-switch',
						'value' => 'no',
						'sync' => 'live', // Ubah ke 'live' untuk real-time preview
						'refresh' => false, // Explicitly prevent refresh
						'transport' => 'postMessage', // Use postMessage transport
					),
					'ct-information-item_horizontal_padding' => array(
						'label' => __( 'Item Horizontal Padding', 'textdomain' ),
						'type' => 'ct-slider',
						'min' => 0,
						'max' => 100,
						'value' => 20,
						'sync' => 'live', // Ubah ke 'live' untuk real-time preview
						'refresh' => false, // Explicitly prevent refresh
						'transport' => 'postMessage', // Use postMessage transport
					),
					'ct-information-gap_inside' => array(
						'label' => __( 'Gap Inside Item', 'textdomain' ),
						'type' => 'ct-slider',
						'min' => 0,
						'max' => 100,
						'value' => 20,
						'sync' => 'live', // Ubah ke 'live' untuk real-time preview
						'refresh' => false, // Explicitly prevent refresh
						'transport' => 'postMessage', // Use postMessage transport
					),
				),
			)
		);

		$options = array_merge( $options, $design_options );

		return $options;
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

		$add_separator = blocksy_akg( 'ct-information-add-separator', $layer, 'no' ) === 'yes';
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

		// Get current layout untuk mendapatkan layer settings
		$layout = get_theme_mod( 'woo_single_layout', array() );
		$product_info_layer = null;

		// Cari layer product_information
		foreach ( $layout as $layer ) {
			if ( isset( $layer['id'] ) && $layer['id'] === 'product_information' && isset( $layer['enabled'] ) && $layer['enabled'] ) {
				$product_info_layer = $layer;
				break;
			}
		}

		// Jika layer tidak ditemukan atau tidak enabled, gunakan default values
		if ( ! $product_info_layer ) {
			$product_info_layer = array(
				'ct-information-border_width' => 1,
				'ct-information-border_color' => array( 'default' => array( 'color' => '#e9ecef' ) ),
				'ct-information-padding' => 20,
				'ct-information-justify_content' => 'center',
				'ct-information-gap_inside' => 20,
				'ct-information-add-separator' => 'no',
				'ct-information-item_horizontal_padding' => 20,
				'ct-text-color' => array( 'default' => array( 'color' => '#333333' ) ),
			);
		}

		$this->generateDynamicCSS( $product_info_layer );
	}

	private function getColor( $layer, $field, $default ) {
		if ( isset( $layer[ $field ] ) && is_array( $layer[ $field ] ) ) {
			if ( isset( $layer[ $field ]['default']['color'] ) ) {
				return $layer[ $field ]['default']['color'];
			}
		}

		return $default;
	}

	/**
	 * Generate dynamic CSS dengan CSS variables
	 *
	 * @param array $layer Layer configuration
	 * @return void
	 */
	private function generateDynamicCSS( $layer ) {
		ob_start();

		// Extract border color
		$border_color = $this->getColor( $layer, 'ct-information-border_color', '#e9ecef' );
		$text_color = $this->getColor( $layer, 'ct-information-text-color', '#333333' );

		?>
		:root {
		--product-information-border-width: <?php echo blocksy_akg( 'ct-information-border_width', $layer, 1 ) . 'px'; ?>;
		--product-information-border-color: <?php echo esc_attr( $border_color ); ?>;
		--product-information-padding: <?php echo blocksy_akg( 'ct-information-padding', $layer, 20 ) . 'px'; ?>;
		--product-information-justify-content:
		<?php echo esc_attr( blocksy_akg( 'ct-information-justify_content', $layer, 'center' ) ); ?>;
		--product-information-gap-inside: <?php echo blocksy_akg( 'ct-information-gap_inside', $layer, 20 ) . 'px'; ?>;
		--product-information-item-horizontal-padding:
		<?php echo blocksy_akg( 'ct-information-item_horizontal_padding', $layer, 20 ) . 'px'; ?>;
		--product-information-text-color: <?php echo esc_attr( $text_color ); ?>;
		--product-information-font-size: <?php echo blocksy_akg( 'ct-information-font-size', $layer, 14 ) . 'px'; ?>;
		--product-information-text-underline:
		<?php echo blocksy_akg( 'ct-information-text-underline', $layer, 'no' ) === 'yes' ? 'underline' : 'none'; ?>;
		}
		<?php

		$css = ob_get_clean();

		wp_enqueue_style( 'product-information-styles', BLAZE_BLOCKSY_URL . '/assets/product/information/style.css', [] );

		wp_add_inline_style( 'product-information-styles', $css );
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
