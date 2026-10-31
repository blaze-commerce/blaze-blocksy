<?php
/**
 * Tests for inc/defaults/helpers.php (opt-in site defaults).
 *
 * Lives beside the perf tests because it shares their runner and stubs.
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/inc/defaults/helpers.php';

$bc_defaults_saved_clients = $GLOBALS['blocksy_child_active_clients'] ?? null;

// Must run before BLOCKSY_CHILD_DEFAULTS is defined below.
bc_test( 'defaults: a site with no manifest, constant or filter gets none', function () {
	$GLOBALS['blocksy_child_active_clients'] = [];
	assert_same( blocksy_child_defaults_enabled_list(), [], 'nothing enabled' );
	assert_same( blocksy_child_default_enabled( 'mobile-inputs' ), false, 'mobile-inputs off' );
} );

bc_test( 'defaults: a manifest without "defaults" (only legacy "features") gets none', function () {
	$GLOBALS['blocksy_child_active_clients'] = [
		[ 'slug' => 'legacy', 'dir' => '/tmp/', 'manifest' => [ 'active' => true ] ],
	];
	assert_same( blocksy_child_defaults_enabled_list(), [], 'no "features"-style all-on fallback' );
} );

if ( ! defined( 'BLOCKSY_CHILD_DEFAULTS' ) ) {
	define( 'BLOCKSY_CHILD_DEFAULTS', [ 'tap-targets' ] );
}

$bc_defaults_filter = function ( $d ) {
	$d[] = 'recaptcha-scope';
	return $d;
};
add_filter( 'blocksy_child_defaults', $bc_defaults_filter );

bc_test( 'defaults: unions manifest "defaults" + BLOCKSY_CHILD_DEFAULTS + filter, lowercased, unknown dropped', function () {
	$GLOBALS['blocksy_child_active_clients'] = [
		[ 'slug' => 't', 'dir' => '/tmp/', 'manifest' => [ 'defaults' => [ 'Mobile-Inputs', 'bogus-default-xyz', 'tap-targets' ] ] ],
	];
	$list = blocksy_child_defaults_enabled_list();
	sort( $list );
	assert_same( $list, [ 'mobile-inputs', 'recaptcha-scope', 'tap-targets' ], 'resolved set' );
	assert_same( blocksy_child_default_enabled( 'MOBILE-INPUTS' ), true, 'case-insensitive check' );
	assert_same( blocksy_child_default_enabled( 'payment-assets-scope' ), false, 'not opted in' );

	$logged = implode( "\n", bc_test_error_log_messages() );
	assert_same( false !== strpos( $logged, 'unknown default: bogus-default-xyz' ), true, 'unknown name logged' );
} );

bc_test( 'defaults: every known default has its file', function () {
	foreach ( blocksy_child_defaults_known() as $name => $kind ) {
		$file = 'css' === $kind
			? BLOCKSY_CHILD_PATH . 'assets/css/components/defaults-' . $name . '.css'
			: BLOCKSY_CHILD_PATH . 'inc/defaults/' . $name . '.php';
		assert_same( file_exists( $file ), true, $name . ' -> ' . $file );
	}
} );

remove_filter( 'blocksy_child_defaults', $bc_defaults_filter );
if ( null === $bc_defaults_saved_clients ) {
	unset( $GLOBALS['blocksy_child_active_clients'] );
} else {
	$GLOBALS['blocksy_child_active_clients'] = $bc_defaults_saved_clients;
}
