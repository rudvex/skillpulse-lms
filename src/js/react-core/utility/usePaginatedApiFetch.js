import { useState, useEffect, useCallback, useRef } from '@wordpress/element';
import { debounce } from 'lodash';
import apiFetch from '@wordpress/api-fetch';
import { buildApiUrl, formatOptions, uniqueMergeOptions } from './apiHelper';

/**
 * Custom hook for paginated API fetching with debounced search
 * 
 * @param {Object} apiConfig - API configuration object
 * @param {string} apiConfig.endpoint - API endpoint path
 * @param {Object} apiConfig.params - Base API parameters
 * @param {string} apiConfig.dataPath - Optional path to extract data from response (e.g., "lessons")
 * @param {Array} apiConfig.useProperties - Property names for formatting [valueProp, labelProp]
 * @param {number} perPage - Items per page (default: 20)
 * @param {number} minSearchLength - Minimum search term length (default: 2)
 * @param {number} debounceDelay - Debounce delay in ms (default: 300)
 * @param {boolean} externalLoading - External loading state
 * 
 * @returns {Object} Hook state and methods
 */
export const usePaginatedApiFetch = ({
    api,
    perPage = 20,
    minSearchLength = 2,
    debounceDelay = 300,
    externalLoading = false,
}) => {
    const [options, setOptions] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [isLoadingMore, setIsLoadingMore] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [searchTerm, setSearchTerm] = useState('');
    const [error, setError] = useState(null);
    const [hasMore, setHasMore] = useState(true);
    
    const abortControllerRef = useRef(null);

    // Fetch data from API
    const fetchApiData = useCallback(async (endpoint, params = {}) => {
        // Cancel any ongoing request
        if (abortControllerRef.current) {
            abortControllerRef.current.abort();
        }
        
        abortControllerRef.current = new AbortController();
        
        const fullPath = buildApiUrl(endpoint, params);
        const response = await apiFetch({
            path: fullPath,
            parse: false,
            signal: abortControllerRef.current.signal
        });

        const data = await response.json();
        const total = response.headers.get('x-wp-total') || data.total || 0;
        const totalPages = response.headers.get('x-wp-totalpages') || data.pages || 1;

        // If data is an array, preserve it as an array and attach pagination info
        if (Array.isArray(data)) {
            return {
                data: data,
                total: parseInt(total, 10),
                totalPages: parseInt(totalPages, 10)
            };
        }

        // If data is an object, spread it and add pagination info
        return {
            ...data,
            total: parseInt(total, 10),
            totalPages: parseInt(totalPages, 10)
        };
    }, []);

    // Load items with pagination and search
    const loadItems = useCallback(async (page = 1, search = '') => {
        if (!api) return;

        setIsLoading(page === 1);
        setIsLoadingMore(page > 1);
        setError(null);

        try {
            const params = {
                page,
                per_page: perPage,
                ...api.params
            };

            if (search && search.length >= minSearchLength) {
                params.search = search;
            }

            const response = await fetchApiData(api.endpoint, params);
            
            // Handle array responses (when API returns array directly)
            let dataToFormat;
            if (api.dataPath && response[api.dataPath]) {
                // Use custom dataPath if specified
                dataToFormat = response[api.dataPath];
            } else if (response.data && Array.isArray(response.data)) {
                // Handle case where array is wrapped in {data: [...]} (from fetchApiData)
                dataToFormat = response.data;
            } else if (Array.isArray(response)) {
                // Response is already an array
                dataToFormat = response;
            } else {
                // Fallback to response itself
                dataToFormat = response;
            }
            
            const formattedOptions = formatOptions(dataToFormat, api.useProperties);

            setTotalPages(response.totalPages || 1);
            setHasMore(page < (response.totalPages || 1));

            if (page === 1) {
                // Replace options for first page or new search
                setOptions(formattedOptions);
            } else {
                // Append options for pagination
                setOptions(prevOptions => uniqueMergeOptions(prevOptions, formattedOptions));
            }
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error('Error loading items:', err);
                setError(err.message || 'Failed to load items. Please try again.');
            }
        } finally {
            setIsLoading(false);
            setIsLoadingMore(false);
        }
    }, [api, perPage, minSearchLength, fetchApiData]);

    // Debounced search handler
    const debouncedSearch = useCallback(
        debounce((term) => {
            setCurrentPage(1);
            loadItems(1, term);
        }, debounceDelay),
        [loadItems, debounceDelay]
    );

    // Handle search input change
    const handleSearch = useCallback((term) => {
        setSearchTerm(term);
        if (term.length >= minSearchLength || term.length === 0) {
            debouncedSearch(term);
        }
    }, [minSearchLength, debouncedSearch]);

    // Reload current page
    const reload = useCallback(() => {
        loadItems(currentPage, searchTerm);
    }, [loadItems, currentPage, searchTerm]);

    // Load next page
    const loadNextPage = useCallback(() => {
        if (!isLoadingMore && !isLoading && hasMore && currentPage < totalPages) {
            const nextPage = currentPage + 1;
            setCurrentPage(nextPage);
            loadItems(nextPage, searchTerm);
        }
    }, [isLoadingMore, isLoading, hasMore, currentPage, totalPages, searchTerm, loadItems]);

    // Initialize: Load first page on mount
    useEffect(() => {
        if (api && !externalLoading) {
            loadItems(1, '');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [api?.endpoint]); // Only reload if endpoint changes

    return {
        options,
        isLoading,
        isLoadingMore,
        hasMore,
        totalPages,
        currentPage,
        searchTerm,
        error,
        loadItems,
        reload,
        handleSearch,
        loadNextPage,
        setOptions, // Expose for merging with selected items
    };
};

