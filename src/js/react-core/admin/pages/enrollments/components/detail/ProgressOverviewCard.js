/**
 * Progress Overview Card Component
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Button } from '@wordpress/components';
import { SplmsIcon } from "../../../../../components/SplmsIcon";
import { ProgressBar } from '../EnrollmentBadges';

const ProgressOverviewCard = ({ enrollment, onGenerateCertificate }) => {
    if (!enrollment) return null;

    const progress = enrollment.progress || 0;
    const lessonsCompleted = enrollment.lessons_completed || 0;
    const totalLessons = enrollment.total_lessons || 0;
    const quizzesPassed = enrollment.quizzes_passed || 0;
    const totalQuizzes = enrollment.total_quizzes || 0;
    const completedItems = enrollment.completed_items || 0;
    const totalItems = enrollment.total_items || 0;

    const lessonsProgress = totalLessons > 0 ? (lessonsCompleted / totalLessons) * 100 : 0;
    const quizzesProgress = totalQuizzes > 0 ? (quizzesPassed / totalQuizzes) * 100 : 0;

    return (
        <Card style={{ marginBottom: '20px' }}>
            <CardHeader>
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                    <SplmsIcon mode="wp" icon="visibility" size={20} />
                    <h3 style={{ margin: 0 }}>{__('Progress Overview', 'skillpulse-lms')}</h3>
                </div>
            </CardHeader>
            <CardBody>
                {/* Overall Progress */}
                <div style={{ marginBottom: '24px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' }}>
                        <strong>{__('Overall Progress', 'skillpulse-lms')}</strong>
                        <span style={{ fontSize: '1.2em', fontWeight: 'bold', color: '#2271b1' }}>
                            {Math.round(progress)}%
                        </span>
                    </div>
                    <ProgressBar progress={progress} />
                </div>

                {/* Progress Breakdown */}
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '20px', marginBottom: '24px' }}>
                    {/* Lessons Progress */}
                    <div>
                        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px' }}>
                            <span style={{ color: '#666' }}>{__('Lessons', 'skillpulse-lms')}</span>
                            <span style={{ fontWeight: 'bold' }}>
                                {lessonsCompleted} / {totalLessons}
                            </span>
                        </div>
                        <ProgressBar progress={lessonsProgress} />
                    </div>

                    {/* Quizzes Progress */}
                    <div>
                        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px' }}>
                            <span style={{ color: '#666' }}>{__('Quizzes', 'skillpulse-lms')}</span>
                            <span style={{ fontWeight: 'bold' }}>
                                {quizzesPassed} / {totalQuizzes}
                            </span>
                        </div>
                        <ProgressBar progress={quizzesProgress} />
                    </div>
                </div>

                {/* Completion Status */}
                <div style={{ 
                    padding: '12px', 
                    backgroundColor: progress >= 100 ? '#d4edda' : progress > 0 ? '#fff3cd' : '#f8d7da',
                    borderRadius: '4px',
                    marginBottom: '16px'
                }}>
                    <strong>
                        {progress >= 100 
                            ? __('✓ Course Completed', 'skillpulse-lms')
                            : progress > 0 
                            ? __('In Progress', 'skillpulse-lms')
                            : __('Not Started', 'skillpulse-lms')
                        }
                    </strong>
                </div>

                {/* Certificate Section */}
                {progress >= 100 && (
                    <div style={{ 
                        padding: '16px', 
                        backgroundColor: '#f0f0f1', 
                        borderRadius: '4px',
                        border: '1px solid #ddd'
                    }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                            <div>
                                <strong style={{ display: 'block', marginBottom: '4px' }}>
                                    {__('Certificate Status', 'skillpulse-lms')}
                                </strong>
                                <span style={{ color: '#666' }}>
                                    {enrollment.certificate_earned 
                                        ? __('Certificate Earned', 'skillpulse-lms')
                                        : __('Certificate Not Generated', 'skillpulse-lms')
                                    }
                                </span>
                                {enrollment.certificate_url && (
                                    <div style={{ marginTop: '8px' }}>
                                        <a 
                                            href={enrollment.certificate_url} 
                                            target="_blank" 
                                            rel="noopener noreferrer"
                                            style={{ color: '#2271b1', textDecoration: 'none' }}
                                        >
                                            {__('View Certificate', 'skillpulse-lms')} →
                                        </a>
                                    </div>
                                )}
                            </div>
                            {!enrollment.certificate_earned && onGenerateCertificate && (enrollment.status === 'completed' || enrollment.progress >= 100 || enrollment.completed_at) && (
                                <Button
                                    isPrimary
                                    onClick={() => onGenerateCertificate(enrollment)}
                                >
                                    <SplmsIcon mode="wp" icon="awards" />
                                    {__('Generate Certificate', 'skillpulse-lms')}
                                </Button>
                            )}
                        </div>
                    </div>
                )}
            </CardBody>
        </Card>
    );
};

export default ProgressOverviewCard;

