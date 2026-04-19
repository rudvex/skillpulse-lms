<?php
/**
 * Courses Class
 *
 * Handles course-related operations including CRUD and data management.
 *
 * @package SkillPulse_LMS
 * @subpackage Courses
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Courses Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Courses {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Courses|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Courses The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_globals();
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * Setup Globals.
	 *
	 * @since 1.0.0
	 */
	/**
	 * Setup globals.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_globals() {
		// Load course-specific query classes and general functions.
		$files = array(
			'includes/modules/courses/class-course-items-query',
			'includes/modules/courses/class-relationships-query',
			'includes/modules/courses/class-course-frontend',
			'includes/modules/courses/general-functions',
		);

		foreach ( $files as $file ) {
			if ( file_exists( SKILLPULSE_LMS_DIR_PATH . $file . '.php' ) ) {
				require_once SKILLPULSE_LMS_DIR_PATH . $file . '.php';
			}
		}
	}

	/**
	 * Setup action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_actions() {
		add_action( 'delete_post', array( $this, 'delete_course_data' ), 10, 2 );
		add_action( 'save_post_' . SPLMS_POST_TYPES['course'], array( $this, 'clear_related_courses_cache' ), 10, 2 );
		add_action( 'set_object_terms', array( $this, 'clear_related_courses_cache_on_term_change' ), 10, 6 );

		// Initialize course frontend functionality.
		if ( ! is_admin() && class_exists( 'SkillPulse_LMS_Course_Frontend' ) ) {
			SkillPulse_LMS_Course_Frontend::get_instance();
		}
	}

	/**
	 * Get course settings.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 * @return array Course settings.
	 */
	public function get_course_settings( $course_id ) {
		$all_meta = get_post_meta( $course_id );

		// Get configuration data and extract defaults.
		$config_data       = SkillPulse_LMS_Config_Loader::get_config( 'courses', 'admin' );
		$defaults_settings = $this->extract_defaults_from_config( $config_data );

		// If config is empty, return empty array (graceful degradation for get method).
		if ( empty( $defaults_settings ) ) {
			return array();
		}

		$course_settings = array();

		foreach ( $defaults_settings as $key => $default_value ) {
			$meta_key   = "_splms_{$key}";
			$meta_value = isset( $all_meta[ $meta_key ][0] ) ? maybe_unserialize( $all_meta[ $meta_key ][0] ) : null;

			// Handle different data types properly.
			if ( is_array( $default_value ) ) {
				// For array defaults, use wp_parse_args.
				$course_settings[ $key ] = wp_parse_args( (array) $meta_value, $default_value );
			} else {
				// For scalar defaults, use the meta value or fall back to default.
				$course_settings[ $key ] = null !== $meta_value ? $meta_value : $default_value;
			}
		}

		// Allow modules to modify course settings (e.g., auto-assign default certificate).
		$course_settings = apply_filters( 'splms_course_settings_loaded', $course_settings, $course_id );

		return apply_filters( 'splms_get_course_settings', $course_settings, $course_id );
	}

	/**
	 * Update course settings.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $course_id     Course ID.
	 * @param array $new_settings  New settings.
	 * @return array|WP_Error Updated settings or error.
	 */
	public function update_course_settings( $course_id, $new_settings = array() ) {
		if ( empty( $course_id ) || ! is_array( $new_settings ) ) {
			return new WP_Error( 'splms_invalid_course_settings', __( 'Invalid course ID or settings.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Get defaults from configuration.
		$config_data       = SkillPulse_LMS_Config_Loader::get_config( 'courses', 'admin' );
		$defaults_settings = $this->extract_defaults_from_config( $config_data );

		// If config is empty, that's a configuration error that should be fixed.
		if ( empty( $defaults_settings ) ) {
			return new WP_Error( 'missing_config', __( 'Course configuration not found.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Validate and sanitize settings.
		$sanitized_settings = $this->validate_and_sanitize_settings( $new_settings, $defaults_settings );

		// Allow modules to modify sanitized settings (e.g., auto-assign default certificate).
		$sanitized_settings = apply_filters( 'splms_course_settings_sanitized', $sanitized_settings, $course_id );

		foreach ( $defaults_settings as $key => $default_value ) {
			$meta_key = "_splms_{$key}";

			// New values for this group (if provided).
			$new_value = isset( $sanitized_settings[ $key ] ) ? $sanitized_settings[ $key ] : null;

			// Handle different data types properly.
			if ( is_array( $default_value ) ) {
				// For array defaults, use wp_parse_args.
				$final_value = wp_parse_args( (array) $new_value, $default_value );
			} else {
				// For scalar defaults, use the new value or fall back to default.
				$final_value = null !== $new_value ? $new_value : $default_value;
			}

			// Update meta.
			update_post_meta( $course_id, $meta_key, $final_value );
		}

		// Fire action hook after settings are updated.
		do_action( 'splms_course_settings_updated', $course_id, $this->get_course_settings( $course_id ) );

		return $this->get_course_settings( $course_id );
	}

	/**
	 * Validate and sanitize course settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Settings to validate.
	 * @param array $defaults  Default settings structure.
	 * @return array Sanitized settings.
	 */
	private function validate_and_sanitize_settings( $settings, $defaults ) {
		$sanitized = array();

		foreach ( $defaults as $group => $fields ) {
			// Skip if not an array (scalar value for individual field).
			if ( ! is_array( $fields ) ) {
				continue;
			}

			if ( ! isset( $settings[ $group ] ) ) {
				continue;
			}

			$sanitized[ $group ] = array();

			foreach ( $fields as $field_id => $default_value ) {
				if ( ! isset( $settings[ $group ][ $field_id ] ) ) {
					continue;
				}

				$value = $settings[ $group ][ $field_id ];

				// Sanitize based on type.
				if ( is_bool( $default_value ) ) {
					$sanitized[ $group ][ $field_id ] = (bool) $value;
				} elseif ( is_int( $default_value ) ) {
					$int_value = intval( $value );
					// Ensure enrollment limits and similar fields are never negative.
					if ( in_array( $field_id, array( 'max_enrollment', 'guest_lesson_limit', 'guest_quiz_limit', 'enrollment_expiration_days' ), true ) && $int_value < 0 ) {
						$sanitized[ $group ][ $field_id ] = 0;
					} else {
						$sanitized[ $group ][ $field_id ] = $int_value;
					}
				} elseif ( is_float( $default_value ) || is_numeric( $default_value ) ) {
					$float_value = floatval( $value );
					// Ensure price fields are never negative.
					if ( in_array( $field_id, array( 'course_price', 'course_discount' ), true ) && $float_value < 0 ) {
						$sanitized[ $group ][ $field_id ] = 0.0;
					} else {
						$sanitized[ $group ][ $field_id ] = $float_value;
					}
				} elseif ( is_array( $default_value ) ) {
					// For arrays, sanitize each element.
					if ( is_array( $value ) ) {
						// Handle specific field types that need integer preservation.
						if ( in_array( $field_id, array( 'invited_users', 'course_instructors' ), true ) ) {
							$sanitized[ $group ][ $field_id ] = array_map( 'intval', $value );
						} else {
							$sanitized[ $group ][ $field_id ] = array_map( 'sanitize_text_field', $value );
						}
					} else {
						$sanitized[ $group ][ $field_id ] = $default_value;
					}
				} else {
					// For strings and other types.
					$sanitized_value = sanitize_text_field( $value );

					// Special validation for specific fields.
					if ( 'difficulty_level' === $field_id ) {
						$valid_levels = array( 'all', 'beginner', 'intermediate', 'advanced' );
						if ( ! in_array( $sanitized_value, $valid_levels, true ) ) {
							$sanitized_value = $default_value; // Fall back to default.
						}
					}

					$sanitized[ $group ][ $field_id ] = $sanitized_value;
				}
			}
		}

		return apply_filters( 'splms_sanitize_course_settings', $sanitized, $settings, $defaults );
	}

	/**
	 * Get courses.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query arguments.
	 * @return array Array of course posts.
	 */
	public function get( $args ) {
		$defaults = array(
			'number'    => 10,
			'offset'    => 0,
			'orderby'   => 'title',
			'order'     => 'ASC',
			'fields'    => 'all',
			'course_id' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$query_args = array(
			'post_type'      => SPLMS_POST_TYPES['course'],
			'posts_per_page' => $args['number'],
			'offset'         => $args['offset'],
			'orderby'        => $args['orderby'],
			'order'          => $args['order'],
			'fields'         => $args['fields'],
		);

		if ( $args['course_id'] ) {
			$query_args['p'] = $args['course_id'];
		}

		$courses = get_posts( $query_args );

		return $courses;
	}

	/**
	 * Create a new course.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Course arguments.
	 * @return int|WP_Error Course ID on success, WP_Error on failure.
	 */
	public function create( $args ) {
		$defaults = array(
			'post_title'    => '',
			'post_content'  => '',
			'post_status'   => 'publish',
			'post_type'     => SPLMS_POST_TYPES['course'],
			'post_password' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$course_id = wp_insert_post( $args );

		return $course_id;
	}

	/**
	 * Update an existing course.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $id   Course ID.
	 * @param array $args Course arguments.
	 * @return int|WP_Error Course ID on success, WP_Error on failure.
	 */
	public function update( $id, $args ) {
		// Get existing post to preserve current values.
		$existing_post = get_post( $id );
		if ( ! $existing_post ) {
			return new WP_Error( 'invalid_course_id', __( 'Invalid course ID.', 'skillpulse-lms' ) );
		}

		$defaults = array(
			'post_title'    => $existing_post->post_title,
			'post_content'  => $existing_post->post_content,
			'post_status'   => $existing_post->post_status,
			'post_password' => $existing_post->post_password,
		);

		$args = wp_parse_args( $args, $defaults );

		$args['ID'] = $id;

		$course_id = wp_update_post( $args );

		return $course_id;
	}

	/**
	 * Delete a course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Course ID.
	 * @return WP_Post|false|null Post object on success, false or null on failure.
	 */
	public function delete( $id ) {
		$result = wp_delete_post( $id );

		return $result;
	}

	/**
	 * Delete course data when a course is deleted.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $course_id Course ID.
	 * @param WP_Post $post      Post object.
	 * @return void
	 */
	public function delete_course_data( $course_id, $post ) {
		// Only run cleanup for actual course deletions, not for other post types.
		if ( ! $post || SPLMS_POST_TYPES['course'] !== $post->post_type ) {
			return;
		}

		// Only run full cleanup when post is being permanently deleted (not just trashed)
		// If post status is 'trash', this is just moving to trash, preserve relationships.
		if ( 'trash' === $post->post_status ) {
			$this->clear_related_courses_cache( $course_id, $post );
			return;
		}

		// Run full cleanup for permanent deletion.
		SkillPulse_LMS_Relationships_Query::get_instance();
		$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
		$course_items_query->delete_items( $course_id );

		// Clear related courses cache for this course.
		$this->clear_related_courses_cache( $course_id, $post );
	}

	/**
	 * Clear related courses cache when a course is saved or updated.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public function clear_related_courses_cache( $post_id, $post ) {
		// Only process course post type.
		if ( SPLMS_POST_TYPES['course'] !== $post->post_type ) {
			return;
		}

		// Clear cache for this course.
		delete_transient( 'splms_related_courses_' . $post_id );

		// Clear cache for other courses that might have this course as related.
		// This happens when categories or tags change.
		$this->clear_related_courses_cache_for_other_courses( $post_id );
	}

	/**
	 * Clear related courses cache when taxonomy terms are updated.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $object_id  Object ID.
	 * @param array  $terms      Terms array.
	 * @param array  $tt_ids     Term taxonomy IDs.
	 * @param string $taxonomy   Taxonomy slug.
	 * @param bool   $append     Whether to append new terms.
	 * @param array  $old_tt_ids Old term taxonomy IDs.
	 */
	/**
	 * Clear related courses cache when taxonomy terms are updated.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $object_id  Object ID.
	 * @param array  $terms      Terms array.
	 * @param array  $tt_ids     Term taxonomy IDs.
	 * @param string $taxonomy   Taxonomy slug.
	 * @param bool   $append     Whether to append new terms.
	 * @param array  $old_tt_ids Old term taxonomy IDs.
	 * @return void
	 */
	public function clear_related_courses_cache_on_term_change( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameters required by hook signature.
		// Only process course post type and course taxonomies.
		$post = get_post( $object_id );
		if ( ! $post || SPLMS_POST_TYPES['course'] !== $post->post_type ) {
			return;
		}

		$course_taxonomies = array(
			SPLMS_TAXONOMIES['course_category'],
			SPLMS_TAXONOMIES['course_tag'],
		);

		if ( ! in_array( $taxonomy, $course_taxonomies, true ) ) {
			return;
		}

		// Clear cache for this course.
		delete_transient( 'splms_related_courses_' . $object_id );

		// Clear cache for other courses that might have this course as related.
		$this->clear_related_courses_cache_for_other_courses( $object_id );
	}

	/**
	 * Clear related courses cache for other courses that might reference this course.
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id Course ID.
	 * @return void
	 */
	private function clear_related_courses_cache_for_other_courses( $course_id ) {
		// Get all published courses (excluding the current one).
		$all_courses = get_posts(
			array(
				'post_type'      => SPLMS_POST_TYPES['course'],
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post__not_in'   => array( $course_id ),
				'fields'         => 'ids',
			)
		);

		// Clear cache for all courses (they might have this course as related).
		// Note: This is a broad approach. For better performance on large sites,
		// you could track which courses share categories/tags and only clear those.
		foreach ( $all_courses as $other_course_id ) {
			delete_transient( 'splms_related_courses_' . $other_course_id );
		}
	}

	/**
	 * Extract default values from course config data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config_data Course configuration data.
	 * @return array Default values organized by groups and individual fields.
	 */
	private function extract_defaults_from_config( $config_data ) {
		$defaults          = array();
		$field_definitions = array();

		if ( ! isset( $config_data['sections'] ) || ! is_array( $config_data['sections'] ) ) {
			return $defaults;
		}

		// First, collect all field definitions to identify groups.
		foreach ( $config_data['sections'] as $section ) {
			if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
				foreach ( $section['fields'] as $field ) {
					if ( isset( $field['id'] ) ) {
						$field_definitions[ $field['id'] ] = $field;
					}
				}
			}
		}

		// Group fields by their 'group' attribute.
		$grouped_fields    = array();
		$individual_fields = array();

		foreach ( $field_definitions as $field_id => $field ) {
			if ( isset( $field['group'] ) && ! empty( $field['group'] ) ) {
				$group_name = $field['group'];
				if ( ! isset( $grouped_fields[ $group_name ] ) ) {
					$grouped_fields[ $group_name ] = array();
				}
				$grouped_fields[ $group_name ][ $field_id ] = $field['default'] ?? '';
			} else {
				$individual_fields[ $field_id ] = $field['default'] ?? '';
			}
		}

		// Combine grouped and individual fields.
		$defaults = array_merge( $grouped_fields, $individual_fields );

		return $defaults;
	}


	/**
	 * Get course difficulty level.
	 *
	 * @param int $course_id Course ID.
	 * @return string Course level.
	 */
	public function get_course_level( $course_id ) {
		$course_settings = $this->get_course_settings( $course_id );

		return isset( $course_settings['course_content_settings']['difficulty_level'] ) ? $course_settings['course_content_settings']['difficulty_level'] : 'beginner';
	}

	/**
	 * Get course price.
	 *
	 * @param int $course_id Course ID.
	 * @return float Course price.
	 */
	public function get_course_price( $course_id ) {
		$course_settings = $this->get_course_settings( $course_id );

		return isset( $course_settings['course_pricing_settings']['course_price'] ) ? (float) $course_settings['course_pricing_settings']['course_price'] : 0.0;
	}
}
