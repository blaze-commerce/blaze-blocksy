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
			$this->register_progress_bar_section( $wp_customize );
			$this->register_item_count_badge_section( $wp_customize );
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
				'label'       => __( 'Primary Color', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
			),
			'secondary_color'         => array(
				'label'       => __( 'Secondary Color', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
			),
			'body_text_color'         => array(
				'label'       => __( 'Body Text Color', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
			),
			'heading_color'           => array(
				'label'       => __( 'Heading Color', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
			),
			'link_color'              => array(
				'label'       => __( 'Link Color', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
			),
			'link_hover_color'        => array(
				'label'       => __( 'Link Hover Color', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
			),
			'content_background'      => array(
				'label'       => __( 'Content Background', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
			),
			'border_color'            => array(
				'label'       => __( 'Border Color', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
			),
		);

		foreach ( $color_settings as $key => $config ) {
			$wp_customize->add_setting(
				"blocksy_fc_{$key}",
				array(
					'default'           => $config['default'],
					'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					"blocksy_fc_{$key}",
					array(
						'label'       => $config['label'],
						'description' => isset( $config['description'] ) ? $config['description'] : '',
						'section'     => 'blocksy_fc_general_colors',
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
			'heading'            => array(
				'title'    => __( 'Heading Typography', 'blocksy-child' ),
				'priority' => 20,
			),
			'incomplete_heading' => array(
				'title'       => __( 'Incomplete Section Heading Typography', 'blocksy-child' ),
				'description' => __( 'Typography for form section headings that are incomplete or being edited', 'blocksy-child' ),
				'priority'    => 21,
			),
			'order_summary'      => array(
				'title'    => __( 'Order Summary Heading Typography', 'blocksy-child' ),
				'priority' => 25,
			),
			'body'               => array(
				'title'    => __( 'Body Text Typography', 'blocksy-child' ),
				'priority' => 30,
			),
			'label'              => array(
				'title'    => __( 'Form Label Typography', 'blocksy-child' ),
				'priority' => 40,
			),
			'placeholder'        => array(
				'title'    => __( 'Placeholder Typography', 'blocksy-child' ),
				'priority' => 50,
			),
			'button'             => array(
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
				'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				"blocksy_fc_{$element}_font_color",
				array(
					'label'       => __( 'Font Color', 'blocksy-child' ),
					'description' => __( 'Leave empty to use theme default', 'blocksy-child' ),
					'section'     => $section,
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
					'inherit' => __( 'Theme Default (Inherit)', 'blocksy-child' ),
					'100'     => __( 'Thin (100)', 'blocksy-child' ),
					'200'     => __( 'Extra Light (200)', 'blocksy-child' ),
					'300'     => __( 'Light (300)', 'blocksy-child' ),
					'400'     => __( 'Normal (400)', 'blocksy-child' ),
					'500'     => __( 'Medium (500)', 'blocksy-child' ),
					'600'     => __( 'Semi Bold (600)', 'blocksy-child' ),
					'700'     => __( 'Bold (700)', 'blocksy-child' ),
					'800'     => __( 'Extra Bold (800)', 'blocksy-child' ),
					'900'     => __( 'Black (900)', 'blocksy-child' ),
				),
			)
		);
	}

	/**
	 * Get typography defaults for different elements
	 *
	 * Returns empty strings or 'inherit' to allow theme defaults to apply.
	 * Users can customize these values in the customizer to override theme styles.
	 */
	private function get_typography_defaults( $element ) {
		$defaults = array(
			'heading'            => array(
				'font'   => 'inherit',
				'size'   => '',
				'color'  => '',
				'weight' => 'inherit',
			),
			'incomplete_heading' => array(
				'font'   => 'inherit',
				'size'   => '',
				'color'  => '',
				'weight' => 'inherit',
			),
			'order_summary'      => array(
				'font'   => 'inherit',
				'size'   => '',
				'color'  => '',
				'weight' => 'inherit',
			),
			'body'               => array(
				'font'   => 'inherit',
				'size'   => '',
				'color'  => '',
				'weight' => 'inherit',
			),
			'label'              => array(
				'font'   => 'inherit',
				'size'   => '',
				'color'  => '',
				'weight' => 'inherit',
			),
			'placeholder'        => array(
				'font'   => 'inherit',
				'size'   => '',
				'color'  => '',
				'weight' => 'inherit',
			),
			'button'             => array(
				'font'   => 'inherit',
				'size'   => '',
				'color'  => '',
				'weight' => 'inherit',
			),
		);

		return isset( $defaults[ $element ] ) ? $defaults[ $element ] : $defaults['body'];
	}

	/**
	 * Get font family choices for dropdown
	 *
	 * Returns an array of font families organized by category.
	 * Includes system fonts, web-safe fonts, popular Google Fonts,
	 * and custom fonts from Blocksy Companion Pro Custom Fonts extension.
	 *
	 * @return array Font family choices for select control
	 */
	private function get_font_family_choices() {
		$fonts = array(
			// Theme Default
			'inherit'                                         => __( 'Theme Default (Inherit)', 'blocksy-child' ),

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

		// Add custom fonts from Blocksy Companion Pro if available
		$custom_fonts = $this->get_blocksy_custom_fonts();
		if ( ! empty( $custom_fonts ) ) {
			$fonts = array_merge( $fonts, $custom_fonts );
		}

		return $fonts;
	}

	/**
	 * Get custom fonts from Blocksy Companion Pro Custom Fonts extension
	 *
	 * Retrieves all custom fonts uploaded through the Blocksy Companion Pro
	 * Custom Fonts extension and formats them for use in font family dropdowns.
	 *
	 * @return array Array of custom fonts with font-family CSS values as keys and display names as values
	 */
	private function get_blocksy_custom_fonts() {
		$custom_fonts = array();

		// Check if Blocksy Companion Pro Custom Fonts extension is active
		if ( ! class_exists( '\Blocksy\Extensions\CustomFonts\Storage' ) ) {
			return $custom_fonts;
		}

		try {
			// Get the custom fonts storage instance
			$storage = new \Blocksy\Extensions\CustomFonts\Storage();
			$fonts   = $storage->get_normalized_fonts_list();

			// If no custom fonts are uploaded, return empty array
			if ( empty( $fonts ) ) {
				return $custom_fonts;
			}

			// Process each custom font
			foreach ( $fonts as $font ) {
				// Skip fonts without variations
				if ( empty( $font['variations'] ) || ! is_array( $font['variations'] ) ) {
					continue;
				}

				// Get the font family CSS value (e.g., "ct_font_proxima_nova")
				$font_family = $this->get_blocksy_font_family_for_name( $font['name'] );

				// Add to custom fonts array with display name
				// Format: 'ct_font_proxima_nova' => 'ProximaNova (Custom Font)'
				$custom_fonts[ $font_family ] = sprintf(
					/* translators: %s: Custom font name */
					__( '%s (Custom Font)', 'blocksy-child' ),
					$font['name']
				);
			}
		} catch ( Exception $e ) {
			// Silently fail if there's an error retrieving custom fonts
			// This ensures the Customizer still works even if there's an issue
			error_log( 'Fluid Checkout Customizer: Error retrieving Blocksy custom fonts - ' . $e->getMessage() );
		}

		return $custom_fonts;
	}

	/**
	 * Convert font name to Blocksy font family CSS value
	 *
	 * Converts a font name like "ProximaNova" to the CSS font-family value
	 * used by Blocksy (e.g., "ct_font_proxima_nova").
	 *
	 * This matches the format used by Blocksy Companion Pro's Custom Fonts extension.
	 *
	 * @param string $name Font name (e.g., "ProximaNova")
	 * @return string Font family CSS value (e.g., "ct_font_proxima_nova")
	 */
	private function get_blocksy_font_family_for_name( $name ) {
		// Convert camelCase to snake_case and add ct_font_ prefix
		// Example: "ProximaNova" -> "ct_font_proxima_nova"
		return str_replace(
			' ',
			'_',
			'ct_font_' . strtolower(
				preg_replace( '/(?<!^)[A-Z]/', '_$0', $name )
			)
		);
	}

	/**
	 * Sanitize CSS unit values
	 *
	 * Allows empty strings to enable theme defaults.
	 */
	public function sanitize_css_unit( $input ) {
		// Allow empty string for theme default
		if ( empty( $input ) || trim( $input ) === '' ) {
			return '';
		}

		$sanitized = preg_replace( '/[^0-9a-zA-Z%.\s-]/', '', $input );

		if ( ! empty( $sanitized ) && ! preg_match( '/(px|em|rem|%|vh|vw|pt|pc|in|cm|mm|ex|ch)$/', $sanitized ) ) {
			$sanitized .= 'px';
		}

		return $sanitized;
	}

	/**
	 * Sanitize color values allowing empty strings
	 *
	 * Allows empty strings to enable theme defaults.
	 * Otherwise uses WordPress core sanitize_hex_color function.
	 */
	public function sanitize_color_allow_empty( $input ) {
		// Allow empty string for theme default
		if ( empty( $input ) || trim( $input ) === '' ) {
			return '';
		}

		// Use WordPress core sanitization for non-empty values
		return sanitize_hex_color( $input );
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
				'label'       => __( 'Input Background Color', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'color',
			),
			'input_border_color'  => array(
				'label'       => __( 'Input Border Color', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'color',
			),
			'input_text_color'    => array(
				'label'       => __( 'Input Text Color', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'color',
			),
			'input_focus_border'  => array(
				'label'       => __( 'Input Focus Border Color', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'color',
			),
			'input_padding'       => array(
				'label'       => __( 'Input Padding', 'blocksy-child' ),
				'description' => __( 'Enter padding with CSS unit (e.g., 12px, 1rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'text',
			),
			'input_border_radius' => array(
				'label'       => __( 'Input Border Radius', 'blocksy-child' ),
				'description' => __( 'Enter border radius with CSS unit (e.g., 4px, 0.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'text',
			),
		);

		foreach ( $form_settings as $key => $config ) {
			$sanitize_callback = ( $config['type'] === 'color' ) ? array( $this, 'sanitize_color_allow_empty' ) : array( $this, 'sanitize_css_unit' );

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
							'label'       => $config['label'],
							'description' => isset( $config['description'] ) ? $config['description'] : '',
							'section'     => 'blocksy_fc_form_elements',
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
				'label'       => __( 'Primary Button Background', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'color',
			),
			'button_primary_text'       => array(
				'label'       => __( 'Primary Button Text', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'color',
			),
			'button_primary_hover_bg'   => array(
				'label'       => __( 'Primary Button Hover Background', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'color',
			),
			'button_primary_hover_text' => array(
				'label'       => __( 'Primary Button Hover Text', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'color',
			),
			'button_padding_top'        => array(
				'label'       => __( 'Button Padding Top', 'blocksy-child' ),
				'description' => __( 'Enter padding with CSS unit (e.g., 12px, 1rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'text',
			),
			'button_padding_right'      => array(
				'label'       => __( 'Button Padding Right', 'blocksy-child' ),
				'description' => __( 'Enter padding with CSS unit (e.g., 24px, 1.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'text',
			),
			'button_padding_bottom'     => array(
				'label'       => __( 'Button Padding Bottom', 'blocksy-child' ),
				'description' => __( 'Enter padding with CSS unit (e.g., 12px, 1rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'text',
			),
			'button_padding_left'       => array(
				'label'       => __( 'Button Padding Left', 'blocksy-child' ),
				'description' => __( 'Enter padding with CSS unit (e.g., 24px, 1.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'text',
			),
			'button_border_radius'      => array(
				'label'       => __( 'Button Border Radius', 'blocksy-child' ),
				'description' => __( 'Enter border radius with CSS unit (e.g., 4px, 0.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
				'type'        => 'text',
			),
		);

		foreach ( $button_settings as $key => $config ) {
			$sanitize_callback = ( $config['type'] === 'color' ) ? array( $this, 'sanitize_color_allow_empty' ) : array( $this, 'sanitize_css_unit' );

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
							'label'       => $config['label'],
							'description' => isset( $config['description'] ) ? $config['description'] : '',
							'section'     => 'blocksy_fc_buttons',
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
				'description' => __( 'Padding for checkout sections (e.g., 20px, 1.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
			),
			'section_padding_right'  => array(
				'label'       => __( 'Section Padding Right', 'blocksy-child' ),
				'description' => __( 'Padding for checkout sections (e.g., 20px, 1.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
			),
			'section_padding_bottom' => array(
				'label'       => __( 'Section Padding Bottom', 'blocksy-child' ),
				'description' => __( 'Padding for checkout sections (e.g., 20px, 1.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
			),
			'section_padding_left'   => array(
				'label'       => __( 'Section Padding Left', 'blocksy-child' ),
				'description' => __( 'Padding for checkout sections (e.g., 20px, 1.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
			),
			'section_margin_bottom'  => array(
				'label'       => __( 'Section Margin Bottom', 'blocksy-child' ),
				'description' => __( 'Space between checkout sections (e.g., 20px, 1.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
			),
			'field_gap'              => array(
				'label'       => __( 'Field Gap', 'blocksy-child' ),
				'description' => __( 'Space between form fields (e.g., 15px, 1rem). Leave empty for theme default.', 'blocksy-child' ),
				'default'     => '',
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
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_section_border_width',
			array(
				'label'       => __( 'Section Border Width', 'blocksy-child' ),
				'description' => __( 'Border width for checkout sections (e.g., 1px, 2px). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_borders',
				'type'        => 'text',
			)
		);

		// Border Color
		$wp_customize->add_setting(
			'blocksy_fc_section_border_color',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_section_border_color',
				array(
					'label'       => __( 'Section Border Color', 'blocksy-child' ),
					'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
					'section'     => 'blocksy_fc_borders',
				)
			)
		);

		// Border Style
		$wp_customize->add_setting(
			'blocksy_fc_section_border_style',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_section_border_style',
			array(
				'label'       => __( 'Section Border Style', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'section'     => 'blocksy_fc_borders',
				'type'        => 'select',
				'choices'     => array(
					''       => __( 'Theme Default', 'blocksy-child' ),
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
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_section_border_radius',
			array(
				'label'       => __( 'Section Border Radius', 'blocksy-child' ),
				'description' => __( 'Border radius for checkout sections (e.g., 8px, 0.5rem). Leave empty for theme default.', 'blocksy-child' ),
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

			// Item Count Badge
			if ( method_exists( $this, 'output_item_count_badge_css' ) ) {
				echo '/* Item Count Badge Styles */';
				$this->output_item_count_badge_css();
			}

			// Progress Bar Full Width
			if ( method_exists( $this, 'output_progress_bar_css' ) ) {
				echo '/* Progress Bar Full Width Styles */';
				$this->output_progress_bar_css();
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
	 *
	 * Only outputs CSS variables for non-empty values.
	 * This allows theme defaults to apply when customizer values are not set.
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

		// Collect non-empty color variables
		$css_variables = array();

		foreach ( $colors as $key => $css_var ) {
			$value = get_theme_mod( "blocksy_fc_{$key}", '' );
			if ( ! empty( $value ) ) {
				$css_variables[] = esc_attr( $css_var ) . ': ' . esc_attr( $value );
			}
		}

		// Only output :root block if there are variables to set
		if ( ! empty( $css_variables ) ) {
			echo ':root { ';
			echo implode( '; ', $css_variables );
			echo '; }';
		}
	}

	/**
	 * Output typography CSS
	 *
	 * Only outputs CSS for non-empty, non-inherit values.
	 * This allows theme defaults to apply when customizer values are not set.
	 */
	private function output_typography_css() {
		// Updated selectors to match Fluid Checkout HTML structure
		// Note: Order Summary heading (.fc-checkout-order-review-title) is now separate
		// Note: Completed substep headings only styled when section is completed (fields are collapsed)
		// Note: Incomplete substep headings styled when section is incomplete or being edited (fields are expanded)
		$elements = array(
			'heading'            => '.fc-step__title, .fc-checkout__title, .fc-step__substep:has(.fc-step__substep-fields.is-collapsed) .fc-step__substep-title',
			'incomplete_heading' => '.fc-step__substep:not(:has(.fc-step__substep-fields.is-collapsed)) .fc-step__substep-title',
			'order_summary'      => '.fc-checkout-order-review-title, .woocommerce-checkout-review-order h3, #order_review h3, .wc-block-components-checkout-order-summary__title',
			'body'               => '.fc-wrapper, .fc-wrapper p, .fc-wrapper span, .fc-step__substep-text',
			'label'              => '.fc-text-field label, .fc-email-field label, .fc-tel-field label, .fc-select-field label, .fc-textarea-field label, .woocommerce-form__label',
			'placeholder'        => '.fc-text-field input::placeholder, .fc-email-field input::placeholder, .fc-tel-field input::placeholder, .fc-textarea-field textarea::placeholder',
			'button'             => '.fc-step__next-step, .fc-place-order-button, .fc-step__substep-save, .fc-coupon-code__apply',
		);

		foreach ( $elements as $element => $selector ) {
			$font_family = get_theme_mod( "blocksy_fc_{$element}_font_family" );
			$font_size   = get_theme_mod( "blocksy_fc_{$element}_font_size" );
			$font_color  = get_theme_mod( "blocksy_fc_{$element}_font_color" );
			$font_weight = get_theme_mod( "blocksy_fc_{$element}_font_weight" );

			// Collect non-empty, non-inherit properties
			$css_properties = array();

			if ( ! empty( $font_family ) && $font_family !== 'inherit' ) {
				$css_properties[] = 'font-family: ' . esc_attr( $font_family ) . ' !important';
			}
			if ( ! empty( $font_size ) ) {
				$css_properties[] = 'font-size: ' . esc_attr( $font_size ) . ' !important';
			}
			if ( ! empty( $font_color ) ) {
				$css_properties[] = 'color: ' . esc_attr( $font_color ) . ' !important';
			}
			if ( ! empty( $font_weight ) && $font_weight !== 'inherit' ) {
				$css_properties[] = 'font-weight: ' . esc_attr( $font_weight ) . ' !important';
			}

			// Only output CSS if there are properties to apply
			if ( ! empty( $css_properties ) ) {
				echo esc_attr( $selector ) . ' { ' . implode( '; ', $css_properties ) . '; }';
			}
		}
	}

	/**
	 * Output form elements CSS
	 *
	 * Only outputs CSS for non-empty values.
	 * This allows theme defaults to apply when customizer values are not set.
	 */
	private function output_form_elements_css() {
		$input_bg           = get_theme_mod( 'blocksy_fc_input_background', '' );
		$input_border       = get_theme_mod( 'blocksy_fc_input_border_color', '' );
		$input_text         = get_theme_mod( 'blocksy_fc_input_text_color', '' );
		$input_focus_border = get_theme_mod( 'blocksy_fc_input_focus_border', '' );
		$input_padding      = get_theme_mod( 'blocksy_fc_input_padding', '' );
		$input_radius       = get_theme_mod( 'blocksy_fc_input_border_radius', '' );

		// Collect non-empty properties for regular input state
		$css_properties = array();

		if ( ! empty( $input_bg ) ) {
			$css_properties[] = 'background-color: ' . esc_attr( $input_bg ) . ' !important';
		}
		if ( ! empty( $input_border ) ) {
			$css_properties[] = 'border-color: ' . esc_attr( $input_border ) . ' !important';
		}
		if ( ! empty( $input_text ) ) {
			$css_properties[] = 'color: ' . esc_attr( $input_text ) . ' !important';
		}
		if ( ! empty( $input_padding ) ) {
			$css_properties[] = 'padding: ' . esc_attr( $input_padding ) . ' !important';
		}
		if ( ! empty( $input_radius ) ) {
			$css_properties[] = 'border-radius: ' . esc_attr( $input_radius ) . ' !important';
		}

		// Only output CSS if there are properties to apply
		if ( ! empty( $css_properties ) ) {
			echo '.fc-text-field input.input-text, ';
			echo '.fc-email-field input.input-text, ';
			echo '.fc-tel-field input.input-text, ';
			echo '.fc-textarea-field textarea.input-text, ';
			echo '.fc-select-field select, ';
			echo '.fc-select2-field select { ';
			echo implode( '; ', $css_properties );
			echo '; }';
		}

		// Handle focus state separately
		if ( ! empty( $input_focus_border ) ) {
			echo '.fc-text-field input.input-text:focus, ';
			echo '.fc-email-field input.input-text:focus, ';
			echo '.fc-tel-field input.input-text:focus, ';
			echo '.fc-textarea-field textarea.input-text:focus, ';
			echo '.fc-select-field select:focus, ';
			echo '.fc-select2-field select:focus { ';
			echo 'border-color: ' . esc_attr( $input_focus_border ) . ' !important; }';
		}
	}

	/**
	 * Output buttons CSS
	 *
	 * Only outputs CSS for non-empty values.
	 * This allows theme defaults to apply when customizer values are not set.
	 */
	private function output_buttons_css() {
		$btn_bg          = get_theme_mod( 'blocksy_fc_button_primary_bg', '' );
		$btn_text        = get_theme_mod( 'blocksy_fc_button_primary_text', '' );
		$btn_hover_bg    = get_theme_mod( 'blocksy_fc_button_primary_hover_bg', '' );
		$btn_hover_text  = get_theme_mod( 'blocksy_fc_button_primary_hover_text', '' );
		$btn_pad_top     = get_theme_mod( 'blocksy_fc_button_padding_top', '' );
		$btn_pad_right   = get_theme_mod( 'blocksy_fc_button_padding_right', '' );
		$btn_pad_bottom  = get_theme_mod( 'blocksy_fc_button_padding_bottom', '' );
		$btn_pad_left    = get_theme_mod( 'blocksy_fc_button_padding_left', '' );
		$btn_radius      = get_theme_mod( 'blocksy_fc_button_border_radius', '' );

		// Collect non-empty properties for regular button state
		$css_properties = array();

		if ( ! empty( $btn_bg ) ) {
			$css_properties[] = 'background-color: ' . esc_attr( $btn_bg ) . ' !important';
		}
		if ( ! empty( $btn_text ) ) {
			$css_properties[] = 'color: ' . esc_attr( $btn_text ) . ' !important';
		}

		// Handle padding - only output if at least one padding value is set
		if ( ! empty( $btn_pad_top ) || ! empty( $btn_pad_right ) || ! empty( $btn_pad_bottom ) || ! empty( $btn_pad_left ) ) {
			$padding_parts = array();
			$padding_parts[] = ! empty( $btn_pad_top ) ? esc_attr( $btn_pad_top ) : '0';
			$padding_parts[] = ! empty( $btn_pad_right ) ? esc_attr( $btn_pad_right ) : '0';
			$padding_parts[] = ! empty( $btn_pad_bottom ) ? esc_attr( $btn_pad_bottom ) : '0';
			$padding_parts[] = ! empty( $btn_pad_left ) ? esc_attr( $btn_pad_left ) : '0';
			$css_properties[] = 'padding: ' . implode( ' ', $padding_parts ) . ' !important';
		}

		if ( ! empty( $btn_radius ) ) {
			$css_properties[] = 'border-radius: ' . esc_attr( $btn_radius ) . ' !important';
		}

		// Only output CSS if there are properties to apply
		if ( ! empty( $css_properties ) ) {
			echo '.fc-step__next-step.button, ';
			echo '.fc-place-order-button, ';
			echo '.fc-step__substep-save.button, ';
			echo '.fc-coupon-code__apply.button, ';
			echo '#place_order { ';
			echo implode( '; ', $css_properties );
			echo '; }';
		}

		// Handle hover state separately
		$hover_properties = array();

		if ( ! empty( $btn_hover_bg ) ) {
			$hover_properties[] = 'background-color: ' . esc_attr( $btn_hover_bg ) . ' !important';
		}
		if ( ! empty( $btn_hover_text ) ) {
			$hover_properties[] = 'color: ' . esc_attr( $btn_hover_text ) . ' !important';
		}

		// Only output hover CSS if there are properties to apply
		if ( ! empty( $hover_properties ) ) {
			echo '.fc-step__next-step.button:hover, ';
			echo '.fc-place-order-button:hover, ';
			echo '.fc-step__substep-save.button:hover, ';
			echo '.fc-coupon-code__apply.button:hover, ';
			echo '#place_order:hover { ';
			echo implode( '; ', $hover_properties );
			echo '; }';
		}
	}

	/**
	 * Output spacing CSS
	 *
	 * Only outputs CSS for non-empty values.
	 * This allows theme defaults to apply when customizer values are not set.
	 */
	private function output_spacing_css() {
		$section_pad_top    = get_theme_mod( 'blocksy_fc_section_padding_top', '' );
		$section_pad_right  = get_theme_mod( 'blocksy_fc_section_padding_right', '' );
		$section_pad_bottom = get_theme_mod( 'blocksy_fc_section_padding_bottom', '' );
		$section_pad_left   = get_theme_mod( 'blocksy_fc_section_padding_left', '' );
		$section_margin_btm = get_theme_mod( 'blocksy_fc_section_margin_bottom', '' );
		$field_gap          = get_theme_mod( 'blocksy_fc_field_gap', '' );

		// Handle section padding - only output if at least one padding value is set
		if ( ! empty( $section_pad_top ) || ! empty( $section_pad_right ) || ! empty( $section_pad_bottom ) || ! empty( $section_pad_left ) ) {
			$padding_parts = array();
			$padding_parts[] = ! empty( $section_pad_top ) ? esc_attr( $section_pad_top ) : '0';
			$padding_parts[] = ! empty( $section_pad_right ) ? esc_attr( $section_pad_right ) : '0';
			$padding_parts[] = ! empty( $section_pad_bottom ) ? esc_attr( $section_pad_bottom ) : '0';
			$padding_parts[] = ! empty( $section_pad_left ) ? esc_attr( $section_pad_left ) : '0';

			echo '.fc-checkout-step, ';
			echo '.fc-step__substep, ';
			echo '.fc-checkout-order-review { ';
			echo 'padding: ' . implode( ' ', $padding_parts ) . ' !important; }';
		}

		// Handle section margin bottom
		if ( ! empty( $section_margin_btm ) ) {
			echo '.fc-checkout-step, ';
			echo '.fc-step__substep { ';
			echo 'margin-bottom: ' . esc_attr( $section_margin_btm ) . ' !important; }';
		}

		// Handle field gap
		if ( ! empty( $field_gap ) ) {
			echo '.fc-text-field, ';
			echo '.fc-email-field, ';
			echo '.fc-tel-field, ';
			echo '.fc-select-field, ';
			echo '.fc-textarea-field { ';
			echo 'margin-bottom: ' . esc_attr( $field_gap ) . ' !important; }';
		}
	}

	/**
	 * Output borders CSS
	 *
	 * Only outputs CSS for non-empty values.
	 * This allows theme defaults to apply when customizer values are not set.
	 */
	private function output_borders_css() {
		$border_width  = get_theme_mod( 'blocksy_fc_section_border_width', '' );
		$border_color  = get_theme_mod( 'blocksy_fc_section_border_color', '' );
		$border_style  = get_theme_mod( 'blocksy_fc_section_border_style', '' );
		$border_radius = get_theme_mod( 'blocksy_fc_section_border_radius', '' );

		// Collect non-empty properties
		$css_properties = array();

		// Only output border if all three values (width, style, color) are set
		if ( ! empty( $border_width ) && ! empty( $border_color ) && ! empty( $border_style ) ) {
			$css_properties[] = 'border: ' . esc_attr( $border_width ) . ' ' . esc_attr( $border_style ) . ' ' . esc_attr( $border_color ) . ' !important';
		}

		if ( ! empty( $border_radius ) ) {
			$css_properties[] = 'border-radius: ' . esc_attr( $border_radius ) . ' !important';
		}

		// Only output CSS if there are properties to apply
		if ( ! empty( $css_properties ) ) {
			echo '.fc-checkout-step, ';
			echo '.fc-step__substep, ';
			echo '.fc-checkout-order-review { ';
			echo implode( '; ', $css_properties );
			echo '; }';
		}
	}

	/**
	 * Output step indicators CSS
	 *
	 * Only outputs CSS for non-empty values.
	 * This allows theme defaults to apply when customizer values are not set.
	 */
	private function output_step_indicators_css() {
		$checkmark_icon_color = get_theme_mod( 'blocksy_fc_checkmark_icon_color', '' );
		$checkmark_bg_color   = get_theme_mod( 'blocksy_fc_checkmark_bg_color', '' );

		// Collect non-empty properties
		$css_properties = array();

		if ( ! empty( $checkmark_icon_color ) ) {
			$css_properties[] = 'color: ' . esc_attr( $checkmark_icon_color ) . ' !important';
		}
		if ( ! empty( $checkmark_bg_color ) ) {
			$css_properties[] = 'background-color: ' . esc_attr( $checkmark_bg_color ) . ' !important';
		}

		// Only output CSS if there are properties to apply
		if ( ! empty( $css_properties ) ) {
			// Target the ::before pseudo-element on completed step substep titles
			// The checkmark appears on .fc-step__substep-title when the parent section has data-step-complete attribute
			echo '[data-step-complete] .fc-step__substep-title::before { ';
			echo implode( '; ', $css_properties );
			echo '; }';
		}
	}

	/**
	 * Output item count badge CSS
	 *
	 * Only outputs CSS for non-empty, non-inherit values.
	 * This allows theme defaults to apply when customizer values are not set.
	 * Uses higher specificity selector and !important flags when values are set.
	 */
	private function output_item_count_badge_css() {
		// Get all customizer values with empty string defaults
		$border_top        = get_theme_mod( 'blocksy_fc_item_count_border_top', '' );
		$border_right      = get_theme_mod( 'blocksy_fc_item_count_border_right', '' );
		$border_bottom     = get_theme_mod( 'blocksy_fc_item_count_border_bottom', '' );
		$border_left       = get_theme_mod( 'blocksy_fc_item_count_border_left', '' );
		$border_style      = get_theme_mod( 'blocksy_fc_item_count_border_style', '' );
		$border_color      = get_theme_mod( 'blocksy_fc_item_count_border_color', '' );
		$border_radius     = get_theme_mod( 'blocksy_fc_item_count_border_radius', '' );
		$padding_top       = get_theme_mod( 'blocksy_fc_item_count_padding_top', '' );
		$padding_right     = get_theme_mod( 'blocksy_fc_item_count_padding_right', '' );
		$padding_bottom    = get_theme_mod( 'blocksy_fc_item_count_padding_bottom', '' );
		$padding_left      = get_theme_mod( 'blocksy_fc_item_count_padding_left', '' );
		$margin_top        = get_theme_mod( 'blocksy_fc_item_count_margin_top', '' );
		$margin_right      = get_theme_mod( 'blocksy_fc_item_count_margin_right', '' );
		$margin_bottom     = get_theme_mod( 'blocksy_fc_item_count_margin_bottom', '' );
		$margin_left       = get_theme_mod( 'blocksy_fc_item_count_margin_left', '' );
		$font_family       = get_theme_mod( 'blocksy_fc_item_count_font_family', 'inherit' );
		$font_size         = get_theme_mod( 'blocksy_fc_item_count_font_size', '' );
		$font_weight       = get_theme_mod( 'blocksy_fc_item_count_font_weight', 'inherit' );
		$line_height       = get_theme_mod( 'blocksy_fc_item_count_line_height', '' );
		$text_color        = get_theme_mod( 'blocksy_fc_item_count_text_color', '' );
		$letter_spacing    = get_theme_mod( 'blocksy_fc_item_count_letter_spacing', '' );
		$bg_color          = get_theme_mod( 'blocksy_fc_item_count_bg_color', '' );

		// Collect non-empty, non-inherit properties
		$css_properties = array();

		// Border
		if ( ! empty( $border_top ) ) {
			$css_properties[] = 'border-top-width: ' . esc_attr( $border_top ) . ' !important';
		}
		if ( ! empty( $border_right ) ) {
			$css_properties[] = 'border-right-width: ' . esc_attr( $border_right ) . ' !important';
		}
		if ( ! empty( $border_bottom ) ) {
			$css_properties[] = 'border-bottom-width: ' . esc_attr( $border_bottom ) . ' !important';
		}
		if ( ! empty( $border_left ) ) {
			$css_properties[] = 'border-left-width: ' . esc_attr( $border_left ) . ' !important';
		}
		if ( ! empty( $border_style ) ) {
			$css_properties[] = 'border-style: ' . esc_attr( $border_style ) . ' !important';
		}
		if ( ! empty( $border_color ) ) {
			$css_properties[] = 'border-color: ' . esc_attr( $border_color ) . ' !important';
		}
		if ( ! empty( $border_radius ) ) {
			$css_properties[] = 'border-radius: ' . esc_attr( $border_radius ) . ' !important';
		}

		// Padding
		if ( ! empty( $padding_top ) ) {
			$css_properties[] = 'padding-top: ' . esc_attr( $padding_top ) . ' !important';
		}
		if ( ! empty( $padding_right ) ) {
			$css_properties[] = 'padding-right: ' . esc_attr( $padding_right ) . ' !important';
		}
		if ( ! empty( $padding_bottom ) ) {
			$css_properties[] = 'padding-bottom: ' . esc_attr( $padding_bottom ) . ' !important';
		}
		if ( ! empty( $padding_left ) ) {
			$css_properties[] = 'padding-left: ' . esc_attr( $padding_left ) . ' !important';
		}

		// Margin
		if ( ! empty( $margin_top ) ) {
			$css_properties[] = 'margin-top: ' . esc_attr( $margin_top ) . ' !important';
		}
		if ( ! empty( $margin_right ) ) {
			$css_properties[] = 'margin-right: ' . esc_attr( $margin_right ) . ' !important';
		}
		if ( ! empty( $margin_bottom ) ) {
			$css_properties[] = 'margin-bottom: ' . esc_attr( $margin_bottom ) . ' !important';
		}
		if ( ! empty( $margin_left ) ) {
			$css_properties[] = 'margin-left: ' . esc_attr( $margin_left ) . ' !important';
		}

		// Typography - Skip 'inherit' values to allow theme defaults
		if ( ! empty( $font_family ) && $font_family !== 'inherit' ) {
			$css_properties[] = 'font-family: ' . esc_attr( $font_family ) . ' !important';
		}
		if ( ! empty( $font_size ) ) {
			$css_properties[] = 'font-size: ' . esc_attr( $font_size ) . ' !important';
		}
		if ( ! empty( $font_weight ) && $font_weight !== 'inherit' ) {
			$css_properties[] = 'font-weight: ' . esc_attr( $font_weight ) . ' !important';
		}
		if ( ! empty( $line_height ) ) {
			$css_properties[] = 'line-height: ' . esc_attr( $line_height ) . ' !important';
		}
		if ( ! empty( $text_color ) ) {
			$css_properties[] = 'color: ' . esc_attr( $text_color ) . ' !important';
		}
		if ( ! empty( $letter_spacing ) ) {
			$css_properties[] = 'letter-spacing: ' . esc_attr( $letter_spacing ) . ' !important';
		}
		if ( ! empty( $bg_color ) ) {
			$css_properties[] = 'background-color: ' . esc_attr( $bg_color ) . ' !important';
		}

		// Only output CSS if there are properties to apply
		if ( ! empty( $css_properties ) ) {
			// Use higher specificity selector to override theme/plugin styles
			echo '.fc-checkout-order-review__head .fc-cart-items-count, .fc-cart-items-count { ';
			echo implode( '; ', $css_properties );
			echo '; }';
		}
	}

	/**
	 * Output Progress Bar Full Width CSS
	 *
	 * Ensures the FluidCheckout progress bar spans the full width of its parent container.
	 * This is applied after the progress bar has been repositioned as the first child
	 * of the .fc-inside container via JavaScript.
	 *
	 * Also applies customizer color settings for the progress bar.
	 *
	 * @since 1.0.0
	 */
	private function output_progress_bar_css() {
		// Force progress bar to full width of parent container
		echo '.fc-inside > .fc-progress-bar { ';
		echo 'width: 100% !important; ';
		echo 'max-width: 100% !important; ';
		echo 'margin-left: 0 !important; ';
		echo 'margin-right: 0 !important; ';
		echo 'box-sizing: border-box !important; ';
		echo '}';

		// Ensure progress bar content also spans full width
		echo '.fc-inside > .fc-progress-bar .fc-progress-bar__inner { ';
		echo 'width: 100% !important; ';
		echo 'max-width: 100% !important; ';
		echo '}';

		// Apply customizer color settings
		$bg_color = get_theme_mod( 'blocksy_fc_progress_bar_bg_color', '' );
		$active_color = get_theme_mod( 'blocksy_fc_progress_bar_active_color', '' );
		$inactive_color = get_theme_mod( 'blocksy_fc_progress_bar_inactive_color', '' );
		$text_color = get_theme_mod( 'blocksy_fc_progress_bar_text_color', '' );

		// Progress bar background color
		if ( ! empty( $bg_color ) ) {
			echo '.fc-progress-bar { ';
			echo 'background-color: ' . esc_attr( $bg_color ) . ' !important; ';
			echo '}';
		}

		// Active/completed step color
		if ( ! empty( $active_color ) ) {
			echo '.fc-progress-bar__step--completed, .fc-progress-bar__step--current { ';
			echo 'background-color: ' . esc_attr( $active_color ) . ' !important; ';
			echo '}';

			// Also apply to step indicator circles/bars
			echo '.fc-progress-bar__step--completed::before, .fc-progress-bar__step--current::before { ';
			echo 'background-color: ' . esc_attr( $active_color ) . ' !important; ';
			echo 'border-color: ' . esc_attr( $active_color ) . ' !important; ';
			echo '}';
		}

		// Inactive/incomplete step color
		if ( ! empty( $inactive_color ) ) {
			echo '.fc-progress-bar__step { ';
			echo 'background-color: ' . esc_attr( $inactive_color ) . ' !important; ';
			echo '}';

			// Also apply to step indicator circles/bars
			echo '.fc-progress-bar__step::before { ';
			echo 'background-color: ' . esc_attr( $inactive_color ) . ' !important; ';
			echo 'border-color: ' . esc_attr( $inactive_color ) . ' !important; ';
			echo '}';
		}

		// Progress bar text color
		if ( ! empty( $text_color ) ) {
			echo '.fc-progress-bar__step, .fc-progress-bar__step-label { ';
			echo 'color: ' . esc_attr( $text_color ) . ' !important; ';
			echo '}';
		}
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
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_checkmark_icon_color',
				array(
					'label'       => __( 'Checkmark Icon Color', 'blocksy-child' ),
					'description' => __( 'Color of the checkmark symbol in completed step indicators. Leave empty for theme default.', 'blocksy-child' ),
					'section'     => 'blocksy_fc_step_indicators',
					'priority'    => 10,
				)
			)
		);

		// Checkmark Background Color
		$wp_customize->add_setting(
			'blocksy_fc_checkmark_bg_color',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_checkmark_bg_color',
				array(
					'label'       => __( 'Checkmark Background Color', 'blocksy-child' ),
					'description' => __( 'Background color of the checkmark circle in completed step indicators. Leave empty for theme default.', 'blocksy-child' ),
					'section'     => 'blocksy_fc_step_indicators',
					'priority'    => 20,
				)
			)
		);
	}

	/**
	 * Register Progress Bar Section
	 */
	private function register_progress_bar_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_fc_progress_bar',
			array(
				'title'    => __( 'Progress Bar', 'blocksy-child' ),
				'panel'    => 'blocksy_fluid_checkout_panel',
				'priority' => 76,
			)
		);

		// Progress Bar Background Color
		$wp_customize->add_setting(
			'blocksy_fc_progress_bar_bg_color',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_progress_bar_bg_color',
				array(
					'label'       => __( 'Progress Bar Background Color', 'blocksy-child' ),
					'description' => __( 'Background color of the progress bar container. Leave empty for theme default.', 'blocksy-child' ),
					'section'     => 'blocksy_fc_progress_bar',
					'priority'    => 10,
				)
			)
		);

		// Progress Bar Active Step Color
		$wp_customize->add_setting(
			'blocksy_fc_progress_bar_active_color',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_progress_bar_active_color',
				array(
					'label'       => __( 'Active Step Color', 'blocksy-child' ),
					'description' => __( 'Color of the active/completed steps in the progress bar. Leave empty for theme default.', 'blocksy-child' ),
					'section'     => 'blocksy_fc_progress_bar',
					'priority'    => 20,
				)
			)
		);

		// Progress Bar Inactive Step Color
		$wp_customize->add_setting(
			'blocksy_fc_progress_bar_inactive_color',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_progress_bar_inactive_color',
				array(
					'label'       => __( 'Inactive Step Color', 'blocksy-child' ),
					'description' => __( 'Color of the inactive/incomplete steps in the progress bar. Leave empty for theme default.', 'blocksy-child' ),
					'section'     => 'blocksy_fc_progress_bar',
					'priority'    => 30,
				)
			)
		);

		// Progress Bar Text Color
		$wp_customize->add_setting(
			'blocksy_fc_progress_bar_text_color',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_progress_bar_text_color',
				array(
					'label'       => __( 'Progress Bar Text Color', 'blocksy-child' ),
					'description' => __( 'Text color for step labels in the progress bar. Leave empty for theme default.', 'blocksy-child' ),
					'section'     => 'blocksy_fc_progress_bar',
					'priority'    => 40,
				)
			)
		);
	}

	/**
	 * Register Item Count Badge Section
	 */
	private function register_item_count_badge_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_fc_item_count_badge',
			array(
				'title'    => __( 'Item Count Badge', 'blocksy-child' ),
				'panel'    => 'blocksy_fluid_checkout_panel',
				'priority' => 80,
			)
		);

		// === BORDER CONTROLS ===

		// Border Width Top
		$wp_customize->add_setting(
			'blocksy_fc_item_count_border_top',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_border_top',
			array(
				'label'       => __( 'Border Width Top', 'blocksy-child' ),
				'description' => __( 'Top border width (e.g., 1px, 2px). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 10,
			)
		);

		// Border Width Right
		$wp_customize->add_setting(
			'blocksy_fc_item_count_border_right',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_border_right',
			array(
				'label'       => __( 'Border Width Right', 'blocksy-child' ),
				'description' => __( 'Right border width (e.g., 1px, 2px). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 20,
			)
		);

		// Border Width Bottom
		$wp_customize->add_setting(
			'blocksy_fc_item_count_border_bottom',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_border_bottom',
			array(
				'label'       => __( 'Border Width Bottom', 'blocksy-child' ),
				'description' => __( 'Bottom border width (e.g., 1px, 2px). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 30,
			)
		);

		// Border Width Left
		$wp_customize->add_setting(
			'blocksy_fc_item_count_border_left',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_border_left',
			array(
				'label'       => __( 'Border Width Left', 'blocksy-child' ),
				'description' => __( 'Left border width (e.g., 1px, 2px). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 40,
			)
		);

		// Border Style
		$wp_customize->add_setting(
			'blocksy_fc_item_count_border_style',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_border_style',
			array(
				'label'       => __( 'Border Style', 'blocksy-child' ),
				'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'select',
				'priority'    => 50,
				'choices'     => array(
					''       => __( 'Theme Default', 'blocksy-child' ),
					'none'   => __( 'None', 'blocksy-child' ),
					'solid'  => __( 'Solid', 'blocksy-child' ),
					'dashed' => __( 'Dashed', 'blocksy-child' ),
					'dotted' => __( 'Dotted', 'blocksy-child' ),
					'double' => __( 'Double', 'blocksy-child' ),
				),
			)
		);

		// Border Color
		$wp_customize->add_setting(
			'blocksy_fc_item_count_border_color',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_item_count_border_color',
				array(
					'label'       => __( 'Border Color', 'blocksy-child' ),
					'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
					'section'     => 'blocksy_fc_item_count_badge',
					'priority'    => 60,
				)
			)
		);

		// Border Radius
		$wp_customize->add_setting(
			'blocksy_fc_item_count_border_radius',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_border_radius',
			array(
				'label'       => __( 'Border Radius', 'blocksy-child' ),
				'description' => __( 'Rounded corners (e.g., 4px, 0.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 70,
			)
		);

		// === SPACING CONTROLS ===

		// Padding Top
		$wp_customize->add_setting(
			'blocksy_fc_item_count_padding_top',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_padding_top',
			array(
				'label'       => __( 'Padding Top', 'blocksy-child' ),
				'description' => __( 'Top padding (e.g., 4px, 0.25rem). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 80,
			)
		);

		// Padding Right
		$wp_customize->add_setting(
			'blocksy_fc_item_count_padding_right',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_padding_right',
			array(
				'label'       => __( 'Padding Right', 'blocksy-child' ),
				'description' => __( 'Right padding (e.g., 8px, 0.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 90,
			)
		);

		// Padding Bottom
		$wp_customize->add_setting(
			'blocksy_fc_item_count_padding_bottom',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_padding_bottom',
			array(
				'label'       => __( 'Padding Bottom', 'blocksy-child' ),
				'description' => __( 'Bottom padding (e.g., 4px, 0.25rem). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 100,
			)
		);

		// Padding Left
		$wp_customize->add_setting(
			'blocksy_fc_item_count_padding_left',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_padding_left',
			array(
				'label'       => __( 'Padding Left', 'blocksy-child' ),
				'description' => __( 'Left padding (e.g., 8px, 0.5rem). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 110,
			)
		);

		// Margin Top
		$wp_customize->add_setting(
			'blocksy_fc_item_count_margin_top',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_margin_top',
			array(
				'label'       => __( 'Margin Top', 'blocksy-child' ),
				'description' => __( 'Top margin (e.g., 0px). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 120,
			)
		);

		// Margin Right
		$wp_customize->add_setting(
			'blocksy_fc_item_count_margin_right',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_margin_right',
			array(
				'label'       => __( 'Margin Right', 'blocksy-child' ),
				'description' => __( 'Right margin (e.g., 0px). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 130,
			)
		);

		// Margin Bottom
		$wp_customize->add_setting(
			'blocksy_fc_item_count_margin_bottom',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_margin_bottom',
			array(
				'label'       => __( 'Margin Bottom', 'blocksy-child' ),
				'description' => __( 'Bottom margin (e.g., 0px). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 140,
			)
		);

		// Margin Left
		$wp_customize->add_setting(
			'blocksy_fc_item_count_margin_left',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_margin_left',
			array(
				'label'       => __( 'Margin Left', 'blocksy-child' ),
				'description' => __( 'Left margin (e.g., 0px). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 150,
			)
		);

		// === TYPOGRAPHY CONTROLS ===

		// Font Family
		$wp_customize->add_setting(
			'blocksy_fc_item_count_font_family',
			array(
				'default'           => 'inherit',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_font_family',
			array(
				'label'       => __( 'Font Family', 'blocksy-child' ),
				'description' => __( 'Select a font family for the item count badge', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'select',
				'priority'    => 160,
				'choices'     => $this->get_font_family_choices(),
			)
		);

		// Font Size
		$wp_customize->add_setting(
			'blocksy_fc_item_count_font_size',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_font_size',
			array(
				'label'       => __( 'Font Size', 'blocksy-child' ),
				'description' => __( 'Enter size with CSS unit (e.g., 15px, 1rem). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 170,
			)
		);

		// Font Weight
		$wp_customize->add_setting(
			'blocksy_fc_item_count_font_weight',
			array(
				'default'           => 'inherit',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_font_weight',
			array(
				'label'    => __( 'Font Weight', 'blocksy-child' ),
				'section'  => 'blocksy_fc_item_count_badge',
				'type'     => 'select',
				'priority' => 180,
				'choices'  => array(
					'inherit' => __( 'Theme Default (Inherit)', 'blocksy-child' ),
					'100'     => __( 'Thin (100)', 'blocksy-child' ),
					'200'     => __( 'Extra Light (200)', 'blocksy-child' ),
					'300'     => __( 'Light (300)', 'blocksy-child' ),
					'400'     => __( 'Normal (400)', 'blocksy-child' ),
					'500'     => __( 'Medium (500)', 'blocksy-child' ),
					'600'     => __( 'Semi Bold (600)', 'blocksy-child' ),
					'700'     => __( 'Bold (700)', 'blocksy-child' ),
					'800'     => __( 'Extra Bold (800)', 'blocksy-child' ),
					'900'     => __( 'Black (900)', 'blocksy-child' ),
				),
			)
		);

		// Line Height
		$wp_customize->add_setting(
			'blocksy_fc_item_count_line_height',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_line_height',
			array(
				'label'       => __( 'Line Height', 'blocksy-child' ),
				'description' => __( 'Enter line height (e.g., 18px, 1.5). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 190,
			)
		);

		// Text Color
		$wp_customize->add_setting(
			'blocksy_fc_item_count_text_color',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_item_count_text_color',
				array(
					'label'       => __( 'Text Color', 'blocksy-child' ),
					'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
					'section'     => 'blocksy_fc_item_count_badge',
					'priority'    => 200,
				)
			)
		);

		// Letter Spacing
		$wp_customize->add_setting(
			'blocksy_fc_item_count_letter_spacing',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_fc_item_count_letter_spacing',
			array(
				'label'       => __( 'Letter Spacing', 'blocksy-child' ),
				'description' => __( 'Enter letter spacing (e.g., 0.5px, normal). Leave empty for theme default.', 'blocksy-child' ),
				'section'     => 'blocksy_fc_item_count_badge',
				'type'        => 'text',
				'priority'    => 210,
			)
		);

		// Background Color
		$wp_customize->add_setting(
			'blocksy_fc_item_count_bg_color',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_color_allow_empty' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_fc_item_count_bg_color',
				array(
					'label'       => __( 'Background Color', 'blocksy-child' ),
					'description' => __( 'Leave empty for theme default', 'blocksy-child' ),
					'section'     => 'blocksy_fc_item_count_badge',
					'priority'    => 220,
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

