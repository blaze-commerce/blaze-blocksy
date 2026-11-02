<?php
namespace Blaze\Child\Modules\OffcanvasIcons;

defined( 'ABSPATH' ) || exit;

use Blaze\Child\Core\Health;
use Blaze\Child\Core\Registry;

/**
 * Customizer image/SVG controls for the heading icons of the mini cart and wishlist panels (port of the v1 file
 * in module.json legacy.files).
 *
 * v1 inlined the uploaded SVG file as-is on every page, so a malicious upload ran script for every visitor. This
 * port inlines only Sanitizer::svg() output, cached per attachment revision. The v1 global helpers stay defined
 * (compat.php) because v1's panel code calls them.
 */
class Module extends \Blaze\Child\Core\Module {

	/** Icon key => v1 Customizer option holding the attachment id. */
	const OPTIONS = [
		'minicart' => 'blocksy_child_minicart_panel_icon_id',
		'wishlist' => 'blocksy_child_wishlist_panel_icon_id',
	];

	/** Icon key => site-config setting that takes precedence over the Customizer option. */
	const SETTINGS = [
		'minicart' => 'minicart_icon_id',
		'wishlist' => 'wishlist_icon_id',
	];

	/** Bump when Sanitizer output changes, so cached icons are re-sanitised. */
	const CACHE_VERSION = 1;

	const CACHE_TTL = 604800;

	/**
	 * @var int Inlined SVGs printed in this request; renumbers cached id prefixes so two icons never share an id.
	 */
	private static $printed = 0;

	/**
	 * The booted instance, or null when the module is off. The module id is this folder's name.
	 *
	 * @return self|null
	 */
	public static function booted(): ?self {
		$module = Registry::instance( basename( __DIR__ ) );
		return $module instanceof self ? $module : null;
	}

	/**
	 * Defines the v1 helpers and registers the Customizer controls (same hook and priority as v1).
	 *
	 * @return void
	 */
	public function boot(): void {
		require_once __DIR__ . '/compat.php';
		add_action( 'customize_register', [ $this, 'register_controls' ] );
	}

	/**
	 * Attachment id configured for an icon: the site-config setting when set (0 = none), else the Customizer option.
	 *
	 * @param string $which 'minicart' or 'wishlist'.
	 * @return int 0 when unset.
	 */
	public function icon_id( string $which ): int {
		if ( ! isset( self::SETTINGS[ $which ] ) ) {
			return 0;
		}

		$key   = self::SETTINGS[ $which ];
		$value = $this->setting( $key );
		if ( $this->has_site_setting( $key ) || ( is_numeric( $value ) && (int) $value > 0 ) ) {
			return is_numeric( $value ) ? max( 0, (int) $value ) : 0;
		}

		return max( 0, (int) get_option( self::OPTIONS[ $which ], 0 ) );
	}

	/**
	 * Icon markup, or '' when none is configured or it cannot be used (the caller then keeps its default icon).
	 *
	 * An SVG attachment is inlined after sanitising, so it inherits Blocksy's `ct-icon` sizing and currentColor.
	 * Any other image, and an SVG whose file cannot be read from uploads, falls back to an <img> as in v1 (an SVG in
	 * <img> cannot run script). An SVG the sanitiser rejects gives ''.
	 *
	 * @param string $which       'minicart' or 'wishlist'.
	 * @param string $extra_class Class(es) added after `ct-icon`.
	 * @return string
	 */
	public function markup( string $which, string $extra_class = '' ): string {
		$id = $this->icon_id( $which );
		if ( ! $id ) {
			return '';
		}

		if ( 'image/svg+xml' === get_post_mime_type( $id ) ) {
			$svg = $this->sanitised_svg( $id );
			if ( '' === $svg ) {
				return '';
			}
			if ( null !== $svg ) {
				return self::with_classes( self::renumber( $svg ), $extra_class );
			}
		}

		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( ! $url ) {
			return '';
		}

		return '<img class="' . esc_attr( trim( 'ct-icon ' . $extra_class ) ) . '" src="' . esc_url( $url ) . '" alt="" />';
	}

	/**
	 * Sanitised SVG of an attachment, cached in a transient keyed by attachment id and post_modified.
	 *
	 * A file that cannot be read from inside the uploads directory gives null and is not cached. A file that fails
	 * sanitising gives '', is cached as '' and is recorded in the health log.
	 *
	 * @param int $id Attachment id.
	 * @return string|null
	 */
	public function sanitised_svg( int $id ): ?string {
		$key    = 'blaze_child_oci_' . self::CACHE_VERSION . '_' . $id . '_' . md5( (string) get_post_field( 'post_modified', $id ) );
		$cached = get_transient( $key );
		if ( is_string( $cached ) ) {
			return $cached;
		}

		$raw = self::read_upload( (string) get_attached_file( $id ) );
		if ( null === $raw ) {
			return null;
		}

		$svg = Sanitizer::svg( $raw );
		if ( '' === $svg ) {
			Health::record(
				'svg_rejected',
				"Panel icon attachment {$id} was not inlined: the SVG is over 512 KB, declares entities or an internal DTD subset, does not parse, or its root is not <svg>. The panel keeps its default icon.",
				[
					'module'     => $this->id(),
					'attachment' => $id,
				]
			);
		}

		set_transient( $key, $svg, self::CACHE_TTL );
		return $svg;
	}

