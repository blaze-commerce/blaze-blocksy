<?php
/**
 * Recently Viewed Products - AJAX Implementation
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add recently viewed products after related products (AJAX placeholder)
 */
add_action( 'woocommerce_after_single_product', 'display_recently_viewed_products_placeholder', 125 );

/**
 * Display recently viewed products placeholder for AJAX loading
 */
function display_recently_viewed_products_placeholder() {
	global $product;
	$current_product_id = $product->get_id();
	?>
	<section class="recently-viewed-products up-sells products is-width-constrained" id="recently-viewed-section"
		style="display: none;">
		<h2 class="ct-module-title">Recently Viewed Products</h2>
		<div class="products columns-4" data-products="type-1" data-hover="zoom-in" id="recently-viewed-products-container">
			<!-- Products will be loaded via AJAX -->
		</div>
	</section>

	<script>
		jQuery(document).ready(function ($) {
			// Load recently viewed products via AJAX
			loadRecentlyViewedProducts(<?php echo $current_product_id; ?>);
		});
	</script>
	<?php
}

/**
 * Get recently viewed products from localStorage/sessionStorage with cookie fallback
 */
function get_recently_viewed_products_from_storage() {
	// This function will be called via AJAX, so we check both cookie and POST data
	$products = array();

	// Check if products are sent via AJAX POST
	if ( isset( $_POST['recently_viewed'] ) && is_array( $_POST['recently_viewed'] ) ) {
		$products = array_map( 'intval', $_POST['recently_viewed'] );
	} else {
		// Fallback to cookie
		$cookie_name = 'recently_viewed_products';
		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			$cookie_products = json_decode( stripslashes( $_COOKIE[ $cookie_name ] ), true );
			if ( is_array( $cookie_products ) ) {
				$products = array_map( 'intval', $cookie_products );
			}
		}
	}

	return $products;
}

/**
 * AJAX handler for getting recently viewed products
 */
add_action( 'wp_ajax_get_recently_viewed_products', 'ajax_get_recently_viewed_products' );
add_action( 'wp_ajax_nopriv_get_recently_viewed_products', 'ajax_get_recently_viewed_products' );

function ajax_get_recently_viewed_products() {
	// Verify nonce for security
	if ( ! wp_verify_nonce( $_POST['nonce'], 'recently_viewed_nonce' ) ) {
		wp_die( 'Security check failed' );
	}

	$current_product_id = intval( $_POST['current_product_id'] );

	// Get recently viewed products
	$recently_viewed_products = get_recently_viewed_products_from_storage();

	// Filter out current product and limit to 10 products
	$product_ids = array_filter( $recently_viewed_products, function ($id) use ($current_product_id) {
		return intval( $id ) !== $current_product_id;
	} );
	$product_ids = array_slice( $product_ids, 0, 10 );

	// If no products to show, return empty
	if ( empty( $product_ids ) ) {
		wp_send_json_success( array( 'html' => '', 'has_products' => false ) );
		return;
	}

	// Start output buffering
	ob_start();

	// Set up WooCommerce loop
	global $woocommerce_loop;
	$woocommerce_loop['is_shortcode'] = true;
	$woocommerce_loop['columns'] = 4;

	foreach ( $product_ids as $product_id ) {
		$product_obj = wc_get_product( intval( $product_id ) );

		if ( ! $product_obj || ! $product_obj->is_visible() ) {
			continue;
		}

		// Set global product untuk template
		$GLOBALS['product'] = $product_obj;

		// Render menggunakan WooCommerce content template
		wc_get_template_part( 'content', 'product' );
	}

	$html = ob_get_clean();

	wp_send_json_success( array(
		'html' => $html,
		'has_products' => ! empty( $product_ids ),
		'product_count' => count( $product_ids )
	) );
}

/**
 * Get recently viewed products from cookie (legacy function for compatibility)
 */
function get_recently_viewed_products_from_cookie() {
	$cookie_name = 'recently_viewed_products';

	if ( ! isset( $_COOKIE[ $cookie_name ] ) ) {
		return array();
	}

	$products = json_decode( stripslashes( $_COOKIE[ $cookie_name ] ), true );

	if ( ! is_array( $products ) ) {
		return array();
	}

	return $products;
}

/**
 * Track viewed product using JavaScript (client-side)
 */
add_action( 'wp_footer', 'add_recently_viewed_tracking_script' );

