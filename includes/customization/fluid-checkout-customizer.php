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

/**
 * Check if Fluid Checkout is active before initializing customizer
 *
 * This prevents fatal errors if Fluid Checkout plugin is deactivated.
 * We check for the main FluidCheckout class which is present in both
 * Fluid Checkout Lite and Fluid Checkout Pro versions.
 */
if ( ! class_exists( 'FluidCheckout' ) ) {
	// Add admin notice if we're in admin area
	if ( is_admin() ) {
		add_action( 'admin_notices', function() {
			if ( current_user_can( 'activate_plugins' ) ) {
				?>
				<div class="notice notice-warning is-dismissible">
					<p>
						<strong><?php esc_html_e( 'Fluid Checkout Customizer:', 'blocksy-child' ); ?></strong>
						<?php esc_html_e( 'The Fluid Checkout Customizer requires Fluid Checkout Lite or Fluid Checkout Pro to be installed and activated.', 'blocksy-child' ); ?>
					</p>
				</div>
				<?php
			}
		} );
	}

	// Exit gracefully - do not load the customizer
	return;
}

class Blocksy_Child_Fluid_Checkout_Customizer {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Double-check dependencies before registering hooks
		if ( ! $this->check_dependencies() ) {
			return;
		}

		// Register customizer hooks
		add_action( 'customize_register', array( $this, 'register_customizer_settings' ) );
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_customizer_controls_styles' ) );
		add_action( 'wp_head', array( $this, 'output_customizer_css' ), 999 );

