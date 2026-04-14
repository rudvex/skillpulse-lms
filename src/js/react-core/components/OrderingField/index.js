/**
 * OrderingField Component
 * Provides a sortable drag-and-drop list for ordering question items
 */
import { __ } from '@wordpress/i18n';
import { useState, useEffect, useRef } from '@wordpress/element';
import { 
    Button, 
    BaseControl,
    TextControl
} from '@wordpress/components';
import { translateLabel } from '../../utility/helper';
// No external dependencies - using native array manipulation
import './styles.scss';

const OrderingField = ({ value = [], onChange, label, help, min = 2, max = 15 }) => {
    // Track if we're updating from internal changes to prevent reset
    const isInternalUpdate = useRef(false);
    
    const normalizeItems = (items, preserveOrder = false) => {
        if (!Array.isArray(items) || items.length === 0) {
            return [{ text: '', order: 0 }, { text: '', order: 1 }];
        }
        return items.map((item, index) => {
            if (typeof item === 'string') {
                return { text: item, order: index };
            }
            // Preserve all existing properties, but ensure text and order are set
            return { 
                ...item, 
                text: item.text || '', 
                order: preserveOrder && item.order !== undefined ? item.order : index 
            };
        });
    };

    const [items, setItems] = useState(() => normalizeItems(value, true));
    const previousValueRef = useRef(JSON.stringify(value));

    // Update local state when value prop changes (but avoid infinite loops)
    useEffect(() => {
        // Skip update if this change came from our own internal update
        if (isInternalUpdate.current) {
            isInternalUpdate.current = false;
            previousValueRef.current = JSON.stringify(value);
            return;
        }

        const currentValueString = JSON.stringify(value);
        
        // Only update if value actually changed
        if (currentValueString === previousValueRef.current) {
            return;
        }

        previousValueRef.current = currentValueString;

        if (Array.isArray(value) && value.length > 0) {
            // Preserve order property when syncing from props
            const normalized = normalizeItems(value, true);
            setItems(normalized);
        } else if (!Array.isArray(value) || value.length === 0) {
            // If value becomes empty, reset to default
            setItems([{ text: '', order: 0 }, { text: '', order: 1 }]);
        }
    }, [value]);

    const addItem = () => {
        if (items.length < max) {
            const newItems = [...items, { text: '', order: items.length }];
            // Update order for all items to reflect their current position
            const itemsWithOrder = newItems.map((item, idx) => ({ ...item, order: idx }));
            setItems(itemsWithOrder);
            isInternalUpdate.current = true;
            onChange(itemsWithOrder);
        }
    };

    const removeItem = (index) => {
        if (items.length > min) {
            const newItems = items.filter((_, i) => i !== index);
            // Update order for all remaining items to reflect their new position
            const itemsWithOrder = newItems.map((item, idx) => ({ ...item, order: idx }));
            setItems(itemsWithOrder);
            isInternalUpdate.current = true;
            onChange(itemsWithOrder);
        }
    };

    const updateItem = (index, newText) => {
        const newItems = [...items];
        newItems[index] = { ...newItems[index], text: newText };
        setItems(newItems);
        isInternalUpdate.current = true;
        onChange(newItems);
    };

    const moveItem = (fromIndex, toIndex) => {
        const newItems = [...items];
        const [removed] = newItems.splice(fromIndex, 1);
        newItems.splice(toIndex, 0, removed);
        // Update order property for all items to reflect their new position
        const itemsWithOrder = newItems.map((item, idx) => ({ ...item, order: idx }));
        setItems(itemsWithOrder);
        isInternalUpdate.current = true;
        onChange(itemsWithOrder);
    };

    const handleDragStart = (e, index) => {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', e.target.outerHTML);
        e.dataTransfer.setData('text/plain', index.toString());
        e.currentTarget.classList.add('splms-ordering-item-dragging');
    };

    const handleDragOver = (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    };

    const handleDragEnter = (e) => {
        e.preventDefault();
        e.currentTarget.classList.add('splms-ordering-item-drag-over');
    };

    const handleDragLeave = (e) => {
        e.currentTarget.classList.remove('splms-ordering-item-drag-over');
    };

    const handleDrop = (e, dropIndex) => {
        e.preventDefault();
        e.currentTarget.classList.remove('splms-ordering-item-drag-over');
        
        const dragIndex = parseInt(e.dataTransfer.getData('text/plain'), 10);
        
        if (dragIndex !== dropIndex && !isNaN(dragIndex)) {
            moveItem(dragIndex, dropIndex);
        }
        
        // Remove dragging class from all items
        document.querySelectorAll('.splms-ordering-item-dragging').forEach(el => {
            el.classList.remove('splms-ordering-item-dragging');
        });
    };

    const handleDragEnd = (e) => {
        e.currentTarget.classList.remove('splms-ordering-item-dragging');
    };

    return (
        <BaseControl
            label={translateLabel(label) || __('Ordered Items', 'skillpulse-lms')}
            help={translateLabel(help) || __('Drag items to reorder them. The order shown here is the correct answer sequence.', 'skillpulse-lms')}
            className="splms-ordering-field"
        >
            <div className="splms-ordering-field-list">
                {items.map((item, index) => (
                    <div
                        key={index}
                        className="splms-ordering-field-item"
                        draggable
                        onDragStart={(e) => handleDragStart(e, index)}
                        onDragOver={handleDragOver}
                        onDragEnter={handleDragEnter}
                        onDragLeave={handleDragLeave}
                        onDrop={(e) => handleDrop(e, index)}
                        onDragEnd={handleDragEnd}
                    >
                        <span className="splms-ordering-field-handle" title={__('Drag to reorder', 'skillpulse-lms')}>
                            ☰
                        </span>
                        <TextControl
                            value={item.text || ''}
                            onChange={(newValue) => updateItem(index, newValue)}
                            placeholder={__('Enter item text...', 'skillpulse-lms')}
                            className="splms-ordering-field-text splms-field"
                        />
                        <Button
                            isDestructive
                            isSmall
                            onClick={() => removeItem(index)}
                            disabled={items.length <= min}
                            className="splms-ordering-field-remove"
                            title={__('Remove item', 'skillpulse-lms')}
                        >
                            ×
                        </Button>
                    </div>
                ))}
            </div>
            {items.length < max && (
                <Button
                    variant="secondary"
                    onClick={addItem}
                    className="splms-ordering-field-add"
                >
                    {__('Add Item', 'skillpulse-lms')}
                </Button>
            )}
            {items.length >= max && (
                <p className="splms-ordering-field-max-note">
                    {__('Maximum number of items reached.', 'skillpulse-lms')}
                </p>
            )}
        </BaseControl>
    );
};

export default OrderingField;

