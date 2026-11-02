<?php
/**
 * v1 global functions, kept as deprecated aliases for site code or templates that call them directly. Loaded by
 * Module::boot(); legacy-v1 skips the v1 file while the module is on.
 *
 * @deprecated 2.0.0 Use the booted module (see README.md).
 * @package Blocksy_Child
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- v1 function names, kept for v1 callers.

if ( ! function_exists( 'bc_track_product_view' ) ) {
	/**
	 * Did the server-side cookie tracking in v1. Tracking now happens in the browser, so this does nothing.
	 *
	 * @deprecated 2.0.0
	 * @return void
	 */
	function bc_track_product_view() {
		_deprecated_function( __FUNCTION__, '2.0.0' );
	}
}

if ( ! function_exists( 'bc_render_recently_viewed_products' ) ) {
	/**
	 * Prints the empty recently viewed container (once per request, as in v1).
	 *
	 * @deprecated 2.0.0 Use module( 'recently-viewed' )->render().
	 * @return void
	 */
	function bc_render_recently_viewed_products() {
		_deprecated_function( __FUNCTION__, '2.0.0', "Blaze\\Child\\Api\\module( 'recently-viewed' )->render()" );
		$module = \Blaze\Child\Modules\RecentlyViewed\Module::booted();
		if ( $module ) {
			$module->render();
		}
	}
}
