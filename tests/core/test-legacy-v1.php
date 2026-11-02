<?php
/**
 * Task 5: legacy-v1 module, skip list, catalogue completeness, surfaces vocabulary.
 *
 * @package Blocksy_Child
 */

function bc_lv1_root(): string {
	return dirname( __DIR__, 2 );
}

/**
 * Builds a temp theme + site and boots the real bootstrap in a subprocess.
 *
 * @param array  $theme_files Relative path (under the theme) => contents.
 * @param string $config      Body of site-config.php (array literal), or '' for none.
 * @param string $body        PHP run after boot (echoes JSON).
 * @param array  $consts      Constants defined before boot.
 * @param array  $site_files  Relative path (under blaze-site/) => contents.
 * @return array
 */
function bc_lv1_boot( array $theme_files, string $config, string $body, array $consts = [], array $site_files = [] ): array {
	$root  = var_export( bc_lv1_root(), true );
	$base  = sys_get_temp_dir() . '/bc_lv1_' . uniqid();
	$theme = $base . '/theme';
	$site  = $base . '/content/blaze-site';

	// The real legacy-v1 module, plus fixture files.
	foreach ( [ 'module.json', 'Module.php' ] as $f ) {
		$theme_files[ 'modules/legacy-v1/' . $f ] = file_get_contents( bc_lv1_root() . '/modules/legacy-v1/' . $f );
	}
	if ( '' !== $config ) {
		$site_files['site-config.php'] = "<?php defined( 'ABSPATH' ) || exit; return {$config};";
	}
	foreach ( $theme_files as $rel => $content ) {
		@mkdir( dirname( "$theme/$rel" ), 0777, true );
		file_put_contents( "$theme/$rel", $content );
	}
	@mkdir( $site, 0777, true );
	foreach ( $site_files as $rel => $content ) {
		@mkdir( dirname( "$site/$rel" ), 0777, true );
		file_put_contents( "$site/$rel", $content );
	}

	$prefix = "ini_set( 'display_errors', '1' );\n"
		. "define( 'BC_TEST_NO_THEME_CONSTANTS', true );\n"
		. "define( 'BLAZE_CHILD_PATH', " . var_export( $theme . '/', true ) . " );\n"
		. "define( 'WP_CONTENT_DIR', " . var_export( $base . '/content', true ) . " );\n";
	foreach ( $consts as $name => $value ) {
		$prefix .= 'define( ' . var_export( $name, true ) . ', ' . var_export( $value, true ) . " );\n";
	}
	$prefix .= "require {$root} . '/tests/core/stubs.php';\n"
		. "spl_autoload_register( function ( \$c ) {\n"
		. "  \$p = 'Blaze\\\\Child\\\\';\n"
		. "  if ( 0 === strpos( \$c, \$p ) && 0 !== strpos( \$c, \$p . 'Modules\\\\' ) ) {\n"
		. "    \$f = {$root} . '/src/' . str_replace( '\\\\', '/', substr( \$c, strlen( \$p ) ) ) . '.php';\n"
		. "    if ( is_file( \$f ) ) { require_once \$f; }\n"
		. "  }\n"
		. "} );\n"
		. "require {$root} . '/src/bootstrap.php';\n"
		. "use Blaze\\Child\\Core\\Health;\n"
		. "use Blaze\\Child\\Core\\Registry;\n"
		. "use Blaze\\Child\\Core\\SiteLayer;\n";
	return bc_test_run_isolated( $prefix . $body );
}

const BC_LV1_LOG = "echo json_encode( [ 'order' => \$GLOBALS['bc_order'] ?? [], 'skip' => \$GLOBALS['bc_skip'] ?? null, 'codes' => array_column( Health::all(), 'code' ) ] );";

