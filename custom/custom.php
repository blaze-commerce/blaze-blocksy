<?php
/**
 * Site-specific custom functions loader.
 * Loaded by functions.php. All custom PHP modules must be required here.
 *
 * This file is tracked in git as a base template. Per-deployment
 * customizations (require_once lines, enqueue calls) are added here.
 *
 * @package Blaze_Commerce
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'custom-style', BLAZE_BLOCKSY_URL . '/custom/product.css', array(), '1.0.0' );
	wp_enqueue_style( 'custom-minicart', BLAZE_BLOCKSY_URL . '/custom/minicart.css', array( 'blaze-blocksy-mini-cart' ), '1.0.0' );

	// Header Figma-exact styles (Task: 86evcm56n)
	wp_enqueue_style( 'custom-header', BLAZE_BLOCKSY_URL . '/custom/header/header.css', array(), '1.26.0' );
	wp_enqueue_style( 'custom-header-search', BLAZE_BLOCKSY_URL . '/custom/header/header-search.css', array(), '1.11.0' );
	wp_enqueue_script( 'header-carousel', BLAZE_BLOCKSY_URL . '/custom/header/carousel.js', array(), '1.0.0', true );

	// Checkout Figma-exact styles (Task: 86evcm57c) - only on checkout page
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		wp_enqueue_style( 'custom-checkout', BLAZE_BLOCKSY_URL . '/custom/checkout.css', array(), '1.2.4' );
		wp_enqueue_style( 'custom-header-checkout', BLAZE_BLOCKSY_URL . '/custom/header/header-checkout.css', array(), '1.0.6' );
	}

	// Enqueue WooCommerce Extra extension styles if not already loaded (Pro license inactive)
	if ( ! wp_style_is( 'blocksy-ext-woocommerce-extra-styles', 'enqueued' ) ) {
		$ext_css = WP_PLUGIN_DIR . '/blocksy-companion-pro/framework/premium/extensions/woocommerce-extra/static/bundle/main.min.css';
		if ( file_exists( $ext_css ) ) {
			wp_enqueue_style(
				'blocksy-ext-woocommerce-extra-styles',
				plugins_url( 'blocksy-companion-pro/framework/premium/extensions/woocommerce-extra/static/bundle/main.min.css' ),
				array( 'ct-main-styles' ),
				'2.0.0'
			);
		}
	}
} );

// FiboSearch: Override Blocksy integration to render as input field (not icon) on desktop (Task: 86evcm56n)
// FiboSearch's blocksy.php hardcodes [fibosearch layout="icon"] — we override the view path filter
// to render layout="classic" (full search bar) instead
add_filter( 'blocksy:header:item-view-path:search', function ( $path ) {
	$custom = get_stylesheet_directory() . '/custom/header/search-override.php';
	if ( file_exists( $custom ) ) {
		return $custom;
	}
	return $path;
}, 20 ); // Priority 20 to run after FiboSearch's priority 10

// FiboSearch: Override Blocksy's forced mobile overlay breakpoint (689px) to 1099px (Task: 86evcm56n)
// Blocksy theme integration in FiboSearch hardcodes forceMobileOverlayBreakpoint: 689,
// but we need overlay mode on tablet (768px) too. This filter runs at priority 20
// to override FiboSearch's Blocksy integration filter at priority 10.
add_filter( 'dgwt/wcas/settings/load_value/key=mobile_overlay_breakpoint', function () {
	return 1099;
}, 20 );

// FiboSearch: Also override the JS-side breakpoint filter as final safety net
add_filter( 'dgwt/wcas/scripts/mobile_overlay_breakpoint', function () {
	return 1099;
}, 20 );

// FiboSearch: Increase suggestion limit to show all sections (categories + products + posts)
// Default 7 gets consumed by categories (2-3) + products (5), leaving 0 for posts
add_filter( 'dgwt/wcas/settings/load_value/key=suggestions_limit', function () {
	return 12;
}, 20 );

// Register custom menu locations for client-editable header elements (Task: 86evcm56n)
add_action( 'after_setup_theme', function () {
	register_nav_menus( array(
		'header_category_nav'    => __( 'Header Category Nav (Mobile/Tablet Bar)', 'blaze-blocksy' ),
		'header_info_dropdown'   => __( 'Header Info Icon Dropdown', 'blaze-blocksy' ),
		'offcanvas_bottom_links' => __( 'Off-Canvas Bottom Links (Mobile)', 'blaze-blocksy' ),
	) );
}, 20 );

// Mega menu section headers from Description field (Task: 86evcm56n)
add_filter( 'walker_nav_menu_start_el', function ( $item_output, $item, $depth, $args ) {
	if ( is_array( $item->classes ) && preg_grep( '/mega-section-header/', $item->classes ) ) {
		if ( ! empty( $item->description ) ) {
			$title       = esc_html( $item->description );
			$item_output = '<span class="mega-section-title">' . $title . '</span>' . $item_output;
		}
	}
	return $item_output;
}, 10, 4 );

// Off-canvas bottom links — client-editable via Appearance > Menus (Task: 86evcm56n)
add_action( 'blocksy:header:offcanvas:mobile:bottom', function () {
	if ( ! has_nav_menu( 'offcanvas_bottom_links' ) ) {
		return;
	}
	wp_nav_menu( array(
		'theme_location'  => 'offcanvas_bottom_links',
		'container'       => 'div',
		'container_class' => 'offcanvas-bottom-links',
		'items_wrap'      => '%3$s',
		'depth'           => 1,
		'fallback_cb'     => false,
		'link_before'     => '',
		'link_after'      => '',
		'walker'          => new class extends Walker_Nav_Menu {
			public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
				$output .= '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
			}
			public function end_el( &$output, $item, $depth = 0, $args = null ) {}
			public function start_lvl( &$output, $depth = 0, $args = null ) {}
			public function end_lvl( &$output, $depth = 0, $args = null ) {}
		},
	) );
} );

// Mobile/Tablet: Inject category nav bar + FiboSearch row (Task: 86evcm56n)
// Blocksy only supports 3 rows — this filter appends custom rows after them
add_filter( 'blocksy:header:rows-render', function ( $custom_content, $rows, $device ) {
	if ( $device !== 'mobile' ) {
		return $custom_content;
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return $custom_content;
	}

	$output = $custom_content ? $custom_content : implode( '', array_values( $rows ) );

	// Category Navigation Bar (#C2E9FF) — client-editable via Appearance > Menus
	$category_nav = '';
	if ( has_nav_menu( 'header_category_nav' ) ) {
		$category_nav = wp_nav_menu( array(
			'theme_location'  => 'header_category_nav',
			'container'       => 'div',
			'container_class' => 'header-category-nav',
			'items_wrap'      => '%3$s',
			'depth'           => 1,
			'echo'            => false,
			'fallback_cb'     => false,
			'walker'          => new class extends Walker_Nav_Menu {
				private $index = 0;
				public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
					if ( $this->index > 0 ) {
						$output .= '<span class="category-divider">|</span>';
					}
					$output .= '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
					$this->index++;
				}
				public function end_el( &$output, $item, $depth = 0, $args = null ) {}
				public function start_lvl( &$output, $depth = 0, $args = null ) {}
				public function end_lvl( &$output, $depth = 0, $args = null ) {}
			},
		) );
	}

	// FiboSearch Bar (#F4F4F4)
	$search_row  = '<div class="header-search-row">';
	$search_row .= do_shortcode( '[fibosearch]' );
	$search_row .= '</div>';

	$output .= $category_nav;
	$output .= $search_row;

	return $output;
}, 11, 3 );

require_once( 'divi/divi-custom.php' );
require_once( 'homepage/homepage.php' );
require_once( 'checkout/checkout-trust-badges.php' );
require_once( 'hide-cart-page.php' );


/**
 * ---------------------------------------------------------
 * 1. ADD CUSTOM FIELDS BEFORE EMAIL / PASSWORD
 * ---------------------------------------------------------
 * Adds:
 * - Billing First Name (required)
 * - Billing Last Name (required)
 * - Billing Company (required)
 * These appear BEFORE the email & password fields
 */
