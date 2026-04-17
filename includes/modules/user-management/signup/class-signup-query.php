<?php
/**
 * Signup query class.
 *
 * Handles signup database operations.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SkillPulse_LMS_Base_Query' ) ) {
	require_once SKILLPULSE_LMS_DIR_PATH . 'includes/modules/core/base/class-base-query.php';
}

/**
 * Signup query class.
 *
 * Handles signup database operations.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Signup_Query extends SkillPulse_LMS_Base_Query {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table_name Table name.
	 */
	protected function __construct( $table_name ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Constructor override needed for singleton pattern.
		parent::__construct( $table_name );
	}

	/**
	 * Get the instance of this class.
	 *
	 * @return SkillPulse_LMS_Signup_Query
	 */
	public static function get_instance() {
		return parent::get_base_instance( __CLASS__, 'splms_signups' );
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'splms_delete_signup', array( $this, 'handle_signup_deletion' ) );
	}

	/**
	 * Handle signup deletion.
	 *
	 * @since 1.0.0
	 *
	 * @param int $signup_id Signup ID.
	 *
	 * @return void
	 */
	public function handle_signup_deletion( $signup_id ) {
		// Additional cleanup can be added here.
		do_action( 'splms_signup_deleted', $signup_id );
	}

	/**
	 * Insert signup.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Signup data.
	 *
	 * @return int|false Insert ID on success, false on failure.
	 */
	public function insert_signup( $args ) {
		$defaults = array(
			'user_login'     => '',
			'user_email'     => '',
			'user_name'      => '',
			'first_name'     => '',
			'last_name'      => '',
			'user_type'      => 'student',
			'meta'           => '',
			'registered'     => current_time( 'mysql' ),
			'activation_key' => '',
			'status'         => 'pending',
		);

		$args = wp_parse_args( $args, $defaults );

		// Serialize meta if it's an array.
		if ( is_array( $args['meta'] ) ) {
			$args['meta'] = maybe_serialize( $args['meta'] );
		}

		return $this->insert(
			$args,
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Update signup.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $signup_id Signup ID.
	 * @param array $args      Update data.
	 *
	 * @return int|false Number of rows updated on success, false on failure.
	 */
	public function update_signup( $signup_id, $args ) {
		$allowed_fields = array(
			'user_login',
			'user_email',
			'user_name',
			'first_name',
			'last_name',
			'user_type',
			'meta',
			'activation_key',
			'status',
		);

		$update_data = array();
		$format      = array();

		foreach ( $allowed_fields as $field ) {
			if ( isset( $args[ $field ] ) ) {
				$update_data[ $field ] = $args[ $field ];
				$format[]              = '%s';
			}
		}

		// Serialize meta if it's an array.
		if ( isset( $update_data['meta'] ) && is_array( $update_data['meta'] ) ) {
			$update_data['meta'] = maybe_serialize( $update_data['meta'] );
		}

		if ( empty( $update_data ) ) {
			return false;
		}

		return $this->update(
			$update_data,
			array( 'id' => $signup_id ),
			$format,
			array( '%d' )
		);
	}

	/**
	 * Delete signup.
	 *
	 * @since 1.0.0
	 *
	 * @param int|array $signup_ids Signup IDs.
	 *
	 * @return int|false Number of rows deleted on success, false on failure.
	 */
	public function delete_signup( $signup_ids ) {
		global $wpdb;

		if ( ! is_array( $signup_ids ) ) {
			$signup_ids = array( $signup_ids );
		}

		$signup_ids   = array_map( 'intval', $signup_ids );
		$placeholders = implode( ',', array_fill( 0, count( $signup_ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table_name is safe, placeholders are used.
		$sql = "DELETE FROM {$this->table_name} WHERE id IN ($placeholders)";

		if ( empty( $signup_ids ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- No parameters needed for this query.
			$result = $wpdb->query( $sql );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared on next line.
			$result = $wpdb->query( $wpdb->prepare( $sql, ...$signup_ids ) );
		}

		return ( false !== $result ) ? $result : false;
	}

	/**
	 * Get signup by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $signup_id Signup ID.
	 *
	 * @return object|null
	 */
	public function get_signup( $signup_id ) {
		$query  = "SELECT * FROM {$this->table_name} WHERE id = %d";
		$signup = $this->get_row( $query, array( $signup_id ) );

		if ( $signup ) {
			$signup->meta = maybe_unserialize( $signup->meta );
		}

		return $signup;
	}

	/**
	 * Get signup by activation key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $activation_key Activation key.
	 *
	 * @return object|null
	 */
	public function get_signup_by_key( $activation_key ) {
		$query  = "SELECT * FROM {$this->table_name} WHERE activation_key = %s";
		$signup = $this->get_row( $query, array( $activation_key ) );

		if ( $signup ) {
			$signup->meta = maybe_unserialize( $signup->meta );
		}

		return $signup;
	}

	/**
	 * Get signup by email.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email Email address.
	 *
	 * @return object|null
	 */
	public function get_signup_by_email( $email ) {
		$query  = "SELECT * FROM {$this->table_name} WHERE user_email = %s";
		$signup = $this->get_row( $query, array( $email ) );

		if ( $signup ) {
			$signup->meta = maybe_unserialize( $signup->meta );
		}

		return $signup;
	}

	/**
	 * Get signup by user login.
	 *
	 * @since 1.0.0
	 *
	 * @param string $user_login User login.
	 *
	 * @return object|null
	 */
	public function get_signup_by_login( $user_login ) {
		$query  = "SELECT * FROM {$this->table_name} WHERE user_login = %s";
		$signup = $this->get_row( $query, array( $user_login ) );

		if ( $signup ) {
			$signup->meta = maybe_unserialize( $signup->meta );
		}

		return $signup;
	}

	/**
	 * Get signups by user type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $user_type User type.
	 *
	 * @return array
	 */
	public function get_signups_by_type( $user_type ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table_name is safe.
		$query = "SELECT * FROM {$this->table_name} WHERE user_type = %s ORDER BY registered DESC";

		$results = $this->get_results( $query, array( $user_type ) );

		// Unserialize meta for each signup.
		foreach ( $results as &$signup ) {
			$signup->meta = maybe_unserialize( $signup->meta );
		}

		return $results;
	}

	/**
	 * Get all signups with optional filters.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	public function get_signups( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'number'         => 20,
			'offset'         => 0,
			'orderby'        => 'registered',
			'order'          => 'DESC',
			'status'         => '',
			'user_type'      => '',
			'user_login'     => '',
			'user_email'     => '',
			'activation_key' => '',
			'include'        => array(),
			'usersearch'     => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$where_conditions = array();
		$where_values     = array();

		// Individual field filters.
		if ( ! empty( $args['user_login'] ) ) {
			$where_conditions[] = 'user_login = %s';
			$where_values[]     = $args['user_login'];
		}

		if ( ! empty( $args['user_email'] ) ) {
			$where_conditions[] = 'user_email = %s';
			$where_values[]     = $args['user_email'];
		}

		if ( ! empty( $args['activation_key'] ) ) {
			$where_conditions[] = 'activation_key = %s';
			$where_values[]     = $args['activation_key'];
		}

		// Include specific IDs.
		if ( ! empty( $args['include'] ) ) {
			$include_ids        = array_map( 'intval', $args['include'] );
			$where_conditions[] = 'id IN (' . implode( ',', array_fill( 0, count( $include_ids ), '%d' ) ) . ')';
			$where_values       = array_merge( $where_values, $include_ids );
		}

		// Status filter.
		if ( ! empty( $args['status'] ) ) {
			$where_conditions[] = 'status = %s';
			$where_values[]     = $args['status'];
		}

		// User type filter.
		if ( ! empty( $args['user_type'] ) ) {
			$where_conditions[] = 'user_type = %s';
			$where_values[]     = $args['user_type'];
		}

		// Search filter (usersearch parameter).
		if ( ! empty( $args['usersearch'] ) ) {
			$search_term        = '%' . $wpdb->esc_like( $args['usersearch'] ) . '%';
			$where_conditions[] = '(user_login LIKE %s OR user_email LIKE %s OR user_name LIKE %s)';
			$where_values[]     = $search_term;
			$where_values[]     = $search_term;
			$where_values[]     = $search_term;
		}

		// Build WHERE clause.
		$where_clause = '';
		if ( ! empty( $where_conditions ) ) {
			$where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );
		}

		// Build ORDER BY clause.
		$orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );

		// Build LIMIT clause.
		$limit = '';
		if ( $args['number'] > 0 ) {
			$limit = $wpdb->prepare( 'LIMIT %d, %d', $args['offset'], $args['number'] );
		}

		// Get total count.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table_name is safe.
		$count_query = "SELECT COUNT(*) FROM {$this->table_name} $where_clause";
		if ( ! empty( $where_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared with $wpdb->prepare().
			$count_query = $wpdb->prepare( $count_query, ...$where_values );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		$total = $wpdb->get_var( $count_query );

		// Get signups.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table_name is safe, orderby is sanitized.
		$query = "SELECT * FROM {$this->table_name} $where_clause ORDER BY $orderby $limit";
		if ( ! empty( $where_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared with $wpdb->prepare().
			$query = $wpdb->prepare( $query, ...$where_values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above.
		$signups = $wpdb->get_results( $query );

		// Unserialize meta for each signup.
		foreach ( $signups as &$signup ) {
			$signup->meta = maybe_unserialize( $signup->meta );
		}

		return array(
			'signups' => $signups,
			'total'   => $total,
		);
	}

	/**
	 * Count signups with optional filters.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query arguments.
	 *
	 * @return int
	 */
	public function count_signups( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'status'    => '',
			'user_type' => '',
			'search'    => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$where_conditions = array();
		$where_values     = array();

		// Status filter.
		if ( ! empty( $args['status'] ) ) {
			$where_conditions[] = 'status = %s';
			$where_values[]     = $args['status'];
		}

		// User type filter.
		if ( ! empty( $args['user_type'] ) ) {
			$where_conditions[] = 'user_type = %s';
			$where_values[]     = $args['user_type'];
		}

		// Search filter.
		if ( ! empty( $args['search'] ) ) {
			$search_term        = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where_conditions[] = '(user_login LIKE %s OR user_email LIKE %s OR user_name LIKE %s OR first_name LIKE %s OR last_name LIKE %s)';
			$where_values[]     = $search_term;
			$where_values[]     = $search_term;
			$where_values[]     = $search_term;
			$where_values[]     = $search_term;
			$where_values[]     = $search_term;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table_name is safe.
		$sql = "SELECT COUNT(*) FROM {$this->table_name}";

		if ( ! empty( $where_conditions ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where_conditions );
		}

		if ( empty( $where_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- No parameters needed for this query.
			return (int) $wpdb->get_var( $sql );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared on next line.
			return (int) $wpdb->get_var( $wpdb->prepare( $sql, ...$where_values ) );
		}
	}

	/**
	 * Update signup status.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $signup_id Signup ID.
	 * @param string $status    New status.
	 *
	 * @return int|false Number of rows updated on success, false on failure.
	 */
	public function update_signup_status( $signup_id, $status ) {
		return $this->update(
			array( 'status' => $status ),
			array( 'id' => $signup_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Check if email already exists in signups.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email Email address.
	 *
	 * @return bool
	 */
	public function email_exists( $email ) {
		$signup = $this->get_signup_by_email( $email );
		return ! empty( $signup );
	}

	/**
	 * Check if user login already exists in signups.
	 *
	 * @since 1.0.0
	 *
	 * @param string $user_login User login.
	 *
	 * @return bool
	 */
	public function login_exists( $user_login ) {
		$signup = $this->get_signup_by_login( $user_login );
		return ! empty( $signup );
	}

	/**
	 * Get pending signups count.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_pending_signups_count() {
		return $this->count_signups( array( 'status' => 'pending' ) );
	}
}
