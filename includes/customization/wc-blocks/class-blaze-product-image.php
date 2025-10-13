<?php
/**
 * Blaze Product Image Block
 *
 * Custom Product Image block with wishlist button and hover image functionality.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Blaze Product Image Block Class
 */
class Blaze_Product_Image_Block {

	/**
	 * Block name
	 *
	 * @var string
	 */
	protected $block_name = 'blaze/product-image';

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
		register_block_type(
			$this->block_name,
			array(
				'api_version'     => 3,
				'title'           => __( 'Blaze Product Image', 'blocksy-child' ),
				'description'     => __( 'Display product image with wishlist button and hover effect.', 'blocksy-child' ),
				'category'        => 'woocommerce',
				'icon'            => 'format-image',
				'keywords'        => array( 'woocommerce', 'product', 'image', 'wishlist', 'blaze' ),
				'supports'        => array(
					'align'   => true,
					'html'    => false,
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
			'productId'                => array(
				'type'    => 'number',
				'default' => 0,
			),
			'showProductLink'          => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'showSaleBadge'            => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'saleBadgeAlign'           => array(
				'type'    => 'string',
				'default' => 'right',
			),
			'imageSizing'              => array(
				'type'    => 'string',
				'default' => 'full',
			),
			'showWishlistButton'       => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'wishlistButtonPosition'   => array(
				'type'    => 'string',
				'default' => 'top-right',
			),
			'enableHoverImage'         => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'width'                    => array(
				'type' => 'string',
			),
			'height'                   => array(
				'type' => 'string',
			),
			'scale'                    => array(
				'type'    => 'string',
				'default' => 'cover',
			),
			'aspectRatio'              => array(
				'type' => 'string',
			),
			'isDescendentOfQueryLoop'  => array(
				'type'    => 'boolean',
				'default' => false,
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
		// Get product
		$product = $this->get_product( $attributes, $block );

		if ( ! $product ) {
			return '';
		}

		// Parse attributes
		$show_product_link = $attributes['showProductLink'] ?? true;
		$show_sale_badge = $attributes['showSaleBadge'] ?? true;
		$sale_badge_align = $attributes['saleBadgeAlign'] ?? 'right';
		$show_wishlist = $attributes['showWishlistButton'] ?? true;
		$wishlist_position = $attributes['wishlistButtonPosition'] ?? 'top-right';
		$enable_hover = $attributes['enableHoverImage'] ?? true;
		$image_sizing = $attributes['imageSizing'] ?? 'full';

		// Build wrapper attributes
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class'                         => 'blaze-product-image',
				'data-product-id'               => $product->get_id(),
				'data-show-wishlist'            => $show_wishlist ? 'true' : 'false',
				'data-wishlist-position'        => $wishlist_position,
				'data-enable-hover'             => $enable_hover ? 'true' : 'false',
			)
		);

		// Start output
		ob_start();
		?>
		<div <?php echo $wrapper_attributes; ?>>
			<div class="blaze-product-image__inner">
				<?php if ( $show_product_link ) : ?>
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="blaze-product-image__link">
				<?php endif; ?>

					<div class="blaze-product-image__container">
						<?php
						// Render main image
						echo $this->render_product_image( $product, $attributes, 'main' );

						// Render hover image if enabled
						if ( $enable_hover ) {
							echo $this->render_product_image( $product, $attributes, 'hover' );
						}

						// Render sale badge
						if ( $show_sale_badge && $product->is_on_sale() ) {
							echo $this->render_sale_badge( $product, $sale_badge_align );
						}

						// Render wishlist button
						if ( $show_wishlist ) {
							echo $this->render_wishlist_button( $product, $wishlist_position );
						}
						?>
					</div>

				<?php if ( $show_product_link ) : ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get product object
	 *
	 * @param array  $attributes Block attributes.
	 * @param object $block Block object.
	 * @return WC_Product|null Product object or null.
	 */
	private function get_product( $attributes, $block ) {
		$product_id = $attributes['productId'] ?? 0;

		// If in query loop context, get product from post
		if ( ! empty( $block->context['postId'] ) ) {
			$product_id = $block->context['postId'];
		}

		// Get from global post if still no ID
		if ( ! $product_id ) {
			global $post;
			if ( $post && 'product' === $post->post_type ) {
				$product_id = $post->ID;
			}
		}

		if ( ! $product_id ) {
			return null;
		}

		return wc_get_product( $product_id );
	}

	/**
	 * Render product image
	 *
	 * @param WC_Product $product Product object.
	 * @param array      $attributes Block attributes.
	 * @param string     $type Image type (main or hover).
	 * @return string Image HTML.
	 */
	private function render_product_image( $product, $attributes, $type = 'main' ) {
		$image_size = $attributes['imageSizing'] ?? 'full';
		$image_id = null;

		if ( 'main' === $type ) {
			$image_id = $product->get_image_id();
		} elseif ( 'hover' === $type ) {
			// Get second image from gallery
			$gallery_ids = $product->get_gallery_image_ids();
			if ( ! empty( $gallery_ids ) ) {
				$image_id = $gallery_ids[0];
			}
		}

		if ( ! $image_id ) {
			if ( 'main' === $type ) {
				return $this->render_placeholder_image( $attributes );
			}
			return '';
		}

		// Build image style
		$image_style = '';
		if ( ! empty( $attributes['width'] ) ) {
			$image_style .= sprintf( 'width:%s;', $attributes['width'] );
		}
		if ( ! empty( $attributes['height'] ) ) {
			$image_style .= sprintf( 'height:%s;', $attributes['height'] );
		}
		if ( ! empty( $attributes['aspectRatio'] ) ) {
			$image_style .= sprintf( 'aspect-ratio:%s;', $attributes['aspectRatio'] );
		}
		if ( ! empty( $attributes['scale'] ) ) {
			$image_style .= sprintf( 'object-fit:%s;', $attributes['scale'] );
		}

		$image_class = 'blaze-product-image__img blaze-product-image__img--' . $type;
		$alt_text = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

		return wp_get_attachment_image(
			$image_id,
			$image_size,
			false,
			array(
				'class' => $image_class,
				'alt'   => $alt_text ? $alt_text : $product->get_name(),
				'style' => $image_style,
			)
		);
	}

	/**
	 * Render placeholder image
	 *
	 * @param array $attributes Block attributes.
	 * @return string Placeholder HTML.
	 */
	private function render_placeholder_image( $attributes ) {
		return sprintf(
			'<img src="%s" alt="%s" class="blaze-product-image__img blaze-product-image__img--placeholder" />',
			esc_url( wc_placeholder_img_src() ),
			esc_attr__( 'Placeholder', 'blocksy-child' )
		);
	}

	/**
	 * Render sale badge
	 *
	 * @param WC_Product $product Product object.
	 * @param string     $align Badge alignment.
	 * @return string Badge HTML.
	 */
	private function render_sale_badge( $product, $align = 'right' ) {
		return sprintf(
			'<span class="blaze-product-image__badge blaze-product-image__badge--sale blaze-product-image__badge--align-%s">%s</span>',
			esc_attr( $align ),
			esc_html__( 'Sale!', 'blocksy-child' )
		);
	}

	/**
	 * Render wishlist button
	 *
	 * @param WC_Product $product Product object.
	 * @param string     $position Button position.
	 * @return string Button HTML.
	 */
	private function render_wishlist_button( $product, $position = 'top-right' ) {
		$product_id = $product->get_id();
		$is_in_wishlist = $this->is_product_in_wishlist( $product_id );
		$button_class = 'blaze-product-image__wishlist';
		$button_class .= ' blaze-product-image__wishlist--' . $position;
		$button_class .= $is_in_wishlist ? ' is-in-wishlist' : '';

		ob_start();
		?>
		<button 
			type="button" 
			class="<?php echo esc_attr( $button_class ); ?>"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
			aria-label="<?php echo esc_attr( $is_in_wishlist ? __( 'Remove from wishlist', 'blocksy-child' ) : __( 'Add to wishlist', 'blocksy-child' ) ); ?>"
		>
			<svg class="blaze-wishlist-icon blaze-wishlist-icon--outline" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" stroke="currentColor" stroke-width="2" fill="none"/>
			</svg>
			<svg class="blaze-wishlist-icon blaze-wishlist-icon--filled" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
			</svg>
		</button>
		<?php
		return ob_get_clean();
	}

	/**
	 * Check if product is in wishlist
	 *
	 * @param int $product_id Product ID.
	 * @return bool True if in wishlist.
	 */
	private function is_product_in_wishlist( $product_id ) {
		// Check if BlocksyChildWishlistHelper exists
		if ( class_exists( 'BlocksyChildWishlistHelper' ) ) {
			$wishlist_items = BlocksyChildWishlistHelper::get_wishlist_items();
			return in_array( $product_id, $wishlist_items, true );
		}

		return false;
	}

	/**
	 * Enqueue editor assets
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script(
			'blaze-product-image-editor',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/blaze-product-image/index.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-block-editor' ),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/blaze-product-image/index.js' ),
			true
		);

		wp_enqueue_style(
			'blaze-product-image-editor',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/blaze-product-image/editor.css',
			array(),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/blaze-product-image/editor.css' )
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
			'blaze-product-image-frontend',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/blaze-product-image/script.js',
			array( 'jquery' ),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/blaze-product-image/script.js' ),
			true
		);

		// Localize script for AJAX
		wp_localize_script(
			'blaze-product-image-frontend',
			'blazeProductImage',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'blaze_wishlist_nonce' ),
			)
		);

		wp_enqueue_style(
			'blaze-product-image-style',
			BLAZE_BLOCKSY_URL . '/assets/wc-blocks/blaze-product-image/style.css',
			array(),
			filemtime( BLAZE_BLOCKSY_PATH . '/assets/wc-blocks/blaze-product-image/style.css' )
		);
	}
}

// Initialize the block
new Blaze_Product_Image_Block();

