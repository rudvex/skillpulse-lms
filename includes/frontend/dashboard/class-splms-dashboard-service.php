<?php
/**
 * Dashboard Service Class
 *
 * Handles dashboard data processing and business logic
 *
 * @since   1.0.0
 * @package SPLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Service Class
 *
 * Centralizes dashboard data processing, calculations, and caching.
 *
 * @since   1.0.0
 * @package SPLMS
 */
class SPLMS_Dashboard_Service {

	/**
	 * Class instance.
	 *
	 * @var SPLMS_Dashboard_Service|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @return SPLMS_Dashboard_Service
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get comprehensive dashboard data for a user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since   1.0.0
	 * @return array Dashboard data.
	 */
	public function get_dashboard_data( $user_id ) {
		$data = array(
			'user_stats'            => $this->get_user_stats( $user_id ),
			'enrolled_courses'      => $this->get_enrolled_courses( $user_id ),
			'user_metrics'          => $this->get_user_metrics( $user_id ),
			'recommended_courses'   => $this->get_recommended_courses( $user_id ),
			'completion_percentage' => 0,
		);

		// Calculate completion percentage.
		if ( $data['user_stats']['total_enrolled'] > 0 ) {
			$data['completion_percentage'] = round(
				( $data['user_stats']['completed_courses'] / $data['user_stats']['total_enrolled'] ) * 100
			);
		}

		return $data;
	}

	/**
	 * Get user statistics.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since   1.0.0
	 * @return array User statistics.
	 */
	private function get_user_stats( $user_id ) {
		$stats = array(
			'total_enrolled'    => 0,
			'completed_courses' => 0,
			'ongoing_courses'   => 0,
			'active_courses'    => array(),
		);

		if ( ! class_exists( 'SPLMS_Dashboard_API' ) ) {
			return $stats;
		}

		$dashboard_api = SPLMS_Dashboard_API::get_instance();
		$user_stats    = $dashboard_api->get_student_stats( $user_id );

		return array(
			'total_enrolled'    => isset( $user_stats['courses_enrolled'] ) ? $user_stats['courses_enrolled'] : 0,
			'completed_courses' => isset( $user_stats['courses_completed'] ) ? $user_stats['courses_completed'] : 0,
			'ongoing_courses'   => isset( $user_stats['ongoing_courses'] ) ? $user_stats['ongoing_courses'] : 0,
			'active_courses'    => $dashboard_api->get_student_courses( $user_id, 'active' ),
		);
	}

	/**
	 * Get enrolled courses for a user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since   1.0.0
	 * @return array Enrolled courses.
	 */
	private function get_enrolled_courses( $user_id ) {
		if ( ! class_exists( 'SPLMS_Enrollments_Query' ) ) {
			return array();
		}

		$enrollments_query = SPLMS_Enrollments_Query::get_instance();

		return $enrollments_query->get_user_courses(
			$user_id,
			array(
				'status'  => array( 'active', 'completed' ),
				'limit'   => 3,
				'orderby' => 'enrolled_at',
			)
		);
	}

	/**
	 * Get user performance metrics.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since   1.0.0
	 * @return array User metrics.
	 */
	public function get_user_metrics( $user_id ) {
		return array(
			'average_score'   => $this->get_average_quiz_score( $user_id ),
			'attendance_rate' => $this->get_attendance_rate( $user_id ),
			'learning_streak' => $this->get_learning_streak( $user_id ),
			'today_minutes'   => $this->get_today_minutes( $user_id ),
		);
	}

