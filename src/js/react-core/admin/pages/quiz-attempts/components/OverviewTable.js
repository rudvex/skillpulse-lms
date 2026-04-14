/**
 * OverviewTable component for displaying quiz questions with answer comparison
 */

import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Button, Modal, TextControl } from '@wordpress/components';
import { formatQuestionType } from '../utils/formatAnswers';
import { SplmsIcon } from "../../../../components/SplmsIcon";
import { gradeQuizAttempt } from '../api';

const OverviewTable = ({ questions = [], attemptId, onGradeUpdate }) => {
    const [expandedQuestion, setExpandedQuestion] = useState(null);
    const [gradingScores, setGradingScores] = useState({});
    const [isGrading, setIsGrading] = useState(false);

    const safeTranslate = (value) => {
        if (typeof value === 'string') return value;
        if (value && typeof value === 'object' && value.rendered) return value.rendered;
        return String(value || '');
    };

    const toggleQuestion = (questionId) => {
        setExpandedQuestion(expandedQuestion === questionId ? null : questionId);
    };
console.log("questions",questions);
    if (!questions || questions.length === 0) {
        return (
            <Card>
                <CardBody>
                    <p>{safeTranslate(__('No questions found.', 'skillpulse-lms'))}</p>
                </CardBody>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <h2>{safeTranslate(__('Quiz Overview', 'skillpulse-lms'))}</h2>
            </CardHeader>
            <CardBody>
                <div style={{ marginBottom: '20px', padding: '12px', backgroundColor: '#f0f0f1', borderRadius: '4px' }}>
                    <p style={{ margin: 0, fontSize: '14px', color: '#50575e' }}>
                        <strong>{safeTranslate(__('Instructions:', 'skillpulse-lms'))}</strong> {safeTranslate(__('Click the review button (↓) to expand each question and view detailed information, compare answers, or grade pending review questions.', 'skillpulse-lms'))}
                    </p>
                </div>
                <table className="splms-table splms-quiz-overview-table">
                    <thead>
                        <tr>
                            <th style={{ width: '50px' }}>{safeTranslate(__('No', 'skillpulse-lms'))}</th>
                            <th style={{ width: '120px' }}>{safeTranslate(__('Type', 'skillpulse-lms'))}</th>
                            <th style={{ minWidth: '250px' }}>{safeTranslate(__('Question', 'skillpulse-lms'))}</th>
                            <th style={{ minWidth: '200px' }}>{safeTranslate(__('Student Answer', 'skillpulse-lms'))}</th>
                            <th style={{ minWidth: '200px' }}>{safeTranslate(__('Correct Answer', 'skillpulse-lms'))}</th>
                            <th style={{ width: '120px' }}>{safeTranslate(__('Status', 'skillpulse-lms'))}</th>
                            <th style={{ width: '100px' }}>{safeTranslate(__('Points', 'skillpulse-lms'))}</th>
                            <th style={{ width: '80px' }}>{safeTranslate(__('Review', 'skillpulse-lms'))}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {questions.map((question, index) => (
                            <React.Fragment key={question.id}>
                                <tr className={question.is_correct ? 'splms-correct-row' : 'splms-incorrect-row'}>
                                    <td style={{ textAlign: 'center', fontWeight: 'bold' }}>{index + 1}</td>
                                    <td>
                                        <span className="splms-question-type-badge" style={{ 
                                            display: 'inline-block',
                                            padding: '4px 8px',
                                            borderRadius: '3px',
                                            fontSize: '12px',
                                            fontWeight: '500',
                                            backgroundColor: '#f0f0f1',
                                            color: '#1d2327'
                                        }}>
                                            {formatQuestionType(question.type)}
                                        </span>
                                    </td>
                                    <td>
                                        <div className="splms-question-text" style={{ lineHeight: '1.5' }}>
                                            <div style={{ marginBottom: '4px' }}>
                                                <strong>{question.question_text || `Question ${question.id}`}</strong>
                                            </div>
                                            {question.explanation && (
                                                <div style={{ fontSize: '12px', color: '#646970', fontStyle: 'italic', marginTop: '4px' }}>
                                                    {question.explanation.substring(0, 100)}{question.explanation.length > 100 ? '...' : ''}
                                                </div>
                                            )}
                                        </div>
                                    </td>
                                    <td>
                                        <div className={`splms-given-answer ${!question.is_correct ? 'splms-incorrect' : ''}`} style={{ 
                                            padding: '8px',
                                            backgroundColor: !question.is_correct ? '#fcf0f1' : '#f0f6fc',
                                            borderRadius: '3px',
                                            border: `1px solid ${!question.is_correct ? '#d63638' : '#2271b1'}`,
                                            minHeight: '40px'
                                        }}>
                                            {question.type === 'file_upload' && question.given_answer ? (
                                                <a 
                                                    href={question.given_answer} 
                                                    target="_blank" 
                                                    rel="noopener noreferrer"
                                                    className="splms-file-link"
                                                    style={{ color: '#2271b1', textDecoration: 'none' }}
                                                >
                                                    {safeTranslate(__('View Uploaded File', 'skillpulse-lms'))} ↗
                                                </a>
                                            ) : question.given_answer ? (
                                                <div style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>
                                                    {question.given_answer}
                                                </div>
                                            ) : (
                                                <span style={{ color: '#646970', fontStyle: 'italic' }}>—</span>
                                            )}
                                        </div>
                                    </td>
                                    <td>
                                        <div className="splms-correct-answer" style={{ 
                                            padding: '8px',
                                            backgroundColor: '#f0f6fc',
                                            borderRadius: '3px',
                                            border: '1px solid #2271b1',
                                            minHeight: '40px'
                                        }}>
                                            {question.type === 'file_upload' || question.type === 'essay' ? (
                                                <span style={{ color: '#646970', fontStyle: 'italic' }}>
                                                    {safeTranslate(__('No answer provided', 'skillpulse-lms'))}
                                                </span>
                                            ) : question.correct_answer ? (
                                                <div style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>
                                                    {question.correct_answer}
                                                </div>
                                            ) : (
                                                <span style={{ color: '#646970', fontStyle: 'italic' }}>—</span>
                                            )}
                                        </div>
                                    </td>
                                    <td style={{ textAlign: 'center' }}>
                                        {question.needs_manual_review && !question.is_graded ? (
                                            <span className="splms-status-pending" style={{ 
                                                display: 'inline-block',
                                                padding: '4px 8px',
                                                borderRadius: '3px',
                                                fontSize: '12px',
                                                fontWeight: '500',
                                                backgroundColor: '#fff3cd',
                                                color: '#856404',
                                                border: '1px solid #ffc107'
                                            }}>
                                                {safeTranslate(__('Pending Review', 'skillpulse-lms'))}
                                            </span>
                                        ) : (
                                            <span className={question.is_correct ? 'splms-status-passed' : 'splms-status-failed'} style={{ 
                                                display: 'inline-block',
                                                padding: '4px 8px',
                                                borderRadius: '3px',
                                                fontSize: '12px',
                                                fontWeight: '500',
                                                backgroundColor: question.is_correct ? '#d1e7dd' : '#f8d7da',
                                                color: question.is_correct ? '#0f5132' : '#842029',
                                                border: `1px solid ${question.is_correct ? '#badbcc' : '#f5c2c7'}`
                                            }}>
                                                {question.is_correct ? safeTranslate(__('Correct', 'skillpulse-lms')) : safeTranslate(__('Incorrect', 'skillpulse-lms'))}
                                            </span>
                                        )}
                                    </td>
                                    <td style={{ textAlign: 'center', fontWeight: 'bold' }}>
                                        {question.points > 0 ? (
                                            <span>{question.points}</span>
                                        ) : (
                                            <span style={{ color: '#646970' }}>—</span>
                                        )}
                                    </td>
                                    <td style={{ textAlign: 'center' }}>
                                        <Button
                                            isSmall
                                            onClick={() => toggleQuestion(question.id)}
                                            title={safeTranslate(__('View Details', 'skillpulse-lms'))}
                                            style={{ minWidth: 'auto' }}
                                        >
                                            <SplmsIcon mode="wp" icon={expandedQuestion === question.id ? "arrow-up" : "arrow-down"} />
                                        </Button>
                                    </td>
                                </tr>
                                {expandedQuestion === question.id && (
                                    <tr className="splms-question-details-row">
                                        <td colSpan="8">
                                            <div className="splms-question-details">
                                                <div className="detail-section">
                                                    <h4>{safeTranslate(__('Question Details', 'skillpulse-lms'))}</h4>
                                                    <div className="detail-content">
                                                        <p><strong>{safeTranslate(__('Type:', 'skillpulse-lms'))}</strong> {formatQuestionType(question.type)}</p>
                                                        <p><strong>{safeTranslate(__('Points:', 'skillpulse-lms'))}</strong> {question.points}</p>
                                                        {question.explanation && (
                                                            <div className="explanation">
                                                                <strong>{safeTranslate(__('Explanation:', 'skillpulse-lms'))}</strong>
                                                                <p>{question.explanation}</p>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                                
                                                {question.options && question.options.length > 0 && (
                                                    <div className="detail-section">
                                                        <h4>{safeTranslate(__('Available Options', 'skillpulse-lms'))}</h4>
                                                        <ul className="splms-options-list">
                                                            {question.options.map((option, optIndex) => (
                                                                <li 
                                                                    key={optIndex}
                                                                    className={option.is_correct ? 'splms-correct-option' : ''}
                                                                >
                                                                    {option.text || option.id}
                                                                    {option.is_correct && (
                                                                        <span className="splms-correct-indicator">
                                                                            <SplmsIcon mode="wp" icon="yes" />
                                                                        </span>
                                                                    )}
                                                                </li>
                                                            ))}
                                                        </ul>
                                                    </div>
                                                )}

                                                {question.needs_manual_review && !question.is_graded ? (
                                                    <div className="detail-section">
                                                        <h4>
                                                            {question.type === 'file_upload' 
                                                                ? safeTranslate(__('Grade File Upload Question', 'skillpulse-lms'))
                                                                : question.type === 'essay'
                                                                ? safeTranslate(__('Grade Essay Question', 'skillpulse-lms'))
                                                                : safeTranslate(__('Grade Question', 'skillpulse-lms'))
                                                            }
                                                        </h4>
                                                        <div className="essay-grading">
                                                            <div className="answer-item">
                                                                <strong>{safeTranslate(__('Student Answer:', 'skillpulse-lms'))}</strong>
                                                                <div className="answer-value essay-answer">
                                                                    {question.type === 'file_upload' && question.given_answer ? (
                                                                        <div className="splms-file-upload-answer">
                                                                            <a 
                                                                                href={question.given_answer} 
                                                                                target="_blank" 
                                                                                rel="noopener noreferrer"
                                                                                className="splms-file-link-button"
                                                                                style={{
                                                                                    display: 'inline-block',
                                                                                    padding: '8px 16px',
                                                                                    backgroundColor: '#0073aa',
                                                                                    color: '#fff',
                                                                                    textDecoration: 'none',
                                                                                    borderRadius: '3px',
                                                                                    marginTop: '8px'
                                                                                }}
                                                                            >
                                                                                {safeTranslate(__('View Uploaded File', 'skillpulse-lms'))}
                                                                            </a>
                                                                            <div style={{ marginTop: '8px', fontSize: '12px', color: '#666' }}>
                                                                                {question.given_answer}
                                                                            </div>
                                                                        </div>
                                                                    ) : (
                                                                        <div style={{ whiteSpace: 'pre-wrap' }}>
                                                                            {question.given_answer || '—'}
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            </div>
                                                            <div className="grading-controls">
                                                                <div style={{ display: 'flex', gap: '10px', alignItems: 'flex-end', marginBottom: '10px' }}>
                                                                    <div style={{ flex: 1 }}>
                                                                        <TextControl
                                                                            label={safeTranslate(__('POINTS AWARDED (OUT OF', 'skillpulse-lms')) + ` ${question.points}):`}
                                                                            type="number"
                                                                            min="0"
                                                                            max={question.points}
                                                                            step="0.5"
                                                                            value={gradingScores[question.id] !== undefined ? gradingScores[question.id] : ''}
                                                                            onChange={(value) => {
                                                                                const numValue = parseFloat(value) || 0;
                                                                                const clampedValue = Math.min(Math.max(numValue, 0), question.points);
                                                                                setGradingScores({
                                                                                    ...gradingScores,
                                                                                    [question.id]: clampedValue
                                                                                });
                                                                            }}
                                                                        />
                                                                    </div>
                                                                    <Button
                                                                        isDestructive
                                                                        onClick={async () => {
                                                                            if (!attemptId) return;
                                                                            setIsGrading(true);
                                                                            
                                                                            try {
                                                                                const questionScores = {
                                                                                    [question.id]: 0
                                                                                };
                                                                                const response = await gradeQuizAttempt(attemptId, questionScores);
                                                                                if (response && response.success) {
                                                                                    if (window.skillpulseToast) {
                                                                                        window.skillpulseToast.success(safeTranslate(__('Question marked as failed!', 'skillpulse-lms')));
                                                                                    }
                                                                                    if (onGradeUpdate) {
                                                                                        onGradeUpdate(response);
                                                                                    }
                                                                                    // Clear grading score for this question
                                                                                    setGradingScores({
                                                                                        ...gradingScores,
                                                                                        [question.id]: 0
                                                                                    });
                                                                                } else {
                                                                                    const errorMessage = response?.message || safeTranslate(__('Failed to mark question as failed', 'skillpulse-lms'));
                                                                                    if (window.skillpulseToast) {
                                                                                        window.skillpulseToast.error(errorMessage);
                                                                                    }
                                                                                }
                                                                            } catch (error) {
                                                                                const errorMessage = error.message || safeTranslate(__('Failed to mark question as failed', 'skillpulse-lms'));
                                                                                if (window.skillpulseToast) {
                                                                                    window.skillpulseToast.error(errorMessage);
                                                                                }
                                                                            } finally {
                                                                                setIsGrading(false);
                                                                            }
                                                                        }}
                                                                        disabled={isGrading}
                                                                        style={{ marginBottom: '8px' }}
                                                                    >
                                                                        {safeTranslate(__('Mark as Failed', 'skillpulse-lms'))}
                                                                    </Button>
                                                                </div>
                                                                <Button
                                                                    isPrimary
                                                                    onClick={async () => {
                                                                        if (!attemptId) return;
                                                                        setIsGrading(true);
                                                                        
                                                                        try {
                                                                            const questionScores = {
                                                                                [question.id]: parseFloat(gradingScores[question.id]) || 0
                                                                            };
                                                                            const response = await gradeQuizAttempt(attemptId, questionScores);
                                                                            
                                                                            if (response && response.success) {
                                                                                if (window.skillpulseToast) {
                                                                                    window.skillpulseToast.success(safeTranslate(__('Question graded successfully!', 'skillpulse-lms')));
                                                                                }
                                                                                if (onGradeUpdate) {
                                                                                    onGradeUpdate(response);
                                                                                }
                                                                                // Clear grading score for this question after successful save
                                                                                setGradingScores({
                                                                                    ...gradingScores,
                                                                                    [question.id]: undefined
                                                                                });
                                                                            } else {
                                                                                const errorMessage = response?.message || safeTranslate(__('Failed to grade question', 'skillpulse-lms'));
                                                                                if (window.skillpulseToast) {
                                                                                    window.skillpulseToast.error(errorMessage);
                                                                                }
                                                                            }
                                                                        } catch (error) {
                                                                            const errorMessage = error.message || safeTranslate(__('Failed to grade question', 'skillpulse-lms'));
                                                                            if (window.skillpulseToast) {
                                                                                window.skillpulseToast.error(errorMessage);
                                                                            }
                                                                        } finally {
                                                                            setIsGrading(false);
                                                                        }
                                                                    }}
                                                                    disabled={isGrading || (gradingScores[question.id] === undefined || gradingScores[question.id] === '')}
                                                                >
                                                                    {isGrading ? safeTranslate(__('Grading...', 'skillpulse-lms')) : safeTranslate(__('Save Grade', 'skillpulse-lms'))}
                                                                </Button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <div className="detail-section">
                                                        <h4>{safeTranslate(__('Answer Comparison', 'skillpulse-lms'))}</h4>
                                                        <div className="answer-comparison">
                                                            <div className="answer-item">
                                                                <strong>{safeTranslate(__('Given Answer:', 'skillpulse-lms'))}</strong>
                                                                <div className={`answer-value ${!question.is_correct ? 'incorrect' : 'correct'}`}>
                                                                    {question.given_answer || '—'}
                                                                </div>
                                                            </div>
                                                            <div className="answer-item">
                                                                <strong>{safeTranslate(__('Correct Answer:', 'skillpulse-lms'))}</strong>
                                                                <div className="answer-value correct">
                                                                    {question.correct_answer || '—'}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </React.Fragment>
                        ))}
                    </tbody>
                </table>
            </CardBody>
        </Card>
    );
};

export default OverviewTable;

