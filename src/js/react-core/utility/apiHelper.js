import apiFetch from '@wordpress/api-fetch';

/**
 * Build API URL with query parameters
 * @param {string} endpoint - API endpoint path
 * @param {Object} params - Query parameters
 * @returns {string} Full API path with query string
 */
export const buildApiUrl = (endpoint, params = {}) => {
    const queryParams = new URLSearchParams();
    Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
            queryParams.append(key, params[key]);
        }
    });

    const url = endpoint.startsWith('/') 
        ? endpoint 
        : `/splms/v1/${endpoint.replace(/^\//, '')}`;
    
    return queryParams.toString() 
        ? `${url}?${queryParams.toString()}` 
        : url;
};

/**
 * Fetch API data with proper WordPress REST API handling
 * Uses apiFetch for WordPress REST API endpoints
 * @param {string} endpoint - API endpoint (will be converted to path)
 * @param {Object} params - Query parameters
 * @returns {Promise<Object>} API response data
 */
export const fetchApiData = async (endpoint, params = {}) => {
    // Convert endpoint to path format
    let path = endpoint;
    
    // Remove protocol and domain if present
    if (path.includes('://')) {
        const url = new URL(path);
        path = url.pathname + url.search;
    }
    
    // Remove /wp-json prefix if present
    path = path.replace(/^\/wp-json\//, '/');
    
    // Build query string if params provided
    if (Object.keys(params).length > 0) {
        const queryParams = new URLSearchParams();
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
                queryParams.append(key, params[key]);
            }
        });
        path = path.includes('?') 
            ? `${path}&${queryParams.toString()}` 
            : `${path}?${queryParams.toString()}`;
    }
    
    return await apiFetch({ path });
};

/**
 * Format API response data into options array
 * @param {Array|Object} data - API response data
 * @param {Array} useProperties - Property names to extract [valueProperty, labelProperty]
 * @returns {Array} Formatted options array [{value, label}, ...]
 */
export const formatOptions = (data, useProperties = ['id', 'title']) => {
    if (!data || !Array.isArray(data)) {
        return [];
    }

    return data.map(item => {
        const value = item[useProperties[0]]?.toString() || '';
        // Support nested properties (e.g., "user.name" -> user.name)
        const label = useProperties[1]?.split('.').reduce((o, i) => o?.[i], item) || value;
        return { label, value };
    });
};

/**
 * Merge new options with existing options, avoiding duplicates
 * @param {Array} existing - Existing options array
 * @param {Array} newItems - New options to merge
 * @returns {Array} Merged options array without duplicates
 */
export const uniqueMergeOptions = (existing = [], newItems = []) => {
    const existingValues = new Set(existing.map(opt => String(opt.value)));
    const uniqueNewItems = newItems.filter(opt => 
        !existingValues.has(String(opt.value))
    );
    return [...existing, ...uniqueNewItems];
};