<?php
/**
 * WooCommerce Hooks — Reusable WooCommerce filters and actions.
 *
 * Only loaded when WooCommerce is active (checked in loader.php).
 * ONLY hooks that the Blocksy Customizer cannot handle.
 *
 * IMPORTANT: Client-specific hooks go in clients/{slug}/{slug}.php.
 * Only REUSABLE hooks that apply to ANY site using this child theme belong here.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PayPal INVALID_STRING_LENGTH fix — Truncate product names to 22 characters.
 *
 * WHY: PayPal's API has an undocumented 22-character limit for item names.
 * Product names longer than 22 chars cause INVALID_STRING_LENGTH errors during
 * checkout, preventing payment. This is a PayPal API limitation — neither
 * the Blocksy Customizer nor WooCommerce settings can fix it.
 *
 * Affects: PayPal Payments plugin (woocommerce-paypal-payments).
 * Reusable: YES — any WooCommerce site using PayPal hits this limit.
 */
add_filter( 'woocommerce_paypal_payments_order_line_item_name', function ( $name, $item_id, $order_id ) {
	if ( strlen( $name ) > 22 ) {
		return substr( $name, 0, 19 ) . '...';
	}
	return $name;
}, 10, 3 );

add_filter( 'woocommerce_paypal_args', function ( $args, $order ) {
	if ( isset( $args ) && is_array( $args ) ) {
		foreach ( $args as $key => $value ) {
			if ( strpos( $key, 'item_name_' ) === 0 && strlen( $value ) > 22 ) {
				$args[ $key ] = substr( $value, 0, 19 ) . '...';
			}
		}
	}
	return $args;
}, 10, 2 );

/**
 * PDP — Render wishlist button inside the Add to Cart row.
 *
 * WHY: Blocksy fires this action via woocommerce_after_add_to_cart_button
 * but nothing in Blocksy core listens to it — it's an extension point.
 * The standalone product_actions layout element must be DISABLED in
 * Blocksy Customizer to avoid a duplicate wishlist button.
 * Reusable: YES — any Blocksy + WooCommerce site with wishlist uses this pattern.
 */
add_action( 'blocksy:pro:woo-extra:wishlist:button:output', function () {
	if ( function_exists( 'blocksy_output_add_to_wish_list' ) ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo blocksy_output_add_to_wish_list( 'single' );
	}
} );

/**
 * PDP (Bundle) — Render wishlist button inside the bundle ATC row.
 *
 * WHY: On bundle products, Blocksy's wishlist renders outside .bundle_data.
 * This hook places it INSIDE .bundle_button (alongside qty + ATC).
 * Reusable: YES — any site using WooCommerce Product Bundles + Blocksy wishlist.
 */
add_action( 'woocommerce_bundles_add_to_cart_button', function () {
	if ( function_exists( 'blocksy_output_add_to_wish_list' ) ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo blocksy_output_add_to_wish_list( 'single' );
	}
}, 20 );

/**
 * PDP — Display stock status (In Stock / Out of Stock) on single product page.
 *
 * WHY: Blocksy has no simple stock status text element.
 * Reusable: YES — any WooCommerce site benefits from visible stock status.
 */
add_action( 'woocommerce_single_product_summary', function () {
	global $product;

	if ( ! $product ) {
		return;
	}

	$in_stock = $product->is_in_stock();
	$status   = $in_stock ? __( 'In Stock', 'woocommerce' ) : __( 'Out of Stock', 'woocommerce' );
	$class    = $in_stock ? 'in-stock' : 'out-of-stock';

	echo '<p class="bc-stock-status bc-stock-' . esc_attr( $class ) . '">' . esc_html( $status ) . '</p>';
}, 11 );

/**
 * PDP — Add "Qty:" label before quantity input.
 *
 * WHY: Shows "Qty:" label next to the quantity stepper.
 * Toggle: BC_FEATURE_QTY_LABEL (default true). Set false in client PHP to disable.
 * Reusable: YES — common UX pattern.
 */
