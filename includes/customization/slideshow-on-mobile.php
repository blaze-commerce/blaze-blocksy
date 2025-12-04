<?php
/**
 * Slideshow on Mobile for Stacked Gallery
 *
 * Adds class to gallery container and renders slideshow for mobile
 * when stacked gallery is selected and "Use Slideshow on Mobile" option is enabled.
 * CSS is used to toggle visibility based on viewport width.
 *
 * @package Blaze_Blocksy
 * @since 1.44.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Blaze_Blocksy_Slideshow_On_Mobile
 *
 * Handles the slideshow on mobile functionality for stacked gallery.
 */
class Blaze_Blocksy_Slideshow_On_Mobile {

	/**
	 * Instance of this class.
	 *
	 * @var Blaze_Blocksy_Slideshow_On_Mobile
	 */
	private static $instance = null;

	/**
	 * Get single instance of this class.
	 *
	 * @return Blaze_Blocksy_Slideshow_On_Mobile
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Add customizer option
		add_filter( 'blocksy:options:single_product:gallery-options', array( $this, 'add_customizer_option' ), 20 );

		// Add class to gallery container
		add_filter( 'blocksy:woocommerce:product-view:attr', array( $this, 'add_container_class' ) );

		// Add slideshow after stacked gallery content
		add_filter( 'blocksy:woocommerce:product-view:content', array( $this, 'append_mobile_slideshow' ), 9999, 4 );

		// Enqueue styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Add "Use Slideshow on Mobile" option to customizer.
	 *
	 * @param array $options Existing options.
	 * @return array Modified options.
	 */
	public function add_customizer_option( $options ) {
		$options[blocksy_rand_md5()] = array(
			'type' => 'ct-condition',
			'condition' => array( 'product_view_type' => 'stacked-gallery' ),
			'options' => array(
				'stacked_gallery_mobile_slideshow' => array(
					'label' => __( 'Use Slideshow on Mobile', 'blaze-blocksy' ),
					'type' => 'ct-switch',
					'value' => 'no',
					'divider' => 'top',
					'sync' => blocksy_sync_whole_page(
						array(
							'prefix' => 'product',
							'loader_selector' => '.woocommerce-product-gallery',
						)
					),
				),
			),
		);

		return $options;
	}

	/**
	 * Check if mobile slideshow is enabled.
	 *
	 * @return bool
	 */
	public function is_mobile_slideshow_enabled() {
		$product_view_type = blocksy_get_theme_mod( 'product_view_type', 'default-gallery' );
		$mobile_slideshow = blocksy_get_theme_mod( 'stacked_gallery_mobile_slideshow', 'no' );

		return 'stacked-gallery' === $product_view_type && 'yes' === $mobile_slideshow;
	}

	/**
	 * Add class to gallery container when mobile slideshow is enabled.
	 *
	 * @param array $attr Container attributes.
	 * @return array Modified attributes.
	 */
	public function add_container_class( $attr ) {
		if ( ! $this->is_mobile_slideshow_enabled() ) {
			return $attr;
		}

		$attr['class'] .= ' ct-stacked-with-mobile-slideshow';

		return $attr;
	}

	/**
	 * Append mobile slideshow after stacked gallery content.
	 *
	 * @param mixed      $content        Existing content (stacked gallery from Blocksy Companion Pro).
	 * @param WC_Product $product        Product object.
	 * @param array      $gallery_images Gallery image IDs.
	 * @param bool       $is_single      Whether on single product page.
	 * @return mixed Modified content.
	 */
	public function append_mobile_slideshow( $content, $product, $gallery_images, $is_single ) {
		// Only apply when mobile slideshow is enabled
		if ( ! $this->is_mobile_slideshow_enabled() ) {
			return $content;
		}

		// Only proceed if we have stacked gallery content
		if ( empty( $content ) ) {
			return $content;
		}

		if ( ! $product ) {
			global $product;
		}

		if ( ! $product || count( $gallery_images ) < 2 ) {
			return $content;
		}

		// Render slideshow gallery for mobile
		$slideshow_html = $this->render_slideshow_gallery( $gallery_images, $is_single );

		// Wrap stacked in desktop-only and slideshow in mobile-only
		$output = $content;
		$output .= $slideshow_html;

		return $output;
	}

	/**
	 * Render slideshow gallery using blocksy_flexy.
	 *
	 * @param array $gallery_images Gallery image IDs.
	 * @param bool  $is_single      Whether on single product page.
	 * @return string HTML content.
	 */
	private function render_slideshow_gallery( $gallery_images, $is_single ) {
		$single_ratio = blocksy_get_theme_mod( 'product_gallery_ratio', '3/4' );
		$default_ratio = apply_filters( 'blocksy:woocommerce:default_product_ratio', '3/4' );
		$has_lazy_load = blocksy_get_theme_mod( 'has_lazy_load_single_product_image', 'yes' ) === 'yes';

		$flexy_args = array(
			'images' => $gallery_images,
			'size' => 'woocommerce_single',
			'pills_images' => $is_single ? $gallery_images : null,
			'images_ratio' => $is_single ? $single_ratio : $default_ratio,
			'lazyload' => $has_lazy_load,
			'active_index' => 1,
		);

		return blocksy_flexy( $flexy_args );
	}

	/**
	 * Enqueue styles for mobile slideshow.
	 */
	public function enqueue_styles() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		if ( ! $this->is_mobile_slideshow_enabled() ) {
			return;
		}

		wp_enqueue_style(
			'blaze-blocksy-slideshow-on-mobile',
			BLAZE_BLOCKSY_URL . '/assets/css/slideshow-on-mobile.css',
			array(),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/css/slideshow-on-mobile.css' )
		);
	}
}

// Initialize
Blaze_Blocksy_Slideshow_On_Mobile::get_instance();
