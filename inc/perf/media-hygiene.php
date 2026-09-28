<?php
/**
 * Media Hygiene — six small image/embed corrections that each stop a page
 * from downloading more, or earlier, than it renders.
 *
 * PURPOSE:
 *   (a) `image_downsize` fallback — when a registered named size was never
 *       generated for an attachment, core silently serves the full ORIGINAL
 *       while reporting the requested dimensions. Substitute the smallest
 *       generated size that still covers the requested width × height.
 *   (b) Header logo — core computes `sizes="(max-width: {W}px) 100vw, {W}px"`
 *       from the logo's declared width, so the browser fetches a
 *       full-viewport candidate for a ~200–400 px slot; and core awards the
 *       logo `fetchpriority="high"` for being first and "large". Give
 *       Blocksy's logo classes an honest `sizes` and drop `fetchpriority`.
 *   (c) Archive thumbnails — explicit `width`/`height` on shop/archive
 *       `woocommerce_thumbnail` images that lack them, so the box is
 *       reserved before paint (CLS; Firefox alt-text flash on lazy images).
 *   (d) Below-fold lazy — `loading="lazy" decoding="async"` on block images
 *       after the first N `<img>` of the request, so eager below-fold images
 *       (Lighthouse does not scroll) stop competing with the LCP image.
 *   (e) Stretched-height fix — a resized image block outputs `width` but no
 *       `height`; once lazy + `sizes="auto"`, core's placeholder
 *       `contain-intrinsic-size: 3000px 1500px` freezes it ~40× too tall.
 *       Restore `height` from the attachment's stored aspect ratio.
 *   (f) youtube-nocookie — serve YouTube `/embed/` iframes from the
 *       privacy-enhanced domain (identical player, same query params), so
 *       no third-party cookies are set before interaction.
 *
 * ORDERING WITH lcp-image. The below-fold pass (d) runs on `render_block` at
 * PRIORITY 20 (controller ruling): `inc/perf/lcp-image.php`'s first-cover-
 * block pass runs at 10 and marks the LCP image `fetchpriority="high"` +
 * class `bc-perf-lcp`, both of which (d) skips — so it can never lazy-load
 * the image lcp-image just promoted, whichever block it sits in.
 *
 * PROMOTED FROM (all `bc-site-customizations/sites/…`):
 *   (a) `alternateworlds/custom/includes/image-size-fallback.php`;
 *   (b) `alternateworlds/custom/includes/logo-image-sizing.php` (widths now
 *       the `blocksy_child_perf_logo_sizes` filter instead of constants);
 *   (c) `austinnaturalmattress/custom/archive-thumbnail-dimensions.php` —
 *       note the source gated on `is_product_archive()`, which WooCommerce
 *       does not define, so it could never run; this uses `is_shop() ||
 *       is_product_taxonomy()` and never guesses a 600×600 fallback;
 *   (d) `byronbaycandles/custom/lazyload-belowfold-images.php` — made
 *       generic (no front-page / page-id gate; first-N eager budget instead);
 *   (e) `byronbaycandles/custom/fix-stretched-image-height.php` — made
 *       generic (no attachment-id allow-list; any `wp-image-<id>` image);
 *   (f) Houston `custom/youtube-nocookie.php`
 *       (`git show fix/hnm-a11y-bp-90plus:…`).
 *
 * MEASURED EVIDENCE: AW image_downsize fallback 310 KB→13 KB (a 1335×2002
 * original served for a 150×150 slot — 15× the LCP image); AW logo
 * 38 KB→8 KB (desktop picked the 2248w candidate for a 388 px slot); BBC
 * stretched images 40× (placeholder height 1500 px on lazy badges); Houston
 * BP 77→96 with nocookie (`third-party-cookies` audit, weight 5 of 26).
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: the missing subsizes themselves
 * (regenerate thumbnails on the site — (a) is insurance, not the repair);
 * AW's measured logo widths are only the default — a different header sets
 * its own via `blocksy_child_perf_logo_sizes`; BBC's attachment allow-list
 * and home-page gate.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------
// (a) image_downsize fallback.
// -----------------------------------------------------------------------

/**
 * Pick the cheapest generated size that still covers the requested
 * dimensions ("covers" = at least as wide AND, when $want_h > 0, at least
 * as tall — never upscaled into the slot). Smallest area wins.
 *
 * @param array $sizes  The `sizes` array from wp_get_attachment_metadata().
 * @param int   $want_w Requested width.
 * @param int   $want_h Requested height; 0 = unconstrained.
 * @return array|null Winning size array with an added 'key', or null.
 */
