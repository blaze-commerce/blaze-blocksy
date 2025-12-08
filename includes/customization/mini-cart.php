<?php

/**
 * Add mini cart specific data to localize script
 * This uses the filter hook from scripts.php
 */
add_filter( 'blaze_blocksy_mini_cart_localize_data', 'add_mini_cart_localize_data' );

function add_mini_cart_localize_data( $data ) {
	// Add any mini cart specific localization data here
	// The base data (ajax_url, nonce, etc.) is already handled in scripts.php

	return $data;
}

/**
 * Override mini cart template
 */
add_filter( 'wc_get_template', function ( $template, $template_name, $args ) {

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
			<span class="coupon-arrow">&#9660;</span>
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
add_action( 'woocommerce_widget_shopping_cart_total', function ( $total_html ) {
	if ( ! WC()->cart ) {
		return $total_html;
	}

	$cart = WC()->cart;
	$subtotal = $cart->get_cart_subtotal();
	$discount_total = $cart->get_discount_total();

	// Get shipping/tax note from customizer
	$cart_options = blaze_blocksy_get_cart_options();
	$shipping_tax_note = function_exists( 'blocksy_akg' )
		? blocksy_akg( 'mini_cart_shipping_tax_note', $cart_options, '* Shipping and tax are calculated after the shipping step is completed.' )
		: '* Shipping and tax are calculated after the shipping step is completed.';

	ob_start();
	?>
	<div class="mini-cart-totals-breakdown">
		<div class="total-line subtotal-line">
			<span class="total-label"><?php esc_html_e( 'Subtotal', 'blaze-blocksy' ); ?></span>
			<span class="total-amount"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php if ( $discount_total > 0 ) : ?>
			<div class="total-line discount-line">
				<span class="total-label"><?php esc_html_e( 'Discount', 'blaze-blocksy' ); ?></span>
				<span class="total-amount">-<?php echo wc_price( $discount_total ); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $shipping_tax_note ) ) : ?>
			<div class="shipping-tax-note">
				<small><?php echo esc_html( $shipping_tax_note ); ?></small>
			</div>
		<?php endif; ?>
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

	ob_start();
	?>
	<a href="<?php echo esc_url( $checkout_url ); ?>" class="button checkout wc-forward secure-checkout-btn">
		<?php esc_html_e( 'SECURE CHECKOUT', 'blaze-blocksy' ); ?>
		<span class="checkout-arrow">&rarr;</span>
	</a>
	<?php
	echo ob_get_clean();
}, 99999 );

/**
 * Add "You May Also Like" section after mini cart buttons
 */
