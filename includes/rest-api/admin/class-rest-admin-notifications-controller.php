<?php
/**
 * REST API Notifications Controller
 *
 * Handles REST API endpoints for in-app notification management.
 * Provides endpoints for getting notifications, templates, bulk operations, and user notifications.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/notifications - Get all notifications (with filters)
 * GET    /splms/v1/notifications/templates - Get in-app notification templates
 * POST   /splms/v1/notifications/templates - Save in-app notification template
 * POST   /splms/v1/notifications/bulk-action - Perform bulk operations
 * DELETE /splms/v1/notifications/{id} - Delete notification
 * GET    /splms/v1/notifications/users/{user_id} - Get user notifications
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Notifications Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Admin_Notifications_Controller extends WP_REST_Controller {

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
	}

	/**
	 * Register the routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_notifications' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/templates',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_templates' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_template' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk-action',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'bulk_action' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_notification' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
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

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/users/(?P<user_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_user_notifications' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
					'args'                => array(
						'user_id' => array(
							'description' => __( 'User ID.', 'skillpulse-lms' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
			)
		);
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
			'user_id'   => array(
				'description' => __( 'Filter by user ID.', 'skillpulse-lms' ),
				'type'        => 'integer',
			),
			'event_key' => array(
				'description' => __( 'Filter by event key.', 'skillpulse-lms' ),
				'type'        => 'string',
			),
			'is_read'   => array(
				'description' => __( 'Filter by read status (0 or 1).', 'skillpulse-lms' ),
				'type'        => 'integer',
				'enum'        => array( 0, 1 ),
			),
			'course_id' => array(
				'description' => __( 'Filter by course ID.', 'skillpulse-lms' ),
				'type'        => 'integer',
			),
			'type'      => array(
				'description' => __( 'Filter by notification type.', 'skillpulse-lms' ),
				'type'        => 'string',
			),
			'per_page'  => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 50,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'page'      => array(
				'description' => __( 'Current page of the collection.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'orderby'   => array(
				'description' => __( 'Sort collection by object attribute.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'created_at',
				'enum'        => array( 'id', 'created_at', 'read_at', 'user_id', 'event_key' ),
			),
			'order'     => array(
				'description' => __( 'Order sort attribute ascending or descending.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'DESC',
				'enum'        => array( 'ASC', 'DESC' ),
			),
		);
	}

	/**
	 * Check admin permissions.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if user has manage_options capability, false otherwise.
	 */
	public function check_admin_permissions() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get all notifications with filters.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_notifications( $request ) {
		$query = SkillPulse_LMS_Notifications_Query::get_instance();

		$args = array(
			'user_id'   => $request->get_param( 'user_id' ),
			'event_key' => $request->get_param( 'event_key' ),
			'is_read'   => $request->get_param( 'is_read' ),
			'course_id' => $request->get_param( 'course_id' ),
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

		// Remove null values.
		$args = array_filter(
			$args,
			function ( $value ) {
				return null !== $value;
			}
		);

		$notifications = $query->get_notifications( $args );

		// Format notifications for response.
		$formatted = array();
		foreach ( $notifications as $notification ) {
			$formatted[] = $this->format_notification( $notification );
		}

		// Get total count for pagination.
		$total_args = $args;
		unset( $total_args['limit'], $total_args['offset'] );
		$stats = $query->get_stats( $total_args );
		$total = $stats['total'];

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $formatted,
				'total'   => $total,
				'pages'   => ceil( $total / $per_page ),
				'page'    => $page,
			)
		);
	}

	/**
	 * Get in-app notification templates.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_templates( $request ) {
		// Check cache first (15-minute TTL for performance).
		$cache_key = 'splms_in_app_templates_api_response';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		// Get all templates using the templates class.
		$in_app_templates = SkillPulse_LMS_In_App_Templates::get_instance();
		$all_templates    = $in_app_templates->get_all_templates();

		// Get event information from preferences.
		$preferences = SkillPulse_LMS_Notification_Preferences::get_instance();
		$events      = $preferences->get_available_events();

		$templates = array();
		foreach ( $all_templates as $event_key => $template ) {
			$event_data = $events[ $event_key ] ?? array();

			$templates[ $event_key ] = array(
				'event_key'         => $event_key,
				'event_name'        => $event_data['name'] ?? '',
				'event_description' => $event_data['description'] ?? '',
				'title'             => $template['title'] ?? '',
				'message'           => $template['message'] ?? '',
				'type'              => $template['type'] ?? 'info',
				'is_enabled'        => isset( $template['is_enabled'] ) ? (bool) $template['is_enabled'] : true,
			);
		}

		$response = array(
			'success' => true,
			'data'    => $templates,
		);

		// Cache the response for 15 minutes.
		set_transient( $cache_key, $response, 15 * MINUTE_IN_SECONDS );

		return rest_ensure_response( $response );
	}

	/**
	 * Save in-app notification template.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function save_template( $request ) {
		$template_data = $request->get_json_params();

		if ( ! $template_data ) {
			$template_data = $request->get_params();
		}

		if ( ! isset( $template_data['event_key'] ) ) {
			return new WP_Error( 'missing_event_key', __( 'Event key is required.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$event_key   = sanitize_text_field( $template_data['event_key'] );
		$option_name = "splms_in_app_template_{$event_key}";

		// Get existing template to compare.
		$existing_template = get_option( $option_name, array() );

		// Build new template array with all fields.
		$template = array(
			'title'      => isset( $template_data['title'] ) ? sanitize_text_field( $template_data['title'] ) : '',
			'message'    => isset( $template_data['message'] ) ? wp_kses_post( $template_data['message'] ) : '',
			'type'       => isset( $template_data['type'] ) ? sanitize_text_field( $template_data['type'] ) : 'info',
			'is_enabled' => isset( $template_data['is_enabled'] ) ? (bool) $template_data['is_enabled'] : true,
		);

		// Validate type value.
		$allowed_types = array( 'info', 'success', 'warning', 'error' );
		if ( ! in_array( $template['type'], $allowed_types, true ) ) {
			$template['type'] = 'info';
		}

		// Save template - update_option returns false if value hasn't changed, which is not an error.
		$result = update_option( $option_name, $template );

		// Check if there was an actual error by comparing old and new values.
		// If update_option returns false but values are different, that's an error.
		// If values are the same, that's fine (no update needed).
		if ( false === $result && $existing_template !== $template ) {
			return new WP_Error( 'save_failed', __( 'Failed to save template.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Clear caches when template is saved (even if value didn't change, clear cache for freshness).
		// This ensures the cache is always fresh after a save attempt.
		delete_transient( 'splms_in_app_templates_api_response' );
		$in_app_templates = SkillPulse_LMS_In_App_Templates::get_instance();
		if ( method_exists( $in_app_templates, 'clear_cache' ) ) {
			$in_app_templates->clear_cache();
		}

		$preferences = SkillPulse_LMS_Notification_Preferences::get_instance();
		$events      = $preferences->get_available_events();
		$event_data  = $events[ $event_key ] ?? array();

		$response = array(
			'event_key'         => $event_key,
			'event_name'        => $event_data['name'] ?? '',
			'event_description' => $event_data['description'] ?? '',
			'title'             => $template['title'],
			'message'           => $template['message'],
			'type'              => $template['type'],
			'is_enabled'        => $template['is_enabled'],
		);

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $response,
			)
		);
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
		$data = $request->get_json_params();

		if ( ! $data ) {
			$data = $request->get_params();
		}

		if ( ! isset( $data['action'] ) || ! isset( $data['ids'] ) || ! is_array( $data['ids'] ) ) {
			return new WP_Error( 'invalid_request', __( 'Action and notification IDs are required.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$action = sanitize_text_field( $data['action'] );
		$ids    = array_map( 'absint', $data['ids'] );
		$query  = SkillPulse_LMS_Notifications_Query::get_instance();

		$results = array(
			'success' => 0,
			'failed'  => 0,
		);

		foreach ( $ids as $id ) {
			$result = false;

			switch ( $action ) {
				case 'delete':
					// Admin can only delete notifications for content moderation.
					// User read/unread status is managed by users themselves for data integrity.
					$result = $query->delete_notification( $id );
					break;

				default:
					// Unsupported action for admin operations.
					// Mark read/unread actions removed to preserve user interaction data integrity.
					$result = false;
					break;
			}

			if ( $result ) {
				++$results['success'];
			} else {
				++$results['failed'];
			}
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $results,
				// translators: %1$d: Number of successful operations, %2$d: Number of failed operations.
				'message' => sprintf( __( 'Processed %1$d notifications successfully, %2$d failed.', 'skillpulse-lms' ), $results['success'], $results['failed'] ),
			)
		);
	}

	/**
	 * Delete a notification.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_notification( $request ) {
		$id = $request->get_param( 'id' );

		if ( ! $id ) {
			return new WP_Error( 'missing_id', __( 'Notification ID is required.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$query  = SkillPulse_LMS_Notifications_Query::get_instance();
		$result = $query->delete_notification( $id );

		if ( ! $result ) {
			return new WP_Error( 'delete_failed', __( 'Failed to delete notification.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Notification deleted successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Get user notifications.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_user_notifications( $request ) {
		$user_id = $request->get_param( 'user_id' );

		if ( ! $user_id ) {
			return new WP_Error( 'missing_user_id', __( 'User ID is required.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$query = SkillPulse_LMS_Notifications_Query::get_instance();

		$per_page = $request->get_param( 'per_page' );
		$page     = $request->get_param( 'page' );
		$orderby  = $request->get_param( 'orderby' );
		$order    = $request->get_param( 'order' );

		$args = array(
			'user_id' => $user_id,
			'limit'   => ! empty( $per_page ) ? absint( $per_page ) : 50,
			'offset'  => ( ( ! empty( $page ) ? absint( $page ) : 1 ) - 1 ) * ( ! empty( $per_page ) ? absint( $per_page ) : 50 ),
			'orderby' => ! empty( $orderby ) ? sanitize_text_field( $orderby ) : 'created_at',
			'order'   => ! empty( $order ) ? sanitize_text_field( $order ) : 'DESC',
		);

		$notifications = $query->get_user_notifications( $user_id, $args );

		// Format notifications for response.
		$formatted = array();
		foreach ( $notifications as $notification ) {
			$formatted[] = $this->format_notification( $notification );
		}

		// Get total count.
		$stats    = $query->get_stats( array( 'user_id' => $user_id ) );
		$total    = $stats['total'];
		$per_page = ! empty( $per_page ) ? absint( $per_page ) : 50;

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $formatted,
				'total'   => $total,
				'pages'   => ceil( $total / $per_page ),
			)
		);
	}

	/**
	 * Format notification for API response.
	 *
	 * @since 1.0.0
	 *
	 * @param object $notification Notification object from database.
	 * @return array Formatted notification array.
	 */
	private function format_notification( $notification ) {
		$meta = null;
		if ( ! empty( $notification->meta ) ) {
			$meta = json_decode( $notification->meta, true );
		}

		// Get user info.
		$user       = get_userdata( $notification->user_id );
		$user_name  = $user ? $user->display_name : __( 'Unknown User', 'skillpulse-lms' );
		$user_email = $user ? $user->user_email : '';

		// Get course info if available.
		$course_title = '';
		if ( $notification->course_id ) {
			$course       = get_post( $notification->course_id );
			$course_title = $course ? $course->post_title : '';
		}

		return array(
			'id'           => (int) $notification->id,
			'user_id'      => (int) $notification->user_id,
			'user_name'    => $user_name,
			'user_email'   => $user_email,
			'event_key'    => $notification->event_key,
			'title'        => $notification->title,
			'message'      => $notification->message,
			'type'         => $notification->type,
			'is_read'      => (bool) $notification->is_read,
			'read_at'      => $notification->read_at,
			'course_id'    => $notification->course_id ? (int) $notification->course_id : null,
			'course_title' => $course_title,
			'related_id'   => $notification->related_id ? (int) $notification->related_id : null,
			'related_type' => $notification->related_type,
			'meta'         => $meta,
			'created_at'   => $notification->created_at,
		);
	}
}
