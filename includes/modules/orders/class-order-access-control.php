<?php
/**
 * Order Access Control System
 *
 * @package SkillPulse_LMS
 * @subpackage Modules
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Access Control Class
 *
 * Manages course access based on order status.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Order_Access_Control {

	/**
	 * Grant course access to user based on completed order.
	 *
	 * @since 1.0.0
	 * @param string $order_id Order ID.
	 * @return bool True on success, false on failure.
	 */
	public static function grant_course_access( $order_id ) {
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order || 'completed' !== $order->status ) {
			return false;
		}

		// Check if this order has items (section-based purchase).
		$order_items = self::get_order_items( $order_id );

		if ( ! empty( $order_items ) ) {
			// This is a section-based purchase - grant section access.
			return self::grant_section_access_from_order( $order_id );
		}

		// This is a full course purchase - enroll user in course.
		$enrollment_result = SkillPulse_LMS_Enrollment::get_instance()->enroll_user_in_course( $order->user_id, $order->course_id );

		// enrollment_result can be enrollment ID (int) or false, so check for truthy value.
		if ( $enrollment_result ) {
			// Update order meta to track access granted.
			$orders_query->update_order_meta( $order_id, 'access_granted_at', current_time( 'mysql' ) );
			$orders_query->update_order_meta( $order_id, 'enrollment_id', $enrollment_result );

			// Log the access grant.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for access grants.
				error_log(
					sprintf(
						'Course access granted: User %d enrolled in course %d via order %s (enrollment ID: %s)',
						$order->user_id,
						$order->course_id,
						$order_id,
						$enrollment_result
					)
				);
			}

			return true;
		}

		// Log enrollment failure for debugging.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for enrollment failures.
			error_log(
				sprintf(
					'Course access grant failed: User %d could not be enrolled in course %d via order %s',
					$order->user_id,
					$order->course_id,
					$order_id
				)
			);
		}

		return false;
	}

	/**
	 * Grant section access from order items.
	 *
	 * @since 1.0.0
	 * @param string $order_id Order ID.
	 * @return bool True on success, false on failure.
	 */
	public static function grant_section_access_from_order( $order_id ) {
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return false;
		}

		$order_items      = self::get_order_items( $order_id );
		$granted_sections = array();

		// Use Section Access Query class for database operations.
		$section_access_query = SkillPulse_LMS_Section_Access_Query::get_instance();

		foreach ( $order_items as $item ) {
			if ( 'section' === $item->item_type ) {
				$access_id = $section_access_query->grant_access(
					$order->user_id,
					$item->item_id,
					$order->course_id,
					'purchased',
					null, // No expiration.
					$order_id
				);

				if ( $access_id ) {
					$granted_sections[] = $item->item_id;
				}
			}
		}

		if ( ! empty( $granted_sections ) ) {
			// Update order meta to track access granted.
			$orders_query->update_order_meta( $order_id, 'access_granted_at', current_time( 'mysql' ) );
			$orders_query->update_order_meta( $order_id, 'section_access_granted', wp_json_encode( $granted_sections ) );

			// Log the access grant.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for access grants.
				error_log(
					sprintf(
						'Section access granted: User %d granted access to %d sections in course %d via order %s',
						$order->user_id,
						count( $granted_sections ),
						$order->course_id,
						$order_id
					)
				);
			}

			return true;
		}

		return false;
	}

	/**
	 * Get order items from database.
	 *
	 * @since 1.0.0
	 * @param string $order_id Order ID.
	 * @return array Order items.
	 */
	public static function get_order_items( $order_id ) {
		global $wpdb;

		$table_name = esc_sql( $wpdb->prefix . 'splms_order_items' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query.
		$items = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safely constructed with $wpdb->prefix.
				"SELECT * FROM {$table_name} WHERE order_id = %s ORDER BY id ASC",
				$order_id
			)
		);

		return $items ? $items : array();
	}

	/**
	 * Revoke course access based on order status change.
	 *
	 * @since 1.0.0
	 * @param string $order_id Order ID.
	 * @return bool True on success, false on failure.
	 */
	public static function revoke_course_access( $order_id ) {
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return false;
		}

		// Check if this order has items (section-based purchase).
		$order_items = self::get_order_items( $order_id );

		if ( ! empty( $order_items ) ) {
			// This is a section-based purchase - revoke section access.
			return self::revoke_section_access_from_order( $order_id );
		}

		// This is a full course purchase - unenroll user from course.
		// Get enrollment ID from order meta.
		$enrollment_id = $orders_query->get_order_meta( $order_id, 'enrollment_id' );

		// Unenroll user from course using Enrollments Query.
		if ( class_exists( 'SkillPulse_LMS_Enrollments_Query' ) ) {
			$enrollments_query   = SkillPulse_LMS_Enrollments_Query::get_instance();
			$unenrollment_result = $enrollments_query->unenroll_user( $order->user_id, $order->course_id );

			if ( $unenrollment_result ) {
				// Update order meta to track access revoked.
				$orders_query->update_order_meta( $order_id, 'access_revoked_at', current_time( 'mysql' ) );
				$orders_query->update_order_meta( $order_id, 'access_revoked_reason', $order->status );

				// Log the access revocation.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for access revocations.
					error_log(
						sprintf(
							'Course access revoked: User %d unenrolled from course %d via order %s (reason: %s)',
							$order->user_id,
							$order->course_id,
							$order_id,
							$order->status
						)
					);
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Revoke section access from order items.
	 *
	 * @since 1.0.0
	 * @param string $order_id Order ID.
	 * @return bool True on success, false on failure.
	 */
	public static function revoke_section_access_from_order( $order_id ) {
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return false;
		}

		// Use Section Access Query class for database operations.
		$section_access_query = SkillPulse_LMS_Section_Access_Query::get_instance();
		$revoke_result        = $section_access_query->revoke_access_by_order( $order_id );

		if ( $revoke_result ) {
			// Update order meta to track access revoked.
			$current_timestamp = current_time( 'mysql' );
			$orders_query->update_order_meta( $order_id, 'access_revoked_at', $current_timestamp );
			$orders_query->update_order_meta( $order_id, 'access_revoked_reason', $order->status );

			// Log the access revocation.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging for access revocations.
				error_log(
					sprintf(
						'Section access revoked: User %d section access revoked for course %d via order %s (reason: %s)',
						$order->user_id,
						$order->course_id,
						$order_id,
						$order->status
					)
				);
			}

			return true;
		}

		return false;
	}

	/**
	 * Handle order status change and manage course access.
	 *
	 * @since 1.0.0
	 * @param string $order_id Order ID.
	 * @param string $old_status Previous order status.
	 * @param string $new_status New order status.
	 * @return bool True on success, false on failure.
	 */
	public static function handle_order_status_change( $order_id, $old_status, $new_status ) {
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order        = $orders_query->get_order_by_id( $order_id );

		if ( ! $order ) {
			return false;
		}

		// Grant access when order is completed.
		if ( 'completed' === $new_status && 'completed' !== $old_status ) {
			return self::grant_course_access( $order_id );
		}

		// Revoke access when order is no longer completed.
		if ( 'completed' === $old_status && 'completed' !== $new_status ) {
			return self::revoke_course_access( $order_id );
		}

		return true;
	}

	/**
	 * Get course access status for user.
	 *
	 * @since 1.0.0
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 * @return array Access status information.
	 */
	public static function get_course_access_status( $user_id, $course_id ) {
		// If paid courses are disabled globally, treat all courses as free.
		if ( ! splms_is_paid_courses_enabled() ) {
			return array(
				'has_access'        => true,
				'access_type'       => 'free',
				'order_id'          => null,
				'access_granted_at' => null,
			);
		}

		// Check if course is free.
		$course_access_info = splms_get_course_access_info( $course_id );
		if ( 'public_free' === $course_access_info['course_access_type'] ) {
			return array(
				'has_access'        => true,
				'access_type'       => 'free',
				'order_id'          => null,
				'access_granted_at' => null,
			);
		}

		// Check for completed orders.
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$orders       = $orders_query->get_orders(
			array(
				'user_id'   => $user_id,
				'course_id' => $course_id,
				'status'    => 'completed',
			)
		);

		if ( ! empty( $orders ) ) {
			$order = $orders[0]; // Get the most recent completed order.
			return array(
				'has_access'        => true,
				'access_type'       => 'paid',
				'order_id'          => $order->id,
				'access_granted_at' => $orders_query->get_order_meta( $order->id, 'access_granted_at' ),
			);
		}

		// Check for pending orders.
		$pending_orders = $orders_query->get_orders(
			array(
				'user_id'   => $user_id,
				'course_id' => $course_id,
				'status'    => array( 'pending', 'processing' ),
			)
		);

		return array(
			'has_access'        => false,
			'access_type'       => 'paid',
			'order_id'          => ! empty( $pending_orders ) ? $pending_orders[0]->id : null,
			'access_granted_at' => null,
			'has_pending_order' => ! empty( $pending_orders ),
		);
	}

	/**
	 * Check if user has access to a specific section.
	 *
	 * Hierarchical check:
	 * 1. Full enrollment → Access to all sections
	 * 2. Section purchase → Access to specific section
	 * 3. Preview → No full access, but can preview items
	 *
	 * @since 1.0.0
	 * @param int $user_id User ID.
	 * @param int $section_id Section ID.
	 * @return array Section access status.
	 */
	public static function get_section_access_status( $user_id, $section_id ) {
		// Get course ID from section.
		$course_id = wp_get_post_parent_id( $section_id );

		if ( ! $course_id ) {
			return array(
				'has_access'  => false,
				'access_type' => 'none',
				'reason'      => 'invalid_section',
			);
		}

		// Level 1: Check full course enrollment.
		$enrollment_manager = SkillPulse_LMS_Enrollment::get_instance();
		$is_enrolled        = $enrollment_manager->is_user_enrolled( $user_id, $course_id );

		if ( $is_enrolled ) {
			return array(
				'has_access'  => true,
				'access_type' => 'enrollment',
				'reason'      => 'full_course_enrollment',
			);
		}

		// Level 2: Check section-specific purchase.
		$section_access = self::get_user_section_access( $user_id, $section_id );

		if ( $section_access && 'purchased' === $section_access->access_type ) {
			return array(
				'has_access'  => true,
				'access_type' => 'section_purchase',
				'reason'      => 'section_purchased',
				'granted_at'  => $section_access->granted_at,
				'order_id'    => $section_access->order_id,
			);
		}

		// Level 3: Check if section is free.
		$section_pricing = get_post_meta( $section_id, '_splms_section_pricing', true );

		if ( is_array( $section_pricing ) && isset( $section_pricing['is_free'] ) && true === $section_pricing['is_free'] ) {
			return array(
				'has_access'  => true,
				'access_type' => 'free',
				'reason'      => 'free_section',
			);
		}

		// No access to section.
		return array(
			'has_access'  => false,
			'access_type' => 'none',
			'reason'      => 'no_purchase',
		);
	}

	/**
	 * Get user section access from database.
	 *
	 * @since 1.0.0
	 * @param int $user_id User ID.
	 * @param int $section_id Section ID.
	 * @return object|null Section access object or null.
	 */
	public static function get_user_section_access( $user_id, $section_id ) {
		// Use Section Access Query class for database operations.
		$section_access_query = SkillPulse_LMS_Section_Access_Query::get_instance();
		return $section_access_query->get_user_section_access( $user_id, $section_id );
	}

	/**
	 * Check if user can preview a specific item (lesson, quiz, assessment).
	 *
	 * @since 1.0.0
	 * @param int    $user_id User ID.
	 * @param int    $item_id Item ID (lesson, quiz, or assessment).
	 * @param string $item_type Item type (lesson, quiz, assessment).
	 * @return array Preview access status.
	 */
	public static function get_item_preview_status( $user_id, $item_id, $item_type ) {
		// Get section ID from item.
		$section_id = wp_get_post_parent_id( $item_id );

		if ( ! $section_id ) {
			return array(
				'can_preview' => false,
				'reason'      => 'invalid_item',
			);
		}

		// First, check if user has full access to section.
		$section_access = self::get_section_access_status( $user_id, $section_id );

		if ( $section_access['has_access'] ) {
			return array(
				'can_preview' => true,
				'has_access'  => true,
				'reason'      => 'full_access',
			);
		}

		// User doesn't have full access - check preview settings.
		$section_pricing = get_post_meta( $section_id, '_splms_section_pricing', true );

		if ( ! is_array( $section_pricing ) || ! isset( $section_pricing['preview_enabled'] ) || true !== $section_pricing['preview_enabled'] ) {
			return array(
				'can_preview' => false,
				'has_access'  => false,
				'reason'      => 'preview_disabled',
			);
		}

		// Check if this specific item is in the preview list.
		$preview_items = isset( $section_pricing['preview_items'] ) ? $section_pricing['preview_items'] : array();
		$item_type_key = $item_type . 's'; // Convert 'lesson' to 'lessons', etc.
		$preview_list  = isset( $preview_items[ $item_type_key ] ) ? $preview_items[ $item_type_key ] : array();
		$is_in_preview = in_array( $item_id, $preview_list, true );

		if ( $is_in_preview ) {
			return array(
				'can_preview' => true,
				'has_access'  => false,
				'reason'      => 'preview_allowed',
			);
		}

		return array(
			'can_preview' => false,
			'has_access'  => false,
			'reason'      => 'not_in_preview_list',
		);
	}

	/**
	 * Get all sections user has access to in a course.
	 *
	 * @since 1.0.0
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 * @return array List of accessible section IDs.
	 */
	public static function get_user_accessible_sections( $user_id, $course_id ) {
		global $wpdb;

		// Check if user has full course enrollment.
		$enrollment_manager = SkillPulse_LMS_Enrollment::get_instance();
		$is_enrolled        = $enrollment_manager->is_user_enrolled( $user_id, $course_id );

		if ( $is_enrolled ) {
			// User has full access - return all sections.
			$sections = get_posts(
				array(
					'post_type'      => 'sp-section',
					'post_parent'    => $course_id,
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				)
			);

			return $sections;
		}

		// Get purchased sections using Section Access Query class.
		$section_access_query = SkillPulse_LMS_Section_Access_Query::get_instance();
		$purchased_sections   = $section_access_query->get_user_course_sections( $user_id, $course_id );

		// Get free sections.
		$all_sections  = get_posts(
			array(
				'post_type'      => 'sp-section',
				'post_parent'    => $course_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$free_sections = array();

		if ( ! empty( $all_sections ) ) {
			// Prime meta cache for all sections in a single query to avoid N+1.
			update_meta_cache( 'post', $all_sections );

			foreach ( $all_sections as $section_id ) {
				$section_pricing = get_post_meta( $section_id, '_splms_section_pricing', true );

				if ( is_array( $section_pricing ) && isset( $section_pricing['is_free'] ) && true === $section_pricing['is_free'] ) {
					$free_sections[] = $section_id;
				}
			}
		}

		// Merge purchased and free sections (unique).
		$accessible_sections = array_unique( array_merge( $purchased_sections, $free_sections ) );

		return $accessible_sections;
	}
}
