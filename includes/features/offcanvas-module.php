<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Generic Off-Canvas Module for Blocksy Child Theme
 *
 * This module provides a reusable off-canvas panel system that integrates
 * with Blocksy's native off-canvas functionality. You can easily create
 * custom off-canvas panels for any purpose (notifications, filters, etc.)
 *
 * @package BlocksyChild
 * @since 1.0.0
 *
 * @example
 * ```php
 * $my_offcanvas = new BlocksyChildOffcanvasModule([
 *     'id' => 'my-panel',
 *     'title' => 'My Panel',
 *     'icon' => '<svg>...</svg>',
 *     'content_callback' => 'my_content_function',
 *     'position' => 'right-side',
 *     'width' => ['desktop' => '400px', 'tablet' => '350px', 'mobile' => '300px']
 * ]);
 * ```
 */

/**
 * Main Off-Canvas Module Class
 *
 * @since 1.0.0
 */
class BlocksyChildOffcanvasModule {

	/**
	 * Unique identifier for this offcanvas instance.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Panel title.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Icon HTML or SVG.
	 *
	 * @var string
	 */
	private $icon;

	/**
	 * Content callback function.
	 *
	 * @var callable
	 */
	private $content_callback;

	/**
	 * Panel position (left-side or right-side).
	 *
	 * @var string
	 */
	private $position;

	/**
	 * Panel width settings.
	 *
	 * @var array
	 */
	private $width;

	/**
	 * CSS selector for trigger elements.
	 *
	 * @var string
	 */
	private $trigger_selector;

	/**
	 * AJAX action name for dynamic content loading.
	 *
	 * @var string|null
	 */
	private $ajax_action;

	/**
	 * Additional CSS classes for the panel.
	 *
	 * @var string
	 */
	private $panel_class;

	/**
	 * Show count badge in header.
	 *
	 * @var bool
	 */
	private $show_count;

	/**
	 * Count callback function.
	 *
	 * @var callable|null
	 */
	private $count_callback;

	/**
	 * Renderer instance.
	 *
	 * @var BlocksyChildOffcanvasRenderer
	 */
	private $renderer;

	/**
	 * Close icon type.
	 *
	 * @var string
	 */
	private $close_icon_type;

	/**
	 * ARIA label for the panel.
	 *
	 * @var string
	 */
	private $aria_label;

	/**
	 * Default configuration values.
	 */
	const DEFAULTS = array(
		'position' => 'right-side',
		'width' => array(
			'desktop' => '500px',
			'tablet' => '65vw',
			'mobile' => '90vw',
		),
		'trigger_selector' => '',
		'ajax_action' => null,
		'panel_class' => '',
		'show_count' => false,
		'count_callback' => null,
		'icon' => '',
		'close_icon_type' => 'type-1',
		'aria_label' => '',
	);

	/**
	 * Constructor.
	 *
	 * @param array $args Configuration arguments.
	 * @throws InvalidArgumentException If required parameters are missing.
	 */
	public function __construct( $args = array() ) {
		// Validate required parameters
		if ( empty( $args['id'] ) ) {
			throw new InvalidArgumentException( 'Offcanvas module requires an "id" parameter.' );
		}

		if ( empty( $args['title'] ) ) {
			throw new InvalidArgumentException( 'Offcanvas module requires a "title" parameter.' );
		}

		if ( empty( $args['content_callback'] ) && empty( $args['ajax_action'] ) ) {
			throw new InvalidArgumentException( 'Offcanvas module requires either "content_callback" or "ajax_action" parameter.' );
		}

		// Merge with defaults
		$args = wp_parse_args( $args, self::DEFAULTS );

		// Set properties
		$this->id = sanitize_key( $args['id'] );
		$this->title = sanitize_text_field( $args['title'] );
		$this->icon = $args['icon'];
		$this->content_callback = $args['content_callback'];
		$this->position = in_array( $args['position'], array( 'left-side', 'right-side', 'modal' ) ) ? $args['position'] : 'right-side';
		$this->width = wp_parse_args( $args['width'], self::DEFAULTS['width'] );
		$this->trigger_selector = $args['trigger_selector'];
		$this->ajax_action = $args['ajax_action'];
		$this->panel_class = sanitize_html_class( $args['panel_class'] );
		$this->show_count = (bool) $args['show_count'];
		$this->count_callback = $args['count_callback'];
		$this->close_icon_type = in_array( $args['close_icon_type'], array( 'type-1', 'type-2', 'type-3' ) ) ? $args['close_icon_type'] : 'type-1';
		$this->aria_label = ! empty( $args['aria_label'] ) ? sanitize_text_field( $args['aria_label'] ) : $this->title;

		// Initialize renderer
		$this->renderer = new BlocksyChildOffcanvasRenderer( $this );

		// Initialize hooks
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Add off-canvas panel to footer
		add_filter( 'blocksy:footer:offcanvas-drawer', array( $this, 'add_offcanvas_to_footer' ), 10, 2 );

		// Enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Add custom CSS for width settings
		add_action( 'wp_head', array( $this, 'add_custom_css' ) );

		// Register AJAX handlers if ajax_action is set
		if ( $this->ajax_action ) {
			add_action( 'wp_ajax_' . $this->ajax_action, array( $this, 'ajax_load_content' ) );
			add_action( 'wp_ajax_nopriv_' . $this->ajax_action, array( $this, 'ajax_load_content' ) );
		}
	}

