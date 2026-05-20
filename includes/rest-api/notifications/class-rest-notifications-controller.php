<?php
/**
 * REST API Notifications Controller (Frontend)
 *
 * Handles REST API endpoints for frontend notification operations.
 * Provides endpoints for authenticated users to manage their own notifications.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/notifications              - Get current user's notifications (with filters)
 * GET    /splms/v1/notifications/{id}         - Get single notification
 * PATCH  /splms/v1/notifications/{id}/read     - Mark notification as read
 * PATCH  /splms/v1/notifications/{id}/unread  - Mark notification as unread
 * POST   /splms/v1/notifications/mark-all-read - Mark all notifications as read
 * DELETE /splms/v1/notifications/{id}         - Delete notification
 * POST   /splms/v1/notifications/bulk-action  - Perform bulk operations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Notifications Controller class (Frontend).
 *
 * @since 1.0.0
 */
class SPLMS_REST_Notifications_Controller extends WP_REST_Controller {

	/**
	 * Notifications query instance.
	 *
	 * @since 1.0.0
	 *
	 * @var SPLMS_Notifications_Query
	 */
	protected $query;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'notifications';
		$this->query     = SPLMS_Notifications_Query::get_instance();
	}

	/**
	 * Register the routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Get all notifications for current user.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_notifications' ),
					'permission_callback' => array( $this, 'check_user_permissions' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		// Mark all as read.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/mark-all-read',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'mark_all_read' ),
					'permission_callback' => array( $this, 'check_user_permissions' ),
					'args'                => array(
						'event_key' => array(
							'description' => __( 'Optional: Filter by event key.', 'skillpulse-lms' ),
							'type'        => 'string',
							'required'    => false,
						),
					),
				),
			)
		);

		// Bulk actions.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-action',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'bulk_action' ),
					'permission_callback' => array( $this, 'check_user_permissions' ),
					'args'                => array(
						'notification_ids' => array(
							'description' => __( 'Array of notification IDs.', 'skillpulse-lms' ),
							'type'        => 'array',
							'required'    => true,
							'items'       => array(
								'type' => 'integer',
							),
						),
						'action'           => array(
							'description' => __( 'Action to perform: mark_read, mark_unread, or delete.', 'skillpulse-lms' ),
							'type'        => 'string',
							'required'    => true,
							'enum'        => array( 'mark_read', 'mark_unread', 'delete' ),
						),
					),
				),
			)
		);

		// Single notification operations.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_notification' ),
					'permission_callback' => array( $this, 'check_user_permissions' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Notification ID.', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_notification' ),
					'permission_callback' => array( $this, 'check_user_permissions' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Notification ID.', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
			)
		);

		// Mark as read.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/read',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'mark_read' ),
					'permission_callback' => array( $this, 'check_user_permissions' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Notification ID.', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
			)
		);

		// Mark as unread.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/unread',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'mark_unread' ),
					'permission_callback' => array( $this, 'check_user_permissions' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Notification ID.', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
			)
		);
	}

	/**
	 * Get item schema for notifications.
	 *
	 * @since 1.0.0
	 *
	 * @return array Schema array.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'notification',
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'description' => __( 'Unique identifier for the notification.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'user_id'      => array(
					'description' => __( 'The ID of the user who owns this notification.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'event_key'    => array(
					'description' => __( 'The event key that triggered this notification.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'title'        => array(
					'description' => __( 'The notification title.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'message'      => array(
					'description' => __( 'The notification message content.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'type'         => array(
					'description' => __( 'The notification type.', 'skillpulse-lms' ),
					'type'        => 'string',
					'enum'        => array( 'info', 'success', 'warning', 'error' ),
					'context'     => array( 'view', 'edit' ),
					'default'     => 'info',
				),
				'is_read'      => array(
					'description' => __( 'Whether the notification has been read.', 'skillpulse-lms' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'default'     => false,
				),
				'read_at'      => array(
					'description' => __( 'The date and time when the notification was read.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'course_id'    => array(
					'description' => __( 'The ID of the related course, if applicable.', 'skillpulse-lms' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit' ),
				),
				'related_id'   => array(
					'description' => __( 'The ID of a related object (e.g., quiz, lesson).', 'skillpulse-lms' ),
					'type'        => array( 'integer', 'null' ),
					'context'     => array( 'view', 'edit' ),
				),
				'related_type' => array(
					'description' => __( 'The type of the related object.', 'skillpulse-lms' ),
					'type'        => array( 'string', 'null' ),
					'context'     => array( 'view', 'edit' ),
				),
				'meta'         => array(
					'description' => __( 'Additional metadata for the notification.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
				),
				'created_at'   => array(
					'description' => __( 'The date and time when the notification was created.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get collection parameters for notifications.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		return array(
			'page'      => array(
				'description' => __( 'Current page of the collection.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'per_page'  => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 20,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'is_read'   => array(
				'description' => __( 'Filter by read status (0 or 1).', 'skillpulse-lms' ),
				'type'        => 'integer',
				'enum'        => array( 0, 1 ),
			),
			'event_key' => array(
				'description' => __( 'Filter by event key.', 'skillpulse-lms' ),
				'type'        => 'string',
			),
			'type'      => array(
				'description' => __( 'Filter by notification type.', 'skillpulse-lms' ),
				'type'        => 'string',
				'enum'        => array( 'info', 'success', 'warning', 'error' ),
			),
			'orderby'   => array(
				'description' => __( 'Sort collection by object attribute.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'created_at',
				'enum'        => array( 'id', 'created_at', 'is_read', 'event_key' ),
			),
			'order'     => array(
				'description' => __( 'Order sort attribute ascending or descending.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'DESC',
				'enum'        => array( 'ASC', 'DESC' ),
			),
			'search'    => array(
				'description' => __( 'Search in title and message.', 'skillpulse-lms' ),
				'type'        => 'string',
			),
		);
	}

	/**
	 * Check user permissions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if user is logged in, WP_Error otherwise.
	 */
	public function check_user_permissions( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to access notifications.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Check notification ownership.
	 *
	 * @since 1.0.0
	 *
	 * @param int $notification_id Notification ID.
	 * @param int $user_id         User ID.
	 * @return bool|WP_Error True if user owns the notification, WP_Error otherwise.
	 */
	private function check_notification_ownership( $notification_id, $user_id ) {
		$notification = $this->query->get_notification( $notification_id );

		if ( ! $notification ) {
			return new WP_Error(
				'rest_notification_not_found',
				__( 'Notification not found.', 'skillpulse-lms' ),
				array( 'status' => 404 )
			);
		}

		if ( (int) $notification->user_id !== (int) $user_id ) {
			return new WP_Error(
				'rest_notification_access_denied',
				__( 'You do not have permission to access this notification.', 'skillpulse-lms' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Format notification for response.
	 *
	 * @since 1.0.0
	 *
	 * @param object $notification Notification object.
	 * @return array Formatted notification array.
	 */
	private function format_notification( $notification ) {
		return array(
			'id'           => (string) $notification->id,
			'user_id'      => (int) $notification->user_id,
			'event_key'    => $notification->event_key,
			'title'        => $notification->title,
			'message'      => $notification->message,
			'type'         => $notification->type,
			'is_read'      => (bool) $notification->is_read,
			'read_at'      => $notification->read_at,
			'course_id'    => $notification->course_id ? (int) $notification->course_id : null,
			'related_id'   => $notification->related_id ? (int) $notification->related_id : null,
			'related_type' => $notification->related_type,
			'meta'         => $notification->meta ? json_decode( $notification->meta, true ) : null,
			'created_at'   => $notification->created_at,
		);
	}

	/**
	 * Get notifications for current user.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_notifications( $request ) {
		$user_id = get_current_user_id();

		$args = array(
			'user_id'   => $user_id,
			'event_key' => $request->get_param( 'event_key' ),
			'is_read'   => $request->get_param( 'is_read' ),
			'type'      => $request->get_param( 'type' ),
			'orderby'   => $request->get_param( 'orderby' ),
			'order'     => $request->get_param( 'order' ),
		);

		// Pagination.
		$per_page = $request->get_param( 'per_page' );
		$page     = $request->get_param( 'page' );
		$offset   = ( $page - 1 ) * $per_page;

		$args['limit']  = $per_page;
		$args['offset'] = $offset;

		// Search functionality.
		$search = $request->get_param( 'search' );
		if ( ! empty( $search ) ) {
			// We'll need to filter results after fetching since search isn't built into the query class.
			// For now, we'll fetch all user notifications and filter in PHP.
			// In a production environment, you might want to add search to the query class.
			$all_notifications = $this->query->get_notifications(
				array(
					'user_id' => $user_id,
					'limit'   => 1000, // Large limit for search.
					'offset'  => 0,
				)
			);

			$search_lower = strtolower( $search );
			$filtered     = array_filter(
				$all_notifications,
				function ( $notification ) use ( $search_lower ) {
					return (
						false !== strpos( strtolower( $notification->title ), $search_lower ) ||
						false !== strpos( strtolower( $notification->message ), $search_lower )
					);
				}
			);

			// Apply additional filters.
			if ( isset( $args['event_key'] ) && $args['event_key'] ) {
				$filtered = array_filter(
					$filtered,
					function ( $notification ) use ( $args ) {
						return $notification->event_key === $args['event_key'];
					}
				);
			}

			if ( isset( $args['is_read'] ) && null !== $args['is_read'] ) {
				$is_read  = (bool) $args['is_read'];
				$filtered = array_filter(
					$filtered,
					function ( $notification ) use ( $is_read ) {
						return (bool) $notification->is_read === $is_read;
					}
				);
			}

			if ( isset( $args['type'] ) && $args['type'] ) {
				$filtered = array_filter(
					$filtered,
					function ( $notification ) use ( $args ) {
						return $notification->type === $args['type'];
					}
				);
			}

			// Sort.
			$orderby = $args['orderby'] ?? 'created_at';
			$order   = $args['order'] ?? 'DESC';
			usort(
				$filtered,
				function ( $a, $b ) use ( $orderby, $order ) {
					$value_a = $a->$orderby ?? '';
					$value_b = $b->$orderby ?? '';

					if ( 'DESC' === $order ) {
						return $value_b <=> $value_a;
					}
					return $value_a <=> $value_b;
				}
			);

			// Paginate.
			$total         = count( $filtered );
			$notifications = array_slice( $filtered, $offset, $per_page );
		} else {
			// Remove null values.
			$args = array_filter(
				$args,
				function ( $value ) {
					return null !== $value;
				}
			);

			$notifications = $this->query->get_notifications( $args );

			// Get total count for pagination.
			$total_args = $args;
			unset( $total_args['limit'], $total_args['offset'] );
			$stats = $this->query->get_stats( $total_args );
			$total = $stats['total'];
		}

		// Format notifications for response.
		$formatted = array();
		foreach ( $notifications as $notification ) {
			$formatted[] = $this->format_notification( $notification );
		}

		$response = rest_ensure_response(
			array(
				'success'  => true,
				'data'     => $formatted,
				'total'    => isset( $total ) ? $total : count( $filtered ),
				'pages'    => isset( $total ) ? ceil( $total / $per_page ) : ceil( count( $filtered ) / $per_page ),
				'page'     => $page,
				'per_page' => $per_page,
			)
		);

		return $response;
	}

	/**
	 * Get single notification.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_notification( $request ) {
		$user_id         = get_current_user_id();
		$notification_id = (int) $request->get_param( 'id' );

		// Check ownership.
		$ownership_check = $this->check_notification_ownership( $notification_id, $user_id );
		if ( is_wp_error( $ownership_check ) ) {
			return $ownership_check;
		}

		$notification = $this->query->get_notification( $notification_id );

		$response = rest_ensure_response(
			array(
				'success' => true,
				'data'    => $this->format_notification( $notification ),
			)
		);

		return $response;
	}


	/**
	 * Mark notification as read.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function mark_read( $request ) {
		$user_id         = get_current_user_id();
		$notification_id = (int) $request->get_param( 'id' );

		// Check ownership.
		$ownership_check = $this->check_notification_ownership( $notification_id, $user_id );
		if ( is_wp_error( $ownership_check ) ) {
			return $ownership_check;
		}

		$result = $this->query->mark_read( $notification_id, $user_id );

		if ( false === $result ) {
			return new WP_Error(
				'rest_update_failed',
				__( 'Failed to update notification.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}

		$notification = $this->query->get_notification( $notification_id );

		$response = rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'id'      => $notification->id,
					'is_read' => (bool) $notification->is_read,
					'read_at' => $notification->read_at,
				),
				'message' => __( 'Notification marked as read.', 'skillpulse-lms' ),
			)
		);

		return $response;
	}

	/**
	 * Mark notification as unread.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function mark_unread( $request ) {
		$user_id         = get_current_user_id();
		$notification_id = (int) $request->get_param( 'id' );

		// Check ownership.
		$ownership_check = $this->check_notification_ownership( $notification_id, $user_id );
		if ( is_wp_error( $ownership_check ) ) {
			return $ownership_check;
		}

		$result = $this->query->update_notification(
			$notification_id,
			array(
				'is_read' => 0,
				'read_at' => null,
			)
		);

		if ( false === $result ) {
			return new WP_Error(
				'rest_update_failed',
				__( 'Failed to update notification.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}

		$notification = $this->query->get_notification( $notification_id );

		$response = rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'id'      => $notification->id,
					'is_read' => (bool) $notification->is_read,
					'read_at' => $notification->read_at,
				),
				'message' => __( 'Notification marked as unread.', 'skillpulse-lms' ),
			)
		);

		return $response;
	}

	/**
	 * Mark all notifications as read.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function mark_all_read( $request ) {
		$user_id = get_current_user_id();

		$event_key = $request->get_param( 'event_key' );

		if ( $event_key ) {
			// Mark all unread notifications for this event as read.
			$args = array(
				'user_id'   => $user_id,
				'event_key' => $event_key,
				'is_read'   => false,
				'limit'     => 1000,
			);

			$notifications = $this->query->get_notifications( $args );
			$updated_count = 0;

			foreach ( $notifications as $notification ) {
				if ( $this->query->mark_read( $notification->id, $user_id ) ) {
					++$updated_count;
				}
			}
		} else {
			// Mark all unread notifications as read.
			$result        = $this->query->mark_all_read( $user_id );
			$updated_count = false !== $result ? $result : 0;
		}

		$response = rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'updated_count' => $updated_count,
				),
				'message' => __( 'All notifications marked as read.', 'skillpulse-lms' ),
			)
		);

		return $response;
	}

	/**
	 * Delete notification.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_notification( $request ) {
		$user_id         = get_current_user_id();
		$notification_id = (int) $request->get_param( 'id' );

		// Check ownership.
		$ownership_check = $this->check_notification_ownership( $notification_id, $user_id );
		if ( is_wp_error( $ownership_check ) ) {
			return $ownership_check;
		}

		$result = $this->query->delete_notification( $notification_id, $user_id );

		if ( false === $result ) {
			return new WP_Error(
				'rest_delete_failed',
				__( 'Failed to delete notification.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}

		$response = rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'deleted' => true,
					'id'      => $notification_id,
				),
				'message' => __( 'Notification deleted successfully.', 'skillpulse-lms' ),
			)
		);

		return $response;
	}

	/**
	 * Perform bulk operations on notifications.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function bulk_action( $request ) {
		$user_id = get_current_user_id();

		$data = $request->get_json_params();
		if ( ! $data ) {
			$data = $request->get_params();
		}

		if ( ! isset( $data['action'] ) || ! isset( $data['notification_ids'] ) || ! is_array( $data['notification_ids'] ) ) {
			return new WP_Error(
				'rest_invalid_request',
				__( 'Action and notification_ids are required.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		$action           = sanitize_text_field( $data['action'] );
		$notification_ids = array_map( 'absint', $data['notification_ids'] );

		// Verify all notifications belong to the user.
		foreach ( $notification_ids as $notification_id ) {
			$ownership_check = $this->check_notification_ownership( $notification_id, $user_id );
			if ( is_wp_error( $ownership_check ) ) {
				return $ownership_check;
			}
		}

		$updated_count = 0;

		foreach ( $notification_ids as $notification_id ) {
			$result = false;

			switch ( $action ) {
				case 'mark_read':
					$result = $this->query->mark_read( $notification_id, $user_id );
					break;

				case 'mark_unread':
					$result = $this->query->update_notification(
						$notification_id,
						array(
							'is_read' => 0,
							'read_at' => null,
						)
					);
					break;

				case 'delete':
					$result = $this->query->delete_notification( $notification_id, $user_id );
					break;
			}

			if ( false !== $result ) {
				++$updated_count;
			}
		}

		$response = rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'updated_count' => $updated_count,
					'action'        => $action,
				),
				'message' => __( 'Bulk action completed successfully.', 'skillpulse-lms' ),
			)
		);

		return $response;
	}
}
