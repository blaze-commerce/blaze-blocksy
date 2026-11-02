<?php
namespace Blaze\Child\Modules\RecentlyViewed;

defined( 'ABSPATH' ) || exit;

/**
 * Recently viewed products on the product page, cache-safe (port of v1 inc/recently-viewed.php; design from PR #273).
 *
 * v1 read the visitor's cookie in PHP and printed their products into the page. Behind a full-page cache that
 * either never ran or stored one visitor's list in HTML served to everyone. This port prints only an empty,
 * hidden container that is identical for every visitor. assets/recently-viewed.js keeps the list in the browser
 * and fills the container from the WooCommerce Store API. Every callback is a named public method, so site code
 * can remove_action( 'x', [ Api\module( 'recently-viewed' ), 'method' ] ).
 */
class Module extends \Blaze\Child\Core\Module {

	/** Script handle Assets gives assets/recently-viewed.js (bc-<id>-<file name>). */
	const HANDLE = 'bc-recently-viewed-recently-viewed';

	/** Ids the browser keeps; v1 kept 15 in its cookie. */
	const STORE_MAX = 15;

	const DEFAULT_LIMIT = 8;

	/** v1's heading text and msgid (R23). */
	const DEFAULT_TITLE = 'Recently Viewed';

	/** v1 defaults of Blocksy's related-products column theme mod. */
	const DEFAULT_COLUMNS = [
		'desktop' => 4,
		'tablet'  => 3,
		'mobile'  => 2,
	];

	/**
	 * @var self|null
	 */
	private static $booted = null;

	/**
	 * @var bool v1 printed the section once per request, whichever hook fired first.
	 */
	private $rendered = false;

	/**
	 * @var int Containers built in this request (keeps heading ids unique if site code prints more than one).
	 */
	private $printed = 0;

	/**
	 * @var bool The inline config was added to the script this request.
	 */
	private $config_added = false;

	/**
	 * @var bool The module's script and style are known to be enqueued this request.
	 */
	private $assets_ready = false;

	/**
	 * The booted instance, or null when the module is off.
	 *
	 * @return self|null
	 */
	public static function booted(): ?self {
		return self::$booted;
	}

	/**
	 * Registers the container on v1's two hooks (same priorities) and the script config. No server-side tracking.
	 *
	 * @return void
	 */
	public function boot(): void {
		self::$booted = $this;
		require_once __DIR__ . '/compat.php';
		add_action( 'blocksy:woocommerce:product-single:related:after', [ $this, 'render' ] );
		add_action( 'woocommerce_after_main_content', [ $this, 'render' ], 10 );
		// After Assets enqueues the module's script (wp_enqueue_scripts, priority 20).
		add_action( 'wp_enqueue_scripts', [ $this, 'add_config' ], 21 );
	}

	/**
	 * Cards shown at most: the `limit` setting, 1 to STORE_MAX; anything else means the default.
	 *
	 * @return int
	 */
	public function limit(): int {
		$value = $this->setting( 'limit' );
		$limit = is_numeric( $value ) ? (int) $value : 0;
		if ( $limit < 1 ) {
			return self::DEFAULT_LIMIT;
		}
		return min( $limit, self::STORE_MAX );
	}

	/**
	 * Section heading (it names the section): the `title` setting, else the translated default.
	 *
	 * @return string
	 */
	public function title(): string {
		$value = $this->setting( 'title' );
		$title = is_string( $value ) && '' !== trim( $value ) ? $value : self::DEFAULT_TITLE;
		if ( self::DEFAULT_TITLE === $title ) {
			return __( 'Recently Viewed', 'blocksy-child' );
		}
		return $title;
	}

	/**
	 * Column counts from Blocksy's related-products setting (as v1), each 1 to 6.
	 *
	 * @return array{desktop:int,tablet:int,mobile:int}
	 */
	public function columns(): array {
		$value = get_theme_mod( 'woocommerce_related_products_slideshow_columns', self::DEFAULT_COLUMNS );
		if ( function_exists( 'blocksy_expand_responsive_value' ) ) {
			$value = blocksy_expand_responsive_value( $value );
		}
		$out = [];
		foreach ( self::DEFAULT_COLUMNS as $device => $default ) {
			$n              = is_array( $value ) && is_numeric( $value[ $device ] ?? null ) ? (int) $value[ $device ] : $default;
			$out[ $device ] = max( 1, min( 6, $n ) );
		}
		return $out;
	}