	/**
	 * Add off-canvas panel to footer.
	 *
	 * @param array $elements Existing elements.
	 * @param array $payload Payload data.
	 * @return array Modified elements.
	 */
	public function add_offcanvas_to_footer( $elements, $payload ) {
		if ( $payload['location'] !== 'start' ) {
			return $elements;
		}

		$elements[] = $this->renderer->render();
		return $elements;
	}

	/**
	 * Enqueue module assets.
	 */
	public function enqueue_assets() {
		$theme_version = wp_get_theme()->get( 'Version' );

		// Enqueue CSS
		wp_enqueue_style(
			'offcanvas-module',
			get_stylesheet_directory_uri() . '/assets/css/offcanvas-module.css',
			array(),
			$theme_version
		);

		// Enqueue JavaScript
		wp_enqueue_script(
			'offcanvas-module',
			get_stylesheet_directory_uri() . '/assets/js/offcanvas-module.js',
			array( 'jquery' ),
			$theme_version,
			true
		);

		// Localize script with configuration
		wp_localize_script(
			'offcanvas-module',
			'offcanvasModuleConfig',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'offcanvas_module_nonce' ),
				'instances' => array(
					$this->id => array(
						'ajaxAction' => $this->ajax_action,
						'triggerSelector' => $this->trigger_selector,
					),
				),
			)
		);
	}

	/**
	 * Add custom CSS for panel width using CSS variables (Blocksy standard).
	 */
	public function add_custom_css() {
		?>
		<style id="offcanvas-<?php echo esc_attr( $this->id ); ?>-css">
			/* Panel Width using CSS Variables - Blocksy Standard */
			#<?php echo esc_attr( $this->id ); ?>-panel {
				--side-panel-width:
					<?php echo esc_attr( $this->width['desktop'] ); ?>
				;
			}

			/* Tablet */
			@media (max-width: 999px) {
				#<?php echo esc_attr( $this->id ); ?>-panel {
					--side-panel-width:
						<?php echo esc_attr( $this->width['tablet'] ); ?>
					;
				}
			}

			/* Mobile */
			@media (max-width: 689px) {
				#<?php echo esc_attr( $this->id ); ?>-panel {
					--side-panel-width:
						<?php echo esc_attr( $this->width['mobile'] ); ?>
					;
				}
			}
		</style>
		<?php
	}

	/**
	 * AJAX handler for loading content.
	 */
	public function ajax_load_content() {
		// Verify nonce
		check_ajax_referer( 'offcanvas_module_nonce', 'nonce' );

		$content = '';
		$count = 0;

		// Get content from callback
		if ( is_callable( $this->content_callback ) ) {
			$content = call_user_func( $this->content_callback );
		}

		// Get count if callback is set
		if ( $this->show_count && is_callable( $this->count_callback ) ) {
			$count = call_user_func( $this->count_callback );
		}

		wp_send_json_success(
			array(
				'content' => $content,
				'count' => $count,
			)
		);
	}

	/**
	 * Get module ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get module title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get module icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return $this->icon;
	}

	/**
	 * Get panel position.
	 *
	 * @return string
	 */
	public function get_position() {
		return $this->position;
	}

	/**
	 * Get panel class.
	 *
	 * @return string
	 */
	public function get_panel_class() {
		return $this->panel_class;
	}

	/**
	 * Get content from callback.
	 *
	 * @return string
	 */
	public function get_content() {
		if ( is_callable( $this->content_callback ) ) {
			return call_user_func( $this->content_callback );
		}
		return '';
	}

	/**
	 * Get count value.
	 *
	 * @return int
	 */
	public function get_count() {
		if ( $this->show_count && is_callable( $this->count_callback ) ) {
			return (int) call_user_func( $this->count_callback );
		}
		return 0;
	}

	/**
	 * Check if count should be shown.
	 *
	 * @return bool
	 */
	public function should_show_count() {
		return $this->show_count;
	}

	/**
	 * Get close icon type.
	 *
	 * @return string
	 */
	public function get_close_icon_type() {
		return $this->close_icon_type;
	}

	/**
	 * Get ARIA label.
	 *
	 * @return string
	 */
	public function get_aria_label() {
		return $this->aria_label;
	}

	/**
	 * Get panel width settings.
	 *
	 * @return array
	 */
	public function get_width() {
		return $this->width;
	}
}

