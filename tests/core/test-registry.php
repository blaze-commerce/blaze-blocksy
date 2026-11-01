<?php
/**
 * Task 2: Registry, Module, Health, Compat, Api.
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/src/bootstrap.php';

use Blaze\Child\Core\Compat;
use Blaze\Child\Core\Health;
use Blaze\Child\Core\Registry;

function bc_fx_dir(): string {
	return __DIR__ . '/fixtures/modules';
}

function bc_fx_reset(): void {
	bc_wp_stub_reset();
	$GLOBALS['bc_core_store']['options'] = [];
	$GLOBALS['bc_test_hooks']            = [];
	$GLOBALS['bc_fx_booted']             = [];
	$GLOBALS['bc_fx_throw']              = false;
}

function bc_fx_codes(): array {
	return array_column( Health::all(), 'code' );
}

/** Builds a temp modules dir from id => manifest array; returns its path. */
function bc_fx_tmp_modules( array $manifests ): string {
	$dir = sys_get_temp_dir() . '/bc_mods_' . uniqid();
	foreach ( $manifests as $name => $json ) {
		mkdir( $dir . '/' . $name, 0777, true );
		file_put_contents( $dir . '/' . $name . '/module.json', is_array( $json ) ? json_encode( $json ) : $json );
	}
	return $dir;
}

bc_test( 'defaults_are_off', function () {
	bc_fx_reset();
	$r = new Registry( bc_fx_dir() );
	assert_same( $r->resolve( [] ), [], 'nothing on by default' );
	assert_same( array_keys( $r->manifests() ), [ 'alpha', 'beta', 'clash-a', 'clash-b', 'needs-wc' ], 'sorted by id' );
} );

bc_test( 'legacy_v1_sorts_first', function () {
	bc_fx_reset();
	$dir = bc_fx_tmp_modules( [
		'zeta'      => [ 'id' => 'zeta' ],
		'legacy-v1' => [ 'id' => 'legacy-v1' ],
		'alpha'     => [ 'id' => 'alpha' ],
	] );
	assert_same( array_keys( ( new Registry( $dir ) )->manifests() ), [ 'legacy-v1', 'alpha', 'zeta' ] );
} );

bc_test( 'site_config_turns_on', function () {
	bc_fx_reset();
	$r = new Registry( bc_fx_dir() );
	assert_same( $r->resolve( [ 'alpha' => true, 'beta' => false ] ), [ 'alpha' ] );
} );

bc_test( 'filter_can_turn_off', function () {
	bc_fx_reset();
	add_filter( 'blaze_child/module_enabled', function ( $on, $id ) {
		return 'alpha' === $id ? false : $on;
	}, 10, 2 );
	$r = new Registry( bc_fx_dir() );
	assert_same( $r->resolve( [ 'alpha' => true, 'clash-a' => true ] ), [ 'clash-a' ] );
} );

bc_test( 'filter_can_turn_on_ordinary_module', function () {
	bc_fx_reset();
	add_filter( 'blaze_child/module_enabled', function ( $on, $id ) {
		return 'alpha' === $id ? true : $on;
	}, 10, 2 );
	assert_same( ( new Registry( bc_fx_dir() ) )->resolve( [] ), [ 'alpha' ] );
} );

bc_test( 'r1_filter_cannot_turn_on_legacy_files_module', function () {
	bc_fx_reset();
	$dir = bc_fx_tmp_modules( [
		'repl' => [ 'id' => 'repl', 'loadable' => true, 'legacy' => [ 'files' => [ 'inc/x.php' ] ] ],
		'free' => [ 'id' => 'free', 'loadable' => true, 'legacy' => [ 'files' => [] ] ],
	] );
	add_filter( 'blaze_child/module_enabled', '__return_true', 10, 2 );
	$r = new Registry( $dir );
	assert_same( $r->resolve( [] ), [ 'free' ], 'filter ON ignored for legacy.files module' );
	assert_same( $r->resolve( [ 'repl' => true ] ), [ 'free', 'repl' ], 'site config turns it on' );
	bc_fx_reset();
	add_filter( 'blaze_child/module_enabled', '__return_false', 10, 2 );
	assert_same( ( new Registry( $dir ) )->resolve( [ 'repl' => true ] ), [], 'filter may still turn it off' );
} );

