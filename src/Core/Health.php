<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Bounded, de-duplicated record of things that went wrong (option blaze_child_health).
 */
final class Health {

	const OPTION = 'blaze_child_health';
	const MAX    = 50;

	/**
	 * Appends an entry; the same code+message only refreshes its time (and moves to newest).
	 *
	 * @param string $code    Machine code.
	 * @param string $message Human message.
	 * @param array  $context Extra data.
	 * @return void
	 */
	public static function record( string $code, string $message, array $context = [] ): void {
		$entries = [];
		foreach ( self::all() as $entry ) {
			if ( ! ( $entry['code'] === $code && $entry['message'] === $message ) ) {
				$entries[] = $entry;
			}
		}
		$entries[] = [
			'code'    => $code,
			'message' => $message,
			'context' => $context,
			'time'    => time(),
		];
		update_option( self::OPTION, array_slice( $entries, -self::MAX ), false );
	}

	/**
	 * All entries, oldest first.
	 *
	 * @return array
	 */
	public static function all(): array {
		$value = get_option( self::OPTION, [] );
		return is_array( $value ) ? array_values( $value ) : [];
	}

	/**
	 * Empties the record.
	 *
	 * @return void
	 */
	public static function clear(): void {
		delete_option( self::OPTION );
	}
}
