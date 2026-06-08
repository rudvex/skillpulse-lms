<?php
/**
 * My Courses Template
 *
 * This template can be overridden by copying it to:
 * your-theme/skillpulse-lms/dashboard/courses.php
 *
 * @package SPLMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Extract variables from args.
// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template file uses extract for convenience.
extract( $args );

// Get user enrollments and calculate progress stats.
$splms_enrolled_courses     = array();
$splms_completed_courses    = 0;
$splms_in_progress_courses  = 0;
$splms_course_progress_data = array();

if ( class_exists( 'SPLMS_Enrollments_Query' ) ) {
	$splms_enrollments_query = SPLMS_Enrollments_Query::get_instance();
	$splms_enrolled_courses  = $splms_enrollments_query->get_user_courses( $splms_user_id );

	// Calculate progress statistics.
	foreach ( $splms_enrolled_courses as $splms_enrollment ) {
		$splms_course_id   = $splms_enrollment->course_id ?? $splms_enrollment['course_id'];
		$splms_course_post = get_post( $splms_course_id );

		if ( ! $splms_course_post ) {
			continue;
		}

		$splms_progress = 0;
		if ( class_exists( 'SPLMS_Dashboard_API' ) ) {
			$splms_dashboard_api = SPLMS_Dashboard_API::get_instance();
			$splms_progress_data = $splms_dashboard_api->get_course_progress( $splms_course_id, $splms_user_id );
			$splms_progress      = $splms_progress_data['percentage'] ?? 0;
		}

		if ( $splms_progress >= 100 ) {
			++$splms_completed_courses;
		} elseif ( $splms_progress > 0 ) {
			++$splms_in_progress_courses;
		}
	}
}

$splms_total_enrolled  = count( $splms_enrolled_courses );
$splms_completion_rate = $splms_total_enrolled > 0 ? round( ( $splms_completed_courses / $splms_total_enrolled ) * 100, 1 ) : 0;
?>
<div class="splms-dashboard-tab splms-courses-tab">

	<!-- Page Header -->
	<div class="splms-section-title" style="margin-bottom: 2rem;">
		<i class="hgi-stroke hgi-book-open-01"></i>
		<?php esc_html_e( 'My Courses', 'skillpulse-lms' ); ?>
	</div>

	<!-- Progress Statistics Summary -->
	<?php if ( ! empty( $splms_enrolled_courses ) ) : ?>
		<div class="splms-courses-summary splms-progress-summary">
			<div class="splms-summary-stats">
				<div class="splms-stat-item">
					<span class="splms-stat-number"><?php echo esc_html( $splms_total_enrolled ); ?></span>
					<span class="splms-stat-label"><?php esc_html_e( 'Enrolled', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="splms-stat-item">
					<span class="splms-stat-number"><?php echo esc_html( $splms_completed_courses ); ?></span>
					<span class="splms-stat-label"><?php esc_html_e( 'Completed', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="splms-stat-item">
					<span class="splms-stat-number"><?php echo esc_html( $splms_in_progress_courses ); ?></span>
					<span class="splms-stat-label"><?php esc_html_e( 'In Progress', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="splms-stat-item">
					<span class="splms-stat-number"><?php echo esc_html( $splms_completion_rate ); ?>%</span>
					<span class="splms-stat-label"><?php esc_html_e( 'Completion Rate', 'skillpulse-lms' ); ?></span>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!-- Courses Content -->
	<div class="splms-courses-content">
		<?php if ( ! empty( $splms_enrolled_courses ) ) : ?>
			<div class="splms-course-list">
				<?php
				$splms_course_colors = array( '#4F46E5', '#EC4899', '#10B981', '#F59E0B', '#8B5CF6', '#06B6D4' );
				$splms_course_icons  = array( 'hgi-computer-check', 'hgi-paint-board', 'hgi-book-open-01', 'hgi-code-circle', 'hgi-database-01', 'hgi-graduation-hat-01' );
				?>

				<?php
				foreach ( $splms_enrolled_courses as $splms_index => $splms_enrollment ) :
					$splms_course_id   = $splms_enrollment->course_id ?? $splms_enrollment['course_id'];
					$splms_course_post = get_post( $splms_course_id );

					if ( ! $splms_course_post ) {
						continue;
					}

					// Get course progress.
					$splms_course_progress = 0;
					if ( class_exists( 'SPLMS_Dashboard_API' ) ) {
						$splms_dashboard_api   = SPLMS_Dashboard_API::get_instance();
						$splms_progress_data   = $splms_dashboard_api->get_course_progress( $splms_course_id, $splms_user_id );
						$splms_course_progress = $splms_progress_data['percentage'] ?? 0;
					}

					$splms_color             = $splms_course_colors[ $splms_index % count( $splms_course_colors ) ];
					$splms_icon              = $splms_course_icons[ $splms_index % count( $splms_course_icons ) ];
					$splms_enrollment_status = $splms_enrollment->status ?? 'active';
					$splms_enrollment_date   = $splms_enrollment->created_at ?? '';

					// Determine progress status.
					$splms_progress_status = 'not_started';
					$splms_progress_label  = __( 'Not Started', 'skillpulse-lms' );

					if ( $splms_course_progress >= 100 ) {
						$splms_progress_status = 'completed';
						$splms_progress_label  = __( 'Completed', 'skillpulse-lms' );
					} elseif ( $splms_course_progress > 0 ) {
						$splms_progress_status = 'in_progress';
						$splms_progress_label  = __( 'In Progress', 'skillpulse-lms' );
					}
					?>
					<div class="splms-course-card" data-status="<?php echo esc_attr( $splms_progress_status ); ?>">
						<div class="splms-course-icon" style="color: <?php echo esc_attr( $splms_color ); ?>; background-color: <?php echo esc_attr( $splms_color ); ?>20;">
							<i class="hgi-stroke <?php echo esc_attr( $splms_icon ); ?>"></i>
						</div>
						<div class="splms-course-info">
							<div class="splms-course-header">
								<h3 class="splms-course-title"><?php echo esc_html( $splms_course_post->post_title ); ?></h3>
								<div class="splms-course-badges">
									<span class="splms-progress-badge splms-progress-<?php echo esc_attr( $splms_progress_status ); ?>">
										<?php echo esc_html( $splms_progress_label ); ?>
									</span>
								</div>
							</div>

							<?php if ( $splms_course_post->post_excerpt ) : ?>
								<p class="splms-course-excerpt"><?php echo esc_html( wp_trim_words( $splms_course_post->post_excerpt, 20 ) ); ?></p>
							<?php endif; ?>

							<div class="splms-progress-bar">
								<div class="splms-progress-fill" style="width: <?php echo esc_attr( $splms_course_progress ); ?>%; background-color: <?php echo esc_attr( $splms_color ); ?>;"></div>
							</div>
							<div class="splms-progress-text">
								<?php
								printf(
									/* translators: %1$d: progress percentage */
									esc_html__( '%1$d%% Completed', 'skillpulse-lms' ),
									(int) $splms_course_progress
								);
								?>
								<?php if ( $splms_enrollment_date ) : ?>
									<span class="splms-enrollment-date">
										<?php
										printf(
											/* translators: %s: enrollment date */
											esc_html__( ' • Enrolled %s', 'skillpulse-lms' ),
											esc_html( human_time_diff( strtotime( $splms_enrollment_date ) ) . ' ago' )
										);
										?>
									</span>
								<?php endif; ?>
							</div>
						</div>
						<div class="splms-course-actions">
							<a href="<?php echo esc_url( get_permalink( $splms_course_id ) ); ?>" class="splms-btn splms-btn-primary">
								<?php echo ( $splms_course_progress > 0 ) ? esc_html__( 'Continue', 'skillpulse-lms' ) : esc_html__( 'Start Learning', 'skillpulse-lms' ); ?>
								<i class="hgi-stroke hgi-arrow-right-01"></i>
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<!-- Empty State -->
			<div class="splms-dashboard-empty-state">
				<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<h3><?php esc_html_e( 'No Courses Yet', 'skillpulse-lms' ); ?></h3>
				<p><?php esc_html_e( 'You haven\'t enrolled in any courses yet. Browse our course catalog to get started!', 'skillpulse-lms' ); ?></p>
				<a href="<?php echo esc_url( get_post_type_archive_link( defined( 'SPLMS_POST_TYPES' ) ? SPLMS_POST_TYPES['course'] : 'sp-course' ) ); ?>" class="splms-btn splms-btn-primary">
					<?php esc_html_e( 'Browse Courses', 'skillpulse-lms' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</div>
