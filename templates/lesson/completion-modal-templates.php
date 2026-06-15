<?php
/**
 * Lesson Completion Modal Templates - JavaScript Templates for Course Completion
 *
 * This template contains the underscore.js template used for rendering the course completion modal.
 * Uses WordPress wp.template() system for proper integration with the SPLMSBaseModal class.
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/lesson/completion-modal-templates.php
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Course Completion Modal Template -->
<script type="text/html" id="tmpl-splms-completion-modal">
	<div class="modal-overlay"></div>
	<div class="modal-content">
		<!-- Fixed Header -->
		<div class="modal-header">
			<div class="modal-header-content">
				<div class="celebration-icon">🏆</div>
				<h2 id="completion-modal-title"><?php esc_html_e( 'Congratulations! Course Completed!', 'skillpulse-lms' ); ?></h2>
				<p class="modal-subtitle">
					<?php
					printf(
						/* translators: %s: Course title */
						esc_html__( 'You\'ve successfully completed %s and gained valuable skills. Well done on your achievement!', 'skillpulse-lms' ),
						'<strong>"{{{data.courseTitle}}}"</strong>'
					);
					?>
				</p>
			</div>
			<button type="button" class="modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'skillpulse-lms' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>

		<!-- Scrollable Content -->
		<div class="modal-body">
			<div class="modal-content-inner">
				<div class="completion-stats">
					<h3>📊 <?php esc_html_e( 'Course Statistics', 'skillpulse-lms' ); ?></h3>
					<div class="stats-grid">
						<div class="stat-item">
							<span class="stat-icon">✅</span>
							<span class="stat-value">{{{data.completedLessons}}}/{{{data.totalLessons}}}</span>
							<span class="stat-label"><?php esc_html_e( 'Lessons Completed', 'skillpulse-lms' ); ?></span>
						</div>
						<div class="stat-item">
							<span class="stat-icon">📅</span>
							<span class="stat-value">{{{data.completionDate}}}</span>
							<span class="stat-label"><?php esc_html_e( 'Completion Date', 'skillpulse-lms' ); ?></span>
						</div>
					</div>
				</div>

				<div class="recommendations">
					<h3>💡 <?php esc_html_e( 'Recommended Next Steps', 'skillpulse-lms' ); ?></h3>
					<ul class="recommendation-list">
						<li><?php esc_html_e( 'Share your achievement on social media', 'skillpulse-lms' ); ?></li>
						<li><?php esc_html_e( 'Explore advanced courses in this topic', 'skillpulse-lms' ); ?></li>
						<li><?php esc_html_e( 'Apply your new skills in real projects', 'skillpulse-lms' ); ?></li>
					</ul>
				</div>
			</div>
		</div>

		<!-- Fixed Footer (only shows if has actions) -->
		<# if (data.hasCertificate || data.showActions !== false) { #>
		<div class="modal-footer">
			<div class="modal-footer-content">
				<h3 class="footer-title">🎯 <?php esc_html_e( 'What\'s Next?', 'skillpulse-lms' ); ?></h3>
				<div class="action-buttons">
					<# if (data.hasCertificate) { #>
						<button type="button" class="splms-btn splms-btn-primary action-btn" data-action="download-certificate">
							<span class="btn-icon">🏆</span>
							<span class="btn-text"><?php esc_html_e( 'Download Certificate', 'skillpulse-lms' ); ?></span>
						</button>
					<# } #>
					<button type="button" class="splms-btn splms-btn-secondary action-btn" data-action="back-to-course">
						<span class="btn-icon">📚</span>
						<span class="btn-text"><?php esc_html_e( 'Back to Course', 'skillpulse-lms' ); ?></span>
					</button>
					<button type="button" class="splms-btn splms-btn-outline action-btn" data-action="goto-dashboard">
						<span class="btn-icon">🏠</span>
						<span class="btn-text"><?php esc_html_e( 'My Dashboard', 'skillpulse-lms' ); ?></span>
					</button>
				</div>
			</div>
		</div>
		<# } #>
	</div>
</script>