add_action( 'woocommerce_register_form_start', 'gb_add_custom_register_fields' );
function gb_add_custom_register_fields() {
	?>

	<!-- Billing First Name -->
	<p class="form-row form-row-first">
		<label for="reg_billing_first_name">
			First name <span class="required">*</span>
		</label>
		<input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name"
			value="<?php echo esc_attr( $_POST['billing_first_name'] ?? '' ); ?>" />
	</p>

	<!-- Billing Last Name -->
	<p class="form-row form-row-last">
		<label for="reg_billing_last_name">
			Last name <span class="required">*</span>
		</label>
		<input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name"
			value="<?php echo esc_attr( $_POST['billing_last_name'] ?? '' ); ?>" />
	</p>

	<div class="clear"></div>

	<!-- Billing Company -->
	<p class="form-row form-row-wide">
		<label for="reg_billing_company">
			Company <span class="required">*</span>
		</label>
		<input type="text" class="input-text" name="billing_company" id="reg_billing_company"
			value="<?php echo esc_attr( $_POST['billing_company'] ?? '' ); ?>" />
	</p>

	<?php
}

/**
 * ---------------------------------------------------------
 * 2. ADD CONFIRM PASSWORD FIELD BELOW PASSWORD
 * ---------------------------------------------------------
 * Hooked AFTER WooCommerce outputs the password field
 */
