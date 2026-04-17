<?php
/**
 * Shared Fullscreen Header Component
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/shared/fullscreen-header.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract parameters passed to template.
$item_id   = isset( $args['item_id'] ) ? $args['item_id'] : get_the_ID();
$item_type = isset( $args['item_type'] ) ? $args['item_type'] : 'lesson';
$user_id   = isset( $args['user_id'] ) ? $args['user_id'] : get_current_user_id();

// Get course data based on item type.
if ( 'quiz' === $item_type ) {
	$course_id = splms_get_quiz_course( $item_id );
} else {
	$course_id = splms_get_lesson_course( $item_id );
}

// Get course info.
$course_title = '';
$course_url   = '';
if ( $course_id ) {
	$course_title = get_the_title( $course_id );
	$course_url   = get_permalink( $course_id );
}

// Get item position and totals.
if ( 'quiz' === $item_type ) {
	$quizzes_instance = SkillPulse_LMS_Quizzes::get_instance();
	$course_items     = $quizzes_instance->get_course_items_ordered( $course_id );
	$item_position    = 0;
	$total_items      = 0;

	foreach ( $course_items as $index => $item ) {
		if ( 'quiz' === $item['type'] ) {
			++$total_items;
			if ( intval( $item['id'] ) === intval( $item_id ) ) {
				$item_position = $total_items;
			}
		}
	}

	// Quiz-specific status logic.
	$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
	$has_passed     = $user_id ? $attempts_query->has_user_passed( $user_id, $item_id ) : false;
	$status_class   = $has_passed ? 'completed' : 'in-progress';
	$status_text    = $has_passed ? __( 'Passed', 'skillpulse-lms' ) : __( 'Not Attempted', 'skillpulse-lms' );

	// Quiz timer settings.
	$quiz_settings  = $quizzes_instance->get_quiz_settings( $item_id );
	$has_time_limit = isset( $quiz_settings['quiz_time_settings']['enable_time_limit'] ) && $quiz_settings['quiz_time_settings']['enable_time_limit'];
	$time_limit     = $has_time_limit && isset( $quiz_settings['quiz_time_settings']['time_limit'] ) ? intval( $quiz_settings['quiz_time_settings']['time_limit'] ) : 0;
} else {
	// Lesson logic.
	$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
	$course_items     = $lessons_instance->get_course_items_ordered( $course_id );
	$item_position    = 0;
	$total_items      = 0;

	foreach ( $course_items as $index => $item ) {
		if ( 'lesson' === $item['type'] ) {
			++$total_items;
			if ( intval( $item['id'] ) === intval( $item_id ) ) {
				$item_position = $total_items;
			}
		}
	}

	// Lesson-specific status logic.
	$is_completed = $user_id ? splms_is_lesson_completed( $item_id, $user_id ) : false;
	$status_class = $is_completed ? 'completed' : 'in-progress';
	$status_text  = $is_completed ? __( 'Completed', 'skillpulse-lms' ) : __( 'In Progress', 'skillpulse-lms' );
}

// Check enrollment.
$is_enrolled = $user_id && $course_id ? splms_is_user_enrolled( $course_id, $user_id ) : false;

// CSS classes.
$header_class = 'splms-fullscreen-header';
if ( 'quiz' === $item_type ) {
	$header_class .= ' splms-quiz-header';
}
?>

<header class="<?php echo esc_attr( $header_class ); ?>" role="banner">
	<div class="splms-header-left">
		<!-- Back Button -->
		<a href="<?php echo esc_url( $course_url ? $course_url : home_url() ); ?>" class="splms-back-button" aria-label="<?php esc_attr_e( 'Back to course', 'skillpulse-lms' ); ?>">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<span class="splms-back-text"><?php esc_html_e( 'Back', 'skillpulse-lms' ); ?></span>
		</a>

		<!-- Course & Item Title -->
		<div class="splms-header-titles">
			<?php if ( $course_title ) : ?>
				<span class="splms-course-title"><?php echo esc_html( $course_title ); ?></span>
				<span class="splms-title-separator">•</span>
			<?php endif; ?>
			<span class="splms-<?php echo esc_attr( $item_type ); ?>-title"><?php echo esc_html( get_the_title() ); ?></span>
		</div>
	</div>

	<div class="splms-header-right">
		<!-- Quiz Timer Display (only for quizzes) -->
		<?php if ( 'quiz' === $item_type ) : ?>
			<div class="splms-quiz-timer-display" style="display: none;" data-time-limit="<?php echo esc_attr( $time_limit ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
					<path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span class="splms-timer-value">--:--</span>
			</div>
		<?php endif; ?>

		<!-- Item Counter -->
		<?php if ( $total_items > 0 ) : ?>
			<div class="splms-<?php echo esc_attr( $item_type ); ?>-counter">
				<?php
				if ( 'quiz' === $item_type ) {
					printf(
						/* translators: 1: Current quiz number, 2: Total quizzes */
						esc_html__( 'QUIZ %1$d OF %2$d', 'skillpulse-lms' ),
						esc_html( $item_position ),
						esc_html( $total_items )
					);
				} else {
					printf(
						/* translators: 1: Current lesson number, 2: Total lessons */
						esc_html__( 'LESSON %1$d OF %2$d', 'skillpulse-lms' ),
						esc_html( $item_position ),
						esc_html( $total_items )
					);
				}
				?>
			</div>
		<?php endif; ?>

		<!-- Status Badge -->
		<span class="splms-status-badge splms-status-<?php echo esc_attr( $status_class ); ?>">
			<?php echo esc_html( $status_text ); ?>
		</span>

		<!-- Mobile Menu Toggle -->
		<button type="button" class="splms-mobile-menu-toggle" aria-label="<?php esc_attr_e( 'Toggle menu', 'skillpulse-lms' ); ?>" aria-expanded="false">
			<svg class="splms-menu-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<svg class="splms-close-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
				<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>

		<!-- Buy Now Button (if not enrolled) -->
		<?php if ( ! $is_enrolled && $course_id ) : ?>
			<?php
			$course_settings = splms_get_course_settings( $course_id );
			$pricing_model   = isset( $course_settings['pricing_model'] ) ? $course_settings['pricing_model'] : 'free';
			if ( 'free' !== $pricing_model ) :
				$purchase_url = splms_get_course_purchase_url( $course_id, $user_id );
				?>
				<?php if ( $purchase_url ) : ?>
				<a href="<?php echo esc_url( $purchase_url ); ?>" class="splms-buy-now-button">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<?php if ( 'quiz' === $item_type ) : ?>
							<circle cx="9" cy="21" r="1" stroke="currentColor" stroke-width="2"/>
							<circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="2"/>
							<path d="M1 1H5L7.68 14.39C7.77144 14.8504 8.02191 15.264 8.38755 15.5583C8.75318 15.8526 9.2107 16.009 9.68 16H19.4C19.8693 16.009 20.3268 15.8526 20.6925 15.5583C21.0581 15.264 21.3086 14.8504 21.4 14.39L23 6H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<?php else : ?>
							<path d="M9 2L7 4H3C1.9 4 1 4.9 1 6V20C1 21.1 1.9 22 3 22H21C22.1 22 23 21.1 23 20V6C23 4.9 22.1 4 21 4H17L15 2H9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<circle cx="12" cy="13" r="3" stroke="currentColor" stroke-width="2"/>
						<?php endif; ?>
					</svg>
					<span class="splms-buy-now-text"><?php esc_html_e( 'Buy Now', 'skillpulse-lms' ); ?></span>
				</a>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</header>

<?php
// Display course progress bar prominently after header.
if ( $course_id && $user_id ) {
	$progress_data = splms_get_course_progress_data( $user_id, $course_id );

	if ( $progress_data['total'] > 0 || $progress_data['percentage'] > 0 ) {
		splms_get_template_part(
			'shared/progress-bar',
			'',
			array(
				'progress_data'    => $progress_data,
				'show_info'        => false, // Keep it clean in header.
				'show_label'       => false,
				'show_percentage'  => true,
				'show_items_count' => true,
				'css_class'        => 'splms-fullscreen-header-progress',
			)
		);
	}
}
?>