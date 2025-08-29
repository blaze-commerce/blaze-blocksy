<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

// Get recently viewed products from localStorage (we'll use cookie as fallback)
$recently_viewed_products = array();
if ( function_exists( 'get_recently_viewed_products_from_cookie' ) ) {
	$recently_viewed_ids = get_recently_viewed_products_from_cookie();
	$recently_viewed_ids = array_slice( $recently_viewed_ids, 0, 2 ); // Limit to 4

	foreach ( $recently_viewed_ids as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product && $product->is_visible() ) {
			$recently_viewed_products[] = $product;
		}
	}
}

// Get wishlist products
$wishlist_products = array();
if ( function_exists( 'blc_get_ext' ) ) {

	try {
		$wishlist_instance = blc_get_ext( 'woocommerce-extra' )->get_wish_list();
		$wishlist_items = $wishlist_instance->get_current_wish_list();

		do_action( 'qm/info', [ 'wishlist' => $wishlist_items ] );

		if ( ! empty( $wishlist_items ) ) {
			$wishlist_items = array_map( function ($item) {
				return $item['id'];
			}, $wishlist_items );

			// remove duplicates from $recently_viewed_products
			$wishlist_items = array_diff( $wishlist_items, $recently_viewed_ids );
			$wishlist_items = array_slice( $wishlist_items, 0, 2 ); // Limit to 4

			foreach ( $wishlist_items as $item_id ) {
				$product = wc_get_product( $item_id );

				if ( $product && $product->is_visible() ) {
					$wishlist_products[] = $product;
				}
			}
		}
	} catch (Exception $e) {
		do_action( 'qm/info', [ 'wishlist_error' => $e->getMessage() ] );
	}
}

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>

	<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				/**
				 * This filter is documented in woocommerce/templates/cart/cart.php.
				 *
				 * @since 2.1.0
				 */
				$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
				$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				$item_total = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
				?>
				<li
					class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
					<?php
					echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'woocommerce_cart_item_remove_link',
						sprintf(
							'<a role="button" href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s" data-success_message="%s"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M21 5.98047C17.67 5.65047 14.32 5.48047 10.98 5.48047C9 5.48047 7.02 5.58047 5.04 5.78047L3 5.98047" stroke="#242424" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M8.5 4.97L8.72 3.66C8.88 2.71 9 2 10.69 2H13.31C15 2 15.13 2.75 15.28 3.67L15.5 4.97" stroke="#242424" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M18.8504 9.13965L18.2004 19.2096C18.0904 20.7796 18.0004 21.9996 15.2104 21.9996H8.79039C6.00039 21.9996 5.91039 20.7796 5.80039 19.2096L5.15039 9.13965" stroke="#242424" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10.3301 16.5H13.6601" stroke="#242424" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9.5 12.5H14.5" stroke="#242424" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</a>',
							esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
							/* translators: %s is the product name */
							esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
							esc_attr( $product_id ),
							esc_attr( $cart_item_key ),
							esc_attr( $_product->get_sku() ),
							/* translators: %s is the product name */
							esc_attr( sprintf( __( '&ldquo;%s&rdquo; has been removed from your cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) )
						),
						$cart_item_key
					);
					?>
					<figure class="product-image-wrapper">
						<?php echo $thumbnail; ?>
					</figure>
					<div class="product-info">
						<?php if ( empty( $product_permalink ) ) : ?>
							<?php echo wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>">
								<?php echo wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php endif; ?>
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<div class="product-price-quantity">
							<span class="product-price"><?php echo $item_total; ?></span>
						</div>
						<?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s', $cart_item['quantity'] ) . '</span>', $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</li>
				<?php
			}
		}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_total' ); ?>

	<div class="woocommerce-mini-cart__total total">
		<?php
		/**
		 * Hook: woocommerce_widget_shopping_cart_total.
		 *
		 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
		 */
		do_action( 'woocommerce_widget_shopping_cart_total' );
		?>
	</div>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

	<p class="woocommerce-mini-cart__buttons buttons">
		<?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?>
	</p>

	<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

<?php else : ?>

	<div class="woocommerce-mini-cart__empty-message">
		<div class="empty-cart-content">
			<!-- Empty Cart Icon and Message -->

			<div class="empty-cart-message">
				<p><?php esc_html_e( 'Your cart is empty, continue to shopping to add item', 'blaze-blocksy' ); ?></p>
			</div>

			<!-- Continue Shopping Button -->
			<div class="continue-shopping-wrapper">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="continue-shopping-btn">
					<?php esc_html_e( 'CONTINUE TO SHOPPING', 'blaze-blocksy' ); ?>
					<span class="arrow">→</span>
				</a>
			</div>

			<!-- Recently Viewed Items Section -->
			<?php if ( ! empty( $recently_viewed_products ) ) : ?>
				<div class="recommendations-section recently-viewed-section">
					<div class="recommendations-header">
						<h4><?php esc_html_e( 'Recently View Items', 'blaze-blocksy' ); ?></h4>
					</div>
					<div class="recommended-products-grid">
						<?php
						foreach ( $recently_viewed_products as $product ) :
							$GLOBALS['product'] = $product;
							wc_get_template_part( 'product/recommend-product-card', );
						endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Favorite Items Section -->
			<?php if ( ! empty( $wishlist_products ) ) : ?>
				<div class="recommendations-section favorite-section">
					<div class="recommendations-header">
						<h4><?php esc_html_e( 'Favorite', 'blaze-blocksy' ); ?></h4>
					</div>
					<div class="recommended-products-grid">
						<?php foreach ( $wishlist_products as $product ) :
							$GLOBALS['product'] = $product;
							wc_get_template_part( 'product/recommend-product-card' );
						endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="widget_shopping_cart_content"> </div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>