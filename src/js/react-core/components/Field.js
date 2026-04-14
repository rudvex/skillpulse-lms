import {
    TextControl,
    TextareaControl,
    CheckboxControl,
    RadioControl,
    SelectControl,
    ToggleControl,
    __experimentalHeading as Heading,
    FormFileUpload,
    __experimentalNumberControl as NumberControl,
    FormTokenField,
    Button,
    ColorPalette,
    BaseControl,
    Card,
    CardBody,
    Tooltip,
    RangeControl,
} from '@wordpress/components';
import { useState, useEffect, useMemo, useCallback } from '@wordpress/element';
import React from 'react';
import { __ } from '@wordpress/i18n';
import { SplmsIcon } from './SplmsIcon';
import SearchableSelect from './SearchableSelect';
import CustomDatePicker from './CustomDatePicker';
import RepeaterField from './RepeaterField';
import MediaUrlField from './MediaUrlField';
import LazyMultiSelect from './LazyMultiSelect';
import StaticMultiSelect from './StaticMultiSelect';
import WordPressEditor from './WordPressEditor';
import { translateLabel } from '../utility/helper';

const Field = React.memo((props) => {
    const [loading, setLoading] = useState(false);
    const [options, setOptions] = useState([]);
    const [showPassword, setShowPassword] = useState(false);

    useEffect(() => {
        // Only load options if field is visible and has API configuration
        if (props.options && Array.isArray(props.options)) {
            setOptions(props.options);
            setLoading(props.loading || false);
        }
    }, [props.pageOptions, props.pageLoading, props.options, props.loading]);

    if (!props.type) {
        console.warn('Field type is required');
        return null;
    }

    const fieldProps = useMemo(() => ({
        ...props,
        label: translateLabel(props.label),
        className: `splms-field splms-field-${props.type} ${props.className || ''}`,
        help: translateLabel(props.help),
        description: translateLabel(props.description),
        placeholder: translateLabel(props.placeholder),        
    }), [props]);

    const renderFieldWithTooltip = useCallback((fieldComponent) => {
        if (fieldProps.tooltip) {
            return (
                <div className="splms-field-with-tooltip">
                    <div className="splms-field-header">
                        {fieldComponent}
                        <Tooltip text={translateLabel(fieldProps.tooltip)}>
                            <SplmsIcon mode="wp" name="info" size={16} className="splms-field-tooltip-icon" />
                        </Tooltip>
                    </div>
                </div>
            );
        }
        return fieldComponent;
    }, [fieldProps.tooltip]);

    const renderField = () => {
        switch (fieldProps.type) {
            case 'heading':
                return <Heading level={fieldProps.level || 3} className="splms-field-heading">{ translateLabel(fieldProps.label)}</Heading>;

            case 'number':
                return (
                    <NumberControl 
                        {...fieldProps} 
                        __next40pxDefaultSize 
                        min={fieldProps.min || 0}
                        max={fieldProps.max || undefined}
                        step={fieldProps.step || 1}
                    />
                );

            case 'range':
                return (
                    <RangeControl
                        {...fieldProps}
                        min={fieldProps.min || 0}
                        max={fieldProps.max || 100}
                        step={fieldProps.step || 1}
                        withInputField={true}
                    />
                );

            case 'text':
            case 'textbox':
            case 'url':
            case 'email':
                return (
                    <TextControl 
                        {...fieldProps} 
                        type={fieldProps.type === 'url' ? 'url' : fieldProps.type === 'email' ? 'email' : 'text'}
                    />
                );

            case 'password':
                return (
                    <>
                        <TextControl 
                            {...fieldProps} 
                            type={showPassword ? 'text' : 'password'}
                        />
                        <button
                            type="button"
                            className="splms-password-toggle"
                            onClick={() => setShowPassword(!showPassword)}
                            aria-label={showPassword ? __('Hide password', 'skillpulse-lms') : __('Show password', 'skillpulse-lms')}
                        >
                            {showPassword ? 
                                <SplmsIcon name="eye-off" size={16} /> :
                                <SplmsIcon name="eye" size={16} />
                            }
                        </button>
                    </>
                );

            case 'textarea':
                return (
                    <TextareaControl 
                        {...fieldProps}
                        rows={fieldProps.rows || 4}
                    />
                );

            case 'checkbox':
                return (
                    <CheckboxControl 
                        {...fieldProps} 
                        checked={fieldProps.value || false}
                        className="splms-checkbox"
                    />
                );

            case 'radio':
                // Ensure options are correctly structured with label and value
                // Create a fresh array to avoid mutation issues
                const radioOptions = fieldProps.options && Array.isArray(fieldProps.options) 
                    ? fieldProps.options.map((option, index) => {
                        // Handle both object format {label, value} and other formats
                        if (option && typeof option === 'object') {
                            // Ensure value is properly converted to string
                            // Handle boolean false correctly (String(false) = "false")
                            let optionValue = option.value;
                            if (optionValue === false || optionValue === true) {
                                optionValue = String(optionValue);
                            } else if (optionValue !== undefined && optionValue !== null) {
                                optionValue = String(optionValue);
                            } else {
                                optionValue = '';
                            }
                            
                            return {
                                label: translateLabel(option.label) || String(optionValue || ''),
                                value: optionValue
                            };
                        }
                        return {
                            label: translateLabel(String(option)),
                            value: String(option)
                        };
                    }) 
                    : [];
                
                return (
                    <RadioControl 
                        label={fieldProps.label}
                        help={fieldProps.help}
                        selected={fieldProps.value || ''}
                        options={radioOptions}
                        onChange={fieldProps.onChange}
                        className="splms-radio"
                    />
                );

            case 'color':
                return (
                    <BaseControl label={fieldProps.label} help={fieldProps.help}>
                        <ColorPalette 
                            value={fieldProps.value || fieldProps.default || 'var(--splms-primary, #7e75ff)'} 
                            onChange={fieldProps.onChange}
                            clearable={true}
                        />
                    </BaseControl>
                );

            case 'select':
                return (
                    <SelectControl
                        {...fieldProps}
                        options={loading ? [{ label: __('Loading...', 'skillpulse-lms'), value: '' }] : options}
                        />
                );

            case 'search_select':
                // Check if this is a page field that should have create functionality
                const isPageField = fieldProps.id && fieldProps.id.includes('page_id');
                const pageType = isPageField ? fieldProps.id : '';

                return (
                    <SearchableSelect
                        {...fieldProps}
                        options={options}
                        loading={loading}
                        api={fieldProps.api}
                        showCreateButton={isPageField}
                        pageType={pageType}
                        createButtonText={__('Create Page', 'skillpulse-lms')}
                    />
                );

            case 'multi-select':
                // Use LazyMultiSelect if API config is provided (for lazy loading)
                if (fieldProps.api) {
                    return (
                        <LazyMultiSelect
                            {...fieldProps}
                            api={fieldProps.api}
                            loading={loading}
                        />
                    );
                }
                
                // Use StaticMultiSelect for static options
                return (
                    <StaticMultiSelect
                        {...fieldProps}
                        options={fieldProps.options || []}
                    />
                );

            case 'toggle':
                return (
                    <ToggleControl 
                        {...fieldProps} 
                        checked={fieldProps.value || false}
                        className="splms-toggle"
                        disabled={fieldProps.disabled}
                    />
                );

            case 'date':
                // Use dateRange config from props
                const dateRangeConfig = fieldProps.dateRange || null;
                
                return (
                    <CustomDatePicker
                        {...fieldProps}
                        dateRange={dateRangeConfig}
                        formData={fieldProps.formData}
                        minDate={fieldProps.minDate}
                        maxDate={fieldProps.maxDate}
                        validate={fieldProps.validate}
                    />
                );

            case 'button':
                return (
                    <Button 
                        {...fieldProps} 
                        disabled={loading || fieldProps.disabled}
                        className="splms-button"
                    >
                        {fieldProps.label}
                    </Button>
                );

            case 'file':
                return (
                    <BaseControl label={fieldProps.label} help={fieldProps.help}>
                        <FormFileUpload 
                            {...fieldProps} 
                            accept={fieldProps.mode === 'image' ? 'image/*' : fieldProps.accept}
                            className="splms-file-upload"
                        >
                            {fieldProps.value ? (
                                fieldProps.mode === 'image' ? 
                                    <img src={fieldProps.value.url} alt={fieldProps.value.filename} style={{maxWidth: '200px', maxHeight: '200px'}} /> : 
                                    fieldProps.value.filename
                            ) : (
                                __('Upload File', 'skillpulse-lms')
                            )}
                        </FormFileUpload>
                    </BaseControl>
                );

            case 'media-url':
                // Determine media type from field ID or explicit prop
                let mediaType = '';
                if (fieldProps.mediaType) {
                    mediaType = fieldProps.mediaType;
                } else if (fieldProps.id) {
                    // Auto-detect from field ID
                    if (fieldProps.id.includes('video')) {
                        mediaType = 'video';
                    } else if (fieldProps.id.includes('audio')) {
                        mediaType = 'audio';
                    } else if (fieldProps.id.includes('document')) {
                        mediaType = 'document';
                    }
                }
                
                return (
                    <MediaUrlField
                        {...fieldProps}
                        mediaType={mediaType}
                    />
                );

            case 'separator':
                return <hr className="splms-field-separator" />;

            case 'info':
                return (
                    <Card className="splms-info-card">
                        <CardBody>
                            <div className="splms-info-content">
                                {fieldProps.icon && <SplmsIcon name={fieldProps.icon} size={24} />}
                                <div>
                                    {fieldProps.label && <h4>{fieldProps.label}</h4>}
                                    {fieldProps.description && <p>{fieldProps.description}</p>}
                                </div>
                            </div>
                        </CardBody>
                    </Card>
                );

            case 'calculated':
            case 'html':
                return (
                    <BaseControl label={fieldProps.label} help={fieldProps.help}>
                        <div 
                            className="splms-calculated-field"
                            style={fieldProps.type === 'calculated' ? { 
                                background: 'var(--splms-background-secondary, #f8f9fa)', 
                                padding: '12px', 
                                borderRadius: '4px',
                                border: '1px solid var(--splms-border-color, #e2e4e7)'
                            } : {}}
                            dangerouslySetInnerHTML={fieldProps.type === 'html' ? { __html: fieldProps.value } : undefined}
                        >
                            {fieldProps.type === 'calculated' && (fieldProps.value || fieldProps.placeholder || __('No value calculated', 'skillpulse-lms'))}
                        </div>
                    </BaseControl>
                );

            case 'editor':
            case 'tinymce':
            case 'richtext':
                // Handle editor_settings configuration from PHP config
                const editorSettings = fieldProps.editor_settings || {};
                const tinymceSettings = editorSettings.tinymce || {};

                return (
                    <WordPressEditor
                        {...fieldProps}
                        height={tinymceSettings.height || editorSettings.textarea_rows * 20 || 200}
                        enableWordPress={editorSettings.media_buttons !== false}
                        enableImageUpload={editorSettings.media_buttons !== false}
                        placeholder={fieldProps.placeholder || __('Enter content...', 'skillpulse-lms')}
                    />
                );

            case 'repeater':
            case 'repeatable-group':
                // Map item_fields to fields for RepeaterField
                const repeaterFields = fieldProps.item_fields || fieldProps.fields || [];
                return (
                    <RepeaterField
                        {...fieldProps}
                        fields={repeaterFields}
                    />
                );

            case 'custom':
                const CustomComponent = fieldProps.component;
                return CustomComponent ? <CustomComponent {...fieldProps} /> : null;

            default:
                console.warn(`${fieldProps.type} is not a valid field type`);
                return (
                    <div className="splms-field-error">
                        <p>{__(`Unsupported field type: ${fieldProps.type}`, 'skillpulse-lms')}</p>
                    </div>
                );
        }
    };

    return (
        <div className={`splms-field-wrapper ${fieldProps.fullWidth ? 'splms-field-full-width' : ''}`}>
            {renderFieldWithTooltip(renderField())}
            {fieldProps.note && (
                <div className="splms-field-note">
                    <small>{fieldProps.note}</small>
                </div>
            )}
        </div>
    );
});

export default Field; 