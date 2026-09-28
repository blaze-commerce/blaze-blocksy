<?php
/**
 * Fonts Critical Path — inline the local Google Fonts CSS, plus optional
 * metric-matched fallback faces.
 *
 * PURPOSE: Blocksy enqueues its Google Fonts under the handle
 * `blocksy-fonts-font-source-google`; Perfmatters' Local Google Fonts
 * localises it into `wp-content/cache/perfmatters/<host>/fonts/<hash>.google-fonts.min.css`.
 * That file is small (AW: 13.9 KB raw, ~847 B gzipped, nothing but
 * `@font-face` rules), render-blocking, and — worse — a middle hop: no font
 * can start downloading until it has landed and been parsed. This module
 * replaces that `<link>` with the same CSS inlined as
 * `<style id="bc-perf-fonts-inline" data-no-optimize="1" data-no-minify="1">`,
 * taking the network dependency tree from three levels to two.
 *
 * Two strategies, the second only as a fallback:
 *
 *   1. `style_loader_tag` @10 for every handle in
 *      `blocksy_child_perf_font_handles` (default
 *      `['blocksy-fonts-font-source-google']`). The href is resolved to a
 *      LOCAL file only (content dir / uploads / child theme, with `realpath`
 *      containment); when it still points at fonts.googleapis.com (the live
 *      case — Perfmatters rewrites the href later, in its output buffer) the
 *      Perfmatters cache directory is globbed instead
 *      (`blocksy_child_perf_fonts_cache_dir`, newest `*google-fonts*.css`
 *      by mtime wins, so a stale hash from a previous config is ignored).
 *   2. `perfmatters_output_buffer` @99 — only when strategy 1 did not
 *      inline anything on this request — swaps a rewritten
 *      `/cache/perfmatters/…google-fonts….css` `<link>` for the inline block.
 *
 * NO NETWORK. No `wp_remote_*` call is ever made: anything that does not
 * resolve to a readable local file ≤ 50 KB (`blocksy_child_perf_fonts_max_bytes`)
 * — or whose contents contain `</` — fails open to the original `<link>`
 * (Global Constraint 10: works with or without Perfmatters).
 *
 * PRELOADS DEFAULT OFF. `blocksy_child_perf_font_preload_faces` defaults to
 * `[]`. Return `true` to derive `<link rel=preload as=font crossorigin>` for
 * every latin (`U+0000-00FF`) woff2 face in the inlined CSS, or an array of
 * woff2 URLs to preload exactly those. Only worth it when the LCP element is
 * TEXT: once the CSS is inline the faces are discoverable from the first
 * parse anyway, so a preload only raises their priority — and on AW it put
 * ~117 KB of fonts at High priority beside a 20.7 KB LCP image.
 *
 * FALLBACK METRICS. `blocksy_child_perf_font_fallbacks` (default `[]`)
 * takes a list of
 *   [ 'family' => 'X Fallback', 'local' => ['Arial','Liberation Sans'],
 *     'size_adjust' => '78%', 'ascent' => '128.2%', 'descent' => '25.6%',
 *     'line_gap' => '0%', 'css_var' => '--wp--preset--font-family--…',
 *     'stack' => '"Barlow Semi Condensed", "X Fallback", sans-serif' ]
 * (optional `weight` / `style`) and prints, at `wp_head` @2, one metric-matched
 * `@font-face` per entry plus `html:root{--var:stack}` — `html:root`, not
 * `:root`, so it out-specifies the preset in Blocksy's `global.css`, which
 * Perfmatters serves deferred and would otherwise revert mid-load. Resolved
 * at PRINT time (callable content), so a map registered on `init` counts;
 * an empty map prints nothing. Measure the numbers per font: ascent/descent
 * overrides are scaled BY size-adjust, and a wrong fallback is worse than none.
 *
 * DOUBLE FONT SYSTEMS. Blocksy's own `local-google-fonts` extension and
 * Perfmatters' Local Google Fonts both on = duplicate font loads. Turn
 * Blocksy's extension off and let Perfmatters own localisation; this module
 * assumes the Perfmatters cache layout.
 *
 * PROMOTED FROM: `bc-site-customizations/sites/alternateworlds/custom/includes/font-critical-path.php`
 * (canonical: handle match, href→path else cache glob, 50 KB cap, fail-open,
 * preloads since disabled), `…/alternateworlds/custom/includes/font-fallback-metrics.php`
 * (metric-matched local fallback, `html:root` specificity trick) and
 * `sites/byronbaycandles/custom/inline-perfmatters-font-css.php` (the
 * `perfmatters_output_buffer` strategy).
 *
 * MEASURED EVIDENCE: Austin `fonts.method=inline` 70–77→82; AW: font CSS
 * inline → NetworkDependencyTree/RenderBlocking insights stop firing,
 * 1,212 ms critical path cleared; AW CLS 0.124→0.034 with metric-matched
 * fallback; AW measured font preloads harmful with an image LCP → default off.
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: the Perfmatters local-fonts cache
 * regenerates per host (hashed filenames, per-host directory) — nothing is
 * copied. The module self-heals after `clear-local-fonts`: the empty cache
 * makes it emit the original Google `<link>`, Perfmatters localises it again
 * on that render, and the next request inlines the new file (one un-inlined
 * render per font-config change). A client's fallback-metric numbers are
 * per-font measurements and belong to that client's filter, not this file.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Style handles whose tag is replaced by inlined CSS.
 *
 * @return string[]
 */
