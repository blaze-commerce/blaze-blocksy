<?php
/**
 * Asset Enqueuing — Registers and enqueues all child theme CSS/JS.
 *
 * Load order (lowest → highest cascade priority):
 * 1. Blocksy parent (ct-main-styles) — Blocksy Customizer output
 * 2. style.css — theme header only, no rules
 * 3. base.css — global tweaks Blocksy Customizer can't do
 * 4. components/*.css — conditional per page type
 * 5. utilities.css — utility classes
 * 6. clients/{slug}/{slug}.css — client-specific (loaded LAST)
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'blocksy_child_enqueue_styles' );

/**
 * Enqueue all child theme stylesheets.
 */
function blocksy_child_enqueue_styles() {
	$css_url  = BLOCKSY_CHILD_URL . 'assets/css/';
	$css_path = BLOCKSY_CHILD_PATH . 'assets/css/';

	// 1. Child theme style.css (theme header only — no rules).
	wp_enqueue_style(
		'blocksy-child-style',
		get_stylesheet_uri(),
		[ 'ct-main-styles' ],
		BLOCKSY_CHILD_VERSION
	);

	// 2. Base CSS — always loaded.
	$base_file = $css_path . 'base.css';
	if ( file_exists( $base_file ) ) {
		wp_enqueue_style(
			'blocksy-child-base',
			$css_url . 'base.css',
			[ 'blocksy-child-style' ],
			filemtime( $base_file )
		);
	}

	// 3. Component CSS — conditionally loaded per page type.
	blocksy_child_enqueue_component( 'header', $css_url, $css_path );

	// 3a. Header tweaks — Cam visual fixes 2026-04-29 (announcement-bar full-width
	//     at mobile + FiboSearch input padding alignment). See
	//     `assets/css/components/header-tweaks.css` for the full reasoning.
	blocksy_child_enqueue_component( 'header-tweaks', $css_url, $css_path );

	if ( function_exists( 'is_shop' ) ) {
		// woo-archive.css contains product card styles (full-width buttons, price
		// suffix). Load on any page that renders product cards: archive pages AND
		// homepage (product slider shortcode). One file, one source of truth.
		if ( is_shop() || is_product_category() || is_product_tag() || is_front_page() ) {
			blocksy_child_enqueue_component( 'woo-archive', $css_url, $css_path );

			// Shop-customizations JS — result count repositioning + AJAX update + sort label tweak.
			// Refactored 2026-05-08 from custom/shop-customizations.{php,js} (audit P1 Layer 1/2).
			if ( blocksy_child_feature_enabled( 'shop-customizations' ) ) {
				$shop_js = BLOCKSY_CHILD_PATH . 'assets/js/shop-customizations.js';
				if ( file_exists( $shop_js ) ) {
					wp_enqueue_script(
						'blocksy-child-shop-customizations',
						BLOCKSY_CHILD_URL . 'assets/js/shop-customizations.js',
						[],
						filemtime( $shop_js ),
						true
					);
				}
			}
		}
		// Category grid — any page with [product_categories] shortcode.
		blocksy_child_enqueue_component( 'woo-category-grid', $css_url, $css_path );
		if ( is_product() ) {
			blocksy_child_enqueue_component( 'woo-single', $css_url, $css_path );
			blocksy_child_enqueue_component( 'product-information', $css_url, $css_path );
			blocksy_child_enqueue_component( 'product-carousel-dots', $css_url, $css_path );

			// Gift card form CSS — only on gift product PDPs (PWGC plugin).
			if ( is_a( wc_get_product(), 'WC_Product_PW_Gift_Card' ) ) {
				blocksy_child_enqueue_component( 'gift-card-form', $css_url, $css_path );
			}

			// Product Information JS (PDP #2).
			$pi_js = BLOCKSY_CHILD_PATH . 'assets/js/product-information.js';
			if ( file_exists( $pi_js ) ) {
				wp_enqueue_script(
					'blocksy-child-product-information',
					BLOCKSY_CHILD_URL . 'assets/js/product-information.js',
					[],
					filemtime( $pi_js ),
					true
				);
				wp_localize_script( 'blocksy-child-product-information', 'bcProductInfo', [
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'bc_product_info' ),
				] );
			}

			// Product Carousel Dots JS — replaces overlapping arrows on
			// Related/Recently-Viewed with bottom dot pagination (CU-86exbzf96).
			$pcd_js = BLOCKSY_CHILD_PATH . 'assets/js/product-carousel-dots.js';
			if ( file_exists( $pcd_js ) ) {
				wp_enqueue_script(
					'blocksy-child-product-carousel-dots',
					BLOCKSY_CHILD_URL . 'assets/js/product-carousel-dots.js',
					[],
					filemtime( $pcd_js ),
					true
				);
			}
		}
		if ( is_checkout() ) {
			blocksy_child_enqueue_component( 'woo-checkout', $css_url, $css_path );

			// Auto-scroll to checkout error messages so customers notice them.
			$checkout_error_js = BLOCKSY_CHILD_PATH . 'assets/js/checkout-error-scroll.js';
			if ( file_exists( $checkout_error_js ) ) {
				wp_enqueue_script(
					'blocksy-child-checkout-error-scroll',
					BLOCKSY_CHILD_URL . 'assets/js/checkout-error-scroll.js',
					[],
					filemtime( $checkout_error_js ),
					true
				);
			}
		}

		// Checkout extras refactored 2026-05-08 from custom/ (audit P1 Layer 1/2).
		if ( is_checkout() ) {
			if ( blocksy_child_feature_enabled( 'checkout-order-summary' ) ) {
				blocksy_child_enqueue_component( 'checkout-order-summary', $css_url, $css_path );
				$cos_js = BLOCKSY_CHILD_PATH . 'assets/js/checkout-order-summary.js';
				if ( file_exists( $cos_js ) ) {
					wp_enqueue_script(
						'blocksy-child-checkout-order-summary',
						BLOCKSY_CHILD_URL . 'assets/js/checkout-order-summary.js',
						[],
						filemtime( $cos_js ),
						true
					);
				}
			}
			if ( blocksy_child_feature_enabled( 'checkout-step-form' ) ) {
				blocksy_child_enqueue_component( 'checkout-step-form', $css_url, $css_path );
			}
			if ( blocksy_child_feature_enabled( 'checkout-trust-badges' ) ) {
				blocksy_child_enqueue_component( 'checkout-trust-badges', $css_url, $css_path );
			}
		}
		// Cart page — woo-cart.css. Migrated 2026-06-03 from Blocksy code-snippet
		// #5 ("WebToffee Smart Coupons", wp_footer) so it no longer depends on the
		// code-snippets extension (dropped from blocksy_active_extensions). The CSS
		// only hides the BOGO giveaway "discount detail", so it is GUARDED to load
		// ONLY when Smart Coupons' giveaway feature is genuinely active, and can be
		// switched off site-wide via the `bbc_smart_coupons_cart_css_enabled` filter
		// (no theme edit needed). If the plugin is missing/inactive/half-installed
		// the file is never enqueued. See bbc_smart_coupons_cart_css_active().
		if ( is_cart() && bbc_smart_coupons_cart_css_active() ) {
			blocksy_child_enqueue_component( 'woo-cart', $css_url, $css_path );
		}
		// Removed dead enqueue refs 2026-05-07 (audit P0.2), re-add file + enqueue together if revived:
		// • is_account_page() → woo-account.css — file never existed, helper silently skipped
		// • unconditional → footer.css — same
	}

