<?php
/**
 * Checkout Upsell — "Exclusive Offer" Order Bump
 *
 * Displays a configurable product upsell inside the Payment step (Step 4)
 * of Fluid Checkout. When the customer checks "Yes! Add it to my order",
 * the product is AJAX-added to the cart and totals update in real time.
 *
 * Admin config (wp_options):
 *   - blaze_checkout_upsell_product_id  (int)  Product ID to offer (0 = disabled)
 *   - blaze_checkout_upsell_enabled     (bool) Master toggle
 *
 * Figma node: 20417:62459
 * @package Blaze_Blocksy
 * @task 86ewm9gtt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch a WC product regardless of post_status visibility.
 *
 * wc_get_product() relies on get_post() which respects WordPress post status
 * visibility — private products return null for non-admin frontend users.
 * Since the admin explicitly chose this product as the upsell, we temporarily
 * make the 'private' post status public so get_post() can find it.
 *
 * @param int $product_id Product ID.
 * @return WC_Product|false Product object or false on failure.
 */
function blaze_upsell_get_product( $product_id ) {
	global $wp_post_statuses;

	// Make 'private' status temporarily public so get_post() returns it.
	$original_public = $wp_post_statuses['private']->public;
	$wp_post_statuses['private']->public = true;

	// Clear cached post to force fresh lookup with new status visibility.
	clean_post_cache( $product_id );

	$product = wc_get_product( $product_id );

	// Restore original visibility.
	$wp_post_statuses['private']->public = $original_public;

	return $product;
}

/**
 * Get the upsell product HTML markup.
 *
 * @return string HTML markup or empty string if guards fail.
 */
function blaze_checkout_upsell_html() {
	// Guard: master toggle (WC stores checkboxes as 'yes'/'no')
	if ( ! blaze_upsell_is_enabled() ) {
		return '';
	}

	// Guard: product ID
	$product_id = absint( get_option( 'blaze_checkout_upsell_product_id', 0 ) );
	if ( ! $product_id ) {
		return '';
	}

	// Fetch product bypassing post_status visibility (handles private products).
	$product = blaze_upsell_get_product( $product_id );
	if ( ! $product || '' === $product->get_price() || ! $product->is_in_stock() || ! $product->is_type( 'simple' ) ) {
		return '';
	}

	// Guard: product not already in cart
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		if ( (int) $cart_item['product_id'] === $product_id ) {
			return '';
		}
	}

	$image_url = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
	if ( ! $image_url ) {
		$image_url = wc_placeholder_img_src( 'thumbnail' );
	}

	$name        = $product->get_name();
	$price_html  = $product->get_price_html();
	$description = wp_strip_all_tags( $product->get_short_description() );
	$nonce       = wp_create_nonce( 'blaze_upsell_nonce' );

	ob_start();
	?>
	<div class="checkout-upsell" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
		<div class="checkout-upsell__content">
			<img class="checkout-upsell__image" src="<?php echo esc_url( $image_url ); ?>" width="80" height="80" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
			<div class="checkout-upsell__text">
				<span class="checkout-upsell__title">Exclusive Offer, Just For You!</span>
				<div class="checkout-upsell__product-row">
					<span class="checkout-upsell__product-name"><?php echo esc_html( $name ); ?></span>
					<span class="checkout-upsell__price"><?php echo $price_html; ?></span>
				</div>
				<?php if ( $description ) : ?>
					<p class="checkout-upsell__description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<label class="checkout-upsell__checkbox-row">
			<input type="checkbox" class="checkout-upsell__checkbox" name="blaze_upsell_add" value="1">
			<span class="checkout-upsell__checkbox-custom"></span>
			<span class="checkout-upsell__checkbox-label">Yes! Add it to my order</span>
		</label>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Output the upsell inside Fluid Checkout's payment step.
 */
function blaze_checkout_upsell_output() {
	echo blaze_checkout_upsell_html();
}
add_action( 'fc_checkout_payment', 'blaze_checkout_upsell_output', 85 );

/**
 * AJAX handler: toggle upsell product in cart.
 */
