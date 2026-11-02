<?php
namespace Blaze\Child\Modules\OffcanvasIcons;

defined( 'ABSPATH' ) || exit;

/**
 * Allow-list SVG sanitiser for icons that are inlined into every page.
 *
 * The input is parsed with DOMDocument (no network, no entity substitution, no DTD) and copied element by element
 * into a fresh document: only allow-listed SVG elements and attributes are copied, so anything unknown is dropped
 * with its children. Pure function, no WordPress calls.
 */
final class Sanitizer {

	// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM extension property names.

	/** Inputs larger than this are rejected, unparsed. */
	const MAX_BYTES = 524288;

	const SVG_NS   = 'http://www.w3.org/2000/svg';
	const XLINK_NS = 'http://www.w3.org/1999/xlink';

	/** Lower-case name => canonical name. */
	const ELEMENTS = [
		'svg'            => 'svg',
		'g'              => 'g',
		'path'           => 'path',
		'circle'         => 'circle',
		'rect'           => 'rect',
		'line'           => 'line',
		'polyline'       => 'polyline',
		'polygon'        => 'polygon',
		'ellipse'        => 'ellipse',
		'title'          => 'title',
		'desc'           => 'desc',
		'defs'           => 'defs',
		'use'            => 'use',
		'symbol'         => 'symbol',
		'lineargradient' => 'linearGradient',
		'radialgradient' => 'radialGradient',
		'stop'           => 'stop',
		'clippath'       => 'clipPath',
		'mask'           => 'mask',
	];

	/** Lower-case name => canonical name. Geometry, presentation, gradient, ARIA. `href` is handled apart. */
	const ATTRIBUTES = [
		'viewbox'             => 'viewBox',
		'preserveaspectratio' => 'preserveAspectRatio',
		'version'             => 'version',
		'class'               => 'class',
		'id'                  => 'id',
		'transform'           => 'transform',
		'd'                   => 'd',
		'points'              => 'points',
		'pathlength'          => 'pathLength',
		'x'                   => 'x',
		'y'                   => 'y',
		'x1'                  => 'x1',
		'y1'                  => 'y1',
		'x2'                  => 'x2',
		'y2'                  => 'y2',
		'cx'                  => 'cx',
		'cy'                  => 'cy',
		'r'                   => 'r',
		'rx'                  => 'rx',
		'ry'                  => 'ry',
		'fx'                  => 'fx',
		'fy'                  => 'fy',
		'fr'                  => 'fr',
		'width'               => 'width',
		'height'              => 'height',
		'fill'                => 'fill',
		'fill-rule'           => 'fill-rule',
		'fill-opacity'        => 'fill-opacity',
		'clip-rule'           => 'clip-rule',
		'stroke'              => 'stroke',
		'stroke-width'        => 'stroke-width',
		'stroke-linecap'      => 'stroke-linecap',
		'stroke-linejoin'     => 'stroke-linejoin',
		'stroke-dasharray'    => 'stroke-dasharray',
		'stroke-dashoffset'   => 'stroke-dashoffset',
		'stroke-miterlimit'   => 'stroke-miterlimit',
		'stroke-opacity'      => 'stroke-opacity',
		'clip-path'           => 'clip-path',
		'mask'                => 'mask',
		'opacity'             => 'opacity',
		'color'               => 'color',
		'display'             => 'display',
		'visibility'          => 'visibility',
		'vector-effect'       => 'vector-effect',
		'shape-rendering'     => 'shape-rendering',
		'offset'              => 'offset',
		'stop-color'          => 'stop-color',
		'stop-opacity'        => 'stop-opacity',
		'gradientunits'       => 'gradientUnits',
		'gradienttransform'   => 'gradientTransform',
		'spreadmethod'        => 'spreadMethod',
		'clippathunits'       => 'clipPathUnits',
		'maskunits'           => 'maskUnits',
		'maskcontentunits'    => 'maskContentUnits',
		'aria-hidden'         => 'aria-hidden',
		'aria-label'          => 'aria-label',
		'role'                => 'role',
		'focusable'           => 'focusable',
	];

	/**
	 * The sanitised SVG markup, or '' when the input is not acceptable SVG.
	 *
	 * Rejected outright: more than MAX_BYTES, any DOCTYPE or ENTITY declaration, input that does not parse, a root
	 * that is not <svg>. Output has no XML declaration and starts with <svg.
	 *
	 * @param string $svg Raw file contents.
	 * @return string
	 */
	public static function svg( string $svg ): string {
		if ( '' === $svg || strlen( $svg ) > self::MAX_BYTES ) {
			return '';
		}
		// No DTD at all: rules out external entities (XXE) and entity expansion bombs before parsing.
		if ( false !== stripos( $svg, '<!DOCTYPE' ) || false !== stripos( $svg, '<!ENTITY' ) ) {
			return '';
		}

		$previous = libxml_use_internal_errors( true );
		try {
			$in = new \DOMDocument();
			// LIBXML_NONET: never fetch anything. No LIBXML_NOENT / LIBXML_DTDLOAD: entities are never substituted.
			$parsed = $in->loadXML( $svg, LIBXML_NONET );
			$root   = $parsed ? $in->documentElement : null;
			if ( ! $root instanceof \DOMElement || 'svg' !== self::element_name( $root ) ) {
				return '';
			}

			$out = new \DOMDocument( '1.0', 'UTF-8' );
			$new = self::copy_element( $root, $out );
			if ( null === $new ) {
				return '';
			}
			$out->appendChild( $new );
			$markup = $out->saveXML( $out->documentElement );
			return is_string( $markup ) ? $markup : '';
		} catch ( \Throwable $e ) {
			return '';
		} finally {
			libxml_clear_errors();
			libxml_use_internal_errors( $previous );
		}
	}

