<?php
/**
 * Task 8: offcanvas-icons port; uploaded SVGs sanitised before inlining.
 *
 * @package Blocksy_Child
 */

require_once dirname( __DIR__, 2 ) . '/src/Core/Module.php';
require_once dirname( __DIR__, 2 ) . '/src/Core/Health.php';
require_once dirname( __DIR__, 2 ) . '/src/Core/Registry.php';
require_once dirname( __DIR__, 2 ) . '/modules/offcanvas-icons/Sanitizer.php';
require_once dirname( __DIR__, 2 ) . '/modules/offcanvas-icons/Module.php';

use Blaze\Child\Core\Health;
use Blaze\Child\Core\Registry;
use Blaze\Child\Modules\OffcanvasIcons\Module as OffcanvasIcons;
use Blaze\Child\Modules\OffcanvasIcons\Sanitizer;

// ---------------------------------------------------------------------------
// Stubs (attachment data comes from $GLOBALS['bc_oci']).
// ---------------------------------------------------------------------------

if ( ! function_exists( 'get_attached_file' ) ) {
	function get_attached_file( $id ) {
		return $GLOBALS['bc_oci']['files'][ (int) $id ] ?? false;
	}
}
if ( ! function_exists( 'get_post_mime_type' ) ) {
	function get_post_mime_type( $id ) {
		return $GLOBALS['bc_oci']['mimes'][ (int) $id ] ?? false;
	}
}
if ( ! function_exists( 'wp_upload_dir' ) ) {
	function wp_upload_dir( $time = null, $create_dir = true, $refresh = false ) {
		$GLOBALS['bc_oci']['upload_dir_create'][] = $create_dir;
		return [
			'basedir' => $GLOBALS['bc_oci']['basedir'],
			'baseurl' => 'https://example.test/wp-content/uploads',
			'error'   => false,
		];
	}
}
if ( ! class_exists( 'WP_Customize_Media_Control' ) ) {
	class WP_Customize_Media_Control {
		public $manager;
		public $id;
		public $args;
		public function __construct( $manager, $id, $args = [] ) {
			$this->manager = $manager;
			$this->id      = $id;
			$this->args    = $args;
		}
	}
}

/** Records add_section/add_setting/add_control calls. */
class BC_OCI_Fake_Customizer {
	public $calls = [];
	public function add_section( $id, $args = [] ) {
		$this->calls[] = [ 'section', $id, $args ];
	}
	public function add_setting( $id, $args = [] ) {
		$this->calls[] = [ 'setting', $id, $args ];
	}
	public function add_control( $control, $args = [] ) {
		$this->calls[] = [ 'control', $control->id, $control->args, get_class( $control ) ];
	}
}

const BC_OCI_CART_ICON = '<svg class="ct-icon ct-cart-panel-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M14.6248 5.5C14.6248 3.84315 13.2816 2.5 11.6248 2.5C9.96813 2.50026 8.62476 3.84331 8.62476 5.5V6.25H14.6248V5.5ZM5.13844 7.75C4.94653 7.75 4.78548 7.89508 4.76539 8.08594L3.50172 20.0859C3.47855 20.3072 3.65226 20.4999 3.87476 20.5H19.3757C19.5982 20.4999 19.772 20.3072 19.7488 20.0859L18.4851 8.08594C18.465 7.89508 18.304 7.75 18.1121 7.75H16.1248V9.16211C16.3549 9.3681 16.4998 9.66684 16.4998 10C16.4998 10.6213 15.9961 11.125 15.3748 11.125C14.7537 11.1247 14.2498 10.6212 14.2498 10C14.2498 9.66733 14.3952 9.36905 14.6248 9.16309V7.75H8.62476V9.16211C8.85489 9.3681 8.99976 9.66684 8.99976 10C8.99976 10.6213 8.49608 11.125 7.87476 11.125C7.25367 11.1247 6.74976 10.6212 6.74976 10C6.74976 9.66733 6.89523 9.36905 7.12476 9.16309V7.75H5.13844ZM16.1248 6.25H18.1121C19.0716 6.25 19.8769 6.97444 19.9773 7.92871L21.24 19.9287C21.3565 21.0357 20.4889 21.9999 19.3757 22H3.87476C2.76163 21.9999 1.89398 21.0357 2.01051 19.9287L3.2732 7.92871C3.37365 6.97444 4.17889 6.25 5.13844 6.25H7.12476V5.5C7.12476 3.01488 9.13971 1.00026 11.6248 1C14.11 1 16.1248 3.01472 16.1248 5.5V6.25Z" fill="currentColor"/></svg>';

const BC_OCI_HEART = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15"><path d="M7.5,13.9l-0.4-0.3c-0.2-0.2-4.6-3.5-5.8-4.8C0.4,7.7-0.1,6.4,0,5.1z"/></svg>';

function bc_oci_manifest(): array {
	return json_decode( (string) file_get_contents( dirname( __DIR__, 2 ) . '/modules/offcanvas-icons/module.json' ), true );
}

/** A fresh uploads dir under the system temp dir; returns its realpath. */
function bc_oci_tmpdir(): string {
	$dir = sys_get_temp_dir() . '/bc_oci_' . bin2hex( random_bytes( 6 ) );
	mkdir( $dir . '/uploads/2026/11', 0777, true );
	mkdir( $dir . '/outside', 0777, true );
	$GLOBALS['bc_oci_tmpdirs'][] = $dir;
	return (string) realpath( $dir );
}

/** Removes a directory tree; symlinks are unlinked, never followed. */
function bc_oci_rmtree( string $path ): void {
	if ( is_link( $path ) || is_file( $path ) ) {
		@unlink( $path );
		return;
	}
	if ( ! is_dir( $path ) ) {
		return;
	}
	foreach ( (array) scandir( $path ) as $entry ) {
		if ( '.' !== $entry && '..' !== $entry ) {
			bc_oci_rmtree( $path . '/' . $entry );
		}
	}
	@rmdir( $path );
}

function bc_oci_cleanup(): void {
	foreach ( $GLOBALS['bc_oci_tmpdirs'] ?? [] as $dir ) {
		bc_oci_rmtree( $dir );
	}
	$GLOBALS['bc_oci_tmpdirs'] = [];
}
register_shutdown_function( 'bc_oci_cleanup' );