add_action( 'woocommerce_register_form', 'gb_add_confirm_password_field' );
function gb_add_confirm_password_field() {
	?>
	<p class="form-row form-row-wide">
		<label for="reg_password2">
			Confirm Password <span class="required">*</span>
		</label>
		<input type="password" class="input-text" name="password2" id="reg_password2" />
	</p>
	<?php
}

/**
 * ---------------------------------------------------------
 * 3. VALIDATE CUSTOM REGISTRATION FIELDS
 * ---------------------------------------------------------
 * - Required fields
 * - Password & Confirm Password match
 */
add_filter( 'woocommerce_registration_errors', 'gb_validate_custom_register_fields', 10, 3 );
function gb_validate_custom_register_fields( $errors, $username, $email ) {

	// Validate First Name
	if ( empty( $_POST['billing_first_name'] ) ) {
		$errors->add( 'billing_first_name_error', __( 'First name is required.', 'woocommerce' ) );
	}

	// Validate Last Name
	if ( empty( $_POST['billing_last_name'] ) ) {
		$errors->add( 'billing_last_name_error', __( 'Last name is required.', 'woocommerce' ) );
	}

	// Validate Company
	if ( empty( $_POST['billing_company'] ) ) {
		$errors->add( 'billing_company_error', __( 'Company is required.', 'woocommerce' ) );
	}

	// Validate Password Match
	if (
		isset( $_POST['password'], $_POST['password2'] ) &&
		$_POST['password'] !== $_POST['password2']
	) {
		$errors->add( 'password_mismatch', __( 'Passwords do not match.', 'woocommerce' ) );
	}

	return $errors;
}

/**
 * ---------------------------------------------------------
 * 4. SAVE CUSTOM FIELDS TO USER PROFILE
 * ---------------------------------------------------------
 * Saves data to:
 * - WooCommerce billing fields
 * - WordPress user profile (first/last name)
 */
add_action( 'woocommerce_created_customer', 'gb_save_custom_register_fields' );
function gb_save_custom_register_fields( $customer_id ) {

	// Save billing fields
	update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ?? '' ) );
	update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ?? '' ) );
	update_user_meta( $customer_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ?? '' ) );

	// Also save WordPress profile names
	update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ?? '' ) );
	update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ?? '' ) );
}

/**
 * ---------------------------------------------------------
 * 5. ENABLE OFFCANVAS CART (when Pro extension is inactive)
 * ---------------------------------------------------------
 * Enables the off-canvas cart drawer type option and renders the
 * #woo-cart-panel via the footer offcanvas drawer hook.
 */

