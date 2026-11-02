<?php
namespace Blaze\Child\Modules\LegacyV1;

defined( 'ABSPATH' ) || exit;

/**
 * Runs v1's code in v1's order (spec 4.8). Order in Core\Theme::boot():
 * load_v1_core() (inc/loader.php) -> site code -> boot() (inc/perf/loader.php).
 */
class Module extends \Blaze\Child\Core\Module {

	/**
	 * Requires inc/loader.php, exactly as v1's functions.php did. Idempotent (require_once).
	 * Called by Core\Theme before site code, so the module is not booted yet.
	 *
	 * @return void
	 */
	public static function load_v1_core(): void {
		require_once BLAZE_CHILD_PATH . 'inc/loader.php';
	}

	/**
	 * Requires inc/perf/loader.php: v1 loaded it after custom/custom.php, so site code can still
	 * define BLOCKSY_CHILD_PERF_FEATURES or hook blocksy_child_perf_features first.
	 *
	 * @return void
	 */
	public function boot(): void {
		require_once BLAZE_CHILD_PATH . 'inc/perf/loader.php';
	}
}
