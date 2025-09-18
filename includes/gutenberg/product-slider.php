<?php
/**
 * Product Carousel Gutenberg Block
 *
 * Custom Gutenberg block for displaying WooCommerce products in an Owl Carousel slider
 * with configurable category filtering, sale attributes, and responsive settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Product_Carousel_Block
 *
 * Handles the registration and rendering of the product carousel Gutenberg block
 */
class Product_Carousel_Block {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Register the Gutenberg block
	 */
	public function register_block() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		register_block_type( 'blaze-blocksy/product-carousel', array(
			'attributes' => array(
				'selectedCategories' => array(
					'type' => 'array',
					'default' => array(),
				),
				'saleAttribute' => array(
					'type' => 'string',
					'default' => 'all',
				),
				'productsPerSlide' => array(
					'type' => 'object',
					'default' => array(
						'desktop' => 4,
						'tablet' => 3,
						'mobile' => 2,
					),
				),
				'showNavigation' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'showDots' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'autoplay' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'autoplayTimeout' => array(
					'type' => 'number',
					'default' => 5000,
				),
				'loop' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'margin' => array(
					'type' => 'number',
					'default' => 24,
				),
				'productsLimit' => array(
					'type' => 'number',
					'default' => 12,
				),
				'orderBy' => array(
					'type' => 'string',
					'default' => 'date',
				),
			),
			'render_callback' => array( $this, 'render_block' ),
			'supports' => array(
				'align' => array( 'wide', 'full' ),
				'className' => true,
			),
		) );
	}

	/**
	 * Render the block on the frontend
	 *
	 * @param array $attributes Block attributes
	 * @param string $content Block content
	 * @param WP_Block $block Block instance
	 * @return string Block HTML output
	 */
	public function render_block( $attributes, $content = '', $block = null ) {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return '<div class="woocommerce-info">' . __( 'WooCommerce is required for this block.', 'blaze-blocksy' ) . '</div>';
		}

		// Get products based on attributes
		$products = $this->get_products( $attributes );

		if ( empty( $products ) ) {
			return '<div class="woocommerce-info">' . __( 'No products found.', 'blaze-blocksy' ) . '</div>';
		}

		// Generate unique ID for this carousel instance
		$carousel_id = 'product-carousel-' . wp_generate_uuid4();

		// Get block wrapper attributes (includes Gutenberg classes)
		$wrapper_attributes = get_block_wrapper_attributes( array(
			'id' => $carousel_id,
			'class' => 'blaze-product-carousel-wrapper',
		) );

		// Start output buffering
		ob_start();

		// Render the carousel with wrapper attributes
		$this->render_carousel( $products, $attributes, $carousel_id, $wrapper_attributes );

		return ob_get_clean();
	}

	/**
	 * Get products based on block attributes
	 *
	 * @param array $attributes Block attributes
	 * @return array Array of WC_Product objects
	 */
	private function get_products( $attributes ) {
		$args = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => $attributes['productsLimit'],
			'meta_query' => array(),
			'tax_query' => array(),
		);

		// Handle order by
		$order_by = isset( $attributes['orderBy'] ) ? $attributes['orderBy'] : 'date';
		switch ( $order_by ) {
			case 'name':
				$args['orderby'] = 'title';
				$args['order'] = 'ASC';
				break;

			case 'newest':
				$args['orderby'] = 'date';
				$args['order'] = 'DESC';
				break;

			case 'most_selling':
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = 'total_sales';
				$args['order'] = 'DESC';
				break;

			case 'most_popular':
				// Order by average rating
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = '_wc_average_rating';
				$args['order'] = 'DESC';
				// Only include products with ratings
				$args['meta_query'][] = array(
					'key' => '_wc_average_rating',
					'value' => 0,
					'compare' => '>',
					'type' => 'DECIMAL',
				);
				break;

			default:
				$args['orderby'] = 'date';
				$args['order'] = 'DESC';
				break;
		}

		// Add category filter if categories are selected
		if ( ! empty( $attributes['selectedCategories'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $attributes['selectedCategories'],
				'operator' => 'IN',
			);
		}

		// Add sale attribute filters
		switch ( $attributes['saleAttribute'] ) {
			case 'featured':
				$args['tax_query'][] = array(
					'taxonomy' => 'product_visibility',
					'field' => 'name',
					'terms' => 'featured',
				);
				break;

			case 'on_sale':
				$args['meta_query'][] = array(
					'key' => '_sale_price',
					'value' => '',
					'compare' => '!=',
				);
				break;

			case 'new':
				// Products created in the last 30 days
				$args['date_query'] = array(
					array(
						'after' => '30 days ago',
					),
				);
				break;

			case 'in_stock':
				$args['meta_query'][] = array(
					'key' => '_stock_status',
					'value' => 'instock',
				);
				break;

			case 'out_of_stock':
				$args['meta_query'][] = array(
					'key' => '_stock_status',
					'value' => 'outofstock',
				);
				break;
		}

		// Exclude hidden products
		$args['tax_query'][] = array(
			'taxonomy' => 'product_visibility',
			'field' => 'name',
			'terms' => array( 'exclude-from-catalog', 'exclude-from-search' ),
			'operator' => 'NOT IN',
		);

		$query = new WP_Query( $args );
		$products = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$product = wc_get_product( get_the_ID() );
				if ( $product && $product->is_visible() ) {
					$products[] = $product;
				}
			}
			wp_reset_postdata();
		}

		return $products;
	}

	/**
	 * Render the carousel HTML
	 *
	 * @param array $products Array of WC_Product objects
	 * @param array $attributes Block attributes
	 * @param string $carousel_id Unique carousel ID
	 * @param string $wrapper_attributes Block wrapper attributes with Gutenberg classes
	 */
	private function render_carousel( $products, $attributes, $carousel_id, $wrapper_attributes ) {
		global $post;

		// Prepare carousel configuration
		$carousel_config = array(
			'loop' => $attributes['loop'],
			'margin' => $attributes['margin'],
			'nav' => $attributes['showNavigation'],
			'dots' => $attributes['showDots'],
			'autoplay' => $attributes['autoplay'],
			'autoplayTimeout' => $attributes['autoplayTimeout'],
			'responsive' => array(
				'0' => array(
					'items' => $attributes['productsPerSlide']['mobile'],
					'nav' => false, // Disable nav on mobile for better UX
				),
				'768' => array(
					'items' => $attributes['productsPerSlide']['tablet'],
				),
				'1000' => array(
					'items' => $attributes['productsPerSlide']['desktop'],
				),
			),
		);

		?>
		<div <?php echo $wrapper_attributes; ?>>
			<div class="products columns-4 owl-carousel owl-theme blaze-product-carousel"
				data-carousel-config='<?php echo wp_json_encode( $carousel_config ); ?>' data-products="type-1"
				data-hover="zoom-in">
				<?php
				// Set up WooCommerce loop context
				global $woocommerce_loop;
				$woocommerce_loop['is_shortcode'] = true;
				$woocommerce_loop['columns'] = $attributes['productsPerSlide']['desktop'];

				foreach ( $products as $product ) {
					// Set global product for template
					$GLOBALS['product'] = $product;

					// Use the existing product card template
					wc_get_template_part( 'content', 'product' );
				}

				// Reset global product
				unset( $GLOBALS['product'] );
				?>
			</div>
		</div>

		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				var $carousel = $('#<?php echo esc_js( $carousel_id ); ?> .blaze-product-carousel');
				var config = $carousel.data('carousel-config');

				if ($carousel.length && typeof $.fn.owlCarousel !== 'undefined') {
					$carousel.owlCarousel(config);
				}
			});
		</script>
		<?php
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		// Only enqueue on pages that might have the block
		if ( ! has_block( 'blaze-blocksy/product-carousel' ) && ! is_admin() ) {
			return;
		}

		// Enqueue Owl Carousel if not already loaded
		if ( ! wp_script_is( 'owl-carousel', 'enqueued' ) ) {
			wp_enqueue_style(
				'owl-carousel',
				'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css',
				array(),
				'2.3.4'
			);
			wp_enqueue_style(
				'owl-theme-default',
				'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css',
				array( 'owl-carousel' ),
				'2.3.4'
			);
			wp_enqueue_script(
				'owl-carousel',
				'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js',
				array( 'jquery' ),
				'2.3.4',
				true
			);
		}

		// Enqueue block-specific styles
		wp_enqueue_style(
			'blaze-product-carousel',
			BLAZE_BLOCKSY_URL . '/assets/css/product-carousel.css',
			array( 'owl-carousel' ),
			'1.0.0'
		);
	}

	/**
	 * Enqueue editor assets
	 */
	public function enqueue_editor_assets() {
		// Enqueue block editor script
		wp_enqueue_script(
			'blaze-product-carousel-editor',
			BLAZE_BLOCKSY_URL . '/assets/js/product-carousel-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		// Enqueue editor styles
		wp_enqueue_style(
			'blaze-product-carousel-editor',
			BLAZE_BLOCKSY_URL . '/assets/css/product-carousel-editor.css',
			array( 'wp-edit-blocks' ),
			'1.0.0'
		);

		// Localize script with data
		wp_localize_script( 'blaze-product-carousel-editor', 'blazeProductCarousel', array(
			'apiUrl' => rest_url( 'wp/v2/' ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		) );
	}

	/**
	 * Get all product categories for the editor
	 *
	 * @return array Array of category data
	 */
	public static function get_product_categories() {
		$categories = get_terms( array(
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		) );

		$category_options = array();
		foreach ( $categories as $category ) {
			$category_options[] = array(
				'value' => $category->term_id,
				'label' => $category->name,
			);
		}

		return $category_options;
	}
}

// Initialize the block
new Product_Carousel_Block();