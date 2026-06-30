<?php
/**
 * Off-Canvas Wishlist Panel — Slide-out drawer for wishlist items.
 *
 * Client-side rendering: PHP preloads wishlist product data as JSON,
 * JS renders the panel content instantly without AJAX round-trips.
 * Panel styling dynamically mirrors the cart panel from Blocksy Customizer.
 *
 * WHY: Blocksy has NO built-in off-canvas wishlist panel.
 * Pattern source: Blocksy OffcanvasCart + Zephyr site build.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'blc_get_ext' ) || ! blc_get_ext( 'woocommerce-extra' ) ) {
	return;
}

/**
 * Whether the wishlist drawer uses the Figma "card grid" layout.
 *
 * OFF by default — every existing site (Byron Bay) keeps the original
 * list-row drawer byte-for-byte. A site opts in with:
 *   add_filter( 'blocksy_child_wishlist_card_layout', '__return_true' );
 * Alternate Worlds enables it in its custom/ overlay. This single gate
 * governs the card item template, the portrait product image size, and
 * the empty-state category grid below.
 *
 * @return bool
 */
function blocksy_child_wishlist_uses_cards() {
	return (bool) apply_filters( 'blocksy_child_wishlist_card_layout', false );
}

/**
 * Image size used for wishlist drawer thumbnails.
 *
 * Card layout shows a full portrait product cover (`woocommerce_single`, the
 * uncropped product image — `woocommerce_thumbnail` is a hard square crop that
 * lops the top/bottom off portrait comic covers); the list-row layout keeps
 * the small square `woocommerce_gallery_thumbnail` it always used.
 *
 * @return string
 */
function blocksy_child_wishlist_image_size() {
	return blocksy_child_wishlist_uses_cards() ? 'woocommerce_single' : 'woocommerce_gallery_thumbnail';
}

/**
 * Render the wishlist off-canvas panel shell into the footer.
 * Content is empty — JS fills it from preloaded data.
 */
add_filter( 'blocksy:footer:offcanvas-drawer', function ( $elements, $payload ) {
	if ( $payload['location'] !== 'start' ) {
		return $elements;
	}
	$elements[] = blocksy_child_render_wishlist_panel();
	return $elements;
}, 10, 2 );

/**
 * Output preloaded wishlist product data + cart panel CSS in footer.
 */
