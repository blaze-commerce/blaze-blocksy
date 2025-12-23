<?php
/**
 * Blocksy Results Count Placement Customization
 *
 * Adds a "Placement" dropdown to Customizer > WooCommerce > Product Archives > Results Count
 * that allows repositioning the results count after the sort dropdown.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Results_Count_Placement
 *
 * Handles the results count placement customization for WooCommerce product archives.
 */
class Results_Count_Placement {

	/**
	 * Available placement options.
	 *
	 * @var array
	 */
	private $placement_options;

	/**
	 * Default placement value.
	 *
	 * @var string
	 */
	private $default_placement = 'default';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->placement_options = array(
			'default'             => __( 'Default', 'blaze-commerce' ),
			'after_sort_dropdown' => __( 'After Sort Dropdown', 'blaze-commerce' ),
		);

		// Add customizer option to Blocksy's Results Count panel
		add_filter( 'blocksy:options:retrieve', array( $this, 'add_placement_option' ), 10, 2 );

		// Handle placement repositioning on frontend
		add_action( 'wp', array( $this, 'handle_placement' ), 10 );

		// Enqueue custom styles when placement is after sort dropdown
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_placement_styles' ) );
	}

	/**
	 * Add placement option to Blocksy's Results Count customizer panel.
	 *
	 * Injects a "Placement" dropdown into the General tab of the Results Count options,
	 * positioned after the visibility option.
	 *
	 * @param array  $options The current options array.
	 * @param string $path    The path to the options file being loaded.
	 * @return array Modified options array.
	 */
	public function add_placement_option( $options, $path ) {
		// Only modify the results-count options file
		if ( strpos( $path, 'woocommerce/results-count.php' ) === false ) {
			return $options;
		}

		// Check if main option exists and has inner-options
		if ( ! isset( $options['has_shop_results_count']['inner-options'] ) ) {
			return $options;
		}

		// Define the new placement option
		$placement_option = array(
			'blz_results_count_placement' => array(
				'label'   => __( 'Placement', 'blaze-commerce' ),
				'type'    => 'ct-select',
				'value'   => $this->default_placement,
				'view'    => 'text',
				'design'  => 'inline',
				'divider' => 'top',
				'setting' => array( 'transport' => 'postMessage' ),
				'choices' => blocksy_ordered_keys( $this->placement_options ),
				'sync'    => blocksy_sync_whole_page( array(
					'prefix'          => 'woo_categories',
					'loader_selector' => '.woo-listing-top',
				) ),
			),
		);

		// Find the General tab and inject the placement option after visibility
		$inner_options = $options['has_shop_results_count']['inner-options'];

		foreach ( $inner_options as $key => $value ) {
			if ( is_array( $value ) && isset( $value['title'] ) && $value['title'] === __( 'General', 'blocksy' ) ) {
				// Found the General tab - inject after visibility option
				if ( isset( $value['options'] ) ) {
					$new_options = array();

					foreach ( $value['options'] as $opt_key => $opt_value ) {
						$new_options[ $opt_key ] = $opt_value;

						// Insert placement option right after visibility
						if ( $opt_key === 'shop_results_count_visibility' ) {
							$new_options = array_merge( $new_options, $placement_option );
						}
					}

					$options['has_shop_results_count']['inner-options'][ $key ]['options'] = $new_options;
				}
				break;
			}
		}

		return $options;
	}

	/**
	 * Get the saved placement value.
	 *
	 * @return string The placement value ('default' or 'after_sort_dropdown').
	 */
	public function get_placement() {
		$placement = get_theme_mod( 'blz_results_count_placement', $this->default_placement );
		return sanitize_key( $placement );
	}

	/**
	 * Handle the repositioning of the results count based on customizer setting.
	 *
	 * Blocksy hook priorities on woocommerce_before_shop_loop:
	 * - Priority 12: Opens .woo-listing-top wrapper
	 * - Priority 20: woocommerce_result_count() - Result count
	 * - Priority 30: woocommerce_catalog_ordering() - Sort dropdown
	 * - Priority 31: Closes .woo-listing-top wrapper
	 *
	 * Strategy for "after_sort_dropdown":
	 * - Remove default result count from priority 20
	 * - Add custom result count output at priority 32 (after wrapper closes)
	 *
	 * @return void
	 */
	public function handle_placement() {
		// Only run on WooCommerce shop/archive pages
		if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
			return;
		}

		// Check if results count is enabled in Blocksy
		if ( function_exists( 'blocksy_get_theme_mod' ) ) {
			if ( blocksy_get_theme_mod( 'has_shop_results_count', 'yes' ) !== 'yes' ) {
				return;
			}
		}

		$placement = $this->get_placement();

		if ( 'after_sort_dropdown' === $placement ) {
			// Remove the default result count output
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

			// Add result count after .woo-listing-top wrapper closes (priority 32 > 31)
			add_action( 'woocommerce_before_shop_loop', array( $this, 'output_relocated_results_count' ), 32 );
		}
	}

	/**
	 * Output the results count after the .woo-listing-top wrapper.
	 *
	 * Uses WooCommerce's native result count function wrapped in a custom container
	 * that inherits Blocksy's styling via the woo-listing-top class.
	 *
	 * @return void
	 */
	public function output_relocated_results_count() {
		// Get visibility classes from Blocksy settings
		$visibility_classes = '';
		if ( function_exists( 'blocksy_visibility_classes' ) && function_exists( 'blocksy_get_theme_mod' ) ) {
			$visibility = blocksy_get_theme_mod( 'shop_results_count_visibility', array(
				'desktop' => true,
				'tablet'  => true,
				'mobile'  => false,
			) );
			$visibility_classes = blocksy_visibility_classes( $visibility );
		}

		// Build the wrapper with appropriate classes
		// Use 'woo-listing-top' class to inherit Blocksy's CSS variable styles
		$wrapper_class = 'blz-results-count-wrapper woo-listing-top';
		if ( ! empty( $visibility_classes ) ) {
			$wrapper_class .= ' ' . esc_attr( trim( $visibility_classes ) );
		}

		echo '<div class="' . esc_attr( $wrapper_class ) . '">';

		// Use WooCommerce's native result count output
		woocommerce_result_count();

		echo '</div>';
	}

	/**
	 * Enqueue styles for the relocated results count.
	 *
	 * Only enqueues on shop/archive pages when placement is set to after_sort_dropdown.
	 *
	 * @return void
	 */
	public function enqueue_placement_styles() {
		// Only enqueue on shop/archive pages when placement is after_sort_dropdown
		if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
			return;
		}

		if ( 'after_sort_dropdown' !== $this->get_placement() ) {
			return;
		}

		wp_enqueue_style(
			'blz-results-count-placement',
			get_template_directory_uri() . '/assets/css/results-count-placement.css',
			array(),
			filemtime( get_template_directory() . '/assets/css/results-count-placement.css' )
		);
	}

	/**
	 * Check if placement is set to after sort dropdown.
	 *
	 * Static helper method for external use.
	 *
	 * @return bool True if placement is after_sort_dropdown, false otherwise.
	 */
	public static function is_after_sort_dropdown() {
		return 'after_sort_dropdown' === get_theme_mod( 'blz_results_count_placement', 'default' );
	}
}

// Initialize the class
new Results_Count_Placement();

/**
 * Helper function to get the results count placement.
 *
 * Provides a simple function-based API for retrieving the placement setting.
 *
 * @return string The placement value ('default' or 'after_sort_dropdown').
 */
if ( ! function_exists( 'blz_get_results_count_placement' ) ) {
	function blz_get_results_count_placement() {
		$placement = get_theme_mod( 'blz_results_count_placement', 'default' );
		return sanitize_key( $placement );
	}
}

/**
 * Helper function to check if results count is after sort dropdown.
 *
 * @return bool True if placement is after_sort_dropdown, false otherwise.
 */
if ( ! function_exists( 'blz_is_results_count_after_sort' ) ) {
	function blz_is_results_count_after_sort() {
		return Results_Count_Placement::is_after_sort_dropdown();
	}
}
