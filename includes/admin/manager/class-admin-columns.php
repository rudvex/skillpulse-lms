<?php
/**
 * Admin Columns
 *
 * Handles custom columns for Courses, Lessons, and Quizzes post type listings.
 *
 * @package SPLMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class SPLMS_Admin_Columns
 *
 * Handles custom columns for post type listings.
 *
 * @since 1.0.0
 */
class SPLMS_Admin_Columns {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Admin_Columns|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Admin_Columns The singleton instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * Setup action hooks.
	 *
	 * @since 1.0.0
	 */
	protected function setup_actions() {
		// Courses columns.
		add_filter( 'manage_' . SPLMS_POST_TYPES['course'] . '_posts_columns', array( $this, 'add_course_columns' ) );
		add_action( 'manage_' . SPLMS_POST_TYPES['course'] . '_posts_custom_column', array( $this, 'render_course_column' ), 10, 2 );

		// Sections columns.
		add_filter( 'manage_' . SPLMS_POST_TYPES['section'] . '_posts_columns', array( $this, 'add_section_columns' ) );
		add_action( 'manage_' . SPLMS_POST_TYPES['section'] . '_posts_custom_column', array( $this, 'render_section_column' ), 10, 2 );

		// Lessons columns.
		add_filter( 'manage_' . SPLMS_POST_TYPES['lesson'] . '_posts_columns', array( $this, 'add_lesson_columns' ) );
		add_action( 'manage_' . SPLMS_POST_TYPES['lesson'] . '_posts_custom_column', array( $this, 'render_lesson_column' ), 10, 2 );

		// Quizzes columns.
		add_filter( 'manage_' . SPLMS_POST_TYPES['quiz'] . '_posts_columns', array( $this, 'add_quiz_columns' ) );
		add_action( 'manage_' . SPLMS_POST_TYPES['quiz'] . '_posts_custom_column', array( $this, 'render_quiz_column' ), 10, 2 );

		// Add admin styles for badges.
		add_action( 'admin_head', array( $this, 'add_admin_styles' ) );
	}

