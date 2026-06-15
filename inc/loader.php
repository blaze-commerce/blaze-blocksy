<?php
/**
 * Module Loader — Bootstraps all child theme modules.
 *
 * Loads core modules with error handling, conditionally loads
 * WooCommerce modules based on client feature flags, and scans
 * clients/ for active client modules.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Safely load a child theme module file.
 *
 * @param string $file Relative path from child theme root (e.g., 'inc/helpers.php').
 * @return bool True if loaded successfully, false otherwise.
 */
function blocksy_child_load_module( $file ) {
	$path = BLOCKSY_CHILD_PATH . $file;

	if ( ! file_exists( $path ) ) {
		error_log( '[blocksy-child] Module not found: ' . $file );
		return false;
	}

	try {
		require_once $path;
		return true;
	} catch ( \Throwable $e ) {
		error_log( '[blocksy-child] Module error in ' . $file . ': ' . $e->getMessage() );
		return false;
	}
}

/**
 * Check if a feature is enabled for the active client.
 *
 * Features are listed in the client's manifest.json under the "features" key.
 * If no features key exists, ALL features are enabled (backward compatible).
 *
 * @param string $feature Feature name (e.g., 'wishlist-offcanvas', 'product-tabs').
 * @return bool
 */
function blocksy_child_feature_enabled( $feature ) {
	global $blocksy_child_active_clients;

	if ( empty( $blocksy_child_active_clients ) ) {
		return true; // No client loaded yet — enable all features.
	}

	foreach ( $blocksy_child_active_clients as $client ) {
		if ( ! isset( $client['manifest']['features'] ) ) {
			return true; // No features key — enable all (backward compatible).
		}
		return in_array( $feature, $client['manifest']['features'], true );
	}

	return true;
}

// --- Core Modules (always loaded) ---
blocksy_child_load_module( 'inc/helpers.php' );
blocksy_child_load_module( 'inc/enqueue.php' );
blocksy_child_load_module( 'inc/hooks.php' );
blocksy_child_load_module( 'inc/icons.php' );
blocksy_child_load_module( 'inc/carousel.php' );

// --- Client Modules (load BEFORE WooCommerce modules so feature flags are available) ---
blocksy_child_load_clients();

// --- WooCommerce Modules (conditional on WC + feature flags) ---
if ( blocksy_child_is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	// Core WooCommerce hooks — always loaded when WC is active.
	blocksy_child_load_module( 'inc/woocommerce.php' );
	blocksy_child_load_module( 'inc/gift-card-checkout-compat.php' );


	// Optional modules — controlled by client manifest "features" array.
	// If no "features" key in manifest, all modules load (backward compatible).
	$optional_modules = [
		'wishlist-offcanvas'     => 'inc/wishlist-offcanvas.php',
		'product-tabs'           => 'inc/product-tabs.php',
		'product-information'    => 'inc/product-information.php',
		'recently-viewed'        => 'inc/recently-viewed.php',
		'mini-cart-empty'        => 'inc/mini-cart-empty.php',
		'product-slider'         => 'inc/product-slider.php',
		'search-restructure'     => 'inc/search-restructure.php',
		'checkout-customization'   => 'inc/checkout-customization.php',
		// Refactored 2026-05-08 from custom/ to inc/ (audit P1 Layer 1/2 architecture).
		'shop-customizations'      => 'inc/shop-customizations.php',
		'checkout-trust-badges'    => 'inc/checkout-trust-badges.php',
		'checkout-order-summary'   => 'inc/checkout-order-summary.php',
		'checkout-step-form'       => 'inc/checkout-step-form.php',
	];

	foreach ( $optional_modules as $feature => $file ) {
		if ( blocksy_child_feature_enabled( $feature ) ) {
			blocksy_child_load_module( $file );
		}
	}
}

/**
 * Scan clients/ directory for active client modules and load them.
 */
function blocksy_child_load_clients() {
	$clients_dir = BLOCKSY_CHILD_PATH . 'clients/';

	if ( ! is_dir( $clients_dir ) ) {
		return;
	}

	$manifests = glob( $clients_dir . '*/manifest.json' );

	if ( empty( $manifests ) ) {
		return;
	}

	foreach ( $manifests as $manifest_file ) {
		$manifest = json_decode( file_get_contents( $manifest_file ), true );

		if ( empty( $manifest ) || empty( $manifest['active'] ) ) {
			continue;
		}

		$client_dir = dirname( $manifest_file ) . '/';
		$slug       = $manifest['slug'] ?? basename( dirname( $manifest_file ) );

		// Store active client data for use by enqueue.php and feature flags.
		global $blocksy_child_active_clients;
		if ( ! isset( $blocksy_child_active_clients ) ) {
			$blocksy_child_active_clients = [];
		}
		$blocksy_child_active_clients[] = [
			'slug'     => $slug,
			'dir'      => $client_dir,
			'manifest' => $manifest,
		];

		// Load client PHP if specified and exists.
		if ( ! empty( $manifest['php'] ) ) {
			$php_file = $client_dir . $manifest['php'];
			if ( file_exists( $php_file ) ) {
				try {
					require_once $php_file;
				} catch ( \Throwable $e ) {
					error_log( '[blocksy-child] Client module error (' . $slug . '): ' . $e->getMessage() );
				}
			}
		}
	}
}
