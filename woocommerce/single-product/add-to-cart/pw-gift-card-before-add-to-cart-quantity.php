<?php
/**
 * Gift Card form — child theme override.
 * Adds live-site IDs and custom styling classes while keeping all PWGC
 * plugin variables, constants, and JS hooks intact.
 *
 * @package blocksy-child
 */

defined( 'ABSPATH' ) || exit;

global $product;
global $pw_gift_cards;
global $pw_gift_cards_email_designer;

$pwgc_custom_amount = isset( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) && ! empty( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) && is_numeric( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ? number_format( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ], wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ) : '';
$pwgc_to            = isset( $_REQUEST[ PWGC_TO_META_KEY ] ) ? stripslashes( htmlentities( $_REQUEST[ PWGC_TO_META_KEY ] ) ) : '';
$pwgc_recipient_name = isset( $_REQUEST[ PWGC_RECIPIENT_NAME_META_KEY ] ) ? stripslashes( htmlentities( $_REQUEST[ PWGC_RECIPIENT_NAME_META_KEY ] ) ) : '';
$pwgc_message       = isset( $_REQUEST[ PWGC_MESSAGE_META_KEY ] ) ? stripslashes( htmlentities( str_replace( '<br />', "\n", $_REQUEST[ PWGC_MESSAGE_META_KEY ] ) ) ) : '';
$pwgc_delivery_date = isset( $_REQUEST[ PWGC_DELIVERY_DATE_META_KEY ] ) ? stripslashes( htmlentities( $_REQUEST[ PWGC_DELIVERY_DATE_META_KEY ] ) ) : '';
$pwgc_physical_card = $product->get_pwgc_is_physical_card();
$pwgc_show_recipient_name = $product->get_pwgc_show_recipient_name();
$pwgc_show_email_preview  = $product->get_pwgc_show_email_preview();
$pwgc_email_design_id  = isset( $_REQUEST[ PWGC_EMAIL_DESIGN_ID_META_KEY ] ) ? absint( $_REQUEST[ PWGC_EMAIL_DESIGN_ID_META_KEY ] ) : '';
$pwgc_email_design_ids = $product->get_pwgc_email_design_ids();

$pwgc_from = '';
if ( isset( $_REQUEST[ PWGC_FROM_META_KEY ] ) ) {
	$pwgc_from = stripslashes( htmlentities( $_REQUEST[ PWGC_FROM_META_KEY ] ) );
} elseif ( 'yes' === get_option( 'pwgc_current_user_populates_from_field', 'yes' ) ) {
	$current_user = wp_get_current_user();
	$pwgc_from    = $current_user->display_name;
}

if ( isset( $_REQUEST[ 'attribute_' . PWGC_DENOMINATION_ATTRIBUTE_SLUG ] ) ) {
	$selected = true;
} else {
	$default_attribute = $product->get_variation_default_attribute( PWGC_DENOMINATION_ATTRIBUTE_NAME );
	$selected          = ! empty( $default_attribute );
}

if ( $pw_gift_cards->use_default_currency_in_cart() ) {
	$pwgc_custom_amount = apply_filters( 'pwgc_to_current_currency', $pwgc_custom_amount );
}
?>
<div id="pwgc-purchase-container"
	style="<?php echo $selected ? '' : 'display: none;'; ?>"
	data-min-amount="<?php echo esc_attr( apply_filters( 'pwgc_to_current_currency', $product->get_pwgc_custom_amount_min() ) ); ?>"
	data-max-amount="<?php echo esc_attr( apply_filters( 'pwgc_to_current_currency', $product->get_pwgc_custom_amount_max() ) ); ?>"
