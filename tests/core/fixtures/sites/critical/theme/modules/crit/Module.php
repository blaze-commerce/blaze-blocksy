<?php
namespace Blaze\Child\Modules\Crit;

defined( 'ABSPATH' ) || exit;

/** Fixture: a critical module whose boot throws. */
class Module extends \Blaze\Child\Core\Module {
	public function boot(): void {
		throw new \RuntimeException( 'crit boot failed' );
	}
}
