<?php
/**
 * Single Quiz Template - Full-Screen Layout
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/single-quiz.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get quiz and course data.
global $post;
$quiz_id   = get_the_ID();
$user_id   = get_current_user_id();
$course_id = splms_get_quiz_course( $quiz_id );

// Get quiz settings.
$quizzes_instance = SkillPulse_LMS_Quizzes::get_instance();
$quiz_settings    = $quizzes_instance->get_quiz_settings( $quiz_id );
$quiz_type        = isset( $quiz_settings['quiz_type_settings']['quiz_type'] ) ? $quiz_settings['quiz_type_settings']['quiz_type'] : 'graded';

// Get navigation.
$navigation = $quizzes_instance->get_quiz_navigation( $quiz_id, $user_id );

// Get course info.
$course_title = $course_id ? get_the_title( $course_id ) : '';
$course_url   = $course_id ? get_permalink( $course_id ) : '';

// Check if quiz is passed.
$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
$has_passed     = $user_id ? $attempts_query->has_user_passed( $user_id, $quiz_id ) : false;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="splms-fullscreen-mode">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'splms-quiz-fullscreen splms-learning-player' ); ?>>

<?php
/**
 * Hook: splms_before_quiz_fullscreen
 */
do_action( 'splms_before_quiz_fullscreen' );
?>

<!-- Full-Screen Quiz Layout -->
<div id="splms-quiz-fullscreen-container" class="splms-fullscreen-container splms-quiz-container" data-quiz-id="<?php echo esc_attr( $quiz_id ); ?>" data-course-id="<?php echo esc_attr( $course_id ); ?>">

	<!-- Header Bar -->
	<?php splms_get_template_part( 'quiz/header' ); ?>

	<!-- Main Layout -->
	<div class="splms-fullscreen-layout">

		<!-- Sidebar (reuse lesson sidebar) -->
		<?php splms_get_template_part( 'lesson/sidebar' ); ?>

		<!-- Main Content -->
		<main class="splms-fullscreen-content splms-quiz-content" role="main">
			<?php
			while ( have_posts() ) {
				the_post();
				?>

				<!-- Quiz Content Area -->
				<?php splms_get_template_part( 'quiz/content' ); ?>

			<?php } ?>
		</main>

	</div>

	<!-- Footer Navigation -->
	<?php splms_get_template_part( 'quiz/footer' ); ?>

</div>

<?php
/**
 * Hook: splms_after_quiz_fullscreen
 */
do_action( 'splms_after_quiz_fullscreen' );
?>

<!-- Include quiz question templates -->
<?php splms_get_template_part( 'quiz/question-templates' ); ?>

<?php wp_footer(); ?>

</body>
</html>
