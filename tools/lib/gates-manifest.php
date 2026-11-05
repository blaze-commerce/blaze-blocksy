<?php
/**
 * Manifest gates: module.json schema, ids, module class names.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * manifest_schema, manifest_ids, class_name.
 */
final class ManifestGates {

	/** Same rule as Core\Registry::ID_PATTERN. */
	const ID_PATTERN = '/^[a-z][a-z0-9]*(-[a-z][a-z0-9]*)*$/';

	const STATUSES = [ 'legacy', 'planned', 'beta', 'stable', 'deprecated' ];

	/** Statuses of catalogue stubs (no PHP, never loaded). */
	const STUB_STATUSES = [ 'legacy', 'planned' ];

	const WHERE = [ 'all', 'front', 'wc', 'pdp', 'archive', 'cart', 'checkout', 'account' ];

	/** Required keys => type rule (spec 4.2). */
	const REQUIRED = [
		'id'          => 'string',
		'title'       => 'nonempty',
		'description' => 'string',
		'status'      => 'status',
		'loadable'    => 'bool',
		'owner'       => 'string',
		'since'       => 'string',
		'surfaces'    => 'strings',
		'provides'    => 'strings',
		'uses'        => 'strings',
		'critical'    => 'bool',
		'default'     => 'bool',
		'requires'    => 'object',
		'settings'    => 'object',
		'assets'      => 'assets',
		'hooks'       => 'hooks',
		'replaces'    => 'strings',
	];

	/** Optional keys => type rule. */
	const OPTIONAL = [
		'legacy' => 'legacy',
		'notes'  => 'string',
	];

	/**
	 * Every module folder has a valid module.json (spec 4.2 shape, defaults off, stubs unloadable).
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function manifest_schema( Context $ctx ): array {
		$files = [];
		$notes = [];
		foreach ( $ctx->scoped_modules() as $name => $mod ) {
			if ( ! $mod['exists'] ) {
				$files[] = $mod['dir'];
				$notes[] = "{$mod['dir']} has no module.json";
				continue;
			}
			if ( ! $mod['obj'] instanceof \stdClass ) {
				$files[] = $mod['file'] . ':1';
				$notes[] = "{$mod['file']} is not a JSON object";
				continue;
			}
			foreach ( self::errors( $ctx, $mod['obj'], $mod['dir'] ) as $key => $error ) {
				$files[] = $mod['file'] . ':' . Context::key_line( $mod['raw'], (string) preg_replace( '/[.\[].*$/', '', $key ) );
				$notes[] = "{$name}: {$error}";
			}
		}
		$count = count( $ctx->scoped_modules() );
		return Context::findings( 'manifest_schema', 'fail', $files, "{$count} manifests match the schema", 'manifest schema errors', $notes );
	}

	/**
	 * Schema errors of one manifest, keyed by key path.
	 *
	 * @param Context   $ctx Context.
	 * @param \stdClass $m   Manifest decoded as objects.
	 * @param string    $dir Module folder (relative).
	 * @return array<string,string>
	 */
	private static function errors( Context $ctx, \stdClass $m, string $dir ): array {
		$errors = [];
		$vars   = get_object_vars( $m );
		$rules  = self::REQUIRED + self::OPTIONAL;

		foreach ( self::REQUIRED as $key => $rule ) {
			if ( ! array_key_exists( $key, $vars ) ) {
				$errors[ $key ] = "missing \"{$key}\"";
			}
		}
		foreach ( $vars as $key => $value ) {
			if ( ! isset( $rules[ $key ] ) ) {
				$errors[ $key ] = "unknown key \"{$key}\"";
				continue;
			}
			$error = self::check( $rules[ $key ], $value );
			if ( null !== $error ) {
				$errors[ $key ] = "\"{$key}\" {$error}";
			}
		}
		if ( isset( $errors['assets'] ) ) {
			return $errors;
		}

		if ( ( $vars['default'] ?? false ) === true ) {
			$errors['default'] = '"default" must be false (every module defaults off)';
		}
		$status = $vars['status'] ?? '';
		if ( in_array( $status, self::STUB_STATUSES, true ) && ( $vars['loadable'] ?? null ) !== false ) {
			$errors['loadable'] = "status \"{$status}\" is a catalogue stub and needs \"loadable\": false";
		}
		if ( ( $vars['loadable'] ?? false ) === true ) {
			foreach ( (array) ( $vars['assets'] ?? [] ) as $i => $asset ) {
				if ( ! is_file( $ctx->path( $dir . '/' . $asset->file ) ) ) {
					$errors[ "assets[{$i}]" ] = "asset {$asset->file} does not exist in {$dir}";
				}
			}
		}
		return $errors;
	}

