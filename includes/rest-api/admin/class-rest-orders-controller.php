<?php
/**
 * Orders REST API Controller
 *
 * Handles REST API endpoints for order management operations.
 * Provides endpoints for CRUD operations, statistics, export, and bulk actions on orders.
 *
 * @since   1.0.0
 *
 * @package SkillPulse_LMS
 * @api
 * Available endpoints:
 * GET    /splms/v1/orders - Get list of orders
 * POST   /splms/v1/orders - Create new order
 * GET    /splms/v1/orders/{id} - Get single order by ID
 * PUT    /splms/v1/orders/{id} - Update order
 * DELETE /splms/v1/orders/{id} - Delete order
 * GET    /splms/v1/orders/statistics - Get order statistics
 * GET    /splms/v1/orders/export - Export orders
 * POST   /splms/v1/orders/bulk - Bulk update operations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orders REST API Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Orders_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'orders';

		// Hook to serve HTML directly for print invoice endpoint.
		add_filter( 'rest_pre_serve_request', array( $this, 'serve_print_invoice_html' ), 10, 4 );
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// GET /splms/v1/orders - Get orders list.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_orders' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_order' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		// GET /splms/v1/orders/statistics - Get order statistics.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/statistics',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_statistics' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// GET /splms/v1/orders/export - Export orders.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/export',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'export_orders' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		// POST /splms/v1/orders/bulk - Bulk operations.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/bulk',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'bulk_update_orders' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// POST /splms/v1/orders/{id}/refund - Refund order.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/refund',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'refund_order' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id'     => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && $param > 0;
							},
						),
						'amount' => array(
							'required'    => false,
							'type'        => 'number',
							'description' => __( 'Refund amount (null for full refund)', 'skillpulse-lms' ),
						),
						'reason' => array(
							'required'    => false,
							'type'        => 'string',
							'description' => __( 'Refund reason', 'skillpulse-lms' ),
						),
					),
				),
			)
		);

		// POST /splms/v1/orders/{id}/cancel - Cancel order.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/cancel',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'cancel_order' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id'     => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && $param > 0;
							},
						),
						'reason' => array(
							'required'    => false,
							'type'        => 'string',
							'description' => __( 'Cancellation reason', 'skillpulse-lms' ),
						),
					),
				),
			)
		);

		// GET/PUT/DELETE /splms/v1/orders/{id} - Single order operations.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_order' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && $param > 0;
							},
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_order' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && $param > 0;
							},
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_order' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && $param > 0;
							},
						),
					),
				),
			)
		);

		// GET /splms/v1/orders/{id}/invoice/download - Download invoice PDF.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/invoice/download',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'download_invoice' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && $param > 0;
							},
						),
					),
				),
			)
		);

		// GET /splms/v1/orders/{id}/invoice/print - Print invoice HTML.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/invoice/print',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'print_invoice' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && $param > 0;
							},
						),
					),
				),
			)
		);

		// GET /splms/v1/orders/{id}/notes - Get order notes.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/notes',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_order_notes' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id'   => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && $param > 0;
							},
						),
						'type' => array(
							'required'    => false,
							'type'        => 'string',
							'enum'        => array( 'note', 'activity', 'customer' ),
							'description' => __( 'Filter notes by type', 'skillpulse-lms' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'add_order_note' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id'      => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && $param > 0;
							},
						),
						'content' => array(
							'required'    => true,
							'type'        => 'string',
							'description' => __( 'Note content', 'skillpulse-lms' ),
						),
						'type'    => array(
							'required'    => false,
							'type'        => 'string',
							'enum'        => array( 'note', 'activity', 'customer' ),
							'default'     => 'note',
							'description' => __( 'Note type', 'skillpulse-lms' ),
						),
					),
				),
			)
		);

		// DELETE /splms/v1/orders/{id}/notes/{note_index} - Delete order note.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/notes/(?P<note_index>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_order_note' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id'         => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && $param > 0;
							},
						),
						'note_index' => array(
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => function ( $param ) {
								return is_numeric( $param ) && $param >= 0;
							},
						),
					),
				),
			)
		);
	}

	/**
	 * Check if a given request has access to get items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error|bool True if request has access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		// Require manage_options capability for all order operations.
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to access orders.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Get single order by ID.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_order( $request ) {
		$order_id     = $request->get_param( 'id' );
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return new WP_Error( 'order_not_found', __( 'Order not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Get order items (for section-based purchases).
		$order->items = $orders_query->get_order_items( $order_id );

		// Add purchase type indicator.
		$order->purchase_type = ! empty( $order->items ) ? 'sections' : 'full_course';

		return rest_ensure_response(
			array(
				'success' => true,
				'order'   => $order,
			)
		);
	}

	/**
	 * Create new order.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_order( $request ) {
		$order_data = $request->get_json_params();

		if ( empty( $order_data ) || ! is_array( $order_data ) ) {
			return new WP_Error( 'invalid_data', __( 'Invalid order data.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Validate required fields.
		$required_fields = array( 'user_id', 'course_id', 'amount' );
		foreach ( $required_fields as $field ) {
			if ( ! isset( $order_data[ $field ] ) ) {
				return new WP_Error(
					'missing_field',
					/* translators: %s: Field name */
					sprintf( __( 'Missing required field: %s', 'skillpulse-lms' ), $field ),
					array( 'status' => 400 )
				);
			}
		}

		// Sanitize and validate input data.
		$sanitized_data = array(
			'user_id'   => isset( $data['user_id'] ) ? absint( $data['user_id'] ) : 0,
			'course_id' => isset( $data['course_id'] ) ? absint( $data['course_id'] ) : 0,
			'amount'    => isset( $data['amount'] ) ? floatval( $data['amount'] ) : 0,
			'currency'  => isset( $data['currency'] ) ? sanitize_text_field( $data['currency'] ) : 'USD',
			'status'    => isset( $data['status'] ) ? sanitize_key( $data['status'] ) : 'pending',
		);

		// Validate user exists.
		if ( ! get_user_by( 'id', $sanitized_data['user_id'] ) ) {
			return new WP_Error( 'invalid_user', __( 'Invalid user ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Validate course exists and is correct post type.
		$course = get_post( $sanitized_data['course_id'] );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'invalid_course', __( 'Invalid course ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Validate amount is positive.
		if ( $sanitized_data['amount'] <= 0 ) {
			return new WP_Error( 'invalid_amount', __( 'Order amount must be greater than zero.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Validate status is allowed.
		$allowed_statuses = array( 'pending', 'completed', 'failed', 'refunded', 'cancelled' );
		if ( ! in_array( $sanitized_data['status'], $allowed_statuses, true ) ) {
			return new WP_Error( 'invalid_status', __( 'Invalid order status.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Validate currency format (3-letter ISO code).
		if ( ! preg_match( '/^[A-Z]{3}$/', $sanitized_data['currency'] ) ) {
			return new WP_Error( 'invalid_currency', __( 'Invalid currency code.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Sanitize optional meta data if provided.
		$meta_data = array();
		if ( isset( $order_data['meta'] ) && is_array( $order_data['meta'] ) ) {
			foreach ( $order_data['meta'] as $key => $value ) {
				$sanitized_key = sanitize_key( $key );
				if ( is_string( $value ) ) {
					$meta_data[ $sanitized_key ] = sanitize_text_field( $value );
				} elseif ( is_numeric( $value ) ) {
					$meta_data[ $sanitized_key ] = is_float( $value ) ? floatval( $value ) : absint( $value );
				} elseif ( is_bool( $value ) ) {
					$meta_data[ $sanitized_key ] = (bool) $value;
				} elseif ( is_array( $value ) ) {
					$meta_data[ $sanitized_key ] = array_map( 'sanitize_text_field', $value );
				}
			}
		}

		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order_id     = $orders_query->create_order( $sanitized_data, $meta_data );

		if ( ! $order_id ) {
			return new WP_Error( 'create_failed', __( 'Failed to create order.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$order = $orders_query->get_order_by_id( $order_id );

		return rest_ensure_response(
			array(
				'success' => true,
				'order'   => $order,
			)
		);
	}

	/**
	 * Delete existing order.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_order( $request ) {
		$order_id     = absint( $request->get_param( 'id' ) );
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();

		$result = $orders_query->delete_order( $order_id );

		if ( ! $result ) {
			return new WP_Error( 'delete_failed', __( 'Failed to delete order.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Order deleted successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Get orders with filters.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_orders( $request ) {
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();

		$params = array(
			'per_page'       => $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 20,
			'page'           => $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1,
			'search'         => $request->get_param( 'search' ),
			'course_id'      => $request->get_param( 'course_id' ),
			'status'         => $request->get_param( 'status' ),
			'payment_method' => $request->get_param( 'payment_method' ),
			'user_id'        => $request->get_param( 'user_id' ),
			'customer'       => $request->get_param( 'customer' ),
			'date_from'      => $request->get_param( 'date_from' ),
			'date_to'        => $request->get_param( 'date_to' ),
			'order_by'       => $request->get_param( 'order_by' ) ? $request->get_param( 'order_by' ) : 'created_at',
			'order'          => strtoupper( $request->get_param( 'order' ) ? $request->get_param( 'order' ) : 'DESC' ),
		);

		// Remove empty parameters.
		$params = array_filter(
			$params,
			function ( $value ) {
				return '' !== $value && null !== $value;
			}
		);

		$orders = $orders_query->get_orders( $params );

		// Add sequential numeric order ID and related data to each order.
		foreach ( $orders as $order ) {
			// Calculate sequential numeric ID (database ID + 1000 to start from 1001).
			$order->display_order_id = '#' . ( intval( $order->id ) + 1000 );

			// Include course title to reduce frontend lookups.
			if ( ! empty( $order->course_id ) ) {
				$course = get_post( $order->course_id );
				if ( $course ) {
					$order->course_title = esc_html( $course->post_title );
					$order->course_link  = esc_url( get_permalink( $course->ID ) );
				} else {
					/* translators: %d: Course ID */
					$order->course_title = esc_html( sprintf( __( 'Course #%d (deleted)', 'skillpulse-lms' ), $order->course_id ) );
					$order->course_link  = '';
				}
			}

			// Include user information to reduce frontend lookups.
			if ( ! empty( $order->user_id ) ) {
				$user = get_userdata( $order->user_id );
				if ( $user ) {
					$order->user_name  = esc_html( $user->display_name ? $user->display_name : $user->user_login );
					$order->user_email = esc_html( $user->user_email );
					$order->user_login = esc_html( $user->user_login );
					// Get avatar URL if available.
					$avatar_url         = get_avatar_url( $order->user_id, array( 'size' => 40 ) );
					$order->user_avatar = $avatar_url ? esc_url( $avatar_url ) : '';
				} else {
					/* translators: %d: User ID */
					$order->user_name   = esc_html( sprintf( __( 'User #%d (deleted)', 'skillpulse-lms' ), $order->user_id ) );
					$order->user_email  = '';
					$order->user_login  = '';
					$order->user_avatar = '';
				}
			}

			// Get order items (for section-based purchases).
			$order->items = $orders_query->get_order_items( $order->id );

			// Add purchase type indicator.
			$order->purchase_type = ! empty( $order->items ) ? 'sections' : 'full_course';

			// Include refund information if order is refunded.
			if ( 'refunded' === $order->status ) {
				$refund_amount = $orders_query->get_order_meta( $order->id, 'refund_amount' );
				$refunded_at   = $orders_query->get_order_meta( $order->id, 'refunded_at' );
				if ( $refund_amount ) {
					$order->refund_amount = floatval( $refund_amount );
				}
				if ( $refunded_at ) {
					$order->refunded_at = $refunded_at;
				}
			}
		}

		// Get total count for pagination.
		$total_params = $params;
		unset( $total_params['per_page'], $total_params['page'] );
		$total_count = $orders_query->get_orders_count( $total_params );

		return rest_ensure_response(
			array(
				'orders'      => $orders,
				'total'       => $total_count,
				'page'        => $params['page'],
				'per_page'    => $params['per_page'],
				'total_pages' => ceil( $total_count / $params['per_page'] ),
			)
		);
	}

	/**
	 * Get order statistics.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_statistics( $request ) {
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();

		$params = array(
			'date_from' => $request->get_param( 'date_from' ),
			'date_to'   => $request->get_param( 'date_to' ),
			'course_id' => $request->get_param( 'course_id' ),
		);

		// Remove empty parameters.
		$params = array_filter(
			$params,
			function ( $value ) {
				return '' !== $value && null !== $value;
			}
		);

		$statistics = $orders_query->get_order_statistics( $params );

		return rest_ensure_response(
			array(
				'statistics' => $statistics,
			)
		);
	}

	/**
	 * Update an order.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_order( $request ) {
		$order_id = absint( $request->get_param( 'id' ) );
		$data     = $request->get_json_params();

		if ( ! $order_id ) {
			return new WP_Error( 'missing_order_id', __( 'Order ID is required.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Validate data is an array.
		if ( ! is_array( $data ) ) {
			return new WP_Error( 'invalid_data', __( 'Invalid update data.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return new WP_Error( 'order_not_found', __( 'Order not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Update order status if provided.
		if ( isset( $data['status'] ) ) {
			$old_status = $order->status;
			// Sanitize and validate status.
			$new_status = sanitize_key( $data['status'] );

			// Validate status is allowed.
			$allowed_statuses = array( 'pending', 'completed', 'failed', 'refunded', 'cancelled' );
			if ( ! in_array( $new_status, $allowed_statuses, true ) ) {
				return new WP_Error( 'invalid_status', __( 'Invalid order status.', 'skillpulse-lms' ), array( 'status' => 400 ) );
			}

			// Validate status transition.
			if ( class_exists( 'SkillPulse_LMS_Order_Status_Manager' ) ) {
				if ( ! SkillPulse_LMS_Order_Status_Manager::can_transition_to( $old_status, $new_status ) ) {
					return new WP_Error( 'invalid_status_transition', __( 'Invalid status transition.', 'skillpulse-lms' ), array( 'status' => 400 ) );
				}
			}

			$update_data = array( 'status' => $new_status );

			if ( 'completed' === $new_status && ! $order->completed_at ) {
				$update_data['completed_at'] = current_time( 'mysql' );
			}

			$orders_query->update_order_status( $order_id, $new_status, $update_data );

			// Handle course access based on status change.
			if ( class_exists( 'SkillPulse_LMS_Order_Access_Control' ) ) {
				SkillPulse_LMS_Order_Access_Control::handle_order_status_change( $order_id, $old_status, $new_status );
			}
		}

		// Update meta data if provided.
		if ( isset( $data['notes'] ) ) {
			// Sanitize notes - allow textarea field.
			$notes = sanitize_textarea_field( $data['notes'] );
			$orders_query->update_order_meta( $order_id, 'notes', $notes );
		}

		// Update amount if provided (with validation).
		if ( isset( $data['amount'] ) ) {
			$amount = floatval( $data['amount'] );
			if ( $amount < 0 ) {
				return new WP_Error( 'invalid_amount', __( 'Order amount cannot be negative.', 'skillpulse-lms' ), array( 'status' => 400 ) );
			}
			$orders_query->update_order_meta( $order_id, 'amount', $amount );
		}

		// Update currency if provided (with validation).
		if ( isset( $data['currency'] ) ) {
			$currency = sanitize_text_field( $data['currency'] );
			// Validate currency format (3-letter ISO code).
			if ( ! preg_match( '/^[A-Z]{3}$/', $currency ) ) {
				return new WP_Error( 'invalid_currency', __( 'Invalid currency code.', 'skillpulse-lms' ), array( 'status' => 400 ) );
			}
			$orders_query->update_order_meta( $order_id, 'currency', $currency );
		}

		// Get updated order.
		$updated_order = $orders_query->get_order_by_id( $order_id );

		return rest_ensure_response(
			array(
				'order'   => $updated_order,
				'message' => __( 'Order updated successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Refund an order.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function refund_order( $request ) {
		$order_id = absint( $request->get_param( 'id' ) );
		$amount   = $request->get_param( 'amount' );
		$reason   = $request->get_param( 'reason' );

		if ( ! $order_id ) {
			return new WP_Error( 'invalid_order_id', __( 'Invalid order ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Validate and sanitize amount if provided.
		if ( null !== $amount ) {
			$amount = floatval( $amount );
			if ( $amount < 0 ) {
				return new WP_Error( 'invalid_amount', __( 'Refund amount cannot be negative.', 'skillpulse-lms' ), array( 'status' => 400 ) );
			}
		}

		// Sanitize reason if provided.
		if ( null !== $reason ) {
			$reason = sanitize_textarea_field( $reason );
			// Limit reason length to prevent abuse.
			if ( strlen( $reason ) > 1000 ) {
				return new WP_Error( 'invalid_reason', __( 'Refund reason is too long. Maximum 1000 characters.', 'skillpulse-lms' ), array( 'status' => 400 ) );
			}
		}

		// Process refund using Refund Manager.
		if ( ! class_exists( 'SkillPulse_LMS_Order_Refund_Manager' ) ) {
			return new WP_Error( 'refund_manager_not_found', __( 'Refund manager not available.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$refund_manager = SkillPulse_LMS_Order_Refund_Manager::get_instance();
		$result         = $refund_manager->process_refund( $order_id, $amount, $reason );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Get updated order.
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		return rest_ensure_response(
			array(
				'success' => true,
				'order'   => $order,
				'refund'  => $result,
				'message' => __( 'Order refunded successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Cancel an order.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function cancel_order( $request ) {
		$order_id = absint( $request->get_param( 'id' ) );
		$reason   = $request->get_param( 'reason' );

		if ( ! $order_id ) {
			return new WP_Error( 'invalid_order_id', __( 'Invalid order ID.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Sanitize reason if provided.
		if ( null !== $reason ) {
			$reason = sanitize_textarea_field( $reason );
			// Limit reason length to prevent abuse.
			if ( strlen( $reason ) > 1000 ) {
				return new WP_Error( 'invalid_reason', __( 'Cancellation reason is too long. Maximum 1000 characters.', 'skillpulse-lms' ), array( 'status' => 400 ) );
			}
		}

		// Process cancellation using Refund Manager.
		if ( ! class_exists( 'SkillPulse_LMS_Order_Refund_Manager' ) ) {
			return new WP_Error( 'refund_manager_not_found', __( 'Refund manager not available.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$refund_manager = SkillPulse_LMS_Order_Refund_Manager::get_instance();
		$result         = $refund_manager->process_cancellation( $order_id, $reason );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Get updated order.
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		return rest_ensure_response(
			array(
				'success' => true,
				'order'   => $order,
				'message' => __( 'Order cancelled successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Bulk update orders.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function bulk_update_orders( $request ) {
		$data      = $request->get_json_params();
		$order_ids = $data['order_ids'] ?? array();
		$action    = $data['action'] ?? '';

		if ( ! $order_ids || ! is_array( $order_ids ) ) {
			return new WP_Error( 'missing_order_ids', __( 'Order IDs are required.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		if ( ! $action ) {
			return new WP_Error( 'missing_action', __( 'Action is required.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		$orders_query  = SkillPulse_LMS_Orders_Query::get_instance();
		$updated_count = 0;

		foreach ( $order_ids as $order_id ) {
			$order = $orders_query->get_order_by_id( $order_id );

			if ( $order ) {
				$update_data = array();

				if ( 'completed' === $action && ! $order->completed_at ) {
					$update_data['completed_at'] = current_time( 'mysql' );
				}

				$orders_query->update_order_status( $order_id, $action, $update_data );
				++$updated_count;
			}
		}

		return rest_ensure_response(
			array(
				'updated_count' => $updated_count,
				'total_count'   => count( $order_ids ),
				// translators: %d: The number of orders updated.
				'message'       => sprintf( __( 'Successfully updated %d order(s).', 'skillpulse-lms' ), $updated_count ),
			)
		);
	}

	/**
	 * Export orders to CSV.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object containing export data or WP_Error on failure.
	 */
	public function export_orders( $request ) {
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();

		$params = array(
			'search'         => $request->get_param( 'search' ),
			'course_id'      => $request->get_param( 'course_id' ),
			'status'         => $request->get_param( 'status' ),
			'payment_method' => $request->get_param( 'payment_method' ),
			'user_id'        => $request->get_param( 'user_id' ),
			'date_from'      => $request->get_param( 'date_from' ),
			'date_to'        => $request->get_param( 'date_to' ),
		);

		// Remove empty parameters.
		$params = array_filter(
			$params,
			function ( $value ) {
				return '' !== $value && null !== $value;
			}
		);

		$orders = $orders_query->get_orders( $params );

		// Generate CSV.
		$filename = 'orders-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';
		$filepath = wp_upload_dir()['basedir'] . '/splms-exports/' . $filename;

		// Create directory if it doesn't exist.
		wp_mkdir_p( dirname( $filepath ) );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Direct file operations needed for CSV export.
		$file = fopen( $filepath, 'w' );

		// CSV headers.
		$headers = array(
			'Order ID',
			'Course',
			'User Email',
			'User Name',
			'Amount',
			'Currency',
			'Payment Method',
			'Status',
			'Created At',
			'Completed At',
			'Gateway Order ID',
			'Transaction ID',
		);

		fputcsv( $file, $headers ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fputcsv.

		// CSV data.
		foreach ( $orders as $order ) {
			$row = array(
				sanitize_text_field( $order->id ),
				isset( $order->meta['course_title'] ) ? sanitize_text_field( $order->meta['course_title'] ) : '',
				isset( $order->meta['user_email'] ) ? sanitize_email( $order->meta['user_email'] ) : '',
				isset( $order->meta['user_name'] ) ? sanitize_text_field( $order->meta['user_name'] ) : '',
				floatval( $order->amount ),
				sanitize_text_field( $order->currency ),
				isset( $order->meta['payment_method'] ) ? sanitize_text_field( $order->meta['payment_method'] ) : '',
				sanitize_text_field( $order->status ),
				sanitize_text_field( $order->created_at ),
				isset( $order->completed_at ) ? sanitize_text_field( $order->completed_at ) : '',
				isset( $order->meta['gateway_order_id'] ) ? sanitize_text_field( $order->meta['gateway_order_id'] ) : '',
				isset( $order->meta['gateway_transaction_id'] ) ? sanitize_text_field( $order->meta['gateway_transaction_id'] ) : '',
			);

			fputcsv( $file, $row ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fputcsv.
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct file operations needed for CSV export.
		fclose( $file );

		$download_url = wp_upload_dir()['baseurl'] . '/splms-exports/' . $filename;

		return rest_ensure_response(
			array(
				'download_url' => $download_url,
				'filename'     => $filename,
				'orders_count' => count( $orders ),
				'message'      => __( 'Orders exported successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Get the query params for collections.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters array.
	 */
	public function get_collection_params() {
		return array(
			'per_page'       => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 20,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'page'           => array(
				'description' => __( 'Current page of the collection.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'search'         => array(
				'description' => __( 'Limit results to those matching a string.', 'skillpulse-lms' ),
				'type'        => 'string',
			),
			'course_id'      => array(
				'description' => __( 'Filter by course ID.', 'skillpulse-lms' ),
				'type'        => 'integer',
			),
			'status'         => array(
				'description' => __( 'Filter by order status.', 'skillpulse-lms' ),
				'type'        => 'string',
				'enum'        => array( 'pending', 'completed', 'failed', 'refunded', 'cancelled' ),
			),
			'payment_method' => array(
				'description' => __( 'Filter by payment method.', 'skillpulse-lms' ),
				'type'        => 'string',
				'enum'        => array( 'paypal', 'stripe', 'bank_transfer', 'cash' ),
			),
			'user_id'        => array(
				'description' => __( 'Filter by user ID.', 'skillpulse-lms' ),
				'type'        => 'integer',
			),
			'customer'       => array(
				'description' => __( 'Filter by customer search (username or email).', 'skillpulse-lms' ),
				'type'        => 'string',
			),
			'date_from'      => array(
				'description' => __( 'Filter orders from this date.', 'skillpulse-lms' ),
				'type'        => 'string',
				'format'      => 'date',
			),
			'date_to'        => array(
				'description' => __( 'Filter orders to this date.', 'skillpulse-lms' ),
				'type'        => 'string',
				'format'      => 'date',
			),
			'order_by'       => array(
				'description' => __( 'Order by field.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'created_at',
				'enum'        => array( 'order_id', 'course_id', 'user_id', 'amount', 'status', 'created_at' ),
			),
			'order'          => array(
				'description' => __( 'Order direction.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'desc',
				'enum'        => array( 'asc', 'desc', 'ASC', 'DESC' ),
			),
		);
	}

	/**
	 * Download invoice PDF for an order.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function download_invoice( $request ) {
		$order_id = $request->get_param( 'id' );

		if ( ! class_exists( 'SkillPulse_LMS_Order_Invoice' ) ) {
			return new WP_Error( 'class_not_found', __( 'Invoice class not found.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$invoice_generator = SkillPulse_LMS_Order_Invoice::get_instance();
		$result            = $invoice_generator->generate_pdf( $order_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response(
			array(
				'success'      => true,
				'download_url' => $result['download_url'],
				'filename'     => $result['filename'],
			)
		);
	}

	/**
	 * Serve print invoice HTML directly, bypassing REST API JSON wrapper.
	 *
	 * @param bool             $served  Whether the request has already been served.
	 * @param WP_HTTP_Response $result  Result to send to the client.
	 * @param WP_REST_Request  $request Request used to generate the response.
	 * @param WP_REST_Server   $server  Server instance.
	 *
	 * @since 1.0.0
	 * @return bool True if request was served, false otherwise.
	 */
	public function serve_print_invoice_html( $served, $result, $request, $server ) {
		// Check if this is the print invoice endpoint.
		$route = $request->get_route();

		// Match route pattern: /splms/v1/orders/{id}/invoice/print.
		$route_pattern = '#^/' . preg_quote( $this->namespace . '/' . $this->rest_base, '#' ) . '/(\d+)/invoice/print$#';

		if ( preg_match( $route_pattern, $route, $matches ) ) {
			// Get the order ID from the route.
			$order_id = isset( $matches[1] ) ? absint( $matches[1] ) : 0;

			if ( ! $order_id ) {
				return $served;
			}

			// Check permissions.
			if ( ! $this->get_items_permissions_check( $request ) ) {
				return $served;
			}

			if ( ! class_exists( 'SkillPulse_LMS_Order_Invoice' ) ) {
				wp_die( esc_html__( 'Invoice class not found.', 'skillpulse-lms' ), '', array( 'response' => 500 ) );

				return true;
			}

			$invoice_generator = SkillPulse_LMS_Order_Invoice::get_instance();
			$invoice_html      = $invoice_generator->get_invoice_html( $order_id );

			if ( is_wp_error( $invoice_html ) ) {
				$error_data  = $invoice_html->get_error_data();
				$status_code = 500;
				if ( isset( $error_data['status'] ) ) {
					$status_code = absint( $error_data['status'] );
				}
				wp_die( esc_html( $invoice_html->get_error_message() ), '', array( 'response' => absint( $status_code ) ) );

				return true;
			}

			// Add print button and styling.
			$print_button = '<button type="button" class="btn btn-primary" onclick="window.print()" style="display: block; margin: 20px auto; padding: 10px 20px; background-color: #2c5aa0; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">' . esc_html__(
				'Print Invoice',
				'skillpulse-lms'
			) . '</button>';
			$print_style  = '<style>
				@media print {
					.btn-primary { display: none !important; }
				}
			</style>';

			$invoice_html = str_replace( '</body>', $print_button . $print_style . '</body>', $invoice_html );

			// Set proper headers for HTML content.
			header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
			nocache_headers();

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Invoice HTML is already escaped in get_invoice_html method.
			echo $invoice_html;
			exit;
		}

		return $served;
	}

	/**
	 * Print invoice HTML for an order.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since 1.0.0
	 * @return string|WP_Error Invoice HTML on success, or WP_Error object on failure.
	 */
	public function print_invoice( $request ) {
		$order_id = $request->get_param( 'id' );

		if ( ! class_exists( 'SkillPulse_LMS_Order_Invoice' ) ) {
			return new WP_Error( 'class_not_found', __( 'Invoice class not found.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		$invoice_generator = SkillPulse_LMS_Order_Invoice::get_instance();
		$invoice_html      = $invoice_generator->get_invoice_html( $order_id );

		if ( is_wp_error( $invoice_html ) ) {
			return $invoice_html;
		}

		// Add print button and styling.
		$print_button = '<button type="button" class="btn btn-primary" onclick="window.print()" style="display: block; margin: 20px auto; padding: 10px 20px; background-color: #2c5aa0; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">' . esc_html__(
			'Print Invoice',
			'skillpulse-lms'
		) . '</button>';
		$print_style  = '<style>
            @media print {
                .btn-primary { display: none !important; }
            }
        </style>';

		$invoice_html = str_replace( '</body>', $print_button . $print_style . '</body>', $invoice_html );

		// Return HTML as string (will be handled by serve_print_invoice_html filter).
		return $invoice_html;
	}

	/**
	 * Get order notes.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.0.0
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function get_order_notes( $request ) {
		$order_id  = absint( $request->get_param( 'id' ) );
		$note_type = $request->get_param( 'type' );

		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$notes        = $orders_query->get_order_notes( $order_id, $note_type );

		// Format notes for response.
		$formatted_notes = array();
		foreach ( $notes as $index => $note ) {
			$added_by_name = __( 'System', 'skillpulse-lms' );
			if ( $note['added_by'] > 0 ) {
				$user = get_userdata( $note['added_by'] );
				if ( $user ) {
					$added_by_name = ! empty( $user->display_name ) ? $user->display_name : $user->user_login;
				}
			}

			$formatted_notes[] = array(
				'index'         => $index,
				'content'       => isset( $note['content'] ) ? $note['content'] : '',
				'type'          => isset( $note['type'] ) ? $note['type'] : 'note',
				'added_by'      => isset( $note['added_by'] ) ? $note['added_by'] : 0,
				'added_by_name' => $added_by_name,
				'created_at'    => isset( $note['created_at'] ) ? $note['created_at'] : current_time( 'mysql' ),
			);
		}

		return rest_ensure_response( array( 'notes' => $formatted_notes ) );
	}

	/**
	 * Add order note.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.0.0
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function add_order_note( $request ) {
		$order_id = absint( $request->get_param( 'id' ) );
		$content  = sanitize_textarea_field( $request->get_param( 'content' ) );
		$type     = $request->get_param( 'type' );
		$type     = ! empty( $type ) ? $type : 'note';

		if ( empty( $content ) ) {
			return new WP_Error(
				'rest_invalid_param',
				__( 'Note content is required.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$note_index   = $orders_query->add_order_note( $order_id, $content, $type );

		if ( false === $note_index ) {
			return new WP_Error(
				'rest_add_note_failed',
				__( 'Failed to add note.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}

		// Get the newly added note.
		$notes    = $orders_query->get_order_notes( $order_id );
		$new_note = $notes[ $note_index ];

		return rest_ensure_response(
			array(
				'success'    => true,
				'note_index' => $note_index,
				'note'       => array(
					'index'         => $note_index,
					'content'       => $new_note['content'],
					'type'          => $new_note['type'],
					'added_by'      => $new_note['added_by'],
					'added_by_name' => $new_note['added_by'] > 0 ? get_userdata( $new_note['added_by'] )->display_name : __( 'System', 'skillpulse-lms' ),
					'created_at'    => $new_note['created_at'],
				),
			)
		);
	}

	/**
	 * Delete order note.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.0.0
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function delete_order_note( $request ) {
		$order_id   = absint( $request->get_param( 'id' ) );
		$note_index = absint( $request->get_param( 'note_index' ) );

		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$result       = $orders_query->delete_order_note( $order_id, $note_index );

		if ( ! $result ) {
			return new WP_Error(
				'rest_delete_note_failed',
				__( 'Failed to delete note.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Note deleted successfully.', 'skillpulse-lms' ),
			)
		);
	}
}
