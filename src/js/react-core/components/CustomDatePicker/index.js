import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { 
    BaseControl,
    Button,
    Popover
} from '@wordpress/components';
import { SplmsIcon } from '../SplmsIcon';
import './styles.scss';

const CustomDatePicker = (props) => {
    const [isOpen, setIsOpen] = useState(false);
    const [displayValue, setDisplayValue] = useState('');
    const [dateValue, setDateValue] = useState('');
    const [timeValue, setTimeValue] = useState('');
    const [validationError, setValidationError] = useState('');
    const [isValidating, setIsValidating] = useState(false);
    const dateInputRef = useRef(null);
    const timeoutRef = useRef(null);

    useEffect(() => {
        if (props.value) {
            try {
                const date = new Date(props.value);
                if (!isNaN(date.getTime())) {
                    // Use local date to avoid timezone issues
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    
                    // Format for display (consistent with original)
                    const formattedDate = date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: '2-digit'
                    });
                    const formattedTime = `${hours}:${minutes}`;
                    
                    setDisplayValue(`${formattedDate} ${formattedTime}`);
                    // Use local date string to avoid timezone shifts
                    setDateValue(`${year}-${month}-${day}`);
                    setTimeValue(`${hours}:${minutes}`);
                }
            } catch (error) {
                console.error('Error parsing date:', error);
            }
        } else {
            setDisplayValue('');
            setDateValue('');
            setTimeValue('');
        }
    }, [props.value]);

    // Debounced validation function
    const debouncedValidation = useCallback((date, time) => {
        if (timeoutRef.current) {
            clearTimeout(timeoutRef.current);
        }
        
        timeoutRef.current = setTimeout(() => {
            setIsValidating(true);
            const isValid = validateDate(date, time);
            setIsValidating(false);
            
            if (!isValid && date) {
                // Show validation error but don't clear the field immediately
                // Allow user to see what they entered and the error message
            }
        }, 300);
    }, []);

    // Re-validate when related fields change (cross-field validation)
    useEffect(() => {
        if (isDateRangeField() && dateValue) {
            debouncedValidation(dateValue, timeValue);
        }
    }, [props.formData, debouncedValidation]);

    // Clean up timeout on unmount
    useEffect(() => {
        return () => {
            if (timeoutRef.current) {
                clearTimeout(timeoutRef.current);
            }
        };
    }, []);

    // Generic date range validation
    const isDateRangeField = () => {
        return props.dateRange && (props.dateRange.startField || props.dateRange.endField);
    };

    const getRelatedFieldId = () => {
        if (!props.dateRange) return null;
        
        if (props.id === props.dateRange.startField) {
            return props.dateRange.endField;
        }
        if (props.id === props.dateRange.endField) {
            return props.dateRange.startField;
        }
        return null;
    };

    const getRelatedDateValue = useCallback(() => {
        if (!isDateRangeField()) return null;
        
        const relatedFieldId = getRelatedFieldId();
        if (!relatedFieldId) return null;
        
        // Try to get from form data first (most reliable)
        if (props.formData && props.formData[relatedFieldId]) {
            return props.formData[relatedFieldId];
        }
        
        // Try to get from WordPress store (if available)
        if (typeof wp !== 'undefined' && wp.data && wp.data.select) {
            try {
                const postMeta = wp.data.select('core/editor')?.getEditedPostAttribute('meta');
                if (postMeta && postMeta[relatedFieldId]) {
                    return postMeta[relatedFieldId];
                }
            } catch (error) {
                console.warn('WordPress store access failed:', error);
            }
        }
        
        // Use DOM search with multiple selectors
        const selectors = [
            `[data-field-id="${relatedFieldId}"] input`,
            `input[data-field-id="${relatedFieldId}"]`,
            `[name="${relatedFieldId}"]`,
            `#${relatedFieldId}`
        ];
        
        for (const selector of selectors) {
            const relatedField = document.querySelector(selector);
            if (relatedField && relatedField.value) {
                return relatedField.value;
            }
        }
        
        return null;
    }, [props.formData, isDateRangeField, getRelatedFieldId]);

    const isStartField = () => {
        return props.dateRange && props.id === props.dateRange.startField;
    };

    const isEndField = () => {
        return props.dateRange && props.id === props.dateRange.endField;
    };

    const getMinDate = useCallback(() => {
        // Use custom min date if provided
        if (props.minDate) return props.minDate;
        
        if (!isDateRangeField()) return null;
        
        const today = new Date().toISOString().split('T')[0];
        
        if (isEndField()) {
            // End field: minimum is start date or today (whichever is later)
            const startDateValue = getRelatedDateValue();
            if (startDateValue) {
                const startDateStr = startDateValue.split('T')[0];
                return startDateStr > today ? startDateStr : today;
            }
            return today;
        } else if (isStartField()) {
            // Start field: allow today by default (more user-friendly)
            return today;
        }
        
        return null;
    }, [props.minDate, isDateRangeField, isEndField, isStartField, getRelatedDateValue]);

    const getMaxDate = useCallback(() => {
        // Use custom max date if provided
        if (props.maxDate) return props.maxDate;
        
        if (!isDateRangeField()) return null;
        
        if (isStartField()) {
            // Start field: maximum is end date (if set)
            const endDateValue = getRelatedDateValue();
            if (endDateValue) {
                return endDateValue.split('T')[0];
            }
        }
        
        return null;
    }, [props.maxDate, isDateRangeField, isStartField, getRelatedDateValue]);

    // Enhanced validation with better error messages
    const validateDate = useCallback((selectedDate, selectedTime = '') => {
        if (!selectedDate) {
            setValidationError('');
            return true;
        }

        // Parse dates as simple date strings (YYYY-MM-DD) for comparison
        const selectedDateStr = selectedDate.split('T')[0]; // Get just the date part
        
        // Check custom validation function first
        if (props.validate && typeof props.validate === 'function') {
            const customValidation = props.validate(selectedDateStr, props.id);
            if (!customValidation.isValid) {
                setValidationError(customValidation.message);
                return false;
            }
        }
        
        // Check min/max date constraints
        const minDate = getMinDate();
        const maxDate = getMaxDate();
        
        if (minDate && selectedDateStr < minDate) {
            const minDateFormatted = new Date(minDate).toLocaleDateString();
            if (isEndField()) {
                setValidationError(__(`End date must be on or after ${minDateFormatted}`, 'skillpulse-lms'));
            } else {
                setValidationError(__(`Date must be on or after ${minDateFormatted}`, 'skillpulse-lms'));
            }
            return false;
        }
        
        if (maxDate && selectedDateStr > maxDate) {
            const maxDateFormatted = new Date(maxDate).toLocaleDateString();
            if (isStartField()) {
                setValidationError(__(`Start date must be on or before ${maxDateFormatted}`, 'skillpulse-lms'));
            } else {
                setValidationError(__(`Date must be on or before ${maxDateFormatted}`, 'skillpulse-lms'));
            }
            return false;
        }

        // Check date range validation if configured
        if (isDateRangeField()) {
            const relatedDateValue = getRelatedDateValue();
            
            if (relatedDateValue) {
                const relatedDateStr = relatedDateValue.split('T')[0]; // Get just the date part
                
                if (isEndField()) {
                    // End field must be after start field
                    if (selectedDateStr <= relatedDateStr) {
                        const startDateFormatted = new Date(relatedDateStr).toLocaleDateString();
                        setValidationError(__(`Enrollment end date must be after start date (${startDateFormatted})`, 'skillpulse-lms'));
                        return false;
                    }
                } else if (isStartField()) {
                    // Start field must be before end field
                    if (selectedDateStr >= relatedDateStr) {
                        const endDateFormatted = new Date(relatedDateStr).toLocaleDateString();
                        setValidationError(__(`Enrollment start date must be before end date (${endDateFormatted})`, 'skillpulse-lms'));
                        return false;
                    }
                }
            } 
        }

        setValidationError('');
        return true;
    }, [props.validate, props.id, getMinDate, getMaxDate, isDateRangeField, getRelatedDateValue, isEndField, isStartField]);

    const handleDateChange = useCallback((newDate) => {
        // Always update the local state first to show user input
        setDateValue(newDate);
        
        // Clear previous validation error when user starts typing
        if (validationError) {
            setValidationError('');
        }
        
        // If date is cleared, update parent immediately
        if (!newDate) {
            updateValue('', timeValue);
            return;
        }
        
        // Validate and update parent if valid
        if (validateDate(newDate, timeValue)) {
            updateValue(newDate, timeValue);
        }
        // Note: We don't prevent the state update if validation fails
        // This allows user to see their input and the validation error
    }, [timeValue, validationError, validateDate]);

    const handleTimeChange = useCallback((newTime) => {
        setTimeValue(newTime);
        
        // Clear previous validation error when user starts typing
        if (validationError) {
            setValidationError('');
        }
        
        // If we have a date, validate the combination
        if (dateValue) {
            if (validateDate(dateValue, newTime)) {
                updateValue(dateValue, newTime);
            }
        } else if (newTime) {
            // If only time is set, just update the time value
            updateValue(dateValue, newTime);
        }
    }, [dateValue, validationError, validateDate]);

    const updateValue = (date, time) => {
        if (date && time) {
            // Ensure time has seconds if not provided
            const timeWithSeconds = time.includes(':') && time.split(':').length === 2 ? `${time}:00` : time;
            const dateTime = new Date(`${date}T${timeWithSeconds}`);
            if (!isNaN(dateTime.getTime())) {
                const isoString = dateTime.toISOString();
                const formattedDate = dateTime.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit'
                });
                // Use consistent 24-hour format
                const hours = String(dateTime.getHours()).padStart(2, '0');
                const minutes = String(dateTime.getMinutes()).padStart(2, '0');
                const formattedTime = `${hours}:${minutes}`;
                
                setDisplayValue(`${formattedDate} ${formattedTime}`);
                if (props.onChange) {
                    props.onChange(isoString);
                }
            }
        } else if (date) {
            const dateOnly = new Date(`${date}T00:00:00`);
            if (!isNaN(dateOnly.getTime())) {
                const formattedDate = dateOnly.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit'
                });
                setDisplayValue(formattedDate);
                if (props.onChange) {
                    props.onChange(dateOnly.toISOString());
                }
            }
        } else {
            setDisplayValue('');
            if (props.onChange) {
                props.onChange('');
            }
        }
    };

    const clearValue = () => {
        setDisplayValue('');
        setDateValue('');
        setTimeValue('');
        if (props.onChange) {
            props.onChange('');
        }
    };

    const openPicker = () => {
        // Ensure modal reflects current state when opening
        if (props.value) {
            try {
                const date = new Date(props.value);
                if (!isNaN(date.getTime())) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    
                    setDateValue(`${year}-${month}-${day}`);
                    setTimeValue(`${hours}:${minutes}`);
                }
            } catch (error) {
                console.error('Error syncing modal state:', error);
            }
        }
        setIsOpen(true);
    };
    const closePicker = () => setIsOpen(false);

    // Helper text for date range fields
    const getHelpText = () => {
        if (props.help) return props.help;
        
        if (isDateRangeField()) {
            if (isStartField()) {
                return __('Select when enrollment opens. End date must be after this date.', 'skillpulse-lms');
            } else if (isEndField()) {
                return __('Select when enrollment closes. Must be after the start date.', 'skillpulse-lms');
            }
        }
        
        return null;
    };

    return (
        <BaseControl label={props.label} help={getHelpText()}>
            <div className="splms-custom-date-picker" data-field-id={props.id}>
                <div className={`date-picker-input ${validationError ? 'has-error' : ''}`} onClick={openPicker}>
                    <div className="input-content">
                        {props.icon && (
                            <SplmsIcon name={props.icon} size={16} />
                        )}
                        <span className={`input-text ${!displayValue ? 'placeholder' : ''}`}>
                            {displayValue || props.placeholder || __('Select date and time', 'skillpulse-lms')}
                        </span>
                    </div>
                    <SplmsIcon name="calendar" size={16} className="calendar-icon" />
                </div>
                
                {validationError && (
                    <div className="date-picker-validation-notice splms-error-message" style={{ padding: '8px 12px', background: 'var(--splms-danger-bg, #f8d7da)', border: '1px solid var(--splms-danger-border, #f5c6cb)', borderRadius: 'var(--splms-border-radius, 4px)', color: 'var(--splms-danger-text, #721c24)', marginTop: '8px', fontSize: '13px' }}>
                        {validationError}
                    </div>
                )}

                {isValidating && (
                    <div className="date-picker-validating">
                        <span className="spinner is-active" style={{ float: 'none', margin: '0 5px 0 0' }} />
                        {__('Validating...', 'skillpulse-lms')}
                    </div>
                )}

                {isOpen && (
                    <Popover
                        position="bottom left"
                        onClose={closePicker}
                        className="splms-date-picker-popover"
                    >
                        <div className="date-picker-content">
                            <div className="date-picker-header">
                                <h4>{__('Select Date & Time', 'skillpulse-lms')}</h4>
                                <Button
                                    icon="no-alt"
                                    onClick={closePicker}
                                    className="close-button"
                                />
                            </div>
                            
                            <div className="date-picker-fields">
                                <div className="field-group">
                                    <label>{__('Date', 'skillpulse-lms')}</label>
                                    <input
                                        ref={dateInputRef}
                                        type="date"
                                        value={dateValue}
                                        onChange={(e) => handleDateChange(e.target.value)}
                                        className="date-input"
                                        min={getMinDate()}
                                        max={getMaxDate()}
                                    />
                                </div>
                                
                                <div className="field-group">
                                    <label>{__('Time', 'skillpulse-lms')}</label>
                                    <input
                                        type="time"
                                        value={timeValue}
                                        onChange={(e) => handleTimeChange(e.target.value)}
                                        className="time-input"
                                        step="60"
                                    />
                                </div>
                            </div>

                            <div className="date-picker-actions">
                                <Button
                                    isSecondary
                                    onClick={clearValue}
                                    className="clear-button"
                                >
                                    {__('Clear', 'skillpulse-lms')}
                                </Button>
                                <Button
                                    isPrimary
                                    onClick={closePicker}
                                    className="apply-button"
                                >
                                    {__('Apply', 'skillpulse-lms')}
                                </Button>
                            </div>
                        </div>
                    </Popover>
                )}
            </div>
        </BaseControl>
    );
};

export default CustomDatePicker;
