<?php
/**
 * Blaze My Account Customization
 * 
 * Custom WooCommerce My Account templates with customization options.
 * Integrated from Blaze My Account plugin into Blocksy child theme.
 * 
 * @package Blocksy_Child
 * @since 1.5.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Blaze My Account Class
 * 
 * Handles WooCommerce my-account page customizations
 */
class Blocksy_Child_Blaze_My_Account {

    /**
     * Initialize the functionality
     */
    public function __construct() {
        // Register hooks
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_fallback' ), 20 );
        add_filter( 'woocommerce_locate_template', array( $this, 'override_my_account_template' ), 10, 3 );
        add_action( 'wp_head', array( $this, 'output_custom_css' ) );
        add_action( 'wp_footer', array( $this, 'add_debug_info' ) );
        
        // Load admin functionality
        if ( is_admin() ) {
            $this->load_admin();
        }
    }

    /**
     * Load admin functionality
     */
    private function load_admin() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only load on WooCommerce pages or when our templates are active
        if ( ! $this->should_load_assets() ) {
            return;
        }

        // Enqueue base CSS
        $css_file = get_stylesheet_directory() . '/assets/css/my-account.css';
        if ( file_exists( $css_file ) ) {
            wp_enqueue_style(
                'blocksy-child-my-account-style',
                get_stylesheet_directory_uri() . '/assets/css/my-account.css',
                array(),
                filemtime( $css_file ),
                'all'
            );
        }