bc_test( 'kill_switch_constant_wins', function () {
	$root = var_export( dirname( __DIR__, 2 ), true );
	$out  = bc_test_run_isolated(
		"define( 'BC_TEST_NO_THEME_CONSTANTS', true );\n"
		. "require {$root} . '/tests/core/stubs.php';\n"
		. "require {$root} . '/src/bootstrap.php';\n"
		. "define( 'BLAZE_CHILD_DISABLE_MODULES', ' alpha , nope' );\n"
		. "\$r = new \\Blaze\\Child\\Core\\Registry( {$root} . '/tests/core/fixtures/modules' );\n"
		. "echo json_encode( \$r->resolve( [ 'alpha' => true, 'clash-a' => true ] ) );"
	);
	assert_same( $out, [ 'clash-a' ] );
	$out = bc_test_run_isolated(
		"define( 'BC_TEST_NO_THEME_CONSTANTS', true );\n"
		. "require {$root} . '/tests/core/stubs.php';\n"
		. "require {$root} . '/src/bootstrap.php';\n"
		. "define( 'BLAZE_CHILD_DISABLE_MODULES', [ 'clash-a' ] );\n"
		. "\$r = new \\Blaze\\Child\\Core\\Registry( {$root} . '/tests/core/fixtures/modules' );\n"
		. "echo json_encode( \$r->resolve( [ 'alpha' => true, 'clash-a' => true ] ) );"
	);
	assert_same( $out, [ 'alpha' ], 'array form' );
} );

bc_test( 'unknown_module_recorded', function () {
	bc_fx_reset();
	$r = new Registry( bc_fx_dir() );
	assert_same( $r->resolve( [ 'alpah' => true, 'alpha' => true ] ), [ 'alpha' ] );
	assert_same( bc_fx_codes(), [ 'unknown_module' ] );
} );

bc_test( 'non_loadable_never_enabled', function () {
	bc_fx_reset();
	$dir = bc_fx_tmp_modules( [ 'stub' => [ 'id' => 'stub', 'loadable' => false, 'default' => true ] ] );
	add_filter( 'blaze_child/module_enabled', '__return_true', 10, 2 );
	assert_same( ( new Registry( $dir ) )->resolve( [ 'stub' => true ] ), [] );
} );

bc_test( 'invalid_manifests_recorded_and_skipped', function () {
	bc_fx_reset();
	$dir = bc_fx_tmp_modules( [
		'good'    => [ 'id' => 'good' ],
		'broken'  => '{ not json',
		'Bad_Id'  => [ 'id' => 'Bad_Id' ],
		'mismatch' => [ 'id' => 'other' ],
		'under_x' => [ 'id' => 'under_x' ],
		'dbl--dash' => [ 'id' => 'dbl--dash' ],
	] );
	assert_same( array_keys( ( new Registry( $dir ) )->manifests() ), [ 'good' ] );
	assert_same( array_unique( bc_fx_codes() ), [ 'manifest_invalid' ] );
	assert_same( count( Health::all() ), 5 );
} );

bc_test( 'surface_clash_keeps_replacer', function () {
	bc_fx_reset();
	$r = new Registry( bc_fx_dir() );
	assert_same( $r->resolve( [ 'clash-a' => true, 'clash-b' => true ] ), [ 'clash-b' ] );
	assert_same( bc_fx_codes(), [ 'surface_clash' ] );
} );

bc_test( 'surface_clash_keeps_first_without_replaces', function () {
	bc_fx_reset();
	$r = new Registry( bc_fx_dir() );
	assert_same( $r->resolve( [ 'beta' => true, 'alpha' => true ] ), [ 'beta' ], 'first in site_modules order' );
	assert_same( bc_fx_codes(), [ 'surface_clash' ] );
} );

bc_test( 'requires_unmet_skips_only_that_module', function () {
	bc_fx_reset();
	$r   = new Registry( bc_fx_dir() );
	$ids = $r->resolve( [ 'alpha' => true, 'needs-wc' => true ] );
	$out = $r->boot( $ids, [] );
	assert_same( array_keys( $out ), [ 'alpha' ] );
	assert_same( $GLOBALS['bc_fx_booted'], [ 'alpha' ] );
	assert_same( bc_fx_codes(), [ 'requires_unmet' ] );
} );