/** Fixture inc/ that appends to the order log. */
function bc_lv1_inc_fixture(): array {
	return [
		'inc/loader.php'      => "<?php defined( 'ABSPATH' ) || exit; \$GLOBALS['bc_order'][] = 'loader'; \$GLOBALS['bc_skip'] = apply_filters( 'blaze_child/legacy_v1/skip_files', [] );",
		'inc/perf/loader.php' => "<?php defined( 'ABSPATH' ) || exit; \$GLOBALS['bc_order'][] = 'perf';",
	];
}

function bc_lv1_manifests(): array {
	$out = [];
	foreach ( glob( bc_lv1_root() . '/modules/*/module.json' ) as $file ) {
		$out[ basename( dirname( $file ) ) ] = json_decode( file_get_contents( $file ), true );
	}
	return $out;
}

bc_test( 'legacy_v1_loads_v1_in_v1_order', function () {
	$site = [ 'site.php' => "<?php defined( 'ABSPATH' ) || exit; \$GLOBALS['bc_order'][] = 'site';" ];
	$out  = bc_lv1_boot( bc_lv1_inc_fixture(), "[ 'modules' => [ 'legacy-v1' => true ] ]", BC_LV1_LOG, [], $site );
	assert_same( $out['order'], [ 'loader', 'site', 'perf' ], 'v1 functions.php order: loader -> custom/site -> perf' );

	// Off by default: nothing of v1 runs.
	$out = bc_lv1_boot( bc_lv1_inc_fixture(), "[ 'modules' => [] ]", BC_LV1_LOG, [], $site );
	assert_same( $out['order'], [ 'site' ], 'default off' );

	// Kill switch.
	$out = bc_lv1_boot( bc_lv1_inc_fixture(), "[ 'modules' => [ 'legacy-v1' => true ] ]", BC_LV1_LOG, [ 'BLAZE_CHILD_DISABLE_MODULES' => 'legacy-v1' ], $site );
	assert_same( $out['order'], [ 'site' ], 'kill switch' );
} );

bc_test( 'legacy_v1_core_not_loaded_when_config_failed', function () {
	$out = bc_lv1_boot( bc_lv1_inc_fixture(), '', BC_LV1_LOG, [], [
		'site-config.php' => "<?php defined( 'ABSPATH' ) || exit; throw new \\RuntimeException( 'boom' );",
		'site.php'        => "<?php defined( 'ABSPATH' ) || exit; \$GLOBALS['bc_order'][] = 'site';",
	] );
	assert_same( $out['order'], [ 'site' ], 'R11: broken config loads no v1, not even the loader; site code still loads (R10)' );
	assert_same( in_array( 'site_config_invalid', $out['codes'], true ), true );
} );

bc_test( 'skip_files_is_union_of_enabled_non_legacy_modules', function () {
	$dir = sys_get_temp_dir() . '/bc_lv1_sf_' . uniqid();
	$mk  = function ( string $id, array $extra ) use ( $dir ) {
		@mkdir( "$dir/$id", 0777, true );
		file_put_contents( "$dir/$id/module.json", json_encode( array_merge( [ 'id' => $id, 'loadable' => true, 'default' => false, 'legacy' => [ 'files' => [] ] ], $extra ) ) );
	};
	$mk( 'legacy-v1', [ 'legacy' => [ 'files' => [ 'inc/loader.php' ] ] ] );
	$mk( 'port-a', [ 'legacy' => [ 'files' => [ 'inc/a.php', 'inc/b.php' ] ] ] );
	$mk( 'port-b', [ 'legacy' => [ 'files' => [ 'inc/b.php', 'inc/c.php' ] ] ] );
	$mk( 'port-default-on', [ 'default' => true, 'legacy' => [ 'files' => [ 'inc/d.php' ] ] ] );
	$mk( 'stub-x', [ 'loadable' => false, 'status' => 'legacy', 'legacy' => [ 'files' => [ 'inc/x.php' ] ] ] );
	$mk( 'plain', [] );

	$registry = new \Blaze\Child\Core\Registry( $dir );
	assert_same( $registry->skip_files( [] ), [ 'inc/d.php' ], 'default-on module counts; legacy-v1 and stubs never' );
	assert_same( $registry->skip_files( [ 'port-a' => true, 'port-b' => true, 'legacy-v1' => true, 'stub-x' => true, 'plain' => true ] ), [ 'inc/a.php', 'inc/b.php', 'inc/c.php', 'inc/d.php' ], 'union, de-duplicated, stub ignored' );
	assert_same( $registry->skip_files( [ 'port-a' => false, 'port-default-on' => false ] ), [], 'config off wins over default' );
} );

