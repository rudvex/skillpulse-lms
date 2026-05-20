<?php
/**
 * Database management class.
 *
 * Handles database table creation and management for the plugin.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database management class.
 *
 * @since 1.0.0
 */
class SPLMS_Database {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var SPLMS_Database|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Database The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup_actions();
			self::$instance->setup_filters();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function __construct() {
	}

	/**
	 * Action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_actions() {
		add_action( 'init', array( $this, 'create_tables' ) );
		add_action( 'wp_ajax_splms_create_page', array( $this, 'ajax_create_page' ) );
	}

	/**
	 * Define all filter hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_filters() {
		add_filter( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );
	}

	/**
	 * Create necessary database tables.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function create_tables() {
		global $wpdb;

		// Check if we need to run database operations.
		$current_db_version = get_option( 'splms_db_version', 0 );

		// Only run if database needs to be created or upgraded.
		if ( $current_db_version >= SPLMS_DB_VERSION ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// Course enrollments table.
		$table_name = esc_sql( $wpdb->prefix . 'splms_enrollments' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			course_id bigint(20) NOT NULL,
			enrolled_at datetime DEFAULT CURRENT_TIMESTAMP,
			completed_at datetime NULL,
			last_accessed_at datetime NULL,
			status varchar(20) DEFAULT 'active',
			progress decimal(5,2) DEFAULT 0.00,
			enrollment_method varchar(20) DEFAULT 'manual',
			notes text NULL,
			access_expires datetime NULL,
			PRIMARY KEY (id),
			UNIQUE KEY user_course (user_id, course_id),
			KEY user_id (user_id),
			KEY course_id (course_id),
			KEY enrollment_method (enrollment_method),
			KEY idx_status_enrolled_at (status, enrolled_at)
		) $charset_collate;";
		dbDelta( $sql );

		// Lesson progress table.
		$table_name = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			lesson_id bigint(20) NOT NULL,
			course_id bigint(20) NOT NULL,
			is_completed tinyint(1) DEFAULT 0,
			completed_at datetime NULL,
			time_spent int(11) DEFAULT 0,
			data longtext NULL,
			PRIMARY KEY (id),
			UNIQUE KEY user_lesson (user_id, lesson_id),
			KEY user_id (user_id),
			KEY lesson_id (lesson_id),
			KEY course_id (course_id),
			KEY idx_completed (is_completed, completed_at)
		) $charset_collate;";
		dbDelta( $sql );

		// Quiz attempts table.
		$table_name = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			quiz_id bigint(20) NOT NULL,
			course_id bigint(20) NOT NULL,
			answers longtext,
			score decimal(5,2) DEFAULT 0.00,
			max_score decimal(5,2) DEFAULT 100.00,
			status varchar(50) NOT NULL DEFAULT 'draft',
			passed tinyint(1) DEFAULT 0,
			attempt_time datetime DEFAULT CURRENT_TIMESTAMP,
			submitted_time datetime NULL,
			graded_time datetime NULL,
			graded_by bigint(20) NULL,
			time_taken int(11) DEFAULT 0,
			feedback text,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY quiz_id (quiz_id),
			KEY course_id (course_id),
			KEY idx_user_quiz (user_id, quiz_id),
			KEY idx_attempt_status (score, passed, time_taken),
			KEY idx_attempt_time (attempt_time),
			KEY idx_status_quiz (status, quiz_id),
			KEY idx_status_lookup (status)
		) $charset_collate;";
		dbDelta( $sql );

		// User activity log table.
		$table_name = esc_sql( $wpdb->prefix . 'splms_user_activity' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			course_id bigint(20) NULL,
			item_id bigint(20) NULL,
			item_type varchar(20) NULL,
			activity_type varchar(50) NOT NULL,
			activity_data text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY course_id (course_id),
			KEY activity_type (activity_type),
			KEY created_at (created_at)
		) $charset_collate;";
		dbDelta( $sql );

		// Course items table (for course-to-item relationships).
		$table_name = esc_sql( $wpdb->prefix . 'splms_course_items' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			course_id bigint(20) NOT NULL,
			item_id bigint(20) NOT NULL,
			item_type varchar(50) NOT NULL,
			order_index int(11) DEFAULT 0,
			PRIMARY KEY (id),
			UNIQUE KEY course_item_unique (course_id, item_id),
			KEY course_id (course_id),
			KEY item_id (item_id),
			KEY item_type (item_type),
			KEY order_index (order_index)
		) $charset_collate;";
		dbDelta( $sql );

		// Relationships table (for hierarchical parent-child relationships).
		$table_name = esc_sql( $wpdb->prefix . 'splms_relationships' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			parent_id bigint(20) NOT NULL,
			child_id bigint(20) NOT NULL,
			child_type varchar(50) NOT NULL,
			order_index int(11) DEFAULT 0,
			PRIMARY KEY (id),
			UNIQUE KEY parent_child_type_unique (parent_id, child_id, child_type),
			KEY parent_id (parent_id),
			KEY child_id (child_id),
			KEY child_type (child_type),
			KEY order_index (order_index)
		) $charset_collate;";
		dbDelta( $sql );

		// Quiz questions table.
		$table_name = esc_sql( $wpdb->prefix . 'splms_quiz_questions' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			quiz_id bigint(20) NOT NULL,
			question_type varchar(50) NOT NULL,
			question_text text NOT NULL,
			question_description text,
			explanation text,
			points decimal(5,2) DEFAULT 1.00,
			order_index int(11) DEFAULT 0,
			is_required tinyint(1) DEFAULT 1,
			settings text,
			options_json longtext NULL,
			correct_answer_json longtext NULL,
			media_type varchar(20) DEFAULT 'none',
			media_url varchar(500),
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY quiz_id (quiz_id),
			KEY question_type (question_type),
			KEY order_index (order_index),
			KEY idx_quiz_order (quiz_id, order_index)
		) $charset_collate;";
		dbDelta( $sql );

		// Signups table for pending user signups.
		$table_name = esc_sql( $wpdb->prefix . 'splms_signups' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_login varchar(60) NOT NULL,
			user_email varchar(100) NOT NULL,
			user_name varchar(100) NOT NULL,
			first_name varchar(50) NOT NULL,
			last_name varchar(50) NOT NULL,
			user_type varchar(20) NOT NULL DEFAULT 'student',
			meta longtext,
			registered datetime NOT NULL,
			activation_key varchar(255) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			PRIMARY KEY (id),
			KEY user_login (user_login),
			KEY user_email (user_email),
			KEY activation_key (activation_key),
			KEY status (status),
			KEY user_type (user_type)
		) $charset_collate;";
		dbDelta( $sql );

		// Orders table - Only essential fields.
		$table_name = esc_sql( $wpdb->prefix . 'splms_orders' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			course_id bigint(20) NOT NULL,
			amount decimal(10,2) NOT NULL,
			currency varchar(3) NOT NULL DEFAULT 'USD',
			status varchar(20) NOT NULL DEFAULT 'pending',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			completed_at datetime NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY course_id (course_id),
			KEY status (status),
			KEY created_at (created_at)
		) $charset_collate;";
		dbDelta( $sql );

		// Order meta table.
		$table_name = esc_sql( $wpdb->prefix . 'splms_order_meta' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			order_id bigint(20) NOT NULL,
			meta_key varchar(255) NOT NULL,
			meta_value longtext,
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY meta_key (meta_key),
			UNIQUE KEY order_meta_key (order_id, meta_key)
		) $charset_collate;";
		dbDelta( $sql );

		// Order items table.
		$table_name = esc_sql( $wpdb->prefix . 'splms_order_items' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			order_id bigint(20) NOT NULL,
			item_type varchar(20) NOT NULL,
			item_id bigint(20) NOT NULL,
			item_name varchar(255) NOT NULL,
			unit_price decimal(10,2) NOT NULL,
			total_price decimal(10,2) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY item_type (item_type),
			KEY item_id (item_id)
		) $charset_collate;";
		dbDelta( $sql );

		// Section access table.
		$table_name = esc_sql( $wpdb->prefix . 'splms_section_access' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			section_id bigint(20) NOT NULL,
			course_id bigint(20) NOT NULL,
			access_type varchar(20) NOT NULL,
			granted_at datetime DEFAULT CURRENT_TIMESTAMP,
			expires_at datetime NULL,
			order_id bigint(20) NULL,
			notes text NULL,
			PRIMARY KEY (id),
			UNIQUE KEY user_section (user_id, section_id),
			KEY user_id (user_id),
			KEY section_id (section_id),
			KEY course_id (course_id)
		) $charset_collate;";
		dbDelta( $sql );

		// Notifications table.
		$table_name = esc_sql( $wpdb->prefix . 'splms_notifications' );
		$sql        = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			event_key varchar(100) NOT NULL,
			title varchar(255) NOT NULL,
			message text NOT NULL,
			type varchar(20) NOT NULL DEFAULT 'info',
			is_read tinyint(1) NOT NULL DEFAULT 0,
			read_at datetime NULL,
			course_id bigint(20) NULL,
			related_id bigint(20) NULL,
			related_type varchar(50) NULL,
			meta longtext NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY event_key (event_key),
			KEY is_read (is_read),
			KEY created_at (created_at),
			KEY user_read (user_id, is_read),
			KEY course_id (course_id)
		) $charset_collate;";
		dbDelta( $sql );

		// Update database version.
		update_option( 'splms_db_version', SPLMS_DB_VERSION );

		// Run database migrations if needed.
		$this->run_database_migrations();

		// Create default pages only during activation.
		if ( 'activated' === get_option( 'splms_activation_hook' ) ) {
			$this->create_default_pages();
		}
	}

	/**
	 * Run database migrations using the migration class.
	 *
	 * @since 1.0.0
	 */
	private function run_database_migrations() {
		// Only run migrations if this is an existing installation.
		$existing_db_version = get_option( 'splms_db_version', 0 );
		if ( 0 === $existing_db_version ) {
			// Fresh install - all tables created with latest schema.
			return;
		}

		// Load and run migrations.
		require_once SPLMS_DIR_PATH . 'includes/modules/core/class-migration.php';
		$migration = SPLMS_Migration::get_instance();

		if ( $migration->is_migration_needed() ) {
			$results = $migration->run_migrations();

			// Log migration results.
			foreach ( $results as $version => $result ) {
				if ( ! $result['success'] ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Critical migration failure logging.
					error_log( "SPLMS Migration Error: Version {$version} failed - {$result['error']}" );
				}
			}
		}
	}


