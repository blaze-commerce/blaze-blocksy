<?php
namespace Blaze\Child\Modules\Demo;

defined( 'ABSPATH' ) || exit;

/**
 * Fixture module.
 */
class Module extends \Blaze\Child\Core\Module {

	public function boot(): void {
		add_filter( 'the_title', [ $this, 'title' ] );
	}

	public function title( $title ) {
		return $title;
	}
}
