<?php
/**
 * My Courses Template
 *
 * This template can be overridden by copying it to:
 * your-theme/skillpulse-lms/dashboard/courses.php
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

// Get user enrollments and calculate progress stats.
$enrolled_courses     = array();
$completed_courses    = 0;
$in_progress_courses  = 0;
$course_progress_data = array();

if ( class_exists( 'SkillPulse_LMS_Enrollments_Query' ) ) {
	$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
	$enrolled_courses  = $enrollments_query->get_user_courses( $user_id );

	// Calculate progress statistics.
	foreach ( $enrolled_courses as $enrollment ) {
		$course_id   = $enrollment->course_id ?? $enrollment['course_id'];
		$course_post = get_post( $course_id );

		if ( ! $course_post ) {
			continue;
		}

		$progress = 0;
		if ( class_exists( 'SkillPulse_LMS_Dashboard_API' ) ) {
			$dashboard_api = SkillPulse_LMS_Dashboard_API::get_instance();
			$progress_data = $dashboard_api->get_course_progress( $course_id, $user_id );
			$progress      = $progress_data['percentage'] ?? 0;
		}

		if ( $progress >= 100 ) {
			++$completed_courses;
		} elseif ( $progress > 0 ) {
			++$in_progress_courses;
		}
	}
}

$total_enrolled  = count( $enrolled_courses );
$completion_rate = $total_enrolled > 0 ? round( ( $completed_courses / $total_enrolled ) * 100, 1 ) : 0;
?>
<div class="splms-dashboard-tab splms-courses-tab">

	<!-- Page Header -->
	<div class="splms-section-title" style="margin-bottom: 2rem;">
		<i class="hgi-stroke hgi-book-open-01"></i>
		<?php esc_html_e( 'My Courses', 'skillpulse-lms' ); ?>
	</div>

	<!-- Progress Statistics Summary -->
	<?php if ( ! empty( $enrolled_courses ) ) : ?>
		<div class="splms-courses-summary splms-progress-summary">
			<div class="splms-summary-stats">
				<div class="splms-stat-item">
					<span class="splms-stat-number"><?php echo esc_html( $total_enrolled ); ?></span>
					<span class="splms-stat-label"><?php esc_html_e( 'Enrolled', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="splms-stat-item">
					<span class="splms-stat-number"><?php echo esc_html( $completed_courses ); ?></span>
					<span class="splms-stat-label"><?php esc_html_e( 'Completed', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="splms-stat-item">
					<span class="splms-stat-number"><?php echo esc_html( $in_progress_courses ); ?></span>
					<span class="splms-stat-label"><?php esc_html_e( 'In Progress', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="splms-stat-item">
					<span class="splms-stat-number"><?php echo esc_html( $completion_rate ); ?>%</span>
					<span class="splms-stat-label"><?php esc_html_e( 'Completion Rate', 'skillpulse-lms' ); ?></span>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!-- Courses Content -->
	<div class="splms-courses-content">
		<?php if ( ! empty( $enrolled_courses ) ) : ?>
			<div class="splms-course-list">
				<?php
				$course_colors = array( '#4F46E5', '#EC4899', '#10B981', '#F59E0B', '#8B5CF6', '#06B6D4' );
				$course_icons  = array( 'hgi-computer-check', 'hgi-paint-board', 'hgi-book-open-01', 'hgi-code-circle', 'hgi-database-01', 'hgi-graduation-hat-01' );
				?>

				<?php
				foreach ( $enrolled_courses as $index => $enrollment ) :
					$course_id   = $enrollment->course_id ?? $enrollment['course_id'];
					$course_post = get_post( $course_id );

					if ( ! $course_post ) {
						continue;
					}

					// Get course progress.
					$course_progress = 0;
					if ( class_exists( 'SkillPulse_LMS_Dashboard_API' ) ) {
						$dashboard_api   = SkillPulse_LMS_Dashboard_API::get_instance();
						$progress_data   = $dashboard_api->get_course_progress( $course_id, $user_id );
						$course_progress = $progress_data['percentage'] ?? 0;
					}

					$color             = $course_colors[ $index % count( $course_colors ) ];
					$icon              = $course_icons[ $index % count( $course_icons ) ];
					$enrollment_status = $enrollment->status ?? 'active';
					$enrollment_date   = $enrollment->created_at ?? '';

					// Determine progress status.
					$progress_status = 'not_started';
					$progress_label  = __( 'Not Started', 'skillpulse-lms' );

					if ( $course_progress >= 100 ) {
						$progress_status = 'completed';
						$progress_label  = __( 'Completed', 'skillpulse-lms' );
					} elseif ( $course_progress > 0 ) {
						$progress_status = 'in_progress';
						$progress_label  = __( 'In Progress', 'skillpulse-lms' );
					}
					?>
					<div class="splms-course-card" data-status="<?php echo esc_attr( $progress_status ); ?>">
						<div class="splms-course-icon" style="color: <?php echo esc_attr( $color ); ?>; background-color: <?php echo esc_attr( $color ); ?>20;">
							<i class="hgi-stroke <?php echo esc_attr( $icon ); ?>"></i>
						</div>
						<div class="splms-course-info">
							<div class="splms-course-header">
								<h3 class="splms-course-title"><?php echo esc_html( $course_post->post_title ); ?></h3>
								<div class="splms-course-badges">
									<span class="splms-progress-badge splms-progress-<?php echo esc_attr( $progress_status ); ?>">
										<?php echo esc_html( $progress_label ); ?>
									</span>
								</div>
							</div>

							<?php if ( $course_post->post_excerpt ) : ?>
								<p class="splms-course-excerpt"><?php echo esc_html( wp_trim_words( $course_post->post_excerpt, 20 ) ); ?></p>
							<?php endif; ?>

							<div class="splms-progress-bar">
								<div class="splms-progress-fill" style="width: <?php echo esc_attr( $course_progress ); ?>%; background-color: <?php echo esc_attr( $color ); ?>;"></div>
							</div>
							<div class="splms-progress-text">
								<?php
								printf(
									/* translators: %1$d: progress percentage */
									esc_html__( '%1$d%% Completed', 'skillpulse-lms' ),
									(int) $course_progress
								);
								?>
								<?php if ( $enrollment_date ) : ?>
									<span class="splms-enrollment-date">
										<?php
										printf(
											/* translators: %s: enrollment date */
											esc_html__( ' • Enrolled %s', 'skillpulse-lms' ),
											esc_html( human_time_diff( strtotime( $enrollment_date ) ) . ' ago' )
										);
										?>
									</span>
								<?php endif; ?>
							</div>
						</div>
						<div class="splms-course-actions">
							<a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>" class="splms-btn splms-btn-primary">
								<?php echo ( $course_progress > 0 ) ? esc_html__( 'Continue', 'skillpulse-lms' ) : esc_html__( 'Start Learning', 'skillpulse-lms' ); ?>
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
