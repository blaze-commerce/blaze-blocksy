<?php
/**
 * Distribution gate: build the zip into a temp folder and inspect it.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * dist_contents.
 */
final class DistGates {

	/** Top-level folders that must never ship. */
	const FORBIDDEN_TOP = [ 'tests', 'docs', 'tools', '.github', 'clients', 'claude-commands', '.superpowers' ];

	/**
	 * Root folder is blocksy-child/, no forbidden folders, no tests/ at any depth, no *.test.mjs,
	 * and .blaze-manifest.json lists exactly the shipped files.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function dist_contents( Context $ctx ): array {
		if ( ! class_exists( '\ZipArchive' ) ) {
			return Context::gate( 'dist_contents', 'fail', false, 'PHP zip extension (ZipArchive) is not available; cannot build the zip' );
		}

		$tmp = sys_get_temp_dir() . '/bc-dist-' . bin2hex( random_bytes( 6 ) );
		try {
			$built = Dist::build( $ctx->root, $tmp );
			return self::inspect( $built['zip'] );
		} catch ( \RuntimeException $e ) {
			return Context::gate( 'dist_contents', 'fail', false, 'build failed: ' . $e->getMessage() );
		} finally {
			if ( is_file( $tmp . '/' . Dist::ROOT . '.zip' ) ) {
				unlink( $tmp . '/' . Dist::ROOT . '.zip' );
			}
			if ( is_dir( $tmp ) ) {
				rmdir( $tmp );
			}
		}
	}

	/**
	 * Checks a built zip.
	 *
	 * @param string $zip_path Zip path.
	 * @return array
	 */
	public static function inspect( string $zip_path ): array {
		$zip = new \ZipArchive();
		if ( true !== $zip->open( $zip_path ) ) {
			return Context::gate( 'dist_contents', 'fail', false, "cannot open {$zip_path}" );
		}
		$entries = [];
		$count   = $zip->count();
		for ( $i = 0; $i < $count; $i++ ) {
			$entries[] = (string) $zip->getNameIndex( $i );
		}
		$manifest_raw = $zip->getFromName( Dist::ROOT . '/' . Dist::MANIFEST );
		$zip->close();

		$files   = [];
		$notes   = [];
		$shipped = [];
		$prefix  = Dist::ROOT . '/';
		foreach ( $entries as $entry ) {
			if ( 0 !== strpos( $entry, $prefix ) ) {
				$files[] = $entry;
				$notes[] = "entry outside {$prefix}";
				continue;
			}
			$rel = substr( $entry, strlen( $prefix ) );
			if ( '' === $rel || '/' === substr( $rel, -1 ) ) {
				continue;
			}
			$parts = explode( '/', $rel );
			if ( in_array( $parts[0], self::FORBIDDEN_TOP, true ) || in_array( 'tests', array_slice( $parts, 0, -1 ), true ) ) {
				$files[] = $rel;
				$notes[] = 'forbidden path ships';
			}
			if ( '.test.mjs' === substr( $rel, -9 ) ) {
				$files[] = $rel;
				$notes[] = '*.test.mjs ships';
			}
			if ( Dist::MANIFEST !== $rel ) {
				$shipped[] = $rel;
			}
		}
		if ( ! in_array( 'style.css', $shipped, true ) ) {
			$notes[] = "no {$prefix}style.css";
		}

		$manifest = false === $manifest_raw ? null : json_decode( $manifest_raw, true );
		sort( $shipped, SORT_STRING );
		if ( ! is_array( $manifest ) ) {
			$files[] = Dist::MANIFEST;
			$notes[] = 'no readable ' . Dist::MANIFEST;
		} elseif ( $manifest !== $shipped ) {
			$files[] = Dist::MANIFEST;
			$notes[] = Dist::MANIFEST . ' does not list exactly the shipped files';
		}

		return Context::findings( 'dist_contents', 'fail', $files, count( $shipped ) . " files ship under {$prefix}; manifest matches", 'distribution problems', $notes );
	}
}