		// Register frontend scripts for checkout page
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	}

	/**
	 * Check if all required dependencies are available
	 *
	 * @return bool True if all dependencies are met, false otherwise
	 */
	private function check_dependencies() {
		// Check for FluidCheckout class
		// Note: We only check for FluidCheckout here, not WP_Customize_Manager,
		// because we need this class to work on the frontend to output CSS via wp_head
		if ( ! class_exists( 'FluidCheckout' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Register all customizer settings, controls, and sections
	 *
	 * @param WP_Customize_Manager $wp_customize WordPress Customizer Manager instance
	 */
	public function register_customizer_settings( $wp_customize ) {
		// Early return if dependencies not met
		if ( ! $this->check_dependencies() ) {
			return;
		}

		// Verify $wp_customize is valid
		if ( ! $wp_customize instanceof WP_Customize_Manager ) {
			return;
		}

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

		// Register sections with error handling
		try {
			$this->register_general_colors_section( $wp_customize );
			$this->register_typography_sections( $wp_customize );
			$this->register_form_elements_section( $wp_customize );
			$this->register_buttons_section( $wp_customize );
			$this->register_spacing_section( $wp_customize );
			$this->register_borders_section( $wp_customize );
			$this->register_content_text_section( $wp_customize );
			$this->register_step_indicators_section( $wp_customize );
		} catch ( Exception $e ) {
			// Log error if WP_DEBUG is enabled
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( 'Fluid Checkout Customizer Error: ' . $e->getMessage() );
			}
		}
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
				'description' => __( 'Select a font family for this element', 'blocksy-child' ),
				'section'     => $section,
				'type'        => 'select',
				'choices'     => $this->get_font_family_choices(),
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
	 * Get font family choices for dropdown
	 *
	 * Returns an array of font families organized by category.
	 * Includes system fonts, web-safe fonts, and popular Google Fonts.
	 *
	 * @return array Font family choices for select control
	 */
	private function get_font_family_choices() {
		return array(
			// System Fonts
			'-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' => __( 'System Default (Recommended)', 'blocksy-child' ),

			// Sans-Serif Fonts
			'Arial, Helvetica, sans-serif'                    => __( 'Arial', 'blocksy-child' ),
			'"Helvetica Neue", Helvetica, Arial, sans-serif'  => __( 'Helvetica Neue', 'blocksy-child' ),
			'Verdana, Geneva, sans-serif'                     => __( 'Verdana', 'blocksy-child' ),
			'Tahoma, Geneva, sans-serif'                      => __( 'Tahoma', 'blocksy-child' ),
			'"Trebuchet MS", Helvetica, sans-serif'           => __( 'Trebuchet MS', 'blocksy-child' ),
			'"Segoe UI", Tahoma, Geneva, Verdana, sans-serif' => __( 'Segoe UI', 'blocksy-child' ),

			// Serif Fonts
			'Georgia, serif'                                  => __( 'Georgia', 'blocksy-child' ),
			'"Times New Roman", Times, serif'                 => __( 'Times New Roman', 'blocksy-child' ),
			'"Palatino Linotype", "Book Antiqua", Palatino, serif' => __( 'Palatino', 'blocksy-child' ),
			'Garamond, serif'                                 => __( 'Garamond', 'blocksy-child' ),

			// Monospace Fonts
			'"Courier New", Courier, monospace'               => __( 'Courier New', 'blocksy-child' ),
			'Monaco, "Lucida Console", monospace'             => __( 'Monaco', 'blocksy-child' ),

			// Popular Google Fonts (if loaded by theme)
			'Roboto, sans-serif'                              => __( 'Roboto', 'blocksy-child' ),
			'"Open Sans", sans-serif'                         => __( 'Open Sans', 'blocksy-child' ),
			'Lato, sans-serif'                                => __( 'Lato', 'blocksy-child' ),
			'Montserrat, sans-serif'                          => __( 'Montserrat', 'blocksy-child' ),
			'"Source Sans Pro", sans-serif'                   => __( 'Source Sans Pro', 'blocksy-child' ),
			'Raleway, sans-serif'                             => __( 'Raleway', 'blocksy-child' ),
			'Poppins, sans-serif'                             => __( 'Poppins', 'blocksy-child' ),
			'Inter, sans-serif'                               => __( 'Inter', 'blocksy-child' ),
			'Nunito, sans-serif'                              => __( 'Nunito', 'blocksy-child' ),
			'"PT Sans", sans-serif'                           => __( 'PT Sans', 'blocksy-child' ),
			'Merriweather, serif'                             => __( 'Merriweather', 'blocksy-child' ),
			'Playfair Display, serif'                         => __( 'Playfair Display', 'blocksy-child' ),
		);
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
		// Early return if dependencies not met
		if ( ! $this->check_dependencies() ) {
			echo '<!-- Fluid Checkout Customizer CSS: Dependencies not met -->';
			return;
		}

		// Only output on checkout pages or if we're in customizer preview
		if ( ! $this->is_checkout_page() && ! is_customize_preview() ) {
			echo '<!-- Fluid Checkout Customizer CSS: Not checkout page (is_checkout=' . ( $this->is_checkout_page() ? 'YES' : 'NO' ) . ', is_customize_preview=' . ( is_customize_preview() ? 'YES' : 'NO' ) . ') -->';
			return;
		}

		// Verify required functions exist
		if ( ! function_exists( 'is_customize_preview' ) ) {
			echo '<!-- Fluid Checkout Customizer CSS: is_customize_preview function not found -->';
			return;
		}

		try {
			echo '<!-- Fluid Checkout Customizer CSS: ACTIVE (Updated selectors for Fluid Checkout HTML structure) -->';
			echo '<style type="text/css" id="blocksy-fluid-checkout-customizer-css">';
			echo '/* Blocksy Child Fluid Checkout - Customizer Styles */';
			echo '/* Updated: 2025-11-08 - Fixed CSS selectors to match Fluid Checkout HTML structure */';

			// General Colors
			if ( method_exists( $this, 'output_color_css_variables' ) ) {
				$this->output_color_css_variables();
			}

			// Typography
			if ( method_exists( $this, 'output_typography_css' ) ) {
				echo '/* Typography Styles */';
				$this->output_typography_css();
			}

			// Form Elements
			if ( method_exists( $this, 'output_form_elements_css' ) ) {
				echo '/* Form Elements Styles */';
				$this->output_form_elements_css();
			}

			// Buttons
			if ( method_exists( $this, 'output_buttons_css' ) ) {
				echo '/* Button Styles */';
				$this->output_buttons_css();
			}

			// Spacing
			if ( method_exists( $this, 'output_spacing_css' ) ) {
				echo '/* Spacing Styles */';
				$this->output_spacing_css();
			}

			// Borders
			if ( method_exists( $this, 'output_borders_css' ) ) {
				echo '/* Border Styles */';
				$this->output_borders_css();
			}

			// Step Indicators
			if ( method_exists( $this, 'output_step_indicators_css' ) ) {
				echo '/* Step Indicators Styles */';
				$this->output_step_indicators_css();
			}

			echo '</style>';
			echo '<!-- Fluid Checkout Customizer CSS: Output complete -->';
		} catch ( Exception $e ) {
			// Log error if WP_DEBUG is enabled
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( 'Fluid Checkout Customizer CSS Output Error: ' . $e->getMessage() );
			}
			echo '<!-- Fluid Checkout Customizer CSS: Error - ' . esc_html( $e->getMessage() ) . ' -->';
		}
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
		// Updated selectors to match Fluid Checkout HTML structure
		$elements = array(
			'heading'     => '.fc-step__title, .fc-step__substep-title, .fc-checkout__title, .fc-checkout-order-review-title',
			'body'        => '.fc-wrapper, .fc-wrapper p, .fc-wrapper span, .fc-step__substep-text',
			'label'       => '.fc-text-field label, .fc-email-field label, .fc-tel-field label, .fc-select-field label, .fc-textarea-field label, .woocommerce-form__label',
			'placeholder' => '.fc-text-field input::placeholder, .fc-email-field input::placeholder, .fc-tel-field input::placeholder, .fc-textarea-field textarea::placeholder',
			'button'      => '.fc-step__next-step, .fc-place-order-button, .fc-step__substep-save, .fc-coupon-code__apply',
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
			// Updated selectors to match Fluid Checkout HTML structure
			echo '.fc-text-field input.input-text, ';
			echo '.fc-email-field input.input-text, ';
			echo '.fc-tel-field input.input-text, ';
			echo '.fc-textarea-field textarea.input-text, ';
			echo '.fc-select-field select, ';
			echo '.fc-select2-field select {';
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
			// Updated selectors to match Fluid Checkout HTML structure
			echo '.fc-text-field input.input-text:focus, ';
			echo '.fc-email-field input.input-text:focus, ';
			echo '.fc-tel-field input.input-text:focus, ';
			echo '.fc-textarea-field textarea.input-text:focus, ';
			echo '.fc-select-field select:focus, ';
			echo '.fc-select2-field select:focus {';
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
			// Updated selectors to match Fluid Checkout HTML structure
			echo '.fc-step__next-step.button, ';
			echo '.fc-place-order-button, ';
			echo '.fc-step__substep-save.button, ';
			echo '.fc-coupon-code__apply.button, ';
			echo '#place_order {';
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
			// Updated selectors to match Fluid Checkout HTML structure
			echo '.fc-step__next-step.button:hover, ';
			echo '.fc-place-order-button:hover, ';
			echo '.fc-step__substep-save.button:hover, ';
			echo '.fc-coupon-code__apply.button:hover, ';
			echo '#place_order:hover {';
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
			// Updated selectors to match Fluid Checkout HTML structure
			echo '.fc-checkout-step, ';
			echo '.fc-step__substep, ';
			echo '.fc-checkout-order-review {';
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
			// Updated selectors to match Fluid Checkout HTML structure
			echo '.fc-checkout-step, ';
			echo '.fc-step__substep {';
			echo 'margin-bottom: ' . esc_attr( $section_margin_btm ) . ' !important;';
			echo '}';
		}

		if ( $field_gap ) {
			// Updated selectors to match Fluid Checkout HTML structure
			echo '.fc-text-field, ';
			echo '.fc-email-field, ';
			echo '.fc-tel-field, ';
			echo '.fc-select-field, ';
			echo '.fc-textarea-field {';
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
			// Updated selectors to match Fluid Checkout HTML structure
			echo '.fc-checkout-step, ';
			echo '.fc-step__substep, ';
			echo '.fc-checkout-order-review {';
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
	 * Output step indicators CSS
	 */
	private function output_step_indicators_css() {
		$checkmark_icon_color = get_theme_mod( 'blocksy_fc_checkmark_icon_color', '#ffffff' );
		$checkmark_bg_color   = get_theme_mod( 'blocksy_fc_checkmark_bg_color', '#7b7575' );

		// Target the ::before pseudo-element on completed step substep titles
		// The checkmark appears on .fc-step__substep-title when the parent section has data-step-complete attribute
		echo '[data-step-complete] .fc-step__substep-title::before {';
		if ( $checkmark_icon_color ) {
			echo 'color: ' . esc_attr( $checkmark_icon_color ) . ' !important;';
		}
		if ( $checkmark_bg_color ) {
			echo 'background-color: ' . esc_attr( $checkmark_bg_color ) . ' !important;';
		}
		echo '}';
	}

	/**
	 * Enqueue preview scripts for live preview
	 */
	public function enqueue_preview_scripts() {
		// Early return if dependencies not met
		if ( ! $this->check_dependencies() ) {
			return;
		}

		// Verify required WordPress functions exist
		if ( ! function_exists( 'wp_enqueue_script' ) || ! function_exists( 'get_stylesheet_directory' ) || ! function_exists( 'get_stylesheet_directory_uri' ) ) {
			return;
		}

		// Enqueue customizer preview script if it exists
		$preview_script_path = get_stylesheet_directory() . '/assets/js/fluid-checkout-customizer-preview.js';
		$preview_script_url = get_stylesheet_directory_uri() . '/assets/js/fluid-checkout-customizer-preview.js';

		// Verify file exists and is readable
		if ( file_exists( $preview_script_path ) && is_readable( $preview_script_path ) ) {
			wp_enqueue_script(
				'blocksy-fluid-checkout-customizer-preview',
				$preview_script_url,
				array( 'jquery', 'customize-preview' ),
				filemtime( $preview_script_path ), // Use file modification time for cache busting
				true
			);
		} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// Log warning if script file is missing
			error_log( 'Fluid Checkout Customizer: Preview script not found at ' . $preview_script_path );
		}
	}

	/**
	 * Enqueue customizer controls styles
	 *
	 * Loads custom CSS for enhanced styling of Fluid Checkout Customizer controls
	 * in the WordPress Customizer interface.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_customizer_controls_styles() {
		// Early return if dependencies not met
		if ( ! $this->check_dependencies() ) {
			return;
		}

		// Verify required WordPress functions exist
		if ( ! function_exists( 'wp_enqueue_style' ) || ! function_exists( 'get_stylesheet_directory' ) || ! function_exists( 'get_stylesheet_directory_uri' ) ) {
			return;
		}

		// Enqueue customizer controls CSS if it exists
		$controls_css_path = get_stylesheet_directory() . '/assets/css/fluid-checkout-customizer.css';
		$controls_css_url = get_stylesheet_directory_uri() . '/assets/css/fluid-checkout-customizer.css';

		// Verify file exists and is readable
		if ( file_exists( $controls_css_path ) && is_readable( $controls_css_path ) ) {
			wp_enqueue_style(
				'blocksy-fluid-checkout-customizer-controls',
				$controls_css_url,
				array( 'customize-controls' ), // Depend on WordPress Customizer controls styles
				filemtime( $controls_css_path ), // Use file modification time for cache busting
				'all'
			);
		} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// Log warning if CSS file is missing
			error_log( 'Fluid Checkout Customizer: Controls CSS not found at ' . $controls_css_path );
		}
	}

	/**
	 * Enqueue frontend scripts for checkout page
	 */
	public function enqueue_frontend_scripts() {
		// Only load on checkout page
		if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
			return;
		}

		// Enqueue the frontend script
		wp_enqueue_script(
			'blocksy-fluid-checkout-frontend',
			get_stylesheet_directory_uri() . '/assets/js/fluid-checkout-frontend.js',
			array( 'jquery' ),
			filemtime( get_stylesheet_directory() . '/assets/js/fluid-checkout-frontend.js' ),
			true
		);

		// Pass customizer settings to JavaScript
		wp_localize_script(
			'blocksy-fluid-checkout-frontend',
			'blocksyFluidCheckoutSettings',
			array(
				'myContactHeadingText' => get_theme_mod( 'blocksy_fc_my_contact_heading_text', 'My contact' ),
			)
		);
	}

	/**
	 * Register Content & Text Section
	 */
	private function register_content_text_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_fc_content_text',
			array(
				'title'    => __( 'Content & Text', 'blocksy-child' ),
				'panel'    => 'blocksy_fluid_checkout_panel',
				'priority' => 70,
			)
		);

		// My Contact Heading Text
		$wp_customize->add_setting(
			'blocksy_fc_my_contact_heading_text',
			array(
				'default'           => 'My contact',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_my_contact_heading_text',
			array(
				'label'       => __( 'My Contact Heading Text', 'blocksy-child' ),
				'description' => __( 'Customize the heading text for the contact step in the checkout process.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_content_text',
				'type'        => 'text',
				'priority'    => 10,
			)
		);
	}

	/**
	 * Register Step Indicators Section
	 */
	private function register_step_indicators_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_fc_step_indicators',
			array(
				'title'    => __( 'Step Indicators', 'blocksy-child' ),
				'panel'    => 'blocksy_fluid_checkout_panel',
				'priority' => 75,
			)
		);

		// Checkmark Icon Color
		$wp_customize->add_setting(
			'blocksy_fc_checkmark_icon_color',
			array(
				'default'           => '#ffffff',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_checkmark_icon_color',
				array(
					'label'       => __( 'Checkmark Icon Color', 'blocksy-child' ),
					'description' => __( 'Color of the checkmark symbol in completed step indicators.', 'blocksy-child' ),
					'section'     => 'blocksy_fc_step_indicators',
					'priority'    => 10,
				)
			)
		);

		// Checkmark Background Color
		$wp_customize->add_setting(
			'blocksy_fc_checkmark_bg_color',
			array(
				'default'           => '#7b7575',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_checkmark_bg_color',
				array(
					'label'       => __( 'Checkmark Background Color', 'blocksy-child' ),
					'description' => __( 'Background color of the checkmark circle in completed step indicators.', 'blocksy-child' ),
					'section'     => 'blocksy_fc_step_indicators',
					'priority'    => 20,
				)
			)
		);
	}

}

// Initialize the customizer integration only if dependencies are met
// Note: We don't check for WP_Customize_Manager here because we need the class
// to be instantiated on the frontend to output the CSS via wp_head hook
if ( class_exists( 'FluidCheckout' ) ) {
	new Blocksy_Child_Fluid_Checkout_Customizer();
}