	/**
	 * Canonical name of an allowed SVG element, or null. Only the SVG namespace (or none) counts, so a foreign
	 * `x:path` or an undeclared `svg:script` prefix is not an SVG element.
	 *
	 * @param \DOMElement $el Source element.
	 * @return string|null
	 */
	private static function element_name( \DOMElement $el ): ?string {
		$ns = $el->namespaceURI;
		if ( null !== $ns && self::SVG_NS !== $ns ) {
			return null;
		}
		if ( null === $ns && false !== strpos( $el->nodeName, ':' ) ) {
			return null;
		}
		$name = strtolower( (string) $el->localName );
		return self::ELEMENTS[ $name ] ?? null;
	}

	/**
	 * Copies an allowed element, its allowed attributes and its allowed children into $doc.
	 *
	 * @param \DOMElement  $src Source element.
	 * @param \DOMDocument $doc Target document.
	 * @return \DOMElement|null Null when the element is not allowed (it is dropped with its children).
	 */
	private static function copy_element( \DOMElement $src, \DOMDocument $doc ): ?\DOMElement {
		$name = self::element_name( $src );
		if ( null === $name ) {
			return null;
		}

		$el = $doc->createElementNS( self::SVG_NS, $name );

		foreach ( $src->attributes as $attr ) {
			self::copy_attribute( $attr, $el );
		}

		foreach ( $src->childNodes as $child ) {
			if ( $child instanceof \DOMElement ) {
				$copy = self::copy_element( $child, $doc );
				if ( null !== $copy ) {
					$el->appendChild( $copy );
				}
			} elseif ( $child instanceof \DOMText ) {
				// Text and CDATA become plain text, escaped on output. Comments and PIs are dropped.
				$el->appendChild( $doc->createTextNode( $child->data ) );
			}
		}

		return $el;
	}

	/**
	 * Copies one attribute when it is allowed and its value is safe.
	 *
	 * @param \DOMAttr    $attr Source attribute.
	 * @param \DOMElement $el   Target element.
	 * @return void
	 */
	private static function copy_attribute( \DOMAttr $attr, \DOMElement $el ): void {
		$ns    = $attr->namespaceURI;
		$local = strtolower( (string) $attr->localName );
		$value = (string) $attr->value;

		// Belt and braces: no allowed name starts with "on", but event handlers must never pass.
		if ( 0 === strpos( $local, 'on' ) || 'style' === $local ) {
			return;
		}
		if ( ! self::safe_value( $value ) ) {
			return;
		}

		if ( 'href' === $local ) {
			// Only same-document fragments: no javascript:, data: or external sprite.
			if ( '' === $value || '#' !== $value[0] ) {
				return;
			}
			if ( self::XLINK_NS === $ns ) {
				$el->setAttributeNS( self::XLINK_NS, 'xlink:href', $value );
			} elseif ( null === $ns && false === strpos( $attr->nodeName, ':' ) ) {
				$el->setAttribute( 'href', $value );
			}
			return;
		}

		if ( null !== $ns || false !== strpos( $attr->nodeName, ':' ) || ! isset( self::ATTRIBUTES[ $local ] ) ) {
			return;
		}

		$el->setAttribute( self::ATTRIBUTES[ $local ], $value );
	}

	/**
	 * False for values carrying a script or data URL, a url() reference that is not a same-document fragment, or a
	 * backslash (CSS escapes could spell either). Whitespace and control characters are ignored, since browsers skip
	 * them inside URL schemes.
	 *
	 * @param string $value Decoded attribute value.
	 * @return bool
	 */
	private static function safe_value( string $value ): bool {
		$flat = strtolower( (string) preg_replace( '/[\x00-\x20\x7f]+/', '', $value ) );
		if ( false !== strpos( $flat, '\\' ) ) {
			return false;
		}
		foreach ( [ 'javascript:', 'vbscript:', 'data:' ] as $scheme ) {
			if ( false !== strpos( $flat, $scheme ) ) {
				return false;
			}
		}
		preg_match_all( '/url\(([^)]*)/', $flat, $refs );
		foreach ( $refs[1] as $ref ) {
			if ( 0 !== strpos( ltrim( $ref, '"\'' ), '#' ) ) {
				return false;
			}
		}
		return true;
	}
	// phpcs:enable
}
