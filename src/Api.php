<?php
namespace Blaze\Child\Api;

defined( 'ABSPATH' ) || exit;

use Blaze\Child\Core\Module;
use Blaze\Child\Core\Registry;

/**
 * The booted instance of a module, or null when it is not enabled.
 *
 * @param string $id Module id.
 * @return Module|null
 */
function module( string $id ): ?Module {
	return Registry::instance( $id );
}