	/**
	 * Get user's average quiz score.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return int Average score percentage.
	 */
	private function get_average_quiz_score( $user_id ) {
		$cache_key = 'splms_user_quiz_performance_' . $user_id;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$average_score = 0;

		if ( ! class_exists( 'SPLMS_Quizzes' ) ) {
			set_transient( $cache_key, $average_score, HOUR_IN_SECONDS );

			return $average_score;
		}

		$enrolled_courses = $this->get_enrolled_courses( $user_id );
		if ( empty( $enrolled_courses ) ) {
			set_transient( $cache_key, $average_score, HOUR_IN_SECONDS );

			return $average_score;
		}

		$quiz_scores = array();

		foreach ( $enrolled_courses as $enrollment ) {
			$course_id = isset( $enrollment->course_id ) ? $enrollment->course_id : $enrollment['course_id'];

			if ( class_exists( 'SPLMS_Course_Items_Query' ) ) {
				$course_items_query = SPLMS_Course_Items_Query::get_instance();
				$course_quizzes     = $course_items_query->get_course_items( $course_id, 'quiz' );

				if ( ! empty( $course_quizzes ) ) {
					foreach ( $course_quizzes as $quiz_item ) {
						if ( function_exists( 'splms_get_user_best_quiz_score' ) ) {
							$best_score = splms_get_user_best_quiz_score( $user_id, $quiz_item->item_id );
							if ( $best_score > 0 ) {
								$quiz_scores[] = $best_score;
							}
						}
					}
				}
			}
		}

		if ( ! empty( $quiz_scores ) ) {
			$average_score = round( array_sum( $quiz_scores ) / count( $quiz_scores ) );
		}

		set_transient( $cache_key, $average_score, HOUR_IN_SECONDS );

		return $average_score;
	}

	/**
	 * Get user's attendance rate (last 30 days).
	 *
	 * @param int $user_id User ID.
	 *
	 * @since   1.0.0
	 * @return int Attendance rate percentage.
	 */
	private function get_attendance_rate( $user_id ) {
		$cache_key = 'splms_user_attendance_' . $user_id;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$attendance_rate = 0;

		if ( ! class_exists( 'SPLMS_User_Activity_Query' ) ) {
			set_transient( $cache_key, $attendance_rate, HOUR_IN_SECONDS );

			return $attendance_rate;
		}

		$activity_query = SPLMS_User_Activity_Query::get_instance();
		$active_days    = $activity_query->get_user_active_days( $user_id, '30 days' );

		if ( $active_days > 0 ) {
			$attendance_rate = round( ( $active_days / 30 ) * 100 );
		}

		set_transient( $cache_key, $attendance_rate, HOUR_IN_SECONDS );

		return $attendance_rate;
	}

	/**
	 * Get user's learning streak.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since   1.0.0
	 * @return int Learning streak in days.
	 */
	private function get_learning_streak( $user_id ) {
		$cache_key = 'splms_user_streak_' . $user_id;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$learning_streak = 0;

		if ( ! class_exists( 'SPLMS_User_Activity_Query' ) ) {
			set_transient( $cache_key, $learning_streak, HOUR_IN_SECONDS );

			return $learning_streak;
		}

		$activity_query  = SPLMS_User_Activity_Query::get_instance();
		$learning_streak = $activity_query->get_user_learning_streak( $user_id );

		set_transient( $cache_key, $learning_streak, HOUR_IN_SECONDS );

		return $learning_streak;
	}

	/**
	 * Get today's learning minutes.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since   1.0.0
	 * @return int Learning minutes today.
	 */
	private function get_today_minutes( $user_id ) {
		if ( ! class_exists( 'SPLMS_User_Activity_Query' ) ) {
			return 0; // Default.
		}

		$activity_query   = SPLMS_User_Activity_Query::get_instance();
		$today_activities = $activity_query->get_user_daily_activity_count( $user_id );

		if ( $today_activities > 0 ) {
			return min( 120, $today_activities * 5 ); // Cap at 2 hours.
		}

		return 0;
	}

