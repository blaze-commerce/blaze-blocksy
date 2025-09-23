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
				var searchQuery = $('.dgwt-wcas-search-input').val() || '';
				var $newContainer = $('<div>');
				var $pendingViewAllLink = null;

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
						// Handle "See all products" element
						if (currentSection === 'products') {
							var moreText = $element.find('.dgwt-wcas-st-more-total').text();
							var totalMatch = moreText.match(/\((\d+)\)/);
							var total = totalMatch ? totalMatch[1] : '46';

							$pendingViewAllLink = $('<a href="/?s=' + encodeURIComponent(searchQuery) + '&post_type=product" class="dgwt-wcas-view-all">VIEW ALL ' + total + ' PRODUCTS →</a>');
						}
					}
				});

				// Finalize last section
				if ($currentSectionWrapper && $currentSectionContent) {
					$currentSectionWrapper.append($currentSectionContent);
					if ($pendingViewAllLink && currentSection === 'products') {
						$currentSectionWrapper.append($pendingViewAllLink);
					}
					$newContainer.append($currentSectionWrapper);
				}

				// Replace container content
				container.html($newContainer.html());

				console.log('FiboSearch Custom: Container HTML after:', container.html());
			}

			// Function to handle products-only suggestions (no headlines)
			function createProductsOnlySection(container) {
				console.log('Creating products-only section');

				var searchQuery = $('.dgwt-wcas-search-input').val() || '';
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

				// Handle "See all products" link if present
				if ($moreSuggestion.length > 0) {
					var moreText = $moreSuggestion.find('.dgwt-wcas-st-more-total').text();
					var totalMatch = moreText.match(/\((\d+)\)/);
					var total = totalMatch ? totalMatch[1] : $productSuggestions.length;

					var $viewAllLink = $('<a href="/?s=' + encodeURIComponent(searchQuery) + '&post_type=product" class="dgwt-wcas-view-all">VIEW ALL ' + total + ' PRODUCTS →</a>');
					$sectionWrapper.append($viewAllLink);
				}

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

