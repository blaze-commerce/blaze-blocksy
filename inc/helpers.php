<?php
/**
 * Helper Functions — Utility functions used across the child theme.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if a plugin is active (works during plugins_loaded and later).
 *
 * Wrapper around WordPress's is_plugin_active() that handles the case
 * where the function isn't available yet (e.g., during early hooks).
 *
 * @param string $plugin Plugin basename (e.g., 'woocommerce/woocommerce.php').
 * @return bool
 */
function blocksy_child_is_plugin_active( $plugin ) {
	if ( function_exists( 'is_plugin_active' ) ) {
		return is_plugin_active( $plugin );
	}

	// Fallback: check active_plugins option directly.
	$active_plugins = (array) get_option( 'active_plugins', [] );
	return in_array( $plugin, $active_plugins, true );
}

/**
 * Render Blocksy's `cart-suggested-products` carousel for use in any
 * off-canvas drawer (mini cart empty state, wishlist drawer, etc.).
 *
 * WHY: Blocksy's flexy carousel template provides consistent typography,
 * SALE badges, hover effects, prices, and responsive behavior — matching
 * the design system. Reusing the template (instead of building our own
 * grid) keeps both drawers visually identical.
 *
 * BULLETPROOFING (defense-in-depth — every layer designed to keep working
 * even if every other layer breaks):
 *
 *   LAYER 1 — Cart-fragments class rename
 *     The wrapper class `ct-suggested-products--mini-cart` is registered
 *     by Blocksy as a WooCommerce cart-fragment selector
 *     (`?wc-ajax=get_refreshed_fragments` replaces matching elements with
 *     empty content on every cart change). We rename the wrapper class
 *     to a unique, child-theme-namespaced class so cart-fragments AJAX
 *     cannot match and wipe it.
 *
 *   LAYER 2 — Native flexy carousel preserved (do NOT strip flexy hooks)
 *     We keep `flexy-container`, `data-flexy="no"`, and `data-autoplay`
 *     untouched so Blocksy's `flexy.min.js` auto-initializes the
 *     carousel and produces the same layout as Image #1 (mini cart with
 *     items). To disable auto-rotation, set the Blocksy customizer
 *     setting `mini_cart_suggested_products_autoplay = "no"` (the
 *     correct, supported lever) — never strip the attribute. Earlier
 *     attempt at stripping produced a raw stacked layout (CU-86exbd883
 *     audit on 2026-04-28).
 *
 *   LAYER 3 — ID validation
 *     Always validates IDs against `wc_get_product()` before rendering —
 *     Blocksy's template crashes on invalid IDs.
 *
 *   LAYER 4 — Bestsellers fallback (caller side)
 *     If caller's IDs are empty/invalid, we fall back to top 4
 *     bestsellers — prevents blank empty states.
 *
 *   LAYER 5 — Output validation + simple-grid fallback (renderer side)
 *     We wrap `blocksy_render_view()` in try/catch and validate the
 *     returned HTML actually contains product markup. If Blocksy's
 *     template throws, returns empty, returns broken HTML, or the
 *     template file is missing, we render our OWN simple HTML grid
 *     (no Blocksy dependency at all). This is the ultimate safety net:
 *     even if Blocksy is uninstalled or the plugin path changes
 *     completely, the drawers still show products.
 *
 *   LAYER 6 — Version sentinel (separate file)
 *     `clients/byronbay/byronbay.php` watches Blocksy theme + Blocksy
 *     Companion Pro version strings via `admin_init` and surfaces an
 *     admin notice when either changes — so the next time Blocksy
 *     updates, the team is prompted to QA the drawers.
 *
 *   LAYER 7 — Runtime visibility sentinel (separate file)
 *     `assets/js/wishlist-flexy-sentinel.js` checks the rendered drawer
 *     on every open and logs `console.error('[BBC sentinel]')` if items
 *     are zero-dim, off-screen, or transformed off-axis.
 *
 * @date 2026-04-26 — extracted as shared helper after the cart-fragments
 *                    wipe regression was traced; bulletproofed to never
 *                    show a blank drawer on any future Blocksy update.
 *
 * @param array  $product_ids   Product IDs to render. If empty, falls
 *                              back to top 4 bestsellers.
 * @param string $unique_class  Required: a child-theme-namespaced class
 *                              like 'bc-wishlist-suggested-grid' that
 *                              cart-fragments AJAX cannot match.
 * @return string Rendered HTML, or empty string if the catalog has no
 *                 published products at all (genuinely nothing to show).
 */
