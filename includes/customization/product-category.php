<?php
/**
 * Product Category/Archive Page Customizations
 *
 * Handles sidebar header, hero sections, and category descriptions
 * for WooCommerce archive pages.
 *
 * @package    Blaze_Commerce
 * @subpackage Product_Category
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the WooCommerce sidebar header HTML.
 *
 * Single source of truth for both PHP and JavaScript.
 * Filterable via 'woo_sidebar_title' and 'woo_sidebar_icon' hooks.
 *
 * @since 1.0.0
 * @return string The sidebar header HTML or empty string.
 */
function blaze_get_woo_sidebar_header_html() {
	/**
	 * Filter the sidebar title text.
	 *
	 * @since 1.0.0
	 * @param string $title The sidebar title.
	 */
	$sidebar_title = apply_filters( 'woo_sidebar_title', __( 'Filter', 'blaze-commerce' ) );

	/**
	 * Filter the sidebar icon SVG.
	 *
	 * @since 1.0.0
	 * @param string $icon The SVG icon markup.
	 */
	$sidebar_icon = apply_filters(
		'woo_sidebar_icon',
		'<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
			<path d="M19 22V11" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M19 7V2" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M12 22V17" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M12 13V2" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M5 22V11" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M5 7V2" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M3 11H7" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M17 11H21" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M10 13H14" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>'
	);

	if ( empty( $sidebar_title ) ) {
		return '';
	}

	return sprintf(
		'<div class="woo-sidebar-header">
			<h2 class="woo-sidebar-title">%s %s</h2>
		</div>',
		$sidebar_icon,
		esc_html( $sidebar_title )
	);
}

/**
 * Check if WooCommerce sidebar is enabled.
 *
 * @since 1.0.0
 * @return bool True if WooCommerce sidebar is active, false otherwise.
 */
function blaze_is_woo_sidebar_enabled() {
	// Only apply to WooCommerce pages.
	if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
		return false;
	}

	// Check if Blocksy function exists.
	if ( ! function_exists( 'blocksy_sidebar_position' ) ) {
		return false;
	}

	// Sidebar is active if position is not 'none'.
	return 'none' !== blocksy_sidebar_position();
}

/**
 * Enqueue product category styles and scripts.
 *
 * @since 1.0.0
 */
function blaze_enqueue_archive_assets() {
	// Only on WooCommerce archive pages.
	if ( ! is_product_category() && ! is_product_tag() && ! is_shop() ) {
		return;
	}

	// Check if constants are defined.
	if ( ! defined( 'BLAZE_BLOCKSY_URL' ) ) {
		return;
	}

	wp_enqueue_style(
		'blaze-blocksy-archive',
		BLAZE_BLOCKSY_URL . '/assets/css/archive.css',
		array(),
		'1.0.0'
	);

	wp_enqueue_script(
		'blaze-blocksy-archive',
		BLAZE_BLOCKSY_URL . '/assets/js/archive.js',
		array( 'jquery' ),
		'1.0.0',
		true
	);

	// Pass sidebar header HTML to JavaScript for AJAX restoration.
	wp_localize_script(
		'blaze-blocksy-archive',
		'blazeArchive',
		array(
			'sidebarHeaderHTML' => blaze_get_woo_sidebar_header_html(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'blaze_enqueue_archive_assets' );

/**
 * Display category description after shop loop.
 *
 * @since 1.0.0
 */
function blaze_display_category_description() {
	if ( ! is_product_category() ) {
		return;
	}

	$term = get_queried_object();

	if ( ! $term || ! isset( $term->name, $term->description ) ) {
		return;
	}

	$term_title  = $term->name;
	$description = $term->description;

	if ( empty( $description ) ) {
		return;
	}
	?>
	<div class="ct-product-category-description-wrapper">
		<h4 class="ct-module-title"><?php echo esc_html( $term_title ); ?></h4>
		<div class="ct-product-category-description">
			<?php echo wp_kses_post( $description ); ?>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_after_shop_loop', 'blaze_display_category_description', 9999 );

/**
 * Move hero section to the top position when sidebar is active.
 *
 * @since 1.0.0
 */
function blaze_move_hero_section_top() {
	if ( ! blaze_is_woo_sidebar_enabled() ) {
		return;
	}

	if ( ! function_exists( 'blocksy_output_hero_section' ) ) {
		return;
	}

	// Output hero sections at the top position.
	echo blocksy_output_hero_section( array( 'type' => 'type-2' ) );
	echo blocksy_output_hero_section( array( 'type' => 'type-1' ) );
}
add_action( 'woocommerce_before_main_content', 'blaze_move_hero_section_top', 5 );

/**
 * Remove duplicate hero sections when sidebar is active.
 *
 * @since 1.0.0
 * @param string $type The hero section type.
 */
function blaze_remove_duplicate_hero( $type ) {
	if ( ! blaze_is_woo_sidebar_enabled() ) {
		return;
	}

	static $hero_rendered = false;

	if ( $hero_rendered ) {
		ob_start();

		add_action(
			'blocksy:hero:after',
			function () {
				ob_end_clean();
			},
			999
		);
	} else {
		$hero_rendered = true;
	}
}
add_action( 'blocksy:hero:before', 'blaze_remove_duplicate_hero', 1 );

/**
 * Add custom body class when WooCommerce sidebar is active.
 *
 * @since 1.0.0
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 */
function blaze_add_sidebar_body_class( $classes ) {
	if ( blaze_is_woo_sidebar_enabled() ) {
		$classes[] = 'shop-sidebar-active';
	}

	return $classes;
}
add_filter( 'body_class', 'blaze_add_sidebar_body_class' );

/**
 * Add sidebar title using the sidebar start hook.
 *
 * @since 1.0.0
 */
function blaze_add_sidebar_title() {
	// Check if we're on WooCommerce pages with sidebar.
	if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
		return;
	}

	// Check if sidebar is active.
	if ( ! function_exists( 'blocksy_sidebar_position' ) || 'none' === blocksy_sidebar_position() ) {
		return;
	}

	// Output the header using the helper function.
	echo blaze_get_woo_sidebar_header_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'blocksy:sidebar:start', 'blaze_add_sidebar_title' );
