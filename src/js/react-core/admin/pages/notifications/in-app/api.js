import apiFetch from '@wordpress/api-fetch';

const API_NOTIFICATIONS_PATH = '/splms/v1/notifications';
const API_NOTIFICATIONS_TEMPLATES_PATH = '/splms/v1/notifications/templates';
const API_NOTIFICATIONS_BULK_PATH = '/splms/v1/notifications/bulk-action';

/**
 * Fetch notifications data from the API.
 * @param {Object} params - Query parameters
 */
export const fetchNotifications = async (params = {}) => {
    const queryParams = new URLSearchParams();
    Object.keys(params).forEach(key => {
        if (params[key] !== '' && params[key] !== null && params[key] !== undefined) {
            queryParams.append(key, params[key]);
        }
    });
    
    const url = queryParams.toString() ? `${API_NOTIFICATIONS_PATH}?${queryParams.toString()}` : API_NOTIFICATIONS_PATH;
    
    return apiFetch({ path: url });
};

/**
 * Fetch in-app notification templates from the API.
 */
export const fetchNotificationTemplates = async () => {
    return apiFetch({ path: API_NOTIFICATIONS_TEMPLATES_PATH });
};

/**
 * Save in-app notification template via the API.
 * @param {Object} templateData - Template data to save
 */
export const saveNotificationTemplate = async (templateData) => {
    return apiFetch({
        path: API_NOTIFICATIONS_TEMPLATES_PATH,
        method: 'POST',
        data: templateData,
    });
};

/**
 * Delete notification via the API.
 * @param {number} notificationId - Notification ID
 */
export const deleteNotification = async (notificationId) => {
    return apiFetch({
        path: `${API_NOTIFICATIONS_PATH}/${notificationId}`,
        method: 'DELETE',
    });
};

/**
 * Perform bulk actions on notifications via the API.
 * @param {Array} notificationIds - Array of notification IDs
 * @param {string} action - Action to perform ('mark_read', 'mark_unread', 'delete')
 */
export const bulkActionNotifications = async (notificationIds, action) => {
    return apiFetch({
        path: API_NOTIFICATIONS_BULK_PATH,
        method: 'POST',
        data: {
            ids: notificationIds,
            action: action,
        },
    });
};

/**
 * Fetch user notifications from the API.
 * @param {number} userId - User ID
 * @param {Object} params - Optional query parameters (pagination, etc.)
 */
export const fetchUserNotifications = async (userId, params = {}) => {
    const queryParams = new URLSearchParams();
    Object.keys(params).forEach(key => {
        if (params[key] !== '' && params[key] !== null && params[key] !== undefined) {
            queryParams.append(key, params[key]);
        }
    });
    
    const url = queryParams.toString() 
        ? `${API_NOTIFICATIONS_PATH}/users/${userId}?${queryParams.toString()}` 
        : `${API_NOTIFICATIONS_PATH}/users/${userId}`;
    
    return apiFetch({ path: url });
};


