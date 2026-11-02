<?php
/**
 * Duplicate global function/class names: two unguarded declarations are an uncatchable redeclare fatal.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * duplicate_symbols.
 */
final class SymbolGates {

	/** Folders whose declarations can meet at runtime. */
	const SCAN = [ 'inc/', 'src/', 'modules/' ];

	/**
	 * Fails when a function or class (compared by fully qualified, case-insensitive name) is declared
	 * twice without a function_exists()/class_exists() guard. Methods, closures, tests/ and fixtures/ are ignored.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function duplicate_symbols( Context $ctx ): array {
		$seen  = [];
		$scan  = $ctx->files_under( self::SCAN, [ 'php' ] );
		$total = 0;
		foreach ( $scan as $rel ) {
			foreach ( self::declarations( $ctx->read( $rel ) ) as $decl ) {
				++$total;
				$seen[ strtolower( $decl['kind'] . ' ' . $decl['name'] ) ][] = [ $decl, "{$rel}:{$decl['line']}" ];
			}
		}

		$files = [];
		$notes = [];
		$own   = null === $ctx->module ? null : "modules/{$ctx->module}/";
		foreach ( $seen as $list ) {
			if ( count( $list ) < 2 ) {
				continue;
			}
			$where = array_column( $list, 1 );
			if ( null !== $own && ! array_filter(
				$where,
				function ( string $w ) use ( $own ): bool {
					return 0 === strpos( $w, $own );
				}
			) ) {
				continue;
			}
			$files   = array_merge( $files, $where );
			$notes[] = "{$list[0][0]['kind']} {$list[0][0]['name']} declared " . count( $list ) . ' times';
		}
		return Context::findings( 'duplicate_symbols', 'fail', $files, "{$total} unguarded global declarations in inc/, src/, modules/, no duplicates", 'duplicate global declarations', $notes );
	}

	/**
	 * Unguarded global function and class-like declarations of one file.
	 *
	 * @param string $php Source.
	 * @return array<int,array{kind:string,name:string,line:int}>
	 */
	public static function declarations( string $php ): array {
		$tokens       = token_get_all( $php );
		$count        = count( $tokens );
		$class_kinds  = [ T_CLASS, T_INTERFACE, T_TRAIT ];
		$out          = [];
		$ns           = '';
		$depth        = 0;
		$class_depths = [];
		$guards       = [];
		$pending      = [
			'class' => false,
			'guard' => null,
		];

		if ( defined( 'T_ENUM' ) ) {
			$class_kinds[] = T_ENUM;
		}

		for ( $i = 0; $i < $count; $i++ ) {
			$t  = $tokens[ $i ];
			$id = is_array( $t ) ? $t[0] : $t;

			if ( '{' === $id || T_CURLY_OPEN === $id || T_DOLLAR_OPEN_CURLY_BRACES === $id ) {
				++$depth;
				if ( $pending['class'] ) {
					$class_depths[]   = $depth;
					$pending['class'] = false;
				}
				if ( null !== $pending['guard'] ) {
					$guards[]         = [ $depth, $pending['guard'] ];
					$pending['guard'] = null;
				}
				continue;
			}
			if ( '}' === $id ) {
				if ( $class_depths && end( $class_depths ) === $depth ) {
					array_pop( $class_depths );
				}
				if ( $guards && end( $guards )[0] === $depth ) {
					array_pop( $guards );
				}
				--$depth;
				continue;
			}
			if ( ! is_array( $t ) ) {
				continue;
			}

			if ( T_NAMESPACE === $id ) {
				$j  = StyleGates::next_significant( $tokens, $i );
				$ns = ( null !== $j && is_array( $tokens[ $j ] ) && in_array( $tokens[ $j ][0], [ T_STRING, T_NAME_QUALIFIED ], true ) ) ? $tokens[ $j ][1] : '';
			} elseif ( T_IF === $id || T_ELSEIF === $id ) {
				$pending['guard'] = self::guard_names( $tokens, $i );
			} elseif ( in_array( $id, $class_kinds, true ) ) {
				$prev = self::prev_significant( $tokens, $i );
				if ( null !== $prev && is_array( $tokens[ $prev ] ) && T_DOUBLE_COLON === $tokens[ $prev ][0] ) {
					continue; // Foo::class.
				}
				$pending['class'] = true;
				$j                = StyleGates::next_significant( $tokens, $i );
				if ( null !== $j && is_array( $tokens[ $j ] ) && T_STRING === $tokens[ $j ][0] && ! self::guarded( $guards, $tokens[ $j ][1] ) ) {
					$out[] = [
						'kind' => 'class',
						'name' => self::qualify( $ns, $tokens[ $j ][1] ),
						'line' => $tokens[ $j ][2],
					];
				}
			} elseif ( T_FUNCTION === $id && ! $class_depths ) {
				$prev = self::prev_significant( $tokens, $i );
				if ( null !== $prev && is_array( $tokens[ $prev ] ) && T_USE === $tokens[ $prev ][0] ) {
					continue; // use function x;
				}
				$j = StyleGates::next_significant( $tokens, $i );
				if ( null !== $j && '&' === $tokens[ $j ] ) {
					$j = StyleGates::next_significant( $tokens, $j );
				}
				if ( null !== $j && is_array( $tokens[ $j ] ) && T_STRING === $tokens[ $j ][0] && ! self::guarded( $guards, $tokens[ $j ][1] ) ) {
					$out[] = [
						'kind' => 'function',
						'name' => self::qualify( $ns, $tokens[ $j ][1] ),
						'line' => $tokens[ $j ][2],
					];
				}
			}
		}
		return $out;
	}

