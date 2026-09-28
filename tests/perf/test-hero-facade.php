<?php
/**
 * Tests for inc/perf/hero-facade.php and assets/js/perf-hero-facade.js.
 *
 * WP stubs used here (is_front_page, attachment_url_to_postid,
 * wp_get_attachment_metadata, wp_get_attachment_image_url, wp_get_upload_dir,
 * add_image_size) all live in tests/perf/bootstrap.php — never declared in
 * this file (controller ruling, Task 5).
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/inc/perf/helpers.php';

// Hooks are not reset between test files — remember how many wp_footer@99
// callbacks existed before this module registered its own.
$GLOBALS['bc_hero_footer_index'] = count( $GLOBALS['bc_test_hooks']['wp_footer'][99] ?? [] );

require_once dirname( __DIR__, 2 ) . '/inc/perf/hero-facade.php';

// -----------------------------------------------------------------------
// Fixtures: a temp poster_dir (YouTube repo posters) and a temp uploads
// basedir (media-library video posters). Removed at the end of this file.
// -----------------------------------------------------------------------

$GLOBALS['bc_hero_tmp'] = rtrim( sys_get_temp_dir(), '/\\' ) . '/bc_hero_' . uniqid( '', true ) . '/';
mkdir( $GLOBALS['bc_hero_tmp'] . 'posters', 0777, true );
mkdir( $GLOBALS['bc_hero_tmp'] . 'uploads/2026/05', 0777, true );

/**
 * Test config: defaults, pointed at the temp poster dir.
 *
 * @param array $over Overrides.
 * @return array
 */
function bc_hero_test_config( array $over = [] ): array {
	return array_merge(
		blocksy_child_perf_hero_config(),
		[
			'poster_dir' => $GLOBALS['bc_hero_tmp'] . 'posters/',
			'poster_url' => 'https://example.test/theme/custom/images/',
		],
		$over
	);
}

/**
 * Write a poster fixture of $bytes bytes.
 *
 * @param string $name  File name under posters/, after the `yt-poster-` prefix.
 * @param int    $bytes Size.
 * @return void
 */
function bc_hero_test_poster( string $name, int $bytes ): void {
	file_put_contents( $GLOBALS['bc_hero_tmp'] . 'posters/yt-poster-' . $name, str_repeat( 'A', $bytes ) );
}

/**
 * The Houston-shaped hero markup.
 *
 * @param string $id YouTube id.
 * @return string
 */
function bc_hero_test_yt_html( string $id ): string {
	return '<p>before</p><div class="wp-block-html bc-hero-video" style="position:relative;padding-top:56.25%">'
		. '<iframe src="https://www.youtube-nocookie.com/embed/' . $id . '?autoplay=1&#038;mute=1&#038;loop=1" title="Our mattresses" allow="autoplay"></iframe>'
		. '</div><p>after</p>';
}

/**
 * Set up a media-library poster attachment (id 77) with sizes on disk.
 *
 * @param array $sizes    Size name => bytes written for that size.
 * @param int   $id       Attachment id.
 * @return string The poster URL the <video> carries.
 */
function bc_hero_test_attachment( array $sizes, int $id = 77 ): string {
	$url = 'https://example.test/wp-content/uploads/2026/05/hero-poster.jpg';

	$GLOBALS['bc_wp_stub']['upload_dir']['basedir']              = $GLOBALS['bc_hero_tmp'] . 'uploads';
	$GLOBALS['bc_wp_stub']['attachment_url_to_postid'][ $url ]   = $id;
	$GLOBALS['bc_wp_stub']['attachment_image_url'][ $id . ':full' ] = $url;

	$meta = [ 'file' => '2026/05/hero-poster.jpg', 'mime_type' => 'image/jpeg', 'sizes' => [] ];

	foreach ( $sizes as $size => $bytes ) {
		$file = 'hero-poster-' . $size . '.jpg';
		file_put_contents( $GLOBALS['bc_hero_tmp'] . 'uploads/2026/05/' . $file, str_repeat( 'B', $bytes ) );
		$meta['sizes'][ $size ] = [ 'file' => $file, 'mime-type' => 'image/jpeg' ];
		$GLOBALS['bc_wp_stub']['attachment_image_url'][ $id . ':' . $size ] = 'https://example.test/wp-content/uploads/2026/05/' . $file;
	}

	$GLOBALS['bc_wp_stub']['attachment_metadata'][ $id ] = $meta;

	return $url;
}

