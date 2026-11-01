<?php
/**
 * Blaze child theme v2 bootstrap: constants, autoloader, legacy shims, boot.
 *
 * @package Blocksy_Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'BLAZE_CHILD_VERSION' ) ) {
	// Single source of truth: the style.css `Version:` header (same read as v1).
	define( 'BLAZE_CHILD_VERSION', wp_get_theme( 'blocksy-child' )->get( 'Version' ) );
}
if ( ! defined( 'BLAZE_CHILD_PATH' ) ) {
	define( 'BLAZE_CHILD_PATH', trailingslashit( get_stylesheet_directory() ) );
}
if ( ! defined( 'BLAZE_CHILD_URL' ) ) {
	define( 'BLAZE_CHILD_URL', trailingslashit( get_stylesheet_directory_uri() ) );
}

/**
 * Maps a class name to its file, or null when it is not ours.
 *
 * Blaze\Child\Modules\<Studly>\<Rest> -> modules/<kebab>/<Rest>.php
 * Blaze\Child\<Rest>                  -> src/<Rest>.php
 *
 * @param string $class Fully qualified class name.
 * @return string|null
 */
function blaze_child_autoload_path( string $class ): ?string {
	$prefix = 'Blaze\\Child\\';
	if ( 0 !== strpos( $class, $prefix ) ) {
		return null;
	}

	$rest = substr( $class, strlen( $prefix ) );
	$mod  = 'Modules\\';

	if ( 0 === strpos( $rest, $mod ) ) {
		$parts = explode( '\\', substr( $rest, strlen( $mod ) ), 2 );
		if ( 2 !== count( $parts ) || '' === $parts[0] ) {
			return null;
		}
		$kebab = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $parts[0] ) );
		return BLAZE_CHILD_PATH . 'modules/' . $kebab . '/' . str_replace( '\\', '/', $parts[1] ) . '.php';
	}

	return BLAZE_CHILD_PATH . 'src/' . str_replace( '\\', '/', $rest ) . '.php';
}

/**
 * SPL autoload callback (a named function, no closure).
 *
 * @param string $class Fully qualified class name.
 * @return void
 */
function blaze_child_autoload( string $class ): void {
	$file = blaze_child_autoload_path( $class );
	if ( null !== $file && is_file( $file ) ) {
		require_once $file;
	}
}

spl_autoload_register( 'blaze_child_autoload' );

// Functions are not autoloaded.
require_once __DIR__ . '/Api.php';

\Blaze\Child\Core\Legacy::define();

\Blaze\Child\Core\Theme::boot();
