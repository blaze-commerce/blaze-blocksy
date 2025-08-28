<?php
/**
 * Thank You Page Asset Loading Test
 * 
 * This file provides debugging functions to test the conditional loading
 * of thank you page assets. Only include this in development environments.
 * 
 * Usage: Add to functions.php temporarily for testing:
 * include_once get_stylesheet_directory() . '/includes/debug/thank-you-asset-test.php';
 */

// Only load in development/debug mode
if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
    return;
}

/**
 * Test function to check thank you page detection
 */
function blocksy_child_test_thank_you_detection() {
    if ( ! function_exists( 'blocksy_child_is_thank_you_page' ) ) {
        return 'Function blocksy_child_is_thank_you_page() not found';
    }
    
    $results = array();
    
    // Test WooCommerce availability
    $results['woocommerce_active'] = function_exists( 'is_wc_endpoint_url' );
    
    // Test primary detection method
    $results['is_wc_endpoint'] = function_exists( 'is_wc_endpoint_url' ) ? 
        is_wc_endpoint_url( 'order-received' ) : false;
    
    // Test secondary detection method
    $results['is_order_received_page'] = function_exists( 'is_order_received_page' ) ? 
        is_order_received_page() : false;
    
    // Test URL pattern matching
    $current_url = $_SERVER['REQUEST_URI'] ?? '';
    $results['current_url'] = $current_url;
    $results['url_contains_order_received'] = strpos( $current_url, 'order-received' ) !== false;
    
    // Test main function
    $results['blocksy_child_is_thank_you_page'] = blocksy_child_is_thank_you_page();
    
    // Test file existence
    $css_path = get_stylesheet_directory() . '/assets/css/thank-you.css';
    $js_path = get_stylesheet_directory() . '/assets/js/thank-you.js';
    $results['css_file_exists'] = file_exists( $css_path );
    $results['js_file_exists'] = file_exists( $js_path );
    
    // Test if assets are enqueued
    $results['css_enqueued'] = wp_style_is( 'blocksy-child-thank-you-css', 'enqueued' );
    $results['js_enqueued'] = wp_script_is( 'blocksy-child-thank-you-js', 'enqueued' );
    
    return $results;
}

/**
 * Add debug information to admin bar (admin users only)
 */
function blocksy_child_add_debug_admin_bar( $wp_admin_bar ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    $test_results = blocksy_child_test_thank_you_detection();
    $is_thank_you_page = $test_results['blocksy_child_is_thank_you_page'] ?? false;
    
    $wp_admin_bar->add_node( array(
        'id'    => 'thank-you-debug',
        'title' => 'Thank You Debug: ' . ( $is_thank_you_page ? '✅ Active' : '❌ Inactive' ),
        'href'  => '#',
        'meta'  => array(
            'title' => 'Click to see debug info in console'
        )
    ) );
}
add_action( 'admin_bar_menu', 'blocksy_child_add_debug_admin_bar', 999 );

/**
 * Add debug script to footer for testing
 */
function blocksy_child_add_debug_script() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    $test_results = blocksy_child_test_thank_you_detection();
    ?>
    <script>
    // Thank You Page Asset Debug Information
    console.group('🔍 Thank You Page Asset Debug');
    console.log('Test Results:', <?php echo json_encode( $test_results, JSON_PRETTY_PRINT ); ?>);
    
    // Check if assets are actually loaded in DOM
    const cssLoaded = document.querySelector('link[href*="thank-you.css"]') !== null;
    const jsLoaded = document.querySelector('script[src*="thank-you.js"]') !== null;
    
    console.log('CSS in DOM:', cssLoaded);
    console.log('JS in DOM:', jsLoaded);
    
    // Check for Blaze Commerce elements
    const blazeElements = document.querySelectorAll('[class*="blaze-commerce"]');
    console.log('Blaze Commerce elements found:', blazeElements.length);
    
    console.groupEnd();
    
    // Add click handler for admin bar debug button
    document.addEventListener('DOMContentLoaded', function() {
        const debugButton = document.querySelector('#wp-admin-bar-thank-you-debug a');
        if (debugButton) {
            debugButton.addEventListener('click', function(e) {
                e.preventDefault();
                console.group('🔍 Thank You Page Asset Debug - Manual Trigger');
                console.log('Full test results:', <?php echo json_encode( $test_results, JSON_PRETTY_PRINT ); ?>);
                console.groupEnd();
            });
        }
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'blocksy_child_add_debug_script' );

/**
 * Add debug information to WooCommerce thank you page
 */
function blocksy_child_add_thank_you_debug_info( $order_id ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    $test_results = blocksy_child_test_thank_you_detection();
    
    echo '<div style="background: #f0f0f0; padding: 15px; margin: 20px 0; border-left: 4px solid #0073aa;">';
    echo '<h4>🔍 Debug: Thank You Page Asset Loading</h4>';
    echo '<pre style="font-size: 12px; overflow-x: auto;">';
    echo htmlspecialchars( print_r( $test_results, true ) );
    echo '</pre>';
    echo '</div>';
}
add_action( 'woocommerce_thankyou', 'blocksy_child_add_thank_you_debug_info', 5 );

/**
 * Log asset loading attempts
 */
function blocksy_child_log_asset_loading() {
    if ( ! function_exists( 'blocksy_child_is_thank_you_page' ) ) {
        return;
    }
    
    $is_thank_you = blocksy_child_is_thank_you_page();
    $current_url = $_SERVER['REQUEST_URI'] ?? '';
    
    error_log( sprintf(
        'Blaze Commerce Asset Loading: URL=%s, IsThankYou=%s, WC_Active=%s',
        $current_url,
        $is_thank_you ? 'YES' : 'NO',
        function_exists( 'is_wc_endpoint_url' ) ? 'YES' : 'NO'
    ) );
}
add_action( 'wp_enqueue_scripts', 'blocksy_child_log_asset_loading', 14 ); // Run before our asset function
