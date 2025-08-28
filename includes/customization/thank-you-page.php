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
 * Check if order uses pickup shipping method
 *
 * @param WC_Order $order The order object
 * @return bool True if pickup method, false otherwise
 */
function blocksy_child_is_pickup_order( $order ) {
    $shipping_methods = $order->get_shipping_methods();

    if ( empty( $shipping_methods ) ) {
        // Fallback: Check if shipping total is 0 and no shipping address
        $shipping_total = $order->get_shipping_total();
        $has_shipping_address = ! empty( $order->get_shipping_address_1() );

        if ( $shipping_total === 0.0 && ! $has_shipping_address ) {
            return true;
        }

        return false;
    }

    foreach ( $shipping_methods as $shipping_method ) {
        $method_id = $shipping_method->get_method_id();
        $method_title = strtolower( $shipping_method->get_method_title() );

        // Check for pickup method indicators
        if (
            $method_id === 'local_pickup' ||
            strpos( $method_title, 'pickup' ) !== false ||
            strpos( $method_title, 'collection' ) !== false
        ) {
            return true;
        }
    }

    // Additional fallback: Check order meta for pickup indicators
    $delivery_method = $order->get_meta( '_delivery_method' );
    if ( $delivery_method === 'pickup' ) {
        return true;
    }

    return false;
}

/**
 * Get pickup locations for an order
 *
 * @param WC_Order $order The order object
 * @return array Array of pickup location data
 */
function blocksy_child_get_pickup_locations( $order ) {
    $pickup_locations = array();

    // First, try to get pickup location from the order's shipping method
    $shipping_methods = $order->get_shipping_methods();

    foreach ( $shipping_methods as $shipping_method ) {
        $method_id = $shipping_method->get_method_id();
        $instance_id = $shipping_method->get_instance_id();

        // Check if this is a pickup method
        if ( $method_id === 'local_pickup' || strpos( strtolower( $shipping_method->get_method_title() ), 'pickup' ) !== false ) {

            // Try to get pickup location from shipping method instance settings
            if ( $instance_id ) {
                $shipping_method_settings = get_option( 'woocommerce_local_pickup_' . $instance_id . '_settings', array() );

                if ( ! empty( $shipping_method_settings ) ) {
                    $location_name = isset( $shipping_method_settings['title'] ) ? $shipping_method_settings['title'] : '';
                    $location_address = '';

                    // Try to build address from various fields
                    $address_fields = array( 'address_1', 'address_2', 'city', 'state', 'postcode' );
                    $address_parts = array();

                    foreach ( $address_fields as $field ) {
                        if ( isset( $shipping_method_settings[$field] ) && ! empty( $shipping_method_settings[$field] ) ) {
                            $address_parts[] = $shipping_method_settings[$field];
                        }
                    }

                    if ( ! empty( $address_parts ) ) {
                        $location_address = implode( ', ', $address_parts );
                    }

                    if ( $location_name || $location_address ) {
                        $pickup_locations[] = array(
                            'name' => $location_name ?: 'Pickup Location',
                            'address' => $location_address,
                            'instructions' => isset( $shipping_method_settings['instructions'] ) ? $shipping_method_settings['instructions'] : ''
                        );
                    }
                }
            }

            // If no specific location found, try order meta
            $pickup_location_meta = $order->get_meta( '_pickup_location' );
            if ( $pickup_location_meta && empty( $pickup_locations ) ) {
                $pickup_locations[] = array(
                    'name' => is_array( $pickup_location_meta ) && isset( $pickup_location_meta['name'] ) ? $pickup_location_meta['name'] : 'Pickup Location',
                    'address' => is_array( $pickup_location_meta ) && isset( $pickup_location_meta['address'] ) ? $pickup_location_meta['address'] : $pickup_location_meta,
                    'instructions' => is_array( $pickup_location_meta ) && isset( $pickup_location_meta['instructions'] ) ? $pickup_location_meta['instructions'] : ''
                );
            }
        }
    }

    // If no pickup locations found from order, try WooCommerce store address as fallback
    if ( empty( $pickup_locations ) ) {
        $store_address = array();
        $store_address_parts = array();

        // Get WooCommerce store address settings
        $address_1 = get_option( 'woocommerce_store_address' );
        $address_2 = get_option( 'woocommerce_store_address_2' );
        $city = get_option( 'woocommerce_store_city' );
        $state = get_option( 'woocommerce_default_country' );
        $postcode = get_option( 'woocommerce_store_postcode' );

        // Parse country/state with input validation
        if ( $state && strpos( $state, ':' ) !== false ) {
            $parts = explode( ':', $state );
            if ( count( $parts ) === 2 ) {
                list( $country, $state_code ) = $parts;
                $countries = WC()->countries->get_countries();
                $states = WC()->countries->get_states( $country );

                $country_name = isset( $countries[$country] ) ? $countries[$country] : $country;
                $state_name = isset( $states[$state_code] ) ? $states[$state_code] : $state_code;
            } else {
                $country_name = '';
                $state_name = $state;
            }
        } else {
            $country_name = '';
            $state_name = $state;
        }

        // Build address parts
        if ( $address_1 ) $store_address_parts[] = $address_1;
        if ( $address_2 ) $store_address_parts[] = $address_2;
        if ( $city ) $store_address_parts[] = $city;
        if ( $state_name ) $store_address_parts[] = $state_name;
        if ( $postcode ) $store_address_parts[] = $postcode;
        if ( $country_name && $country_name !== $state_name ) $store_address_parts[] = $country_name;

        if ( ! empty( $store_address_parts ) ) {
            $pickup_locations[] = array(
                'name' => get_bloginfo( 'name' ) . ' Store',
                'address' => implode( ', ', $store_address_parts ),
                'instructions' => ''
            );
        }
    }

    // Return empty array if no valid pickup locations found
    return $pickup_locations;
}

