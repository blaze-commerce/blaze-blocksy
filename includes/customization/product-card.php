<?php
/**
 * WooCommerce Product Card Customization
 *
 * This file implements a dynamic option for WooCommerce product cards
 * that integrates with Blocksy's customizer architecture.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WooCommerce_Product_Card
 *
 * Handles the implementation of customizable product card
 */
class WooCommerce_Product_Card {

	/**
	 * Constructor
	 */
	public function __construct() {
		do_action( 'qm/info', 'test' );
		// Add dynamic CSS
		add_action( 'wp_head', array( $this, 'inject_border_styles' ), 100 );

		// Add live preview support
		add_action( 'customize_preview_init', array( $this, 'customize_preview_js' ) );
		add_filter( 'blocksy_woo_card_options:additional_options', array( $this, 'add_border_option' ) );
	}

	/**
	 * Add border option to Blocksy's card options
	 *
	 * @param array $options
	 * @return array
	 */
	public function add_border_option( $options ) {
		$options['woo_card_border'] = array(
			'label' => __( 'Product Card Border', 'blocksy' ),
			'type' => 'ct-border',
			'design' => 'block',
			'sync' => 'live',
			'responsive' => true,
			'divider' => 'bottom',
			'value' => array(
				'width' => 1,
				'style' => 'none',
				'color' => array(
					'color' => 'rgba(0, 0, 0, 0.1)',
				),
			),
		);

		return $options;
	}

	/**
	 * Sanitize border setting
	 *
	 * @param mixed $value
	 * @return array
	 */
	public function sanitize_border_setting( $value ) {
		if ( ! is_array( $value ) ) {
			return array(
				'width' => 1,
				'style' => 'none',
				'color' => array( 'color' => 'rgba(0, 0, 0, 0.1)' ),
			);
		}

		$sanitized = array();
		$sanitized['width'] = isset( $value['width'] ) ? absint( $value['width'] ) : 1;
		$sanitized['style'] = isset( $value['style'] ) ? $this->sanitize_border_style( $value['style'] ) : 'none';

		if ( isset( $value['color'] ) && is_array( $value['color'] ) && isset( $value['color']['color'] ) ) {
			$sanitized['color'] = array( 'color' => sanitize_hex_color( $value['color']['color'] ) );
		} else {
			$sanitized['color'] = array( 'color' => 'rgba(0, 0, 0, 0.1)' );
		}

		return $sanitized;
	}

	/**
	 * Sanitize border style
	 *
	 * @param string $style
	 * @return string
	 */
	public function sanitize_border_style( $style ) {
		$allowed_styles = array( 'none', 'solid', 'dashed', 'dotted', 'double' );
		return in_array( $style, $allowed_styles ) ? $style : 'none';
	}

	/**
	 * Inject border styles into wp_head
	 */
	public function inject_border_styles() {
		// Only add styles on shop/archive pages
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_product_taxonomy() ) {
			return;
		}

		$border_settings = $this->get_border_settings();

		// Don't output CSS if border style is 'none'
		if ( $border_settings['style'] === 'none' ) {
			return;
		}

		$css = $this->generate_border_css( $border_settings );

		if ( ! empty( $css ) ) {
			echo '<style id="custom-woo-card-border">' . $css . '</style>';
		}
	}

	/**
	 * Get border settings from theme mods
	 *
	 * @return array
	 */
	private function get_border_settings() {
		// Try to get Blocksy theme mod first
		if ( function_exists( 'blocksy_get_theme_mod' ) ) {
			return blocksy_get_theme_mod( 'woo_card_border', array(
				'width' => 1,
				'style' => 'none',
				'color' => array( 'color' => 'rgba(0, 0, 0, 0.1)' ),
			) );
		}

		// Fallback to individual settings
		return array(
			'width' => get_theme_mod( 'woo_card_border_width', 1 ),
			'style' => get_theme_mod( 'woo_card_border_style', 'none' ),
			'color' => array( 'color' => get_theme_mod( 'woo_card_border_color', 'rgba(0, 0, 0, 0.1)' ) ),
		);
	}

	/**
	 * Generate CSS for border styles
	 *
	 * @param array $settings
	 * @return string
	 */
	private function generate_border_css( $settings ) {
		$width = absint( $settings['width'] ) . 'px';
		$style = sanitize_text_field( $settings['style'] );
		$color = sanitize_text_field( $settings['color']['color'] );

		// Target all product cards
		$selector = '[data-products] .product';

		$css = "
        /* WooCommerce Product Card Dynamic Border */
        {$selector} {
            --woo-card-border-width: {$width};
            --woo-card-border-style: {$style};
            --woo-card-border-color: {$color};
            border: {$width} {$style} {$color} !important;
        }

        /* Ensure border radius is maintained */
        {$selector} {
            border-radius: 8px;
        }

        /* Hover effects for better UX */
        {$selector}:hover {
            border-color: {$color};
            opacity: 0.9;
            transition: all 0.3s ease;
        }
        ";

		return $css;
	}

	/**
	 * Add live preview JavaScript
	 */
	public function customize_preview_js() {
		wp_enqueue_script(
			'woo-card-border-preview',
			BLAZE_BLOCKSY_URL . '/assets/js/customizer-preview.js',
			array( 'jquery', 'customize-preview' ),
			'1.0.0',
			true
		);
	}
}

// Initialize the class
new WooCommerce_Product_Card();