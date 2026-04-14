/**
 * QuestionReviewList - Clean card-based layout for reviewing quiz questions
 * Replaces the complex OverviewTable with a more intuitive interface
 */

import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, SelectControl } from '@wordpress/components';
import QuestionReviewCard from './QuestionReviewCard';

const QuestionReviewList = ({ questions = [], attemptId, onGradeUpdate }) => {
	const [filterStatus, setFilterStatus] = useState('all');
	const [sortBy, setSortBy] = useState('number');

	const safeTranslate = (value) => {
		if ('string' === typeof value) {
			return value;
		}
		if (value && 'object' === typeof value && value.rendered) {
			return value.rendered;
		}
		return String(value || '');
	};

	if (!questions || 0 === questions.length) {
		return (
			<Card>
				<CardBody>
					<p>{safeTranslate(__('No questions found.', 'skillpulse-lms'))}</p>
				</CardBody>
			</Card>
		);
	}

	// Filter questions.
	const filteredQuestions = questions.filter((question) => {
		if ('all' === filterStatus) {
			return true;
		}
		if ('pending' === filterStatus) {
			return question.needs_manual_review && !question.is_graded;
		}
		if ('correct' === filterStatus) {
			return question.is_correct;
		}
		if ('incorrect' === filterStatus) {
			return !question.is_correct && (!question.needs_manual_review || question.is_graded);
		}
		return true;
	});

	// Sort questions.
	const sortedQuestions = [...filteredQuestions].sort((a, b) => {
		if ('number' === sortBy) {
			return 0; // Keep original order.
		}
		if ('status' === sortBy) {
			// Priority: pending > incorrect > correct.
			const aScore = a.needs_manual_review && !a.is_graded ? 3 : !a.is_correct ? 2 : 1;
			const bScore = b.needs_manual_review && !b.is_graded ? 3 : !b.is_correct ? 2 : 1;
			return bScore - aScore;
		}
		return 0;
	});

	const stats = {
		total: questions.length,
		pending: questions.filter((q) => q.needs_manual_review && !q.is_graded).length,
		correct: questions.filter((q) => q.is_correct).length,
		incorrect: questions.filter((q) => !q.is_correct && (!q.needs_manual_review || q.is_graded)).length,
	};

	return (
		<div className="splms-question-review-list">
			{/* Stats Header */}
			<div className="splms-review-stats">
				<div className="splms-stat-card splms-stat-total">
					<span className="splms-stat-value">{stats.total}</span>
					<span className="splms-stat-label">{__('Total Questions', 'skillpulse-lms')}</span>
				</div>
				<div className="splms-stat-card splms-stat-correct">
					<span className="splms-stat-value">{stats.correct}</span>
					<span className="splms-stat-label">{__('Correct', 'skillpulse-lms')}</span>
				</div>
				<div className="splms-stat-card splms-stat-incorrect">
					<span className="splms-stat-value">{stats.incorrect}</span>
					<span className="splms-stat-label">{__('Incorrect', 'skillpulse-lms')}</span>
				</div>
				{stats.pending > 0 && (
					<div className="splms-stat-card splms-stat-pending">
						<span className="splms-stat-value">{stats.pending}</span>
						<span className="splms-stat-label">{__('Needs Review', 'skillpulse-lms')}</span>
					</div>
				)}
			</div>

			{/* Filters and Controls */}
			<div className="splms-review-controls">
				<SelectControl
					label={__('Filter by Status', 'skillpulse-lms')}
					value={filterStatus}
					onChange={setFilterStatus}
					options={[
						{ label: __('All Questions', 'skillpulse-lms'), value: 'all' },
						{ label: __('Pending Review', 'skillpulse-lms'), value: 'pending' },
						{ label: __('Correct', 'skillpulse-lms'), value: 'correct' },
						{ label: __('Incorrect', 'skillpulse-lms'), value: 'incorrect' },
					]}
				/>
				<SelectControl
					label={__('Sort By', 'skillpulse-lms')}
					value={sortBy}
					onChange={setSortBy}
					options={[
						{ label: __('Question Number', 'skillpulse-lms'), value: 'number' },
						{ label: __('Status (Review First)', 'skillpulse-lms'), value: 'status' },
					]}
				/>
			</div>

			{/* Questions List */}
			<div className="splms-questions-list">
				{0 === sortedQuestions.length ? (
					<Card>
						<CardBody>
							<p style={{ textAlign: 'center', color: '#666', margin: '20px 0' }}>
								{safeTranslate(__('No questions match the selected filter.', 'skillpulse-lms'))}
							</p>
						</CardBody>
					</Card>
				) : (
					sortedQuestions.map((question, index) => (
						<QuestionReviewCard
							key={question.id}
							question={question}
							questionNumber={questions.findIndex((q) => q.id === question.id) + 1}
							attemptId={attemptId}
							onGradeUpdate={onGradeUpdate}
						/>
					))
				)}
			</div>
		</div>
	);
};

export default QuestionReviewList;
