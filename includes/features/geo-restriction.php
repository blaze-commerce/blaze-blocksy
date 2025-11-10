<?php
/**
 * Product Geo-Restriction Feature
 *
 * Restricts product purchases based on geographic location (US States).
 * Uses browser Geolocation API for client-side detection.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Product_Geo_Restriction
 *
 * Handles geographic restriction for WooCommerce products
 */
class Product_Geo_Restriction {

	/**
	 * US States list
	 *
	 * @var array
	 */
	private $us_states = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
		'DC' => 'District of Columbia',
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register ACF fields
		add_action( 'acf/init', array( $this, 'register_acf_fields' ) );

		// Enqueue assets on single product pages
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Add product restriction data to frontend
		add_action( 'wp_footer', array( $this, 'add_product_data' ) );
	}

	/**
	 * Register ACF fields for product geo-restriction
	 */
	public function register_acf_fields() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Prepare state choices for ACF
		$state_choices = array();
		foreach ( $this->us_states as $code => $name ) {
			$state_choices[ $code ] = $name;
		}

		acf_add_local_field_group(
			array(
				'key' => 'group_product_geo_restriction',
				'title' => 'Product Geo-Restriction',
				'fields' => array(
					array(
						'key' => 'field_enable_geo_restriction',
						'label' => 'Enable Geo-Restriction',
						'name' => 'enable_geo_restriction',
						'type' => 'true_false',
						'instructions' => 'Enable geographic restriction for this product. Only customers from selected US states will be able to purchase.',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => 'Enabled',
						'ui_off_text' => 'Disabled',
					),
					array(
						'key' => 'field_allowed_us_states',
						'label' => 'Allowed US States',
						'name' => 'allowed_us_states',
						'type' => 'select',
						'instructions' => 'Select which US states are allowed to purchase this product. Leave empty to allow all states.',
						'choices' => $state_choices,
						'default_value' => array(),
						'multiple' => 1,
						'ui' => 1,
						'ajax' => 0,
						'placeholder' => 'Select allowed states...',
						'allow_null' => 1,
						'return_format' => 'value',
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_enable_geo_restriction',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
					),
					array(
						'key' => 'field_geo_restriction_message',
						'label' => 'Custom Restriction Message',
						'name' => 'geo_restriction_message',
						'type' => 'textarea',
						'instructions' => 'Custom message to display when product is not available in user\'s location. Leave empty for default message.',
						'default_value' => '',
						'placeholder' => 'This item is ineligible for your location',
						'rows' => 3,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_enable_geo_restriction',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'product',
						),
					),
				),
				'menu_order' => 20,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
			)
		);
	}

	/**
	 * Enqueue JavaScript and CSS assets
	 */
	public function enqueue_assets() {
		// Only load on single product pages
		if ( ! is_product() ) {
			return;
		}

		global $product;
		if ( ! $product ) {
			return;
		}

		// Check if geo-restriction is enabled for this product
		$enabled = get_field( 'enable_geo_restriction', $product->get_id() );
		if ( ! $enabled ) {
			return;
		}

		// Enqueue JavaScript
		wp_enqueue_script(
			'blaze-geo-restriction',
			BLAZE_BLOCKSY_URL . '/assets/js/geo-restriction.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		// Enqueue CSS
		wp_enqueue_style(
			'blaze-geo-restriction',
			BLAZE_BLOCKSY_URL . '/assets/css/geo-restriction.css',
			array(),
			'1.0.0'
		);

		// Localize script with data
		wp_localize_script(
			'blaze-geo-restriction',
			'blazeGeoRestriction',
			array(
				'product_id' => $product->get_id(),
				'states' => $this->us_states,
				'debug' => defined( 'WP_DEBUG' ) && WP_DEBUG,
				'cache_duration' => 24 * 60 * 60 * 1000, // 24 hours in milliseconds
			)
		);
	}

	/**
	 * Add product restriction data to footer
	 */
	public function add_product_data() {
		if ( ! is_product() ) {
			return;
		}

		global $product;
		if ( ! $product ) {
			return;
		}

		$enabled = get_field( 'enable_geo_restriction', $product->get_id() );
		if ( ! $enabled ) {
			return;
		}

		$allowed_states = get_field( 'allowed_us_states', $product->get_id() );
		$custom_message = get_field( 'geo_restriction_message', $product->get_id() );

		// Default message
		$default_message = 'This item is ineligible for your location';

		// Prepare allowed states list for display
		$allowed_states_names = array();
		if ( is_array( $allowed_states ) && ! empty( $allowed_states ) ) {
			foreach ( $allowed_states as $state_code ) {
				if ( isset( $this->us_states[ $state_code ] ) ) {
					$allowed_states_names[] = $this->us_states[ $state_code ];
				}
			}
		}

		?>
		<script type="text/javascript">
			window.blazeGeoRestrictionData = {
				enabled: <?php echo $enabled ? 'true' : 'false'; ?>,
				allowedStates: <?php echo wp_json_encode( $allowed_states ); ?>,
				allowedStatesNames: <?php echo wp_json_encode( $allowed_states_names ); ?>,
				restrictionMessage: <?php echo wp_json_encode( ! empty( $custom_message ) ? $custom_message : $default_message ); ?>
			};
		</script>
		<?php
	}

	/**
	 * Get US states list
	 *
	 * @return array
	 */
	public function get_us_states() {
		return $this->us_states;
	}
}

// Initialize the class
new Product_Geo_Restriction();

