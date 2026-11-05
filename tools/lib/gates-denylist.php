<?php
/**
 * Client denylist: no client names, client page/post IDs or client hex colours in shared code.
 *
 * The client names are private (R27: the theme repo is public), so they are never in the repo. They come from,
 * in order: env BLAZE_CLIENT_DENYLIST (newline- or comma-separated), env BLAZE_CLIENT_DENYLIST_FILE (a file,
 * one name per line), or ~/.config/blaze/client-denylist.txt. tools/denylist.txt holds only generic tokens.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * client_denylist.
 */
final class DenylistGates {

	const LIST_FILE = 'tools/denylist.txt';

	/** Env var holding the client names (CI: the BLAZE_CLIENT_DENYLIST secret). */
	const ENV_LIST = 'BLAZE_CLIENT_DENYLIST';

	/** Env var naming a file of client names. */
	const ENV_FILE = 'BLAZE_CLIENT_DENYLIST_FILE';

	/** Per-user list, relative to $HOME. */
	const HOME_FILE = '.config/blaze/client-denylist.txt';

	/** Text files the name rule reads. */
	const TEXT_EXTENSIONS = [ 'php', 'js', 'mjs', 'css', 'json', 'md', 'txt', 'html', 'xml', 'neon', 'yml', 'yaml', 'svg' ];

	/** A 4+-digit integer literal as the first argument of a page/post/ID lookup. */
	const ID_PATTERN = '/\b(?:is_page|get_post|get_permalink|in_array)\s*\(\s*(?:\[\s*|array\s*\(\s*)?[\'"]?\d{4,}\b/i';

	/**
	 * Client names (from client_names()) and the generic tokens in tools/denylist.txt, case-insensitive, in src/,
	 * tools/, beta/stable modules and every module.json except its `notes`; hard-coded IDs in PHP; raw hex in
	 * module CSS outside `:root`. inc/, v1 assets/, woocommerce/, partials/, tests/ and fixtures/ are never read.
	 * With no client name list the name part warns (fails when env CI is set). The detail names the source,
	 * never the names.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function client_denylist( Context $ctx ): array {
		if ( ! is_file( $ctx->path( self::LIST_FILE ) ) ) {
			return Context::gate( 'client_denylist', 'fail', false, self::LIST_FILE . ' missing', [ self::LIST_FILE ] );
		}
		$names  = self::client_names();
		$tokens = array_values( array_unique( array_merge( self::tokens( $ctx->read( self::LIST_FILE ) ), $names['tokens'] ) ) );
		$about  = null === $names['source']
			? 'client names not checked: ' . $names['reason']
			: 'names from ' . $names['source'] . ' (' . count( $names['tokens'] ) . ')';

		// Never scan a name list that happens to sit inside the checkout.
		$skip = [ self::LIST_FILE ];
		if ( null !== $names['file'] ) {
			$real = realpath( $names['file'] );
			if ( false !== $real && 0 === strpos( $real, $ctx->root . '/' ) ) {
				$skip[] = substr( $real, strlen( $ctx->root ) + 1 );
			}
		}

		$scan = array_values( array_diff( $ctx->style_files( self::TEXT_EXTENSIONS ), $skip ) );
		foreach ( $ctx->scoped_modules() as $mod ) {
			if ( $mod['exists'] ) {
				$scan[] = $mod['file'];
			}
		}
		$scan = array_values( array_unique( $scan ) );
		sort( $scan, SORT_STRING );

		$files = [];
		$notes = [];
		foreach ( $scan as $rel ) {
			$text     = $ctx->read( $rel );
			$manifest = 'module.json' === basename( $rel );
			foreach ( preg_split( '/\R/', $text ) as $i => $line ) {
				if ( $manifest && preg_match( '/^\s*"notes"\s*:/', $line ) ) {
					continue;
				}
				$lower = strtolower( $line );
				foreach ( $tokens as $token ) {
					if ( false !== strpos( $lower, $token ) ) {
						$files[] = $rel . ':' . ( $i + 1 );
						// Never the token itself: the detail is printed in public CI logs.
						$notes[] = 'client name';
					}
				}
			}

			$ext = strtolower( pathinfo( $rel, PATHINFO_EXTENSION ) );
			if ( 'php' === $ext && preg_match_all( self::ID_PATTERN, $text, $m, PREG_OFFSET_CAPTURE ) ) {
				foreach ( $m[0] as $hit ) {
					$files[] = $rel . ':' . Context::line_at( $text, $hit[1] );
					$notes[] = 'hard-coded ID';
				}
			}
			if ( 'css' === $ext && 0 === strpos( $rel, 'modules/' ) ) {
				foreach ( self::hex_lines( $text ) as $line ) {
					$files[] = "{$rel}:{$line}";
					$notes[] = 'raw hex outside :root';
				}
			}
		}

		$gate            = Context::findings( 'client_denylist', 'fail', $files, count( $scan ) . ' files free of client names, IDs and hex', 'client-specific code', $notes );
		$gate['detail'] .= "; {$about}";
		if ( $gate['passed'] && null === $names['source'] ) {
			// Same rule as phpcs/phpstan: required in CI, a warning on a developer machine.
			$gate['passed']   = false;
			$gate['severity'] = '' !== (string) getenv( 'CI' ) ? 'fail' : 'warn';
			$gate['detail']   = count( $scan ) . " files free of IDs and hex; {$about}";
		}
		return $gate;
	}

	/**
	 * The private client name list: env BLAZE_CLIENT_DENYLIST, else the file in env BLAZE_CLIENT_DENYLIST_FILE,
	 * else ~/.config/blaze/client-denylist.txt. The first source that is set decides; a set source with no names
	 * counts as missing (so a blank secret cannot pass silently).
	 *
	 * @return array{tokens: string[], source: ?string, reason: string, file: ?string}
	 */
	public static function client_names(): array {
		$out = [
			'tokens' => [],
			'source' => null,
			'reason' => '',
			'file'   => null,
		];

		$env = trim( (string) getenv( self::ENV_LIST ) );
		if ( '' !== $env ) {
			$out['tokens'] = self::tokens( str_replace( ',', "\n", $env ) );
			return self::named( $out, 'env ' . self::ENV_LIST );
		}

		$file = trim( (string) getenv( self::ENV_FILE ) );
		if ( '' !== $file ) {
			$out['file'] = $file;
			if ( ! is_file( $file ) || ! is_readable( $file ) ) {
				$out['reason'] = self::ENV_FILE . ' is set but the file is unreadable';
				return $out;
			}
			$out['tokens'] = self::tokens( (string) file_get_contents( $file ) );
			return self::named( $out, 'env ' . self::ENV_FILE );
		}

		$home = rtrim( (string) getenv( 'HOME' ), '/' );
		if ( '' !== $home && is_file( "{$home}/" . self::HOME_FILE ) && is_readable( "{$home}/" . self::HOME_FILE ) ) {
			$out['file']   = "{$home}/" . self::HOME_FILE;
			$out['tokens'] = self::tokens( (string) file_get_contents( $out['file'] ) );
			return self::named( $out, '~/' . self::HOME_FILE );
		}

		$out['reason'] = 'no list (set ' . self::ENV_LIST . ' or ' . self::ENV_FILE . ', or create ~/' . self::HOME_FILE . ')';
		return $out;
	}

