<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Debug utility to test Judge.me tab conditional loading
 * Only loads in debug mode for administrators
 */

if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || ! current_user_can( 'manage_options' ) ) {
	return;
}

/**
 * Test Judge.me plugin detection
 */
function blaze_blocksy_test_judgeme_detection() {
	// Include plugin functions if not already loaded
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	$results = array(
		'plugin_active' => is_plugin_active( 'judgeme-product-reviews/judgeme.php' ),
		'function_exists' => function_exists( 'judgeme_widget' ),
		'shortcode_exists' => shortcode_exists( 'jgm-review-widget' ),
		'should_show_tab' => false,
		'woocommerce_active' => class_exists( 'WooCommerce' ),
		'timestamp' => current_time( 'mysql' ),
	);

	// Determine if tab should be shown
	$results['should_show_tab'] = (
		$results['plugin_active'] ||
		$results['function_exists'] ||
		$results['shortcode_exists']
	);

	return $results;
}

/**
 * Add debug information to admin bar (admin users only)
 */
function blaze_blocksy_add_judgeme_debug_admin_bar( $wp_admin_bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$test_results = blaze_blocksy_test_judgeme_detection();
	$should_show = $test_results['should_show_tab'] ?? false;

	$wp_admin_bar->add_node(
		array(
			'id'    => 'judgeme-debug',
			'title' => 'Judge.me Tab: ' . ( $should_show ? '✅ Active' : '❌ Disabled' ),
			'href'  => '#',
			'meta'  => array(
				'title' => 'Click to see debug info in console',
			),
		)
	);
}
add_action( 'admin_bar_menu', 'blaze_blocksy_add_judgeme_debug_admin_bar', 999 );

/**
 * Add debug script to footer
 */
function blaze_blocksy_add_judgeme_debug_script() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$test_results = blaze_blocksy_test_judgeme_detection();
	?>
	<script>
	// Add click handler for admin bar debug button
	document.addEventListener('DOMContentLoaded', function() {
		const debugButton = document.querySelector('#wp-admin-bar-judgeme-debug a');
		if (debugButton) {
			debugButton.addEventListener('click', function(e) {
				e.preventDefault();
				console.group('🔍 Judge.me Reviews Tab Debug - Manual Trigger');
				console.log('Full test results:', <?php echo json_encode( $test_results, JSON_PRETTY_PRINT ); ?>);
				console.groupEnd();
			});
		}
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'blaze_blocksy_add_judgeme_debug_script' );

/**
 * Add debug information to product pages
 */
function blaze_blocksy_add_judgeme_product_debug_info() {
	if ( ! current_user_can( 'manage_options' ) || ! is_product() ) {
		return;
	}

	$test_results = blaze_blocksy_test_judgeme_detection();

	echo '<div style="background: #f0f0f0; padding: 15px; margin: 20px 0; border-left: 4px solid #0073aa;">';
	echo '<h4>🔍 Debug: Judge.me Reviews Tab Detection</h4>';
	echo '<pre style="font-size: 12px; overflow-x: auto;">';
	echo htmlspecialchars( print_r( $test_results, true ) );
	echo '</pre>';
	echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'blaze_blocksy_add_judgeme_product_debug_info', 25 );
