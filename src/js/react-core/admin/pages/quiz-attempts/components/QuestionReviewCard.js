/**
 * QuestionReviewCard - Individual question card for quiz attempt review
 * Clean, card-based layout replacing the complex table structure
 */

import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, Button, TextControl } from '@wordpress/components';
import { SplmsIcon } from '../../../../components/SplmsIcon';
import { formatQuestionType } from '../utils/formatAnswers';
import { gradeQuizAttempt } from '../api';

const QuestionReviewCard = ({ question, questionNumber, attemptId, onGradeUpdate }) => {
	const [gradingScore, setGradingScore] = useState('');
	const [isGrading, setIsGrading] = useState(false);
	const [isExpanded, setIsExpanded] = useState(false);

	const safeTranslate = (value) => {
		if ('string' === typeof value) {
			return value;
		}
		if (value && 'object' === typeof value && value.rendered) {
			return value.rendered;
		}
		return String(value || '');
	};

	const getStatusConfig = () => {
		if (question.needs_manual_review && !question.is_graded) {
			return {
				type: 'pending',
				label: __('Pending Review', 'skillpulse-lms'),
				icon: 'clock',
				color: 'var(--splms-warning, #f59e0b)',
			};
		}

		if (question.is_correct) {
			return {
				type: 'correct',
				label: __('Correct', 'skillpulse-lms'),
				icon: 'yes-alt',
				color: 'var(--splms-success, #10b981)',
			};
		}

		return {
			type: 'incorrect',
			label: __('Incorrect', 'skillpulse-lms'),
			icon: 'dismiss',
			color: 'var(--splms-danger, #ef4444)',
		};
	};

	const handleGrade = async (score) => {
		if (!attemptId) {
			return;
		}

		setIsGrading(true);

		try {
			const questionScores = { [question.id]: score };
			const response = await gradeQuizAttempt(attemptId, questionScores);

			if (response && response.success) {
				if (window.skillpulseToast) {
					window.skillpulseToast.success(
						0 === score
							? safeTranslate(__('Question marked as failed!', 'skillpulse-lms'))
							: safeTranslate(__('Question graded successfully!', 'skillpulse-lms'))
					);
				}
				if (onGradeUpdate) {
					onGradeUpdate(response);
				}
				setGradingScore('');
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
	};

	const status = getStatusConfig();
	const questionText = question.question_text || `Question ${question.id}`;
	const questionPreview = questionText.length > 80 ? `${questionText.substring(0, 80)}...` : questionText;

	return (
		<Card className={`splms-question-card splms-question-${status.type} ${isExpanded ? 'is-expanded' : 'is-collapsed'}`}>
			<CardBody>
				{/* Question Header - Clickable to expand/collapse */}
				<div
					className="splms-question-header"
					onClick={() => setIsExpanded(!isExpanded)}
					role="button"
					tabIndex={0}
					onKeyPress={(e) => {
						if ('Enter' === e.key || ' ' === e.key) {
							e.preventDefault();
							setIsExpanded(!isExpanded);
						}
					}}
				>
					<div className="splms-question-header-left">
						<div className="splms-question-meta">
							<span className="splms-question-number">#{questionNumber}</span>
							<span className="splms-question-type-badge">
								{formatQuestionType(question.type)}
							</span>
						</div>
						{!isExpanded && (
							<div className="splms-question-preview">
								{questionPreview}
							</div>
						)}
					</div>
					<div className="splms-question-header-right">
						<div className={`splms-question-status splms-status-${status.type}`}>
							<SplmsIcon mode="wp" icon={status.icon} size={16} />
							<span>{status.label}</span>
						</div>
						<Button
							icon={isExpanded ? 'arrow-up-alt2' : 'arrow-down-alt2'}
							className="splms-expand-toggle"
							onClick={(e) => {
								e.stopPropagation();
								setIsExpanded(!isExpanded);
							}}
							label={isExpanded ? __('Collapse', 'skillpulse-lms') : __('Expand', 'skillpulse-lms')}
						/>
					</div>
				</div>

				{/* Expanded Content */}
				{isExpanded && (
					<div className="splms-question-expanded-content">
						{/* Question Text */}
						<div className="splms-question-text">
							<h4>{__('Question', 'skillpulse-lms')}</h4>
							<p>{questionText}</p>
						</div>

				{/* Answer Section */}
				<div className="splms-answer-section">
					<div className="splms-answer-grid">
						{/* Student Answer */}
						<div className={`splms-answer-box splms-student-answer ${!question.is_correct ? 'incorrect' : 'correct'}`}>
							<div className="splms-answer-label">
								<SplmsIcon mode="wp" icon="admin-users" size={16} />
								<strong>{__('Student Answer', 'skillpulse-lms')}</strong>
							</div>
							<div className="splms-answer-content">
								{'file_upload' === question.type && question.given_answer ? (
									<a
										href={question.given_answer}
										target="_blank"
										rel="noopener noreferrer"
										className="splms-file-link"
									>
										<SplmsIcon mode="wp" icon="media-document" size={18} />
										{__('View Uploaded File', 'skillpulse-lms')} ↗
									</a>
								) : question.given_answer ? (
									<div className="splms-answer-text">{question.given_answer}</div>
								) : (
									<span className="splms-no-answer">
										{__('No answer provided', 'skillpulse-lms')}
									</span>
								)}
							</div>
						</div>

						{/* Correct Answer (if applicable) */}
						{'file_upload' !== question.type && 'essay' !== question.type && (
							<div className="splms-answer-box splms-correct-answer">
								<div className="splms-answer-label">
									<SplmsIcon mode="wp" icon="yes-alt" size={16} />
									<strong>{__('Correct Answer', 'skillpulse-lms')}</strong>
								</div>
								<div className="splms-answer-content">
									{question.correct_answer ? (
										<div className="splms-answer-text">{question.correct_answer}</div>
									) : (
										<span className="splms-no-answer">—</span>
									)}
								</div>
							</div>
						)}
					</div>
				</div>

				{/* Options (for multiple choice, etc.) */}
				{question.options && question.options.length > 0 && (
					<div className="splms-options-section">
						<h5>{__('Available Options', 'skillpulse-lms')}</h5>
						<ul className="splms-options-list">
							{question.options.map((option, index) => (
								<li
									key={index}
									className={option.is_correct ? 'splms-correct-option' : ''}
								>
									<span className="splms-option-text">{option.text || option.id}</span>
									{option.is_correct && (
										<span className="splms-correct-indicator">
											<SplmsIcon mode="wp" icon="yes" size={14} />
											{__('Correct', 'skillpulse-lms')}
										</span>
									)}
								</li>
							))}
						</ul>
					</div>
				)}

				{/* Explanation */}
				{question.explanation && (
					<div className="splms-explanation-section">
						<h5>
							<SplmsIcon mode="wp" icon="info" size={16} />
							{__('Explanation', 'skillpulse-lms')}
						</h5>
						<p>{question.explanation}</p>
					</div>
				)}

				{/* Grading Section (for manual review) */}
				{question.needs_manual_review && !question.is_graded ? (
					<div className="splms-grading-section">
						<h5>{__('Grade This Question', 'skillpulse-lms')}</h5>
						<div className="splms-grading-controls">
							<div className="splms-grading-input">
								<TextControl
									label={`${__('Points', 'skillpulse-lms')} (${__('out of', 'skillpulse-lms')} ${question.points})`}
									type="number"
									min="0"
									max={question.points}
									step="0.5"
									value={gradingScore}
									onChange={(value) => {
										const numValue = parseFloat(value) || 0;
										const clampedValue = Math.min(Math.max(numValue, 0), question.points);
										setGradingScore(clampedValue);
									}}
									className="splms-grade-input"
								/>
							</div>
							<div className="splms-grading-buttons">
								<Button
									variant="primary"
									onClick={() => handleGrade(parseFloat(gradingScore) || 0)}
									disabled={isGrading || '' === gradingScore}
									isBusy={isGrading}
								>
									{isGrading
										? __('Saving...', 'skillpulse-lms')
										: __('Save Grade', 'skillpulse-lms')}
								</Button>
								<Button
									variant="secondary"
									onClick={() => handleGrade(question.points)}
									disabled={isGrading}
								>
									<SplmsIcon mode="wp" icon="yes-alt" size={16} />
									{__('Full Points', 'skillpulse-lms')} ({question.points})
								</Button>
								<Button
									isDestructive
									onClick={() => handleGrade(0)}
									disabled={isGrading}
								>
									<SplmsIcon mode="wp" icon="dismiss" size={16} />
									{__('Mark Failed', 'skillpulse-lms')}
								</Button>
							</div>
						</div>
					</div>
				) : (
					<div className="splms-points-display">
						<SplmsIcon mode="wp" icon="star-filled" size={16} />
						<strong>{__('Points', 'skillpulse-lms')}:</strong>
						<span className="splms-points-value">
							{question.points > 0 ? question.points : '0'} / {question.points}
						</span>
					</div>
				)}
					</div>
				)}
			</CardBody>
		</Card>
	);
};

export default QuestionReviewCard;
