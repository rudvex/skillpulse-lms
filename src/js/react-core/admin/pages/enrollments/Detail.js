/**
 * Detail component for displaying a single enrollment with comprehensive information
 *
 * REFACTORED VERSION - Uses reusable SPLMS_DetailView component
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { SPLMS_DetailView } from '../../../components/admin/DetailView';
import { SplmsIcon } from "../../../components/SplmsIcon";
import { StatusBadge, MethodBadge } from './components/EnrollmentBadges';

// Import detail card components
import StudentInfoCard from './components/detail/StudentInfoCard';
import CourseInfoCard from './components/detail/CourseInfoCard';
import EnrollmentDetailsCard from './components/detail/EnrollmentDetailsCard';
import ProgressOverviewCard from './components/detail/ProgressOverviewCard';
import LessonsProgressTable from './components/detail/LessonsProgressTable';
import QuizAttemptsTable from './components/detail/QuizAttemptsTable';
import ActivityTimeline from './components/detail/ActivityTimeline';

class Detail extends Component {
    constructor(props) {
        super(props);
        this.state = {
            lessons: [],
            attempts: []
        };
    }

    getSections() {
        const { enrollment, onGenerateCertificate } = this.props;

        return [
            {
                id: 'overview',
                label: __('Overview', 'skillpulse-lms'),
                default: true,
                render: () => (
                    <div>
                        {/* Student and Course Info Cards - Side by Side */}
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '20px' }}>
                            <StudentInfoCard enrollment={enrollment} />
                            <CourseInfoCard enrollment={enrollment} />
                        </div>

                        {/* Progress Overview */}
                        <ProgressOverviewCard enrollment={enrollment} onGenerateCertificate={onGenerateCertificate} />
                    </div>
                )
            },
            {
                id: 'progress',
                label: __('Progress Details', 'skillpulse-lms'),
                render: () => (
                    <div>
                        {/* Lessons Progress Table */}
                        <LessonsProgressTable
                            enrollment={enrollment}
                            onLessonsLoaded={(lessons) => this.setState({ lessons })}
                        />

                        {/* Quiz Attempts Table */}
                        <QuizAttemptsTable
                            enrollment={enrollment}
                            onAttemptsLoaded={(attempts) => this.setState({ attempts })}
                        />
                    </div>
                )
            },
            {
                id: 'activity',
                label: __('Activity Timeline', 'skillpulse-lms'),
                render: () => (
                    <ActivityTimeline
                        enrollment={enrollment}
                        lessons={this.state.lessons}
                        attempts={this.state.attempts}
                    />
                )
            },
            {
                id: 'details',
                label: __('Enrollment Details', 'skillpulse-lms'),
                render: () => (
                    <EnrollmentDetailsCard enrollment={enrollment} />
                )
            }
        ];
    }

    getSidebar() {
        const { enrollment, onEdit, onDelete, onGenerateCertificate } = this.props;

        const sidebar = [];

        // Quick Actions
        const actions = [];
        if (onEdit) {
            actions.push({
                icon: 'edit',
                label: __('Edit Enrollment', 'skillpulse-lms'),
                onClick: () => onEdit(enrollment)
            });
        }
        if (onGenerateCertificate && enrollment.progress >= 100 && !enrollment.certificate_earned) {
            actions.push({
                icon: 'awards',
                label: __('Generate Certificate', 'skillpulse-lms'),
                onClick: () => onGenerateCertificate(enrollment)
            });
        }
        if (onDelete) {
            actions.push({
                icon: 'trash',
                label: __('Delete Enrollment', 'skillpulse-lms'),
                onClick: () => onDelete(enrollment),
                className: 'delete'
            });
        }

        if (actions.length > 0) {
            sidebar.push({
                title: __('Quick Actions', 'skillpulse-lms'),
                render: () => (
                    <div className="splms-quick-actions">
                        <ul className="quick-actions-list">
                            {actions.map((action, index) => (
                                <li key={index} className="quick-action-item">
                                    <button
                                        type="button"
                                        onClick={action.onClick}
                                        className={`quick-action-button ${action.className || ''}`}
                                    >
                                        <span className="quick-action-icon">
                                            <SplmsIcon mode="wp" icon={action.icon} size={18} />
                                        </span>
                                        <span className="quick-action-label">{action.label}</span>
                                    </button>
                                </li>
                            ))}
                        </ul>
                    </div>
                )
            });
        }

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
                    title={`${__('Enrollment', 'skillpulse-lms')} #${enrollment.id}`}
                    onBack={onBack}
                    backLabel={__('Back to Enrollments', 'skillpulse-lms')}
                    sections={this.getSections()}
                    sidebar={this.getSidebar()}
                    sectionMode="tabs"
                />
            </div>
        );
    }
}

export default Detail;

