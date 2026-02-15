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

// === Assets ===
// Each feature gets its own file in css/ or js/. custom.css/custom.js are for quick one-offs only.
// This keeps custom.php as a thin loader — just append enqueue lines per feature.
add_action( 'wp_enqueue_scripts', function () {
	$dir = BLAZE_BLOCKSY_PATH . '/custom';
	$uri = BLAZE_BLOCKSY_URL . '/custom';

	// Quick one-off overrides (use sparingly — prefer dedicated files below).
	if ( file_exists( "$dir/custom.css" ) && filesize( "$dir/custom.css" ) > 0 ) {
		wp_enqueue_style( 'blaze-custom', "$uri/custom.css", [], filemtime( "$dir/custom.css" ) );
	}
	if ( file_exists( "$dir/custom.js" ) && filesize( "$dir/custom.js" ) > 0 ) {
		wp_enqueue_script( 'blaze-custom', "$uri/custom.js", [ 'jquery' ], filemtime( "$dir/custom.js" ), true );
	}

	// --- Feature stylesheets (add one line per feature) ---
	// wp_enqueue_style( 'blaze-custom-header', "$uri/css/header.css", [], filemtime( "$dir/css/header.css" ) );

	// --- Feature scripts (add one line per feature) ---
	// wp_enqueue_script( 'blaze-custom-header', "$uri/js/header.js", [ 'jquery' ], filemtime( "$dir/js/header.js" ), true );
} );

// === PHP Modules ===
// Each feature gets its own file. Just append require_once lines here.
// require_once __DIR__ . '/block-currency-visibility.php';
// require_once __DIR__ . '/global-block-config.php';
