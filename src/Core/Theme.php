<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Boot order owner (spec 4.3).
 */
final class Theme {

	const NOTICE_MAX_AGE = 86400;

	/**
	 * @var string[] Files legacy-v1 must skip (Registry::skip_files()).
	 */
	private static $skip_files = [];

	/**
	 * Boots the theme at load time: site config, v1 core, site code, modules, assets, notices.
	 * legacy-v1 keeps v1's order: inc/loader.php -> site code -> inc/perf (in its boot()).
	 *
	 * @return void
	 */
	public static function boot(): void {
		$config   = SiteLayer::config();
		$registry = new Registry( BLAZE_CHILD_PATH . 'modules' );

		// R1: the skip list must answer before inc/loader.php runs (config + kill switch only).
		self::$skip_files = $registry->skip_files( $config['modules'] );
		add_filter( 'blaze_child/legacy_v1/skip_files', [ self::class, 'filter_skip_files' ] );

		self::load_v1_core( $registry, $config );
		SiteLayer::include_site_code();

		// A broken site-config.php boots nothing: site code's blaze_child/module_enabled filter must not reach resolve().
		$booted = SiteLayer::config_failed() ? [] : $registry->boot( $registry->resolve( $config['modules'] ), $config['settings'] );
		if ( Registry::critical_failures() && 'open' !== $config['on_failure'] ) {
			FailClosed::arm( 'critical_module_failed' );
		}

		Assets::register( $booted );
		add_action( 'admin_notices', [ self::class, 'admin_notices' ] );
	}

	/**
	 * Loads v1's inc/loader.php when legacy-v1 is on by config or default (and not killed).
	 *
	 * @param Registry $registry Module registry.
	 * @param array    $config   SiteLayer::config().
	 * @return void
	 */
	private static function load_v1_core( Registry $registry, array $config ): void {
		$class = \Blaze\Child\Modules\LegacyV1\Module::class;
		// R11: a broken site-config.php boots nothing, v1 included.
		if ( SiteLayer::config_failed() || ! $registry->enabled_before_site_code( 'legacy-v1', $config['modules'] ) || ! class_exists( $class ) ) {
			return;
		}
		try {
			$class::load_v1_core();
		} catch ( \Throwable $e ) {
			Health::record( 'module_failed', "Module 'legacy-v1' core failed to load: " . $e->getMessage(), [ 'module' => 'legacy-v1' ] );
			if ( 'open' !== $config['on_failure'] ) {
				FailClosed::arm( 'legacy_v1_failed' );
			}
		}
	}

	/**
	 * Adds the files replaced by enabled ports to the legacy-v1 skip list.
	 *
	 * @param mixed $files Skip list so far.
	 * @return array
	 */
	public static function filter_skip_files( $files ): array {
		return array_values( array_unique( array_merge( is_array( $files ) ? $files : [], self::$skip_files ) ) );
	}

	/**
	 * Lists health entries from the last 24 h for users who can manage options.
	 *
	 * @return void
	 */
	public static function admin_notices(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$cutoff = time() - self::NOTICE_MAX_AGE;
		$items  = '';
		foreach ( Health::all() as $entry ) {
			if ( ( $entry['time'] ?? 0 ) >= $cutoff ) {
				$items .= '<li><code>' . esc_html( $entry['code'] ) . '</code> ' . esc_html( $entry['message'] ) . '</li>';
			}
		}
		if ( '' === $items ) {
			return;
		}
		echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Blaze child theme: problems in the last 24 hours', 'blocksy-child' ) . '</strong></p><ul>' . $items . '</ul></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $items is built from esc_html() above.
	}

	/**
	 * 'hide-cart-page' -> 'HideCartPage'.
	 *
	 * @param string $id Module id (kebab-case).
	 * @return string
	 */
	public static function studly( string $id ): string {
		return str_replace( ' ', '', ucwords( str_replace( '-', ' ', $id ) ) );
	}

	/**
	 * Absolute path of a module's Module.php.
	 *
	 * @param string $id Module id (kebab-case).
	 * @return string
	 */
	public static function module_class_path( string $id ): string {
		return BLAZE_CHILD_PATH . 'modules/' . $id . '/Module.php';
	}
}
