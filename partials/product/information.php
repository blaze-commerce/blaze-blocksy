<?php

if ( ! function_exists( 'get_field' ) ) {
	echo 'ACF not found';
	return;
}

$tab_data = [];
$shipping_icon = get_field( 'shipping_icon', 'option' );
$offcanvas_data = (array) get_field( 'tab_data', 'option' );

$offcanvas_data = apply_filters( 'blocksychild:product_information:offcanvas_data', $offcanvas_data );
?>

<div class="info-list">
	<div class="info-item" onclick="openOffcanvas('shipping')">
		<?php echo $shipping_icon; ?>
		<span>Shipping</span>
	</div>

	<?php foreach ( (array) $offcanvas_data as $info ) :
		if ( isset( $info['type'] ) && 'link' === $info['type'] ) :
			?>
			<a href="<?php echo esc_url( $info['link'] ); ?>" class="info-item">
				<?php echo $info['svg_icon']; ?>
				<span><?php echo $info['title']; ?></span>
			</a>
			<?php
		else :
			?>
			<div class="info-item" onclick="openOffcanvas('<?php echo sanitize_title( $info['title'] ); ?>')">
				<?php echo $info['svg_icon']; ?>
				<span><?php echo $info['title']; ?></span>
			</div>
			<?php
		endif;
	endforeach; ?>

</div>

<!-- Offcanvas -->
<div class="ct-information-canvas offcanvas-overlay" id="ct-product-information-offcanvas-overlay"
	onclick="closeOffcanvas()"></div>
<div class="ct-information-canvas offcanvas" id="ct-product-information-offcanvas">
	<div class="offcanvas-header">
		<div class="offcanvas-tabs">
			<div class="offcanvas-tab active" data-tab="shipping">Shipping</div>
			<?php
			foreach ( $offcanvas_data as $info ) :
				if ( isset( $info['type'] ) && 'link' === $info['type'] ) :
					continue;
				endif;
				?>
				<div class="offcanvas-tab" data-tab="<?php echo sanitize_title( $info['title'] ); ?>">
					<?php echo $info['title']; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<button class="close-btn" onclick="closeOffcanvas()">&times;</button>
	</div>
	<div class="offcanvas-body">
		<div class="tab-content active" id="shipping-content">
			<?php require_once( BLAZE_BLOCKSY_PATH . '/partials/product/shipping-calculator.php' ); ?>
		</div>

		<?php
		foreach ( (array) $offcanvas_data as $info ) :
			if ( isset( $info['type'] ) && 'link' === $info['type'] ) :
				continue;
			endif;
			?>
			<div class="tab-content" id="<?php echo sanitize_title( $info['title'] ); ?>-content">
				<?php echo $info['content']; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>