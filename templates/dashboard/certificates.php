<?php
/**
 * Certificates Template
 *
 * This template can be overridden by copying it to:
 * your-theme/skillpulse-lms/dashboard/certificates.php
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Extract variables from args.
// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template file uses extract for convenience.
extract( $args );

// Get user certificates.
$splms_user_certificates = array();
if ( class_exists( 'SkillPulse_LMS_Certificate_Display' ) && class_exists( 'SkillPulse_LMS_Enrollments_Query' ) ) {
	$splms_enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
	$splms_enrolled_courses  = $splms_enrollments_query->get_user_courses( $splms_user_id );

	foreach ( $splms_enrolled_courses as $splms_enrollment ) {
		$splms_course_id = $splms_enrollment->course_id ?? $splms_enrollment['course_id'];

		// Check if course is completed.
		$splms_progress = 0;
		if ( class_exists( 'SkillPulse_LMS_Dashboard_API' ) ) {
			$splms_dashboard_api = SkillPulse_LMS_Dashboard_API::get_instance();
			$splms_progress_data = $splms_dashboard_api->get_course_progress( $splms_course_id, $splms_user_id );
			$splms_progress      = $splms_progress_data['percentage'] ?? 0;
		}

		if ( $splms_progress >= 100 ) {
			$splms_course_post = get_post( $splms_course_id );
			if ( $splms_course_post ) {
				$splms_user_certificates[] = array(
					'course_id'       => $splms_course_id,
					'course_title'    => $splms_course_post->post_title,
					'completion_date' => gmdate( 'F j, Y' ), // You might want to get actual completion date.
					'certificate_id'  => 'CERT-' . strtoupper( substr( md5( $splms_course_id . $splms_user_id ), 0, 8 ) ),
					'user_id'         => $splms_user_id,
					'instructor_name' => get_the_author_meta( 'display_name', $splms_course_post->post_author ),
				);
			}
		}
	}
}
?>
<div class="splms-dashboard-tab splms-certificates-tab">

	<!-- Page Header -->
	<div class="splms-section-title" style="margin-bottom: 2rem;">
		<i class="hgi-stroke hgi-certificate-01"></i>
		<?php esc_html_e( 'My Certificates', 'skillpulse-lms' ); ?>
	</div>

	<!-- Certificates Content -->
	<div class="splms-certificates-content">
		<?php if ( ! empty( $splms_user_certificates ) ) : ?>
			<!-- Simple summary -->
			<div class="splms-courses-summary">
				<p class="splms-summary-text">
					<?php
					printf(
						/* translators: %d: number of certificates */
						esc_html__( 'You have earned %d certificates', 'skillpulse-lms' ),
						count( $splms_user_certificates )
					);
					?>
				</p>
			</div>

			<div class="splms-course-list">
				<?php
				$splms_course_colors = array( '#4F46E5', '#EC4899', '#10B981', '#F59E0B', '#8B5CF6', '#06B6D4' );
				?>
				<?php
				foreach ( $splms_user_certificates as $splms_index => $splms_certificate ) :
					$splms_color = $splms_course_colors[ $splms_index % count( $splms_course_colors ) ];
					?>
					<div class="splms-course-card"
						data-course-id="<?php echo esc_attr( $splms_certificate['course_id'] ); ?>"
						data-user-id="<?php echo esc_attr( $splms_certificate['user_id'] ); ?>"
						data-certificate-id="<?php echo esc_attr( $splms_certificate['certificate_id'] ); ?>">

						<div class="splms-course-icon" style="color: <?php echo esc_attr( $splms_color ); ?>; background-color: <?php echo esc_attr( $splms_color ); ?>20;">
							<i class="hgi-stroke hgi-certificate-01"></i>
						</div>

						<div class="splms-course-info">
							<div class="splms-course-header">
								<h3 class="splms-course-title"><?php echo esc_html( $splms_certificate['course_title'] ); ?></h3>
								<span class="splms-enrollment-status-badge splms-enrollment-status-completed">
									<?php esc_html_e( 'Earned', 'skillpulse-lms' ); ?>
								</span>
							</div>

							<div class="splms-certificate-meta">
								<p class="splms-course-excerpt">
									<?php
									printf(
										/* translators: %1$s: certificate ID, %2$s: completion date */
										esc_html__( 'Certificate ID: %1$s • Completed on %2$s', 'skillpulse-lms' ),
										esc_html( $splms_certificate['certificate_id'] ),
										esc_html( $splms_certificate['completion_date'] )
									);
									?>
									<?php if ( $splms_certificate['instructor_name'] ) : ?>
										<?php
										printf(
											/* translators: %s: instructor name */
											esc_html__( ' • Instructor: %s', 'skillpulse-lms' ),
											esc_html( $splms_certificate['instructor_name'] )
										);
										?>
									<?php endif; ?>
								</p>
							</div>
						</div>

						<div class="splms-course-actions">
							<button type="button"
									class="splms-btn splms-btn-primary certificate-print"
									data-course-id="<?php echo esc_attr( $splms_certificate['course_id'] ); ?>"
									data-user-id="<?php echo esc_attr( $splms_certificate['user_id'] ); ?>"
									title="<?php esc_attr_e( 'Print Certificate', 'skillpulse-lms' ); ?>">
								<?php esc_html_e( 'Print', 'skillpulse-lms' ); ?>
								<i class="hgi-stroke hgi-printer"></i>
							</button>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<!-- Empty State -->
			<div class="splms-dashboard-empty-state">
				<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<h3><?php esc_html_e( 'No Certificates Yet', 'skillpulse-lms' ); ?></h3>
				<p><?php esc_html_e( 'Complete your courses to earn certificates!', 'skillpulse-lms' ); ?></p>
			</div>
		<?php endif; ?>
	</div>

	<!-- Certificate Preview Modal -->
	<div id="splms-certificate-modal" class="splms-modal splms-certificate-preview-modal" style="display: none;">
		<div class="splms-modal-overlay"></div>
		<div class="splms-modal-content">
			<div class="splms-modal-header">
				<h3><?php esc_html_e( 'Certificate Preview', 'skillpulse-lms' ); ?></h3>
				<button type="button" class="splms-modal-close" aria-label="<?php esc_attr_e( 'Close preview', 'skillpulse-lms' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			</div>
			<div class="splms-modal-body">
				<div id="splms-certificate-preview-content" class="splms-certificate-preview-content">
					<!-- Certificate content will be loaded here -->
				</div>
			</div>
			<div class="splms-modal-footer">
				<button type="button" class="splms-btn splms-btn-primary certificate-print-modal">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<polyline points="6,9 6,2 18,2 18,9" stroke="currentColor" stroke-width="2" fill="none"/>
						<path d="M6,18H4a2,2 0,0 1,-2-2v-5a2,2 0,0 1,2-2H20a2,2 0,0 1,2,2v5a2,2 0,0 1,-2,2H18" stroke="currentColor" stroke-width="2" fill="none"/>
						<rect x="6" y="14" width="12" height="8" stroke="currentColor" stroke-width="2" fill="none"/>
					</svg>
					<?php esc_html_e( 'Print Certificate', 'skillpulse-lms' ); ?>
				</button>
				<button type="button" class="splms-btn splms-btn-secondary certificate-share-modal">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/>
						<circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
						<circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/>
						<line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="currentColor" stroke-width="2"/>
						<line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="currentColor" stroke-width="2"/>
					</svg>
					<?php esc_html_e( 'Share', 'skillpulse-lms' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Templates -->
<script type="text/html" id="tmpl-splms-dashboard-certificates">
	<div class="splms-certificates-list">
		<# _.each( data, function( certificate ) { #>
			<div class="splms-certificate-item" data-course-id="{{{ certificate.course_id }}}" data-user-id="{{{ certificate.user_id }}}" data-certificate-id="{{{ certificate.certificate_id }}}">
				<div class="splms-certificate-thumbnail">
					<div class="splms-certificate-icon">
						<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M12 18l-3-3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M16 18l3-3-3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<# if ( certificate.completion_date ) { #>
						<div class="splms-certificate-earned-badge">
							<?php esc_html_e( 'Earned', 'skillpulse-lms' ); ?>
						</div>
					<# } #>
				</div>

				<div class="splms-certificate-content">
					<div class="splms-certificate-main">
						<h3 class="splms-certificate-title">{{{ certificate.course_title }}}</h3>
						<div class="splms-certificate-meta">
							<# if ( certificate.certificate_id ) { #>
								<span class="splms-certificate-id">{{{ certificate.certificate_id }}}</span>
							<# } #>
							<# if ( certificate.instructor_name ) { #>
								<span class="splms-certificate-instructor"><?php esc_html_e( 'by', 'skillpulse-lms' ); ?> {{{ certificate.instructor_name }}}</span>
							<# } #>
						</div>
					</div>

					<# if ( certificate.completion_date ) { #>
						<div class="splms-certificate-date">
							<time class="splms-certificate-date-value">{{{ certificate.completion_date }}}</time>
						</div>
					<# } #>
				</div>

				<div class="splms-certificate-actions">
					<button type="button" class="splms-btn splms-btn-icon certificate-print" data-course-id="{{{ certificate.course_id }}}" data-user-id="{{{ certificate.user_id }}}" title="<?php esc_attr_e( 'Print Certificate', 'skillpulse-lms' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<polyline points="6,9 6,2 18,2 18,9" stroke="currentColor" stroke-width="2" fill="none"/>
							<path d="M6,18H4a2,2 0,0 1,-2-2v-5a2,2 0,0 1,2-2H20a2,2 0,0 1,2,2v5a2,2 0,0 1,-2,2H18" stroke="currentColor" stroke-width="2" fill="none"/>
							<rect x="6" y="14" width="12" height="8" stroke="currentColor" stroke-width="2" fill="none"/>
						</svg>
					</button>
				</div>
			</div>
		<# }); #>
	</div>
</script>

<script type="text/html" id="tmpl-splms-dashboard-empty-state">
	<div class="splms-dashboard-empty-state">
		<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="{{{ data.svgPath }}}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		<h3>{{{ data.title }}}</h3>
		<p>{{{ data.message }}}</p>
	</div>
</script>


