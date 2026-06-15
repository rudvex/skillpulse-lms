<?php
/**
 * Notifications Template
 *
 * This template can be overridden by copying it to:
 * your-theme/skillpulse-lms/dashboard/notifications.php
 *
 * @package SPLMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract variables from args.
if ( is_array( $args ) ) {
	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template file uses extract for convenience.
	extract( $args );
}
?>
<div class="splms-dashboard-tab splms-notifications-tab">

	<!-- Page Header -->
	<div class="splms-section-title" style="margin-bottom: 1.5rem;">
		<i class="hgi-stroke hgi-notification-03"></i>
		<?php esc_html_e( 'Notifications', 'skillpulse-lms' ); ?>
	</div>

	<!-- Notifications Content -->
	<div class="splms-notifications-content">

		<!-- Filter Controls -->
		<div class="splms-notifications-controls">
			<div class="splms-filter-tabs">
				<button type="button" class="splms-filter-tab active" data-filter="unread">
					<i class="hgi-stroke hgi-notification-02"></i>
					<?php esc_html_e( 'Unread', 'skillpulse-lms' ); ?>
				</button>
				<button type="button" class="splms-filter-tab" data-filter="read">
					<i class="hgi-stroke hgi-notification-off-02"></i>
					<?php esc_html_e( 'Read', 'skillpulse-lms' ); ?>
				</button>
			</div>
			<div class="splms-notifications-actions">
				<button type="button" class="splms-btn splms-btn-secondary splms-btn-sm" id="splms-mark-all-notifications-read">
					<i class="hgi-stroke hgi-tick-02"></i>
					<?php esc_html_e( 'Mark All as Read', 'skillpulse-lms' ); ?>
				</button>
			</div>
		</div>

		<!-- Notifications List Container -->
		<div class="splms-notifications-container" id="splms-dashboard-notifications-content">
			<div class="splms-loading-state">
				<div class="splms-spinner"></div>
				<p><?php esc_html_e( 'Loading notifications...', 'skillpulse-lms' ); ?></p>
			</div>
		</div>

	</div>
</div>

<!-- JavaScript Templates -->
<script type="text/html" id="tmpl-splms-dashboard-notifications">
	<div class="splms-notifications-list">
		<# _.each( data.notifications, function( notification ) { #>
			<div class="splms-notification-item {{ notification.is_read ? 'read' : 'unread' }}" data-notification-id="{{ notification.id }}">
				<div class="splms-notification-card">
					<div class="splms-notification-indicator">
						<div class="splms-notification-icon {{ notification.is_read ? 'read' : 'unread' }}">
							<i class="hgi-stroke hgi-notification-02"></i>
						</div>
					</div>
					<div class="splms-notification-content">
						<div class="splms-notification-header">
							<h4 class="splms-notification-title">{{ notification.title || 'Notification' }}</h4>
							<span class="splms-notification-time">{{ notification.time_ago }}</span>
						</div>
						<div class="splms-notification-body">
							<p class="splms-notification-message">{{{ notification.message }}}</p>
						</div>
					</div>
					<div class="splms-notification-actions">
						<div class="splms-dropdown">
							<button type="button" class="splms-dropdown-toggle" data-notification-id="{{ notification.id }}">
								<i class="hgi-stroke hgi-more-horizontal"></i>
							</button>
							<div class="splms-dropdown-menu">
								<# if ( notification.is_read ) { #>
									<button type="button" class="splms-dropdown-item" data-action="mark-unread" data-id="{{ notification.id }}">
										<i class="hgi-stroke hgi-notification-02"></i>
										<?php esc_html_e( 'Mark as unread', 'skillpulse-lms' ); ?>
									</button>
								<# } else { #>
									<button type="button" class="splms-dropdown-item" data-action="mark-read" data-id="{{ notification.id }}">
										<i class="hgi-stroke hgi-tick-02"></i>
										<?php esc_html_e( 'Mark as read', 'skillpulse-lms' ); ?>
									</button>
								<# } #>
								<button type="button" class="splms-dropdown-item text-danger" data-action="delete" data-id="{{ notification.id }}">
									<i class="hgi-stroke hgi-delete-02"></i>
									<?php esc_html_e( 'Delete notification', 'skillpulse-lms' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		<# }); #>
	</div>
</script>

<script type="text/html" id="tmpl-splms-dashboard-notifications-empty">
	<div class="splms-empty-state">
		<div class="splms-empty-state-icon">
			<i class="hgi-stroke hgi-notification-off-02"></i>
		</div>
		<div class="splms-empty-state-content">
			<h3><?php esc_html_e( 'No notifications yet', 'skillpulse-lms' ); ?></h3>
			<p><?php esc_html_e( 'We\'ll notify you when something happens.', 'skillpulse-lms' ); ?></p>
		</div>
	</div>
</script>


