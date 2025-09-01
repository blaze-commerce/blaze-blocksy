<?php

function blaze_blocksy_ajax_get_states( $country_code ) {
	$countries = new WC_Countries();
	$states = $countries->get_states( $country_code );
	wp_send_json_success( $states );
}

add_action( 'wp_ajax_blaze_blocksy_get_states', 'blaze_blocksy_ajax_get_states' );
add_action( 'wp_ajax_nopriv_blaze_blocksy_get_states', 'blaze_blocksy_ajax_get_states' );
