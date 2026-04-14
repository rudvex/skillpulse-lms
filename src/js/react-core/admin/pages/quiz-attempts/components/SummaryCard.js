/**
 * SummaryCard component for displaying quiz attempt summary
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader } from '@wordpress/components';
import { dateI18n } from '@wordpress/date';
import { SplmsIcon } from "../../../../components/SplmsIcon";

const SummaryCard = ({ attempt, questionsData }) => {
    const safeTranslate = (value) => {
        if (typeof value === 'string') return value;
        if (value && typeof value === 'object' && value.rendered) return value.rendered;
        return String(value || '');
    };

    const formatTime = (seconds) => {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        if (hours > 0) {
            return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }
        return `${minutes}:${String(secs).padStart(2, '0')}`;
    };

    const getUserAvatar = (userId, userName) => {
        // For guest users, use "Guest" as the name for avatar generation.
        const name = userId === 0 ? 'Guest' : (userName || 'User');
        return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=7e75ff&color=fff&size=60`;
    };

    if (!attempt) return null;

    const totalQuestions = questionsData?.total_questions || 0;
    const correctAnswers = questionsData?.correct_count || 0;
    const incorrectAnswers = questionsData?.incorrect_count || 0;
    const pendingReviewAnswers = questionsData?.pending_review_count || 0;

    // BUGFIX: Detect and correct score calculation issues.
    // If attempt shows 0.00/0.00 but we have correct answers, recalculate from questions data.
    let effectiveScore = attempt.score;
    let effectiveMaxScore = attempt.max_score;
    let percentage = attempt.max_score > 0 ? ((attempt.score / attempt.max_score) * 100).toFixed(1) : 0;

    if ((attempt.score === 0 && attempt.max_score === 0) && totalQuestions > 0 && correctAnswers > 0) {
        // Database has incorrect 0/0 score but questions show correct answers.
        // Calculate score from questions data as fallback.
        effectiveScore = correctAnswers; // Assume 1 point per correct question.
        effectiveMaxScore = totalQuestions; // Assume 1 point per question.
        percentage = totalQuestions > 0 ? ((correctAnswers / totalQuestions) * 100).toFixed(1) : 0;

        // Log this for debugging.
        console.warn('SPLMS: Detected score calculation issue, using questions data fallback. Score:', effectiveScore, 'Max:', effectiveMaxScore, 'Percentage:', percentage + '%');
    }
    // Show "Guest" for guest attempts (user_id = 0), otherwise show user name or "User {id}".
    // Use top-level user_name/user_email first (for consistency with enrollments), then fall back to nested user object.
    const userName = attempt.user_id === 0 
        ? safeTranslate(__('Guest', 'skillpulse-lms'))
        : (attempt.user_name || (attempt.user ? attempt.user.display_name : null) || `User ${attempt.user_id}`);
    const userEmail = attempt.user_email || (attempt.user ? attempt.user.user_email : '');
    // Use user_avatar from API if available (same as enrollments), otherwise generate one
    const userAvatar = attempt.user_avatar || getUserAvatar(attempt.user_id, userName);

    // Determine if attempt passed using corrected score.
    // Default passing grade is 70%, but we'll use the percentage we calculated.
    const passingGrade = attempt.passing_grade || 70;
    const effectivePassed = parseFloat(percentage) >= passingGrade;

    const resultStatus = attempt.pending_review || pendingReviewAnswers > 0
        ? { text: safeTranslate(__('Pending Review', 'skillpulse-lms')), class: 'splms-status-pending' }
        : effectivePassed
            ? { text: safeTranslate(__('Passed', 'skillpulse-lms')), class: 'splms-status-passed' }
            : { text: safeTranslate(__('Failed', 'skillpulse-lms')), class: 'splms-status-failed' };

    return (
        <Card>
            <CardHeader>
                <h2>{safeTranslate(__('Attempt Summary', 'skillpulse-lms'))}</h2>
            </CardHeader>
            <CardBody>
                <div className="splms-attempt-summary">
                    {/* Main Performance Section */}
                    <div className="summary-performance">
                        <div className="summary-score-card">
                            <div className="score-main">
                                <div className="score-value">{percentage}%</div>
                                <div className="score-label">{safeTranslate(__('Score', 'skillpulse-lms'))}</div>
                            </div>
                            <div className="score-details">
                                <div className="score-marks">
                                    <span className="earned">{effectiveScore.toFixed(2)}</span>
                                    <span className="separator">/</span>
                                    <span className="total">{effectiveMaxScore.toFixed(2)}</span>
                                </div>
                                <div className="result-badge">
                                    <span className={resultStatus.class}>{resultStatus.text}</span>
                                </div>
                            </div>
                        </div>

                        <div className="summary-stats">
                            <div className="stat-item stat-correct">
                                <div className="stat-value">{correctAnswers}</div>
                                <div className="stat-label">{safeTranslate(__('Correct', 'skillpulse-lms'))}</div>
                            </div>
                            <div className="stat-item stat-incorrect">
                                <div className="stat-value">{incorrectAnswers}</div>
                                <div className="stat-label">{safeTranslate(__('Incorrect', 'skillpulse-lms'))}</div>
                            </div>
                            {pendingReviewAnswers > 0 && (
                                <div className="stat-item stat-pending">
                                    <div className="stat-value">{pendingReviewAnswers}</div>
                                    <div className="stat-label">{safeTranslate(__('Pending', 'skillpulse-lms'))}</div>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Details Section */}
                    <div className="summary-details">
                        <div className="detail-group">
                            <div className="detail-item">
                                <span className="detail-label">{safeTranslate(__('Course', 'skillpulse-lms'))}</span>
                                <span className="detail-value">
                                    {attempt.course ? (
                                        <a 
                                            href={`${window.SPLMSCore_Data?.adminUrl || '/wp-admin'}/post.php?post=${attempt.course_id}&action=edit`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            {attempt.course.post_title}
                                            <span className="link-icon">↗</span>
                                        </a>
                                    ) : (
                                        `Course ${attempt.course_id}`
                                    )}
                                </span>
                            </div>
                            <div className="detail-item">
                                <span className="detail-label">{safeTranslate(__('Quiz', 'skillpulse-lms'))}</span>
                                <span className="detail-value">
                                    {attempt.quiz ? (
                                        <a 
                                            href={`${window.SPLMSCore_Data?.adminUrl || '/wp-admin'}/post.php?post=${attempt.quiz_id}&action=edit`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            {attempt.quiz.post_title}
                                            <span className="link-icon">↗</span>
                                        </a>
                                    ) : (
                                        `Quiz ${attempt.quiz_id}`
                                    )}
                                </span>
                            </div>
                        </div>

                        <div className="detail-group">
                            <div className="detail-item">
                                <span className="detail-label">{safeTranslate(__('Attempted By', 'skillpulse-lms'))}</span>
                                <span className="detail-value">
                                    <div className="user-info-compact">
                                        <img
                                            src={userAvatar}
                                            alt={userName}
                                            className="user-avatar-small"
                                            onError={(e) => {
                                                e.target.src = getUserAvatar(attempt.user_id, userName);
                                            }}
                                        />
                                        <div>
                                            {attempt.user_id === 0 ? (
                                                <strong>{userName}</strong>
                                            ) : attempt.user ? (
                                                <a 
                                                    href={`${window.SPLMSCore_Data?.adminUrl || '/wp-admin'}/user-edit.php?user_id=${attempt.user_id}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <strong>{userName}</strong>
                                                    <span className="link-icon">↗</span>
                                                </a>
                                            ) : (
                                                <strong>{userName}</strong>
                                            )}
                                            {userEmail && <div className="user-email">{userEmail}</div>}
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div className="detail-item">
                                <span className="detail-label">{safeTranslate(__('Date & Time', 'skillpulse-lms'))}</span>
                                <span className="detail-value">{dateI18n('M j, Y g:i A', attempt.attempt_time)}</span>
                            </div>
                        </div>

                        <div className="detail-group">
                            <div className="detail-item">
                                <span className="detail-label">{safeTranslate(__('Questions', 'skillpulse-lms'))}</span>
                                <span className="detail-value">{totalQuestions}</span>
                            </div>
                            <div className="detail-item">
                                <span className="detail-label">{safeTranslate(__('Time Taken', 'skillpulse-lms'))}</span>
                                <span className="detail-value">{formatTime(attempt.time_taken)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </CardBody>
        </Card>
    );
};

export default SummaryCard;