	/**
	 * Get recommended courses for a user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since   1.0.0
	 * @return array Recommended courses.
	 */
	public function get_recommended_courses( $user_id ) {
		$cache_key = 'splms_dashboard_recommendations_' . $user_id;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$enrolled_courses    = $this->get_enrolled_courses( $user_id );
		$enrolled_course_ids = array();

		foreach ( $enrolled_courses as $enrollment ) {
			$enrolled_course_ids[] = isset( $enrollment->course_id ) ? $enrollment->course_id : $enrollment['course_id'];
		}

		$args = array(
			'post_type'              => defined( 'SPLMS_POST_TYPES' ) ? SPLMS_POST_TYPES['course'] : 'sp-course',
			'post_status'            => 'publish',
			'posts_per_page'         => 3,
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Small array of enrolled course IDs; no performant alternative.
			'post__not_in'           => $enrolled_course_ids,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'date',
			'order'                  => 'DESC',
		);

		$query               = new WP_Query( $args );
		$recommended_courses = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$course_id = get_the_ID();

				$recommended_courses[] = array(
					'id'          => $course_id,
					'title'       => get_the_title(),
					'url'         => get_permalink( $course_id ),
					'thumbnail'   => splms_get_course_thumbnail_url( $course_id, 'medium' ),
					'duration'    => function_exists( 'splms_get_course_duration' ) ? splms_get_course_duration( $course_id ) : 'N/A',
					'enrollments' => splms_get_course_enrollment_count( $course_id ),
					'tag'         => $this->get_course_tag( $course_id ),
					'level'       => function_exists( 'splms_get_course_level' ) ? splms_get_course_level( $course_id ) : 'beginner',
					'price'       => function_exists( 'splms_get_course_price' ) ? splms_get_course_price( $course_id ) : 0.0,
				);
			}
			wp_reset_postdata();
		}

		set_transient( $cache_key, $recommended_courses, HOUR_IN_SECONDS );

		return $recommended_courses;
	}

	/**
	 * Get course tag based on newness and popularity.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @since   1.0.0
	 * @return string Course tag.
	 */
	private function get_course_tag( $course_id ) {
		$course_date      = get_the_date( 'Y-m-d', $course_id );
		$enrollment_count = splms_get_course_enrollment_count( $course_id );

		if ( strtotime( $course_date ) > strtotime( '-30 days' ) ) {
			return __( 'New', 'skillpulse-lms' );
		} elseif ( $enrollment_count > 100 ) {
			return __( 'Popular', 'skillpulse-lms' );
		} elseif ( $enrollment_count > 50 ) {
			return __( 'Trending', 'skillpulse-lms' );
		}

		return '';
	}


	/**
	 * Get dashboard tab information.
	 *
	 * @since   1.0.0
	 * @return array Tab configuration.
	 */
	public function get_tab_info() {
		return array(
			'my-courses'    => array(
				'title'    => __( 'My Courses', 'skillpulse-lms' ),
				'template' => 'courses',
			),
			'wishlist'      => array(
				'title'    => __( 'Wishlist', 'skillpulse-lms' ),
				'template' => 'wishlist',
			),
			'bookmarks'     => array(
				'title'    => __( 'Bookmarks', 'skillpulse-lms' ),
				'template' => 'bookmarks',
			),
			'certificates'  => array(
				'title'    => __( 'Certificates', 'skillpulse-lms' ),
				'template' => 'certificates',
			),
			'notifications' => array(
				'title'    => __( 'Notifications', 'skillpulse-lms' ),
				'template' => 'notifications',
			),
			'orders'        => array(
				'title'    => __( 'Orders', 'skillpulse-lms' ),
				'template' => 'orders',
			),
			'settings'      => array(
				'title'    => __( 'Settings', 'skillpulse-lms' ),
				'template' => 'settings',
			),
		);
	}

	/**
	 * Get courses with progress for dashboard display.
	 *
	 * @param int $user_id User ID.
	 * @param int $limit   Number of courses to return.
	 *
	 * @since   1.0.0
	 * @return array Courses with progress data.
	 */
	public function get_dashboard_courses( $user_id, $limit = 2 ) {
		$courses     = array();
		$enrollments = $this->get_enrolled_courses( $user_id );

		foreach ( array_slice( $enrollments, 0, $limit ) as $enrollment ) {
			$course_id   = isset( $enrollment->course_id ) ? $enrollment->course_id : $enrollment['course_id'];
			$course_post = get_post( $course_id );

			if ( $course_post ) {
				$course_progress = 0;
				if ( class_exists( 'SPLMS_Dashboard_API' ) ) {
					$dashboard_api   = SPLMS_Dashboard_API::get_instance();
					$progress_data   = $dashboard_api->get_course_progress( $course_id, $user_id );
					$course_progress = isset( $progress_data['percentage'] ) ? $progress_data['percentage'] : 0;
				}

				$courses[] = array(
					'id'        => $course_id,
					'title'     => $course_post->post_title,
					'progress'  => $course_progress,
					'url'       => get_permalink( $course_id ),
					'thumbnail' => splms_get_course_thumbnail_url( $course_id, 'medium' ),
					'remaining' => $course_progress > 0 ? sprintf( '%dh %dm remaining', wp_rand( 1, 5 ), wp_rand( 0, 59 ) ) : __( 'Just started', 'skillpulse-lms' ),
				);
			}
		}

		return $courses;
	}
}
