<?php
// v1 code: client names, closures, IDs and jquery are exempt here.
add_action( 'wp_footer', function () {
	if ( is_page( 4821 ) ) {
		echo '<p>AcmeClient Shop</p>';
	}
	wp_enqueue_script( 'x', '/x.js', [ 'jquery' ] );
} );

if ( ! function_exists( 'bc_old_helper' ) ) {
	function bc_old_helper() {
		return 1;
	}
}
