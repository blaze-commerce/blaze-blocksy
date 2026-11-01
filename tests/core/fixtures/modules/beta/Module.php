<?php
namespace Blaze\Child\Modules\Beta;

defined( 'ABSPATH' ) || exit;

class Module extends \Blaze\Child\Core\Module {
	public function boot(): void {
		$GLOBALS['bc_fx_booted'][] = $this->id();
		if ( ! empty( $GLOBALS['bc_fx_throw'] ) ) {
			throw new \RuntimeException( 'beta exploded' );
		}
	}
}
