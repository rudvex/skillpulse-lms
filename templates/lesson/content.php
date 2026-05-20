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
$splms_lesson_id = get_the_ID();
$splms_user_id   = get_current_user_id();

// Check access for this lesson.
$splms_section_id          = splms_get_item_section( $splms_lesson_id );
$splms_course_id_for_check = splms_get_lesson_course( $splms_lesson_id );
$splms_has_access          = false;


// Check if user is enrolled in the full course.
if ( $splms_user_id && $splms_course_id_for_check && splms_is_user_enrolled( $splms_course_id_for_check, $splms_user_id ) ) {
	$splms_has_access = true;
} elseif ( $splms_user_id && $splms_section_id ) {
	// Check if user has individual section access (handles all section pricing logic).
	$splms_has_access = splms_user_has_section_access( $splms_section_id, $splms_user_id );
}

// If no access yet, check guest preview (for both logged-in and guest users).
if ( ! $splms_has_access && splms_is_lesson_guest_preview_available( $splms_lesson_id ) ) {
	$splms_has_access = true;
}

// Get lesson settings.
$splms_lesson_settings     = splms_get_lesson_settings( $splms_lesson_id );
$splms_lesson_type         = isset( $splms_lesson_settings['lesson_type'] ) ? $splms_lesson_settings['lesson_type'] : 'text';
$splms_lesson_duration     = isset( $splms_lesson_settings['lesson_duration'] ) ? $splms_lesson_settings['lesson_duration'] : 0;
$splms_lesson_video_url    = isset( $splms_lesson_settings['lesson_video_url'] ) ? $splms_lesson_settings['lesson_video_url'] : '';
$splms_lesson_audio_url    = isset( $splms_lesson_settings['lesson_audio_url'] ) ? $splms_lesson_settings['lesson_audio_url'] : '';
$splms_lesson_document_url = isset( $splms_lesson_settings['lesson_document_url'] ) ? $splms_lesson_settings['lesson_document_url'] : '';
$splms_lesson_embed_code   = isset( $splms_lesson_settings['lesson_embed_code'] ) ? $splms_lesson_settings['lesson_embed_code'] : '';

// Get completion settings.
$splms_completion_settings = isset( $splms_lesson_settings['lesson_completion_settings'] ) ? $splms_lesson_settings['lesson_completion_settings'] : array();
$splms_completion_type     = isset( $splms_completion_settings['completion_type'] ) ? $splms_completion_settings['completion_type'] : 'manual';
$splms_required_time       = isset( $splms_completion_settings['required_time'] ) ? $splms_completion_settings['required_time'] : 0;

// Get author info.
$splms_author_id    = get_post_field( 'post_author', $splms_lesson_id );
$splms_author_name  = get_the_author_meta( 'display_name', $splms_author_id );
$splms_publish_date = get_the_date( '', $splms_lesson_id );

// Get attachments and progress data.
$splms_lessons_instance = SPLMS_Lessons::get_instance();
$splms_attachments      = $splms_lessons_instance->get_formatted_lesson_attachments( $splms_lesson_id );

// Get lesson position and progress data.
$splms_course_id       = splms_get_lesson_course( $splms_lesson_id );
$splms_course_items    = $splms_lessons_instance->get_course_items_ordered( $splms_course_id );
$splms_lesson_position = 0;
$splms_total_lessons   = 0;

