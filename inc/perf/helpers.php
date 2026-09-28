<?php
/**
 * Perf Helpers — opt-in resolution, output helpers, script-id registry.
 *
 * PURPOSE: Shared infrastructure for the `inc/perf/*.php` PageSpeed module
 * family — every feature module (later tasks) is built on
 * `blocksy_child_perf_enabled()`, `blocksy_child_perf_style()` /
 * `blocksy_child_perf_script()`, and `blocksy_child_perf_data()` defined
 * here. This file has no feature-specific logic of its own.
 *
 * PROMOTED FROM: generalises the pattern used across individual client
 * PageSpeed passes into shared theme infrastructure, per
 * `W:\BLAZE COMMERCE\pagespeed-docs\RECOMMENDATIONS-90plus-in-3-hours.md`
 * (the plan spec this module family implements).
 *
 * MEASURED EVIDENCE: none directly attaches to this file — it loads no
 * feature and prints nothing on its own. Before/after PageSpeed numbers
 * live in each feature module's own docblock (later tasks).
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: nothing here is client-specific.
 * The per-client opt-in list (`clients/<slug>/manifest.json`'s `"perf"`
 * key) is what does not travel — this file only reads it.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All perf feature names this theme knows how to load.
 *
 * Single source of truth for both the opt-in resolver below (unknown-name
 * detection) and the feature -> file map in inc/perf/loader.php.
 *
 * @return string[]
 */
function blocksy_child_perf_known_features(): array {
	return [
		'perfmatters-config',
		'perfmatters-filters',
		'rucss-safelist',
		'critical-css-supplements',
		'lcp-image',
		'hero-facade',
		'fonts-critical-path',
		'async-styles',
		'dequeue-assets',
		'media-hygiene',
		'content-visibility',
		'minicart-hydrate',
		'lazy-rescan',
	];
}

/**
 * Resolve the enabled perf-feature set.
 *
 * Union of:
 *   - the active client manifest's `"perf"` array (if it is an array);
 *   - the `BLOCKSY_CHILD_PERF_FEATURES` constant (if defined and an array);
 *   - the `blocksy_child_perf_features` filter (default `[]`).
 *
 * Deliberately does NOT consult the legacy `"features"` key or
 * `blocksy_child_feature_enabled()`'s "no key = all on" rule — perf
 * modules are opt-in only (Global Constraint 2).
 *
 * Names are normalised to unique lowercase strings. `'*'` is kept as a
 * literal member of the resolved set (see blocksy_child_perf_enabled()).
 * Any other name not in blocksy_child_perf_known_features() is dropped
 * from the resolved set and logged once via error_log().
 *
 * Memoised (in $GLOBALS['blocksy_child_perf_state']['enabled_features'],
 * not a plain `static` — see blocksy_child_perf_reset_state() below): the
 * result is computed once per request. Later mutations to the client
 * manifest / constant / filter after the first call have no effect.
 *
 * @return string[] Resolved, deduped, lowercase feature names (may include '*').
 */
function blocksy_child_perf_enabled_features(): array {
	if ( isset( $GLOBALS['blocksy_child_perf_state']['enabled_features'] ) ) {
		return $GLOBALS['blocksy_child_perf_state']['enabled_features'];
	}

	global $blocksy_child_active_clients;

	$features = [];

	if ( ! empty( $blocksy_child_active_clients ) ) {
		foreach ( $blocksy_child_active_clients as $client ) {
			if ( isset( $client['manifest']['perf'] ) && is_array( $client['manifest']['perf'] ) ) {
				$features = array_merge( $features, $client['manifest']['perf'] );
			}
		}
	}

	if ( defined( 'BLOCKSY_CHILD_PERF_FEATURES' ) && is_array( BLOCKSY_CHILD_PERF_FEATURES ) ) {
		$features = array_merge( $features, BLOCKSY_CHILD_PERF_FEATURES );
	}

	$filtered = apply_filters( 'blocksy_child_perf_features', [] );
	if ( is_array( $filtered ) ) {
		$features = array_merge( $features, $filtered );
	}

	$features = array_unique( array_map( function ( $feature ) {
		return strtolower( (string) $feature );
	}, $features ) );

	$known = blocksy_child_perf_known_features();
	$valid = [];

	foreach ( $features as $feature ) {
		if ( '*' === $feature || in_array( $feature, $known, true ) ) {
			$valid[] = $feature;
			continue;
		}

		// Ignored silently as far as behaviour goes (never matches a known
		// feature -> file mapping) but logged once so a typo in a
		// manifest is discoverable.
		error_log( '[blocksy-child][perf] unknown feature: ' . $feature );
	}

	$resolved = array_values( array_unique( $valid ) );

	$GLOBALS['blocksy_child_perf_state']['enabled_features'] = $resolved;

	return $resolved;
}

