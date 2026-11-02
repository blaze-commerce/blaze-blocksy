<?php
/**
 * Task 7: hide-cart-page port (config-first, v1 aliases kept).
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/src/Core/Module.php';
require_once dirname( __DIR__, 2 ) . '/src/Core/Health.php';
require_once dirname( __DIR__, 2 ) . '/modules/hide-cart-page/Module.php';

use Blaze\Child\Core\Health;
use Blaze\Child\Modules\HideCartPage\Module as HideCartPage;

if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		return 'https://example.test' . $path;
	}
}
if ( ! function_exists( 'wc_get_checkout_url' ) ) {
	function wc_get_checkout_url() {
		return 'https://example.test/checkout/';
	}
}
if ( ! function_exists( 'wc_get_page_id' ) ) {
	function wc_get_page_id( $page ) {
		return 'cart' === $page ? 42 : 0;
	}
}
if ( ! function_exists( 'wp_safe_redirect' ) ) {
	function wp_safe_redirect( $url, $status = 302 ) {
		throw new \RuntimeException( 'redirect:' . $url );
	}
}
if ( ! function_exists( 'WC' ) ) {
	function WC() {
		return (object) [
			'cart' => new class() {
				public function is_empty() {
					return ! empty( $GLOBALS['bc_hcp_cart_empty'] );
				}
			},
		];
	}
}

function bc_hcp_manifest(): array {
	return json_decode( (string) file_get_contents( dirname( __DIR__, 2 ) . '/modules/hide-cart-page/module.json' ), true );
}

/** Fresh state plus a booted module instance. */
function bc_hcp_boot( array $settings = [], bool $cart_empty = false ): HideCartPage {
	bc_wp_stub_reset();
	$GLOBALS['bc_test_hooks']                    = [];
	$GLOBALS['bc_core_store']['options']         = [];
	$GLOBALS['bc_core_store']['deprecated']      = [];
	$GLOBALS['bc_core_store']['actions']         = [ 'wp_loaded' => 1 ];
	$GLOBALS['bc_hcp_cart_empty']                = $cart_empty;
	$module = new HideCartPage( bc_hcp_manifest(), $settings );
	$module->boot();
	return $module;
}

function bc_hcp_redirect_target( HideCartPage $module ): string {
	try {
		$module->maybe_redirect();
	} catch ( \RuntimeException $e ) {
		return substr( $e->getMessage(), strlen( 'redirect:' ) );
	}
	return '';
}

/** Runs $body in a fresh process with constants defined first (constants cannot be undefined). */
function bc_hcp_isolated( array $consts, string $body ): array {
	$root   = var_export( dirname( __DIR__, 2 ), true );
	$prefix = '';
	foreach ( $consts as $name => $value ) {
		$prefix .= 'define( ' . var_export( $name, true ) . ', ' . var_export( $value, true ) . " );\n";
	}
	$prefix .= "require {$root} . '/tests/core/stubs.php';\n"
		. "require {$root} . '/src/Core/Module.php';\n"
		. "require {$root} . '/src/Core/Health.php';\n"
		. "require {$root} . '/modules/hide-cart-page/Module.php';\n"
		. "function wc_get_checkout_url() { return 'https://example.test/checkout/'; }\n"
		. "function home_url( \$p = '' ) { return 'https://example.test' . \$p; }\n"
		. "function WC() { return (object) [ 'cart' => new class() { public function is_empty() { return false; } } ]; }\n"
		. "\$GLOBALS['bc_core_store']['actions']['wp_loaded'] = 1;\n"
		. "\$m = new Blaze\\Child\\Modules\\HideCartPage\\Module( json_decode( file_get_contents( {$root} . '/modules/hide-cart-page/module.json' ), true ), [] );\n"
		. "\$m->boot();\n";
	return bc_test_run_isolated( $prefix . $body );
}

bc_test( 'redirects_cart_to_checkout_when_cart_has_items', function () {
	$module = bc_hcp_boot();
	$GLOBALS['bc_wp_stub']['is_cart'] = true;
	assert_same( bc_hcp_redirect_target( $module ), 'https://example.test/checkout/' );
	assert_same( $module->destination(), 'https://example.test/checkout/', 'auto + items -> checkout' );

	$GLOBALS['bc_wp_stub']['is_cart'] = false;
	assert_same( bc_hcp_redirect_target( $module ), '', 'not the cart page: no redirect' );
} );

bc_test( 'redirects_empty_cart_to_shop', function () {
	$shop   = 'https://example.test/shop/';
	$module = bc_hcp_boot( [], true );
	$GLOBALS['bc_wp_stub']['is_cart'] = true;
	assert_same( bc_hcp_redirect_target( $module ), $shop, 'auto + empty -> shop (checkout would bounce back to /cart)' );

	$module = bc_hcp_boot( [ 'destination' => 'checkout' ], true );
	assert_same( $module->destination(), $shop, 'checkout + empty cart still goes to shop: no loop' );

	$module = bc_hcp_boot( [ 'destination' => 'checkout' ], false );
	assert_same( $module->destination(), 'https://example.test/checkout/' );

	// Before wp_loaded the cart is unknown, which counts as empty.
	$module = bc_hcp_boot();
	$GLOBALS['bc_core_store']['actions'] = [];
	assert_same( $module->destination(), $shop, 'cart unknown before wp_loaded' );
} );

