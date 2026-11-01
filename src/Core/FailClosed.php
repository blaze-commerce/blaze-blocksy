<?php
namespace Blaze\Child\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Refuses orders while site code or a critical module is broken (spec 4.5). The catalogue stays browsable.
 */
final class FailClosed {

	const MAIL_TRANSIENT = 'blaze_child_failclosed_mail';
	const MAIL_INTERVAL  = 3600;
	const ERROR_CODE     = 'blaze_child_checkout_unavailable';

	/**
	 * Store API checkout routes, any namespace version (WordPress matches routes case-insensitively).
	 */
	const STORE_API_CHECKOUT = '#^/+wc/store(?:/v\d+)?/checkout#i';

	/**
	 * @var bool
	 */
	private static $armed = false;

	/**
	 * Closes checkout for this request and alerts the site admin (at most once per hour). Idempotent.
	 *
	 * @param string $reason Why checkout is closed (e.g. 'site_code_failed').
	 * @return void
	 */
	public static function arm( string $reason ): void {
		if ( self::$armed ) {
			return;
		}
		self::$armed = true;

		add_action( 'woocommerce_checkout_process', [ self::class, 'block_classic_checkout' ] );
		add_filter( 'rest_pre_dispatch', [ self::class, 'block_store_api' ], 10, 3 );

		Health::record( 'checkout_closed', "Checkout is refusing orders: {$reason}", [ 'reason' => $reason ] );
		self::alert( $reason );
	}

	/**
	 * @return bool
	 */
	public static function is_armed(): bool {
		return self::$armed;
	}

	/**
	 * Hooked on woocommerce_checkout_process: an error notice stops WooCommerce creating the order.
	 *
	 * @return void
	 */
	public static function block_classic_checkout(): void {
		wc_add_notice( self::message(), 'error' );
	}

	/**
	 * Hooked on rest_pre_dispatch: refuses POST to the Store API checkout; every other request is untouched.
	 *
	 * @param mixed  $result  Earlier short-circuit value (null when none).
	 * @param mixed  $server  WP_REST_Server.
	 * @param mixed  $request WP_REST_Request.
	 * @return mixed
	 */
	public static function block_store_api( $result, $server, $request ) {
		if ( null !== $result || ! $request instanceof \WP_REST_Request ) {
			return $result;
		}
		if ( 'POST' !== strtoupper( (string) $request->get_method() ) ) {
			return $result;
		}
		if ( 1 !== preg_match( self::STORE_API_CHECKOUT, (string) $request->get_route() ) ) {
			return $result;
		}
		return new \WP_Error( self::ERROR_CODE, self::message(), [ 'status' => 503 ] );
	}

	/**
	 * @return string
	 */
	private static function message(): string {
		return __( 'Checkout is temporarily unavailable. Please try again shortly.', 'blocksy-child' );
	}

	/**
	 * Mails admin_email, throttled by a one-hour transient (set before sending so a slow mailer can't repeat it).
	 *
	 * @param string $reason Why checkout is closed.
	 * @return void
	 */
	private static function alert( string $reason ): void {
		if ( false !== get_transient( self::MAIL_TRANSIENT ) ) {
			return;
		}
		set_transient( self::MAIL_TRANSIENT, time(), self::MAIL_INTERVAL );

		$to = get_option( 'admin_email' );
		if ( ! is_string( $to ) || '' === $to ) {
			return;
		}
		$site = (string) get_bloginfo( 'name' );
		wp_mail(
			$to,
			sprintf( '[%s] Checkout is closed: site code failed', $site ),
			"Checkout on {$site} is refusing orders because the site's code failed to load ({$reason}).\n\n"
			. "The catalogue is still browsable. Details are under the Blaze child theme notice in wp-admin.\n"
			. 'This alert is sent at most once an hour while the problem lasts.'
		);
	}
}