	/**
	 * Names checked by function_exists()/class_exists() in an if condition, or null when the
	 * condition has none or the if has no braced body.
	 *
	 * @param array $tokens Tokens.
	 * @param int   $i      Index of T_IF/T_ELSEIF.
	 * @return string[]|null
	 */
	private static function guard_names( array $tokens, int $i ): ?array {
		$count = count( $tokens );
		$j     = StyleGates::next_significant( $tokens, $i );
		if ( null === $j || '(' !== $tokens[ $j ] ) {
			return null;
		}
		$names  = [];
		$exists = false;
		for ( $depth = 0; $j < $count; $j++ ) {
			$t = $tokens[ $j ];
			if ( '(' === $t ) {
				++$depth;
			} elseif ( ')' === $t ) {
				if ( 0 === --$depth ) {
					break;
				}
			} elseif ( is_array( $t ) && T_STRING === $t[0] && in_array( strtolower( $t[1] ), [ 'function_exists', 'class_exists', 'interface_exists', 'trait_exists', 'enum_exists' ], true ) ) {
				$exists = true;
			} elseif ( $exists && is_array( $t ) && T_CONSTANT_ENCAPSED_STRING === $t[0] ) {
				$names[] = strtolower( ltrim( substr( $t[1], 1, -1 ), '\\' ) );
			}
		}
		$next = StyleGates::next_significant( $tokens, $j );
		return $names && null !== $next && '{' === $tokens[ $next ] ? $names : null;
	}

	/**
	 * True when an enclosing if-block guards this name (short or qualified).
	 *
	 * @param array  $guards Open guard blocks [depth, names].
	 * @param string $name   Declared short name.
	 * @return bool
	 */
	private static function guarded( array $guards, string $name ): bool {
		$name = strtolower( $name );
		foreach ( $guards as $guard ) {
			foreach ( $guard[1] as $guarded ) {
				$short = strrpos( $guarded, '\\' );
				if ( $guarded === $name || ( false !== $short && substr( $guarded, $short + 1 ) === $name ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Fully qualified name.
	 *
	 * @param string $ns   Namespace or ''.
	 * @param string $name Short name.
	 * @return string
	 */
	private static function qualify( string $ns, string $name ): string {
		return '' === $ns ? $name : $ns . '\\' . $name;
	}

	/**
	 * Index of the previous token that is not whitespace or a comment, or null.
	 *
	 * @param array $tokens Tokens.
	 * @param int   $i      Index.
	 * @return int|null
	 */
	private static function prev_significant( array $tokens, int $i ): ?int {
		for ( $j = $i - 1; $j >= 0; $j-- ) {
			if ( ! is_array( $tokens[ $j ] ) || ! in_array( $tokens[ $j ][0], [ T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ], true ) ) {
				return $j;
			}
		}
		return null;
	}
}
