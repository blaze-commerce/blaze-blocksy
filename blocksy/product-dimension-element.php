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
class CustomProductDimensionsElement {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Hook untuk menambahkan element ke single product page
		add_filter( 'blocksy_woo_single_options_layers:defaults', array( $this, 'addToDefaultLayout' ) );
		add_filter( 'blocksy_woo_single_options_layers:extra', array( $this, 'addLayerOptions' ) );
		add_action( 'blocksy:woocommerce:product:custom:layer', array( $this, 'renderLayer' ) );

		// Enqueue styles jika diperlukan
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueStyles' ) );
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
					'id'      => 'product_dimensions',
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
				'product_dimensions' => array(
					'label'   => __( 'Product Dimensions', 'textdomain' ),
					'options' => array(
						'dimensions_title'  => array(
							'label'               => __( 'Section Title', 'textdomain' ),
							'type'                => 'text',
							'value'               => 'Dimensions',
							'design'              => 'block',
							'disableRevertButton' => true,
							'sync'                => array(
								'id' => 'woo_single_layout_skip',
							),
						),

						'show_weight'       => array(
							'label' => __( 'Show Weight', 'textdomain' ),
							'type'  => 'ct-switch',
							'value' => 'yes',
							'sync'  => array(
								'id' => 'woo_single_layout_skip',
							),
						),

						'show_dimensions'   => array(
							'label' => __( 'Show Dimensions (L x W x H)', 'textdomain' ),
							'type'  => 'ct-switch',
							'value' => 'yes',
							'sync'  => array(
								'id' => 'woo_single_layout_skip',
							),
						),

						'dimensions_format' => array(
							'label'   => __( 'Dimensions Format', 'textdomain' ),
							'type'    => 'ct-select',
							'value'   => 'table',
							'design'  => 'inline',
							'choices' => array(
								'table'  => __( 'Table Format', 'textdomain' ),
								'inline' => __( 'Inline Format', 'textdomain' ),
								'list'   => __( 'List Format', 'textdomain' ),
							),
							'sync'    => array(
								'id' => 'woo_single_layout_skip',
							),
						),

						'spacing'           => array(
							'label'      => __( 'Bottom Spacing', 'textdomain' ),
							'type'       => 'ct-slider',
							'min'        => 0,
							'max'        => 100,
							'value'      => 20,
							'responsive' => true,
							'sync'       => array(
								'id' => 'woo_single_layout_skip',
							),
						),
					),
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
		if ( $layer['id'] !== 'product_dimensions' ) {
			return;
		}

		global $product;

		if ( ! $product ) {
			return;
		}

		// Ambil pengaturan dari layer
		$title           = blocksy_akg( 'dimensions_title', $layer, 'Dimensions' );
		$show_weight     = blocksy_akg( 'show_weight', $layer, 'yes' ) === 'yes';
		$show_dimensions = blocksy_akg( 'show_dimensions', $layer, 'yes' ) === 'yes';
		$format          = blocksy_akg( 'dimensions_format', $layer, 'table' );

		// Ambil data produk
		$weight = $product->get_weight();
		$length = $product->get_length();
		$width  = $product->get_width();
		$height = $product->get_height();

		// Cek apakah ada data yang akan ditampilkan
		$has_weight     = $show_weight && ! empty( $weight );
		$has_dimensions = $show_dimensions && ( ! empty( $length ) || ! empty( $width ) || ! empty( $height ) );

		if ( ! $has_weight && ! $has_dimensions ) {
			return; // Tidak ada data untuk ditampilkan
		}

		// Mulai output
		$content = '';

		// Title
		if ( ! empty( $title ) || is_customize_preview() ) {
			$content .= blocksy_html_tag(
				'h4',
				array( 'class' => 'ct-product-dimensions-title' ),
				$title
			);
		}

		// Content berdasarkan format
		if ( $format === 'table' ) {
			$content .= $this->_renderTableFormat( $product, $has_weight, $has_dimensions );
		} elseif ( $format === 'inline' ) {
			$content .= $this->_renderInlineFormat( $product, $has_weight, $has_dimensions );
		} else {
			$content .= $this->_renderListFormat( $product, $has_weight, $has_dimensions );
		}

		// Output final
		echo blocksy_html_tag(
			'div',
			array(
				'class' => 'ct-product-dimensions ct-format-' . $format,
			),
			$content
		);
	}

