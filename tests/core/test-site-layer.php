<?php
/**
 * Task 4: site layer, boot order, fail-closed checkout.
 *
 * Every case boots the theme in a subprocess: SiteLayer reads constants
 * (WP_CONTENT_DIR, BLAZE_SITE_DIR, BLAZE_CHILD_PATH) that cannot be undone.
 *
 * @package Blocksy_Child
 */

function bc_sl_sites(): string {
	return __DIR__ . '/fixtures/sites';
}

/**
 * Boots the real theme bootstrap in a subprocess, then runs $body (which echoes JSON).
 *
 * @param string|null $content WP_CONTENT_DIR (null: leave undefined).
 * @param string      $theme   BLAZE_CHILD_PATH (theme folder: modules/, custom/).
 * @param string      $body    PHP run after boot.
 * @param array       $consts  Extra constants defined before boot.
 * @param string      $pre     PHP run after the stubs load, before boot.
 * @return array
 */
function bc_sl_boot( ?string $content, string $theme, string $body, array $consts = [], string $pre = '' ): array {
	$root   = var_export( dirname( __DIR__, 2 ), true );
	$prefix = "ini_set( 'display_errors', '1' );\n"
		. "define( 'BC_TEST_NO_THEME_CONSTANTS', true );\n"
		. "define( 'BLAZE_CHILD_PATH', " . var_export( rtrim( $theme, '/' ) . '/', true ) . " );\n";
	if ( null !== $content ) {
		$prefix .= "define( 'WP_CONTENT_DIR', " . var_export( $content, true ) . " );\n";
	}
	foreach ( $consts as $name => $value ) {
		$prefix .= "define( " . var_export( $name, true ) . ', ' . var_export( $value, true ) . " );\n";
	}
	// Core classes come from the real src/ whatever BLAZE_CHILD_PATH is; modules from the fixture theme.
	$prefix .= "require {$root} . '/tests/core/stubs.php';\n"
		. "spl_autoload_register( function ( \$c ) {\n"
		. "  \$p = 'Blaze\\\\Child\\\\';\n"
		. "  if ( 0 === strpos( \$c, \$p ) && 0 !== strpos( \$c, \$p . 'Modules\\\\' ) ) {\n"
		. "    \$f = {$root} . '/src/' . str_replace( '\\\\', '/', substr( \$c, strlen( \$p ) ) ) . '.php';\n"
		. "    if ( is_file( \$f ) ) { require_once \$f; }\n"
		. "  }\n"
		. "} );\n"
		. $pre . "\n"
		. "require {$root} . '/src/bootstrap.php';\n"
		. "use Blaze\\Child\\Core\\FailClosed;\n"
		. "use Blaze\\Child\\Core\\Health;\n"
		. "use Blaze\\Child\\Core\\SiteLayer;\n"
		. "use Blaze\\Child\\Core\\Registry;\n"
		. "function bc_codes() { return array_column( Health::all(), 'code' ); }\n";
	return bc_test_run_isolated( $prefix . $body );
}

/** Copies a fixture site to a temp dir; returns the temp dir. */
function bc_sl_tmp_site( array $files ): string {
	$dir = sys_get_temp_dir() . '/bc_site_' . uniqid();
	foreach ( $files as $rel => $src ) {
		@mkdir( dirname( "$dir/$rel" ), 0777, true );
		copy( $src, "$dir/$rel" );
	}
	return $dir;
}

const BC_SL_STATE = "echo json_encode( [ 'codes' => bc_codes(), 'armed' => FailClosed::is_armed(), 'booted' => array_keys( \$GLOBALS['bc_sl_booted'] ?? [] ), 'loaded' => \$GLOBALS['bc_site_loaded'] ?? [], 'order' => \$GLOBALS['bc_order'] ?? [], 'fx' => \$GLOBALS['bc_fx_booted'] ?? [] ] );";