if ( is_front_page() || ( function_exists("is_page") && is_page(645912) ) ) {
		blocksy_child_enqueue_component( 'homepage', $css_url, $css_path );
		blocksy_child_enqueue_component( 'product-slider', $css_url, $css_path );

		// Hero Slider JS.
		$hero_js = BLOCKSY_CHILD_PATH . 'assets/js/hero-slider.js';
		if ( file_exists( $hero_js ) ) {
			wp_enqueue_script( 'blocksy-child-hero-slider', BLOCKSY_CHILD_URL . 'assets/js/hero-slider.js', [], filemtime( $hero_js ), true );
		}

		// Product Slider JS.
		$ps_js = BLOCKSY_CHILD_PATH . 'assets/js/product-slider.js';
		if ( file_exists( $ps_js ) ) {
			wp_enqueue_script( 'blocksy-child-product-slider', BLOCKSY_CHILD_URL . 'assets/js/product-slider.js', [], filemtime( $ps_js ), true );
		}
	}

	// 3a. Off-canvas shared panel styling -- loaded globally (all panels).
	blocksy_child_enqueue_component( 'offcanvas', $css_url, $css_path );

	// 3a-bis. Qty stepper — horizontal layout shared by PDP add-to-cart
	//          and mini cart drawer. Refactored 2026-05-08 (#54).
	blocksy_child_enqueue_component( 'qty-stepper', $css_url, $css_path );
	// 3b. Wishlist off-canvas -- loaded globally (header icon is on every page).
	blocksy_child_enqueue_component( 'wishlist-offcanvas', $css_url, $css_path );
	// 3e. Breadcrumb mobile horizontal scroll -- loaded globally
	//     (breadcrumbs render on PDP, archives, single posts, pages).
	blocksy_child_enqueue_component( 'breadcrumbs', $css_url, $css_path );

	// 3f. Smart Coupons BOGO giveaway popup styling -- loaded globally (the floating giveaway popup
	//     button can appear on any page where a BOGO offer applies). Migrated 2026-06-04 from
	//     Customizer Additional CSS into the child theme (runbook §13.18). Inert without BOGO markup.
	blocksy_child_enqueue_component( 'woo-bogo', $css_url, $css_path );

	// 3c. Wishlist off-canvas JS -- trigger, refresh, remove.
	$js_path = BLOCKSY_CHILD_PATH . 'assets/js/wishlist-offcanvas.js';
	if ( file_exists( $js_path ) ) {
		wp_enqueue_script(
			'blocksy-child-wishlist-offcanvas',
			BLOCKSY_CHILD_URL . 'assets/js/wishlist-offcanvas.js',
			[],
			filemtime( $js_path ),
			true
		);

		// Localize ajaxurl + nonce for the wishlist AJAX endpoint.
		// Endpoint: `wp_ajax_blocksy_child_wishlist_product` in inc/wishlist-offcanvas.php
		// validates the `_wpnonce` field against the `bc_wishlist` action.
		wp_localize_script( 'blocksy-child-wishlist-offcanvas', 'bcWishlist', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'bc_wishlist' ),
		] );
	}

	// 3c-bis. Cart off-canvas heading count sync (CU-86exbe71m).
	//         Keeps `.ct-cart-count-number` inside the drawer heading in
	//         lockstep with `.ct-dynamic-count-cart` after cart fragment
	//         refreshes (the heading itself is not a cart fragment).
	$cart_js_path = BLOCKSY_CHILD_PATH . 'assets/js/cart-offcanvas.js';
	if ( file_exists( $cart_js_path ) ) {
		wp_enqueue_script(
			'blocksy-child-cart-offcanvas',
			BLOCKSY_CHILD_URL . 'assets/js/cart-offcanvas.js',
			[ 'jquery' ],
			filemtime( $cart_js_path ),
			true
		);
	}

	// 3d. Wishlist flexy visibility sentinel — detects Blocksy CSS regressions
	//     in the suggested-products carousel inside the wishlist drawer.
	//     Logs to console + sets window.bcWishlistFlexyState. @date 2026-04-26
	$sentinel_path = BLOCKSY_CHILD_PATH . 'assets/js/wishlist-flexy-sentinel.js';
	if ( file_exists( $sentinel_path ) ) {
		wp_enqueue_script(
			'blocksy-child-wishlist-flexy-sentinel',
			BLOCKSY_CHILD_URL . 'assets/js/wishlist-flexy-sentinel.js',
			[],
			filemtime( $sentinel_path ),
			true
		);
	}

	// 3e. ARIA panel-sync — keep aria-expanded/aria-controls on offcanvas
	//     triggers in lockstep with each panel's .active class. WCAG 4.1.2.
	//     Audit P1 F9, 2026-05-08.
	$aria_sync = BLOCKSY_CHILD_PATH . 'assets/js/aria-panel-sync.js';
	if ( file_exists( $aria_sync ) ) {
		wp_enqueue_script(
			'blocksy-child-aria-panel-sync',
			BLOCKSY_CHILD_URL . 'assets/js/aria-panel-sync.js',
			[],
			filemtime( $aria_sync ),
			true
		);
	}

	// 4. Utilities CSS — always loaded (small file).
	$utilities_file = $css_path . 'utilities.css';
	if ( file_exists( $utilities_file ) ) {
		wp_enqueue_style(
			'blocksy-child-utilities',
			$css_url . 'utilities.css',
			[ 'blocksy-child-style' ],
			filemtime( $utilities_file )
		);
	}

	// 5. Client CSS — loaded LAST for highest cascade priority.
	blocksy_child_enqueue_client_assets();
}

