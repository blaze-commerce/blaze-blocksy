<?php
/**
 * My Account Customizer Integration
 * 
 * Integrates my-account form customization into WordPress Customizer
 * for live preview and better user experience.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Blocksy_Child_My_Account_Customizer {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register customizer hooks
        add_action( 'customize_register', array( $this, 'register_customizer_settings' ) );
        add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );
        add_action( 'wp_head', array( $this, 'output_customizer_css' ) );
        
        // Add selective refresh support
        add_action( 'customize_register', array( $this, 'add_selective_refresh' ) );
    }
    
    /**
     * Register all customizer settings, controls, and sections
     */
    public function register_customizer_settings( $wp_customize ) {
        // Add My Account Form Panel
        $wp_customize->add_panel( 'blocksy_my_account_panel', array(
            'title'       => __( 'My Account Form', 'blocksy-child' ),
            'description' => __( 'Customize WooCommerce login and register forms with advanced typography and responsive controls.', 'blocksy-child' ),
            'priority'    => 160,
            'capability'  => 'edit_theme_options',
        ) );
        
        // Register sections
        $this->register_template_section( $wp_customize );
        $this->register_typography_sections( $wp_customize );
        $this->register_colors_section( $wp_customize );
        $this->register_spacing_section( $wp_customize );
        $this->register_responsive_sections( $wp_customize );
    }
    
    /**
     * Register template selection section
     */
    private function register_template_section( $wp_customize ) {
        // Template Section
        $wp_customize->add_section( 'blocksy_my_account_template', array(
            'title'    => __( 'Template Selection', 'blocksy-child' ),
            'panel'    => 'blocksy_my_account_panel',
            'priority' => 10,
        ) );
        
        // Template Setting
        $wp_customize->add_setting( 'blocksy_child_my_account_template', array(
            'default'           => 'default',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ) );
        
        // Template Control
        $wp_customize->add_control( 'blocksy_child_my_account_template', array(
            'label'       => __( 'Select Template', 'blocksy-child' ),
            'description' => __( 'Choose the template design for your login/register forms.', 'blocksy-child' ),
            'section'     => 'blocksy_my_account_template',
            'type'        => 'select',
            'choices'     => array(
                'default'   => __( 'Default WooCommerce', 'blocksy-child' ),
                'template1' => __( 'Template 1 - Side by Side', 'blocksy-child' ),
                'template2' => __( 'Template 2 - Centered', 'blocksy-child' ),
            ),
        ) );
    }
    
    /**
     * Register typography sections
     */
    private function register_typography_sections( $wp_customize ) {
        // Heading Typography Section
        $wp_customize->add_section( 'blocksy_my_account_heading_typography', array(
            'title'    => __( 'Heading Typography', 'blocksy-child' ),
            'panel'    => 'blocksy_my_account_panel',
            'priority' => 20,
        ) );
        
        // Body Typography Section
        $wp_customize->add_section( 'blocksy_my_account_body_typography', array(
            'title'    => __( 'Body Text Typography', 'blocksy-child' ),
            'panel'    => 'blocksy_my_account_panel',
            'priority' => 30,
        ) );
        
        // Placeholder Typography Section
        $wp_customize->add_section( 'blocksy_my_account_placeholder_typography', array(
            'title'    => __( 'Placeholder Typography', 'blocksy-child' ),
            'panel'    => 'blocksy_my_account_panel',
            'priority' => 40,
        ) );
        
        // Button Typography Section
        $wp_customize->add_section( 'blocksy_my_account_button_typography', array(
            'title'    => __( 'Button Typography', 'blocksy-child' ),
            'panel'    => 'blocksy_my_account_panel',
            'priority' => 50,
        ) );
        
        // Register typography controls for each section
        $this->register_typography_controls( $wp_customize, 'heading', 'blocksy_my_account_heading_typography' );
        $this->register_typography_controls( $wp_customize, 'body', 'blocksy_my_account_body_typography' );
        $this->register_typography_controls( $wp_customize, 'placeholder', 'blocksy_my_account_placeholder_typography' );
        $this->register_typography_controls( $wp_customize, 'button', 'blocksy_my_account_button_typography' );
    }
    
    /**
     * Register typography controls for a specific element
     */
    private function register_typography_controls( $wp_customize, $element, $section ) {
        $defaults = $this->get_typography_defaults( $element );
        
        // Font Family
        $wp_customize->add_setting( "blocksy_child_my_account_{$element}_font", array(
            'default'           => $defaults['font'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage',
        ) );
        
        $wp_customize->add_control( "blocksy_child_my_account_{$element}_font", array(
            'label'       => __( 'Font Family', 'blocksy-child' ),
            'description' => __( 'Enter a font family (e.g., Arial, sans-serif)', 'blocksy-child' ),
            'section'     => $section,
            'type'        => 'text',
        ) );
        
        // Font Size
        $wp_customize->add_setting( "blocksy_child_my_account_{$element}_font_size", array(
            'default'           => $defaults['size'],
            'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
            'transport'         => 'postMessage',
        ) );
        
        $wp_customize->add_control( "blocksy_child_my_account_{$element}_font_size", array(
            'label'       => __( 'Font Size', 'blocksy-child' ),
            'description' => __( 'Enter size with CSS unit (e.g., 16px, 1rem)', 'blocksy-child' ),
            'section'     => $section,
            'type'        => 'text',
        ) );
        
        // Font Color
        $wp_customize->add_setting( "blocksy_child_my_account_{$element}_font_color", array(
            'default'           => $defaults['color'],
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage',
        ) );
        
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, "blocksy_child_my_account_{$element}_font_color", array(
            'label'   => __( 'Font Color', 'blocksy-child' ),
            'section' => $section,
        ) ) );
        
        // Font Weight
        $wp_customize->add_setting( "blocksy_child_my_account_{$element}_font_weight", array(
            'default'           => $defaults['weight'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage',
        ) );
        
        $wp_customize->add_control( "blocksy_child_my_account_{$element}_font_weight", array(
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
        ) );
        
        // Text Transform
        $wp_customize->add_setting( "blocksy_child_my_account_{$element}_text_transform", array(
            'default'           => $defaults['transform'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage',
        ) );
        
        $wp_customize->add_control( "blocksy_child_my_account_{$element}_text_transform", array(
            'label'   => __( 'Text Transform', 'blocksy-child' ),
            'section' => $section,
            'type'    => 'select',
            'choices' => array(
                'none'       => __( 'None', 'blocksy-child' ),
                'uppercase'  => __( 'Uppercase', 'blocksy-child' ),
                'lowercase'  => __( 'Lowercase', 'blocksy-child' ),
                'capitalize' => __( 'Capitalize', 'blocksy-child' ),
            ),
        ) );
    }
    
    /**
     * Get typography defaults for different elements
     */
    private function get_typography_defaults( $element ) {
        $defaults = array(
            'heading' => array(
                'font'      => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                'size'      => '24px',
                'color'     => '#333333',
                'weight'    => '600',
                'transform' => 'none',
            ),
            'body' => array(
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
            'button' => array(
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
        // Remove any potentially harmful characters
        $sanitized = preg_replace( '/[^0-9a-zA-Z%.\s-]/', '', $input );
        
        // Check if the value has a valid CSS unit
        if ( ! empty( $sanitized ) && ! preg_match( '/(px|em|rem|%|vh|vw|pt|pc|in|cm|mm|ex|ch)$/', $sanitized ) ) {
            // If no valid unit, append 'px'
            $sanitized .= 'px';
        }
        
        return $sanitized;
    }
    
    /**
     * Output customizer CSS in head
     */
    public function output_customizer_css() {
        // Only output on my-account pages or if we're in customizer preview
        if ( ! $this->is_my_account_page() && ! is_customize_preview() ) {
            return;
        }
        
        $template = get_theme_mod( 'blocksy_child_my_account_template', 'default' );
        
        if ( $template === 'default' ) {
            return; // No custom styling for default template
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
        
        return is_account_page() || is_page( 'my-account' ) || 
               ( isset( $_GET['page'] ) && $_GET['page'] === 'my-account' );
    }
    
    /**
     * Register colors section
     */
    private function register_colors_section( $wp_customize ) {
        // Colors Section
        $wp_customize->add_section( 'blocksy_my_account_colors', array(
            'title'    => __( 'Colors', 'blocksy-child' ),
            'panel'    => 'blocksy_my_account_panel',
            'priority' => 60,
        ) );

        // Button Colors
        $button_colors = array(
            'button_color'            => array( 'label' => __( 'Button Background', 'blocksy-child' ), 'default' => '#007cba' ),
            'button_text_color'       => array( 'label' => __( 'Button Text', 'blocksy-child' ), 'default' => '#ffffff' ),
            'button_hover_color'      => array( 'label' => __( 'Button Hover Background', 'blocksy-child' ), 'default' => '#005a87' ),
            'button_hover_text_color' => array( 'label' => __( 'Button Hover Text', 'blocksy-child' ), 'default' => '#ffffff' ),
        );

        foreach ( $button_colors as $key => $config ) {
            $wp_customize->add_setting( "blocksy_child_my_account_{$key}", array(
                'default'           => $config['default'],
                'sanitize_callback' => 'sanitize_hex_color',
                'transport'         => 'postMessage',
            ) );

            $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, "blocksy_child_my_account_{$key}", array(
                'label'   => $config['label'],
                'section' => 'blocksy_my_account_colors',
            ) ) );
        }

        // Input Colors
        $input_colors = array(
            'input_background_color' => array( 'label' => __( 'Input Background', 'blocksy-child' ), 'default' => '#ffffff' ),
            'input_border_color'     => array( 'label' => __( 'Input Border', 'blocksy-child' ), 'default' => '#dddddd' ),
            'input_text_color'       => array( 'label' => __( 'Input Text', 'blocksy-child' ), 'default' => '#333333' ),
        );

        foreach ( $input_colors as $key => $config ) {
            $wp_customize->add_setting( "blocksy_child_my_account_{$key}", array(
                'default'           => $config['default'],
                'sanitize_callback' => 'sanitize_hex_color',
                'transport'         => 'postMessage',
            ) );

            $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, "blocksy_child_my_account_{$key}", array(
                'label'   => $config['label'],
                'section' => 'blocksy_my_account_colors',
            ) ) );
        }
    }

    /**
     * Register spacing section
     */
    private function register_spacing_section( $wp_customize ) {
        // Spacing Section
        $wp_customize->add_section( 'blocksy_my_account_spacing', array(
            'title'    => __( 'Button Padding (Desktop)', 'blocksy-child' ),
            'panel'    => 'blocksy_my_account_panel',
            'priority' => 70,
        ) );

        // Button Padding Controls
        $padding_sides = array(
            'top'    => array( 'label' => __( 'Top Padding', 'blocksy-child' ), 'default' => '12px' ),
            'right'  => array( 'label' => __( 'Right Padding', 'blocksy-child' ), 'default' => '24px' ),
            'bottom' => array( 'label' => __( 'Bottom Padding', 'blocksy-child' ), 'default' => '12px' ),
            'left'   => array( 'label' => __( 'Left Padding', 'blocksy-child' ), 'default' => '24px' ),
        );

        foreach ( $padding_sides as $side => $config ) {
            $wp_customize->add_setting( "blocksy_child_my_account_button_padding_{$side}", array(
                'default'           => $config['default'],
                'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
                'transport'         => 'postMessage',
            ) );

            $wp_customize->add_control( "blocksy_child_my_account_button_padding_{$side}", array(
                'label'       => $config['label'],
                'description' => __( 'Enter padding with CSS unit (e.g., 12px, 1rem)', 'blocksy-child' ),
                'section'     => 'blocksy_my_account_spacing',
                'type'        => 'text',
            ) );
        }
    }

    /**
     * Register responsive sections
     */
    private function register_responsive_sections( $wp_customize ) {
        // Tablet Responsive Section
        $wp_customize->add_section( 'blocksy_my_account_tablet', array(
            'title'       => __( 'Tablet Responsive (768px - 1023px)', 'blocksy-child' ),
            'description' => __( 'Override desktop settings for tablet devices. Only Font Size and Font Weight can be customized.', 'blocksy-child' ),
            'panel'       => 'blocksy_my_account_panel',
            'priority'    => 80,
        ) );

        // Mobile Responsive Section
        $wp_customize->add_section( 'blocksy_my_account_mobile', array(
            'title'       => __( 'Mobile Responsive (< 768px)', 'blocksy-child' ),
            'description' => __( 'Override desktop settings for mobile devices. Only Font Size and Font Weight can be customized.', 'blocksy-child' ),
            'panel'       => 'blocksy_my_account_panel',
            'priority'    => 90,
        ) );

        // Register responsive controls
        $this->register_responsive_controls( $wp_customize, 'tablet', 'blocksy_my_account_tablet' );
        $this->register_responsive_controls( $wp_customize, 'mobile', 'blocksy_my_account_mobile' );
    }

    /**
     * Register responsive controls for tablet or mobile
     */
    private function register_responsive_controls( $wp_customize, $device, $section ) {
        $elements = array( 'heading', 'body', 'placeholder', 'button' );

        foreach ( $elements as $element ) {
            // Font Size
            $wp_customize->add_setting( "blocksy_child_my_account_{$device}_{$element}_font_size", array(
                'default'           => '',
                'sanitize_callback' => array( $this, 'sanitize_css_unit' ),
                'transport'         => 'postMessage',
            ) );

            $wp_customize->add_control( "blocksy_child_my_account_{$device}_{$element}_font_size", array(
                'label'       => sprintf( __( '%s Font Size', 'blocksy-child' ), ucfirst( $element ) ),
                'description' => sprintf( __( 'Override desktop %s font size for %s', 'blocksy-child' ), $element, $device ),
                'section'     => $section,
                'type'        => 'text',
            ) );

            // Font Weight
            $wp_customize->add_setting( "blocksy_child_my_account_{$device}_{$element}_font_weight", array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'postMessage',
            ) );

            $wp_customize->add_control( "blocksy_child_my_account_{$device}_{$element}_font_weight", array(
                'label'       => sprintf( __( '%s Font Weight', 'blocksy-child' ), ucfirst( $element ) ),
                'description' => sprintf( __( 'Override desktop %s font weight for %s', 'blocksy-child' ), $element, $device ),
                'section'     => $section,
                'type'        => 'select',
                'choices'     => array(
                    ''    => __( 'Use Desktop Setting', 'blocksy-child' ),
                    '300' => __( 'Light (300)', 'blocksy-child' ),
                    '400' => __( 'Normal (400)', 'blocksy-child' ),
                    '500' => __( 'Medium (500)', 'blocksy-child' ),
                    '600' => __( 'Semi Bold (600)', 'blocksy-child' ),
                    '700' => __( 'Bold (700)', 'blocksy-child' ),
                ),
            ) );
        }
    }

    /**
     * Generate CSS from customizer settings
     */
    private function generate_customizer_css( $template ) {
        $css = '<style type="text/css" id="blocksy-my-account-customizer-css">';
        $css .= '/* Blocksy Child My Account - Customizer Styles for Template ' . esc_attr( $template ) . ' */';

        // Generate desktop CSS
        $css .= $this->generate_desktop_css( $template );

        // Generate responsive CSS
        $css .= $this->generate_responsive_css( $template );

        $css .= '</style>';

        echo $css;
    }

    /**
     * Generate desktop CSS
     */
    private function generate_desktop_css( $template ) {
        $css = '';

        // Typography CSS
        $elements = array( 'heading', 'body', 'placeholder', 'button' );

        foreach ( $elements as $element ) {
            $font_family = get_theme_mod( "blocksy_child_my_account_{$element}_font", $this->get_typography_defaults( $element )['font'] );
            $font_size = get_theme_mod( "blocksy_child_my_account_{$element}_font_size", $this->get_typography_defaults( $element )['size'] );
            $font_color = get_theme_mod( "blocksy_child_my_account_{$element}_font_color", $this->get_typography_defaults( $element )['color'] );
            $font_weight = get_theme_mod( "blocksy_child_my_account_{$element}_font_weight", $this->get_typography_defaults( $element )['weight'] );
            $text_transform = get_theme_mod( "blocksy_child_my_account_{$element}_text_transform", $this->get_typography_defaults( $element )['transform'] );

            $selector = $this->get_element_selector( $element, $template );

            if ( $selector ) {
                $css .= $selector . ' {';
                $css .= "font-family: {$font_family} !important;";
                $css .= "font-size: {$font_size} !important;";
                $css .= "color: {$font_color} !important;";
                $css .= "font-weight: {$font_weight} !important;";
                $css .= "text-transform: {$text_transform} !important;";
                $css .= '}';
            }
        }

        // Button specific styles
        $button_bg = get_theme_mod( 'blocksy_child_my_account_button_color', '#007cba' );
        $button_text = get_theme_mod( 'blocksy_child_my_account_button_text_color', '#ffffff' );
        $button_hover_bg = get_theme_mod( 'blocksy_child_my_account_button_hover_color', '#005a87' );
        $button_hover_text = get_theme_mod( 'blocksy_child_my_account_button_hover_text_color', '#ffffff' );

        // Button padding
        $padding_top = get_theme_mod( 'blocksy_child_my_account_button_padding_top', '12px' );
        $padding_right = get_theme_mod( 'blocksy_child_my_account_button_padding_right', '24px' );
        $padding_bottom = get_theme_mod( 'blocksy_child_my_account_button_padding_bottom', '12px' );
        $padding_left = get_theme_mod( 'blocksy_child_my_account_button_padding_left', '24px' );

        $css .= ".blaze-login-register.{$template} button, .blaze-login-register.{$template} .button {";
        $css .= "background-color: {$button_bg} !important;";
        $css .= "color: {$button_text} !important;";
        $css .= "padding: {$padding_top} {$padding_right} {$padding_bottom} {$padding_left} !important;";
        $css .= '}';

        $css .= ".blaze-login-register.{$template} button:hover, .blaze-login-register.{$template} .button:hover {";
        $css .= "background-color: {$button_hover_bg} !important;";
        $css .= "color: {$button_hover_text} !important;";
        $css .= '}';

        // Input styles
        $input_bg = get_theme_mod( 'blocksy_child_my_account_input_background_color', '#ffffff' );
        $input_border = get_theme_mod( 'blocksy_child_my_account_input_border_color', '#dddddd' );
        $input_text = get_theme_mod( 'blocksy_child_my_account_input_text_color', '#333333' );

        $css .= ".blaze-login-register.{$template} input[type=\"text\"], ";
        $css .= ".blaze-login-register.{$template} input[type=\"email\"], ";
        $css .= ".blaze-login-register.{$template} input[type=\"password\"] {";
        $css .= "background-color: {$input_bg} !important;";
        $css .= "border-color: {$input_border} !important;";
        $css .= "color: {$input_text} !important;";
        $css .= '}';

        return $css;
    }

    /**
     * Generate responsive CSS
     */
    private function generate_responsive_css( $template ) {
        $css = '';

        // Tablet CSS
        $tablet_css = $this->generate_device_css( 'tablet', $template );
        if ( $tablet_css ) {
            $css .= '@media (max-width: 1023px) and (min-width: 768px) {' . $tablet_css . '}';
        }

        // Mobile CSS
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
        $css = '';
        $elements = array( 'heading', 'body', 'placeholder', 'button' );

        foreach ( $elements as $element ) {
            $font_size = get_theme_mod( "blocksy_child_my_account_{$device}_{$element}_font_size", '' );
            $font_weight = get_theme_mod( "blocksy_child_my_account_{$device}_{$element}_font_weight", '' );

            if ( $font_size || $font_weight ) {
                $selector = $this->get_element_selector( $element, $template );

                if ( $selector ) {
                    $css .= $selector . ' {';
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
            'heading' => ".blaze-login-register.{$template} h2",
            'body' => ".blaze-login-register.{$template} p, .blaze-login-register.{$template} label, .blaze-login-register.{$template} span, .blaze-login-register.{$template} a",
            'placeholder' => ".blaze-login-register.{$template} input::placeholder",
            'button' => ".blaze-login-register.{$template} button, .blaze-login-register.{$template} .button",
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
        
        // Add selective refresh for template changes
        $wp_customize->selective_refresh->add_partial( 'blocksy_child_my_account_template', array(
            'selector'        => '.woocommerce-account .woocommerce',
            'render_callback' => array( $this, 'render_my_account_form' ),
        ) );
    }
    
    /**
     * Render callback for selective refresh
     */
    public function render_my_account_form() {
        // This will be implemented to return the updated form HTML
        return '';
    }
}

// Initialize the customizer integration
new Blocksy_Child_My_Account_Customizer();
