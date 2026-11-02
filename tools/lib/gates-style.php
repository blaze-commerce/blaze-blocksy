<?php
/**
 * House-style gates (spec 4.9) for v2 code: src/, tools/ and beta/stable modules.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * no_jquery, no_innerhtml_vars, no_hook_closures, no_strict_types, abspath_guard.
 */
final class StyleGates {

	/** Script registration calls whose arguments may carry dependency handles. */
	const SCRIPT_CALLS = [ 'wp_enqueue_script', 'wp_register_script' ];

	/**
	 * No jQuery: not in manifest asset deps, not in JS code, not as a script dependency in PHP.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function no_jquery( Context $ctx ): array {
		$files = [];

		foreach ( $ctx->scoped_modules() as $mod ) {
			if ( ! in_array( $mod['data']['status'] ?? '', Context::STYLE_STATUSES, true ) ) {
				continue;
			}
			foreach ( (array) ( $mod['data']['assets'] ?? [] ) as $asset ) {
				foreach ( (array) ( $asset['deps'] ?? [] ) as $dep ) {
					if ( is_string( $dep ) && preg_match( '/^jquery/i', $dep ) ) {
						$pos     = strpos( $mod['raw'], '"' . $dep . '"' );
						$files[] = $mod['file'] . ':' . ( false === $pos ? 1 : Context::line_at( $mod['raw'], $pos ) );
					}
				}
			}
		}

		foreach ( $ctx->style_files( [ 'js', 'mjs' ] ) as $rel ) {
			foreach ( preg_split( '/\R/', $ctx->read( $rel ) ) as $i => $line ) {
				if ( ! self::is_comment_line( $line ) && preg_match( '/\bjquery\b/i', $line ) ) {
					$files[] = $rel . ':' . ( $i + 1 );
				}
			}
		}

		foreach ( $ctx->style_files( [ 'php' ] ) as $rel ) {
			foreach ( self::script_dep_lines( $ctx->read( $rel ) ) as $line ) {
				$files[] = "{$rel}:{$line}";
			}
		}

		return Context::findings( 'no_jquery', 'fail', $files, 'no jQuery in asset deps, JS or script deps', 'jQuery use' );
	}

	/**
	 * Lines where a 'jquery*' string literal is passed to wp_enqueue_script()/wp_register_script().
	 *
	 * @param string $php Source.
	 * @return int[]
	 */
	private static function script_dep_lines( string $php ): array {
		$tokens = token_get_all( $php );
		$count  = count( $tokens );
		$lines  = [];
		for ( $i = 0; $i < $count; $i++ ) {
			if ( ! self::is_call_name( $tokens[ $i ], self::SCRIPT_CALLS ) ) {
				continue;
			}
			$j = self::next_significant( $tokens, $i );
			if ( null === $j || '(' !== $tokens[ $j ] ) {
				continue;
			}
			for ( $depth = 0; $j < $count; $j++ ) {
				$t = $tokens[ $j ];
				if ( '(' === $t || '[' === $t ) {
					++$depth;
				} elseif ( ')' === $t || ']' === $t ) {
					if ( 0 === --$depth ) {
						break;
					}
				} elseif ( is_array( $t ) && T_CONSTANT_ENCAPSED_STRING === $t[0] && preg_match( '/^[\'"]jquery/i', $t[1] ) ) {
					$lines[] = $t[2];
				}
			}
		}
		return $lines;
	}

	/**
	 * Warn on innerHTML/outerHTML assigned anything but a plain string literal (spec 4.9: textContent for
	 * server strings).
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function no_innerhtml_vars( Context $ctx ): array {
		$files = [];
		foreach ( $ctx->style_files( [ 'js', 'mjs' ] ) as $rel ) {
			foreach ( preg_split( '/\R/', $ctx->read( $rel ) ) as $i => $line ) {
				if ( self::is_comment_line( $line ) ) {
					continue;
				}
				if ( preg_match( '/\.(?:inner|outer)HTML\s*\+?=(?!=)\s*(.*)$/', $line, $m ) && ! self::is_plain_literal( $m[1] ) ) {
					$files[] = $rel . ':' . ( $i + 1 );
				} elseif ( preg_match( '/\.insertAdjacentHTML\s*\(\s*[^,]+,\s*(.*)$/', $line, $m ) && ! self::is_plain_literal( rtrim( $m[1], " \t);" ) ) ) {
					$files[] = $rel . ':' . ( $i + 1 );
				}
			}
		}
		return Context::findings( 'no_innerhtml_vars', 'warn', $files, 'no HTML injection from variables', 'innerHTML/outerHTML/insertAdjacentHTML with non-literal values' );
	}

	/**
	 * True for a JS string literal without interpolation, optionally followed by ';'.
	 *
	 * @param string $rhs Right-hand side.
	 * @return bool
	 */
	private static function is_plain_literal( string $rhs ): bool {
		$rhs = rtrim( trim( $rhs ), ';' );
		return 1 === preg_match( '/^(?:\'[^\'\\\\]*\'|"[^"\\\\]*"|`[^`$\\\\]*`)$/', trim( $rhs ) );
	}