	/**
	 * One type rule; null when the value is fine.
	 *
	 * @param string $rule  Rule name.
	 * @param mixed  $value Value (objects decoded as stdClass).
	 * @return string|null
	 */
	private static function check( string $rule, $value ): ?string {
		switch ( $rule ) {
			case 'string':
				return is_string( $value ) ? null : 'must be a string';
			case 'nonempty':
				return is_string( $value ) && '' !== trim( $value ) ? null : 'must be a non-empty string';
			case 'bool':
				return is_bool( $value ) ? null : 'must be true or false';
			case 'object':
				return $value instanceof \stdClass ? null : 'must be an object';
			case 'status':
				return in_array( $value, self::STATUSES, true ) ? null : 'must be one of ' . implode( ', ', self::STATUSES );
			case 'strings':
				return self::is_strings( $value ) ? null : 'must be an array of strings';
			case 'hooks':
				if ( ! $value instanceof \stdClass || ! self::is_strings( $value->filters ?? null ) || ! self::is_strings( $value->actions ?? null ) ) {
					return 'must be { "filters": [strings], "actions": [strings] }';
				}
				return null;
			case 'legacy':
				if ( ! $value instanceof \stdClass ) {
					return 'must be an object';
				}
				foreach ( get_object_vars( $value ) as $k => $v ) {
					if ( ! in_array( $k, [ 'files', 'constants', 'filters' ], true ) || ! self::is_strings( $v ) ) {
						return 'may hold only "files", "constants", "filters" (arrays of strings)';
					}
				}
				return null;
			case 'assets':
				return self::check_assets( $value );
		}
		return "has unknown rule {$rule}";
	}

	/**
	 * Assets: [{ file, where[], deps[] }], where ⊆ WHERE.
	 *
	 * @param mixed $value Value.
	 * @return string|null
	 */
	private static function check_assets( $value ): ?string {
		if ( ! is_array( $value ) ) {
			return 'must be an array';
		}
		foreach ( $value as $i => $asset ) {
			if ( ! $asset instanceof \stdClass || ! is_string( $asset->file ?? null ) || '' === $asset->file ) {
				return "[{$i}] needs a \"file\" string";
			}
			if ( ! self::is_strings( $asset->where ?? null ) || ! $asset->where ) {
				return "[{$i}] needs a non-empty \"where\" array";
			}
			$bad = array_diff( $asset->where, self::WHERE );
			if ( $bad ) {
				return "[{$i}] has unknown where " . implode( ', ', $bad ) . ' (allowed: ' . implode( ', ', self::WHERE ) . ')';
			}
			if ( isset( $asset->deps ) && ! self::is_strings( $asset->deps ) ) {
				return "[{$i}] \"deps\" must be an array of strings";
			}
		}
		return null;
	}

