<?php
/**
 * Tests for inc/perf/lcp-image.php.
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/inc/perf/helpers.php';
require_once dirname( __DIR__, 2 ) . '/inc/perf/lcp-image.php';

// -----------------------------------------------------------------------
// Registration shape — run FIRST, before any other test in this file
// mutates $GLOBALS['bc_test_hooks'] or blocksy_child_perf_lcp_image_register()
// is called again, since that module-level call only happens once, at the
// require above.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lcp_image_register(): wires every hook at its mandated priority', function () {
	assert_same( isset( $GLOBALS['bc_test_hooks']['wp_get_attachment_image_attributes'][10] ), true, 'PDP attribute filter at priority 10' );
	assert_same( in_array( 'blocksy_child_perf_lcp_pdp_attributes', $GLOBALS['bc_test_hooks']['wp_get_attachment_image_attributes'][10], true ), true );

	assert_same( isset( $GLOBALS['bc_test_hooks']['blocksy:woocommerce:image_additional_attributes'][10] ), true, "Blocksy's own lazyload filter at its default priority 10" );
	assert_same( in_array( 'blocksy_child_perf_lcp_pdp_lazyload_false', $GLOBALS['bc_test_hooks']['blocksy:woocommerce:image_additional_attributes'][10], true ), true );

	assert_same( isset( $GLOBALS['bc_test_hooks']['wp_head'][1] ), true, 'wp_head priority 1 has callbacks' );
	assert_same( count( $GLOBALS['bc_test_hooks']['wp_head'][1] ) >= 2, true, 'both the PDP preload and the hero preload are registered at wp_head priority 1' );

	assert_same( isset( $GLOBALS['bc_test_hooks']['render_block'][10] ), true, 'render_block at priority 10 (below Task 8\'s below-fold lazy pass at priority 20)' );
	assert_same( isset( $GLOBALS['bc_test_hooks']['the_content'][20] ), true, 'the_content at priority 20' );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_lcp_pdp_attributes() — PDP featured-image attribute
// rewrite. First vs later render, using a fake $attachment object and the
// get_post_thumbnail_id() stub declared at the bottom of this file.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lcp_pdp_attributes(): not is_singular(product) -> attributes untouched', function () {
	global $bc_test_is_singular_product;
	$bc_test_is_singular_product = false;

	$attr = blocksy_child_perf_lcp_pdp_attributes( [ 'loading' => 'lazy' ], (object) [ 'ID' => 42 ], 'woocommerce_single' );

	assert_same( $attr, [ 'loading' => 'lazy' ], 'unchanged off a product singular' );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_attributes(): different attachment (not the featured image) -> attributes untouched', function () {
	global $bc_test_is_singular_product, $bc_test_post_thumbnail_id;
	$bc_test_is_singular_product = true;
	$bc_test_post_thumbnail_id   = 42;

	$attr = blocksy_child_perf_lcp_pdp_attributes( [ 'loading' => 'lazy' ], (object) [ 'ID' => 999 ], 'woocommerce_single' );

	assert_same( $attr, [ 'loading' => 'lazy' ], 'a different attachment id is never touched' );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_attributes(): a DIFFERENT size, BEFORE the gallery has rendered -> left untouched (never guessed lazy)', function () {
	global $bc_test_is_singular_product, $bc_test_post_thumbnail_id;
	$bc_test_is_singular_product = true;
	$bc_test_post_thumbnail_id   = 42;

	$attr = blocksy_child_perf_lcp_pdp_attributes( [ 'loading' => 'lazy' ], (object) [ 'ID' => 42 ], 'thumbnail' );

	assert_same( $attr, [ 'loading' => 'lazy' ], 'not forced lazy — the real gallery render has not happened yet this request' );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_attributes(): FIRST render at woocommerce_single gets the full LCP treatment', function () {
	global $bc_test_is_singular_product, $bc_test_post_thumbnail_id;
	$bc_test_is_singular_product = true;
	$bc_test_post_thumbnail_id   = 42;

	$attr = blocksy_child_perf_lcp_pdp_attributes( [ 'class' => 'wp-image-42', 'loading' => 'lazy' ], (object) [ 'ID' => 42 ], 'woocommerce_single' );

	assert_same( strpos( $attr['class'], 'blaze-lcp-image' ) !== false, true, 'blaze-lcp-image class present (Perfmatters compatibility)' );
	assert_same( strpos( $attr['class'], 'bc-perf-lcp' ) !== false, true, 'bc-perf-lcp class marker present' );
	assert_same( strpos( $attr['class'], 'wp-image-42' ) !== false, true, 'pre-existing class preserved, not overwritten' );
	assert_same( isset( $attr['loading'] ), false, 'loading unset' );
	assert_same( $attr['fetchpriority'], 'high' );
	assert_same( $attr['decoding'], 'sync' );
	assert_same( $attr['sizes'], blocksy_child_perf_lcp_sizes(), 'element sizes == the shared sizes helper' );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_attributes(): a SECOND render at woocommerce_single this request is left untouched', function () {
	global $bc_test_is_singular_product, $bc_test_post_thumbnail_id;
	$bc_test_is_singular_product = true;
	$bc_test_post_thumbnail_id   = 42;

	$attr = blocksy_child_perf_lcp_pdp_attributes( [ 'loading' => 'lazy' ], (object) [ 'ID' => 42 ], 'woocommerce_single' );

	assert_same( $attr, [ 'loading' => 'lazy' ], 'a duplicate render at the gallery size is not re-promoted (or demoted)' );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_attributes(): a DIFFERENT size, AFTER the gallery has rendered -> forced lazy, no fetchpriority', function () {
	global $bc_test_is_singular_product, $bc_test_post_thumbnail_id;
	$bc_test_is_singular_product = true;
	$bc_test_post_thumbnail_id   = 42;

	// e.g. the floating add-to-cart bar's small copy of the same featured image.
	$attr = blocksy_child_perf_lcp_pdp_attributes( [ 'fetchpriority' => 'high' ], (object) [ 'ID' => 42 ], [ 100, 100 ] );

	assert_same( $attr['loading'], 'lazy' );
	assert_same( isset( $attr['fetchpriority'] ), false, 'fetchpriority stripped from the later, smaller copy' );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_lcp_pdp_lazyload_false() — Blocksy's own lazyload flag.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lcp_pdp_lazyload_false(): sets lazyload=false on a product singular', function () {
	global $bc_test_is_singular_product;
	$bc_test_is_singular_product = true;

	assert_same( blocksy_child_perf_lcp_pdp_lazyload_false( [ 'lazyload' => 'yes' ] ), [ 'lazyload' => false ] );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_lazyload_false(): untouched off a product singular', function () {
	global $bc_test_is_singular_product;
	$bc_test_is_singular_product = false;

	assert_same( blocksy_child_perf_lcp_pdp_lazyload_false( [ 'lazyload' => 'yes' ] ), [ 'lazyload' => 'yes' ] );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_lcp_sizes() — the blocksy_child_perf_lcp_sizes filter.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lcp_sizes(): default value', function () {
	assert_same(
		blocksy_child_perf_lcp_sizes(),
		'(min-width:1024px) 500px, (min-width:480px) calc(100vw - 48px), calc(100vw - 32px)'
	);
} );

bc_test( 'blocksy_child_perf_lcp_sizes(): blocksy_child_perf_lcp_sizes filter is respected', function () {
	$cb = function () {
		return '100vw';
	};
	add_filter( 'blocksy_child_perf_lcp_sizes', $cb );

	assert_same( blocksy_child_perf_lcp_sizes(), '100vw' );

	remove_filter( 'blocksy_child_perf_lcp_sizes', $cb );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_lcp_preload_markup() — the single preload builder.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lcp_preload_markup(): builds href + imagesrcset + imagesizes', function () {
	blocksy_child_perf_reset_state();

	$out = blocksy_child_perf_lcp_preload_markup(
		'https://example.test/img-1024x768.jpg',
		'https://example.test/img-600.jpg 600w, https://example.test/img-1024.jpg 1024w',
		'(min-width:1024px) 500px, 100vw'
	);

	assert_same(
		$out,
		'<link rel="preload" as="image" href="https://example.test/img-1024x768.jpg" fetchpriority="high" imagesrcset="https://example.test/img-600.jpg 600w, https://example.test/img-1024.jpg 1024w" imagesizes="(min-width:1024px) 500px, 100vw" />' . "\n"
	);
} );

bc_test( 'blocksy_child_perf_lcp_preload_markup(): no srcset -> no imagesrcset/imagesizes attributes at all', function () {
	blocksy_child_perf_reset_state();

	$out = blocksy_child_perf_lcp_preload_markup( 'https://example.test/img.jpg' );

	assert_same( $out, '<link rel="preload" as="image" href="https://example.test/img.jpg" fetchpriority="high" />' . "\n" );
} );

bc_test( 'blocksy_child_perf_lcp_preload_markup(): srcset without sizes -> imagesrcset only, no imagesizes', function () {
	blocksy_child_perf_reset_state();

	$out = blocksy_child_perf_lcp_preload_markup( 'https://example.test/img.jpg', 'https://example.test/img-600.jpg 600w' );

	assert_same( strpos( $out, 'imagesrcset=' ) !== false, true );
	assert_same( strpos( $out, 'imagesizes=' ) !== false, false, 'imagesizes never written without imagesrcset context' );
} );

bc_test( 'blocksy_child_perf_lcp_preload_markup(): empty src -> \'\'', function () {
	blocksy_child_perf_reset_state();

	assert_same( blocksy_child_perf_lcp_preload_markup( '' ), '' );
} );

bc_test( 'blocksy_child_perf_lcp_preload_markup(): a data: URI is never preloaded', function () {
	blocksy_child_perf_reset_state();

	assert_same( blocksy_child_perf_lcp_preload_markup( 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==' ), '' );
} );

bc_test( 'blocksy_child_perf_lcp_preload_markup(): high-priority slot guard — a second candidate this request is refused and logged', function () {
	blocksy_child_perf_reset_state();

	$first = blocksy_child_perf_lcp_preload_markup( 'https://example.test/first.jpg' );
	assert_same( $first !== '', true, 'first candidate takes the slot' );

	$second = blocksy_child_perf_lcp_preload_markup( 'https://example.test/second.jpg' );
	assert_same( $second, '', 'second candidate this request is refused' );

	$logged = false;
	foreach ( bc_test_error_log_messages() as $message ) {
		if ( false !== strpos( $message, '[blocksy-child][perf] lcp-image: high-priority preload slot already used this request, skipping: https://example.test/second.jpg' ) ) {
			$logged = true;
		}
	}
	assert_same( $logged, true, 'refusal is logged with the skipped URL' );
} );

bc_test( 'blocksy_child_perf_lcp_preload_markup(): the slot guard applies across separate calls (e.g. PDP preload vs hero preload)', function () {
	blocksy_child_perf_reset_state();

	global $bc_test_is_singular_product, $bc_test_post_thumbnail_id, $bc_test_attachment_urls, $bc_test_attachment_srcsets;
	global $bc_test_is_front_page, $bc_test_option_page_on_front, $bc_test_post_field_content;

	$bc_test_is_singular_product              = true;
	$bc_test_post_thumbnail_id                = 42;
	$bc_test_attachment_urls['42:woocommerce_single']    = 'https://example.test/pdp.jpg';
	$bc_test_attachment_srcsets['42:woocommerce_single']  = '';

	$pdp_markup = blocksy_child_perf_lcp_pdp_preload_markup();
	assert_same( strpos( $pdp_markup, 'pdp.jpg' ) !== false, true, 'PDP preload takes the slot' );

	$bc_test_is_front_page          = true;
	$bc_test_option_page_on_front   = 7;
	$bc_test_post_field_content     = '<img src="https://example.test/hero.jpg" class="bc-hero-img wp-image-99">';

	$hero_markup = blocksy_child_perf_lcp_hero_preload_markup();
	assert_same( $hero_markup, '', 'hero preload refused — the PDP preload already used the one slot' );

	blocksy_child_perf_reset_state();
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_lcp_cover_block_rewrite() — first core/cover block only.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lcp_cover_block_rewrite(): adds fetchpriority=high + strips loading=lazy on the first <img>', function () {
	blocksy_child_perf_reset_state();

	$html = '<div class="wp-block-cover"><img src="a.jpg" loading="lazy" class="wp-image-1"/><p>text</p></div>';
	$out  = blocksy_child_perf_lcp_cover_block_rewrite( $html );

	assert_same( strpos( $out, 'fetchpriority="high"' ) !== false, true );
	assert_same( strpos( $out, 'loading="lazy"' ) !== false, false, 'loading=lazy stripped' );
} );

bc_test( 'blocksy_child_perf_lcp_cover_block_rewrite(): once only — a second cover block this request is left untouched', function () {
	$html = '<div class="wp-block-cover"><img src="b.jpg" loading="lazy" class="wp-image-2"/></div>';
	$out  = blocksy_child_perf_lcp_cover_block_rewrite( $html );

	assert_same( $out, $html, 'the slot was already claimed by the first cover block above' );
} );

bc_test( 'blocksy_child_perf_lcp_cover_block_rewrite(): no <img> in the block -> untouched, and does not consume the slot', function () {
	blocksy_child_perf_reset_state();

	$no_img = '<div class="wp-block-cover"><p>text only</p></div>';
	assert_same( blocksy_child_perf_lcp_cover_block_rewrite( $no_img ), $no_img );

	// The slot is still free — the next block WITH an <img> is still the first eligible one.
	$html = '<div class="wp-block-cover"><img src="c.jpg" loading="lazy"/></div>';
	$out  = blocksy_child_perf_lcp_cover_block_rewrite( $html );
	assert_same( strpos( $out, 'fetchpriority="high"' ) !== false, true );
} );

bc_test( 'blocksy_child_perf_lcp_cover_block_rewrite(): idempotent — an <img> that already carries fetchpriority is left exactly as given', function () {
	blocksy_child_perf_reset_state();

	$html = '<div class="wp-block-cover"><img src="d.jpg" fetchpriority="high" loading="lazy" class="wp-image-4"/></div>';
	$out  = blocksy_child_perf_lcp_cover_block_rewrite( $html );

	assert_same( $out, $html, "core already set fetchpriority — left untouched, no duplicate attribute, loading left as-is" );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_lcp_hero_from_content() — front-page hero derivation.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lcp_hero_from_content(): id + WxH size parsed from a matching <img>', function () {
	$content = '<p>intro</p><figure><img src="https://example.test/wp-content/uploads/2026/01/hero-1200x600.jpg" class="bc-hero-img wp-image-321" alt=""/></figure>';

	$result = blocksy_child_perf_lcp_hero_from_content( $content, 'bc-hero-img' );

	assert_same( $result, [ 'id' => 321, 'size' => [ 1200, 600 ] ] );
} );

bc_test( 'blocksy_child_perf_lcp_hero_from_content(): no WxH suffix in the src -> size falls back to \'full\'', function () {
	$content = '<img src="https://example.test/hero-original.jpg" class="bc-hero-img wp-image-55"/>';

	$result = blocksy_child_perf_lcp_hero_from_content( $content, 'bc-hero-img' );

	assert_same( $result, [ 'id' => 55, 'size' => 'full' ] );
} );

bc_test( 'blocksy_child_perf_lcp_hero_from_content(): no <img> carries the hero class -> null', function () {
	$content = '<img src="https://example.test/other-300x200.jpg" class="wp-image-1 some-other-class"/>';

	assert_same( blocksy_child_perf_lcp_hero_from_content( $content, 'bc-hero-img' ), null );
} );

bc_test( 'blocksy_child_perf_lcp_hero_from_content(): empty content -> null', function () {
	assert_same( blocksy_child_perf_lcp_hero_from_content( '', 'bc-hero-img' ), null );
} );

bc_test( 'blocksy_child_perf_lcp_hero_from_content(): matching class but no wp-image-<id> token -> null (fails open, never guesses)', function () {
	$content = '<img src="https://example.test/hero-800x400.jpg" class="bc-hero-img"/>';

	assert_same( blocksy_child_perf_lcp_hero_from_content( $content, 'bc-hero-img' ), null );
} );

bc_test( 'blocksy_child_perf_lcp_hero_from_content(): a class that merely CONTAINS the token as a substring does not match (whole-token match only)', function () {
	$content = '<img src="https://example.test/hero-800x400.jpg" class="my-bc-hero-img-wrapper wp-image-9"/>';

	assert_same( blocksy_child_perf_lcp_hero_from_content( $content, 'bc-hero-img' ), null );
} );

bc_test( 'blocksy_child_perf_lcp_hero_from_content(): a custom class (via the $class argument) is honoured', function () {
	$content = '<img src="https://example.test/hero-800x400.jpg" class="my-hero wp-image-9"/>';

	assert_same( blocksy_child_perf_lcp_hero_from_content( $content, 'my-hero' ), [ 'id' => 9, 'size' => [ 800, 400 ] ] );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_lcp_hero_content_attributes() — the_content pass.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lcp_hero_content_attributes(): adds fetchpriority + strips loading=lazy on the matching <img> only', function () {
	$html = '<img src="a.jpg" class="wp-image-1" loading="lazy"/><img src="b.jpg" class="bc-hero-img wp-image-2" loading="lazy"/>';

	$out = blocksy_child_perf_lcp_hero_content_attributes( $html, 'bc-hero-img' );

	assert_same( strpos( $out, '<img src="a.jpg" class="wp-image-1" loading="lazy"/>' ) !== false, true, 'non-hero image untouched' );
	assert_same( strpos( $out, 'fetchpriority="high"' ) !== false, true, 'hero image gets fetchpriority' );
	assert_same( substr_count( $out, 'loading="lazy"' ), 1, 'only the hero image lost its loading=lazy' );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_lcp_pdp_preload_markup() / blocksy_child_perf_lcp_hero_preload_markup()
// — the full assemblers, using the WP stubs declared below.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lcp_pdp_preload_markup(): builds from get_post_thumbnail_id() + woocommerce_single, same sizes string as the element', function () {
	blocksy_child_perf_reset_state();

	global $bc_test_is_singular_product, $bc_test_post_thumbnail_id, $bc_test_attachment_urls, $bc_test_attachment_srcsets;
	$bc_test_is_singular_product = true;
	$bc_test_post_thumbnail_id   = 77;
	$bc_test_attachment_urls['77:woocommerce_single']   = 'https://example.test/gallery.jpg';
	$bc_test_attachment_srcsets['77:woocommerce_single'] = 'https://example.test/gallery-600.jpg 600w';

	$out = blocksy_child_perf_lcp_pdp_preload_markup();

	assert_same( strpos( $out, 'href="https://example.test/gallery.jpg"' ) !== false, true );
	assert_same( strpos( $out, 'imagesrcset="https://example.test/gallery-600.jpg 600w"' ) !== false, true );
	assert_same( strpos( $out, 'imagesizes="' . blocksy_child_perf_lcp_sizes() . '"' ) !== false, true, 'preload imagesizes == the element sizes helper' );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_preload_markup(): no featured image -> \'\'', function () {
	blocksy_child_perf_reset_state();

	global $bc_test_is_singular_product, $bc_test_post_thumbnail_id;
	$bc_test_is_singular_product = true;
	$bc_test_post_thumbnail_id   = 0;

	assert_same( blocksy_child_perf_lcp_pdp_preload_markup(), '' );
} );

bc_test( 'blocksy_child_perf_lcp_hero_preload_markup(): builds from the front page\'s post_content', function () {
	blocksy_child_perf_reset_state();

	global $bc_test_option_page_on_front, $bc_test_post_field_content, $bc_test_attachment_urls, $bc_test_attachment_srcsets, $bc_test_attachment_sizes;

	$bc_test_option_page_on_front = 7;
	$bc_test_post_field_content   = '<figure><img src="https://example.test/hero-1200x600.jpg" class="bc-hero-img wp-image-321"/></figure>';

	$bc_test_attachment_urls['321:1200x600']    = 'https://example.test/hero-1200x600.jpg';
	$bc_test_attachment_srcsets['321:1200x600']  = 'https://example.test/hero-600x300.jpg 600w, https://example.test/hero-1200x600.jpg 1200w';
	$bc_test_attachment_sizes['321:1200x600']    = '(max-width: 1200px) 100vw, 1200px';

	$out = blocksy_child_perf_lcp_hero_preload_markup();

	assert_same( strpos( $out, 'href="https://example.test/hero-1200x600.jpg"' ) !== false, true );
	assert_same( strpos( $out, 'imagesrcset="https://example.test/hero-600x300.jpg 600w, https://example.test/hero-1200x600.jpg 1200w"' ) !== false, true );
	assert_same( strpos( $out, 'imagesizes="(max-width: 1200px) 100vw, 1200px"' ) !== false, true );
} );

bc_test( 'blocksy_child_perf_lcp_hero_preload_markup(): no hero match in the content -> \'\'', function () {
	blocksy_child_perf_reset_state();

	global $bc_test_option_page_on_front, $bc_test_post_field_content;
	$bc_test_option_page_on_front = 7;
	$bc_test_post_field_content   = '<p>no hero image here</p>';

	assert_same( blocksy_child_perf_lcp_hero_preload_markup(), '' );
} );

bc_test( 'blocksy_child_perf_lcp_hero_preload_markup(): no page_on_front configured -> \'\'', function () {
	blocksy_child_perf_reset_state();

	global $bc_test_option_page_on_front;
	$bc_test_option_page_on_front = 0;

	assert_same( blocksy_child_perf_lcp_hero_preload_markup(), '' );
} );

// -----------------------------------------------------------------------
// Test fixtures / WP stubs — declared last; PHP hoists function
// declarations, so these are available from the very first bc_test() call
// above despite appearing at the bottom of the file (same convention as
// tests/perf/test-critical-css.php).
// -----------------------------------------------------------------------

function is_singular( $type = '' ) {
	global $bc_test_is_singular_product;

	if ( 'product' === $type ) {
		return ! empty( $bc_test_is_singular_product );
	}

	return false;
}

function is_front_page() {
	global $bc_test_is_front_page;
	return ! empty( $bc_test_is_front_page );
}

function get_post_thumbnail_id( $post = 0 ) {
	global $bc_test_post_thumbnail_id;
	return (int) ( $bc_test_post_thumbnail_id ?? 0 );
}

/**
 * Keys the stub attachment maps by "<id>:<size>", where an array size
 * (a `[width, height]` pair, as derived by blocksy_child_perf_lcp_hero_from_content())
 * is flattened to "WxH" so it can be used as an array key.
 *
 * @param int          $id
 * @param string|array $size
 * @return string
 */
function bc_test_attachment_key( $id, $size ) {
	return $id . ':' . ( is_array( $size ) ? implode( 'x', $size ) : $size );
}

function wp_get_attachment_image_url( $id, $size ) {
	global $bc_test_attachment_urls;
	$key = bc_test_attachment_key( $id, $size );
	return $bc_test_attachment_urls[ $key ] ?? '';
}

function wp_get_attachment_image_srcset( $id, $size ) {
	global $bc_test_attachment_srcsets;
	$key = bc_test_attachment_key( $id, $size );
	return $bc_test_attachment_srcsets[ $key ] ?? '';
}

function wp_get_attachment_image_sizes( $id, $size ) {
	global $bc_test_attachment_sizes;
	$key = bc_test_attachment_key( $id, $size );
	return $bc_test_attachment_sizes[ $key ] ?? '';
}

function get_post_field( $field, $post ) {
	global $bc_test_post_field_content;
	return ( 'post_content' === $field ) ? (string) ( $bc_test_post_field_content ?? '' ) : '';
}

function get_option( $name ) {
	global $bc_test_option_page_on_front;

	if ( 'page_on_front' === $name ) {
		return (int) ( $bc_test_option_page_on_front ?? 0 );
	}

	return false;
}