	/**
	 * True for a line that is only a comment.
	 *
	 * @param string $line Line.
	 * @return bool
	 */
	private static function is_comment_line( string $line ): bool {
		return 1 === preg_match( '#^\s*(//|/\*|\*|\#)#', $line );
	}

	/**
	 * No closures (function/fn, static or not) as the callback of add_action()/add_filter().
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function no_hook_closures( Context $ctx ): array {
		$files = [];
		foreach ( $ctx->style_files( [ 'php' ] ) as $rel ) {
			$tokens = token_get_all( $ctx->read( $rel ) );
			$count  = count( $tokens );
			for ( $i = 0; $i < $count; $i++ ) {
				if ( ! self::is_call_name( $tokens[ $i ], [ 'add_action', 'add_filter' ] ) ) {
					continue;
				}
				$j = self::next_significant( $tokens, $i );
				if ( null === $j || '(' !== $tokens[ $j ] ) {
					continue;
				}
				// Skip the hook name: up to the first comma at depth 1.
				for ( $depth = 0; $j < $count; $j++ ) {
					$t = $tokens[ $j ];
					if ( '(' === $t || '[' === $t || self::is_brace_open( $t ) ) {
						++$depth;
					} elseif ( ')' === $t || ']' === $t || '}' === $t ) {
						if ( 0 === --$depth ) {
							break;
						}
					} elseif ( ',' === $t && 1 === $depth ) {
						break;
					}
				}
				if ( ',' !== ( $tokens[ $j ] ?? null ) ) {
					continue;
				}
				$k = self::next_significant( $tokens, $j );
				if ( null !== $k && is_array( $tokens[ $k ] ) && T_STATIC === $tokens[ $k ][0] ) {
					$k = self::next_significant( $tokens, $k );
				}
				if ( null !== $k && is_array( $tokens[ $k ] ) && in_array( $tokens[ $k ][0], [ T_FUNCTION, T_FN ], true ) ) {
					$files[] = $rel . ':' . $tokens[ $k ][2];
				}
			}
		}
		return Context::findings( 'no_hook_closures', 'fail', $files, 'every hook callback is a named callable', 'closures on hooks' );
	}

	/**
	 * No declare(strict_types=1) (spec 4.9: a TypeError in a hook callback is an uncaught fatal).
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function no_strict_types( Context $ctx ): array {
		$files = [];
		foreach ( $ctx->style_files( [ 'php' ] ) as $rel ) {
			$tokens = token_get_all( $ctx->read( $rel ) );
			foreach ( $tokens as $i => $t ) {
				if ( ! is_array( $t ) || T_DECLARE !== $t[0] ) {
					continue;
				}
				$j = self::next_significant( $tokens, $i );
				$j = null === $j ? null : self::next_significant( $tokens, $j );
				if ( null !== $j && is_array( $tokens[ $j ] ) && 'strict_types' === strtolower( $tokens[ $j ][1] ) ) {
					$files[] = $rel . ':' . $t[2];
				}
			}
		}
		return Context::findings( 'no_strict_types', 'fail', $files, 'no strict_types declarations', 'strict_types declarations' );
	}

	/**
	 * Every PHP file in src/ and loadable modules starts with the ABSPATH guard: after the open tag,
	 * comments, the namespace line and `use` imports, the first statement is `defined( 'ABSPATH' ) || exit;`
	 * (or `|| die`, `or exit`, `if ( ! defined( 'ABSPATH' ) ) { exit; }`). R2: stubs (loadable false) are skipped.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function abspath_guard( Context $ctx ): array {
		$prefixes = null === $ctx->module ? [ 'src/' ] : [];
		foreach ( $ctx->scoped_modules() as $name => $mod ) {
			if ( false !== ( $mod['data']['loadable'] ?? null ) ) {
				$prefixes[] = "modules/{$name}/";
			}
		}
		$scan  = $ctx->files_under( $prefixes, [ 'php' ] );
		$files = [];
		foreach ( $scan as $rel ) {
			$line = self::guard_error_line( $ctx->read( $rel ) );
			if ( null !== $line ) {
				$files[] = "{$rel}:{$line}";
			}
		}
		return Context::findings( 'abspath_guard', 'fail', $files, count( $scan ) . ' PHP files start with the ABSPATH guard', 'PHP files without a leading ABSPATH guard' );
	}

	/**
	 * Null when the guard is the first statement, else the line of the first other statement.
	 *
	 * @param string $php Source.
	 * @return int|null
	 */
	private static function guard_error_line( string $php ): ?int {
		$tokens = token_get_all( $php );
		$count  = count( $tokens );
		$i      = self::skip_trivia( $tokens, 0 );

		// namespace X; and use imports may come first.
		while ( $i < $count && is_array( $tokens[ $i ] ) && in_array( $tokens[ $i ][0], [ T_NAMESPACE, T_USE ], true ) ) {
			while ( $i < $count && ';' !== $tokens[ $i ] ) {
				++$i;
			}
			$i = self::skip_trivia( $tokens, $i + 1 );
		}

		$start = $i;
		$code  = '';
		$depth = 0;
		for ( ; $i < $count; $i++ ) {
			$t = $tokens[ $i ];
			if ( is_array( $t ) && in_array( $t[0], [ T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ], true ) ) {
				continue;
			}
			$text  = is_array( $t ) ? $t[1] : $t;
			$code .= strtolower( str_replace( '"', "'", $text ) );
			if ( '{' === $t ) {
				++$depth;
			} elseif ( '}' === $t ) {
				--$depth;
				if ( 0 === $depth ) {
					break;
				}
			} elseif ( ';' === $t && 0 === $depth ) {
				break;
			}
		}

		$ok = [
			"defined('abspath')||exit;",
			"defined('abspath')||exit();",
			"defined('abspath')||die;",
			"defined('abspath')||die();",
			"defined('abspath')orexit;",
			"defined('abspath')ordie;",
			"if(!defined('abspath')){exit;}",
			"if(!defined('abspath')){exit();}",
			"if(!defined('abspath')){die;}",
		];
		if ( in_array( $code, $ok, true ) ) {
			return null;
		}
		$t = $tokens[ $start ] ?? null;
		return is_array( $t ) ? $t[2] : 1;
	}

