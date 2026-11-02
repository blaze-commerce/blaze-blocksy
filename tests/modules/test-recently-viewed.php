<?php
/**
 * Task 9: recently-viewed port (cache-safe, client-side; absorbs PR #273).
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/src/Core/Module.php';
require_once dirname( __DIR__, 2 ) . '/src/Core/Health.php';
require_once dirname( __DIR__, 2 ) . '/src/Core/Registry.php';
require_once dirname( __DIR__, 2 ) . '/src/Core/Assets.php';
require_once dirname( __DIR__, 2 ) . '/modules/recently-viewed/Module.php';

use Blaze\Child\Core\Assets;
use Blaze\Child\Modules\RecentlyViewed\Module as RecentlyViewed;

if ( ! function_exists( 'rest_url' ) ) {
	function rest_url( $path = '', $scheme = 'rest' ) {
		return 'https://example.test/wp-json/' . ltrim( (string) $path, '/' );
	}
}
if ( ! function_exists( 'get_theme_mod' ) ) {
	function get_theme_mod( $name, $default = false ) {
		return $GLOBALS['bc_rv_theme_mods'][ $name ] ?? $default;
	}
}
if ( ! function_exists( '_deprecated_function' ) ) {
	function _deprecated_function( $function_name, $version, $replacement = '' ) {
		$GLOBALS['bc_core_store']['deprecated'][] = [ $function_name, $version, $replacement ];
	}
}
if ( ! function_exists( 'wp_create_nonce' ) ) {
	function wp_create_nonce( $action = -1 ) {
		$GLOBALS['bc_rv_nonce_calls'][] = $action;
		return 'n0nce';
	}
}

function bc_rv_manifest(): array {
	return json_decode( (string) file_get_contents( dirname( __DIR__, 2 ) . '/modules/recently-viewed/module.json' ), true );
}

/** Fresh state plus a booted module on a product page (queried object id 1). */
function bc_rv_boot( array $settings = [] ): RecentlyViewed {
	bc_wp_stub_reset();
	$GLOBALS['bc_test_hooks']                   = [];
	$GLOBALS['bc_core_store']['assets']         = [];
	$GLOBALS['bc_core_store']['deprecated']     = [];
	$GLOBALS['bc_rv_theme_mods']                = [];
	$GLOBALS['bc_rv_nonce_calls']               = [];
	$GLOBALS['bc_wp_stub']['is_product']        = true;
	$GLOBALS['bc_wp_stub']['post_content_blocks'] = [];
	$module = new RecentlyViewed( bc_rv_manifest(), $settings );
	$module->boot();
	return $module;
}

function bc_rv_render( RecentlyViewed $module ): string {
	ob_start();
	$module->render();
	return (string) ob_get_clean();
}

bc_test( 'container_has_no_personal_data_or_nonce', function () {
	$module = bc_rv_boot();
	$_COOKIE['woocommerce_recently_viewed'] = '4011|4022|4033';
	$GLOBALS['bc_wp_stub']['current_user_can'] = true;
	$html = bc_rv_render( $module );
	unset( $_COOKIE['woocommerce_recently_viewed'] );

	assert_same( 1, preg_match( '/^<section class="bc-recently-viewed[ "]/', $html ), 'starts with the bc-recently-viewed section: ' . $html );
	foreach ( [ 'data-bc-rv', 'data-limit="8"', 'data-product-id="1"', ' hidden', 'aria-labelledby="bc-recently-viewed-title"', 'id="bc-recently-viewed-title"', '>Recently viewed</h2>' ] as $needle ) {
		assert_same( true, false !== strpos( $html, $needle ), "container has {$needle}: {$html}" );
	}
	foreach ( [ '4011', '4022', '4033', 'nonce', 'n0nce', '<li', '<img', 'href=' ] as $needle ) {
		assert_same( false, false !== strpos( $html, $needle ), "container must not hold {$needle}: {$html}" );
	}
	assert_same( $GLOBALS['bc_rv_nonce_calls'], [], 'no nonce is created' );

	// Cache-safe: the same request without the cookie prints byte-identical markup.
	$module = bc_rv_boot();
	assert_same( bc_rv_render( $module ), $html, 'output does not depend on the visitor cookie' );
} );