bc_test( 'skip_files_honours_kill_switch', function () {
	$out = bc_lv1_boot(
		[
			'modules/port-a/module.json' => json_encode( [ 'id' => 'port-a', 'loadable' => true, 'default' => false, 'legacy' => [ 'files' => [ 'inc/a.php' ] ] ] ),
			'modules/port-a/Module.php'  => "<?php namespace Blaze\\Child\\Modules\\PortA; defined( 'ABSPATH' ) || exit; class Module extends \\Blaze\\Child\\Core\\Module { public function boot(): void {} }",
		] + bc_lv1_inc_fixture(),
		"[ 'modules' => [ 'legacy-v1' => true, 'port-a' => true ] ]",
		BC_LV1_LOG,
		[ 'BLAZE_CHILD_DISABLE_MODULES' => 'port-a' ]
	);
	assert_same( $out['skip'], [], 'a killed module skips nothing: the v1 file keeps loading' );
} );

bc_test( 'theme_adds_skip_files_before_v1_core', function () {
	$out = bc_lv1_boot(
		[
			'modules/port-a/module.json' => json_encode( [ 'id' => 'port-a', 'loadable' => true, 'default' => false, 'legacy' => [ 'files' => [ 'inc/a.php' ] ] ] ),
			'modules/port-a/Module.php'  => "<?php namespace Blaze\\Child\\Modules\\PortA; defined( 'ABSPATH' ) || exit; class Module extends \\Blaze\\Child\\Core\\Module { public function boot(): void {} }",
		] + bc_lv1_inc_fixture(),
		"[ 'modules' => [ 'legacy-v1' => true, 'port-a' => true ] ]",
		"echo json_encode( [ 'skip' => \$GLOBALS['bc_skip'], 'cb' => \$GLOBALS['bc_test_hooks']['blaze_child/legacy_v1/skip_files'][10][0] ] );"
	);
	assert_same( $out['skip'], [ 'inc/a.php' ], 'filter already answers when inc/loader.php runs' );
	assert_same( $out['cb'], [ 'Blaze\\Child\\Core\\Theme', 'filter_skip_files' ], 'named static callback, no closure' );
} );

bc_test( 'skip_list_skips_replaced_file', function () {
	// The real inc/loader.php against a marker theme (no test bootstrap: it stubs blocksy_child_load_module).
	$real = file_get_contents( bc_lv1_root() . '/inc/loader.php' );
	preg_match_all( "#'(inc/[a-z-]+\\.php)'#", $real, $m );
	$base = sys_get_temp_dir() . '/bc_lv1_skip_' . uniqid();
	@mkdir( $base . '/inc', 0777, true );
	file_put_contents( $base . '/inc/loader.php', $real );
	$files = array_values( array_unique( $m[1] ) );
	foreach ( $files as $f ) {
		file_put_contents( "$base/$f", "<?php defined( 'ABSPATH' ) || exit; \$GLOBALS['loaded'][] = " . var_export( $f, true ) . ';'
			. ( 'inc/helpers.php' === $f ? ' function blocksy_child_is_plugin_active( $p ) { return true; }' : '' ) );
	}

	$run = function ( array $skip ) use ( $base ) {
		return bc_test_run_isolated(
			"define( 'ABSPATH', '/' );\nini_set( 'error_log', '/dev/null' );\n"
			. "define( 'BLOCKSY_CHILD_PATH', " . var_export( $base . '/', true ) . " );\n"
			. "\$GLOBALS['loaded'] = [];\n"
			. "function apply_filters( \$h, \$v ) { return array_merge( \$v, " . var_export( $skip, true ) . " ); }\n"
			. "require BLOCKSY_CHILD_PATH . 'inc/loader.php';\n"
			. "echo json_encode( \$GLOBALS['loaded'] );"
		);
	};

	$all = $run( [] );
	assert_same( in_array( 'inc/hide-cart-page.php', $all, true ), true, 'baseline loads hide-cart-page' );
	assert_same( in_array( 'inc/carousel.php', $all, true ), true, 'baseline loads an always-loaded file' );
	assert_same( count( $all ), count( $files ), 'baseline loads every file the loader names' );

	$some = $run( [ 'inc/hide-cart-page.php', 'inc/carousel.php' ] );
	assert_same( in_array( 'inc/hide-cart-page.php', $some, true ), false, 'optional file skipped' );
	assert_same( in_array( 'inc/carousel.php', $some, true ), false, 'always-loaded file skipped' );
	assert_same( count( $some ), count( $all ) - 2, 'only those two skipped' );
} );