/**
 * Whether a single perf feature is enabled.
 *
 * `'*'` in the resolved set enables every KNOWN feature (not arbitrary
 * unknown names — those are already dropped by
 * blocksy_child_perf_enabled_features()).
 *
 * @param string $feature Feature name (see blocksy_child_perf_known_features()).
 * @return bool
 */
function blocksy_child_perf_enabled( string $feature ): bool {
	$feature  = strtolower( $feature );
	$features = blocksy_child_perf_enabled_features();

	if ( in_array( '*', $features, true ) ) {
		return in_array( $feature, blocksy_child_perf_known_features(), true );
	}

	return in_array( $feature, $features, true );
}

/**
 * Register a printed `<style>`/`<script>` id so other perf modules (e.g. a
 * later perfmatters-filters module) can see what this request printed —
 * or would print.
 *
 * Stored in $GLOBALS['blocksy_child_perf_state']['script_ids'] (not a
 * plain `global` scalar) for the same reason blocksy_child_perf_enabled_features()
 * uses that store — see blocksy_child_perf_reset_state() below.
 *
 * @param string $id Element id (should start with `bc-perf-`, Global Constraint 1).
 * @return void
 */
function blocksy_child_perf_register_script_id( string $id ): void {
	if ( ! isset( $GLOBALS['blocksy_child_perf_state']['script_ids'] ) ) {
		$GLOBALS['blocksy_child_perf_state']['script_ids'] = [];
	}

	if ( ! in_array( $id, $GLOBALS['blocksy_child_perf_state']['script_ids'], true ) ) {
		$GLOBALS['blocksy_child_perf_state']['script_ids'][] = $id;
	}
}

/**
 * All script ids registered via blocksy_child_perf_register_script_id() so far this request.
 *
 * @return string[]
 */
function blocksy_child_perf_script_ids(): array {
	return $GLOBALS['blocksy_child_perf_state']['script_ids'] ?? [];
}

/**
 * Whether this request is a genuine front-end page view.
 *
 * Output-producing perf helpers bail unless this is true (Global Constraint 5).
 *
 * @return bool
 */
function blocksy_child_perf_is_frontend_request(): bool {
	if ( function_exists( 'is_admin' ) && is_admin() ) {
		return false;
	}

	if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
		return false;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return false;
	}

	return true;
}

/**
 * Register a wp_head printer for an inline, optimizer-exempt <style> block.
 *
 * No-ops entirely (nothing is registered) unless
 * blocksy_child_perf_is_frontend_request(). `$condition`, if given, is
 * evaluated at PRINT time (not registration time) and suppresses output
 * for that single request when it returns falsy.
 *
 * `$css` may be a plain string OR a callable. A callable is invoked at
 * PRINT time (not registration time, and after `$condition` has already
 * passed) — exactly like `$condition` — so a module can defer filter
 * resolution (e.g. `apply_filters('some_site_extra_css', …)`) to the
 * moment `wp_head` actually fires, rather than to whenever the module
 * itself happened to load. This matters because `inc/perf/loader.php`
 * requires every enabled module directly from `functions.php`, not on a
 * hook — very early — so a filter a plugin or mu-plugin registers on
 * `init`/`plugins_loaded` would already have been missed by a plain
 * string built at module-load time. Passing a closure instead lets that
 * later registration still be seen when `wp_head` prints.
 *
 * The resolved content is cast to string; if that string is `''`, NOTHING
 * is printed for this id on this request — not even an empty `<style>`
 * tag — so an all-conditional bucket (e.g. everything routed through
 * `$css` returning '' when no filter supplies content) never emits a
 * pointless empty element.
 *
 * Prints exactly:
 *   <style id="{id}" data-no-optimize="1" data-no-minify="1">{css}</style>
 *
 * @param string          $id        Element id (Global Constraint 1: `bc-perf-` prefix).
 * @param string|callable $css       Raw CSS to print verbatim inside the tag, or a
 *                                    callable returning it, resolved at print time.
 * @param int             $priority  wp_head priority.
 * @param callable|null   $condition Optional print-time gate.
 * @return void
 */
