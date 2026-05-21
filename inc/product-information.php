<?php
/**
 * Product Information — off-canvas panel with Shipping Calculator, Returns & FAQ.
 *
 * Renders 3 clickable items on the PDP (priority 35, after ATC).
 * Clicking any item opens a ct-panel off-canvas with tabbed content.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────────────────────────────
 * Tab configuration — add a line here to add a new tab.
 * ──────────────────────────────────────────────────────────────── */
function bc_product_info_tabs() {
	return [
		[
			'slug'  => 'shipping',
			'title' => 'Calculate Shipping',
			'icon'  => '<svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
			'type'  => 'calculator',
		],
		[
			'slug'    => 'returns',
			'title'   => 'Return',
			'icon'    => '<svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
			'type'    => 'page',
			'page_id' => 7315,
		],
		[
			'slug'    => 'faqs',
			'title'   => 'FAQ',
			'icon'    => '<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" width="18" height="18"><g id="Circle_Info"><g><g><path d="M11.5,15a.5.5,0,0,0,1,0h0V10.981a.5.5,0,0,0-1,0Z"/><circle cx="12" cy="8.999" r="0.5"/></g><path d="M12,2.065A9.934,9.934,0,1,1,2.066,12,9.945,9.945,0,0,1,12,2.065Zm0,18.867A8.934,8.934,0,1,0,3.066,12,8.944,8.944,0,0,0,12,20.932Z"/></g></g></svg>',
			'type'    => 'page',
			'page_id' => 7244,
		],
	];
}

/* ────────────────────────────────────────────────────────────────
 * 1. Render the clickable item row on the PDP.
 * ──────────────────────────────────────────────────────────────── */
