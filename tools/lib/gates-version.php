<?php
/**
 * Version agreement between style.css and CHANGELOG.md.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * version_agreement.
 */
final class VersionGates {

	/**
	 * style.css `Version` must equal the first `## [x]` heading of CHANGELOG.md.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function version_agreement( Context $ctx ): array {
		$version = $ctx->version();
		$log     = $ctx->read( 'CHANGELOG.md' );
		$top     = preg_match( '/^## \[([^\]]+)\]/m', $log, $m, PREG_OFFSET_CAPTURE ) ? $m[1][0] : '';

		if ( '' === $version ) {
			return Context::gate( 'version_agreement', 'fail', false, 'style.css has no Version header', [ 'style.css' ] );
		}
		if ( '' === $top ) {
			return Context::gate( 'version_agreement', 'fail', false, 'CHANGELOG.md has no "## [x]" heading', [ 'CHANGELOG.md' ] );
		}
		if ( $version !== $top ) {
			$style_line = preg_match( '/^[ \t\/*#@]*Version:/mi', $ctx->read( 'style.css' ), $s, PREG_OFFSET_CAPTURE ) ? Context::line_at( $ctx->read( 'style.css' ), $s[0][1] ) : 1;
			return Context::gate(
				'version_agreement',
				'fail',
				false,
				"style.css Version {$version} != CHANGELOG.md top heading {$top}",
				[ "style.css:{$style_line}", 'CHANGELOG.md:' . Context::line_at( $log, $m[0][1] ) ]
			);
		}
		return Context::gate( 'version_agreement', 'fail', true, "style.css and CHANGELOG.md agree on {$version}" );
	}
}