foreach ( $splms_course_items as $splms_index => $splms_item ) {
	if ( 'lesson' === $splms_item['type'] ) {
		++$splms_total_lessons;
		if ( intval( $splms_item['id'] ) === intval( $splms_lesson_id ) ) {
			$splms_lesson_position = $splms_total_lessons;
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
				<span><?php echo esc_html( $splms_author_name ); ?></span>
			</div>

			<span class="splms-meta-separator">•</span>

			<div class="splms-lesson-meta-item">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
					<path d="M16 2V6M8 2V6M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span><?php echo esc_html( $splms_publish_date ); ?></span>
			</div>

			<?php if ( $splms_lesson_duration > 0 ) : ?>
				<span class="splms-meta-separator">•</span>

				<div class="splms-lesson-meta-item">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
						<path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
					<span><?php echo esc_html( $splms_lesson_duration ); ?> <?php esc_html_e( 'min', 'skillpulse-lms' ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</header>

	<!-- Lesson Media/Content -->
	<div class="splms-lesson-content-main">
		<?php if ( ! $splms_has_access ) : ?>
			<?php
			// Determine lock type — drip lock vs purchase lock.
			$splms_is_drip_locked = false;
			$splms_drip_date      = false;
			$splms_course_id      = splms_get_lesson_course( $splms_lesson_id );

			if ( is_user_logged_in() && $splms_course_id && splms_is_user_enrolled( $splms_course_id ) ) {
				// User is enrolled — check if this is a drip lock.
				if ( ! splms_is_lesson_drip_available( $splms_lesson_id ) ) {
					$splms_is_drip_locked = true;
					$splms_drip_date      = splms_get_lesson_drip_unlock_date( $splms_lesson_id );
				}
			}
			?>
			<!-- Locked Content Message -->
			<div class="splms-locked-content <?php echo $splms_is_drip_locked ? 'splms-drip-locked' : ''; ?>">
				<div class="splms-locked-icon">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<?php if ( $splms_is_drip_locked ) : ?>
							<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
							<path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<?php else : ?>
							<rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
							<path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<circle cx="12" cy="16" r="1" fill="currentColor"/>
						<?php endif; ?>
					</svg>
				</div>

				<?php if ( $splms_is_drip_locked ) : ?>
					<h3><?php esc_html_e( 'This lesson is not yet available', 'skillpulse-lms' ); ?></h3>
					<?php if ( $splms_drip_date ) : ?>
						<p>
							<?php
							printf(
								/* translators: %s: Date when the lesson becomes available. */
								esc_html__( 'This lesson will be available %s.', 'skillpulse-lms' ),
								'<strong>' . esc_html( $splms_drip_date ) . '</strong>'
							);
							?>
						</p>
					<?php else : ?>
						<p><?php esc_html_e( 'This lesson will become available soon. Please check back later.', 'skillpulse-lms' ); ?></p>
					<?php endif; ?>

					<?php if ( $splms_course_id ) : ?>
						<a href="<?php echo esc_url( get_permalink( $splms_course_id ) ); ?>" class="splms-btn splms-btn-secondary">
							<?php esc_html_e( 'Back to Course', 'skillpulse-lms' ); ?>
						</a>
					<?php endif; ?>
				<?php else : ?>
					<h3><?php esc_html_e( 'This lesson is locked', 'skillpulse-lms' ); ?></h3>
					<p><?php esc_html_e( 'You need to purchase this section to access this lesson.', 'skillpulse-lms' ); ?></p>

					<?php if ( $splms_course_id ) : ?>
						<a href="<?php echo esc_url( get_permalink( $splms_course_id ) ); ?>" class="splms-btn splms-btn-primary">
							<?php esc_html_e( 'View Course & Purchase', 'skillpulse-lms' ); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<?php
			// Users can override by creating: yourtheme/skillpulse-lms/lesson/types/{type}.php.
			splms_get_template_part(
				'lesson/types/' . $splms_lesson_type,
				null,
				array(
					'splms_lesson_id'           => $splms_lesson_id,
					'splms_lesson_settings'     => $splms_lesson_settings,
					'splms_lesson_video_url'    => $splms_lesson_video_url,
					'splms_lesson_audio_url'    => $splms_lesson_audio_url,
					'splms_lesson_document_url' => $splms_lesson_document_url,
					'splms_lesson_embed_code'   => $splms_lesson_embed_code,
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
		<?php if ( $splms_has_access && 'time_based' === $splms_completion_type && $splms_required_time > 0 ) : ?>
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
						esc_html( gmdate( 'i:s', $splms_required_time ) )
					);
					?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Lesson Attachments -->
		<?php if ( $splms_has_access && ! empty( $splms_attachments ) ) : ?>
			<?php splms_get_template_part( 'lesson/attachments' ); ?>
		<?php endif; ?>

	</div>

</div>
