<?php
/**
 * Surface gates: one owner per surface, a fixed vocabulary, shared components kept in their provider.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * surface_unique, surface_vocabulary, component_lookalike.
 */
final class SurfaceGates {

	/** Shared component => look-alike words (spec 4.2: only the providing module may hold this code). */
	const LOOKALIKES = [
		'component.drawer'   => '/\b(?:drawer|off-?canvas)\b/i',
		'component.carousel' => '/\b(?:carousel|swiper|slick|flickity|splide|glide)\b/i',
	];

	/**
	 * A surface is declared by at most one non-deprecated module unless one of the pair `replaces` the other.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function surface_unique( Context $ctx ): array {
		$owners = [];
		foreach ( $ctx->modules() as $name => $mod ) {
			$m = $mod['data'];
			if ( null === $m || 'deprecated' === ( $m['status'] ?? '' ) ) {
				continue;
			}
			foreach ( self::strings( $m['surfaces'] ?? [] ) as $surface ) {
				$owners[ $surface ][] = $name;
			}
		}

		$files = [];
		$notes = [];
		foreach ( $owners as $surface => $names ) {
			$names = array_values( array_unique( $names ) );
			$count = count( $names );
			for ( $i = 0; $i < $count; $i++ ) {
				for ( $j = $i + 1; $j < $count; $j++ ) {
					[ $a, $b ] = [ $names[ $i ], $names[ $j ] ];
					if ( ! $ctx->in_scope( $a ) && ! $ctx->in_scope( $b ) ) {
						continue;
					}
					if ( self::replaces( $ctx, $a, $b ) || self::replaces( $ctx, $b, $a ) ) {
						continue;
					}
					foreach ( [ $a, $b ] as $name ) {
						$files[] = self::surface_at( $ctx, $name, $surface );
					}
					$notes[] = "{$surface} declared by {$a} and {$b} (neither replaces the other)";
				}
			}
		}
		sort( $files, SORT_STRING );
		return Context::findings( 'surface_unique', 'fail', $files, count( $owners ) . ' surfaces, one owner each', 'surface clashes', $notes );
	}

	/**
	 * Every surface is in tools/surfaces.json; every provides/uses is a listed component.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function surface_vocabulary( Context $ctx ): array {
		$vocab = json_decode( $ctx->read( 'tools/surfaces.json' ), true );
		if ( ! is_array( $vocab ) || ! is_array( $vocab['surfaces'] ?? null ) || ! is_array( $vocab['components'] ?? null ) ) {
			return Context::gate( 'surface_vocabulary', 'fail', false, 'tools/surfaces.json missing or not { "surfaces": {...}, "components": [...] }', [ 'tools/surfaces.json' ] );
		}
		$surfaces   = array_keys( $vocab['surfaces'] );
		$components = $vocab['components'];

		$files = [];
		$notes = [];
		foreach ( $ctx->modules() as $name => $mod ) {
			$m = $mod['data'];
			if ( null === $m ) {
				continue;
			}
			foreach ( self::strings( $m['surfaces'] ?? [] ) as $surface ) {
				if ( ! in_array( $surface, $surfaces, true ) ) {
					$files[] = self::surface_at( $ctx, $name, $surface );
					$notes[] = "{$name}: unknown surface {$surface}";
				}
			}
			foreach ( [ 'provides', 'uses' ] as $key ) {
				foreach ( self::strings( $m[ $key ] ?? [] ) as $component ) {
					if ( ! in_array( $component, $components, true ) ) {
						$files[] = self::surface_at( $ctx, $name, $component );
						$notes[] = "{$name}: unknown component {$component} in {$key}";
					}
				}
			}
		}
		return Context::findings( 'surface_vocabulary', 'fail', $files, 'every surface and component is in tools/surfaces.json', 'surfaces/components not in tools/surfaces.json', $notes );
	}

	/**
	 * Warn on drawer/carousel code in beta/stable modules that do not provide that component.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function component_lookalike( Context $ctx ): array {
		$files = [];
		$notes = [];
		foreach ( $ctx->scoped_modules() as $name => $mod ) {
			$m = $mod['data'];
			if ( null === $m || ! in_array( $m['status'] ?? '', Context::STYLE_STATUSES, true ) ) {
				continue;
			}
			$provides = self::strings( $m['provides'] ?? [] );
			foreach ( $ctx->files_under( [ "modules/{$name}/" ], [ 'php', 'js', 'mjs', 'css' ] ) as $rel ) {
				foreach ( preg_split( '/\R/', $ctx->read( $rel ) ) as $i => $line ) {
					// Calling the shared component (window.blazeChild.<component>) is the right way to use it.
					if ( false !== strpos( $line, 'blazeChild.' ) ) {
						continue;
					}
					foreach ( self::LOOKALIKES as $component => $pattern ) {
						if ( ! in_array( $component, $provides, true ) && preg_match( $pattern, $line ) ) {
							$files[] = $rel . ':' . ( $i + 1 );
							$notes[] = "{$name}: {$component} look-alike outside its provider";
						}
					}
				}
			}
		}
		return Context::findings( 'component_lookalike', 'warn', $files, 'no drawer/carousel look-alikes outside their providers', 'possible drawer/carousel code outside the providing module', $notes );
	}

	/**
	 * True when module $a lists $b in `replaces`.
	 *
	 * @param Context $ctx Context.
	 * @param string  $a   Module.
	 * @param string  $b   Module.
	 * @return bool
	 */
	private static function replaces( Context $ctx, string $a, string $b ): bool {
		return in_array( $b, self::strings( $ctx->modules()[ $a ]['data']['replaces'] ?? [] ), true );
	}

	/**
	 * "modules/<name>/module.json:<line>" of a value inside the manifest.
	 *
	 * @param Context $ctx   Context.
	 * @param string  $name  Module folder.
	 * @param string  $value Surface or component.
	 * @return string
	 */
	private static function surface_at( Context $ctx, string $name, string $value ): string {
		$mod = $ctx->modules()[ $name ];
		$pos = strpos( $mod['raw'], '"' . $value . '"' );
		return $mod['file'] . ':' . ( false === $pos ? 1 : Context::line_at( $mod['raw'], $pos ) );
	}

	/**
	 * Strings of a list value, ignoring anything else (the schema gate reports bad types).
	 *
	 * @param mixed $value Value.
	 * @return string[]
	 */
	private static function strings( $value ): array {
		return is_array( $value ) ? array_values( array_filter( $value, 'is_string' ) ) : [];
	}
}
