<?php
/**
 * v1 global helpers, kept because v1's mini cart and wishlist panel code (inc/woocommerce.php and the wishlist
 * panel file) calls blocksy_child_offcanvas_icon_markup(). Each delegates to the booted module, so the markup is
 * always sanitised. Loaded by Module::boot(); legacy-v1 skips the v1 file while the module is on.
 *
 * @deprecated 2.0.0 Site code should call markup() on the booted module (see README.md).
 * @package Blocksy_Child
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- v1 function names, kept for v1 callers.

if ( ! function_exists( 'blocksy_child_offcanvas_icon_options' ) ) {
	/**
	 * Icon key => v1 option name.
	 *
	 * @return array<string,string>
	 */
	function blocksy_child_offcanvas_icon_options() {
		return \Blaze\Child\Modules\OffcanvasIcons\Module::OPTIONS;
	}
}

if ( ! function_exists( 'blocksy_child_offcanvas_icon_id' ) ) {
	/**
	 * Attachment id configured for an icon, or 0.
	 *
	 * @param string $which 'minicart' or 'wishlist'.
	 * @return int
	 */
	function blocksy_child_offcanvas_icon_id( $which ) {
		$module = \Blaze\Child\Modules\OffcanvasIcons\Module::booted();
		return $module ? $module->icon_id( (string) $which ) : 0;
	}
}

if ( ! function_exists( 'blocksy_child_offcanvas_icon_markup' ) ) {
	/**
	 * Sanitised icon markup, or '' (the caller keeps its default icon).
	 *
	 * @param string $which       'minicart' or 'wishlist'.
	 * @param string $extra_class Class(es) added after `ct-icon`.
	 * @return string
	 */
	function blocksy_child_offcanvas_icon_markup( $which, $extra_class = '' ) {
		$module = \Blaze\Child\Modules\OffcanvasIcons\Module::booted();
		return $module ? $module->markup( (string) $which, (string) $extra_class ) : '';
	}
}

if ( ! function_exists( 'blocksy_child_register_offcanvas_icon_controls' ) ) {
	/**
	 * Registers the Customizer controls. Not hooked: the module hooks its own register_controls() method.
	 *
	 * @param \WP_Customize_Manager $wp_customize Customizer manager.
	 * @return void
	 */
	function blocksy_child_register_offcanvas_icon_controls( $wp_customize ) {
		$module = \Blaze\Child\Modules\OffcanvasIcons\Module::booted();
		if ( $module ) {
			$module->register_controls( $wp_customize );
		}
	}
}

// phpcs:enable
