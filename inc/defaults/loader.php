<?php
/**
 * Defaults Loader — loads the opt-in `inc/defaults/` family.
 *
 * A site opts in from custom/custom.php:
 *
 *     add_filter( 'blocksy_child_defaults', function ( $d ) {
 *         return array_merge( $d, [ 'mobile-inputs', 'tap-targets' ] );
 *     } );
 *
 * or with a manifest `"defaults"` array, or the BLOCKSY_CHILD_DEFAULTS
 * constant. See docs/patterns/defaults.md for what each one changes.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once BLOCKSY_CHILD_PATH . 'inc/defaults/helpers.php';

foreach ( blocksy_child_defaults_known() as $name => $kind ) {
	if ( 'php' === $kind && blocksy_child_default_enabled( $name ) ) {
		blocksy_child_load_module( 'inc/defaults/' . $name . '.php' );
	}
}

// Priority 20: after blocksy_child_enqueue_styles() (10) has registered
// blocksy-child-style, which each component lists as its dependency.
add_action( 'wp_enqueue_scripts', function () {
	foreach ( blocksy_child_defaults_known() as $name => $kind ) {
		if ( 'css' === $kind && blocksy_child_default_enabled( $name ) ) {
			blocksy_child_enqueue_component(
				'defaults-' . $name,
				BLOCKSY_CHILD_URL . 'assets/css/',
				BLOCKSY_CHILD_PATH . 'assets/css/'
			);
		}
	}
}, 20 );
