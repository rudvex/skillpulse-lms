<?php
/**
 * Orders Query Class for SkillPulse LMS
 *
 * Handles database operations for orders/payments
 *
 * @package SkillPulse_LMS
 * @subpackage Modules
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SPLMS_Base_Query' ) ) {
	require_once SPLMS_DIR_PATH . 'includes/modules/core/base/class-base-query.php';
}

/**
 * Class SPLMS_Orders_Query
 *
 * Handles orders/payments database operations
 *
 * @since 1.0.0
 */
class SPLMS_Orders_Query extends SPLMS_Base_Query {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param string $table_name Database table name.
	 */
	protected function __construct( $table_name ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Constructor is needed to call parent.
		parent::__construct( $table_name );
	}

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 * @return SPLMS_Orders_Query Instance of the class.
	 */
	public static function get_instance() {
		return parent::get_base_instance( __CLASS__, 'splms_orders' );
	}

	/**
	 * Create a new order.
	 *
	 * @since 1.0.0
	 * @param array $order_data Order data.
	 * @param array $meta_data Order meta data.
	 * @return int|false Numeric order ID on success, false on failure.
	 */
	public function create_order( $order_data, $meta_data = array() ) {
		$defaults = array(
			'user_id'    => 0,
			'course_id'  => 0,
			'amount'     => 0.00,
			'currency'   => 'USD',
			'status'     => 'pending',
			'created_at' => current_time( 'mysql' ),
		);

		$order_data = wp_parse_args( $order_data, $defaults );

		// Remove order_id if present (not needed, using AUTO_INCREMENT id).
		unset( $order_data['order_id'] );

		// Insert order (only essential fields).
		$order_id = $this->insert(
			$order_data,
			array( '%d', '%d', '%f', '%s', '%s', '%s' )
		);

		if ( $order_id && ! empty( $meta_data ) ) {
			// Insert meta data using numeric ID.
			$this->add_order_meta( $order_id, $meta_data );
		}

		// Log order creation activity.
		if ( $order_id ) {
			$current_user_id  = get_current_user_id();
			$activity_message = sprintf(
				/* translators: %s: Order status */
				__( 'Order created with status: %s', 'skillpulse-lms' ),
				ucfirst( $order_data['status'] )
			);
			$this->log_order_activity( $order_id, $activity_message, $current_user_id );
		}

		return $order_id ? (int) $order_id : false;
	}

	/**
	 * Get order by numeric ID.
	 *
	 * @since 1.0.0
	 * @param int $order_id Numeric order ID.
	 * @return object|null Order object or null if not found.
	 */
	public function get_order_by_id( $order_id ) {
		global $wpdb;

		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return null;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$sql = "SELECT * FROM {$this->table_name} WHERE id = %d";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		$order = $wpdb->get_row( $wpdb->prepare( $sql, $order_id ) );

		if ( $order ) {
			// Load meta data using numeric ID.
			$order->meta = $this->get_order_meta( $order_id );
		}

		return $order;
	}

	/**
	 * Add order meta data.
	 *
	 * @since 1.0.0
	 * @param int          $order_id Numeric order ID.
	 * @param string|array $meta_key Meta key or array of meta data.
	 * @param mixed        $meta_value Meta value (if $meta_key is string).
	 * @return bool True on success, false on failure.
	 */
	public function add_order_meta( $order_id, $meta_key, $meta_value = null ) {
		global $wpdb;

		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return false;
		}

		$meta_table = esc_sql( $wpdb->prefix . 'splms_order_meta' );

		// Handle array of meta data.
		if ( is_array( $meta_key ) ) {
			$meta_data = $meta_key;
			$success   = true;

			foreach ( $meta_data as $key => $value ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
				$result = $wpdb->replace(
					$meta_table,
					array(
						'order_id'   => $order_id,
						// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Meta queries are necessary for order meta functionality.
						'meta_key'   => $key,
						// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Meta queries are necessary for order meta functionality.
						'meta_value' => maybe_serialize( $value ),
					),
					array( '%d', '%s', '%s' )
				);

				if ( false === $result ) {
					$success = false;
				}
			}

			return $success;
		}