bc_test( 'no_site_package_boots_nothing', function () {
	$none = bc_sl_sites() . '/none';
	$out  = bc_sl_boot( $none, $none . '/theme', "echo json_encode( [ 'dir' => SiteLayer::dir(), 'config' => SiteLayer::config(), 'health' => Health::all(), 'armed' => FailClosed::is_armed(), 'crit' => Registry::critical_failures() ] );" );
	assert_same( $out['dir'], null, 'no blaze-site folder' );
	assert_same( $out['config'], [ 'modules' => [], 'settings' => [], 'on_failure' => 'closed' ], 'defaults' );
	assert_same( array_column( $out['health'], 'code' ), [ 'no_site_config' ], 'exactly one health entry' );
	assert_same( $out['armed'], false, 'checkout stays open: no site code is not a failure' );
	assert_same( $out['crit'], [], 'no critical failures' );

	// WP_CONTENT_DIR undefined (e.g. a CLI context): same outcome, no warning.
	$out = bc_sl_boot( null, $none . '/theme', BC_SL_STATE );
	assert_same( $out['codes'], [ 'no_site_config' ] );
} );

bc_test( 'config_modules_and_settings_normalised', function () {
	$ok  = bc_sl_sites() . '/ok';
	$out = bc_sl_boot( $ok, dirname( bc_sl_sites() ), "\$r = Registry::instance( 'alpha' );\n"
		. "echo json_encode( [ 'dir' => SiteLayer::dir(), 'config' => SiteLayer::config(), 'codes' => bc_codes(), 'color' => \$r ? \$r->setting( 'color' ) : null, 'beta' => null !== Registry::instance( 'beta' ), 'loaded' => \$GLOBALS['bc_site_loaded'] ?? [], 'armed' => FailClosed::is_armed(), 'failed' => SiteLayer::config_failed() ] );" );
	assert_same( $out['dir'], $ok . '/blaze-site', 'WP_CONTENT_DIR/blaze-site' );
	assert_same( $out['failed'], false, 'valid config is not a failure' );
	assert_same( $out['config'], [
		'modules'    => [ 'alpha' => true, 'beta' => false, 'gamma' => true ],
		'settings'   => [ 'alpha' => [ 'color' => 'blue' ] ],
		'on_failure' => 'closed',
	], 'bools cast, non-string ids and non-array settings dropped, unknown on_failure -> closed' );
	assert_same( $out['color'], 'blue', 'alpha booted with its settings' );
	assert_same( $out['beta'], false, 'beta off' );
	assert_same( $out['loaded'], [ 'blaze-site' ], 'site.php included once' );
	assert_same( $out['codes'], [ 'unknown_module' ], 'only the unknown gamma is recorded' );
	assert_same( $out['armed'], false );

	// BLAZE_SITE_DIR overrides WP_CONTENT_DIR/blaze-site.
	$out = bc_sl_boot( bc_sl_sites() . '/none', dirname( bc_sl_sites() ), "echo json_encode( [ SiteLayer::dir(), SiteLayer::config()['modules'] ] );", [ 'BLAZE_SITE_DIR' => $ok . '/blaze-site' ] );
	assert_same( $out, [ $ok . '/blaze-site', [ 'alpha' => true, 'beta' => false, 'gamma' => true ] ], 'BLAZE_SITE_DIR wins' );
} );

