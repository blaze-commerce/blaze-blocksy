<?php
/**
 * Tests for inc/perf/fonts-critical-path.php, inc/perf/async-styles.php and
 * inc/perf/data/async-style-handles.php.
 *
 * No WP stub is declared here (Task 5 ruling) — the fonts module resolves
 * URLs through the existing wp_get_upload_dir() stub
 * ($GLOBALS['bc_wp_stub']['upload_dir']) and finds the Perfmatters cache
 * through the injectable `blocksy_child_perf_fonts_cache_dir` filter, so
 * neither WP_CONTENT_DIR nor content_url()/home_url() is needed.
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/inc/perf/helpers.php';
require_once dirname( __DIR__, 2 ) . '/inc/perf/fonts-critical-path.php';
require_once dirname( __DIR__, 2 ) . '/inc/perf/async-styles.php';

// -----------------------------------------------------------------------
// Fixtures.
// -----------------------------------------------------------------------

/**
 * A fresh temp directory tree, removed at shutdown.
 *
 * @return string Absolute path, no trailing slash.
 */
function bc_fonts_test_tmpdir(): string {
	$dir = sys_get_temp_dir() . '/bc-perf-fonts-' . bin2hex( random_bytes( 6 ) );
	mkdir( $dir, 0777, true );

	register_shutdown_function( function () use ( $dir ) {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ( $it as $f ) {
			$f->isDir() ? @rmdir( $f->getPathname() ) : @unlink( $f->getPathname() );
		}
		@rmdir( $dir );
	} );

	return $dir;
}

/**
 * Point the uploads stub at a temp dir and return [ basedir, baseurl ].
 *
 * @return string[]
 */
function bc_fonts_test_uploads(): array {
	$base = bc_fonts_test_tmpdir();
	$GLOBALS['bc_wp_stub']['upload_dir'] = [
		'basedir' => $base,
		'baseurl' => 'https://example.test/wp-content/uploads',
	];
	return [ $base, 'https://example.test/wp-content/uploads' ];
}

/**
 * Temporarily hook a filter for the duration of $fn.
 */
function bc_fonts_test_with_filter( string $hook, callable $cb, callable $fn ) {
	add_filter( $hook, $cb, 10 );
	try {
		return $fn();
	} finally {
		remove_filter( $hook, $cb, 10 );
	}
}

/** Cache dir filter that resolves to nothing — isolates the href path. */
function bc_fonts_test_no_cache() {
	return '';
}

$bc_fonts_css  = '@font-face{font-family:"Inter";font-style:normal;font-weight:400;font-display:swap;src:url(https://example.test/wp-content/cache/perfmatters/example.test/fonts/inter.woff2) format("woff2");unicode-range:U+0000-00FF,U+0131}';
$bc_fonts_link = "<link rel='stylesheet' id='blocksy-fonts-font-source-google-css' href='https://fonts.googleapis.com/css2?family=Inter' media='all' />\n";

// -----------------------------------------------------------------------
// Registration shape.
// -----------------------------------------------------------------------