	/**
	 * Create or get existing page by slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $page_slug  Page slug.
	 * @param string $page_title Page title.
	 * @param string $page_content Page content.
	 * @param string $page_template Page template (optional).
	 * @return int|false Page ID on success, false on failure.
	 */
	private function get_or_create_page( $page_slug, $page_title, $page_content = '', $page_template = '' ) {
		// Check if page already exists by slug.
		$existing_page = get_page_by_path( $page_slug );

		if ( $existing_page ) {
			return $existing_page->ID;
		}

		// Check if page exists by title using WP_Query (get_page_by_title is deprecated).
		$query = new WP_Query(
			array(
				'post_type'              => 'page',
				'title'                  => $page_title,
				'post_status'            => array( 'publish', 'private', 'draft' ),
				'numberposts'            => 1,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => 'post_date ID',
				'order'                  => 'ASC',
			)
		);

		if ( ! empty( $query->posts ) ) {
			return $query->posts[0]->ID;
		}

		// Create new page.
		$page_data = array(
			'post_title'   => $page_title,
			'post_name'    => $page_slug,
			'post_content' => $page_content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id(),
		);

		$page_id = wp_insert_post( $page_data, true );

		if ( is_wp_error( $page_id ) ) {
			return false;
		}

		// Set custom template if provided.
		if ( ! empty( $page_template ) ) {
			update_post_meta( $page_id, '_wp_page_template', $page_template );
		}

		return $page_id;
	}

