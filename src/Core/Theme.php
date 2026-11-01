<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Boot order owner (spec 4.3). Registry/SiteLayer wiring arrives in Task 4.
 */
final class Theme {

	/**
	 * Boots the theme. Intentionally empty until Task 4.
	 *
	 * @return void
	 */
	public static function boot(): void {
	}

	/**
	 * 'hide-cart-page' -> 'HideCartPage'.
	 *
	 * @param string $id Module id (kebab-case).
	 * @return string
	 */
	public static function studly( string $id ): string {
		return str_replace( ' ', '', ucwords( str_replace( '-', ' ', $id ) ) );
	}

	/**
	 * Absolute path of a module's Module.php.
	 *
	 * @param string $id Module id (kebab-case).
	 * @return string
	 */
	public static function module_class_path( string $id ): string {
		return BLAZE_CHILD_PATH . 'modules/' . $id . '/Module.php';
	}
}
