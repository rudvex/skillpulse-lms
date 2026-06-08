<?php
/**
 * Section Access Query Class
 *
 * Handles section access database operations.
 *
 * @package SPLMS
 * @subpackage Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'SPLMS_Base_Query' ) ) {
	require_once SPLMS_DIR_PATH . 'includes/modules/core/base/class-base-query.php';
}

/**
 * Class SPLMS_Section_Access_Query
 *
 * @since 1.0.0
 */
class SPLMS_Section_Access_Query extends SPLMS_Base_Query {

	/**
	 * Request-level cache for section access queries.
	 *
	 * @since 1.0.0
	 *
	 * @var array Cache array keyed by "user_id_section_id".
	 */
	private static $access_cache = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table_name Table name.
	 * @return void
	 */
	protected function __construct( $table_name ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Required for singleton pattern implementation.
		parent::__construct( $table_name );
	}

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Section_Access_Query The class instance.
	 */
	public static function get_instance() {
		return parent::get_base_instance( __CLASS__, 'splms_section_access' );
	}

	/**
	 * Grant section access to a user.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id      User ID.
	 * @param int    $section_id   Section ID.
	 * @param int    $course_id    Course ID.
	 * @param string $access_type  Access type (purchase, enrollment, etc).
	 * @param string $expires_at   Expiration date (optional).
	 * @param int    $order_id     Order ID (optional).
	 * @return int|false Access ID on success, false on failure.
	 */
	public function grant_access( $user_id, $section_id, $course_id, $access_type = 'purchase', $expires_at = null, $order_id = null ) {
		// Check if access already exists.
		$existing_access = $this->get_user_section_access( $user_id, $section_id );

		$access_data = array(
			'user_id'     => $user_id,
			'section_id'  => $section_id,
			'course_id'   => $course_id,
			'access_type' => $access_type,
			'granted_at'  => current_time( 'mysql' ),
			'expires_at'  => $expires_at,
			'order_id'    => $order_id,
		);

		$format = array( '%d', '%d', '%d', '%s', '%s', '%s', '%d' );

		if ( $existing_access ) {
			// Update existing access.
			$result = $this->update(
				$access_data,
				array(
					'user_id'    => $user_id,
					'section_id' => $section_id,
				),
				$format,
				array( '%d', '%d' )
			);

			// Clear cache for this access.
			$cache_key = $user_id . '_' . $section_id;
			unset( self::$access_cache[ $cache_key ] );

			return $result ? $existing_access->id : false;
		} else {
			// Insert new access.
			$access_id = $this->insert( $access_data, $format );

			// Clear cache for this access.
			$cache_key = $user_id . '_' . $section_id;
			unset( self::$access_cache[ $cache_key ] );

			return $access_id;
		}
	}

	/**
	 * Check if user has access to a section.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id    User ID.
	 * @param int $section_id Section ID.
	 * @return bool True if user has access, false otherwise.
	 */
	public function user_has_access( $user_id, $section_id ) {
		// Check cache first.
		$cache_key = $user_id . '_' . $section_id;
		if ( isset( self::$access_cache[ $cache_key ] ) ) {
			return self::$access_cache[ $cache_key ];
		}

		$access_record = $this->get_user_section_access( $user_id, $section_id );

		if ( ! $access_record ) {
			self::$access_cache[ $cache_key ] = false;
			return false;
		}

		// Check if access has expired.
		if ( $access_record->expires_at && strtotime( $access_record->expires_at ) < time() ) {
			self::$access_cache[ $cache_key ] = false;
			return false;
		}

		self::$access_cache[ $cache_key ] = true;
		return true;
	}

	/**
	 * Get user's section access record.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id    User ID.
	 * @param int $section_id Section ID.
	 * @return object|null Access record or null if not found.
	 */
	public function get_user_section_access( $user_id, $section_id ) {
		$query = "SELECT * FROM {$this->table_name} WHERE user_id = %d AND section_id = %d LIMIT 1";
		return $this->get_row( $query, array( $user_id, $section_id ) );
	}

	/**
	 * Get user's purchased sections for a course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return array Array of section IDs user has access to.
	 */
	public function get_user_course_sections( $user_id, $course_id ) {
		$query   = "SELECT section_id FROM {$this->table_name} WHERE user_id = %d AND course_id = %d";
		$results = $this->get_results( $query, array( $user_id, $course_id ) );

		if ( ! $results ) {
			return array();
		}

		return array_map( 'intval', wp_list_pluck( $results, 'section_id' ) );
	}