// Enable the cart drawer type option (prevent it from being forced to 'dropdown')
add_filter( 'blocksy:header:cart:cart_drawer_type:option', function () {
	return 'ct-image-picker';
}, 10 );

// Render the #woo-cart-panel in the footer offcanvas drawer area
add_filter( 'blocksy:footer:offcanvas-drawer', function ( $elements, $payload ) {
	if ( $payload['location'] !== 'start' || empty( $payload['blocksy_has_default_header'] ) ) {
		return $elements;
	}

	if ( ! function_exists( 'woocommerce_mini_cart' ) ) {
		return $elements;
	}

	// Check if the Pro extension already rendered the panel
	if ( class_exists( 'Blocksy\Extensions\WoocommerceExtra\OffcanvasCart' ) ) {
		return $elements;
	}

	$render = new \Blocksy_Header_Builder_Render();

	if ( ! $render->contains_item( 'cart' ) ) {
		return $elements;
	}

	$atts = $render->get_item_data_for( 'cart' );

	$cart_drawer_type = blocksy_default_akg( 'cart_drawer_type', $atts, 'dropdown' );

	if ( $cart_drawer_type !== 'offcanvas' ) {
		return $elements;
	}

	$cart_panel_position = blocksy_default_akg( 'cart_panel_position', $atts, 'right' );
	$behavior = $cart_panel_position . '-side';

	ob_start();
	woocommerce_mini_cart();
	$content = ob_get_clean();

	$close_icon = '<svg class="ct-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5364 6.2636C7.18492 5.91213 6.61508 5.91213 6.2636 6.2636C5.91213 6.61508 5.91213 7.18492 6.2636 7.5364L10.7272 12L6.2636 16.4636C5.91213 16.8151 5.91213 17.3849 6.2636 17.7364C6.61508 18.0879 7.18492 18.0879 7.5364 17.7364L12 13.2728L16.4636 17.7364C16.8151 18.0879 17.3849 18.0879 17.7364 17.7364C18.0879 17.3849 18.0879 16.8151 17.7364 16.4636L13.2728 12L17.7364 7.5364C18.0879 7.18492 18.0879 6.61508 17.7364 6.2636C17.3849 5.91213 16.8151 5.91213 16.4636 6.2636L12 10.7272L7.5364 6.2636Z" fill="currentColor"/></svg>';

	$cart_icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.25 3H3.63568C4.14537 3 4.59138 3.34265 4.7227 3.83513L5.1059 5.27209M7.5 14.25C5.84315 14.25 4.5 15.5931 4.5 17.25H20.25M7.5 14.25H18.7183C19.8394 11.9494 20.8177 9.56635 21.6417 7.1125C16.88 5.89646 11.8905 5.25 6.75 5.25C6.20021 5.25 5.65214 5.2574 5.1059 5.27209M7.5 14.25L5.1059 5.27209M6 20.25C6 20.6642 5.66421 21 5.25 21C4.83579 21 4.5 20.6642 4.5 20.25C4.5 19.8358 4.83579 19.5 5.25 19.5C5.66421 19.5 6 19.8358 6 20.25ZM18.75 20.25C18.75 20.6642 18.4142 21 18 21C17.5858 21 17.25 20.6642 17.25 20.25C17.25 19.8358 17.5858 19.5 18 19.5C18.4142 19.5 18.75 19.8358 18.75 20.25Z" stroke="#353638" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

	$elements[] = '<div id="woo-cart-panel" class="ct-panel" data-behaviour="' . esc_attr( $behavior ) . '" role="dialog" aria-label="Shopping cart panel" inert>
		<div class="ct-panel-inner">
			<div class="ct-panel-actions">
				<span class="ct-panel-heading"><span class="cart-panel-icon">' . $cart_icon . '</span>Shopping Cart</span>
				<button class="ct-toggle-close" data-type="type-1" aria-label="Close cart drawer">' . $close_icon . '</button>
			</div>
			<div class="ct-panel-content">
				<div class="ct-panel-content-inner">' . $content . '</div>
			</div>
		</div>
	</div>';

	return $elements;
}, 10, 2 );