	/**
	 * Marks a source as used when it holds at least one name.
	 *
	 * @param array  $out    client_names() result so far.
	 * @param string $source Source label.
	 * @return array
	 */
	private static function named( array $out, string $source ): array {
		if ( $out['tokens'] ) {
			$out['source'] = $source;
		} else {
			$out['reason'] = "{$source} holds no names";
		}
		return $out;
	}

	/**
	 * Lowercase tokens: one per line, blank lines and # comments ignored.
	 *
	 * @param string $text File contents.
	 * @return string[]
	 */
	public static function tokens( string $text ): array {
		$out = [];
		foreach ( preg_split( '/\R/', $text ) as $line ) {
			$line = strtolower( trim( $line ) );
			if ( '' !== $line && '#' !== $line[0] ) {
				$out[] = $line;
			}
		}
		return array_values( array_unique( $out ) );
	}

	/**
	 * Lines with a hex colour inside a declaration block, ignoring comments and `:root` blocks.
	 *
	 * @param string $css CSS.
	 * @return int[]
	 */
	public static function hex_lines( string $css ): array {
		// Blank comments out, keeping offsets and newlines.
		$css = (string) preg_replace_callback(
			'#/\*.*?\*/#s',
			function ( array $m ): string {
				return (string) preg_replace( '/[^\n]/', ' ', $m[0] );
			},
			$css
		);

		$lines = [];
		// Innermost blocks hold declarations; the text before "{" back to the last brace is their selector.
		if ( preg_match_all( '/([^{}]*)\{([^{}]*)\}/', $css, $blocks, PREG_OFFSET_CAPTURE | PREG_SET_ORDER ) ) {
			foreach ( $blocks as $block ) {
				if ( self::is_root_selector( $block[1][0] ) ) {
					continue;
				}
				if ( preg_match_all( '/#(?:[0-9a-f]{8}|[0-9a-f]{6}|[0-9a-f]{3,4})\b/i', $block[2][0], $hex, PREG_OFFSET_CAPTURE ) ) {
					foreach ( $hex[0] as $hit ) {
						$lines[] = Context::line_at( $css, $block[2][1] + $hit[1] );
					}
				}
			}
		}
		return array_values( array_unique( $lines ) );
	}

	/**
	 * True when a rule's selector is exactly `:root`, optionally qualified (`:root[data-theme="dark"]`,
	 * `:root:not(.x)`); a selector list such as `.a, :root` is not a token block.
	 *
	 * @param string $prelude Text between the previous brace and "{".
	 * @return bool
	 */
	public static function is_root_selector( string $prelude ): bool {
		$pos      = strrpos( $prelude, ';' );
		$selector = trim( false === $pos ? $prelude : substr( $prelude, $pos + 1 ) );
		return 1 === preg_match( '/^:root(?:\[[^\]]*\]|::?[a-z-]+(?:\([^)]*\))?)*$/i', $selector );
	}
}
