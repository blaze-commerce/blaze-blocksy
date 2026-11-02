<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Fixture class.
 */
final class Thing {

	public function boot(): void {
		add_action( 'init', [ $this, 'run' ] );
	}

	public function run(): void {
		if ( is_page( 12 ) ) {
			return;
		}
	}
}
