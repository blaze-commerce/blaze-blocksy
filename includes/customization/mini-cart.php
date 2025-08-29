<?php

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'blaze-blocksy-mini-cart', BLAZE_BLOCKSY_URL . '/assets/css/mini-cart.css' );
	wp_enqueue_script( 'blaze-blocksy-mini-cart-js', BLAZE_BLOCKSY_URL . '/assets/js/mini-cart.js', array( 'jquery' ), '1.0.0', true );

	// Localize script for AJAX
	wp_localize_script( 'blaze-blocksy-mini-cart-js', 'blazeBlocksyMiniCart', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'blaze_blocksy_mini_cart_nonce' ),
		'applying_coupon' => __( 'Applying...', 'blaze-blocksy' ),
		'apply_coupon' => __( 'APPLY COUPON', 'blaze-blocksy' )
	) );
} );

/**
 * Override mini cart template
 */
add_filter( 'wc_get_template', function ($template, $template_name, $args) {

	if ( 'cart/mini-cart.php' === $template_name ) {
		return BLAZE_BLOCKSY_PATH . '/woocommerce/cart/mini-cart.php';
	}

	return $template;
}, 999, 3 );

/**
 * Add coupon form to mini cart before buttons
 */
add_action( 'woocommerce_widget_shopping_cart_before_total', function () {
	?>
	<div class="mini-cart-coupon-section">
		<div class="coupon-toggle">
			<span class="coupon-label"><?php esc_html_e( 'Coupon Code', 'blaze-blocksy' ); ?></span>
			<span class="coupon-arrow">▼</span>
		</div>
		<div class="coupon-form-wrapper" style="display: none;">
			<form class="mini-cart-coupon-form" method="post">
				<div class="coupon-input-wrapper">
					<input type="text" name="coupon_code" class="coupon-code-input"
						placeholder="<?php esc_attr_e( 'Enter Promo Code', 'blaze-blocksy' ); ?>" />
					<button type="submit"
						class="apply-coupon-btn"><?php esc_html_e( 'APPLY COUPON', 'blaze-blocksy' ); ?></button>
				</div>
				<?php wp_nonce_field( 'apply_coupon_mini_cart', 'mini_cart_coupon_nonce' ); ?>
			</form>
		</div>
	</div>
	<?php
} );

/**
 * Customize mini cart total display with price breakdown
 */
add_action( 'woocommerce_widget_shopping_cart_total', function ($total_html) {
	if ( ! WC()->cart ) {
		return $total_html;
	}

	$cart = WC()->cart;
	$subtotal = $cart->get_subtotal();
	$discount_total = $cart->get_discount_total();
	$shipping_total = $cart->get_shipping_total();
	$tax_total = $cart->get_total_tax();

	ob_start();
	?>
	<div class="mini-cart-totals-breakdown">
		<div class="total-line subtotal-line">
			<span class="total-label"><?php esc_html_e( 'Subtotal', 'blaze-blocksy' ); ?></span>
			<span class="total-amount"><?php echo wc_price( $subtotal ); ?></span>
		</div>

		<?php if ( $discount_total > 0 ) : ?>
			<div class="total-line discount-line">
				<span class="total-label"><?php esc_html_e( 'Discount', 'blaze-blocksy' ); ?></span>
				<span class="total-amount">-<?php echo wc_price( $discount_total ); ?></span>
			</div>
		<?php endif; ?>

		<div class="shipping-tax-note">
			<small><?php esc_html_e( '* Shipping and tax are calculated after the shipping step is completed.', 'blaze-blocksy' ); ?></small>
		</div>
	</div>
	<?php
	echo ob_get_clean();
} );

add_action( 'wp', function () {
	remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
	remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );
	remove_action( 'woocommerce_widget_shopping_cart_total', 'woocommerce_widget_shopping_cart_subtotal', 10 );
}, 99999 );

/**
 * Customize mini cart buttons to show "SECURE CHECKOUT"
 */
add_action( 'woocommerce_widget_shopping_cart_buttons', function () {
	$checkout_url = wc_get_checkout_url();
	$cart_url = wc_get_cart_url();

	ob_start();
	?>
	<a href="<?php echo esc_url( $checkout_url ); ?>" class="button checkout wc-forward secure-checkout-btn">
		<?php esc_html_e( 'SECURE CHECKOUT', 'blaze-blocksy' ); ?>
		<span class="checkout-arrow">→</span>
	</a>
	<?php
	echo ob_get_clean();
}, 99999 );

/**
 * Add "You May Also Like" section after mini cart buttons
 */
add_action( 'woocommerce_mini_cart_contents', function () {
	?>
	<li class="mini-cart-recommendations">
		<div class="recommendations-header">
			<h4><?php esc_html_e( 'You May Also Like', 'blaze-blocksy' ); ?></h4>
		</div>
		<div class="recommendations-products">
			<?php blaze_blocksy_get_recommended_products_for_mini_cart(); ?>
		</div>
	</li>
	<?php
}, 20 );

