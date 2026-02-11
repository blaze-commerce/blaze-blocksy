<?php
/**
 * Product Custom Tabs Metabox
 *
 * Adds per-product custom tabs functionality:
 * - Admin can add custom product tabs for each product
 * - Tab fields: Title (text), Content (WYSIWYG)
 * - Drag and drop priority sorting
 *
 * @package BlazeBlocksy
 * @since 1.65.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Custom Tabs Metabox Class
 */
class BlazeBlocksy_Product_Custom_Tabs {

	/**
	 * Meta key for storing custom tabs
	 */
	const META_KEY = '_blaze_custom_product_tabs';

	/**
	 * Constructor
	 */
	public function __construct() {
		// Add metabox to product edit page
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		// Save metabox data
		add_action( 'save_post_product', array( $this, 'save_meta_box' ), 10, 2 );

		// Enqueue admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Filter WooCommerce product tabs to add custom tabs
		add_filter( 'woocommerce_product_tabs', array( $this, 'add_custom_tabs_to_product' ), 98 );
	}

	/**
	 * Add meta box to product edit page
	 */
	public function add_meta_box() {
		add_meta_box(
			'blaze_custom_product_tabs',
			__( 'Custom Product Tabs', 'blaze-blocksy' ),
			array( $this, 'render_meta_box' ),
			'product',
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		global $post;

		// Only load on product edit pages
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		if ( ! $post || 'product' !== $post->post_type ) {
			return;
		}

		// Enqueue jQuery UI Sortable for drag and drop
		wp_enqueue_script( 'jquery-ui-sortable' );

		// Enqueue WordPress editor
		wp_enqueue_editor();

		// Enqueue our custom script
		$js_file = get_stylesheet_directory() . '/assets/js/admin-product-custom-tabs.js';
		if ( file_exists( $js_file ) ) {
			wp_enqueue_script(
				'blaze-product-custom-tabs-admin',
				get_stylesheet_directory_uri() . '/assets/js/admin-product-custom-tabs.js',
				array( 'jquery', 'jquery-ui-sortable', 'wp-editor' ),
				filemtime( $js_file ),
				true
			);

			wp_localize_script(
				'blaze-product-custom-tabs-admin',
				'blazeProductTabs',
				array(
					'i18n' => array(
						'confirmDelete' => __( 'Are you sure you want to delete this tab?', 'blaze-blocksy' ),
						'tabTitle'      => __( 'Tab Title', 'blaze-blocksy' ),
						'tabContent'    => __( 'Tab Content', 'blaze-blocksy' ),
						'deleteTab'     => __( 'Delete Tab', 'blaze-blocksy' ),
						'dragToReorder' => __( 'Drag to reorder', 'blaze-blocksy' ),
					),
				)
			);
		}

		// Enqueue our custom styles
		$css_file = get_stylesheet_directory() . '/assets/css/admin-product-custom-tabs.css';
		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				'blaze-product-custom-tabs-admin',
				get_stylesheet_directory_uri() . '/assets/css/admin-product-custom-tabs.css',
				array(),
				filemtime( $css_file )
			);
		}
	}

	/**
	 * Render meta box content
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_meta_box( $post ) {
		// Get saved tabs
		$tabs = get_post_meta( $post->ID, self::META_KEY, true );
		if ( ! is_array( $tabs ) ) {
			$tabs = array();
		}

		// Nonce field
		wp_nonce_field( 'blaze_custom_tabs_nonce', 'blaze_custom_tabs_nonce_field' );
		?>
		<div class="blaze-custom-tabs-wrapper">
			<p class="description">
				<?php esc_html_e( 'Add custom tabs to this product. Drag and drop to reorder tabs.', 'blaze-blocksy' ); ?>
			</p>

			<div id="blaze-custom-tabs-container" class="blaze-custom-tabs-container">
				<?php
				if ( ! empty( $tabs ) ) {
					foreach ( $tabs as $index => $tab ) {
						$this->render_tab_row( $index, $tab );
					}
				}
				?>
			</div>

			<button type="button" id="blaze-add-custom-tab" class="button button-primary">
				<span class="dashicons dashicons-plus-alt2"></span>
				<?php esc_html_e( 'Add New Tab', 'blaze-blocksy' ); ?>
			</button>

			<!-- Template for new tab (hidden) -->
			<script type="text/template" id="blaze-tab-template">
				<?php $this->render_tab_row( '{{INDEX}}', array( 'title' => '', 'content' => '' ) ); ?>
			</script>
		</div>
		<?php
	}

	/**
	 * Render a single tab row
	 *
	 * @param int|string $index Tab index.
	 * @param array      $tab   Tab data.
	 */
	private function render_tab_row( $index, $tab ) {
		$title   = isset( $tab['title'] ) ? $tab['title'] : '';
		$content = isset( $tab['content'] ) ? $tab['content'] : '';
		$editor_id = 'blaze_tab_content_' . $index;
		?>
		<div class="blaze-custom-tab-item" data-index="<?php echo esc_attr( $index ); ?>">
			<div class="blaze-tab-header">
				<span class="blaze-tab-drag-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'blaze-blocksy' ); ?>"></span>
				<span class="blaze-tab-number"><?php echo esc_html( is_numeric( $index ) ? $index + 1 : '#' ); ?></span>
				<input
					type="text"
					name="blaze_custom_tabs[<?php echo esc_attr( $index ); ?>][title]"
					value="<?php echo esc_attr( $title ); ?>"
					placeholder="<?php esc_attr_e( 'Tab Title', 'blaze-blocksy' ); ?>"
					class="blaze-tab-title-input"
				/>
				<button type="button" class="blaze-tab-toggle button button-small">
					<span class="dashicons dashicons-arrow-down-alt2"></span>
				</button>
				<button type="button" class="blaze-tab-delete button button-small button-link-delete">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>
			<div class="blaze-tab-content-wrapper">
				<label class="blaze-tab-content-label"><?php esc_html_e( 'Tab Content', 'blaze-blocksy' ); ?></label>
				<textarea
					name="blaze_custom_tabs[<?php echo esc_attr( $index ); ?>][content]"
					id="<?php echo esc_attr( $editor_id ); ?>"
					class="blaze-tab-content-textarea"
					rows="10"
				><?php echo esc_textarea( $content ); ?></textarea>
			</div>
		</div>
		<?php
	}

	/**
	 * Save meta box data
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ) {
		// Verify nonce
		if ( ! isset( $_POST['blaze_custom_tabs_nonce_field'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['blaze_custom_tabs_nonce_field'] ) ), 'blaze_custom_tabs_nonce' ) ) {
			return;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_product', $post_id ) ) {
			return;
		}

		// Get and sanitize tabs data
		$tabs = array();

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below
		$raw_tabs = isset( $_POST['blaze_custom_tabs'] ) ? $_POST['blaze_custom_tabs'] : array();

		if ( is_array( $raw_tabs ) ) {
			$index = 0;
			foreach ( $raw_tabs as $tab ) {
				if ( ! is_array( $tab ) ) {
					continue;
				}

				$title = isset( $tab['title'] ) ? sanitize_text_field( $tab['title'] ) : '';
				// Allow HTML in content (WYSIWYG)
				$content = isset( $tab['content'] ) ? wp_kses_post( $tab['content'] ) : '';

				// Only save if title is not empty
				if ( ! empty( $title ) ) {
					$tabs[ $index ] = array(
						'title'   => $title,
						'content' => $content,
					);
					$index++;
				}
			}
		}

		// Update or delete meta
		if ( ! empty( $tabs ) ) {
			update_post_meta( $post_id, self::META_KEY, $tabs );
		} else {
			delete_post_meta( $post_id, self::META_KEY );
		}
	}

	/**
	 * Add custom tabs to WooCommerce product tabs
	 *
	 * @param array $tabs Existing product tabs.
	 * @return array Modified product tabs.
	 */
	public function add_custom_tabs_to_product( $tabs ) {
		global $product;

		if ( ! $product ) {
			return $tabs;
		}

		$product_id = $product->get_id();
		$custom_tabs = get_post_meta( $product_id, self::META_KEY, true );

		if ( ! is_array( $custom_tabs ) || empty( $custom_tabs ) ) {
			return $tabs;
		}

		// Add custom tabs with priority based on their order
		$base_priority = 50; // Start after description (10), additional_information (20), reviews (30)

		foreach ( $custom_tabs as $index => $tab ) {
			if ( empty( $tab['title'] ) ) {
				continue;
			}

			$tab_key = 'blaze_custom_tab_' . $index;

			$tabs[ $tab_key ] = array(
				'title'    => $tab['title'],
				'priority' => $base_priority + ( $index * 5 ),
				'callback' => array( $this, 'render_custom_tab_content' ),
				'content'  => $tab['content'],
			);
		}

		return $tabs;
	}

	/**
	 * Render custom tab content on frontend
	 *
	 * @param string $tab_key Tab key.
	 * @param array  $tab     Tab data.
	 */
	public function render_custom_tab_content( $tab_key, $tab ) {
		if ( ! isset( $tab['content'] ) ) {
			return;
		}

		echo '<div class="blaze-custom-tab-content">';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is sanitized with wp_kses_post on save
		echo wpautop( do_shortcode( $tab['content'] ) );
		echo '</div>';
	}

	/**
	 * Get custom tabs for a product
	 *
	 * @param int $product_id Product ID.
	 * @return array Custom tabs.
	 */
	public static function get_product_custom_tabs( $product_id ) {
		$tabs = get_post_meta( $product_id, self::META_KEY, true );
		return is_array( $tabs ) ? $tabs : array();
	}
}

// Initialize
new BlazeBlocksy_Product_Custom_Tabs();
