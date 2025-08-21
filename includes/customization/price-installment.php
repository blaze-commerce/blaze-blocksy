<?php

add_action( 'blocksy:woocommerce:product-single:price:after', function () {

	if ( ! is_product() )
		return;

	global $product;

	$month = 12;
	$product_price = $product->get_price();
	$installment_price = $product_price / $month;

	?>
	<div class="ct-price-installment">
		<div class="ct-price-installment-logo">
			<img src="<?php echo BLAZE_BLOCKSY_URL; ?>/assets/images/affirm.svg" alt="affirm" />
			<img src="<?php echo BLAZE_BLOCKSY_URL; ?>/assets/images/klarna.svg" alt="klarna" />
			<img src="<?php echo BLAZE_BLOCKSY_URL; ?>/assets/images/afterpay.svg" alt="afterpay" />
		</div>
		<p>
			As low as <?php echo wc_price( $installment_price ); ?> per month or interest-free
			<img src="<?php echo BLAZE_BLOCKSY_URL; ?>/assets/images/info.svg" alt="info" />
		</p>
	</div>
	<?php
} );