	/**
	 * Index of the first token at or after $i that is not the open tag, whitespace or a comment.
	 *
	 * @param array $tokens Tokens.
	 * @param int   $i      Start index.
	 * @return int
	 */
	private static function skip_trivia( array $tokens, int $i ): int {
		$count = count( $tokens );
		while ( $i < $count && is_array( $tokens[ $i ] ) && in_array( $tokens[ $i ][0], [ T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ], true ) ) {
			++$i;
		}
		return $i;
	}

	/**
	 * True for a function-name token (plain or fully qualified, e.g. \add_filter) naming one of $names.
	 *
	 * @param mixed    $token Token from token_get_all().
	 * @param string[] $names Lowercase function names.
	 * @return bool
	 */
	private static function is_call_name( $token, array $names ): bool {
		return is_array( $token )
			&& in_array( $token[0], [ T_STRING, T_NAME_FULLY_QUALIFIED ], true )
			&& in_array( strtolower( ltrim( $token[1], '\\' ) ), $names, true );
	}

	/**
	 * True for "{" and the string-interpolation openers "{$" and "${" (each closes with "}").
	 *
	 * @param mixed $token Token from token_get_all().
	 * @return bool
	 */
	private static function is_brace_open( $token ): bool {
		return '{' === $token || ( is_array( $token ) && in_array( $token[0], [ T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES ], true ) );
	}

	/**
	 * Index of the next token after $i that is not whitespace or a comment, or null.
	 *
	 * @param array $tokens Tokens.
	 * @param int   $i      Index.
	 * @return int|null
	 */
	public static function next_significant( array $tokens, int $i ): ?int {
		$count = count( $tokens );
		for ( $j = $i + 1; $j < $count; $j++ ) {
			if ( ! is_array( $tokens[ $j ] ) || ! in_array( $tokens[ $j ][0], [ T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ], true ) ) {
				return $j;
			}
		}
		return null;
	}
}
