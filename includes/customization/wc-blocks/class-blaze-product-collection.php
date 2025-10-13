<?php
/**
 * Blaze Product Collection Block
 *
 * Custom Product Collection block with responsive display settings.
 * Allows different product counts and column layouts for desktop, tablet, and mobile.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Blaze Product Collection Block Class
 */
class Blaze_Product_Collection_Block {

	/**
	 * Block name
	 *
	 * @var string
	 */
	protected $block_name = 'blaze/product-collection';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Register the block
	 */
	public function register_block() {
		// Register block type
		register_block_type(
			$this->block_name,
			array(
				'api_version'     => 3,
				'title'           => __( 'Blaze Product Collection', 'blocksy-child' ),
				'description'     => __( 'Display a responsive collection of products from your store.', 'blocksy-child' ),
				'category'        => 'woocommerce',
				'icon'            => 'grid-view',
				'keywords'        => array( 'woocommerce', 'products', 'collection', 'responsive', 'blaze' ),
				'supports'        => array(
					'align'   => array( 'wide', 'full' ),
					'html'    => false,
					'anchor'  => true,
				),
				'attributes'      => $this->get_block_attributes(),
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Get block attributes
	 *
	 * @return array Block attributes
	 */
	private function get_block_attributes() {
		return array(
			'queryId'                    => array(
				'type' => 'number',
			),
			'query'                      => array(
				'type'    => 'object',
				'default' => array(
					'perPage'      => 8,
					'pages'        => 0,
					'offset'       => 0,
					'postType'     => 'product',
					'order'        => 'asc',
					'orderBy'      => 'title',
					'author'       => '',
					'search'       => '',
					'exclude'      => array(),
					'sticky'       => '',
					'inherit'      => false,
					'taxQuery'     => null,
					'parents'      => array(),
					'woocommerceOnSale' => false,
					'woocommerceStockStatus' => array(),
					'woocommerceAttributes' => array(),
					'isProductCollectionBlock' => true,
				),
			),
			'displayLayout'              => array(
				'type'    => 'object',
				'default' => array(
					'type'    => 'flex',
					'columns' => 4,
				),
			),
			'enableResponsive'           => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'responsiveColumns'          => array(
				'type'    => 'object',
				'default' => array(
					'desktop' => 4,
					'tablet'  => 3,
					'mobile'  => 2,
				),
			),
			'responsiveProductCount'     => array(
				'type'    => 'object',
				'default' => array(
					'desktop' => 8,
					'tablet'  => 6,
					'mobile'  => 4,
				),
			),
			'collection'                 => array(
				'type' => 'string',
			),
			'hideControls'               => array(
				'type'    => 'array',
				'default' => array(),
			),
			'align'                      => array(
				'type' => 'string',
			),
		);
	}

	/**
	 * Render block callback
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content Block content.
	 * @param object $block Block object.
	 * @return string Block HTML.
	 */
	public function render_block( $attributes, $content, $block ) {
		// Get responsive settings
		$enable_responsive = $attributes['enableResponsive'] ?? true;
		$responsive_columns = $attributes['responsiveColumns'] ?? array(
			'desktop' => 4,
			'tablet'  => 3,
			'mobile'  => 2,
		);
		$responsive_counts = $attributes['responsiveProductCount'] ?? array(
			'desktop' => 8,
			'tablet'  => 6,
			'mobile'  => 4,
		);

		// Get query parameters
		$query_args = $this->build_query_args( $attributes );

		// Get products
		$products = $this->get_products( $query_args );

		// Build wrapper attributes
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class'                      => 'blaze-product-collection',
				'data-enable-responsive'     => $enable_responsive ? 'true' : 'false',
				'data-responsive-columns'    => wp_json_encode( $responsive_columns ),
				'data-responsive-counts'     => wp_json_encode( $responsive_counts ),
				'data-current-device'        => 'desktop',
			)
		);

		// Start output buffering
		ob_start();
		?>
		<div <?php echo $wrapper_attributes; ?>>
			<div class="blaze-product-collection__inner">
				<?php if ( ! empty( $products ) ) : ?>
					<ul class="products columns-<?php echo esc_attr( $responsive_columns['desktop'] ); ?>">
						<?php foreach ( $products as $product ) : ?>
							<?php $this->render_product_item( $product ); ?>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="woocommerce-info"><?php esc_html_e( 'No products found.', 'blocksy-child' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Build query arguments
	 *
	 * @param array $attributes Block attributes.
	 * @return array Query arguments.
	 */
	private function build_query_args( $attributes ) {
		$query = $attributes['query'] ?? array();

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $query['perPage'] ?? 8,
			'orderby'        => $query['orderBy'] ?? 'title',
			'order'          => $query['order'] ?? 'ASC',
		);

		// Handle on sale filter
		if ( ! empty( $query['woocommerceOnSale'] ) ) {
			$args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
		}

		// Handle stock status filter
		if ( ! empty( $query['woocommerceStockStatus'] ) ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_stock_status',
					'value'   => $query['woocommerceStockStatus'],
					'compare' => 'IN',
				),
			);
		}

		// Handle taxonomy query
		if ( ! empty( $query['taxQuery'] ) ) {
			$args['tax_query'] = $query['taxQuery'];
		}

		return apply_filters( 'blaze_product_collection_query_args', $args, $attributes );
	}

	/**
	 * Get products based on query args
	 *
	 * @param array $args Query arguments.
	 * @return array Array of WC_Product objects.
	 */
	private function get_products( $args ) {
		$query = new WP_Query( $args );
		$products = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$product = wc_get_product( get_the_ID() );
				if ( $product ) {
					$products[] = $product;
				}
			}
			wp_reset_postdata();
		}

		return $products;
	}

	/**
	 * Render single product item
	 *
	 * @param WC_Product $product Product object.
	 */
	private function render_product_item( $product ) {
		?>
		<li <?php wc_product_class( '', $product ); ?>>
			<?php
			/**
			 * Hook: woocommerce_before_shop_loop_item.
			 */
			do_action( 'woocommerce_before_shop_loop_item' );
			?>

			<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
				<?php
				/**
				 * Hook: woocommerce_before_shop_loop_item_title.
				 */
				do_action( 'woocommerce_before_shop_loop_item_title' );

				/**
				 * Hook: woocommerce_shop_loop_item_title.
				 */
				do_action( 'woocommerce_shop_loop_item_title' );

				/**
				 * Hook: woocommerce_after_shop_loop_item_title.
				 */
				do_action( 'woocommerce_after_shop_loop_item_title' );
				?>
			</a>

			<?php
			/**
			 * Hook: woocommerce_after_shop_loop_item.
			 */
			do_action( 'woocommerce_after_shop_loop_item' );
			?>
		</li>
		<?php
	}

	/**
	 * Enqueue editor assets
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script(
			'blaze-product-collection-editor',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/blaze-product-collection/index.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-block-editor' ),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/blaze-product-collection/index.js' ),
			true
		);

		wp_enqueue_style(
			'blaze-product-collection-editor',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/blaze-product-collection/editor.css',
			array(),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/blaze-product-collection/editor.css' )
		);
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		if ( ! has_block( $this->block_name ) ) {
			return;
		}

		wp_enqueue_script(
			'blaze-product-collection-frontend',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/blaze-product-collection/script.js',
			array( 'jquery' ),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/blaze-product-collection/script.js' ),
			true
		);

		wp_enqueue_style(
			'blaze-product-collection-style',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/blaze-product-collection/style.css',
			array(),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/blaze-product-collection/style.css' )
		);
	}
}

// Initialize the block
new Blaze_Product_Collection_Block();

