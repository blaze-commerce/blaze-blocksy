<?php
/**
 * Checkout Trust Section — "Checkout Features" block.
 *
 * Renders a trust section with payment logos + "Secure Payments" text +
 * contact info. Two render variants share the same markup, both attached
 * to fc_checkout_after_order_review at different priorities:
 *   - sidebar (priority 60): desktop sidebar slot.
 *   - mobile  (priority 70): sibling AFTER the same order-review wrapper —
 *     because FC Pro injects the Place Order section inside the wrapper,
 *     this priority puts trust below Place Order on mobile.
 * CSS in assets/css/components/checkout-trust.css toggles which variant is visible
 * per breakpoint so only one shows at a time.
 *
 * Figma: Byron Bay Candles node 6054-109813, Section 8
 * ClickUp: 86ewn0gw9 (parent), 86exc6wjz (mobile fix)
 *
 * @refactored 2026-05-08 — moved from custom/ to inc/ (Layer 1/Layer 2 architecture compliance)
 * @package Blocksy_Child
 * @date    2026-04-23
 * @updated 2026-04-25
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FluidCheckout' ) ) {
	return;
}

/**
 * Enqueue trust section CSS on checkout page only.
 */
// Enqueues are centralized in inc/enqueue.php (refactored 2026-05-08, audit P1).

/**
 * Return the trust section configuration array.
 *
 * @return array
 */
function bc_checkout_trust_get_config() {
	return [
		'left' => [
			'payment_logos' => [
				[
					'name' => 'PayPal',
					'svg'  => '<img src="' . esc_url( BLOCKSY_CHILD_URL . 'assets/images/payment-icons/paypal.png' ) . '" alt="PayPal Logo" loading="lazy" />',
				],
				[
					'name' => 'Secure',
					'svg'  => '<img src="' . esc_url( BLOCKSY_CHILD_URL . 'assets/images/payment-icons/secure.png' ) . '" alt="Secure Payments Logo" loading="lazy" />',	
				],
				[
					'name' => 'Afterpay',
					'svg'  => '<img src="' . esc_url( BLOCKSY_CHILD_URL . 'assets/images/payment-icons/afterpay.png' ) . '" alt="Afterpay Logo" loading="lazy" />',	
				],
				[
					'name' => 'Zip',
					'svg'  => '<img src="' . esc_url( BLOCKSY_CHILD_URL . 'assets/images/payment-icons/zip.png' ) . '" alt="Zip Logo" loading="lazy" />',
				]
			],
			'title'        => __( 'Secure Payments', 'blocksy-child' ),
			'text'         => __( 'We protect your transaction with 256-bit SSL encryption and secure payment methods. Shop confidently, knowing your data is fully protected.', 'blocksy-child' ),
			'contact_text' => 'Have questions or need assistance? Call us at <a href="tel:+61266855478">+61 2 6685 5478</a> to speak with one of our expert. You\'ll find us located in the Industry and Arts Estate in Byron Bay and you are welcome to visit us at the factory door, Monday to Friday during opening hours.',
		],
	];
}

/**
 * Render the trust section markup.
 *
 * @param string $position 'sidebar' (desktop) or 'mobile'.
 */
function bc_checkout_trust_render( $position = 'sidebar' ) {
	$config   = bc_checkout_trust_get_config();
	$position = in_array( $position, array( 'sidebar', 'mobile' ), true ) ? $position : 'sidebar';
	?>
	<div class="bc-checkout-trust bc-checkout-trust--<?php echo esc_attr( $position ); ?>" data-position="<?php echo esc_attr( $position ); ?>">
		<div class="bc-checkout-trust__logos">
			<?php foreach ( $config['left']['payment_logos'] as $logo ) : ?>
				<span class="bc-checkout-trust__logo" title="<?php echo esc_attr( $logo['name'] ); ?>">
					<?php echo $logo['svg']; ?>
				</span>
			<?php endforeach; ?>
		</div>

		<div class="bc-checkout-trust__block">
			<h4 class="bc-checkout-trust__heading">
				<?php echo esc_html( $config['left']['title'] ); ?>
				<span class="bc-checkout-trust__checkmark">&#10004;</span>
			</h4>
			<p class="bc-checkout-trust__body"><?php echo esc_html( $config['left']['text'] ); ?></p>
		</div>

		<p class="bc-checkout-trust__body"><?php echo wp_kses_post( $config['left']['contact_text'] ); ?></p>
	</div>
	<?php
}

// Desktop: inside .fc-sidebar__inner, after #fc-checkout-order-review.
add_action( 'fc_checkout_after_order_review', function () {
	bc_checkout_trust_render( 'sidebar' );
}, 60 );

// Mobile: same parent hook, later priority — renders as a sibling of
// #fc-checkout-order-review. On mobile the sidebar falls below the form
// and the wrapper holds the Place Order button (injected inside the
// order review wrapper via fc_checkout_after_order_review_inside),
// so this order produces: Place Order → Secure Payments visually.
add_action( 'fc_checkout_after_order_review', function () {
	bc_checkout_trust_render( 'mobile' );
}, 70 );