		// Handle single meta key-value pair.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
		return $wpdb->replace(
			$meta_table,
			array(
				'order_id'   => $order_id,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Meta queries are necessary for order meta functionality.
				'meta_key'   => $meta_key,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Meta queries are necessary for order meta functionality.
				'meta_value' => maybe_serialize( $meta_value ),
			),
			array( '%d', '%s', '%s' )
		) !== false;
	}

	/**
	 * Get order meta data.
	 *
	 * @since 1.0.0
	 * @param int    $order_id Numeric order ID.
	 * @param string $meta_key Optional meta key.
	 * @return mixed Meta value(s).
	 */
	public function get_order_meta( $order_id, $meta_key = null ) {
		global $wpdb;

		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return $meta_key ? null : array();
		}

		$meta_table = esc_sql( $wpdb->prefix . 'splms_order_meta' );

		if ( $meta_key ) {
			// Get single meta value.
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.SlowDBQuery.slow_db_query_meta_key,WordPress.DB.SlowDBQuery.slow_db_query_meta_value, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, meta queries are necessary.
			$sql = "SELECT meta_value FROM $meta_table WHERE order_id = %d AND meta_key = %s";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
			$value = $wpdb->get_var( $wpdb->prepare( $sql, $order_id, $meta_key ) );
			return maybe_unserialize( $value );
		} else {
			// Get all meta data.
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
			$sql = "SELECT meta_key, meta_value FROM $meta_table WHERE order_id = %d";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ) );

			$meta = array();
			foreach ( $results as $result ) {
				$meta[ $result->meta_key ] = maybe_unserialize( $result->meta_value );
			}

			return $meta;
		}
	}

	/**
	 * Update order meta data.
	 *
	 * @since 1.0.0
	 * @param int    $order_id Numeric order ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $meta_value Meta value.
	 * @return bool True on success, false on failure.
	 */
	public function update_order_meta( $order_id, $meta_key, $meta_value ) {
		return $this->add_order_meta( $order_id, $meta_key, $meta_value );
	}

	/**
	 * Get order by gateway order ID.
	 *
	 * @since 1.0.0
	 * @param string $gateway_order_id Gateway order ID.
	 * @return object|null Order object or null if not found.
	 */
	public function get_order_by_gateway_id( $gateway_order_id ) {
		global $wpdb;

		// First find the numeric order_id by gateway_order_id from meta table.
		$meta_table = esc_sql( $wpdb->prefix . 'splms_order_meta' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.SlowDBQuery.slow_db_query_meta_key,WordPress.DB.SlowDBQuery.slow_db_query_meta_value, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, meta queries are necessary.
		$sql = "SELECT order_id FROM $meta_table WHERE meta_key = 'gateway_order_id' AND meta_value = %s LIMIT 1";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		$order_id = $wpdb->get_var( $wpdb->prepare( $sql, $gateway_order_id ) );

		if ( $order_id ) {
			return $this->get_order_by_id( absint( $order_id ) );
		}

		return null;
	}

	/**
	 * Delete an order.
	 *
	 * @since 1.0.0
	 * @param int $order_id Numeric order ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete_order( $order_id ) {
		global $wpdb;

		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return false;
		}

		// Cascading delete for related data (following WordPress pattern: wp_delete_post deletes postmeta before deleting post).
		// We handle cascade deletion at application level, not with database foreign keys.

		// Delete order items (for section-based purchases).
		$order_items_table = esc_sql( $wpdb->prefix . 'splms_order_items' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
		$wpdb->delete(
			$order_items_table,
			array( 'order_id' => $order_id ),
			array( '%d' )
		);

		// Delete section access grants tied to this order using Section Access Query class.
		$section_access_query = SPLMS_Section_Access_Query::get_instance();
		$section_access_query->revoke_access_by_order( $order_id );

		// Delete order meta.
		$meta_table = esc_sql( $wpdb->prefix . 'splms_order_meta' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write operation.
		$wpdb->delete(
			$meta_table,
			array( 'order_id' => $order_id ),
			array( '%d' )
		);

		// Delete order using numeric ID.
		return $this->delete(
			array( 'id' => $order_id ),
			array( '%d' )
		);
	}

	/**
	 * Get order items (for section-based purchases).
	 *
	 * @since 1.0.0
	 * @param int $order_id Numeric order ID.
	 * @return array Array of order item objects, empty array if none found.
	 */
	public function get_order_items( $order_id ) {
		global $wpdb;

		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return array();
		}

		$items_table = esc_sql( $wpdb->prefix . 'splms_order_items' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$sql = "SELECT * FROM {$items_table} WHERE order_id = %d ORDER BY id ASC";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		$items = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ) );

		return $items ? $items : array();
	}

	/**
	 * Get order items for multiple orders in a single query.
	 *
	 * @since 1.0.0
	 * @param array $order_ids Array of numeric order IDs.
	 * @return array Associative array keyed by order_id with item arrays as values.
	 */
	public function get_order_items_batch( $order_ids ) {
		global $wpdb;

		$order_ids = array_map( 'absint', $order_ids );
		$order_ids = array_filter( $order_ids );

		if ( empty( $order_ids ) ) {
			return array();
		}

		$sorted_ids = $order_ids;
		sort( $sorted_ids );
		$cache_key   = 'splms_order_items_batch_' . md5( implode( ',', $sorted_ids ) );
		$cache_group = 'skillpulse-lms';
		$items       = wp_cache_get( $cache_key, $cache_group );

		if ( false === $items ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table query, no WP API available.
			$items = $wpdb->get_results(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Table prefix and placeholders are safely concatenated.
					'SELECT * FROM ' . $wpdb->prefix . 'splms_order_items WHERE order_id IN (' . implode( ',', array_fill( 0, count( $order_ids ), '%d' ) ) . ') ORDER BY id ASC',
					...$order_ids
				)
			);

			wp_cache_set( $cache_key, $items, $cache_group );
		}

		$grouped = array();
		foreach ( $order_ids as $oid ) {
			$grouped[ $oid ] = array();
		}

		if ( $items ) {
			foreach ( $items as $item ) {
				$grouped[ $item->order_id ][] = $item;
			}
		}

		return $grouped;
	}

	/**
	 * Update order status.
	 *
	 * @since 1.0.0
	 * @param int    $order_id Numeric order ID.
	 * @param string $status New status.
	 * @param array  $additional_data Additional data to update.
	 * @return bool True on success, false on failure.
	 */
	public function update_order_status( $order_id, $status, $additional_data = array() ) {
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return false;
		}

		// Get current order to validate transition and fire hooks.
		$order = $this->get_order_by_id( $order_id );
		if ( ! $order ) {
			return false;
		}

		$old_status = $order->status;
		$new_status = $status;

		// Skip if status hasn't changed.
		if ( $old_status === $new_status ) {
			return true;
		}

		// Validate status transition.
		if ( class_exists( 'SPLMS_Order_Status_Manager' ) ) {
			if ( ! SPLMS_Order_Status_Manager::can_transition_to( $old_status, $new_status ) ) {
				return false;
			}
		}

		// Fire "before" hook.
		do_action( 'splms_before_order_status_change', $order_id, $old_status, $new_status, $order );

		$update_data = array_merge(
			array(
				'status'     => $new_status,
				'updated_at' => current_time( 'mysql' ),
			),
			$additional_data
		);

		// Set completed_at if status is completed.
		if ( 'completed' === $new_status && ! isset( $update_data['completed_at'] ) ) {
			$update_data['completed_at'] = current_time( 'mysql' );
		}

		// Serialize complex data.
		if ( isset( $update_data['gateway_response'] ) && is_array( $update_data['gateway_response'] ) ) {
			$update_data['gateway_response'] = maybe_serialize( $update_data['gateway_response'] );
		}

		if ( isset( $update_data['metadata'] ) && is_array( $update_data['metadata'] ) ) {
			$update_data['metadata'] = maybe_serialize( $update_data['metadata'] );
		}

		// Perform database update using numeric ID.
		$result = $this->update(
			$update_data,
			array( 'id' => $order_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( ! $result ) {
			return false;
		}

		// Get updated order object.
		$updated_order = $this->get_order_by_id( $order_id );

		// Log activity automatically.
		$current_user_id  = get_current_user_id();
		$activity_message = sprintf(
			/* translators: 1: Old status, 2: New status */
			__( 'Order status changed from %1$s to %2$s', 'skillpulse-lms' ),
			ucfirst( $old_status ),
			ucfirst( $new_status )
		);
		$this->log_order_activity( $order_id, $activity_message, $current_user_id );

		// Fire "after" hook.
		do_action( 'splms_after_order_status_change', $order_id, $old_status, $new_status, $updated_order );

		// Fire general status changed hook.
		do_action( 'splms_order_status_changed', $order_id, $old_status, $new_status, $updated_order );

		// Fire specific transition hook (e.g., splms_order_status_pending_to_completed).
		do_action(
			'splms_order_status_' . $old_status . '_to_' . $new_status,
			$order_id,
			$updated_order,
			$old_status,
			$new_status
		);

		// Fire new status hook (e.g., splms_order_status_completed).
		do_action(
			'splms_order_status_' . $new_status,
			$order_id,
			$updated_order,
			$old_status
		);

		// Automatically handle access control.
		if ( class_exists( 'SPLMS_Order_Access_Control' ) ) {
			SPLMS_Order_Access_Control::handle_order_status_change(
				$order_id,
				$old_status,
				$new_status
			);
		}

		return true;
	}

	/**
	 * Get user orders.
	 *
	 * @since 1.0.0
	 * @param int   $user_id User ID.
	 * @param array $args Query arguments.
	 * @return array Array of order objects.
	 */
	public function get_user_orders( $user_id, $args = array() ) {
		global $wpdb;

		$defaults = array(
			'status'             => array(),
			'payment_method'     => '',
			'limit'              => 20,
			'offset'             => 0,
			'order_by'           => 'created_at',
			'order'              => 'DESC',
			'only_valid_courses' => false, // Filter out orders with deleted courses.
		);

		$args = wp_parse_args( $args, $defaults );

		// Build SQL query with optional JOIN to filter deleted courses.
		$values = array( $user_id );
		if ( $args['only_valid_courses'] ) {
			// Join with wp_posts to ensure course exists and is published.
			$course_post_type = defined( 'SPLMS_POST_TYPES' ) && isset( SPLMS_POST_TYPES['course'] ) ? SPLMS_POST_TYPES['course'] : 'sp-course';
			$sql              = "SELECT o.* FROM {$this->table_name} AS o
					INNER JOIN {$wpdb->posts} AS p ON o.course_id = p.ID
					WHERE o.user_id = %d 
					AND p.post_status != 'trash' 
					AND p.post_type = %s";
			$values[]         = $course_post_type;
		} else {
			$sql = "SELECT * FROM {$this->table_name} WHERE user_id = %d";
		}

		// Add status filter.
		$status_field = $args['only_valid_courses'] ? 'o.status' : 'status';
		if ( ! empty( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				$placeholders = implode( ',', array_fill( 0, count( $args['status'] ), '%s' ) );
				$sql         .= " AND {$status_field} IN ($placeholders)";
				$values       = array_merge( $values, $args['status'] );
			} else {
				$sql     .= " AND {$status_field} = %s";
				$values[] = $args['status'];
			}
		}

		// Add payment method filter.
		$payment_field = $args['only_valid_courses'] ? 'o.payment_method' : 'payment_method';
		if ( ! empty( $args['payment_method'] ) ) {
			$sql     .= " AND {$payment_field} = %s";
			$values[] = $args['payment_method'];
		}

		// Add ordering with validation.
		$allowed_order_by = array( 'id', 'course_id', 'user_id', 'amount', 'status', 'created_at', 'updated_at' );
		$order_by         = in_array( $args['order_by'], $allowed_order_by, true ) ? $args['order_by'] : 'created_at';
		// Map 'order_id' to 'id' for backward compatibility.
		if ( 'order_id' === $order_by ) {
			$order_by = 'id';
		}
		$order_by_field = $args['only_valid_courses'] ? 'o.' . $order_by : $order_by;
		// Validate order direction.
		$order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Order by field is validated via whitelist.
		$sql .= " ORDER BY {$order_by_field} {$order}";

		// Add limit and offset.
		if ( $args['limit'] > 0 ) {
			$sql     .= ' LIMIT %d OFFSET %d';
			$values[] = $args['limit'];
			$values[] = $args['offset'];
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		if ( empty( $values ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
			$orders = $wpdb->get_results( $sql );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
			$orders = $wpdb->get_results( $wpdb->prepare( $sql, ...$values ) );
		}

		// Load meta data for each order.
		foreach ( $orders as $order ) {
			$order->meta = $this->get_order_meta( $order->id );
		}

		return $orders;
	}

	/**
	 * Check if user has purchased course.
	 *
	 * @since 1.0.0
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 * @return bool True if user has purchased the course.
	 */
	public function has_user_purchased_course( $user_id, $course_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$sql = "SELECT COUNT(*) FROM {$this->table_name} 
				WHERE user_id = %d AND course_id = %d AND status = 'completed'";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		$count = $wpdb->get_var( $wpdb->prepare( $sql, $user_id, $course_id ) );

		return intval( $count ) > 0;
	}

	/**
	 * Get orders with filters and pagination.
	 *
	 * @since 1.0.0
	 * @param array $args Query arguments.
	 * @return array Orders data.
	 */
	public function get_orders( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'per_page'       => 20,
			'page'           => 1,
			'search'         => '',
			'course_id'      => '',
			'status'         => '',
			'payment_method' => '',
			'user_id'        => '',
			'customer'       => '',
			'date_from'      => '',
			'date_to'        => '',
			'order_by'       => 'created_at',
			'order'          => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$sql              = "SELECT * FROM {$this->table_name}";
		$where_conditions = array();
		$values           = array();

		// Add search filter.
		if ( ! empty( $args['search'] ) ) {
			$where_conditions[] = "(id LIKE %s OR user_id IN (SELECT ID FROM {$wpdb->users} WHERE user_email LIKE %s OR display_name LIKE %s))";
			$search_term        = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$values[]           = $search_term;
			$values[]           = $search_term;
			$values[]           = $search_term;
		}

		// Add course filter.
		if ( ! empty( $args['course_id'] ) ) {
			$where_conditions[] = 'course_id = %d';
			$values[]           = intval( $args['course_id'] );
		}

		// Add status filter.
		if ( ! empty( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				$placeholders       = implode( ', ', array_fill( 0, count( $args['status'] ), '%s' ) );
				$where_conditions[] = "status IN ($placeholders)";
				foreach ( $args['status'] as $status_val ) {
					$values[] = sanitize_text_field( $status_val );
				}
			} elseif ( is_scalar( $args['status'] ) ) {
				$where_conditions[] = 'status = %s';
				$values[]           = sanitize_text_field( $args['status'] );
			}
		}

		// Add user filter.
		if ( ! empty( $args['user_id'] ) ) {
			$where_conditions[] = 'user_id = %d';
			$values[]           = intval( $args['user_id'] );
		}

		// Add customer search filter (username or email).
		if ( ! empty( $args['customer'] ) ) {
			$where_conditions[] = "user_id IN (SELECT ID FROM {$wpdb->users} WHERE user_email LIKE %s OR user_login LIKE %s OR display_name LIKE %s)";
			$customer_term      = '%' . $wpdb->esc_like( $args['customer'] ) . '%';
			$values[]           = $customer_term;
			$values[]           = $customer_term;
			$values[]           = $customer_term;
		}

		// Add date filters.
		if ( ! empty( $args['date_from'] ) ) {
			$where_conditions[] = 'created_at >= %s';
			$values[]           = $args['date_from'] . ' 00:00:00';
		}

		if ( ! empty( $args['date_to'] ) ) {
			$where_conditions[] = 'created_at <= %s';
			$values[]           = $args['date_to'] . ' 23:59:59';
		}

		// Build WHERE clause.
		if ( ! empty( $where_conditions ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where_conditions );
		}

		// Add ORDER BY clause.
		$allowed_order_by = array( 'id', 'course_id', 'user_id', 'amount', 'status', 'created_at', 'updated_at' );
		$order_by         = in_array( $args['order_by'], $allowed_order_by, true ) ? $args['order_by'] : 'created_at';
		// Map 'order_id' to 'id' for backward compatibility.
		if ( 'order_id' === $order_by ) {
			$order_by = 'id';
		}
		$order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Order by field is validated.
		$sql .= " ORDER BY {$order_by} {$order}";

		// Add LIMIT clause.
		$offset   = ( $args['page'] - 1 ) * $args['per_page'];
		$sql     .= ' LIMIT %d OFFSET %d';
		$values[] = intval( $args['per_page'] );
		$values[] = intval( $offset );

		// Prepare and execute query.
		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
			$sql = $wpdb->prepare( $sql, ...$values );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		$orders = $wpdb->get_results( $sql );

		// Get meta data for each order (optimized for listing - only load essential fields).
		$listing_meta_keys = array( 'payment_method', 'gateway_transaction_id', 'gateway_order_id' );
		foreach ( $orders as $order ) {
			// Only load essential meta fields for listing to improve performance.
			$order->meta = array();
			foreach ( $listing_meta_keys as $meta_key ) {
				$meta_value = $this->get_order_meta( $order->id, $meta_key );
				if ( null !== $meta_value ) {
					$order->meta[ $meta_key ] = $meta_value;
				}
			}
		}

		// Filter by payment method if specified.
		if ( ! empty( $args['payment_method'] ) ) {
			$orders = array_filter(
				$orders,
				function ( $order ) use ( $args ) {
					return isset( $order->meta['payment_method'] ) && $args['payment_method'] === $order->meta['payment_method'];
				}
			);
		}

		return array_values( $orders );
	}

	/**
	 * Get orders count with filters.
	 *
	 * @since 1.0.0
	 * @param array $args Query arguments.
	 * @return int Orders count.
	 */
	public function get_orders_count( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'search'         => '',
			'course_id'      => '',
			'status'         => '',
			'payment_method' => '',
			'user_id'        => '',
			'customer'       => '',
			'date_from'      => '',
			'date_to'        => '',
		);

		$args = wp_parse_args( $args, $defaults );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
		$sql              = "SELECT COUNT(*) FROM {$this->table_name}";
		$where_conditions = array();
		$values           = array();

		// Add search filter.
		if ( ! empty( $args['search'] ) ) {
			$where_conditions[] = "(id LIKE %s OR user_id IN (SELECT ID FROM {$wpdb->users} WHERE user_email LIKE %s OR display_name LIKE %s))";
			$search_term        = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$values[]           = $search_term;
			$values[]           = $search_term;
			$values[]           = $search_term;
		}

		// Add course filter.
		if ( ! empty( $args['course_id'] ) ) {
			$where_conditions[] = 'course_id = %d';
			$values[]           = intval( $args['course_id'] );
		}

		// Add status filter.
		if ( ! empty( $args['status'] ) ) {
			$where_conditions[] = 'status = %s';
			$values[]           = $args['status'];
		}

		// Add user filter.
		if ( ! empty( $args['user_id'] ) ) {
			$where_conditions[] = 'user_id = %d';
			$values[]           = intval( $args['user_id'] );
		}

		// Add customer search filter (username or email).
		if ( ! empty( $args['customer'] ) ) {
			$where_conditions[] = "user_id IN (SELECT ID FROM {$wpdb->users} WHERE user_email LIKE %s OR user_login LIKE %s OR display_name LIKE %s)";
			$customer_term      = '%' . $wpdb->esc_like( $args['customer'] ) . '%';
			$values[]           = $customer_term;
			$values[]           = $customer_term;
			$values[]           = $customer_term;
		}

		// Add date filters.
		if ( ! empty( $args['date_from'] ) ) {
			$where_conditions[] = 'created_at >= %s';
			$values[]           = $args['date_from'] . ' 00:00:00';
		}

		if ( ! empty( $args['date_to'] ) ) {
			$where_conditions[] = 'created_at <= %s';
			$values[]           = $args['date_to'] . ' 23:59:59';
		}

		// Build WHERE clause.
		if ( ! empty( $where_conditions ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where_conditions );
		}

		// Prepare and execute query.
		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
			$sql = $wpdb->prepare( $sql, ...$values );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		$count = $wpdb->get_var( $sql );

		// If payment method filter is specified, we need to filter the results.
		if ( ! empty( $args['payment_method'] ) ) {
			$orders          = $this->get_orders( $args );
			$filtered_orders = array_filter(
				$orders,
				function ( $order ) use ( $args ) {
					return isset( $order->meta['payment_method'] ) && $order->meta['payment_method'] === $args['payment_method'];
				}
			);
			$count           = count( $filtered_orders );
		}

		return intval( $count );
	}

	/**
	 * Get order statistics.
	 *
	 * @since 1.0.0
	 * @param array $args Query arguments.
	 * @return array Statistics data.
	 */
	public function get_order_statistics( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'date_from' => '',
			'date_to'   => '',
			'status'    => array( 'completed' ),
			'course_id' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$sql = "SELECT 
					COUNT(*) as total_orders,
					SUM(amount) as total_revenue,
					AVG(amount) as average_order_value,
					COUNT(DISTINCT user_id) as unique_customers
				FROM {$this->table_name}";

		$where_conditions = array();
		$values           = array();

		// Add status filter.
		if ( ! empty( $args['status'] ) ) {
			if ( is_array( $args['status'] ) ) {
				$placeholders       = implode( ',', array_fill( 0, count( $args['status'] ), '%s' ) );
				$where_conditions[] = "status IN ($placeholders)";
				$values             = array_merge( $values, $args['status'] );
			} else {
				$where_conditions[] = 'status = %s';
				$values[]           = $args['status'];
			}
		}

		// Add course filter.
		if ( ! empty( $args['course_id'] ) ) {
			$where_conditions[] = 'course_id = %d';
			$values[]           = $args['course_id'];
		}

		// Add date filters.
		if ( ! empty( $args['date_from'] ) ) {
			$where_conditions[] = 'created_at >= %s';
			$values[]           = $args['date_from'];
		}

		if ( ! empty( $args['date_to'] ) ) {
			$where_conditions[] = 'created_at <= %s';
			$values[]           = $args['date_to'];
		}

		if ( ! empty( $where_conditions ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where_conditions );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		if ( empty( $values ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
			$stats = $wpdb->get_row( $sql );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
			$stats = $wpdb->get_row( $wpdb->prepare( $sql, ...$values ) );
		}

		return array(
			'total_orders'        => intval( $stats->total_orders ),
			'total_revenue'       => floatval( $stats->total_revenue ),
			'average_order_value' => floatval( $stats->average_order_value ),
			'unique_customers'    => intval( $stats->unique_customers ),
		);
	}

	/**
	 * Add a note to an order.
	 *
	 * @since 1.0.0
	 * @param int    $order_id     Order ID.
	 * @param string $note_content Note content.
	 * @param string $note_type    Note type: 'note', 'activity', or 'customer'. Default 'note'.
	 * @param int    $added_by     User ID who added the note. Default current user ID.
	 * @return bool|int Note index on success, false on failure.
	 */
	public function add_order_note( $order_id, $note_content, $note_type = 'note', $added_by = null ) {
		$order_id = absint( $order_id );
		if ( ! $order_id || empty( $note_content ) ) {
			return false;
		}

		// Validate note type.
		$allowed_types = array( 'note', 'activity', 'customer' );
		$note_type     = in_array( $note_type, $allowed_types, true ) ? $note_type : 'note';

		// Get current user ID if not provided.
		if ( null === $added_by ) {
			$added_by = get_current_user_id();
		}
		$added_by = absint( $added_by );

		// Get existing notes.
		$notes = $this->get_order_notes( $order_id );

		// Add new note.
		$new_note = array(
			'content'    => sanitize_textarea_field( $note_content ),
			'type'       => $note_type,
			'added_by'   => $added_by,
			'created_at' => current_time( 'mysql' ),
		);

		$notes[] = $new_note;

		// Save notes back to meta.
		$result = $this->update_order_meta( $order_id, '_order_notes', $notes );

		if ( $result ) {
			// Return the index of the newly added note.
			return count( $notes ) - 1;
		}

		return false;
	}

	/**
	 * Get all notes for an order.
	 *
	 * @since 1.0.0
	 * @param int    $order_id  Order ID.
	 * @param string $note_type Optional. Filter by note type.
	 * @return array Array of notes.
	 */
	public function get_order_notes( $order_id, $note_type = null ) {
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return array();
		}

		// Get notes from meta.
		$notes = $this->get_order_meta( $order_id, '_order_notes' );

		// If no notes exist, return empty array.
		if ( empty( $notes ) || ! is_array( $notes ) ) {
			return array();
		}

		// Filter by note type if specified.
		if ( ! empty( $note_type ) ) {
			$notes = array_filter(
				$notes,
				function ( $note ) use ( $note_type ) {
					return isset( $note['type'] ) && $note['type'] === $note_type;
				}
			);
		}

		// Sort by created_at (newest first).
		usort(
			$notes,
			function ( $a, $b ) {
				$a_time = isset( $a['created_at'] ) ? strtotime( $a['created_at'] ) : 0;
				$b_time = isset( $b['created_at'] ) ? strtotime( $b['created_at'] ) : 0;
				return $b_time - $a_time; // Descending order (newest first).
			}
		);

		return array_values( $notes );
	}

	/**
	 * Delete a specific note from an order.
	 *
	 * @since 1.0.0
	 * @param int $order_id   Order ID.
	 * @param int $note_index Index of the note to delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete_order_note( $order_id, $note_index ) {
		$order_id   = absint( $order_id );
		$note_index = absint( $note_index );

		if ( ! $order_id || $note_index < 0 ) {
			return false;
		}

		// Get existing notes.
		$notes = $this->get_order_notes( $order_id );

		// Check if note index is valid.
		if ( ! isset( $notes[ $note_index ] ) ) {
			return false;
		}

		// Remove the note.
		unset( $notes[ $note_index ] );

		// Re-index array.
		$notes = array_values( $notes );

		// Save updated notes.
		return $this->update_order_meta( $order_id, '_order_notes', $notes );
	}

	/**
	 * Log an activity note automatically (e.g., status change).
	 *
	 * @since 1.0.0
	 * @param int    $order_id     Order ID.
	 * @param string $activity_message Activity message.
	 * @param int    $added_by     User ID who triggered the activity. Default 0 (system).
	 * @return bool|int Note index on success, false on failure.
	 */
	public function log_order_activity( $order_id, $activity_message, $added_by = 0 ) {
		return $this->add_order_note( $order_id, $activity_message, 'activity', $added_by );
	}

	/**
	 * Update order's updated_at timestamp.
	 *
	 * Used to refresh the order's last updated time without changing status.
	 * Useful for tracking verification attempts, activity updates, etc.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID.
	 * @return bool True on success, false on failure.
	 */
	public function touch_order( $order_id ) {
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return false;
		}

		// Update only the updated_at timestamp.
		return $this->update(
			array( 'updated_at' => current_time( 'mysql' ) ),
			array( 'id' => $order_id ),
			array( '%s' ),
			array( '%d' )
		);
	}
}