/** Output with the per-output id number replaced by N. */
function bc_oci_norm( string $svg ): string {
	return (string) preg_replace( '/bc-oci-\d+-/', 'bc-oci-N-', $svg );
}

/** Fresh state plus a booted module; $attachments: id => [ 'file' => path, 'mime' => type, 'modified' => string ]. */
function bc_oci_boot( array $settings = [], array $options = [], array $attachments = [], string $root = '' ): OffcanvasIcons {
	bc_wp_stub_reset();
	$GLOBALS['bc_test_hooks']                 = [];
	$GLOBALS['bc_core_store']['options']      = [];
	$GLOBALS['bc_core_store']['transients']   = [];
	$GLOBALS['bc_core_store']['deprecated']   = [];
	$GLOBALS['bc_wp_stub']['options']         = $options;
	$GLOBALS['bc_oci']                        = [
		'files'             => [],
		'mimes'             => [],
		'basedir'           => '' !== $root ? $root . '/uploads' : '/nonexistent-uploads',
		'upload_dir_create' => [],
	];
	foreach ( $attachments as $id => $a ) {
		$GLOBALS['bc_oci']['files'][ $id ] = $a['file'];
		$GLOBALS['bc_oci']['mimes'][ $id ] = $a['mime'];
	}
	$GLOBALS['bc_wp_stub']['post_field']['post_modified'] = $attachments ? ( reset( $attachments )['modified'] ?? '2026-11-01 00:00:00' ) : '';
	$module = new OffcanvasIcons( bc_oci_manifest(), $settings );
	$module->boot();
	return $module;
}

function bc_oci_has( string $haystack, string $needle ): bool {
	return false !== stripos( $haystack, $needle );
}

// ---------------------------------------------------------------------------
// Sanitizer::svg()
// ---------------------------------------------------------------------------

bc_test( 'strips_script_element', function () {
	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><path d="M0 0h1"/><g><script type="text/javascript"><![CDATA[alert(2)]]></script></g></svg>' );
	assert_same( bc_oci_has( $out, 'script' ), false, 'script elements gone' );
	assert_same( bc_oci_has( $out, 'alert' ), false, 'and their text' );
	assert_same( bc_oci_has( $out, '<path d="M0 0h1"/>' ), true, 'geometry kept' );

	// Namespaced: declared prefix, a foreign namespace, and an undeclared prefix.
	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg"><svg:script>alert(1)</svg:script><svg:path d="M1 1"/></svg>' );
	assert_same( bc_oci_has( $out, 'script' ) || bc_oci_has( $out, 'alert' ), false, 'svg:script removed' );
	assert_same( bc_oci_has( $out, '<path d="M1 1"/>' ), true, 'svg:path kept, unprefixed' );

	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg" xmlns:x="http://evil.test/ns"><x:path d="M1 1" onload="alert(1)"/></svg>' );
	assert_same( bc_oci_has( $out, 'path' ) || bc_oci_has( $out, 'alert' ), false, 'an allowed name in a foreign namespace is dropped' );

	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><svg:script>alert(1)</svg:script></svg>' );
	assert_same( bc_oci_has( $out, 'script' ) || bc_oci_has( $out, 'alert' ), false, 'undeclared svg: prefix removed too' );

	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><SCRIPT>alert(1)</SCRIPT><Script>alert(2)</Script></svg>' );
	assert_same( bc_oci_has( $out, 'alert' ), false, 'any case of script removed' );
} );

bc_test( 'strips_on_attributes', function () {
	$out = Sanitizer::svg( '<SVG oNload="alert(1)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"><PATH d="M0 0" ONCLICK="alert(2)" onMouseOver="alert(3)"/></SVG>' );
	assert_same( bc_oci_has( $out, 'alert' ), false, 'no on* attribute survives in any case' );
	assert_same( bc_oci_has( $out, 'onload' ) || bc_oci_has( $out, 'onclick' ) || bc_oci_has( $out, 'onmouseover' ), false );
	assert_same( 0 === strpos( $out, '<svg ' ), true, 'root normalised to lowercase <svg>' );
	assert_same( bc_oci_has( $out, '<path d="M0 0"/>' ), true, 'PATH normalised to path' );
	assert_same( bc_oci_has( $out, 'viewBox="0 0 1 1"' ), true, 'viewBox keeps its canonical case' );

	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg" onbegin="alert(1)" onfocusin="alert(2)" onerror="alert(3)"><rect width="1" height="1" onanimationstart="alert(4)"/></svg>' );
	assert_same( bc_oci_has( $out, 'alert' ), false );
} );