bc_test( 'broken_site_config_fails_closed', function () {
	$parse = bc_sl_tmp_site( [ 'site-config.php' => bc_sl_sites() . '/parse-error/site-config.php.broken' ] );
	$cases = [
		'throws'      => [ bc_sl_sites() . '/throws', [] ],
		'not-array'   => [ bc_sl_sites() . '/not-array', [] ],
		'parse-error' => [ bc_sl_sites() . '/none', [ 'BLAZE_SITE_DIR' => $parse ] ],
	];
	foreach ( $cases as $name => [ $content, $consts ] ) {
		try {
			$out = bc_sl_boot( $content, dirname( bc_sl_sites() ), "echo json_encode( [ 'config' => SiteLayer::config(), 'codes' => bc_codes(), 'msgs' => array_column( Health::all(), 'message' ), 'armed' => FailClosed::is_armed(), 'failed' => SiteLayer::config_failed(), 'fx' => \$GLOBALS['bc_fx_booted'] ?? [], 'loaded' => \$GLOBALS['bc_site_loaded'] ?? [] ] );", $consts );
		} finally {
			if ( 'parse-error' === $name ) {
				@unlink( $parse . '/site-config.php' );
				@rmdir( $parse );
			}
		}
		if ( 'parse-error' === $name ) {
			assert_same( 1, preg_match( '/syntax error/', implode( ' ', $out['msgs'] ) ), 'parse-error: ParseError caught, not a missing file' );
		}
		assert_same( $out['config'], [ 'modules' => [], 'settings' => [], 'on_failure' => 'closed' ], "{$name}: defaults" );
		assert_same( in_array( 'site_config_invalid', $out['codes'], true ), true, "{$name}: health site_config_invalid" );
		assert_same( $out['armed'], true, "{$name}: checkout fails closed" );
		assert_same( $out['fx'], [], "{$name}: no modules boot" );
		assert_same( $out['failed'], true, "{$name}: config_failed()" );
	}
	assert_same( $out['loaded'] ?? null, [], 'parse-error has no site.php' );

	// throws: its site.php still loads (it may define functions templates call), but its
	// module_enabled filter must not boot anything while the config is broken.
	$out = bc_sl_boot( bc_sl_sites() . '/throws', dirname( bc_sl_sites() ), BC_SL_STATE );
	assert_same( $out['loaded'], [ 'blaze-site' ], 'site.php still loads' );
	assert_same( $out['fx'], [], 'filter __return_true boots no module' );
	assert_same( $out['armed'], true );
} );

bc_test( 'site_code_throw_fails_closed', function () {
	$site = bc_sl_sites() . '/site-throws';
	$out  = bc_sl_boot( $site, $site . '/theme', "echo json_encode( [ 'codes' => bc_codes(), 'armed' => FailClosed::is_armed(), 'hooks' => array_keys( \$GLOBALS['bc_test_hooks'] ), 'mails' => array_column( \$GLOBALS['bc_core_store']['mails'], 'to' ) ] );", [], "update_option( 'admin_email', 'owner@example.test' );" );
	assert_same( in_array( 'site_code_failed', $out['codes'], true ), true, 'health site_code_failed' );
	assert_same( $out['armed'], true, 'armed' );
	assert_same( in_array( 'woocommerce_checkout_process', $out['hooks'], true ), true, 'classic checkout hooked' );
	assert_same( in_array( 'rest_pre_dispatch', $out['hooks'], true ), true, 'store api hooked' );
	assert_same( $out['mails'], [ 'owner@example.test' ], 'admin alerted once' );
} );

bc_test( 'site_code_throw_open_when_configured', function () {
	$site = bc_sl_sites() . '/site-throws-open';
	$out  = bc_sl_boot( $site, $site . '/theme', "echo json_encode( [ 'codes' => bc_codes(), 'armed' => FailClosed::is_armed(), 'hooks' => array_keys( \$GLOBALS['bc_test_hooks'] ) ] );" );
	assert_same( in_array( 'site_code_failed', $out['codes'], true ), true, 'still recorded' );
	assert_same( $out['armed'], false, 'on_failure open keeps checkout open' );
	assert_same( in_array( 'rest_pre_dispatch', $out['hooks'], true ), false, 'no store api hook' );
} );

bc_test( 'blaze_site_wins_over_legacy_custom', function () {
	$site = bc_sl_sites() . '/both';
	$out  = bc_sl_boot( $site, $site . '/theme', BC_SL_STATE );
	assert_same( $out['loaded'], [ 'blaze-site' ], 'only blaze-site/site.php loaded (no redeclare fatal)' );
	assert_same( $out['codes'], [ 'legacy_custom_ignored' ], 'legacy ignored, recorded; no no_site_config' );
	assert_same( $out['armed'], false );
} );

bc_test( 'legacy_custom_loaded_with_health_entry', function () {
	$site = bc_sl_sites() . '/legacy-only';
	$out  = bc_sl_boot( $site, $site . '/theme', BC_SL_STATE );
	assert_same( $out['loaded'], [ 'legacy' ], 'custom/custom.php loaded' );
	assert_same( $out['codes'], [ 'legacy_custom_in_theme' ], 'one health entry, not no_site_config' );
	assert_same( $out['armed'], false );
} );