bc_test( 'destination_setting_home', function () {
	assert_same( bc_hcp_boot( [ 'destination' => 'home' ] )->destination(), 'https://example.test/' );
	assert_same( bc_hcp_boot( [ 'destination' => 'home' ], true )->destination(), 'https://example.test/', 'home wins even for an empty cart' );
	assert_same( bc_hcp_boot( [ 'destination' => 'shop' ] )->destination(), 'https://example.test/shop/' );
	assert_same( bc_hcp_boot( [ 'destination' => 'nonsense' ] )->destination(), 'https://example.test/checkout/', 'unknown value falls back to auto' );
	assert_same( bc_hcp_boot( [ 'destination' => 7 ] )->destination(), 'https://example.test/checkout/', 'non-string falls back to auto' );
	assert_same( bc_hcp_boot( [ 'destination' => 'home' ] )->apply_cart_url( 'https://example.test/cart/' ), 'https://example.test/', 'cart URL filter uses the same destination' );
} );

bc_test( 'new_filter_overrides_destination', function () {
	$module = bc_hcp_boot();
	add_filter( 'blaze_child/hide-cart-page/destination', function ( $url, $empty, $configured ) {
		return $url . '?via=' . ( $empty ? 'empty' : 'items' ) . '&c=' . $configured;
	}, 10, 3 );
	assert_same( $module->destination(), 'https://example.test/checkout/?via=items&c=auto' );
} );

bc_test( 'legacy_constant_false_disables', function () {
	$out = bc_hcp_isolated( [ 'BLAZE_HIDE_CART_PAGE' => false ], "echo json_encode( [ 'hooks' => array_keys( \$GLOBALS['bc_test_hooks'] ), 'health' => Blaze\\Child\\Core\\Health::all() ] );" );
	assert_same( $out['hooks'], [], 'no hook registered when the v1 constant is false' );
	assert_same( array_column( $out['health'], 'code' ), [ 'deprecated_alias' ] );
	assert_same( $out['health'][0]['context']['module'], 'hide-cart-page' );
	assert_same( $out['health'][0]['context']['alias'], 'BLAZE_HIDE_CART_PAGE' );

	$out = bc_hcp_isolated( [ 'BLAZE_HIDE_CART_PAGE' => true ], "echo json_encode( [ 'hooks' => count( \$GLOBALS['bc_test_hooks'] ), 'health' => Blaze\\Child\\Core\\Health::all() ] );" );
	assert_same( $out['hooks'], 5, 'constant true keeps the module on' );
	assert_same( $out['health'], [], 'true records nothing' );
} );

bc_test( 'legacy_constant_falsy_disables', function () {
	$out = bc_hcp_isolated( [ 'BLAZE_HIDE_CART_PAGE' => 0 ], "echo json_encode( [ 'hooks' => count( \$GLOBALS['bc_test_hooks'] ), 'codes' => array_column( Blaze\\Child\\Core\\Health::all(), 'code' ) ] );" );
	assert_same( $out['hooks'], 0, 'v1 used ! BLAZE_HIDE_CART_PAGE: 0 is off too' );
	assert_same( $out['codes'], [ 'deprecated_alias' ] );
} );

bc_test( 'site_destination_wins_over_legacy_constant', function () {
	$body = "\$m2 = new Blaze\\Child\\Modules\\HideCartPage\\Module( json_decode( file_get_contents( %s . '/modules/hide-cart-page/module.json' ), true ), %s ); \$m2->boot();"
		. "echo json_encode( [ 'url' => \$m2->destination(), 'msg' => array_column( Blaze\\Child\\Core\\Health::all(), 'message' ) ] );";
	$root = var_export( dirname( __DIR__, 2 ), true );

	$out = bc_hcp_isolated( [ 'BLAZE_HIDE_CART_PAGE_DESTINATION' => 'home' ], sprintf( $body, $root, var_export( [ 'destination' => 'shop' ], true ) ) );
	assert_same( $out['url'], 'https://example.test/shop/', 'site setting beats the constant' );
	assert_same( 1 === preg_match( '/constant is ignored/', implode( ' ', $out['msg'] ) ), true, 'message says the constant is ignored' );

	$out = bc_hcp_isolated( [ 'BLAZE_HIDE_CART_PAGE_DESTINATION' => 'home' ], sprintf( $body, $root, var_export( [ 'destination' => 'auto' ], true ) ) );
	assert_same( $out['url'], 'https://example.test/checkout/', 'an explicit auto also wins' );
} );

