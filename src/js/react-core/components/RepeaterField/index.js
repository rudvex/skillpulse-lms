import { useState, useEffect, useCallback, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, BaseControl, TextControl, TextareaControl, CheckboxControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';
import { translateLabel } from '../../utility/helper';
import './styles.scss';

const RepeaterField = (props) => {
    const { value, onChange, fields = [], label, help, icon, itemName, min, max } = props;
    const isInitialMount = useRef(true);
    
    // Initialize with empty array if value is null/undefined
    const [items, setItems] = useState(() => {
        if (!value) {
            return [];
        }
        
        // If value is an array, use it
        if (Array.isArray(value)) {
            return value;
        }
        
        return [];
    });

    // Sync with external value changes (but not on initial mount)
    useEffect(() => {
        if (isInitialMount.current) {
            isInitialMount.current = false;
            return;
        }

        // Only update if value prop changed externally
        if (value !== undefined && JSON.stringify(value) !== JSON.stringify(items)) {
            if (Array.isArray(value)) {
                setItems(value);
            }
        }
    }, [value]);

    const addItem = useCallback(() => {
        if (max && items.length >= max) {
            return;
        }
        const newItem = {};
        fields.forEach(field => {
            if (field.type === 'checkbox' || field.type === 'radio') {
                newItem[field.id] = field.default || false;
            } else {
                newItem[field.id] = field.default || '';
            }
        });
        setItems(prev => {
            const updated = [...prev, newItem];
            if (onChange) {
                onChange(updated);
            }
            return updated;
        });
    }, [fields, onChange, max, items.length]);

    const removeItem = useCallback((index) => {
        if (min && items.length <= min) {
            return;
        }
        setItems(prev => {
            const updated = prev.filter((_, i) => i !== index);
            if (onChange) {
                onChange(updated);
            }
            return updated;
        });
    }, [onChange, min, items.length]);

    const updateItem = useCallback((index, fieldId, fieldValue) => {
        setItems(prev => {
            const updated = [...prev];
            
            // Check if this field is a radio button that requires mutual exclusivity
            const field = fields.find(f => f.id === fieldId);
            if (field && field.type === 'radio' && fieldValue === true) {
                // If this is a radio button being set to true, set all others to false
                updated.forEach((item, idx) => {
                    if (idx !== index) {
                        updated[idx] = {
                            ...item,
                            [fieldId]: false
                        };
                    }
                });
            }
            
            updated[index] = {
                ...updated[index],
                [fieldId]: fieldValue
            };
            if (onChange) {
                onChange(updated);
            }
            return updated;
        });
    }, [onChange, fields]);

    const moveItem = useCallback((index, direction) => {
        setItems(prev => {
            const updated = [...prev];
            const newIndex = index + direction;
            if (newIndex >= 0 && newIndex < updated.length) {
                [updated[index], updated[newIndex]] = [updated[newIndex], updated[index]];
            }
            if (onChange) {
                onChange(updated);
            }
            return updated;
        });
    }, [onChange]);

    const openMediaLibrary = useCallback((itemIndex, fieldId) => {
        if (typeof wp === 'undefined' || !wp.media) {
            // Prompt for URL
            const url = prompt(__('Enter the file URL:', 'skillpulse-lms'));
            if (url) {
                updateItem(itemIndex, fieldId, url);
            }
            return;
        }

        const frame = wp.media({
            title: __('Select or Upload File', 'skillpulse-lms'),
            button: {
                text: __('Use this file', 'skillpulse-lms')
            },
            multiple: false,
            library: {
                type: '' // Allow all file types
            }
        });

        frame.on('select', () => {
            const attachment = frame.state().get('selection').first().toJSON();
            updateItem(itemIndex, fieldId, attachment.url);
        });

        frame.open();
    }, [updateItem]);

    const renderField = (field, itemValue, itemIndex) => {
        const fieldValue = itemValue[field.id] !== undefined ? itemValue[field.id] : (field.default !== undefined ? field.default : (field.type === 'checkbox' ? false : ''));
        
        switch (field.type) {
            case 'file':
                return (
                    <div className="splms-repeater-file-field">
                        {fieldValue ? (
                            <div className="splms-repeater-file-preview">
                                <div className="splms-repeater-file-info">
                                    <span className="splms-repeater-file-url" title={fieldValue}>
                                        {fieldValue.split('/').pop()}
                                    </span>
                                    <Button
                                        isDestructive
                                        isSmall
                                        onClick={() => updateItem(itemIndex, field.id, '')}
                                    >
                                        {__('Remove', 'skillpulse-lms')}
                                    </Button>
                                </div>
                                <div className="splms-repeater-file-url-full" title={fieldValue}>
                                    {fieldValue}
                                </div>
                            </div>
                        ) : null}
                        <Button
                            variant={fieldValue ? "secondary" : "primary"}
                            isSmall={!!fieldValue}
                            onClick={() => openMediaLibrary(itemIndex, field.id)}
                        >
                            {fieldValue ? __('Change File', 'skillpulse-lms') : (translateLabel(field.label) || __('Select File', 'skillpulse-lms'))}
                        </Button>
                    </div>
                );
            
            case 'checkbox':
                return (
                    <CheckboxControl
                        label={translateLabel(field.label)}
                        checked={fieldValue || false}
                        onChange={(newValue) => updateItem(itemIndex, field.id, newValue)}
                    />
                );
            
            case 'radio':
                // For radio buttons in repeatable groups (like "is_correct" in multiple choice),
                // we ensure mutual exclusivity (only one can be true)
                // Use a simple checkbox interface but with mutual exclusivity logic
                return (
                    <div className="splms-repeater-radio-field">
                        <CheckboxControl
                            label={translateLabel(field.label)}
                            checked={fieldValue || false}
                            onChange={(newValue) => {
                                // If setting to true, the updateItem callback will handle
                                // deselecting all other items automatically
                                updateItem(itemIndex, field.id, newValue);
                            }}
                            help={fieldValue ? __('This is the correct answer', 'skillpulse-lms') : __('Mark this as the correct answer', 'skillpulse-lms')}
                        />
                    </div>
                );
            
            case 'textarea':
                return (
                    <TextareaControl
                        label={translateLabel(field.label)}
                        value={fieldValue || ''}
                        onChange={(newValue) => updateItem(itemIndex, field.id, newValue)}
                        placeholder={translateLabel(field.placeholder) || ''}
                        rows={field.rows || 3}
                    />
                );
            
            case 'number':
                return (
                    <NumberControl
                        label={translateLabel(field.label)}
                        value={fieldValue !== undefined && fieldValue !== '' ? fieldValue : (field.default || 0)}
                        onChange={(newValue) => updateItem(itemIndex, field.id, newValue !== undefined ? newValue : '')}
                        min={field.min}
                        max={field.max}
                        step={field.step || 1}
                        __next40pxDefaultSize
                    />
                );
            
            case 'text':
            default:
                return (
                    <TextControl
                        value={fieldValue || ''}
                        onChange={(newValue) => updateItem(itemIndex, field.id, newValue)}
                        placeholder={translateLabel(field.placeholder) || ''}
                        label={translateLabel(field.label)}
                    />
                );
        }
    };

    return (
        <BaseControl
            label={translateLabel(label)}
            help={translateLabel(help)}
            className="splms-repeater-field"
        >
            <div className="splms-repeater-items">
                {items.map((item, index) => (
                    <div key={index} className="splms-repeater-item">
                        <div className="splms-repeater-item-header">
                            <span className="splms-repeater-item-number">
                                {translateLabel(itemName) || __('Item', 'skillpulse-lms')} {index + 1}
                            </span>
                            <div className="splms-repeater-item-actions">
                                <Button
                                    isSmall
                                    disabled={index === 0}
                                    onClick={() => moveItem(index, -1)}
                                    icon="arrow-up-alt2"
                                    label={__('Move Up', 'skillpulse-lms')}
                                />
                                <Button
                                    isSmall
                                    disabled={index === items.length - 1}
                                    onClick={() => moveItem(index, 1)}
                                    icon="arrow-down-alt2"
                                    label={__('Move Down', 'skillpulse-lms')}
                                />
                                <Button
                                    isSmall
                                    isDestructive
                                    onClick={() => removeItem(index)}
                                    icon="trash"
                                    label={__('Remove', 'skillpulse-lms')}
                                    disabled={min && items.length <= min}
                                />
                            </div>
                        </div>
                        <div className="splms-repeater-item-fields">
                            {fields.map((field) => (
                                <div key={field.id} className="splms-repeater-item-field">
                                    {renderField(field, item, index)}
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
            <Button
                variant="secondary"
                onClick={addItem}
                icon="plus-alt2"
                className="splms-repeater-add-button"
                disabled={max && items.length >= max}
            >
                {__('Add Item', 'skillpulse-lms')}
            </Button>
        </BaseControl>
    );
};

export default RepeaterField;