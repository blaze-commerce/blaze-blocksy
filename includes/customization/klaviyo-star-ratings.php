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
		// Register customizer hooks
		add_action( 'customize_register', array( $this, 'register_customizer_settings' ) );

		// Add star ratings to product cards and product pages
		add_action( 'init', array( $this, 'init_star_ratings' ) );
	}

	/**
	 * Initialize star ratings hooks
	 */
	public function init_star_ratings() {
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
		return get_theme_mod( 'blocksy_child_enable_klaviyo_star_ratings', true );
	}

	/**
	 * Display star rating widget on product cards
	 */
	public function display_star_rating_on_card() {
		global $product;

		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		$product_id = $product->get_id();
		echo '<div class="klaviyo-star-rating-widget" data-id="' . esc_attr( $product_id ) . '"></div>';
	}

	/**
	 * Display star rating widget on single product page
	 */
	public function display_star_rating_on_product() {
		global $product;

		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		$product_id = $product->get_id();
		echo '<div class="klaviyo-star-rating-widget" data-id="' . esc_attr( $product_id ) . '"></div>';
	}

	/**
	 * Register customizer settings for Klaviyo star ratings toggle
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function register_customizer_settings( $wp_customize ) {
		// Add setting for star ratings toggle
		$wp_customize->add_setting(
			'blocksy_child_enable_klaviyo_star_ratings',
			array(
				'default'           => true, // Enabled by default
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'transport'         => 'refresh', // Use refresh to ensure proper functionality
			)
		);

		// Add control for star ratings toggle
		$wp_customize->add_control(
			'blocksy_child_enable_klaviyo_star_ratings',
			array(
				'label'       => __( 'Enable Klaviyo Star Ratings', 'blocksy-child' ),
				'description' => __( 'Display Klaviyo star ratings on product cards and product pages. When disabled, star ratings will not be shown.', 'blocksy-child' ),
				'section'     => 'woocommerce_product_catalog', // Add to WooCommerce Product Catalog section
				'type'        => 'checkbox',
				'priority'    => 999, // Place at the end of the section
			)
		);
	}

	/**
	 * Sanitize checkbox input
	 *
	 * @param mixed $checked
	 * @return bool
	 */
	public function sanitize_checkbox( $checked ) {
		return ( ( isset( $checked ) && true === $checked ) ? true : false );
	}
}

// Initialize the class
new Klaviyo_Star_Ratings();