/**
 * Austin-shaped hero video markup, inside the wrapper.
 *
 * @param string $poster Poster URL.
 * @return string
 */
function bc_hero_test_video_html( string $poster ): string {
	return '<div class="bc-hero-video"><video autoplay muted loop playsinline src="https://example.test/hero.mp4" poster="' . $poster . '"></video></div>';
}

// -----------------------------------------------------------------------
// Registration.
// -----------------------------------------------------------------------

bc_test( 'hero-facade: registers the_content@20, after_setup_theme, and the bc-perf-hero-facade footer script', function () {
	assert_same( in_array( 'blocksy_child_perf_hero_filter_content', $GLOBALS['bc_test_hooks']['the_content'][20] ?? [], true ), true, 'the_content @20' );
	assert_same( in_array( 'blocksy_child_perf_hero_image_size', $GLOBALS['bc_test_hooks']['after_setup_theme'][10] ?? [], true ), true, 'after_setup_theme' );
	assert_same( in_array( 'bc-perf-hero-facade', blocksy_child_perf_script_ids(), true ), true, 'script id registered for delay exclusion' );
	assert_same( isset( $GLOBALS['bc_test_hooks']['wp_footer'][99][ $GLOBALS['bc_hero_footer_index'] ] ), true, 'wp_footer @99 printer registered' );
} );

bc_test( 'hero-facade: after_setup_theme registers bc_hero_poster 640x360 hard-crop', function () {
	blocksy_child_perf_hero_image_size();
	assert_same( $GLOBALS['bc_wp_stub']['image_sizes']['bc_hero_poster'] ?? null, [ 640, 360, true ] );
} );

bc_test( 'blocksy_child_perf_hero_config(): defaults verbatim from the brief, filterable', function () {
	$c = blocksy_child_perf_hero_config();
	assert_same( $c['wrapper_class'], 'bc-hero-video' );
	assert_same( $c['poster_dir'], BLOCKSY_CHILD_PATH . 'custom/images/' );
	assert_same( $c['poster_url'], BLOCKSY_CHILD_URL . 'custom/images/' );
	assert_same( $c['poster_max_inline_bytes'], 24576 );
	assert_same( $c['delay_ms'], 5000 );
	assert_same( $c['hires_min_width'], 900 );
	assert_same( $c['front_page_only'], true );

	$cb = function ( $cfg ) {
		$cfg['delay_ms'] = 1234;
		return $cfg;
	};
	add_filter( 'blocksy_child_perf_hero_facade', $cb );
	assert_same( blocksy_child_perf_hero_config()['delay_ms'], 1234, 'filter applied' );
	remove_filter( 'blocksy_child_perf_hero_facade', $cb );
} );

// -----------------------------------------------------------------------
// YouTube rewrite.
// -----------------------------------------------------------------------

bc_test( 'YouTube: small poster is inlined as data:, iframe replaced, attrs stashed on the wrapper', function () {
	bc_hero_test_poster( 'aaaaaaaaaaa-800.avif', 100 );

	$out = blocksy_child_perf_hero_rewrite_content( bc_hero_test_yt_html( 'aaaaaaaaaaa' ), bc_hero_test_config() );

	assert_same( strpos( $out, '<iframe' ), false, 'iframe removed from the initial HTML' );
	assert_same( strpos( $out, 'src="data:image/avif;base64,' . base64_encode( str_repeat( 'A', 100 ) ) . '"' ) !== false, true, 'poster inlined as data:' );
	assert_same( strpos( $out, 'class="bc-perf-hero__poster"' ) !== false, true );
	assert_same( strpos( $out, 'fetchpriority="high"' ) !== false, true );
	assert_same( strpos( $out, 'decoding="sync"' ) !== false, true );
	assert_same( 1, preg_match( '#<img[^>]*\swidth="\d+"[^>]*\sheight="\d+"#', $out ), 'width + height on the poster' );
	assert_same( strpos( $out, 'data-bc-yt-src="https://www.youtube-nocookie.com/embed/aaaaaaaaaaa?autoplay=1&mute=1&loop=1"' ) !== false, true, 'decoded iframe src stashed' );
	assert_same( strpos( $out, 'data-bc-yt-title="Our mattresses"' ) !== false, true, 'title stashed' );
	assert_same( strpos( $out, 'alt="Our mattresses"' ) !== false, true, 'title reused as alt' );
	assert_same( strpos( $out, 'data-bc-hires' ), false, 'no hi-res asset -> no data-bc-hires' );
	assert_same( strpos( $out, '<p>before</p>' ) === 0 && substr( $out, -12 ) === '<p>after</p>', true, 'surrounding content preserved' );
	assert_same( strpos( $out, 'rel="preload"' ), false, 'never preloads the data: poster' );
} );

