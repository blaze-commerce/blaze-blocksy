<?php
$countries = new WC_Countries();

// get all available countries
$available_countries = $countries->get_countries();
?>

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
	<label for="city">Town/City</label>
	<select id="city-options'>
		<option value="">City</option>
		<option value=" new-york">New York</option>
		<option value="los-angeles">Los Angeles</option>
		<option value="chicago">Chicago</option>
		<option value="houston">Houston</option>
		<option value="jakarta">Jakarta</option>
	</select>
</div>

<div class="form-group">
	<label for="postcode">Postcode/Zip</label>
	<input type="text" id="postcode" placeholder="Enter postcode/zip">
</div>

<button class="calculate-btn">CALCULATE SHIPPING</button>
<script>
	jQuery(document).ready(function ($) {
		$('#country-options').change(function () {
			var selectedCountry = $(this).val();
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'blaze_blocksy_get_states',
					country_code: selectedCountry
				},
				success: function (response) {
					var states = response.data;
					var statesSelect = $('#city-options');
					statesSelect.empty();
					$.each(states, function (key, value) {
						statesSelect.append('<option value="' + key + '">' + value + '</option>');
					});
				}
			});
		});
	});
</script>