function bc_render_blocksy_suggested_carousel( $product_ids, $unique_class ) {
	// ---------------------------------------------------------------------
	// LAYER 4: Caller-side fallback chain — caller IDs → bestsellers.
	// ---------------------------------------------------------------------
	$valid_ids = bc_resolve_suggested_product_ids( $product_ids, 4 );

	if ( empty( $valid_ids ) ) {
		// Catalog has no published products — genuinely nothing to render.
		return '';
	}

	// ---------------------------------------------------------------------
	// Defensive: caller MUST provide a unique class — without it,
	// WC cart-fragments AJAX will silently wipe our element on
	// every cart change (root cause of 2026-04-26 regression).
	// ---------------------------------------------------------------------
	if ( ! $unique_class ) {
		error_log( '[BBC] bc_render_blocksy_suggested_carousel called without unique_class — falling back to simple grid to avoid cart-fragments wipe.' );
		return bc_render_simple_product_grid_fallback( $valid_ids, 'bc-suggested-grid-fallback' );
	}

	// ---------------------------------------------------------------------
	// LAYER 5a: Try to render via Blocksy's template.
	// ---------------------------------------------------------------------
	$html = bc_try_render_blocksy_template( $valid_ids );

	// ---------------------------------------------------------------------
	// LAYER 5b: Validate Blocksy output. If empty, missing product markup,
	// or any signal of a broken render, fall back to simple grid.
	// ---------------------------------------------------------------------
	if ( ! bc_blocksy_template_output_is_valid( $html, $valid_ids ) ) {
		error_log( '[BBC] Blocksy suggested-products template produced invalid output — falling back to simple grid. Class: ' . $unique_class );
		return bc_render_simple_product_grid_fallback( $valid_ids, $unique_class );
	}

	// ---------------------------------------------------------------------
	// LAYER 1: Rename the wrapper class so WC cart-fragments AJAX cannot
	// match and wipe our element. WooCommerce registers
	// `[class*="ct-suggested-products--mini-cart"]` as a cart fragment;
	// renaming this single class to a `bc-*` namespace escapes the wipe.
	//
	// IMPORTANT — we DO NOT strip `flexy-container`, `data-flexy`, or
	// `data-autoplay` from the markup. Blocksy's flexy.min.js auto-init
	// depends on those, and stripping them produced a raw stacked layout
	// (Images #3 and #4 from CU-86exbd883 audit on 2026-04-28). The
	// correct way to disable autoplay is the customizer setting
	// `mini_cart_suggested_products_autoplay = "no"` — see memory file
	// `blocksy-drawer-suggested-products-architecture.md`.
	// ---------------------------------------------------------------------
	// Replace with our cart-fragments-safe class PLUS keep `ct-suggested-products`
	// (without the `--mini-cart` suffix). Why: Blocksy's flexy.js binds prev/next
	// arrow click handlers via `closest([class*="ct-suggested-products"])` —
	// without that substring on our wrapper the arrows are decorative-only. The
	// cart-fragments selector `[class*="ct-suggested-products--mini-cart"]`
	// requires the `--mini-cart` substring, so dropping it escapes the wipe while
	// keeping JS + customizer CSS hooks intact (LAYER 1 + LAYER 9, see memory).
	$html = str_replace(
		'ct-suggested-products--mini-cart',
		$unique_class . ' ct-suggested-products',
		$html
	);

	return $html;
}


/**
 * Resolve the final list of product IDs to render.
 *
 * Caller IDs are validated first; if none are valid, we fall back to
 * top bestsellers. This is the one fallback layer that runs BEFORE we
 * even attempt to render anything.
 *
 * @param array $product_ids Caller-provided IDs (may be empty).
 * @param int   $limit       Bestsellers fallback count.
 * @return array Validated, absint'd, array_values'd list of product IDs.
 */