        // Enqueue JavaScript if exists
        $js_file = get_stylesheet_directory() . '/assets/js/my-account.js';
        if ( file_exists( $js_file ) ) {
            wp_enqueue_script(
                'blocksy-child-my-account-script',
                get_stylesheet_directory_uri() . '/assets/js/my-account.js',
                array( 'jquery' ),
                filemtime( $js_file ),
                true
            );
        }
    }

    /**
     * Fallback enqueue method - ensures CSS loads on WooCommerce pages
     */
    public function enqueue_scripts_fallback() {
        // Check if our main CSS was already enqueued
        if ( wp_style_is( 'blocksy-child-my-account-style', 'enqueued' ) ) {
            return;
        }

        // Force load on specific WooCommerce pages
        $should_force_load = false;

        // Check if we're on my-account page by URL
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        if ( strpos( $current_url, 'my-account' ) !== false ) {
            $should_force_load = true;
        }

        // Check if page contains WooCommerce login form
        if ( is_page() ) {
            global $post;
            if ( $post && (
                strpos( $post->post_content, 'woocommerce_my_account' ) !== false ||
                strpos( $post->post_content, 'woocommerce-form-login' ) !== false ||
                strpos( $post->post_content, 'blaze-login-register' ) !== false
            ) ) {
                $should_force_load = true;
            }
        }

        // Force load if custom template is selected
        $selected_template = get_option( 'blocksy_child_my_account_template', 'default' );
        if ( $selected_template !== 'default' ) {
            $should_force_load = true;
        }

        if ( $should_force_load ) {
            $css_file = get_stylesheet_directory() . '/assets/css/my-account.css';
            if ( file_exists( $css_file ) ) {
                wp_enqueue_style(
                    'blocksy-child-my-account-style-fallback',
                    get_stylesheet_directory_uri() . '/assets/css/my-account.css',
                    array(),
                    filemtime( $css_file ),
                    'all'
                );
            }
        }
    }

    /**
     * Determine if we should load plugin assets
     */
    private function should_load_assets() {
        // Always load on WooCommerce my-account page
        if ( function_exists( 'is_account_page' ) && is_account_page() ) {
            return true;
        }

        // Check if we're on a page that contains my-account content
        if ( is_page() ) {
            global $post;
            if ( $post ) {
                // Check for WooCommerce shortcodes
                if ( has_shortcode( $post->post_content, 'woocommerce_my_account' ) ||
                     has_shortcode( $post->post_content, 'woocommerce_login' ) ) {
                    return true;
                }

                // Check for our custom classes in content
                if ( strpos( $post->post_content, 'blaze-login-register' ) !== false ||
                     strpos( $post->post_content, 'blaze-my-account' ) !== false ) {
                    return true;
                }

                // Check page slug for my-account related pages
                if ( in_array( $post->post_name, array( 'my-account', 'account', 'login', 'register' ) ) ) {
                    return true;
                }
            }
        }

        // Load if we're using a custom template
        $selected_template = get_option( 'blocksy_child_my_account_template', 'default' );
        if ( $selected_template !== 'default' ) {
            // Load on WooCommerce pages
            if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
                return true;
            }

            // Fallback: Load on any page that might be WooCommerce related
            if ( is_page() || is_single() ) {
                // Check URL for WooCommerce patterns
                $current_url = $_SERVER['REQUEST_URI'] ?? '';
                if ( strpos( $current_url, 'my-account' ) !== false ||
                     strpos( $current_url, 'account' ) !== false ||
                     strpos( $current_url, 'login' ) !== false ) {
                    return true;
                }
            }
        }

        // Always load in admin preview or customizer
        if ( is_admin() || is_customize_preview() ) {
            return true;
        }

        return false;
    }

    /**
     * Override WooCommerce templates
     */
    public function override_my_account_template( $template, $template_name, $template_path ) {
        // Handle form-login.php template only
        if ( $template_name === 'myaccount/form-login.php' ) {
            $selected_template = get_option( 'blocksy_child_my_account_template', 'default' );

            if ( $selected_template === 'default' ) {
                return $template;
            }

            $theme_path = get_stylesheet_directory() . '/woocommerce/myaccount/';

            if ( $selected_template === 'template1' ) {
                $template_file = $theme_path . 'template1/form-login.php';
            } else if ( $selected_template === 'template2' ) {
                $template_file = $theme_path . 'template2/form-login.php';
            }

            if ( file_exists( $template_file ) ) {
                return $template_file;
            }
        }

        return $template;
    }

    /**
     * Output custom CSS based on settings
     */
    public function output_custom_css() {
        $selected_template = get_option( 'blocksy_child_my_account_template', 'default' );
        
        if ( $selected_template === 'default' ) {
            return;
        }
        
        // Get all customization settings
        $heading_font = get_option( 'blocksy_child_my_account_heading_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' );
        $heading_font_size = get_option( 'blocksy_child_my_account_heading_font_size', '24px' );
        $heading_color = get_option( 'blocksy_child_my_account_heading_font_color', '#333333' );
        $heading_font_weight = get_option( 'blocksy_child_my_account_heading_font_weight', '600' );
        $heading_text_transform = get_option( 'blocksy_child_my_account_heading_text_transform', 'none' );
        
        // Continue with more CSS generation in the next part...
        $this->output_dynamic_css( $selected_template, compact(
            'heading_font', 'heading_font_size', 'heading_color',
            'heading_font_weight', 'heading_text_transform'
        ) );
    }

    /**
     * Output dynamic CSS based on settings
     */
    private function output_dynamic_css( $template, $settings ) {
        extract( $settings );

        // Get body text settings
        $body_font = get_option( 'blocksy_child_my_account_body_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' );
        $body_font_size = get_option( 'blocksy_child_my_account_body_font_size', '16px' );
        $body_color = get_option( 'blocksy_child_my_account_body_font_color', '#666666' );
        $body_font_weight = get_option( 'blocksy_child_my_account_body_font_weight', '400' );
        $body_text_transform = get_option( 'blocksy_child_my_account_body_text_transform', 'none' );

        // Get placeholder settings
        $placeholder_font = get_option( 'blocksy_child_my_account_placeholder_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' );
        $placeholder_font_size = get_option( 'blocksy_child_my_account_placeholder_font_size', '14px' );
        $placeholder_color = get_option( 'blocksy_child_my_account_placeholder_font_color', '#999999' );
        $placeholder_font_weight = get_option( 'blocksy_child_my_account_placeholder_font_weight', '400' );
        $placeholder_text_transform = get_option( 'blocksy_child_my_account_placeholder_text_transform', 'none' );

        // Get color settings
        $button_background = get_option( 'blocksy_child_my_account_button_color', '#007cba' );
        $button_text_color = get_option( 'blocksy_child_my_account_button_text_color', '#ffffff' );
        $button_hover_background = get_option( 'blocksy_child_my_account_button_hover_color', '#005a87' );
        $button_hover_text_color = get_option( 'blocksy_child_my_account_button_hover_text_color', '#ffffff' );
        $input_background = get_option( 'blocksy_child_my_account_input_background_color', '#ffffff' );
        $input_border = get_option( 'blocksy_child_my_account_input_border_color', '#dddddd' );
        $input_text_color = get_option( 'blocksy_child_my_account_input_text_color', '#333333' );

        // Get button typography settings
        $button_font = get_option( 'blocksy_child_my_account_button_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' );
        $button_font_size = get_option( 'blocksy_child_my_account_button_font_size', '16px' );
        $button_font_weight = get_option( 'blocksy_child_my_account_button_font_weight', '600' );
        $button_text_transform = get_option( 'blocksy_child_my_account_button_text_transform', 'none' );

        // Get button padding settings
        $button_padding_top = get_option( 'blocksy_child_my_account_button_padding_top', '12px' );
        $button_padding_right = get_option( 'blocksy_child_my_account_button_padding_right', '24px' );
        $button_padding_bottom = get_option( 'blocksy_child_my_account_button_padding_bottom', '12px' );
        $button_padding_left = get_option( 'blocksy_child_my_account_button_padding_left', '24px' );

        echo '<style type="text/css">
            /* Blocksy Child My Account - Custom Styles for Template ' . esc_attr( $template ) . ' */

            /* Heading Styles */
            .blaze-login-register.' . esc_attr( $template ) . ' h2 {
                font-family: ' . esc_attr( $heading_font ) . ' !important;
                font-size: ' . esc_attr( $heading_font_size ) . ' !important;
                color: ' . esc_attr( $heading_color ) . ' !important;
                font-weight: ' . esc_attr( $heading_font_weight ) . ' !important;
                text-transform: ' . esc_attr( $heading_text_transform ) . ' !important;
            }

            /* Body Text Styles */
            .blaze-login-register.' . esc_attr( $template ) . ' p,
            .blaze-login-register.' . esc_attr( $template ) . ' label,
            .blaze-login-register.' . esc_attr( $template ) . ' span,
            .blaze-login-register.' . esc_attr( $template ) . ' a {
                font-family: ' . esc_attr( $body_font ) . ' !important;
                font-size: ' . esc_attr( $body_font_size ) . ' !important;
                color: ' . esc_attr( $body_color ) . ' !important;
                font-weight: ' . esc_attr( $body_font_weight ) . ' !important;
                text-transform: ' . esc_attr( $body_text_transform ) . ' !important;
            }

            /* Input Styles */
            .blaze-login-register.' . esc_attr( $template ) . ' input[type="text"],
            .blaze-login-register.' . esc_attr( $template ) . ' input[type="email"],
            .blaze-login-register.' . esc_attr( $template ) . ' input[type="password"] {
                color: ' . esc_attr( $input_text_color ) . ' !important;
                background-color: ' . esc_attr( $input_background ) . ' !important;
                border-color: ' . esc_attr( $input_border ) . ' !important;
            }

            /* Placeholder Styles */
            .blaze-login-register.' . esc_attr( $template ) . ' input::placeholder {
                font-family: ' . esc_attr( $placeholder_font ) . ' !important;
                font-size: ' . esc_attr( $placeholder_font_size ) . ' !important;
                color: ' . esc_attr( $placeholder_color ) . ' !important;
                font-weight: ' . esc_attr( $placeholder_font_weight ) . ' !important;
                text-transform: ' . esc_attr( $placeholder_text_transform ) . ' !important;
            }

            /* Button Styles */
            .blaze-login-register.' . esc_attr( $template ) . ' button,
            .blaze-login-register.' . esc_attr( $template ) . ' .button {
                font-family: ' . esc_attr( $button_font ) . ' !important;
                font-size: ' . esc_attr( $button_font_size ) . ' !important;
                font-weight: ' . esc_attr( $button_font_weight ) . ' !important;
                text-transform: ' . esc_attr( $button_text_transform ) . ' !important;
                color: ' . esc_attr( $button_text_color ) . ' !important;
                background-color: ' . esc_attr( $button_background ) . ' !important;
                padding: ' . esc_attr( $button_padding_top ) . ' ' . esc_attr( $button_padding_right ) . ' ' . esc_attr( $button_padding_bottom ) . ' ' . esc_attr( $button_padding_left ) . ' !important;
            }

            .blaze-login-register.' . esc_attr( $template ) . ' button:hover,
            .blaze-login-register.' . esc_attr( $template ) . ' .button:hover {
                color: ' . esc_attr( $button_hover_text_color ) . ' !important;
                background-color: ' . esc_attr( $button_hover_background ) . ' !important;
            }
        </style>';

        // Add responsive styles
        $this->output_responsive_css( $template );
    }

    /**
     * Output responsive CSS for tablet and mobile
     */
    private function output_responsive_css( $template ) {
        // Get tablet settings
        $tablet_heading_font_size = get_option( 'blocksy_child_my_account_tablet_heading_font_size', '' );
        $tablet_heading_font_weight = get_option( 'blocksy_child_my_account_tablet_heading_font_weight', '' );
        $tablet_body_font_size = get_option( 'blocksy_child_my_account_tablet_body_font_size', '' );
        $tablet_body_font_weight = get_option( 'blocksy_child_my_account_tablet_body_font_weight', '' );
        $tablet_placeholder_font_size = get_option( 'blocksy_child_my_account_tablet_placeholder_font_size', '' );
        $tablet_placeholder_font_weight = get_option( 'blocksy_child_my_account_tablet_placeholder_font_weight', '' );
        $tablet_button_font_size = get_option( 'blocksy_child_my_account_tablet_button_font_size', '' );
        $tablet_button_font_weight = get_option( 'blocksy_child_my_account_tablet_button_font_weight', '' );

        $tablet_button_padding_top = get_option( 'blocksy_child_my_account_tablet_button_padding_top', '' );
        $tablet_button_padding_right = get_option( 'blocksy_child_my_account_tablet_button_padding_right', '' );
        $tablet_button_padding_bottom = get_option( 'blocksy_child_my_account_tablet_button_padding_bottom', '' );
        $tablet_button_padding_left = get_option( 'blocksy_child_my_account_tablet_button_padding_left', '' );

        // Get mobile settings
        $mobile_heading_font_size = get_option( 'blocksy_child_my_account_mobile_heading_font_size', '' );
        $mobile_heading_font_weight = get_option( 'blocksy_child_my_account_mobile_heading_font_weight', '' );
        $mobile_body_font_size = get_option( 'blocksy_child_my_account_mobile_body_font_size', '' );
        $mobile_body_font_weight = get_option( 'blocksy_child_my_account_mobile_body_font_weight', '' );
        $mobile_placeholder_font_size = get_option( 'blocksy_child_my_account_mobile_placeholder_font_size', '' );
        $mobile_placeholder_font_weight = get_option( 'blocksy_child_my_account_mobile_placeholder_font_weight', '' );
        $mobile_button_font_size = get_option( 'blocksy_child_my_account_mobile_button_font_size', '' );
        $mobile_button_font_weight = get_option( 'blocksy_child_my_account_mobile_button_font_weight', '' );

        $mobile_button_padding_top = get_option( 'blocksy_child_my_account_mobile_button_padding_top', '' );
        $mobile_button_padding_right = get_option( 'blocksy_child_my_account_mobile_button_padding_right', '' );
        $mobile_button_padding_bottom = get_option( 'blocksy_child_my_account_mobile_button_padding_bottom', '' );
        $mobile_button_padding_left = get_option( 'blocksy_child_my_account_mobile_button_padding_left', '' );

        // Output tablet styles if any settings exist
        if ( $tablet_heading_font_size || $tablet_heading_font_weight || $tablet_body_font_size || $tablet_body_font_weight ||
             $tablet_placeholder_font_size || $tablet_placeholder_font_weight || $tablet_button_font_size || $tablet_button_font_weight ||
             $tablet_button_padding_top || $tablet_button_padding_right || $tablet_button_padding_bottom || $tablet_button_padding_left ) {

            echo '<style type="text/css">
                @media (max-width: 1023px) and (min-width: 768px) {
                    /* Tablet Styles for Template ' . esc_attr( $template ) . ' */';

            if ( $tablet_heading_font_size || $tablet_heading_font_weight ) {
                echo '.blaze-login-register.' . esc_attr( $template ) . ' h2 {';
                if ( $tablet_heading_font_size ) echo 'font-size: ' . esc_attr( $tablet_heading_font_size ) . ' !important;';
                if ( $tablet_heading_font_weight ) echo 'font-weight: ' . esc_attr( $tablet_heading_font_weight ) . ' !important;';
                echo '}';
            }

            if ( $tablet_body_font_size || $tablet_body_font_weight ) {
                echo '.blaze-login-register.' . esc_attr( $template ) . ' p,
                      .blaze-login-register.' . esc_attr( $template ) . ' label,
                      .blaze-login-register.' . esc_attr( $template ) . ' span,
                      .blaze-login-register.' . esc_attr( $template ) . ' a {';
                if ( $tablet_body_font_size ) echo 'font-size: ' . esc_attr( $tablet_body_font_size ) . ' !important;';
                if ( $tablet_body_font_weight ) echo 'font-weight: ' . esc_attr( $tablet_body_font_weight ) . ' !important;';
                echo '}';
            }

            if ( $tablet_placeholder_font_size || $tablet_placeholder_font_weight ) {
                echo '.blaze-login-register.' . esc_attr( $template ) . ' input::placeholder {';
                if ( $tablet_placeholder_font_size ) echo 'font-size: ' . esc_attr( $tablet_placeholder_font_size ) . ' !important;';
                if ( $tablet_placeholder_font_weight ) echo 'font-weight: ' . esc_attr( $tablet_placeholder_font_weight ) . ' !important;';
                echo '}';
            }

            if ( $tablet_button_font_size || $tablet_button_font_weight ) {
                echo '.blaze-login-register.' . esc_attr( $template ) . ' button,
                      .blaze-login-register.' . esc_attr( $template ) . ' .button {';
                if ( $tablet_button_font_size ) echo 'font-size: ' . esc_attr( $tablet_button_font_size ) . ' !important;';
                if ( $tablet_button_font_weight ) echo 'font-weight: ' . esc_attr( $tablet_button_font_weight ) . ' !important;';
                echo '}';
            }

            if ( $tablet_button_padding_top || $tablet_button_padding_right || $tablet_button_padding_bottom || $tablet_button_padding_left ) {
                echo '.blaze-login-register.' . esc_attr( $template ) . ' button,
                      .blaze-login-register.' . esc_attr( $template ) . ' .button {';
                $padding_values = array(
                    $tablet_button_padding_top ?: 'inherit',
                    $tablet_button_padding_right ?: 'inherit',
                    $tablet_button_padding_bottom ?: 'inherit',
                    $tablet_button_padding_left ?: 'inherit'
                );
                echo 'padding: ' . implode( ' ', $padding_values ) . ' !important;';
                echo '}';
            }

            echo '}
                </style>';
        }

        // Output mobile styles if any settings exist
        if ( $mobile_heading_font_size || $mobile_heading_font_weight || $mobile_body_font_size || $mobile_body_font_weight ||
             $mobile_placeholder_font_size || $mobile_placeholder_font_weight || $mobile_button_font_size || $mobile_button_font_weight ||
             $mobile_button_padding_top || $mobile_button_padding_right || $mobile_button_padding_bottom || $mobile_button_padding_left ) {

            echo '<style type="text/css">
                @media (max-width: 767px) {
                    /* Mobile Styles for Template ' . esc_attr( $template ) . ' */';

            if ( $mobile_heading_font_size || $mobile_heading_font_weight ) {
                echo '.blaze-login-register.' . esc_attr( $template ) . ' h2 {';
                if ( $mobile_heading_font_size ) echo 'font-size: ' . esc_attr( $mobile_heading_font_size ) . ' !important;';
                if ( $mobile_heading_font_weight ) echo 'font-weight: ' . esc_attr( $mobile_heading_font_weight ) . ' !important;';
                echo '}';
            }

            if ( $mobile_body_font_size || $mobile_body_font_weight ) {
                echo '.blaze-login-register.' . esc_attr( $template ) . ' p,
                      .blaze-login-register.' . esc_attr( $template ) . ' label,
                      .blaze-login-register.' . esc_attr( $template ) . ' span,
                      .blaze-login-register.' . esc_attr( $template ) . ' a {';
                if ( $mobile_body_font_size ) echo 'font-size: ' . esc_attr( $mobile_body_font_size ) . ' !important;';
                if ( $mobile_body_font_weight ) echo 'font-weight: ' . esc_attr( $mobile_body_font_weight ) . ' !important;';
                echo '}';
            }

            if ( $mobile_placeholder_font_size || $mobile_placeholder_font_weight ) {
                echo '.blaze-login-register.' . esc_attr( $template ) . ' input::placeholder {';
                if ( $mobile_placeholder_font_size ) echo 'font-size: ' . esc_attr( $mobile_placeholder_font_size ) . ' !important;';
                if ( $mobile_placeholder_font_weight ) echo 'font-weight: ' . esc_attr( $mobile_placeholder_font_weight ) . ' !important;';
                echo '}';
            }

            if ( $mobile_button_font_size || $mobile_button_font_weight ) {
                echo '.blaze-login-register.' . esc_attr( $template ) . ' button,
                      .blaze-login-register.' . esc_attr( $template ) . ' .button {';
                if ( $mobile_button_font_size ) echo 'font-size: ' . esc_attr( $mobile_button_font_size ) . ' !important;';
                if ( $mobile_button_font_weight ) echo 'font-weight: ' . esc_attr( $mobile_button_font_weight ) . ' !important;';
                echo '}';
            }

            if ( $mobile_button_padding_top || $mobile_button_padding_right || $mobile_button_padding_bottom || $mobile_button_padding_left ) {
                echo '.blaze-login-register.' . esc_attr( $template ) . ' button,
                      .blaze-login-register.' . esc_attr( $template ) . ' .button {';
                $padding_values = array(
                    $mobile_button_padding_top ?: 'inherit',
                    $mobile_button_padding_right ?: 'inherit',
                    $mobile_button_padding_bottom ?: 'inherit',
                    $mobile_button_padding_left ?: 'inherit'
                );
                echo 'padding: ' . implode( ' ', $padding_values ) . ' !important;';
                echo '}';
            }

            echo '}
                </style>';
        }
    }

    /**
     * Add debug information to help troubleshoot CSS loading
     */
    public function add_debug_info() {
        if ( isset( $_GET['blaze_debug'] ) && $_GET['blaze_debug'] == '1' && current_user_can( 'manage_options' ) ) {
            echo '<!-- Blocksy Child My Account Debug Info -->';
            echo '<div style="position: fixed; top: 10px; right: 10px; background: #fff; border: 1px solid #ccc; padding: 10px; z-index: 9999; font-size: 12px; max-width: 300px;">';
            echo '<strong>Blocksy Child My Account Debug</strong><br>';
            echo 'Template: ' . get_option( 'blocksy_child_my_account_template', 'default' ) . '<br>';
            echo 'Should Load Assets: ' . ( $this->should_load_assets() ? 'Yes' : 'No' ) . '<br>';
            echo 'Is Account Page: ' . ( function_exists( 'is_account_page' ) && is_account_page() ? 'Yes' : 'No' ) . '<br>';
            echo 'Is WooCommerce: ' . ( function_exists( 'is_woocommerce' ) && is_woocommerce() ? 'Yes' : 'No' ) . '<br>';
            echo 'CSS File Exists: ' . ( file_exists( get_stylesheet_directory() . '/assets/css/my-account.css' ) ? 'Yes' : 'No' ) . '<br>';
            echo 'JS File Exists: ' . ( file_exists( get_stylesheet_directory() . '/assets/js/my-account.js' ) ? 'Yes' : 'No' ) . '<br>';
            echo '</div>';
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'My Account Form',
            'My Account Form',
            'manage_options',
            'blocksy-child-my-account',
            array( $this, 'display_admin_page' ),
            'dashicons-admin-users',
            30
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        // Register template setting
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_template' );

        // Register heading settings
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_heading_font' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_heading_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_heading_font_color' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_heading_font_weight' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_heading_text_transform' );

        // Register body text settings
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_body_font' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_body_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_body_font_color' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_body_font_weight' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_body_text_transform' );

        // Register placeholder text settings
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_placeholder_font' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_placeholder_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_placeholder_font_color' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_placeholder_font_weight' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_placeholder_text_transform' );

        // Register button typography settings
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_font' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_font_weight' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_text_transform' );

        // Register color settings
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_color' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_text_color' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_hover_color' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_hover_text_color' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_input_background_color' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_input_border_color' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_input_text_color' );

        // Register button padding settings with sanitization
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_padding_top', array( $this, 'sanitize_padding' ) );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_padding_right', array( $this, 'sanitize_padding' ) );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_padding_bottom', array( $this, 'sanitize_padding' ) );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_button_padding_left', array( $this, 'sanitize_padding' ) );

        // TABLET - Register tablet typography settings
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_heading_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_heading_font_weight' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_body_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_body_font_weight' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_placeholder_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_placeholder_font_weight' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_button_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_button_font_weight' );

        // Register tablet button padding settings with sanitization
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_button_padding_top', array( $this, 'sanitize_padding' ) );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_button_padding_right', array( $this, 'sanitize_padding' ) );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_button_padding_bottom', array( $this, 'sanitize_padding' ) );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_tablet_button_padding_left', array( $this, 'sanitize_padding' ) );

        // MOBILE - Register mobile typography settings
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_heading_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_heading_font_weight' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_body_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_body_font_weight' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_placeholder_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_placeholder_font_weight' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_button_font_size' );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_button_font_weight' );

        // Register mobile button padding settings with sanitization
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_button_padding_top', array( $this, 'sanitize_padding' ) );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_button_padding_right', array( $this, 'sanitize_padding' ) );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_button_padding_bottom', array( $this, 'sanitize_padding' ) );
        register_setting( 'blocksy_child_my_account_settings', 'blocksy_child_my_account_mobile_button_padding_left', array( $this, 'sanitize_padding' ) );
    }

    /**
     * Sanitize padding input values
     */
    public function sanitize_padding( $input ) {
        // Remove any potentially harmful characters
        $sanitized = preg_replace( '/[^0-9a-zA-Z%.\s-]/', '', $input );

        // Check if the value has a valid CSS unit
        if ( ! preg_match( '/(px|em|rem|%|vh|vw|pt|pc|in|cm|mm|ex|ch)$/', $sanitized ) ) {
            // If no valid unit, append 'px'
            $sanitized .= 'px';
        }

        return $sanitized;
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts( $hook ) {
        // Only load on plugin settings page
        if ( $hook != 'toplevel_page_blocksy-child-my-account' ) {
            return;
        }

        // Add color picker
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        // Add admin script
        wp_enqueue_script(
            'blocksy-child-my-account-admin',
            get_stylesheet_directory_uri() . '/assets/js/my-account-admin.js',
            array( 'jquery', 'wp-color-picker' ),
            '1.0.0',
            true
        );
    }

    /**
     * Display the admin settings page
     */
    public function display_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'blocksy_child_my_account_settings' );
                do_settings_sections( 'blocksy_child_my_account_settings' );
                ?>

                <h2>Template Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Select Template</th>
                        <td>
                            <select name="blocksy_child_my_account_template">
                                <option value="default" <?php selected( get_option( 'blocksy_child_my_account_template', 'default' ), 'default' ); ?>>Default WooCommerce</option>
                                <option value="template1" <?php selected( get_option( 'blocksy_child_my_account_template', 'default' ), 'template1' ); ?>>Template 1 - Side by Side</option>
                                <option value="template2" <?php selected( get_option( 'blocksy_child_my_account_template', 'default' ), 'template2' ); ?>>Template 2 - Centered</option>
                            </select>
                            <p class="description">Choose the template design for your login/register forms.</p>
                        </td>
                    </tr>
                </table>

                <h2>Heading Typography</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Font Family</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_heading_font" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_heading_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' ) ); ?>" class="regular-text" />
                            <p class="description">Enter a font family for headings</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Font Size</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_heading_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_heading_font_size', '24px' ) ); ?>" class="small-text" />
                            <p class="description">Enter a font size with units (e.g., 24px, 1.5rem)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Font Color</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_heading_font_color" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_heading_font_color', '#333333' ) ); ?>" class="color-picker" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Font Weight</th>
                        <td>
                            <select name="blocksy_child_my_account_heading_font_weight">
                                <option value="300" <?php selected( get_option( 'blocksy_child_my_account_heading_font_weight', '600' ), '300' ); ?>>Light (300)</option>
                                <option value="400" <?php selected( get_option( 'blocksy_child_my_account_heading_font_weight', '600' ), '400' ); ?>>Normal (400)</option>
                                <option value="500" <?php selected( get_option( 'blocksy_child_my_account_heading_font_weight', '600' ), '500' ); ?>>Medium (500)</option>
                                <option value="600" <?php selected( get_option( 'blocksy_child_my_account_heading_font_weight', '600' ), '600' ); ?>>Semi Bold (600)</option>
                                <option value="700" <?php selected( get_option( 'blocksy_child_my_account_heading_font_weight', '600' ), '700' ); ?>>Bold (700)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Text Transform</th>
                        <td>
                            <select name="blocksy_child_my_account_heading_text_transform">
                                <option value="none" <?php selected( get_option( 'blocksy_child_my_account_heading_text_transform', 'none' ), 'none' ); ?>>None</option>
                                <option value="uppercase" <?php selected( get_option( 'blocksy_child_my_account_heading_text_transform', 'none' ), 'uppercase' ); ?>>Uppercase</option>
                                <option value="lowercase" <?php selected( get_option( 'blocksy_child_my_account_heading_text_transform', 'none' ), 'lowercase' ); ?>>Lowercase</option>
                                <option value="capitalize" <?php selected( get_option( 'blocksy_child_my_account_heading_text_transform', 'none' ), 'capitalize' ); ?>>Capitalize</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2>Body Text Typography</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Font Family</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_body_font" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_body_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' ) ); ?>" class="regular-text" />
                            <p class="description">Enter a font family for body text</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Font Size</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_body_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_body_font_size', '16px' ) ); ?>" class="small-text" />
                            <p class="description">Enter a font size with units (e.g., 16px, 1rem)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Font Color</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_body_font_color" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_body_font_color', '#666666' ) ); ?>" class="color-picker" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Font Weight</th>
                        <td>
                            <select name="blocksy_child_my_account_body_font_weight">
                                <option value="300" <?php selected( get_option( 'blocksy_child_my_account_body_font_weight', '400' ), '300' ); ?>>Light (300)</option>
                                <option value="400" <?php selected( get_option( 'blocksy_child_my_account_body_font_weight', '400' ), '400' ); ?>>Normal (400)</option>
                                <option value="500" <?php selected( get_option( 'blocksy_child_my_account_body_font_weight', '400' ), '500' ); ?>>Medium (500)</option>
                                <option value="600" <?php selected( get_option( 'blocksy_child_my_account_body_font_weight', '400' ), '600' ); ?>>Semi Bold (600)</option>
                                <option value="700" <?php selected( get_option( 'blocksy_child_my_account_body_font_weight', '400' ), '700' ); ?>>Bold (700)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Text Transform</th>
                        <td>
                            <select name="blocksy_child_my_account_body_text_transform">
                                <option value="none" <?php selected( get_option( 'blocksy_child_my_account_body_text_transform', 'none' ), 'none' ); ?>>None</option>
                                <option value="uppercase" <?php selected( get_option( 'blocksy_child_my_account_body_text_transform', 'none' ), 'uppercase' ); ?>>Uppercase</option>
                                <option value="lowercase" <?php selected( get_option( 'blocksy_child_my_account_body_text_transform', 'none' ), 'lowercase' ); ?>>Lowercase</option>
                                <option value="capitalize" <?php selected( get_option( 'blocksy_child_my_account_body_text_transform', 'none' ), 'capitalize' ); ?>>Capitalize</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2>Placeholder Text Typography</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Font Family</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_placeholder_font" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_placeholder_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' ) ); ?>" class="regular-text" />
                            <p class="description">Enter a font family for placeholder text</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Font Size</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_placeholder_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_placeholder_font_size', '14px' ) ); ?>" class="small-text" />
                            <p class="description">Enter a font size with units (e.g., 14px, 0.875rem)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Font Color</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_placeholder_font_color" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_placeholder_font_color', '#999999' ) ); ?>" class="color-picker" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Font Weight</th>
                        <td>
                            <select name="blocksy_child_my_account_placeholder_font_weight">
                                <option value="300" <?php selected( get_option( 'blocksy_child_my_account_placeholder_font_weight', '400' ), '300' ); ?>>Light (300)</option>
                                <option value="400" <?php selected( get_option( 'blocksy_child_my_account_placeholder_font_weight', '400' ), '400' ); ?>>Normal (400)</option>
                                <option value="500" <?php selected( get_option( 'blocksy_child_my_account_placeholder_font_weight', '400' ), '500' ); ?>>Medium (500)</option>
                                <option value="600" <?php selected( get_option( 'blocksy_child_my_account_placeholder_font_weight', '400' ), '600' ); ?>>Semi Bold (600)</option>
                                <option value="700" <?php selected( get_option( 'blocksy_child_my_account_placeholder_font_weight', '400' ), '700' ); ?>>Bold (700)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Text Transform</th>
                        <td>
                            <select name="blocksy_child_my_account_placeholder_text_transform">
                                <option value="none" <?php selected( get_option( 'blocksy_child_my_account_placeholder_text_transform', 'none' ), 'none' ); ?>>None</option>
                                <option value="uppercase" <?php selected( get_option( 'blocksy_child_my_account_placeholder_text_transform', 'none' ), 'uppercase' ); ?>>Uppercase</option>
                                <option value="lowercase" <?php selected( get_option( 'blocksy_child_my_account_placeholder_text_transform', 'none' ), 'lowercase' ); ?>>Lowercase</option>
                                <option value="capitalize" <?php selected( get_option( 'blocksy_child_my_account_placeholder_text_transform', 'none' ), 'capitalize' ); ?>>Capitalize</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2>Button Typography</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Font Family</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_button_font" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_button_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' ) ); ?>" class="regular-text" />
                            <p class="description">Enter a font family for buttons</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Font Size</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_button_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_button_font_size', '16px' ) ); ?>" class="small-text" />
                            <p class="description">Enter a font size with units (e.g., 16px, 1rem)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Font Weight</th>
                        <td>
                            <select name="blocksy_child_my_account_button_font_weight">
                                <option value="300" <?php selected( get_option( 'blocksy_child_my_account_button_font_weight', '600' ), '300' ); ?>>Light (300)</option>
                                <option value="400" <?php selected( get_option( 'blocksy_child_my_account_button_font_weight', '600' ), '400' ); ?>>Normal (400)</option>
                                <option value="500" <?php selected( get_option( 'blocksy_child_my_account_button_font_weight', '600' ), '500' ); ?>>Medium (500)</option>
                                <option value="600" <?php selected( get_option( 'blocksy_child_my_account_button_font_weight', '600' ), '600' ); ?>>Semi Bold (600)</option>
                                <option value="700" <?php selected( get_option( 'blocksy_child_my_account_button_font_weight', '600' ), '700' ); ?>>Bold (700)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Text Transform</th>
                        <td>
                            <select name="blocksy_child_my_account_button_text_transform">
                                <option value="none" <?php selected( get_option( 'blocksy_child_my_account_button_text_transform', 'none' ), 'none' ); ?>>None</option>
                                <option value="uppercase" <?php selected( get_option( 'blocksy_child_my_account_button_text_transform', 'none' ), 'uppercase' ); ?>>Uppercase</option>
                                <option value="lowercase" <?php selected( get_option( 'blocksy_child_my_account_button_text_transform', 'none' ), 'lowercase' ); ?>>Lowercase</option>
                                <option value="capitalize" <?php selected( get_option( 'blocksy_child_my_account_button_text_transform', 'none' ), 'capitalize' ); ?>>Capitalize</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2>Color Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Button Background Color</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_button_color" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_button_color', '#007cba' ) ); ?>" class="color-picker" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Button Text Color</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_button_text_color" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_button_text_color', '#ffffff' ) ); ?>" class="color-picker" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Button Hover Background Color</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_button_hover_color" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_button_hover_color', '#005a87' ) ); ?>" class="color-picker" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Button Hover Text Color</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_button_hover_text_color" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_button_hover_text_color', '#ffffff' ) ); ?>" class="color-picker" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Input Background Color</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_input_background_color" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_input_background_color', '#ffffff' ) ); ?>" class="color-picker" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Input Border Color</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_input_border_color" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_input_border_color', '#dddddd' ) ); ?>" class="color-picker" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Input Text Color</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_input_text_color" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_input_text_color', '#333333' ) ); ?>" class="color-picker" />
                        </td>
                    </tr>
                </table>

                <h2>Button Padding (Desktop)</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Top Padding</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_button_padding_top" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_button_padding_top', '12px' ) ); ?>" class="small-text" />
                            <p class="description">Enter padding with units (e.g., 12px, 0.75rem)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Right Padding</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_button_padding_right" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_button_padding_right', '24px' ) ); ?>" class="small-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Bottom Padding</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_button_padding_bottom" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_button_padding_bottom', '12px' ) ); ?>" class="small-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Left Padding</th>
                        <td>
                            <input type="text" name="blocksy_child_my_account_button_padding_left" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_button_padding_left', '24px' ) ); ?>" class="small-text" />
                        </td>
                    </tr>
                </table>

                <div class="responsive-tabs-wrapper" style="margin-top: 30px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                    <h2>Responsive Typography & Spacing</h2>
                    <p class="description">Override desktop settings for specific device sizes. Only Font Size and Font Weight can be customized for tablet and mobile.</p>

                    <nav class="nav-tab-wrapper" style="border-bottom: 1px solid #ccd0d4; margin: 0 0 20px 0;">
                        <a href="#desktop-responsive" class="nav-tab nav-tab-active" data-tab="desktop">Desktop</a>
                        <a href="#tablet-responsive" class="nav-tab" data-tab="tablet">Tablet (768px - 1023px)</a>
                        <a href="#mobile-responsive" class="nav-tab" data-tab="mobile">Mobile (< 768px)</a>
                    </nav>

                    <div class="tab-content-wrapper">
                        <div id="desktop-responsive" class="tab-panel active">
                            <p><strong>Desktop settings are configured above.</strong> Use the sections above to set your base typography and spacing.</p>
                        </div>

                        <div id="tablet-responsive" class="tab-panel" style="display: none;">
                            <h3>Tablet Typography Overrides</h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Heading Font Size</th>
                                    <td>
                                        <input type="text" name="blocksy_child_my_account_tablet_heading_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_tablet_heading_font_size', '' ) ); ?>" class="small-text" placeholder="e.g., 20px" />
                                        <p class="description">Override desktop heading font size for tablets</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Heading Font Weight</th>
                                    <td>
                                        <select name="blocksy_child_my_account_tablet_heading_font_weight">
                                            <option value="">Use Desktop Setting</option>
                                            <option value="300" <?php selected( get_option( 'blocksy_child_my_account_tablet_heading_font_weight', '' ), '300' ); ?>>Light (300)</option>
                                            <option value="400" <?php selected( get_option( 'blocksy_child_my_account_tablet_heading_font_weight', '' ), '400' ); ?>>Normal (400)</option>
                                            <option value="500" <?php selected( get_option( 'blocksy_child_my_account_tablet_heading_font_weight', '' ), '500' ); ?>>Medium (500)</option>
                                            <option value="600" <?php selected( get_option( 'blocksy_child_my_account_tablet_heading_font_weight', '' ), '600' ); ?>>Semi Bold (600)</option>
                                            <option value="700" <?php selected( get_option( 'blocksy_child_my_account_tablet_heading_font_weight', '' ), '700' ); ?>>Bold (700)</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Body Text Font Size</th>
                                    <td>
                                        <input type="text" name="blocksy_child_my_account_tablet_body_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_tablet_body_font_size', '' ) ); ?>" class="small-text" placeholder="e.g., 14px" />
                                        <p class="description">Override desktop body text font size for tablets</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Body Text Font Weight</th>
                                    <td>
                                        <select name="blocksy_child_my_account_tablet_body_font_weight">
                                            <option value="">Use Desktop Setting</option>
                                            <option value="300" <?php selected( get_option( 'blocksy_child_my_account_tablet_body_font_weight', '' ), '300' ); ?>>Light (300)</option>
                                            <option value="400" <?php selected( get_option( 'blocksy_child_my_account_tablet_body_font_weight', '' ), '400' ); ?>>Normal (400)</option>
                                            <option value="500" <?php selected( get_option( 'blocksy_child_my_account_tablet_body_font_weight', '' ), '500' ); ?>>Medium (500)</option>
                                            <option value="600" <?php selected( get_option( 'blocksy_child_my_account_tablet_body_font_weight', '' ), '600' ); ?>>Semi Bold (600)</option>
                                            <option value="700" <?php selected( get_option( 'blocksy_child_my_account_tablet_body_font_weight', '' ), '700' ); ?>>Bold (700)</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Placeholder Font Size</th>
                                    <td>
                                        <input type="text" name="blocksy_child_my_account_tablet_placeholder_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_tablet_placeholder_font_size', '' ) ); ?>" class="small-text" placeholder="e.g., 12px" />
                                        <p class="description">Override desktop placeholder font size for tablets</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Placeholder Font Weight</th>
                                    <td>
                                        <select name="blocksy_child_my_account_tablet_placeholder_font_weight">
                                            <option value="">Use Desktop Setting</option>
                                            <option value="300" <?php selected( get_option( 'blocksy_child_my_account_tablet_placeholder_font_weight', '' ), '300' ); ?>>Light (300)</option>
                                            <option value="400" <?php selected( get_option( 'blocksy_child_my_account_tablet_placeholder_font_weight', '' ), '400' ); ?>>Normal (400)</option>
                                            <option value="500" <?php selected( get_option( 'blocksy_child_my_account_tablet_placeholder_font_weight', '' ), '500' ); ?>>Medium (500)</option>
                                            <option value="600" <?php selected( get_option( 'blocksy_child_my_account_tablet_placeholder_font_weight', '' ), '600' ); ?>>Semi Bold (600)</option>
                                            <option value="700" <?php selected( get_option( 'blocksy_child_my_account_tablet_placeholder_font_weight', '' ), '700' ); ?>>Bold (700)</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Button Font Size</th>
                                    <td>
                                        <input type="text" name="blocksy_child_my_account_tablet_button_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_tablet_button_font_size', '' ) ); ?>" class="small-text" placeholder="e.g., 14px" />
                                        <p class="description">Override desktop button font size for tablets</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Button Font Weight</th>
                                    <td>
                                        <select name="blocksy_child_my_account_tablet_button_font_weight">
                                            <option value="">Use Desktop Setting</option>
                                            <option value="300" <?php selected( get_option( 'blocksy_child_my_account_tablet_button_font_weight', '' ), '300' ); ?>>Light (300)</option>
                                            <option value="400" <?php selected( get_option( 'blocksy_child_my_account_tablet_button_font_weight', '' ), '400' ); ?>>Normal (400)</option>
                                            <option value="500" <?php selected( get_option( 'blocksy_child_my_account_tablet_button_font_weight', '' ), '500' ); ?>>Medium (500)</option>
                                            <option value="600" <?php selected( get_option( 'blocksy_child_my_account_tablet_button_font_weight', '' ), '600' ); ?>>Semi Bold (600)</option>
                                            <option value="700" <?php selected( get_option( 'blocksy_child_my_account_tablet_button_font_weight', '' ), '700' ); ?>>Bold (700)</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div id="mobile-responsive" class="tab-panel" style="display: none;">
                            <h3>Mobile Typography Overrides</h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Heading Font Size</th>
                                    <td>
                                        <input type="text" name="blocksy_child_my_account_mobile_heading_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_mobile_heading_font_size', '' ) ); ?>" class="small-text" placeholder="e.g., 18px" />
                                        <p class="description">Override desktop heading font size for mobile</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Heading Font Weight</th>
                                    <td>
                                        <select name="blocksy_child_my_account_mobile_heading_font_weight">
                                            <option value="">Use Desktop Setting</option>
                                            <option value="300" <?php selected( get_option( 'blocksy_child_my_account_mobile_heading_font_weight', '' ), '300' ); ?>>Light (300)</option>
                                            <option value="400" <?php selected( get_option( 'blocksy_child_my_account_mobile_heading_font_weight', '' ), '400' ); ?>>Normal (400)</option>
                                            <option value="500" <?php selected( get_option( 'blocksy_child_my_account_mobile_heading_font_weight', '' ), '500' ); ?>>Medium (500)</option>
                                            <option value="600" <?php selected( get_option( 'blocksy_child_my_account_mobile_heading_font_weight', '' ), '600' ); ?>>Semi Bold (600)</option>
                                            <option value="700" <?php selected( get_option( 'blocksy_child_my_account_mobile_heading_font_weight', '' ), '700' ); ?>>Bold (700)</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Body Text Font Size</th>
                                    <td>
                                        <input type="text" name="blocksy_child_my_account_mobile_body_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_mobile_body_font_size', '' ) ); ?>" class="small-text" placeholder="e.g., 14px" />
                                        <p class="description">Override desktop body text font size for mobile</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Body Text Font Weight</th>
                                    <td>
                                        <select name="blocksy_child_my_account_mobile_body_font_weight">
                                            <option value="">Use Desktop Setting</option>
                                            <option value="300" <?php selected( get_option( 'blocksy_child_my_account_mobile_body_font_weight', '' ), '300' ); ?>>Light (300)</option>
                                            <option value="400" <?php selected( get_option( 'blocksy_child_my_account_mobile_body_font_weight', '' ), '400' ); ?>>Normal (400)</option>
                                            <option value="500" <?php selected( get_option( 'blocksy_child_my_account_mobile_body_font_weight', '' ), '500' ); ?>>Medium (500)</option>
                                            <option value="600" <?php selected( get_option( 'blocksy_child_my_account_mobile_body_font_weight', '' ), '600' ); ?>>Semi Bold (600)</option>
                                            <option value="700" <?php selected( get_option( 'blocksy_child_my_account_mobile_body_font_weight', '' ), '700' ); ?>>Bold (700)</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Placeholder Font Size</th>
                                    <td>
                                        <input type="text" name="blocksy_child_my_account_mobile_placeholder_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_mobile_placeholder_font_size', '' ) ); ?>" class="small-text" placeholder="e.g., 12px" />
                                        <p class="description">Override desktop placeholder font size for mobile</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Placeholder Font Weight</th>
                                    <td>
                                        <select name="blocksy_child_my_account_mobile_placeholder_font_weight">
                                            <option value="">Use Desktop Setting</option>
                                            <option value="300" <?php selected( get_option( 'blocksy_child_my_account_mobile_placeholder_font_weight', '' ), '300' ); ?>>Light (300)</option>
                                            <option value="400" <?php selected( get_option( 'blocksy_child_my_account_mobile_placeholder_font_weight', '' ), '400' ); ?>>Normal (400)</option>
                                            <option value="500" <?php selected( get_option( 'blocksy_child_my_account_mobile_placeholder_font_weight', '' ), '500' ); ?>>Medium (500)</option>
                                            <option value="600" <?php selected( get_option( 'blocksy_child_my_account_mobile_placeholder_font_weight', '' ), '600' ); ?>>Semi Bold (600)</option>
                                            <option value="700" <?php selected( get_option( 'blocksy_child_my_account_mobile_placeholder_font_weight', '' ), '700' ); ?>>Bold (700)</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Button Font Size</th>
                                    <td>
                                        <input type="text" name="blocksy_child_my_account_mobile_button_font_size" value="<?php echo esc_attr( get_option( 'blocksy_child_my_account_mobile_button_font_size', '' ) ); ?>" class="small-text" placeholder="e.g., 14px" />
                                        <p class="description">Override desktop button font size for mobile</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Button Font Weight</th>
                                    <td>
                                        <select name="blocksy_child_my_account_mobile_button_font_weight">
                                            <option value="">Use Desktop Setting</option>
                                            <option value="300" <?php selected( get_option( 'blocksy_child_my_account_mobile_button_font_weight', '' ), '300' ); ?>>Light (300)</option>
                                            <option value="400" <?php selected( get_option( 'blocksy_child_my_account_mobile_button_font_weight', '' ), '400' ); ?>>Normal (400)</option>
                                            <option value="500" <?php selected( get_option( 'blocksy_child_my_account_mobile_button_font_weight', '' ), '500' ); ?>>Medium (500)</option>
                                            <option value="600" <?php selected( get_option( 'blocksy_child_my_account_mobile_button_font_weight', '' ), '600' ); ?>>Semi Bold (600)</option>
                                            <option value="700" <?php selected( get_option( 'blocksy_child_my_account_mobile_button_font_weight', '' ), '700' ); ?>>Bold (700)</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.color-picker').wpColorPicker();
        });
        </script>
        <?php
    }
}

// Initialize the class
new Blocksy_Child_Blaze_My_Account();
