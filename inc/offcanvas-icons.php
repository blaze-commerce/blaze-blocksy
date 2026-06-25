<?php
/**
 * Off-canvas Panel Icons — Customizer image/SVG control for the drawer headings.
 *
 * Adds an "Off-canvas Panel Icons" Customizer section with two media-upload
 * controls so an admin can swap the icon shown in the heading of the minicart
 * drawer and the wishlist drawer — WITHOUT touching code or a /custom overlay.
 *
 * Scope: OFF-CANVAS PANEL ICONS ONLY. These are deliberately INDEPENDENT of the
 * header trigger icons (the little cart/wishlist icons in the header bar), which
 * are already configurable via Blocksy's native Customizer
 * (Header > Cart / Wishlist > Icon Source: Custom). The drawer heading icon and
 * the header icon can be different by design, so each is controlled separately.
 *
 * What this drives (the previously hard-coded drawer icons):
 *   - minicart drawer heading icon  → inc/woocommerce.php (`.ct-cart-panel-icon`)
 *   - wishlist drawer heading icon  → inc/wishlist-offcanvas.php (`.ct-wishlist-panel-icon`)
 *
 * Storage: attachment ID in wp_options (`blocksy_child_minicart_panel_icon_id`,
 * `blocksy_child_wishlist_panel_icon_id`). Empty = keep the theme default (so
 * clients that don't set one — e.g. byronbay — are unaffected).
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map of supported off-canvas icons => the wp_options key holding the attachment ID.
 *
 * @return array<string,string>
 */
function blocksy_child_offcanvas_icon_options() {
	return array(
		'minicart' => 'blocksy_child_minicart_panel_icon_id',
		'wishlist' => 'blocksy_child_wishlist_panel_icon_id',
	);
}

/**
 * Attachment ID configured for a given off-canvas icon ('minicart' | 'wishlist').
 *
 * @param string $which Icon key.
 * @return int Attachment ID, or 0 when unset.
 */
function blocksy_child_offcanvas_icon_id( $which ) {
	$options = blocksy_child_offcanvas_icon_options();

	if ( ! isset( $options[ $which ] ) ) {
		return 0;
	}

	return (int) get_option( $options[ $which ], 0 );
}

/**
 * Inline markup for a configured off-canvas panel icon, or '' when none is set.
 *
 * SVG attachments are inlined (so they inherit Blocksy's `ct-icon` sizing/color
 * via `currentColor`, exactly like the stock icons). Raster attachments fall
 * back to an <img>. Returns '' when no icon is configured — callers then keep
 * their own default markup.
 *
 * @param string $which       Icon key ('minicart' | 'wishlist').
 * @param string $extra_class Extra class(es) to add alongside `ct-icon`.
 * @return string Icon HTML, or '' when unset/unreadable.
 */
function blocksy_child_offcanvas_icon_markup( $which, $extra_class = '' ) {
	$id = blocksy_child_offcanvas_icon_id( $which );

	if ( ! $id ) {
		return '';
	}

	$classes = trim( 'ct-icon ' . $extra_class );
	$file    = get_attached_file( $id );
	$mime    = get_post_mime_type( $id );

	if ( $file && 'image/svg+xml' === $mime && is_readable( $file ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme asset, not a remote fetch.
		$svg = file_get_contents( $file );

		if ( ! $svg ) {
			return '';
		}

		// Strip XML prolog / doctype / comments so the SVG inlines cleanly.
		$svg = preg_replace( '/<\?xml.*?\?>/is', '', $svg );
		$svg = preg_replace( '/<!DOCTYPE.*?>/is', '', $svg );
		$svg = preg_replace( '/<!--.*?-->/is', '', $svg );
		$svg = trim( (string) $svg );

		if ( '' === $svg || false === stripos( $svg, '<svg' ) ) {
			return '';
		}

		// Merge our classes into the root <svg> (prepend if it already has class=).
		if ( preg_match( '/<svg\b[^>]*\bclass="/i', $svg ) ) {
			$svg = preg_replace( '/(<svg\b[^>]*\bclass=")/i', '${1}' . esc_attr( $classes ) . ' ', $svg, 1 );
		} else {
			$svg = preg_replace( '/<svg\b/i', '<svg class="' . esc_attr( $classes ) . '"', $svg, 1 );
		}

		return (string) $svg;
	}

	$url = wp_get_attachment_image_url( $id, 'full' );

	if ( ! $url ) {
		return '';
	}

	return '<img class="' . esc_attr( $classes ) . '" src="' . esc_url( $url ) . '" alt="" />';
}

/**
 * Register the "Off-canvas Panel Icons" Customizer section + media controls.
 *
 * Stored as `option` (site-wide), sanitized to an attachment ID. The native
 * WP_Customize_Media_Control gives the same upload UX as Blocksy's custom-icon
 * field. Intentionally separate from the header trigger icons.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function blocksy_child_register_offcanvas_icon_controls( $wp_customize ) {
	$wp_customize->add_section(
		'blocksy_child_offcanvas_icons',
		array(
			'title'       => __( 'Off-canvas Panel Icons (Child Theme)', 'blocksy-child' ),
			'priority'    => 160,
			'description' => __( 'Upload a custom image/SVG for the icon shown in the heading of the minicart drawer and the wishlist drawer. These are separate from the header bar icons (set those under Header > Cart / Wishlist). Leave empty to keep the theme default.', 'blocksy-child' ),
		)
	);

	$controls = array(
		'minicart' => __( 'Minicart Drawer Icon', 'blocksy-child' ),
		'wishlist' => __( 'Wishlist Drawer Icon', 'blocksy-child' ),
	);

	$options = blocksy_child_offcanvas_icon_options();

	foreach ( $controls as $key => $label ) {
		$setting = $options[ $key ];

		$wp_customize->add_setting(
			$setting,
			array(
				'type'              => 'option',
				'capability'        => 'manage_options',
				'default'           => '',
				'sanitize_callback' => 'absint',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				$setting,
				array(
					'label'       => $label,
					'description' => __( 'SVG recommended (square viewBox, ~24×24). Falls back to the theme default when empty.', 'blocksy-child' ),
					'section'     => 'blocksy_child_offcanvas_icons',
					'mime_type'   => 'image',
				)
			)
		);
	}
}
add_action( 'customize_register', 'blocksy_child_register_offcanvas_icon_controls' );
