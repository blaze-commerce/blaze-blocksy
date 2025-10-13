<?php
/**
 * WooCommerce Block Extensions Loader
 *
 * Loads and initializes all WooCommerce block extensions.
 * This file serves as the main entry point for block customizations.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * WooCommerce Block Extensions Loader Class
 */
class WC_Block_Extensions_Loader {

	/**
	 * Singleton instance
	 *
	 * @var WC_Block_Extensions_Loader
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return WC_Block_Extensions_Loader
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the loader
	 */
	private function init() {
		// Check if WooCommerce is active
		if ( ! $this->is_woocommerce_active() ) {
			return;
		}

		// Load extension files
		$this->load_extensions();

		// Initialize hooks
		$this->init_hooks();
	}

	/**
	 * Check if WooCommerce is active
	 *
	 * @return bool True if WooCommerce is active.
	 */
	private function is_woocommerce_active() {
		return function_exists( 'WC' ) && class_exists( 'WooCommerce' );
	}

	/**
	 * Load extension files
	 */
	private function load_extensions() {
		$extensions_dir = dirname( __FILE__ );

		// Product Collection Responsive Extension
		require_once $extensions_dir . '/product-collection-responsive.php';

		// Product Image Enhancement Extension
		require_once $extensions_dir . '/product-image-enhancements.php';

		// Wishlist AJAX Handler
		require_once $extensions_dir . '/wishlist-ajax-handler.php';
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Add admin notices if needed
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Display admin notices
	 */
	public function admin_notices() {
		// Check if WooCommerce Blocks plugin is active
		if ( ! $this->is_woocommerce_blocks_active() ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php esc_html_e( 'WooCommerce Block Extensions:', 'blocksy-child' ); ?></strong>
					<?php esc_html_e( 'WooCommerce Blocks plugin is recommended for full functionality.', 'blocksy-child' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Check if WooCommerce Blocks plugin is active
	 *
	 * @return bool True if WooCommerce Blocks is active.
	 */
	private function is_woocommerce_blocks_active() {
		// WooCommerce Blocks is bundled with WooCommerce 8.0+
		if ( defined( 'WC_BLOCKS_VERSION' ) ) {
			return true;
		}

		// Check if standalone plugin is active
		return class_exists( 'Automattic\WooCommerce\Blocks\Package' );
	}
}

// Initialize the loader
WC_Block_Extensions_Loader::get_instance();

