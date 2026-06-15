<?php
/**
 * Course Items Query Class
 *
 * Handles database queries for course items.
 *
 * @since      1.0.0
 * @subpackage Courses
 * @package SPLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'SPLMS_Base_Query' ) ) {
	require_once SPLMS_DIR_PATH . 'includes/modules/core/base/class-base-query.php';
}

/**
 * Course Items Query Class
 *
 * @since 1.0.0
 */
class SPLMS_Course_Items_Query extends SPLMS_Base_Query {
	/**
	 * Constructor.
	 *
	 * @param string $table_name Table name.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $table_name ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Constructor needed to call parent.
		parent::__construct( $table_name );
	}

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Course_Items_Query The class instance.
	 */
	public static function get_instance() {
		return parent::get_base_instance( __CLASS__, 'splms_course_items' );
	}

	/**
	 * Add or update a course item.
	 *
	 * @param array $args Item arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Item ID on success, false on failure.
	 */
	public function add_item( $args ) {
		$defaults = array(
			'course_id'   => 0,
			'item_id'     => 0,
			'item_type'   => '',
			'order_index' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$item = $this->get_item( $args['item_id'] );

		if ( empty( $item ) ) {
			return $this->insert_item( $args );
		}

		return $this->update_item( $args['item_id'], $args );
	}

	/**
	 * Insert a new course item.
	 *
	 * @param array $args Item arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Item ID on success, false on failure.
	 */
	public function insert_item( $args ) {
		$defaults = array(
			'course_id'   => 0,
			'item_id'     => 0,
			'item_type'   => '',
			'order_index' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->insert(
			$args,
			array( '%d', '%d', '%s', '%d' )
		);
	}

	/**
	 * Update an existing course item.
	 *
	 * @param int   $id   Item ID.
	 * @param array $args Item arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Number of rows affected on success, false on failure.
	 */
	public function update_item( $id, $args = array() ) {
		$defaults = array(
			'course_id'   => 0,
			'item_id'     => 0,
			'item_type'   => '',
			'order_index' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->update(
			$args,
			array( 'item_id' => $id ),
			array( '%d', '%d', '%s', '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Delete a course item.
	 *
	 * @param int $item_id Item ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Number of rows affected on success, false on failure.
	 */
	public function delete_item( $item_id ) {
		return $this->delete( array( 'item_id' => $item_id ), array( '%d' ) );
	}

	/**
	 * Get a single course item by ID.
	 *
	 * @param int    $item_id   Item ID.
	 * @param string $item_type Item type filter.
	 *
	 * @since 1.0.0
	 *
	 * @return object|null Item object or null if not found.
	 */
	public function get_item( $item_id, $item_type = '' ) {
		$query = "SELECT * FROM {$this->table_name} WHERE item_id = %d AND item_type = %s";

		return $this->get_row( $query, array( $item_id, $item_type ) );
	}

	/**
	 * Get all items for a course.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of item objects.
	 */
	public function get_items( $course_id ) {
		$query = "SELECT * FROM {$this->table_name} WHERE course_id = %d ORDER BY order_index ASC";

		return $this->get_results( $query, array( $course_id ) );
	}

	/**
	 * Get course items filtered by item type.
	 *
	 * @param int    $course_id Course ID.
	 * @param string $item_type Optional. Item type to filter by (e.g., 'quiz', 'lesson', 'section').
	 *                          Can be item type string or post type. Default: empty (all items).
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of course items objects, or empty array if none found.
	 */
	public function get_course_items( $course_id, $item_type = '' ) {
		$base_query   = "SELECT * FROM {$this->table_name} WHERE course_id = %d";
		$query_params = array( $course_id );

		// Add item type filter if specified.
		if ( ! empty( $item_type ) ) {
			// Handle both direct item type ('quiz') and post type constant (SPLMS_POST_TYPES['quiz']).
			if ( defined( 'SPLMS_POST_TYPES' ) && isset( SPLMS_POST_TYPES[ $item_type ] ) ) {
				$item_type = SPLMS_POST_TYPES[ $item_type ];
			}

			$base_query    .= ' AND item_type = %s';
			$query_params[] = $item_type;
		}

		$query = $base_query . ' ORDER BY order_index ASC';

		return $this->get_results( $query, $query_params );
	}

	/**
	 * Delete all items for a course.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Number of rows affected on success, false on failure.
	 */
	public function delete_items( $course_id ) {
		$items = $this->get_items( $course_id );
		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				do_action( 'splms_delete_course_item', $item->item_id );
			}
		}

		return $this->delete( array( 'course_id' => $course_id ), array( '%d' ) );
	}

	/**
	 * Get course ID for a specific item.
	 *
	 * @param int    $item_id   Item ID.
	 * @param string $item_type Item type.
	 *
	 * @since 1.0.0
	 *
	 * @return int Course ID or 0 if not found.
	 */
	public function get_item_course_id( $item_id, $item_type = SPLMS_POST_TYPES['section'] ) {
		$item_data = $this->get_item( $item_id, $item_type );

		return ! empty( $item_data ) ? $item_data->course_id : 0;
	}


	/**
	 * Get formatted course curriculum with sections, lessons, and quizzes.
	 *
	 * This method provides a unified way to fetch course curriculum data with various options
	 * for filtering, completion checking, access control, and statistics calculation.
	 *
	 * @param int   $course_id Course ID.
	 * @param array $args {
	 *     Optional. Arguments to customize curriculum fetching.
	 *
	 *     @type int|null    $user_id           User ID for completion status. Default: current user.
	 *     @type bool        $include_lessons   Include lessons in results. Default: true.
	 *     @type bool        $include_quizzes   Include quizzes in results. Default: true.
	 *     @type bool        $include_sections  Include sections in results. Default: true.
	 *     @type bool        $check_completion  Check completion status for user. Default: false.
	 *     @type bool        $check_access      Check access permissions. Default: false.
	 *     @type bool        $include_meta      Include additional metadata. Default: false.
	 *     @type bool        $calculate_stats   Calculate statistics (counts, progress). Default: false.
	 *     @type int|null    $section_id        Fetch only specific section's children. Default: null (all).
	 *     @type array       $fields            Specific fields to return. Default: all.
	 *     @type string      $format            Output format: 'nested' or 'flat'. Default: 'nested'.
	 * }
	 *
	 * @since 1.0.0
	 *
	 * @return array Formatted curriculum data.
	 */
	public function get_course_curriculum( $course_id, $args = array() ) {
		$defaults = array(
			'user_id'          => get_current_user_id(),
			'include_lessons'  => true,
			'include_quizzes'  => true,
			'include_sections' => true,
			'check_completion' => false,
			'check_access'     => false,
			'include_meta'     => false,
			'calculate_stats'  => false,
			'section_id'       => null,
			'fields'           => array(), // Empty = all fields.
			'format'           => 'nested', // 'nested' or 'flat' format.
		);

		$args = wp_parse_args( $args, $defaults );

		// Validate course ID.
		$course_id = absint( $course_id );
		if ( ! $course_id || ! get_post( $course_id ) ) {
			return $this->get_empty_curriculum_result( $args['format'] );
		}

		// Validate user ID.
		$user_id = isset( $args['user_id'] ) ? absint( $args['user_id'] ) : 0;

		// Initialize instances we might need.
		$relationships_query = SPLMS_Relationships_Query::get_instance();
		$lessons_instance    = null;
		$quiz_attempts_query = null;

		if ( $args['check_completion'] && $user_id ) {
			$lessons_instance    = SPLMS_Lessons::get_instance();
			$quiz_attempts_query = SPLMS_Quiz_Attempts_Query::get_instance();
		}

		// Get course items (sections).
		if ( $args['section_id'] ) {
			// Fetch specific section only.
			$section      = $this->get_item( $args['section_id'], SPLMS_POST_TYPES['section'] );
			$course_items = $section ? array( $section ) : array();
		} else {
			// Fetch all sections.
			$course_items = $this->get_items( $course_id );
		}

		// Initialize statistics.
		$stats = array(
			'total_sections' => 0,
			'total_lessons'  => 0,
			'total_quizzes'  => 0,
			'total_items'    => 0,
			'completed'      => 0,
			'percentage'     => 0,
		);

		$sections   = array();
		$flat_items = array();

		foreach ( $course_items as $course_item ) {
			// Skip if not a section.
			if ( SPLMS_POST_TYPES['section'] !== $course_item->item_type ) {
				continue;
			}

			$section_id = $course_item->item_id;

			// Skip if section post doesn't exist.
			if ( ! get_post( $section_id ) ) {
				continue;
			}

			// Increment section count.
			if ( $args['calculate_stats'] ) {
				++$stats['total_sections'];
			}

			// Build section data.
			$section = $this->build_section_data( $course_item, $args );

			// Get section children (lessons and quizzes).
			$children = $relationships_query->get_children( $section_id );

			$section_children = array();
			$section_stats    = array(
				'total_items'  => 0,
				'lesson_count' => 0,
				'quiz_count'   => 0,
				'completed'    => 0,
			);

			foreach ( $children as $child ) {
				$child_type = $child->child_type;

				// Filter by type.
				$include_this_child = false;
				if ( SPLMS_POST_TYPES['lesson'] === $child_type && $args['include_lessons'] ) {
					$include_this_child = true;
				} elseif ( SPLMS_POST_TYPES['quiz'] === $child_type && $args['include_quizzes'] ) {
					$include_this_child = true;
				}

				if ( ! $include_this_child ) {
					continue;
				}

				// Skip if post doesn't exist.
				if ( ! get_post( $child->child_id ) ) {
					continue;
				}

				// Build child data.
				$child_data = $this->build_child_data( $child, $args, $user_id, $lessons_instance, $quiz_attempts_query );

				// Calculate statistics.
				if ( $args['calculate_stats'] ) {
					++$section_stats['total_items'];

					if ( SPLMS_POST_TYPES['lesson'] === $child_type ) {
						++$section_stats['lesson_count'];
						++$stats['total_lessons'];
					} elseif ( SPLMS_POST_TYPES['quiz'] === $child_type ) {
						++$section_stats['quiz_count'];
						++$stats['total_quizzes'];
					}

					if ( isset( $child_data['completed'] ) && $child_data['completed'] ) {
						++$section_stats['completed'];
						++$stats['completed'];
					}

					++$stats['total_items'];
				}

				$section_children[] = $child_data;

				// Add to flat items array if flat format.
				if ( 'flat' === $args['format'] ) {
					$child_data['section_id'] = $section_id;
					$flat_items[]             = $child_data;
				}
			}

			// Add children to section.
			$section['children'] = $section_children;

			// Add section stats if requested.
			if ( $args['calculate_stats'] ) {
				$section['stats'] = $section_stats;
			}

			// Add section to sections array if including sections.
			if ( $args['include_sections'] || 'nested' === $args['format'] ) {
				$sections[] = $section;
			}
		}

		// Calculate completion percentage.
		if ( $args['calculate_stats'] && $stats['total_items'] > 0 ) {
			$stats['percentage'] = round( ( $stats['completed'] / $stats['total_items'] ) * 100, 2 );
		}

		// Return based on format.
		if ( 'flat' === $args['format'] ) {
			return array(
				'course_id' => $course_id,
				'items'     => $flat_items,
				'stats'     => $args['calculate_stats'] ? $stats : null,
			);
		}

		// Return nested format (default).
		return array(
			'course_id' => $course_id,
			'sections'  => $sections,
			'stats'     => $args['calculate_stats'] ? $stats : null,
		);
	}

	/**
	 * Build section data array.
	 *
	 * @param object $course_item Course item object.
	 * @param array  $args        Arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return array Section data.
	 */
	private function build_section_data( $course_item, $args ) {
		$section_id = $course_item->item_id;
		$fields     = $args['fields'];

		$section = array(
			'id'    => $section_id,
			'title' => get_the_title( $section_id ),
			'type'  => $course_item->item_type,
			'order' => $course_item->order_index,
		);

		// Add fields if specific fields requested or all fields.
		if ( empty( $fields ) || in_array( 'permalink', $fields, true ) ) {
			$section['permalink'] = get_permalink( $section_id );
		}

		if ( empty( $fields ) || in_array( 'description', $fields, true ) ) {
			$section['description'] = get_the_excerpt( $section_id );
		}

		// Add meta if requested.
		if ( $args['include_meta'] && ( empty( $fields ) || in_array( 'meta', $fields, true ) ) ) {
			$section['meta'] = array(
				'content' => get_post_field( 'post_content', $section_id ),
			);
		}

		// Add access control data for section if access checking is enabled.
		if ( $args['check_access'] ) {
			$user_id = isset( $args['user_id'] ) ? absint( $args['user_id'] ) : 0;

			// Initialize section access variables.
			$has_section_access = false;

			// Get course ID from section.
			$course_id = $course_item->course_id;

			// Check user enrollment.
			$is_enrolled = false;
			if ( $user_id && $course_id ) {
				$is_enrolled        = splms_is_user_enrolled( $course_id, $user_id );
				$has_section_access = $is_enrolled; // Default to enrollment status.
			}

			// Check if course uses section-based pricing.
			$uses_section_pricing = $course_id ? splms_course_uses_section_pricing( $course_id ) : false;

			if ( $uses_section_pricing ) {
				$section_pricing = splms_get_section_pricing_with_access( $section_id, $user_id );

				// Check if section has pricing configured.
				$has_pricing_configured = isset( $section_pricing['is_free'] ) && $section_pricing['is_free'];
				if ( ! $has_pricing_configured && isset( $section_pricing['price'] ) ) {
					$has_pricing_configured = floatval( $section_pricing['price'] ) > 0;
				}

				// User has access if enrolled in full course OR has purchased this section.
				$purchased_sections = splms_get_user_purchased_sections( $course_id, $user_id );
				$has_section_access = $is_enrolled || in_array( (int) $section_id, $purchased_sections, true ) || ( isset( $section_pricing['user_has_access'] ) && $section_pricing['user_has_access'] );

				// Section is locked if pricing is configured and user doesn't have access.
				$section_is_locked = $has_pricing_configured && ! $has_section_access;
			} else {
				// When section pricing is disabled, section access follows enrollment status.
				$section_is_locked = ! $has_section_access;
			}

			// Add access data to section.
			if ( empty( $fields ) || in_array( 'has_access', $fields, true ) ) {
				$section['has_access'] = $has_section_access;
			}

			if ( empty( $fields ) || in_array( 'is_locked', $fields, true ) ) {
				$section['is_locked'] = $section_is_locked;
			}

			if ( empty( $fields ) || in_array( 'access_meta', $fields, true ) ) {
				$section['access_meta'] = array(
					'is_enrolled'          => $is_enrolled,
					'uses_section_pricing' => $uses_section_pricing,
					'access_reason'        => $has_section_access ? ( $is_enrolled ? 'enrolled' : 'section_purchased' ) : 'locked',
				);
			}
		}

		return $section;
	}

	/**
	 * Build child item (lesson/quiz) data array.
	 *
	 * @param object      $child               Child relationship object.
	 * @param array       $args                Arguments.
	 * @param int         $user_id             User ID.
	 * @param object|null $lessons_instance   Lessons instance.
	 * @param object|null $quiz_attempts_query Quiz attempts query instance.
	 *
	 * @since 1.0.0
	 *
	 * @return array Child data.
	 */
	private function build_child_data( $child, $args, $user_id, $lessons_instance, $quiz_attempts_query ) {
		$child_id   = $child->child_id;
		$child_type = $child->child_type;
		$fields     = $args['fields'];

		$child_data = array(
			'id'        => $child_id,
			'title'     => get_the_title( $child_id ),
			'type'      => $child_type,
			'order'     => $child->order_index,
			'completed' => 0,
		);

		// Add fields if specific fields requested or all fields.
		if ( empty( $fields ) || in_array( 'permalink', $fields, true ) ) {
			$child_data['permalink'] = get_permalink( $child_id );
		}

		if ( empty( $fields ) || in_array( 'description', $fields, true ) ) {
			$child_data['description'] = get_the_excerpt( $child_id );
		}

		// Add lesson duration for lessons if requested or if no specific fields are requested.
		if ( SPLMS_POST_TYPES['lesson'] === $child_type && ( empty( $fields ) || in_array( 'duration', $fields, true ) ) ) {
			$lesson_duration = get_post_meta( $child_id, '_splms_lesson_duration', true );
			if ( $lesson_duration ) {
				$child_data['duration'] = sprintf(
					/* translators: %d: Duration in minutes */
					esc_html__( '%d min', 'skillpulse-lms' ),
					(int) $lesson_duration
				);
			} else {
				$child_data['duration'] = '';
			}
		}

		// Check completion status if requested.
		if ( $args['check_completion'] && $user_id ) {
			$is_completed = false;

			if ( SPLMS_POST_TYPES['lesson'] === $child_type && $lessons_instance ) {
				$is_completed = $lessons_instance->is_lesson_completed( $child_id, $user_id );
			} elseif ( SPLMS_POST_TYPES['quiz'] === $child_type && $quiz_attempts_query ) {
				$is_completed = $quiz_attempts_query->has_user_passed( $user_id, $child_id );
			}

			if ( empty( $fields ) || in_array( 'completed', $fields, true ) ) {
				$child_data['completed'] = $is_completed;
			}
		}

		// Check access if requested - Enhanced with comprehensive access logic.
		if ( $args['check_access'] ) {
			// Get course ID from child's parent section.
			$section_id = $child->parent_id;
			$course_id  = 0;
			if ( $section_id ) {
				$section_item = $this->get_item( $section_id, SPLMS_POST_TYPES['section'] );
				$course_id    = $section_item ? $section_item->course_id : 0;
			}

			// Initialize access variables.
			$item_has_access            = false;
			$is_guest_preview_available = false;
			$is_section_preview_item    = false;
			$is_enrolled                = false;

			// Check user enrollment if user is logged in.
			if ( $user_id && $course_id ) {
				$is_enrolled = splms_is_user_enrolled( $course_id, $user_id );
			}

			// Check if course uses section-based pricing.
			$uses_section_pricing = $course_id ? splms_course_uses_section_pricing( $course_id ) : false;

			// Get user's purchased sections.
			$purchased_sections = array();
			if ( $uses_section_pricing && $user_id && $course_id ) {
				$purchased_sections = splms_get_user_purchased_sections( $course_id, $user_id );
			}

			// Get section pricing and access data.
			$section_pricing    = null;
			$has_section_access = $is_enrolled; // Default to enrollment status, not always true.

			if ( $uses_section_pricing && $section_id ) {
				$section_pricing = splms_get_section_pricing_with_access( $section_id, $user_id );

				// Check if section has pricing configured.
				$has_pricing_configured = isset( $section_pricing['is_free'] ) && $section_pricing['is_free'];
				if ( ! $has_pricing_configured && isset( $section_pricing['price'] ) ) {
					$has_pricing_configured = floatval( $section_pricing['price'] ) > 0;
				}

				// User has access if enrolled in full course OR has purchased this section.
				$has_section_access = $is_enrolled || in_array( (int) $section_id, $purchased_sections, true ) || ( isset( $section_pricing['user_has_access'] ) && $section_pricing['user_has_access'] );
			}

			// Set initial access based on section access.
			$item_has_access = $has_section_access;

			// Check section-based preview items if section is locked.
			if ( $uses_section_pricing && $section_pricing && ! $has_section_access ) {
				// Check if preview is enabled for this section.
				if ( isset( $section_pricing['preview_enabled'] ) && $section_pricing['preview_enabled'] ) {
					// Check if this item is in the preview items list.
					$preview_items = isset( $section_pricing['preview_items'] ) ? $section_pricing['preview_items'] : array();

					if ( SPLMS_POST_TYPES['lesson'] === $child_type ) {
						$lesson_preview_ids      = isset( $preview_items['lessons'] ) ? $preview_items['lessons'] : array();
						$is_section_preview_item = in_array( (int) $child_id, $lesson_preview_ids, true );
					} elseif ( SPLMS_POST_TYPES['quiz'] === $child_type ) {
						$quiz_preview_ids        = isset( $preview_items['quizzes'] ) ? $preview_items['quizzes'] : array();
						$is_section_preview_item = in_array( (int) $child_id, $quiz_preview_ids, true );
					}

					if ( $is_section_preview_item ) {
						$item_has_access = true;
					}
				}
			}

			// Check global guest preview if not enrolled and no section access.
			if ( ! $is_enrolled && ! $item_has_access ) {
				if ( SPLMS_POST_TYPES['lesson'] === $child_type ) {
					$is_guest_preview_available = splms_is_lesson_guest_preview_available( $child_id );
				} elseif ( SPLMS_POST_TYPES['quiz'] === $child_type ) {
					$is_guest_preview_available = splms_is_quiz_guest_preview_available( $child_id );
				}

				if ( $is_guest_preview_available ) {
					$item_has_access = true;
				}
			}

			// Check drip content for enrolled users with access.
			$is_drip_locked = false;
			if ( $item_has_access && $is_enrolled && $user_id && SPLMS_POST_TYPES['lesson'] === $child_type ) {
				if ( ! splms_is_lesson_drip_available( $child_id, $user_id ) ) {
					$item_has_access = false;
					$is_drip_locked  = true;
				}
			}

			// Add access data to child data.
			if ( empty( $fields ) || in_array( 'has_access', $fields, true ) ) {
				$child_data['has_access'] = $item_has_access;
			}

			// Add lock status for template simplification.
			if ( empty( $fields ) || in_array( 'is_locked', $fields, true ) ) {
				$child_data['is_locked'] = ! $item_has_access;
			}

			// Add drip lock flag so templates can show drip-specific messages.
			if ( empty( $fields ) || in_array( 'is_drip_locked', $fields, true ) ) {
				$child_data['is_drip_locked'] = $is_drip_locked;
			}

			// Add additional access metadata for advanced use cases.
			if ( empty( $fields ) || in_array( 'access_meta', $fields, true ) ) {
				$child_data['access_meta'] = array(
					'is_enrolled'                => $is_enrolled,
					'has_section_access'         => $has_section_access,
					'is_guest_preview_available' => $is_guest_preview_available,
					'is_section_preview_item'    => $is_section_preview_item,
					'uses_section_pricing'       => $uses_section_pricing,
					'is_drip_locked'             => $is_drip_locked,
				);
			}
		}

		// Add meta if requested.
		if ( $args['include_meta'] && ( empty( $fields ) || in_array( 'meta', $fields, true ) ) ) {
			$child_data['meta'] = array(
				'content' => get_post_field( 'post_content', $child_id ),
			);

			// Add lesson-specific meta.
			if ( SPLMS_POST_TYPES['lesson'] === $child_type ) {
				$child_data['meta']['lesson_type'] = get_post_meta( $child_id, '_splms_lesson_type', true );
				$child_data['meta']['duration']    = get_post_meta( $child_id, '_splms_lesson_duration', true );
			}

			// Add quiz-specific meta.
			if ( SPLMS_POST_TYPES['quiz'] === $child_type ) {
				$child_data['meta']['time_limit']    = get_post_meta( $child_id, '_splms_quiz_time_limit', true );
				$child_data['meta']['passing_score'] = get_post_meta( $child_id, '_splms_quiz_passing_score', true );
			}
		}

		return $child_data;
	}

	/**
	 * Get empty curriculum result structure.
	 *
	 * @param string $format Format type ('nested' or 'flat').
	 *
	 * @since 1.0.0
	 *
	 * @return array Empty curriculum structure.
	 */
	private function get_empty_curriculum_result( $format = 'nested' ) {
		if ( 'flat' === $format ) {
			return array(
				'course_id' => 0,
				'items'     => array(),
				'stats'     => null,
			);
		}

		return array(
			'course_id' => 0,
			'sections'  => array(),
			'stats'     => null,
		);
	}
}