	/**
	 * True for a JSON array of strings.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	private static function is_strings( $value ): bool {
		if ( ! is_array( $value ) || array_values( $value ) !== $value ) {
			return false;
		}
		foreach ( $value as $item ) {
			if ( ! is_string( $item ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Ids are valid, equal their folder name and are unique.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function manifest_ids( Context $ctx ): array {
		$files = [];
		$notes = [];
		$seen  = [];
		foreach ( $ctx->modules() as $name => $mod ) {
			$id = is_string( $mod['data']['id'] ?? null ) ? $mod['data']['id'] : null;
			if ( null !== $id ) {
				$seen[ $id ][] = $name;
			}
			if ( ! $ctx->in_scope( $name ) || null === $mod['data'] ) {
				continue;
			}
			$at = $mod['file'] . ':' . Context::key_line( $mod['raw'], 'id' );
			if ( null === $id || 1 !== preg_match( self::ID_PATTERN, $id ) ) {
				$files[] = $at;
				$notes[] = "{$name}: id must be lowercase kebab-case";
			} elseif ( $id !== $name ) {
				$files[] = $at;
				$notes[] = "{$name}: id '{$id}' does not match its folder";
			}
		}
		foreach ( $seen as $id => $folders ) {
			if ( count( $folders ) < 2 ) {
				continue;
			}
			foreach ( $folders as $name ) {
				if ( $ctx->in_scope( $name ) ) {
					$files[] = "modules/{$name}/module.json:" . Context::key_line( $ctx->modules()[ $name ]['raw'], 'id' );
					$notes[] = "id '{$id}' used by " . implode( ', ', $folders );
				}
			}
		}
		return Context::findings( 'manifest_ids', 'fail', $files, 'ids are kebab-case, unique and match their folders', 'bad module ids', $notes );
	}

	/**
	 * Loadable modules have modules/<id>/Module.php declaring Blaze\Child\Modules\<Studly>\Module
	 * extends Blaze\Child\Core\Module (R2: stubs with loadable false are skipped).
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function class_name( Context $ctx ): array {
		$files   = [];
		$notes   = [];
		$checked = 0;
		foreach ( $ctx->scoped_modules() as $name => $mod ) {
			if ( true !== ( $mod['data']['loadable'] ?? null ) ) {
				continue;
			}
			++$checked;
			$rel    = "{$mod['dir']}/Module.php";
			$studly = self::studly( $name );
			if ( self::kebab( $studly ) !== $name ) {
				$files[] = $mod['file'] . ':1';
				$notes[] = "{$name}: id does not round-trip through the autoloader ({$studly})";
				continue;
			}
			if ( ! is_file( $ctx->path( $rel ) ) ) {
				$files[] = $rel;
				$notes[] = "{$name}: loadable module has no Module.php";
				continue;
			}
			$error = self::class_error( $ctx->read( $rel ), "Blaze\\Child\\Modules\\{$studly}" );
			if ( null !== $error ) {
				$files[] = $rel . ':' . $error[0];
				$notes[] = "{$name}: {$error[1]}";
			}
		}
		return Context::findings( 'class_name', 'fail', $files, "{$checked} loadable modules declare the expected class", 'module class mismatches', $notes );
	}

	/**
	 * Null when the source declares `<namespace>\Module extends Blaze\Child\Core\Module`, else [line, message].
	 *
	 * @param string $php       Source.
	 * @param string $expected_ns Expected namespace.
	 * @return array{0:int,1:string}|null
	 */
	private static function class_error( string $php, string $expected_ns ): ?array {
		if ( ! preg_match( '/^\s*namespace\s+([^;{\s]+)\s*;/m', $php, $ns, PREG_OFFSET_CAPTURE ) ) {
			return [ 1, "no namespace; expected {$expected_ns}" ];
		}
		if ( $ns[1][0] !== $expected_ns ) {
			return [ Context::line_at( $php, $ns[0][1] ), "namespace {$ns[1][0]}; expected {$expected_ns}" ];
		}
		if ( ! preg_match( '/^\s*(?:(?:final|abstract)\s+)?class\s+Module\s+extends\s+(\\\\?[A-Za-z_][A-Za-z0-9_\\\\]*)/m', $php, $cl, PREG_OFFSET_CAPTURE ) ) {
			return [ 1, 'no "class Module extends ..." declaration' ];
		}
		$parent = ltrim( $cl[1][0], '\\' );
		if ( 'Blaze\\Child\\Core\\Module' === $parent ) {
			return null;
		}
		// An imported alias: use Blaze\Child\Core\Module [as X];
		if ( preg_match_all( '/^\s*use\s+\\\\?Blaze\\\\Child\\\\Core\\\\Module(?:\s+as\s+(\w+))?\s*;/m', $php, $uses, PREG_SET_ORDER ) ) {
			foreach ( $uses as $use ) {
				if ( ( $use[1] ?? '' ) === $parent || ( empty( $use[1] ) && 'Module' === $parent ) ) {
					return null;
				}
			}
		}
		return [ Context::line_at( $php, $cl[0][1] ), "Module extends {$cl[1][0]}; expected \\Blaze\\Child\\Core\\Module" ];
	}

	/**
	 * Module id to StudlyCaps (hide-cart-page -> HideCartPage, legacy-v1 -> LegacyV1).
	 *
	 * @param string $id Id.
	 * @return string
	 */
	public static function studly( string $id ): string {
		return implode( '', array_map( 'ucfirst', explode( '-', $id ) ) );
	}

	/**
	 * The autoloader's reverse mapping (src/bootstrap.php blaze_child_autoload_path()).
	 *
	 * @param string $studly StudlyCaps.
	 * @return string
	 */
	private static function kebab( string $studly ): string {
		return strtolower( (string) preg_replace( '/(?<!^)[A-Z]/', '-$0', $studly ) );
	}
}
