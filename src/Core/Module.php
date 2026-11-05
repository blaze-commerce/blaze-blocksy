<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Base class of every module (modules/<id>/Module.php).
 */
abstract class Module {

	/**
	 * @var array
	 */
	private $manifest;

	/**
	 * @var array
	 */
	private $settings;

	/**
	 * @param array $manifest Parsed module.json.
	 * @param array $settings Site-config settings for this module.
	 */
	final public function __construct( array $manifest, array $settings ) {
		$this->manifest = $manifest;
		$this->settings = $settings;
	}

	/**
	 * Registers the module's hooks.
	 *
	 * @return void
	 */
	abstract public function boot(): void;

	/**
	 * @return array
	 */
	final public function manifest(): array {
		return $this->manifest;
	}

	/**
	 * @return string
	 */
	final public function id(): string {
		return (string) ( $this->manifest['id'] ?? '' );
	}

	/**
	 * True when site config sets this key explicitly (a manifest default does not count).
	 *
	 * @param string $key Setting key.
	 * @return bool
	 */
	final public function has_site_setting( string $key ): bool {
		return array_key_exists( $key, $this->settings );
	}

	/**
	 * Site setting over manifest default, then the blaze_child/<id>/settings filter.
	 *
	 * @param string $key Setting key.
	 * @return mixed Null when unset.
	 */
	final public function setting( string $key ) {
		$merged = [];
		$schema = is_array( $this->manifest['settings'] ?? null ) ? $this->manifest['settings'] : [];
		foreach ( $schema as $name => $def ) {
			if ( is_array( $def ) && array_key_exists( 'default', $def ) ) {
				$merged[ $name ] = $def['default'];
			}
		}
		$merged = array_merge( $merged, $this->settings );
		$merged = apply_filters( "blaze_child/{$this->id()}/settings", $merged );
		return is_array( $merged ) ? ( $merged[ $key ] ?? null ) : null;
	}
}
