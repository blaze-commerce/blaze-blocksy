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
	}

	/**
	 * Add off-canvas setting to wishlist customizer options
	 */
	public function add_offcanvas_setting( $opts ) {
		// Debug: Log that the filter is being called
		error_log( 'Wishlist off-canvas filter called' );

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

				'wishlist_offcanvas_width' => array(
					'label' => __( 'Canvas Width', 'blocksy-companion' ),
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
					'value' => '1',
					'view' => 'text',
					'design' => 'block',
					'choices' => array(
						'1' => __( '1 Column', 'blocksy-companion' ),
						'2' => __( '2 Columns', 'blocksy-companion' ),
					),
					'desc' => __( 'Choose how many columns to display products in the off-canvas.', 'blocksy-companion' ),
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

			),
		);

		return $opts;
	}

	/**
	 * Add custom CSS for canvas width setting
	 */
	public function add_canvas_width_css() {
		$display_mode = get_theme_mod( 'wishlist_display_mode', 'page' );

		// Only add CSS if off-canvas mode is enabled
		if ( $display_mode !== 'offcanvas' ) {
			return;
		}

		// Get the canvas width setting
		$canvas_width = get_theme_mod( 'wishlist_offcanvas_width', array(
			'desktop' => '400px',
			'tablet' => '350px',
			'mobile' => '300px',
		) );

		?>
		<style id="wishlist-offcanvas-width-css">
			/* Desktop */
			#wishlist-offcanvas-panel[data-behaviour*="side"] {
				width:
					<?php echo esc_attr( $canvas_width['desktop'] ); ?>
				;
			}

			/* Tablet */
			@media (max-width: 999px) {
				#wishlist-offcanvas-panel[data-behaviour*="side"] {
					width:
						<?php echo esc_attr( $canvas_width['tablet'] ); ?>
					;
				}
			}

			/* Mobile */
			@media (max-width: 689px) {
				#wishlist-offcanvas-panel[data-behaviour*="side"] {
					width:
						<?php echo esc_attr( $canvas_width['mobile'] ); ?>
					;
				}
			}
		</style>
		<?php
	}
}

// Initialize the off-canvas wishlist functionality
new BlocksyChildWishlistOffCanvas();