function blocksy_child_perf_fonts_handles(): array {
	$handles = apply_filters( 'blocksy_child_perf_font_handles', [ 'blocksy-fonts-font-source-google' ] );

	return is_array( $handles ) ? array_values( array_filter( $handles, 'is_string' ) ) : [];
}

/**
 * Normalise a URL for scheme-insensitive prefix comparison (the enqueued
 * href and content_url() can disagree on http/https behind a proxy).
 *
 * @param string $url
 * @return string
 */
function blocksy_child_perf_fonts_strip_scheme( string $url ): string {
	return (string) preg_replace( '#^(?:https?:)?//#i', '', $url );
}

/**
 * URL prefix => local directory roots a stylesheet href may resolve under.
 *
 * Content dir (when WordPress is loaded), uploads, and the child theme.
 * Filterable via `blocksy_child_perf_fonts_url_roots`.
 *
 * @return array<string,string>
 */
function blocksy_child_perf_fonts_url_roots(): array {
	$roots = [];

	if ( function_exists( 'content_url' ) && defined( 'WP_CONTENT_DIR' ) ) {
		$roots[ content_url() ] = WP_CONTENT_DIR;
	}

	if ( function_exists( 'wp_get_upload_dir' ) ) {
		$upload = wp_get_upload_dir();
		if ( ! empty( $upload['baseurl'] ) && ! empty( $upload['basedir'] ) ) {
			$roots[ (string) $upload['baseurl'] ] = (string) $upload['basedir'];
		}
	}

	if ( defined( 'BLOCKSY_CHILD_URL' ) && defined( 'BLOCKSY_CHILD_PATH' ) ) {
		$roots[ BLOCKSY_CHILD_URL ] = BLOCKSY_CHILD_PATH;
	}

	$roots = apply_filters( 'blocksy_child_perf_fonts_url_roots', $roots );

	return is_array( $roots ) ? $roots : [];
}

/**
 * Whether $path (already realpath'd) lies inside $root.
 *
 * @param string $path
 * @param string $root
 * @return bool
 */
function blocksy_child_perf_fonts_contained( string $path, string $root ): bool {
	$real_root = realpath( $root );
	if ( false === $real_root ) {
		return false;
	}

	$real_root = rtrim( str_replace( '\\', '/', $real_root ), '/' ) . '/';
	$path      = str_replace( '\\', '/', $path );

	return 0 === strpos( $path, $real_root );
}

/**
 * Resolve a stylesheet URL to a readable local file under one of the
 * blocksy_child_perf_fonts_url_roots(). Never fetches anything.
 *
 * @param string $href
 * @return string|null Real path, or null.
 */
function blocksy_child_perf_fonts_url_to_path( string $href ): ?string {
	$clean = blocksy_child_perf_fonts_strip_scheme( (string) strtok( $href, '?#' ) );

	if ( '' === $clean ) {
		return null;
	}

	foreach ( blocksy_child_perf_fonts_url_roots() as $url => $dir ) {
		$prefix = rtrim( blocksy_child_perf_fonts_strip_scheme( (string) $url ), '/' ) . '/';

		if ( '/' === $prefix || 0 !== strpos( $clean, $prefix ) || '' === (string) $dir ) {
			continue;
		}

		$candidate = rtrim( (string) $dir, '/\\' ) . '/' . rawurldecode( substr( $clean, strlen( $prefix ) ) );
		$real      = realpath( $candidate );

		if ( false !== $real && is_file( $real ) && blocksy_child_perf_fonts_contained( $real, (string) $dir ) ) {
			return $real;
		}
	}

	return null;
}

