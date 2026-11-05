<?php
namespace Blaze\Child\Modules\Alpha;

defined( 'ABSPATH' ) || exit;

class Module extends \Blaze\Child\Core\Module {
	public function boot(): void {
		$GLOBALS['bc_fx_booted'][] = $this->id();

	}
}
