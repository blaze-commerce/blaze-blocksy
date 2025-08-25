<?php
/**
 * Recently Viewed Products - Auto Display After Related Products
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add recently viewed products after related products
 */
add_action( 'woocommerce_after_single_product', 'display_recently_viewed_products', 125 );

/**
 * Display recently viewed products section
 */
function display_recently_viewed_products() {
	global $product;

	do_action( 'qm/info', [ 
		'is_product' => is_product(),
		'is_archive' => is_archive(),
		'is_product_category' => is_product_category(),
		"tester" => "tester"
	] );
	$current_product_id = $product->get_id();
	?>

	Muncul dong!

	<button type="button" onclick="loadRecentlyViewedProducts()">Load Recently Viewed Products</button>

	<section class="recently-viewed-products related products is-width-constrained">
		<h2 class="ct-module-title">Recently Viewed Products</h2>

		<div class="recently-viewed-loading" style="display: none; text-align: center; padding: 40px 0;">
			<div
				style="display: inline-block; width: 20px; height: 20px; border: 2px solid #f3f3f3; border-top: 2px solid #333; border-radius: 50%; animation: spin 1s linear infinite;">
			</div>
			<span style="margin-left: 10px;">Loading products...</span>
		</div>

		<div class="recently-viewed-empty" style="display: none; text-align: center; padding: 40px 0; color: #666;">
			<p>No recently viewed products found.</p>
		</div>

		<div class="products columns-4" id="recently-viewed-products-container">
			<!-- Products akan dimuat via JavaScript -->
		</div>
	</section>

	<style>
		@keyframes spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}
	</style>

	<script>
		document.addEventListener('DOMContentLoaded', function () {
			loadRecentlyViewedProducts();
		});

		function loadRecentlyViewedProducts() {
			// Ambil recently viewed products dari localStorage
			const recentlyViewed = getRecentlyViewedProducts();
			const currentProductId = <?php echo $current_product_id; ?>;

			// Filter out current product dan ambil maksimal 6 produk
			const productIds = recentlyViewed
				.filter(id => id !== currentProductId)
				.slice(0, 6);

			if (productIds.length === 0) {
				showEmptyState();
				return;
			}

			showLoadingState();

			// AJAX request untuk mendapatkan detail produk
			fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'get_recently_viewed_products',
					nonce: '<?php echo wp_create_nonce( 'recently_viewed_nonce' ); ?>',
					product_ids: JSON.stringify(productIds),
					current_product_id: currentProductId,

				})
			})
				.then(response => response.json())
				.then(data => {
					if (data.success && data.data && data.data.html) {
						renderProductsHTML(data.data.html);
					} else {
						showEmptyState();
					}
				})
				.catch(error => {
					console.error('Error loading recently viewed products:', error);
					showEmptyState();
				});
		}

		function getRecentlyViewedProducts() {
			try {
				const stored = localStorage.getItem('recently_viewed_products');
				return stored ? JSON.parse(stored) : [];
			} catch (e) {
				console.error('Error parsing recently viewed products:', e);
				return [];
			}
		}

		function renderProductsHTML(html) {
			const container = document.getElementById('recently-viewed-products-container');
			const section = document.querySelector('.recently-viewed-products');

			if (!container || !section) return;

			// Clear container dan insert HTML dari server
			container.innerHTML = html;

			// Show section
			showProductsState();

			// Initialize carousel menggunakan konfigurasi yang sama dengan related products
			if (typeof jQuery !== 'undefined' && jQuery.fn.owlCarousel) {
				setTimeout(() => {
					const $container = jQuery(container);
					$container.addClass('owl-carousel owl-theme');
					$container.owlCarousel({
						loop: false,
						margin: 24,
						nav: false,
						dots: true,
						responsive: {
							0: { items: 1 },
							600: { items: 2 },
							1000: { items: 4 }
						}
					});
				}, 100);
			}
		}

		function showLoadingState() {
			const loading = document.querySelector('.recently-viewed-loading');
			const empty = document.querySelector('.recently-viewed-empty');
			const container = document.getElementById('recently-viewed-products-container');

			if (loading) loading.style.display = 'block';
			if (empty) empty.style.display = 'none';
			if (container) container.style.display = 'none';
		}

		function showEmptyState() {
			const section = document.querySelector('.recently-viewed-products');
			if (section) {
				section.style.display = 'none';
			}
		}

		function showProductsState() {
			const loading = document.querySelector('.recently-viewed-loading');
			const empty = document.querySelector('.recently-viewed-empty');
			const container = document.getElementById('recently-viewed-products-container');

			if (loading) loading.style.display = 'none';
			if (empty) empty.style.display = 'none';
			if (container) container.style.display = 'block';
		}
	</script>

	<?php
}

/**
 * Track viewed product di localStorage
 */
add_action( 'wp_footer', 'track_recently_viewed_product' );

function track_recently_viewed_product() {
	if ( ! is_product() ) {
		return;
	}

	global $product;

	if ( ! $product ) {
		return;
	}

	$product_id = $product->get_id();
	?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			// Ambil recently viewed products dari localStorage
			let recentlyViewed = JSON.parse(localStorage.getItem('recently_viewed_products') || '[]');

			// Hapus product ID yang sudah ada (untuk menghindari duplikat)
			recentlyViewed = recentlyViewed.filter(id => id != <?php echo $product_id; ?>);

			// Tambahkan product ID di awal array
			recentlyViewed.unshift(<?php echo $product_id; ?>);

			// Batasi maksimal 20 produk
			if (recentlyViewed.length > 20) {
				recentlyViewed = recentlyViewed.slice(0, 20);
			}

			// Simpan kembali ke localStorage
			localStorage.setItem('recently_viewed_products', JSON.stringify(recentlyViewed));
		});
	</script>
	<?php
}

/**
 * AJAX handler untuk mengambil recently viewed products
 */
add_action( 'wp_ajax_get_recently_viewed_products', 'ajax_get_recently_viewed_products' );
add_action( 'wp_ajax_nopriv_get_recently_viewed_products', 'ajax_get_recently_viewed_products' );

function ajax_get_recently_viewed_products() {
	// Verify nonce untuk security
	if ( ! wp_verify_nonce( $_POST['nonce'], 'recently_viewed_nonce' ) ) {
		wp_die( 'Security check failed' );
	}

	$product_ids = isset( $_POST['product_ids'] ) ? json_decode( stripslashes( $_POST['product_ids'] ), true ) : array();
	$current_product_id = isset( $_POST['current_product_id'] ) ? intval( $_POST['current_product_id'] ) : 0;

	// Hapus current product dari list
	if ( $current_product_id > 0 ) {
		$product_ids = array_filter( $product_ids, function ($id) use ($current_product_id) {
			return intval( $id ) !== $current_product_id;
		} );
	}

	// Batasi jumlah produk
	$product_ids = array_slice( $product_ids, 0, 6 );

	// Generate HTML menggunakan WooCommerce loop
	ob_start();

	// Set up WooCommerce loop
	global $woocommerce_loop;
	$woocommerce_loop['is_shortcode'] = true;
	$woocommerce_loop['columns'] = 4;

	foreach ( $product_ids as $product_id ) {
		$product = wc_get_product( intval( $product_id ) );

		if ( ! $product || ! $product->is_visible() ) {
			continue;
		}

		// Set global product untuk template
		global $product;
		$GLOBALS['product'] = $product;

		// Render menggunakan WooCommerce content template
		wc_get_template_part( 'content', 'product' );
	}

	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}