/**
 * Default Perfmatters local-fonts cache directory for a host.
 *
 * @param string $host
 * @return string '' when WP_CONTENT_DIR is undefined or $host is empty.
 */
function blocksy_child_perf_fonts_default_cache_dir( string $host ): string {
	if ( ! defined( 'WP_CONTENT_DIR' ) || '' === $host ) {
		return '';
	}

	return rtrim( WP_CONTENT_DIR, '/\\' ) . '/cache/perfmatters/' . $host . '/fonts';
}

/**
 * Newest `*google-fonts*.css` in the Perfmatters cache directory.
 *
 * The directory is `blocksy_child_perf_fonts_cache_dir` (default
 * WP_CONTENT_DIR/cache/perfmatters/<home_url host>/fonts). Host-scoped on
 * purpose so a multi-host cache can never hand one site another's fonts.
 *
 * @return string|null
 */
function blocksy_child_perf_fonts_cached_file(): ?string {
	$host = '';
	if ( function_exists( 'home_url' ) ) {
		$host = (string) parse_url( (string) home_url(), PHP_URL_HOST );
	}

	$dir = (string) apply_filters( 'blocksy_child_perf_fonts_cache_dir', blocksy_child_perf_fonts_default_cache_dir( $host ) );

	if ( '' === $dir || ! is_dir( $dir ) ) {
		return null;
	}

	$matches = glob( rtrim( $dir, '/\\' ) . '/*google-fonts*.css' );
	if ( empty( $matches ) ) {
		return null;
	}

	usort( $matches, function ( $a, $b ) {
		return filemtime( $b ) <=> filemtime( $a );
	} );

	foreach ( $matches as $match ) {
		$real = realpath( $match );
		if ( false !== $real && is_file( $real ) && blocksy_child_perf_fonts_contained( $real, $dir ) ) {
			return $real;
		}
	}

	return null;
}

/**
 * Read a font stylesheet for inlining, or null to fail open.
 *
 * @param string|null $path
 * @return string|null
 */
function blocksy_child_perf_fonts_read( ?string $path ): ?string {
	if ( null === $path || ! is_readable( $path ) ) {
		return null;
	}

	$max  = (int) apply_filters( 'blocksy_child_perf_fonts_max_bytes', 51200 );
	$size = filesize( $path );

	if ( false === $size || $size > $max ) {
		return null;
	}

	$css = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	// `</` could close the <style> element early — never inline it.
	if ( false === $css || '' === trim( $css ) || false !== strpos( $css, '</' ) ) {
		return null;
	}

	return $css;
}

/**
 * Optional font preload tags for the inlined CSS (default: none).
 *
 * @param string $css
 * @return string
 */
