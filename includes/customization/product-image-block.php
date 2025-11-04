<?php
/**
 * WooCommerce Product Image Block Customization
 *
 * This file implements hover image effect and wishlist button functionality
 * for WooCommerce Product Image blocks in product listings.
 *
 * Features:
 * - Hover image effect: Display second product image on mouse hover
 * - Wishlist button: Floating add to wishlist button on product images
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WooCommerce_Product_Image_Block_Enhancement
 *
 * Handles the enhancement of WooCommerce Product Image blocks with
 * hover effects and wishlist functionality.
 */
class WooCommerce_Product_Image_Block_Enhancement {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Modify product image block output
		add_filter( 'render_block_woocommerce/product-image', array( $this, 'enhance_product_image_block' ), 10, 2 );

		// Enqueue frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enhance the product image block output
	 *
	 * @param string $block_content The block content.
	 * @param array  $block The block data.
	 * @return string Modified block content.
	 */
	public function enhance_product_image_block( $block_content, $block ) {
		global $product;

		// check if product is a WooCommerce product
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return $block_content;
		}

		// Get product ID from block context
		$post_id = $product->get_id();

		if ( ! $post_id ) {
			return $block_content;
		}

		// Process the HTML using WP_HTML_Tag_Processor
		$processor = new WP_HTML_Tag_Processor( $block_content );

		// Find the product image container
		if ( $processor->next_tag( array( 'class_name' => 'wc-block-components-product-image' ) ) ) {
			// Add enhancement class
			$processor->add_class( 'wc-enhanced-product-image' );

			// Add hover image functionality
			$hover_image_data = $this->get_hover_image_data( $product );
			if ( $hover_image_data ) {
				$processor->add_class( 'wc-hover-image-enabled' );
				// Store as separate data attributes to avoid encoding issues
				$processor->set_attribute( 'data-hover-url', esc_url( $hover_image_data['url'] ) );
				$processor->set_attribute( 'data-hover-srcset', esc_attr( $hover_image_data['srcset'] ) );
				$processor->set_attribute( 'data-hover-alt', esc_attr( $hover_image_data['alt'] ) );
			}

			// Add wishlist functionality
			$processor->add_class( 'wc-wishlist-enabled' );
			$processor->set_attribute( 'data-product-id', $post_id );

			// Get the enhanced content
			$enhanced_content = $processor->get_updated_html();

			// Inject wishlist button before closing div
			$wishlist_button = $this->get_wishlist_button_html( $product );
			$enhanced_content = $this->inject_wishlist_button( $enhanced_content, $wishlist_button );

			return $enhanced_content;
		}

