<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Off-Canvas Wishlist Functionality for Blocksy Child Theme
 *
 * This file adds off-canvas wishlist functionality without modifying plugin files.
 * It uses WordPress hooks and filters to extend the existing wishlist behavior.
 *
 * @package BlocksyChild
 * @since 1.0.0
 */

/**
 * Helper class for common wishlist operations and utilities.
 *
 * @since 1.0.0
 */
class BlocksyChildWishlistHelper {

	/**
	 * Default values for various settings.
	 */
	const DEFAULT_CANVAS_WIDTH = array(
		'desktop' => '400px',
		'tablet' => '350px',
		'mobile' => '300px',
	);

	const DEFAULT_ICON_SIZE = '18';
	const DEFAULT_CLOSE_ICON_SIZE = '32';
	const DEFAULT_COLUMNS = '2';
	const DEFAULT_POSITION = 'right-side';
	const DEFAULT_ICON_TYPE = 'type-1';

	/**
	 * Cache for theme mod values to avoid repeated calls.
	 *
	 * @var array
	 */
	private static $theme_mod_cache = array();

	/**
	 * Get theme mod value with caching and fallback support.
	 *
	 * @param string $option Option name.
	 * @param mixed  $default Default value.
	 * @return mixed Option value.
	 */
	public static function get_theme_mod( $option, $default = null ) {
		if ( isset( self::$theme_mod_cache[ $option ] ) ) {
			return self::$theme_mod_cache[ $option ];
		}

		$value = function_exists( 'blocksy_get_theme_mod' )
			? blocksy_get_theme_mod( $option, $default )
			: get_theme_mod( $option, $default );

		self::$theme_mod_cache[ $option ] = $value;
		return $value;
	}

	/**
	 * Check if Blocksy WooCommerce Extra extension is active.
	 *
	 * @return bool True if extension is active.
	 */
	public static function is_woocommerce_extra_active() {
		return class_exists( '\Blocksy\Extensions\WoocommerceExtra\WishList' );
	}

	/**
	 * Check if off-canvas mode is enabled.
	 *
	 * @return bool True if off-canvas mode is enabled.
	 */
	public static function is_offcanvas_enabled() {
		return self::get_theme_mod( 'wishlist_display_mode', 'page' ) === 'offcanvas';
	}

	/**
	 * Get wishlist extension instance.
	 *
	 * @return object|null Wishlist extension instance or null.
	 */
	public static function get_wishlist_extension() {
		if ( ! function_exists( 'blc_get_ext' ) ) {
			return null;
		}

		$ext = blc_get_ext( 'woocommerce-extra' );
		return $ext ? $ext->get_wish_list() : null;
	}

	/**
	 * Get current wishlist items.
	 *
	 * @return array Wishlist items.
	 */
	public static function get_current_wishlist() {
		$wishlist_ext = self::get_wishlist_extension();
		return $wishlist_ext ? $wishlist_ext->get_current_wish_list() : array();
	}

	/**
	 * Extract product IDs from wishlist data structure.
	 *
	 * @param array $wishlist Wishlist items.
	 * @return array Product IDs.
	 */
	public static function extract_product_ids( $wishlist ) {
		$product_ids = array();

		if ( empty( $wishlist ) ) {
			return $product_ids;
		}

		foreach ( $wishlist as $item ) {
			if ( is_array( $item ) && isset( $item['id'] ) ) {
				$product_ids[] = $item['id'];
			} elseif ( is_numeric( $item ) ) {
				$product_ids[] = $item;
			}
		}

		return $product_ids;
	}

	/**
	 * Get wishlist count.
	 *
	 * @return int Number of items in wishlist.
	 */
	public static function get_wishlist_count() {
		$wishlist = self::get_current_wishlist();
		return ! empty( $wishlist ) ? count( $wishlist ) : 0;
	}

	/**
	 * Check if wishlist is empty.
	 *
	 * @return bool True if wishlist is empty.
	 */
	public static function is_wishlist_empty() {
		$wishlist    = self::get_current_wishlist();
		$product_ids = self::extract_product_ids( $wishlist );
		return empty( $product_ids );
	}

	/**
	 * Sanitize and validate canvas width setting.
	 *
	 * @param mixed $canvas_width Canvas width setting.
	 * @return array Validated canvas width array.
	 */
	public static function sanitize_canvas_width( $canvas_width ) {
		// Handle both string and array formats from Customizer
		if ( ! is_array( $canvas_width ) ) {
			$desktop_value = is_string( $canvas_width ) && ! empty( $canvas_width )
				? $canvas_width
				: self::DEFAULT_CANVAS_WIDTH['desktop'];

			$canvas_width = array(
				'desktop' => $desktop_value,
				'tablet' => self::DEFAULT_CANVAS_WIDTH['tablet'],
				'mobile' => self::DEFAULT_CANVAS_WIDTH['mobile'],
			);
		}

		return wp_parse_args( $canvas_width, self::DEFAULT_CANVAS_WIDTH );
	}

	/**
	 * Sanitize icon size value.
	 *
	 * @param string $size Icon size value.
	 * @param string $default Default size.
	 * @return string Sanitized size with px unit.
	 */
	public static function sanitize_icon_size( $size, $default = self::DEFAULT_ICON_SIZE ) {
		if ( empty( $size ) ) {
			$size = $default;
		}

		return is_numeric( $size ) ? $size . 'px' : $size;
	}
}

/**
 * Customizer settings for wishlist off-canvas functionality.
 *
 * @since 1.0.0
 */
class BlocksyChildWishlistCustomizer {

	/**
	 * Add off-canvas settings to wishlist customizer options.
	 *
	 * @param array $opts Existing customizer options.
	 * @return array Modified options with off-canvas settings.
	 */
	public static function add_offcanvas_settings( $opts ) {
		// Add display mode setting to existing wishlist panel
		if ( isset( $opts['has_wishlist_panel']['inner-options'] ) ) {
			$opts['has_wishlist_panel']['inner-options']['wishlist_display_mode'] = array(
				'label' => __( 'Wishlist Display Mode', 'blocksy-companion' ),
				'type' => 'ct-radio',
				'value' => 'page',
				'view' => 'text',
				'design' => 'block',
				'divider' => 'top',
				'desc' => __( 'Choose how the wishlist should be displayed when accessed from the header.', 'blocksy-companion' ),
				'choices' => array(
					'page' => __( 'Page', 'blocksy-companion' ),
					'offcanvas' => __( 'Off Canvas', 'blocksy-companion' ),
				),
			);
		}

		// Add separate panel for off-canvas specific settings
		$opts['wishlist_offcanvas_panel'] = self::get_offcanvas_panel_settings();

		return $opts;
	}

