<?php
/**
 * Single course template
 *
 * This template follows WordPress theme conventions for single post pages.
 * It can be overridden by copying it to yourtheme/single-course.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

<div class="splms-container splms-single-course">

	<?php
	/**
	 * Hook: splms_before_single_course_content
	 */
	do_action( 'splms_before_single_course_content' );
	?>

	<?php
	while ( have_posts() ) {
		the_post();
		?>

		<article id="course-<?php the_ID(); ?>" <?php post_class( 'splms-course-single' ); ?>>

			<?php splms_get_template_part( 'course/partials/course', 'hero' ); ?>

			<div class="course-main-content">
				<div class="course-content-area">
					<?php splms_get_template_part( 'course/partials/course', 'navigation' ); ?>
					<?php splms_get_template_part( 'course/partials/course', 'content' ); ?>
				</div>

				<div class="course-sidebar">
					<?php splms_get_template_part( 'course/partials/course', 'sidebar' ); ?>
				</div>
			</div>

			<?php splms_get_template_part( 'course/partials/pricing', 'comparison' ); ?>

			<?php splms_get_template_part( 'course/partials/course', 'related' ); ?>

		</article>

	<?php } ?>

	<?php
	/**
	 * Hook: splms_after_single_course_content
	 */
	do_action( 'splms_after_single_course_content' );
	?>

</div>

<?php get_footer(); ?>