bc_test( 'fonts/async: register hooks at the mandated priorities', function () {
	assert_same( in_array( 'blocksy_child_perf_fonts_filter_style_tag', $GLOBALS['bc_test_hooks']['style_loader_tag'][10] ?? [], true ), true, 'fonts style_loader_tag @10' );
	assert_same( in_array( 'blocksy_child_perf_fonts_output_buffer', $GLOBALS['bc_test_hooks']['perfmatters_output_buffer'][99] ?? [], true ), true, 'fonts perfmatters_output_buffer @99' );
	assert_same( in_array( 'blocksy_child_perf_async_filter_style_tag', $GLOBALS['bc_test_hooks']['style_loader_tag'][20] ?? [], true ), true, 'async style_loader_tag @20' );
	assert_same( ! empty( $GLOBALS['bc_test_hooks']['wp_head'][2] ), true, 'font-fallbacks printer at wp_head @2' );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_fonts_inline_tag() — href -> local path.
// -----------------------------------------------------------------------

bc_test( 'fonts inline: a local href under uploads is inlined as bc-perf-fonts-inline', function () use ( $bc_fonts_css, $bc_fonts_link ) {
	list( $base, $url ) = bc_fonts_test_uploads();
	mkdir( $base . '/fonts', 0777, true );
	file_put_contents( $base . '/fonts/g.google-fonts.min.css', $bc_fonts_css );

	$out = bc_fonts_test_with_filter( 'blocksy_child_perf_fonts_cache_dir', 'bc_fonts_test_no_cache', function () use ( $bc_fonts_link, $url ) {
		return blocksy_child_perf_fonts_inline_tag( $bc_fonts_link, 'blocksy-fonts-font-source-google', $url . '/fonts/g.google-fonts.min.css?ver=1' );
	} );

	assert_same( strpos( $out, '<style id="bc-perf-fonts-inline" data-no-optimize="1" data-no-minify="1">' ), 0, 'inline style tag with optimizer-exempt attributes' );
	assert_same( strpos( $out, $bc_fonts_css ) !== false, true, 'file contents inlined verbatim' );
	assert_same( strpos( $out, '<link' ), false, 'no <link> left (preloads default off)' );
	assert_same( strpos( $out, 'preload' ), false, 'no preload emitted by default' );
} );

bc_test( 'fonts inline: a handle not in blocksy_child_perf_font_handles is untouched', function () use ( $bc_fonts_link ) {
	assert_same( blocksy_child_perf_fonts_inline_tag( $bc_fonts_link, 'some-other-style', 'https://example.test/x.css' ), $bc_fonts_link );
} );

bc_test( 'fonts inline: file over the 50 KB cap fails open to the original tag', function () use ( $bc_fonts_link ) {
	list( $base, $url ) = bc_fonts_test_uploads();
	file_put_contents( $base . '/big.css', str_repeat( 'a', 51201 ) );

	$out = bc_fonts_test_with_filter( 'blocksy_child_perf_fonts_cache_dir', 'bc_fonts_test_no_cache', function () use ( $bc_fonts_link, $url ) {
		return blocksy_child_perf_fonts_inline_tag( $bc_fonts_link, 'blocksy-fonts-font-source-google', $url . '/big.css' );
	} );

	assert_same( $out, $bc_fonts_link, 'oversized file -> original <link>' );
} );

bc_test( 'fonts inline: missing file (and no cache) fails open to the original tag', function () use ( $bc_fonts_link ) {
	list( , $url ) = bc_fonts_test_uploads();

	$out = bc_fonts_test_with_filter( 'blocksy_child_perf_fonts_cache_dir', 'bc_fonts_test_no_cache', function () use ( $bc_fonts_link, $url ) {
		return blocksy_child_perf_fonts_inline_tag( $bc_fonts_link, 'blocksy-fonts-font-source-google', $url . '/nope.css' );
	} );

	assert_same( $out, $bc_fonts_link );
} );

bc_test( 'fonts inline: a remote (non-local) href with no cache fails open — never fetched', function () use ( $bc_fonts_link ) {
	bc_fonts_test_uploads();

	$out = bc_fonts_test_with_filter( 'blocksy_child_perf_fonts_cache_dir', 'bc_fonts_test_no_cache', function () use ( $bc_fonts_link ) {
		return blocksy_child_perf_fonts_inline_tag( $bc_fonts_link, 'blocksy-fonts-font-source-google', 'https://fonts.googleapis.com/css2?family=Inter' );
	} );

	assert_same( $out, $bc_fonts_link );
} );

bc_test( 'fonts inline: ../ traversal out of the uploads root is refused (realpath containment)', function () use ( $bc_fonts_css, $bc_fonts_link ) {
	list( $base, $url ) = bc_fonts_test_uploads();
	mkdir( $base . '/up', 0777, true );
	file_put_contents( $base . '/outside.css', $bc_fonts_css );
	$GLOBALS['bc_wp_stub']['upload_dir']['basedir'] = $base . '/up';

	$out = bc_fonts_test_with_filter( 'blocksy_child_perf_fonts_cache_dir', 'bc_fonts_test_no_cache', function () use ( $bc_fonts_link, $url ) {
		return blocksy_child_perf_fonts_inline_tag( $bc_fonts_link, 'blocksy-fonts-font-source-google', $url . '/../outside.css' );
	} );

	assert_same( $out, $bc_fonts_link, 'escaping the root -> original tag' );
} );

bc_test( 'fonts inline: CSS containing </style is refused (fails open)', function () use ( $bc_fonts_link ) {
	list( $base, $url ) = bc_fonts_test_uploads();
	file_put_contents( $base . '/evil.css', 'a{}</style><script>alert(1)</script>' );

	$out = bc_fonts_test_with_filter( 'blocksy_child_perf_fonts_cache_dir', 'bc_fonts_test_no_cache', function () use ( $bc_fonts_link, $url ) {
		return blocksy_child_perf_fonts_inline_tag( $bc_fonts_link, 'blocksy-fonts-font-source-google', $url . '/evil.css' );
	} );

	assert_same( $out, $bc_fonts_link );
} );

// -----------------------------------------------------------------------
// Glob fallback — Perfmatters cache.
// -----------------------------------------------------------------------

bc_test( 'fonts inline: remote href falls back to the Perfmatters cache glob (newest wins)', function () use ( $bc_fonts_css, $bc_fonts_link ) {
	bc_fonts_test_uploads();
	$content = bc_fonts_test_tmpdir();
	$dir     = $content . '/cache/perfmatters/example.test/fonts';
	mkdir( $dir, 0777, true );
	file_put_contents( $dir . '/old.google-fonts.min.css', 'OLD{}' );
	touch( $dir . '/old.google-fonts.min.css', time() - 3600 );
	file_put_contents( $dir . '/x.google-fonts.min.css', $bc_fonts_css );
	file_put_contents( $dir . '/unrelated.css', 'NOPE{}' );

	$out = bc_fonts_test_with_filter( 'blocksy_child_perf_fonts_cache_dir', function () use ( $dir ) {
		return $dir;
	}, function () use ( $bc_fonts_link ) {
		return blocksy_child_perf_fonts_inline_tag( $bc_fonts_link, 'blocksy-fonts-font-source-google', 'https://fonts.googleapis.com/css2?family=Inter' );
	} );

	assert_same( strpos( $out, '<style id="bc-perf-fonts-inline"' ), 0, 'inlined from the cache' );
	assert_same( strpos( $out, $bc_fonts_css ) !== false, true, 'newest google-fonts file used' );
	assert_same( strpos( $out, 'OLD' ), false, 'stale hash ignored' );
} );

bc_test( 'fonts inline: default cache dir is WP_CONTENT_DIR/cache/perfmatters/<host>/fonts, or empty without WP_CONTENT_DIR', function () {
	$dir = blocksy_child_perf_fonts_default_cache_dir( 'example.test' );
	if ( defined( 'WP_CONTENT_DIR' ) ) {
		assert_same( $dir, rtrim( WP_CONTENT_DIR, '/\\' ) . '/cache/perfmatters/example.test/fonts' );
	} else {
		assert_same( $dir, '' );
	}
} );

bc_test( 'fonts inline: preload faces — true derives latin woff2 faces with crossorigin', function () use ( $bc_fonts_css, $bc_fonts_link ) {
	list( $base, $url ) = bc_fonts_test_uploads();
	file_put_contents( $base . '/p.css', $bc_fonts_css . '@font-face{font-family:"Inter";src:url(https://example.test/cyr.woff2) format("woff2");unicode-range:U+0400-045F}' );

	$out = bc_fonts_test_with_filter( 'blocksy_child_perf_fonts_cache_dir', 'bc_fonts_test_no_cache', function () use ( $bc_fonts_link, $url ) {
		return bc_fonts_test_with_filter( 'blocksy_child_perf_font_preload_faces', '__return_true', function () use ( $bc_fonts_link, $url ) {
			return blocksy_child_perf_fonts_inline_tag( $bc_fonts_link, 'blocksy-fonts-font-source-google', $url . '/p.css' );
		} );
	} );

	assert_same( substr_count( $out, 'rel="preload"' ), 1, 'only the latin face preloaded' );
	assert_same( strpos( $out, 'inter.woff2" as="font" type="font/woff2" crossorigin' ) !== false, true, 'crossorigin font preload' );
	assert_same( strpos( $out, 'cyr.woff2' ) !== false && strpos( $out, 'href="https://example.test/cyr.woff2"' ) === false, true, 'non-latin face not preloaded' );
} );

// -----------------------------------------------------------------------
// Output-buffer strategy (Perfmatters rewrites the link after style_loader_tag).
// -----------------------------------------------------------------------

bc_test( 'fonts buffer: inlines a rewritten Perfmatters cache <link> when style_loader_tag did not fire', function () use ( $bc_fonts_css ) {
	list( $base, $url ) = bc_fonts_test_uploads();
	$GLOBALS['blocksy_child_perf_state']['fonts_inlined'] = false;
	mkdir( $base . '/cache/perfmatters/example.test/fonts', 0777, true );
	file_put_contents( $base . '/cache/perfmatters/example.test/fonts/h.google-fonts.min.css', $bc_fonts_css );

	$link = '<link rel="stylesheet" id="blocksy-fonts-font-source-google-css" href="' . $url . '/cache/perfmatters/example.test/fonts/h.google-fonts.min.css?ver=2" media="all">';
	$html = '<html><head>' . $link . '<link rel="stylesheet" href="' . $url . '/other.css"></head></html>';

	$out = blocksy_child_perf_fonts_output_buffer( $html );

	assert_same( strpos( $out, $link ), false, 'font <link> removed' );
	assert_same( strpos( $out, '<style id="bc-perf-fonts-inline" data-no-optimize="1" data-no-minify="1">' . $bc_fonts_css . '</style>' ) !== false, true, 'inlined' );
	assert_same( strpos( $out, '/other.css' ) !== false, true, 'other links untouched' );
} );

bc_test( 'fonts buffer: no-op when style_loader_tag already inlined this request', function () {
	$GLOBALS['blocksy_child_perf_state']['fonts_inlined'] = true;
	$html = '<link rel="stylesheet" href="https://example.test/wp-content/uploads/cache/perfmatters/x/fonts/a.google-fonts.min.css">';
	assert_same( blocksy_child_perf_fonts_output_buffer( $html ), $html );
	$GLOBALS['blocksy_child_perf_state']['fonts_inlined'] = false;
} );

bc_test( 'fonts buffer: unreadable cache link fails open', function () {
	bc_fonts_test_uploads();
	$GLOBALS['blocksy_child_perf_state']['fonts_inlined'] = false;
	$html = '<link rel="stylesheet" href="https://example.test/wp-content/uploads/cache/perfmatters/x/fonts/missing.google-fonts.min.css">';
	assert_same( blocksy_child_perf_fonts_output_buffer( $html ), $html );
} );

bc_test( 'fonts: a %00 (NUL byte) href is refused before realpath() — original tag / HTML returned, no ValueError', function () use ( $bc_fonts_css, $bc_fonts_link ) {
	list( $base, $url ) = bc_fonts_test_uploads();
	file_put_contents( $base . '/g.google-fonts.min.css', $bc_fonts_css );

	$out = bc_fonts_test_with_filter( 'blocksy_child_perf_fonts_cache_dir', 'bc_fonts_test_no_cache', function () use ( $bc_fonts_link, $url ) {
		return blocksy_child_perf_fonts_inline_tag( $bc_fonts_link, 'blocksy-fonts-font-source-google', $url . '/g.google-fonts.min.css%00.png' );
	} );
	assert_same( $out, $bc_fonts_link, 'style_loader_tag path: original tag' );

	assert_same( blocksy_child_perf_fonts_url_to_path( $url . '/g.google-fonts.min.css%00' ), null, 'url_to_path(): null, not a ValueError' );

	$GLOBALS['blocksy_child_perf_state']['fonts_inlined'] = false;
	$html = '<link rel="stylesheet" href="' . $url . '/cache/perfmatters/x/fonts/a%00.google-fonts.min.css">';
	assert_same( blocksy_child_perf_fonts_output_buffer( $html ), $html, 'output-buffer path: HTML unchanged' );
} );

bc_test( 'fonts: successful style_loader_tag inline sets the request flag', function () use ( $bc_fonts_css, $bc_fonts_link ) {
	list( $base, $url ) = bc_fonts_test_uploads();
	$GLOBALS['blocksy_child_perf_state']['fonts_inlined'] = false;
	file_put_contents( $base . '/f.css', $bc_fonts_css );

	bc_fonts_test_with_filter( 'blocksy_child_perf_fonts_cache_dir', 'bc_fonts_test_no_cache', function () use ( $bc_fonts_link, $url ) {
		return blocksy_child_perf_fonts_filter_style_tag( $bc_fonts_link, 'blocksy-fonts-font-source-google', $url . '/f.css' );
	} );

	assert_same( $GLOBALS['blocksy_child_perf_state']['fonts_inlined'], true );
	$GLOBALS['blocksy_child_perf_state']['fonts_inlined'] = false;
} );

// -----------------------------------------------------------------------
// Metric-matched fallback faces.
// -----------------------------------------------------------------------

$bc_fonts_map = [
	[
		'family'      => 'X Fallback',
		'local'       => [ 'Arial', 'Liberation Sans' ],
		'size_adjust' => '78%',
		'ascent'      => '128.2%',
		'descent'     => '25.6%',
		'line_gap'    => '0%',
		'css_var'     => '--wp--preset--font-family--barlow-semi-condensed',
		'stack'       => '"Barlow Semi Condensed", "X Fallback", sans-serif',
	],
];

bc_test( 'fonts fallback css: exact @font-face + html:root var for a sample map', function () use ( $bc_fonts_map ) {
	$css = blocksy_child_perf_fonts_fallback_css( $bc_fonts_map );

	assert_same(
		$css,
		'@font-face{font-family:"X Fallback";src:local("Arial"),local("Liberation Sans");size-adjust:78%;ascent-override:128.2%;descent-override:25.6%;line-gap-override:0%}'
		. 'html:root{--wp--preset--font-family--barlow-semi-condensed:"Barlow Semi Condensed", "X Fallback", sans-serif}'
	);
	assert_same( strpos( $css, 'size-adjust:78%' ) !== false, true );
	assert_same( strpos( $css, 'html:root{' ) !== false, true );
} );

bc_test( 'fonts fallback css: empty map -> empty string; invalid entries dropped', function () {
	assert_same( blocksy_child_perf_fonts_fallback_css( [] ), '' );
	assert_same( blocksy_child_perf_fonts_fallback_css( [ [ 'family' => 'X', 'local' => [] ] ] ), '', 'no local() sources -> dropped' );
	assert_same( blocksy_child_perf_fonts_fallback_css( [ [ 'family' => 'X', 'local' => [ 'Arial' ], 'size_adjust' => '78%;}body{display:none' ] ] ), '', 'bad percentage -> dropped' );
	assert_same( blocksy_child_perf_fonts_fallback_css( [ [ 'family' => 'X</style>', 'local' => [ 'Arial' ] ] ] ), '', 'unsafe family -> dropped' );
} );

bc_test( 'fonts fallback: wp_head @2 prints nothing with an empty map, and the filtered map at print time', function () use ( $bc_fonts_map ) {
	$fire = function () {
		ob_start();
		foreach ( $GLOBALS['bc_test_hooks']['wp_head'][2] as $cb ) {
			call_user_func( $cb );
		}
		return ob_get_clean();
	};

	assert_same( $fire(), '', 'empty map prints nothing' );

	$out = bc_fonts_test_with_filter( 'blocksy_child_perf_font_fallbacks', function () use ( $bc_fonts_map ) {
		return $bc_fonts_map;
	}, $fire );

	assert_same( strpos( $out, '<style id="bc-perf-font-fallbacks" data-no-optimize="1" data-no-minify="1">@font-face{' ), 0 );
	assert_same( strpos( $out, 'html:root{' ) !== false, true );
} );

// -----------------------------------------------------------------------
// async-styles.
// -----------------------------------------------------------------------

bc_test( 'async data file: ships the two default handles', function () {
	$h = blocksy_child_perf_data( 'async-style-handles' );
	assert_same( in_array( 'dgwt-wcas-style', $h, true ), true );
	assert_same( in_array( 'brb-public-main-css', $h, true ), true );
} );

bc_test( 'async: media="all" swapped to print+onload with a <noscript> original', function () {
	$tag = '<link rel="stylesheet" id="dgwt-wcas-style-css" href="https://example.test/wp-content/plugins/ajax-search-for-woocommerce/assets/css/style.min.css" media="all">' . "\n";
	$out = blocksy_child_perf_async_style_tag( $tag, 'dgwt-wcas-style' );

	assert_same( strpos( $out, 'media="print" onload="this.media=\'all\'"' ) !== false, true, 'print swap' );
	assert_same( strpos( $out, '<noscript>' . rtrim( $tag ) . '</noscript>' ) !== false, true, 'noscript original' );
	assert_same( substr_count( $out, 'media=' ), 3, 'one media attr in the async tag (+ the onload text) + one in noscript' );
} );

bc_test( 'async: tag without a media attribute gets one injected', function () {
	$tag = "<link rel='stylesheet' id='brb-public-main-css-css' href='https://example.test/b.css' />\n";
	$out = blocksy_child_perf_async_style_tag( $tag, 'brb-public-main-css' );

	assert_same( strpos( $out, "href='https://example.test/b.css' media=\"print\" onload=\"this.media='all'\" />" ) !== false, true, 'injected before />' );
	assert_same( strpos( $out, '<noscript>' ) !== false, true );
} );

bc_test( 'async: filter-added handle is honoured; unlisted handle untouched', function () {
	$tag = '<link rel="stylesheet" id="foo-css" href="https://example.test/foo.css" media="all">';
	assert_same( blocksy_child_perf_async_style_tag( $tag, 'foo' ), $tag, 'unlisted' );

	$out = bc_fonts_test_with_filter( 'blocksy_child_perf_async_style_handles', function ( $h ) {
		$h[] = 'foo';
		return $h;
	}, function () use ( $tag ) {
		return blocksy_child_perf_async_style_tag( $tag, 'foo' );
	} );
	assert_same( strpos( $out, 'media="print"' ) !== false, true, 'filter-added' );
} );

bc_test( 'async: skips tags Perfmatters already delays (data-pmdelayedstyle)', function () {
	$tag = '<link rel="stylesheet" id="dgwt-wcas-style-css" data-pmdelayedstyle="https://example.test/s.css" media="all">';
	assert_same( blocksy_child_perf_async_style_tag( $tag, 'dgwt-wcas-style' ), $tag );
} );

bc_test( 'async: media="print" stylesheets and already-async tags are left alone', function () {
	$print = '<link rel="stylesheet" id="dgwt-wcas-style-css" href="https://example.test/s.css" media="print">';
	assert_same( blocksy_child_perf_async_style_tag( $print, 'dgwt-wcas-style' ), $print );

	$done = '<link rel="stylesheet" id="dgwt-wcas-style-css" href="https://example.test/s.css" media="print" onload="this.media=\'all\'">';
	assert_same( blocksy_child_perf_async_style_tag( $done, 'dgwt-wcas-style' ), $done );
} );

bc_test( 'async: refusal list — ct-main-styles, ct-*, blocksy-child-*, perfmatters-used-css, main.min.css href — unchanged + logged', function () {
	$add = function ( $h ) {
		return array_merge( $h, [ 'ct-main-styles', 'ct-woocommerce-styles', 'blocksy-child-style', 'blocksy-child-extra', 'perfmatters-used-css', 'sneaky' ] );
	};

	bc_fonts_test_with_filter( 'blocksy_child_perf_async_style_handles', $add, function () {
		foreach ( [ 'ct-main-styles', 'ct-woocommerce-styles', 'blocksy-child-style', 'blocksy-child-extra', 'perfmatters-used-css' ] as $handle ) {
			$tag = '<link rel="stylesheet" id="' . $handle . '-css" href="https://example.test/x.css" media="all">';
			$before = count( bc_test_error_log_messages() );
			assert_same( blocksy_child_perf_async_style_tag( $tag, $handle ), $tag, "{$handle} refused" );
			$log = bc_test_error_log_messages();
			assert_same( count( $log ) > $before && strpos( end( $log ), $handle ) !== false, true, "{$handle} refusal logged" );
		}

		$tag = '<link rel="stylesheet" id="sneaky-css" href="https://example.test/wp-content/themes/blocksy/static/bundle/main.min.css?ver=2" media="all">';
		$before = count( bc_test_error_log_messages() );
		assert_same( blocksy_child_perf_async_style_tag( $tag, 'sneaky' ), $tag, 'main.min.css href refused' );
		assert_same( count( bc_test_error_log_messages() ) > $before, true, 'main.min.css refusal logged' );
	} );
} );

bc_test( 'async: refusal holds even for a default handle whose tag points at main.min.css', function () {
	$tag = '<link rel="stylesheet" id="dgwt-wcas-style-css" href="https://example.test/main.min.css" media="all">';
	assert_same( blocksy_child_perf_async_style_tag( $tag, 'dgwt-wcas-style' ), $tag );
} );
