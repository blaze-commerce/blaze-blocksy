<?php
/**
 * Tests for the Task 8 perf modules:
 *   inc/perf/dequeue-assets.php (+ inc/perf/data/dequeue-handles.php),
 *   inc/perf/media-hygiene.php,
 *   inc/perf/content-visibility.php,
 *   inc/perf/minicart-hydrate.php (+ the inc/mini-cart-empty.php <template> wrap),
 *   inc/perf/lazy-rescan.php (+ assets/js/perf-lazy-rescan.js).
 *
 * No WP stub is declared here (Task 5 ruling) — every stub these modules
 * need lives in tests/perf/bootstrap.php, driven by $GLOBALS['bc_wp_stub'].
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/inc/perf/helpers.php';
require_once dirname( __DIR__, 2 ) . '/inc/perf/dequeue-assets.php';
require_once dirname( __DIR__, 2 ) . '/inc/perf/media-hygiene.php';
require_once dirname( __DIR__, 2 ) . '/inc/perf/content-visibility.php';
// The bc-perf-cv printer the module just registered (wp_head @2).
$GLOBALS['bc_t8_cv_printer'] = end( $GLOBALS['bc_test_hooks']['wp_head'][2] );
require_once dirname( __DIR__, 2 ) . '/inc/perf/minicart-hydrate.php';
// The bc-perf-minicart-hydrate printer the module just registered (wp_footer @99).
$GLOBALS['bc_t8_minicart_printer'] = end( $GLOBALS['bc_test_hooks']['wp_footer'][99] );
require_once dirname( __DIR__, 2 ) . '/inc/perf/lazy-rescan.php';
require_once dirname( __DIR__, 2 ) . '/inc/mini-cart-empty.php';

/**
 * Whether $callback is registered on $hook at exactly $priority.
 */
function bc_t8_hooked( string $hook, $callback, int $priority ): bool {
	return isset( $GLOBALS['bc_test_hooks'][ $hook ][ $priority ] )
		&& in_array( $callback, $GLOBALS['bc_test_hooks'][ $hook ][ $priority ], true );
}

/**
 * Temporarily hook a filter for the duration of $fn.
 */
function bc_t8_with_filter( string $hook, callable $cb, callable $fn ) {
	add_filter( $hook, $cb, 10 );
	try {
		return $fn();
	} finally {
		remove_filter( $hook, $cb, 10 );
	}
}

/**
 * Whether any error_log() message so far contains $needle.
 */
function bc_t8_logged( string $needle ): bool {
	foreach ( bc_test_error_log_messages() as $message ) {
		if ( false !== strpos( $message, $needle ) ) {
			return true;
		}
	}
	return false;
}

// -----------------------------------------------------------------------
// Registration shape — FIRST, before anything else mutates the hook table.
// -----------------------------------------------------------------------

bc_test( 'task 8: every module wires its hooks at the mandated priorities', function () {
	assert_same( bc_t8_hooked( 'wp_enqueue_scripts', 'blocksy_child_perf_dequeue_assets', 1000 ), true, 'dequeue @1000' );

	assert_same( bc_t8_hooked( 'image_downsize', 'blocksy_child_perf_image_downsize_fallback', 10 ), true, 'image_downsize fallback @10' );
	assert_same( bc_t8_hooked( 'wp_get_attachment_image_attributes', 'blocksy_child_perf_logo_attributes', 999 ), true, 'logo attributes @999' );
	assert_same( bc_t8_hooked( 'wp_get_attachment_image_attributes', 'blocksy_child_perf_archive_thumb_attributes', 20 ), true, 'archive thumb dims @20' );
	assert_same( bc_t8_hooked( 'render_block', 'blocksy_child_perf_belowfold_lazy_render_block', 20 ), true, 'below-fold lazy on render_block @20 (after lcp-image cover pass @10)' );
	assert_same( bc_t8_hooked( 'wp_content_img_tag', 'blocksy_child_perf_img_height_fix_filter', 20 ), true, 'stretched-height fix @20' );

	foreach ( [ 'the_content', 'embed_oembed_html', 'widget_text_content', 'render_block' ] as $hook ) {
		assert_same( bc_t8_hooked( $hook, 'blocksy_child_perf_youtube_nocookie', 30 ), true, "nocookie on {$hook} @30" );
	}

	assert_same( bc_t8_hooked( 'wp_enqueue_scripts', 'blocksy_child_perf_minicart_fragments_seed', 30 ), true, 'fragments seed @30' );
	assert_same( bc_t8_hooked( 'wp_enqueue_scripts', 'blocksy_child_perf_lazy_rescan_enqueue', 20 ), true, 'lazy-rescan enqueue @20' );

	assert_same( isset( $GLOBALS['bc_test_hooks']['wp_head'][2] ), true, 'content-visibility style printer at wp_head @2' );
	assert_same( in_array( 'bc-perf-minicart-hydrate', blocksy_child_perf_script_ids(), true ), true, 'minicart hydrate script id registered' );
} );

// -----------------------------------------------------------------------
// dequeue-assets
// -----------------------------------------------------------------------

bc_test( 'dequeue data file: default styles + scripts', function () {
	$data = blocksy_child_perf_data( 'dequeue-handles' );

	assert_same( $data['styles'], [ 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'wp-components', 'jquery-ui-styles', 'slick-css', 'slick-css-theme', 'algolia-satellite' ] );
	assert_same( $data['scripts'], [ 'jquery-ui-scripts' ] );
} );

bc_test( 'blocksy_child_perf_dequeue_is_protected(): ct-*, blocksy-child-*, jquery-core, jquery, woocommerce, wc-cart-fragments', function () {
	foreach ( [ 'ct-main-styles', 'ct-anything', 'blocksy-child-style', 'blocksy-child-base', 'jquery-core', 'jquery', 'woocommerce', 'wc-cart-fragments' ] as $h ) {
		assert_same( blocksy_child_perf_dequeue_is_protected( $h ), true, "{$h} protected" );
	}
	foreach ( [ 'wp-block-library', 'slick-css', 'jquery-ui-scripts', 'woocommerce-general', 'jquery-migrate-x' ] as $h ) {
		assert_same( blocksy_child_perf_dequeue_is_protected( $h ), false, "{$h} not protected" );
	}
} );

