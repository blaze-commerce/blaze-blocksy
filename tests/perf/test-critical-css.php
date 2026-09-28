<?php
/**
 * Tests for inc/perf/critical-css-supplements.php and
 * inc/perf/data/critical-css.php.
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/inc/perf/helpers.php';
require_once dirname( __DIR__, 2 ) . '/inc/perf/critical-css-supplements.php';

/**
 * Fires every recorded wp_head callback and returns the captured output.
 * Isolates wp_head first, so only what happens inside $register_fn (or
 * whatever is already registered when no $register_fn is given) fires.
 *
 * @param callable|null $register_fn Optional: called after wp_head is
 *                                    cleared, before the callbacks fire —
 *                                    typically blocksy_child_perf_critical_css_register().
 * @return string
 */
function bc_critical_css_fire_wp_head( ?callable $register_fn = null ): string {
	$GLOBALS['bc_test_hooks']['wp_head'] = [];

	if ( null !== $register_fn ) {
		$register_fn();
	}

	ob_start();
	foreach ( $GLOBALS['bc_test_hooks']['wp_head'] ?? [] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}
	return ob_get_clean();
}

// -----------------------------------------------------------------------
// inc/perf/data/critical-css.php
// -----------------------------------------------------------------------

bc_test( 'inc/perf/data/critical-css.php: loads and returns the three buckets', function () {
	$data = blocksy_child_perf_data( 'critical-css' );

	assert_same( is_array( $data ), true, 'data file returns an array' );
	assert_same( isset( $data['global'] ), true, "'global' bucket present" );
	assert_same( isset( $data['archive'] ), true, "'archive' bucket present" );
	assert_same( isset( $data['product'] ), true, "'product' bucket present" );

	foreach ( [ 'global', 'archive', 'product' ] as $bucket ) {
		assert_same( is_string( $data[ $bucket ] ), true, "{$bucket} bucket is a string" );
		assert_same( '' !== $data[ $bucket ], true, "{$bucket} bucket is non-empty" );
	}
} );

bc_test( 'inc/perf/data/critical-css.php: every bucket is <= 4 KB (with no header-min-height / extra-CSS filters active)', function () {
	$data = blocksy_child_perf_data( 'critical-css' );

	foreach ( [ 'global', 'archive', 'product' ] as $bucket ) {
		assert_same( strlen( $data[ $bucket ] ) <= 4096, true, "{$bucket} bucket is " . strlen( $data[ $bucket ] ) . ' bytes' );
	}
} );

bc_test( 'inc/perf/data/critical-css.php: underline rule uses --theme-text-decoration on Blocksy\'s exact selector', function () {
	$data = blocksy_child_perf_data( 'critical-css' );

	assert_same( strpos( $data['global'], '--theme-text-decoration:underline' ) !== false, true, 'custom property setter present' );
	assert_same(
		strpos( $data['global'], '[data-link="type-2"] .entry-content :where(p,em,strong) > a' ) !== false,
		true,
		'Blocksy\'s exact in-copy-link selector present'
	);
} );

bc_test( 'inc/perf/data/critical-css.php: archive bucket carries the grid/listing-top/overlay/heart rules', function () {
	$data = blocksy_child_perf_data( 'critical-css' );

	assert_same( strpos( $data['archive'], '.woo-listing-top{display:flex' ) !== false, true, 'listing-top bar' );
	assert_same( strpos( $data['archive'], '.woocommerce-ordering{display:flex' ) !== false, true, 'ordering row' );
	assert_same( strpos( $data['archive'], '.pif-has-gallery .wp-post-image--secondary{position:absolute' ) !== false, true, 'secondary-image hide' );
	assert_same( strpos( $data['archive'], '[data-hover="swap"] .ct-swap{position:absolute' ) !== false, true, 'hover-swap hide' );
	assert_same( strpos( $data['archive'], '.ct-heart-fill{opacity:0}' ) !== false, true, 'wishlist heart outline' );
	assert_same( strpos( $data['archive'], '@media(min-width:1000px){[data-products].columns-2{--shop-columns:repeat(2,minmax(0,1fr))}' ) !== false, true, 'desktop grid columns' );
} );