	/**
	 * Get section access records by order ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id Order ID.
	 * @return array Array of access records.
	 */
	public function get_access_by_order( $order_id ) {
		$query = "SELECT * FROM {$this->table_name} WHERE order_id = %d";
		return $this->get_results( $query, array( $order_id ) );
	}

	/**
	 * Revoke section access from a user.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id    User ID.
	 * @param int $section_id Section ID.
	 * @return bool True on success, false on failure.
	 */
	public function revoke_access( $user_id, $section_id ) {
		$result = $this->delete(
			array(
				'user_id'    => $user_id,
				'section_id' => $section_id,
			),
			array( '%d', '%d' )
		);

		// Clear cache for this access.
		$cache_key = $user_id . '_' . $section_id;
		unset( self::$access_cache[ $cache_key ] );

		return false !== $result;
	}

	/**
	 * Revoke all section access for an order.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id Order ID.
	 * @return bool True on success, false on failure.
	 */
	public function revoke_access_by_order( $order_id ) {
		// Get access records to clear cache.
		$access_records = $this->get_access_by_order( $order_id );

		$result = $this->delete(
			array( 'order_id' => $order_id ),
			array( '%d' )
		);

		// Clear cache for affected access records.
		foreach ( $access_records as $record ) {
			$cache_key = $record->user_id . '_' . $record->section_id;
			unset( self::$access_cache[ $cache_key ] );
		}

		return false !== $result;
	}

	/**
	 * Update section access expiration.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id    User ID.
	 * @param int    $section_id Section ID.
	 * @param string $expires_at New expiration date.
	 * @return bool True on success, false on failure.
	 */
	public function update_expiration( $user_id, $section_id, $expires_at ) {
		$result = $this->update(
			array( 'expires_at' => $expires_at ),
			array(
				'user_id'    => $user_id,
				'section_id' => $section_id,
			),
			array( '%s' ),
			array( '%d', '%d' )
		);

		// Clear cache for this access.
		$cache_key = $user_id . '_' . $section_id;
		unset( self::$access_cache[ $cache_key ] );

		return false !== $result;
	}

	/**
	 * Get expired section access records.
	 *
	 * @since 1.0.0
	 *
	 * @param int $limit Limit number of results (optional).
	 * @return array Array of expired access records.
	 */
	public function get_expired_access( $limit = null ) {
		$query = "SELECT * FROM {$this->table_name} WHERE expires_at IS NOT NULL AND expires_at < %s ORDER BY expires_at ASC";
		$args  = array( current_time( 'mysql' ) );

		if ( $limit ) {
			$query .= ' LIMIT %d';
			$args[] = $limit;
		}

		return $this->get_results( $query, $args );
	}

	/**
	 * Cleanup expired section access records.
	 *
	 * @since 1.0.0
	 *
	 * @return int Number of records cleaned up.
	 */
	public function cleanup_expired_access() {
		global $wpdb;

		// Get expired records first to clear cache.
		$expired_records = $this->get_expired_access();

		// Clear cache for expired records.
		foreach ( $expired_records as $record ) {
			$cache_key = $record->user_id . '_' . $record->section_id;
			unset( self::$access_cache[ $cache_key ] );
		}

		// Delete expired records.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Cleanup operation.
		$deleted = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safely constructed in constructor.
				"DELETE FROM {$this->table_name} WHERE expires_at IS NOT NULL AND expires_at < %s",
				current_time( 'mysql' )
			)
		);

		return $deleted ? $deleted : 0;
	}

	/**
	 * Get section access statistics.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID (optional).
	 * @return array Access statistics.
	 */
	public function get_access_stats( $section_id = null ) {
		$where_clause = '';
		$args         = array();

		if ( $section_id ) {
			$where_clause = 'WHERE section_id = %d';
			$args[]       = $section_id;
		}

		$query = "SELECT
			COUNT(*) as total_access,
			COUNT(CASE WHEN expires_at IS NULL OR expires_at > %s THEN 1 END) as active_access,
			COUNT(CASE WHEN expires_at IS NOT NULL AND expires_at <= %s THEN 1 END) as expired_access
			FROM {$this->table_name} {$where_clause}";

		// Add current time twice for active and expired checks.
		$current_time = current_time( 'mysql' );
		array_unshift( $args, $current_time, $current_time );

		$result = $this->get_row( $query, $args );

		return array(
			'total_access'   => (int) $result->total_access,
			'active_access'  => (int) $result->active_access,
			'expired_access' => (int) $result->expired_access,
		);
	}
}