/**
 * Enqueue a component CSS file from assets/css/components/.
 *
 * @param string $component Component name (e.g., 'header', 'woo-single').
 * @param string $css_url   URL to assets/css/ directory.
 * @param string $css_path  Filesystem path to assets/css/ directory.
 */
/**
 * Whether the Smart Coupons cart CSS (woo-cart.css) should load.
 *
 * Guards the BOGO giveaway "discount detail" hide migrated 2026-06-03 from Blocksy
 * code-snippet #5. Because this rule is client-specific (WebToffee Smart Coupons),
 * it is decoupled from the theme's always-on CSS and gated three ways:
 *
 *   1. On/off switch — the `bbc_smart_coupons_cart_css_enabled` filter (default true).
 *      Return false anywhere (mu-plugin, Customizer toggle, snippet) to disable it
 *      site-wide WITHOUT editing the child theme.
 *   2. Plugin-present check — class_exists() for the Smart Coupons giveaway class
 *      (mirrors the original snippet's guard). Covers "plugin not installed right":
 *      deactivated, half-installed, or a renamed build all fall through to false.
 *   3. Inert by design — even if it somehow loaded, the selector only matches markup
 *      that Smart Coupons emits, so it can never affect a cart without giveaways.
 *
 * @return bool
 */
function bbc_smart_coupons_cart_css_active() {
	// 1. Explicit on/off switch (default ON).
	if ( ! apply_filters( 'bbc_smart_coupons_cart_css_enabled', true ) ) {
		return false;
	}

	// 2. Only load when Smart Coupons' giveaway feature is genuinely present.
	return class_exists( 'Wt_Smart_Coupon_Giveaway_Product' )
		|| class_exists( 'Wt_Smart_Coupon' );
}

