<?php
/**
 * FiboSearch Custom Suggestions Layout
 * Add this code to your theme's functions.php or create as a custom plugin
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom JavaScript to work with existing heading structure
 */
add_action(
	'wp_footer',
	function () {
		if ( ! wp_script_is( 'jquery-dgwt-wcas', 'enqueued' ) ) {
			return;
		}
		?>
	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			console.log('FiboSearch Custom: Script loaded');

			// Helper: get the active search query from the visible input
			function getActiveSearchQuery() {
				var query = '';
				$('.dgwt-wcas-search-input').each(function() {
					if ($(this).val()) { query = $(this).val(); return false; }
				});
				return query;
			}

			// Helper: build the "SEE ALL PRODUCTS" link
			function buildViewAllLink(searchQuery, total) {
				var q = searchQuery || getActiveSearchQuery();
				var label = total ? 'SEE ALL ' + total + ' PRODUCTS' : 'SEE ALL PRODUCTS';
				return $('<a href="/?s=' + encodeURIComponent(q) + '&post_type=product" class="dgwt-wcas-view-all">' + label + ' &rarr;</a>');
			}

			// Function to process suggestions
			function processSuggestions(container) {
				console.log('FiboSearch Custom: Processing suggestions');
				console.log('FiboSearch Custom: Container HTML before:', container.html());

				// Check if we have headlines or just product suggestions
				var hasHeadlines = container.find('.js-dgwt-wcas-suggestion-headline').length > 0;
				var hasProducts = container.find('.dgwt-wcas-suggestion-product').length > 0;

				console.log('Has headlines:', hasHeadlines, 'Has products:', hasProducts);

				// If we have products but no headlines, create a products section
				if (hasProducts && !hasHeadlines) {
					console.log('Creating products section without headlines');
					createProductsOnlySection(container);
					return;
				}

				// Work with existing heading structure
				var currentSection = null;
				var $currentSectionWrapper = null;
				var $currentSectionContent = null;
				var productCount = 0;
				var searchQuery = getActiveSearchQuery();
				var $newContainer = $('<div>');
				var viewAllTotal = null;

				// Process each element in the container
				container.children().each(function () {
					var $element = $(this);

					console.log('Processing element:', $element.attr('class'));

					// Check if this is a headline
					if ($element.hasClass('js-dgwt-wcas-suggestion-headline')) {
						// Finalize previous section if exists
						if ($currentSectionWrapper && $currentSectionContent) {
							$currentSectionWrapper.append($currentSectionContent);
							$newContainer.append($currentSectionWrapper);
						}

						var headlineText = $element.find('.dgwt-wcas-st').text().toLowerCase();
						var sectionClass = '';
						var layout = 'list';

						console.log('Found headline:', headlineText);

						// Determine section type and layout
						if (headlineText.includes('categories') || headlineText.includes('category')) {
							sectionClass = 'categories';
							layout = 'list';
						} else if (headlineText.includes('products') || headlineText.includes('product')) {
							sectionClass = 'products';
							layout = 'grid';
						} else if (headlineText.includes('posts') || headlineText.includes('blog')) {
							sectionClass = 'blog';
							layout = 'list';
						} else if (headlineText.includes('pages') || headlineText.includes('page')) {
							sectionClass = 'pages';
							layout = 'list';
						} else {
							sectionClass = 'other';
							layout = 'list';
						}

						console.log('Creating section:', sectionClass, 'with layout:', layout);

						// Create new section wrapper
						$currentSectionWrapper = $('<div class="dgwt-wcas-suggestion-section dgwt-wcas-section-' + sectionClass + '">');

						// Create section header
						var $sectionHeader = $('<div class="dgwt-wcas-section-header">');
						var $sectionTitle = $('<h3 class="dgwt-wcas-section-title">' + $element.find('.dgwt-wcas-st').text() + '</h3>');
						$sectionHeader.append($sectionTitle);

						$currentSectionWrapper.append($sectionHeader);

						// Create section content
						$currentSectionContent = $('<div class="dgwt-wcas-section-content dgwt-wcas-layout-' + layout + '">');

						currentSection = sectionClass;
						productCount = 0;

					} else if ($element.hasClass('dgwt-wcas-suggestion') && !$element.hasClass('js-dgwt-wcas-suggestion-more')) {
						// This is a regular suggestion (not the "See all products" link)
						if ($currentSectionContent) {
							// For products, limit to 4 and add view all link
							if (currentSection === 'products') {
								productCount++;
								if (productCount <= 4) {
									$currentSectionContent.append($element.clone());
								}
							} else {
								// For other sections, add all suggestions
								$currentSectionContent.append($element.clone());
							}
						}

					} else if ($element.hasClass('js-dgwt-wcas-suggestion-more')) {
						// Extract total count from "See all products" element
						if (currentSection === 'products') {
							var moreText = $element.find('.dgwt-wcas-st-more-total').text();
							var totalMatch = moreText.match(/\((\d+)\)/);
							viewAllTotal = totalMatch ? totalMatch[1] : null;
						}
					}
				});

				// Finalize last section
				if ($currentSectionWrapper && $currentSectionContent) {
					$currentSectionWrapper.append($currentSectionContent);
					$newContainer.append($currentSectionWrapper);
				}

				// Always add "SEE ALL PRODUCTS" link after products section
				var $productsSection = $newContainer.find('.dgwt-wcas-section-products');
				if ($productsSection.length > 0) {
					$productsSection.append(buildViewAllLink(searchQuery, viewAllTotal));
				}

				// Reorder sections: Categories → Products → Blog/Pages/Other
				var $orderedContainer = $('<div>');
				var sectionOrder = ['categories', 'products', 'blog', 'pages', 'other'];
				sectionOrder.forEach(function(sectionName) {
					$newContainer.find('.dgwt-wcas-section-' + sectionName).each(function() {
						$orderedContainer.append($(this));
					});
				});

				// Replace container content
				container.html($orderedContainer.html());

				console.log('FiboSearch Custom: Container HTML after:', container.html());
			}

			// Function to handle products-only suggestions (no headlines)
			function createProductsOnlySection(container) {
				console.log('Creating products-only section');

				var searchQuery = getActiveSearchQuery();
				var $productSuggestions = container.find('.dgwt-wcas-suggestion-product');
				var $moreSuggestion = container.find('.js-dgwt-wcas-suggestion-more');

				console.log('Found', $productSuggestions.length, 'product suggestions');

				// Create products section wrapper
				var $sectionWrapper = $('<div class="dgwt-wcas-suggestion-section dgwt-wcas-section-products">');

				// Create section header
				var $sectionHeader = $('<div class="dgwt-wcas-section-header">');
				var $sectionTitle = $('<h3 class="dgwt-wcas-section-title">Products</h3>');
				$sectionHeader.append($sectionTitle);
				$sectionWrapper.append($sectionHeader);

				// Create section content with grid layout
				var $sectionContent = $('<div class="dgwt-wcas-section-content dgwt-wcas-layout-grid">');

				// Add up to 4 product suggestions
				var productCount = 0;
				$productSuggestions.each(function () {
					if (productCount < 4) {
						$sectionContent.append($(this).clone());
						productCount++;
					}
				});

				$sectionWrapper.append($sectionContent);

				// Always add "SEE ALL PRODUCTS" link — extract total if available
				var viewAllTotal = null;
				if ($moreSuggestion.length > 0) {
					var moreText = $moreSuggestion.find('.dgwt-wcas-st-more-total').text();
					var totalMatch = moreText.match(/\((\d+)\)/);
					viewAllTotal = totalMatch ? totalMatch[1] : null;
				}
				$sectionWrapper.append(buildViewAllLink(searchQuery, viewAllTotal));

				// Replace container content
				container.html($sectionWrapper);

				console.log('Products-only section created');
			}



			// Use MutationObserver to watch for suggestions container changes
			var observer = new MutationObserver(function (mutations) {
				mutations.forEach(function (mutation) {
					if (mutation.type === 'childList') {
						var $target = $(mutation.target);

						// Check if this is the suggestions wrapper and has content
						if ($target.hasClass('dgwt-wcas-suggestions-wrapp')) {
							var hasHeadlines = $target.find('.js-dgwt-wcas-suggestion-headline').length > 0;
							var hasProducts = $target.find('.dgwt-wcas-suggestion-product').length > 0;
							var hasProcessedStructure = $target.find('.dgwt-wcas-suggestion-section').length > 0;

							console.log('FiboSearch Custom: MutationObserver detected changes');
							console.log('Has headlines:', hasHeadlines, 'Has products:', hasProducts, 'Already processed:', hasProcessedStructure);

							// Only process if we have content and haven't already processed it
							if ((hasHeadlines || hasProducts) && !hasProcessedStructure) {
								console.log('FiboSearch Custom: Processing suggestions via MutationObserver');
								setTimeout(function () {
									processSuggestions($target);
								}, 10);
							}
						}
					}
				});
			});

			// Start observing
			setTimeout(function () {
				var suggestionsContainer = document.querySelector('.dgwt-wcas-suggestions-wrapp');
				if (suggestionsContainer) {
					console.log('FiboSearch Custom: Starting MutationObserver');
					observer.observe(suggestionsContainer, {
						childList: true,
						subtree: true
					});
				}
			}, 1000);

			// Additional fallback: Check for suggestions periodically and on search events
			function checkAndProcessSuggestions() {
				var $container = $('.dgwt-wcas-suggestions-wrapp');
				if ($container.length > 0 && $container.is(':visible')) {
					var hasHeadlines = $container.find('.js-dgwt-wcas-suggestion-headline').length > 0;
					var hasProducts = $container.find('.dgwt-wcas-suggestion-product').length > 0;
					var hasProcessedStructure = $container.find('.dgwt-wcas-suggestion-section').length > 0;

					if ((hasHeadlines || hasProducts) && !hasProcessedStructure) {
						console.log('FiboSearch Custom: Fallback processing triggered');
						processSuggestions($container);
					}
				}
			}

			// Bind to search input events as fallback
			$(document).on('input keyup', '.dgwt-wcas-search-input', function () {
				setTimeout(checkAndProcessSuggestions, 100);
			});

			// Also check when suggestions become visible
			$(document).on('DOMNodeInserted', '.dgwt-wcas-suggestions-wrapp', function () {
				setTimeout(checkAndProcessSuggestions, 50);
			});

		});
	</script>
	<?php
	},
	999
);