bc_test( 'YouTube: poster larger than poster_max_inline_bytes is referenced by URL', function () {
	bc_hero_test_poster( 'bbbbbbbbbbb-800.avif', 24577 );

	$out = blocksy_child_perf_hero_rewrite_content( bc_hero_test_yt_html( 'bbbbbbbbbbb' ), bc_hero_test_config() );

	assert_same( strpos( $out, 'data:' ), false, 'not inlined' );
	assert_same( 1, preg_match( '#<img[^>]*\ssrc="https://example\.test/theme/custom/images/yt-poster-bbbbbbbbbbb-800\.avif(\?ver=\d+)?"#', $out ), 'poster by URL' );
} );

bc_test( 'YouTube: a -1920 asset becomes data-bc-hires', function () {
	bc_hero_test_poster( 'ccccccccccc-800.avif', 100 );
	bc_hero_test_poster( 'ccccccccccc-1920.avif', 50000 );

	$out = blocksy_child_perf_hero_rewrite_content( bc_hero_test_yt_html( 'ccccccccccc' ), bc_hero_test_config() );

	assert_same( 1, preg_match( '#data-bc-hires="https://example\.test/theme/custom/images/yt-poster-ccccccccccc-1920\.avif(\?ver=\d+)?"#', $out ) );
} );

bc_test( 'YouTube: no poster asset -> markup left untouched', function () {
	$html = bc_hero_test_yt_html( 'zzzzzzzzzzz' );
	assert_same( blocksy_child_perf_hero_rewrite_content( $html, bc_hero_test_config() ), $html );
} );

bc_test( 'YouTube: iframe outside the wrapper is left untouched', function () {
	bc_hero_test_poster( 'ddddddddddd-800.avif', 100 );
	$html = '<div class="other"><iframe src="https://www.youtube.com/embed/ddddddddddd"></iframe></div>';
	assert_same( blocksy_child_perf_hero_rewrite_content( $html, bc_hero_test_config() ), $html );
} );

bc_test( 'YouTube: a nested <div> inside the wrapper does not cut the wrapper short', function () {
	bc_hero_test_poster( 'eeeeeeeeeee-800.avif', 100 );
	$html = '<div class="bc-hero-video"><div class="inner"><span>x</span></div><iframe src="https://www.youtube.com/embed/eeeeeeeeeee"></iframe></div><iframe src="https://www.youtube.com/embed/eeeeeeeeeee"></iframe>';

	$out = blocksy_child_perf_hero_rewrite_content( $html, bc_hero_test_config() );

	assert_same( substr_count( $out, '<iframe' ), 1, 'only the iframe inside the wrapper replaced' );
	assert_same( strpos( $out, '<div class="inner"><span>x</span></div>' ) !== false, true );
} );

bc_test( 'YouTube: idempotent — rewriting already-rewritten content changes nothing', function () {
	bc_hero_test_poster( 'fffffffffff-800.avif', 100 );
	$once = blocksy_child_perf_hero_rewrite_content( bc_hero_test_yt_html( 'fffffffffff' ), bc_hero_test_config() );
	assert_same( blocksy_child_perf_hero_rewrite_content( $once, bc_hero_test_config() ), $once );
} );

bc_test( 'blocksy_child_perf_hero_poster_src(): YouTube id -> src/hires, invalid id -> null', function () {
	bc_hero_test_poster( 'ggggggggggg-800.avif', 10 );
	$p = blocksy_child_perf_hero_poster_src( 'ggggggggggg', bc_hero_test_config() );
	assert_same( 0 === strpos( $p['src'], 'data:image/avif;base64,' ), true );
	assert_same( $p['hires'], '' );
	assert_same( blocksy_child_perf_hero_poster_src( '../../etc/pa', bc_hero_test_config() ), null, 'not an 11-char YouTube id' );
} );

// -----------------------------------------------------------------------
// <video autoplay> rewrite.
// -----------------------------------------------------------------------

