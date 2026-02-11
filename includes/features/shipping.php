<?php
/**
 * This adds an endpoint for getting available shipping methods based on cart data
 * POST /wp-json/wooless-wc/v1/available-shipping-methods
 * Request Params
 * {
 *  "products": [
 *      {
 *          "id": int required - the product id you want to add to cart.
 *          "quantity": int required - the number of products you want to add to cart.
 *          "variation": object optional - the variation object, this is only used for products that has variants
 *          {
 *              "id": int requried - the parent id of the product that has variant
 *              // the following is dynamic, depending on how many attributes that the variant product requires
 *              // example. pa_size and measurement attributes is required, then we will add the following
 *              "attribute_pa_size": string
 *              "attribute_measurement": string
 *              // and so on...
 *          }
 *      }
 *  ],
 * "country": string required. The Alpha‑2 code of the country. Example AU, US, NZ.
 * "state": string required. The code of the state. Example: ACT, NSW, QLD.
 * "post_code": string optional. The postal code.
 * }
 * 
 * Response
 * This returns an object of shipping rates based on the product data and address passed to the request
 * {
 *  "flat_rate:3": {
 *      "id": string - the id of the shipping rate
 *      "method_id": string - the method id of the shipping rate
 *      "instance_id": int - the instance id of the shipping rate
 *      "label": string - the label of the shipping rate
 *      "cost": string - the cost of the shipping rate
 *      "taxes": array of objects - applicable for shipping that has taxes
 *  }
 * }
 */

namespace BlazeBlocksy;