>

	<div id="pwgc-custom-amount-form" class="pwgc-field-container <?php echo ! empty( $pwgc_custom_amount ) ? '' : 'pwgc-hidden'; ?>">
		<label for="pwgc-custom-amount" class="pwgc-label"><?php echo esc_html( pwgc_get_other_amount_prompt( $product ) ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?>&nbsp;</label>
		<input type="text" id="pwgc-custom-amount" class="short wc_input_price pwgc-input" name="<?php echo PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY; ?>" value="<?php echo esc_attr( $pwgc_custom_amount ); ?>" />
		<div id="pwgc-custom-amount-error" class="pwgc-subtitle" style="color: red !important;"></div>
	</div>

	<?php if ( $pwgc_physical_card ) : ?>
		<div id="pwgc-form-shipping-address-message" class="pwgc-field-container">
			<?php esc_html_e( 'Gift card will be delivered to the Shipping Address entered during checkout.', 'pw-woocommerce-gift-cards' ); ?>
		</div>
	<?php else : ?>
		<div id="pwgc-form-to" class="pwgc-field-container">
			<label for="pwgc-to" class="pwgc-label"><?php echo esc_html__( PWGC_TO_META_DISPLAY_NAME, 'pw-woocommerce-gift-cards' ); ?></label>
			<?php
			$placeholder = __( 'Enter an email address for each recipient', 'pw-woocommerce-gift-cards' );
			$subtitle    = __( 'Separate multiple email addresses with a comma.', 'pw-woocommerce-gift-cards' );
			if ( 'yes' !== get_option( 'pwgc_allow_multiple_recipients', 'yes' ) ) {
				$placeholder = __( 'Recipient email address', 'pw-woocommerce-gift-cards' );
				$subtitle    = '';
			}
			?>
			<input type="text"
				id="pwgc-to"
				name="<?php echo PWGC_TO_META_KEY; ?>"
				class="pwgc-input-text pwgc-input"
				placeholder="<?php echo esc_attr( $placeholder ); ?>"
				value="<?php echo esc_attr( $pwgc_to ); ?>"
				required
			/>
			<div class="pwgc-subtitle"><?php echo esc_html( $subtitle ); ?></div>
		</div>
	<?php endif; ?>

	<?php if ( $pwgc_show_recipient_name ) : ?>
		<div id="pwgc-form-recipient-name" class="pwgc-field-container">
			<label for="pwgc-recipient-name" class="pwgc-label"><?php echo esc_html__( PWGC_RECIPIENT_NAME_META_DISPLAY_NAME, 'pw-woocommerce-gift-cards' ); ?></label>
			<input type="text"
				id="pwgc-recipient-name"
				name="<?php echo PWGC_RECIPIENT_NAME_META_KEY; ?>"
				class="pwgc-input-text pwgc-input"
				placeholder="<?php esc_attr_e( 'Enter a friendly name for the recipient (optional).', 'pw-woocommerce-gift-cards' ); ?>"
				value="<?php echo esc_attr( $pwgc_recipient_name ); ?>"
			/>
		</div>
	<?php endif; ?>

	<div id="pwgc-form-from" class="pwgc-field-container">
		<label for="pwgc-from" class="pwgc-label"><?php echo esc_html__( PWGC_FROM_META_DISPLAY_NAME, 'pw-woocommerce-gift-cards' ); ?></label>
		<input type="text"
			id="pwgc-from"
			name="<?php echo PWGC_FROM_META_KEY; ?>"
			class="pwgc-input-text pwgc-input"
			placeholder="<?php esc_attr_e( 'Your name', 'pw-woocommerce-gift-cards' ); ?>"
			value="<?php echo esc_attr( $pwgc_from ); ?>"
			required
		/>
	</div>

	<div id="pwgc-form-message" class="pwgc-field-container">
		<label for="pwgc-message" class="pwgc-label"><?php echo esc_html__( PWGC_MESSAGE_META_DISPLAY_NAME, 'pw-woocommerce-gift-cards' ); ?> <?php esc_html_e( '(optional)', 'pw-woocommerce-gift-cards' ); ?></label>
		<textarea
			id="pwgc-message"
			name="<?php echo PWGC_MESSAGE_META_KEY; ?>"
			class="pwgc-input pwgc-textarea"
			placeholder="<?php esc_attr_e( 'Add a message', 'pw-woocommerce-gift-cards' ); ?>"
			maxlength="<?php echo PWGC_MAX_MESSAGE_CHARACTERS; ?>"
		><?php echo esc_textarea( $pwgc_message ); ?></textarea>
		<div class="pwgc-subtitle"><span id="pwgc-message-characters-remaining"><?php echo PWGC_MAX_MESSAGE_CHARACTERS; ?></span> <?php esc_html_e( 'characters remaining', 'pw-woocommerce-gift-cards' ); ?></div>
	</div>

	<?php if ( ! $pwgc_physical_card && 'yes' === get_option( 'pwgc_allow_scheduled_delivery', 'yes' ) ) : ?>
		<div id="pwgc-form-delivery-date" class="pwgc-field-container">
			<label for="pwgc-delivery-date" class="pwgc-label"><?php echo esc_html__( PWGC_DELIVERY_DATE_META_DISPLAY_NAME, 'pw-woocommerce-gift-cards' ); ?></label>
			<input type="text"
				id="pwgc-delivery-date"
				name="<?php echo PWGC_DELIVERY_DATE_META_KEY; ?>"
				class="pwgc-input-text pwgc-input"
				placeholder="<?php esc_attr_e( 'Now', 'pw-woocommerce-gift-cards' ); ?>"
				value="<?php echo esc_attr( $pwgc_delivery_date ); ?>"
				autocomplete="off"
			/>
			<div class="pwgc-subtitle"><?php esc_html_e( 'Up to a year from today', 'pw-woocommerce-gift-cards' ); ?></div>
		</div>
	<?php endif; ?>

	<?php
	$design_options = array();
	if ( count( $pwgc_email_design_ids ) > 1 ) {
		$designs = $pw_gift_cards_email_designer->get_designs();
		foreach ( $designs as $id => $design ) {
			if ( in_array( $id, $pwgc_email_design_ids ) ) {
				$design_options[ $id ] = $design['name'];
			}
		}
	}
	?>
	<div id="pwgc-form-email-design" class="pwgc-field-container">
		<?php if ( count( $design_options ) > 0 ) : ?>
			<label for="pwgc-email-design" class="pwgc-label"><?php echo esc_html__( PWGC_EMAIL_DESIGN_META_DISPLAY_NAME, 'pw-woocommerce-gift-cards' ); ?></label>
			<select id="pwgc-email-design-id" name="<?php echo PWGC_EMAIL_DESIGN_ID_META_KEY; ?>">
				<?php foreach ( $design_options as $id => $name ) : ?>
					<option value="<?php echo absint( $id ); ?>" <?php selected( $pwgc_email_design_id, $id ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php else :
			$design_id = count( $pwgc_email_design_ids ) > 0 ? $pwgc_email_design_ids[0] : '0'; ?>
			<input id="pwgc-email-design-id" type="hidden" name="<?php echo PWGC_EMAIL_DESIGN_ID_META_KEY; ?>" value="<?php echo esc_attr( $design_id ); ?>">
		<?php endif; ?>

		<?php if ( $pwgc_show_email_preview && ! $pwgc_physical_card ) : ?>
			<button id="pwgc-email-preview-button" class="button pwgc-email-preview-button"><?php esc_html_e( 'Preview', 'pw-woocommerce-gift-cards' ); ?></button>
			<iframe id="pwgc-email-preview-container"></iframe>
		<?php endif; ?>
	</div>

	<div id="pwgc-quantity-one-per-recipient" class="pwgc-field-container">
		<div class="pwgc-label"><?php esc_html_e( 'Quantity', 'pw-woocommerce-gift-cards' ); ?>: <span id="pwgc-recipient-count">1</span></div>
	</div>

</div>
