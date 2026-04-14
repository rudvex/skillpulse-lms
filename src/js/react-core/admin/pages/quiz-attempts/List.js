/**
 * List component for displaying quiz attempts using SPLMS_ListView
 *
 * REFACTORED VERSION - Uses reusable SPLMS_ListView component
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { dateI18n } from '@wordpress/date';
import { SPLMS_ListView } from '../../../components/admin/ListView';
import { SplmsIcon } from '../../../components/SplmsIcon';

class List extends Component {
	constructor( props ) {
		super( props );
		this.handleBulkAction = this.handleBulkAction.bind( this );
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

	handleBulkAction( action, selectedItems ) {
		const { onVerifyAttempt, onDeleteAttempt } = this.props;

		if ( 'verify' === action ) {
			if ( window.confirm( this.safeTranslate( __( 'Are you sure you want to verify the selected attempts?', 'skillpulse-lms' ) ) ) ) {
				selectedItems.forEach( ( attemptId ) => {
					onVerifyAttempt( attemptId );
				} );
			}
		} else if ( 'delete' === action ) {
			if ( window.confirm( this.safeTranslate( __( 'Are you sure you want to delete the selected attempts? This action cannot be undone.', 'skillpulse-lms' ) ) ) ) {
				selectedItems.forEach( ( attemptId ) => {
					onDeleteAttempt( attemptId, true ); // true = skip individual confirmation for bulk operations
				} );
			}
		}
	}

	formatTime( seconds ) {
		const hours = Math.floor( seconds / 3600 );
		const minutes = Math.floor( ( seconds % 3600 ) / 60 );
		const secs = seconds % 60;

		if ( hours > 0 ) {
			return `${hours}:${String( minutes ).padStart( 2, '0' )}:${String( secs ).padStart( 2, '0' )}`;
		}
		return `${minutes}:${String( secs ).padStart( 2, '0' )}`;
	}

	formatScore( score, maxScore ) {
		const percentage = maxScore > 0 ? ( ( score / maxScore ) * 100 ).toFixed( 1 ) : 0;
		return `${score.toFixed( 2 )} / ${maxScore.toFixed( 2 )} (${percentage}%)`;
	}

	getUserAvatar( userId, userName ) {
		// For guest users, use "Guest" as the name for avatar generation.
		const name = 0 === userId ? 'Guest' : ( userName || 'User' );
		return `https://ui-avatars.com/api/?name=${encodeURIComponent( name )}&background=7e75ff&color=fff&size=40`;
	}

	getRowActions( attempt ) {
		const { onViewAttempt, onVerifyAttempt, onDeleteAttempt } = this.props;
		const actions = [];

		// View action.
		actions.push( {
			id: 'view',
			label: this.safeTranslate( __( 'View', 'skillpulse-lms' ) ),
			onClick: () => onViewAttempt( attempt.id ),
		} );


		// Delete action.
		actions.push( {
			id: 'delete',
			label: this.safeTranslate( __( 'Delete', 'skillpulse-lms' ) ),
			className: 'delete',
			onClick: () => onDeleteAttempt( attempt.id ),
		} );

		return actions;
	}

	getBulkActions() {
		return [
			{ value: 'verify', label: this.safeTranslate( __( 'Verify', 'skillpulse-lms' ) ) },
			{ value: 'delete', label: this.safeTranslate( __( 'Delete', 'skillpulse-lms' ) ) },
		];
	}

	getColumns() {
		return [
			{
				id: 'id',
				label: __( 'ID', 'skillpulse-lms' ),
				sortable: true,
				width: '80px',
				render: ( attempt ) => <strong>#{attempt.id}</strong>,
			},
			{
				id: 'user_id',
				label: __( 'User', 'skillpulse-lms' ),
				sortable: true,
				render: ( attempt ) => {
					const userName = 0 === attempt.user_id
						? this.safeTranslate( __( 'Guest', 'skillpulse-lms' ) )
						: ( attempt.user_name || ( attempt.user ? attempt.user.display_name : null ) || `User ${attempt.user_id}` );
					const userEmail = attempt.user_email || ( attempt.user ? attempt.user.user_email : '' );

					return (
						<>
							{0 === attempt.user_id ? (
								<strong>{userName}</strong>
							) : attempt.user ? (
								<strong>
									<a
										href={`${window.SPLMSCore_Data?.adminUrl || '/wp-admin'}/user-edit.php?user_id=${attempt.user_id}`}
										target="_blank"
										rel="noopener noreferrer"
										className="splms-user-link"
										title={this.safeTranslate( __( 'Edit User', 'skillpulse-lms' ) )}
										onClick={( e ) => e.stopPropagation()}
									>
										{userName}
									</a>
								</strong>
							) : (
								<strong>{userName}</strong>
							)}
							{userEmail && (
								<>
									<br />
									<small className="splms-user-email">{userEmail}</small>
								</>
							)}
						</>
					);
				},
			},
			{
				id: 'course_id',
				label: __( 'Course', 'skillpulse-lms' ),
				sortable: true,
				render: ( attempt ) => {
					if ( attempt.course ) {
						return (
							<a
								href={`${window.SPLMSCore_Data?.adminUrl || '/wp-admin'}/post.php?post=${attempt.course_id}&action=edit`}
								target="_blank"
								rel="noopener noreferrer"
								className="splms-course-link"
								title={this.safeTranslate( __( 'Edit Course', 'skillpulse-lms' ) )}
							>
								<strong>{attempt.course.post_title}</strong>
								<span className="splms-link-icon">↗</span>
							</a>
						);
					}
					return <strong>{`Course ${attempt.course_id}`}</strong>;
				},
			},
			{
				id: 'quiz_id',
				label: __( 'Quiz', 'skillpulse-lms' ),
				sortable: true,
				render: ( attempt ) => {
					if ( attempt.quiz ) {
						return (
							<a
								href={`${window.SPLMSCore_Data?.adminUrl || '/wp-admin'}/post.php?post=${attempt.quiz_id}&action=edit`}
								target="_blank"
								rel="noopener noreferrer"
								className="splms-course-link"
								title={this.safeTranslate( __( 'Edit Quiz', 'skillpulse-lms' ) )}
							>
								<strong>{attempt.quiz.post_title}</strong>
								<span className="splms-link-icon">↗</span>
							</a>
						);
					}
					return <strong>{`Quiz ${attempt.quiz_id}`}</strong>;
				},
			},
			{
				id: 'score',
				label: __( 'Score', 'skillpulse-lms' ),
				sortable: true,
				render: ( attempt ) => <strong>{this.formatScore( attempt.score, attempt.max_score )}</strong>,
			},
			{
				id: 'status',
				label: __( 'Status', 'skillpulse-lms' ),
				sortable: true,
				render: ( attempt ) => {
					const status = attempt.status || 'draft';
					let statusClass = 'splms-status-draft';
					let statusLabel = 'Draft';

					switch ( status ) {
						case 'in_progress':
							statusClass = 'splms-status-in-progress';
							statusLabel = this.safeTranslate( __( 'In Progress', 'skillpulse-lms' ) );
							break;
						case 'submitted':
							statusClass = 'splms-status-submitted';
							statusLabel = this.safeTranslate( __( 'Submitted', 'skillpulse-lms' ) );
							break;
						case 'pending_review':
							statusClass = 'splms-status-pending';
							statusLabel = this.safeTranslate( __( 'Pending Review', 'skillpulse-lms' ) );
							break;
						case 'graded':
							// For graded, show passed/failed based on score.
							const passed = attempt.percentage >= ( attempt.passing_grade || 70 );
							statusClass = passed ? 'splms-status-passed' : 'splms-status-failed';
							statusLabel = passed
								? this.safeTranslate( __( 'Passed', 'skillpulse-lms' ) )
								: this.safeTranslate( __( 'Failed', 'skillpulse-lms' ) );
							break;
						case 'expired':
							statusClass = 'splms-status-expired';
							statusLabel = this.safeTranslate( __( 'Expired', 'skillpulse-lms' ) );
							break;
						case 'requires_resubmission':
							statusClass = 'splms-status-resubmit';
							statusLabel = this.safeTranslate( __( 'Requires Resubmission', 'skillpulse-lms' ) );
							break;
						default:
							statusLabel = this.safeTranslate( __( 'Draft', 'skillpulse-lms' ) );
					}

					return (
						<span className={statusClass}>
							{statusLabel}
						</span>
					);
				},
			},
			{
				id: 'attempt_time',
				label: __( 'Attempt Time', 'skillpulse-lms' ),
				sortable: true,
				render: ( attempt ) => dateI18n( 'Y-m-d H:i', attempt.attempt_time ),
			},
			{
				id: 'time_taken',
				label: __( 'Time Taken', 'skillpulse-lms' ),
				render: ( attempt ) => this.formatTime( attempt.time_taken ),
			},
		];
	}

	getFilters() {
		const { filters = {}, onFilterChange } = this.props;

		return [
			{
				id: 'search',
				type: 'search',
				flex: 1,
				value: filters.search || '',
				placeholder: __( 'Search by user, course, or quiz...', 'skillpulse-lms' ),
				onChange: ( value ) => onFilterChange( 'search', value ),
			},
			{
				id: 'passed',
				type: 'select',
				value: filters.passed || '',
				options: [
					{ label: __( 'All', 'skillpulse-lms' ), value: '' },
					{ label: __( 'Passed', 'skillpulse-lms' ), value: '1' },
					{ label: __( 'Failed', 'skillpulse-lms' ), value: '0' },
				],
				onChange: ( value ) => onFilterChange( 'passed', value ),
			},
			{
				id: 'pending_review',
				type: 'select',
				value: filters.pending_review || '',
				options: [
					{ label: __( 'All', 'skillpulse-lms' ), value: '' },
					{ label: __( 'Pending Review', 'skillpulse-lms' ), value: '1' },
					{ label: __( 'Graded', 'skillpulse-lms' ), value: '0' },
				],
				onChange: ( value ) => onFilterChange( 'pending_review', value ),
			},
		];
	}

	render() {
		const {
			attempts = [],
			pagination = {},
			isLoading,
			onApplyFilters,
			onViewAttempt,
			sortBy,
			sortOrder,
			onSortChange,
			onPageChange,
		} = this.props;

		return (
			<SPLMS_ListView
				items={attempts}
				totalItems={pagination.total || 0}
				isLoading={isLoading}
				columns={this.getColumns()}
				filters={this.getFilters()}
				sortBy={sortBy}
				sortOrder={sortOrder}
				onSortChange={onSortChange}
				currentPage={pagination.page || 1}
				perPage={pagination.per_page || 20}
				onPageChange={onPageChange}
				onApplyFilters={onApplyFilters}
				onRowClick={( attempt ) => onViewAttempt( attempt.id )}
				selectable={true}
				bulkActions={this.getBulkActions()}
				onBulkAction={this.handleBulkAction}
				rowActions={( attempt ) => this.getRowActions( attempt )}
				primaryColumn="user_id"
				emptyIcon="forms"
				emptyTitle={__( 'No quiz attempts found', 'skillpulse-lms' )}
				emptyMessage={__( 'Try adjusting your filters', 'skillpulse-lms' )}
			/>
		);
	}
}

export default List;
