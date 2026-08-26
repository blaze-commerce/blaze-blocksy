<?php
/**
 * Bonza (client-specific hooks).
 *
 * Loaded via manifest.json when this client is active.
 * Only add hooks specific to THIS client here.
 * Reusable hooks go in inc/woocommerce.php or inc/hooks.php.
 *
 * @package Blocksy_Child
 * @client  Bonza
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add client-specific hooks below.

/**
 * Login/signup modal skin (86eypb6k6).
 *
 * Restyles Blocksy Companion Pro's native account modal to match the Figma
 * "LOGIN/SIGNUP MODAL" component set. Own file, own handle, isolated from
 * bonza.css and every other client asset so concurrent client-module work
 * on this file cannot collide with it.
 */
function bonza_login_signup_modal_enqueue_assets() {
	$file = BLOCKSY_CHILD_PATH . 'clients/bonza/login-signup-modal.css';

	if ( ! file_exists( $file ) ) {
		return;
	}

	wp_enqueue_style(
		'bonza-login-signup-modal',
		BLOCKSY_CHILD_URL . 'clients/bonza/login-signup-modal.css',
		[ 'blocksy-child-style' ],
		filemtime( $file )
	);
}
add_action( 'wp_enqueue_scripts', 'bonza_login_signup_modal_enqueue_assets' );
