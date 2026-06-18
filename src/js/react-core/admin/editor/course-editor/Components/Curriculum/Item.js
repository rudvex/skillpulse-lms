import { useDrag, useDrop } from 'react-dnd';

import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { useState, useCallback, useEffect, useMemo } from '@wordpress/element';
import React from 'react';
import { TextControl, Button, Icon, Animate, Tooltip } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';

import { SplmsIcon } from '../../../../../components/SplmsIcon';
import { getPostUrl } from "../../../../../utility/helper";
import { getPostEditUrl } from "../../../../../utility/url";

const Item = (props) => {

    const {
        sectionId,
        item,
        isEditing,
        startEditingItem,
        updateEditingItem,
        blurEditingItem,
        deleteItem,
        moveChildren,
        index,
        className = '',
    } = props;

    const [isHovering, setIsHovering] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);

    const { id:editingId, title:editingName } = props.editingItem;

    const [{ isDragging }, drag] = useDrag({
        type: 'children',
        item: { id: item.id, sectionId, type: 'children' },
        collect: (monitor) => ({
            isDragging: monitor.isDragging()
        }),
    });

    const [{ highlighted,hovered }, drop] = useDrop({
        accept: 'children',
        hover: (draggedItem) => {
            if (draggedItem.id !== item.id) {
                moveChildren(draggedItem.sectionId, sectionId, draggedItem.id, item.id);
                draggedItem.sectionId = sectionId; // Update sectionId for subsequent drag operations
            }
        },
        collect: (monitor) => ({
            highlighted: monitor.canDrop() && monitor.getItem()?.id === item.id,
            hovered: monitor.isOver() && monitor.getItem()?.id === item.id,
        })
    });

    const dropTargetClass = highlighted
        ? 'drop-target-highlighted'
        : hovered
            ? 'drop-target-hovered'
            : '';

    // Memoize expensive calculations
    const itemIcon = useMemo(() => {
        if (item.type === 'sp-lesson' || item.type.includes('lesson')) {
            return 'media-document';
        } else if (item.type === 'sp-quiz' || item.type.includes('quiz')) {
            return 'format-status';
        }
        return 'admin-page';
    }, [item.type]);

    const itemTypeLabel = useMemo(() => {
        if (item.type === 'sp-lesson' || item.type.includes('lesson')) {
            return __('Lesson', 'skillpulse-lms');
        } else if (item.type === 'sp-quiz' || item.type.includes('quiz')) {
            return __('Quiz', 'skillpulse-lms');
        }
        return __('Item', 'skillpulse-lms');
    }, [item.type]);

    // Optimize timer management with proper cleanup
    useEffect(() => {
        let deleteTimer;
        if (isDeleting) {
            deleteTimer = setTimeout(() => {
                deleteItem(item.id);
            }, 150);
        }
        
        return () => {
            if (deleteTimer) clearTimeout(deleteTimer);
        };
    }, [isDeleting, deleteItem, item.id]);

    const handleDelete = useCallback(() => {
        if (window.confirm(__('Are you sure you want to delete this item?', 'skillpulse-lms'))) {
            setIsDeleting(true);
        }
    }, []);

    const dragStyle = {
        opacity: isDragging ? 0.5 : 1,
        transform: isDragging ? 'rotate(5deg)' : 'none',
    };

    if (highlighted) {
                    dragStyle.border = '2px dashed var(--splms-primary)';
        dragStyle.backgroundColor = 'var(--splms-primary-alpha-5, rgba(126, 117, 255, 0.05))';
    }

    if (hovered) {
        dragStyle.backgroundColor = 'var(--splms-primary-alpha-3, rgba(126, 117, 255, 0.03))';
                    dragStyle.borderColor = 'var(--splms-primary-light, #a5a0ff)';
    }

    return (
        <div
            className={`${item.type} item content-over ${dropTargetClass} ${className} ${isDragging ? 'dragging' : ''} ${isDeleting ? 'deleting' : ''}`}
            key={item.id}
            ref={(node) => drag(drop(node))}
            onMouseEnter={() => setIsHovering(true)}
            onMouseLeave={() => setIsHovering(false)}
            style={dragStyle}
            data-id={`${item.type}-${item.id}`}
        >
            <div className={`item-header ${item.type}-header`}>
                <Tooltip text={__('Drag to reorder', 'skillpulse-lms')}>
                    <SplmsIcon name="draggable" size={16} className="drag-handle" />
                </Tooltip>
                
                <div className="item-type-indicator">
                    {/* <Icon icon={itemIcon} size={18} /> */}
                    <span className="item-type-label">{itemTypeLabel}</span>
                </div>

                <>
                    {isEditing ? (
                        <TextControl
                            className={`item-name-input ${item.type}-name-input`}
                            value={editingName}
                            onChange={(value) => updateEditingItem(item.id, value)}
                            onBlur={() => blurEditingItem()}
                            autoFocus
                            placeholder={__('Enter title...', 'skillpulse-lms')}
                        />
                    ) : (
                        <Tooltip text={__('Click to edit title', 'skillpulse-lms')}>
                            <span 
                                className={`item-name ${item.type}-name`} 
                                onClick={() => startEditingItem(item.id, item.title)}
                            >
                                {item.title}
                            </span>
                        </Tooltip>
                    )}
                </>
            </div>
            
            {isHovering && !isEditing && (
                <div className="actions">
                    <div className="action-buttons">
                        <Tooltip text={__('Preview', 'skillpulse-lms')}>
                        <a
                                className="action-btn preview-btn"
                            href={getPostUrl(item.permalink)}
                                title={__('Preview Item', 'skillpulse-lms')}
                                target="_blank"
                                rel="noopener noreferrer"
                        >
                                <Icon icon="visibility" size={16} />
                            </a>
                        </Tooltip>
                        
                        <Tooltip text={__('Edit', 'skillpulse-lms')}>
                        <a
                                className="action-btn edit-btn"
                            href={getPostEditUrl(item.id)}
                                title={__('Edit Item', 'skillpulse-lms')}
                        >
                                <Icon icon="edit" size={16} />
                            </a>
                        </Tooltip>
                        
                        <Tooltip text={__('Delete', 'skillpulse-lms')}>
                            <button 
                                className="action-btn delete-btn"
                                onClick={handleDelete}
                                type="button"
                            >
                                <Icon icon="trash" size={16} />
                            </button>
                        </Tooltip>
                    </div>
            </div>
            )}
        </div>
    );
};

const ItemComponent = React.memo(Item);

export default compose([
    withSelect((select) => {
        const { getEditingItem } = select('splms/course-curriculum');
        return {
            editingItem: getEditingItem(),
        };
    }),
    withDispatch((dispatch) => {
        const {
            startEditingItem,
            updateEditingItem,
            blurEditingItem,
            deleteItem,
            moveChildren,
        } = dispatch('splms/course-curriculum');
        return {
            startEditingItem,
            updateEditingItem,
            blurEditingItem,
            deleteItem,
            moveChildren,
        };
    }),
])(ItemComponent);
