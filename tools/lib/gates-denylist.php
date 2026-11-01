<?php
/**
 * Client denylist: no client names, client page/post IDs or client hex colours in shared code.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * client_denylist.
 */
final class DenylistGates {

	const LIST_FILE = 'tools/denylist.txt';

	/** Text files the name rule reads. */
	const TEXT_EXTENSIONS = [ 'php', 'js', 'mjs', 'css', 'json', 'md', 'txt', 'html', 'xml', 'neon', 'yml', 'yaml', 'svg' ];

	/** A 4+-digit integer literal as the first argument of a page/post/ID lookup. */
	const ID_PATTERN = '/\b(?:is_page|get_post|get_permalink|in_array)\s*\(\s*\d{4,}\b/';

	/**
	 * Names from tools/denylist.txt (case-insensitive) in src/, tools/, beta/stable modules and every
	 * module.json except its `notes`; hard-coded IDs in PHP; raw hex in module CSS outside `:root`.
	 * inc/, v1 assets/, woocommerce/, partials/, tests/ and fixtures/ are never read.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function client_denylist( Context $ctx ): array {
		if ( ! is_file( $ctx->path( self::LIST_FILE ) ) ) {
			return Context::gate( 'client_denylist', 'fail', false, self::LIST_FILE . ' missing', [ self::LIST_FILE ] );
		}
		$tokens = self::tokens( $ctx->read( self::LIST_FILE ) );

		$scan = array_values( array_diff( $ctx->style_files( self::TEXT_EXTENSIONS ), [ self::LIST_FILE ] ) );
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
						$notes[] = "client name '{$token}'";
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

		return Context::findings( 'client_denylist', 'fail', $files, count( $scan ) . ' files free of client names, IDs and hex', 'client-specific code', $notes );
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
				if ( preg_match( '/(^|[\s,]):root\b/', $block[1][0] ) ) {
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
}
