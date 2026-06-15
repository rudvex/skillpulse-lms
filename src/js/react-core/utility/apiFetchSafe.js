import apiFetch from '@wordpress/api-fetch';
import { getRestApiUrl } from './helper';

/**
 * Safe wrapper around apiFetch for consistent error handling and logging
 * @param {object} options - apiFetch options (path, method, data, parse, etc.)
 * @returns {Promise} Promise resolving to API response
 */
export const apiFetchSafe = async (options) => {
    try {
        const result = await apiFetch(options);
        return result;
    } catch (error) {
        console.error('SkillPulse API Error:', {
            path: options.path,
            method: options.method || 'GET',
            error: error.message || error,
        });
        throw error;
    }
};

/**
 * Fetch API data with blob response (for exports/downloads)
 * @param {string} path - API endpoint path (e.g., 'splms/v1/enrollments/export' or '/splms/v1/enrollments/export')
 * @param {object} options - Additional options (method, data, etc.)
 * @returns {Promise<Blob>} Promise resolving to blob
 */
export const apiFetchBlob = async (path, options = {}) => {
    const { method = 'POST', data = {} } = options;
    
    // Use native fetch for blob responses
    const nonce = window?.wpApiSettings?.nonce || '';
    const apiRoot = getRestApiUrl();
    
    // Normalize path - remove leading slash and ensure proper format
    let normalizedPath = path.startsWith('/') ? path.substring(1) : path;
    
    // Remove wp-json prefix if present
    normalizedPath = normalizedPath.replace(/^wp-json\//, '');
    
    const url = `${apiRoot}${normalizedPath}`;
    
    const response = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': nonce,
        },
        body: method !== 'GET' ? JSON.stringify(data) : undefined,
    });

    if (!response.ok) {
        throw new Error(`API request failed: ${response.statusText}`);
    }

    return await response.blob();
};

