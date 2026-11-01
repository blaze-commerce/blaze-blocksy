<?php
namespace Blaze\Child\Modules\LegacyV1;

defined( 'ABSPATH' ) || exit;

/** Fixture: records the boot order. */
class Module extends \Blaze\Child\Core\Module {
	public static function load_v1_core(): void {
		$GLOBALS['bc_order'][] = 'v1-core';
	}
	public function boot(): void {
		$GLOBALS['bc_order'][] = 'boot';
	}
}
