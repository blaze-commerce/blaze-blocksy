<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues manifest-declared assets of enabled modules, only where their `where` contexts match.
 */
final class Assets {

	const WC_BLOCKS     = [
		'woocommerce/all-products',
		'woocommerce/handpicked-products',
		'woocommerce/product-new',
		'woocommerce/product-best-sellers',
		'woocommerce/product-top-rated',
		'woocommerce/product-on-sale',
		'woocommerce/product-category',
		'woocommerce/featured-product',
		'woocommerce/add-to-cart-form',
	];
	const WC_SHORTCODES = [
		'products',
		'product',
		'product_category',
		'add_to_cart',
		'woocommerce_cart',
		'woocommerce_checkout',
		'recent_products',
		'sale_products',
		'best_selling_products',
	];

	/**
	 * @var Module[]
	 */
	private static $modules = [];

	/**
	 * @var string
	 */
	private static $base_dir = '';

	/**
	 * @var string
	 */
	private static $base_url = '';

	/**
	 * Hooks the enqueue and body-class work for the booted modules.
	 *
	 * @param Module[] $modules  Booted modules (id => Module).
	 * @param string   $base_dir Theme directory with trailing slash (tests); defaults to BLAZE_CHILD_PATH.
	 * @param string   $base_url Theme URL with trailing slash (tests); defaults to BLAZE_CHILD_URL.
	 * @return void
	 */
	public static function register( array $modules, string $base_dir = '', string $base_url = '' ): void {
		self::$modules  = $modules;
		self::$base_dir = '' !== $base_dir ? $base_dir : ( defined( 'BLAZE_CHILD_PATH' ) ? BLAZE_CHILD_PATH : '' );
		self::$base_url = '' !== $base_url ? $base_url : ( defined( 'BLAZE_CHILD_URL' ) ? BLAZE_CHILD_URL : '' );
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue' ], 20 );
		add_filter( 'body_class', [ self::class, 'body_class' ] );
	}

	/**
	 * Contexts of the current request.
	 *
	 * @return string[]
	 */
	public static function contexts(): array {
		$ctx = [ 'all' ];
		if ( ! is_admin() ) {
			$ctx[] = 'front';
		}
		if ( self::cond( 'is_woocommerce' ) || self::cond( 'is_cart' ) || self::cond( 'is_checkout' ) || self::cond( 'is_account_page' ) || self::page_has_wc_content() ) {
			$ctx[] = 'wc';
		}
		if ( self::cond( 'is_product' ) ) {
			$ctx[] = 'pdp';
		}
		if ( self::cond( 'is_shop' ) || self::cond( 'is_product_taxonomy' ) ) {
			$ctx[] = 'archive';
		}
		if ( self::cond( 'is_cart' ) ) {
			$ctx[] = 'cart';
		}
		if ( self::cond( 'is_checkout' ) ) {
			$ctx[] = 'checkout';
		}
		if ( self::cond( 'is_account_page' ) ) {
			$ctx[] = 'account';
		}
		return $ctx;
	}

	/**
	 * True when the current singular/front page holds WooCommerce blocks or shortcodes (Tier B; never page IDs).
	 *
	 * @return bool
	 */
	public static function page_has_wc_content(): bool {
		if ( ! ( function_exists( 'is_singular' ) && is_singular() ) && ! ( function_exists( 'is_front_page' ) && is_front_page() ) ) {
			return false;
		}
		$post = function_exists( 'get_post' ) ? get_post( function_exists( 'get_queried_object_id' ) ? get_queried_object_id() : 0 ) : null;
		if ( ! $post ) {
			return false;
		}
		if ( function_exists( 'has_block' ) ) {
			foreach ( self::WC_BLOCKS as $block ) {
				if ( has_block( $block, $post ) ) {
					return true;
				}
			}
		}
		if ( function_exists( 'has_shortcode' ) ) {
			$content = (string) $post->post_content;
			foreach ( self::WC_SHORTCODES as $tag ) {
				if ( has_shortcode( $content, $tag ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Enqueues every matching asset of every registered module.
	 *
	 * @return void
	 */
	public static function enqueue(): void {
		$contexts = self::contexts();
		foreach ( self::$modules as $id => $module ) {
			$manifest = $module->manifest();
			$id       = $module->id();
			$assets   = is_array( $manifest['assets'] ?? null ) ? $manifest['assets'] : [];
			foreach ( $assets as $asset ) {
				$file  = is_array( $asset ) && is_string( $asset['file'] ?? null ) ? $asset['file'] : '';
				$where = is_array( $asset['where'] ?? null ) ? $asset['where'] : [];
				if ( '' === $file || ! array_intersect( $where, $contexts ) ) {
					continue;
				}
				self::enqueue_one( $id, $manifest, $file, is_array( $asset['deps'] ?? null ) ? array_values( $asset['deps'] ) : [] );
			}
		}
	}

	/**
	 * Adds bc-m-<id> for each registered module.
	 *
	 * @param mixed $classes Body classes.
	 * @return mixed
	 */
	public static function body_class( $classes ) {
		if ( ! is_array( $classes ) ) {
			return $classes;
		}
		foreach ( self::$modules as $module ) {
			$classes[] = 'bc-m-' . $module->id();
		}
		return $classes;
	}

	/**
	 * @param string   $id       Module id.
	 * @param array    $manifest Manifest.
	 * @param string   $file     Path relative to the module folder.
	 * @param string[] $deps     Declared dependencies.
	 * @return void
	 */
	private static function enqueue_one( string $id, array $manifest, string $file, array $deps ): void {
		if ( in_array( 'jquery', $deps, true ) && 'legacy' !== ( $manifest['status'] ?? '' ) ) {
			Health::record(
				'jquery_dep_refused',
				"Module '{$id}' asset '{$file}' declares jquery; dependency dropped",
				[
					'module' => $id,
					'file'   => $file,
				]
			);
			$deps = array_values( array_diff( $deps, [ 'jquery' ] ) );
		}
		$rel   = 'modules/' . $id . '/' . ltrim( $file, '/' );
		$mtime = @filemtime( self::$base_dir . $rel );
		if ( false === $mtime ) {
			Health::record(
				'asset_missing',
				"Module '{$id}' asset '{$file}' not found",
				[
					'module' => $id,
					'file'   => $file,
				]
			);
			return;
		}
		$ver    = (string) $mtime;
		$url    = self::$base_url . $rel;
		$ext    = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
		$handle = 'bc-' . $id . '-' . pathinfo( $file, PATHINFO_FILENAME );
		if ( 'js' === $ext ) {
			wp_enqueue_script(
				$handle,
				$url,
				$deps,
				$ver,
				[
					'strategy'  => 'defer',
					'in_footer' => true,
				]
			);
		} elseif ( 'css' === $ext ) {
			wp_enqueue_style( $handle, $url, $deps, $ver );
		}
	}

	/**
	 * Calls a conditional tag only when it exists (WooCommerce may be inactive).
	 *
	 * @param string $fn Function name.
	 * @return bool
	 */
	private static function cond( string $fn ): bool {
		return function_exists( $fn ) && (bool) $fn();
	}
}