function blaze_upsell_toggle_handler() {
	check_ajax_referer( 'blaze_upsell_nonce', 'nonce' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$action     = sanitize_text_field( $_POST['upsell_action'] ?? '' );

	// Verify the requested product matches the admin-configured upsell product.
	$configured_id = absint( get_option( 'blaze_checkout_upsell_product_id', 0 ) );
	if ( ! $product_id || $product_id !== $configured_id ) {
		wp_send_json_error( array( 'message' => 'Invalid product.' ) );
	}

	if ( 'add' === $action ) {
		$product = blaze_upsell_get_product( $product_id );
		if ( ! $product || '' === $product->get_price() || ! $product->is_in_stock() || ! $product->is_type( 'simple' ) ) {
			wp_send_json_error( array( 'message' => 'Product not available.' ) );
		}

		// Check not already in cart
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( (int) $cart_item['product_id'] === $product_id ) {
				wp_send_json_success( array( 'message' => 'Already in cart.' ) );
			}
		}

		// Temporarily force purchasable + bypass post_status for private products.
		$force_purchasable = function ( $purchasable, $p ) use ( $product_id ) {
			return ( $p->get_id() === $product_id ) ? true : $purchasable;
		};
		add_filter( 'woocommerce_is_purchasable', $force_purchasable, 10, 2 );

		global $wp_post_statuses;
		$original_public = $wp_post_statuses['private']->public;
		$wp_post_statuses['private']->public = true;

		$cart_item_key = WC()->cart->add_to_cart( $product_id, 1 );

		$wp_post_statuses['private']->public = $original_public;
		remove_filter( 'woocommerce_is_purchasable', $force_purchasable, 10 );

		if ( $cart_item_key ) {
			wp_send_json_success( array( 'message' => 'Added.', 'cart_item_key' => $cart_item_key ) );
		} else {
			wp_send_json_error( array( 'message' => 'Could not add to cart.' ) );
		}
	} elseif ( 'remove' === $action ) {
		foreach ( WC()->cart->get_cart() as $key => $cart_item ) {
			if ( (int) $cart_item['product_id'] === $product_id ) {
				WC()->cart->remove_cart_item( $key );
				wp_send_json_success( array( 'message' => 'Removed.' ) );
			}
		}
		wp_send_json_success( array( 'message' => 'Not in cart.' ) );
	} else {
		wp_send_json_error( array( 'message' => 'Invalid action.' ) );
	}
}
add_action( 'wp_ajax_blaze_upsell_toggle', 'blaze_upsell_toggle_handler' );
add_action( 'wp_ajax_nopriv_blaze_upsell_toggle', 'blaze_upsell_toggle_handler' );

/**
 * ─── WooCommerce Admin Settings: Checkout Upsell ───
 *
 * Adds a "Checkout Upsell" section under WooCommerce > Settings > Products
 * so the client can easily toggle the upsell and select the product.
 */

/**
 * Register the "Checkout Upsell" section under the Products tab.
 */
function blaze_upsell_add_section( $sections ) {
	$sections['checkout_upsell'] = __( 'Checkout Upsell', 'blaze-blocksy' );
	return $sections;
}
add_filter( 'woocommerce_get_sections_products', 'blaze_upsell_add_section' );

/**
 * Add settings fields for the Checkout Upsell section.
 */
function blaze_upsell_settings( $settings, $current_section ) {
	if ( 'checkout_upsell' !== $current_section ) {
		return $settings;
	}

	$upsell_settings = array(
		array(
			'title' => __( 'Checkout Upsell (Order Bump)', 'blaze-blocksy' ),
			'type'  => 'title',
			'desc'  => __( 'Configure the "Exclusive Offer" upsell box that appears on the checkout Payment step.', 'blaze-blocksy' ),
			'id'    => 'blaze_checkout_upsell_options',
		),
		array(
			'title'   => __( 'Enable Checkout Upsell', 'blaze-blocksy' ),
			'desc'    => __( 'Show the upsell offer on the checkout page', 'blaze-blocksy' ),
			'id'      => 'blaze_checkout_upsell_enabled',
			'type'    => 'checkbox',
			'default' => 'no',
		),
		array(
			'title'    => __( 'Upsell Product', 'blaze-blocksy' ),
			'desc'     => __( 'Search and select the product to offer as an upsell on checkout.', 'blaze-blocksy' ),
			'id'       => 'blaze_checkout_upsell_product_id',
			'type'     => 'blaze_product_search',
			'default'  => '',
			'desc_tip' => true,
		),
		array(
			'type' => 'sectionend',
			'id'   => 'blaze_checkout_upsell_options',
		),
	);

	return $upsell_settings;
}
add_filter( 'woocommerce_get_settings_products', 'blaze_upsell_settings', 10, 2 );

