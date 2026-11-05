<?php
/**
 * Task 1: bootstrap constants, legacy shims, autoloader mapping.
 *
 * @package Blocksy_Child
 */

/**
 * Prefix for isolated subprocesses: model theme-load order (no theme constants
 * pre-defined by the test bootstrap), then run the real bootstrap.
 */
function bc_core_boot_prefix(): string {
	$root = var_export( dirname( __DIR__, 2 ), true );
	return "define( 'BC_TEST_NO_THEME_CONSTANTS', true );\n"
		. "require {$root} . '/tests/core/stubs.php';\n";
}

bc_test( 'bootstrap_defines_blaze_and_v1_constants', function () {
	$root = var_export( dirname( __DIR__, 2 ), true );
	$out  = bc_test_run_isolated(
		bc_core_boot_prefix()
		. "require {$root} . '/src/bootstrap.php';\n"
		. "echo json_encode( [\n"
		. "  'defined' => array_map( 'defined', [ 'BLAZE_CHILD_VERSION', 'BLAZE_CHILD_PATH', 'BLAZE_CHILD_URL', 'BLOCKSY_CHILD_VERSION', 'BLOCKSY_CHILD_PATH', 'BLOCKSY_CHILD_URL' ] ),\n"
		. "  'path_same' => BLAZE_CHILD_PATH === BLOCKSY_CHILD_PATH,\n"
		. "  'ver_same' => BLAZE_CHILD_VERSION === BLOCKSY_CHILD_VERSION,\n"
		. "  'url_same' => BLAZE_CHILD_URL === BLOCKSY_CHILD_URL,\n"
		. "  'slash' => substr( BLAZE_CHILD_PATH, -1 ) . substr( BLAZE_CHILD_URL, -1 ),\n"
		. "  'version' => BLAZE_CHILD_VERSION,\n"
		. "  'blaze_blocksy' => [ BLAZE_BLOCKSY_URL === BLAZE_CHILD_URL, BLAZE_BLOCKSY_PATH === BLAZE_CHILD_PATH ],\n"
		. "] );"
	);
	assert_same( $out['defined'], [ true, true, true, true, true, true ], 'all six constants' );
	assert_same( $out['path_same'], true, 'BLAZE_CHILD_PATH === BLOCKSY_CHILD_PATH' );
	assert_same( $out['ver_same'], true, 'versions match' );
	assert_same( $out['url_same'], true, 'urls match' );
	assert_same( $out['slash'], '//', 'trailing slashes' );
	assert_same( $out['version'], '2.0.0-alpha.1', 'version read from style.css' );
	assert_same( $out['blaze_blocksy'], [ true, true ], 'BLAZE_BLOCKSY_* default to child values' );
} );

bc_test( 'legacy_constants_not_redefined', function () {
	$root = var_export( dirname( __DIR__, 2 ), true );
	$out  = bc_test_run_isolated(
		bc_core_boot_prefix()
		. "define( 'BLAZE_BLOCKSY_URL', 'x' );\n"
		. "define( 'BLOCKSY_CHILD_VERSION', 'pre' );\n"
		. "require {$root} . '/src/bootstrap.php';\n"
		. "echo json_encode( [ BLAZE_BLOCKSY_URL, BLOCKSY_CHILD_VERSION, BLAZE_BLOCKSY_PATH === BLAZE_CHILD_PATH ] );"
	);
	assert_same( $out, [ 'x', 'pre', true ], 'pre-defined constants kept, no warning' );
} );

bc_test( 'autoloader_maps_module_ids', function () {
	$root = var_export( dirname( __DIR__, 2 ), true );
	$out  = bc_test_run_isolated(
		bc_core_boot_prefix()
		. "require {$root} . '/src/bootstrap.php';\n"
		. "\$p = \\Blaze\\Child\\Core\\Theme::module_class_path( 'hide-cart-page' );\n"
		. "echo json_encode( [ \\Blaze\\Child\\Core\\Theme::studly( 'hide-cart-page' ), substr( \$p, -strlen( 'modules/hide-cart-page/Module.php' ) ), class_exists( '\\\\Blaze\\\\Child\\\\Core\\\\Legacy' ) ] );"
	);
	assert_same( $out[0], 'HideCartPage' );
	assert_same( $out[1], 'modules/hide-cart-page/Module.php' );
	assert_same( $out[2], true, 'PSR-4 autoload of src/Core/Legacy.php' );
} );

bc_test( 'autoloader_resolves_module_namespace_to_kebab_dir', function () {
	require_once dirname( __DIR__, 2 ) . '/src/bootstrap.php';
	assert_same( \Blaze\Child\Core\Theme::studly( 'a' ), 'A' );
	assert_same( blaze_child_autoload_path( 'Blaze\\Child\\Modules\\HideCartPage\\Module' ), BLAZE_CHILD_PATH . 'modules/hide-cart-page/Module.php' );
	assert_same( blaze_child_autoload_path( 'Blaze\\Child\\Core\\Theme' ), BLAZE_CHILD_PATH . 'src/Core/Theme.php' );
	assert_same( blaze_child_autoload_path( 'Other\\Thing' ), null );
} );
