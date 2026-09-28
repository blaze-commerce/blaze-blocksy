<?php
/**
 * COPY-TO-mu-plugins/ SHIM for inc/perf/perfmatters-config.php.
 *
 * NOT autoloaded from here. `wp-content/mu-plugins/` is the only directory
 * WordPress scans for must-use plugins, and `inc/perf/mu-plugins/` — inside
 * the theme — is not it. To use this shim, copy (or symlink) this file to
 * `wp-content/mu-plugins/bc-perfmatters-config.php`.
 *
 * WHEN THIS IS NEEDED: normally it is not. `inc/perf/perfmatters-config.php`
 * registers its `option_perfmatters_options` filter from `inc/perf/loader.php`,
 * required directly from functions.php at theme-load time — after
 * `plugins_loaded`, before `init` — and Perfmatters reads its own options
 * later still (its own `plugins_loaded`/`init` hooks, then output
 * buffering), so the theme's own filter registration is early enough in
 * the normal case. Use this shim only if a future Perfmatters version
 * (or a must-use/drop-in plugin that reads `perfmatters_options` even
 * earlier) reads its options ON `plugins_loaded` itself, before the theme
 * loads — mu-plugins load before regular plugins, which settles that
 * timing question outright (verified against Perfmatters directly: see
 * Houston `86eyhbd6j-perfmatters-config-as-code.md`, "Proof it works").
 *
 * Locates the active theme's stylesheet directory via
 * get_stylesheet_directory() (guarded by function_exists() — mu-plugins
 * load very early) and requires inc/perf/helpers.php then
 * inc/perf/perfmatters-config.php from there, but only if both files exist
 * and BLOCKSY_CHILD_PERF_CONFIG_LOADED is not already defined. The module
 * itself defines that constant on load, so if the theme's own
 * inc/perf/loader.php also requires perfmatters-config.php later in the
 * same request (the normal case — this shim does not replace that, it
 * only runs earlier), that second require is a no-op rather than a
 * duplicate filter registration.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'BLOCKSY_CHILD_PERF_CONFIG_LOADED' ) ) {
	return;
}

if ( ! function_exists( 'get_stylesheet_directory' ) ) {
	return;
}

$bc_perf_mu_theme_dir = rtrim( get_stylesheet_directory(), '/\\' ) . '/';

$bc_perf_mu_helpers_path = $bc_perf_mu_theme_dir . 'inc/perf/helpers.php';
$bc_perf_mu_module_path  = $bc_perf_mu_theme_dir . 'inc/perf/perfmatters-config.php';

if ( file_exists( $bc_perf_mu_helpers_path ) && file_exists( $bc_perf_mu_module_path ) ) {
	require_once $bc_perf_mu_helpers_path;
	require_once $bc_perf_mu_module_path;
}

unset( $bc_perf_mu_theme_dir, $bc_perf_mu_helpers_path, $bc_perf_mu_module_path );