bc_test( 'every_inc_file_is_catalogued', function () {
	$manifests = bc_lv1_manifests();
	$claimed   = [];
	foreach ( $manifests as $id => $m ) {
		foreach ( (array) ( $m['legacy']['files'] ?? [] ) as $f ) {
			$claimed[ $f ][] = $id;
		}
	}
	$root = bc_lv1_root();
	$real = array_merge(
		array_map( function ( $p ) use ( $root ) { return substr( $p, strlen( $root ) + 1 ); }, glob( $root . '/inc/*.php' ) ),
		array_map( function ( $p ) use ( $root ) { return substr( $p, strlen( $root ) + 1 ); }, glob( $root . '/inc/perf/*.php' ) )
	);
	foreach ( $real as $f ) {
		assert_same( count( $claimed[ $f ] ?? [] ), 1, "{$f} must appear in exactly one manifest (got " . implode( ',', $claimed[ $f ] ?? [] ) . ')' );
	}
	foreach ( array_keys( $claimed ) as $f ) {
		assert_same( is_file( $root . '/' . $f ), true, "legacy.files names a missing file: {$f}" );
	}

	$core = [ 'inc/loader.php', 'inc/helpers.php', 'inc/enqueue.php', 'inc/hooks.php', 'inc/icons.php', 'inc/perf/loader.php', 'inc/perf/helpers.php' ];
	foreach ( $core as $f ) {
		assert_same( $claimed[ $f ] ?? [], [ 'legacy-v1' ], "{$f} belongs to legacy-v1" );
	}
} );

bc_test( 'stubs_not_loadable', function () {
	$manifests = bc_lv1_manifests();
	assert_same( isset( $manifests['legacy-v1'] ), true );
	foreach ( $manifests as $id => $m ) {
		assert_same( $m['id'], $id, "{$id}: id equals folder" );
		assert_same( 1 === preg_match( \Blaze\Child\Core\Registry::ID_PATTERN, $id ), true, "{$id}: id pattern" );
		assert_same( $m['default'], false, "{$id}: every module defaults off" );
		if ( 'legacy-v1' === $id ) {
			assert_same( $m['loadable'], true );
			assert_same( is_file( bc_lv1_root() . '/modules/legacy-v1/Module.php' ), true );
			continue;
		}
		if ( 'stable' === $m['status'] ) {
			// A real port replaced its stub in place: loadable, with PHP, still defaulting off.
			assert_same( $m['loadable'], true, "{$id}: a port is loadable" );
			assert_same( is_file( bc_lv1_root() . "/modules/{$id}/Module.php" ), true, "{$id}: a port has Module.php" );
			continue;
		}
		assert_same( in_array( $m['status'], [ 'legacy', 'planned' ], true ), true, "{$id}: status legacy|planned" );
		assert_same( $m['loadable'], false, "{$id}: loadable false" );
		assert_same( is_file( bc_lv1_root() . "/modules/{$id}/Module.php" ), false, "{$id}: a stub has no PHP" );
		foreach ( [ 'title', 'description', 'owner', 'since', 'surfaces', 'legacy' ] as $key ) {
			assert_same( array_key_exists( $key, $m ), true, "{$id}: has {$key}" );
		}
		assert_same( '' !== $m['title'] && '' !== $m['description'], true, "{$id}: title and description" );
		assert_same( $m['owner'], '', "{$id}: owner empty" );
		assert_same( $m['since'], '1.x', "{$id}: since 1.x" );
		assert_same( isset( $m['legacy']['files'] ), true, "{$id}: legacy.files" );
		if ( 'planned' === $m['status'] ) {
			assert_same( $m['legacy']['files'], [], "{$id}: planned stubs have no v1 files" );
			assert_same( '' !== ( $m['notes'] ?? '' ), true, "{$id}: planned stub notes name the legacy path" );
		} else {
			assert_same( count( $m['legacy']['files'] ) >= 1, true, "{$id}: legacy stub names its v1 file" );
		}
	}
	foreach ( [ 'my-account', 'thank-you', 'upsell' ] as $id ) {
		assert_same( $manifests[ $id ]['status'] ?? null, 'planned', "{$id} planned" );
	}
} );

