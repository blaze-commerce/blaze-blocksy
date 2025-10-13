<?php
/**
 * Product Image Block - Enhancements
 *
 * Extends WooCommerce Product Image block with:
 * 1. Hover image swap (shows second gallery image on hover)
 * 2. Wishlist button integration (uses Blocksy wishlist functionality)
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Product Image Enhancement Extension Class
 */
class WC_Product_Image_Enhancement_Extension {

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
		// Extend block metadata with enhancement attributes
		add_filter( 'block_type_metadata', array( $this, 'extend_block_metadata' ) );

		// Add enhancements to rendered block
		add_filter( 'render_block_woocommerce/product-image', array( $this, 'add_image_enhancements' ), 10, 2 );

		// Enqueue editor assets
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );

		// Enqueue frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Extend Product Image block metadata
	 *
	 * @param array $metadata Block metadata.
	 * @return array Modified metadata.
	 */
	public function extend_block_metadata( $metadata ) {
		if ( isset( $metadata['name'] ) && $metadata['name'] === 'woocommerce/product-image' ) {
			if ( ! isset( $metadata['attributes'] ) ) {
				$metadata['attributes'] = array();
			}

			$metadata['attributes'] = array_merge(
				$metadata['attributes'],
				array(
					'enableHoverImage' => array(
						'type' => 'boolean',
						'default' => false,
					),
					'showWishlistButton' => array(
						'type' => 'boolean',
						'default' => false,
					),
					'wishlistButtonPosition' => array(
						'type' => 'string',
						'default' => 'top-right',
						'enum' => array( 'top-left', 'top-right', 'bottom-left', 'bottom-right' ),
					),
				)
			);
		}
		return $metadata;
	}

	/**
	 * Add hover image and wishlist functionality to Product Image block
	 *
	 * @param string $block_content Block HTML content.
	 * @param array  $block Block data.
	 * @return string Modified block content.
	 */
	public function add_image_enhancements( $block_content, $block ) {
		$enable_hover = $block['attrs']['enableHoverImage'] ?? false;
		$show_wishlist = $block['attrs']['showWishlistButton'] ?? false;

		// If no enhancements are enabled, return original content
		if ( ! $enable_hover && ! $show_wishlist ) {
			return $block_content;
		}

		// Get product ID from block context
		$post_id = $block['context']['postId'] ?? 0;
		if ( ! $post_id ) {
			return $block_content;
		}

		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return $block_content;
		}

		// Use WP_HTML_Tag_Processor to safely modify HTML
		$processor = new WP_HTML_Tag_Processor( $block_content );

		// Find the product image container
		if ( $processor->next_tag( array( 'class_name' => 'wc-block-components-product-image' ) ) ) {
			$processor->add_class( 'wc-enhanced-product-image' );

			// Add hover image functionality
			if ( $enable_hover ) {
				$processor->add_class( 'wc-hover-image-enabled' );
				$hover_image_data = $this->get_hover_image_data( $product );
				if ( $hover_image_data ) {
					$processor->set_attribute( 'data-hover-image', wp_json_encode( $hover_image_data ) );
				}
			}

			// Add wishlist button functionality
			if ( $show_wishlist ) {
				$processor->add_class( 'wc-wishlist-enabled' );
				$position = $block['attrs']['wishlistButtonPosition'] ?? 'top-right';
				$processor->set_attribute( 'data-wishlist-position', $position );
				$processor->set_attribute( 'data-product-id', $post_id );
			}
		}

		$modified_content = $processor->get_updated_html();

		// Add wishlist button HTML if enabled
		if ( $show_wishlist ) {
			$wishlist_button = $this->get_wishlist_button_html( $product, $block['attrs'] );
			// Insert wishlist button before the closing div of the image container
			$modified_content = preg_replace(
				'/(<\/div>\s*<\/div>)$/i',
				$wishlist_button . '$1',
				$modified_content,
				1
			);
		}

		return $modified_content;
	}

	/**
	 * Get hover image data for product
	 *
	 * @param WC_Product $product Product object.
	 * @return array|null Hover image data or null if no gallery images.
	 */
	private function get_hover_image_data( $product ) {
		$gallery_images = $product->get_gallery_image_ids();

		if ( empty( $gallery_images ) ) {
			return null;
		}

		$hover_image_id = $gallery_images[0];
		$hover_image_url = wp_get_attachment_image_url( $hover_image_id, 'woocommerce_thumbnail' );
		$hover_image_srcset = wp_get_attachment_image_srcset( $hover_image_id, 'woocommerce_thumbnail' );

		return array(
			'url' => $hover_image_url,
			'srcset' => $hover_image_srcset,
			'alt' => get_post_meta( $hover_image_id, '_wp_attachment_image_alt', true ),
		);
	}

	/**
	 * Generate wishlist button HTML
	 *
	 * Uses Blocksy wishlist functionality if available
	 *
	 * @param WC_Product $product Product object.
	 * @param array      $attributes Block attributes.
	 * @return string Wishlist button HTML.
	 */
	private function get_wishlist_button_html( $product, $attributes ) {
		$position = $attributes['wishlistButtonPosition'] ?? 'top-right';
		$product_id = $product->get_id();

		// Check if product is in wishlist
		$is_in_wishlist = false;
		if ( class_exists( 'BlocksyChildWishlistHelper' ) ) {
			$wishlist = BlocksyChildWishlistHelper::get_current_wishlist();
			$product_ids = BlocksyChildWishlistHelper::extract_product_ids( $wishlist );
			$is_in_wishlist = in_array( $product_id, $product_ids, true );
		}

		$button_class = 'wc-wishlist-button wc-wishlist-button--' . esc_attr( $position );
		if ( $is_in_wishlist ) {
			$button_class .= ' wc-wishlist-added';
		}

		$button_html = sprintf(
			'<button class="%s" data-product-id="%d" aria-label="%s" type="button">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
				</svg>
			</button>',
			esc_attr( $button_class ),
			esc_attr( $product_id ),
			esc_attr__( 'Add to wishlist', 'blocksy-child' )
		);

		return $button_html;
	}

	/**
	 * Enqueue editor assets
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script(
			'wc-product-image-enhancement-editor',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/product-image-enhancement-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-compose', 'wp-hooks' ),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/product-image-enhancement-editor.js' ),
			true
		);
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		// Only enqueue if Product Image block is present
		if ( ! has_block( 'woocommerce/product-image' ) ) {
			return;
		}

		wp_enqueue_script(
			'wc-product-image-enhancement-frontend',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/product-image-enhancement-frontend.js',
			array( 'jquery' ),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/product-image-enhancement-frontend.js' ),
			true
		);

		// Localize script with AJAX data for wishlist integration
		wp_localize_script(
			'wc-product-image-enhancement-frontend',
			'wcBlockImageEnhancements',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wc_block_image_enhancements_nonce' ),
				'messages' => array(
					'added' => __( 'Added to wishlist', 'blocksy-child' ),
					'removed' => __( 'Removed from wishlist', 'blocksy-child' ),
					'error' => __( 'Error updating wishlist', 'blocksy-child' ),
				),
			)
		);

		wp_enqueue_style(
			'wc-product-image-enhancement',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/product-image-enhancement.css',
			array(),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/product-image-enhancement.css' )
		);
	}
}

// Initialize the extension
new WC_Product_Image_Enhancement_Extension();

