/**
 * Activity Timeline Component
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Spinner } from '@wordpress/components';
import { SplmsIcon } from "../../../../../components/SplmsIcon";
import { formatDate } from '../../../../../utility/helper';
import { apiFetchSafe } from '../../../../../utility/apiFetchSafe';

class ActivityTimeline extends Component {
    constructor(props) {
        super(props);
        this.state = {
            activities: [],
            isLoading: false,
            error: null
        };
    }

    componentDidMount() {
        if (this.props.enrollment) {
            this.loadActivity();
        }
    }

    componentDidUpdate(prevProps) {
        if (prevProps.enrollment?.id !== this.props.enrollment?.id) {
            this.loadActivity();
        }
    }

    async loadActivity() {
        const { enrollment } = this.props;
        if (!enrollment || !enrollment.id) return;

        this.setState({ isLoading: true, error: null });

        try {
            // Use optimized endpoint to get activity timeline.
            const response = await apiFetchSafe({
                path: `/splms/v1/enrollments/${enrollment.id}/activity`,
                method: 'GET'
            });

            let activities = [];
            if (response && response.success && Array.isArray(response.activities)) {
                activities = response.activities;
            } else if (Array.isArray(response)) {
                activities = response;
            }

            this.setState({ activities, isLoading: false });
        } catch (error) {
            console.error('Error loading activity timeline:', error);
            this.setState({ 
                error: error.message || __('Failed to load activity timeline', 'skillpulse-lms'),
                isLoading: false 
            });
        }
    }

    getActivityIcon(type) {
        const iconMap = {
            enrollment: 'admin-users',
            lesson_completed: 'book',
            quiz_attempt: 'quiz',
            course_completed: 'yes-alt',
            certificate_earned: 'awards'
        };
        return iconMap[type] || 'info';
    }

    getActivityColor(type) {
        const colorMap = {
            enrollment: '#2271b1',
            lesson_completed: '#28a745',
            quiz_attempt: '#ffc107',
            course_completed: '#28a745',
            certificate_earned: '#856404'
        };
        return colorMap[type] || '#666';
    }

    render() {
        const { enrollment } = this.props;
        const { activities, isLoading, error } = this.state;

        if (!enrollment) return null;

        return (
            <Card>
                <CardHeader>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                        <SplmsIcon mode="wp" icon="clock" size={20} />
                        <h3 style={{ margin: 0 }}>{__('Activity Timeline', 'skillpulse-lms')}</h3>
                    </div>
                </CardHeader>
                <CardBody>
                    {isLoading ? (
                        <div style={{ textAlign: 'center', padding: '20px' }}>
                            <Spinner />
                        </div>
                    ) : error ? (
                        <p style={{ color: '#d63638', textAlign: 'center', padding: '20px' }}>
                            {error}
                        </p>
                    ) : activities.length === 0 ? (
                        <p style={{ color: '#666', textAlign: 'center', padding: '20px' }}>
                            {__('No activity recorded yet.', 'skillpulse-lms')}
                        </p>
                    ) : (
                        <div style={{ position: 'relative', paddingLeft: '30px' }}>
                            {/* Timeline line */}
                            <div style={{
                                position: 'absolute',
                                left: '15px',
                                top: '0',
                                bottom: '0',
                                width: '2px',
                                backgroundColor: '#ddd'
                            }} />

                            {activities.map((activity, index) => {
                                const color = this.getActivityColor(activity.type);
                                return (
                                    <div 
                                        key={index}
                                        style={{
                                            position: 'relative',
                                            marginBottom: '24px',
                                            paddingLeft: '20px'
                                        }}
                                    >
                                        {/* Timeline dot */}
                                        <div style={{
                                            position: 'absolute',
                                            left: '-23px',
                                            top: '4px',
                                            width: '16px',
                                            height: '16px',
                                            borderRadius: '50%',
                                            backgroundColor: color,
                                            border: '3px solid #fff',
                                            boxShadow: '0 0 0 2px ' + color
                                        }} />

                                        {/* Activity content */}
                                        <div>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '4px' }}>
                                                <SplmsIcon mode="wp" icon={this.getActivityIcon(activity.type)} size={16} />
                                                <strong style={{ color: color }}>
                                                    {activity.title}
                                                </strong>
                                            </div>
                                            <p style={{ margin: '4px 0', color: '#666', fontSize: '0.9em' }}>
                                                {activity.description}
                                            </p>
                                            {activity.score !== undefined && activity.maxScore !== undefined && (
                                                <p style={{ margin: '4px 0', color: '#666', fontSize: '0.85em' }}>
                                                    {__('Score:', 'skillpulse-lms')} {activity.score.toFixed(2)} / {activity.maxScore.toFixed(2)}
                                                </p>
                                            )}
                                            <p style={{ margin: '4px 0', color: '#999', fontSize: '0.85em' }}>
                                                {formatDate(activity.date)}
                                            </p>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </CardBody>
            </Card>
        );
    }
}

export default ActivityTimeline;

