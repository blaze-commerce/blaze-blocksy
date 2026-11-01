<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Reads module manifests, resolves which modules are on (spec 4.4), boots them.
 */
final class Registry {

	const ID_PATTERN = '/^[a-z][a-z0-9]*(-[a-z][a-z0-9]*)*$/';

	/**
	 * @var array<string,Module>
	 */
	private static $instances = [];

	/**
	 * @var string[]
	 */
	private static $critical_failures = [];

	/**
	 * @var string
	 */
	private $dir;

	/**
	 * @var array|null
	 */
	private $manifests = null;

	/**
	 * @param string $modules_dir Directory holding <id>/module.json.
	 */
	public function __construct( string $modules_dir ) {
		$this->dir = rtrim( $modules_dir, '/\\' );
	}

	/**
	 * Valid manifests by id, sorted; legacy-v1 first.
	 *
	 * @return array<string,array>
	 */
	public function manifests(): array {
		if ( null !== $this->manifests ) {
			return $this->manifests;
		}

		$out   = [];
		$files = glob( $this->dir . '/*/module.json' );
		foreach ( $files ? $files : [] as $file ) {
			$dirname  = basename( dirname( $file ) );
			$manifest = json_decode( (string) file_get_contents( $file ), true );
			$id       = is_array( $manifest ) && is_string( $manifest['id'] ?? null ) ? $manifest['id'] : '';

			if ( ! is_array( $manifest ) ) {
				Health::record( 'manifest_invalid', "{$dirname}/module.json is not valid JSON", [ 'module' => $dirname ] );
			} elseif ( 1 !== preg_match( self::ID_PATTERN, $id ) ) {
				Health::record( 'manifest_invalid', "{$dirname}/module.json has an invalid id", [ 'module' => $dirname ] );
			} elseif ( $id !== $dirname ) {
				Health::record( 'manifest_invalid', "{$dirname}/module.json id '{$id}' does not match its folder", [ 'module' => $dirname ] );
			} else {
				$out[ $id ] = $manifest;
			}
		}

		ksort( $out );
		if ( isset( $out['legacy-v1'] ) ) {
			$out = [ 'legacy-v1' => $out['legacy-v1'] ] + $out;
		}

		$this->manifests = $out;
		return $out;
	}

	/**
	 * Ordered ids of enabled modules.
	 *
	 * @param array<string,bool> $site_modules Site config `modules` (id => bool), in site order.
	 * @return string[]
	 */
	public function resolve( array $site_modules ): array {
		$manifests = $this->manifests();

		foreach ( array_keys( $site_modules ) as $id ) {
			if ( ! isset( $manifests[ $id ] ) ) {
				Health::record( 'unknown_module', "Site config names unknown module '{$id}'", [ 'module' => (string) $id ] );
			}
		}

		$enabled = [];
		foreach ( $manifests as $id => $manifest ) {
			$on = ! empty( $manifest['default'] );
			if ( array_key_exists( $id, $site_modules ) ) {
				$on = (bool) $site_modules[ $id ];
			}

			$filtered = (bool) apply_filters( 'blaze_child/module_enabled', $on, $id );
			// R1: a module that replaces legacy files is switched ON by site config only.
			if ( $filtered && ! $on && ! empty( $manifest['legacy']['files'] ) ) {
				$filtered = false;
			}
			$on = $filtered;

			if ( isset( $manifest['loadable'] ) && false === $manifest['loadable'] ) {
				$on = false;
			}
			if ( $on ) {
				$enabled[] = $id;
			}
		}

		$killed  = self::kill_switch();
		$enabled = array_values( array_diff( $enabled, $killed ) );

		return $this->resolve_clashes( $enabled, array_keys( $site_modules ) );
	}

