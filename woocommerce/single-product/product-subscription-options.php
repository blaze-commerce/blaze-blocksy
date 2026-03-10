<?php
/**
 * Product Subscription Options Template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/product-subscription-options.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 4.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wcsatt-options-wrapper <?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
	data-sign_up_text="<?php echo esc_attr( $sign_up_text ); ?>" <?php echo $hide_wrapper ? 'style="display:none;"' : ''; ?>>
	<div class="wcsatt-options-product-prompt <?php echo esc_attr( implode( ' ', $prompt_classes ) ); ?>" style="display:none;"
		data-prompt_type="<?php echo esc_attr( $prompt_type ); ?>">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $prompt;
		?>
	</div>
	<h3 class="wcsatt-subscription-header">Subscription</h3>
	<div class="wcsatt-options-product-wrapper" <?php echo in_array( 'closed', $wrapper_classes ) ? 'style="display:none;"' : ''; ?>>
		<?php
		if ( $display_dropdown ) {

			$select_id = 'wcsatt-options-product-dropdown-' . absint( $product_id );

			if ( $dropdown_label ) {
				?>
				<label class="wcsatt-options-product-dropdown-label"
					for="<?php echo esc_attr( $select_id ); ?>"><?php echo wp_kses_post( $dropdown_label ); ?></label>
				<?php
			} else {
				?>
				<label class="wcsatt-options-product-dropdown-label screen-reader-text"
					for="<?php echo esc_attr( $select_id ); ?>"><?php esc_html_e( 'Select subscription option', 'woocommerce-all-products-for-subscriptions' ); ?></label>
				<?php
			}

			?>
			<select class="wcsatt-options-product-dropdown" id="<?php echo esc_attr( $select_id ); ?>"
				name="convert_to_sub_dropdown<?php echo absint( $product_id ); ?>">
				<?php
				foreach ( $options as $option ) {

					if ( ! $option['value'] ) {
						continue;
					}

					?>
					<option <?php echo $option['selected'] ? 'selected="true"' : ''; ?>value="<?php echo esc_attr( $option['value'] ); ?>"><?php echo esc_html( $option['dropdown'] ); ?>
					</option>
					<?php
				}
				?>
			</select>
			<?php
		}

		?>
		<ul class="wcsatt-options-product wcsatt-options-product--<?php echo $display_dropdown ? 'hidden' : ''; ?>">
			<?php
			$_product = wc_get_product( $product_id );

			foreach ( $options as $option ) {
				$is_one_time     = 'one-time-option' === $option['class'];
				$is_subscription = 'subscription-option' === $option['class'];

				// Extract data for card display.
				$card_label = '';
				$card_price = '';
				$card_terms = '';
				$card_badge = '';
				$discount   = 0;

				if ( $is_one_time ) {
					$card_label = __( 'One Time Purchase', 'woocommerce-all-products-for-subscriptions' );

					// Build price with WCSATT-targetable class so the plugin's JS
					// can find and update it on variation change.
					// WCSATT JS targets: .one-time-option .price.one-time-price
					$raw_price = '';
					if ( $_product && class_exists( 'WCS_ATT_Product_Prices' ) ) {
						$raw_price = WCS_ATT_Product_Prices::get_price_html( $_product, false, array() );
					} elseif ( $_product ) {
						$raw_price = $_product->get_price_html();
					}
					$card_price = '<span class="price one-time-price">' . $raw_price . '</span>';

				} elseif ( $is_subscription && ! empty( $option['data']['subscription_scheme'] ) ) {
					$scheme   = $option['data']['subscription_scheme'];
					$discount = ! empty( $scheme['discount'] ) ? floatval( $scheme['discount'] ) : 0;

					if ( $discount > 0 ) {
						$card_label = sprintf( __( 'Subscribe & Save %s%%', 'woocommerce-all-products-for-subscriptions' ), intval( $discount ) );
						$card_badge = __( 'Most Popular', 'woocommerce-all-products-for-subscriptions' );
					} else {
						$card_label = __( 'Subscribe', 'woocommerce-all-products-for-subscriptions' );
					}

					// Use the original description which contains .subscription-price class.
					// WCSATT JS targets: scheme.$el.find('.subscription-price')
					// This allows the plugin's own JS to update the price on variation change.
					$card_price = $option['description'];

					// Build terms text from scheme data.
					$min_periods = ! empty( $scheme['length'] ) ? intval( $scheme['length'] ) : 0;
					if ( $min_periods > 0 ) {
						$period = ! empty( $scheme['period'] ) ? $scheme['period'] : 'month';
						$card_terms = sprintf(
							__( 'I agree to a minimum %d %s subscription which can be cancelled at any time.', 'woocommerce-all-products-for-subscriptions' ),
							$min_periods,
							$period
						);
					}
				}

				?>
				<li class="wcsatt-option-card <?php echo esc_attr( $option['class'] ); ?>">
					<label>
						<input type="radio" name="convert_to_sub_<?php echo absint( $product_id ); ?>"
							data-custom_data="<?php echo esc_attr( wp_json_encode( $option['data'] ) ); ?>"
							value="<?php echo esc_attr( $option['value'] ); ?>" <?php checked( $option['selected'], true, true ); ?> />
						<div class="wcsatt-option-card-content">
							<div class="wcsatt-option-card-header">
								<span class="wcsatt-option-card-label"><?php echo esc_html( $card_label ); ?></span>
								<?php if ( $card_badge ) : ?>
									<span class="wcsatt-option-card-badge"><?php echo esc_html( $card_badge ); ?></span>
								<?php endif; ?>
							</div>
							<span class="wcsatt-option-card-price <?php echo esc_attr( $option['class'] ); ?>-price <?php echo esc_attr( $option['class'] ); ?>-details"><?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $card_price;
							?></span>
						</div>
						<?php if ( $card_terms ) : ?>
							<p class="wcsatt-option-card-terms"><?php echo esc_html( $card_terms ); ?></p>
						<?php endif; ?>
					</label>
				</li>
				<?php
			}
			?>
		</ul>
	</div>
	<?php
	/**
	 * 'wcsatt_display_subscriptions_matching_cart' action.
	 *
	 * @since  3.1.25
	 */
	do_action( 'wcsatt_after_product_subscription_options' );
	?>
</div>
