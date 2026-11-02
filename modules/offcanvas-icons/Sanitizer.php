<?php
namespace Blaze\Child\Modules\OffcanvasIcons;

defined( 'ABSPATH' ) || exit;

/**
 * Allow-list SVG sanitiser for icons that are inlined into every page.
 *
 * The input is parsed with DOMDocument (no network, no entity substitution, no DTD loading) and copied element by element
 * into a fresh document: only allow-listed SVG elements and attributes are copied, so anything unknown is dropped
 * with its children. No WordPress calls; the only state is the output counter used in id prefixes.
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

	/** Ids and class names allowed in the output. */
	const NAME_PATTERN = '/^[A-Za-z][A-Za-z0-9_-]*$/';

	/** A CSS colour: keyword, #hex, or rgb[a]()/hsl[a]() with no nested function. */
	const COLOUR = '(?:[a-z]+|#[0-9a-f]{3,8}|(?:rgb|hsl)a?\([0-9a-z.,%\s\/+-]*\))';

	/** A same-document reference: url(#id), id captured. */
	const URL_REF = 'url\(\s*(["\']?)#([a-z][a-z0-9_-]*)\1\s*\)';

	/** One SVG transform function with numeric arguments (separator required unless the next number has a sign). */
	const TRANSFORM_FN = '(?:matrix|translate|scale|rotate|skewx|skewy)\s*\(\s*NUM(?:(?:\s*,\s*|\s+|(?=[+-]))NUM)*\s*\)';

	const NUMBER = '[+-]?(?:\d+\.?\d*|\.\d+)(?:e[+-]?\d+)?';

	/**
	 * @var int Outputs produced in this process; numbers the id prefix so two inlined icons never share an id.
	 */
	private static $outputs = 0;

	/**
	 * @var string Id prefix of the output being built (`bc-oci-<n>-`).
	 */
	private static $prefix = '';

	/**
	 * The sanitised SVG markup, or '' when the input is not acceptable SVG.
	 *
	 * Rejected outright: '' or more than MAX_BYTES, input that does not parse, a DTD with an internal subset or any
	 * entity declaration, a root that is not <svg>. A plain DOCTYPE (public/system id only) is accepted and dropped.
	 * Output has no XML declaration and starts with <svg. Every id is prefixed `bc-oci-<n>-` (n counts outputs) and
	 * same-document references are rewritten to match.
	 *
	 * @param string $svg Raw file contents.
	 * @return string
	 */
	public static function svg( string $svg ): string {
		if ( '' === $svg || strlen( $svg ) > self::MAX_BYTES ) {
			return '';
		}
		// Cheap early exit for the common encoding; the authoritative check runs on the parsed DTD below.
		if ( false !== stripos( $svg, '<!ENTITY' ) ) {
			return '';
		}

		$previous = libxml_use_internal_errors( true );
		try {
			$in = new \DOMDocument();
			// LIBXML_NONET: never fetch anything. No LIBXML_NOENT / LIBXML_DTDLOAD: entities are never substituted
			// and no external DTD is loaded.
			$parsed = $in->loadXML( $svg, LIBXML_NONET );
			if ( ! $parsed || self::has_dtd_declarations( $in ) ) {
				return '';
			}
			$root = $in->documentElement;
			if ( ! $root instanceof \DOMElement || 'svg' !== self::element_name( $root ) ) {
				return '';
			}

			self::$prefix = 'bc-oci-' . ( ++self::$outputs ) . '-';
			$out          = new \DOMDocument( '1.0', 'UTF-8' );
			$new          = self::copy_element( $root, $out );
			if ( null === $new ) {
				return '';
			}
			// Only the root element is copied: the DOCTYPE, comments and PIs around it are dropped.
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
	 * True when the document's DTD declares anything (internal subset, entities, notations). This catches what the
	 * byte-string check cannot see, such as a UTF-16 file.
	 *
	 * @param \DOMDocument $doc Parsed document.
	 * @return bool
	 */
	private static function has_dtd_declarations( \DOMDocument $doc ): bool {
		$dtd = $doc->doctype;
		if ( null === $dtd ) {
			return false;
		}
		return '' !== trim( (string) $dtd->internalSubset ) || $dtd->entities->length > 0 || $dtd->notations->length > 0;
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
				// Text and CDATA become plain text, escaped on output. Comments, PIs and entity references are dropped.
				$el->appendChild( $doc->createTextNode( $child->data ) );
			}
		}

		return $el;
	}

	/**
	 * Copies one attribute when it is allowed and its value passes that attribute's grammar.
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
		if ( 0 === strpos( $local, 'on' ) || 'style' === $local || ! self::safe_value( $value ) ) {
			return;
		}

		if ( 'href' === $local ) {
			// Only same-document fragments: no javascript:, data: or external sprite.
			$ref = self::fragment( $value );
			if ( null === $ref ) {
				return;
			}
			if ( self::XLINK_NS === $ns ) {
				$el->setAttributeNS( self::XLINK_NS, 'xlink:href', $ref );
			} elseif ( null === $ns && false === strpos( $attr->nodeName, ':' ) ) {
				$el->setAttribute( 'href', $ref );
			}
			return;
		}

		if ( null !== $ns || false !== strpos( $attr->nodeName, ':' ) || ! isset( self::ATTRIBUTES[ $local ] ) ) {
			return;
		}

		$name  = self::ATTRIBUTES[ $local ];
		$clean = self::clean_value( $name, $value );
		if ( null !== $clean ) {
			$el->setAttribute( $name, $clean );
		}
	}

	/**
	 * The value to output for an allowed attribute, or null to drop it.
	 *
	 * Values that CSS parses may not call functions, except paint values (colour functions, url(#id)) and transform
	 * lists (SVG transform functions with numbers): that rules out image-set(), image(), cross-fade() and friends,
	 * which would fetch a URL on every page.
	 *
	 * @param string $name  Canonical attribute name.
	 * @param string $value Decoded value.
	 * @return string|null
	 */
	private static function clean_value( string $name, string $value ): ?string {
		switch ( $name ) {
			case 'id':
				return preg_match( self::NAME_PATTERN, $value ) ? self::$prefix . $value : null;
			case 'class':
				$names = array_filter(
					preg_split( '/\s+/', trim( $value ) ),
					[ self::class, 'is_name' ]
				);
				return $names ? implode( ' ', $names ) : null;
			case 'fill':
			case 'stroke':
				return self::paint( $value, true );
			case 'mask':
			case 'clip-path':
				return self::paint( $value, false );
			case 'color':
			case 'stop-color':
				return preg_match( '/^\s*' . self::COLOUR . '\s*$/i', $value ) ? trim( $value ) : null;
			case 'transform':
			case 'gradientTransform':
				$fn = str_replace( 'NUM', self::NUMBER, self::TRANSFORM_FN );
				return preg_match( '/^\s*' . $fn . '(?:(?:\s*,\s*|\s*)' . $fn . ')*\s*$/i', $value ) ? $value : null;
			case 'aria-label':
				return $value;
			default:
				return false === strpos( $value, '(' ) ? $value : null;
		}
	}

	/**
	 * True for an id or class name made of `[A-Za-z][A-Za-z0-9_-]*`.
	 *
	 * @param string $value Candidate.
	 * @return bool
	 */
	public static function is_name( string $value ): bool {
		return 1 === preg_match( self::NAME_PATTERN, $value );
	}

	/**
	 * A paint or reference value: `url(#id)` (optionally followed by a fallback colour when $colour), or a colour
	 * when $colour, or `none`. The id is rewritten to the output's prefix.
	 *
	 * @param string $value  Decoded value.
	 * @param bool   $colour Whether a colour is valid here (fill, stroke) or only none/url (mask, clip-path).
	 * @return string|null
	 */
	private static function paint( string $value, bool $colour ): ?string {
		$value = trim( $value );
		if ( preg_match( '/^' . self::URL_REF . '(?:\s+(' . self::COLOUR . '))?$/i', $value, $m ) ) {
			if ( ! self::is_name( $m[2] ) || ( isset( $m[3] ) && ! $colour ) ) {
				return null;
			}
			return 'url(#' . self::$prefix . $m[2] . ')' . ( isset( $m[3] ) ? ' ' . $m[3] : '' );
		}
		if ( 0 === strcasecmp( $value, 'none' ) ) {
			return 'none';
		}
		return $colour && preg_match( '/^' . self::COLOUR . '$/i', $value ) ? $value : null;
	}

	/**
	 * `#id` rewritten to the output's prefix, or null when the value is not a same-document fragment.
	 *
	 * @param string $value Decoded href.
	 * @return string|null
	 */
	private static function fragment( string $value ): ?string {
		if ( '' === $value || '#' !== $value[0] || ! self::is_name( substr( $value, 1 ) ) ) {
			return null;
		}
		return '#' . self::$prefix . substr( $value, 1 );
	}

	/**
	 * False for values carrying a script or data URL, or a backslash (CSS escapes could spell either). Whitespace
	 * and control characters are ignored, since browsers skip them inside URL schemes.
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
		return true;
	}

	// phpcs:enable
}
