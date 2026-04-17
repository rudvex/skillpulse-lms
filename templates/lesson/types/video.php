<?php
/**
 * Lesson Type: Video
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/lesson/types/video.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get passed variables.
$lesson_id        = isset( $args['lesson_id'] ) ? $args['lesson_id'] : get_the_ID();
$lesson_video_url = isset( $args['lesson_video_url'] ) ? $args['lesson_video_url'] : '';

if ( ! $lesson_video_url ) {
	return;
}

// Get lesson settings for completion requirement.
$lesson_settings     = isset( $args['lesson_settings'] ) ? $args['lesson_settings'] : splms_get_lesson_settings( $lesson_id );
$completion_required = isset( $lesson_settings['lesson_completion_required'] ) ? intval( $lesson_settings['lesson_completion_required'] ) : 100;

// Determine video type.
$video_type = 'unknown';
if ( false !== strpos( $lesson_video_url, 'youtube.com' ) || false !== strpos( $lesson_video_url, 'youtu.be' ) ) {
	$video_type = 'youtube';
} elseif ( false !== strpos( $lesson_video_url, 'vimeo.com' ) ) {
	$video_type = 'vimeo';
} elseif ( preg_match( '/\.(mp4|webm|ogg)$/i', $lesson_video_url ) ) {
	$video_type = 'html5';
}

// Extract video ID for YouTube/Vimeo.
$video_id = '';
if ( 'youtube' === $video_type ) {
	if ( preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $lesson_video_url, $matches ) ) {
		$video_id = $matches[1];
	}
} elseif ( 'vimeo' === $video_type ) {
	if ( preg_match( '/vimeo\.com\/(?:video\/)?(\d+)/i', $lesson_video_url, $matches ) ) {
		$video_id = $matches[1];
	}
}
?>

<div class="splms-video-player-container"
	data-lesson-id="<?php echo esc_attr( $lesson_id ); ?>"
	data-video-type="<?php echo esc_attr( $video_type ); ?>"
	data-video-id="<?php echo esc_attr( $video_id ); ?>"
	data-video-url="<?php echo esc_url( $lesson_video_url ); ?>"
	data-completion-required="<?php echo esc_attr( $completion_required ); ?>">

	<?php if ( 'youtube' === $video_type && $video_id ) : ?>
		<!-- YouTube Player -->
		<div class="splms-video-player-wrapper">
			<div id="splms-youtube-player-<?php echo esc_attr( $lesson_id ); ?>" class="splms-video-player"></div>
		</div>

		<!-- Video Controls Info -->
		<div class="splms-video-info">
			<div class="splms-video-progress-info">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
					<path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span class="splms-video-watch-percentage">0%</span>
				<span><?php esc_html_e( 'watched', 'skillpulse-lms' ); ?></span>
			</div>

			<?php if ( $completion_required < 100 ) : ?>
				<div class="splms-video-requirement-info">
					<?php
					printf(
						/* translators: %d: Required completion percentage */
						esc_html__( 'Watch at least %d%% to complete', 'skillpulse-lms' ),
						esc_html( $completion_required )
					);
					?>
				</div>
			<?php endif; ?>
		</div>

	<?php elseif ( 'vimeo' === $video_type && $video_id ) : ?>
		<!-- Vimeo Player -->
		<div class="splms-video-player-wrapper">
			<iframe
				id="splms-vimeo-player-<?php echo esc_attr( $lesson_id ); ?>"
				class="splms-video-player"
				src="https://player.vimeo.com/video/<?php echo esc_attr( $video_id ); ?>?byline=0&portrait=0"
				frameborder="0"
				allow="autoplay; fullscreen; picture-in-picture"
				allowfullscreen>
			</iframe>
		</div>

		<!-- Video Controls Info -->
		<div class="splms-video-info">
			<div class="splms-video-progress-info">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
					<path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span class="splms-video-watch-percentage">0%</span>
				<span><?php esc_html_e( 'watched', 'skillpulse-lms' ); ?></span>
			</div>

			<?php if ( $completion_required < 100 ) : ?>
				<div class="splms-video-requirement-info">
					<?php
					printf(
						/* translators: %d: Required completion percentage */
						esc_html__( 'Watch at least %d%% to complete', 'skillpulse-lms' ),
						esc_html( $completion_required )
					);
					?>
				</div>
			<?php endif; ?>
		</div>

	<?php elseif ( 'html5' === $video_type ) : ?>
		<!-- HTML5 Video Player -->
		<div class="splms-video-player-wrapper">
			<video id="splms-html5-player-<?php echo esc_attr( $lesson_id ); ?>" class="splms-video-player" controls>
				<source src="<?php echo esc_url( $lesson_video_url ); ?>" type="video/mp4">
				<?php esc_html_e( 'Your browser does not support the video tag.', 'skillpulse-lms' ); ?>
			</video>
		</div>

		<!-- Video Controls Info -->
		<div class="splms-video-info">
			<div class="splms-video-progress-info">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
					<path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span class="splms-video-watch-percentage">0%</span>
				<span><?php esc_html_e( 'watched', 'skillpulse-lms' ); ?></span>
			</div>

			<?php if ( $completion_required < 100 ) : ?>
				<div class="splms-video-requirement-info">
					<?php
					printf(
						/* translators: %d: Required completion percentage */
						esc_html__( 'Watch at least %d%% to complete', 'skillpulse-lms' ),
						esc_html( $completion_required )
					);
					?>
				</div>
			<?php endif; ?>
		</div>

	<?php else : ?>
		<!-- Unsupported Video Type -->
		<div class="splms-video-error">
			<p><?php esc_html_e( 'Video format not supported.', 'skillpulse-lms' ); ?></p>
			<a href="<?php echo esc_url( $lesson_video_url ); ?>" target="_blank" class="splms-btn splms-btn-secondary">
				<?php esc_html_e( 'Open Video Link', 'skillpulse-lms' ); ?>
			</a>
		</div>
	<?php endif; ?>

</div>
