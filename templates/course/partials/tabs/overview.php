<?php
/**
 * Course Overview Tab
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/course/partials/tabs/overview.php.
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



$splms_course_id = get_the_ID();
?>

<div class="course-content-tabs">
	<div class="course-content-section course-overview">
		<h2 class="section-title"><?php esc_html_e( 'Course Overview', 'skillpulse-lms' ); ?></h2>
		<div class="section-content">
			<?php the_content(); ?>

			<?php
			// Course requirements/prerequisites.
			$splms_prerequisites = splms_get_course_prerequisites( $splms_course_id );
			if ( ! empty( $splms_prerequisites ) ) {
				?>
				<div class="course-requirements">
					<h3><?php esc_html_e( 'Prerequisites & Requirements', 'skillpulse-lms' ); ?></h3>
					<div class="requirements-content">
						echo wp_kses_post( wpautop( wp_kses_post( $splms_prerequisites ) ) );
						?>
					</div>
				</div>
			<?php } ?>

			<?php
			// What you'll learn - learning outcomes.
			$splms_content_info      = splms_get_course_content_info( $splms_course_id );
			$splms_learning_outcomes = $splms_content_info['learning_outcomes'];
			if ( ! empty( $splms_learning_outcomes ) ) {
				?>
				<div class="course-learning-outcomes">
					<h3><?php esc_html_e( 'What You\'ll Learn', 'skillpulse-lms' ); ?></h3>
					<div class="learning-outcomes-content">
						echo wp_kses_post( wpautop( wp_kses_post( $splms_learning_outcomes ) ) );
						?>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
