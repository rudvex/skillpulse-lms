<?php
/**
 * Lesson Type: Audio
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/lesson/types/audio.php
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Get passed variables.
$splms_lesson_audio_url = isset( $args['splms_lesson_audio_url'] ) ? $args['splms_lesson_audio_url'] : '';

if ( ! $splms_lesson_audio_url ) {
	return;
}

// Get MIME type.
$splms_parsed_url = wp_parse_url( $splms_lesson_audio_url );
$splms_audio_path = isset( $splms_parsed_url['path'] ) ? $splms_parsed_url['path'] : '';
$splms_file_ext   = strtolower( pathinfo( $splms_audio_path, PATHINFO_EXTENSION ) );
$splms_host       = isset( $splms_parsed_url['host'] ) ? strtolower( $splms_parsed_url['host'] ) : '';

// Check for SoundCloud embed.
if ( false !== strpos( $splms_host, 'soundcloud.com' ) ) {
	?>
	<div class="splms-audio-player-container">
		<iframe width="100%" height="166" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=<?php echo esc_url( rawurlencode( $splms_lesson_audio_url ) ); ?>&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true"></iframe>
	</div>
	<?php
	return;
}

// Determine MIME type for direct audio files.
$splms_mime_type = 'audio/mpeg'; // Default.
switch ( $splms_file_ext ) {
	case 'mp3':
		$splms_mime_type = 'audio/mpeg';
		break;
	case 'm4a':
		$splms_mime_type = 'audio/mp4';
		break;
	case 'aac':
		$splms_mime_type = 'audio/aac';
		break;
	case 'ogg':
	case 'oga':
		$splms_mime_type = 'audio/ogg';
		break;
	case 'wav':
		$splms_mime_type = 'audio/wav';
		break;
	case 'flac':
		$splms_mime_type = 'audio/flac';
		break;
	case 'wma':
		$splms_mime_type = 'audio/x-ms-wma';
		break;
}
?>

<div class="splms-audio-player-container">
	<audio controls preload="metadata" crossorigin="anonymous" style="width: 100%;">
		<source src="<?php echo esc_url( $splms_lesson_audio_url ); ?>" type="<?php echo esc_attr( $splms_mime_type ); ?>">
		<?php esc_html_e( 'Your browser does not support the audio player.', 'skillpulse-lms' ); ?>
	</audio>
</div>
