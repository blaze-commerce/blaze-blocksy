<?php
/**
 * Task 6: tools/check.php gates, MODULES.md generator, dist build.
 *
 * Each test copies tests/tools/fixtures/good/ to a temp dir, breaks one thing and runs the gates.
 *
 * Usage: php tests/tools/test-check.php
 *
 * @package Blocksy_Child
 */

define( 'BC_TC_REPO', dirname( __DIR__, 2 ) );
define( 'BC_TC_GOOD', __DIR__ . '/fixtures/good' );

require_once BC_TC_REPO . '/tools/lib/load.php';

use Blaze\Child\Tools\Runner;

$GLOBALS['tc_results'] = [ 'pass' => 0, 'fail' => 0 ];
$GLOBALS['tc_tmp']     = [];

function tc_test( string $name, callable $fn ): void {
	try {
		$fn();
		$GLOBALS['tc_results']['pass']++;
		echo "PASS: {$name}\n";
	} catch ( \Throwable $e ) {
		$GLOBALS['tc_results']['fail']++;
		echo "FAIL: {$name} -- {$e->getMessage()}\n";
	}
}

function tc_same( $actual, $expected, string $message = '' ): void {
	if ( $actual !== $expected ) {
		throw new \RuntimeException( sprintf( '%s (expected %s, got %s)', '' !== $message ? $message : 'tc_same failed', var_export( $expected, true ), var_export( $actual, true ) ) );
	}
}

function tc_true( $value, string $message ): void {
	if ( true !== $value ) {
		throw new \RuntimeException( $message );
	}
}

function tc_copy_dir( string $from, string $to ): void {
	if ( ! is_dir( $to ) ) {
		mkdir( $to, 0777, true );
	}
	$it = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $from, \FilesystemIterator::SKIP_DOTS ), \RecursiveIteratorIterator::SELF_FIRST );
	foreach ( $it as $item ) {
		$dest = $to . '/' . substr( $item->getPathname(), strlen( $from ) + 1 );
		if ( $item->isDir() ) {
			if ( ! is_dir( $dest ) ) {
				mkdir( $dest, 0777, true );
			}
		} else {
			copy( $item->getPathname(), $dest );
		}
	}
}

function tc_rm( string $dir ): void {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	$it = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ), \RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $it as $item ) {
		$item->isDir() ? rmdir( $item->getPathname() ) : unlink( $item->getPathname() );
	}
	rmdir( $dir );
}

/** A fresh copy of the passing fixture. */
function tc_good(): string {
	$dir = sys_get_temp_dir() . '/bc_check_' . uniqid( '', true );
	tc_copy_dir( BC_TC_GOOD, $dir );
	$GLOBALS['tc_tmp'][] = $dir;
	return $dir;
}

function tc_write( string $root, string $rel, string $content ): void {
	$path = $root . '/' . $rel;
	if ( ! is_dir( dirname( $path ) ) ) {
		mkdir( dirname( $path ), 0777, true );
	}
	file_put_contents( $path, $content );
}

