<?php
/**
 * Brand Attributes Converter
 *
 * Custom REST API Endpoint and Admin Interface for converting Brand Attributes (pa_brand)
 * to Product Brand taxonomy and assigning them to products.
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Register custom REST API endpoint for brand attributes
 */
add_action( 'rest_api_init', function () {
	register_rest_route( 'blaze-online/v1', '/convert-brand-attributes', array(
		'methods'             => 'GET',
		'callback'            => 'blaze_blocksy_get_brand_attributes',
		'permission_callback' => '__return_true', // Allow public access
	) );
} );

/**
 * Add admin menu page under Tools
 */
add_action( 'admin_menu', 'blaze_blocksy_add_brand_conversion_page' );

/**
 * Add brand conversion page to admin menu
 *
 * @since 1.0.0
 */
function blaze_blocksy_add_brand_conversion_page() {
	add_management_page(
		__( 'Convert Brand Attributes', 'blaze-blocksy' ),
		__( 'Convert Brand Attributes', 'blaze-blocksy' ),
		'manage_options',
		'convert-brand-attributes',
		'blaze_blocksy_render_brand_conversion_page'
	);
}

/**
 * Render the admin page
 *
 * @since 1.0.0
 */
function blaze_blocksy_render_brand_conversion_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Convert Brand Attributes', 'blaze-blocksy' ); ?></h1>
		<p><?php esc_html_e( 'This tool will convert product attributes (pa_brand) to the product_brand taxonomy and assign them to products.', 'blaze-blocksy' ); ?></p>
		
		<div id="conversion-controls">
			<button id="start-conversion" class="button button-primary button-large">
				<?php esc_html_e( 'Start Conversion', 'blaze-blocksy' ); ?>
			</button>
			<button id="stop-conversion" class="button button-secondary button-large" style="display:none;">
				<?php esc_html_e( 'Stop Conversion', 'blaze-blocksy' ); ?>
			</button>
		</div>

		<div id="conversion-status" style="margin-top: 20px; display:none;">
			<h2><?php esc_html_e( 'Conversion Progress', 'blaze-blocksy' ); ?></h2>
			<div style="background: #fff; border: 1px solid #ccc; padding: 20px; border-radius: 4px;">
				<div id="overall-progress" style="margin-bottom: 20px;">
					<strong><?php esc_html_e( 'Overall Progress:', 'blaze-blocksy' ); ?></strong>
					<div style="background: #f0f0f0; height: 30px; border-radius: 4px; margin-top: 10px; position: relative;">
						<div id="progress-bar" style="background: #2271b1; height: 100%; border-radius: 4px; width: 0%; transition: width 0.3s;"></div>
						<span id="progress-text" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-weight: bold;">0%</span>
					</div>
				</div>
				
				<div id="current-brand" style="margin-bottom: 20px;">
					<strong><?php esc_html_e( 'Current Brand:', 'blaze-blocksy' ); ?></strong> <span id="current-brand-name">-</span>
				</div>

				<div id="stats" style="display: flex; gap: 20px; margin-bottom: 20px;">
					<div>
						<strong><?php esc_html_e( 'Brands Processed:', 'blaze-blocksy' ); ?></strong> <span id="brands-processed">0</span> / <span id="brands-total">0</span>
					</div>
					<div>
						<strong><?php esc_html_e( 'Products Updated:', 'blaze-blocksy' ); ?></strong> <span id="products-updated">0</span>
					</div>
					<div>
						<strong><?php esc_html_e( 'Errors:', 'blaze-blocksy' ); ?></strong> <span id="error-count" style="color: #d63638;">0</span>
					</div>
				</div>
			</div>
		</div>

		<div id="conversion-log" style="margin-top: 20px; display:none;">
			<h2><?php esc_html_e( 'Conversion Log', 'blaze-blocksy' ); ?></h2>
			<div id="log-container" style="background: #fff; border: 1px solid #ccc; padding: 15px; height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px;">
			</div>
		</div>

		<div id="conversion-complete" style="margin-top: 20px; display:none;">
			<div class="notice notice-success" style="padding: 15px;">
				<h2 style="margin-top: 0;"><?php esc_html_e( 'Conversion Complete!', 'blaze-blocksy' ); ?></h2>
				<p id="complete-message"></p>
			</div>
		</div>
	</div>

	<style>
		#log-container .log-entry {
			padding: 5px 0;
			border-bottom: 1px solid #f0f0f0;
		}
		#log-container .log-success {
			color: #46b450;
		}
		#log-container .log-error {
			color: #d63638;
		}
		#log-container .log-info {
			color: #2271b1;
		}
		#log-container .log-warning {
			color: #dba617;
		}
	</style>
	<?php
}

