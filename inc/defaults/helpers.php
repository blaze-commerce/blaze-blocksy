<?php
/**
 * Defaults Helpers — opt-in resolution for the `inc/defaults/` family.
 *
 * PURPOSE: site-level defaults that retheme builds kept re-writing in their
 * own custom/ folders (iOS zoom on form fields, tap targets, the theme-leak
 * reset for hand-built design markup, script scoping). Each one changes how
 * a site looks or which scripts load, so none of them may turn on just
 * because a site has no manifest — which is what `$optional_modules` in
 * inc/loader.php does. They copy the perf family's opt-in rule instead.
 *
 * PROMOTED FROM: `bulkwholesale` custom/ (custom.css, includes/bw-core.php),
 * cross-checked against bc-site-customizations/sites/* (2026-10-31): 16px
 * !important font-size rules on 8 sites, 24px min-heights on 6, heading
 * line-height resets on 4, object-fit:contain on 8.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Every default this theme knows, mapped to how it loads.
 *
 * 'css' loads assets/css/components/defaults-<name>.css on the front end;
 * 'php' requires inc/defaults/<name>.php at theme load.
 *
 * @return array<string,string>
 */
function blocksy_child_defaults_known(): array {
	return [
		'design-scope'           => 'css',
		'pl-placeholder-contain' => 'css',
		'card-image-contain'     => 'css',
		'mobile-inputs'          => 'css',
		'tap-targets'            => 'css',
		'recaptcha-scope'        => 'php',
		'payment-assets-scope'   => 'php',
	];
}

/**
 * Resolve the enabled defaults.
 *
 * Union of the active client manifest's `"defaults"` array, the
 * `BLOCKSY_CHILD_DEFAULTS` constant and the `blocksy_child_defaults` filter
 * (default `[]`). A site with none of these gets nothing. Unknown names are
 * dropped and logged so a typo is discoverable.
 *
 * Not memoised: it is called a handful of times per request, and a custom/
 * filter added after the first call should still count.
 *
 * @return string[]
 */
function blocksy_child_defaults_enabled_list(): array {
	global $blocksy_child_active_clients;

	$names = [];

	if ( ! empty( $blocksy_child_active_clients ) ) {
		foreach ( $blocksy_child_active_clients as $client ) {
			if ( isset( $client['manifest']['defaults'] ) && is_array( $client['manifest']['defaults'] ) ) {
				$names = array_merge( $names, $client['manifest']['defaults'] );
			}
		}
	}

	if ( defined( 'BLOCKSY_CHILD_DEFAULTS' ) && is_array( BLOCKSY_CHILD_DEFAULTS ) ) {
		$names = array_merge( $names, BLOCKSY_CHILD_DEFAULTS );
	}

	$filtered = apply_filters( 'blocksy_child_defaults', [] );
	if ( is_array( $filtered ) ) {
		$names = array_merge( $names, $filtered );
	}

	$known = blocksy_child_defaults_known();
	$valid = [];

	foreach ( array_unique( array_map( 'strtolower', array_map( 'strval', $names ) ) ) as $name ) {
		if ( isset( $known[ $name ] ) ) {
			$valid[] = $name;
			continue;
		}
		error_log( '[blocksy-child][defaults] unknown default: ' . $name );
	}

	return $valid;
}

/**
 * Whether one default is enabled for this site.
 *
 * @param string $name Default name (see blocksy_child_defaults_known()).
 * @return bool
 */
function blocksy_child_default_enabled( string $name ): bool {
	return in_array( strtolower( $name ), blocksy_child_defaults_enabled_list(), true );
}
