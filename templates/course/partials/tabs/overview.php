<?php
/**
 * Course Overview Tab
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/course/partials/tabs/overview.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$course_id = get_the_ID();
?>

<div class="course-content-tabs">
	<div class="course-content-section course-overview">
		<h2 class="section-title"><?php esc_html_e( 'Course Overview', 'skillpulse-lms' ); ?></h2>
		<div class="section-content">
			<?php the_content(); ?>

			<?php
			// Course requirements/prerequisites.
			$prerequisites = splms_get_course_prerequisites( $course_id );
			if ( ! empty( $prerequisites ) ) {
				?>
				<div class="course-requirements">
					<h3><?php esc_html_e( 'Prerequisites & Requirements', 'skillpulse-lms' ); ?></h3>
					<div class="requirements-content">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wpautop output is safe when used with wp_kses_post.
						echo wpautop( wp_kses_post( $prerequisites ) );
						?>
					</div>
				</div>
			<?php } ?>

			<?php
			// What you'll learn - learning outcomes.
			$content_info      = splms_get_course_content_info( $course_id );
			$learning_outcomes = $content_info['learning_outcomes'];
			if ( ! empty( $learning_outcomes ) ) {
				?>
				<div class="course-learning-outcomes">
					<h3><?php esc_html_e( 'What You\'ll Learn', 'skillpulse-lms' ); ?></h3>
					<div class="learning-outcomes-content">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wpautop output is safe when used with wp_kses_post.
						echo wpautop( wp_kses_post( $learning_outcomes ) );
						?>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