bc_test( 'blocksy_child_perf_dequeue_handles(): a filter asking for protected handles is refused + logged, others merged', function () {
	$cb = function ( $handles ) {
		$handles['styles'][]  = 'ct-main-styles';
		$handles['styles'][]  = 'blocksy-child-style';
		$handles['styles'][]  = 'some-plugin-css';
		$handles['scripts'][] = 'jquery';
		$handles['scripts'][] = 'jquery-core';
		$handles['scripts'][] = 'wc-cart-fragments';
		$handles['scripts'][] = 'woocommerce';
		$handles['scripts'][] = 'some-plugin-js';
		return $handles;
	};

	$handles = bc_t8_with_filter( 'blocksy_child_perf_dequeue_handles', $cb, 'blocksy_child_perf_dequeue_handles' );

	assert_same( in_array( 'some-plugin-css', $handles['styles'], true ), true, 'non-protected filter style merged' );
	assert_same( in_array( 'some-plugin-js', $handles['scripts'], true ), true, 'non-protected filter script merged' );
	assert_same( in_array( 'wp-block-library', $handles['styles'], true ), true, 'data default still present' );

	foreach ( [ 'ct-main-styles', 'blocksy-child-style' ] as $h ) {
		assert_same( in_array( $h, $handles['styles'], true ), false, "{$h} refused" );
	}
	foreach ( [ 'jquery', 'jquery-core', 'wc-cart-fragments', 'woocommerce' ] as $h ) {
		assert_same( in_array( $h, $handles['scripts'], true ), false, "{$h} refused" );
	}

	assert_same( bc_t8_logged( '[blocksy-child][perf] dequeue-assets: refused protected handle ct-main-styles' ), true, 'refusal logged' );
	assert_same( bc_t8_logged( '[blocksy-child][perf] dequeue-assets: refused protected handle jquery-core' ), true, 'refusal logged' );
} );

bc_test( 'blocksy_child_perf_dequeue_assets(): dequeues AND deregisters every handle, never a protected one', function () {
	$GLOBALS['bc_wp_stub']['asset_calls'] = [];

	$cb = function ( $handles ) {
		$handles['styles'][]  = 'ct-main-styles';
		$handles['scripts'][] = 'jquery';
		return $handles;
	};

	bc_t8_with_filter( 'blocksy_child_perf_dequeue_handles', $cb, 'blocksy_child_perf_dequeue_assets' );

	$calls = $GLOBALS['bc_wp_stub']['asset_calls'];

	assert_same( in_array( 'wp_dequeue_style:wp-block-library', $calls, true ), true );
	assert_same( in_array( 'wp_deregister_style:wp-block-library', $calls, true ), true );
	assert_same( in_array( 'wp_dequeue_style:algolia-satellite', $calls, true ), true );
	assert_same( in_array( 'wp_deregister_style:algolia-satellite', $calls, true ), true );
	assert_same( in_array( 'wp_dequeue_script:jquery-ui-scripts', $calls, true ), true );
	assert_same( in_array( 'wp_deregister_script:jquery-ui-scripts', $calls, true ), true );
	assert_same( count( $calls ), 2 * 9, 'exactly 8 styles + 1 script, twice each' );

	foreach ( $calls as $call ) {
		assert_same( false !== strpos( $call, ':ct-' ) || false !== strpos( $call, ':jquery' . "\0" ), false, "no protected handle touched: {$call}" );
	}
	assert_same( in_array( 'wp_dequeue_script:jquery', $calls, true ), false, 'jquery never dequeued' );
	assert_same( in_array( 'wp_dequeue_style:ct-main-styles', $calls, true ), false, 'ct-main-styles never dequeued' );
} );

// -----------------------------------------------------------------------
// media-hygiene (a): image_downsize fallback
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_image_size_fallback_pick(): smallest generated size covering w x h', function () {
	$sizes = [
		'woocommerce_gallery_thumbnail' => [ 'file' => 'a-100x100.jpg', 'width' => 100, 'height' => 100 ],
		'woocommerce_thumbnail'         => [ 'file' => 'a-300x300.jpg', 'width' => 300, 'height' => 300 ],
		'medium_large'                  => [ 'file' => 'a-768x1152.jpg', 'width' => 768, 'height' => 1152 ],
	];

	$pick = blocksy_child_perf_image_size_fallback_pick( $sizes, 150, 150 );
	assert_same( $pick['file'], 'a-300x300.jpg' );
	assert_same( $pick['key'], 'woocommerce_thumbnail' );

	assert_same( blocksy_child_perf_image_size_fallback_pick( $sizes, 2000, 0 ), null, 'nothing covers -> null' );
	assert_same( blocksy_child_perf_image_size_fallback_pick( $sizes, 500, 0 )['file'], 'a-768x1152.jpg', 'height 0 = unconstrained' );
} );

bc_test( 'blocksy_child_perf_image_downsize_fallback(): missing named size -> generated substitute, never the original', function () {
	$GLOBALS['bc_wp_stub']['attachment_metadata'][1278476] = [
		'width'  => 1335,
		'height' => 2002,
		'file'   => '2026/08/19481-24.jpg',
		'sizes'  => [
			'woocommerce_thumbnail'         => [ 'file' => '19481-24-300x300.jpg', 'width' => 300, 'height' => 300 ],
			'woocommerce_gallery_thumbnail' => [ 'file' => '19481-24-100x100.jpg', 'width' => 100, 'height' => 100 ],
		],
	];
	$GLOBALS['bc_wp_stub']['registered_image_subsizes'] = [ 'thumbnail' => [ 'width' => 150, 'height' => 150, 'crop' => true ] ];
	$GLOBALS['bc_wp_stub']['post_meta']['1278476:_wp_attached_file'] = '2026/08/19481-24.jpg';

	$out = blocksy_child_perf_image_downsize_fallback( false, 1278476, 'thumbnail' );

	assert_same( $out, [ 'https://example.test/wp-content/uploads/2026/08/19481-24-300x300.jpg', 300, 300, true ] );
} );

