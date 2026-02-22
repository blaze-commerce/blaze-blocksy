<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add review_submitted query param to the redirect URL after a product review is posted.
 * This lets the product page detect and display a success notice on reload.
 *
 * @param string     $location Redirect URL.
 * @param WP_Comment $comment  The comment object.
 * @return string Modified redirect URL.
 */
add_filter( 'comment_post_redirect', function ( $location, $comment ) {
	if ( $comment->comment_type !== 'review' ) {
		return $location;
	}

	$status = ( '1' === $comment->comment_approved ) ? 'approved' : 'hold';
	$location = add_query_arg( 'review_submitted', $status, $location );

	return $location;
}, 10, 2 );

/**
 * Pass review submission status to the single-product JS object so the front-end
 * can display an appropriate success or moderation notice.
 */
add_filter( 'blaze_blocksy_single_product_localize_data', function ( $data ) {
	if ( ! is_product() ) {
		return $data;
	}

	$review_submitted = isset( $_GET['review_submitted'] ) ? sanitize_text_field( wp_unslash( $_GET['review_submitted'] ) ) : '';

	// Fallback: WordPress adds unapproved + moderation-hash params for moderated comments.
	if ( ! $review_submitted && isset( $_GET['unapproved'], $_GET['moderation-hash'] ) ) {
		$review_submitted = 'hold';
	}

	if ( $review_submitted && in_array( $review_submitted, array( 'approved', 'hold' ), true ) ) {
		$data['reviewSubmitted'] = $review_submitted;
	}

	return $data;
} );
