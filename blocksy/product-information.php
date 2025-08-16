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

		// Enqueue styles jika diperlukan
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueStyles' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ), 100 );
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
							'sync' => array(
								'id' => 'woo_single_layout_skip',
							),
						),
						'ct-information-border_color' => array(
							'label' => __( 'Border Color', 'textdomain' ),
							'type' => 'ct-color-picker',
							'design' => 'inline', // atau 'block'
							'sync' => array(
								'id' => 'woo_single_layout_skip',
							),
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
							'sync' => array(
								'id' => 'woo_single_layout_skip',
							),
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
							),
							'sync' => array(
								'id' => 'woo_single_layout_skip',
							),
						),
						'ct-information-gap_between' => array(
							'label' => __( 'Gap Between Items', 'textdomain' ),
							'type' => 'ct-slider',
							'min' => 0,
							'max' => 100,
							'value' => 20,
							'sync' => array(
								'id' => 'woo_single_layout_skip',
							),
						),
						'ct-information-gap_inside' => array(
							'label' => __( 'Gap Inside Item', 'textdomain' ),
							'type' => 'ct-slider',
							'min' => 0,
							'max' => 100,
							'value' => 20,
							'sync' => array(
								'id' => 'woo_single_layout_skip',
							),
						),
					)
				),
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

		ob_start();

		require_once( get_stylesheet_directory() . '/partials/product/information.php' );

		$content = ob_get_clean();

		// Output final
		echo blocksy_html_tag(
			'div',
			array(
				'class' => 'ct-product-information',
			),
			$content
		);
	}

	/**
	 * Enqueue needed styles
	 * @param array $layer Layer options
	 *
	 * @return void
	 */
	public function enqueueStyles( $layer ) {
		if ( ! is_product() && ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
			return;
		}

		ob_start();

		?>
		:root {
		--product-information-border-width: <?php echo blocksy_akg( 'ct-information-border_width', $layer, 1 ) . 'px'; ?>;
		--product-information-border-color: <?php echo blocksy_akg( 'ct-information-border_color', $layer, '#e9ecef' ); ?>;
		--product-information-padding: <?php echo blocksy_akg( 'ct-information-padding', $layer, 20 ) . 'px'; ?>;
		--product-information-justify-content:
		<?php echo blocksy_akg( 'ct-information-justify_content', $layer, 'justify-center' ); ?>;
		--product-information-gap-between: <?php echo blocksy_akg( 'ct-information-gap_between', $layer, 20 ) . 'px'; ?>;
		--product-information-gap-inside: <?php echo blocksy_akg( 'ct-information-gap_inside', $layer, 20 ) . 'px'; ?>;
		}
		<?php

		require_once( get_stylesheet_directory() . '/assets/product/information/style.css' );

		$css = ob_get_clean();

		do_action( 'qm/info', [ 'css' => $css ] );

		wp_add_inline_style( 'ct-main-styles', $css );
	}

	/**
	 * Enqueue needed scripts
	 *
	 * @return void
	 */
	public function enqueueScripts() {
		if ( ! is_product() && ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
			return;
		}

		ob_start();

		require_once( get_stylesheet_directory() . '/assets/product/information/script.js' );

		$js = ob_get_clean();

		wp_add_inline_script( 'ct-main-scripts', $js );
	}
}

// Inisialisasi element
new CustomProductInformationElement();
