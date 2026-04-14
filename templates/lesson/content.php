<?php
/**
 * Lesson Fullscreen Content
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/lesson/content.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get lesson data.
$lesson_id = get_the_ID();
$user_id   = get_current_user_id();

// Check access for this lesson.
$section_id          = splms_get_item_section( $lesson_id );
$course_id_for_check = splms_get_lesson_course( $lesson_id );
$has_access          = false;


// Check if user is enrolled in the full course.
if ( $user_id && $course_id_for_check && splms_is_user_enrolled( $course_id_for_check, $user_id ) ) {
	$has_access = true;
} elseif ( $user_id && $section_id ) {
	// Check if user has individual section access (handles all section pricing logic).
	$has_access = splms_user_has_section_access( $section_id, $user_id );
}

// If no access yet, check guest preview (for both logged-in and guest users).
if ( ! $has_access && splms_is_lesson_guest_preview_available( $lesson_id ) ) {
	$has_access = true;
}

// Get lesson settings.
$lesson_settings     = splms_get_lesson_settings( $lesson_id );
$lesson_type         = isset( $lesson_settings['lesson_type'] ) ? $lesson_settings['lesson_type'] : 'text';
$lesson_duration     = isset( $lesson_settings['lesson_duration'] ) ? $lesson_settings['lesson_duration'] : 0;
$lesson_video_url    = isset( $lesson_settings['lesson_video_url'] ) ? $lesson_settings['lesson_video_url'] : '';
$lesson_audio_url    = isset( $lesson_settings['lesson_audio_url'] ) ? $lesson_settings['lesson_audio_url'] : '';
$lesson_document_url = isset( $lesson_settings['lesson_document_url'] ) ? $lesson_settings['lesson_document_url'] : '';
$lesson_embed_code   = isset( $lesson_settings['lesson_embed_code'] ) ? $lesson_settings['lesson_embed_code'] : '';

// Get completion settings.
$completion_settings = isset( $lesson_settings['lesson_completion_settings'] ) ? $lesson_settings['lesson_completion_settings'] : array();
$completion_type     = isset( $completion_settings['completion_type'] ) ? $completion_settings['completion_type'] : 'manual';
$required_time       = isset( $completion_settings['required_time'] ) ? $completion_settings['required_time'] : 0;

// Get author info.
$author_id    = get_post_field( 'post_author', $lesson_id );
$author_name  = get_the_author_meta( 'display_name', $author_id );
$publish_date = get_the_date( '', $lesson_id );

// Get attachments and progress data.
$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
$attachments      = $lessons_instance->get_formatted_lesson_attachments( $lesson_id );

// Get lesson position and progress data.
$course_id       = splms_get_lesson_course( $lesson_id );
$course_items    = $lessons_instance->get_course_items_ordered( $course_id );
$lesson_position = 0;
$total_lessons   = 0;

foreach ( $course_items as $index => $item ) {
	if ( 'lesson' === $item['type'] ) {
		++$total_lessons;
		if ( intval( $item['id'] ) === intval( $lesson_id ) ) {
			$lesson_position = $total_lessons;
		}
	}
}

?>
<div class="splms-lesson-content-wrapper">

	<!-- Lesson Header -->
	<header class="splms-lesson-content-header">
		<h1 class="splms-lesson-content-title"><?php the_title(); ?></h1>

		<div class="splms-lesson-meta">
			<div class="splms-lesson-meta-item">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<span><?php echo esc_html( $author_name ); ?></span>
			</div>

			<span class="splms-meta-separator">•</span>

			<div class="splms-lesson-meta-item">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
					<path d="M16 2V6M8 2V6M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span><?php echo esc_html( $publish_date ); ?></span>
			</div>

			<?php if ( $lesson_duration > 0 ) : ?>
				<span class="splms-meta-separator">•</span>

				<div class="splms-lesson-meta-item">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
						<path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
					<span><?php echo esc_html( $lesson_duration ); ?> <?php esc_html_e( 'min', 'skillpulse-lms' ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</header>

	<!-- Lesson Media/Content -->
	<div class="splms-lesson-content-main">
		<?php if ( ! $has_access ) : ?>
			<!-- Locked Content Message -->
			<div class="splms-locked-content">
				<div class="splms-locked-icon">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
						<path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						<circle cx="12" cy="16" r="1" fill="currentColor"/>
					</svg>
				</div>
				<h3><?php esc_html_e( 'This lesson is locked', 'skillpulse-lms' ); ?></h3>
				<p><?php esc_html_e( 'You need to purchase this section to access this lesson.', 'skillpulse-lms' ); ?></p>

				<?php
				$course_id = splms_get_lesson_course( $lesson_id );
				if ( $course_id ) {
					$course_url = get_permalink( $course_id );
					?>
					<a href="<?php echo esc_url( $course_url ); ?>" class="splms-btn splms-btn-primary">
						<?php esc_html_e( 'View Course & Purchase', 'skillpulse-lms' ); ?>
					</a>
					<?php
				}
				?>
			</div>
		<?php else : ?>
			<?php
			// Load lesson type template.
			// This allows for theme overrides of specific lesson types.
			// Users can override by creating: yourtheme/skillpulse-lms/lesson/types/{type}.php.
			splms_get_template_part(
				'lesson/types/' . $lesson_type,
				null,
				array(
					'lesson_id'           => $lesson_id,
					'lesson_settings'     => $lesson_settings,
					'lesson_video_url'    => $lesson_video_url,
					'lesson_audio_url'    => $lesson_audio_url,
					'lesson_document_url' => $lesson_document_url,
					'lesson_embed_code'   => $lesson_embed_code,
				)
			);
			?>

			<!-- Lesson Text Content -->
			<?php if ( get_the_content() ) : ?>
				<div class="splms-lesson-text-content">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<!-- Completion Requirements Notice -->
		<?php if ( $has_access && 'time_based' === $completion_type && $required_time > 0 ) : ?>
			<div class="splms-completion-notice splms-notice-info">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
					<path d="M12 16V12M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<div>
					<?php
					printf(
						/* translators: %s: Required time in minutes */
						esc_html__( 'You must spend at least %s on this lesson before marking it as complete.', 'skillpulse-lms' ),
						esc_html( gmdate( 'i:s', $required_time ) )
					);
					?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Lesson Attachments -->
		<?php if ( $has_access && ! empty( $attachments ) ) : ?>
			<?php splms_get_template_part( 'lesson/attachments' ); ?>
		<?php endif; ?>

	</div>

</div>
