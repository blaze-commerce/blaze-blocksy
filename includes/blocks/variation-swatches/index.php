<?php
/**
 * Product Variation Swatches Block
 * 
 * Custom WordPress block for displaying variation swatches in WooCommerce Product Collection blocks
 * 
 * @package BlazeBlocksy
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Variation Swatches Block Class
 */
class ProductVariationSwatchesBlock {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Register the block
	 */
	public function register_block() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		register_block_type(
			__DIR__ . '/block.json',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Render the block on the frontend
	 * 
	 * @param array $attributes Block attributes
	 * @param string $content Block content
	 * @param WP_Block $block Block instance
	 * @return string
	 */
	public function render_block( $attributes, $content, $block ) {
		// Get product from block context
		$product_id = $block->context['woocommerce/product-id'] ??
			$block->context['postId'] ?? null;

		if ( ! $product_id ) {
			return '';
		}

		$product = wc_get_product( $product_id );

		// Only show for variable products if onlyVariableProducts is true
		if ( ! $product || ( $attributes['onlyVariableProducts'] && ! $product->is_type( 'variable' ) ) ) {
			return '';
		}

		// If not a variable product, return empty
		if ( ! $product->is_type( 'variable' ) ) {
			return '';
		}

		// Generate swatches HTML
		return $this->generate_swatches_html( $product, $attributes );
	}

	/**
	 * Generate swatches HTML using existing plugin structure
	 *
	 * @param WC_Product $product
	 * @param array $attributes
	 * @return string
	 */
	private function generate_swatches_html( $product, $attributes ) {
		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			return '';
		}

		// Get variation data
		$available_variations = $product->get_available_variations();
		$attributes_data = $product->get_variation_attributes();

		if ( empty( $available_variations ) || empty( $attributes_data ) ) {
			return '';
		}

		// Enhanced plugin dependency safety check
		$use_plugin_structure = $this->is_variation_swatches_plugin_available();

		if ( $use_plugin_structure ) {
			return $this->render_with_plugin_structure( $product, $attributes, $available_variations, $attributes_data );
		} else {
			return $this->render_basic_structure( $product, $attributes, $available_variations, $attributes_data );
		}
	}

	/**
	 * Comprehensive check for Variation Swatches Pro plugin availability
	 *
	 * @return bool
	 */
	private function is_variation_swatches_plugin_available() {
		// Check if main plugin function exists
		if ( ! function_exists( 'woo_variation_swatches' ) ) {
			return false;
		}

		// Check if main plugin class exists
		if ( ! class_exists( 'Woo_Variation_Swatches_Pro_Archive_Page' ) ) {
			return false;
		}

		// Check if required WooCommerce functions exist
		if ( ! function_exists( 'wc_dropdown_variation_attribute_options' ) ) {
			return false;
		}

		// Check if plugin is actually active (not just loaded)
		// Ensure is_plugin_active function is available
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( ! is_plugin_active( 'woo-variation-swatches-pro/woo-variation-swatches-pro.php' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Render using existing plugin structure for consistency
	 *
	 * @param WC_Product $product
	 * @param array $attributes
	 * @param array $available_variations
	 * @param array $attributes_data
	 * @return string
	 */
	private function render_with_plugin_structure( $product, $attributes, $available_variations, $attributes_data ) {
		// Additional safety check before rendering
		if ( ! $this->is_variation_swatches_plugin_available() ) {
			return $this->render_basic_structure( $product, $attributes, $available_variations, $attributes_data );
		}

		$product_id = $product->get_id();
		$variations_json = wp_json_encode( $available_variations );
		$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : esc_attr( $variations_json );

		ob_start();
		?>
		<div class="wp-block-custom-product-variation-swatches">
			<div class="wvs-archive-variations-wrapper" data-product_id="<?php echo absint( $product_id ); ?>"
				data-product_variations="<?php echo esc_attr( $variations_attr ); ?>"
				data-max_visible="<?php echo absint( $attributes['maxVisible'] ); ?>">

				<ul class="variations">
					<?php foreach ( $attributes_data as $attribute => $options ) : ?>
						<!-- Dynamic label that shows only selected variation name -->
						<li class="woo-variation-item-label hidden-by-default">
							<label for="<?php echo esc_attr( sprintf( '%s-%d', sanitize_title( $attribute ), $product_id ) ); ?>"
								class="dynamic-variation-label" data-attribute="<?php echo esc_attr( $attribute ); ?>">
								<span class="variation-name-text"></span>
							</label>
						</li>

						<li class="woo-variation-items-wrapper">
							<?php
							// Use WooCommerce's built-in function to generate attribute options
							if ( function_exists( 'wc_dropdown_variation_attribute_options' ) ) {
								wc_dropdown_variation_attribute_options( array(
									'options' => $options,
									'attribute' => $attribute,
									'product' => $product,
									'is_archive' => true
								) );
							}
							?>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="wvs-archive-information"></div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render basic structure when plugin is not available
	 *
	 * @param WC_Product $product
	 * @param array $attributes
	 * @param array $available_variations
	 * @param array $attributes_data
	 * @return string
	 */
	private function render_basic_structure( $product, $attributes, $available_variations, $attributes_data ) {
		$product_id = $product->get_id();

		ob_start();
		?>
		<div class="wp-block-custom-product-variation-swatches">
			<div class="basic-variations-wrapper" data-product_id="<?php echo absint( $product_id ); ?>">
				<?php foreach ( $attributes_data as $attribute => $options ) : ?>
					<!-- Dynamic label that shows only selected variation name -->
					<div class="variation-label dynamic-variation-label hidden-by-default"
						data-attribute="<?php echo esc_attr( $attribute ); ?>">
						<span class="variation-name-text"></span>
					</div>

					<div class="variation-options">
						<?php foreach ( $options as $option ) : ?>
							<span class="variation-option" data-attribute="<?php echo esc_attr( $attribute ); ?>"
								data-value="<?php echo esc_attr( $option ); ?>" data-option-name="<?php echo esc_attr( $option ); ?>">
								<?php echo esc_html( $option ); ?>
							</span>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		// Only enqueue on pages that might have Product Collection blocks
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! has_block( 'woocommerce/product-collection' ) ) {
			return;
		}

		// Enqueue existing variation swatches assets if available with safety checks
		if ( $this->is_variation_swatches_plugin_available() && wp_script_is( 'woo-variation-swatches-pro', 'registered' ) ) {
			wp_enqueue_script( 'woo-variation-swatches-pro' );
		}

		// Enqueue our custom JavaScript for dynamic label functionality
		$custom_js_path = get_stylesheet_directory_uri() . '/includes/blocks/variation-swatches/assets/frontend.js';
		wp_enqueue_script(
			'custom-variation-swatches',
			$custom_js_path,
			array( 'jquery' ),
			'1.0.0',
			true
		);
	}
}

// Initialize the block
new ProductVariationSwatchesBlock();
