<?php
/**
 * LCP Image — never-lazy + matching preload for the page's Largest
 * Contentful Paint image (PDP gallery, front-page hero, first `core/cover`
 * block).
 *
 * PURPOSE: "discoverable in the initial document" and "discovered early"
 * are different things. Lazy-loading (or a low fetch priority) on the LCP
 * image, and a `<head>` heavy with inlined critical CSS ahead of the
 * element in byte order, both push the image's request well past when the
 * browser could have asked for it. This module un-lazies the LCP candidate
 * on three surfaces — the WooCommerce product gallery image, the first
 * `core/cover` block, and an author-tagged front-page hero image — sets
 * `fetchpriority="high"` on it, and prints a matching `<link rel=preload>`
 * at `wp_head` priority 1 so the preload scanner queues the request before
 * it parses that far.
 *
 * THE `sizes="auto"` TRAP. Blocksy's product gallery ships
 * `sizes="auto, (max-width: 600px) 100vw, 600px"`. The `auto` keyword is
 * ONLY valid on a lazy-loaded image (WP 6.7 added it for exactly that
 * case). Remove `loading="lazy"` without also rewriting `sizes` and `auto`
 * becomes meaningless — the browser falls through to the next entry, which
 * at a typical mobile viewport selects the 1200w ORIGINAL instead of the
 * ~380px file actually rendered. Un-lazying the image without fixing
 * `sizes` in the SAME filter call makes the page slower, not faster. This
 * module always writes both together, from the one filter, and reuses the
 * exact same `sizes` string when it builds the matching preload — see
 * blocksy_child_perf_lcp_sizes() below.
 *
 * ONE HIGH-PRIORITY SLOT PER PAGE. `fetchpriority="high"` is a hint, not a
 * quota, but two `<link rel=preload fetchpriority=high>` tags on one page
 * split the browser's priority budget and can each arrive later than a
 * single one would have. blocksy_child_perf_lcp_preload_markup() —the one
 * function that ever emits a `<link rel=preload as=image>` tag in this
 * module (no other function echoes one) — keeps a request-scoped "slot
 * used" flag and only the first candidate wins; a second candidate logs via
 * error_log() and prints nothing. It also refuses a `data:` URI outright:
 * preloading an inline data URI fetches nothing (it is already in the
 * document) and only wastes the browser's preload-priority budget.
 *
 * PROMOTED FROM: `sites/alternateworlds/custom/includes/pdp-lcp-image.php`
 * (canonical — the `sizes` rewrite + first-render/later-render split +
 * `blocksy:woocommerce:image_additional_attributes` lazyload=false filter,
 * and the matching-preload discipline), the preload builder shape (src +
 * imagesrcset + imagesizes, `wp_get_attachment_image_srcset/sizes/url`) from
 * `sites/austinnaturalmattress/custom/product-lcp-image.php`, the first-
 * `core/cover`-block-only fetchpriority rewrite from
 * `sites/houstonnaturalmattress/custom/snippets/add-fetch-priority-to-cover-block.php`
 * (`git show fix/hnm-a11y-bp-90plus:…`), and the front-page hero derivation
 * (parse `wp-image-<id>` + a `-WxH` size suffix out of the saved block
 * content, because `wp_head` runs long before `the_content`) from
 * `aw_hero_preload_markup()` in `sites/alternateworlds/custom/homepage/homepage.php`.
 *
 * MEASURED EVIDENCE: AlternateWorlds PDP gallery image — LCP "load delay"
 * subpart 1,873 -> 238 ms (-87%, mobile, cold browser cache), 1,244 ms
 * (warm cache) before the fix; Chrome's LCPDiscovery insight failed 2 of 3
 * checks (fetchpriority not applied, loading=lazy present) beforehand.
 * AlternateWorlds homepage hero — LCP 1,415 ms with a 740 ms load-delay
 * subpart against a ~1 ms download (the file itself is tiny; the wait was
 * entirely ordering). birdcontrol LCP 14,038 -> 2,550 ms (-82%) after
 * un-lazying the equivalent element (headline case for this module family,
 * `.superpowers/sdd/…` plan spec). `sizes="auto"` is only valid on a
 * lazy-loaded image — un-lazy without rewriting `sizes` in the same call
 * and the 1200w original is chosen instead of the ~380px file actually
 * rendered (see the `sizes="auto"` TRAP section above).
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: no media files ship with this
 * module — it only rewrites attributes/markup around attachments the site
 * already has. The `bc-hero-img` class is an authoring convention a site's
 * content author opts into (via `blocksy_child_perf_hero_img_class`) on
 * whichever block they want treated as the hero; nothing here assumes a
 * particular homepage block layout.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------
// Pure helpers — no hook wiring below this point until
// blocksy_child_perf_lcp_image_register().
// -----------------------------------------------------------------------

/**
 * The registered image size Blocksy renders for the single-product gallery.
 * Both the single-image path and the multi-image flexy path request this
 * exact size, so matching on it targets the gallery image and nothing else.
 *
 * @return string
 */