bc_test( 'blocksy_child_perf_image_downsize_fallback(): hands back to core in every case it is not sure about', function () {
	$GLOBALS['bc_wp_stub']['attachment_metadata'][5] = [
		'width'  => 1000,
		'height' => 1000,
		'sizes'  => [ 'thumbnail' => [ 'file' => 'b-150x150.jpg', 'width' => 150, 'height' => 150 ] ],
	];
	$GLOBALS['bc_wp_stub']['registered_image_subsizes'] = [
		'thumbnail' => [ 'width' => 150, 'height' => 150, 'crop' => true ],
		'huge'      => [ 'width' => 5000, 'height' => 5000, 'crop' => false ],
	];
	$GLOBALS['bc_wp_stub']['post_meta']['5:_wp_attached_file'] = 'b.jpg';

	assert_same( blocksy_child_perf_image_downsize_fallback( [ 'x' ], 5, 'medium' ), [ 'x' ], 'someone already answered' );
	assert_same( blocksy_child_perf_image_downsize_fallback( false, 5, 'full' ), false, 'explicit full' );
	assert_same( blocksy_child_perf_image_downsize_fallback( false, 5, [ 100, 100 ] ), false, 'array size -> core' );
	assert_same( blocksy_child_perf_image_downsize_fallback( false, 5, 'thumbnail' ), false, 'size exists -> core' );
	assert_same( blocksy_child_perf_image_downsize_fallback( false, 5, 'not-registered' ), false, 'unregistered size -> core' );
	assert_same( blocksy_child_perf_image_downsize_fallback( false, 5, 'huge' ), false, 'nothing covers -> core' );
	assert_same( blocksy_child_perf_image_downsize_fallback( false, 999, 'thumbnail' ), false, 'no metadata -> core' );
} );

// -----------------------------------------------------------------------
// media-hygiene (b): logo sizes + fetchpriority
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_logo_attributes(): Blocksy logo gets honest sizes and loses fetchpriority', function () {
	foreach ( [ 'default-logo', 'transparent-logo', 'sticky-logo', 'mobile-logo', 'offcanvas-logo' ] as $class ) {
		$attr = blocksy_child_perf_logo_attributes( [
			'class'         => $class . ' foo',
			'srcset'        => 'logo-600.png 600w, logo-2248.png 2248w',
			'sizes'         => '(max-width: 2248px) 100vw, 2248px',
			'fetchpriority' => 'high',
		] );

		assert_same( $attr['sizes'], '(max-width: 999px) 220px, 420px', "{$class}: sizes" );
		assert_same( isset( $attr['fetchpriority'] ), false, "{$class}: fetchpriority dropped" );
	}
} );

bc_test( 'blocksy_child_perf_logo_attributes(): sizes filterable; no srcset -> sizes untouched; non-logo untouched', function () {
	$cb = function () {
		return '300px';
	};
	$attr = bc_t8_with_filter( 'blocksy_child_perf_logo_sizes', $cb, function () {
		return blocksy_child_perf_logo_attributes( [ 'class' => 'default-logo', 'srcset' => 'a 1w' ] );
	} );
	assert_same( $attr['sizes'], '300px', 'filter respected' );

	$attr = blocksy_child_perf_logo_attributes( [ 'class' => 'default-logo', 'fetchpriority' => 'high' ] );
	assert_same( isset( $attr['sizes'] ), false, 'no srcset -> no sizes written' );
	assert_same( isset( $attr['fetchpriority'] ), false, 'fetchpriority still dropped' );

	$content = [ 'class' => 'wp-image-4 not-a-default-logo-x', 'srcset' => 'a 1w', 'fetchpriority' => 'high' ];
	assert_same( blocksy_child_perf_logo_attributes( $content ), $content, 'content image untouched (whole-token class match)' );
} );

// -----------------------------------------------------------------------
// media-hygiene (c): archive thumbnail dims
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_archive_thumb_attributes(): shop/archive woocommerce_thumbnail missing dims -> wc_get_image_size', function () {
	$GLOBALS['bc_wp_stub']['is_shop']                                = true;
	$GLOBALS['bc_wp_stub']['wc_image_size']['woocommerce_thumbnail'] = [ 'width' => 300, 'height' => 400, 'crop' => 1 ];

	$attr = blocksy_child_perf_archive_thumb_attributes( [ 'loading' => 'lazy' ], (object) [ 'ID' => 3 ], 'woocommerce_thumbnail' );
	assert_same( $attr['width'], 300 );
	assert_same( $attr['height'], 400 );

	$GLOBALS['bc_wp_stub']['is_shop']             = false;
	$GLOBALS['bc_wp_stub']['is_product_taxonomy'] = true;
	$attr = blocksy_child_perf_archive_thumb_attributes( [ 'width' => '300' ], (object) [ 'ID' => 3 ], 'woocommerce_thumbnail' );
	assert_same( $attr['height'], 400, 'taxonomy archive too; height filled when only width present' );
} );