	/**
	 * Get off-canvas panel settings configuration.
	 *
	 * @return array Panel settings array.
	 */
	private static function get_offcanvas_panel_settings() {
		return array(
			'label' => __( 'Wishlist Off-Canvas Settings', 'blocksy-companion' ),
			'type' => 'ct-panel',
			'setting' => array( 'transport' => 'postMessage' ),
			'inner-options' => array_merge(
				self::get_layout_settings(),
				self::get_header_icon_settings(),
				self::get_empty_state_settings(),
				self::get_product_display_settings(),
				self::get_recommendations_settings()
			),
		);
	}

	/**
	 * Get layout settings for off-canvas.
	 *
	 * @return array Layout settings.
	 */
	private static function get_layout_settings() {
		return array(
			'wishlist_offcanvas_width' => array(
				'label' => __( 'Off-Canvas Width', 'blocksy-companion' ),
				'type' => 'ct-slider',
				'value' => BlocksyChildWishlistHelper::DEFAULT_CANVAS_WIDTH,
				'units' => array(
					array( 'unit' => 'px', 'min' => 250, 'max' => 800 ),
					array( 'unit' => 'vw', 'min' => 20, 'max' => 90 ),
					array( 'unit' => '%', 'min' => 20, 'max' => 90 ),
				),
				'responsive' => true,
				'desc' => __( 'Set the width of the off-canvas panel for different devices.', 'blocksy-companion' ),
			),
			'wishlist_offcanvas_columns' => array(
				'label' => __( 'Number of Columns', 'blocksy-companion' ),
				'type' => 'ct-radio',
				'value' => BlocksyChildWishlistHelper::DEFAULT_COLUMNS,
				'view' => 'text',
				'design' => 'block',
				'choices' => array(
					'1' => __( '1 Column', 'blocksy-companion' ),
					'2' => __( '2 Columns', 'blocksy-companion' ),
				),
				'desc' => __( 'Choose how many columns to display products in the off-canvas.', 'blocksy-companion' ),
			),
			'wishlist_offcanvas_position' => array(
				'label' => __( 'Off-Canvas Position', 'blocksy-companion' ),
				'type' => 'ct-radio',
				'value' => BlocksyChildWishlistHelper::DEFAULT_POSITION,
				'view' => 'text',
				'design' => 'block',
				'choices' => array(
					'left-side' => __( 'Left Side', 'blocksy-companion' ),
					'right-side' => __( 'Right Side', 'blocksy-companion' ),
				),
				'desc' => __( 'Choose which side the off-canvas panel slides in from.', 'blocksy-companion' ),
			),
		);
	}

	/**
	 * Get header icon settings.
	 *
	 * @return array Header icon settings.
	 */
	private static function get_header_icon_settings() {
		return array(
			blocksy_rand_md5() => array(
				'type' => 'ct-title',
				'label' => __( 'Header Icon Settings', 'blocksy-companion' ),
				'desc' => __( 'Configure the wishlist icon that appears in the header when off-canvas mode is enabled.', 'blocksy-companion' ),
			),
			'wishlist_offcanvas_icon_source' => array(
				'label' => __( 'Icon Source', 'blocksy-companion' ),
				'type' => 'ct-radio',
				'value' => 'header',
				'view' => 'text',
				'design' => 'block',
				'desc' => __( 'Choose the source for the wishlist icon.', 'blocksy-companion' ),
				'choices' => array(
					'header' => __( 'Use Header Settings', 'blocksy-companion' ),
					'custom' => __( 'Custom Configuration', 'blocksy-companion' ),
				),
			),
			blocksy_rand_md5() => array(
				'type' => 'ct-condition',
				'condition' => array( 'wishlist_offcanvas_icon_source' => 'custom' ),
				'options' => self::get_custom_icon_settings(),
			),
		);
	}

	/**
	 * Get custom icon settings.
	 *
	 * @return array Custom icon settings.
	 */
	private static function get_custom_icon_settings() {
		return array(
			'wishlist_offcanvas_icon_type_source' => array(
				'label' => __( 'Icon Type', 'blocksy-companion' ),
				'type' => 'ct-radio',
				'value' => 'default',
				'view' => 'text',
				'design' => 'block',
				'desc' => __( 'Choose between default icon types or custom icon.', 'blocksy-companion' ),
				'choices' => array(
					'default' => __( 'Default Icons', 'blocksy-companion' ),
					'custom' => __( 'Custom Icon', 'blocksy-companion' ),
				),
			),
			blocksy_rand_md5() => array(
				'type' => 'ct-condition',
				'condition' => array( 'wishlist_offcanvas_icon_type_source' => 'default' ),
				'options' => self::get_default_icon_options(),
			),
			blocksy_rand_md5() => array(
				'type' => 'ct-condition',
				'condition' => array( 'wishlist_offcanvas_icon_type_source' => 'custom' ),
				'options' => self::get_custom_icon_picker_options(),
			),
			'wishlist_offcanvas_icon_size' => array(
				'label' => __( 'Icon Size', 'blocksy-companion' ),
				'type' => 'text',
				'value' => BlocksyChildWishlistHelper::DEFAULT_ICON_SIZE,
				'attr' => array( 'placeholder' => BlocksyChildWishlistHelper::DEFAULT_ICON_SIZE ),
				'setting' => array( 'transport' => 'postMessage' ),
				'desc' => __( 'Set the size of the wishlist icon in pixels (e.g., 18).', 'blocksy-companion' ),
			),
			'wishlist_offcanvas_close_icon_size' => array(
				'label' => __( 'Close Icon Size', 'blocksy-companion' ),
				'type' => 'text',
				'value' => BlocksyChildWishlistHelper::DEFAULT_CLOSE_ICON_SIZE,
				'attr' => array( 'placeholder' => BlocksyChildWishlistHelper::DEFAULT_CLOSE_ICON_SIZE ),
				'setting' => array( 'transport' => 'postMessage' ),
				'desc' => __( 'Set the size of the close icon in pixels (e.g., 32).', 'blocksy-companion' ),
			),
		);
	}

