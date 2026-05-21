<?php
/**
 * Blocksy Child Theme — Lean Loader
 *
 * This file defines constants and loads the module loader.
 * All logic lives in inc/ — keep this file minimal.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme version — read from style.css `Version:` header (single source of truth).
 *
 * Why not a hardcoded constant: 2026-05-08 audit caught style.css `1.1.1`
 * drifting from a hardcoded `BLOCKSY_CHILD_VERSION = 1.1.4`. Two version
 * sources = inevitable drift. This pattern derives the constant from the
 * style.css header at boot, so bumping `Version:` in style.css alone is
 * enough — no manual two-place sync.
 *
 * `wp_get_theme()` is cached internally by WordPress (see
 * WP_Theme::__construct), so this is a one-time stat per request.
 *
 * Per-asset cache-busting still uses `filemtime()` in `inc/enqueue.php`
 * for surgical bust on individual file changes. `BLOCKSY_CHILD_VERSION`
 * is informational (CHANGELOG references, admin display, conditional
 * logic for forks).
 */
define( 'BLOCKSY_CHILD_VERSION', wp_get_theme( 'blocksy-child' )->get( 'Version' ) );

// Filesystem path to child theme root (with trailing slash).
define( 'BLOCKSY_CHILD_PATH', trailingslashit( get_stylesheet_directory() ) );

// URL to child theme root (with trailing slash).
define( 'BLOCKSY_CHILD_URL', trailingslashit( get_stylesheet_directory_uri() ) );

// Load the module loader — everything else is bootstrapped from there.
require_once BLOCKSY_CHILD_PATH . 'inc/loader.php';