	/**
	 * Contents of a file inside the uploads directory (compared after resolving symlinks and `..`).
	 *
	 * @param string $file Absolute path from get_attached_file().
	 * @return string|null Null when the file is missing, unreadable or outside uploads; '' when over the size limit.
	 */
	private static function read_upload( string $file ): ?string {
		if ( '' === $file ) {
			return null;
		}

		$uploads = wp_upload_dir( null, false );
		$base    = realpath( (string) $uploads['basedir'] );
		$real    = realpath( $file );
		if ( false === $base || false === $real || 0 !== strpos( $real, trailingslashit( $base ) ) || ! is_file( $real ) || ! is_readable( $real ) ) {
			return null;
		}

		$size = filesize( $real );
		if ( false === $size || $size > Sanitizer::MAX_BYTES ) {
			return '';
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local upload, not a remote fetch.
		$raw = file_get_contents( $real );
		return false === $raw ? null : $raw;
	}

	/**
	 * Gives the cached markup's `bc-oci-<n>-` id prefix a number unique to this request, so the mini cart and wishlist
	 * icons (cached separately, possibly with the same n) never share an id on one page.
	 *
	 * @param string $svg Sanitizer::svg() output.
	 * @return string
	 */
	public static function renumber( string $svg ): string {
		if ( false === strpos( $svg, 'bc-oci-' ) ) {
			return $svg;
		}
		return (string) preg_replace( '/(?<![A-Za-z0-9_-])bc-oci-\d+-/', 'bc-oci-' . ( ++self::$printed ) . '-', $svg );
	}

	/**
	 * Adds `ct-icon` plus extra classes to the root <svg> of sanitised markup: prepended to an existing class
	 * attribute, else inserted right after `<svg`, as v1 did.
	 *
	 * @param string $svg         Sanitizer::svg() output (starts with `<svg`, attribute values escaped).
	 * @param string $extra_class Class(es) added after `ct-icon`.
	 * @return string
	 */
	public static function with_classes( string $svg, string $extra_class = '' ): string {
		$classes = esc_attr( trim( 'ct-icon ' . $extra_class ) );
		$end     = strpos( $svg, '>' );
		$tag     = false === $end ? $svg : substr( $svg, 0, $end );

		if ( preg_match( '/\sclass="/', $tag, $match, PREG_OFFSET_CAPTURE ) ) {
			$at = $match[0][1] + strlen( $match[0][0] );
			return substr( $svg, 0, $at ) . $classes . ' ' . substr( $svg, $at );
		}

		return '<svg class="' . $classes . '"' . substr( $svg, 4 );
	}

	/**
	 * Registers the Customizer section and its two media controls, with v1's ids, labels and priority.
	 *
	 * @param \WP_Customize_Manager $wp_customize Customizer manager.
	 * @return void
	 */
	public function register_controls( $wp_customize ): void {
		$wp_customize->add_section(
			'blocksy_child_offcanvas_icons',
			[
				'title'       => __( 'Off-canvas Panel Icons (Child Theme)', 'blocksy-child' ),
				'priority'    => 160,
				'description' => __( 'Upload a custom image/SVG for the icon shown in the heading of the minicart drawer and the wishlist drawer. These are separate from the header bar icons (set those under Header > Cart / Wishlist). Leave empty to keep the theme default.', 'blocksy-child' ),
			]
		);

		$labels = [
			'minicart' => __( 'Minicart Drawer Icon', 'blocksy-child' ),
			'wishlist' => __( 'Wishlist Drawer Icon', 'blocksy-child' ),
		];

		foreach ( $labels as $key => $label ) {
			$setting = self::OPTIONS[ $key ];

			$wp_customize->add_setting(
				$setting,
				[
					'type'              => 'option',
					'capability'        => 'manage_options',
					'default'           => '',
					'sanitize_callback' => 'absint',
				]
			);

			$wp_customize->add_control(
				new \WP_Customize_Media_Control(
					$wp_customize,
					$setting,
					[
						'label'       => $label,
						'description' => __( 'SVG recommended (square viewBox, ~24×24). Falls back to the theme default when empty.', 'blocksy-child' ),
						'section'     => 'blocksy_child_offcanvas_icons',
						'mime_type'   => 'image',
					]
				)
			);
		}
	}
}