bc_test( 'video: strips autoplay, preload=none, src -> data-bc-src, bc_hero_poster inlined, hi-res kept', function () {
	$poster = bc_hero_test_attachment( [ 'bc_hero_poster' => 200, 'medium_large' => 300 ] );

	$out = blocksy_child_perf_hero_rewrite_content( bc_hero_test_video_html( $poster ), bc_hero_test_config() );

	assert_same( 1, preg_match( '#<video\b[^>]*>#', $out, $m ) );
	$tag = $m[0];
	assert_same( preg_match( '#\sautoplay\b#', $tag ), 0, 'autoplay stripped' );
	assert_same( strpos( $tag, 'preload="none"' ) !== false, true );
	assert_same( preg_match( '#\ssrc=#', $tag ), 0, 'no src left' );
	assert_same( strpos( $tag, 'data-bc-src="https://example.test/hero.mp4"' ) !== false, true );
	assert_same( strpos( $tag, 'poster="data:image/jpeg;base64,' . base64_encode( str_repeat( 'B', 200 ) ) . '"' ) !== false, true, 'bc_hero_poster size inlined' );
	assert_same( strpos( $tag, 'data-bc-hires="' . $poster . '"' ) !== false, true, 'full-size URL kept as hi-res' );
	assert_same( strpos( $tag, 'muted' ) !== false && strpos( $tag, 'playsinline' ) !== false, true, 'other attributes preserved' );
} );

bc_test( 'video: no bc_hero_poster size -> falls back to medium_large', function () {
	$poster = bc_hero_test_attachment( [ 'medium_large' => 300 ], 78 );
	$out    = blocksy_child_perf_hero_rewrite_content( bc_hero_test_video_html( $poster ), bc_hero_test_config() );
	assert_same( strpos( $out, 'poster="data:image/jpeg;base64,' . base64_encode( str_repeat( 'B', 300 ) ) . '"' ) !== false, true );
} );

bc_test( 'video: poster over the inline limit -> referenced by the small-size URL', function () {
	$poster = bc_hero_test_attachment( [ 'bc_hero_poster' => 30000 ], 79 );
	$out    = blocksy_child_perf_hero_rewrite_content( bc_hero_test_video_html( $poster ), bc_hero_test_config() );
	assert_same( strpos( $out, 'poster="https://example.test/wp-content/uploads/2026/05/hero-poster-bc_hero_poster.jpg"' ) !== false, true );
	assert_same( strpos( $out, 'data:' ), false );
} );

bc_test( 'video: unresolvable poster attachment -> poster kept as-is, video still deferred', function () {
	$html = bc_hero_test_video_html( 'https://cdn.example.test/elsewhere.jpg' );
	$out  = blocksy_child_perf_hero_rewrite_content( $html, bc_hero_test_config() );
	assert_same( strpos( $out, 'poster="https://cdn.example.test/elsewhere.jpg"' ) !== false, true );
	assert_same( strpos( $out, 'data-bc-src=' ) !== false, true );
} );

bc_test( 'video: non-autoplay video and a video outside the wrapper are untouched', function () {
	$a = '<div class="bc-hero-video"><video muted src="https://example.test/a.mp4"></video></div>';
	$b = '<div class="other"><video autoplay src="https://example.test/b.mp4"></video></div>';
	assert_same( blocksy_child_perf_hero_rewrite_content( $a, bc_hero_test_config() ), $a );
	assert_same( blocksy_child_perf_hero_rewrite_content( $b, bc_hero_test_config() ), $b );
} );

// -----------------------------------------------------------------------
// the_content gate + footer script.
// -----------------------------------------------------------------------

bc_test( 'the_content filter: non-front-page untouched; front page rewritten; front_page_only=false rewrites anywhere', function () {
	bc_hero_test_poster( 'hhhhhhhhhhh-800.avif', 100 );
	$html = bc_hero_test_yt_html( 'hhhhhhhhhhh' );
	$cfg  = function ( $c ) {
		// Not bc_hero_test_config() — that calls the filter again (recursion).
		$c['poster_dir'] = $GLOBALS['bc_hero_tmp'] . 'posters/';
		$c['poster_url'] = 'https://example.test/theme/custom/images/';
		return $c;
	};
	add_filter( 'blocksy_child_perf_hero_facade', $cfg );

	$GLOBALS['bc_wp_stub']['is_front_page'] = false;
	assert_same( blocksy_child_perf_hero_filter_content( $html ), $html, 'not the front page' );

	$GLOBALS['bc_wp_stub']['is_front_page'] = true;
	assert_same( strpos( blocksy_child_perf_hero_filter_content( $html ), '<iframe' ), false, 'front page rewritten' );

	$GLOBALS['bc_wp_stub']['is_front_page'] = false;
	$anywhere = function ( $c ) {
		$c['front_page_only'] = false;
		return $c;
	};
	add_filter( 'blocksy_child_perf_hero_facade', $anywhere, 11 );
	assert_same( strpos( blocksy_child_perf_hero_filter_content( $html ), '<iframe' ), false, 'front_page_only=false' );

	remove_filter( 'blocksy_child_perf_hero_facade', $anywhere, 11 );
	remove_filter( 'blocksy_child_perf_hero_facade', $cfg );
	$GLOBALS['bc_wp_stub']['is_front_page'] = false;
} );

