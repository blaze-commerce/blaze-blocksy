<?php
/**
 * Off-Canvas Bottom Links Feature
 *
 * Registers a custom menu location for client-editable links at the bottom
 * of the mobile off-canvas panel (Contact, Downloads, Wishlist, etc.).
 * Also adds mega-section-title support for menu section headers.
 *
 * @package BlocksyChild
 * @since 1.70.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Register the off-canvas bottom links menu location.
 *
 * Allows site admins to manage mobile off-canvas bottom links
 * via Appearance > Menus without touching code.
 */
add_action( 'after_setup_theme', function () {
	register_nav_menus( array(
		'offcanvas_bottom_links' => __( 'Off-Canvas Bottom Links (Mobile)', 'blaze-blocksy' ),
	) );
}, 20 );

/**
 * Render off-canvas bottom links in the mobile panel.
 *
 * Uses a custom inline Walker to output flat <a> elements (no <li> wrappers)
 * inside a .offcanvas-bottom-links container, matching the off-canvas design.
 *
 * Hooked to Blocksy's offcanvas mobile bottom area.
 */
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

/**
 * Mega-menu section headers from Description field.
 *
 * When a menu item has the CSS class "mega-section-header", its Description
 * field value is rendered as a <span class="mega-section-title"> before the
 * menu item output. This provides visual section grouping in the off-canvas menu.
 */
add_filter( 'walker_nav_menu_start_el', function ( $item_output, $item, $depth, $args ) {
	if ( is_array( $item->classes ) && preg_grep( '/mega-section-header/', $item->classes ) ) {
		if ( ! empty( $item->description ) ) {
			$title       = esc_html( $item->description );
			$item_output = '<span class="mega-section-title">' . $title . '</span>' . $item_output;
		}
	}
	return $item_output;
}, 10, 4 );
