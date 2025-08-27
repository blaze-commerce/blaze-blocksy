<?php
/**
 * WooCommerce Thank You Page Blaze Commerce Design Implementation
 *
 * Complete layout redesign based on Blaze Commerce specifications.
 * Pixel-perfect responsive implementation with preserved functionality.
 *
 * @package Blocksy_Child
 * @since 2.0.3
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get formatted shipping display for order
 *
 * @param WC_Order $order The order object
 * @return string Formatted shipping display
 */
function blocksy_child_get_shipping_display( $order ) {
    // Get shipping methods from the order
    $shipping_methods = $order->get_shipping_methods();
    $shipping_total = $order->get_shipping_total();

    // If no shipping methods or shipping is free
    if ( empty( $shipping_methods ) || $shipping_total == 0 ) {
        return 'Free';
    }

    // Get the first shipping method (most common case)
    $shipping_method = reset( $shipping_methods );
    $method_title = $shipping_method->get_method_title();

    // Format the shipping cost
    $formatted_cost = wc_price( $shipping_total );

    // Return method name with cost, or just cost if no method name
    if ( $method_title ) {
        return $method_title . ' - ' . $formatted_cost;
    } else {
        return $formatted_cost;
    }
}

/**
 * Replace the entire thank you page content with Blaze Commerce design
 *
 * @param int $order_id The order ID
 */
function blocksy_child_blaze_commerce_thank_you_content( $order_id ) {
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    // Start output buffering to replace default content
    ob_start();
    ?>
    <div class="blaze-commerce-thank-you-wrapper">
        <div class="blaze-commerce-thank-you-container">

            <!-- Header Section -->
            <div class="blaze-commerce-thank-you-header">
                <h1 class="blaze-commerce-thank-you-title">Thank you for your Order!</h1>
            </div>

            <!-- Main Content Area -->
            <div class="blaze-commerce-main-content">
                <div class="blaze-commerce-order-details-container">
                    
                    <div class="blaze-commerce-order-confirmation-container">
                        <p class="blaze-commerce-order-confirmation">
                        Your order number is <strong>#<?php echo $order->get_order_number(); ?></strong>
                        </p>
                        <p class="blaze-commerce-order-number">
                            You will receive your confirmation email to <strong><?php echo esc_html( $order->get_billing_email() ); ?></strong> within 5 minutes. If you do not see the email in your inbox, please check your spam or junk folder
                        </p>
                        <p class="blaze-commerce-email-confirmation">
                            If you still do not receive the email, please contact our support team at <strong><?php echo esc_html( get_option( 'admin_email' ) ); ?></strong>
                        </p>
                    </div>
                    <?php 

                    // blocksy_child_blaze_commerce_order_details( $order ); 

                    ?>
                    <?php blocksy_child_blaze_commerce_addresses_section( $order ); ?>
                </div>
                <?php blocksy_child_blaze_commerce_account_creation( $order ); ?>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="blaze-commerce-order-summary">
                <?php blocksy_child_blaze_commerce_order_summary( $order ); ?>
            </div>

        </div>
    </div>
    <?php

    // Get the content and clean the buffer
    $content = ob_get_clean();
    echo $content;
}
add_action( 'woocommerce_thankyou', 'blocksy_child_blaze_commerce_thank_you_content', 5 );

/**
 * Generate order details section matching Blaze Commerce design
 *
 * @param WC_Order $order The order object
 */