	/**
	 * Create default pages for SkillPulse LMS.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function create_default_pages() {
		$page_configs = $this->get_page_configurations();
		$this->create_pages_from_configs( $page_configs );
	}

	/**
	 * Public method to create all missing pages.
	 *
	 * Can be used by admin utilities or setup wizards.
	 *
	 * @since 1.0.0
	 *
	 * @return array Results with created pages information.
	 */
	public function create_all_missing_pages() {
		$page_configs      = $this->get_page_configurations();
		$settings_instance = SPLMS_Settings::get_instance();
		$general_settings  = $this->ensure_pages_settings_exist( $settings_instance );
		$results           = array();
		$updated_settings  = false;

		foreach ( $page_configs as $page_type => $config ) {
			if ( $this->is_page_already_configured( $general_settings, $page_type ) ) {
				$page_id               = $general_settings['pages_settings'][ $page_type ];
				$results[ $page_type ] = array(
					'status'  => 'exists',
					// translators: %s: Title.
					'message' => sprintf( __( '%s page already exists.', 'skillpulse-lms' ), $config['title'] ),
					'page_id' => $page_id,
				);
				continue;
			}

			$page_id = $this->get_or_create_page( $config['slug'], $config['title'], $config['content'] );

			if ( $page_id ) {
				$general_settings['pages_settings'][ $page_type ] = $page_id;
				$updated_settings                                 = true;
				$results[ $page_type ]                            = array(
					'status'  => 'created',
					// translators: %s: Title.
					'message' => sprintf( __( '%s page created successfully.', 'skillpulse-lms' ), $config['title'] ),
					'page_id' => $page_id,
				);
			} else {
				$results[ $page_type ] = array(
					'status'  => 'error',
					// translators: %s: Title.
					'message' => sprintf( __( 'Failed to create %s page.', 'skillpulse-lms' ), $config['title'] ),
					'page_id' => 0,
				);
			}
		}

		if ( $updated_settings ) {
			$settings_instance->update_tab_settings( 'general', $general_settings );
		}

		return $results;
	}


