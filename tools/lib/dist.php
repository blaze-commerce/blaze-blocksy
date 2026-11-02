<?php
/**
 * Distribution build: blocksy-child.zip with root folder blocksy-child/, .distignore applied,
 * and .blaze-manifest.json listing every shipped file.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * Builds the theme zip (tools/build.php and the dist_contents gate share this).
 */
final class Dist {

	/** Folder name inside the zip (the theme slug). */
	const ROOT = 'blocksy-child';

	const MANIFEST = '.blaze-manifest.json';

	/**
	 * Never shipped, whatever .distignore says (same syntax): VCS data, secrets, editor and log files,
	 * dev-only dependency folders (no Composer or npm at runtime).
	 */
	const ALWAYS_IGNORE = "/.git\n.env\n.env.*\nproject-config.json\n.idea/\n.vscode/\n*.log\nvendor/\nnode_modules/\n";

	/**
	 * Builds <out_dir>/blocksy-child.zip.
	 *
	 * @param string $root    Theme root.
	 * @param string $out_dir Output folder (created when missing).
	 * @return array{zip:string,files:string[]}
	 * @throws \RuntimeException When ZipArchive is missing or the zip cannot be written.
	 */
	public static function build( string $root, string $out_dir ): array {
		if ( ! class_exists( '\ZipArchive' ) ) {
			throw new \RuntimeException( 'PHP zip extension (ZipArchive) is not available' );
		}
		$root = rtrim( $root, '/\\' );
		if ( ! is_file( $root . '/style.css' ) ) {
			throw new \RuntimeException( "{$root} is not a theme root (no style.css)" );
		}
		if ( ! is_dir( $out_dir ) && ! mkdir( $out_dir, 0777, true ) && ! is_dir( $out_dir ) ) {
			throw new \RuntimeException( "cannot create {$out_dir}" );
		}

		$files = self::shipped_files( $root );
		$zip   = rtrim( $out_dir, '/\\' ) . '/' . self::ROOT . '.zip';
		if ( is_file( $zip ) ) {
			unlink( $zip );
		}

		$archive = new \ZipArchive();
		if ( true !== $archive->open( $zip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) ) {
			throw new \RuntimeException( "cannot write {$zip}" );
		}
		foreach ( $files as $rel ) {
			$archive->addFile( $root . '/' . $rel, self::ROOT . '/' . $rel );
		}
		$archive->addFromString( self::ROOT . '/' . self::MANIFEST, json_encode( $files, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n" );
		if ( ! $archive->close() ) {
			throw new \RuntimeException( "cannot write {$zip}" );
		}

		return [
			'zip'   => $zip,
			'files' => $files,
		];
	}

	/**
	 * Relative paths that ship: every file except ALWAYS_IGNORE and .distignore matches and any
	 * stale .blaze-manifest.json at the root. Sorted.
	 *
	 * @param string $root Theme root.
	 * @return string[]
	 */
	public static function shipped_files( string $root ): array {
		$ignore   = is_file( $root . '/.distignore' ) ? (string) file_get_contents( $root . '/.distignore' ) : '';
		$patterns = self::patterns( self::ALWAYS_IGNORE . $ignore );
		$out      = [];
		foreach ( self::walk( $root ) as $rel ) {
			if ( self::MANIFEST !== $rel && ! self::ignored( $rel, $patterns ) ) {
				$out[] = $rel;
			}
		}
		return $out;
	}

	/**
	 * Every file under $root except .git (folder or worktree file) and node_modules, sorted.
	 *
	 * @param string $root Root.
	 * @return string[]
	 */
	private static function walk( string $root ): array {
		$out = [];
		$it  = new \RecursiveIteratorIterator(
			new \RecursiveCallbackFilterIterator(
				new \RecursiveDirectoryIterator( $root, \FilesystemIterator::SKIP_DOTS ),
				[ self::class, 'walk_filter' ]
			)
		);
		foreach ( $it as $file ) {
			$out[] = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $root ) + 1 ) );
		}
		sort( $out, SORT_STRING );
		return $out;
	}

	/**
	 * Iterator filter: skip .git (folder or file) and node_modules.
	 *
	 * @param \SplFileInfo $file File.
	 * @return bool
	 */
	public static function walk_filter( \SplFileInfo $file ): bool {
		return ! in_array( $file->getFilename(), [ '.git', 'node_modules' ], true );
	}

	/**
	 * Parsed .distignore lines (gitignore subset: "/x" anchors to the root, "x/" matches folders only,
	 * a pattern without "/" matches a name at any depth, globs via fnmatch; no "!" negation).
	 *
	 * @param string $text .distignore contents.
	 * @return array<int,array{glob:string,anchored:bool,dir:bool}>
	 */
	public static function patterns( string $text ): array {
		$out = [];
		foreach ( preg_split( '/\R/', $text ) as $line ) {
			$line = trim( $line );
			if ( '' === $line || '#' === $line[0] ) {
				continue;
			}
			$dir   = '/' === substr( $line, -1 );
			$glob  = trim( $line, '/' );
			$out[] = [
				'glob'     => $glob,
				'anchored' => '/' === $line[0] || false !== strpos( $glob, '/' ),
				'dir'      => $dir,
			];
		}
		return $out;
	}

	/**
	 * True when a relative file path matches any pattern.
	 *
	 * @param string $rel      Relative path.
	 * @param array  $patterns From patterns().
	 * @return bool
	 */
	public static function ignored( string $rel, array $patterns ): bool {
		$parts = explode( '/', $rel );
		$last  = count( $parts ) - 1;
		foreach ( $patterns as $p ) {
			for ( $k = 0; $k <= $last; $k++ ) {
				if ( $p['dir'] && $k === $last ) {
					break; // Folder-only pattern: the file itself is not a folder.
				}
				$subject = $p['anchored'] ? implode( '/', array_slice( $parts, 0, $k + 1 ) ) : $parts[ $k ];
				if ( fnmatch( $p['glob'], $subject, FNM_PATHNAME | FNM_PERIOD ) ) {
					return true;
				}
			}
		}
		return false;
	}
}