function bc_resolve_suggested_product_ids( $product_ids, $limit = 4 ) {
	$valid_ids = [];

	if ( ! empty( $product_ids ) && function_exists( 'wc_get_product' ) ) {
		$valid_ids = array_values( array_filter(
			array_map( 'absint', $product_ids ),
			function ( $id ) {
				return $id > 0 && wc_get_product( $id );
			}
		) );
	}

	if ( empty( $valid_ids ) && function_exists( 'wc_get_products' ) ) {
		// Cache the bestsellers fallback for 1 hour. Prevents a popularity-sort
		// product query on every empty-drawer open. Cleared automatically by
		// WP/WC cache flush + our save_post hook below.
		$cache_key   = 'bc_suggested_bestsellers_' . (int) $limit;
		$bestsellers = get_transient( $cache_key );
		if ( false === $bestsellers ) {
			$bestsellers = wc_get_products( [
				'status'  => 'publish',
				'limit'   => (int) $limit,
				'orderby' => 'popularity',
				'return'  => 'ids',
			] );
			if ( ! is_array( $bestsellers ) ) {
				$bestsellers = [];
			}
			set_transient( $cache_key, $bestsellers, HOUR_IN_SECONDS );
		}
		if ( ! empty( $bestsellers ) ) {
			$valid_ids = array_map( 'absint', $bestsellers );
		}
	}

	return $valid_ids;
}


/**
 * Attempt to render Blocksy's cart-suggested-products template.
 *
 * Wrapped in try/catch so a fatal in the template never bubbles up
 * and breaks the drawer. Returns empty string on any failure — the
 * caller will detect that and fall back to the simple grid.
 *
 * @param array $valid_ids Pre-validated product IDs.
 * @return string Rendered HTML, or empty string on any failure.
 */
function bc_try_render_blocksy_template( $valid_ids ) {
	$template_path = ABSPATH . 'wp-content/plugins/blocksy-companion-pro/framework/premium/extensions/woocommerce-extra/features/cart-suggested-products/views/suggested-products.php';

	if ( ! file_exists( $template_path ) ) {
		error_log( '[BBC] Blocksy cart-suggested-products template missing — ' . $template_path );
		return '';
	}

	if ( ! function_exists( 'blocksy_render_view' ) ) {
		error_log( '[BBC] blocksy_render_view() unavailable — Blocksy theme inactive?' );
		return '';
	}

	// Make sure flexy CSS is enqueued (Blocksy template depends on it).
	if ( function_exists( 'wp_enqueue_style' ) ) {
		wp_enqueue_style( 'ct-flexy-styles' );
	}

	try {
		$html = blocksy_render_view(
			$template_path,
			[
				'added_products' => $valid_ids,
				'prefix'         => 'mini_cart_suggested_',
			]
		);
	} catch ( \Throwable $e ) {
		error_log( '[BBC] blocksy_render_view threw: ' . $e->getMessage() );
		return '';
	}

	return is_string( $html ) ? $html : '';
}


/**
 * Validate that Blocksy's template output looks correct.
 *
 * The template emits a `<ul class="flexy-items">` containing one `<li>`
 * per product. If the output is empty, missing the wrapper, or has
 * fewer items than we expect, treat it as broken and let the caller
 * fall back to the simple grid.
 *
 * We don't strictly require N items (Blocksy may de-duplicate, etc.) —
 * we just require AT LEAST ONE item plus the wrapper class.
 *
 * @param string $html      Blocksy template output.
 * @param array  $valid_ids The IDs we passed in.
 * @return bool True if output looks valid; false if we should fall back.
 */
function bc_blocksy_template_output_is_valid( $html, $valid_ids ) {
	if ( ! is_string( $html ) || trim( $html ) === '' ) {
		return false;
	}

	// Wrapper class must be present (we rename it next; absence means
	// Blocksy changed the markup completely).
	if ( strpos( $html, 'ct-suggested-products--mini-cart' ) === false ) {
		return false;
	}

	// Must contain at least one rendered product item.
	if ( strpos( $html, 'flexy-item' ) === false ) {
		return false;
	}

	return true;
}