function blocksy_child_perf_lcp_pdp_size(): string {
	return 'woocommerce_single';
}

/**
 * The `sizes` attribute used on the PDP gallery LCP image AND on its
 * matching preload's `imagesizes` — always read through this one function
 * so the two can never drift apart (Requirement: "preload built from the
 * same size and the same sizes string").
 *
 * @return string
 */
function blocksy_child_perf_lcp_sizes(): string {
	/**
	 * The PDP gallery LCP image's `sizes` attribute (and its preload's
	 * `imagesizes`).
	 *
	 * @param string $sizes Default '(min-width:1024px) 500px, (min-width:480px) calc(100vw - 48px), calc(100vw - 32px)'.
	 */
	return (string) apply_filters(
		'blocksy_child_perf_lcp_sizes',
		'(min-width:1024px) 500px, (min-width:480px) calc(100vw - 48px), calc(100vw - 32px)'
	);
}

/**
 * The class a content author adds to a front-page block's `<img>` to mark
 * it as the LCP hero candidate.
 *
 * @return string
 */
function blocksy_child_perf_lcp_hero_class(): string {
	/**
	 * The class blocksy_child_perf_lcp_hero_from_content() looks for.
	 *
	 * @param string $class Default 'bc-hero-img'.
	 */
	return (string) apply_filters( 'blocksy_child_perf_hero_img_class', 'bc-hero-img' );
}

/**
 * Build a `<link rel="preload" as="image">` tag — the ONLY place in this
 * module that ever emits one. Enforces both hard rules from the module
 * docblock: never a `data:` URI, never a second high-priority image
 * preload on one request.
 *
 * The "slot used" flag lives in $GLOBALS['blocksy_child_perf_state'] (not a
 * plain `static`) so tests/perf's blocksy_child_perf_reset_state() can give
 * each test case a clean slate — see inc/perf/helpers.php.
 *
 * @param string $src    Image URL. '' or a `data:` URI yields ''.
 * @param string $srcset Optional `srcset` candidate list for `imagesrcset`.
 * @param string $sizes  Optional `sizes` string for `imagesizes` (only
 *                        written when `$srcset` is non-empty — `imagesizes`
 *                        with no `imagesrcset` is meaningless).
 * @return string Markup, or '' when nothing should be preloaded.
 */
function blocksy_child_perf_lcp_preload_markup( string $src, string $srcset = '', string $sizes = '' ): string {
	if ( '' === $src ) {
		return '';
	}

	if ( 0 === stripos( $src, 'data:' ) ) {
		return '';
	}

	if ( ! empty( $GLOBALS['blocksy_child_perf_state']['lcp_slot_used'] ) ) {
		error_log( '[blocksy-child][perf] lcp-image: high-priority preload slot already used this request, skipping: ' . $src );
		return '';
	}

	$out = sprintf( '<link rel="preload" as="image" href="%s" fetchpriority="high"', esc_url( $src ) );

	// `imagesizes` is only meaningful alongside `imagesrcset`.
	if ( '' !== $srcset ) {
		$out .= sprintf( ' imagesrcset="%s"', esc_attr( $srcset ) );

		if ( '' !== $sizes ) {
			$out .= sprintf( ' imagesizes="%s"', esc_attr( $sizes ) );
		}
	}

	$out .= " />\n";

	$GLOBALS['blocksy_child_perf_state']['lcp_slot_used'] = true;

	return $out;
}

/**
 * Whether a core/cover block on this request may claim the LCP hint: only
 * when the queried singular post's FIRST top-level block is core/cover (so
 * the cover is the page's lead element, not one further down or in a
 * footer pattern) and no other LCP candidate already took the high-priority
 * preload slot (front-page `bc-hero-img`, PDP gallery). Fails closed: no
 * singular post, no parse_blocks(), or anything unexpected returns false.
 *
 * Memoised per request in $GLOBALS['blocksy_child_perf_state'].
 *
 * @return bool
 */
