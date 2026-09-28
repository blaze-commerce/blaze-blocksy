<?php
/**
 * Hero Facade — poster-first homepage hero for a YouTube iframe or an
 * autoplay `<video>`, with the real media mounted late and revealed only
 * once it is actually playing.
 *
 * PURPOSE: an ambient autoplaying hero is the biggest thing above the fold
 * and, shipped as-is, it wrecks LCP in two different ways:
 *
 *  - YouTube `<iframe>`: a cross-origin iframe never yields an LCP candidate
 *    for the parent page, so the hero contributes nothing and Lighthouse
 *    settles on some small heading further down (LCP = pure render delay).
 *    `autoplay=1` also pulls ~484 KB of player JS into the LCP window.
 *  - `<video autoplay>`: the browser discovers and starts fetching a 5+ MiB
 *    mp4 during initial render, competing with everything else.
 *
 * This module rewrites the hero inside `<div class="{wrapper_class}">` in
 * `the_content` (priority 20, front page only unless `front_page_only` is
 * false) so the first paint is a still poster — the video's own frame 0 —
 * and a footer script mounts the real media later:
 *
 *  (a) YouTube: the iframe is replaced by `<img class="bc-perf-hero__poster"
 *      fetchpriority="high" decoding="sync">` built from the repo asset
 *      `{poster_dir}yt-poster-<id>-800.avif` (inlined as a `data:` URI when
 *      it is at most `poster_max_inline_bytes`, else referenced by URL), with
 *      an optional `yt-poster-<id>-1920.avif` as `data-bc-hires`. The iframe
 *      src/title are stashed on the wrapper as `data-bc-yt-src` /
 *      `data-bc-yt-title`. No poster asset for that id => markup untouched.
 *  (b) `<video autoplay>`: `autoplay` stripped, `preload="none"`, `src` moved
 *      to `data-bc-src`; the poster is resolved to its attachment
 *      (`attachment_url_to_postid`) and inlined as `data:` from the
 *      `bc_hero_poster` 640x360 size (fallback `medium_large`) when small
 *      enough; the full-size URL is kept as `data-bc-hires`.
 *
 * The footer script (assets/js/perf-hero-facade.js, inlined through
 * blocksy_child_perf_script() so the delay-JS exclusion is automatic) arms
 * `pointerdown/touchstart/wheel/keydown/mousemove/scroll` at parse time, plus
 * a `delay_ms` timer after `load` (skipped under `prefers-reduced-motion:
 * reduce`). On the first trigger a YouTube hero gets its iframe mounted at
 * opacity 0 with `enablejsapi=1`, is sent `{"event":"listening"}`, and is
 * revealed only when the player reports `playerState === 1` (PLAYING); a
 * video gets `src` + `.load()` + `.play()`, gated by a 200 px
 * IntersectionObserver. The media always stacks OVER the poster — the
 * poster itself is never faded or removed. On `load`, viewports at least
 * `hires_min_width` wide swap in the hi-res poster.
 *
 * WHY SPEED INDEX FORCES "REVEAL ONLY WHEN PLAYING". Speed Index scores every
 * filmstrip frame against the FINAL frame. If the player paints into the hero
 * late (or, in headless Chrome, paints a grey "can't play this video" panel),
 * the final frame changes and every earlier frame becomes retroactively
 * incomplete — Houston measured SI 16,070 ms that way with FCP/LCP untouched.
 * Keeping the iframe invisible until PLAYING means a client that cannot play
 * never sees a pixel change.
 *
 * WHY THE POSTER IS A REPO ASSET (YouTube). It travels with the PR and is
 * byte-identical on every environment — no media-library upload, no
 * `wp media regenerate` per environment. Generate one from frame 0 of the
 * source video:
 *
 *     yt-dlp -f mp4 -o in.mp4 "https://www.youtube.com/watch?v=<id>"
 *     ffmpeg -i in.mp4 -vf "select=eq(n\,0)" -vframes 1 f0.png
 *     ffmpeg -i f0.png -vf scale=800:-2 -c:v libaom-av1 -crf 32 -still-picture 1 yt-poster-<id>-800.avif
 *     (optional, same with scale=1920:-2 -> yt-poster-<id>-1920.avif)
 *
 * and commit them under `custom/images/` (the default `poster_dir`).
 *
 * TESTED AND REJECTED:
 *  - 2 s timer instead of 5 s (Austin): did nothing for the user and put
 *    video fetch/decode inside the LCP window (median Perf 99 -> 84).
 *  - Interaction-only, no timer (Austin, Houston PR #847 review): a visitor
 *    who never scrolls/taps/moves never sees the video at all — and without
 *    a scheduled task the poster's paint swung 449-2506 ms (Perf 99 -> 76).
 *  - Fading the poster out when the media starts (Austin): removed the
 *    recorded LCP element; the metric swung 2.3 <-> 5.9 s between runs.
 *    So the media stacks over the poster and the poster is never touched.
 *  - Preloading the poster (Austin, 2026-05-27): a `data:` URI needs no
 *    fetch, and a Link-header preload competed with the HTML on slow 4G.
 *    This module never preloads the poster.
 *
 * PROMOTED FROM: `sites/houstonnaturalmattress/custom/hero-youtube-facade.php`
 * (`git show fix/hnm-a11y-bp-90plus:…` in bc-site-customizations — PLAYING-
 * gated reveal, interaction-or-5 s trigger, reduced-motion, repo poster
 * assets; docs `86eyhbd6j-homepage-pagespeed-optimization.md`) and
 * `sites/austinnaturalmattress/custom/hero-video-defer.php` (`<video
 * autoplay>` -> `data-bc-src`, `preload=none`, `data:` poster, the
 * `hero_video_poster` 640x360 size — renamed `bc_hero_poster` here —,
 * `load` + 5 s + `requestIdleCallback` + IntersectionObserver; docs
 * `86exr8zrp-homepage-pagespeed-optimization.md`). Unified behind one config
 * array (the `blocksy_child_perf_hero_facade` filter).
 *
 * MEASURED EVIDENCE: Austin 87→99, LCP 4,951→1,757 ms; Houston 83→91, LCP
 * 4,440→1,749 ms; cross-origin iframe never yields an LCP candidate; Speed
 * Index scores every frame against the final frame → reveal only when
 * PLAYING.
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: the poster assets must exist under
 * `custom/images/` (or the filtered `poster_dir`) on each environment — a
 * hero whose video id has no `yt-poster-<id>-800.avif` is left untouched.
 * Video posters in the media library need
 * `wp media regenerate --only-missing` (after enabling this feature) to
 * produce the `bc_hero_poster` size; until then `medium_large` is used. The
 * wrapper must be positioned (`position:relative`, fixed aspect ratio) —
 * the poster and the mounted iframe are absolutely positioned inside it.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------
// Config + pure helpers.
// -----------------------------------------------------------------------

/**
 * The resolved hero-facade config.
 *
 * @return array{wrapper_class:string,poster_dir:string,poster_url:string,poster_max_inline_bytes:int,delay_ms:int,hires_min_width:int,front_page_only:bool}
 */