bc_test( 'container_keeps_v1_classes_and_placement', function () {
	$module = bc_rv_boot();
	$hooks  = $GLOBALS['bc_test_hooks'];
	assert_same( $hooks['blocksy:woocommerce:product-single:related:after'][10] ?? null, [ [ $module, 'render' ] ], 'Blocksy hook, priority 10 (v1)' );
	assert_same( $hooks['woocommerce_after_main_content'][10] ?? null, [ [ $module, 'render' ] ], 'fallback hook, priority 10 (v1)' );
	assert_same( isset( $hooks['template_redirect'] ), false, 'no server-side tracking' );

	$html = bc_rv_render( $module );
	assert_same( true, false !== strpos( $html, 'class="bc-recently-viewed related products is-layout-slider is-width-constrained"' ), 'v1 section classes kept: ' . $html );
	assert_same( true, false !== strpos( $html, 'class="ct-module-title bc-recently-viewed__title"' ), 'v1 heading class kept: ' . $html );
	assert_same( bc_rv_render( $module ), '', 'rendered once per request (v1 static guard)' );

	$module = bc_rv_boot();
	$GLOBALS['bc_wp_stub']['is_product'] = false;
	assert_same( bc_rv_render( $module ), '', 'nothing outside product pages' );
} );

bc_test( 'settings_limit_title_and_columns', function () {
	$module = bc_rv_boot( [
		'limit' => 4,
		'title' => 'You looked at <these>',
	] );
	$html = bc_rv_render( $module );
	assert_same( true, false !== strpos( $html, 'data-limit="4"' ), $html );
	assert_same( true, false !== strpos( $html, '>You looked at <these></h2>' ), 'title setting used (escaping: see v1_functions_are_deprecated_aliases): ' . $html );
	assert_same( true, false !== strpos( $html, 'style="--bc-rv-columns:4;--bc-rv-columns-tablet:3;--bc-rv-columns-mobile:2"' ), 'v1 column defaults: ' . $html );

	assert_same( bc_rv_boot( [ 'limit' => 0 ] )->limit(), 8, 'invalid limit falls back to 8' );
	assert_same( bc_rv_boot( [ 'limit' => 99 ] )->limit(), 15, 'limit capped at the 15 stored ids' );
	assert_same( bc_rv_boot( [ 'limit' => 'x' ] )->limit(), 8 );
	assert_same( bc_rv_boot( [ 'title' => '  ' ] )->title(), 'Recently viewed', 'an empty title would leave the section unnamed' );
	assert_same( bc_rv_boot( [ 'title' => [] ] )->title(), 'Recently viewed' );

	$module = bc_rv_boot();
	$GLOBALS['bc_rv_theme_mods']['woocommerce_related_products_slideshow_columns'] = [
		'desktop' => 5,
		'tablet'  => 2,
		'mobile'  => 1,
	];
	assert_same( true, false !== strpos( bc_rv_render( $module ), 'style="--bc-rv-columns:5;--bc-rv-columns-tablet:2;--bc-rv-columns-mobile:1"' ), 'Blocksy related columns followed' );
} );