function blocksy_child_perf_lcp_cover_is_lead_block(): bool {
	if ( ! empty( $GLOBALS['blocksy_child_perf_state']['lcp_slot_used'] ) ) {
		return false;
	}

	if ( array_key_exists( 'lcp_cover_lead', $GLOBALS['blocksy_child_perf_state'] ?? [] ) ) {
		return $GLOBALS['blocksy_child_perf_state']['lcp_cover_lead'];
	}

	$lead = false;

	if ( function_exists( 'is_singular' ) && is_singular() && function_exists( 'get_post' ) && function_exists( 'parse_blocks' ) ) {
		$post = get_post( function_exists( 'get_queried_object_id' ) ? get_queried_object_id() : null );

		if ( $post && isset( $post->post_content ) ) {
			foreach ( parse_blocks( (string) $post->post_content ) as $top ) {
				if ( empty( $top['blockName'] ) ) {
					continue; // Whitespace between blocks.
				}

				$lead = ( 'core/cover' === $top['blockName'] );
				break;
			}
		}
	}

	$GLOBALS['blocksy_child_perf_state']['lcp_cover_lead'] = $lead;

	return $lead;
}

/**
 * Rewrite the first `<img>` inside a `core/cover` block's rendered markup:
 * add `fetchpriority="high"` and strip `loading="lazy"`.
 *
 * Once-per-request (only the first `core/cover` block containing an `<img`
 * is ever touched — later calls this request return their input unchanged)
 * and idempotent (if THAT `<img>` — the one this function would otherwise
 * rewrite, not merely something else in the block's markup — already
 * carries a `fetchpriority` attribute — core, WP 6.3+, may already have set
 * one — that `<img>` is left exactly as given, including its `loading`
 * attribute, rather than risk a duplicate attribute). The `fetchpriority=`
 * check is scoped to the matched `<img>` tag itself, deliberately: a cover
 * block can carry other elements (a `<video>` poster, a second `<img>`
 * later in the same block) that happen to carry their own `fetchpriority`
 * attribute unrelated to this one — checking the whole block's HTML would
 * wrongly skip rewriting the first `<img>` because of an attribute on a
 * completely different element.
 *
 * The caller (blocksy_child_perf_lcp_image_register()) is responsible for
 * only passing `core/cover` block content — this function itself does not
 * inspect `$block['blockName']`.
 *
 * @param string $html Rendered `core/cover` block HTML.
 * @return string
 */
function blocksy_child_perf_lcp_cover_block_rewrite( string $html ): string {
	if ( ! empty( $GLOBALS['blocksy_child_perf_state']['lcp_cover_done'] ) ) {
		return $html;
	}

	if ( false === strpos( $html, '<img' ) ) {
		return $html;
	}

	// The first core/cover block carrying an <img> claims the slot,
	// whether or not the rewrite below actually changes anything.
	$GLOBALS['blocksy_child_perf_state']['lcp_cover_done'] = true;

	return preg_replace_callback(
		'#<img\b[^>]*>#i',
		function ( $m ) {
			$tag = $m[0];

			if ( false !== stripos( $tag, 'fetchpriority=' ) ) {
				return $tag; // core already handled THIS <img> — idempotent, no double attribute.
			}

			$tag = preg_replace( '#<img\s+#i', '<img fetchpriority="high" ', $tag, 1 );
			$tag = preg_replace( '#\s*loading=(["\'])lazy\1#i', '', $tag, 1 );
			return $tag;
		},
		$html,
		1
	);
}

/**
 * Derive the front-page hero image's attachment id + size from the front
 * page's SAVED block content (raw `post_content`, not rendered `the_content`
 * output — `wp_head`, where this feeds the preload, runs long before
 * `the_content` does).
 *
 * Finds the first `<img>` whose `class` attribute contains `$class` as a
 * whole class token, reads the WordPress-core `wp-image-<id>` class token
 * core itself adds to every content image for the attachment id, and reads
 * a `-WxH` size suffix off the `src` filename exactly as
 * `wp_image_add_srcset_and_sizes()` does (falling back to `'full'` when
 * there is no such suffix — the original, unscaled file).
 *
 * Fails open: no `$content`, no matching `<img>`, or a matching `<img>`
 * with no `wp-image-<id>` class all return `null` — never a guess.
 *
 * @param string $content Raw `post_content` to search.
 * @param string $class   The hero marker class to match (see
 *                          blocksy_child_perf_lcp_hero_class()).
 * @return array{id:int,size:string|array}|null
 */
