<?php
/**
 * Single Lesson Template - Full-Screen Layout
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/single-lesson.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get lesson and course data.
global $post;
$lesson_id = get_the_ID();
$user_id   = get_current_user_id();
$course_id = splms_get_lesson_course( $lesson_id );

// Get lesson settings.
$lesson_settings = splms_get_lesson_settings( $lesson_id );
$lesson_type     = isset( $lesson_settings['lesson_type'] ) ? $lesson_settings['lesson_type'] : 'text';

// Get navigation.
$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
$navigation       = $lessons_instance->get_lesson_navigation( $lesson_id, $user_id );

// Get course info.
$course_title = $course_id ? get_the_title( $course_id ) : '';
$course_url   = $course_id ? get_permalink( $course_id ) : '';

// Check if lesson is completed.
$is_completed = $user_id ? splms_is_lesson_completed( $lesson_id, $user_id ) : false;

// Get progress data.
$progress_data = array(
	'completed_lessons' => 0,
	'total_lessons'     => 0,
	'percentage'        => 0,
);
if ( $user_id && $course_id ) {
	$progress_data = $lessons_instance->calculate_course_progress( $user_id, $course_id );
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="splms-fullscreen-mode">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'splms-lesson-fullscreen splms-learning-player' ); ?>>

<?php
/**
 * Hook: splms_before_lesson_fullscreen
 */
do_action( 'splms_before_lesson_fullscreen' );
?>

<!-- Full-Screen Lesson Layout -->
<div id="splms-lesson-fullscreen-container" class="splms-fullscreen-container" data-lesson-id="<?php echo esc_attr( $lesson_id ); ?>" data-course-id="<?php echo esc_attr( $course_id ); ?>">

	<!-- Header Bar -->
	<?php splms_get_template_part( 'lesson/header' ); ?>

	<!-- Main Layout -->
	<div class="splms-fullscreen-layout">

		<!-- Sidebar -->
		<?php splms_get_template_part( 'lesson/sidebar' ); ?>

		<!-- Main Content -->
		<main class="splms-fullscreen-content" role="main">
			<?php
			while ( have_posts() ) {
				the_post();
				?>
				<!-- Lesson Content Area -->
				<?php splms_get_template_part( 'lesson/content' ); ?>

			<?php } ?>
		</main>

	</div>

	<!-- Footer Navigation -->
	<?php splms_get_template_part( 'lesson/footer' ); ?>

</div>

<?php
/**
 * Hook: splms_after_lesson_fullscreen
 */
do_action( 'splms_after_lesson_fullscreen' );

// Include completion modal templates for JavaScript.
splms_get_template_part( 'lesson/completion-modal-templates' );

wp_footer();
?>

</body>
</html>
