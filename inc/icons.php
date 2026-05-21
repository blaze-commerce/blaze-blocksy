<?php
/**
 * Icon Shortcode — Centralized SVG icon registry.
 *
 * Usage: [bc_icon name="verified" size="32"]
 *
 * Keeps SVGs out of post content. Change once, updates everywhere.
 *
 * @package Blocksy_Child
 * @date    2026-04-07
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry of named SVG icons.
 *
 * Each icon is a callable that receives $size and returns SVG markup.
 * viewBox stays fixed; width/height scale via the size attribute.
 *
 * @return array<string, callable>
 */
function blocksy_child_get_icons() {
	return [
		'verified' => function ( $size ) {
			return '<svg xmlns="http://www.w3.org/2000/svg" width="' . esc_attr( $size ) . '" height="' . esc_attr( $size ) . '" viewBox="0 0 40 40" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M32.8064 25.3046C34.706 24.2975 36 22.2999 36 20C36 17.7001 34.706 15.7025 32.8064 14.6954C33.4375 12.64 32.94 10.3125 31.3137 8.68624C29.6875 7.05999 27.3599 6.56244 25.3046 7.19358C24.2975 5.29396 22.2998 4 20 4C17.7001 4 15.7025 5.29398 14.6954 7.19363C12.64 6.56251 10.3125 7.06007 8.68629 8.68631C7.06005 10.3126 6.56249 12.6401 7.19361 14.6954C5.29397 15.7025 4 17.7001 4 20C4 22.2999 5.29398 24.2975 7.19361 25.3046C6.56247 27.3599 7.06003 29.6875 8.68628 31.3137C10.3125 32.94 12.6401 33.4375 14.6954 32.8064C15.7025 34.706 17.7001 36 20 36C22.2999 36 24.2975 34.706 25.3046 32.8064C27.3599 33.4375 29.6875 32.9399 31.3137 31.3137C32.9399 29.6874 33.4375 27.3599 32.8064 25.3046ZM27.7131 16.3823C28.2004 15.7123 28.0522 14.7742 27.3823 14.2869C26.7123 13.7996 25.7742 13.9478 25.2869 14.6177L18.3194 24.1981L14.5607 20.4393C13.9749 19.8536 13.0251 19.8536 12.4393 20.4393C11.8536 21.0251 11.8536 21.9749 12.4393 22.5607L17.4393 27.5607C17.7495 27.8709 18.18 28.0297 18.6173 27.9954C19.0547 27.9611 19.4551 27.737 19.7131 27.3823L27.7131 16.3823Z" fill="#746A5F"/></svg>';
		},
	];
}

/**
 * [bc_icon] shortcode handler.
 *
 * @param array $atts Shortcode attributes.
 * @return string SVG markup wrapped in a div.
 */
function blocksy_child_icon_shortcode( $atts ) {
	$atts = shortcode_atts( [
		'name' => '',
		'size' => '32',
	], $atts, 'bc_icon' );

	$icons = blocksy_child_get_icons();
	$name  = sanitize_key( $atts['name'] );
	$size  = absint( $atts['size'] );

	if ( empty( $name ) || ! isset( $icons[ $name ] ) ) {
		return '';
	}

	return '<div class="bc-icon bc-icon--' . esc_attr( $name ) . '">' . $icons[ $name ]( $size ) . '</div>';
}
add_shortcode( 'bc_icon', 'blocksy_child_icon_shortcode' );