bc_test( 'blocksy_child_perf_archive_thumb_attributes(): uncropped (height 0) size -> attachment meta; untouched elsewhere', function () {
	$GLOBALS['bc_wp_stub']['is_shop']                                = true;
	$GLOBALS['bc_wp_stub']['wc_image_size']['woocommerce_thumbnail'] = [ 'width' => 300, 'height' => '', 'crop' => 0 ];
	$GLOBALS['bc_wp_stub']['attachment_metadata'][8]                 = [ 'sizes' => [ 'woocommerce_thumbnail' => [ 'width' => 300, 'height' => 450 ] ] ];

	$attr = blocksy_child_perf_archive_thumb_attributes( [], (object) [ 'ID' => 8 ], 'woocommerce_thumbnail' );
	assert_same( [ $attr['width'], $attr['height'] ], [ 300, 450 ], 'meta fallback' );

	$attr = blocksy_child_perf_archive_thumb_attributes( [], (object) [ 'ID' => 9 ], 'woocommerce_thumbnail' );
	assert_same( $attr, [], 'nothing resolvable -> never guessed' );

	$full = [ 'width' => 10, 'height' => 20 ];
	assert_same( blocksy_child_perf_archive_thumb_attributes( $full, (object) [ 'ID' => 8 ], 'woocommerce_thumbnail' ), $full, 'already sized' );
	assert_same( blocksy_child_perf_archive_thumb_attributes( [], (object) [ 'ID' => 8 ], 'woocommerce_single' ), [], 'other size' );

	$GLOBALS['bc_wp_stub']['is_shop']             = false;
	$GLOBALS['bc_wp_stub']['is_product_taxonomy'] = false;
	assert_same( blocksy_child_perf_archive_thumb_attributes( [], (object) [ 'ID' => 8 ], 'woocommerce_thumbnail' ), [], 'not an archive' );
} );

// -----------------------------------------------------------------------
// media-hygiene (d): below-fold lazy
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_belowfold_lazy(): the first 3 <img> of the request stay eager (stamped only), the rest lazy + async', function () {
	blocksy_child_perf_reset_state();

	$out = blocksy_child_perf_belowfold_lazy( '<img src="1.jpg"><img src="2.jpg">' );
	assert_same( $out, '<img data-bc-perf-n="1" src="1.jpg"><img data-bc-perf-n="2" src="2.jpg">', 'imgs 1-2 eager, stamped' );

	$out = blocksy_child_perf_belowfold_lazy( '<img src="3.jpg"><img src="4.jpg" decoding="sync"><img src="5.jpg">' );
	assert_same(
		$out,
		'<img data-bc-perf-n="3" src="3.jpg"><img data-bc-perf-n="4" loading="lazy" src="4.jpg" decoding="sync"><img data-bc-perf-n="5" loading="lazy" decoding="async" src="5.jpg">',
		'img 3 eager; 4+ lazy; existing decoding kept'
	);
} );

bc_test( 'blocksy_child_perf_belowfold_lazy(): LCP / fetchpriority / skip classes / explicit loading are never lazied', function () {
	blocksy_child_perf_reset_state();
	blocksy_child_perf_belowfold_lazy( '<img src="1.jpg"><img src="2.jpg"><img src="3.jpg">' );

	$skip = [
		'<img src="a.jpg" class="wp-image-1 blaze-lcp-image">',
		'<img src="b.jpg" class="bc-perf-lcp">',
		'<img src="c.jpg" class="bc-perf-hero__poster">',
		'<img src="d.jpg" class="skip-lazy">',
		'<img src="e.jpg" class="kb-skip-lazy">',
		'<img fetchpriority="high" src="f.jpg">',
		'<img src="g.jpg" loading="eager">',
		'<img src="h.jpg" loading="lazy">',
	];
	$n = 3;
	foreach ( $skip as $img ) {
		$n++;
		$out = blocksy_child_perf_belowfold_lazy( $img );
		assert_same( $out, preg_replace( '#^<img#', '<img data-bc-perf-n="' . $n . '"', $img ), "only stamped: {$img}" );
	}

	assert_same( blocksy_child_perf_belowfold_lazy( '<img src="z.jpg" class="lcp-ish">' ), '<img data-bc-perf-n="12" loading="lazy" decoding="async" src="z.jpg" class="lcp-ish">', 'an ordinary later image is lazied' );
} );

bc_test( 'blocksy_child_perf_belowfold_lazy(): inner-block tags ALTERED by later filters before the parent pass are not re-counted (stamp survives)', function () {
	blocksy_child_perf_reset_state();

	// Inner core/image blocks, each through the render_block @20 callback.
	$inner = [];
	foreach ( [ 1, 2, 3 ] as $i ) {
		$inner[] = blocksy_child_perf_belowfold_lazy_render_block( '<figure class="wp-block-image"><img src="' . $i . '.jpg" class="wp-image-' . $i . '"></figure>', [ 'blockName' => 'core/image' ] );
	}

	// A later render_block / render_block_core/image callback (priority > 20)
	// rewrites each inner tag before the parent gallery sees it.
	$altered = array_map( function ( $html ) {
		return str_replace( 'class="wp-image-', 'data-extra="1" class="altered wp-image-', $html );
	}, $inner );

	$gallery_in = '<figure class="wp-block-gallery">' . implode( '', $altered ) . '</figure>';
	$gallery    = blocksy_child_perf_belowfold_lazy_render_block( $gallery_in, [ 'blockName' => 'core/gallery' ] );

	assert_same( $gallery, $gallery_in, 'parent pass leaves the three (altered) above-fold images alone' );
	assert_same( substr_count( $gallery, 'loading=' ), 0, 'none of images 1-3 lazied on the parent pass' );

	$out = blocksy_child_perf_belowfold_lazy( '<img src="4.jpg">' );
	assert_same( $out, '<img data-bc-perf-n="4" loading="lazy" decoding="async" src="4.jpg">', 'the next new image is position 4' );
} );

bc_test( 'blocksy_child_perf_belowfold_lazy(): identical repeated tags each count — the 4th identical copy is lazied', function () {
	blocksy_child_perf_reset_state();

	$icon = '<img src="icon.svg" alt="">';
	$out  = blocksy_child_perf_belowfold_lazy( str_repeat( $icon, 4 ) );

	assert_same(
		$out,
		'<img data-bc-perf-n="1" src="icon.svg" alt="">'
		. '<img data-bc-perf-n="2" src="icon.svg" alt="">'
		. '<img data-bc-perf-n="3" src="icon.svg" alt="">'
		. '<img data-bc-perf-n="4" loading="lazy" decoding="async" src="icon.svg" alt="">'
	);
	blocksy_child_perf_reset_state();
} );