function blocksy_child_perf_image_size_fallback_pick( array $sizes, int $want_w, int $want_h ): ?array {
	$best = null;

	foreach ( $sizes as $key => $candidate ) {
		if ( ! is_array( $candidate ) || empty( $candidate['file'] ) || empty( $candidate['width'] ) || empty( $candidate['height'] ) ) {
			continue;
		}

		$c_w = (int) $candidate['width'];
		$c_h = (int) $candidate['height'];

		if ( $c_w < $want_w || ( $want_h > 0 && $c_h < $want_h ) ) {
			continue;
		}

		if ( null === $best || ( $c_w * $c_h ) < ( (int) $best['width'] * (int) $best['height'] ) ) {
			$candidate['key'] = $key;
			$best             = $candidate;
		}
	}

	return $best;
}

/**
 * `image_downsize` filter — substitute a generated size where core would
 * otherwise serve the full original for a missing named size. Returns
 * `$out` unchanged (handing control back to core) in every case it is not
 * sure about.
 *
 * @param mixed      $out  Short-circuit value (false = let core decide).
 * @param int        $id   Attachment id.
 * @param string|int[] $size Requested size.
 * @return mixed
 */
function blocksy_child_perf_image_downsize_fallback( $out, $id, $size ) {
	if ( false !== $out || 'full' === $size ) {
		return $out;
	}

	// Array sizes go through image_get_intermediate_size(), which already
	// only chooses among generated sizes.
	if ( ! is_string( $size ) || '' === $size ) {
		return $out;
	}

	$meta = wp_get_attachment_metadata( $id );
	if ( ! is_array( $meta ) || empty( $meta['sizes'] ) || ! is_array( $meta['sizes'] ) ) {
		return $out;
	}

	if ( isset( $meta['sizes'][ $size ] ) ) {
		return $out; // Generated — core serves it.
	}

	$registered = function_exists( 'wp_get_registered_image_subsizes' ) ? wp_get_registered_image_subsizes() : [];
	if ( ! isset( $registered[ $size ] ) ) {
		return $out;
	}

	$pick = blocksy_child_perf_image_size_fallback_pick(
		$meta['sizes'],
		(int) $registered[ $size ]['width'],
		(int) $registered[ $size ]['height']
	);
	if ( null === $pick ) {
		return $out;
	}

	// No saving if the original is already no bigger than the substitute.
	$orig_w = isset( $meta['width'] ) ? (int) $meta['width'] : 0;
	$orig_h = isset( $meta['height'] ) ? (int) $meta['height'] : 0;
	if ( $orig_w && $orig_h && ( $orig_w * $orig_h ) <= ( (int) $pick['width'] * (int) $pick['height'] ) ) {
		return $out;
	}

	// Build the URL the way image_get_intermediate_size() does — NOT via
	// wp_get_attachment_image_src(), which would re-enter this filter.
	$attached = get_post_meta( $id, '_wp_attached_file', true );
	if ( ! is_string( $attached ) || '' === $attached ) {
		return $out;
	}

	$uploads = wp_get_upload_dir();
	if ( ! empty( $uploads['error'] ) || empty( $uploads['baseurl'] ) ) {
		return $out;
	}

	$subdir = ltrim( str_replace( '\\', '/', dirname( $attached ) ), '.' );
	$subdir = ( '' === $subdir || '/' === $subdir ) ? '' : trailingslashit( ltrim( $subdir, '/' ) );

	return [ trailingslashit( $uploads['baseurl'] ) . $subdir . $pick['file'], (int) $pick['width'], (int) $pick['height'], true ];
}

// -----------------------------------------------------------------------
// (b) Header logo sizes + fetchpriority.
// -----------------------------------------------------------------------

/**
 * Whether a class attribute marks a Blocksy header logo image (whole-token
 * match against `default-logo|transparent-logo|sticky-logo|mobile-logo|offcanvas-logo`).
 *
 * @param mixed $class_attr The image's class attribute.
 * @return bool
 */
function blocksy_child_perf_logo_is_logo( $class_attr ): bool {
	if ( ! is_string( $class_attr ) || '' === $class_attr ) {
		return false;
	}

	$classes = preg_split( '#\s+#', $class_attr, -1, PREG_SPLIT_NO_EMPTY );

	return (bool) array_intersect(
		$classes,
		[ 'default-logo', 'transparent-logo', 'sticky-logo', 'mobile-logo', 'offcanvas-logo' ]
	);
}

/**
 * The logo `sizes` string.
 *
 * @return string
 */