	/**
	 * Get default icon options.
	 *
	 * @return array Default icon options.
	 */
	private static function get_default_icon_options() {
		return array(
			'wishlist_offcanvas_icon_type' => array(
				'label' => __( 'Icon Style', 'blocksy-companion' ),
				'type' => 'ct-image-picker',
				'value' => BlocksyChildWishlistHelper::DEFAULT_ICON_TYPE,
				'attr' => array(
					'data-type' => 'background',
					'data-columns' => '3',
				),
				'desc' => __( 'Choose from predefined wishlist icon styles.', 'blocksy-companion' ),
				'choices' => array(
					'type-1' => array(
						'src' => function_exists( 'blocksy_image_picker_file' ) ? blocksy_image_picker_file( 'wishlist-1' ) : '',
						'title' => __( 'Type 1', 'blocksy-companion' ),
					),
					'type-2' => array(
						'src' => function_exists( 'blocksy_image_picker_file' ) ? blocksy_image_picker_file( 'wishlist-2' ) : '',
						'title' => __( 'Type 2', 'blocksy-companion' ),
					),
					'type-3' => array(
						'src' => function_exists( 'blocksy_image_picker_file' ) ? blocksy_image_picker_file( 'wishlist-3' ) : '',
						'title' => __( 'Type 3', 'blocksy-companion' ),
					),
				),
			),
		);
	}

	/**
	 * Get custom icon picker options.
	 *
	 * @return array Custom icon picker options.
	 */
	private static function get_custom_icon_picker_options() {
		return array(
			'wishlist_offcanvas_custom_icon' => array(
				'type' => 'icon-picker',
				'label' => __( 'Custom Icon', 'blocksy-companion' ),
				'design' => 'inline',
				'value' => array( 'icon' => 'blc blc-heart' ),
				'desc' => __( 'Select a custom icon for the wishlist header item.', 'blocksy-companion' ),
			),
		);
	}

	/**
	 * Get empty state settings.
	 *
	 * @return array Empty state settings.
	 */
	private static function get_empty_state_settings() {
		return array(
			blocksy_rand_md5() => array(
				'type' => 'ct-title',
				'label' => __( 'Empty State Settings', 'blocksy-companion' ),
				'desc' => __( 'Configure what to display when the wishlist is empty.', 'blocksy-companion' ),
			),
			'wishlist_empty_state_image' => array(
				'label' => __( 'Empty State Image', 'blocksy-companion' ),
				'type' => 'ct-image-uploader',
				'value' => array( 'attachment_id' => null ),
				'attr' => array( 'data-type' => 'no-frame' ),
				'emptyLabel' => __( 'Select Image', 'blocksy-companion' ),
				'filledLabel' => __( 'Change Image', 'blocksy-companion' ),
				'desc' => __( 'Upload an SVG or image to display when the wishlist is empty. Leave empty to show default cart icon.', 'blocksy-companion' ),
			),
			'wishlist_signup_button_url' => array(
				'label' => __( 'Sign Up Button URL', 'blocksy-companion' ),
				'type' => 'text',
				'value' => '',
				'attr' => array( 'placeholder' => wp_registration_url() ),
				'desc' => __( 'Custom URL for the sign up button. Leave empty to use the default WordPress registration URL.', 'blocksy-companion' ),
			),
			'wishlist_signup_button_text' => array(
				'label' => __( 'Sign Up Button Text', 'blocksy-companion' ),
				'type' => 'text',
				'value' => __( 'Sign Up', 'blocksy-companion' ),
				'attr' => array( 'placeholder' => __( 'Sign Up', 'blocksy-companion' ) ),
				'desc' => __( 'Custom text for the sign up button.', 'blocksy-companion' ),
			),
		);
	}

	/**
	 * Get product display settings.
	 *
	 * @return array Product display settings.
	 */
	private static function get_product_display_settings() {
		return array(
			blocksy_rand_md5() => array(
				'type' => 'ct-title',
				'label' => __( 'Product Display Settings', 'blocksy-companion' ),
				'desc' => __( 'Configure what information to show for each product in the off-canvas.', 'blocksy-companion' ),
			),
			'wishlist_show_product_price' => array(
				'label' => __( 'Show Product Price', 'blocksy-companion' ),
				'type' => 'ct-switch',
				'value' => 'yes',
				'desc' => __( 'Display product price in the off-canvas wishlist.', 'blocksy-companion' ),
			),
			'wishlist_show_product_image' => array(
				'label' => __( 'Show Product Image', 'blocksy-companion' ),
				'type' => 'ct-switch',
				'value' => 'yes',
				'desc' => __( 'Display product image in the off-canvas wishlist.', 'blocksy-companion' ),
			),
			'wishlist_show_add_to_cart' => array(
				'label' => __( 'Show Add to Cart Button', 'blocksy-companion' ),
				'type' => 'ct-switch',
				'value' => 'yes',
				'desc' => __( 'Display add to cart button for each product in the off-canvas.', 'blocksy-companion' ),
			),
			'wishlist_show_remove_button' => array(
				'label' => __( 'Show Remove Button', 'blocksy-companion' ),
				'type' => 'ct-switch',
				'value' => 'yes',
				'desc' => __( 'Display remove button for each product in the off-canvas.', 'blocksy-companion' ),
			),
		);
	}

