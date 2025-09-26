<?php
$countries = new WC_Countries();

// get all available countries
$available_countries = $countries->get_countries();
?>

<div id="shipping-calculator-container" class="shipping-calculator-container">
	<div class="form-group">
		<label for="country">Country</label>
		<select id="country-options">
			<option value="">Country</option>
			<?php foreach ( $available_countries as $country_code => $country_name ) { ?>
				<option value="<?php echo esc_attr( $country_code ); ?>"><?php echo esc_html( $country_name ); ?></option>
			<?php } ?>
		</select>
	</div>

	<div class="form-group">
		<label for="state">State/Province</label>
		<select id="state-options">
			<option value="">Select State</option>
		</select>
	</div>

	<div class="form-group">
		<label for="postcode">Postcode/Zip</label>
		<input type="text" id="postcode" placeholder="Enter postcode/zip">
	</div>

	<button class="calculate-btn" id="calculate-shipping-btn">CALCULATE SHIPPING</button>

	<!-- Shipping Results Area -->
	<div id="shipping-results" class="shipping-results" style="display: none;">
		<h4>Available Shipping Methods</h4>
		<div id="shipping-methods-list"></div>
	</div>


</div> <!-- End shipping-calculator-container -->
<script>
	jQuery(document).ready(function ($) {

		// Check if blockUI is available (from WooCommerce)
		var hasBlockUI = typeof $.blockUI !== 'undefined';

		// Custom blockUI function for shipping calculator
		function blockShippingCalculator(message) {
			if (hasBlockUI) {
				$('#shipping-calculator-container').block({
					message: message || 'Processing...',
					css: {
						border: 'none',
						padding: '15px',
						backgroundColor: '#000',
						'-webkit-border-radius': '10px',
						'-moz-border-radius': '10px',
						opacity: 0.5,
						color: '#fff'
					},
					overlayCSS: {
						backgroundColor: '#fff',
						opacity: 0.6
					}
				});
			} else {
				// Fallback: simple loading overlay
				var $container = $('#shipping-calculator-container');
				if ($container.find('.shipping-loading-overlay').length === 0) {
					$container.css('position', 'relative').append(
						'<div class="shipping-loading-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 999;">' +
						'<div style="background: #000; color: #fff; padding: 15px; border-radius: 10px; opacity: 0.9;">' + (message || 'Processing...') + '</div>' +
						'</div>'
					);
				}
			}
		}

		// Custom unblock function
		function unblockShippingCalculator() {
			if (hasBlockUI) {
				$('#shipping-calculator-container').unblock();
			} else {
				$('#shipping-calculator-container .shipping-loading-overlay').remove();
			}
		}
		$('#country-options').change(function () {
			var selectedCountry = $(this).val();
			var statesSelect = $('#state-options');

			// Clear states dropdown
			statesSelect.empty().append('<option value="">Select State</option>');

			// If no country selected, return
			if (!selectedCountry) {
				return;
			}

			// Block UI while loading states
			blockShippingCalculator('Loading states...');

			// Show loading state in dropdown as well
			statesSelect.append('<option value="">Loading states...</option>');

			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'blaze_blocksy_get_states',
					country_code: selectedCountry
				},
				success: function (response) {
					// Unblock UI
					unblockShippingCalculator();

					// Clear loading state
					statesSelect.empty().append('<option value="">Select State</option>');

					if (response.success && response.data) {
						var states = response.data;

						// Check if states object has any properties
						if (Object.keys(states).length > 0) {
							$.each(states, function (key, value) {
								statesSelect.append('<option value="' + key + '">' + value + '</option>');
							});
						} else {
							// No states available for this country
							statesSelect.append('<option value="">No states available</option>');
						}
					} else {
						// Error occurred
						statesSelect.append('<option value="">Error loading states</option>');
						console.error('Error loading states:', response.data ? response.data.message : 'Unknown error');
					}
				},
				error: function (xhr, status, error) {
					// Unblock UI
					unblockShippingCalculator();

					// Clear loading state and show error
					statesSelect.empty().append('<option value="">Select State</option>');
					statesSelect.append('<option value="">Error loading states</option>');
					console.error('AJAX Error:', error);
				}
			});
		});

		// Handle Calculate Shipping button click
		$('#calculate-shipping-btn').click(function (e) {
			e.preventDefault();

			var country = $('#country-options').val();
			var state = $('#state-options').val();
			var postcode = $('#postcode').val();
			var $button = $(this);
			var $results = $('#shipping-results');
			var $methodsList = $('#shipping-methods-list');

			// Validation
			if (!country) {
				alert('Please select a country');
				return;
			}

			if (!state) {
				alert('Please select a state/province');
				return;
			}

			// Show loading state
			$button.prop('disabled', true).text('CALCULATING...');
			$results.hide();
			$methodsList.empty();

			// Block UI while calculating shipping
			blockShippingCalculator('Calculating shipping methods...');

			// Get current product ID if available
			var productId = 0;
			<?php
			global $product;
			if ( $product ) {
				echo 'productId = ' . $product->get_id() . ';';
			}
			?>

			// Prepare data for shipping calculation
			var shippingData = {
				action: 'calculate_shipping_methods',
				country: country,
				state: state,
				postcode: postcode || '',
				product_id: productId,
				nonce: '<?php echo wp_create_nonce( 'shipping_calculator_nonce' ); ?>'
			};

			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: shippingData,
				success: function (response) {
					// Unblock UI
					unblockShippingCalculator();

					if (response.success && response.data) {
						displayShippingMethods(response.data);
						$results.show();
					} else {
						var errorMessage = response.data && response.data.message ?
							response.data.message : 'Unable to calculate shipping methods';
						$methodsList.html('<div class="shipping-error">Error: ' + errorMessage + '</div>');
						$results.show();
					}
				},
				error: function (xhr, status, error) {
					// Unblock UI
					unblockShippingCalculator();

					console.error('Shipping calculation error:', error);
					$methodsList.html('<div class="shipping-error">Error calculating shipping. Please try again.</div>');
					$results.show();
				},
				complete: function () {
					// Reset button state
					$button.prop('disabled', false).text('CALCULATE SHIPPING');
				}
			});
		});

		// Function to display shipping methods
		function displayShippingMethods(methods) {
			var $methodsList = $('#shipping-methods-list');
			$methodsList.empty();

			if (!methods || methods.length === 0) {
				$methodsList.html('<div class="no-shipping">No shipping methods available for this location.</div>');
				return;
			}

			var methodsHtml = '<div class="shipping-methods">';

			$.each(methods, function (index, method) {
				var cost = method.cost ? method.cost : 'Free';
				var description = method.description ? method.description : '';

				methodsHtml += '<div class="shipping-method">';
				methodsHtml += '<div class="method-info">';
				methodsHtml += '<span class="method-title">' + method.title + '</span>';
				if (description) {
					methodsHtml += '<span class="method-description">' + description + '</span>';
				}
				methodsHtml += '</div>';
				methodsHtml += '<div class="method-cost">' + cost + '</div>';
				methodsHtml += '</div>';
			});

			methodsHtml += '</div>';
			$methodsList.html(methodsHtml);
		}
	});
</script>