	/**
	 * Add admin styles for column badges.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_styles() {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, array( SPLMS_POST_TYPES['course'], SPLMS_POST_TYPES['section'], SPLMS_POST_TYPES['lesson'], SPLMS_POST_TYPES['quiz'] ), true ) ) {
			return;
		}

		$css = '.splms-badge{display:inline-block;padding:3px 8px;border-radius:3px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.3px;line-height:1.4}'
			. '.splms-badge-free,.splms-badge-public-free{background-color:#d4edda;color:#155724}'
			. '.splms-badge-public-paid{background-color:#fff3cd;color:#856404}'
			. '.splms-badge-invitation-only{background-color:#d1ecf1;color:#0c5460}'
			. '.splms-badge-prerequisite-required{background-color:#f8d7da;color:#721c24}'
			. '.splms-badge-text{background-color:#e7f3ff;color:#004085}'
			. '.splms-badge-video{background-color:#ffe7e7;color:#721c24}'
			. '.splms-badge-audio{background-color:#fff4e6;color:#856404}'
			. '.splms-badge-interactive{background-color:#e6f3ff;color:#004085}'
			. '.splms-badge-document,.splms-badge-duration{background-color:#f0f0f0;color:#333}'
			. '.splms-badge-graded,.splms-badge-beginner{background-color:#d4edda;color:#155724}'
			. '.splms-badge-practice,.splms-badge-intermediate{background-color:#fff3cd;color:#856404}'
			. '.splms-badge-survey,.splms-badge-expert{background-color:#d1ecf1;color:#0c5460}'
			. '.splms-badge-advanced{background-color:#f8d7da;color:#721c24}'
			. '.splms-badge-price{background-color:#e7f3ff;color:#004085}';

		wp_add_inline_style( 'wp-admin', $css );
	}

	/**
	 * Add custom columns for Courses.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_course_columns( $columns ) {
		// Insert after title.
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'title' === $key ) {
				$new_columns['splms_students']   = __( 'Students', 'skillpulse-lms' );
				$new_columns['splms_sections']   = __( 'Sections', 'skillpulse-lms' );
				$new_columns['splms_content']    = __( 'Content', 'skillpulse-lms' );
				$new_columns['splms_difficulty'] = __( 'Difficulty', 'skillpulse-lms' );
				$new_columns['splms_access']     = __( 'Access Type', 'skillpulse-lms' );
				$new_columns['splms_price']      = __( 'Price', 'skillpulse-lms' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render custom column content for Courses.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id     Post ID.
	 */
	public function render_course_column( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'splms_students':
				$count = splms_get_course_enrollment_count( $post_id );
				echo esc_html( $count );
				break;

			case 'splms_sections':
				$this->render_course_sections_column( $post_id );
				break;

			case 'splms_content':
				$this->render_course_content_column( $post_id );
				break;

			case 'splms_difficulty':
				$this->render_course_difficulty_column( $post_id );
				break;

			case 'splms_access':
				$this->render_course_access_column( $post_id );
				break;

			case 'splms_price':
				$this->render_course_price_column( $post_id );
				break;
		}
	}

	/**
	 * Render course content column (lessons + quizzes count).
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 */
	private function render_course_content_column( $course_id ) {
		// Get course curriculum using unified method.
		$curriculum_result = splms_get_course_curriculum( $course_id );

		$lessons_count = 0;
		$quizzes_count = 0;

		// Count lessons and quizzes from curriculum.
		if ( isset( $curriculum_result['sections'] ) ) {
			foreach ( $curriculum_result['sections'] as $section ) {
				if ( isset( $section['children'] ) ) {
					foreach ( $section['children'] as $child ) {
						if ( SPLMS_POST_TYPES['lesson'] === $child['type'] ) {
							++$lessons_count;
						} elseif ( SPLMS_POST_TYPES['quiz'] === $child['type'] ) {
							++$quizzes_count;
						}
					}
				}
			}
		}

		$content = array();
		if ( $lessons_count > 0 ) {
			// translators: %d: Number of lessons.
			$content[] = sprintf( _n( '%d Lesson', '%d Lessons', $lessons_count, 'skillpulse-lms' ), $lessons_count );
		}
		if ( $quizzes_count > 0 ) {
			// translators: %d: Number of quizzes.
			$content[] = sprintf( _n( '%d Quiz', '%d Quizzes', $quizzes_count, 'skillpulse-lms' ), $quizzes_count );
		}

		if ( empty( $content ) ) {
			echo '<span aria-label="' . esc_attr__( 'No content', 'skillpulse-lms' ) . '">—</span>';
		} else {
			echo esc_html( implode( ', ', $content ) );
		}
	}

	/**
	 * Render course access column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 */
	private function render_course_access_column( $course_id ) {
		$settings        = splms_get_course_settings( $course_id );
		$access_settings = isset( $settings['course_access_settings'] ) ? $settings['course_access_settings'] : array();
		$access_type     = isset( $access_settings['course_access_type'] ) ? $access_settings['course_access_type'] : 'public_free';

		$labels = array(
			'public_free'           => __( 'Free', 'skillpulse-lms' ),
			'public_paid'           => __( 'Paid', 'skillpulse-lms' ),
			'invitation_only'       => __( 'Invitation', 'skillpulse-lms' ),
			'prerequisite_required' => __( 'Prerequisite', 'skillpulse-lms' ),
		);

		$label = isset( $labels[ $access_type ] ) ? $labels[ $access_type ] : $access_type;
		$class = 'splms-badge splms-badge-' . esc_attr( str_replace( '_', '-', $access_type ) );

		echo '<span class="' . esc_attr( $class ) . '" aria-label="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * Render course sections column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 */
	private function render_course_sections_column( $course_id ) {
		$curriculum_result = splms_get_course_curriculum( $course_id, null, array( 'include_stats' => false ) );

		$sections_count = 0;

		if ( isset( $curriculum_result['sections'] ) && is_array( $curriculum_result['sections'] ) ) {
			$sections_count = count( $curriculum_result['sections'] );
		}

		if ( $sections_count > 0 ) {
			// translators: %d: Number of sections.
			echo esc_html( sprintf( _n( '%d Section', '%d Sections', $sections_count, 'skillpulse-lms' ), $sections_count ) );
		} else {
			echo '<span aria-label="' . esc_attr__( 'No sections', 'skillpulse-lms' ) . '">—</span>';
		}
	}

	/**
	 * Render course difficulty column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 */
	private function render_course_difficulty_column( $course_id ) {
		$settings   = splms_get_course_settings( $course_id );
		$difficulty = isset( $settings['course_level'] ) ? $settings['course_level'] : 'beginner';

		$labels = array(
			'beginner'     => __( 'Beginner', 'skillpulse-lms' ),
			'intermediate' => __( 'Intermediate', 'skillpulse-lms' ),
			'advanced'     => __( 'Advanced', 'skillpulse-lms' ),
			'expert'       => __( 'Expert', 'skillpulse-lms' ),
		);

		$label = isset( $labels[ $difficulty ] ) ? $labels[ $difficulty ] : ucfirst( $difficulty );
		$class = 'splms-badge splms-badge-' . esc_attr( $difficulty );

		echo '<span class="' . esc_attr( $class ) . '" aria-label="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * Render course price column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 */
	private function render_course_price_column( $course_id ) {
		$settings        = splms_get_course_settings( $course_id );
		$access_settings = isset( $settings['course_access_settings'] ) ? $settings['course_access_settings'] : array();
		$access_type     = isset( $access_settings['course_access_type'] ) ? $access_settings['course_access_type'] : 'public_free';

		if ( 'public_free' === $access_type ) {
			echo '<span class="splms-badge splms-badge-free" aria-label="' . esc_attr__( 'Free', 'skillpulse-lms' ) . '">' . esc_html__( 'Free', 'skillpulse-lms' ) . '</span>';
		} elseif ( 'public_paid' === $access_type ) {
			$price = isset( $access_settings['course_price'] ) ? floatval( $access_settings['course_price'] ) : 0;
			if ( $price > 0 ) {
				$price_display = splms_get_price_format( $price );
				echo '<span class="splms-badge splms-badge-price" aria-label="' . esc_attr( $price_display ) . '">' . esc_html( $price_display ) . '</span>';
			} else {
				echo '<span aria-label="' . esc_attr__( 'Price not set', 'skillpulse-lms' ) . '">—</span>';
			}
		} else {
			echo '<span aria-label="' . esc_attr__( 'Not for sale', 'skillpulse-lms' ) . '">—</span>';
		}
	}

	/**
	 * Add custom columns for Sections.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_section_columns( $columns ) {
		// Insert after title.
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'title' === $key ) {
				$new_columns['splms_course']     = __( 'Course', 'skillpulse-lms' );
				$new_columns['splms_duration']   = __( 'Duration', 'skillpulse-lms' );
				$new_columns['splms_difficulty'] = __( 'Difficulty', 'skillpulse-lms' );
				$new_columns['splms_content']    = __( 'Content', 'skillpulse-lms' );

				// Only add pricing column if section-based pricing is enabled.
				if ( splms_get_setting( 'enable_section_based_pricing', false ) ) {
					$new_columns['splms_pricing'] = __( 'Pricing', 'skillpulse-lms' );
				}
			}
		}

		return $new_columns;
	}

	/**
	 * Render custom column content for Sections.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id     Post ID.
	 */
	public function render_section_column( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'splms_course':
				$this->render_section_course_column( $post_id );
				break;

			case 'splms_duration':
				$this->render_section_duration_column( $post_id );
				break;

			case 'splms_difficulty':
				$this->render_section_difficulty_column( $post_id );
				break;

			case 'splms_content':
				$this->render_section_content_column( $post_id );
				break;

			case 'splms_pricing':
				$this->render_section_pricing_column( $post_id );
				break;
		}
	}

	/**
	 * Render section course column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 */
	private function render_section_course_column( $section_id ) {
		$course_id = $this->get_section_course_id( $section_id );
		if ( $course_id ) {
			$course_title = get_the_title( $course_id );
			$edit_url     = get_edit_post_link( $course_id );
			printf(
				'<a href="%s">%s</a>',
				esc_url( $edit_url ),
				esc_html( $course_title )
			);
		} else {
			echo '<span aria-label="' . esc_attr__( 'No course assigned', 'skillpulse-lms' ) . '">—</span>';
		}
	}

	/**
	 * Render section duration column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 */
	private function render_section_duration_column( $section_id ) {
		$duration = $this->get_section_duration( $section_id );
		if ( $duration ) {
			echo '<span class="splms-badge splms-badge-duration" aria-label="' . esc_attr( $duration ) . '">' . esc_html( $duration ) . '</span>';
		} else {
			echo '<span aria-label="' . esc_attr__( 'No duration set', 'skillpulse-lms' ) . '">—</span>';
		}
	}

	/**
	 * Render section difficulty column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 */
	private function render_section_difficulty_column( $section_id ) {
		$difficulty = $this->get_section_difficulty( $section_id );

		$labels = array(
			'beginner'     => __( 'Beginner', 'skillpulse-lms' ),
			'intermediate' => __( 'Intermediate', 'skillpulse-lms' ),
			'advanced'     => __( 'Advanced', 'skillpulse-lms' ),
			'expert'       => __( 'Expert', 'skillpulse-lms' ),
		);

		$label = isset( $labels[ $difficulty ] ) ? $labels[ $difficulty ] : ucfirst( $difficulty );
		$class = 'splms-badge splms-badge-' . esc_attr( $difficulty );

		echo '<span class="' . esc_attr( $class ) . '" aria-label="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * Render section content column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 */
	private function render_section_content_column( $section_id ) {
		$content_count = $this->get_section_content_count( $section_id );

		$lessons_count = isset( $content_count['lessons'] ) ? intval( $content_count['lessons'] ) : 0;
		$quizzes_count = isset( $content_count['quizzes'] ) ? intval( $content_count['quizzes'] ) : 0;

		$content = array();
		if ( $lessons_count > 0 ) {
			// translators: %d: Number of lessons.
			$content[] = sprintf( _n( '%d Lesson', '%d Lessons', $lessons_count, 'skillpulse-lms' ), $lessons_count );
		}
		if ( $quizzes_count > 0 ) {
			// translators: %d: Number of quizzes.
			$content[] = sprintf( _n( '%d Quiz', '%d Quizzes', $quizzes_count, 'skillpulse-lms' ), $quizzes_count );
		}

		if ( empty( $content ) ) {
			echo '<span aria-label="' . esc_attr__( 'No content', 'skillpulse-lms' ) . '">—</span>';
		} else {
			echo esc_html( implode( ', ', $content ) );
		}
	}

	/**
	 * Render section pricing column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 */
	private function render_section_pricing_column( $section_id ) {
		$pricing_info = $this->get_section_pricing_info( $section_id );

		if ( isset( $pricing_info['is_free'] ) && $pricing_info['is_free'] ) {
			echo '<span class="splms-badge splms-badge-free" aria-label="' . esc_attr__( 'Free', 'skillpulse-lms' ) . '">' . esc_html__( 'Free', 'skillpulse-lms' ) . '</span>';
		} elseif ( isset( $pricing_info['price'] ) && $pricing_info['price'] > 0 ) {
			$price_display = splms_get_price_format( $pricing_info['price'] );
			echo '<span class="splms-badge splms-badge-price" aria-label="' . esc_attr( $price_display ) . '">' . esc_html( $price_display ) . '</span>';
		} else {
			echo '<span aria-label="' . esc_attr__( 'No price set', 'skillpulse-lms' ) . '">—</span>';
		}
	}

	/**
	 * Get section's parent course ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 * @return int|null Course ID or null if not found.
	 */
	private function get_section_course_id( $section_id ) {
		$course_items_query = SPLMS_Course_Items_Query::get_instance();
		$course_id          = $course_items_query->get_item_course_id( $section_id, SPLMS_POST_TYPES['section'] );
		return $course_id ? intval( $course_id ) : null;
	}

	/**
	 * Get section duration.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 * @return string Section duration or empty string.
	 */
	private function get_section_duration( $section_id ) {
		$settings = get_post_meta( $section_id, '_splms_section_settings', true );
		return isset( $settings['duration'] ) ? $settings['duration'] : '';
	}

	/**
	 * Get section difficulty level.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 * @return string Section difficulty level.
	 */
	private function get_section_difficulty( $section_id ) {
		$settings = get_post_meta( $section_id, '_splms_section_settings', true );
		return isset( $settings['difficulty_level'] ) ? $settings['difficulty_level'] : 'beginner';
	}

	/**
	 * Get section content count (lessons and quizzes).
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 * @return array Content count with lessons and quizzes.
	 */
	private function get_section_content_count( $section_id ) {
		$section_curriculum = splms_get_section_curriculum( $section_id, null, array( 'include_stats' => false ) );

		$lessons_count = 0;
		$quizzes_count = 0;

		if ( isset( $section_curriculum['items'] ) && is_array( $section_curriculum['items'] ) ) {
			foreach ( $section_curriculum['items'] as $child ) {
				if ( SPLMS_POST_TYPES['lesson'] === $child['type'] ) {
					++$lessons_count;
				} elseif ( SPLMS_POST_TYPES['quiz'] === $child['type'] ) {
					++$quizzes_count;
				}
			}
		}

		return array(
			'lessons' => $lessons_count,
			'quizzes' => $quizzes_count,
		);
	}

	/**
	 * Get section pricing information.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 * @return array Pricing information.
	 */
	private function get_section_pricing_info( $section_id ) {
		$pricing_settings = get_post_meta( $section_id, '_splms_section_pricing', true );

		if ( ! is_array( $pricing_settings ) ) {
			return array(
				'is_free' => true,
				'price'   => 0,
			);
		}

		return array(
			'is_free'    => isset( $pricing_settings['is_free'] ) ? $pricing_settings['is_free'] : true,
			'price'      => isset( $pricing_settings['price'] ) ? floatval( $pricing_settings['price'] ) : 0,
			'sale_price' => isset( $pricing_settings['sale_price'] ) ? floatval( $pricing_settings['sale_price'] ) : 0,
		);
	}

	/**
	 * Add custom columns for Lessons.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_lesson_columns( $columns ) {
		// Insert after title.
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'title' === $key ) {
				$new_columns['splms_course']   = __( 'Course', 'skillpulse-lms' );
				$new_columns['splms_type']     = __( 'Type', 'skillpulse-lms' );
				$new_columns['splms_duration'] = __( 'Duration', 'skillpulse-lms' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render custom column content for Lessons.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id     Post ID.
	 */
	public function render_lesson_column( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'splms_course':
				$this->render_lesson_course_column( $post_id );
				break;

			case 'splms_type':
				$this->render_lesson_type_column( $post_id );
				break;

			case 'splms_duration':
				$this->render_lesson_duration_column( $post_id );
				break;
		}
	}

	/**
	 * Render lesson course column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $lesson_id Lesson ID.
	 */
	private function render_lesson_course_column( $lesson_id ) {
		$course_id = splms_get_lesson_course( $lesson_id );
		if ( $course_id ) {
			$course_title = get_the_title( $course_id );
			$edit_url     = get_edit_post_link( $course_id );
			printf(
				'<a href="%s">%s</a>',
				esc_url( $edit_url ),
				esc_html( $course_title )
			);
		} else {
			echo '<span aria-label="' . esc_attr__( 'No course assigned', 'skillpulse-lms' ) . '">—</span>';
		}
	}

	/**
	 * Render lesson type column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $lesson_id Lesson ID.
	 */
	private function render_lesson_type_column( $lesson_id ) {
		$settings    = splms_get_lesson_settings( $lesson_id );
		$lesson_type = isset( $settings['lesson_type'] ) ? $settings['lesson_type'] : 'text';

		$labels = array(
			'text'        => __( 'Text', 'skillpulse-lms' ),
			'video'       => __( 'Video', 'skillpulse-lms' ),
			'audio'       => __( 'Audio', 'skillpulse-lms' ),
			'interactive' => __( 'Interactive', 'skillpulse-lms' ),
			'document'    => __( 'Document', 'skillpulse-lms' ),
		);

		$label = isset( $labels[ $lesson_type ] ) ? $labels[ $lesson_type ] : $lesson_type;
		$class = 'splms-badge splms-badge-' . esc_attr( $lesson_type );

		echo '<span class="' . esc_attr( $class ) . '" aria-label="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * Render lesson duration column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $lesson_id Lesson ID.
	 */
	private function render_lesson_duration_column( $lesson_id ) {
		$settings = splms_get_lesson_settings( $lesson_id );
		$duration = isset( $settings['lesson_duration'] ) ? intval( $settings['lesson_duration'] ) : 0;

		if ( $duration > 0 ) {
			// translators: %d: Duration in minutes.
			echo esc_html( sprintf( _n( '%d min', '%d mins', $duration, 'skillpulse-lms' ), $duration ) );
		} else {
			echo '<span aria-label="' . esc_attr__( 'No duration set', 'skillpulse-lms' ) . '">—</span>';
		}
	}

	/**
	 * Add custom columns for Quizzes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_quiz_columns( $columns ) {
		// Insert after title.
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'title' === $key ) {
				$new_columns['splms_course']    = __( 'Course', 'skillpulse-lms' );
				$new_columns['splms_type']      = __( 'Type', 'skillpulse-lms' );
				$new_columns['splms_questions'] = __( 'Questions', 'skillpulse-lms' );
				$new_columns['splms_attempts']  = __( 'Attempts', 'skillpulse-lms' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render custom column content for Quizzes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id     Post ID.
	 */
	public function render_quiz_column( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'splms_course':
				$this->render_quiz_course_column( $post_id );
				break;

			case 'splms_type':
				$this->render_quiz_type_column( $post_id );
				break;

			case 'splms_questions':
				$this->render_quiz_questions_column( $post_id );
				break;

			case 'splms_attempts':
				$this->render_quiz_attempts_column( $post_id );
				break;
		}
	}

	/**
	 * Render quiz course column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 */
	private function render_quiz_course_column( $quiz_id ) {
		$course_id = splms_get_quiz_course( $quiz_id );
		if ( $course_id ) {
			$course_title = get_the_title( $course_id );
			$edit_url     = get_edit_post_link( $course_id );
			printf(
				'<a href="%s">%s</a>',
				esc_url( $edit_url ),
				esc_html( $course_title )
			);
		} else {
			echo '<span aria-label="' . esc_attr__( 'No course assigned', 'skillpulse-lms' ) . '">—</span>';
		}
	}

	/**
	 * Render quiz type column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 */
	private function render_quiz_type_column( $quiz_id ) {
		$quiz_type = splms_get_quiz_type( $quiz_id );

		$labels = array(
			'graded'   => __( 'Graded', 'skillpulse-lms' ),
			'practice' => __( 'Practice', 'skillpulse-lms' ),
			'survey'   => __( 'Survey', 'skillpulse-lms' ),
		);

		$label = isset( $labels[ $quiz_type ] ) ? $labels[ $quiz_type ] : $quiz_type;
		$class = 'splms-badge splms-badge-' . esc_attr( $quiz_type );

		echo '<span class="' . esc_attr( $class ) . '" aria-label="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * Render quiz questions column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 */
	private function render_quiz_questions_column( $quiz_id ) {
		$count = splms_get_quiz_questions_count( $quiz_id );
		echo esc_html( $count );
	}

	/**
	 * Render quiz attempts column.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id Quiz ID.
	 */
	private function render_quiz_attempts_column( $quiz_id ) {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'splms_quiz_attempts' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$count = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name cannot be prepared.
				"SELECT COUNT(*) FROM {$table_name} WHERE quiz_id = %d",
				$quiz_id
			)
		);

		echo esc_html( intval( $count ) );
	}
}
