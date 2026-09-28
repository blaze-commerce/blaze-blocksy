<?php
/**
 * Tests for inc/perf/perfmatters-filters.php and inc/perf/rucss-safelist.php.
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/inc/perf/helpers.php';
require_once dirname( __DIR__, 2 ) . '/inc/perf/perfmatters-filters.php';
require_once dirname( __DIR__, 2 ) . '/inc/perf/rucss-safelist.php';

// -----------------------------------------------------------------------
// Registration: every mandated filter is registered exactly once, each
// with a named (non-closure) callback.
// -----------------------------------------------------------------------

bc_test( 'perfmatters_used_css_below: registered with __return_true', function () {
	assert_same( apply_filters( 'perfmatters_used_css_below', false ), true );
} );

bc_test( 'perfmatters_delay_js_delay_click: registered with __return_true', function () {
	assert_same( apply_filters( 'perfmatters_delay_js_delay_click', false ), true );
} );

bc_test( 'every registered perfmatters_/blocksy_child_perf_pmf_ callback is a named function, not a closure', function () {
	$hooks = [
		'perfmatters_used_css_below',
		'perfmatters_delay_js_timeout',
		'perfmatters_delay_js_delay_click',
		'perfmatters_delay_js_exclusions',
		'perfmatters_css_background_selectors',
		'perfmatters_exclude_leading_images',
		'perfmatters_rucss_excluded_stylesheets',
		'perfmatters_defer_js_exclusions',
		'perfmatters_rucss_excluded_selectors',
	];

	foreach ( $hooks as $hook ) {
		assert_same( empty( $GLOBALS['bc_test_hooks'][ $hook ] ), false, "{$hook} has at least one registered callback" );

		foreach ( $GLOBALS['bc_test_hooks'][ $hook ] as $callbacks ) {
			foreach ( $callbacks as $callback ) {
				assert_same( is_string( $callback ), true, "{$hook} callback is a named function (string callable), not a Closure" );
			}
		}
	}
} );

// -----------------------------------------------------------------------
// perfmatters_delay_js_timeout
// -----------------------------------------------------------------------

bc_test( 'perfmatters_delay_js_timeout: defaults to 7000 (seconds)', function () {
	assert_same( apply_filters( 'perfmatters_delay_js_timeout', 1 ), 7000 );
} );

bc_test( 'perfmatters_delay_js_timeout: blocksy_child_perf_delay_timeout overrides the default', function () {
	$cb = function () {
		return 3600;
	};
	add_filter( 'blocksy_child_perf_delay_timeout', $cb );

	assert_same( apply_filters( 'perfmatters_delay_js_timeout', 1 ), 3600 );

	remove_action( 'blocksy_child_perf_delay_timeout', $cb );
} );

// -----------------------------------------------------------------------
// perfmatters_delay_js_exclusions
// -----------------------------------------------------------------------

bc_test( 'perfmatters_delay_js_exclusions: returns an array when given null', function () {
	$result = apply_filters( 'perfmatters_delay_js_exclusions', null );
	assert_same( is_array( $result ), true );
} );

bc_test( 'perfmatters_delay_js_exclusions: returns an array when given a string', function () {
	$result = apply_filters( 'perfmatters_delay_js_exclusions', 'not-an-array' );
	assert_same( is_array( $result ), true );
} );

bc_test( 'perfmatters_delay_js_exclusions: includes a registered script id', function () {
	blocksy_child_perf_register_script_id( 'bc-perf-test-filters-script' );

	$result = apply_filters( 'perfmatters_delay_js_exclusions', [] );

	assert_same( in_array( 'bc-perf-test-filters-script', $result, true ), true );
} );

bc_test( 'perfmatters_delay_js_exclusions: includes the analytics substrings by default', function () {
	$result = apply_filters( 'perfmatters_delay_js_exclusions', [] );

	foreach ( blocksy_child_perf_pmf_analytics_delay_exclusions() as $entry ) {
		assert_same( in_array( $entry, $result, true ), true, "{$entry} present" );
	}
} );

bc_test( 'perfmatters_delay_js_exclusions: blocksy_child_perf_exclude_analytics_from_delay=false removes the analytics entries', function () {
	$cb = '__return_false';
	add_filter( 'blocksy_child_perf_exclude_analytics_from_delay', $cb );

	$result = apply_filters( 'perfmatters_delay_js_exclusions', [] );

	foreach ( blocksy_child_perf_pmf_analytics_delay_exclusions() as $entry ) {
		assert_same( in_array( $entry, $result, true ), false, "{$entry} absent once analytics exclusion is opted out" );
	}

	remove_action( 'blocksy_child_perf_exclude_analytics_from_delay', $cb );
} );

bc_test( 'perfmatters_delay_js_exclusions: blocksy_child_perf_delay_exclusions adds extra entries', function () {
	$cb = function ( $extra ) {
		$extra[] = 'my-custom-handle';
		return $extra;
	};
	add_filter( 'blocksy_child_perf_delay_exclusions', $cb );

	$result = apply_filters( 'perfmatters_delay_js_exclusions', [] );

	assert_same( in_array( 'my-custom-handle', $result, true ), true );

	remove_action( 'blocksy_child_perf_delay_exclusions', $cb );
} );

bc_test( 'perfmatters_delay_js_exclusions: dedupes', function () {
	blocksy_child_perf_register_script_id( 'bc-perf-test-filters-script' ); // already registered above.

	$result = apply_filters( 'perfmatters_delay_js_exclusions', [ 'bc-perf-test-filters-script' ] );

	assert_same( count( array_keys( $result, 'bc-perf-test-filters-script', true ) ), 1 );
} );

// -----------------------------------------------------------------------
// perfmatters_css_background_selectors
// -----------------------------------------------------------------------

bc_test( 'perfmatters_css_background_selectors: returns an array when given null', function () {
	assert_same( is_array( apply_filters( 'perfmatters_css_background_selectors', null ) ), true );
} );

bc_test( 'perfmatters_css_background_selectors: includes the default GreenShift selectors', function () {
	$result = apply_filters( 'perfmatters_css_background_selectors', [] );

	assert_same( in_array( '.wp-block-greenshift-blocks-container', $result, true ), true );
	assert_same( in_array( '.wp-block-greenshift-blocks-row', $result, true ), true );
} );

bc_test( 'perfmatters_css_background_selectors: blocksy_child_perf_css_bg_selectors can add to the default', function () {
	$cb = function ( $selectors ) {
		$selectors[] = '.my-hero-bg';
		return $selectors;
	};
	add_filter( 'blocksy_child_perf_css_bg_selectors', $cb );

	$result = apply_filters( 'perfmatters_css_background_selectors', [] );

	assert_same( in_array( '.my-hero-bg', $result, true ), true );
	assert_same( in_array( '.wp-block-greenshift-blocks-container', $result, true ), true, 'default preserved alongside the addition' );

	remove_action( 'blocksy_child_perf_css_bg_selectors', $cb );
} );

// -----------------------------------------------------------------------
// perfmatters_exclude_leading_images
// -----------------------------------------------------------------------

bc_test( 'perfmatters_exclude_leading_images: takes the max of the incoming count and the filtered default', function () {
	assert_same( apply_filters( 'perfmatters_exclude_leading_images', 5 ), 5, 'incoming count (5) beats the unfiltered default (0)' );

	$cb = function () {
		return 14;
	};
	add_filter( 'blocksy_child_perf_leading_images', $cb );

	assert_same( apply_filters( 'perfmatters_exclude_leading_images', 5 ), 14, 'filtered default (14) beats the incoming count (5)' );
	assert_same( apply_filters( 'perfmatters_exclude_leading_images', 20 ), 20, 'incoming count (20) beats the filtered default (14)' );

	remove_action( 'blocksy_child_perf_leading_images', $cb );
} );

// -----------------------------------------------------------------------
// perfmatters_rucss_excluded_stylesheets
// -----------------------------------------------------------------------

bc_test( 'perfmatters_rucss_excluded_stylesheets: returns an array when given null', function () {
	assert_same( is_array( apply_filters( 'perfmatters_rucss_excluded_stylesheets', null ) ), true );
} );

bc_test( 'perfmatters_rucss_excluded_stylesheets: merges blocksy_child_perf_rucss_stylesheets', function () {
	$cb = function ( $stylesheets ) {
		$stylesheets[] = 'plugins/some-plugin/style.min.css';
		return $stylesheets;
	};
	add_filter( 'blocksy_child_perf_rucss_stylesheets', $cb );

	$result = apply_filters( 'perfmatters_rucss_excluded_stylesheets', [] );

	assert_same( in_array( 'plugins/some-plugin/style.min.css', $result, true ), true );

	remove_action( 'blocksy_child_perf_rucss_stylesheets', $cb );
} );

bc_test( 'perfmatters_rucss_excluded_stylesheets: rejects any entry matching main.min.css (Global Constraint 11) and logs it', function () {
	$cb = function ( $stylesheets ) {
		$stylesheets[] = 'wp-content/themes/blocksy-child/assets/css/main.min.css';
		return $stylesheets;
	};
	add_filter( 'blocksy_child_perf_rucss_stylesheets', $cb );

	$result = apply_filters( 'perfmatters_rucss_excluded_stylesheets', [ 'safe.min.css' ] );

	assert_same( in_array( 'safe.min.css', $result, true ), true, 'unrelated entry kept' );

	$found_main = false;
	foreach ( $result as $entry ) {
		if ( false !== strpos( $entry, 'main.min.css' ) ) {
			$found_main = true;
		}
	}
	assert_same( $found_main, false, 'main.min.css never present in the returned list' );

	$logged = false;
	foreach ( bc_test_error_log_messages() as $message ) {
		if ( false !== strpos( $message, 'rejected RUCSS excluded stylesheet matching main.min.css' ) ) {
			$logged = true;
		}
	}
	assert_same( $logged, true, 'rejection logged' );

	remove_action( 'blocksy_child_perf_rucss_stylesheets', $cb );
} );

// -----------------------------------------------------------------------
// perfmatters_defer_js_exclusions
// -----------------------------------------------------------------------

bc_test( 'perfmatters_defer_js_exclusions: returns an array when given null and includes registered script ids', function () {
	blocksy_child_perf_register_script_id( 'bc-perf-test-defer-script' );

	$result = apply_filters( 'perfmatters_defer_js_exclusions', null );

	assert_same( is_array( $result ), true );
	assert_same( in_array( 'bc-perf-test-defer-script', $result, true ), true );
} );

// -----------------------------------------------------------------------
// blocksy_child_perf_rucss_selector_is_valid()
// -----------------------------------------------------------------------

bc_test( 'blocksy_child_perf_rucss_selector_is_valid(): rejects empty / non-string / whitespace-only', function () {
	assert_same( blocksy_child_perf_rucss_selector_is_valid( '' ), false );
	assert_same( blocksy_child_perf_rucss_selector_is_valid( '   ' ), false );
	assert_same( blocksy_child_perf_rucss_selector_is_valid( null ), false );
	assert_same( blocksy_child_perf_rucss_selector_is_valid( [] ), false );
} );

bc_test( 'blocksy_child_perf_rucss_selector_is_valid(): rejects a trailing combinator', function () {
	assert_same( blocksy_child_perf_rucss_selector_is_valid( '.ct-active>' ), false );
	assert_same( blocksy_child_perf_rucss_selector_is_valid( '.ct-active >' ), false );
	assert_same( blocksy_child_perf_rucss_selector_is_valid( '.ct-active+' ), false );
	assert_same( blocksy_child_perf_rucss_selector_is_valid( '.ct-active~' ), false );
} );

bc_test( 'blocksy_child_perf_rucss_selector_is_valid(): rejects a trailing-dash token', function () {
	assert_same( blocksy_child_perf_rucss_selector_is_valid( '.foo-' ), false );
} );

bc_test( 'blocksy_child_perf_rucss_selector_is_valid(): accepts an ordinary selector', function () {
	assert_same( blocksy_child_perf_rucss_selector_is_valid( '.ct-cart-panel' ), true );
	assert_same( blocksy_child_perf_rucss_selector_is_valid( '[data-id="cart"]' ), true );
	assert_same( blocksy_child_perf_rucss_selector_is_valid( '.product_list_widget li' ), true );
} );

// -----------------------------------------------------------------------
// perfmatters_rucss_excluded_selectors
// -----------------------------------------------------------------------

bc_test( 'perfmatters_rucss_excluded_selectors: returns an array when given null', function () {
	assert_same( is_array( apply_filters( 'perfmatters_rucss_excluded_selectors', null ) ), true );
} );

bc_test( 'perfmatters_rucss_excluded_selectors: flattens the shipped safelist into the result', function () {
	$result = apply_filters( 'perfmatters_rucss_excluded_selectors', [] );

	assert_same( in_array( '.dgwt-wcas-search-wrapp', $result, true ), true, 'fibosearch group present' );
	assert_same( in_array( '.ct-cart-panel', $result, true ), true, 'blocksy-cart-panel group present' );
	assert_same( in_array( '.woocommerce-mini-cart', $result, true ), true, 'mini-cart group present' );
	assert_same( in_array( '.owl-carousel', $result, true ), true, 'carousels group present' );
	assert_same( in_array( '.wcsatt-options', $result, true ), true, 'subscriptions group present' );
	assert_same( in_array( '.has-text-align-left', $result, true ), true, 'gutenberg-utilities group present' );
	assert_same( in_array( '.loading', $result, true ), true, 'runtime-state group present' );
} );

bc_test( 'perfmatters_rucss_excluded_selectors: dedupes an entry already present in the incoming list', function () {
	$result = apply_filters( 'perfmatters_rucss_excluded_selectors', [ '.ct-cart-panel' ] );

	assert_same( count( array_keys( $result, '.ct-cart-panel', true ) ), 1 );
} );

bc_test( 'perfmatters_rucss_excluded_selectors: result is reindexed (sequential integer keys from 0)', function () {
	$result = apply_filters( 'perfmatters_rucss_excluded_selectors', [] );

	assert_same( array_keys( $result ), range( 0, count( $result ) - 1 ) );
} );

bc_test( 'perfmatters_rucss_excluded_selectors: rejects .ct-active> and .foo- and logs both', function () {
	$result = apply_filters( 'perfmatters_rucss_excluded_selectors', [ '.ct-active>', '.foo-' ] );

	assert_same( in_array( '.ct-active>', $result, true ), false );
	assert_same( in_array( '.foo-', $result, true ), false );

	$messages = bc_test_error_log_messages();

	$found = [
		'.ct-active>' => false,
		'.foo-'       => false,
	];

	foreach ( $messages as $message ) {
		if ( false === strpos( $message, 'rejected invalid RUCSS excluded selector' ) ) {
			continue;
		}
		foreach ( array_keys( $found ) as $needle ) {
			if ( false !== strpos( $message, var_export( $needle, true ) ) ) {
				$found[ $needle ] = true;
			}
		}
	}

	assert_same( $found['.ct-active>'], true, '.ct-active> rejection logged' );
	assert_same( $found['.foo-'], true, '.foo- rejection logged' );
} );

bc_test( 'perfmatters_rucss_excluded_selectors: blocksy_child_perf_rucss_selectors filters the shipped safelist before merge', function () {
	$cb = function ( $safelist ) {
		$safelist[] = '.my-site-only-widget';
		return $safelist;
	};
	add_filter( 'blocksy_child_perf_rucss_selectors', $cb );

	$result = apply_filters( 'perfmatters_rucss_excluded_selectors', [] );

	assert_same( in_array( '.my-site-only-widget', $result, true ), true );

	remove_action( 'blocksy_child_perf_rucss_selectors', $cb );
} );

// -----------------------------------------------------------------------
// Every entry in the shipped data file passes the validator.
// -----------------------------------------------------------------------

bc_test( 'inc/perf/data/rucss-safelist.php: every shipped entry passes blocksy_child_perf_rucss_selector_is_valid()', function () {
	$safelist = blocksy_child_perf_data( 'rucss-safelist' );

	assert_same( is_array( $safelist ), true, 'data file returns an array' );
	assert_same( count( $safelist ) > 0, true, 'non-empty' );

	$invalid = [];
	foreach ( $safelist as $selector ) {
		if ( ! blocksy_child_perf_rucss_selector_is_valid( $selector ) ) {
			$invalid[] = $selector;
		}
	}

	assert_same( $invalid, [], 'no shipped entry is rejected by the validator' );
} );

bc_test( 'inc/perf/data/rucss-safelist.php: no duplicate entries across the labelled sub-arrays', function () {
	$safelist = blocksy_child_perf_data( 'rucss-safelist' );

	assert_same( count( $safelist ), count( array_unique( $safelist ) ), 'each selector appears in exactly one labelled group' );
} );

bc_test( 'inc/perf/data/rucss-safelist.php: contains the full 111-selector known-good row plus the documented additions', function () {
	$safelist = blocksy_child_perf_data( 'rucss-safelist' );

	// Spot-check one entry from each of the three 111-selector sub-groups
	// plus the count itself, rather than re-listing all 111 inline here.
	assert_same( in_array( '[class*="dgwt-wcas"]', $safelist, true ), true, 'fibosearch attribute-wildcard entry present' );
	assert_same( in_array( '[data-id="cart"]', $safelist, true ), true, 'blocksy-cart-panel attribute entry present' );
	assert_same( in_array( '.wp-block-blaze-blocksy-product-carousel', $safelist, true ), true, 'woocommerce-cards last entry present' );

	assert_same( count( $safelist ), 153, '111 known-good-row selectors + 42 documented additions' );
} );