bc_test( 'legacy_destination_constant_maps_to_setting', function () {
	$out = bc_hcp_isolated( [ 'BLAZE_HIDE_CART_PAGE_DESTINATION' => 'home' ], "echo json_encode( [ 'url' => \$m->destination(), 'codes' => array_column( Blaze\\Child\\Core\\Health::all(), 'code' ) ] );" );
	assert_same( $out['url'], 'https://example.test/' );
	assert_same( $out['codes'], [ 'deprecated_alias' ] );
} );

bc_test( 'legacy_filter_still_applies_with_deprecation', function () {
	$module = bc_hcp_boot();
	add_filter( 'blaze_child/hide-cart-page/destination', function ( $url ) {
		return $url . 'A';
	} );
	add_filter( 'blaze_hide_cart_page_destination', function ( $url, $empty, $configured ) {
		return $url . 'B' . ( $empty ? 'e' : 'i' ) . $configured;
	}, 10, 3 );
	assert_same( $module->destination(), 'https://example.test/checkout/ABiauto', 'new filter first, then the deprecated one, same args' );
	$dep = $GLOBALS['bc_core_store']['deprecated'];
	assert_same( count( $dep ), 1 );
	assert_same( $dep[0], [ 'blaze_hide_cart_page_destination', '2.0.0', 'blaze_child/hide-cart-page/destination' ] );
} );

bc_test( 'menu_cart_items_removed', function () {
	$module = bc_hcp_boot();
	$items  = [
		(object) [ 'object' => 'page', 'object_id' => 42 ],
		(object) [ 'object' => 'page', 'object_id' => '7' ],
		(object) [ 'object' => 'custom', 'object_id' => 42 ],
	];
	$out = $module->filter_menu_items( $items, null, [] );
	assert_same( array_keys( $out ), [ 1, 2 ], 'only the cart page item is dropped' );
	assert_same( $module->filter_menu_items( 'x', null, [] ), 'x', 'non-array passes through' );

	$crumbs = $module->filter_breadcrumbs( [ [ 'id' => 42 ], [ 'id' => 9 ], [ 'name' => 'Home' ] ] );
	assert_same( $crumbs, [ [ 'id' => 9 ], [ 'name' => 'Home' ] ], 'breadcrumb trail drops the cart page and re-indexes' );

	$GLOBALS['bc_wp_stub']['is_cart'] = true;
	ob_start();
	$module->noindex();
	assert_same( ob_get_clean(), '<meta name="robots" content="noindex, nofollow">' . "\n" );
	$GLOBALS['bc_wp_stub']['is_cart'] = false;
	ob_start();
	$module->noindex();
	assert_same( ob_get_clean(), '', 'nothing off the cart page' );
} );

bc_test( 'callbacks_are_removable_named_methods', function () {
	$module = bc_hcp_boot();
	$expect = [
		[ 'template_redirect', 'maybe_redirect', 1 ],
		[ 'woocommerce_get_cart_url', 'apply_cart_url', 10 ],
		[ 'wp_get_nav_menu_items', 'filter_menu_items', 10 ],
		[ 'wp_head', 'noindex', 1 ],
		[ 'blocksy:general:breadcrumbs:items', 'filter_breadcrumbs', 10 ],
	];
	assert_same( count( $GLOBALS['bc_test_hooks'] ), 5, 'five hooks, nothing else' );
	foreach ( $expect as [ $hook, $method, $priority ] ) {
		assert_same( is_callable( [ $module, $method ] ), true, "{$method} is public" );
		assert_same( remove_action( $hook, [ $module, $method ], $priority ), true, "{$hook} @ {$priority} removable by [ module, '{$method}' ]" );
	}
} );

bc_test( 'manifest_follows_spec_and_r4', function () {
	$m = bc_hcp_manifest();
	assert_same( $m['status'], 'stable' );
	assert_same( $m['loadable'], true );
	assert_same( $m['surfaces'], [ 'cart.page' ] );
	assert_same( $m['default'], false );
	assert_same( $m['replaces'], [] );
	assert_same( $m['requires']['plugins']['woocommerce'], '>=9.0' );
	assert_same( $m['settings']['destination']['default'], 'auto', 'R4' );
	assert_same( $m['settings']['destination']['enum'], [ 'auto', 'checkout', 'shop', 'home' ] );
	assert_same( $m['legacy']['files'], [ 'inc/hide-cart-page.php' ] );
	assert_same( $m['legacy']['constants'], [ 'BLAZE_HIDE_CART_PAGE', 'BLAZE_HIDE_CART_PAGE_DESTINATION' ] );
	assert_same( $m['legacy']['filters'], [ 'blaze_hide_cart_page_destination' ] );
	assert_same( $m['hooks']['filters'], [ 'blaze_child/hide-cart-page/destination' ] );
	assert_same( bc_hcp_boot()->setting( 'destination' ), 'auto', 'manifest default reaches setting()' );
} );
