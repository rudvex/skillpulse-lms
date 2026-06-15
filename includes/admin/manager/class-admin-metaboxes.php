<?php
/**
 * Admin Metaboxes
 *
 * Manages metaboxes for custom post types (courses, lessons, quizzes).
 *
 * @package SPLMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class SPLMS_Admin_Metaboxes
 *
 * Manages metaboxes for custom post types (courses, lessons, quizzes).
 *
 * @since 1.0.0
 */
class SPLMS_Admin_Metaboxes {
	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Admin_Metaboxes|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Admin_Metaboxes The singleton instance.
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
	 * Action hooks
	 *
	 * @since 1.0.0
	 */
	protected function setup_actions() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	/**
	 * Add meta boxes to custom post type edit screens.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {
		// Course metaboxes.
		add_meta_box(
			'splms-course-curriculum',
			__( 'Course Curriculum', 'skillpulse-lms' ),
			array( $this, 'render_course_curriculum' ),
			SPLMS_POST_TYPES['course'],
			'normal',
			'high'
		);
		add_meta_box(
			'splms-course-settings',
			__( 'Course Settings', 'skillpulse-lms' ),
			array( $this, 'render_course_settings' ),
			SPLMS_POST_TYPES['course'],
			'normal',
			'high'
		);

		// Lesson metaboxes.
		add_meta_box(
			'splms-lesson-settings',
			__( 'Lesson Settings', 'skillpulse-lms' ),
			array( $this, 'render_lesson_settings' ),
			SPLMS_POST_TYPES['lesson'],
			'normal',
			'high'
		);

		// Quiz metaboxes.
		add_meta_box(
			'splms-quiz-questions',
			__( 'Quiz Questions', 'skillpulse-lms' ),
			array( $this, 'render_quiz_questions' ),
			SPLMS_POST_TYPES['quiz'],
			'normal',
			'high'
		);
		add_meta_box(
			'splms-quiz-settings',
			__( 'Quiz Settings', 'skillpulse-lms' ),
			array( $this, 'render_quiz_settings' ),
			SPLMS_POST_TYPES['quiz'],
			'normal',
			'high'
		);

		// Section metaboxes.
		add_meta_box(
			'splms-section-settings',
			__( 'Section Settings', 'skillpulse-lms' ),
			array( $this, 'render_section_settings' ),
			SPLMS_POST_TYPES['section'],
			'normal',
			'high'
		);
	}

	/**
	 * Render course curriculum metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_course_curriculum( $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by WordPress hook signature.
		?>
		<div class="wrap splms-course-editor-wrap">
			<div id="splms-course-curriculum-page"></div>
		</div>
		<?php
	}

	/**
	 * Render course settings metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_course_settings( $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by WordPress hook signature.
		?>
		<div class="wrap splms-course-editor-wrap">
			<div id="splms-course-settings-page" class=""></div>
		</div>
		<?php
	}

	/**
	 * Render lesson settings metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_lesson_settings( $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by WordPress hook signature.
		?>
		<div class="wrap splms-lesson-editor-wrap">
			<div id="splms-lesson-settings-page" class=""></div>
		</div>
		<?php
	}

	/**
	 * Render quiz questions metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_quiz_questions( $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by WordPress hook signature.
		?>
		<div class="wrap splms-quiz-editor-wrap">
			<div id="splms-quiz-questions-page" class=""></div>
		</div>
		<?php
	}

	/**
	 * Render quiz settings metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_quiz_settings( $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by WordPress hook signature.
		?>
		<div class="wrap splms-quiz-editor-wrap">
			<div id="splms-quiz-settings-page" class=""></div>
		</div>
		<?php
	}

	/**
	 * Render section settings metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_section_settings( $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by WordPress hook signature.
		?>
		<div class="wrap splms-section-editor-wrap">
			<div id="splms-section-settings-page" class=""></div>
		</div>
		<?php
	}
}