if ( ! defined( 'BC_FEATURE_QTY_LABEL' ) ) {
	define( 'BC_FEATURE_QTY_LABEL', true );
}

if ( BC_FEATURE_QTY_LABEL ) {
	add_action( 'woocommerce_before_quantity_input_field', function () {
		if ( ! is_product() ) {
			return;
		}
		echo '<span class="bc-qty-label">Qty:</span>';
	} );
}

/**
 * Product cards — Change button text to "See More →" for variable/complex products.
 *
 * WHY: Products with options should link to the product page instead of ajax add-to-cart.
 * Toggle: BC_FEATURE_SEE_MORE_BUTTON (default true).
 * Reusable: YES — common UX pattern for stores with variable products.
 */
if ( ! defined( 'BC_FEATURE_SEE_MORE_BUTTON' ) ) {
	define( 'BC_FEATURE_SEE_MORE_BUTTON', true );
}

if ( BC_FEATURE_SEE_MORE_BUTTON ) {
	/**
	 * "See More" label markup. Uses an inline SVG (Heroicons "arrow-right" 20x20)
	 * with `fill="currentColor"` so the arrow inherits the link colour. Mirrors
	 * the live site's exact paint after CU-86exduk1q (live emits the same SVG
	 * via Next.js; we replicate it on staging so the icon style/weight matches
	 * pixel-for-pixel instead of the unicode `&rarr;` we used previously).
	 *
	 * @date 2026-04-28
	 */
	$bc_see_more_label = 'See More <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="bc-see-more-arrow" width="20" height="20"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"></path></svg>';

	add_filter( 'woocommerce_loop_add_to_cart_link', function ( $link, $product, $args ) use ( $bc_see_more_label ) {
		if ( ! $product->is_type( 'simple' ) ) {
			return sprintf(
				'<a href="%s" class="%s" %s>%s</a>',
				esc_url( $product->get_permalink() ),
				esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
				isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
				$bc_see_more_label
			);
		}

		$addons = get_post_meta( $product->get_id(), '_product_addons', true );
		$global_addons = get_posts( [
			'post_type'   => 'global_product_addon',
			'numberposts' => 1,
			'fields'      => 'ids',
		] );

		if ( ( ! empty( $addons ) && is_array( $addons ) ) || ! empty( $global_addons ) ) {
			return sprintf(
				'<a href="%s" class="%s" %s>%s</a>',
				esc_url( $product->get_permalink() ),
				esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
				isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
				$bc_see_more_label
			);
		}

		return $link;
	}, 10, 3 );
}

/**
 * Mini Cart — Order Total section above the Checkout button.
 *
 * WHY: Native Blocksy/WooCommerce mini cart only emits the Subtotal row.
 * Per Figma 684:85959 (the parent off-canvas spec) and standard WC drawer
 * UX, the drawer needs a clearer totals breakdown before checkout:
 *   - Shipping: "Calculated at checkout" (when cart needs shipping)
 *   - Order Total: <total> (bold, prominent)
 *
 * Hooks into `woocommerce_widget_shopping_cart_before_buttons` which fires
 * AFTER the native Subtotal `__total` row and BEFORE the View Cart /
 * Checkout buttons. The whole `div.widget_shopping_cart_content` is a
 * cart-fragment, so values update automatically on qty change without
 * any extra JS.
 *
 * Toggle: BC_FEATURE_MINI_CART_TOTALS (default true). Set false in client
 * PHP to disable for sites that don't want the breakdown.
 *
 * Reusable: YES — common pattern for sites that need a totals breakdown
 * in the mini cart drawer.
 *
 * CU-86exbd8gx, @date 2026-04-28.
 */
if ( ! defined( 'BC_FEATURE_MINI_CART_TOTALS' ) ) {
	define( 'BC_FEATURE_MINI_CART_TOTALS', true );
}

if ( BC_FEATURE_MINI_CART_TOTALS ) {
	add_action( 'woocommerce_widget_shopping_cart_before_buttons', 'bc_render_mini_cart_totals_section', 30 );
}