bc_test( 'inc/perf/data/critical-css.php: product bucket carries Flexy containment + both qty fallback rules', function () {
	$data = blocksy_child_perf_data( 'critical-css' );

	assert_same( strpos( $data['product'], '.flexy-view{overflow:hidden}' ) !== false, true, 'Flexy view containment' );
	assert_same( strpos( $data['product'], '.flexy-items{display:flex;flex-wrap:nowrap}' ) !== false, true, 'Flexy items row' );
	assert_same( strpos( $data['product'], '.flexy-item{flex:0 0 100%;max-width:100%}' ) !== false, true, 'Flexy item sizing' );
	assert_same( strpos( $data['product'], '.quantity.hidden::after{content:"1"' ) !== false, true, 'qty fallback number' );
	assert_same( strpos( $data['product'], '.quantity.hidden .ct-increase' ) !== false, true, 'qty fallback locked buttons' );
	assert_same( strpos( $data['product'], 'var(--aw-font-head)' ) === false, true, 'AW site CSS variable replaced with a plain value' );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_critical_css_header_min_height()
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_critical_css_header_min_height(): empty by default (opt-in only)', function () {
	assert_same( blocksy_child_perf_critical_css_header_min_height(), '' );
} );

bc_test( 'blocksy_child_perf_critical_css_header_min_height(): empty when the filter returns something malformed', function () {
	$cb = function () {
		return [ 'desktop' => 158 ]; // missing 'mobile'.
	};
	add_filter( 'blocksy_child_perf_header_min_height', $cb );

	assert_same( blocksy_child_perf_critical_css_header_min_height(), '' );

	remove_filter( 'blocksy_child_perf_header_min_height', $cb );
} );

bc_test( 'blocksy_child_perf_critical_css_header_min_height(): renders both breakpoints once the filter supplies values', function () {
	$cb = function () {
		return [ 'desktop' => 158, 'mobile' => 194 ];
	};
	add_filter( 'blocksy_child_perf_header_min_height', $cb );

	$css = blocksy_child_perf_critical_css_header_min_height();

	assert_same(
		$css,
		'@media (max-width: 999.98px){header#header.ct-header{min-height:194px}}@media (min-width:1000px){header#header.ct-header{min-height:158px}}'
	);

	remove_filter( 'blocksy_child_perf_header_min_height', $cb );
} );

// -----------------------------------------------------------------------
// wp_head printers — bc-perf-critical-global / -archive / -product.
// -----------------------------------------------------------------------

bc_test( 'bc-perf-critical-global: prints unconditionally, with the mandated attributes and no header-min-height rule by default', function () {
	$output = bc_critical_css_fire_wp_head( 'blocksy_child_perf_critical_css_register' );

	assert_same( strpos( $output, '<style id="bc-perf-critical-global" data-no-optimize="1" data-no-minify="1">' ) !== false, true, 'mandated attributes + id present' );
	assert_same( strpos( $output, '--theme-text-decoration:underline' ) !== false, true, 'underline rule present' );
	assert_same( strpos( $output, 'min-height' ) !== false, false, 'no header-min-height rule when unconfigured' );
} );

bc_test( 'bc-perf-critical-global: includes the header-min-height rule once the filter is configured', function () {
	$cb = function () {
		return [ 'desktop' => 158, 'mobile' => 194 ];
	};
	add_filter( 'blocksy_child_perf_header_min_height', $cb );

	$output = bc_critical_css_fire_wp_head( 'blocksy_child_perf_critical_css_register' );

	assert_same( strpos( $output, 'header#header.ct-header{min-height:194px}' ) !== false, true, 'mobile min-height present' );
	assert_same( strpos( $output, 'header#header.ct-header{min-height:158px}' ) !== false, true, 'desktop min-height present' );

	remove_filter( 'blocksy_child_perf_header_min_height', $cb );
} );

bc_test( 'bc-perf-critical-global: header-min-height filter registered AFTER blocksy_child_perf_critical_css_register() still takes effect at wp_head print time', function () {
	// Simulates a plugin/mu-plugin registering its filter on init/plugins_loaded
	// — after inc/perf/loader.php (and this module with it) has already been
	// required from functions.php. blocksy_child_perf_style() accepts a
	// callable for $css precisely so this ordering still works (see
	// inc/perf/helpers.php's docblock for blocksy_child_perf_style()).
	$GLOBALS['bc_test_hooks']['wp_head'] = [];
	blocksy_child_perf_critical_css_register(); // "module load" — no filter registered yet.

	$cb = function () {
		return [ 'desktop' => 158, 'mobile' => 194 ];
	};
	add_filter( 'blocksy_child_perf_header_min_height', $cb ); // registered AFTER module load.

	ob_start();
	foreach ( $GLOBALS['bc_test_hooks']['wp_head'] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			call_user_func( $callback );
		}
	}
	$output = ob_get_clean();

	assert_same( strpos( $output, 'header#header.ct-header{min-height:194px}' ) !== false, true, 'late-registered filter still seen at print time' );
	assert_same( strpos( $output, 'header#header.ct-header{min-height:158px}' ) !== false, true );

	remove_filter( 'blocksy_child_perf_header_min_height', $cb );
} );