	/**
	 * The empty, hidden container. It holds only site-wide values and the current product id, never visitor data.
	 *
	 * @param int $product_id Product whose page this is (excluded from the list, then recorded as viewed); 0 = none.
	 * @return string
	 */
	public function container_markup( int $product_id ): string {
		// Callers outside the hooked product-page path (site code, the v1 alias) still get a working section.
		$this->ensure_assets();
		++$this->printed;
		$heading_id = 'bc-recently-viewed-title' . ( $this->printed > 1 ? '-' . $this->printed : '' );
		$columns    = $this->columns();
		$style      = '--bc-rv-columns:' . $columns['desktop'] . ';--bc-rv-columns-tablet:' . $columns['tablet'] . ';--bc-rv-columns-mobile:' . $columns['mobile'];

		return '<section class="bc-recently-viewed related products is-layout-slider is-width-constrained" data-bc-rv'
			. ' data-limit="' . esc_attr( (string) $this->limit() ) . '"'
			. ' data-product-id="' . esc_attr( (string) max( 0, $product_id ) ) . '"'
			. ' aria-labelledby="' . esc_attr( $heading_id ) . '"'
			. ' style="' . esc_attr( $style ) . '" hidden>'
			. '<h2 class="ct-module-title bc-recently-viewed__title" id="' . esc_attr( $heading_id ) . '">' . esc_html( $this->title() ) . '</h2>'
			. '</section>';
	}

	/**
	 * Prints the container on a product page, once per request (v1 hooked two places with a static guard).
	 *
	 * @return void
	 */
	public function render(): void {
		if ( $this->rendered || ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		$this->rendered = true;
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- container_markup() escapes every value.
		echo $this->container_markup( (int) get_queried_object_id() );
	}

	/**
	 * Script config, printed before the script. Site-wide values only, so cached pages stay correct.
	 *
	 * @return array{restUrl:string,cookiePath:string,cookieDomain:string}
	 */
	public function config(): array {
		return [
			'restUrl'      => rest_url( 'wc/store/v1/products' ),
			// WooCommerce's wc_setcookie() uses these, so the mirrored cookie replaces v1's instead of adding one.
			'cookiePath'   => defined( 'COOKIEPATH' ) && COOKIEPATH ? (string) COOKIEPATH : '/',
			'cookieDomain' => defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ? (string) COOKIE_DOMAIN : '',
		];
	}

	/**
	 * wp_enqueue_scripts, priority 21: adds the config when Assets enqueued the script (product pages).
	 *
	 * @return void
	 */
	public function add_config(): void {
		if ( ! wp_script_is( self::HANDLE, 'enqueued' ) ) {
			return;
		}
		$this->assets_ready = true;
		$this->attach_config();
	}

	/**
	 * Adds the config as window.blazeChild.recentlyViewedConfig before the module script, once per request.
	 *
	 * @return void
	 */
	public function attach_config(): void {
		if ( $this->config_added ) {
			return;
		}
		$this->config_added = true;
		wp_add_inline_script(
			self::HANDLE,
			'window.blazeChild=window.blazeChild||{};window.blazeChild.recentlyViewedConfig=' . wp_json_encode( $this->config() ) . ';',
			'before'
		);
	}

	/**
	 * Enqueues the module's script and style (same handles, defer and footer as Assets) and the config, once per
	 * request. Used when the container is printed where the manifest `where` (pdp) did not enqueue them.
	 *
	 * @return void
	 */
	public function ensure_assets(): void {
		if ( ! $this->assets_ready ) {
			$this->assets_ready = true;
			if ( ! wp_script_is( self::HANDLE, 'enqueued' ) ) {
				$base = ( defined( 'BLAZE_CHILD_URL' ) ? BLAZE_CHILD_URL : '' ) . 'modules/recently-viewed/assets/recently-viewed';
				wp_enqueue_script(
					self::HANDLE,
					$base . '.js',
					[],
					self::asset_version( 'js' ),
					[
						'strategy'  => 'defer',
						'in_footer' => true,
					]
				);
				wp_enqueue_style( self::HANDLE, $base . '.css', [], self::asset_version( 'css' ) );
			}
		}
		$this->attach_config();
	}

	/**
	 * File modification time of an asset, as Assets versions them.
	 *
	 * @param string $ext 'js' or 'css'.
	 * @return string
	 */
	private static function asset_version( string $ext ): string {
		$file = __DIR__ . '/assets/recently-viewed.' . $ext;
		return file_exists( $file ) ? (string) filemtime( $file ) : '2.0.0';
	}
}
