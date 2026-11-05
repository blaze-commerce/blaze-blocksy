<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * v1 constant shims, each defined only when absent so nothing is redefined.
 */
final class Legacy {

	/**
	 * Defines BLOCKSY_CHILD_* (v1) and BLAZE_BLOCKSY_* (older shim) constants.
	 *
	 * @return void
	 */
	public static function define(): void {
		$map = [
			'BLOCKSY_CHILD_VERSION' => BLAZE_CHILD_VERSION,
			'BLOCKSY_CHILD_PATH'    => BLAZE_CHILD_PATH,
			'BLOCKSY_CHILD_URL'     => BLAZE_CHILD_URL,
			'BLAZE_BLOCKSY_URL'     => BLAZE_CHILD_URL,
			'BLAZE_BLOCKSY_PATH'    => BLAZE_CHILD_PATH,
		];

		foreach ( $map as $name => $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound -- the v1 BLOCKSY_CHILD_*/BLAZE_BLOCKSY_* names are the compatibility contract.
			}
		}
	}
}