bc_test( 'strips_style_attribute_and_element', function () {
	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><style><![CDATA[ path { fill: url(javascript:alert(1)) } @import url(https://evil.test/x.css); ]]></style><style>.a{}</style><path d="M0 0" style="background:url(https://evil.test/t)"/></svg>' );
	assert_same( bc_oci_has( $out, 'style' ), false, 'style element (CDATA too) and style attribute removed' );
	assert_same( bc_oci_has( $out, 'evil' ) || bc_oci_has( $out, 'import' ) || bc_oci_has( $out, 'alert' ), false );
	assert_same( $out, '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>' );
} );

bc_test( 'strips_javascript_href_keeps_fragment_href', function () {
	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><defs><path id="p" d="M0 0"/></defs><use href="#p"/><use xlink:href="#p"/><use href="data:image/svg+xml;base64,PHN2Zy8+"/><use xlink:href="https://evil.test/sprite.svg#p"/><a href="javascript:alert(1)"><path d="M9 9"/></a></svg>' );
	$out = bc_oci_norm( $out );
	assert_same( bc_oci_has( $out, '<use href="#bc-oci-N-p"/>' ), true, 'fragment href kept, rewritten to the prefixed id' );
	assert_same( bc_oci_has( $out, 'xlink:href="#bc-oci-N-p"' ), true, 'fragment xlink:href kept, rewritten' );
	assert_same( bc_oci_has( $out, '<path id="bc-oci-N-p"' ), true, 'and the target id carries the same prefix' );
	assert_same( bc_oci_has( $out, 'data:' ) || bc_oci_has( $out, 'evil' ) || bc_oci_has( $out, 'javascript' ), false, 'data:, external and javascript: hrefs gone' );
	assert_same( substr_count( $out, '<use' ), 4, 'the <use> elements stay, without the bad href' );
	assert_same( bc_oci_has( $out, '<a' ) || bc_oci_has( $out, 'M9 9' ), false, '<a> removed with its children' );

	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><use href=" #p"/><use href="java&#x09;script:alert(1)"/></svg>' );
	assert_same( bc_oci_has( $out, 'href' ), false, 'href must start with # (no leading space), entity-split javascript gone' );
} );

bc_test( 'strips_foreign_object', function () {
	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><foreignObject width="10" height="10"><body xmlns="http://www.w3.org/1999/xhtml"><img src="x" onerror="alert(1)"/></body></foreignObject><circle cx="1" cy="1" r="1"/></svg>' );
	assert_same( bc_oci_has( $out, 'foreign' ) || bc_oci_has( $out, 'img' ) || bc_oci_has( $out, 'alert' ), false );
	assert_same( bc_oci_has( $out, '<circle cx="1" cy="1" r="1"/>' ), true );

	foreach ( [ 'image', 'iframe', 'embed', 'object', 'pattern', 'text', 'switch', 'feImage', 'filter', 'handler', 'listener' ] as $el ) {
		$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><' . $el . ' href="https://evil.test/x"><rect width="1" height="1"/></' . $el . '></svg>' );
		assert_same( $out, '<svg xmlns="http://www.w3.org/2000/svg"/>', "{$el} removed with its children" );
	}
} );

bc_test( 'strips_animate_and_set_tricks', function () {
	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><a><animate attributeName="href" to="javascript:alert(1)"/><rect width="9" height="9"/></a><use href="#x"><set attributeName="href" to="javascript:alert(2)"/></use><animateTransform attributeName="transform"/><animateMotion/></svg>' );
	assert_same( bc_oci_has( $out, 'animate' ) || bc_oci_has( $out, 'set' ) || bc_oci_has( $out, 'javascript' ), false );
	assert_same( bc_oci_norm( $out ), '<svg xmlns="http://www.w3.org/2000/svg"><use href="#bc-oci-N-x"/></svg>' );
} );

bc_test( 'strips_javascript_and_data_in_any_attribute', function () {
	$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0" fill="javascript:alert(1)" class="  JaVa&#10;ScRiPt:x" stroke="DATA:text/html,x" mask="url(https://evil.test/m.svg#m)" clip-path="url(#c)" filter="url(#f)"/></svg>' );
	assert_same( bc_oci_norm( $out ), '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0" clip-path="url(#bc-oci-N-c)"/></svg>', 'only url(#...) references survive; filter is not on the list' );
} );

bc_test( 'rejects_xxe_entity', function () {
	$secret = tempnam( sys_get_temp_dir(), 'bc_oci_secret_' );
	file_put_contents( $secret, 'TOPSECRET' );
	try {
		$xxe = '<?xml version="1.0"?><!DOCTYPE svg [<!ENTITY xxe SYSTEM "file://' . $secret . '">]><svg xmlns="http://www.w3.org/2000/svg"><title>&xxe;</title></svg>';
		assert_same( Sanitizer::svg( $xxe ), '', 'external entity rejected' );
		assert_same( Sanitizer::svg( '<!DOCTYPE svg [<!ENTITY a "aaaaaaaaaa"><!ENTITY b "&a;&a;&a;&a;&a;">]><svg xmlns="http://www.w3.org/2000/svg"><title>&b;</title></svg>' ), '', 'internal entity (billion laughs) rejected' );
		assert_same(
			Sanitizer::svg( '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>' ),
			'<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>',
			'R19: a plain DOCTYPE (no internal subset) is accepted and not copied'
		);
		assert_same( Sanitizer::svg( '<!DOCTYPE svg [<!ATTLIST svg onload CDATA "alert(1)">]><svg xmlns="http://www.w3.org/2000/svg"/>' ), '', 'any internal subset rejected (ATTLIST defaults)' );
		assert_same( Sanitizer::svg( '<!DOCTYPE svg [ ]><svg xmlns="http://www.w3.org/2000/svg"/>' ), '<svg xmlns="http://www.w3.org/2000/svg"/>', 'an empty internal subset declares nothing' );
		assert_same( Sanitizer::svg( '<!DOCTYPE svg [<!ENTITY % p SYSTEM "http://127.0.0.1:9/dtd"> %p;]><svg xmlns="http://www.w3.org/2000/svg"/>' ), '', 'parameter entity rejected' );

		// UTF-16 with a BOM: the byte-string <!ENTITY check cannot see it, the parsed-DTD check must.
		$utf8 = '<?xml version="1.0" encoding="UTF-16"?><!DOCTYPE svg [<!ENTITY xxe SYSTEM "file://' . $secret . '"><!ENTITY js "javascript:alert(1)">]><svg xmlns="http://www.w3.org/2000/svg"><title>&xxe;</title><path d="M0 0" aria-label="&js;"/></svg>';
		assert_same( Sanitizer::svg( "\xFF\xFE" . mb_convert_encoding( $utf8, 'UTF-16LE', 'UTF-8' ) ), '', 'UTF-16LE with BOM rejected' );
		assert_same( Sanitizer::svg( "\xFE\xFF" . mb_convert_encoding( $utf8, 'UTF-16BE', 'UTF-8' ) ), '', 'UTF-16BE with BOM rejected' );
		$plain16 = '<?xml version="1.0" encoding="UTF-16"?><svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>';
		assert_same( Sanitizer::svg( "\xFF\xFE" . mb_convert_encoding( $plain16, 'UTF-16LE', 'UTF-8' ) ), '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>', 'a clean UTF-16 icon still works' );
		assert_same( Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><title>&lt;!ENTITY</title></svg>' ), '<svg xmlns="http://www.w3.org/2000/svg"><title>&lt;!ENTITY</title></svg>', 'escaped text is not a declaration' );
	} finally {
		@unlink( $secret );
	}
} );

bc_test( 'rejects_oversize_input', function () {
	$big = '<svg xmlns="http://www.w3.org/2000/svg"><path d="' . str_repeat( 'M0 0', 262144 ) . '"/></svg>'; // > 1 MB.
	assert_same( strlen( $big ) > 1048576, true );
	assert_same( Sanitizer::svg( $big ), '', 'over 512 KB rejected' );

	$limit = '<svg xmlns="http://www.w3.org/2000/svg"><path d="' . str_repeat( 'M', Sanitizer::MAX_BYTES - 58 ) . '"/></svg>';
	assert_same( strlen( $limit ), Sanitizer::MAX_BYTES );
	assert_same( '' !== Sanitizer::svg( $limit ), true, 'exactly 512 KB is accepted' );
	assert_same( Sanitizer::svg( $limit . ' ' ), '', 'one byte over is not' );
} );

bc_test( 'keeps_plain_icon_byte_equivalent_geometry', function () {
	$out = Sanitizer::svg( BC_OCI_CART_ICON );
	$expected = str_replace(
		'<svg class="ct-icon ct-cart-panel-icon" xmlns="http://www.w3.org/2000/svg" ',
		'<svg xmlns="http://www.w3.org/2000/svg" class="ct-icon ct-cart-panel-icon" ',
		BC_OCI_CART_ICON
	);
	assert_same( $out, $expected, 'the v1 default cart icon passes unchanged apart from xmlns position' );

	$out = bc_oci_norm( Sanitizer::svg( '<?xml version="1.0" encoding="UTF-8"?>' . "\n<!-- Generator: x -->\n" . '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 10 10"><title>Bag</title><desc>d</desc><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#000" stop-opacity=".5"/></linearGradient><radialGradient id="r" cx="1" cy="1" r="1"/><clipPath id="c"><rect x="0" y="0" width="10" height="10" rx="1" ry="1"/></clipPath><mask id="m"><ellipse cx="5" cy="5" rx="2" ry="3"/></mask><symbol id="s" viewBox="0 0 1 1"><line x1="0" y1="0" x2="1" y2="1"/></symbol></defs><g transform="translate(1 1)" opacity=".9" fill-rule="evenodd" clip-rule="evenodd" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="1 2"><polyline points="0,0 1,1"/><polygon points="0,0 1,1 1,0"/><circle cx="5" cy="5" r="4" fill="url(#g)" clip-path="url(#c)" mask="url(#m)"/></g><use xlink:href="#s" x="1" y="1" width="2" height="2"/></svg>' ) );
	assert_same( 0 === strpos( $out, '<svg ' ), true, 'no XML declaration, root first' );
	assert_same( bc_oci_has( $out, '<?xml' ) || bc_oci_has( $out, '<!--' ), false, 'no prolog, no comments' );
	foreach ( [ 'title', 'desc', 'defs', 'linearGradient', 'radialGradient', 'stop', 'clipPath', 'mask', 'ellipse', 'symbol', 'line', 'g', 'polyline', 'polygon', 'circle', 'use', 'rect' ] as $el ) {
		assert_same( false !== strpos( $out, '<' . $el . ( 'g' === $el ? ' ' : '' ) ), true, "{$el} kept with canonical case" );
	}
	foreach ( [ 'gradientUnits="userSpaceOnUse"', 'stop-color="#000"', 'stop-opacity=".5"', 'offset="0"', 'transform="translate(1 1)"', 'fill-rule="evenodd"', 'clip-rule="evenodd"', 'stroke-width="1.5"', 'stroke-linecap="round"', 'stroke-dasharray="1 2"', 'points="0,0 1,1 1,0"', 'fill="url(#bc-oci-N-g)"', 'mask="url(#bc-oci-N-m)"', 'clip-path="url(#bc-oci-N-c)"', 'xlink:href="#bc-oci-N-s"', 'id="bc-oci-N-g"', '<title>Bag</title>' ] as $attr ) {
		assert_same( false !== strpos( $out, $attr ), true, "{$attr} kept" );
	}
} );

bc_test( 'non_svg_returns_empty', function () {
	$cases = [
		''                                                    => 'empty',
		'hello'                                               => 'plain text',
		"\x89PNG\r\n\x1a\n\0\0\0\rIHDR"                       => 'binary',
		'<html><body><svg/></body></html>'                    => 'root is not svg',
		'<?xml version="1.0"?><foo/>'                         => 'root is not svg',
		'<svg xmlns="http://www.w3.org/2000/svg"><path></svg>' => 'malformed',
		'<x:svg xmlns:x="http://evil.test/ns"/>'              => 'svg in a foreign namespace',
	];
	foreach ( $cases as $input => $why ) {
		assert_same( Sanitizer::svg( (string) $input ), '', $why );
	}
	assert_same( Sanitizer::svg( '<svg/>' ), '<svg xmlns="http://www.w3.org/2000/svg"/>', 'an svg without xmlns is accepted and gets the SVG namespace' );
} );

bc_test( 'restores_libxml_error_state', function () {
	$before = libxml_use_internal_errors( false );
	Sanitizer::svg( '<svg><path></svg>' );
	Sanitizer::svg( BC_OCI_HEART );
	assert_same( libxml_use_internal_errors( true ), false, 'false restored' );
	Sanitizer::svg( '<svg><path></svg>' );
	assert_same( libxml_use_internal_errors( $before ), true, 'true restored' );
	assert_same( libxml_get_errors(), [], 'no errors left behind' );
} );

// ---------------------------------------------------------------------------
// Module.
// ---------------------------------------------------------------------------

bc_test( 'strips_css_image_functions', function () {
	$fetchers = [
		'image-set(&quot;https://evil.test/a.png&quot; 1x)',
		'-webkit-image-set(&quot;https://evil.test/a.png&quot; 1x)',
		'image(&quot;https://evil.test/a.png&quot;)',
		'cross-fade(url(#a), url(https://evil.test/a.png), 50%)',
		'url(#a) image-set(&quot;https://evil.test/a.png&quot; 1x)',
		'rgb(1 2 3) image(https://evil.test/a.png)',
		'element(#a)',
		'paint(x)',
		'var(--x)',
		'env(x)',
	];
	foreach ( [ 'mask', 'fill', 'stroke', 'clip-path', 'stop-color', 'color', 'opacity', 'stroke-width' ] as $attr ) {
		foreach ( $fetchers as $value ) {
			$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0" ' . $attr . '="' . $value . '"/></svg>' );
			assert_same( $out, '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>', "{$attr}={$value} dropped" );
		}
	}

	// Values that stay.
	$keep = [
		'fill'      => [ 'none', 'currentColor', '#fff', '#ffffff80', 'rgb(0, 0, 0)', 'rgba(0 0 0 / 50%)', 'hsl(120deg 50% 50%)', 'red', 'url(#g)', 'url(#g) #000', "url('#g')" ],
		'stroke'    => [ 'none', 'currentColor', 'url(#g) none' ],
		'mask'      => [ 'url(#m)', 'none' ],
		'clip-path' => [ 'url(#c)', 'none' ],
	];
	foreach ( $keep as $attr => $values ) {
		foreach ( $values as $value ) {
			$out = bc_oci_norm( Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0" ' . $attr . '="' . $value . '"/></svg>' ) );
			assert_same( false !== strpos( $out, ' ' . $attr . '="' ), true, "{$attr}={$value} kept" );
		}
	}
	assert_same( bc_oci_norm( Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0" fill="url( \'#g\' )"/></svg>' ) ), '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0" fill="url(#bc-oci-N-g)"/></svg>', 'url() normalised and rewritten' );
	assert_same( Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0" mask="url(#m) #000" clip-path="currentColor"/></svg>' ), '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>', 'mask/clip-path take only none or url(#id)' );

	// transform: only SVG transform functions with numbers.
	foreach ( [ 'translate(1 1)', 'translate(1,1) rotate(45)', 'matrix(1 0 0 1 -2.5 3e2)', 'scale(.5)skewX(10)', 'translate(1-2)', 'rotate(45, 12, 12) , scale(2)' ] as $t ) {
		$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><g transform="' . $t . '"/></svg>' );
		assert_same( $out, '<svg xmlns="http://www.w3.org/2000/svg"><g transform="' . $t . '"/></svg>', "transform {$t} kept" );
	}
	foreach ( [ 'translate(1 1) image(https://evil.test/a)', 'translate(calc(1px))', 'perspective(1)', 'translate()', 'translate(1 1', 'url(#a)', 'translate(1 1);x' ] as $t ) {
		$out = Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><linearGradient gradientTransform="' . $t . '"/><g transform="' . $t . '"/></svg>' );
		assert_same( $out, '<svg xmlns="http://www.w3.org/2000/svg"><linearGradient/><g/></svg>', "transform {$t} dropped" );
	}
	$start = microtime( true );
	Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg"><g transform="matrix(' . str_repeat( '1', 100000 ) . 'x)"/></svg>' );
	assert_same( microtime( true ) - $start < 1.0, true, 'no catastrophic backtracking on a long number' );
} );

bc_test( 'ids_prefixed_and_references_rewritten', function () {
	$in  = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="wc_add_to_cart_params" class="woocommerce-js 9bad x&quot;y ok_2"><defs><linearGradient id="g"/><clipPath id="__proto__"/><path id="1x" d="M0 0"/><symbol id="s"/></defs><use href="#s"/><use xlink:href="#g"/><use href="#__proto__"/><path d="M0 0" fill="url(#g)" clip-path="url(#__proto__)"/></svg>';
	$a   = Sanitizer::svg( $in );
	$b   = Sanitizer::svg( $in );
	assert_same( 1 === preg_match( '/id="(bc-oci-(\d+)-)wc_add_to_cart_params"/', $a, $m ), true, 'id prefixed, so it cannot clobber window.wc_add_to_cart_params' );
	$p = $m[1];
	assert_same( false !== strpos( $a, 'id="' . $p . 'g"' ) && false !== strpos( $a, 'id="' . $p . 's"' ), true, 'every id shares the output prefix' );
	assert_same( false !== strpos( $a, '<use href="#' . $p . 's"/>' ), true, 'href follows its id' );
	assert_same( false !== strpos( $a, 'xlink:href="#' . $p . 'g"' ), true, 'xlink:href follows its id' );
	assert_same( false !== strpos( $a, 'fill="url(#' . $p . 'g)"' ), true, 'url(#id) follows its id' );
	assert_same( bc_oci_has( $a, '__proto__' ) || bc_oci_has( $a, '1x' ), false, 'ids (and references) outside [A-Za-z][A-Za-z0-9_-]* dropped' );
	assert_same( false !== strpos( $a, 'class="woocommerce-js ok_2"' ), true, 'class names outside the pattern dropped, the rest kept' );
	assert_same( Sanitizer::svg( '<svg xmlns="http://www.w3.org/2000/svg" class="9 $x"/>' ), '<svg xmlns="http://www.w3.org/2000/svg"/>', 'no valid class left: attribute dropped' );
	assert_same( 1 === preg_match( '/id="bc-oci-(\d+)-wc_add_to_cart_params"/', $b, $m2 ) && $m2[1] !== $m[2], true, 'two outputs get different numbers' );

	// The module renumbers per printed icon, so two icons cached separately (same n) still differ on the page.
	$x = OffcanvasIcons::renumber( '<svg xmlns="http://www.w3.org/2000/svg"><defs><g id="bc-oci-1-a"/></defs><use href="#bc-oci-1-a"/></svg>' );
	$y = OffcanvasIcons::renumber( '<svg xmlns="http://www.w3.org/2000/svg"><defs><g id="bc-oci-1-a"/></defs><use href="#bc-oci-1-a"/></svg>' );
	assert_same( $x !== $y, true, 'renumbered per print' );
	assert_same( 1 === preg_match( '/id="(bc-oci-\d+-)a"\/><\/defs><use href="#\1a"/', $x ), true, 'id and reference renumbered together' );
	assert_same( OffcanvasIcons::renumber( '<svg/>' ), '<svg/>', 'nothing to renumber' );
} );

bc_test( 'v1_option_ids_still_read', function () {
	$module = bc_oci_boot( [], [
		'blocksy_child_minicart_panel_icon_id' => '12',
		'blocksy_child_wishlist_panel_icon_id' => 34,
	] );
	assert_same( $module->icon_id( 'minicart' ), 12, 'Customizer option read as v1 did' );
	assert_same( $module->icon_id( 'wishlist' ), 34 );
	assert_same( $module->icon_id( 'nope' ), 0, 'unknown key' );
	assert_same( OffcanvasIcons::OPTIONS, [
		'minicart' => 'blocksy_child_minicart_panel_icon_id',
		'wishlist' => 'blocksy_child_wishlist_panel_icon_id',
	] );

	$module = bc_oci_boot( [ 'minicart_icon_id' => 56 ], [ 'blocksy_child_minicart_panel_icon_id' => 12 ] );
	assert_same( $module->icon_id( 'minicart' ), 56, 'site config wins over the Customizer option' );
	$module = bc_oci_boot( [ 'minicart_icon_id' => 0 ], [ 'blocksy_child_minicart_panel_icon_id' => 12 ] );
	assert_same( $module->icon_id( 'minicart' ), 0, 'an explicit 0 in site config means no custom icon' );
	$module = bc_oci_boot( [ 'wishlist_icon_id' => 'junk' ], [ 'blocksy_child_wishlist_panel_icon_id' => 7 ] );
	assert_same( $module->icon_id( 'wishlist' ), 0, 'non-numeric site value is 0' );
	assert_same( bc_oci_boot()->icon_id( 'minicart' ), 0, 'nothing set' );
} );

bc_test( 'markup_inlines_sanitised_svg_with_classes', function () {
	$root = bc_oci_tmpdir();
	$file = $root . '/uploads/2026/11/bag.svg';
	file_put_contents( $file, '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" class="mine" onload="alert(1)" viewBox="0 0 1 1"><script>alert(2)</script><path d="M0 0"/></svg>' );
	$module = bc_oci_boot( [], [ 'blocksy_child_minicart_panel_icon_id' => 5 ], [ 5 => [ 'file' => $file, 'mime' => 'image/svg+xml' ] ], $root );

	assert_same(
		$module->markup( 'minicart', 'ct-cart-panel-icon' ),
		'<svg xmlns="http://www.w3.org/2000/svg" class="ct-icon ct-cart-panel-icon mine" viewBox="0 0 1 1"><path d="M0 0"/></svg>',
		'classes prepended to an existing class, as v1 did; script and onload gone'
	);

	file_put_contents( $file, BC_OCI_HEART );
	$GLOBALS['bc_wp_stub']['post_field']['post_modified'] = '2026-11-02 00:00:00';
	assert_same(
		$module->markup( 'minicart', 'ct-cart-panel-icon' ),
		'<svg class="ct-icon ct-cart-panel-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15"><path d="M7.5,13.9l-0.4-0.3c-0.2-0.2-4.6-3.5-5.8-4.8C0.4,7.7-0.1,6.4,0,5.1z"/></svg>',
		'no class: class attribute inserted right after <svg, as v1 did'
	);
	assert_same( $module->markup( 'minicart' ), str_replace( 'ct-icon ct-cart-panel-icon', 'ct-icon', $module->markup( 'minicart', 'ct-cart-panel-icon' ) ), 'extra class optional' );
	assert_same( $module->markup( 'wishlist' ), '', 'unset icon -> empty, caller keeps its default' );
	assert_same( $GLOBALS['bc_oci']['upload_dir_create'][0] ?? null, false, 'wp_upload_dir() never creates directories' );
} );

bc_test( 'markup_escapes_the_extra_class_with_real_esc_attr', function () {
	$root = var_export( dirname( __DIR__, 2 ), true );
	$out  = bc_test_run_isolated(
		"define( 'ABSPATH', '/' );\n"
		. "function esc_attr( \$t ) { return htmlspecialchars( (string) \$t, ENT_QUOTES, 'UTF-8' ); }\n"
		. "require {$root} . '/src/Core/Module.php';\n"
		. "require {$root} . '/modules/offcanvas-icons/Sanitizer.php';\n"
		. "require {$root} . '/modules/offcanvas-icons/Module.php';\n"
		. "echo json_encode( [ Blaze\\Child\\Modules\\OffcanvasIcons\\Module::with_classes( '<svg xmlns=\"http://www.w3.org/2000/svg\"/>', 'a\" onload=\"x \$1 \\\\1' ), Blaze\\Child\\Modules\\OffcanvasIcons\\Module::with_classes( '<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"k\"/>', '\$0' ) ] );"
	);
	assert_same( $out[0], '<svg class="ct-icon a&quot; onload=&quot;x $1 \\1" xmlns="http://www.w3.org/2000/svg"/>', 'quotes escaped, $1 and \\1 stay literal' );
	assert_same( $out[1], '<svg xmlns="http://www.w3.org/2000/svg" class="ct-icon $0 k"/>', '$0 stays literal' );
} );

bc_test( 'raster_falls_back_to_img_and_is_not_cached', function () {
	$root = bc_oci_tmpdir();
	$file = $root . '/uploads/2026/11/bag.png';
	file_put_contents( $file, 'png' );
	$module = bc_oci_boot( [], [ 'blocksy_child_wishlist_panel_icon_id' => 9 ], [ 9 => [ 'file' => $file, 'mime' => 'image/png' ] ], $root );
	$GLOBALS['bc_wp_stub']['attachment_image_url']['9:full'] = 'https://example.test/wp-content/uploads/2026/11/bag.png';
	assert_same( $module->markup( 'wishlist', 'ct-wishlist-panel-icon' ), '<img class="ct-icon ct-wishlist-panel-icon" src="https://example.test/wp-content/uploads/2026/11/bag.png" alt="" />', 'v1 <img> fallback' );
	assert_same( $GLOBALS['bc_core_store']['transients'], [], 'nothing cached for a non-SVG' );

	$GLOBALS['bc_wp_stub']['attachment_image_url'] = [];
	assert_same( $module->markup( 'wishlist' ), '', 'no URL -> empty' );
} );

bc_test( 'svg_outside_uploads_is_never_read_and_falls_back_to_img', function () {
	$root = bc_oci_tmpdir();
	file_put_contents( $root . '/outside/evil.svg', BC_OCI_HEART );
	symlink( $root . '/outside/evil.svg', $root . '/uploads/2026/11/link.svg' );
	file_put_contents( $root . '/uploads-evil.svg', BC_OCI_HEART );
	$cases = [
		$root . '/outside/evil.svg'                    => 'plain path outside uploads',
		$root . '/uploads/2026/11/../../../outside/evil.svg' => 'traversal out of uploads',
		$root . '/uploads/2026/11/link.svg'            => 'symlink inside uploads pointing outside',
		$root . '/uploads-evil.svg'                    => 'sibling sharing the uploads prefix',
		$root . '/uploads/2026/11/missing.svg'         => 'missing file',
	];
	$img = '<img class="ct-icon ct-cart-panel-icon" src="https://example.test/wp-content/uploads/icon.svg" alt="" />';
	foreach ( $cases as $path => $why ) {
		$module = bc_oci_boot( [], [ 'blocksy_child_minicart_panel_icon_id' => 3 ], [ 3 => [ 'file' => $path, 'mime' => 'image/svg+xml' ] ], $root );
		$GLOBALS['bc_wp_stub']['attachment_image_url']['3:full'] = 'https://example.test/wp-content/uploads/icon.svg';
		assert_same( $module->markup( 'minicart', 'ct-cart-panel-icon' ), $img, "{$why}: never inlined, R21 v1 <img> fallback" );
		assert_same( $GLOBALS['bc_core_store']['transients'], [], "{$why}: nothing cached" );
		$GLOBALS['bc_wp_stub']['attachment_image_url'] = [];
		assert_same( $module->markup( 'minicart' ), '', "{$why}: no URL -> empty" );
	}
	$module = bc_oci_boot( [], [ 'blocksy_child_minicart_panel_icon_id' => 3 ], [ 3 => [ 'file' => $root . '/outside/evil.svg', 'mime' => 'image/svg+xml' ] ], '' );
	assert_same( $module->markup( 'minicart' ), '', 'no uploads dir at all, no URL' );

	$out = bc_test_run_isolated(
		"define( 'ABSPATH', '/' );\n"
		. "function esc_attr( \$t ) { return htmlspecialchars( (string) \$t, ENT_QUOTES, 'UTF-8' ); }\n"
		. "function esc_url( \$u ) { return str_replace( [ '\"', '<', '>' ], [ '%22', '%3C', '%3E' ], (string) \$u ); }\n"
		. "function get_option( \$n, \$d = false ) { return 'blocksy_child_minicart_panel_icon_id' === \$n ? 3 : \$d; }\n"
		. "function apply_filters( \$h, \$v ) { return \$v; }\n"
		. "function get_post_mime_type( \$id ) { return 'image/svg+xml'; }\n"
		. "function get_post_field( \$f, \$id ) { return 'x'; }\n"
		. "function get_transient( \$k ) { return false; }\n"
		. "function get_attached_file( \$id ) { return '/nonexistent/icon.svg'; }\n"
		. "function wp_upload_dir( \$t = null, \$c = true ) { return [ 'basedir' => sys_get_temp_dir() ]; }\n"
		. "function trailingslashit( \$s ) { return rtrim( \$s, '/' ) . '/'; }\n"
		. "function wp_get_attachment_image_url( \$id, \$s ) { return 'https://example.test/a\"onerror=\"x.svg'; }\n"
		. "require " . var_export( dirname( __DIR__, 2 ), true ) . " . '/src/Core/Module.php';\n"
		. "require " . var_export( dirname( __DIR__, 2 ), true ) . " . '/modules/offcanvas-icons/Sanitizer.php';\n"
		. "require " . var_export( dirname( __DIR__, 2 ), true ) . " . '/modules/offcanvas-icons/Module.php';\n"
		. "\$m = new Blaze\\Child\\Modules\\OffcanvasIcons\\Module( [ 'id' => 'offcanvas-icons' ], [] );\n"
		. "echo json_encode( [ \$m->markup( 'minicart', 'a\"b' ) ] );"
	);
	assert_same( $out[0], '<img class="ct-icon a&quot;b" src="https://example.test/a%22onerror=%22x.svg" alt="" />', 'class and URL escaped in the fallback' );
} );

bc_test( 'sanitised_svg_cached_by_id_and_post_modified', function () {
	$root = bc_oci_tmpdir();
	$file = $root . '/uploads/2026/11/bag.svg';
	file_put_contents( $file, '<svg xmlns="http://www.w3.org/2000/svg"><path d="M1 1"/></svg>' );
	$module = bc_oci_boot( [], [ 'blocksy_child_minicart_panel_icon_id' => 5 ], [ 5 => [ 'file' => $file, 'mime' => 'image/svg+xml', 'modified' => '2026-11-01 10:00:00' ] ], $root );

	$first = $module->markup( 'minicart' );
	assert_same( count( $GLOBALS['bc_core_store']['transients'] ), 1, 'one transient written' );
	$key = array_keys( $GLOBALS['bc_core_store']['transients'] )[0];
	assert_same( 0 === strpos( $key, 'blaze_child_' ), true, 'transient prefixed blaze_child_' );
	assert_same( strlen( $key ) <= 172, true, 'transient name fits wp_options' );
	assert_same( $GLOBALS['bc_core_store']['transients'][ $key ], '<svg xmlns="http://www.w3.org/2000/svg"><path d="M1 1"/></svg>', 'the sanitised SVG is cached, without caller classes' );
	assert_same( $GLOBALS['bc_core_store']['transient_ttl'][ $key ] > 0, true, 'cache expires (not autoloaded forever)' );

	file_put_contents( $file, '<svg xmlns="http://www.w3.org/2000/svg"><path d="M2 2"/></svg>' );
	assert_same( $module->markup( 'minicart' ), $first, 'same id + post_modified: served from cache, file not re-read' );

	$GLOBALS['bc_wp_stub']['post_field']['post_modified'] = '2026-11-01 11:00:00';
	assert_same( bc_oci_has( $module->markup( 'minicart' ), 'M2 2' ), true, 'new post_modified: re-read and re-sanitised' );
	assert_same( count( $GLOBALS['bc_core_store']['transients'] ), 2 );
} );

bc_test( 'rejected_svg_records_health_and_caches_empty', function () {
	$root = bc_oci_tmpdir();
	$file = $root . '/uploads/2026/11/bad.svg';
	file_put_contents( $file, '<!DOCTYPE svg [<!ATTLIST svg onload CDATA "alert(1)">]><svg xmlns="http://www.w3.org/2000/svg"/>' );
	$module = bc_oci_boot( [], [ 'blocksy_child_wishlist_panel_icon_id' => 8 ], [ 8 => [ 'file' => $file, 'mime' => 'image/svg+xml' ] ], $root );
	assert_same( $module->markup( 'wishlist' ), '', 'rejected SVG -> empty, caller keeps its default' );
	$health = Health::all();
	assert_same( array_column( $health, 'code' ), [ 'svg_rejected' ] );
	assert_same( $health[0]['context'], [ 'module' => 'offcanvas-icons', 'attachment' => 8 ] );
	assert_same( array_values( $GLOBALS['bc_core_store']['transients'] ), [ '' ], 'the empty result is cached too, so the file is not re-parsed per request' );
	assert_same( $module->markup( 'wishlist' ), '' );
} );

bc_test( 'customizer_controls_registered_like_v1', function () {
	$module = bc_oci_boot();
	assert_same( count( $GLOBALS['bc_test_hooks'] ), 1, 'one hook, nothing else' );
	assert_same( remove_action( 'customize_register', [ $module, 'register_controls' ], 10 ), true, 'customize_register @ 10 removable by [ module, register_controls ]' );

	$fake = new BC_OCI_Fake_Customizer();
	$module->register_controls( $fake );
	assert_same( $fake->calls[0][0], 'section' );
	assert_same( $fake->calls[0][1], 'blocksy_child_offcanvas_icons' );
	assert_same( $fake->calls[0][2]['priority'], 160 );
	assert_same( $fake->calls[0][2]['title'], 'Off-canvas Panel Icons (Child Theme)' );
	$settings = array_values( array_filter( $fake->calls, function ( $c ) {
		return 'setting' === $c[0];
	} ) );
	assert_same( array_column( $settings, 1 ), [ 'blocksy_child_minicart_panel_icon_id', 'blocksy_child_wishlist_panel_icon_id' ] );
	assert_same( $settings[0][2], [
		'type'              => 'option',
		'capability'        => 'manage_options',
		'default'           => '',
		'sanitize_callback' => 'absint',
	] );
	$controls = array_values( array_filter( $fake->calls, function ( $c ) {
		return 'control' === $c[0];
	} ) );
	assert_same( array_column( $controls, 1 ), [ 'blocksy_child_minicart_panel_icon_id', 'blocksy_child_wishlist_panel_icon_id' ] );
	assert_same( $controls[0][3], 'WP_Customize_Media_Control' );
	assert_same( $controls[0][2]['label'], 'Minicart Drawer Icon' );
	assert_same( $controls[1][2]['label'], 'Wishlist Drawer Icon' );
	assert_same( $controls[0][2]['section'], 'blocksy_child_offcanvas_icons' );
	assert_same( $controls[0][2]['mime_type'], 'image' );
} );

bc_test( 'v1_functions_kept_for_v1_callers', function () {
	$root = bc_oci_tmpdir();
	$file = $root . '/uploads/2026/11/bag.svg';
	file_put_contents( $file, '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><path d="M0 0"/></svg>' );
	$module = bc_oci_boot( [], [ 'blocksy_child_minicart_panel_icon_id' => 5 ], [ 5 => [ 'file' => $file, 'mime' => 'image/svg+xml' ] ], $root );

	assert_same( function_exists( 'blocksy_child_offcanvas_icon_markup' ), true, 'inc/woocommerce.php and inc/wishlist-offcanvas.php check function_exists()' );
	$instances = new ReflectionProperty( Registry::class, 'instances' );
	$instances->setValue( null, [ 'offcanvas-icons' => $module ] );
	try {
		assert_same( blocksy_child_offcanvas_icon_markup( 'minicart', 'ct-cart-panel-icon' ), '<svg class="ct-icon ct-cart-panel-icon" xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>', 'v1 helper returns the sanitised markup' );
		assert_same( blocksy_child_offcanvas_icon_id( 'minicart' ), 5 );
		assert_same( blocksy_child_offcanvas_icon_options(), OffcanvasIcons::OPTIONS );
		$fake = new BC_OCI_Fake_Customizer();
		blocksy_child_register_offcanvas_icon_controls( $fake );
		assert_same( count( $fake->calls ), 5, 'section + 2 settings + 2 controls' );

		$instances->setValue( null, [] );
		assert_same( blocksy_child_offcanvas_icon_markup( 'minicart' ), '', 'module not booted -> empty, never unsanitised' );
		assert_same( blocksy_child_offcanvas_icon_id( 'minicart' ), 0 );
	} finally {
		$instances->setValue( null, [] );
	}
} );

bc_test( 'manifest_follows_rulings', function () {
	$m = bc_oci_manifest();
	assert_same( $m['status'], 'stable' );
	assert_same( $m['loadable'], true );
	assert_same( $m['default'], false );
	assert_same( $m['surfaces'], [ 'drawer.cart.icon', 'drawer.wishlist.icon' ], 'R3' );
	assert_same( $m['provides'], [ 'component.icons' ], 'R3' );
	assert_same( $m['requires'], [], 'v1 uses WordPress core only' );
	assert_same( $m['legacy']['files'], [ 'inc/offcanvas-icons.php' ] );
	assert_same( $m['settings']['minicart_icon_id']['default'], 0 );
	assert_same( $m['settings']['wishlist_icon_id']['default'], 0 );
	assert_same( array_keys( $m['legacy'] ), [ 'files' ], 'the v1 option ids stay live storage, not aliases' );
} );