function blocksy_child_perf_hero_config(): array {
	$defaults = [
		'wrapper_class'           => 'bc-hero-video',
		'poster_dir'              => BLOCKSY_CHILD_PATH . 'custom/images/',
		'poster_url'              => BLOCKSY_CHILD_URL . 'custom/images/',
		'poster_max_inline_bytes' => 24576,
		'delay_ms'                => 5000,
		'hires_min_width'         => 900,
		'front_page_only'         => true,
	];

	/**
	 * Hero-facade config.
	 *
	 * @param array $config See $defaults above.
	 */
	$config = apply_filters( 'blocksy_child_perf_hero_facade', $defaults );

	return is_array( $config ) ? array_merge( $defaults, $config ) : $defaults;
}

/**
 * A local file as a `data:` URI when it is readable and at most $max bytes,
 * otherwise $url (which may be '').
 *
 * @param string $file Absolute path.
 * @param string $url  Fallback URL.
 * @param string $mime MIME type for the data URI.
 * @param int    $max  Inline byte limit.
 * @return string
 */
function blocksy_child_perf_hero_inline_or_url( string $file, string $url, string $mime, int $max ): string {
	if ( '' !== $file && is_readable( $file ) ) {
		$size = filesize( $file );

		if ( false !== $size && $size <= $max ) {
			$bytes = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions

			if ( false !== $bytes ) {
				return 'data:' . $mime . ';base64,' . base64_encode( $bytes ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
			}
		}
	}

	return $url;
}

/**
 * Resolve a hero poster.
 *
 * - A YouTube video id (string, exactly 11 chars `[A-Za-z0-9_-]`): the repo
 *   asset `{poster_dir}yt-poster-<id>-800.avif` (+ optional `-1920.avif`
 *   as hi-res). Missing 800 asset => null.
 * - An attachment id (int): the media-library poster, `bc_hero_poster` size
 *   when generated, else `medium_large`; hi-res = the `full` URL. Nothing
 *   resolvable => null.
 *
 * @param string|int $id_or_path YouTube id or attachment id.
 * @param array      $config     blocksy_child_perf_hero_config() shape.
 * @return array{src:string,hires:string}|null
 */
function blocksy_child_perf_hero_poster_src( $id_or_path, array $config ) {
	$max = (int) $config['poster_max_inline_bytes'];

	if ( is_int( $id_or_path ) ) {
		$id   = $id_or_path;
		$meta = function_exists( 'wp_get_attachment_metadata' ) ? wp_get_attachment_metadata( $id ) : [];
		$meta = is_array( $meta ) ? $meta : [];
		$size = ! empty( $meta['sizes']['bc_hero_poster'] ) ? 'bc_hero_poster' : 'medium_large';

		$url   = (string) wp_get_attachment_image_url( $id, $size );
		$hires = (string) wp_get_attachment_image_url( $id, 'full' );
		$file  = '';
		$mime  = 'image/jpeg';

		if ( ! empty( $meta['sizes'][ $size ]['file'] ) && ! empty( $meta['file'] ) && function_exists( 'wp_get_upload_dir' ) ) {
			$upload = wp_get_upload_dir();
			$dir    = dirname( $meta['file'] );
			$file   = rtrim( (string) ( $upload['basedir'] ?? '' ), '/\\' ) . '/'
				. ( '.' === $dir ? '' : $dir . '/' )
				. $meta['sizes'][ $size ]['file'];
			$mime   = $meta['sizes'][ $size ]['mime-type'] ?? ( $meta['mime_type'] ?? $mime );
		}

		$src = blocksy_child_perf_hero_inline_or_url( $file, $url, (string) $mime, $max );

		if ( '' === $src ) {
			return null;
		}

		return [
			'src'   => $src,
			'hires' => $hires,
		];
	}

	$video_id = (string) $id_or_path;

	if ( ! preg_match( '/^[A-Za-z0-9_-]{11}$/', $video_id ) ) {
		return null;
	}

	$dir  = rtrim( (string) $config['poster_dir'], '/\\' ) . '/';
	$base = rtrim( (string) $config['poster_url'], '/' ) . '/';
	$name = 'yt-poster-' . $video_id;
	$file = $dir . $name . '-800.avif';

	if ( ! is_readable( $file ) ) {
		return null;
	}

	$src = blocksy_child_perf_hero_inline_or_url( $file, $base . $name . '-800.avif?ver=' . (int) filemtime( $file ), 'image/avif', $max );

	$hires_file = $dir . $name . '-1920.avif';
	$hires      = is_readable( $hires_file ) ? $base . $name . '-1920.avif?ver=' . (int) filemtime( $hires_file ) : '';

	return [
		'src'   => $src,
		'hires' => $hires,
	];
}

/**
 * Byte ranges of every `<div>` whose class list contains $class: the
 * opening tag and the inner HTML up to its MATCHING `</div>` (nested divs
 * are depth-counted, so an inner `<div>` does not cut the wrapper short).
 * An unclosed wrapper is skipped.
 *
 * @param string $html
 * @param string $class
 * @return array<int,array{0:int,1:int,2:int}> [ open_start, inner_start, inner_end ].
 */
function blocksy_child_perf_hero_wrapper_regions( string $html, string $class ): array {
	if ( '' === $class || false === strpos( $html, $class ) ) {
		return [];
	}

	if ( ! preg_match_all( '#<div\b[^>]*\bclass=(["\'])(.*?)\1[^>]*>#i', $html, $opens, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ) {
		return [];
	}

	$regions = [];

	foreach ( $opens as $open ) {
		if ( ! in_array( $class, preg_split( '/\s+/', trim( $open[2][0] ) ), true ) ) {
			continue;
		}

		$start       = $open[0][1];
		$inner_start = $start + strlen( $open[0][0] );

		// Skip wrappers nested inside one already found.
		if ( $regions && $start < $regions[ count( $regions ) - 1 ][2] ) {
			continue;
		}

		if ( ! preg_match_all( '#<div\b|</div\s*>#i', $html, $tags, PREG_OFFSET_CAPTURE, $inner_start ) ) {
			continue;
		}

		$depth = 1;

		foreach ( $tags[0] as $tag ) {
			$depth += ( '/' === $tag[0][1] ) ? -1 : 1;

			if ( 0 === $depth ) {
				$regions[] = [ $start, $inner_start, $tag[1] ];
				break;
			}
		}
	}

	return $regions;
}

/**
 * Case (a): replace a YouTube iframe inside a wrapper with the poster <img>.
 *
 * @param string $open   Wrapper opening tag (modified in place).
 * @param string $inner  Wrapper inner HTML.
 * @param array  $config Config.
 * @return string|null New inner HTML, or null when nothing changed.
 */
function blocksy_child_perf_hero_rewrite_youtube( string &$open, string $inner, array $config ) {
	if ( false !== stripos( $open, 'data-bc-yt-src' ) ) {
		return null; // Already rewritten.
	}

	if ( ! preg_match( '#<iframe\b[^>]*>.*?</iframe\s*>#is', $inner, $m, PREG_OFFSET_CAPTURE ) ) {
		return null;
	}

	$iframe = $m[0][0];

	if ( ! preg_match( '/\ssrc=("|\')([^"\']+)\1/i', $iframe, $src_m ) ) {
		return null;
	}

	// Saved content holds &#038; entities.
	$src = html_entity_decode( $src_m[2], ENT_QUOTES, 'UTF-8' );

	if ( ! preg_match( '#/embed/([A-Za-z0-9_-]{11})#', $src, $id_m ) ) {
		return null;
	}

	$poster = blocksy_child_perf_hero_poster_src( $id_m[1], $config );

	if ( null === $poster ) {
		return null; // No shipped poster — leave the markup for whatever else handles it.
	}

	$title = '';
	if ( preg_match( '/\stitle=("|\')([^"\']*)\1/i', $iframe, $t_m ) ) {
		$title = html_entity_decode( $t_m[2], ENT_QUOTES, 'UTF-8' );
	}

	// 16:9 intrinsic size (the wrapper's aspect ratio); CSS fills the wrapper.
	$img = '<img class="bc-perf-hero__poster"'
		. ' src="' . esc_attr( $poster['src'] ) . '"'
		. ' width="1920" height="1080"'
		. ' alt="' . esc_attr( $title ) . '"'
		. ' fetchpriority="high" decoding="sync"'
		. ( '' !== $poster['hires'] ? ' data-bc-hires="' . esc_url( $poster['hires'] ) . '"' : '' )
		. ' style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover">';

	$open = preg_replace( '#\s*/?>$#', '', $open )
		. ' data-bc-yt-src="' . esc_attr( $src ) . '"'
		. ' data-bc-yt-title="' . esc_attr( $title ) . '">';

	return substr_replace( $inner, $img, $m[0][1], strlen( $iframe ) );
}

/**
 * Case (b): defer every `<video autoplay>` inside a wrapper.
 *
 * @param string $inner  Wrapper inner HTML.
 * @param array  $config Config.
 * @return string
 */
function blocksy_child_perf_hero_rewrite_videos( string $inner, array $config ): string {
	if ( false === stripos( $inner, '<video' ) ) {
		return $inner;
	}

	return preg_replace_callback(
		'#<video\b([^>]*)>#i',
		function ( $m ) use ( $config ) {
			$attrs = $m[1];

			if ( ! preg_match( '/\sautoplay\b/i', $attrs ) || false !== stripos( $attrs, 'data-bc-perf-hero' ) ) {
				return $m[0];
			}

			$attrs = preg_replace( '/\s+autoplay(=("[^"]*"|\'[^\']*\'|[^\s>]*))?(?=[\s>\/]|$)/i', '', $attrs );
			$attrs = preg_replace( '/\s+preload=("[^"]*"|\'[^\']*\'|[^\s>]*)/i', '', $attrs );
			$attrs = preg_replace( '/\s+src=("[^"]*"|\'[^\']*\')/i', ' data-bc-src=$1', $attrs, 1 );

			$attrs = preg_replace_callback(
				'/(\s+poster=("|\'))([^"\']+)(\2)/i',
				function ( $pm ) use ( $config ) {
					$id = function_exists( 'attachment_url_to_postid' ) ? (int) attachment_url_to_postid( html_entity_decode( $pm[3], ENT_QUOTES, 'UTF-8' ) ) : 0;

					if ( ! $id ) {
						return $pm[0];
					}

					$poster = blocksy_child_perf_hero_poster_src( $id, $config );

					if ( null === $poster ) {
						return $pm[0];
					}

					return $pm[1] . esc_attr( $poster['src'] ) . $pm[4]
						. ( '' !== $poster['hires'] ? ' data-bc-hires="' . esc_url( $poster['hires'] ) . '"' : '' );
				},
				$attrs,
				1
			);

			return '<video data-bc-perf-hero="1" preload="none"' . $attrs . '>';
		},
		$inner
	);
}

/**
 * Rewrite every hero wrapper in $html (pure: no conditionals, no globals
 * other than the "a rewrite happened" flag the footer script reads).
 *
 * @param string $html   Rendered content.
 * @param array  $config blocksy_child_perf_hero_config() shape.
 * @return string
 */
function blocksy_child_perf_hero_rewrite_content( $html, array $config ): string {
	$html    = (string) $html;
	$regions = blocksy_child_perf_hero_wrapper_regions( $html, (string) $config['wrapper_class'] );

	// Last to first, so earlier offsets stay valid.
	foreach ( array_reverse( $regions ) as $region ) {
		list( $start, $inner_start, $inner_end ) = $region;

		$open  = substr( $html, $start, $inner_start - $start );
		$inner = substr( $html, $inner_start, $inner_end - $inner_start );

		$new_open  = $open;
		$new_inner = blocksy_child_perf_hero_rewrite_youtube( $new_open, $inner, $config );
		$new_inner = blocksy_child_perf_hero_rewrite_videos( null === $new_inner ? $inner : $new_inner, $config );

		if ( $new_open === $open && $new_inner === $inner ) {
			continue;
		}

		$GLOBALS['blocksy_child_perf_state']['hero_facade_rewritten'] = true;

		$html = substr_replace( $html, $new_open . $new_inner, $start, $inner_end - $start );
	}

	return $html;
}

// -----------------------------------------------------------------------
// Hook callbacks.
// -----------------------------------------------------------------------

/**
 * `the_content` @20.
 *
 * @param string $html
 * @return string
 */
function blocksy_child_perf_hero_filter_content( $html ) {
	if ( ! blocksy_child_perf_is_frontend_render() ) {
		return $html; // REST content.rendered / feeds / admin / AJAX.
	}

	if ( empty( $html ) ) {
		return $html;
	}

	$config = blocksy_child_perf_hero_config();

	if ( ! empty( $config['front_page_only'] ) && ( ! function_exists( 'is_front_page' ) || ! is_front_page() ) ) {
		return $html;
	}

	return blocksy_child_perf_hero_rewrite_content( $html, $config );
}

/**
 * `after_setup_theme`: the 640x360 poster size (PSI mobile renders the hero
 * ~363 CSS px wide at ~1.74 DPR ≈ 634 device px; `medium_large` 768x432
 * over-delivers). Registered whenever this module is loaded — i.e. only when
 * the feature is enabled — on every request type, so `wp media regenerate`
 * (WP-CLI) sees it too.
 *
 * @return void
 */
function blocksy_child_perf_hero_image_size(): void {
	add_image_size( 'bc_hero_poster', 640, 360, true );
}

/**
 * Footer script body: `window.bcPerfHeroFacade = {...};` followed by
 * assets/js/perf-hero-facade.js. Resolved at print time (passed as a
 * callable) so the config filter is applied then. '' — so nothing at all
 * is printed — unless a hero was rewritten this request.
 *
 * @return string
 */
function blocksy_child_perf_hero_footer_js(): string {
	if ( empty( $GLOBALS['blocksy_child_perf_state']['hero_facade_rewritten'] ) ) {
		return '';
	}

	$file = BLOCKSY_CHILD_PATH . 'assets/js/perf-hero-facade.js';

	if ( ! is_readable( $file ) ) {
		error_log( '[blocksy-child][perf] hero-facade: script not found: ' . $file );
		return '';
	}

	$js     = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$config = blocksy_child_perf_hero_config();

	return 'window.bcPerfHeroFacade = ' . wp_json_encode(
		[
			'delay_ms'        => (int) $config['delay_ms'],
			'hires_min_width' => (int) $config['hires_min_width'],
		]
	) . ";\n" . trim( $js );
}

/**
 * Register every hook this module owns.
 *
 * @return void
 */
function blocksy_child_perf_hero_facade_register(): void {
	add_action( 'after_setup_theme', 'blocksy_child_perf_hero_image_size' );

	// Registers the id for delay-JS exclusion even off the front end;
	// itself no-ops the printer unless this is a front-end request.
	// A Closure, not the function-name string: helpers.php prints a bare
	// string verbatim and only invokes a non-string callable.
	blocksy_child_perf_script( 'bc-perf-hero-facade', function () {
		return blocksy_child_perf_hero_footer_js();
	}, 99 );

	if ( ! blocksy_child_perf_is_frontend_request() ) {
		return;
	}

	add_filter( 'the_content', 'blocksy_child_perf_hero_filter_content', 20 );
}

blocksy_child_perf_hero_facade_register();
