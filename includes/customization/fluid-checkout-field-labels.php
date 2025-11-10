<?php
/**
 * Fluid Checkout Field Label Customization
 *
 * Adds customizer options to replace/customize individual field labels
 * in the Fluid Checkout form with highest priority to override defaults.
 *
 * @package Blocksy_Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check if Fluid Checkout is active before initializing
 */
if ( ! class_exists( 'FluidCheckout' ) ) {
	return;
}

class Blocksy_Child_Fluid_Checkout_Field_Labels {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register customizer hooks
		add_action( 'customize_register', array( $this, 'register_customizer_settings' ), 20 );
		
		// Apply field label filters with highest priority
		add_filter( 'woocommerce_checkout_fields', array( $this, 'customize_checkout_field_labels' ), 9999 );
		add_filter( 'woocommerce_default_address_fields', array( $this, 'customize_default_address_fields' ), 9999 );
	}

	/**
	 * Register customizer settings for field labels
	 *
	 * @param WP_Customize_Manager $wp_customize WordPress Customizer Manager instance
	 */
	public function register_customizer_settings( $wp_customize ) {
		// Verify $wp_customize is valid
		if ( ! $wp_customize instanceof WP_Customize_Manager ) {
			return;
		}

		// Check if the Fluid Checkout panel exists
		if ( ! $wp_customize->get_panel( 'blocksy_fluid_checkout_panel' ) ) {
			return;
		}

		// Add Field Labels Section
		$wp_customize->add_section(
			'blocksy_fc_field_labels',
			array(
				'title'    => __( 'Field Labels', 'blocksy-child' ),
				'description' => __( 'Customize the text for each individual field label in the checkout form. Leave empty to use default labels.', 'blocksy-child' ),
				'panel'    => 'blocksy_fluid_checkout_panel',
				'priority' => 80,
			)
		);

		// Define all field labels with their defaults
		$field_labels = array(
			// Contact Section
			'billing_email' => array(
				'label'   => __( 'Email Address Label', 'blocksy-child' ),
				'default' => 'Email address',
			),

			// Shipping Address Fields
			'shipping_first_name' => array(
				'label'   => __( 'Shipping First Name Label', 'blocksy-child' ),
				'default' => 'First name',
			),
			'shipping_last_name' => array(
				'label'   => __( 'Shipping Last Name Label', 'blocksy-child' ),
				'default' => 'Last name',
			),
			'shipping_phone' => array(
				'label'   => __( 'Shipping Phone Label', 'blocksy-child' ),
				'default' => 'Shipping phone',
			),
			'shipping_company' => array(
				'label'   => __( 'Shipping Company Label', 'blocksy-child' ),
				'default' => 'Company name',
			),
			'shipping_country' => array(
				'label'   => __( 'Shipping Country Label', 'blocksy-child' ),
				'default' => 'Country / Region',
			),
			'shipping_address_1' => array(
				'label'   => __( 'Shipping Street Address Label', 'blocksy-child' ),
				'default' => 'Street address',
			),
			'shipping_address_2' => array(
				'label'   => __( 'Shipping Apartment/Suite Label', 'blocksy-child' ),
				'default' => 'Apartment, suite, unit, etc.',
			),
			'shipping_city' => array(
				'label'   => __( 'Shipping City Label', 'blocksy-child' ),
				'default' => 'Town / City',
			),
			'shipping_state' => array(
				'label'   => __( 'Shipping State Label', 'blocksy-child' ),
				'default' => 'State / County',
			),
			'shipping_postcode' => array(
				'label'   => __( 'Shipping Postcode Label', 'blocksy-child' ),
				'default' => 'Postcode / ZIP',
			),

			// Billing Address Fields
			'billing_first_name' => array(
				'label'   => __( 'Billing First Name Label', 'blocksy-child' ),
				'default' => 'First name',
			),
			'billing_last_name' => array(
				'label'   => __( 'Billing Last Name Label', 'blocksy-child' ),
				'default' => 'Last name',
			),
			'billing_phone' => array(
				'label'   => __( 'Billing Phone Label', 'blocksy-child' ),
				'default' => 'Phone',
			),
			'billing_company' => array(
				'label'   => __( 'Billing Company Label', 'blocksy-child' ),
				'default' => 'Company name',
			),
			'billing_country' => array(
				'label'   => __( 'Billing Country Label', 'blocksy-child' ),
				'default' => 'Country / Region',
			),
			'billing_address_1' => array(
				'label'   => __( 'Billing Street Address Label', 'blocksy-child' ),
				'default' => 'Street address',
			),
			'billing_address_2' => array(
				'label'   => __( 'Billing Apartment/Suite Label', 'blocksy-child' ),
				'default' => 'Apartment, suite, unit, etc.',
			),
			'billing_city' => array(
				'label'   => __( 'Billing City Label', 'blocksy-child' ),
				'default' => 'Town / City',
			),
			'billing_state' => array(
				'label'   => __( 'Billing State Label', 'blocksy-child' ),
				'default' => 'State / County',
			),
			'billing_postcode' => array(
				'label'   => __( 'Billing Postcode Label', 'blocksy-child' ),
				'default' => 'Postcode / ZIP',
			),

			// Additional Fields
			'order_comments' => array(
				'label'   => __( 'Order Notes Label', 'blocksy-child' ),
				'default' => 'Order notes',
			),
		);

		// Register settings and controls for each field
		foreach ( $field_labels as $field_key => $config ) {
			$setting_id = "blocksy_fc_label_{$field_key}";

			// Add setting
			$wp_customize->add_setting(
				$setting_id,
				array(
					'default'           => $config['default'],
					'sanitize_callback' => 'sanitize_text_field',
					'transport'         => 'refresh',
				)
			);

			// Add control
			$wp_customize->add_control(
				$setting_id,
				array(
					'label'       => $config['label'],
					'description' => sprintf( __( 'Default: %s', 'blocksy-child' ), $config['default'] ),
					'section'     => 'blocksy_fc_field_labels',
					'type'        => 'text',
				)
			);
		}
	}

	/**
	 * Customize checkout field labels
	 *
	 * @param array $fields Checkout fields
	 * @return array Modified checkout fields
	 */
	public function customize_checkout_field_labels( $fields ) {
		// Email field
		$email_label = get_theme_mod( 'blocksy_fc_label_billing_email', '' );
		if ( ! empty( $email_label ) && isset( $fields['billing']['billing_email'] ) ) {
			$fields['billing']['billing_email']['label'] = $email_label;
		}

		// Shipping fields
		$shipping_fields = array(
			'shipping_first_name',
			'shipping_last_name',
			'shipping_phone',
			'shipping_company',
			'shipping_country',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_state',
			'shipping_postcode',
		);

		foreach ( $shipping_fields as $field_key ) {
			$custom_label = get_theme_mod( "blocksy_fc_label_{$field_key}", '' );
			if ( ! empty( $custom_label ) && isset( $fields['shipping'][ $field_key ] ) ) {
				$fields['shipping'][ $field_key ]['label'] = $custom_label;
			}
		}

		// Billing fields
		$billing_fields = array(
			'billing_first_name',
			'billing_last_name',
			'billing_phone',
			'billing_company',
			'billing_country',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_postcode',
		);

		foreach ( $billing_fields as $field_key ) {
			$custom_label = get_theme_mod( "blocksy_fc_label_{$field_key}", '' );
			if ( ! empty( $custom_label ) && isset( $fields['billing'][ $field_key ] ) ) {
				$fields['billing'][ $field_key ]['label'] = $custom_label;
			}
		}

		// Order comments
		$order_comments_label = get_theme_mod( 'blocksy_fc_label_order_comments', '' );
		if ( ! empty( $order_comments_label ) && isset( $fields['order']['order_comments'] ) ) {
			$fields['order']['order_comments']['label'] = $order_comments_label;
		}

		return $fields;
	}

	/**
	 * Customize default address fields
	 *
	 * This filter is applied to default address fields before they are merged
	 * into billing and shipping fields, providing an additional layer of customization.
	 *
	 * @param array $fields Default address fields
	 * @return array Modified address fields
	 */
	public function customize_default_address_fields( $fields ) {
		$address_fields = array(
			'first_name',
			'last_name',
			'company',
			'country',
			'address_1',
			'address_2',
			'city',
			'state',
			'postcode',
		);

		foreach ( $address_fields as $field_key ) {
			// Try both shipping and billing versions
			$shipping_label = get_theme_mod( "blocksy_fc_label_shipping_{$field_key}", '' );
			$billing_label = get_theme_mod( "blocksy_fc_label_billing_{$field_key}", '' );
			
			// Use shipping label as default if both are set (shipping is more commonly customized)
			$custom_label = ! empty( $shipping_label ) ? $shipping_label : $billing_label;
			
			if ( ! empty( $custom_label ) && isset( $fields[ $field_key ] ) ) {
				$fields[ $field_key ]['label'] = $custom_label;
			}
		}

		return $fields;
	}
}

// Initialize the field labels customization
new Blocksy_Child_Fluid_Checkout_Field_Labels();