	/**
	 * Get recommendations settings.
	 *
	 * @return array Recommendations settings.
	 */
	private static function get_recommendations_settings() {
		return array(
			blocksy_rand_md5() => array(
				'type' => 'ct-title',
				'label' => __( 'Recommendations Settings', 'blocksy-companion' ),
				'desc' => __( 'Configure the "You May Also Like" section that appears below wishlist items.', 'blocksy-companion' ),
			),
			'wishlist_show_recommendations' => array(
				'label' => __( 'Show Recommendations', 'blocksy-companion' ),
				'type' => 'ct-switch',
				'value' => 'yes',
				'desc' => __( 'Display product recommendations below wishlist items.', 'blocksy-companion' ),
			),
			blocksy_rand_md5() => array(
				'type' => 'ct-condition',
				'condition' => array( 'wishlist_show_recommendations' => 'yes' ),
				'options' => array(
					'wishlist_recommendations_show_image' => array(
						'label' => __( 'Show Product Image', 'blocksy-companion' ),
						'type' => 'ct-switch',
						'value' => 'yes',
						'desc' => __( 'Display product image in recommendations.', 'blocksy-companion' ),
					),
					'wishlist_recommendations_show_price' => array(
						'label' => __( 'Show Product Price', 'blocksy-companion' ),
						'type' => 'ct-switch',
						'value' => 'yes',
						'desc' => __( 'Display product price in recommendations.', 'blocksy-companion' ),
					),
					'wishlist_recommendations_show_add_to_cart' => array(
						'label' => __( 'Show Add to Cart Button', 'blocksy-companion' ),
						'type' => 'ct-switch',
						'value' => 'yes',
						'desc' => __( 'Display add to cart button for recommended products.', 'blocksy-companion' ),
					),
				),
			),
		);
	}
}

/**
 * Main off-canvas wishlist controller class.
 *
 * @since 1.0.0
 */
class BlocksyChildWishlistOffCanvas {

	/**
	 * Renderer instance.
	 *
	 * @var BlocksyChildWishlistRenderer
	 */
	private $renderer;

	/**
	 * Recommendations instance.
	 *
	 * @var BlocksyChildWishlistRecommendations
	 */
	private $recommendations;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Only initialize if the WooCommerce Extra extension is active
		if ( ! BlocksyChildWishlistHelper::is_woocommerce_extra_active() ) {
			return;
		}

		$this->renderer        = new BlocksyChildWishlistRenderer();
		$this->recommendations = new BlocksyChildWishlistRecommendations();

		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Add off-canvas setting to wishlist options
		add_filter( 'blocksy_customizer_options:woocommerce:general:end', array( 'BlocksyChildWishlistCustomizer', 'add_offcanvas_settings' ), 60 );

		// Add custom CSS for the canvas width setting
		add_action( 'wp_head', array( $this, 'add_canvas_width_css' ) );

		// Add off-canvas panel to footer
		add_filter( 'blocksy:footer:offcanvas-drawer', array( $this, 'add_offcanvas_to_footer' ), 10, 2 );

		// Enqueue scripts and styles when off-canvas mode is enabled
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_offcanvas_assets' ) );

		// Add AJAX handlers for off-canvas content
		add_action( 'wp_ajax_load_wishlist_offcanvas', array( $this, 'ajax_load_wishlist_content' ) );
		add_action( 'wp_ajax_nopriv_load_wishlist_offcanvas', array( $this, 'ajax_load_wishlist_content' ) );

		// Modify wishlist header output when off-canvas is enabled (run late to ensure we replace final markup)
		add_filter( 'blocksy:header:render-item', array( $this, 'modify_wishlist_header_output' ), 999, 2 );