/**
 * Off-Canvas Renderer Class
 *
 * Handles HTML rendering for off-canvas panels.
 *
 * @since 1.0.0
 */
class BlocksyChildOffcanvasRenderer {

	/**
	 * Module instance.
	 *
	 * @var BlocksyChildOffcanvasModule
	 */
	private $module;

	/**
	 * Constructor.
	 *
	 * @param BlocksyChildOffcanvasModule $module Module instance.
	 */
	public function __construct( $module ) {
		$this->module = $module;
	}

	/**
	 * Render the complete off-canvas panel.
	 *
	 * @param array $args Optional arguments.
	 * @return string HTML output.
	 */
	public function render( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'has_container' => true,
			)
		);

		$content = $this->get_panel_content();
		$without_container = '<div class="ct-panel-content" data-device="desktop"><div class="ct-panel-content-inner">' . $content . '</div></div>';

		if ( ! $args['has_container'] ) {
			return $without_container;
		}

		$panel_id = $this->module->get_id() . '-panel';
		$behavior = $this->module->get_position();
		$close_icon = $this->get_close_icon_svg();
		$icon_html = $this->get_icon_html();
		$title = esc_html( $this->module->get_title() );
		$count_html = $this->get_count_html();
		$panel_class = $this->module->get_panel_class();
		$aria_label = $this->module->get_aria_label();
		$close_label = sprintf( __( 'Close %s', 'blocksy' ), strtolower( $this->module->get_title() ) );
		$close_icon_type = $this->module->get_close_icon_type();

		$classes = array( 'ct-panel', 'ct-offcanvas-module' );
		if ( ! empty( $panel_class ) ) {
			$classes[] = $panel_class;
		}

		return sprintf(
			'<div id="%s" class="%s" data-behaviour="%s" role="dialog" aria-label="%s" inert>
				<div class="ct-panel-inner">
					<div class="ct-panel-actions">
						<span class="ct-panel-heading">%s%s%s</span>
						<button class="ct-toggle-close" data-type="%s" aria-label="%s">%s</button>
					</div>
					%s
				</div>
			</div>',
			esc_attr( $panel_id ),
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $behavior ),
			esc_attr( $aria_label ),
			$icon_html,
			$title,
			$count_html,
			esc_attr( $close_icon_type ),
			esc_attr( $close_label ),
			$close_icon,
			$without_container
		);
	}

	/**
	 * Get panel content.
	 *
	 * @return string HTML content.
	 */
	private function get_panel_content() {
		return $this->module->get_content();
	}

	/**
	 * Get icon HTML.
	 *
	 * @return string Icon HTML.
	 */
	private function get_icon_html() {
		$icon = $this->module->get_icon();
		if ( empty( $icon ) ) {
			return '';
		}

		return '<span class="ct-panel-heading-icon">' . $icon . '</span> ';
	}

	/**
	 * Get count HTML.
	 *
	 * @return string Count HTML.
	 */
	private function get_count_html() {
		if ( ! $this->module->should_show_count() ) {
			return '';
		}

		$count = $this->module->get_count();
		return sprintf( ' <span class="offcanvas-count">(%d)</span>', $count );
	}

	/**
	 * Get close icon SVG.
	 *
	 * @return string SVG markup.
	 */
	private function get_close_icon_svg() {
		// Blocksy standard close icon
		$icon = '<svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15">
			<path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/>
		</svg>';

		// Allow filtering for custom close icons
		return apply_filters( 'blocksy:main:offcanvas:close:icon', $icon, $this->module->get_close_icon_type() );
	}
}