function blocksy_child_perf_lcp_hero_from_content( string $content, string $class ) {
	if ( '' === $content || '' === $class ) {
		return null;
	}

	if ( ! preg_match_all( '#<img\b[^>]*>#i', $content, $tags ) ) {
		return null;
	}

	foreach ( $tags[0] as $tag ) {
		if ( ! preg_match( '#\bclass=(["\'])(.*?)\1#i', $tag, $class_match ) ) {
			continue;
		}

		$classes = preg_split( '/\s+/', trim( $class_match[2] ) );

		if ( ! in_array( $class, $classes, true ) ) {
			continue;
		}

		// This is the hero <img> — either it has a usable attachment id, or
		// there is no match at all (never fall through to a different <img>).
		if ( ! preg_match( '#\bwp-image-(\d+)\b#i', $tag, $id_match ) ) {
			return null;
		}

		$size = 'full';

		if ( preg_match( '#\bsrc=(["\'])(.*?)\1#i', $tag, $src_match ) ) {
			$src_path = strtok( $src_match[2], '?' );

			if ( preg_match( '#-(\d+)x(\d+)\.[a-z0-9]+$#i', (string) $src_path, $dim ) ) {
				$size = [ (int) $dim[1], (int) $dim[2] ];
			}
		}

		return [
			'id'   => (int) $id_match[1],
			'size' => $size,
		];
	}

	return null;
}

/**
 * Add `fetchpriority="high"` and strip `loading="lazy"` on the front-page
 * hero `<img>` inside RENDERED content (the `the_content` pass — the
 * counterpart to blocksy_child_perf_lcp_hero_from_content(), which reads
 * the raw, unrendered content for the earlier `wp_head` preload).
 *
 * Scans every `<img>` in `$html` but only ever rewrites the FIRST one whose
 * class list contains `$class` — later images (including later `<img>`s
 * that also happen to carry `$class`, which should not occur for a single
 * hero image but is guarded against regardless) are left untouched.
 *
 * @param string $html  Rendered post content.
 * @param string $class The hero marker class to match.
 * @return string
 */
function blocksy_child_perf_lcp_hero_content_attributes( string $html, string $class ) {
	if ( '' === $html || '' === $class ) {
		return $html;
	}

	$done = false;

	return preg_replace_callback(
		'#<img\b[^>]*>#i',
		function ( $m ) use ( $class, &$done ) {
			$tag = $m[0];

			if ( $done ) {
				return $tag;
			}

			if ( ! preg_match( '#\bclass=(["\'])(.*?)\1#i', $tag, $class_match ) ) {
				return $tag;
			}

			$classes = preg_split( '/\s+/', trim( $class_match[2] ) );

			if ( ! in_array( $class, $classes, true ) ) {
				return $tag;
			}

			$done = true;

			$tag = preg_replace( '#\s*loading=(["\'])lazy\1#i', '', $tag, 1 );

			if ( false === stripos( $tag, 'fetchpriority=' ) ) {
				$tag = preg_replace( '#<img\s+#i', '<img fetchpriority="high" ', $tag, 1 );
			}

			return $tag;
		},
		$html
	);
}

// -----------------------------------------------------------------------
// Hook wiring.
// -----------------------------------------------------------------------

/**
 * `wp_get_attachment_image_attributes` filter — PDP gallery image.
 *
 * Only ever acts when `is_singular('product')` and the attachment being
 * rendered is the queried product's featured image
 * (`get_post_thumbnail_id()`, no args — the current post in the loop).
 *
 * Two branches, mirroring the canonical AlternateWorlds source:
 *
 * 1. `$size === blocksy_child_perf_lcp_pdp_size()`, first occurrence this
 *    request: the gallery's own render. `loading` is unset,
 *    `fetchpriority="high"` + `decoding="sync"` are added, `class` gains
 *    `blaze-lcp-image bc-perf-lcp`, and `sizes` is overwritten with
 *    blocksy_child_perf_lcp_sizes() (see the module docblock's
 *    `sizes="auto"` trap section for why this MUST happen in the same
 *    filter call as dropping `loading`). A later call at this same size
 *    (e.g. a duplicate render) is left completely unchanged.
 *
 * 2. Any OTHER size, once the gallery render above has already happened:
 *    forced `loading="lazy"`, `fetchpriority` unset. On a product page
 *    this is the floating add-to-cart bar's small copy of the same
 *    featured image — eager + un-sized, it would otherwise compete with
 *    the real LCP element for priority. Gated on "the gallery already
 *    rendered" so a call at a different size BEFORE the gallery renders is
 *    never forced lazy by accident.
 *
 * @param array   $attr       Image attributes.
 * @param WP_Post $attachment Attachment post object.
 * @param mixed   $size       Requested image size.
 * @return array
 */