function blocksy_child_perf_style( string $id, $css, int $priority = 1, ?callable $condition = null ): void {
	if ( ! blocksy_child_perf_is_frontend_request() ) {
		return;
	}

	add_action( 'wp_head', function () use ( $id, $css, $condition ) {
		if ( null !== $condition && ! call_user_func( $condition ) ) {
			return;
		}

		$out = is_callable( $css ) ? (string) call_user_func( $css ) : (string) $css;

		if ( '' === $out ) {
			return;
		}

		echo '<style id="' . esc_attr( $id ) . '" data-no-optimize="1" data-no-minify="1">' . $out . '</style>';
	}, $priority );
}

/**
 * Register a wp_footer printer for an inline, optimizer-exempt <script> block.
 *
 * The id is registered via blocksy_child_perf_register_script_id() at
 * REGISTRATION time (not print time), so blocksy_child_perf_script_ids()
 * reflects it even on a request where blocksy_child_perf_is_frontend_request()
 * is false and the wp_footer printer is therefore never registered.
 * `$condition`, if given, is evaluated at print time.
 *
 * `$js` may be a plain string OR a callable, resolved at print time —
 * same reasoning and same "print nothing (not even the tag) when the
 * resolved content is ''" behaviour as `blocksy_child_perf_style()`'s
 * `$css` parameter; see that function's docblock for why this matters for
 * a module whose content depends on a filter that may be registered after
 * the module itself has loaded.
 *
 * Prints exactly:
 *   <script id="{id}" data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false">{js}</script>
 *
 * @param string          $id        Element id (Global Constraint 1: `bc-perf-` prefix).
 * @param string|callable $js        Raw JS to print verbatim inside the tag, or a
 *                                    callable returning it, resolved at print time.
 * @param int             $priority  wp_footer priority.
 * @param callable|null   $condition Optional print-time gate.
 * @return void
 */
function blocksy_child_perf_script( string $id, $js, int $priority = 99, ?callable $condition = null ): void {
	blocksy_child_perf_register_script_id( $id );

	if ( ! blocksy_child_perf_is_frontend_request() ) {
		return;
	}

	add_action( 'wp_footer', function () use ( $id, $js, $condition ) {
		if ( null !== $condition && ! call_user_func( $condition ) ) {
			return;
		}

		$out = is_callable( $js ) ? (string) call_user_func( $js ) : (string) $js;

		if ( '' === $out ) {
			return;
		}

		echo '<script id="' . esc_attr( $id ) . '" data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false">' . $out . '</script>';
	}, $priority );
}

/**
 * Load a perf data file.
 *
 * Data files live at inc/perf/data/<name>.php and `return [...]`
 * (Global Constraint 7). Missing file or a non-array return both resolve
 * to `[]` rather than a fatal.
 *
 * @param string $name Data file name, without directory or `.php` extension.
 * @return array
 */
function blocksy_child_perf_data( string $name ): array {
	$name = basename( $name );
	$path = BLOCKSY_CHILD_PATH . 'inc/perf/data/' . $name . '.php';

	if ( ! file_exists( $path ) ) {
		error_log( '[blocksy-child][perf] data file not found: ' . $name );
		return [];
	}

	$data = include $path;

	return is_array( $data ) ? $data : [];
}

if ( defined( 'BC_PERF_TESTING' ) && BC_PERF_TESTING ) {
	/**
	 * TESTING ONLY — reset process-lifetime perf module state.
	 *
	 * Clears the memoised blocksy_child_perf_enabled_features() resolution
	 * and the blocksy_child_perf_register_script_id() registry (both held
	 * in $GLOBALS['blocksy_child_perf_state'], which is exactly what makes
	 * them resettable — a plain function `static` cannot be cleared from
	 * outside the function). Lets tests/perf/run.php give each
	 * tests/perf/test-*.php file a clean slate instead of inheriting
	 * whatever an earlier test file resolved/registered.
	 *
	 * Only defined at all when BC_PERF_TESTING is true (set by
	 * tests/perf/bootstrap.php) — does not exist in a real WordPress
	 * request. Callers must check function_exists() first.
	 *
	 * @return void
	 */
	function blocksy_child_perf_reset_state(): void {
		$GLOBALS['blocksy_child_perf_state'] = [
			'enabled_features' => null,
			'script_ids'       => [],
		];
	}
}
