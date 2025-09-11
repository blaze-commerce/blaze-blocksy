<?php

/**
 * Enqueue product category styles
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_product_category() && ! is_product_tag() && ! is_shop() )
		return;

	wp_enqueue_style( 'blaze-blocksy-archive', BLAZE_BLOCKSY_URL . '/assets/css/archive.css' );
	wp_enqueue_script( 'blaze-blocksy-archive', BLAZE_BLOCKSY_URL . '/assets/js/archive.js', array( 'jquery' ), '1.0.0', true );
} );


/**
 * Display category description
 */
add_action( 'woocommerce_after_shop_loop', function () {
	if ( ! is_product_category() ) {
		return;
	}
	$term = get_queried_object();
	$term_title = $term->name;
	$description = $term->description;


	?>
	<div class="ct-product-category-description-wrapper">
		<h4 class="ct-module-title"><?php echo $term_title; ?></h4>
		<div class="ct-product-category-description">
			<?php echo $description; ?>
		</div>
	</div>
	<?php
}, 9999 );

/**
 * Helper function to check if WooCommerce sidebar is enabled
 * 
 * @return bool True if WooCommerce sidebar is active, false otherwise
 */
function is_woo_sidebar_enabled() {
	// Only apply to WooCommerce pages (shop, product category, product tag)
	if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
		return false;
	}

	// Use Blocksy's function to check sidebar position
	// This function considers all sidebar settings and filters
	$sidebar_position = blocksy_sidebar_position();

	// Sidebar is active if position is not 'none'
	return ( $sidebar_position !== 'none' );
}

/**
 * Move hero section to the top position - ONLY when sidebar is active
 * 
 * This hook runs before WooCommerce main content with priority 5
 * to ensure it executes before Blocksy's default hooks (priority 10)
 */
add_action( 'woocommerce_before_main_content', function () {
	// Only execute if WooCommerce sidebar is active
	if ( ! is_woo_sidebar_enabled() ) {
		return;
	}

	// Output hero sections at the top position
	// type-2: Usually the main hero banner
	// type-1: Usually the breadcrumb/page title section
	echo blocksy_output_hero_section( [ 'type' => 'type-2' ] );
	echo blocksy_output_hero_section( [ 'type' => 'type-1' ] );
}, 5 );

/**
 * Remove duplicate hero sections - ONLY when sidebar is active
 * 
 * This hook captures and discards duplicate hero section output
 * using output buffering to prevent multiple hero sections from appearing
 */
add_action( 'blocksy:hero:before', function ($type) {
	// Only execute if WooCommerce sidebar is active
	if ( ! is_woo_sidebar_enabled() ) {
		return;
	}

	// Static variable to track if hero section has been rendered
	static $hero_rendered = false;

	if ( $hero_rendered ) {
		// If hero section was already rendered, start output buffering
		// to capture and discard this duplicate hero section
		ob_start();

		// Add hook to clean the buffer after hero section is complete
		add_action( 'blocksy:hero:after', function () {
			ob_end_clean(); // Discard the duplicate hero section output
		}, 999 );
	} else {
		// Mark that the first hero section has been rendered
		$hero_rendered = true;
	}
}, 1 );

/**
 * Reset static variable for each new request
 * 
 * This ensures that the hero section tracking doesn't carry over
 * between different page requests
 */
add_action( 'wp', function () {
	// Reset the static variable used in the hero section tracking
	static $hero_rendered = false;
	$hero_rendered = false;
} );

/**
 * Add custom body class when WooCommerce sidebar is active
 * 
 * This filter adds 'shop-sidebar-active' class to the body element
 * when sidebar is enabled on WooCommerce shop or category pages
 * 
 * @param array $classes Existing body classes
 * @return array Modified body classes
 */
add_filter( 'body_class', function ($classes) {
	// Check if we're on WooCommerce pages and sidebar is enabled
	if ( is_woo_sidebar_enabled() ) {
		// Add the custom class to body
		$classes[] = 'shop-sidebar-active';
	}

	return $classes;
} );

/**
 * Add sidebar title using the sidebar start hook
 * 
 * This hook fires at the very beginning of sidebar content
 * inside the sidebar container
 */
add_action( 'blocksy:sidebar:start', function () {
	// Check if we're on WooCommerce pages with sidebar
	if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
		return;
	}

	// Check if sidebar is active
	if ( blocksy_sidebar_position() === 'none' ) {
		return;
	}

	// Define the title (you can make this customizable)
	$sidebar_title = apply_filters( 'woo_sidebar_title', __( 'Filter', 'textdomain' ) );

	// Output the title with proper styling
	if ( ! empty( $sidebar_title ) ) {
		printf(
			'<div class="woo-sidebar-header">
						<h2 class="woo-sidebar-title">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M19 22V11" stroke="#020202" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M19 7V2" stroke="#020202" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 22V17" stroke="#020202" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 13V2" stroke="#020202" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M5 22V11" stroke="#020202" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M5 7V2" stroke="#020202" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M3 11H7" stroke="#020202" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M17 11H21" stroke="#020202" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10 13H14" stroke="#020202" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
						%s
						</h2>
						</div>',
			esc_html( $sidebar_title )
		);
	}
} );
