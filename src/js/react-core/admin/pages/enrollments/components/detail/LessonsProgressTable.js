/**
 * Lessons Progress Table Component
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Spinner, Button } from '@wordpress/components';
import { SplmsIcon } from "../../../../../components/SplmsIcon";
import { formatDate } from '../../../../../utility/helper';
import { apiFetchSafe } from '../../../../../utility/apiFetchSafe';

class LessonsProgressTable extends Component {
    constructor(props) {
        super(props);
        this.state = {
            lessons: [],
            isLoading: false,
            error: null
        };
    }

    componentDidMount() {
        if (this.props.enrollment) {
            this.loadLessons();
        }
    }

    componentDidUpdate(prevProps) {
        if (prevProps.enrollment?.course_id !== this.props.enrollment?.course_id ||
            prevProps.enrollment?.user_id !== this.props.enrollment?.user_id) {
            this.loadLessons();
        }
    }

    async loadLessons() {
        const { enrollment } = this.props;
        if (!enrollment || !enrollment.id) return;

        this.setState({ isLoading: true, error: null });

        try {
            // Use optimized endpoint to get all lessons with progress in one call.
            const response = await apiFetchSafe({
                path: `/splms/v1/enrollments/${enrollment.id}/lessons`,
                method: 'GET'
            });

            let lessons = [];
            if (response && response.success && Array.isArray(response.lessons)) {
                lessons = response.lessons;
            } else if (Array.isArray(response)) {
                lessons = response;
            }

            this.setState({ lessons, isLoading: false });
            
            // Notify parent component
            if (this.props.onLessonsLoaded) {
                this.props.onLessonsLoaded(lessons);
            }
        } catch (error) {
            console.error('Error loading lessons:', error);
            this.setState({ 
                error: error.message || __('Failed to load lessons', 'skillpulse-lms'),
                isLoading: false 
            });
        }
    }

    formatTime(seconds) {
        if (!seconds || seconds === 0) return '-';
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        if (hours > 0) {
            return `${hours}h ${minutes}m`;
        }
        return `${minutes}m`;
    }

    getStatusBadge(lesson) {
        if (lesson.completed) {
            return (
                <span style={{ 
                    padding: '4px 8px', 
                    backgroundColor: '#d4edda', 
                    color: '#155724', 
                    borderRadius: '4px',
                    fontSize: '0.875em',
                    fontWeight: '500'
                }}>
                    {__('Completed', 'skillpulse-lms')}
                </span>
            );
        } else if (lesson.progress > 0) {
            return (
                <span style={{ 
                    padding: '4px 8px', 
                    backgroundColor: '#fff3cd', 
                    color: '#856404', 
                    borderRadius: '4px',
                    fontSize: '0.875em',
                    fontWeight: '500'
                }}>
                    {__('In Progress', 'skillpulse-lms')}
                </span>
            );
        }
        return (
            <span style={{ 
                padding: '4px 8px', 
                backgroundColor: '#f8d7da', 
                color: '#721c24', 
                borderRadius: '4px',
                fontSize: '0.875em',
                fontWeight: '500'
            }}>
                {__('Not Started', 'skillpulse-lms')}
            </span>
        );
    }

    render() {
        const { enrollment } = this.props;
        const { lessons, isLoading, error } = this.state;

        if (!enrollment) return null;

        const lessonEditUrl = (lessonId) => 
            getPostTypeEditUrl('sp-lesson', lessonId);

        return (
            <Card>
                <CardHeader>
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                            <SplmsIcon mode="wp" icon="book" size={20} />
                            <h3 style={{ margin: 0 }}>{__('Lessons Progress', 'skillpulse-lms')}</h3>
                        </div>
                        <span style={{ color: '#666', fontSize: '0.875em' }}>
                            {lessons.length} {__('lessons', 'skillpulse-lms')}
                        </span>
                    </div>
                </CardHeader>
                <CardBody>
                    {isLoading ? (
                        <div style={{ textAlign: 'center', padding: '40px' }}>
                            <Spinner />
                            <p>{__('Loading lessons...', 'skillpulse-lms')}</p>
                        </div>
                    ) : error ? (
                        <div style={{ padding: '20px', backgroundColor: '#f8d7da', color: '#721c24', borderRadius: '4px' }}>
                            {error}
                        </div>
                    ) : lessons.length === 0 ? (
                        <p style={{ color: '#666', textAlign: 'center', padding: '20px' }}>
                            {__('No lessons found for this course.', 'skillpulse-lms')}
                        </p>
                    ) : (
                        <div style={{ overflowX: 'auto' }}>
                            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                                <thead>
                                    <tr style={{ borderBottom: '2px solid #ddd' }}>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Lesson', 'skillpulse-lms')}
                                        </th>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Status', 'skillpulse-lms')}
                                        </th>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Progress', 'skillpulse-lms')}
                                        </th>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Completed', 'skillpulse-lms')}
                                        </th>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Time Spent', 'skillpulse-lms')}
                                        </th>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Actions', 'skillpulse-lms')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {lessons.map((lesson, index) => (
                                        <tr 
                                            key={lesson.id || index}
                                            style={{ 
                                                borderBottom: '1px solid #eee',
                                                ':hover': { backgroundColor: '#f5f5f5' }
                                            }}
                                        >
                                            <td style={{ padding: '12px' }}>
                                                <strong>
                                                    {lesson.title?.rendered || lesson.title || `Lesson #${lesson.id}`}
                                                </strong>
                                            </td>
                                            <td style={{ padding: '12px' }}>
                                                {this.getStatusBadge(lesson)}
                                            </td>
                                            <td style={{ padding: '12px' }}>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                                    <div style={{ 
                                                        flex: 1, 
                                                        height: '8px', 
                                                        backgroundColor: '#e0e0e0', 
                                                        borderRadius: '4px',
                                                        overflow: 'hidden'
                                                    }}>
                                                        <div style={{
                                                            width: `${lesson.progress || 0}%`,
                                                            height: '100%',
                                                            backgroundColor: lesson.completed ? '#28a745' : '#ffc107',
                                                            transition: 'width 0.3s ease'
                                                        }} />
                                                    </div>
                                                    <span style={{ fontSize: '0.875em', color: '#666', minWidth: '45px' }}>
                                                        {Math.round(lesson.progress || 0)}%
                                                    </span>
                                                </div>
                                            </td>
                                            <td style={{ padding: '12px', color: '#666' }}>
                                                {lesson.completed_at ? formatDate(lesson.completed_at) : '-'}
                                            </td>
                                            <td style={{ padding: '12px', color: '#666' }}>
                                                {this.formatTime(lesson.time_spent)}
                                            </td>
                                            <td style={{ padding: '12px' }}>
                                                <Button
                                                    isSmall
                                                    isLink
                                                    href={lessonEditUrl(lesson.id)}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    {__('View', 'skillpulse-lms')}
                                                </Button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </CardBody>
            </Card>
        );
    }
}

export default LessonsProgressTable;

