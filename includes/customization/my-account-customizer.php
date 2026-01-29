<?php
/**
 * My Account Customizer Integration
 *
 * Integrates my-account form customization into WordPress Customizer
 * for live preview and better user experience.
 *
 * @package Blocksy_Child
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Blocksy_Child_My_Account_Customizer {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'customize_register', array( $this, 'register_customizer_settings' ) );
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );
		add_action( 'wp_head', array( $this, 'output_customizer_css' ) );
		add_action( 'customize_register', array( $this, 'add_selective_refresh' ) );
	}

	/**
	 * Register all customizer settings, controls, and sections
	 */
	public function register_customizer_settings( $wp_customize ) {
		$wp_customize->add_panel(
			'blocksy_my_account_panel',
			array(
				'title'       => __( 'My Account Form', 'blocksy-child' ),
				'description' => __( 'Customize WooCommerce login and register forms.', 'blocksy-child' ),
				'priority'    => 160,
				'capability'  => 'edit_theme_options',
			)
		);

		// Register sections in logical order
		$this->register_template_section( $wp_customize );
		$this->register_heading_typography_section( $wp_customize );
		$this->register_body_typography_section( $wp_customize );
		$this->register_input_fields_section( $wp_customize );
		$this->register_button_styling_section( $wp_customize );
		$this->register_form_layout_section( $wp_customize );
		$this->register_account_navigation_section( $wp_customize );
		$this->register_footer_text_section( $wp_customize );
		$this->register_responsive_sections( $wp_customize );
		$this->register_custom_css_section( $wp_customize );
	}

	/**
	 * 1. Template Selection Section (Priority 10)
	 */
	private function register_template_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_my_account_template',
			array(
				'title'    => __( 'Template Selection', 'blocksy-child' ),
				'panel'    => 'blocksy_my_account_panel',
				'priority' => 10,
			)
		);

		$wp_customize->add_setting(
			'blocksy_child_my_account_template',
			array(
				'default'           => 'default',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'blocksy_child_my_account_template',
			array(
				'label'       => __( 'Select Template', 'blocksy-child' ),
				'description' => __( 'Choose the template design for your login/register forms.', 'blocksy-child' ),
				'section'     => 'blocksy_my_account_template',
				'type'        => 'select',
				'choices'     => array(
					'default'   => __( 'Default WooCommerce', 'blocksy-child' ),
					'template1' => __( 'Template 1 - Side by Side', 'blocksy-child' ),
					'template2' => __( 'Template 2 - Centered', 'blocksy-child' ),
				),
			)
		);
	}

	/**
	 * 2. Heading Typography Section (Priority 20)
	 */
	private function register_heading_typography_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_my_account_heading_typography',
			array(
				'title'    => __( 'Heading Typography', 'blocksy-child' ),
				'panel'    => 'blocksy_my_account_panel',
				'priority' => 20,
			)
		);

		$this->register_typography_controls( $wp_customize, 'heading', 'blocksy_my_account_heading_typography' );
		$this->register_text_align_control( $wp_customize, 'heading', 'blocksy_my_account_heading_typography' );
	}

	/**
	 * 3. Body Text Typography Section (Priority 30)
	 */
	private function register_body_typography_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_my_account_body_typography',
			array(
				'title'    => __( 'Body Text Typography', 'blocksy-child' ),
				'panel'    => 'blocksy_my_account_panel',
				'priority' => 30,
			)
		);

		$this->register_typography_controls( $wp_customize, 'body', 'blocksy_my_account_body_typography' );
		$this->register_text_align_control( $wp_customize, 'body', 'blocksy_my_account_body_typography' );
	}

	/**
	 * 4. Input Fields Section (Priority 40)
	 * Combines: Input colors + Placeholder typography
	 */
	private function register_input_fields_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_my_account_input_fields',
			array(
				'title'       => __( 'Input Fields', 'blocksy-child' ),
				'description' => __( 'Customize input field colors and placeholder text styling.', 'blocksy-child' ),
				'panel'       => 'blocksy_my_account_panel',
				'priority'    => 40,
			)
		);

		// Input Colors
		$input_colors = array(
			'input_background_color' => array(
				'label'   => __( 'Input Background', 'blocksy-child' ),
				'default' => '#ffffff',
			),
			'input_border_color'     => array(
				'label'   => __( 'Input Border', 'blocksy-child' ),
				'default' => '#dddddd',
			),
			'input_text_color'       => array(
				'label'   => __( 'Input Text', 'blocksy-child' ),
				'default' => '#333333',
			),
		);

		foreach ( $input_colors as $key => $config ) {
			$wp_customize->add_setting(
				"blocksy_child_my_account_{$key}",
				array(
					'default'           => $config['default'],
					'sanitize_callback' => 'sanitize_hex_color',
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					"blocksy_child_my_account_{$key}",
					array(
						'label'   => $config['label'],
						'section' => 'blocksy_my_account_input_fields',
					)
				)
			);
		}

		// Placeholder Typography (merged into Input Fields)
		$this->register_typography_controls( $wp_customize, 'placeholder', 'blocksy_my_account_input_fields' );
	}

	/**
	 * 5. Button Styling Section (Priority 50)
	 * Combines: Button typography + Button colors + Button spacing
	 */
	private function register_button_styling_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_my_account_button_styling',
			array(
				'title'       => __( 'Button Styling', 'blocksy-child' ),
				'description' => __( 'Customize button typography, colors, and dimensions.', 'blocksy-child' ),
				'panel'       => 'blocksy_my_account_panel',
				'priority'    => 50,
			)
		);

		// Button Typography
		$this->register_typography_controls( $wp_customize, 'button', 'blocksy_my_account_button_styling' );

		// Button Colors
		$button_colors = array(
			'button_color'            => array(
				'label'   => __( 'Button Background', 'blocksy-child' ),
				'default' => '#007cba',
			),
			'button_text_color'       => array(
				'label'   => __( 'Button Text', 'blocksy-child' ),
				'default' => '#ffffff',
			),
			'button_hover_color'      => array(
				'label'   => __( 'Button Hover Background', 'blocksy-child' ),
				'default' => '#005a87',
			),
			'button_hover_text_color' => array(
				'label'   => __( 'Button Hover Text', 'blocksy-child' ),
				'default' => '#ffffff',
			),
		);

		foreach ( $button_colors as $key => $config ) {
			$wp_customize->add_setting(
				"blocksy_child_my_account_{$key}",
				array(
					'default'           => $config['default'],
					'sanitize_callback' => 'sanitize_hex_color',
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					"blocksy_child_my_account_{$key}",
					array(
						'label'   => $config['label'],
						'section' => 'blocksy_my_account_button_styling',
					)
				)
			);
		}

		// Button Padding
		$padding_sides = array(
			'top'    => array(
				'label'   => __( 'Top Padding', 'blocksy-child' ),
				'default' => '12px',
			),
			'right'  => array(
				'label'   => __( 'Right Padding', 'blocksy-child' ),
				'default' => '24px',
			),
			'bottom' => array(
				'label'   => __( 'Bottom Padding', 'blocksy-child' ),
				'default' => '12px',
			),
			'left'   => array(
				'label'   => __( 'Left Padding', 'blocksy-child' ),
				'default' => '24px',
			),
		);

		foreach ( $padding_sides as $side => $config ) {
			$wp_customize->add_setting(
				"blocksy_child_my_account_button_padding_{$side}",
				array(
					'default'           => $config['default'],
					'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				"blocksy_child_my_account_button_padding_{$side}",
				array(
					'label'   => $config['label'],
					'section' => 'blocksy_my_account_button_styling',
					'type'    => 'text',
				)
			);
		}

		// Button Border Radius
		$wp_customize->add_setting(
			'blocksy_child_my_account_button_border_radius',
			array(
				'default'           => '3px',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_child_my_account_button_border_radius',
			array(
				'label'   => __( 'Button Border Radius', 'blocksy-child' ),
				'section' => 'blocksy_my_account_button_styling',
				'type'    => 'text',
			)
		);
	}

	/**
	 * 6. Form Layout Section (Priority 60)
	 * Renamed from "Form Elements"
	 */
	private function register_form_layout_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_my_account_form_layout',
			array(
				'title'       => __( 'Form Layout', 'blocksy-child' ),
				'description' => __( 'Customize form container and element styling.', 'blocksy-child' ),
				'panel'       => 'blocksy_my_account_panel',
				'priority'    => 60,
			)
		);

		// Column Border Radius
		$wp_customize->add_setting(
			'blocksy_child_my_account_column_border_radius',
			array(
				'default'           => '12px',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_child_my_account_column_border_radius',
			array(
				'label'       => __( 'Form Container Border Radius', 'blocksy-child' ),
				'description' => __( 'Border radius for login/register columns (e.g., 12px)', 'blocksy-child' ),
				'section'     => 'blocksy_my_account_form_layout',
				'type'        => 'text',
			)
		);

		// Checkbox Border Color
		$wp_customize->add_setting(
			'blocksy_child_my_account_checkbox_border_color',
			array(
				'default'           => '#CDD1D4',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_child_my_account_checkbox_border_color',
				array(
					'label'   => __( 'Checkbox Border Color', 'blocksy-child' ),
					'section' => 'blocksy_my_account_form_layout',
				)
			)
		);

		// Required Field Color
		$wp_customize->add_setting(
			'blocksy_child_my_account_required_field_color',
			array(
				'default'           => '#ff0000',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'blocksy_child_my_account_required_field_color',
				array(
					'label'   => __( 'Required Field Asterisk Color', 'blocksy-child' ),
					'section' => 'blocksy_my_account_form_layout',
				)
			)
		);
	}

	/**
	 * 7. Account Navigation Section (Priority 70)
	 */
	private function register_account_navigation_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_my_account_navigation',
			array(
				'title'       => __( 'Account Navigation', 'blocksy-child' ),
				'description' => __( 'Customize the account dashboard navigation menu.', 'blocksy-child' ),
				'panel'       => 'blocksy_my_account_panel',
				'priority'    => 70,
			)
		);

		$nav_colors = array(
			'nav_border_color'      => array(
				'label'   => __( 'Navigation Border Color', 'blocksy-child' ),
				'default' => '#CDD1D4',
			),
			'nav_text_color'        => array(
				'label'   => __( 'Navigation Text Color', 'blocksy-child' ),
				'default' => '#242424',
			),
			'nav_active_text_color' => array(
				'label'   => __( 'Active/Hover Text Color', 'blocksy-child' ),
				'default' => '#1ED760',
			),
			'nav_active_color'      => array(
				'label'   => __( 'Active/Hover Background Color', 'blocksy-child' ),
				'default' => '#be252f',
			),
		);

		foreach ( $nav_colors as $key => $config ) {
			$wp_customize->add_setting(
				"blocksy_child_my_account_{$key}",
				array(
					'default'           => $config['default'],
					'sanitize_callback' => 'sanitize_hex_color',
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					"blocksy_child_my_account_{$key}",
					array(
						'label'   => $config['label'],
						'section' => 'blocksy_my_account_navigation',
					)
				)
			);
		}
	}

	/**
	 * 8. Footer Text Section (Priority 80)
	 */
	private function register_footer_text_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_my_account_footer_text',
			array(
				'title'       => __( 'Footer Text', 'blocksy-child' ),
				'description' => __( 'Customize footer links and privacy policy text size.', 'blocksy-child' ),
				'panel'       => 'blocksy_my_account_panel',
				'priority'    => 80,
			)
		);

		$wp_customize->add_setting(
			'blocksy_child_my_account_footer_font_size_desktop',
			array(
				'default'           => '14px',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_child_my_account_footer_font_size_desktop',
			array(
				'label'   => __( 'Desktop Font Size', 'blocksy-child' ),
				'section' => 'blocksy_my_account_footer_text',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'blocksy_child_my_account_footer_font_size_mobile',
			array(
				'default'           => '12px',
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_child_my_account_footer_font_size_mobile',
			array(
				'label'   => __( 'Mobile Font Size', 'blocksy-child' ),
				'section' => 'blocksy_my_account_footer_text',
				'type'    => 'text',
			)
		);
	}

	/**
	 * 9 & 10. Responsive Sections (Priority 90, 100)
	 */
	private function register_responsive_sections( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_my_account_tablet',
			array(
				'title'       => __( 'Tablet Responsive', 'blocksy-child' ),
				'description' => __( 'Override settings for tablet devices (768px - 1023px).', 'blocksy-child' ),
				'panel'       => 'blocksy_my_account_panel',
				'priority'    => 90,
			)
		);

		$wp_customize->add_section(
			'blocksy_my_account_mobile',
			array(
				'title'       => __( 'Mobile Responsive', 'blocksy-child' ),
				'description' => __( 'Override settings for mobile devices (< 768px).', 'blocksy-child' ),
				'panel'       => 'blocksy_my_account_panel',
				'priority'    => 100,
			)
		);

		$this->register_responsive_controls( $wp_customize, 'tablet', 'blocksy_my_account_tablet' );
		$this->register_responsive_controls( $wp_customize, 'mobile', 'blocksy_my_account_mobile' );
	}

	/**
	 * 11. Custom CSS Section (Priority 110)
	 */
	private function register_custom_css_section( $wp_customize ) {
		$wp_customize->add_section(
			'blocksy_my_account_custom_css',
			array(
				'title'       => __( 'Custom CSS', 'blocksy-child' ),
				'description' => __( 'Add your own CSS to customize the My Account forms. This CSS will only apply to the My Account pages.', 'blocksy-child' ),
				'panel'       => 'blocksy_my_account_panel',
				'priority'    => 110,
			)
		);

		$wp_customize->add_setting(
			'blocksy_child_my_account_custom_css',
			array(
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_custom_css' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'blocksy_child_my_account_custom_css',
			array(
				'label'       => __( 'Custom CSS', 'blocksy-child' ),
				'description' => __( 'Enter your custom CSS code here. Do not include &lt;style&gt; tags.', 'blocksy-child' ),
				'section'     => 'blocksy_my_account_custom_css',
				'type'        => 'textarea',
				'input_attrs' => array(
					'rows'        => 15,
					'placeholder' => "/* Example */\n.blaze-login-register h2 {\n    color: #333;\n}",
					'style'       => 'font-family: monospace; font-size: 12px;',
				),
			)
		);
	}

	/**
	 * Sanitize custom CSS
	 */
	public function sanitize_custom_css( $css ) {
		// Remove any script tags
		$css = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $css );
		// Remove style tags (user shouldn't include them)
		$css = preg_replace( '/<\/?style[^>]*>/i', '', $css );
		// Strip HTML tags
		$css = wp_strip_all_tags( $css );
		return $css;
	}

	/**
	 * Register typography controls for a specific element
	 */
	private function register_typography_controls( $wp_customize, $element, $section ) {
		$defaults = $this->get_typography_defaults( $element );
		$prefix   = "blocksy_child_my_account_{$element}";

		// Font Family
		$wp_customize->add_setting(
			"{$prefix}_font",
			array(
				'default'           => $defaults['font'],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			"{$prefix}_font",
			array(
				'label'   => __( 'Font Family', 'blocksy-child' ),
				'section' => $section,
				'type'    => 'text',
			)
		);

		// Font Size
		$wp_customize->add_setting(
			"{$prefix}_font_size",
			array(
				'default'           => $defaults['size'],
				'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			"{$prefix}_font_size",
			array(
				'label'   => __( 'Font Size', 'blocksy-child' ),
				'section' => $section,
				'type'    => 'text',
			)
		);

		// Font Color
		$wp_customize->add_setting(
			"{$prefix}_font_color",
			array(
				'default'           => $defaults['color'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				"{$prefix}_font_color",
				array(
					'label'   => __( 'Font Color', 'blocksy-child' ),
					'section' => $section,
				)
			)
		);

		// Font Weight
		$wp_customize->add_setting(
			"{$prefix}_font_weight",
			array(
				'default'           => $defaults['weight'],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			"{$prefix}_font_weight",
			array(
				'label'   => __( 'Font Weight', 'blocksy-child' ),
				'section' => $section,
				'type'    => 'select',
				'choices' => array(
					'300' => __( 'Light (300)', 'blocksy-child' ),
					'400' => __( 'Normal (400)', 'blocksy-child' ),
					'500' => __( 'Medium (500)', 'blocksy-child' ),
					'600' => __( 'Semi Bold (600)', 'blocksy-child' ),
					'700' => __( 'Bold (700)', 'blocksy-child' ),
				),
			)
		);

		// Text Transform
		$wp_customize->add_setting(
			"{$prefix}_text_transform",
			array(
				'default'           => $defaults['transform'],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			"{$prefix}_text_transform",
			array(
				'label'   => __( 'Text Transform', 'blocksy-child' ),
				'section' => $section,
				'type'    => 'select',
				'choices' => array(
					'none'       => __( 'None', 'blocksy-child' ),
					'uppercase'  => __( 'Uppercase', 'blocksy-child' ),
					'lowercase'  => __( 'Lowercase', 'blocksy-child' ),
					'capitalize' => __( 'Capitalize', 'blocksy-child' ),
				),
			)
		);
	}

	/**
	 * Register text alignment control
	 */
	private function register_text_align_control( $wp_customize, $element, $section ) {
		$wp_customize->add_setting(
			"blocksy_child_my_account_{$element}_text_align",
			array(
				'default'           => 'left',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			"blocksy_child_my_account_{$element}_text_align",
			array(
				'label'   => __( 'Text Alignment', 'blocksy-child' ),
				'section' => $section,
				'type'    => 'select',
				'choices' => array(
					'left'   => __( 'Left', 'blocksy-child' ),
					'center' => __( 'Center', 'blocksy-child' ),
					'right'  => __( 'Right', 'blocksy-child' ),
				),
			)
		);
	}

	/**
	 * Register responsive controls for tablet or mobile
	 */
	private function register_responsive_controls( $wp_customize, $device, $section ) {
		$elements = array( 'heading', 'body', 'placeholder', 'button' );

		foreach ( $elements as $element ) {
			$prefix = "blocksy_child_my_account_{$device}_{$element}";

			$wp_customize->add_setting(
				"{$prefix}_font_size",
				array(
					'default'           => '',
					'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				"{$prefix}_font_size",
				array(
					'label'   => sprintf( __( '%s Font Size', 'blocksy-child' ), ucfirst( $element ) ),
					'section' => $section,
					'type'    => 'text',
				)
			);

			$wp_customize->add_setting(
				"{$prefix}_font_weight",
				array(
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				"{$prefix}_font_weight",
				array(
					'label'   => sprintf( __( '%s Font Weight', 'blocksy-child' ), ucfirst( $element ) ),
					'section' => $section,
					'type'    => 'select',
					'choices' => array(
						''    => __( 'Use Desktop Setting', 'blocksy-child' ),
						'300' => __( 'Light (300)', 'blocksy-child' ),
						'400' => __( 'Normal (400)', 'blocksy-child' ),
						'500' => __( 'Medium (500)', 'blocksy-child' ),
						'600' => __( 'Semi Bold (600)', 'blocksy-child' ),
						'700' => __( 'Bold (700)', 'blocksy-child' ),
					),
				)
			);
		}
	}

	/**
	 * Get typography defaults for different elements
	 */
	private function get_typography_defaults( $element ) {
		$defaults = array(
			'heading'     => array(
				'font'      => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				'size'      => '24px',
				'color'     => '#333333',
				'weight'    => '600',
				'transform' => 'none',
			),
			'body'        => array(
				'font'      => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				'size'      => '16px',
				'color'     => '#666666',
				'weight'    => '400',
				'transform' => 'none',
			),
			'placeholder' => array(
				'font'      => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				'size'      => '14px',
				'color'     => '#999999',
				'weight'    => '400',
				'transform' => 'none',
			),
			'button'      => array(
				'font'      => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				'size'      => '16px',
				'color'     => '#ffffff',
				'weight'    => '600',
				'transform' => 'none',
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
	 * Output customizer CSS in head
	 */
	public function output_customizer_css() {
		if ( ! $this->is_my_account_page() && ! is_customize_preview() ) {
			return;
		}

		$template = get_theme_mod( 'blocksy_child_my_account_template', 'default' );

		if ( 'default' === $template ) {
			return;
		}

		$this->generate_customizer_css( $template );
	}

	/**
	 * Check if current page is my-account related
	 */
	private function is_my_account_page() {
		if ( ! function_exists( 'is_account_page' ) ) {
			return false;
		}

		return is_account_page() || is_page( 'my-account' );
	}

	/**
	 * Generate CSS from customizer settings
	 */
	private function generate_customizer_css( $template ) {
		?>
		<style type="text/css" id="blocksy-my-account-customizer-css">
			<?php echo $this->generate_desktop_css( $template ); ?>
			<?php echo $this->generate_responsive_css( $template ); ?>
		</style>
		<?php
	}

	/**
	 * Generate desktop CSS
	 */
	private function generate_desktop_css( $template ) {
		$css      = '';
		$elements = array( 'heading', 'body', 'placeholder', 'button' );

		foreach ( $elements as $element ) {
			$defaults       = $this->get_typography_defaults( $element );
			$font_family    = get_theme_mod( "blocksy_child_my_account_{$element}_font", $defaults['font'] );
			$font_size      = get_theme_mod( "blocksy_child_my_account_{$element}_font_size", $defaults['size'] );
			$font_color     = get_theme_mod( "blocksy_child_my_account_{$element}_font_color", $defaults['color'] );
			$font_weight    = get_theme_mod( "blocksy_child_my_account_{$element}_font_weight", $defaults['weight'] );
			$text_transform = get_theme_mod( "blocksy_child_my_account_{$element}_text_transform", $defaults['transform'] );
			$text_align     = get_theme_mod( "blocksy_child_my_account_{$element}_text_align", 'left' );

			$selector = $this->get_element_selector( $element, $template );

			if ( $selector ) {
				$css .= "{$selector} {";
				$css .= "font-family: {$font_family} !important;";
				$css .= "font-size: {$font_size} !important;";
				$css .= "color: {$font_color} !important;";
				$css .= "font-weight: {$font_weight} !important;";
				$css .= "text-transform: {$text_transform} !important;";
				if ( in_array( $element, array( 'heading', 'body' ), true ) ) {
					$css .= "text-align: {$text_align} !important;";
				}
				$css .= '}';
			}
		}

		// Button styles
		$button_bg           = get_theme_mod( 'blocksy_child_my_account_button_color', '#007cba' );
		$button_text         = get_theme_mod( 'blocksy_child_my_account_button_text_color', '#ffffff' );
		$button_hover_bg     = get_theme_mod( 'blocksy_child_my_account_button_hover_color', '#005a87' );
		$button_hover_text   = get_theme_mod( 'blocksy_child_my_account_button_hover_text_color', '#ffffff' );
		$padding_top         = get_theme_mod( 'blocksy_child_my_account_button_padding_top', '12px' );
		$padding_right       = get_theme_mod( 'blocksy_child_my_account_button_padding_right', '24px' );
		$padding_bottom      = get_theme_mod( 'blocksy_child_my_account_button_padding_bottom', '12px' );
		$padding_left        = get_theme_mod( 'blocksy_child_my_account_button_padding_left', '24px' );
		$button_border_radius = get_theme_mod( 'blocksy_child_my_account_button_border_radius', '3px' );

		$css .= ".blaze-login-register.{$template} button, .blaze-login-register.{$template} .button {";
		$css .= "background-color: {$button_bg} !important;";
		$css .= "color: {$button_text} !important;";
		$css .= "padding: {$padding_top} {$padding_right} {$padding_bottom} {$padding_left} !important;";
		$css .= "border-radius: {$button_border_radius} !important;";
		$css .= '}';

		$css .= ".blaze-login-register.{$template} button:hover, .blaze-login-register.{$template} .button:hover {";
		$css .= "background-color: {$button_hover_bg} !important;";
		$css .= "color: {$button_hover_text} !important;";
		$css .= '}';

		// Input styles
		$input_bg     = get_theme_mod( 'blocksy_child_my_account_input_background_color', '#ffffff' );
		$input_border = get_theme_mod( 'blocksy_child_my_account_input_border_color', '#dddddd' );
		$input_text   = get_theme_mod( 'blocksy_child_my_account_input_text_color', '#333333' );

		$css .= ".blaze-login-register.{$template} input[type=\"text\"], ";
		$css .= ".blaze-login-register.{$template} input[type=\"email\"], ";
		$css .= ".blaze-login-register.{$template} input[type=\"password\"] {";
		$css .= "background-color: {$input_bg} !important;";
		$css .= "border-color: {$input_border} !important;";
		$css .= "color: {$input_text} !important;";
		$css .= '}';

		// Form layout styles
		$column_border_radius = get_theme_mod( 'blocksy_child_my_account_column_border_radius', '12px' );
		$checkbox_border      = get_theme_mod( 'blocksy_child_my_account_checkbox_border_color', '#CDD1D4' );
		$required_color       = get_theme_mod( 'blocksy_child_my_account_required_field_color', '#ff0000' );

		$css .= ".blaze-column { border-radius: {$column_border_radius}; }";
		$css .= ".blaze-login-register input.woocommerce-form__input-checkbox { border-color: {$checkbox_border} !important; }";
		$css .= ".blaze-login-register span .required, .blaze-login-register.template1 span.required { color: {$required_color} !important; }";

		// Footer text styles
		$footer_desktop = get_theme_mod( 'blocksy_child_my_account_footer_font_size_desktop', '14px' );
		$footer_mobile  = get_theme_mod( 'blocksy_child_my_account_footer_font_size_mobile', '12px' );

		$css .= '.blaze-login-register .login-form-footer span, .blaze-login-register .login-form-footer a, ';
		$css .= '.blaze-login-register .woocommerce-privacy-policy-text p, .blaze-login-register .woocommerce-privacy-policy-text p a {';
		$css .= "font-size: {$footer_desktop} !important;";
		$css .= '}';

		$css .= '@media (max-width: 768px) {';
		$css .= '.blaze-login-register .login-form-footer span, .blaze-login-register .login-form-footer a, ';
		$css .= '.blaze-login-register .woocommerce-privacy-policy-text p, .blaze-login-register .woocommerce-privacy-policy-text p a {';
		$css .= "font-size: {$footer_mobile} !important;";
		$css .= '}}';

		// Account navigation styles
		$nav_border      = get_theme_mod( 'blocksy_child_my_account_nav_border_color', '#CDD1D4' );
		$nav_text        = get_theme_mod( 'blocksy_child_my_account_nav_text_color', '#242424' );
		$nav_active_text = get_theme_mod( 'blocksy_child_my_account_nav_active_text_color', '#1ED760' );
		$nav_active_bg   = get_theme_mod( 'blocksy_child_my_account_nav_active_color', '#be252f' );

		$css .= ".blz-my_account .ct-acount-nav { border: 1px solid {$nav_border} !important; }";
		$css .= ".blz-my_account p, .blz-my_account a { color: {$nav_text}; }";
		$css .= ".blz-my_account ul li.is-active a, .blz-my_account ul li:hover a { color: {$nav_active_text} !important; }";
		$css .= ".blz-my_account ul li.is-active, .blz-my_account ul li:hover { --account-nav-background-active-color: {$nav_active_bg}; }";

		// Custom CSS
		$custom_css = get_theme_mod( 'blocksy_child_my_account_custom_css', '' );
		if ( ! empty( $custom_css ) ) {
			$css .= '/* Custom CSS */' . $custom_css;
		}

		return $css;
	}

	/**
	 * Generate responsive CSS
	 */
	private function generate_responsive_css( $template ) {
		$css = '';

		$tablet_css = $this->generate_device_css( 'tablet', $template );
		if ( $tablet_css ) {
			$css .= '@media (max-width: 1023px) and (min-width: 768px) {' . $tablet_css . '}';
		}

		$mobile_css = $this->generate_device_css( 'mobile', $template );
		if ( $mobile_css ) {
			$css .= '@media (max-width: 767px) {' . $mobile_css . '}';
		}

		return $css;
	}

	/**
	 * Generate CSS for specific device
	 */
	private function generate_device_css( $device, $template ) {
		$css      = '';
		$elements = array( 'heading', 'body', 'placeholder', 'button' );

		foreach ( $elements as $element ) {
			$font_size   = get_theme_mod( "blocksy_child_my_account_{$device}_{$element}_font_size", '' );
			$font_weight = get_theme_mod( "blocksy_child_my_account_{$device}_{$element}_font_weight", '' );

			if ( $font_size || $font_weight ) {
				$selector = $this->get_element_selector( $element, $template );

				if ( $selector ) {
					$css .= "{$selector} {";
					if ( $font_size ) {
						$css .= "font-size: {$font_size} !important;";
					}
					if ( $font_weight ) {
						$css .= "font-weight: {$font_weight} !important;";
					}
					$css .= '}';
				}
			}
		}

		return $css;
	}

	/**
	 * Get CSS selector for element
	 */
	private function get_element_selector( $element, $template ) {
		$selectors = array(
			'heading'     => ".blaze-login-register.{$template} h2",
			'body'        => ".blaze-login-register.{$template} p, .blaze-login-register.{$template} label, .blaze-login-register.{$template} span, .blaze-login-register.{$template} a",
			'placeholder' => ".blaze-login-register.{$template} input::placeholder",
			'button'      => ".blaze-login-register.{$template} button, .blaze-login-register.{$template} .button",
		);

		return isset( $selectors[ $element ] ) ? $selectors[ $element ] : '';
	}

	/**
	 * Enqueue preview scripts for live preview
	 */
	public function enqueue_preview_scripts() {
		wp_enqueue_script(
			'blocksy-my-account-customizer-preview',
			get_stylesheet_directory_uri() . '/assets/js/my-account-customizer-preview.js',
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

		$wp_customize->selective_refresh->add_partial(
			'blocksy_child_my_account_template',
			array(
				'selector'        => '.woocommerce-account .woocommerce',
				'render_callback' => '__return_false',
			)
		);
	}
}

new Blocksy_Child_My_Account_Customizer();
