<?php
/**
 * Load More Counter Customization
 * Updates the "SHOWING X-Y OF Z RESULTS" counter when Load More button is clicked
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle Load More Counter functionality
 */
class Blaze_Blocksy_Load_More_Counter {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Only load on shop/category pages
		add_action( 'template_redirect', array( $this, 'maybe_init_counter' ) );
	}

	/**
	 * Check if we should initialize the counter on current page
	 */
	public function maybe_init_counter() {
		if ( ! $this->should_load_counter() ) {
			return;
		}

		// Add inline styles for better UX
		add_action( 'wp_head', array( $this, 'add_inline_styles' ) );
		
		// Add debug info if WP_DEBUG is enabled
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			add_action( 'wp_footer', array( $this, 'add_debug_info' ) );
		}
	}

	/**
	 * Check if counter should be loaded on current page
	 */
	private function should_load_counter() {
		// Only load on WooCommerce shop/category/tag pages
		if ( ! function_exists( 'is_shop' ) ) {
			return false;
		}

		return is_shop() || is_product_category() || is_product_tag();
	}

	/**
	 * Add inline styles for smooth transitions
	 */
	public function add_inline_styles() {
		?>
		<style>
		.woocommerce-result-count {
			transition: opacity 0.3s ease-in-out;
		}
		
		.woocommerce-result-count.updating {
			opacity: 0.7;
		}
		
		/* Ensure counter is visible and properly styled */
		.woocommerce-result-count {
			display: block !important;
			visibility: visible !important;
		}
		</style>
		<?php
	}

	/**
	 * Add debug information to footer
	 */
	public function add_debug_info() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<script>
		console.log('[BlazeBlocksy LoadMore Counter] Debug Info:');
		console.log('- Page Type:', '<?php echo $this->get_page_type(); ?>');
		console.log('- WooCommerce Active:', <?php echo function_exists( 'WC' ) ? 'true' : 'false'; ?>);
		console.log('- Result Count Element:', document.querySelector('.woocommerce-result-count') ? 'Found' : 'Not Found');
		console.log('- Products Container:', document.querySelector('.products') ? 'Found' : 'Not Found');
		console.log('- Load More Button:', document.querySelector('.ct-load-more') ? 'Found' : 'Not Found');
		console.log('- Blocksy Events:', typeof window.ctEvents !== 'undefined' ? 'Available' : 'Not Available');
		</script>
		<?php
	}

	/**
	 * Get current page type for debugging
	 */
	private function get_page_type() {
		if ( is_shop() ) {
			return 'shop';
		} elseif ( is_product_category() ) {
			return 'product_category';
		} elseif ( is_product_tag() ) {
			return 'product_tag';
		}
		return 'unknown';
	}

	/**
	 * Get total product count for current query
	 * This can be used for server-side validation if needed
	 */
	public function get_total_product_count() {
		global $wp_query;
		
		if ( isset( $wp_query->found_posts ) ) {
			return $wp_query->found_posts;
		}
		
		return 0;
	}

	/**
	 * Get current products per page setting
	 */
	public function get_products_per_page() {
		return wc_get_default_products_per_row() * wc_get_default_product_rows_per_page();
	}

	/**
	 * Check if pagination is set to load more type
	 */
	public function is_load_more_pagination() {
		// This would depend on Blocksy theme settings
		// For now, we assume it's load more if the button exists
		return true;
	}
}

/**
 * Initialize the Load More Counter
 */
function blaze_blocksy_init_load_more_counter() {
	new Blaze_Blocksy_Load_More_Counter();
}

// Initialize on plugins loaded to ensure WooCommerce is available
add_action( 'plugins_loaded', 'blaze_blocksy_init_load_more_counter', 20 );

/**
 * Helper function to check if load more counter is active
 */
function blaze_blocksy_is_load_more_counter_active() {
	return class_exists( 'Blaze_Blocksy_Load_More_Counter' );
}
