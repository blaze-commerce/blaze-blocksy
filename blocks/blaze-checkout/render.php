<?php
/**
 * Blaze Commerce Multi-Step Checkout Block Render Template
 *
 * @package BlazeCommerce
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Debug: Check if render.php is being called
error_log( 'NEW RENDER.PHP: Starting execution' );

// Check if we're in the editor context (WordPress admin or REST API for editor)
$is_editor_context = (
	is_admin() ||
	( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
	( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '/wp-json/' ) !== false )
);

// If we're in the editor context, show a preview instead of the full checkout
if ( $is_editor_context ) {
	?>
	<div class="blaze-checkout-editor-preview" style="border: 2px dashed #007cba; padding: 30px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
		<div style="text-align: center; margin-bottom: 25px;">
			<h3 style="margin: 0 0 10px 0; color: #007cba; font-size: 24px; font-weight: 600;">🛒 Blaze Commerce Multi-Step Checkout</h3>
			<p style="margin: 0; color: #6c757d; font-size: 14px;">Interactive checkout form with progressive steps and order summary</p>
		</div>

		<!-- Step Progress Indicator -->
		<div style="display: flex; justify-content: center; gap: 15px; margin: 25px 0; flex-wrap: wrap;">
			<div style="display: flex; align-items: center; gap: 8px;">
				<span style="background: #007cba; color: white; padding: 8px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; min-width: 120px;">1. Contact Info</span>
				<span style="color: #007cba; font-size: 18px;">→</span>
			</div>
			<div style="display: flex; align-items: center; gap: 8px;">
				<span style="background: #6c757d; color: white; padding: 8px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; min-width: 120px;">2. Billing/Shipping</span>
				<span style="color: #6c757d; font-size: 18px;">→</span>
			</div>
			<div>
				<span style="background: #6c757d; color: white; padding: 8px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; min-width: 120px;">3. Payment</span>
			</div>
		</div>

		<!-- Preview Layout -->
		<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin: 25px 0; text-align: left;">
			<!-- Checkout Steps Preview -->
			<div style="background: white; padding: 20px; border-radius: 6px; border: 1px solid #dee2e6;">
				<h4 style="margin: 0 0 15px 0; color: #495057; font-size: 16px;">Checkout Form</h4>
				<div style="space-y: 10px;">
					<div style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 8px;">
						<strong style="color: #007cba;">Contact Information</strong>
						<div style="font-size: 12px; color: #6c757d; margin-top: 4px;">Email, phone, account options</div>
					</div>
					<div style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 8px; opacity: 0.6;">
						<strong style="color: #6c757d;">Billing & Shipping Details</strong>
						<div style="font-size: 12px; color: #6c757d; margin-top: 4px;">Address forms, shipping options</div>
					</div>
					<div style="background: #f8f9fa; padding: 10px; border-radius: 4px; opacity: 0.6;">
						<strong style="color: #6c757d;">Payment & Review</strong>
						<div style="font-size: 12px; color: #6c757d; margin-top: 4px;">Payment methods, order review</div>
					</div>
				</div>
			</div>

			<!-- Order Summary Preview -->
			<div style="background: white; padding: 20px; border-radius: 6px; border: 1px solid #dee2e6;">
				<h4 style="margin: 0 0 15px 0; color: #495057; font-size: 16px;">Order Summary</h4>
				<div style="font-size: 12px; color: #6c757d; line-height: 1.5;">
					<div style="margin-bottom: 8px;">• Cart items with quantities</div>
					<div style="margin-bottom: 8px;">• Subtotal & taxes</div>
					<div style="margin-bottom: 8px;">• Shipping methods</div>
					<div style="margin-bottom: 8px;">• Coupon codes</div>
					<div style="font-weight: 600; color: #495057;">• Final total</div>
				</div>
			</div>
		</div>

		<!-- Features List -->
		<div style="background: rgba(0, 124, 186, 0.1); padding: 15px; border-radius: 6px; margin: 20px 0;">
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; font-size: 12px; color: #495057;">
				<div>✅ Multi-step progression</div>
				<div>✅ Mobile responsive</div>
				<div>✅ WooCommerce integration</div>
				<div>✅ Order summary sidebar</div>
				<div>✅ Form validation</div>
				<div>✅ Guest & account checkout</div>
			</div>
		</div>

		<div style="text-align: center; margin-top: 20px;">
			<p style="margin: 0; color: #6c757d; font-size: 13px; font-style: italic;">
				Preview only - full functionality available on frontend checkout page
			</p>
		</div>
	</div>
	<?php
	return;
}

// Include helper functions
require_once __DIR__ . '/includes/helper-functions.php';

// Ensure WooCommerce is available and properly initialized
if ( ! class_exists( 'WooCommerce' ) ) {
	echo '<div class="blaze-checkout-error">WooCommerce is required for the checkout functionality.</div>';
	return;
}

// Ensure WooCommerce is properly initialized
if ( ! blaze_ensure_woocommerce_initialized() ) {
	echo '<div class="blaze-checkout-error">Unable to initialize WooCommerce. Please refresh the page.</div>';
	return;
}

// Get block attributes with defaults
$attributes = wp_parse_args( $attributes ?? [], [
	'mainHeading' => __( 'Checkout', 'blocksy-child' ),
	'recipientDetailsHeading' => __( 'Recipients Details', 'blocksy-child' ),
	'orderSummaryHeading' => __( 'Order Summary', 'blocksy-child' ),
	'editButtonText' => __( 'Edit', 'blocksy-child' ),
	'createAccountHeading' => __( 'Create an account', 'blocksy-child' ),
	'createAccountText' => __( 'Enter a password to save your information.', 'blocksy-child' ),
	'optionalText' => __( '(Optional)', 'blocksy-child' ),
	'subscriptionWarning' => __( 'You need to be logged in when buying subscription products.', 'blocksy-child' ),
	'checkoutType' => 'multi-step', // 'multi-step' or 'traditional'
	'accordionSettings' => [
		'desktop' => 'open',
		'tablet' => 'closed',
		'mobile' => 'closed'
	]
]);

// Enqueue frontend assets
wp_enqueue_script(
	'blaze-checkout-frontend',
	get_stylesheet_directory_uri() . '/blocks/blaze-checkout/assets/js/frontend.js',
	array( 'jquery', 'wc-checkout' ),
	'2.0.3',
	true
);

wp_enqueue_style(
	'blaze-checkout-frontend',
	get_stylesheet_directory_uri() . '/blocks/blaze-checkout/assets/css/blaze-online-checkout.css',
	array(),
	'2.0.3'
);

// Localize script with checkout settings
wp_localize_script( 'blaze-checkout-frontend', 'blazeCheckoutSettings', array(
	'multiStepEnabled' => $attributes['checkoutType'] === 'multi-step',
	'ajaxUrl' => admin_url( 'admin-ajax.php' ),
	'nonce' => wp_create_nonce( 'blaze_checkout_nonce' ),
	'stepLabels' => array(
		'step1' => $attributes['stepLabels']['step1'] ?? __( 'Contact Information', 'blocksy-child' ),
		'step2' => $attributes['stepLabels']['step2'] ?? __( 'Recipients Details', 'blocksy-child' ),
		'step3' => $attributes['stepLabels']['step3'] ?? __( 'Order Payment', 'blocksy-child' )
	)
));

// Get WooCommerce checkout object
$checkout = null;
if ( WC() && is_callable( array( WC(), 'checkout' ) ) ) {
	$checkout = WC()->checkout();
}

if ( ! $checkout ) {
	echo '<div class="blaze-checkout-error">Unable to initialize checkout. Please try again.</div>';
	return;
}

// Step labels for multi-step checkout
$step_labels = [
	'step1' => $attributes['stepLabels']['step1'] ?? __( 'Contact Information', 'blocksy-child' ),
	'step2' => $attributes['stepLabels']['step2'] ?? $attributes['recipientDetailsHeading'],
	'step3' => $attributes['stepLabels']['step3'] ?? __( 'Order Payment', 'blocksy-child' )
];

$step_descriptions = [
	'step1' => __( 'Enter your contact details', 'blocksy-child' ),
	'step2' => __( 'Billing and shipping information', 'blocksy-child' ),
	'step3' => __( 'Review and complete your order', 'blocksy-child' )
];

$continue_button_texts = [
	'step1' => __( 'CONTINUE TO RECIPIENTS DETAILS →', 'blocksy-child' ),
	'step2' => __( 'CONTINUE TO ORDER PAYMENT →', 'blocksy-child' ),
	'step3' => __( 'PLACE ORDER', 'blocksy-child' )
];

// Start output buffering to capture the checkout content
ob_start();
?>

<div class="blaze-checkout-block" data-checkout-type="<?php echo esc_attr( $attributes['checkoutType'] ); ?>">
	<div class="blaze-checkout-container">

		<?php if ( ! empty( $attributes['mainHeading'] ) ) : ?>
		<h1 class="blaze-main-heading"><?php echo esc_html( $attributes['mainHeading'] ); ?></h1>
		<?php endif; ?>

		<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

			<?php if ( $checkout->get_checkout_fields() ) : ?>

				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<div class="blaze-checkout-content">

					<?php if ( $attributes['checkoutType'] === 'multi-step' ) : ?>
						<?php blaze_render_multi_step_blaze_checkout( $checkout, $step_labels, $step_descriptions, $continue_button_texts ); ?>
					<?php else : ?>
						<?php blaze_render_original_checkout_structure( $checkout ); ?>
					<?php endif; ?>

				</div>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

			<?php endif; ?>

			<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php
				// Use our custom order summary that excludes payment elements
				blaze_render_clean_order_summary(
					$attributes['orderSummaryHeading'],
					$attributes['editButtonText'] ?? __( 'Edit', 'blocksy-child' )
				);
				?>
			</div>

			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

		</form>

	</div>
</div>

<?php
// Get the buffered content and echo it (required for Blocksy theme compatibility)
$checkout_content = ob_get_clean();
echo $checkout_content;