function blocksy_child_perf_logo_sizes(): string {
	/**
	 * The header logo's `sizes` attribute. Default is AW's measured
	 * rendered widths (193 px @412 viewport, 388 px @1440), rounded up —
	 * a different header design should set its own.
	 *
	 * @param string $sizes Default '(max-width: 999px) 220px, 420px'.
	 */
	return (string) apply_filters( 'blocksy_child_perf_logo_sizes', '(max-width: 999px) 220px, 420px' );
}

/**
 * `wp_get_attachment_image_attributes` @999 — honest `sizes` (only when a
 * `srcset` exists; inert otherwise) and no `fetchpriority` on Blocksy logos.
 *
 * @param mixed $attr Image attributes.
 * @return mixed
 */
function blocksy_child_perf_logo_attributes( $attr ) {
	if ( ! is_array( $attr ) || ! blocksy_child_perf_logo_is_logo( $attr['class'] ?? '' ) ) {
		return $attr;
	}

	if ( ! empty( $attr['srcset'] ) ) {
		$attr['sizes'] = blocksy_child_perf_logo_sizes();
	}

	unset( $attr['fetchpriority'] );

	return $attr;
}

// -----------------------------------------------------------------------
// (c) Archive thumbnail width/height.
// -----------------------------------------------------------------------

/**
 * `wp_get_attachment_image_attributes` @20 — on shop / product-taxonomy
 * archives, give `woocommerce_thumbnail` images missing a width or height
 * both dimensions from `wc_get_image_size('woocommerce_thumbnail')`,
 * falling back to the attachment's own generated `woocommerce_thumbnail`
 * metadata (uncropped thumbnails report height 0). Never guesses.
 *
 * @param mixed  $attr       Image attributes.
 * @param object $attachment Attachment post.
 * @param mixed  $size       Requested size.
 * @return mixed
 */
function blocksy_child_perf_archive_thumb_attributes( $attr, $attachment = null, $size = '' ) {
	if ( ! is_array( $attr ) || 'woocommerce_thumbnail' !== $size ) {
		return $attr;
	}

	$is_archive = ( function_exists( 'is_shop' ) && is_shop() )
		|| ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() );
	if ( ! $is_archive ) {
		return $attr;
	}

	if ( ! empty( $attr['width'] ) && (int) $attr['width'] > 0 && ! empty( $attr['height'] ) && (int) $attr['height'] > 0 ) {
		return $attr;
	}

	$data   = function_exists( 'wc_get_image_size' ) ? wc_get_image_size( 'woocommerce_thumbnail' ) : [];
	$width  = ! empty( $data['width'] ) ? (int) $data['width'] : 0;
	$height = ! empty( $data['height'] ) ? (int) $data['height'] : 0;

	if ( ( $width <= 0 || $height <= 0 ) && isset( $attachment->ID ) ) {
		$meta  = wp_get_attachment_metadata( $attachment->ID );
		$thumb = $meta['sizes']['woocommerce_thumbnail'] ?? null;

		if ( is_array( $thumb ) && ! empty( $thumb['width'] ) && ! empty( $thumb['height'] ) ) {
			$width  = (int) $thumb['width'];
			$height = (int) $thumb['height'];
		}
	}

	if ( $width > 0 && $height > 0 ) {
		$attr['width']  = $width;
		$attr['height'] = $height;
	}

	return $attr;
}

// -----------------------------------------------------------------------
// (d) Below-fold lazy.
// -----------------------------------------------------------------------

/**
 * How many `<img>` of the request stay eager before the below-fold pass
 * starts adding `loading="lazy"`.
 *
 * @return int
 */
function blocksy_child_perf_eager_img_count(): int {
	/**
	 * @param int $count Default 3.
	 */
	return max( 0, (int) apply_filters( 'blocksy_child_perf_eager_img_count', 3 ) );
}

/**
 * Add `loading="lazy"` (+ `decoding="async"` unless one is set) to every
 * `<img>` after the first N of the request (blocksy_child_perf_eager_img_count()).
 *
 * Skipped (never lazied, but still counted positionally and stamped): an `<img>`
 * that already has a `loading=` attribute, carries `fetchpriority="high"`,
 * or has a class matching `blaze-lcp-image`, `bc-perf-lcp`,
 * `bc-perf-hero__poster`, `skip-lazy` or `kb-skip-lazy`.
 *
 * `render_block` fires for an inner block AND again for its parent, whose
 * content already contains the inner block's rendered `<img>` — possibly
 * altered in between, because `WP_Block::render()` runs later
 * `render_block` / `render_block_{name}` callbacks (priority > 20) on the
 * inner block before the parent sees it. So an image cannot be recognised
 * by its tag text. Instead every `<img>` this pass counts is STAMPED with
 * `data-bc-perf-n="<position>"` (inserted right after `<img`), and an
 * already-stamped tag is skipped on every later pass: never re-counted,
 * never lazied. Attribute-preserving rewrites by other filters keep the
 * stamp; identical repeated tags (an icon used four times) each get their
 * own stamp and position, so the 4th copy is lazied like any other 4th
 * image. The marker stays in the HTML (a few bytes per image — accepted).
 *
 * Why not "process only top-level blocks": that needs a depth counter
 * paired across `pre_render_block` and `render_block`, and a plugin that
 * short-circuits `pre_render_block` skips the matching `render_block`
 * call, so the counter drifts for the rest of the request. The stamp has
 * no pairing to lose.
 *
 * The request-scoped position counter lives in
 * $GLOBALS['blocksy_child_perf_state']['lazy_count'] (reset by
 * blocksy_child_perf_reset_state()).
 *
 * @param string $html Rendered block HTML.
 * @return string
 */