bc_test( 'legacy_v1_core_then_site_code_then_modules', function () {
	$site = bc_sl_sites() . '/order';
	$out  = bc_sl_boot( $site, $site . '/theme', BC_SL_STATE );
	assert_same( $out['order'], [ 'v1-core', 'site', 'boot' ], 'v1 order: loader -> custom -> perf' );

	$out = bc_sl_boot( $site, $site . '/theme', BC_SL_STATE, [ 'BLAZE_CHILD_DISABLE_MODULES' => 'legacy-v1' ] );
	assert_same( $out['order'], [ 'site' ], 'kill switch stops v1 core and the module' );

	// legacy-v1 off in config: v1 core not loaded.
	$out = bc_sl_boot( bc_sl_sites() . '/none', $site . '/theme', BC_SL_STATE );
	assert_same( $out['order'], [], 'off by default' );
} );

bc_test( 'critical_module_failure_fails_closed', function () {
	$site = bc_sl_sites() . '/critical';
	$out  = bc_sl_boot( $site, $site . '/theme', "echo json_encode( [ 'codes' => bc_codes(), 'armed' => FailClosed::is_armed(), 'crit' => Registry::critical_failures() ] );" );
	assert_same( $out['crit'], [ 'crit' ] );
	assert_same( in_array( 'module_failed', $out['codes'], true ), true );
	assert_same( $out['armed'], true, 'critical failure arms fail-closed' );
} );

bc_test( 'fail_closed_blocks_classic_checkout', function () {
	$out = bc_sl_boot( null, dirname( bc_sl_sites() ), "FailClosed::arm( 'test' );\nFailClosed::arm( 'test' );\n"
		. "foreach ( \$GLOBALS['bc_test_hooks']['woocommerce_checkout_process'] as \$cbs ) { foreach ( \$cbs as \$cb ) { call_user_func( \$cb ); } }\n"
		. "echo json_encode( [ 'notices' => \$GLOBALS['bc_core_store']['notices'], 'count' => count( \$GLOBALS['bc_test_hooks']['woocommerce_checkout_process'][10] ) ] );" );
	assert_same( $out['notices'], [ [ 'error', 'Checkout is temporarily unavailable. Please try again shortly.' ] ] );
	assert_same( $out['count'], 1, 'arm() is idempotent: one hook' );

	$out = bc_sl_boot( null, dirname( bc_sl_sites() ), "echo json_encode( [ FailClosed::is_armed(), isset( \$GLOBALS['bc_test_hooks']['woocommerce_checkout_process'] ) ] );" );
	assert_same( $out, [ false, false ], 'not armed: nothing hooked' );
} );

bc_test( 'fail_closed_blocks_store_api_post_only', function () {
	$out = bc_sl_boot( null, dirname( bc_sl_sites() ), "FailClosed::arm( 'test' );\n"
		. "\$r = [];\n"
		. "foreach ( [ [ 'POST', '/wc/store/v1/checkout' ], [ 'POST', '/wc/store/v1/checkout/123' ], [ 'post', '/WC/Store/V1/Checkout' ], [ 'POST', '/wc/store/checkout' ], [ 'GET', '/wc/store/v1/checkout' ], [ 'POST', '/wc/store/v1/cart/add-item' ], [ 'POST', '/wp/v2/posts' ] ] as [ \$m, \$route ] ) {\n"
		. "  \$res = apply_filters( 'rest_pre_dispatch', null, null, new WP_REST_Request( \$m, \$route ) );\n"
		. "  \$r[ \"\$m \$route\" ] = \$res instanceof WP_Error ? [ \$res->get_error_code(), \$res->get_error_message(), \$res->get_error_data() ] : \$res;\n"
		. "}\n"
		. "\$r['earlier'] = apply_filters( 'rest_pre_dispatch', 'kept', null, new WP_REST_Request( 'GET', '/wc/store/v1/cart' ) );\n"
		. "echo json_encode( \$r );" );
	$blocked = [ 'blaze_child_checkout_unavailable', 'Checkout is temporarily unavailable. Please try again shortly.', [ 'status' => 503 ] ];
	assert_same( $out['POST /wc/store/v1/checkout'], $blocked, 'checkout POST blocked' );
	assert_same( $out['POST /wc/store/v1/checkout/123'], $blocked, 'pay-for-order POST blocked' );
	assert_same( $out['post /WC/Store/V1/Checkout'], $blocked, 'case variants blocked (WP matches routes case-insensitively)' );
	assert_same( $out['POST /wc/store/checkout'], $blocked, 'unversioned legacy namespace blocked' );
	assert_same( $out['GET /wc/store/v1/checkout'], null, 'GET untouched' );
	assert_same( $out['POST /wc/store/v1/cart/add-item'], null, 'cart untouched' );
	assert_same( $out['POST /wp/v2/posts'], null, 'other routes untouched' );
	assert_same( $out['earlier'], 'kept', 'an earlier short-circuit is passed through' );
} );

