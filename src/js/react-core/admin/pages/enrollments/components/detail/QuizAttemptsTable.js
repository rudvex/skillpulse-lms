/**
 * Quiz Attempts Table Component
 */

import React, { Component } from 'react';
import { getAdminUrl, getAdminPageUrl } from '../../../../../utility/url';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Spinner, Button } from '@wordpress/components';
import { SplmsIcon } from "../../../../../components/SplmsIcon";
import { formatDate } from '../../../../../utility/helper';
import { apiFetchSafe } from '../../../../../utility/apiFetchSafe';

class QuizAttemptsTable extends Component {
    constructor(props) {
        super(props);
        this.state = {
            attempts: [],
            isLoading: false,
            error: null
        };
    }

    componentDidMount() {
        if (this.props.enrollment) {
            this.loadQuizAttempts();
        }
    }

    componentDidUpdate(prevProps) {
        if (prevProps.enrollment?.course_id !== this.props.enrollment?.course_id ||
            prevProps.enrollment?.user_id !== this.props.enrollment?.user_id) {
            this.loadQuizAttempts();
        }
    }

    async loadQuizAttempts() {
        const { enrollment } = this.props;
        if (!enrollment || !enrollment.course_id || !enrollment.user_id) return;

        this.setState({ isLoading: true, error: null });

        try {
            // Fetch quiz attempts for this user and course
            const response = await apiFetchSafe({
                path: `/splms/v1/quiz-attempts?user_id=${enrollment.user_id}&course_id=${enrollment.course_id}&per_page=100`,
                method: 'GET'
            });

            let attemptsData = [];
            if (response && response.attempts && Array.isArray(response.attempts)) {
                attemptsData = response.attempts;
            } else if (Array.isArray(response)) {
                attemptsData = response;
            }
            
            this.setState({ attempts: attemptsData, isLoading: false });
            
            // Notify parent component
            if (this.props.onAttemptsLoaded) {
                this.props.onAttemptsLoaded(attemptsData);
            }
        } catch (error) {
            console.error('Error loading quiz attempts:', error);
            this.setState({ 
                error: error.message || __('Failed to load quiz attempts', 'skillpulse-lms'),
                isLoading: false 
            });
        }
    }

    formatScore(score, maxScore) {
        if (!maxScore || maxScore === 0) return '-';
        const percentage = ((score / maxScore) * 100).toFixed(1);
        return `${score.toFixed(2)} / ${maxScore.toFixed(2)} (${percentage}%)`;
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

    getStatusBadge(attempt) {
        if (attempt.passed) {
            return (
                <span style={{ 
                    padding: '4px 8px', 
                    backgroundColor: '#d4edda', 
                    color: '#155724', 
                    borderRadius: '4px',
                    fontSize: '0.875em',
                    fontWeight: '500'
                }}>
                    {__('Passed', 'skillpulse-lms')}
                </span>
            );
        } else if (attempt.score !== undefined && attempt.score !== null) {
            return (
                <span style={{ 
                    padding: '4px 8px', 
                    backgroundColor: '#f8d7da', 
                    color: '#721c24', 
                    borderRadius: '4px',
                    fontSize: '0.875em',
                    fontWeight: '500'
                }}>
                    {__('Failed', 'skillpulse-lms')}
                </span>
            );
        }
        return (
            <span style={{ 
                padding: '4px 8px', 
                backgroundColor: '#fff3cd', 
                color: '#856404', 
                borderRadius: '4px',
                fontSize: '0.875em',
                fontWeight: '500'
            }}>
                {__('Pending', 'skillpulse-lms')}
            </span>
        );
    }

    handleViewAttempt = (attemptId) => {
        // Navigate to quiz attempts detail page
        const quizAttemptsUrl = getAdminPageUrl('splms-quiz-attempts', { attempt_id: attemptId });
        window.open(quizAttemptsUrl, '_blank');
    }

    render() {
        const { enrollment } = this.props;
        const { attempts, isLoading, error } = this.state;

        if (!enrollment) return null;

        return (
            <Card>
                <CardHeader>
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                            <SplmsIcon mode="wp" icon="quiz" size={20} />
                            <h3 style={{ margin: 0 }}>{__('Quiz Attempts', 'skillpulse-lms')}</h3>
                        </div>
                        <span style={{ color: '#666', fontSize: '0.875em' }}>
                            {attempts.length} {__('attempts', 'skillpulse-lms')}
                        </span>
                    </div>
                </CardHeader>
                <CardBody>
                    {isLoading ? (
                        <div style={{ textAlign: 'center', padding: '40px' }}>
                            <Spinner />
                            <p>{__('Loading quiz attempts...', 'skillpulse-lms')}</p>
                        </div>
                    ) : error ? (
                        <div style={{ padding: '20px', backgroundColor: '#f8d7da', color: '#721c24', borderRadius: '4px' }}>
                            {error}
                        </div>
                    ) : attempts.length === 0 ? (
                        <p style={{ color: '#666', textAlign: 'center', padding: '20px' }}>
                            {__('No quiz attempts found for this enrollment.', 'skillpulse-lms')}
                        </p>
                    ) : (
                        <div style={{ overflowX: 'auto' }}>
                            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                                <thead>
                                    <tr style={{ borderBottom: '2px solid #ddd' }}>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Quiz', 'skillpulse-lms')}
                                        </th>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Attempt Date', 'skillpulse-lms')}
                                        </th>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Score', 'skillpulse-lms')}
                                        </th>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Status', 'skillpulse-lms')}
                                        </th>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Time Taken', 'skillpulse-lms')}
                                        </th>
                                        <th style={{ textAlign: 'left', padding: '12px', fontWeight: '600' }}>
                                            {__('Actions', 'skillpulse-lms')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {attempts.map((attempt, index) => (
                                        <tr 
                                            key={attempt.id || index}
                                            style={{ 
                                                borderBottom: '1px solid #eee'
                                            }}
                                        >
                                            <td style={{ padding: '12px' }}>
                                                <strong>
                                                    {attempt.quiz?.post_title || attempt.quiz?.title || `Quiz #${attempt.quiz_id}`}
                                                </strong>
                                            </td>
                                            <td style={{ padding: '12px', color: '#666' }}>
                                                {attempt.attempt_time ? formatDate(attempt.attempt_time) : '-'}
                                            </td>
                                            <td style={{ padding: '12px', color: '#666' }}>
                                                {this.formatScore(attempt.score || 0, attempt.max_score || 0)}
                                            </td>
                                            <td style={{ padding: '12px' }}>
                                                {this.getStatusBadge(attempt)}
                                            </td>
                                            <td style={{ padding: '12px', color: '#666' }}>
                                                {this.formatTime(attempt.time_taken)}
                                            </td>
                                            <td style={{ padding: '12px' }}>
                                                <Button
                                                    isSmall
                                                    isLink
                                                    onClick={() => this.handleViewAttempt(attempt.id)}
                                                >
                                                    {__('View Details', 'skillpulse-lms')}
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

export default QuizAttemptsTable;