/**
 * Enqueue admin scripts
 *
 * @since 1.0.0
 */
add_action( 'admin_enqueue_scripts', 'blaze_blocksy_enqueue_brand_conversion_scripts' );

/**
 * Enqueue brand conversion scripts
 *
 * @param string $hook Current admin page hook
 * @since 1.0.0
 */
function blaze_blocksy_enqueue_brand_conversion_scripts( $hook ) {
	if ( $hook !== 'tools_page_convert-brand-attributes' ) {
		return;
	}

	// Enqueue jQuery (WordPress already includes it)
	wp_enqueue_script( 'jquery' );
	
	// Localize script with ajax URL and nonce
	wp_localize_script( 'jquery', 'brandConversionData', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'convert_brand_nonce' ),
		'restUrl' => rest_url( 'blaze-online/v1/convert-brand-attributes' ),
	) );

	// Add inline script
	wp_add_inline_script( 'jquery', '
		console.log("Brand conversion script loaded");
		console.log("Data:", brandConversionData);

		jQuery(document).ready(function($) {
			console.log("jQuery ready");

			let isConverting = false;
			let shouldStop = false;
			let stats = {
				brandsProcessed: 0,
				brandsTotal: 0,
				productsUpdated: 0,
				errors: 0
			};

			function log(message, type = "info") {
				const timestamp = new Date().toLocaleTimeString();
				const className = "log-" + type;
				$("#log-container").append(
					`<div class="log-entry ${className}">[${timestamp}] ${message}</div>`
				);
				$("#log-container").scrollTop($("#log-container")[0].scrollHeight);
			}

			function updateProgress() {
				const percent = stats.brandsTotal > 0
					? Math.round((stats.brandsProcessed / stats.brandsTotal) * 100)
					: 0;
				$("#progress-bar").css("width", percent + "%");
				$("#progress-text").text(percent + "%");
				$("#brands-processed").text(stats.brandsProcessed);
				$("#brands-total").text(stats.brandsTotal);
				$("#products-updated").text(stats.productsUpdated);
				$("#error-count").text(stats.errors);
			}

			async function processBrand(brand) {
				if (shouldStop) {
					throw new Error("Conversion stopped by user");
				}

				$("#current-brand-name").text(brand.name + " (" + brand.slug + ")");
				log(`Processing brand: ${brand.name} (${brand.slug})`, "info");

				try {
					const response = await $.ajax({
						url: brandConversionData.ajaxurl,
						method: "POST",
						data: {
							action: "convert_single_brand",
							nonce: brandConversionData.nonce,
							brand_slug: brand.slug,
							brand_name: brand.name
						}
					});

					if (response.success) {
						log(`✓ ${brand.name}: ${response.data.products_updated} products updated`, "success");
						stats.productsUpdated += response.data.products_updated;
					} else {
						log(`✗ ${brand.name}: ${response.data.message}`, "error");
						stats.errors++;
					}
				} catch (error) {
					log(`✗ ${brand.name}: ${error.responseJSON?.data?.message || error.statusText}`, "error");
					stats.errors++;
				}

				stats.brandsProcessed++;
				updateProgress();
			}

			async function startConversion() {
				if (isConverting) return;

				isConverting = true;
				shouldStop = false;
				stats = { brandsProcessed: 0, brandsTotal: 0, productsUpdated: 0, errors: 0 };

				$("#start-conversion").hide();
				$("#stop-conversion").show();
				$("#conversion-status").show();
				$("#conversion-log").show();
				$("#conversion-complete").hide();
				$("#log-container").empty();

				log("Starting brand conversion...", "info");

				try {
					// Get all brands
					log("Fetching brand attributes...", "info");
					const brandsResponse = await $.get(brandConversionData.restUrl);

					const brands = brandsResponse.brands;
					stats.brandsTotal = brands.length;
					updateProgress();

					log(`Found ${brands.length} brands to process`, "success");

					// Process each brand sequentially
					for (const brand of brands) {
						if (shouldStop) {
							log("Conversion stopped by user", "warning");
							break;
						}
						await processBrand(brand);
					}

					if (!shouldStop) {
						log("Conversion completed successfully!", "success");
						$("#conversion-complete").show();
						$("#complete-message").html(
							`<strong>Summary:</strong><br>` +
							`- Brands processed: ${stats.brandsProcessed}<br>` +
							`- Products updated: ${stats.productsUpdated}<br>` +
							`- Errors: ${stats.errors}`
						);
					}

				} catch (error) {
					log("Fatal error: " + (error.responseJSON?.message || error.statusText || error.message), "error");
					alert("An error occurred during conversion. Please check the log.");
				} finally {
					isConverting = false;
					$("#start-conversion").show();
					$("#stop-conversion").hide();
					$("#current-brand-name").text("-");
				}
			}

			$("#start-conversion").on("click", function() {
				console.log("Start button clicked");
				startConversion();
			});

			$("#stop-conversion").on("click", function() {
				console.log("Stop button clicked");
				if (confirm("Are you sure you want to stop the conversion?")) {
					shouldStop = true;
					log("Stopping conversion...", "warning");
				}
			});
		});
	' );
}