add_action( 'wp_footer', function () {
	// --- Preload wishlist product data as JSON ---
	$wish_list_ext = blc_get_ext( 'woocommerce-extra' )->get_wish_list();
	$items_data    = [];

	if ( $wish_list_ext ) {
		$items = $wish_list_ext->get_current_wish_list();

		foreach ( $items as $item ) {
			$product_id = isset( $item['id'] ) ? (int) $item['id'] : 0;
			$product    = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			$items_data[] = [
				'id'    => $product_id,
				'name'  => $product->get_name(),
				'url'   => $product->get_permalink(),
				'image' => $product->get_image( blocksy_child_wishlist_image_size() ),
				'price' => $product->get_price_html(),
			];
		}
	}

	$preload = [
		'items'      => $items_data,
		'isGuest'    => ! is_user_logged_in(),
		'accountUrl' => wc_get_page_permalink( 'myaccount' ),
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'cardLayout' => blocksy_child_wishlist_uses_cards(),
	];

	echo '<script id="bc-wishlist-data">var bcWishlistData = ' . wp_json_encode( $preload ) . ';</script>';

	// --- Mirror cart panel CSS onto wishlist panel ---
	$placements = get_theme_mod( 'header_placements' );
	$cart_atts  = [];

	if ( ! empty( $placements['sections'] ) ) {
		foreach ( $placements['sections'] as $section ) {
			if ( empty( $section['items'] ) ) {
				continue;
			}
			foreach ( $section['items'] as $item ) {
				if ( $item['id'] === 'cart' && isset( $item['values'] ) ) {
					$cart_atts = $item['values'];
					break 2;
				}
			}
		}
	}

	// Width.
	$width     = isset( $cart_atts['cart_panel_width'] ) ? $cart_atts['cart_panel_width'] : null;
	$desktop_w = '500px';
	$tablet_w  = '65vw';
	$mobile_w  = '90vw';

	if ( is_array( $width ) ) {
		$desktop_w = isset( $width['desktop'] ) ? $width['desktop'] : $desktop_w;
		$tablet_w  = isset( $width['tablet'] ) ? $width['tablet'] : $tablet_w;
		$mobile_w  = isset( $width['mobile'] ) ? $width['mobile'] : $mobile_w;
	} elseif ( $width ) {
		$desktop_w = $width;
	}

	// Backdrop.
	$backdrop = 'rgba(18, 21, 25, 0.6)';
	if ( isset( $cart_atts['cart_panel_backdrop']['backgroundColor']['default']['color'] ) ) {
		$backdrop = $cart_atts['cart_panel_backdrop']['backgroundColor']['default']['color'];
	}

	// Inner background.
	$inner_bg = 'var(--theme-palette-color-8)';
	if ( isset( $cart_atts['cart_panel_background']['backgroundColor']['default']['color'] ) ) {
		$inner_bg = $cart_atts['cart_panel_background']['backgroundColor']['default']['color'];
	}

	// Shadow.
	$shadow = '0px 0px 70px rgba(0, 0, 0, 0.35)';
	if ( isset( $cart_atts['cart_panel_shadow']['enable'] ) && $cart_atts['cart_panel_shadow']['enable'] ) {
		$s     = $cart_atts['cart_panel_shadow'];
		$inset = ! empty( $s['inset'] ) ? 'inset ' : '';
		$c     = isset( $s['color']['color'] ) ? $s['color']['color'] : 'rgba(0,0,0,0.35)';
		$shadow = $inset . ( $s['h_offset'] ?? 0 ) . 'px ' . ( $s['v_offset'] ?? 0 ) . 'px ' . ( $s['blur'] ?? 70 ) . 'px ' . ( $s['spread'] ?? 0 ) . 'px ' . $c;
	}

	// Close button.
	$close_color       = 'rgba(0, 0, 0, 0.5)';
	$close_color_hover = 'rgba(0, 0, 0, 0.8)';
	if ( isset( $cart_atts['cart_panel_close_button_color']['default']['color'] ) ) {
		$close_color = $cart_atts['cart_panel_close_button_color']['default']['color'];
	}
	if ( isset( $cart_atts['cart_panel_close_button_color']['hover']['color'] ) ) {
		$close_color_hover = $cart_atts['cart_panel_close_button_color']['hover']['color'];
	}

	echo '<style id="blocksy-child-wishlist-panel-css">
#woo-wishlist-panel{--side-panel-width:' . esc_attr( $desktop_w ) . ';--theme-box-shadow:' . esc_attr( $shadow ) . ';background-color:' . esc_attr( $backdrop ) . ';}
#woo-wishlist-panel .ct-panel-inner{background-color:' . esc_attr( $inner_bg ) . ';}
#woo-wishlist-panel .ct-toggle-close{--theme-icon-color:' . esc_attr( $close_color ) . ';}
#woo-wishlist-panel .ct-toggle-close:hover{--theme-icon-color:' . esc_attr( $close_color_hover ) . ';}
@media(max-width:999.98px){#woo-wishlist-panel{--side-panel-width:' . esc_attr( $tablet_w ) . ';}}
@media(max-width:689.98px){#woo-wishlist-panel{--side-panel-width:' . esc_attr( $mobile_w ) . ';}}
</style>';
}, 99 );

/**
 * AJAX endpoint — fetch single product data for newly added items.
 * Only called when JS doesn't have the product data locally.
 */
add_action( 'wp_ajax_blocksy_child_wishlist_product', 'blocksy_child_ajax_wishlist_product' );
add_action( 'wp_ajax_nopriv_blocksy_child_wishlist_product', 'blocksy_child_ajax_wishlist_product' );

/**
 * Render suggested products for the wishlist panel.
 *
 * Delegates to the shared helper bc_render_blocksy_suggested_carousel()
 * so this drawer and the mini-cart empty state render identically and
 * inherit the same bulletproofing layers (cart-fragments class rename,
 * flexy-strip, simple-grid fallback). See inc/helpers.php for the full
 * defense-in-depth design.
 *
 * @date 2026-04-26 — refactored to shared helper.
 */
function blocksy_child_render_wishlist_suggested() {
	$valid_ids = [];

	// Pull current wishlist IDs (if Blocksy's wishlist extension is active).
	if ( function_exists( 'blc_get_ext' ) ) {
		$ext = blc_get_ext( 'woocommerce-extra' );
		if ( $ext && method_exists( $ext, 'get_wish_list' ) ) {
			$wish_list_ext = $ext->get_wish_list();
			if ( $wish_list_ext && method_exists( $wish_list_ext, 'get_current_wish_list' ) ) {
				$items = $wish_list_ext->get_current_wish_list();
				if ( is_array( $items ) ) {
					$valid_ids = array_filter( array_map( function ( $item ) {
						return isset( $item['id'] ) ? (int) $item['id'] : 0;
					}, $items ) );
				}
			}
		}
	}

	// Shared helper handles ID validation, bestsellers fallback, Blocksy
	// template render with cart-fragments-safe class rename, flexy-strip,
	// and simple-grid fallback if the template fails entirely.
	return bc_render_blocksy_suggested_carousel( $valid_ids, 'bc-wishlist-suggested-grid' );
}

/**
 * Render the empty-state "You May Also Like" category grid.
 *
 * Figma's wishlist empty state replaces the suggested-products carousel with
 * a 2-column grid of top-level product categories (cover image + name + live
 * product count). Card layout only — returns '' otherwise, so Byron Bay never
 * sees this markup.
 *
 * Selection is data-driven: top-level product categories that have a curated
 * term thumbnail (the site's "featured" categories). A site can override the
 * exact set with the `blocksy_child_wishlist_category_ids` filter (array of
 * term IDs). Names + counts are always live WooCommerce data — never the
 * Figma scaffold values.
 *
 * @return string
 */
function blocksy_child_render_wishlist_categories() {
	if ( ! blocksy_child_wishlist_uses_cards() || ! function_exists( 'get_terms' ) ) {
		return '';
	}

	// Allow a site to curate the exact category set; otherwise auto-pick
	// top-level categories that have a featured term image.
	$curated_ids = apply_filters( 'blocksy_child_wishlist_category_ids', [] );

	$terms = [];
	if ( ! empty( $curated_ids ) && is_array( $curated_ids ) ) {
		$terms = get_terms( [
			'taxonomy'   => 'product_cat',
			'include'    => array_map( 'absint', $curated_ids ),
			'orderby'    => 'include',
			'hide_empty' => false,
		] );
	} else {
		$top = get_terms( [
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
		] );

		if ( ! is_wp_error( $top ) ) {
			foreach ( $top as $term ) {
				if ( (int) get_term_meta( $term->term_id, 'thumbnail_id', true ) > 0 ) {
					$terms[] = $term;
				}
			}
		}
	}

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	$terms = array_slice( $terms, 0, 4 );
	$cards = '';

	foreach ( $terms as $term ) {
		$thumb_id  = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
		$image     = $thumb_id ? wp_get_attachment_image( $thumb_id, 'woocommerce_thumbnail', false, [ 'alt' => $term->name, 'loading' => 'lazy' ] ) : '';
		$count_txt = sprintf( _n( '%s product', '%s products', $term->count, 'blocksy-child' ), number_format_i18n( $term->count ) );

		$cards .= '<a class="ct-wishlist-category-card" href="' . esc_url( get_term_link( $term ) ) . '">'
			. '<span class="ct-wishlist-category-media">' . $image . '</span>'
			. '<span class="ct-wishlist-category-name">' . esc_html( $term->name ) . '</span>'
			. '<span class="ct-wishlist-category-count">' . esc_html( $count_txt ) . '</span>'
			. '</a>';
	}

	return '<div class="ct-module-title">You May Also Like</div>'
		. '<div class="ct-wishlist-category-grid">' . $cards . '</div>';
}

/**
 * AJAX endpoint — fetch product details for wishlist drawer.
 *
 * Security: requires `bc_wishlist` nonce (added 2026-05-08 — was missing,
 * outlier among the 4 child-theme AJAX endpoints which all had nonces).
 * The nonce is localised to JS via `inc/enqueue.php` as `bcWishlist.nonce`
 * and sent on every request as `_wpnonce`.
 */
function blocksy_child_ajax_wishlist_product() {
	check_ajax_referer( 'bc_wishlist', '_wpnonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;

	if ( ! $product_id ) {
		wp_send_json_error( [ 'message' => 'Invalid product ID.' ] );
	}

	if ( ! function_exists( 'wc_get_product' ) ) {
		wp_send_json_error( [ 'message' => 'WooCommerce not available.' ] );
	}

	$product = wc_get_product( $product_id );

	if ( ! $product ) {
		wp_send_json_error( [ 'message' => 'Product not found.' ] );
	}

	wp_send_json_success( [
		'id'    => $product_id,
		'name'  => $product->get_name(),
		'url'   => $product->get_permalink(),
		'image' => $product->get_image( blocksy_child_wishlist_image_size() ),
		'price' => $product->get_price_html(),
	] );
}

/**
 * Render the panel shell (empty — JS fills content from preloaded data).
 */
function blocksy_child_render_wishlist_panel() {
	$placements = get_theme_mod( 'header_placements' );
	$close_type = 'type-1';

	if ( ! empty( $placements['sections'] ) ) {
		foreach ( $placements['sections'] as $section ) {
			if ( empty( $section['items'] ) ) {
				continue;
			}
			foreach ( $section['items'] as $item ) {
				if ( $item['id'] === 'cart' && isset( $item['values']['cart_panel_close_button_type'] ) ) {
					$close_type = $item['values']['cart_panel_close_button_type'];
					break 2;
				}
			}
		}
	}

	$close_icon = '<svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15"><path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/></svg>';

	// Wishlist heart icon — same SVG as the header wishlist icon. This is the
	// DEFAULT; the "Off-canvas Panel Icons" Customizer control
	// (inc/offcanvas-icons.php) overrides it when an admin uploads a drawer icon.
	$heart_icon = '<svg class="ct-icon ct-wishlist-panel-icon" width="15" height="15" viewBox="0 0 15 15"><path d="M7.5,13.9l-0.4-0.3c-0.2-0.2-4.6-3.5-5.8-4.8C0.4,7.7-0.1,6.4,0,5.1c0.1-1.2,0.7-2.2,1.6-3c0.9-0.8,2.3-1,3.6-0.8C6.1,1.5,6.9,2,7.5,2.6c0.6-0.6,1.4-1.1,2.4-1.3c1.3-0.2,2.6,0,3.5,0.8l0,0c0.9,0.7,1.5,1.8,1.6,3c0.1,1.3-0.3,2.6-1.3,3.7c-1.2,1.4-5.6,4.7-5.7,4.8L7.5,13.9z"/></svg>';

	if ( function_exists( 'blocksy_child_offcanvas_icon_markup' ) ) {
		$custom_heart = blocksy_child_offcanvas_icon_markup( 'wishlist', 'ct-wishlist-panel-icon' );

		if ( '' !== $custom_heart ) {
			$heart_icon = $custom_heart;
		}
	}

	// Render suggested products (server-side, same as mini cart).
	$suggested_html = blocksy_child_render_wishlist_suggested();

	// Card layout adds: a per-panel class (CSS scope), an empty-state category
	// grid, and an initial empty/filled state class so the right "You May Also
	// Like" block shows without a flash. JS keeps the state class in sync as
	// items are added/removed. All no-ops when card layout is off.
	$uses_cards     = blocksy_child_wishlist_uses_cards();
	$categories_html = blocksy_child_render_wishlist_categories();

	$panel_classes = 'ct-panel';
	if ( $uses_cards ) {
		$panel_classes .= ' ct-wishlist-cards';

		$server_count   = count( blocksy_child_wishlist_current_ids() );
		$panel_classes .= $server_count > 0 ? ' ct-wishlist-state-filled' : ' ct-wishlist-state-empty';
	}

	$categories_block = $uses_cards
		? '<div class="ct-wishlist-categories">' . $categories_html . '</div>'
		: '';

	return '<div id="woo-wishlist-panel" class="' . esc_attr( $panel_classes ) . '" data-behaviour="right-side" role="dialog" aria-label="Wishlist panel" inert>
		<div class="ct-panel-inner">
			<div class="ct-panel-actions">
				<h2 class="ct-panel-heading">' . $heart_icon . ' <span class="ct-wishlist-panel-title">Wishlist</span> <span class="ct-wishlist-panel-count">(<span class="ct-wishlist-count-number">0</span>)</span></h2>
				<button class="ct-toggle-close" data-type="' . esc_attr( $close_type ) . '" aria-label="Close wishlist drawer">' . $close_icon . '</button>
			</div>
			<div class="ct-panel-content">
				<div class="ct-panel-content-inner"></div>
			</div>
			' . $categories_block . '
			<div class="ct-wishlist-suggested">' . $suggested_html . '</div>
		</div>
	</div>';
}

/**
 * Current wishlist product IDs (server-side), or [] when unavailable.
 *
 * Shared by the suggested carousel and the initial card-layout state class.
 *
 * @return int[]
 */
function blocksy_child_wishlist_current_ids() {
	if ( ! function_exists( 'blc_get_ext' ) ) {
		return [];
	}

	$ext = blc_get_ext( 'woocommerce-extra' );
	if ( ! $ext || ! method_exists( $ext, 'get_wish_list' ) ) {
		return [];
	}

	$wish_list_ext = $ext->get_wish_list();
	if ( ! $wish_list_ext || ! method_exists( $wish_list_ext, 'get_current_wish_list' ) ) {
		return [];
	}

	$items = $wish_list_ext->get_current_wish_list();
	if ( ! is_array( $items ) ) {
		return [];
	}

	return array_values( array_filter( array_map( function ( $item ) {
		return isset( $item['id'] ) ? (int) $item['id'] : 0;
	}, $items ) ) );
}
