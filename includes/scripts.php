<?php

// Ensure is_plugin_active() function is available
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

add_action( 'wp_enqueue_scripts', function () {

	$template_uri = get_stylesheet_directory_uri();
	wp_enqueue_style( 'parent-style', $template_uri . '/style.css' );

	// Enqueue child style
	wp_enqueue_style(
		'blocksy-child-search-style',
		$template_uri . '/assets/css/search.css',
		array( 'parent-style' )
	);

	// Enqueue checkout script and styles only on checkout page and when FluidCheckout is active
	if ( is_checkout() && ! is_wc_endpoint_url() ) {
		// Check if FluidCheckout plugin is active
		if ( is_plugin_active( 'fluid-checkout/fluid-checkout.php' ) ) {
			// Enqueue checkout CSS
			wp_enqueue_style(
				'blocksy-child-checkout-css',
				$template_uri . '/assets/css/checkout.css',
				array( 'parent-style' ),
				'1.0.0'
			);

			// Enqueue checkout JavaScript
			wp_enqueue_script(
				'blocksy-child-checkout-js',
				$template_uri . '/assets/js/checkout.js',
				array( 'jquery' ),
				'1.0.0',
				true
			);
		} else {
			// FluidCheckout is not active - add admin notice for administrators
			if ( current_user_can( 'manage_options' ) ) {
				add_action( 'admin_notices', 'blocksy_child_fluidcheckout_missing_notice' );
			}
		}
	}

} );

/**
 * Admin notice when FluidCheckout plugin is not active but checkout customizations are expected
 */
function blocksy_child_fluidcheckout_missing_notice() {
	// Only show on checkout page or admin dashboard
	if ( ! is_checkout() && ! is_admin() ) {
		return;
	}

	// Check if notice has been dismissed (optional - can be enhanced later)
	$notice_dismissed = get_user_meta( get_current_user_id(), 'blocksy_child_fluidcheckout_notice_dismissed', true );
	if ( $notice_dismissed ) {
		return;
	}

	?>
	<div class="notice notice-warning is-dismissible" data-notice="fluidcheckout-missing">
		<p>
			<strong>Checkout Customizations Notice:</strong>
			The checkout page customizations require the <strong>FluidCheckout</strong> plugin to be installed and activated.
			<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=fluid+checkout&tab=search&type=term' ) ); ?>">Install FluidCheckout</a>
			or <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">activate it</a> to enable the enhanced checkout experience.
		</p>
	</div>
	<script>
	jQuery(document).ready(function($) {
		$(document).on('click', '[data-notice="fluidcheckout-missing"] .notice-dismiss', function() {
			$.post(ajaxurl, {
				action: 'dismiss_fluidcheckout_notice',
				nonce: '<?php echo wp_create_nonce( 'dismiss_fluidcheckout_notice' ); ?>'
			});
		});
	});
	</script>
	<?php
}

/**
 * AJAX handler for dismissing the FluidCheckout notice
 */
add_action( 'wp_ajax_dismiss_fluidcheckout_notice', 'handle_dismiss_fluidcheckout_notice' );
function handle_dismiss_fluidcheckout_notice() {
	check_ajax_referer( 'dismiss_fluidcheckout_notice', 'nonce' );

	if ( current_user_can( 'manage_options' ) ) {
		update_user_meta( get_current_user_id(), 'blocksy_child_fluidcheckout_notice_dismissed', true );
		wp_send_json_success();
	} else {
		wp_send_json_error();
	}
}
