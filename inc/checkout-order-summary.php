<?php
/**
 * Checkout Order Summary — FC Pro hooks, CSS/JS enqueue, free shipping bar.
 *
 * Handles:
 *   - Mobile position: forces before_checkout_steps (native FC Pro collapsible toggle)
 *   - Toggle text: "Show Order Summary" / "Hide Order Summary"
 *   - Title: "Order Summary"
 *   - Item count badge: pill badge appended after order review title
 *   - Coupon position: inside_order_summary
 *   - Coupon toggle label + input placeholder
 *   - Remove link: trash SVG icon
 *   - Variation name separator
 *   - Free shipping progress bar
 *   - CSS/JS enqueue: checkout page only
 *
 * Figma: Byron Bay Candles node 6054-109813
 * ClickUp: 86ewn0gw9
 * Reference: ANM implementation doc 86ewyrrcm (adapted for BBC)
 *
 * @refactored 2026-05-08 — moved from custom/ to inc/ (Layer 1/Layer 2 architecture compliance)
 * @package Blocksy_Child
 * @date    2026-04-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FluidCheckout' ) ) {
	return;
}

/**
 * Disable Blocksy Companion Pro's Suggested Products on checkout.
 *
 * WHY: Figma checkout spec does not include a suggested products carousel
 * in the order summary sidebar. Disabling via theme_mod filter prevents
 * the render callback, JS bundle, and CSS from loading on checkout pages.
 * @date 2026-04-23
 */
add_filter( 'theme_mod_checkout_suggested_products', function () {
	return 'no';
} );

/**
 * Disable Blocksy Companion Pro's Free Shipping Progress Bar on checkout.
 *
 * WHY: Figma checkout spec (6054:68281) shows Subtotal → Shipping → Tax → Total
 * with no progress bar. The bar is a Blocksy Companion Pro extension feature
 * (woocommerce-extra/shipping-progress), not our custom code.
 * @date 2026-04-23
 */
add_filter( 'theme_mod_woo_shipping_progress_in_checkout', function () {
	return 'no';
} );

// ─── Enqueue CSS + JS on checkout only ───────────────────────────────────────

// Enqueues are centralized in inc/enqueue.php (refactored 2026-05-08, audit P1).

// ─── FC Pro: Force mobile order summary position ─────────────────────────────

add_filter( 'option_fc_pro_checkout_order_summary_position_mobile', function () {
	return 'before_checkout_steps';
} );

// ─── FC: Order review title text ─────────────────────────────────────────────

add_filter( 'fc_order_review_title', function () {
	return __( 'Order Summary', 'blocksy-child' );
} );

// ─── FC Pro: Collapsible toggle title text ───────────────────────────────────

add_filter( 'fc_pro_checkout_order_summary_collapsible_toggle_title_text', function ( $text, $cart_items_count ) {
	return __( 'Show Order Summary', 'blocksy-child' );
}, 10, 2 );

// ─── FC Pro: Coupon codes inside order summary ───────────────────────────────
// Priority 20 to override FC Lite's pre_option_ at priority 10.

add_filter( 'pre_option_fc_pro_checkout_coupon_codes_position', function () {
	return 'inside_order_summary';
}, 20 );

// ─── FC: Coupon toggle label ─────────────────────────────────────────────────

add_filter( 'fc_expansible_section_toggle_label_coupon_code', function () {
	return __( 'Coupon Code/Gift Voucher', 'blocksy-child' );
} );

// ─── FC: Coupon input placeholder ────────────────────────────────────────────

add_filter( 'fc_coupon_code_field_placeholder', function () {
	return __( 'Enter promo code', 'blocksy-child' );
} );

// ─── WooCommerce: Remove link → trash SVG icon ──────────────────────────────

add_filter( 'woocommerce_cart_item_remove_link', function ( $remove_link, $cart_item_key ) {
	if ( ! is_checkout() ) {
		return $remove_link;
	}

	$trash_svg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>';

	// Replace inner text/content of the <a> tag with the SVG icon.
	$remove_link = preg_replace(
		'/(<a\b[^>]*>).*?(<\/a>)/s',
		'$1' . $trash_svg . '<span class="screen-reader-text">' . esc_html__( 'Remove item', 'blocksy-child' ) . '</span>$2',
		$remove_link
	);

	return $remove_link;
}, 10, 2 );

// ─── WooCommerce: Separate product name from variation ───────────────────────

add_filter( 'woocommerce_cart_item_name', function ( $name, $cart_item, $cart_item_key ) {
	if ( ! is_checkout() ) {
		return $name;
	}

	$product = $cart_item['data'];
	if ( ! $product instanceof WC_Product_Variation ) {
		return $name;
	}

	$parent_id   = $product->get_parent_id();
	$parent      = wc_get_product( $parent_id );
	$parent_name = $parent ? $parent->get_name() : $name;

	$variation_attrs = $product->get_variation_attributes();
	$attr_parts      = [];
	foreach ( $variation_attrs as $attr_key => $attr_value ) {
		if ( $attr_value ) {
			$taxonomy = str_replace( 'attribute_', '', $attr_key );
			$term     = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $attr_value, $taxonomy ) : false;
			$attr_parts[] = $term ? $term->name : ucfirst( $attr_value );
		}
	}

	if ( empty( $attr_parts ) ) {
		return $name;
	}

	return $parent_name . '<dl class="variation"><dd>' . esc_html( implode( ', ', $attr_parts ) ) . '</dd></dl>';
}, 10, 3 );

// ─── FC: Item count badge after review title (desktop) ───────────────────────

add_action( 'fc_checkout_after_order_review_title_after', function () {
	$cart = WC()->cart;
	if ( ! $cart ) {
		return;
	}
	$count = 0;
	foreach ( $cart->get_cart() as $item ) {
		$count += (int) $item['quantity'];
	}
	$label = sprintf(
		_n( '%d Item', '%d Items', $count, 'blocksy-child' ),
		$count
	);
	echo '<span class="bc-os-item-count">' . esc_html( $label ) . '</span>';
} );

// ─── WooCommerce: Mobile order summary header + badge ────────────────────────

add_action( 'woocommerce_checkout_order_review', function () {
	$cart  = WC()->cart;
	$count = 0;
	if ( $cart ) {
		foreach ( $cart->get_cart() as $item ) {
			$count += (int) $item['quantity'];
		}
	}
	$label = sprintf(
		_n( '%d Item', '%d Items', $count, 'blocksy-child' ),
		$count
	);
	echo '<div class="bc-mobile-order-summary-header">';
	echo '<h3 class="bc-mobile-order-summary-heading">' . esc_html__( 'Order Summary', 'blocksy-child' ) . '</h3>';
	echo '<span class="bc-os-item-count">' . esc_html( $label ) . '</span>';
	echo '</div>';
}, 5 );