	/**
	 * Drop all tables and clean up all plugin data (for uninstall).
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function drop_tables() {
		global $wpdb;

		// 1. Drop database tables.
		$tables = array(
			$wpdb->prefix . 'splms_enrollments',
			$wpdb->prefix . 'splms_lesson_progress',
			$wpdb->prefix . 'splms_quiz_attempts',
			$wpdb->prefix . 'splms_user_activity',
			$wpdb->prefix . 'splms_course_items',
			$wpdb->prefix . 'splms_relationships',
			$wpdb->prefix . 'splms_quiz_questions',
			$wpdb->prefix . 'splms_signups',
			$wpdb->prefix . 'splms_orders',
			$wpdb->prefix . 'splms_order_meta',
			$wpdb->prefix . 'splms_order_items',
			$wpdb->prefix . 'splms_section_access',
			$wpdb->prefix . 'splms_notifications',
		);

		foreach ( $tables as $table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe, schema change is intentional.
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		}

		// 2. Delete fixed options.
		$fixed_options = array(
			'splms_db_version',
			'splms_settings',
			'splms_activation_hook',
			'splms_license',
			'splms_jwt_blacklist',
			'splms_license_key',
			'splms_license_status',
			'splms_license_expires',
			'splms_license_type',
			'splms_license_site_count',
			'splms_license_last_check',
		);

		foreach ( $fixed_options as $option ) {
			delete_option( $option );
		}

		// 3. Delete dynamic options (email & in-app templates).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Uninstall operation, no caching needed.
		$wpdb->query(
			"DELETE FROM {$wpdb->options}
			 WHERE option_name LIKE 'splms_email_template_%'
				OR option_name LIKE 'splms_in_app_template_%'"
		);

		// 4. Delete transients.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Uninstall operation, no caching needed.
		$wpdb->query(
			"DELETE FROM {$wpdb->options}
			 WHERE option_name LIKE '_transient_splms_%'
				OR option_name LIKE '_transient_timeout_splms_%'
				OR option_name LIKE '_transient_course_settings_%'
				OR option_name LIKE '_transient_timeout_course_settings_%'"
		);

		// 5. Delete post meta.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Uninstall operation, no caching needed.
		$wpdb->query(
			"DELETE FROM {$wpdb->postmeta}
			 WHERE meta_key LIKE '_splms_%'
				OR meta_key LIKE 'splms_%'"
		);

		// 6. Delete user meta.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Uninstall operation, no caching needed.
		$wpdb->query(
			"DELETE FROM {$wpdb->usermeta}
			 WHERE meta_key LIKE 'splms_%'"
		);

		// 7. Clear any remaining object caches.
		wp_cache_flush();
	}

	/**
	 * Display custom post states for specific pages.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $post_states Current post states.
	 * @param WP_Post $post        The current post object.
	 * @return array Modified post states.
	 */
	public function display_post_states( $post_states, $post ) {
		$general_tab = SPLMS_Settings::get_instance()->get_tab_setting( 'general', '' );

		// Define page states configuration.
		$page_states = array(
			'signup_page_id'           => __( 'Signup Page', 'skillpulse-lms' ),
			'courses_page_id'          => __( 'Courses Page', 'skillpulse-lms' ),
			'dashboard_page_id'        => __( 'Dashboard Page', 'skillpulse-lms' ),
			'logout_redirect_page_id'  => __( 'Logout Redirect Page', 'skillpulse-lms' ),
			'terms_conditions_page_id' => __( 'Terms & Conditions Page', 'skillpulse-lms' ),
			'privacy_policy_page_id'   => __( 'Privacy Policy Page', 'skillpulse-lms' ),
		);

		// Check each page setting.
		foreach ( $page_states as $setting_key => $state_label ) {
			// Check in the proper nested structure: general.pages_settings.{field_id}.
			$page_id = isset( $general_tab['pages_settings'][ $setting_key ] ) ? $general_tab['pages_settings'][ $setting_key ] : 0;

			// Fallback to check flat structure for backward compatibility.
			if ( ! $page_id ) {
				$page_id = isset( $general_tab[ $setting_key ] ) ? $general_tab[ $setting_key ] : 0;
			}

			if ( $page_id && intval( $post->ID ) === intval( $page_id ) ) {
				$post_states[ $setting_key ] = $state_label;
				break; // Only one state per page.
			}
		}

		return $post_states;
	}

