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



			// Approach 2: Use MutationObserver to watch for suggestions container changes
			var observer = new MutationObserver(function (mutations) {
				mutations.forEach(function (mutation) {
					if (mutation.type === 'childList') {
						var $target = $(mutation.target);
						if ($target.hasClass('dgwt-wcas-suggestions-wrapp') && $target.find('.js-dgwt-wcas-suggestion-headline').length > 0) {
							console.log('FiboSearch Custom: MutationObserver detected suggestions');
							setTimeout(function () {
								processSuggestions($target);
							}, 10);
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



		});
	</script>
		<?php
	},
	999
);