function blocksy_child_enqueue_component( $component, $css_url, $css_path ) {
	$file = $css_path . 'components/' . $component . '.css';

	if ( ! file_exists( $file ) ) {
		return;
	}

	wp_enqueue_style(
		'blocksy-child-' . $component,
		$css_url . 'components/' . $component . '.css',
		[ 'blocksy-child-style' ],
		filemtime( $file )
	);
}

/**
 * Enqueue CSS/JS for active client modules.
 */
function blocksy_child_enqueue_client_assets() {
	global $blocksy_child_active_clients;

	if ( empty( $blocksy_child_active_clients ) ) {
		return;
	}

	foreach ( $blocksy_child_active_clients as $client ) {
		$slug     = $client['slug'];
		$dir      = $client['dir'];
		$manifest = $client['manifest'];

		// Client CSS.
		if ( ! empty( $manifest['css'] ) ) {
			$css_file = $dir . $manifest['css'];
			if ( file_exists( $css_file ) ) {
				wp_enqueue_style(
					'blocksy-child-client-' . $slug,
					BLOCKSY_CHILD_URL . 'clients/' . $slug . '/' . $manifest['css'],
					[ 'blocksy-child-style' ],
					filemtime( $css_file )
				);
			}
		}

		// Client JS.
		if ( ! empty( $manifest['js'] ) ) {
			$js_file = $dir . $manifest['js'];
			if ( file_exists( $js_file ) ) {
				wp_enqueue_script(
					'blocksy-child-client-' . $slug,
					BLOCKSY_CHILD_URL . 'clients/' . $slug . '/' . $manifest['js'],
					[],
					filemtime( $js_file ),
					true
				);
			}
		}
	}
}
