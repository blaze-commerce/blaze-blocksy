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
 *   - The Subscribe & Save badge renders only when a SINGLE product carries
 *     both a one-time and a subscription price on itself (a native WC
 *     Subscriptions product with its own regular price, or a WCS-ATT scheme
 *     product). Verified live on bonza-retheme.blz.au 7 Sep 2026: Bonza's
 *     actual "subscribe and save" catalog shape is TWIN PRODUCTS instead —
 *     e.g. `boost-bioactive-bites-otp` (post 132462, one-time, £29) and
 *     `daily-multivitamin-dogs` (post 131519, native variable-subscription,
 *     From £21.60) are two separate posts with no shared SKU prefix or
 *     product-meta linkage, sharing only a near-identical display name.
 *     `WCS_ATT_Product_Schemes::get_subscription_schemes()` returns an empty
 *     array for the subscription twin (WCS-ATT is not what creates it), and
 *     its own `get_regular_price()` is empty (only its variations carry a
 *     price), so neither function below can compute the real £7.40 saving —
 *     that number only exists by comparing two different product objects,
 *     and there is today no reliable rule (naming, SKU, meta) to pair them.
 *     The badge therefore does NOT render anywhere in Bonza's current
 *     catalog. This is a deliberate deferral, not a bug: see CU-86eyuup3c
 *     for the options (define a real OTP<->subscription linkage, or accept
 *     no badge) once the client decides which.
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
 * Bonza runs "All Products for WooCommerce Subscriptions" (WCS-ATT), which
 * adds a subscribe-and-save SCHEME (usually a percentage discount) to an
 * otherwise simple/variable product, rather than making the product itself
 * a native WooCommerce Subscriptions product. Checking only
 * WC_Subscriptions_Product::is_subscription() would never match a WCS-ATT
 * product, so the badge could never render on this site. This checks
 * WCS-ATT's own scheme data first and falls back to a native subscription
 * product for any site that has one. Every step is guarded with
 * method_exists()/is_callable(), so a plugin-version API mismatch produces
 * an empty string (no badge), never a fatal or a guessed discount.
 *
 * @param WC_Product $product Product.
 * @return string Badge text (e.g. "Subscribe & Save £2.25 per delivery"), or ''.
 */
function blocksy_child_wishlist_card_subscribe_badge( $product ) {
	$saving = blocksy_child_wishlist_card_wcsatt_saving( $product );

	if ( null === $saving ) {
		$saving = blocksy_child_wishlist_card_native_subscription_saving( $product );
	}

	if ( null === $saving || $saving <= 0 ) {
		return '';
	}

	return sprintf(
		/* translators: %s: saving amount, formatted as a price. */
		__( 'Subscribe & Save %s per delivery', 'blocksy-child' ),
		wp_strip_all_tags( wc_price( $saving ) )
	);
}

/**
 * Saving amount from a WCS-ATT (All Products for Subscriptions) scheme.
 *
 * Not exercised by anything in Bonza's live catalog today (verified 7 Sep
 * 2026: `get_subscription_schemes()` returns an empty array for every
 * product checked there), so this has never run against real scheme data
 * in this project. It reads `get_discount()` first (the accessor a
 * WCS_ATT_Scheme object is documented to expose) and only falls back to
 * guessing an array/property shape if that method is absent, and only
 * applies the percentage for a scheme whose pricing mode is NOT 'override'
 * (an override scheme sets its own fixed price instead of a % off, so
 * treating its discount value as a percentage would be wrong). Every step
 * stays guarded with method_exists()/is_callable() so an API mismatch
 * yields null (no badge), never a fatal or a guessed number.
 *
 * @param WC_Product $product Product.
 * @return float|null Saving amount, or null when WCS-ATT is not active, the
 *                     product has no scheme, the scheme uses fixed
 *                     ('override') pricing, or no discount percentage could
 *                     be read.
 */
function blocksy_child_wishlist_card_wcsatt_saving( $product ) {
	if ( ! class_exists( 'WCS_ATT_Product_Schemes' ) || ! method_exists( 'WCS_ATT_Product_Schemes', 'get_subscription_schemes' ) ) {
		return null;
	}

	$schemes = WCS_ATT_Product_Schemes::get_subscription_schemes( $product );

	if ( empty( $schemes ) || ! is_array( $schemes ) ) {
		return null;
	}

	$scheme = reset( $schemes );

	if ( is_object( $scheme ) && method_exists( $scheme, 'get_pricing_mode' ) ) {
		if ( 'override' === $scheme->get_pricing_mode() ) {
			return null;
		}
	}

	$percent = null;

	if ( is_object( $scheme ) && method_exists( $scheme, 'get_discount' ) ) {
		$percent = (float) $scheme->get_discount();
	} elseif ( is_object( $scheme ) && method_exists( $scheme, 'get_data' ) ) {
		$data    = $scheme->get_data();
		$percent = isset( $data['subscription_discount'] ) ? (float) $data['subscription_discount'] : ( isset( $data['discount'] ) ? (float) $data['discount'] : null );
	} elseif ( is_array( $scheme ) ) {
		$percent = isset( $scheme['subscription_discount'] ) ? (float) $scheme['subscription_discount'] : ( isset( $scheme['discount'] ) ? (float) $scheme['discount'] : null );
	}

	if ( null === $percent || $percent <= 0 ) {
		return null;
	}

	$price = (float) $product->get_price();

	if ( $price <= 0 ) {
		return null;
	}

	return $price * ( $percent / 100 );
}

/**
 * Saving amount for a native WooCommerce Subscriptions product, comparing
 * its own regular price against its active subscription price.
 *
 * Only ever fires for a SINGLE product that carries both prices on itself.
 * Verified live 7 Sep 2026: Bonza's `variable-subscription` products (e.g.
 * `daily-multivitamin-dogs`) return an empty `get_regular_price()` on the
 * parent post (only their variations have a price), so `$regular_price`
 * below is 0 and this returns null for every one of them today - correctly
 * showing no badge rather than a wrong number, but it cannot see the
 * separate one-time-purchase twin product Bonza actually sells alongside
 * it (see the file header note). Fixing that needs a real product-to-
 * product linkage, not a change to this function.
 *
 * @param WC_Product $product Product.
 * @return float|null Saving amount, or null when not applicable.
 */
function blocksy_child_wishlist_card_native_subscription_saving( $product ) {
	if ( ! class_exists( 'WC_Subscriptions_Product' ) || ! WC_Subscriptions_Product::is_subscription( $product ) ) {
		return null;
	}

	$sub_price     = (float) WC_Subscriptions_Product::get_price( $product );
	$regular_price = (float) $product->get_regular_price();

	if ( $sub_price <= 0 || $regular_price <= $sub_price ) {
		return null;
	}

	return $regular_price - $sub_price;
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

	// Wrapped in its own inner element (not left to the caller's own
	// wrapper) so the card's rounded-corner/overflow:hidden chrome lives
	// here, never on the caller's own wrapper (the wishlist item <li> or
	// the suggested-card div). Those wrappers also hold the "Remove"
	// control in the item context; clipping at the caller level would cut
	// off its focus ring.
	$html  = '<div class="ct-wishlist-card-inner">';
	$html .= '<a href="' . esc_url( $permalink ) . '" class="ct-wishlist-card-media">' . $image . '</a>';
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
