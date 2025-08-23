<?php
/**
 * Blaze Checkout Block Helper Functions
 *
 * Based on the original Blaze Commerce checkout template with multi-step enhancements.
 * Preserves all original Blaze styling and structure while adding progressive checkout flow.
 */

// DEBUG: File loaded at timestamp - FORCE RELOAD
error_log( 'BLAZE DEBUG: helper-functions.php loaded at ' . date( 'Y-m-d H:i:s' ) . ' - VERSION 3.0 - FORCE RELOAD' );

// Clear all caches to force reload
if ( function_exists( 'opcache_reset' ) ) {
	opcache_reset();
	error_log( 'BLAZE DEBUG: PHP OPcache cleared' );
}

if ( function_exists( 'wp_cache_flush' ) ) {
	wp_cache_flush();
	error_log( 'BLAZE DEBUG: WordPress object cache cleared' );
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clear all caches to ensure updated code is loaded
 */
function blaze_clear_all_caches() {
	// Clear PHP OPcache
	if (function_exists('opcache_reset')) {
		opcache_reset();
		error_log('BLAZE DEBUG: PHP OPcache cleared');
	}

	// Clear WordPress object cache
	if (function_exists('wp_cache_flush')) {
		wp_cache_flush();
		error_log('BLAZE DEBUG: WordPress object cache cleared');
	}

	// Clear transients
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'");
	error_log('BLAZE DEBUG: Transients cleared');
}

// Clear caches immediately when this file is loaded
blaze_clear_all_caches();

/**
 * Ensure WooCommerce is properly initialized and session is available
 */
function blaze_ensure_woocommerce_initialized() {
	// Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}

	// Initialize WooCommerce if not already done
	if ( ! WC() ) {
		return false;
	}

	// Ensure session is initialized
	if ( ! WC()->session ) {
		WC()->session = new WC_Session_Handler();
		WC()->session->init();
	}

	// Ensure cart is initialized
	if ( ! WC()->cart ) {
		WC()->cart = new WC_Cart();
	}

	// Ensure customer is initialized
	if ( ! WC()->customer ) {
		WC()->customer = new WC_Customer();
	}

	return true;
}

/**
 * Safely check if cart needs shipping address
 */
function blaze_cart_needs_shipping() {
	if ( ! blaze_ensure_woocommerce_initialized() ) {
		return false;
	}

	$cart = WC()->cart;
	if ( ! $cart || ! is_callable( array( $cart, 'needs_shipping_address' ) ) ) {
		return false;
	}

	return $cart->needs_shipping_address();
}

/**
 * Safely get cart contents count
 */
function blaze_get_cart_count() {
	if ( ! blaze_ensure_woocommerce_initialized() ) {
		return 0;
	}

	$cart = WC()->cart;
	if ( ! $cart || ! is_callable( array( $cart, 'get_cart_contents_count' ) ) ) {
		return 0;
	}

	return $cart->get_cart_contents_count();
}

/**
 * Safely get cart contents
 */
function blaze_get_cart_contents() {
	if ( ! blaze_ensure_woocommerce_initialized() ) {
		return array();
	}

	$cart = WC()->cart;
	if ( ! $cart || ! is_callable( array( $cart, 'get_cart' ) ) ) {
		return array();
	}

	return $cart->get_cart();
}

/**
 * Safely check if cart needs shipping and should show shipping options
 */
function blaze_cart_needs_shipping_display() {
	if ( ! blaze_ensure_woocommerce_initialized() ) {
		return false;
	}

	$cart = WC()->cart;
	if ( ! $cart ) {
		return false;
	}

	return ( is_callable( array( $cart, 'needs_shipping' ) ) && $cart->needs_shipping() ) &&
	       ( is_callable( array( $cart, 'show_shipping' ) ) && $cart->show_shipping() );
}

/**
 * Safely check if cart needs payment
 */
function blaze_cart_needs_payment() {
	if ( ! blaze_ensure_woocommerce_initialized() ) {
		return false;
	}

	$cart = WC()->cart;
	if ( ! $cart || ! is_callable( array( $cart, 'needs_payment' ) ) ) {
		return false;
	}

	return $cart->needs_payment();
}

/**
 * Safely get cart total
 */
function blaze_get_cart_total() {
	if ( ! blaze_ensure_woocommerce_initialized() ) {
		return '';
	}

	$cart = WC()->cart;
	if ( ! $cart || ! is_callable( array( $cart, 'get_total' ) ) ) {
		return '';
	}

	return $cart->get_total();
}

/**
 * Render multi-step Blaze checkout based on original template
 */
function blaze_render_multi_step_blaze_checkout( $checkout, $step_labels, $step_descriptions, $continue_button_texts ) {
	?>
	<div class="blaze-multi-step-wrapper">

		<!-- Step 1: Contact Information -->
		<div class="blaze-step blaze-step-1 active current-step" data-step="1">
			<div class="blaze-step-header">
				<div class="blaze-step-number completed">1</div>
				<h5><?php echo esc_html( $step_labels['step1'] ); ?></h5>
				<div class="blaze-step-edit" style="display: none;">
					<button type="button" class="blaze-edit-step" data-step="1"><?php _e( 'Edit', 'blocksy-child' ); ?></button>
				</div>
			</div>

			<div class="blaze-step-content">
				<?php blaze_render_contact_information_step(); ?>
			</div>

			<div class="blaze-step-summary" style="display: none;">
				<div class="summary-content">
					<div class="summary-email"></div>
					<div class="summary-user-type"></div>
				</div>
			</div>
		</div>

		<!-- Step 2: Recipients Details -->
		<div class="blaze-step blaze-step-2 inactive" data-step="2">
			<div class="blaze-step-header">
				<div class="blaze-step-number">2</div>
				<h5><?php echo esc_html( $step_labels['step2'] ); ?></h5>
				<div class="blaze-step-edit" style="display: none;">
					<button type="button" class="blaze-edit-step" data-step="2"><?php _e( 'Edit', 'blocksy-child' ); ?></button>
				</div>
			</div>

			<div class="blaze-step-content">
				<?php blaze_render_recipients_details_step_v3( $checkout ); ?>
			</div>

			<div class="blaze-step-summary" style="display: none;">
				<div class="summary-content">
					<div class="summary-billing-address"></div>
					<div class="summary-shipping-address"></div>
				</div>
			</div>
		</div>

		<!-- Step 3: Order Payment -->
		<div class="blaze-step blaze-step-3 inactive" data-step="3">
			<div class="blaze-step-header">
				<div class="blaze-step-number">3</div>
				<h5><?php echo esc_html( $step_labels['step3'] ); ?></h5>
				<div class="blaze-step-edit" style="display: none;">
					<button type="button" class="blaze-edit-step" data-step="3"><?php _e( 'Edit', 'blocksy-child' ); ?></button>
				</div>
			</div>

			<div class="blaze-step-content">
				<?php blaze_render_order_payment_step( $checkout ); ?>
			</div>

			<div class="blaze-step-summary" style="display: none;">
				<div class="summary-content">
					<div class="summary-shipping-method"></div>
					<div class="summary-payment-method"></div>
				</div>
			</div>
		</div>

	</div>
	<?php
}

/**
 * Render original Blaze checkout structure (non-multi-step)
 */
function blaze_render_original_checkout_structure( $checkout ) {
	?>
	<div class="checkout-form">
		<div class="accordion-item">
			<div class="accordion-title billing-shipping-accordion">
				<h5><?php _e( 'Recipients Details', 'blaze-online-checkout' ); ?></h5>
			</div>

			<div class="accordion-content billing-shipping-accordion-content">

				<div class="blz-billing-shipping-container">
					<div class="col-1">
						<div>
							<?php do_action( 'woocommerce_checkout_billing' ); ?>
						</div>
					</div>
					<div class="col-2">
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>
				</div>

				<?php if ( ! is_user_logged_in() ) : ?>
				<div class="blaze-register-container">
					<h5 class="blaze-checkout-register"><?php _e( 'Create an account', 'blaze-online-checkout' ); ?></h5>
					<label class="blz-check-register">
						<input type="checkbox" id="save-info-checkbox">
						<?php _e( 'Enter a password to save your information.', 'blaze-online-checkout' ); ?>
						<span class="optional-text" style="font-style: italic;"><?php _e( '(Optional)', 'blaze-online-checkout' ); ?></span>
						<span class="subscription-warning" style="color: red; display: none;"><?php _e( 'You need to be logged in when buying subscription products.', 'blaze-online-checkout' ); ?></span>
					</label>

					<?php blaze_render_registration_form(); ?>
				</div>
				<?php endif; ?>

				<div class="blz-button-container">
					<a class="btn-continue" href="#" target="_self"><?php _e( 'CONTINUE TO ORDER PAYMENT →', 'blaze-online-checkout' ); ?></a>
				</div>
			</div>
			<div class="billing-shipping-content-preview billing-shippiing-content-preview"></div>
		</div>

		<?php do_action( 'blaze_checkout_after_append_accordions' ); ?>
	</div>
	<?php
}

/**
 * Render Step 1: Contact Information (Enhanced with Continue Button)
 */
function blaze_render_contact_information_step() {
	?>
	<div class="information-section">
		<div class="information-content">
			<h4><?php _e( 'Contact information', 'blaze-online-checkout' ); ?></h4>

			<?php if ( ! is_user_logged_in() ) : ?>
				<div class="guest-form">
					<div class="blaze-form-row">
						<input type="email" id="guest_email" name="guest_email" placeholder="<?php esc_attr_e( 'Email address', 'blaze-online-checkout' ); ?>" class="email-input" required />
						<span class="blaze-field-error" style="display: none;"></span>
					</div>
				</div>

				<div class="login-register-tabs">
					<div class="tab-buttons">
						<button type="button" class="tab-button active" data-tab="login">
							<?php _e( 'Login', 'blaze-online-checkout' ); ?>
						</button>
						<button type="button" class="tab-button" data-tab="register">
							<?php _e( 'Register', 'blaze-online-checkout' ); ?>
						</button>
					</div>

					<div class="tab-content" id="login-tab">
						<form class="login-form">
							<div class="blaze-form-row">
								<input type="email" id="signin_email" name="username" placeholder="<?php esc_attr_e( 'Email', 'blaze-online-checkout' ); ?>" required />
								<span class="blaze-field-error" style="display: none;"></span>
							</div>
							<div class="blaze-form-row">
								<input type="password" id="signin_password" name="password" placeholder="<?php esc_attr_e( 'Password', 'blaze-online-checkout' ); ?>" required />
								<span class="blaze-field-error" style="display: none;"></span>
							</div>
							<button type="submit" class="login-button">
								<?php _e( 'Login', 'blaze-online-checkout' ); ?>
							</button>
							<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="forgot-password">
								<?php _e( 'Forgot password?', 'blaze-online-checkout' ); ?>
							</a>
						</form>
					</div>

					<div class="tab-content" id="register-tab" style="display: none;">
						<form class="register-form">
							<div class="blaze-form-row">
								<input type="text" name="first_name" placeholder="<?php esc_attr_e( 'First Name', 'blaze-online-checkout' ); ?>" required />
								<span class="blaze-field-error" style="display: none;"></span>
							</div>
							<div class="blaze-form-row">
								<input type="text" name="last_name" placeholder="<?php esc_attr_e( 'Last Name', 'blaze-online-checkout' ); ?>" required />
								<span class="blaze-field-error" style="display: none;"></span>
							</div>
							<div class="blaze-form-row">
								<input type="email" name="email" placeholder="<?php esc_attr_e( 'Email', 'blaze-online-checkout' ); ?>" required />
								<span class="blaze-field-error" style="display: none;"></span>
							</div>
							<div class="blaze-form-row">
								<input type="password" name="password" placeholder="<?php esc_attr_e( 'Password', 'blaze-online-checkout' ); ?>" required />
								<span class="blaze-field-error" style="display: none;"></span>
							</div>
							<button type="submit" class="register-button">
								<?php _e( 'Register', 'blaze-online-checkout' ); ?>
							</button>
						</form>
					</div>
				</div>
			<?php else : ?>
				<div class="logged-in-user">
					<p><?php printf( __( 'Welcome back, %s!', 'blaze-online-checkout' ), wp_get_current_user()->display_name ); ?></p>
					<a href="<?php echo esc_url( wp_logout_url( wc_get_checkout_url() ) ); ?>" class="logout-link">
						<?php _e( 'Logout', 'blaze-online-checkout' ); ?>
					</a>
				</div>
			<?php endif; ?>

			<!-- Continue Button for Step 1 -->
			<div class="blaze-step-navigation">
				<button type="button" class="blaze-continue-button blaze-continue-step-1" data-next-step="2">
					<?php _e( 'CONTINUE TO RECIPIENTS DETAILS →', 'blaze-online-checkout' ); ?>
				</button>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Fallback function to detect if old version is still being called
 */
function blaze_render_recipients_details_step( $checkout ) {
	error_log( 'BLAZE DEBUG: OLD FUNCTION NAME CALLED - This should not happen!' );
	error_log( 'BLAZE DEBUG: Stack trace: ' . wp_debug_backtrace_summary() );
	// Call the new version
	blaze_render_recipients_details_step_v3( $checkout );
}

function blaze_render_recipients_details_step_v2( $checkout ) {
	error_log( 'BLAZE DEBUG: OLD V2 FUNCTION NAME CALLED - This should not happen!' );
	error_log( 'BLAZE DEBUG: Stack trace: ' . wp_debug_backtrace_summary() );
	// Call the new version
	blaze_render_recipients_details_step_v3( $checkout );
}

/**
 * Render Step 2: Recipients Details (Enhanced with Complete Form Fields)
 */
function blaze_render_recipients_details_step_v3( $checkout ) {
	error_log( 'BLAZE DEBUG: blaze_render_recipients_details_step_v3 called - FIXED VERSION 3.0 - FORCE RELOAD' );
	error_log( 'BLAZE DEBUG: Stack trace: ' . wp_debug_backtrace_summary() );

	// Ensure WooCommerce is properly initialized
	if ( ! blaze_ensure_woocommerce_initialized() ) {
		echo '<div class="blaze-checkout-error">' . __( 'Unable to load checkout. Please refresh the page.', 'blocksy-child' ) . '</div>';
		return;
	}

	?>
	<div class="recipients-details-section">
		<div class="blaze-billing-shipping-container">

			<!-- Billing Address Section -->
			<div class="blaze-billing-section">
				<h4><?php _e( 'Billing Address', 'blaze-online-checkout' ); ?></h4>
				<div class="blaze-billing-fields">
					<?php
					$billing_fields = $checkout->get_checkout_fields( 'billing' );

					// Ensure proper field order and styling
					$field_order = [
						'billing_first_name',
						'billing_last_name',
						'billing_company',
						'billing_address_1',
						'billing_address_2',
						'billing_city',
						'billing_state',
						'billing_postcode',
						'billing_country',
						'billing_phone',
						'billing_email'
					];

					foreach ( $field_order as $field_key ) {
						if ( isset( $billing_fields[ $field_key ] ) ) {
							$field = $billing_fields[ $field_key ];

							// Add error span for validation
							$field['custom_attributes'] = isset( $field['custom_attributes'] ) ? $field['custom_attributes'] : [];

							woocommerce_form_field( $field_key, $field, $checkout->get_value( $field_key ) );
						}
					}
					?>
				</div>

				<!-- Shipping Address Section - Now contained within billing section -->
				<?php if ( blaze_cart_needs_shipping() ) : ?>
				<div class="blaze-shipping-subsection">
					<div class="blaze-shipping-toggle">
						<label class="blaze-checkbox-label" for="ship-to-different-address-checkbox">
							<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="ship_to_different_address" value="1" />
							<span class="checkmark"></span>
							<h4><?php _e( 'Ship to a different address?', 'woocommerce' ); ?></h4>
						</label>
					</div>

					<div class="blaze-shipping-fields" style="display: none;">
						<h4><?php _e( 'Shipping Address', 'blaze-online-checkout' ); ?></h4>
						<?php
						$shipping_fields = $checkout->get_checkout_fields( 'shipping' );

						// Ensure proper field order for shipping
						$shipping_field_order = [
							'shipping_first_name',
							'shipping_last_name',
							'shipping_company',
							'shipping_address_1',
							'shipping_address_2',
							'shipping_city',
							'shipping_state',
							'shipping_postcode',
							'shipping_country'
						];

						foreach ( $shipping_field_order as $field_key ) {
							if ( isset( $shipping_fields[ $field_key ] ) ) {
								$field = $shipping_fields[ $field_key ];
								woocommerce_form_field( $field_key, $field, $checkout->get_value( $field_key ) );
							}
						}
						?>
					</div>
				</div>
				<?php endif; ?>

				<!-- Continue Button for Step 2 - Positioned after all form fields -->
				<div class="blaze-step-navigation">
					<button type="button" class="blaze-continue-button blaze-continue-step-2" data-next-step="3">
						<?php _e( 'CONTINUE TO ORDER PAYMENT →', 'blaze-online-checkout' ); ?>
					</button>
				</div>
			</div>

			<!-- Order Notes -->
			<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_checkout_notes' ) ) ) : ?>
			<div class="blaze-order-notes">
				<h4><?php _e( 'Order Notes', 'blaze-online-checkout' ); ?></h4>
				<?php
				$order_fields = $checkout->get_checkout_fields( 'order' );
				foreach ( $order_fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>
			<?php endif; ?>

			<!-- Account Creation -->
			<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
			<div class="blaze-account-creation">
				<h4><?php _e( 'Create Account', 'blaze-online-checkout' ); ?></h4>
				<label class="blaze-checkbox-label" for="createaccount">
					<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" name="createaccount" value="1" />
					<span class="checkmark"></span>
					<span><?php _e( 'Create an account?', 'woocommerce' ); ?></span>
					<span class="blaze-optional-text"><?php _e( '(Optional)', 'blaze-online-checkout' ); ?></span>
				</label>

				<div class="blaze-create-account-password" style="display: none;">
					<?php
					$account_fields = $checkout->get_checkout_fields( 'account' );
					foreach ( $account_fields as $key => $field ) {
						woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
					}
					?>
				</div>
			</div>
			<?php endif; ?>


		</div>
	</div>
	<?php
}

/**
 * Render Step 3: Order Payment (Enhanced with Better Shipping Methods)
 */
function blaze_render_order_payment_step( $checkout ) {
	?>
	<div class="order-payment-section">

		<!-- Shipping Methods -->
		<?php if ( blaze_cart_needs_shipping_display() ) : ?>
		<div class="blaze-shipping-methods">
			<h4><?php _e( 'Shipping Method', 'blaze-online-checkout' ); ?></h4>
			<div class="shipping-options">
				<?php
				$packages = WC()->shipping()->get_packages();
				foreach ( $packages as $i => $package ) {
					$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
					$product_names = array();

					if ( count( $packages ) > 1 ) {
						foreach ( $package['contents'] as $item_id => $values ) {
							$product_names[ $values['product_id'] ] = $values['data']->get_name();
						}
						$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
					}
					?>
					<div class="shipping-package" data-index="<?php echo $i; ?>">
						<?php if ( count( $packages ) > 1 ) : ?>
							<h5><?php printf( _n( 'Shipping for %s', 'Shipping for %s', count( $product_names ), 'woocommerce' ), implode( ', ', $product_names ) ); ?></h5>
						<?php endif; ?>

						<div class="shipping-methods-list">
							<?php if ( count( $package['rates'] ) > 1 ) : ?>
								<?php foreach ( $package['rates'] as $method ) : ?>
									<label class="shipping-method-option">
										<input type="radio" name="shipping_method[<?php echo $i; ?>]" data-index="<?php echo $i; ?>" id="shipping_method_<?php echo $i; ?>_<?php echo esc_attr( sanitize_title( $method->id ) ); ?>" value="<?php echo esc_attr( $method->id ); ?>" <?php checked( $method->id, $chosen_method ); ?> class="shipping_method" />
										<span class="shipping-method-details">
											<span class="shipping-method-name"><?php echo wp_kses_post( $method->get_label() ); ?></span>
											<span class="shipping-method-cost"><?php echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ) ); ?></span>
										</span>
									</label>
								<?php endforeach; ?>
							<?php elseif ( 1 === count( $package['rates'] ) ) : ?>
								<?php
								$method = reset( $package['rates'] );
								printf( '%s: %s', esc_html( $method->get_label() ), wp_kses_post( wc_cart_totals_shipping_method_label( $method ) ) );
								?>
								<input type="hidden" name="shipping_method[<?php echo $i; ?>]" data-index="<?php echo $i; ?>" id="shipping_method_<?php echo $i; ?>_<?php echo esc_attr( sanitize_title( $method->id ) ); ?>" value="<?php echo esc_attr( $method->id ); ?>" class="shipping_method" />
							<?php else : ?>
								<p><?php _e( 'No shipping options were found.', 'woocommerce' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php endif; ?>

		<!-- Payment Methods -->
		<div class="blaze-payment-methods">
			<h4><?php _e( 'Payment Method', 'blaze-online-checkout' ); ?></h4>

			<?php if ( blaze_cart_needs_payment() ) : ?>
				<div class="payment-methods-list">
					<?php
					$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
					WC()->payment_gateways()->set_current_gateway( $available_gateways );

					if ( ! empty( $available_gateways ) ) {
						foreach ( $available_gateways as $gateway ) {
							?>
							<div class="payment-method-option">
								<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />
								<label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
									<span class="payment-method-name"><?php echo $gateway->get_title(); ?></span>
									<?php echo $gateway->get_icon(); ?>
								</label>
								<?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
									<div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>" <?php if ( ! $gateway->chosen ) : ?>style="display:none;"<?php endif; ?>>
										<?php $gateway->payment_fields(); ?>
									</div>
								<?php endif; ?>
							</div>
							<?php
						}
					} else {
						echo '<div class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' ) ) . '</div>';
					}
					?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Terms and Conditions -->
		<div class="blaze-terms-conditions">
			<?php do_action( 'woocommerce_checkout_terms_and_conditions' ); ?>
			<?php wc_get_template( 'checkout/terms.php' ); ?>
		</div>

		<!-- Place Order Button -->
		<div class="blaze-place-order">
			<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="blaze-place-order-button" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr__( 'Place order', 'woocommerce' ) . '" data-value="' . esc_attr__( 'Place order', 'woocommerce' ) . '">' . esc_html__( 'Place order', 'woocommerce' ) . '</button>' ); ?>
			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render registration form (Original Blaze Structure)
 */
function blaze_render_registration_form() {
	?>
	<div class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

		<?php do_action( 'woocommerce_register_form_start' ); ?>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide blz-custom-reg-username">
			<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" placeholder="Username" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" />
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide blz-custom-reg-email">
			<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="email" placeholder="Email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" />
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="password" placeholder="Password" class="blaze-checkout-form-register-password-field woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
		</p>

		<?php do_action( 'woocommerce_register_form' ); ?>

		<p class="woocommerce-form-row form-row">
			<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
			<button type="submit" class="blaze-checkout-form-register-button woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
				<?php esc_html_e( 'REGISTER', 'woocommerce' ); ?>
			</button>
		</p>

		<?php do_action( 'woocommerce_register_form_end' ); ?>

	</div>
	<?php
}

/**
 * Legacy function - kept for compatibility
 */
function blaze_checkout_render_multi_step_checkout( $checkout, $step_labels, $step_descriptions, $continue_button_texts, $guest_checkout_heading, $guest_checkout_description, $sign_in_heading, $sign_in_toggle_text, $billing_address_heading, $shipping_address_heading, $create_account_heading, $create_account_text, $optional_text, $place_order_button_text ) {
	// Use the new Blaze-based multi-step checkout
	blaze_render_multi_step_blaze_checkout( $checkout, $step_labels, $step_descriptions, $continue_button_texts );
}

/**
 * Render Step 1: Account
 */
function blaze_checkout_render_account_step( $checkout, $guest_checkout_heading, $guest_checkout_description, $sign_in_heading, $sign_in_toggle_text, $continue_button_text ) {
	?>
	<div class="blaze-account-step">

		<!-- Guest Checkout Section -->
		<div class="blaze-guest-checkout-section">
			<h2><?php echo esc_html( $guest_checkout_heading ); ?></h2>
			<p><?php echo esc_html( $guest_checkout_description ); ?></p>

			<div class="blaze-guest-form">
				<div class="blaze-form-row">
					<label for="guest_email">
						<?php _e( 'Email', 'blocksy-child' ); ?>
						<span class="required">*</span>
					</label>
					<input type="email" id="guest_email" name="guest_email" placeholder="<?php esc_attr_e( 'Email address', 'blocksy-child' ); ?>" class="blaze-email-input" required />
					<div class="blaze-field-error"></div>
					<small><?php _e( 'Order number and receipt will be sent to this email address.', 'blocksy-child' ); ?></small>
				</div>

				<div class="blaze-newsletter-subscription">
					<label>
						<input type="checkbox" name="mailchimp_woocommerce_newsletter" id="mailchimp_woocommerce_newsletter" value="1" />
						<?php _e( 'Subscribe to our newsletter', 'blocksy-child' ); ?>
					</label>
				</div>

				<button type="button" class="blaze-continue-button blaze-continue-step-1">
					<?php echo esc_html( $continue_button_text ); ?>
				</button>
			</div>
		</div>

		<!-- Sign In Section -->
		<div class="blaze-signin-section">
			<div class="blaze-signin-toggle">
				<span><?php echo esc_html( $sign_in_heading ); ?></span>
				<button type="button" class="blaze-signin-toggle-btn">
					<?php echo esc_html( $sign_in_toggle_text ); ?>
					<span class="blaze-toggle-icon"></span>
				</button>
			</div>

			<div class="blaze-signin-form" style="display: none;">
				<ul class="blaze-signin-benefits">
					<li><?php _e( 'Sign In to Faster Checkout', 'blocksy-child' ); ?></li>
				</ul>

				<form class="blaze-login-form">
					<div class="blaze-form-row">
						<label for="signin_email">
							<?php _e( 'Email address', 'blocksy-child' ); ?>
							<span class="required">*</span>
						</label>
						<input type="email" id="signin_email" name="username" required />
					</div>

					<div class="blaze-form-row">
						<label for="signin_password">
							<?php _e( 'Password', 'blocksy-child' ); ?>
							<span class="required">*</span>
						</label>
						<input type="password" id="signin_password" name="password" required />
					</div>

					<div class="blaze-form-row blaze-form-row-inline">
						<label>
							<input type="checkbox" name="rememberme" id="rememberme" value="forever" />
							<?php _e( 'Remember me', 'blocksy-child' ); ?>
						</label>
						<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="blaze-forgot-password">
							<?php _e( 'Forgot password?', 'blocksy-child' ); ?>
						</a>
					</div>

					<button type="submit" class="blaze-signin-button">
						<?php _e( 'Sign in', 'blocksy-child' ); ?>
					</button>

					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				</form>
			</div>
		</div>

	</div>
	<?php
}

/**
 * Render Step 2: Recipients Details
 */
function blaze_checkout_render_recipients_step( $checkout, $billing_address_heading, $shipping_address_heading, $create_account_heading, $create_account_text, $optional_text, $continue_button_text ) {
	?>
	<div class="blaze-recipients-step">

		<div class="blaze-billing-shipping-container">
			<!-- Billing Address -->
			<div class="blaze-billing-section">
				<h5><?php echo esc_html( $billing_address_heading ); ?></h5>
				<div class="blaze-billing-fields">
					<?php
					$fields = $checkout->get_checkout_fields( 'billing' );
					foreach ( $fields as $key => $field ) {
						woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
					}
					?>
				</div>
			</div>

			<!-- Shipping Address -->
			<?php if ( blaze_cart_needs_shipping() ) : ?>
			<div class="blaze-shipping-section">
				<div class="blaze-shipping-toggle">
					<h3>
						<label for="ship-to-different-address-checkbox">
							<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="ship_to_different_address" value="1" />
							<?php echo esc_html( $shipping_address_heading ); ?>
						</label>
					</h3>
					<span class="required">*</span>
				</div>

				<div class="blaze-shipping-fields" style="display: none;">
					<?php
					$fields = $checkout->get_checkout_fields( 'shipping' );
					foreach ( $fields as $key => $field ) {
						woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
					}
					?>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<!-- Order Notes -->
		<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_checkout_notes' ) ) ) : ?>
		<div class="blaze-order-notes">
			<?php
			$fields = $checkout->get_checkout_fields( 'order' );
			foreach ( $fields as $key => $field ) {
				woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
			}
			?>
		</div>
		<?php endif; ?>

		<!-- Account Creation -->
		<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
		<div class="blaze-account-creation">
			<h5><?php echo esc_html( $create_account_heading ); ?></h5>
			<label class="blaze-create-account-checkbox">
				<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" name="createaccount" value="1" />
				<?php echo esc_html( $create_account_text ); ?>
				<span class="blaze-optional-text"><?php echo esc_html( $optional_text ); ?></span>
			</label>

			<div class="blaze-create-account-password" style="display: none;">
				<?php
				$fields = $checkout->get_checkout_fields( 'account' );
				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>
		</div>
		<?php endif; ?>

		<button type="button" class="blaze-continue-button blaze-continue-step-2">
			<?php echo esc_html( $continue_button_text ); ?>
		</button>

	</div>
	<?php
}

/**
 * Render the information section (legacy)
 */
function blaze_checkout_render_information_section() {
	?>
	<div class="blaze-information-content">
		<h4><?php _e( 'Contact information', 'blocksy-child' ); ?></h4>
		
		<?php if ( ! is_user_logged_in() ) : ?>
			<div class="blaze-guest-form">
				<input type="email" id="guest_email" name="guest_email" placeholder="<?php esc_attr_e( 'Email address', 'blocksy-child' ); ?>" class="blaze-email-input" />
				<button type="button" class="blaze-btn-guest btn-checkout-as-guest">
					<?php _e( 'Continue as guest', 'blocksy-child' ); ?>
				</button>
			</div>
			
			<div class="blaze-login-register-tabs">
				<div class="blaze-tab-buttons">
					<button type="button" class="blaze-tab-button active" data-tab="login">
						<?php _e( 'Login', 'blocksy-child' ); ?>
					</button>
					<button type="button" class="blaze-tab-button" data-tab="register">
						<?php _e( 'Register', 'blocksy-child' ); ?>
					</button>
				</div>
				
				<div class="blaze-tab-content" id="login-tab">
					<form class="blaze-login-form">
						<input type="email" name="username" placeholder="<?php esc_attr_e( 'Email', 'blocksy-child' ); ?>" required />
						<input type="password" name="password" placeholder="<?php esc_attr_e( 'Password', 'blocksy-child' ); ?>" required />
						<button type="submit" class="blaze-login-button">
							<?php _e( 'Login', 'blocksy-child' ); ?>
						</button>
						<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="blaze-forgot-password">
							<?php _e( 'Forgot password?', 'blocksy-child' ); ?>
						</a>
					</form>
				</div>
				
				<div class="blaze-tab-content" id="register-tab" style="display: none;">
					<form class="blaze-register-form">
						<input type="text" name="first_name" placeholder="<?php esc_attr_e( 'First Name', 'blocksy-child' ); ?>" required />
						<input type="text" name="last_name" placeholder="<?php esc_attr_e( 'Last Name', 'blocksy-child' ); ?>" required />
						<input type="email" name="email" placeholder="<?php esc_attr_e( 'Email', 'blocksy-child' ); ?>" required />
						<input type="password" name="password" placeholder="<?php esc_attr_e( 'Password', 'blocksy-child' ); ?>" required />
						<button type="submit" class="blaze-register-button">
							<?php _e( 'Register', 'blocksy-child' ); ?>
						</button>
					</form>
				</div>
			</div>
		<?php else : ?>
			<div class="blaze-logged-in-user">
				<p><?php printf( __( 'Welcome back, %s!', 'blocksy-child' ), wp_get_current_user()->display_name ); ?></p>
				<a href="<?php echo esc_url( wp_logout_url( wc_get_checkout_url() ) ); ?>" class="blaze-logout-link">
					<?php _e( 'Logout', 'blocksy-child' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render the registration form
 */
function blaze_checkout_render_registration_form() {
	?>
	<div class="blaze-register-form-container" style="display: none;">
		<div class="woocommerce-form woocommerce-form-register register">
			<?php do_action( 'woocommerce_register_form_start' ); ?>
			
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide blaze-custom-reg-username">
				<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" placeholder="<?php esc_attr_e( 'Username', 'blocksy-child' ); ?>" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" />
			</p>
			
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide blaze-custom-reg-email">
				<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="email" placeholder="<?php esc_attr_e( 'Email', 'blocksy-child' ); ?>" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" />
			</p>
			
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="password" placeholder="<?php esc_attr_e( 'Password', 'blocksy-child' ); ?>" class="blaze-checkout-form-register-password-field woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
			</p>
			
			<?php do_action( 'woocommerce_register_form' ); ?>
			
			<p class="woocommerce-form-row form-row">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="blaze-checkout-form-register-button woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
					<?php esc_html_e( 'REGISTER', 'woocommerce' ); ?>
				</button>
			</p>
			
			<?php do_action( 'woocommerce_register_form_end' ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render Step 3: Payment
 */
function blaze_checkout_render_payment_step( $checkout, $place_order_button_text ) {
	?>
	<div class="blaze-payment-step">

		<!-- Shipping Methods -->
		<?php if ( blaze_cart_needs_shipping_display() ) : ?>
		<div class="blaze-shipping-methods">
			<table class="blaze-shipping-table">
				<thead>
					<tr>
						<th><?php _e( 'Choose Your Shipping', 'blocksy-child' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php blaze_checkout_cart_totals_shipping_html(); ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>

		<!-- Payment Methods -->
		<div class="blaze-payment-methods">
			<h3><?php _e( 'Payment', 'blocksy-child' ); ?></h3>

			<?php if ( blaze_cart_needs_payment() ) : ?>
				<ul class="wc_payment_methods payment_methods methods">
					<?php
					$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
					WC()->payment_gateways()->set_current_gateway( $available_gateways );

					if ( ! empty( $available_gateways ) ) {
						foreach ( $available_gateways as $gateway ) {
							wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
						}
					} else {
						echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' ) ) . '</li>';
					}
					?>
				</ul>
			<?php endif; ?>
		</div>

		<!-- Terms and Conditions -->
		<div class="blaze-terms-conditions">
			<?php do_action( 'woocommerce_checkout_terms_and_conditions' ); ?>

			<div class="blaze-privacy-policy">
				<p>
					<?php
					printf(
						/* translators: %s privacy policy page name and link */
						esc_html__( 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.', 'woocommerce' ),
						'<a href="' . esc_url( wc_privacy_policy_page_url() ) . '" class="woocommerce-privacy-policy-link" target="_blank">' . esc_html__( 'privacy policy', 'woocommerce' ) . '</a>'
					);
					?>
				</p>
			</div>

			<?php wc_get_template( 'checkout/terms.php' ); ?>
		</div>

		<!-- Place Order Button -->
		<div class="blaze-place-order">
			<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $place_order_button_text ) . '" data-value="' . esc_attr( $place_order_button_text ) . '">' . esc_html( $place_order_button_text ) . '</button>' ); ?>
			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>
		</div>

	</div>
	<?php
}

/**
 * Render traditional single-page checkout
 */
function blaze_checkout_render_traditional_checkout( $checkout, $create_account_heading, $create_account_text, $optional_text ) {
	?>
	<div class="blaze-traditional-checkout">

		<!-- Customer Information -->
		<div class="blaze-customer-details">
			<div class="blaze-billing-fields">
				<h3><?php _e( 'Billing details', 'woocommerce' ); ?></h3>
				<?php
				$fields = $checkout->get_checkout_fields( 'billing' );
				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>

			<?php if ( blaze_cart_needs_shipping() ) : ?>
			<div class="blaze-shipping-fields">
				<h3 id="ship-to-different-address">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
						<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="ship_to_different_address" value="1" />
						<span><?php _e( 'Ship to a different address?', 'woocommerce' ); ?></span>
					</label>
				</h3>

				<div class="shipping_address">
					<?php
					$fields = $checkout->get_checkout_fields( 'shipping' );
					foreach ( $fields as $key => $field ) {
						woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
					}
					?>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_checkout_notes' ) ) ) : ?>
			<div class="blaze-additional-fields">
				<?php
				$fields = $checkout->get_checkout_fields( 'order' );
				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>
			<?php endif; ?>
		</div>

		<!-- Account Creation -->
		<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
		<div class="blaze-create-account">
			<p class="create-account">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" type="checkbox" name="createaccount" value="1" />
					<span><?php echo esc_html( $create_account_text ); ?></span>
				</label>
			</p>

			<div class="create-account">
				<?php
				$fields = $checkout->get_checkout_fields( 'account' );
				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>
		</div>
		<?php endif; ?>

	</div>

	<div class="blaze-order-review">
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>
	<?php
}

/**
 * Render the payment section (legacy)
 */
function blaze_checkout_render_payment_section() {
	?>
	<h5><?php _e( 'Payment', 'blocksy-child' ); ?></h5>
	
	<?php if ( blaze_cart_needs_payment() ) : ?>
		<div class="blaze-payment-methods">
			<?php
			$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
			WC()->payment_gateways()->set_current_gateway( $available_gateways );
			
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<p>' . __( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) . '</p>';
			}
			?>
		</div>
	<?php endif; ?>
	
	<div class="blaze-place-order-container">
		<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>
	<?php
}

/**
 * Render the order summary
 */
function blaze_checkout_render_order_summary( $order_summary_heading, $edit_button_text ) {
	global $woocommerce;
	$cart_count = $woocommerce->cart->cart_contents_count;
	?>
	
	<div class="blaze-cart-count-container">
		<div class="blaze-cart-count-edit">
			<h2 class="blaze-order-heading"><?php echo esc_html( $order_summary_heading ); ?></h2>
			<span class="blaze-cart-count">
				<?php echo sprintf( _n( '%d item', '%d items', $cart_count, 'blocksy-child' ), $cart_count ); ?>
			</span>
		</div>
		<a class="blaze-edit-checkout-items" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
			<?php echo esc_html( $edit_button_text ); ?>
		</a>
	</div>
	
	<!-- Order Items -->
	<div class="blaze-order-items">
		<?php
		$cart_contents = blaze_get_cart_contents();
		if ( ! empty( $cart_contents ) ) {
			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			
			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				?>
				<div class="blaze-order-item">
					<div class="blaze-item-image">
						<?php
						$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
						if ( ! $thumbnail ) {
							$thumbnail = '<div class="blaze-placeholder-image"></div>';
						}
						echo $thumbnail;
						?>
					</div>
					<div class="blaze-item-details">
						<h6><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?></h6>
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
						<span class="blaze-item-quantity"><?php echo $cart_item['quantity']; ?></span>
					</div>
					<div class="blaze-item-price">
						<?php
						if ( blaze_ensure_woocommerce_initialized() && WC()->cart ) {
							echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
						} else {
							echo wc_price( $_product->get_price() * $cart_item['quantity'] );
						}
						?>
					</div>
				</div>
				<?php
			}
		}
	}
	?>
</div>
	
	<!-- Coupon Section -->
	<div class="blaze-coupon-section">
		<button type="button" class="blaze-coupon-toggle">
			<?php _e( 'Add discount code', 'blocksy-child' ); ?>
		</button>
		<div class="blaze-coupon-form blaze-form-toggle" style="display: none;">
			<input type="text" id="coupon-code-input" name="coupon_code" placeholder="<?php esc_attr_e( 'Discount code', 'blocksy-child' ); ?>" />
			<button type="button" class="blaze-coupon-apply coupon-code-apply-button">
				<?php _e( 'Apply', 'blocksy-child' ); ?>
			</button>
		</div>
		
		<!-- Hidden WooCommerce coupon form -->
		<div style="display: none;">
			<?php wc_get_template( 'checkout/form-coupon.php', array( 'checkout' => WC()->checkout() ) ); ?>
		</div>
	</div>
	
	<!-- Shipping Methods -->
	<?php if ( blaze_cart_needs_shipping_display() ) : ?>
		<div class="blaze-shipping-methods">
			<h6><?php _e( 'Shipping Method', 'blocksy-child' ); ?></h6>
			<table class="blaze-select-shipping-method">
				<tbody>
					<?php blaze_checkout_cart_totals_shipping_html(); ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
	
	<!-- Order Totals -->
	<div class="blaze-order-totals">
		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>
		
		<div class="blaze-total-row">
			<span><?php _e( 'Subtotal', 'blocksy-child' ); ?></span>
			<span><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php if ( blaze_ensure_woocommerce_initialized() && WC()->cart ) {
			foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
				<div class="blaze-total-row blaze-coupon-row">
					<span><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
					<span><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
				</div>
			<?php endforeach;
		} ?>

		<?php if ( blaze_cart_needs_shipping_display() ) : ?>
			<div class="blaze-total-row">
				<span><?php _e( 'Shipping', 'blocksy-child' ); ?></span>
				<span><?php wc_cart_totals_shipping_html(); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( blaze_ensure_woocommerce_initialized() && WC()->cart ) {
			foreach ( WC()->cart->get_fees() as $fee ) : ?>
				<div class="blaze-total-row">
					<span><?php echo esc_html( $fee->name ); ?></span>
					<span><?php wc_cart_totals_fee_html( $fee ); ?></span>
				</div>
			<?php endforeach;
		} ?>

		<?php if ( wc_tax_enabled() && blaze_ensure_woocommerce_initialized() && WC()->cart && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php if ( blaze_ensure_woocommerce_initialized() && WC()->cart ) {
					foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
						<div class="blaze-total-row">
							<span><?php echo esc_html( $tax->label ); ?></span>
							<span><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
						</div>
					<?php endforeach;
				} ?>
			<?php else : ?>
				<div class="blaze-total-row">
					<span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
					<span><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>
		
		<div class="blaze-total-row blaze-total-final">
			<span><?php _e( 'Total', 'blocksy-child' ); ?></span>
			<span><?php wc_cart_totals_order_total_html(); ?></span>
		</div>
		
		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	</div>
	<?php
}

/**
 * Render clean order summary without payment elements (for ORDER SUMMARY section)
 */
function blaze_render_clean_order_summary( $order_summary_heading, $edit_button_text ) {
	// Ensure WooCommerce is properly initialized
	if ( ! blaze_ensure_woocommerce_initialized() ) {
		echo '<div class="blaze-checkout-error">' . __( 'Unable to load order summary. Please refresh the page.', 'blocksy-child' ) . '</div>';
		return;
	}

	$cart_count = blaze_get_cart_count();
	$cart_total = blaze_get_cart_total();
	?>

	<!-- Mobile/Tablet Accordion Header (hidden on desktop) -->
	<div class="blaze-order-summary-accordion-header"
		 style="display: none;"
		 role="button"
		 tabindex="0"
		 aria-expanded="false"
		 aria-controls="blaze-order-summary-content"
		 aria-label="<?php echo esc_attr( sprintf( __( 'Toggle order summary - %s total', 'blocksy-child' ), wp_strip_all_tags( $cart_total ) ) ); ?>">
		<div class="blaze-accordion-summary-info">
			<h5 class="blaze-accordion-title"><?php echo esc_html( $order_summary_heading ); ?></h5>
			<p class="blaze-accordion-subtitle"><?php echo sprintf( _n( '%d item', '%d items', $cart_count, 'blocksy-child' ), $cart_count ); ?></p>
		</div>
		<div class="blaze-accordion-right">
			<span class="blaze-accordion-total"><?php echo wp_kses_post( $cart_total ); ?></span>
			<div class="blaze-accordion-toggle" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</div>
		</div>
	</div>

	<!-- Accordion Content Wrapper -->
	<div class="blaze-order-summary-accordion-content" id="blaze-order-summary-content" aria-hidden="true">
		<div class="blaze-cart-count-container">
		<div class="blaze-cart-count-edit">
			<h2 class="blaze-order-heading"><?php echo esc_html( $order_summary_heading ); ?></h2>
			<span class="blaze-cart-count">
				<?php echo sprintf( _n( '%d item', '%d items', $cart_count, 'blocksy-child' ), $cart_count ); ?>
			</span>
		</div>
		<a class="blaze-edit-checkout-items" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
			<?php echo esc_html( $edit_button_text ); ?>
		</a>
	</div>

	<!-- Order Items -->
	<div class="blaze-order-items">
		<?php
		$cart_contents = blaze_get_cart_contents();
		if ( ! empty( $cart_contents ) ) {
			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				?>
				<div class="blaze-order-item">
					<div class="blaze-item-image">
						<?php
						$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
						if ( ! $thumbnail ) {
							$thumbnail = '<div class="blaze-placeholder-image"></div>';
						}
						echo $thumbnail;
						?>
					</div>
					<div class="blaze-item-details">
						<h6><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?></h6>
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
						<span class="blaze-item-quantity"><?php echo $cart_item['quantity']; ?></span>
					</div>
					<div class="blaze-item-price">
						<?php
						if ( blaze_ensure_woocommerce_initialized() && WC()->cart ) {
							echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
						} else {
							echo wc_price( $_product->get_price() * $cart_item['quantity'] );
						}
						?>
					</div>
				</div>
				<?php
			}
		}
	}
	?>
</div>

	<!-- Coupon Section -->
	<div class="blaze-coupon-section">
		<button type="button" class="blaze-coupon-toggle">
			<?php _e( 'Add discount code', 'blocksy-child' ); ?>
		</button>
		<div class="blaze-coupon-form blaze-form-toggle" style="display: none;">
			<input type="text" id="coupon-code-input" name="coupon_code" placeholder="<?php esc_attr_e( 'Discount code', 'blocksy-child' ); ?>" />
			<button type="button" class="blaze-coupon-apply coupon-code-apply-button">
				<?php _e( 'Apply', 'blocksy-child' ); ?>
			</button>
		</div>

		<!-- Hidden WooCommerce coupon form -->
		<div style="display: none;">
			<?php wc_get_template( 'checkout/form-coupon.php', array( 'checkout' => WC()->checkout() ) ); ?>
		</div>
	</div>

	<!-- Order Totals -->
	<div class="blaze-order-totals">
		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<div class="blaze-total-row">
			<span><?php _e( 'Subtotal', 'blocksy-child' ); ?></span>
			<span><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php if ( blaze_ensure_woocommerce_initialized() && WC()->cart ) {
			foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
				<div class="blaze-total-row blaze-coupon-row">
					<span><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
					<span><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
				</div>
			<?php endforeach;
		} ?>

		<?php if ( blaze_cart_needs_shipping_display() ) : ?>
			<div class="blaze-total-row">
				<span><?php _e( 'Shipping', 'blocksy-child' ); ?></span>
				<span><?php wc_cart_totals_shipping_html(); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( blaze_ensure_woocommerce_initialized() && WC()->cart ) {
			foreach ( WC()->cart->get_fees() as $fee ) : ?>
				<div class="blaze-total-row">
					<span><?php echo esc_html( $fee->name ); ?></span>
					<span><?php wc_cart_totals_fee_html( $fee ); ?></span>
				</div>
			<?php endforeach;
		} ?>

		<?php if ( wc_tax_enabled() && blaze_ensure_woocommerce_initialized() && WC()->cart && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php if ( blaze_ensure_woocommerce_initialized() && WC()->cart ) {
					foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
						<div class="blaze-total-row">
							<span><?php echo esc_html( $tax->label ); ?></span>
							<span><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
						</div>
					<?php endforeach;
				} ?>
			<?php else : ?>
				<div class="blaze-total-row">
					<span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
					<span><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<div class="blaze-total-row blaze-total-final">
			<span><?php _e( 'Total', 'blocksy-child' ); ?></span>
			<span><?php wc_cart_totals_order_total_html(); ?></span>
		</div>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	</div>
	</div> <!-- Close accordion content wrapper -->
	<?php
}

/**
 * Get shipping methods for checkout
 */
function blaze_checkout_cart_totals_shipping_html() {
	$packages = WC()->shipping()->get_packages();
	$first    = true;

	foreach ( $packages as $i => $package ) {
		$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
		$product_names = array();

		if ( count( $packages ) > 1 ) {
			foreach ( $package['contents'] as $item_id => $values ) {
				$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
			}
			$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
		}

		wc_get_template(
			'cart/cart-shipping.php',
			array(
				'package'                  => $package,
				'available_methods'        => $package['rates'],
				'show_package_details'     => count( $packages ) > 1,
				'show_shipping_calculator' => is_cart() && apply_filters( 'woocommerce_shipping_show_shipping_calculator', $first, $i, $package ),
				'package_details'          => implode( ', ', $product_names ),
				'package_name'             => apply_filters( 'woocommerce_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Shipping %d', 'shipping packages', 'woocommerce' ), ( $i + 1 ) ) : _x( 'Choose Your Shipping', 'shipping packages', 'woocommerce' ), $i, $package ),
				'index'                    => $i,
				'chosen_method'            => $chosen_method,
				'formatted_destination'    => WC()->countries->get_formatted_address( $package['destination'], ', ' ),
				'has_calculated_shipping'  => WC()->customer->has_calculated_shipping(),
			)
		);

		$first = false;
	}
}
