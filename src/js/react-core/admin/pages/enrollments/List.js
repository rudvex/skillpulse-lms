/**
 * List component for displaying enrollments using SPLMS_ListView
 *
 * REFACTORED VERSION - Uses reusable SPLMS_ListView component
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { SPLMS_ListView } from '../../../components/admin/ListView';
import { StatusBadge, MethodBadge, ProgressBar } from './components/EnrollmentBadges';
import { SplmsIcon } from '../../../components/SplmsIcon';
import { getAdminUrl } from '../../../utility/url';

class List extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			filters: {
				search: '',
				course: '',
				status: '',
				user: '',
				method: '',
				dateStart: '',
				dateEnd: '',
			},
			sortBy: 'enrolled_at',
			sortOrder: 'desc',
		};
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

	handleFilterChange = ( filters ) => {
		this.setState( { filters }, () => {
			if ( this.props.onFilterChange ) {
				this.props.onFilterChange( filters );
			}
		} );
	};

	handleSortChange = ( sortBy, sortOrder ) => {
		this.setState( { sortBy, sortOrder }, () => {
			if ( this.props.onSortChange ) {
				this.props.onSortChange( sortBy, sortOrder );
			}
		} );
	};

	getColumns() {
		return [
			{
				id: 'id',
				label: __( 'ID', 'skillpulse-lms' ),
				sortable: true,
				width: '80px',
				render: ( enrollment ) => <strong>#{enrollment.id}</strong>,
			},
			{
				id: 'student',
				label: __( 'Student', 'skillpulse-lms' ),
				sortable: true,
				render: ( enrollment ) => (
					<>
						<a
							href={`${getAdminUrl()}/user-edit.php?user_id=${enrollment.user_id}`}
							target="_blank"
							rel="noopener noreferrer"
							className="splms-user-link"
							title={this.safeTranslate( __( 'Edit User', 'skillpulse-lms' ) )}
							onClick={( e ) => e.stopPropagation()}
						>
							<strong>{enrollment.user_name}</strong>
						</a>
						{enrollment.user_email && (
							<>
								<br />
								<small className="splms-user-email">{enrollment.user_email}</small>
							</>
						)}
					</>
				),
			},
			{
				id: 'course',
				label: __( 'Course', 'skillpulse-lms' ),
				sortable: true,
				render: ( enrollment ) => (
					<a
						href={`${getAdminUrl()}/post.php?post=${enrollment.course_id}&action=edit`}
						target="_blank"
						rel="noopener noreferrer"
						className="splms-course-link"
						title={this.safeTranslate( __( 'Edit Course', 'skillpulse-lms' ) )}
						onClick={( e ) => e.stopPropagation()}
					>
						<strong>{enrollment.course_title}</strong>
						<span className="splms-link-icon">↗</span>
					</a>
				),
			},
			{
				id: 'enrolled_at',
				label: __( 'Enrollment Date', 'skillpulse-lms' ),
				sortable: true,
				render: ( enrollment ) => dateI18n( 'M j, Y', enrollment.enrolled_at ),
			},
			{
				id: 'method',
				label: __( 'Method', 'skillpulse-lms' ),
				sortable: true,
				render: ( enrollment ) => <MethodBadge method={enrollment.enrollment_method} />,
			},
			{
				id: 'status',
				label: __( 'Status', 'skillpulse-lms' ),
				sortable: false,
				render: ( enrollment ) => <StatusBadge status={enrollment.status} />,
			},
			{
				id: 'progress',
				label: __( 'Progress', 'skillpulse-lms' ),
				sortable: false,
				render: ( enrollment ) => <ProgressBar progress={enrollment.progress} />,
			},
			{
				id: 'completed_at',
				label: __( 'Completion Date', 'skillpulse-lms' ),
				sortable: true,
				render: ( enrollment ) =>
					enrollment.completed_at ? dateI18n( 'M j, Y', enrollment.completed_at ) : '—',
			},
		];
	}

	getFilters() {
		const { courses = [], users = [] } = this.props;

		return [
			{
				id: 'search',
				type: 'search',
				placeholder: __( 'Search enrollments...', 'skillpulse-lms' ),
			},
			{
				id: 'course',
				type: 'select',
				label: __( 'Course', 'skillpulse-lms' ),
				options: [
					{ value: '', label: __( 'All Courses', 'skillpulse-lms' ) },
					...courses.map( ( course ) => ( {
						value: course.id.toString(),
						label: course.title?.rendered || course.title || `Course ${course.id}`,
					} ) ),
				],
			},
			{
				id: 'status',
				type: 'select',
				label: __( 'Status', 'skillpulse-lms' ),
				options: [
					{ value: '', label: __( 'All Statuses', 'skillpulse-lms' ) },
					{ value: 'active', label: __( 'Active', 'skillpulse-lms' ) },
					{ value: 'completed', label: __( 'Completed', 'skillpulse-lms' ) },
					{ value: 'suspended', label: __( 'Suspended', 'skillpulse-lms' ) },
					{ value: 'cancelled', label: __( 'Cancelled', 'skillpulse-lms' ) },
				],
			},
			{
				id: 'user',
				type: 'select',
				label: __( 'Student', 'skillpulse-lms' ),
				options: [
					{ value: '', label: __( 'All Students', 'skillpulse-lms' ) },
					...users.map( ( user ) => ( {
						value: user.id.toString(),
						label: user.name || `User ${user.id}`,
					} ) ),
				],
			},
			{
				id: 'method',
				type: 'select',
				label: __( 'Enrollment Method', 'skillpulse-lms' ),
				options: [
					{ value: '', label: __( 'All Methods', 'skillpulse-lms' ) },
					{ value: 'manual', label: __( 'Manual', 'skillpulse-lms' ) },
					{ value: 'purchase', label: __( 'Purchase', 'skillpulse-lms' ) },
					{ value: 'self', label: __( 'Self Enrollment', 'skillpulse-lms' ) },
					{ value: 'admin', label: __( 'Admin Enrolled', 'skillpulse-lms' ) },
				],
			},
		];
	}

	getRowActions( enrollment ) {
		const { onView, onDelete, onGenerateCertificate } = this.props;
		const actions = [];

		if ( onView ) {
			actions.push( {
				id: 'view',
				label: this.safeTranslate( __( 'View Details', 'skillpulse-lms' ) ),
				onClick: () => onView( enrollment ),
			} );
		}

		if ( onGenerateCertificate && ( 'completed' === enrollment.status || enrollment.progress >= 100 || enrollment.completed_at ) && ! enrollment.certificate_earned ) {
			actions.push( {
				id: 'generate_certificate',
				label: this.safeTranslate( __( 'Generate Certificate', 'skillpulse-lms' ) ),
				onClick: () => onGenerateCertificate( enrollment ),
			} );
		}

		if ( onDelete ) {
			actions.push( {
				id: 'delete',
				label: this.safeTranslate( __( 'Delete', 'skillpulse-lms' ) ),
				onClick: () => onDelete( enrollment ),
				className: 'delete',
			} );
		}

		return actions;
	}

	getBulkActions() {
		return [
			{ value: 'activate', label: __( 'Mark as Active', 'skillpulse-lms' ) },
			{ value: 'complete', label: __( 'Mark as Completed', 'skillpulse-lms' ) },
			{ value: 'suspend', label: __( 'Suspend', 'skillpulse-lms' ) },
			{ value: 'cancel', label: __( 'Cancel', 'skillpulse-lms' ) },
			{ value: 'delete', label: __( 'Delete', 'skillpulse-lms' ) },
			{ value: 'export', label: __( 'Export Selected', 'skillpulse-lms' ) },
			{ value: 'send_reminder', label: __( 'Send Reminder Email', 'skillpulse-lms' ) },
		];
	}

	render() {
		const {
			enrollments = [],
			isLoading = false,
			pagination = {},
			onBulkAction,
			onPageChange,
			onPerPageChange,
		} = this.props;

		const { sortBy, sortOrder } = this.state;

		return (
			<SPLMS_ListView
				title={__( 'Enrollments', 'skillpulse-lms' )}
				items={enrollments}
				totalItems={pagination.total || 0}
				columns={this.getColumns()}
				filters={this.getFilters()}
				bulkActions={this.getBulkActions()}
				rowActions={( enrollment ) => this.getRowActions( enrollment )}
				primaryColumn="student"
				selectable={true}
				isLoading={isLoading}
				sortBy={sortBy}
				sortOrder={sortOrder}
				onSortChange={this.handleSortChange}
				onFilterChange={this.handleFilterChange}
				onBulkAction={onBulkAction}
				currentPage={pagination.currentPage || 1}
				perPage={pagination.perPage || 20}
				onPageChange={onPageChange}
				onPerPageChange={onPerPageChange}
				emptyStateIcon="groups"
				emptyStateTitle={__( 'No enrollments found', 'skillpulse-lms' )}
				emptyStateMessage={__( 'There are no course enrollments to display.', 'skillpulse-lms' )}
			/>
		);
	}
}

export default List;