/**
 * Filter upsell product search to only show simple, in-stock products.
 *
 * Hooks into WC's AJAX product search results and removes any product
 * that is out of stock or not a simple product (variable, grouped, and
 * external products cannot be added to cart without variation/option selection).
 * Only active on the Checkout Upsell settings page (checked via referer).
 */
function blaze_upsell_filter_search_products( $products ) {
	$referer = wp_get_raw_referer();
	if ( ! $referer || strpos( $referer, 'section=checkout_upsell' ) === false ) {
		return $products;
	}

	foreach ( $products as $id => $name ) {
		$product = wc_get_product( $id );
		if ( ! $product || ! $product->is_in_stock() || ! $product->is_type( 'simple' ) ) {
			unset( $products[ $id ] );
		}
	}

	return $products;
}
add_filter( 'woocommerce_json_search_found_products', 'blaze_upsell_filter_search_products' );

/**
 * Render the custom product search field type.
 * Uses WooCommerce's built-in Select2 AJAX product search.
 */
function blaze_upsell_product_search_field( $value ) {
	$option_value = get_option( $value['id'], $value['default'] );
	$product_id   = absint( $option_value );
	$product_name = '';

	if ( $product_id ) {
		$product = blaze_upsell_get_product( $product_id );
		if ( $product ) {
			$product_name = sprintf(
				'%s (#%d) — %s',
				$product->get_name(),
				$product_id,
				wp_strip_all_tags( $product->get_price_html() )
			);
		}
	}

	$description = WC_Admin_Settings::get_field_description( $value );
	?>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			<?php echo $description['tooltip_html']; ?>
		</th>
		<td class="forminp forminp-blaze_product_search">
			<select
				class="wc-product-search"
				id="<?php echo esc_attr( $value['id'] ); ?>"
				name="<?php echo esc_attr( $value['id'] ); ?>"
				style="width: 400px;"
				data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'blaze-blocksy' ); ?>"
				data-action="woocommerce_json_search_products"
				data-allow_clear="true"
			>
				<?php if ( $product_id && $product_name ) : ?>
					<option value="<?php echo esc_attr( $product_id ); ?>" selected="selected"><?php echo esc_html( $product_name ); ?></option>
				<?php endif; ?>
			</select>
			<?php echo $description['description']; ?>
		</td>
	</tr>
	<?php
}
add_action( 'woocommerce_admin_field_blaze_product_search', 'blaze_upsell_product_search_field' );

/**
 * Explicitly save checkout upsell settings from $_POST.
 *
 * WC's save_fields() doesn't reliably persist our custom 'blaze_product_search'
 * field type, so we hook into the products tab save action and handle it directly.
 */
function blaze_upsell_save_settings() {
	global $current_section;
	if ( 'checkout_upsell' !== $current_section ) {
		return;
	}

	// Checkbox: present in POST only when checked.
	$enabled = isset( $_POST['blaze_checkout_upsell_enabled'] ) ? 'yes' : 'no';
	update_option( 'blaze_checkout_upsell_enabled', $enabled );

	// Product ID: from Select2 widget.
	$product_id = isset( $_POST['blaze_checkout_upsell_product_id'] ) ? absint( $_POST['blaze_checkout_upsell_product_id'] ) : 0;
	update_option( 'blaze_checkout_upsell_product_id', $product_id );
}
add_action( 'woocommerce_update_options_products', 'blaze_upsell_save_settings' );

/**
 * Check if the checkout upsell is enabled.
 * Handles both WC checkbox format ('yes'/'no') and legacy CLI-set values ('1'/1/true).
 */
function blaze_upsell_is_enabled() {
	$val = get_option( 'blaze_checkout_upsell_enabled', 'no' );
	return in_array( $val, array( 'yes', '1', 1, true ), true );
}

/**
 * Keep the upsell product purchasable on the frontend.
 *
 * Private products fail WC's is_purchasable() check because get_post() returns
 * null for non-admin users. WooCommerce validates cart items on every page load
 * and removes any product that is not purchasable. This filter ensures the
 * admin-selected upsell product stays purchasable so it isn't removed from cart
 * after the AJAX add succeeds.
 */
function blaze_upsell_force_purchasable( $purchasable, $product ) {
	if ( $purchasable ) {
		return $purchasable;
	}

	if ( ! blaze_upsell_is_enabled() ) {
		return $purchasable;
	}

	$upsell_id = absint( get_option( 'blaze_checkout_upsell_product_id', 0 ) );
	if ( $upsell_id && $product->get_id() === $upsell_id ) {
		return true;
	}

	return $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'blaze_upsell_force_purchasable', 10, 2 );