bc_test( 'assets_declared_for_pdp', function () {
	$manifest = bc_rv_manifest();
	assert_same( $manifest['surfaces'], [ 'pdp.recently-viewed' ] );
	assert_same( $manifest['status'], 'stable' );
	assert_same( $manifest['loadable'], true );
	assert_same( $manifest['default'], false );
	assert_same( $manifest['requires'], [ 'plugins' => [ 'woocommerce' => '>=9.0' ] ] );
	assert_same( $manifest['legacy']['files'], [ 'inc/recently-viewed.php' ] );
	assert_same( $manifest['assets'], [
		[ 'file' => 'assets/recently-viewed.js', 'where' => [ 'pdp', 'archive', 'front' ], 'deps' => [] ],
		[ 'file' => 'assets/recently-viewed.css', 'where' => [ 'pdp', 'archive', 'front' ], 'deps' => [] ],
	] );

	// Enqueued on a product page, JS deferred, through the real Assets class and real files.
	$module = bc_rv_boot();
	$root   = dirname( __DIR__, 2 ) . '/';
	Assets::register( [ 'recently-viewed' => $module ], $root, 'https://example.test/theme/' );
	Assets::enqueue();
	$calls = $GLOBALS['bc_core_store']['assets'];
	assert_same( array_column( $calls, 1 ), [ 'bc-recently-viewed-recently-viewed', 'bc-recently-viewed-recently-viewed' ], 'js and css enqueued' );
	assert_same( $calls[0][0], 'wp_enqueue_script' );
	assert_same( $calls[0][5], [ 'strategy' => 'defer', 'in_footer' => true ] );
	assert_same( $calls[1][0], 'wp_enqueue_style' );

	// Config is printed before the script: Store API URL and the cookie path, no nonce.
	$GLOBALS['bc_wp_stub']['scripts_registered'] = [ RecentlyViewed::HANDLE ];
	$GLOBALS['bc_wp_stub']['inline_scripts']     = [];
	$module->add_config();
	$inline = $GLOBALS['bc_wp_stub']['inline_scripts'];
	assert_same( count( $inline ), 1, 'one inline config script' );
	assert_same( $inline[0][0], RecentlyViewed::HANDLE );
	assert_same( $inline[0][2], 'before' );
	assert_same( 0, strpos( $inline[0][1], 'window.blazeChild=window.blazeChild||{};window.blazeChild.recentlyViewedConfig={' ), $inline[0][1] );
	assert_same( true, false !== strpos( $inline[0][1], '"restUrl":"https:\/\/example.test\/wp-json\/wc\/store\/v1\/products"' ), $inline[0][1] );
	assert_same( false, false !== stripos( $inline[0][1], 'nonce' ), 'no nonce in config' );
	assert_same( $GLOBALS['bc_test_hooks']['wp_enqueue_scripts'][21] ?? null, [ [ $module, 'add_config' ] ], 'config after Assets (priority 20)' );

	// Not enqueued (another page type): no inline script.
	$GLOBALS['bc_wp_stub']['scripts_registered'] = [];
	$GLOBALS['bc_wp_stub']['inline_scripts']     = [];
	$module->add_config();
	assert_same( $GLOBALS['bc_wp_stub']['inline_scripts'], [] );
} );

bc_test( 'v1_functions_are_deprecated_aliases', function () {
	$out = bc_test_run_isolated(
		"define( 'BC_TEST_ESCAPE_HTML', true );\n"
		. "require " . var_export( dirname( __DIR__, 2 ) . '/tests/core/stubs.php', true ) . ";\n"
		. "require " . var_export( dirname( __DIR__, 2 ) . '/src/Core/Module.php', true ) . ";\n"
		. "require " . var_export( dirname( __DIR__, 2 ) . '/src/Core/Health.php', true ) . ";\n"
		. "require " . var_export( dirname( __DIR__, 2 ) . '/src/Core/Registry.php', true ) . ";\n"
		. "require " . var_export( dirname( __DIR__, 2 ) . '/modules/recently-viewed/Module.php', true ) . ";\n"
		. "function _deprecated_function( \$f, \$v, \$r = '' ) { \$GLOBALS['dep'][] = \$f; }\n"
		. "function get_theme_mod( \$n, \$d = false ) { return \$d; }\n"
		. "\$GLOBALS['dep'] = [];\n"
		. "\$m = new Blaze\\Child\\Modules\\RecentlyViewed\\Module( json_decode( file_get_contents( " . var_export( dirname( __DIR__, 2 ) . '/modules/recently-viewed/module.json', true ) . " ), true ), [ 'title' => 'You looked at <these>' ] );\n"
		. "\$m->boot();\n"
		. "\$GLOBALS['bc_wp_stub']['is_product'] = true;\n"
		. "bc_track_product_view();\n"
		. "ob_start(); bc_render_recently_viewed_products(); \$html = ob_get_clean();\n"
		. "echo json_encode( [ 'dep' => \$GLOBALS['dep'], 'html' => \$html, 'exists' => function_exists( 'bc_render_recently_viewed_products' ) ] );"
	);
	assert_same( $out['exists'], true );
	assert_same( $out['dep'], [ 'bc_track_product_view', 'bc_render_recently_viewed_products' ] );
	assert_same( 0, strpos( $out['html'], '<section class="bc-recently-viewed ' ), 'alias renders the empty container: ' . $out['html'] );
	assert_same( true, false !== strpos( $out['html'], '>You looked at &lt;these&gt;</h2>' ), 'title escaped: ' . $out['html'] );
} );
