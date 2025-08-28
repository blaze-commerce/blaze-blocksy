<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Off-Canvas Wishlist Functionality for Blocksy Child Theme
 *
 * This file adds off-canvas wishlist functionality without modifying plugin files.
 * It uses WordPress hooks and filters to extend the existing wishlist behavior.
 */

class BlocksyChildWishlistOffCanvas {

	public function __construct() {
		// Only initialize if the WooCommerce Extra extension is active
		if ( ! $this->is_woocommerce_extra_active() ) {
			return;
		}

		$this->init_hooks();
	}

	/**
	 * Check if Blocksy WooCommerce Extra extension is active
	 */
	private function is_woocommerce_extra_active() {
		return class_exists( '\Blocksy\Extensions\WoocommerceExtra\WishList' );
	}



	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Add off-canvas setting to wishlist options
		add_filter( 'blocksy_customizer_options:woocommerce:general:end', array( $this, 'add_offcanvas_setting' ), 60 );

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
		add_action( 'template_redirect', array( $this, 'force_track_product_view' ), 25 );
	}

	/**
	 * Add off-canvas setting to wishlist customizer options
	 */
	public function add_offcanvas_setting( $opts ) {
		// First, add the display mode setting to the existing wishlist panel
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

		// Add a separate panel for off-canvas specific settings
		$opts['wishlist_offcanvas_panel'] = array(
			'label' => __( 'Wishlist Off-Canvas Settings', 'blocksy-companion' ),
			'type' => 'ct-panel',
			'setting' => array( 'transport' => 'postMessage' ),
			'inner-options' => array(

				'wishlist_offcanvas_width' => array(
					'label' => __( 'Off-Canvas Width', 'blocksy-companion' ),
					'type' => 'ct-slider',
					'value' => array(
						'desktop' => '400px',
						'tablet' => '350px',
						'mobile' => '300px',
					),
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
					'value' => '2',
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
					'value' => 'right-side',
					'view' => 'text',
					'design' => 'block',
					'choices' => array(
						'left-side' => __( 'Left Side', 'blocksy-companion' ),
						'right-side' => __( 'Right Side', 'blocksy-companion' ),
					),
					'desc' => __( 'Choose which side the off-canvas panel slides in from.', 'blocksy-companion' ),
				),

				// Header Icon Settings Section
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
					'options' => array(

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
							'options' => array(
								'wishlist_offcanvas_icon_type' => array(
									'label' => __( 'Icon Style', 'blocksy-companion' ),
									'type' => 'ct-image-picker',
									'value' => 'type-1',
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
							),
						),

						blocksy_rand_md5() => array(
							'type' => 'ct-condition',
							'condition' => array( 'wishlist_offcanvas_icon_type_source' => 'custom' ),
							'options' => array(
								'wishlist_offcanvas_custom_icon' => array(
									'type' => 'icon-picker',
									'label' => __( 'Custom Icon', 'blocksy-companion' ),
									'design' => 'inline',
									'value' => array(
										'icon' => 'blc blc-heart'
									),
									'desc' => __( 'Select a custom icon for the wishlist header item.', 'blocksy-companion' ),
								),
							),
						),

						'wishlist_offcanvas_icon_size' => array(
							'label' => __( 'Icon Size', 'blocksy-companion' ),
							'type' => 'text',
							'value' => '18',
							'attr' => array(
								'placeholder' => '18'
							),
							'setting' => array( 'transport' => 'postMessage' ),
							'desc' => __( 'Set the size of the wishlist icon in pixels (e.g., 18).', 'blocksy-companion' ),
						),

						'wishlist_offcanvas_close_icon_size' => array(
							'label' => __( 'Close Icon Size', 'blocksy-companion' ),
							'type' => 'text',
							'value' => '32',
							'attr' => array(
								'placeholder' => '32'
							),
							'setting' => array( 'transport' => 'postMessage' ),
							'desc' => __( 'Set the size of the close icon in pixels (e.g., 32).', 'blocksy-companion' ),
						),

					),
				),

				// Empty State Settings Section
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

				// Product Display Settings Section
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

				// Recommendations Settings Section
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

			),
		);

		return $opts;
	}

	/**
	 * Add custom CSS for canvas width and icon settings
	 */
	public function add_canvas_width_css() {
		$display_mode = function_exists( 'blocksy_get_theme_mod' )
			? blocksy_get_theme_mod( 'wishlist_display_mode', 'page' )
			: get_theme_mod( 'wishlist_display_mode', 'page' );

		// Only add CSS if off-canvas mode is enabled
		if ( $display_mode !== 'offcanvas' ) {
			return;
		}

		$get_mod = function_exists( 'blocksy_get_theme_mod' ) ? 'blocksy_get_theme_mod' : 'get_theme_mod';

		// Get the canvas width setting
		$canvas_width_raw = $get_mod( 'wishlist_offcanvas_width', null );
		$canvas_width     = $get_mod( 'wishlist_offcanvas_width', array(
			'desktop' => '400px',
			'tablet' => '350px',
			'mobile' => '300px',
		) );

		// Handle both string and array formats from Customizer
		if ( ! is_array( $canvas_width ) ) {
			// If it's a string (like "445px"), use it for desktop and set defaults for others
			if ( is_string( $canvas_width ) && ! empty( $canvas_width ) ) {
				$desktop_value = $canvas_width;
			} else {
				$desktop_value = '400px';
			}
			$canvas_width = array(
				'desktop' => $desktop_value,
				'tablet' => '350px',
				'mobile' => '300px',
			);
		}

		// Ensure all required keys exist with default values
		$canvas_width = wp_parse_args( $canvas_width, array(
			'desktop' => '400px',
			'tablet' => '350px',
			'mobile' => '300px',
		) );

		// Get icon settings
		$icon_source     = $get_mod( 'wishlist_offcanvas_icon_source', 'header' );
		$icon_size       = array();
		$close_icon_size = '';

		if ( $icon_source === 'custom' ) {
			$icon_size_value = $get_mod( 'wishlist_offcanvas_icon_size', '18' );

			// Ensure we have a valid size value
			if ( empty( $icon_size_value ) ) {
				$icon_size_value = '18';
			}

			// Add 'px' if it's just a number
			$icon_size = $icon_size_value;
			if ( is_numeric( $icon_size_value ) ) {
				$icon_size = $icon_size_value . 'px';
			}
		}

		// Get close icon size (always available)
		$close_icon_size_value = $get_mod( 'wishlist_offcanvas_close_icon_size', '32' );

		// Ensure we have a valid close icon size value
		if ( empty( $close_icon_size_value ) ) {
			$close_icon_size_value = '32';
		}

		// Add 'px' if it's just a number
		$close_icon_size = $close_icon_size_value;
		if ( is_numeric( $close_icon_size_value ) ) {
			$close_icon_size = $close_icon_size_value . 'px';
		}

		?>
		<style id="wishlist-offcanvas-custom-css">
			/* Canvas Width - Desktop */
			#wishlist-offcanvas-panel[data-behaviour*="side"] {
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
				#wishlist-offcanvas-panel[data-behaviour*="side"] {
					width: 100vw;
					max-width:
						<?php echo esc_attr( $canvas_width['tablet'] ); ?>
					;
				}


			}

			/* Mobile */
			@media (max-width: 689px) {
				#wishlist-offcanvas-panel[data-behaviour*="side"] {
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
	 * Add off-canvas panel to footer
	 */
	public function add_offcanvas_to_footer( $elements, $payload ) {
		$display_mode = function_exists( 'blocksy_get_theme_mod' )
			? blocksy_get_theme_mod( 'wishlist_display_mode', 'page' )
			: get_theme_mod( 'wishlist_display_mode', 'page' );

		// Only add off-canvas if mode is enabled and we're in the right location
		if ( $display_mode !== 'offcanvas' || $payload['location'] !== 'start' ) {
			return $elements;
		}

		$elements[] = $this->render_wishlist_offcanvas();

		return $elements;
	}

	/**
	 * Enqueue off-canvas assets when needed
	 */
	public function enqueue_offcanvas_assets() {
		$display_mode = function_exists( 'blocksy_get_theme_mod' )
			? blocksy_get_theme_mod( 'wishlist_display_mode', 'page' )
			: get_theme_mod( 'wishlist_display_mode', 'page' );

		if ( $display_mode !== 'offcanvas' ) {
			return;
		}

		// Enqueue CSS
		wp_enqueue_style(
			'wishlist-offcanvas',
			get_stylesheet_directory_uri() . '/assets/css/wishlist-offcanvas.css',
			array(),
			wp_get_theme()->get( 'Version' )
		);

		// Enqueue JavaScript
		wp_enqueue_script(
			'wishlist-offcanvas',
			get_stylesheet_directory_uri() . '/assets/js/wishlist-offcanvas.js',
			array( 'jquery' ),
			wp_get_theme()->get( 'Version' ),
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
	 * Render the wishlist off-canvas panel HTML
	 */
	public function render_wishlist_offcanvas( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'has_container' => true,
		) );

		// Get wishlist content
		$content = $this->get_wishlist_content();

		$without_container = '<div class="ct-panel-content"><div class="ct-panel-content-inner">' . $content . '</div></div>';

		if ( ! $args['has_container'] ) {
			return $without_container;
		}

		// Get off-canvas settings
		$get_mod    = function_exists( 'blocksy_get_theme_mod' ) ? 'blocksy_get_theme_mod' : 'get_theme_mod';
		$behavior   = $get_mod( 'wishlist_offcanvas_position', 'right-side' );
		$close_icon = '<svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15"><path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/></svg>';

		// Get the wishlist icon for the heading
		$wishlist_icon = $this->get_wishlist_icon();
		$icon_html     = $wishlist_icon ? '<span class="ct-panel-heading-icon">' . $wishlist_icon . '</span> ' : '';

		return '<div id="wishlist-offcanvas-panel" class="ct-panel ct-header ct-header-wishlist" data-behaviour="' . esc_attr( $behavior ) . '" role="dialog" aria-label="' . esc_attr__( 'Wishlist panel', 'blocksy-companion' ) . '" inert="">
			<div class="ct-panel-inner">
				<div class="ct-panel-actions">
					<span class="ct-panel-heading">' . $icon_html . esc_html__( 'Wishlist', 'blocksy-companion' ) . ' <span class="wishlist-count">(' . $this->get_wishlist_count() . ')</span></span>
					<button class="ct-toggle-close" data-type="type-1" aria-label="' . esc_attr__( 'Close wishlist panel', 'blocksy-companion' ) . '">
						' . $close_icon . '
					</button>
				</div>
				' . $without_container . '
			</div>
		</div>';
	}

	/**
	 * Get wishlist content HTML
	 */
	private function get_wishlist_content() {
		if ( ! function_exists( 'blc_get_ext' ) || ! blc_get_ext( 'woocommerce-extra' ) || ! blc_get_ext( 'woocommerce-extra' )->get_wish_list() ) {
			return '<div class="ct-offcanvas-wishlist"><p>' . esc_html__( 'Wishlist functionality is not available.', 'blocksy-companion' ) . '</p></div>';
		}

		// Get current wishlist
		$wishlist = blc_get_ext( 'woocommerce-extra' )->get_wish_list()->get_current_wish_list();

		if ( empty( $wishlist ) ) {
			return $this->get_empty_wishlist_content();
		}

		return $this->render_wishlist_items( $wishlist );
	}

	/**
	 * Get empty wishlist content
	 */
	private function get_empty_wishlist_content() {
		$get_mod      = function_exists( 'blocksy_get_theme_mod' ) ? 'blocksy_get_theme_mod' : 'get_theme_mod';
		$is_logged_in = is_user_logged_in();

		// Get empty state image setting
		$empty_state_image = $get_mod( 'wishlist_empty_state_image', array( 'attachment_id' => null ) );

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
			// Default cart icon (existing behavior)
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

		// Always show recommendations below the empty state (if enabled)
		// Avoid duplicating the guest notice here since we already showed it above (for logged-out users)
		$html .= $this->get_recommendations_section( array( 'include_guest_notice' => false ) );

		$html .= '</div>';
		return $html;
	}

	/**
	 * Render wishlist items
	 */
	private function render_wishlist_items( $items ) {
		$get_mod = function_exists( 'blocksy_get_theme_mod' ) ? 'blocksy_get_theme_mod' : 'get_theme_mod';

		$columns          = $get_mod( 'wishlist_offcanvas_columns', '2' );
		$show_price       = $get_mod( 'wishlist_show_product_price', 'yes' ) === 'yes';
		$show_image       = $get_mod( 'wishlist_show_product_image', 'yes' ) === 'yes';
		$show_add_to_cart = $get_mod( 'wishlist_show_add_to_cart', 'yes' ) === 'yes';
		$show_remove      = $get_mod( 'wishlist_show_remove_button', 'yes' ) === 'yes';

		$html = '<div class="ct-offcanvas-wishlist" data-columns="' . esc_attr( $columns ) . '">
			<div class="wishlist-items">';

		foreach ( $items as $item ) {
			// Extract product ID using the same logic as Blocksy's table
			$product_id = null;

			if ( isset( $item['id'] ) && is_numeric( $item['id'] ) ) {
				$product_id = $item['id'];
			} elseif ( is_numeric( $item ) ) {
				$product_id = $item;
			}

			if ( ! $product_id ) {
				continue;
			}

			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				continue;
			}

			// Check product status (same as Blocksy table)
			$status = $product->get_status();
			if ( $status === 'trash' ) {
				continue;
			}

			if ( $status === 'private' && ! current_user_can( 'read_private_products' ) ) {
				continue;
			}

			$html .= '<div class="wishlist-item" data-product-id="' . esc_attr( $product_id ) . '">';

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

				$html .= '<div class="wishlist-item-price">' . $price_html . '</div>';
			}

			$html .= '<div class="wishlist-item-actions">';

			if ( $show_add_to_cart && $product->is_purchasable() ) {
				$html .= '<button class="button add_to_cart_button" data-product_id="' . esc_attr( $product_id ) . '">' . esc_html__( 'Add to cart', 'woocommerce' ) . '</button>';
			}

			if ( $show_remove ) {
				$html .= '<button class="ct-wishlist-remove" data-product-id="' . esc_attr( $product_id ) . '">' . esc_html__( 'Remove', 'blocksy-companion' ) . '</button>';
			}

			$html .= '</div></div></div>';
		}

		$html .= '</div>';

		// Add "You May Also Like" section if there are recommendations
		$html .= $this->get_recommendations_section();

		$html .= '</div>';

		return $html;
	}

	/**
	 * Get recommendations section
	 */
	private function get_recommendations_section( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'include_guest_notice' => true,
		) );

		$get_mod = function_exists( 'blocksy_get_theme_mod' ) ? 'blocksy_get_theme_mod' : 'get_theme_mod';

		// Check if recommendations are enabled
		$show_recommendations = $get_mod( 'wishlist_show_recommendations', 'yes' ) === 'yes';
		if ( ! $show_recommendations ) {
			return '';
		}

		$recommended_products = $this->get_recommended_products();

		if ( empty( $recommended_products ) ) {
			return '';
		}

		// Check if wishlist is empty to determine the title
		$wishlist_is_empty = $this->is_wishlist_empty();
		$title             = $wishlist_is_empty
			? esc_html__( 'Recently Viewed Items', 'blocksy-companion' )
			: esc_html__( 'You May Also Like', 'blocksy-companion' );

		$html = '';

		// Show guest notice above recommendations for logged-out users when requested
		if ( $args['include_guest_notice'] && ! is_user_logged_in() ) {
			$html .= $this->get_guest_notice_html();
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
	 * Guest notice HTML shown to logged-out users
	 */
	private function get_guest_notice_html() {
		$signup_url = wp_registration_url();
		$login_url  = wp_login_url();
		$notice     = '<div class="wishlist-guest-notice">'
			. '<p class="notice-text">' . esc_html__( 'Guest favorites are only saved to your device for 7 days, or until you clear your cache. Sign in or create an account to hang on to your picks.', 'blocksy-companion' ) . '</p>'
			. '<div class="notice-actions">'
			. '<a href="' . esc_url( $signup_url ) . '" class="button notice-signup">' . esc_html__( 'Sign Up', 'blocksy-companion' ) . '</a>'
			. '</div>'
			. '</div>';

		return $notice;
	}

	/**
	 * Get recommended products based on wishlist items
	 */
	private function get_recommended_products() {
		$recommended_ids = array();

		// Get current wishlist
		if ( ! function_exists( 'blc_get_ext' ) || ! blc_get_ext( 'woocommerce-extra' ) || ! blc_get_ext( 'woocommerce-extra' )->get_wish_list() ) {
			return array();
		}

		$wishlist = blc_get_ext( 'woocommerce-extra' )->get_wish_list()->get_current_wish_list();

		// Extract product IDs from wishlist data structure
		$wishlist_ids = array();
		if ( ! empty( $wishlist ) ) {
			foreach ( $wishlist as $item ) {
				if ( is_array( $item ) && isset( $item['id'] ) ) {
					$wishlist_ids[] = $item['id'];
				} elseif ( is_numeric( $item ) ) {
					$wishlist_ids[] = $item;
				}
			}
		}

		// If wishlist is empty, return recently viewed products
		if ( empty( $wishlist_ids ) ) {
			return $this->get_recently_viewed_products( 2 );
		}

		// Step 1 & 2: Get cross-sells and upsells from wishlist items
		if ( ! empty( $wishlist_ids ) ) {
			foreach ( $wishlist_ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( ! $product ) {
					continue;
				}

				// Get cross-sells
				$cross_sells = $product->get_cross_sell_ids();
				if ( ! empty( $cross_sells ) ) {
					$recommended_ids = array_merge( $recommended_ids, $cross_sells );
				}

				// Get upsells
				$upsells = $product->get_upsell_ids();
				if ( ! empty( $upsells ) ) {
					$recommended_ids = array_merge( $recommended_ids, $upsells );
				}
			}

			// Remove duplicates and products already in wishlist
			$recommended_ids = array_diff( array_unique( $recommended_ids ), $wishlist_ids );
		}

		// Step 3: Limit to 2 items if we have recommendations
		if ( ! empty( $recommended_ids ) ) {
			$recommended_ids = array_slice( $recommended_ids, 0, 2 );
		} else {
			// Step 4: If no cross-sells/upsells, get random products
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
	 * Get random products excluding wishlist items
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
					'compare' => '='
				)
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
	 * Get recently viewed products
	 */
	private function get_recently_viewed_products( $limit = 2 ) {
		// Get recently viewed product IDs from cookie
		$recently_viewed_ids = array();

		if ( ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ) {
			$recently_viewed_ids = wp_parse_id_list(
				(array) explode(
					'|',
					wp_unslash( $_COOKIE['woocommerce_recently_viewed'] )
				)
			);

			// Reverse to get most recent first
			$recently_viewed_ids = array_reverse( $recently_viewed_ids );

			// Limit the results
			$recently_viewed_ids = array_slice( $recently_viewed_ids, 0, $limit );
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
	 * Check if wishlist is empty
	 */
	private function is_wishlist_empty() {
		// Get current wishlist
		if ( ! function_exists( 'blc_get_ext' ) || ! blc_get_ext( 'woocommerce-extra' ) || ! blc_get_ext( 'woocommerce-extra' )->get_wish_list() ) {
			return true;
		}

		$wishlist = blc_get_ext( 'woocommerce-extra' )->get_wish_list()->get_current_wish_list();

		// Extract product IDs from wishlist data structure
		$wishlist_ids = array();
		if ( ! empty( $wishlist ) ) {
			foreach ( $wishlist as $item ) {
				if ( is_array( $item ) && isset( $item['id'] ) ) {
					$wishlist_ids[] = $item['id'];
				} elseif ( is_numeric( $item ) ) {
					$wishlist_ids[] = $item;
				}
			}
		}

		return empty( $wishlist_ids );
	}

	/**
	 * Force track product views for recently viewed functionality
	 * This bypasses WooCommerce's requirement for the Recently Viewed Products widget to be active
	 */
	public function force_track_product_view() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}

		global $post;

		if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) {
			$viewed_products = array();
		} else {
			$viewed_products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) );
		}

		// Unset if already in viewed products list.
		$keys = array_flip( $viewed_products );

		if ( isset( $keys[ $post->ID ] ) ) {
			unset( $viewed_products[ $keys[ $post->ID ] ] );
		}

		$viewed_products[] = $post->ID;

		if ( count( $viewed_products ) > 15 ) {
			array_shift( $viewed_products );
		}

		// Store for session only.
		wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ) );
	}

	/**
	 * Render a single recommendation item
	 */
	private function render_recommendation_item( $product ) {
		$product_id = $product->get_id();
		$get_mod    = function_exists( 'blocksy_get_theme_mod' ) ? 'blocksy_get_theme_mod' : 'get_theme_mod';

		// Get recommendation-specific settings
		$show_image       = $get_mod( 'wishlist_recommendations_show_image', 'yes' ) === 'yes';
		$show_price       = $get_mod( 'wishlist_recommendations_show_price', 'yes' ) === 'yes';
		$show_add_to_cart = $get_mod( 'wishlist_recommendations_show_add_to_cart', 'yes' ) === 'yes';

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

		// Add to cart button (if product is purchasable and setting is enabled)
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

	/**
	 * Get wishlist count
	 */
	private function get_wishlist_count() {
		if ( ! function_exists( 'blc_get_ext' ) || ! blc_get_ext( 'woocommerce-extra' ) || ! blc_get_ext( 'woocommerce-extra' )->get_wish_list() ) {
			return 0;
		}

		$wishlist = blc_get_ext( 'woocommerce-extra' )->get_wish_list()->get_current_wish_list();

		return ! empty( $wishlist ) ? count( $wishlist ) : 0;
	}

	/**
	 * AJAX handler for loading wishlist content
	 */
	public function ajax_load_wishlist_content() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wishlist_offcanvas_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$content = $this->get_wishlist_content();
		$count   = $this->get_wishlist_count();

		wp_send_json_success( array(
			'content' => $content,
			'count' => $count,
		) );
	}

	/**
	 * Modify wishlist header output when off-canvas is enabled
	 */
	public function modify_wishlist_header_output( $output, $item_id ) {
		if ( $item_id !== 'wish-list' ) {
			return $output;
		}

		$display_mode = function_exists( 'blocksy_get_theme_mod' )
			? blocksy_get_theme_mod( 'wishlist_display_mode', 'page' )
			: get_theme_mod( 'wishlist_display_mode', 'page' );

		if ( $display_mode === 'offcanvas' ) {
			// Always try to get the appropriate icon based on settings
			$new_icon = $this->get_wishlist_icon();

			if ( $new_icon ) {
				// Replace the first inline SVG in the header output with our icon
				$output = preg_replace( '/<svg[^>]*>.*?<\/svg>/s', $new_icon, $output );
			}

			// Use regex to replace any href value with our off-canvas identifier
			$output = preg_replace( '/href="[^"]*"/', 'href="#wishlist-offcanvas"', $output );

			// Add a data attribute to identify this as an off-canvas trigger
			$output = str_replace( 'class="', 'class="ct-offcanvas-trigger ', $output );
		}

		return $output;
	}

	/**
	 * Get header wishlist settings for icon configuration
	 */
	private function get_header_wishlist_settings() {
		// Get the header builder data
		$header_builder = function_exists( 'blocksy_get_theme_mod' )
			? blocksy_get_theme_mod( 'header_placements', array() )
			: get_theme_mod( 'header_placements', array() );

		// Look for wishlist item in header builder
		$wishlist_settings = array();

		if ( is_array( $header_builder ) ) {
			foreach ( $header_builder as $row ) {
				if ( is_array( $row ) ) {
					foreach ( $row as $section ) {
						if ( is_array( $section ) ) {
							foreach ( $section as $item ) {
								if ( is_array( $item ) && isset( $item['id'] ) && $item['id'] === 'wish-list' ) {
									$wishlist_settings = $item;
									break 3; // Break out of all loops
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
	 * Get the appropriate icon based on settings
	 */
	private function get_wishlist_icon() {
		$get_mod = function_exists( 'blocksy_get_theme_mod' ) ? 'blocksy_get_theme_mod' : 'get_theme_mod';

		$icon_source = $get_mod( 'wishlist_offcanvas_icon_source', 'header' );

		if ( $icon_source === 'custom' ) {
			// Get the icon type source (default icons or custom icon)
			$icon_type_source = $get_mod( 'wishlist_offcanvas_icon_type_source', 'default' );

			if ( $icon_type_source === 'default' ) {
				// Use predefined icon types
				$icon_type = $get_mod( 'wishlist_offcanvas_icon_type', 'type-1' );
				return $this->get_default_wishlist_icon( $icon_type );
			} else {
				// Use custom icon picker
				$custom_icon = $get_mod( 'wishlist_offcanvas_custom_icon', array( 'icon' => 'blc blc-heart' ) );

				if ( function_exists( 'blc_get_icon' ) && is_array( $custom_icon ) ) {
					// Simply pass the icon descriptor to blc_get_icon without manipulation
					// The blc_get_icon function handles attachments and library icons automatically
					$result = blc_get_icon( array(
						'icon_descriptor' => $custom_icon,
						'icon_container' => false,
						'icon_html_atts' => array( 'class' => 'ct-icon' ),
					) );
					return $result;
				}
			}
		}

		// If using header settings or custom failed, get from header
		$header_settings = $this->get_header_wishlist_settings();

		if ( ! empty( $header_settings ) ) {
			// Check if header uses custom icon
			$header_icon_source = isset( $header_settings['icon_source'] ) ? $header_settings['icon_source'] : 'default';

			if ( $header_icon_source === 'custom' && function_exists( 'blc_get_icon' ) ) {
				$header_icon = isset( $header_settings['icon'] ) ? $header_settings['icon'] : array( 'icon' => 'blc blc-heart' );

				return blc_get_icon( array(
					'icon_descriptor' => $header_icon,
					'icon_container' => false,
				) );
			} else {
				// Use header's default icon type
				$header_icon_type = isset( $header_settings['wishlist_item_type'] ) ? $header_settings['wishlist_item_type'] : 'type-1';
				return $this->get_default_wishlist_icon( $header_icon_type );
			}
		}

		// Fallback to default heart icon
		return $this->get_default_wishlist_icon( 'type-1' );
	}

	/**
	 * Get default wishlist icon based on type
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

// Initialize the off-canvas wishlist functionality
new BlocksyChildWishlistOffCanvas();