function add_recently_viewed_tracking_script() {
	if ( ! is_product() ) {
		return;
	}

	global $product;
	if ( ! $product ) {
		return;
	}

	$product_id = $product->get_id();
	$ajax_url = admin_url( 'admin-ajax.php' );
	$nonce = wp_create_nonce( 'recently_viewed_nonce' );
	?>
	<script>
		// Recently Viewed Products - Client-side tracking and AJAX loading
		(function ($) {
			'use strict';

			// Storage key for recently viewed products
			const STORAGE_KEY = 'recently_viewed_products';
			const COOKIE_NAME = 'recently_viewed_products';

			// Get products from localStorage with sessionStorage and cookie fallback
			function getRecentlyViewedProducts() {
				let products = [];

				// Try localStorage first
				try {
					const stored = localStorage.getItem(STORAGE_KEY);
					if (stored) {
						products = JSON.parse(stored);
					}
				} catch (e) {
					// Fallback to sessionStorage
					try {
						const stored = sessionStorage.getItem(STORAGE_KEY);
						if (stored) {
							products = JSON.parse(stored);
						}
					} catch (e2) {
						// Fallback to cookie
						const cookieValue = getCookie(COOKIE_NAME);
						if (cookieValue) {
							try {
								products = JSON.parse(cookieValue);
							} catch (e3) {
								products = [];
							}
						}
					}
				}

				return Array.isArray(products) ? products : [];
			}

			// Save products to storage
			function saveRecentlyViewedProducts(products) {
				const jsonProducts = JSON.stringify(products);

				// Try localStorage first
				try {
					localStorage.setItem(STORAGE_KEY, jsonProducts);
				} catch (e) {
					// Fallback to sessionStorage
					try {
						sessionStorage.setItem(STORAGE_KEY, jsonProducts);
					} catch (e2) {
						// Fallback to cookie
						setCookie(COOKIE_NAME, jsonProducts, 30);
					}
				}

				// Always try to set cookie as backup
				try {
					setCookie(COOKIE_NAME, jsonProducts, 30);
				} catch (e) {
					// Silent fail
				}
			}

			// Cookie helper functions
			function getCookie(name) {
				const value = "; " + document.cookie;
				const parts = value.split("; " + name + "=");
				if (parts.length === 2) return parts.pop().split(";").shift();
				return null;
			}

			function setCookie(name, value, days) {
				const expires = new Date();
				expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
				document.cookie = name + "=" + value + ";expires=" + expires.toUTCString() + ";path=/";
			}

			// Track current product
			function trackCurrentProduct(productId) {
				let products = getRecentlyViewedProducts();

				// Remove current product if it exists (avoid duplicates)
				products = products.filter(id => parseInt(id) !== parseInt(productId));

				// Add current product to the beginning
				products.unshift(parseInt(productId));

				// Limit to maximum 20 products
				if (products.length > 20) {
					products = products.slice(0, 20);
				}

				// Save to storage
				saveRecentlyViewedProducts(products);
			}

			// Load recently viewed products via AJAX
			window.loadRecentlyViewedProducts = function (currentProductId) {
				const products = getRecentlyViewedProducts();

				$.ajax({
					url: '<?php echo $ajax_url; ?>',
					type: 'POST',
					data: {
						action: 'get_recently_viewed_products',
						current_product_id: currentProductId,
						recently_viewed: products,
						nonce: '<?php echo $nonce; ?>'
					},
					success: function (response) {
						if (response.success && response.data.has_products) {
							$('#recently-viewed-products-container').html(response.data.html);
							$('#recently-viewed-section').show();

							// Initialize owl carousel for recently viewed products
							initializeRecentlyViewedCarousel();
						}
					},
					error: function () {
						console.log('Failed to load recently viewed products');
					}
				});
			};

			// Initialize owl carousel for recently viewed products
			function initializeRecentlyViewedCarousel() {
				const $carousel = $('.recently-viewed-products .products');

				if ($carousel.length && !$carousel.hasClass('owl-carousel')) {
					$carousel.addClass('owl-carousel owl-theme');

					// Use the same config as related products (from related-carousel.php)
					const carouselConfig = {
						loop: false,
						margin: 24,
						nav: false,
						dots: true,
						responsive: {
							0: {
								items: 2,
							},
							1000: {
								items: 4
							}
						}
					};

					// Small delay to ensure DOM is ready
					setTimeout(function () {
						$carousel.owlCarousel(carouselConfig);
					}, 100);
				}
			}

			// Track current product when page loads
			$(document).ready(function () {
				trackCurrentProduct(<?php echo $product_id; ?>);
			});

		})(jQuery);
	</script>
	<?php
}


