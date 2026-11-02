<?php
namespace Blaze\Child\Modules\NeedsWoo;

defined( 'ABSPATH' ) || exit;

/** Fixture: never booted (WooCommerce is absent in tests). */
class Module extends \Blaze\Child\Core\Module {
	public function boot(): void {
		$GLOBALS['bc_fx_booted'][] = $this->id();
	}
}