		return $block_content;
	}

	/**
	 * Get hover image data for a product
	 *
	 * @param WC_Product $product The product object.
	 * @return array|null Hover image data or null if no gallery images.
	 */
	private function get_hover_image_data( $product ) {
		// Get gallery image IDs
		$gallery_ids = $product->get_gallery_image_ids();

		// Return null if no gallery images
		if ( empty( $gallery_ids ) ) {
			return null;
		}

		// Get the second image (first gallery image)
		$hover_image_id = $gallery_ids[0];

		// Get image data
		$hover_image_url = wp_get_attachment_image_url( $hover_image_id, 'woocommerce_thumbnail' );
		$hover_image_srcset = wp_get_attachment_image_srcset( $hover_image_id, 'woocommerce_thumbnail' );
		$hover_image_alt = get_post_meta( $hover_image_id, '_wp_attachment_image_alt', true );

		// Return structured data
		return array(
			'url' => $hover_image_url,
			'srcset' => $hover_image_srcset ?: '',
			'alt' => $hover_image_alt ?: $product->get_name(),
		);
	}

	/**
	 * Generate wishlist button HTML
	 *
	 * @param WC_Product $product The product object.
	 * @return string Wishlist button HTML.
	 */
	private function get_wishlist_button_html( $product ) {
		$product_id = $product->get_id();

		// Check if product is already in wishlist
		$is_in_wishlist = $this->is_product_in_wishlist( $product_id );
		$button_class = 'wc-product-image-wishlist-button';
		if ( $is_in_wishlist ) {
			$button_class .= ' added';
		}

		// Heart icon SVG
		$heart_icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
			<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
		</svg>';

		return sprintf(
			'<button class="%s" data-product-id="%d" aria-label="%s" title="%s">%s</button>',
			esc_attr( $button_class ),
			esc_attr( $product_id ),
			esc_attr__( 'Add to wishlist', 'blocksy-child' ),
			esc_attr__( 'Add to wishlist', 'blocksy-child' ),
			$heart_icon
		);
	}

	/**
	 * Inject wishlist button into the HTML content
	 *
	 * @param string $content The HTML content.
	 * @param string $button The wishlist button HTML.
	 * @return string Modified HTML content.
	 */
	private function inject_wishlist_button( $content, $button ) {
		// Find the last closing div tag and inject button before it
		$last_div_pos = strrpos( $content, '</div>' );

		if ( false !== $last_div_pos ) {
			$content = substr_replace( $content, $button, $last_div_pos, 0 );
		}

		return $content;
	}

	/**
	 * Check if product is in wishlist
	 *
	 * @param int $product_id The product ID.
	 * @return bool True if product is in wishlist.
	 */
	private function is_product_in_wishlist( $product_id ) {
		// Check if wishlist helper class exists
		if ( ! class_exists( 'BlocksyChildWishlistHelper' ) ) {
			return false;
		}

		// Get wishlist extension
		$wishlist_ext = BlocksyChildWishlistHelper::get_wishlist_extension();
		if ( ! $wishlist_ext ) {
			return false;
		}

		// Get current wishlist
		$wishlist = BlocksyChildWishlistHelper::get_current_wishlist();
		if ( empty( $wishlist ) ) {
			return false;
		}

		// Check if product is in wishlist
		foreach ( $wishlist as $item ) {
			$item_product_id = is_array( $item ) && isset( $item['product_id'] )
				? $item['product_id']
				: ( is_object( $item ) && isset( $item->product_id ) ? $item->product_id : $item );

			if ( (int) $item_product_id === (int) $product_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_assets() {

		// Enqueue CSS
		wp_enqueue_style(
			'blaze-product-image-block',
			BLAZE_BLOCKSY_URL . '/assets/css/product-image-block.css',
			array(),
			'1.0.0'
		);

		// Enqueue JavaScript
		wp_enqueue_script(
			'blaze-product-image-block',
			BLAZE_BLOCKSY_URL . '/assets/js/product-image-block.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		// Localize script for AJAX and settings
		wp_localize_script(
			'blaze-product-image-block',
			'blazeProductImageBlock',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'blaze_product_image_block_nonce' ),
				'messages' => array(
					'added' => __( 'Added to wishlist!', 'blocksy-child' ),
					'removed' => __( 'Removed from wishlist', 'blocksy-child' ),
					'error' => __( 'Error processing request', 'blocksy-child' ),
				),
			)
		);

		// Ensure Blocksy wishlist data is available
		$this->ensure_wishlist_data();
	}

	/**
	 * Ensure wishlist data is available in JavaScript
	 */
	private function ensure_wishlist_data() {
		// Check if Blocksy Companion Pro is active
		if ( ! function_exists( 'blc_get_ext' ) ) {
			return;
		}

		// Get wishlist extension
		$wishlist_ext = BlocksyChildWishlistHelper::get_wishlist_extension();
		if ( ! $wishlist_ext ) {
			return;
		}

		// Get current wishlist
		$wishlist = BlocksyChildWishlistHelper::get_current_wishlist();

		// Prepare wishlist data
		$wishlist_data = array(
			'v' => 2,
			'items' => array(),
		);

		// Format wishlist items
		if ( ! empty( $wishlist ) ) {
			foreach ( $wishlist as $item ) {
				$wishlist_item = array( 'id' => is_array( $item ) ? $item['id'] : $item );

				// Add attributes if exists (for variable products)
				if ( is_array( $item ) && isset( $item['attributes'] ) ) {
					$wishlist_item['attributes'] = $item['attributes'];
				}

				$wishlist_data['items'][] = $wishlist_item;
			}
		}

		// Add inline script to ensure ct_localizations exists
		wp_add_inline_script(
			'blaze-product-image-block',
			sprintf(
				'window.ct_localizations = window.ct_localizations || {};
				window.ct_localizations.ajax_url = window.ct_localizations.ajax_url || %s;
				window.ct_localizations.blc_ext_wish_list = window.ct_localizations.blc_ext_wish_list || {
					user_logged_in: %s,
					list: %s
				};',
				wp_json_encode( admin_url( 'admin-ajax.php' ) ),
				wp_json_encode( is_user_logged_in() ? 'yes' : 'no' ),
				wp_json_encode( $wishlist_data )
			),
			'before'
		);
	}
}

// Initialize the enhancement
new WooCommerce_Product_Image_Block_Enhancement();