		// Force enable recently viewed products tracking
		add_action( 'template_redirect', array( $this->recommendations, 'force_track_product_view' ), 25 );
	}

	/**
	 * Add custom CSS for canvas width and icon settings.
	 */
	public function add_canvas_width_css() {
		if ( ! BlocksyChildWishlistHelper::is_offcanvas_enabled() ) {
			return;
		}

		$canvas_width = BlocksyChildWishlistHelper::sanitize_canvas_width(
			BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_offcanvas_width', BlocksyChildWishlistHelper::DEFAULT_CANVAS_WIDTH )
		);

		$icon_source     = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_offcanvas_icon_source', 'header' );
		$icon_size       = '';
		$close_icon_size = '';

		if ( $icon_source === 'custom' ) {
			$icon_size_value = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_offcanvas_icon_size', BlocksyChildWishlistHelper::DEFAULT_ICON_SIZE );
			$icon_size       = BlocksyChildWishlistHelper::sanitize_icon_size( $icon_size_value );
		}

		$close_icon_size_value = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_offcanvas_close_icon_size', BlocksyChildWishlistHelper::DEFAULT_CLOSE_ICON_SIZE );
		$close_icon_size       = BlocksyChildWishlistHelper::sanitize_icon_size( $close_icon_size_value, BlocksyChildWishlistHelper::DEFAULT_CLOSE_ICON_SIZE );

		$this->output_canvas_css( $canvas_width, $icon_size, $close_icon_size, $icon_source );
	}

	/**
	 * Output the canvas CSS styles.
	 *
	 * @param array  $canvas_width Canvas width settings.
	 * @param string $icon_size Icon size.
	 * @param string $close_icon_size Close icon size.
	 * @param string $icon_source Icon source setting.
	 */
	private function output_canvas_css( $canvas_width, $icon_size, $close_icon_size, $icon_source ) {
		?>
		<style id="wishlist-offcanvas-custom-css">
			/* Canvas Width - Desktop */
			#wishlist-offcanvas-panel[data-behaviour*="side"] .ct-panel-inner {
				width: 100vw;
				max-width:
					<?php echo esc_attr( $canvas_width['desktop'] ); ?>
					!important;
			}

			<?php if ( $icon_source === 'custom' && ! empty( $icon_size ) ) : ?>
				/* Icon Size - Only for off-canvas wishlist trigger */
				#wishlist-offcanvas-panel .ct-icon,
				.ct-offcanvas-wishlist-trigger .ct-icon {
					font-size:
						<?php echo esc_attr( $icon_size ); ?>
						!important;
					width:
						<?php echo esc_attr( $icon_size ); ?>
						!important;
					height:
						<?php echo esc_attr( $icon_size ); ?>
						!important;
				}

			<?php endif; ?>

			<?php if ( ! empty( $close_icon_size ) ) : ?>
				/* Close Icon Size */
				#wishlist-offcanvas-panel .ct-toggle-close .ct-icon {
					font-size:
						<?php echo esc_attr( $close_icon_size ); ?>
						!important;
					width:
						<?php echo esc_attr( $close_icon_size ); ?>
						!important;
					height:
						<?php echo esc_attr( $close_icon_size ); ?>
						!important;
				}

			<?php endif; ?>

			/* Tablet */
			@media (max-width: 999px) {
				#wishlist-offcanvas-panel[data-behaviour*="side"] .ct-panel-inner {
					width: 100vw;
					max-width:
						<?php echo esc_attr( $canvas_width['tablet'] ); ?>
					;
				}
			}

			/* Mobile */
			@media (max-width: 689px) {
				#wishlist-offcanvas-panel[data-behaviour*="side"] .ct-panel-inner {
					width: 100vw;
					max-width:
						<?php echo esc_attr( $canvas_width['mobile'] ); ?>
					;
				}
			}
		</style>
		<?php
	}

	/**
	 * Add off-canvas panel to footer.
	 *
	 * @param array $elements Existing elements.
	 * @param array $payload Payload data.
	 * @return array Modified elements.
	 */
	public function add_offcanvas_to_footer( $elements, $payload ) {
		if ( ! BlocksyChildWishlistHelper::is_offcanvas_enabled() || $payload['location'] !== 'start' ) {
			return $elements;
		}

		$elements[] = $this->renderer->render_wishlist_offcanvas();
		return $elements;
	}

	/**
	 * Enqueue off-canvas assets when needed.
	 */
	public function enqueue_offcanvas_assets() {
		if ( ! BlocksyChildWishlistHelper::is_offcanvas_enabled() ) {
			return;
		}

		$theme_version = wp_get_theme()->get( 'Version' );

		// Enqueue CSS
		wp_enqueue_style(
			'wishlist-offcanvas',
			get_stylesheet_directory_uri() . '/assets/css/wishlist-offcanvas.css',
			array(),
			$theme_version
		);

		// Enqueue JavaScript
		wp_enqueue_script(
			'wishlist-offcanvas',
			get_stylesheet_directory_uri() . '/assets/js/wishlist-offcanvas.js',
			array( 'jquery' ),
			$theme_version,
			true
		);

		// Enqueue frontend JavaScript for DOM modifications
		wp_enqueue_script(
			'wishlist-offcanvas-frontend',
			get_stylesheet_directory_uri() . '/assets/js/wishlist-offcanvas-frontend.js',
			array( 'jquery' ),
			$theme_version,
			true
		);

		// Localize script with AJAX data
		wp_localize_script(
			'wishlist-offcanvas',
			'wishlistOffcanvas',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wishlist_offcanvas_nonce' ),
			)
		);
	}

	/**
	 * AJAX handler for loading wishlist content.
	 */
	public function ajax_load_wishlist_content() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wishlist_offcanvas_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$content = $this->renderer->get_wishlist_content();
		$count   = BlocksyChildWishlistHelper::get_wishlist_count();

		wp_send_json_success( array(
			'content' => $content,
			'count' => $count,
		) );
	}

	/**
	 * Modify wishlist header output when off-canvas is enabled.
	 *
	 * @param string $output Original output.
	 * @param string $item_id Item ID.
	 * @return string Modified output.
	 */
	public function modify_wishlist_header_output( $output, $item_id ) {
		if ( $item_id !== 'wish-list' || ! BlocksyChildWishlistHelper::is_offcanvas_enabled() ) {
			return $output;
		}

		// Get the appropriate icon based on settings
		$new_icon = $this->renderer->get_wishlist_icon();

		if ( $new_icon ) {
			// Replace the first inline SVG in the header output with our icon
			$output = preg_replace( '/<svg[^>]*>.*?<\/svg>/s', $new_icon, $output );
		}

		// Use regex to replace any href value with our off-canvas identifier
		$output = preg_replace( '/href="[^"]*"/', 'href="#wishlist-offcanvas"', $output );

		// Add a data attribute to identify this as an off-canvas trigger
		$output = str_replace( 'class="', 'class="ct-offcanvas-trigger ', $output );

		return $output;
	}
}

/**
 * Renderer class for wishlist off-canvas HTML output.
 *
 * @since 1.0.0
 */
class BlocksyChildWishlistRenderer {

