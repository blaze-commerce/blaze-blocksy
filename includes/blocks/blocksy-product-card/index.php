<?php
/**
 * Blocksy Product Card Block
 *
 * Renders a product card using Blocksy's native card rendering system,
 * respecting all Customizer settings (card type, layout, hover effects, toolbar).
 *
 * Instead of wrapping output in its own <ul><li>, this block:
 * - Injects data-products/data-hover attrs onto the parent wc-block-product-template <ul>
 * - Outputs card content (figure, title, price, etc.) directly into the existing <li>
 *
 * @package BlazeBlocksy
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BlocksyProductCardBlock {

	public function __construct() {
		add_action( 'init', [ $this, 'register_block' ] );
		add_filter( 'render_block_woocommerce/product-template', [ $this, 'inject_blocksy_attrs' ], 10, 2 );
	}

	public function register_block() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( ! function_exists( 'blocksy_get_theme_mod' ) ) {
			return;
		}

		register_block_type(
			__DIR__ . '/block.json',
			[
				'render_callback' => [ $this, 'render_block' ],
			]
		);
	}

	/**
	 * Inject Blocksy data attributes onto the woocommerce/product-template <ul>.
	 *
	 * Adds data-products, data-hover, and the "products" class to the existing
	 * <ul class="wc-block-product-template"> so Blocksy's CSS selectors match.
	 */
	public function inject_blocksy_attrs( $block_content, $block ) {
		if ( ! function_exists( 'blocksy_get_theme_mod' ) ) {
			return $block_content;
		}

		// Only inject if our block is used inside this product template
		if ( ! $this->has_blocksy_card_inner_block( $block ) ) {
			return $block_content;
		}

		$shop_cards_type = blocksy_get_theme_mod( 'shop_cards_type', 'type-1' );

		// Get hover value from layout config
		$hover_value = $this->get_hover_value();

		// Build attributes to inject
		$inject_attrs = ' data-products="' . esc_attr( $shop_cards_type ) . '"';

		if ( $hover_value !== 'none' ) {
			$inject_attrs .= ' data-hover="' . esc_attr( $hover_value ) . '"';
		}

		if ( function_exists( 'blocksy_quick_view_attr' ) ) {
			$quick_view = blocksy_quick_view_attr();
			foreach ( $quick_view as $key => $value ) {
				$inject_attrs .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}

		// Inject attributes into the opening <ul> tag and add "products" class
		$block_content = preg_replace(
			'/(<ul\b[^>]*class="[^"]*)(")/',
			'$1 products$2' . $inject_attrs,
			$block_content,
			1
		);

		return $block_content;
	}

	/**
	 * Check if our blocksy-product-card block is among the inner blocks.
	 */
	private function has_blocksy_card_inner_block( $block ) {
		if ( empty( $block['innerBlocks'] ) ) {
			return false;
		}

		foreach ( $block['innerBlocks'] as $inner_block ) {
			if ( $inner_block['blockName'] === 'custom/blocksy-product-card' ) {
				return true;
			}

			if ( ! empty( $inner_block['innerBlocks'] ) && $this->has_blocksy_card_inner_block( $inner_block ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the hover value from Blocksy Customizer layout config.
	 */
	private function get_hover_value() {
		$default_layout       = blocksy_get_woo_archive_layout_defaults();
		$render_layout_config = blocksy_get_theme_mod( 'woo_card_layout', $default_layout );
		$render_layout_config = blocksy_normalize_layout( $render_layout_config, $default_layout );

		foreach ( $render_layout_config as $layout ) {
			if ( $layout['id'] === 'product_image' ) {
				return blocksy_akg( 'product_image_hover', $layout, 'none' );
			}
		}

		return 'none';
	}

	/**
	 * Render the block — outputs only card content, no wrapper elements.
	 */
	public function render_block( $attributes, $content, $block ) {
		$product_id = $block->context['woocommerce/product-id']
			?? $block->context['postId']
			?? null;

		if ( ! $product_id ) {
			return '';
		}

		$wc_product = wc_get_product( $product_id );

		if ( ! $wc_product ) {
			return '';
		}

		return $this->render_card( $wc_product, $product_id );
	}

	private function render_card( $wc_product, $product_id ) {
		global $product, $post;
		$original_product = $product;
		$original_post    = $post;

		$product = $wc_product;
		$post    = get_post( $product_id );
		setup_postdata( $post );

		try {
			$html = $this->build_card_html();
		} finally {
			$product = $original_product;
			$post    = $original_post;
			if ( $original_post ) {
				setup_postdata( $original_post );
			} else {
				wp_reset_postdata();
			}
		}

		return $html;
	}

	/**
	 * Build card HTML — only the card elements, no wrapper <ul>/<li>.
	 */
	private function build_card_html() {
		$shop_cards_type = blocksy_get_theme_mod( 'shop_cards_type', 'type-1' );

		$default_layout       = blocksy_get_woo_archive_layout_defaults();
		$render_layout_config = blocksy_get_theme_mod( 'woo_card_layout', $default_layout );
		$render_layout_config = blocksy_normalize_layout( $render_layout_config, $default_layout );

		ob_start();

		foreach ( $render_layout_config as $layout ) {
			if ( ! $layout['enabled'] ) {
				continue;
			}

			$this->render_layout_element( $layout, $shop_cards_type );
		}

		return ob_get_clean();
	}

	private function render_layout_element( $layout, $shop_cards_type ) {
		switch ( $layout['id'] ) {

			case 'product_image':
				blocksy_template_loop_product_thumbnail( $layout );
				break;

			case 'product_title':
				$this->render_title( $layout );
				break;

			case 'product_price':
				if ( $shop_cards_type !== 'type-2' ) {
					$this->render_price();
				}
				break;

			case 'product_rating':
				$this->render_rating( $layout );
				break;

			case 'product_meta':
				$this->render_meta( $layout );
				break;

			case 'product_desc':
				$this->render_description( $layout );
				break;

			case 'product_stock':
				$this->render_stock();
				break;

			case 'product_add_to_cart':
				if ( $shop_cards_type === 'type-1' ) {
					$this->render_add_to_cart( $layout );
				}
				break;

			case 'product_add_to_cart_and_price':
				if ( $shop_cards_type === 'type-2' ) {
					$this->render_add_to_cart_and_price();
				}
				break;

			default:
				do_action( 'blocksy:woocommerce:product-card:custom:layer', $layout );
				break;
		}
	}

	private function render_title( $layout ) {
		$heading_tag = blocksy_akg( 'heading_tag', $layout, 'h2' );

		$link_attrs = apply_filters(
			'blocksy:woocommerce:product-card:title:link',
			[
				'href'   => get_the_permalink(),
				'target' => '_self',
			]
		);

		do_action( 'blocksy:woocommerce:product-card:title:before' );

		echo blocksy_html_tag(
			$heading_tag,
			[
				'class' => esc_attr(
					apply_filters(
						'woocommerce_product_loop_title_classes',
						'woocommerce-loop-product__title'
					)
				),
			],
			blocksy_html_tag(
				'a',
				array_merge(
					[
						'class' => 'woocommerce-LoopProduct-link woocommerce-loop-product__link',
					],
					$link_attrs
				),
				get_the_title()
			)
		);

		do_action( 'blocksy:woocommerce:product-card:title:after' );
	}

	private function render_price() {
		do_action( 'blocksy:woocommerce:product-card:price:before' );

		ob_start();
		woocommerce_template_loop_price();
		$default_price = ob_get_clean();

		echo apply_filters(
			'blocksy:woocommerce:product-card:price',
			$default_price
		);

		do_action( 'blocksy:woocommerce:product-card:price:after' );
	}

	private function render_rating( $layout ) {
		global $product;

		ob_start();
		woocommerce_template_loop_rating();
		$rating_stars = ob_get_clean();

		if ( $product->get_review_count() <= 0 ) {
			return;
		}

		$average_rating = '';
		$review_count   = '';

		if ( blocksy_akg( 'average_rating', $layout, 'no' ) === 'yes' ) {
			$average_rating = blocksy_html_tag(
				'span',
				[ 'class' => 'ct-rating-average' ],
				'(' . $product->get_average_rating() . ')'
			);
		}

		if ( blocksy_akg( 'review_count', $layout, 'no' ) === 'yes' ) {
			$review_count = blocksy_html_tag(
				'span',
				[ 'class' => 'ct-rating-count' ],
				'(' . $product->get_review_count() . ')'
			);
		}

		echo blocksy_html_tag(
			'div',
			[ 'class' => 'ct-woo-card-rating' ],
			$rating_stars . $review_count . $average_rating
		);
	}

	private function render_meta( $layout ) {
		$style = isset( $layout['style'] ) ? $layout['style'] : 'simple';

		echo blocksy_post_meta(
			[
				[
					'id'       => 'categories',
					'enabled'  => true,
					'style'    => $style,
					'taxonomy' => blocksy_akg( 'taxonomy', $layout, 'product_cat' ),
				],
			],
			[
				'attr' => [
					'data-id' => blocksy_akg( '__id', $layout, 'default' ),
				],
			]
		);
	}

	private function render_description( $layout ) {
		echo blocksy_entry_excerpt( [
			'length' => blocksy_akg( 'excerpt_length', $layout, '40' ),
			'source' => blocksy_default_akg( 'excerpt_source', $layout, 'excerpt' ),
		] );
	}

	private function render_stock() {
		global $product;

		echo blocksy_html_tag(
			'div',
			[ 'class' => 'ct-woo-card-stock' ],
			wc_get_stock_html( $product )
		);
	}

	private function render_add_to_cart( $layout ) {
		$auto_hide       = blocksy_akg( 'auto_hide_button', $layout, 'yes' );
		$equal_alignment = blocksy_akg( 'button_equal_alignment', $layout, 'yes' );

		$html_atts = [];

		if ( $auto_hide === 'yes' ) {
			$html_atts['data-add-to-cart'] = 'auto-hide';
		}

		if ( $equal_alignment === 'yes' ) {
			$html_atts['data-alignment'] = 'equal';
		}

		do_action( 'blocksy:woocommerce:product-card:actions:before' );
		echo '<div class="ct-woo-card-actions" ' . blocksy_attr_to_html( $html_atts ) . '>';
		woocommerce_template_loop_add_to_cart();
		echo '</div>';
		do_action( 'blocksy:woocommerce:product-card:actions:after' );
	}

	private function render_add_to_cart_and_price() {
		do_action( 'blocksy:woocommerce:product-card:actions:before' );
		echo '<div class="ct-woo-card-actions" data-add-to-cart="auto-hide">';

		ob_start();
		woocommerce_template_loop_price();
		$default_price = ob_get_clean();

		echo apply_filters(
			'blocksy:woocommerce:product-card:price',
			$default_price
		);

		woocommerce_template_loop_add_to_cart();
		echo '</div>';
		do_action( 'blocksy:woocommerce:product-card:actions:after' );
	}
}

new BlocksyProductCardBlock();