function blocksy_child_perf_lcp_pdp_attributes( $attr, $attachment, $size ) {
	if ( ! function_exists( 'is_singular' ) || ! is_singular( 'product' ) ) {
		return $attr;
	}

	$featured = function_exists( 'get_post_thumbnail_id' ) ? (int) get_post_thumbnail_id() : 0;

	if ( ! $featured || ! isset( $attachment->ID ) || (int) $attachment->ID !== $featured ) {
		return $attr;
	}

	$gallery_size = blocksy_child_perf_lcp_pdp_size();

	if ( $gallery_size !== $size ) {
		if ( ! empty( $GLOBALS['blocksy_child_perf_state']['lcp_pdp_done'] ) ) {
			$attr['loading'] = 'lazy';
			unset( $attr['fetchpriority'] );
		}

		return $attr;
	}

	if ( ! empty( $GLOBALS['blocksy_child_perf_state']['lcp_pdp_done'] ) ) {
		return $attr;
	}

	$GLOBALS['blocksy_child_perf_state']['lcp_pdp_done'] = true;

	$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] . ' ' : '' ) . 'blaze-lcp-image bc-perf-lcp' );

	unset( $attr['loading'] );
	$attr['fetchpriority'] = 'high';
	$attr['decoding']      = 'sync';
	$attr['sizes']         = blocksy_child_perf_lcp_sizes();

	return $attr;
}

/**
 * `blocksy:woocommerce:image_additional_attributes` filter — tells Blocksy
 * not to lazy-load the single-image gallery at source, on a product page.
 * Belt-and-braces alongside blocksy_child_perf_lcp_pdp_attributes()'s own
 * `unset($attr['loading'])`: `blocksy_media()` passes this flag straight
 * through to `wp_get_attachment_image()` as `['loading' => false]`, so core
 * never emits a `loading` attribute at all, rather than relying on
 * Blocksy's own attribute-string reassembly to drop one already set.
 *
 * First call per request only. The gallery renders ahead of the rest of the
 * single-product template (`woocommerce_before_single_product_summary`), so
 * the first image through this filter is the main/LCP image; later calls
 * (other gallery slides, related / upsell cards) keep Blocksy's lazyload.
 * Same rule as Bonza's `bonza_pdp_main_gallery_image_fetchpriority()`.
 *
 * @param array $args Blocksy media args.
 * @return array
 */
function blocksy_child_perf_lcp_pdp_lazyload_false( $args ) {
	if ( ! function_exists( 'is_singular' ) || ! is_singular( 'product' ) ) {
		return $args;
	}

	if ( ! empty( $GLOBALS['blocksy_child_perf_state']['lcp_pdp_lazyload_done'] ) ) {
		return $args;
	}

	$GLOBALS['blocksy_child_perf_state']['lcp_pdp_lazyload_done'] = true;

	$args['lazyload'] = false;

	return $args;
}

/**
 * Build the PDP gallery preload markup. Same attachment
 * (`get_post_thumbnail_id()`), same size (blocksy_child_perf_lcp_pdp_size())
 * and same `sizes` string (blocksy_child_perf_lcp_sizes()) as
 * blocksy_child_perf_lcp_pdp_attributes() writes onto the element itself —
 * required so the preload is actually reused instead of costing a second
 * download.
 *
 * @return string
 */
function blocksy_child_perf_lcp_pdp_preload_markup(): string {
	$id = function_exists( 'get_post_thumbnail_id' ) ? (int) get_post_thumbnail_id() : 0;

	if ( ! $id ) {
		return '';
	}

	$size = blocksy_child_perf_lcp_pdp_size();

	$src = function_exists( 'wp_get_attachment_image_url' ) ? wp_get_attachment_image_url( $id, $size ) : '';

	if ( ! $src ) {
		return '';
	}

	$srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $id, $size ) : '';

	return blocksy_child_perf_lcp_preload_markup( (string) $src, (string) $srcset, blocksy_child_perf_lcp_sizes() );
}

