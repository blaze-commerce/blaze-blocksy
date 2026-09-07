<?php
/**
 * Wishlist / suggested-products PRODUCT CARD. CU-86eyuup3c.
 *
 * Figma's Wishlist component set (68:34939) reuses a shared "PRODUCT CARD"
 * component (29089:54366) inside both the wishlist item cards and the
 * "You May Also Like" suggested row, with only these componentProperties
 * visible in this context: category, Show Subheadline, Regular price. Star
 * Rating, description, the pill/subscription-frequency row, Add to cart,
 * New/Best Seller/Sale badges, and the Wishlist heart are all off.
 *
 * Off by default for every site. Opt in per usage the same way the rest of
 * this file's card-layout features do:
 *   blocksy_child_wishlist_card_layout                    (item cards)
 *   blocksy_child_wishlist_suggested_uses_product_cards   (suggested row)
 * Byron Bay, AlternateWorlds and The Natural Mattress stay on their current
 * layout unless they opt in.
 *
 * Data sourcing:
 *   - Category pills read product_cat terms. A site can switch this to
 *     product_tag via the blocksy_child_wishlist_card_pill_taxonomy filter.
 *   - Subheadline reads the product's short description (WooCommerce's own
 *     "Product short description" field, stripped of markup). This reuses
 *     an already-editable WP field rather than adding a new one.
 *   - The Subscribe & Save badge renders only when WooCommerce Subscriptions
 *     is active and the product has a real subscription discount to show.
 *     Most products have no such discount configured today, see
 *     CU-86eyuup3c's data-gap note, and get no badge until that changes.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category (or tag) pill labels for a product.
 *
 * @param WC_Product $product Product.
 * @return string[] Term names.
 */
function blocksy_child_wishlist_card_pill_labels( $product ) {
	$taxonomy = apply_filters( 'blocksy_child_wishlist_card_pill_taxonomy', 'product_cat' );
	$terms    = get_the_terms( $product->get_id(), $taxonomy );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return [];
	}

	return wp_list_pluck( $terms, 'name' );
}

/**
 * Category/tag pill row markup. Empty string when the product has no terms.
 *
 * @param WC_Product $product Product.
 * @return string Pills row HTML, or ''.
 */
function blocksy_child_wishlist_card_pills_html( $product ) {
	$labels = blocksy_child_wishlist_card_pill_labels( $product );

	if ( empty( $labels ) ) {
		return '';
	}

	$pills = '';

	foreach ( $labels as $label ) {
		$pills .= '<span class="ct-wishlist-card-pill">' . esc_html( $label ) . '</span>';
	}

	return '<div class="ct-wishlist-card-pills">' . $pills . '</div>';
}

/**
 * Subscribe & Save badge text for a product. Empty string when not applicable.
 *
 * Requires a real WooCommerce Subscriptions product with both a one-time
 * price and a subscription price to compare. A missing comparison price
 * produces an empty string, never a guessed discount.
 *
 * @param WC_Product $product Product.
 * @return string Badge text (e.g. "Subscribe & Save £2.25 per delivery"), or ''.
 */
function blocksy_child_wishlist_card_subscribe_badge( $product ) {
	if ( ! class_exists( 'WC_Subscriptions_Product' ) || ! WC_Subscriptions_Product::is_subscription( $product ) ) {
		return '';
	}

	$sub_price = (float) WC_Subscriptions_Product::get_price( $product );

	if ( $sub_price <= 0 ) {
		return '';
	}

	$regular_price = (float) $product->get_regular_price();

	if ( $regular_price <= $sub_price ) {
		return '';
	}

	$saving = $regular_price - $sub_price;

	return sprintf(
		/* translators: %s: saving amount, formatted as a price. */
		__( 'Subscribe & Save %s per delivery', 'blocksy-child' ),
		wp_strip_all_tags( wc_price( $saving ) )
	);
}

/**
 * Render the shared wishlist/suggested PRODUCT CARD markup for one product.
 *
 * Figma places the "Remove" control below the card, as its own element, in
 * the wishlist item context only. This function renders the card alone;
 * a caller that needs a remove control appends its own after it.
 *
 * @param WC_Product $product Product to render.
 * @return string Card HTML. Empty string if the product is invalid.
 */
function blocksy_child_wishlist_product_card_html( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}

	$name        = $product->get_name();
	$permalink   = $product->get_permalink();
	$image       = $product->get_image( blocksy_child_wishlist_image_size(), [ 'class' => 'ct-wishlist-card-image' ] );
	$pills       = blocksy_child_wishlist_card_pills_html( $product );
	$subheadline = wp_strip_all_tags( $product->get_short_description() );
	$badge       = blocksy_child_wishlist_card_subscribe_badge( $product );

	$html  = '<a href="' . esc_url( $permalink ) . '" class="ct-wishlist-card-media">' . $image . '</a>';
	$html .= '<div class="ct-wishlist-card-info">';
	$html .= '<a href="' . esc_url( $permalink ) . '" class="ct-wishlist-card-name">' . esc_html( $name ) . '</a>';
	$html .= $pills;

	if ( '' !== $subheadline ) {
		$html .= '<p class="ct-wishlist-card-subheadline">' . esc_html( $subheadline ) . '</p>';
	}

	$html .= '<hr class="ct-wishlist-card-divider" />';
	$html .= '<div class="ct-wishlist-card-price">' . $product->get_price_html() . '</div>';

	if ( '' !== $badge ) {
		$html .= '<span class="ct-wishlist-card-badge">' . esc_html( $badge ) . '</span>';
	}

	$html .= '</div>';

	return $html;
}

/**
 * Render the "You May Also Like" suggested-products row as a static grid of
 * PRODUCT CARDs, in place of Blocksy's own carousel template.
 *
 * Off by default (blocksy_child_wishlist_suggested_uses_product_cards
 * filter). Byron Bay, AlternateWorlds and The Natural Mattress keep
 * Blocksy's stock suggested-products carousel untouched.
 *
 * @param int[] $product_ids Product IDs to render.
 * @return string Grid HTML (heading and grid). Empty string with no valid products.
 */
function blocksy_child_render_wishlist_suggested_product_cards( $product_ids ) {
	$cards = '';

	foreach ( $product_ids as $product_id ) {
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			continue;
		}

		$cards .= '<div class="ct-wishlist-suggested-card">' . blocksy_child_wishlist_product_card_html( $product ) . '</div>';
	}

	if ( '' === $cards ) {
		return '';
	}

	return '<div class="ct-module-title">' . esc_html__( 'You May Also Like', 'blocksy-child' ) . '</div>'
		. '<div class="ct-wishlist-suggested-grid">' . $cards . '</div>';
}