class CalculateShipping {
	private static $instance = null;

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_rest_endpoints' ) );
		add_action( 'wp_ajax_blaze_blocksy_get_states', array( $this, 'ajax_get_states' ) );
		add_action( 'wp_ajax_nopriv_blaze_blocksy_get_states', array( $this, 'ajax_get_states' ) );
		add_action( 'wp_ajax_calculate_shipping_methods', array( $this, 'ajax_calculate_shipping_methods' ) );
		add_action( 'wp_ajax_nopriv_calculate_shipping_methods', array( $this, 'ajax_calculate_shipping_methods' ) );
	}

	public function register_rest_endpoints() {
		register_rest_route(
			'wooless-wc/v1',
			'/available-shipping-methods',
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'get_available_shipping_methods_callback' ),
				'args' => array(
					'products' => array(
						'required' => true,
					),
					'country' => array(
						'required' => true,
					),
					'state' => array(
						'required' => true,
					),
					'post_code' => array(
						'required' => false,
					),
				),
			)
		);
	}

	public function get_available_shipping_methods_callback( \WP_REST_Request $request ) {
		$products = $request->get_param( 'products' );
		$country = $request->get_param( 'country' );
		$state = $request->get_param( 'state' );
		$post_code = $request->get_param( 'post_code' );
		$json_response = array();

		if ( ! class_exists( 'WooCommerce' ) ) {
			$response = new \WP_REST_Response( 'Error: Woocommerce is not active!' );
			$response->set_status( 400 );
			return;
		}

		require_once WC_ABSPATH . 'includes/wc-notice-functions.php';
		require_once WC_ABSPATH . 'includes/class-wc-customer.php';
		require_once WC_ABSPATH . 'includes/abstracts/abstract-wc-session.php';
		require_once WC_ABSPATH . 'includes/class-wc-cart-session.php';
		require_once WC_ABSPATH . 'includes/wc-cart-functions.php';
		require_once WC_ABSPATH . 'includes/class-wc-cart.php';
		require_once WC_ABSPATH . 'includes/class-wc-shipping.php';
		require_once WC_ABSPATH . 'includes/class-wc-customer.php';

		// Ensure WC session and cart are initialized
		if ( ! WC()->session ) {
			WC()->session = new \WC_Session_Handler();
			WC()->session->init();
		}
		if ( ! WC()->customer ) {
			WC()->customer = new \WC_Customer();
		}
		if ( ! WC()->cart ) {
			WC()->cart = new \WC_Cart();
		}

		// Save the current cart contents
		$saved_cart_contents = WC()->cart->get_cart_contents();

		try {
			// Empty the cart for shipping calculation (without clearing session/cookies)
			WC()->cart->empty_cart( false );

			// Add products from the request to the cart
			foreach ( $products as $product ) {
				$variation_id = 0;
				$variation_data = array();
				if ( isset( $product['variation'] ) ) {
					$variation_id = $product['variation']['id'];
					unset( $product['variation']['id'] );
					$variation_data = $product['variation'];
				}
				WC()->cart->add_to_cart( $product['id'], $product['quantity'], $variation_id, $variation_data );
			}

			// Set shipping destination
			WC()->customer->set_shipping_country( $country );
			WC()->customer->set_shipping_state( $state );
			WC()->customer->set_shipping_postcode( $post_code );

			// Calculate shipping
			$packages = WC()->cart->get_shipping_packages();
			$shipping_methods = WC()->shipping()->calculate_shipping( $packages );

			$available_methods = $shipping_methods[0];

			$rates = array_map( function ( \WC_Shipping_Rate $rate ) {
				return array(
					'id' => $rate->id,
					'method_id' => $rate->method_id,
					'instance_id' => $rate->instance_id,
					'label' => $rate->label,
					'cost' => $rate->cost,
					'taxes' => $rate->taxes,
				);
			}, $available_methods['rates'] );

			$subtotal = WC()->cart->subtotal;
		} finally {
			// Restore the original cart contents
			WC()->cart->empty_cart( false );
			foreach ( $saved_cart_contents as $cart_item_key => $cart_item ) {
				WC()->cart->add_to_cart(
					$cart_item['product_id'],
					$cart_item['quantity'],
					isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0,
					isset( $cart_item['variation'] ) ? $cart_item['variation'] : array()
				);
			}
			WC()->cart->calculate_totals();
		}

		$json_response['subtotal'] = $subtotal;
		$json_response['rates'] = $rates;

		$response = new \WP_REST_Response( $json_response );

		// Add a custom status code
		$response->set_status( 201 );

		return $response;
	}

	function ajax_get_states() {
		// Verify nonce for security (optional but recommended)
		// if ( ! wp_verify_nonce( $_POST['nonce'], 'shipping_calculator_nonce' ) ) {
		//     wp_send_json_error( array( 'message' => 'Security check failed' ) );
		//     return;
		// }

		// Get country code from POST data
		$country_code = isset( $_POST['country_code'] ) ? sanitize_text_field( $_POST['country_code'] ) : '';

		// Validate country code
		if ( empty( $country_code ) ) {
			wp_send_json_error( array( 'message' => 'Country code is required' ) );
			return;
		}

		// Check if WooCommerce is active
		if ( ! class_exists( 'WC_Countries' ) ) {
			wp_send_json_error( array( 'message' => 'WooCommerce is not active' ) );
			return;
		}

		try {
			$countries = new \WC_Countries();
			$states = $countries->get_states( $country_code );

			// If no states found, return empty array instead of false
			if ( ! $states ) {
				$states = array();
			}

			wp_send_json_success( $states );
		} catch (\Exception $e) {
			wp_send_json_error( array( 'message' => 'Error retrieving states: ' . $e->getMessage() ) );
		}
	}

	/**
	 * AJAX handler for calculating shipping methods
	 */
	function ajax_calculate_shipping_methods() {
		// Verify nonce for security
		if ( ! wp_verify_nonce( $_POST['nonce'], 'shipping_calculator_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed' ) );
			return;
		}

		// Get form data
		$country = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '';
		$state = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
		$postcode = isset( $_POST['postcode'] ) ? sanitize_text_field( $_POST['postcode'] ) : '';
		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;

		// Validate required fields
		if ( empty( $country ) ) {
			wp_send_json_error( array( 'message' => 'Country is required' ) );
			return;
		}

		if ( empty( $state ) ) {
			wp_send_json_error( array( 'message' => 'State/Province is required' ) );
			return;
		}

		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_send_json_error( array( 'message' => 'WooCommerce is not active' ) );
			return;
		}

		try {
			// Get current product if we're on a product page
			global $product;
			if ( ! $product && $product_id ) {
				$product = wc_get_product( $product_id );
			}

			// Prepare products array for the REST API call
			$products = array();
			if ( $product ) {
				$products[] = array(
					'id' => $product->get_id(),
					'quantity' => 1
				);
			} else {
				// If no product, use a dummy product for shipping calculation
				wp_send_json_error( array( 'message' => 'Product not found for shipping calculation' ) );
				return;
			}

			// Use the existing REST API method
			$request_data = array(
				'products' => $products,
				'country' => $country,
				'state' => $state,
				'post_code' => $postcode
			);

			// Create a mock WP_REST_Request
			$request = new \WP_REST_Request( 'POST' );
			$request->set_param( 'products', $products );
			$request->set_param( 'country', $country );
			$request->set_param( 'state', $state );
			$request->set_param( 'post_code', $postcode );

			// Call the existing method
			$response = $this->get_available_shipping_methods_callback( $request );

			if ( $response instanceof \WP_REST_Response ) {
				$data = $response->get_data();

				// Format the response for frontend
				$shipping_methods = array();
				if ( isset( $data['rates'] ) && is_array( $data['rates'] ) ) {
					foreach ( $data['rates'] as $rate ) {
						$cost_display = 'Free';
						if ( isset( $rate['cost'] ) && $rate['cost'] > 0 ) {
							$cost_display = wc_price( $rate['cost'] );
						}

						$shipping_methods[] = array(
							'id' => $rate['id'],
							'title' => $rate['label'],
							'description' => '',
							'cost' => $cost_display
						);
					}
				}

				wp_send_json_success( $shipping_methods );
			} else {
				wp_send_json_error( array( 'message' => 'Error calculating shipping methods' ) );
			}

		} catch (\Exception $e) {
			wp_send_json_error( array( 'message' => 'Error calculating shipping: ' . $e->getMessage() ) );
		}
	}
}

CalculateShipping::get_instance();

