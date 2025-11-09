<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Example: Notification Off-Canvas Panel
 *
 * This file demonstrates how to use the generic offcanvas module
 * to create a custom notification panel.
 *
 * @package BlocksyChild
 * @since 1.0.0
 */

/**
 * Initialize Notification Off-Canvas
 */
function blocksy_child_init_notification_offcanvas() {
	// Make sure the offcanvas module is loaded
	if ( ! class_exists( 'BlocksyChildOffcanvasModule' ) ) {
		return;
	}

	try {
		// Create notification offcanvas instance
		new BlocksyChildOffcanvasModule(
			array(
				'id'               => 'notifications',
				'title'            => __( 'Notifications', 'blocksy' ),
				'icon'             => blocksy_child_get_notification_icon(),
				'content_callback' => 'blocksy_child_get_notification_content',
				'position'         => 'right-side',
				'width'            => array(
					'desktop' => '420px',
					'tablet'  => '380px',
					'mobile'  => '320px',
				),
				'trigger_selector' => '.notification-trigger',
				'show_count'       => true,
				'count_callback'   => 'blocksy_child_get_notification_count',
				'panel_class'      => 'ct-notification-panel',
			)
		);
	} catch ( Exception $e ) {
		error_log( 'Failed to initialize notification offcanvas: ' . $e->getMessage() );
	}
}
add_action( 'init', 'blocksy_child_init_notification_offcanvas' );

/**
 * Get notification icon SVG.
 *
 * @return string SVG icon.
 */
function blocksy_child_get_notification_icon() {
	return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
		<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
		<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
	</svg>';
}

/**
 * Get notification content HTML.
 *
 * @return string HTML content.
 */
function blocksy_child_get_notification_content() {
	$notifications = blocksy_child_get_user_notifications();

	if ( empty( $notifications ) ) {
		return blocksy_child_get_empty_notifications_html();
	}

	$html = '<div class="notification-list">';

	foreach ( $notifications as $notification ) {
		$html .= blocksy_child_render_notification_item( $notification );
	}

	$html .= '</div>';

	// Add mark all as read button
	if ( count( $notifications ) > 0 ) {
		$html .= '<div class="notification-actions">';
		$html .= '<button class="button mark-all-read">' . esc_html__( 'Mark All as Read', 'blocksy' ) . '</button>';
		$html .= '</div>';
	}

	return $html;
}

/**
 * Get empty notifications HTML.
 *
 * @return string HTML content.
 */
function blocksy_child_get_empty_notifications_html() {
	return '<div class="notification-empty">
		<div class="notification-empty-icon">
			<svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
				<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
				<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
			</svg>
		</div>
		<h3>' . esc_html__( 'No Notifications', 'blocksy' ) . '</h3>
		<p>' . esc_html__( 'You\'re all caught up! Check back later for updates.', 'blocksy' ) . '</p>
	</div>';
}

/**
 * Render a single notification item.
 *
 * @param array $notification Notification data.
 * @return string HTML content.
 */
function blocksy_child_render_notification_item( $notification ) {
	$unread_class = ! empty( $notification['unread'] ) ? 'unread' : '';
	$time_ago     = human_time_diff( $notification['timestamp'], current_time( 'timestamp' ) );

	return sprintf(
		'<div class="notification-item %s" data-id="%s">
			<div class="notification-icon">%s</div>
			<div class="notification-content">
				<h4 class="notification-title">%s</h4>
				<p class="notification-message">%s</p>
				<span class="notification-time">%s</span>
			</div>
			%s
		</div>',
		esc_attr( $unread_class ),
		esc_attr( $notification['id'] ),
		blocksy_child_get_notification_type_icon( $notification['type'] ),
		esc_html( $notification['title'] ),
		esc_html( $notification['message'] ),
		sprintf( esc_html__( '%s ago', 'blocksy' ), $time_ago ),
		! empty( $notification['unread'] ) ? '<span class="notification-badge"></span>' : ''
	);
}

/**
 * Get notification type icon.
 *
 * @param string $type Notification type.
 * @return string SVG icon.
 */
function blocksy_child_get_notification_type_icon( $type ) {
	$icons = array(
		'order'   => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>',
		'message' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>',
		'alert'   => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>',
		'success' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
	);

	return isset( $icons[ $type ] ) ? $icons[ $type ] : $icons['message'];
}

/**
 * Get user notifications (demo data).
 *
 * In a real implementation, this would fetch from database or API.
 *
 * @return array Notifications.
 */
