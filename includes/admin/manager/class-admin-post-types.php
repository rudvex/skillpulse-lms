<?php
/**
 * Admin Post Types
 *
 * Handles registration and management of custom post types and taxonomies.
 *
 * @package SPLMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class SPLMS_Admin_Post_Types
 *
 * Handles registration and management of custom post types and taxonomies.
 *
 * @since 1.0.0
 */
class SPLMS_Admin_Post_Types {
	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Admin_Post_Types|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Admin_Post_Types The singleton instance.
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
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Register all custom post types.
	 *
	 * @since 1.0.0
	 */
	public function register_post_types() {
		$this->register_course_post_type();
		$this->register_section_post_type();
		$this->register_lesson_post_type();
		$this->register_quiz_post_type();

		// Only register certificate post type if certificates are enabled.
		if ( splms_get_setting( 'enable_certificates', false ) ) {
			$this->register_certificate_post_type();
		}
		$this->register_assignment_post_type();
	}

	/**
	 * Register course post type and related taxonomies.
	 *
	 * @since 1.0.0
	 */
	public function register_course_post_type() {
		$labels = array(
			'name'               => _x( 'Courses', 'post type general name', 'skillpulse-lms' ),
			'singular_name'      => _x( 'Course', 'post type singular name', 'skillpulse-lms' ),
			'menu_name'          => _x( 'Courses', 'admin menu', 'skillpulse-lms' ),
			'name_admin_bar'     => _x( 'Course', 'add new on admin bar', 'skillpulse-lms' ),
			'add_new'            => _x( 'Add New', 'course', 'skillpulse-lms' ),
			'add_new_item'       => __( 'Add New', 'skillpulse-lms' ),
			'new_item'           => __( 'New Course', 'skillpulse-lms' ),
			'edit_item'          => __( 'Edit Course ', 'skillpulse-lms' ),
			'view_item'          => __( 'View Course', 'skillpulse-lms' ),
			'all_items'          => __( 'All Courses', 'skillpulse-lms' ),
			'search_items'       => __( 'Search Course', 'skillpulse-lms' ),
			'parent_item_colon'  => __( 'Parent Courses:', 'skillpulse-lms' ),
			'not_found'          => __( 'No courses found.', 'skillpulse-lms' ),
			'not_found_in_trash' => __( 'No courses found in Trash.', 'skillpulse-lms' ),

		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'skillpulse-lms' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'course' ),
			'capability_type'    => 'page',
			'has_archive'        => 'courses',
			'hierarchical'       => false,
			'show_in_rest'       => true,
			'menu_position'      => 5,
			'taxonomies'         => array( SPLMS_TAXONOMIES['course_category'], SPLMS_TAXONOMIES['course_tag'] ),
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'trash' ),
		);

		register_post_type( SPLMS_POST_TYPES['course'], $args );

		if ( splms_get_setting( 'course_categories_enabled', true ) ) {
			// Register course taxonomy.
			$labels = array(
				'name'              => _x( 'Course Categories', 'taxonomy general name', 'skillpulse-lms' ),
				'singular_name'     => _x( 'Course Category', 'taxonomy singular name', 'skillpulse-lms' ),
				'search_items'      => __( 'Search Course Categories', 'skillpulse-lms' ),
				'all_items'         => __( 'All Course Categories', 'skillpulse-lms' ),
				'parent_item'       => __( 'Parent Course Category', 'skillpulse-lms' ),
				'parent_item_colon' => __( 'Parent Course Category:', 'skillpulse-lms' ),
				'edit_item'         => __( 'Edit Category', 'skillpulse-lms' ),
				'update_item'       => __( 'Update Category', 'skillpulse-lms' ),
				'add_new_item'      => __( 'Add New Category', 'skillpulse-lms' ),
				'new_item_name'     => __( 'New Category Name', 'skillpulse-lms' ),
				'menu_name'         => __( 'Course Categories', 'skillpulse-lms' ),
			);

			$args = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'course-category' ),
			);

			register_taxonomy( SPLMS_TAXONOMIES['course_category'], SPLMS_POST_TYPES['course'], $args );
		}

		if ( splms_get_setting( 'course_tags_enabled', true ) ) {
			// Register course tags.
			$labels = array(
				'name'              => _x( 'Course Tags', 'taxonomy general name', 'skillpulse-lms' ),
				'singular_name'     => _x( 'Course Tag', 'taxonomy singular name', 'skillpulse-lms' ),
				'search_items'      => __( 'Search Course Tags', 'skillpulse-lms' ),
				'all_items'         => __( 'All Course Tags', 'skillpulse-lms' ),
				'parent_item'       => __( 'Parent Course Tag', 'skillpulse-lms' ),
				'parent_item_colon' => __( 'Parent Course Tag:', 'skillpulse-lms' ),
				'edit_item'         => __( 'Edit Tag', 'skillpulse-lms' ),
				'update_item'       => __( 'Update Tag', 'skillpulse-lms' ),
				'add_new_item'      => __( 'Add New Tag', 'skillpulse-lms' ),
				'new_item_name'     => __( 'New Course Tag Name', 'skillpulse-lms' ),
				'menu_name'         => __( 'Course Tags', 'skillpulse-lms' ),
			);

			$args = array(
				'hierarchical'      => false,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'course-tag' ),
			);

			register_taxonomy( SPLMS_TAXONOMIES['course_tag'], SPLMS_POST_TYPES['course'], $args );
		}
	}

	/**
	 * Register section post type.
	 *
	 * @since 1.0.0
	 */
	public function register_section_post_type() {
		$labels = array(
			'name'               => _x( 'Sections', 'post type general name', 'skillpulse-lms' ),
			'singular_name'      => _x( 'Section', 'post type singular name', 'skillpulse-lms' ),
			'menu_name'          => _x( 'Sections', 'admin menu', 'skillpulse-lms' ),
			'name_admin_bar'     => _x( 'Section', 'add new on admin bar', 'skillpulse-lms' ),
			'add_new'            => _x( 'Add New', 'section', 'skillpulse-lms' ),
			'add_new_item'       => __( 'Add New Section', 'skillpulse-lms' ),
			'new_item'           => __( 'New Section', 'skillpulse-lms' ),
			'edit_item'          => __( 'Edit Section', 'skillpulse-lms' ),
			'view_item'          => __( 'View Section', 'skillpulse-lms' ),
			'all_items'          => __( 'All Sections', 'skillpulse-lms' ),
			'search_items'       => __( 'Search Sections', 'skillpulse-lms' ),
			'parent_item_colon'  => __( 'Parent Sections:', 'skillpulse-lms' ),
			'not_found'          => __( 'No sections found.', 'skillpulse-lms' ),
			'not_found_in_trash' => __( 'No sections found in Trash.', 'skillpulse-lms' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'skillpulse-lms' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'section' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'show_in_rest'       => true,
			'menu_position'      => 5,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		);

		register_post_type( SPLMS_POST_TYPES['section'], $args );
	}

	/**
	 * Register lesson post type.
	 *
	 * @since 1.0.0
	 */
	public function register_lesson_post_type() {
		$labels = array(
			'name'               => _x( 'Lessons', 'post type general name', 'skillpulse-lms' ),
			'singular_name'      => _x( 'Lesson', 'post type singular name', 'skillpulse-lms' ),
			'menu_name'          => _x( 'Lessons', 'admin menu', 'skillpulse-lms' ),
			'name_admin_bar'     => _x( 'Lesson', 'add new on admin bar', 'skillpulse-lms' ),
			'add_new'            => _x( 'Add New', 'lesson', 'skillpulse-lms' ),
			'add_new_item'       => __( 'Add New Lesson', 'skillpulse-lms' ),
			'new_item'           => __( 'New Lesson', 'skillpulse-lms' ),
			'edit_item'          => __( 'Edit Lesson', 'skillpulse-lms' ),
			'view_item'          => __( 'View Lesson', 'skillpulse-lms' ),
			'all_items'          => __( 'All Lessons', 'skillpulse-lms' ),
			'search_items'       => __( 'Search Lessons', 'skillpulse-lms' ),
			'parent_item_colon'  => __( 'Parent Lessons:', 'skillpulse-lms' ),
			'not_found'          => __( 'No lessons found.', 'skillpulse-lms' ),
			'not_found_in_trash' => __( 'No lessons found in Trash.', 'skillpulse-lms' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'skillpulse-lms' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'lesson' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'show_in_rest'       => true,
			'menu_position'      => 5,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
		);

		register_post_type( SPLMS_POST_TYPES['lesson'], $args );
	}

	/**
	 * Register quiz post type.
	 *
	 * @since 1.0.0
	 */
	public function register_quiz_post_type() {
		$labels = array(
			'name'               => _x( 'Quizzes', 'post type general name', 'skillpulse-lms' ),
			'singular_name'      => _x( 'Quiz', 'post type singular name', 'skillpulse-lms' ),
			'menu_name'          => _x( 'Quizzes', 'admin menu', 'skillpulse-lms' ),
			'name_admin_bar'     => _x( 'Quiz', 'add new on admin bar', 'skillpulse-lms' ),
			'add_new'            => _x( 'Add New', 'quiz', 'skillpulse-lms' ),
			'add_new_item'       => __( 'Add New Quiz', 'skillpulse-lms' ),
			'new_item'           => __( 'New Quiz', 'skillpulse-lms' ),
			'edit_item'          => __( 'Edit Quiz', 'skillpulse-lms' ),
			'view_item'          => __( 'View Quiz', 'skillpulse-lms' ),
			'all_items'          => __( 'All Quizzes', 'skillpulse-lms' ),
			'search_items'       => __( 'Search Quizzes', 'skillpulse-lms' ),
			'parent_item_colon'  => __( 'Parent Quizzes:', 'skillpulse-lms' ),
			'not_found'          => __( 'No quizzes found.', 'skillpulse-lms' ),
			'not_found_in_trash' => __( 'No quizzes found in Trash.', 'skillpulse-lms' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'skillpulse-lms' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'quiz' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'show_in_rest'       => true,
			'menu_position'      => 5,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
		);

		register_post_type( SPLMS_POST_TYPES['quiz'], $args );
	}

	/**
	 * Register certificate post type - Simple approach like LearnDash.
	 *
	 * @since 1.0.0
	 */
	public function register_certificate_post_type() {
		$labels = array(
			'name'               => _x( 'Certificates', 'post type general name', 'skillpulse-lms' ),
			'singular_name'      => _x( 'Certificate', 'post type singular name', 'skillpulse-lms' ),
			'menu_name'          => _x( 'Certificates', 'admin menu', 'skillpulse-lms' ),
			'name_admin_bar'     => _x( 'Certificate', 'add new on admin bar', 'skillpulse-lms' ),
			'add_new'            => _x( 'Add New', 'certificate', 'skillpulse-lms' ),
			'add_new_item'       => __( 'Add New Certificate', 'skillpulse-lms' ),
			'new_item'           => __( 'New Certificate', 'skillpulse-lms' ),
			'edit_item'          => __( 'Edit Certificate', 'skillpulse-lms' ),
			'view_item'          => __( 'View Certificate', 'skillpulse-lms' ),
			'all_items'          => __( 'All Certificates', 'skillpulse-lms' ),
			'search_items'       => __( 'Search Certificates', 'skillpulse-lms' ),
			'parent_item_colon'  => __( 'Parent Certificates:', 'skillpulse-lms' ),
			'not_found'          => __( 'No certificates found.', 'skillpulse-lms' ),
			'not_found_in_trash' => __( 'No certificates found in Trash.', 'skillpulse-lms' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'skillpulse-lms' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'certificate' ),
			'capability_type'    => 'post',
			'has_archive'        => 'certificate',
			'hierarchical'       => false,
			'menu_position'      => 5,
			'supports'           => array( 'title' ),
			'show_in_rest'       => true,
		);

		register_post_type( SPLMS_POST_TYPES['certificate'], $args );
	}

	/**
	 * Register assignment post type.
	 *
	 * @since 1.0.0
	 */
	public function register_assignment_post_type() {
	}
}
