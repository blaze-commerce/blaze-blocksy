<?php
/**
 * Klaviyo Star Ratings Integration
 *
 * Adds Klaviyo star ratings to product cards and product pages with a customizer toggle.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Klaviyo_Star_Ratings
 *
 * Handles the implementation of Klaviyo star ratings on product cards and product pages
 */
class Klaviyo_Star_Ratings {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Add Blocksy toggle switch to WooCommerce General section
		add_filter( 'blocksy_customizer_options:woocommerce:general:end', array( $this, 'add_blocksy_toggle_option' ), 60 );

		// Add star ratings to product cards and product pages
		add_action( 'init', array( $this, 'init_star_ratings' ) );
	}

	/**
	 * Check if WooCommerce is active and available
	 *
	 * @return bool
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Initialize star ratings hooks
	 */
	public function init_star_ratings() {
		// Check if WooCommerce is active
		if ( ! $this->is_woocommerce_active() ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Klaviyo Star Ratings: WooCommerce is not active. Star ratings will not be displayed.' );
			}
			return;
		}

		// Check if star ratings are enabled
		if ( ! $this->is_star_ratings_enabled() ) {
			return;
		}

		// Add star ratings to product cards (shop/archive pages)
		add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'display_star_rating_on_card' ), 5 );

		// Add star ratings to single product pages
		add_action( 'woocommerce_single_product_summary', array( $this, 'display_star_rating_on_product' ), 6 );
	}

	/**
	 * Check if star ratings are enabled in customizer
	 *
	 * @return bool
	 */
	private function is_star_ratings_enabled() {
		// Verify get_theme_mod function exists
		if ( ! function_exists( 'get_theme_mod' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Klaviyo Star Ratings: get_theme_mod function does not exist.' );
			}
			return false;
		}

		// Blocksy ct-switch uses 'yes'/'no' values
		$value = get_theme_mod( 'blocksy_child_enable_klaviyo_star_ratings', 'yes' );
		return ( 'yes' === $value );
	}

	/**
	 * Display star rating widget on product cards
	 */
	public function display_star_rating_on_card() {
		// Verify WooCommerce is active
		if ( ! $this->is_woocommerce_active() ) {
			return;
		}

		global $product;

		// Validate product object exists and is a WC_Product instance
		if ( ! isset( $product ) || ! is_object( $product ) || ! is_a( $product, 'WC_Product' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Klaviyo Star Ratings: Invalid product object in display_star_rating_on_card.' );
			}
			return;
		}

		// Verify get_id method exists
		if ( ! method_exists( $product, 'get_id' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Klaviyo Star Ratings: Product object does not have get_id method.' );
			}
			return;
		}

		$product_id = $product->get_id();

		// Validate product ID
		if ( empty( $product_id ) || ! is_numeric( $product_id ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Klaviyo Star Ratings: Invalid product ID: ' . var_export( $product_id, true ) );
			}
			return;
		}

		echo '<div class="klaviyo-star-rating-widget" data-id="' . esc_attr( $product_id ) . '"></div>';
	}

	/**
	 * Display star rating widget on single product page
	 */
	public function display_star_rating_on_product() {
		// Verify WooCommerce is active
		if ( ! $this->is_woocommerce_active() ) {
			return;
		}

		global $product;

		// Validate product object exists and is a WC_Product instance
		if ( ! isset( $product ) || ! is_object( $product ) || ! is_a( $product, 'WC_Product' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Klaviyo Star Ratings: Invalid product object in display_star_rating_on_product.' );
			}
			return;
		}

		// Verify get_id method exists
		if ( ! method_exists( $product, 'get_id' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Klaviyo Star Ratings: Product object does not have get_id method.' );
			}
			return;
		}

		$product_id = $product->get_id();

		// Validate product ID
		if ( empty( $product_id ) || ! is_numeric( $product_id ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Klaviyo Star Ratings: Invalid product ID: ' . var_export( $product_id, true ) );
			}
			return;
		}

		echo '<div class="klaviyo-star-rating-widget" data-id="' . esc_attr( $product_id ) . '"></div>';
	}

	/**
	 * Add Blocksy-styled toggle switch to WooCommerce General section
	 *
	 * @param array $options Existing Blocksy options array
	 * @return array Modified options array with our custom toggle
	 */
	public function add_blocksy_toggle_option( $options ) {
		// Add our custom toggle switch using Blocksy's ct-switch control type
		$options['blocksy_child_enable_klaviyo_star_ratings'] = array(
			'label' => __( 'Enable Klaviyo Star Ratings', 'blocksy-child' ),
			'type'  => 'ct-switch',
			'value' => 'yes', // Default: enabled (Blocksy uses 'yes'/'no' instead of true/false)
			'desc'  => __( 'Display Klaviyo star ratings on product cards and product pages. When disabled, star ratings will not be shown.', 'blocksy-child' ),
			'setting' => array(
				'transport' => 'refresh', // Refresh page when toggle changes
			),
		);

		return $options;
	}
}

// Initialize the class only if WordPress is loaded
if ( defined( 'ABSPATH' ) && function_exists( 'add_action' ) ) {
	new Klaviyo_Star_Ratings();
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( 'Klaviyo Star Ratings: WordPress environment not properly loaded. Class not initialized.' );
}

