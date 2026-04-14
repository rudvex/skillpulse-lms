<?php
/**
 * Reviews Empty State
 *
 * Displayed when there are no reviews yet.
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/course/partials/tabs/reviews/empty-state.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$permission = isset( $args['permission'] ) ? $args['permission'] : array();
$user_id    = get_current_user_id();

// Get permission status if not provided.
if ( empty( $permission ) && $user_id ) {
	$course_id  = get_the_ID();
	$permission = SPLMS_Review_Permissions::can_user_review( $user_id, $course_id );
}
?>

<div class="no-reviews">
	<div class="no-reviews-icon">
		<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M11.049 2.927C11.3483 2.00556 12.6517 2.00556 12.951 2.927L14.4697 7.60081C14.6035 8.01284 14.9875 8.29653 15.4207 8.29653H20.4329C21.4016 8.29653 21.8044 9.54169 21.0207 10.1008L17.0205 12.9894C16.6704 13.244 16.5234 13.6982 16.6572 14.1102L18.1759 18.784C18.4752 19.7054 17.4241 20.4649 16.6405 19.9058L12.6402 17.0172C12.2901 16.7626 11.7099 16.7626 11.3598 17.0172L7.35954 19.9058C6.57589 20.4649 5.52481 19.7054 5.82411 18.784L7.34276 14.1102C7.47659 13.6982 7.32961 13.244 6.97946 12.9894L2.97918 10.1008C2.19553 9.54169 2.59832 8.29653 3.56708 8.29653H8.57929C9.01252 8.29653 9.39647 8.01284 9.53029 7.60081L11.049 2.927Z"
					stroke="currentColor" stroke-width="1.5"/>
		</svg>
	</div>
	<h3><?php esc_html_e( 'No reviews yet', 'skillpulse-lms' ); ?></h3>
	<p><?php esc_html_e( 'Be the first to share your experience with this course and help other students make informed decisions.', 'skillpulse-lms' ); ?></p>

	<?php if ( $user_id && isset( $permission['can_review'] ) && $permission['can_review'] ) { ?>
		<button class="btn btn-primary write-review-btn" data-course-id="<?php echo esc_attr( get_the_ID() ); ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 4V20M20 12H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
			<?php esc_html_e( 'Write the First Review', 'skillpulse-lms' ); ?>
		</button>
	<?php } ?>
</div>