function bc_render_mini_cart_totals_section() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}

	$cart           = WC()->cart;
	$needs_shipping = $cart->needs_shipping();
	$total          = $cart->get_total( 'edit' );

	echo '<div class="bc-mini-cart-totals">';

	if ( $needs_shipping ) {
		// If the cart already qualifies for free shipping (Blocksy's progress
		// bar shows "Congratulations! You got free shipping"), say "Free"
		// instead of "Calculated at checkout" — anything else feels like a
		// contradiction to the customer.
		$shipping_value = bc_mini_cart_shipping_value();

		echo '<div class="bc-mini-cart-totals__row bc-mini-cart-totals__shipping">';
		echo '<span class="bc-mini-cart-totals__label">' . esc_html__( 'Shipping', 'blocksy-child' ) . '</span>';
		echo '<span class="bc-mini-cart-totals__value">' . esc_html( $shipping_value ) . '</span>';
		echo '</div>';
	}

	echo '<div class="bc-mini-cart-totals__row bc-mini-cart-totals__total">';
	echo '<span class="bc-mini-cart-totals__label">' . esc_html__( 'Order Total', 'blocksy-child' ) . '</span>';
	echo '<span class="bc-mini-cart-totals__value">' . wp_kses_post( wc_price( $total ) ) . '</span>';
	echo '</div>';

	echo '</div>';
}

/**
 * Decide what to show in the Shipping row of the mini cart totals.
 *
 * Mirrors Blocksy Pro's shipping-progress-bar threshold logic (see
 * blocksy-companion-pro/.../shipping-progress/feature.php) so the "Shipping:
 * Free" label always agrees with the green "Congratulations! You got free
 * shipping" banner above it.
 *
 * Blocksy reads the threshold from theme mods, not from WC's zone
 * min_amount, so an earlier implementation that walked WC()->shipping()
 * rates produced a contradiction on this site (Blocksy custom limit = $100,
 * WC Australia zone min_amount = $75 → at $88 cart, banner said "Add $12
 * more" while our row said "Free").
 *
 * @return string Either "Free" or "Calculated at checkout".
 */
function bc_mini_cart_shipping_value() {
	$default = __( 'Calculated at checkout', 'blocksy-child' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $default;
	}

	$cart = WC()->cart;

	$get_mod = function ( $key, $fallback ) {
		if ( function_exists( 'blocksy_get_theme_mod' ) ) {
			return blocksy_get_theme_mod( $key, $fallback );
		}
		return get_theme_mod( $key, $fallback );
	};

	$method   = $get_mod( 'woo_count_method', 'custom' );
	$criteria = $get_mod( 'woo_custom_count_criteria', 'price' );
	$is_items = ( 'custom' === $method && 'items' === $criteria );

	if ( $is_items ) {
		$total = (int) $cart->get_cart_contents_count();
		$limit = (float) $get_mod( 'woo_count_progress_items', 2 );
	} else {
		$total = method_exists( $cart, 'get_displayed_subtotal' ) ? (float) $cart->get_displayed_subtotal() : 0.0;
		if ( method_exists( $cart, 'get_fee_total' ) ) {
			$total += (float) $cart->get_fee_total();
		}

		if ( 'woo' === $method ) {
			$limit    = 0.0;
			$packages = $cart->get_shipping_packages();
			$package  = reset( $packages );
			if ( $package && function_exists( 'wc_get_shipping_zone' ) ) {
				$zone = wc_get_shipping_zone( $package );
				foreach ( $zone->get_shipping_methods( true ) as $m ) {
					if ( 'free_shipping' === $m->id && $m->get_option( 'min_amount' ) ) {
						$limit = (float) $m->get_option( 'min_amount' );
					}
				}
			}
		} else {
			$limit = (float) $get_mod( 'woo_count_progress_amount', 100 );
		}
	}

	$coupon_free = false;
	if ( $cart->get_coupons() ) {
		$count_with_discount = $get_mod( 'woo_count_with_discount', 'yes' );
		foreach ( $cart->get_coupons() as $coupon ) {
			if ( method_exists( $coupon, 'get_free_shipping' ) && $coupon->get_free_shipping() ) {
				$coupon_free = true;
				break;
			}
			if ( ! $is_items && 'yes' === $count_with_discount ) {
				$total -= (float) $cart->get_coupon_discount_amount( $coupon->get_code(), $cart->display_cart_ex_tax );
			}
		}
	}

	if ( $coupon_free || ( $limit > 0 && $total >= $limit ) ) {
		return __( 'Free', 'blocksy-child' );
	}

	return $default;
}

