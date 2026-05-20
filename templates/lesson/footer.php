<?php
/**
 * Lesson Fullscreen Footer
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/lesson/footer.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Get lesson and course data.
$splms_lesson_id = get_the_ID();
$splms_user_id   = get_current_user_id();
$splms_course_id = splms_get_lesson_course( $splms_lesson_id );

// Get navigation.
$splms_lessons_instance = SPLMS_Lessons::get_instance();
$splms_navigation       = $splms_lessons_instance->get_lesson_navigation( $splms_lesson_id, $splms_user_id );

// Check completion status.
$splms_is_completed = $splms_user_id ? splms_is_lesson_completed( $splms_lesson_id, $splms_user_id ) : false;

// Check if user has access to this lesson.
$splms_has_access = splms_user_can_access_lesson( $splms_lesson_id, $splms_user_id );

// Get completion settings.
$splms_lesson_settings     = splms_get_lesson_settings( $splms_lesson_id );
$splms_completion_settings = isset( $splms_lesson_settings['lesson_completion_settings'] ) ? $splms_lesson_settings['lesson_completion_settings'] : array();
$splms_prevent_skip        = isset( $splms_completion_settings['prevent_skip'] ) ? $splms_completion_settings['prevent_skip'] : false;

// Prepare center content (Mark Complete button).
ob_start();
?>
<?php if ( $splms_has_access ) : ?>
	<button
		type="button"
		class="splms-btn splms-btn-complete <?php echo esc_attr( $splms_is_completed ? 'is-completed' : '' ); ?>"
		id="splms-mark-complete-btn"
		data-lesson-id="<?php echo esc_attr( $splms_lesson_id ); ?>"
		data-course-id="<?php echo esc_attr( $splms_course_id ); ?>"
		<?php echo esc_attr( $splms_is_completed ? 'disabled' : '' ); ?>
	>
		<svg class="splms-complete-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		<span class="splms-btn-text">
			<?php echo $splms_is_completed ? esc_html__( 'Completed', 'skillpulse-lms' ) : esc_html__( 'Mark as Complete', 'skillpulse-lms' ); ?>
		</span>
	</button>
<?php endif; ?>
<?php
$splms_center_content = ob_get_clean();

// Use shared navigation footer component.
splms_get_template_part(
	'shared/navigation-footer',
	'',
	array(
		'splms_context_class'   => 'splms-lesson-footer',
		'splms_navigation'      => $splms_navigation,
		'splms_center_content'  => $splms_center_content,
		'splms_current_item_id' => $splms_lesson_id,
		'splms_user_id'         => $splms_user_id,
	)
);