bc_test( 'blocksy_child_perf_belowfold_lazy(): eager count is filterable (blocksy_child_perf_eager_img_count)', function () {
	blocksy_child_perf_reset_state();
	$cb = function () {
		return 0;
	};
	$out = bc_t8_with_filter( 'blocksy_child_perf_eager_img_count', $cb, function () {
		return blocksy_child_perf_belowfold_lazy( '<img src="1.jpg">' );
	} );
	assert_same( $out, '<img data-bc-perf-n="1" loading="lazy" decoding="async" src="1.jpg">' );
	blocksy_child_perf_reset_state();
} );

bc_test( 'below-fold pass at render_block @20 respects the lcp-image cover pass @10 (fetchpriority="high" + bc-perf-lcp)', function () {
	blocksy_child_perf_reset_state();
	blocksy_child_perf_belowfold_lazy( '<img src="1.jpg"><img src="2.jpg"><img src="3.jpg">' );

	// What inc/perf/lcp-image.php's cover rewrite leaves behind.
	$cover = '<div class="wp-block-cover"><img fetchpriority="high" src="hero.jpg" class="wp-block-cover__image-background"/></div>';
	$out   = blocksy_child_perf_belowfold_lazy_render_block( $cover, [ 'blockName' => 'core/cover' ] );
	assert_same( false !== strpos( $out, 'loading=' ), false, 'LCP cover image never lazied' );
	assert_same( false !== strpos( $out, 'fetchpriority="high" src="hero.jpg"' ), true );
	blocksy_child_perf_reset_state();
} );

// -----------------------------------------------------------------------
// media-hygiene (e): stretched height
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_img_height_fix_filter(): width + wp-image-<id> + no height -> height from aspect ratio', function () {
	$GLOBALS['bc_wp_stub']['attachment_metadata'][639922] = [ 'width' => 1000, 'height' => 500 ];

	$img = '<img src="badge.png" width="120" class="wp-image-639922" loading="lazy" sizes="auto, 120px">';
	$out = blocksy_child_perf_img_height_fix_filter( $img, 'the_content', 0 );
	assert_same( $out, '<img height="60" src="badge.png" width="120" class="wp-image-639922" loading="lazy" sizes="auto, 120px">' );
} );

bc_test( 'blocksy_child_perf_img_height_fix(): a hard-cropped subsize in src uses THAT ratio, not the original one', function () {
	$meta = [
		'width'  => 1200,
		'height' => 800,
		'sizes'  => [ 'thumbnail' => [ 'file' => 'photo-300x300.jpg', 'width' => 300, 'height' => 300 ] ],
	];

	assert_same(
		blocksy_child_perf_img_height_fix( '<img src="https://example.test/photo-300x300.jpg?v=2" width="150" class="wp-image-3">', $meta ),
		'<img height="150" src="https://example.test/photo-300x300.jpg?v=2" width="150" class="wp-image-3">',
		'square crop -> 150, not 100'
	);

	assert_same(
		blocksy_child_perf_img_height_fix( '<img src="https://example.test/photo-640x480.jpg" width="150" class="wp-image-3">', $meta ),
		'<img height="100" src="https://example.test/photo-640x480.jpg" width="150" class="wp-image-3">',
		'suffix not among the generated sizes -> original ratio'
	);
} );

bc_test( 'blocksy_child_perf_img_height_fix_filter(): left alone when height present, no width, non-integer width, no wp-image class, or no meta', function () {
	$GLOBALS['bc_wp_stub']['attachment_metadata'][7] = [ 'width' => 400, 'height' => 300 ];

	foreach ( [
		'<img src="a.png" width="120" height="10" class="wp-image-7">',
		'<img src="a.png" class="wp-image-7">',
		'<img src="a.png" width="100%" class="wp-image-7">',
		'<img src="a.png" width="12.5" class="wp-image-7">',
		'<img src="a.png" width="120" class="foo">',
		'<img src="a.png" width="120" class="wp-image-8">',
	] as $img ) {
		assert_same( blocksy_child_perf_img_height_fix_filter( $img, 'the_content', 0 ), $img, "untouched: {$img}" );
	}

	assert_same(
		blocksy_child_perf_img_height_fix( '<img width="200" class="wp-image-7">', [ 'width' => 400, 'height' => 300 ] ),
		'<img height="150" width="200" class="wp-image-7">',
		'pure helper'
	);
	assert_same(
		blocksy_child_perf_img_height_fix( '<img width=200 class="wp-image-7">', [ 'width' => 400, 'height' => 300 ] ),
		'<img height="150" width=200 class="wp-image-7">',
		'unquoted integer width'
	);
} );

// -----------------------------------------------------------------------
// media-hygiene (f): youtube-nocookie
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_youtube_nocookie(): rewrites /embed/ URLs only', function () {
	$in  = '<iframe src="https://www.youtube.com/embed/abc?autoplay=1"></iframe><iframe src="//youtube.com/embed/def"></iframe>';
	$out = blocksy_child_perf_youtube_nocookie( $in );
	assert_same( $out, '<iframe src="https://www.youtube-nocookie.com/embed/abc?autoplay=1"></iframe><iframe src="//www.youtube-nocookie.com/embed/def"></iframe>' );

	$link = '<a href="https://www.youtube.com/watch?v=abc">watch</a>';
	assert_same( blocksy_child_perf_youtube_nocookie( $link ), $link, 'watch links untouched' );
	assert_same( blocksy_child_perf_youtube_nocookie( [ 'x' ] ), [ 'x' ], 'non-string passthrough' );
	assert_same( blocksy_child_perf_youtube_nocookie( '<iframe src="https://www.youtube-nocookie.com/embed/a"></iframe>' ), '<iframe src="https://www.youtube-nocookie.com/embed/a"></iframe>', 'idempotent' );
} );

