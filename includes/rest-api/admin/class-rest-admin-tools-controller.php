<?php
/**
 * Admin Tools REST API Controller
 *
 * Handles REST API endpoints for admin tools and maintenance operations.
 * Provides endpoints for data import/export, database rebuilding, and various utility functions.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * POST   /splms/v1/admin/import-sample-data - Import sample data
 * POST   /splms/v1/admin/export-data - Export data
 * POST   /splms/v1/admin/rebuild-database - Rebuild database
 * POST   /splms/v1/admin/export-courses - Export courses
 * POST   /splms/v1/admin/export-user-progress - Export user progress
 * POST   /splms/v1/admin/import-courses - Import courses
 * POST   /splms/v1/admin/reset-data - Reset LMS data
 * GET    /splms/v1/admin/system-info - Get system information
 * GET    /splms/v1/admin/system-report - Download system report
 * GET    /splms/v1/admin/migration-status - Get migration status
 * POST   /splms/v1/admin/run-migrations - Run pending migrations
 * GET    /splms/v1/admin/migration-logs - Get migration logs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Tools REST API Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Rest_Admin_Tools_Controller extends WP_REST_Controller {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'admin';
	}

	/**
	 * Register the tools routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Data Management endpoints.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/import-sample-data',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'import_sample_data' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/export-data',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'export_data' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/rebuild-database',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'rebuild_database' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		// Enhanced Data Management endpoints.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/export-courses',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'export_courses' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/export-user-progress',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'export_user_progress' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/import-courses',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'import_courses' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		// Reset Tools endpoints.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/reset-data',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'reset_lms_data' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		// System Info endpoints.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/system-info',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_system_info' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => array(
					'force_refresh' => array(
						'description' => __( 'Force refresh cached data.', 'skillpulse-lms' ),
						'type'        => 'boolean',
						'default'     => false,
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/system-report',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'download_system_report' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => array(
					'force_refresh' => array(
						'description' => __( 'Force refresh cached data for report.', 'skillpulse-lms' ),
						'type'        => 'boolean',
						'default'     => false,
					),
				),
			)
		);

		// Migration Management endpoints.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/migration-status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_migration_status' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/run-migrations',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'run_migrations' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/migration-logs',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_migration_logs' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => array(
					'limit'   => array(
						'description' => __( 'Number of log entries to retrieve.', 'skillpulse-lms' ),
						'type'        => 'integer',
						'default'     => 100,
					),
					'version' => array(
						'description' => __( 'Filter logs by migration version.', 'skillpulse-lms' ),
						'type'        => 'integer',
						'default'     => 0,
					),
				),
			)
		);
	}

	/**
	 * Check if a given request has access to admin dashboard data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $_request Full data about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function get_permissions_check( $_request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by REST API callback signature.
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'splms_rest_forbidden',
				__( 'Sorry, you are not allowed to access admin dashboard.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Import sample data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function import_sample_data( $request ) {
		$imported = 0;

		// Create sample courses.
		$sample_courses = array(
			array(
				'title'   => 'Introduction to Web Development',
				'content' => 'Learn the basics of web development including HTML, CSS, and JavaScript.',
			),
			array(
				'title'   => 'Advanced WordPress Development',
				'content' => 'Master advanced WordPress development techniques and best practices.',
			),
			array(
				'title'   => 'Digital Marketing Fundamentals',
				'content' => 'Understand the core concepts of digital marketing and online promotion.',
			),
		);

		foreach ( $sample_courses as $course_data ) {
			$course_id = wp_insert_post(
				array(
					'post_title'   => $course_data['title'],
					'post_content' => $course_data['content'],
					'post_type'    => SPLMS_POST_TYPES['course'],
					'post_status'  => 'publish',
				)
			);

			if ( $course_id ) {
				++$imported;

				// Add sample lessons.
				$sample_lessons = array(
					'Lesson 1: Getting Started',
					'Lesson 2: Basic Concepts',
					'Lesson 3: Advanced Techniques',
				);

				foreach ( $sample_lessons as $lesson_title ) {
					wp_insert_post(
						array(
							'post_title'   => $lesson_title,
							'post_content' => 'This is a sample lesson content.',
							'post_type'    => SPLMS_POST_TYPES['lesson'],
							'post_status'  => 'publish',
							'post_parent'  => $course_id,
						)
					);
				}
			}
		}

		return rest_ensure_response(
			array(
				'success'  => true,
				'imported' => $imported,
				// translators: %d: The number of courses imported.
				'message'  => sprintf( __( 'Successfully imported %d sample courses with lessons.', 'skillpulse-lms' ), $imported ),
			)
		);
	}

	/**
	 * Export all data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function export_data( $request ) {
		global $wpdb;

		// Get all data.
		$data = array(
			'courses'     => array(),
			'lessons'     => array(),
			'quizzes'     => array(),
			'enrollments' => array(),
		);

		// Export courses.
		$courses = get_posts(
			array(
				'post_type'      => SPLMS_POST_TYPES['course'],
				'posts_per_page' => - 1,
				'post_status'    => 'any',
			)
		);

		foreach ( $courses as $course ) {
			$data['courses'][] = array(
				'id'      => $course->ID,
				'title'   => $course->post_title,
				'content' => $course->post_content,
				'status'  => $course->post_status,
				'date'    => $course->post_date,
			);
		}

		// Export lessons.
		$lessons = get_posts(
			array(
				'post_type'      => SPLMS_POST_TYPES['lesson'],
				'posts_per_page' => - 1,
				'post_status'    => 'any',
			)
		);

		foreach ( $lessons as $lesson ) {
			$data['lessons'][] = array(
				'id'        => $lesson->ID,
				'title'     => $lesson->post_title,
				'content'   => $lesson->post_content,
				'course_id' => $lesson->post_parent,
				'status'    => $lesson->post_status,
				'date'      => $lesson->post_date,
			);
		}

		// Export enrollments.
		$enrollments = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}splms_enrollments ORDER BY enrolled_at DESC"
		);

		foreach ( $enrollments as $enrollment ) {
			$data['enrollments'][] = array(
				'id'          => $enrollment->id,
				'user_id'     => $enrollment->user_id,
				'course_id'   => $enrollment->course_id,
				'enrolled_at' => $enrollment->enrolled_at,
				'progress'    => $enrollment->progress,
				'status'      => $enrollment->status,
			);
		}

		// Create JSON file using FileManager.
		$filename = 'splms-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.json';
		$content  = wp_json_encode( $data, JSON_PRETTY_PRINT );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_write -- Using custom FileManager class for file operations.
		$file_result = SkillPulse_LMS_File_Manager::write_file( 'exports', $filename, $content );

		if ( is_wp_error( $file_result ) ) {
			return new WP_Error(
				'file_creation_failed',
				__( 'Failed to create export file: ', 'skillpulse-lms' ) . $file_result->get_error_message(),
				array( 'status' => 500 )
			);
		}

		$download_url = $file_result['fileurl'];

		return rest_ensure_response(
			array(
				'success'      => true,
				'download_url' => $download_url,
				'filename'     => $filename,
			)
		);
	}

	/**
	 * Rebuild database tables.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function rebuild_database( $request ) {
		global $wpdb;

		$tables_created = 0;

		// Define table schemas.
		return rest_ensure_response(
			array(
				'success' => true,
				'tables'  => $tables_created,
				// translators: %d: The number of database tables rebuilt.
				'message' => sprintf( __( 'Successfully rebuilt %d database tables.', 'skillpulse-lms' ), $tables_created ),
			)
		);
	}

	/**
	 * Export courses data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function export_courses( $request ) {
		global $wpdb;

		// Get all courses with metadata.
		$courses = get_posts(
			array(
				'post_type'      => SPLMS_POST_TYPES['course'],
				'posts_per_page' => - 1,
				'post_status'    => 'any',
			)
		);

		$course_data = array();
		foreach ( $courses as $course ) {
			$course_data[] = array(
				'id'      => $course->ID,
				'title'   => $course->post_title,
				'content' => $course->post_content,
				'status'  => $course->post_status,
				'date'    => $course->post_date,
				'meta'    => get_post_meta( $course->ID ),
				'lessons' => get_posts(
					array(
						'post_type'   => SPLMS_POST_TYPES['lesson'],
						'post_parent' => $course->ID,
						'numberposts' => - 1,
					)
				),
			);
		}

		// Create JSON file using FileManager.
		$filename = 'splms-courses-' . gmdate( 'Y-m-d-H-i-s' ) . '.json';
		$content  = wp_json_encode( $course_data, JSON_PRETTY_PRINT );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_write -- Using custom FileManager class for file operations.
		$file_result = SkillPulse_LMS_File_Manager::write_file( 'exports', $filename, $content );

		if ( is_wp_error( $file_result ) ) {
			return new WP_Error(
				'file_creation_failed',
				__( 'Failed to create courses export file: ', 'skillpulse-lms' ) . $file_result->get_error_message(),
				array( 'status' => 500 )
			);
		}

		$download_url = $file_result['fileurl'];

		return rest_ensure_response(
			array(
				'success'      => true,
				'download_url' => $download_url,
				'filename'     => $filename,
				'count'        => count( $course_data ),
			)
		);
	}

	/**
	 * Export user progress data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function export_user_progress( $request ) {
		global $wpdb;

		// Get all enrollment data.
		$enrollments = $wpdb->get_results(
			"SELECT e.*, p.post_title, u.display_name, u.user_email 
			FROM {$wpdb->prefix}splms_enrollments e 
			LEFT JOIN {$wpdb->posts} p ON e.course_id = p.ID 
			LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID 
			ORDER BY e.enrolled_at DESC"
		);

		// Create CSV file using FileManager.
		$filename = 'splms-user-progress-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		// Build CSV content.
		$csv_content = '';

		// Add CSV header.
		$header       = array( 'User ID', 'Email', 'Name', 'Course ID', 'Course Title', 'Enrolled Date', 'Progress', 'Status' );
		$csv_content .= implode(
			',',
			array_map(
				function ( $field ) {
					return '"' . str_replace( '"', '""', $field ) . '"';
				},
				$header
			)
		) . "\n";

		// Add CSV data rows.
		foreach ( $enrollments as $enrollment ) {
			$row          = array(
				$enrollment->user_id,
				$enrollment->user_email,
				$enrollment->display_name,
				$enrollment->course_id,
				$enrollment->post_title,
				$enrollment->enrolled_at,
				$enrollment->progress . '%',
				$enrollment->status,
			);
			$csv_content .= implode(
				',',
				array_map(
					function ( $field ) {
						return '"' . str_replace( '"', '""', $field ) . '"';
					},
					$row
				)
			) . "\n";
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_write -- Using custom FileManager class for file operations.
		$file_result = SkillPulse_LMS_File_Manager::write_file( 'exports', $filename, $csv_content );

		if ( is_wp_error( $file_result ) ) {
			return new WP_Error(
				'file_creation_failed',
				__( 'Failed to create user progress export file: ', 'skillpulse-lms' ) . $file_result->get_error_message(),
				array( 'status' => 500 )
			);
		}

		$download_url = $file_result['fileurl'];

		return rest_ensure_response(
			array(
				'success'      => true,
				'download_url' => $download_url,
				'filename'     => $filename,
				'count'        => count( $enrollments ),
			)
		);
	}

	/**
	 * Import courses from file.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function import_courses( $request ) {
		$files = $request->get_file_params();

		if ( empty( $files['file'] ) ) {
			return new WP_Error(
				'no_file',
				__( 'No file provided for import.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		$file = $files['file'];

		// Validate file upload.
		if ( ! isset( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error(
				'invalid_file',
				__( 'Invalid file upload.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading uploaded file from tmp_name is safe.
		$file_content = file_get_contents( $file['tmp_name'] );
		$imported     = 0;

		if ( pathinfo( $file['name'], PATHINFO_EXTENSION ) === 'json' ) {
			$course_data = json_decode( $file_content, true );

			if ( is_array( $course_data ) ) {
				foreach ( $course_data as $course ) {
					$course_id = wp_insert_post(
						array(
							'post_title'   => isset( $course['title'] ) ? sanitize_text_field( $course['title'] ) : '',
							'post_content' => isset( $course['content'] ) ? wp_kses_post( $course['content'] ) : '',
							'post_type'    => SPLMS_POST_TYPES['course'],
							'post_status'  => 'publish',
						)
					);

					if ( $course_id ) {
						++$imported;

						// Import metadata.
						if ( isset( $course['meta'] ) && is_array( $course['meta'] ) ) {
							foreach ( $course['meta'] as $key => $values ) {
								$sanitized_key = sanitize_key( $key );
								if ( is_array( $values ) ) {
									foreach ( $values as $value ) {
										// Sanitize meta value based on type.
										$sanitized_value = is_string( $value ) ? sanitize_text_field( $value ) : $value;
										add_post_meta( $course_id, $sanitized_key, $sanitized_value );
									}
								}
							}
						}
					}
				}
			}
		}

		return rest_ensure_response(
			array(
				'success'  => true,
				'imported' => $imported,
				// translators: %d: The number of courses imported.
				'message'  => sprintf( __( 'Successfully imported %d courses.', 'skillpulse-lms' ), $imported ),
			)
		);
	}

	/**
	 * Reset LMS data based on options.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function reset_lms_data( $request ) {
		global $wpdb;

		$body        = $request->get_json_params();
		$options     = isset( $body['options'] ) ? $body['options'] : array();
		$reset_count = 0;

		// Complete reset - remove all plugin data (like fresh install).
		if ( ! empty( $options['complete_reset'] ) ) {
			return $this->perform_complete_reset();
		}

		if ( ! empty( $options['enrollments'] ) ) {
			$deleted      = $wpdb->query( "DELETE FROM {$wpdb->prefix}splms_enrollments" );
			$reset_count += $deleted;
		}

		if ( ! empty( $options['progress'] ) ) {
			$deleted      = $wpdb->query( "UPDATE {$wpdb->prefix}splms_enrollments SET progress = 0" );
			$reset_count += $deleted;
		}

		if ( ! empty( $options['quizAttempts'] ) ) {
			$deleted      = $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '%splms_quiz_attempts%'" );
			$reset_count += $deleted;
		}

		if ( ! empty( $options['certificates'] ) ) {
			$deleted      = $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '%splms_certificates%'" );
			$reset_count += $deleted;
		}

		return rest_ensure_response(
			array(
				'success'     => true,
				'reset_count' => $reset_count,
				// translators: %d: The number of records reset.
				'message'     => sprintf( __( 'Successfully reset %d records.', 'skillpulse-lms' ), $reset_count ),
			)
		);
	}

	/**
	 * Perform complete reset - remove all SkillPulse LMS data.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	private function perform_complete_reset() {
		global $wpdb;

		// Additional safety check - only allow this for admin users.
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'insufficient_permissions',
				__( 'You do not have permission to perform a complete reset.', 'skillpulse-lms' )
			);
		}

		$reset_count = 0;
		$reset_items = array();

		try {
			// 1. Drop all custom database tables using the Database class method.
			SkillPulse_LMS_Database::drop_tables();
			$reset_items[] = 'Dropped all SkillPulse LMS database tables';
			$reset_count  += 11; // Number of tables dropped.

			// 2. Delete all SkillPulse LMS post types.
			$post_types = SPLMS_POST_TYPES;
			foreach ( $post_types as $post_type ) {
				$posts = get_posts(
					array(
						'post_type'   => $post_type,
						'post_status' => 'any',
						'numberposts' => -1,
						'fields'      => 'ids',
					)
				);

				foreach ( $posts as $post_id ) {
					wp_delete_post( $post_id, true ); // Force delete.
					++$reset_count;
				}

				if ( ! empty( $posts ) ) {
					$reset_items[] = sprintf( 'Deleted %d %s posts', count( $posts ), $post_type );
				}
			}

			// 3. Delete all SkillPulse LMS taxonomies and terms.
			$taxonomies = SPLMS_TAXONOMIES;
			foreach ( $taxonomies as $taxonomy ) {
				$terms = get_terms(
					array(
						'taxonomy'   => $taxonomy,
						'hide_empty' => false,
						'fields'     => 'ids',
					)
				);

				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
					foreach ( $terms as $term_id ) {
						wp_delete_term( $term_id, $taxonomy );
						++$reset_count;
					}
					$reset_items[] = sprintf( 'Deleted %d %s terms', count( $terms ), $taxonomy );
				}
			}

			// 4. Delete all WordPress options related to SkillPulse LMS.
			$options_to_delete = array(
				'splms_db_version',
				'splms_settings',
				'splms_activation_hook',
				'splms_license',  // Single consolidated license option (replaces splms_license_key, splms_license_status, splms_license_info, etc.).
			);

			foreach ( $options_to_delete as $option ) {
				if ( delete_option( $option ) ) {
					$reset_items[] = "Deleted option: $option";
					++$reset_count;
				}
			}

			// 5. Delete all user meta related to SkillPulse LMS.
			$user_meta_deleted = $wpdb->query(
				"DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '%splms_%' OR meta_key LIKE '%_splms_%'"
			);
			if ( $user_meta_deleted > 0 ) {
				$reset_items[] = "Deleted $user_meta_deleted user meta entries";
				$reset_count  += $user_meta_deleted;
			}

			// 6. Delete all post meta related to SkillPulse LMS.
			$post_meta_deleted = $wpdb->query(
				"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '%splms_%' OR meta_key LIKE '%_splms_%'"
			);
			if ( $post_meta_deleted > 0 ) {
				$reset_items[] = "Deleted $post_meta_deleted post meta entries";
				$reset_count  += $post_meta_deleted;
			}

			// 7. Delete all term meta related to SkillPulse LMS.
			$term_meta_deleted = $wpdb->query(
				"DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE '%splms_%' OR meta_key LIKE '%_splms_%'"
			);
			if ( $term_meta_deleted > 0 ) {
				$reset_items[] = "Deleted $term_meta_deleted term meta entries";
				$reset_count  += $term_meta_deleted;
			}

			// 8. Clear any transients related to SkillPulse LMS.
			$transients_deleted = $wpdb->query(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_splms_%' OR option_name LIKE '%_transient_timeout_splms_%'"
			);
			if ( $transients_deleted > 0 ) {
				$reset_items[] = "Deleted $transients_deleted transient entries";
				$reset_count  += $transients_deleted;
			}

			// 9. Remove user roles created by SkillPulse LMS.
			$roles_to_remove = array( 'sp_student' );
			foreach ( $roles_to_remove as $role ) {
				if ( get_role( $role ) ) {
					remove_role( $role );
					$reset_items[] = "Removed user role: $role";
					++$reset_count;
				}
			}

			// 10. Remove capabilities from existing roles.
			$wp_roles               = wp_roles();
			$capabilities_to_remove = array(
				'manage_courses',
				'edit_courses',
				'edit_others_courses',
				'publish_courses',
				'read_private_courses',
				'delete_courses',
				'delete_private_courses',
				'delete_published_courses',
				'delete_others_courses',
				'edit_private_courses',
				'edit_published_courses',
				'manage_course_terms',
				'edit_course_terms',
				'delete_course_terms',
				'assign_course_terms',
			);

			foreach ( $wp_roles->roles as $role_name => $role_info ) {
				$role = get_role( $role_name );
				if ( $role ) {
					foreach ( $capabilities_to_remove as $cap ) {
						if ( $role->has_cap( $cap ) ) {
							$role->remove_cap( $cap );
						}
					}
				}
			}
			$reset_items[] = 'Removed SkillPulse LMS capabilities from all roles';
			++$reset_count;

			// 11. Clear rewrite rules cache.
			flush_rewrite_rules();
			$reset_items[] = 'Flushed rewrite rules';
			++$reset_count;

			// 12. Clear any object cache.
			if ( function_exists( 'wp_cache_flush' ) ) {
				wp_cache_flush();
				$reset_items[] = 'Cleared object cache';
				++$reset_count;
			}

			// 13. Immediately recreate tables and default settings (like fresh install).
			$database = SkillPulse_LMS_Database::get_instance();
			$database->create_tables();
			$reset_items[] = 'Recreated all database tables with default settings';
			++$reset_count;

			return rest_ensure_response(
				array(
					'success'     => true,
					'reset_count' => $reset_count,
					'reset_items' => $reset_items,
					'message'     => sprintf(
						// translators: %d: The number of items removed during reset.
						__( 'Complete reset successful! Removed %d items and recreated fresh database tables. SkillPulse LMS has been returned to fresh install state.', 'skillpulse-lms' ),
						$reset_count
					),
				)
			);

		} catch ( Exception $e ) {
			return new WP_Error(
				'reset_failed',
				// translators: %s: The exception error message.
				sprintf( __( 'Reset failed: %s', 'skillpulse-lms' ), $e->getMessage() )
			);
		}
	}

	/**
	 * Get system information.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_system_info( $request ) {
		// Check for force refresh parameter.
		$force_refresh = $request->get_param( 'force_refresh' );

		// Clear caches if force refresh is requested.
		if ( $force_refresh ) {
			$this->clear_system_info_caches();
		}

		// Try to get cached data first (unless force refresh).
		if ( ! $force_refresh ) {
			$cached_data = get_transient( 'splms_system_info' );
			if ( false !== $cached_data ) {
				return rest_ensure_response( $cached_data );
			}
		}
		global $wp_version, $wpdb;

		$theme          = wp_get_theme();
		$plugins        = get_plugins();
		$active_plugins = get_option( 'active_plugins' );
		$upload_dir     = wp_upload_dir();

		// Get course counts.
		$course_post_type = defined( 'SPLMS_POST_TYPES' ) && isset( SPLMS_POST_TYPES['course'] ) ? SPLMS_POST_TYPES['course'] : 'sp-course';
		$courses_count    = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
				$course_post_type
			)
		);

		// Get students count - check if table exists first.
		$students_count    = 0;
		$enrollments_table = $wpdb->prefix . 'splms_enrollments';
		$table_exists      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $enrollments_table ) ) === $enrollments_table;

		if ( $table_exists ) {
			$students_count = $wpdb->get_var(
				"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}splms_enrollments"
			);
		}

		// Format active plugins with details.
		$active_plugin_details = array();
		foreach ( $active_plugins as $plugin_path ) {
			if ( isset( $plugins[ $plugin_path ] ) ) {
				$plugin_data             = $plugins[ $plugin_path ];
				$active_plugin_details[] = array(
					'name'    => $plugin_data['Name'],
					'version' => $plugin_data['Version'],
					'author'  => $plugin_data['Author'],
				);
			}
		}

		// Get PHP extensions.
		$php_extensions      = array();
		$required_extensions = array( 'curl', 'gd', 'json', 'mbstring', 'mysql', 'openssl', 'zip', 'xml' );
		foreach ( $required_extensions as $ext ) {
			$php_extensions[] = array(
				'name'   => $ext,
				'loaded' => extension_loaded( $ext ),
				'status' => extension_loaded( $ext ) ? 'Enabled' : 'Disabled',
			);
		}

		// Get directory sizes (cached for 1 hour - expensive operation).
		$directory_sizes = $this->get_directory_sizes_cached( $force_refresh );

		// Get filesystem permissions (cached for 30 minutes).
		$filesystem_permissions = $this->get_filesystem_permissions_cached( $force_refresh );

		$system_info = array(
			// WordPress Environment (Enhanced).
			'wp_version'              => $wp_version,
			'wp_multisite'            => is_multisite(),
			'wp_cache'                => defined( 'WP_CACHE' ) && WP_CACHE,
			'wp_cron_disabled'        => defined( 'DISABLE_WP_CRON' ) ? constant( 'DISABLE_WP_CRON' ) : false,
			'wp_memory_limit'         => WP_MEMORY_LIMIT,
			'wp_max_memory_limit'     => WP_MAX_MEMORY_LIMIT,
			'wp_language'             => get_locale(),
			'wp_timezone'             => wp_timezone_string(),
			'site_url'                => site_url(),
			'home_url'                => home_url(),
			'admin_email'             => get_option( 'admin_email' ),
			'users_can_register'      => get_option( 'users_can_register' ) ? 'Yes' : 'No',
			'default_role'            => get_option( 'default_role' ),
			'auto_updates_core'       => get_option( 'auto_update_core_dev', 'enabled' ),
			'auto_updates_plugins'    => get_option( 'auto_update_plugins' ) ? 'Yes' : 'No',
			'auto_updates_themes'     => get_option( 'auto_update_themes' ) ? 'Yes' : 'No',

			// Server Environment (Enhanced).
			'server_software'         => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'Unknown', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized.
			'server_os'               => PHP_OS,
			'server_architecture'     => php_uname( 'm' ),
			'php_version'             => PHP_VERSION,
			'php_sapi'                => php_sapi_name(),
			'php_memory_limit'        => ini_get( 'memory_limit' ),
			'php_max_execution_time'  => ini_get( 'max_execution_time' ),
			'php_max_input_vars'      => ini_get( 'max_input_vars' ),
			'php_post_max_size'       => ini_get( 'post_max_size' ),
			'php_upload_max_filesize' => ini_get( 'upload_max_filesize' ),
			'php_max_file_uploads'    => ini_get( 'max_file_uploads' ),
			'php_allow_url_fopen'     => ini_get( 'allow_url_fopen' ) ? 'Yes' : 'No',
			'php_display_errors'      => ini_get( 'display_errors' ) ? 'Yes' : 'No',
			'php_session_save_path'   => session_save_path() ? session_save_path() : ini_get( 'session.save_path' ),
			'php_extensions'          => $php_extensions,
			'mysql_version'           => $wpdb->db_version(),
			'mysql_host'              => DB_HOST,
			'mysql_database'          => DB_NAME,
			'mysql_charset'           => DB_CHARSET,
			'mysql_collate'           => DB_COLLATE ? DB_COLLATE : 'Default',

			// Directories and Sizes.
			'wp_path'                 => ABSPATH,
			'wp_content_dir'          => WP_CONTENT_DIR,
			'wp_plugin_dir'           => WP_PLUGIN_DIR,
			'wp_upload_dir'           => $upload_dir['basedir'],
			'wp_upload_url'           => $upload_dir['baseurl'],
			'temp_dir'                => get_temp_dir(),
			'directory_sizes'         => $directory_sizes,
			'disk_free_space'         => $this->get_disk_free_space(),
			'disk_total_space'        => $this->get_disk_total_space(),

			// Filesystem Permissions.
			'filesystem_permissions'  => $filesystem_permissions,

			// Theme Information.
			'theme_name'              => $theme->get( 'Name' ),
			'theme_version'           => $theme->get( 'Version' ),
			'theme_author'            => $theme->get( 'Author' ),
			'theme_uri'               => $theme->get( 'ThemeURI' ),
			'parent_theme'            => $theme->get( 'Template' ) ? wp_get_theme( $theme->get( 'Template' ) )->get( 'Name' ) : null,
			'child_theme'             => is_child_theme() ? 'Yes' : 'No',

			// SkillPulse LMS.
			'lms_version'             => $this->get_lms_version(),
			'lms_db_version'          => get_option( 'splms_db_version', defined( 'SKILLPULSE_LMS_DB_VERSION' ) ? (string) SKILLPULSE_LMS_DB_VERSION : '1' ),
			'total_courses'           => intval( $courses_count ),
			'total_students'          => intval( $students_count ),

			// Active Plugins.
			'active_plugins'          => $active_plugin_details,
			'total_plugins'           => count( $plugins ),
			'mu_plugins'              => $this->get_mu_plugins(),
		);

		// Cache the complete system info for 1 hour.
		set_transient( 'splms_system_info', $system_info, 1 * HOUR_IN_SECONDS ); // 1 hour.

		return rest_ensure_response( $system_info );
	}

	/**
	 * Get LMS version from plugin header or constant.
	 *
	 * @since 1.0.0
	 *
	 * @return string Plugin version.
	 */
	private function get_lms_version() {
		// First try the constant.
		if ( defined( 'SKILLPULSE_LMS_VERSION' ) ) {
			return SKILLPULSE_LMS_VERSION;
		}

		// Fallback to plugin header.
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_file = SKILLPULSE_LMS_DIR_PATH . 'skillpulse-lms.php';
		if ( file_exists( $plugin_file ) ) {
			$plugin_data = get_plugin_data( $plugin_file, false, false );
			if ( ! empty( $plugin_data['Version'] ) ) {
				return $plugin_data['Version'];
			}
		}

		// Final fallback.
		return '1.0.0';
	}

	/**
	 * Clear system info caches.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function clear_system_info_caches() {
		delete_transient( 'splms_system_info' );
		delete_transient( 'splms_directory_sizes' );
		delete_transient( 'splms_filesystem_permissions' );
	}

	/**
	 * Get directory sizes with caching.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $force_refresh Whether to force refresh cache.
	 *
	 * @return array Directory sizes array.
	 */
	private function get_directory_sizes_cached( $force_refresh = false ) {
		$cache_key = 'splms_directory_sizes';

		if ( ! $force_refresh ) {
			$cached_sizes = get_transient( $cache_key );
			if ( false !== $cached_sizes ) {
				return $cached_sizes;
			}
		}

		$sizes = $this->get_directory_sizes();

		// Cache for 1 hour (directory sizes don't change frequently).
		set_transient( $cache_key, $sizes, HOUR_IN_SECONDS );

		return $sizes;
	}

	/**
	 * Get filesystem permissions with caching.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $force_refresh Whether to force refresh cache.
	 *
	 * @return array Filesystem permissions array.
	 */
	private function get_filesystem_permissions_cached( $force_refresh = false ) {
		$cache_key = 'splms_filesystem_permissions';

		if ( ! $force_refresh ) {
			$cached_permissions = get_transient( $cache_key );
			if ( false !== $cached_permissions ) {
				return $cached_permissions;
			}
		}

		$permissions = $this->get_filesystem_permissions();

		// Cache for 30 minutes.
		set_transient( $cache_key, $permissions, 30 * MINUTE_IN_SECONDS );

		return $permissions;
	}

	/**
	 * Get directory sizes.
	 *
	 * @since 1.0.0
	 *
	 * @return array Directory sizes array.
	 */
	private function get_directory_sizes() {
		$sizes = array();

		$directories = array(
			'WordPress Root' => ABSPATH,
			'wp-content'     => WP_CONTENT_DIR,
			'Plugins'        => WP_PLUGIN_DIR,
			'Themes'         => get_theme_root(),
			'Uploads'        => wp_upload_dir()['basedir'],
		);

		foreach ( $directories as $name => $path ) {
			if ( is_dir( $path ) ) {
				$size    = $this->get_directory_size( $path );
				$sizes[] = array(
					'name'           => $name,
					'path'           => $path,
					'size'           => $size,
					'size_formatted' => size_format( $size ),
				);
			}
		}

		return $sizes;
	}

	/**
	 * Get directory size recursively.
	 *
	 * @since 1.0.0
	 *
	 * @param string $directory Directory path.
	 *
	 * @return int Directory size in bytes.
	 */
	private function get_directory_size( $directory ) {
		$size = 0;

		if ( ! is_dir( $directory ) ) {
			return $size;
		}

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( $file->isFile() ) {
					$size += $file->getSize();
				}
			}
		} catch ( Exception $e ) {
			// If we can't read the directory, return 0.
			$size = 0;
		}

		return $size;
	}

	/**
	 * Get filesystem permissions.
	 *
	 * @since 1.0.0
	 *
	 * @return array Filesystem permissions array.
	 */
	private function get_filesystem_permissions() {
		$permissions = array();

		$directories = array(
			'WordPress Root'    => ABSPATH,
			'wp-content'        => WP_CONTENT_DIR,
			'wp-config.php'     => ABSPATH . 'wp-config.php',
			'Plugins Directory' => WP_PLUGIN_DIR,
			'Themes Directory'  => get_theme_root(),
			'Uploads Directory' => wp_upload_dir()['basedir'],
		);

		foreach ( $directories as $name => $path ) {
			if ( file_exists( $path ) ) {
				$permissions[] = array(
					'name'        => $name,
					'path'        => $path,
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_readable -- Direct file operations needed for system info.
					'readable'    => is_readable( $path ),
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Direct file operations needed for system info.
					'writable'    => is_writable( $path ),
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fileperms -- Direct file operations needed for system info.
					'permissions' => substr( sprintf( '%o', fileperms( $path ) ), -4 ),
					'owner'       => function_exists( 'posix_getpwuid' ) && function_exists( 'fileowner' ) ?
						posix_getpwuid( fileowner( $path ) )['name'] ?? 'Unknown' : 'Unknown',
				);
			}
		}

		return $permissions;
	}

	/**
	 * Get Must-Use plugins.
	 *
	 * @since 1.0.0
	 *
	 * @return array Must-Use plugins array.
	 */
	private function get_mu_plugins() {
		$mu_plugins = array();

		if ( function_exists( 'get_mu_plugins' ) ) {
			$mu_plugin_files = get_mu_plugins();
			foreach ( $mu_plugin_files as $plugin_file => $plugin_data ) {
				$mu_plugins[] = array(
					'name'    => $plugin_data['Name'],
					'version' => $plugin_data['Version'],
					'file'    => $plugin_file,
				);
			}
		}

		return $mu_plugins;
	}

	/**
	 * Get disk free space.
	 *
	 * @since 1.0.0
	 *
	 * @return string Formatted disk free space.
	 */
	private function get_disk_free_space() {
		$free_bytes = disk_free_space( ABSPATH );
		return false !== $free_bytes ? size_format( $free_bytes ) : 'Unknown';
	}

	/**
	 * Get disk total space.
	 *
	 * @since 1.0.0
	 *
	 * @return string Formatted disk total space.
	 */
	private function get_disk_total_space() {
		$total_bytes = disk_total_space( ABSPATH );
		return false !== $total_bytes ? size_format( $total_bytes ) : 'Unknown';
	}

	/**
	 * Download system report as a file.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function download_system_report( $request ) {
		// Force refresh cache to get the most current data.
		$this->clear_system_info_caches();

		// Create a mock request object with force_refresh = true.
		$mock_request = new WP_REST_Request( 'GET', '/splms/v1/admin/system-info' );
		$mock_request->set_param( 'force_refresh', true );

		// Get fresh comprehensive system info using the same method as the main endpoint.
		$system_info_response = $this->get_system_info( $mock_request );
		$system_info          = $system_info_response->data;

		// Generate report content.
		$current_date = current_time( 'mysql' );
		$content      = "SkillPulse LMS System Report\n";
		$content     .= "Generated: {$current_date}\n";
		$content     .= str_repeat( '=', 50 ) . "\n\n";

		// WordPress Environment.
		$content .= "=== WordPress Environment ===\n";
		$content .= 'WordPress Version: ' . ( $system_info['wp_version'] ?? 'N/A' ) . "\n";
		$content .= 'Multisite: ' . ( $system_info['wp_multisite'] ? 'Yes' : 'No' ) . "\n";
		$content .= 'Site URL: ' . ( $system_info['site_url'] ?? 'N/A' ) . "\n";
		$content .= 'Home URL: ' . ( $system_info['home_url'] ?? 'N/A' ) . "\n";
		$content .= 'WordPress Memory Limit: ' . ( $system_info['wp_memory_limit'] ?? 'N/A' ) . "\n";
		$content .= 'WordPress Max Memory Limit: ' . ( $system_info['wp_max_memory_limit'] ?? 'N/A' ) . "\n";
		$content .= 'WP Cache: ' . ( $system_info['wp_cache'] ? 'Enabled' : 'Disabled' ) . "\n";
		$content .= 'WP Cron: ' . ( $system_info['wp_cron_disabled'] ? 'Disabled' : 'Enabled' ) . "\n";
		$content .= 'WordPress Language: ' . ( $system_info['wp_language'] ?? 'N/A' ) . "\n";
		$content .= 'WordPress Timezone: ' . ( $system_info['wp_timezone'] ?? 'N/A' ) . "\n";
		$content .= 'Admin Email: ' . ( $system_info['admin_email'] ?? 'N/A' ) . "\n";
		$content .= 'User Signup: ' . ( $system_info['users_can_register'] ?? 'N/A' ) . "\n";
		$content .= 'Default Role: ' . ( $system_info['default_role'] ?? 'N/A' ) . "\n";
		if ( ! empty( $system_info['auto_updates_core'] ) ) {
			$content .= 'Auto Updates (Core): ' . $system_info['auto_updates_core'] . "\n";
		}
		if ( ! empty( $system_info['auto_updates_plugins'] ) ) {
			$content .= 'Auto Updates (Plugins): ' . $system_info['auto_updates_plugins'] . "\n";
		}
		if ( ! empty( $system_info['auto_updates_themes'] ) ) {
			$content .= 'Auto Updates (Themes): ' . $system_info['auto_updates_themes'] . "\n";
		}
		$content .= "\n";

		// Server Environment.
		$content .= "=== Server Environment ===\n";
		$content .= 'Server Software: ' . ( $system_info['server_software'] ?? 'N/A' ) . "\n";
		$content .= 'Server OS: ' . ( $system_info['server_os'] ?? 'N/A' ) . "\n";
		$content .= 'Server Architecture: ' . ( $system_info['server_architecture'] ?? 'N/A' ) . "\n";
		$content .= 'PHP Version: ' . ( $system_info['php_version'] ?? 'N/A' ) . "\n";
		$content .= 'PHP SAPI: ' . ( $system_info['php_sapi'] ?? 'N/A' ) . "\n";
		$content .= 'PHP Memory Limit: ' . ( $system_info['php_memory_limit'] ?? 'N/A' ) . "\n";
		$content .= 'PHP Max Execution Time: ' . ( $system_info['php_max_execution_time'] ?? 'N/A' ) . "s\n";
		$content .= 'PHP Max Input Vars: ' . ( $system_info['php_max_input_vars'] ?? 'N/A' ) . "\n";
		$content .= 'PHP Post Max Size: ' . ( $system_info['php_post_max_size'] ?? 'N/A' ) . "\n";
		$content .= 'PHP Upload Max Filesize: ' . ( $system_info['php_upload_max_filesize'] ?? 'N/A' ) . "\n";
		$content .= 'PHP Max File Uploads: ' . ( $system_info['php_max_file_uploads'] ?? 'N/A' ) . "\n";
		$content .= 'PHP Allow URL fopen: ' . ( $system_info['php_allow_url_fopen'] ?? 'N/A' ) . "\n";
		$content .= 'PHP Display Errors: ' . ( $system_info['php_display_errors'] ?? 'N/A' ) . "\n";
		$content .= 'PHP Session Save Path: ' . ( $system_info['php_session_save_path'] ?? 'N/A' ) . "\n";
		$content .= 'MySQL Version: ' . ( $system_info['mysql_version'] ?? 'N/A' ) . "\n";
		$content .= 'MySQL Host: ' . ( $system_info['mysql_host'] ?? 'N/A' ) . "\n";
		$content .= 'MySQL Database: ' . ( $system_info['mysql_database'] ?? 'N/A' ) . "\n";
		$content .= 'MySQL Charset: ' . ( $system_info['mysql_charset'] ?? 'N/A' ) . "\n";
		$content .= 'MySQL Collate: ' . ( $system_info['mysql_collate'] ?? 'N/A' ) . "\n\n";

		// PHP Extensions.
		$content .= "=== PHP Extensions ===\n";
		if ( ! empty( $system_info['php_extensions'] ) && is_array( $system_info['php_extensions'] ) ) {
			foreach ( $system_info['php_extensions'] as $extension ) {
				$content .= $extension['name'] . ': ' . $extension['status'] . "\n";
			}
		} else {
			$content .= "No PHP extension information available\n";
		}
		$content .= "\n";

		// Directories and Sizes.
		$content .= "=== Directories and Sizes ===\n";
		$content .= 'WordPress Path: ' . ( $system_info['wp_path'] ?? 'N/A' ) . "\n";
		$content .= 'Content Directory: ' . ( $system_info['wp_content_dir'] ?? 'N/A' ) . "\n";
		$content .= 'Plugin Directory: ' . ( $system_info['wp_plugin_dir'] ?? 'N/A' ) . "\n";
		$content .= 'Upload Directory: ' . ( $system_info['wp_upload_dir'] ?? 'N/A' ) . "\n";
		$content .= 'Upload URL: ' . ( $system_info['wp_upload_url'] ?? 'N/A' ) . "\n";
		$content .= 'Temp Directory: ' . ( $system_info['temp_dir'] ?? 'N/A' ) . "\n";
		$content .= 'Disk Free Space: ' . ( $system_info['disk_free_space'] ?? 'N/A' ) . "\n";
		$content .= 'Disk Total Space: ' . ( $system_info['disk_total_space'] ?? 'N/A' ) . "\n";

		if ( ! empty( $system_info['directory_sizes'] ) && is_array( $system_info['directory_sizes'] ) ) {
			$content .= "\nDirectory Sizes:\n";
			foreach ( $system_info['directory_sizes'] as $dir ) {
				$content .= $dir['name'] . ': ' . $dir['size_formatted'] . "\n";
			}
		}
		$content .= "\n";

		// Filesystem Permissions.
		$content .= "=== Filesystem Permissions ===\n";
		if ( ! empty( $system_info['filesystem_permissions'] ) && is_array( $system_info['filesystem_permissions'] ) ) {
			foreach ( $system_info['filesystem_permissions'] as $perm ) {
				$content .= $perm['name'] . ': ';
				$content .= 'Readable=' . ( $perm['readable'] ? 'Yes' : 'No' ) . ', ';
				$content .= 'Writable=' . ( $perm['writable'] ? 'Yes' : 'No' ) . ', ';
				$content .= 'Permissions=' . $perm['permissions'] . ', ';
				$content .= 'Owner=' . $perm['owner'] . "\n";
			}
		} else {
			$content .= "No filesystem permission information available\n";
		}
		$content .= "\n";

		// Theme Information.
		$content .= "=== Theme Information ===\n";
		$content .= 'Active Theme: ' . ( $system_info['theme_name'] ?? 'N/A' ) . "\n";
		$content .= 'Theme Version: ' . ( $system_info['theme_version'] ?? 'N/A' ) . "\n";
		$content .= 'Theme Author: ' . ( $system_info['theme_author'] ?? 'N/A' ) . "\n";
		$content .= 'Theme URI: ' . ( $system_info['theme_uri'] ?? 'N/A' ) . "\n";
		$content .= 'Parent Theme: ' . ( $system_info['parent_theme'] ?? 'None' ) . "\n";
		$content .= 'Child Theme: ' . ( $system_info['child_theme'] ?? 'N/A' ) . "\n\n";

		// SkillPulse LMS.
		$content .= "=== SkillPulse LMS ===\n";
		$content .= 'LMS Version: ' . ( $system_info['lms_version'] ?? 'N/A' ) . "\n";
		$content .= 'Database Version: ' . ( $system_info['lms_db_version'] ?? 'N/A' ) . "\n";
		$content .= 'Total Courses: ' . ( $system_info['total_courses'] ?? '0' ) . "\n";
		$content .= 'Total Students: ' . ( $system_info['total_students'] ?? '0' ) . "\n";

		// Active Plugins.
		$content .= "=== Active Plugins ===\n";
		if ( ! empty( $system_info['total_plugins'] ) ) {
			$content .= 'Total Plugins Installed: ' . $system_info['total_plugins'] . "\n";
		}
		if ( ! empty( $system_info['active_plugins'] ) && is_array( $system_info['active_plugins'] ) ) {
			$content .= 'Active Plugins: ' . count( $system_info['active_plugins'] ) . "\n\n";
			foreach ( $system_info['active_plugins'] as $plugin ) {
				$content .= $plugin['name'] . ' (v' . $plugin['version'] . ') by ' . $plugin['author'] . "\n";
			}
		} else {
			$content .= "No plugin information available\n";
		}
		$content .= "\n";

		// Must-Use Plugins.
		if ( ! empty( $system_info['mu_plugins'] ) && is_array( $system_info['mu_plugins'] ) ) {
			$content .= "=== Must-Use Plugins ===\n";
			foreach ( $system_info['mu_plugins'] as $plugin ) {
				$content .= $plugin['name'] . ' (v' . $plugin['version'] . ")\n";
			}
			$content .= "\n";
		}

		// System Directories.
		$content .= "=== System Directories ===\n";
		$content .= 'WordPress Path: ' . ( $system_info['wp_path'] ?? 'N/A' ) . "\n";
		$content .= 'Upload Directory: ' . ( $system_info['wp_upload_dir'] ?? 'N/A' ) . "\n";
		$content .= 'Upload Directory Writable: ' . ( wp_is_writable( $system_info['wp_upload_dir'] ?? '' ) ? 'Yes' : 'No' ) . "\n\n";

		// Additional Information.
		$content .= "=== Additional Information ===\n";
		$content .= 'Report Generated By: ' . wp_get_current_user()->display_name . ' (ID: ' . get_current_user_id() . ")\n";
		$content .= 'Server Time: ' . current_time( 'mysql' ) . "\n";
		$content .= 'Timezone: ' . wp_timezone_string() . "\n";

		// Generate filename.
		$filename = 'splms-system-report-' . gmdate( 'Y-m-d-H-i-s' ) . '.txt';

		// Return file content directly with download headers.
		$response = new WP_REST_Response( $content, 200 );
		$response->header( 'Content-Type', 'text/plain; charset=utf-8' );
		$response->header( 'Content-Disposition', 'attachment; filename="' . $filename . '"' );
		$response->header( 'Content-Length', strlen( $content ) );
		$response->header( 'Cache-Control', 'no-cache, must-revalidate' );
		$response->header( 'Pragma', 'no-cache' );
		$response->header( 'Expires', '0' );

		return $response;
	}

	/**
	 * Get migration status.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_migration_status( $request ) {
		require_once SKILLPULSE_LMS_DIR_PATH . 'includes/modules/core/class-migration.php';
		$migration = SkillPulse_LMS_Migration::get_instance();

		$status = $migration->get_migration_status();

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $status,
			)
		);
	}

	/**
	 * Run pending migrations.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function run_migrations( $request ) {
		require_once SKILLPULSE_LMS_DIR_PATH . 'includes/modules/core/class-migration.php';
		$migration = SkillPulse_LMS_Migration::get_instance();

		if ( ! $migration->is_migration_needed() ) {
			return rest_ensure_response(
				array(
					'success' => true,
					'message' => __( 'No migrations needed. Database is up to date.', 'skillpulse-lms' ),
					'data'    => $migration->get_migration_status(),
				)
			);
		}

		// Run migrations.
		$results = $migration->run_migrations();

		// Check if any migrations failed.
		$failed_migrations = array_filter(
			$results,
			function ( $result ) {
				return ! $result['success'];
			}
		);

		if ( ! empty( $failed_migrations ) ) {
			return new WP_Error(
				'migration_failed',
				__( 'Some migrations failed to complete.', 'skillpulse-lms' ),
				array(
					'status'  => 500,
					'results' => $results,
				)
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'All migrations completed successfully.', 'skillpulse-lms' ),
				'data'    => array(
					'results' => $results,
					'status'  => $migration->get_migration_status(),
				),
			)
		);
	}

	/**
	 * Get migration logs.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_migration_logs( $request ) {
		$limit   = $request->get_param( 'limit' );
		$version = $request->get_param( 'version' );

		require_once SKILLPULSE_LMS_DIR_PATH . 'includes/modules/core/class-migration.php';
		$migration = SkillPulse_LMS_Migration::get_instance();

		$logs = $migration->get_migration_logs( $limit, $version );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $logs,
			)
		);
	}
}
