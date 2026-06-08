<?php
/**
 * Shared Fullscreen Header Component
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/shared/fullscreen-header.php
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Extract parameters passed to template.
$splms_item_id   = isset( $args['splms_item_id'] ) ? $args['splms_item_id'] : get_the_ID();
$splms_item_type = isset( $args['splms_item_type'] ) ? $args['splms_item_type'] : 'lesson';
$splms_user_id   = isset( $args['splms_user_id'] ) ? $args['splms_user_id'] : get_current_user_id();

// Get course data based on item type.
if ( 'quiz' === $splms_item_type ) {
	$splms_course_id = splms_get_quiz_course( $splms_item_id );
} else {
	$splms_course_id = splms_get_lesson_course( $splms_item_id );
}

// Get course info.
$splms_course_title = '';
$splms_course_url   = '';
if ( $splms_course_id ) {
	$splms_course_title = get_the_title( $splms_course_id );
	$splms_course_url   = get_permalink( $splms_course_id );
}

// Get item position and totals.
if ( 'quiz' === $splms_item_type ) {
	$splms_quizzes_instance = SPLMS_Quizzes::get_instance();
	$splms_course_items     = $splms_quizzes_instance->get_course_items_ordered( $splms_course_id );
	$splms_item_position    = 0;
	$splms_total_items      = 0;

	foreach ( $splms_course_items as $splms_index => $splms_item ) {
		if ( 'quiz' === $splms_item['type'] ) {
			++$splms_total_items;
			if ( intval( $splms_item['id'] ) === intval( $splms_item_id ) ) {
				$splms_item_position = $splms_total_items;
			}
		}
	}

	// Quiz-specific status logic.
	$splms_attempts_query = SPLMS_Quiz_Attempts_Query::get_instance();
	$splms_has_passed     = $splms_user_id ? $splms_attempts_query->has_user_passed( $splms_user_id, $splms_item_id ) : false;
	$splms_status_class   = $splms_has_passed ? 'completed' : 'in-progress';
	$splms_status_text    = $splms_has_passed ? __( 'Passed', 'skillpulse-lms' ) : __( 'Not Attempted', 'skillpulse-lms' );

	// Quiz timer settings.
	$splms_quiz_settings  = $splms_quizzes_instance->get_quiz_settings( $splms_item_id );
	$splms_has_time_limit = isset( $splms_quiz_settings['quiz_time_settings']['enable_time_limit'] ) && $splms_quiz_settings['quiz_time_settings']['enable_time_limit'];
	$splms_time_limit     = $splms_has_time_limit && isset( $splms_quiz_settings['quiz_time_settings']['time_limit'] ) ? intval( $splms_quiz_settings['quiz_time_settings']['time_limit'] ) : 0;
} else {
	// Lesson logic.
	$splms_lessons_instance = SPLMS_Lessons::get_instance();
	$splms_course_items     = $splms_lessons_instance->get_course_items_ordered( $splms_course_id );
	$splms_item_position    = 0;
	$splms_total_items      = 0;

	foreach ( $splms_course_items as $splms_index => $splms_item ) {
		if ( 'lesson' === $splms_item['type'] ) {
			++$splms_total_items;
			if ( intval( $splms_item['id'] ) === intval( $splms_item_id ) ) {
				$splms_item_position = $splms_total_items;
			}
		}
	}

	// Lesson-specific status logic.
	$splms_is_completed = $splms_user_id ? splms_is_lesson_completed( $splms_item_id, $splms_user_id ) : false;
	$splms_status_class = $splms_is_completed ? 'completed' : 'in-progress';
	$splms_status_text  = $splms_is_completed ? __( 'Completed', 'skillpulse-lms' ) : __( 'In Progress', 'skillpulse-lms' );
}

// Check enrollment.
$splms_is_enrolled = $splms_user_id && $splms_course_id ? splms_is_user_enrolled( $splms_course_id, $splms_user_id ) : false;

// CSS classes.
$splms_header_class = 'splms-fullscreen-header';
if ( 'quiz' === $splms_item_type ) {
	$splms_header_class .= ' splms-quiz-header';
}
?>

<header class="<?php echo esc_attr( $splms_header_class ); ?>" role="banner">
	<div class="splms-header-left">
		<!-- Back Button -->
		<a href="<?php echo esc_url( $splms_course_url ? $splms_course_url : home_url() ); ?>" class="splms-back-button" aria-label="<?php esc_attr_e( 'Back to course', 'skillpulse-lms' ); ?>">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<span class="splms-back-text"><?php esc_html_e( 'Back', 'skillpulse-lms' ); ?></span>
		</a>

		<!-- Course & Item Title -->
		<div class="splms-header-titles">
			<?php if ( $splms_course_title ) : ?>
				<span class="splms-course-title"><?php echo esc_html( $splms_course_title ); ?></span>
				<span class="splms-title-separator">•</span>
			<?php endif; ?>
			<span class="splms-<?php echo esc_attr( $splms_item_type ); ?>-title"><?php echo esc_html( get_the_title() ); ?></span>
		</div>
	</div>

	<div class="splms-header-right">
		<!-- Quiz Timer Display (only for quizzes) -->
		<?php if ( 'quiz' === $splms_item_type ) : ?>
			<div class="splms-quiz-timer-display" style="display: none;" data-time-limit="<?php echo esc_attr( $splms_time_limit ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
					<path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span class="splms-timer-value">--:--</span>
			</div>
		<?php endif; ?>

		<!-- Item Counter -->
		<?php if ( $splms_total_items > 0 ) : ?>
			<div class="splms-<?php echo esc_attr( $splms_item_type ); ?>-counter">
				<?php
				if ( 'quiz' === $splms_item_type ) {
					printf(
						/* translators: 1: Current quiz number, 2: Total quizzes */
						esc_html__( 'QUIZ %1$d OF %2$d', 'skillpulse-lms' ),
						esc_html( $splms_item_position ),
						esc_html( $splms_total_items )
					);
				} else {
					printf(
						/* translators: 1: Current lesson number, 2: Total lessons */
						esc_html__( 'LESSON %1$d OF %2$d', 'skillpulse-lms' ),
						esc_html( $splms_item_position ),
						esc_html( $splms_total_items )
					);
				}
				?>
			</div>
		<?php endif; ?>

		<!-- Status Badge -->
		<span class="splms-status-badge splms-status-<?php echo esc_attr( $splms_status_class ); ?>">
			<?php echo esc_html( $splms_status_text ); ?>
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
		<?php if ( ! $splms_is_enrolled && $splms_course_id ) : ?>
			<?php
			$splms_course_settings = splms_get_course_settings( $splms_course_id );
			$splms_pricing_model   = isset( $splms_course_settings['pricing_model'] ) ? $splms_course_settings['pricing_model'] : 'free';
			if ( 'free' !== $splms_pricing_model ) :
				$splms_purchase_url = splms_get_course_purchase_url( $splms_course_id, $splms_user_id );
				?>
				<?php if ( $splms_purchase_url ) : ?>
				<a href="<?php echo esc_url( $splms_purchase_url ); ?>" class="splms-buy-now-button">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<?php if ( 'quiz' === $splms_item_type ) : ?>
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
if ( $splms_course_id && $splms_user_id ) {
	$splms_progress_data = splms_get_course_progress_data( $splms_user_id, $splms_course_id );

	if ( $splms_progress_data['total'] > 0 || $splms_progress_data['percentage'] > 0 ) {
		splms_get_template_part(
			'shared/progress-bar',
			'',
			array(
				'splms_progress_data'    => $splms_progress_data,
				'splms_show_info'        => false, // Keep it clean in header.
				'splms_show_label'       => false,
				'splms_show_percentage'  => true,
				'splms_show_items_count' => true,
				'splms_css_class'        => 'splms-fullscreen-header-progress',
			)
		);
	}
}
?>