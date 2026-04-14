/**
 * Dashboard Notifications Tab
 *
 * Handles notification loading, rendering, filtering, and actions
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

(function($) {
	'use strict';

	const DashboardNotifications = {
		currentFilter: 'unread',

		init: function() {
			// Only initialize if notifications tab is present
			if ($('.splms-notifications-tab').length === 0) {
				return;
			}

			// Ensure wp.template is available
			if (typeof wp === 'undefined' || typeof wp.template === 'undefined') {
				console.error('WordPress wp.template is required for dashboard notifications');
				return;
			}

			this.bindEvents();
			this.loadNotifications();
		},

		bindEvents: function() {
			const self = this;

			// Handle filter tabs
			$(document).on('click', '.splms-filter-tab', function(e) {
				e.preventDefault();
				const $tab = $(this);
				const filter = $tab.data('filter');

				// Update active state
				$('.splms-filter-tab').removeClass('active');
				$tab.addClass('active');

				// Update current filter
				self.currentFilter = filter;

				// Reload notifications with filter
				self.loadNotifications();
			});

			// Handle "Mark all as read" button
			$('#splms-mark-all-notifications-read').on('click', function(e) {
				e.preventDefault();
				self.handleMarkAllAsRead();
			});

			// Setup menu handlers (only called once, uses event delegation for dynamic elements)
			self.setupMenuHandlers();
		},

		setupMenuHandlers: function() {
			const self = this;

			// Remove any existing handlers first to prevent duplicates
			$(document).off('click.splms-notification-menu');

			// Close all menus when clicking outside
			$(document).on('click.splms-notification-menu', function(e) {
				if (!$(e.target).closest('.splms-dropdown').length) {
					$('.splms-dropdown-menu').removeClass('show');
				}
			});

			// Toggle dropdown menu on button click
			$(document).on('click.splms-notification-menu', '.splms-dropdown-toggle', function(e) {
				e.preventDefault();
				e.stopPropagation();

				const $btn = $(this);
				const $dropdown = $btn.closest('.splms-dropdown');
				const $menu = $dropdown.find('.splms-dropdown-menu');

				// Close all other menus
				$('.splms-dropdown-menu').not($menu).removeClass('show');

				// Toggle current menu
				$menu.toggleClass('show');
			});

			// Handle dropdown item clicks
			$(document).on('click.splms-notification-menu', '.splms-dropdown-item', function(e) {
				e.preventDefault();
				e.stopPropagation();

				const $item = $(this);
				const action = $item.data('action');
				const notificationId = $item.data('id');
				const $notificationItem = $('.splms-notification-item[data-notification-id="' + notificationId + '"]');

				// Close menu
				$('.splms-dropdown-menu').removeClass('show');

				if (action === 'mark-read') {
					self.markNotificationAsRead(notificationId, $notificationItem);
				} else if (action === 'mark-unread') {
					self.markNotificationAsUnread(notificationId, $notificationItem);
				} else if (action === 'delete') {
					self.deleteNotification(notificationId, $notificationItem);
				}
			});
		},

		loadNotifications: function() {
			const self = this;
			const $container = $('#splms-dashboard-notifications-content');

			$container.html('<div class="splms-spinner"></div>');

			// Get dashboard object
			const dashboard = window.splms_dashboard;
			if (!dashboard || !dashboard.ajax_url || !dashboard.nonces?.splms_frontend_nonce) {
				console.error('SPLMS: No valid AJAX data available for notifications.');
				self.renderError(self.getLocalizedString('failed_to_load_ajax_missing') || 'Failed to load notifications: AJAX data missing.');
				return;
			}

			// Get filter parameter
			let isReadParam = null;
			if (self.currentFilter === 'unread') {
				isReadParam = '0';
			} else if (self.currentFilter === 'read') {
				isReadParam = '1';
			}

			$.ajax({
				url: dashboard.ajax_url,
				type: 'POST',
				data: {
					action: 'splms_get_notifications',
					nonce: dashboard.nonces.splms_frontend_nonce,
					limit: 100,
					offset: 0,
					is_read: isReadParam
				},
				success: function(response) {
					console.log('Notifications AJAX response:', response);
					if (response.success && response.data && response.data.notifications && response.data.notifications.length > 0) {
						self.renderNotifications(response.data.notifications);
					} else {
						console.log('No notifications found or invalid response structure');
						self.renderEmptyState();
					}
				},
				error: function(xhr, status, error) {
					console.error('Notifications load error:', { xhr, status, error });
					console.error('Response text:', xhr.responseText);
					self.renderError(self.getLocalizedString('network_error') || 'Network error. Please try again.');
				}
			});
		},

		renderNotifications: function(notifications) {
			const self = this;
			const $container = $('#splms-dashboard-notifications-content');

			// Get current user avatar
			const userAvatar = this.getUserAvatar();

			// Format notifications for template
			const formattedNotifications = notifications.map(function(notification) {
				// Combine title and message for cleaner display
				let fullMessage = '';
				if (notification.title) {
					fullMessage = notification.title;
					if (notification.message) {
						fullMessage += ' ' + notification.message;
					}
				} else {
					fullMessage = notification.message || '';
				}

				return {
					id: notification.id,
					message: fullMessage,
					is_read: notification.is_read || false,
					time_ago: self.formatTimeAgo(notification.created_at),
					url: self.getNotificationUrl(notification),
					avatar: userAvatar
				};
			});

			// All notifications are returned from server, client-side filtering is handled by template
			let filteredNotifications = formattedNotifications;

			if (filteredNotifications.length === 0) {
				self.renderEmptyState();
				return;
			}

			const notificationsTemplate = wp.template('splms-dashboard-notifications');
			const templateData = {
				notifications: filteredNotifications
			};

			const html = notificationsTemplate(templateData);
			$container.html(html);

			// Setup click handlers for notifications
			self.setupNotificationHandlers();

			// Update filter button visibility
			self.updateFilterButtons();
		},

		renderEmptyState: function() {
			const $container = $('#splms-dashboard-notifications-content');
			const emptyTemplate = wp.template('splms-dashboard-notifications-empty');
			const html = emptyTemplate({});
			$container.html(html);
		},

		renderError: function(message) {
			const $container = $('#splms-dashboard-notifications-content');
			const html = '<div class="splms-alert splms-alert-error">' + message + '</div>';
			$container.html(html);
		},

		formatTimeAgo: function(dateString) {
			if (!dateString) return '';

			const date = new Date(dateString);
			const now = new Date();
			const diff = Math.floor((now - date) / 1000); // difference in seconds

			if (diff < 60) {
				return this.getLocalizedString('just_now') || 'Just now';
			} else if (diff < 3600) {
				const minutes = Math.floor(diff / 60);
				return minutes + ' ' + (this.getLocalizedString('minutes_ago') || 'minutes ago');
			} else if (diff < 86400) {
				const hours = Math.floor(diff / 3600);
				return hours + ' ' + (this.getLocalizedString('hours_ago') || 'hours ago');
			} else if (diff < 604800) {
				const days = Math.floor(diff / 86400);
				return days + ' ' + (this.getLocalizedString('days_ago') || 'days ago');
			} else {
				return date.toLocaleDateString();
			}
		},

		getNotificationUrl: function(notification) {
			// Check if meta has course_url
			if (notification.meta && notification.meta.course_url) {
				return notification.meta.course_url;
			}

			// Build URL from course_id or related_id
			if (notification.course_id) {
				return this.getBaseUrl() + '/courses/' + notification.course_id;
			} else if (notification.related_id && notification.related_type === 'course') {
				return this.getBaseUrl() + '/courses/' + notification.related_id;
			}

			return '#';
		},

		getBaseUrl: function() {
			// Try to get base URL from localized data or use window.location.origin
			const dashboard = window.splms_dashboard;
			if (dashboard && dashboard.base_url) {
				return dashboard.base_url;
			}
			return window.location.origin;
		},

		getUserAvatar: function() {
			// Try to get avatar from localized data or use default
			const dashboard = window.splms_dashboard;
			if (dashboard && dashboard.user_avatar) {
				return dashboard.user_avatar;
			}
			// Fallback to default avatar
			return '';
		},

		getLocalizedString: function(key) {
			// Try to get localized strings from various sources
			const dashboard = window.splms_dashboard;
			if (dashboard && dashboard.strings && dashboard.strings[key]) {
				return dashboard.strings[key];
			}
			return null;
		},

		setupNotificationHandlers: function() {
			// No specific handlers needed since we removed the action buttons
		},

		markNotificationAsRead: function(notificationId, $item) {
			const self = this;
			const dashboard = window.splms_dashboard;
			if (!dashboard || !dashboard.ajax_url || !dashboard.nonces?.splms_frontend_nonce) {
				return;
			}

			$.ajax({
				url: dashboard.ajax_url,
				type: 'POST',
				data: {
					action: 'splms_mark_notification_read',
					nonce: dashboard.nonces.splms_frontend_nonce,
					notification_id: notificationId
				},
				success: function(response) {
					if (response.success) {
						$item.addClass('read').removeClass('unread');
						// If filtering by unread, remove the item
						if (self.currentFilter === 'unread') {
							$item.fadeOut(200, function() {
								$(this).remove();
								if ($('.splms-notification-item').length === 0) {
									self.renderEmptyState();
								}
							});
						}
						self.updateFilterButtons();
					}
				},
				error: function() {
					console.error('Failed to mark notification as read');
				}
			});
		},

		markNotificationAsUnread: function(notificationId, $item) {
			const self = this;
			const dashboard = window.splms_dashboard;
			if (!dashboard || !dashboard.ajax_url || !dashboard.nonces?.splms_frontend_nonce) {
				return;
			}

			$.ajax({
				url: dashboard.ajax_url,
				type: 'POST',
				data: {
					action: 'splms_mark_notification_unread',
					nonce: dashboard.nonces.splms_frontend_nonce,
					notification_id: notificationId
				},
				success: function(response) {
					if (response.success) {
						$item.removeClass('read').addClass('unread');
						// If filtering by read, remove the item
						if (self.currentFilter === 'read') {
							$item.fadeOut(200, function() {
								$(this).remove();
								if ($('.splms-notification-item').length === 0) {
									self.renderEmptyState();
								}
							});
						}
						self.updateFilterButtons();
					}
				},
				error: function() {
					console.error('Failed to mark notification as unread');
				}
			});
		},

		deleteNotification: function(notificationId, $item) {
			const self = this;
			const confirmMessage = this.getLocalizedString('confirm_delete_notification') || 'Are you sure you want to delete this notification?';
			
			if (!confirm(confirmMessage)) {
				return;
			}

			const dashboard = window.splms_dashboard;
			if (!dashboard || !dashboard.ajax_url || !dashboard.nonces?.splms_frontend_nonce) {
				return;
			}

			$.ajax({
				url: dashboard.ajax_url,
				type: 'POST',
				data: {
					action: 'splms_delete_notification',
					nonce: dashboard.nonces.splms_frontend_nonce,
					notification_id: notificationId
				},
				success: function(response) {
					if (response.success) {
						$item.fadeOut(200, function() {
							$(this).remove();
							if ($('.splms-notification-item').length === 0) {
								self.renderEmptyState();
							}
							self.updateFilterButtons();
						});
					}
				},
				error: function() {
					console.error('Failed to delete notification');
				}
			});
		},

		handleMarkAllAsRead: function() {
			const self = this;
			const $button = $('#splms-mark-all-notifications-read');
			const originalText = $button.text();
			const markingText = this.getLocalizedString('marking') || 'Marking...';
			$button.prop('disabled', true).text(markingText);

			const dashboard = window.splms_dashboard;
			if (!dashboard || !dashboard.ajax_url || !dashboard.nonces?.splms_frontend_nonce) {
				$button.prop('disabled', false).text(originalText);
				return;
			}

			$.ajax({
				url: dashboard.ajax_url,
				type: 'POST',
				data: {
					action: 'splms_mark_all_notifications_read',
					nonce: dashboard.nonces.splms_frontend_nonce
				},
				success: function(response) {
					if (response.success) {
						// Update all notifications to read state
						$('.splms-notification-item').addClass('read').removeClass('unread');

						// Reload notifications to reflect changes
						self.loadNotifications();
					}
				},
				error: function() {
					console.error('Failed to mark all notifications as read');
				},
				complete: function() {
					$button.prop('disabled', false).text(originalText);
				}
			});
		},

		updateFilterButtons: function() {
			const unreadCount = $('.splms-notification-item.unread').length;
			if (unreadCount === 0) {
				$('#splms-mark-all-notifications-read').hide();
			} else {
				$('#splms-mark-all-notifications-read').show();
			}
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		if ($('.splms-notifications-tab').length) {
			DashboardNotifications.init();
		}
	});

	// Make DashboardNotifications available globally
	window.SPLMSDashboardNotifications = DashboardNotifications;

})(jQuery);

