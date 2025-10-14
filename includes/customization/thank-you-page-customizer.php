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
		// Register customizer hooks
		add_action( 'customize_register', array( $this, 'register_customizer_settings' ) );
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );
		
		// Add selective refresh support
		add_action( 'customize_register', array( $this, 'add_selective_refresh' ) );
	}

	/**
	 * Register customizer settings for thank you page toggle
	 */
	public function register_customizer_settings( $wp_customize ) {
		// Add setting for thank you page toggle
		$wp_customize->add_setting(
			'blocksy_child_enable_custom_thank_you_page',
			array(
				'default'           => true, // Enabled by default since it's already implemented
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'transport'         => 'refresh', // Use refresh to ensure proper functionality
			)
		);

		// Add control for thank you page toggle
		$wp_customize->add_control(
			'blocksy_child_enable_custom_thank_you_page',
			array(
				'label'       => __( 'Enable Custom Thank You Page', 'blocksy-child' ),
				'description' => __( 'Enable the custom Blaze Commerce thank you page design. When disabled, the default WooCommerce thank you page will be used.', 'blocksy-child' ),
				'section'     => 'woocommerce_general', // Add to existing WooCommerce General section
				'type'        => 'checkbox',
				'priority'    => 999, // Place at the end of the section
			)
		);
	}

	/**
	 * Sanitize checkbox input
	 */
	public function sanitize_checkbox( $checked ) {
		return ( ( isset( $checked ) && true === $checked ) ? true : false );
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
	 * Add selective refresh support
	 */
	public function add_selective_refresh( $wp_customize ) {
		if ( ! isset( $wp_customize->selective_refresh ) ) {
			return;
		}

		// Add selective refresh for thank you page changes
		$wp_customize->selective_refresh->add_partial(
			'blocksy_child_enable_custom_thank_you_page',
			array(
				'selector'        => '.woocommerce-order',
				'render_callback' => array( $this, 'render_thank_you_page' ),
			)
		);
	}

	/**
	 * Render callback for selective refresh
	 */
	public function render_thank_you_page() {
		// This will be called during selective refresh
		// Return empty string as the actual rendering is handled by the main thank you page functions
		return '';
	}

	/**
	 * Check if custom thank you page is enabled
	 */
	public static function is_custom_thank_you_page_enabled() {
		return get_theme_mod( 'blocksy_child_enable_custom_thank_you_page', true );
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