	/**
	 * AJAX handler for creating pages from admin settings.
	 *
	 * @since 1.0.0
	 */
	public function ajax_create_page() {
		// Security and permission checks.
		if ( ! $this->validate_ajax_request() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		if ( empty( $_POST['page_type'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$page_type = sanitize_text_field( wp_unslash( $_POST['page_type'] ) );

		// Create single page and update settings.
		$result = $this->create_single_page( $page_type );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Get page configurations for different page types.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_page_configurations() {
		return array(
			'signup_page_id'           => array(
				'slug'        => 'signup',
				'title'       => __( 'Sign Up', 'skillpulse-lms' ),
				'content'     => '',
				'description' => __( 'User registration and signup page', 'skillpulse-lms' ),
			),
			'courses_page_id'          => array(
				'slug'        => 'courses',
				'title'       => __( 'Courses', 'skillpulse-lms' ),
				'content'     => __( 'Browse our available courses and start your learning journey.', 'skillpulse-lms' ),
				'description' => __( 'Main courses listing page', 'skillpulse-lms' ),
			),
			'dashboard_page_id'        => array(
				'slug'        => 'dashboard',
				'title'       => __( 'Dashboard', 'skillpulse-lms' ),
				'content'     => '<!-- Dashboard content handled by SkillPulse LMS template -->',
				'description' => __( 'User dashboard and learning center', 'skillpulse-lms' ),
			),
			'logout_redirect_page_id'  => array(
				'slug'        => 'logout-redirect',
				'title'       => __( 'Thank You', 'skillpulse-lms' ),
				'content'     => __( 'Thank you for using our learning platform. You have been logged out successfully.', 'skillpulse-lms' ),
				'description' => __( 'Page users see after logout', 'skillpulse-lms' ),
			),
			'terms_conditions_page_id' => array(
				'slug'        => 'terms-and-conditions',
				'title'       => __( 'Terms and Conditions', 'skillpulse-lms' ),
				'content'     => __( 'Please read these terms and conditions carefully before using our service.', 'skillpulse-lms' ),
				'description' => __( 'Terms and conditions for users', 'skillpulse-lms' ),
			),
			'privacy_policy_page_id'   => array(
				'slug'        => 'privacy-policy',
				'title'       => __( 'Privacy Policy', 'skillpulse-lms' ),
				'content'     => __( 'This privacy policy describes how we collect, use, and protect your personal information.', 'skillpulse-lms' ),
				'description' => __( 'Privacy policy for user data protection', 'skillpulse-lms' ),
			),
		);
	}

	/**
	 * Create pages from configuration array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $page_configs Page configurations array.
	 * @return bool True if any pages were created.
	 */
	private function create_pages_from_configs( $page_configs ) {
		$settings_instance = SPLMS_Settings::get_instance();
		$general_settings  = $this->ensure_pages_settings_exist( $settings_instance );
		$updated_settings  = false;

		foreach ( $page_configs as $setting_key => $page_config ) {
			// Skip if page ID is already set in settings and page still exists.
			if ( $this->is_page_already_configured( $general_settings, $setting_key ) ) {
				continue;
			}

			$page_id = $this->get_or_create_page( $page_config['slug'], $page_config['title'], $page_config['content'] );

			if ( $page_id ) {
				$general_settings['pages_settings'][ $setting_key ] = $page_id;
				$updated_settings                                   = true;
			}
		}

		if ( $updated_settings ) {
			$settings_instance->update_tab_settings( 'general', $general_settings );
		}

		return $updated_settings;
	}

	/**
	 * Create a single page and update settings.
	 *
	 * @since 1.0.0
	 *
	 * @param string $page_type Page type key.
	 * @return array|WP_Error Success data or error.
	 */
	private function create_single_page( $page_type ) {
		$page_configs = $this->get_page_configurations();

		if ( ! isset( $page_configs[ $page_type ] ) ) {
			return new WP_Error( 'invalid_page_type', __( 'Invalid page type.', 'skillpulse-lms' ) );
		}

		$config = $page_configs[ $page_type ];

		// Create the page.
		$page_id = $this->get_or_create_page( $config['slug'], $config['title'], $config['content'] );

		if ( ! $page_id ) {
			return new WP_Error( 'page_creation_failed', __( 'Failed to create page.', 'skillpulse-lms' ) );
		}

		// Update settings.
		$this->update_page_setting( $page_type, $page_id );

		// Return success data.
		$page = get_post( $page_id );

		return array(
			// translators: %s: Title.
			'message'    => sprintf( __( '%s page created successfully!', 'skillpulse-lms' ), $config['title'] ),
			'page_id'    => $page_id,
			'page_title' => $page->post_title,
			'edit_link'  => admin_url( 'post.php?post=' . $page_id . '&action=edit' ),
			'view_link'  => get_permalink( $page_id ),
		);
	}

	/**
	 * Validate AJAX request with security checks.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if valid, false otherwise.
	 */
	private function validate_ajax_request() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skillpulse-lms' ) ) );
			return false;
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'skillpulse-lms' ) ) );
			return false;
		}

		return true;
	}