/**
 * Mini cart secure-checkout trust badge — CU-86exbd8pp.
 *
 * Renders a small lock icon + "Secure Checkout · 256-bit SSL Encryption"
 * strip directly below the View Cart / Checkout buttons in the off-canvas
 * cart drawer. Pattern mirrors the Austin Natural Mattress reference.
 *
 * Hook: woocommerce_widget_shopping_cart_after_buttons (fires inside the
 * cart-fragments AJAX response, so it auto-refreshes with qty/remove.)
 *
 * Toggle: BC_FEATURE_MINI_CART_SECURE_BADGE (default true).
 */
if ( ! defined( 'BC_FEATURE_MINI_CART_SECURE_BADGE' ) ) {
	define( 'BC_FEATURE_MINI_CART_SECURE_BADGE', true );
}

if ( BC_FEATURE_MINI_CART_SECURE_BADGE ) {
	add_action( 'woocommerce_widget_shopping_cart_after_buttons', 'bc_render_mini_cart_secure_badge', 20 );
}

function bc_render_mini_cart_secure_badge() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	?>
	<div class="bc-mini-cart-secure" aria-label="<?php esc_attr_e( 'Secure checkout — 256-bit SSL encryption', 'blocksy-child' ); ?>">
		<svg class="bc-mini-cart-secure__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
			<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
			<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
		</svg>
		<span class="bc-mini-cart-secure__text">
			<?php esc_html_e( 'Secure Checkout', 'blocksy-child' ); ?>
			<span class="bc-mini-cart-secure__sep" aria-hidden="true"> · </span>
			<?php esc_html_e( '256-bit SSL Encryption', 'blocksy-child' ); ?>
		</span>
	</div>
	<?php
}

/**
 * Mini cart drawer heading — CU-86exbe71m.
 *
 * Replaces Blocksy Companion Pro's default "Shopping Cart" heading with the
 * Figma-spec "Your bag (X)" pattern: shopping-bag icon + title + item count
 * in parentheses. Mirrors the structure used by our wishlist drawer in
 * inc/wishlist-offcanvas.php.
 *
 * Hook: gettext_blocksy-companion (text-domain-scoped, narrowest possible).
 * Source string lives at:
 *   wp-content/plugins/blocksy-companion-pro/framework/premium/extensions/
 *   woocommerce-extra/features/offcanvas-cart/feature.php:180
 *
 * Count is rendered initially server-side and live-updated via the
 * cart-offcanvas.js helper which listens for `wc_fragments_refreshed`.
 *
 * Toggle: BC_FEATURE_CART_PANEL_HEADING (default true).
 */
if ( ! defined( 'BC_FEATURE_CART_PANEL_HEADING' ) ) {
	define( 'BC_FEATURE_CART_PANEL_HEADING', true );
}

if ( BC_FEATURE_CART_PANEL_HEADING ) {
	add_filter( 'gettext_blocksy-companion', 'bc_filter_cart_panel_heading', 10, 3 );
}

/**
 * Bump FiboSearch mobile-overlay breakpoint above Blocksy integration's
 * hardcoded 689 — CU-86exbd0gr V3.
 *
 * FiboSearch's Blocksy theme integration (in
 * `ajax-search-for-woocommerce-premium/includes/Integrations/Themes/
 * ThemesCompatibility.php` line 334) forces `forceMobileOverlayBreakpoint =>
 * 689` regardless of the option value. We want the overlay to fire on tablet
 * (768-1023) too, matching the Austin Natural Mattress pattern, so we filter
 * at higher priority (11 vs default 10) to win.
 *
 * Toggle: BC_FEATURE_SEARCH_OVERLAY_BREAKPOINT (default 1099). Set to 0 in
 * client PHP to revert to the plugin's Blocksy default (689).
 */
