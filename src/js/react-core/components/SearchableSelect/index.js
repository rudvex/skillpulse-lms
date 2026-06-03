import React, { useState, useEffect } from 'react';
import { ComboboxControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { SplmsIcon } from '../SplmsIcon';
import { fetchApiData, formatOptions } from '../../utility/apiHelper';
import './styles.scss';
import { getSiteUrl } from '../../utility/url';

/**
 * SearchableSelect Component
 *
 * Simple searchable dropdown with optional view button and create page functionality
 */
const SearchableSelect = ({
    label,
    value,
    onChange,
    options = [],
    loading = false,
    api,
    searchParam = 'search',
    placeholder = __('Search and select...', 'skillpulse-lms'),
    disabled = false,
    help,
    className = '',
    minSearchLength = 2,
    maxResults = 20,
    showViewButton = false,
    viewButtonText = __('View Page', 'skillpulse-lms'),
    viewButtonUrl = null,
    // Page creation options
    showCreateButton = false,
    createButtonText = __('Create Page', 'skillpulse-lms'),
    pageType = '',
    onPageCreated = null,
    ...rest
}) => {
    const [searchTerm, setSearchTerm] = useState('');
    const [allOptions, setAllOptions] = useState([]);
    const [isSearching, setIsSearching] = useState(false);
    const [isCreatingPage, setIsCreatingPage] = useState(false);

    // Initialize options from props
    useEffect(() => {
        setAllOptions(Array.isArray(options) ? options : []);
    }, [options]);


    // Search function
    const searchItems = async (term) => {
        if (!api || term.length < minSearchLength) return;

        setIsSearching(true);
        try {
            const params = {
                [searchParam]: term,
                per_page: maxResults,
                ...api.params
            };

            const response = await fetchApiData(api.endpoint, params);
            const searchResults = formatOptions(response, api.useProperties);
            
            // Combine with existing options, remove duplicates
            const existingValues = new Set(allOptions.map(opt => opt.value));
            const newResults = searchResults.filter(opt => !existingValues.has(opt.value));
            
            setAllOptions([...allOptions, ...newResults]);
        } catch (error) {
            console.error('Search error:', error);
        } finally {
            setIsSearching(false);
        }
    };

    // Handle search input change
    const handleFilterChange = (searchValue) => {
        setSearchTerm(searchValue);
        if (searchValue && api) {
            searchItems(searchValue);
        }
    };

    // Handle selection change
    const handleChange = (selectedInput) => {
        if (!onChange) return;

        // Handle clearing (empty string, null, or undefined)
        if (!selectedInput || selectedInput === '') {
            onChange('');
            return;
        }

        // ComboboxControl might pass either label or value, so check both
        let selectedOption = allOptions.find(opt => opt.label === selectedInput);

        // If not found by label, try by value
        if (!selectedOption) {
            selectedOption = allOptions.find(opt => String(opt.value) === String(selectedInput));
        }

        const valueToPass = selectedOption ? selectedOption.value : selectedInput;
        onChange(valueToPass);
    };

    // Get the display label for current value
    const getDisplayValue = () => {
        if (!value || value === '' || value === 0 || value === '0') return '';

        const selectedOption = allOptions.find(opt => String(opt.value) === String(value));

        return selectedOption ? selectedOption.label : '';
    };

    // Get view URL
    const getViewUrl = () => {
        if (!value || value === '' || value === 0 || value === '0' || !showViewButton) return null;

        if (viewButtonUrl) {
            return typeof viewButtonUrl === 'function' ? viewButtonUrl(value) : viewButtonUrl;
        }

        // Default for pages
        if (typeof value === 'number' || /^\d+$/.test(value)) {
            return `${getSiteUrl()}/?page_id=${value}`;
        }

        return null;
    };

    // Handle page creation
    const handleCreatePage = async () => {
        if (!pageType || isCreatingPage) return;

        setIsCreatingPage(true);

        try {
            const formData = new FormData();
            formData.append('action', 'splms_create_page');
            formData.append('page_type', pageType);
            formData.append('nonce', window.SPLMSCore_Data?.admin_nonce || '');

            const response = await fetch(window.SPLMSCore_Data?.ajax_url || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result && result.success) {
                const newPageId = result.data.page_id;
                const newPageTitle = result.data.page_title || 'New Page';

                // Add the new page to options
                const newOption = {
                    value: newPageId,
                    label: newPageTitle
                };

                setAllOptions(prevOptions => [newOption, ...prevOptions]);

                // Set as selected value
                if (onChange) {
                    onChange(newPageId);
                }

                // Call callback if provided
                if (onPageCreated) {
                    onPageCreated(result.data);
                }

                // Show success message
                if (window.wp && window.wp.data) {
                    window.wp.data.dispatch('core/notices').createNotice(
                        'success',
                        result.data.message || __('Page created successfully!', 'skillpulse-lms'),
                        { type: 'snackbar', isDismissible: true }
                    );
                }

            } else {
                throw new Error(result?.data?.message || __('Failed to create page', 'skillpulse-lms'));
            }
        } catch (error) {
            console.error('Error creating page:', error);

            // Show error message
            if (window.wp && window.wp.data) {
                window.wp.data.dispatch('core/notices').createNotice(
                    'error',
                    error.message || __('Failed to create page. Please try again.', 'skillpulse-lms'),
                    { type: 'snackbar', isDismissible: true }
                );
            }
        } finally {
            setIsCreatingPage(false);
        }
    };

    // Check if we should show the create button
    const shouldShowCreateButton = showCreateButton && pageType && (!value || value === '' || value === 0 || value === '0');

    return (
        <div className={`splms-searchable-select-wrapper ${className}`}>
            {/* Label */}
            {label && <div className="splms-field-label">{label}</div>}
            
            {/* Main control row - dropdown and button side by side */}
            <div className="splms-control-row">
                <div className="splms-dropdown-container">
                    <ComboboxControl
                        value={value}
                        onChange={handleChange}
                        options={allOptions}
                        onFilterValueChange={handleFilterChange}
                        placeholder={placeholder}
                        disabled={disabled || loading}
                        {...rest}
                    />
                </div>

                {/* Action buttons container */}
                <div className="splms-action-container">
                    {/* Create page button - shown when no page is selected */}
                    {shouldShowCreateButton && (
                        <Button
                            primary
                            onClick={handleCreatePage}
                            disabled={isCreatingPage}
                            icon={isCreatingPage ? <SplmsIcon mode="wp" name="update" size={12} /> : <SplmsIcon mode="wp" name="plus-alt2" size={12} />}
                            className="splms-create-button"
                        >
                            {isCreatingPage ? __('Creating...', 'skillpulse-lms') : createButtonText}
                        </Button>
                    )}

                    {/* View button - shown when page is selected */}
                    {showViewButton && value && value !== '' && value !== 0 && value !== '0' && getViewUrl() && (
                        <Button
                            secondary
                            href={getViewUrl()}
                            target="_blank"
                            rel="noopener noreferrer"
                            icon={<SplmsIcon mode="wp" name="external" size={12} />}
                            className="splms-view-button"
                        >
                            {viewButtonText}
                        </Button>
                    )}
                </div>
            </div>
            
            {/* Help text */}
            {help && <div className="splms-field-help">{help}</div>}
            
            {/* Loading indicator */}
            {(loading || isSearching) && (
                <div className="splms-loading-indicator">
                    <SplmsIcon mode="wp" name="update" size={14} />
                    <span>{isSearching ? __('Searching...', 'skillpulse-lms') : __('Loading...', 'skillpulse-lms')}</span>
                </div>
            )}
        </div>
    );
};

export default SearchableSelect;