bc_test( 'fail_closed_mails_once_per_hour', function () {
	$out = bc_sl_boot( null, dirname( bc_sl_sites() ), "update_option( 'admin_email', 'owner@example.test' );\n"
		. "\$reset = function () { \$p = new ReflectionProperty( FailClosed::class, 'armed' ); \$p->setAccessible( true ); \$p->setValue( null, false ); };\n"
		. "FailClosed::arm( 'first' ); FailClosed::arm( 'again' );\n"
		. "\$a = count( \$GLOBALS['bc_core_store']['mails'] );\n"
		. "\$reset(); FailClosed::arm( 'next request' );\n"
		. "\$b = count( \$GLOBALS['bc_core_store']['mails'] );\n"
		. "unset( \$GLOBALS['bc_core_store']['transients']['blaze_child_failclosed_mail'] );\n"
		. "\$reset(); FailClosed::arm( 'an hour later' );\n"
		. "echo json_encode( [ \$a, \$b, count( \$GLOBALS['bc_core_store']['mails'] ), \$GLOBALS['bc_core_store']['mails'][0]['to'], \$GLOBALS['bc_core_store']['transient_ttl']['blaze_child_failclosed_mail'], strpos( \$GLOBALS['bc_core_store']['mails'][0]['message'], 'first' ) !== false ] );" );
	assert_same( $out, [ 1, 1, 2, 'owner@example.test', 3600, true ], 'one mail per hour, to admin_email, naming the reason' );
} );

bc_test( 'admin_notice_only_for_manage_options', function () {
	$out = bc_sl_boot( null, dirname( bc_sl_sites() ), "Health::clear();\n"
		. "update_option( 'blaze_child_health', [ [ 'code' => 'old_thing', 'message' => 'two days ago', 'context' => [], 'time' => time() - 2 * 86400 ] ] );\n"
		. "Health::record( 'site_code_failed', 'boom <script>' );\n"
		. "\$cb = \$GLOBALS['bc_test_hooks']['admin_notices'][10][0];\n"
		. "ob_start(); call_user_func( \$cb ); \$denied = ob_get_clean();\n"
		. "\$GLOBALS['bc_wp_stub']['current_user_can'] = true;\n"
		. "ob_start(); call_user_func( \$cb ); \$shown = ob_get_clean();\n"
		. "echo json_encode( [ 'cb' => \$cb, 'denied' => \$denied, 'shown' => \$shown, 'caps' => array_unique( \$GLOBALS['bc_core_store']['caps'] ) ] );" );
	assert_same( $out['cb'], [ 'Blaze\\Child\\Core\\Theme', 'admin_notices' ], 'named method on admin_notices' );
	assert_same( $out['denied'], '', 'nothing for users without manage_options' );
	assert_same( $out['caps'], [ 'manage_options' ], 'checks manage_options' );
	assert_same( false !== strpos( $out['shown'], 'site_code_failed' ), true, 'recent entry shown' );
	assert_same( false !== strpos( $out['shown'], 'boom' ), true, 'message shown' );
	assert_same( false !== strpos( $out['shown'], 'old_thing' ), false, 'entries older than 24 h hidden' );
} );

