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
	$GLOBALS['bc_wp_stub']['is_singular'] = false;

	$attr = blocksy_child_perf_lcp_pdp_attributes( [ 'loading' => 'lazy' ], (object) [ 'ID' => 42 ], 'woocommerce_single' );

	assert_same( $attr, [ 'loading' => 'lazy' ], 'unchanged off a product singular' );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_attributes(): different attachment (not the featured image) -> attributes untouched', function () {
	$GLOBALS['bc_wp_stub']['is_singular']       = true;
	$GLOBALS['bc_wp_stub']['post_thumbnail_id'] = 42;

	$attr = blocksy_child_perf_lcp_pdp_attributes( [ 'loading' => 'lazy' ], (object) [ 'ID' => 999 ], 'woocommerce_single' );

	assert_same( $attr, [ 'loading' => 'lazy' ], 'a different attachment id is never touched' );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_attributes(): a DIFFERENT size, BEFORE the gallery has rendered -> left untouched (never guessed lazy)', function () {
	$GLOBALS['bc_wp_stub']['is_singular']       = true;
	$GLOBALS['bc_wp_stub']['post_thumbnail_id'] = 42;

	$attr = blocksy_child_perf_lcp_pdp_attributes( [ 'loading' => 'lazy' ], (object) [ 'ID' => 42 ], 'thumbnail' );

	assert_same( $attr, [ 'loading' => 'lazy' ], 'not forced lazy — the real gallery render has not happened yet this request' );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_attributes(): FIRST render at woocommerce_single gets the full LCP treatment', function () {
	$GLOBALS['bc_wp_stub']['is_singular']       = true;
	$GLOBALS['bc_wp_stub']['post_thumbnail_id'] = 42;

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
	$GLOBALS['bc_wp_stub']['is_singular']       = true;
	$GLOBALS['bc_wp_stub']['post_thumbnail_id'] = 42;

	$attr = blocksy_child_perf_lcp_pdp_attributes( [ 'loading' => 'lazy' ], (object) [ 'ID' => 42 ], 'woocommerce_single' );

	assert_same( $attr, [ 'loading' => 'lazy' ], 'a duplicate render at the gallery size is not re-promoted (or demoted)' );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_attributes(): a DIFFERENT size, AFTER the gallery has rendered -> forced lazy, no fetchpriority', function () {
	$GLOBALS['bc_wp_stub']['is_singular']       = true;
	$GLOBALS['bc_wp_stub']['post_thumbnail_id'] = 42;

	// e.g. the floating add-to-cart bar's small copy of the same featured image.
	$attr = blocksy_child_perf_lcp_pdp_attributes( [ 'fetchpriority' => 'high' ], (object) [ 'ID' => 42 ], [ 100, 100 ] );

	assert_same( $attr['loading'], 'lazy' );
	assert_same( isset( $attr['fetchpriority'] ), false, 'fetchpriority stripped from the later, smaller copy' );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_lcp_pdp_lazyload_false() — Blocksy's own lazyload flag.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lcp_pdp_lazyload_false(): sets lazyload=false on a product singular', function () {
	$GLOBALS['bc_wp_stub']['is_singular'] = true;

	assert_same( blocksy_child_perf_lcp_pdp_lazyload_false( [ 'lazyload' => 'yes' ] ), [ 'lazyload' => false ] );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_lazyload_false(): untouched off a product singular', function () {
	$GLOBALS['bc_wp_stub']['is_singular'] = false;

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

	$GLOBALS['bc_wp_stub']['is_singular']                                       = true;
	$GLOBALS['bc_wp_stub']['post_thumbnail_id']                                 = 42;
	$GLOBALS['bc_wp_stub']['attachment_image_url']['42:woocommerce_single']     = 'https://example.test/pdp.jpg';
	$GLOBALS['bc_wp_stub']['attachment_image_srcset']['42:woocommerce_single']  = '';

	$pdp_markup = blocksy_child_perf_lcp_pdp_preload_markup();
	assert_same( strpos( $pdp_markup, 'pdp.jpg' ) !== false, true, 'PDP preload takes the slot' );

	$GLOBALS['bc_wp_stub']['is_front_page']              = true;
	$GLOBALS['bc_wp_stub']['options']['page_on_front']   = 7;
	$GLOBALS['bc_wp_stub']['post_field']['post_content'] = '<img src="https://example.test/hero.jpg" class="bc-hero-img wp-image-99">';

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

bc_test( 'blocksy_child_perf_lcp_cover_block_rewrite(): a fetchpriority attribute on a DIFFERENT element in the block does not block the rewrite of the first <img>', function () {
	blocksy_child_perf_reset_state();

	// The video poster carries its own unrelated fetchpriority attribute — a
	// whole-block substring check would wrongly treat the block as already
	// handled and skip the <img> entirely.
	$html = '<div class="wp-block-cover"><video poster="e.jpg" fetchpriority="low"></video><img src="e.jpg" loading="lazy" class="wp-image-5"/></div>';
	$out  = blocksy_child_perf_lcp_cover_block_rewrite( $html );

	assert_same( strpos( $out, '<img fetchpriority="high" src="e.jpg" class="wp-image-5"/>' ) !== false, true, 'the <img> itself is still rewritten' );
	assert_same( strpos( $out, 'loading="lazy" class="wp-image-5"' ) !== false, false, 'loading=lazy stripped from the <img>' );
	assert_same( strpos( $out, '<video poster="e.jpg" fetchpriority="low">' ) !== false, true, 'the unrelated <video> element is left completely untouched' );
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

bc_test( 'blocksy_child_perf_lcp_hero_content_attributes(): idempotent — a hero <img> that already carries fetchpriority is not given a duplicate attribute', function () {
	$html = '<img src="b.jpg" class="bc-hero-img wp-image-2" fetchpriority="high" loading="lazy"/>';

	$out = blocksy_child_perf_lcp_hero_content_attributes( $html, 'bc-hero-img' );

	assert_same( substr_count( $out, 'fetchpriority="high"' ), 1, 'not duplicated' );
	assert_same( strpos( $out, 'loading="lazy"' ) !== false, false, 'loading=lazy still stripped' );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_lcp_pdp_preload_markup() / blocksy_child_perf_lcp_hero_preload_markup()
// — the full assemblers, using the WP stubs declared below.
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lcp_pdp_preload_markup(): builds from get_post_thumbnail_id() + woocommerce_single, same sizes string as the element', function () {
	blocksy_child_perf_reset_state();

	$GLOBALS['bc_wp_stub']['is_singular']                                      = true;
	$GLOBALS['bc_wp_stub']['post_thumbnail_id']                                = 77;
	$GLOBALS['bc_wp_stub']['attachment_image_url']['77:woocommerce_single']    = 'https://example.test/gallery.jpg';
	$GLOBALS['bc_wp_stub']['attachment_image_srcset']['77:woocommerce_single'] = 'https://example.test/gallery-600.jpg 600w';

	$out = blocksy_child_perf_lcp_pdp_preload_markup();

	assert_same( strpos( $out, 'href="https://example.test/gallery.jpg"' ) !== false, true );
	assert_same( strpos( $out, 'imagesrcset="https://example.test/gallery-600.jpg 600w"' ) !== false, true );
	assert_same( strpos( $out, 'imagesizes="' . blocksy_child_perf_lcp_sizes() . '"' ) !== false, true, 'preload imagesizes == the element sizes helper' );
} );

bc_test( 'blocksy_child_perf_lcp_pdp_preload_markup(): no featured image -> \'\'', function () {
	blocksy_child_perf_reset_state();

	$GLOBALS['bc_wp_stub']['is_singular']       = true;
	$GLOBALS['bc_wp_stub']['post_thumbnail_id'] = 0;

	assert_same( blocksy_child_perf_lcp_pdp_preload_markup(), '' );
} );

bc_test( 'blocksy_child_perf_lcp_hero_preload_markup(): builds from the front page\'s post_content', function () {
	blocksy_child_perf_reset_state();

	$GLOBALS['bc_wp_stub']['options']['page_on_front']   = 7;
	$GLOBALS['bc_wp_stub']['post_field']['post_content'] = '<figure><img src="https://example.test/hero-1200x600.jpg" class="bc-hero-img wp-image-321"/></figure>';

	$GLOBALS['bc_wp_stub']['attachment_image_url']['321:1200x600']    = 'https://example.test/hero-1200x600.jpg';
	$GLOBALS['bc_wp_stub']['attachment_image_srcset']['321:1200x600'] = 'https://example.test/hero-600x300.jpg 600w, https://example.test/hero-1200x600.jpg 1200w';
	$GLOBALS['bc_wp_stub']['attachment_image_sizes']['321:1200x600']  = '(max-width: 1200px) 100vw, 1200px';

	$out = blocksy_child_perf_lcp_hero_preload_markup();

	assert_same( strpos( $out, 'href="https://example.test/hero-1200x600.jpg"' ) !== false, true );
	assert_same( strpos( $out, 'imagesrcset="https://example.test/hero-600x300.jpg 600w, https://example.test/hero-1200x600.jpg 1200w"' ) !== false, true );
	assert_same( strpos( $out, 'imagesizes="(max-width: 1200px) 100vw, 1200px"' ) !== false, true );
} );

bc_test( 'blocksy_child_perf_lcp_hero_preload_markup(): no hero match in the content -> \'\'', function () {
	blocksy_child_perf_reset_state();

	$GLOBALS['bc_wp_stub']['options']['page_on_front']   = 7;
	$GLOBALS['bc_wp_stub']['post_field']['post_content'] = '<p>no hero image here</p>';

	assert_same( blocksy_child_perf_lcp_hero_preload_markup(), '' );
} );

bc_test( 'blocksy_child_perf_lcp_hero_preload_markup(): no page_on_front configured -> \'\'', function () {
	blocksy_child_perf_reset_state();

	$GLOBALS['bc_wp_stub']['options']['page_on_front'] = 0;

	assert_same( blocksy_child_perf_lcp_hero_preload_markup(), '' );
} );

// is_singular(), is_front_page(), get_post_thumbnail_id(),
// wp_get_attachment_image_url/srcset/sizes(), get_post_field(), get_option()
// are declared once, for every test-*.php file that needs them, in
// tests/perf/bootstrap.php — driven by $GLOBALS['bc_wp_stub'], reset before
// each test file by run.php.