function blocksy_child_perf_belowfold_lazy( string $html ): string {
	if ( false === stripos( $html, '<img' ) ) {
		return $html;
	}

	$eager = blocksy_child_perf_eager_img_count();

	return preg_replace_callback(
		'#<img\b[^>]*>#i',
		function ( $m ) use ( $eager ) {
			$tag = $m[0];

			if ( preg_match( '#\sdata-bc-perf-n\s*=#i', $tag ) ) {
				return $tag; // Counted on an earlier (inner-block) pass.
			}

			if ( ! isset( $GLOBALS['blocksy_child_perf_state']['lazy_count'] ) ) {
				$GLOBALS['blocksy_child_perf_state']['lazy_count'] = 0;
			}
			$position = ++$GLOBALS['blocksy_child_perf_state']['lazy_count'];

			$add = ' data-bc-perf-n="' . $position . '"';

			$skip = $position <= $eager
				|| preg_match( '#\sloading\s*=#i', $tag )
				|| preg_match( '#\sfetchpriority\s*=\s*["\']?\s*high#i', $tag )
				// Substring match inside the class attribute (as the BBC source
				// did) — deliberately loose: over-skipping only leaves an image
				// eager, under-skipping could lazy-load the LCP image.
				|| ( preg_match( '#\sclass\s*=\s*(["\'])(.*?)\1#i', $tag, $cm )
					&& preg_match( '#blaze-lcp-image|bc-perf-lcp|bc-perf-hero__poster|skip-lazy|kb-skip-lazy#i', $cm[2] ) );

			if ( ! $skip ) {
				$add .= ' loading="lazy"';
				if ( ! preg_match( '#\sdecoding\s*=#i', $tag ) ) {
					$add .= ' decoding="async"';
				}
			}

			return preg_replace( '#^<img\b#i', '<img' . $add, $tag, 1 );
		},
		$html
	);
}

/**
 * `render_block` @20 adapter for blocksy_child_perf_belowfold_lazy().
 *
 * @param mixed $block_content Rendered block HTML.
 * @param array $block         Parsed block (unused).
 * @return mixed
 */
function blocksy_child_perf_belowfold_lazy_render_block( $block_content, $block = [] ) {
	if ( ! blocksy_child_perf_is_frontend_render() ) {
		return $block_content; // REST content.rendered / feeds / admin / AJAX.
	}

	if ( ! is_string( $block_content ) || '' === $block_content ) {
		return $block_content;
	}

	return blocksy_child_perf_belowfold_lazy( $block_content );
}

// -----------------------------------------------------------------------
// (e) Stretched-height fix.
// -----------------------------------------------------------------------

/**
 * Add a `height` to an `<img>` that has a plain-integer `width` (not e.g.
 * `width="100%"`) but no `height`. Pure — no WP calls.
 *
 * The aspect ratio comes from the generated subsize the tag actually
 * displays when its `src` ends in a `-WxH.ext` suffix matching an entry in
 * `$meta['sizes']` (a hard-cropped subsize has a different ratio from the
 * original); otherwise from the original's `width`/`height`.
 *
 * @param string $img  The `<img>` tag.
 * @param array  $meta Attachment metadata.
 * @return string
 */
