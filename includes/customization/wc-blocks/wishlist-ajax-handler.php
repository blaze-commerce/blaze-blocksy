<?php
/**
 * Wishlist AJAX Handler for WooCommerce Blocks
 *
 * Handles AJAX requests for wishlist functionality in WooCommerce blocks.
 * Integrates with Blocksy wishlist extension.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Wishlist AJAX Handler Class
 */
class WC_Block_Wishlist_AJAX_Handler {

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
		// AJAX handlers for both logged in and non-logged in users
		add_action( 'wp_ajax_wc_block_toggle_wishlist', array( $this, 'ajax_toggle_wishlist' ) );
		add_action( 'wp_ajax_nopriv_wc_block_toggle_wishlist', array( $this, 'ajax_toggle_wishlist' ) );
	}

	/**
	 * AJAX handler for toggling wishlist
	 */
	public function ajax_toggle_wishlist() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wc_block_image_enhancements_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed', 'blocksy-child' ),
				)
			);
		}

		// Get product ID and action
		$product_id      = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$wishlist_action = isset( $_POST['wishlist_action'] ) ? sanitize_text_field( $_POST['wishlist_action'] ) : 'add';

		if ( ! $product_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid product ID', 'blocksy-child' ),
				)
			);
		}

		// Check if product exists
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error(
				array(
					'message' => __( 'Product not found', 'blocksy-child' ),
				)
			);
		}

		// Try to use Blocksy wishlist functionality
		if ( $this->is_blocksy_wishlist_available() ) {
			$result = $this->toggle_blocksy_wishlist( $product_id, $wishlist_action );
		} else {
			// Fallback to custom wishlist implementation
			$result = $this->toggle_custom_wishlist( $product_id, $wishlist_action );
		}

		if ( $result['success'] ) {
			wp_send_json_success(
				array(
					'message'    => $result['message'],
					'product_id' => $product_id,
					'action'     => $wishlist_action,
					'count'      => $result['count'],
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => $result['message'],
				)
			);
		}
	}

	/**
	 * Check if Blocksy wishlist is available
	 *
	 * @return bool True if Blocksy wishlist is available.
	 */
	private function is_blocksy_wishlist_available() {
		return class_exists( 'BlocksyChildWishlistHelper' ) && BlocksyChildWishlistHelper::is_woocommerce_extra_active();
	}

	/**
	 * Toggle wishlist using Blocksy wishlist functionality
	 *
	 * @param int    $product_id Product ID.
	 * @param string $action Action to perform (add or remove).
	 * @return array Result array with success status and message.
	 */
	private function toggle_blocksy_wishlist( $product_id, $action ) {
		$wishlist_ext = BlocksyChildWishlistHelper::get_wishlist_extension();
		
		if ( ! $wishlist_ext ) {
			return array(
				'success' => false,
				'message' => __( 'Wishlist functionality is not available', 'blocksy-child' ),
			);
		}

		try {
			if ( $action === 'add' ) {
				// Add to wishlist
				$wishlist_ext->add_to_wishlist( $product_id );
				$message = __( 'Added to wishlist', 'blocksy-child' );
			} else {
				// Remove from wishlist
				$wishlist_ext->remove_from_wishlist( $product_id );
				$message = __( 'Removed from wishlist', 'blocksy-child' );
			}

			$count = BlocksyChildWishlistHelper::get_wishlist_count();

			return array(
				'success' => true,
				'message' => $message,
				'count'   => $count,
			);
		} catch ( Exception $e ) {
			return array(
				'success' => false,
				'message' => __( 'Error updating wishlist', 'blocksy-child' ),
			);
		}
	}

	/**
	 * Toggle wishlist using custom implementation (fallback)
	 *
	 * @param int    $product_id Product ID.
	 * @param string $action Action to perform (add or remove).
	 * @return array Result array with success status and message.
	 */
	private function toggle_custom_wishlist( $product_id, $action ) {
		// Get current wishlist from cookie/session
		$wishlist = $this->get_custom_wishlist();

		if ( $action === 'add' ) {
			if ( ! in_array( $product_id, $wishlist, true ) ) {
				$wishlist[] = $product_id;
				$message    = __( 'Added to wishlist', 'blocksy-child' );
			} else {
				$message = __( 'Already in wishlist', 'blocksy-child' );
			}
		} else {
			$key = array_search( $product_id, $wishlist, true );
			if ( $key !== false ) {
				unset( $wishlist[ $key ] );
				$message = __( 'Removed from wishlist', 'blocksy-child' );
			} else {
				$message = __( 'Not in wishlist', 'blocksy-child' );
			}
		}

		// Save wishlist
		$this->save_custom_wishlist( $wishlist );

		return array(
			'success' => true,
			'message' => $message,
			'count'   => count( $wishlist ),
		);
	}

	/**
	 * Get custom wishlist from cookie
	 *
	 * @return array Wishlist product IDs.
	 */
	private function get_custom_wishlist() {
		$wishlist = array();

		if ( isset( $_COOKIE['wc_block_wishlist'] ) ) {
			$wishlist_data = json_decode( stripslashes( $_COOKIE['wc_block_wishlist'] ), true );
			if ( is_array( $wishlist_data ) ) {
				$wishlist = array_map( 'absint', $wishlist_data );
			}
		}

		return $wishlist;
	}

	/**
	 * Save custom wishlist to cookie
	 *
	 * @param array $wishlist Wishlist product IDs.
	 */
	private function save_custom_wishlist( $wishlist ) {
		$wishlist = array_values( array_unique( array_filter( $wishlist ) ) );
		
		// Set cookie for 30 days
		setcookie(
			'wc_block_wishlist',
			wp_json_encode( $wishlist ),
			time() + ( 30 * DAY_IN_SECONDS ),
			COOKIEPATH,
			COOKIE_DOMAIN,
			is_ssl(),
			true
		);
	}
}

// Initialize the AJAX handler
new WC_Block_Wishlist_AJAX_Handler();

