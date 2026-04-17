<?php
/**
 * Quiz Fullscreen Footer Component
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/quiz/footer.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get quiz and course data.
$quiz_id   = get_the_ID();
$user_id   = get_current_user_id();
$course_id = splms_get_quiz_course( $quiz_id );

// Get quiz navigation.
$quizzes_instance = SkillPulse_LMS_Quizzes::get_instance();
$navigation       = $quizzes_instance->get_quiz_navigation( $quiz_id, $user_id );

// Check if quiz is completed/passed.
$is_completed = false;
if ( $user_id ) {
	$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
	$is_completed   = $attempts_query->has_user_passed( $user_id, $quiz_id );
}

// Get progress data for progress bar.
$progress_percentage = 0;
if ( $user_id && $course_id ) {
	$lessons_instance    = SkillPulse_LMS_Lessons::get_instance();
	$progress_data       = $lessons_instance->calculate_course_progress( $user_id, $course_id );
	$progress_percentage = isset( $progress_data['percentage'] ) ? floatval( $progress_data['percentage'] ) : 0;
}

// Prepare center content (Quiz Status Info).
ob_start();
?>
<div class="splms-quiz-status-info">
	<?php if ( $is_completed ) : ?>
		<div class="splms-quiz-passed-badge">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<span><?php esc_html_e( 'Quiz Passed', 'skillpulse-lms' ); ?></span>
		</div>
	<?php else : ?>
		<div class="splms-quiz-info-text">
			<?php esc_html_e( 'Complete the quiz to continue', 'skillpulse-lms' ); ?>
		</div>
	<?php endif; ?>
</div>
<?php
$center_content = ob_get_clean();

// Use shared navigation footer component.
splms_get_template_part(
	'shared/navigation-footer',
	'',
	array(
		'context_class'   => 'splms-quiz-footer',
		'navigation'      => $navigation,
		'center_content'  => $center_content,
		'current_item_id' => $quiz_id,
		'user_id'         => $user_id,
	)
);
