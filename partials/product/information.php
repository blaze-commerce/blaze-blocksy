<?php

if ( ! function_exists( 'get_field' ) ) {
	echo 'ACF not found';
	return;
}

$tab_data = [];
$shipping_icon = get_field( 'shipping_icon', 'option' );
$offcanvas_data = (array) get_field( 'tab_data', 'option' );

$offcanvas_data = array_map( function ( $info ) {
	$info['offcanvas_id'] = sanitize_title( $info['title'] );
	return $info;
}, $offcanvas_data );

$offcanvas_data = apply_filters( 'blocksychild:product_information:offcanvas_data', $offcanvas_data );
?>

<div class="info-list">
	<div class="info-item" onclick="openOffcanvas('shipping')">
		<?php echo $shipping_icon; ?>
		<span>Shipping</span>
	</div>

	<?php
	$list_data = apply_filters( 'blocksychild:product_information:list_data', $offcanvas_data );
	foreach ( (array) $list_data as $info ) :
		if ( isset( $info['type'] ) && 'link' === $info['type'] ) :
			?>
			<a href="<?php echo esc_url( $info['link'] ); ?>" class="info-item">
				<?php echo $info['svg_icon']; ?>
				<span><?php echo $info['title']; ?></span>
			</a>
			<?php
		else :
			?>
			<div class="info-item" onclick="openOffcanvas('<?php echo esc_attr( $info['offcanvas_id'] ); ?>')">
				<?php echo $info['svg_icon']; ?>
				<span><?php echo $info['title']; ?></span>
			</div>
			<?php
		endif;
	endforeach; ?>

</div>

<!-- Placeholder for lazy-loaded offcanvas content -->
<div id="ct-product-information-offcanvas-container"></div>