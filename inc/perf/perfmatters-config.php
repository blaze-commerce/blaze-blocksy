<?php
/**
 * Perfmatters Config As Code — ship the perfmatters_options row with the theme.
 *
 * PURPOSE: Perfmatters stores its entire configuration in one `wp_options`
 * row (`perfmatters_options`). That row is not part of any deploy — a
 * clone inherits every perf CODE change and none of the perf SETTINGS,
 * because a database row does not travel with a PR. This module supplies
 * the row from a file instead, via WordPress's own `option_perfmatters_options`
 * / `default_option_perfmatters_options` filters, so the settings travel
 * with the theme the same way the code already does.
 *
 * `inc/perf/data/perfmatters-defaults.json` is the shipped configuration —
 * the Houston row with all site-specific data (search/cart/product CSS
 * selectors, YouTube dns-prefetch hosts, the `bc-hero-yt-facade` delay-JS
 * exclusion, etc.) removed, since those do not generalise across clients.
 * `custom/perfmatters.json`, if present, is layered OVER the defaults —
 * that is the per-site override deployed by `bc-site-customizations`, not
 * managed by this repo.
 *
 * Perfmatters reads its settings with a plain `get_option('perfmatters_options')`
 * at runtime and caches them in no global or static, so a filter registered
 * any time before that read supplies them. The theme loads after
 * `plugins_loaded` and before `init`, and every one of those reads happens
 * later still (output buffering), so registering this filter from
 * `inc/perf/loader.php` (required directly from functions.php, not on a
 * hook) is early enough. If a future Perfmatters version ever reads its
 * options ON `plugins_loaded` itself, before the theme loads, use the
 * `inc/perf/mu-plugins/bc-perfmatters-config.php` shim instead (copy it to
 * `wp-content/mu-plugins/`; see that file's own docblock).
 *
 * PROMOTED FROM: Houston `86eyhbd6j-perfmatters-config-as-code.md`
 * (`docs/perfmatters/config-as-code/perfmatters-config-as-code.php` in that
 * site's repo) — ported here with a type guard added to the merge (Global
 * Constraint 11) and a per-site override layer.
 *
 * MEASURED EVIDENCE: Houston homepage 54→83 after applying the Perfmatters
 * options row alone (final 98 after the full PageSpeed pass) — the single
 * biggest lever found in this project — and the single most common
 * cross-environment failure: prod PDP 43 vs staging 96, same code, same
 * template, differing only in that prod's `perfmatters_options` row was a
 * 76-byte stub.
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: nothing, for the keys this module
 * manages — that is the entire point of it. `assets.rucss_excluded_selectors`
 * ships empty here on purpose; the CSS safelist itself travels via a filter
 * in the `perfmatters-filters` module (Task 3), not as a JSON list, so a
 * site can extend it without editing this file. Media files (fonts, images)
 * still do not travel — `fonts.*` config does, the cached font files do
 * not.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Escape hatch: define this false in wp-config.php (or a client's own
// bootstrap) to fall back to the database row entirely.
if ( defined( 'BC_PERFMATTERS_CONFIG_AS_CODE' ) && ! BC_PERFMATTERS_CONFIG_AS_CODE ) {
	return;
}

// Documents (rather than causes) why the inc/perf/mu-plugins/bc-perfmatters-config.php
// shim requiring this file, followed by the theme's own loader.php requiring
// it again later in the same request, does not register the filters below
// twice: both call sites build their path from the same get_stylesheet_directory()
// call, so require_once() itself already recognises the second require as
// the same file (via realpath()) and never re-opens it — this guard's
// `return` is not what does the preventing there. It is not a general
// safety net for a path require_once() fails to recognise as identical
// (e.g. a filesystem hard link to this file): the functions below are
// declared unconditionally at file scope, so PHP binds them as soon as
// such a path is genuinely re-parsed, before this `if` can run, and that
// would fatal on redeclaration regardless of this constant.
if ( defined( 'BLOCKSY_CHILD_PERF_CONFIG_LOADED' ) ) {
	return;
}

define( 'BLOCKSY_CHILD_PERF_CONFIG_LOADED', true );

/**
 * Perfmatters option keys whose value must always be an array.
 *
 * Perfmatters' non-JS admin form can save one of these as a newline-
 * separated string instead of an array (Global Constraint 11); the type
 * guard in blocksy_child_perf_pm_apply_type_guard() below converts a
 * string found at any of these paths back into an array. Each entry is
 * `section.key`; every key here is exactly one level deep.
 *
 * @return string[]
 */
function blocksy_child_perf_pm_list_keys(): array {
	return [
		'assets.delay_js_exclusions',
		'assets.delay_js_inclusions',
		'assets.rucss_excluded_stylesheets',
		'assets.rucss_excluded_selectors',
		'lazyload.lazy_loading_exclusions',
		'lazyload.lazy_loading_parent_exclusions',
		'lazyload.element_selectors',
		'preload.dns_prefetch',
		'preload.preconnect',
		'preload.preload',
		'preload.fetch_priority',
		'preload.early_hint_types',
		'fonts.subsets',
	];
}

