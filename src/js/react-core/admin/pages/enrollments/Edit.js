/**
 * Edit component for editing enrollment details
 *
 * REFACTORED VERSION - Uses reusable SPLMS_DetailView component
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import {
    Button,
    SelectControl,
    TextControl,
    TextareaControl,
    Card,
    CardBody,
    CardHeader
} from '@wordpress/components';
import { SPLMS_DetailView } from '../../../components/admin/DetailView';
import { SplmsIcon } from "../../../components/SplmsIcon";
import { StatusBadge, MethodBadge } from './components/EnrollmentBadges';

// Import info cards (read-only sections)
import StudentInfoCard from './components/detail/StudentInfoCard';
import CourseInfoCard from './components/detail/CourseInfoCard';
import ProgressOverviewCard from './components/detail/ProgressOverviewCard';

class Edit extends Component {
    constructor(props) {
        super(props);
        const { enrollment } = props;

        this.state = {
            editForm: {
                status: enrollment?.status || '',
                completed_at: enrollment?.completed_at ? enrollment.completed_at.split('T')[0] : '',
                notes: enrollment?.notes || ''
            },
            isSaving: false,
            formErrors: {}
        };
    }

    componentDidUpdate(prevProps) {
        // Update form when enrollment changes
        if (prevProps.enrollment?.id !== this.props.enrollment?.id) {
            const { enrollment } = this.props;
            this.setState({
                editForm: {
                    status: enrollment?.status || '',
                    completed_at: enrollment?.completed_at ? enrollment.completed_at.split('T')[0] : '',
                    notes: enrollment?.notes || ''
                },
                formErrors: {}
            });
        }
    }

    handleFormChange = (field, value) => {
        this.setState(prevState => ({
            editForm: {
                ...prevState.editForm,
                [field]: value
            },
            formErrors: {
                ...prevState.formErrors,
                [field]: '' // Clear error when user changes field
            }
        }));
    }

    validateForm = () => {
        const { editForm } = this.state;
        const errors = {};

        if (!editForm.status) {
            errors.status = __('Status is required', 'skillpulse-lms');
        }

        if (editForm.status === 'completed' && !editForm.completed_at) {
            errors.completed_at = __('Completion date is required when status is completed', 'skillpulse-lms');
        }

        this.setState({ formErrors: errors });
        return Object.keys(errors).length === 0;
    }

    handleSave = async () => {
        if (!this.validateForm()) {
            return;
        }

        const { enrollment, onSave } = this.props;
        const { editForm } = this.state;

        if (!enrollment || !onSave) {
            return;
        }

        this.setState({ isSaving: true });

        try {
            // Convert form data to API format
            const updateData = {
                status: editForm.status,
                completed_at: editForm.completed_at || null,
                notes: editForm.notes || ''
            };

            // If status is completed, ensure completed_at is set
            if (editForm.status === 'completed' && !editForm.completed_at) {
                updateData.completed_at = new Date().toISOString().split('T')[0];
            }

            // If status is cancelled or not completed, clear completed_at
            if (editForm.status === 'cancelled' || editForm.status !== 'completed') {
                updateData.completed_at = null;
            }

            await onSave(enrollment.id, updateData);

        } catch (error) {
            console.error('Error updating enrollment:', error);
            this.setState({
                formErrors: {
                    ...this.state.formErrors,
                    _general: error.message || __('Failed to update enrollment', 'skillpulse-lms')
                }
            });
        } finally {
            this.setState({ isSaving: false });
        }
    }

    handleBack = () => {
        if (this.props.onBack) {
            this.props.onBack();
        }
    }

    getSections() {
        const { enrollment } = this.props;
        const { editForm, isSaving, formErrors } = this.state;

        return [
            {
                id: 'edit',
                label: __('Edit Enrollment', 'skillpulse-lms'),
                default: true,
                render: () => (
                    <div>
                        {/* General Error Message */}
                        {formErrors._general && (
                            <Card style={{ marginBottom: '20px', borderColor: '#d63638' }}>
                                <CardBody>
                                    <div className="splms-notice-error" style={{
                                        padding: '12px',
                                        background: '#f8d7da',
                                        border: '1px solid #d63638',
                                        borderRadius: '4px',
                                        color: '#842029'
                                    }}>
                                        <p style={{ margin: 0 }}>{formErrors._general}</p>
                                    </div>
                                </CardBody>
                            </Card>
                        )}

                        {/* Student and Course Info Cards - Side by Side (Read-only) */}
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '20px' }}>
                            <StudentInfoCard enrollment={enrollment} />
                            <CourseInfoCard enrollment={enrollment} />
                        </div>

                        {/* Progress Overview (Read-only) */}
                        <ProgressOverviewCard enrollment={enrollment} />

                        {/* Editable Administrative Fields */}
                        <Card style={{ marginTop: '20px' }}>
                            <CardHeader>
                                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                    <SplmsIcon mode="wp" icon="admin-settings" size={20} />
                                    <h3 style={{ margin: 0 }}>{__('Administrative Settings', 'skillpulse-lms')}</h3>
                                </div>
                            </CardHeader>
                            <CardBody>
                                {/* Status Field */}
                                <div className="form-field" style={{ marginBottom: '20px' }}>
                                    <SelectControl
                                        label={__('Enrollment Status', 'skillpulse-lms')}
                                        value={editForm.status}
                                        onChange={(value) => this.handleFormChange('status', value)}
                                        options={[
                                            { label: __('Select Status', 'skillpulse-lms'), value: '' },
                                            { label: __('Active', 'skillpulse-lms'), value: 'active' },
                                            { label: __('Cancelled', 'skillpulse-lms'), value: 'cancelled' }
                                        ]}
                                        help={formErrors.status ? formErrors.status : __('Set enrollment as Active (has access) or Cancelled (no access)', 'skillpulse-lms')}
                                    />
                                    {formErrors.status && (
                                        <div className="form-error" style={{ color: '#d63638', marginTop: '5px' }}>{formErrors.status}</div>
                                    )}

                                    {/* Warning message when cancelling */}
                                    {editForm.status === 'cancelled' && (
                                        <div className="splms-notice-warning" style={{
                                            marginTop: '10px',
                                            padding: '12px',
                                            background: '#fff3cd',
                                            border: '1px solid #ffc107',
                                            borderRadius: '4px',
                                            fontSize: '13px'
                                        }}>
                                            <strong>{__('Warning:', 'skillpulse-lms')}</strong> {__('Cancelling this enrollment will immediately revoke the student\'s access to the course. They will no longer be able to view course content, lessons, or quizzes. This action can be reversed by setting the status back to Active.', 'skillpulse-lms')}
                                        </div>
                                    )}

                                    {/* Info message when active */}
                                    {editForm.status === 'active' && (
                                        <div className="splms-notice-info" style={{
                                            marginTop: '10px',
                                            padding: '12px',
                                            background: '#e7f5fe',
                                            border: '1px solid #2271b1',
                                            borderRadius: '4px',
                                            fontSize: '13px'
                                        }}>
                                            {__('Active enrollment grants the student full access to the course content, lessons, and quizzes.', 'skillpulse-lms')}
                                        </div>
                                    )}
                                </div>

                                {/* Completion Date Field (only show when status is completed) */}
                                {editForm.status === 'completed' && (
                                    <div className="form-field" style={{ marginBottom: '20px' }}>
                                        <TextControl
                                            label={__('Completion Date', 'skillpulse-lms')}
                                            value={editForm.completed_at}
                                            onChange={(value) => this.handleFormChange('completed_at', value)}
                                            type="date"
                                            help={formErrors.completed_at ? formErrors.completed_at : __('Date when the course was completed', 'skillpulse-lms')}
                                        />
                                        {formErrors.completed_at && (
                                            <div className="form-error" style={{ color: '#d63638', marginTop: '5px' }}>{formErrors.completed_at}</div>
                                        )}
                                    </div>
                                )}

                                {/* Notes Field */}
                                <div className="form-field">
                                    <TextareaControl
                                        label={__('Administrative Notes', 'skillpulse-lms')}
                                        value={editForm.notes}
                                        onChange={(value) => this.handleFormChange('notes', value)}
                                        rows={3}
                                        help={__('Optional notes about this enrollment (visible to administrators only)', 'skillpulse-lms')}
                                    />
                                </div>
                            </CardBody>
                        </Card>
                    </div>
                )
            }
        ];
    }

    getSidebar() {
        const { enrollment } = this.props;
        const { isSaving } = this.state;

        const sidebar = [];

        // Quick Actions
        sidebar.push({
            title: __('Actions', 'skillpulse-lms'),
            render: () => (
                <div className="splms-quick-actions">
                    <ul className="quick-actions-list">
                        <li className="quick-action-item">
                            <button
                                type="button"
                                onClick={this.handleSave}
                                disabled={isSaving}
                                className="quick-action-button"
                                style={{
                                    background: isSaving ? '#f0f0f1' : '#2271b1',
                                    color: '#fff',
                                    borderColor: isSaving ? '#c3c4c7' : '#2271b1'
                                }}
                            >
                                <span className="quick-action-icon">
                                    <SplmsIcon mode="wp" icon={isSaving ? 'update' : 'saved'} size={18} />
                                </span>
                                <span className="quick-action-label">
                                    {isSaving ? __('Saving...', 'skillpulse-lms') : __('Save Changes', 'skillpulse-lms')}
                                </span>
                            </button>
                        </li>
                        <li className="quick-action-item">
                            <button
                                type="button"
                                onClick={this.handleBack}
                                disabled={isSaving}
                                className="quick-action-button"
                            >
                                <span className="quick-action-icon">
                                    <SplmsIcon mode="wp" icon="no-alt" size={18} />
                                </span>
                                <span className="quick-action-label">{__('Cancel', 'skillpulse-lms')}</span>
                            </button>
                        </li>
                    </ul>
                </div>
            )
        });

        // Enrollment Information
        sidebar.push({
            title: __('Enrollment Information', 'skillpulse-lms'),
            render: () => (
                <div className="splms-info-list">
                    <div className="splms-info-item">
                        <span className="splms-info-label">
                            <SplmsIcon mode="wp" icon="calendar" size={16} />
                            {__('Enrolled', 'skillpulse-lms')}
                        </span>
                        <span className="splms-info-value">
                            {enrollment.enrolled_at ? dateI18n('M j, Y', enrollment.enrolled_at) : '—'}
                        </span>
                    </div>
                    {enrollment.completed_at && (
                        <div className="splms-info-item">
                            <span className="splms-info-label">
                                <SplmsIcon mode="wp" icon="yes-alt" size={16} />
                                {__('Completed', 'skillpulse-lms')}
                            </span>
                            <span className="splms-info-value">
                                {dateI18n('M j, Y', enrollment.completed_at)}
                            </span>
                        </div>
                    )}
                    <div className="splms-info-item">
                        <span className="splms-info-label">
                            <SplmsIcon mode="wp" icon="admin-users" size={16} />
                            {__('Method', 'skillpulse-lms')}
                        </span>
                        <span className="splms-info-value">
                            <MethodBadge method={enrollment.enrollment_method} />
                        </span>
                    </div>
                    <div className="splms-info-item">
                        <span className="splms-info-label">
                            <SplmsIcon mode="wp" icon="info" size={16} />
                            {__('Status', 'skillpulse-lms')}
                        </span>
                        <span className="splms-info-value">
                            <StatusBadge status={enrollment.status} />
                        </span>
                    </div>
                    {enrollment.last_activity && (
                        <div className="splms-info-item">
                            <span className="splms-info-label">
                                <SplmsIcon mode="wp" icon="clock" size={16} />
                                {__('Last Activity', 'skillpulse-lms')}
                            </span>
                            <span className="splms-info-value">
                                {dateI18n('M j, Y g:i a', enrollment.last_activity)}
                            </span>
                        </div>
                    )}
                </div>
            )
        });

        return sidebar;
    }

    render() {
        const { enrollment, onBack } = this.props;

        if (!enrollment) {
            return (
                <div className="splms-container">
                    <SPLMS_DetailView isLoading={true} />
                </div>
            );
        }

        return (
            <div className="splms-container" role="main">
                <SPLMS_DetailView
                    title={`${__('Edit Enrollment', 'skillpulse-lms')} #${enrollment.id}`}
                    onBack={onBack}
                    backLabel={__('Back to Details', 'skillpulse-lms')}
                    sections={this.getSections()}
                    sidebar={this.getSidebar()}
                    sectionMode="single"
                />
            </div>
        );
    }
}

export default Edit;