/**
 * Self-contained simple product grid renderer — the ultimate fallback.
 *
 * Used when:
 *   • Blocksy theme is inactive
 *   • Blocksy Companion Pro template path has changed
 *   • blocksy_render_view() throws or returns broken HTML
 *   • Caller forgot to pass a unique_class (we still want SOMETHING to render)
 *
 * Outputs a simple, dependency-free HTML grid:
 *   <div class="$unique_class bc-fallback-grid">
 *     <a class="bc-fallback-item" href>
 *       <div class="bc-fallback-image"><img></div>
 *       <span class="bc-fallback-title">…</span>
 *       <span class="bc-fallback-price">…</span>
 *     </a>
 *     …
 *   </div>
 *
 * The wrapper class is whatever the caller provides (still namespaced
 * to escape cart-fragments AJAX). The `.bc-fallback-grid` class lets
 * the CSS in `wishlist-offcanvas.css` style the fallback to LOOK like
 * the Blocksy template — visually consistent for users.
 *
 * @param array  $valid_ids    Pre-validated product IDs.
 * @param string $unique_class Wrapper class (cart-fragments-safe namespace).
 * @return string Self-contained HTML, or '' if WooCommerce APIs missing.
 */
function bc_render_simple_product_grid_fallback( $valid_ids, $unique_class ) {
	if ( empty( $valid_ids ) || ! function_exists( 'wc_get_products' ) ) {
		return '';
	}

	$products = wc_get_products( [
		'include' => array_map( 'absint', $valid_ids ),
		'limit'   => count( $valid_ids ),
		'status'  => 'publish',
		'orderby' => 'post__in',
	] );

	if ( empty( $products ) ) {
		return '';
	}

	$class_attr = esc_attr( trim( $unique_class . ' bc-fallback-grid' ) );

	ob_start();
	?>
	<div class="<?php echo $class_attr; ?>" data-bc-fallback="1">
		<?php foreach ( $products as $product ) :
			if ( ! is_object( $product ) || ! method_exists( $product, 'get_permalink' ) ) {
				continue;
			}
			$permalink = $product->get_permalink();
			$image     = $product->get_image( 'woocommerce_gallery_thumbnail' );
			$title     = $product->get_name();
			$price     = $product->get_price_html();
			?>
			<a href="<?php echo esc_url( $permalink ); ?>" class="bc-fallback-item">
				<div class="bc-fallback-image"><?php echo $image; ?></div>
				<div class="bc-fallback-info">
					<span class="bc-fallback-title"><?php echo esc_html( $title ); ?></span>
					<span class="bc-fallback-price"><?php echo wp_kses_post( $price ); ?></span>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Bust the bestsellers transient when products change. Keeps the
 * empty-drawer carousel reasonably fresh without per-request DB hits.
 *
 * @date 2026-04-28
 */
add_action( 'save_post_product', 'bc_bust_suggested_bestsellers_cache' );
add_action( 'woocommerce_product_set_stock', 'bc_bust_suggested_bestsellers_cache' );

function bc_bust_suggested_bestsellers_cache() {
	delete_transient( 'bc_suggested_bestsellers_4' );
}

// ──── BC P1 audit 2026-05-08: bc_get_recently_viewed_cookie helper ────
/**
 * Parse the WooCommerce 'recently viewed' cookie into a clean ID list.
 *
 * Consolidates the duplicate cookie-parsing pattern from
 * inc/recently-viewed.php and inc/mini-cart-empty.php into one helper.
 *
 * @return int[] Recently-viewed product IDs (oldest → newest as stored).
 *               Empty array if cookie missing or empty.
 */
if ( ! function_exists( 'bc_get_recently_viewed_cookie' ) ) {
	function bc_get_recently_viewed_cookie() {
		if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) {
			return [];
		}
		return array_filter(
			array_map(
				'absint',
				explode( '|', sanitize_text_field( wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) )
			)
		);
	}
}