if ( ! defined( 'BC_FEATURE_SEARCH_OVERLAY_BREAKPOINT' ) ) {
	define( 'BC_FEATURE_SEARCH_OVERLAY_BREAKPOINT', 1099 );
}

if ( BC_FEATURE_SEARCH_OVERLAY_BREAKPOINT > 0 ) {
	add_filter( 'dgwt/wcas/settings/load_value/key=mobile_overlay_breakpoint', 'bc_filter_search_overlay_breakpoint', 11 );
	add_filter( 'dgwt/wcas/scripts/mobile_overlay_breakpoint', 'bc_filter_search_overlay_breakpoint', 11 );
}

function bc_filter_search_overlay_breakpoint() {
	return BC_FEATURE_SEARCH_OVERLAY_BREAKPOINT;
}

function bc_filter_cart_panel_heading( $translation, $text, $domain ) {
	if ( 'Shopping Cart' !== $text ) {
		return $translation;
	}

	$count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;

	// Same custom shopping-bag icon used by the header cart trigger. Solid-fill,
	// not stroked. fill=currentColor so the icon inherits the heading text color.
	// This is the DEFAULT; the "Off-canvas Panel Icons" Customizer control
	// (inc/offcanvas-icons.php) overrides it when an admin uploads a drawer icon.
	$icon = '<svg class="ct-icon ct-cart-panel-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M14.6248 5.5C14.6248 3.84315 13.2816 2.5 11.6248 2.5C9.96813 2.50026 8.62476 3.84331 8.62476 5.5V6.25H14.6248V5.5ZM5.13844 7.75C4.94653 7.75 4.78548 7.89508 4.76539 8.08594L3.50172 20.0859C3.47855 20.3072 3.65226 20.4999 3.87476 20.5H19.3757C19.5982 20.4999 19.772 20.3072 19.7488 20.0859L18.4851 8.08594C18.465 7.89508 18.304 7.75 18.1121 7.75H16.1248V9.16211C16.3549 9.3681 16.4998 9.66684 16.4998 10C16.4998 10.6213 15.9961 11.125 15.3748 11.125C14.7537 11.1247 14.2498 10.6212 14.2498 10C14.2498 9.66733 14.3952 9.36905 14.6248 9.16309V7.75H8.62476V9.16211C8.85489 9.3681 8.99976 9.66684 8.99976 10C8.99976 10.6213 8.49608 11.125 7.87476 11.125C7.25367 11.1247 6.74976 10.6212 6.74976 10C6.74976 9.66733 6.89523 9.36905 7.12476 9.16309V7.75H5.13844ZM16.1248 6.25H18.1121C19.0716 6.25 19.8769 6.97444 19.9773 7.92871L21.24 19.9287C21.3565 21.0357 20.4889 21.9999 19.3757 22H3.87476C2.76163 21.9999 1.89398 21.0357 2.01051 19.9287L3.2732 7.92871C3.37365 6.97444 4.17889 6.25 5.13844 6.25H7.12476V5.5C7.12476 3.01488 9.13971 1.00026 11.6248 1C14.11 1 16.1248 3.01472 16.1248 5.5V6.25Z" fill="currentColor"/></svg>';

	if ( function_exists( 'blocksy_child_offcanvas_icon_markup' ) ) {
		$custom = blocksy_child_offcanvas_icon_markup( 'minicart', 'ct-cart-panel-icon' );

		if ( '' !== $custom ) {
			$icon = $custom;
		}
	}

	return $icon
		. ' <span class="ct-cart-panel-title">' . esc_html__( 'Your bag', 'blocksy-child' ) . '</span>'
		. ' <span class="ct-cart-panel-count">(<span class="ct-cart-count-number">' . esc_html( (string) $count ) . '</span>)</span>';
}

