<?php
/**
 * Profile module class.
 *
 * Handles user profile functionality including statistics, analytics, and profile management.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Profile module class.
 *
 * Handles user profile functionality including statistics, analytics, and profile management.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Profile {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Profile|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @return SkillPulse_LMS_Profile
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
	 *
	 * @return void
	 */
	private function setup_actions() {
		// AJAX handlers for profile functionality.
		add_action( 'wp_ajax_splms_upload_avatar', array( $this, 'handle_avatar_upload' ) );
		add_action( 'wp_ajax_splms_remove_avatar', array( $this, 'handle_avatar_remove' ) );
		add_action( 'wp_ajax_splms_enroll_course', array( $this, 'handle_course_enrollment' ) );
		add_action( 'wp_ajax_splms_unenroll_course', array( $this, 'handle_course_unenrollment' ) );
		add_action( 'wp_ajax_splms_toggle_wishlist', array( $this, 'handle_wishlist_toggle' ) );
		add_action( 'wp_ajax_splms_toggle_bookmark', array( $this, 'handle_bookmark_toggle' ) );
		add_action( 'wp_ajax_splms_get_course_purchase_url', array( $this, 'handle_get_course_purchase_url' ) );

		// WordPress admin user profile hooks.
		if ( is_admin() ) {
			add_action( 'show_user_profile', array( $this, 'add_billing_fields_to_user_profile' ) );
			add_action( 'edit_user_profile', array( $this, 'add_billing_fields_to_user_profile' ) );
			add_action( 'personal_options_update', array( $this, 'save_billing_fields_from_user_profile' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_billing_fields_from_user_profile' ) );
		}
	}

	/**
	 * Add billing fields to WordPress admin user profile.
	 *
	 * @param WP_User $user User object.
	 *
	 * @return void
	 */
	public function add_billing_fields_to_user_profile( $user ) {
		$billing_address = get_user_meta( $user->ID, 'billing_address', true );
		$billing_address = is_array( $billing_address ) ? $billing_address : array();
		$billing_phone   = get_user_meta( $user->ID, 'billing_phone', true );
		$billing_company = get_user_meta( $user->ID, 'billing_company', true );
		?>
		<h2><?php esc_html_e( 'Billing Information', 'skillpulse-lms' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Billing information is used for orders and invoices.', 'skillpulse-lms' ); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="billing_address_1"><?php esc_html_e( 'Address Line 1', 'skillpulse-lms' ); ?></label></th>
				<td>
					<input type="text" name="billing_address_1" id="billing_address_1" value="<?php echo esc_attr( $billing_address['address_1'] ?? '' ); ?>" class="regular-text"/>
				</td>
			</tr>
			<tr>
				<th><label for="billing_address_2"><?php esc_html_e( 'Address Line 2', 'skillpulse-lms' ); ?></label></th>
				<td>
					<input type="text" name="billing_address_2" id="billing_address_2" value="<?php echo esc_attr( $billing_address['address_2'] ?? '' ); ?>" class="regular-text"/>
					<p class="description"><?php esc_html_e( 'Apartment, suite, etc. (optional)', 'skillpulse-lms' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="billing_city"><?php esc_html_e( 'City', 'skillpulse-lms' ); ?></label></th>
				<td>
					<input type="text" name="billing_city" id="billing_city" value="<?php echo esc_attr( $billing_address['city'] ?? '' ); ?>" class="regular-text"/>
				</td>
			</tr>
			<tr>
				<th><label for="billing_state"><?php esc_html_e( 'State/Province', 'skillpulse-lms' ); ?></label></th>
				<td>
					<input type="text" name="billing_state" id="billing_state" value="<?php echo esc_attr( $billing_address['state'] ?? '' ); ?>" class="regular-text"/>
				</td>
			</tr>
			<tr>
				<th><label for="billing_postcode"><?php esc_html_e( 'Postal Code', 'skillpulse-lms' ); ?></label></th>
				<td>
					<input type="text" name="billing_postcode" id="billing_postcode" value="<?php echo esc_attr( $billing_address['postcode'] ?? '' ); ?>" class="regular-text"/>
				</td>
			</tr>
			<tr>
				<th><label for="billing_country"><?php esc_html_e( 'Country', 'skillpulse-lms' ); ?></label></th>
				<td>
					<input type="text" name="billing_country" id="billing_country" value="<?php echo esc_attr( $billing_address['country'] ?? '' ); ?>" class="regular-text"
							placeholder="<?php esc_attr_e( 'e.g., US, UK', 'skillpulse-lms' ); ?>"/>
				</td>
			</tr>
			<tr>
				<th><label for="billing_phone"><?php esc_html_e( 'Phone', 'skillpulse-lms' ); ?></label></th>
				<td>
					<input type="tel" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $billing_phone ); ?>" class="regular-text"/>
				</td>
			</tr>
			<tr>
				<th><label for="billing_company"><?php esc_html_e( 'Company', 'skillpulse-lms' ); ?></label></th>
				<td>
					<input type="text" name="billing_company" id="billing_company" value="<?php echo esc_attr( $billing_company ); ?>" class="regular-text"/>
					<p class="description"><?php esc_html_e( 'Company name (optional)', 'skillpulse-lms' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save billing fields from WordPress admin user profile.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 */
	public function save_billing_fields_from_user_profile( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by WordPress core.
		if ( ! isset( $_POST['billing_address_1'] ) ) {
			return;
		}

		// Save billing address as array.
		$billing_address = array();
		if ( isset( $_POST['billing_address_1'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$billing_address['address_1'] = sanitize_text_field( wp_unslash( $_POST['billing_address_1'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if ( isset( $_POST['billing_address_2'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$billing_address['address_2'] = sanitize_text_field( wp_unslash( $_POST['billing_address_2'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if ( isset( $_POST['billing_city'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$billing_address['city'] = sanitize_text_field( wp_unslash( $_POST['billing_city'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if ( isset( $_POST['billing_state'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$billing_address['state'] = sanitize_text_field( wp_unslash( $_POST['billing_state'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if ( isset( $_POST['billing_postcode'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$billing_address['postcode'] = sanitize_text_field( wp_unslash( $_POST['billing_postcode'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if ( isset( $_POST['billing_country'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$billing_address['country'] = sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		update_user_meta( $user_id, 'billing_address', $billing_address );

		// Save billing phone.
		if ( isset( $_POST['billing_phone'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_user_meta(
				$user_id,
				'billing_phone',
				sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			);
		}

		// Save billing company.
		if ( isset( $_POST['billing_company'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_user_meta(
				$user_id,
				'billing_company',
				sanitize_text_field( wp_unslash( $_POST['billing_company'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			);
		}
	}

	/**
	 * Get student count for a specific course.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	private function get_course_student_count( $course_id ) {
		global $wpdb;

		$enrollments_table = $wpdb->prefix . 'splms_enrollments';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table_name is safe, generated from $wpdb->prefix.
		$query = "
			SELECT COUNT(*) 
			FROM {$enrollments_table} 
			WHERE course_id = %d 
			AND status = 'active'
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- SQL is prepared with $wpdb->prepare().
		return intval( $wpdb->get_var( $wpdb->prepare( $query, $course_id ) ) );
	}

	/**
	 * Get profile picture URL.
	 *
	 * @param int $user_id User ID.
	 * @param int $size    Size of the avatar.
	 *
	 * @return string
	 */
	public function get_profile_picture_url( $user_id, $size = 150 ) {
		$custom_avatar_id = get_user_meta( $user_id, '_splms_profile_picture', true );

		if ( $custom_avatar_id ) {
			return wp_get_attachment_url( $custom_avatar_id );
		}

		return get_avatar_url( $user_id, array( 'size' => $size ) );
	}

	/**
	 * Check if user has profile picture.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return bool
	 */
	public function has_profile_picture( $user_id ) {
		$custom_avatar_id = get_user_meta( $user_id, '_splms_profile_picture', true );

		return $custom_avatar_id ? true : false;
	}

	/**
	 * Handle course enrollment AJAX request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_course_enrollment() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'You must be logged in to enroll in courses.', 'skillpulse-lms' ) );
		}

		$user_id = get_current_user_id();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$course_id = ! empty( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;

		if ( ! $course_id ) {
			wp_send_json_error( __( 'Invalid course ID.', 'skillpulse-lms' ) );
		}

		// Check if course exists and is published.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type || 'publish' !== $course->post_status ) {
			wp_send_json_error( __( 'Course not found or not available.', 'skillpulse-lms' ) );
		}

		// Check enrollment dates.
		$enrollment_dates = splms_get_course_enrollment_dates( $course_id );
		if ( ! $enrollment_dates['is_open'] ) {
			if ( ! empty( $enrollment_dates['start_date'] ) && ! empty( $enrollment_dates['end_date'] ) ) {
				$start_date = date_i18n( get_option( 'date_format' ), strtotime( $enrollment_dates['start_date'] ) );
				$end_date   = date_i18n( get_option( 'date_format' ), strtotime( $enrollment_dates['end_date'] ) );
				// translators: %1$s: Start date, %2$s: End date.
				wp_send_json_error( sprintf( __( 'Enrollment is closed for this course. Enrollment period: %1$s to %2$s', 'skillpulse-lms' ), $start_date, $end_date ) );
			} else {
				wp_send_json_error( __( 'Enrollment is currently closed for this course.', 'skillpulse-lms' ) );
			}
		}

		// Check course capacity.
		$capacity_info = splms_get_course_max_enrollment_info( $course_id );
		if ( $capacity_info['is_full'] ) {
			wp_send_json_error(
				sprintf(
					/* translators: %1$d: Enrolled count, %2$d: Max enrollment. */
					__( 'This course is full (%1$d/%2$d students). No more enrollments are allowed.', 'skillpulse-lms' ),
					$capacity_info['enrolled_count'],
					$capacity_info['max_enrollment']
				)
			);
		}

		// Get course access info to check if it's a paid course.
		$course_access_info = splms_get_course_access_info( $course_id );
		$is_paid_course     = 'public_paid' === $course_access_info['course_access_type'] && splms_is_paid_courses_enabled();

		// For paid courses, check if user has purchased.
		if ( $is_paid_course ) {
			$has_purchased = splms_has_user_purchased_course( $course_id, $user_id );
			if ( ! $has_purchased ) {
				wp_send_json_error( __( 'You must purchase this course before enrolling.', 'skillpulse-lms' ) );
			}
		}

		// Check membership requirements.
		if ( ! splms_user_has_required_membership( $user_id, $course_id ) ) {
			wp_send_json_error( __( 'This course requires a membership. Please purchase a membership to enroll.', 'skillpulse-lms' ) );
		}

		// Check if already enrolled (active status).
		$enrollments_query   = SkillPulse_LMS_Enrollments_Query::get_instance();
		$existing_enrollment = $enrollments_query->get_enrollment( $user_id, $course_id );

		// If user is already actively enrolled, return success.
		if ( $existing_enrollment && 'active' === $existing_enrollment->status ) {
			wp_send_json_success(
				array(
					'message' => __( 'You are already enrolled in this course.', 'skillpulse-lms' ),
				)
			);
		}

		// Enroll user in database (will update if inactive, insert if new).
		$enrollment_class  = SkillPulse_LMS_Enrollment::get_instance();
		$enrollment_result = $enrollment_class->enroll_user_in_course( $user_id, $course_id );

		if ( false === $enrollment_result ) {
			wp_send_json_error( __( 'Failed to enroll in course. Please try again.', 'skillpulse-lms' ) );
		}

		// Log activity.
		SkillPulse_LMS_User_Activity_Query::get_instance()->log_activity( $user_id, 'course_enrolled', $course_id );

		// Send success response.
		wp_send_json_success(
			array(
				'message' => __( 'Successfully enrolled in course!', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Handle course unenrollment AJAX request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_course_unenrollment() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'You must be logged in to unenroll from courses.', 'skillpulse-lms' ) );
		}

		// Check if student unenrollment is allowed.
		if ( ! splms_is_student_unenrollment_allowed() ) {
			wp_send_json_error( __( 'Student unenrollment is disabled. Please contact an administrator.', 'skillpulse-lms' ) );
		}

		$user_id = get_current_user_id();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$course_id = ! empty( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;

		if ( ! $course_id ) {
			wp_send_json_error( __( 'Invalid course ID.', 'skillpulse-lms' ) );
		}

		// Check if course exists.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			wp_send_json_error( __( 'Course not found.', 'skillpulse-lms' ) );
		}

		// Check if user is enrolled.
		$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
		$enrollment        = $enrollments_query->get_enrollment( $user_id, $course_id );

		if ( ! $enrollment ) {
			wp_send_json_error( __( 'You are not enrolled in this course.', 'skillpulse-lms' ) );
		}

		// Update enrollment status to 'cancelled'.
		$result = $enrollments_query->update_enrollment( $enrollment->id, array( 'status' => 'cancelled' ) );

		if ( ! $result ) {
			wp_send_json_error( __( 'Failed to unenroll from course. Please try again.', 'skillpulse-lms' ) );
		}

		// Log activity.
		do_action( 'splms_user_unenrolled', $user_id, $course_id );

		wp_send_json_success(
			array(
				'message' => __( 'Successfully unenrolled from course.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Handle wishlist toggle AJAX request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_wishlist_toggle() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'You must be logged in to manage your wishlist.', 'skillpulse-lms' ) );
		}

		$user_id = get_current_user_id();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$course_id = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;

		if ( ! $course_id ) {
			wp_send_json_error( __( 'Invalid course ID.', 'skillpulse-lms' ) );
		}

		// Check if feature enabled.
		$wishlist_enabled = function_exists( 'splms_get_setting' ) ? splms_get_setting( 'enable_course_wishlist', true ) : true;
		if ( ! $wishlist_enabled ) {
			wp_send_json_error( __( 'Wishlist is disabled by site admin.', 'skillpulse-lms' ) );
		}

		$wishlist = get_user_meta( $user_id, '_splms_course_wishlist', true );
		if ( ! is_array( $wishlist ) ) {
			$wishlist = array();
		}
		// Normalize to integers to ensure consistent comparisons.
		$wishlist = array_map( 'intval', $wishlist );

		$is_in_wishlist = in_array( (int) $course_id, $wishlist, true );

		if ( $is_in_wishlist ) {
			// Remove from wishlist.
			$wishlist = array_values( array_diff( $wishlist, array( (int) $course_id ) ) );
			$message  = __( 'Course removed from wishlist.', 'skillpulse-lms' );
			$action   = 'removed';
		} else {
			// Add to wishlist.
			$wishlist[] = (int) $course_id;
			$message    = __( 'Course added to wishlist.', 'skillpulse-lms' );
			$action     = 'added';
		}

		update_user_meta( $user_id, '_splms_course_wishlist', array_values( $wishlist ) );

		wp_send_json_success(
			array(
				'message'        => $message,
				'action'         => $action,
				'item_id'        => $course_id,
				'wishlist_count' => count( $wishlist ),
			)
		);
	}

	/**
	 * Handle bookmark toggle AJAX request.
	 *
	 * Bookmarks are for lessons and quizzes, and only if user is enrolled in the parent course.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_bookmark_toggle() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'You must be logged in to manage your bookmarks.', 'skillpulse-lms' ) );
		}

		$user_id = get_current_user_id();
		// Bookmarks support both lessons and quizzes.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$item_id = isset( $_POST['lesson_id'] ) ? intval( $_POST['lesson_id'] ) : ( isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0 );

		if ( ! $item_id ) {
			wp_send_json_error( __( 'Invalid item ID.', 'skillpulse-lms' ) );
		}

		// Verify that the item is a lesson or quiz.
		$item = get_post( $item_id );
		if ( ! $item || ! in_array( $item->post_type, array( SPLMS_POST_TYPES['lesson'], SPLMS_POST_TYPES['quiz'] ), true ) ) {
			wp_send_json_error( __( 'Invalid item. Bookmarks are only available for lessons and quizzes.', 'skillpulse-lms' ) );
		}

		// Check if user is enrolled in the parent course.
		$relationships_query  = SkillPulse_LMS_Relationships_Query::get_instance();
		$parent_relationships = $relationships_query->get_parents( $item_id );
		$course_id            = null;
		if ( ! empty( $parent_relationships ) ) {
			$section_id = $parent_relationships[0]->parent_id;
			$course_id  = SkillPulse_LMS_Course_Items_Query::get_instance()->get_item_course_id( $section_id );
		}

		if ( ! $course_id ) {
			$item_type = SPLMS_POST_TYPES['quiz'] === $item->post_type ? __( 'Quiz', 'skillpulse-lms' ) : __( 'Lesson', 'skillpulse-lms' );
			// translators: %s: Item type.
			wp_send_json_error( sprintf( __( '%s must belong to a course.', 'skillpulse-lms' ), $item_type ) );
		}

		// Check if user is enrolled in the course.
		if ( ! splms_is_user_enrolled( $course_id, $user_id ) ) {
			$item_type = SPLMS_POST_TYPES['quiz'] === $item->post_type ? __( 'quizzes', 'skillpulse-lms' ) : __( 'lessons', 'skillpulse-lms' );
			// translators: %s: Item type.
			wp_send_json_error( sprintf( __( 'You must be enrolled in the course to bookmark %s.', 'skillpulse-lms' ), $item_type ) );
		}

		// Check if feature enabled.
		$enabled = function_exists( 'splms_get_setting' ) ? splms_get_setting( 'enable_bookmarks', true ) : true;
		if ( ! $enabled ) {
			wp_send_json_error( __( 'Bookmarks are disabled by site admin.', 'skillpulse-lms' ) );
		}

		$bookmarks = get_user_meta( $user_id, '_splms_bookmarks', true );
		if ( ! is_array( $bookmarks ) ) {
			$bookmarks = array();
		}
		$bookmarks = array_map( 'intval', $bookmarks );

		$is_bookmarked = in_array( (int) $item_id, $bookmarks, true );

		if ( $is_bookmarked ) {
			$bookmarks = array_values( array_diff( $bookmarks, array( (int) $item_id ) ) );
			$message   = __( 'Removed from bookmarks.', 'skillpulse-lms' );
			$action    = 'removed';
		} else {
			$bookmarks[] = (int) $item_id;
			$bookmarks   = array_values( array_unique( $bookmarks ) );
			$message     = __( 'Added to bookmarks.', 'skillpulse-lms' );
			$action      = 'added';
		}

		update_user_meta( $user_id, '_splms_bookmarks', $bookmarks );

		wp_send_json_success(
			array(
				'message'         => $message,
				'action'          => $action,
				'item_id'         => $item_id,
				'bookmarks_count' => count( $bookmarks ),
			)
		);
	}

	/**
	 * Handle avatar upload AJAX request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_avatar_upload() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'You must be logged in to upload an avatar.', 'skillpulse-lms' ) );
		}

		$user_id = get_current_user_id();

		// Check if file was uploaded.
		if ( ! isset( $_FILES['avatar'] ) || ! isset( $_FILES['avatar']['error'] ) || UPLOAD_ERR_OK !== $_FILES['avatar']['error'] ) {
			wp_send_json_error( __( 'No file uploaded or upload error occurred.', 'skillpulse-lms' ) );
		}

		// Validate file type.
		if ( ! isset( $_FILES['avatar']['name'] ) || ! isset( $_FILES['avatar']['tmp_name'] ) ) {
			wp_send_json_error( __( 'Invalid file upload.', 'skillpulse-lms' ) );
		}

		$file_name     = sanitize_file_name( wp_unslash( $_FILES['avatar']['name'] ) );
		$file_type     = wp_check_filetype( $file_name );
		$allowed_types = array( 'jpg', 'jpeg', 'png', 'gif' );

		// Validate file extension.
		if ( ! in_array( strtolower( $file_type['ext'] ), $allowed_types, true ) ) {
			wp_send_json_error( __( 'Invalid file type. Please upload a JPG, PNG, or GIF image.', 'skillpulse-lms' ) );
		}

		// Check file size (max 5MB).
		if ( ! isset( $_FILES['avatar']['size'] ) || $_FILES['avatar']['size'] > 5 * 1024 * 1024 ) {
			wp_send_json_error( __( 'File size too large. Please upload an image smaller than 5MB.', 'skillpulse-lms' ) );
		}

		// Validate MIME type.
		$allowed_mimes = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
		);

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wp_check_filetype_and_ext handles validation.
		$tmp_name = isset( $_FILES['avatar']['tmp_name'] ) ? sanitize_text_field( $_FILES['avatar']['tmp_name'] ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wp_check_filetype_and_ext handles validation.
		$file_name = isset( $_FILES['avatar']['name'] ) ? sanitize_file_name( $_FILES['avatar']['name'] ) : '';

		$wp_filetype = wp_check_filetype_and_ext( $tmp_name, $file_name, $allowed_mimes );
		if ( ! $wp_filetype['ext'] || ! $wp_filetype['type'] ) {
			wp_send_json_error( __( 'Invalid file type. Please upload a valid image file.', 'skillpulse-lms' ) );
		}

		// Verify it's actually an image by checking the file content.
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Validated file type above, checking image validity.
		$image_info = getimagesize( $tmp_name );
		if ( false === $image_info ) {
			wp_send_json_error( __( 'Invalid image file. The file does not appear to be a valid image.', 'skillpulse-lms' ) );
		}

		// Verify MIME type matches the file content.
		$allowed_mime_types = array( 'image/jpeg', 'image/gif', 'image/png' );
		if ( ! isset( $image_info['mime'] ) || ! in_array( $image_info['mime'], $allowed_mime_types, true ) ) {
			wp_send_json_error( __( 'Invalid image MIME type. Please upload a valid image file.', 'skillpulse-lms' ) );
		}

		// Handle the upload using WordPress functions with MIME type validation.
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		// Override upload handler to enforce MIME type validation.
		add_filter( 'upload_mimes', array( $this, 'filter_avatar_upload_mimes' ) );
		$attachment_id = media_handle_upload( 'avatar', 0 );
		remove_filter( 'upload_mimes', array( $this, 'filter_avatar_upload_mimes' ) );

		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( __( 'Failed to upload image. Please try again.', 'skillpulse-lms' ) );
		}

		// Get the attachment URL.
		$attachment_url = wp_get_attachment_url( $attachment_id );

		// Update user's avatar.
		$result = update_user_meta( $user_id, '_splms_profile_picture', $attachment_id );

		if ( ! $result ) {
			wp_send_json_error( __( 'Failed to update avatar. Please try again.', 'skillpulse-lms' ) );
		}

		wp_send_json_success(
			array(
				'message'       => __( 'Avatar updated successfully!', 'skillpulse-lms' ),
				'avatar_url'    => $attachment_url,
				'attachment_id' => $attachment_id,
			)
		);
	}

	/**
	 * Handle avatar remove AJAX request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_avatar_remove() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'You must be logged in to remove your avatar.', 'skillpulse-lms' ) );
		}

		$user_id = get_current_user_id();

		// Get current avatar attachment ID.
		$current_avatar_id = get_user_meta( $user_id, '_splms_profile_picture', true );

		if ( ! $current_avatar_id ) {
			wp_send_json_error( __( 'No custom avatar found to remove.', 'skillpulse-lms' ) );
		}

		// Delete the attachment from media library.
		$delete_result = wp_delete_attachment( $current_avatar_id, true );

		if ( ! $delete_result ) {
			wp_send_json_error( __( 'Failed to remove avatar. Please try again.', 'skillpulse-lms' ) );
		}

		// Remove the user meta.
		$meta_result = delete_user_meta( $user_id, '_splms_profile_picture' );

		if ( ! $meta_result ) {
			wp_send_json_error( __( 'Failed to update user profile. Please try again.', 'skillpulse-lms' ) );
		}

		wp_send_json_success(
			array(
				'message'    => __( 'Avatar removed successfully!', 'skillpulse-lms' ),
				'avatar_url' => get_avatar_url( $user_id ), // Return default avatar URL.
			)
		);
	}

	/**
	 * Handle get course purchase URL AJAX request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_get_course_purchase_url() {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is done below.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'splms_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$course_id = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;

		if ( ! $course_id ) {
			wp_send_json_error( __( 'Invalid course ID.', 'skillpulse-lms' ) );
		}

		// Check if course exists and is published.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type || 'publish' !== $course->post_status ) {
			wp_send_json_error( __( 'Course not found or not available.', 'skillpulse-lms' ) );
		}

		// Get course purchase URL (pass user ID to check membership).
		$user_id      = get_current_user_id();
		$purchase_url = splms_get_course_purchase_url( $course_id, $user_id );

		wp_send_json_success(
			array(
				'purchase_url' => $purchase_url,
			)
		);
	}

	/**
	 * Filter allowed MIME types for avatar uploads.
	 *
	 * @param array $mimes Allowed MIME types.
	 *
	 * @since 1.0.0
	 *
	 * @return array Filtered MIME types.
	 */
	public function filter_avatar_upload_mimes( $mimes ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter required by WordPress filter signature.
		$allowed_mimes = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
		);

		/**
		 * Filters the allowed MIME types for avatar uploads.
		 *
		 * @param array $allowed_mimes Allowed MIME types.
		 *
		 * @since 1.0.0
		 *
		 * @return array Filtered MIME types.
		 */
		$allowed_mimes = apply_filters( 'splms_avatar_upload_mimes', $allowed_mimes );

		return $allowed_mimes;
	}
}
