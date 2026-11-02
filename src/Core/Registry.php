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
			$dirname = basename( dirname( $file ) );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file, not a remote URL.
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
	 * On/off for one module from manifest default, site config and kill switch only (no filter: site code
	 * hasn't loaded yet). Used before site code, e.g. for legacy-v1's core.
	 *
	 * @param string             $id           Module id.
	 * @param array<string,bool> $site_modules Site config `modules`.
	 * @return bool
	 */
	public function enabled_before_site_code( string $id, array $site_modules ): bool {
		$manifests = $this->manifests();
		$on        = ! empty( $manifests[ $id ]['default'] );
		if ( array_key_exists( $id, $site_modules ) ) {
			$on = (bool) $site_modules[ $id ];
		}
		return $on && ! in_array( $id, self::kill_switch(), true );
	}

	/**
	 * Files legacy-v1 must not load because a ported module replaces them (R1).
	 *
	 * The union of skip_files_by_module(), de-duplicated.
	 *
	 * @param array<string,bool> $site_modules Site config `modules`.
	 * @return string[]
	 */
	public function skip_files( array $site_modules ): array {
		$files = [];
		foreach ( $this->skip_files_by_module( $site_modules ) as $module_files ) {
			$files = array_merge( $files, $module_files );
		}
		return array_values( array_unique( $files ) );
	}

	/**
	 * Which module put which v1 files on the skip list (R1, R13).
	 *
	 * Every module other than legacy-v1 that is on by enabled_before_site_code() (default + site config +
	 * kill switch; the module_enabled filter cannot be consulted: site code has not loaded) contributes its
	 * `legacy.files`. Catalogue stubs (loadable: false) have no replacement code, so they never skip anything.
	 *
	 * @param array<string,bool> $site_modules Site config `modules`.
	 * @return array<string,string[]> Module id => its legacy.files.
	 */
	public function skip_files_by_module( array $site_modules ): array {
		$by_module = [];
		foreach ( $this->manifests() as $id => $manifest ) {
			if ( 'legacy-v1' === $id || ( isset( $manifest['loadable'] ) && false === $manifest['loadable'] ) ) {
				continue;
			}
			if ( ! $this->enabled_before_site_code( $id, $site_modules ) ) {
				continue;
			}
			$files = [];
			foreach ( (array) ( $manifest['legacy']['files'] ?? [] ) as $file ) {
				if ( is_string( $file ) ) {
					$files[] = $file;
				}
			}
			if ( $files ) {
				$by_module[ $id ] = $files;
			}
		}
		return $by_module;
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
		return is_array( $value ) ? array_values( array_diff( array_map( 'trim', array_map( 'strval', $value ) ), [ '' ] ) ) : [];
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
				$dropped[ $lose ] = true;
				foreach ( $owner as $s => $o ) {
					if ( $o === $lose ) {
						unset( $owner[ $s ] );
					}
				}
				$owner[ $surface ] = $keep;
				Health::record(
					'surface_clash',
					"Modules '{$keep}' and '{$lose}' both declare surface '{$surface}'; '{$lose}' skipped",
					[
						'surface' => $surface,
						'kept'    => $keep,
						'dropped' => $lose,
					]
				);
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

			try {
				$unmet = Compat::unmet( $manifest );
				if ( $unmet ) {
					Health::record(
						'requires_unmet',
						"Module '{$id}' skipped: " . implode( '; ', $unmet ),
						[
							'module' => $id,
							'unmet'  => $unmet,
						]
					);
					if ( ! empty( $manifest['critical'] ) ) {
						self::$critical_failures[] = $id;
					}
					continue;
				}

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
	 * Ids of critical modules that failed to boot (exception, missing class) or were skipped for unmet requirements in the last boot().
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