/**
 * Get all unique brand attributes (pa_brand)
 *
 * @param WP_REST_Request $request REST request object
 * @return WP_REST_Response|WP_Error Response object or error
 * @since 1.0.0
 */
function blaze_blocksy_get_brand_attributes( $request ) {
	// Get all terms from the pa_brand taxonomy
	$brand_terms = get_terms( array(
		'taxonomy'   => 'pa_brand',
		'hide_empty' => false, // Include terms even if not assigned to products
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );

	// Check for errors
	if ( is_wp_error( $brand_terms ) ) {
		return new WP_Error(
			'no_brands',
			__( 'Could not retrieve brand attributes', 'blaze-blocksy' ),
			array( 'status' => 404 )
		);
	}

	// Check if any brands exist
	if ( empty( $brand_terms ) ) {
		return new WP_REST_Response( array(
			'success' => true,
			'message' => __( 'No brand attributes found', 'blaze-blocksy' ),
			'brands'  => array(),
			'count'   => 0,
		), 200 );
	}

	// Format the response data and create brands in product_brand taxonomy
	$brands         = array();
	$created_brands = array();
	$skipped_brands = array();
	$errors         = array();

	foreach ( $brand_terms as $term ) {
		$brands[] = array(
			'slug'    => $term->slug,
			'name'    => $term->name,
			'term_id' => $term->term_id,
			'count'   => $term->count, // Number of products with this brand
		);

		// Check if brand already exists in product_brand taxonomy
		$existing_term = term_exists( $term->slug, 'product_brand' );

		if ( $existing_term ) {
			$skipped_brands[] = array(
				'slug'   => $term->slug,
				'name'   => $term->name,
				'reason' => __( 'Already exists', 'blaze-blocksy' ),
			);
			continue;
		}

		// Create the brand in product_brand taxonomy
		$new_term = wp_insert_term(
			$term->name, // Term name
			'product_brand', // Taxonomy
			array(
				'slug' => $term->slug, // Use the same slug
			)
		);

		if ( is_wp_error( $new_term ) ) {
			$errors[] = array(
				'slug'  => $term->slug,
				'name'  => $term->name,
				'error' => $new_term->get_error_message(),
			);
		} else {
			$created_brands[] = array(
				'slug'        => $term->slug,
				'name'        => $term->name,
				'new_term_id' => $new_term['term_id'],
			);
		}
	}

	// Return the response
	return new WP_REST_Response( array(
		'success' => true,
		'brands'  => $brands,
		'count'   => count( $brands ),
		'created' => array(
			'count'  => count( $created_brands ),
			'brands' => $created_brands,
		),
		'skipped' => array(
			'count'  => count( $skipped_brands ),
			'brands' => $skipped_brands,
		),
		'errors'  => array(
			'count'  => count( $errors ),
			'brands' => $errors,
		),
	), 200 );
}

/**
 * AJAX handler to convert a single brand
 *
 * @since 1.0.0
 */
add_action( 'wp_ajax_convert_single_brand', 'blaze_blocksy_ajax_convert_single_brand' );

/**
 * Convert a single brand from pa_brand to product_brand taxonomy
 *
 * @since 1.0.0
 */
function blaze_blocksy_ajax_convert_single_brand() {
	// Verify nonce
	check_ajax_referer( 'convert_brand_nonce', 'nonce' );

	// Check user permissions
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array(
			'message' => __( 'Insufficient permissions', 'blaze-blocksy' ),
		), 403 );
	}

	$brand_slug = sanitize_text_field( $_POST['brand_slug'] );
	$brand_name = sanitize_text_field( $_POST['brand_name'] );

	if ( empty( $brand_slug ) || empty( $brand_name ) ) {
		wp_send_json_error( array(
			'message' => __( 'Brand slug and name are required', 'blaze-blocksy' ),
		) );
	}

	// Get or create the brand in product_brand taxonomy
	$product_brand_term = term_exists( $brand_slug, 'product_brand' );

	if ( ! $product_brand_term ) {
		$product_brand_term = wp_insert_term( $brand_name, 'product_brand', array(
			'slug' => $brand_slug,
		) );

		if ( is_wp_error( $product_brand_term ) ) {
			wp_send_json_error( array(
				'message' => sprintf(
					/* translators: %s: Error message */
					__( 'Failed to create product_brand term: %s', 'blaze-blocksy' ),
					$product_brand_term->get_error_message()
				),
			) );
		}
	}

	$product_brand_term_id = is_array( $product_brand_term ) ? $product_brand_term['term_id'] : $product_brand_term;

	// Query all products that have this pa_brand attribute
	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'tax_query'      => array(
			array(
				'taxonomy' => 'pa_brand',
				'field'    => 'slug',
				'terms'    => $brand_slug,
			),
		),
		'fields'         => 'ids', // Only get IDs for better performance
	);

	$products         = get_posts( $args );
	$products_updated = 0;
	$errors           = array();

	// Loop through each product and add the product_brand taxonomy
	foreach ( $products as $product_id ) {
		$result = wp_set_object_terms( $product_id, (int) $product_brand_term_id, 'product_brand', true );

		if ( is_wp_error( $result ) ) {
			$errors[] = array(
				'product_id' => $product_id,
				'error'      => $result->get_error_message(),
			);
		} else {
			$products_updated++;
		}
	}

	wp_send_json_success( array(
		'brand_slug'             => $brand_slug,
		'brand_name'             => $brand_name,
		'products_found'         => count( $products ),
		'products_updated'       => $products_updated,
		'errors'                 => $errors,
		'product_brand_term_id'  => $product_brand_term_id,
	) );
}
