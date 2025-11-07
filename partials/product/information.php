<?php

if ( ! function_exists( 'get_field' ) ) {
	echo 'ACF not found';
	return;
}

$tab_data = [];
$product_information = get_field( 'tab_data', 'option' );
?>

<div class="info-list">
	<div class="info-item" onclick="openOffcanvas('shipping')">
		<svg viewBox="0 0 24 24">
			<path
				d="M3,4A2,2 0 0,0 1,6V17H3A3,3 0 0,0 6,20A3,3 0 0,0 9,17H15A3,3 0 0,0 18,20A3,3 0 0,0 21,17H23V12L20,8H17V4M3,6H15V15H9.17C8.5,14.4 7.79,14 7,14C6.21,14 5.5,14.4 4.83,15H3M17,10H19.5L21.47,12H17M6,15.5A1.5,1.5 0 0,1 7.5,17A1.5,1.5 0 0,1 6,18.5A1.5,1.5 0 0,1 4.5,17A1.5,1.5 0 0,1 6,15.5M18,15.5A1.5,1.5 0 0,1 19.5,17A1.5,1.5 0 0,1 18,18.5A1.5,1.5 0 0,1 16.5,17A1.5,1.5 0 0,1 18,15.5Z" />
		</svg>
		<span>Shipping</span>
	</div>

	<?php foreach ( (array) $product_information as $info ) : ?>

		<?php if ( $add_separator ) { ?>
			<div class="separator"></div>
		<?php } ?>

		<div class="info-item" onclick="openOffcanvas('<?php echo sanitize_title( $info['title'] ); ?>')">
			<?php echo $info['svg_icon']; ?>
			<span><?php echo $info['title']; ?></span>
		</div>

	<?php endforeach; ?>

</div>

<!-- Offcanvas -->
<div class="ct-information-canvas offcanvas-overlay" id="ct-product-information-offcanvas-overlay"
	onclick="closeOffcanvas()"></div>
<div class="ct-information-canvas offcanvas" id="ct-product-information-offcanvas">
	<div class="offcanvas-header">
		<div class="offcanvas-tabs">
			<div class="offcanvas-tab active" data-tab="shipping">Shipping</div>
			<?php foreach ( (array) $product_information as $info ) : ?>
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

		<?php foreach ( (array) $product_information as $info ) : ?>
			<div class="tab-content" id="<?php echo sanitize_title( $info['title'] ); ?>-content">
				<?php echo $info['content']; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>