	/**
	 * Ids named in BLAZE_CHILD_DISABLE_MODULES.
	 *
	 * @return string[]
	 */
	private static function kill_switch(): array {
		if ( ! defined( 'BLAZE_CHILD_DISABLE_MODULES' ) ) {
			return [];
		}
		$value = BLAZE_CHILD_DISABLE_MODULES;
		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}
		return is_array( $value ) ? array_values( array_filter( array_map( 'trim', array_map( 'strval', $value ) ), 'strlen' ) ) : [];
	}

	/**
	 * Keeps one module per surface: the replacer, else the first in site order.
	 *
	 * @param string[] $enabled    Enabled ids in manifest order.
	 * @param array    $site_order Site config ids in their order.
	 * @return string[]
	 */
	private function resolve_clashes( array $enabled, array $site_order ): array {
		$manifests = $this->manifests();
		$rank      = [];
		foreach ( array_merge( $site_order, $enabled ) as $id ) {
			if ( ! isset( $rank[ $id ] ) ) {
				$rank[ $id ] = count( $rank );
			}
		}

		$owner   = [];
		$dropped = [];
		foreach ( $enabled as $id ) {
			foreach ( (array) ( $manifests[ $id ]['surfaces'] ?? [] ) as $surface ) {
				if ( isset( $dropped[ $id ] ) ) {
					break;
				}
				if ( ! isset( $owner[ $surface ] ) ) {
					$owner[ $surface ] = $id;
					continue;
				}
				$other = $owner[ $surface ];
				if ( in_array( $other, (array) ( $manifests[ $id ]['replaces'] ?? [] ), true ) ) {
					$keep = $id;
					$lose = $other;
				} elseif ( in_array( $id, (array) ( $manifests[ $other ]['replaces'] ?? [] ), true ) ) {
					$keep = $other;
					$lose = $id;
				} else {
					$keep = $rank[ $id ] < $rank[ $other ] ? $id : $other;
					$lose = $keep === $id ? $other : $id;
				}
				$dropped[ $lose ]  = true;
				$owner[ $surface ] = $keep;
				Health::record( 'surface_clash', "Modules '{$keep}' and '{$lose}' both declare surface '{$surface}'; '{$lose}' skipped", [ 'surface' => $surface, 'kept' => $keep, 'dropped' => $lose ] );
			}
		}

		return array_values( array_diff( $enabled, array_keys( $dropped ) ) );
	}

	/**
	 * Instantiates and boots the given modules.
	 *
	 * @param string[] $ids             Enabled ids.
	 * @param array    $settings_by_id  id => settings array.
	 * @return array<string,Module>
	 */
	public function boot( array $ids, array $settings_by_id ): array {
		$manifests               = $this->manifests();
		self::$instances         = [];
		self::$critical_failures = [];
		$booted                  = [];

		foreach ( $ids as $id ) {
			if ( ! isset( $manifests[ $id ] ) ) {
				continue;
			}
			$manifest = $manifests[ $id ];

			$unmet = Compat::unmet( $manifest );
			if ( $unmet ) {
				Health::record( 'requires_unmet', "Module '{$id}' skipped: " . implode( '; ', $unmet ), [ 'module' => $id, 'unmet' => $unmet ] );
				continue;
			}

			try {
				$class = 'Blaze\\Child\\Modules\\' . Theme::studly( $id ) . '\\Module';
				if ( ! class_exists( $class, false ) ) {
					$file = $this->dir . '/' . $id . '/Module.php';
					if ( is_file( $file ) ) {
						require_once $file;
					}
				}
				if ( ! class_exists( $class ) || ! is_subclass_of( $class, Module::class ) ) {
					throw new \RuntimeException( "Module class {$class} not found or not a Core\\Module" );
				}
				$settings = is_array( $settings_by_id[ $id ] ?? null ) ? $settings_by_id[ $id ] : [];
				$module   = new $class( $manifest, $settings );
				$module->boot();
				self::$instances[ $id ] = $module;
				$booted[ $id ]          = $module;
			} catch ( \Throwable $e ) {
				Health::record( 'module_failed', "Module '{$id}' failed to boot: " . $e->getMessage(), [ 'module' => $id ] );
				if ( ! empty( $manifest['critical'] ) ) {
					self::$critical_failures[] = $id;
				}
			}
		}

		return $booted;
	}

	/**
	 * Ids of critical modules that failed to boot in the last boot().
	 *
	 * @return string[]
	 */
	public static function critical_failures(): array {
		return self::$critical_failures;
	}

	/**
	 * The booted instance for an id.
	 *
	 * @param string $id Module id.
	 * @return Module|null
	 */
	public static function instance( string $id ): ?Module {
		return self::$instances[ $id ] ?? null;
	}
}
