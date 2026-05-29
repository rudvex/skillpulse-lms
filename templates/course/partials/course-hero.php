<?php
/**
 * Single course hero template part
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/single-course-hero.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



$splms_course_id               = get_the_ID();
$splms_user_id                 = get_current_user_id();
$splms_difficulty              = splms_get_course_difficulty( $splms_course_id );
$splms_access_info             = splms_get_course_access_info( $splms_course_id, $splms_user_id );
$splms_duration                = splms_get_course_duration( $splms_course_id );
$splms_students_count          = splms_get_course_enrollment_count( $splms_course_id );
$splms_rating_summary          = splms_get_course_rating( $splms_course_id );
$splms_section_pricing_enabled = splms_get_setting( 'enable_section_based_pricing', false );
$splms_course_access_type      = ! empty( $splms_access_info['course_access_type'] ) ? $splms_access_info['course_access_type'] : 'public_free';

$splms_is_enrolled       = splms_is_user_enrolled( $splms_course_id );
$splms_enrollment_status = splms_get_user_enrollment_status( $splms_course_id );

// Get enrollment and capacity info for button states.
$splms_enrollment_dates = splms_get_course_enrollment_dates( $splms_course_id );
$splms_capacity_info    = splms_get_course_max_enrollment_info( $splms_course_id );

// Get progress data for enrolled users.
$splms_user_progress      = array();
$splms_next_lesson        = null;
$splms_recently_completed = array();
$splms_last_activity_time = null;

if ( $splms_is_enrolled && $splms_user_id ) {
	// Calculate progress from database tables.
	$splms_lessons_instance = SPLMS_Lessons::get_instance();
	if ( $splms_lessons_instance ) {
		$splms_progress_data = $splms_lessons_instance->calculate_course_progress( $splms_user_id, $splms_course_id );

		$splms_user_progress = array(
			'percentage'        => $splms_progress_data['percentage'],
			'completed_lessons' => $splms_progress_data['completed_lessons'] + $splms_progress_data['passed_quizzes'],
			'total_lessons'     => $splms_progress_data['total_items'],
		);
	}

	// Get course curriculum using unified method.
	$splms_curriculum_result = splms_get_course_curriculum( $splms_course_id, $splms_user_id );

	// Find next incomplete lesson and collect completed items.
	if ( isset( $splms_curriculum_result['sections'] ) ) {
		foreach ( $splms_curriculum_result['sections'] as $splms_section ) {
			foreach ( $splms_section['children'] as $splms_child ) {
				$splms_is_completed = isset( $splms_child['completed'] ) ? $splms_child['completed'] : false;

				// Find first incomplete item.
				if ( ! $splms_is_completed && ! $splms_next_lesson ) {
					$splms_next_lesson = array(
						'id'            => $splms_child['id'],
						'title'         => $splms_child['title'],
						'type'          => $splms_child['type'],
						'permalink'     => $splms_child['permalink'],
						'section_title' => $splms_section['title'],
					);
				}

				// Collect completed items for recently completed section.
				if ( $splms_is_completed ) {
					$splms_recently_completed[] = array(
						'id'        => $splms_child['id'],
						'title'     => $splms_child['title'],
						'type'      => $splms_child['type'],
						'permalink' => $splms_child['permalink'],
					);
				}
			}
		}
	}

	// Get last 3 recently completed (reverse order to show most recent first).
	$splms_recently_completed = array_slice( array_reverse( $splms_recently_completed ), 0, 3 );

	// Get last activity time (from lesson progress table).
	if ( $splms_lessons_instance ) {
		global $wpdb;
		$splms_progress_table = esc_sql( $wpdb->prefix . 'splms_lesson_progress' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Performance-critical query.
		$splms_last_activity = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safely constructed with $wpdb->prefix.
				"SELECT MAX(completed_at) FROM {$splms_progress_table} WHERE user_id = %d AND course_id = %d",
				$splms_user_id,
				$splms_course_id
			)
		);

		if ( $splms_last_activity ) {
			$splms_last_activity_time = strtotime( $splms_last_activity );
		}
	}
}

// Get current tab.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Template file, nonce verification handled at higher level.
$splms_current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'overview';

// Get course categories.
$splms_categories = get_the_terms( $splms_course_id, SPLMS_TAXONOMIES['course_category'] );
// Wishlist: only show if NOT enrolled (pre-enrollment feature).
$splms_can_wishlist = splms_get_setting( 'enable_course_wishlist', true ) && ! $splms_is_enrolled;
$splms_can_share    = splms_get_setting( 'enable_course_share', true );
// Determine initial wishlist state for current user.
$splms_user_wishlist    = is_user_logged_in() ? get_user_meta( $splms_user_id, '_splms_course_wishlist', true ) : array();
$splms_user_wishlist    = is_array( $splms_user_wishlist ) ? array_map( 'intval', $splms_user_wishlist ) : array();
$splms_in_wishlist      = in_array( $splms_course_id, $splms_user_wishlist, true );
$splms_is_enable_rating = splms_get_setting( 'enable_course_reviews', true );
?>

<!-- Breadcrumb - Outside hero for better hierarchy -->
<div class="splms-breadcrumb-wrapper">
	<div class="splms-breadcrumb-container">
		<nav class="splms-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'skillpulse-lms' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="splms-breadcrumb__link">
				<?php esc_html_e( 'Home', 'skillpulse-lms' ); ?>
			</a>
			<span class="splms-breadcrumb__separator">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="9,18 15,12 9,6"></polyline>
				</svg>
			</span>
			<a href="<?php echo esc_url( get_post_type_archive_link( SPLMS_POST_TYPES['course'] ) ); ?>" class="splms-breadcrumb__link">
				<?php esc_html_e( 'Courses', 'skillpulse-lms' ); ?>
			</a>
			<?php if ( $splms_categories && ! is_wp_error( $splms_categories ) ) { ?>
				<span class="splms-breadcrumb__separator">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="9,18 15,12 9,6"></polyline>
					</svg>
				</span>
				<a href="<?php echo esc_url( get_term_link( $splms_categories[0] ) ); ?>" class="splms-breadcrumb__link">
					<?php echo esc_html( $splms_categories[0]->name ); ?>
				</a>
			<?php } ?>
		</nav>
	</div>
</div>

<!-- Course Hero - Unified Component -->
<div class="splms-hero splms-hero--course">
	<div class="splms-hero__container">
		<div class="splms-hero__course-layout">

			<!-- Left: Course Information (60%) -->
			<div class="splms-hero__course-main">
				<div class="splms-hero__content">

					<!-- Course Categories as Tags -->
					<?php if ( $splms_categories && ! is_wp_error( $splms_categories ) ) { ?>
						<div class="splms-hero__categories">
							<?php foreach ( $splms_categories as $splms_category ) { ?>
								<a href="<?php echo esc_url( get_term_link( $splms_category ) ); ?>">
									<?php echo esc_html( $splms_category->name ); ?>
								</a>
							<?php } ?>
						</div>
					<?php } ?>

					<!-- Title with Badge -->
					<div class="splms-hero__title">
						<h1>
							<?php the_title(); ?>
						</h1>
						<?php splms_get_template_part( 'course/partials/pricing-model', 'badge' ); ?>
					</div>

					<!-- Short Description -->
					<div class="splms-hero__excerpt">
						<?php echo wp_kses_post( wpautop( get_the_excerpt() ) ); ?>
					</div>

					<!-- Instructor Card - Improved Design -->
					<div class="splms-hero__instructor">
						<div class="splms-hero__instructor-avatar">
							<?php echo get_avatar( get_the_author_meta( 'ID' ), 48, '', get_the_author(), array( 'splms_class' => 'splms-avatar' ) ); ?>
						</div>
						<div class="splms-hero__instructor-details">
							<span class="splms-hero__instructor-details-label"><?php esc_html_e( 'Instructor', 'skillpulse-lms' ); ?></span>
							<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="splms-hero__instructor-details-name">
								<?php the_author(); ?>
							</a>
						</div>
					</div>

					<!-- Quick Stats Bar - Compact Design -->
					<div class="splms-hero__meta-list">
						<?php if ( $splms_rating_summary['average_rating'] > 0 && $splms_is_enable_rating ) { ?>
							<div class="splms-hero__meta-list-item">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" stroke="currentColor"
											stroke-width="2" stroke-linejoin="round"/>
								</svg>
								<span>
									<?php echo number_format( $splms_rating_summary['average_rating'], 1 ); ?>
									(<?php echo esc_html( $splms_rating_summary['total_reviews'] ); ?>)
								</span>
							</div>
						<?php } ?>

						<?php if ( $splms_students_count > 0 ) { ?>
							<!-- Show student count for non-enrolled users -->
							<div class="splms-hero__meta-list-item">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21M13 7C13 9.20914 11.2091 11 9 11C6.79086 11 5 9.20914 5 7C5 4.79086 6.79086 3 9 3C11.2091 3 13 4.79086 13 7Z"
											stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<span>
									<?php
									// Format student count for display.
									if ( $splms_students_count >= 1000 ) {
										echo esc_html( number_format( $splms_students_count / 1000, 1 ) . 'k' );
									} else {
										echo esc_html( number_format( $splms_students_count ) );
									}
									?>
								</span>
							</div>
						<?php } ?>

						<?php if ( $splms_duration ) { ?>
							<div class="splms-hero__meta-list-item">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
									<polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<span><?php echo esc_html( $splms_duration ); ?></span>
							</div>
						<?php } ?>

						<div class="splms-hero__meta-list-item">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M13 2L3 14H12L11 22L21 10H12L13 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<span class="difficulty-<?php echo esc_attr( strtolower( $splms_difficulty ) ); ?>"><?php echo esc_html( ucfirst( $splms_difficulty ) ); ?></span>
						</div>

						<?php if ( $splms_is_enrolled && $splms_last_activity_time ) { ?>
							<!-- Show last activity for enrolled users -->
							<div class="splms-hero__meta-list-item">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
									<polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<span>
									<?php
									printf(
									/* translators: %s: Human-readable time difference. */
										esc_html__( 'Last active %s', 'skillpulse-lms' ),
										esc_html(
											human_time_diff(
												$splms_last_activity_time,
											// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Need timestamp for time difference calculation.
												current_time( 'timestamp' )
											) . ' ' . __( 'ago', 'skillpulse-lms' )
										)
									);
									?>
								</span>
							</div>
						<?php } else { ?>
							<!-- Show last updated for non-enrolled users -->
							<div class="splms-hero__meta-list-item">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H20" stroke="currentColor" stroke-width="2"
											stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M18 14L22 10L18 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M7 10H22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<span><?php echo esc_html( get_the_modified_date() ); ?></span>
							</div>
						<?php } ?>

						<!-- Course Info Button -->
						<div class="splms-hero__meta-list-item splms-hero__meta-list-item--btn">
							<button type="button" class="splms-course-info-btn" data-course-id="<?php echo esc_attr( $splms_course_id ); ?>"
									title="<?php esc_attr_e( 'View Course Information', 'skillpulse-lms' ); ?>">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
									<path d="M12 16V12M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								</svg>
								<span><?php esc_html_e( 'Course Info', 'skillpulse-lms' ); ?></span>
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Right: Sticky Enrollment Card (40%) -->
			<div class="splms-hero__course-sidebar">
				<div class="splms-hero__enrollment-card">

					<?php if ( $splms_is_enrolled ) { ?>
						<!-- Enrolled State - Clean Design -->
						<div class="splms-enrolled-state">
							<?php if ( 'completed' === $splms_enrollment_status ) { ?>
								<div class="splms-status-badge splms-status-badge--completed">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
												stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<span><?php esc_html_e( 'Course Completed', 'skillpulse-lms' ); ?></span>
								</div>

								<div class="splms-completion-message">
									<p><?php esc_html_e( 'Congratulations! You have successfully completed this course.', 'skillpulse-lms' ); ?></p>
								</div>

								<?php
								// Display course sharing section.
								splms_get_template(
									'course/partials/course-share',
									array(
										'splms_course_id' => $splms_course_id,
									)
								);
								?>
							<?php } else { ?>
								<!-- Enhanced Enrolled State with Progress -->

								<!-- Circular Progress Indicator -->
								<?php if ( ! empty( $splms_user_progress ) ) { ?>
									<div class="splms-progress-circle-container">
										<div class="splms-progress-circle" data-progress="<?php echo esc_attr( $splms_user_progress['percentage'] ); ?>">
											<svg class="splms-progress-ring" width="100" height="100">
												<circle class="splms-progress-ring-circle-bg" stroke="#e5e7eb" stroke-width="7" fill="transparent" r="43" cx="50" cy="50"/>
												<circle class="splms-progress-ring-circle" stroke="url(#gradient)" stroke-width="7" fill="transparent" r="43" cx="50" cy="50"
														style="stroke-dasharray: <?php echo esc_attr( 2 * 3.14159 * 43 ); ?>; stroke-dashoffset: <?php echo esc_attr( 2 * 3.14159 * 43 * ( 1 - $splms_user_progress['percentage'] / 100 ) ); ?>;"/>
												<defs>
													<linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
														<stop offset="0%" style="stop-color:var(--splms-primary);stop-opacity:1"/>
														<stop offset="100%" style="stop-color:var(--splms-primary-light);stop-opacity:1"/>
													</linearGradient>
												</defs>
											</svg>
											<div class="splms-progress-text">
												<span class="splms-progress-percentage"><?php echo esc_html( round( $splms_user_progress['percentage'] ) ); ?>%</span>
											</div>
										</div>
									</div>
									<!-- Progress Stats -->
									<div class="splms-progress-stats">
										<div class="splms-progress-stats__header">
											<span class="splms-progress-stats__label"><?php esc_html_e( 'Your Progress', 'skillpulse-lms' ); ?></span>
											<span class="splms-progress-stats__value"><?php echo esc_html( round( $splms_user_progress['percentage'] ) ); ?>%</span>
										</div>
										<div class="splms-progress-bar">
											<div class="splms-progress-bar__fill" style="width: <?php echo esc_attr( $splms_user_progress['percentage'] ); ?>%"></div>
										</div>
										<p class="splms-progress-stats__details">
											<?php
											printf(
											/* translators: 1: Completed lessons, 2: Total lessons. */
												esc_html__( '%1$d of %2$d lessons completed', 'skillpulse-lms' ),
												(int) $splms_user_progress['completed_lessons'],
												(int) $splms_user_progress['total_lessons']
											);
											?>
										</p>
									</div>
								<?php } ?>

								<!-- Up Next Section -->
								<?php if ( $splms_next_lesson ) { ?>
									<div class="splms-up-next-section collapsed" data-collapsible="up-next">
										<h4 class="splms-up-next__title" role="button" aria-expanded="false" tabindex="0">
											<div class="splms-up-next__title-content">
												<svg class="splms-up-next__toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												</svg>
												<?php esc_html_e( 'Up Next', 'skillpulse-lms' ); ?>
											</div>
										</h4>
										<div class="splms-up-next__content">
											<p class="splms-up-next__section"><?php echo esc_html( $splms_next_lesson['section_title'] ); ?></p>
											<p class="splms-up-next__lesson">
												<a href="<?php echo esc_url( $splms_next_lesson['permalink'] ); ?>">
													<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
														<?php if ( SPLMS_POST_TYPES['lesson'] === $splms_next_lesson['type'] ) { ?>
															<path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z"
																	stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
															<polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
																		stroke-linejoin="round"/>
														<?php } else { ?>
															<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
															<path d="M9.09 9C9.3251 8.33167 9.78915 7.76811 10.4 7.40913C11.0108 7.05016 11.7289 6.91894 12.4272 7.03871C13.1255 7.15849 13.7588 7.52152 14.2151 8.06353C14.6713 8.60553 14.9211 9.29152 14.92 10C14.92 12 11.92 13 11.92 13"
																	stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
														<?php } ?>
													</svg>
													<?php echo esc_html( $splms_next_lesson['title'] ); ?>
												</a>
											</p>
										</div>
									</div>
								<?php } elseif ( 'curriculum' !== $splms_current_tab ) { ?>
									<!-- Fallback: Generic Continue Learning -->
									<a href="<?php echo esc_url( add_query_arg( 'tab', 'curriculum', get_permalink() ) ); ?>"
										class="splms-btn splms-btn--primary splms-btn--large splms-btn--block">
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<?php esc_html_e( 'Continue Learning', 'skillpulse-lms' ); ?>
									</a>
								<?php } ?>
								<!-- Unenroll Button -->
								<?php if ( splms_is_student_unenrollment_allowed() ) { ?>
									<button type="button" class="splms-btn splms-btn--secondary splms-btn--block splms-btn-unenroll"
											data-course-id="<?php echo esc_attr( $splms_course_id ); ?>">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<?php esc_html_e( 'Unenroll from Course', 'skillpulse-lms' ); ?>
									</button>
								<?php } ?>
							<?php } ?>
						</div>
					<?php } else { ?>
						<?php

						// Check if user has purchased sections (but not full course).
						$splms_section_pricing_enabled = splms_get_setting( 'enable_section_based_pricing', false );
						$splms_user_purchased_sections = array();
						$splms_has_section_purchase    = false;

						if ( $splms_section_pricing_enabled && $splms_user_id && function_exists( 'splms_get_user_purchased_sections' ) ) {
							$splms_user_purchased_sections = splms_get_user_purchased_sections( $splms_course_id, $splms_user_id );
							$splms_has_section_purchase    = ! empty( $splms_user_purchased_sections );
						}

						$splms_section_summary = splms_get_course_section_pricing_summary( $splms_course_id, $splms_user_id );

						// Purchase Section - Show sections list if section-based pricing is enabled.

						?>
							<div class="splms-purchase-section">
								<!-- Sections List. -->
								<div id="splms-sections-list-anchor" class="splms-all-sections-list">
									<h3 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 700; color: #111827;">
										<?php esc_html_e( 'Courses', 'skillpulse-lms' ); ?>
									</h3>

									<div class="splms-sections-scroll-container">
										<?php foreach ( $splms_section_summary['sections'] as $splms_index => $splms_section ) { ?>
											<a href="<?php echo esc_url( $splms_section['permalink'] ); ?>"
												class="splms-section-row <?php echo esc_attr( $splms_index < $splms_section_summary['total_sections'] - 1 ? 'has-border' : '' ); ?>">

												<!-- Left -->
												<div class="splms-section-left">
													<h5 class="splms-section-title">
														<?php echo esc_html( $splms_section['title'] ); ?>
													</h5>

													<p class="splms-section-label">
														<?php esc_html_e( 'Section', 'skillpulse-lms' ); ?>
													</p>

													<div class="splms-section-stats">
														<?php if ( $splms_section['duration'] ) { ?>
															<span><?php echo esc_html( $splms_section['duration'] ); ?></span>
														<?php } ?>

														<?php if ( $splms_section['duration'] && $splms_section['stats']['lessons'] > 0 ) { ?>
															<span>•</span>
														<?php } ?>

														<?php if ( $splms_section['stats']['lessons'] > 0 ) { ?>
															<span>
															<?php
															/* translators: %d: Number of lessons */
															echo esc_html( sprintf( _n( '%d Lesson', '%d Lessons', $splms_section['stats']['lessons'], 'skillpulse-lms' ), (int) $splms_section['stats']['lessons'] ) );
															?>
						</span>
														<?php } ?>

														<?php if ( $splms_section['stats']['quizzes'] > 0 ) { ?>
															<span>•</span>
															<span>
															<?php
															/* translators: %d: Number of quizzes */
															echo esc_html( sprintf( _n( '%d Quiz', '%d Quizzes', $splms_section['stats']['quizzes'], 'skillpulse-lms' ), (int) $splms_section['stats']['quizzes'] ) );
															?>
						</span>
														<?php } ?>
													</div>
												</div>

												<!-- Right -->
												<div class="splms-section-right">
													<?php if ( $splms_section_pricing_enabled && splms_is_paid_courses_enabled() && 'public_paid' === $splms_access_info['course_access_type'] ) { ?>

														<?php if ( $splms_section['is_purchased'] ) { ?>
															<span class="splms-badge splms-badge--purchased">
																<?php esc_html_e( 'Purchased', 'skillpulse-lms' ); ?>
															</span>
														<?php } else { ?>
															<?php if ( $splms_section['pricing']['is_free'] ) { ?>
																<span class="splms-badge splms-badge--free">
																	<?php esc_html_e( 'Free', 'skillpulse-lms' ); ?>
																</span>
															<?php } else { ?>
																<span class="splms-badge splms-badge--price">
																	<?php echo esc_html( splms_get_price_format( $splms_section['pricing']['effective_price'] ) ); ?>
																</span>
															<?php } ?>
														<?php } ?>

													<?php } ?>

													<svg class="splms-section-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none">
														<path d="M9 5L16 12L9 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
													</svg>
												</div>
											</a>
										<?php } ?>
									</div>
								</div>

								<div class="splms-purchase-section-footer">
									<?php
									if ( $splms_has_section_purchase ) {
										// User has purchased sections - show comprehensive section list.
										$splms_total_sections    = $splms_section_summary['total_sections'];
										$splms_owned_count       = count( $splms_user_purchased_sections );
										$splms_owns_all_sections = $splms_total_sections > 0 && $splms_owned_count >= $splms_total_sections;
										$splms_can_show_upgrade  = 'public_paid' === $splms_course_access_type && ! $splms_owns_all_sections && splms_is_paid_courses_enabled();

										if ( $splms_can_show_upgrade ) {
											$splms_upgrade_info = splms_calculate_upgrade_price( $splms_course_id, $splms_user_id );

											if ( isset( $splms_upgrade_info['upgrade_price'] ) && $splms_upgrade_info['upgrade_price'] > 0 ) {
												$splms_savings          = isset( $splms_upgrade_info['savings'] ) ? $splms_upgrade_info['savings'] : 0;
												$splms_full_price       = isset( $splms_upgrade_info['full_course_price'] ) ? $splms_upgrade_info['full_course_price'] : 0;
												$splms_discount_percent = $splms_savings > 0 && $splms_full_price > 0 ? round( ( $splms_savings / $splms_full_price ) * 100 ) : 0;
												?>
													<div class="splms-upgrade-section">
														<div class="splms-upgrade-banner">
															<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																<path d="M13 2L3 14H12L11 22L21 10H12L13 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"
																		stroke-linejoin="round"/>
															</svg>
															<div class="splms-upgrade-text">
																<h4><?php esc_html_e( 'Upgrade to Full Course', 'skillpulse-lms' ); ?></h4>
																<p>
																	<?php
																	printf(
																	/* translators: %s: Full course price. */
																		esc_html__( 'Get all sections for %s', 'skillpulse-lms' ),
																		'<strong>' . esc_html( splms_get_price_format( $splms_full_price ) ) . '</strong>'
																	);
																	?>
																</p>
																		<?php if ( $splms_discount_percent > 0 ) : ?>
																	<p class="splms-upgrade-savings">
																			<?php
																			printf(
																			/* translators: 1: Discount percentage, 2: Savings amount. */
																				esc_html__( '(%1$d%% OFF - Save %2$s)', 'skillpulse-lms' ),
																				(int) $splms_discount_percent,
																				esc_html( splms_get_price_format( $splms_savings ) )
																			);
																			?>
																	</p>
																<?php endif; ?>
															</div>
														</div>
																<?php $splms_upgrade_url = splms_get_course_purchase_url( $splms_course_id, $splms_user_id, 'full_course' ); ?>
															<a href="<?php echo esc_url( $splms_upgrade_url ); ?>" class="splms-btn btn splms-btn--secondary splms-btn--block">
																	<?php esc_html_e( 'Upgrade Now', 'skillpulse-lms' ); ?>
															</a>
												</div>
												<?php
											}
										}
									} else {
										$splms_course_type_data = splms_get_course_type_data( $splms_access_info );
										if ( isset( $splms_course_type_data['price_display'] ) ) {
											echo '<div class="splms-price-container">';
											echo wp_kses_post( $splms_course_type_data['price_display'] );
											echo '</div>';
										}
										?>
										<!-- Enrollment Button. -->
										<div class="splms-enrollment-action">
											<?php
											echo wp_kses_post(
												splms_render_enrollment_button(
													$splms_course_id,
													is_user_logged_in() ? $splms_user_id : 0,
													$splms_access_info,
													$splms_enrollment_dates,
													$splms_capacity_info,
													$splms_is_enrolled
												)
											);
											?>
										</div>
									<?php } ?>
								</div>
							</div>
							<!-- Secondary Actions (Wishlist & Share) -->
							<?php if ( $splms_can_wishlist || $splms_can_share ) { ?>
								<div class="splms-secondary-actions">
									<?php if ( $splms_can_wishlist ) { ?>
										<button type="button"
												class="splms-action-btn splms-action-btn--wishlist wishlist-btn <?php echo esc_attr( $splms_in_wishlist ? 'in-wishlist active' : '' ); ?>"
												data-course-id="<?php echo esc_attr( $splms_course_id ); ?>" aria-pressed="<?php echo esc_attr( $splms_in_wishlist ? 'true' : 'false' ); ?>"
												title="
													<?php
													echo $splms_in_wishlist ? esc_attr__( 'Remove from Wishlist', 'skillpulse-lms' ) : esc_attr__(
														'Add to Wishlist',
														'skillpulse-lms'
													);
													?>
														">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo esc_attr( $splms_in_wishlist ? 'currentColor' : 'none' ); ?>"
												xmlns="http://www.w3.org/2000/svg">
												<path d="M20.84 4.61A5.5 5.5 0 0 0 16.5 2.5A5.5 5.5 0 0 0 12 5.5A5.5 5.5 0 0 0 7.5 2.5A5.5 5.5 0 0 0 3.16 4.61A5.5 5.5 0 0 0 2 8.89A5.5 5.5 0 0 0 3.16 13.17L12 22L20.84 13.17A5.5 5.5 0 0 0 22 8.89A5.5 5.5 0 0 0 20.84 4.61Z"
														stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<span><?php echo $splms_in_wishlist ? esc_html__( 'Wishlisted', 'skillpulse-lms' ) : esc_html__( 'Wishlist', 'skillpulse-lms' ); ?></span>
										</button>
									<?php } ?>

									<?php if ( $splms_can_share ) { ?>
										<button type="button" class="splms-action-btn splms-action-btn--share share-btn" data-course-id="<?php echo esc_attr( $splms_course_id ); ?>"
												title="<?php esc_attr_e( 'Share Course', 'skillpulse-lms' ); ?>">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/>
												<circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
												<circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/>
												<path d="M8.59 13.51L15.42 17.49" stroke="currentColor" stroke-width="2"/>
												<path d="M15.41 6.51L8.59 10.49" stroke="currentColor" stroke-width="2"/>
											</svg>
											<span><?php esc_html_e( 'Share', 'skillpulse-lms' ); ?></span>
										</button>
									<?php } ?>
								</div>
							<?php } ?>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Course Information Modal -->
<div class="splms-course-info-modal" id="splms-course-info-modal" role="dialog" aria-labelledby="course-info-modal-title" aria-hidden="true">
	<div class="splms-modal-overlay"></div>
	<div class="splms-modal-content">
		<div class="splms-modal-header">
			<h3 id="course-info-modal-title">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
					<path d="M12 16V12M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<?php esc_html_e( 'Course Information', 'skillpulse-lms' ); ?>
			</h3>
			<button type="button" class="splms-modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'skillpulse-lms' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>
		<div class="splms-modal-body">
			<div class="course-info-list">
				<?php
				// Get course settings for the modal.
				$splms_course_settings = splms_get_course_settings( $splms_course_id );

				// Display course type with correct data.
				if ( ! empty( $splms_access_info['course_access_type'] ) ) {
					$splms_course_type_data = splms_get_course_type_data( $splms_access_info );
					?>
					<div class="info-item">
						<div class="info-icon">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
								<path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<div class="info-content">
							<span class="info-label"><?php echo esc_html( $splms_course_type_data['label'] ); ?></span>
							<span class="info-value">
								<?php
								if ( isset( $splms_course_type_data['price_display'] ) ) {
									echo wp_kses_post( $splms_course_type_data['price_display'] );
								} elseif ( isset( $splms_course_type_data['restriction_display'] ) ) {
									echo wp_kses_post( $splms_course_type_data['restriction_display'] );
								} else {
									echo esc_html( $splms_course_type_data['display'] );
								}
								?>
							</span>
						</div>
					</div>
					<?php
				}

				// Show membership requirement if set.
				$splms_access_settings      = isset( $splms_course_settings['course_access_settings'] ) ? $splms_course_settings['course_access_settings'] : array();
				$splms_required_memberships = isset( $splms_access_settings['required_memberships'] ) ? $splms_access_settings['required_memberships'] : array();

				if ( ! empty( $splms_required_memberships ) ) {
					$splms_all_memberships  = splms_get_available_memberships();
					$splms_membership_names = array();

					foreach ( $splms_required_memberships as $splms_membership_id ) {
						foreach ( $splms_all_memberships as $splms_membership ) {
							if ( isset( $splms_membership['id'] ) && $splms_membership['id'] === $splms_membership_id ) {
								$splms_membership_names[] = $splms_membership['name'];
								break;
							}
						}
					}

					if ( ! empty( $splms_membership_names ) ) {
						?>
						<div class="info-item">
							<div class="info-icon">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21M13 7C13 9.20914 11.2091 11 9 11C6.79086 11 5 9.20914 5 7C5 4.79086 6.79086 3 9 3C11.2091 3 13 4.79086 13 7ZM23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88"
											stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</div>
							<div class="info-content">
								<span class="info-label"><?php esc_html_e( 'Membership', 'skillpulse-lms' ); ?></span>
								<span class="info-value"><?php echo esc_html( implode( ', ', $splms_membership_names ) ); ?></span>
							</div>
						</div>
						<?php
					}
				}
				?>

				<div class="info-item">
					<div class="info-icon">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 10L11 14L9 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
						</svg>
					</div>
					<div class="info-content">
						<span class="info-label"><?php esc_html_e( 'Learning Method', 'skillpulse-lms' ); ?></span>
						<span class="info-value"><?php echo esc_html( splms_get_formatted_learning_method( $splms_course_id ) ); ?></span>
					</div>
				</div>

				<div class="info-item">
					<div class="info-icon">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M16 4H18C19.1046 4 20 4.89543 20 6V18C20 19.1046 19.1046 20 18 20H6C4.89543 20 4 19.1046 4 18V6C4 4.89543 4.89543 4 6 4H8"
									stroke="currentColor"
									stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<rect x="8" y="2" width="8" height="4" rx="1" ry="1" stroke="currentColor" stroke-width="2"/>
						</svg>
					</div>
					<div class="info-content">
						<span class="info-label"><?php esc_html_e( 'Delivery Mode', 'skillpulse-lms' ); ?></span>
						<span class="info-value"><?php echo esc_html( splms_get_formatted_delivery_mode( $splms_course_id ) ); ?></span>
					</div>
				</div>

				<?php
				// Display course start date for cohort-based courses.
				$splms_delivery_info = splms_get_course_delivery_info( $splms_course_id );
				if ( 'cohort' === $splms_delivery_info['delivery_mode'] && ! empty( $splms_delivery_info['course_start_date'] ) ) {
					$splms_start_timestamp = strtotime( $splms_delivery_info['course_start_date'] );
					if ( false !== $splms_start_timestamp ) {
						?>
						<div class="info-item">
							<div class="info-icon">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
									<line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/>
									<line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/>
									<line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
								</svg>
							</div>
							<div class="info-content">
								<span class="info-label"><?php esc_html_e( 'Start Date', 'skillpulse-lms' ); ?></span>
								<span class="info-value"><?php echo esc_html( date_i18n( get_option( 'date_format' ), $splms_start_timestamp ) ); ?></span>
							</div>
						</div>
						<?php
					}
				}
				?>

				<div class="info-item">
					<div class="info-icon">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4905 2.02168 11.3391C2.16356 9.18755 2.99721 7.14354 4.39828 5.49003C5.79935 3.83652 7.69279 2.65676 9.79619 2.11205C11.8996 1.56734 14.1003 1.68159 16.13 2.43"
									stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<polyline points="22,4 12,14.01 9,11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="info-content">
						<span class="info-label"><?php esc_html_e( 'Completion', 'skillpulse-lms' ); ?></span>
						<span class="info-value"><?php echo esc_html( splms_get_formatted_completion_criteria( $splms_course_id ) ); ?></span>
					</div>
				</div>

				<?php
				$splms_capacity_info_modal = splms_get_course_max_enrollment_info( $splms_course_id );
				if ( $splms_capacity_info_modal['has_limit'] ) {
					?>
					<div class="info-item">
						<div class="info-icon">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21M13 7C13 9.20914 11.2091 11 9 11C6.79086 11 5 9.20914 5 7C5 4.79086 6.79086 3 9 3C11.2091 3 13 4.79086 13 7ZM23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88"
										stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<div class="info-content">
							<span class="info-label"><?php esc_html_e( 'Capacity', 'skillpulse-lms' ); ?></span>
							<span class="info-value capacity-<?php echo esc_attr( $splms_capacity_info_modal['is_full'] ? 'full' : 'available' ); ?>">
							<?php
							if ( $splms_capacity_info_modal['is_full'] ) {
								$splms_capacity_text = sprintf(
								/* translators: %1$d: Enrolled count, %2$d: Max enrollment. */
									esc_html__( 'Full (%1$d/%2$d)', 'skillpulse-lms' ),
									(int) $splms_capacity_info_modal['enrolled_count'],
									(int) $splms_capacity_info_modal['max_enrollment']
								);
							} else {
								$splms_capacity_text = sprintf(
								/* translators: %1$d: Enrolled count, %2$d: Max enrollment. */
									esc_html__( '%1$d of %2$d spots', 'skillpulse-lms' ),
									(int) $splms_capacity_info_modal['enrolled_count'],
									(int) $splms_capacity_info_modal['max_enrollment']
								);
							}
							echo esc_html( $splms_capacity_text );
							?>
						</span>
						</div>
					</div>
				<?php } ?>

				<?php
				$splms_enrollment_dates_modal = splms_get_course_enrollment_dates( $splms_course_id );
				if ( ! empty( $splms_enrollment_dates_modal['start_date'] ) || ! empty( $splms_enrollment_dates_modal['end_date'] ) ) {
					// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Using timestamp for date comparison.
					$splms_current_time            = current_time( 'timestamp' );
					$splms_enrollment_status       = 'open';
					$splms_enrollment_status_class = 'open';
					$splms_enrollment_status_text  = __( 'Open Enrollment', 'skillpulse-lms' );

					if ( ! empty( $splms_enrollment_dates_modal['start_date'] ) ) {
						$splms_start_time = strtotime( $splms_enrollment_dates_modal['start_date'] );
						if ( $splms_current_time < $splms_start_time ) {
							$splms_enrollment_status       = 'not_started';
							$splms_enrollment_status_class = 'not-started';
							$splms_enrollment_status_text  = sprintf(
							/* translators: %s: Start date. */
								__( 'Starts %s', 'skillpulse-lms' ),
								date_i18n( get_option( 'date_format' ), $splms_start_time )
							);
						}
					}

					if ( ! empty( $splms_enrollment_dates_modal['end_date'] ) ) {
						$splms_end_time = strtotime( $splms_enrollment_dates_modal['end_date'] );
						if ( $splms_current_time > $splms_end_time ) {
							$splms_enrollment_status       = 'ended';
							$splms_enrollment_status_class = 'closed';
							$splms_enrollment_status_text  = __( 'Enrollment Closed', 'skillpulse-lms' );
						} elseif ( 'open' === $splms_enrollment_status ) {
							$splms_enrollment_status_text = sprintf(
							/* translators: %s: End date. */
								__( 'Open until %s', 'skillpulse-lms' ),
								date_i18n( get_option( 'date_format' ), $splms_end_time )
							);
						}
					}
					?>
					<div class="info-item">
						<div class="info-icon">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
								<line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/>
								<line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/>
								<line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
							</svg>
						</div>
						<div class="info-content">
							<span class="info-label"><?php esc_html_e( 'Enrollment', 'skillpulse-lms' ); ?></span>
							<span class="info-value enrollment-<?php echo esc_attr( $splms_enrollment_status_class ); ?>">
								<?php echo esc_html( $splms_enrollment_status_text ); ?>
							</span>
						</div>
					</div>
				<?php } ?>

				<?php
				$splms_certificate_enabled_modal = splms_is_certificate_enabled( $splms_course_id );
				if ( $splms_certificate_enabled_modal ) {
					?>
					<div class="info-item">
						<div class="info-icon">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
										stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<div class="info-content">
							<span class="info-label"><?php esc_html_e( 'Certificate', 'skillpulse-lms' ); ?></span>
							<span class="info-value certificate-yes">
							<?php esc_html_e( 'Yes', 'skillpulse-lms' ); ?>
						</span>
						</div>
					</div>

					<?php
					$splms_scheduling_settings = isset( $splms_course_settings['course_scheduling_settings'] ) ? $splms_course_settings['course_scheduling_settings'] : array();
					$splms_expiration_enabled  = isset( $splms_scheduling_settings['enable_enrollment_expiration'] ) ? (bool) $splms_scheduling_settings['enable_enrollment_expiration'] : false;
					$splms_expiration_days     = isset( $splms_scheduling_settings['enrollment_expiration_days'] ) ? (int) $splms_scheduling_settings['enrollment_expiration_days'] : 0;

					if ( $splms_expiration_enabled && $splms_expiration_days > 0 ) {
						$splms_access_text = sprintf(
						/* translators: %d: Number of days. */
							_n( '%d day', '%d days', $splms_expiration_days, 'skillpulse-lms' ),
							$splms_expiration_days
						);
					} else {
						$splms_access_text = __( 'Lifetime', 'skillpulse-lms' );
					}
					?>
					<div class="info-item">
						<div class="info-icon">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M21 16V8C20.9996 7.64928 20.9071 7.30481 20.7315 7.00116C20.556 6.69751 20.3037 6.44536 20 6.27L13 2.27C12.696 2.09446 12.3511 2.00205 12 2.00205C11.6489 2.00205 11.304 2.09446 11 2.27L4 6.27C3.69626 6.44536 3.44398 6.69751 3.26846 7.00116C3.09294 7.30481 3.00036 7.64928 3 8V16C3.00036 16.3507 3.09294 16.6952 3.26846 16.9988C3.44398 17.3025 3.69626 17.5546 4 17.73L11 21.73C11.304 21.9055 11.6489 21.9979 12 21.9979C12.3511 21.9979 12.696 21.9055 13 21.73L20 17.73C20.3037 17.5546 20.556 17.3025 20.7315 16.9988C20.9071 16.6952 20.9996 16.3507 21 16Z"
										stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<div class="info-content">
							<span class="info-label"><?php esc_html_e( 'Access', 'skillpulse-lms' ); ?></span>
							<span class="info-value access-lifetime"><?php echo esc_html( $splms_access_text ); ?></span>
						</div>
					</div>

					<div class="info-item">
						<div class="info-icon">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21M13 7C13 9.20914 11.2091 11 9 11C6.79086 11 5 9.20914 5 7C5 4.79086 6.79086 3 9 3C11.2091 3 13 4.79086 13 7ZM23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88"
										stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<div class="info-content">
							<span class="info-label"><?php esc_html_e( 'Language', 'skillpulse-lms' ); ?></span>
							<span class="info-value"><?php echo esc_html( splms_get_formatted_course_language( $splms_course_id ) ); ?></span>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
