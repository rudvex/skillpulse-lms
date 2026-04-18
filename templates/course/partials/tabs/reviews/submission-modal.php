<?php
/**
 * Review Submission Modal
 *
 * Modal for submitting course reviews.
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/course/partials/tabs/reviews/submission-modal.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



$splms_course_id  = isset( $args['splms_course_id'] ) ? $args['splms_course_id'] : get_the_ID();
$splms_permission = isset( $args['splms_permission'] ) ? $args['splms_permission'] : array();
$splms_user_id    = get_current_user_id();

// Get settings.
$splms_min_length = splms_get_setting( 'reviews_min_length', 10 );
$splms_max_length = splms_get_setting( 'reviews_max_length', 500 );
?>

<div class="splms-review-modal" id="review-submission-modal" style="display: none;">
	<div class="modal-overlay"></div>
	<div class="modal-container">
		<div class="modal-header">
			<h3>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M11.049 2.927C11.3483 2.00556 12.6517 2.00556 12.951 2.927L14.4697 7.60081C14.6035 8.01284 14.9875 8.29653 15.4207 8.29653H20.4329C21.4016 8.29653 21.8044 9.54169 21.0207 10.1008L17.0205 12.9894C16.6704 13.244 16.5234 13.6982 16.6572 14.1102L18.1759 18.784C18.4752 19.7054 17.4241 20.4649 16.6405 19.9058L12.6402 17.0172C12.2901 16.7626 11.7099 16.7626 11.3598 17.0172L7.35954 19.9058C6.57589 20.4649 5.52481 19.7054 5.82411 18.784L7.34276 14.1102C7.47659 13.6982 7.32961 13.244 6.97946 12.9894L2.97918 10.1008C2.19553 9.54169 2.59832 8.29653 3.56708 8.29653H8.57929C9.01252 8.29653 9.39647 8.01284 9.53029 7.60081L11.049 2.927Z"
							stroke="currentColor" stroke-width="1.5"/>
				</svg>
				<?php esc_html_e( 'Write a Review', 'skillpulse-lms' ); ?>
			</h3>
			<button class="modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'skillpulse-lms' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>

		<div class="modal-body">
			<p class="modal-description"><?php esc_html_e( 'Share your experience to help other students', 'skillpulse-lms' ); ?></p>

			<form class="review-form course-review-form" method="post" data-course-id="<?php echo esc_attr( $splms_course_id ); ?>">
				<div class="form-group rating-input">
					<label class="form-label">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M11.049 2.927C11.3483 2.00556 12.6517 2.00556 12.951 2.927L14.4697 7.60081C14.6035 8.01284 14.9875 8.29653 15.4207 8.29653H20.4329C21.4016 8.29653 21.8044 9.54169 21.0207 10.1008L17.0205 12.9894C16.6704 13.244 16.5234 13.6982 16.6572 14.1102L18.1759 18.784C18.4752 19.7054 17.4241 20.4649 16.6405 19.9058L12.6402 17.0172C12.2901 16.7626 11.7099 16.7626 11.3598 17.0172L7.35954 19.9058C6.57589 20.4649 5.52481 19.7054 5.82411 18.784L7.34276 14.1102C7.47659 13.6982 7.32961 13.244 6.97946 12.9894L2.97918 10.1008C2.19553 9.54169 2.59832 8.29653 3.56708 8.29653H8.57929C9.01252 8.29653 9.39647 8.01284 9.53029 7.60081L11.049 2.927Z"
									stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php esc_html_e( 'Rating', 'skillpulse-lms' ); ?>
						<span class="required">*</span>
					</label>
					<div class="star-rating-input">
						<?php for ( $splms_i = 1; $splms_i <= 5; $splms_i++ ) { ?>
							<?php
							/* translators: %d: Number of stars. */
							$splms_star_title = sprintf( esc_attr__( '%d stars', 'skillpulse-lms' ), $splms_i );
							?>
							<button type="button" class="star-btn" data-rating="<?php echo esc_attr( $splms_i ); ?>" title="<?php echo esc_attr( $splms_star_title ); ?>">★</button>
						<?php } ?>
					</div>
					<input type="hidden" name="rating" required/>
					<div class="rating-description"></div>
					<div class="rating-labels" style="display: none;">
						<span class="rating-label" data-rating="1"><?php esc_html_e( 'Poor', 'skillpulse-lms' ); ?></span>
						<span class="rating-label" data-rating="2"><?php esc_html_e( 'Fair', 'skillpulse-lms' ); ?></span>
						<span class="rating-label" data-rating="3"><?php esc_html_e( 'Good', 'skillpulse-lms' ); ?></span>
						<span class="rating-label" data-rating="4"><?php esc_html_e( 'Very Good', 'skillpulse-lms' ); ?></span>
						<span class="rating-label" data-rating="5"><?php esc_html_e( 'Excellent', 'skillpulse-lms' ); ?></span>
					</div>
					<div class="form-error" style="display: none;"></div>
				</div>

				<div class="form-group comment-input">
					<label for="review-comment" class="form-label">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z"
									stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php esc_html_e( 'Your Review', 'skillpulse-lms' ); ?>
						<span class="required">*</span>
					</label>
					<textarea
							name="review_text"
							id="review-comment"
							rows="5"
							placeholder="
							<?php
							esc_attr_e(
								'Tell us about your experience with this course. What did you like? What could be improved? Your feedback helps other students and the instructor.',
								'skillpulse-lms'
							);
							?>
							"
							required
							minlength="<?php echo esc_attr( $splms_min_length ); ?>"
							maxlength="<?php echo esc_attr( $splms_max_length ); ?>"
					></textarea>
					<div class="character-counter">
						<span class="current-count">0</span>/<span class="max-count"><?php echo esc_html( $splms_max_length ); ?></span>
					</div>
					<div class="form-error" style="display: none;"></div>
				</div>

				<div class="form-message" style="display: none;"></div>

				<div class="form-actions">
					<button type="submit" name="submit_review" class="btn btn-primary">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"
									stroke-linejoin="round"/>
						</svg>
						<?php esc_html_e( 'Submit Review', 'skillpulse-lms' ); ?>
					</button>
					<button type="button" class="btn btn-secondary cancel-btn">
						<?php esc_html_e( 'Cancel', 'skillpulse-lms' ); ?>
					</button>
				</div>

				<input type="hidden" name="course_id" value="<?php echo esc_attr( $splms_course_id ); ?>"/>
				<?php wp_nonce_field( 'splms_submit_review_' . $splms_course_id, 'splms_review_nonce' ); ?>
			</form>
		</div>
	</div>
</div>