add_action( 'woocommerce_single_product_summary', function () {
	$tabs = bc_product_info_tabs();
	?>
	<div class="bc-product-info-items">
		<?php foreach ( $tabs as $i => $tab ) : ?>
			<?php if ( $i > 0 ) : ?>
				<span class="bc-product-info-divider" aria-hidden="true"></span>
			<?php endif; ?>
			<a href="#product-info-panel"
			   class="bc-product-info-item"
			   data-panel="product-info-panel"
			   data-tab="<?php echo esc_attr( $tab['slug'] ); ?>"
			   aria-label="<?php echo esc_attr( $tab['title'] ); ?>">
				<span class="bc-product-info-icon"><?php echo $tab['icon']; ?></span>
				<span class="bc-product-info-label"><?php echo esc_html( $tab['title'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}, 35 );

/* ────────────────────────────────────────────────────────────────
 * 2. Render the off-canvas panel via Blocksy's offcanvas-drawer.
 * ──────────────────────────────────────────────────────────────── */
add_filter( 'blocksy:footer:offcanvas-drawer', function ( $elements, $payload ) {
	if ( ! is_product() || $payload['location'] !== 'start' ) {
		return $elements;
	}

	$elements[] = bc_render_product_info_panel();
	return $elements;
}, 10, 2 );

/**
 * Build the off-canvas panel HTML.
 */
function bc_render_product_info_panel() {
	global $product;
	$tabs      = bc_product_info_tabs();
	$countries = WC()->countries->get_countries();
	$base      = WC()->countries->get_base_country();

	$close_icon = '<svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15"><path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/></svg>';

	// --- Tabs ---
	$tabs_html = '<div class="bc-info-tabs" role="tablist">';
	foreach ( $tabs as $i => $tab ) {
		$active = 0 === $i;
		$tabs_html .= '<button class="bc-info-tab' . ( $active ? ' active' : '' ) . '"'
			. ' role="tab"'
			. ' aria-selected="' . ( $active ? 'true' : 'false' ) . '"'
			. ' aria-controls="bc-tab-' . esc_attr( $tab['slug'] ) . '"'
			. ' data-tab="' . esc_attr( $tab['slug'] ) . '">'
			. esc_html( $tab['title'] )
			. '</button>';
	}
	$tabs_html .= '</div>';

	// --- Tab panes ---
	$panes_html = '';
	foreach ( $tabs as $i => $tab ) {
		$active      = 0 === $i;
		$pane_class  = 'bc-tab-pane' . ( $active ? ' active' : '' );
		$hidden_attr = $active ? '' : ' hidden';

		$pane_content = '';

		if ( 'calculator' === $tab['type'] ) {
			$product_id = $product ? $product->get_id() : 0;

			// Country options.
			$country_options = '<option value="">' . esc_html__( 'Select a country&hellip;', 'blocksy-child' ) . '</option>';
			foreach ( $countries as $code => $name ) {
				$selected = ( $code === $base ) ? ' selected' : '';
				$country_options .= '<option value="' . esc_attr( $code ) . '"' . $selected . '>' . esc_html( $name ) . '</option>';
			}

			$pane_content = '<form class="bc-shipping-calc" data-product-id="' . esc_attr( $product_id ) . '">'
				. '<div class="bc-field">'
				. '<label for="bc-calc-country">' . esc_html__( 'Country', 'blocksy-child' ) . '</label>'
				. '<select id="bc-calc-country" name="country">' . $country_options . '</select>'
				. '</div>'
				. '<div class="bc-field bc-field-state">'
				. '<label for="bc-calc-state">' . esc_html__( 'State / Region', 'blocksy-child' ) . '</label>'
				. '<select id="bc-calc-state" name="state"><option value="">' . esc_html__( 'Select a state&hellip;', 'blocksy-child' ) . '</option></select>'
				. '</div>'
				. '<div class="bc-field">'
				. '<label for="bc-calc-postcode">' . esc_html__( 'Postcode', 'blocksy-child' ) . '</label>'
				. '<input type="text" id="bc-calc-postcode" name="postcode" placeholder="e.g. 2000">'
				. '</div>'
				. '<button type="submit" class="bc-calc-submit">' . esc_html__( 'Calculate Shipping', 'blocksy-child' ) . '</button>'
				. '</form>'
				. '<div class="bc-shipping-results" aria-live="polite"></div>';

		} elseif ( 'page' === $tab['type'] && ! empty( $tab['page_id'] ) ) {
			$page = get_post( $tab['page_id'] );
			if ( $page && 'publish' === $page->post_status ) {
				$pane_content = '<div class="bc-page-content">' . apply_filters( 'the_content', $page->post_content ) . '</div>';
			}
		}

		$panes_html .= '<div id="bc-tab-' . esc_attr( $tab['slug'] ) . '" class="' . $pane_class . '" role="tabpanel"' . $hidden_attr . '>'
			. $pane_content
			. '</div>';
	}

	// --- Dynamic CSS mirroring cart panel ---
	bc_product_info_panel_css();

	return '<div id="product-info-panel" class="ct-panel" data-behaviour="right-side" role="dialog" aria-label="' . esc_attr__( 'Product information', 'blocksy-child' ) . '" inert>'
		. '<div class="ct-panel-inner">'
		. '<div class="ct-panel-actions">'
		. '<button class="ct-toggle-close" data-type="type-1" aria-label="' . esc_attr__( 'Close panel', 'blocksy-child' ) . '">' . $close_icon . '</button>'
		. '</div>'
		. '<div class="ct-panel-content">' . $tabs_html . '<div class="ct-panel-content-inner">' . $panes_html . '</div></div>'
		. '</div>'
		. '</div>';
}

/**
 * Output dynamic CSS that mirrors the cart panel styling from Blocksy Customizer.
 * Same approach as wishlist-offcanvas.php.
 */
function bc_product_info_panel_css() {
	$placements = get_theme_mod( 'header_placements' );
	$cart_atts  = [];

	if ( ! empty( $placements['sections'] ) ) {
		foreach ( $placements['sections'] as $section ) {
			if ( empty( $section['items'] ) ) {
				continue;
			}
			foreach ( $section['items'] as $item ) {
				// Defensive (audit P1 2026-05-08): guard against Blocksy returning
				// items missing the 'id' key after a Customizer change.
				if ( ! empty( $item['id'] ) && $item['id'] === 'cart' && isset( $item['values'] ) ) {
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

	echo '<style id="blocksy-child-product-info-panel-css">
#product-info-panel{--side-panel-width:' . esc_attr( $desktop_w ) . ';--theme-box-shadow:' . esc_attr( $shadow ) . ';background-color:' . esc_attr( $backdrop ) . ';}
#product-info-panel .ct-panel-inner{background-color:' . esc_attr( $inner_bg ) . ';}
#product-info-panel .ct-toggle-close{--theme-icon-color:' . esc_attr( $close_color ) . ';}
#product-info-panel .ct-toggle-close:hover{--theme-icon-color:' . esc_attr( $close_color_hover ) . ';}
@media(max-width:999.98px){#product-info-panel{--side-panel-width:' . esc_attr( $tablet_w ) . ';}}
@media(max-width:689.98px){#product-info-panel{--side-panel-width:' . esc_attr( $mobile_w ) . ';}}
</style>';
}

/* ────────────────────────────────────────────────────────────────
 * 3. AJAX — Get states for a country.
 * ──────────────────────────────────────────────────────────────── */
function bc_ajax_get_states() {
	check_ajax_referer( 'bc_product_info', 'nonce' );

	$country = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
	$states  = WC()->countries->get_states( $country );

	wp_send_json_success( $states ? $states : [] );
}
add_action( 'wp_ajax_bc_get_states', 'bc_ajax_get_states' );
add_action( 'wp_ajax_nopriv_bc_get_states', 'bc_ajax_get_states' );

/* ────────────────────────────────────────────────────────────────
 * 4. AJAX — Calculate shipping rates.
 * ──────────────────────────────────────────────────────────────── */
function bc_ajax_calculate_shipping() {
	check_ajax_referer( 'bc_product_info', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$country    = isset( $_POST['country'] )    ? sanitize_text_field( wp_unslash( $_POST['country'] ) )  : '';
	$state      = isset( $_POST['state'] )      ? sanitize_text_field( wp_unslash( $_POST['state'] ) )    : '';
	$postcode   = isset( $_POST['postcode'] )   ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '';

	if ( ! $product_id || ! $country ) {
		wp_send_json_error( [ 'message' => __( 'Please select a country.', 'blocksy-child' ) ] );
	}

	$product = wc_get_product( $product_id );

	if ( ! $product || ! $product->needs_shipping() ) {
		wp_send_json_error( [ 'message' => __( 'This product does not require shipping.', 'blocksy-child' ) ] );
	}

	$package = [
		'contents'        => [
			[
				'product_id'   => $product_id,
				'variation_id' => 0,
				'data'         => $product,
				'quantity'     => 1,
				'line_total'   => (float) $product->get_price(),
				'line_tax'     => 0,
			],
		],
		'contents_cost'   => (float) $product->get_price(),
		'applied_coupons' => [],
		'destination'     => [
			'country'   => $country,
			'state'     => $state,
			'postcode'  => $postcode,
			'city'      => '',
			'address'   => '',
			'address_2' => '',
		],
	];

	$shipping = WC()->shipping();
	$rates    = $shipping->calculate_shipping_for_package( $package );

	if ( empty( $rates['rates'] ) ) {
		wp_send_json_error( [ 'message' => __( 'No shipping options were found for this address.', 'blocksy-child' ) ] );
	}

	$result = [];
	foreach ( $rates['rates'] as $rate ) {
		$result[] = [
			'label' => $rate->get_label(),
			'cost'  => html_entity_decode( wp_strip_all_tags( wc_price( $rate->get_cost() ) ) ),
		];
	}

	wp_send_json_success( $result );
}
add_action( 'wp_ajax_bc_calculate_shipping', 'bc_ajax_calculate_shipping' );
add_action( 'wp_ajax_nopriv_bc_calculate_shipping', 'bc_ajax_calculate_shipping' );
