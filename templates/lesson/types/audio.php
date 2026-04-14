<?php
/**
 * Lesson Type: Audio
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/lesson/types/audio.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get passed variables.
$lesson_audio_url = isset( $args['lesson_audio_url'] ) ? $args['lesson_audio_url'] : '';

if ( ! $lesson_audio_url ) {
	return;
}

// Get MIME type.
$parsed_url = wp_parse_url( $lesson_audio_url );
$audio_path = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
$file_ext   = strtolower( pathinfo( $audio_path, PATHINFO_EXTENSION ) );
$host       = isset( $parsed_url['host'] ) ? strtolower( $parsed_url['host'] ) : '';

// Check for SoundCloud embed.
if ( false !== strpos( $host, 'soundcloud.com' ) ) {
	?>
	<div class="splms-audio-player-container">
		<iframe width="100%" height="166" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=<?php echo esc_url( rawurlencode( $lesson_audio_url ) ); ?>&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true"></iframe>
	</div>
	<?php
	return;
}

// Determine MIME type for direct audio files.
$mime_type = 'audio/mpeg'; // Default.
switch ( $file_ext ) {
	case 'mp3':
		$mime_type = 'audio/mpeg';
		break;
	case 'm4a':
		$mime_type = 'audio/mp4';
		break;
	case 'aac':
		$mime_type = 'audio/aac';
		break;
	case 'ogg':
	case 'oga':
		$mime_type = 'audio/ogg';
		break;
	case 'wav':
		$mime_type = 'audio/wav';
		break;
	case 'flac':
		$mime_type = 'audio/flac';
		break;
	case 'wma':
		$mime_type = 'audio/x-ms-wma';
		break;
}
?>

<div class="splms-audio-player-container">
	<audio controls preload="metadata" crossorigin="anonymous" style="width: 100%;">
		<source src="<?php echo esc_url( $lesson_audio_url ); ?>" type="<?php echo esc_attr( $mime_type ); ?>">
		<?php esc_html_e( 'Your browser does not support the audio player.', 'skillpulse-lms' ); ?>
	</audio>
</div>
