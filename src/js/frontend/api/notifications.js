/**
 * SkillPulse LMS Notifications REST API Helper
 * 
 * JavaScript helper for interacting with the Notifications REST API
 * Useful for mobile apps, third-party integrations, or custom implementations
 * 
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

class SPLMSNotificationsAPI {
	constructor() {
		this.namespace = 'splms/v1';
		this.restBase = 'notifications';
		this.baseURL = window.SPLMSCore.helper.getRestApiUrl(`${this.namespace}/${this.restBase}`);
	}

	/**
     * Get authentication headers
     * @returns {Object} Headers object
     */
	getHeaders() {
		const headers = {
			'Content-Type': 'application/json',
		};

		// Add nonce if available
		if (window.wpApiSettings?.nonce) {
			headers['X-WP-Nonce'] = window.wpApiSettings.nonce;
		}

		return headers;
	}

	/**
     * Make API request
     * @param {string} endpoint - API endpoint
     * @param {Object} options - Request options
     * @returns {Promise} Promise resolving to response data
     */
	async request(endpoint, options = {}) {
		const url = endpoint.startsWith('http') ? endpoint : `${this.baseURL}${endpoint}`;
		const config = {
			method: options.method || 'GET',
			headers: this.getHeaders(),
			...options,
		};

		if (options.body && typeof options.body === 'object') {
			config.body = JSON.stringify(options.body);
		}

		const response = await fetch(url, config);
		const data = await response.json();

		if (!response.ok) {
			throw new Error(data.message || `HTTP error! status: ${response.status}`);
		}

		return data;
	}

	/**
     * Get notifications for current user
     * @param {Object} params - Query parameters
     * @returns {Promise} Promise resolving to notifications array
     */
	async getNotifications(params = {}) {
		const queryString = new URLSearchParams(params).toString();
		const endpoint = queryString ? `?${queryString}` : '';
		return this.request(endpoint);
	}

	/**
     * Get single notification
     * @param {number|string} id - Notification ID
     * @returns {Promise} Promise resolving to notification object
     */
	async getNotification(id) {
		return this.request(`/${id}`);
	}

	/**
     * Get unread count
     * @returns {Promise} Promise resolving to unread count
     */
	async getUnreadCount() {
		const response = await this.request('/unread-count');
		return response.data?.unread_count || 0;
	}

	/**
     * Mark notification as read
     * @param {number|string} id - Notification ID
     * @returns {Promise} Promise resolving to updated notification
     */
	async markAsRead(id) {
		return this.request(`/${id}/read`, {
			method: 'PATCH',
		});
	}

	/**
     * Mark notification as unread
     * @param {number|string} id - Notification ID
     * @returns {Promise} Promise resolving to updated notification
     */
	async markAsUnread(id) {
		return this.request(`/${id}/unread`, {
			method: 'PATCH',
		});
	}

	/**
     * Mark all notifications as read
     * @param {string} eventKey - Optional event key filter
     * @returns {Promise} Promise resolving to result
     */
	async markAllAsRead(eventKey = null) {
		const body = eventKey ? { event_key: eventKey } : {};
		return this.request('/mark-all-read', {
			method: 'POST',
			body,
		});
	}

	/**
     * Delete notification
     * @param {number|string} id - Notification ID
     * @returns {Promise} Promise resolving to deletion result
     */
	async deleteNotification(id) {
		return this.request(`/${id}`, {
			method: 'DELETE',
		});
	}

	/**
     * Perform bulk action on notifications
     * @param {Array<number|string>} notificationIds - Array of notification IDs
     * @param {string} action - Action to perform: 'mark_read', 'mark_unread', or 'delete'
     * @returns {Promise} Promise resolving to result
     */
	async bulkAction(notificationIds, action) {
		if (!Array.isArray(notificationIds) || notificationIds.length === 0) {
			throw new Error('notificationIds must be a non-empty array');
		}

		const validActions = ['mark_read', 'mark_unread', 'delete'];
		if (!validActions.includes(action)) {
			throw new Error(`action must be one of: ${validActions.join(', ')}`);
		}

		return this.request('/bulk-action', {
			method: 'POST',
			body: {
				notification_ids: notificationIds,
				action: action,
			},
		});
	}
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
	module.exports = SPLMSNotificationsAPI;
}

// Make available globally
window.SPLMSNotificationsAPI = SPLMSNotificationsAPI;