bc_test( 'stub_surfaces_follow_r3', function () {
	$m = bc_lv1_manifests();
	assert_same( $m['hide-cart-page']['surfaces'], [ 'cart.page' ] );
	assert_same( $m['offcanvas-icons']['surfaces'], [ 'drawer.cart.icon', 'drawer.wishlist.icon' ] );
	assert_same( $m['offcanvas-icons']['provides'], [ 'component.icons' ] );
	assert_same( $m['recently-viewed']['surfaces'], [ 'pdp.recently-viewed' ] );
	assert_same( $m['perf-lcp-image']['surfaces'], [ 'perf.lcp-image' ] );
} );

bc_test( 'every_surface_is_in_vocabulary', function () {
	$vocab = json_decode( file_get_contents( bc_lv1_root() . '/tools/surfaces.json' ), true );
	assert_same( is_array( $vocab['surfaces'] ?? null ) && is_array( $vocab['components'] ?? null ), true, 'shape' );
	assert_same( $vocab['components'], [ 'component.drawer', 'component.carousel', 'component.icons' ] );
	foreach ( $vocab['surfaces'] as $name => $desc ) {
		assert_same( is_string( $desc ) && '' !== $desc, true, "{$name}: one-line description" );
	}
	$owners = [];
	foreach ( bc_lv1_manifests() as $id => $m ) {
		foreach ( (array) ( $m['surfaces'] ?? [] ) as $s ) {
			assert_same( isset( $vocab['surfaces'][ $s ] ), true, "{$id}: surface '{$s}' is not in tools/surfaces.json" );
			$owners[ $s ][] = $id;
		}
		foreach ( array_merge( (array) ( $m['provides'] ?? [] ), (array) ( $m['uses'] ?? [] ) ) as $c ) {
			assert_same( in_array( $c, $vocab['components'], true ), true, "{$id}: component '{$c}' not in vocabulary" );
		}
	}
	foreach ( $owners as $s => $ids ) {
		assert_same( count( $ids ), 1, "surface {$s} declared by more than one module: " . implode( ',', $ids ) );
	}
	foreach ( array_keys( $vocab['surfaces'] ) as $s ) {
		assert_same( isset( $owners[ $s ] ), true, "vocabulary surface '{$s}' is used by no module" );
	}
} );