	/**
	 * Render format tabel
	 *
	 * @param object $product Product object
	 * @param bool   $has_weight Apakah ada berat
	 * @param bool   $has_dimensions Apakah ada dimensi
	 *
	 * @return string
	 */
	private function _renderTableFormat( $product, $has_weight, $has_dimensions ) {
		$rows = '';

		if ( $has_weight ) {
			$weight_unit = get_option( 'woocommerce_weight_unit' );
			$rows       .= '<tr><td>' . __( 'Weight', 'textdomain' ) . '</td><td>' . $product->get_weight() . ' ' . $weight_unit . '</td></tr>';
		}

		if ( $has_dimensions ) {
			$dimension_unit = get_option( 'woocommerce_dimension_unit' );
			$dimensions     = array_filter(
				array(
					$product->get_length(),
					$product->get_width(),
					$product->get_height(),
				)
			);

			if ( ! empty( $dimensions ) ) {
				$dimensions_text = implode( ' × ', $dimensions ) . ' ' . $dimension_unit;
				$rows           .= '<tr><td>' . __( 'Dimensions', 'textdomain' ) . '</td><td>' . $dimensions_text . '</td></tr>';
			}
		}

		return '<table class="ct-dimensions-table"><tbody>' . $rows . '</tbody></table>';
	}

	/**
	 * Render format inline
	 *
	 * @param object $product Product object
	 * @param bool   $has_weight Apakah ada berat
	 * @param bool   $has_dimensions Apakah ada dimensi
	 *
	 * @return string
	 */
	private function _renderInlineFormat( $product, $has_weight, $has_dimensions ) {
		$items = array();

		if ( $has_weight ) {
			$weight_unit = get_option( 'woocommerce_weight_unit' );
			$items[]     = '<span class="ct-weight">' . __( 'Weight:', 'textdomain' ) . ' ' . $product->get_weight() . ' ' . $weight_unit . '</span>';
		}

		if ( $has_dimensions ) {
			$dimension_unit = get_option( 'woocommerce_dimension_unit' );
			$dimensions     = array_filter(
				array(
					$product->get_length(),
					$product->get_width(),
					$product->get_height(),
				)
			);

			if ( ! empty( $dimensions ) ) {
				$dimensions_text = implode( ' × ', $dimensions ) . ' ' . $dimension_unit;
				$items[]         = '<span class="ct-dimensions">' . __( 'Dimensions:', 'textdomain' ) . ' ' . $dimensions_text . '</span>';
			}
		}

		return '<div class="ct-dimensions-inline">' . implode( ' | ', $items ) . '</div>';
	}

	/**
	 * Render format list
	 *
	 * @param object $product Product object
	 * @param bool   $has_weight Apakah ada berat
	 * @param bool   $has_dimensions Apakah ada dimensi
	 *
	 * @return string
	 */
	private function _renderListFormat( $product, $has_weight, $has_dimensions ) {
		$items = '';

		if ( $has_weight ) {
			$weight_unit = get_option( 'woocommerce_weight_unit' );
			$items      .= '<li><strong>' . __( 'Weight:', 'textdomain' ) . '</strong> ' . $product->get_weight() . ' ' . $weight_unit . '</li>';
		}

		if ( $has_dimensions ) {
			$dimension_unit = get_option( 'woocommerce_dimension_unit' );
			$dimensions     = array_filter(
				array(
					$product->get_length(),
					$product->get_width(),
					$product->get_height(),
				)
			);

			if ( ! empty( $dimensions ) ) {
				$dimensions_text = implode( ' × ', $dimensions ) . ' ' . $dimension_unit;
				$items          .= '<li><strong>' . __( 'Dimensions:', 'textdomain' ) . '</strong> ' . $dimensions_text . '</li>';
			}
		}

		return '<ul class="ct-dimensions-list">' . $items . '</ul>';
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

		$css = '
        .ct-product-dimensions {
            margin-bottom: 20px;
        }
        
        .ct-product-dimensions-title {
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        .ct-dimensions-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .ct-dimensions-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
        }
        
        .ct-dimensions-table td:first-child {
            font-weight: 600;
            background-color: #f9f9f9;
            width: 30%;
        }
        
        .ct-dimensions-inline span {
            margin-right: 15px;
        }
        
        .ct-dimensions-list {
            margin: 0;
            padding-left: 20px;
        }
        
        .ct-dimensions-list li {
            margin-bottom: 5px;
        }
        ';

		wp_add_inline_style( 'ct-main-styles', $css );
	}
}

// Inisialisasi element
new CustomProductDimensionsElement();