function blocksy_child_blaze_commerce_order_details( $order ) {
    ?>
    <div class="blaze-commerce-order-details">
        <h3>Order Details</h3>
        <div class="blaze-commerce-order-meta">
            <div class="blaze-commerce-order-info-item">
                <strong>Order Date:</strong>
                <span><?php
                    $order_date = $order->get_date_created();
                    echo $order_date ? $order_date->format( 'F j, Y' ) : 'N/A';
                ?></span>
            </div>
            <div class="blaze-commerce-order-info-item">
                <strong>Payment Method:</strong>
                <span><?php echo $order->get_payment_method_title(); ?></span>
            </div>
            <div class="blaze-commerce-order-info-item">
                <strong>Order Status:</strong>
                <span><?php echo wc_get_order_status_name( $order->get_status() ); ?></span>
            </div>
            <div class="blaze-commerce-order-info-item">
                <strong>Delivery:</strong>
                <span><?php echo blocksy_child_get_shipping_display( $order ); ?></span>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Generate addresses section matching Blaze Commerce design
 *
 * @param WC_Order $order The order object
 */
function blocksy_child_blaze_commerce_addresses_section( $order ) {
    ?>
    <div class="blaze-commerce-addresses-section">
        <div class="blaze-commerce-addresses-grid">

            <!-- Shipping Address -->
            <div class="blaze-commerce-address-block">
                <h4 class="blaze-commerce-address-title">Shipping Address</h4>
                <div class="blaze-commerce-address-content">
                    <?php
                    $shipping_address = $order->get_formatted_shipping_address();
                    if ( $shipping_address ) {
                        echo wp_kses_post( $shipping_address );
                    } else {
                        echo wp_kses_post( $order->get_formatted_billing_address() );
                    }
                    ?>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="blaze-commerce-address-block">
                <h4 class="blaze-commerce-address-title">Billing Address</h4>
                <div class="blaze-commerce-address-content">
                    <?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?>
                </div>
            </div>

        </div>
    </div>
    <?php
}

/**
 * Generate account creation section matching Blaze Commerce design
 *
 * @param WC_Order $order The order object
 */
function blocksy_child_blaze_commerce_account_creation( $order ) {
    // Only show if user is not logged in
    if ( is_user_logged_in() ) {
        return;
    }

    ?>
    <div class="blaze-commerce-account-creation">
        <h3 class="blaze-commerce-account-title">Create an account from this order & checkout faster next time</h3>

        <ul class="blaze-commerce-account-benefits">
            <li>Save Time on future orders</li>
            <li>Order Tracking</li>
            <li>Access to Past Invoices</li>
            <li>Access Order Receipts</li>
        </ul>

        <form class="blaze-commerce-account-form" method="post" action="<?php echo esc_url( wc_get_endpoint_url( 'order-received', $order->get_id(), wc_get_checkout_url() ) ); ?>">
            <div class="blaze-commerce-form-field">
                <label for="account_first_name">First name *</label>
                <input type="text" id="account_first_name" name="account_first_name" value="<?php echo esc_attr( $order->get_billing_first_name() ); ?>" required>
            </div>

            <div class="blaze-commerce-form-field">
                <label for="account_last_name">Last name *</label>
                <input type="text" id="account_last_name" name="account_last_name" value="<?php echo esc_attr( $order->get_billing_last_name() ); ?>" required>
            </div>

            <div class="blaze-commerce-form-field blaze-commerce-password-field">
                <label for="account_password">Password *</label>
                <input type="password" id="account_password" name="account_password" required>
            </div>

            <?php wp_nonce_field( 'create_account_from_order', 'create_account_nonce' ); ?>
            <input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
            <input type="hidden" name="create_account_from_order" value="1">

            <button type="submit" class="blaze-commerce-create-account-btn">CREATE ACCOUNT</button>
        </form>
    </div>
    <?php
}

/**
 * Generate order summary section matching Blaze Commerce design
 *
 * @param WC_Order $order The order object
 */
function blocksy_child_blaze_commerce_order_summary( $order ) {
    ?>
    <div class="blaze-commerce-summary-header">
        <h3 class="blaze-commerce-summary-title">
            ORDER SUMMARY
        </h3>
    </div>

    <div class="blaze-commerce-summary-content">
        <?php
        // Get order items
        $items = $order->get_items();
        foreach ( $items as $item_id => $item ) {
            $product = $item->get_product();
            if ( ! $product ) continue;

            $product_image = wp_get_attachment_image_src( $product->get_image_id(), 'thumbnail' );
            $image_url = $product_image ? $product_image[0] : wc_placeholder_img_src();
            ?>
            <div class="blaze-commerce-product-item">
                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" class="blaze-commerce-product-image">
                <div class="blaze-commerce-product-details">
                    <h4 class="blaze-commerce-product-name"><?php echo esc_html( $product->get_name() ); ?></h4>
                    <p class="blaze-commerce-product-quantity">Qty: <?php echo esc_html( $item->get_quantity() ); ?></p>
                </div>
                <p class="blaze-commerce-product-price">$<?php echo number_format( $item->get_total(), 2 ); ?></p>
            </div>
            <?php
        }
        ?>

        <div class="blaze-commerce-summary-breakdown">
            <div class="blaze-commerce-summary-row">
                <span class="blaze-commerce-summary-label">Subtotal</span>
                <span class="blaze-commerce-summary-value">$<?php echo number_format( $order->get_subtotal(), 2 ); ?></span>
            </div>

            <div class="blaze-commerce-summary-row">
                <span class="blaze-commerce-summary-label">Delivery</span>
                <span class="blaze-commerce-summary-value"><?php echo blocksy_child_get_shipping_display( $order ); ?></span>
            </div>

            <?php if ( $order->get_total_tax() > 0 ) : ?>
            <div class="blaze-commerce-summary-row">
                <span class="blaze-commerce-summary-label">Tax</span>
                <span class="blaze-commerce-summary-value">$<?php echo number_format( $order->get_total_tax(), 2 ); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="blaze-commerce-summary-total">
            <span class="blaze-commerce-total-label">Total</span>
            <span class="blaze-commerce-total-value">$<?php echo number_format( $order->get_total(), 2 ); ?> USD</span>
        </div>
    </div>
    <?php
}

/**
 * Handle account creation from order
 */
function blocksy_child_handle_account_creation_from_order() {
    if ( ! isset( $_POST['create_account_from_order'] ) || ! isset( $_POST['create_account_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['create_account_nonce'], 'create_account_from_order' ) ) {
        return;
    }

    if ( is_user_logged_in() ) {
        return;
    }

    $order_id = intval( $_POST['order_id'] );
    $order = wc_get_order( $order_id );

    if ( ! $order ) {
        return;
    }

    $first_name = sanitize_text_field( $_POST['account_first_name'] );
    $last_name = sanitize_text_field( $_POST['account_last_name'] );
    $password = $_POST['account_password'];
    $email = $order->get_billing_email();

    // Check if user already exists
    if ( email_exists( $email ) ) {
        wc_add_notice( 'An account with this email address already exists.', 'error' );
        return;
    }

    // Create the user account
    $user_id = wp_create_user( $email, $password, $email );

    if ( is_wp_error( $user_id ) ) {
        wc_add_notice( 'Account creation failed. Please try again.', 'error' );
        return;
    }

    // Update user meta
    wp_update_user( array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => $first_name . ' ' . $last_name
    ) );

    // Link order to user
    $order->set_customer_id( $user_id );
    $order->save();

    // Log the user in
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id );

    wc_add_notice( 'Account created successfully! You are now logged in.', 'success' );
}
add_action( 'init', 'blocksy_child_handle_account_creation_from_order' );

/**
 * Hide default WooCommerce order details table on thank you page
 */
function blocksy_child_hide_default_order_details() {
    if ( is_wc_endpoint_url( 'order-received' ) ) {
        remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
        remove_action( 'woocommerce_thankyou', 'woocommerce_order_again_button', 20 );
    }
}
add_action( 'wp', 'blocksy_child_hide_default_order_details' );

/**
 * Modify the default thank you message to be empty (we replace it with our custom content)
 *
 * @param string $message The default message
 * @param WC_Order $order The order object
 * @return string Empty message
 */
function blocksy_child_hide_default_thank_you_message( $message, $order ) {
    return ''; // Return empty to hide default message
}
add_filter( 'woocommerce_thankyou_order_received_text', 'blocksy_child_hide_default_thank_you_message', 10, 2 );

/**
 * Enqueue thank you page specific styles and scripts
 */
function blocksy_child_enqueue_thank_you_assets() {
    if ( is_wc_endpoint_url( 'order-received' ) ) {
        wp_enqueue_style(
            'blocksy-child-thank-you-css',
            get_stylesheet_directory_uri() . '/assets/css/thank-you.css',
            array(),
            '2.0.3'
        );

        wp_enqueue_script(
            'blocksy-child-thank-you-js',
            get_stylesheet_directory_uri() . '/assets/js/thank-you.js',
            array( 'jquery' ),
            '2.0.3',
            true
        );

        // Add inline script for immediate visibility fix and order summary toggle
        wp_add_inline_script( 'blocksy-child-thank-you-js', '
            // CRITICAL FIX: Ensure Blaze Commerce elements are visible immediately
            jQuery(document).ready(function($) {
                console.log("🔧 Applying visibility fixes for Blaze Commerce elements");

                // Remove any opacity: 0 styles that might be hiding elements
                $(".blaze-commerce-thank-you-header, .blaze-commerce-order-summary, .blaze-commerce-main-content, .blaze-commerce-order-details, .blaze-commerce-addresses-section, .blaze-commerce-account-creation").css({
                    "opacity": "1",
                    "visibility": "visible",
                    "display": "block"
                });

                // Ensure the blocksy_child_blaze_commerce_order_summary function is available globally
                window.blocksy_child_blaze_commerce_order_summary = function() {
                    console.log("✅ blocksy_child_blaze_commerce_order_summary function called");
                    return true;
                };

                console.log("✅ Blaze Commerce elements visibility fixed");

                // Order summary toggle functionality
                $(".blaze-commerce-summary-toggle").on("click", function() {
                    var $content = $(".blaze-commerce-summary-content");
                    var $button = $(this);

                    $content.slideToggle(300, function() {
                        if ($content.is(":visible")) {
                            $button.text("Hide");
                        } else {
                            $button.text("Show");
                        }
                    });
                });
            });
        ' );
    }
}
add_action( 'wp_enqueue_scripts', 'blocksy_child_enqueue_thank_you_assets' );