	/**
	 * Render the wishlist off-canvas panel HTML.
	 *
	 * @param array $args Optional arguments.
	 * @return string HTML output.
	 */
	public function render_wishlist_offcanvas( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'has_container' => true,
		) );

		$content           = $this->get_wishlist_content();
		$without_container = '<div class="ct-panel-content"><div class="ct-panel-content-inner">' . $content . '</div></div>';

		if ( ! $args['has_container'] ) {
			return $without_container;
		}

		$behavior      = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_offcanvas_position', BlocksyChildWishlistHelper::DEFAULT_POSITION );
		$close_icon    = $this->get_close_icon_svg();
		$wishlist_icon = $this->get_wishlist_icon();
		$icon_html     = $wishlist_icon ? '<span class="ct-panel-heading-icon">' . $wishlist_icon . '</span> ' : '';

		return sprintf(
			'<div id="wishlist-offcanvas-panel" class="ct-panel ct-header ct-header-wishlist" data-behaviour="%s" role="dialog" aria-label="%s" inert="">
				<div class="ct-panel-inner">
					<div class="ct-panel-actions">
						<span class="ct-panel-heading">%s%s <span class="wishlist-count">(%d)</span></span>
						<button class="ct-toggle-close" data-type="type-1" aria-label="%s">%s</button>
					</div>
					%s
				</div>
			</div>',
			esc_attr( $behavior ),
			esc_attr__( 'Wishlist panel', 'blocksy-companion' ),
			$icon_html,
			esc_html__( 'Wishlist', 'blocksy-companion' ),
			BlocksyChildWishlistHelper::get_wishlist_count(),
			esc_attr__( 'Close wishlist panel', 'blocksy-companion' ),
			$close_icon,
			$without_container
		);
	}

	/**
	 * Get wishlist content HTML.
	 *
	 * @return string HTML content.
	 */
	public function get_wishlist_content() {
		$wishlist_ext = BlocksyChildWishlistHelper::get_wishlist_extension();
		if ( ! $wishlist_ext ) {
			return '<div class="ct-offcanvas-wishlist"><p>' . esc_html__( 'Wishlist functionality is not available.', 'blocksy-companion' ) . '</p></div>';
		}

		$wishlist = BlocksyChildWishlistHelper::get_current_wishlist();

		if ( empty( $wishlist ) ) {
			return $this->get_empty_wishlist_content();
		}

		return $this->render_wishlist_items( $wishlist );
	}

	/**
	 * Get close icon SVG.
	 *
	 * @return string SVG markup.
	 */
	private function get_close_icon_svg() {
		return '<svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15"><path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/></svg>';
	}

	/**
	 * Get empty wishlist content.
	 *
	 * @return string HTML content.
	 */
	private function get_empty_wishlist_content() {
		$is_logged_in      = is_user_logged_in();
		$empty_state_image = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_empty_state_image', array( 'attachment_id' => null ) );

		$html = '<div class="ct-offcanvas-wishlist">';
		$html .= '<div class="wishlist-empty">';

		// Add empty state image if configured
		if ( ! empty( $empty_state_image['attachment_id'] ) ) {
			$image_url = wp_get_attachment_url( $empty_state_image['attachment_id'] );
			$image_alt = get_post_meta( $empty_state_image['attachment_id'], '_wp_attachment_image_alt', true );

			if ( $image_url ) {
				$html .= '<div class="wishlist-empty-image">';
				$html .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image_alt ?: __( 'Empty wishlist', 'blocksy-companion' ) ) . '" />';
				$html .= '</div>';
			}
		} else {
			// Default cart icon
			$html .= '<div class="wishlist-empty-icon">';
			$html .= '<svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">';
			$html .= '<path d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5M17 21a2 2 0 100-4 2 2 0 000 4zM9 21a2 2 0 100-4 2 2 0 000 4z"/>';
			$html .= '</svg>';
			$html .= '</div>';
		}

		$html .= '<p>' . esc_html__( 'Your Wishlist is Empty', 'blocksy-companion' ) . '</p>';

		if ( ! $is_logged_in ) {
			$html .= $this->get_guest_notice_html();
		}
		$html .= '</div>';

		// Add recommendations section
		$recommendations = new BlocksyChildWishlistRecommendations();
		$html .= $recommendations->get_recommendations_section( array( 'include_guest_notice' => false ) );

		$html .= '</div>';
		return $html;
	}

	/**
	 * Render wishlist items.
	 *
	 * @param array $items Wishlist items.
	 * @return string HTML content.
	 */
	private function render_wishlist_items( $items ) {
		$columns          = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_offcanvas_columns', BlocksyChildWishlistHelper::DEFAULT_COLUMNS );
		$show_price       = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_show_product_price', 'yes' ) === 'yes';
		$show_image       = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_show_product_image', 'yes' ) === 'yes';
		$show_add_to_cart = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_show_add_to_cart', 'yes' ) === 'yes';
		$show_remove      = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_show_remove_button', 'yes' ) === 'yes';

		$html = '<div class="ct-offcanvas-wishlist" data-columns="' . esc_attr( $columns ) . '">
			<div class="wishlist-items">';

		foreach ( $items as $item ) {
			$product_id = $this->extract_product_id( $item );
			if ( ! $product_id ) {
				continue;
			}

			$product = wc_get_product( $product_id );
			if ( ! $product || ! $this->is_product_visible( $product ) ) {
				continue;
			}

			$html .= $this->render_single_wishlist_item( $product, $show_image, $show_price, $show_add_to_cart, $show_remove );
		}

		$html .= '</div>';

		// Add recommendations section
		$recommendations = new BlocksyChildWishlistRecommendations();
		$html .= $recommendations->get_recommendations_section();

		$html .= '</div>';
		return $html;
	}

	/**
	 * Extract product ID from wishlist item.
	 *
	 * @param mixed $item Wishlist item.
	 * @return int|null Product ID or null.
	 */
	private function extract_product_id( $item ) {
		if ( isset( $item['id'] ) && is_numeric( $item['id'] ) ) {
			return $item['id'];
		} elseif ( is_numeric( $item ) ) {
			return $item;
		}
		return null;
	}

	/**
	 * Check if product is visible to current user.
	 *
	 * @param WC_Product $product Product object.
	 * @return bool True if visible.
	 */
	private function is_product_visible( $product ) {
		$status = $product->get_status();

		if ( $status === 'trash' ) {
			return false;
		}

		if ( $status === 'private' && ! current_user_can( 'read_private_products' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Render a single wishlist item.
	 *
	 * @param WC_Product $product Product object.
	 * @param bool       $show_image Show image.
	 * @param bool       $show_price Show price.
	 * @param bool       $show_add_to_cart Show add to cart.
	 * @param bool       $show_remove Show remove button.
	 * @return string HTML content.
	 */
	private function render_single_wishlist_item( $product, $show_image, $show_price, $show_add_to_cart, $show_remove ) {
		$product_id = $product->get_id();
		$html       = '<div class="wishlist-item" data-product-id="' . esc_attr( $product_id ) . '">';

		if ( $show_image ) {
			$html .= '<div class="wishlist-item-image">
				<a href="' . esc_url( $product->get_permalink() ) . '">
					' . $product->get_image( 'woocommerce_thumbnail' ) . '
				</a>
			</div>';
		}

		$html .= '<div class="wishlist-item-details">
			<h3 class="wishlist-item-title">
				<a href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $product->get_name() ) . '</a>
			</h3>';

		if ( $show_price ) {
			$html .= '<div class="wishlist-item-price">' . $this->get_product_price_html( $product ) . '</div>';
		}

		// Add action buttons if needed
		if ( ( $show_add_to_cart && $product->is_purchasable() ) || $show_remove ) {
			$html .= '<div class="wishlist-item-actions">';

			if ( $show_add_to_cart && $product->is_purchasable() ) {
				$html .= '<button class="button add_to_cart_button" data-product_id="' . esc_attr( $product_id ) . '">' . esc_html__( 'Add to cart', 'woocommerce' ) . '</button>';
			}

			if ( $show_remove ) {
				$html .= '<button class="ct-wishlist-remove" data-product-id="' . esc_attr( $product_id ) . '">' . esc_html__( 'Remove', 'blocksy-companion' ) . '</button>';
			}

			$html .= '</div>';
		}

		$html .= '</div></div>';
		return $html;
	}

	/**
	 * Get product price HTML with fallback handling.
	 *
	 * @param WC_Product $product Product object.
	 * @return string Price HTML.
	 */
	private function get_product_price_html( $product ) {
		$price_html = $product->get_price_html();

		if ( empty( $price_html ) ) {
			$regular_price = $product->get_regular_price();
			$sale_price    = $product->get_sale_price();

			if ( '' !== $sale_price && '' !== $regular_price ) {
				$price_html = wc_format_sale_price( wc_price( $regular_price ), wc_price( $sale_price ) );
			} else {
				$display_price = wc_get_price_to_display( $product );
				if ( '' !== $display_price && null !== $display_price ) {
					$price_html = wc_price( $display_price );
				}
			}
		}

		return $price_html;
	}

	/**
	 * Get guest notice HTML for logged-out users.
	 *
	 * @return string HTML content.
	 */
	public function get_guest_notice_html() {
		$custom_signup_url  = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_signup_button_url', '' );
		$signup_url         = ! empty( $custom_signup_url ) ? $custom_signup_url : wp_registration_url();
		$signup_button_text = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_signup_button_text', __( 'Sign Up', 'blocksy-companion' ) );

		return '<div class="wishlist-guest-notice">'
			. '<p class="notice-text">' . esc_html__( 'Guest favorites are only saved to your device for 7 days, or until you clear your cache. Sign in or create an account to hang on to your picks.', 'blocksy-companion' ) . '</p>'
			. '<div class="notice-actions">'
			. '<a href="' . esc_url( $signup_url ) . '" class="button notice-signup">' . esc_html( $signup_button_text ) . '</a>'
			. '</div>'
			. '</div>';
	}

	/**
	 * Get the appropriate wishlist icon based on settings.
	 *
	 * @return string Icon HTML.
	 */
	public function get_wishlist_icon() {
		$icon_source = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_offcanvas_icon_source', 'header' );

		if ( $icon_source === 'custom' ) {
			$icon_type_source = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_offcanvas_icon_type_source', 'default' );

			if ( $icon_type_source === 'default' ) {
				$icon_type = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_offcanvas_icon_type', BlocksyChildWishlistHelper::DEFAULT_ICON_TYPE );
				return $this->get_default_wishlist_icon( $icon_type );
			} else {
				$custom_icon = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_offcanvas_custom_icon', array( 'icon' => 'blc blc-heart' ) );

				if ( function_exists( 'blc_get_icon' ) && is_array( $custom_icon ) ) {
					return blc_get_icon( array(
						'icon_descriptor' => $custom_icon,
						'icon_container' => false,
						'icon_html_atts' => array( 'class' => 'ct-icon' ),
					) );
				}
			}
		}

		// If using header settings or custom failed, get from header
		$header_settings = $this->get_header_wishlist_settings();

		if ( ! empty( $header_settings ) ) {
			$header_icon_source = isset( $header_settings['icon_source'] ) ? $header_settings['icon_source'] : 'default';

			if ( $header_icon_source === 'custom' && function_exists( 'blc_get_icon' ) ) {
				$header_icon = isset( $header_settings['icon'] ) ? $header_settings['icon'] : array( 'icon' => 'blc blc-heart' );

				return blc_get_icon( array(
					'icon_descriptor' => $header_icon,
					'icon_container' => false,
				) );
			} else {
				$header_icon_type = isset( $header_settings['wishlist_item_type'] ) ? $header_settings['wishlist_item_type'] : BlocksyChildWishlistHelper::DEFAULT_ICON_TYPE;
				return $this->get_default_wishlist_icon( $header_icon_type );
			}
		}

		// Fallback to default heart icon
		return $this->get_default_wishlist_icon( BlocksyChildWishlistHelper::DEFAULT_ICON_TYPE );
	}

	/**
	 * Get header wishlist settings for icon configuration.
	 *
	 * @return array Header settings.
	 */
	private function get_header_wishlist_settings() {
		$header_builder    = BlocksyChildWishlistHelper::get_theme_mod( 'header_placements', array() );
		$wishlist_settings = array();

		if ( is_array( $header_builder ) ) {
			foreach ( $header_builder as $row ) {
				if ( is_array( $row ) ) {
					foreach ( $row as $section ) {
						if ( is_array( $section ) ) {
							foreach ( $section as $item ) {
								if ( is_array( $item ) && isset( $item['id'] ) && $item['id'] === 'wish-list' ) {
									$wishlist_settings = $item;
									break 3;
								}
							}
						}
					}
				}
			}
		}

		return $wishlist_settings;
	}

	/**
	 * Get default wishlist icon based on type.
	 *
	 * @param string $type Icon type.
	 * @return string SVG icon.
	 */
	private function get_default_wishlist_icon( $type = 'type-1' ) {
		$icons = array(
			'type-1' => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" stroke="currentColor" stroke-width="2" fill="none"/></svg>',
			'type-2' => '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
			'type-3' => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity="0.2"/></svg>',
		);

		return isset( $icons[ $type ] ) ? $icons[ $type ] : $icons['type-1'];
	}
}

/**
 * Recommendations handler for wishlist off-canvas.
 *
 * @since 1.0.0
 */
class BlocksyChildWishlistRecommendations {

	/**
	 * Get recommendations section HTML.
	 *
	 * @param array $args Optional arguments.
	 * @return string HTML content.
	 */
	public function get_recommendations_section( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'include_guest_notice' => true,
		) );

		// Check if recommendations are enabled
		$show_recommendations = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_show_recommendations', 'yes' ) === 'yes';
		if ( ! $show_recommendations ) {
			return '';
		}

		$recommended_products = $this->get_recommended_products();
		if ( empty( $recommended_products ) ) {
			return '';
		}

		$wishlist_is_empty = BlocksyChildWishlistHelper::is_wishlist_empty();
		$title             = $wishlist_is_empty
			? esc_html__( 'Recently Viewed Items', 'blocksy-companion' )
			: esc_html__( 'You May Also Like', 'blocksy-companion' );

		$html = '';

		// Show guest notice above recommendations for logged-out users when requested
		if ( $args['include_guest_notice'] && ! is_user_logged_in() ) {
			$renderer = new BlocksyChildWishlistRenderer();
			$html .= $renderer->get_guest_notice_html();
		}

		$html .= '<div class="wishlist-recommendations">
			<h3 class="recommendations-title">' . $title . '</h3>
			<div class="recommendations-grid" data-columns="2">';

		foreach ( $recommended_products as $product ) {
			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}

			$html .= $this->render_recommendation_item( $product );
		}

		$html .= '</div></div>';
		return $html;
	}

	/**
	 * Get recommended products based on wishlist items.
	 *
	 * @return array Product objects.
	 */
	private function get_recommended_products() {
		$wishlist     = BlocksyChildWishlistHelper::get_current_wishlist();
		$wishlist_ids = BlocksyChildWishlistHelper::extract_product_ids( $wishlist );

		// If wishlist is empty, return recently viewed products
		if ( empty( $wishlist_ids ) ) {
			return $this->get_recently_viewed_products( 2 );
		}

		$recommended_ids = array();

		// Get cross-sells and upsells from wishlist items
		foreach ( $wishlist_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				continue;
			}

			// Get cross-sells and upsells
			$cross_sells = $product->get_cross_sell_ids();
			$upsells     = $product->get_upsell_ids();

			$recommended_ids = array_merge( $recommended_ids, $cross_sells, $upsells );
		}

		// Remove duplicates and products already in wishlist
		$recommended_ids = array_diff( array_unique( $recommended_ids ), $wishlist_ids );

		// Limit to 2 items if we have recommendations
		if ( ! empty( $recommended_ids ) ) {
			$recommended_ids = array_slice( $recommended_ids, 0, 2 );
		} else {
			// If no cross-sells/upsells, get random products
			$recommended_ids = $this->get_random_products( 2, $wishlist_ids );
		}

		// Convert IDs to product objects
		$products = array();
		foreach ( $recommended_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_visible() ) {
				$products[] = $product;
			}
		}

		return $products;
	}

	/**
	 * Get recently viewed products.
	 *
	 * @param int $limit Number of products to return.
	 * @return array Product objects.
	 */
	private function get_recently_viewed_products( $limit = 2 ) {
		$recently_viewed_ids = array();

		if ( ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ) {
			$recently_viewed_ids = wp_parse_id_list(
				(array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) )
			);

			// Reverse to get most recent first and limit results
			$recently_viewed_ids = array_slice( array_reverse( $recently_viewed_ids ), 0, $limit );
		}

		// Convert IDs to product objects
		$products = array();
		foreach ( $recently_viewed_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_visible() ) {
				$products[] = $product;
			}
		}

		return $products;
	}

	/**
	 * Get random products excluding wishlist items.
	 *
	 * @param int   $limit Number of products to return.
	 * @param array $exclude_ids Product IDs to exclude.
	 * @return array Product IDs.
	 */
	private function get_random_products( $limit = 2, $exclude_ids = array() ) {
		$args = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => $limit,
			'orderby' => 'rand',
			'meta_query' => array(
				array(
					'key' => '_stock_status',
					'value' => 'instock',
					'compare' => '=',
				),
			),
			'tax_query' => array(
				array(
					'taxonomy' => 'product_visibility',
					'field' => 'name',
					'terms' => array( 'exclude-from-catalog', 'exclude-from-search' ),
					'operator' => 'NOT IN',
				),
			),
		);

		if ( ! empty( $exclude_ids ) ) {
			$args['post__not_in'] = $exclude_ids;
		}

		$query       = new WP_Query( $args );
		$product_ids = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$product_ids[] = get_the_ID();
			}
			wp_reset_postdata();
		}

		return $product_ids;
	}

	/**
	 * Force track product views for recently viewed functionality.
	 */
	public function force_track_product_view() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}

		global $post;

		$viewed_products = empty( $_COOKIE['woocommerce_recently_viewed'] )
			? array()
			: wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) );

		// Remove current product if already in list
		$keys = array_flip( $viewed_products );
		if ( isset( $keys[ $post->ID ] ) ) {
			unset( $viewed_products[ $keys[ $post->ID ] ] );
		}

		$viewed_products[] = $post->ID;

		// Keep only last 15 products
		if ( count( $viewed_products ) > 15 ) {
			array_shift( $viewed_products );
		}

		wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ) );
	}

	/**
	 * Render a single recommendation item.
	 *
	 * @param WC_Product $product Product object.
	 * @return string HTML content.
	 */
	private function render_recommendation_item( $product ) {
		$product_id       = $product->get_id();
		$show_image       = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_recommendations_show_image', 'yes' ) === 'yes';
		$show_price       = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_recommendations_show_price', 'yes' ) === 'yes';
		$show_add_to_cart = BlocksyChildWishlistHelper::get_theme_mod( 'wishlist_recommendations_show_add_to_cart', 'yes' ) === 'yes';

		$html = '<div class="recommendation-item" data-product-id="' . esc_attr( $product_id ) . '">';

		// Product image
		if ( $show_image ) {
			$html .= '<div class="recommendation-item-image">
				<a href="' . esc_url( $product->get_permalink() ) . '">
					' . $product->get_image( 'woocommerce_thumbnail' ) . '
				</a>
			</div>';
		}

		// Product details
		$html .= '<div class="recommendation-item-details">
			<h4 class="recommendation-item-title">
				<a href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $product->get_name() ) . '</a>
			</h4>';

		// Price
		if ( $show_price ) {
			$html .= '<div class="recommendation-item-price">' . $product->get_price_html() . '</div>';
		}

		// Add to cart button
		if ( $show_add_to_cart && $product->is_purchasable() && $product->is_in_stock() ) {
			$html .= '<div class="recommendation-item-actions">
				<a href="' . esc_url( $product->add_to_cart_url() ) . '"
				   class="button add_to_cart_button"
				   data-product_id="' . esc_attr( $product_id ) . '"
				   data-product_sku="' . esc_attr( $product->get_sku() ) . '"
				   aria-label="' . esc_attr( sprintf( __( 'Add "%s" to your cart', 'woocommerce' ), $product->get_name() ) ) . '"
				   rel="nofollow">
					' . esc_html( $product->add_to_cart_text() ) . '
				</a>
			</div>';
		}

		$html .= '</div></div>';
		return $html;
	}
}

// Initialize the off-canvas wishlist functionality
new BlocksyChildWishlistOffCanvas();
