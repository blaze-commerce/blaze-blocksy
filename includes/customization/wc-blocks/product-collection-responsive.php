<?php
/**
 * Product Collection Block - Responsive Extension
 *
 * Extends WooCommerce Product Collection block with responsive column and product count controls.
 * Allows different settings for desktop, tablet, and mobile devices.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Product Collection Responsive Extension Class
 */
class WC_Product_Collection_Responsive_Extension {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Extend block metadata with responsive attributes
		add_filter( 'block_type_metadata', array( $this, 'extend_block_metadata' ) );
		
		// Add responsive attributes to rendered block
		add_filter( 'render_block_woocommerce/product-collection', array( $this, 'add_responsive_attributes' ), 10, 2 );
		
		// Enqueue editor assets
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		
		// Enqueue frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Extend Product Collection block metadata with responsive attributes
	 *
	 * @param array $metadata Block metadata.
	 * @return array Modified metadata.
	 */
	public function extend_block_metadata( $metadata ) {
		if ( isset( $metadata['name'] ) && $metadata['name'] === 'woocommerce/product-collection' ) {
			if ( ! isset( $metadata['attributes'] ) ) {
				$metadata['attributes'] = array();
			}

			$metadata['attributes'] = array_merge(
				$metadata['attributes'],
				array(
					'responsiveColumns'      => array(
						'type'    => 'object',
						'default' => array(
							'desktop' => 4,
							'tablet'  => 3,
							'mobile'  => 2,
						),
					),
					'responsiveProductCount' => array(
						'type'    => 'object',
						'default' => array(
							'desktop' => 8,
							'tablet'  => 6,
							'mobile'  => 4,
						),
					),
					'enableResponsive'       => array(
						'type'    => 'boolean',
						'default' => false,
					),
				)
			);
		}
		return $metadata;
	}

	/**
	 * Add responsive CSS classes and data attributes to Product Collection block
	 *
	 * @param string $block_content Block HTML content.
	 * @param array  $block Block data.
	 * @return string Modified block content.
	 */
	public function add_responsive_attributes( $block_content, $block ) {
		// Check if responsive mode is enabled
		if ( ! isset( $block['attrs']['enableResponsive'] ) || ! $block['attrs']['enableResponsive'] ) {
			return $block_content;
		}

		$responsive_columns = $block['attrs']['responsiveColumns'] ?? array(
			'desktop' => 4,
			'tablet'  => 3,
			'mobile'  => 2,
		);
		$responsive_counts  = $block['attrs']['responsiveProductCount'] ?? array(
			'desktop' => 8,
			'tablet'  => 6,
			'mobile'  => 4,
		);

		// Use WP_HTML_Tag_Processor to safely modify HTML
		$processor = new WP_HTML_Tag_Processor( $block_content );

		// Find the product collection container
		if ( $processor->next_tag( array( 'class_name' => 'wp-block-woocommerce-product-collection' ) ) ) {
			$processor->add_class( 'wc-responsive-collection' );

			// Add data attributes for JavaScript
			if ( ! empty( $responsive_columns ) ) {
				$processor->set_attribute( 'data-responsive-columns', wp_json_encode( $responsive_columns ) );
			}
			if ( ! empty( $responsive_counts ) ) {
				$processor->set_attribute( 'data-responsive-counts', wp_json_encode( $responsive_counts ) );
			}
		}

		return $processor->get_updated_html();
	}

	/**
	 * Enqueue editor assets
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script(
			'wc-product-collection-responsive-editor',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/product-collection-responsive-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-compose', 'wp-hooks' ),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/product-collection-responsive-editor.js' ),
			true
		);
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		// Only enqueue if Product Collection block is present
		if ( ! has_block( 'woocommerce/product-collection' ) ) {
			return;
		}

		wp_enqueue_script(
			'wc-product-collection-responsive-frontend',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/product-collection-responsive-frontend.js',
			array( 'jquery' ),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/product-collection-responsive-frontend.js' ),
			true
		);

		wp_enqueue_style(
			'wc-product-collection-responsive',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/product-collection-responsive.css',
			array(),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/product-collection-responsive.css' )
		);
	}
}

// Initialize the extension
new WC_Product_Collection_Responsive_Extension();