bc_test( 'bc-perf-critical-global: appends blocksy_child_perf_critical_css_extra for the global bucket', function () {
	$cb = function ( $extra, $bucket ) {
		if ( 'global' === $bucket ) {
			$extra .= '.my-site-only{color:red}';
		}
		return $extra;
	};
	add_filter( 'blocksy_child_perf_critical_css_extra', $cb );

	$output = bc_critical_css_fire_wp_head( 'blocksy_child_perf_critical_css_register' );

	assert_same( strpos( $output, '.my-site-only{color:red}' ) !== false, true, 'extra CSS present in the global bucket' );

	remove_filter( 'blocksy_child_perf_critical_css_extra', $cb );
} );

bc_test( 'blocksy_child_perf_critical_css_extra: only reaches the bucket it names', function () {
	$cb = function ( $extra, $bucket ) {
		if ( 'archive' === $bucket ) {
			$extra .= '.archive-only-marker{color:red}';
		}
		return $extra;
	};
	add_filter( 'blocksy_child_perf_critical_css_extra', $cb );

	global $bc_test_is_shop, $bc_test_is_product_taxonomy;
	$bc_test_is_shop             = true;
	$bc_test_is_product_taxonomy = false;

	$output = bc_critical_css_fire_wp_head( 'blocksy_child_perf_critical_css_register' );

	assert_same( strpos( $output, '.archive-only-marker' ) !== false, true, 'present in archive bucket output' );

	$global_only = substr( $output, 0, strpos( $output, 'id="bc-perf-critical-archive"' ) );
	assert_same( strpos( $global_only, '.archive-only-marker' ) !== false, false, 'absent from the global bucket that printed before it' );

	remove_filter( 'blocksy_child_perf_critical_css_extra', $cb );
} );

bc_test( 'bc-perf-critical-archive: suppressed when is_shop()/is_product_taxonomy() are both false', function () {
	global $bc_test_is_shop, $bc_test_is_product_taxonomy;
	$bc_test_is_shop             = false;
	$bc_test_is_product_taxonomy = false;

	$output = bc_critical_css_fire_wp_head( 'blocksy_child_perf_critical_css_register' );

	assert_same( strpos( $output, 'bc-perf-critical-archive' ) !== false, false, 'archive <style> not printed' );
} );

bc_test( 'bc-perf-critical-archive: prints (with mandated attributes) when is_shop() is true', function () {
	global $bc_test_is_shop, $bc_test_is_product_taxonomy;
	$bc_test_is_shop             = true;
	$bc_test_is_product_taxonomy = false;

	$output = bc_critical_css_fire_wp_head( 'blocksy_child_perf_critical_css_register' );

	assert_same( strpos( $output, '<style id="bc-perf-critical-archive" data-no-optimize="1" data-no-minify="1">' ) !== false, true );
	assert_same( strpos( $output, '.woo-listing-top{display:flex' ) !== false, true );
} );

bc_test( 'bc-perf-critical-archive: prints when is_product_taxonomy() is true (is_shop() false)', function () {
	global $bc_test_is_shop, $bc_test_is_product_taxonomy;
	$bc_test_is_shop             = false;
	$bc_test_is_product_taxonomy = true;

	$output = bc_critical_css_fire_wp_head( 'blocksy_child_perf_critical_css_register' );

	assert_same( strpos( $output, 'bc-perf-critical-archive' ) !== false, true );
} );

bc_test( 'bc-perf-critical-product: suppressed when is_product() is false', function () {
	global $bc_test_is_product;
	$bc_test_is_product = false;

	$output = bc_critical_css_fire_wp_head( 'blocksy_child_perf_critical_css_register' );

	assert_same( strpos( $output, 'bc-perf-critical-product' ) !== false, false );
} );

bc_test( 'bc-perf-critical-product: prints (with mandated attributes) when is_product() is true', function () {
	global $bc_test_is_product;
	$bc_test_is_product = true;

	$output = bc_critical_css_fire_wp_head( 'blocksy_child_perf_critical_css_register' );

	assert_same( strpos( $output, '<style id="bc-perf-critical-product" data-no-optimize="1" data-no-minify="1">' ) !== false, true );
	assert_same( strpos( $output, '.flexy-view{overflow:hidden}' ) !== false, true );
} );

// -----------------------------------------------------------------------
// Test fixtures for is_shop() / is_product_taxonomy() / is_product() —
// declared last so every test above ran with the state it explicitly set;
// PHP hoists function declarations, so these are available from the very
// first bc_test() call above despite appearing at the bottom of the file.
// -----------------------------------------------------------------------

function is_shop() {
	global $bc_test_is_shop;
	return ! empty( $bc_test_is_shop );
}

function is_product_taxonomy() {
	global $bc_test_is_product_taxonomy;
	return ! empty( $bc_test_is_product_taxonomy );
}

function is_product() {
	global $bc_test_is_product;
	return ! empty( $bc_test_is_product );
}
