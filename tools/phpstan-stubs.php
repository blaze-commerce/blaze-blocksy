<?php
/**
 * PHPStan config include: adds the WooCommerce stubs to scanFiles when they are installed (CI installs
 * php-stubs/woocommerce-stubs with Composer, globally; the theme itself has no Composer dependencies).
 *
 * @package Blocksy_Child
 */

$blaze_child_homes = array_filter(
	[
		dirname( __DIR__ ),
		(string) getenv( 'COMPOSER_HOME' ),
		(string) getenv( 'HOME' ) . '/.composer',
		(string) getenv( 'HOME' ) . '/.config/composer',
	]
);

foreach ( $blaze_child_homes as $blaze_child_home ) {
	$blaze_child_stub = $blaze_child_home . '/vendor/php-stubs/woocommerce-stubs/woocommerce-stubs.php';
	if ( is_file( $blaze_child_stub ) ) {
		return [ 'parameters' => [ 'scanFiles' => [ $blaze_child_stub ] ] ];
	}
}

return [];
