<?php
/**
 * Lesson Type: Document
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/lesson/types/document.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get passed variables.
$lesson_id           = isset( $args['lesson_id'] ) ? $args['lesson_id'] : get_the_ID();
$lesson_document_url = isset( $args['lesson_document_url'] ) ? $args['lesson_document_url'] : '';

if ( ! $lesson_document_url ) {
	return;
}

// Parse URL to determine document type.
$parsed_url = wp_parse_url( $lesson_document_url );
$host       = isset( $parsed_url['host'] ) ? strtolower( $parsed_url['host'] ) : '';
$url_path   = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
$file_ext   = strtolower( pathinfo( $url_path, PATHINFO_EXTENSION ) );

// Determine document type.
$document_type = 'downloadable';

// Check for Google Docs/Slides/Sheets.
if ( false !== strpos( $host, 'docs.google.com' ) || false !== strpos( $host, 'drive.google.com' ) ) {
	$document_type = 'google_docs';
}

// Check for images.
$image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico' );
if ( in_array( $file_ext, $image_extensions, true ) ) {
	$document_type = 'image';
}

// Check for PDF.
if ( 'pdf' === $file_ext ) {
	$document_type = 'pdf';
}
?>

<div class="splms-document-viewer-container">
	<?php if ( 'image' === $document_type ) : ?>
		<!-- Image Preview -->
		<div class="splms-lesson-document__image-wrapper splms-doc-image">
			<img src="<?php echo esc_url( $lesson_document_url ); ?>" alt="<?php esc_attr_e( 'Document image', 'skillpulse-lms' ); ?>" class="splms-lesson-document__image" />
		</div>

	<?php elseif ( 'pdf' === $document_type ) : ?>
		<!-- PDF Viewer -->
		<div class="splms-lesson-document__pdf-wrapper">
			<iframe src="<?php echo esc_url( $lesson_document_url ); ?>" class="splms-lesson-document__iframe" frameborder="0"></iframe>
		</div>

	<?php elseif ( 'google_docs' === $document_type ) : ?>
		<!-- Google Docs Viewer -->
		<?php
		// Convert Google Docs/Sheets/Slides to embeddable format.
		$embed_url = $lesson_document_url;
		if ( false !== strpos( $embed_url, '/edit' ) ) {
			$embed_url = str_replace( '/edit', '/preview', $embed_url );
		}
		if ( false !== strpos( $embed_url, '?usp=sharing' ) ) {
			$embed_url = str_replace( '?usp=sharing', '', $embed_url );
		}
		if ( false === strpos( $embed_url, '/view' ) && false === strpos( $embed_url, '/preview' ) ) {
			$embed_url = rtrim( $embed_url, '/' ) . '/preview';
		}
		?>
		<div class="splms-lesson-document__google-wrapper">
			<iframe src="<?php echo esc_url( $embed_url ); ?>" class="splms-lesson-document__iframe" frameborder="0" allowfullscreen></iframe>
		</div>

	<?php else : ?>
		<!-- Downloadable Document -->
		<div class="splms-lesson-document__download-wrapper splms-doc-download">
			<div class="splms-lesson-document__preview-placeholder">
				<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="splms-lesson-document__placeholder-icon">
					<path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<p><?php esc_html_e( 'This document is not previewable. Please download to view.', 'skillpulse-lms' ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<!-- Download Button (always shown) -->
	<div class="splms-lesson-document__download">
		<a href="<?php echo esc_url( $lesson_document_url ); ?>" class="splms-button splms-button--secondary" target="_blank" download>
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M21 15V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V5C3 3.89543 3.89543 3 5 3H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M18 3H21V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M10 14L21 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<?php esc_html_e( 'Download Document', 'skillpulse-lms' ); ?>
		</a>
	</div>
</div>
