<?php
/**
 * Template Loader
 *
 * Handles template loading and customization for SkillPulse LMS.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Template Loader Class
 *
 * Manages template loading, theme overrides, and body class customization.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */
class SkillPulse_LMS_Template {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Template|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_filters();
		}

		return self::$instance;
	}


	/**
	 * Define all filter hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_filters() {
		add_filter( 'template_include', array( $this, 'template_loader' ) );
		add_filter( 'page_template', array( $this, 'assign_dashboard_template' ) );
		add_filter( 'body_class', array( $this, 'add_body_custom_class' ) );
	}

	/**
	 * Template loader.
	 *
	 * @param string $template Template file path.
	 *
	 * @return string Template file path.
	 */
	public function template_loader( $template ) {
		if ( is_embed() ) {
			return $template;
		}

		// Check for maintenance mode before loading any SkillPulse LMS templates.
		if ( $this->is_maintenance_mode_active() ) {
			return $this->load_maintenance_template();
		}

		$default_file = $this->get_template_loader_default_file();

		if ( $default_file ) {
			/**
			 * Filter hook to choose which files to find before Academy does it's own logic.
			 *
			 * @var array
			 */
			$search_files = $this->get_template_loader_files( $default_file );
			$template     = locate_template( $search_files );

			if ( ! $template ) {
				$template = SKILLPULSE_LMS_DIR_PATH . 'templates/' . $default_file;
			}
		}

		return $template;
	}

	/**
	 * Get template loader files.
	 *
	 * @param string $default_file Default template file.
	 *
	 * @return array Array of template file paths.
	 */
	private function get_template_loader_files( $default_file ) {
		$templates = array();

		// Custom page templates.
		if ( is_page_template() ) {
			$page_template = get_page_template_slug();
			if ( $page_template && 0 === validate_file( $page_template ) ) {
				$templates[] = $page_template;
			}
		}

		// Simple template hierarchy - theme override first, then plugin template.
		if ( $default_file ) {
			// Theme override in skillpulse-lms folder.
			$templates[] = $this->template_path( $default_file );
			// Plugin template.
			$templates[] = $default_file;
		}

		return array_unique( $templates );
	}

	/**
	 * Get the template path.
	 *
	 * @param string $path Template path to append.
	 *
	 * @return string Template path.
	 */
	public static function template_path( $path = '' ) {
		/**
		 * Filter hook to change the template path.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		$template_path = apply_filters( 'splms_template_path', 'skillpulse-lms/' );

		return $template_path . $path;
	}

	/**
	 * Get the default filename for a template.
	 *
	 * @return string
	 */
	private function get_template_loader_default_file() {
		if ( is_singular( SPLMS_POST_TYPES['course'] ) ) {
			$default_file = 'single-course.php';
		} elseif ( is_singular( SPLMS_POST_TYPES['section'] ) ) {
			$default_file = 'single-section.php';
		} elseif ( is_singular( SPLMS_POST_TYPES['lesson'] ) ) {
			$default_file = 'single-lesson.php';
		} elseif ( is_singular( SPLMS_POST_TYPES['quiz'] ) ) {
			$default_file = 'single-quiz.php';
		} elseif ( is_certificate_page() ) {
			$default_file = 'certificate/preview.php';
		} elseif ( is_tax( get_object_taxonomies( SPLMS_POST_TYPES['course'] ) ) ) {
			if ( is_tax( SPLMS_TAXONOMIES['course_category'] ) ) {
				$default_file = 'taxonomy-course-category.php';
			} elseif ( is_tax( SPLMS_TAXONOMIES['course_tag'] ) ) {
				$default_file = 'taxonomy-course-tag.php';
			} else {
				$default_file = 'archive-course.php';
			}
		} elseif ( is_post_type_archive( SPLMS_POST_TYPES['course'] ) || splms_is_course_page() ) {
			$default_file = 'archive-course.php';
		} elseif ( is_author() ) {
			// Theme-style author template.
			$default_file = 'author.php';
		} elseif ( $this->is_signup_page() ) {
			$default_file = 'signup/signup.php';
		} else {
			$default_file = '';
		}

		return $default_file;
	}

	/**
	 * Check if current page is a signup page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_signup_page() {
		$component = get_query_var( 'splms_component' );

		return 'signup' === $component;
	}

	/**
	 * Add custom body classes.
	 *
	 * @param array $classes Body classes.
	 *
	 * @since 1.0.0
	 *
	 * @return array Modified body classes.
	 */
	public function add_body_custom_class( $classes ) {
		global $wp_query;
		$custom_classes   = array();
		$custom_classes[] = 'skillpulse-lms';
		$post_type        = get_query_var( 'post_type' );

		if ( splms_is_course_archive() ) {
			$custom_classes[] = 'splms-course';
		} elseif ( SPLMS_POST_TYPES['lesson'] === $post_type ) {
			$custom_classes[] = 'splms-lesson';
		} elseif ( SPLMS_POST_TYPES['quiz'] === $post_type ) {
			$custom_classes[] = 'splms-quiz';
		} elseif ( ! empty( $wp_query->query['author_name'] ) ) {
			$custom_classes[] = 'splms-author';
		} elseif ( splms_is_course_category_page() ) {
			$custom_classes[] = 'splms-course-category';
		} elseif ( splms_is_course_tag_page() ) {
			$custom_classes[] = 'splms-course-tag';
		} elseif ( splms_is_dashboard_page() ) {
			$custom_classes[] = 'splms-dashboard';
		}

		return array_merge( $classes, $custom_classes );
	}

	/**
	 * Check if maintenance mode is active
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function is_maintenance_mode_active() {
		// Only check maintenance mode for SkillPulse LMS related pages.
		if ( $this->is_exclude_pages_from_maintenance_mode() ) {
			return false;
		}

		// Allow administrators to bypass maintenance mode.
		if ( current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Get maintenance mode setting.
		$maintenance_mode = splms_get_setting( 'maintenance_mode', false );

		return (bool) $maintenance_mode;
	}

	/**
	 * Load maintenance mode template
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function load_maintenance_template() {
		// Look for maintenance template in theme first, then plugin.
		$template_files = array(
			'skillpulse-lms/global/maintenance.php',
			'skillpulse-lms/maintenance.php',
		);

		$template = locate_template( $template_files );

		if ( ! $template ) {
			$template = SKILLPULSE_LMS_DIR_PATH . 'templates/global/maintenance.php';
		}

		// Set appropriate HTTP status.
		if ( ! headers_sent() ) {
			status_header( 503 );
			header( 'Retry-After: 3600' ); // Retry after 1 hour.
		}

		return $template;
	}

	/**
	 * Check if current page is related to SkillPulse LMS
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function is_exclude_pages_from_maintenance_mode() {
		$exclude_pages = false;

		if ( SkillPulse_LMS_Login::get_instance()->is_login_page() ) {
			$exclude_pages = true;
		}

		return apply_filters( 'splms_is_exclude_pages_from_maintenance_mode', $exclude_pages );
	}

	/**
	 * Assign dashboard template to selected page
	 *
	 * @param string $template Template path.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function assign_dashboard_template( $template ) {
		$dashboard_page_id = splms_get_setting( 'dashboard_page_id', 0 );

		if ( ! $dashboard_page_id ) {
			return $template;
		}

		if ( is_page( $dashboard_page_id ) ) {
			// Check for theme override first.
			$theme_template = locate_template(
				array(
					'page-dashboard.php',
					'templates/page-dashboard.php',
				)
			);

			if ( $theme_template ) {
				return $theme_template;
			}

			// Use plugin template.
			$plugin_template = SKILLPULSE_LMS_DIR_PATH . 'templates/page-dashboard.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}
}
