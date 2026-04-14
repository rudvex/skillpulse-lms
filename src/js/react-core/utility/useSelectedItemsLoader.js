import { useCallback } from '@wordpress/element';
import { buildApiUrl, formatOptions, uniqueMergeOptions } from './apiHelper';
import apiFetch from '@wordpress/api-fetch';

/**
 * Custom hook for loading selected items that aren't in the current list
 * 
 * @param {Object} apiConfig - API configuration object
 * @param {string} apiConfig.endpoint - API endpoint path
 * @param {Object} apiConfig.params - Base API parameters
 * @param {string} apiConfig.dataPath - Optional path to extract data from response
 * @param {Array} apiConfig.useProperties - Property names for formatting [valueProp, labelProp]
 * @param {Function} setOptions - Function to update options state
 * 
 * @returns {Function} Function to load selected items by IDs
 */
export const useSelectedItemsLoader = ({
    api,
    setOptions,
}) => {
    /**
     * Load selected items that aren't in current results
     * @param {Array<string|number>} ids - Array of item IDs to load
     */
    const loadSelectedItems = useCallback(async (ids) => {
        if (!api || !ids || ids.length === 0 || !setOptions) return;
        
        try {
            const params = {
                include: ids.join(','),
                per_page: ids.length,
                ...api.params
            };

            const fullPath = buildApiUrl(api.endpoint, params);
            
            const response = await apiFetch({
                path: fullPath,
                parse: false
            });

            const data = await response.json();
            const dataToFormat = api.dataPath ? (data[api.dataPath] || data) : data;
            const formattedOptions = formatOptions(dataToFormat, api.useProperties);
            
            // Merge with existing options, avoiding duplicates
            setOptions(prevOptions => uniqueMergeOptions(prevOptions, formattedOptions));
        } catch (err) {
            console.error('Error loading selected items:', err);
        }
    }, [api, setOptions]);

    return loadSelectedItems;
};

