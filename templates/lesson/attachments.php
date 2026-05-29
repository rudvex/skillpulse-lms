<?php
/**
 * Lesson Attachments Component
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/lesson/attachments.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Get lesson attachments.
$splms_lesson_id        = get_the_ID();
$splms_lessons_instance = SPLMS_Lessons::get_instance();
$splms_attachments      = $splms_lessons_instance->get_formatted_lesson_attachments( $splms_lesson_id );

if ( empty( $splms_attachments ) ) {
	return;
}
?>

<div class="splms-lesson-attachments">
	<h3 class="splms-attachments-heading">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M21.44 11.05L12.25 20.24C11.1242 21.3658 9.59723 21.9983 8.005 21.9983C6.41277 21.9983 4.88579 21.3658 3.76 20.24C2.63421 19.1142 2.00166 17.5872 2.00166 15.995C2.00166 14.4028 2.63421 12.8758 3.76 11.75L12.33 3.18C13.0806 2.42943 14.0943 2.00675 15.1512 2.00675C16.2081 2.00675 17.2218 2.42943 17.9725 3.18C18.723 3.93057 19.1457 4.94428 19.1457 6.00116C19.1457 7.05804 18.723 8.07175 17.9725 8.82232L9.39 17.41C9.01472 17.7853 8.50784 17.9966 7.97875 17.9966C7.44966 17.9966 6.94278 17.7853 6.5675 17.41C6.19222 17.0347 5.98093 16.5278 5.98093 15.9987C5.98093 15.4696 6.19222 14.9628 6.5675 14.5875L14.29 6.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		<?php esc_html_e( 'Lesson Materials', 'skillpulse-lms' ); ?>
	</h3>

	<div class="splms-attachments-list">
		<?php foreach ( $splms_attachments as $splms_attachment ) : ?>
			<?php
			$splms_file_url   = isset( $splms_attachment['url'] ) ? $splms_attachment['url'] : '';
			$splms_file_title = isset( $splms_attachment['title'] ) ? $splms_attachment['title'] : '';
			$splms_file_label = isset( $splms_attachment['label'] ) ? $splms_attachment['label'] : '';
			$splms_file_type  = isset( $splms_attachment['type'] ) ? $splms_attachment['type'] : '';

			// Determine file icon based on type.
			$splms_file_icon = '';
			if ( false !== strpos( $splms_file_type, 'pdf' ) ) {
				$splms_file_icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="2"/><path d="M9 13H15M9 17H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
			} elseif ( false !== strpos( $splms_file_type, 'zip' ) || false !== strpos( $splms_file_type, 'archive' ) ) {
				$splms_file_icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2"/><path d="M7 10L12 15L17 10M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
			} elseif ( false !== strpos( $splms_file_type, 'image' ) ) {
				$splms_file_icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/><path d="M21 15L16 10L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
			} else {
				$splms_file_icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V9L13 2Z" stroke="currentColor" stroke-width="2"/><path d="M13 2V9H20" stroke="currentColor" stroke-width="2"/></svg>';
			}

			// Get file size if available.
			$splms_file_size = '';
			if ( isset( $splms_attachment['id'] ) && $splms_attachment['id'] ) {
				$splms_file_path = get_attached_file( $splms_attachment['id'] );
				if ( $splms_file_path && file_exists( $splms_file_path ) ) {
					$splms_file_size_bytes = filesize( $splms_file_path );
					$splms_file_size       = size_format( $splms_file_size_bytes, 2 );
				}
			}
			?>

			<a href="<?php echo esc_url( $splms_file_url ); ?>" class="splms-attachment-card" target="_blank" rel="noopener noreferrer" download>
				<div class="splms-attachment-icon">
					<?php echo wp_kses_post( $splms_file_icon ); ?>
				</div>
				<div class="splms-attachment-info">
					<div class="splms-attachment-title"><?php echo esc_html( $splms_file_title ); ?></div>
					<?php if ( $splms_file_label && $splms_file_label !== $splms_file_title ) : ?>
						<div class="splms-attachment-label"><?php echo esc_html( $splms_file_label ); ?></div>
					<?php endif; ?>
					<?php if ( $splms_file_size ) : ?>
						<div class="splms-attachment-size"><?php echo esc_html( $splms_file_size ); ?></div>
					<?php endif; ?>
				</div>
				<div class="splms-attachment-download">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M7 10L12 15L17 10M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
			</a>

		<?php endforeach; ?>
	</div>
</div>