// Update cart fragments for AJAX cart updates
add_filter( 'blocksy:woocommerce:cart-fragments', function ( $fragments ) {
	if ( ! function_exists( 'woocommerce_mini_cart' ) ) {
		return $fragments;
	}

	if ( class_exists( 'Blocksy\Extensions\WoocommerceExtra\OffcanvasCart' ) ) {
		return $fragments;
	}

	ob_start();
	woocommerce_mini_cart();
	$content = ob_get_clean();

	$fragments['#woo-cart-panel .ct-panel-content'] = '<div class="ct-panel-content"><div class="ct-panel-content-inner">' . $content . '</div></div>';

	return $fragments;
} );

// Force fragment refresh when panel content is empty but cart has items (stale cache fix)
add_action( 'wp_footer', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	?>
	<script>
	(function() {
		if (typeof jQuery === 'undefined' || typeof wc_cart_fragments_params === 'undefined') return;
		var $ = jQuery;
		function blazeCheckCartPanel() {
			var panel = document.querySelector('#woo-cart-panel .ct-panel-content-inner');
			if (!panel) return;
			var isEmpty = panel.innerHTML.trim().length === 0;
			var cartCount = document.querySelector('.ct-dynamic-count-cart');
			var hasItems = cartCount && parseInt(cartCount.textContent) > 0;
			if (isEmpty && hasItems) {
				var key = Object.keys(sessionStorage).find(function(k) { return k.indexOf('wc_fragments') !== -1; });
				var hashKey = Object.keys(sessionStorage).find(function(k) { return k.indexOf('wc_cart_hash') !== -1; });
				if (key) sessionStorage.removeItem(key);
				if (hashKey) sessionStorage.removeItem(hashKey);
				$(document.body).trigger('wc_fragment_refresh');
			}
		}
		// Check after cart-fragments.js applies cached fragments
		$(document.body).on('wc_fragments_loaded', blazeCheckCartPanel);
		// Also check on DOM ready for cases where wc_fragments_loaded already fired
		$(blazeCheckCartPanel);
	})();
	</script>
	<?php
}, 99 );

// Info icon: populate .info-dropdown-panel with wp_nav_menu + click-only toggle (Task: 86evcm56n)
add_action( 'wp_footer', function () {
	if ( ! has_nav_menu( 'header_info_dropdown' ) ) {
		return;
	}
	// Detect wishlist page URL to intercept clicks and open offcanvas (Task: 86ewnj5v5)
	// Try Blocksy's theme mod first, fall back to page slug lookup
	$wishlist_page_id = function_exists( 'blocksy_get_theme_mod' )
		? (int) blocksy_get_theme_mod( 'woocommerce_wish_list_page', 0 )
		: 0;
	if ( ! $wishlist_page_id ) {
		$wishlist_page    = get_page_by_path( 'wishlist' );
		$wishlist_page_id = $wishlist_page ? $wishlist_page->ID : 0;
	}
	$wishlist_url = $wishlist_page_id ? untrailingslashit( get_permalink( $wishlist_page_id ) ) : '';
	// Render the menu links
	$menu_html = wp_nav_menu( array(
		'theme_location'  => 'header_info_dropdown',
		'container'       => false,
		'items_wrap'      => '%3$s',
		'depth'           => 1,
		'echo'            => false,
		'fallback_cb'     => false,
		'walker'          => new class( $wishlist_url ) extends Walker_Nav_Menu {
			private $wishlist_url;
			public function __construct( $wishlist_url ) {
				$this->wishlist_url = $wishlist_url;
			}
			public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
				$item_path    = untrailingslashit( wp_parse_url( $item->url, PHP_URL_PATH ) ?? $item->url );
				$wishlist_path = $this->wishlist_url
					? untrailingslashit( wp_parse_url( $this->wishlist_url, PHP_URL_PATH ) ?? '' )
					: '';
				$is_wishlist = $wishlist_path && $item_path === $wishlist_path;
				if ( $is_wishlist ) {
					$output .= '<a href="#wishlist-offcanvas" data-shortcut="wishlist" class="ct-offcanvas-trigger">'
						. esc_html( $item->title ) . '</a>';
				} else {
					$output .= '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
				}
			}
			public function end_el( &$output, $item, $depth = 0, $args = null ) {}
			public function start_lvl( &$output, $depth = 0, $args = null ) {}
			public function end_lvl( &$output, $depth = 0, $args = null ) {}
		},
	) );
	if ( empty( $menu_html ) ) {
		return;
	}
	$menu_json = wp_json_encode( $menu_html );
	?>
	<script>
	(function() {
		var wrap = document.querySelector('.header-icon-info-wrap');
		if (!wrap) return;
		// Populate empty info-dropdown-panel with menu links
		var panel = wrap.querySelector('.info-dropdown-panel');
		if (panel && !panel.innerHTML.trim()) {
			panel.innerHTML = <?php echo $menu_json; ?>;
		}
		// Close info dropdown when wishlist off-canvas link is triggered (capture phase
		// ensures it runs before stopPropagation in wishlist-offcanvas.js)
		document.addEventListener('click', function(e) {
			if (e.target.closest('[data-shortcut="wishlist"]')) {
				wrap.classList.remove('info-open');
			}
		}, true);
		// Click-only toggle (not hover)
		var link = wrap.querySelector('.header-icon-info');
		if (!link) return;
		link.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			wrap.classList.toggle('info-open');
		});
		document.addEventListener('click', function(e) {
			if (!wrap.contains(e.target)) {
				wrap.classList.remove('info-open');
			}
		});
	})();
	</script>
	<?php
}, 100 );

