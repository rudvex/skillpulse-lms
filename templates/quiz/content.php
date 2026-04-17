<?php
/**
 * Quiz Fullscreen Content Component
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/quiz/content.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Get quiz data.
$splms_quiz_id          = get_the_ID();
$splms_quiz_title       = get_the_title();
$splms_quiz_content     = get_the_content();
$splms_quiz_description = get_the_excerpt();

// Get actual questions count from database.
$splms_questions_query      = SkillPulse_LMS_Quiz_Questions_Query::get_instance();
$splms_quiz_questions_count = $splms_questions_query->get_questions_count( $splms_quiz_id );

// Get quiz settings.
$splms_quizzes_instance = SkillPulse_LMS_Quizzes::get_instance();
$splms_quiz_settings    = $splms_quizzes_instance->get_quiz_settings( $splms_quiz_id );

// Helper function to get setting value from grouped or flat structure.
$splms_get_setting = function ( $splms_key, $splms_default_value = null ) use ( $splms_quiz_settings ) {
	// Try direct access first.
	if ( isset( $splms_quiz_settings[ $splms_key ] ) && ! is_array( $splms_quiz_settings[ $splms_key ] ) ) {
		return $splms_quiz_settings[ $splms_key ];
	}
	// Try finding in any group.
	foreach ( $splms_quiz_settings as $splms_group_key => $splms_group_value ) {
		if ( is_array( $splms_group_value ) && isset( $splms_group_value[ $splms_key ] ) ) {
			return $splms_group_value[ $splms_key ];
		}
	}
	return $splms_default_value;
};

$splms_quiz_passing_score       = $splms_get_setting( 'passing_grade', 70 );
$splms_quiz_attempts_allowed    = $splms_get_setting( 'max_attempts', 3 );
$splms_quiz_attempts_allowed    = ! empty( $splms_quiz_attempts_allowed ) ? intval( $splms_quiz_attempts_allowed ) : 0;
$splms_quiz_time_limit          = ( $splms_get_setting( 'time_limit_enabled', false ) && $splms_get_setting( 'time_limit', 0 ) ) ? $splms_get_setting( 'time_limit', 30 ) : null;
$splms_auto_show_duration_hours = intval( $splms_get_setting( 'auto_show_results_duration', 0 ) );

// Check password protection.
$splms_is_password_protected = $splms_get_setting( 'require_password', false );
$splms_quiz_password         = $splms_get_setting( 'quiz_password', '' );
$splms_password_verified     = false;

// Check if password was submitted and is correct.
if ( $splms_is_password_protected ) {
	if ( isset( $_POST['quiz_password'] ) && isset( $_POST['quiz_password_nonce'] ) ) {
		$splms_nonce = isset( $_POST['quiz_password_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['quiz_password_nonce'] ) ) : '';
		if ( wp_verify_nonce( $splms_nonce, 'quiz_password_' . $splms_quiz_id ) ) {
			$splms_submitted_password = isset( $_POST['quiz_password'] ) ? sanitize_text_field( wp_unslash( $_POST['quiz_password'] ) ) : '';
			if ( $splms_submitted_password === $splms_quiz_password ) {
				$splms_password_verified = true;
				// Store in session to avoid re-asking.
				if ( ! session_id() ) {
					session_start();
				}
				$_SESSION[ 'quiz_password_verified_' . $splms_quiz_id ] = true;
			}
		}
	} elseif ( isset( $_SESSION[ 'quiz_password_verified_' . $splms_quiz_id ] ) ) {
		$splms_password_verified = true;
	}
} else {
	$splms_password_verified = true;
}

// Get course information.
$splms_course_id        = splms_get_quiz_course( $splms_quiz_id );
$splms_course_permalink = $splms_course_id ? get_permalink( $splms_course_id ) : '';

// Get current user and enrollment status.
$splms_current_user_id  = get_current_user_id();
$splms_is_enrolled      = false;
$splms_is_guest_preview = false;

if ( $splms_current_user_id && $splms_course_id ) {
	$splms_is_enrolled = splms_is_user_enrolled( $splms_course_id, $splms_current_user_id );
}

// Check if this is a guest preview quiz.
if ( ! $splms_is_enrolled ) {
	$splms_is_guest_preview = splms_is_quiz_guest_preview_available( $splms_quiz_id );
}

// Check access for this quiz similar to lesson/content.php.
$splms_section_id = splms_get_item_section( $splms_quiz_id );
$splms_has_access = false;

// Check if user is enrolled in the full course.
if ( $splms_current_user_id && $splms_course_id && $splms_is_enrolled ) {
	$splms_has_access = true;
} elseif ( $splms_current_user_id && $splms_section_id && splms_get_setting( 'enable_section_based_pricing', false ) ) {
	// Check if logged-in user has purchased this specific section.
	$splms_has_access = splms_user_has_section_access( $splms_section_id, $splms_current_user_id );
}

// If no access yet, check guest preview (for both logged-in and guest users).
if ( ! $splms_has_access && $splms_is_guest_preview ) {
	$splms_has_access = true;
}

// Check quiz access restrictions.
$splms_access_granted = true;
$splms_access_message = '';

if ( $splms_current_user_id && $splms_is_enrolled && $splms_course_id ) {
	// Check course start date.
	if ( ! splms_is_course_content_available( $splms_course_id ) ) {
		$splms_access_granted  = false;
		$splms_start_date_info = splms_get_course_start_date_info( $splms_course_id );
		$splms_access_message  = $splms_start_date_info['message'];
	}
}

// Get quiz completion status and attempts using core functions.
$splms_quiz_attempts           = array();
$splms_attempts_used           = 0;
$splms_best_score              = 0;
$splms_quiz_completed          = false;
$splms_has_incomplete_attempt  = false;
$splms_incomplete_attempt_data = null;

if ( $splms_current_user_id ) {
	$splms_attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();

	// Use core functions for cleaner code.
	$splms_attempts_used  = $splms_attempts_query->count_completed_attempts( $splms_current_user_id, $splms_quiz_id );
	$splms_quiz_attempts  = $splms_quizzes_instance->get_formatted_quiz_attempts( $splms_current_user_id, $splms_quiz_id, true );
	$splms_quiz_completed = $splms_attempts_query->has_user_passed( $splms_current_user_id, $splms_quiz_id );

	// Calculate best score percentage from formatted attempts.
	$splms_best_score = 0;
	foreach ( $splms_quiz_attempts as $splms_attempt ) {
		$splms_percentage = isset( $splms_attempt['percentage'] ) ? floatval( $splms_attempt['percentage'] ) : 0;
		if ( $splms_percentage > $splms_best_score ) {
			$splms_best_score = $splms_percentage;
		}
	}

	// Check for incomplete attempt.
	$splms_incomplete_attempt = $splms_attempts_query->get_in_progress_attempt( $splms_current_user_id, $splms_quiz_id );
	if ( $splms_incomplete_attempt ) {
		$splms_has_incomplete_attempt  = true;
		$splms_incomplete_attempt_data = array(
			'attempt_id' => $splms_incomplete_attempt->id,
			'answers'    => $splms_incomplete_attempt->answers,
			'time_taken' => $splms_incomplete_attempt->time_taken,
			'start_time' => $splms_incomplete_attempt->attempt_time,
		);
	}

	// Check for recent completed attempts to show results instead of quiz interface.
	$splms_show_latest_results = false;
	$splms_latest_attempt_data = null;


	if ( ! empty( $splms_quiz_attempts ) ) {
		// Get the most recent completed attempt.
		$splms_latest_attempt = reset( $splms_quiz_attempts ); // First item (most recent).


		// Try multiple possible time field names.
		$splms_time_field       = null;
		$splms_attempt_time_raw = null;

		foreach ( array( 'attempt_time', 'attempt_date', 'date', 'created_at', 'time' ) as $splms_field ) {
			if ( isset( $splms_latest_attempt[ $splms_field ] ) ) {
				$splms_time_field       = $splms_field;
				$splms_attempt_time_raw = $splms_latest_attempt[ $splms_field ];
				break;
			}
		}

		if ( $splms_time_field && $splms_attempt_time_raw ) {
			$splms_attempt_time = strtotime( $splms_attempt_time_raw );
			// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Need timestamp for time difference calculation.
			$splms_current_time = current_time( 'timestamp' );
			$splms_time_diff    = $splms_current_time - $splms_attempt_time;


			// Convert auto-show duration from hours to seconds.
			$splms_auto_show_duration_seconds = $splms_auto_show_duration_hours * 3600;

			// Show results if within configured duration and no incomplete attempt.
			if ( $splms_auto_show_duration_hours > 0 && $splms_time_diff <= $splms_auto_show_duration_seconds && ! $splms_has_incomplete_attempt ) {
				$splms_show_latest_results = true;
				$splms_latest_attempt_data = $splms_latest_attempt;
			}
		}
	}
}
?>
<div class="splms-quiz-content-wrapper">
	<?php if ( ! $splms_has_access ) : ?>
		<!-- Section Access Locked Notice -->
		<div class="splms-locked-content">
			<div class="splms-locked-icon">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
					<path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					<circle cx="12" cy="16" r="1" fill="currentColor"/>
				</svg>
			</div>
			<h3><?php esc_html_e( 'Quiz Access Restricted', 'skillpulse-lms' ); ?></h3>
			<p><?php esc_html_e( 'You need to enroll in this course to access this quiz.', 'skillpulse-lms' ); ?></p>
			<?php if ( $splms_course_id && $splms_course_permalink ) : ?>
				<a href="<?php echo esc_url( $splms_course_permalink ); ?>" class="splms-btn splms-btn-primary">
					<?php esc_html_e( 'View Course', 'skillpulse-lms' ); ?>
				</a>
			<?php endif; ?>
		</div>

	<?php elseif ( ! $splms_is_enrolled && ! $splms_is_guest_preview ) : ?>
		<!-- Enrollment Required Notice -->
		<div class="splms-enrollment-notice">
			<div class="splms-notice-icon">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/>
					<path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
			</div>
			<h3><?php esc_html_e( 'Enrollment Required', 'skillpulse-lms' ); ?></h3>
			<p><?php esc_html_e( 'You need to be enrolled in this course to access this quiz.', 'skillpulse-lms' ); ?></p>
			<?php if ( $splms_course_id && $splms_course_permalink ) : ?>
				<a href="<?php echo esc_url( $splms_course_permalink ); ?>" class="splms-btn splms-btn-primary">
					<?php esc_html_e( 'View Course', 'skillpulse-lms' ); ?>
				</a>
			<?php endif; ?>
		</div>

	<?php elseif ( ! $splms_access_granted && $splms_is_enrolled ) : ?>
		<!-- Access Restricted Notice -->
		<div class="splms-access-restricted">
			<div class="splms-notice-icon">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
					<path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2"/>
				</svg>
			</div>
			<h3><?php esc_html_e( 'Access Restricted', 'skillpulse-lms' ); ?></h3>
			<p><?php echo esc_html( $splms_access_message ); ?></p>
		</div>

	<?php else : ?>
		<?php if ( $splms_is_guest_preview ) : ?>
			<!-- Guest Preview Banner -->
			<div class="splms-guest-preview-banner">
				<div class="splms-guest-preview-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 12S5 4 12 4S23 12 23 12S19 20 12 20S1 12 1 12Z" stroke="currentColor" stroke-width="2"/>
						<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
					</svg>
				</div>
				<div class="splms-guest-preview-content">
					<h4><?php esc_html_e( 'Quiz Preview Mode', 'skillpulse-lms' ); ?></h4>
					<p><?php esc_html_e( 'You are viewing this quiz in preview mode. Results will not be saved. Enroll to track your progress.', 'skillpulse-lms' ); ?></p>
					<?php if ( $splms_course_id && $splms_course_permalink ) : ?>
						<a href="<?php echo esc_url( $splms_course_permalink ); ?>" class="splms-btn splms-btn-sm splms-btn-secondary">
							<?php esc_html_e( 'View Course', 'skillpulse-lms' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $splms_is_password_protected && ! $splms_password_verified ) : ?>
			<!-- Password Protection Form -->
			<div class="splms-quiz-password-form">
				<div class="splms-password-form-icon">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
						<path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2"/>
					</svg>
				</div>
				<h3><?php esc_html_e( 'Password Required', 'skillpulse-lms' ); ?></h3>
				<p><?php esc_html_e( 'This quiz is password protected. Please enter the password to access it.', 'skillpulse-lms' ); ?></p>

				<?php if ( isset( $_POST['quiz_password'] ) && ! $splms_password_verified ) : ?>
					<div class="splms-password-error">
						<p><?php esc_html_e( 'Incorrect password. Please try again.', 'skillpulse-lms' ); ?></p>
					</div>
				<?php endif; ?>

				<form method="post" class="splms-quiz-password-form-fields">
					<?php wp_nonce_field( 'quiz_password_' . $splms_quiz_id, 'quiz_password_nonce' ); ?>
					<div class="splms-form-group">
						<label for="quiz_password"><?php esc_html_e( 'Password:', 'skillpulse-lms' ); ?></label>
						<input type="password" id="quiz_password" name="quiz_password" required>
					</div>
					<button type="submit" class="splms-btn splms-btn-primary">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
							<path d="M7 11V7C7 4 9 2 12 2C15 2 17 4 17 7V11" stroke="currentColor" stroke-width="2"/>
						</svg>
						<?php esc_html_e( 'Access Quiz', 'skillpulse-lms' ); ?>
					</button>
				</form>
			</div>

		<?php else : ?>

			<!-- Quiz Statistics -->
			<div class="splms-quiz-stats" <?php echo esc_attr( $splms_show_latest_results ? 'style="display: none;"' : '' ); ?>>
				<div class="splms-quiz-stat">
					<div class="splms-quiz-stat-icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
							<path d="M9.09 9C9.3251 8.33167 9.78915 7.76811 10.4 7.40913C11.0108 7.05016 11.7289 6.91894 12.4272 7.03871C13.1255 7.15849 13.7588 7.52152 14.2151 8.06353C14.6713 8.60553 14.9211 9.29152 14.92 10C14.92 12 11.92 13 11.92 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<path d="M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
					</div>
					<div class="splms-quiz-stat-value"><?php echo esc_html( $splms_quiz_questions_count ); ?></div>
					<div class="splms-quiz-stat-label"><?php esc_html_e( 'Questions', 'skillpulse-lms' ); ?></div>
				</div>

				<div class="splms-quiz-stat">
					<div class="splms-quiz-stat-icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="splms-quiz-stat-value"><?php echo esc_html( $splms_quiz_passing_score ); ?>%</div>
					<div class="splms-quiz-stat-label"><?php esc_html_e( 'Passing Score', 'skillpulse-lms' ); ?></div>
				</div>

				<div class="splms-quiz-stat">
					<div class="splms-quiz-stat-icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1 4V10H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M23 20V14H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14L18.36 18.36A9 9 0 0 1 3.51 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="splms-quiz-stat-value">
						<?php
						if ( $splms_is_guest_preview || 0 === $splms_quiz_attempts_allowed ) {
							esc_html_e( 'Unlimited', 'skillpulse-lms' );
						} else {
							echo esc_html( max( 0, $splms_quiz_attempts_allowed - $splms_attempts_used ) );
						}
						?>
					</div>
					<div class="splms-quiz-stat-label"><?php esc_html_e( 'Attempts Left', 'skillpulse-lms' ); ?></div>
				</div>

				<?php if ( $splms_best_score > 0 ) : ?>
					<div class="splms-quiz-stat">
						<div class="splms-quiz-stat-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 15C15.866 15 19 11.866 19 8C19 4.13401 15.866 1 12 1C8.13401 1 5 4.13401 5 8C5 11.866 8.13401 15 12 15Z" stroke="currentColor" stroke-width="2"/>
								<path d="M8.21 13.89L7 23L12 20L17 23L15.79 13.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<div class="splms-quiz-stat-value"><?php echo esc_html( $splms_best_score ); ?>%</div>
						<div class="splms-quiz-stat-label"><?php esc_html_e( 'Best Score', 'skillpulse-lms' ); ?></div>
					</div>
				<?php endif; ?>
			</div>

			<!-- Quiz Description -->
			<?php if ( $splms_quiz_content || $splms_quiz_description ) : ?>
				<div class="splms-quiz-description" <?php echo esc_attr( $splms_show_latest_results ? 'style="display: none;"' : '' ); ?>>
					<h2 class="splms-quiz-description-title"><?php esc_html_e( 'About This Quiz', 'skillpulse-lms' ); ?></h2>
					<?php
					if ( $splms_quiz_content ) {
						echo apply_filters( 'the_content', $splms_quiz_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} elseif ( $splms_quiz_description ) {
						echo wp_kses_post( wpautop( $splms_quiz_description ) );
					}
					?>
				</div>
			<?php endif; ?>

			<!-- Quiz Actions -->
			<div class="splms-quiz-actions" <?php echo esc_attr( $splms_show_latest_results ? 'style="display: none;"' : '' ); ?>>
				<?php
				// Restructured conditional logic with clear state priorities.
				if ( ! $splms_access_granted && $splms_is_enrolled ) :
					// Priority 1: Access denied (course start date restrictions).
					?>
					<div class="splms-access-notice">
						<p><?php esc_html_e( 'Quiz access is not available until course content is accessible.', 'skillpulse-lms' ); ?></p>
					</div>

					<?php
				elseif ( $splms_has_incomplete_attempt ) :
					// Priority 2: Resume incomplete attempt.
					?>
					<div class="splms-quiz-resume-notice">
						<h4><?php esc_html_e( 'Resume Your Quiz', 'skillpulse-lms' ); ?></h4>
						<p><?php esc_html_e( 'You have an incomplete quiz attempt. You can resume where you left off.', 'skillpulse-lms' ); ?></p>
						<div class="splms-quiz-action-buttons">
							<button type="button" class="splms-btn splms-btn-primary resume-quiz-btn" data-quiz-id="<?php echo esc_attr( $splms_quiz_id ); ?>" data-course-id="<?php echo esc_attr( $splms_course_id ); ?>">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<polygon points="5 3 19 12 5 21 5 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<?php esc_html_e( 'Resume Quiz', 'skillpulse-lms' ); ?>
							</button>
							<?php if ( 0 === $splms_quiz_attempts_allowed || $splms_attempts_used < $splms_quiz_attempts_allowed ) : ?>
								<button type="button" class="splms-btn splms-btn-secondary restart-quiz-btn" data-quiz-id="<?php echo esc_attr( $splms_quiz_id ); ?>" data-course-id="<?php echo esc_attr( $splms_course_id ); ?>">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M1 4V10H7M23 20V14H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14L18.36 18.36A9 9 0 0 1 3.51 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<?php esc_html_e( 'Start New Attempt', 'skillpulse-lms' ); ?>
								</button>
							<?php endif; ?>
						</div>
					</div>

					<?php
				elseif ( $splms_quiz_completed && ! $splms_is_guest_preview && ( 0 === $splms_quiz_attempts_allowed || $splms_attempts_used < $splms_quiz_attempts_allowed ) ) :
					// Priority 3: Quiz passed - show retake option.
					?>
					<div class="splms-quiz-completed-state">
						<div class="splms-quiz-passed-notice">
							<div class="splms-quiz-passed-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</div>
							<div class="splms-quiz-passed-content">
								<h4><?php esc_html_e( 'Quiz Completed Successfully!', 'skillpulse-lms' ); ?></h4>
								<p>
								<?php
								printf(
									/* translators: %s: Best score */
									esc_html__( 'Best Score: %s%%', 'skillpulse-lms' ),
									esc_html( $splms_best_score )
								);
								?>
										</p>
							</div>
						</div>
						<div class="splms-quiz-passed">
						<button type="button" class="splms-btn splms-btn-secondary splms-btn-large retake-quiz-btn start-quiz-btn" data-quiz-id="<?php echo esc_attr( $splms_quiz_id ); ?>" data-course-id="<?php echo esc_attr( $splms_course_id ); ?>">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1 4V10H7M23 20V14H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14L18.36 18.36A9 9 0 0 1 3.51 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<?php esc_html_e( 'Retake Quiz', 'skillpulse-lms' ); ?>
						</button>
						</div>
					</div>

					<?php
				elseif ( 0 === $splms_quiz_attempts_allowed || $splms_attempts_used < $splms_quiz_attempts_allowed || $splms_is_guest_preview ) :
					// Priority 4: Start quiz (first attempt or not completed).
					?>
					<button type="button" class="splms-btn splms-btn-primary splms-btn-large start-quiz-btn" data-quiz-id="<?php echo esc_attr( $splms_quiz_id ); ?>" data-course-id="<?php echo esc_attr( $splms_course_id ); ?>" <?php echo $splms_is_guest_preview ? 'data-preview-mode="true"' : ''; ?>>
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<polygon points="5 3 19 12 5 21 5 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php echo $splms_is_guest_preview ? esc_html__( 'Start Quiz Preview', 'skillpulse-lms' ) : esc_html__( 'Start Quiz', 'skillpulse-lms' ); ?>
					</button>

					<?php
				else :
					// Priority 5: No attempts remaining.
					?>
					<div class="splms-quiz-no-attempts">
						<div class="splms-no-attempts-icon">
							<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
								<path d="M15 9L9 15M9 9L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
						</div>
						<h3><?php esc_html_e( 'No Attempts Remaining', 'skillpulse-lms' ); ?></h3>
						<p><?php esc_html_e( 'You have used all available attempts for this quiz.', 'skillpulse-lms' ); ?></p>
						<?php if ( $splms_quiz_completed ) : ?>
							<p class="splms-final-score">
							<?php
							printf(
									/* translators: Score */
								esc_html__( 'Final Score: %s%%', 'skillpulse-lms' ),
								esc_html( $splms_best_score )
							);
							?>
									</p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Quiz Interface Container (hidden by default, shown by JavaScript) -->
			<div id="splms-quiz-interface" class="splms-quiz-interface" style="display: none;">
				<div class="splms-quiz-questions-container">
					<!-- JavaScript will render quiz questions here. -->
				</div>
				<div class="splms-quiz-navigation-container">
					<!-- JavaScript will render navigation here. -->
				</div>
			</div>

			<!-- Quiz Results Container (hidden by default, shown after submission) -->
			<div id="splms-quiz-results" class="splms-quiz-results" <?php echo esc_attr( $splms_show_latest_results ? '' : 'style="display: none;"' ); ?>>
				<?php if ( $splms_show_latest_results && $splms_latest_attempt_data ) : ?>
					<?php
					// Render the quiz results directly in PHP instead of using JavaScript.
					$splms_attempt = $splms_latest_attempt_data;

					// Determine status and styling.
					$splms_status_class = 'splms-quiz-result-failed';
					$splms_status_icon  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="var(--splms-danger)"/><path d="15 9L9 15M9 9L15 15" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>';
					$splms_heading      = __( 'Quiz Results', 'skillpulse-lms' );
					$splms_subtitle     = '';

					if ( $splms_attempt['pending_review'] ) {
						$splms_status_class = 'splms-quiz-result-pending';
						$splms_status_icon  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="var(--splms-warning)"/><path d="M12 6V12L16 14" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
						$splms_heading      = __( 'Quiz Submitted', 'skillpulse-lms' );
						$splms_subtitle     = __( 'Your answers are being reviewed', 'skillpulse-lms' );
					} elseif ( $splms_attempt['passed'] ) {
						$splms_status_class = 'splms-quiz-result-passed';
						$splms_status_icon  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="var(--splms-success)"/><path d="9 12L11 14L15 10" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
						$splms_heading      = __( 'Congratulations!', 'skillpulse-lms' );
						$splms_subtitle     = __( 'You have passed this quiz', 'skillpulse-lms' );
					} else {
						$splms_subtitle = __( 'You did not pass this quiz', 'skillpulse-lms' );
					}

					// Calculate values.
					$splms_score_percentage = round( $splms_attempt['percentage'], 1 );
					$splms_score            = $splms_attempt['score'] ?? 0;
					$splms_max_score        = $splms_attempt['max_score'] ?? 0;
					$splms_time_taken       = $splms_attempt['time_taken'] ?? 0;
					$splms_passing_grade    = $splms_quiz_passing_score ?? 70;

					// Calculate correct answers and total questions from score data
					// If max_score represents total questions (1 point per question).
					$splms_total_questions = intval( $splms_max_score );
					$splms_correct_answers = intval( $splms_score );

					// Format time taken.
					$splms_time_display = $splms_time_taken > 0 ? sprintf( '%d:%02d', floor( $splms_time_taken / 60 ), $splms_time_taken % 60 ) : '0:00';

					// Get retake availability.
					$splms_retake_available = ( 0 === $splms_quiz_attempts_allowed || $splms_attempts_used < $splms_quiz_attempts_allowed );

					// Security: Only show answers for practice quizzes, never for graded/assessment quizzes.
					$splms_quiz_type    = $splms_get_setting( 'quiz_type', 'graded' );
					$splms_show_answers = ! $splms_attempt['pending_review'] && 'practice' === $splms_quiz_type;

					// Status message.
					if ( $splms_attempt['pending_review'] ) {
						$splms_status_message = __( 'Your quiz is being reviewed by the instructor.', 'skillpulse-lms' );
					} elseif ( $splms_attempt['passed'] ) {
						$splms_status_message = __( 'You have successfully passed this quiz!', 'skillpulse-lms' );
					} else {
						$splms_status_message = sprintf(
							/* translators: 1: User's score percentage, 2: Required passing grade percentage */
							__( 'You scored %1$s%%, but need %2$s%% to pass.', 'skillpulse-lms' ),
							$splms_score_percentage,
							$splms_passing_grade
						);
					}
					?>

					<div class="splms-quiz-results-container">
						<!-- Result Header -->
						<div class="splms-quiz-result-header <?php echo esc_attr( $splms_status_class ); ?>">
							<div class="splms-quiz-result-icon">
								<span class="splms-quiz-result-status-icon"><?php echo wp_kses_post( $splms_status_icon ); ?></span>
							</div>
							<div class="splms-quiz-result-title">
								<h2 class="splms-quiz-result-heading"><?php echo esc_html( $splms_heading ); ?></h2>
								<p class="splms-quiz-result-subtitle"><?php echo esc_html( $splms_subtitle ); ?></p>
							</div>
						</div>

						<!-- Score Summary -->
						<div class="splms-quiz-score-summary">
							<div class="splms-quiz-score-item">
								<div class="splms-quiz-score-value splms-quiz-score-percentage"><?php echo esc_html( $splms_score_percentage ); ?>%</div>
								<div class="splms-quiz-score-label"><?php esc_html_e( 'Score', 'skillpulse-lms' ); ?></div>
							</div>
							<div class="splms-quiz-score-item">
								<div class="splms-quiz-score-value splms-quiz-correct-answers"><?php echo esc_html( $splms_correct_answers ); ?></div>
								<div class="splms-quiz-score-label"><?php esc_html_e( 'Correct', 'skillpulse-lms' ); ?></div>
							</div>
							<div class="splms-quiz-score-item">
								<div class="splms-quiz-score-value splms-quiz-total-questions"><?php echo esc_html( $splms_total_questions ); ?></div>
								<div class="splms-quiz-score-label"><?php esc_html_e( 'Total', 'skillpulse-lms' ); ?></div>
							</div>
							<div class="splms-quiz-score-item">
								<div class="splms-quiz-score-value splms-quiz-time-taken"><?php echo esc_html( $splms_time_display ); ?></div>
								<div class="splms-quiz-score-label"><?php esc_html_e( 'Time', 'skillpulse-lms' ); ?></div>
							</div>
						</div>

						<!-- Pass/Fail Status -->
						<div class="splms-quiz-pass-fail-status <?php echo esc_attr( $splms_status_class ); ?>">
							<div class="splms-quiz-status-content">
								<div class="splms-quiz-status-message"><?php echo esc_html( $splms_status_message ); ?></div>
								<div class="splms-quiz-passing-grade-info">
									<?php esc_html_e( 'Passing grade:', 'skillpulse-lms' ); ?> <span class="splms-quiz-passing-grade"><?php echo esc_html( $splms_passing_grade ); ?></span>%
								</div>
							</div>
						</div>

						<!-- Actions -->
						<div class="splms-quiz-result-actions">
							<div class="splms-quiz-action-buttons">
								<?php if ( $splms_show_answers ) : ?>
									<button type="button" class="splms-btn splms-btn-secondary splms-quiz-view-answers-btn view-answers-btn">
										<svg class="splms-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M1 12S5 4 12 4S23 12 23 12S19 20 12 20S1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<?php esc_html_e( 'View Answers', 'skillpulse-lms' ); ?>
									</button>
								<?php endif; ?>

								<?php if ( $splms_course_id && $splms_course_permalink ) : ?>
									<a href="<?php echo esc_url( $splms_course_permalink ); ?>" class="splms-btn splms-btn-secondary splms-quiz-view-course-btn">
										<svg class="splms-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M2 3H8C9.1 3 10 3.9 10 5V19C10 20.1 9.1 21 8 21H2V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M22 3H16C14.9 3 14 3.9 14 5V19C14 20.1 14.9 21 16 21H22V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<?php esc_html_e( 'View Course', 'skillpulse-lms' ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>

						<!-- Attempt History -->
						<div class="splms-quiz-attempt-history">
							<h4 class="splms-attempt-history-title">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
									<polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<?php esc_html_e( 'Attempt History', 'skillpulse-lms' ); ?>
							</h4>
							<div class="splms-quiz-attempts-list">
								<?php if ( ! empty( $splms_quiz_attempts ) ) : ?>
									<?php foreach ( $splms_quiz_attempts as $splms_index => $splms_history_attempt ) : ?>
										<?php
										$splms_history_status_class = 'attempt-failed';
										$splms_history_status_text  = __( 'Failed', 'skillpulse-lms' );
										$splms_history_status_icon  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="var(--splms-danger)"/><path d="15 9L9 15M9 9L15 15" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>';

										if ( $splms_history_attempt['pending_review'] ) {
											$splms_history_status_class = 'attempt-pending';
											$splms_history_status_text  = __( 'Pending Review', 'skillpulse-lms' );
											$splms_history_status_icon  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="var(--splms-warning)"/><path d="M12 6V12L16 14" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
										} elseif ( $splms_history_attempt['passed'] ) {
											$splms_history_status_class = 'attempt-passed';
											$splms_history_status_text  = __( 'Passed', 'skillpulse-lms' );
											$splms_history_status_icon  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="var(--splms-success)"/><path d="9 12L11 14L15 10" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
										}

										$splms_history_time_taken   = $splms_history_attempt['time_taken'] ?? 0;
										$splms_history_time_display = $splms_history_time_taken > 0 ? sprintf( '%d:%02d', floor( $splms_history_time_taken / 60 ), $splms_history_time_taken % 60 ) : '0:00';
										$splms_attempt_number       = count( $splms_quiz_attempts ) - $splms_index;
										?>
										<div class="splms-quiz-attempt-item <?php echo esc_attr( $splms_history_status_class ); ?> <?php echo ( $splms_history_attempt['id'] === $splms_attempt['id'] ) ? 'current-attempt' : ''; ?>">
											<div class="splms-attempt-header">
												<div class="splms-attempt-number">
													<span class="splms-attempt-badge">#<?php echo esc_html( $splms_attempt_number ); ?></span>
													<?php if ( $splms_history_attempt['id'] === $splms_attempt['id'] ) : ?>
														<span class="splms-current-badge"><?php esc_html_e( 'Current', 'skillpulse-lms' ); ?></span>
													<?php endif; ?>
												</div>
												<div class="splms-attempt-status-badge <?php echo esc_attr( $splms_history_status_class ); ?>">
													<span class="splms-status-icon"><?php echo wp_kses_post( $splms_history_status_icon ); ?></span>
													<span class="splms-status-text"><?php echo esc_html( $splms_history_status_text ); ?></span>
												</div>
											</div>
											<div class="splms-attempt-details">
												<div class="splms-attempt-detail-item">
													<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
														<rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
														<line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/>
														<line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/>
														<line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
													</svg>
													<span class="splms-attempt-date"><?php echo esc_html( wp_date( 'M j, Y \a\t g:i A', strtotime( $splms_history_attempt['date'] ) ) ); ?></span>
												</div>
												<div class="splms-attempt-detail-item">
													<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M9 11H15M9 15H15M17 21L12 16L7 21V5C7 3.89543 7.89543 3 9 3H15C16.1046 3 17 3.89543 17 5V21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
													</svg>
													<span class="splms-attempt-score"><?php echo esc_html( round( $splms_history_attempt['percentage'], 1 ) ); ?>% (<?php echo esc_html( $splms_history_attempt['score'] ?? 0 ); ?>/<?php echo esc_html( $splms_history_attempt['max_score'] ?? 0 ); ?>)</span>
												</div>
												<div class="splms-attempt-detail-item">
													<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
														<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
														<polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
													</svg>
													<span class="splms-attempt-time"><?php echo esc_html( $splms_history_time_display ); ?></span>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								<?php else : ?>
									<div class="splms-no-attempts-message">
										<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
											<line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2"/>
											<line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2"/>
										</svg>
										<p><?php esc_html_e( 'No previous attempts found.', 'skillpulse-lms' ); ?></p>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php else : ?>
					<!-- JavaScript will render quiz results here when not showing latest results -->
				<?php endif; ?>
			</div>


		<?php endif; ?>

	<?php endif; ?>

</div>