// -----------------------------------------------------------------------
// content-visibility
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_cv_css(): default map -> exact CSS', function () {
	assert_same(
		blocksy_child_perf_cv_css( blocksy_child_perf_cv_map() ),
		'footer.ct-footer{content-visibility:auto;contain-intrinsic-size:auto 400px}'
		. '.related.products{content-visibility:auto;contain-intrinsic-size:auto 600px}'
		. '.ct-panel.ct-offcanvas-module:not(.active){content-visibility:auto;contain-intrinsic-size:auto 823px}'
	);
} );

bc_test( 'blocksy_child_perf_cv_css(): bad entries dropped + logged', function () {
	$css = blocksy_child_perf_cv_css( [ '.ok' => 100, '' => 50, '.zero' => 0, '.x{}' => 10, '</style>' => 10 ] );
	assert_same( $css, '.ok{content-visibility:auto;contain-intrinsic-size:auto 100px}' );
	assert_same( bc_t8_logged( '[blocksy-child][perf] content-visibility: dropped' ), true );
} );

bc_test( 'content-visibility: bc-perf-cv printed at wp_head, filter applied at PRINT time, empty map prints nothing', function () {
	$printer = $GLOBALS['bc_t8_cv_printer'];

	$cb = function () {
		return [ '.late-filter' => 300 ];
	};
	add_filter( 'blocksy_child_perf_cv_map', $cb );
	ob_start();
	$printer();
	$out = ob_get_clean();
	remove_filter( 'blocksy_child_perf_cv_map', $cb );

	assert_same( $out, '<style id="bc-perf-cv" data-no-optimize="1" data-no-minify="1">.late-filter{content-visibility:auto;contain-intrinsic-size:auto 300px}</style>' );

	$empty = function () {
		return [];
	};
	add_filter( 'blocksy_child_perf_cv_map', $empty );
	ob_start();
	$printer();
	$out = ob_get_clean();
	remove_filter( 'blocksy_child_perf_cv_map', $empty );
	assert_same( $out, '', 'empty map -> no tag' );
} );

// -----------------------------------------------------------------------
// minicart-hydrate
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_minicart_hydrate_js(): clones the template on hover / scroll / click / idle 4s', function () {
	$js = blocksy_child_perf_minicart_hydrate_js( blocksy_child_perf_minicart_triggers() );

	assert_same( false !== strpos( $js, 'template.bc-perf-minicart-template' ), true, 'targets the template' );
	assert_same( false !== strpos( $js, 'cloneNode(true)' ), true, 'clones content' );
	assert_same( false !== strpos( $js, json_encode( '.ct-cart-item, [data-toggle-panel="#woo-cart-panel"], .ct-cart-trigger, .cart-customlocation a' ) ), true, 'default triggers, JSON-encoded' );
	assert_same( false !== strpos( $js, "'mouseover'" ), true, 'hover' );
	assert_same( false !== strpos( $js, "'scroll'" ) && false !== strpos( $js, "'click'" ), true, 'first scroll / click' );
	assert_same( false !== strpos( $js, 'requestIdleCallback' ) && false !== strpos( $js, 'timeout:4000' ), true, 'idle 4s' );
	assert_same( false !== strpos( $js, "removeEventListener('mouseover',onTrigger" ), true, 'hover listener removed once hydrated' );
	assert_same( false !== strpos( $js, "removeEventListener('focusin',onTrigger" ), true, 'focus listener removed once hydrated' );
	assert_same( false !== strpos( $js, "ctEvents.trigger('blocksy:frontend:init')" ), true, 'Blocksy re-mount dispatched through window.ctEvents' );
} );

bc_test( 'bc-perf-minicart-hydrate: triggers filter applied at PRINT time', function () {
	$printer = $GLOBALS['bc_t8_minicart_printer'];

	$f = function () {
		return '.my-cart';
	};
	add_filter( 'blocksy_child_perf_minicart_triggers', $f );
	ob_start();
	$printer();
	$out = ob_get_clean();
	remove_filter( 'blocksy_child_perf_minicart_triggers', $f );

	assert_same( 0 === strpos( $out, '<script id="bc-perf-minicart-hydrate" data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false">' ), true, 'printed via the helper' );
	assert_same( false !== strpos( $out, '".my-cart"' ), true, 'late filter seen' );
} );