bc_test( 'compat_unmet_versions', function () {
	assert_same( count( Compat::unmet( [ 'requires' => [] ] ) ), 0 );
	$m = [ 'requires' => [ 'plugins' => [ 'woocommerce' => '>=9.0' ] ] ];
	assert_same( count( Compat::unmet( $m ) ), 1, 'WC inactive' );
	$m = [ 'requires' => [ 'blocksy' => '>=99.0' ] ];
	assert_same( count( Compat::unmet( $m ) ), 1, 'blocksy too old' );
	$m = [ 'requires' => [ 'blocksy' => '>=1.0' ] ];
	assert_same( Compat::unmet( $m ), [], 'blocksy ok' );
} );

bc_test( 'compat_woocommerce_version_isolated', function () {
	$root = var_export( dirname( __DIR__, 2 ), true );
	$pre  = "define( 'BC_TEST_NO_THEME_CONSTANTS', true );\n"
		. "require {$root} . '/tests/core/stubs.php';\n"
		. "require {$root} . '/src/bootstrap.php';\n"
		. "class WooCommerce {}\n";
	$run  = function ( $ver ) use ( $pre ) {
		return bc_test_run_isolated(
			$pre . "define( 'WC_VERSION', '{$ver}' );\n"
			. "\$m = [ 'requires' => [ 'plugins' => [ 'woocommerce' => '>=9.0' ] ] ];\n"
			. "echo json_encode( [ count( \\Blaze\\Child\\Core\\Compat::unmet( \$m ) ) ] );"
		);
	};
	assert_same( $run( '8.5.0' ), [ 1 ], 'old WC unmet' );
	assert_same( $run( '9.5.1' ), [ 0 ], 'new WC met' );
} );

bc_test( 'module_boot_exception_recorded_others_boot', function () {
	bc_fx_reset();
	$GLOBALS['bc_fx_throw'] = true;
	$r   = new Registry( bc_fx_dir() );
	$out = $r->boot( [ 'beta', 'alpha' ], [] );
	assert_same( array_keys( $out ), [ 'alpha' ] );
	assert_same( bc_fx_codes(), [ 'module_failed' ] );
	assert_same( Registry::critical_failures(), [ 'beta' ], 'beta is critical' );
} );

bc_test( 'setting_merges_site_over_default_then_filter', function () {
	bc_fx_reset();
	$r = new Registry( bc_fx_dir() );
	$m = $r->boot( [ 'alpha' ], [] );
	assert_same( $m['alpha']->setting( 'color' ), 'red', 'manifest default' );
	$m = $r->boot( [ 'alpha' ], [ 'alpha' => [ 'color' => 'blue' ] ] );
	assert_same( $m['alpha']->setting( 'color' ), 'blue', 'site over default' );
	add_filter( 'blaze_child/alpha/settings', function ( $s ) {
		$s['color'] = 'green';
		return $s;
	} );
	assert_same( $m['alpha']->setting( 'color' ), 'green', 'filter last' );
	assert_same( $m['alpha']->setting( 'missing' ), null );
	assert_same( $m['alpha']->id(), 'alpha' );
	assert_same( $m['alpha']->manifest()['title'], 'Alpha' );
} );

bc_test( 'health_caps_at_50_and_dedupes', function () {
	bc_fx_reset();
	for ( $i = 0; $i < 60; $i++ ) {
		Health::record( 'c', 'm' . $i );
	}
	assert_same( count( Health::all() ), 50 );
	assert_same( Health::all()[0]['message'], 'm10', 'oldest dropped' );
	Health::record( 'c', 'm30', [ 'x' => 1 ] );
	assert_same( count( Health::all() ), 50, 'duplicate not appended' );
	$e = Health::all()[49];
	assert_same( $e['message'], 'm30', 'duplicate moved to newest' );
	assert_same( array_keys( $e ), [ 'code', 'message', 'context', 'time' ] );
	Health::clear();
	assert_same( Health::all(), [] );
} );

bc_test( 'api_module_returns_instance', function () {
	bc_fx_reset();
	$r = new Registry( bc_fx_dir() );
	$r->boot( [ 'alpha' ], [] );
	assert_same( \Blaze\Child\Api\module( 'alpha' ) instanceof \Blaze\Child\Core\Module, true );
	assert_same( \Blaze\Child\Api\module( 'beta' ), null );
} );
