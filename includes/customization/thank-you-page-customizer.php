<?php
/**
 * Thank You Page Customizer Integration
 *
 * Adds a toggle option to enable/disable the custom Blaze Commerce thank you page
 * in the Blocksy WooCommerce settings section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Blocksy_Child_Thank_You_Page_Customizer {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Add Blocksy toggle switch to WooCommerce General section
		add_filter( 'blocksy_customizer_options:woocommerce:general:end', array( $this, 'add_blocksy_toggle_option' ), 60 );

		// Keep preview scripts for live preview functionality
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );
	}

	/**
	 * Add Blocksy-styled toggle switch to WooCommerce General section
	 *
	 * @param array $options Existing Blocksy options array
	 * @return array Modified options array with our custom toggle
	 */
	public function add_blocksy_toggle_option( $options ) {
		// Add our custom toggle switch using Blocksy's ct-switch control type
		$options['blocksy_child_enable_custom_thank_you_page'] = array(
			'label' => __( 'Enable Custom Thank You Page', 'blocksy-child' ),
			'type'  => 'ct-switch',
			'value' => 'yes', // Default: enabled (Blocksy uses 'yes'/'no' instead of true/false)
			'desc'  => __( 'Enable the custom Blaze Commerce thank you page design. When disabled, the default WooCommerce thank you page will be used.', 'blocksy-child' ),
			'setting' => array(
				'transport' => 'refresh', // Refresh page when toggle changes
			),
		);

		return $options;
	}

	/**
	 * Enqueue preview scripts for live preview
	 */
	public function enqueue_preview_scripts() {
		wp_enqueue_script(
			'blocksy-thank-you-customizer-preview',
			get_stylesheet_directory_uri() . '/assets/js/thank-you-customizer-preview.js',
			array( 'jquery', 'customize-preview' ),
			'1.0.0',
			true
		);
	}

	/**
	 * Check if custom thank you page is enabled
	 *
	 * @return bool True if enabled, false otherwise
	 */
	public static function is_custom_thank_you_page_enabled() {
		// Blocksy ct-switch uses 'yes'/'no' values
		$value = get_theme_mod( 'blocksy_child_enable_custom_thank_you_page', 'yes' );
		return ( 'yes' === $value );
	}
}

// Initialize the customizer integration
new Blocksy_Child_Thank_You_Page_Customizer();

/**
 * Helper function to check if custom thank you page is enabled
 * This can be used throughout the theme
 */
function blocksy_child_is_custom_thank_you_page_enabled() {
	return Blocksy_Child_Thank_You_Page_Customizer::is_custom_thank_you_page_enabled();
}