/**
 * Get recommended products for mini cart display
 */
function blaze_blocksy_get_recommended_products_for_mini_cart() {
	// Get related products based on cart items or fallback to recent products
	$cart_items = WC()->cart->get_cart();
	$product_ids = array();

	// Collect product IDs from cart
	foreach ( $cart_items as $cart_item ) {
		$product_ids[] = $cart_item['product_id'];
	}

	// Get related products
	$recommended_products = array();
	if ( ! empty( $product_ids ) ) {
		// Get related products from the first cart item
		$first_product_id = $product_ids[0];
		$product = wc_get_product( $first_product_id );

		if ( $product ) {
			$related_ids = wc_get_related_products( $first_product_id, 2 );
			$recommended_products = $related_ids;
		}
	}

	// Fallback to recent products if no related products found
	if ( empty( $recommended_products ) ) {
		$recent_products = wc_get_products( array(
			'limit' => 2,
			'orderby' => 'date',
			'order' => 'DESC',
			'status' => 'publish',
			'exclude' => $product_ids
		) );

		foreach ( $recent_products as $product ) {
			$recommended_products[] = $product->get_id();
		}
	}

	// Display recommended products
	if ( ! empty( $recommended_products ) ) {
		echo '<div class="recommended-products-grid">';
		foreach ( array_slice( $recommended_products, 0, 2 ) as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$GLOBALS['product'] = $product;
				wc_get_template_part( 'product/recommend-product-card' );
			}
		}
		echo '</div>';
	}
}

/**
 * Handle AJAX coupon application in mini cart
 */
add_action( 'wp_ajax_apply_mini_cart_coupon', 'blaze_blocksy_handle_mini_cart_coupon' );
add_action( 'wp_ajax_nopriv_apply_mini_cart_coupon', 'blaze_blocksy_handle_mini_cart_coupon' );

function blaze_blocksy_handle_mini_cart_coupon() {
	// Verify nonce
	if ( ! wp_verify_nonce( $_POST['nonce'], 'blaze_blocksy_mini_cart_nonce' ) ) {
		wp_die( 'Security check failed' );
	}

	$coupon_code = sanitize_text_field( $_POST['coupon_code'] );

	if ( empty( $coupon_code ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a coupon code.', 'blaze-blocksy' ) ) );
	}

	// Apply coupon
	$result = WC()->cart->apply_coupon( $coupon_code );

	if ( $result ) {
		// Get updated cart fragments
		WC_AJAX::get_refreshed_fragments();
	} else {
		// Get the last error message
		$notices = wc_get_notices( 'error' );
		$error_message = ! empty( $notices ) ? $notices[0]['notice'] : __( 'Invalid coupon code.', 'blaze-blocksy' );
		wc_clear_notices();

		wp_send_json_error( array( 'message' => $error_message ) );
	}
}

/**
 * Add field URL to Blocksy cart customizer options
 */
add_filter( 'blocksy:options:retrieve', function ($options, $path, $pass_inside) {
	// Check if this is the cart options file
	if ( strpos( $path, 'panel-builder/header/cart/options.php' ) === false ) {
		return $options;
	}

	// Add custom options to cart settings
	$custom_options = array(
		'bmcu_divider' => array(
			'type' => 'ct-divider',
		),

		'bmcu_section_title' => array(
			'type' => 'ct-title',
			'label' => __( 'Custom URL Settings', 'blaze-blocksy' ),
		),

		'mini_cart_help_url' => array(
			'label' => __( 'Help Link URL', 'blaze-blocksy' ),
			'type' => 'text',
			'value' => '/contact',
			'design' => 'block',
			'desc' => __( 'Enter URL for the "Need Help?" link in mini cart.', 'blaze-blocksy' ),
			'setting' => array( 'transport' => 'postMessage' ),
		),
	);

	// Merge our options with existing options
	return array_merge( $options, $custom_options );
}, 10, 3 );

/**
 * Add "Need Help?" link after mini cart
 */
add_action( 'woocommerce_widget_shopping_cart_after_buttons', function () {
	// Get URL from Blocksy cart options
	if ( class_exists( 'Blocksy_Header_Builder_Render' ) ) {
		$header = new Blocksy_Header_Builder_Render();
		$atts = $header->get_item_data_for( 'cart' );
		$help_url = blocksy_akg( 'mini_cart_help_url', $atts, '/contact' );
	} else {
		$help_url = '/contact'; // Fallback
	}
	?>
	<div class="mini-cart-help">
		<a href="<?php echo esc_url( $help_url ); ?>"
			class="help-link"><?php esc_html_e( 'Need Help?', 'blaze-blocksy' ); ?></a>
	</div>
	<?php
}, 10 );