// Footer: Make "Blaze Commerce" link open in new tab (Task: 86evcm57c)
add_action( 'wp_footer', function () {
	?>
	<script>
	(function() {
		var link = document.querySelector('footer.ct-footer a[href*="blazecommerce"]');
		if (link) {
			link.setAttribute('target', '_blank');
			link.setAttribute('rel', 'noopener noreferrer');
		}
	})();
	</script>
	<?php
}, 98 );

// Replace Blocksy built-in Account, Cart & Close SVGs with Figma-exact icons (Task: 86evcm56n)
add_action( 'wp_footer', function () {
	?>
	<script>
	(function() {
		// Figma-exact User icon (21x24 per Figma, viewBox 0 0 24 26)
		var userSvg = '<svg class="ct-icon" aria-hidden="true" width="21" height="24" viewBox="0 0 24 26" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.5 24.75C22.5 20.106 17.7939 16.35 12 16.35C6.20605 16.35 1.5 20.106 1.5 24.75M18.1116 6.75C18.1116 10.0637 15.3752 12.75 11.9998 12.75C8.62438 12.75 5.88805 10.0637 5.88805 6.75C5.88805 3.43629 8.62438 0.75 11.9998 0.75C15.3752 0.75 18.1116 3.43629 18.1116 6.75Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		// Figma-exact Cart icon (24x22 stroke content, viewBox cropped to content bounds with stroke padding)
		var cartSvg = '<svg aria-hidden="true" width="24" height="22" viewBox="9 10 26 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 11H11.715C12.3458 11 12.8978 11.4188 13.0603 12.0207L13.5346 13.777M16.4976 24.75C14.447 24.75 12.7847 26.3916 12.7847 28.4167H32.2775M16.4976 24.75H30.3818C31.7693 21.9381 32.9802 19.0255 34 16.0264C28.1068 14.5401 21.9315 13.75 15.5694 13.75C14.8889 13.75 14.2106 13.759 13.5346 13.777M16.4976 24.75L13.5346 13.777M14.6412 32.0833C14.6412 32.5896 14.2256 33 13.7129 33C13.2003 33 12.7847 32.5896 12.7847 32.0833C12.7847 31.5771 13.2003 31.1667 13.7129 31.1667C14.2256 31.1667 14.6412 31.5771 14.6412 32.0833ZM30.4211 32.0833C30.4211 32.5896 30.0055 33 29.4928 33C28.9802 33 28.5646 32.5896 28.5646 32.0833C28.5646 31.5771 28.9802 31.1667 29.4928 31.1667C30.0055 31.1667 30.4211 31.5771 30.4211 32.0833Z" stroke="currentColor" stroke-opacity="0.9" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		// Figma-exact Close icon (heroicons-mini/x-mark, 24x24 stroke-based)
		var closeSvg = '<svg class="ct-icon" aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 18L18 6M6 6L18 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		// Figma-exact Info icon (29.33x29.33 visible content, viewBox cropped to content at offset 7.33)
		var infoSvg = '<svg class="info-icon-svg" aria-hidden="true" width="29" height="29" viewBox="6.58 6.58 30.83 30.83" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21.9997 27.867V20.5337M21.9916 16.1337H22.0048M21.9997 7.33366C30.0663 7.33366 36.6663 13.9337 36.6663 22.0003C36.6663 30.067 30.0663 36.667 21.9997 36.667C13.933 36.667 7.33301 30.067 7.33301 22.0003C7.33301 13.9337 13.933 7.33366 21.9997 7.33366Z" stroke="#1D1D1F" stroke-opacity="0.9" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		// Replace Account icon (handles both SVG icons and avatar images)
		var accountEls = document.querySelectorAll('[data-id="account"] svg.ct-icon, [data-id="account"] .ct-image-container img');
		accountEls.forEach(function(el) {
			var tmp = document.createElement('div');
			tmp.innerHTML = userSvg;
			var newSvg = tmp.firstElementChild;
			el.parentNode.replaceChild(newSvg, el);
		});

		// Replace Cart icon (keep the badge/count span)
		var cartContainers = document.querySelectorAll('[data-id="cart"] .ct-icon-container');
		cartContainers.forEach(function(container) {
			var oldSvg = container.querySelector('svg');
			if (oldSvg) {
				var tmp = document.createElement('div');
				tmp.innerHTML = cartSvg;
				var newSvg = tmp.firstElementChild;
				oldSvg.parentNode.replaceChild(newSvg, oldSvg);
			}
		});

		// Replace Close icon in off-canvas panel
		var closeSvgs = document.querySelectorAll('#offcanvas .ct-toggle-close svg');
		closeSvgs.forEach(function(svg) {
			var tmp = document.createElement('div');
			tmp.innerHTML = closeSvg;
			var newSvg = tmp.firstElementChild;
			svg.parentNode.replaceChild(newSvg, svg);
		});

		// Replace Info icon with Figma-exact SVG (29.33x29.33 visible content)
		var infoIcons = document.querySelectorAll('.info-icon-svg');
		infoIcons.forEach(function(svg) {
			var tmp = document.createElement('div');
			tmp.innerHTML = infoSvg;
			var newSvg = tmp.firstElementChild;
			svg.parentNode.replaceChild(newSvg, svg);
		});

		// Move account-modal close button inside the white modal box (upper-right)
		var accountModal = document.querySelector('#account-modal');
		if (accountModal) {
			var modalBox = accountModal.querySelector('.ct-account-modal');
			var closeBtn = accountModal.querySelector('.ct-toggle-close');
			if (modalBox && closeBtn) {
				closeBtn.style.cssText = 'position:absolute !important;top:12px;right:12px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;background:none;border:none;cursor:pointer;z-index:11;padding:0;';
				// Replace Blocksy filled SVG with stroke-based X
				var oldCloseSvg = closeBtn.querySelector('svg');
				if (oldCloseSvg) {
					var tmp2 = document.createElement('div');
					tmp2.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 18L18 6M6 6L18 18" stroke="#1D1D1F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
					oldCloseSvg.parentNode.replaceChild(tmp2.firstElementChild, oldCloseSvg);
				}
				modalBox.insertBefore(closeBtn, modalBox.firstChild);
			}
		}
	})();
	</script>
	<?php
}, 101 );