/**
 * Perfmatters option keys whose value must always be a string, even when
 * it is numeric.
 *
 * `minify_css_exclusions` is a string field in Perfmatters, not a list —
 * it is deliberately absent from blocksy_child_perf_pm_list_keys() above.
 *
 * @return string[]
 */
function blocksy_child_perf_pm_string_numeric_keys(): array {
	return [
		'lazyload.threshold',
		'lazyload.exclude_leading_images',
		'lazyload.css_background_exclude_leading',
	];
}

/**
 * Type-guard the merged options array (Global Constraint 11).
 *
 * Every key in blocksy_child_perf_pm_list_keys() that holds a string is
 * converted to an array: split on newlines, each line trimmed, empty
 * lines removed. Every key in blocksy_child_perf_pm_string_numeric_keys()
 * that holds an int or float is cast to a string. Anything else is left
 * untouched.
 *
 * @param array $options Merged perfmatters_options array.
 * @return array
 */
function blocksy_child_perf_pm_apply_type_guard( array $options ): array {
	foreach ( blocksy_child_perf_pm_list_keys() as $path ) {
		[ $section, $key ] = explode( '.', $path, 2 );

		if ( ! isset( $options[ $section ][ $key ] ) || ! is_string( $options[ $section ][ $key ] ) ) {
			continue;
		}

		$lines = preg_split( '/\r\n|\r|\n/', $options[ $section ][ $key ] );
		$lines = array_map( 'trim', $lines );
		$lines = array_values( array_filter( $lines, function ( $line ) {
			return '' !== $line;
		} ) );

		$options[ $section ][ $key ] = $lines;
	}

	foreach ( blocksy_child_perf_pm_string_numeric_keys() as $path ) {
		[ $section, $key ] = explode( '.', $path, 2 );

		if ( ! isset( $options[ $section ][ $key ] ) ) {
			continue;
		}

		if ( ! is_int( $options[ $section ][ $key ] ) && ! is_float( $options[ $section ][ $key ] ) ) {
			continue;
		}

		$options[ $section ][ $key ] = (string) $options[ $section ][ $key ];
	}

	return $options;
}

/**
 * Layer $config OVER $stored, one level down, then type-guard the result.
 *
 * `array_replace_recursive()` would merge the *contents* of list-valued
 * keys such as `rucss_excluded_selectors`, producing a hybrid of both
 * sides; those lists must be taken wholesale, so a section's array value
 * replaces its stored counterpart one level down (`array_replace()`, not
 * recursive) rather than being merged key-by-key beneath that. A key the
 * config does not mention — including a whole section — keeps its stored
 * value. Ported from Houston's `bc_perfmatters_merge_options()`
 * (`86eyhbd6j-perfmatters-config-as-code.md`); this file's own tests call
 * this function directly, so the tests and the filter cannot diverge.
 *
 * @param array $stored Stored (or lower-priority) option row.
 * @param array $config Shipped (or higher-priority) config, layered on top.
 * @return array
 */
function blocksy_child_perf_pm_merge_options( array $stored, array $config ): array {
	$merged = $stored;

	foreach ( $config as $section => $value ) {
		if ( is_array( $value ) && isset( $merged[ $section ] ) && is_array( $merged[ $section ] ) ) {
			$merged[ $section ] = array_replace( $merged[ $section ], $value );
			continue;
		}

		$merged[ $section ] = $value;
	}

	return blocksy_child_perf_pm_apply_type_guard( $merged );
}

/**
 * The child theme's root directory, with a trailing slash.
 *
 * BLOCKSY_CHILD_PATH when functions.php has already defined it; otherwise
 * derived from this file's own location (`inc/perf/` is two levels below
 * the theme root). The fallback is what the mu-plugin shim path relies on:
 * a must-use plugin loads — and `option_perfmatters_options` can fire —
 * before the theme's functions.php has run at all.
 *
 * @return string
 */
function blocksy_child_perf_pm_theme_dir(): string {
	if ( defined( 'BLOCKSY_CHILD_PATH' ) ) {
		return rtrim( (string) BLOCKSY_CHILD_PATH, '/\\' ) . '/';
	}

	return dirname( __DIR__, 2 ) . '/';
}

/**
 * Read, decode and memoise the shipped configuration.
 *
 * Starts from `inc/perf/data/perfmatters-defaults.json` (resolved from this
 * file's own `__DIR__`). If `<theme dir>/custom/perfmatters.json` (see
 * blocksy_child_perf_pm_theme_dir(); filterable via
 * `blocksy_child_perf_pm_site_config_path`) also exists and decodes to a
 * non-empty array, it is merged OVER the defaults with
 * blocksy_child_perf_pm_merge_options() — that file is the per-site
 * override deployed by `bc-site-customizations`, not managed by this repo.
 *
 * Memoised in $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] for
 * the lifetime of the request (same store blocksy_child_perf_enabled_features()
 * uses, inc/perf/helpers.php) — computed once, not recomputed if the
 * underlying files change mid-request.
 *
 * @return array|null Decoded, merged config, or null when the defaults
 *                     file is missing, unreadable, or does not decode to
 *                     an array.
 */