/* 86extrx5y #6 — Archive cards must open the product page, never ajax add-to-cart (client request 2026-05-28). Priority 20 runs after the See More filter (10); covers simple products that previously fell through to the native ajax button. Remove this block to revert. */
add_filter( 'woocommerce_loop_add_to_cart_link', function ( $link, $product, $args ) {
	$bc_more = 'See More <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="bc-see-more-arrow" width="20" height="20"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"></path></svg>';
	return sprintf( '<a href="%s" class="button bc-see-more-link">%s</a>', esc_url( $product->get_permalink() ), $bc_more );
}, 20, 3 );

/**
 * Hide the WooCommerce "Free Shipping" method at checkout for wholesale customers.
 *
 * Migrated 2026-06-04 from Code Snippets #11 ("Hide Free Shipping for Wholesale") into the child theme
 * so the Code Snippets plugin can be removed, then hardened to BC child-theme conventions:
 *   - function_exists() guard       → no fatal redeclaration if the old snippet is ever still active.
 *   - master on/off filter          → `bbc_hide_free_shipping_for_wholesale_enabled` (default true);
 *                                      return false anywhere (mu-plugin/snippet) to disable site-wide
 *                                      WITHOUT editing the theme. Mirrors bbc_smart_coupons_cart_css_active().
 *   - dependency-safe               → only runs inside a real WooCommerce shipping calc; if B2B Suite or
 *                                      the roles are gone the role match simply finds nothing → no-op,
 *                                      no errors. Nothing here hard-depends on B2B Suite being active.
 *   - portable / configurable roles → target roles via `bbc_free_shipping_hidden_roles` so a site with
 *                                      different B2B Suite role IDs can override without touching the theme
 *                                      (role post-IDs differ per environment — verified sitebuild vs live).
 *
 * @param array $rates   WC_Shipping_Rate[] keyed by rate id.
 * @param array $package The shipping package.
 * @return array Filtered rates.
 */
if ( ! function_exists( 'bbc_hide_free_shipping_for_wholesale' ) ) {
	function bbc_hide_free_shipping_for_wholesale( $rates, $package ) {
		// 1. Master on/off switch (default ON). Turn off site-wide without editing the theme.
		if ( ! apply_filters( 'bbc_hide_free_shipping_for_wholesale_enabled', true ) ) {
			return $rates;
		}

		// 2. Only act for a logged-in front-end customer in a real Woo context.
		if ( ! function_exists( 'WC' ) || is_admin() || ! is_user_logged_in() ) {
			return $rates;
		}

		// 3. Wholesale role slugs that should NOT see Free Shipping (documented by display name).
		//    Filterable so each environment / future role change can override without a code edit.
		$hidden_roles = apply_filters( 'bbc_free_shipping_hidden_roles', array(
			'b2bwhs_role_642389', // VIP wholesale local
			'b2bwhs_role_637532', // Commission Base
			'b2bwhs_role_632083', // Local Wholesale
			'b2bwhs_role_631619', // Distributor (absent on some envs — harmless if missing)
			'b2bwhs_role_631578', // Wholesale
		) );
		if ( empty( $hidden_roles ) ) {
			return $rates;
		}

		// 4. Bail unless the current user holds one of the targeted wholesale roles.
		$user = wp_get_current_user();
		if ( ! $user || empty( $user->roles ) || ! array_intersect( (array) $user->roles, $hidden_roles ) ) {
			return $rates;
		}

		// 5. Remove every Free Shipping rate from the package.
		foreach ( $rates as $rate_id => $rate ) {
			if ( isset( $rate->method_id ) && 'free_shipping' === $rate->method_id ) {
				unset( $rates[ $rate_id ] );
			}
		}

		return $rates;
	}
}
add_filter( 'woocommerce_package_rates', 'bbc_hide_free_shipping_for_wholesale', 10, 2 );