bc_test( 'footer script: nothing printed when no hero was rewritten this request', function () {
	unset( $GLOBALS['blocksy_child_perf_state']['hero_facade_rewritten'] );
	assert_same( blocksy_child_perf_hero_footer_js(), '' );

	$printer = $GLOBALS['bc_test_hooks']['wp_footer'][99][ $GLOBALS['bc_hero_footer_index'] ];
	ob_start();
	$printer();
	assert_same( ob_get_clean(), '', 'no empty <script> tag either' );
} );

bc_test( 'footer script: after a rewrite, prints window.bcPerfHeroFacade (filtered at print time) then the JS, with the mandated attributes', function () {
	$GLOBALS['blocksy_child_perf_state']['hero_facade_rewritten'] = true;

	$late = function ( $c ) {
		$c['delay_ms']        = 4321;
		$c['hires_min_width'] = 1000;
		return $c;
	};
	add_filter( 'blocksy_child_perf_hero_facade', $late );

	$printer = $GLOBALS['bc_test_hooks']['wp_footer'][99][ $GLOBALS['bc_hero_footer_index'] ];
	ob_start();
	$printer();
	$out = ob_get_clean();
	remove_filter( 'blocksy_child_perf_hero_facade', $late );
	unset( $GLOBALS['blocksy_child_perf_state']['hero_facade_rewritten'] );

	assert_same( 0 === strpos( $out, '<script id="bc-perf-hero-facade" data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false">' ), true, 'mandated attributes' );
	assert_same( strpos( $out, 'window.bcPerfHeroFacade = {"delay_ms":4321,"hires_min_width":1000};' ) !== false, true, 'config object, filter resolved at print time' );
	$js = file_get_contents( dirname( __DIR__, 2 ) . '/assets/js/perf-hero-facade.js' );
	assert_same( strpos( $out, 'window.bcPerfHeroFacade' ) < strpos( $out, trim( $js ) ), true, 'config printed before the JS body' );
} );

// -----------------------------------------------------------------------
// The JS source.
// -----------------------------------------------------------------------

bc_test( 'perf-hero-facade.js: exists, never fades the poster, PLAYING-gated YouTube reveal', function () {
	$file = dirname( __DIR__, 2 ) . '/assets/js/perf-hero-facade.js';
	assert_same( is_readable( $file ), true, 'JS file exists' );
	$js = file_get_contents( $file );

	assert_same( preg_match( '/poster[^;\n]{0,120}opacity|opacity[^;\n]{0,120}poster/i', $js ), 0, 'no rule fading the poster' );
	assert_same( strpos( $js, 'playerState' ) !== false, true, 'playerState' );
	assert_same( strpos( $js, '"event":"listening"' ) !== false, true, '"event":"listening"' );
	assert_same( strpos( $js, 'enablejsapi=1' ) !== false, true );
	assert_same( strpos( $js, 'prefers-reduced-motion: reduce' ) !== false, true );
	assert_same( strpos( $js, "rootMargin: '200px" ) !== false, true, 'IntersectionObserver 200px' );
	foreach ( [ 'pointerdown', 'touchstart', 'wheel', 'keydown', 'mousemove', 'scroll' ] as $ev ) {
		assert_same( strpos( $js, "'" . $ev . "'" ) !== false, true, 'arms ' . $ev );
	}
	assert_same( strpos( $js, 'bcPerfHeroFacade' ) !== false, true, 'reads its config object' );
} );

// -----------------------------------------------------------------------
// Cleanup.
// -----------------------------------------------------------------------

( function ( $dir ) {
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $it as $f ) {
		$f->isDir() ? rmdir( $f->getPathname() ) : unlink( $f->getPathname() );
	}
	rmdir( $dir );
} )( $GLOBALS['bc_hero_tmp'] );
