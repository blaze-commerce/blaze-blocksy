<?php
/**
 * Checkout Step Form — Figma-spec styling for checkout step form area.
 * Task: CU-86ewn0gw9
 *
 * Enqueues CSS for the checkout step form (left column) and injects
 * custom HTML elements via Fluid Checkout hooks:
 *   - Guest description paragraph above email/phone fields
 *   - Newsletter opt-in checkbox below contact fields
 *
 * Requires: Fluid Checkout plugin (class FluidCheckout).
 *
 * @refactored 2026-05-08 — moved from custom/ to inc/ (Layer 1/Layer 2 architecture compliance)
 * @package Blocksy_Child
 * @since   1.0.0
 * @date    2026-04-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FluidCheckout' ) ) {
	return;
}

/**
 * Enqueue checkout step form CSS on checkout pages only.
 */
// Enqueues are centralized in inc/enqueue.php (refactored 2026-05-08, audit P1).

/**
 * Inject guest checkout description paragraph.
 *
 * Figma: "Checking out as a Guest? You'll be able to save your details
 * to create an account with us later." — shown above email/phone fields
 * for non-logged-in users.
 *
 * Hook: fc_checkout_before_contact_fields (form-contact.php line 23)
 */
add_action( 'fc_checkout_before_contact_fields', function () {
	if ( is_user_logged_in() ) {
		return;
	}

	echo '<p class="bc-guest-description">';
	echo esc_html__( "Checking out as a Guest? You'll be able to save your details to create an account with us later.", 'blocksy-child' );
	echo '</p>';
}, 5 );

/**
 * Inject newsletter opt-in checkbox after contact fields.
 *
 * Figma: "Sign me up to receive email updates and member only offers (Optional)"
 * Renders a checkbox that saves to order meta.
 *
 * Hook: fc_checkout_contact_after_fields (form-contact.php line 37)
 */
add_action( 'fc_checkout_contact_after_fields', function () {
	?>
	<div class="bc-newsletter-optin form-row">
		<label class="bc-newsletter-optin__label">
			<input type="checkbox" name="bc_newsletter_optin" value="1" class="bc-newsletter-optin__checkbox" />
			<span class="bc-newsletter-optin__text"><?php echo esc_html__( 'Sign me up to receive email updates and member only offers (Optional)', 'blocksy-child' ); ?></span>
		</label>
	</div>
	<?php
}, 20 );

/**
 * Save newsletter opt-in to order meta.
 */
add_action( 'woocommerce_checkout_update_order_meta', function ( $order_id ) {
	if ( ! empty( $_POST['bc_newsletter_optin'] ) ) {
		update_post_meta( $order_id, '_bc_newsletter_optin', 'yes' );
	}
} );