function blocksy_child_get_user_notifications() {
	// Demo data - replace with actual database query
	return array(
		array(
			'id'        => 1,
			'type'      => 'order',
			'title'     => __( 'Order Shipped', 'blocksy' ),
			'message'   => __( 'Your order #12345 has been shipped and is on its way!', 'blocksy' ),
			'timestamp' => current_time( 'timestamp' ) - 3600, // 1 hour ago
			'unread'    => true,
		),
		array(
			'id'        => 2,
			'type'      => 'message',
			'title'     => __( 'New Message', 'blocksy' ),
			'message'   => __( 'You have a new message from customer support.', 'blocksy' ),
			'timestamp' => current_time( 'timestamp' ) - 7200, // 2 hours ago
			'unread'    => true,
		),
		array(
			'id'        => 3,
			'type'      => 'success',
			'title'     => __( 'Payment Confirmed', 'blocksy' ),
			'message'   => __( 'Your payment has been successfully processed.', 'blocksy' ),
			'timestamp' => current_time( 'timestamp' ) - 86400, // 1 day ago
			'unread'    => false,
		),
	);
}

/**
 * Get notification count.
 *
 * @return int Number of unread notifications.
 */
function blocksy_child_get_notification_count() {
	$notifications = blocksy_child_get_user_notifications();
	$unread_count  = 0;

	foreach ( $notifications as $notification ) {
		if ( ! empty( $notification['unread'] ) ) {
			$unread_count++;
		}
	}

	return $unread_count;
}

/**
 * Add notification trigger button to header (optional).
 *
 * This is just an example of how to add a trigger button.
 * You can add it anywhere in your theme.
 */
function blocksy_child_add_notification_trigger() {
	$count = blocksy_child_get_notification_count();
	?>
	<div class="notification-trigger-wrapper">
		<a href="#notifications-panel" class="notification-trigger" data-offcanvas-trigger="notifications-panel">
			<?php echo blocksy_child_get_notification_icon(); ?>
			<?php if ( $count > 0 ) : ?>
				<span class="notification-badge-count"><?php echo esc_html( $count ); ?></span>
			<?php endif; ?>
		</a>
	</div>
	<?php
}

/**
 * Enqueue notification-specific styles.
 */
function blocksy_child_enqueue_notification_styles() {
	wp_add_inline_style(
		'offcanvas-module',
		'
		/* Notification Panel Styles */
		.notification-list {
			display: flex;
			flex-direction: column;
			gap: 12px;
		}

		.notification-item {
			display: flex;
			gap: 12px;
			padding: 16px;
			background: #f9fafb;
			border-radius: 8px;
			border-left: 3px solid transparent;
			position: relative;
			transition: all 0.2s ease;
		}

		.notification-item:hover {
			background: #f3f4f6;
		}

		.notification-item.unread {
			background: #eff6ff;
			border-left-color: #3b82f6;
		}

		.notification-icon {
			flex-shrink: 0;
			width: 40px;
			height: 40px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #fff;
			border-radius: 50%;
			color: #6b7280;
		}

		.notification-item.unread .notification-icon {
			color: #3b82f6;
		}

		.notification-content {
			flex: 1;
		}

		.notification-title {
			margin: 0 0 4px 0;
			font-size: 14px;
			font-weight: 600;
			color: #111827;
		}

		.notification-message {
			margin: 0 0 8px 0;
			font-size: 13px;
			color: #6b7280;
			line-height: 1.5;
		}

		.notification-time {
			font-size: 12px;
			color: #9ca3af;
		}

		.notification-badge {
			position: absolute;
			top: 12px;
			right: 12px;
			width: 8px;
			height: 8px;
			background: #3b82f6;
			border-radius: 50%;
		}

		.notification-actions {
			margin-top: 16px;
			padding-top: 16px;
			border-top: 1px solid #e5e7eb;
		}

		.notification-actions .button {
			width: 100%;
		}

		.notification-empty {
			text-align: center;
			padding: 40px 20px;
		}

		.notification-empty-icon {
			margin-bottom: 16px;
			color: #d1d5db;
		}

		.notification-empty h3 {
			margin: 0 0 8px 0;
			font-size: 18px;
			color: #111827;
		}

		.notification-empty p {
			margin: 0;
			color: #6b7280;
		}

		/* Trigger button styles */
		.notification-trigger {
			position: relative;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 40px;
			height: 40px;
			color: inherit;
			text-decoration: none;
		}

		.notification-badge-count {
			position: absolute;
			top: 0;
			right: 0;
			min-width: 18px;
			height: 18px;
			padding: 0 4px;
			background: #ef4444;
			color: #fff;
			font-size: 11px;
			font-weight: 600;
			border-radius: 9px;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		'
	);
}
add_action( 'wp_enqueue_scripts', 'blocksy_child_enqueue_notification_styles' );

