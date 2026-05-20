<?php
/**
 * Base Query Class
 *
 * Base class for database query operations.
 * Includes object caching for read operations and cache invalidation for writes.
 *
 * @package    SkillPulse_LMS
 * @subpackage Core
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Base Query Class
 *
 * @since 1.0.0
 */
class SPLMS_Base_Query {

	/**
	 * Table name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $table_name;

	/**
	 * Cache group name derived from table name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $cache_group;

	/**
	 * Class instances.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table_name Table name.
	 */
	protected function __construct( $table_name ) {
		global $wpdb;
		$this->table_name  = $wpdb->prefix . $table_name;
		$this->cache_group = 'splms_' . $table_name;
	}

	/**
	 * Get base instance.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class_name  Class name.
	 * @param string $table_name  Table name.
	 * @return SPLMS_Base_Query Instance.
	 */
	public static function get_base_instance( $class_name, $table_name ) {
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name( $table_name );
			self::$instances[ $class_name ]->register_hooks();
		}

		return self::$instances[ $class_name ];
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function register_hooks() {
		// Child classes can override this method to register hooks.
	}

	/**
	 * Generate a cache key from query and arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query SQL query.
	 * @param array  $args  Query arguments.
	 * @return string Cache key.
	 */
	protected function get_cache_key( $query, $args = array() ) {
		return md5( $query . wp_json_encode( $args ) );
	}

	/**
	 * Invalidate all cached queries for this table.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function invalidate_cache() {
		wp_cache_incr( 'last_changed', 1, $this->cache_group );
	}

	/**
	 * Get the cache version for building versioned cache keys.
	 *
	 * @since 1.0.0
	 *
	 * @return string Cache version string.
	 */
	private function get_cache_version() {
		$last_changed = wp_cache_get( 'last_changed', $this->cache_group );
		if ( false === $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, $this->cache_group );
		}
		return $last_changed;
	}

	/**
	 * Insert data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data   Data to insert.
	 * @param array $format Format array.
	 * @return int|false Insert ID or false on failure.
	 */
	protected function insert( $data, $format ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table requires direct DB access.
		$wpdb->insert( $this->table_name, $data, $format );

		$this->invalidate_cache();

		return $wpdb->insert_id;
	}

	/**
	 * Update data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data         Data to update.
	 * @param array $where        Where conditions.
	 * @param array $format       Format array.
	 * @param array $where_format Where format array.
	 * @return int|false Number of rows updated or false on failure.
	 */
	protected function update( $data, $where, $format, $where_format ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write; cache invalidated below.
		$result = $wpdb->update( $this->table_name, $data, $where, $format, $where_format );

		$this->invalidate_cache();

		return $result;
	}

	/**
	 * Delete data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $where        Where conditions.
	 * @param array $where_format Where format array.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	protected function delete( $where, $where_format ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write; cache invalidated below.
		$result = $wpdb->delete( $this->table_name, $where, $where_format );

		$this->invalidate_cache();

		return $result;
	}

	/**
	 * Get results with object caching.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query SQL query with placeholders.
	 * @param array  $args  Query arguments.
	 * @return array|null Database query results.
	 */
	protected function get_results( $query, $args = array() ) {
		global $wpdb;

		$cache_key = $this->get_cache_key( $query, $args ) . ':' . $this->get_cache_version();
		$results   = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $results ) {
			return $results;
		}

		if ( empty( $args ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query has no placeholders; caching handled above.
			$results = $wpdb->get_results( $query );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query prepared with $wpdb->prepare(); caching handled above.
			$results = $wpdb->get_results( $wpdb->prepare( $query, ...$args ) );
		}

		wp_cache_set( $cache_key, $results, $this->cache_group );

		return $results;
	}

	/**
	 * Get single row with object caching.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query SQL query with placeholders.
	 * @param array  $args  Query arguments.
	 * @return object|null Database query row.
	 */
	protected function get_row( $query, $args = array() ) {
		global $wpdb;

		$cache_key = $this->get_cache_key( $query, $args ) . ':' . $this->get_cache_version();
		$result    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $result ) {
			return $result;
		}

		if ( empty( $args ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query has no placeholders; caching handled above.
			$result = $wpdb->get_row( $query );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query prepared with $wpdb->prepare(); caching handled above.
			$result = $wpdb->get_row( $wpdb->prepare( $query, ...$args ) );
		}

		// Cache null results as empty string to distinguish from cache miss.
		wp_cache_set( $cache_key, null === $result ? '' : $result, $this->cache_group );

		return $result;
	}

	/**
	 * Get single variable with object caching.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query SQL query with placeholders.
	 * @param array  $args  Query arguments.
	 * @return string|null Database query variable.
	 */
	protected function get_var( $query, $args = array() ) {
		global $wpdb;

		$cache_key = $this->get_cache_key( $query, $args ) . ':' . $this->get_cache_version();
		$result    = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $result ) {
			return '' === $result ? null : $result;
		}

		if ( empty( $args ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query has no placeholders; caching handled above.
			$result = $wpdb->get_var( $query );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query prepared with $wpdb->prepare(); caching handled above.
			$result = $wpdb->get_var( $wpdb->prepare( $query, ...$args ) );
		}

		wp_cache_set( $cache_key, null === $result ? '' : $result, $this->cache_group );

		return $result;
	}

	/**
	 * Get column values with object caching.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query SQL query with placeholders.
	 * @param array  $args  Query arguments.
	 * @return array Database query column values.
	 */
	protected function get_col( $query, $args = array() ) {
		global $wpdb;

		$cache_key = $this->get_cache_key( $query, $args ) . ':' . $this->get_cache_version();
		$results   = wp_cache_get( $cache_key, $this->cache_group );

		if ( false !== $results ) {
			return $results;
		}

		if ( empty( $args ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query has no placeholders; caching handled above.
			$results = $wpdb->get_col( $query );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query prepared with $wpdb->prepare(); caching handled above.
			$results = $wpdb->get_col( $wpdb->prepare( $query, ...$args ) );
		}

		wp_cache_set( $cache_key, $results, $this->cache_group );

		return $results;
	}

	/**
	 * Get table name.
	 *
	 * @since 1.0.0
	 *
	 * @return string Table name.
	 */
	public function get_table_name() {
		return $this->table_name;
	}
}