/**
 * Get formatted shipping display for order
 *
 * @param WC_Order $order The order object
 * @return string Formatted shipping display
 */
function blocksy_child_get_shipping_display( $order ) {
	// Check if this is a pickup order
	if ( blocksy_child_is_pickup_order( $order ) ) {
		$pickup_locations = blocksy_child_get_pickup_locations( $order );
		if ( ! empty( $pickup_locations ) ) {
			$first_location = reset( $pickup_locations );
			$location_name = $first_location ? $first_location['name'] : 'Pickup Location';
			return 'Pickup (' . $location_name . ') - Free';
		} else {
			// Generic pickup message when no specific location data available
			return 'Pickup - Free';
		}
	}

	// Get shipping methods from the order
	$shipping_methods = $order->get_shipping_methods();
	$shipping_total   = $order->get_shipping_total();

	// If no shipping methods or shipping is free
	if ( empty( $shipping_methods ) || $shipping_total == 0 ) {
		return 'Free';
	}

	// Get the first shipping method (most common case)
	$shipping_method = reset( $shipping_methods );
	$method_title    = $shipping_method->get_method_title();

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
				<span>
				<?php
					$order_date = $order->get_date_created();
					echo $order_date ? $order_date->format( 'F j, Y' ) : 'N/A';
				?>
				</span>
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
 * Generate addresses section with conditional display based on shipping method
 *
 * @param WC_Order $order The order object
 */
function blocksy_child_blaze_commerce_addresses_section( $order ) {
	$is_pickup = blocksy_child_is_pickup_order( $order );
	?>
	<div class="blaze-commerce-addresses-section">
		<!-- Support both old grid and new flex layouts for backward compatibility -->
		<div class="blaze-commerce-addresses-grid blaze-commerce-addresses-flex <?php echo $is_pickup ? 'pickup-billing' : 'shipping-billing'; ?>">

			<?php if ( $is_pickup ) : ?>
			<!-- Pickup Location(s) - Show above billing address for pickup orders -->
			<?php
			$pickup_locations = blocksy_child_get_pickup_locations( $order );
			if ( ! empty( $pickup_locations ) ) :
				foreach ( $pickup_locations as $index => $location ) :
					$location_title = count( $pickup_locations ) > 1 ? 'Pickup Location ' . ( $index + 1 ) : 'Pickup Location';
			?>
			<div class="blaze-commerce-address-block">
				<h4 class="blaze-commerce-address-title"><?php echo esc_html( $location_title ); ?></h4>
				<div class="blaze-commerce-address-content">
					<?php if ( ! empty( $location['name'] ) ) : ?>
						<strong><?php echo esc_html( $location['name'] ); ?></strong><br>
					<?php endif; ?>

					<?php if ( ! empty( $location['address'] ) ) : ?>
						<?php
						// Format address with proper line breaks
						$address_lines = explode( ', ', $location['address'] );
						foreach ( $address_lines as $line_index => $line ) {
							echo esc_html( trim( $line ) );
							if ( $line_index < count( $address_lines ) - 1 ) {
								echo '<br>';
							}
						}
						?>
						<br>
					<?php endif; ?>

					<?php
					// Show custom instructions if available, otherwise default message
					$instructions = ! empty( $location['instructions'] ) ? $location['instructions'] : 'Please wait for email confirmation before pickup';
					?>
					<em><?php echo esc_html( $instructions ); ?></em>
				</div>
			</div>
			<?php
				endforeach;
			endif; // End pickup locations check
			?>
			<?php else : ?>
			<!-- Shipping Address - Only show for delivery orders -->
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
			<?php endif; ?>

			<!-- Billing Address - Always show -->
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
			if ( ! $product ) {
				continue;
			}

			$product_image = wp_get_attachment_image_src( $product->get_image_id(), 'thumbnail' );
			$image_url     = $product_image ? $product_image[0] : wc_placeholder_img_src();
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
	$order    = wc_get_order( $order_id );

	if ( ! $order ) {
		return;
	}

	$first_name = sanitize_text_field( $_POST['account_first_name'] );
	$last_name  = sanitize_text_field( $_POST['account_last_name'] );
	// Password intentionally not sanitized to preserve special characters for wp_create_user()
	$password   = $_POST['account_password'];
	$email      = $order->get_billing_email();

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
	wp_update_user(
		array(
			'ID'           => $user_id,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'display_name' => $first_name . ' ' . $last_name,
		)
	);

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
 * @param string   $message The default message
 * @param WC_Order $order The order object
 * @return string Empty message
 */
function blocksy_child_hide_default_thank_you_message( $message, $order ) {
	return ''; // Return empty to hide default message
}
add_filter( 'woocommerce_thankyou_order_received_text', 'blocksy_child_hide_default_thank_you_message', 10, 2 );

/**
 * Check if we're on the WooCommerce thank you/order received page
 *
 * Uses multiple conditional checks for maximum reliability:
 * 1. Primary: is_wc_endpoint_url('order-received') - WooCommerce standard
 * 2. Fallback: is_order_received_page() - Alternative WooCommerce function
 * 3. URL-based: Secure pattern matching for edge cases with sanitization
 *
 * @return bool True if on thank you page, false otherwise
 * @since 2.0.3
 */
function blocksy_child_is_thank_you_page() {
    // Ensure WooCommerce is active before using WooCommerce functions
    if ( ! function_exists( 'is_wc_endpoint_url' ) ) {
        return false;
    }

    // Primary check: WooCommerce endpoint detection
    if ( is_wc_endpoint_url( 'order-received' ) ) {
        return true;
    }

    // Secondary check: Alternative WooCommerce function (if available)
    if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
        return true;
    }

    // Fallback check: Secure URL pattern matching for edge cases
    $current_url = sanitize_text_field( $_SERVER['REQUEST_URI'] ?? '' );
    if ( ! empty( $current_url ) ) {
        // Use specific regex pattern to match order-received endpoint with order ID
        // Pattern: /order-received/[digits]/ or /order-received/[digits]?key=...
        if ( preg_match( '/\/order-received\/\d+(?:\/|\?|$)/', $current_url ) ) {
            return true;
        }

        // Check for checkout with order-received parameter (more specific)
        // Pattern: /checkout/ followed by order-received somewhere in query
        if ( preg_match( '/\/checkout\/.*[?&].*order-received/', $current_url ) ) {
            return true;
        }

        // Additional check for WooCommerce endpoint structure
        // Pattern: /checkout/order-received/[digits]/
        if ( preg_match( '/\/checkout\/order-received\/\d+/', $current_url ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Enqueue thank you page assets with enhanced detection
 *
 * @since 2.0.3
 */
function blocksy_child_enqueue_thank_you_assets() {
	if ( blocksy_child_is_thank_you_page() ) {
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
		wp_add_inline_script(
			'blocksy-child-thank-you-js',
			'
            // CRITICAL FIX: Ensure Blaze Commerce elements are visible immediately

/**
 * Get file version for cache busting with proper validation
 *
 * @param string $file_path Absolute file path
 * @return string Version string (validated filemtime or fallback)
 * @since 2.0.3
 */
function blocksy_child_get_file_version( $file_path ) {
    if ( file_exists( $file_path ) ) {
        $mtime = filemtime( $file_path );

        // Validate filemtime return value (can return false on failure)
        if ( $mtime !== false && is_numeric( $mtime ) && $mtime > 0 ) {
            return (string) $mtime;
        }

        // Log warning if filemtime failed but file exists
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Blaze Commerce: filemtime() failed for existing file: ' . $file_path );
        }
    }

    // Use WordPress version as fallback for better cache management
    global $wp_version;
    $fallback_version = '2.0.3';

    // Include WP version if available for better cache differentiation
    if ( ! empty( $wp_version ) ) {
        $fallback_version .= '-wp' . sanitize_key( $wp_version );
    }

    return $fallback_version;
}

/**
 * Centralized logging function for missing assets
 *
 * @param string $type Asset type (CSS, JS, etc.)
 * @param string $path File path that was not found
 * @since 2.0.3
 */
function blocksy_child_log_missing_asset( $type, $path ) {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( sprintf(
            'Blaze Commerce: Thank you page %s file not found: %s',
            sanitize_text_field( $type ),
            sanitize_text_field( $path )
        ) );
    }
}

/**
 * Add thank you page inline script functionality
 *
 * Now uses external JavaScript file for better caching and maintainability.
 * Falls back to inline script if external file is not available.
 *
 * @since 2.0.3
 */
function blocksy_child_add_thank_you_inline_script() {
    $inline_js_path = get_stylesheet_directory() . '/assets/js/thank-you-inline.js';
    $inline_js_url = get_stylesheet_directory_uri() . '/assets/js/thank-you-inline.js';

    // Prefer external file for better caching
    if ( file_exists( $inline_js_path ) ) {
        wp_enqueue_script(
            'blocksy-child-thank-you-inline',
            $inline_js_url,
            array( 'blocksy-child-thank-you-js', 'jquery' ), // Dependencies
            blocksy_child_get_file_version( $inline_js_path ),
            true // Load in footer
        );
    } else {
        // Fallback to inline script if external file not found
        blocksy_child_log_missing_asset( 'Inline JS', $inline_js_path );

        // Minimal inline fallback for critical functionality
        wp_add_inline_script( 'blocksy-child-thank-you-js', '
            // CRITICAL FALLBACK: Basic visibility fixes
            jQuery(document).ready(function($) {
                console.log("🔧 Applying fallback visibility fixes for Blaze Commerce elements");

                $(".blaze-commerce-thank-you-header, .blaze-commerce-order-summary, .blaze-commerce-main-content, .blaze-commerce-order-details, .blaze-commerce-addresses-section, .blaze-commerce-account-creation").css({
                    "opacity": "1",
                    "visibility": "visible",
                    "display": "block"
                });

                // Basic global function for compatibility
                window.blocksy_child_blaze_commerce_order_summary = function() {
                    return true;
                };

                console.log("✅ Fallback visibility fixes applied");
            });
        '
		);
	}
}

/**
 * Enqueue thank you page specific styles and scripts with enhanced conditional loading
 *
 * Features:
 * - Multiple conditional checks for reliability
 * - File existence validation with static caching
 * - Automatic cache busting with filemtime
 * - Graceful error handling
 * - Performance optimized loading with reduced file operations
 *
 * @since 2.0.3
 */
function blocksy_child_enqueue_thank_you_assets() {
    // Only proceed if we're on the thank you page
    if ( ! blocksy_child_is_thank_you_page() ) {
        return;
    }

    // Static cache for file paths and existence checks to avoid repeated operations
    static $asset_cache = null;

    if ( $asset_cache === null ) {
        $base_dir = get_stylesheet_directory();
        $base_uri = get_stylesheet_directory_uri();

        $asset_cache = array(
            'css' => array(
                'path' => $base_dir . '/assets/css/thank-you.css',
                'url'  => $base_uri . '/assets/css/thank-you.css',
                'exists' => null
            ),
            'js' => array(
                'path' => $base_dir . '/assets/js/thank-you.js',
                'url'  => $base_uri . '/assets/js/thank-you.js',
                'exists' => null
            )
        );

        // Check file existence once and cache results
        $asset_cache['css']['exists'] = file_exists( $asset_cache['css']['path'] );
        $asset_cache['js']['exists'] = file_exists( $asset_cache['js']['path'] );
    }

    // Enqueue CSS if file exists
    if ( $asset_cache['css']['exists'] ) {
        wp_enqueue_style(
            'blocksy-child-thank-you-css',
            $asset_cache['css']['url'],
            array(), // No dependencies for CSS
            blocksy_child_get_file_version( $asset_cache['css']['path'] ),
            'all' // Media type
        );
    } else {
        blocksy_child_log_missing_asset( 'CSS', $asset_cache['css']['path'] );
    }

    // Enqueue JavaScript if file exists
    if ( $asset_cache['js']['exists'] ) {
        wp_enqueue_script(
            'blocksy-child-thank-you-js',
            $asset_cache['js']['url'],
            array( 'jquery' ), // jQuery dependency
            blocksy_child_get_file_version( $asset_cache['js']['path'] ),
            true // Load in footer
        );

        // Add inline script functionality
        blocksy_child_add_thank_you_inline_script();
    } else {
        blocksy_child_log_missing_asset( 'JS', $asset_cache['js']['path'] );
    }
}

// Hook with priority 15 to ensure WooCommerce has initialized
add_action( 'wp_enqueue_scripts', 'blocksy_child_enqueue_thank_you_assets', 15 );
