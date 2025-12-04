<?php
/**
 * Slideshow on Mobile for Stacked Gallery
 *
 * Adds class to gallery container and renders OwlCarousel slideshow for mobile
 * when stacked gallery is selected and "Use Slideshow on Mobile" option is enabled.
 * Uses Fancybox for lightbox/zoom functionality.
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

		// Enqueue styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
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
	 * Render slideshow gallery using OwlCarousel + Fancybox.
	 *
	 * @param array $gallery_images Gallery image IDs.
	 * @param bool  $is_single      Whether on single product page.
	 * @return string HTML content.
	 */
	private function render_slideshow_gallery( $gallery_images, $is_single ) {
		$output = '<div class="blaze-gallery-container">';

		// Main slider
		$output .= '<div class="blaze-gallery-main owl-carousel owl-theme">';
		foreach ( $gallery_images as $index => $image_id ) {
			$full_src = wp_get_attachment_image_url( $image_id, 'full' );
			$large_src = wp_get_attachment_image_url( $image_id, 'woocommerce_single' );
			$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			$title = get_the_title( $image_id );

			$output .= '<div class="blaze-slide-item">';
			$output .= '<a href="' . esc_url( $full_src ) . '" data-fancybox="blaze-gallery" data-caption="' . esc_attr( $title ) . '">';
			$output .= '<img src="' . esc_url( $large_src ) . '" alt="' . esc_attr( $alt ) . '" />';
			$output .= '</a>';
			$output .= '<button type="button" class="blaze-zoom-trigger" aria-label="' . esc_attr__( 'Zoom', 'blaze-blocksy' ) . '">';
			$output .= '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg>';
			$output .= '</button>';
			$output .= '</div>';
		}
		$output .= '</div>';

		// Thumbnails
		$output .= '<div class="blaze-gallery-thumbs owl-carousel owl-theme">';
		foreach ( $gallery_images as $index => $image_id ) {
			$thumb_src = wp_get_attachment_image_url( $image_id, 'woocommerce_gallery_thumbnail' );
			$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			$active = ( 0 === $index ) ? ' active' : '';

			$output .= '<div class="blaze-thumb-item' . $active . '" data-index="' . $index . '">';
			$output .= '<img src="' . esc_url( $thumb_src ) . '" alt="' . esc_attr( $alt ) . '" />';
			$output .= '</div>';
		}
		$output .= '</div>';

		$output .= '</div>';

		return $output;
	}

	/**
	 * Enqueue styles and scripts for mobile slideshow.
	 */
	public function enqueue_assets() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		if ( ! $this->is_mobile_slideshow_enabled() ) {
			return;
		}

		// OwlCarousel CSS
		wp_enqueue_style(
			'owl-carousel',
			BLAZE_BLOCKSY_URL . '/assets/vendor/owlcarousel/owl.carousel.min.css',
			array(),
			'2.3.4'
		);
		wp_enqueue_style(
			'owl-carousel-theme',
			BLAZE_BLOCKSY_URL . '/assets/vendor/owlcarousel/owl.theme.default.min.css',
			array( 'owl-carousel' ),
			'2.3.4'
		);

		// Fancybox CSS
		wp_enqueue_style(
			'fancybox',
			BLAZE_BLOCKSY_URL . '/assets/vendor/fancybox/fancybox.css',
			array(),
			'5.0'
		);

		// Custom CSS
		wp_enqueue_style(
			'blaze-mobile-gallery',
			BLAZE_BLOCKSY_URL . '/assets/css/slideshow-on-mobile.css',
			array( 'owl-carousel', 'fancybox' ),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/css/slideshow-on-mobile.css' )
		);

		// OwlCarousel JS
		wp_enqueue_script(
			'owl-carousel',
			BLAZE_BLOCKSY_URL . '/assets/vendor/owlcarousel/owl.carousel.min.js',
			array( 'jquery' ),
			'2.3.4',
			true
		);

		// Fancybox JS
		wp_enqueue_script(
			'fancybox',
			BLAZE_BLOCKSY_URL . '/assets/vendor/fancybox/fancybox.umd.js',
			array(),
			'5.0',
			true
		);

		// Custom JS
		wp_enqueue_script(
			'blaze-mobile-gallery',
			BLAZE_BLOCKSY_URL . '/assets/js/mobile-gallery.js',
			array( 'jquery', 'owl-carousel', 'fancybox' ),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/js/mobile-gallery.js' ),
			true
		);
	}
}

// Initialize
Blaze_Blocksy_Slideshow_On_Mobile::get_instance();
