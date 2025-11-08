<?php
/**
 * Fluid Checkout Customizer Integration
 *
 * Adds comprehensive styling options for Fluid Checkout elements
 * in the Blocksy Customizer for live preview and customization.
 *
 * @package Blocksy_Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Blocksy_Child_Fluid_Checkout_Customizer {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register customizer hooks
		add_action( 'customize_register', array( $this, 'register_customizer_settings' ) );
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );
		add_action( 'wp_head', array( $this, 'output_customizer_css' ), 999 );
	}

	/**
	 * Register all customizer settings, controls, and sections
	 */
	public function register_customizer_settings( $wp_customize ) {
		// Add Fluid Checkout Panel
		$wp_customize->add_panel(
			'blocksy_fluid_checkout_panel',
			array(
				'title'       => __( 'Fluid Checkout Styling', 'blocksy-child' ),
				'description' => __( 'Customize the appearance of Fluid Checkout elements with advanced typography, colors, spacing, and border controls.', 'blocksy-child' ),
				'priority'    => 165,
				'capability'  => 'edit_theme_options',
			)
		);

		// Register sections
		$this->register_general_colors_section( $wp_customize );
		$this->register_typography_sections( $wp_customize );
		$this->register_form_elements_section( $wp_customize );
		$this->register_buttons_section( $wp_customize );
		$this->register_spacing_section( $wp_customize );
		$this->register_borders_section( $wp_customize );
	}

	/**
	 * Register General Colors Section
	 */
	private function register_general_colors_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_fc_general_colors',
			array(
				'title'    => __( 'General Colors', 'blocksy-child' ),
				'panel'    => 'blocksy_fluid_checkout_panel',
				'priority' => 10,
			)
		);

		$color_settings = array(
			'primary_color'           => array(
				'label'   => __( 'Primary Color', 'blocksy-child' ),
				'default' => '#0047e3',
			),
			'secondary_color'         => array(
				'label'   => __( 'Secondary Color', 'blocksy-child' ),
				'default' => '#fed766',
			),
			'body_text_color'         => array(
				'label'   => __( 'Body Text Color', 'blocksy-child' ),
				'default' => '#394859',
			),
			'heading_color'           => array(
				'label'   => __( 'Heading Color', 'blocksy-child' ),
				'default' => '#394859',
			),
			'link_color'              => array(
				'label'   => __( 'Link Color', 'blocksy-child' ),
				'default' => '#00277a',
			),
			'link_hover_color'        => array(
				'label'   => __( 'Link Hover Color', 'blocksy-child' ),
				'default' => '#5c8fff',
			),
			'content_background'      => array(
				'label'   => __( 'Content Background', 'blocksy-child' ),
				'default' => '#ffffff',
			),
			'border_color'            => array(
				'label'   => __( 'Border Color', 'blocksy-child' ),
				'default' => '#dfdfde',
			),
		);

		foreach ( $color_settings as $key => $config ) {
			$wp_customize->add_setting(
				"blocksy_fc_{$key}",
				array(
					'default'           => $config['default'],
					'sanitize_callback' => 'sanitize_hex_color',
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					"blocksy_fc_{$key}",
					array(
						'label'   => $config['label'],
						'section' => 'blocksy_fc_general_colors',
					)
				)
			);
		}
	}

	/**
	 * Register Typography Sections
	 */
	private function register_typography_sections( $wp_customize ) {
		$typography_elements = array(
			'heading'     => array(
				'title'    => __( 'Heading Typography', 'blocksy-child' ),
				'priority' => 20,
			),
			'body'        => array(
				'title'    => __( 'Body Text Typography', 'blocksy-child' ),
				'priority' => 30,
			),
			'label'       => array(
				'title'    => __( 'Form Label Typography', 'blocksy-child' ),
				'priority' => 40,
			),
			'placeholder' => array(
				'title'    => __( 'Placeholder Typography', 'blocksy-child' ),
				'priority' => 50,
			),
			'button'      => array(
				'title'    => __( 'Button Typography', 'blocksy-child' ),
				'priority' => 60,
			),
		);

		foreach ( $typography_elements as $element => $config ) {
			$section_id = "blocksy_fc_{$element}_typography";

			$wp_customize->add_section(
				$section_id,
				array(
					'title'    => $config['title'],
					'panel'    => 'blocksy_fluid_checkout_panel',
					'priority' => $config['priority'],
				)
			);

			$this->register_typography_controls( $wp_customize, $element, $section_id );
		}
	}

	/**
	 * Register typography controls for a specific element
	 */
	private function register_typography_controls( $wp_customize, $element, $section ) {
		$defaults = $this->get_typography_defaults( $element );

		// Font Family
		$wp_customize->add_setting(
			"blocksy_fc_{$element}_font_family",
			array(
				'default'           => $defaults['font'],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			"blocksy_fc_{$element}_font_family",
			array(
				'label'       => __( 'Font Family', 'blocksy-child' ),
				'description' => __( 'Enter a font family (e.g., Arial, sans-serif)', 'blocksy-child' ),
				'section'     => $section,
				'type'        => 'text',
			)
		);

		// Font Size
		$wp_customize->add_setting(
			"blocksy_fc_{$element}_font_size",
			array(
				'default'           => $defaults['size'],
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			"blocksy_fc_{$element}_font_size",
			array(
				'label'       => __( 'Font Size', 'blocksy-child' ),
				'description' => __( 'Enter size with CSS unit (e.g., 16px, 1rem)', 'blocksy-child' ),
				'section'     => $section,
				'type'        => 'text',
			)
		);

		// Font Color
		$wp_customize->add_setting(
			"blocksy_fc_{$element}_font_color",
			array(
				'default'           => $defaults['color'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				"blocksy_fc_{$element}_font_color",
				array(
					'label'   => __( 'Font Color', 'blocksy-child' ),
					'section' => $section,
				)
			)
		);

		// Font Weight
		$wp_customize->add_setting(
			"blocksy_fc_{$element}_font_weight",
			array(
				'default'           => $defaults['weight'],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			"blocksy_fc_{$element}_font_weight",
			array(
				'label'   => __( 'Font Weight', 'blocksy-child' ),
				'section' => $section,
				'type'    => 'select',
				'choices' => array(
					'100' => __( 'Thin (100)', 'blocksy-child' ),
					'200' => __( 'Extra Light (200)', 'blocksy-child' ),
					'300' => __( 'Light (300)', 'blocksy-child' ),
					'400' => __( 'Normal (400)', 'blocksy-child' ),
					'500' => __( 'Medium (500)', 'blocksy-child' ),
					'600' => __( 'Semi Bold (600)', 'blocksy-child' ),
					'700' => __( 'Bold (700)', 'blocksy-child' ),
					'800' => __( 'Extra Bold (800)', 'blocksy-child' ),
					'900' => __( 'Black (900)', 'blocksy-child' ),
				),
			)
		);
	}

	/**
	 * Get typography defaults for different elements
	 */
	private function get_typography_defaults( $element ) {
		$defaults = array(
			'heading'     => array(
				'font'   => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				'size'   => '24px',
				'color'  => '#394859',
				'weight' => '600',
			),
			'body'        => array(
				'font'   => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				'size'   => '16px',
				'color'  => '#394859',
				'weight' => '400',
			),
			'label'       => array(
				'font'   => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				'size'   => '14px',
				'color'  => '#394859',
				'weight' => '500',
			),
			'placeholder' => array(
				'font'   => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				'size'   => '14px',
				'color'  => '#8C949c',
				'weight' => '400',
			),
			'button'      => array(
				'font'   => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				'size'   => '16px',
				'color'  => '#ffffff',
				'weight' => '600',
			),
		);

		return isset( $defaults[ $element ] ) ? $defaults[ $element ] : $defaults['body'];
	}

	/**
	 * Sanitize CSS unit values
	 */
	public function sanitize_css_unit( $input ) {
		$sanitized = preg_replace( '/[^0-9a-zA-Z%.\s-]/', '', $input );

		if ( ! empty( $sanitized ) && ! preg_match( '/(px|em|rem|%|vh|vw|pt|pc|in|cm|mm|ex|ch)$/', $sanitized ) ) {
			$sanitized .= 'px';
		}

		return $sanitized;
	}

	/**
	 * Register Form Elements Section
	 */
	private function register_form_elements_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_fc_form_elements',
			array(
				'title'    => __( 'Form Elements', 'blocksy-child' ),
				'panel'    => 'blocksy_fluid_checkout_panel',
				'priority' => 70,
			)
		);

		$form_settings = array(
			'input_background'    => array(
				'label'   => __( 'Input Background Color', 'blocksy-child' ),
				'default' => '#ffffff',
				'type'    => 'color',
			),
			'input_border_color'  => array(
				'label'   => __( 'Input Border Color', 'blocksy-child' ),
				'default' => '#dfdfde',
				'type'    => 'color',
			),
			'input_text_color'    => array(
				'label'   => __( 'Input Text Color', 'blocksy-child' ),
				'default' => '#394859',
				'type'    => 'color',
			),
			'input_focus_border'  => array(
				'label'   => __( 'Input Focus Border Color', 'blocksy-child' ),
				'default' => '#0047e3',
				'type'    => 'color',
			),
			'input_padding'       => array(
				'label'       => __( 'Input Padding', 'blocksy-child' ),
				'description' => __( 'Enter padding with CSS unit (e.g., 12px, 1rem)', 'blocksy-child' ),
				'default'     => '12px',
				'type'        => 'text',
			),
			'input_border_radius' => array(
				'label'       => __( 'Input Border Radius', 'blocksy-child' ),
				'description' => __( 'Enter border radius with CSS unit (e.g., 4px, 0.5rem)', 'blocksy-child' ),
				'default'     => '4px',
				'type'        => 'text',
			),
		);

		foreach ( $form_settings as $key => $config ) {
			$sanitize_callback = ( $config['type'] === 'color' ) ? 'sanitize_hex_color' : array( $this, 'sanitize_css_unit' );

			$wp_customize->add_setting(
				"blocksy_fc_{$key}",
				array(
					'default'           => $config['default'],
					'sanitize_callback' => $sanitize_callback,
					'transport'         => 'postMessage',
				)
			);

			if ( $config['type'] === 'color' ) {
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
						$wp_customize,
						"blocksy_fc_{$key}",
						array(
							'label'   => $config['label'],
							'section' => 'blocksy_fc_form_elements',
						)
					)
				);
			} else {
				$wp_customize->add_control(
					"blocksy_fc_{$key}",
					array(
						'label'       => $config['label'],
						'description' => isset( $config['description'] ) ? $config['description'] : '',
						'section'     => 'blocksy_fc_form_elements',
						'type'        => 'text',
					)
				);
			}
		}
	}

	/**
	 * Register Buttons Section
	 */
	private function register_buttons_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_fc_buttons',
			array(
				'title'    => __( 'Buttons', 'blocksy-child' ),
				'panel'    => 'blocksy_fluid_checkout_panel',
				'priority' => 80,
			)
		);

		$button_settings = array(
			'button_primary_bg'         => array(
				'label'   => __( 'Primary Button Background', 'blocksy-child' ),
				'default' => '#0047e3',
				'type'    => 'color',
			),
			'button_primary_text'       => array(
				'label'   => __( 'Primary Button Text', 'blocksy-child' ),
				'default' => '#ffffff',
				'type'    => 'color',
			),
			'button_primary_hover_bg'   => array(
				'label'   => __( 'Primary Button Hover Background', 'blocksy-child' ),
				'default' => '#00277a',
				'type'    => 'color',
			),
			'button_primary_hover_text' => array(
				'label'   => __( 'Primary Button Hover Text', 'blocksy-child' ),
				'default' => '#ffffff',
				'type'    => 'color',
			),
			'button_padding_top'        => array(
				'label'       => __( 'Button Padding Top', 'blocksy-child' ),
				'description' => __( 'Enter padding with CSS unit (e.g., 12px, 1rem)', 'blocksy-child' ),
				'default'     => '12px',
				'type'        => 'text',
			),
			'button_padding_right'      => array(
				'label'       => __( 'Button Padding Right', 'blocksy-child' ),
				'description' => __( 'Enter padding with CSS unit (e.g., 24px, 1.5rem)', 'blocksy-child' ),
				'default'     => '24px',
				'type'        => 'text',
			),
			'button_padding_bottom'     => array(
				'label'       => __( 'Button Padding Bottom', 'blocksy-child' ),
				'description' => __( 'Enter padding with CSS unit (e.g., 12px, 1rem)', 'blocksy-child' ),
				'default'     => '12px',
				'type'        => 'text',
			),
			'button_padding_left'       => array(
				'label'       => __( 'Button Padding Left', 'blocksy-child' ),
				'description' => __( 'Enter padding with CSS unit (e.g., 24px, 1.5rem)', 'blocksy-child' ),
				'default'     => '24px',
				'type'        => 'text',
			),
			'button_border_radius'      => array(
				'label'       => __( 'Button Border Radius', 'blocksy-child' ),
				'description' => __( 'Enter border radius with CSS unit (e.g., 4px, 0.5rem)', 'blocksy-child' ),
				'default'     => '4px',
				'type'        => 'text',
			),
		);

		foreach ( $button_settings as $key => $config ) {
			$sanitize_callback = ( $config['type'] === 'color' ) ? 'sanitize_hex_color' : array( $this, 'sanitize_css_unit' );

			$wp_customize->add_setting(
				"blocksy_fc_{$key}",
				array(
					'default'           => $config['default'],
					'sanitize_callback' => $sanitize_callback,
					'transport'         => 'postMessage',
				)
			);

			if ( $config['type'] === 'color' ) {
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
						$wp_customize,
						"blocksy_fc_{$key}",
						array(
							'label'   => $config['label'],
							'section' => 'blocksy_fc_buttons',
						)
					)
				);
			} else {
				$wp_customize->add_control(
					"blocksy_fc_{$key}",
					array(
						'label'       => $config['label'],
						'description' => isset( $config['description'] ) ? $config['description'] : '',
						'section'     => 'blocksy_fc_buttons',
						'type'        => 'text',
					)
				);
			}
		}
	}

	/**
	 * Register Spacing Section
	 */
	private function register_spacing_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_fc_spacing',
			array(
				'title'    => __( 'Spacing', 'blocksy-child' ),
				'panel'    => 'blocksy_fluid_checkout_panel',
				'priority' => 90,
			)
		);

		$spacing_settings = array(
			'section_padding_top'    => array(
				'label'       => __( 'Section Padding Top', 'blocksy-child' ),
				'description' => __( 'Padding for checkout sections (e.g., 20px, 1.5rem)', 'blocksy-child' ),
				'default'     => '20px',
			),
			'section_padding_right'  => array(
				'label'       => __( 'Section Padding Right', 'blocksy-child' ),
				'description' => __( 'Padding for checkout sections (e.g., 20px, 1.5rem)', 'blocksy-child' ),
				'default'     => '20px',
			),
			'section_padding_bottom' => array(
				'label'       => __( 'Section Padding Bottom', 'blocksy-child' ),
				'description' => __( 'Padding for checkout sections (e.g., 20px, 1.5rem)', 'blocksy-child' ),
				'default'     => '20px',
			),
			'section_padding_left'   => array(
				'label'       => __( 'Section Padding Left', 'blocksy-child' ),
				'description' => __( 'Padding for checkout sections (e.g., 20px, 1.5rem)', 'blocksy-child' ),
				'default'     => '20px',
			),
			'section_margin_bottom'  => array(
				'label'       => __( 'Section Margin Bottom', 'blocksy-child' ),
				'description' => __( 'Space between checkout sections (e.g., 20px, 1.5rem)', 'blocksy-child' ),
				'default'     => '20px',
			),
			'field_gap'              => array(
				'label'       => __( 'Field Gap', 'blocksy-child' ),
				'description' => __( 'Space between form fields (e.g., 15px, 1rem)', 'blocksy-child' ),
				'default'     => '15px',
			),
		);

		foreach ( $spacing_settings as $key => $config ) {
			$wp_customize->add_setting(
				"blocksy_fc_{$key}",
				array(
					'default'           => $config['default'],
					'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				"blocksy_fc_{$key}",
				array(
					'label'       => $config['label'],
					'description' => $config['description'],
					'section'     => 'blocksy_fc_spacing',
					'type'        => 'text',
				)
			);
		}
	}

	/**
	 * Register Borders Section
	 */
	private function register_borders_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_fc_borders',
			array(
				'title'    => __( 'Borders', 'blocksy-child' ),
				'panel'    => 'blocksy_fluid_checkout_panel',
				'priority' => 100,
			)
		);

		// Border Width
		$wp_customize->add_setting(
			'blocksy_fc_section_border_width',
			array(
				'default'           => '1px',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_section_border_width',
			array(
				'label'       => __( 'Section Border Width', 'blocksy-child' ),
				'description' => __( 'Border width for checkout sections (e.g., 1px, 2px)', 'blocksy-child' ),
				'section'     => 'blocksy_fc_borders',
				'type'        => 'text',
			)
		);

		// Border Color
		$wp_customize->add_setting(
			'blocksy_fc_section_border_color',
			array(
				'default'           => '#dfdfde',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_section_border_color',
				array(
					'label'   => __( 'Section Border Color', 'blocksy-child' ),
					'section' => 'blocksy_fc_borders',
				)
			)
		);

		// Border Style
		$wp_customize->add_setting(
			'blocksy_fc_section_border_style',
			array(
				'default'           => 'solid',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_section_border_style',
			array(
				'label'   => __( 'Section Border Style', 'blocksy-child' ),
				'section' => 'blocksy_fc_borders',
				'type'    => 'select',
				'choices' => array(
					'none'   => __( 'None', 'blocksy-child' ),
					'solid'  => __( 'Solid', 'blocksy-child' ),
					'dashed' => __( 'Dashed', 'blocksy-child' ),
					'dotted' => __( 'Dotted', 'blocksy-child' ),
					'double' => __( 'Double', 'blocksy-child' ),
				),
			)
		);

		// Border Radius
		$wp_customize->add_setting(
			'blocksy_fc_section_border_radius',
			array(
				'default'           => '8px',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_section_border_radius',
			array(
				'label'       => __( 'Section Border Radius', 'blocksy-child' ),
				'description' => __( 'Border radius for checkout sections (e.g., 8px, 0.5rem)', 'blocksy-child' ),
				'section'     => 'blocksy_fc_borders',
				'type'        => 'text',
			)
		);
	}

	/**
	 * Output customizer CSS in head
	 */
	public function output_customizer_css() {
		// Only output on checkout pages or if we're in customizer preview
		if ( ! $this->is_checkout_page() && ! is_customize_preview() ) {
			return;
		}

		echo '<style type="text/css" id="blocksy-fluid-checkout-customizer-css">';
		echo '/* Blocksy Child Fluid Checkout - Customizer Styles */';

		// General Colors
		$this->output_color_css_variables();

		// Typography
		$this->output_typography_css();

		// Form Elements
		$this->output_form_elements_css();

		// Buttons
		$this->output_buttons_css();

		// Spacing
		$this->output_spacing_css();

		// Borders
		$this->output_borders_css();

		echo '</style>';
	}

	/**
	 * Check if current page is checkout page
	 */
	private function is_checkout_page() {
		if ( ! function_exists( 'is_checkout' ) ) {
			return false;
		}

		return is_checkout() || is_page( 'checkout' );
	}

	/**
	 * Output color CSS variables
	 */
	private function output_color_css_variables() {
		$colors = array(
			'primary_color'      => '--fluidtheme--color--primary',
			'secondary_color'    => '--fluidtheme--color--secondary',
			'body_text_color'    => '--fluidtheme--color--body-text',
			'heading_color'      => '--fluidtheme--color--heading',
			'link_color'         => '--fluidtheme--color--link',
			'link_hover_color'   => '--fluidtheme--color--link--hover',
			'content_background' => '--fluidtheme--color--content-background',
			'border_color'       => '--fluidtheme--color--border',
		);

		echo ':root {';
		foreach ( $colors as $key => $css_var ) {
			$value = get_theme_mod( "blocksy_fc_{$key}" );
			if ( $value ) {
				echo esc_attr( $css_var ) . ': ' . esc_attr( $value ) . ';';
			}
		}
		echo '}';
	}

	/**
	 * Output typography CSS
	 */
	private function output_typography_css() {
		$elements = array(
			'heading'     => '.woocommerce-checkout h1, .woocommerce-checkout h2, .woocommerce-checkout h3, .fc-step__title',
			'body'        => '.woocommerce-checkout, .woocommerce-checkout p, .woocommerce-checkout span',
			'label'       => '.woocommerce-checkout label, .form-row label',
			'placeholder' => '.woocommerce-checkout input::placeholder, .woocommerce-checkout textarea::placeholder',
			'button'      => '.woocommerce-checkout button, .woocommerce-checkout .button',
		);

		foreach ( $elements as $element => $selector ) {
			$font_family = get_theme_mod( "blocksy_fc_{$element}_font_family" );
			$font_size   = get_theme_mod( "blocksy_fc_{$element}_font_size" );
			$font_color  = get_theme_mod( "blocksy_fc_{$element}_font_color" );
			$font_weight = get_theme_mod( "blocksy_fc_{$element}_font_weight" );

			if ( $font_family || $font_size || $font_color || $font_weight ) {
				echo esc_attr( $selector ) . ' {';
				if ( $font_family ) {
					echo 'font-family: ' . esc_attr( $font_family ) . ' !important;';
				}
				if ( $font_size ) {
					echo 'font-size: ' . esc_attr( $font_size ) . ' !important;';
				}
				if ( $font_color ) {
					echo 'color: ' . esc_attr( $font_color ) . ' !important;';
				}
				if ( $font_weight ) {
					echo 'font-weight: ' . esc_attr( $font_weight ) . ' !important;';
				}
				echo '}';
			}
		}
	}

	/**
	 * Output form elements CSS
	 */
	private function output_form_elements_css() {
		$input_bg           = get_theme_mod( 'blocksy_fc_input_background' );
		$input_border       = get_theme_mod( 'blocksy_fc_input_border_color' );
		$input_text         = get_theme_mod( 'blocksy_fc_input_text_color' );
		$input_focus_border = get_theme_mod( 'blocksy_fc_input_focus_border' );
		$input_padding      = get_theme_mod( 'blocksy_fc_input_padding' );
		$input_radius       = get_theme_mod( 'blocksy_fc_input_border_radius' );

		if ( $input_bg || $input_border || $input_text || $input_padding || $input_radius ) {
			echo '.woocommerce-checkout input[type="text"], ';
			echo '.woocommerce-checkout input[type="email"], ';
			echo '.woocommerce-checkout input[type="tel"], ';
			echo '.woocommerce-checkout input[type="password"], ';
			echo '.woocommerce-checkout textarea, ';
			echo '.woocommerce-checkout select {';
			if ( $input_bg ) {
				echo 'background-color: ' . esc_attr( $input_bg ) . ' !important;';
			}
			if ( $input_border ) {
				echo 'border-color: ' . esc_attr( $input_border ) . ' !important;';
			}
			if ( $input_text ) {
				echo 'color: ' . esc_attr( $input_text ) . ' !important;';
			}
			if ( $input_padding ) {
				echo 'padding: ' . esc_attr( $input_padding ) . ' !important;';
			}
			if ( $input_radius ) {
				echo 'border-radius: ' . esc_attr( $input_radius ) . ' !important;';
			}
			echo '}';
		}

		if ( $input_focus_border ) {
			echo '.woocommerce-checkout input[type="text"]:focus, ';
			echo '.woocommerce-checkout input[type="email"]:focus, ';
			echo '.woocommerce-checkout input[type="tel"]:focus, ';
			echo '.woocommerce-checkout input[type="password"]:focus, ';
			echo '.woocommerce-checkout textarea:focus, ';
			echo '.woocommerce-checkout select:focus {';
			echo 'border-color: ' . esc_attr( $input_focus_border ) . ' !important;';
			echo '}';
		}
	}

	/**
	 * Output buttons CSS
	 */
	private function output_buttons_css() {
		$btn_bg          = get_theme_mod( 'blocksy_fc_button_primary_bg' );
		$btn_text        = get_theme_mod( 'blocksy_fc_button_primary_text' );
		$btn_hover_bg    = get_theme_mod( 'blocksy_fc_button_primary_hover_bg' );
		$btn_hover_text  = get_theme_mod( 'blocksy_fc_button_primary_hover_text' );
		$btn_pad_top     = get_theme_mod( 'blocksy_fc_button_padding_top' );
		$btn_pad_right   = get_theme_mod( 'blocksy_fc_button_padding_right' );
		$btn_pad_bottom  = get_theme_mod( 'blocksy_fc_button_padding_bottom' );
		$btn_pad_left    = get_theme_mod( 'blocksy_fc_button_padding_left' );
		$btn_radius      = get_theme_mod( 'blocksy_fc_button_border_radius' );

		if ( $btn_bg || $btn_text || $btn_pad_top || $btn_radius ) {
			echo '.woocommerce-checkout button.button, ';
			echo '.woocommerce-checkout .button, ';
			echo '.woocommerce-checkout input[type="submit"], ';
			echo '.woocommerce-checkout #place_order {';
			if ( $btn_bg ) {
				echo 'background-color: ' . esc_attr( $btn_bg ) . ' !important;';
			}
			if ( $btn_text ) {
				echo 'color: ' . esc_attr( $btn_text ) . ' !important;';
			}
			if ( $btn_pad_top || $btn_pad_right || $btn_pad_bottom || $btn_pad_left ) {
				$padding = sprintf(
					'%s %s %s %s',
					$btn_pad_top ? esc_attr( $btn_pad_top ) : '12px',
					$btn_pad_right ? esc_attr( $btn_pad_right ) : '24px',
					$btn_pad_bottom ? esc_attr( $btn_pad_bottom ) : '12px',
					$btn_pad_left ? esc_attr( $btn_pad_left ) : '24px'
				);
				echo 'padding: ' . $padding . ' !important;';
			}
			if ( $btn_radius ) {
				echo 'border-radius: ' . esc_attr( $btn_radius ) . ' !important;';
			}
			echo '}';
		}

		if ( $btn_hover_bg || $btn_hover_text ) {
			echo '.woocommerce-checkout button.button:hover, ';
			echo '.woocommerce-checkout .button:hover, ';
			echo '.woocommerce-checkout input[type="submit"]:hover, ';
			echo '.woocommerce-checkout #place_order:hover {';
			if ( $btn_hover_bg ) {
				echo 'background-color: ' . esc_attr( $btn_hover_bg ) . ' !important;';
			}
			if ( $btn_hover_text ) {
				echo 'color: ' . esc_attr( $btn_hover_text ) . ' !important;';
			}
			echo '}';
		}
	}

	/**
	 * Output spacing CSS
	 */
	private function output_spacing_css() {
		$section_pad_top    = get_theme_mod( 'blocksy_fc_section_padding_top' );
		$section_pad_right  = get_theme_mod( 'blocksy_fc_section_padding_right' );
		$section_pad_bottom = get_theme_mod( 'blocksy_fc_section_padding_bottom' );
		$section_pad_left   = get_theme_mod( 'blocksy_fc_section_padding_left' );
		$section_margin_btm = get_theme_mod( 'blocksy_fc_section_margin_bottom' );
		$field_gap          = get_theme_mod( 'blocksy_fc_field_gap' );

		if ( $section_pad_top || $section_pad_right || $section_pad_bottom || $section_pad_left ) {
			echo '.woocommerce-checkout .fc-step, ';
			echo '.woocommerce-checkout .fc-cart-section, ';
			echo '.woocommerce-checkout .woocommerce-checkout-review-order {';
			$padding = sprintf(
				'%s %s %s %s',
				$section_pad_top ? esc_attr( $section_pad_top ) : '20px',
				$section_pad_right ? esc_attr( $section_pad_right ) : '20px',
				$section_pad_bottom ? esc_attr( $section_pad_bottom ) : '20px',
				$section_pad_left ? esc_attr( $section_pad_left ) : '20px'
			);
			echo 'padding: ' . $padding . ' !important;';
			echo '}';
		}

		if ( $section_margin_btm ) {
			echo '.woocommerce-checkout .fc-step, ';
			echo '.woocommerce-checkout .fc-cart-section {';
			echo 'margin-bottom: ' . esc_attr( $section_margin_btm ) . ' !important;';
			echo '}';
		}

		if ( $field_gap ) {
			echo '.woocommerce-checkout .form-row {';
			echo 'margin-bottom: ' . esc_attr( $field_gap ) . ' !important;';
			echo '}';
		}
	}

	/**
	 * Output borders CSS
	 */
	private function output_borders_css() {
		$border_width  = get_theme_mod( 'blocksy_fc_section_border_width' );
		$border_color  = get_theme_mod( 'blocksy_fc_section_border_color' );
		$border_style  = get_theme_mod( 'blocksy_fc_section_border_style' );
		$border_radius = get_theme_mod( 'blocksy_fc_section_border_radius' );

		if ( $border_width || $border_color || $border_style || $border_radius ) {
			echo '.woocommerce-checkout .fc-step, ';
			echo '.woocommerce-checkout .fc-cart-section, ';
			echo '.woocommerce-checkout .woocommerce-checkout-review-order {';
			if ( $border_width && $border_color && $border_style ) {
				echo 'border: ' . esc_attr( $border_width ) . ' ' . esc_attr( $border_style ) . ' ' . esc_attr( $border_color ) . ' !important;';
			}
			if ( $border_radius ) {
				echo 'border-radius: ' . esc_attr( $border_radius ) . ' !important;';
			}
			echo '}';
		}
	}

	/**
	 * Enqueue preview scripts for live preview
	 */
	public function enqueue_preview_scripts() {
		// Enqueue customizer preview script if it exists
		$preview_script = get_stylesheet_directory_uri() . '/assets/js/fluid-checkout-customizer-preview.js';
		if ( file_exists( get_stylesheet_directory() . '/assets/js/fluid-checkout-customizer-preview.js' ) ) {
			wp_enqueue_script(
				'blocksy-fluid-checkout-customizer-preview',
				$preview_script,
				array( 'jquery', 'customize-preview' ),
				'1.0.0',
				true
			);
		}
	}
}

// Initialize the customizer integration
new Blocksy_Child_Fluid_Checkout_Customizer();

