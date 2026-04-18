<?php
/**
 * Database Migration Management Class
 *
 * Handles all database schema migrations for SkillPulse LMS plugin.
 * Provides safe, logged, and reversible database upgrades.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Migration Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Migration {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var SkillPulse_LMS_Migration|null $instance
	 */
	private static $instance;

	/**
	 * Migration log table name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $log_table;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Migration The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		global $wpdb;
		$this->log_table = $wpdb->prefix . 'splms_migration_log';
		$this->create_migration_log_table();
	}

	/**
	 * Run all pending migrations.
	 *
	 * @since 1.0.0
	 *
	 * @return array Migration results.
	 */
	public function run_migrations() {
		$current_version = get_option( 'splms_db_version', 0 );
		$target_version  = SKILLPULSE_LMS_DB_VERSION;
		$results         = array();

		$this->log_migration_start( $current_version, $target_version );

		// Run migrations sequentially.
		for ( $version = $current_version + 1; $version <= $target_version; $version++ ) {
			$method_name = "upgrade_to_version_{$version}";

			if ( method_exists( $this, $method_name ) ) {
				$this->log_message( "Starting migration to version {$version}" );

				try {
					$result              = $this->execute_migration( $version, $method_name );
					$results[ $version ] = $result;

					if ( $result['success'] ) {
						$this->log_message( "Migration to version {$version} completed successfully" );
						update_option( 'splms_db_version', $version );
					} else {
						$this->log_error( "Migration to version {$version} failed: {$result['error']}" );
						break; // Stop on first failure.
					}
				} catch ( Exception $e ) {
					$error_message = "Migration to version {$version} threw exception: " . $e->getMessage();
					$this->log_error( $error_message );
					$results[ $version ] = array(
						'success' => false,
						'error'   => $error_message,
					);
					break;
				}
			}
		}

		$this->log_migration_complete( get_option( 'splms_db_version' ) );

		return $results;
	}

	/**
	 * Execute a single migration with safety features.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $version     Migration version.
	 * @param string $method_name Migration method name.
	 * @return array Migration result.
	 * @throws Exception When migration fails or backup cannot be created.
	 */
	private function execute_migration( $version, $method_name ) {
		global $wpdb;

		// Start transaction.
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Create backup before migration.
			$backup_created = $this->create_backup( $version );

			if ( ! $backup_created ) {
				throw new Exception( 'Failed to create database backup' );
			}

			// Execute the migration.
			$migration_result = $this->$method_name();

			if ( ! $migration_result ) {
				throw new Exception( 'Migration method returned false' );
			}

			// Commit transaction.
			$wpdb->query( 'COMMIT' );

			return array(
				'success'        => true,
				'backup_created' => true,
				'message'        => "Migration to version {$version} completed successfully",
			);

		} catch ( Exception $e ) {
			// Rollback transaction.
			$wpdb->query( 'ROLLBACK' );

			return array(
				'success'        => false,
				'backup_created' => isset( $backup_created ) ? $backup_created : false,
				'error'          => $e->getMessage(),
			);
		}
	}

	/**
	 * Create backup of affected tables before migration.
	 *
	 * @since 1.0.0
	 *
	 * @param int $version Migration version.
	 * @return bool True on success, false on failure.
	 */
	private function create_backup( $version ) {
		global $wpdb;

		$timestamp     = wp_date( 'Y-m-d_H-i-s' );
		$backup_tables = $this->get_backup_tables_for_version( $version );

		foreach ( $backup_tables as $table ) {
			$backup_table = "{$table}_backup_v{$version}_{$timestamp}";

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are from internal config, not user input.
			$result = $wpdb->query( 'CREATE TABLE `' . esc_sql( $backup_table ) . '` AS SELECT * FROM `' . esc_sql( $table ) . '`' );

			if ( false === $result ) {
				$this->log_error( "Failed to backup table {$table} to {$backup_table}" );
				return false;
			}

			$this->log_message( "Created backup table {$backup_table}" );
		}

		return true;
	}

	/**
	 * Get tables that need backup for specific version.
	 *
	 * @since 1.0.0
	 *
	 * @param int $version Migration version.
	 * @return array Table names to backup.
	 */
	private function get_backup_tables_for_version( $version ) {
		global $wpdb;

		// Define which tables each migration affects.
		$version_tables = array(
			3 => array(
				$wpdb->prefix . 'splms_notifications',
				$wpdb->prefix . 'splms_enrollments',
			),
			4 => array(
				$wpdb->prefix . 'splms_quiz_attempts',
			),
			// Add more versions as needed.
		);

		return isset( $version_tables[ $version ] ) ? $version_tables[ $version ] : array();
	}

	/**
	 * Create migration log table.
	 *
	 * @since 1.0.0
	 */
	private function create_migration_log_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->log_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			migration_version int(11) NOT NULL,
			action varchar(50) NOT NULL,
			message text NOT NULL,
			level varchar(20) DEFAULT 'info',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY migration_version (migration_version),
			KEY action (action),
			KEY level (level),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Log migration start.
	 *
	 * @since 1.0.0
	 *
	 * @param int $current_version Current database version.
	 * @param int $target_version  Target database version.
	 */
	private function log_migration_start( $current_version, $target_version ) {
		$message = "Migration batch started: version {$current_version} to {$target_version}";
		$this->log_message( $message, 'migration_start', 0 );
	}

	/**
	 * Log migration completion.
	 *
	 * @since 1.0.0
	 *
	 * @param int $final_version Final database version achieved.
	 */
	private function log_migration_complete( $final_version ) {
		$message = "Migration batch completed: final version {$final_version}";
		$this->log_message( $message, 'migration_complete', 0 );
	}

	/**
	 * Log a migration message.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message   Log message.
	 * @param string $action    Action type.
	 * @param int    $version   Migration version (0 for general).
	 * @param string $level     Log level (info, warning, error).
	 */
	private function log_message( $message, $action = 'migration', $version = 0, $level = 'info' ) {
		global $wpdb;

		if ( 0 === $version ) {
			$version = get_option( 'splms_db_version', 0 );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Migration logging is essential.
		$wpdb->insert(
			$this->log_table,
			array(
				'migration_version' => $version,
				'action'            => $action,
				'message'           => $message,
				'level'             => $level,
				'created_at'        => wp_date( 'Y-m-d H:i:s' ),
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		// Also log to WordPress debug log if enabled.
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging when enabled.
			error_log( "SPLMS Migration [{$level}]: {$message}" );
		}
	}

	/**
	 * Log an error message.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message Error message.
	 * @param int    $version Migration version.
	 */
	private function log_error( $message, $version = 0 ) {
		$this->log_message( $message, 'migration_error', $version, 'error' );
	}

	/**
	 * Get migration logs.
	 *
	 * @since 1.0.0
	 *
	 * @param int $limit Number of logs to retrieve.
	 * @param int $version Specific version to filter (0 for all).
	 * @return array Migration logs.
	 */
	public function get_migration_logs( $limit = 100, $version = 0 ) {
		global $wpdb;

		$where_clause = '';
		$prepare_args = array();

		if ( $version > 0 ) {
			$where_clause   = ' WHERE migration_version = %d';
			$prepare_args[] = $version;
		}

		$prepare_args[] = $limit;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe.
		$query = "SELECT * FROM {$this->log_table}{$where_clause} ORDER BY created_at DESC LIMIT %d";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query is prepared.
		return $wpdb->get_results( $wpdb->prepare( $query, ...$prepare_args ) );
	}

	/**
	 * Check if migration is needed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if migration is needed.
	 */
	public function is_migration_needed() {
		$current_version = get_option( 'splms_db_version', 0 );
		return $current_version < SKILLPULSE_LMS_DB_VERSION;
	}

	/**
	 * Get migration status.
	 *
	 * @since 1.0.0
	 *
	 * @return array Migration status information.
	 */
	public function get_migration_status() {
		$current_version = get_option( 'splms_db_version', 0 );
		$target_version  = SKILLPULSE_LMS_DB_VERSION;

		return array(
			'current_version' => $current_version,
			'target_version'  => $target_version,
			'is_needed'       => $current_version < $target_version,
			'pending_count'   => max( 0, $target_version - $current_version ),
		);
	}

	/**
	 * Clean up old backup tables (optional maintenance function).
	 *
	 * @since 1.0.0
	 *
	 * @param int $days_to_keep Number of days to keep backup tables.
	 */
	public function cleanup_old_backups( $days_to_keep = 30 ) {
		global $wpdb;

		// Get all backup tables.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Maintenance operation.
		$tables = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT table_name FROM information_schema.tables
				WHERE table_schema = %s AND table_name LIKE %s',
				DB_NAME,
				$wpdb->prefix . 'splms_%_backup_%'
			)
		);

		$cutoff_date = wp_date( 'Y-m-d', strtotime( "-{$days_to_keep} days" ) );

		foreach ( $tables as $table ) {
			// Extract date from table name (format: tablename_backup_v3_2024-01-15_10-30-45).
			if ( preg_match( '/(\d{4}-\d{2}-\d{2})/', $table->table_name, $matches ) ) {
				$table_date = $matches[1];

				if ( $table_date < $cutoff_date ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Cleanup operation, table name from SHOW TABLES query.
					$wpdb->query( 'DROP TABLE IF EXISTS `' . esc_sql( $table->table_name ) . '`' );
					$this->log_message( "Cleaned up old backup table: {$table->table_name}" );
				}
			}
		}
	}

	/**
	 * Upgrade database to version 2.
	 *
	 * Add indexes and optimize existing tables.
	 *
	 * @since 1.0.0
	 *
	 * @return array Migration result.
	 */
	protected function upgrade_to_version_2() {
		global $wpdb;

		$success  = true;
		$messages = array();

		// Fix relationships table unique constraint to include child_type.
		$relationships_table = esc_sql( $wpdb->prefix . 'splms_relationships' );

		// Drop old unique constraint if it exists.
		$existing_indexes = $wpdb->get_results(
			$wpdb->prepare( 'SHOW INDEX FROM `' . esc_sql( $relationships_table ) . '` WHERE Key_name = %s', 'parent_child_unique' ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from esc_sql().
			ARRAY_A
		);

		if ( ! empty( $existing_indexes ) ) {
			$wpdb->query( "ALTER TABLE `{$relationships_table}` DROP INDEX parent_child_unique" ); // phpcs:ignore
			$messages[] = 'Dropped old parent_child_unique constraint';
		}

		// Add new unique constraint that includes child_type.
		$existing_new_indexes = $wpdb->get_results(
			$wpdb->prepare( 'SHOW INDEX FROM `' . esc_sql( $relationships_table ) . '` WHERE Key_name = %s', 'parent_child_type_unique' ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from esc_sql().
			ARRAY_A
		);

		if ( empty( $existing_new_indexes ) ) {
			$result = $wpdb->query( "ALTER TABLE `{$relationships_table}` ADD UNIQUE KEY parent_child_type_unique (parent_id, child_id, child_type)" ); // phpcs:ignore
			if ( false === $result ) {
				$success    = false;
				$messages[] = 'Failed to add parent_child_type_unique constraint';
			} else {
				$messages[] = 'Added parent_child_type_unique constraint';
			}
		}

		// Add additional indexes for performance.
		$indexes = array(
			$wpdb->prefix . 'splms_enrollments'     => array(
				'idx_enrollment_status' => 'ADD INDEX idx_enrollment_status (status)',
			),
			$wpdb->prefix . 'splms_lesson_progress' => array(
				'idx_completion' => 'ADD INDEX idx_completion (is_completed)',
			),
			$wpdb->prefix . 'splms_quiz_attempts'   => array(
				'idx_quiz_status' => 'ADD INDEX idx_quiz_status (status)',
			),
		);

		foreach ( $indexes as $table => $table_indexes ) {
			foreach ( $table_indexes as $index_name => $index_sql ) {
				// Check if index already exists.
				$existing_indexes = $wpdb->get_results(
					'SHOW INDEX FROM `' . esc_sql( $table ) . '`', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name from internal config via esc_sql().
					ARRAY_A
				);

				$index_exists = false;
				foreach ( $existing_indexes as $existing_index ) {
					if ( $existing_index['Key_name'] === $index_name ) {
						$index_exists = true;
						break;
					}
				}

				if ( ! $index_exists ) {
					$result = $wpdb->query( "ALTER TABLE `{$table}` {$index_sql}" ); // phpcs:ignore
					if ( false === $result ) {
						$success    = false;
						$messages[] = "Failed to add index {$index_name} to {$table}";
					} else {
						$messages[] = "Added index {$index_name} to {$table}";
					}
				} else {
					$messages[] = "Index {$index_name} already exists on {$table}";
				}
			}
		}

		return array(
			'success' => $success,
			'message' => implode( '; ', $messages ),
		);
	}

	/**
	 * Upgrade to version 3: Add generic 'data' column to lesson_progress table.
	 *
	 * @since 1.0.0
	 *
	 * @return array Migration result.
	 */
	protected function upgrade_to_version_3() {
		global $wpdb;

		$success  = true;
		$messages = array();

		// Add generic 'data' column to lesson_progress table for content-specific progress tracking.
		$lesson_progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );

		// Check if 'data' column already exists.
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
				 WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'data'",
				DB_NAME,
				$lesson_progress_table
			)
		);

		if ( empty( $column_exists ) ) {
			// Add the 'data' column after 'time_spent'.
			$result = $wpdb->query( // phpcs:ignore
				"ALTER TABLE `{$lesson_progress_table}` ADD COLUMN `data` longtext NULL AFTER `time_spent`" // phpcs:ignore
			);

			if ( false === $result ) {
				$success    = false;
				$messages[] = 'Failed to add data column to lesson_progress table';
			} else {
				$messages[] = 'Successfully added data column to lesson_progress table for content-specific progress tracking';
			}
		} else {
			$messages[] = 'Data column already exists in lesson_progress table';
		}

		return array(
			'success' => $success,
			'message' => implode( '; ', $messages ),
		);
	}
}
