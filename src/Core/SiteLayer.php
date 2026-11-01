<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Per-site code outside the theme folder (spec 4.5): wp-content/blaze-site/{site-config.php,site.php}.
 */
final class SiteLayer {

	/**
	 * @var array|null Normalised config, read once per request.
	 */
	private static $config = null;

	/**
	 * The site folder: BLAZE_SITE_DIR, else WP_CONTENT_DIR/blaze-site; null when it does not exist.
	 *
	 * @return string|null
	 */
	public static function dir(): ?string {
		if ( defined( 'BLAZE_SITE_DIR' ) ) {
			$dir = (string) BLAZE_SITE_DIR;
		} elseif ( defined( 'WP_CONTENT_DIR' ) ) {
			$dir = WP_CONTENT_DIR . '/blaze-site';
		} else {
			return null;
		}
		$dir = rtrim( $dir, '/\\' );
		return is_dir( $dir ) ? $dir : null;
	}

	/**
	 * v1's per-site entry inside the theme (blocksy-child/custom/custom.php).
	 *
	 * @return string
	 */
	public static function legacy_file(): string {
		return BLAZE_CHILD_PATH . 'custom/custom.php';
	}

	/**
	 * Normalised site config. A broken site-config.php closes checkout and boots no modules.
	 *
	 * @return array{modules: array<string,bool>, settings: array<string,array>, on_failure: string}
	 */
	public static function config(): array {
		if ( null !== self::$config ) {
			return self::$config;
		}

		$defaults = [
			'modules'    => [],
			'settings'   => [],
			'on_failure' => 'closed',
		];
		$dir      = self::dir();

		if ( null === $dir ) {
			if ( ! is_file( self::legacy_file() ) ) {
				Health::record( 'no_site_config', 'No wp-content/blaze-site/ folder and no legacy custom/custom.php: no modules are enabled' );
			}
			return self::$config = $defaults;
		}

		$file = $dir . '/site-config.php';
		if ( ! is_file( $file ) ) {
			return self::$config = $defaults;
		}

		try {
			$raw = self::include_file( $file );
			if ( ! is_array( $raw ) ) {
				throw new \UnexpectedValueException( 'site-config.php must return an array, got ' . gettype( $raw ) );
			}
		} catch ( \Throwable $e ) {
			Health::record( 'site_config_invalid', 'site-config.php failed: ' . $e->getMessage(), [ 'file' => $file ] );
			FailClosed::arm( 'site_config_invalid' );
			return self::$config = $defaults;
		}

		return self::$config = self::normalise( $raw, $defaults );
	}

	/**
	 * Includes blaze-site/site.php, or legacy custom/custom.php when there is no blaze-site folder.
	 *
	 * @return bool True when site code was included without an error.
	 */
	public static function include_site_code(): bool {
		$dir    = self::dir();
		$legacy = self::legacy_file();

		if ( null !== $dir ) {
			if ( is_file( $legacy ) ) {
				Health::record( 'legacy_custom_ignored', 'Both wp-content/blaze-site/ and theme custom/custom.php exist; only blaze-site is loaded' );
			}
			$file = $dir . '/site.php';
		} elseif ( is_file( $legacy ) ) {
			Health::record( 'legacy_custom_in_theme', 'Site code is in the theme folder (custom/custom.php); a theme update deletes it. Move it to wp-content/blaze-site/site.php' );
			$file = $legacy;
		} else {
			return false;
		}

		if ( ! is_file( $file ) ) {
			return false;
		}

		try {
			self::include_file( $file );
			return true;
		} catch ( \Throwable $e ) {
			Health::record( 'site_code_failed', basename( $file ) . ' failed: ' . $e->getMessage(), [ 'file' => $file, 'line' => $e->getLine() ] );
			if ( 'open' !== self::config()['on_failure'] ) {
				FailClosed::arm( 'site_code_failed' );
			}
			return false;
		}
	}

	/**
	 * Includes a file in a scope that exposes none of this class's locals.
	 *
	 * @param string $file Absolute path.
	 * @return mixed The file's return value.
	 */
	private static function include_file( string $file ) {
		return include $file;
	}

	/**
	 * @param array $raw      What site-config.php returned.
	 * @param array $defaults Default config.
	 * @return array
	 */
	private static function normalise( array $raw, array $defaults ): array {
		$out = $defaults;

		foreach ( is_array( $raw['modules'] ?? null ) ? $raw['modules'] : [] as $id => $on ) {
			if ( is_string( $id ) ) {
				$out['modules'][ $id ] = (bool) $on;
			}
		}
		foreach ( is_array( $raw['settings'] ?? null ) ? $raw['settings'] : [] as $id => $settings ) {
			if ( is_string( $id ) && is_array( $settings ) ) {
				$out['settings'][ $id ] = $settings;
			}
		}
		if ( 'open' === ( $raw['on_failure'] ?? null ) ) {
			$out['on_failure'] = 'open';
		}

		return $out;
	}
}
