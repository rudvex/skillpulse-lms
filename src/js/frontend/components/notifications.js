import { getSiteUrl } from '../../react-core/utility/url';

/**
 * SkillPulse LMS Unified Notifications System
 * 
 * Handles notifications for nav menu, header, and standalone usage
 * Single source of truth for all notification functionality
 * 
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

class SPLMSNotifications {
	constructor() {
		this.notifications = [];
		this.unreadCount = 0;
		this.isLoading = false;
		this.isOpen = false;
        
		// Pagination
		this.currentPage = 1;
		this.perPage = 20;
		this.totalPages = 1;
		this.hasMore = false;
        
		// Filtering
		this.currentFilter = 'all'; // 'all', 'unread', 'read', 'type', 'event'
		this.filterType = null;
		this.filterEvent = null;
        
		// Lazy loading
		this.loadedPages = new Set();
		this.cache = {
			notifications: [],
			timestamp: null,
			ttl: 60000 // 1 minute cache
		};
        
		// Element selectors for different contexts
		this.selectors = {
			// Nav menu elements (primary)
			navBell: '.splms-nav-notification-btn',
			navDropdown: '#splms-nav-notifications-dropdown',
			navCount: '.splms-nav-notification-count',
            
			// Header elements
			headerBell: '.splms-notification-btn',
			headerDropdown: '.splms-notifications-dropdown',
			headerCount: '.splms-notification-count',
            
			// Generic elements
			anyBell: '.splms-notification-btn, .splms-nav-notification-btn',
			anyDropdown: '.splms-notifications-dropdown, #splms-nav-notifications-dropdown',
			anyCount: '.splms-notification-count, .splms-nav-notification-count'
		};

		this.init();
	}

	init() {
		jQuery(document).ready(() => this.setup());
	}

	setup() {
		this.findElements();
		this.setupEventListeners();
	}

	findElements() {
		// Find notification elements in any context
		this.$bellElement = jQuery(this.selectors.anyBell);
		this.$dropdownElement = jQuery(this.selectors.anyDropdown);
		this.$countElement = jQuery(this.selectors.anyCount);
	}

	setupEventListeners() {
		if (!this.$bellElement || this.$bellElement.length === 0) return;

		// Handle notification bell clicks (both nav menu and header)
		jQuery(document).on('click', this.selectors.anyBell, (e) => {
			e.preventDefault();
			e.stopPropagation();
			this.toggleDropdown();
		});

		// Handle mark all as read
		jQuery(document).on('click', '.splms-mark-all-read', (e) => {
			e.preventDefault();
			this.markAllAsRead();
		});

		// Close dropdown when clicking outside
		jQuery(document).on('click', (e) => {
			if (!jQuery(e.target).closest('.splms-nav-notifications-trigger, .splms-notifications-trigger').length) {
				this.closeDropdown();
			}
		});

		// Close dropdown on escape key
		jQuery(document).on('keydown', (e) => {
			if (e.key === 'Escape' && this.isOpen) {
				this.closeDropdown();
			}
		});

		// Handle notification item clicks
		jQuery(document).on('click', '.splms-notification-link', (e) => {
			const $item = jQuery(e.currentTarget).closest('.splms-notification-item');
			const notificationId = $item.data('notification-id');
			const url = jQuery(e.currentTarget).data('url');
            
			if (!$item.hasClass('is-read')) {
				this.markNotificationAsRead(notificationId, $item);
			}
            
			if (url && url !== '#') {
				window.location.href = url;
			}
		});
	}

	toggleDropdown() {
		if (this.isOpen) {
			this.closeDropdown();
		} else {
			this.openDropdown();
		}
	}

	openDropdown() {
		if (!this.$dropdownElement || this.$dropdownElement.length === 0) return;
        
		// Handle both nav menu and header dropdowns
		this.$dropdownElement.addClass('is-open').show();
		this.isOpen = true;
        
		// Load notifications if not already loaded or cache expired
		const cacheValid = this.cache.timestamp && 
                          (Date.now() - this.cache.timestamp) < this.cache.ttl;
        
		if (!this.$dropdownElement.data('loaded') || !cacheValid) {
			this.fetchNotifications();
		} else {
			// Use cached data
			this.renderNotifications();
		}
	}

	closeDropdown() {
		if (!this.$dropdownElement || this.$dropdownElement.length === 0) return;
        
		this.$dropdownElement.removeClass('is-open').hide();
		this.isOpen = false;
	}

	fetchNotifications(page = 1, append = false) {
		this.isLoading = true;
		if (!append) {
			this.showLoadingState();
		}

		// Use available AJAX data from frontend object
		const frontend = window.splms_frontend;
		if (!frontend || !frontend.ajax_url || !frontend.nonces?.splms_frontend_nonce) {
			window.console?.error('SPLMS: No AJAX data available');
			this.showError('Unable to load notifications. Please refresh the page.');
			this.isLoading = false;
			return;
		}

		// Build request data
		const requestData = {
			action: 'splms_get_notifications',
			nonce: frontend.nonces.splms_frontend_nonce,
			limit: this.perPage,
			offset: (page - 1) * this.perPage
		};

		// Add filters
		if (this.currentFilter === 'unread') {
			requestData.is_read = '0';
		} else if (this.currentFilter === 'read') {
			requestData.is_read = '1';
		}

		if (this.filterType) {
			requestData.type = this.filterType;
		}

		if (this.filterEvent) {
			requestData.event_key = this.filterEvent;
		}

		jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: requestData,
			success: (response) => {
				if (response.success && response.data) {
					const newNotifications = response.data.notifications || [];
                    
					if (append) {
						// Append to existing notifications
						this.notifications = [...this.notifications, ...newNotifications];
					} else {
						// Replace notifications
						this.notifications = newNotifications;
					}
                    
					this.unreadCount = response.data.unread_count || 0;
					this.currentPage = page;
					this.totalPages = response.data.pages || 1;
					this.hasMore = page < this.totalPages;
                    
					// Update cache
					this.cache.notifications = this.notifications;
					this.cache.timestamp = Date.now();
                    
					this.renderNotifications();
					this.updateBadge();
					this.$dropdownElement.data('loaded', true);
					this.loadedPages.add(page);
				} else {
					window.console?.error('Failed to fetch notifications:', response.data || response);
					const errorMessage = response.data && typeof response.data === 'string' 
						? response.data 
						: 'Failed to load notifications';
					this.showError(errorMessage);
				}
			},
			error: (xhr, status, error) => {
				window.console?.error('Error fetching notifications:', error, xhr);
				let errorMessage = 'Network error occurred';
				if (xhr.responseJSON && xhr.responseJSON.data) {
					errorMessage = xhr.responseJSON.data;
				} else if (xhr.status === 0) {
					errorMessage = 'Connection error. Please check your internet connection.';
				}
				this.showError(errorMessage);
			},
			complete: () => {
				this.isLoading = false;
			}
		});
	}

	/**
     * Load more notifications (pagination)
     */
	loadMore() {
		if (this.isLoading || !this.hasMore) return;
        
		const nextPage = this.currentPage + 1;
		this.fetchNotifications(nextPage, true);
	}

	/**
     * Set filter and reload notifications
     * @param {string} filter - Filter type: 'all', 'unread', 'read'
     * @param {string} type - Optional notification type filter
     * @param {string} event - Optional event key filter
     */
	setFilter(filter, type = null, event = null) {
		this.currentFilter = filter;
		this.filterType = type;
		this.filterEvent = event;
		this.currentPage = 1;
		this.loadedPages.clear();
		this.fetchNotifications(1, false);
	}

	markNotificationAsRead(notificationId, $item = null) {
		const frontend = window.splms_frontend;
        
		if (!frontend || !frontend.ajax_url || !frontend.nonces?.splms_frontend_nonce) {
			window.console?.error('SPLMS: No AJAX data available');
			return;
		}

		if (!notificationId) {
			window.console?.error('SPLMS: Notification ID is required');
			return;
		}

		// Convert notificationId to string for comparison (PHP returns IDs as strings)
		const notificationIdStr = String(notificationId);
        
		jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: {
				action: 'splms_mark_notification_read',
				nonce: frontend.nonces.splms_frontend_nonce,
				notification_id: notificationId
			},
			success: (response) => {
				if (response.success) {
					// Update local state - handle both string and number IDs
					this.notifications = this.notifications.map(notification => {
						const notifId = String(notification.id);
						return notifId === notificationIdStr 
							? { ...notification, is_read: true }
							: notification;
					});
					this.unreadCount = Math.max(0, this.unreadCount - 1);
					this.updateBadge();
                    
					// Update UI if item element provided
					if ($item) {
						$item.removeClass('is-unread').addClass('is-read');
						$item.find('.splms-notification-status-dot').remove();
					}
				} else {
					window.console?.error('Failed to mark notification as read:', response.data || response);
				}
			},
			error: (xhr, status, error) => {
				window.console?.error('Error marking notification as read:', error, xhr);
			}
		});
	}

	markAllAsRead() {
		if (this.unreadCount === 0) return;

		const frontend = window.splms_frontend;
        
		if (!frontend || !frontend.ajax_url || !frontend.nonces?.splms_frontend_nonce) {
			window.console?.error('SPLMS: No AJAX data available');
			return;
		}

		// Disable button during request
		const $button = jQuery('.splms-mark-all-read');
		const originalText = $button.text();
		$button.prop('disabled', true).text('Marking...');

		jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: {
				action: 'splms_mark_all_notifications_read',
				nonce: frontend.nonces.splms_frontend_nonce
			},
			success: (response) => {
				if (response.success) {
					// Update all notifications to read
					this.notifications = this.notifications.map(n => ({ ...n, is_read: true }));
					this.unreadCount = 0;
					this.updateBadge();
					this.renderNotifications();
				} else {
					window.console?.error('Failed to mark all notifications as read:', response.data || response);
					$button.prop('disabled', false).text(originalText);
				}
			},
			error: (xhr, status, error) => {
				window.console?.error('Error marking all notifications as read:', error, xhr);
				$button.prop('disabled', false).text(originalText);
			}
		});
	}

	renderNotifications() {
		if (!this.$dropdownElement || this.$dropdownElement.length === 0) return;

		let html = `
            <div class="splms-notifications-header">
                <div class="splms-notifications-title">
                    <h3>Notifications</h3>
                    ${this.unreadCount > 0 ? `<span class="splms-unread-count">${this.unreadCount}</span>` : ''}
                </div>
                <div class="splms-notifications-actions">
                    ${this.unreadCount > 0 ? '<button class="splms-mark-all-read">Mark all read</button>' : ''}
                </div>
            </div>
            <div class="splms-notifications-filters">
                <button class="splms-filter-btn ${this.currentFilter === 'all' ? 'is-active' : ''}" data-filter="all">All</button>
                <button class="splms-filter-btn ${this.currentFilter === 'unread' ? 'is-active' : ''}" data-filter="unread">Unread</button>
                <button class="splms-filter-btn ${this.currentFilter === 'read' ? 'is-active' : ''}" data-filter="read">Read</button>
            </div>
            <div class="splms-notifications-list">
        `;

		if (this.notifications.length > 0) {
			this.notifications.forEach(notification => {
				const timeAgo = this.formatTimeAgo(notification.created_at);
				const typeIcon = this.getNotificationIcon(notification.type);
				const typeClass = this.getNotificationTypeClass(notification.type);
				// Generate course URL - check meta first, then build from course_id
				let courseUrl = '#';
				if (notification.meta && notification.meta.course_url) {
					courseUrl = notification.meta.course_url;
				} else if (notification.course_id) {
					// Try to get URL from window if available, otherwise use simple pattern
					const baseUrl = getSiteUrl() || '';
					courseUrl = `${baseUrl}/course/${notification.course_id}/`;
				}
                
				html += `
                    <div class="splms-notification-item ${!notification.is_read ? 'is-unread' : 'is-read'}" data-notification-id="${notification.id}">
                        <div class="splms-notification-link" data-url="${courseUrl}">
                            <div class="splms-notification-icon-wrapper">
                                <div class="splms-notification-icon ${typeClass}">
                                    ${typeIcon}
                                </div>
                            </div>
                            <div class="splms-notification-content">
                                <div class="splms-notification-main">
                                    <h4 class="splms-notification-title">${notification.title}</h4>
                                    <p class="splms-notification-message">${notification.message}</p>
                                </div>
                                <div class="splms-notification-meta">
                                    <span class="splms-notification-time">${timeAgo}</span>
                                    <span class="splms-notification-type">${this.getNotificationTypeLabel(notification.type)}</span>
                                </div>
                            </div>
                            ${!notification.is_read ? '<div class="splms-notification-status-dot"></div>' : ''}
                        </div>
                    </div>
                `;
			});
		} else {
			html += `
                <div class="splms-notifications-empty">
                    <div class="splms-notifications-empty-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h4>No notifications yet</h4>
                    <p>We'll notify you when something important happens.</p>
                </div>
            `;
		}

		html += '</div>';
        
		// Add pagination controls if there are more pages
		if (this.hasMore) {
			html += `
                <div class="splms-notifications-footer">
                    <button class="splms-load-more-btn" ${this.isLoading ? 'disabled' : ''}>
                        ${this.isLoading ? 'Loading...' : 'Load More'}
                    </button>
                </div>
            `;
		}
        
		this.$dropdownElement.html(html);
        
		// Setup filter button handlers
		this.setupFilterHandlers();
        
		// Setup load more handler
		if (this.hasMore) {
			this.setupLoadMoreHandler();
		}
	}

	setupFilterHandlers() {
		jQuery(document).off('click', '.splms-filter-btn').on('click', '.splms-filter-btn', (e) => {
			e.preventDefault();
			const filter = jQuery(e.currentTarget).data('filter');
			if (filter && filter !== this.currentFilter) {
				jQuery('.splms-filter-btn').removeClass('is-active');
				jQuery(e.currentTarget).addClass('is-active');
				this.setFilter(filter);
			}
		});
	}

	setupLoadMoreHandler() {
		jQuery(document).off('click', '.splms-load-more-btn').on('click', '.splms-load-more-btn', (e) => {
			e.preventDefault();
			if (!this.isLoading && this.hasMore) {
				this.loadMore();
			}
		});
	}

	getNotificationIcon(type) {
		const icons = {
			'system': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2C13.1 2 14 2.9 14 4C14 4.74 13.6 5.39 13 5.73V7C13 10.76 15.15 14 18 16V17H6V16C8.85 14 11 10.76 11 7V5.73C10.4 5.39 10 4.74 10 4C10 2.9 10.9 2 12 2ZM10 21C10 22.1 10.9 23 12 23S14 22.1 14 21H10Z" fill="currentColor"/></svg>',
			'success': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="currentColor"/><path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			'course': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 10v6M2 10l10-5 10 5-10 5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 12v5c3 3 9 3 12 0v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			'info': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 16v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
			'warning': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="currentColor" stroke-width="2"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2"/></svg>',
			'error': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/><line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/></svg>'
		};
        
		return icons[type] || icons['info'];
	}

	getNotificationTypeClass(type) {
		const classes = {
			'system': 'system-update',
			'success': 'course-completed',
			'course': 'course-notification',
			'info': 'info-notification',
			'warning': 'warning-notification',
			'error': 'error-notification'
		};
        
		return classes[type] || 'info-notification';
	}

	getNotificationTypeLabel(type) {
		const labels = {
			'system': 'Info',
			'success': 'Success',
			'course': 'Course',
			'info': 'Info',
			'warning': 'Warning',
			'error': 'Error'
		};
        
		return labels[type] || 'Info';
	}

	formatTimeAgo(dateString) {
		const now = new Date();
		const date = new Date(dateString);
		const diff = Math.floor((now - date) / 1000);

		if (diff < 60) return 'Just now';
		if (diff < 3600) return `${Math.floor(diff / 60)} minutes ago`;
		if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
		if (diff < 604800) return `${Math.floor(diff / 86400)} days ago`;
        
		return date.toLocaleDateString();
	}

	updateBadge() {
		if (!this.$countElement || this.$countElement.length === 0) return;
        
		if (this.unreadCount > 0) {
			this.$countElement.text(this.unreadCount).show();
		} else {
			this.$countElement.hide();
		}
	}

	/**
     * Refresh unread count badge without fetching all notifications
     * Useful for periodic updates or after external changes
     */
	refreshUnreadCount() {
		const frontend = window.splms_frontend;
        
		if (!frontend || !frontend.ajax_url || !frontend.nonces?.splms_frontend_nonce) {
			return;
		}

		// Fetch only unread count by requesting unread notifications with limit 1
		jQuery.ajax({
			url: frontend.ajax_url,
			type: 'POST',
			data: {
				action: 'splms_get_notifications',
				nonce: frontend.nonces.splms_frontend_nonce,
				limit: 1,
				offset: 0,
				is_read: '0'
			},
			success: (response) => {
				if (response.success && response.data) {
					// The unread_count is included in the response
					this.unreadCount = response.data.unread_count || 0;
					this.updateBadge();
				}
			},
			error: (xhr, status, error) => {
				// Silently fail for background refresh
				window.console?.debug('Failed to refresh unread count:', error);
			}
		});
	}

	showLoadingState() {
		if (!this.$dropdownElement || this.$dropdownElement.length === 0) return;

		this.$dropdownElement.html(`
            <div class="splms-notification-loading">
                <div class="loading-spinner"></div>
                <span>Loading notifications...</span>
            </div>
        `);
	}

	showError(message) {
		if (!this.$dropdownElement || this.$dropdownElement.length === 0) return;

		this.$dropdownElement.html(`
            <div class="splms-notification-error">
                <span>${message}</span>
            </div>
        `);
	}
}

// Initialize notifications when DOM is ready
jQuery(document).ready(function() {
	window.splmsNotifications = new SPLMSNotifications();
});

// Re-initialize when content is dynamically loaded
jQuery(document).on('splms_content_loaded', function() {
	if (window.splmsNotifications) {
		window.splmsNotifications.setup();
	}
});
