/**
 * FeedbackEditor component for instructor feedback
 */

import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Button, TextareaControl, Spinner } from '@wordpress/components';
import { updateQuizAttemptFeedback } from '../api';

const FeedbackEditor = ({ attemptId, initialFeedback = '', onUpdate }) => {
    const [feedback, setFeedback] = useState(initialFeedback);
    const [isSaving, setIsSaving] = useState(false);

    useEffect(() => {
        setFeedback(initialFeedback);
    }, [initialFeedback]);

    const safeTranslate = (value) => {
        if (typeof value === 'string') return value;
        if (value && typeof value === 'object' && value.rendered) return value.rendered;
        return String(value || '');
    };

    const handleSave = async () => {
        setIsSaving(true);

        try {
            const response = await updateQuizAttemptFeedback(attemptId, feedback);
            
            if (response && response.success) {
                if (window.skillpulseToast) {
                    window.skillpulseToast.success(safeTranslate(__('Feedback saved successfully', 'skillpulse-lms')));
                }
                if (onUpdate) {
                    onUpdate(feedback);
                }
            } else {
                const errorMessage = response?.message || safeTranslate(__('Failed to save feedback', 'skillpulse-lms'));
                if (window.skillpulseToast) {
                    window.skillpulseToast.error(errorMessage);
                }
            }
        } catch (err) {
            console.error('Error saving feedback:', err);
            const errorMessage = err.message || safeTranslate(__('Failed to save feedback', 'skillpulse-lms'));
            if (window.skillpulseToast) {
                window.skillpulseToast.error(errorMessage);
            }
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <Card>
            <CardHeader>
                <h2>{safeTranslate(__('Instructor Feedback', 'skillpulse-lms'))}</h2>
            </CardHeader>
            <CardBody>

                <TextareaControl
                    label={safeTranslate(__('Feedback', 'skillpulse-lms'))}
                    value={feedback}
                    onChange={(value) => setFeedback(value)}
                    rows={8}
                    placeholder={safeTranslate(__('Enter instructor feedback for this quiz attempt...', 'skillpulse-lms'))}
                />

                <div style={{ marginTop: '15px' }}>
                    <Button
                        isPrimary
                        onClick={handleSave}
                        disabled={isSaving}
                    >
                        {isSaving ? (
                            <>
                                <Spinner />
                                {safeTranslate(__('Saving...', 'skillpulse-lms'))}
                            </>
                        ) : (
                            safeTranslate(__('Save Feedback', 'skillpulse-lms'))
                        )}
                    </Button>
                </div>
            </CardBody>
        </Card>
    );
};

export default FeedbackEditor;