	/**
	 * Ensure pages_settings section exists in settings.
	 *
	 * @since 1.0.0
	 *
	 * @param SPLMS_Settings $settings_instance Settings instance.
	 * @return array General settings array.
	 */
	private function ensure_pages_settings_exist( $settings_instance ) {
		$general_settings = $settings_instance->get_tab_setting( 'general', '', array() );

		if ( ! isset( $general_settings['pages_settings'] ) ) {
			$general_settings['pages_settings'] = array();
		}

		return $general_settings;
	}

	/**
	 * Check if page is already configured and still exists.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $general_settings General settings array.
	 * @param string $setting_key      Setting key to check.
	 * @return bool True if page is properly configured.
	 */
	private function is_page_already_configured( $general_settings, $setting_key ) {
		if ( empty( $general_settings['pages_settings'][ $setting_key ] ) ) {
			return false;
		}

		$page_id = $general_settings['pages_settings'][ $setting_key ];
		$page    = get_post( $page_id );

		return $page && 'page' === $page->post_type && 'publish' === $page->post_status;
	}

	/**
	 * Update page setting in database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $page_type Page type key.
	 * @param int    $page_id   Page ID.
	 */
	private function update_page_setting( $page_type, $page_id ) {
		$settings_instance = SPLMS_Settings::get_instance();
		$general_settings  = $this->ensure_pages_settings_exist( $settings_instance );

		$general_settings['pages_settings'][ $page_type ] = $page_id;
		$settings_instance->update_tab_settings( 'general', $general_settings );
	}
}
