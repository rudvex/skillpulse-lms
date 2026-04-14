import React from 'react';
import { FormTokenField } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { translateLabel } from '../../utility/helper';

/**
 * StaticMultiSelect Component
 * 
 * Simple multi-select field for static options (non-API based)
 * Wraps FormTokenField for consistent UI with LazyMultiSelect
 */
const StaticMultiSelect = ({
    value = [],
    onChange,
    label,
    help,
    placeholder,
    options = [],
    disabled = false,
    className = '',
    ...rest
}) => {
    const currentValues = Array.isArray(value) ? value : [];
    
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
        <div className={`splms-static-multi-select ${className}`}>
            <FormTokenField
                {...rest}
                label={translateLabel(label)}
                help={translateLabel(help)}
                value={selectedLabels}
                suggestions={allSuggestions}
                onChange={handleChange}
                placeholder={translateLabel(placeholder) || __('Type to add options...', 'skillpulse-lms')}
                disabled={disabled}
                __experimentalExpandOnFocus={true}
                __experimentalShowHowTo={true}
                maxSuggestions={allSuggestions.length || 10}
            />
        </div>
    );
};

export default StaticMultiSelect;

