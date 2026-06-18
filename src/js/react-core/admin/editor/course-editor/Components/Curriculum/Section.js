import { useDrag, useDrop } from 'react-dnd';

import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { Button, TextControl, Tooltip, Icon } from '@wordpress/components';
import { useState, useMemo, useCallback, useEffect } from '@wordpress/element';
import React from 'react';
import { withSelect, withDispatch  } from '@wordpress/data';

import Item from './Item';
import { getPostEditUrl } from '../../../../../utility/url';

const Section = (props) => {

    const {
        section,
        sectionChildren,
        startEditingItem,
        updateEditingItem,
        blurEditingItem,
        addLesson,
        addQuiz,
        moveSection,
    } = props;

    const { id:editingId, title:editingName } = props.editingItem;
    const [isHovering, setIsHovering] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);
    const [isAddingLesson, setIsAddingLesson] = useState(false);
    const [isAddingQuiz, setIsAddingQuiz] = useState(false);

    const [{ highlighted,hovered }, drop] = useDrop({
        accept: ['section', 'children'],
        drop: (item) => {
            if (item.type === 'section') {
                moveSection(item.id, section.id);
            }
        },
        collect: (monitor) => ({
            highlighted: monitor.canDrop() && monitor.getItem()?.id !== section.id && monitor.getItem()?.type === 'section',
            hovered: monitor.isOver() && monitor.getItem()?.id !== section.id && monitor.getItem()?.type === 'section',
        })

    });

    const [{ isDragging }, drag] = useDrag({
        type: 'section',
        item: { id: section.id, type: 'section' },
        collect: (monitor) => ({
            isDragging: monitor.isDragging(),
        }),
    });

    const dropTargetClass = highlighted
        ? 'section-drop-target-highlighted'
        : hovered
            ? 'section-drop-target-hovered'
            : '';

    const setStyle = {
        opacity: isDragging ? 0.5 : 1,
    }

    if ( highlighted ) {
        setStyle.border = '2px dashed var(--splms-primary)';
        setStyle.backgroundColor = 'var(--splms-primary-alpha-5, rgba(126, 117, 255, 0.05))';
    }

    if ( hovered ) {
        setStyle.backgroundColor = 'var(--splms-primary-alpha-3, rgba(126, 117, 255, 0.03))';
        setStyle.borderColor = 'var(--splms-primary-light, #a5a0ff)';
    }



    // Optimize timer management with proper cleanup and validation
    useEffect(() => {
        let lessonTimer, quizTimer, deleteTimer;

        if (isAddingLesson && section?.id) {
            lessonTimer = setTimeout(() => {
                setIsAddingLesson(false);
            }, 300);
        }

        if (isAddingQuiz && section?.id) {
            quizTimer = setTimeout(() => {
                setIsAddingQuiz(false);
            }, 300);
        }

        if (isDeleting && section?.id && props?.deleteSection) {
            deleteTimer = setTimeout(() => {
                try {
                    props.deleteSection(section.id);
                } catch (error) {
                    console.error('Error deleting section:', error);
                    setIsDeleting(false);
                }
            }, 150);
        }

        // Cleanup function with explicit timer validation
        return () => {
            if (lessonTimer) {
                clearTimeout(lessonTimer);
                lessonTimer = null;
            }
            if (quizTimer) {
                clearTimeout(quizTimer);
                quizTimer = null;
            }
            if (deleteTimer) {
                clearTimeout(deleteTimer);
                deleteTimer = null;
            }
        };
    }, [isAddingLesson, isAddingQuiz, isDeleting, section?.id, props?.deleteSection]);

    const handleAddLesson = useCallback(() => {
        if (!section?.id || !addLesson || isAddingLesson) {
            return;
        }
        setIsAddingLesson(true);
        try {
            addLesson(section.id);
        } catch (error) {
            console.error('Error adding lesson:', error);
            setIsAddingLesson(false);
        }
    }, [addLesson, section?.id, isAddingLesson]);

    const handleAddQuiz = useCallback(() => {
        if (!section?.id || !addQuiz || isAddingQuiz) {
            return;
        }
        setIsAddingQuiz(true);
        try {
            addQuiz(section.id);
        } catch (error) {
            console.error('Error adding quiz:', error);
            setIsAddingQuiz(false);
        }
    }, [addQuiz, section?.id, isAddingQuiz]);

    // Optimize item counting with early returns and stable references
    const itemCounts = useMemo(() => {
        if (!Array.isArray(sectionChildren) || sectionChildren.length === 0) {
            return { lessonCount: 0, quizCount: 0 };
        }

        let lessonCount = 0;
        let quizCount = 0;

        // Use for loop for better performance than reduce
        for (let i = 0; i < sectionChildren.length; i++) {
            const item = sectionChildren[i];
            if (item?.type === 'sp-lesson') {
                lessonCount++;
            } else if (item?.type === 'sp-quiz') {
                quizCount++;
            }
        }

        return { lessonCount, quizCount };
    }, [sectionChildren]);


    // Memoize section items count display
    const sectionItemsCount = useMemo(() => {
        const { lessonCount, quizCount } = itemCounts;

        return (
            <>
                {lessonCount > 0 && (
                    <span className="badge lessons-badge">
                        <span className="count">{lessonCount}</span> lesson{lessonCount !== 1 ? 's' : ''}
                    </span>
                )}

                {quizCount > 0 && (
                    <span className="badge quizzes-badge">
                        <span className="count">{quizCount}</span> quiz{quizCount !== 1 ? 'zes' : ''}
                    </span>
                )}

                {lessonCount === 0 && quizCount === 0 && (
                    <span className="empty-text">Empty section</span>
                )}
            </>
        );
    }, [itemCounts]);

    const handleDelete = useCallback(() => {
        if (window.confirm(__('Are you sure you want to delete this item?', 'skillpulse-lms'))) {
            setIsDeleting(true);
        }
    }, []);

    // Safety check - don't render if section is invalid
    if (!section || !section.id) {
        return null;
    }

    return (
        <div
            className={`section section-over ${dropTargetClass} ${isDragging ? 'dragging' : ''} ${isDeleting ? 'deleting' : ''}`}
            ref={drop}
            style={setStyle}
        >
            <div className="section-header"
                onMouseEnter={() => setIsHovering(true)}
                onMouseLeave={() => setIsHovering(false)}
                 ref={drag}>

                {section.id === editingId ? (
                    <TextControl
                        className={'section-title-input'}
                        value={editingName}
                        onChange={(value) => updateEditingItem(section.id, value)}
                        onBlur={() => blurEditingItem()}
                        autoFocus
                    />
                ) : (
                    <div className="section-title-container" data-id={section.id}>
                        <Tooltip text={__('Click to edit section title', 'skillpulse-lms')}>
                            <span className={'section-title'} onClick={() => startEditingItem(section.id, section.title)}>
                                {`${section.title}`}
                            </span>
                        </Tooltip>
                        <div className="section-meta">
                            {sectionItemsCount}
                        </div>
                    </div>
                )}

                {isHovering && section?.id && section.id !== editingId && !isDeleting && (
                    <div className="actions">
                        <Tooltip text={__('Edit section', 'skillpulse-lms')}>
                            <Button
                                icon="edit"
                                onClick={() => window.open(getPostEditUrl(section.id), '_blank')}
                                className="edit-button"
                            />
                        </Tooltip>
                        <Tooltip text={__('Delete section', 'skillpulse-lms')}>
                            <Button
                                icon="trash"
                                onClick={handleDelete}
                                className="delete-button"
                                isDestructive
                                disabled={isDeleting}
                            />
                        </Tooltip>
                    </div>
                )}
            </div>

            <div className="section-content">
                {sectionChildren.length === 0 && (
                    <div className="empty-section-message">
                        <Icon icon="book" size={24} />
                        <p>{__('This section is empty. Add lessons and quizzes using the buttons below.', 'skillpulse-lms')}</p>
                    </div>
                )}
                
                {sectionChildren.map((item, index) => (
                    <Item
                        sectionId={section.id}
                        key={item.id}
                        item={item}
                        isEditing={item.id === editingId}
                        index={index}
                        className={item.id === editingId ? 'editing' : ''}
                    />
                ))}
            </div>
            
            <div className="section-actions">
                <Button
                    isSecondary
                    onClick={handleAddLesson}
                    disabled={isAddingLesson}
                    className={isAddingLesson ? 'loading' : ''}
                >

                    {isAddingLesson ? __('Adding...', 'skillpulse-lms') : __('Add Lesson', 'skillpulse-lms')}
                </Button>

                <Button
                    isSecondary
                    onClick={handleAddQuiz}
                    disabled={isAddingQuiz}
                    className={isAddingQuiz ? 'loading' : ''}
                >

                    {isAddingQuiz ? __('Adding...', 'skillpulse-lms') : __('Add Quiz', 'skillpulse-lms')}
                </Button>
            </div>

        </div>
    );
};

const SectionComponent = React.memo(Section);

export default compose([
    withSelect((select,props) => {
        const { getEditingItem, getSectionChildren } = select('splms/course-curriculum');
        return {
            editingItem: getEditingItem(),
            sectionChildren: getSectionChildren(props.section.id),
        };
    }),
    withDispatch((dispatch) => {
        const {
            startEditingItem,
            updateEditingItem,
            blurEditingItem,
            deleteSection,
            addLesson,
            addQuiz,
            moveSection,
        } = dispatch('splms/course-curriculum');

        return {
            startEditingItem,
            updateEditingItem,
            blurEditingItem,
            deleteSection,
            addLesson,
            addQuiz,
            moveSection,
        };
    }),
])(SectionComponent);