bc_test( 'no_client_names_in_catalogue', function () {
	// R27: the names are private; read them from the same source as the client_denylist gate.
	require_once bc_lv1_root() . '/tools/lib/gates-denylist.php';
	$names = \Blaze\Child\Tools\DenylistGates::client_names();
	if ( null === $names['source'] ) {
		echo "SKIP: no_client_names_in_catalogue -- client names not checked: {$names['reason']}\n";
		return;
	}
	$hits = [];
	foreach ( array_merge( glob( bc_lv1_root() . '/modules/*/*' ), [ bc_lv1_root() . '/tools/surfaces.json' ] ) as $f ) {
		if ( ! is_file( $f ) ) {
			continue;
		}
		$text = strtolower( (string) file_get_contents( $f ) );
		foreach ( $names['tokens'] as $token ) {
			if ( false !== strpos( $text, $token ) ) {
				$hits[] = $f;
				break;
			}
		}
	}
	// File paths only: the names never reach the output.
	assert_same( $hits, [], "client names found (names from {$names['source']})" );
} );

/** Port fixture: manifest + a Module whose boot() does $boot_body. */
function bc_lv1_port( string $id, string $studly, array $manifest, string $boot_body = '' ): array {
	return [
		"modules/{$id}/module.json" => json_encode( array_merge( [ 'id' => $id, 'loadable' => true, 'default' => false, 'legacy' => [ 'files' => [ "inc/{$id}.php" ] ] ], $manifest ) ),
		"modules/{$id}/Module.php"  => "<?php namespace Blaze\\Child\\Modules\\{$studly}; defined( 'ABSPATH' ) || exit; class Module extends \\Blaze\\Child\\Core\\Module { public function boot(): void { {$boot_body} } }",
	];
}

const BC_LV1_ORPHANS = "echo json_encode( [ 'codes' => array_column( Health::all(), 'code' ), 'orphans' => array_values( array_map( function ( \$e ) { return \$e['context']; }, array_filter( Health::all(), function ( \$e ) { return 'legacy_files_orphaned' === \$e['code']; } ) ) ), 'messages' => array_column( Health::all(), 'message' ) ] );";

bc_test( 'orphaned_legacy_files_recorded_when_port_does_not_boot', function () {
	$cfg = "[ 'modules' => [ 'legacy-v1' => true, 'port-a' => true ] ]";

	// Booted fine: no orphan.
	$out = bc_lv1_boot( bc_lv1_port( 'port-a', 'PortA', [] ) + bc_lv1_inc_fixture(), $cfg, BC_LV1_ORPHANS );
	assert_same( $out['orphans'], [], 'a booted port orphans nothing' );

	// requires unmet (WooCommerce class absent).
	$out = bc_lv1_boot( bc_lv1_port( 'port-a', 'PortA', [ 'requires' => [ 'plugins' => [ 'woocommerce' => '>=9.0' ] ] ] ) + bc_lv1_inc_fixture(), $cfg, BC_LV1_ORPHANS );
	assert_same( $out['orphans'], [ [ 'module' => 'port-a', 'files' => [ 'inc/port-a.php' ] ] ], 'requires_unmet' );
	assert_same( in_array( 'requires_unmet', $out['codes'], true ), true, 'the existing health entry stays' );
	assert_same( 1 === preg_match( '#port-a.*inc/port-a\\.php#', implode( ' ', $out['messages'] ) ), true, 'a message names the module and the skipped file' );

	// Threw in boot().
	$out = bc_lv1_boot( bc_lv1_port( 'port-a', 'PortA', [], "throw new \\RuntimeException( 'boom' );" ) + bc_lv1_inc_fixture(), $cfg, BC_LV1_ORPHANS );
	assert_same( $out['orphans'], [ [ 'module' => 'port-a', 'files' => [ 'inc/port-a.php' ] ] ], 'threw' );
	assert_same( in_array( 'module_failed', $out['codes'], true ), true );

	// Filtered off by site code (the filter may switch a module off even though the skip list was computed on).
	$site = [ 'site.php' => "<?php defined( 'ABSPATH' ) || exit; add_filter( 'blaze_child/module_enabled', function ( \$on, \$id ) { return 'port-a' === \$id ? false : \$on; }, 10, 2 );" ];
	$out  = bc_lv1_boot( bc_lv1_port( 'port-a', 'PortA', [] ) + bc_lv1_inc_fixture(), $cfg, BC_LV1_ORPHANS, [], $site );
	assert_same( $out['orphans'], [ [ 'module' => 'port-a', 'files' => [ 'inc/port-a.php' ] ] ], 'filtered off' );
} );