/**
 * Build the front-page hero preload markup: derive the hero attachment +
 * size from the front page's raw saved content
 * (blocksy_child_perf_lcp_hero_from_content()), then resolve the same
 * `src`/`srcset`/`sizes` core itself would render for that attachment at
 * that size.
 *
 * @return string
 */
function blocksy_child_perf_lcp_hero_preload_markup(): string {
	$front_id = function_exists( 'get_option' ) ? (int) get_option( 'page_on_front' ) : 0;

	if ( ! $front_id ) {
		return '';
	}

	$content = function_exists( 'get_post_field' ) ? (string) get_post_field( 'post_content', $front_id ) : '';

	if ( '' === $content ) {
		return '';
	}

	$hero = blocksy_child_perf_lcp_hero_from_content( $content, blocksy_child_perf_lcp_hero_class() );

	if ( null === $hero ) {
		return '';
	}

	$src = function_exists( 'wp_get_attachment_image_url' ) ? wp_get_attachment_image_url( $hero['id'], $hero['size'] ) : '';

	if ( ! $src ) {
		return '';
	}

	$srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $hero['id'], $hero['size'] ) : '';
	$sizes  = ( '' !== (string) $srcset && function_exists( 'wp_get_attachment_image_sizes' ) )
		? (string) wp_get_attachment_image_sizes( $hero['id'], $hero['size'] )
		: '';

	return blocksy_child_perf_lcp_preload_markup( (string) $src, (string) $srcset, $sizes );
}

/**
 * Register every hook this module owns. Front-end only (Global Constraint
 * 5) — nothing below is registered at all unless
 * blocksy_child_perf_is_frontend_request().
 *
 * @return void
 */
function blocksy_child_perf_lcp_image_register(): void {
	if ( ! blocksy_child_perf_is_frontend_request() ) {
		return;
	}

	// PDP gallery image: attribute rewrite + Blocksy's own lazyload flag.
	add_filter( 'wp_get_attachment_image_attributes', 'blocksy_child_perf_lcp_pdp_attributes', 10, 3 );
	add_filter( 'blocksy:woocommerce:image_additional_attributes', 'blocksy_child_perf_lcp_pdp_lazyload_false' );

	// PDP gallery image: matching preload, wp_head priority 1.
	add_action(
		'wp_head',
		function () {
			if ( ! function_exists( 'is_singular' ) || ! is_singular( 'product' ) ) {
				return;
			}

			echo blocksy_child_perf_lcp_pdp_preload_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_url()/esc_attr() inside blocksy_child_perf_lcp_preload_markup().
		},
		1
	);

	// Lead core/cover block (first top-level block of a singular post, no other
	// LCP preload this request): fetchpriority + un-lazy, render_block priority 10
	// (Task 8's below-fold lazy pass runs at priority 20 and skips images already
	// carrying fetchpriority="high" — controller ruling).
	add_filter(
		'render_block',
		function ( $block_content, $block ) {
			if ( ! blocksy_child_perf_is_frontend_render() ) {
				return $block_content; // REST content.rendered / feeds / admin / AJAX.
			}

			if ( ! isset( $block['blockName'] ) || 'core/cover' !== $block['blockName'] ) {
				return $block_content;
			}

			if ( ! blocksy_child_perf_lcp_cover_is_lead_block() ) {
				return $block_content;
			}

			return blocksy_child_perf_lcp_cover_block_rewrite( (string) $block_content );
		},
		10,
		2
	);

	// Front-page hero: matching preload, wp_head priority 1.
	add_action(
		'wp_head',
		function () {
			if ( ! function_exists( 'is_front_page' ) || ! is_front_page() ) {
				return;
			}

			echo blocksy_child_perf_lcp_hero_preload_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_url()/esc_attr() inside blocksy_child_perf_lcp_preload_markup().
		},
		1
	);

	// Front-page hero: fetchpriority + un-lazy on the rendered <img>, the_content priority 20.
	add_filter(
		'the_content',
		function ( $html ) {
			if ( ! blocksy_child_perf_is_frontend_render() ) {
				return $html;
			}

			if ( ! function_exists( 'is_front_page' ) || ! is_front_page() ) {
				return $html;
			}

			return blocksy_child_perf_lcp_hero_content_attributes( $html, blocksy_child_perf_lcp_hero_class() );
		},
		20
	);
}

blocksy_child_perf_lcp_image_register();
