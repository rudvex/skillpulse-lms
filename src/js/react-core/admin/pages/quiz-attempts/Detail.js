/**
 * Detail component for displaying a single quiz attempt using SPLMS_DetailView
 *
 * REFACTORED VERSION - Uses reusable SPLMS_DetailView component
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { Card, CardBody } from '@wordpress/components';
import { SPLMS_DetailView } from '../../../components/admin/DetailView';
import PublishBox from '../../../components/admin/DetailView/PublishBox';
import QuickActions from '../../../components/admin/DetailView/QuickActions';
import ActivityLog from '../../../components/admin/DetailView/ActivityLog';
import SummaryCard from './components/SummaryCard';
import QuestionReviewList from './components/QuestionReviewList';
import FeedbackEditor from './components/FeedbackEditor';
import { fetchQuizAttemptQuestions } from './api';

class Detail extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			questions: [],
			questionsData: null,
			isLoadingQuestions: false,
		};
	}

	componentDidMount() {
		if ( this.props.attempt && this.props.attempt.id ) {
			this.loadQuestions();
		}
	}

	componentDidUpdate( prevProps ) {
		if ( prevProps.attempt?.id !== this.props.attempt?.id ) {
			this.loadQuestions();
		}
	}

	safeTranslate( value ) {
		if ( 'string' === typeof value ) {
			return value;
		}
		if ( value && 'object' === typeof value && value.rendered ) {
			return value.rendered;
		}
		return String( value || '' );
	}

	async loadQuestions() {
		const { attempt } = this.props;
		if ( ! attempt || ! attempt.id ) {
			return;
		}

		this.setState( { isLoadingQuestions: true } );

		try {
			const response = await fetchQuizAttemptQuestions( attempt.id );
			if ( response && response.success ) {
				this.setState( {
					questions: response.questions || [],
					questionsData: {
						total_questions: response.total_questions || 0,
						correct_count: response.correct_count || 0,
						incorrect_count: response.incorrect_count || 0,
						pending_review_count: response.pending_review_count || 0,
					},
				} );
			} else {
				const errorMessage = response?.message || this.safeTranslate( __( 'Failed to load questions', 'skillpulse-lms' ) );
				if ( window.skillpulseToast ) {
					window.skillpulseToast.error( errorMessage );
				}
			}
		} catch ( error ) {
			console.error( 'Error loading questions:', error );
			const errorMessage = error.message || this.safeTranslate( __( 'Failed to load questions', 'skillpulse-lms' ) );
			if ( window.skillpulseToast ) {
				window.skillpulseToast.error( errorMessage );
			}
		} finally {
			this.setState( { isLoadingQuestions: false } );
		}
	}

	handleFeedbackUpdate = ( feedback ) => {
		// Update the attempt object in parent if needed.
		if ( this.props.onFeedbackUpdate ) {
			this.props.onFeedbackUpdate( feedback );
		}
	};

	getStatus() {
		const { attempt } = this.props;
		const status = attempt.status || 'draft';

		switch ( status ) {
			case 'in_progress':
				return {
					label: __( 'In Progress', 'skillpulse-lms' ),
					type: 'info',
				};
			case 'submitted':
				return {
					label: __( 'Submitted', 'skillpulse-lms' ),
					type: 'info',
				};
			case 'pending_review':
				return {
					label: __( 'Pending Review', 'skillpulse-lms' ),
					type: 'warning',
				};
			case 'graded':
				// For graded, check if passed based on score.
				const passed = attempt.percentage >= ( attempt.passing_grade || 70 );
				return {
					label: passed ? __( 'Passed', 'skillpulse-lms' ) : __( 'Failed', 'skillpulse-lms' ),
					type: passed ? 'success' : 'error',
				};
			case 'expired':
				return {
					label: __( 'Expired', 'skillpulse-lms' ),
					type: 'error',
				};
			case 'requires_resubmission':
				return {
					label: __( 'Requires Resubmission', 'skillpulse-lms' ),
					type: 'warning',
				};
			default:
				return {
					label: __( 'Draft', 'skillpulse-lms' ),
					type: 'info',
				};
		}
	}

	getActions() {
		const { attempt, onDelete } = this.props;
		const actions = [];

		actions.push( {
			label: __( 'Delete Attempt', 'skillpulse-lms' ),
			isDestructive: true,
			onClick: () => {
				if ( window.confirm( this.safeTranslate( __( 'Are you sure you want to delete this attempt?', 'skillpulse-lms' ) ) ) ) {
					onDelete( attempt.id );
				}
			},
			icon: 'trash',
		} );

		return actions;
	}

	getSections() {
		const { attempt } = this.props;
		const { questions, questionsData, isLoadingQuestions } = this.state;

		return [
			{
				id: 'overview',
				label: __( 'Overview', 'skillpulse-lms' ),
				icon: 'dashboard',
				default: true,
				render: () => (
					<SummaryCard
						attempt={attempt}
						questionsData={questionsData}
					/>
				),
			},
			{
				id: 'questions',
				label: __( 'Questions', 'skillpulse-lms' ),
				icon: 'editor-ol',
				render: () => (
					<>
						{isLoadingQuestions ? (
							<div style={{ textAlign: 'center', padding: '40px' }}>
								<p>{this.safeTranslate( __( 'Loading questions...', 'skillpulse-lms' ) )}</p>
							</div>
						) : (
							<QuestionReviewList
								questions={questions}
								attemptId={attempt.id}
								onGradeUpdate={async ( response ) => {
									// Update attempt data after grading.
									if ( this.props.onAttemptUpdate ) {
										this.props.onAttemptUpdate( {
											...attempt,
											score: response.score,
											max_score: response.max_score,
											passed: response.passed,
											pending_review: false,
										} );
									}
									// Reload questions to get updated status.
									await this.loadQuestions();
								}}
							/>
						)}
					</>
				),
			},
			{
				id: 'feedback',
				label: __( 'Instructor Feedback', 'skillpulse-lms' ),
				icon: 'edit',
				render: () => (
					<FeedbackEditor
						attemptId={attempt.id}
						initialFeedback={attempt.feedback || ''}
						onUpdate={this.handleFeedbackUpdate}
					/>
				),
			},
		];
	}

	getSidebar() {
		const { attempt, onVerify, onDelete } = this.props;
		const { questionsData } = this.state;

		const userName = 0 === attempt.user_id
			? this.safeTranslate( __( 'Guest', 'skillpulse-lms' ) )
			: ( attempt.user_name || ( attempt.user ? attempt.user.display_name : null ) || `User ${attempt.user_id}` );

		const pendingReviewCount = questionsData?.pending_review_count || 0;

		return [
			{
				title: __( 'Status & Actions', 'skillpulse-lms' ),
				render: () => (
					<PublishBox
						status={this.getStatus()}
						actions={this.getActions()}
						metadata={[
							{
								label: __( 'Submitted', 'skillpulse-lms' ),
								value: dateI18n( 'M j, Y g:i A', attempt.attempt_time ),
							},
							{
								label: __( 'Time Taken', 'skillpulse-lms' ),
								value: this.formatTime( attempt.time_taken ),
							},
						]}
					/>
				),
			},
			{
				title: __( 'Quick Actions', 'skillpulse-lms' ),
				render: () => (
					<QuickActions
						actions={[
							{
								label: __( 'View User Profile', 'skillpulse-lms' ),
								url: 0 !== attempt.user_id
									? `${window.SPLMSCore_Data?.adminUrl || '/wp-admin'}/user-edit.php?user_id=${attempt.user_id}`
									: null,
								external: true,
								icon: 'admin-users',
							},
							{
								label: __( 'Edit Quiz', 'skillpulse-lms' ),
								url: attempt.quiz
									? `${window.SPLMSCore_Data?.adminUrl || '/wp-admin'}/post.php?post=${attempt.quiz_id}&action=edit`
									: null,
								external: true,
								icon: 'edit',
							},
							{
								label: __( 'View Course', 'skillpulse-lms' ),
								url: attempt.course
									? `${window.SPLMSCore_Data?.adminUrl || '/wp-admin'}/post.php?post=${attempt.course_id}&action=edit`
									: null,
								external: true,
								icon: 'welcome-learn-more',
							},
							{
								label: __( 'Email Student', 'skillpulse-lms' ),
								url: attempt.user?.user_email
									? `mailto:${attempt.user.user_email}`
									: null,
								icon: 'email',
							},
						].filter( ( action ) => null !== action.url )}
					/>
				),
			},
			{
				title: __( 'Quiz Information', 'skillpulse-lms' ),
				render: () => (
					<div className="splms-quiz-meta">
						<table style={{ width: '100%', fontSize: '13px', lineHeight: '1.8' }}>
							<tbody>
								<tr>
									<td style={{ color: '#666' }}>{__( 'Quiz:', 'skillpulse-lms' )}</td>
									<td style={{ textAlign: 'right' }}>
										<strong>{attempt.quiz?.post_title || `Quiz ${attempt.quiz_id}`}</strong>
									</td>
								</tr>
								<tr>
									<td style={{ color: '#666' }}>{__( 'Course:', 'skillpulse-lms' )}</td>
									<td style={{ textAlign: 'right' }}>
										<strong>{attempt.course?.post_title || `Course ${attempt.course_id}`}</strong>
									</td>
								</tr>
								<tr>
									<td style={{ color: '#666' }}>{__( 'Questions:', 'skillpulse-lms' )}</td>
									<td style={{ textAlign: 'right' }}>
										{questionsData?.total_questions || 0}
									</td>
								</tr>
								<tr>
									<td style={{ color: '#666' }}>{__( 'Student:', 'skillpulse-lms' )}</td>
									<td style={{ textAlign: 'right' }}>
										<strong>{userName}</strong>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				),
			},
			{
				title: __( 'Activity Timeline', 'skillpulse-lms' ),
				render: () => (
					<ActivityLog
						activities={[
							{
								type: 'success',
								title: __( 'Submitted', 'skillpulse-lms' ),
								description: `${__( 'By', 'skillpulse-lms' )} ${userName}`,
								timestamp: dateI18n( 'M j, g:i A', attempt.attempt_time ),
							},
							pendingReviewCount > 0
								? {
										type: 'pending',
										title: __( 'Awaiting Review', 'skillpulse-lms' ),
										description: `${pendingReviewCount} ${__( 'essay question(s)', 'skillpulse-lms' )}`,
								  }
								: null,
						].filter( Boolean )}
					/>
				),
			},
		];
	}

	formatTime( seconds ) {
		const hours = Math.floor( seconds / 3600 );
		const minutes = Math.floor( ( seconds % 3600 ) / 60 );
		const secs = seconds % 60;

		if ( hours > 0 ) {
			return `${hours}h ${minutes}m ${secs}s`;
		}
		if ( minutes > 0 ) {
			return `${minutes}m ${secs}s`;
		}
		return `${secs}s`;
	}

	render() {
		const { attempt, onBack } = this.props;
		const { isLoadingQuestions } = this.state;

		if ( ! attempt ) {
			return null;
		}

		return (
			<SPLMS_DetailView
				title={`${__( 'Quiz Attempt', 'skillpulse-lms' )} #${attempt.id}`}
				onBack={onBack}
				backLabel={__( 'Back to Attempts List', 'skillpulse-lms' )}
				sections={this.getSections()}
				sectionMode="tabs"
				sidebar={this.getSidebar()}
				isLoading={isLoadingQuestions && ! this.state.questions.length}
			/>
		);
	}
}

export default Detail;
