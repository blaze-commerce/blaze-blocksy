<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Checks a manifest's `requires` against the running site.
 */
final class Compat {

	/**
	 * Human-readable unmet requirements; empty when all are met.
	 *
	 * @param array $manifest Module manifest.
	 * @return string[]
	 */
	public static function unmet( array $manifest ): array {
		$unmet    = [];
		$requires = is_array( $manifest['requires'] ?? null ) ? $manifest['requires'] : [];

		$wc = $requires['plugins']['woocommerce'] ?? null;
		if ( is_string( $wc ) && '' !== $wc ) {
			if ( ! class_exists( 'WooCommerce' ) ) {
				$unmet[] = "WooCommerce {$wc} required but WooCommerce is not active";
			} else {
				$have = defined( 'WC_VERSION' ) ? (string) WC_VERSION : '0';
				if ( ! self::satisfies( $have, $wc ) ) {
					$unmet[] = "WooCommerce {$wc} required, found {$have}";
				}
			}
		}

		$blocksy = $requires['blocksy'] ?? null;
		if ( is_string( $blocksy ) && '' !== $blocksy ) {
			$have = wp_get_theme( 'blocksy' )->get( 'Version' );
			if ( ! is_string( $have ) || '' === $have ) {
				$unmet[] = "Blocksy {$blocksy} required but the Blocksy parent theme was not found";
			} elseif ( ! self::satisfies( $have, $blocksy ) ) {
				$unmet[] = "Blocksy {$blocksy} required, found {$have}";
			}
		}

		return $unmet;
	}

	/**
	 * True when $have satisfies a constraint like '>=9.0' (bare version means '>=').
	 *
	 * @param string $have       Installed version.
	 * @param string $constraint Constraint.
	 * @return bool
	 */
	private static function satisfies( string $have, string $constraint ): bool {
		if ( ! preg_match( '/^\s*(>=|<=|==|!=|>|<|=)?\s*(\S+)\s*$/', $constraint, $m ) ) {
			return false;
		}
		$op = '' === $m[1] ? '>=' : $m[1];
		return (bool) version_compare( $have, $m[2], $op );
	}
}