bc_test( 'blocksy_child_perf_minicart_fragments_seed_js(): the three AW guards + a widget-mirroring (not aborting) seed', function () {
	$js = blocksy_child_perf_minicart_fragments_seed_js( 'wc_fragments_abc', 'wc_cart_hash_abc' );

	assert_same( false !== strpos( $js, 'document.cookie' ), true, 'layer 1: WC cookies' );
	assert_same( false !== strpos( $js, 'woocommerce_(items_in_cart|cart_hash)=' ), true, 'layer 1: both cookie names' );
	assert_same( false !== strpos( $js, '!sessionStorage.getItem("wc_fragments_abc")' ), true, 'layer 2: fragments key' );
	assert_same( false !== strpos( $js, '!sessionStorage.getItem("wc_cart_hash_abc")' ), true, 'layer 2: session hash' );
	assert_same( false !== strpos( $js, '!localStorage.getItem("wc_cart_hash_abc")' ), true, 'layer 3: localStorage hash' );

	// The widget lookup is a BRANCH for the seeded value, never an abort.
	assert_same( false !== strpos( $js, "querySelector('div.widget_shopping_cart_content')" ), true, 'widget lookup' );
	assert_same( false !== strpos( $js, '.outerHTML' ), true, 'rendered widget outerHTML mirrored into the fragment' );
	assert_same( false !== strpos( $js, "!document.querySelector('div.widget_shopping_cart_content')" ), false, 'no longer aborts when the widget exists' );
	assert_same( false !== strpos( $js, 'f["div.widget_shopping_cart_content"]=w.outerHTML' ), true, 'fragment key is the one cart-fragments.js requires' );
	assert_same( false !== strpos( $js, 'if(w){' ) && false !== strpos( $js, '}else{v=' ), true, 'widget present -> mirror, absent -> no-op div' );
	assert_same( false !== strpos( $js, '<div class=\\\\\\"widget_shopping_cart_content\\\\\\">' ), true, 'no-op fallback still present' );

	assert_same( false !== strpos( $js, 'sessionStorage.setItem("wc_fragments_abc",v)' ), true, 'seeds the fragments key' );
	assert_same( false !== strpos( $js, 'window.jQuery(bcSeed)' ), true, 'value computed in a jQuery ready callback registered before cart-fragments.js' );
	assert_same( substr_count( $js, 'bcSeed' ), 2, 'bcSeed only defined + registered as a ready callback' );
	assert_same( preg_match( '#(?<!jQuery\()bcSeed\(\)#', $js ), 0, 'no synchronous bcSeed() call anywhere' );
	assert_same( false !== strpos( $js, 'else{bcSeed' ), false, 'no no-jQuery fallback branch' );
	assert_same( false !== strpos( $js, '&&window.jQuery){' ), true, 'no jQuery -> no seed at all' );
	assert_same( false !== strpos( $js, "if(w.querySelector('[data-bc-perf-hydrated]'))return;" ), true, 'already-hydrated widget -> skip seeding' );
	assert_same( strpos( $js, '[data-bc-perf-hydrated]' ) < strpos( $js, '.outerHTML' ), true, 'hydrated check happens before outerHTML is read' );
	assert_same( strpos( $js, 'localStorage.getItem(' ) < strpos( $js, 'var bcSeed' ), true, 'guards evaluated before any seeding' );
	assert_same( 0 === strpos( $js, 'try{' ) && '}catch(e){}' === substr( $js, -11 ), true, 'storage access wrapped' );
} );

bc_test( 'blocksy_child_perf_minicart_fragments_seed(): bails without WooCommerce / wc-cart-fragments', function () {
	$GLOBALS['bc_wp_stub']['scripts_registered'] = [ 'wc-cart-fragments' ];
	$GLOBALS['bc_wp_stub']['inline_scripts']     = [];

	blocksy_child_perf_minicart_fragments_seed();

	assert_same( $GLOBALS['bc_wp_stub']['inline_scripts'], [], 'no WooCommerce class in this harness -> nothing added' );
} );

bc_test( 'blocksy_child_perf_minicart_fragments_seed(): with WooCommerce + wc-cart-fragments -> seed added BEFORE wc-cart-fragments', function () {
	// Must run after the "bails without WooCommerce" case above: the class
	// cannot be undeclared (see bc_wp_stub_declare_woocommerce()).
	bc_wp_stub_declare_woocommerce();

	$GLOBALS['bc_wp_stub']['inline_scripts']     = [];
	$GLOBALS['bc_wp_stub']['scripts_registered'] = [];
	blocksy_child_perf_minicart_fragments_seed();
	assert_same( $GLOBALS['bc_wp_stub']['inline_scripts'], [], 'wc-cart-fragments not registered -> nothing added' );

	$GLOBALS['bc_wp_stub']['scripts_registered'] = [ 'wc-cart-fragments' ];
	blocksy_child_perf_minicart_fragments_seed();

	$calls = $GLOBALS['bc_wp_stub']['inline_scripts'];
	assert_same( count( $calls ), 1, 'one inline script' );
	assert_same( $calls[0][0], 'wc-cart-fragments', 'attached to wc-cart-fragments' );
	assert_same( $calls[0][2], 'before', "position 'before'" );

	$suffix = md5( '1_https://example.test/blocksy' );
	assert_same( $calls[0][1], blocksy_child_perf_minicart_fragments_seed_js( 'wc_fragments_' . $suffix, 'wc_cart_hash_' . $suffix ), 'keys mirror WC_Frontend_Scripts' );
} );

bc_test( 'inc/mini-cart-empty.php: suggestions wrapped in <template> only when minicart-hydrate is enabled', function () {
	$GLOBALS['bc_wp_stub']['suggested_carousel_html'] = '<div class="bc-minicart-suggested-grid"><img src="p.jpg"></div>';

	blocksy_child_perf_reset_state();
	ob_start();
	bc_mini_cart_empty_state();
	$off = ob_get_clean();

	assert_same( false !== strpos( $off, '<div class="bc-minicart-suggested-grid"><img src="p.jpg"></div>' ), true, 'suggestions rendered' );
	assert_same( false !== strpos( $off, '<template' ), false, 'disabled -> no template' );

	$features = function () {
		return [ 'minicart-hydrate' ];
	};
	add_filter( 'blocksy_child_perf_features', $features );
	blocksy_child_perf_reset_state();
	ob_start();
	bc_mini_cart_empty_state();
	$on = ob_get_clean();
	remove_filter( 'blocksy_child_perf_features', $features );
	blocksy_child_perf_reset_state();

	assert_same( false !== strpos( $on, '<template class="bc-perf-minicart-template"><div class="bc-minicart-suggested-grid"><img src="p.jpg"></div></template>' ), true, 'enabled -> wrapped' );
	assert_same( false !== strpos( $on, 'Your cart is currently empty.' ), true, 'empty message stays outside the template' );
	assert_same( strpos( $on, 'Your cart is currently empty.' ) < strpos( $on, '<template' ), true );

	$GLOBALS['bc_wp_stub']['suggested_carousel_html'] = '';
	add_filter( 'blocksy_child_perf_features', $features );
	blocksy_child_perf_reset_state();
	ob_start();
	bc_mini_cart_empty_state();
	$none = ob_get_clean();
	remove_filter( 'blocksy_child_perf_features', $features );
	blocksy_child_perf_reset_state();
	assert_same( false !== strpos( $none, '<template' ), false, 'no suggestions -> no empty template' );
} );

