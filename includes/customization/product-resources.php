<?php
/**
 * WooCommerce Product Custom Fields
 *
 * Adds custom fields to the WooCommerce product edit page for:
 * - Product Sheet (URL)
 * - Installation Sheet (URL)
 * - Book an Installer (URL)
 * - Installation Video (URL)
 * - Product Warranty URL
 *
 * Features:
 * - Custom "Product Resources" tab in product data
 * - URL input fields with validation
 * - Save/retrieve functionality
 * - Helper methods for frontend access
 *
 * @package BlazeCommerce
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Product_Resources
 *
 * Handles the addition of custom fields to WooCommerce products and integrates with Blocksy customizer
 */
class Product_Resources {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Add custom tab to product data
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_custom_product_tab' ) );

		// Add custom fields to the tab
		add_action( 'woocommerce_product_data_panels', array( $this, 'add_custom_product_fields' ) );

		// Save custom fields
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_custom_product_fields' ) );

		// Add basic styling
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );

		// Add frontend styles (keep for backward compatibility)
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );

		// Add contact page footer script
		add_action( 'wp_footer', array( $this, 'enqueue_contact_page_script' ) );

		// Register element in Blocksy's layout system
		add_filter( 'blocksy_woo_single_options_layers:defaults', array( $this, 'add_to_defaults' ) );
		add_filter( 'blocksy_woo_single_options_layers:extra', array( $this, 'add_options' ) );
		add_action( 'blocksy:woocommerce:product:custom:layer', array( $this, 'render_element' ) );
	}

	/**
	 * Add custom tab to product data tabs
	 *
	 * @param array $tabs Existing tabs
	 * @return array Modified tabs
	 */
	public function add_custom_product_tab( $tabs ) {
		$tabs['product_resources'] = array(
			'label' => __( 'Product Resources', 'blocksy-child' ),
			'target' => 'product_resources_data',
			'class' => array( 'show_if_simple', 'show_if_variable', 'show_if_grouped', 'show_if_external' ),
			'priority' => 25,
		);

		return $tabs;
	}

	/**
	 * Add custom fields to the product data panel
	 */
	public function add_custom_product_fields() {
		global $post;

		?>
		<div id="product_resources_data" class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<h3><?php esc_html_e( 'Product Documentation', 'blocksy-child' ); ?></h3>
				<p class="form-field-description">
					<?php esc_html_e( 'Add links to product documentation and resources.', 'blocksy-child' ); ?>
				</p>

				<?php
				// Product Sheet URL
				woocommerce_wp_text_input( array(
					'id' => '_product_sheet_url',
					'label' => __( 'Product Sheet URL', 'blocksy-child' ),
					'placeholder' => 'https://example.com/product-sheet.pdf',
					'desc_tip' => true,
					'description' => __( 'Enter a URL to the product specification sheet or datasheet.', 'blocksy-child' ),
					'type' => 'url',
					'value' => get_post_meta( $post->ID, '_product_sheet_url', true ),
				) );

				// Installation Sheet URL
				woocommerce_wp_text_input( array(
					'id' => '_installation_sheet_url',
					'label' => __( 'Installation Sheet URL', 'blocksy-child' ),
					'placeholder' => 'https://example.com/installation-guide.pdf',
					'desc_tip' => true,
					'description' => __( 'Enter a URL to the installation instructions or guide.', 'blocksy-child' ),
					'type' => 'url',
					'value' => get_post_meta( $post->ID, '_installation_sheet_url', true ),
				) );
				?>
			</div>

			<div class="options_group">
				<h3><?php esc_html_e( 'Installation Services', 'blocksy-child' ); ?></h3>
				<p class="form-field-description">
					<?php esc_html_e( 'Add links to installation services and instructional content.', 'blocksy-child' ); ?>
				</p>

				<?php
				// Book an Installer URL
				woocommerce_wp_text_input( array(
					'id' => '_book_installer_url',
					'label' => __( 'Book an Installer URL', 'blocksy-child' ),
					'placeholder' => 'https://example.com/book-installer',
					'desc_tip' => true,
					'description' => __( 'Enter a URL to a booking page for professional installation services.', 'blocksy-child' ),
					'type' => 'url',
					'value' => get_post_meta( $post->ID, '_book_installer_url', true ),
				) );

				// Installation Video URL
				woocommerce_wp_text_input( array(
					'id' => '_installation_video_url',
					'label' => __( 'Installation Video URL', 'blocksy-child' ),
					'placeholder' => 'https://youtube.com/watch?v=example',
					'desc_tip' => true,
					'description' => __( 'Enter a URL to an instructional video (YouTube, Vimeo, etc.).', 'blocksy-child' ),
					'type' => 'url',
					'value' => get_post_meta( $post->ID, '_installation_video_url', true ),
				) );
				?>
			</div>

			<div class="options_group">
				<h3><?php esc_html_e( 'Product Support', 'blocksy-child' ); ?></h3>
				<p class="form-field-description">
					<?php esc_html_e( 'Add links to product support and warranty information.', 'blocksy-child' ); ?>
				</p>

				<?php
				// Product Warranty URL
				woocommerce_wp_text_input( array(
					'id' => '_product_warranty_url',
					'label' => __( 'Product Warranty URL', 'blocksy-child' ),
					'placeholder' => 'https://example.com/warranty-info',
					'desc_tip' => true,
					'description' => __( 'Enter a URL to the product warranty information or registration page.', 'blocksy-child' ),
					'type' => 'url',
					'value' => get_post_meta( $post->ID, '_product_warranty_url', true ),
				) );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save custom product fields
	 *
	 * @param int $post_id Product ID
	 */
	public function save_custom_product_fields( $post_id ) {
		// Verify nonce for security
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
			return;
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Define the URL fields to save
		$url_fields = array(
			'_product_sheet_url',
			'_installation_sheet_url',
			'_book_installer_url',
			'_installation_video_url',
			'_product_warranty_url'
		);

		// Save each URL field
		foreach ( $url_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = sanitize_url( $_POST[ $field ] );

				// Save or delete the meta
				if ( ! empty( $value ) ) {
					update_post_meta( $post_id, $field, $value );
				} else {
					delete_post_meta( $post_id, $field );
				}
			}
		}
	}

	/**
	 * Add Product Resource element to default layout (Blocksy integration)
	 *
	 * @param array $layers Existing layers
	 * @return array Modified layers
	 */
	public function add_to_defaults( $layers ) {
		$layers[] = array(
			'id' => 'product_resource',
			'enabled' => false,
		);
		return $layers;
	}

	/**
	 * Add customizer options for Product Resource element (Blocksy integration)
	 *
	 * @param array $options Existing options
	 * @return array Modified options
	 */
	public function add_options( $options ) {
		$options['product_resource'] = array(
			'label' => __( 'Product Resource', 'blocksy-child' ),
			'options' => array(
				'product_resource_visibility' => array(
					'label' => __( 'Visibility', 'blocksy-child' ),
					'type' => 'ct-visibility',
					'design' => 'block',
					'setting' => array( 'transport' => 'postMessage' ),
					'value' => array(
						'desktop' => true,
						'tablet' => true,
						'mobile' => true,
					),
					'choices' => blocksy_ordered_keys( array(
						'desktop' => __( 'Desktop', 'blocksy-child' ),
						'tablet' => __( 'Tablet', 'blocksy-child' ),
						'mobile' => __( 'Mobile', 'blocksy-child' ),
					) ),
				),
			),
		);
		return $options;
	}

	/**
	 * Render the Product Resource element (Blocksy integration)
	 *
	 * @param array $layer Layer configuration
	 */
	public function render_element( $layer ) {
		if ( $layer['id'] !== 'product_resource' ) {
			return;
		}

		global $product;

		if ( ! $product ) {
			return;
		}

		$product_id = $product->get_id();
		$fields     = self::get_all_custom_fields( $product_id );

		// Check if any fields have values
		$has_content = false;
		foreach ( $fields as $field_value ) {
			if ( ! empty( $field_value ) ) {
				$has_content = true;
				break;
			}
		}

		if ( ! $has_content ) {
			return;
		}

		// Get visibility settings
		$visibility = blocksy_akg( 'product_resource_visibility', $layer, array(
			'desktop' => true,
			'tablet' => true,
			'mobile' => true,
		) );

		$visibility_classes = array();
		if ( ! $visibility['desktop'] ) {
			$visibility_classes[] = 'ct-hidden-desktop';
		}
		if ( ! $visibility['tablet'] ) {
			$visibility_classes[] = 'ct-hidden-tablet';
		}
		if ( ! $visibility['mobile'] ) {
			$visibility_classes[] = 'ct-hidden-mobile';
		}

		$class_attr = 'ct-product-resource-element';
		if ( ! empty( $visibility_classes ) ) {
			$class_attr .= ' ' . implode( ' ', $visibility_classes );
		}

		echo blocksy_html_tag(
			'div',
			array(
				'class' => $class_attr,
				'data-id' => blocksy_akg( '__id', $layer, 'default' ),
			),
			$this->get_product_resources_content( $fields )
		);
	}

	/**
	 * Get product resources content for Blocksy element
	 *
	 * @param array $fields Product resource fields
	 * @return string HTML content
	 */
	private function get_product_resources_content( $fields ) {
		ob_start();
		?>
		<div class="product-resources-section">
			<div class="product-resources-grid">
				<?php if ( ! empty( $fields['product_sheet_url'] ) ) : ?>
					<div class="resource-item">
						<h4 class="resource-title"><?php esc_html_e( 'Product Sheet', 'blocksy-child' ); ?></h4>
						<a href="<?php echo esc_url( $fields['product_sheet_url'] ); ?>" target="_blank"
							class="resource-link download-link">
							<span class="download-icon">
								<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M6.75 0.75C6.75 0.335787 6.41421 0 6 0C5.58579 0 5.25 0.335786 5.25 0.75L5.25 6.43934L3.03033 4.21967C2.73744 3.92678 2.26256 3.92678 1.96967 4.21967C1.67678 4.51256 1.67678 4.98744 1.96967 5.28033L5.46967 8.78033C5.76256 9.07322 6.23744 9.07322 6.53033 8.78033L10.0303 5.28033C10.3232 4.98744 10.3232 4.51256 10.0303 4.21967C9.73744 3.92678 9.26256 3.92678 8.96967 4.21967L6.75 6.43934L6.75 0.75Z"
										fill="#0F172A" />
									<path
										d="M1.5 7.75C1.5 7.33579 1.16421 7 0.75 7C0.335786 7 0 7.33579 0 7.75V9.25C0 10.7688 1.23122 12 2.75 12H9.25C10.7688 12 12 10.7688 12 9.25V7.75C12 7.33579 11.6642 7 11.25 7C10.8358 7 10.5 7.33579 10.5 7.75V9.25C10.5 9.94036 9.94036 10.5 9.25 10.5H2.75C2.05964 10.5 1.5 9.94036 1.5 9.25V7.75Z"
										fill="#0F172A" />
								</svg>
							</span>
							<?php echo esc_html( 'View Brochure' ); ?>
						</a>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $fields['installation_sheet_url'] ) ) : ?>
					<div class="resource-item">
						<h4 class="resource-title"><?php esc_html_e( 'Installation Sheet', 'blocksy-child' ); ?></h4>
						<a href="<?php echo esc_url( $fields['installation_sheet_url'] ); ?>" target="_blank"
							class="resource-link download-link">
							<span class="download-icon">
								<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M6.75 0.75C6.75 0.335787 6.41421 0 6 0C5.58579 0 5.25 0.335786 5.25 0.75L5.25 6.43934L3.03033 4.21967C2.73744 3.92678 2.26256 3.92678 1.96967 4.21967C1.67678 4.51256 1.67678 4.98744 1.96967 5.28033L5.46967 8.78033C5.76256 9.07322 6.23744 9.07322 6.53033 8.78033L10.0303 5.28033C10.3232 4.98744 10.3232 4.51256 10.0303 4.21967C9.73744 3.92678 9.26256 3.92678 8.96967 4.21967L6.75 6.43934L6.75 0.75Z"
										fill="#0F172A" />
									<path
										d="M1.5 7.75C1.5 7.33579 1.16421 7 0.75 7C0.335786 7 0 7.33579 0 7.75V9.25C0 10.7688 1.23122 12 2.75 12H9.25C10.7688 12 12 10.7688 12 9.25V7.75C12 7.33579 11.6642 7 11.25 7C10.8358 7 10.5 7.33579 10.5 7.75V9.25C10.5 9.94036 9.94036 10.5 9.25 10.5H2.75C2.05964 10.5 1.5 9.94036 1.5 9.25V7.75Z"
										fill="#0F172A" />
								</svg>
							</span>
							<?php echo esc_html( 'View Installation Guide' ); ?>
						</a>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $fields['book_installer_url'] ) ) : ?>
					<div class="resource-item">
						<h4 class="resource-title"><?php esc_html_e( 'Book an Installer', 'blocksy-child' ); ?></h4>
						<a href="<?php echo esc_url( $fields['book_installer_url'] ); ?>" target="_blank" class="resource-link">
							<?php esc_html_e( 'Book an Installer Now', 'blocksy-child' ); ?>
						</a>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $fields['product_warranty_url'] ) ) : ?>
					<div class="resource-item">
						<h4 class="resource-title"><?php esc_html_e( 'Product Warranty', 'blocksy-child' ); ?></h4>
						<a href="<?php echo esc_url( $fields['product_warranty_url'] ); ?>" target="_blank" class="resource-link">
							<?php esc_html_e( 'View Warranty Information', 'blocksy-child' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $fields['installation_video_url'] ) ) : ?>
				<div class="video-section">
					<h4 class="resource-title video-title"><?php esc_html_e( 'Installation Video', 'blocksy-child' ); ?></h4>
					<div class="video-container">
						<?php echo self::get_video_embed( $fields['installation_video_url'] ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Display product resources on frontend (legacy method - kept for backward compatibility)
	 */
	public function display_product_resources() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$product_id = $product->get_id();
		$fields     = self::get_all_custom_fields( $product_id );

		// Check if any fields have values
		$has_content = false;
		foreach ( $fields as $field_value ) {
			if ( ! empty( $field_value ) ) {
				$has_content = true;
				break;
			}
		}

		if ( ! $has_content ) {
			return;
		}

		?>
		<div class="product-resources-section">
			<div class="product-resources-grid">
				<?php if ( ! empty( $fields['product_sheet_url'] ) ) : ?>
					<div class="resource-item">
						<h4 class="resource-title"><?php esc_html_e( 'Product Sheet', 'blocksy-child' ); ?></h4>
						<a href="<?php echo esc_url( $fields['product_sheet_url'] ); ?>" target="_blank"
							class="resource-link download-link">
							<span class="download-icon">
								<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M6.75 0.75C6.75 0.335787 6.41421 0 6 0C5.58579 0 5.25 0.335786 5.25 0.75L5.25 6.43934L3.03033 4.21967C2.73744 3.92678 2.26256 3.92678 1.96967 4.21967C1.67678 4.51256 1.67678 4.98744 1.96967 5.28033L5.46967 8.78033C5.76256 9.07322 6.23744 9.07322 6.53033 8.78033L10.0303 5.28033C10.3232 4.98744 10.3232 4.51256 10.0303 4.21967C9.73744 3.92678 9.26256 3.92678 8.96967 4.21967L6.75 6.43934L6.75 0.75Z"
										fill="#0F172A" />
									<path
										d="M1.5 7.75C1.5 7.33579 1.16421 7 0.75 7C0.335786 7 0 7.33579 0 7.75V9.25C0 10.7688 1.23122 12 2.75 12H9.25C10.7688 12 12 10.7688 12 9.25V7.75C12 7.33579 11.6642 7 11.25 7C10.8358 7 10.5 7.33579 10.5 7.75V9.25C10.5 9.94036 9.94036 10.5 9.25 10.5H2.75C2.05964 10.5 1.5 9.94036 1.5 9.25V7.75Z"
										fill="#0F172A" />
								</svg>
							</span>
							<?php echo esc_html( 'View Brochure' ); ?>
						</a>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $fields['installation_sheet_url'] ) ) : ?>
					<div class="resource-item">
						<h4 class="resource-title"><?php esc_html_e( 'Installation Sheet', 'blocksy-child' ); ?></h4>
						<a href="<?php echo esc_url( $fields['installation_sheet_url'] ); ?>" target="_blank"
							class="resource-link download-link">
							<span class="download-icon">
								<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path
										d="M6.75 0.75C6.75 0.335787 6.41421 0 6 0C5.58579 0 5.25 0.335786 5.25 0.75L5.25 6.43934L3.03033 4.21967C2.73744 3.92678 2.26256 3.92678 1.96967 4.21967C1.67678 4.51256 1.67678 4.98744 1.96967 5.28033L5.46967 8.78033C5.76256 9.07322 6.23744 9.07322 6.53033 8.78033L10.0303 5.28033C10.3232 4.98744 10.3232 4.51256 10.0303 4.21967C9.73744 3.92678 9.26256 3.92678 8.96967 4.21967L6.75 6.43934L6.75 0.75Z"
										fill="#0F172A" />
									<path
										d="M1.5 7.75C1.5 7.33579 1.16421 7 0.75 7C0.335786 7 0 7.33579 0 7.75V9.25C0 10.7688 1.23122 12 2.75 12H9.25C10.7688 12 12 10.7688 12 9.25V7.75C12 7.33579 11.6642 7 11.25 7C10.8358 7 10.5 7.33579 10.5 7.75V9.25C10.5 9.94036 9.94036 10.5 9.25 10.5H2.75C2.05964 10.5 1.5 9.94036 1.5 9.25V7.75Z"
										fill="#0F172A" />
								</svg>
							</span>
							<?php echo esc_html( 'View Installation Guide' ); ?>
						</a>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $fields['book_installer_url'] ) ) : ?>
					<div class="resource-item">
						<h4 class="resource-title"><?php esc_html_e( 'Book an Installer', 'blocksy-child' ); ?></h4>
						<a href="<?php echo esc_url( $fields['book_installer_url'] ); ?>" target="_blank" class="resource-link">
							<?php esc_html_e( 'Book an Installer Now', 'blocksy-child' ); ?>
						</a>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $fields['product_warranty_url'] ) ) : ?>
					<div class="resource-item">
						<h4 class="resource-title"><?php esc_html_e( 'Product Warranty', 'blocksy-child' ); ?></h4>
						<a href="<?php echo esc_url( $fields['product_warranty_url'] ); ?>" target="_blank" class="resource-link">
							<?php esc_html_e( 'View Warranty Information', 'blocksy-child' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $fields['installation_video_url'] ) ) : ?>
				<div class="video-section">
					<h4 class="resource-title video-title"><?php esc_html_e( 'Rail Installation Video', 'blocksy-child' ); ?></h4>
					<div class="video-container">
						<?php echo $this->get_video_embed( $fields['installation_video_url'] ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Get video embed HTML
	 *
	 * @param string $url Video URL
	 * @return string Embed HTML
	 */
	public static function get_video_embed( $url ) {
		if ( empty( $url ) ) {
			return '';
		}

		// YouTube embed
		if ( strpos( $url, 'youtube.com' ) !== false || strpos( $url, 'youtu.be' ) !== false ) {
			$video_id = self::extract_youtube_id( $url );
			if ( $video_id ) {
				return '<iframe width="100%" height="400" src="https://www.youtube.com/embed/' . esc_attr( $video_id ) . '" frameborder="0" allowfullscreen></iframe>';
			}
		}

		// Vimeo embed
		if ( strpos( $url, 'vimeo.com' ) !== false ) {
			$video_id = self::extract_vimeo_id( $url );
			if ( $video_id ) {
				return '<iframe width="100%" height="400" src="https://player.vimeo.com/video/' . esc_attr( $video_id ) . '" frameborder="0" allowfullscreen></iframe>';
			}
		}

		// Fallback: simple link
		return '<a href="' . esc_url( $url ) . '" target="_blank" class="video-link">' . esc_html__( 'Watch Installation Video', 'blocksy-child' ) . '</a>';
	}

	/**
	 * Extract YouTube video ID from URL
	 *
	 * @param string $url YouTube URL
	 * @return string|false Video ID or false
	 */
	private static function extract_youtube_id( $url ) {
		$pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
		preg_match( $pattern, $url, $matches );
		return isset( $matches[1] ) ? $matches[1] : false;
	}

	/**
	 * Extract Vimeo video ID from URL
	 *
	 * @param string $url Vimeo URL
	 * @return string|false Video ID or false
	 */
	private static function extract_vimeo_id( $url ) {
		$pattern = '/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/';
		preg_match( $pattern, $url, $matches );
		return isset( $matches[3] ) ? $matches[3] : false;
	}

	/**
	 * Add basic styling for the custom fields
	 *
	 * @param string $hook Current admin page hook
	 */
	public function enqueue_admin_styles( $hook ) {
		// Only load on product edit pages
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		global $post_type;
		if ( $post_type !== 'product' ) {
			return;
		}

		// Add inline CSS for styling
		?>
		<style type="text/css">
			.form-field-description {
				color: #666;
				font-style: italic;
				margin-bottom: 15px;
			}

			#product_resources_data {
				padding: 10px;
			}

			#product_resources_data h3 {
				margin: 20px 0 10px 0;
				padding: 0;
				font-size: 14px;
				font-weight: 600;
			}

			#product_resources_data h3:first-child {
				margin-top: 0;
			}
		</style>
		<?php
	}

	/**
	 * Enqueue frontend styles
	 */
	public function enqueue_frontend_styles() {
		if ( ! is_product() ) {
			return;
		}

		?>
		<style type="text/css">
			.woocommerce-product-details__short-description .fusion-button.button-small,
			.woocommerce-product-details__short-description iframe {
				display: none;
			}

			.product-resources-section {
				margin: 30px 0;
				padding: 20px 0;
				border-top: 1px solid #e0e0e0;
			}

			.product-resources-grid {
				display: flex;
				flex-wrap: wrap;
				gap: 20px;
				margin-bottom: 30px;
			}

			.resource-item {}

			.resource-title {
				margin: 0 0 10px 0;
				padding-bottom: 15px;
				border-bottom: 4px solid var(--primary);
				color: var(--foreground, #040711);
				font-size: 18px;
				font-style: normal;
				font-weight: 700;
			}

			.resource-link {
				display: inline-flex;
				align-items: center;
				gap: 8px;
				text-decoration: none;
				transition: color 0.3s ease;
				color: var(--secondary, #1A1A1A);
				text-align: center;
				font-size: var(--Size-Links, 14px);
				font-style: normal;
				font-weight: 600;
				line-height: var(--leading-5, 20px);

			}

			.resource-link:hover {
				color: var(--primary);
				text-decoration: none;
			}

			.download-icon {
				display: inline-flex;
				align-items: center;
				justify-content: center;
			}

			.download-icon svg {
				width: 12px;
				height: 12px;
				transition: fill 0.3s ease;
			}

			.download-icon svg path {
				fill: #333;
				transition: fill 0.3s ease;
			}

			.resource-link:hover .download-icon svg path {
				fill: var(--primary);
			}

			.video-section {
				margin-top: 30px;
			}

			.video-title {
				margin: 0 0 15px 0;
				font-size: 18px;
				font-weight: 600;
				color: #333;
				border-bottom: 4px solid var(--primary);
				padding-bottom: 15px;
				display: inline-block;
			}

			.video-container {
				position: relative;
				width: 100%;
				max-width: 800px;
				margin: 0 auto;
			}

			.video-container iframe {
				width: 100%;
				height: 400px;
				border-radius: 8px;
				box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
			}

			.video-link {
				display: inline-block;
				background: #dc3545;
				color: white !important;
				padding: 12px 24px;
				border-radius: 6px;
				text-decoration: none;
				font-weight: 600;
				transition: background 0.3s ease;
			}

			.video-link:hover {
				background: #c82333;
				color: white !important;
				text-decoration: none;
			}

			@media (max-width: 768px) {


				.video-container iframe {
					height: 250px;
				}

				.resource-title {
					font-size: var(--Size-Paragraph, 14px);
				}

				.resource-link {
					font-size: var(--Size-Links, 12px);
				}
			}
		</style>
		<?php
	}

	/**
	 * Get custom field value
	 *
	 * @param int $product_id Product ID
	 * @param string $field_key Field key
	 * @return string Field value
	 */
	public static function get_custom_field( $product_id, $field_key ) {
		return get_post_meta( $product_id, $field_key, true );
	}

	/**
	 * Get all custom fields for a product
	 *
	 * @param int $product_id Product ID
	 * @return array Array of field values
	 */
	public static function get_all_custom_fields( $product_id ) {
		return array(
			'product_sheet_url' => self::get_custom_field( $product_id, '_product_sheet_url' ),
			'installation_sheet_url' => self::get_custom_field( $product_id, '_installation_sheet_url' ),
			'book_installer_url' => self::get_custom_field( $product_id, '_book_installer_url' ),
			'installation_video_url' => self::get_custom_field( $product_id, '_installation_video_url' ),
			'product_warranty_url' => self::get_custom_field( $product_id, '_product_warranty_url' ),
		);
	}

	/**
	 * Enqueue contact page script for installer booking
	 */
	public function enqueue_contact_page_script() {
		// Only load on the contact page
		if ( ! is_page( 'contact-us' ) ) {
			return;
		}

		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				// Get the query string parameter "service"
				const urlParams = new URLSearchParams(window.location.search);
				const service = urlParams.get('service');

				// If service = installer_booking, check the radio
				if (service === 'installer_booking') {
					$('#wpforms-10338-field_8_2').prop('checked', true);
				}
			});
		</script>
		<?php
	}
}



// Initialize the class
new Product_Resources();