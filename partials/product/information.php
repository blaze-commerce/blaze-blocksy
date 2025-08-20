<div class="info-list ct-product-information">
	<div class="info-item" onclick="openOffcanvas('shipping')">
		<svg viewBox="0 0 24 24">
			<path
				d="M3,4A2,2 0 0,0 1,6V17H3A3,3 0 0,0 6,20A3,3 0 0,0 9,17H15A3,3 0 0,0 18,20A3,3 0 0,0 21,17H23V12L20,8H17V4M3,6H15V15H9.17C8.5,14.4 7.79,14 7,14C6.21,14 5.5,14.4 4.83,15H3M17,10H19.5L21.47,12H17M6,15.5A1.5,1.5 0 0,1 7.5,17A1.5,1.5 0 0,1 6,18.5A1.5,1.5 0 0,1 4.5,17A1.5,1.5 0 0,1 6,15.5M18,15.5A1.5,1.5 0 0,1 19.5,17A1.5,1.5 0 0,1 18,18.5A1.5,1.5 0 0,1 16.5,17A1.5,1.5 0 0,1 18,15.5Z" />
		</svg>
		<span>Shipping</span>
	</div>

	<?php if ( $add_separator ) { ?>
		<div class="separator"></div>
	<?php } ?>

	<div class="info-item" onclick="openOffcanvas('returns')">
		<svg viewBox="0 0 24 24">
			<path
				d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6M10,8V10L6,12L10,14V16L4,12L10,8Z" />
		</svg>
		<span>Returns</span>
	</div>

	<?php if ( $add_separator ) { ?>
		<div class="separator"></div>
	<?php } ?>

	<div class="info-item" onclick="openOffcanvas('faq')">
		<svg viewBox="0 0 24 24">
			<path
				d="M11,18H13V16H11V18M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,6A4,4 0 0,0 8,10H10A2,2 0 0,1 12,8A2,2 0 0,1 14,10C14,12 11,11.75 11,15H13C13,12.75 16,12.5 16,10A4,4 0 0,0 12,6Z" />
		</svg>
		<span>FAQ</span>
	</div>
</div>

<!-- Offcanvas -->
<div class="ct-information-canvas offcanvas-overlay" id="ct-product-information-offcanvas-overlay"
	onclick="closeOffcanvas()"></div>
<div class="ct-information-canvas offcanvas" id="ct-product-information-offcanvas">
	<div class="offcanvas-header">
		<div class="offcanvas-tabs">
			<div class="offcanvas-tab active" data-tab="shipping">Shipping</div>
			<div class="offcanvas-tab" data-tab="returns">Returns</div>
			<div class="offcanvas-tab" data-tab="faq">FAQ's</div>
		</div>
		<button class="close-btn" onclick="closeOffcanvas()">&times;</button>
	</div>
	<div class="offcanvas-body">
		<div class="tab-content active" id="shipping-content">
			<?php require_once( BLAZE_BLOCKSY_PATH . '/partials/product/shipping-calculator.php' ); ?>
		</div>

		<div class="tab-content" id="returns-content">
			<h3>Returns Policy</h3>
			<p>Our returns policy allows you to return items within 30 days of purchase. Items must be in original
				condition with tags attached.</p>
			<ul>
				<li>Free returns within 30 days</li>
				<li>Items must be unworn and in original packaging</li>
				<li>Refund processed within 5-7 business days</li>
			</ul>
		</div>

		<div class="tab-content" id="faq-content">
			<h3>Frequently Asked Questions</h3>
			<div style="margin-bottom: 15px;">
				<strong>Q: How long does shipping take?</strong>
				<p>A: Standard shipping takes 3-5 business days, express shipping takes 1-2 business days.</p>
			</div>
			<div style="margin-bottom: 15px;">
				<strong>Q: Do you ship internationally?</strong>
				<p>A: Yes, we ship to most countries worldwide. Shipping costs vary by location.</p>
			</div>
			<div style="margin-bottom: 15px;">
				<strong>Q: What payment methods do you accept?</strong>
				<p>A: We accept all major credit cards, PayPal, and bank transfers.</p>
			</div>
		</div>
	</div>
</div>