// -----------------------------------------------------------------------
// lazy-rescan
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_lazy_rescan_enqueue(): shop / product taxonomy only, footer, filemtime version', function () {
	$GLOBALS['bc_wp_stub']['is_shop']             = false;
	$GLOBALS['bc_wp_stub']['is_product_taxonomy'] = false;
	$GLOBALS['bc_wp_stub']['enqueued_scripts']    = [];
	blocksy_child_perf_lazy_rescan_enqueue();
	assert_same( $GLOBALS['bc_wp_stub']['enqueued_scripts'], [], 'not on a plain page' );

	foreach ( [ 'is_shop', 'is_product_taxonomy' ] as $cond ) {
		$GLOBALS['bc_wp_stub']['is_shop']             = false;
		$GLOBALS['bc_wp_stub']['is_product_taxonomy'] = false;
		$GLOBALS['bc_wp_stub'][ $cond ]               = true;
		$GLOBALS['bc_wp_stub']['enqueued_scripts']    = [];

		blocksy_child_perf_lazy_rescan_enqueue();

		$s = $GLOBALS['bc_wp_stub']['enqueued_scripts']['bc-perf-lazy-rescan'] ?? null;
		assert_same( is_array( $s ), true, "enqueued on {$cond}" );
		assert_same( $s[0], BLOCKSY_CHILD_URL . 'assets/js/perf-lazy-rescan.js' );
		assert_same( $s[2], filemtime( BLOCKSY_CHILD_PATH . 'assets/js/perf-lazy-rescan.js' ), 'filemtime version' );
		assert_same( $s[3], true, 'in footer' );
	}
} );

bc_test( 'assets/js/perf-lazy-rescan.js: observer + Blocksy events + instance lookup, never LazyLoad.update()', function () {
	$js = (string) file_get_contents( BLOCKSY_CHILD_PATH . 'assets/js/perf-lazy-rescan.js' );

	assert_same( false !== strpos( $js, 'new MutationObserver' ), true );
	assert_same( false !== strpos( $js, 'observe(document.body' ), true );
	foreach ( [ 'ct:refresh', 'blocksy:ajax-filter:done', 'ct:product-card:refresh', 'blocksy:ajax:loaded', 'blocksy:frontend:init', 'blocksy-pagination:load-more:complete' ] as $ev ) {
		assert_same( false !== strpos( $js, "'{$ev}'" ), true, "listens for {$ev}" );
	}
	assert_same( false !== strpos( $js, 'LazyLoad::Initialized' ), true, 'captures the Perfmatters instance' );
	assert_same( false !== strpos( $js, 'window.ctEvents.on(ev, onBlocksyEvent)' ), true, 'subscribes on the Blocksy ctEvents bus' );
	assert_same( false !== strpos( $js, 'document.addEventListener(ev, onBlocksyEvent, true)' ), true, 'DOM listener kept as fallback' );
	assert_same( false !== strpos( $js, 'window.LazyLoad.update' ), false, 'never calls update() on the constructor' );
	assert_same( false !== strpos( $js, 'img.perfmatters-lazy[data-src]' ), true, 'source fallback sweep' );

	// No-instance fallback is viewport-limited, never a blanket upgrade.
	assert_same( false !== strpos( $js, 'getBoundingClientRect' ), true, 'fallback measures each image box' );
	assert_same( false !== strpos( $js, 'new IntersectionObserver' ), true, 'off-screen stuck images are watched, not upgraded' );
	assert_same( 1 === preg_match( '/var FALLBACK_MARGIN = (\d+);/', $js, $m ) && (int) $m[1] > 0, true, 'fallback margin declared' );
	assert_same( false !== strpos( $js, 'upgradeStuck(FALLBACK_MARGIN)' ), true, 'no-instance path upgrades within the margin only' );
	assert_same( false !== strpos( $js, 'if (!inViewport(imgs[i], margin)) continue;' ), true, 'upgradeStuck() always applies the viewport check' );
	assert_same( 0 === preg_match( '/upgradeStuck\(\s*(false|true)?\s*\)/', $js ), true, 'no boolean/argument-less upgradeStuck() mode remains' );
	assert_same( 0 === preg_match( '/querySelectorAll\([^)]*\)\s*\.forEach\(\s*upgrade/', $js ), true, 'no unconditional querySelectorAll(...).forEach(upgrade...) path' );
	assert_same( false !== strpos( $js, 'upgradeStuck(0)' ), true, 'instance path keeps the strict on-screen check' );
} );

bc_test( 'lazy-rescan: script tag id registered and present in the Perfmatters delay-JS exclusions', function () {
	require_once dirname( __DIR__, 2 ) . '/inc/perf/perfmatters-filters.php';

	blocksy_child_perf_reset_state();
	blocksy_child_perf_lazy_rescan_register();

	assert_same( in_array( 'bc-perf-lazy-rescan-js', blocksy_child_perf_script_ids(), true ), true, 'registered in the script-id registry' );

	$exclusions = blocksy_child_perf_pmf_delay_js_exclusions( [] );
	assert_same( in_array( 'bc-perf-lazy-rescan-js', $exclusions, true ), true, 'delay-JS exclusions filter output contains the tag id' );

	// Perfmatters matches each exclusion as a substring of the whole tag;
	// WordPress prints the enqueued handle 'bc-perf-lazy-rescan' as
	// id="bc-perf-lazy-rescan-js", which is exactly this entry.
	assert_same( in_array( 'bc-perf-lazy-rescan-js', apply_filters( 'perfmatters_delay_js_exclusions', [] ), true ), true, 'via the registered perfmatters_delay_js_exclusions filter too' );

	blocksy_child_perf_reset_state();
} );
