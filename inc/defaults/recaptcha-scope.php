<?php
/**
 * Default `recaptcha-scope` — load Contact Form 7's reCAPTCHA only where a
 * form renders.
 *
 * CF7 enqueues Google reCAPTCHA (and its badge) on every page. This
 * dequeues it everywhere, then re-enqueues it whenever a form renders
 * (`wpcf7_form_elements`), so forms in widgets, templates or blocks keep
 * their protection. Footer scripts, so a late enqueue still prints.
 *
 * Dequeue only, never deregister: the re-enqueue needs the handle to stay
 * registered (inc/perf/dequeue-assets.php deregisters, so it can't do this).
 *
 * Caveat: a site that hides the badge must show Google's reCAPTCHA notice
 * text on its forms instead.
 *
 * PROMOTED FROM: bulkwholesale custom/includes/bw-core.php.
 *
 * @package Blocksy_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	if ( is_singular() ) {
		$post = get_post();
		if ( $post && ( has_shortcode( $post->post_content, 'contact-form-7' ) || has_block( 'contact-form-7/contact-form-selector', $post ) ) ) {
			return;
		}
	}
	wp_dequeue_script( 'wpcf7-recaptcha' );
}, 30 );

add_filter( 'wpcf7_form_elements', function ( $html ) {
	if ( wp_script_is( 'wpcf7-recaptcha', 'registered' ) ) {
		wp_enqueue_script( 'wpcf7-recaptcha' );
	}
	return $html;
} );
