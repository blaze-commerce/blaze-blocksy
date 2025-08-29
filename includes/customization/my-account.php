<?php
/**
 * My Account Form Customization
 *
 * Handles WooCommerce my-account form customization with template overrides
 * and dynamic styling. Now integrated with WordPress Customizer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blocksy Child Blaze My Account Class
 */
class Blocksy_Child_Blaze_My_Account {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_fallback' ), 20 );
		add_filter( 'woocommerce_locate_template', array( $this, 'override_my_account_template' ), 10, 3 );
		add_action( 'wp_head', array( $this, 'output_custom_css' ) );
		add_action( 'wp_footer', array( $this, 'add_debug_info' ) );

		// Load customizer integration
		require_once get_stylesheet_directory() . '/includes/customization/my-account-customizer.php';

		// Migrate existing settings to customizer
		add_action( 'init', array( $this, 'migrate_settings_to_customizer' ) );
	}

	/**
	 * Migrate existing settings from options to theme mods
	 */
	public function migrate_settings_to_customizer() {
		// Check if migration has already been done
		if ( get_option( 'blocksy_my_account_migrated_to_customizer', false ) ) {
			return;
		}

		// List of all settings to migrate
		$settings_to_migrate = array(
			'blocksy_child_my_account_template',
			'blocksy_child_my_account_heading_font',
			'blocksy_child_my_account_heading_font_size',
			'blocksy_child_my_account_heading_font_color',
			'blocksy_child_my_account_heading_font_weight',
			'blocksy_child_my_account_heading_text_transform',
			'blocksy_child_my_account_body_font',
			'blocksy_child_my_account_body_font_size',
			'blocksy_child_my_account_body_font_color',
			'blocksy_child_my_account_body_font_weight',
			'blocksy_child_my_account_body_text_transform',
			'blocksy_child_my_account_placeholder_font',
			'blocksy_child_my_account_placeholder_font_size',
			'blocksy_child_my_account_placeholder_font_color',
			'blocksy_child_my_account_placeholder_font_weight',
			'blocksy_child_my_account_placeholder_text_transform',
			'blocksy_child_my_account_button_font',
			'blocksy_child_my_account_button_font_size',
			'blocksy_child_my_account_button_font_weight',
			'blocksy_child_my_account_button_text_transform',
			'blocksy_child_my_account_button_color',
			'blocksy_child_my_account_button_text_color',
			'blocksy_child_my_account_button_hover_color',
			'blocksy_child_my_account_button_hover_text_color',
			'blocksy_child_my_account_input_background_color',
			'blocksy_child_my_account_input_border_color',
			'blocksy_child_my_account_input_text_color',
			'blocksy_child_my_account_button_padding_top',
			'blocksy_child_my_account_button_padding_right',
			'blocksy_child_my_account_button_padding_bottom',
			'blocksy_child_my_account_button_padding_left',
			// Responsive settings
			'blocksy_child_my_account_tablet_heading_font_size',
			'blocksy_child_my_account_tablet_heading_font_weight',
			'blocksy_child_my_account_tablet_body_font_size',
			'blocksy_child_my_account_tablet_body_font_weight',
			'blocksy_child_my_account_tablet_placeholder_font_size',
			'blocksy_child_my_account_tablet_placeholder_font_weight',
			'blocksy_child_my_account_tablet_button_font_size',
			'blocksy_child_my_account_tablet_button_font_weight',
			'blocksy_child_my_account_mobile_heading_font_size',
			'blocksy_child_my_account_mobile_heading_font_weight',
			'blocksy_child_my_account_mobile_body_font_size',
			'blocksy_child_my_account_mobile_body_font_weight',
			'blocksy_child_my_account_mobile_placeholder_font_size',
			'blocksy_child_my_account_mobile_placeholder_font_weight',
			'blocksy_child_my_account_mobile_button_font_size',
			'blocksy_child_my_account_mobile_button_font_weight',
		);

		// Migrate each setting
		foreach ( $settings_to_migrate as $setting ) {
			$option_value = get_option( $setting );
			if ( $option_value !== false ) {
				set_theme_mod( $setting, $option_value );
			}
		}

		// Mark migration as complete
		update_option( 'blocksy_my_account_migrated_to_customizer', true );
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		// Only load if we should load assets
		if ( ! $this->should_load_assets() ) {
			return;
		}

		// Enqueue base CSS
		if ( file_exists( get_stylesheet_directory() . '/assets/css/my-account.css' ) ) {
			wp_enqueue_style(
				'blocksy-child-my-account-style',
				get_stylesheet_directory_uri() . '/assets/css/my-account.css',
				array(),
				filemtime( get_stylesheet_directory() . '/assets/css/my-account.css' ),
				'all'
			);
		}

		// Enqueue JavaScript if exists
		if ( file_exists( get_stylesheet_directory() . '/assets/js/my-account.js' ) ) {
			wp_enqueue_script(
				'blocksy-child-my-account-script',
				get_stylesheet_directory_uri() . '/assets/js/my-account.js',
				array( 'jquery' ),
				filemtime( get_stylesheet_directory() . '/assets/js/my-account.js' ),
				true
			);
		}
	}

	/**
	 * Fallback script loading
	 */
	public function enqueue_scripts_fallback() {
		// Check if our main style was loaded
		if ( wp_style_is( 'blocksy-child-my-account-style', 'enqueued' ) ) {
			return;
		}

		// Force load on specific WooCommerce pages
		$should_force_load = false;
		$current_url       = $_SERVER['REQUEST_URI'] ?? '';

		// Check URL patterns
		if ( strpos( $current_url, 'my-account' ) !== false ) {
			$should_force_load = true;
		}

		// Check if page contains WooCommerce login form
		global $post;
		if ( $post && (
				has_shortcode( $post->post_content, 'woocommerce_my_account' ) ||
				has_shortcode( $post->post_content, 'woocommerce_login' ) ) ) {
				$should_force_load = true;
		}

		// Force load if custom template is selected
		$selected_template = get_theme_mod( 'blocksy_child_my_account_template', 'default' );
		if ( $selected_template !== 'default' ) {
			$should_force_load = true;
		}

		if ( $should_force_load ) {
			if ( file_exists( get_stylesheet_directory() . '/assets/css/my-account.css' ) ) {
				wp_enqueue_style(
					'blocksy-child-my-account-style-fallback',
					get_stylesheet_directory_uri() . '/assets/css/my-account.css',
					array(),
					filemtime( get_stylesheet_directory() . '/assets/css/my-account.css' ),
					'all'
				);
			}
		}
	}

	/**
	 * Check if we should load assets
	 */
	private function should_load_assets() {
		// Always load on WooCommerce account pages
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return true;
		}

		// Check if we're on a page that contains my-account content
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

		// Load if we're using a custom template
		$selected_template = get_theme_mod( 'blocksy_child_my_account_template', 'default' );
		if ( $selected_template !== 'default' ) {
			// Load on WooCommerce pages
			if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
				return true;
			}

			// Fallback: Load on any page that might be WooCommerce related
			$current_url = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			if ( ! empty( $current_url ) ) {
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
			$selected_template = get_theme_mod( 'blocksy_child_my_account_template', 'default' );

			if ( $selected_template === 'default' ) {
				return $template;
			}

			$theme_path = get_stylesheet_directory() . '/woocommerce/myaccount/';
			if ( $selected_template === 'template1' ) {
				$template_file = $theme_path . 'template1/form-login.php';
			} elseif ( $selected_template === 'template2' ) {
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
		$selected_template = get_theme_mod( 'blocksy_child_my_account_template', 'default' );

		if ( $selected_template === 'default' ) {
			return;
		}

		// Get all customization settings from theme mods
		$heading_font           = get_theme_mod( 'blocksy_child_my_account_heading_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' );
		$heading_font_size      = get_theme_mod( 'blocksy_child_my_account_heading_font_size', '24px' );
		$heading_color          = get_theme_mod( 'blocksy_child_my_account_heading_font_color', '#333333' );
		$heading_font_weight    = get_theme_mod( 'blocksy_child_my_account_heading_font_weight', '600' );
		$heading_text_transform = get_theme_mod( 'blocksy_child_my_account_heading_text_transform', 'none' );

		// Continue with more CSS generation in the next part...
		$this->output_dynamic_css(
			$selected_template,
			compact(
				'heading_font',
				'heading_font_size',
				'heading_color',
				'heading_font_weight',
				'heading_text_transform'
			)
		);
	}

	/**
	 * Output dynamic CSS based on settings
	 */
	private function output_dynamic_css( $template, $settings ) {
		extract( $settings );

		// Get all settings from theme mods
		$body_font           = get_theme_mod( 'blocksy_child_my_account_body_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' );
		$body_font_size      = get_theme_mod( 'blocksy_child_my_account_body_font_size', '16px' );
		$body_color          = get_theme_mod( 'blocksy_child_my_account_body_font_color', '#666666' );
		$body_font_weight    = get_theme_mod( 'blocksy_child_my_account_body_font_weight', '400' );
		$body_text_transform = get_theme_mod( 'blocksy_child_my_account_body_text_transform', 'none' );

		$placeholder_font           = get_theme_mod( 'blocksy_child_my_account_placeholder_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' );
		$placeholder_font_size      = get_theme_mod( 'blocksy_child_my_account_placeholder_font_size', '14px' );
		$placeholder_color          = get_theme_mod( 'blocksy_child_my_account_placeholder_font_color', '#999999' );
		$placeholder_font_weight    = get_theme_mod( 'blocksy_child_my_account_placeholder_font_weight', '400' );
		$placeholder_text_transform = get_theme_mod( 'blocksy_child_my_account_placeholder_text_transform', 'none' );

		$button_font           = get_theme_mod( 'blocksy_child_my_account_button_font', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' );
		$button_font_size      = get_theme_mod( 'blocksy_child_my_account_button_font_size', '16px' );
		$button_font_weight    = get_theme_mod( 'blocksy_child_my_account_button_font_weight', '600' );
		$button_text_transform = get_theme_mod( 'blocksy_child_my_account_button_text_transform', 'none' );

		$button_background       = get_theme_mod( 'blocksy_child_my_account_button_color', '#007cba' );
		$button_text_color       = get_theme_mod( 'blocksy_child_my_account_button_text_color', '#ffffff' );
		$button_hover_background = get_theme_mod( 'blocksy_child_my_account_button_hover_color', '#005a87' );
		$button_hover_text_color = get_theme_mod( 'blocksy_child_my_account_button_hover_text_color', '#ffffff' );
		$input_background        = get_theme_mod( 'blocksy_child_my_account_input_background_color', '#ffffff' );
		$input_border            = get_theme_mod( 'blocksy_child_my_account_input_border_color', '#dddddd' );
		$input_text_color        = get_theme_mod( 'blocksy_child_my_account_input_text_color', '#333333' );

		$button_padding_top    = get_theme_mod( 'blocksy_child_my_account_button_padding_top', '12px' );
		$button_padding_right  = get_theme_mod( 'blocksy_child_my_account_button_padding_right', '24px' );
		$button_padding_bottom = get_theme_mod( 'blocksy_child_my_account_button_padding_bottom', '12px' );
		$button_padding_left   = get_theme_mod( 'blocksy_child_my_account_button_padding_left', '24px' );

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
		$tablet_heading_font_size       = get_theme_mod( 'blocksy_child_my_account_tablet_heading_font_size', '' );
		$tablet_heading_font_weight     = get_theme_mod( 'blocksy_child_my_account_tablet_heading_font_weight', '' );
		$tablet_body_font_size          = get_theme_mod( 'blocksy_child_my_account_tablet_body_font_size', '' );
		$tablet_body_font_weight        = get_theme_mod( 'blocksy_child_my_account_tablet_body_font_weight', '' );
		$tablet_placeholder_font_size   = get_theme_mod( 'blocksy_child_my_account_tablet_placeholder_font_size', '' );
		$tablet_placeholder_font_weight = get_theme_mod( 'blocksy_child_my_account_tablet_placeholder_font_weight', '' );
		$tablet_button_font_size        = get_theme_mod( 'blocksy_child_my_account_tablet_button_font_size', '' );
		$tablet_button_font_weight      = get_theme_mod( 'blocksy_child_my_account_tablet_button_font_weight', '' );

		// Get mobile settings
		$mobile_heading_font_size       = get_theme_mod( 'blocksy_child_my_account_mobile_heading_font_size', '' );
		$mobile_heading_font_weight     = get_theme_mod( 'blocksy_child_my_account_mobile_heading_font_weight', '' );
		$mobile_body_font_size          = get_theme_mod( 'blocksy_child_my_account_mobile_body_font_size', '' );
		$mobile_body_font_weight        = get_theme_mod( 'blocksy_child_my_account_mobile_body_font_weight', '' );
		$mobile_placeholder_font_size   = get_theme_mod( 'blocksy_child_my_account_mobile_placeholder_font_size', '' );
		$mobile_placeholder_font_weight = get_theme_mod( 'blocksy_child_my_account_mobile_placeholder_font_weight', '' );
		$mobile_button_font_size        = get_theme_mod( 'blocksy_child_my_account_mobile_button_font_size', '' );
		$mobile_button_font_weight      = get_theme_mod( 'blocksy_child_my_account_mobile_button_font_weight', '' );

		// Output tablet styles if any settings exist
		if ( $tablet_heading_font_size || $tablet_heading_font_weight || $tablet_body_font_size || $tablet_body_font_weight ||
			$tablet_placeholder_font_size || $tablet_placeholder_font_weight || $tablet_button_font_size || $tablet_button_font_weight ) {

			echo '<style type="text/css">
                @media (max-width: 1023px) and (min-width: 768px) {
                    /* Tablet Styles for Template ' . esc_attr( $template ) . ' */';

			if ( $tablet_heading_font_size || $tablet_heading_font_weight ) {
				echo '.blaze-login-register.' . esc_attr( $template ) . ' h2 {';
				if ( $tablet_heading_font_size ) {
					echo 'font-size: ' . esc_attr( $tablet_heading_font_size ) . ' !important;';
				}
				if ( $tablet_heading_font_weight ) {
					echo 'font-weight: ' . esc_attr( $tablet_heading_font_weight ) . ' !important;';
				}
				echo '}';
			}

			if ( $tablet_body_font_size || $tablet_body_font_weight ) {
				echo '.blaze-login-register.' . esc_attr( $template ) . ' p,
                      .blaze-login-register.' . esc_attr( $template ) . ' label,
                      .blaze-login-register.' . esc_attr( $template ) . ' span,
                      .blaze-login-register.' . esc_attr( $template ) . ' a {';
				if ( $tablet_body_font_size ) {
					echo 'font-size: ' . esc_attr( $tablet_body_font_size ) . ' !important;';
				}
				if ( $tablet_body_font_weight ) {
					echo 'font-weight: ' . esc_attr( $tablet_body_font_weight ) . ' !important;';
				}
				echo '}';
			}

			if ( $tablet_placeholder_font_size || $tablet_placeholder_font_weight ) {
				echo '.blaze-login-register.' . esc_attr( $template ) . ' input::placeholder {';
				if ( $tablet_placeholder_font_size ) {
					echo 'font-size: ' . esc_attr( $tablet_placeholder_font_size ) . ' !important;';
				}
				if ( $tablet_placeholder_font_weight ) {
					echo 'font-weight: ' . esc_attr( $tablet_placeholder_font_weight ) . ' !important;';
				}
				echo '}';
			}

			if ( $tablet_button_font_size || $tablet_button_font_weight ) {
				echo '.blaze-login-register.' . esc_attr( $template ) . ' button,
                      .blaze-login-register.' . esc_attr( $template ) . ' .button {';
				if ( $tablet_button_font_size ) {
					echo 'font-size: ' . esc_attr( $tablet_button_font_size ) . ' !important;';
				}
				if ( $tablet_button_font_weight ) {
					echo 'font-weight: ' . esc_attr( $tablet_button_font_weight ) . ' !important;';
				}
				echo '}';
			}

			echo '}
                </style>';
		}

		// Output mobile styles if any settings exist
		if ( $mobile_heading_font_size || $mobile_heading_font_weight || $mobile_body_font_size || $mobile_body_font_weight ||
			$mobile_placeholder_font_size || $mobile_placeholder_font_weight || $mobile_button_font_size || $mobile_button_font_weight ) {

			echo '<style type="text/css">
                @media (max-width: 767px) {
                    /* Mobile Styles for Template ' . esc_attr( $template ) . ' */';

			if ( $mobile_heading_font_size || $mobile_heading_font_weight ) {
				echo '.blaze-login-register.' . esc_attr( $template ) . ' h2 {';
				if ( $mobile_heading_font_size ) {
					echo 'font-size: ' . esc_attr( $mobile_heading_font_size ) . ' !important;';
				}
				if ( $mobile_heading_font_weight ) {
					echo 'font-weight: ' . esc_attr( $mobile_heading_font_weight ) . ' !important;';
				}
				echo '}';
			}

			if ( $mobile_body_font_size || $mobile_body_font_weight ) {
				echo '.blaze-login-register.' . esc_attr( $template ) . ' p,
                      .blaze-login-register.' . esc_attr( $template ) . ' label,
                      .blaze-login-register.' . esc_attr( $template ) . ' span,
                      .blaze-login-register.' . esc_attr( $template ) . ' a {';
				if ( $mobile_body_font_size ) {
					echo 'font-size: ' . esc_attr( $mobile_body_font_size ) . ' !important;';
				}
				if ( $mobile_body_font_weight ) {
					echo 'font-weight: ' . esc_attr( $mobile_body_font_weight ) . ' !important;';
				}
				echo '}';
			}

			if ( $mobile_placeholder_font_size || $mobile_placeholder_font_weight ) {
				echo '.blaze-login-register.' . esc_attr( $template ) . ' input::placeholder {';
				if ( $mobile_placeholder_font_size ) {
					echo 'font-size: ' . esc_attr( $mobile_placeholder_font_size ) . ' !important;';
				}
				if ( $mobile_placeholder_font_weight ) {
					echo 'font-weight: ' . esc_attr( $mobile_placeholder_font_weight ) . ' !important;';
				}
				echo '}';
			}

			if ( $mobile_button_font_size || $mobile_button_font_weight ) {
				echo '.blaze-login-register.' . esc_attr( $template ) . ' button,
                      .blaze-login-register.' . esc_attr( $template ) . ' .button {';
				if ( $mobile_button_font_size ) {
					echo 'font-size: ' . esc_attr( $mobile_button_font_size ) . ' !important;';
				}
				if ( $mobile_button_font_weight ) {
					echo 'font-weight: ' . esc_attr( $mobile_button_font_weight ) . ' !important;';
				}
				echo '}';
			}

			echo '}
                </style>';
		}
	}

	/**
	 * Add debug information
	 */
	public function add_debug_info() {
		if ( isset( $_GET['blaze_debug'] ) && $_GET['blaze_debug'] == '1' ) {
			$selected_template = get_theme_mod( 'blocksy_child_my_account_template', 'default' );

			echo '<div style="position: fixed; top: 10px; right: 10px; background: #000; color: #fff; padding: 10px; z-index: 9999; font-size: 12px;">';
			echo '<strong>Blaze My Account Debug</strong><br>';
			echo 'Selected Template: ' . esc_html( $selected_template ) . '<br>';
			echo 'CSS File Exists: ' . ( file_exists( get_stylesheet_directory() . '/assets/css/my-account.css' ) ? 'Yes' : 'No' ) . '<br>';
			echo 'JS File Exists: ' . ( file_exists( get_stylesheet_directory() . '/assets/js/my-account.js' ) ? 'Yes' : 'No' ) . '<br>';
			echo '</div>';
		}
	}
}

// Initialize the class
new Blocksy_Child_Blaze_My_Account();
