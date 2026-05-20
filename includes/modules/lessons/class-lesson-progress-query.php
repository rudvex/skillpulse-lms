<?php
/**
 * Lesson Progress Query
 *
 * Handles lesson progress database operations
 *
 * @since      1.0.0
 * @subpackage Lessons
 * @package    SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'SPLMS_Base_Query' ) ) {
	require_once SPLMS_DIR_PATH . 'includes/modules/core/base/class-base-query.php';
}

/**
 * Lesson Progress Query Class
 *
 * @since 1.0.0
 */
class SPLMS_Lesson_Progress_Query extends SPLMS_Base_Query {

	/**
	 * Constructor.
	 *
	 * @param string $table_name Database table name.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function __construct( $table_name ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Necessary for parent constructor call.
		parent::__construct( $table_name );
	}

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Lesson_Progress_Query Class instance.
	 */
	public static function get_instance() {
		return parent::get_base_instance( __CLASS__, 'splms_lesson_progress' );
	}

	/**
	 * Mark lesson as completed.
	 *
	 * @param int $lesson_id  Lesson ID.
	 * @param int $user_id    User ID.
	 * @param int $course_id  Course ID.
	 * @param int $time_spent Time spent in seconds.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Progress ID on success, false on failure.
	 */
	public function complete_lesson( $lesson_id, $user_id, $course_id, $time_spent = 0 ) {
		// Check if already exists.
		$existing = $this->get_lesson_progress( $user_id, $lesson_id );

		if ( $existing ) {
			// Update existing record.
			return $this->update(
				array(
					'is_completed' => 1,
					'completed_at' => current_time( 'mysql' ),
					'time_spent'   => $time_spent,
				),
				array( 'id' => $existing->id ),
				array( '%d', '%s', '%d' ),
				array( '%d' )
			);
		} else {
			// Insert new record.
			$progress_data = array(
				'user_id'      => $user_id,
				'lesson_id'    => $lesson_id,
				'course_id'    => $course_id,
				'is_completed' => 1,
				'completed_at' => current_time( 'mysql' ),
				'time_spent'   => $time_spent,
			);

			return $this->insert(
				$progress_data,
				array( '%d', '%d', '%d', '%d', '%s', '%d' )
			);
		}
	}

	/**
	 * Check if lesson is completed.
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if completed, false otherwise.
	 */
	public function is_lesson_completed( $lesson_id, $user_id ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
		$sql = "SELECT is_completed FROM {$this->table_name} WHERE lesson_id = %d AND user_id = %d";

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL is prepared above, $sql variable usage is safe.
		$result = $wpdb->get_var( $wpdb->prepare( $sql, $lesson_id, $user_id ) );

		return intval( $result ) === 1;
	}

	/**
	 * Get lesson progress data.
	 *
	 * @param int $user_id   User ID.
	 * @param int $lesson_id Lesson ID.
	 *
	 * @since 1.0.0
	 *
	 * @return object|null Progress object on success, null on failure.
	 */
	public function get_lesson_progress( $user_id, $lesson_id ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared, values are prepared.
		$sql = "SELECT * FROM {$this->table_name} WHERE user_id = %d AND lesson_id = %d";

		return $this->get_row( $sql, array( $user_id, $lesson_id ) );
	}

	/**
	 * Save lesson content progress data (video, audio, etc.).
	 *
	 * @param int   $user_id   User ID.
	 * @param int   $lesson_id Lesson ID.
	 * @param int   $course_id Course ID.
	 * @param array $data      Content progress data array.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Progress ID on success, false on failure.
	 */
	public function save_lesson_content_progress( $user_id, $lesson_id, $course_id, $data ) {
		// Validate required parameters.
		if ( ! $user_id || ! $lesson_id || ! $course_id || ! is_array( $data ) ) {
			return false;
		}

		// Check if progress record already exists.
		$existing = $this->get_lesson_progress( $user_id, $lesson_id );

		if ( $existing ) {
			// Update existing record with new data.
			return $this->update(
				array( 'data' => wp_json_encode( $data ) ),
				array( 'id' => $existing->id ),
				array( '%s' ),
				array( '%d' )
			);
		} else {
			// Insert new progress record.
			$progress_data = array(
				'user_id'      => $user_id,
				'lesson_id'    => $lesson_id,
				'course_id'    => $course_id,
				'is_completed' => 0,
				'completed_at' => null,
				'time_spent'   => 0,
				'data'         => wp_json_encode( $data ),
			);

			return $this->insert(
				$progress_data,
				array( '%d', '%d', '%d', '%d', '%s', '%d', '%s' )
			);
		}
	}

	/**
	 * Get lesson content progress data (video, audio, etc.).
	 *
	 * @param int $user_id   User ID.
	 * @param int $lesson_id Lesson ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array|null Progress data array on success, null if not found.
	 */
	public function get_lesson_content_progress( $user_id, $lesson_id ) {
		// Get the progress record.
		$progress = $this->get_lesson_progress( $user_id, $lesson_id );

		if ( ! $progress || empty( $progress->data ) ) {
			return null;
		}

		// Decode JSON data.
		$data = json_decode( $progress->data, true );

		// Return null if JSON decode failed.
		return is_array( $data ) ? $data : null;
	}

	/**
	 * Update lesson time spent without affecting completion status.
	 *
	 * @param int $user_id    User ID.
	 * @param int $lesson_id  Lesson ID.
	 * @param int $time_spent Time spent in seconds.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function update_lesson_time_spent( $user_id, $lesson_id, $time_spent ) {
		$existing = $this->get_lesson_progress( $user_id, $lesson_id );

		if ( ! $existing ) {
			return false;
		}

		return $this->update(
			array( 'time_spent' => intval( $time_spent ) ),
			array( 'id' => $existing->id ),
			array( '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Get video-specific progress data for a lesson.
	 *
	 * @param int $user_id   User ID.
	 * @param int $lesson_id Lesson ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array|null Video progress data or null if not found.
	 */
	public function get_video_progress( $user_id, $lesson_id ) {
		$content_data = $this->get_lesson_content_progress( $user_id, $lesson_id );

		// Return video data if it exists and is video type.
		if ( is_array( $content_data ) &&
			isset( $content_data['type'] ) &&
			'video' === $content_data['type'] &&
			isset( $content_data['video'] ) ) {
			return $content_data['video'];
		}

		return null;
	}

	/**
	 * Save video-specific progress data.
	 *
	 * @param int   $user_id      User ID.
	 * @param int   $lesson_id    Lesson ID.
	 * @param int   $course_id    Course ID.
	 * @param array $video_data   Video progress data.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Progress ID on success, false on failure.
	 */
	public function save_video_progress( $user_id, $lesson_id, $course_id, $video_data ) {
		// Structure the data with content type.
		$progress_data = array(
			'type'  => 'video',
			'video' => $video_data,
		);

		return $this->save_lesson_content_progress( $user_id, $lesson_id, $course_id, $progress_data );
	}
}