function blocksy_child_perf_img_height_fix( string $img, array $meta ): string {
	if ( preg_match( '#\sheight\s*=#i', $img ) ) {
		return $img;
	}

	if ( ! preg_match( '#\swidth\s*=\s*(["\']?)(\d+)\1(?=[\s/>])#i', $img, $m ) ) {
		return $img;
	}

	$ratio_w = (int) ( $meta['width'] ?? 0 );
	$ratio_h = (int) ( $meta['height'] ?? 0 );

	if ( ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] )
		&& preg_match( '#\ssrc\s*=\s*(["\'])(.*?)\1#i', $img, $src )
		&& preg_match( '#-(\d+)x(\d+)\.[a-z0-9]+$#i', (string) strtok( $src[2], '?' ), $dim ) ) {
		foreach ( $meta['sizes'] as $sub ) {
			if ( is_array( $sub ) && (int) ( $sub['width'] ?? 0 ) === (int) $dim[1] && (int) ( $sub['height'] ?? 0 ) === (int) $dim[2] ) {
				$ratio_w = (int) $dim[1];
				$ratio_h = (int) $dim[2];
				break;
			}
		}
	}

	if ( $ratio_w <= 0 || $ratio_h <= 0 ) {
		return $img;
	}

	$height = (int) round( (int) $m[2] * ( $ratio_h / $ratio_w ) );
	if ( $height < 1 ) {
		return $img;
	}

	return preg_replace( '#^<img\s#i', '<img height="' . $height . '" ', $img, 1 );
}

/**
 * `wp_content_img_tag` @20 — the attachment id comes from the tag's own
 * `wp-image-<id>` class token (no allow-list); no such class, no change.
 *
 * @param mixed  $filtered_image The `<img>` tag.
 * @param string $context        Filter context (unused).
 * @param int    $attachment_id  Core's resolved id (unused — the class is authoritative).
 * @return mixed
 */
function blocksy_child_perf_img_height_fix_filter( $filtered_image, $context = '', $attachment_id = 0 ) {
	if ( ! blocksy_child_perf_is_frontend_render() ) {
		return $filtered_image;
	}

	if ( ! is_string( $filtered_image ) || ! preg_match( '#\bwp-image-(\d+)\b#', $filtered_image, $m ) ) {
		return $filtered_image;
	}

	if ( preg_match( '#\sheight\s*=#i', $filtered_image ) || ! preg_match( '#\swidth\s*=#i', $filtered_image ) ) {
		return $filtered_image; // Skip the metadata read when there is nothing to fix.
	}

	$meta = wp_get_attachment_metadata( (int) $m[1] );

	return blocksy_child_perf_img_height_fix( $filtered_image, is_array( $meta ) ? $meta : [] );
}

// -----------------------------------------------------------------------
// (f) youtube-nocookie.
// -----------------------------------------------------------------------

/**
 * Rewrite `youtube.com/embed/` player URLs to `www.youtube-nocookie.com/embed/`.
 * Only `/embed/` URLs: a `watch?v=` link is an ordinary outbound link that
 * sets no cookies on this page.
 *
 * @param mixed $html Markup that may contain YouTube iframes.
 * @return mixed
 */
function blocksy_child_perf_youtube_nocookie( $html ) {
	if ( ! blocksy_child_perf_is_frontend_render() ) {
		return $html;
	}

	if ( ! is_string( $html ) || false === stripos( $html, 'youtube.com/embed/' ) ) {
		return $html;
	}

	return preg_replace(
		'#(https?:)?//(?:www\.)?youtube\.com/embed/#i',
		'$1//www.youtube-nocookie.com/embed/',
		$html
	);
}

// -----------------------------------------------------------------------
// Hook wiring.
// -----------------------------------------------------------------------

/**
 * Register every hook this module owns. Front-end only (Global Constraint 5).
 *
 * @return void
 */
function blocksy_child_perf_media_hygiene_register(): void {
	if ( ! blocksy_child_perf_is_frontend_request() ) {
		return;
	}

	add_filter( 'image_downsize', 'blocksy_child_perf_image_downsize_fallback', 10, 3 );
	add_filter( 'wp_get_attachment_image_attributes', 'blocksy_child_perf_logo_attributes', 999 );
	add_filter( 'wp_get_attachment_image_attributes', 'blocksy_child_perf_archive_thumb_attributes', 20, 3 );

	// Priority 20 — after lcp-image's cover-block pass at 10 (controller ruling).
	add_filter( 'render_block', 'blocksy_child_perf_belowfold_lazy_render_block', 20, 2 );

	add_filter( 'wp_content_img_tag', 'blocksy_child_perf_img_height_fix_filter', 20, 3 );

	add_filter( 'the_content', 'blocksy_child_perf_youtube_nocookie', 30 );
	add_filter( 'embed_oembed_html', 'blocksy_child_perf_youtube_nocookie', 30 );
	add_filter( 'widget_text_content', 'blocksy_child_perf_youtube_nocookie', 30 );
	add_filter( 'render_block', 'blocksy_child_perf_youtube_nocookie', 30 );
}

blocksy_child_perf_media_hygiene_register();