function blocksy_child_perf_pm_shipped_config(): ?array {
	if ( array_key_exists( 'pm_shipped_config', $GLOBALS['blocksy_child_perf_state'] ?? [] ) ) {
		return $GLOBALS['blocksy_child_perf_state']['pm_shipped_config'];
	}

	$config = null;

	// Resolved from __DIR__, never from BLOCKSY_CHILD_PATH: when this file is
	// loaded by the mu-plugin shim (inc/perf/mu-plugins/bc-perfmatters-config.php)
	// the filter can fire before functions.php has defined that constant.
	$defaults_path = __DIR__ . '/data/perfmatters-defaults.json';

	if ( is_readable( $defaults_path ) ) {
		$decoded = json_decode( (string) file_get_contents( $defaults_path ), true );

		if ( is_array( $decoded ) ) {
			$config = $decoded;
		}
	}

	if ( null !== $config ) {
		/**
		 * Filter the path to the per-site Perfmatters override JSON.
		 *
		 * @param string $path Default: <theme dir>/custom/perfmatters.json
		 *                     (see blocksy_child_perf_pm_theme_dir()).
		 */
		$site_path = apply_filters( 'blocksy_child_perf_pm_site_config_path', blocksy_child_perf_pm_theme_dir() . 'custom/perfmatters.json' );

		if ( is_string( $site_path ) && '' !== $site_path && is_readable( $site_path ) ) {
			$site_decoded = json_decode( (string) file_get_contents( $site_path ), true );

			if ( is_array( $site_decoded ) && ! empty( $site_decoded ) ) {
				$config = blocksy_child_perf_pm_merge_options( $config, $site_decoded );
			}
		}
	}

	$GLOBALS['blocksy_child_perf_state']['pm_shipped_config'] = $config;

	return $config;
}

/**
 * `option_perfmatters_options` filter callback.
 *
 * Global Constraint 6: always returns an array.
 *
 * @param mixed $stored The stored option value, as WordPress read it.
 * @return array
 */
function blocksy_child_perf_pm_filter_option( $stored ) {
	$config = blocksy_child_perf_pm_shipped_config();

	if ( null === $config ) {
		return is_array( $stored ) ? $stored : [];
	}

	if ( ! is_array( $stored ) ) {
		return $config;
	}

	return blocksy_child_perf_pm_merge_options( $stored, $config );
}

/**
 * `default_option_perfmatters_options` filter callback.
 *
 * Covers a site with no row at all: `get_option()` falls through to
 * `default_option_{$option}` before `option_{$option}` is ever consulted
 * when the row does not exist. Global Constraint 6: always returns an
 * array.
 *
 * @param mixed $default The default WordPress would otherwise return.
 * @return array
 */
function blocksy_child_perf_pm_filter_default_option( $default ) {
	$config = blocksy_child_perf_pm_shipped_config();

	if ( null === $config ) {
		return is_array( $default ) ? $default : [];
	}

	return $config;
}

add_filter( 'option_perfmatters_options', 'blocksy_child_perf_pm_filter_option', 10, 1 );
add_filter( 'default_option_perfmatters_options', 'blocksy_child_perf_pm_filter_default_option', 10, 1 );

/**
 * Admin notice: the Perfmatters settings screens show the stored row, not
 * the effective one, for any key this module owns.
 *
 * The only admin-side code in this module (exempt from Global Constraint 5
 * per the task brief). Fires on `admin_notices`; no-ops unless Perfmatters
 * itself is active AND the current admin screen is one of its own — both
 * checks are guarded with function_exists()/defined() so this never fatals
 * on a site (or test run) where Perfmatters, or even wp-admin's screen
 * API, is absent (Global Constraint 10).
 *
 * @return void
 */
function blocksy_child_perf_pm_admin_notice(): void {
	if ( ! defined( 'PERFMATTERS_VERSION' ) && ! function_exists( 'perfmatters_admin_menu' ) ) {
		return;
	}

	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$screen    = get_current_screen();
	$screen_id = ( $screen && isset( $screen->id ) ) ? (string) $screen->id : '';

	if ( false === strpos( $screen_id, 'perfmatters' ) ) {
		return;
	}

	echo '<div class="notice notice-info is-dismissible"><p>'
		. 'Perfmatters settings are managed by the child theme (<code>inc/perf/perfmatters-config</code>). '
		. 'Values saved here are overridden by <code>perfmatters-defaults.json</code> + <code>custom/perfmatters.json</code>.'
		. '</p></div>';
}

add_action( 'admin_notices', 'blocksy_child_perf_pm_admin_notice' );
