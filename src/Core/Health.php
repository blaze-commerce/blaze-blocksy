<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Bounded, de-duplicated record of things that went wrong (option blaze_child_health).
 */
final class Health {

	const OPTION = 'blaze_child_health';
	const MAX    = 50;
	const WINDOW = 3600;

	/**
	 * Appends an entry; the same code+message is skipped while younger than 1 hour, else refreshed (moved to newest).
	 *
	 * @param string $code    Machine code.
	 * @param string $message Human message.
	 * @param array  $context Extra data.
	 * @return void
	 */
	public static function record( string $code, string $message, array $context = [] ): void {
		$entries = [];
		$now     = time();
		foreach ( self::all() as $entry ) {
			if ( $entry['code'] === $code && $entry['message'] === $message ) {
				// A persistent condition must not write wp_options on every request.
				if ( $now - $entry['time'] < self::WINDOW ) {
					return;
				}
				continue;
			}
			$entries[] = $entry;
		}
		$entries[] = [
			'code'    => $code,
			'message' => $message,
			'context' => $context,
			'time'    => $now,
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
		if ( ! is_array( $value ) ) {
			return [];
		}
		$out = [];
		foreach ( $value as $entry ) {
			if ( is_array( $entry ) && is_string( $entry['code'] ?? null ) && is_string( $entry['message'] ?? null ) && is_int( $entry['time'] ?? null ) ) {
				$entry['context'] = is_array( $entry['context'] ?? null ) ? $entry['context'] : [];
				$out[]            = $entry;
			}
		}
		return $out;
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