bc_test( 'orphaned_legacy_files_recorded_for_surface_clash_loser', function () {
	$themes = bc_lv1_port( 'port-a', 'PortA', [ 'surfaces' => [ 's.one' ] ] ) + bc_lv1_port( 'port-b', 'PortB', [ 'surfaces' => [ 's.one' ] ] ) + bc_lv1_inc_fixture();
	$out    = bc_lv1_boot( $themes, "[ 'modules' => [ 'legacy-v1' => true, 'port-a' => true, 'port-b' => true ] ]", BC_LV1_ORPHANS );
	assert_same( $out['orphans'], [ [ 'module' => 'port-b', 'files' => [ 'inc/port-b.php' ] ] ], 'first in site order wins; the loser lost its v1 file with no replacement' );
	assert_same( in_array( 'surface_clash', $out['codes'], true ), true );
} );

bc_test( 'module_with_no_legacy_files_never_orphans', function () {
	$out = bc_lv1_boot( bc_lv1_port( 'port-a', 'PortA', [ 'legacy' => [ 'files' => [] ] ], "throw new \\RuntimeException( 'x' );" ) + bc_lv1_inc_fixture(), "[ 'modules' => [ 'legacy-v1' => true, 'port-a' => true ] ]", BC_LV1_ORPHANS );
	assert_same( $out['orphans'], [] );
} );

bc_test( 'loader_skip_check_survives_non_array_filter_result', function () {
	$real = file_get_contents( bc_lv1_root() . '/inc/loader.php' );
	$base = sys_get_temp_dir() . '/bc_lv1_cast_' . uniqid();
	@mkdir( $base . '/inc', 0777, true );
	file_put_contents( $base . '/inc/loader.php', $real );
	preg_match_all( "#'(inc/[a-z-]+\\.php)'#", $real, $m );
	foreach ( array_unique( $m[1] ) as $f ) {
		file_put_contents( "$base/$f", "<?php defined( 'ABSPATH' ) || exit; \$GLOBALS['loaded'][] = " . var_export( $f, true ) . ';'
			. ( 'inc/helpers.php' === $f ? ' function blocksy_child_is_plugin_active( $p ) { return true; }' : '' ) );
	}
	// A site filter that returns null (or a string) must not turn into a TypeError in in_array().
	foreach ( [ 'null', "'inc/carousel.php'" ] as $ret ) {
		$out = bc_test_run_isolated(
			"define( 'ABSPATH', '/' );\nini_set( 'error_log', '/dev/null' );\n"
			. "define( 'BLOCKSY_CHILD_PATH', " . var_export( $base . '/', true ) . " );\n"
			. "\$GLOBALS['loaded'] = [];\n"
			. "function apply_filters( \$h, \$v ) { return {$ret}; }\n"
			. "require BLOCKSY_CHILD_PATH . 'inc/loader.php';\n"
			. "echo json_encode( \$GLOBALS['loaded'] );"
		);
		assert_same( in_array( 'inc/helpers.php', $out, true ), true, "loader still ran with filter result {$ret}" );
	}
} );

bc_test( 'no_orphans_when_legacy_v1_is_off', function () {
	// v1 never loaded the file, so nothing was lost even though the port did not boot.
	$out = bc_lv1_boot( bc_lv1_port( 'port-a', 'PortA', [], "throw new \\RuntimeException( 'boom' );" ) + bc_lv1_inc_fixture(), "[ 'modules' => [ 'port-a' => true ] ]", BC_LV1_ORPHANS );
	assert_same( $out['orphans'], [], 'legacy-v1 off' );
	assert_same( in_array( 'module_failed', $out['codes'], true ), true, 'the failure itself is still recorded' );
} );