function blocksy_child_perf_fonts_preload_markup( string $css ): string {
	$faces = apply_filters( 'blocksy_child_perf_font_preload_faces', [] );
	$urls  = [];

	if ( true === $faces ) {
		// Split on '}' so each chunk holds at most one @font-face and the
		// unicode-range test cannot pair with another rule's url().
		foreach ( explode( '}', $css ) as $block ) {
			if ( false === strpos( $block, '@font-face' ) || false === stripos( $block, 'U+0000-00FF' ) ) {
				continue;
			}
			if ( preg_match( '#url\(\s*[\'"]?((?:https?:)?//[^)\'"\s]+\.woff2)#i', $block, $m ) ) {
				$urls[ $m[1] ] = true;
			}
		}
		$urls = array_keys( $urls );
	} elseif ( is_array( $faces ) ) {
		$urls = array_values( array_unique( array_filter( $faces, 'is_string' ) ) );
	}

	$out = '';
	foreach ( $urls as $url ) {
		$out .= '<link rel="preload" href="' . esc_url( $url ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	}

	return $out;
}

/**
 * `<style>` id for an inlined handle — `bc-perf-fonts-inline` for the
 * Blocksy handle, suffixed for any other so ids never collide.
 *
 * @param string $handle
 * @return string
 */
function blocksy_child_perf_fonts_style_id( string $handle ): string {
	if ( '' === $handle || 'blocksy-fonts-font-source-google' === $handle ) {
		return 'bc-perf-fonts-inline';
	}

	return 'bc-perf-fonts-inline-' . preg_replace( '/[^a-z0-9_-]/i', '', $handle );
}

/**
 * Wrap CSS in the inline, optimizer-exempt style element (+ optional preloads).
 *
 * @param string $css
 * @param string $id
 * @return string
 */
function blocksy_child_perf_fonts_markup( string $css, string $id ): string {
	return blocksy_child_perf_fonts_preload_markup( $css )
		. '<style id="' . esc_attr( $id ) . '" data-no-optimize="1" data-no-minify="1">' . $css . "</style>\n";
}

/**
 * Pure: replace a font stylesheet tag with inlined CSS, or return it unchanged.
 *
 * @param string $tag    Original `<link>` markup.
 * @param string $handle Style handle.
 * @param string $href   Enqueued href.
 * @return string
 */
function blocksy_child_perf_fonts_inline_tag( $tag, $handle, $href ) {
	if ( ! in_array( (string) $handle, blocksy_child_perf_fonts_handles(), true ) ) {
		return $tag;
	}

	$css = blocksy_child_perf_fonts_read( blocksy_child_perf_fonts_url_to_path( (string) $href ) );

	if ( null === $css ) {
		$css = blocksy_child_perf_fonts_read( blocksy_child_perf_fonts_cached_file() );
	}

	if ( null === $css ) {
		return $tag;
	}

	return blocksy_child_perf_fonts_markup( $css, blocksy_child_perf_fonts_style_id( (string) $handle ) );
}

/**
 * `style_loader_tag` @10 callback — front-end only; records success so the
 * output-buffer strategy stands down on this request.
 *
 * @param string $tag
 * @param string $handle
 * @param string $href
 * @return string
 */
function blocksy_child_perf_fonts_filter_style_tag( $tag, $handle, $href = '' ) {
	if ( ! blocksy_child_perf_is_frontend_request() ) {
		return $tag;
	}

	$out = blocksy_child_perf_fonts_inline_tag( $tag, $handle, $href );

	if ( $out !== $tag ) {
		$GLOBALS['blocksy_child_perf_state']['fonts_inlined'] = true;
	}

	return $out;
}

/**
 * `perfmatters_output_buffer` @99 callback — second strategy, for when
 * Perfmatters rewrote the font link after `style_loader_tag` and strategy 1
 * did not inline anything this request.
 *
 * @param string $html
 * @return string
 */
function blocksy_child_perf_fonts_output_buffer( $html ) {
	if ( ! is_string( $html ) || ! empty( $GLOBALS['blocksy_child_perf_state']['fonts_inlined'] ) ) {
		return $html;
	}

	if ( ! blocksy_child_perf_is_frontend_request() || false === strpos( $html, 'google-fonts' ) ) {
		return $html;
	}

	$count  = 0;
	$result = preg_replace_callback(
		'#<link\b[^>]*?\bhref=([\'"])([^\'"]*?/cache/perfmatters/[^\'"]*?google-fonts[^\'"]*?\.css(?:\?[^\'"]*)?)\1[^>]*>#i',
		function ( $m ) use ( &$count ) {
			if ( ! preg_match( '#\brel=([\'"])stylesheet\1#i', $m[0] ) ) {
				return $m[0];
			}

			$css = blocksy_child_perf_fonts_read( blocksy_child_perf_fonts_url_to_path( html_entity_decode( $m[2] ) ) );
			if ( null === $css ) {
				return $m[0];
			}

			$count++;
			$id = 'bc-perf-fonts-inline' . ( $count > 1 ? '-' . $count : '' );

			return blocksy_child_perf_fonts_markup( $css, $id );
		},
		$html
	);

	if ( null === $result ) {
		return $html;
	}

	if ( $count > 0 ) {
		$GLOBALS['blocksy_child_perf_state']['fonts_inlined'] = true;
	}

	return $result;
}

/**
 * Sanitise a CSS percentage (`78%`, `128.2%`).
 *
 * @param mixed $value
 * @return string|null
 */
function blocksy_child_perf_fonts_pct( $value ): ?string {
	return ( is_string( $value ) && preg_match( '/^\d{1,4}(?:\.\d{1,4})?%$/', $value ) ) ? $value : null;
}

/**
 * A family/local() name safe to drop into double quotes.
 *
 * @param mixed $value
 * @return bool
 */
function blocksy_child_perf_fonts_safe_name( $value ): bool {
	return is_string( $value ) && '' !== trim( $value ) && ! preg_match( '/["\'\\\\{}<>;\n\r]/', $value );
}

/**
 * Pure: build the metric-matched fallback CSS for a map (see file docblock).
 * Invalid entries are dropped with an error_log(); empty map -> ''.
 *
 * @param array $map
 * @return string
 */
function blocksy_child_perf_fonts_fallback_css( array $map ): string {
	$faces = '';
	$vars  = [];

	foreach ( $map as $i => $entry ) {
		$locals = ( is_array( $entry ) && isset( $entry['local'] ) && is_array( $entry['local'] ) ) ? array_values( $entry['local'] ) : [];

		$ok = is_array( $entry ) && blocksy_child_perf_fonts_safe_name( $entry['family'] ?? null ) && ! empty( $locals );
		foreach ( $locals as $local ) {
			$ok = $ok && blocksy_child_perf_fonts_safe_name( $local );
		}

		$props = [];
		foreach ( [ 'size_adjust' => 'size-adjust', 'ascent' => 'ascent-override', 'descent' => 'descent-override', 'line_gap' => 'line-gap-override' ] as $key => $prop ) {
			if ( ! $ok || ! isset( $entry[ $key ] ) ) {
				continue;
			}
			$pct = blocksy_child_perf_fonts_pct( $entry[ $key ] );
			if ( null === $pct ) {
				$ok = false;
				continue;
			}
			$props[] = $prop . ':' . $pct;
		}

		$head = '';
		if ( $ok && isset( $entry['style'] ) ) {
			$ok   = is_string( $entry['style'] ) && preg_match( '/^(normal|italic|oblique)$/', $entry['style'] );
			$head .= $ok ? 'font-style:' . $entry['style'] . ';' : '';
		}
		if ( $ok && isset( $entry['weight'] ) ) {
			$ok   = (bool) preg_match( '/^\d{3}(?: \d{3})?$/', (string) $entry['weight'] );
			$head .= $ok ? 'font-weight:' . $entry['weight'] . ';' : '';
		}

		$var = null;
		if ( $ok && ( isset( $entry['css_var'] ) || isset( $entry['stack'] ) ) ) {
			$ok = is_string( $entry['css_var'] ?? null ) && preg_match( '/^--[A-Za-z0-9_-]+$/', $entry['css_var'] )
				&& is_string( $entry['stack'] ?? null ) && '' !== trim( $entry['stack'] ) && ! preg_match( '/[{}<>;\\\\\n\r]/', $entry['stack'] );
			$var = $ok ? $entry['css_var'] . ':' . $entry['stack'] : null;
		}

		if ( ! $ok ) {
			error_log( '[blocksy-child][perf] fonts-critical-path: dropped invalid font fallback entry ' . $i );
			continue;
		}

		$src = implode( ',', array_map( function ( $name ) {
			return 'local("' . $name . '")';
		}, $locals ) );

		$faces .= '@font-face{font-family:"' . $entry['family'] . '";' . $head . 'src:' . $src
			. ( $props ? ';' . implode( ';', $props ) : '' ) . '}';

		if ( null !== $var ) {
			$vars[] = $var;
		}
	}

	return $faces . ( $vars ? 'html:root{' . implode( ';', array_unique( $vars ) ) . '}' : '' );
}

/**
 * Wire the module's hooks.
 *
 * @return void
 */
function blocksy_child_perf_fonts_register(): void {
	add_filter( 'style_loader_tag', 'blocksy_child_perf_fonts_filter_style_tag', 10, 3 );
	add_filter( 'perfmatters_output_buffer', 'blocksy_child_perf_fonts_output_buffer', 99 );

	blocksy_child_perf_style( 'bc-perf-font-fallbacks', function () {
		$map = apply_filters( 'blocksy_child_perf_font_fallbacks', [] );

		return is_array( $map ) ? blocksy_child_perf_fonts_fallback_css( $map ) : '';
	}, 2 );
}

blocksy_child_perf_fonts_register();