/**
 * Override FiboSearch product image size to use WooCommerce thumbnail size.
 *
 * This filter forces FiboSearch to use WooCommerce's thumbnail size (400x400)
 * instead of the default 64x64 size for better image quality in search results.
 *
 * Safety checks:
 * - Verifies FiboSearch plugin is active
 * - Verifies WooCommerce is active
 * - Verifies WooCommerce thumbnail size exists
 * - Gracefully degrades if any dependency is missing
 *
 * @since 1.0.0
 */
add_action(
	'plugins_loaded',
	function () {
		// Safety check: Ensure FiboSearch plugin is active
		// DGWT_WCAS is the main FiboSearch class
		if ( ! class_exists( 'DGWT_WCAS' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'BlazeCommerce: FiboSearch image size override skipped - FiboSearch plugin not active' );
			}
			return;
		}

		// Safety check: Ensure WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'BlazeCommerce: FiboSearch image size override skipped - WooCommerce not active' );
			}
			return;
		}

		// Safety check: Verify WooCommerce thumbnail size exists
		$thumbnail_size = wc_get_image_size( 'woocommerce_thumbnail' );
		if ( empty( $thumbnail_size ) || ! isset( $thumbnail_size['width'] ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'BlazeCommerce: FiboSearch image size override skipped - WooCommerce thumbnail size not found' );
			}
			return;
		}

		/**
		 * Override FiboSearch to use larger product images.
		 * Forces FiboSearch to use WooCommerce thumbnail size instead of 64x64 default.
		 *
		 * @param string $size The image size to use.
		 * @return string The WooCommerce thumbnail size identifier.
		 */
		add_filter(
			'dgwt/wcas/setup/thumbnail_size',
			function ( $size ) {
				return 'woocommerce_thumbnail'; // Uses WooCommerce thumbnail size (400x400)
			},
			10,
			1
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'BlazeCommerce: FiboSearch image size override applied successfully' );
		}
	},
	20 // Priority 20 to ensure plugins are fully loaded
);

/**
 * Override FiboSearch product image to use full-size image URL.
 *
 * This filter modifies the product data during indexing to use
 * the full-size image instead of the default smaller version,
 * ensuring higher quality images in search suggestions.
 *
 * @param array      $data       The product data array.
 * @param int        $product_id The product ID.
 * @param WC_Product $product    The product object.
 * @return array Modified product data with full-size image URL.
 * @since 1.0.0
 */
add_filter(
	'dgwt/wcas/tnt/indexer/readable/product/data',
	function ( $data, $product_id, $product ) {
		$thumbnail_url = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'full' );
		if ( is_array( $thumbnail_url ) && ! empty( $thumbnail_url[0] ) ) {
			$data['image'] = $thumbnail_url[0];
		}
		return $data;
	},
	10,
	3
);