/** Edits a module.json in place: $fn receives and returns the decoded array. */
function tc_manifest( string $root, string $id, callable $fn ): void {
	$file = "{$root}/modules/{$id}/module.json";
	$data = json_decode( (string) file_get_contents( $file ), true );
	file_put_contents( $file, json_encode( $fn( $data ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n" );
}

/** Every gate except the two external tools (the tool tests cover those with a fake binary). */
function tc_codes(): array {
	return array_values( array_diff( Runner::CODES, [ 'phpcs', 'phpstan' ] ) );
}

function tc_run( string $root, ?string $module = null, ?array $only = null, array $opts = [] ): array {
	return Runner::run( $root, $module, $only ?? tc_codes(), $opts + [ 'path' => '' ] );
}

function tc_gate( array $report, string $code ): array {
	foreach ( $report['gates'] as $gate ) {
		if ( $gate['code'] === $code ) {
			return $gate;
		}
	}
	throw new \RuntimeException( "gate {$code} not in report" );
}

/** Runs one gate on $root and asserts it failed naming $file (prefix match on "path:line"). */
function tc_fails( string $root, string $code, string $file, string $severity = 'fail' ): array {
	$gate = tc_gate( tc_run( $root, null, [ $code ] ), $code );
	tc_same( $gate['passed'], false, "{$code} should fail: " . $gate['detail'] );
	tc_same( $gate['severity'], $severity, "{$code} severity" );
	$hit = false;
	foreach ( $gate['files'] as $f ) {
		if ( $f === $file || 0 === strpos( $f, $file . ':' ) ) {
			$hit = true;
		}
	}
	tc_true( $hit, "{$code} files should name {$file}, got " . json_encode( $gate['files'] ) );
	return $gate;
}

function tc_passes( string $root, string $code, ?string $module = null ): void {
	$gate = tc_gate( tc_run( $root, $module, [ $code ] ), $code );
	tc_same( $gate['passed'], true, "{$code} should pass: " . $gate['detail'] . ' ' . json_encode( $gate['files'] ) );
}

function tc_cli( array $args, ?int &$code = null ): string {
	$cmd = escapeshellarg( PHP_BINARY );
	foreach ( $args as $arg ) {
		$cmd .= ' ' . escapeshellarg( $arg );
	}
	$out = [];
	exec( $cmd . ' 2>/dev/null', $out, $code );
	return implode( "\n", $out );
}

// ---------------------------------------------------------------------------------------------------------------

tc_test( 'good_fixture_passes_every_fail_gate', function () {
	$report = tc_run( tc_good() );
	foreach ( $report['gates'] as $gate ) {
		if ( 'fail' === $gate['severity'] ) {
			tc_same( $gate['passed'], true, "{$gate['code']}: {$gate['detail']} " . json_encode( $gate['files'] ) );
		}
	}
	tc_same( $report['passed'], true, 'report passed' );
	tc_same( array_column( $report['gates'], 'code' ), tc_codes(), 'every gate ran, in contract order' );
} );

tc_test( 'php_lint', function () {
	$root = tc_good();
	tc_passes( $root, 'php_lint' );
	tc_write( $root, 'src/Core/Broken.php', "<?php\ndefined( 'ABSPATH' ) || exit;\n\nfunction (\n" );
	tc_fails( $root, 'php_lint', 'src/Core/Broken.php' );
} );

tc_test( 'php_lint_ignores_fixtures', function () {
	$root = tc_good();
	tc_write( $root, 'tests/x/fixtures/broken.php', "<?php\nfunction (\n" );
	tc_passes( $root, 'php_lint' );
} );

tc_test( 'version_agreement', function () {
	$root = tc_good();
	tc_passes( $root, 'version_agreement' );
	file_put_contents( "{$root}/style.css", str_replace( '9.9.9', '9.9.8', (string) file_get_contents( "{$root}/style.css" ) ) );
	tc_fails( $root, 'version_agreement', 'style.css' );
	tc_same( tc_run( $root, null, [ 'version_agreement' ] )['version'], '9.9.8', 'report version is style.css Version' );
} );

tc_test( 'manifest_schema', function () {
	$root = tc_good();
	tc_passes( $root, 'manifest_schema' );
	tc_manifest( $root, 'demo', function ( $m ) {
		unset( $m['title'] );
		$m['status'] = 'shiny';
		return $m;
	} );
	$gate = tc_fails( $root, 'manifest_schema', 'modules/demo/module.json' );
	tc_true( false !== strpos( $gate['detail'] . implode( ' ', $gate['files'] ), 'modules/demo' ), 'names the module' );
} );

tc_test( 'manifest_schema_folder_without_manifest', function () {
	$root = tc_good();
	mkdir( "{$root}/modules/empty" );
	tc_fails( $root, 'manifest_schema', 'modules/empty' );
} );

tc_test( 'manifest_schema_default_on_and_stub_loadable', function () {
	$root = tc_good();
	tc_manifest( $root, 'demo', function ( $m ) {
		$m['default'] = true;
		return $m;
	} );
	tc_fails( $root, 'manifest_schema', 'modules/demo/module.json' );

	$root = tc_good();
	tc_manifest( $root, 'old-feature', function ( $m ) {
		$m['loadable'] = true;
		return $m;
	} );
	tc_fails( $root, 'manifest_schema', 'modules/old-feature/module.json' );
} );

tc_test( 'manifest_schema_asset_where_and_file', function () {
	$root = tc_good();
	tc_manifest( $root, 'demo', function ( $m ) {
		$m['assets'][0]['where'] = [ 'everywhere' ];
		$m['assets'][1]['file']  = 'assets/missing.css';
		return $m;
	} );
	$gate = tc_fails( $root, 'manifest_schema', 'modules/demo/module.json' );
	tc_true( false !== strpos( $gate['detail'], 'everywhere' ) || count( $gate['files'] ) >= 2, 'reports both asset errors' );
} );

tc_test( 'manifest_ids', function () {
	$root = tc_good();
	tc_passes( $root, 'manifest_ids' );
	tc_manifest( $root, 'demo', function ( $m ) {
		$m['id'] = 'demo-two';
		return $m;
	} );
	tc_fails( $root, 'manifest_ids', 'modules/demo/module.json' );
} );

tc_test( 'class_name', function () {
	$root = tc_good();
	tc_passes( $root, 'class_name' );
	$php = (string) file_get_contents( "{$root}/modules/demo/Module.php" );
	file_put_contents( "{$root}/modules/demo/Module.php", str_replace( 'Modules\\Demo;', 'Modules\\Demoo;', $php ) );
	tc_fails( $root, 'class_name', 'modules/demo/Module.php' );
} );

tc_test( 'class_name_missing_module_php', function () {
	$root = tc_good();
	unlink( "{$root}/modules/demo/Module.php" );
	tc_fails( $root, 'class_name', 'modules/demo/Module.php' );
} );

tc_test( 'class_name_skips_unloadable_stub', function () {
	$root = tc_good();
	// old-feature (loadable: false) has no Module.php and the gate passes (R2).
	tc_true( ! is_file( "{$root}/modules/old-feature/Module.php" ), 'stub has no php' );
	tc_passes( $root, 'class_name' );
	tc_passes( $root, 'abspath_guard' );
} );

tc_test( 'surface_unique', function () {
	$root = tc_good();
	tc_passes( $root, 'surface_unique' );
	tc_manifest( $root, 'old-feature', function ( $m ) {
		$m['surfaces'] = [ 'cart.page' ];
		return $m;
	} );
	tc_fails( $root, 'surface_unique', 'modules/old-feature/module.json' );

	tc_manifest( $root, 'demo', function ( $m ) {
		$m['replaces'] = [ 'old-feature' ];
		return $m;
	} );
	tc_passes( $root, 'surface_unique' );
} );

tc_test( 'surface_unique_ignores_deprecated', function () {
	$root = tc_good();
	tc_manifest( $root, 'old-feature', function ( $m ) {
		$m['surfaces'] = [ 'cart.page' ];
		$m['status']   = 'deprecated';
		return $m;
	} );
	tc_passes( $root, 'surface_unique' );
} );

tc_test( 'surface_vocabulary', function () {
	$root = tc_good();
	tc_passes( $root, 'surface_vocabulary' );
	tc_manifest( $root, 'demo', function ( $m ) {
		$m['surfaces'] = [ 'cart.nowhere' ];
		$m['uses']     = [ 'component.teleporter' ];
		return $m;
	} );
	$gate = tc_fails( $root, 'surface_vocabulary', 'modules/demo/module.json' );
	tc_true( false !== strpos( $gate['detail'], 'cart.nowhere' ) && false !== strpos( $gate['detail'], 'component.teleporter' ), 'detail names both: ' . $gate['detail'] );
} );

tc_test( 'component_lookalike_warns', function () {
	$root = tc_good();
	tc_passes( $root, 'component_lookalike' );
	tc_write( $root, 'modules/demo/assets/demo.js', "const drawer = document.querySelector( '.bc-demo__drawer' );\n" );
	tc_fails( $root, 'component_lookalike', 'modules/demo/assets/demo.js', 'warn' );

	tc_manifest( $root, 'demo', function ( $m ) {
		$m['provides'] = [ 'component.drawer' ];
		return $m;
	} );
	tc_passes( $root, 'component_lookalike' );
} );

tc_test( 'client_denylist_names', function () {
	$root = tc_good();
	tc_passes( $root, 'client_denylist' );
	tc_write( $root, 'modules/demo/Module.php', str_replace( 'Fixture module.', 'Fixture module for AcmeClient.', (string) file_get_contents( "{$root}/modules/demo/Module.php" ) ) );
	tc_fails( $root, 'client_denylist', 'modules/demo/Module.php' );
} );

tc_test( 'client_denylist_ids_and_hex', function () {
	$root = tc_good();
	tc_write( $root, 'src/Core/Ids.php', "<?php\nnamespace Blaze\\Child\\Core;\n\ndefined( 'ABSPATH' ) || exit;\n\nfunction bc_x() {\n\treturn is_page( 4821 ) || get_permalink( 12345 );\n}\n" );
	tc_fails( $root, 'client_denylist', 'src/Core/Ids.php:7' );

	$root = tc_good();
	tc_write( $root, 'modules/demo/assets/demo.css', ":root {\n\t--bc-demo-accent: #ff0000;\n}\n.bc-demo__label {\n\tcolor: #a1b2c3;\n}\n" );
	tc_fails( $root, 'client_denylist', 'modules/demo/assets/demo.css:5' );
} );

tc_test( 'client_denylist_scans_descriptions_not_notes', function () {
	$root = tc_good();
	// Good fixture: the stub's notes mention a client name and the gate passes.
	tc_passes( $root, 'client_denylist' );
	tc_manifest( $root, 'old-feature', function ( $m ) {
		$m['description'] = 'ExampleStore shop tweaks.';
		return $m;
	} );
	tc_fails( $root, 'client_denylist', 'modules/old-feature/module.json' );
} );

tc_test( 'legacy_inc_exempt_from_denylist', function () {
	$root = tc_good();
	// inc/old.php holds a client name, a page ID, a hook closure and a jquery dep: v1 code is exempt.
	foreach ( [ 'client_denylist', 'no_hook_closures', 'no_jquery' ] as $code ) {
		tc_passes( $root, $code );
	}
	// The same code in a legacy (stub) module folder is exempt too; in a beta module it is not.
	tc_write( $root, 'modules/old-feature/notes.php', (string) file_get_contents( "{$root}/inc/old.php" ) );
	tc_passes( $root, 'client_denylist' );
	tc_write( $root, 'modules/demo/old.php', (string) file_get_contents( "{$root}/inc/old.php" ) );
	tc_fails( $root, 'client_denylist', 'modules/demo/old.php' );
} );

tc_test( 'no_jquery', function () {
	$root = tc_good();
	tc_passes( $root, 'no_jquery' );
	tc_manifest( $root, 'demo', function ( $m ) {
		$m['assets'][0]['deps'] = [ 'jquery' ];
		return $m;
	} );
	tc_fails( $root, 'no_jquery', 'modules/demo/module.json' );

	$root = tc_good();
	tc_write( $root, 'modules/demo/assets/demo.js', "jQuery( function ( $ ) { $( '.x' ).hide(); } );\n" );
	tc_fails( $root, 'no_jquery', 'modules/demo/assets/demo.js:1' );

	$root = tc_good();
	tc_write( $root, 'modules/demo/Enqueue.php', "<?php\nnamespace Blaze\\Child\\Modules\\Demo;\n\ndefined( 'ABSPATH' ) || exit;\n\nwp_enqueue_script( 'bc-demo', 'x.js', [ 'jquery' ], '1', true );\n" );
	tc_fails( $root, 'no_jquery', 'modules/demo/Enqueue.php:6' );
} );

tc_test( 'no_innerhtml_vars_warns', function () {
	$root = tc_good();
	tc_passes( $root, 'no_innerhtml_vars' );
	tc_write( $root, 'modules/demo/assets/demo.js', "const el = document.body;\nel.innerHTML = window.label;\n" );
	tc_fails( $root, 'no_innerhtml_vars', 'modules/demo/assets/demo.js:2', 'warn' );
} );

tc_test( 'no_hook_closures', function () {
	$root = tc_good();
	tc_passes( $root, 'no_hook_closures' );
	tc_write( $root, 'modules/demo/Hooks.php', "<?php\nnamespace Blaze\\Child\\Modules\\Demo;\n\ndefined( 'ABSPATH' ) || exit;\n\nadd_action( 'init', function () {\n} );\nadd_filter( 'the_title', static fn( \$t ) => \$t );\n" );
	$gate = tc_fails( $root, 'no_hook_closures', 'modules/demo/Hooks.php:6' );
	tc_true( in_array( 'modules/demo/Hooks.php:8', $gate['files'], true ), 'static arrow fn found: ' . json_encode( $gate['files'] ) );
} );

tc_test( 'no_strict_types', function () {
	$root = tc_good();
	tc_passes( $root, 'no_strict_types' );
	tc_write( $root, 'src/Core/Strict.php', "<?php\ndeclare(strict_types=1);\nnamespace Blaze\\Child\\Core;\n\ndefined( 'ABSPATH' ) || exit;\n" );
	tc_fails( $root, 'no_strict_types', 'src/Core/Strict.php:2' );
} );

tc_test( 'abspath_guard', function () {
	$root = tc_good();
	tc_passes( $root, 'abspath_guard' );
	tc_write( $root, 'src/Core/Open.php', "<?php\nnamespace Blaze\\Child\\Core;\n\nfinal class Open {}\n" );
	tc_fails( $root, 'abspath_guard', 'src/Core/Open.php' );

	$root = tc_good();
	tc_write( $root, 'modules/demo/Late.php', "<?php\nnamespace Blaze\\Child\\Modules\\Demo;\n\n\$x = 1;\ndefined( 'ABSPATH' ) || exit;\n" );
	tc_fails( $root, 'abspath_guard', 'modules/demo/Late.php' );
} );

tc_test( 'abspath_guard_accepts_docblock_and_use', function () {
	$root = tc_good();
	tc_write( $root, 'src/Core/Ok.php', "<?php\n/**\n * Doc.\n */\nnamespace Blaze\\Child\\Core;\n\nuse Blaze\\Child\\Core\\Thing;\n\ndefined( 'ABSPATH' ) || exit;\n" );
	tc_write( $root, 'src/plain.php', "<?php\n// Comment.\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n" );
	tc_passes( $root, 'abspath_guard' );
} );

tc_test( 'duplicate_symbols', function () {
	$root = tc_good();
	tc_passes( $root, 'duplicate_symbols' );
	tc_write( $root, 'inc/dup.php', "<?php\nfunction bc_dup_fn() {}\n" );
	tc_write( $root, 'modules/demo/helpers.php', "<?php\ndefined( 'ABSPATH' ) || exit;\n\nfunction BC_Dup_Fn() {}\n" );
	$gate = tc_fails( $root, 'duplicate_symbols', 'inc/dup.php:2' );
	tc_true( in_array( 'modules/demo/helpers.php:4', $gate['files'], true ), 'both declarations listed: ' . json_encode( $gate['files'] ) );
} );

tc_test( 'duplicate_symbols_classes', function () {
	$root = tc_good();
	tc_write( $root, 'inc/a.php', "<?php\nclass BC_Twice {}\n" );
	tc_write( $root, 'inc/b.php', "<?php\nfinal class bc_twice {\n\tpublic function boot() {}\n}\n" );
	tc_fails( $root, 'duplicate_symbols', 'inc/b.php:2' );
} );

tc_test( 'duplicate_symbols_guarded_namespaced_methods_tests', function () {
	$root = tc_good();
	// Guarded in inc/old.php already; a second guarded copy is fine.
	tc_write( $root, 'inc/again.php', "<?php\nif ( ! function_exists( 'bc_old_helper' ) ) {\n\tfunction bc_old_helper() {}\n}\n" );
	// Same short name in two namespaces is fine.
	tc_write( $root, 'src/A.php', "<?php\nnamespace Blaze\\Child\\A;\n\ndefined( 'ABSPATH' ) || exit;\n\nfunction run() {}\nclass Thing {}\n" );
	tc_write( $root, 'src/B.php', "<?php\nnamespace Blaze\\Child\\B;\n\ndefined( 'ABSPATH' ) || exit;\n\nfunction run() {}\n" );
	// Methods, closures and `use function` are not global declarations.
	tc_write( $root, 'inc/c.php', "<?php\nuse function strlen;\nclass BC_C {\n\tpublic function run() {}\n\tpublic function boot() { \$f = function () {}; }\n}\n\$y = new class { public function run() {} };\n" );
	// Duplicates under tests/ or fixtures/ are ignored.
	tc_write( $root, 'modules/demo/tests/a.php', "<?php\nfunction bc_old_helper() {}\n" );
	tc_write( $root, 'modules/demo/tests/fixtures/b.php', "<?php\nfunction bc_old_helper() {}\n" );
	tc_passes( $root, 'duplicate_symbols' );
} );

tc_test( 'dist_contents', function () {
	$root = tc_good();
	tc_passes( $root, 'dist_contents' );
	file_put_contents( "{$root}/.distignore", str_replace( "tests/\n", '', (string) file_get_contents( "{$root}/.distignore" ) ) );
	tc_fails( $root, 'dist_contents', 'tests/keep.txt' );
} );

tc_test( 'dist_contents_test_mjs', function () {
	$root = tc_good();
	file_put_contents( "{$root}/.distignore", str_replace( "*.test.mjs\n", '', (string) file_get_contents( "{$root}/.distignore" ) ) );
	tc_write( $root, 'modules/demo/assets/demo.test.mjs', "export {};\n" );
	tc_fails( $root, 'dist_contents', 'modules/demo/assets/demo.test.mjs' );
} );

tc_test( 'modules_md_fresh', function () {
	$root = tc_good();
	tc_passes( $root, 'modules_md_fresh' );
	tc_manifest( $root, 'demo', function ( $m ) {
		$m['title'] = 'Renamed demo';
		return $m;
	} );
	tc_fails( $root, 'modules_md_fresh', 'docs/MODULES.md' );
} );

tc_test( 'phpcs_and_phpstan_warn_when_not_installed', function () {
	// Outside CI (GitHub sets CI=true; see fix5 for the CI rule).
	$report = tc_with_ci( null, function () {
		return tc_run( tc_good(), null, [ 'phpcs', 'phpstan' ], [ 'path' => '' ] );
	} );
	foreach ( [ 'phpcs', 'phpstan' ] as $tool ) {
		$gate = tc_gate( $report, $tool );
		tc_same( [ $gate['severity'], $gate['passed'], $gate['detail'] ], [ 'warn', false, "{$tool} not installed" ], $tool );
	}
	tc_same( $report['passed'], true, 'warnings do not fail the run' );
} );

tc_test( 'phpcs_and_phpstan_fail_on_errors_when_present', function () {
	$root = tc_good();
	tc_write( $root, 'tools/phpcs.xml', "<?xml version=\"1.0\"?>\n<ruleset name=\"x\"/>\n" );
	tc_write( $root, 'tools/phpstan.neon', "parameters:\n  level: 5\n" );
	$abs   = realpath( $root ) . '/src/Core/Thing.php';
	$cs    = json_encode( [ 'totals' => [ 'errors' => 1, 'warnings' => 0 ], 'files' => [ $abs => [ 'errors' => 1, 'warnings' => 0, 'messages' => [ [ 'message' => 'Bad', 'line' => 11, 'type' => 'ERROR' ] ] ] ] ] );
	$stan  = json_encode( [ 'totals' => [ 'errors' => 0, 'file_errors' => 1 ], 'files' => [ $abs => [ 'errors' => 1, 'messages' => [ [ 'message' => 'Worse', 'line' => 15 ] ] ] ], 'errors' => [] ] );
	tc_write( $root, 'vendor/bin/phpcs', "#!/bin/sh\necho '{$cs}'\nexit 2\n" );
	tc_write( $root, 'vendor/bin/phpstan', "#!/bin/sh\necho '{$stan}'\nexit 1\n" );
	chmod( "{$root}/vendor/bin/phpcs", 0755 );
	chmod( "{$root}/vendor/bin/phpstan", 0755 );

	$report = tc_run( $root, null, [ 'phpcs', 'phpstan' ] );
	tc_same( tc_gate( $report, 'phpcs' )['severity'], 'fail', 'phpcs present: fail severity' );
	tc_same( tc_gate( $report, 'phpcs' )['files'], [ 'src/Core/Thing.php:11' ], 'phpcs file:line' );
	tc_same( tc_gate( $report, 'phpstan' )['files'], [ 'src/Core/Thing.php:15' ], 'phpstan file:line' );
	tc_same( $report['passed'], false, 'run fails' );

	tc_write( $root, 'vendor/bin/phpcs', "#!/bin/sh\necho '{\"totals\":{\"errors\":0,\"warnings\":3},\"files\":{}}'\nexit 1\n" );
	tc_same( tc_gate( tc_run( $root, null, [ 'phpcs' ] ), 'phpcs' )['passed'], true, 'warnings only: pass' );
} );

tc_test( 'module_flag_limits_scope', function () {
	$root = tc_good();
	// A violation outside the selected module (src/) must not show up with --module=demo.
	tc_write( $root, 'src/Core/Bad.php', "<?php\nnamespace Blaze\\Child\\Core;\n\nadd_action( 'init', function () {} ); // acmeclient\n" );
	$report = Runner::run( $root, 'demo', null, [ 'path' => '' ] );
	$codes  = array_column( $report['gates'], 'code' );
	foreach ( [ 'php_lint', 'version_agreement', 'surface_vocabulary', 'client_denylist', 'abspath_guard', 'no_hook_closures', 'class_name' ] as $code ) {
		tc_true( in_array( $code, $codes, true ), "{$code} runs with --module" );
	}
	foreach ( [ 'dist_contents', 'modules_md_fresh' ] as $code ) {
		tc_true( ! in_array( $code, $codes, true ), "{$code} does not run with --module" );
	}
	tc_same( $report['module'], 'demo', 'report module' );
	foreach ( [ 'client_denylist', 'abspath_guard', 'no_hook_closures' ] as $code ) {
		tc_same( tc_gate( $report, $code )['passed'], true, "{$code} ignores src/ with --module=demo" );
	}
	tc_same( tc_gate( Runner::run( $root, null, [ 'client_denylist' ], [ 'path' => '' ] ), 'client_denylist' )['passed'], false, 'but fails without --module' );

	// And a violation inside the module still fails.
	tc_write( $root, 'modules/demo/Bad.php', "<?php\ndefined( 'ABSPATH' ) || exit;\n// examplestore\n" );
	tc_same( tc_gate( Runner::run( $root, 'demo', null, [ 'path' => '' ] ), 'client_denylist' )['passed'], false, 'module violation fails' );
} );

tc_test( 'module_flag_unknown_module_fails', function () {
	$report = Runner::run( tc_good(), 'nope', null, [ 'path' => '' ] );
	$gate   = tc_gate( $report, 'module_exists' );
	tc_same( [ $gate['severity'], $gate['passed'] ], [ 'fail', false ], 'module_exists' );
	tc_same( $report['passed'], false, 'run fails' );
} );

tc_test( 'json_shape_matches_contract', function () {
	$root = tc_good();
	$out  = tc_cli( [ BC_TC_REPO . '/tools/check.php', '--json', '--root=' . $root ], $code );
	$json = json_decode( $out, true );
	tc_true( is_array( $json ), 'stdout is only JSON: ' . substr( $out, 0, 200 ) );
	tc_same( array_keys( $json ), [ 'version', 'root', 'module', 'gates', 'passed' ], 'top-level keys' );
	tc_same( $json['version'], '9.9.9', 'version' );
	tc_same( $json['root'], realpath( $root ), 'root' );
	tc_same( $json['module'], null, 'module null' );
	tc_same( $json['passed'], true, 'passed' );
	tc_same( $code, 0, 'exit 0 when passed' );
	foreach ( $json['gates'] as $gate ) {
		tc_same( array_keys( $gate ), [ 'code', 'severity', 'passed', 'detail', 'files' ], 'gate keys' );
		tc_true( in_array( $gate['severity'], [ 'fail', 'warn' ], true ) && is_bool( $gate['passed'] ) && is_string( $gate['detail'] ) && is_array( $gate['files'] ), 'gate types' );
	}
	tc_same( array_column( $json['gates'], 'code' ), Runner::CODES, 'all codes, contract order' );

	tc_write( $root, 'src/Core/Strict.php', "<?php\ndeclare(strict_types=1);\n" );
	$json = json_decode( tc_cli( [ BC_TC_REPO . '/tools/check.php', '--json', '--root=' . $root, '--module=demo' ], $code ), true );
	tc_same( [ $json['module'], $json['passed'], $code ], [ 'demo', true, 0 ], '--module json' );
	$json = json_decode( tc_cli( [ BC_TC_REPO . '/tools/check.php', '--json', '--root=' . $root ], $code ), true );
	tc_same( [ $json['passed'], $code ], [ false, 1 ], 'exit 1 when a fail gate fails' );
} );

tc_test( 'build_writes_zip_with_root_and_manifest', function () {
	$root = tc_good();
	$out  = $root . '/../' . basename( $root ) . '-out';
	$GLOBALS['tc_tmp'][] = $out;
	tc_cli( [ BC_TC_REPO . '/tools/build.php', '--root=' . $root, '--out=' . $out ], $code );
	tc_same( $code, 0, 'build exit 0' );
	$zip = new \ZipArchive();
	tc_same( $zip->open( $out . '/blocksy-child.zip' ), true, 'zip opens' );
	$names = [];
	for ( $i = 0; $i < $zip->numFiles; $i++ ) {
		$names[] = $zip->getNameIndex( $i );
	}
	foreach ( $names as $name ) {
		tc_true( 0 === strpos( $name, 'blocksy-child/' ), "root folder: {$name}" );
		tc_true( ! preg_match( '#^blocksy-child/(tests|docs|tools)/#', $name ), "forbidden: {$name}" );
	}
	$manifest = json_decode( (string) $zip->getFromName( 'blocksy-child/.blaze-manifest.json' ), true );
	$shipped  = array_values( array_diff( array_map( function ( $n ) {
		return substr( $n, strlen( 'blocksy-child/' ) );
	}, $names ), [ '.blaze-manifest.json' ] ) );
	sort( $shipped );
	tc_same( $manifest, $shipped, 'manifest lists exactly the shipped files, sorted' );
	tc_true( in_array( 'style.css', $manifest, true ) && in_array( 'modules/demo/Module.php', $manifest, true ), 'ships theme files' );
	$zip->close();
} );

tc_test( 'gen_modules_md_is_deterministic_and_checks', function () {
	$root = tc_good();
	tc_cli( [ BC_TC_REPO . '/tools/gen-modules-md.php', '--root=' . $root, '--check' ], $code );
	tc_same( $code, 0, 'fixture MODULES.md is fresh' );
	$md = (string) file_get_contents( "{$root}/docs/MODULES.md" );
	tc_true( false !== strpos( $md, '2 modules: 0 stable, 1 beta, 0 deprecated, 1 legacy, 0 planned' ), 'counts line' );
	tc_true( false !== strpos( $md, '| `demo` | Demo module | `cart.page` | someone | off |' ), 'demo row' );

	unlink( "{$root}/docs/MODULES.md" );
	tc_cli( [ BC_TC_REPO . '/tools/gen-modules-md.php', '--root=' . $root, '--check' ], $code );
	tc_same( $code, 1, '--check exits 1 when missing' );
	tc_cli( [ BC_TC_REPO . '/tools/gen-modules-md.php', '--root=' . $root ], $code );
	tc_same( [ $code, (string) file_get_contents( "{$root}/docs/MODULES.md" ) ], [ 0, $md ], 'regenerates byte-identical' );
} );

// --- Fix round 1 -----------------------------------------------------------------------------------------------

/** Runs $fn with the CI env var set to $value (null = unset), then restores it. */
function tc_with_ci( ?string $value, callable $fn ) {
	$old = getenv( 'CI' );
	putenv( null === $value ? 'CI' : "CI={$value}" );
	try {
		return $fn();
	} finally {
		putenv( false === $old ? 'CI' : "CI={$old}" );
	}
}

tc_test( 'fix1_no_hook_closures_fully_qualified_call', function () {
	$root = tc_good();
	tc_write( $root, 'modules/demo/Fq.php', "<?php\nnamespace Blaze\\Child\\Modules\\Demo;\n\ndefined( 'ABSPATH' ) || exit;\n\n\\add_filter( 'the_title', function ( \$t ) {\n\treturn \$t;\n} );\n" );
	tc_fails( $root, 'no_hook_closures', 'modules/demo/Fq.php:6' );
} );

tc_test( 'fix1_no_jquery_fully_qualified_call', function () {
	$root = tc_good();
	tc_write( $root, 'modules/demo/Fq.php', "<?php\nnamespace Blaze\\Child\\Modules\\Demo;\n\ndefined( 'ABSPATH' ) || exit;\n\n\\wp_enqueue_script( 'bc-demo', 'x.js', [ 'jquery' ], '1', true );\n" );
	tc_fails( $root, 'no_jquery', 'modules/demo/Fq.php:6' );
} );

tc_test( 'fix2_no_hook_closures_interpolated_hook_name', function () {
	$root = tc_good();
	tc_write( $root, 'modules/demo/Interp.php', "<?php\nnamespace Blaze\\Child\\Modules\\Demo;\n\ndefined( 'ABSPATH' ) || exit;\n\n\$id = 'demo';\nadd_filter( \"blaze_child/{\$id}/title\", function ( \$t ) {\n\treturn \$t;\n} );\nadd_filter( \"blaze_child/\${id}/label\", static fn( \$t ) => \$t );\n" );
	$gate = tc_fails( $root, 'no_hook_closures', 'modules/demo/Interp.php:7' );
	tc_true( in_array( 'modules/demo/Interp.php:10', $gate['files'], true ), '${} form found: ' . json_encode( $gate['files'] ) );
} );

tc_test( 'fix3_fixtures_outside_tests_are_checked_and_never_ship', function () {
	$root = tc_good();
	tc_write( $root, 'src/fixtures/Evil.php', "<?php\nfunction (\n" );
	tc_fails( $root, 'php_lint', 'src/fixtures/Evil.php' );

	$root = tc_good();
	tc_write( $root, 'src/fixtures/Evil.php', "<?php\nnamespace Blaze\\Child\\Core;\n\nadd_action( 'init', function () {} ); // acmeclient\n" );
	tc_fails( $root, 'no_hook_closures', 'src/fixtures/Evil.php:4' );
	tc_fails( $root, 'client_denylist', 'src/fixtures/Evil.php:4' );
	tc_fails( $root, 'abspath_guard', 'src/fixtures/Evil.php' );
	// The fixture's .distignore has no fixtures/ line: dist_contents still refuses to ship it.
	tc_fails( $root, 'dist_contents', 'src/fixtures/Evil.php' );

	// tests/**/fixtures/ stays exempt.
	$root = tc_good();
	tc_write( $root, 'tests/x/fixtures/broken.php', "<?php\nfunction (\n" );
	tc_passes( $root, 'php_lint' );
} );

tc_test( 'fix4_hex_exempt_only_for_exact_root_selector', function () {
	$root = tc_good();
	tc_write( $root, 'modules/demo/assets/demo.css', ":root[data-theme=\"dark\"] {\n\t--bc-demo-accent: #000;\n}\n:root:not(.x) {\n\t--bc-demo-bg: #fff;\n}\n" );
	tc_passes( $root, 'client_denylist' );
	tc_write( $root, 'modules/demo/assets/demo.css', ".bc-demo__label, :root {\n\tcolor: #a1b2c3;\n}\n" );
	tc_fails( $root, 'client_denylist', 'modules/demo/assets/demo.css:2' );
} );

tc_test( 'fix5_missing_tools_fail_in_ci', function () {
	$report = tc_with_ci( 'true', function () {
		return tc_run( tc_good(), null, [ 'phpcs', 'phpstan' ], [ 'path' => '' ] );
	} );
	foreach ( [ 'phpcs', 'phpstan' ] as $tool ) {
		$gate = tc_gate( $report, $tool );
		tc_same( [ $gate['severity'], $gate['passed'], $gate['detail'] ], [ 'fail', false, "{$tool} not installed" ], $tool );
	}
	tc_same( $report['passed'], false, 'CI run fails' );
	$local = tc_with_ci( null, function () {
		return tc_run( tc_good(), null, [ 'phpcs' ], [ 'path' => '' ] );
	} );
	tc_same( tc_gate( $local, 'phpcs' )['severity'], 'warn', 'outside CI: warn' );
} );

tc_test( 'fix6_build_never_ships_secrets_or_dev_files', function () {
	$root    = tc_good();
	$secrets = [ '.env', '.env.local', 'project-config.json', '.idea/x.xml', '.vscode/settings.json', 'debug.log', 'vendor/autoload.php', 'node_modules/x/index.js', 'modules/demo/.git' ];
	foreach ( $secrets as $rel ) {
		tc_write( $root, $rel, "x\n" );
	}
	$shipped = \Blaze\Child\Tools\Dist::shipped_files( $root );
	foreach ( $secrets as $rel ) {
		tc_true( ! in_array( $rel, $shipped, true ), "{$rel} must not ship" );
	}
	tc_true( in_array( 'style.css', $shipped, true ), 'theme still ships' );
	tc_passes( $root, 'dist_contents' );
} );

tc_test( 'fix7_json_report_survives_bad_utf8_and_encode_failure', function () {
	$report = [
		'version' => '9.9.9',
		'root'    => '/x',
		'module'  => null,
		'gates'   => [ \Blaze\Child\Tools\Context::gate( 'php_lint', 'fail', false, "bad \xB1\x31 name", [ "src/\xFF.php:1" ] ) ],
		'passed'  => false,
	];
	$json = json_decode( \Blaze\Child\Tools\Cli::encode_report( $report ), true );
	tc_true( is_array( $json ) && 'php_lint' === $json['gates'][0]['code'], 'non-UTF-8 detail still encodes' );
	tc_true( false !== strpos( $json['gates'][0]['detail'], "\u{FFFD}" ), 'invalid bytes substituted' );

	$report['gates'][0]['detail'] = NAN;
	$json                         = json_decode( \Blaze\Child\Tools\Cli::encode_report( $report ), true );
	tc_same( array_keys( $json ), [ 'version', 'root', 'module', 'gates', 'passed' ], 'fallback keeps the contract keys' );
	tc_same( [ $json['passed'], $json['gates'][0]['code'], $json['gates'][0]['passed'] ], [ false, 'report_encoding', false ], 'fallback fails' );
} );

tc_test( 'fix8_denylist_ids_quoted_and_in_arrays', function () {
	foreach ( [ "is_page( '4821' )", 'is_page( "4821" )', 'is_page( [ 4821, 12 ] )', 'get_post( array( 4821 ) )', "IS_PAGE( ['4821'] )" ] as $call ) {
		$root = tc_good();
		tc_write( $root, 'src/Core/Ids.php', "<?php\nnamespace Blaze\\Child\\Core;\n\ndefined( 'ABSPATH' ) || exit;\n\nfunction bc_x() {\n\treturn {$call};\n}\n" );
		tc_fails( $root, 'client_denylist', 'src/Core/Ids.php:7' );
	}
} );

foreach ( $GLOBALS['tc_tmp'] as $dir ) {
	tc_rm( $dir );
}

printf( "\n%d passed, %d failed\n", $GLOBALS['tc_results']['pass'], $GLOBALS['tc_results']['fail'] );

exit( $GLOBALS['tc_results']['fail'] > 0 ? 1 : 0 );
