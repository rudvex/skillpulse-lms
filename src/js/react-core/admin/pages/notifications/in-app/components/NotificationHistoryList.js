/**
 * Notification History List component using SPLMS_ListView
 *
 * Refactored from custom table implementation to use standardized ListView component
 * for consistency with other admin pages (enrollments, orders, quiz attempts)
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { SPLMS_ListView } from '../../../../../components/admin/ListView';
import { SplmsIcon } from '../../../../../components/SplmsIcon';
import { fetchNotifications, bulkActionNotifications, deleteNotification } from '../api';

class NotificationHistoryList extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			notifications: [],
			isLoading: true,
			filters: {
				user_id: '',
				event_key: '',
				is_read: '',
				search: ''
			},
			pagination: {
				currentPage: 1,
				perPage: 20,
				total: 0
			},
			sortBy: 'created_at',
			sortOrder: 'desc'
		};
	}

	componentDidMount() {
		this.fetchNotifications();
	}

	fetchNotifications = async () => {
		this.setState( { isLoading: true } );

		try {
			const { filters, pagination, sortBy, sortOrder } = this.state;

			const params = {
				page: pagination.currentPage,
				per_page: pagination.perPage,
				orderby: sortBy,
				order: sortOrder.toUpperCase(),
			};

			// Add filters
			if ( filters.user_id ) {
				params.user_id = parseInt( filters.user_id );
			}
			if ( filters.event_key ) {
				params.event_key = filters.event_key;
			}
			if ( filters.is_read !== '' ) {
				params.is_read = parseInt( filters.is_read );
			}

			const response = await fetchNotifications( params );

			if ( response.success ) {
				// Filter by search term if provided (client-side for now)
				let filteredData = response.data || [];
				if ( filters.search ) {
					const searchTerm = filters.search.toLowerCase();
					filteredData = filteredData.filter( notification =>
						notification.title?.toLowerCase().includes( searchTerm ) ||
						notification.message?.toLowerCase().includes( searchTerm ) ||
						notification.user_name?.toLowerCase().includes( searchTerm ) ||
						notification.event_key?.toLowerCase().includes( searchTerm )
					);
				}

				this.setState( {
					notifications: filteredData,
					pagination: {
						...pagination,
						total: response.total || 0
					},
					isLoading: false
				} );
			} else {
				throw new Error( response.message || __( 'Failed to fetch notifications', 'skillpulse-lms' ) );
			}
		} catch ( error ) {
			console.error( 'Error fetching notifications:', error );
			if ( window.skillpulseToast ) {
				window.skillpulseToast.error( error.message || __( 'Failed to load notifications', 'skillpulse-lms' ) );
			}
			this.setState( { isLoading: false } );
		}
	};

	handleFilterChange = ( newFilters ) => {
		this.setState( {
			filters: { ...this.state.filters, ...newFilters },
			pagination: { ...this.state.pagination, currentPage: 1 }
		}, () => {
			this.fetchNotifications();
		} );
	};

	handleSortChange = ( sortBy, sortOrder ) => {
		this.setState( {
			sortBy,
			sortOrder,
			pagination: { ...this.state.pagination, currentPage: 1 }
		}, () => {
			this.fetchNotifications();
		} );
	};

	handlePageChange = ( page ) => {
		this.setState( {
			pagination: { ...this.state.pagination, currentPage: page }
		}, () => {
			this.fetchNotifications();
		} );
	};

	handlePerPageChange = ( perPage ) => {
		this.setState( {
			pagination: { ...this.state.pagination, perPage, currentPage: 1 }
		}, () => {
			this.fetchNotifications();
		} );
	};

	handleBulkAction = async ( action, selectedItems ) => {
		if ( selectedItems.length === 0 ) {
			if ( window.skillpulseToast ) {
				window.skillpulseToast.error( __( 'Please select at least one notification', 'skillpulse-lms' ) );
			}
			return;
		}

		this.setState( { isLoading: true } );

		try {
			const response = await bulkActionNotifications( selectedItems, action );

			if ( response.success ) {
				const successMessage = response.message || __( 'Bulk action completed successfully', 'skillpulse-lms' );
				if ( window.skillpulseToast ) {
					window.skillpulseToast.success( successMessage );
				}
				// Refetch notifications
				await this.fetchNotifications();
			} else {
				throw new Error( response.message || __( 'Bulk action failed', 'skillpulse-lms' ) );
			}
		} catch ( error ) {
			console.error( 'Error performing bulk action:', error );
			if ( window.skillpulseToast ) {
				window.skillpulseToast.error( error.message || __( 'Failed to perform bulk action', 'skillpulse-lms' ) );
			}
			this.setState( { isLoading: false } );
		}
	};

	handleDeleteNotification = async ( notification ) => {
		if ( ! window.confirm( __( 'Are you sure you want to delete this notification?', 'skillpulse-lms' ) ) ) {
			return;
		}

		this.setState( { isLoading: true } );

		try {
			const response = await deleteNotification( notification.id );

			if ( response.success ) {
				if ( window.skillpulseToast ) {
					window.skillpulseToast.success( __( 'Notification deleted successfully', 'skillpulse-lms' ) );
				}
				await this.fetchNotifications();
			} else {
				throw new Error( response.message || __( 'Failed to delete notification', 'skillpulse-lms' ) );
			}
		} catch ( error ) {
			console.error( 'Error deleting notification:', error );
			if ( window.skillpulseToast ) {
				window.skillpulseToast.error( error.message || __( 'Failed to delete notification', 'skillpulse-lms' ) );
			}
			this.setState( { isLoading: false } );
		}
	};

	formatDate = ( dateString ) => {
		if ( ! dateString ) return '—';
		return dateI18n( 'M j, Y g:i A', dateString );
	};

	getStatusBadge = ( isRead ) => {
		return isRead ? (
			<span className="splms-status-badge splms-status-read">
				{__( 'Read', 'skillpulse-lms' )}
			</span>
		) : (
			<span className="splms-status-badge splms-status-unread">
				{__( 'Unread', 'skillpulse-lms' )}
			</span>
		);
	};

	getTypeBadge = ( type ) => {
		const typeMap = {
			'info': { className: 'splms-type-info', label: __( 'Info', 'skillpulse-lms' ) },
			'success': { className: 'splms-type-success', label: __( 'Success', 'skillpulse-lms' ) },
			'warning': { className: 'splms-type-warning', label: __( 'Warning', 'skillpulse-lms' ) },
			'error': { className: 'splms-type-error', label: __( 'Error', 'skillpulse-lms' ) }
		};

		const config = typeMap[type] || typeMap.info;
		return (
			<span className={`splms-type-badge ${config.className}`}>
				{config.label}
			</span>
		);
	};

	getEventBadge = ( eventKey ) => {
		if ( ! eventKey ) return '—';

		const eventMap = {
			'course_enrollment': __( 'Course Enrollment', 'skillpulse-lms' ),
			'course_completion': __( 'Course Completion', 'skillpulse-lms' ),
			'lesson_completion': __( 'Lesson Completion', 'skillpulse-lms' ),
			'quiz_completion': __( 'Quiz Completion', 'skillpulse-lms' ),
			'certificate_awarded': __( 'Certificate Awarded', 'skillpulse-lms' ),
			'review_reply': __( 'Review Reply', 'skillpulse-lms' ),
			'review_new': __( 'New Review', 'skillpulse-lms' )
		};

		return (
			<span className="splms-event-badge">
				{eventMap[eventKey] || eventKey}
			</span>
		);
	};

	getColumns() {
		return [
			{
				id: 'user',
				label: __( 'User', 'skillpulse-lms' ),
				sortable: true,
				render: ( notification ) => (
					<>
						<strong>{notification.user_name || `#${notification.user_id}`}</strong>
						{notification.user_email && (
							<>
								<br />
								<small className="splms-user-email">{notification.user_email}</small>
							</>
						)}
					</>
				),
			},
			{
				id: 'event_key',
				label: __( 'Event', 'skillpulse-lms' ),
				sortable: true,
				render: ( notification ) => this.getEventBadge( notification.event_key ),
			},
			{
				id: 'title',
				label: __( 'Title', 'skillpulse-lms' ),
				sortable: false,
				render: ( notification ) => (
					<strong>{notification.title || '—'}</strong>
				),
			},
			{
				id: 'message',
				label: __( 'Message', 'skillpulse-lms' ),
				sortable: false,
				render: ( notification ) => (
					<div className="splms-message-preview">
						{notification.message || '—'}
					</div>
				),
			},
			{
				id: 'type',
				label: __( 'Type', 'skillpulse-lms' ),
				sortable: false,
				render: ( notification ) => this.getTypeBadge( notification.type ),
			},
			{
				id: 'is_read',
				label: __( 'Status', 'skillpulse-lms' ),
				sortable: false,
				render: ( notification ) => this.getStatusBadge( notification.is_read ),
			},
			{
				id: 'created_at',
				label: __( 'Created', 'skillpulse-lms' ),
				sortable: true,
				render: ( notification ) => this.formatDate( notification.created_at ),
			}
		];
	}

	getFilters() {
		return [
			{
				id: 'search',
				type: 'search',
				placeholder: __( 'Search notifications...', 'skillpulse-lms' ),
			},
			{
				id: 'user_id',
				type: 'text',
				label: __( 'User ID', 'skillpulse-lms' ),
				placeholder: __( 'Enter User ID', 'skillpulse-lms' ),
			},
			{
				id: 'event_key',
				type: 'select',
				label: __( 'Event Type', 'skillpulse-lms' ),
				options: [
					{ value: '', label: __( 'All Events', 'skillpulse-lms' ) },
					{ value: 'course_enrollment', label: __( 'Course Enrollment', 'skillpulse-lms' ) },
					{ value: 'course_completion', label: __( 'Course Completion', 'skillpulse-lms' ) },
					{ value: 'lesson_completion', label: __( 'Lesson Completion', 'skillpulse-lms' ) },
					{ value: 'quiz_completion', label: __( 'Quiz Completion', 'skillpulse-lms' ) },
					{ value: 'certificate_awarded', label: __( 'Certificate Awarded', 'skillpulse-lms' ) },
					{ value: 'review_reply', label: __( 'Review Reply', 'skillpulse-lms' ) },
					{ value: 'review_new', label: __( 'New Review', 'skillpulse-lms' ) },
				],
			},
			{
				id: 'is_read',
				type: 'select',
				label: __( 'Status', 'skillpulse-lms' ),
				options: [
					{ value: '', label: __( 'All Statuses', 'skillpulse-lms' ) },
					{ value: '1', label: __( 'Read', 'skillpulse-lms' ) },
					{ value: '0', label: __( 'Unread', 'skillpulse-lms' ) },
				],
			},
		];
	}

	getRowActions( notification ) {
		const actions = [];

		// Delete action (content moderation only)
		actions.push( {
			id: 'delete',
			label: __( 'Delete', 'skillpulse-lms' ),
			className: 'delete',
			onClick: () => this.handleDeleteNotification( notification ),
		} );

		// Filter out any undefined or empty actions for robustness
		return actions.filter( action =>
			action &&
			action.id &&
			action.label &&
			typeof action.onClick === 'function'
		);
	}

	getBulkActions() {
		return [
			{ value: 'delete', label: __( 'Delete', 'skillpulse-lms' ) },
		];
	}

	render() {
		const { notifications, isLoading, pagination, sortBy, sortOrder } = this.state;

		return (
			<SPLMS_ListView
				title={__( 'Notification History', 'skillpulse-lms' )}
				items={notifications}
				totalItems={pagination.total}
				columns={this.getColumns()}
				filters={this.getFilters()}
				bulkActions={this.getBulkActions()}
				rowActions={( notification ) => this.getRowActions( notification )}
				primaryColumn="title"
				selectable={true}
				isLoading={isLoading}
				sortBy={sortBy}
				sortOrder={sortOrder}
				onSortChange={this.handleSortChange}
				onFilterChange={this.handleFilterChange}
				onBulkAction={this.handleBulkAction}
				currentPage={pagination.currentPage}
				perPage={pagination.perPage}
				onPageChange={this.handlePageChange}
				onPerPageChange={this.handlePerPageChange}
				emptyStateIcon="bell"
				emptyStateTitle={__( 'No notifications found', 'skillpulse-lms' )}
				emptyStateMessage={__( 'There are no notifications to display. Adjust your filters or check back later.', 'skillpulse-lms' )}
			/>
		);
	}
}

export default NotificationHistoryList;