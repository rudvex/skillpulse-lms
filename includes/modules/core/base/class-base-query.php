<?php
/**
 * Base Query Class
 *
 * Base class for database query operations.
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
class SkillPulse_LMS_Base_Query {

	/**
	 * Table name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $table_name;

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
		$this->table_name = $wpdb->prefix . $table_name;
	}

	/**
	 * Get base instance.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class_name  Class name.
	 * @param string $table_name  Table name.
	 * @return SkillPulse_LMS_Base_Query Instance.
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
		$wpdb->insert( $this->table_name, $data, $format );

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

		return $wpdb->update( $this->table_name, $data, $where, $format, $where_format );
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

		return $wpdb->delete( $this->table_name, $where, $where_format );
	}

	/**
	 * Get results.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query SQL query with placeholders.
	 * @param array  $args  Query arguments.
	 * @return array|null Database query results.
	 */
	protected function get_results( $query, $args = array() ) {
		global $wpdb;

		if ( empty( $args ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query has no placeholders when args is empty.
			return $wpdb->get_results( $query );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared with $wpdb->prepare() and spread operator.
		return $wpdb->get_results( $wpdb->prepare( $query, ...$args ) );
	}

	/**
	 * Get single row.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query SQL query with placeholders.
	 * @param array  $args  Query arguments.
	 * @return object|null Database query row.
	 */
	protected function get_row( $query, $args = array() ) {
		global $wpdb;

		if ( empty( $args ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query has no placeholders when args is empty.
			return $wpdb->get_row( $query );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared with $wpdb->prepare() and spread operator.
		return $wpdb->get_row( $wpdb->prepare( $query, ...$args ) );
	}

	/**
	 * Get single variable.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query SQL query with placeholders.
	 * @param array  $args  Query arguments.
	 * @return string|null Database query variable.
	 */
	protected function get_var( $query, $args = array() ) {
		global $wpdb;

		if ( empty( $args ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query has no placeholders when args is empty.
			return $wpdb->get_var( $query );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared with $wpdb->prepare() and spread operator.
		return $wpdb->get_var( $wpdb->prepare( $query, ...$args ) );
	}

	/**
	 * Get column values.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query SQL query with placeholders.
	 * @param array  $args  Query arguments.
	 * @return array Database query column values.
	 */
	protected function get_col( $query, $args = array() ) {
		global $wpdb;

		if ( empty( $args ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query has no placeholders when args is empty.
			return $wpdb->get_col( $query );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared with $wpdb->prepare() and spread operator.
		return $wpdb->get_col( $wpdb->prepare( $query, ...$args ) );
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
