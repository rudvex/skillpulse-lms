import React, { useEffect, useCallback, useRef } from 'react';
import { FormTokenField, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { translateLabel } from '../../utility/helper';
import { usePaginatedApiFetch } from '../../utility/usePaginatedApiFetch';
import { useSelectedItemsLoader } from '../../utility/useSelectedItemsLoader';
import './styles.scss';

/**
 * LazyMultiSelect Component
 * 
 * Reusable multi-select field with lazy loading, infinite scroll, and debounced search
 * Supports any API endpoint that returns paginated data
 */
const LazyMultiSelect = ({
    value = [],
    onChange,
    label,
    help,
    placeholder,
    api,
    loading: externalLoading = false,
    disabled = false,
    className = '',
    perPage = 20,
    minSearchLength = 2,
    debounceDelay = 300,
    itemLabel = null, // Optional: Custom label for items (e.g., "lessons", "courses", "users")
    loadingText = null, // Optional: Custom loading text
    errorText = null, // Optional: Custom error text
    ...rest
}) => {
    const suggestionsListRef = useRef(null);
    const currentValues = Array.isArray(value) ? value : [];

    // Derive default texts
    const defaultLoadingText = loadingText || __('Loading...', 'skillpulse-lms');
    const defaultErrorText = errorText || __('Failed to load items. Please try again.', 'skillpulse-lms');
    const defaultPlaceholder = placeholder || __('Type to search...', 'skillpulse-lms');

    // Use paginated API fetch hook
    const {
        options,
        isLoading,
        isLoadingMore,
        hasMore,
        searchTerm,
        error,
        handleSearch,
        loadNextPage,
        setOptions,
    } = usePaginatedApiFetch({
        api,
        perPage,
        minSearchLength,
        debounceDelay,
        externalLoading,
    });

    // Use selected items loader hook
    const loadSelectedItems = useSelectedItemsLoader({
        api,
        setOptions,
    });

    // Load selected items if they're not in current options
    useEffect(() => {
        if (currentValues.length > 0 && options.length > 0 && api) {
            const missingIds = currentValues.filter(id => 
                !options.find(opt => String(opt.value) === String(id))
            );
            
            if (missingIds.length > 0) {
                loadSelectedItems(missingIds);
            }
        }
    }, [currentValues.join(','), options.length, api, loadSelectedItems]);

    // Handle input change (search)
    const handleInputChange = (inputValue) => {
        handleSearch(inputValue);
    };

    // Handle scroll to load more
    const handleScroll = useCallback((event) => {
        const target = event.target;
        const scrollTop = target.scrollTop;
        const scrollHeight = target.scrollHeight;
        const clientHeight = target.clientHeight;

        // Load more when 80% scrolled
        if (
            scrollTop + clientHeight >= scrollHeight * 0.8 &&
            !isLoadingMore &&
            !isLoading &&
            hasMore
        ) {
            loadNextPage();
        }
    }, [isLoadingMore, isLoading, hasMore, loadNextPage]);

    // Attach scroll listener to suggestions list
    useEffect(() => {
        // Use MutationObserver to detect when suggestions list is added to DOM
        const observer = new MutationObserver(() => {
            const suggestionsList = document.querySelector(
                '.components-form-token-field__suggestions-list'
            );
            
            if (suggestionsList && !suggestionsListRef.current) {
                suggestionsListRef.current = suggestionsList;
                suggestionsList.addEventListener('scroll', handleScroll);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Also try to attach immediately
        const suggestionsList = document.querySelector(
            '.components-form-token-field__suggestions-list'
        );
        
        if (suggestionsList) {
            suggestionsListRef.current = suggestionsList;
            suggestionsList.addEventListener('scroll', handleScroll);
        }
        
        return () => {
            observer.disconnect();
            if (suggestionsListRef.current) {
                suggestionsListRef.current.removeEventListener('scroll', handleScroll);
                suggestionsListRef.current = null;
            }
        };
    }, [handleScroll]);

    // Convert stored values (IDs) to display labels for FormTokenField
    const selectedLabels = currentValues.map(valueId => {
        const option = options.find(opt => String(opt.value) === String(valueId));
        return option ? option.label : String(valueId);
    });

    // All available suggestions (labels)
    const allSuggestions = options.map(option => option.label);

    // Handle token change
    const handleChange = (selectedLabels) => {
        if (!onChange) return;

        // Convert labels back to values (IDs) for storage
        const selectedValues = selectedLabels.map(label => {
            const option = options.find(opt => opt.label === label);
            return option ? option.value : label;
        });

        onChange(selectedValues);
    };

    return (
        <div className={`splms-lazy-multi-select ${className} ${disabled || externalLoading || isLoading ? 'is-disabled' : ''}`}>
            <FormTokenField
                {...rest}
                label={translateLabel(label)}
                help={translateLabel(help)}
                value={selectedLabels}
                suggestions={allSuggestions}
                onChange={handleChange}
                onInputChange={handleInputChange}
                placeholder={translateLabel(placeholder) || defaultPlaceholder}
                disabled={disabled || externalLoading || isLoading}
                __experimentalExpandOnFocus={true}
                __experimentalShowHowTo={true}
                maxSuggestions={allSuggestions.length}
            />

            {/* Loading indicator */}
            {(isLoading || externalLoading) && (
                <div className="splms-lazy-multi-select__loading">
                    <Spinner />
                    <span>{defaultLoadingText}</span>
                </div>
            )}

            {/* Loading more indicator */}
            {isLoadingMore && (
                <div className="splms-lazy-multi-select__loading-more">
                    <Spinner />
                    <span>{__('Loading more...', 'skillpulse-lms')}</span>
                </div>
            )}

            {/* Error message */}
            {error && (
                <div className="splms-lazy-multi-select__error">
                    <span>{error || defaultErrorText}</span>
                </div>
            )}            
        </div>
    );
};

export default LazyMultiSelect;
