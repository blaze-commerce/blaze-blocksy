<?php
namespace Blaze\Child\Modules\ClashA;

defined( 'ABSPATH' ) || exit;

class Module extends \Blaze\Child\Core\Module {
	public function boot(): void {
		$GLOBALS['bc_fx_booted'][] = $this->id();

	}
}
