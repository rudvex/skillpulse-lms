<?php
/**
 * Single Lesson Template - Full-Screen Layout
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/single-lesson.php
 *
 * @package SPLMS
 * @version 1.0.0
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Get lesson and course data.
global $post;
$splms_lesson_id = get_the_ID();
$splms_user_id   = get_current_user_id();
$splms_course_id = splms_get_lesson_course( $splms_lesson_id );

// Get lesson settings.
$splms_lesson_settings = splms_get_lesson_settings( $splms_lesson_id );
$splms_lesson_type     = isset( $splms_lesson_settings['lesson_type'] ) ? $splms_lesson_settings['lesson_type'] : 'text';

// Get navigation.
$splms_lessons_instance = SPLMS_Lessons::get_instance();
$splms_navigation       = $splms_lessons_instance->get_lesson_navigation( $splms_lesson_id, $splms_user_id );

// Get course info.
$splms_course_title = $splms_course_id ? get_the_title( $splms_course_id ) : '';
$splms_course_url   = $splms_course_id ? get_permalink( $splms_course_id ) : '';

// Check if lesson is completed.
$splms_is_completed = $splms_user_id ? splms_is_lesson_completed( $splms_lesson_id, $splms_user_id ) : false;

// Get progress data.
$splms_progress_data = array(
	'completed_lessons' => 0,
	'total_lessons'     => 0,
	'percentage'        => 0,
);
if ( $splms_user_id && $splms_course_id ) {
	$splms_progress_data = $splms_lessons_instance->calculate_course_progress( $splms_user_id, $splms_course_id );
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
<div id="splms-lesson-fullscreen-container" class="splms-fullscreen-container" data-lesson-id="<?php echo esc_attr( $splms_lesson_id ); ?>" data-course-id="<?php echo esc_attr( $splms_course_id ); ?>">

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
