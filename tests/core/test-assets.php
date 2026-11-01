<?php
/**
 * Task 3: Assets.
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/src/bootstrap.php';

use Blaze\Child\Core\Assets;
use Blaze\Child\Core\Health;
use Blaze\Child\Core\Module;

/** Temp theme dir with modules/<id>/<file>; returns [ dir, url ]. */
function bc_as_theme( array $files ): array {
	$dir = sys_get_temp_dir() . '/bc_as_' . uniqid();
	foreach ( $files as $rel ) {
		@mkdir( dirname( "$dir/$rel" ), 0777, true );
		file_put_contents( "$dir/$rel", '/* x */' );
		touch( "$dir/$rel", 1700000000 );
	}
	return [ $dir . '/', 'https://example.test/theme/' ];
}

function bc_as_module( string $id, array $assets, string $status = 'beta' ): Module {
	return new class( [ 'id' => $id, 'status' => $status, 'assets' => $assets ], [] ) extends Module {
		public function boot(): void {}
	};
}

function bc_as_reset(): void {
	bc_wp_stub_reset();
	$GLOBALS['bc_test_hooks']            = [];
	$GLOBALS['bc_core_store']['assets']  = [];
	$GLOBALS['bc_core_store']['options'] = [];
}

function bc_as_run( array $modules, array $files ): void {
	[ $dir, $url ] = bc_as_theme( $files );
	Assets::register( $modules, $dir, $url );
	Assets::enqueue();
}

function bc_as_handles(): array {
	return array_column( $GLOBALS['bc_core_store']['assets'], 1 );
}

bc_test( 'pdp_asset_only_on_pdp', function () {
	bc_as_reset();
	$m = bc_as_module( 'pdpmod', [ [ 'file' => 'assets/x.js', 'where' => [ 'pdp' ], 'deps' => [] ] ] );
	bc_as_run( [ 'pdpmod' => $m ], [ 'modules/pdpmod/assets/x.js' ] );
	assert_same( bc_as_handles(), [], 'not on pdp' );
	$GLOBALS['bc_wp_stub']['is_product'] = true;
	Assets::enqueue();
	assert_same( bc_as_handles(), [ 'bc-pdpmod-x' ], 'on pdp' );
} );

bc_test( 'css_and_js_get_handles_and_filemtime_version', function () {
	bc_as_reset();
	$m = bc_as_module( 'mod', [
		[ 'file' => 'assets/a.js', 'where' => [ 'all' ], 'deps' => [ 'wp-hooks' ] ],
		[ 'file' => 'assets/b.min.css', 'where' => [ 'front' ], 'deps' => [] ],
	] );
	bc_as_run( [ 'mod' => $m ], [ 'modules/mod/assets/a.js', 'modules/mod/assets/b.min.css' ] );
	$calls = $GLOBALS['bc_core_store']['assets'];
	assert_same( $calls[0][0], 'wp_enqueue_script' );
	assert_same( $calls[0][1], 'bc-mod-a' );
	assert_same( $calls[0][2], 'https://example.test/theme/modules/mod/assets/a.js' );
	assert_same( $calls[0][3], [ 'wp-hooks' ] );
	assert_same( $calls[0][4], '1700000000' );
	assert_same( $calls[1][0], 'wp_enqueue_style' );
	assert_same( $calls[1][1], 'bc-mod-b.min' );
	assert_same( $calls[1][4], '1700000000' );
} );

bc_test( 'js_is_deferred_in_footer', function () {
	bc_as_reset();
	$m = bc_as_module( 'mod', [ [ 'file' => 'assets/a.js', 'where' => [ 'all' ], 'deps' => [] ] ] );
	bc_as_run( [ 'mod' => $m ], [ 'modules/mod/assets/a.js' ] );
	assert_same( $GLOBALS['bc_core_store']['assets'][0][5], [ 'strategy' => 'defer', 'in_footer' => true ] );
} );

bc_test( 'jquery_dep_refused', function () {
	bc_as_reset();
	$m = bc_as_module( 'mod', [ [ 'file' => 'assets/a.js', 'where' => [ 'all' ], 'deps' => [ 'jquery', 'wp-hooks' ] ] ] );
	bc_as_run( [ 'mod' => $m ], [ 'modules/mod/assets/a.js' ] );
	assert_same( $GLOBALS['bc_core_store']['assets'][0][3], [ 'wp-hooks' ], 'dep dropped, asset still enqueued' );
	assert_same( in_array( 'jquery_dep_refused', array_column( Health::all(), 'code' ), true ), true, 'health recorded' );

	bc_as_reset();
	$m = bc_as_module( 'old', [ [ 'file' => 'assets/a.js', 'where' => [ 'all' ], 'deps' => [ 'jquery' ] ] ], 'legacy' );
	bc_as_run( [ 'old' => $m ], [ 'modules/old/assets/a.js' ] );
	assert_same( $GLOBALS['bc_core_store']['assets'][0][3], [ 'jquery' ], 'legacy module keeps jquery' );
	assert_same( Health::all(), [], 'no health for legacy' );
} );

bc_test( 'body_class_lists_enabled_modules', function () {
	bc_as_reset();
	Assets::register( [ 'a' => bc_as_module( 'a', [] ), 'b-c' => bc_as_module( 'b-c', [] ) ] );
	assert_same( Assets::body_class( [ 'home' ] ), [ 'home', 'bc-m-a', 'bc-m-b-c' ] );
	assert_same( Assets::body_class( 'oops' ), 'oops', 'non-array passes through' );
	assert_same( in_array( [ 'Blaze\\Child\\Core\\Assets', 'enqueue' ], $GLOBALS['bc_test_hooks']['wp_enqueue_scripts'][20] ?? [], true ), true, 'enqueue hooked @20' );
	assert_same( in_array( [ 'Blaze\\Child\\Core\\Assets', 'body_class' ], $GLOBALS['bc_test_hooks']['body_class'][10] ?? [], true ), true, 'body_class hooked' );
} );

bc_test( 'wc_context_from_shortcode_page', function () {
	bc_as_reset();
	$GLOBALS['bc_wp_stub']['is_singular']         = true;
	$GLOBALS['bc_wp_stub']['post_content_blocks'] = [];
	$GLOBALS['bc_wp_stub']['shortcodes']          = [ 'add_to_cart' ];
	assert_same( Assets::page_has_wc_content(), true );
	assert_same( in_array( 'wc', Assets::contexts(), true ), true, 'wc context present' );
	$GLOBALS['bc_wp_stub']['shortcodes'] = [];
	$GLOBALS['bc_wp_stub']['has_blocks'] = [ 'woocommerce/handpicked-products' ];
	assert_same( Assets::page_has_wc_content(), true, 'block match' );
} );

bc_test( 'wc_context_false_on_plain_page', function () {
	bc_as_reset();
	$GLOBALS['bc_wp_stub']['is_singular']         = true;
	$GLOBALS['bc_wp_stub']['post_content_blocks'] = [];
	assert_same( Assets::page_has_wc_content(), false );
	assert_same( in_array( 'wc', Assets::contexts(), true ), false );
	$GLOBALS['bc_wp_stub']['is_singular']  = false;
	$GLOBALS['bc_wp_stub']['shortcodes']   = [ 'products' ];
	assert_same( Assets::page_has_wc_content(), false, 'not singular/front' );
	$GLOBALS['bc_wp_stub']['is_cart'] = true;
	assert_same( in_array( 'wc', Assets::contexts(), true ), true, 'cart implies wc' );
} );