/* *
add_action( 'woocommerce_widget_shopping_cart_before_total', function () {
	?>
	<ul class="product_recommendation_list_widget product_list_widget">
		<li class="mini-cart-recommendations">
			<div class="recommendations-header">
				<h4><?php esc_html_e( 'You May Also Like', 'blaze-blocksy' ); ?></h4>
			</div>
			<div class="recommendations-products">
				<?php blaze_blocksy_get_recommended_products_for_mini_cart(); ?>
			</div>
		</li>
	</ul>
	<?php
}, -1 );
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
	// Early return if WooCommerce cart is not available
	if ( ! WC()->cart ) {
		return;
	}

	$cart_items = WC()->cart->get_cart();
	$product_ids = array();

	// Collect product IDs from cart
	foreach ( $cart_items as $cart_item ) {
		$product_ids[] = $cart_item['product_id'];
	}

	// Generate cache key based on cart product IDs
	$cache_key = 'blaze_mini_cart_recs_' . md5( implode( '_', $product_ids ) );
	$recommended_products = get_transient( $cache_key );

	// If no cached data, fetch from database
	if ( false === $recommended_products ) {
		$recommended_products = array();

		if ( ! empty( $product_ids ) ) {
			// Get related products from the first cart item
			$first_product_id = $product_ids[0];
			$related_ids = wc_get_related_products( $first_product_id, 2 );

			if ( ! empty( $related_ids ) ) {
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
				'exclude' => $product_ids,
				'return' => 'ids', // Return only IDs for better performance
			) );

			$recommended_products = $recent_products;
		}

		// Cache for 1 hour
		set_transient( $cache_key, $recommended_products, HOUR_IN_SECONDS );
	}

	// Display recommended products
	if ( ! empty( $recommended_products ) ) {
		// Store original product to restore later
		$original_product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;

		// Get layout setting from customizer
		$cart_options = blaze_blocksy_get_cart_options();
		$layout = function_exists( 'blocksy_akg' )
			? blocksy_akg( 'mini_cart_recommendation_layout', $cart_options, 'stacked' )
			: 'stacked';

		// Determine wrapper class and template based on layout
		$wrapper_class = ( 'stacked' === $layout ) ? 'recommended-products-stacked' : 'recommended-products-grid';
		$template_name = ( 'stacked' === $layout ) ? 'product/recommend-product-card-stacked' : 'product/recommend-product-card';

		echo '<div class="' . esc_attr( $wrapper_class ) . '">';
		foreach ( array_slice( $recommended_products, 0, 2 ) as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$GLOBALS['product'] = $product;
				wc_get_template_part( $template_name );
			}
		}
		echo '</div>';

		// Restore original product
		$GLOBALS['product'] = $original_product;
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
add_filter( 'blocksy:options:retrieve', function ( $options, $path, $pass_inside ) {
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
			'label' => __( 'Custom Text Settings', 'blaze-blocksy' ),
		),

		'mini_cart_panel_title' => array(
			'label' => __( 'Cart Panel Title', 'blaze-blocksy' ),
			'type' => 'text',
			'value' => 'Shopping Cart',
			'design' => 'block',
			'desc' => __( 'Enter the title for the cart offcanvas panel header.', 'blaze-blocksy' ),
			'setting' => array( 'transport' => 'postMessage' ),
		),

		'mini_cart_empty_message' => array(
			'label' => __( 'Empty Cart Message', 'blaze-blocksy' ),
			'type' => 'text',
			'value' => 'Your cart is empty, continue to shopping to add item',
			'design' => 'block',
			'desc' => __( 'Enter the message displayed when the cart is empty.', 'blaze-blocksy' ),
			'setting' => array( 'transport' => 'postMessage' ),
		),

		'mini_cart_continue_shopping_text' => array(
			'label' => __( 'Continue Shopping Button Text', 'blaze-blocksy' ),
			'type' => 'text',
			'value' => 'CONTINUE TO SHOPPING',
			'design' => 'block',
			'desc' => __( 'Enter the text for the continue shopping button.', 'blaze-blocksy' ),
			'setting' => array( 'transport' => 'postMessage' ),
		),

		'mini_cart_continue_shopping_url' => array(
			'label' => __( 'Continue Shopping Button URL', 'blaze-blocksy' ),
			'type' => 'text',
			'value' => '',
			'design' => 'block',
			'desc' => __( 'Enter the URL for the continue shopping button. Leave empty to use default shop page.', 'blaze-blocksy' ),
			'setting' => array( 'transport' => 'postMessage' ),
		),

		'mini_cart_shipping_tax_note' => array(
			'label' => __( 'Shipping & Tax Note', 'blaze-blocksy' ),
			'type' => 'text',
			'value' => '* Shipping and tax are calculated after the shipping step is completed.',
			'design' => 'block',
			'desc' => __( 'Enter the shipping and tax note displayed below subtotal.', 'blaze-blocksy' ),
			'setting' => array( 'transport' => 'postMessage' ),
		),

		'bmcu_url_divider' => array(
			'type' => 'ct-divider',
		),

		'bmcu_url_section_title' => array(
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

		'bmcu_layout_divider' => array(
			'type' => 'ct-divider',
		),

		'bmcu_layout_section_title' => array(
			'type' => 'ct-title',
			'label' => __( 'Recommendation Products Layout', 'blaze-blocksy' ),
		),

		'mini_cart_recommendation_layout' => array(
			'label' => __( 'Product Card Layout', 'blaze-blocksy' ),
			'type' => 'ct-radio',
			'value' => 'stacked',
			'view' => 'text',
			'design' => 'block',
			'choices' => array(
				'grid' => __( 'Grid (2 Columns)', 'blaze-blocksy' ),
				'stacked' => __( 'Stacked (Like Cart Items)', 'blaze-blocksy' ),
			),
			'desc' => __( 'Choose how recommendation products are displayed in mini cart.', 'blaze-blocksy' ),
			'setting' => array( 'transport' => 'postMessage' ),
		),
	);

	// Merge our options with existing options
	return array_merge( $options, $custom_options );
}, 10, 3 );

/**
 * Get Blocksy cart options with caching
 */
function blaze_blocksy_get_cart_options() {
	static $cart_options = null;

	if ( null === $cart_options ) {
		$cart_options = array();

		if ( class_exists( 'Blocksy_Header_Builder_Render' ) ) {
			$header = new Blocksy_Header_Builder_Render();
			$cart_options = $header->get_item_data_for( 'cart' );
		}
	}

	return $cart_options;
}

/**
 * Add custom panel title to localized script data
 * This replaces the expensive output buffering approach with lightweight JavaScript
 */
add_filter( 'blaze_blocksy_mini_cart_localize_data', 'blaze_blocksy_add_panel_title_to_localize' );

function blaze_blocksy_add_panel_title_to_localize( $data ) {
	$cart_options = blaze_blocksy_get_cart_options();
	$custom_title = function_exists( 'blocksy_akg' )
		? blocksy_akg( 'mini_cart_panel_title', $cart_options, 'Shopping Cart' )
		: 'Shopping Cart';

	$data['panel_title'] = $custom_title;
	$data['default_panel_title'] = 'Shopping Cart';

	return $data;
}

/**
 * Add "Need Help?" link after mini cart
 */
add_action( 'woocommerce_widget_shopping_cart_after_buttons', function () {
	$cart_options = blaze_blocksy_get_cart_options();
	$help_url = function_exists( 'blocksy_akg' )
		? blocksy_akg( 'mini_cart_help_url', $cart_options, '/contact' )
		: '/contact';

	if ( empty( $help_url ) ) {
		return;
	}
	?>
	<div class="mini-cart-help">
		<a href="<?php echo esc_url( $help_url ); ?>"
			class="help-link"><?php esc_html_e( 'Need Help?', 'blaze-blocksy' ); ?></a>
	</div>
	<?php
}, 10 );


