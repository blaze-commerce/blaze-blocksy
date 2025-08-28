<?php

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_product_category() && ! is_product_tag() && ! is_shop() )
		return;

	wp_enqueue_style( 'blaze-blocksy-product-category', BLAZE_BLOCKSY_URL . '/assets/css/product-category.css' );
} );


add_action( 'wp_footer', function () {

	if ( ! is_product_category() && ! is_shop() )
		return;

	?>
	<script>
		(function ($) {
			/**
			 * Generate counter text based on current and total products
			 */
			const generateCounterText = function (currentProducts, totalCount) {
				if (currentProducts === 1) {
					return "Showing the single result";
				} else if (currentProducts >= totalCount) {
					return `Showing all ${totalCount} results`;
				} else {
					return `Showing 1–${currentProducts} of ${totalCount} results`;
				}
			};

			/**
			 * Update both original and navigation counters
			 */
			const updateCounters = function () {
				const resultCountEl = document.querySelector('.woocommerce-result-count');
				const productsContainer = document.querySelector('.products');
				const navCountEl = document.querySelector('.ct-product-category-count');

				if (!resultCountEl || !productsContainer) {
					return;
				}

				const currentProducts = productsContainer.querySelectorAll('.product').length;
				const originalText = resultCountEl.textContent;
				const totalMatch = originalText.match(/of\s+(\d+)/i);

				if (totalMatch) {
					const totalCount = parseInt(totalMatch[1]);
					const newText = generateCounterText(currentProducts, totalCount);

					// Update original counter
					resultCountEl.textContent = newText;

					// Update navigation counter if exists
					if (navCountEl) {
						navCountEl.textContent = newText;
					}

					<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ): ?>
					console.log('[BlazeBlocksy LoadMore] Updated counters:', currentProducts + '/' + totalCount);
					<?php endif; ?>
				}
			};

			/**
			 * Display initial product count
			 */
			const displayProductCount = function () {
				const theText = $('.woocommerce-result-count').text();
				$('.ct-product-category-count').text(theText);
			};

			/**
			 * Debounce function to prevent excessive calls
			 */
			const debounce = function (func, wait) {
				let timeout;
				return function executedFunction(...args) {
					const later = () => {
						clearTimeout(timeout);
						func(...args);
					};
					clearTimeout(timeout);
					timeout = setTimeout(later, wait);
				};
			};

			// Create debounced version of updateCounters
			const debouncedUpdateCounters = debounce(updateCounters, 100);

			$(document).ready(function () {
				// Add element to .ct-pagination
				$('.ct-pagination').prepend('<div class="ct-product-category-count"></div>');
				displayProductCount();

				// Initialize load more counter functionality
				<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ): ?>
				console.log('[BlazeBlocksy LoadMore] Initializing load more counter');
				<?php endif; ?>

				// Listen for Blocksy theme events
				if (window.ctEvents) {
					<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ): ?>
					console.log('[BlazeBlocksy LoadMore] Blocksy ctEvents found, binding events');
					<?php endif; ?>

					// Primary event for infinite scroll load
					ctEvents.on('ct:infinite-scroll:load', function() {
						setTimeout(debouncedUpdateCounters, 500);
					});

					// Secondary event for frontend initialization
					ctEvents.on('blocksy:frontend:init', function() {
						setTimeout(debouncedUpdateCounters, 500);
					});
				}

				// Fallback: Listen for Load More button clicks
				$(document).on('click', '.ct-load-more', function() {
					<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ): ?>
					console.log('[BlazeBlocksy LoadMore] Load More button clicked');
					<?php endif; ?>

					// Wait for AJAX to complete and DOM to update
					setTimeout(function() {
						debouncedUpdateCounters();
					}, 1000);
				});

				// Fallback: MutationObserver for DOM changes
				const productsContainer = document.querySelector('.products');
				if (productsContainer) {
					<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ): ?>
					console.log('[BlazeBlocksy LoadMore] Setting up MutationObserver');
					<?php endif; ?>

					const observer = new MutationObserver(function(mutations) {
						let shouldUpdate = false;

						mutations.forEach(function(mutation) {
							if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
								// Check if any added nodes are product items
								for (let node of mutation.addedNodes) {
									if (node.nodeType === Node.ELEMENT_NODE &&
										(node.classList.contains('product') ||
										 node.querySelector && node.querySelector('.product'))) {
										shouldUpdate = true;
										break;
									}
								}
							}
						});

						if (shouldUpdate) {
							<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ): ?>
							console.log('[BlazeBlocksy LoadMore] Products container changed, updating counters');
							<?php endif; ?>
							debouncedUpdateCounters();
						}
					});

					observer.observe(productsContainer, {
						childList: true,
						subtree: true
					});
				}

				// Listen for WooCommerce events
				$(document.body).on('wc_fragments_refreshed', function() {
					setTimeout(debouncedUpdateCounters, 300);
				});
			});
		})(jQuery)
	</script>
	<?php
} );

add_action( 'woocommerce_after_shop_loop', function () {
	if ( ! is_product_category() ) {
		return;
	}
	$term = get_queried_object();
	$term_title = $term->name;
	$description = $term->description;


	?>
	<div class="ct-product-category-description-wrapper">
		<h4 class="ct-module-title"><?php echo $term_title; ?></h4>
		<div class="ct-product-category-description">
			<?php echo $description; ?>
		</div>
	</div>
	<?php
}, 9999 );