bc_test( 'fail_closed_blocks_classic_order_pay', function () {
	$out = bc_sl_boot( null, dirname( bc_sl_sites() ), "FailClosed::arm( 'test' );\n"
		. "foreach ( \$GLOBALS['bc_test_hooks']['woocommerce_before_pay_action'] ?? [] as \$cbs ) { foreach ( \$cbs as \$cb ) { call_user_func( \$cb, new stdClass() ); } }\n"
		. "echo json_encode( [ 'notices' => \$GLOBALS['bc_core_store']['notices'] ] );" );
	assert_same( $out['notices'], [ [ 'error', 'Checkout is temporarily unavailable. Please try again shortly.' ] ], 'order-pay gets the error notice, so pay_action skips payment' );

	$out = bc_sl_boot( null, dirname( bc_sl_sites() ), "echo json_encode( [ isset( \$GLOBALS['bc_test_hooks']['woocommerce_before_pay_action'] ) ] );" );
	assert_same( $out, [ false ], 'not armed: order-pay untouched' );
} );

bc_test( 'admin_notice_escapes_output', function () {
	$out = bc_sl_boot( null, dirname( bc_sl_sites() ), "Health::clear();\n"
		. "Health::record( 'code<b>', 'boom <script>alert(1)</script> \"q\"' );\n"
		. "\$GLOBALS['bc_wp_stub']['current_user_can'] = true;\n"
		. "ob_start(); Blaze\\Child\\Core\\Theme::admin_notices(); \$shown = ob_get_clean();\n"
		. "echo json_encode( [ 'shown' => \$shown, 'stub' => esc_html( '<i>' ) ] );", [ 'BC_TEST_ESCAPE_HTML' => true ] );
	assert_same( $out['stub'], '&lt;i&gt;', 'escaping stub active in this subprocess' );
	assert_same( false !== strpos( $out['shown'], 'boom &lt;script&gt;alert(1)&lt;/script&gt; &quot;q&quot;' ), true, 'message escaped' );
	assert_same( false !== strpos( $out['shown'], 'code&lt;b&gt;' ), true, 'code escaped' );
	assert_same( false === strpos( $out['shown'], '<script>' ) && false === strpos( $out['shown'], '<b>' ), true, 'no raw markup from health entries' );
} );

bc_test( 'critical_module_unmet_requires_fails_closed', function () {
	$site = bc_sl_sites() . '/critical-unmet';
	$out  = bc_sl_boot( $site, $site . '/theme', "echo json_encode( [ 'codes' => bc_codes(), 'armed' => FailClosed::is_armed(), 'crit' => Registry::critical_failures(), 'fx' => \$GLOBALS['bc_fx_booted'] ?? [], 'wc' => class_exists( 'WooCommerce', false ) ] );" );
	assert_same( $out['wc'], false, 'WooCommerce absent' );
	assert_same( $out['fx'], [], 'module skipped' );
	assert_same( $out['crit'], [ 'needs-woo' ], 'R7: unmet critical counts as a failure' );
	assert_same( in_array( 'requires_unmet', $out['codes'], true ), true );
	assert_same( $out['armed'], true, 'Theme::boot() arms fail-closed' );
} );

bc_test( 'site_dir_constant_missing_recorded', function () {
	$none = bc_sl_sites() . '/none';
	$out  = bc_sl_boot( $none, $none . '/theme', "echo json_encode( [ 'dir' => SiteLayer::dir(), 'codes' => bc_codes(), 'armed' => FailClosed::is_armed() ] );", [ 'BLAZE_SITE_DIR' => $none . '/does-not-exist' ] );
	assert_same( $out['dir'], null, 'falls back as before: no site folder' );
	assert_same( $out['codes'], [ 'site_dir_missing', 'no_site_config' ], 'missing BLAZE_SITE_DIR recorded' );
	assert_same( $out['armed'], false );
} );

bc_test( 'fail_closed_mail_is_reason_neutral', function () {
	$out = bc_sl_boot( null, dirname( bc_sl_sites() ), "update_option( 'admin_email', 'owner@example.test' );\n"
		. "FailClosed::arm( 'critical_module_failed' );\n"
		. "echo json_encode( \$GLOBALS['bc_core_store']['mails'][0] );" );
	assert_same( false !== strpos( $out['subject'], 'Checkout is closed (critical_module_failed)' ), true, 'subject names the reason' );
	assert_same( false !== strpos( $out['message'], 'Checkout is closed (critical_module_failed)' ), true, 'body names the reason' );
	assert_same( false === stripos( $out['subject'] . $out['message'], 'site code failed' ), true, 'no site-code wording' );
} );

